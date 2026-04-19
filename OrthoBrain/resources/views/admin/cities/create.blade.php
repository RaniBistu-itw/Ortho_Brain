@extends('layouts.admin')
@section('title', 'Add City')
@section('page_title', 'Add City')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cities.store') }}" class="ob-form-validate" novalidate>
            @include('admin.cities._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
