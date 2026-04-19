@extends('layouts.admin')
@section('title', 'Add Zip Code')
@section('page_title', 'Add Zip Code')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.zipcodes.store') }}" class="ob-form-validate" novalidate>
            @include('admin.zipcodes._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
