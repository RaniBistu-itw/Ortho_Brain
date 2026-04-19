<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'state_id'   => ['required', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'name'       => ['required', 'string', 'max:100'],
            'status'     => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
