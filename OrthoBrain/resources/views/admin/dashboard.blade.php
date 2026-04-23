@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════
   OrthoBrain Dashboard v2  ·  Gentle Medical UI
   Palette: Soft Medical Blue · Clinical Teal · Enamel White
   ═══════════════════════════════════════════════════════════════════ */

#ob-dash {
  --d-navy:      #1E3A5F;
  --d-teal:      #0EA5C5;
  --d-teal-dk:   #0B7A94;
  --d-blue:      #2563EB;
  --d-purple:    #7C3AED;
  --d-green:     #059669;
  --d-amber:     #D97706;
  --d-slate:     #64748B;
  --d-border:    rgba(14,100,165,.1);
  --d-shadow:    0 2px 16px rgba(14,50,90,.08);
  --d-shadow-lg: 0 6px 36px rgba(14,50,90,.15);
  --d-r:         16px;
  --d-r-sm:      10px;
}

/* ── Beat the global `h1,h2 { color:#111 !important }` override ──
   Both rules have !important; ours wins because (a) it comes later
   in the <head> AND (b) it has far higher specificity.              */
body #ob-dash .ob-hero,
body #ob-dash .ob-hero * { color: #fff !important; }

body #ob-dash .ob-hero .ob-hero-eyebrow { color: rgba(255,255,255,.62) !important; }
body #ob-dash .ob-hero .ob-hero-sub     { color: rgba(255,255,255,.72) !important; }

/* ─── Animations ─────────────────────────────────────────── */
@keyframes ob-tooth-float {
  0%, 100% { transform: translateY(0px) rotate(-1.5deg); }
  50%       { transform: translateY(-10px) rotate(1.5deg); }
}
@keyframes ob-pulse-dot {
  0%   { box-shadow: 0 0 0 0 rgba(74,222,128,.7); }
  70%  { box-shadow: 0 0 0 8px rgba(74,222,128,0); }
  100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); }
}
@keyframes ob-fade-up {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}

.ob-tooth-anim  { animation: ob-tooth-float 4.5s ease-in-out infinite; }
.ob-anim-1 { animation: ob-fade-up .45s ease .05s both; }
.ob-anim-2 { animation: ob-fade-up .45s ease .10s both; }
.ob-anim-3 { animation: ob-fade-up .45s ease .15s both; }
.ob-anim-4 { animation: ob-fade-up .45s ease .20s both; }
.ob-anim-5 { animation: ob-fade-up .45s ease .25s both; }
.ob-anim-6 { animation: ob-fade-up .45s ease .30s both; }

/* ─── Hero Banner ───────────────────────────────────────────── */
.ob-hero {
  background: linear-gradient(135deg, #164E7A 0%, #1E6FA8 50%, #0EA5C5 100%);
  border-radius: var(--d-r);
  padding: 2rem 2.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 178px;
  box-shadow: var(--d-shadow-lg);
  animation: ob-fade-up .4s ease both;
}
.ob-hero::before {
  content: '';
  position: absolute;
  top: -90px; right: -70px;
  width: 360px; height: 360px;
  background: radial-gradient(circle, rgba(14,165,197,.17) 0%, transparent 68%);
  pointer-events: none;
}
.ob-hero::after {
  content: '';
  position: absolute;
  bottom: -90px; left: 35%;
  width: 260px; height: 260px;
  background: radial-gradient(circle, rgba(99,102,241,.12) 0%, transparent 68%);
  pointer-events: none;
}

.ob-hero-left { position: relative; z-index: 2; }

.ob-hero-eyebrow {
  font-size: .72rem;
  font-weight: 600;
  letter-spacing: 1.3px;
  text-transform: uppercase;
  opacity: .6;
  margin: 0 0 .35rem;
  display: flex;
  align-items: center;
  gap: .4rem;
}
.ob-hero-heading {
  font-size: 1.85rem;
  font-weight: 800;
  margin: 0 0 .35rem;
  letter-spacing: -.5px;
  line-height: 1.15;
}
.ob-hero-sub {
  font-size: .88rem;
  opacity: .68;
  margin: 0 0 1rem;
  font-weight: 400;
}
.ob-status-badge {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.22);
  backdrop-filter: blur(8px);
  border-radius: 30px;
  padding: .28rem .9rem;
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: .7px;
  text-transform: uppercase;
}
.ob-status-dot {
  width: 8px; height: 8px;
  background: #4ADE80;
  border-radius: 50%;
  animation: ob-pulse-dot 2s infinite;
}
.ob-hero-right {
  position: relative; z-index: 2;
  flex-shrink: 0;
  margin-left: 1.5rem;
}

