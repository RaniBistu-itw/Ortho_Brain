@extends('layouts.admin')
@section('title', 'Add Category')
@section('page_title', 'Add Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.product-categories.store') }}" class="ob-form-validate" novalidate>
            @include('admin.product-categories._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
