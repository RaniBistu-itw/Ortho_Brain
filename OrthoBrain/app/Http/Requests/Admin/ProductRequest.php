<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'         => ['required', 'integer', Rule::exists('products_category', 'id')->whereNull('deleted_at')],
            'subcategory_id'      => ['nullable', 'integer', Rule::exists('products_subcategory', 'id')->whereNull('deleted_at')],
            'name'                => ['required', 'string', 'max:255'],
            'base_price'          => ['required', 'numeric', 'min:0'],
            'number_of_revisions' => ['nullable', 'integer', 'min:0'],
            'product_term_months' => ['nullable', 'integer', 'min:0'],
            'from_step'           => ['nullable', 'integer', 'min:0'],
            'to_step'             => ['nullable', 'integer', 'min:0', 'gte:from_step'],
            'url'                 => ['nullable', 'url', 'max:500'],
            'status'              => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'image'               => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'description'         => ['nullable', 'string'],
        ];
    }
}
