<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class PatientInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName'              => 'required|string|max:100',
            'lastName'               => 'required|string|max:100',
            'dateOfBirth'            => 'required|date|before:today|after:' . now()->subYears(120)->toDateString(),
            'biologicalGender'       => 'required|in:Male,Female,Non-binary,Prefer not to say,Self-describe/Other',
            'biologicalGenderOther'  => 'required_if:biologicalGender,Self-describe/Other|nullable|string|max:100',
            'patientChartId'         => 'nullable|string|max:50',
            'chiefComplaint'         => 'required|string|max:5000',
        ];
    }
}
