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
        $cityId = $this->route('city')?->id;

        return [
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'state_id'   => ['required', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'name'       => [
                'required', 'string', 'max:100',
                Rule::unique('cities', 'name')
                    ->ignore($cityId)
                    ->where(fn ($q) => $q->where('state_id', $this->input('state_id')))
                    ->whereNull('deleted_at'),
            ],
            'status'     => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A city with this name already exists for the selected state.',
        ];
    }
}
