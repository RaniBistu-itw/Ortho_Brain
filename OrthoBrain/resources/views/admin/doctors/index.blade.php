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
       Doctors listing — aligned with orthobrain brand palette
       (cyan #5bc0de primary, green #8cc63f accent)
       ────────────────────────────────────────────────────────── */
    #doctors-page {
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

    /* ──────────────────────────────────────────────────────────
       Main listing card
       ────────────────────────────────────────────────────────── */
    .ob-list-card {
        background: var(--ob-surface);
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
    .ob-card-head-title { font-size: 1.05rem; font-weight: 700; color: #111; margin: 0; }
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
        background: #f3f2f7;
        color: var(--ob-muted);
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
    .ob-btn-clear {
        border: 1px solid #e2e0ea;
        background: #fff;
        color: var(--ob-muted);
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.5rem 0.85rem;
        transition: color 120ms ease, border-color 120ms ease, background 120ms ease;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
    }
    .ob-btn-clear:hover { color: var(--ob-danger); border-color: rgba(234, 84, 85, 0.35); background: #fff7f7; }
    .ob-btn-clear svg { width: 14px; height: 14px; }

    /* Active-filter chips row */
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

    /* ──────────────────────────────────────────────────────────
       Table
       ────────────────────────────────────────────────────────── */
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

    /* Doctor cell */
    .ob-doctor-cell { display: flex; align-items: center; gap: 0.75rem; min-width: 220px; }
    .ob-doctor-cell .ob-doctor-name {
        font-weight: 700; color: #111; line-height: 1.2;
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
        box-shadow: 0 0 0 2px #fff, 0 0 0 3px #ebe9f1;
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

    /* Status badge (dot + label) */
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
    .ob-status--success  { background: var(--ob-accent-soft); color: #5a8f21; border-color: rgba(140, 198, 63, 0.28); }
    .ob-status--warning  { background: var(--ob-warning-soft); color: #b76a00; border-color: rgba(255, 159, 67, 0.28); }
    .ob-status--danger   { background: var(--ob-danger-soft); color: #c53a3b; border-color: rgba(234, 84, 85, 0.22); }
    .ob-status--secondary{ background: #eef0f4; color: #6c7283; border-color: #e2e4eb; }

    /* Registered cell */
    .ob-when { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--ob-text); }
    .ob-when svg { width: 13px; height: 13px; color: #9a9aab; }

    /* Row actions */
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
    .ob-empty-title { font-weight: 700; color: #111; margin-bottom: 0.25rem; }
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
    $tabCount = fn ($key) => $key === null ? ($totalCount ?? 0) : (int) ($statusCounts[$key] ?? 0);
    $statusToBadge = [
        'PENDING'   => ['label' => 'Pending',   'tone' => 'warning'],
        'APPROVED'  => ['label' => 'Approved',  'tone' => 'success'],
        'REJECTED'  => ['label' => 'Rejected',  'tone' => 'danger'],
        'SUSPENDED' => ['label' => 'Suspended', 'tone' => 'secondary'],
    ];

    $selectedPractice = request('practice_id')
        ? optional($practices->firstWhere('id', (int) request('practice_id')))->name
        : null;
    $searchTerm = trim((string) request('search', ''));
    $hasActiveFilters = $selectedPractice || $searchTerm !== '';

    $queryWithout = function (array $remove) {
        $q = request()->query();
        foreach ($remove as $k) { unset($q[$k]); }
        return route('admin.doctors.index', $q);
    };
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

            @if ($hasActiveFilters)
                <div class="ob-active-filters">
                    @if ($searchTerm !== '')
                        <span class="ob-chip">
                            <span class="ob-chip-label">Search:</span> {{ $searchTerm }}
                            <a href="{{ $queryWithout(['search']) }}" title="Remove filter"><i data-feather="x"></i></a>
                        </span>
                    @endif
                    @if ($selectedPractice)
                        <span class="ob-chip">
                            <span class="ob-chip-label">Practice:</span> {{ $selectedPractice }}
                            <a href="{{ $queryWithout(['practice_id']) }}" title="Remove filter"><i data-feather="x"></i></a>
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
                        <th>Doctor</th>
                        <th>Practice</th>
                        <th>Contact</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $doctor)
                        @php
                            $initials = strtoupper(substr($doctor->first_name, 0, 1) . substr($doctor->last_name, 0, 1));
                            $badge = $statusToBadge[$doctor->approval_status] ?? ['label' => $doctor->approval_status, 'tone' => 'secondary'];
                            $avatarUrl = $doctor->avatarUrl();
                        @endphp
                        <tr>
                            <td>
                                <div class="ob-doctor-cell">
                                    <span class="ob-avatar">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" alt="Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}"
                                                 data-preview-src="{{ $avatarUrl }}" data-preview-size="lg">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </span>
                                    <div>
                                        <span class="ob-doctor-name">Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}</span>
                                        @if ($doctor->other_email)
                                            <span class="ob-doctor-sub">{{ $doctor->other_email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="ob-practice">{{ $doctor->practice?->name ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="ob-contact-line">
                                    <i data-feather="mail"></i>
                                    {{ $doctor->doctor_contact_email }}
                                </span>
                                @if ($doctor->doctor_cell_phone)
                                    <br>
                                    <span class="ob-contact-line is-sub">
                                        <i data-feather="phone"></i>
                                        {{ $doctor->doctor_cell_phone }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="ob-when" title="{{ $doctor->created_at?->toDayDateTimeString() }}">
                                    <i data-feather="calendar"></i>
                                    {{ $doctor->created_at?->diffForHumans() ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="ob-status ob-status--{{ $badge['tone'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="ob-row-actions">
                                    <a href="{{ route('admin.doctors.show', $doctor) }}"
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
                                    <div class="ob-empty-icon"><i data-feather="users"></i></div>
                                    <div class="ob-empty-title">No doctors found</div>
                                    <div class="ob-empty-sub">
                                        @if ($hasActiveFilters)
                                            Try adjusting your filters or <a href="{{ route('admin.doctors.index') }}">clear all filters</a>.
                                        @else
                                            Get started by adding your first doctor.
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
        @if ($doctors->total() > 0)
            <div class="ob-foot">
                <div class="ob-foot-meta">
                    Showing <strong>{{ $doctors->firstItem() }}</strong>–<strong>{{ $doctors->lastItem() }}</strong>
                    of <strong>{{ number_format($doctors->total()) }}</strong>
                    {{ \Illuminate\Support\Str::plural('doctor', $doctors->total()) }}
                </div>
                <div>{{ $doctors->links() }}</div>
            </div>
        @endif
    </div>
</section>

@push('scripts')<script>obAutoFilter('#doctorsFilter');</script>@endpush
@endsection
