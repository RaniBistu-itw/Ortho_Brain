<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

            'images'              => ['sometimes', 'array', 'max:' . Product::MAX_IMAGES],
            'images.*'            => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'remove_image_ids'    => ['sometimes', 'array'],
            'remove_image_ids.*'  => ['integer'],
            'image_order'         => ['sometimes', 'array'],
            'image_order.*'       => ['integer'],

            'description'         => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // Enforce the post-update total count: (existing - removed + new) ≤ MAX_IMAGES.
        $validator->after(function (Validator $v) {
            $product = $this->route('product');
            $existing = $product ? $product->images()->count() : 0;
            $toRemove = count($this->input('remove_image_ids', []));
            $toAdd    = count($this->file('images', []));

            $finalCount = max(0, $existing - $toRemove) + $toAdd;

            if ($finalCount > Product::MAX_IMAGES) {
                $v->errors()->add('images',
                    'A product can have at most ' . Product::MAX_IMAGES . ' images. This change would result in ' . $finalCount . '.');
            }
        });
    }
}
