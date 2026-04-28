@extends('layouts.admin')
@section('title', 'Doctors')
@php
    $doctorsPageTitle = request('status')
        ? ucfirst(strtolower(request('status'))) . ' Doctors'
        : 'All Doctors';
@endphp
@section('page_title', $doctorsPageTitle)

@push('styles')
<style>
    /* ──────────────────────────────────────────────────────────
       Doctors listing — page-local tokens only.
       Shared brand tokens (--ob-primary, --ob-border, --ob-text,
       --ob-surface-1/2, --ob-success/warning/danger) come from
       orthobrain-palette.css and swap with light/dark.
       ────────────────────────────────────────────────────────── */
    #doctors-page {
        --ob-primary-softer: rgba(59, 130, 246, 0.07);
        --ob-accent: #8cc63f;
        --ob-accent-soft: rgba(140, 198, 63, 0.14);
        --ob-warning-soft: rgba(255, 159, 67, 0.12);
        --ob-danger-soft: rgba(234, 84, 85, 0.12);
        --ob-muted: var(--ob-text-muted);
        --ob-surface-alt: var(--ob-surface-2);
        --ob-shadow-sm: 0 1px 2px rgba(24, 28, 40, 0.04);
        --ob-shadow-md: 0 4px 20px rgba(24, 28, 40, 0.06);
    }

    /* ──────────────────────────────────────────────────────────
       Main listing card
       ────────────────────────────────────────────────────────── */
    .ob-list-card {
        background: var(--ob-surface-1);
        border: 1px solid var(--ob-border);
        border-radius: 0.85rem;
        box-shadow: var(--ob-shadow-sm);
        overflow: hidden;
    }

    /* Card header: title + CTA cluster */
    .ob-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        flex-wrap: wrap;
    }
    .ob-card-head-title { font-size: 1.05rem; font-weight: 700; color: var(--ob-text); margin: 0; }
    .ob-card-head-cta { display: inline-flex; align-items: center; gap: 0.625rem; flex-wrap: wrap; }

    .ob-btn-primary {
        background: var(--ob-primary);
        border-color: var(--ob-primary);
        color: #fff;
        font-weight: 600;
        padding: 0.55rem 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(91, 192, 222, 0.28);
        transition: transform 120ms ease, box-shadow 120ms ease, background 120ms ease, border-color 120ms ease;
        display: inline-flex; align-items: center; gap: 0.4rem;
    }
    .ob-btn-primary:hover,
    .ob-btn-primary:focus {
        background: var(--ob-primary-hover);
        border-color: var(--ob-primary-hover);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(91, 192, 222, 0.35);
    }
    .ob-btn-primary svg { width: 14px; height: 14px; }

    /* Pending-review pill */
    .ob-alert-pending {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--ob-warning-soft);
        color: #b76a00;
        border: 1px solid rgba(255, 159, 67, 0.3);
        padding: 0.45rem 0.8rem;
        border-radius: 10rem;
        font-weight: 600;
        font-size: 0.8rem;
        text-decoration: none;
        transition: background 140ms ease, transform 120ms ease;
    }
    .ob-alert-pending:hover { background: rgba(255, 159, 67, 0.22); color: #b76a00; transform: translateY(-1px); }
    .ob-alert-pending svg { width: 14px; height: 14px; }

    /* ──────────────────────────────────────────────────────────
       Tabs (segmented)
       ────────────────────────────────────────────────────────── */
    .ob-tabs {
        display: flex; flex-wrap: wrap;
        padding: 0.85rem 1.25rem 0;
        gap: 0.35rem;
        border-bottom: 1px solid var(--ob-border);
        list-style: none;
        margin: 0;
    }
    .ob-tabs .ob-tab {
        display: inline-flex; align-items: center; gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        color: var(--ob-muted);
        font-weight: 600;
        font-size: 0.87rem;
        border-radius: 0.5rem 0.5rem 0 0;
        border: 0;
        background: transparent;
        text-decoration: none;
        position: relative;
        transition: color 140ms ease, background 140ms ease;
    }
    .ob-tabs .ob-tab:hover { color: var(--ob-primary); background: var(--ob-primary-softer); }
    .ob-tabs .ob-tab.is-active { color: var(--ob-primary); background: var(--ob-primary-softer); }
    .ob-tabs .ob-tab.is-active::after {
        content: '';
        position: absolute;
        left: 0.6rem; right: 0.6rem; bottom: -1px; height: 2px;
        background: var(--ob-primary);
        border-radius: 2px 2px 0 0;
    }
    .ob-tabs .ob-tab-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 1.35rem; height: 1.35rem;
        padding: 0 0.45rem;
        font-size: 0.7rem;
        font-weight: 700;
        background: var(--ob-surface-2);
        color: var(--ob-text-muted);
        border-radius: 999px;
    }
    .ob-tabs .ob-tab.is-active .ob-tab-count { background: var(--ob-primary-soft); color: var(--ob-primary); }

    /* ──────────────────────────────────────────────────────────
       Filter toolbar
       ────────────────────────────────────────────────────────── */
    .ob-toolbar {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        background: var(--ob-surface-alt);
    }
    .ob-input-icon { position: relative; }
    .ob-input-icon > svg {
        position: absolute;
        left: 0.75rem; top: 50%; transform: translateY(-50%);
        width: 16px; height: 16px;
        color: #9a9aab;
        pointer-events: none;
    }
    .ob-input-icon .form-control {
        padding-left: 2.35rem;
    }
    .ob-toolbar .form-control, .ob-toolbar .form-select {
        border-radius: 0.5rem;
    }
    .ob-toolbar .form-control:focus, .ob-toolbar .form-select:focus {
        border-color: var(--ob-primary);
        box-shadow: 0 0 0 3px var(--ob-primary-softer);
    }
    /* .ob-btn-clear is defined globally in vuexy/css/orthobrain-overrides.css */

    /* Active-filter chips row */
    .ob-active-filters {
        display: flex; flex-wrap: wrap; gap: 0.4rem;
        margin-top: 0.65rem;
    }
    .ob-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.25rem 0.55rem 0.25rem 0.7rem;
        background: var(--ob-surface-1);
        border: 1px solid var(--ob-border);
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--ob-text);
    }
    .ob-chip .ob-chip-label { color: var(--ob-text-muted); font-weight: 500; margin-right: 0.15rem; }
    .ob-chip a {
        display: inline-flex; align-items: center; justify-content: center;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: var(--ob-surface-2);
        color: var(--ob-text-muted);
        transition: background 120ms ease, color 120ms ease;
    }
    .ob-chip a:hover { background: var(--ob-danger); color: #fff; }
    .ob-chip a svg { width: 10px; height: 10px; }

    /* Table chrome (.ob-admin-table), status pills (.ob-status), icon
       buttons (.ob-icon-btn), and row actions (.ob-row-actions) live in
       orthobrain-palette.css so they're shared across admin index pages. */

    /* Doctor cell */
    .ob-doctor-cell { display: flex; align-items: center; gap: 0.75rem; min-width: 220px; }
    .ob-doctor-cell .ob-doctor-name {
        font-weight: 700; color: var(--ob-text); line-height: 1.2;
        display: block;
    }
    .ob-doctor-cell .ob-doctor-sub {
        color: var(--ob-muted); font-size: 0.76rem; font-weight: 500;
        display: block; margin-top: 0.1rem;
    }
    .ob-avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e3f4fa 0%, #c9ebf5 100%);
        color: var(--ob-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex: 0 0 auto;
        overflow: hidden;
        box-shadow: 0 0 0 2px var(--ob-surface-1), 0 0 0 3px var(--ob-border);
    }
    .ob-avatar img { width: 100%; height: 100%; object-fit: cover; }

    /* Contact cell: icon-prefixed lines */
    .ob-contact-line {
        display: inline-flex; align-items: center; gap: 0.4rem;
        color: var(--ob-text);
    }
    .ob-contact-line + .ob-contact-line { margin-top: 0.2rem; }
    .ob-contact-line svg { width: 13px; height: 13px; color: #9a9aab; flex: 0 0 auto; }
    .ob-contact-line.is-sub { color: var(--ob-muted); font-size: 0.76rem; }

    /* Practice cell */
    .ob-practice { color: var(--ob-text); font-weight: 600; }

    /* Registered cell */
    .ob-when { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--ob-text); }
    .ob-when svg { width: 13px; height: 13px; color: var(--ob-text-muted); }

    /* Empty state */
    .ob-empty { padding: 3rem 1.5rem; text-align: center; }
    .ob-empty-icon {
        width: 64px; height: 64px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: var(--ob-primary-softer);
        color: var(--ob-primary);
        display: inline-flex; align-items: center; justify-content: center;
    }
    .ob-empty-icon svg { width: 26px; height: 26px; }
    .ob-empty-title { font-weight: 700; color: var(--ob-text); margin-bottom: 0.25rem; }
    .ob-empty-sub { color: var(--ob-muted); font-size: 0.86rem; }
    .ob-empty-sub a { color: var(--ob-primary); font-weight: 600; text-decoration: none; }
    .ob-empty-sub a:hover { text-decoration: underline; }

    /* Pagination footer */
    .ob-foot {
        padding: 0.85rem 1.25rem;
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap;
        border-top: 1px solid var(--ob-border);
        background: var(--ob-surface-alt);
    }
    .ob-foot .ob-foot-meta { color: var(--ob-muted); font-size: 0.8rem; font-weight: 500; }
    .ob-foot .pagination { margin: 0; }

    .ob-list-card .table-responsive { overflow-x: auto; }
</style>
@endpush

@section('content')
@php
    $tabs = [
        ['key' => null,         'label' => 'All',       'tone' => null],
        ['key' => 'PENDING',    'label' => 'Pending',   'tone' => 'warning'],
        ['key' => 'APPROVED',   'label' => 'Approved',  'tone' => 'success'],
        ['key' => 'REJECTED',   'label' => 'Rejected',  'tone' => 'danger'],
        ['key' => 'SUSPENDED',  'label' => 'Suspended', 'tone' => 'secondary'],
    ];
    $tabCount   = fn ($key) => $key === null ? ($totalCount ?? 0) : (int) ($statusCounts[$key] ?? 0);
    $searchTerm = trim((string) request('search', ''));
@endphp

<section id="doctors-page">
    <div class="ob-list-card">

        {{-- Tabs --}}
        <ul class="ob-tabs">
            @foreach ($tabs as $tab)
                @php
                    $isActive = ($currentStatus ?? null) === $tab['key'];
                    $href = $tab['key']
                        ? route('admin.doctors.index', ['status' => $tab['key']])
                        : route('admin.doctors.index');
                @endphp
                <li>
                    <a href="{{ $href }}" class="ob-tab {{ $isActive ? 'is-active' : '' }}">
                        {{ $tab['label'] }}
                        <span class="ob-tab-count">{{ $tabCount($tab['key']) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Toolbar --}}
        <div class="ob-toolbar">
            <form id="doctorsFilter" method="GET" class="row g-2 align-items-center">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="col-md-4">
                    <select name="practice_id" class="js-searchable form-select">
                        <option value="">All practices</option>
                        @foreach ($practices as $p)
                            <option value="{{ $p->id }}" @selected(request('practice_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="ob-input-icon">
                        <i data-feather="search"></i>
                        <input type="text" name="search" placeholder="Search by name, email, or phone"
                               value="{{ $searchTerm }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.doctors.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary w-100">
                        <i data-feather="plus" class="me-25"></i> Add Doctor
                    </a>
                </div>
            </form>
        </div>

        {{-- Results pane: chips + table + pagination. Swapped on AJAX so
             search/filter feels instant and the input keeps focus. --}}
        <div id="doctors-pane">
            @include('admin.doctors._results')
        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    'use strict';

    // Live filter without full-page reload: type into search / change practice
    // → fetch the rendered _results partial and swap #doctors-pane innerHTML.
    // Cursor stays in the input, no flicker, URL stays in sync via
    // history.replaceState. Falls back to a regular form GET if JS is off.

    const $form    = $('#doctorsFilter');
    const $input   = $form.find('input[name="search"]');
    const $practice = $form.find('select[name="practice_id"]');
    const baseUrl  = @json(route('admin.doctors.index'));
    let inflight  = null;
    let textTimer;

    function buildQuery() {
        const params = new URLSearchParams();
        const status = $form.find('input[name="status"]').val();
        const search = ($input.val() || '').trim();
        const practice = $practice.val();
        const current = new URLSearchParams(window.location.search);
        const sort = current.get('sort');
        const dir  = current.get('dir');
        if (status)   params.set('status', status);
        if (search)   params.set('search', search);
        if (practice) params.set('practice_id', practice);
        if (sort)     params.set('sort', sort);
        if (dir)      params.set('dir', dir);
        return params;
    }

    function reload() {
        if (inflight) inflight.abort();
        const params = buildQuery();
        const qs = params.toString();
        const url = qs ? (baseUrl + '?' + qs) : baseUrl;

        inflight = $.ajax({
            url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            dataType: 'html',
        })
        .done(function (html) {
            $('#doctors-pane').html(html);
            if (window.feather) window.feather.replace({ width: 14, height: 14 });
            window.history.replaceState({}, '', url);
        })
        .fail(function (xhr) {
            if (xhr.statusText !== 'abort') {
                console.error('Doctors search failed:', xhr.status, xhr.responseText);
            }
        });
    }

    $input.on('input', function () {
        clearTimeout(textTimer);
        textTimer = setTimeout(reload, 250);
    });
    $practice.on('change', reload);

    // Tab clicks (status filter) and pagination links inside the swapped pane
    // remain plain anchors → full-page navigation. That keeps the URL canonical
    // and the back button working as expected.
})();
</script>
@endpush
@endsection
