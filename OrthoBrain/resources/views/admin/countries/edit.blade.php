@extends('layouts.admin')
@section('title', 'Edit Country')
@section('page_title', 'Edit Country')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.countries.update', $country) }}" class="ob-form-validate" novalidate>
            @method('PUT')
            @include('admin.countries._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
