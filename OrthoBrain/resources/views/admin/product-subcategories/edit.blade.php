@extends('layouts.admin')
@section('title', 'Edit Sub Category')
@section('page_title', 'Edit Sub Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.product-subcategories.update', $subcategory) }}" class="ob-form-validate" novalidate>
            @method('PUT')
            @include('admin.product-subcategories._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
