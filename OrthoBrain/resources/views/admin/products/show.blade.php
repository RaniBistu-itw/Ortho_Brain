@extends('layouts.admin')
@section('title', 'View Product')

@php
    $imgService = app(\App\Services\ImageUploadService::class);
    $imageUrl = $product->image_s3_key ? $imgService->url($product->image_s3_key) : null;
@endphp

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">View Product</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @include('admin._partials.view_field', ['label' => 'Category', 'value' => $product->category?->name, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'Sub Category', 'value' => $product->subcategory?->name])
        @include('admin._partials.view_field', ['label' => 'Name', 'value' => $product->name, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'Base Price', 'value' => '$' . number_format((float) $product->base_price, 2), 'required' => true])
        @include('admin._partials.view_field', ['label' => 'Number of Revisions/Refinements', 'value' => $product->number_of_revisions])
        @include('admin._partials.view_field', ['label' => 'Product Term (in months)', 'value' => $product->product_term_months])
        @include('admin._partials.view_field', ['label' => 'From Step', 'value' => $product->from_step])
        @include('admin._partials.view_field', ['label' => 'To Step', 'value' => $product->to_step])
        @include('admin._partials.view_field', ['label' => 'URL', 'value' => $product->url])
        @include('admin._partials.view_field', ['label' => 'Status', 'value' => $product->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-[#5e5873] mb-1.5">Image</label>
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-40 h-40 object-cover rounded-lg border border-[#d8d6de] bg-white shadow-sm">
            @else
                <div class="w-40 h-40 flex items-center justify-center rounded-lg border border-dashed border-[#d8d6de] bg-[#f8f8f8] text-[#b9b9c3]">
                    <i class="bi bi-image text-4xl"></i>
                </div>
            @endif
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-[#5e5873] mb-1.5">Description</label>
            <div class="w-full rounded-md border border-[#d8d6de] bg-[#f8f8f8] px-3 py-2 text-sm text-[#6e6b7b] min-h-[100px] prose prose-sm max-w-none">
                {!! $product->description ?: '<span class="text-[#b9b9c3]">—</span>' !!}
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.products.edit', $product) }}" class="px-5 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm">Edit</a>
        <a href="{{ route('admin.products.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Back</a>
    </div>
</div>
@endsection
