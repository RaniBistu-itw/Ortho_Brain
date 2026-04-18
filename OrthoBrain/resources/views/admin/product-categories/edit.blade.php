@extends('layouts.admin')
@section('title', 'Edit Category')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Edit Category</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.product-categories.update', $category) }}" class="ob-form-validate"  novalidate>
        @method('PUT')
        @include('admin.product-categories._form', ['submitLabel' => 'Update'])
    </form>
</div>
@endsection
