<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class ShippingAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'streetAddress'  => 'required|string|max:200',
            'streetAddress2' => 'nullable|string|max:200',
            'zipId'          => 'required|string',
            'city'           => 'required|string|max:100',
            'state'          => 'required|string|max:100',
            'country'        => 'required|in:US,CA,AU',
            'savedAddressId' => 'nullable|string',
        ];
    }
}
