@extends('layouts.admin')
@section('title', 'Edit Category')
@section('page_title', 'Edit Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.product-categories.update', $category) }}" class="ob-form-validate" novalidate>
            @method('PUT')
            @include('admin.product-categories._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
