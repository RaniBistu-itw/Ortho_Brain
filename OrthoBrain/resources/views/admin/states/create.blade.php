@extends('layouts.admin')
@section('title', 'Add State')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Add State</h1>
</div>
<div class="bg-white border border-[#ebe9f1] rounded-lg p-6">
    <form method="POST" action="{{ route('admin.states.store') }}" class="ob-form-validate"  novalidate>
        @include('admin.states._form', ['submitLabel' => 'Submit'])
    </form>
</div>
@endsection
