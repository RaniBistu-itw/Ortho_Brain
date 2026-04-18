@extends('layouts.admin')
@section('title', 'Edit State')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Edit State</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.states.update', $state) }}" class="ob-form-validate"  novalidate>
        @method('PUT')
        @include('admin.states._form', ['submitLabel' => 'Update'])
    </form>
</div>
@endsection
