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
    #practices-page {
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

    .ob-list-card {
        background: var(--ob-surface-1);
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
    .ob-card-head-title { font-size: 1.05rem; font-weight: 700; color: var(--ob-text); margin: 0; }
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
        background: var(--ob-surface-2);
        color: var(--ob-text-muted);
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
        color: var(--ob-text-muted);
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

    .ob-table { width: 100%; margin: 0; color: var(--ob-text); }
    .ob-table thead th {
        background: var(--ob-surface-alt);
        color: var(--ob-text);
        font-weight: 700;
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
        border-top: 1px solid var(--ob-border);
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .ob-table tbody tr { transition: background 120ms ease; }
    .ob-table tbody tr:hover { background: var(--ob-primary-softer); }

    .ob-practice-cell { display: flex; align-items: center; gap: 0.75rem; min-width: 240px; }
    .ob-practice-cell .ob-practice-name {
        font-weight: 700; color: var(--ob-text); line-height: 1.2;
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
        box-shadow: 0 0 0 2px var(--ob-surface-1), 0 0 0 3px var(--ob-border);
    }
    .ob-logo img { width: 100%; height: 100%; object-fit: cover; }

    .ob-contact-line {
        display: inline-flex; align-items: center; gap: 0.4rem;
        color: var(--ob-text);
    }
    .ob-contact-line + .ob-contact-line { margin-top: 0.2rem; }
    .ob-contact-line svg { width: 13px; height: 13px; color: var(--ob-text-muted); flex: 0 0 auto; }
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

    .pr-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .pr-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .pr-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .pr-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

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
    .ob-when svg { width: 13px; height: 13px; color: var(--ob-text-muted); }

    /* .ob-row-actions and .ob-icon-btn (incl. --view/--edit/--delete rest +
       hover states) are defined globally in orthobrain-palette.css so they
       match every other admin index. Don't redefine them here. */

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
    @include('admin._partials.stat_cards', [
        'cards' => [
            ['label' => 'Total',    'value' => $totalCount ?? 0,                          'icon' => 'briefcase',    'tone' => 'primary'],
            ['label' => 'Active',   'value' => (int) ($statusCounts['ACTIVE']   ?? 0),    'icon' => 'check-circle', 'tone' => 'success'],
            ['label' => 'Inactive', 'value' => (int) ($statusCounts['INACTIVE'] ?? 0),    'icon' => 'slash',        'tone' => 'danger'],
        ],
    ])

    <div class="ob-list-card">
        {{-- Card head: dynamic title --}}
        <div class="ob-card-head">
            <h4 class="card-title mb-0">{{ $practicesTableTitle }}</h4>
        </div>

        {{-- Toolbar --}}
        <div class="ob-toolbar">
            <form id="practicesFilter" method="GET" class="row g-2 align-items-center">
                <div class="col-md-2">
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
                <div class="col-md-3">
                    <div class="ob-input-icon">
                        <i data-feather="search"></i>
                        <input type="text" name="search" placeholder="Search by name, website, or phone"
                               value="{{ $searchTerm }}" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="order" class="form-select" aria-label="Sort order">
                        <option value="newest" @selected(request('order', 'newest') === 'newest')>Newest first</option>
                        <option value="oldest" @selected(request('order') === 'oldest')>Oldest first</option>
                    </select>
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
            <table class="table ob-table">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Practice', 'key' => 'practice', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Location', 'key' => 'location', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Contact', 'key' => 'contact', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Members', 'key' => 'members_count', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($practices as $practice)
                        @php
                            $initials = strtoupper(mb_substr(trim($practice->name ?? ''), 0, 2));
                            $logoUrl = $practice->logoUrl();

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
                        <tr data-row-href="{{ route('admin.practices.show', $practice) }}">
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
                                    @php $phoneHref = preg_replace('/[^0-9+]/', '', $phoneDisplay); @endphp
                                    <span class="ob-contact-line">
                                        <i data-feather="phone"></i>
                                        <a href="tel:{{ $phoneHref }}"
                                           title="Call {{ $practice->name }}">{{ $phoneDisplay }}</a>
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
                            <td>
                                <span class="pr-status pr-status--{{ $practice->status === 'ACTIVE' ? 'active' : 'inactive' }}">{{ $practice->status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="ob-row-actions">
                                    <a href="{{ route('admin.practices.show', $practice) }}"
                                       class="ob-icon-btn ob-icon-btn--view" title="View details">
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
