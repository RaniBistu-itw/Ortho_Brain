@extends('layouts.admin')
@section('title', 'Add Scanner')
@section('page_title', 'Add Scanner')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.scanners.store') }}" class="ob-form-validate" novalidate>
            @include('admin.scanners._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
