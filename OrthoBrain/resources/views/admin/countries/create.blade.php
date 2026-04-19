@extends('layouts.admin')
@section('title', 'Add Country')
@section('page_title', 'Add Country')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.countries.store') }}" class="ob-form-validate" novalidate>
            @include('admin.countries._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
