@extends('layouts.admin')
@section('title', 'View Sub Category')
@section('page_title', 'View Sub Category')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @include('admin._partials.view_field', ['label' => 'Category', 'value' => $subcategory->category?->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Sub Category Name', 'value' => $subcategory->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Status', 'value' => $subcategory->status ? 'Active' : 'Inactive', 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Products', 'value' => $subcategory->products_count])
            @include('admin._partials.view_field', ['label' => 'Description', 'value' => $subcategory->description, 'span' => 2])
        </div>
        <div class="d-flex mt-2">
            <a href="{{ route('admin.product-subcategories.edit', $subcategory) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.product-subcategories.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
