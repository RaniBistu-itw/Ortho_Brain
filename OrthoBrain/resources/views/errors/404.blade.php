@extends('errors.layout')

@section('page_title', 'Page not found')
@section('page_code', '404')
@section('code_pill_tone', '')
@section('code_pill_label', 'Page not found')

@push('styles')
<style>
    /* 404 illustration — stylised map card with a dashed path and a dropped
       pin that gently bobs. Signals "you went off course" without any jargon. */
    .map-wrap { position: relative; width: 100%; max-width: 320px; margin: 0.25rem auto 0; }
    .map-wrap svg { overflow: visible; }

    .map-card { transform-box: fill-box; transform-origin: center; animation: cardBreathe 6s ease-in-out infinite; }
    @keyframes cardBreathe {
        0%, 100% { transform: rotate(-2deg) translateY(0); }
        50%      { transform: rotate(-2deg) translateY(-4px); }
    }

    .map-path {
        stroke-dasharray: 6 8;
        animation: dashMove 3s linear infinite;
    }
    @keyframes dashMove { to { stroke-dashoffset: -56; } }

    .pin-drop {
        transform-box: fill-box;
        transform-origin: 50% 100%;
        animation: pinBob 2.6s ease-in-out infinite;
    }
    @keyframes pinBob {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-6px); }
    }

    .pin-shadow { transform-box: fill-box; transform-origin: center; animation: shadowPulse 2.6s ease-in-out infinite; }
    @keyframes shadowPulse {
        0%, 100% { transform: scaleX(1);   opacity: 0.18; }
        50%      { transform: scaleX(0.7); opacity: 0.28; }
    }

    .q-mark {
        transform-box: fill-box;
        transform-origin: center;
        animation: qWiggle 3.2s ease-in-out infinite;
    }
    @keyframes qWiggle {
        0%, 100% { transform: rotate(-8deg); }
        50%      { transform: rotate(8deg); }
    }

    .cloud-float { transform-box: fill-box; transform-origin: center; animation: cloudDrift 9s ease-in-out infinite; }
    .cloud-float.c2 { animation-duration: 11s; animation-delay: -3s; }
    @keyframes cloudDrift {
        0%, 100% { transform: translateX(0); }
        50%      { transform: translateX(10px); }
    }
</style>
@endpush

@section('illustration')
    <div class="map-wrap">
        <svg viewBox="0 0 320 210" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="mapCyan" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"  stop-color="#eaf7fb"/>
                    <stop offset="100%" stop-color="#d0ecf5"/>
                </linearGradient>
                <linearGradient id="pinGreen" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%"  stop-color="#a8d95e"/>
                    <stop offset="100%" stop-color="#76ac2f"/>
                </linearGradient>
                <filter id="mapShadow" x="-10%" y="-10%" width="120%" height="130%">
                    <feDropShadow dx="0" dy="8" stdDeviation="8" flood-color="#1f3a44" flood-opacity="0.1"/>
                </filter>
            </defs>

            {{-- ── Soft background clouds ─────────────────────── --}}
            <g class="cloud-float c1" opacity="0.55">
                <ellipse cx="50" cy="40" rx="28" ry="8" fill="#e5f6fa"/>
                <ellipse cx="65" cy="36" rx="14" ry="7" fill="#e5f6fa"/>
            </g>
            <g class="cloud-float c2" opacity="0.5">
                <ellipse cx="270" cy="28" rx="22" ry="7" fill="#e5f6fa"/>
                <ellipse cx="280" cy="24" rx="10" ry="5" fill="#e5f6fa"/>
            </g>

            {{-- ── Map card ───────────────────────────────────── --}}
            <g class="map-card" filter="url(#mapShadow)">
                <rect x="40" y="55" width="240" height="130" rx="14" fill="#ffffff" stroke="#dbe3ec" stroke-width="1.5"/>
                <rect x="40" y="55" width="240" height="130" rx="14" fill="url(#mapCyan)" opacity="0.65"/>

                {{-- terrain blobs --}}
                <path d="M55 145 Q 95 120 140 140 T 240 130 L 240 175 L 55 175 Z" fill="#d7eafc" opacity="0.8"/>
                <path d="M55 160 Q 110 150 160 165 T 255 155 L 255 180 L 55 180 Z" fill="#bee1a8" opacity="0.6"/>

                {{-- subtle grid --}}
                <g stroke="#c9dbe4" stroke-width="1" opacity="0.35">
                    <line x1="85"  y1="60" x2="85"  y2="180"/>
                    <line x1="135" y1="60" x2="135" y2="180"/>
                    <line x1="185" y1="60" x2="185" y2="180"/>
                    <line x1="235" y1="60" x2="235" y2="180"/>
                    <line x1="45"  y1="95"  x2="275" y2="95"/>
                    <line x1="45"  y1="130" x2="275" y2="130"/>
                    <line x1="45"  y1="165" x2="275" y2="165"/>
                </g>

                {{-- dashed route path: winds across the map --}}
                <path class="map-path"
                      d="M 70 150 Q 110 90 150 120 T 230 80"
                      stroke="#5bc0de" stroke-width="3.5" stroke-linecap="round" fill="none"/>

                {{-- start marker (small cyan dot) --}}
                <circle cx="70" cy="150" r="5" fill="#ffffff" stroke="#5bc0de" stroke-width="3"/>
            </g>

            {{-- ── Destination pin (dropped, bobbing) ─────────── --}}
            <g transform="translate(220 50)">
                <ellipse class="pin-shadow" cx="10" cy="50" rx="16" ry="4" fill="#1f3a44"/>
                <g class="pin-drop">
                    <path d="M10 0 C -4 0 -14 10 -14 24 C -14 38 10 54 10 54 C 10 54 34 38 34 24 C 34 10 24 0 10 0 Z"
                          fill="url(#pinGreen)"/>
                    <circle cx="10" cy="22" r="8" fill="#ffffff"/>
                    <text x="10" y="27" text-anchor="middle"
                          font-family="Montserrat, sans-serif" font-weight="800" font-size="14" fill="#76ac2f">?</text>
                </g>
            </g>

            {{-- ── Floating question glyph ────────────────────── --}}
            <g class="q-mark" transform="translate(285 120)">
                <circle r="14" fill="#fff" stroke="#5bc0de" stroke-width="2.5"/>
                <text x="0" y="5" text-anchor="middle"
                      font-family="Montserrat, sans-serif" font-weight="800" font-size="16" fill="#5bc0de">?</text>
            </g>
        </svg>
    </div>
@endsection

@section('error_title', "Hmm, we can't find that page")
@section('error_sub')
    The link you followed may be broken, or the page may have been moved.
    Don't worry — let's get you back to somewhere useful.
@endsection

@section('support_line')
    Think this is a mistake?
    <a href="mailto:{{ config('admin.brand.support_email', 'support@orthobrain.com') }}">Let us know</a>
    and we'll take a look.
@endsection
