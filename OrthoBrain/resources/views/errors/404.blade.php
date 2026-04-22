@extends('errors.layout')

@section('page_title', 'Page not found')
@section('page_code', '404')

@section('digit_first', '4')
@section('digit_last', '4')

@section('orbiter_tone', 'orbiter--cyan')
@section('orbiter_icon')
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="10" cy="10" r="6.5" class="orbiter-shape"/>
        <line x1="15" y1="15" x2="21" y2="21" class="orbiter-stem" stroke-width="3"/>
    </svg>
@endsection

@section('error_title', "We couldn't find that page")
@section('error_sub')
    The page you're looking for doesn't exist, was moved, or the link might be broken.
    Let's get you back on track.
@endsection
