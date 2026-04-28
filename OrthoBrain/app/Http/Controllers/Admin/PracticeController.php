<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Practice;
use App\Models\Zipcode;
use App\Notifications\PracticeActivatedNotification;
use App\Notifications\PracticeRequestApproved;
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
        ];
        $sortKey = $request->get('sort');
        $sortCol = $sortable[$sortKey] ?? 'practices.created_at';
        $dir     = strtolower($request->get('dir', $sortKey ? 'asc' : 'desc')) === 'desc' ? 'desc' : 'asc';

        $query = Practice::query()
            ->select('practices.*')
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
