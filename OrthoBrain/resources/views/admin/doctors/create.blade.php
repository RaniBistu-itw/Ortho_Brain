@extends('layouts.admin')
@section('title', 'Add Doctor')
@section('page_title', 'Add Doctor')

@push('styles')
<style>
    /* ──────────────────────────────────────────────────────────────
       Admin · Add Doctor. Scroll-spy rail + simultaneous sections.
       All styles scoped under #obw so nothing leaks.
       Same cyan-accent theme as the rest of the admin, just tokenised.
       ────────────────────────────────────────────────────────────── */

    #obw {
        /* ── Design tokens (single source of truth) ── */
        --obw-accent:       #5bc0de;
        --obw-accent-600:   #46b8da;
        --obw-accent-soft:  rgba(91,192,222,.10);
        --obw-accent-line:  rgba(91,192,222,.28);
        --obw-success:      #28c76f;
        --obw-success-soft: rgba(40,199,111,.12);
        --obw-warn:         #ff9f43;
        --obw-danger:       #ea5455;
        --obw-danger-soft:  rgba(234,84,85,.08);

        --obw-ink:          #5e5873;
        --obw-ink-2:        #6e6b7b;
        --obw-mute:         #b9b9c3;
        --obw-line:         #ebe9f1;
        --obw-line-2:       #f3f2f7;
        --obw-surface:      #ffffff;
        --obw-surface-alt:  #fcfcfd;
        --obw-bg-soft:      #f8fbfc;

        --obw-radius:       .6rem;
        --obw-radius-sm:    .428rem;
        --obw-shadow:       0 2px 14px rgba(34,41,47,.06);
        --obw-shadow-lg:    0 8px 28px rgba(34,41,47,.08);

        position: relative;
        padding-bottom: 5.5rem;
        color: var(--obw-ink);
    }

    #obw .obw-layout { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
    @media (min-width: 992px) {
        #obw .obw-layout { grid-template-columns: 244px 1fr; gap: 1.75rem; align-items: start; }
    }

    /* ── Sticky scroll-spy rail ─────────────────────────────── */
    #obw .obw-rail {
        position: sticky; top: 6rem;
        background: var(--obw-surface);
        border: 1px solid var(--obw-line);
        border-radius: var(--obw-radius);
        box-shadow: var(--obw-shadow);
        padding: 1.15rem 1rem;
    }
    #obw .obw-rail-title {
        font-size: .7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--obw-mute);
        margin: 0 0 .85rem;
    }
    #obw .obw-progress {
        height: 6px; background: var(--obw-line-2);
        border-radius: 999px; overflow: hidden; margin-bottom: .5rem;
    }
    #obw .obw-progress-fill {
        height: 100%; width: 0%;
        background: linear-gradient(90deg, var(--obw-accent), var(--obw-accent-600));
        border-radius: 999px;
        transition: width .35s ease;
    }
    #obw .obw-progress-meta {
        display: flex; justify-content: space-between;
        font-size: .72rem; color: var(--obw-ink-2);
        margin-bottom: 1rem;
        font-weight: 500;
    }
    #obw .obw-progress-meta strong { color: var(--obw-ink); font-weight: 600; }

    #obw .obw-steps {
        list-style: none; margin: 0; padding: 0;
        display: flex; flex-direction: column; gap: .25rem;
    }
    #obw .obw-step {
        display: flex; gap: .75rem; align-items: center;
        padding: .6rem .7rem;
        border-radius: var(--obw-radius-sm);
        cursor: pointer;
        color: var(--obw-ink);
        border: 1px solid transparent;
        transition: background .15s, border-color .15s, transform .1s;
        user-select: none;
    }
    #obw .obw-step:hover { background: var(--obw-bg-soft); }
    #obw .obw-step:active { transform: scale(.99); }
    #obw .obw-step.is-current {
        background: var(--obw-accent-soft);
        border-color: var(--obw-accent-line);
    }
    #obw .obw-step.is-current .obw-step-title { color: var(--obw-accent-600); }

    #obw .obw-step-dot {
        flex-shrink: 0;
        width: 26px; height: 26px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--obw-line-2); color: var(--obw-mute);
        font-weight: 600; font-size: .75rem;
        transition: all .2s;
    }
    #obw .obw-step.is-current .obw-step-dot {
        background: var(--obw-accent); color: #fff;
        box-shadow: 0 0 0 4px var(--obw-accent-soft);
    }
    #obw .obw-step.is-complete .obw-step-dot { background: var(--obw-success); color: #fff; }
    #obw .obw-step.is-complete .obw-step-dot .obw-num { display: none; }
    #obw .obw-step.is-complete .obw-step-dot::after {
        content: ''; width: 11px; height: 11px;
        background: no-repeat center/contain
          url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>");
    }
    #obw .obw-step.is-error .obw-step-dot { background: var(--obw-danger); color: #fff; }
    #obw .obw-step.is-error .obw-step-dot .obw-num { display: none; }
    #obw .obw-step.is-error .obw-step-dot::after { content: '!'; color: #fff; font-weight: 700; }

    #obw .obw-step-body { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; }
    #obw .obw-step-title { font-weight: 600; font-size: .875rem; }
    #obw .obw-step-meta  { font-size: .7rem; color: var(--obw-mute); margin-top: 1px; }

    /* ── Main pane + sections ─────────────────────────────── */
    #obw .obw-pane { min-width: 0; display: flex; flex-direction: column; gap: 1.1rem; }
    #obw .obw-section { scroll-margin-top: 6rem; }
    #obw .obw-section > .card {
        border: 1px solid var(--obw-line);
        border-radius: var(--obw-radius);
        box-shadow: var(--obw-shadow);
        overflow: hidden;
    }
    #obw .obw-section .card-header {
        align-items: center; justify-content: space-between; gap: 1rem;
        background: var(--obw-surface);
        border-bottom: 1px solid var(--obw-line);
        padding: 1.1rem 1.5rem;
    }
    #obw .obw-section .card-body { padding: 1.5rem; }

    #obw .obw-section-title {
        font-size: 1.0625rem; font-weight: 600;
        color: var(--obw-ink); margin: 0;
        letter-spacing: -.01em;
    }
    #obw .obw-section-sub {
        font-size: .8125rem; color: var(--obw-ink-2);
        margin: .2rem 0 0; line-height: 1.45;
    }
    #obw .obw-section-counter {
        font-size: .7rem; font-weight: 600;
        padding: .3rem .7rem; border-radius: 999px;
        background: var(--obw-accent-soft); color: var(--obw-accent-600);
        white-space: nowrap;
        border: 1px solid var(--obw-accent-line);
    }
    #obw .obw-section-counter.is-complete {
        background: var(--obw-success-soft); color: var(--obw-success);
        border-color: rgba(40,199,111,.25);
    }
    #obw .obw-section-counter.is-optional {
        background: var(--obw-line-2); color: var(--obw-mute);
        border-color: var(--obw-line);
    }

    /* Error summary (shown on submit failure inside a section) */
    #obw .obw-error-summary {
        border-left: 3px solid var(--obw-danger);
        background: var(--obw-danger-soft);
        padding: .85rem 1rem; border-radius: var(--obw-radius-sm);
        margin-bottom: 1.25rem;
    }
    #obw .obw-error-summary h6 {
        font-size: .8125rem; color: var(--obw-danger);
        margin: 0 0 .35rem; font-weight: 600;
    }
    #obw .obw-error-summary ul { margin: 0; padding-left: 1.1rem; font-size: .8125rem; color: var(--obw-ink); }
    #obw .obw-error-summary li a { color: var(--obw-danger); text-decoration: underline; cursor: pointer; }

    /* ── Admin-context banner (calmer, component-ised) ────── */
    #obw .obw-admin-banner {
        display: flex; align-items: center; gap: .9rem;
        padding: .9rem 1.1rem;
        background: var(--obw-accent-soft);
        border: 1px solid var(--obw-accent-line);
        border-radius: var(--obw-radius);
        color: var(--obw-ink);
    }
    #obw .obw-admin-banner .obw-admin-banner-icon {
        flex-shrink: 0;
        width: 34px; height: 34px; border-radius: 50%;
        background: #fff; color: var(--obw-accent);
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: 0 1px 3px rgba(91,192,222,.25);
    }
    #obw .obw-admin-banner strong { color: var(--obw-ink); font-weight: 600; }
    #obw .obw-admin-banner p {
        margin: .1rem 0 0; font-size: .8125rem;
        color: var(--obw-ink-2); line-height: 1.45;
    }

    /* ── Form controls ────────────────────────────────────── */
    #obw .form-label {
        font-size: .8125rem; font-weight: 500;
        color: var(--obw-ink); margin-bottom: .45rem;
    }
    #obw .form-control,
    #obw .form-select {
        font-size: .875rem;
        border-color: var(--obw-line);
        color: var(--obw-ink);
        padding: .5rem .85rem;
        border-radius: var(--obw-radius-sm);
        transition: border-color .15s, box-shadow .15s;
    }
    #obw .form-control::placeholder { color: var(--obw-mute); }
    #obw .form-control:focus,
    #obw .form-select:focus {
        border-color: var(--obw-accent);
        box-shadow: 0 0 0 .2rem var(--obw-accent-soft);
    }
    #obw .input-group-text {
        background: var(--obw-surface-alt);
        border-color: var(--obw-line);
        color: var(--obw-mute);
        padding: .5rem .75rem;
    }
    #obw .obw-hint { font-size: .75rem; color: var(--obw-mute); margin-top: .35rem; line-height: 1.4; }

    /* ── Password strength (grouped, not scattered) ───────── */
    #obw .obw-pw-meter {
        height: 4px; background: var(--obw-line);
        border-radius: 999px; margin-top: .55rem; overflow: hidden;
    }
    #obw .obw-pw-meter-fill { height: 100%; width: 0%; transition: width .25s, background .25s; border-radius: 999px; }
    #obw .obw-pw-meter-fill[data-strength="1"] { background: var(--obw-danger); width: 20%; }
    #obw .obw-pw-meter-fill[data-strength="2"] { background: var(--obw-warn);   width: 45%; }
    #obw .obw-pw-meter-fill[data-strength="3"] { background: #ffc107;           width: 70%; }
    #obw .obw-pw-meter-fill[data-strength="4"] { background: var(--obw-success); width: 90%; }
    #obw .obw-pw-meter-fill[data-strength="5"] { background: var(--obw-success); width: 100%; }
    #obw .obw-pw-label {
        font-size: .72rem; color: var(--obw-ink-2);
        margin-top: .4rem; display: flex; justify-content: space-between;
        font-weight: 500;
    }
    #obw .obw-pw-rules {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: .4rem .9rem;
        margin-top: .55rem;
        padding: .7rem .85rem;
        background: var(--obw-bg-soft);
        border: 1px dashed var(--obw-line);
        border-radius: var(--obw-radius-sm);
    }
    #obw .obw-pw-rules .obw-pw-rule {
        font-size: .72rem; color: var(--obw-mute);
        display: flex; align-items: center; gap: .4rem;
    }
    #obw .obw-pw-rules .obw-pw-rule.ok { color: var(--obw-success); }
    #obw .obw-pw-rules .obw-pw-rule .obw-pw-rule-dot {
        width: 12px; height: 12px; border-radius: 50%; border: 1.5px solid currentColor;
        display: inline-flex; align-items: center; justify-content: center; font-size: 8px;
    }
    #obw .obw-pw-rules .obw-pw-rule.ok .obw-pw-rule-dot { background: currentColor; }
    #obw .obw-pw-rules .obw-pw-rule.ok .obw-pw-rule-dot::after {
        content: ''; width: 6px; height: 6px;
        background: no-repeat center/contain
          url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'><polyline points='20 6 9 17 4 12'/></svg>");
    }
    #obw .obw-pw-toggle { cursor: pointer; color: var(--obw-mute); transition: color .15s; }
    #obw .obw-pw-toggle:hover { color: var(--obw-accent); }

    /* ── Practice picker chip ────────────────────────────── */
    #obw .obw-practice-chip {
        display: flex; gap: .85rem; align-items: center;
        padding: .9rem 1rem;
        background: var(--obw-accent-soft);
        border: 1px solid var(--obw-accent-line);
        border-radius: var(--obw-radius);
    }
    #obw .obw-practice-chip .obw-chip-icon {
        width: 42px; height: 42px; border-radius: var(--obw-radius-sm);
        background: #fff; color: var(--obw-accent);
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        box-shadow: 0 1px 3px rgba(91,192,222,.2);
    }
    #obw .obw-practice-chip .obw-chip-body { flex: 1; min-width: 0; }
    #obw .obw-practice-chip .obw-chip-title { font-weight: 600; color: var(--obw-ink); font-size: .9375rem; }
    #obw .obw-practice-chip .obw-chip-meta  { font-size: .75rem; color: var(--obw-ink-2); margin-top: 2px; }

    /* Practice mode link — slightly more button-y */
    #obw .obw-practice-mode-link {
        color: var(--obw-accent-600); font-weight: 500; font-size: .78rem;
        text-decoration: none; padding: .25rem .55rem;
        border-radius: 999px; background: var(--obw-accent-soft);
        transition: background .15s;
    }
    #obw .obw-practice-mode-link:hover { background: var(--obw-accent-line); color: var(--obw-accent-600); }

    /* ── Location chips (zip auto-fill) ──────────────────── */
    #obw .obw-location-chips {
        display: flex; flex-wrap: wrap; gap: .5rem;
        margin-top: .45rem;
        min-height: 40px; align-items: center;
    }
    #obw .obw-location-chip {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .45rem .8rem; border-radius: 999px;
        background: var(--obw-surface-alt);
        border: 1px solid var(--obw-line);
        font-size: .75rem; color: var(--obw-ink);
        font-weight: 500;
    }
    #obw .obw-location-chip[data-empty="1"] { color: var(--obw-mute); font-style: italic; border-style: dashed; }
    #obw .obw-location-chip .obw-location-chip-label {
        color: var(--obw-mute); text-transform: uppercase;
        font-size: .6rem; letter-spacing: .06em; font-weight: 700;
        font-style: normal;
    }

    /* Address eyebrow inside §2 */
    #obw .obw-eyebrow {
        display: block;
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .08em;
        color: var(--obw-mute);
        margin: 0 0 .85rem;
    }

    /* ── Preferences bands (visual grouping of §3 cards) ── */
    #obw .obw-band { margin-bottom: 1.75rem; }
    #obw .obw-band:last-child { margin-bottom: 0; }
    #obw .obw-band-head {
        display: flex; align-items: center; gap: .65rem;
        padding-bottom: .75rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--obw-line);
    }
    #obw .obw-band-head .obw-band-num {
        flex-shrink: 0;
        width: 22px; height: 22px; border-radius: 50%;
        background: var(--obw-accent-soft); color: var(--obw-accent-600);
        font-size: .7rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }
    #obw .obw-band-head h6 {
        margin: 0;
        font-size: .72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .08em;
        color: var(--obw-ink-2);
    }

    #obw .obw-pref-grid { display: grid; grid-template-columns: 1fr; gap: .85rem; }
    @media (min-width: 768px) { #obw .obw-pref-grid { grid-template-columns: 1fr 1fr; } }
    #obw .obw-pref-grid.obw-pref-grid-4 { grid-template-columns: 1fr; }
    @media (min-width: 768px)  { #obw .obw-pref-grid.obw-pref-grid-4 { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1200px) { #obw .obw-pref-grid.obw-pref-grid-4 { grid-template-columns: repeat(4, 1fr); } }

    #obw .obw-pref-card {
        border: 1px solid var(--obw-line);
        border-radius: var(--obw-radius-sm);
        padding: 1rem 1.1rem;
        background: var(--obw-surface);
        display: flex; flex-direction: column; gap: .7rem;
        transition: border-color .15s, box-shadow .15s;
    }
    #obw .obw-pref-card:hover { border-color: var(--obw-accent-line); }
    #obw .obw-pref-card:focus-within {
        border-color: var(--obw-accent);
        box-shadow: 0 0 0 .15rem var(--obw-accent-soft);
    }
    #obw .obw-pref-card h6 {
        font-size: .8125rem; font-weight: 600; color: var(--obw-ink);
        margin: 0; display: flex; align-items: center; gap: .4rem;
    }
    #obw .obw-pref-card .form-check { margin-bottom: 0; }
    #obw .obw-pref-card .form-check-label {
        font-size: .8125rem; color: var(--obw-ink-2); line-height: 1.45;
    }
    #obw .obw-pref-card.obw-pref-card-wide { grid-column: 1 / -1; }

    /* Toggle cluster — list-group style, no hairline seam */
    #obw .obw-toggle-group {
        border: 1px solid var(--obw-line);
        border-radius: var(--obw-radius-sm);
        overflow: hidden;
        background: var(--obw-surface);
    }
    #obw .obw-toggle-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: .85rem 1rem;
        border: 0;
        border-bottom: 1px solid var(--obw-line);
        background: var(--obw-surface);
    }
    #obw .obw-toggle-row .form-check-label { font-size: .875rem; font-weight: 500; color: var(--obw-ink); }
    #obw .obw-toggle-row small { font-size: .75rem; color: var(--obw-mute); }
    #obw .obw-toggle-body {
        padding: .9rem 1rem;
        background: var(--obw-bg-soft);
        border: 0;
        border-bottom: 1px solid var(--obw-line);
    }
    #obw .obw-toggle-group > *:last-child { border-bottom: 0; }
    #obw .obw-toggle-body[hidden] { display: none !important; }

    /* Bootstrap form-switch tuned to brand cyan */
    #obw .form-check-input:checked { background-color: var(--obw-accent); border-color: var(--obw-accent); }
    #obw .form-check-input:focus   { border-color: var(--obw-accent); box-shadow: 0 0 0 .2rem var(--obw-accent-soft); }

    /* ── Smooth conditional reveals ───────────────────────── */
    #obw .obw-reveal { overflow: hidden; transition: max-height .25s ease, opacity .2s ease, margin .2s ease; }
    #obw .obw-reveal[hidden] {
        display: block !important; max-height: 0; opacity: 0;
        margin-top: 0 !important; margin-bottom: 0 !important;
        pointer-events: none;
    }

    /* ── Sticky action bar (Cancel + Save) ────────────────── */
    #obw .obw-actionbar {
        position: fixed; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,.96);
        backdrop-filter: saturate(180%) blur(8px);
        -webkit-backdrop-filter: saturate(180%) blur(8px);
        border-top: 1px solid var(--obw-line);
        box-shadow: 0 -4px 20px rgba(34,41,47,.06);
        z-index: 1040;
        padding: .85rem 1.5rem;
    }
    #obw .obw-actionbar-inner {
        max-width: 1440px; margin: 0 auto;
        display: flex; align-items: center; gap: .75rem;
        justify-content: flex-end;
    }
    #obw .obw-actionbar-hint {
        margin-right: auto; font-size: .8125rem;
        color: var(--obw-ink-2); font-weight: 500;
        display: inline-flex; align-items: center; gap: .5rem;
    }
    #obw .obw-actionbar-hint i { color: var(--obw-success); }

    /* ── Dark-mode parity (token override) ────────────────── */
    [data-theme="dark"] #obw {
        --obw-surface:     #283046;
        --obw-surface-alt: #242b3d;
        --obw-bg-soft:     #1e2440;
        --obw-ink:         #d0d2d6;
        --obw-ink-2:       #b4b7bd;
        --obw-mute:        #676d7d;
        --obw-line:        #3b4253;
        --obw-line-2:      #323a50;
        --obw-shadow:      0 2px 14px rgba(0,0,0,.22);
    }
    [data-theme="dark"] #obw .obw-admin-banner .obw-admin-banner-icon,
    [data-theme="dark"] #obw .obw-practice-chip .obw-chip-icon { background: #1e2440; }
    [data-theme="dark"] #obw .obw-actionbar { background: rgba(40,48,70,.96); }
