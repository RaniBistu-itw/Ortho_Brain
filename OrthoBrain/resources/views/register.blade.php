<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Orthobrain Registration</title>

        {{-- Vuexy theme stylesheets --}}
        <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/vendors.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/core.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/overrides.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/orthobrain-overrides.css') }}" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        {{-- Scoped registration-page classes (used by ported sections; unported sections still use Tailwind CDN below) --}}
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap');
            html, body { font-family: 'Montserrat', ui-sans-serif, system-ui, sans-serif; }
            body.reg-body { min-height: 100vh; background: var(--ob-surface-2); color: var(--ob-text-muted); padding-bottom: 2.5rem; margin: 0; }

            .reg-shell { padding: 2rem 1.5rem; width: 100%; }
            @media (min-width: 640px) { .reg-shell { padding: 2rem 2rem; } }

            .reg-header { margin-bottom: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; }
            .reg-logo-text { font-size: 2.6rem; font-weight: 500; letter-spacing: -0.01em; margin-top: 0.5rem; line-height: 1.1; display: inline-flex; align-items: flex-start; }
            .reg-logo-text .p1 { color: #5bc0de; }
            .reg-logo-text .p2 { color: #8cc63f; }
            .reg-logo-text .tm { color: #8cc63f; font-size: 0.8rem; margin-left: 1px; margin-top: 0.625rem; }
            .reg-tagline { font-size: 12px; font-style: italic; color: var(--ob-text-muted); margin-top: 0.25rem; letter-spacing: 0.025em; }

            .reg-layout { display: flex; flex-direction: column; gap: 1.5rem; align-items: flex-start; position: relative; }
            @media (min-width: 768px) { .reg-layout { flex-direction: row; gap: 2rem; } }

            .reg-sidebar {
                width: 100%;
                flex-shrink: 0;
                background: #fff;
                border-radius: 0.358rem;
                box-shadow: 0 4px 24px 0 rgba(34,41,47,0.1);
                padding: 1.25rem;
                align-self: flex-start;
            }
            @media (min-width: 768px) { .reg-sidebar { width: 260px; position: sticky; top: 1.5rem; } }
            .reg-sidebar nav { display: flex; flex-direction: column; gap: 1rem; }
            .reg-nav-item { display: flex; align-items: center; gap: 1rem; cursor: pointer; }
            .reg-nav-icon {
                display: flex; align-items: center; justify-content: center;
                width: 2.5rem; height: 2.5rem;
                border-radius: 0.358rem;
                background: var(--ob-surface-2);
                color: var(--ob-text-muted);
                transition: all 0.2s;
                flex-shrink: 0;
            }
            .reg-nav-item .reg-nav-title { font-size: 0.95rem; color: var(--ob-text); font-weight: 500; transition: all 0.2s; }
            .reg-nav-item .reg-nav-sub { font-size: 0.8rem; color: var(--ob-text-muted); }
            .reg-nav-item.active .reg-nav-icon { background: var(--ob-primary); color: #fff; box-shadow: 0 2px 4px rgba(59, 130, 246,0.4); }
            .reg-nav-item.active .reg-nav-title { color: var(--ob-primary); }

            .reg-main { flex: 1; width: 100%; display: flex; flex-direction: column; gap: 1.5rem; }
            .reg-card {
                background: #fff;
                border-radius: 0.358rem;
                box-shadow: 0 4px 24px 0 rgba(34,41,47,0.1);
                padding: 1.5rem;
                scroll-margin-top: 1.5rem;
            }
            .reg-card-head { margin-bottom: 1.25rem; }
            .reg-card-head h2 { font-size: 1.3rem; font-weight: 500; color: var(--ob-text); margin: 0 0 0.25rem; }
            .reg-card-head p { font-size: 0.9rem; color: var(--ob-text-muted); margin: 0; }
            .reg-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
            @media (min-width: 768px) { .reg-grid { grid-template-columns: 1fr 1fr; } }
            .reg-col-span-2 { grid-column: span 1; }
            @media (min-width: 768px) { .reg-col-span-2 { grid-column: span 2; } }

            .reg-label { display: block; font-size: 0.85rem; font-weight: 500; color: var(--ob-text); margin-bottom: 0.25rem; }
            .reg-required { color: #ea5455; }
            .reg-input-group {
                display: flex;
                align-items: center;
                border: 1px solid var(--ob-border-strong);
                border-radius: 0.358rem;
                background: #fff;
                overflow: hidden;
                transition: all .2s;
            }
            .reg-input-group:focus-within { border-color: var(--ob-primary); box-shadow: 0 0 0 0.2rem rgba(59, 130, 246,0.25); }
            .reg-input-group.is-invalid { border-color: #ea5455; }
            .reg-input-icon {
                display: flex; align-items: center; justify-content: center;
                padding: 0.5rem 0.5rem 0.5rem 0.75rem;
                color: var(--ob-text-muted);
                background: #fff;
                border-right: 1px solid var(--ob-border-strong);
                align-self: stretch;
            }
            .reg-input-group.is-invalid .reg-input-icon { border-right-color: #ea5455; }
            .reg-input {
                flex: 1;
                border: 0;
                outline: none;
                padding: 0.5rem 0.75rem;
                font-size: 0.95rem;
                color: var(--ob-text-muted);
                background: transparent;
                min-width: 0;
            }
            .reg-input::placeholder { color: var(--ob-text-muted); }
            .reg-select {
                width: 100%;
                height: 2.5rem;
                padding: 0 0.75rem;
                background: #fff;
                border: 1px solid var(--ob-border-strong);
                border-radius: 0.358rem;
                outline: none;
                font-size: 0.95rem;
                color: var(--ob-text-muted);
                appearance: none;
                -webkit-appearance: none;
            }
            .reg-select:focus { border-color: var(--ob-primary); box-shadow: 0 0 0 0.2rem rgba(59, 130, 246,0.25); }
            .reg-select.is-invalid { border-color: #ea5455; }
            .reg-phone {
                display: flex;
                border: 1px solid var(--ob-border-strong);
                border-radius: 0.358rem;
                background: #fff;
                transition: all .2s;
                overflow: hidden;
            }
            .reg-phone:focus-within { border-color: var(--ob-primary); box-shadow: 0 0 0 0.2rem rgba(59, 130, 246,0.25); }
            .reg-phone.is-invalid { border-color: #ea5455; }
            .reg-phone select {
                padding: 0 0.75rem;
                background: var(--ob-surface-2);
                border: 0;
                border-right: 1px solid var(--ob-border-strong);
                color: var(--ob-text-muted);
                font-size: 0.9rem;
                outline: none;
            }
            .reg-phone input {
                flex: 1;
                border: 0;
                outline: none;
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
                color: var(--ob-text-muted);
                background: transparent;
                min-width: 0;
            }
            .reg-err { color: #ea5455; font-size: 0.8rem; margin: 0.25rem 0 0; }
            .reg-err.hidden { display: none; }
            /* neutralize Vuexy/Bootstrap default margins that inflate gaps inside our cards */
            .reg-card input, .reg-card select, .reg-card textarea, .reg-card button, .reg-card label { margin: 0; }
            .reg-card .reg-label { margin-bottom: 0.25rem; }

            /* Locked (read-only) state when an existing practice is picked */
            .reg-input-group.is-locked { background: var(--ob-surface-2); }
            .reg-input-group.is-locked input { background: transparent; }
            .reg-phone.is-locked { background: var(--ob-surface-2); }
            .reg-phone.is-locked select, .reg-phone.is-locked input { background: transparent; pointer-events: none; }
            .reg-select.is-locked { background: var(--ob-surface-2); pointer-events: none; }

            /* Autocomplete wrapper + menu */
            .reg-autocomplete { position: relative; }
            .reg-autocomplete-menu {
                position: absolute;
                top: calc(100% + 4px);
                left: 0;
                right: 0;
                background: #fff;
                border: 1px solid var(--ob-border-strong);
                border-radius: 0.358rem;
                box-shadow: 0 4px 12px rgba(34,41,47,0.08);
                max-height: 260px;
                overflow-y: auto;
                z-index: 100;
                display: none;
            }
            .reg-autocomplete-menu.open { display: block; }
            .reg-autocomplete-item {
                padding: 0.5rem 0.75rem;
                cursor: pointer;
                font-size: 0.9rem;
                color: var(--ob-text-muted);
                border-bottom: 1px solid #f6f6f6;
            }
            .reg-autocomplete-item:last-child { border-bottom: 0; }
            .reg-autocomplete-item:hover,
            .reg-autocomplete-item.active { background: var(--ob-surface-2); color: var(--ob-text); }
            .reg-autocomplete-empty { padding: 0.5rem 0.75rem; font-size: 0.85rem; color: var(--ob-text-muted); font-style: italic; }
            .reg-change-link {
                display: inline-block;
                margin-top: 0.25rem;
                font-size: 0.8rem;
                color: var(--ob-primary);
                cursor: pointer;
                text-decoration: none;
            }
            .reg-change-link:hover { text-decoration: underline; }

            /* Additional card */
            .reg-card-head-with-toggle { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem; }
            .reg-expand-btn { background: var(--ob-primary); color: #fff; border: 0; border-radius: 0.358rem; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer; transition: background .2s; }
            .reg-expand-btn:hover { background: #46b8da; }
            .reg-additional-body { display: flex; flex-direction: column; gap: 1.5rem; transition: all .3s; }
            .reg-additional-body.hidden { display: none; }

            .reg-section-label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--ob-text); margin-bottom: 0.5rem; }
            .reg-option-list { display: flex; flex-direction: column; gap: 0.25rem; }
            .reg-option-grid { display: grid; grid-template-columns: 1fr; row-gap: 0.25rem; column-gap: 1rem; }
            @media (min-width: 640px) { .reg-option-grid { grid-template-columns: 1fr 1fr; } }
            .reg-option { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem; color: var(--ob-text-muted); }
            .reg-option.align-start { align-items: flex-start; }
            .reg-option input[type="radio"], .reg-option input[type="checkbox"] { accent-color: var(--ob-primary); width: 1rem; height: 1rem; min-width: 1rem; cursor: pointer; flex-shrink: 0; }

            .reg-form-box { background: #fbfbfb; border: 1px solid var(--ob-border-strong); border-radius: 0.358rem; padding: 1.25rem; }
            .reg-form-box.hidden { display: none; }
            .reg-email-row { display: flex; gap: 0.5rem; }
            .reg-email-row .reg-input-group { flex: 1; }
            .reg-btn-delete { width: 2.5rem; height: 2.5rem; background: rgba(234,84,85,0.125); color: #ea5455; border: 0; border-radius: 0.358rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background .2s; }
            .reg-btn-delete:hover { background: rgba(234,84,85,0.2); }
            .reg-btn-primary { background: var(--ob-primary); color: #fff; border: 0; padding: 0.5rem 1rem; border-radius: 0.358rem; font-size: 0.85rem; font-weight: 500; cursor: pointer; box-shadow: 0 2px 4px rgba(59, 130, 246,0.3); transition: background .2s; }
            .reg-btn-primary:hover { background: #46b8da; }

            /* Doctor Preferences panel */
            .reg-pref-panel { background: #fcfcfc; border: 1px solid var(--ob-border); border-radius: 0.358rem; overflow: hidden; display: flex; flex-direction: column; }
            .reg-pref-header-bar { background: #fff; border-bottom: 1px solid var(--ob-border); padding: 0.75rem 1rem; font-weight: 700; color: var(--ob-text); font-size: 0.95rem; }
            .reg-pref-scroll { overflow-y: auto; max-height: 500px; background: #fff; position: relative; }
            .reg-pref-scroll::-webkit-scrollbar { width: 6px; }
            .reg-pref-scroll::-webkit-scrollbar-track { background: #fcfcfc; }
            .reg-pref-scroll::-webkit-scrollbar-thumb { background: var(--ob-border-strong); border-radius: 4px; }
            .reg-pref-scroll::-webkit-scrollbar-thumb:hover { background: var(--ob-text-muted); }
            .reg-pref-section { padding: 1.25rem 1rem 1rem; border-bottom: 1px solid var(--ob-border); }
            .reg-pref-title { font-weight: 500; color: var(--ob-text); margin: 0 0 0.75rem; font-size: 0.95rem; }
            .reg-pref-list { display: flex; flex-direction: column; gap: 0.5rem; }
            .reg-pref-item { display: flex; align-items: center; cursor: pointer; }
            .reg-pref-item.align-start { align-items: flex-start; }
            .reg-pref-item input[type="radio"], .reg-pref-item input[type="checkbox"] { accent-color: var(--ob-primary); margin-right: 0.5rem; width: 1rem; height: 1rem; cursor: pointer; flex-shrink: 0; }
            .reg-pref-item.align-start input { margin-top: 0.25rem; }
            .reg-pref-item span { color: var(--ob-text-muted); font-size: 0.95rem; }

            /* Toggle switches */
            .reg-toggle-group { display: flex; flex-direction: column; gap: 1.25rem; }
            .reg-toggle-label { display: flex; align-items: center; cursor: pointer; }
            .reg-toggle-switch { position: relative; width: 2.5rem; height: 22px; }
            .reg-toggle-switch input.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
            .reg-toggle-bg { display: block; width: 2.5rem; height: 22px; background: var(--ob-border); border-radius: 9999px; transition: background .2s; }
            .reg-toggle-dot { position: absolute; width: 1rem; height: 1rem; background: #fff; border-radius: 50%; top: 3px; right: 3px; transform: translateX(-125%); transition: transform .2s; box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
            .reg-toggle-switch input.sr-only:checked ~ .reg-toggle-bg { background: var(--ob-primary); }
            .reg-toggle-switch input.sr-only:checked ~ .reg-toggle-dot { transform: translateX(0); }
            .reg-toggle-text { margin-left: 0.75rem; font-size: 0.95rem; color: var(--ob-text-muted); }
            .reg-toggle-panel { margin-top: 0.75rem; padding: 1rem; background: var(--ob-surface-2); border: 1px solid var(--ob-border); border-radius: 0.358rem; display: flex; flex-direction: column; gap: 0.5rem; }
            .reg-toggle-panel.hidden { display: none; }

            .reg-back-to-top { position: sticky; bottom: 0; background: var(--ob-primary); color: #fff; width: 2rem; height: 2rem; border-radius: 0.358rem; display: flex; justify-content: center; align-items: center; cursor: pointer; opacity: 0.9; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: -2rem; float: right; z-index: 10; margin-right: 0.5rem; }
            .reg-back-to-top:hover { background: #46b8da; }

            /* Terms + actions */
            .reg-terms-wrap { margin-top: 1.5rem; padding: 0 0.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
            .reg-terms-label { display: flex; align-items: flex-start; color: var(--ob-text-muted); font-size: 0.9rem; cursor: pointer; }
            .reg-terms-label input { margin: 0.2rem 0.75rem 0 0 !important; width: 1.1rem; height: 1.1rem; accent-color: var(--ob-primary); cursor: pointer; flex-shrink: 0; }
            .reg-terms-link { color: var(--ob-primary); text-decoration: none; }
            .reg-terms-link:hover { text-decoration: underline; }
            .reg-actions { display: flex; justify-content: flex-end; align-items: center; gap: 1rem; margin-top: 1.5rem; border-top: 1px solid var(--ob-border); padding-top: 1.5rem; }
            .reg-btn-back { padding: 0.6rem 1.25rem; background: var(--ob-primary); color: #fff; border-radius: 0.358rem; font-weight: 500; text-decoration: none; font-size: 0.95rem; box-shadow: 0 2px 4px rgba(59, 130, 246,0.3); transition: background .2s; }
            .reg-btn-back:hover { background: #46b8da; color: #fff; }
            .reg-btn-submit { padding: 0.6rem 1.25rem; background: #8cc63f; color: #fff; border: 0; border-radius: 0.358rem; font-weight: 500; font-size: 0.95rem; letter-spacing: 0.025em; cursor: pointer; box-shadow: 0 2px 4px rgba(140,198,63,0.3); transition: background .2s; }
            .reg-btn-submit:hover { background: #7cb038; }
        </style>

        <style>
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus,
            input:-webkit-autofill:active {
                -webkit-box-shadow: 0 0 0 30px #fff9e6 inset !important;
                background-color: #fff9e6 !important;
            }
            html { scroll-behavior: smooth; }
        </style>
    </head>
    <body class="reg-body">
        <div class="reg-shell">

            <header class="reg-header">
                <div style="display:flex; flex-direction:column; align-items:center;">
                    <svg width="70" height="60" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:-8px">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff" />
                        <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7M28 20c1.5 1.5 1.5 4 0 5M36 20c-1.5 1.5-1.5 4 0 5M19 33c2.5 1 3.5 4 1 6M45 33c-2.5 1-3.5 4-1 6" stroke="#b8b8b8" />
                        <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none" />
                    </svg>
                    <div class="reg-logo-text">
                        <span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span>
                    </div>
                    <div class="reg-tagline">Orthodontics for Your Dental Practice</div>
                </div>
            </header>

            <div class="reg-layout">

                <aside class="reg-sidebar">
                    <nav id="scrollspy-nav">
                        {{-- Account --}}
                        <div class="reg-nav-item active" data-target="step-account">
                            <div class="reg-nav-icon"><i class="bi bi-house-door" style="font-size:1.15rem"></i></div>
                            <div>
                                <div class="reg-nav-title">Account</div>
                                <div class="reg-nav-sub">Account Details</div>
                            </div>
                        </div>

                        {{-- Practice --}}
                        <div class="reg-nav-item" data-target="step-practice">
                            <div class="reg-nav-icon"><i class="bi bi-building" style="font-size:1.15rem"></i></div>
                            <div>
                                <div class="reg-nav-title">Practice</div>
                                <div class="reg-nav-sub">Practice Information</div>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="reg-nav-item" data-target="step-address">
                            <div class="reg-nav-icon"><i class="bi bi-geo-alt" style="font-size:1.15rem"></i></div>
                            <div>
                                <div class="reg-nav-title">Address</div>
                                <div class="reg-nav-sub">Address Information</div>
                            </div>
                        </div>

                        {{-- Additional --}}
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
                    {{-- Surface server-side validation errors so silent bounce-backs are impossible --}}
                    @if($errors->any())
                        <div style="background:#fdecea;border:1px solid #f5c6cb;color:#721c24;padding:0.85rem 1rem;border-radius:6px;margin-bottom:1rem;">
                            <strong>Please fix the following before continuing:</strong>
                            <ul style="margin:0.4rem 0 0 1.2rem;padding:0;">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="registrationForm" action="{{ url('/register') }}" method="POST" novalidate onsubmit="return validateForm(event)">
                        @csrf

                        {{-- Doctor Information Card --}}
                        <div id="step-account" class="reg-card">
                            <div class="reg-card-head">
                                <h2>Doctor Information</h2>
                                <p>Enter your account details</p>
                            </div>
                            <div class="reg-grid">
                                {{-- Email --}}
                                <div class="reg-col-span-2">
                                    <label class="reg-label">Email (Username)<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-email">
                                        <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                        <input id="in-email" name="email" type="email" required title="Please enter an email address" class="reg-input" placeholder="Enter email" value="{{ old('email') }}" oninput="clearError('email')" />
                                    </div>
                                    <p id="err-email" class="reg-err hidden"></p>
                                </div>
                                {{-- First Name --}}
                                <div>
                                    <label class="reg-label">First Name<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-firstName">
                                        <span class="reg-input-icon"><i class="bi bi-person"></i></span>
                                        <input id="in-firstName" name="first_name" type="text" class="reg-input" placeholder="Enter first name" value="{{ old('first_name') }}" oninput="clearError('firstName')" />
                                    </div>
                                    <p id="err-firstName" class="reg-err hidden"></p>
                                </div>
                                {{-- Last Name --}}
                                <div>
                                    <label class="reg-label">Last Name<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-lastName">
                                        <span class="reg-input-icon"><i class="bi bi-person"></i></span>
                                        <input id="in-lastName" name="last_name" type="text" class="reg-input" placeholder="Enter last name" value="{{ old('last_name') }}" oninput="clearError('lastName')" />
                                    </div>
                                    <p id="err-lastName" class="reg-err hidden"></p>
                                </div>
                                {{-- Password --}}
                                <div>
                                    <label class="reg-label">Password<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-password">
                                        <span class="reg-input-icon"><i class="bi bi-lock"></i></span>
                                        <input id="in-password" name="password" type="password" class="reg-input" placeholder="&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;" oninput="clearError('password')" />
                                        <span class="reg-input-icon" style="border-right:0; border-left:1px solid var(--ob-border-strong); cursor:pointer;" onclick="const p=document.getElementById('in-password');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash');">
                                            <i class="bi bi-eye-slash"></i>
                                        </span>
                                    </div>
                                    <p id="err-password" class="reg-err hidden"></p>
                                </div>
                                {{-- Confirm Password --}}
                                <div>
                                    <label class="reg-label">Confirm Password<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-confirmPassword">
                                        <span class="reg-input-icon"><i class="bi bi-lock"></i></span>
                                        <input id="in-confirmPassword" name="confirm_password" type="password" class="reg-input" placeholder="&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;&middot;" oninput="clearError('confirmPassword')" />
                                        <span class="reg-input-icon" style="border-right:0; border-left:1px solid var(--ob-border-strong); cursor:pointer;" onclick="const p=document.getElementById('in-confirmPassword');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash');">
                                            <i class="bi bi-eye-slash"></i>
                                        </span>
                                    </div>
                                    <p id="err-confirmPassword" class="reg-err hidden"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Practice Information Card --}}
                        <div id="step-practice" class="reg-card">
                            <div class="reg-card-head">
                                <h2>Practice Information</h2>
                                <p>Enter your practice details</p>
                            </div>
                            <div class="reg-grid">
                                {{-- Practice Name (autocomplete) --}}
                                <div>
                                    <label class="reg-label">Practice Name<span class="reg-required">*</span></label>
                                    <div class="reg-autocomplete">
                                        {{-- Hidden: set when an existing practice is picked; empty = new practice --}}
                                        <input type="hidden" id="hid-practiceId" name="practice_id" value="{{ old('practice_id') }}">
                                        <div class="reg-input-group" id="box-practiceName">
                                            <span class="reg-input-icon"><i class="bi bi-building"></i></span>
                                            <input id="in-practiceName" name="practice_name" type="text"
                                                   autocomplete="off"
                                                   class="reg-input"
                                                   placeholder="Start typing your practice name…"
                                                   value="{{ old('practice_name') }}"
                                                   oninput="onPracticeInput(event)"
                                                   onkeydown="onPracticeKey(event)"
                                                   onblur="onPracticeBlur(event)" />
                                        </div>
                                        <div id="practice-suggest" class="reg-autocomplete-menu" role="listbox"></div>
                                    </div>
                                    <a id="practice-change-link" class="reg-change-link" style="display:none" onclick="clearPracticeSelection()">Change practice</a>
                                    <p id="err-practiceName" class="reg-err hidden"></p>
                                </div>
                                {{-- Practice Phone Number --}}
                                <div>
                                    <label class="reg-label">Practice Phone Number<span class="reg-required">*</span></label>
                                    <div class="reg-phone" id="box-phone">
                                        <span class="reg-input-icon" style="border-right:0"><i class="bi bi-telephone"></i></span>
                                        <select name="practice_phone_country_code">
                                            <option value="+1_US">+1 (US)</option>
                                            <option value="+1_CA">+1 (CA)</option>
                                            <option value="+61_AU">+61 (AU)</option>
                                        </select>
                                        <input id="in-phone" name="practice_phone_number" type="text" maxlength="10" placeholder="XXX-XXX-XXXX" value="{{ old('practice_phone_number') }}" oninput="clearError('phone')" />
                                    </div>
                                    <p id="err-phone" class="reg-err hidden"></p>
                                </div>
                                {{-- Practice Website --}}
                                <div>
                                    <label class="reg-label">Practice Website<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-website">
                                        <span class="reg-input-icon"><i class="bi bi-globe"></i></span>
                                        <input id="in-website" type="text" name="practice_website" class="reg-input" placeholder="www.example.com" value="{{ old('practice_website') }}" oninput="clearError('website')" />
                                    </div>
                                    <p id="err-website" class="reg-err hidden"></p>
                                </div>
                                {{-- Preferred Language --}}
                                <div>
                                    <label class="reg-label">Preferred Language<span class="reg-required">*</span></label>
                                    <select id="in-language" name="preferred_language" required class="reg-select" onchange="clearError('language')">
                                        <option value="English" selected>English</option>
                                        <option value="Spanish">Spanish</option>
                                        <option value="French">French</option>
                                    </select>
                                    <p id="err-language" class="reg-err hidden"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Address Information Card --}}
                        <div id="step-address" class="reg-card">
                            <div class="reg-card-head">
                                <h2>Address Information</h2>
                                <p>Enter your address details</p>
                            </div>
                            {{-- Hidden inputs populated by the zip auto-fill JS --}}
                            <input type="hidden" name="city_id"    id="hid-city">
                            <input type="hidden" name="state_id"   id="hid-state">
                            <input type="hidden" name="country_id" id="hid-country">

                            <div class="reg-grid">
                                {{-- Street Address --}}
                                <div>
                                    <label class="reg-label">Street Address<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-address1">
                                        <span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                        <input id="in-address1" type="text" name="street_address_1" class="reg-input" placeholder="Street address 1" value="{{ old('street_address_1') }}" oninput="clearError('address1')" />
                                    </div>
                                    <p id="err-address1" class="reg-err hidden"></p>
                                </div>
                                {{-- Street Address 2 --}}
                                <div>
                                    <label class="reg-label">Street Address 2</label>
                                    <div class="reg-input-group" id="box-address2">
                                        <span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                        <input id="in-address2" type="text" name="street_address_2" class="reg-input" placeholder="Street address 2" value="{{ old('street_address_2') }}" />
                                    </div>
                                </div>
                                {{-- Zip (master-driven) --}}
                                <div>
                                    <label class="reg-label">Zip<span class="reg-required">*</span></label>
                                    <select id="in-zip" name="zip_id" required class="reg-select" onchange="onRegZipChange()">
                                        <option value="" disabled {{ old('zip_id') ? '' : 'selected' }}>Select zip code</option>
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
                                </div>
                                {{-- City (auto-filled, readonly) --}}
                                <div>
                                    <label class="reg-label">City<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" style="background:var(--ob-surface-2)">
                                        <input id="in-city" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                                {{-- State/Province (auto-filled, readonly) --}}
                                <div>
                                    <label class="reg-label">State/Province<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" style="background:var(--ob-surface-2)">
                                        <input id="in-state" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                                {{-- Country (auto-filled, readonly) --}}
                                <div>
                                    <label class="reg-label">Country<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" style="background:var(--ob-surface-2)">
                                        <input id="in-country" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Practices (optional) --}}
                        <div id="step-extra-practices" class="reg-card">
                            <div class="reg-card-head">
                                <h2>Other Practices You Work At <span style="font-weight:400;font-size:0.9rem;color:var(--ob-text-muted);">(optional)</span></h2>
                                <p>Add up to 5 other practices. Pick existing ones from the search, or create new ones inline. Each is reviewed by the admin separately.</p>
                            </div>

                            <div id="extra-practice-rows"></div>

                            <div style="margin-top:1rem;">
                                <button type="button" id="btn-add-extra-practice" onclick="addExtraPracticeRow()"
                                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.1rem;background:#fff;border:1px dashed var(--ob-primary);color:var(--ob-primary);font-weight:600;border-radius:6px;cursor:pointer;transition:all 0.15s ease;"
                                        onmouseover="this.style.background='var(--ob-primary)';this.style.color='#fff';"
                                        onmouseout="this.style.background='#fff';this.style.color='var(--ob-primary)';">
                                    <i class="bi bi-plus-circle" style="font-size:1.05rem;"></i>
                                    <span>Add another practice</span>
                                </button>
                                <small class="text-muted" style="margin-left:0.75rem;">Up to 5 additional practices.</small>
                            </div>

                            {{-- Server-side old() dump: if validation bounces the form,
                                 this JSON contains the rows the doctor had entered so JS
                                 can rebuild them on load (see DOMContentLoaded handler). --}}
                            @if(old('additional_practices'))
                                <script type="application/json" id="extra-old-data">@json(old('additional_practices'))</script>
                            @endif

                            {{-- Zip options reused per-row in JS template (avoids repeating the @foreach above) --}}
                            <template id="extra-prac-zip-options">
                                <option value="" disabled selected>Select zip code</option>
                                @foreach(($zipcodes ?? []) as $z)
                                    <option value="{{ $z->id }}"
                                            data-city-id="{{ $z->city?->id }}"
                                            data-city="{{ $z->city?->name }}"
                                            data-state-id="{{ $z->city?->state?->id }}"
                                            data-state="{{ $z->city?->state?->name }}"
                                            data-country-id="{{ $z->city?->state?->country?->id }}"
                                            data-country="{{ $z->city?->state?->country?->name }}">
                                        {{ $z->code }} — {{ $z->city?->name }}, {{ $z->city?->state?->state_code }}
                                    </option>
                                @endforeach
                            </template>
                        </div>

                        {{-- Additional --}}
                        <div id="step-additional" class="reg-card">
                            <div class="reg-card-head-with-toggle">
                                <div>
                                    <h2 style="font-size:1.3rem; font-weight:500; color:var(--ob-text); margin:0 0 0.25rem;">Additional Doctor Information</h2>
                                    <p style="font-size:0.9rem; color:var(--ob-text-muted); margin:0;">This information will automatically be saved to your account for all future submissions. You may edit this information at any time by visiting the My Profile tab.</p>
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
                                        <label class="reg-option"><input type="radio" name="providing_ortho" value="yes"> Yes</label>
                                        <label class="reg-option"><input type="radio" name="providing_ortho" value="no"> No</label>
                                    </div>
                                </div>

                                {{-- Modalities (master-driven) --}}
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

                                {{-- Specialties (master-driven) --}}
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
                                        <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="doctor"> Doctor Only</label>
                                        <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="employee"> Employee/Office</label>
                                        <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="both"> Doctor and Employee/Office</label>
                                    </div>

                                    <div id="contact-forms-container" style="display:flex; flex-direction:column; gap:1rem;">
                                        {{-- Doctor Form Box --}}
                                        <div id="form-box-doctor" class="reg-form-box hidden">
                                            <div class="reg-grid" style="margin-bottom:1.25rem;">
                                                <div>
                                                    <label class="reg-label">Doctor Email<span class="reg-required">*</span></label>
                                                    <div class="reg-input-group">
                                                        <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                                        <input type="email" name="contact_doctor_email" class="reg-input" placeholder="name@example.com" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="reg-label">Doctor Cell Phone Number</label>
                                                    <div class="reg-input-group">
                                                        <span class="reg-input-icon"><i class="bi bi-telephone"></i></span>
                                                        <input type="text" name="contact_doctor_phone" class="reg-input" placeholder="XXX-XXX-XXXX" />
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
                                                        <input type="text" name="contact_emp_name" class="reg-input" placeholder="Enter name" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="reg-label">Employee Title<span class="reg-required">*</span></label>
                                                    <div class="reg-input-group">
                                                        <span class="reg-input-icon"><i class="bi bi-briefcase"></i></span>
                                                        <input type="text" name="contact_emp_title" class="reg-input" placeholder="Enter title" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="reg-label">Office/Employee Email<span class="reg-required">*</span></label>
                                                    <div class="reg-input-group">
                                                        <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                                        <input type="email" name="contact_emp_email" class="reg-input" placeholder="Enter office/employee email" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="reg-label">Office/Employee Cell Phone Number<span class="reg-required">*</span></label>
                                                    <div class="reg-input-group">
                                                        <span class="reg-input-icon"><i class="bi bi-telephone"></i></span>
                                                        <input type="text" name="contact_emp_phone" class="reg-input" placeholder="XXX-XXX-XXXX" />
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

                        {{-- Global: Terms and SMS Checkboxes --}}
                        <div class="reg-terms-wrap">
                            <label class="reg-terms-label">
                                <input type="checkbox" name="terms_agreed" onchange="clearError('terms')" />
                                <span>By creating an account at orthobrain you accept the <a href="#" class="reg-terms-link">Terms and Conditions</a>.<span class="reg-required">*</span></span>
                            </label>
                            <p id="err-terms" class="reg-err hidden"></p>
                            <label class="reg-terms-label">
                                <input type="checkbox" name="sms_agreed" />
                                <span style="display:inline-flex; align-items:center;">I agree to receive SMS messages for authentication purposes.
                                    <i class="bi bi-info-circle" style="margin-left:0.375rem; color:var(--ob-text); opacity:0.7; cursor:pointer;"></i>
                                </span>
                            </label>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="reg-actions">
                            <a href="{{ url('/login') }}" class="reg-btn-back">Back to Login</a>
                            <button type="button" onclick="validateForm()" class="reg-btn-submit">Submit for Approval</button>
                        </div>
                    </form>
                </main>
            </div>
        </div>

        <script>
            // Additional Doctor Info UI Logic
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
                    if (selected.value === 'doctor') {
                        docForm.classList.remove('hidden');
                    } else if (selected.value === 'employee') {
                        empForm.classList.remove('hidden');
                    } else if (selected.value === 'both') {
                        docForm.classList.remove('hidden');
                        empForm.classList.remove('hidden');
                    }
                }
            }

            function toggleIprNote() {
                const iprSelect = document.getElementById('in-ipr-protocol');
                const noteContainer = document.getElementById('ipr-note-container');
                if (iprSelect && iprSelect.value === 'OTHER') {
                    noteContainer.classList.remove('hidden');
                } else {
                    noteContainer.classList.add('hidden');
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

            // ────────────────────────────────────────────────────────────────
            //  Practice autocomplete
            //    – Debounced AJAX to /practice-search (min 2 chars)
            //    – Keyboard navigation (↑ ↓ Enter Esc)
            //    – Pick a suggestion → fills practice + address fields, locks them
            //    – "Change practice" link clears selection and unlocks
            // ────────────────────────────────────────────────────────────────
            const PRACTICE_SEARCH_URL = @json(route('practice.search'));
            let practiceSearchTimer    = null;
            let practiceSearchAbort    = null;
            let practiceSuggestions    = [];
            let practiceActiveIdx      = -1;

            function onPracticeInput(e) {
                clearError('practiceName');
                // Any typing clears a previously-picked practice (they're choosing again)
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

            function onPracticeBlur() {
                // Delay so click on a menu item registers before the menu hides.
                setTimeout(hidePracticeMenu, 150);
            }

            function pickPractice(idx) {
                const p = practiceSuggestions[idx];
                if (!p) return;

                document.getElementById('hid-practiceId').value = p.id;
                document.getElementById('in-practiceName').value = p.name;

                // Practice info
                setVal('in-phone', p.phone_number);
                setVal('in-website', p.website);
                const cc = document.querySelector('select[name="practice_phone_country_code"]');
                if (cc && p.phone_country_code) cc.value = p.phone_country_code;

                // Address info
                setVal('in-address1', p.street_address_1);
                setVal('in-address2', p.street_address_2);

                // Zip select — if it contains a matching option, select it; otherwise inject one so the form still submits.
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
                setReadonly('in-practiceName', false);                       // keep editable so user can clear + search again
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

            // ── Zip auto-fill: reads data-* on the selected option and populates
            //    city / state / country readonly fields + hidden FK inputs.
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

            // ScrollSpy Navigation Logic
            const navItems = document.querySelectorAll('.reg-nav-item');
            const sections = document.querySelectorAll('div[id^="step-"]');

            let isNavigating = false;
            let navTimer = null;

            function setActive(id) {
                navItems.forEach(item => {
                    if (item.getAttribute('data-target') === id) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });
            }

            function updateActiveNav() {
                if (isNavigating) return;
                const triggerPoint = window.innerHeight * 0.2;
                let activeId = sections[0] ? sections[0].id : null;
                sections.forEach(sec => {
                    if (sec.getBoundingClientRect().top <= triggerPoint) {
                        activeId = sec.id;
                    }
                });
                setActive(activeId);
            }

            window.addEventListener('scroll', updateActiveNav, { passive: true });
            updateActiveNav();

            // Smooth Scroll on nav item click
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    const targetId = item.getAttribute('data-target');
                    const target = document.getElementById(targetId);
                    if (!target) return;

                    isNavigating = true;
                    clearTimeout(navTimer);
                    setActive(targetId);

                    navTimer = setTimeout(() => {
                        isNavigating = false;
                        updateActiveNav();
                    }, 900);

                    window.scrollTo({
                        top: window.scrollY + target.getBoundingClientRect().top - 24,
                        behavior: 'smooth'
                    });
                });
            });

            // ── Form Validation Logic ──────────────────────────────
            const emailRe    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const nameRe     = /^[A-Za-z\s\-]+$/;
            const websiteRe  = /^(https?:\/\/)?([\da-z\.\-]+)\.([a-z\.]{2,6})([\/\w \.\-]*)*\/?$/i;
            const pwSpecial  = /[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\/]/;

            // Per-field validators: id → (value) → '' when OK, else error message.
            // The `all` arg is used for cross-field rules (e.g. confirmPassword).
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

            // Which section each field belongs to (for scroll-on-submit-error).
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
                // password is compared raw (no trim) for confirmPassword rule; keep raw separately
                out.password = document.getElementById('in-password')?.value ?? '';
                out.confirmPassword = document.getElementById('in-confirmPassword')?.value ?? '';
                return out;
            }

            function showError(fieldId, errorMsg) {
                const errElement = document.getElementById('err-' + fieldId);
                const boxElement = document.getElementById('box-' + fieldId);
                const inElement = document.getElementById('in-' + fieldId);
                if (errElement) { errElement.textContent = errorMsg; errElement.classList.remove('hidden'); }
                if (boxElement) boxElement.classList.add('border-red-500');
                else if (inElement) inElement.classList.add('border-red-500');
            }

            function _clearUI(fieldId) {
                const errElement = document.getElementById('err-' + fieldId);
                const boxElement = document.getElementById('box-' + fieldId);
                const inElement = document.getElementById('in-' + fieldId);
                if (errElement) errElement.classList.add('hidden');
                if (boxElement) boxElement.classList.remove('border-red-500');
                else if (inElement) inElement.classList.remove('border-red-500');
            }

            // Validate one field. Used by blur/input listeners and by submit.
            function validateField(fieldId) {
                const v = VALIDATORS[fieldId];
                if (!v) return true;
                const all = _allValues();
                const msg = v(all[fieldId], all);
                if (msg) { showError(fieldId, msg); return false; }
                _clearUI(fieldId);
                return true;
            }

            // Called by `oninput="clearError(...)"` in the markup.
            // If the field has been touched (blurred once), re-run validation live
            // so the error updates as the user fixes / re-breaks the field.
            function clearError(fieldId) {
                if (touched.has(fieldId)) {
                    validateField(fieldId);
                    // cross-field: retyping password should re-check confirmPassword
                    if (fieldId === 'password' && touched.has('confirmPassword')) {
                        validateField('confirmPassword');
                    }
                } else {
                    _clearUI(fieldId);
                }
            }

            // Wire blur + change listeners for live validation.
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

                // Terms checkbox live feedback
                const terms = document.querySelector('input[name="terms_agreed"]');
                if (terms) {
                    terms.addEventListener('change', () => {
                        const err = document.getElementById('err-terms');
                        if (terms.checked && err) err.classList.add('hidden');
                    });
                }
            });

            // Submit handler — validate every field, scroll to first error section.
            function validateForm() {
                // Mark everything touched so all errors surface for someone who
                // clicked Submit without interacting with the form.
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

                // Zip picked but city_id wasn't populated (auto-fill failed) — block submit.
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
                            top: window.scrollY + target.getBoundingClientRect().top - 24,
                            behavior: 'smooth'
                        });
                    }
                    return false;
                }

                // Per-row validation for additional practices — shows inline errors
                // on each row's fields. Failing rows keep the doctor on the page
                // instead of making the round-trip to the server.
                let extraRowsOk = true;
                document.querySelectorAll('.extra-prac-row').forEach(row => {
                    if (!epValidateRow(row.dataset.idx)) extraRowsOk = false;
                });

                if (isValid && !extraRowsOk) {
                    // Scroll to the first invalid extra-row so the doctor sees the error
                    const firstBad = document.querySelector('.extra-prac-row .reg-err:not(.hidden)');
                    if (firstBad) {
                        window.scrollTo({
                            top: window.scrollY + firstBad.getBoundingClientRect().top - 100,
                            behavior: 'smooth'
                        });
                    }
                    return false;
                }

                if (isValid) {
                    // Drop incomplete extra-practice rows — see cleanExtraRows() below.
                    cleanExtraRows();
                    document.getElementById('registrationForm').submit();
                }
                return isValid;
            }

            // ────────────────────────────────────────────────────────────────
            //  Other Practices (optional) — repeating rows, two modes per row:
            //    EXISTING : autocomplete-pick from /practice-search, then
            //               row locks into a readonly summary card ("Change" to re-open)
            //    NEW      : full inline form with live per-field validation on blur
            //
            //  Each row submits as additional_practices[<idx>][...] so the controller
            //  can validate per-row with required_if rules.
            //  On validation failure, the server dumps old('additional_practices') into
            //  the #extra-old-data element and this JS rebuilds rows with prior data.
            // ────────────────────────────────────────────────────────────────
            let extraPracticeSeq = 0;
            const MAX_EXTRA_PRACTICES = 5;
            const extraRowState = {};   // idx -> {mode, picked: {id,name,phone,website,city,...}}

            const epWebsiteRe = /^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z]{2,})+$/i;

            function addExtraPracticeRow() {
                const visible = document.querySelectorAll('.extra-prac-row').length;
                if (visible >= MAX_EXTRA_PRACTICES) {
                    alert('You can add up to ' + MAX_EXTRA_PRACTICES + ' additional practices.');
                    return;
                }
                const idx = extraPracticeSeq++;

                const wrap = document.createElement('div');
                wrap.className = 'extra-prac-row';
                wrap.style.cssText = 'border:1px solid #e0dee8;border-radius:8px;padding:1rem;margin-top:0.75rem;background:#fafafd;position:relative;';
                wrap.dataset.idx = idx;

                wrap.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.75rem;">
                        <div style="display:flex;gap:1rem;align-items:center;">
                            <strong style="color:var(--ob-text);">Practice <span class="ep-label-num">#</span></strong>
                            <label style="margin:0;display:inline-flex;align-items:center;gap:0.35rem;cursor:pointer;">
                                <input type="radio" name="additional_practices[${idx}][mode]" value="existing" checked onchange="onExtraModeChange(${idx})"> Existing
                            </label>
                            <label style="margin:0;display:inline-flex;align-items:center;gap:0.35rem;cursor:pointer;">
                                <input type="radio" name="additional_practices[${idx}][mode]" value="new" onchange="onExtraModeChange(${idx})"> Create new
                            </label>
                        </div>
                        <button type="button" class="reg-btn-delete" onclick="removeExtraRow(${idx})" title="Remove"><i class="bi bi-trash"></i></button>
                    </div>

                    {{-- EXISTING mode --}}
                    <div id="ep-existing-${idx}" class="ep-pane">
                        <div style="position:relative;">
                            <input type="hidden" name="additional_practices[${idx}][practice_id]" id="ep-id-${idx}" value="">
                            <div class="reg-input-group">
                                <span class="reg-input-icon"><i class="bi bi-search"></i></span>
                                <input type="text" id="ep-search-${idx}" class="reg-input"
                                       placeholder="Search existing practice by name…"
                                       autocomplete="off"
                                       oninput="onExtraSearch(${idx}, event)"
                                       onblur="setTimeout(() => hideExtraMenu(${idx}), 150)" />
                            </div>
                            <div id="ep-menu-${idx}" class="reg-autocomplete-menu" role="listbox"></div>
                        </div>
                        <p class="reg-err hidden" id="ep-err-existing-${idx}"></p>
                    </div>

                    {{-- NEW mode --}}
                    <div id="ep-new-${idx}" class="ep-pane" style="display:none;">
                        <div class="reg-grid">
                            <div>
                                <label class="reg-label">Practice Name<span class="reg-required">*</span></label>
                                <div class="reg-input-group"><span class="reg-input-icon"><i class="bi bi-building"></i></span>
                                    <input type="text" name="additional_practices[${idx}][name]" class="reg-input" placeholder="Practice name"
                                           onblur="epValidateField(${idx}, 'name')"
                                           oninput="epValidateField(${idx}, 'name')" />
                                </div>
                                <p class="reg-err hidden ep-err-name-${idx}"></p>
                            </div>
                            <div>
                                <label class="reg-label">Phone Number<span class="reg-required">*</span></label>
                                <div class="reg-phone">
                                    <span class="reg-input-icon" style="border-right:0"><i class="bi bi-telephone"></i></span>
                                    <select name="additional_practices[${idx}][phone_country_code]">
                                        <option value="+1_US">+1 (US)</option>
                                        <option value="+1_CA">+1 (CA)</option>
                                        <option value="+61_AU">+61 (AU)</option>
                                    </select>
                                    <input type="text" name="additional_practices[${idx}][phone_number]" maxlength="10"
                                           placeholder="10 digits, no dashes"
                                           onblur="epValidateField(${idx}, 'phone_number')"
                                           oninput="epValidateField(${idx}, 'phone_number')" />
                                </div>
                                <p class="reg-err hidden ep-err-phone_number-${idx}"></p>
                            </div>
                            <div>
                                <label class="reg-label">Website<span class="reg-required">*</span></label>
                                <div class="reg-input-group"><span class="reg-input-icon"><i class="bi bi-globe"></i></span>
                                    <input type="text" name="additional_practices[${idx}][website]" class="reg-input" placeholder="www.example.com"
                                           onblur="epValidateField(${idx}, 'website')"
                                           oninput="epValidateField(${idx}, 'website')" />
                                </div>
                                <p class="reg-err hidden ep-err-website-${idx}"></p>
                            </div>
                            <div>
                                <label class="reg-label">Street Address<span class="reg-required">*</span></label>
                                <div class="reg-input-group"><span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" name="additional_practices[${idx}][street_address_1]" class="reg-input" placeholder="Street address 1"
                                           onblur="epValidateField(${idx}, 'street_address_1')"
                                           oninput="epValidateField(${idx}, 'street_address_1')" />
                                </div>
                                <p class="reg-err hidden ep-err-street_address_1-${idx}"></p>
                            </div>
                            <div>
                                <label class="reg-label">Street Address 2</label>
                                <div class="reg-input-group"><span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" name="additional_practices[${idx}][street_address_2]" class="reg-input" placeholder="Street address 2 (optional)" />
                                </div>
                            </div>
                            <div>
                                <label class="reg-label">Zip<span class="reg-required">*</span></label>
                                <input type="hidden" name="additional_practices[${idx}][city_id]"    id="ep-h-city-${idx}">
                                <input type="hidden" name="additional_practices[${idx}][state_id]"   id="ep-h-state-${idx}">
                                <input type="hidden" name="additional_practices[${idx}][country_id]" id="ep-h-country-${idx}">
                                <select name="additional_practices[${idx}][zip_id]" id="ep-zip-${idx}" class="reg-select" onchange="onExtraZipChange(${idx})"></select>
                                <p class="reg-err hidden ep-err-zip-${idx}"></p>
                            </div>
                            <div>
                                <label class="reg-label">City</label>
                                <div class="reg-input-group" style="background:var(--ob-surface-2)">
                                    <input type="text" id="ep-city-${idx}" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" readonly />
                                </div>
                            </div>
                            <div>
                                <label class="reg-label">State / Country</label>
                                <div class="reg-input-group" style="background:var(--ob-surface-2)">
                                    <input type="text" id="ep-state-${idx}" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('extra-practice-rows').appendChild(wrap);

                // Clone zip options from the template
                const zipSel = document.getElementById('ep-zip-' + idx);
                const tpl = document.getElementById('extra-prac-zip-options');
                if (zipSel && tpl) zipSel.innerHTML = tpl.innerHTML;

                renumberExtraRows();
            }

            function removeExtraRow(idx) {
                const row = document.querySelector(`.extra-prac-row[data-idx="${idx}"]`);
                if (row) row.remove();
                delete extraRowState[idx];
                renumberExtraRows();
            }

            function renumberExtraRows() {
                document.querySelectorAll('.extra-prac-row').forEach((row, i) => {
                    const lbl = row.querySelector('.ep-label-num');
                    if (lbl) lbl.textContent = String(i + 1);
                });
            }

            function onExtraModeChange(idx) {
                const mode = document.querySelector(`input[name="additional_practices[${idx}][mode]"]:checked`)?.value;
                const ex = document.getElementById('ep-existing-' + idx);
                const nw = document.getElementById('ep-new-' + idx);
                if (mode === 'new') { ex.style.display = 'none'; nw.style.display = ''; }
                else                { ex.style.display = '';     nw.style.display = 'none'; }
            }

            const extraSearchState = {};
            async function onExtraSearch(idx, e) {
                document.getElementById('ep-id-' + idx).value = '';
                const q = e.target.value.trim();
                clearTimeout(extraSearchState[idx]?.timer);
                if (q.length < 2) { hideExtraMenu(idx); return; }
                extraSearchState[idx] = extraSearchState[idx] || {};
                extraSearchState[idx].timer = setTimeout(async () => {
                    try {
                        const res = await fetch(PRACTICE_SEARCH_URL + '?q=' + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (!res.ok) return;
                        const items = await res.json();
                        const primaryId = document.getElementById('hid-practiceId').value;
                        const alreadyPicked = Array.from(document.querySelectorAll('input[name^="additional_practices"][name$="[practice_id]"]'))
                            .map(i => i.value).filter(Boolean);
                        const filtered = items.filter(p => String(p.id) !== String(primaryId) && !alreadyPicked.includes(String(p.id)));
                        // Save results per-row so pick handler can look them up by index (avoids HTML-escape issues with names containing quotes)
                        extraSearchState[idx].items = filtered;
                        renderExtraMenu(idx, filtered);
                    } catch (err) { /* ignore */ }
                }, 300);
            }

            function renderExtraMenu(idx, items) {
                const menu = document.getElementById('ep-menu-' + idx);
                if (!menu) return;
                if (!items.length) {
                    menu.innerHTML = '<div class="reg-autocomplete-empty">No match. Switch to "Create new" to register a new practice.</div>';
                } else {
                    menu.innerHTML = items.map((p, i) =>
                        `<div class="reg-autocomplete-item" role="option" data-item-idx="${i}" onmousedown="pickExtraExistingByIndex(${idx}, ${i})">${escapeHtml(p.label)}</div>`
                    ).join('');
                }
                menu.classList.add('open');
            }

            function hideExtraMenu(idx) {
                const menu = document.getElementById('ep-menu-' + idx);
                if (!menu) return;
                menu.classList.remove('open');
                menu.innerHTML = '';
            }

            // Called by the dropdown; looks up the picked item from the per-row state
            // so names / special characters are never HTML-interpolated.
            function pickExtraExistingByIndex(idx, itemIdx) {
                const items = extraSearchState[idx]?.items;
                if (!items || !items[itemIdx]) return;
                lockExtraRowAsPicked(idx, items[itemIdx]);
            }

            // Replace the row's existing-mode pane with a readonly summary card.
            // Hidden inputs for additional_practices[idx][practice_id] + mode stay in place.
            function lockExtraRowAsPicked(idx, p) {
                extraRowState[idx] = { mode: 'existing', picked: p };

                document.getElementById('ep-id-' + idx).value = p.id;
                const pane = document.getElementById('ep-existing-' + idx);
                if (!pane) return;

                const addressLine = [p.street_address_1, p.city, p.state_code].filter(Boolean).join(', ');
                pane.innerHTML = `
                    <input type="hidden" name="additional_practices[${idx}][practice_id]" id="ep-id-${idx}" value="${p.id}">
                    <div style="background:#f0f9ff;border:1px solid #b6e3fa;border-radius:6px;padding:0.85rem 1rem;display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <strong style="color:var(--ob-text);">${escapeHtml(p.name)}</strong>
                                <span style="font-size:0.75rem;color:var(--ob-primary);background:#ece9fb;padding:0.15rem 0.55rem;border-radius:10rem;font-weight:600;">Existing</span>
                            </div>
                            <div style="font-size:0.82rem;color:var(--ob-text-muted);margin-top:0.4rem;display:flex;flex-wrap:wrap;gap:0.65rem 1.25rem;">
                                ${p.website ? `<span><i class="bi bi-globe"></i> ${escapeHtml(p.website)}</span>` : ''}
                                ${p.phone_number ? `<span><i class="bi bi-telephone"></i> ${escapeHtml(p.phone_number)}</span>` : ''}
                                ${addressLine ? `<span><i class="bi bi-geo-alt"></i> ${escapeHtml(addressLine)}</span>` : ''}
                            </div>
                        </div>
                        <button type="button" class="reg-change-link" onclick="unlockExtraRow(${idx})">Change</button>
                    </div>
                `;
            }

            function unlockExtraRow(idx) {
                const pane = document.getElementById('ep-existing-' + idx);
                if (!pane) return;
                extraRowState[idx] = { mode: 'existing', picked: null };
                pane.innerHTML = `
                    <div style="position:relative;">
                        <input type="hidden" name="additional_practices[${idx}][practice_id]" id="ep-id-${idx}" value="">
                        <div class="reg-input-group">
                            <span class="reg-input-icon"><i class="bi bi-search"></i></span>
                            <input type="text" id="ep-search-${idx}" class="reg-input"
                                   placeholder="Search existing practice by name…"
                                   autocomplete="off"
                                   oninput="onExtraSearch(${idx}, event)"
                                   onblur="setTimeout(() => hideExtraMenu(${idx}), 150)" />
                        </div>
                        <div id="ep-menu-${idx}" class="reg-autocomplete-menu" role="listbox"></div>
                    </div>
                    <p class="reg-err hidden" id="ep-err-existing-${idx}"></p>
                `;
            }

            function onExtraZipChange(idx) {
                const sel = document.getElementById('ep-zip-' + idx);
                const opt = sel?.options[sel.selectedIndex];
                if (!opt || !opt.value) return;
                document.getElementById('ep-city-' + idx).value     = opt.dataset.city || '';
                document.getElementById('ep-state-' + idx).value    = (opt.dataset.state || '') + (opt.dataset.country ? ' / ' + opt.dataset.country : '');
                document.getElementById('ep-h-city-' + idx).value    = opt.dataset.cityId || '';
                document.getElementById('ep-h-state-' + idx).value   = opt.dataset.stateId || '';
                document.getElementById('ep-h-country-' + idx).value = opt.dataset.countryId || '';
                epValidateField(idx, 'zip');
            }

            // ── Per-row inline validation for NEW-mode fields ───────────────
            //    Fires on blur (via onblur attribute set below) and on submit.
            //    Shows/clears the reg-err paragraph under each field, not a top banner.
            function epValidateField(idx, field) {
                const row = document.querySelector(`.extra-prac-row[data-idx="${idx}"]`);
                if (!row) return true;
                const get = (name) => row.querySelector(`[name="additional_practices[${idx}][${name}]"]`);
                const setErr = (sel, msg) => {
                    const el = row.querySelector(sel);
                    if (!el) return;
                    if (msg) { el.textContent = msg; el.classList.remove('hidden'); }
                    else     { el.textContent = '';  el.classList.add('hidden'); }
                };

                let msg = '';
                const val = (get(field)?.value || '').trim();
                switch (field) {
                    case 'name':
                        msg = !val ? 'Practice name is required' : (val.length < 2 ? 'At least 2 characters' : '');
                        break;
                    case 'phone_number':
                        msg = !val ? 'Phone number is required'
                            : !/^\d{10}$/.test(val) ? 'Phone must be 10 digits (no spaces or dashes)' : '';
                        break;
                    case 'website':
                        msg = !val ? 'Website is required'
                            : !epWebsiteRe.test(val) ? 'Enter a valid website (e.g. www.example.com)' : '';
                        break;
                    case 'street_address_1':
                        msg = !val ? 'Street address is required'
                            : val.length < 5 ? 'At least 5 characters' : '';
                        break;
                    case 'zip':
                        msg = !get('zip_id')?.value ? 'Please select a zip code' : '';
                        break;
                }
                setErr(`.ep-err-${field}-${idx}`, msg);
                return !msg;
            }

            function epValidateRow(idx) {
                const row = document.querySelector(`.extra-prac-row[data-idx="${idx}"]`);
                if (!row) return true;
                const mode = row.querySelector(`input[name="additional_practices[${idx}][mode]"]:checked`)?.value || 'existing';
                if (mode === 'existing') {
                    const pid = row.querySelector(`input[name="additional_practices[${idx}][practice_id]"]`)?.value;
                    return !!pid;   // if they picked, row is valid; otherwise cleaned on submit
                }
                let ok = true;
                ['name','phone_number','website','street_address_1','zip'].forEach(f => {
                    if (!epValidateField(idx, f)) ok = false;
                });
                return ok;
            }

            // Drop rows that are clearly empty so the server doesn't see junk:
            //  - existing mode with no practice_id picked
            //  - new mode with no practice name typed
            function cleanExtraRows() {
                document.querySelectorAll('.extra-prac-row').forEach(row => {
                    const idx = row.dataset.idx;
                    const mode = row.querySelector(`input[name="additional_practices[${idx}][mode]"]:checked`)?.value || 'existing';
                    if (mode === 'existing') {
                        const hid = row.querySelector(`input[name="additional_practices[${idx}][practice_id]"]`);
                        if (!hid || !hid.value) row.remove();
                    } else {
                        const nameInp = row.querySelector(`input[name="additional_practices[${idx}][name]"]`);
                        if (!nameInp || !nameInp.value.trim()) row.remove();
                    }
                });
            }

            // On load: if the server bounced the form back with old('additional_practices'),
            // rebuild the rows from that data so the doctor doesn't lose what they typed.
            document.addEventListener('DOMContentLoaded', () => {
                const dataEl = document.getElementById('extra-old-data');
                if (!dataEl) return;
                let rows = [];
                try { rows = JSON.parse(dataEl.textContent || '[]'); } catch (e) { return; }
                if (!Array.isArray(rows) || !rows.length) return;

                rows.forEach(row => {
                    addExtraPracticeRow();
                    const idx = extraPracticeSeq - 1;
                    const wrap = document.querySelector(`.extra-prac-row[data-idx="${idx}"]`);
                    if (!wrap) return;

                    const mode = (row.mode === 'new') ? 'new' : 'existing';
                    wrap.querySelector(`input[name="additional_practices[${idx}][mode]"][value="${mode}"]`).checked = true;
                    onExtraModeChange(idx);

                    if (mode === 'new') {
                        ['name','website','phone_country_code','phone_number','street_address_1','street_address_2','zip_id','city_id','state_id','country_id'].forEach(k => {
                            const el = wrap.querySelector(`[name="additional_practices[${idx}][${k}]"]`);
                            if (el && row[k] != null) el.value = row[k];
                        });
                        // Re-run zip change to refresh visible city/state fields
                        if (row.zip_id) onExtraZipChange(idx);
                    } else if (row.practice_id) {
                        // Just stash the ID; we don't have the full practice details to render a locked card,
                        // so show the search field with "(prev. selected: #id)" placeholder — admin form will re-resolve on submit.
                        const hid = wrap.querySelector(`input[name="additional_practices[${idx}][practice_id]"]`);
                        if (hid) hid.value = row.practice_id;
                        const search = wrap.querySelector(`#ep-search-${idx}`);
                        if (search) search.placeholder = 'Previously selected — re-pick if you want to change';
                    }
                });
            });
        </script>
    </body>
</html>
