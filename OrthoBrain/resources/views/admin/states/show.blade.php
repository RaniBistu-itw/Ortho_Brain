@extends('layouts.admin')
@section('title', 'View State')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">View State</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @include('admin._partials.view_field', ['label' => 'Country', 'value' => $state->country?->name, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'State Name', 'value' => $state->name, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'State Code', 'value' => $state->state_code, 'required' => true])
        @include('admin._partials.view_field', ['label' => 'Status', 'value' => $state->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.states.edit', $state) }}" class="px-5 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm">Edit</a>
        <a href="{{ route('admin.states.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Back</a>
    </div>
</div>
@endsection
