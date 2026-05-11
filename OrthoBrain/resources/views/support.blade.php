<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Contact Support — Orthobrain</title>

        <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap');

            *, *::before, *::after { box-sizing: border-box; }
            html { scroll-behavior: smooth; }
            body.sup-body {
                margin: 0;
                min-height: 100vh;
                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
                color: var(--ob-text);
                background:
                    radial-gradient(ellipse 1200px 600px at 80% -10%, rgba(96, 165, 250, 0.18), transparent 60%),
                    radial-gradient(ellipse 800px 600px at -5% 110%, rgba(147, 197, 253, 0.20), transparent 65%),
                    linear-gradient(135deg, #F1F5F9 0%, #E0F2FE 50%, #DBEAFE 100%);
                background-attachment: fixed;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            .sup-shell-3col {
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr) 300px;
                gap: 1.25rem;
                max-width: 1480px;
                margin: 0 auto;
                padding: 1.25rem;
                align-items: start;
            }
            @media (max-width: 1180px) {
                .sup-shell-3col { grid-template-columns: 1fr; }
                .sup-brand-panel, .sup-help-panel { position: static !important; }
            }

            /* ─────── Left brand panel ─────── */
            .sup-brand-panel {
                position: sticky;
                top: 1.25rem;
                background: linear-gradient(160deg, #dbeafe 0%, #eff6ff 50%, #f8fafc 100%);
                border: 1px solid #dbe5f5;
                border-radius: 18px;
                padding: 1.5rem 1.25rem 1.25rem;
                min-height: 600px;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                box-shadow: 0 6px 20px -10px rgba(59,130,246,0.25);
                overflow: hidden;
            }
            .sup-brand-logo {
                display: inline-flex; align-items: center; gap: 0.55rem;
                font-family: 'Montserrat', sans-serif;
                font-weight: 500; font-size: 1.55rem; line-height: 1;
                margin: 0.25rem 0 0.5rem;
            }
            .sup-brand-logo .ob-mark {
                width: 32px; height: 32px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #fff; border-radius: 8px;
                box-shadow: 0 2px 6px rgba(59,130,246,0.18);
            }
            .sup-brand-logo .p1 { color: #5bc0de; }
            .sup-brand-logo .p2 { color: #8cc63f; }
            .sup-brand-logo .tm { color: #8cc63f; font-size: 0.55rem; margin-top: -10px; margin-left: 1px; }

            .sup-brand-art {
                position: relative;
                flex: 1;
                margin: 0.5rem -0.5rem 0.5rem;
                border-radius: 14px;
                background:
                    radial-gradient(140px 110px at 50% 38%, rgba(255,255,255,0.85) 0%, transparent 70%),
                    linear-gradient(165deg, #cfe1ff 0%, #e8f1ff 100%);
                overflow: hidden;
                display: flex; align-items: center; justify-content: center;
                min-height: 220px;
            }
            .sup-brand-card {
                display: flex; gap: 0.75rem; align-items: flex-start;
                background: rgba(255,255,255,0.85);
                backdrop-filter: blur(4px);
                border: 1px solid #e0eaf6;
                border-radius: 12px;
                padding: 0.85rem 0.95rem;
                box-shadow: 0 2px 6px rgba(34,41,47,0.04);
            }
            .sup-brand-card .ico {
                width: 36px; height: 36px; flex-shrink: 0;
                display: inline-flex; align-items: center; justify-content: center;
                background: rgba(59,130,246,0.12);
                color: var(--ob-primary);
                border-radius: 10px;
                font-size: 1.05rem;
            }
            .sup-brand-card .ttl { font-weight: 600; color: var(--ob-text); font-size: 0.85rem; line-height: 1.2; margin: 0 0 0.15rem; }
            .sup-brand-card .sub { font-size: 0.78rem; color: var(--ob-text-muted); margin: 0; line-height: 1.35; }

            /* ─────── Center form panel ─────── */
            .sup-card {
                background: #fff;
                border: 1px solid #e6ebf3;
                border-radius: 18px;
                padding: 1.75rem 1.75rem 1.5rem;
                box-shadow: 0 10px 30px -12px rgba(34,41,47,0.10);
                min-width: 0;
            }
            .sup-card-head { margin-bottom: 1.5rem; }
            .sup-card-head h1 {
                font-size: 1.5rem; font-weight: 600; color: var(--ob-text);
                margin: 0 0 0.25rem; letter-spacing: -0.01em;
            }
            .sup-card-head p { font-size: 0.92rem; color: var(--ob-text-muted); margin: 0; }

            .sup-grid { display: grid; grid-template-columns: 1fr; gap: 1.1rem 1.25rem; }
            @media (min-width: 720px) { .sup-grid { grid-template-columns: 1fr 1fr; } }
            .sup-col-span-2 { grid-column: span 1; }
            @media (min-width: 720px) { .sup-col-span-2 { grid-column: span 2; } }

            .sup-label { display: block; font-size: 0.85rem; font-weight: 500; color: var(--ob-text); margin-bottom: 0.4rem; }
            .sup-required { color: #ef4444; margin-left: 2px; }
            .sup-input-group {
                display: flex; align-items: center;
                border: 1px solid var(--ob-border-strong);
                border-radius: 10px;
                background: #fff;
                overflow: hidden;
                transition: border-color .18s, box-shadow .18s;
                height: 44px;
            }
            .sup-input-group:focus-within { border-color: var(--ob-primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
            .sup-input-group.is-invalid { border-color: #ef4444; }
            .sup-input-icon {
                display: flex; align-items: center; justify-content: center;
                padding: 0 0.65rem 0 0.85rem;
                color: var(--ob-text-muted);
                background: transparent;
                font-size: 1rem;
                align-self: stretch;
            }
            .sup-input {
                flex: 1; border: 0; outline: none;
                padding: 0.55rem 0.85rem 0.55rem 0.25rem;
                font-size: 0.92rem;
                color: var(--ob-text);
                background: transparent;
                min-width: 0;
                font-family: inherit;
            }
            .sup-input::placeholder { color: #9aa4b5; }

            .sup-select {
                width: 100%; height: 44px;
                padding: 0 2rem 0 0.85rem;
                background: #fff;
                border: 1px solid var(--ob-border-strong);
                border-radius: 10px;
                outline: none;
                font-size: 0.92rem;
                color: var(--ob-text);
                appearance: none; -webkit-appearance: none;
                background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='%2364748B'><path d='M3.204 5h9.592L8 10.481zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z'/></svg>");
                background-repeat: no-repeat;
                background-position: right 0.85rem center;
                font-family: inherit;
            }
            .sup-select:focus { border-color: var(--ob-primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
            .sup-select.is-invalid { border-color: #ef4444; }

            .sup-textarea {
                width: 100%;
                min-height: 160px;
                padding: 0.7rem 0.85rem;
                background: #fff;
                border: 1px solid var(--ob-border-strong);
                border-radius: 10px;
                outline: none;
                font-size: 0.92rem;
                color: var(--ob-text);
                font-family: inherit;
                resize: vertical;
                transition: border-color .18s, box-shadow .18s;
            }
            .sup-textarea:focus { border-color: var(--ob-primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
            .sup-textarea.is-invalid { border-color: #ef4444; }
            .sup-textarea::placeholder { color: #9aa4b5; }

            .sup-helper { font-size: 0.78rem; color: var(--ob-text-muted); margin: 0.3rem 0 0; }
            .sup-err { color: #ef4444; font-size: 0.78rem; margin: 0.3rem 0 0; }
            .sup-err.hidden { display: none; }

            /* Action row */
            .sup-actions {
                display: flex; justify-content: space-between; align-items: center;
                gap: 1rem;
                margin-top: 1.5rem;
                padding-top: 1.25rem;
                border-top: 1px solid #eef1f6;
            }
            .sup-back-link {
                display: inline-flex; align-items: center; gap: 0.4rem;
                font-size: 0.88rem; color: var(--ob-text-muted);
                text-decoration: none;
                transition: color .15s;
            }
            .sup-back-link:hover { color: var(--ob-primary); }
            .sup-btn-primary-grad {
                display: inline-flex; align-items: center; gap: 0.45rem;
                padding: 0.7rem 1.5rem;
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: #fff;
                border: 0;
                border-radius: 10px;
                font-weight: 600; font-size: 0.92rem;
                cursor: pointer;
                box-shadow: 0 6px 16px -4px rgba(59,130,246,0.45);
                transition: transform .1s, box-shadow .15s, filter .15s, opacity .15s;
                font-family: inherit;
            }
            .sup-btn-primary-grad:hover:not(:disabled) {
                transform: translateY(-1px);
                box-shadow: 0 10px 22px -6px rgba(59,130,246,0.55);
                filter: brightness(1.04);
            }
            .sup-btn-primary-grad:active { transform: translateY(0); }
            .sup-btn-primary-grad:disabled { opacity: 0.55; cursor: not-allowed; box-shadow: none; }

            /* ─────── Right help panel ─────── */
            .sup-help-panel {
                position: sticky;
                top: 1.25rem;
                display: flex; flex-direction: column; gap: 1rem;
            }
            .sup-help-card {
                background: #fff;
                border: 1px solid #e6ebf3;
                border-radius: 16px;
                padding: 1.25rem 1.15rem;
                box-shadow: 0 6px 20px -10px rgba(34,41,47,0.10);
            }
            .sup-help-card h3 {
                font-size: 1rem; font-weight: 700; color: var(--ob-text);
                margin: 0 0 0.85rem;
            }
            .sup-help-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.65rem; }
            .sup-help-row { display: flex; align-items: flex-start; gap: 0.65rem; font-size: 0.85rem; color: var(--ob-text); }
            .sup-help-row .ico {
                width: 30px; height: 30px; flex-shrink: 0;
                display: inline-flex; align-items: center; justify-content: center;
                background: rgba(59,130,246,0.10);
                color: var(--ob-primary);
                border-radius: 8px;
                font-size: 0.95rem;
            }
            .sup-help-row .lbl { font-size: 0.72rem; color: var(--ob-text-muted); margin: 0; line-height: 1; }
            .sup-help-row .val { font-size: 0.88rem; color: var(--ob-text); margin: 0.15rem 0 0; font-weight: 500; }
            .sup-help-row a { color: inherit; text-decoration: none; }
            .sup-help-row a:hover { color: var(--ob-primary); }

            .sup-faq-link {
                display: flex; align-items: center; justify-content: space-between;
                padding: 0.6rem 0.7rem;
                border-radius: 8px;
                color: var(--ob-text);
                text-decoration: none;
                font-size: 0.85rem;
                transition: background .15s, color .15s;
                background: #f8fafc;
            }
            .sup-faq-link:hover { background: #eff6ff; color: var(--ob-primary); }
            .sup-faq-link i { font-size: 0.9rem; opacity: 0.6; }

            .sup-faq-item { list-style: none; }
            button.sup-faq-link {
                width: 100%;
                border: none;
                cursor: pointer;
                font: inherit;
                text-align: left;
                line-height: 1.35;
            }
            button.sup-faq-link:focus-visible {
                outline: 2px solid var(--ob-primary);
                outline-offset: 2px;
            }
            .sup-faq-link[aria-expanded="true"] {
                background: #eff6ff;
                color: var(--ob-primary);
            }

            .sup-faq-icon {
                position: relative;
                width: 14px; height: 14px;
                flex-shrink: 0;
                color: currentColor;
                opacity: 0.85;
            }
            .sup-faq-icon::before,
            .sup-faq-icon::after {
                content: '';
                position: absolute;
                top: 50%; left: 50%;
                background: currentColor;
                border-radius: 1px;
                transform-origin: center;
                transition: transform .25s cubic-bezier(.2,.8,.2,1);
            }
            .sup-faq-icon::before { width: 12px; height: 2px; transform: translate(-50%, -50%); }
            .sup-faq-icon::after  { width: 2px; height: 12px; transform: translate(-50%, -50%); }
            .sup-faq-link[aria-expanded="true"] .sup-faq-icon::after {
                transform: translate(-50%, -50%) scaleY(0);
            }

            .sup-faq-a {
                overflow: hidden;
                max-height: 0;
                transition: max-height .28s cubic-bezier(.2,.8,.2,1);
            }
            .sup-faq-a-inner {
                padding: 0.55rem 0.75rem 0.7rem;
                font-size: 0.82rem;
                line-height: 1.55;
                color: var(--ob-text-muted);
            }
            .sup-faq-a-inner p { margin: 0; }

            @media (prefers-reduced-motion: reduce) {
                .sup-faq-a,
                .sup-faq-icon::before,
                .sup-faq-icon::after { transition: none; }
            }

            /* Banners */
            .sup-error-banner {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #991b1b;
                padding: 0.85rem 1rem;
                border-radius: 10px;
                margin-bottom: 1rem;
                font-size: 0.88rem;
            }
            .sup-error-banner ul { margin: 0.4rem 0 0 1.2rem; padding: 0; }
            .sup-error-banner strong { font-weight: 700; }

            /* Required-fields callout */
            .sup-required-note {
                margin-top: 0.85rem;
                background: #f8fafc;
                border: 1px solid #e6ebf3;
                border-radius: 10px;
                padding: 0.65rem 0.85rem;
                font-size: 0.82rem;
                color: var(--ob-text-muted);
                display: flex; align-items: center; gap: 0.55rem;
            }
            .sup-required-note i { color: var(--ob-primary); font-size: 1rem; }
            .sup-required-note .sup-required { font-size: 0.95rem; }

            .sup-mascot-wrap{
                position: relative;
                width: 160px;
                height: 160px;
                margin: 0.25rem auto 1.25rem;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer;
                will-change: transform;
                transition: transform .28s cubic-bezier(.2,.8,.2,1),
                            filter   .28s cubic-bezier(.2,.8,.2,1);
                animation: sup-mascot-bob 4.2s ease-in-out 1.2s infinite;
                filter: drop-shadow(0 8px 18px rgba(15, 23, 42, 0.10));
            }
            .sup-mascot{
                width: 100%; height: 100%;
                user-select: none;
                transition: transform .25s cubic-bezier(.2,.8,.2,1);
            }
            .sup-mascot .eye-pupil{ transition: transform 0.08s linear; }
            .sup-mascot .blush    { transition: opacity .25s ease; }

            .sup-mascot-wrap:hover{
                filter: drop-shadow(0 14px 28px rgba(37, 99, 235, 0.22));
            }
            .sup-mascot-wrap:hover .sup-mascot{
                transform: scale(1.045) rotate(-1.5deg);
            }
            .sup-mascot-wrap:hover .blush{ opacity: 0.9; }

            @keyframes sup-mascot-bob{
                0%, 100%{ transform: translateY(0) translateX(0); }
                50%     { transform: translateY(-5px); }
            }

            @media (max-width: 768px){
                .sup-mascot-wrap{ width: 120px; height: 120px; }
            }
            @media (prefers-reduced-motion: reduce){
                .sup-mascot-wrap{ animation: none; }
                .sup-mascot, .sup-mascot .eye-pupil{ transition: none; }
            }
        </style>
    </head>
    <body class="sup-body">
        <div class="sup-shell-3col">

            {{-- ─────────────── LEFT: Branding ─────────────── --}}
            <aside class="sup-brand-panel">
                <div class="sup-brand-logo">
                    <span class="ob-mark">
                        <svg width="22" height="22" viewBox="0 0 64 64" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#fff"/>
                            <path d="M32 18v14M24 28c1.5 1 1.5 4 0 5.5M40 28c-1.5 1-1.5 4 0 5.5" stroke="#94a3b8"/>
                        </svg>
                    </span>
                    <span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span>
                </div>

                <div class="sup-brand-art" aria-hidden="true">
                    <div class="sup-mascot-wrap">
                    <svg class="sup-mascot" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <radialGradient id="cupShine" cx="35%" cy="30%" r="75%">
                                <stop offset="0%"  stop-color="#ffffff"/>
                                <stop offset="60%" stop-color="#f1f9ff"/>
                                <stop offset="100%" stop-color="#dbeafe"/>
                            </radialGradient>
                            <linearGradient id="bandShine" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#f8fbff"/>
                                <stop offset="100%" stop-color="#dbeafe"/>
                            </linearGradient>
                        </defs>

                        <path d="M18 54 C 18 26, 82 26, 82 54"
                              fill="none" stroke="#bfdbfe" stroke-width="6" stroke-linecap="round"/>
                        <path d="M22 54 C 22 30, 78 30, 78 54"
                              fill="none" stroke="url(#bandShine)" stroke-width="3" stroke-linecap="round"/>

                        <rect x="10" y="50" width="20" height="30" rx="9" ry="10"
                              fill="url(#cupShine)" stroke="#bfdbfe" stroke-width="2"/>
                        <rect x="14" y="55" width="12" height="20" rx="5" ry="6"
                              fill="#e0f0ff" opacity="0.75"/>
                        <rect x="70" y="50" width="20" height="30" rx="9" ry="10"
                              fill="url(#cupShine)" stroke="#bfdbfe" stroke-width="2"/>
                        <rect x="74" y="55" width="12" height="20" rx="5" ry="6"
                              fill="#e0f0ff" opacity="0.75"/>

                        <path d="M14 56 C 12 62, 12 70, 14 76" stroke="#ffffff"
                              stroke-width="2" stroke-linecap="round" fill="none" opacity="0.85"/>

                        <ellipse class="blush" cx="38" cy="44" rx="4" ry="2.6" fill="#fda4af" opacity="0.5"/>
                        <ellipse class="blush" cx="62" cy="44" rx="4" ry="2.6" fill="#fda4af" opacity="0.5"/>

                        <circle class="eye-white" cx="42" cy="40" r="4.2" fill="#ffffff" stroke="#1e293b" stroke-width="1.3"/>
                        <circle class="eye-white" cx="58" cy="40" r="4.2" fill="#ffffff" stroke="#1e293b" stroke-width="1.3"/>
                        <circle class="eye-pupil" data-eye-cx="42" data-eye-cy="40" cx="42" cy="40" r="1.9" fill="#1e293b"/>
                        <circle class="eye-pupil" data-eye-cx="58" data-eye-cy="40" cx="58" cy="40" r="1.9" fill="#1e293b"/>

                        <path class="mouth-smile" d="M46 47 Q 50 50, 54 47"
                              stroke="#1e293b" stroke-width="1.6" stroke-linecap="round" fill="none"/>
                    </svg>
                    </div>
                </div>

                <div class="sup-brand-card">
                    <span class="ico"><i class="bi bi-life-preserver"></i></span>
                    <div>
                        <p class="ttl">Need help? We're here for you.</p>
                        <p class="sub">Our team typically responds within a business day.</p>
                    </div>
                </div>

                <div class="sup-brand-card">
                    <span class="ico"><i class="bi bi-clock-history"></i></span>
                    <div>
                        <p class="ttl">Response time: within 24 hours</p>
                        <p class="sub">Mon – Fri, 9am – 6pm ET.</p>
                    </div>
                </div>
            </aside>

            {{-- ─────────────── CENTER: Form ─────────────── --}}
            <main class="sup-card">
                <header class="sup-card-head">
                    <h1>Contact Support</h1>
                    <p>Have questions or need help? Reach out to our team.</p>
                </header>

                @if($errors->any())
                    <div class="sup-error-banner" id="sup-server-banner">
                        <strong>Please fix the following before continuing:</strong>
                        <ul>
                            @foreach($errors->keys() as $key)
                                @foreach($errors->get($key) as $msg)
                                    <li data-server-field-key="{{ $key }}">{{ $msg }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="supportForm" action="{{ route('support.send') }}" method="POST" novalidate>
                    @csrf

                    <div class="sup-grid">
                        <div>
                            <label class="sup-label">Full Name<span class="sup-required">*</span></label>
                            <div class="sup-input-group" id="box-name">
                                <span class="sup-input-icon"><i class="bi bi-person"></i></span>
                                <input id="in-name" name="name" type="text" class="sup-input" placeholder="Enter your full name" value="{{ old('name') }}" oninput="clearError('name')" />
                            </div>
                            <p id="err-name" class="sup-err hidden"></p>
                        </div>

                        <div>
                            <label class="sup-label">Email Address<span class="sup-required">*</span></label>
                            <div class="sup-input-group" id="box-email">
                                <span class="sup-input-icon"><i class="bi bi-envelope"></i></span>
                                <input id="in-email" name="email" type="email" class="sup-input" placeholder="you@example.com" value="{{ old('email') }}" oninput="clearError('email')" />
                            </div>
                            <p id="err-email" class="sup-err hidden"></p>
                        </div>

                        <div class="sup-col-span-2">
                            <label class="sup-label">Subject<span class="sup-required">*</span></label>
                            <div class="sup-input-group" id="box-subject">
                                <span class="sup-input-icon"><i class="bi bi-tag"></i></span>
                                <input id="in-subject" name="subject" type="text" class="sup-input" placeholder="Briefly describe your issue" value="{{ old('subject') }}" oninput="clearError('subject')" />
                            </div>
                            <p id="err-subject" class="sup-err hidden"></p>
                        </div>

                        <div>
                            <label class="sup-label">Category</label>
                            <select id="in-category" name="category" class="sup-select">
                                <option value="" @selected(! old('category'))>Select a category (optional)</option>
                                <option value="Technical Issue"  @selected(old('category') === 'Technical Issue')>Technical Issue</option>
                                <option value="Billing"          @selected(old('category') === 'Billing')>Billing</option>
                                <option value="General Inquiry"  @selected(old('category') === 'General Inquiry')>General Inquiry</option>
                            </select>
                        </div>
                        <div></div>

                        <div class="sup-col-span-2">
                            <label class="sup-label">Message<span class="sup-required">*</span></label>
                            <textarea id="in-message" name="message" class="sup-textarea" placeholder="Tell us what's going on…" oninput="clearError('message')">{{ old('message') }}</textarea>
                            <p class="sup-helper">Please provide as much detail as possible.</p>
                            <p id="err-message" class="sup-err hidden"></p>
                        </div>
                    </div>

                    <div class="sup-required-note">
                        <i class="bi bi-info-circle"></i>
                        <span>All fields marked with <span class="sup-required">*</span> are required.</span>
                    </div>

                    <div class="sup-actions">
                        <a href="{{ url('/login') }}" class="sup-back-link">
                            <i class="bi bi-arrow-left"></i> Back to Login
                        </a>
                        <button type="submit" id="sup-submit" class="sup-btn-primary-grad">
                            Send Message <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </main>

            {{-- ─────────────── RIGHT: Help info ─────────────── --}}
            <aside class="sup-help-panel">
                <div class="sup-help-card">
                    <h3>Other ways to reach us</h3>
                    <ul class="sup-help-list">
                        <li class="sup-help-row">
                            <span class="ico"><i class="bi bi-envelope"></i></span>
                            <div>
                                <p class="lbl">Email</p>
                                <p class="val"><a href="mailto:support@orthobrain.com">support@orthobrain.com</a></p>
                            </div>
                        </li>
                        <li class="sup-help-row">
                            <span class="ico"><i class="bi bi-telephone"></i></span>
                            <div>
                                <p class="lbl">Phone</p>
                                <p class="val"><a href="tel:+15550100001">+1 (555) 010-0001</a></p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="sup-help-card">
                    <h3>FAQs</h3>
                    <ul class="sup-help-list sup-faq-list" style="gap:0.4rem;">
                        <li class="sup-faq-item">
                            <button type="button" class="sup-faq-link sup-faq-q"
                                    aria-expanded="false" aria-controls="sup-faq-panel-1">
                                <span>How do I reset my password?</span>
                                <span class="sup-faq-icon" aria-hidden="true"></span>
                            </button>
                            <div id="sup-faq-panel-1" class="sup-faq-a" role="region" aria-label="Answer">
                                <div class="sup-faq-a-inner">
                                    <p>Go to the login page and click on 'Forgot Password?'. Enter your email address and follow the instructions sent to your email.</p>
                                </div>
                            </div>
                        </li>
                        <li class="sup-faq-item">
                            <button type="button" class="sup-faq-link sup-faq-q"
                                    aria-expanded="false" aria-controls="sup-faq-panel-2">
                                <span>How long does account approval take?</span>
                                <span class="sup-faq-icon" aria-hidden="true"></span>
                            </button>
                            <div id="sup-faq-panel-2" class="sup-faq-a" role="region" aria-label="Answer">
                                <div class="sup-faq-a-inner">
                                    <p>Account approval usually takes 1&ndash;2 business days. You will receive an email once your account is approved.</p>
                                </div>
                            </div>
                        </li>
                        <li class="sup-faq-item">
                            <button type="button" class="sup-faq-link sup-faq-q"
                                    aria-expanded="false" aria-controls="sup-faq-panel-3">
                                <span>How do I switch practices?</span>
                                <span class="sup-faq-icon" aria-hidden="true"></span>
                            </button>
                            <div id="sup-faq-panel-3" class="sup-faq-a" role="region" aria-label="Answer">
                                <div class="sup-faq-a-inner">
                                    <p>You can switch practices from the top-right dropdown menu after logging in, or contact support for assistance.</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>

        <script>
            // Mirror of register.blade.php's per-field validator pattern, scoped to the support form.
            const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const nameRe  = /^[A-Za-z\s\-]+$/;

            const VALIDATORS = {
                name:    v => !v ? 'Full name is required'
                            : v.length < 2 ? 'Name must be at least 2 characters'
                            : !nameRe.test(v) ? 'Only letters, spaces, and hyphens are allowed' : '',
                email:   v => !v ? 'Email is required'
                            : !emailRe.test(v) ? 'Please enter a valid email address' : '',
                subject: v => !v ? 'Subject is required'
                            : v.length > 200 ? 'Subject is too long (max 200 characters)' : '',
                message: v => !v ? 'Message is required'
                            : v.length < 10 ? 'Message must be at least 10 characters'
                            : v.length > 5000 ? 'Message is too long (max 5000 characters)' : '',
            };

            const SERVER_FIELD_MAP = {
                name: 'name', email: 'email', subject: 'subject',
                category: 'category', message: 'message',
            };
            const CLIENT_TO_SERVER_KEYS = {
                name: ['name'], email: ['email'], subject: ['subject'], message: ['message'],
            };

            const touched = new Set();

            function _val(id) {
                const el = document.getElementById('in-' + id);
                return el ? (el.value || '').trim() : '';
            }
            function showError(id, msg) {
                const err = document.getElementById('err-' + id);
                const box = document.getElementById('box-' + id);
                const inp = document.getElementById('in-' + id);
                if (err) { err.textContent = msg; err.classList.remove('hidden'); }
                if (box) box.classList.add('is-invalid');
                else if (inp) inp.classList.add('is-invalid');
            }
            function _clearUI(id) {
                const err = document.getElementById('err-' + id);
                const box = document.getElementById('box-' + id);
                const inp = document.getElementById('in-' + id);
                if (err) err.classList.add('hidden');
                if (box) box.classList.remove('is-invalid');
                else if (inp) inp.classList.remove('is-invalid');
            }
            function validateField(id) {
                const v = VALIDATORS[id];
                if (!v) return true;
                const msg = v(_val(id));
                if (msg) { showError(id, msg); return false; }
                _clearUI(id);
                return true;
            }
            function dismissServerBannerFor(id) {
                const banner = document.getElementById('sup-server-banner');
                if (!banner) return;
                (CLIENT_TO_SERVER_KEYS[id] || []).forEach(k => {
                    banner.querySelectorAll('li[data-server-field-key="' + k + '"]').forEach(li => li.remove());
                });
                if (banner.querySelectorAll('li').length === 0) banner.remove();
            }
            function clearError(id) {
                if (touched.has(id)) validateField(id);
                else _clearUI(id);
                const v = VALIDATORS[id];
                if (v && !v(_val(id))) dismissServerBannerFor(id);
            }

            document.addEventListener('DOMContentLoaded', () => {
                Object.keys(VALIDATORS).forEach(id => {
                    const el = document.getElementById('in-' + id);
                    if (!el) return;
                    el.addEventListener('blur', () => { touched.add(id); validateField(id); });
                });

                // Surface server-side errors inline (matches register's behaviour).
                const SERVER_ERRORS = @json($errors->messages());
                Object.entries(SERVER_ERRORS).forEach(([key, msgs]) => {
                    const fid = SERVER_FIELD_MAP[key];
                    if (fid && msgs && msgs.length) { touched.add(fid); showError(fid, msgs[0]); }
                });

                // Final submit gate — run all validators; refuse if any fail.
                document.getElementById('supportForm').addEventListener('submit', (e) => {
                    let ok = true;
                    Object.keys(VALIDATORS).forEach(id => {
                        touched.add(id);
                        if (!validateField(id)) ok = false;
                    });
                    if (!ok) {
                        e.preventDefault();
                        const firstBad = document.querySelector('.sup-err:not(.hidden)');
                        if (firstBad) firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            });

            (function () {
                const wrap   = document.querySelector('.sup-mascot-wrap');
                const mascot = wrap && wrap.querySelector('.sup-mascot');
                if (!wrap || !mascot) return;

                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                const pupils = mascot.querySelectorAll('.eye-pupil');
                const MAX_PUPIL = 1.6;
                const MAX_PARALLAX = 7;

                let raf = 0, lastX = 0, lastY = 0, hovering = false;

                function apply() {
                    raf = 0;
                    const vw = window.innerWidth || 1, vh = window.innerHeight || 1;
                    const nx = (lastX / vw) * 2 - 1;
                    const ny = (lastY / vh) * 2 - 1;
                    const tx = Math.max(-1, Math.min(1, nx)) * MAX_PARALLAX;
                    const ty = Math.max(-1, Math.min(1, ny)) * MAX_PARALLAX;
                    mascot.style.transform = hovering
                        ? `translate(${tx.toFixed(2)}px, ${ty.toFixed(2)}px) scale(1.045) rotate(-1.5deg)`
                        : `translate(${tx.toFixed(2)}px, ${ty.toFixed(2)}px)`;

                    const rect = mascot.getBoundingClientRect();
                    if (rect.width === 0 || rect.height === 0) return;
                    const sx = 100 / rect.width, sy = 100 / rect.height;
                    const cx = (lastX - rect.left) * sx;
                    const cy = (lastY - rect.top)  * sy;
                    pupils.forEach(function (p) {
                        const ex = parseFloat(p.dataset.eyeCx);
                        const ey = parseFloat(p.dataset.eyeCy);
                        const dx = cx - ex, dy = cy - ey;
                        const d  = Math.hypot(dx, dy) || 1;
                        const off = Math.min(MAX_PUPIL, d / 18);
                        p.setAttribute('transform',
                            'translate(' + (dx/d*off).toFixed(2) + ' ' + (dy/d*off).toFixed(2) + ')');
                    });
                }

                function onMove(x, y) {
                    lastX = x; lastY = y;
                    if (!raf) raf = requestAnimationFrame(apply);
                }

                window.addEventListener('mousemove', function (e) { onMove(e.clientX, e.clientY); }, { passive: true });
                window.addEventListener('touchmove', function (e) {
                    if (e.touches && e.touches[0]) onMove(e.touches[0].clientX, e.touches[0].clientY);
                }, { passive: true });

                wrap.addEventListener('mouseenter', function () {
                    hovering = true;
                    if (!raf) raf = requestAnimationFrame(apply);
                });
                wrap.addEventListener('mouseleave', function () {
                    hovering = false;
                    mascot.style.transform = '';
                    pupils.forEach(function (p) { p.setAttribute('transform', 'translate(0 0)'); });
                });
            })();

            (function () {
                const buttons = document.querySelectorAll('.sup-faq-q');
                if (!buttons.length) return;

                function panelOf(btn) {
                    return document.getElementById(btn.getAttribute('aria-controls'));
                }

                function openItem(btn) {
                    const panel = panelOf(btn);
                    if (!panel) return;
                    btn.setAttribute('aria-expanded', 'true');
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    const onEnd = function (e) {
                        if (e.propertyName !== 'max-height') return;
                        if (btn.getAttribute('aria-expanded') === 'true') {
                            // Let content reflow naturally after open (e.g. on viewport resize)
                            panel.style.maxHeight = 'none';
                        }
                        panel.removeEventListener('transitionend', onEnd);
                    };
                    panel.addEventListener('transitionend', onEnd);
                }

                function closeItem(btn) {
                    const panel = panelOf(btn);
                    if (!panel) return;
                    // If maxHeight was unset to 'none', re-pin to current pixel height before animating to 0
                    if (panel.style.maxHeight === 'none' || panel.style.maxHeight === '') {
                        panel.style.maxHeight = panel.scrollHeight + 'px';
                        // Force reflow so the next change actually transitions
                        void panel.offsetHeight;
                    }
                    btn.setAttribute('aria-expanded', 'false');
                    panel.style.maxHeight = '0px';
                }

                buttons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const isOpen = btn.getAttribute('aria-expanded') === 'true';
                        buttons.forEach(function (other) {
                            if (other !== btn && other.getAttribute('aria-expanded') === 'true') {
                                closeItem(other);
                            }
                        });
                        if (isOpen) closeItem(btn);
                        else openItem(btn);
                    });
                });
            })();
        </script>
    </body>
</html>
