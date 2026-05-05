<?php

namespace App\Http\Requests\Cases;

use Illuminate\Foundation\Http\FormRequest;

class ImpressionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'impressionMethodId' => 'required|string',
            // TODO: add `exists:impression_methods,id` once table exists
        ];
    }
}
