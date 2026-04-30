<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Practice;
use App\Models\Zipcode;
use App\Notifications\PracticeActivatedNotification;
use App\Notifications\PracticeDeactivatedNotification;
use App\Notifications\PracticeRequestApproved;
use App\Notifications\PracticeRequestRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PracticeController extends Controller
{
    private const ALLOWED_STATUSES = ['ACTIVE', 'INACTIVE'];

    public function index(Request $request)
    {
        $status = strtoupper((string) $request->query('status', ''));
        $status = in_array($status, self::ALLOWED_STATUSES, true) ? $status : null;

        $search = trim((string) $request->query('search', ''));

        $sortable = [
            'practice'      => 'practices.name',
            'location'      => 'countries.name',
            'contact'       => 'practices.phone_number',
            'members_count' => 'members_count',
            'created_at'    => 'practices.created_at',
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sortCol = $sortable[$sortKey];
            $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sortKey = null;
            $sortCol = 'practices.created_at';
            $dir     = $order === 'oldest' ? 'asc' : 'desc';
        }

        $query = Practice::query()
            ->select([
                'practices.id',
                'practices.name',
                'practices.street_address_1',
                'practices.phone_country_code',
                'practices.phone_number',
                'practices.website',
                'practices.status',
                'practices.city_id',
                'practices.state_id',
                'practices.country_id',
                'practices.logo_path',
                'practices.created_at',
            ])
            ->with([
                'city:id,name',
                'state:id,name',
                'country:id,name',
            ])
            ->withCount('members')
            ->withCount(['doctors as pending_pivot_count' => function ($q) {
                $q->where('doctor_practice.approval_status', 'PENDING');
            }]);

        // Always float practices with pending doctor approvals to the top.
        $query->orderByDesc('pending_pivot_count');

        if ($sortKey === 'location') {
            $query->leftJoin('countries', 'countries.id', '=', 'practices.country_id')
                  ->orderBy($sortCol, $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        $practices = $query
            ->when($status, fn ($q) => $q->where('practices.status', $status))
            ->when(
                $request->filled('country_id'),
                fn ($q) => $q->where('practices.country_id', $request->integer('country_id'))
            )
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('practices.name', 'like', $like)
                      ->orWhere('practices.website', 'like', $like)
                      ->orWhere('practices.phone_number', 'like', $like);
                });
            })
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

    public function edit(Practice $practice)
    {
        $practice->load(['city', 'state', 'country', 'zipcode']);

        $zipcodes = Zipcode::with('city.state.country')
            ->where('status', 'ACTIVE')
            ->whereHas('city', fn ($q) => $q->where('status', 'ACTIVE'))
            ->orderBy('code')
            ->get();

        $phoneCodes = Country::where('status', 'ACTIVE')
            ->select('phone_code')
            ->distinct()
            ->orderBy('phone_code')
            ->pluck('phone_code')
            ->all();

        return view('admin.practices.edit', [
            'practice'   => $practice,
            'zipcodes'   => $zipcodes,
            'phoneCodes' => $phoneCodes,
        ]);
    }

    public function update(Request $request, Practice $practice)
    {
        $phoneCodes = Country::where('status', 'ACTIVE')
            ->select('phone_code')
            ->distinct()
            ->pluck('phone_code')
            ->all();

        $data = $request->validate([
            'name'               => 'required|string|max:200',
            'website'            => 'nullable|string|max:500',
            'phone_country_code' => ['required', Rule::in($phoneCodes)],
            'phone_number'       => 'required|string|regex:/^\d{10}$/',
            'street_address_1'   => 'required|string|min:5|max:255',
            'street_address_2'   => 'nullable|string|max:255',
            'zip_id'             => 'required|integer|exists:zipcodes,id',
            'city_id'            => 'required|integer|exists:cities,id',
            'state_id'           => 'required|integer|exists:states,id',
            'country_id'         => 'required|integer|exists:countries,id',
        ], [
            'phone_number.regex' => 'Phone must be exactly 10 digits.',
        ]);

        $practice->update($data);

        return redirect()
            ->route('admin.practices.show', $practice)
            ->with('success', 'Practice details updated.');
    }

    public function updateStatus(Request $request, Practice $practice)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', self::ALLOWED_STATUSES),
        ]);

        $previous = $practice->status;
        $adminId  = Auth::user()?->admin?->id;

        $pendingDoctors    = collect();
        $approvedDoctors   = collect();
        $deactivatedDoctors = collect();

        DB::transaction(function () use ($practice, $data, $previous, $adminId, &$pendingDoctors, &$approvedDoctors, &$deactivatedDoctors) {
            $practice->update(['status' => $data['status']]);

            if ($previous === 'INACTIVE' && $data['status'] === 'ACTIVE') {
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
                }

                $approvedDoctors = $practice->doctors()
                    ->wherePivot('approval_status', 'APPROVED')
                    ->with('user')
                    ->get();
            }

            if ($previous === 'ACTIVE' && $data['status'] === 'INACTIVE') {
                $deactivatedDoctors = $practice->doctors()
                    ->wherePivot('approval_status', 'APPROVED')
                    ->with('user')
                    ->get();
            }
        });

        // Send notifications outside the transaction so mail failures never roll back DB changes.
        foreach ($pendingDoctors as $doctor) {
            try { $doctor->user?->notify(new PracticeRequestApproved($practice)); } catch (\Throwable) {}
            usleep(600000);
        }
        foreach ($approvedDoctors as $doctor) {
            try { $doctor->user?->notify(new PracticeActivatedNotification($practice)); } catch (\Throwable) {}
            usleep(600000);
        }
        foreach ($deactivatedDoctors as $doctor) {
            try { $doctor->user?->notify(new PracticeDeactivatedNotification($practice)); } catch (\Throwable) {}
            usleep(600000);
        }

        return response()->json([
            'ok'     => true,
            'status' => $practice->fresh()->status,
        ]);
    }

    /**
     * Bulk approve or reject every PENDING doctor link for a practice.
     */
    public function bulkPendingAction(Request $request, Practice $practice)
    {
        $data = $request->validate([
            'action' => 'required|in:APPROVE,REJECT',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($data['action'] === 'REJECT' && empty(trim((string) ($data['reason'] ?? '')))) {
            return response()->json([
                'ok'      => false,
                'message' => 'A rejection reason is required.',
            ], 422);
        }

        $pendingPivots = DB::table('doctor_practice')
            ->where('practice_id', $practice->id)
            ->where('approval_status', 'PENDING')
            ->get(['id', 'doctor_id']);

        if ($pendingPivots->isEmpty()) {
            return response()->json(['ok' => true, 'count' => 0]);
        }

        $adminId    = Auth::user()?->admin?->id;
        $doctorIds  = $pendingPivots->pluck('doctor_id')->all();
        $count      = $pendingPivots->count();

        DB::transaction(function () use ($practice, $data, $adminId) {
            if ($data['action'] === 'APPROVE') {
                DB::table('doctor_practice')
                    ->where('practice_id', $practice->id)
                    ->where('approval_status', 'PENDING')
                    ->update([
                        'approval_status'      => 'APPROVED',
                        'approved_at'          => now(),
                        'approved_by_admin_id' => $adminId,
                        'rejected_at'          => null,
                        'rejection_reason'     => null,
                        'updated_at'           => now(),
                    ]);
            } else {
                DB::table('doctor_practice')
                    ->where('practice_id', $practice->id)
                    ->where('approval_status', 'PENDING')
                    ->update([
                        'approval_status'      => 'REJECTED',
                        'rejected_at'          => now(),
                        'rejection_reason'     => $data['reason'],
                        'approved_by_admin_id' => $adminId,
                        'approved_at'          => null,
                        'updated_at'           => now(),
                    ]);
            }
        });

        $doctors = Doctor::with('user')->whereIn('id', $doctorIds)->get();
        foreach ($doctors as $doctor) {
            if (! $doctor->user) continue;
            try {
                $doctor->user->notify(
                    $data['action'] === 'APPROVE'
                        ? new PracticeRequestApproved($practice)
                        : new PracticeRequestRejected($practice, $data['reason'])
                );
            } catch (\Throwable) {}
            usleep(600000);
        }

        return response()->json(['ok' => true, 'count' => $count]);
    }
}
