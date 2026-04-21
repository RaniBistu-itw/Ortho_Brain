@extends('layouts.admin')
@section('title', 'Add Doctor')
@section('page_title', 'Add Doctor')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.doctors.index') }}">Doctors</a></li>
    <li class="breadcrumb-item active">Add Doctor</li>
@endsection

@push('styles')
<style>
    /* ──────────────────────────────────────────────────────────────
       Scoped styles for the admin Add-Doctor form.
       Mirrors the public /register layout so the admin form keeps
       the same four-section structure (Account / Practice / Address /
       Additional) the user is already used to. All class names are
       prefixed "reg-" to avoid colliding with the Vuexy admin theme.
       ────────────────────────────────────────────────────────────── */
    #adminDoctorCreate { color: #6e6b7b; }
    #adminDoctorCreate .reg-layout { display: flex; flex-direction: column; gap: 1.5rem; align-items: flex-start; position: relative; }
    @media (min-width: 992px) { #adminDoctorCreate .reg-layout { flex-direction: row; gap: 1.5rem; } }

    #adminDoctorCreate .reg-sidebar {
        width: 100%;
        flex-shrink: 0;
        background: #fff;
        border-radius: 0.428rem;
        box-shadow: 0 4px 24px 0 rgba(34,41,47,0.1);
        padding: 1.25rem;
        align-self: flex-start;
    }
    @media (min-width: 992px) { #adminDoctorCreate .reg-sidebar { width: 260px; position: sticky; top: 6rem; } }
    #adminDoctorCreate .reg-sidebar nav { display: flex; flex-direction: column; gap: 1rem; }
    #adminDoctorCreate .reg-nav-item { display: flex; align-items: center; gap: 1rem; cursor: pointer; }
    #adminDoctorCreate .reg-nav-icon {
        display: flex; align-items: center; justify-content: center;
        width: 2.5rem; height: 2.5rem;
        border-radius: 0.358rem;
        background: #f8f8f8;
        color: #b9b9c3;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    #adminDoctorCreate .reg-nav-item .reg-nav-title { font-size: 0.95rem; color: #5e5873; font-weight: 500; transition: all 0.2s; }
    #adminDoctorCreate .reg-nav-item .reg-nav-sub { font-size: 0.8rem; color: #b9b9c3; }
    #adminDoctorCreate .reg-nav-item.active .reg-nav-icon { background: #5bc0de; color: #fff; box-shadow: 0 2px 4px rgba(91,192,222,0.4); }
    #adminDoctorCreate .reg-nav-item.active .reg-nav-title { color: #5bc0de; }

    #adminDoctorCreate .reg-main { flex: 1; width: 100%; display: flex; flex-direction: column; gap: 1.5rem; min-width: 0; }
    #adminDoctorCreate .reg-card {
        background: #fff;
        border-radius: 0.428rem;
        box-shadow: 0 4px 24px 0 rgba(34,41,47,0.1);
        padding: 1.5rem;
        scroll-margin-top: 6rem;
    }
    #adminDoctorCreate .reg-card-head { margin-bottom: 1.25rem; }
    #adminDoctorCreate .reg-card-head h2 { font-size: 1.3rem; font-weight: 500; color: #5e5873; margin: 0 0 0.25rem; }
    #adminDoctorCreate .reg-card-head p { font-size: 0.9rem; color: #6e6b7b; margin: 0; }
    #adminDoctorCreate .reg-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
    @media (min-width: 768px) { #adminDoctorCreate .reg-grid { grid-template-columns: 1fr 1fr; } }
    #adminDoctorCreate .reg-col-span-2 { grid-column: span 1; }
    @media (min-width: 768px) { #adminDoctorCreate .reg-col-span-2 { grid-column: span 2; } }

    #adminDoctorCreate .reg-label { display: block; font-size: 0.85rem; font-weight: 500; color: #5e5873; margin-bottom: 0.25rem; }
    #adminDoctorCreate .reg-required { color: #ea5455; }
    #adminDoctorCreate .reg-input-group {
        display: flex;
        align-items: center;
        border: 1px solid #d8d6de;
        border-radius: 0.358rem;
        background: #fff;
        overflow: hidden;
        transition: all .2s;
    }
    #adminDoctorCreate .reg-input-group:focus-within { border-color: #5bc0de; box-shadow: 0 0 0 0.2rem rgba(91,192,222,0.25); }
    #adminDoctorCreate .reg-input-group.is-invalid { border-color: #ea5455; }
    #adminDoctorCreate .reg-input-icon {
        display: flex; align-items: center; justify-content: center;
        padding: 0.5rem 0.5rem 0.5rem 0.75rem;
        color: #b9b9c3;
        background: #fff;
        border-right: 1px solid #d8d6de;
        align-self: stretch;
    }
    #adminDoctorCreate .reg-input-group.is-invalid .reg-input-icon { border-right-color: #ea5455; }
    #adminDoctorCreate .reg-input {
        flex: 1;
        border: 0;
        outline: none;
        padding: 0.5rem 0.75rem;
        font-size: 0.95rem;
        color: #6e6b7b;
        background: transparent;
        min-width: 0;
    }
    #adminDoctorCreate .reg-input::placeholder { color: #b9b9c3; }
    #adminDoctorCreate .reg-select {
        width: 100%;
        height: 2.5rem;
        padding: 0 0.75rem;
        background: #fff;
        border: 1px solid #d8d6de;
        border-radius: 0.358rem;
        outline: none;
        font-size: 0.95rem;
        color: #6e6b7b;
        appearance: none;
        -webkit-appearance: none;
    }
    #adminDoctorCreate .reg-select:focus { border-color: #5bc0de; box-shadow: 0 0 0 0.2rem rgba(91,192,222,0.25); }
    #adminDoctorCreate .reg-select.is-invalid { border-color: #ea5455; }
    #adminDoctorCreate .reg-phone {
        display: flex;
        border: 1px solid #d8d6de;
        border-radius: 0.358rem;
        background: #fff;
        transition: all .2s;
        overflow: hidden;
    }
    #adminDoctorCreate .reg-phone:focus-within { border-color: #5bc0de; box-shadow: 0 0 0 0.2rem rgba(91,192,222,0.25); }
    #adminDoctorCreate .reg-phone.is-invalid { border-color: #ea5455; }
    #adminDoctorCreate .reg-phone select {
        padding: 0 0.75rem;
        background: #f8f8f8;
        border: 0;
        border-right: 1px solid #d8d6de;
        color: #6e6b7b;
        font-size: 0.9rem;
        outline: none;
    }
    #adminDoctorCreate .reg-phone input {
        flex: 1;
        border: 0;
        outline: none;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        color: #6e6b7b;
        background: transparent;
        min-width: 0;
    }
    #adminDoctorCreate .reg-err { color: #ea5455; font-size: 0.8rem; margin: 0.25rem 0 0; }
    #adminDoctorCreate .reg-err.hidden { display: none; }
    #adminDoctorCreate .reg-card input,
    #adminDoctorCreate .reg-card select,
    #adminDoctorCreate .reg-card textarea,
    #adminDoctorCreate .reg-card button,
    #adminDoctorCreate .reg-card label { margin: 0; }
    #adminDoctorCreate .reg-card .reg-label { margin-bottom: 0.25rem; }

    /* Locked (read-only) state when an existing practice is picked */
    #adminDoctorCreate .reg-input-group.is-locked { background: #f8f8f8; }
    #adminDoctorCreate .reg-input-group.is-locked input { background: transparent; }
    #adminDoctorCreate .reg-phone.is-locked { background: #f8f8f8; }
    #adminDoctorCreate .reg-phone.is-locked select,
    #adminDoctorCreate .reg-phone.is-locked input { background: transparent; pointer-events: none; }
    #adminDoctorCreate .reg-select.is-locked { background: #f8f8f8; pointer-events: none; }

    /* Autocomplete wrapper + menu */
    #adminDoctorCreate .reg-autocomplete { position: relative; }
    #adminDoctorCreate .reg-autocomplete-menu {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #d8d6de;
        border-radius: 0.358rem;
        box-shadow: 0 4px 12px rgba(34,41,47,0.08);
        max-height: 260px;
        overflow-y: auto;
        z-index: 100;
        display: none;
    }
    #adminDoctorCreate .reg-autocomplete-menu.open { display: block; }
    #adminDoctorCreate .reg-autocomplete-item {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        font-size: 0.9rem;
        color: #6e6b7b;
        border-bottom: 1px solid #f6f6f6;
    }
    #adminDoctorCreate .reg-autocomplete-item:last-child { border-bottom: 0; }
    #adminDoctorCreate .reg-autocomplete-item:hover,
    #adminDoctorCreate .reg-autocomplete-item.active { background: #f8f8f8; color: #5e5873; }
    #adminDoctorCreate .reg-autocomplete-empty { padding: 0.5rem 0.75rem; font-size: 0.85rem; color: #b9b9c3; font-style: italic; }
    #adminDoctorCreate .reg-change-link {
        display: inline-block;
        margin-top: 0.25rem;
        font-size: 0.8rem;
        color: #5bc0de;
        cursor: pointer;
        text-decoration: none;
    }
    #adminDoctorCreate .reg-change-link:hover { text-decoration: underline; }

    /* Additional card */
    #adminDoctorCreate .reg-card-head-with-toggle { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem; }
    #adminDoctorCreate .reg-expand-btn { background: #5bc0de; color: #fff; border: 0; border-radius: 0.358rem; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer; transition: background .2s; }
    #adminDoctorCreate .reg-expand-btn:hover { background: #46b8da; }
    #adminDoctorCreate .reg-additional-body { display: flex; flex-direction: column; gap: 1.5rem; transition: all .3s; }
    #adminDoctorCreate .reg-additional-body.hidden { display: none; }

    #adminDoctorCreate .reg-section-label { display: block; font-size: 0.875rem; font-weight: 600; color: #5e5873; margin-bottom: 0.5rem; }
    #adminDoctorCreate .reg-option-list { display: flex; flex-direction: column; gap: 0.25rem; }
    #adminDoctorCreate .reg-option-grid { display: grid; grid-template-columns: 1fr; row-gap: 0.25rem; column-gap: 1rem; }
    @media (min-width: 640px) { #adminDoctorCreate .reg-option-grid { grid-template-columns: 1fr 1fr; } }
    #adminDoctorCreate .reg-option { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem; color: #6e6b7b; }
    #adminDoctorCreate .reg-option.align-start { align-items: flex-start; }
    #adminDoctorCreate .reg-option input[type="radio"],
    #adminDoctorCreate .reg-option input[type="checkbox"] { accent-color: #5bc0de; width: 1rem; height: 1rem; min-width: 1rem; cursor: pointer; flex-shrink: 0; }

    #adminDoctorCreate .reg-form-box { background: #fbfbfb; border: 1px solid #d8d6de; border-radius: 0.358rem; padding: 1.25rem; }
    #adminDoctorCreate .reg-form-box.hidden { display: none; }
    #adminDoctorCreate .reg-email-row { display: flex; gap: 0.5rem; }
    #adminDoctorCreate .reg-email-row .reg-input-group { flex: 1; }
    #adminDoctorCreate .reg-btn-delete { width: 2.5rem; height: 2.5rem; background: rgba(234,84,85,0.125); color: #ea5455; border: 0; border-radius: 0.358rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background .2s; }
    #adminDoctorCreate .reg-btn-delete:hover { background: rgba(234,84,85,0.2); }
    #adminDoctorCreate .reg-btn-primary { background: #5bc0de; color: #fff; border: 0; padding: 0.5rem 1rem; border-radius: 0.358rem; font-size: 0.85rem; font-weight: 500; cursor: pointer; box-shadow: 0 2px 4px rgba(91,192,222,0.3); transition: background .2s; }
    #adminDoctorCreate .reg-btn-primary:hover { background: #46b8da; }

    /* Doctor Preferences panel */
    #adminDoctorCreate .reg-pref-panel { background: #fcfcfc; border: 1px solid #ebe9f1; border-radius: 0.358rem; overflow: hidden; display: flex; flex-direction: column; }
    #adminDoctorCreate .reg-pref-header-bar { background: #fff; border-bottom: 1px solid #ebe9f1; padding: 0.75rem 1rem; font-weight: 700; color: #5e5873; font-size: 0.95rem; }
    #adminDoctorCreate .reg-pref-scroll { overflow-y: auto; max-height: 500px; background: #fff; position: relative; }
    #adminDoctorCreate .reg-pref-scroll::-webkit-scrollbar { width: 6px; }
    #adminDoctorCreate .reg-pref-scroll::-webkit-scrollbar-track { background: #fcfcfc; }
    #adminDoctorCreate .reg-pref-scroll::-webkit-scrollbar-thumb { background: #d8d6de; border-radius: 4px; }
    #adminDoctorCreate .reg-pref-scroll::-webkit-scrollbar-thumb:hover { background: #b9b9c3; }
    #adminDoctorCreate .reg-pref-section { padding: 1.25rem 1rem 1rem; border-bottom: 1px solid #ebe9f1; }
    #adminDoctorCreate .reg-pref-title { font-weight: 500; color: #5e5873; margin: 0 0 0.75rem; font-size: 0.95rem; }
    #adminDoctorCreate .reg-pref-list { display: flex; flex-direction: column; gap: 0.5rem; }
    #adminDoctorCreate .reg-pref-item { display: flex; align-items: center; cursor: pointer; }
    #adminDoctorCreate .reg-pref-item.align-start { align-items: flex-start; }
    #adminDoctorCreate .reg-pref-item input[type="radio"],
    #adminDoctorCreate .reg-pref-item input[type="checkbox"] { accent-color: #5bc0de; margin-right: 0.5rem; width: 1rem; height: 1rem; cursor: pointer; flex-shrink: 0; }
    #adminDoctorCreate .reg-pref-item.align-start input { margin-top: 0.25rem; }
    #adminDoctorCreate .reg-pref-item span { color: #6e6b7b; font-size: 0.95rem; }

    /* Toggle switches */
    #adminDoctorCreate .reg-toggle-group { display: flex; flex-direction: column; gap: 1.25rem; }
    #adminDoctorCreate .reg-toggle-label { display: flex; align-items: center; cursor: pointer; }
    #adminDoctorCreate .reg-toggle-switch { position: relative; width: 2.5rem; height: 22px; }
    #adminDoctorCreate .reg-toggle-switch input.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
    #adminDoctorCreate .reg-toggle-bg { display: block; width: 2.5rem; height: 22px; background: #ebe9f1; border-radius: 9999px; transition: background .2s; }
    #adminDoctorCreate .reg-toggle-dot { position: absolute; width: 1rem; height: 1rem; background: #fff; border-radius: 50%; top: 3px; right: 3px; transform: translateX(-125%); transition: transform .2s; box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
    #adminDoctorCreate .reg-toggle-switch input.sr-only:checked ~ .reg-toggle-bg { background: #5bc0de; }
    #adminDoctorCreate .reg-toggle-switch input.sr-only:checked ~ .reg-toggle-dot { transform: translateX(0); }
    #adminDoctorCreate .reg-toggle-text { margin-left: 0.75rem; font-size: 0.95rem; color: #6e6b7b; }
    #adminDoctorCreate .reg-toggle-panel { margin-top: 0.75rem; padding: 1rem; background: #f8f8f8; border: 1px solid #ebe9f1; border-radius: 0.358rem; display: flex; flex-direction: column; gap: 0.5rem; }
    #adminDoctorCreate .reg-toggle-panel.hidden { display: none; }

    #adminDoctorCreate .reg-back-to-top { position: sticky; bottom: 0; background: #5bc0de; color: #fff; width: 2rem; height: 2rem; border-radius: 0.358rem; display: flex; justify-content: center; align-items: center; cursor: pointer; opacity: 0.9; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: -2rem; float: right; z-index: 10; margin-right: 0.5rem; }
    #adminDoctorCreate .reg-back-to-top:hover { background: #46b8da; }

    /* Terms + actions — use admin Bootstrap buttons for the action bar */
    #adminDoctorCreate .reg-terms-wrap { margin-top: 1.5rem; padding: 0 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
    #adminDoctorCreate .reg-terms-label { display: flex; align-items: flex-start; color: #6e6b7b; font-size: 0.9rem; cursor: pointer; }
    #adminDoctorCreate .reg-terms-label input { margin: 0.2rem 0.75rem 0 0 !important; width: 1.1rem; height: 1.1rem; accent-color: #5bc0de; cursor: pointer; flex-shrink: 0; }
    #adminDoctorCreate .reg-terms-link { color: #5bc0de; text-decoration: none; }
    #adminDoctorCreate .reg-terms-link:hover { text-decoration: underline; }

    #adminDoctorCreate .reg-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.5rem;
        border-top: 1px solid #ebe9f1;
        padding-top: 1.5rem;
    }
