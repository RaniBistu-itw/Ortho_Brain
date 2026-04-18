@extends('layouts.admin')
@section('title', 'Edit Zip Code')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Edit Zip Code</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.zipcodes.update', $zipcode) }}" class="ob-form-validate"  novalidate>
        @method('PUT')
        @include('admin.zipcodes._form', ['submitLabel' => 'Update'])
    </form>
</div>
@endsection
