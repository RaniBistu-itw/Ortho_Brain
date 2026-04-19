@extends('layouts.admin')
@section('title', 'Add State')
@section('page_title', 'Add State')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.states.store') }}" class="ob-form-validate" novalidate>
            @include('admin.states._form', ['submitLabel' => 'Submit'])
        </form>
    </div>
</div>
@endsection
