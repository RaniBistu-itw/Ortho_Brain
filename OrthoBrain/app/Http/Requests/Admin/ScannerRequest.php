<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'portal_link'     => ['nullable', 'url', 'max:255'],
            'portal_password' => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:255'],
            'status'          => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
