<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('products_category', 'id')->whereNull('deleted_at')],
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }
}
