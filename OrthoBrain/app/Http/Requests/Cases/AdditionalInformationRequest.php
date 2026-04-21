<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class AdditionalInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sectionExpanded'                               => 'nullable|boolean',

            // A. Diagnosis
            'diagnosis'                                     => 'nullable|array',
            'diagnosis.*'                                   => 'nullable|boolean',

            // B. Medical History
            'medicalHistory.wnl'                            => 'nullable|boolean',
            'medicalHistory.allergies.enabled'              => 'nullable|boolean',
            'medicalHistory.allergies.description'          => 'nullable|string|max:5000',
            'medicalHistory.medications.enabled'            => 'nullable|boolean',
            'medicalHistory.medications.value'              => 'nullable|in:immunosuppressants,boneModifier,calciumChannelBlocker,antiConvulsant,other',
            'medicalHistory.medications.otherText'          => 'nullable|string|max:200',
            'medicalHistory.boneDisorders.enabled'          => 'nullable|boolean',
            'medicalHistory.boneDisorders.description'      => 'nullable|string|max:5000',

            // C. Dental History
            'dentalHistory.wnl'                             => 'nullable|boolean',
            'dentalHistory.trauma.enabled'                  => 'nullable|boolean',
            'dentalHistory.trauma.description'              => 'nullable|string|max:5000',
            'dentalHistory.poorPrognosis.enabled'           => 'nullable|boolean',
            'dentalHistory.poorPrognosis.description'       => 'nullable|string|max:5000',
            'dentalHistory.oralPathology'                   => 'nullable|boolean',
            'dentalHistory.enamelPathologies'               => 'nullable|boolean',
            'dentalHistory.attrition'                       => 'nullable|boolean',
            'dentalHistory.speechDysfunction'               => 'nullable|boolean',
            'dentalHistory.airwayProblems'                  => 'nullable|boolean',
            'dentalHistory.tmjIssues'                       => 'nullable|boolean',
            'dentalHistory.other.enabled'                   => 'nullable|boolean',
            'dentalHistory.other.otherText'                 => 'nullable|string|max:200',

            // D. Orthodontic History
            'orthodonticHistory.noPreviousTreatment'        => 'nullable|boolean',
            'orthodonticHistory.completedPhase1'            => 'nullable|boolean',
            'orthodonticHistory.previousComprehensive'      => 'nullable|boolean',
            'orthodonticHistory.other.enabled'              => 'nullable|boolean',
            'orthodonticHistory.other.otherText'            => 'nullable|string|max:200',

            // E. Family History
            'familyHistory.none'                            => 'nullable|boolean',
            'familyHistory.notKnown'                        => 'nullable|boolean',
            'familyHistory.extractions'                     => 'nullable|boolean',
            'familyHistory.impactedCanines'                 => 'nullable|boolean',
            'familyHistory.orthognathicSurgery'             => 'nullable|boolean',

            // F. Periodontal Evaluation
            'periodontalEvaluation.wnl'                                  => 'nullable|boolean',
            'periodontalEvaluation.oralHygiene'                          => 'nullable|in:wnl,fair,poor',
            'periodontalEvaluation.frenalAttachments'                    => 'nullable|in:wnl,excessive,frenectomy',
            'periodontalEvaluation.gingivalTissue'                       => 'nullable|array',
            'periodontalEvaluation.gingivalTissue.*'                     => 'nullable|boolean',
            'periodontalEvaluation.hardTissue.wnl'                       => 'nullable|boolean',
            'periodontalEvaluation.hardTissue.boneLoss'                  => 'nullable|boolean',
            'periodontalEvaluation.hardTissue.other.enabled'             => 'nullable|boolean',
            'periodontalEvaluation.hardTissue.other.otherText'           => 'nullable|string|max:200',

            // G. Parafunctional Habits
            'parafunctionalHabits'                          => 'nullable|array',
            'parafunctionalHabits.other.enabled'            => 'nullable|boolean',
            'parafunctionalHabits.other.otherText'          => 'nullable|string|max:200',

            // H. Centric Relation
            'centricRelation.value'                         => 'nullable|in:notPresent,present',
            'centricRelation.description'                   => 'nullable|string|max:5000',

            // I. Treatment Scope
            'treatmentScope.value'                          => 'nullable|in:aesthetics,comprehensive,other',
            'treatmentScope.otherText'                      => 'nullable|string|max:200',

            // J. Anterior-Posterior Relationship
            'anteriorPosterior.value'                       => 'nullable|in:defer,maintain,iprIfNecessary,elasticsOnly,elasticsAndIpr,other',
            'anteriorPosterior.otherText'                   => 'nullable|string|max:200',

            // K / L / M
            'midlineCorrection'                             => 'nullable|in:yes,no,defer',
            'overjetOverbite'                               => 'nullable|in:defer,maintain,improve',
            'crossbite'                                     => 'nullable|in:defer,maintain,improve',
        ];
    }
}
