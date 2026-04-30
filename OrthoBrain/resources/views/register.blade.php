<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Doctor Registration</title>

        {{-- OrthoBrain palette (defines --ob-* CSS variables used by .reg-* classes) --}}
        <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}" />

        {{-- Vuexy theme stylesheets (kept for icon/utility resets used by ported markup) --}}
        <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/vendors.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/core.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/overrides.css') }}" />
        <link rel="stylesheet" href="{{ asset('vuexy/css/orthobrain-overrides.css') }}" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap');

            *, *::before, *::after { box-sizing: border-box; }
            html { scroll-behavior: smooth; }
            body.reg-body {
                margin: 0;
                min-height: 100vh;
                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
                color: var(--ob-text);
                /* Multi-layer gradient backdrop ported from PR #56 (login parity).
                   `background-attachment: fixed` keeps the wash stationary on scroll. */
                background:
                    radial-gradient(ellipse 1200px 600px at 80% -10%, rgba(96, 165, 250, 0.18), transparent 60%),
                    radial-gradient(ellipse 800px 600px at -5% 110%, rgba(147, 197, 253, 0.20), transparent 65%),
                    linear-gradient(135deg, #F1F5F9 0%, #E0F2FE 50%, #DBEAFE 100%);
                background-attachment: fixed;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            /* ─────────────── 3-column shell ─────────────── */
            .reg-shell-3col {
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr) 300px;
                gap: 1.25rem;
                max-width: 1480px;
                margin: 0 auto;
                padding: 1.25rem;
                align-items: start;
            }
            @media (max-width: 1180px) {
                .reg-shell-3col { grid-template-columns: 1fr; }
                .reg-brand-panel, .reg-benefits-panel { position: static !important; }
            }

            /* ─────────────── Left brand panel ─────────────── */
            .reg-brand-panel {
                position: sticky;
                top: 1.25rem;
                background: linear-gradient(160deg, #dbeafe 0%, #eff6ff 50%, #f8fafc 100%);
                border: 1px solid #dbe5f5;
                border-radius: 18px;
                padding: 1.5rem 1.25rem 1.25rem;
                min-height: 720px;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                box-shadow: 0 6px 20px -10px rgba(59,130,246,0.25);
                overflow: hidden;
            }
            .reg-brand-logo {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                font-family: 'Montserrat', sans-serif;
                font-weight: 500;
                font-size: 1.55rem;
                line-height: 1;
                margin: 0.25rem 0 0.5rem;
            }
            .reg-brand-logo .ob-mark {
                width: 32px; height: 32px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #fff; border-radius: 8px;
                box-shadow: 0 2px 6px rgba(59,130,246,0.18);
            }
            .reg-brand-logo .p1 { color: #5bc0de; }
            .reg-brand-logo .p2 { color: #8cc63f; }
            .reg-brand-logo .tm { color: #8cc63f; font-size: 0.55rem; margin-top: -10px; margin-left: 1px; }

            .reg-brand-art {
                position: relative;
                flex: 1;
                margin: 0.5rem -0.5rem 0.5rem;
                border-radius: 14px;
                background:
                    radial-gradient(140px 110px at 50% 38%, rgba(255,255,255,0.85) 0%, transparent 70%),
                    linear-gradient(165deg, #cfe1ff 0%, #e8f1ff 100%);
                overflow: hidden;
                display: flex;
                align-items: flex-end;
                justify-content: center;
            }
            .reg-brand-art::before {
                content: ""; position: absolute; inset: 0;
                background-image:
                    radial-gradient(circle at 14% 18%, rgba(255,255,255,0.55) 1.5px, transparent 2px),
                    radial-gradient(circle at 82% 22%, rgba(255,255,255,0.4) 1px, transparent 2px),
                    radial-gradient(circle at 30% 70%, rgba(255,255,255,0.35) 1px, transparent 2px),
                    radial-gradient(circle at 70% 80%, rgba(255,255,255,0.5) 1.2px, transparent 2px);
                background-size: 100% 100%;
                opacity: 0.7;
                pointer-events: none;
            }
            .reg-brand-art .reg-doctor {
                width: 100%;
                height: auto;
                max-height: 460px;
                object-fit: contain;
                object-position: bottom center;
                display: block;
                padding: 0 0.5rem;
            }

            .reg-brand-card {
                display: flex;
                gap: 0.75rem;
                align-items: flex-start;
                background: rgba(255,255,255,0.85);
                backdrop-filter: blur(4px);
                border: 1px solid #e0eaf6;
                border-radius: 12px;
                padding: 0.85rem 0.95rem;
                box-shadow: 0 2px 6px rgba(34,41,47,0.04);
            }
            .reg-brand-card .ico {
                width: 36px; height: 36px;
                flex-shrink: 0;
                display: inline-flex; align-items: center; justify-content: center;
                background: rgba(59,130,246,0.12);
                color: var(--ob-primary);
                border-radius: 10px;
                font-size: 1.05rem;
            }
            .reg-brand-card .ttl { font-weight: 600; color: var(--ob-text); font-size: 0.85rem; line-height: 1.2; margin: 0 0 0.15rem; }
            .reg-brand-card .sub { font-size: 0.78rem; color: var(--ob-text-muted); margin: 0; line-height: 1.35; }
            .reg-brand-card a { color: var(--ob-primary); text-decoration: none; font-weight: 600; font-size: 0.78rem; }
            .reg-brand-card a:hover { text-decoration: underline; }

            /* ─────────────── Center wizard panel ─────────────── */
            .reg-wizard-panel {
                background: #fff;
                border: 1px solid #e6ebf3;
                border-radius: 18px;
                padding: 1.75rem 1.75rem 1.5rem;
                box-shadow: 0 10px 30px -12px rgba(34,41,47,0.10);
                min-width: 0;
            }
            .reg-wizard-head { margin-bottom: 1.25rem; }
            .reg-wizard-head h1 { font-size: 1.5rem; font-weight: 600; color: var(--ob-text); margin: 0 0 0.25rem; letter-spacing: -0.01em; }
            .reg-wizard-head p { font-size: 0.92rem; color: var(--ob-text-muted); margin: 0; }

            /* Stepper (4 steps) */
            .reg-stepper {
                list-style: none;
                margin: 0 0 1.5rem;
                padding: 0.25rem 0 0.5rem;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 0;
                position: relative;
            }
            .reg-stepper li {
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                cursor: pointer;
                user-select: none;
                padding: 0 0.25rem;
            }
            .reg-stepper li .num {
                position: relative; z-index: 2;
                width: 38px; height: 38px;
                border-radius: 50%;
                display: inline-flex; align-items: center; justify-content: center;
                background: #fff;
                border: 1.5px solid #d8dde6;
                color: #94a0b3;
                font-weight: 600;
                font-size: 0.95rem;
                transition: all 0.2s ease;
            }
            .reg-stepper li .lbl {
                font-size: 0.78rem;
                color: #94a0b3;
                font-weight: 500;
                text-align: center;
                line-height: 1.25;
                max-width: 12rem;
                transition: color 0.2s ease;
            }
            .reg-stepper li:not(:last-child)::after {
                content: "";
                position: absolute;
                top: 19px;
                left: calc(50% + 19px);
                right: calc(-50% + 19px);
                height: 2px;
                background: #e3e7ee;
                z-index: 1;
            }
            .reg-stepper li.completed .num {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                border-color: transparent;
                color: #fff;
                box-shadow: 0 4px 10px -3px rgba(59,130,246,0.45);
            }
            .reg-stepper li.completed::after { background: linear-gradient(90deg, #3b82f6, #93c5fd); }
            .reg-stepper li.completed .lbl { color: var(--ob-text); }
            .reg-stepper li.active .num {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                border-color: transparent;
                color: #fff;
                box-shadow: 0 6px 14px -4px rgba(59,130,246,0.55), 0 0 0 4px rgba(59,130,246,0.12);
                transform: scale(1.04);
            }
            .reg-stepper li.active .lbl { color: var(--ob-primary); font-weight: 600; }
            .reg-stepper li:hover:not(.active) .num { border-color: #3b82f6; color: #3b82f6; }
            @media (max-width: 720px) {
                .reg-stepper li .lbl { font-size: 0.7rem; }
                .reg-stepper li .num { width: 32px; height: 32px; font-size: 0.85rem; }
                .reg-stepper li:not(:last-child)::after { top: 16px; left: calc(50% + 16px); right: calc(-50% + 16px); }
            }

            /* Steps */
            .reg-step { display: none; animation: regFadeIn 0.25s ease-out; }
            .reg-step.active { display: block; }
            @keyframes regFadeIn {
                from { opacity: 0; transform: translateY(6px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .reg-step-card {
                border: 1px solid #e6ebf3;
                border-radius: 14px;
                padding: 1.5rem;
                background: #fff;
            }
            .reg-step-head { margin-bottom: 1.25rem; }
            .reg-step-head h2 { font-size: 1.18rem; font-weight: 600; color: var(--ob-text); margin: 0 0 0.25rem; }
            .reg-step-head p  { font-size: 0.88rem; color: var(--ob-text-muted); margin: 0; }

            /* Form internals (preserved scoped classes) */
            .reg-grid { display: grid; grid-template-columns: 1fr; gap: 1.1rem 1.25rem; }
            @media (min-width: 720px) { .reg-grid { grid-template-columns: 1fr 1fr; } }
            .reg-col-span-2 { grid-column: span 1; }
            @media (min-width: 720px) { .reg-col-span-2 { grid-column: span 2; } }

            .reg-label { display: block; font-size: 0.85rem; font-weight: 500; color: var(--ob-text); margin-bottom: 0.4rem; }
            .reg-required { color: #ef4444; margin-left: 2px; }
            .reg-input-group {
                display: flex; align-items: center;
                border: 1px solid var(--ob-border-strong);
                border-radius: 10px;
                background: #fff;
                overflow: hidden;
                transition: border-color .18s, box-shadow .18s;
                height: 44px;
            }
            .reg-input-group:focus-within { border-color: var(--ob-primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
            .reg-input-group.is-invalid { border-color: #ef4444; }
            .reg-input-icon {
                display: flex; align-items: center; justify-content: center;
                padding: 0 0.65rem 0 0.85rem;
                color: var(--ob-text-muted);
                background: transparent;
                font-size: 1rem;
                align-self: stretch;
            }
            .reg-input {
                flex: 1; border: 0; outline: none;
                padding: 0.55rem 0.85rem 0.55rem 0.25rem;
                font-size: 0.92rem;
                color: var(--ob-text);
                background: transparent;
                min-width: 0;
                font-family: inherit;
            }
            .reg-input::placeholder { color: #9aa4b5; }

            .reg-select {
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
            .reg-select:focus { border-color: var(--ob-primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
            .reg-select.is-invalid { border-color: #ef4444; }

            .reg-phone {
                display: flex;
                border: 1px solid var(--ob-border-strong);
                border-radius: 10px;
                background: #fff;
                transition: border-color .18s, box-shadow .18s;
                overflow: hidden;
                height: 44px;
            }
            .reg-phone:focus-within { border-color: var(--ob-primary); box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
            .reg-phone.is-invalid { border-color: #ef4444; }
            .reg-phone select {
                padding: 0 0.65rem;
                background: #f8fafc;
                border: 0;
                border-right: 1px solid var(--ob-border-strong);
                color: var(--ob-text-muted);
                font-size: 0.88rem;
                outline: none;
            }
            .reg-phone input {
                flex: 1; border: 0; outline: none;
                padding: 0 0.85rem;
                font-size: 0.92rem;
                color: var(--ob-text);
                background: transparent;
                min-width: 0;
                font-family: inherit;
            }
            .reg-phone input::placeholder { color: #9aa4b5; }

            .reg-err { color: #ef4444; font-size: 0.78rem; margin: 0.3rem 0 0; }
            .reg-err.hidden { display: none; }
            .reg-step input, .reg-step select, .reg-step textarea, .reg-step button, .reg-step label { margin: 0; }

            /* Locked (read-only) state when an existing practice is picked */
            .reg-input-group.is-locked { background: #f1f5f9; }
            .reg-input-group.is-locked input { background: transparent; color: var(--ob-text-muted); }
            .reg-phone.is-locked { background: #f1f5f9; }
            .reg-phone.is-locked select, .reg-phone.is-locked input { background: transparent; pointer-events: none; }
            .reg-select.is-locked { background: #f1f5f9; pointer-events: none; }

            /* Autocomplete */
            .reg-autocomplete { position: relative; }
            .reg-autocomplete-menu {
                position: absolute; top: calc(100% + 4px); left: 0; right: 0;
                background: #fff;
                border: 1px solid var(--ob-border-strong);
                border-radius: 10px;
                box-shadow: 0 12px 28px -10px rgba(34,41,47,0.18);
                max-height: 260px; overflow-y: auto;
                z-index: 100;
                display: none;
            }
            .reg-autocomplete-menu.open { display: block; }
            .reg-autocomplete-item {
                padding: 0.55rem 0.85rem;
                cursor: pointer;
                font-size: 0.88rem;
                color: var(--ob-text);
                border-bottom: 1px solid #f3f5f9;
            }
            .reg-autocomplete-item:last-child { border-bottom: 0; }
            .reg-autocomplete-item:hover, .reg-autocomplete-item.active {
                background: #eff6ff; color: var(--ob-primary);
            }
            .reg-autocomplete-empty { padding: 0.55rem 0.85rem; font-size: 0.82rem; color: var(--ob-text-muted); font-style: italic; }
            .reg-change-link {
                display: inline-block; margin-top: 0.35rem;
                font-size: 0.78rem; color: var(--ob-primary);
                cursor: pointer; text-decoration: none;
            }
            .reg-change-link:hover { text-decoration: underline; }

            /* Section labels + option lists (additional doctor info) */
            .reg-section-label { display: block; font-size: 0.88rem; font-weight: 600; color: var(--ob-text); margin-bottom: 0.6rem; }
            .reg-option-list { display: flex; flex-direction: column; gap: 0.4rem; }
            .reg-option-grid { display: grid; grid-template-columns: 1fr; row-gap: 0.4rem; column-gap: 1rem; }
            @media (min-width: 640px) { .reg-option-grid { grid-template-columns: 1fr 1fr; } }
            .reg-option { display: flex; align-items: center; gap: 0.55rem; cursor: pointer; font-size: 0.88rem; color: var(--ob-text); }
            .reg-option.align-start { align-items: flex-start; }
            .reg-option input[type="radio"], .reg-option input[type="checkbox"] {
                accent-color: var(--ob-primary); width: 1rem; height: 1rem; min-width: 1rem; cursor: pointer; flex-shrink: 0;
            }

            .reg-form-box { background: #f8fafc; border: 1px solid #e6ebf3; border-radius: 12px; padding: 1.1rem; }
            .reg-form-box.hidden { display: none; }
            .reg-email-row { display: flex; gap: 0.5rem; }
            .reg-email-row .reg-input-group { flex: 1; }
            .reg-btn-delete {
                width: 44px; height: 44px;
                background: rgba(239,68,68,0.10); color: #ef4444;
                border: 0; border-radius: 10px;
                cursor: pointer;
                display: flex; align-items: center; justify-content: center;
                flex-shrink: 0;
                transition: background .15s;
            }
            .reg-btn-delete:hover { background: rgba(239,68,68,0.18); }
            .reg-btn-add-email {
                background: var(--ob-primary); color: #fff;
                border: 0; padding: 0.55rem 1rem; border-radius: 8px;
                font-size: 0.85rem; font-weight: 500;
                cursor: pointer;
                box-shadow: 0 4px 10px -3px rgba(59,130,246,0.4);
                transition: background .15s, transform .1s;
            }
            .reg-btn-add-email:hover { background: #2563eb; transform: translateY(-1px); }

            /* Doctor Preferences panel (now nested inside step 4) */
            .reg-pref-panel {
                background: #fcfdff;
                border: 1px solid #e6ebf3;
                border-radius: 12px;
                overflow: hidden;
                display: flex; flex-direction: column;
            }
            .reg-pref-header-bar {
                background: #fff;
                border-bottom: 1px solid #e6ebf3;
                padding: 0.85rem 1rem;
                font-weight: 700;
                color: var(--ob-text);
                font-size: 0.95rem;
            }
            .reg-pref-scroll { background: #fff; max-height: none; }
            .reg-pref-section { padding: 1.1rem 1rem 1rem; border-bottom: 1px solid #f0f3f8; }
            .reg-pref-section:last-child { border-bottom: 0; }
            .reg-pref-title { font-weight: 600; color: var(--ob-text); margin: 0 0 0.6rem; font-size: 0.92rem; }
            .reg-pref-list { display: flex; flex-direction: column; gap: 0.4rem; }
            .reg-pref-item { display: flex; align-items: center; cursor: pointer; }
            .reg-pref-item.align-start { align-items: flex-start; }
            .reg-pref-item input[type="radio"], .reg-pref-item input[type="checkbox"] {
                accent-color: var(--ob-primary); margin-right: 0.55rem;
                width: 1rem; height: 1rem; cursor: pointer; flex-shrink: 0;
            }
            .reg-pref-item.align-start input { margin-top: 0.25rem; }
            .reg-pref-item span { color: var(--ob-text); font-size: 0.88rem; line-height: 1.45; }

            /* Toggle switches */
            .reg-toggle-group { display: flex; flex-direction: column; gap: 1.1rem; }
            .reg-toggle-label { display: flex; align-items: center; cursor: pointer; }
            .reg-toggle-switch { position: relative; width: 2.5rem; height: 22px; }
            .reg-toggle-switch input.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
            .reg-toggle-bg { display: block; width: 2.5rem; height: 22px; background: #d8dde6; border-radius: 9999px; transition: background .2s; }
            .reg-toggle-dot { position: absolute; width: 1rem; height: 1rem; background: #fff; border-radius: 50%; top: 3px; right: 3px; transform: translateX(-125%); transition: transform .2s; box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
            .reg-toggle-switch input.sr-only:checked ~ .reg-toggle-bg { background: var(--ob-primary); }
            .reg-toggle-switch input.sr-only:checked ~ .reg-toggle-dot { transform: translateX(0); }
            .reg-toggle-text { margin-left: 0.75rem; font-size: 0.9rem; color: var(--ob-text); font-weight: 500; }
            .reg-toggle-panel { margin-top: 0.6rem; padding: 0.85rem; background: #f8fafc; border: 1px solid #e6ebf3; border-radius: 10px; display: flex; flex-direction: column; gap: 0.4rem; }
            .reg-toggle-panel.hidden { display: none; }

            /* Terms */
            .reg-terms-wrap { margin-top: 1.25rem; padding: 1rem 1.1rem; background: #f8fafc; border: 1px solid #e6ebf3; border-radius: 12px; display: flex; flex-direction: column; gap: 0.65rem; }
            .reg-terms-label { display: flex; align-items: flex-start; color: var(--ob-text); font-size: 0.88rem; cursor: pointer; }
            .reg-terms-label input { margin: 0.2rem 0.7rem 0 0 !important; width: 1.05rem; height: 1.05rem; accent-color: var(--ob-primary); cursor: pointer; flex-shrink: 0; }
            .reg-terms-link { color: var(--ob-primary); text-decoration: none; font-weight: 600; }
            .reg-terms-link:hover { text-decoration: underline; }

            /* Required-fields callout */
            .reg-required-note {
                margin-top: 1.1rem;
                background: #f8fafc;
                border: 1px solid #e6ebf3;
                border-radius: 10px;
                padding: 0.7rem 0.9rem;
                font-size: 0.82rem;
                color: var(--ob-text-muted);
                display: flex; align-items: center; gap: 0.55rem;
            }
            .reg-required-note i { color: var(--ob-primary); font-size: 1rem; }
            .reg-required-note .reg-required { font-size: 0.95rem; }

            /* Bottom action bar */
            .reg-actions {
                display: flex; justify-content: space-between; align-items: center;
                gap: 1rem;
                margin-top: 1.5rem;
                padding-top: 1.25rem;
                border-top: 1px solid #eef1f6;
            }
            .reg-btn-secondary {
                display: inline-flex; align-items: center; gap: 0.4rem;
                padding: 0.65rem 1.25rem;
                background: #fff;
                color: var(--ob-text);
                border: 1px solid #d8dde6;
                border-radius: 10px;
                font-weight: 500; font-size: 0.9rem;
                cursor: pointer;
                transition: background .15s, border-color .15s, color .15s;
                text-decoration: none;
                font-family: inherit;
            }
            .reg-btn-secondary:hover { background: #f8fafc; border-color: #b9c1cf; color: var(--ob-text); }
            .reg-btn-secondary:disabled { opacity: 0.5; cursor: not-allowed; }
            .reg-btn-primary-grad {
                display: inline-flex; align-items: center; gap: 0.45rem;
                padding: 0.65rem 1.5rem;
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: #fff;
                border: 0;
                border-radius: 10px;
                font-weight: 600; font-size: 0.92rem;
                cursor: pointer;
                box-shadow: 0 6px 16px -4px rgba(59,130,246,0.45);
                transition: transform .1s, box-shadow .15s, filter .15s;
                font-family: inherit;
            }
            .reg-btn-primary-grad:hover { transform: translateY(-1px); box-shadow: 0 10px 22px -6px rgba(59,130,246,0.55); filter: brightness(1.04); }
            .reg-btn-primary-grad:active { transform: translateY(0); }
            .reg-btn-primary-grad.is-submit { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
            .hidden-btn { display: none !important; }

            /* ─────────────── Right benefits panel ─────────────── */
            .reg-benefits-panel {
                position: sticky;
                top: 1.25rem;
                background: #fff;
                border: 1px solid #e6ebf3;
                border-radius: 16px;
                padding: 1.35rem 1.25rem;
                box-shadow: 0 6px 20px -10px rgba(34,41,47,0.10);
            }
            .reg-benefits-panel h3 {
                font-size: 1.05rem; font-weight: 700; color: var(--ob-text);
                margin: 0 0 1rem;
            }
            .reg-feat-list { display: flex; flex-direction: column; gap: 1rem; list-style: none; margin: 0; padding: 0; }
            .reg-feat { display: flex; align-items: flex-start; gap: 0.85rem; }
            .reg-feat-icon {
                width: 38px; height: 38px;
                flex-shrink: 0;
                display: inline-flex; align-items: center; justify-content: center;
                background: rgba(59,130,246,0.10);
                color: var(--ob-primary);
                border-radius: 10px;
                font-size: 1.05rem;
            }
            .reg-feat-body .ttl { font-size: 0.9rem; font-weight: 600; color: var(--ob-text); margin: 0 0 0.2rem; }
            .reg-feat-body .sub { font-size: 0.78rem; color: var(--ob-text-muted); margin: 0; line-height: 1.45; }

            /* Server-error banner */
            .reg-error-banner {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #991b1b;
                padding: 0.85rem 1rem;
                border-radius: 10px;
                margin-bottom: 1rem;
                font-size: 0.88rem;
            }
            .reg-error-banner ul { margin: 0.4rem 0 0 1.2rem; padding: 0; }
            .reg-error-banner strong { font-weight: 700; }

            /* Autofill — keep familiar yellow inset to flag autofilled fields */
            input:-webkit-autofill, input:-webkit-autofill:hover,
            input:-webkit-autofill:focus, input:-webkit-autofill:active {
                -webkit-box-shadow: 0 0 0 30px #fff9e6 inset !important;
                background-color: #fff9e6 !important;
            }

            /* Sub-section divider used inside steps for nested blocks */
            .reg-substep-head {
                margin: 1.5rem 0 1rem;
                padding-top: 1.25rem;
                border-top: 1px dashed #e6ebf3;
            }
            .reg-substep-head h3 { font-size: 1rem; font-weight: 600; color: var(--ob-text); margin: 0 0 0.2rem; }
            .reg-substep-head p { font-size: 0.85rem; color: var(--ob-text-muted); margin: 0; }
        </style>
        @if(config('captcha.site_key'))
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('captcha.site_key') }}"></script>
        @endif
    </head>
    <body class="reg-body">
        <div class="reg-shell-3col">

            {{-- ─────────────── LEFT: Branding ─────────────── --}}
            <aside class="reg-brand-panel">
                <div class="reg-brand-logo">
                    <span class="ob-mark">
                        <svg width="22" height="22" viewBox="0 0 64 64" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#fff"/>
                            <path d="M32 18v14M24 28c1.5 1 1.5 4 0 5.5M40 28c-1.5 1-1.5 4 0 5.5" stroke="#94a3b8"/>
                        </svg>
                    </span>
                    <span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span>
                </div>

                {{-- Doctor illustration — same asset the login page uses, for brand consistency --}}
                <div class="reg-brand-art" aria-hidden="true">
                    <img src="{{ asset('images/auth/doctor.png') }}"
                         alt="Doctor illustration"
                         class="reg-doctor"
                         loading="lazy" />
                </div>

                <div class="reg-brand-card">
                    <span class="ico"><i class="bi bi-shield-lock"></i></span>
                    <div>
                        <p class="ttl">Your information is safe with us.</p>
                        <p class="sub">We use enterprise-grade security to keep your data protected.</p>
                    </div>
                </div>

                <div class="reg-brand-card">
                    <span class="ico"><i class="bi bi-question-circle"></i></span>
                    <div>
                        <p class="ttl">Need help?</p>
                        <a href="{{ url('/contact-support') }}">Contact Support</a>
                    </div>
                </div>
            </aside>

            {{-- ─────────────── CENTER: Wizard ─────────────── --}}
            <main class="reg-wizard-panel">
                <header class="reg-wizard-head">
                    <h1>Doctor Registration</h1>
                    <p>Complete your profile to get started with Orthobrain.</p>
                </header>

                <ol class="reg-stepper" id="reg-stepper">
                    <li data-step="1" class="active" onclick="regGoToStep(1)"><span class="num">1</span><span class="lbl">Account Details</span></li>
                    <li data-step="2" onclick="regGoToStep(2)"><span class="num">2</span><span class="lbl">Practice Information</span></li>
                    <li data-step="3" onclick="regGoToStep(3)"><span class="num">3</span><span class="lbl">Address Information</span></li>
                    <li data-step="4" onclick="regGoToStep(4)"><span class="num">4</span><span class="lbl">Additional Doctor Information</span></li>
                </ol>

                {{-- Surface server-side validation errors so silent bounce-backs are impossible.
                     Each <li> carries data-server-field-key so JS can remove it as the user
                     edits the corresponding field (see dismissServerBannerFor below). --}}
                @if($errors->any())
                    <div class="reg-error-banner" id="reg-server-banner">
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

                <form id="registrationForm" action="{{ url('/register') }}" method="POST" novalidate onsubmit="return validateForm(event)">
                    @csrf
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-register" value="" />
                    @if($errors->has('captcha'))
                        <div class="reg-form-banner reg-form-banner--error" role="alert" style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:0.6rem 0.85rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem;">
                            {{ $errors->first('captcha') }}
                        </div>
                    @endif

                    @if (session('throttle_retry_at'))
                        <div id="throttle-banner"
                             role="alert"
                             data-retry-at="{{ session('throttle_retry_at') }}"
                             style="background:#FEE2E2; border:1px solid #FCA5A5; color:#991B1B; border-radius:10px; padding:14px 16px; margin-bottom:1rem; display:flex; gap:12px; align-items:flex-start;">
                            <i class="bi bi-shield-lock-fill" style="font-size:1.3rem; line-height:1.2;"></i>
                            <div style="flex:1;">
                                <div style="font-weight:600; margin-bottom:4px;">Too many attempts</div>
                                <div style="font-size:.9rem; line-height:1.45;">
                                    {{ session('throttle_message', 'You\'ve submitted too many times.') }}
                                    Try again in
                                    <span id="throttle-timer" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-weight:700; font-size:1.05rem;">--:--</span>.
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- preferred_language is fixed (no UI dropdown) — server still requires the field. --}}
                    <input type="hidden" name="preferred_language" value="English">

                    {{-- ─────── Step 1: Account Details ─────── --}}
                    <section id="step-account" class="reg-step active" data-step="1">
                        <div class="reg-step-card">
                            <div class="reg-step-head">
                                <h2>Account Details</h2>
                                <p>Create your account to get started.</p>
                            </div>
                            <div class="reg-grid">
                                <div class="reg-col-span-2">
                                    <label class="reg-label">Email (Username)<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-email">
                                        <span class="reg-input-icon"><i class="bi bi-envelope"></i></span>
                                        <input id="in-email" name="email" type="email" required title="Please enter an email address" class="reg-input" placeholder="Enter email" value="{{ old('email') }}" oninput="clearError('email')" />
                                    </div>
                                    <p id="err-email" class="reg-err hidden"></p>
                                </div>
                                <div>
                                    <label class="reg-label">First Name<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-firstName">
                                        <span class="reg-input-icon"><i class="bi bi-person"></i></span>
                                        <input id="in-firstName" name="first_name" type="text" class="reg-input" placeholder="Enter first name" value="{{ old('first_name') }}" oninput="clearError('firstName')" />
                                    </div>
                                    <p id="err-firstName" class="reg-err hidden"></p>
                                </div>
                                <div>
                                    <label class="reg-label">Last Name<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-lastName">
                                        <span class="reg-input-icon"><i class="bi bi-person"></i></span>
                                        <input id="in-lastName" name="last_name" type="text" class="reg-input" placeholder="Enter last name" value="{{ old('last_name') }}" oninput="clearError('lastName')" />
                                    </div>
                                    <p id="err-lastName" class="reg-err hidden"></p>
                                </div>
                                <div>
                                    <label class="reg-label">Password<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-password">
                                        <span class="reg-input-icon"><i class="bi bi-lock"></i></span>
                                        <input id="in-password" name="password" type="password" class="reg-input" placeholder="Enter password" oninput="clearError('password')" />
                                        <span class="reg-input-icon" style="cursor:pointer;" onclick="const p=document.getElementById('in-password');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash');">
                                            <i class="bi bi-eye-slash"></i>
                                        </span>
                                    </div>
                                    <p id="err-password" class="reg-err hidden"></p>
                                </div>
                                <div>
                                    <label class="reg-label">Confirm Password<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-confirmPassword">
                                        <span class="reg-input-icon"><i class="bi bi-lock"></i></span>
                                        <input id="in-confirmPassword" name="confirm_password" type="password" class="reg-input" placeholder="Confirm password" oninput="clearError('confirmPassword')" />
                                        <span class="reg-input-icon" style="cursor:pointer;" onclick="const p=document.getElementById('in-confirmPassword');p.type=p.type==='password'?'text':'password';this.querySelector('i').classList.toggle('bi-eye');this.querySelector('i').classList.toggle('bi-eye-slash');">
                                            <i class="bi bi-eye-slash"></i>
                                        </span>
                                    </div>
                                    <p id="err-confirmPassword" class="reg-err hidden"></p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ─────── Step 2: Practice Information (with Other Practices nested) ─────── --}}
                    <section id="step-practice" class="reg-step" data-step="2">
                        <div class="reg-step-card">
                            <div class="reg-step-head">
                                <h2>Practice Information</h2>
                                <p>Tell us about your practice.</p>
                            </div>
                            <div class="reg-grid">
                                <div>
                                    <label class="reg-label">Practice Name<span class="reg-required">*</span></label>
                                    <div class="reg-autocomplete">
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
                                <div>
                                    <label class="reg-label">Practice Phone Number<span class="reg-required">*</span></label>
                                    <div class="reg-phone" id="box-phone">
                                        <span class="reg-input-icon" style="border-right:0"><i class="bi bi-telephone"></i></span>
                                        <select name="practice_phone_country_code">
                                            @foreach($phoneCodes as $code)
                                                <option value="{{ $code }}" @selected(old('practice_phone_country_code', '+1') === $code)>{{ $code }}</option>
                                            @endforeach
                                        </select>
                                        <input id="in-phone" name="practice_phone_number" type="text" maxlength="10" placeholder="XXX-XXX-XXXX" value="{{ old('practice_phone_number') }}" oninput="clearError('phone')" />
                                    </div>
                                    <p id="err-phone" class="reg-err hidden"></p>
                                </div>
                                <div>
                                    <label class="reg-label">Practice Website<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-website">
                                        <span class="reg-input-icon"><i class="bi bi-globe"></i></span>
                                        <input id="in-website" type="text" name="practice_website" class="reg-input" placeholder="www.example.com" value="{{ old('practice_website') }}" oninput="clearError('website')" />
                                    </div>
                                    <p id="err-website" class="reg-err hidden"></p>
                                </div>
                            </div>

                            {{-- Other Practices You Work At — nested --}}
                            <div id="step-extra-practices" class="reg-substep-head">
                                <h3>Other Practices You Work At <span style="font-weight:400;font-size:0.82rem;color:var(--ob-text-muted);">(optional)</span></h3>
                                <p>Add up to 5 other practices. Pick existing ones from the search, or create new ones inline. Each is reviewed by the admin separately.</p>
                            </div>

                            <div id="extra-practice-rows"></div>

                            <div style="margin-top:0.85rem;">
                                <button type="button" id="btn-add-extra-practice" onclick="addExtraPracticeRow()"
                                        style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.1rem;background:#fff;border:1px dashed var(--ob-primary);color:var(--ob-primary);font-weight:600;border-radius:8px;cursor:pointer;transition:all 0.15s ease;font-family:inherit;"
                                        onmouseover="this.style.background='var(--ob-primary)';this.style.color='#fff';"
                                        onmouseout="this.style.background='#fff';this.style.color='var(--ob-primary)';">
                                    <i class="bi bi-plus-circle" style="font-size:1.05rem;"></i>
                                    <span>Add another practice</span>
                                </button>
                                <small style="margin-left:0.75rem;color:var(--ob-text-muted);font-size:0.78rem;">Up to 5 additional practices.</small>
                            </div>

                            @if(old('additional_practices'))
                                <script type="application/json" id="extra-old-data">@json(old('additional_practices'))</script>
                            @endif

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

                            <template id="extra-prac-phone-options">
                                @foreach($phoneCodes as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                            </template>
                        </div>
                    </section>

                    {{-- ─────── Step 3: Address Information ─────── --}}
                    <section id="step-address" class="reg-step" data-step="3">
                        <div class="reg-step-card">
                            <div class="reg-step-head">
                                <h2>Address Information</h2>
                                <p>Where is your primary practice located?</p>
                            </div>

                            <input type="hidden" name="city_id"    id="hid-city"    value="{{ old('city_id') }}">
                            <input type="hidden" name="state_id"   id="hid-state"   value="{{ old('state_id') }}">
                            <input type="hidden" name="country_id" id="hid-country" value="{{ old('country_id') }}">
                            <input type="hidden" name="primary_address_source"       id="hid-addr-source"   value="{{ old('primary_address_source') }}">
                            <input type="hidden" name="primary_address_practice_ref" id="hid-addr-prac-ref" value="{{ old('primary_address_practice_ref') }}">

                            <div style="margin-bottom:1rem;">
                                <label class="reg-label" for="addr-source-select">Address source<span class="reg-required">*</span></label>
                                <select id="addr-source-select" class="reg-select" onchange="onPrimaryAddressSourceChange(this.value)">
                                    <option value="">Select…</option>
                                </select>
                                <p id="err-addr-source" class="reg-err hidden"></p>
                                <small style="color:var(--ob-text-muted);font-size:0.78rem;">
                                    Pick one of your practices to use its address, or "Other" to enter manually.
                                </small>
                            </div>

                            <div class="reg-grid">
                                <div>
                                    <label class="reg-label">Street Address<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" id="box-address1">
                                        <span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                        <input id="in-address1" type="text" name="street_address_1" class="reg-input" placeholder="Street address 1" value="{{ old('street_address_1') }}" oninput="clearError('address1')" />
                                    </div>
                                    <p id="err-address1" class="reg-err hidden"></p>
                                </div>
                                <div>
                                    <label class="reg-label">Street Address 2</label>
                                    <div class="reg-input-group" id="box-address2">
                                        <span class="reg-input-icon"><i class="bi bi-geo-alt"></i></span>
                                        <input id="in-address2" type="text" name="street_address_2" class="reg-input" placeholder="Street address 2" value="{{ old('street_address_2') }}" />
                                    </div>
                                </div>
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
                                <div>
                                    <label class="reg-label">City<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" style="background:#f1f5f9">
                                        <input id="in-city" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                                <div>
                                    <label class="reg-label">State/Province<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" style="background:#f1f5f9">
                                        <input id="in-state" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                                <div>
                                    <label class="reg-label">Country<span class="reg-required">*</span></label>
                                    <div class="reg-input-group" style="background:#f1f5f9">
                                        <input id="in-country" type="text" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" value="" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ─────── Step 4: Additional Doctor Information (with Doctor Preferences nested) ─────── --}}
                    <section id="step-additional" class="reg-step" data-step="4">
                        <div class="reg-step-card">
                            <div class="reg-step-head">
                                <h2>Additional Doctor Information</h2>
                                <p>This information will automatically be saved to your account for all future submissions. You may edit this information at any time by visiting the My Profile tab.</p>
                            </div>

                            <div id="additional-info-body" style="display:flex;flex-direction:column;gap:1.4rem;">
                                <div>
                                    <label class="reg-section-label">Are you currently providing orthodontic services in your practice?</label>
                                    <div class="reg-option-list">
                                        <label class="reg-option"><input type="radio" name="providing_ortho" value="yes"> Yes</label>
                                        <label class="reg-option"><input type="radio" name="providing_ortho" value="no"> No</label>
                                    </div>
                                </div>

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

                                <div>
                                    <label class="reg-section-label">Preferred doctor contact information</label>
                                    <div class="reg-option-list" style="margin-bottom:1rem;">
                                        <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="doctor"> Doctor Only</label>
                                        <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="employee"> Employee/Office</label>
                                        <label class="reg-option"><input type="radio" onchange="toggleContactViews()" name="contact_preference" value="both"> Doctor and Employee/Office</label>
                                    </div>

                                    <div id="contact-forms-container" style="display:flex; flex-direction:column; gap:1rem;">
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
                                            <button type="button" onclick="addEmailRow('doctor-other-emails-list')" class="reg-btn-add-email">+ Add Other Email</button>
                                        </div>

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
                                            <button type="button" onclick="addEmailRow('employee-other-emails-list')" class="reg-btn-add-email">+ Add Other Email</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Doctor Preferences — nested under Additional Doctor Information --}}
                            <div class="reg-substep-head">
                                <h3>Doctor Preferences</h3>
                                <p>Set treatment defaults that will apply to your future cases. You can update these later in your profile.</p>
                            </div>

                            <div class="reg-pref-panel">
                                <div class="reg-pref-header-bar">Treatment defaults</div>
                                <div class="reg-pref-scroll">

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

                                    <div class="reg-pref-section">
                                        <p class="reg-pref-title">Preferred Tooth Numbering System</p>
                                        <div class="reg-pref-list">
                                            <label class="reg-pref-item"><input type="radio" name="tooth_numbering" checked><span>Universal (1-32)</span></label>
                                            <label class="reg-pref-item"><input type="radio" name="tooth_numbering"><span>FDI (11-48)</span></label>
                                            <label class="reg-pref-item"><input type="radio" name="tooth_numbering"><span>Palmer (UR1-UR8)</span></label>
                                            <label class="reg-pref-item"><input type="radio" name="tooth_numbering"><span>International (11-48)</span></label>
                                        </div>
                                    </div>

                                    <div class="reg-pref-section">
                                        <p class="reg-pref-title">Smile Arc</p>
                                        <div class="reg-pref-list">
                                            <label class="reg-pref-item"><input type="radio" name="smile_arc" checked><span>Defer to orthobrain&reg;</span></label>
                                            <label class="reg-pref-item"><input type="radio" name="smile_arc"><span>Lateral incisors .5mm shorter than central incisors</span></label>
                                            <label class="reg-pref-item"><input type="radio" name="smile_arc"><span>Lateral incisors same length as central incisors</span></label>
                                        </div>
                                    </div>

                                    <div class="reg-pref-section">
                                        <p class="reg-pref-title">Treatment of Small Lateral Incisors</p>
                                        <div class="reg-pref-list">
                                            <label class="reg-pref-item"><input type="radio" name="lateral_incisors" checked><span>Defer to orthobrain&reg;</span></label>
                                            <label class="reg-pref-item"><input type="radio" name="lateral_incisors"><span>Interproximal Reduction (IPR) on lower arch to camouflage</span></label>
                                            <label class="reg-pref-item"><input type="radio" name="lateral_incisors"><span>Leave spacing mesial and distal to maxillary laterals for future cosmetic correction</span></label>
                                        </div>
                                    </div>

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

                                    <div class="reg-pref-section">
                                        <p class="reg-pref-title">Mixed Dentition and Bite Correcting Appliances</p>
                                        <div class="reg-pref-list">
                                            <label class="reg-pref-item align-start"><input type="radio" name="mixed_dentition" checked><span>Defer to orthobrain&reg;</span></label>
                                            <label class="reg-pref-item align-start"><input type="radio" name="mixed_dentition"><span>I prefer not to use any growth and adjunctive appliances (e.g., expanders, bite planes, bit correctors, herbst, etc.) and request a proposal for a best outcome without an appliance knowing and fully understanding that this may not be an ideal Perfect Smile Plan for optimal results.</span></label>
                                        </div>
                                    </div>

                                    <div class="reg-pref-section">
                                        <p class="reg-pref-title">Orthodontic Extractions</p>
                                        <div class="reg-pref-list">
                                            <label class="reg-pref-item align-start"><input type="radio" name="ortho_extractions" checked><span>Defer to orthobrain&reg;</span></label>
                                            <label class="reg-pref-item align-start"><input type="radio" name="ortho_extractions"><span>I prefer not to extract teeth and request a proposal for a best outcome without extractions fully knowing and fully understanding that this may not be an ideal treatment plan for optimal results.</span></label>
                                        </div>
                                    </div>

                                    <div class="reg-pref-section">
                                        <p class="reg-pref-title" style="margin-bottom:1rem;">Preferences</p>
                                        <div class="reg-toggle-group">
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
                                                    <label class="reg-pref-item"><input type="radio" name="ipr_opt" checked><span>Defer to orthobrain&reg;</span></label>
                                                    <label class="reg-pref-item"><input type="radio" name="ipr_opt"><span>No IPR</span></label>
                                                    <label class="reg-pref-item"><input type="radio" name="ipr_opt"><span>Other</span></label>
                                                </div>
                                            </div>

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
                                                    <label class="reg-pref-item"><input type="radio" name="attachment_opt" checked><span>At Aligner Step 1</span></label>
                                                    <label class="reg-pref-item"><input type="radio" name="attachment_opt"><span>At Aligner Step</span></label>
                                                </div>
                                            </div>

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
                                                    <label class="reg-pref-item"><input type="radio" name="elastics_opt" checked><span>Yes</span></label>
                                                    <label class="reg-pref-item"><input type="radio" name="elastics_opt"><span>No</span></label>
                                                </div>
                                            </div>

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
                                                    <label class="reg-pref-item"><input type="radio" name="extractions_opt" checked><span>Yes</span></label>
                                                    <label class="reg-pref-item"><input type="radio" name="extractions_opt"><span>No</span></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="reg-terms-wrap">
                                <label class="reg-terms-label">
                                    <input type="checkbox" name="terms_agreed" @checked(old('terms_agreed')) onchange="clearError('terms')" />
                                    <span>By creating an account at orthobrain you accept the <a href="#" class="reg-terms-link">Terms and Conditions</a>.<span class="reg-required">*</span></span>
                                </label>
                                <p id="err-terms" class="reg-err hidden"></p>
                                <label class="reg-terms-label">
                                    <input type="checkbox" name="sms_agreed" @checked(old('sms_agreed')) />
                                    <span style="display:inline-flex; align-items:center;">I agree to receive SMS messages for authentication purposes.
                                        <i class="bi bi-info-circle" style="margin-left:0.375rem; opacity:0.7; cursor:pointer;"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </section>

                    <div class="reg-required-note">
                        <i class="bi bi-info-circle"></i>
                        <span>All fields marked with <span class="reg-required">*</span> are required.</span>
                    </div>

                    <div class="reg-actions">
                        <a href="{{ url('/login') }}" id="reg-back-login" class="reg-btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Login
                        </a>
                        <button type="button" id="reg-back-btn" class="reg-btn-secondary hidden-btn" onclick="regGoPrev()">
                            <i class="bi bi-arrow-left"></i> Back
                        </button>
                        <div style="display:flex;gap:0.6rem;align-items:center;">
                            <button type="button" id="reg-next-btn" class="reg-btn-primary-grad" onclick="regGoNext()">
                                Next <i class="bi bi-arrow-right"></i>
                            </button>
                            <button type="button" id="reg-submit-btn" class="reg-btn-primary-grad is-submit hidden-btn" onclick="validateForm()">
                                Submit for Approval <i class="bi bi-check2-circle"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </main>

            {{-- ─────────────── RIGHT: Benefits ─────────────── --}}
            <aside class="reg-benefits-panel">
                <h3>Why join Orthobrain?</h3>
                <ul class="reg-feat-list">
                    <li class="reg-feat">
                        <span class="reg-feat-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                        <div class="reg-feat-body">
                            <p class="ttl">All-in-one Platform</p>
                            <p class="sub">Everything you need to run your orthodontic practice.</p>
                        </div>
                    </li>
                    <li class="reg-feat">
                        <span class="reg-feat-icon"><i class="bi bi-cloud-check"></i></span>
                        <div class="reg-feat-body">
                            <p class="ttl">Cloud Secure</p>
                            <p class="sub">Enterprise-grade security with 99.9% uptime.</p>
                        </div>
                    </li>
                    <li class="reg-feat">
                        <span class="reg-feat-icon"><i class="bi bi-lightning-charge-fill"></i></span>
                        <div class="reg-feat-body">
                            <p class="ttl">Smart Automation</p>
                            <p class="sub">Save time with AI-powered workflows.</p>
                        </div>
                    </li>
                    <li class="reg-feat">
                        <span class="reg-feat-icon"><i class="bi bi-headset"></i></span>
                        <div class="reg-feat-body">
                            <p class="ttl">Dedicated Support</p>
                            <p class="sub">Our team is here to help you succeed.</p>
                        </div>
                    </li>
                </ul>
            </aside>

        </div>

        <script>
            // ────────────────────────────────────────────────────────────────
            //  Wizard navigation (4 steps)
            // ────────────────────────────────────────────────────────────────
            const REG_TOTAL_STEPS = 4;
            const REG_STEP_TO_ID = {
                1: 'step-account', 2: 'step-practice', 3: 'step-address', 4: 'step-additional',
            };
            const REG_ID_TO_STEP = Object.fromEntries(Object.entries(REG_STEP_TO_ID).map(([k,v]) => [v, Number(k)]));
            let regCurrentStep = 1;

            function regShowStep(n) {
                if (n < 1) n = 1;
                if (n > REG_TOTAL_STEPS) n = REG_TOTAL_STEPS;
                regCurrentStep = n;

                document.querySelectorAll('.reg-step').forEach(sec => {
                    sec.classList.toggle('active', Number(sec.dataset.step) === n);
                });
                document.querySelectorAll('.reg-stepper li').forEach(li => {
                    const s = Number(li.dataset.step);
                    li.classList.toggle('active', s === n);
                    li.classList.toggle('completed', s < n);
                });

                const backLogin = document.getElementById('reg-back-login');
                const backBtn   = document.getElementById('reg-back-btn');
                const nextBtn   = document.getElementById('reg-next-btn');
                const submitBtn = document.getElementById('reg-submit-btn');

                if (n === 1) {
                    backLogin.classList.remove('hidden-btn');
                    backBtn.classList.add('hidden-btn');
                } else {
                    backLogin.classList.add('hidden-btn');
                    backBtn.classList.remove('hidden-btn');
                }

                if (n === REG_TOTAL_STEPS) {
                    nextBtn.classList.add('hidden-btn');
                    submitBtn.classList.remove('hidden-btn');
                } else {
                    nextBtn.classList.remove('hidden-btn');
                    submitBtn.classList.add('hidden-btn');
                }

                const panel = document.querySelector('.reg-wizard-panel');
                if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function regGoToStep(n) { regShowStep(n); }
            function regGoNext() {
                // Validate this step's fields before advancing. Failures stay visible
                // inline so the user sees exactly what's missing on the current step.
                const fields = (typeof STEP_FIELDS !== 'undefined' && STEP_FIELDS[regCurrentStep]) || [];
                let stepOk = true;
                fields.forEach(id => {
                    touched.add(id);
                    if (!validateField(id)) stepOk = false;
                });
                if (!stepOk) return;
                regShowStep(regCurrentStep + 1);
            }
            function regGoPrev()    { regShowStep(regCurrentStep - 1); }

            // ─── Existing form helpers (preserved) ─────────────────────────

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
            // ────────────────────────────────────────────────────────────────
            const PRACTICE_SEARCH_URL = @json(route('practice.search'));
            let practiceSearchTimer    = null;
            let practiceSearchAbort    = null;
            let practiceSuggestions    = [];
            let practiceActiveIdx      = -1;

            function onPracticeInput(e) {
                clearError('practiceName');
                if (document.getElementById('hid-practiceId').value) {
                    document.getElementById('hid-practiceId').value = '';
                    unlockPracticeFields();
                    document.getElementById('practice-change-link').style.display = 'none';
                }
                refreshPrimaryCache();
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
                setTimeout(hidePracticeMenu, 150);
            }

            function pickPractice(idx) {
                const p = practiceSuggestions[idx];
                if (!p) return;

                document.getElementById('hid-practiceId').value = p.id;
                document.getElementById('in-practiceName').value = p.name;

                setVal('in-phone', p.phone_number);
                setVal('in-website', p.website);
                const cc = document.querySelector('select[name="practice_phone_country_code"]');
                if (cc && p.phone_country_code) cc.value = p.phone_country_code;

                // Stash the picked existing primary practice's full address into the
                // dropdown cache so Step 3 can offer it as a source. We deliberately
                // DO NOT touch Step 3 fields directly — the Step 3 dropdown is the
                // single source of truth for the doctor's primary practice address.
                practiceDataCache.primary = { mode: 'existing', label: p.name, data: p };
                rebuildPrimaryAddressDropdown();

                lockPracticeFields();
                document.getElementById('practice-change-link').style.display = 'inline-block';
                hidePracticeMenu();
            }

            function clearPracticeSelection() {
                document.getElementById('hid-practiceId').value = '';
                ['in-practiceName','in-phone','in-website'].forEach(id => setVal(id, ''));

                practiceDataCache.primary = null;
                rebuildPrimaryAddressDropdown();

                unlockPracticeFields();
                document.getElementById('practice-change-link').style.display = 'none';
                document.getElementById('in-practiceName').focus();
            }

            // Locks/unlocks only the practice-metadata fields (phone, website).
            // Step 3 address fields are NOT touched here — they're owned by the
            // Step 3 primary-address dropdown handler.
            function lockPracticeFields() {
                setReadonly('in-practiceName', false);
                setLockedGroup('box-phone',     true);
                setLockedGroup('box-website',   true);
                ['in-phone','in-website'].forEach(id => setReadonly(id, true));
            }

            function unlockPracticeFields() {
                setLockedGroup('box-phone',     false);
                setLockedGroup('box-website',   false);
                ['in-phone','in-website'].forEach(id => setReadonly(id, false));
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

            // Zip auto-fill
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

            // ── Validation ──────────────────────────────
            const emailRe    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const nameRe     = /^[A-Za-z\s\-]+$/;
            const websiteRe  = /^(https?:\/\/)?([\da-z\.\-]+)\.([a-z\.]{2,6})([\/\w \.\-]*)*\/?$/i;
            const pwSpecial  = /[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\/]/;

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
                address1: v => !v ? 'Street address is required'
                    : v.length < 5 ? 'Please enter a complete street address (min 5 characters)' : '',
                zip: v => !v ? 'Please select a zip code' : '',
            };

            const FIELD_SECTION = {
                email: 'step-account', firstName: 'step-account', lastName: 'step-account',
                password: 'step-account', confirmPassword: 'step-account',
                practiceName: 'step-practice', phone: 'step-practice',
                website: 'step-practice',
                address1: 'step-address', zip: 'step-address',
            };

            // Client field id → server error keys whose banner items should be removed
            // when this field's value becomes acceptable. Mostly 1:1; `zip` also clears
            // the city/state/country auto-filled keys (one picker drives all four).
            const CLIENT_TO_SERVER_KEYS = {
                email: ['email'], firstName: ['first_name'], lastName: ['last_name'],
                password: ['password'], confirmPassword: ['confirm_password'],
                practiceName: ['practice_name'], phone: ['practice_phone_number'],
                website: ['practice_website'],
                address1: ['street_address_1'],
                zip: ['zip_id', 'city_id', 'state_id', 'country_id'],
                terms: ['terms_agreed'],
            };

            function dismissServerBannerFor(fieldId) {
                const banner = document.getElementById('reg-server-banner');
                if (!banner) return;
                (CLIENT_TO_SERVER_KEYS[fieldId] || []).forEach(k => {
                    banner.querySelectorAll('li[data-server-field-key="' + k + '"]')
                          .forEach(li => li.remove());
                });
                if (banner.querySelectorAll('li').length === 0) banner.remove();
            }

            // Fields belonging to each wizard step — used by regGoNext() to gate advancing.
            const STEP_FIELDS = {
                1: ['email', 'firstName', 'lastName', 'password', 'confirmPassword'],
                2: ['practiceName', 'phone', 'website'],
                3: ['address1', 'zip'],
                4: [],
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
                out.password = document.getElementById('in-password')?.value ?? '';
                out.confirmPassword = document.getElementById('in-confirmPassword')?.value ?? '';
                return out;
            }

            function showError(fieldId, errorMsg) {
                const errElement = document.getElementById('err-' + fieldId);
                const boxElement = document.getElementById('box-' + fieldId);
                const inElement = document.getElementById('in-' + fieldId);
                if (errElement) { errElement.textContent = errorMsg; errElement.classList.remove('hidden'); }
                if (boxElement) boxElement.classList.add('is-invalid');
                else if (inElement) inElement.classList.add('is-invalid');
            }

            function _clearUI(fieldId) {
                const errElement = document.getElementById('err-' + fieldId);
                const boxElement = document.getElementById('box-' + fieldId);
                const inElement = document.getElementById('in-' + fieldId);
                if (errElement) errElement.classList.add('hidden');
                if (boxElement) boxElement.classList.remove('is-invalid');
                else if (inElement) inElement.classList.remove('is-invalid');
            }

            function validateField(fieldId) {
                const v = VALIDATORS[fieldId];
                if (!v) return true;
                const all = _allValues();
                const msg = v(all[fieldId], all);
                if (msg) { showError(fieldId, msg); return false; }
                _clearUI(fieldId);
                return true;
            }

            function clearError(fieldId) {
                if (touched.has(fieldId)) {
                    validateField(fieldId);
                    if (fieldId === 'password' && touched.has('confirmPassword')) {
                        validateField('confirmPassword');
                    }
                } else {
                    _clearUI(fieldId);
                }
                // If the field's current value is acceptable, drop the matching banner item(s).
                const v = VALIDATORS[fieldId];
                if (v) {
                    const all = _allValues();
                    if (!v(all[fieldId], all)) dismissServerBannerFor(fieldId);
                }
            }

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

                const terms = document.querySelector('input[name="terms_agreed"]');
                if (terms) {
                    terms.addEventListener('change', () => {
                        const err = document.getElementById('err-terms');
                        if (terms.checked && err) err.classList.add('hidden');
                    });
                }

                regShowStep(1);

                // On bounce: zip_id is preserved on the <select>, but visible city/state/country
                // readonly fields are JS-populated. Re-run the auto-fill once so they show.
                if (document.getElementById('in-zip')?.value) {
                    onRegZipChange();
                }

                // Surface server-side validation errors inline (per field), not just in the top banner.
                const SERVER_ERRORS = @json($errors->messages());
                const SERVER_FIELD_MAP = {
                    email: 'email', first_name: 'firstName', last_name: 'lastName',
                    password: 'password', confirm_password: 'confirmPassword',
                    practice_name: 'practiceName', practice_phone_number: 'phone',
                    practice_website: 'website',
                    street_address_1: 'address1', zip_id: 'zip', terms_agreed: 'terms',
                    primary_address_source: 'addr-source', primary_address_practice_ref: 'addr-source',
                };
                Object.entries(SERVER_ERRORS).forEach(([key, msgs]) => {
                    const fid = SERVER_FIELD_MAP[key];
                    if (fid && msgs && msgs.length) {
                        touched.add(fid);
                        showError(fid, msgs[0]);
                    }
                });

                @if($errors->any())
                @php
                    $errStepMap = [
                        'email' => 1, 'first_name' => 1, 'last_name' => 1, 'password' => 1, 'confirm_password' => 1,
                        'practice_name' => 2, 'practice_phone_number' => 2, 'practice_website' => 2,
                        'additional_practices' => 2,
                        'street_address_1' => 3, 'zip_id' => 3, 'city_id' => 3, 'state_id' => 3, 'country_id' => 3,
                        'primary_address_source' => 3, 'primary_address_practice_ref' => 3,
                        'contact_preference' => 4, 'modalities' => 4, 'specialties' => 4, 'providing_ortho' => 4,
                        'terms_agreed' => 4,
                    ];
                    $firstErrStep = 1;
                    foreach ($errors->keys() as $key) {
                        $bare = explode('.', $key)[0];
                        if (isset($errStepMap[$bare])) { $firstErrStep = $errStepMap[$bare]; break; }
                    }
                @endphp
                regShowStep({{ $firstErrStep }});
                @endif
            });

            // Submit handler — validate every field, jump to the first step with errors.
            function validateForm() {
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
                    const stepNum = REG_ID_TO_STEP[firstErrorSection];
                    if (stepNum) regShowStep(stepNum);
                    return false;
                }

                let extraRowsOk = true;
                document.querySelectorAll('.extra-prac-row').forEach(row => {
                    if (!epValidateRow(row.dataset.idx)) extraRowsOk = false;
                });

                if (isValid && !extraRowsOk) {
                    regShowStep(2);  // Other practices live inside Step 2
                    const firstBad = document.querySelector('.extra-prac-row .reg-err:not(.hidden)');
                    if (firstBad) {
                        setTimeout(() => firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' }), 250);
                    }
                    return false;
                }

                if (isValid) {
                    cleanExtraRows();
                    submitWithRecaptcha();
                }
                return isValid;
            }

            // Fetch a reCAPTCHA v3 token, attach it to the hidden field,
            // then submit. Falls back to plain submit when captcha isn't
            // configured or the script failed to load (offline localhost).
            function submitWithRecaptcha() {
                const form = document.getElementById('registrationForm');
                const tokenField = document.getElementById('g-recaptcha-response-register');
                const siteKey = "{{ config('captcha.site_key') }}";

                if (!siteKey || typeof grecaptcha === 'undefined' || !tokenField) {
                    form.submit();
                    return;
                }
                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, { action: 'register' })
                        .then(function (token) { tokenField.value = token; form.submit(); })
                        .catch(function () { form.submit(); });
                });
            }

            // ────────────────────────────────────────────────────────────────
            //  Other Practices (optional) — dynamic rows
            // ────────────────────────────────────────────────────────────────
            let extraPracticeSeq = 0;
            const MAX_EXTRA_PRACTICES = 5;
            const extraRowState = {};

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
                wrap.style.cssText = 'border:1px solid #e6ebf3;border-radius:12px;padding:1rem;margin-top:0.75rem;background:#f8fafc;position:relative;';
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
                                    <select name="additional_practices[${idx}][phone_country_code]" id="ep-phone-cc-${idx}"></select>
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
                                <div class="reg-input-group" style="background:#f1f5f9">
                                    <input type="text" id="ep-city-${idx}" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" readonly />
                                </div>
                            </div>
                            <div>
                                <label class="reg-label">State / Country</label>
                                <div class="reg-input-group" style="background:#f1f5f9">
                                    <input type="text" id="ep-state-${idx}" class="reg-input" style="background:transparent" placeholder="Auto-filled from zip" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('extra-practice-rows').appendChild(wrap);

                const zipSel = document.getElementById('ep-zip-' + idx);
                const tpl = document.getElementById('extra-prac-zip-options');
                if (zipSel && tpl) zipSel.innerHTML = tpl.innerHTML;

                const phoneSel = document.getElementById('ep-phone-cc-' + idx);
                const phoneTpl = document.getElementById('extra-prac-phone-options');
                if (phoneSel && phoneTpl) phoneSel.innerHTML = phoneTpl.innerHTML;

                // Wire new-mode address inputs to refresh the address-source cache
                // (so the Step 3 dropdown picks them up + live-syncs when this row
                // is the chosen source).
                const newPane = document.getElementById('ep-new-' + idx);
                if (newPane) {
                    newPane.querySelectorAll('input[name^="additional_practices"]').forEach(el => {
                        el.addEventListener('input', () => {
                            refreshExtraRowCache(idx);
                            maybeReapplySource('extra-' + idx);
                        });
                    });
                }

                renumberExtraRows();
                refreshExtraRowCache(idx);
            }

            function removeExtraRow(idx) {
                const row = document.querySelector(`.extra-prac-row[data-idx="${idx}"]`);
                if (row) row.remove();
                delete extraRowState[idx];
                delete practiceDataCache['extra-' + idx];
                renumberExtraRows();
                rebuildPrimaryAddressDropdown();
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
                refreshExtraRowCache(idx);
                maybeReapplySource('extra-' + idx);
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

            function pickExtraExistingByIndex(idx, itemIdx) {
                const items = extraSearchState[idx]?.items;
                if (!items || !items[itemIdx]) return;
                lockExtraRowAsPicked(idx, items[itemIdx]);
            }

            function lockExtraRowAsPicked(idx, p) {
                extraRowState[idx] = { mode: 'existing', picked: p };
                refreshExtraRowCache(idx);
                maybeReapplySource('extra-' + idx);

                document.getElementById('ep-id-' + idx).value = p.id;
                const pane = document.getElementById('ep-existing-' + idx);
                if (!pane) return;

                const addressLine = [p.street_address_1, p.city, p.state_code].filter(Boolean).join(', ');
                pane.innerHTML = `
                    <input type="hidden" name="additional_practices[${idx}][practice_id]" id="ep-id-${idx}" value="${p.id}">
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:0.85rem 1rem;display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <i class="bi bi-check-circle-fill" style="color:#10b981;"></i>
                                <strong style="color:var(--ob-text);">${escapeHtml(p.name)}</strong>
                                <span style="font-size:0.72rem;color:var(--ob-primary);background:#dbeafe;padding:0.15rem 0.55rem;border-radius:10rem;font-weight:600;">Existing</span>
                            </div>
                            <div style="font-size:0.78rem;color:var(--ob-text-muted);margin-top:0.4rem;display:flex;flex-wrap:wrap;gap:0.5rem 1.25rem;">
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
                refreshExtraRowCache(idx);
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
                refreshExtraRowCache(idx);
                maybeReapplySource('extra-' + idx);
            }

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
                    return !!pid;
                }
                let ok = true;
                ['name','phone_number','website','street_address_1','zip'].forEach(f => {
                    if (!epValidateField(idx, f)) ok = false;
                });
                return ok;
            }

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

            // Rebuild rows on bounce-back from server
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
                        if (row.zip_id) onExtraZipChange(idx);
                    } else if (row.practice_id) {
                        const hid = wrap.querySelector(`input[name="additional_practices[${idx}][practice_id]"]`);
                        if (hid) hid.value = row.practice_id;
                        const search = wrap.querySelector(`#ep-search-${idx}`);
                        if (search) search.placeholder = 'Previously selected — re-pick if you want to change';
                    }
                    refreshExtraRowCache(idx);
                });
                rebuildPrimaryAddressDropdown();
            });

            // ────────────────────────────────────────────────────────────────
            //  Step 3 primary-practice-address dropdown
            // ────────────────────────────────────────────────────────────────
            //
            // Doctor picks the source of Step 3's address from a dropdown listing
            // every practice they entered (existing/new) plus "Other".
            //  - Pick a practice → autofill Step 3 from its address (locked)
            //  - Pick "Other"    → Step 3 fields blank + editable
            //
            // `practiceDataCache` mirrors the form's practice slots; the dropdown
            // is rebuilt from it whenever Step 2 changes.
            const practiceDataCache = { primary: null };

            function isPrimaryNew() {
                return ! document.getElementById('hid-practiceId').value;
            }

            function readPrimaryNewLabel() {
                return (document.getElementById('in-practiceName')?.value || '').trim();
            }

            function refreshPrimaryCache() {
                const pid = document.getElementById('hid-practiceId').value;
                if (pid) {
                    // Existing primary — pickPractice already stored it in cache.
                    if (practiceDataCache.primary && practiceDataCache.primary.mode === 'existing') {
                        rebuildPrimaryAddressDropdown();
                        return;
                    }
                }
                const label = readPrimaryNewLabel();
                if (label) {
                    // New primary that has a name. The new primary's address IS Step 3
                    // (legacy controller behaviour: $data['street_address_1'] populates the
                    // new Practice). Mark `data: null` so the dropdown handler keeps Step 3
                    // editable rather than auto-filling.
                    practiceDataCache.primary = { mode: 'new', label, data: null };
                } else {
                    practiceDataCache.primary = null;
                }
                rebuildPrimaryAddressDropdown();
            }

            function refreshExtraRowCache(idx) {
                const row = document.querySelector(`.extra-prac-row[data-idx="${idx}"]`);
                if (!row) {
                    delete practiceDataCache['extra-' + idx];
                    rebuildPrimaryAddressDropdown();
                    return;
                }
                const mode = row.querySelector(`input[name="additional_practices[${idx}][mode]"]:checked`)?.value || 'existing';
                if (mode === 'existing') {
                    const state = extraRowState[idx];
                    if (state && state.picked) {
                        practiceDataCache['extra-' + idx] = {
                            mode: 'existing',
                            label: state.picked.name,
                            data: state.picked,
                        };
                    } else {
                        delete practiceDataCache['extra-' + idx];
                    }
                } else {
                    // New mode — read fields directly off the row.
                    const get = (n) => row.querySelector(`[name="additional_practices[${idx}][${n}]"]`)?.value || '';
                    const label = get('name').trim();
                    if (!label) {
                        delete practiceDataCache['extra-' + idx];
                    } else {
                        const zipSel = row.querySelector(`[name="additional_practices[${idx}][zip_id]"]`);
                        const zipOpt = zipSel?.options[zipSel.selectedIndex];
                        practiceDataCache['extra-' + idx] = {
                            mode: 'new',
                            label,
                            data: {
                                street_address_1: get('street_address_1'),
                                street_address_2: get('street_address_2'),
                                zip_id:           get('zip_id'),
                                zip_code:         zipOpt ? (zipOpt.textContent.split(' — ')[0] || '') : '',
                                city_id:          get('city_id'),
                                city:             zipOpt?.dataset?.city || '',
                                state_id:         get('state_id'),
                                state:            zipOpt?.dataset?.state || '',
                                state_code:       zipOpt?.dataset?.state || '',
                                country_id:       get('country_id'),
                                country:          zipOpt?.dataset?.country || '',
                            },
                        };
                    }
                }
                rebuildPrimaryAddressDropdown();
            }

            function rebuildPrimaryAddressDropdown() {
                const sel = document.getElementById('addr-source-select');
                if (!sel) return;
                // Determine the currently-active selection: visible dropdown value
                // takes precedence; fall back to hidden inputs (covers initial load).
                let prevValue = sel.value;
                if (!prevValue) {
                    const oldSrc = document.getElementById('hid-addr-source').value;
                    if (oldSrc === 'OTHER') prevValue = 'OTHER';
                    else if (oldSrc === 'PRACTICE') prevValue = document.getElementById('hid-addr-prac-ref').value;
                }

                const opts = ['<option value="">Select…</option>'];
                Object.keys(practiceDataCache).forEach(key => {
                    const entry = practiceDataCache[key];
                    if (!entry || !entry.label) return;
                    const tag = entry.mode === 'existing' ? 'existing' : 'new';
                    opts.push(`<option value="${escapeHtml(key)}">${escapeHtml(entry.label)} (${tag})</option>`);
                });
                opts.push('<option value="OTHER">Other (enter manually)</option>');
                sel.innerHTML = opts.join('');

                if (prevValue && Array.from(sel.options).some(o => o.value === prevValue)) {
                    sel.value = prevValue;
                } else {
                    sel.value = '';
                    document.getElementById('hid-addr-source').value = '';
                    document.getElementById('hid-addr-prac-ref').value = '';
                    clearStep3Address();
                    unlockStep3Address();
                }
            }

            function onPrimaryAddressSourceChange(value) {
                const srcEl = document.getElementById('hid-addr-source');
                const refEl = document.getElementById('hid-addr-prac-ref');
                clearError('addr-source');

                if (!value) {
                    srcEl.value = '';
                    refEl.value = '';
                    clearStep3Address();
                    unlockStep3Address();
                    return;
                }

                if (value === 'OTHER') {
                    srcEl.value = 'OTHER';
                    refEl.value = '';
                    clearStep3Address();
                    unlockStep3Address();
                    document.getElementById('in-address1')?.focus();
                    return;
                }

                srcEl.value = 'PRACTICE';
                refEl.value = value;

                const entry = practiceDataCache[value];
                if (!entry || !entry.data) {
                    // Primary-new — no address data yet; Step 3 IS this practice's
                    // address. Keep current Step 3 values, leave editable.
                    unlockStep3Address();
                    return;
                }
                fillStep3FromData(entry.data);
                lockStep3Address();
            }

            function fillStep3FromData(p) {
                setVal('in-address1', p.street_address_1);
                setVal('in-address2', p.street_address_2);

                const zipSel = document.getElementById('in-zip');
                if (zipSel && p.zip_id) {
                    let found = Array.from(zipSel.options).find(o => o.value == p.zip_id);
                    if (!found) {
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
            }

            function clearStep3Address() {
                ['in-address1','in-address2','in-city','in-state','in-country','hid-city','hid-state','hid-country']
                    .forEach(id => setVal(id, ''));
                const zipSel = document.getElementById('in-zip');
                if (zipSel) zipSel.selectedIndex = 0;
            }

            function lockStep3Address() {
                setLockedGroup('box-address1', true);
                setLockedGroup('box-address2', true);
                setReadonly('in-address1', true);
                setReadonly('in-address2', true);
                const zipSel = document.getElementById('in-zip');
                if (zipSel) {
                    zipSel.classList.add('is-locked');
                    zipSel.style.pointerEvents = 'none';
                }
            }

            function unlockStep3Address() {
                setLockedGroup('box-address1', false);
                setLockedGroup('box-address2', false);
                setReadonly('in-address1', false);
                setReadonly('in-address2', false);
                const zipSel = document.getElementById('in-zip');
                if (zipSel) {
                    zipSel.classList.remove('is-locked');
                    zipSel.style.pointerEvents = '';
                }
            }

            // Live re-sync: if the dropdown's source is `extra-N` (or `primary` in
            // existing mode) and that source's data changes, re-apply the selection
            // so Step 3 reflects the latest values.
            function maybeReapplySource(forKey) {
                const ref = document.getElementById('hid-addr-prac-ref').value;
                if (ref && ref === forKey) {
                    onPrimaryAddressSourceChange(ref);
                }
            }

            // Bounce-back rehydration: after server-side validation failure, rebuild
            // dropdown from current cache state and re-apply the saved selection.
            document.addEventListener('DOMContentLoaded', () => {
                refreshPrimaryCache();
                document.querySelectorAll('.extra-prac-row').forEach(r => refreshExtraRowCache(r.dataset.idx));

                const oldSource = document.getElementById('hid-addr-source').value;
                const oldRef    = document.getElementById('hid-addr-prac-ref').value;
                if (oldSource === 'OTHER') {
                    const sel = document.getElementById('addr-source-select');
                    if (sel) sel.value = 'OTHER';
                    onPrimaryAddressSourceChange('OTHER');
                } else if (oldSource === 'PRACTICE' && oldRef) {
                    const sel = document.getElementById('addr-source-select');
                    if (sel && Array.from(sel.options).some(o => o.value === oldRef)) {
                        sel.value = oldRef;
                        onPrimaryAddressSourceChange(oldRef);
                    }
                }
            });
        </script>

        <script>
            // Throttle countdown for /register. The global exception
            // handler in bootstrap/app.php flashes `throttle_retry_at`
            // (ISO 8601) into session whenever ThrottleRequestsException
            // fires. We render a banner with that timestamp and tick
            // down locally against the user's clock until the retry
            // instant. While the banner is present, the registration
            // form is disabled; when the timer hits 0 we hide the
            // banner and re-enable inputs in place — no page reload.
            (function () {
                const banner = document.getElementById('throttle-banner');
                if (!banner) return;

                const retryAt = Date.parse(banner.dataset.retryAt);
                if (!Number.isFinite(retryAt)) return;

                const form    = document.getElementById('registrationForm');
                const timerEl = document.getElementById('throttle-timer');
                const fields  = form ? form.querySelectorAll('input, button, select, textarea') : [];

                fields.forEach(el => { el.disabled = true; });
                if (form) form.setAttribute('aria-busy', 'true');

                const fmt = (ms) => {
                    const total = Math.max(0, Math.floor(ms / 1000));
                    const m = String(Math.floor(total / 60)).padStart(2, '0');
                    const s = String(total % 60).padStart(2, '0');
                    return `${m}:${s}`;
                };

                const tick = () => {
                    const remaining = retryAt - Date.now();
                    if (remaining <= 0) {
                        clearInterval(interval);
                        banner.style.display = 'none';
                        fields.forEach(el => { el.disabled = false; });
                        if (form) form.removeAttribute('aria-busy');
                        const first = document.getElementById('in-email');
                        if (first) first.focus();
                        return;
                    }
                    timerEl.textContent = fmt(remaining);
                };

                tick();
                const interval = setInterval(tick, 1000);
            })();
        </script>
    </body>
</html>
