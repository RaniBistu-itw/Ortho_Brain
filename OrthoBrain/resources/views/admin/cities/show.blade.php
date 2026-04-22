@extends('layouts.admin')
@section('title', 'View City')
@section('page_title', 'View City')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @include('admin._partials.view_field', ['label' => 'Country', 'value' => $city->state?->country?->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'State', 'value' => $city->state?->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'City Name', 'value' => $city->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Status', 'value' => $city->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
        </div>
        <div class="d-flex mt-2">
            <a href="{{ route('admin.cities.index', ['edit' => $city->id]) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
