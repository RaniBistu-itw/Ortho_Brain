<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $section = $this->input('section');

        $base = [
            'section' => ['required', Rule::in(['contact', 'preferences', 'clinical', 'ortho'])],
        ];

        return array_merge($base, match ($section) {
            'contact'     => $this->contactRules(),
            'preferences' => $this->preferencesRules(),
            'clinical'    => $this->clinicalRules(),
            'ortho'       => $this->orthoRules(),
            default       => [],
        });
    }

    private function contactRules(): array
    {
        return [
            'first_name'                          => ['required', 'string', 'max:100'],
            'last_name'                           => ['required', 'string', 'max:100'],
            'preferred_language'                  => ['required', 'string', 'max:50'],
            'doctor_contact_email'                => ['required', 'email', 'max:150'],
            'doctor_cell_phone'                   => ['nullable', 'string', 'max:20'],
            'other_email'                         => ['nullable', 'email', 'max:150'],
            'preferred_contact_mode'              => ['required', Rule::in(['DOCTOR_ONLY', 'EMPLOYEE_OFFICE', 'DOCTOR_AND_EMPLOYEE_OFFICE'])],
            'currently_providing_ortho_services'  => ['nullable', 'boolean'],
        ];
    }

    private function preferencesRules(): array
    {
        return [
            'preferred_tooth_numbering_system' => ['required', Rule::in(['UNIVERSAL', 'FDI', 'PALMER', 'INTERNATIONAL'])],
            'smile_arc_pref'                   => ['required', Rule::in(['DEFER', 'LATERALS_0_5MM_SHORTER', 'LATERALS_SAME_LENGTH'])],
            'small_lateral_incisors_pref'      => ['required', Rule::in(['DEFER', 'IPR_LOWER_CAMOUFLAGE', 'LEAVE_SPACING_MESIAL_DISTAL'])],
            'mixed_dentition_pref'             => ['required', Rule::in(['DEFER', 'NO_APPLIANCES'])],
            'orthodontic_extractions_pref'     => ['required', Rule::in(['DEFER', 'NO_EXTRACTIONS'])],
            'ipr_protocol_pref'                => ['required', Rule::in(['DEFER', 'NO_IPR', 'OTHER'])],
            'ipr_protocol_other_note'          => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function clinicalRules(): array
    {
        return [
            'elastics_bonded_buttons_pref'   => ['required', Rule::in(['YES', 'NO'])],
            'extractions_if_suggested_pref'  => ['required', Rule::in(['YES', 'NO'])],
            'attachment_stage_pref'          => ['required', Rule::in(['AT_STEP_1', 'AT_STEP_OTHER'])],
        ];
    }

    private function orthoRules(): array
    {
        return [
            'specialties'            => ['nullable', 'array'],
            'specialties.*'          => ['integer', Rule::exists('specialties', 'id')],
            'modalities'             => ['nullable', 'array'],
            'modalities.*'           => ['integer', Rule::exists('modalities', 'id')],
            'treatment_modalities'   => ['nullable', 'array'],
            'treatment_modalities.*' => ['integer', Rule::exists('treatment_modalities', 'id')],
            'buccal_corridors'       => ['nullable', 'array'],
            'buccal_corridors.*'     => ['integer', Rule::exists('buccal_corridor_options', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currently_providing_ortho_services')) {
            $this->merge([
                'currently_providing_ortho_services' => filter_var(
                    $this->input('currently_providing_ortho_services'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ) ?? false,
            ]);
        }
    }
}
