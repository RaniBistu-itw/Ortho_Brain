<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifs = $user->notifications()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($n) {
                $d = $n->data;
                return [
                    'type'    => 'notification',
                    'id'      => $n->id,
                    'title'   => $d['title'] ?? 'Notification',
                    'body'    => $d['body']  ?? '',
                    'url'     => $d['url']   ?? null,
                    'kind'    => $d['kind']  ?? null,
                    'read'    => (bool) $n->read_at,
                    'time'    => $n->created_at->diffForHumans(),
                ];
            });

        $doctor   = \App\Models\Doctor::where('user_id', $user->id)->first();
        $pending  = $doctor
            ? $doctor->pendingPractices()->get()->map(function ($p) {
                $created = optional($p->pivot?->created_at);
                return [
                    'type'         => 'pending',
                    'id'           => (int) $p->pivot->id,
                    'practiceName' => $p->name,
                    'title'        => 'Practice request under review',
                    'body'         => 'Your request to join '.$p->name.' is awaiting admin review.',
                    'url'          => route('doctor.profile.index', ['tab' => 'practices']),
                    'kind'         => 'pending',
                    'read'         => false,
                    'time'         => $created ? $created->diffForHumans() : '',
                ];
            })
            : collect();

        return response()->json([
            'items'         => $pending->concat($notifs)->values(),
            'unread'        => $user->unreadNotifications()->count(),
            'pendingCount'  => $pending->count(),
            'bellCount'     => $user->unreadNotifications()->count() + $pending->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $n = $request->user()->notifications()->findOrFail($notification);
        $n->markAsRead();

        return response()->json([
            'ok'     => true,
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true, 'unread' => 0]);
    }

    public function destroy(Request $request, string $notification): JsonResponse
    {
        $n = $request->user()->notifications()->findOrFail($notification);
        $n->delete();

        return response()->json([
            'ok'     => true,
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json(['ok' => true, 'unread' => 0]);
    }

    /**
     * Bell-dropdown action — cancel a pending practice-join request.
     * Mirrors PracticeMembershipController::cancel but returns JSON.
     */
    public function cancelPending(Request $request, int $link): JsonResponse
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $row = DB::table('doctor_practice')->where('id', $link)->first();
        abort_unless($row && (int) $row->doctor_id === $doctor->id, 404);

        if ($row->approval_status !== 'PENDING') {
            return response()->json(['ok' => false, 'message' => 'Only pending requests can be cancelled.'], 422);
        }

        DB::table('doctor_practice')->where('id', $link)->update([
            'approval_status' => 'CANCELLED',
            'updated_at'      => now(),
        ]);

        $unread       = $request->user()->unreadNotifications()->count();
        $pendingCount = $doctor->pendingPractices()->count();

        return response()->json([
            'ok'           => true,
            'unread'       => $unread,
            'pendingCount' => $pendingCount,
            'bellCount'    => $unread + $pendingCount,
        ]);
    }
}
