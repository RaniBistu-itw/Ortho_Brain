@extends('layouts.admin')
@section('title', 'View Country')
@section('page_title', 'View Country')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @include('admin._partials.view_field', ['label' => 'Country Name', 'value' => $country->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Country Code', 'value' => $country->country_code, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Phone Code', 'value' => $country->phone_code, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Status', 'value' => $country->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
        </div>

        <div class="d-flex mt-2">
            <a href="{{ route('admin.countries.edit', $country) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
