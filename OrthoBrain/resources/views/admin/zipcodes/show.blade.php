@extends('layouts.admin')
@section('title', 'View Zip Code')
@section('page_title', 'View Zip Code')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @include('admin._partials.view_field', ['label' => 'Country Name', 'value' => $zipcode->city?->state?->country?->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'State Name', 'value' => $zipcode->city?->state?->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'City Name', 'value' => $zipcode->city?->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Zip Code', 'value' => $zipcode->code, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Zip Details', 'value' => $zipcode->details])
            @include('admin._partials.view_field', ['label' => 'Status', 'value' => $zipcode->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
        </div>
        <div class="d-flex mt-2">
            <a href="{{ route('admin.zipcodes.index', ['edit' => $zipcode->id]) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.zipcodes.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
