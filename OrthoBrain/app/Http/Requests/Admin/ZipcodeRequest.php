<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ZipcodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'state_id'   => ['nullable', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'city_id'    => ['required', 'integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],
            'code'       => ['required', 'string', 'max:20'],
            'details'    => ['nullable', 'string'],
            'status'     => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
