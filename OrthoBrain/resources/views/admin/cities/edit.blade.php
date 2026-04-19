@extends('layouts.admin')
@section('title', 'Edit City')
@section('page_title', 'Edit City')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cities.update', $city) }}" class="ob-form-validate" novalidate>
            @method('PUT')
            @include('admin.cities._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