</style>
@endpush

@section('content')
<div id="obw"
     data-practice-search-url="{{ route('practice.search') }}"
     data-doctors-index-url="{{ route('admin.doctors.index') }}"
     data-server-error-fields="{{ $errors->any() ? json_encode(array_keys($errors->toArray())) : '' }}">
    <form id="obw-form"
          action="{{ route('admin.doctors.store') }}"
          method="POST"
          class="ob-form-validate"
          novalidate
          autocomplete="off">
        @csrf

        <div class="obw-layout">

            {{-- ── Sticky scroll-spy rail ───────────────────────────── --}}
            <aside class="obw-rail" aria-label="Section navigation">
                <h5 class="obw-rail-title">Add Doctor</h5>

                <div class="obw-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div class="obw-progress-fill" id="obw-progress"></div>
                </div>
                <div class="obw-progress-meta">
                    <span><strong id="obw-fields-done">0</strong>/<span id="obw-fields-total">8</span> required</span>
                    <span id="obw-completion-pct">0%</span>
                </div>

                <ol class="obw-steps">
                    <li class="obw-step is-current" data-target="#obw-section-1" data-step="1" tabindex="0" role="button" aria-label="Jump to Account">
                        <span class="obw-step-dot"><span class="obw-num">1</span></span>
                        <div class="obw-step-body">
                            <span class="obw-step-title">Account</span>
                            <span class="obw-step-meta">Email, name &amp; password</span>
                        </div>
                    </li>
                    <li class="obw-step" data-target="#obw-section-2" data-step="2" tabindex="0" role="button" aria-label="Jump to Practice">
                        <span class="obw-step-dot"><span class="obw-num">2</span></span>
                        <div class="obw-step-body">
                            <span class="obw-step-title">Practice &amp; Address</span>
                            <span class="obw-step-meta">Existing or new practice</span>
                        </div>
                    </li>
                    <li class="obw-step" data-target="#obw-section-3" data-step="3" tabindex="0" role="button" aria-label="Jump to Preferences">
                        <span class="obw-step-dot"><span class="obw-num">3</span></span>
                        <div class="obw-step-body">
                            <span class="obw-step-title">Preferences</span>
                            <span class="obw-step-meta">Ortho, contact &amp; clinical</span>
                        </div>
                    </li>
                </ol>
            </aside>

            {{-- ── Main pane — every section visible simultaneously ── --}}
            <div class="obw-pane">

                {{-- Server-side error banner --}}
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <h6 class="alert-heading mb-50">Please fix the following before submitting:</h6>
                        <ul class="mb-0 ps-3" style="font-size:.85rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Admin context banner --}}
                <div class="obw-admin-banner" role="note">
                    <span class="obw-admin-banner-icon"><i data-feather="shield"></i></span>
                    <div>
                        <strong>Admin-created account.</strong>
                        <p>This doctor will be auto-approved on save — they can log in immediately and won't go through the pending queue.</p>
                    </div>
                </div>

                {{-- ─── §1 Account ─────────────────────────────────── --}}
                <section class="obw-section" id="obw-section-1" data-step="1" data-required="email,first_name,last_name,password,confirm_password">
                    <div class="card">
                        <div class="card-header d-flex">
                            <div>
                                <h2 class="obw-section-title">Account</h2>
                                <p class="obw-section-sub">Login credentials &amp; basic identity for the new doctor.</p>
                            </div>
                            <span class="obw-section-counter" data-counter-step="1">0 / 5</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="email" class="form-label">Email (Username)<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i data-feather="mail"></i></span>
                                        <input id="email" name="email" type="email" required
                                               value="{{ old('email') }}"
                                               placeholder="doctor@example.com"
                                               class="form-control @error('email') is-invalid @enderror"
                                               aria-describedby="email-err">
                                    </div>
                                    <div id="email-err" class="invalid-feedback d-block" data-err-for="email">@error('email'){{ $message }}@enderror</div>
                                    <div class="obw-hint">This email becomes the doctor's login username and their default contact email.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i data-feather="user"></i></span>
                                        <input id="first_name" name="first_name" type="text" required
                                               value="{{ old('first_name') }}"
                                               placeholder="Jane"
                                               class="form-control @error('first_name') is-invalid @enderror"
                                               aria-describedby="first_name-err">
                                    </div>
                                    <div id="first_name-err" class="invalid-feedback d-block" data-err-for="first_name">@error('first_name'){{ $message }}@enderror</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i data-feather="user"></i></span>
                                        <input id="last_name" name="last_name" type="text" required
                                               value="{{ old('last_name') }}"
                                               placeholder="Doe"
                                               class="form-control @error('last_name') is-invalid @enderror"
                                               aria-describedby="last_name-err">
                                    </div>
                                    <div id="last_name-err" class="invalid-feedback d-block" data-err-for="last_name">@error('last_name'){{ $message }}@enderror</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i data-feather="lock"></i></span>
                                        <input id="password" name="password" type="password" required
                                               placeholder="At least 8 characters"
                                               class="form-control @error('password') is-invalid @enderror"
                                               aria-describedby="password-err password-rules">
                                        <button type="button" class="input-group-text obw-pw-toggle" aria-label="Show password" data-target="password">
                                            <i data-feather="eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-err" class="invalid-feedback d-block" data-err-for="password">@error('password'){{ $message }}@enderror</div>
                                    <div class="obw-pw-meter"><div class="obw-pw-meter-fill" data-strength="0"></div></div>
                                    <div class="obw-pw-label"><span>Password strength</span><span id="password-strength-label">—</span></div>
                                    <div class="obw-pw-rules" id="password-rules" aria-live="polite">
                                        <span class="obw-pw-rule" data-rule="len"><span class="obw-pw-rule-dot"></span>8+ characters</span>
                                        <span class="obw-pw-rule" data-rule="upper"><span class="obw-pw-rule-dot"></span>Uppercase letter</span>
                                        <span class="obw-pw-rule" data-rule="lower"><span class="obw-pw-rule-dot"></span>Lowercase letter</span>
                                        <span class="obw-pw-rule" data-rule="digit"><span class="obw-pw-rule-dot"></span>Number</span>
                                        <span class="obw-pw-rule" data-rule="special"><span class="obw-pw-rule-dot"></span>Special character</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">Confirm Password<span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i data-feather="lock"></i></span>
                                        <input id="confirm_password" name="confirm_password" type="password" required
                                               placeholder="Re-enter password"
                                               class="form-control"
                                               aria-describedby="confirm_password-err">
                                        <button type="button" class="input-group-text obw-pw-toggle" aria-label="Show password" data-target="confirm_password">
                                            <i data-feather="eye"></i>
                                        </button>
                                    </div>
                                    <div id="confirm_password-err" class="invalid-feedback d-block" data-err-for="confirm_password"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ─── §2 Practice & Address ─────────────────────── --}}
                <section class="obw-section" id="obw-section-2" data-step="2" data-required="practice_name,practice_phone_number,practice_website,street_address_1,zip_id">
                    <div class="card">
                        <div class="card-header d-flex">
                            <div>
                                <h2 class="obw-section-title">Practice &amp; Address</h2>
                                <p class="obw-section-sub">Search for an existing practice or enter details for a new one. Address auto-fills from zip.</p>
                            </div>
                            <span class="obw-section-counter" data-counter-step="2">0 / 6</span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>Practice<span class="text-danger">*</span></span>
                                        <a href="#" class="obw-practice-mode-link" id="obw-toggle-practice-mode">Practice not listed? Create new &rarr;</a>
                                    </label>
                                    <input type="hidden" id="practice_id" name="practice_id" value="{{ old('practice_id') }}">
                                    <select id="obw-practice-search"
                                            class="form-select"
                                            data-placeholder="Search by practice name…"
                                            aria-label="Search existing practice">
                                        <option></option>
                                        @if (old('practice_id') && old('practice_name'))
                                            <option value="{{ old('practice_id') }}" selected>{{ old('practice_name') }}</option>
                                        @endif
                                    </select>
                                    <div class="obw-hint">Typing 2+ characters searches the practice directory.</div>
                                </div>

                                {{-- Picked-practice chip --}}
                                <div class="col-12 obw-reveal" id="obw-practice-chip-wrap" hidden>
                                    <div class="obw-practice-chip mt-1">
                                        <span class="obw-chip-icon"><i data-feather="briefcase"></i></span>
                                        <div class="obw-chip-body">
                                            <div class="obw-chip-title" id="obw-chip-name">—</div>
                                            <div class="obw-chip-meta" id="obw-chip-meta">—</div>
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="obw-change-practice">
                                            <i data-feather="edit-2" class="me-25"></i>Change
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- New-practice fields (hidden when an existing practice is picked) --}}
                            <div class="obw-reveal" id="obw-new-practice">
                                <hr class="my-3" style="border-color: var(--obw-line); opacity:1;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="practice_name" class="form-label">Practice Name<span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-feather="briefcase"></i></span>
                                            <input id="practice_name" name="practice_name" type="text"
                                                   value="{{ old('practice_name') }}"
                                                   placeholder="e.g. Wall Street Dental"
                                                   class="form-control @error('practice_name') is-invalid @enderror">
                                        </div>
                                        <div id="practice_name-err" class="invalid-feedback d-block" data-err-for="practice_name">@error('practice_name'){{ $message }}@enderror</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="practice_phone_number" class="form-label">Practice Phone<span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select id="practice_phone_country_code" name="practice_phone_country_code" class="form-select js-searchable" style="max-width:110px;">
                                                <option value="+1"  @selected(old('practice_phone_country_code','+1') === '+1')>+1 (US/CA)</option>
                                                <option value="+61" @selected(old('practice_phone_country_code') === '+61')>+61 (AU)</option>
                                            </select>
                                            <input id="practice_phone_number" name="practice_phone_number" type="tel"
                                                   value="{{ old('practice_phone_number') }}"
                                                   maxlength="10" inputmode="numeric"
                                                   placeholder="10-digit phone"
                                                   class="form-control @error('practice_phone_number') is-invalid @enderror">
                                        </div>
                                        <div id="practice_phone_number-err" class="invalid-feedback d-block" data-err-for="practice_phone_number">@error('practice_phone_number'){{ $message }}@enderror</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="practice_website" class="form-label">Practice Website<span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-feather="globe"></i></span>
                                            <input id="practice_website" name="practice_website" type="text"
                                                   value="{{ old('practice_website') }}"
                                                   placeholder="www.example.com"
                                                   class="form-control @error('practice_website') is-invalid @enderror">
                                        </div>
                                        <div id="practice_website-err" class="invalid-feedback d-block" data-err-for="practice_website">@error('practice_website'){{ $message }}@enderror</div>
                                    </div>
                                </div>

                                <hr class="my-4" style="border-color: var(--obw-line); opacity:1;">
                                <span class="obw-eyebrow">Practice Address</span>

                                <input type="hidden" name="city_id"    id="city_id"    value="{{ old('city_id') }}">
                                <input type="hidden" name="state_id"   id="state_id"   value="{{ old('state_id') }}">
                                <input type="hidden" name="country_id" id="country_id" value="{{ old('country_id') }}">

                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label for="street_address_1" class="form-label">Street Address<span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i data-feather="map-pin"></i></span>
                                            <input id="street_address_1" name="street_address_1" type="text"
                                                   value="{{ old('street_address_1') }}"
                                                   placeholder="123 Main Street"
                                                   class="form-control @error('street_address_1') is-invalid @enderror">
                                        </div>
                                        <div id="street_address_1-err" class="invalid-feedback d-block" data-err-for="street_address_1">@error('street_address_1'){{ $message }}@enderror</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="street_address_2" class="form-label">Suite / Apt <span class="text-muted small">(optional)</span></label>
                                        <input id="street_address_2" name="street_address_2" type="text"
                                               value="{{ old('street_address_2') }}"
                                               placeholder="Suite 400"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-5">
                                        <label for="zip_id" class="form-label">Zip / Postal Code<span class="text-danger">*</span></label>
                                        <select id="zip_id" name="zip_id" required class="form-select js-searchable @error('zip_id') is-invalid @enderror"
                                                data-placeholder="Select a zip code">
                                            <option value="">Select a zip code</option>
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
                                        <div id="zip_id-err" class="invalid-feedback d-block" data-err-for="zip_id">@error('zip_id'){{ $message }}@enderror</div>
                                    </div>

                                    <div class="col-md-7">
                                        <label class="form-label">Auto-filled location</label>
                                        <div class="obw-location-chips">
                                            <span class="obw-location-chip" data-chip="city" data-empty="1">
                                                <span class="obw-location-chip-label">City</span><span class="obw-location-chip-val">—</span>
                                            </span>
                                            <span class="obw-location-chip" data-chip="state" data-empty="1">
                                                <span class="obw-location-chip-label">State</span><span class="obw-location-chip-val">—</span>
                                            </span>
                                            <span class="obw-location-chip" data-chip="country" data-empty="1">
                                                <span class="obw-location-chip-label">Country</span><span class="obw-location-chip-val">—</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ─── §3 Preferences ─────────────────────────────── --}}
                <section class="obw-section" id="obw-section-3" data-step="3">
                    <div class="card">
                        <div class="card-header d-flex">
                            <div>
                                <h2 class="obw-section-title">Preferences</h2>
                                <p class="obw-section-sub">Optional clinical &amp; contact preferences — the doctor can edit these later from their profile.</p>
                            </div>
                            <span class="obw-section-counter is-optional">Optional</span>
                        </div>
                        <div class="card-body">

                            {{-- Band 1 · Contact preferences --}}
                            <div class="obw-band">
                                <div class="obw-band-head">
                                    <span class="obw-band-num">1</span>
                                    <h6>Contact</h6>
                                </div>
                                <div class="obw-pref-grid">

                                <div class="obw-pref-card">
                                    <h6>Currently providing orthodontic services?</h6>
                                    <div class="d-flex gap-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="providing_ortho" id="providing_yes" value="yes" @checked(old('providing_ortho') === 'yes')>
                                            <label class="form-check-label" for="providing_yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="providing_ortho" id="providing_no" value="no" @checked(old('providing_ortho') === 'no')>
                                            <label class="form-check-label" for="providing_no">No</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="obw-pref-card">
                                    <h6>Preferred doctor contact</h6>
                                    <div class="d-flex flex-column gap-25">
                                        <div class="form-check">
                                            <input class="form-check-input obw-contact-radio" type="radio" name="contact_preference" id="contact_doctor"   value="doctor"   @checked(old('contact_preference') === 'doctor')>
                                            <label class="form-check-label" for="contact_doctor">Doctor only</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input obw-contact-radio" type="radio" name="contact_preference" id="contact_employee" value="employee" @checked(old('contact_preference') === 'employee')>
                                            <label class="form-check-label" for="contact_employee">Employee / office</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input obw-contact-radio" type="radio" name="contact_preference" id="contact_both"     value="both"     @checked(old('contact_preference') === 'both')>
                                            <label class="form-check-label" for="contact_both">Doctor and employee / office</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="obw-pref-card obw-pref-card-wide obw-reveal" id="obw-contact-doctor" hidden>
                                    <h6><i data-feather="user" class="me-25"></i>Doctor contact details</h6>
                                    <div class="row g-1">
                                        <div class="col-md-6">
                                            <label class="form-label">Doctor Email</label>
                                            <input type="email" name="contact_doctor_email" value="{{ old('contact_doctor_email') }}" class="form-control" placeholder="name@example.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Doctor Cell Phone</label>
                                            <input type="text" name="contact_doctor_phone" value="{{ old('contact_doctor_phone') }}" class="form-control" placeholder="XXX-XXX-XXXX">
                                        </div>
                                    </div>
                                    <div id="obw-doctor-emails" class="mt-1"></div>
                                    <button type="button" class="btn btn-outline-primary btn-sm align-self-start" data-add-email="doctor">
                                        <i data-feather="plus" class="me-25"></i>Add another email
                                    </button>
                                </div>

                                <div class="obw-pref-card obw-pref-card-wide obw-reveal" id="obw-contact-employee" hidden>
                                    <h6><i data-feather="users" class="me-25"></i>Employee / office contact details</h6>
                                    <div class="row g-1">
                                        <div class="col-md-6">
                                            <label class="form-label">Employee Name</label>
                                            <input type="text" name="contact_emp_name" value="{{ old('contact_emp_name') }}" class="form-control" placeholder="Full name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Employee Title</label>
                                            <input type="text" name="contact_emp_title" value="{{ old('contact_emp_title') }}" class="form-control" placeholder="Office Manager">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Office / Employee Email</label>
                                            <input type="email" name="contact_emp_email" value="{{ old('contact_emp_email') }}" class="form-control" placeholder="office@example.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Office / Employee Phone</label>
                                            <input type="text" name="contact_emp_phone" value="{{ old('contact_emp_phone') }}" class="form-control" placeholder="XXX-XXX-XXXX">
                                        </div>
                                    </div>
                                    <div id="obw-emp-emails" class="mt-1"></div>
                                    <button type="button" class="btn btn-outline-primary btn-sm align-self-start" data-add-email="emp">
                                        <i data-feather="plus" class="me-25"></i>Add another email
                                    </button>
                                </div>

                                </div>
                            </div>

                            {{-- Band 2 · Clinical defaults --}}
                            <div class="obw-band">
                                <div class="obw-band-head">
                                    <span class="obw-band-num">2</span>
                                    <h6>Clinical defaults</h6>
                                </div>
                                <div class="obw-pref-grid obw-pref-grid-4">

                                <div class="obw-pref-card">
                                    <h6>Modalities offered</h6>
                                    <div class="d-flex flex-column gap-25">
                                        @foreach(($modalitiesList ?? collect()) as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="modalities[]" id="mod_{{ $opt->id }}"
                                                       value="{{ $opt->id }}"
                                                       @checked(in_array((string) $opt->id, (array) old('modalities', []), true))>
                                                <label class="form-check-label" for="mod_{{ $opt->id }}">{{ $opt->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="obw-pref-card">
                                    <h6>Specialties</h6>
                                    <div class="d-flex flex-column gap-25">
                                        @foreach(($specialtiesList ?? collect()) as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="specialties[]" id="spec_{{ $opt->id }}"
                                                       value="{{ $opt->id }}"
                                                       @checked(in_array((string) $opt->id, (array) old('specialties', []), true))>
                                                <label class="form-check-label" for="spec_{{ $opt->id }}">{{ $opt->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="obw-pref-card">
                                    <h6>Preferred treatment modality</h6>
                                    <div class="d-flex flex-column gap-25">
                                        @foreach(($treatmentModalitiesList ?? collect()) as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="treatment_modalities[]" id="tm_{{ $opt->id }}"
                                                       value="{{ $opt->id }}"
                                                       @checked(in_array((string) $opt->id, (array) old('treatment_modalities', []), true))>
                                                <label class="form-check-label" for="tm_{{ $opt->id }}">{{ $opt->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="obw-pref-card">
                                    <h6>Preferred tooth numbering system</h6>
                                    <div class="d-flex flex-column gap-25">
                                        <div class="form-check"><input class="form-check-input" type="radio" name="tooth_numbering" id="tn_u" checked><label class="form-check-label" for="tn_u">Universal (1–32)</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="tooth_numbering" id="tn_f"><label class="form-check-label" for="tn_f">FDI (11–48)</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="tooth_numbering" id="tn_p"><label class="form-check-label" for="tn_p">Palmer (UR1–UR8)</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="tooth_numbering" id="tn_i"><label class="form-check-label" for="tn_i">International (11–48)</label></div>
                                    </div>
                                </div>

                                </div>
                            </div>

                            {{-- Band 3 · Treatment approach --}}
                            <div class="obw-band">
                                <div class="obw-band-head">
                                    <span class="obw-band-num">3</span>
                                    <h6>Treatment approach</h6>
                                </div>
                                <div class="obw-pref-grid">

                                <div class="obw-pref-card">
                                    <h6>Smile arc</h6>
                                    <div class="d-flex flex-column gap-25">
                                        <div class="form-check"><input class="form-check-input" type="radio" name="smile_arc" id="sa_1" checked><label class="form-check-label" for="sa_1">Defer to orthobrain&reg;</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="smile_arc" id="sa_2"><label class="form-check-label" for="sa_2">Lateral incisors .5mm shorter than central incisors</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="smile_arc" id="sa_3"><label class="form-check-label" for="sa_3">Lateral incisors same length as central incisors</label></div>
                                    </div>
                                </div>

                                <div class="obw-pref-card">
                                    <h6>Treatment of small lateral incisors</h6>
                                    <div class="d-flex flex-column gap-25">
                                        <div class="form-check"><input class="form-check-input" type="radio" name="lateral_incisors" id="li_1" checked><label class="form-check-label" for="li_1">Defer to orthobrain&reg;</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="lateral_incisors" id="li_2"><label class="form-check-label" for="li_2">Interproximal Reduction (IPR) on lower arch to camouflage</label></div>
                                        <div class="form-check"><input class="form-check-input" type="radio" name="lateral_incisors" id="li_3"><label class="form-check-label" for="li_3">Leave spacing mesial and distal to maxillary laterals for future cosmetic correction</label></div>
                                    </div>
                                </div>

                                <div class="obw-pref-card">
                                    <h6>Buccal corridors</h6>
                                    <div class="d-flex flex-column gap-25">
                                        @foreach(($buccalCorridorsList ?? collect()) as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="buccal_corridors[]" id="bc_{{ $opt->id }}"
                                                       value="{{ $opt->id }}"
                                                       @checked(in_array((string) $opt->id, (array) old('buccal_corridors', []), true))>
                                                <label class="form-check-label" for="bc_{{ $opt->id }}">{!! $opt->name !!}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="obw-pref-card obw-pref-card-wide">
                                    <h6>Mixed dentition &amp; bite correcting appliances</h6>
                                    <div class="d-flex flex-column gap-25">
                                        <div class="form-check"><input class="form-check-input" type="radio" name="mixed_dentition" id="md_1" checked><label class="form-check-label" for="md_1">Defer to orthobrain&reg;</label></div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="mixed_dentition" id="md_2">
                                            <label class="form-check-label" for="md_2" style="font-size:.82rem;">I prefer not to use any growth and adjunctive appliances (e.g., expanders, bite planes, bit correctors, herbst, etc.) and request a proposal for a best outcome without an appliance — fully understanding that this may not be an ideal Perfect Smile Plan for optimal results.</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="obw-pref-card obw-pref-card-wide">
                                    <h6>Orthodontic extractions</h6>
                                    <div class="d-flex flex-column gap-25">
                                        <div class="form-check"><input class="form-check-input" type="radio" name="ortho_extractions" id="oe_1" checked><label class="form-check-label" for="oe_1">Defer to orthobrain&reg;</label></div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ortho_extractions" id="oe_2">
                                            <label class="form-check-label" for="oe_2" style="font-size:.82rem;">I prefer not to extract teeth and request a proposal for a best outcome without extractions — fully understanding that this may not be an ideal treatment plan for optimal results.</label>
                                        </div>
                                    </div>
                                </div>

                                </div>
                            </div>

                            {{-- Band 4 · Advanced clinical toggles --}}
                            <div class="obw-band">
                                <div class="obw-band-head">
                                    <span class="obw-band-num">4</span>
                                    <h6>Advanced clinical toggles</h6>
                                </div>

                                <div class="obw-toggle-group">
                                    <div class="obw-toggle-row">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input obw-toggle-trigger" type="checkbox" id="t_ipr" data-target="body_ipr">
                                            <label class="form-check-label" for="t_ipr">IPR Protocol</label>
                                        </div>
                                        <small class="text-muted">Custom IPR preference</small>
                                    </div>
                                    <div class="obw-toggle-body" id="body_ipr" hidden>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="ipr_opt" id="ipr_1" checked><label class="form-check-label" for="ipr_1">Defer to orthobrain&reg;</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="ipr_opt" id="ipr_2"><label class="form-check-label" for="ipr_2">No IPR</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="ipr_opt" id="ipr_3"><label class="form-check-label" for="ipr_3">Other</label></div>
                                        </div>
                                    </div>

                                    <div class="obw-toggle-row">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input obw-toggle-trigger" type="checkbox" id="t_att" data-target="body_att">
                                            <label class="form-check-label" for="t_att">Attachments</label>
                                        </div>
                                        <small class="text-muted">When to place attachments</small>
                                    </div>
                                    <div class="obw-toggle-body" id="body_att" hidden>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="attachment_opt" id="att_1" checked><label class="form-check-label" for="att_1">At Aligner Step 1</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="attachment_opt" id="att_2"><label class="form-check-label" for="att_2">At Aligner Step (other)</label></div>
                                        </div>
                                    </div>

                                    <div class="obw-toggle-row">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input obw-toggle-trigger" type="checkbox" id="t_elast" data-target="body_elast">
                                            <label class="form-check-label" for="t_elast">Elastics / Bonded Buttons</label>
                                        </div>
                                        <small class="text-muted">Use elastics or bonded buttons</small>
                                    </div>
                                    <div class="obw-toggle-body" id="body_elast" hidden>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="elastics_opt" id="elast_1" checked><label class="form-check-label" for="elast_1">Yes</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="elastics_opt" id="elast_2"><label class="form-check-label" for="elast_2">No</label></div>
                                        </div>
                                    </div>

                                    <div class="obw-toggle-row">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input obw-toggle-trigger" type="checkbox" id="t_ext" data-target="body_ext">
                                            <label class="form-check-label" for="t_ext">Extractions if suggested</label>
                                        </div>
                                        <small class="text-muted">Accept extractions when suggested</small>
                                    </div>
                                    <div class="obw-toggle-body" id="body_ext" hidden>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check"><input class="form-check-input" type="radio" name="extractions_opt" id="ext_1" checked><label class="form-check-label" for="ext_1">Yes</label></div>
                                            <div class="form-check"><input class="form-check-input" type="radio" name="extractions_opt" id="ext_2"><label class="form-check-label" for="ext_2">No</label></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

                {{-- ─── §4 Confirm ──────────────────────────────────── --}}
                <section class="obw-section" id="obw-section-4" data-step="4" data-required="terms_agreed">
                    <div class="card">
                        <div class="card-header d-flex">
                            <div>
                                <h2 class="obw-section-title">Confirm</h2>
                                <p class="obw-section-sub">One last check before saving.</p>
                            </div>
                            <span class="obw-section-counter" data-counter-step="4">0 / 1</span>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-50">
                                <input class="form-check-input" type="checkbox" name="terms_agreed" id="terms_agreed" @checked(old('terms_agreed'))>
                                <label class="form-check-label" for="terms_agreed">
                                    The doctor has agreed to the <a href="#" target="_blank" class="obw-practice-mode-link" style="padding:0;background:transparent;">Terms and Conditions</a>.<span class="text-danger">*</span>
                                </label>
                                <div id="terms_agreed-err" class="invalid-feedback d-block" data-err-for="terms_agreed">@error('terms_agreed'){{ $message }}@enderror</div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="sms_agreed" id="sms_agreed" @checked(old('sms_agreed'))>
                                <label class="form-check-label" for="sms_agreed">
                                    The doctor agrees to receive SMS messages for authentication purposes.
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>

        {{-- ─── Sticky footer (Cancel + Save only) ──────────────── --}}
        <div class="obw-actionbar">
            <div class="obw-actionbar-inner">
                <span class="obw-actionbar-hint">
                    <i data-feather="check-circle"></i>
                    Saved doctors are auto-approved and can log in immediately.
                </span>
                <button type="submit" class="btn btn-primary me-1" id="obw-save">Save Doctor</button>
                <button type="button" class="btn btn-outline-secondary" id="obw-cancel">Cancel</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    'use strict';

    const $root = $('#obw');
    const PRACTICE_SEARCH_URL = $root.data('practice-search-url');
    const DOCTORS_INDEX_URL   = $root.data('doctors-index-url');
    const SERVER_ERROR_FIELDS = (() => {
        const raw = $root.data('server-error-fields');
        if (!raw) return [];
        if (typeof raw === 'string') { try { return JSON.parse(raw); } catch (e) { return []; } }
        return raw;
    })();

    let isDirty = false;

    // Per-step state: 'pending' | 'current' | 'complete' | 'error'
    const stepState = { 1: 'current', 2: 'pending', 3: 'pending', 4: 'pending' };

    function setStepState(step, state) {
        stepState[step] = state;
        $('.obw-step[data-step="' + step + '"]')
            .removeClass('is-current is-complete is-error')
            .addClass(state === 'pending' ? '' : 'is-' + state);
    }

    // ─── Scroll-spy: which section is currently in view ─────
    const $sections = $('.obw-section');
    function updateScrollSpy() {
        const offset = 180;
        let activeStep = 1;
        $sections.each(function () {
            const top = this.getBoundingClientRect().top;
            if (top - offset <= 0) activeStep = parseInt($(this).data('step'), 10);
        });
        $('.obw-step').each(function () {
            const s = parseInt($(this).data('step'), 10);
            if (s === activeStep && stepState[s] !== 'error' && stepState[s] !== 'complete') {
                setStepState(s, 'current');
            } else if (s !== activeStep && stepState[s] === 'current') {
                setStepState(s, 'pending');
            }
        });
    }
    $(window).on('scroll', updateScrollSpy);

    // ─── Rail click: smooth-scroll to the target section ────
    $('.obw-step').on('click keydown', function (e) {
        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
        e.preventDefault();
        const target = $($(this).data('target'));
        if (!target.length) return;
        $('html, body').animate({ scrollTop: target.offset().top - 100 }, 260);
    });

    // ─── Per-section required-field counter + progress ──────
    function countStep(step) {
        const $sec = $('#obw-section-' + step);
        const required = ($sec.data('required') || '').toString().split(',').filter(Boolean);
        let done = 0;
        required.forEach(name => {
            const $el = $sec.find('[name="' + name + '"]').first();
            if (!$el.length) { done += 1; return; } // field may be hidden for existing-practice path
            if ($el.is(':checkbox')) { if ($el.is(':checked')) done += 1; }
            else if ((($el.val() || '') + '').trim() !== '') done += 1;
        });
        // Practice step: if an existing practice is selected, all required fields are already set
        if (step === 2 && $('#practice_id').val()) return { done: required.length, total: required.length };
        return { done, total: required.length };
    }
    function refreshCounters() {
        let fullDone = 0, fullTotal = 0;
        [1, 2, 3, 4].forEach(s => {
            const { done, total } = countStep(s);
            const $c = $('[data-counter-step="' + s + '"]');
            if (total > 0 && $c.length) {
                $c.text(done + ' / ' + total).toggleClass('is-complete', done === total);
                if (done === total) {
                    if (stepState[s] === 'pending' || stepState[s] === 'current') setStepState(s, 'complete');
                } else if (stepState[s] === 'complete') {
                    setStepState(s, 'pending');
                }
            }
            fullDone  += done;
            fullTotal += total;
        });
        $('#obw-fields-done').text(fullDone);
        $('#obw-fields-total').text(fullTotal);
        const pct = fullTotal ? Math.round((fullDone / fullTotal) * 100) : 0;
        $('#obw-progress').css('width', pct + '%').closest('[role="progressbar"]').attr('aria-valuenow', pct);
        $('#obw-completion-pct').text(pct + '%');
    }
    $(document).on('input change', '#obw-form input, #obw-form select, #obw-form textarea', () => {
        isDirty = true;
        refreshCounters();
    });

    // ─── Inline validators ──────────────────────────────────
    const emailRe   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const nameRe    = /^[A-Za-z\s\-]+$/;
    const websiteRe = /^(https?:\/\/)?([\da-z\.\-]+)\.([a-z\.]{2,6})([\/\w \.\-]*)*\/?$/i;
    const pwSpecial = /[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\/]/;

    const V = {
        email: v => !v ? 'Email is required' : !emailRe.test(v) ? 'Enter a valid email address' : '',
        first_name: v => !v ? 'First name is required' : v.length < 2 ? 'Minimum 2 characters' : !nameRe.test(v) ? 'Letters, spaces and hyphens only' : '',
        last_name:  v => !v ? 'Last name is required'  : v.length < 2 ? 'Minimum 2 characters' : !nameRe.test(v) ? 'Letters, spaces and hyphens only' : '',
        password:   v => !v ? 'Password is required' :
            (v.length < 8 || !/[A-Z]/.test(v) || !/[a-z]/.test(v) || !/\d/.test(v) || !pwSpecial.test(v))
                ? 'Min 8 chars with upper, lower, number & special' : '',
        confirm_password: v => {
            if (!v) return 'Please confirm the password';
            if (v !== $('#password').val()) return 'Passwords do not match';
            return '';
        },
        practice_name: () => {
            if ($('#practice_id').val()) return '';
            const v = ($('#practice_name').val() || '').trim();
            return !v ? 'Practice name is required' : '';
        },
        practice_phone_number: () => {
            if ($('#practice_id').val()) return '';
            const v = ($('#practice_phone_number').val() || '').trim();
            return !v ? 'Phone is required' : !/^\d{10}$/.test(v) ? 'Must be 10 digits' : '';
        },
        practice_website: () => {
            if ($('#practice_id').val()) return '';
            const v = ($('#practice_website').val() || '').trim();
            return !v ? 'Website is required' : !websiteRe.test(v) ? 'Enter a valid website' : '';
        },
        street_address_1: () => {
            if ($('#practice_id').val()) return '';
            const v = ($('#street_address_1').val() || '').trim();
            return !v ? 'Street address is required' : v.length < 5 ? 'Min 5 characters' : '';
        },
        zip_id: () => {
            if ($('#practice_id').val()) return '';
            const v = $('#zip_id').val();
            return !v ? 'Select a zip code' : '';
        },
        terms_agreed: () => $('#terms_agreed').is(':checked') ? '' : 'Terms must be accepted to create the account',
    };

    function showFieldError(name, msg) {
        const $input = $('#obw-form').find('[name="' + name + '"]').first();
        const $err = $('[data-err-for="' + name + '"]');
        $err.text(msg || '').toggleClass('d-block', !!msg);
        if (msg) {
            $input.addClass('is-invalid').attr('aria-invalid', 'true');
        } else {
            $input.removeClass('is-invalid').removeAttr('aria-invalid');
        }
    }

    // Live-validate on blur
    $('#obw-form').on('blur', 'input, select', function () {
        const name = $(this).attr('name');
        if (name && V[name]) showFieldError(name, V[name]($(this).val()));
    });
    $('#password').on('input', () => {
        if (($('#confirm_password').val() || '') !== '') showFieldError('confirm_password', V.confirm_password($('#confirm_password').val()));
    });
    $('#terms_agreed').on('change', function () {
        showFieldError('terms_agreed', V.terms_agreed());
        refreshCounters();
    });

    // ─── Password strength meter + rules ────────────────────
    $('#password').on('input', function () {
        const v = $(this).val() || '';
        const rules = {
            len:     v.length >= 8,
            upper:   /[A-Z]/.test(v),
            lower:   /[a-z]/.test(v),
            digit:   /\d/.test(v),
            special: pwSpecial.test(v),
        };
        let strength = 0;
        Object.values(rules).forEach(ok => ok && strength++);
        Object.entries(rules).forEach(([k, ok]) => {
            $('.obw-pw-rule[data-rule="' + k + '"]').toggleClass('ok', ok);
        });
        $('.obw-pw-meter-fill').attr('data-strength', v ? strength : 0);
        $('#password-strength-label').text(
            v === '' ? '—' :
            strength <= 1 ? 'Very weak' :
            strength === 2 ? 'Weak' :
            strength === 3 ? 'Fair' :
            strength === 4 ? 'Good' : 'Strong'
        );
    });

    // ─── Password show/hide ─────────────────────────────────
    $('.obw-pw-toggle').on('click', function () {
        const $t = $('#' + $(this).data('target'));
        const showing = $t.attr('type') === 'text';
        $t.attr('type', showing ? 'password' : 'text');
        $(this).find('i').attr('data-feather', showing ? 'eye' : 'eye-off');
        if (window.feather) window.feather.replace({ width: 14, height: 14 });
    });

    // ─── Practice picker (Select2 AJAX) ─────────────────────
    const $practiceSearch = $('#obw-practice-search');
    $practiceSearch.select2({
        width: '100%',
        allowClear: true,
        placeholder: 'Search practice by name…',
        minimumInputLength: 2,
        dropdownParent: $('#obw'),
        ajax: {
            url: PRACTICE_SEARCH_URL,
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: (data || []).map(p => ({ ...p, text: p.label, id: p.id }))
            }),
            cache: true
        }
    });

    $practiceSearch.on('select2:select', function (e) {
        const p = e.params.data;
        $('#practice_id').val(p.id);
        renderPracticeChip(p);
        lockNewPractice(true);
        $('#practice_name').val(p.name || '');
        $('#practice_phone_number').val(p.phone_number || '');
        $('#practice_website').val(p.website || '');
        if (p.phone_country_code) $('#practice_phone_country_code').val(p.phone_country_code);
        $('#street_address_1').val(p.street_address_1 || '');
        $('#street_address_2').val(p.street_address_2 || '');
        if (p.zip_id) {
            if (!$('#zip_id option[value="' + p.zip_id + '"]').length) {
                $('#zip_id').append(new Option((p.zip_code || '') + ' — ' + (p.city || ''), p.zip_id, true, true));
            }
            $('#zip_id').val(p.zip_id).trigger('change');
        }
        $('#city_id').val(p.city_id || '');
        $('#state_id').val(p.state_id || '');
        $('#country_id').val(p.country_id || '');
        refreshCounters();
    });

    $practiceSearch.on('select2:clear', clearPractice);
    $('#obw-change-practice').on('click', clearPractice);
    $('#obw-toggle-practice-mode').on('click', function (e) {
        e.preventDefault();
        clearPractice();
        $('#practice_name').trigger('focus');
    });

    function clearPractice() {
        $('#practice_id').val('');
        $('#obw-practice-chip-wrap').attr('hidden', true);
        lockNewPractice(false);
        $('#practice_name,#practice_phone_number,#practice_website').val('');
        $('#street_address_1,#street_address_2').val('');
        $('#zip_id').val('').trigger('change');
        $('#city_id,#state_id,#country_id').val('');
        $practiceSearch.val(null).trigger('change');
        refreshCounters();
    }

    function renderPracticeChip(p) {
        const addressLine = [p.street_address_1, p.city, p.state_code].filter(Boolean).join(', ');
        $('#obw-chip-name').text(p.name || p.label || 'Practice');
        $('#obw-chip-meta').text(addressLine || 'Existing practice selected');
        $('#obw-practice-chip-wrap').removeAttr('hidden');
        if (window.feather) window.feather.replace({ width: 14, height: 14 });
    }

    function lockNewPractice(locked) {
        $('#obw-new-practice').attr('hidden', locked ? true : null);
    }

    // ─── Zip → auto-fill chips ──────────────────────────────
    function syncZipChips() {
        const opt = document.querySelector('#zip_id option:checked');
        const fields = { city: '', state: '', country: '' };
        if (opt && opt.value) {
            fields.city    = opt.dataset.city || '';
            fields.state   = opt.dataset.state || '';
            fields.country = opt.dataset.country || '';
            $('#city_id').val(opt.dataset.cityId || '');
            $('#state_id').val(opt.dataset.stateId || '');
            $('#country_id').val(opt.dataset.countryId || '');
        } else {
            $('#city_id,#state_id,#country_id').val('');
        }
        Object.entries(fields).forEach(([k, v]) => {
            const $chip = $('.obw-location-chip[data-chip="' + k + '"]');
            $chip.attr('data-empty', v ? null : '1');
            $chip.find('.obw-location-chip-val').text(v || '—');
        });
    }
    $('#zip_id').on('change', syncZipChips);
    syncZipChips();
    window.obSearchable && window.obSearchable('#zip_id');

    // ─── Contact preference reveals ─────────────────────────
    function syncContactReveals() {
        const val = ($('input[name="contact_preference"]:checked').val()) || '';
        $('#obw-contact-doctor').attr('hidden', (val === 'doctor' || val === 'both') ? null : true);
        $('#obw-contact-employee').attr('hidden', (val === 'employee' || val === 'both') ? null : true);
    }
    $(document).on('change', '.obw-contact-radio', syncContactReveals);
    syncContactReveals();

    // ─── Repeatable "other email" rows ──────────────────────
    $(document).on('click', '[data-add-email]', function () {
        const which = $(this).data('add-email');
        const inputName = which === 'doctor' ? 'contact_doctor_other_emails[]' : 'contact_emp_other_emails[]';
        const target = which === 'doctor' ? '#obw-doctor-emails' : '#obw-emp-emails';
        const row = $(
            '<div class="input-group mb-50 obw-email-row">' +
              '<span class="input-group-text"><i data-feather="mail"></i></span>' +
              '<input type="email" name="' + inputName + '" class="form-control" placeholder="Additional email">' +
              '<button type="button" class="btn btn-outline-danger obw-email-remove" title="Remove"><i data-feather="trash-2"></i></button>' +
            '</div>'
        );
        $(target).append(row);
        if (window.feather) window.feather.replace({ width: 14, height: 14 });
    });
    $(document).on('click', '.obw-email-remove', function () { $(this).closest('.obw-email-row').remove(); });

    // ─── Toggle-based clinical preferences ──────────────────
    $(document).on('change', '.obw-toggle-trigger', function () {
        $('#' + $(this).data('target')).attr('hidden', $(this).is(':checked') ? null : true);
    });

    // ─── Submit: full validation + per-section error summary ─
    function validateAllAndSummarize() {
        let ok = true;
        const errorsByStep = { 1: [], 2: [], 3: [], 4: [] };

        const stepOf = {
            email: 1, first_name: 1, last_name: 1, password: 1, confirm_password: 1,
            practice_name: 2, practice_phone_number: 2, practice_website: 2,
            street_address_1: 2, zip_id: 2,
            terms_agreed: 4,
        };

        Object.keys(V).forEach(name => {
            const $el = $('[name="' + name + '"]').first();
            const msg = V[name]($el.val());
            showFieldError(name, msg);
            if (msg) {
                ok = false;
                (errorsByStep[stepOf[name] || 1] || []).push({ name, msg });
            }
        });

        // Per-section error summary banners
        [1, 2, 3, 4].forEach(s => {
            $('#obw-section-' + s).find('.obw-error-summary').remove();
            if (errorsByStep[s].length) {
                setStepState(s, 'error');
                const $alert = $('<div class="obw-error-summary" role="alert"><h6>Please fix the highlighted fields</h6><ul></ul></div>');
                errorsByStep[s].forEach(({ name, msg }) => {
                    const $li = $('<li><a href="#"></a></li>');
                    $li.find('a').text(msg).on('click', function (ev) {
                        ev.preventDefault();
                        const $t = $('[name="' + name + '"]').first();
                        if ($t.length) {
                            $('html, body').animate({ scrollTop: $t.offset().top - 140 }, 220, () => $t.focus());
                        }
                    });
                    $alert.find('ul').append($li);
                });
                $('#obw-section-' + s).find('.card-body').prepend($alert);
            }
        });

        return ok;
    }

    $('#obw-form').on('submit', function (e) {
        if (!validateAllAndSummarize()) {
            e.preventDefault();
            // Scroll to the first section with an error
            const $firstErr = $('.obw-step.is-error').first();
            if ($firstErr.length) {
                const target = $($firstErr.data('target'));
                if (target.length) $('html, body').animate({ scrollTop: target.offset().top - 100 }, 220);
            }
            return;
        }
        isDirty = false; // allow unload on successful submit
    });

    // ─── Cancel + unsaved guard ─────────────────────────────
    function confirmLeave() {
        if (!isDirty) { window.location.href = DOCTORS_INDEX_URL; return; }
        Swal.fire({
            title: 'Discard this draft?',
            text: 'You have unsaved changes. Leaving now will lose everything entered.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Discard & leave',
            cancelButtonText: 'Keep editing',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-outline-secondary ms-1' },
            buttonsStyling: false
        }).then(r => { if (r.isConfirmed) { isDirty = false; window.location.href = DOCTORS_INDEX_URL; } });
    }
    $('#obw-cancel').on('click', confirmLeave);
    // ─── Server-side error recovery: jump to earliest bad section ─
    if (SERVER_ERROR_FIELDS && SERVER_ERROR_FIELDS.length) {
        const stepFor = {
            email: 1, first_name: 1, last_name: 1, password: 1, confirm_password: 1,
            practice_id: 2, practice_name: 2, practice_phone_number: 2, practice_phone_country_code: 2,
            practice_website: 2, street_address_1: 2, street_address_2: 2,
            zip_id: 2, city_id: 2, state_id: 2, country_id: 2,
            terms_agreed: 4,
        };
        let target = 1;
        SERVER_ERROR_FIELDS.forEach(f => { if (stepFor[f]) target = Math.min(target === 1 ? stepFor[f] : target, stepFor[f]); });
        SERVER_ERROR_FIELDS.forEach(f => { if (stepFor[f]) setStepState(stepFor[f], 'error'); });
        const $target = $('#obw-section-' + target);
        if ($target.length) {
            setTimeout(() => { $('html, body').animate({ scrollTop: $target.offset().top - 100 }, 260); }, 120);
        }
    }

    // ─── Init ───────────────────────────────────────────────
    refreshCounters();
    updateScrollSpy();
    if (window.feather) window.feather.replace({ width: 14, height: 14 });
});
</script>
@endpush
