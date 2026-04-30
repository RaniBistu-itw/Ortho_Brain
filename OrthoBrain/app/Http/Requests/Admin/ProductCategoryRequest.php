<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('product_category')?->id ?? $this->route('productCategory')?->id;

        return [
            'name'   => [
                'required', 'string', 'max:255',
                Rule::unique('products_category', 'name')
                    ->ignore($categoryId)
                    ->whereNull('deleted_at'),
            ],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists.',
        ];
    }
}
