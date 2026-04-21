<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CasesController as DoctorCasesController;
use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Scanner;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    private const STATUS_OPTIONS = ['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'];

    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $doctorFilter = $request->query('doctor_id');

        $query = CaseModel::with(['doctor:id,first_name,last_name,practice_id', 'doctor.practice:id,name'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, self::STATUS_OPTIONS, true)) {
            $query->where('status', $statusFilter);
        }

        if ($doctorFilter) {
            $query->where('doctor_id', $doctorFilter);
        }

        $cases = $query->paginate(20)->withQueryString();

        $doctors = Doctor::with('practice:id,name')
            ->select('id', 'first_name', 'last_name', 'practice_id')
            ->orderBy('last_name')
            ->get();

        return view('admin.cases.index', [
            'cases' => $cases,
            'doctors' => $doctors,
            'statusOptions' => self::STATUS_OPTIONS,
            'statusFilter' => $statusFilter,
            'doctorFilter' => $doctorFilter,
        ]);
    }

    public function edit(int $id)
    {
        $case = CaseModel::with(['doctor:id,first_name,last_name,practice_id', 'doctor.practice:id,name', 'prescription.toothRestrictions'])
            ->findOrFail($id);

        // Reuse the doctor CasesController's serializer so the Prescription
        // prefill shape matches exactly what the Alpine component expects.
        $doctorController = new DoctorCasesController();
        $reflection = new \ReflectionMethod($doctorController, 'serializePrescription');
        $reflection->setAccessible(true);
        $prescriptionPrefill = $reflection->invoke($doctorController, $case->prescription);

        return view('content.cases.add-case', [
            'id' => $case->id,
            'prescriptionPrefill' => $prescriptionPrefill,
            'adminMode' => true,
            'caseRow' => $case,
            'caseDoctor' => $case->doctor,
            'statusOptions' => self::STATUS_OPTIONS,
            'scanners' => Scanner::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $payload = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUS_OPTIONS),
        ]);

        $case = CaseModel::findOrFail($id);

        $updates = ['status' => $payload['status']];
        if ($payload['status'] === 'SUBMITTED' && ! $case->submitted_at) {
            $updates['submitted_at'] = now();
        }

        $case->update($updates);

        return response()->json([
            'ok' => true,
            'status' => $case->status,
            'submitted_at' => $case->submitted_at?->toIso8601String(),
            'message' => 'Status updated.',
        ]);
    }
}
