@extends('errors.layout')

@section('page_title', 'Session expired')
@section('page_code', '419')

@section('digit_first', '4')
@section('digit_last', '9')

@section('orbiter_tone', 'orbiter--warning')
@section('orbiter_icon')
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="13" r="7.5" class="orbiter-shape" stroke="#fff"/>
        <line x1="12" y1="13" x2="12" y2="9"  class="orbiter-stem" stroke="#fff" stroke-width="2.2"/>
        <line x1="12" y1="13" x2="15" y2="15" class="orbiter-stem" stroke="#fff" stroke-width="2.2"/>
    </svg>
@endsection

@section('error_title', 'Your session has expired')
@section('error_sub')
    For your security, we log you out after a period of inactivity. Please sign in again to
    continue where you left off.
@endsection

@section('actions')
    <a href="{{ url('/login') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
        Sign In Again
    </a>
    <a href="{{ url('/') }}" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12l9-9 9 9"/><path d="M9 21V12h6v9"/><path d="M5 10v11h14V10"/>
        </svg>
        Back to Home
    </a>
@endsection
