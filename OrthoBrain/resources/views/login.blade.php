<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>OrthoBrain Login</title>

        {{-- Vuexy theme stylesheets --}}
        <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/vendors.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/core.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/overrides.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/orthobrain-overrides.css') }}" />
        {{-- OrthoBrain palette — navy + Inter. Loaded LAST so it wins over Vuexy. --}}
        <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
            html, body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
            body {
                height: 100vh;
                overflow: hidden;
                background: #f8f8f8;
                color: #6e6b7b;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin: 0;
            }
            .ortho-auth-wrap { width: 100%; max-width: 440px; margin: 0 auto; padding: 0 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
            .ortho-card {
                background: #fff;
                border-radius: 0.5rem;
                padding: 1.5rem 2rem;
                box-shadow: 0 4px 24px 0 rgba(34,41,47,0.1);
                text-align: center;
            }
            .ortho-logo-text { font-size: 2rem; font-weight: 500; letter-spacing: -0.01em; margin-top: 0.375rem; line-height: 1; display: inline-flex; align-items: flex-start; }
            .ortho-logo-text .p1 { color: #5bc0de; }
            .ortho-logo-text .p2 { color: #8cc63f; }
            .ortho-logo-text .tm { color: #8cc63f; font-size: 0.7rem; margin-left: 1px; margin-top: 0.5rem; }
            .ortho-tagline { font-size: 11px; font-style: italic; color: #6e6b7b; margin-top: 0.125rem; letter-spacing: 0.025em; }
            .ortho-heading { font-size: 1.3rem; font-weight: 500; color: #5e5873; margin: 0 0 0.125rem; line-height: 1.2; }
            .ortho-sub { font-size: 0.85rem; color: #6e6b7b; margin: 0; }
            .ortho-label { display: block; font-size: 0.82rem; font-weight: 500; color: #5e5873; margin-bottom: 0.25rem; }
            .ortho-input-group {
                display: flex;
                align-items: center;
                border: 1px solid #d8d6de;
                border-radius: 0.358rem;
                overflow: hidden;
                background: #fff;
                transition: all .2s;
            }
            .ortho-input-group:focus-within { border-color: var(--ob-primary); box-shadow: 0 0 0 0.2rem rgba(59, 130, 246,0.25); }
            .ortho-input-group.is-invalid { border-color: #ea5455; }
            .ortho-input-group .input-icon {
                display: flex; align-items: center; justify-content: center;
                padding: 0.4rem 0.55rem;
                color: #b9b9c3;
                background: #f8f8f8;
                border-right: 1px solid #d8d6de;
                align-self: stretch;
            }
            .ortho-input-group.is-invalid .input-icon { border-right-color: #ea5455; }
            .ortho-input-group input {
                flex: 1;
                border: 0;
                outline: none;
                padding: 0.42rem 0.75rem;
                font-size: 0.9rem;
                color: #6e6b7b;
                background: transparent;
                min-width: 0;
            }
            .ortho-input-group input::placeholder { color: #b9b9c3; }
            .ortho-input-group .eye-toggle {
                padding: 0 0.75rem;
                cursor: pointer;
                color: #b9b9c3;
                display: flex;
                align-items: center;
            }
            .ortho-input-group .eye-toggle:hover { color: #6e6b7b; }
            .ortho-btn-primary {
                width: 100%;
                background: var(--ob-primary);
                border: 1px solid var(--ob-primary);
                color: #fff;
                font-weight: 500;
                border-radius: 0.358rem;
                padding: 0.55rem;
                font-size: 0.95rem;
                box-shadow: 0 2px 4px rgba(59, 130, 246,0.4);
                cursor: pointer;
                transition: background .2s, border-color .2s;
            }
            .ortho-btn-primary:hover { background: #46b8da; border-color: #46b8da; }
            .ortho-alert-success {
                background: #e2f8eb;
                border: 1px solid #28c76f;
                color: #28c76f;
                padding: 0.5rem 1rem;
                border-radius: 0.358rem;
                margin-bottom: 0.75rem;
                font-size: 0.85rem;
                text-align: left;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .ortho-link { color: var(--ob-primary); font-weight: 500; text-decoration: none; }
            .ortho-link:hover { color: #46b8da; }
            .ortho-error-text { color: #ea5455; font-size: 0.78rem; margin: 0.125rem 0 0; }
            .ortho-remember { display: inline-flex; align-items: center; cursor: pointer; }
            .ortho-remember input {
                width: 0.95rem; height: 0.95rem;
                margin: 0 0.5rem 0 0;
                accent-color: var(--ob-primary);
                cursor: pointer;
                border: 1px solid #d8d6de;
                border-radius: 3px;
            }
            .ortho-remember span { font-size: 0.85rem; color: #6e6b7b; }
            .ortho-field + .ortho-field { margin-top: 0.75rem; }
            .ortho-page-footer { text-align: center; font-size: 9.5px; color: #b9b9c3; line-height: 1.5; padding: 0 0.5rem; }
        </style>
    </head>
    <body>
        <div class="ortho-auth-wrap">

            {{-- Login Card --}}
            <div class="ortho-card">

                {{-- Logo --}}
                <div class="d-flex flex-column align-items-center" style="margin-bottom: 1.25rem;">
                    <svg width="52" height="44" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:-6px">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff" />
                        <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7M28 20c1.5 1.5 1.5 4 0 5M36 20c-1.5 1.5-1.5 4 0 5M19 33c2.5 1 3.5 4 1 6M45 33c-2.5 1-3.5 4-1 6" stroke="#b8b8b8" />
                        <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none" />
                    </svg>
                    <div class="ortho-logo-text">
                        <span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span>
                    </div>
                    <div class="ortho-tagline">Orthodontics for Your Dental Practice</div>
                </div>

                {{-- Welcome text --}}
                <div class="text-start mb-3">
                    <h1 class="ortho-heading">Welcome to Orthobrain!</h1>
                    <p class="ortho-sub">Please sign-in to your account</p>
                </div>

                @if (session('success'))
                    <div class="ortho-alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ url('/login') }}" method="POST" class="text-start" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="ortho-field">
                        <label for="email" class="ortho-label">Email</label>
                        <div class="ortho-input-group @error('email') is-invalid @enderror">
                            <span class="input-icon"><i class="bi bi-envelope"></i></span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email">
                        </div>
                        @error('email')
                            <p class="ortho-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="ortho-field">
                        <div class="d-flex align-items-center justify-content-between" style="margin-bottom: 0.25rem;">
                            <label for="password" class="ortho-label" style="margin-bottom: 0;">Password</label>
                            <a href="{{ url('/forgot-password') }}" class="ortho-link" style="font-size: 0.82rem;">Forgot Password?</a>
                        </div>
                        <div class="ortho-input-group @error('password') is-invalid @enderror">
                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                            <input id="password" name="password" type="password" placeholder="············">
                            <span class="eye-toggle" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash');">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        @error('password')
                            <p class="ortho-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="ortho-field">
                        <label class="ortho-remember">
                            <input type="checkbox" />
                            <span>Remember Me</span>
                        </label>
                    </div>

                    {{-- Sign In --}}
                    <div class="ortho-field pt-1">
                        <button type="submit" class="ortho-btn-primary">Sign in</button>
                    </div>
                </form>

                {{-- Card Footer --}}
                <div class="text-center" style="margin-top: 1rem; font-size: 0.85rem;">
                    <p style="color: #6e6b7b; margin: 0 0 0.125rem;">Request to become a provider at no cost</p>
                    <a href="{{ url('/register') }}" class="ortho-link">Create an account</a>
                    <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #5e5873; font-weight: 700; font-style: italic;">Designed for OrthoDentists&trade;</div>
                </div>
            </div>

            {{-- Page Footer --}}
            <div class="ortho-page-footer">
                <p>Copyright © 2025 orthobrain®. All rights reserved. Users are subject to the <a href="#" class="ortho-link">Agreement for Use</a>, <a href="#" class="ortho-link">User Content Policy</a>, and <a href="#" class="ortho-link">Independent Contractor Agreement</a>.</p>
            </div>

        </div>
    </body>
</html>
