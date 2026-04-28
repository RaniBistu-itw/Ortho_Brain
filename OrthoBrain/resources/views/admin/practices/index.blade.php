@extends('layouts.admin')
@section('title', 'Practices')
@section('page_title', 'Practices')
@php
    $practicesTableTitle = request('status')
        ? ucfirst(strtolower(request('status'))) . ' Practices'
        : 'All Practices';
@endphp

@push('styles')
<style>
    /* ──────────────────────────────────────────────────────────
       Practices listing — aligned with orthobrain brand palette
       (cyan #5bc0de primary, green #8cc63f accent)
       ────────────────────────────────────────────────────────── */
    #practices-page {
        --ob-primary: #5bc0de;
        --ob-primary-hover: #3fb1d4;
        --ob-primary-soft: rgba(91, 192, 222, 0.14);
        --ob-primary-softer: rgba(91, 192, 222, 0.07);
        --ob-accent: #8cc63f;
        --ob-accent-soft: rgba(140, 198, 63, 0.14);
        --ob-warning: #ff9f43;
        --ob-warning-soft: rgba(255, 159, 67, 0.12);
        --ob-danger: #ea5455;
        --ob-danger-soft: rgba(234, 84, 85, 0.12);
        --ob-muted: #6e6b7b;
        --ob-border: #ebe9f1;
        --ob-surface: #ffffff;
        --ob-surface-alt: #f8f8fb;
        --ob-text: #1f1f1f;
        --ob-shadow-sm: 0 1px 2px rgba(24, 28, 40, 0.04);
        --ob-shadow-md: 0 4px 20px rgba(24, 28, 40, 0.06);
    }

    /* KPI strip — mirrors the masters / product-categories layout */
    .pc-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767.98px) { .pc-kpis { grid-template-columns: 1fr; } }
    .pc-kpi { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: .6rem;
              background: #fff; box-shadow: 0 2px 8px rgba(34, 41, 47, .05); border: 1px solid rgba(34, 41, 47, .05); }
    .pc-kpi__icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; }
    .pc-kpi__icon svg { width: 20px; height: 20px; }
    .pc-kpi__icon--total    { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
    .pc-kpi__icon--active   { background: rgba(var(--bs-success-rgb), .12); color: var(--bs-success); }
    .pc-kpi__icon--inactive { background: rgba(var(--bs-danger-rgb), .12);  color: var(--bs-danger); }
    .pc-kpi__label { font-size: .78rem; color: #6e6b7b; text-transform: uppercase; letter-spacing: .04em; }
    .pc-kpi__value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; color: #5e5873; }

    .ob-list-card {
        background: var(--ob-surface);
        border: 1px solid var(--ob-border);
        border-radius: 0.85rem;
        box-shadow: var(--ob-shadow-sm);
        overflow: hidden;
    }

    .ob-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        flex-wrap: wrap;
    }
    .ob-card-head-title { font-size: 1.05rem; font-weight: 700; color: #111; margin: 0; }
    .ob-card-head-meta { color: var(--ob-muted); font-size: 0.82rem; font-weight: 500; }

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
        background: #f3f2f7;
        color: var(--ob-muted);
        border-radius: 999px;
    }
    .ob-tabs .ob-tab.is-active .ob-tab-count { background: var(--ob-primary-soft); color: var(--ob-primary); }

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
        background: #fff;
    }
    .ob-toolbar .form-control, .ob-toolbar .form-select {
        border-radius: 0.5rem;
        border-color: #e2e0ea;
    }
    .ob-toolbar .form-control:focus, .ob-toolbar .form-select:focus {
        border-color: var(--ob-primary);
        box-shadow: 0 0 0 3px var(--ob-primary-softer);
    }
    /* .ob-btn-clear is defined globally in vuexy/css/orthobrain-overrides.css */

    .ob-active-filters {
        display: flex; flex-wrap: wrap; gap: 0.4rem;
        margin-top: 0.65rem;
    }
    .ob-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.25rem 0.55rem 0.25rem 0.7rem;
        background: #fff;
        border: 1px solid #e2e0ea;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--ob-text);
    }
    .ob-chip .ob-chip-label { color: var(--ob-muted); font-weight: 500; margin-right: 0.15rem; }
    .ob-chip a {
        display: inline-flex; align-items: center; justify-content: center;
        width: 16px; height: 16px;
        border-radius: 50%;
        background: #f3f2f7;
        color: var(--ob-muted);
        transition: background 120ms ease, color 120ms ease;
    }
    .ob-chip a:hover { background: var(--ob-danger); color: #fff; }
    .ob-chip a svg { width: 10px; height: 10px; }

    .ob-table { width: 100%; margin: 0; }
    .ob-table thead th {
        background: var(--ob-surface-alt);
        color: #555668;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.85rem 1.25rem;
        border-top: 0;
        border-bottom: 1px solid var(--ob-border);
        white-space: nowrap;
    }
    .ob-table tbody td {
        padding: 0.9rem 1.25rem;
        border-top: 1px solid #f3f2f7;
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .ob-table tbody tr { transition: background 120ms ease; }
    .ob-table tbody tr:hover { background: var(--ob-primary-softer); }

    .ob-practice-cell { display: flex; align-items: center; gap: 0.75rem; min-width: 240px; }
    .ob-practice-cell .ob-practice-name {
        font-weight: 700; color: #111; line-height: 1.2;
        display: block;
    }
    .ob-practice-cell .ob-practice-sub {
        color: var(--ob-muted); font-size: 0.76rem; font-weight: 500;
        display: block; margin-top: 0.1rem;
    }
    .ob-logo {
        width: 40px; height: 40px;
        border-radius: 0.5rem;
        background: linear-gradient(135deg, #e3f4fa 0%, #c9ebf5 100%);
        color: var(--ob-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex: 0 0 auto;
        overflow: hidden;
        box-shadow: 0 0 0 2px #fff, 0 0 0 3px #ebe9f1;
    }
    .ob-logo img { width: 100%; height: 100%; object-fit: cover; }

    .ob-contact-line {
        display: inline-flex; align-items: center; gap: 0.4rem;
        color: var(--ob-text);
    }
    .ob-contact-line + .ob-contact-line { margin-top: 0.2rem; }
    .ob-contact-line svg { width: 13px; height: 13px; color: #9a9aab; flex: 0 0 auto; }
    .ob-contact-line.is-sub { color: var(--ob-muted); font-size: 0.76rem; }
    .ob-contact-line a { color: var(--ob-text); text-decoration: none; }
    .ob-contact-line a:hover { color: var(--ob-primary); text-decoration: underline; }

    .ob-owner { color: var(--ob-text); font-weight: 600; }
    .ob-location { color: var(--ob-text); }
    .ob-location .ob-location-sub { display: block; color: var(--ob-muted); font-size: 0.76rem; margin-top: 0.1rem; }

    .ob-members-pill {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.2rem 0.55rem;
        background: var(--ob-primary-softer);
        color: var(--ob-primary);
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .ob-members-pill svg { width: 12px; height: 12px; }

    .ob-status {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }
    .ob-status::before {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }
    .ob-status--success   { background: var(--ob-accent-soft); color: #5a8f21; border-color: rgba(140, 198, 63, 0.28); }
    .ob-status--secondary { background: #eef0f4; color: #6c7283; border-color: #e2e4eb; }

    .ob-pending-pill {
        display: inline-block;
        margin-left: 0.45rem;
        padding: 0.15rem 0.55rem;
        background: var(--ob-warning-soft);
        color: #b9681a;
        border: 1px solid #ffdcaf;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        text-decoration: none;
        vertical-align: middle;
    }
    .ob-pending-pill:hover { background: rgba(255, 159, 67, 0.22); color: #9c560e; }

    .ob-when { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--ob-text); }
    .ob-when svg { width: 13px; height: 13px; color: #9a9aab; }

    .ob-row-actions { display: inline-flex; align-items: center; gap: 0.3rem; justify-content: flex-end; }
    .ob-icon-btn {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 0.5rem;
        border: 1px solid var(--ob-border);
        background: #fff;
        color: var(--ob-muted);
        transition: color 120ms ease, border-color 120ms ease, background 120ms ease, transform 120ms ease;
    }
    .ob-icon-btn:hover {
        color: var(--ob-primary);
        border-color: var(--ob-primary);
        background: var(--ob-primary-softer);
        transform: translateY(-1px);
    }
    .ob-icon-btn svg { width: 15px; height: 15px; }

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
    .ob-empty-title { font-weight: 700; color: #111; margin-bottom: 0.25rem; }
    .ob-empty-sub { color: var(--ob-muted); font-size: 0.86rem; }
    .ob-empty-sub a { color: var(--ob-primary); font-weight: 600; text-decoration: none; }
    .ob-empty-sub a:hover { text-decoration: underline; }

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
    $selectedCountry = request('country_id')
        ? optional($countries->firstWhere('id', (int) request('country_id')))->name
        : null;
    $searchTerm = trim((string) request('search', ''));
    $hasActiveFilters = $selectedCountry || $searchTerm !== '';

    $queryWithout = function (array $remove) {
        $q = request()->query();
        foreach ($remove as $k) { unset($q[$k]); }
        return route('admin.practices.index', $q);
    };
@endphp

<section id="practices-page">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    @php
        $activeCount   = (int) ($statusCounts['ACTIVE']   ?? 0);
        $inactiveCount = (int) ($statusCounts['INACTIVE'] ?? 0);
    @endphp
    <div class="pc-kpis">
        <div class="pc-kpi">
            <div class="pc-kpi__icon pc-kpi__icon--total"><i data-feather="briefcase"></i></div>
            <div>
                <div class="pc-kpi__label">Total</div>
                <div class="pc-kpi__value">{{ $totalCount ?? 0 }}</div>
            </div>
        </div>
        <div class="pc-kpi">
            <div class="pc-kpi__icon pc-kpi__icon--active"><i data-feather="check-circle"></i></div>
            <div>
                <div class="pc-kpi__label">Active</div>
                <div class="pc-kpi__value">{{ $activeCount }}</div>
            </div>
        </div>
        <div class="pc-kpi">
            <div class="pc-kpi__icon pc-kpi__icon--inactive"><i data-feather="slash"></i></div>
            <div>
                <div class="pc-kpi__label">Inactive</div>
                <div class="pc-kpi__value">{{ $inactiveCount }}</div>
            </div>
        </div>
    </div>

    <div class="ob-list-card">
        {{-- Card head: dynamic title --}}
        <div class="ob-card-head">
            <h4 class="card-title mb-0">{{ $practicesTableTitle }}</h4>
        </div>

        {{-- Toolbar --}}
        <div class="ob-toolbar">
            <form id="practicesFilter" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status') === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="country_id" class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}" @selected(request('country_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="ob-input-icon">
                        <i data-feather="search"></i>
                        <input type="text" name="search" placeholder="Search by name, website, or phone"
                               value="{{ $searchTerm }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.practices.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>

            @if ($hasActiveFilters)
                <div class="ob-active-filters">
                    @if ($searchTerm !== '')
                        <span class="ob-chip">
                            <span class="ob-chip-label">Search:</span> {{ $searchTerm }}
                            <a href="{{ $queryWithout(['search']) }}" title="Remove filter"><i data-feather="x"></i></a>
                        </span>
                    @endif
                    @if ($selectedCountry)
                        <span class="ob-chip">
                            <span class="ob-chip-label">Country:</span> {{ $selectedCountry }}
                            <a href="{{ $queryWithout(['country_id']) }}" title="Remove filter"><i data-feather="x"></i></a>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="ob-table">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Practice', 'key' => 'practice', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Owner', 'key' => 'owner', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Location', 'key' => 'location', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Contact', 'key' => 'contact', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Members', 'key' => 'members_count', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($practices as $practice)
                        @php
                            $initials = strtoupper(mb_substr(trim($practice->name ?? ''), 0, 2));
                            $logoUrl = $practice->logoUrl();

                            $ownerName = $practice->owner
                                ? 'Dr. ' . trim(($practice->owner->first_name ?? '') . ' ' . ($practice->owner->last_name ?? ''))
                                : null;

                            $cityName    = $practice->city?->name;
                            $stateName   = $practice->state?->name;
                            $countryName = $practice->country?->name;
                            $locationMain = trim(collect([$cityName, $stateName])->filter()->implode(', '));

                            $phoneDisplay = trim(($practice->phone_country_code ? '+' . ltrim($practice->phone_country_code, '+') . ' ' : '') . ($practice->phone_number ?? ''));
                            $websiteDisplay = $practice->website;
                            $websiteHref = $websiteDisplay
                                ? (\Illuminate\Support\Str::startsWith($websiteDisplay, ['http://', 'https://']) ? $websiteDisplay : 'http://' . $websiteDisplay)
                                : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="ob-practice-cell">
                                    <span class="ob-logo">
                                        @if ($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $practice->name }}"
                                                 data-preview-src="{{ $logoUrl }}" data-preview-size="lg">
                                        @else
                                            {{ $initials ?: 'P' }}
                                        @endif
                                    </span>
                                    <div>
                                        <span class="ob-practice-name">
                                            {{ $practice->name }}
                                            @if (($practice->pending_pivot_count ?? 0) > 0)
                                                <a href="{{ route('admin.practices.show', $practice) }}"
                                                   class="ob-pending-pill"
                                                   title="{{ $practice->pending_pivot_count }} pending doctor {{ \Illuminate\Support\Str::plural('approval', $practice->pending_pivot_count) }}">
                                                    {{ $practice->pending_pivot_count }} pending
                                                </a>
                                            @endif
                                        </span>
                                        @if ($practice->street_address_1)
                                            <span class="ob-practice-sub">{{ $practice->street_address_1 }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="ob-owner">{{ $ownerName ?? '—' }}</span>
                            </td>
                            <td>
                                @if ($locationMain !== '' || $countryName)
                                    <span class="ob-location">
                                        {{ $locationMain !== '' ? $locationMain : '—' }}
                                        @if ($countryName)
                                            <span class="ob-location-sub">{{ $countryName }}</span>
                                        @endif
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($phoneDisplay !== '')
                                    <span class="ob-contact-line">
                                        <i data-feather="phone"></i>
                                        {{ $phoneDisplay }}
                                    </span>
                                @endif
                                @if ($websiteDisplay)
                                    @if ($phoneDisplay !== '')<br>@endif
                                    <span class="ob-contact-line is-sub">
                                        <i data-feather="globe"></i>
                                        <a href="{{ $websiteHref }}" target="_blank" rel="noopener">{{ $websiteDisplay }}</a>
                                    </span>
                                @endif
                                @if ($phoneDisplay === '' && !$websiteDisplay)
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="ob-members-pill" title="Doctors in this practice">
                                    <i data-feather="users"></i>
                                    {{ (int) ($practice->members_count ?? 0) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="ob-row-actions">
                                    <a href="{{ route('admin.practices.show', $practice) }}"
                                       class="ob-icon-btn" title="View details">
                                        <i data-feather="eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="ob-empty">
                                    <div class="ob-empty-icon"><i data-feather="briefcase"></i></div>
                                    <div class="ob-empty-title">No practices found</div>
                                    <div class="ob-empty-sub">
                                        @if ($hasActiveFilters)
                                            Try adjusting your filters or <a href="{{ route('admin.practices.index') }}">clear all filters</a>.
                                        @else
                                            Practices will appear here as doctors register them.
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        @if ($practices->total() > 0)
            <div class="ob-foot">
                <div class="ob-foot-meta">
                    Showing <strong>{{ $practices->firstItem() }}</strong>–<strong>{{ $practices->lastItem() }}</strong>
                    of <strong>{{ number_format($practices->total()) }}</strong>
                    {{ \Illuminate\Support\Str::plural('practice', $practices->total()) }}
                </div>
                <div>{{ $practices->links() }}</div>
            </div>
        @endif
    </div>
</section>

@push('scripts')<script>obAutoFilter('#practicesFilter');</script>@endpush
@endsection
