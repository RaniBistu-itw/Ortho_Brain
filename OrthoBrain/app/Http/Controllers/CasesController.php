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
    public function index()
    {
        $doctor = $this->currentDoctor();

        $cases = CaseModel::where('doctor_id', $doctor->id)
            ->latest()
            ->paginate(20);

        return view('content.cases.case-list', compact('cases'));
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

        $case = CaseModel::create([
            'doctor_id' => $doctor->id,
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

        $case = CaseModel::with('prescription.toothRestrictions')
            ->where('doctor_id', $doctor->id)
            ->findOrFail($id);

        return view('content.cases.add-case', [
            'id' => $case->id,
            'prescriptionPrefill' => $this->serializePrescription($case->prescription),
            'caseDoctor' => $doctor,
            'scanners' => $this->activeScanners(),
        ]);
    }

    public function submit(int $id)
    {
        $doctor = $this->currentDoctor();

        $case = CaseModel::with('prescription.toothRestrictions')
            ->where('doctor_id', $doctor->id)
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
