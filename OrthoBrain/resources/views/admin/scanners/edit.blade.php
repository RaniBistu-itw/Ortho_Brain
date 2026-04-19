@extends('layouts.admin')
@section('title', 'Edit Scanner')
@section('page_title', 'Edit Scanner')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.scanners.update', $scanner) }}" class="ob-form-validate" novalidate>
            @method('PUT')
            @include('admin.scanners._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
