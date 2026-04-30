<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>OrthoBrain — Sign in</title>

        {{-- OrthoBrain palette (Inter + Public Sans, brand tokens) --}}
        <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        @if(config('captcha.site_key'))
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('captcha.site_key') }}"></script>
        @endif

        <style>
            *, *::before, *::after { box-sizing: border-box; }
            html, body {
                margin: 0;
                height: 100%;
                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
                color: var(--ob-text, #1E293B);
                background: #fff;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            /* ---------------- Animations ---------------- */
            @keyframes ob-fade-up {
                from { opacity: 0; transform: translateY(14px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes ob-fade-in {
                from { opacity: 0; }
                to   { opacity: 1; }
            }
            @keyframes ob-doctor-in {
                from { opacity: 0; transform: translateX(-50%) translateY(40px) scale(0.98); }
                to   { opacity: 1; transform: translateX(-50%) translateY(0) scale(1); }
            }
            @keyframes ob-bg-zoom {
                from { transform: scale(1.06); }
                to   { transform: scale(1); }
            }
            @keyframes ob-btn-shimmer {
                0%   { background-position: 0% 50%; }
                100% { background-position: 100% 50%; }
            }

            /* ---------------- Split shell ---------------- */
            .auth-shell {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
                min-height: 100vh;
                width: 100%;
            }

            /* ---------------- Left brand panel ---------------- */
            .auth-brand {
                position: relative;
                overflow: hidden;
                background:
                    linear-gradient(110deg, rgba(219, 234, 254, 0.55) 0%, rgba(219, 234, 254, 0.25) 55%, rgba(255, 255, 255, 0.95) 100%),
                    url('{{ asset('images/auth/clinic-bg.png') }}') center / cover no-repeat,
                    linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 50%, #E0F2FE 100%);
                isolation: isolate;
            }
            /* Cool blue tint + fade-to-right that blends into the form panel */
            .auth-brand::before {
                content: '';
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(90deg, rgba(96, 165, 250, 0.18) 0%, rgba(96, 165, 250, 0.08) 60%, rgba(255, 255, 255, 0.85) 100%);
                pointer-events: none;
                z-index: 1;
            }
            /* Faint tooth pattern, bottom-right of brand panel */
            .auth-brand::after {
                content: '';
                position: absolute;
                right: -2rem;
                bottom: 0;
                width: 60%;
                height: 28%;
                background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 80' fill='none' stroke='%2393C5FD' stroke-width='1.2' stroke-linecap='round' stroke-linejoin='round'><g opacity='0.55'><path d='M20 70 C 20 40, 35 25, 50 25 C 65 25, 80 40, 80 70 Z'/><path d='M110 70 C 110 40, 125 25, 140 25 C 155 25, 170 40, 170 70 Z'/><path d='M200 70 C 200 40, 215 25, 230 25 C 245 25, 260 40, 260 70 Z'/><path d='M290 70 C 290 40, 305 25, 320 25 C 335 25, 350 40, 350 70 Z'/><path d='M380 70 C 380 40, 395 25, 410 25 C 425 25, 440 40, 440 70 Z'/><path d='M470 70 C 470 40, 485 25, 500 25 C 515 25, 530 40, 530 70 Z'/></g></svg>");
                background-repeat: no-repeat;
                background-size: 100% 100%;
                opacity: 0.45;
                pointer-events: none;
                z-index: 2;
            }

            .brand-logo {
                position: absolute;
                top: 1.75rem;
                left: 2rem;
                display: inline-flex;
                align-items: center;
                gap: 0.6rem;
                z-index: 4;
                animation: ob-fade-up 0.7s cubic-bezier(.2,.8,.2,1) 0.15s both;
            }
            .brand-logo .logo-mark {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: #fff;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .brand-logo .logo-mark svg { width: 26px; height: 26px; }
            .brand-logo .logo-name {
                display: inline-flex;
                align-items: flex-start;
                font-size: 1.1rem;
                font-weight: 500;
                letter-spacing: -0.01em;
                line-height: 1;
            }
            .brand-logo .logo-name .p1 { color: #5bc0de; }
            .brand-logo .logo-name .p2 { color: #8cc63f; }
            .brand-logo .logo-name .tm { color: #8cc63f; font-size: 0.6em; margin-left: 1px; margin-top: 0.15rem; }

            .brand-doctor {
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                height: 94%;
                width: auto;
                max-width: none;
                object-fit: contain;
                object-position: bottom center;
                z-index: 3;
                user-select: none;
                -webkit-user-drag: none;
                filter: drop-shadow(0 24px 40px rgba(15, 23, 42, 0.14));
                animation: ob-doctor-in 1.1s cubic-bezier(.16,.84,.3,1) 0.25s both;
                transition: filter .4s ease;
            }
            .auth-brand:hover .brand-doctor {
                filter: drop-shadow(0 28px 48px rgba(15, 23, 42, 0.18));
            }
            /* Very-wide brand panels: cap doctor width so he doesn't sprawl past panel edges */
            @media (min-width: 1400px) {
                .brand-doctor { height: auto; width: 88%; max-height: 96%; }
            }

            /* ---------------- Right form panel ---------------- */
            .auth-form-panel {
                position: relative;
                background: #FFFFFF;
                display: flex;
                flex-direction: column;
                padding: 3rem clamp(1.5rem, 6vw, 5.5rem) 1.5rem;
            }

            /* ---------------- Tooth mascot (cursor-tracking eyes) ---------------- */
            .form-inner { position: relative; }
            .tooth-mascot-wrap {
                position: absolute;
                top: -1rem;
                right: 2.5rem;
                width: 90px;
                height: 100px;
                z-index: 5;
                animation: ob-fade-up 0.8s cubic-bezier(.2,.8,.2,1) 0.6s both,
                           tooth-bob 4s ease-in-out 1.4s infinite;
            }
            .tooth-mascot {
                width: 100%;
                height: 100%;
                display: block;
                user-select: none;
                cursor: pointer;
                filter: drop-shadow(0 6px 14px rgba(15, 23, 42, 0.12));
            }
            @keyframes tooth-bob {
                0%, 100% { transform: translateY(0); }
                50%      { transform: translateY(-6px); }
            }
            .tooth-mascot .eye-pupil {
                transition: transform 0.08s linear;
                transform-box: fill-box;
                transform-origin: center;
            }

            /* Alternate eye / mouth shapes — hidden until a state class is applied */
            .tooth-mascot .eye-closed,
            .tooth-mascot .mouth-sad,
            .tooth-mascot .mouth-shy { display: none; }

            /* Closed eyes — happens while typing password */
            .tooth-mascot.is-closed .eye-white,
            .tooth-mascot.is-closed .eye-pupil { display: none; }
            .tooth-mascot.is-closed .eye-closed { display: inline; }

            /* Sad reaction — wrong password */
            .tooth-mascot.is-sad .mouth-smile { display: none; }
            .tooth-mascot.is-sad .mouth-sad { display: inline; }
            .tooth-mascot.is-sad { animation: tooth-shake 0.5s cubic-bezier(.36,.07,.19,.97) 2; }
            @keyframes tooth-shake {
                10%, 90%  { transform: translateX(-2px); }
                20%, 80%  { transform: translateX(4px);  }
                30%, 50%, 70% { transform: translateX(-6px); }
                40%, 60%  { transform: translateX(6px);  }
            }

            /* Shy reaction — on hover/touch */
            .tooth-mascot .blush { transition: opacity .25s ease; }
            .tooth-mascot.is-shy .mouth-smile { display: none; }
            .tooth-mascot.is-shy .mouth-shy { display: inline; }
            .tooth-mascot.is-shy .blush { opacity: 0.95; }

            /* Speech bubble (cloud) */
            .tooth-bubble {
                position: absolute;
                bottom: calc(100% - 8px);
                right: 60px;
                background: #ffffff;
                border: 1.5px solid #e2e8f0;
                color: #334155;
                font-size: 0.75rem;
                font-weight: 500;
                line-height: 1.3;
                padding: 0.45rem 0.7rem;
                border-radius: 14px;
                white-space: nowrap;
                box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
                opacity: 0;
                transform: translateY(6px) scale(0.85);
                transform-origin: bottom right;
                transition: opacity .2s ease, transform .2s cubic-bezier(.2,.8,.2,1);
                pointer-events: none;
            }
            .tooth-bubble::after {
                content: '';
                position: absolute;
                bottom: -7px;
                right: 14px;
                width: 12px;
                height: 12px;
                background: #ffffff;
                border-right: 1.5px solid #e2e8f0;
                border-bottom: 1.5px solid #e2e8f0;
                transform: rotate(45deg);
            }
            .tooth-mascot-wrap.is-shy .tooth-bubble,
            .tooth-mascot-wrap.show-bubble .tooth-bubble {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            @media (max-width: 960px) { .tooth-mascot-wrap { display: none; } }

            .form-inner {
                width: 100%;
                max-width: 460px;
                margin: auto;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }

            /* Form panel — staggered entrance */
            .form-inner > * {
                animation: ob-fade-up 0.65s cubic-bezier(.2,.8,.2,1) both;
            }
            .form-inner > .form-mark    { animation-delay: 0.30s; }
            .form-inner > .form-eyebrow { animation-delay: 0.40s; }
            .form-inner > .form-heading { animation-delay: 0.48s; }
            .form-inner > .form-sub     { animation-delay: 0.56s; }
            .form-inner > .ortho-form   { animation-delay: 0.66s; }
            /* Form fields — sub-stagger inside the form */
            .ortho-form > * { animation: ob-fade-up 0.55s cubic-bezier(.2,.8,.2,1) both; }
            .ortho-form > *:nth-child(1) { animation-delay: 0.78s; }
            .ortho-form > *:nth-child(2) { animation-delay: 0.86s; }
            .ortho-form > *:nth-child(3) { animation-delay: 0.94s; }
            .ortho-form > *:nth-child(4) { animation-delay: 1.02s; }
            .ortho-form > *:nth-child(5) { animation-delay: 1.10s; }

            .form-mark {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                margin-bottom: 0.25rem;
            }
            .form-mark svg { width: 32px; height: 32px; }
            .form-mark .name {
                display: inline-flex;
                align-items: flex-start;
                font-size: 1.85rem;
                font-weight: 500;
                letter-spacing: -0.01em;
                line-height: 1;
            }
            .form-mark .name .p1 { color: #5bc0de; }
            .form-mark .name .p2 { color: #8cc63f; }
            .form-mark .name .tm { color: #8cc63f; font-size: 0.42em; margin-left: 2px; margin-top: 0.25rem; font-weight: 500; }

            .form-eyebrow {
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.18em;
                color: var(--ob-text-muted, #64748B);
                text-transform: uppercase;
                margin: 1rem 0 0.5rem;
            }
            .form-heading {
                font-family: 'Public Sans', 'Inter', sans-serif;
                font-size: clamp(1.75rem, 2.6vw, 2.25rem);
                font-weight: 700;
                line-height: 1.18;
                letter-spacing: -0.025em;
                color: #0F172A;
                margin: 0;
            }
            .form-sub {
                font-size: 0.95rem;
                line-height: 1.55;
                color: var(--ob-text-muted, #64748B);
                margin: 0.5rem 0 0.25rem;
            }

            /* ---------------- Form ---------------- */
            .ortho-form { display: flex; flex-direction: column; gap: 1rem; margin-top: 0.75rem; }
            .field { display: flex; flex-direction: column; gap: 0.4rem; }
            .field-row {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 0.5rem;
            }
            .field-label {
                font-size: 0.85rem;
                font-weight: 500;
                color: #1E293B;
                margin: 0;
            }
            .field-label .req { color: #EF4444; margin-left: 2px; }
            .field-link {
                font-size: 0.82rem;
                font-weight: 500;
                color: var(--ob-primary, #3B82F6);
                text-decoration: none;
                transition: color .15s ease;
            }
            .field-link:hover { color: var(--ob-primary-hover, #2563EB); text-decoration: underline; }

            .input-wrap {
                position: relative;
                display: flex;
                align-items: center;
                background: #fff;
                border: 1px solid #E2E8F0;
                border-radius: 12px;
                transition: border-color .15s ease, box-shadow .15s ease;
            }
            .input-wrap:hover { border-color: #CBD5E1; }
            .input-wrap:focus-within {
                border-color: var(--ob-primary, #3B82F6);
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            }
            .input-wrap.is-invalid { border-color: #EF4444; }
            .input-wrap.is-invalid:focus-within { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12); }

            .input-wrap .lead-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0 0.5rem 0 1rem;
                color: #94A3B8;
                font-size: 1.05rem;
                pointer-events: none;
                transition: color .25s ease, transform .25s cubic-bezier(.2,.8,.2,1);
            }
            .input-wrap:focus-within .lead-icon {
                color: var(--ob-primary, #3B82F6);
                transform: scale(1.12);
            }
            .input-wrap input {
                flex: 1;
                border: 0;
                outline: none;
                background: transparent;
                font: inherit;
                font-size: 0.95rem;
                color: #0F172A;
                padding: 0.85rem 1rem 0.85rem 0.5rem;
                min-width: 0;
            }
            .input-wrap input::placeholder { color: #94A3B8; }
            /* Kill Chrome's lavender autofill background — keep our white surface */
            .input-wrap input:-webkit-autofill,
            .input-wrap input:-webkit-autofill:hover,
            .input-wrap input:-webkit-autofill:focus,
            .input-wrap input:-webkit-autofill:active {
                -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset !important;
                -webkit-text-fill-color: #0F172A !important;
                caret-color: #0F172A;
                transition: background-color 9999s ease-out 0s;
            }
            .input-wrap .trail-btn {
                appearance: none;
                background: transparent;
                border: 0;
                color: #94A3B8;
                padding: 0 1rem;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                font-size: 1.05rem;
                transition: color .15s ease;
            }
            .input-wrap .trail-btn:hover { color: #475569; }

            .field-error {
                font-size: 0.78rem;
                color: #DC2626;
                margin: 0;
            }

            .submit-btn {
                appearance: none;
                position: relative;
                overflow: hidden;
                width: 100%;
                border: 0;
                cursor: pointer;
                color: #fff;
                font: inherit;
                font-size: 0.98rem;
                font-weight: 600;
                letter-spacing: 0.01em;
                padding: 0.95rem 1rem;
                border-radius: 12px;
                background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 50%, #2563EB 100%);
                background-size: 200% 200%;
                background-position: 0% 50%;
                box-shadow: 0 6px 18px rgba(37, 99, 235, 0.28);
                transition: transform .2s ease, box-shadow .25s ease, background-position .6s ease;
                margin-top: 0.5rem;
            }
            .submit-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 12px 26px rgba(37, 99, 235, 0.38);
                background-position: 100% 50%;
            }
            .submit-btn:active { transform: translateY(0); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28); }
            /* Sliding sheen on hover */
            .submit-btn::after {
                content: "";
                position: absolute;
                top: 0;
                left: -120%;
                width: 80%;
                height: 100%;
                background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,0.18) 50%, transparent 100%);
                transform: skewX(-20deg);
                transition: left .7s ease;
                pointer-events: none;
            }
            .submit-btn:hover::after { left: 130%; }

            .form-aux {
                text-align: center;
                font-size: 0.9rem;
                color: var(--ob-text-muted, #64748B);
                margin-top: 0.25rem;
            }
            .form-aux a {
                color: var(--ob-primary, #3B82F6);
                font-weight: 600;
                text-decoration: none;
                margin-left: 0.35rem;
            }
            .form-aux a:hover { text-decoration: underline; }

            .alert-success {
                background: #ECFDF5;
                border: 1px solid #A7F3D0;
                color: #047857;
                padding: 0.65rem 0.85rem;
                border-radius: 10px;
                font-size: 0.85rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            /* ---------------- Footer ---------------- */
            .auth-footer {
                position: absolute;
                left: 0; right: 0; bottom: 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem clamp(1.5rem, 6vw, 5.5rem) 1.25rem;
                font-size: 0.78rem;
                color: #94A3B8;
            }
            .auth-footer a { color: #64748B; text-decoration: none; }
            .auth-footer a:hover { color: var(--ob-primary, #3B82F6); }
            .auth-footer .sep { margin: 0 0.5rem; color: #CBD5E1; }

            /* ---------------- Visibility helpers ---------------- */
            .only-mobile { display: none; }

            /* ---------------- Responsive: mobile = original card layout ---------------- */
            @media (max-width: 960px) {
                body { background: #f8f8f8; }
                .auth-shell {
                    display: block;
                    min-height: 100vh;
                }
                .auth-brand { display: none; }
                .only-desktop { display: none; }
                .only-mobile { display: revert; }
                /* Restore the specific display for label-based remember-me */
                label.ortho-remember.only-mobile { display: inline-flex; }

                .auth-form-panel {
                    background: transparent;
                    padding: 1.5rem 1rem 1rem;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 0.75rem;
                }

                /* Centered card */
                .form-inner {
                    background: #fff;
                    border-radius: 0.5rem;
                    box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.10);
                    padding: 1.5rem 1.75rem;
                    max-width: 440px;
                    width: 100%;
                    margin: 0 auto;
                    text-align: center;
                    gap: 0.5rem;
                }

                /* Logo block: centered, stacked, with tagline */
                .form-mark {
                    flex-direction: column;
                    align-items: center;
                    gap: 0.25rem;
                    margin: 0.25rem 0 0.125rem;
                }
                .form-mark svg { width: 52px; height: 44px; margin-bottom: -6px; }
                .form-mark .name { font-size: 2rem; font-weight: 500; }
                .form-mark .name .tm { font-size: 0.35em; margin-top: 0.5rem; }

                .ortho-tagline {
                    font-size: 11px;
                    font-style: italic;
                    color: #6e6b7b;
                    letter-spacing: 0.025em;
                    margin: 0 0 0.5rem;
                }

                /* Welcome block — left aligned within card */
                .mobile-welcome { text-align: left; margin-top: 0.25rem; }
                .mobile-welcome h1 {
                    font-family: 'Inter', sans-serif;
                    font-size: 1.3rem;
                    font-weight: 500;
                    color: #5e5873;
                    margin: 0 0 0.125rem;
                    line-height: 1.2;
                    letter-spacing: 0;
                }
                .mobile-welcome p {
                    font-size: 0.85rem;
                    color: #6e6b7b;
                    margin: 0;
                }

                /* Form: left aligned within card, tighter input radius */
                .ortho-form { text-align: left; gap: 0.85rem; margin-top: 0.5rem; }
                .input-wrap { border-radius: 0.358rem; }
                .input-wrap input { padding: 0.55rem 0.75rem 0.55rem 0.5rem; font-size: 0.9rem; }
                .field-label { font-size: 0.82rem; }
                .field-link { font-size: 0.82rem; }

                /* Remember Me — original styling */
                .ortho-remember {
                    display: inline-flex;
                    align-items: center;
                    cursor: pointer;
                    margin-top: 0.25rem;
                }
                .ortho-remember input {
                    width: 0.95rem;
                    height: 0.95rem;
                    margin: 0 0.5rem 0 0;
                    accent-color: var(--ob-primary, #3B82F6);
                    cursor: pointer;
                }
                .ortho-remember span { font-size: 0.85rem; color: #6e6b7b; }

                /* Submit — match original flat blue */
                .submit-btn {
                    background: var(--ob-primary, #3B82F6);
                    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.35);
                    padding: 0.6rem 1rem;
                    font-size: 0.95rem;
                    border-radius: 0.358rem;
                    margin-top: 0.5rem;
                }

                /* Below-form CTA: centered text + provider blurb */
                .mobile-cta { text-align: center; margin-top: 1rem; font-size: 0.85rem; }
                .mobile-cta p { color: #6e6b7b; margin: 0 0 0.125rem; }
                .mobile-cta a { color: var(--ob-primary, #3B82F6); font-weight: 500; text-decoration: none; }
                .mobile-cta a:hover { text-decoration: underline; }
                .mobile-cta .designed {
                    margin-top: 0.5rem;
                    font-size: 0.8rem;
                    color: #5e5873;
                    font-weight: 700;
                    font-style: italic;
                }

                /* Page footer — single, centered, fine print */
                .auth-footer { display: none; }
                .mobile-page-footer {
                    text-align: center;
                    font-size: 9.5px;
                    color: #b9b9c3;
                    line-height: 1.5;
                    padding: 0 0.5rem;
                    max-width: 440px;
                }
                .mobile-page-footer a { color: var(--ob-primary, #3B82F6); text-decoration: none; }
                .mobile-page-footer a:hover { text-decoration: underline; }
            }

            /* Accessibility: honor users who prefer reduced motion */
            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.001ms !important;
                    animation-iteration-count: 1 !important;
                    animation-delay: 0ms !important;
                    transition-duration: 0.001ms !important;
                }
                .submit-btn::after { display: none; }
            }
        </style>
    </head>
    <body>
        <main class="auth-shell">

            {{-- ============ Left brand panel ============ --}}
            <aside class="auth-brand" aria-hidden="true">
                <div class="brand-logo">
                    <span class="logo-mark">
                        <svg viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff" />
                            <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7M28 20c1.5 1.5 1.5 4 0 5M36 20c-1.5 1.5-1.5 4 0 5M19 33c2.5 1 3.5 4 1 6M45 33c-2.5 1-3.5 4-1 6" stroke="#b8b8b8" />
                            <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none" />
                        </svg>
                    </span>
                    <span class="logo-name"><span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span></span>
                </div>

                <img class="brand-doctor"
                     src="{{ asset('images/auth/doctor.png') }}"
                     alt=""
                     onerror="this.style.display='none'" />
            </aside>

            {{-- ============ Right form panel ============ --}}
            <section class="auth-form-panel">
                <div class="form-inner">

                    {{-- Cute tooth mascot — eyes follow the cursor; closes eyes on password focus, sad on wrong password, shy on hover --}}
                    <div class="tooth-mascot-wrap only-desktop">
                        <div class="tooth-bubble" role="status" aria-live="polite"><span></span></div>
                        <svg class="tooth-mascot @if($errors->has('email') || $errors->has('password')) is-sad @endif" viewBox="0 0 100 110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <defs>
                                <radialGradient id="toothShine" cx="35%" cy="30%" r="70%">
                                    <stop offset="0%" stop-color="#ffffff"/>
                                    <stop offset="60%" stop-color="#f1f9ff"/>
                                    <stop offset="100%" stop-color="#dbeafe"/>
                                </radialGradient>
                            </defs>
                            <path d="M50 6 C 28 6, 12 18, 12 40 C 12 56, 18 72, 24 86 C 28 96, 34 102, 40 102 C 46 102, 48 92, 50 80 C 52 92, 54 102, 60 102 C 66 102, 72 96, 76 86 C 82 72, 88 56, 88 40 C 88 18, 72 6, 50 6 Z"
                                  fill="url(#toothShine)" stroke="#bfdbfe" stroke-width="2"/>
                            <ellipse class="blush" cx="28" cy="58" rx="6" ry="4" fill="#fda4af" opacity="0.45"/>
                            <ellipse class="blush" cx="72" cy="58" rx="6" ry="4" fill="#fda4af" opacity="0.45"/>
                            <path d="M28 22 C 24 30, 22 40, 24 50" stroke="#ffffff" stroke-width="3" stroke-linecap="round" fill="none" opacity="0.85"/>

                            {{-- Open eyes (whites + tracking pupils) --}}
                            <circle class="eye-white" cx="38" cy="42" r="8" fill="#ffffff" stroke="#1e293b" stroke-width="1.5"/>
                            <circle class="eye-white" cx="62" cy="42" r="8" fill="#ffffff" stroke="#1e293b" stroke-width="1.5"/>
                            <circle class="eye-pupil" data-eye-cx="38" data-eye-cy="42" cx="38" cy="42" r="3.6" fill="#1e293b"/>
                            <circle class="eye-pupil" data-eye-cx="62" data-eye-cy="42" cx="62" cy="42" r="3.6" fill="#1e293b"/>

                            {{-- Closed eyes (happy ︶︶ arcs) — shown when typing password --}}
                            <path class="eye-closed" d="M30 44 Q 38 36, 46 44" stroke="#1e293b" stroke-width="2.2" stroke-linecap="round" fill="none"/>
                            <path class="eye-closed" d="M54 44 Q 62 36, 70 44" stroke="#1e293b" stroke-width="2.2" stroke-linecap="round" fill="none"/>

                            {{-- Mouths: default smile, sad frown (wrong password), small shy smile (on hover) --}}
                            <path class="mouth-smile" d="M40 64 Q 50 74, 60 64" stroke="#1e293b" stroke-width="2.2" stroke-linecap="round" fill="none"/>
                            <path class="mouth-sad"   d="M40 72 Q 50 62, 60 72" stroke="#1e293b" stroke-width="2.2" stroke-linecap="round" fill="none"/>
                            <path class="mouth-shy"   d="M44 68 Q 50 72, 56 68" stroke="#1e293b" stroke-width="2.2" stroke-linecap="round" fill="none"/>
                        </svg>
                    </div>

                    {{-- orthobrain wordmark — original brain-tooth glyph + ortho/brain split --}}
                    <div class="form-mark">
                        <svg viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff" />
                            <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7M28 20c1.5 1.5 1.5 4 0 5M36 20c-1.5 1.5-1.5 4 0 5M19 33c2.5 1 3.5 4 1 6M45 33c-2.5 1-3.5 4-1 6" stroke="#b8b8b8" />
                            <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none" />
                        </svg>
                        <span class="name"><span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span></span>
                    </div>

                    {{-- Mobile-only tagline (under logo, italic) --}}
                    <p class="ortho-tagline only-mobile">Orthodontics for Your Dental Practice</p>

                    {{-- Desktop hero copy --}}
                    <p class="form-eyebrow only-desktop">For Orthodontists.</p>
                    <h1 class="form-heading only-desktop">Orthodontics, built into your dental practice.</h1>

                    {{-- Mobile welcome block --}}
                    <div class="mobile-welcome only-mobile">
                        <h1>Welcome to Orthobrain!</h1>
                        <p>Please sign-in to your account</p>
                    </div>

                    @if (session('success'))
                        <div class="alert-success" role="status">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->has('captcha'))
                        <div class="alert-success" role="alert" style="background:#FEE2E2; border-color:#FCA5A5; color:#991B1B;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>{{ $errors->first('captcha') }}</span>
                        </div>
                    @endif

                    @if (session('locked_until'))
                        <div id="lockout-banner"
                             role="alert"
                             data-unlock-at="{{ session('locked_until') }}"
                             style="background:#FEE2E2; border:1px solid #FCA5A5; color:#991B1B; border-radius:10px; padding:14px 16px; margin-bottom:14px; display:flex; gap:12px; align-items:flex-start;">
                            <i class="bi bi-shield-lock-fill" style="font-size:1.3rem; line-height:1.2;"></i>
                            <div style="flex:1;">
                                <div style="font-weight:600; margin-bottom:4px;">Too many attempts</div>
                                <div style="font-size:.9rem; line-height:1.45;">
                                    Wrong password entered too many times. Try again in
                                    <span id="lockout-timer" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-weight:700; font-size:1.05rem;">--:--</span>.
                                </div>
                            </div>
                        </div>
                    @endif

                    <form id="login-form" action="{{ url('/login') }}" method="POST" class="ortho-form" novalidate>
                        @csrf
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-login" value="" />

                        {{-- Email --}}
                        <div class="field">
                            <div class="field-row">
                                <label for="email" class="field-label">Email Address<span class="req">*</span></label>
                            </div>
                            <div class="input-wrap @error('email') is-invalid @enderror">
                                <span class="lead-icon"><i class="bi bi-envelope"></i></span>
                                <input id="email" name="email" type="email" autocomplete="email"
                                       placeholder="Enter your email..." value="{{ old('email') }}" />
                            </div>
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="field">
                            <div class="field-row">
                                <label for="password" class="field-label">Password<span class="req">*</span></label>
                                <a href="{{ url('/forgot-password') }}" class="field-link">Forgot password?</a>
                            </div>
                            <div class="input-wrap @error('password') is-invalid @enderror">
                                <span class="lead-icon"><i class="bi bi-lock"></i></span>
                                <input id="password" name="password" type="password"
                                       autocomplete="current-password" placeholder="Enter your password..." />
                                <button type="button" class="trail-btn" aria-label="Toggle password visibility"
                                        onclick="
                                            const p = document.getElementById('password');
                                            const i = this.querySelector('i');
                                            p.type = p.type === 'password' ? 'text' : 'password';
                                            i.classList.toggle('bi-eye');
                                            i.classList.toggle('bi-eye-slash');
                                        ">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remember Me — mobile only, matches original --}}
                        <label class="ortho-remember only-mobile">
                            <input type="checkbox" name="remember" />
                            <span>Remember Me</span>
                        </label>

                        <button type="submit" class="submit-btn">Sign in</button>

                        {{-- Desktop secondary link --}}
                        <p class="form-aux only-desktop">
                            Don't have an account?
                            <a href="{{ url('/register') }}">Create one</a>
                        </p>

                        {{-- Mobile provider CTA — matches original copy --}}
                        <div class="mobile-cta only-mobile">
                            <p>Request to become a provider at no cost</p>
                            <a href="{{ url('/register') }}">Create an account</a>
                            <div class="designed">Designed for OrthoDentists&trade;</div>
                        </div>
                    </form>
                </div>

                {{-- Mobile-only fine-print footer (centered, under card) --}}
                <div class="mobile-page-footer only-mobile">
                    <p>Copyright &copy; {{ date('Y') }} orthobrain&reg;. All rights reserved. Users are subject to the
                        <a href="#">Agreement for Use</a>,
                        <a href="#">User Content Policy</a>, and
                        <a href="#">Independent Contractor Agreement</a>.
                    </p>
                </div>

                <footer class="auth-footer">
                    <span>&copy; {{ date('Y') }} orthobrain<sup>&reg;</sup></span>
                    <span>
                        <a href="#">Agreement</a>
                        <span class="sep">&middot;</span>
                        <a href="#">Privacy</a>
                    </span>
                </footer>
            </section>
        </main>

        <script>
            (function () {
                const mascot = document.querySelector('.tooth-mascot');
                if (!mascot) return;
                const wrap = mascot.closest('.tooth-mascot-wrap');
                const pupils = mascot.querySelectorAll('.eye-pupil');
                const passwordInput = document.getElementById('password');
                const bubble = wrap && wrap.querySelector('.tooth-bubble span');
                const MAX_OFFSET = 2.6;

                function trackEyes(clientX, clientY) {
                    if (mascot.classList.contains('is-closed')) return;
                    const rect = mascot.getBoundingClientRect();
                    const sx = 100 / rect.width;
                    const sy = 110 / rect.height;
                    const cursorSvgX = (clientX - rect.left) * sx;
                    const cursorSvgY = (clientY - rect.top) * sy;
                    pupils.forEach((p) => {
                        const cx = parseFloat(p.dataset.eyeCx);
                        const cy = parseFloat(p.dataset.eyeCy);
                        const dx = cursorSvgX - cx;
                        const dy = cursorSvgY - cy;
                        const dist = Math.hypot(dx, dy) || 1;
                        const offset = Math.min(MAX_OFFSET, dist / 12);
                        const tx = (dx / dist) * offset;
                        const ty = (dy / dist) * offset;
                        p.setAttribute('transform', `translate(${tx.toFixed(2)} ${ty.toFixed(2)})`);
                    });
                }
                window.addEventListener('mousemove', (e) => trackEyes(e.clientX, e.clientY), { passive: true });
                window.addEventListener('touchmove', (e) => {
                    if (e.touches && e.touches[0]) trackEyes(e.touches[0].clientX, e.touches[0].clientY);
                }, { passive: true });

                // 1) Close eyes while typing password.
                if (passwordInput) {
                    passwordInput.addEventListener('focus', () => mascot.classList.add('is-closed'));
                    passwordInput.addEventListener('blur',  () => mascot.classList.remove('is-closed'));
                }

                // 2) Wrong-password sad shake + "try again" bubble — applied server-side via .is-sad.
                if (mascot.classList.contains('is-sad')) {
                    const sadMessages = [
                        "Oops! Try again?",
                        "Wrong one — give it another go!",
                        "Hmm, that's not it. Try again!",
                        "Not quite — one more try!"
                    ];
                    if (wrap && bubble) {
                        bubble.textContent = sadMessages[Math.floor(Math.random() * sadMessages.length)];
                        wrap.classList.add('show-bubble');
                    }
                    setTimeout(() => mascot.classList.remove('is-sad'), 1100);
                    const bubbleTimer = setTimeout(() => wrap && wrap.classList.remove('show-bubble'), 4500);
                    if (passwordInput) {
                        const clearOnEdit = () => {
                            mascot.classList.remove('is-sad');
                            if (wrap) wrap.classList.remove('show-bubble');
                            clearTimeout(bubbleTimer);
                            passwordInput.removeEventListener('input', clearOnEdit);
                        };
                        passwordInput.addEventListener('input', clearOnEdit);
                    }
                }

                // 3) Shy reaction + speech bubble on hover/touch.
                if (wrap && bubble) {
                    const messages = [
                        "Did you wash your hands?",
                        "Eek! Be gentle, I'm shy.",
                        "Don't forget to floss tonight!",
                        "Hi there! Brushing later, right?",
                        "Psst — minty fresh, please!"
                    ];
                    let touchTimer;
                    const showBubble = () => {
                        bubble.textContent = messages[Math.floor(Math.random() * messages.length)];
                        wrap.classList.add('is-shy');
                        mascot.classList.add('is-shy');
                    };
                    const hideBubble = () => {
                        wrap.classList.remove('is-shy');
                        mascot.classList.remove('is-shy');
                    };
                    wrap.addEventListener('mouseenter', showBubble);
                    wrap.addEventListener('mouseleave', hideBubble);
                    wrap.addEventListener('touchstart', () => {
                        showBubble();
                        clearTimeout(touchTimer);
                        touchTimer = setTimeout(hideBubble, 2200);
                    }, { passive: true });
                }
            })();
        </script>

        <script>
            // Lockout countdown. The server flashes `locked_until` (ISO 8601)
            // into session when the per-account counter trips; we render a
            // banner with that timestamp and tick down locally against the
            // user's clock until the unlock instant. While the banner is
            // present, the form is disabled; when the timer hits 0 we hide
            // the banner and re-enable inputs in place — no page reload.
            (function () {
                const banner = document.getElementById('lockout-banner');
                if (!banner) return;

                const unlockAt = Date.parse(banner.dataset.unlockAt);
                if (!Number.isFinite(unlockAt)) return;

                const form     = document.getElementById('login-form');
                const timerEl  = document.getElementById('lockout-timer');
                const fields   = form ? form.querySelectorAll('input, button') : [];

                fields.forEach(el => { el.disabled = true; });
                if (form) form.setAttribute('aria-busy', 'true');

                const fmt = (ms) => {
                    const total = Math.max(0, Math.floor(ms / 1000));
                    const m = String(Math.floor(total / 60)).padStart(2, '0');
                    const s = String(total % 60).padStart(2, '0');
                    return `${m}:${s}`;
                };

                const tick = () => {
                    const remaining = unlockAt - Date.now();
                    if (remaining <= 0) {
                        clearInterval(interval);
                        banner.style.display = 'none';
                        fields.forEach(el => { el.disabled = false; });
                        if (form) form.removeAttribute('aria-busy');
                        const email = document.getElementById('email');
                        if (email) email.focus();
                        return;
                    }
                    timerEl.textContent = fmt(remaining);
                };

                tick();
                const interval = setInterval(tick, 1000);
            })();
        </script>

        @if(config('captcha.site_key'))
        <script>
            // reCAPTCHA v3: fetch a fresh token on submit, attach it to the
            // hidden field, then submit normally. If grecaptcha is unavailable
            // (e.g. user is offline), fall back to plain submit so the page
            // never becomes unsubmittable.
            (function () {
                const form = document.getElementById('login-form');
                const tokenField = document.getElementById('g-recaptcha-response-login');
                const siteKey = "{{ config('captcha.site_key') }}";
                if (!form || !tokenField || !siteKey || typeof grecaptcha === 'undefined') return;

                form.addEventListener('submit', function (e) {
                    if (tokenField.value) return; // already populated, allow submit
                    e.preventDefault();
                    grecaptcha.ready(function () {
                        grecaptcha.execute(siteKey, { action: 'login' }).then(function (token) {
                            tokenField.value = token;
                            form.submit();
                        }).catch(function () {
                            form.submit();
                        });
                    });
                });
            })();
        </script>
        @endif
    </body>
</html>
