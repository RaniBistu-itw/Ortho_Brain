<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardsCaseStatus;
use App\Http\Requests\Cases\PatientInformationRequest;
use App\Models\CaseModel;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Real persistence layer for the case wizard's Patient Information section.
 *
 * All operations are scoped to (doctor, practice). Both routes live inside
 * the dev/active.practice middleware group so currentPractice() is non-null.
 */
class PatientController extends Controller
{
    use GuardsCaseStatus;

    private const SEARCH_LIMIT = 8;

    /**
     * Autocomplete for the Patient Information search field. Matches across
     * first/last name, email, phone (digits-only), and chart_id. Returns up
     * to 8 rows.
     */
    public function search(Request $request): JsonResponse
    {
        $doctor = $this->currentDoctor();
        $practiceId = currentPractice()->id;

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $qLower  = mb_strtolower($q);
        $qDigits = preg_replace('/[^0-9]/', '', $q);

        $query = Patient::query()
            ->where('doctor_id', $doctor->id)
            ->where('practice_id', $practiceId)
            ->where(function ($w) use ($qLower, $qDigits) {
                $w->whereRaw('LOWER(first_name) LIKE ?',           ['%' . $qLower . '%'])
                  ->orWhereRaw('LOWER(last_name)  LIKE ?',         ['%' . $qLower . '%'])
                  ->orWhereRaw('LOWER(CONCAT(first_name," ",last_name)) LIKE ?', ['%' . $qLower . '%'])
                  ->orWhereRaw('LOWER(email)      LIKE ?',         ['%' . $qLower . '%'])
                  ->orWhereRaw('LOWER(chart_id)   LIKE ?',         ['%' . $qLower . '%']);
                if ($qDigits !== '' && mb_strlen($qDigits) >= 3) {
                    // MariaDB lacks a digits-only function; match the digits
                    // against the raw phone value (formatting tolerated by
                    // wrapping % on both sides).
                    $w->orWhere('phone', 'like', '%' . $qDigits . '%');
                }
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(self::SEARCH_LIMIT);

        return response()->json($query->get()->map(fn ($p) => $this->serialize($p)));
    }

    /**
     * Upsert this case's patient record. Creates a new patient if the case
     * has none yet, updates the existing one otherwise. Always sets
     * cases.patient_id to point to the resulting row.
     *
     * Body validates against PatientInformationRequest (the dead-code form
     * request that's been waiting for a real call site since the schema
     * landed).
     */
    public function upsert(PatientInformationRequest $request, int $caseId): JsonResponse
    {
        $doctor = $this->currentDoctor();
        $practiceId = currentPractice()->id;

        $case = CaseModel::where('doctor_id', $doctor->id)
            ->where('practice_id', $practiceId)
            ->findOrFail($caseId);
        $this->abortIfNotDraft($case);

        $payload = $request->validated();

        $patientData = [
            'doctor_id'                => $doctor->id,
            'practice_id'              => $practiceId,
            'first_name'               => $payload['firstName'],
            'last_name'                => $payload['lastName'],
            'date_of_birth'            => $payload['dateOfBirth'],
            'biological_gender'        => $payload['biologicalGender'] ?? null,
            'biological_gender_other'  => $payload['biologicalGenderOther'] ?? null,
            'chart_id'                 => $payload['patientChartId'] ?? null,
            'chief_complaint'          => $payload['chiefComplaint'] ?? null,
            'email'                    => $payload['email'] ?? null,
            'phone'                    => $payload['phone'] ?? null,
        ];

        $patient = DB::transaction(function () use ($case, $patientData, $request) {
            // If the client sent an explicit selectedPatientId (from picking
            // a search match), update that row instead of the case's current
            // patient — supports "I picked the wrong patient earlier; load
            // a different one and save".
            $explicitId = $request->integer('selectedPatientId');
            $existing = null;

            if ($explicitId) {
                $existing = Patient::where('id', $explicitId)
                    ->where('doctor_id', $patientData['doctor_id'])
                    ->where('practice_id', $patientData['practice_id'])
                    ->first();
            } elseif ($case->patient_id) {
                $existing = Patient::find($case->patient_id);
            }

            if ($existing) {
                // Preserve existing chart_id — never overwrite with null
                // on update. Doctor may have a previously auto-generated
                // or manually entered chart_id that must not be lost.
                if (empty($patientData['chart_id']) && $existing->chart_id) {
                    unset($patientData['chart_id']);
                }
                $existing->update($patientData);
                $patient = $existing;
            } else {
                $patient = Patient::create($patientData);
                // Auto-generate chart_id if doctor did not provide one.
                // Format: PT-{doctor_id}-{practice_id}-{patient_id}
                // Only on creation — never overwrite an existing chart_id.
                if (empty($patientData['chart_id'])) {
                    $generated = 'PT-' . $patientData['doctor_id']
                        . '-' . $patientData['practice_id']
                        . '-' . $patient->id;
                    DB::table('patients')->where('id', $patient->id)
                        ->update(['chart_id' => $generated]);
                    $patient->chart_id = $generated;
                }
            }

            if ($case->patient_id !== $patient->id) {
                $case->patient_id = $patient->id;
                $case->save();
            } else {
                $case->touch();
            }

            return $patient;
        });

        return response()->json([
            'ok'      => true,
            'patient' => $this->serialize($patient),
            'caseId'  => $case->id,
        ]);
    }

    private function currentDoctor(): Doctor
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        if (! $doctor) {
            abort(403, 'Doctor profile not found.');
        }
        return $doctor;
    }

    /**
     * Shape a Patient for JSON output.
     */
    private function serialize(Patient $p): array
    {
        return [
            'id'             => $p->id,
            'firstName'      => $p->first_name,
            'lastName'       => $p->last_name,
            'dob'            => $p->date_of_birth?->toDateString(),
            'gender'         => $p->biological_gender,
            'genderOther'    => $p->biological_gender_other,
            'chartId'        => $p->chart_id,
            'email'          => $p->email,
            'phone'          => $p->phone,
            'chiefComplaint' => $p->chief_complaint,
        ];
    }
}
