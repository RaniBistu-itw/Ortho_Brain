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
        $subcategoryId = $this->route('product_subcategory')?->id ?? $this->route('productSubcategory')?->id;

        return [
            'category_id' => ['required', 'integer', Rule::exists('products_category', 'id')->whereNull('deleted_at')],
            'name'        => [
                'required', 'string', 'max:100',
                Rule::unique('products_subcategory', 'name')
                    ->ignore($subcategoryId)
                    ->where(fn ($q) => $q->where('category_id', $this->input('category_id')))
                    ->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A sub-category with this name already exists for the selected category.',
        ];
    }
}
