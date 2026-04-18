@extends('layouts.admin')
@section('title', 'Edit City')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Edit City</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.cities.update', $city) }}" class="ob-form-validate"  novalidate>
        @method('PUT')
        @include('admin.cities._form', ['submitLabel' => 'Update'])
    </form>
</div>
@endsection
