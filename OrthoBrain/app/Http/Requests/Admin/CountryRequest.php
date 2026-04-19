<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $countryId = $this->route('country')?->id;

        return [
            'name'         => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'max:10', Rule::unique('countries', 'country_code')->ignore($countryId)->whereNull('deleted_at')],
            'phone_code'   => ['required', 'string', 'max:10'],
            'status'       => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
