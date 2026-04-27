<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\Scanner;
use App\Http\Requests\Cases\PrescriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CasesController extends Controller
{
    private const STATUSES        = ['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'];
    private const ACTIVE_STATUSES = ['SUBMITTED', 'IN_REVIEW', 'APPROVED'];

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

        $cases = CaseModel::where('doctor_id', $doctor->id)
            ->where('practice_id', $practiceId)
            ->when($activeStatus === 'ACTIVE', fn ($q) => $q->whereIn('status', self::ACTIVE_STATUSES))
            ->when($activeStatus && $activeStatus !== 'ACTIVE', fn ($q) => $q->where('status', $activeStatus))
            ->when($staleOnly, fn ($q) => $q->where('updated_at', '<', now()->subDays(3)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('content.cases.case-list', [
            'cases'        => $cases,
            'activeStatus' => $activeStatus,
            'statuses'     => self::STATUSES,
            'staleOnly'    => $staleOnly,
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
        $practiceId = currentPractice()->id;

        $case = CaseModel::with(['prescription.toothRestrictions', 'media'])
            ->where('doctor_id', $doctor->id)
            ->where('practice_id', $practiceId)
            ->findOrFail($id);

        return view('content.cases.add-case', [
            'id' => $case->id,
            'prescriptionPrefill' => $this->serializePrescription($case->prescription),
            'caseDoctor' => $doctor,
            'scanners' => $this->activeScanners(),
            'caseMedia' => $this->serializeMedia($case->media),
        ]);
    }

    public function submit(int $id)
    {
        $doctor = $this->currentDoctor();
        $practiceId = currentPractice()->id;

        $case = CaseModel::with('prescription.toothRestrictions')
            ->where('doctor_id', $doctor->id)
            ->where('practice_id', $practiceId)
            ->findOrFail($id);

        if (! $case->prescription) {
            return response()->json([
                'ok' => false,
                'errors' => ['prescription' => 'Prescription section must be completed before submit.'],
            ], 422);
        }

        $case->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
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
                'url'        => \Illuminate\Support\Facades\Storage::disk($m->disk)->url($m->path),
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
}
