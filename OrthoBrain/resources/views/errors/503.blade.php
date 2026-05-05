@extends('errors.layout')

@section('page_title', 'Under maintenance')
@section('page_code', '503')
@section('code_pill_tone', 'tone-warning')
@section('code_pill_label', 'Service temporarily unavailable')

@push('styles')
<style>
    /* Maintenance illustration — two interlocking gears that rotate, plus a
       floating wrench and a soft workbench platform underneath */
    .gears-wrap { position: relative; width: 100%; max-width: 300px; margin: 0.5rem auto 0; }
    .gears-wrap svg { overflow: visible; }

    .gear {
        transform-box: fill-box;
        transform-origin: center;
    }
    .gear-1 { animation: gearSpin 9s linear infinite; }
    .gear-2 { animation: gearSpinReverse 7s linear infinite; }

    @keyframes gearSpin        { from { transform: rotate(0deg); }   to { transform: rotate(360deg); } }
    @keyframes gearSpinReverse { from { transform: rotate(0deg); }   to { transform: rotate(-360deg); } }

    .wrench-float {
        transform-box: fill-box;
        transform-origin: center;
        animation: wrenchBob 3.5s ease-in-out infinite;
    }
    @keyframes wrenchBob {
        0%, 100% { transform: translateY(0) rotate(-18deg); }
        50%      { transform: translateY(-6px) rotate(-10deg); }
    }

    .spark {
        transform-box: fill-box;
        transform-origin: center;
        animation: sparkPop 2.4s ease-in-out infinite;
    }
    .spark.s2 { animation-delay: -0.8s; }
    .spark.s3 { animation-delay: -1.6s; }
    @keyframes sparkPop {
        0%, 100% { opacity: 0; transform: scale(0.6); }
        50%      { opacity: 1; transform: scale(1.1); }
    }
</style>
@endpush

@section('illustration')
    <div class="gears-wrap">
        <svg viewBox="0 0 300 210" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="gearCyan" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"  stop-color="#8ddff0"/>
                    <stop offset="100%" stop-color="#5bc0de"/>
                </linearGradient>
                <linearGradient id="gearGreen" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%"  stop-color="#bde579"/>
                    <stop offset="100%" stop-color="#8cc63f"/>
                </linearGradient>
                <filter id="gearShadow" x="-30%" y="-30%" width="160%" height="160%">
                    <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#1f3a44" flood-opacity="0.12"/>
                </filter>
            </defs>

            {{-- Soft bench/platform under the gears --}}
            <ellipse cx="150" cy="185" rx="120" ry="10" fill="#1f3a44" opacity="0.07"/>

            {{-- ── Large gear (cyan) ─────────────────────────── --}}
            <g class="gear gear-1" filter="url(#gearShadow)">
                {{-- teeth ring: 8 stubby rectangles around the circle --}}
                <g fill="url(#gearCyan)">
                    <rect x="108" y="30"  width="18" height="22" rx="3"/>
                    <rect x="108" y="128" width="18" height="22" rx="3"/>
                    <rect x="59"  y="79"  width="22" height="18" rx="3"/>
                    <rect x="157" y="79"  width="22" height="18" rx="3"/>
                    <rect x="72"  y="42"  width="18" height="22" rx="3" transform="rotate(-45 81 53)"/>
                    <rect x="72"  y="116" width="18" height="22" rx="3" transform="rotate(45 81 127)"/>
                    <rect x="144" y="42"  width="18" height="22" rx="3" transform="rotate(45 153 53)"/>
                    <rect x="144" y="116" width="18" height="22" rx="3" transform="rotate(-45 153 127)"/>
                </g>
                <circle cx="117" cy="90" r="42" fill="url(#gearCyan)"/>
                <circle cx="117" cy="90" r="32" fill="#ffffff"/>
                <circle cx="117" cy="90" r="10" fill="url(#gearCyan)"/>
                {{-- spokes --}}
                <g stroke="url(#gearCyan)" stroke-width="6" stroke-linecap="round">
                    <line x1="117" y1="62" x2="117" y2="78"/>
                    <line x1="117" y1="102" x2="117" y2="118"/>
                    <line x1="89"  y1="90" x2="105" y2="90"/>
                    <line x1="129" y1="90" x2="145" y2="90"/>
                </g>
            </g>

            {{-- ── Small gear (green) ────────────────────────── --}}
            <g class="gear gear-2" filter="url(#gearShadow)">
                <g fill="url(#gearGreen)">
                    <rect x="196" y="62"  width="14" height="18" rx="3"/>
                    <rect x="196" y="136" width="14" height="18" rx="3"/>
                    <rect x="159" y="99"  width="18" height="14" rx="3"/>
                    <rect x="229" y="99"  width="18" height="14" rx="3"/>
                    <rect x="170" y="72"  width="14" height="18" rx="3" transform="rotate(-45 177 81)"/>
                    <rect x="170" y="126" width="14" height="18" rx="3" transform="rotate(45 177 135)"/>
                    <rect x="222" y="72"  width="14" height="18" rx="3" transform="rotate(45 229 81)"/>
                    <rect x="222" y="126" width="14" height="18" rx="3" transform="rotate(-45 229 135)"/>
                </g>
                <circle cx="203" cy="108" r="32" fill="url(#gearGreen)"/>
                <circle cx="203" cy="108" r="23" fill="#ffffff"/>
                <circle cx="203" cy="108" r="7"  fill="url(#gearGreen)"/>
                <g stroke="url(#gearGreen)" stroke-width="5" stroke-linecap="round">
                    <line x1="203" y1="87"  x2="203" y2="98"/>
                    <line x1="203" y1="118" x2="203" y2="129"/>
                    <line x1="182" y1="108" x2="193" y2="108"/>
                    <line x1="213" y1="108" x2="224" y2="108"/>
                </g>
            </g>

            {{-- ── Floating wrench ──────────────────────────── --}}
            <g class="wrench-float" transform="translate(30 28)">
                <path d="M0 28 L22 6 a10 10 0 0 1 14 14 L14 42 a6 6 0 0 1 -8 0 L -1 35 a5 5 0 0 1 1 -7 z"
                      fill="#5bc0de" opacity="0.95"/>
                <circle cx="27" cy="13" r="5" fill="#ffffff"/>
            </g>

            {{-- ── Spark dots near the gears ────────────────── --}}
            <circle class="spark s1" cx="250" cy="50"  r="3.5" fill="#ff9f43"/>
            <circle class="spark s2" cx="85"  cy="155" r="3"   fill="#8cc63f"/>
            <circle class="spark s3" cx="265" cy="145" r="2.5" fill="#5bc0de"/>
        </svg>
    </div>
@endsection

@section('error_title', "We'll be right back")
@section('error_sub')
    Our team is making quick improvements so everything runs smoothly for you.
    This usually takes just a few minutes — please try again shortly. Thanks for your patience!
@endsection

@section('actions')
    <a href="javascript:location.reload()" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
        </svg>
        Try Again
    </a>
    <a href="mailto:{{ config('admin.brand.support_email', 'support@orthobrain.com') }}" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
        </svg>
        Contact Support
    </a>
@endsection

@section('support_line')
    Need urgent help?
    <a href="mailto:{{ config('admin.brand.support_email', 'support@orthobrain.com') }}">Reach out to our team</a>
    and we'll get back to you as soon as we're back online.
@endsection
