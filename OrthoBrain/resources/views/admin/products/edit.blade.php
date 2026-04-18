@extends('layouts.admin')
@section('title', 'Edit Product')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Edit Product</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="ob-form-validate" enctype="multipart/form-data" novalidate>
        @method('PUT')
        @include('admin.products._form', ['submitLabel' => 'Update'])
    </form>
</div>
@endsection
