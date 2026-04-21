<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorSectionRequest;
use App\Models\BuccalCorridorOption;
use App\Models\Doctor;
use App\Models\Modality;
use App\Models\Practice;
use App\Models\Specialty;
use App\Models\TreatmentModality;
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

        $doctors = Doctor::query()
            ->with(['practice:id,name'])
            ->when($status, fn ($q) => $q->where('approval_status', $status))
            ->when(
                $request->filled('practice_id'),
                fn ($q) => $q->where('practice_id', $request->integer('practice_id'))
            )
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('first_name', 'like', $like)
                      ->orWhere('last_name', 'like', $like)
                      ->orWhere('doctor_contact_email', 'like', $like)
                      ->orWhere('other_email', 'like', $like)
                      ->orWhere('doctor_cell_phone', 'like', $like);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Doctor::query()
            ->selectRaw('approval_status, COUNT(*) as total')
            ->groupBy('approval_status')
            ->pluck('total', 'approval_status');

        return view('admin.doctors.index', [
            'doctors'        => $doctors,
            'practices'      => Practice::orderBy('name')->get(['id', 'name']),
            'currentStatus'  => $status,
            'statusCounts'   => $statusCounts,
            'totalCount'     => $statusCounts->sum(),
            'pendingCount'   => (int) ($statusCounts['PENDING'] ?? 0),
        ]);
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
            'doctor'                => $doctor,
            'allSpecialties'        => Specialty::orderBy('name')->get(['id', 'name']),
            'allModalities'         => Modality::orderBy('name')->get(['id', 'name']),
            'allTreatmentModalities'=> TreatmentModality::orderBy('name')->get(['id', 'name']),
            'allBuccalCorridors'    => BuccalCorridorOption::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateDoctorSectionRequest $request, Doctor $doctor)
    {
        $data = $request->validated();
        $section = $data['section'];
        unset($data['section']);

        $specialties         = $data['specialties']          ?? null;
        $modalities          = $data['modalities']           ?? null;
        $treatmentModalities = $data['treatment_modalities'] ?? null;
        $buccalCorridors     = $data['buccal_corridors']     ?? null;
        unset($data['specialties'], $data['modalities'], $data['treatment_modalities'], $data['buccal_corridors']);

        DB::transaction(function () use ($doctor, $section, $data, $specialties, $modalities, $treatmentModalities, $buccalCorridors) {
            if (!empty($data)) {
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
            'approval_status'       => 'APPROVED',
            'approved_at'           => now(),
            'approved_by_admin_id'  => $this->currentAdminId(),
            'rejection_reason'      => null,
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
            'approval_status'      => 'REJECTED',
            'rejection_reason'     => $request->validated()['rejection_reason'],
            'approved_at'          => null,
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
            'approval_status'      => 'APPROVED',
            'approved_at'          => now(),
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
