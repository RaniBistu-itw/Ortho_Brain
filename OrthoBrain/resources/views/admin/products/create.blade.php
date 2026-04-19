@extends('layouts.admin')
@section('title', 'Add Product')
@section('page_title', 'Add Product')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.products.store') }}" class="ob-form-validate" enctype="multipart/form-data" novalidate>
            @include('admin.products._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
