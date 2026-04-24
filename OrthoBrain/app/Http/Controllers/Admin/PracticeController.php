<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Practice;
use App\Notifications\PracticeActivatedNotification;
use App\Notifications\PracticeRequestApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PracticeController extends Controller
{
    private const ALLOWED_STATUSES = ['ACTIVE', 'INACTIVE'];

    public function index(Request $request)
    {
        $status = strtoupper((string) $request->query('status', ''));
        $status = in_array($status, self::ALLOWED_STATUSES, true) ? $status : null;

        $search = trim((string) $request->query('search', ''));

        $practices = Practice::query()
            ->with([
                'owner:id,first_name,last_name',
                'city:id,name',
                'state:id,name',
                'country:id,name',
            ])
            ->withCount('members')
            ->withCount(['doctors as pending_pivot_count' => function ($q) {
                $q->where('doctor_practice.approval_status', 'PENDING');
            }])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when(
                $request->filled('country_id'),
                fn ($q) => $q->where('country_id', $request->integer('country_id'))
            )
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                      ->orWhere('website', 'like', $like)
                      ->orWhere('phone_number', 'like', $like);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Practice::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.practices.index', [
            'practices'     => $practices,
            'countries'     => Country::orderBy('name')->get(['id', 'name']),
            'currentStatus' => $status,
            'statusCounts'  => $statusCounts,
            'totalCount'    => $statusCounts->sum(),
        ]);
    }

    public function show(Practice $practice)
    {
        $practice->load([
            'owner',
            'country',
            'state',
            'city',
            'zipcode',
            'members',
            'doctors.user:id,email',
        ]);

        return view('admin.practices.show', [
            'practice' => $practice,
        ]);
    }

    public function updateStatus(Request $request, Practice $practice)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', self::ALLOWED_STATUSES),
        ]);

        $previous = $practice->status;
        $adminId  = Auth::user()?->admin?->id;

        DB::transaction(function () use ($practice, $data, $previous, $adminId) {
            $practice->update(['status' => $data['status']]);

            if ($previous === 'INACTIVE' && $data['status'] === 'ACTIVE') {
                // Cascade: approve every PENDING pivot for this practice.
                $pendingDoctors = $practice->doctors()
                    ->wherePivot('approval_status', 'PENDING')
                    ->with('user')
                    ->get();

                if ($pendingDoctors->isNotEmpty()) {
                    DB::table('doctor_practice')
                        ->where('practice_id', $practice->id)
                        ->where('approval_status', 'PENDING')
                        ->update([
                            'approval_status'      => 'APPROVED',
                            'approved_at'          => now(),
                            'approved_by_admin_id' => $adminId,
                            'updated_at'           => now(),
                        ]);

                    foreach ($pendingDoctors as $doctor) {
                        $doctor->user?->notify(new PracticeRequestApproved($practice));
                    }
                }

                // Already-approved doctors: notify that the practice is back online.
                $practice->doctors()
                    ->wherePivot('approval_status', 'APPROVED')
                    ->with('user')
                    ->get()
                    ->each(function ($doctor) use ($practice) {
                        $doctor->user?->notify(new PracticeActivatedNotification($practice));
                    });
            }
        });

        return response()->json([
            'ok'     => true,
            'status' => $practice->fresh()->status,
        ]);
    }
}
