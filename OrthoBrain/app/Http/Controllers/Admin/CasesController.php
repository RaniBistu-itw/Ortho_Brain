<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CasesController as DoctorCasesController;
use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Scanner;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    private const STATUS_OPTIONS = ['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'];

    private const STATUS_LABELS = [
        'DRAFT'     => 'Draft',
        'SUBMITTED' => 'Submitted',
        'IN_REVIEW' => 'In Review',
        'APPROVED'  => 'Approved',
        'REJECTED'  => 'Unapproved',
    ];

    // Allowed status transitions. Anything not in this map is rejected by
    // updateStatus. Self-transitions (X → X) are treated as no-ops upstream.
    // APPROVED / REJECTED can be reopened by moving back to IN_REVIEW; from
    // there an admin can re-decide.
    private const ALLOWED_TRANSITIONS = [
        'DRAFT'     => ['SUBMITTED'],
        'SUBMITTED' => ['IN_REVIEW'],
        'IN_REVIEW' => ['APPROVED', 'REJECTED'],
        'APPROVED'  => ['IN_REVIEW'],
        'REJECTED'  => ['IN_REVIEW'],
    ];

    public function index(Request $request)
    {
        $statusFilter  = $request->query('status');
        $doctorFilter  = $request->query('doctor_id');
        $patientFilter = $request->query('patient_id');

        $sortable = [
            'id'           => 'cases.id',
            'patient'      => 'patients.last_name',
            'doctor'       => 'doctors.last_name',
            'practice'     => 'practices.name',
            'created_at'   => 'cases.created_at',
            'submitted_at' => 'cases.submitted_at',
        ];
        $order   = $request->query('order') === 'oldest' ? 'oldest' : 'newest';
        $sortKey = $request->get('sort');
        if ($sortKey && isset($sortable[$sortKey])) {
            $sortCol = $sortable[$sortKey];
            $dir     = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        } else {
            $sortKey = null;
            $sortCol = 'cases.created_at';
            $dir     = $order === 'oldest' ? 'asc' : 'desc';
        }

        $query = CaseModel::query()
            ->with([
                'doctor:id,first_name,last_name,practice_id',
                'doctor.practice:id,name',
                'patient:id,first_name,last_name',
            ])
            ->select([
                'cases.id',
                'cases.case_code',
                'cases.doctor_id',
                'cases.patient_id',
                'cases.status',
                'cases.created_at',
                'cases.submitted_at',
            ]);

        if (in_array($sortKey, ['doctor', 'practice'], true)) {
            $query->leftJoin('doctors', 'doctors.id', '=', 'cases.doctor_id')
                  ->leftJoin('practices', 'practices.id', '=', 'doctors.practice_id')
                  ->orderBy($sortCol, $dir);
        } elseif ($sortKey === 'patient') {
            $query->leftJoin('patients', 'patients.id', '=', 'cases.patient_id')
                  ->orderBy($sortCol, $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        if ($statusFilter && in_array($statusFilter, self::STATUS_OPTIONS, true)) {
            $query->where('cases.status', $statusFilter);
        }

        if ($doctorFilter) {
            $query->where('cases.doctor_id', $doctorFilter);
        }

        if ($patientFilter) {
            $query->where('cases.patient_id', $patientFilter);
        }

        $cases = $query->paginate(20)->withQueryString();

        $selectedDoctor = $doctorFilter
            ? Doctor::with('practice:id,name')
                ->select('id', 'first_name', 'last_name', 'practice_id')
                ->find($doctorFilter)
            : null;

        $selectedPatient = $patientFilter
            ? Patient::select('id', 'first_name', 'last_name', 'chart_id')
                ->find($patientFilter)
            : null;

        $statusCounts = CaseModel::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.cases.index', [
            'cases' => $cases,
            'selectedDoctor' => $selectedDoctor,
            'selectedPatient' => $selectedPatient,
            'statusOptions' => self::STATUS_OPTIONS,
            'statusLabels' => self::STATUS_LABELS,
            'statusFilter' => $statusFilter,
            'doctorFilter' => $doctorFilter,
            'patientFilter' => $patientFilter,
            'statusCounts' => $statusCounts,
            'totalCount'   => (int) $statusCounts->sum(),
        ]);
    }

    public function edit(int $id)
    {
        $case = CaseModel::with([
                'doctor:id,first_name,last_name,practice_id',
                'doctor.practice:id,name',
                'prescription.toothRestrictions',
                'media',
                'patient',
            ])
            ->findOrFail($id);

        // Reuse the doctor CasesController's serializers so the prefill
        // shapes (prescription / media / patient) match exactly what the
        // Alpine components expect.
        $doctorController = app(DoctorCasesController::class);

        $reflection = new \ReflectionMethod($doctorController, 'serializePrescription');
        $reflection->setAccessible(true);
        $prescriptionPrefill = $reflection->invoke($doctorController, $case->prescription);

        $serializeMedia = new \ReflectionMethod($doctorController, 'serializeMedia');
        $serializeMedia->setAccessible(true);
        $caseMedia = $serializeMedia->invoke($doctorController, $case->media);

        $serializePatient = new \ReflectionMethod($doctorController, 'serializePatient');
        $serializePatient->setAccessible(true);
        $patientPrefill = $serializePatient->invoke($doctorController, $case->patient);

        return view('content.cases.add-case', [
            'id' => $case->id,
            'prescriptionPrefill' => $prescriptionPrefill,
            'adminMode' => true,
            'caseRow' => $case,
            'caseDoctor' => $case->doctor,
            'statusOptions' => self::STATUS_OPTIONS,
            'statusLabels' => self::STATUS_LABELS,
            'scanners' => Scanner::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name']),
            'caseMedia' => $caseMedia,
            'patientPrefill' => $patientPrefill,
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $payload = $request->validate([
            'status' => 'required|in:' . implode(',', self::STATUS_OPTIONS),
        ]);

        $case = CaseModel::findOrFail($id);
        $current = $case->status;
        $next = $payload['status'];

        // No-op: setting the same status returns the current state without
        // touching submitted_at. Idempotent for clients that re-send.
        if ($next === $current) {
            return response()->json([
                'ok' => true,
                'status' => $case->status,
                'submitted_at' => $case->submitted_at?->toIso8601String(),
                'message' => 'Status unchanged.',
            ]);
        }

        $allowedNext = self::ALLOWED_TRANSITIONS[$current] ?? [];
        if (! in_array($next, $allowedNext, true)) {
            $currentLabel = self::STATUS_LABELS[$current] ?? $current;
            $nextLabel    = self::STATUS_LABELS[$next] ?? $next;
            return response()->json([
                'ok' => false,
                'error' => "Cannot transition case from {$currentLabel} to {$nextLabel}.",
                'current' => $current,
                'allowed_next' => $allowedNext,
            ], 422);
        }

        $updates = ['status' => $next];
        if ($next === 'SUBMITTED' && ! $case->submitted_at) {
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
