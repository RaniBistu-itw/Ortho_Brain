@extends('layouts.admin')
@section('title', 'Add Country')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Add Country</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.countries.store') }}" class="ob-form-validate"  novalidate>
        @include('admin.countries._form', ['submitLabel' => 'Submit'])
    </form>
</div>
@endsection
