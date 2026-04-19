@extends('layouts.admin')
@section('title', 'Edit Zip Code')
@section('page_title', 'Edit Zip Code')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.zipcodes.update', $zipcode) }}" class="ob-form-validate" novalidate>
            @method('PUT')
            @include('admin.zipcodes._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
