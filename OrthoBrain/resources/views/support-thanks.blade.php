<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Message Received — Orthobrain</title>

        <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap');

            *, *::before, *::after { box-sizing: border-box; }
            html { scroll-behavior: smooth; }
            body.sup-thanks-body {
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
                display: flex; align-items: center; justify-content: center;
                padding: 2rem 1.25rem;
            }

            .sup-thanks-shell {
                width: 100%;
                max-width: 560px;
                display: flex; flex-direction: column;
                gap: 1.25rem;
            }

            /* Brand logo (matches support page) */
            .sup-thanks-brand {
                display: inline-flex; align-items: center; gap: 0.55rem;
                font-family: 'Montserrat', sans-serif;
                font-weight: 500; font-size: 1.55rem; line-height: 1;
                align-self: center;
            }
            .sup-thanks-brand .ob-mark {
                width: 32px; height: 32px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #fff; border-radius: 8px;
                box-shadow: 0 2px 6px rgba(59,130,246,0.18);
            }
            .sup-thanks-brand .p1 { color: #5bc0de; }
            .sup-thanks-brand .p2 { color: #8cc63f; }
            .sup-thanks-brand .tm { color: #8cc63f; font-size: 0.55rem; margin-top: -10px; margin-left: 1px; }

            /* Confirmation card */
            .sup-thanks-card {
                background: #fff;
                border: 1px solid #e6ebf3;
                border-radius: 18px;
                padding: 2.25rem 2rem 1.75rem;
                box-shadow: 0 10px 30px -12px rgba(34,41,47,0.10);
                text-align: center;
            }

            /* Illustration wrapper */
            .sup-thanks-art {
                position: relative;
                width: 180px;
                height: 180px;
                margin: 0 auto 1.25rem;
                display: flex; align-items: center; justify-content: center;
                animation: sup-thanks-float 4.5s ease-in-out infinite;
                filter: drop-shadow(0 10px 22px rgba(37, 99, 235, 0.18));
            }
            .sup-thanks-art svg {
                width: 100%; height: 100%;
                user-select: none;
            }

            @keyframes sup-thanks-float {
                0%, 100% { transform: translateY(0); }
                50%      { transform: translateY(-6px); }
            }
            @media (prefers-reduced-motion: reduce) {
                .sup-thanks-art { animation: none; }
                .sup-thanks-spark { animation: none !important; }
            }

            /* Sparkle pulse */
            .sup-thanks-spark {
                transform-origin: center;
                animation: sup-thanks-twinkle 2.6s ease-in-out infinite;
            }
            .sup-thanks-spark.s2 { animation-delay: 0.6s; }
            .sup-thanks-spark.s3 { animation-delay: 1.2s; }
            @keyframes sup-thanks-twinkle {
                0%, 100% { opacity: 0.35; transform: scale(0.85); }
                50%      { opacity: 1;    transform: scale(1.1); }
            }

            .sup-thanks-headline {
                font-size: 1.55rem;
                font-weight: 700;
                color: var(--ob-text);
                margin: 0 0 0.5rem;
                letter-spacing: -0.01em;
            }
            .sup-thanks-sub {
                font-size: 0.95rem;
                line-height: 1.55;
                color: var(--ob-text-muted);
                margin: 0 0 1.5rem;
            }

            .sup-thanks-actions {
                display: flex; flex-direction: column; align-items: center;
                gap: 0.75rem;
            }
            .sup-thanks-btn {
                display: inline-flex; align-items: center; gap: 0.5rem;
                padding: 0.75rem 1.75rem;
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: #fff;
                border: 0;
                border-radius: 10px;
                font-weight: 600; font-size: 0.95rem;
                text-decoration: none;
                cursor: pointer;
                box-shadow: 0 6px 16px -4px rgba(59,130,246,0.45);
                transition: transform .1s, box-shadow .15s, filter .15s;
                font-family: inherit;
            }
            .sup-thanks-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 22px -6px rgba(59,130,246,0.55);
                filter: brightness(1.04);
            }
            .sup-thanks-btn:active { transform: translateY(0); }

            .sup-thanks-secondary {
                font-size: 0.88rem;
                color: var(--ob-text-muted);
                text-decoration: none;
                transition: color .15s;
            }
            .sup-thanks-secondary:hover { color: var(--ob-primary); }

            .sup-thanks-meta {
                margin-top: 1.5rem;
                padding-top: 1.25rem;
                border-top: 1px solid #eef1f6;
                display: flex; align-items: center; justify-content: center;
                gap: 0.5rem;
                font-size: 0.82rem;
                color: var(--ob-text-muted);
            }
            .sup-thanks-meta i { color: var(--ob-primary); }

            @media (max-width: 480px) {
                .sup-thanks-card { padding: 1.75rem 1.25rem 1.25rem; }
                .sup-thanks-art { width: 140px; height: 140px; }
                .sup-thanks-headline { font-size: 1.3rem; }
            }
        </style>
    </head>
    <body class="sup-thanks-body">
        <main class="sup-thanks-shell">

            <a href="{{ url('/') }}" class="sup-thanks-brand" aria-label="Orthobrain home">
                <span class="ob-mark">
                    <svg width="22" height="22" viewBox="0 0 64 64" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#fff"/>
                        <path d="M32 18v14M24 28c1.5 1 1.5 4 0 5.5M40 28c-1.5 1-1.5 4 0 5.5" stroke="#94a3b8"/>
                    </svg>
                </span>
                <span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span>
            </a>

            <section class="sup-thanks-card">
                <div class="sup-thanks-art" aria-hidden="true">
                    {{-- Paper-plane illustration with success badge — distinct from the support page's headphone mascot --}}
                    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Paper plane sent successfully">
                        <defs>
                            <linearGradient id="stThanksPlane" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%"  stop-color="#dbeafe"/>
                                <stop offset="55%" stop-color="#93c5fd"/>
                                <stop offset="100%" stop-color="#3b82f6"/>
                            </linearGradient>
                            <linearGradient id="stThanksFold" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%"  stop-color="#1d4ed8"/>
                                <stop offset="100%" stop-color="#2563eb"/>
                            </linearGradient>
                            <linearGradient id="stThanksTrail" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%"  stop-color="#bfdbfe" stop-opacity="0"/>
                                <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.6"/>
                            </linearGradient>
                            <radialGradient id="stThanksHalo" cx="50%" cy="50%" r="50%">
                                <stop offset="0%"  stop-color="#dbeafe" stop-opacity="0.85"/>
                                <stop offset="100%" stop-color="#dbeafe" stop-opacity="0"/>
                            </radialGradient>
                        </defs>

                        {{-- soft halo behind plane --}}
                        <circle cx="100" cy="100" r="78" fill="url(#stThanksHalo)"/>

                        {{-- arching motion trail --}}
                        <path d="M28 138 Q 70 68, 138 88"
                              stroke="url(#stThanksTrail)" stroke-width="3"
                              stroke-linecap="round" stroke-dasharray="2 7"
                              fill="none"/>

                        {{-- paper plane body --}}
                        <g transform="translate(54 48) rotate(-12 56 50)">
                            {{-- back wing (under) --}}
                            <path d="M2 70 L 110 14 L 60 60 Z"
                                  fill="#bfdbfe" stroke="#3b82f6" stroke-width="2"
                                  stroke-linejoin="round"/>
                            {{-- top wing (main) --}}
                            <path d="M2 70 L 110 14 L 78 96 L 60 60 Z"
                                  fill="url(#stThanksPlane)" stroke="#2563eb" stroke-width="2"
                                  stroke-linejoin="round"/>
                            {{-- center fold (darker triangle) --}}
                            <path d="M60 60 L 110 14 L 78 96 Z"
                                  fill="url(#stThanksFold)" opacity="0.85"
                                  stroke="#1d4ed8" stroke-width="1.5" stroke-linejoin="round"/>
                            {{-- crisp fold line highlight --}}
                            <path d="M60 60 L 110 14"
                                  stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"
                                  opacity="0.7"/>
                        </g>

                        {{-- success badge (lower-right) --}}
                        <g transform="translate(132 122)">
                            <circle cx="22" cy="22" r="22" fill="#ffffff"/>
                            <circle cx="22" cy="22" r="20" fill="#10b981"/>
                            <path d="M13 22 L 19.5 28 L 31 16"
                                  stroke="#ffffff" stroke-width="3.4"
                                  stroke-linecap="round" stroke-linejoin="round"
                                  fill="none"/>
                        </g>

                        {{-- sparkles --}}
                        <g class="sup-thanks-spark" transform="translate(38 56)">
                            <path d="M0 -7 L 1.6 -1.6 L 7 0 L 1.6 1.6 L 0 7 L -1.6 1.6 L -7 0 L -1.6 -1.6 Z"
                                  fill="#60a5fa"/>
                        </g>
                        <g class="sup-thanks-spark s2" transform="translate(160 70)">
                            <path d="M0 -5 L 1.2 -1.2 L 5 0 L 1.2 1.2 L 0 5 L -1.2 1.2 L -5 0 L -1.2 -1.2 Z"
                                  fill="#3b82f6"/>
                        </g>
                        <g class="sup-thanks-spark s3" transform="translate(56 168)">
                            <path d="M0 -4 L 1 -1 L 4 0 L 1 1 L 0 4 L -1 1 L -4 0 L -1 -1 Z"
                                  fill="#93c5fd"/>
                        </g>
                    </svg>
                </div>

                <h1 class="sup-thanks-headline">Thanks for reaching out!</h1>
                <p class="sup-thanks-sub">
                    We've received your message and a member of our team will reply within one business day.
                </p>

                <div class="sup-thanks-actions">
                    <a href="{{ url('/login') }}" class="sup-thanks-btn">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                    <a href="{{ route('support.show') }}" class="sup-thanks-secondary">
                        Send another message
                    </a>
                </div>

                <div class="sup-thanks-meta">
                    <i class="bi bi-clock-history"></i>
                    <span>Typical response time: within 24 hours, Mon&ndash;Fri 9am&ndash;6pm ET</span>
                </div>
            </section>
        </main>
    </body>
</html>
