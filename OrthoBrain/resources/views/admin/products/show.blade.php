@extends('layouts.admin')
@section('title', 'View Product')
@section('page_title', 'View Product')

@php
    $imgService = app(\App\Services\ImageUploadService::class);
    $imageUrls  = $product->images->map(fn ($im) => $imgService->url($im->s3_key))->filter()->values();
@endphp

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
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

            <div class="col-12 mb-1">
                <label class="form-label d-flex align-items-center" style="gap:.5rem;">
                    <span>Images</span>
                    @if ($imageUrls->count())
                        <span class="ob-image-count">{{ $imageUrls->count() }}</span>
                    @endif
                </label>
                @if ($imageUrls->count())
                    <div class="ob-show-gallery">
                        @foreach ($imageUrls as $i => $url)
                            <div class="ob-show-gallery-item">
                                <img src="{{ $url }}" alt="{{ $product->name }}" class="ob-show-gallery-img"
                                     data-preview-src="{{ $url }}">
                                @if ($i === 0)
                                    <span class="ob-image-cover-badge" title="Cover image">Cover</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded bg-light-secondary d-flex align-items-center justify-content-center" style="width: 160px; height: 160px;">
                        <i data-feather="image" class="text-muted" style="width:40px;height:40px;"></i>
                    </div>
                @endif
            </div>

            <div class="col-12 mb-1">
                <label class="form-label">Description</label>
                <div class="form-control bg-light-secondary" style="min-height: 100px;">
                    {!! $product->description ?: '<span class="text-muted">—</span>' !!}
                </div>
            </div>
        </div>

        <div class="d-flex mt-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
