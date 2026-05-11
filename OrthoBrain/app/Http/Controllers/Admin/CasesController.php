<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CasesController as DoctorCasesController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCaseStatusRequest;
use App\Http\Requests\Cases\AdditionalInformationRequest;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Practice;
use App\Models\Scanner;
use App\Notifications\CaseApprovedNotification;
use App\Notifications\CaseRejectedNotification;
use Illuminate\Http\Request;

class CasesController extends Controller
{
    private const STATUS_OPTIONS = ['SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'];

    private const STATUS_LABELS = [
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
        $statusFilter   = $request->query('status');
        $doctorFilter   = $request->query('doctor_id');
        $patientFilter  = $request->query('patient_id');
        $practiceFilter = $request->query('practice_id');
        $caseIdFilter   = $request->query('case_id');

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
            ])
            ->where('cases.status', '!=', 'DRAFT');

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

        if ($practiceFilter) {
            $query->whereHas('doctor', fn ($q) => $q->where('practice_id', $practiceFilter));
        }

        if ($caseIdFilter && ctype_digit((string) $caseIdFilter)) {
            $query->where('cases.id', (int) $caseIdFilter);
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

        $selectedPractice = $practiceFilter
            ? Practice::select('id', 'name')->find($practiceFilter)
            : null;

        $statusCounts = CaseModel::query()
            ->selectRaw('status, COUNT(*) as total')
            ->where('status', '!=', 'DRAFT')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.cases.index', [
            'cases'           => $cases,
            'selectedDoctor'  => $selectedDoctor,
            'selectedPatient' => $selectedPatient,
            'selectedPractice'=> $selectedPractice,
            'statusOptions'   => self::STATUS_OPTIONS,
            'statusLabels'    => self::STATUS_LABELS,
            'statusFilter'    => $statusFilter,
            'doctorFilter'    => $doctorFilter,
            'patientFilter'   => $patientFilter,
            'practiceFilter'  => $practiceFilter,
            'caseIdFilter'    => $caseIdFilter,
            'statusCounts'    => $statusCounts,
            'totalCount'      => (int) $statusCounts->sum(),
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
                'additionalInfo',
                'shippingAddress',
            ])
            ->findOrFail($id);

        abort_if($case->status === 'DRAFT', 404);

        // Reuse the doctor CasesController's serializers so the prefill
        // shapes match exactly what the Alpine components expect.
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

        $serializeShipping = new \ReflectionMethod($doctorController, 'serializeShipping');
        $serializeShipping->setAccessible(true);
        $shippingAddressPrefill = $serializeShipping->invoke($doctorController, $case->shippingAddress);

        $serializeSavedAddresses = new \ReflectionMethod($doctorController, 'serializeDoctorSavedAddresses');
        $serializeSavedAddresses->setAccessible(true);
        $doctorSavedAddresses = $serializeSavedAddresses->invoke($doctorController, $case->doctor);

        $serializeSubmitOrder = new \ReflectionMethod($doctorController, 'serializeSubmitOrder');
        $serializeSubmitOrder->setAccessible(true);
        $submitOrderPrefill = $serializeSubmitOrder->invoke($doctorController, $case);

        return view('content.cases.add-case', [
            'id' => $case->id,
            'caseStatus' => $case->status,
            'prescriptionPrefill' => $prescriptionPrefill,
            'adminMode' => true,
            'caseRow' => $case,
            'caseDoctor' => $case->doctor,
            'statusOptions' => self::STATUS_OPTIONS,
            'statusLabels' => self::STATUS_LABELS,
            'allowedTransitions' => self::ALLOWED_TRANSITIONS[$case->status] ?? [],
            'scanners' => Scanner::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name']),
            'caseMedia' => $caseMedia,
            'patientPrefill' => $patientPrefill,
            'additionalInfoPrefill' => $case->additionalInfo?->data,
            'shippingAddressPrefill' => $shippingAddressPrefill,
            'doctorSavedAddresses' => $doctorSavedAddresses,
            'impressionsPrefill' => [
                'impressionMethod' => $case->impression_method ? strtolower($case->impression_method) : null,
                'scannerId' => $case->scanner_id,
            ],
            'photographsPrefill' => ['dateOfPhotos' => $case->photos_date?->format('Y-m-d')],
            'xraysPrefill'       => ['dateOfXrays'  => $case->xrays_date?->format('Y-m-d')],
            'submitOrderPrefill' => $submitOrderPrefill,
        ]);
    }

    public function updateStatus(UpdateCaseStatusRequest $request, int $id)
    {
        $payload = $request->validated();

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
        if ($next === 'REJECTED') {
            $updates['rejection_reason'] = $payload['rejection_reason'];
        }
        if ($current === 'REJECTED' && $next === 'IN_REVIEW') {
            $updates['rejection_reason'] = null;
        }

        $case->update($updates);

        // Eager-load to avoid N+1 inside notification pipeline.
        $case->loadMissing('doctor.user');

        // Dispatch case lifecycle notifications to the doctor.
        // Only APPROVED and REJECTED transitions notify the doctor.
        // Other transitions (→IN_REVIEW, →SUBMITTED) are admin-internal.
        // See Docs/case-workflow.md — Notifications.
        if ($next === 'APPROVED') {
            $case->doctor?->user?->notify(
                new CaseApprovedNotification($case)
            );
        } elseif ($next === 'REJECTED') {
            $case->doctor?->user?->notify(
                new CaseRejectedNotification($case, $case->rejection_reason)
            );
        }

        // TODO B-4b: dispatch CaseEditedByAdminNotification from
        // admin section-save endpoints once B-4b is implemented.

        return response()->json([
            'ok' => true,
            'status' => $case->status,
            'submitted_at' => $case->submitted_at?->toIso8601String(),
            'message' => 'Status updated.',
        ]);
    }

    // ─── Section-save endpoints (B-2) ────────────────────────────────────────
    //
    // Admin equivalents of the doctor section-save methods. Key differences:
    //   - No doctor_id / practice_id scope (admin sees all cases).
    //   - No abortIfNotDraft guard (admin can edit SUBMITTED + IN_REVIEW).
    //
    // APPROVED and REJECTED cases are read-only client-side via the widened
    // window.__isReadOnly formula — the server does not re-enforce this because
    // the UI prevents submission. These endpoints mirror the doctor-side
    // contracts so the same case-api.js payload shapes work unchanged.

    // Admin section save: shipping address.
    public function saveShipping(Request $request, int $id)
    {
        $case = CaseModel::findOrFail($id);
        $case->shippingAddress()->updateOrCreate(
            ['case_id' => $case->id],
            [
                'practice_name'    => $request->input('practice'),
                'doctor_name'      => $request->input('doctorName'),
                'street_address_1' => $request->input('streetAddress'),
                'street_address_2' => $request->input('streetAddress2'),
                'zip_id'           => $request->input('zipId'),
                'city_id'          => $request->input('cityId'),
                'state_id'         => $request->input('stateId'),
                'country_id'       => $request->input('countryId'),
            ]
        );
        return response()->json(['ok' => true]);
    }

    // Admin section save: impression method + scanner.
    public function saveImpressions(Request $request, int $id)
    {
        $case = CaseModel::findOrFail($id);
        $case->update([
            'impression_method' => strtoupper($request->input('impressionMethod', '')),
            'scanner_id'        => $request->input('scannerId'),
        ]);
        return response()->json(['ok' => true]);
    }

    // Admin section save: additional information JSON blob.
    // Reuses AdditionalInformationRequest — all rules are nullable so an
    // admin partial-save never fails validation on untouched sections.
    public function saveAdditionalInfo(AdditionalInformationRequest $request, int $id)
    {
        $case = CaseModel::findOrFail($id);
        $case->additionalInfo()->updateOrCreate(
            ['case_id' => $case->id],
            ['data' => $request->validated()]
        );
        return response()->json(['ok' => true]);
    }

    // Admin section save: patient non-identity fields only.
    // first_name, last_name, date_of_birth are intentionally excluded —
    // admin cannot alter patient identity. See Docs/case-workflow.md.
    public function savePatient(Request $request, int $id)
    {
        $case = CaseModel::findOrFail($id);
        if ($case->patient) {
            $case->patient->update([
                'biological_gender'       => $request->input('biologicalGender'),
                'biological_gender_other' => $request->input('biologicalGenderOther'),
                'chart_id'                => $request->input('patientChartId'),
                'chief_complaint'         => $request->input('chiefComplaint'),
                'email'                   => $request->input('email'),
                'phone'                   => $request->input('phone'),
            ]);
        }
        return response()->json(['ok' => true]);
    }

    // Admin section save: submitter initials.
    public function saveSubmitOrder(Request $request, int $id)
    {
        $case = CaseModel::findOrFail($id);
        $initials = trim((string) $request->input('submitterInitials', ''));
        $case->update([
            'submitter_initials' => $initials !== '' ? $initials : null,
        ]);
        return response()->json(['ok' => true]);
    }
}
