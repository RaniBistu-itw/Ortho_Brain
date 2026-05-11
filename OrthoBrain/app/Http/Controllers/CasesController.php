<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardsCaseStatus;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\Scanner;
use App\Models\CaseAdditionalInfo;
use App\Models\CaseShippingAddress;
use App\Http\Requests\Cases\AdditionalInformationRequest;
use App\Services\ImageUploadService;
use App\Support\ActivePractice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CasesController extends Controller
{
    use GuardsCaseStatus;

    private const STATUSES        = ['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'];
    private const ACTIVE_STATUSES = ['SUBMITTED', 'IN_REVIEW', 'APPROVED'];

    public function __construct(private ImageUploadService $images) {}

    public function index(Request $request)
    {
        $doctor = $this->currentDoctor();
        $practiceId = currentPractice()->id;

        $requested = strtoupper((string) $request->query('status', ''));
        $allowed   = [...self::STATUSES, 'ACTIVE'];
        $activeStatus = in_array($requested, $allowed, true) ? $requested : null;

        // Stale flag only makes sense when filtering to DRAFT — matches the
        // dashboard "X drafts stale" alert semantics (updated >3 days ago).
        $staleOnly = $activeStatus === 'DRAFT' && $request->boolean('stale');

        $searchTerm = trim((string) $request->query('search', ''));

        $sortable = [
            'id'           => 'cases.id',
            'patient'      => 'patients.last_name',
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

        $query = CaseModel::with('patient:id,first_name,last_name')
            ->where('cases.doctor_id', $doctor->id)
            ->where('cases.practice_id', $practiceId)
            ->when($activeStatus === 'ACTIVE', fn ($q) => $q->whereIn('cases.status', self::ACTIVE_STATUSES))
            ->when($activeStatus && $activeStatus !== 'ACTIVE', fn ($q) => $q->where('cases.status', $activeStatus))
            ->when($staleOnly, fn ($q) => $q->where('cases.updated_at', '<', now()->subDays(3)))
            ->when($searchTerm !== '', function ($q) use ($searchTerm) {
                $like = "%{$searchTerm}%";
                $q->where(function ($w) use ($searchTerm, $like) {
                    if (ctype_digit($searchTerm)) {
                        $w->orWhere('cases.id', (int) $searchTerm);
                    }
                    $w->orWhereHas('patient', function ($p) use ($like) {
                        $p->where('first_name', 'like', $like)
                          ->orWhere('last_name', 'like', $like)
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
                    });
                });
            })
            ->select(['cases.id', 'cases.case_code', 'cases.status', 'cases.created_at', 'cases.submitted_at', 'cases.doctor_id', 'cases.practice_id', 'cases.patient_id']);

        if ($sortKey === 'patient') {
            $query->leftJoin('patients', 'patients.id', '=', 'cases.patient_id')
                  ->orderBy($sortCol, $dir);
        } else {
            $query->orderBy($sortCol, $dir);
        }

        $cases = $query->paginate(20)->withQueryString();

        return view('content.cases.case-list', [
            'cases'        => $cases,
            'activeStatus' => $activeStatus,
            'statuses'     => self::STATUSES,
            'staleOnly'    => $staleOnly,
            'searchTerm'   => $searchTerm,
        ]);
    }

    public function create()
    {
        $doctor = $this->currentDoctor();
        $doctor->loadMissing('practice:id,name');

        return view('content.cases.add-case', [
            'id' => null,
            'prescriptionPrefill' => null,
            'caseDoctor' => $doctor,
            'scanners' => $this->activeScanners(),
            'doctorSavedAddresses' => $this->serializeDoctorSavedAddresses($doctor),
        ]);
    }

    public function store(Request $request)
    {
        $doctor = $this->currentDoctor();
        $practiceId = currentPractice()->id;

        $case = CaseModel::create([
            'doctor_id' => $doctor->id,
            'practice_id' => $practiceId,
            'status' => 'DRAFT',
        ]);

        return response()->json([
            'ok' => true,
            'id' => $case->id,
            'redirect' => route('doctor.cases.edit', $case->id),
        ]);
    }

    public function edit(int $id)
    {
        $doctor = $this->currentDoctor();
        $doctor->loadMissing('practice:id,name');

        $case = CaseModel::with(['prescription.toothRestrictions', 'media', 'patient', 'additionalInfo', 'shippingAddress'])
            ->where('doctor_id', $doctor->id)
            ->findOrFail($id);

        // Align the active practice to the case's practice so the topbar,
        // form, and case-list scope all match what the doctor is editing.
        // ActivePractice::set() returns false if the doctor doesn't have
        // an APPROVED link to that practice — in that case they have no
        // access to this case anymore (e.g., they LEFT the practice).
        if ((int) $case->practice_id !== (int) (currentPractice()?->id)) {
            if (! ActivePractice::set((int) $case->practice_id)) {
                abort(404);
            }
        }

        return view('content.cases.add-case', [
            'id' => $case->id,
            'caseStatus' => $case->status,
            'prescriptionPrefill' => $this->serializePrescription($case->prescription),
            'caseDoctor' => $doctor,
            'scanners' => $this->activeScanners(),
            'caseMedia' => $this->serializeMedia($case->media),
            'patientPrefill' => $this->serializePatient($case->patient),
            'additionalInfoPrefill' => $case->additionalInfo?->data,
            'shippingAddressPrefill' => $this->serializeShipping($case->shippingAddress),
            'doctorSavedAddresses' => $this->serializeDoctorSavedAddresses($doctor),
            'impressionsPrefill' => [
                'impressionMethod' => $case->impression_method ? strtolower($case->impression_method) : null,
                'scannerId' => $case->scanner_id,
            ],
            'photographsPrefill' => ['dateOfPhotos' => $case->photos_date?->format('Y-m-d')],
            'xraysPrefill'       => ['dateOfXrays'  => $case->xrays_date?->format('Y-m-d')],
            'submitOrderPrefill' => $this->serializeSubmitOrder($case),
        ]);
    }

    public function saveShipping(Request $request, int $id)
    {
        $case = CaseModel::where('doctor_id', $this->currentDoctor()->id)->findOrFail($id);
        $this->abortIfNotDraft($case);

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

    public function saveImpressions(Request $request, int $id)
    {
        $case = CaseModel::where('doctor_id', $this->currentDoctor()->id)->findOrFail($id);
        $this->abortIfNotDraft($case);

        $case->update([
            'impression_method' => strtoupper($request->input('impressionMethod')),
            'scanner_id'        => $request->input('scannerId'),
        ]);

        return response()->json(['ok' => true]);
    }

    public function saveAdditionalInfo(AdditionalInformationRequest $request, int $id)
    {
        $case = CaseModel::where('doctor_id', $this->currentDoctor()->id)->findOrFail($id);
        $this->abortIfNotDraft($case);

        $case->additionalInfo()->updateOrCreate(
            ['case_id' => $case->id],
            ['data' => $request->validated()]
        );

        return response()->json(['ok' => true]);
    }

    public function saveSubmitOrder(Request $request, int $id)
    {
        $case = CaseModel::where('doctor_id', $this->currentDoctor()->id)->findOrFail($id);

        // Lenient draft validation — accept empty or partial input.
        // The submit endpoint enforces the strict 2-5 letter regex.
        $initials = trim((string) $request->input('submitterInitials', ''));
        $case->update([
            'submitter_initials' => $initials !== '' ? $initials : null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function savePhotographsDate(Request $request, int $id)
    {
        $data = $request->validate([
            'dateOfPhotos' => 'nullable|date|before_or_equal:today',
        ]);

        $case = CaseModel::where('doctor_id', $this->currentDoctor()->id)->findOrFail($id);
        $case->update(['photos_date' => $data['dateOfPhotos'] ?? null]);

        return response()->json(['ok' => true]);
    }

    public function saveXraysDate(Request $request, int $id)
    {
        $data = $request->validate([
            'dateOfXrays' => 'nullable|date|before_or_equal:today',
        ]);

        $case = CaseModel::where('doctor_id', $this->currentDoctor()->id)->findOrFail($id);
        $case->update(['xrays_date' => $data['dateOfXrays'] ?? null]);

        return response()->json(['ok' => true]);
    }

    public function submit(Request $request, int $id)
    {
        $doctor = $this->currentDoctor();

        $case = CaseModel::with('prescription.toothRestrictions')
            ->where('doctor_id', $doctor->id)
            ->findOrFail($id);

        if ((int) $case->practice_id !== (int) (currentPractice()?->id)) {
            if (! ActivePractice::set((int) $case->practice_id)) {
                abort(404);
            }
        }

        if ($case->status !== 'DRAFT') {
            return response()->json([
                'ok'       => true,
                'redirect' => route('doctor.cases.index'),
                'message'  => 'Case already submitted.',
            ]);
        }

        if (! $case->prescription) {
            return response()->json([
                'ok' => false,
                'errors' => ['prescription' => 'Prescription section must be completed before submit.'],
            ], 422);
        }

        if (! $case->patient_id) {
            return response()->json([
                'ok'      => false,
                'error'   => 'patient_required',
                'message' => 'Cannot submit case: no patient linked. Save patient information first.',
            ], 422);
        }

        // Submitter initials are an attestation: 2-5 letters, mixed case
        // explicitly allowed (help text says "ABcd" is valid). Stored as
        // typed — uppercasing here would silently rewrite the user's input
        // and violate the stated UI contract.
        $initials = trim((string) $request->input('submitter_initials', ''));
        if (! preg_match('/^[A-Za-z]{2,5}$/', $initials)) {
            return response()->json([
                'ok'      => false,
                'error'   => 'initials_required',
                'message' => 'Submitter initials are required (2-5 letters).',
            ], 422);
        }

        // Refresh in case the saveDraft() flush from add-case-submit.js
        // wrote xrays_date / photos_date microseconds before this request landed.
        $case->refresh();
        if (! $case->xrays_date) {
            return response()->json([
                'ok'      => false,
                'error'   => 'xrays_date_required',
                'message' => 'Date of X-Rays is required before submit.',
            ], 422);
        }

        // photos_date required before submission — same rule as xrays_date.
        // Ensures the case has dated media records before it reaches the admin.
        // See Docs/case-workflow.md.
        if (! $case->photos_date) {
            return response()->json([
                'ok'      => false,
                'error'   => 'photos_date_required',
                'message' => 'Date of Photos is required before submit.',
            ], 422);
        }

        $case->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
            'submitter_initials' => $initials,
        ]);

        return response()->json([
            'ok' => true,
            'redirect' => route('doctor.cases.index'),
            'message' => 'Case submitted successfully.',
        ]);
    }

    private function currentDoctor(): Doctor
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();

        if (! $doctor) {
            abort(403, 'Doctor profile not found for this user.');
        }

        return $doctor;
    }

    private function activeScanners()
    {
        return Scanner::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Shape a Patient for the case wizard's hydration.
     */
    private function serializePatient(?\App\Models\Patient $patient): ?array
    {
        if (! $patient) {
            return null;
        }
        return [
            'id'             => $patient->id,
            'firstName'      => $patient->first_name,
            'lastName'       => $patient->last_name,
            'dob'            => $patient->date_of_birth?->toDateString(),
            'gender'         => $patient->biological_gender,
            'genderOther'    => $patient->biological_gender_other,
            'chartId'        => $patient->chart_id,
            'email'          => $patient->email,
            'phone'          => $patient->phone,
            'chiefComplaint' => $patient->chief_complaint,
        ];
    }

    /**
     * Shape the CaseMedia rows the way photographs.js / xrays.js expect them
     * for hydration (one entry per filled tile, keyed by section + tile_id).
     */
    private function serializeMedia($mediaCollection): array
    {
        if (! $mediaCollection) {
            return [];
        }
        return $mediaCollection->map(function ($m) {
            return [
                'section'    => $m->section,
                'tileId'     => $m->tile_id,
                'url'        => $this->images->url($m->path),
                'mime'       => $m->mime_type,
                'size'       => $m->size_bytes,
                'cropParams' => $m->crop_params,
            ];
        })->values()->all();
    }

    private function serializePrescription(?Prescription $prescription): ?array
    {
        if (! $prescription) {
            return null;
        }

        $movement = $prescription->toothRestrictions
            ->where('restriction_type', 'MOVEMENT')
            ->pluck('tooth_code')
            ->values()
            ->all();

        $attachment = $prescription->toothRestrictions
            ->where('restriction_type', 'ATTACHMENT')
            ->pluck('tooth_code')
            ->values()
            ->all();

        return [
            'arches' => $this->lowerOrNull($prescription->arches),
            'iprProtocol' => [
                'enabled' => (bool) $prescription->ipr_enabled,
                'value' => $this->iprValueToUi($prescription->ipr_value),
            ],
            'attachments' => [
                'enabled' => (bool) $prescription->attachments_enabled,
                'value' => $this->attachmentsValueToUi($prescription->attachments_value),
                'specificStepNumber' => $prescription->attachments_specific_step,
            ],
            'elastics' => [
                'enabled' => (bool) $prescription->elastics_enabled,
                'value' => $this->lowerOrNull($prescription->elastics_value),
            ],
            'extractions' => [
                'enabled' => (bool) $prescription->extractions_enabled,
                'value' => $this->lowerOrNull($prescription->extractions_value),
            ],
            'toothMovementRestrictions' => [
                'mode' => strtolower($prescription->tooth_movement_mode),
                'selectedTeeth' => $movement,
            ],
            'attachmentRestrictions' => [
                'mode' => strtolower($prescription->attachment_restrictions_mode),
                'selectedTeeth' => $attachment,
            ],
            'additionalComments' => $prescription->additional_comments ?? '',
            'futureRestorativeWork' => [
                'hasWork' => $this->lowerOrNull($prescription->future_restorative_work_has_work),
                'explanation' => $prescription->future_restorative_work_explanation ?? '',
            ],
        ];
    }

    private function lowerOrNull(?string $value): ?string
    {
        return $value === null ? null : strtolower($value);
    }

    private function iprValueToUi(?string $value): ?string
    {
        return match ($value) {
            'DEFER' => 'defer',
            'NO_IPR' => 'no-ipr',
            'OTHER' => 'other',
            default => null,
        };
    }

    private function attachmentsValueToUi(?string $value): ?string
    {
        return match ($value) {
            'STEP_1' => 'step-1',
            'SPECIFIC_STEP' => 'specific-step',
            default => null,
        };
    }

    private function serializeSubmitOrder(?CaseModel $case): ?array
    {
        if (! $case) return null;
        return [
            'submitterInitials' => $case->submitter_initials,
        ];
    }

    private function serializeShipping(?CaseShippingAddress $addr): ?array
    {
        if (! $addr) return null;
        // loadMissing() ensures sub-relations are loaded for text resolution.
        // The caller loads shippingAddress but not its sub-relations.
        // No-op if already eager-loaded.
        $addr->loadMissing(['zip', 'city', 'state', 'country']);
        return [
            'practice'       => $addr->practice_name,
            'doctorName'     => $addr->doctor_name,
            'streetAddress'  => $addr->street_address_1,
            'streetAddress2' => $addr->street_address_2,
            'zipId'          => $addr->zip_id,
            'zipCode'        => $addr->zip?->code,
            'cityId'         => $addr->city_id,
            'city'           => $addr->city?->name,
            'stateId'        => $addr->state_id,
            'state'          => $addr->state?->name,
            'countryId'      => $addr->country_id,
            // country holds the ISO code (e.g. "US"), not the display name —
            // matches the ZipcodeSearchController cascade payload and the
            // <select> options keyed by Country.country_code.
            'country'        => $addr->country?->country_code,
        ];
    }

    private function serializeDoctorSavedAddresses(Doctor $doctor): array
    {
        return $doctor->shippingAddresses()
            ->with(['zipcode', 'city', 'state', 'country'])
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get()
            ->map(fn (\App\Models\DoctorAddress $a) => [
                'id'             => (string) $a->id,
                'isDefault'      => (bool) $a->is_default,
                'label'          => $this->buildAddressLabel($a),
                'streetAddress'  => $a->street_address_1,
                'streetAddress2' => $a->street_address_2,
                'zipId'          => $a->zip_id,
                'zipCode'        => $a->zipcode?->code,
                'cityId'         => $a->city_id,
                'city'           => $a->city?->name,
                'stateId'        => $a->state_id,
                'state'          => $a->state?->name,
                'countryId'      => $a->country_id,
                // ISO code, not display name — matches serializeShipping.
                'country'        => $a->country?->country_code,
            ])
            ->values()
            ->all();
    }

    private function buildAddressLabel(\App\Models\DoctorAddress $a): string
    {
        $parts = array_filter([
            $a->street_address_1,
            $a->city?->name,
            $a->state?->name,
            $a->zipcode?->code,
        ], fn ($v) => filled($v));
        $base = $parts ? implode(', ', $parts) : ('Address #' . $a->id);
        return $a->is_default ? $base . ' (Default)' : $base;
    }
}
