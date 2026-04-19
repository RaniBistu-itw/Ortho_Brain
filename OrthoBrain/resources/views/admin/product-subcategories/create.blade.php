@extends('layouts.admin')
@section('title', 'Add Sub Category')
@section('page_title', 'Add Sub Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.product-subcategories.store') }}" class="ob-form-validate" novalidate>
            @include('admin.product-subcategories._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
