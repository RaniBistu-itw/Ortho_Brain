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
        $zipcodeId = $this->route('zipcode')?->id;

        return [
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'state_id'   => ['nullable', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'city_id'    => ['required', 'integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],
            'code'       => [
                'required', 'string', 'max:20',
                Rule::unique('zipcodes', 'code')
                    ->ignore($zipcodeId)
                    ->where(fn ($q) => $q->where('city_id', $this->input('city_id'))),
            ],
            'details'    => ['nullable', 'string'],
            'status'     => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'A zip code with this value already exists for the selected city.',
        ];
    }
}
