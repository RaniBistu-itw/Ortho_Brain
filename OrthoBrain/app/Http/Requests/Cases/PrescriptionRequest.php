<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'arches'                                    => 'required|in:both,maxillary,mandibular',
            'iprProtocol.enabled'                       => 'required|boolean',
            'iprProtocol.value'                         => 'required_if:iprProtocol.enabled,true|nullable|in:defer,no-ipr,other',
            'attachments.enabled'                       => 'required|boolean',
            'attachments.value'                         => 'required_if:attachments.enabled,true|nullable|in:step-1,specific-step',
            'attachments.specificStepNumber'            => 'required_if:attachments.value,specific-step|nullable|integer|min:1',
            'elastics.enabled'                          => 'required|boolean',
            'elastics.value'                            => 'required_if:elastics.enabled,true|nullable|in:yes,no',
            'extractions.enabled'                       => 'required|boolean',
            'extractions.value'                         => 'required_if:extractions.enabled,true|nullable|in:yes,no',
            'toothMovementRestrictions.mode'            => 'required|in:none,select',
            'toothMovementRestrictions.selectedTeeth'   => 'required_if:toothMovementRestrictions.mode,select|array|min:1',
            'attachmentRestrictions.mode'               => 'required|in:none,select',
            'attachmentRestrictions.selectedTeeth'      => 'required_if:attachmentRestrictions.mode,select|array|min:1',
            'additionalComments'                        => 'nullable|string|max:5000',
            'futureRestorativeWork.hasWork'             => 'required|in:yes,no',
            'futureRestorativeWork.explanation'         => 'required_if:futureRestorativeWork.hasWork,yes|nullable|string|max:5000',
        ];
    }
}
