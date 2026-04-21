<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class SubmitOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'submitterInitials' => 'required|string|regex:/^[A-Za-z]{2,5}$/',
            'termsAgreed'       => 'required|accepted',
        ];
    }
}
