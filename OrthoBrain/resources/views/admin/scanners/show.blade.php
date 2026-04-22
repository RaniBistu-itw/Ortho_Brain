@extends('layouts.admin')
@section('title', 'View Scanner')
@section('page_title', 'View Scanner')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            @include('admin._partials.view_field', ['label' => 'Scanner Name', 'value' => $scanner->name, 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Scanner Portal Link', 'value' => $scanner->portal_link])
            @include('admin._partials.view_field', ['label' => 'Portal Password', 'value' => $scanner->portal_password ? str_repeat('•', 8) : null])
            @include('admin._partials.view_field', ['label' => 'Status', 'value' => $scanner->status === 'ACTIVE' ? 'Active' : 'Inactive', 'required' => true])
            @include('admin._partials.view_field', ['label' => 'Description', 'value' => $scanner->description, 'span' => 2])
        </div>
        <div class="d-flex mt-2">
            <a href="{{ route('admin.scanners.index', ['edit' => $scanner->id]) }}" class="btn btn-primary me-1">Edit</a>
            <a href="{{ route('admin.scanners.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
