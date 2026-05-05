@extends('errors.layout')

@section('page_title', 'Something went wrong')
@section('page_code', '500')

@section('digit_first', '5')
@section('digit_last', '0')

@section('orbiter_tone', 'orbiter--danger')
@section('orbiter_icon')
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.3 2.3-2.4-.3-.3-2.4 2.3-2.3z"
              class="orbiter-shape" stroke="#fff"/>
    </svg>
@endsection

@section('error_title', 'Something went wrong on our end')
@section('error_sub')
    An unexpected error occurred while processing your request. Our team has been notified.
    Please try again in a moment, or head back home.
@endsection

@section('actions')
    <a href="{{ url('/') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12l9-9 9 9"/><path d="M9 21V12h6v9"/><path d="M5 10v11h14V10"/>
        </svg>
        Back to Home
    </a>
    <a href="javascript:location.reload()" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
        </svg>
        Try Again
    </a>
@endsection
