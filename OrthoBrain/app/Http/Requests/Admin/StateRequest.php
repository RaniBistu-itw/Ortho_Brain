<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stateId = $this->route('state')?->id;

        return [
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'name'       => ['required', 'string', 'max:100'],
            'state_code' => [
                'required', 'string', 'max:100',
                Rule::unique('states', 'state_code')
                    ->ignore($stateId)
                    ->where(fn ($q) => $q->where('country_id', $this->input('country_id')))
                    ->whereNull('deleted_at'),
            ],
            'status'     => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
