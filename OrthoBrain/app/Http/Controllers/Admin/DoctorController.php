<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorSectionRequest;
use App\Models\BuccalCorridorOption;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Modality;
use App\Models\Practice;
use App\Models\Specialty;
use App\Models\TreatmentModality;
use App\Models\Zipcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    private const ALLOWED_STATUSES = ['PENDING', 'APPROVED', 'REJECTED', 'SUSPENDED'];

    public function index(Request $request)
    {
        $status = strtoupper((string) $request->query('status', ''));
        $status = in_array($status, self::ALLOWED_STATUSES, true) ? $status : null;

        $search = trim((string) $request->query('search', ''));

        $sortable = [
            'doctor'     => 'doctors.last_name',
            'contact'    => 'doctors.doctor_contact_email',
            'practice'   => 'practices.name',
            'created_at' => 'doctors.created_at',
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sortCol = $sortable[$sortKey];
            $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sortKey = null;
            $sortCol = 'doctors.created_at';
            $dir     = $order === 'oldest' ? 'asc' : 'desc';
        }

        $query = Doctor::query()
            ->with(['practice:id,name'])
            ->select([
                'doctors.id',
                'doctors.first_name',
                'doctors.last_name',
                'doctors.doctor_contact_email',
                'doctors.other_email',
                'doctors.doctor_cell_phone',
                'doctors.approval_status',
                'doctors.practice_id',
                'doctors.created_at',
                'doctors.profile_photo_s3_key',
            ]);

        if ($sortKey === 'practice') {
            $query->leftJoin('practices', 'practices.id', '=', 'doctors.practice_id')
                  ->orderBy($sortCol, $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        $doctors = $query
            ->when($status, fn ($q) => $q->where('doctors.approval_status', $status))
            ->when(
                $request->filled('practice_id'),
                fn ($q) => $q->where('doctors.practice_id', $request->integer('practice_id'))
            )
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($w) use ($like) {
                    $w->where('doctors.first_name', 'like', $like)
                        ->orWhere('doctors.last_name', 'like', $like)
                        ->orWhere('doctors.doctor_contact_email', 'like', $like)
                        ->orWhere('doctors.other_email', 'like', $like)
                        ->orWhere('doctors.doctor_cell_phone', 'like', $like);
                });
            })
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Doctor::query()
            ->selectRaw('approval_status, COUNT(*) as total')
            ->groupBy('approval_status')
            ->pluck('total', 'approval_status');

        $practices = Practice::orderBy('name')->get(['id', 'name']);

        // AJAX live-filter: ship just the swappable results pane so the search
        // input on /admin/doctors keeps focus and the page doesn't flicker.
        if ($request->ajax()) {
            return view('admin.doctors._results', [
                'doctors' => $doctors,
                'practices' => $practices,
            ])->render();
        }

        return view('admin.doctors.index', [
            'doctors' => $doctors,
            'practices' => $practices,
            'currentStatus' => $status,
            'statusCounts' => $statusCounts,
            'totalCount' => $statusCounts->sum(),
            'pendingCount' => (int) ($statusCounts['PENDING'] ?? 0),
        ]);
    }

    public function create()
    {
        $zipcodes = Zipcode::with('city.state.country')
            ->where('status', 'ACTIVE')
            ->whereHas('city', fn ($q) => $q->where('status', 'ACTIVE'))
            ->orderBy('code')
            ->get();

        return view('admin.doctors.create', [
            'zipcodes' => $zipcodes,
            'modalitiesList' => Modality::orderBy('id')->get(),
            'specialtiesList' => Specialty::orderBy('id')->get(),
            'treatmentModalitiesList' => TreatmentModality::orderBy('id')->get(),
            'buccalCorridorsList' => BuccalCorridorOption::orderBy('id')->get(),
            // Drives the practice phone country-code dropdown. Must mirror RegisterController's
            // Rule::in($phoneCodes) check, otherwise a hardcoded option could fail validation.
            'phoneCodes' => Country::where('status', 'ACTIVE')
                ->select('phone_code')
                ->distinct()
                ->orderBy('phone_code')
                ->pluck('phone_code')
                ->all(),
        ]);
    }

    public function store(Request $request)
    {
        // Delegate to the registration flow so validation + creation logic
        // lives in exactly one place. Validation errors propagate via
        // ValidationException → back() with errors, so the admin returns to
        // /admin/doctors/create with all inputs preserved.
        app(RegisterController::class)->store($request);

        // Admin-created doctors skip the pending queue and are auto-approved,
        // stamped with the current admin as the reviewer.
        $doctor = Doctor::query()
            ->whereHas('user', fn ($q) => $q->where('email', $request->input('email')))
            ->latest('id')
            ->first();

        if ($doctor && $doctor->approval_status !== 'APPROVED') {
            $doctor->update([
                'approval_status' => 'APPROVED',
                'approved_at' => now(),
                'approved_by_admin_id' => $this->currentAdminId(),
                'rejection_reason' => null,
            ]);
        }

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'Doctor added and approved.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load([
            'practice.country',
            'practice.state',
            'practice.city',
            'practice.zipcode',
            'approverAdmin',
            'shippingAddresses.country',
            'shippingAddresses.state',
            'shippingAddresses.city',
            'shippingAddresses.zipcode',
            'billingAddresses.country',
            'billingAddresses.state',
            'billingAddresses.city',
            'billingAddresses.zipcode',
            'specialties',
            'modalities',
            'treatmentModalities',
            'buccalCorridorOptions',
        ]);

        return view('admin.doctors.show', [
            'doctor' => $doctor,
            'allSpecialties' => Specialty::orderBy('name')->get(['id', 'name']),
            'allModalities' => Modality::orderBy('name')->get(['id', 'name']),
            'allTreatmentModalities' => TreatmentModality::orderBy('name')->get(['id', 'name']),
            'allBuccalCorridors' => BuccalCorridorOption::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateDoctorSectionRequest $request, Doctor $doctor)
    {
        $data = $request->validated();
        $section = $data['section'];
        unset($data['section']);

        $specialties = $data['specialties'] ?? null;
        $modalities = $data['modalities'] ?? null;
        $treatmentModalities = $data['treatment_modalities'] ?? null;
        $buccalCorridors = $data['buccal_corridors'] ?? null;
        unset($data['specialties'], $data['modalities'], $data['treatment_modalities'], $data['buccal_corridors']);

        DB::transaction(function () use ($doctor, $section, $data, $specialties, $modalities, $treatmentModalities, $buccalCorridors) {
            if (! empty($data)) {
                $doctor->fill($data)->save();
            }

            if ($section === 'ortho') {
                $doctor->specialties()->sync($specialties ?? []);
                $doctor->modalities()->sync($modalities ?? []);
                $doctor->treatmentModalities()->sync($treatmentModalities ?? []);
                $doctor->buccalCorridorOptions()->sync($buccalCorridors ?? []);
            }
        });

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('success', 'Doctor updated.');
    }

    public function approve(Doctor $doctor)
    {
        if ($doctor->approval_status === 'APPROVED') {
            return back()->with('error', 'Doctor is already approved.');
        }

        $doctor->update([
            'approval_status' => 'APPROVED',
            'approved_at' => now(),
            'approved_by_admin_id' => $this->currentAdminId(),
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('success', 'Doctor approved.');
    }

    public function reject(RejectDoctorRequest $request, Doctor $doctor)
    {
        if ($doctor->approval_status === 'REJECTED') {
            return back()->with('error', 'Doctor is already rejected.');
        }

        $doctor->update([
            'approval_status' => 'REJECTED',
            'rejection_reason' => $request->validated()['rejection_reason'],
            'approved_at' => null,
            'approved_by_admin_id' => $this->currentAdminId(),
        ]);

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('success', 'Doctor rejected.');
    }

    public function suspend(Doctor $doctor)
    {
        if ($doctor->approval_status !== 'APPROVED') {
            return back()->with('error', 'Only approved doctors can be suspended.');
        }

        $doctor->update(['approval_status' => 'SUSPENDED']);

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('success', 'Doctor suspended.');
    }

    public function reactivate(Doctor $doctor)
    {
        if ($doctor->approval_status !== 'SUSPENDED') {
            return back()->with('error', 'Only suspended doctors can be reactivated.');
        }

        $doctor->update([
            'approval_status' => 'APPROVED',
            'approved_at' => now(),
            'approved_by_admin_id' => $this->currentAdminId(),
        ]);

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('success', 'Doctor reactivated.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'Doctor removed.');
    }

    private function currentAdminId(): ?int
    {
        $user = Auth::user();

        return $user?->admin?->id;
    }
}
