@extends('layouts.admin')
@section('title', 'View Category')
@section('page_title', 'View Category')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @include('admin._partials.view_field', ['label' => 'Category Name', 'value' => $category->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Status', 'value' => $category->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Sub-categories', 'value' => $category->subcategories_count])
            @include('admin._partials.view_field', ['label' => 'Products', 'value' => $category->products_count])
        </div>
        <div class="d-flex mt-2">
            <a href="{{ route('admin.product-categories.edit', $category) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
