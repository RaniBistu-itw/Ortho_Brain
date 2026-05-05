@extends('layouts.admin')
@section('title', 'Edit Product')
@section('page_title', 'Edit Product')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" class="ob-form-validate" enctype="multipart/form-data" novalidate>
            @method('PUT')
            @include('admin.products._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
