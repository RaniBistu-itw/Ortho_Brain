@extends('layouts.admin')
@section('title', 'View City')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">View City</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @include('admin._partials.view_field', ['label' => 'Country', 'value' => $city->state?->country?->name, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'State', 'value' => $city->state?->name, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'City Name', 'value' => $city->name, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'Status', 'value' => $city->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.cities.edit', $city) }}" class="px-5 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm">Edit</a>
        <a href="{{ route('admin.cities.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Back</a>
    </div>
</div>
@endsection