/* ─── KPI Cards ─────────────────────────────────────────────── */
.ob-kpi {
  display: block;
  border-radius: var(--d-r);
  padding: 1.4rem 1.5rem;
  color: #fff;
  text-decoration: none;
  position: relative;
  overflow: hidden;
  transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s;
  box-shadow: var(--d-shadow);
  margin-bottom: 0;
}
.ob-kpi:hover { transform: translateY(-6px); box-shadow: var(--d-shadow-lg); color: #fff; }
.ob-kpi::after {
  content: '';
  position: absolute;
  right: -28px; bottom: -28px;
  width: 150px; height: 150px;
  background: rgba(255,255,255,.09);
  border-radius: 50%;
  pointer-events: none;
}
.ob-kpi::before {
  content: '';
  position: absolute;
  right: 55px; bottom: -60px;
  width: 100px; height: 100px;
  background: rgba(255,255,255,.06);
  border-radius: 50%;
  pointer-events: none;
}

.ob-kpi--doctors  { background: linear-gradient(135deg, #1252A3 0%, #2575D0 60%, #41A8D8 100%); }
.ob-kpi--practices{ background: linear-gradient(135deg, #0C6B4A 0%, #10A372 60%, #30D49B 100%); }
.ob-kpi--cases    { background: linear-gradient(135deg, #5B1F9E 0%, #8B5CF6 60%, #B89EF8 100%); }
.ob-kpi--products { background: linear-gradient(135deg, #B26010 0%, #E8920A 60%, #FBCA55 100%); }

.ob-kpi-icon-wrap {
  position: absolute;
  top: 1.2rem; right: 1.2rem;
  width: 46px; height: 46px;
  background: rgba(255,255,255,.16);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  backdrop-filter: blur(4px);
}
.ob-kpi-icon-wrap svg { width: 22px !important; height: 22px !important; }

.ob-kpi-badge {
  display: flex;
  align-items: center;
  gap: .35rem;
  font-size: .69rem;
  font-weight: 700;
  letter-spacing: .9px;
  text-transform: uppercase;
  opacity: .82;
  margin-bottom: .45rem;
}
.ob-kpi-badge svg { width: 11px !important; height: 11px !important; }

.ob-kpi-num {
  font-size: 2.75rem;
  font-weight: 900;
  line-height: 1;
  letter-spacing: -1.5px;
  margin: .15rem 0 .4rem;
}
.ob-kpi-sub { font-size: .78rem; opacity: .75; font-weight: 500; }

.ob-kpi-pills { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .45rem; }
.ob-pill {
  font-size: .68rem;
  font-weight: 700;
  border-radius: 20px;
  padding: .12rem .58rem;
  background: rgba(255,255,255,.18);
}
.ob-pill--success { background: rgba(74,222,128,.25); }
.ob-pill--warn    { background: rgba(251,191,36,.3); }

/* ─── White Section Cards ─────────────────────────────────── */
.ob-card {
  background: #fff;
  border-radius: var(--d-r);
  box-shadow: var(--d-shadow);
  padding: 1.5rem;
  border: 1px solid var(--d-border);
}
.ob-card-title {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .7rem;
  font-weight: 800;
  letter-spacing: 1.3px;
  text-transform: uppercase;
  color: var(--d-slate);
  margin-bottom: 1.15rem;
}
.ob-card-title-bar {
  width: 4px; height: 18px;
  border-radius: 3px;
  flex-shrink: 0;
}

/* Stat rows */
.ob-stat-row {
  display: flex;
  align-items: center;
  padding: .6rem .5rem;
  border-radius: 10px;
  text-decoration: none;
  color: inherit;
  transition: background .15s;
  border-bottom: 1px solid #F1F5F9;
}
.ob-stat-row:last-child { border-bottom: none; }
.ob-stat-row:hover { background: #F4F8FD; color: inherit; }
.ob-stat-icon-wrap {
  width: 38px; height: 38px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  margin-right: .85rem;
}
.ob-stat-icon-wrap svg { width: 18px !important; height: 18px !important; }
.ob-stat-name { flex: 1; font-size: .88rem; font-weight: 600; }
.ob-stat-num  { font-size: 1.3rem; font-weight: 800; color: #1E293B; min-width: 44px; text-align: right; }

/* Mini stat tiles */
.ob-mini-tiles { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #F1F5F9; }
.ob-mini-tile  {
  flex: 1; min-width: 80px;
  background: #F4F8FD;
  border: 1px solid #E2EAF4;
  border-radius: 10px;
  padding: .75rem .85rem;
}
.ob-mini-num   { font-size: 1.4rem; font-weight: 800; color: #1E293B; line-height: 1; }
.ob-mini-label { font-size: .67rem; font-weight: 700; color: #64748B; letter-spacing: .6px; text-transform: uppercase; margin-top: .18rem; }

/* ─── Coverage Card (dark) ────────────────────────────────── */
.ob-coverage {
  background: linear-gradient(155deg, #0F172A 0%, #1E293B 100%);
  border-radius: var(--d-r);
  color: #fff;
  padding: 1.5rem;
  box-shadow: var(--d-shadow-lg);
  position: relative;
  overflow: hidden;
  height: 100%;
}
.ob-coverage::before {
  content: '';
  position: absolute;
  top: -55px; right: -55px;
  width: 210px; height: 210px;
  background: radial-gradient(circle, rgba(14,165,197,.15) 0%, transparent 65%);
  pointer-events: none;
}
.ob-coverage::after {
  content: '';
  position: absolute;
  bottom: -65px; left: -35px;
  width: 190px; height: 190px;
  background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 65%);
  pointer-events: none;
}

.ob-cov-title {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .7rem;
  font-weight: 800;
  letter-spacing: 1.3px;
  text-transform: uppercase;
  color: rgba(255,255,255,.5);
  margin-bottom: 1.2rem;
}
.ob-cov-title-bar { width: 4px; height: 18px; background: #0EA5C5; border-radius: 3px; flex-shrink: 0; }

.ob-cov-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .7rem;
  position: relative; z-index: 1;
}
.ob-cov-item {
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 12px;
  padding: .85rem .95rem;
  text-decoration: none;
  display: block;
  transition: background .18s, border-color .18s, transform .18s;
}
.ob-cov-item:hover {
  background: rgba(14,165,197,.18);
  border-color: rgba(14,165,197,.45);
  transform: translateY(-2px);
}
.ob-cov-num   { font-size: 1.65rem; font-weight: 800; color: #fff; line-height: 1; }
.ob-cov-label {
  display: flex; align-items: center; gap: .25rem;
  font-size: .67rem; font-weight: 700;
  letter-spacing: .7px; text-transform: uppercase;
  color: rgba(255,255,255,.52);
  margin-top: .25rem;
}
.ob-cov-label svg { width: 11px !important; height: 11px !important; }

/* ─── Quick Access ────────────────────────────────────────── */
.ob-qa-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(108px, 1fr));
  gap: .8rem;
}
.ob-qa-btn {
  display: flex; flex-direction: column; align-items: center;
  gap: .42rem; padding: .95rem .4rem;
  background: #F4F8FD;
  border: 1.5px solid #E2EAF4;
  border-radius: var(--d-r-sm);
  text-decoration: none;
  color: #1E293B;
  font-size: .73rem; font-weight: 700;
  text-align: center; line-height: 1.25;
  transition: all .2s cubic-bezier(.34,1.56,.64,1);
}
.ob-qa-btn:hover {
  background: #1E293B;
  border-color: #1E293B;
  color: #fff;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(30,41,59,.22);
}
.ob-qa-icon {
  width: 42px; height: 42px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s;
}
.ob-qa-icon svg { width: 20px !important; height: 20px !important; }
.ob-qa-btn:hover .ob-qa-icon { background: rgba(255,255,255,.15) !important; }

/* ─── Dark mode ──────────────────────────────────────────── */
.dark-layout .ob-card          { background: #283046; border-color: rgba(255,255,255,.06); }
.dark-layout .ob-card-title    { color: rgba(255,255,255,.45); }
.dark-layout .ob-stat-row      { color: #cfd3d9; border-bottom-color: rgba(255,255,255,.07); }
.dark-layout .ob-stat-row:hover{ background: rgba(255,255,255,.04); }
.dark-layout .ob-stat-num      { color: #d0d2d6; }
.dark-layout .ob-mini-tile     { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.08); }
.dark-layout .ob-mini-num      { color: #d0d2d6; }
.dark-layout .ob-mini-label    { color: rgba(255,255,255,.4); }
.dark-layout .ob-mini-tiles    { border-top-color: rgba(255,255,255,.08); }
.dark-layout .ob-qa-btn        { background: #3b4253; border-color: rgba(255,255,255,.08); color: #d0d2d6; }
.dark-layout .ob-qa-btn:hover  { background: #0EA5C5; border-color: #0EA5C5; color: #fff; }
</style>
@endpush

@section('content')
@php
    $hour = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $adminFirstName = auth()->user()->admin?->first_name ?? '';
    $heroName = $adminFirstName ? ', ' . $adminFirstName : '';
@endphp

<div id="ob-dash">

    {{-- ══════════════════════════════════════════════════════
         HERO BANNER
    ══════════════════════════════════════════════════════ --}}
    <div class="ob-hero">
        <div class="ob-hero-left">
            <p class="ob-hero-eyebrow">
                <i data-feather="calendar"></i>
                {{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp;
                <span id="ob-clock"></span>
            </p>
            <h1 class="ob-hero-heading">{{ $greet }}{{ $heroName }}</h1>
            <p class="ob-hero-sub">Here's your OrthoBrain platform overview for today.</p>
            <div class="ob-status-badge">
                <span class="ob-status-dot"></span>
                All systems operational
            </div>
        </div>

        <div class="ob-hero-right d-none d-lg-flex align-items-center">
            {{-- Animated 3D-style Tooth SVG --}}
            <svg class="ob-tooth-anim" width="118" height="150" viewBox="0 0 120 155" fill="none"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <radialGradient id="tCrown" cx="38%" cy="28%" r="66%">
                        <stop offset="0%"   stop-color="#FFFFFF"/>
                        <stop offset="58%"  stop-color="#D6EEFA"/>
                        <stop offset="100%" stop-color="#A4CAE7"/>
                    </radialGradient>
                    <linearGradient id="tRoot" x1="50%" y1="0%" x2="50%" y2="100%">
                        <stop offset="0%"   stop-color="#EDD5A8"/>
                        <stop offset="100%" stop-color="#B8864E"/>
                    </linearGradient>
                    <radialGradient id="tGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0%"   stop-color="#0EA5C5" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#0EA5C5" stop-opacity="0"/>
                    </radialGradient>
                    <filter id="tDrop" x="-20%" y="-15%" width="145%" height="145%">
                        <feDropShadow dx="0" dy="5" stdDeviation="6"
                                      flood-color="#0EA5C5" flood-opacity="0.4"/>
                    </filter>
                </defs>

                <!-- Glow base -->
                <ellipse cx="60" cy="149" rx="34" ry="7" fill="url(#tGlow)"/>

                <!-- Roots -->
                <path d="M35 87 Q30 109 27 129 Q25 138 30 140 Q37 141 39 131 Q41 113 43 93"
                      fill="url(#tRoot)" opacity=".92"/>
                <path d="M60 91 Q59 115 58 134 Q57 142 62 143 Q66 143 66 135 Q66 117 65 94"
                      fill="url(#tRoot)" opacity=".92"/>
                <path d="M85 87 Q90 109 93 129 Q95 138 90 140 Q83 141 81 131 Q79 113 77 93"
                      fill="url(#tRoot)" opacity=".92"/>

                <!-- Crown body -->
                <path d="M17 45 C15 24 23 10 39 8 C48 6 54 16 60 16 C66 16 72 6 81 8
                         C97 10 105 24 103 45 C101 64 92 84 81 90 C70 95 50 95 39 90
                         C28 84 19 64 17 45Z"
                      fill="url(#tCrown)"
                      stroke="rgba(158,208,240,.65)" stroke-width="1.5"
                      filter="url(#tDrop)"/>

                <!-- Cusps -->
                <path d="M33 30 C29 19 35 10 41 12 C47 15 47 25 44 33"
                      fill="rgba(255,255,255,.34)" stroke="rgba(192,228,248,.4)" stroke-width=".8"/>
                <path d="M53 24 C51 12 59 7 65 10 C71 13 70 23 66 31"
                      fill="rgba(255,255,255,.34)" stroke="rgba(192,228,248,.4)" stroke-width=".8"/>
                <path d="M75 27 C73 14 81 9 87 13 C93 16 91 27 87 34"
                      fill="rgba(255,255,255,.34)" stroke="rgba(192,228,248,.4)" stroke-width=".8"/>

                <!-- Central fissure -->
                <path d="M44 34 Q60 43 76 35" stroke="rgba(138,190,220,.58)"
                      stroke-width="1.8" fill="none" stroke-linecap="round"/>
                <path d="M60 40 L60 70" stroke="rgba(138,190,220,.44)"
                      stroke-width="1.4" fill="none" stroke-linecap="round"/>

                <!-- Specular highlights (enamel shine) -->
                <ellipse cx="45" cy="34" rx="12" ry="18"
                         fill="rgba(255,255,255,.27)" transform="rotate(-18 45 34)"/>
                <ellipse cx="40" cy="27" rx="5.5" ry="8"
                         fill="rgba(255,255,255,.46)" transform="rotate(-22 40 27)"/>
            </svg>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         PRIMARY KPI CARDS
    ══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-2">

        {{-- Doctors --}}
        <div class="col-xl-3 col-sm-6 ob-anim-1">
            <a href="{{ route('admin.doctors.index') }}" class="ob-kpi ob-kpi--doctors">
                <div class="ob-kpi-icon-wrap"><i data-feather="user-check"></i></div>
                <div class="ob-kpi-badge"><i data-feather="users"></i>&nbsp;Doctors</div>
                <div class="ob-kpi-num" data-count-to="{{ $stats['doctors_total'] }}">{{ $stats['doctors_total'] }}</div>
                <div class="ob-kpi-pills">
                    <span class="ob-pill ob-pill--success">✓ {{ $stats['doctors_approved'] }} approved</span>
                    @if($stats['doctors_pending'] > 0)
                        <span class="ob-pill ob-pill--warn">⏳ {{ $stats['doctors_pending'] }} pending</span>
                    @endif
                </div>
            </a>
        </div>

        {{-- Practices --}}
        <div class="col-xl-3 col-sm-6 ob-anim-2">
            <a href="{{ route('admin.practices.index') }}" class="ob-kpi ob-kpi--practices">
                <div class="ob-kpi-icon-wrap"><i data-feather="briefcase"></i></div>
                <div class="ob-kpi-badge"><i data-feather="home"></i>&nbsp;Practices</div>
                <div class="ob-kpi-num" data-count-to="{{ $stats['practices_total'] }}">{{ $stats['practices_total'] }}</div>
                <div class="ob-kpi-sub">Registered dental practices</div>
            </a>
        </div>

        {{-- Cases --}}
        <div class="col-xl-3 col-sm-6 ob-anim-3">
            <a href="{{ route('admin.cases.index') }}" class="ob-kpi ob-kpi--cases">
                <div class="ob-kpi-icon-wrap"><i data-feather="folder"></i></div>
                <div class="ob-kpi-badge"><i data-feather="clipboard"></i>&nbsp;Cases</div>
                <div class="ob-kpi-num" data-count-to="{{ $stats['cases_total'] }}">{{ $stats['cases_total'] }}</div>
                <div class="ob-kpi-sub">Treatment prescriptions</div>
            </a>
        </div>

        {{-- Products --}}
        <div class="col-xl-3 col-sm-6 ob-anim-4">
            <a href="{{ route('admin.products.index') }}" class="ob-kpi ob-kpi--products">
                <div class="ob-kpi-icon-wrap"><i data-feather="box"></i></div>
                <div class="ob-kpi-badge"><i data-feather="package"></i>&nbsp;Products</div>
                <div class="ob-kpi-num" data-count-to="{{ $stats['products'] }}">{{ $stats['products'] }}</div>
                <div class="ob-kpi-sub">In product catalog</div>
            </a>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         CATALOG MANAGEMENT  +  GLOBAL REACH
    ══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-2">

        {{-- Catalog Management --}}
        <div class="col-xl-8 ob-anim-5">
            <div class="ob-card h-100">
                <div class="ob-card-title">
                    <span class="ob-card-title-bar" style="background:#0EA5C5"></span>
                    Catalog Management
                </div>

                @php
                    $catalogRows = [
                        ['label' => 'Categories',     'key' => 'categories',   'route' => 'admin.product-categories.index',   'icon' => 'tag',    'bg' => '#EFF6FF', 'color' => '#2563EB'],
                        ['label' => 'Sub-Categories', 'key' => 'subcategories','route' => 'admin.product-subcategories.index','icon' => 'layers', 'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                        ['label' => 'Scanners',       'key' => 'scanners',     'route' => 'admin.scanners.index',             'icon' => 'cpu',    'bg' => '#ECFDF5', 'color' => '#059669'],
                    ];
                @endphp

                @foreach($catalogRows as $item)
                    <a href="{{ route($item['route']) }}" class="ob-stat-row">
                        <div class="ob-stat-icon-wrap"
                             style="background:{{ $item['bg'] }}; color:{{ $item['color'] }}">
                            <i data-feather="{{ $item['icon'] }}"></i>
                        </div>
                        <span class="ob-stat-name">{{ $item['label'] }}</span>
                        <span class="ob-stat-num"
                              data-count-to="{{ $stats[$item['key']] }}">{{ $stats[$item['key']] }}</span>
                    </a>
                @endforeach

                {{-- Mini snapshot tiles --}}
                <div class="ob-mini-tiles">
                    @php
                        $snap = [
                            ['label' => 'Products',      'key' => 'products'],
                            ['label' => 'Categories',    'key' => 'categories'],
                            ['label' => 'Sub-Cats',      'key' => 'subcategories'],
                            ['label' => 'Scanners',      'key' => 'scanners'],
                        ];
                    @endphp
                    @foreach($snap as $s)
                        <div class="ob-mini-tile">
                            <div class="ob-mini-num"
                                 data-count-to="{{ $stats[$s['key']] }}">{{ $stats[$s['key']] }}</div>
                            <div class="ob-mini-label">{{ $s['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Global Reach --}}
        <div class="col-xl-4 ob-anim-6">
            <div class="ob-coverage">
                <div class="ob-cov-title">
                    <span class="ob-cov-title-bar"></span>
                    Global Reach
                </div>
                <div class="ob-cov-grid">
                    @php
                        $covItems = [
                            ['label' => 'Countries', 'key' => 'countries', 'route' => 'admin.countries.index', 'icon' => 'globe'],
                            ['label' => 'States',    'key' => 'states',    'route' => 'admin.states.index',    'icon' => 'map'],
                            ['label' => 'Cities',    'key' => 'cities',    'route' => 'admin.cities.index',    'icon' => 'map-pin'],
                            ['label' => 'Zip Codes', 'key' => 'zipcodes',  'route' => 'admin.zipcodes.index',  'icon' => 'mail'],
                        ];
                    @endphp
                    @foreach($covItems as $c)
                        <a href="{{ route($c['route']) }}" class="ob-cov-item">
                            <div class="ob-cov-num"
                                 data-count-to="{{ $stats[$c['key']] }}">{{ $stats[$c['key']] }}</div>
                            <div class="ob-cov-label">
                                <i data-feather="{{ $c['icon'] }}"></i>
                                {{ $c['label'] }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         QUICK ACCESS
    ══════════════════════════════════════════════════════ --}}
    <div class="ob-card" style="animation: ob-fade-up .45s ease .35s both;">
        <div class="ob-card-title">
            <span class="ob-card-title-bar" style="background:#1E293B"></span>
            Quick Access
        </div>
        <div class="ob-qa-grid">
            @php
                $qaItems = [
                    ['label' => 'Doctors',      'route' => 'admin.doctors.index',               'icon' => 'user-check', 'bg' => '#EFF6FF', 'color' => '#2563EB'],
                    ['label' => 'Practices',    'route' => 'admin.practices.index',             'icon' => 'briefcase',  'bg' => '#ECFDF5', 'color' => '#059669'],
                    ['label' => 'Cases',        'route' => 'admin.cases.index',                 'icon' => 'folder',     'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                    ['label' => 'Products',     'route' => 'admin.products.index',              'icon' => 'box',        'bg' => '#FFFBEB', 'color' => '#D97706'],
                    ['label' => 'Categories',   'route' => 'admin.product-categories.index',    'icon' => 'tag',        'bg' => '#EFF6FF', 'color' => '#2563EB'],
                    ['label' => 'Sub-Cats',     'route' => 'admin.product-subcategories.index', 'icon' => 'layers',     'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                    ['label' => 'Scanners',     'route' => 'admin.scanners.index',              'icon' => 'cpu',        'bg' => '#ECFDF5', 'color' => '#059669'],
                    ['label' => 'Countries',    'route' => 'admin.countries.index',             'icon' => 'globe',      'bg' => '#FFF1F2', 'color' => '#E11D48'],
                    ['label' => 'States',       'route' => 'admin.states.index',                'icon' => 'map',        'bg' => '#FFF7ED', 'color' => '#EA580C'],
                    ['label' => 'Cities',       'route' => 'admin.cities.index',                'icon' => 'map-pin',    'bg' => '#F0FDF4', 'color' => '#16A34A'],
                    ['label' => 'Zip Codes',    'route' => 'admin.zipcodes.index',              'icon' => 'mail',       'bg' => '#EFF6FF', 'color' => '#0EA5E9'],
                ];
            @endphp
            @foreach($qaItems as $qa)
                <a href="{{ route($qa['route']) }}" class="ob-qa-btn">
                    <div class="ob-qa-icon"
                         style="background:{{ $qa['bg'] }}; color:{{ $qa['color'] }}">
                        <i data-feather="{{ $qa['icon'] }}"></i>
                    </div>
                    <span>{{ $qa['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    // ── Live clock ────────────────────────────────────────────
    var clockEl = document.getElementById('ob-clock');
    function tickClock() {
        if (clockEl) {
            clockEl.textContent = new Date().toLocaleTimeString([], {
                hour: '2-digit', minute: '2-digit'
            });
        }
    }
    tickClock();
    setInterval(tickClock, 1000);

    // ── Count-up animation ────────────────────────────────────
    function animateCount(el) {
        var target = parseInt(el.dataset.countTo, 10);
        if (!target || isNaN(target)) return;
        var duration = 1100, start = null;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            var ease = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('[data-count-to]').forEach(animateCount);
})();
</script>
@endpush
