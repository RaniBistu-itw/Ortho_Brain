@extends('errors.layout')

@section('page_title', 'Under maintenance')
@section('page_code', '503')

@section('digit_first', '5')
@section('digit_last', '3')

@section('orbiter_tone', 'orbiter--warning')
@section('orbiter_icon')
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="13" r="7.5" class="orbiter-shape" stroke="#fff"/>
        <line x1="12" y1="13" x2="12" y2="9" class="orbiter-stem" stroke="#fff" stroke-width="2.2"/>
        <line x1="12" y1="13" x2="15" y2="15" class="orbiter-stem" stroke="#fff" stroke-width="2.2"/>
        <line x1="9"  y1="2.5" x2="15" y2="2.5" class="orbiter-stem" stroke-width="2.2"/>
    </svg>
@endsection

@section('error_title', "We'll be right back")
@section('error_sub')
    {{ $exception?->getMessage() ?: "We're performing some scheduled maintenance to bring you a better experience. This should only take a few minutes — thanks for your patience." }}
@endsection

@section('actions')
    <a href="javascript:location.reload()" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
        </svg>
        Check Again
    </a>
@endsection

@section('support_line')
    Need urgent help?
    <a href="mailto:{{ config('admin.brand.support_email', 'support@orthobrain.com') }}">Reach out to support</a>
    and we'll get back to you as soon as we're back online.
@endsection
