<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <title>@yield('page_title', 'Error') — {{ config('admin.brand.name', 'orthobrain') }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('vuexy/images/ico/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --ob-cyan: #5bc0de;
            --ob-cyan-dark: #3fb1d4;
            --ob-green: #8cc63f;
            --ob-green-dark: #76ac2f;
            --ob-warning: #ff9f43;
            --ob-danger: #ea5455;
            --ob-text: #1f1f1f;
            --ob-muted: #6e6b7b;
            --ob-bg: #f7fafd;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 500;
            color: var(--ob-text);
            background: var(--ob-bg);
            overflow-x: hidden;
            position: relative;
        }

        /* ── Ambient animated orbs ──────────────────────────── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
            will-change: transform;
        }
        .orb--cyan {
            width: 520px; height: 520px;
            background: radial-gradient(circle at 30% 30%, #8ddff0 0%, #5bc0de 70%);
            top: -160px; left: -160px;
            animation: orbFloat1 18s ease-in-out infinite;
        }
        .orb--green {
            width: 460px; height: 460px;
            background: radial-gradient(circle at 70% 70%, #c6eb95 0%, #8cc63f 70%);
            bottom: -180px; right: -140px;
            animation: orbFloat2 22s ease-in-out infinite;
        }
        .orb--soft {
            width: 360px; height: 360px;
            background: radial-gradient(circle, #e5f6fa 0%, #d7eafc 70%);
            top: 40%; left: 60%;
            opacity: 0.6;
            animation: orbFloat3 26s ease-in-out infinite;
        }
        @keyframes orbFloat1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(80px,60px) scale(1.08); } }
        @keyframes orbFloat2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-60px,-50px) scale(1.1); } }
        @keyframes orbFloat3 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-50px,40px); } }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(24,28,40,0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.5;
            z-index: 0;
            pointer-events: none;
        }

        /* ── Brand bar ──────────────────────────────────────── */
        .brandbar {
            position: relative;
            z-index: 2;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .brandbar img { height: 36px; width: auto; }
        .brand-text { font-size: 1.1rem; font-weight: 600; letter-spacing: -0.01em; line-height: 1; }
        .brand-ortho { color: var(--ob-cyan); }
        .brand-brain { color: var(--ob-green); }
        .brand-tm    { color: var(--ob-green); font-size: 0.55em; vertical-align: super; }
        .brand-tagline {
            display: block;
            font-size: 0.68rem;
            font-style: italic;
            color: var(--ob-muted);
            margin-top: 3px;
            font-weight: 400;
        }

        /* ── Stage ──────────────────────────────────────────── */
        .stage {
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 110px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem 4rem;
        }
        .content { width: 100%; max-width: 640px; text-align: center; position: relative; }

        /* ── Big error number with animated orbit ──────────── */
        .err-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.1rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            font-size: clamp(6.5rem, 22vw, 13rem);
            position: relative;
        }
        .err-number > span.digit {
            display: inline-block;
            background: linear-gradient(135deg, var(--ob-cyan) 0%, var(--ob-green) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            animation: digitFloat 4s ease-in-out infinite;
            text-shadow: 0 2px 20px rgba(91,192,222,0.15);
        }
        .err-number > .digit.first { animation-delay: 0s; }
        .err-number > .digit.last  { animation-delay: -1.4s; }

        @keyframes digitFloat {
            0%,100% { transform: translateY(0); }
            50%     { transform: translateY(-10px); }
        }

        /* The middle glyph — SVG ring with an orbiting icon */
        .err-orbit {
            position: relative;
            width: 1em; height: 1em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.05em;
            animation: digitFloat 4s ease-in-out infinite -0.7s;
        }
        .err-orbit svg { width: 100%; height: 100%; overflow: visible; }

        .err-orbit .orbiter {
            position: absolute;
            width: 38%; height: 38%;
            top: 6%;
            right: 4%;
            animation: orbiterSpin 8s linear infinite;
            transform-origin: -85% 180%;
        }
        .err-orbit .orbiter svg { width: 100%; height: 100%; }
        @keyframes orbiterSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Tone variants on the orbiter (inner fill color) */
        .orbiter--cyan   .orbiter-shape { fill: var(--ob-cyan);    stroke: #fff; }
        .orbiter--cyan   .orbiter-stem  { stroke: var(--ob-cyan); }
        .orbiter--green  .orbiter-shape { fill: var(--ob-green);   stroke: #fff; }
        .orbiter--green  .orbiter-stem  { stroke: var(--ob-green); }
        .orbiter--warning .orbiter-shape{ fill: var(--ob-warning); stroke: #fff; }
        .orbiter--warning .orbiter-stem { stroke: var(--ob-warning); }
        .orbiter--danger .orbiter-shape { fill: var(--ob-danger);  stroke: #fff; }
        .orbiter--danger .orbiter-stem  { stroke: var(--ob-danger); }

        /* Title + subtitle */
        .err-title {
            font-size: clamp(1.4rem, 3.5vw, 1.9rem);
            font-weight: 700;
            color: #111;
            margin-top: 0.5rem;
            margin-bottom: 0.6rem;
            letter-spacing: -0.01em;
            opacity: 0;
            animation: fadeUp 0.8s ease forwards 0.2s;
        }
        .err-sub {
            font-size: 1rem;
            color: var(--ob-muted);
            max-width: 460px;
            margin: 0 auto 2rem;
            line-height: 1.55;
            opacity: 0;
            animation: fadeUp 0.8s ease forwards 0.35s;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* CTAs */
        .err-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: center;
            opacity: 0;
            animation: fadeUp 0.8s ease forwards 0.5s;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.3rem;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 0.6rem;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform 150ms ease, box-shadow 150ms ease, background 150ms ease, color 150ms ease, border-color 150ms ease;
            font-family: inherit;
        }
        .btn svg { width: 16px; height: 16px; }
        .btn-primary {
            background: linear-gradient(135deg, var(--ob-cyan) 0%, var(--ob-cyan-dark) 100%);
            color: #fff;
            box-shadow: 0 6px 18px rgba(91,192,222,0.32);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(91,192,222,0.42);
            color: #fff;
        }
        .btn-ghost {
            background: #fff;
            color: var(--ob-text);
            border-color: #e2e4ec;
        }
        .btn-ghost:hover {
            transform: translateY(-2px);
            border-color: var(--ob-cyan);
            color: var(--ob-cyan-dark);
            box-shadow: 0 6px 14px rgba(24,28,40,0.06);
        }

        .err-support {
            margin-top: 2.25rem;
            font-size: 0.82rem;
            color: var(--ob-muted);
            opacity: 0;
            animation: fadeUp 0.8s ease forwards 0.7s;
        }
        .err-support a {
            color: var(--ob-cyan-dark);
            text-decoration: none;
            font-weight: 600;
        }
        .err-support a:hover { text-decoration: underline; }

        .err-dot {
            position: absolute;
            border-radius: 50%;
            opacity: 0.55;
            pointer-events: none;
        }
        .err-dot--1 { width: 10px; height: 10px; background: var(--ob-cyan);    top: 8%;  left: 12%; animation: dotFloat 6s ease-in-out infinite; }
        .err-dot--2 { width: 7px;  height: 7px;  background: var(--ob-green);   top: 18%; right: 14%; animation: dotFloat 7s ease-in-out infinite 1s; }
        .err-dot--3 { width: 5px;  height: 5px;  background: var(--ob-cyan);    top: 70%; left: 6%;  animation: dotFloat 5s ease-in-out infinite 0.5s; }
        .err-dot--4 { width: 9px;  height: 9px;  background: var(--ob-green);   top: 64%; right: 8%; animation: dotFloat 8s ease-in-out infinite 1.5s; }
        @keyframes dotFloat {
            0%,100% { transform: translate(0,0); }
            50%     { transform: translate(8px,-12px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .orb, .err-number > span, .err-orbit, .err-orbit .orbiter,
            .err-dot, .err-title, .err-sub, .err-actions, .err-support {
                animation: none !important;
                opacity: 1 !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <div class="orb orb--cyan"></div>
    <div class="orb orb--green"></div>
    <div class="orb orb--soft"></div>

    <header class="brandbar">
        <a href="{{ url('/') }}" style="text-decoration:none; display:flex; align-items:center; gap:.75rem;">
            <img src="{{ asset('vuexy/images/logo/logo.svg') }}" alt="orthobrain" onerror="this.style.display='none'">
            <div>
                <div class="brand-text">
                    <span class="brand-ortho">ortho</span><span class="brand-brain">brain</span><span class="brand-tm">&trade;</span>
                </div>
                <span class="brand-tagline">Orthodontics for Your Dental Practice</span>
            </div>
        </a>
    </header>

    <main class="stage">
        <div class="content">
            <span class="err-dot err-dot--1"></span>
            <span class="err-dot err-dot--2"></span>
            <span class="err-dot err-dot--3"></span>
            <span class="err-dot err-dot--4"></span>

            <div class="err-number" aria-label="@yield('page_code', 'Error')">
                <span class="digit first">@yield('digit_first')</span>
                <span class="err-orbit" aria-hidden="true">
                    <svg viewBox="0 0 120 120" fill="none">
                        <defs>
                            <linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#5bc0de"/>
                                <stop offset="100%" stop-color="#8cc63f"/>
                            </linearGradient>
                        </defs>
                        <circle cx="60" cy="60" r="46" stroke="url(#ringGrad)" stroke-width="14" fill="none"
                                stroke-linecap="round" stroke-dasharray="260 40" stroke-dashoffset="0">
                            <animate attributeName="stroke-dashoffset" from="0" to="-300" dur="6s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    <span class="orbiter @yield('orbiter_tone', 'orbiter--cyan')" aria-hidden="true">
                        @yield('orbiter_icon')
                    </span>
                </span>
                <span class="digit last">@yield('digit_last')</span>
            </div>

            <h1 class="err-title">@yield('error_title')</h1>
            <p class="err-sub">@yield('error_sub')</p>

            <div class="err-actions">
                @hasSection('actions')
                    @yield('actions')
                @else
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12l9-9 9 9"/><path d="M9 21V12h6v9"/><path d="M5 10v11h14V10"/>
                        </svg>
                        Back to Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-ghost">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Go Back
                    </a>
                @endif
            </div>

            <p class="err-support">
                @hasSection('support_line')
                    @yield('support_line')
                @else
                    Still stuck?
                    <a href="mailto:{{ config('admin.brand.support_email', 'support@orthobrain.com') }}">Contact support</a>
                    and we'll help you find what you need.
                @endif
            </p>
        </div>
    </main>

    @stack('scripts')
</body>
</html>
