@extends('layouts.admin')
@section('title', 'Add Zip Code')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Add Zip Code</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.zipcodes.store') }}" class="ob-form-validate"  novalidate>
        @include('admin.zipcodes._form', ['submitLabel' => 'Submit'])
    </form>
</div>
@endsection
