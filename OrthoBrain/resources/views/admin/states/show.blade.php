@extends('layouts.admin')
@section('title', 'View State')
@section('page_title', 'View State')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @include('admin._partials.view_field', ['label' => 'Country', 'value' => $state->country?->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'State Name', 'value' => $state->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'State Code', 'value' => $state->state_code, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Status', 'value' => $state->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
        </div>
        <div class="d-flex mt-2">
            <a href="{{ route('admin.states.edit', $state) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.states.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
