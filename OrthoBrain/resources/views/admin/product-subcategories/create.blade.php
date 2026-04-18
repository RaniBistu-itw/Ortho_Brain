@extends('layouts.admin')
@section('title', 'Add Sub Category')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Add Sub Category</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.product-subcategories.store') }}" class="ob-form-validate"  novalidate>
        @include('admin.product-subcategories._form', ['submitLabel' => 'Submit'])
    </form>
</div>
@endsection
