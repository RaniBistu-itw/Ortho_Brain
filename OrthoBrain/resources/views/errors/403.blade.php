@extends('errors.layout')

@section('page_title', 'Access forbidden')
@section('page_code', '403')

@section('digit_first', '4')
@section('digit_last', '3')

@section('orbiter_tone', 'orbiter--warning')
@section('orbiter_icon')
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="5" y="10.5" width="14" height="10" rx="2.5" class="orbiter-shape" stroke="#fff"/>
        <path d="M8 10.5V7a4 4 0 018 0v3.5" class="orbiter-stem" stroke-width="2.5" fill="none"/>
    </svg>
@endsection

@section('error_title', 'You don\'t have access to this page')
@section('error_sub')
    This area is restricted. If you believe this is a mistake, check that you're signed in with
    the right account, or ask an administrator to grant you access.
@endsection
