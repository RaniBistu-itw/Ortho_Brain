<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardsCaseStatus;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionToothRestriction;
use App\Notifications\CaseEditedByAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    use GuardsCaseStatus;

    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        if ($user->role === 'ADMIN') {
            $case = CaseModel::findOrFail($id);
        } else {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (! $doctor) {
                abort(403, 'Doctor profile not found.');
            }
            $case = CaseModel::where('doctor_id', $doctor->id)
                ->where('practice_id', currentPractice()->id)
                ->findOrFail($id);
            $this->abortIfNotDraft($case);
        }

        $payload = $request->validate([
            'arches'                                   => 'nullable|in:both,maxillary,mandibular',
            'iprProtocol.enabled'                      => 'nullable|boolean',
            'iprProtocol.value'                        => 'nullable|in:defer,no-ipr,other',
            'attachments.enabled'                      => 'nullable|boolean',
            'attachments.value'                        => 'nullable|in:step-1,specific-step',
            'attachments.specificStepNumber'           => 'nullable|integer|min:1|max:255',
            'elastics.enabled'                         => 'nullable|boolean',
            'elastics.value'                           => 'nullable|in:yes,no',
            'extractions.enabled'                      => 'nullable|boolean',
            'extractions.value'                        => 'nullable|in:yes,no',
            'toothMovementRestrictions.mode'           => 'nullable|in:none,select',
            'toothMovementRestrictions.selectedTeeth'  => 'nullable|array',
            'toothMovementRestrictions.selectedTeeth.*'=> 'string|max:4',
            'attachmentRestrictions.mode'              => 'nullable|in:none,select',
            'attachmentRestrictions.selectedTeeth'     => 'nullable|array',
            'attachmentRestrictions.selectedTeeth.*'   => 'string|max:4',
            'additionalComments'                       => 'nullable|string|max:5000',
            'futureRestorativeWork.hasWork'            => 'nullable|in:yes,no',
            'futureRestorativeWork.explanation'        => 'nullable|string|max:5000',
        ]);

        $savedAt = DB::transaction(function () use ($case, $payload) {
            $prescription = Prescription::updateOrCreate(
                ['case_id' => $case->id],
                $this->mapToColumns($payload)
            );

            PrescriptionToothRestriction::where('prescription_id', $prescription->id)
                ->whereIn('restriction_type', ['MOVEMENT', 'ATTACHMENT'])
                ->delete();

            $this->insertToothRestrictions(
                $prescription->id,
                'MOVEMENT',
                data_get($payload, 'toothMovementRestrictions.selectedTeeth', [])
            );
            $this->insertToothRestrictions(
                $prescription->id,
                'ATTACHMENT',
                data_get($payload, 'attachmentRestrictions.selectedTeeth', [])
            );

            $case->touch();

            return now()->toIso8601String();
        });

        // Notify doctor when admin edits prescription.
        // Guarded to admin-only — doctor edits do not self-notify.
        if ($user->role === 'ADMIN') {
            $case->loadMissing('doctor.user');
            $case->doctor?->user?->notify(
                new CaseEditedByAdminNotification($case, 'prescription')
            );
        }

        return response()->json([
            'ok' => true,
            'savedAt' => $savedAt,
        ]);
    }

    private function mapToColumns(array $payload): array
    {
        $iprEnumMap        = ['defer' => 'DEFER', 'no-ipr' => 'NO_IPR', 'other' => 'OTHER'];
        $attachmentEnumMap = ['step-1' => 'STEP_1', 'specific-step' => 'SPECIFIC_STEP'];

        return [
            'arches' => isset($payload['arches']) ? strtoupper($payload['arches']) : null,

            'ipr_enabled' => data_get($payload, 'iprProtocol.enabled', true),
            'ipr_value' => $iprEnumMap[data_get($payload, 'iprProtocol.value')] ?? null,

            'attachments_enabled' => data_get($payload, 'attachments.enabled', true),
            'attachments_value' => $attachmentEnumMap[data_get($payload, 'attachments.value')] ?? null,
            'attachments_specific_step' => data_get($payload, 'attachments.specificStepNumber'),

            'elastics_enabled' => data_get($payload, 'elastics.enabled', true),
            'elastics_value' => $this->upperOrNull(data_get($payload, 'elastics.value')),

            'extractions_enabled' => data_get($payload, 'extractions.enabled', true),
            'extractions_value' => $this->upperOrNull(data_get($payload, 'extractions.value')),

            'tooth_movement_mode' => $this->upperOrDefault(data_get($payload, 'toothMovementRestrictions.mode'), 'NONE'),
            'attachment_restrictions_mode' => $this->upperOrDefault(data_get($payload, 'attachmentRestrictions.mode'), 'NONE'),

            'additional_comments' => data_get($payload, 'additionalComments'),

            'future_restorative_work_has_work' => $this->upperOrNull(data_get($payload, 'futureRestorativeWork.hasWork')),
            'future_restorative_work_explanation' => data_get($payload, 'futureRestorativeWork.explanation'),
        ];
    }

    private function insertToothRestrictions(int $prescriptionId, string $type, array $teeth): void
    {
        $teeth = array_values(array_unique(array_filter($teeth, fn($t) => $t !== null && $t !== '')));
        if (empty($teeth)) {
            return;
        }

        $now = now();
        $rows = array_map(fn($code) => [
            'prescription_id' => $prescriptionId,
            'restriction_type' => $type,
            'tooth_code' => (string) $code,
            'created_at' => $now,
            'updated_at' => $now,
        ], $teeth);

        PrescriptionToothRestriction::insert($rows);
    }

    private function upperOrNull(?string $value): ?string
    {
        return $value === null ? null : strtoupper($value);
    }

    private function upperOrDefault(?string $value, string $default): string
    {
        return $value === null ? $default : strtoupper($value);
    }
}
