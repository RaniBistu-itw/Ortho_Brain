@extends('layouts.admin')
@section('title', 'Edit State')
@section('page_title', 'Edit State')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.states.update', $state) }}" class="ob-form-validate" novalidate>
            @method('PUT')
            @include('admin.states._form', ['submitLabel' => 'Update'])
        </form>
    </div>
</div>
@endsection