</style>
@endpush

@section('content')
<div id="adminDoctorCreate">
    <div class="reg-layout">

        <aside class="reg-sidebar">
            <nav id="scrollspy-nav">
                <div class="reg-nav-item active" data-target="step-account">
                    <div class="reg-nav-icon"><i class="bi bi-house-door" style="font-size:1.15rem"></i></div>
                    <div>
                        <div class="reg-nav-title">Account</div>
                        <div class="reg-nav-sub">Account Details</div>
                    </div>
                </div>

                <div class="reg-nav-item" data-target="step-practice">
                    <div class="reg-nav-icon"><i class="bi bi-building" style="font-size:1.15rem"></i></div>
                    <div>
                        <div class="reg-nav-title">Practice</div>
                        <div class="reg-nav-sub">Practice Information</div>
                    </div>
                </div>

                <div class="reg-nav-item" data-target="step-address">
                    <div class="reg-nav-icon"><i class="bi bi-geo-alt" style="font-size:1.15rem"></i></div>
                    <div>
                        <div class="reg-nav-title">Address</div>
                        <div class="reg-nav-sub">Address Information</div>
                    </div>
                </div>

                <div class="reg-nav-item" data-target="step-additional">
                    <div class="reg-nav-icon"><i class="bi bi-file-earmark-text" style="font-size:1.15rem"></i></div>
                    <div>
                        <div class="reg-nav-title">Additional</div>
                        <div class="reg-nav-sub">Doctor Information</div>
                    </div>
                </div>
            </nav>
        </aside>

        <main class="reg-main">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="alert-body">
                        <strong>Please correct the following:</strong>
                        <ul class="mb-0 mt-25">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form id="registrationForm" action="{{ route('admin.doctors.store') }}" method="POST" novalidate onsubmit="return validateForm(event)">
                @csrf

                {{-- Account (Doctor Information) --}}
                <div id="step-account" class="reg-card">
                    <div class="reg-card-head">
                        <h2>Doctor Information</h2>
                        <p>Enter the doctor's account details</p>
                    </div>
                    <div class="reg-grid">
                        {{-- Email --}}
                        <div class="reg-col-span-2">
                            <label class="reg-label">Email (Username)<span class="reg-required">*</span></label>
                            <div class="reg-input-group" id="box-email">
                                <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                <input id="in-email" name="email" type="email" required value="{{ old('email') }}" class="reg-input" placeholder="Enter email" oninput="clearError('email')" />
                            </div>
                            <p id="err-email" class="reg-err hidden"></p>
                            @error('email')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- First Name --}}
                        <div>
                            <label class="reg-label">First Name<span class="reg-required">*</span></label>
                            <div class="reg-input-group" id="box-firstName">
                                <span class="reg-input-icon"><i class="bi bi-person"></i></span>
                                <input id="in-firstName" name="first_name" type="text" value="{{ old('first_name') }}" class="reg-input" placeholder="Enter first name" oninput="clearError('firstName')" />
                            </div>
                            <p id="err-firstName" class="reg-err hidden"></p>
                            @error('first_name')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- Last Name --}}
                        <div>
                            <label class="reg-label">Last Name<span class="reg-required">*</span></label>
                            <div class="reg-input-group" id="box-lastName">
                                <span class="reg-input-icon"><i class="bi bi-person"></i></span>
                                <input id="in-lastName" name="last_name" type="text" value="{{ old('last_name') }}" class="reg-input" placeholder="Enter last name" oninput="clearError('lastName')" />
                            </div>
                            <p id="err-lastName" class="reg-err hidden"></p>
                            @error('last_name')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- Password --}}
                        <div>
                            <label class="reg-label">Password<span class="reg-required">*</span></label>
                            <div class="reg-input-group" id="box-password">
                                <span class="reg-input-icon"><i class="bi bi-lock"></i></span>
                                <input id="in-password" name="password" type="password" class="reg-input" placeholder="&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;" oninput="clearError('password')" />
                                <span class="reg-input-icon" style="border-right:0; border-left:1px solid #d8d6de; cursor:pointer;" onclick="const p=document.getElementById('in-password');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash');">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                            </div>
                            <p id="err-password" class="reg-err hidden"></p>
                            @error('password')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- Confirm Password --}}
                        <div>
                            <label class="reg-label">Confirm Password<span class="reg-required">*</span></label>
                            <div class="reg-input-group" id="box-confirmPassword">
                                <span class="reg-input-icon"><i class="bi bi-lock"></i></span>
                                <input id="in-confirmPassword" name="confirm_password" type="password" class="reg-input" placeholder="&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;" oninput="clearError('confirmPassword')" />
                                <span class="reg-input-icon" style="border-right:0; border-left:1px solid #d8d6de; cursor:pointer;" onclick="const p=document.getElementById('in-confirmPassword');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash');">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                            </div>
                            <p id="err-confirmPassword" class="reg-err hidden"></p>
                        </div>
                    </div>
                </div>

                {{-- Practice --}}
                <div id="step-practice" class="reg-card">
                    <div class="reg-card-head">
                        <h2>Practice Information</h2>
                        <p>Search an existing practice or enter details for a new one</p>
                    </div>
                    <div class="reg-grid">
                        {{-- Practice Name (autocomplete) --}}
                        <div>
                            <label class="reg-label">Practice Name<span class="reg-required">*</span></label>
                            <div class="reg-autocomplete">
                                <input type="hidden" id="hid-practiceId" name="practice_id" value="{{ old('practice_id') }}">
                                <div class="reg-input-group" id="box-practiceName">
                                    <span class="reg-input-icon"><i class="bi bi-building"></i></span>
                                    <input id="in-practiceName" name="practice_name" type="text"
                                           autocomplete="off"
                                           value="{{ old('practice_name') }}"
                                           class="reg-input"
                                           placeholder="Start typing practice name…"
                                           oninput="onPracticeInput(event)"
                                           onkeydown="onPracticeKey(event)"
                                           onblur="onPracticeBlur(event)" />
                                </div>
                                <div id="practice-suggest" class="reg-autocomplete-menu" role="listbox"></div>
                            </div>
                            <a id="practice-change-link" class="reg-change-link" style="display:none" onclick="clearPracticeSelection()">Change practice</a>
                            <p id="err-practiceName" class="reg-err hidden"></p>
                            @error('practice_name')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- Practice Phone Number --}}
                        <div>
                            <label class="reg-label">Practice Phone Number<span class="reg-required">*</span></label>
                            <div class="reg-phone" id="box-phone">
                                <span class="reg-input-icon" style="border-right:0"><i class="bi bi-telephone"></i></span>
                                <select name="practice_phone_country_code">
                                    <option value="+1_US" @selected(old('practice_phone_country_code') === '+1_US')>+1 (US)</option>
                                    <option value="+1_CA" @selected(old('practice_phone_country_code') === '+1_CA')>+1 (CA)</option>
                                    <option value="+61_AU" @selected(old('practice_phone_country_code') === '+61_AU')>+61 (AU)</option>
                                </select>
                                <input id="in-phone" name="practice_phone_number" type="text" maxlength="10" value="{{ old('practice_phone_number') }}" placeholder="XXX-XXX-XXXX" oninput="clearError('phone')" />
                            </div>
                            <p id="err-phone" class="reg-err hidden"></p>
                            @error('practice_phone_number')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- Practice Website --}}
                        <div>
                            <label class="reg-label">Practice Website<span class="reg-required">*</span></label>
                            <div class="reg-input-group" id="box-website">
                                <span class="reg-input-icon"><i class="bi bi-globe"></i></span>
                                <input id="in-website" type="text" name="practice_website" value="{{ old('practice_website') }}" class="reg-input" placeholder="www.example.com" oninput="clearError('website')" />
                            </div>
                            <p id="err-website" class="reg-err hidden"></p>
                            @error('practice_website')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- Preferred Language --}}
                        <div>
                            <label class="reg-label">Preferred Language<span class="reg-required">*</span></label>
                            <select id="in-language" name="preferred_language" required class="reg-select" onchange="clearError('language')">
                                <option value="English"  @selected(old('preferred_language', 'English') === 'English')>English</option>
                                <option value="Spanish"  @selected(old('preferred_language') === 'Spanish')>Spanish</option>
                                <option value="French"   @selected(old('preferred_language') === 'French')>French</option>
                            </select>
                            <p id="err-language" class="reg-err hidden"></p>
                            @error('preferred_language')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Address --}}
                <div id="step-address" class="reg-card">
                    <div class="reg-card-head">
                        <h2>Address Information</h2>
                        <p>Enter the practice's address (auto-filled when you pick an existing practice)</p>
                    </div>
                    <input type="hidden" name="city_id"    id="hid-city"    value="{{ old('city_id') }}">
                    <input type="hidden" name="state_id"   id="hid-state"   value="{{ old('state_id') }}">
                    <input type="hidden" name="country_id" id="hid-country" value="{{ old('country_id') }}">

                    <div class="reg-grid">
                        {{-- Street Address --}}
                        <div>
                            <label class="reg-label">Street Address<span class="reg-required">*</span></label>
                            <div class="reg-input-group" id="box-address1">
                                <span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                <input id="in-address1" type="text" name="street_address_1" value="{{ old('street_address_1') }}" class="reg-input" placeholder="Street address 1" oninput="clearError('address1')" />
                            </div>
                            <p id="err-address1" class="reg-err hidden"></p>
                            @error('street_address_1')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- Street Address 2 --}}
                        <div>
                            <label class="reg-label">Street Address 2</label>
                            <div class="reg-input-group" id="box-address2">
                                <span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                <input id="in-address2" type="text" name="street_address_2" value="{{ old('street_address_2') }}" class="reg-input" placeholder="Street address 2" />
                            </div>
                        </div>
                        {{-- Zip --}}
                        <div>
                            <label class="reg-label">Zip<span class="reg-required">*</span></label>
                            <select id="in-zip" name="zip_id" required class="reg-select" onchange="onRegZipChange()">
                                <option value="" disabled @selected(!old('zip_id'))>Select zip code</option>
                                @foreach(($zipcodes ?? []) as $z)
                                    <option value="{{ $z->id }}"
                                            @selected(old('zip_id') == $z->id)
                                            data-city-id="{{ $z->city?->id }}"
                                            data-city="{{ $z->city?->name }}"
                                            data-state-id="{{ $z->city?->state?->id }}"
                                            data-state="{{ $z->city?->state?->name }}"
                                            data-country-id="{{ $z->city?->state?->country?->id }}"
                                            data-country="{{ $z->city?->state?->country?->name }}">
                                        {{ $z->code }} — {{ $z->city?->name }}, {{ $z->city?->state?->state_code }}
                                    </option>
                                @endforeach
                            </select>
                            <p id="err-zip" class="reg-err hidden"></p>
                            @error('zip_id')<p class="reg-err">{{ $message }}</p>@enderror
                        </div>
                        {{-- City --}}
                        <div>
                            <label class="reg-label">City<span class="reg-required">*</span></label>
                            <div class="reg-input-group" style="background:#f8f8f8">
                                <input id="in-city" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                            </div>
                        </div>
                        {{-- State --}}
                        <div>
                            <label class="reg-label">State/Province<span class="reg-required">*</span></label>
                            <div class="reg-input-group" style="background:#f8f8f8">
                                <input id="in-state" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                            </div>
                        </div>
                        {{-- Country --}}
                        <div>
                            <label class="reg-label">Country<span class="reg-required">*</span></label>
                            <div class="reg-input-group" style="background:#f8f8f8">
                                <input id="in-country" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Additional --}}
                <div id="step-additional" class="reg-card">
                    <div class="reg-card-head-with-toggle">
                        <div>
                            <h2 style="font-size:1.3rem; font-weight:500; color:#5e5873; margin:0 0 0.25rem;">Additional Doctor Information</h2>
                            <p style="font-size:0.9rem; color:#6e6b7b; margin:0;">This information will be saved to the doctor's account for all future submissions. The doctor may edit it later from their profile.</p>
                        </div>
                        <button type="button" onclick="toggleAdditionalInfo()" id="btn-toggle-add" class="reg-expand-btn">
                            <i id="icon-collapse" class="bi bi-dash-lg" style="font-size:1.1rem"></i>
                            <i id="icon-expand" class="bi bi-plus-lg hidden" style="font-size:1.1rem"></i>
                        </button>
                    </div>

                    <div id="additional-info-body" class="reg-additional-body">
                        {{-- Orthodontic Services --}}
                        <div>
                            <label class="reg-section-label">Are you currently providing orthodontic services in your practice?</label>
                            <div class="reg-option-list">
                                <label class="reg-option"><input type="radio" name="providing_ortho" value="yes" @checked(old('providing_ortho') === 'yes')> Yes</label>
                                <label class="reg-option"><input type="radio" name="providing_ortho" value="no"  @checked(old('providing_ortho') === 'no')> No</label>
                            </div>
                        </div>

                        {{-- Modalities --}}
                        <div>
                            <label class="reg-section-label">What modalities are you currently/or planning to provide?</label>
                            <div class="reg-option-list">
                                @foreach(($modalitiesList ?? collect()) as $opt)
                                    <label class="reg-option">
                                        <input type="checkbox" name="modalities[]" value="{{ $opt->id }}" @checked(in_array((string) $opt->id, (array) old('modalities', []), true))>
                                        {{ $opt->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Specialties --}}
                        <div>
                            <label class="reg-section-label">Specialties:</label>
                            <div class="reg-option-grid">
                                @foreach(($specialtiesList ?? collect()) as $opt)
                                    <label class="reg-option">
                                        <input type="checkbox" name="specialties[]" value="{{ $opt->id }}" @checked(in_array((string) $opt->id, (array) old('specialties', []), true))>
                                        {{ $opt->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Preferred doctor contact information --}}
                        <div>
                            <label class="reg-section-label">Preferred doctor contact information</label>
                            <div class="reg-option-list" style="margin-bottom:1rem;">
                                <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="doctor"   @checked(old('contact_preference') === 'doctor')> Doctor Only</label>
                                <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="employee" @checked(old('contact_preference') === 'employee')> Employee/Office</label>
                                <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="both"     @checked(old('contact_preference') === 'both')> Doctor and Employee/Office</label>
                            </div>

                            <div id="contact-forms-container" style="display:flex; flex-direction:column; gap:1rem;">
                                {{-- Doctor Form Box --}}
                                <div id="form-box-doctor" class="reg-form-box hidden">
                                    <div class="reg-grid" style="margin-bottom:1.25rem;">
                                        <div>
                                            <label class="reg-label">Doctor Email<span class="reg-required">*</span></label>
                                            <div class="reg-input-group">
                                                <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                                <input type="email" name="contact_doctor_email" value="{{ old('contact_doctor_email') }}" class="reg-input" placeholder="name@example.com" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="reg-label">Doctor Cell Phone Number</label>
                                            <div class="reg-input-group">
                                                <span class="reg-input-icon"><i class="bi bi-telephone"></i></span>
                                                <input type="text" name="contact_doctor_phone" value="{{ old('contact_doctor_phone') }}" class="reg-input" placeholder="XXX-XXX-XXXX" />
                                            </div>
                                        </div>
                                    </div>
                                    <div id="doctor-other-emails-list" style="display:flex; flex-direction:column; gap:1rem; margin-bottom:1rem;">
                                        <div>
                                            <label class="reg-label">Other Email</label>
                                            <div class="reg-email-row">
                                                <div class="reg-input-group">
                                                    <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                                    <input type="email" name="contact_doctor_other_emails[]" class="reg-input" placeholder="Enter other email" />
                                                </div>
                                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="reg-btn-delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="addEmailRow('doctor-other-emails-list')" class="reg-btn-primary">+ Add Other Email</button>
                                </div>

                                {{-- Employee Form Box --}}
                                <div id="form-box-employee" class="reg-form-box hidden">
                                    <div class="reg-grid" style="margin-bottom:1.25rem;">
                                        <div>
                                            <label class="reg-label">Employee Name<span class="reg-required">*</span></label>
                                            <div class="reg-input-group">
                                                <span class="reg-input-icon"><i class="bi bi-person"></i></span>
                                                <input type="text" name="contact_emp_name" value="{{ old('contact_emp_name') }}" class="reg-input" placeholder="Enter name" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="reg-label">Employee Title<span class="reg-required">*</span></label>
                                            <div class="reg-input-group">
                                                <span class="reg-input-icon"><i class="bi bi-briefcase"></i></span>
                                                <input type="text" name="contact_emp_title" value="{{ old('contact_emp_title') }}" class="reg-input" placeholder="Enter title" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="reg-label">Office/Employee Email<span class="reg-required">*</span></label>
                                            <div class="reg-input-group">
                                                <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                                <input type="email" name="contact_emp_email" value="{{ old('contact_emp_email') }}" class="reg-input" placeholder="Enter office/employee email" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="reg-label">Office/Employee Cell Phone Number<span class="reg-required">*</span></label>
                                            <div class="reg-input-group">
                                                <span class="reg-input-icon"><i class="bi bi-telephone"></i></span>
                                                <input type="text" name="contact_emp_phone" value="{{ old('contact_emp_phone') }}" class="reg-input" placeholder="XXX-XXX-XXXX" />
                                            </div>
                                        </div>
                                    </div>
                                    <div id="employee-other-emails-list" style="display:flex; flex-direction:column; gap:1rem; margin-bottom:1rem;">
                                        <div>
                                            <label class="reg-label">Other Email</label>
                                            <div class="reg-email-row">
                                                <div class="reg-input-group">
                                                    <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                                    <input type="email" name="contact_emp_other_emails[]" class="reg-input" placeholder="Enter other email" />
                                                </div>
                                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="reg-btn-delete"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="addEmailRow('employee-other-emails-list')" class="reg-btn-primary">+ Add Other Email</button>
                                </div>
                            </div>
                        </div>

                        {{-- Doctor Preferences Box --}}
                        <div class="reg-pref-panel">
                            <div class="reg-pref-header-bar">Doctor Preferences</div>
                            <div class="reg-pref-scroll">

                                {{-- Preferred Treatment Modality --}}
                                <div class="reg-pref-section">
                                    <p class="reg-pref-title">Preferred Treatment Modality</p>
                                    <div class="reg-pref-list">
                                        @foreach(($treatmentModalitiesList ?? collect()) as $opt)
                                            <label class="reg-pref-item">
                                                <input type="checkbox" name="treatment_modalities[]" value="{{ $opt->id }}" @checked(in_array((string) $opt->id, (array) old('treatment_modalities', []), true))>
                                                <span>{{ $opt->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Preferred Tooth Numbering System --}}
                                <div class="reg-pref-section">
                                    <p class="reg-pref-title">Preferred Tooth Numbering System</p>
                                    <div class="reg-pref-list">
                                        <label class="reg-pref-item"><input type="radio" name="tooth_numbering" checked><span>Universal (1-32)</span></label>
                                        <label class="reg-pref-item"><input type="radio" name="tooth_numbering"><span>FDI (11-48)</span></label>
                                        <label class="reg-pref-item"><input type="radio" name="tooth_numbering"><span>Palmer (UR1-UR8)</span></label>
                                        <label class="reg-pref-item"><input type="radio" name="tooth_numbering"><span>International (11-48)</span></label>
                                    </div>
                                </div>

                                {{-- Smile Arc --}}
                                <div class="reg-pref-section">
                                    <p class="reg-pref-title">Smile Arc</p>
                                    <div class="reg-pref-list">
                                        <label class="reg-pref-item"><input type="radio" name="smile_arc" checked><span>Defer to orthobrain&reg;</span></label>
                                        <label class="reg-pref-item"><input type="radio" name="smile_arc"><span>Lateral incisors .5mm shorter than central incisors</span></label>
                                        <label class="reg-pref-item"><input type="radio" name="smile_arc"><span>Lateral incisors same length as central incisors</span></label>
                                    </div>
                                </div>

                                {{-- Treatment of Small Lateral Incisors --}}
                                <div class="reg-pref-section">
                                    <p class="reg-pref-title">Treatment of Small Lateral Incisors</p>
                                    <div class="reg-pref-list">
                                        <label class="reg-pref-item"><input type="radio" name="lateral_incisors" checked><span>Defer to orthobrain&reg;</span></label>
                                        <label class="reg-pref-item"><input type="radio" name="lateral_incisors"><span>Interproximal Reduction (IPR) on lower arch to camouflage</span></label>
                                        <label class="reg-pref-item"><input type="radio" name="lateral_incisors"><span>Leave spacing mesial and distal to maxillary laterals for future cosmetic correction</span></label>
                                    </div>
                                </div>

                                {{-- Buccal Corridors --}}
                                <div class="reg-pref-section">
                                    <p class="reg-pref-title">Buccal Corridors</p>
                                    <div class="reg-pref-list">
                                        @foreach(($buccalCorridorsList ?? collect()) as $opt)
                                            <label class="reg-pref-item">
                                                <input type="checkbox" name="buccal_corridors[]" value="{{ $opt->id }}" @checked(in_array((string) $opt->id, (array) old('buccal_corridors', []), true))>
                                                <span>{!! $opt->name !!}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Mixed Dentition --}}
                                <div class="reg-pref-section">
                                    <p class="reg-pref-title">Mixed Dentition and Bite Correcting Appliances</p>
                                    <div class="reg-pref-list">
                                        <label class="reg-pref-item align-start"><input type="radio" name="mixed_dentition" checked><span>Defer to orthobrain&reg;</span></label>
                                        <label class="reg-pref-item align-start"><input type="radio" name="mixed_dentition"><span>I prefer not to use any growth and adjunctive appliances (e.g., expanders, bite planes, bit correctors, herbst, etc.) and request a proposal for a best outcome without an appliance knowing and fully understanding that this may not be an ideal Perfect Smile Plan for optimal results.</span></label>
                                    </div>
                                </div>

                                {{-- Orthodontic Extractions --}}
                                <div class="reg-pref-section">
                                    <p class="reg-pref-title">Orthodontic Extractions</p>
                                    <div class="reg-pref-list">
                                        <label class="reg-pref-item align-start"><input type="radio" name="ortho_extractions" checked><span>Defer to orthobrain&reg;</span></label>
                                        <label class="reg-pref-item align-start"><input type="radio" name="ortho_extractions"><span>I prefer not to extract teeth and request a proposal for a best outcome without extractions fully knowing and fully understanding that this may not be an ideal treatment plan for optimal results.</span></label>
                                    </div>
                                </div>

                                {{-- Preferences (Toggles) --}}
                                <div class="reg-pref-section" style="border-bottom:0; padding-bottom:1.5rem;">
                                    <p class="reg-pref-title" style="margin-bottom:1rem;">Preferences</p>
                                    <div class="reg-toggle-group">

                                        {{-- IPR Protocol --}}
                                        <div>
                                            <label class="reg-toggle-label">
                                                <span class="reg-toggle-switch">
                                                    <input type="checkbox" class="sr-only" onchange="document.getElementById('ipr-options').classList.toggle('hidden')">
                                                    <span class="reg-toggle-bg"></span>
                                                    <span class="reg-toggle-dot"></span>
                                                </span>
                                                <span class="reg-toggle-text">IPR Protocol</span>
                                            </label>
                                            <div id="ipr-options" class="reg-toggle-panel hidden">
                                                <label class="reg-pref-item"><input type="radio" name="ipr_opt" checked><span style="font-size:0.9rem;">Defer to orthobrain&reg;</span></label>
                                                <label class="reg-pref-item"><input type="radio" name="ipr_opt"><span style="font-size:0.9rem;">No IPR</span></label>
                                                <label class="reg-pref-item"><input type="radio" name="ipr_opt"><span style="font-size:0.9rem;">Other</span></label>
                                            </div>
                                        </div>

                                        {{-- Attachments --}}
                                        <div>
                                            <label class="reg-toggle-label">
                                                <span class="reg-toggle-switch">
                                                    <input type="checkbox" class="sr-only" onchange="document.getElementById('attachment-options').classList.toggle('hidden')">
                                                    <span class="reg-toggle-bg"></span>
                                                    <span class="reg-toggle-dot"></span>
                                                </span>
                                                <span class="reg-toggle-text">Attachments</span>
                                            </label>
                                            <div id="attachment-options" class="reg-toggle-panel hidden">
                                                <label class="reg-pref-item"><input type="radio" name="attachment_opt" checked><span style="font-size:0.9rem;">At Aligner Step 1</span></label>
                                                <label class="reg-pref-item"><input type="radio" name="attachment_opt"><span style="font-size:0.9rem;">At Aligner Step</span></label>
                                            </div>
                                        </div>

                                        {{-- Elastics/Bonded Buttons --}}
                                        <div>
                                            <label class="reg-toggle-label">
                                                <span class="reg-toggle-switch">
                                                    <input type="checkbox" class="sr-only" onchange="document.getElementById('elastics-options').classList.toggle('hidden')">
                                                    <span class="reg-toggle-bg"></span>
                                                    <span class="reg-toggle-dot"></span>
                                                </span>
                                                <span class="reg-toggle-text">Elastics/Bonded Buttons</span>
                                            </label>
                                            <div id="elastics-options" class="reg-toggle-panel hidden">
                                                <label class="reg-pref-item"><input type="radio" name="elastics_opt" checked><span style="font-size:0.9rem;">Yes</span></label>
                                                <label class="reg-pref-item"><input type="radio" name="elastics_opt"><span style="font-size:0.9rem;">No</span></label>
                                            </div>
                                        </div>

                                        {{-- Extractions if suggested --}}
                                        <div>
                                            <label class="reg-toggle-label">
                                                <span class="reg-toggle-switch">
                                                    <input type="checkbox" class="sr-only" onchange="document.getElementById('extractions-options').classList.toggle('hidden')">
                                                    <span class="reg-toggle-bg"></span>
                                                    <span class="reg-toggle-dot"></span>
                                                </span>
                                                <span class="reg-toggle-text">Extractions if suggested</span>
                                            </label>
                                            <div id="extractions-options" class="reg-toggle-panel hidden">
                                                <label class="reg-pref-item"><input type="radio" name="extractions_opt" checked><span style="font-size:0.9rem;">Yes</span></label>
                                                <label class="reg-pref-item"><input type="radio" name="extractions_opt"><span style="font-size:0.9rem;">No</span></label>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- Back to Top --}}
                                    <div class="reg-back-to-top" onclick="this.closest('.reg-pref-scroll').scrollTo({top: 0, behavior: 'smooth'});">
                                        <i class="bi bi-arrow-up" style="font-size:1rem"></i>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Terms + SMS --}}
                    <div class="reg-terms-wrap">
                        <label class="reg-terms-label">
                            <input type="checkbox" name="terms_agreed" @checked(old('terms_agreed')) onchange="clearError('terms')" />
                            <span>By creating an account at orthobrain you accept the <a href="#" class="reg-terms-link">Terms and Conditions</a>.<span class="reg-required">*</span></span>
                        </label>
                        <p id="err-terms" class="reg-err hidden"></p>
                        @error('terms_agreed')<p class="reg-err">{{ $message }}</p>@enderror
                        <label class="reg-terms-label">
                            <input type="checkbox" name="sms_agreed" @checked(old('sms_agreed'))/>
                            <span style="display:inline-flex; align-items:center;">I agree to receive SMS messages for authentication purposes.
                                <i class="bi bi-info-circle" style="margin-left:0.375rem; color:#5e5873; opacity:0.7; cursor:pointer;"></i>
                            </span>
                        </label>
                    </div>

                    {{-- Action Buttons (admin Bootstrap style) --}}
                    <div class="reg-actions">
                        <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="button" onclick="validateForm()" class="btn btn-success">
                            <i data-feather="save" class="me-25"></i> Save Doctor
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Additional Doctor Info UI
    function toggleAdditionalInfo() {
        const body = document.getElementById('additional-info-body');
        const iconExpand = document.getElementById('icon-expand');
        const iconCollapse = document.getElementById('icon-collapse');
        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            iconExpand.classList.add('hidden');
            iconCollapse.classList.remove('hidden');
        } else {
            body.classList.add('hidden');
            iconExpand.classList.remove('hidden');
            iconCollapse.classList.add('hidden');
        }
    }

    function toggleContactViews() {
        const selected = document.querySelector('input[name="contact_preference"]:checked');
        const docForm = document.getElementById('form-box-doctor');
        const empForm = document.getElementById('form-box-employee');
        docForm.classList.add('hidden');
        empForm.classList.add('hidden');
        if (selected) {
            if (selected.value === 'doctor')        docForm.classList.remove('hidden');
            else if (selected.value === 'employee') empForm.classList.remove('hidden');
            else if (selected.value === 'both') {
                docForm.classList.remove('hidden');
                empForm.classList.remove('hidden');
            }
        }
    }

    function addEmailRow(containerId) {
        const container = document.getElementById(containerId);
        const isDoctor = containerId.includes('doctor');
        const inputName = isDoctor ? 'contact_doctor_other_emails[]' : 'contact_emp_other_emails[]';
        const row = document.createElement('div');
        row.innerHTML = `
            <label class="reg-label">Other Email</label>
            <div class="reg-email-row">
                <div class="reg-input-group">
                    <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="${inputName}" class="reg-input" placeholder="Enter other email" />
                </div>
                <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="reg-btn-delete"><i class="bi bi-trash"></i></button>
            </div>
        `;
        container.appendChild(row);
    }

    // ─── Practice autocomplete ───────────────────────────
    const PRACTICE_SEARCH_URL = @json(route('practice.search'));
    let practiceSearchTimer    = null;
    let practiceSearchAbort    = null;
    let practiceSuggestions    = [];
    let practiceActiveIdx      = -1;

    function onPracticeInput(e) {
        clearError('practiceName');
        if (document.getElementById('hid-practiceId').value) {
            document.getElementById('hid-practiceId').value = '';
            unlockPracticeFields();
            document.getElementById('practice-change-link').style.display = 'none';
        }
        const q = e.target.value.trim();
        clearTimeout(practiceSearchTimer);
        if (q.length < 2) { hidePracticeMenu(); return; }
        practiceSearchTimer = setTimeout(() => fetchPracticeSuggestions(q), 300);
    }

    async function fetchPracticeSuggestions(q) {
        if (practiceSearchAbort) practiceSearchAbort.abort();
        practiceSearchAbort = new AbortController();
        try {
            const res = await fetch(PRACTICE_SEARCH_URL + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: practiceSearchAbort.signal,
            });
            if (!res.ok) throw new Error('search failed');
            practiceSuggestions = await res.json();
            practiceActiveIdx   = -1;
            renderPracticeMenu();
        } catch (err) {
            if (err.name !== 'AbortError') console.error('practice search error', err);
        }
    }

    function renderPracticeMenu() {
        const menu = document.getElementById('practice-suggest');
        if (!practiceSuggestions || practiceSuggestions.length === 0) {
            menu.innerHTML = '<div class="reg-autocomplete-empty">No match — you can continue typing to register a new practice.</div>';
            menu.classList.add('open');
            return;
        }
        menu.innerHTML = practiceSuggestions.map((p, i) =>
            `<div class="reg-autocomplete-item ${i === practiceActiveIdx ? 'active' : ''}" role="option" data-idx="${i}" onmousedown="pickPractice(${i})">${escapeHtml(p.label)}</div>`
        ).join('');
        menu.classList.add('open');
    }

    function hidePracticeMenu() {
        const menu = document.getElementById('practice-suggest');
        menu.classList.remove('open');
        menu.innerHTML = '';
    }

    function onPracticeKey(e) {
        const menu = document.getElementById('practice-suggest');
        if (!menu.classList.contains('open') || practiceSuggestions.length === 0) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            practiceActiveIdx = (practiceActiveIdx + 1) % practiceSuggestions.length;
            renderPracticeMenu();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            practiceActiveIdx = (practiceActiveIdx - 1 + practiceSuggestions.length) % practiceSuggestions.length;
            renderPracticeMenu();
        } else if (e.key === 'Enter') {
            if (practiceActiveIdx >= 0) {
                e.preventDefault();
                pickPractice(practiceActiveIdx);
            }
        } else if (e.key === 'Escape') {
            hidePracticeMenu();
        }
    }

    function onPracticeBlur() { setTimeout(hidePracticeMenu, 150); }

    function pickPractice(idx) {
        const p = practiceSuggestions[idx];
        if (!p) return;
        document.getElementById('hid-practiceId').value = p.id;
        document.getElementById('in-practiceName').value = p.name;
        setVal('in-phone', p.phone_number);
        setVal('in-website', p.website);
        const cc = document.querySelector('select[name="practice_phone_country_code"]');
        if (cc && p.phone_country_code) cc.value = p.phone_country_code;
        setVal('in-address1', p.street_address_1);
        setVal('in-address2', p.street_address_2);
        const zipSel = document.getElementById('in-zip');
        if (zipSel) {
            let found = Array.from(zipSel.options).find(o => o.value == p.zip_id);
            if (!found && p.zip_id) {
                const opt = document.createElement('option');
                opt.value = p.zip_id;
                opt.textContent = (p.zip_code ?? '') + ' — ' + (p.city ?? '') + (p.state_code ? ', ' + p.state_code : '');
                zipSel.appendChild(opt);
            }
            zipSel.value = p.zip_id;
        }
        setVal('in-city',    p.city);
        setVal('in-state',   p.state);
        setVal('in-country', p.country);
        setVal('hid-city',    p.city_id);
        setVal('hid-state',   p.state_id);
        setVal('hid-country', p.country_id);
        lockPracticeFields();
        document.getElementById('practice-change-link').style.display = 'inline-block';
        hidePracticeMenu();
    }

    function clearPracticeSelection() {
        document.getElementById('hid-practiceId').value = '';
        ['in-practiceName','in-phone','in-website','in-address1','in-address2',
         'in-city','in-state','in-country','hid-city','hid-state','hid-country']
            .forEach(id => setVal(id, ''));
        const zipSel = document.getElementById('in-zip');
        if (zipSel) zipSel.selectedIndex = 0;
        unlockPracticeFields();
        document.getElementById('practice-change-link').style.display = 'none';
        document.getElementById('in-practiceName').focus();
    }

    function lockPracticeFields() {
        setLockedGroup('box-phone',     true);
        setLockedGroup('box-website',   true);
        setLockedGroup('box-address1',  true);
        setLockedGroup('box-address2',  true);
        ['in-phone','in-website','in-address1','in-address2'].forEach(id => setReadonly(id, true));
        const zipSel = document.getElementById('in-zip');
        if (zipSel) zipSel.classList.add('is-locked');
    }

    function unlockPracticeFields() {
        setLockedGroup('box-phone',     false);
        setLockedGroup('box-website',   false);
        setLockedGroup('box-address1',  false);
        setLockedGroup('box-address2',  false);
        ['in-phone','in-website','in-address1','in-address2'].forEach(id => setReadonly(id, false));
        const zipSel = document.getElementById('in-zip');
        if (zipSel) zipSel.classList.remove('is-locked');
    }

    function setLockedGroup(boxId, locked) {
        const el = document.getElementById(boxId);
        if (!el) return;
        el.classList.toggle('is-locked', locked);
    }
    function setReadonly(id, readonly) {
        const el = document.getElementById(id);
        if (!el) return;
        if (readonly) el.setAttribute('readonly', 'readonly');
        else          el.removeAttribute('readonly');
    }
    function setVal(id, v) {
        const el = document.getElementById(id);
        if (el) el.value = (v ?? '');
    }
    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // ─── Zip auto-fill ─────────────────────────────
    function onRegZipChange() {
        clearError('zip');
        const sel = document.getElementById('in-zip');
        const opt = sel?.options[sel.selectedIndex];
        const city    = document.getElementById('in-city');
        const state   = document.getElementById('in-state');
        const country = document.getElementById('in-country');
        const hCity   = document.getElementById('hid-city');
        const hState  = document.getElementById('hid-state');
        const hCty    = document.getElementById('hid-country');
        if (!opt || !opt.value) {
            [city, state, country, hCity, hState, hCty].forEach(el => { if (el) el.value = ''; });
            return;
        }
        if (city)    city.value    = opt.dataset.city    || '';
        if (state)   state.value   = opt.dataset.state   || '';
        if (country) country.value = opt.dataset.country || '';
        if (hCity)   hCity.value   = opt.dataset.cityId    || '';
        if (hState)  hState.value  = opt.dataset.stateId   || '';
        if (hCty)    hCty.value    = opt.dataset.countryId || '';
    }
    // Run once to populate readonly city/state/country if the server sent an old zip_id
    document.addEventListener('DOMContentLoaded', onRegZipChange);
    document.addEventListener('DOMContentLoaded', toggleContactViews);

    // ─── ScrollSpy Navigation ─────────────────────
    const navItems = document.querySelectorAll('#adminDoctorCreate .reg-nav-item');
    const sections = document.querySelectorAll('#adminDoctorCreate div[id^="step-"]');

    let isNavigating = false;
    let navTimer = null;

    function setActive(id) {
        navItems.forEach(item => item.classList.toggle('active', item.getAttribute('data-target') === id));
    }

    function updateActiveNav() {
        if (isNavigating) return;
        const triggerPoint = window.innerHeight * 0.2;
        let activeId = sections[0] ? sections[0].id : null;
        sections.forEach(sec => {
            if (sec.getBoundingClientRect().top <= triggerPoint) activeId = sec.id;
        });
        setActive(activeId);
    }

    window.addEventListener('scroll', updateActiveNav, { passive: true });
    updateActiveNav();

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const targetId = item.getAttribute('data-target');
            const target = document.getElementById(targetId);
            if (!target) return;
            isNavigating = true;
            clearTimeout(navTimer);
            setActive(targetId);
            navTimer = setTimeout(() => { isNavigating = false; updateActiveNav(); }, 900);
            window.scrollTo({
                top: window.scrollY + target.getBoundingClientRect().top - 80,
                behavior: 'smooth'
            });
        });
    });

    // ─── Form Validation ──────────────────────────
    const emailRe    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const nameRe     = /^[A-Za-z\s\-]+$/;
    const websiteRe  = /^(https?:\/\/)?([\da-z\.\-]+)\.([a-z\.]{2,6})([\/\w \.\-]*)*\/?$/i;
    const pwSpecial  = /[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\/]/;

    const VALIDATORS = {
        email: v => !v ? 'Email is required'
            : !emailRe.test(v) ? 'Please enter a valid email address' : '',
        firstName: v => !v ? 'First name is required'
            : v.length < 2 ? 'First name must be at least 2 characters'
            : !nameRe.test(v) ? 'Only letters, spaces, and hyphens are allowed' : '',
        lastName: v => !v ? 'Last name is required'
            : v.length < 2 ? 'Last name must be at least 2 characters'
            : !nameRe.test(v) ? 'Only letters, spaces, and hyphens are allowed' : '',
        password: v => !v ? 'Password is required'
            : (v.length < 8 || !/[A-Z]/.test(v) || !/[a-z]/.test(v) || !/\d/.test(v) || !pwSpecial.test(v))
                ? 'Min 8 characters with uppercase, lowercase, number & special character' : '',
        confirmPassword: (v, all) => {
            if (!v) return 'Please confirm your password';
            if (v !== all.password) return 'Passwords do not match';
            return '';
        },
        practiceName: v => !v ? 'Please select a practice' : '',
        phone: v => !v ? 'Phone number is required'
            : !/^\d{10}$/.test(v) ? 'Please enter a valid 10-digit phone number' : '',
        website: v => !v ? 'Website is required'
            : !websiteRe.test(v) ? 'Please enter a valid website (e.g. www.example.com)' : '',
        language: v => !v ? 'Please select a preferred language' : '',
        address1: v => !v ? 'Street address is required'
            : v.length < 5 ? 'Please enter a complete street address (min 5 characters)' : '',
        zip: v => !v ? 'Please select a zip code' : '',
    };

    const FIELD_SECTION = {
        email: 'step-account', firstName: 'step-account', lastName: 'step-account',
        password: 'step-account', confirmPassword: 'step-account',
        practiceName: 'step-practice', phone: 'step-practice',
        website: 'step-practice', language: 'step-practice',
        address1: 'step-address', zip: 'step-address',
    };

    const touched = new Set();

    function _fieldValue(fieldId) {
        const el = document.getElementById('in-' + fieldId);
        if (!el) return '';
        return (el.value || '').trim();
    }
    function _allValues() {
        const out = {};
        Object.keys(VALIDATORS).forEach(id => { out[id] = _fieldValue(id); });
        out.password = document.getElementById('in-password')?.value ?? '';
        out.confirmPassword = document.getElementById('in-confirmPassword')?.value ?? '';
        return out;
    }

    function showError(fieldId, errorMsg) {
        const errElement = document.getElementById('err-' + fieldId);
        const boxElement = document.getElementById('box-' + fieldId);
        const inElement  = document.getElementById('in-' + fieldId);
        if (errElement) { errElement.textContent = errorMsg; errElement.classList.remove('hidden'); }
        if (boxElement) boxElement.classList.add('is-invalid');
        else if (inElement) inElement.classList.add('is-invalid');
    }

    function _clearUI(fieldId) {
        const errElement = document.getElementById('err-' + fieldId);
        const boxElement = document.getElementById('box-' + fieldId);
        const inElement  = document.getElementById('in-' + fieldId);
        if (errElement) errElement.classList.add('hidden');
        if (boxElement) boxElement.classList.remove('is-invalid');
        else if (inElement) inElement.classList.remove('is-invalid');
    }

    function validateField(fieldId) {
        const v = VALIDATORS[fieldId];
        if (!v) return true;
        const all = _allValues();
        const msg = v(all[fieldId], all);
        if (msg) { showError(fieldId, msg); return false; }
        _clearUI(fieldId);
        return true;
    }

    function clearError(fieldId) {
        if (touched.has(fieldId)) {
            validateField(fieldId);
            if (fieldId === 'password' && touched.has('confirmPassword')) {
                validateField('confirmPassword');
            }
        } else {
            _clearUI(fieldId);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        Object.keys(VALIDATORS).forEach(fieldId => {
            const el = document.getElementById('in-' + fieldId);
            if (!el) return;
            el.addEventListener('blur', () => {
                touched.add(fieldId);
                validateField(fieldId);
                if (fieldId === 'password' && touched.has('confirmPassword')) {
                    validateField('confirmPassword');
                }
            });
            if (el.tagName === 'SELECT') {
                el.addEventListener('change', () => {
                    touched.add(fieldId);
                    validateField(fieldId);
                });
            }
        });

        const terms = document.querySelector('input[name="terms_agreed"]');
        if (terms) {
            terms.addEventListener('change', () => {
                const err = document.getElementById('err-terms');
                if (terms.checked && err) err.classList.add('hidden');
            });
        }
    });

    function validateForm() {
        Object.keys(VALIDATORS).forEach(id => touched.add(id));

        let isValid = true;
        let firstErrorSection = null;
        const mark = (id) => { if (!firstErrorSection) firstErrorSection = id; };

        Object.keys(VALIDATORS).forEach(fieldId => {
            if (!validateField(fieldId)) {
                isValid = false;
                mark(FIELD_SECTION[fieldId]);
            }
        });

        const zipVal = document.getElementById('in-zip')?.value;
        const hiddenCity = document.getElementById('hid-city')?.value;
        if (zipVal && !hiddenCity) {
            showError('zip', 'Zip lookup failed. Please reselect the zip code.');
            mark('step-address');
            isValid = false;
        }

        const termsCheckbox = document.querySelector('input[name="terms_agreed"]');
        if (termsCheckbox && !termsCheckbox.checked) {
            showError('terms', 'You must accept the Terms and Conditions to continue');
            mark('step-additional');
            isValid = false;
        }

        if (!isValid && firstErrorSection) {
            const target = document.getElementById(firstErrorSection);
            if (target) {
                isNavigating = true;
                clearTimeout(navTimer);
                setActive(firstErrorSection);
                navTimer = setTimeout(() => { isNavigating = false; updateActiveNav(); }, 900);
                window.scrollTo({
                    top: window.scrollY + target.getBoundingClientRect().top - 80,
                    behavior: 'smooth'
                });
            }
            return false;
        }

        if (isValid) {
            document.getElementById('registrationForm').submit();
        }
        return isValid;
    }
</script>
@endpush
