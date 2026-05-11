<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'           => 'required|in:SUBMITTED,IN_REVIEW,APPROVED,REJECTED',
            'rejection_reason' => [
                'required_if:status,REJECTED',
                'nullable',
                'string',
                'min:10',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required_if' => 'Please provide a rejection reason (minimum 10 characters).',
            'rejection_reason.min'         => 'The rejection reason should be at least 10 characters.',
        ];
    }
}
