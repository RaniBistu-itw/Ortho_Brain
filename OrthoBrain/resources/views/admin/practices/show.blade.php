@extends('layouts.admin')
@section('title', 'Practice · ' . $practice->name)
@section('page_title', $practice->name)

@push('styles')
<style>
    #practice-show {
        --ob-primary: #5bc0de;
        --ob-primary-softer: rgba(91, 192, 222, 0.07);
        --ob-accent-soft: rgba(140, 198, 63, 0.14);
        --ob-muted: #6e6b7b;
        --ob-border: #ebe9f1;
        --ob-surface: #ffffff;
        --ob-surface-alt: #f8f8fb;
        --ob-text: #1f1f1f;
    }
    #practice-show .ob-card {
        background: var(--ob-surface);
        border: 1px solid var(--ob-border);
        border-radius: 0.85rem;
        box-shadow: 0 1px 2px rgba(24, 28, 40, 0.04);
        overflow: hidden;
    }
    #practice-show .ob-card + .ob-card { margin-top: 1rem; }
    #practice-show .ob-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        gap: 0.75rem; flex-wrap: wrap;
    }
    #practice-show .ob-card-title { font-weight: 700; color: #111; margin: 0; font-size: 1rem; }
    #practice-show .ob-card-body { padding: 1rem 1.25rem; }

    #practice-show .ob-back {
        display: inline-flex; align-items: center; gap: 0.35rem;
        color: var(--ob-muted);
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 0.75rem;
    }
    #practice-show .ob-back:hover { color: var(--ob-primary); }
    #practice-show .ob-back svg { width: 14px; height: 14px; }

    #practice-show .ob-hero {
        display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;
        padding: 1.25rem;
    }
    #practice-show .ob-logo-lg {
        width: 72px; height: 72px;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #e3f4fa 0%, #c9ebf5 100%);
        color: var(--ob-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1.3rem;
        overflow: hidden;
        box-shadow: 0 0 0 2px #fff, 0 0 0 3px #ebe9f1;
        flex: 0 0 auto;
    }
    #practice-show .ob-logo-lg img { width: 100%; height: 100%; object-fit: cover; }
    #practice-show .ob-hero-name { font-size: 1.2rem; font-weight: 700; color: #111; margin: 0; }
    #practice-show .ob-hero-sub { color: var(--ob-muted); font-size: 0.88rem; margin-top: 0.15rem; }

    #practice-show .ob-status {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem; font-weight: 700;
        letter-spacing: 0.03em; text-transform: uppercase;
        border: 1px solid transparent;
    }
    #practice-show .ob-status::before {
        content: ''; width: 6px; height: 6px;
        border-radius: 50%; background: currentColor; display: inline-block;
    }
    #practice-show .ob-status--success   { background: var(--ob-accent-soft); color: #5a8f21; border-color: rgba(140, 198, 63, 0.28); }
    #practice-show .ob-status--secondary { background: #eef0f4; color: #6c7283; border-color: #e2e4eb; }

    #practice-show .ob-grid {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem 1.5rem;
    }
    @media (max-width: 576px) { #practice-show .ob-grid { grid-template-columns: 1fr; } }

    #practice-show .ob-field-label {
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;
        color: var(--ob-muted); text-transform: uppercase;
        display: block; margin-bottom: 0.25rem;
    }
    #practice-show .ob-field-value { color: var(--ob-text); font-size: 0.92rem; }
    #practice-show .ob-field-value a { color: var(--ob-primary); text-decoration: none; }
    #practice-show .ob-field-value a:hover { text-decoration: underline; }

    #practice-show .ob-members-list { width: 100%; }
    #practice-show .ob-members-list th, #practice-show .ob-members-list td {
        padding: 0.65rem 1.25rem;
        border-top: 1px solid #f3f2f7;
        font-size: 0.88rem;
    }
    #practice-show .ob-members-list thead th {
        background: var(--ob-surface-alt);
        color: #555668;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-top: 0;
    }
    #practice-show .ob-empty-sub { color: var(--ob-muted); padding: 1.25rem; text-align: center; }

    #practice-show .ob-pivot-pill {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-radius: 999px;
        border: 1px solid transparent;
    }
    #practice-show .ob-pivot-pill--pending   { background: #fff5e5; color: #b9681a; border-color: #ffdcaf; }
    #practice-show .ob-pivot-pill--approved  { background: var(--ob-accent-soft); color: #5a8f21; border-color: rgba(140, 198, 63, 0.28); }
    #practice-show .ob-pivot-pill--rejected  { background: #fde7e7; color: #b13233; border-color: #f5caca; }
    #practice-show .ob-pivot-pill--cancelled,
    #practice-show .ob-pivot-pill--left      { background: #eef0f4; color: #6c7283; border-color: #e2e4eb; }
    #practice-show .ob-pivot-pill--onhold    { background: #fff5e5; color: #b9681a; border-color: #ffdcaf; }

    #practice-show .ob-inline-btn {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.3rem 0.6rem;
        border-radius: 0.4rem;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
    }
    #practice-show .ob-inline-btn + .ob-inline-btn { margin-left: 0.3rem; }
    #practice-show .ob-inline-btn--approve  { background: #2eb85c; color: #fff; }
    #practice-show .ob-inline-btn--approve:hover { background: #289c4f; }
    #practice-show .ob-inline-btn--reject   { background: #fff; color: #c53030; border-color: #f5caca; }
    #practice-show .ob-inline-btn--reject:hover { background: #fde7e7; }
    #practice-show .ob-inline-btn svg { width: 12px; height: 12px; }

    /* Hero practice-status select */
    #practice-show .ob-hero-status {
        display: flex; flex-direction: column; gap: 0.25rem; align-items: flex-end;
    }
    #practice-show .ob-hero-status-label {
        font-size: 0.66rem;
        color: var(--ob-muted);
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    #practice-show .ob-status-select {
        padding: 0.4rem 2rem 0.4rem 0.9rem;
        border-radius: 0.5rem;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border: 1px solid transparent;
        cursor: pointer;
        appearance: none;
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        background-size: 10px 10px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M1 3l4 4 4-4' stroke='%236e6b7b' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    }
    #practice-show .ob-status-select:focus { outline: 2px solid rgba(91, 192, 222, 0.3); outline-offset: 1px; }
    #practice-show .ob-status-select--success   { background-color: var(--ob-accent-soft); color: #5a8f21; border-color: rgba(140, 198, 63, 0.28); }
    #practice-show .ob-status-select--secondary { background-color: #eef0f4; color: #6c7283; border-color: #e2e4eb; }
    #practice-show .ob-status-select[disabled]  { opacity: .6; cursor: wait; }

    /* Per-doctor state dropdown */
    #practice-show .ob-doctor-state {
        padding: 0.35rem 1.9rem 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        border: 1px solid transparent;
        cursor: pointer;
        appearance: none;
        background-repeat: no-repeat;
        background-position: right 0.55rem center;
        background-size: 10px 10px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M1 3l4 4 4-4' stroke='%236e6b7b' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        min-width: 140px;
    }
    #practice-show .ob-doctor-state--pending   { background-color: #fff5e5; color: #b9681a; border-color: #ffdcaf; }
    #practice-show .ob-doctor-state--approved  { background-color: var(--ob-accent-soft); color: #5a8f21; border-color: rgba(140, 198, 63, 0.28); }
    #practice-show .ob-doctor-state--rejected  { background-color: #fde7e7; color: #b13233; border-color: #f5caca; }
    #practice-show .ob-doctor-state--suspended { background-color: #eef0f4; color: #555668; border-color: #d5d8e0; }
    #practice-show .ob-doctor-state[disabled]  { opacity: .6; cursor: wait; }

    /* Doctor card toolbar (search + bulk actions) */
    #practice-show .ob-doctors-toolbar {
        display: flex; align-items: center; gap: .6rem; flex-wrap: wrap;
        padding: .85rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        background: var(--ob-surface-alt);
    }
    #practice-show .ob-doctors-search {
        flex: 1 1 240px;
        position: relative;
        min-width: 220px;
    }
    #practice-show .ob-doctors-search > svg {
        position: absolute;
        left: .75rem; top: 50%; transform: translateY(-50%);
        width: 16px; height: 16px;
        color: #9a9aab;
        pointer-events: none;
    }
    #practice-show .ob-doctors-search input {
        width: 100%;
        padding: .45rem .75rem .45rem 2.3rem;
        border: 1px solid #e2e0ea;
        border-radius: .5rem;
        background: #fff;
        font-size: .85rem;
        color: var(--ob-text);
    }
    #practice-show .ob-doctors-search input:focus {
        outline: none;
        border-color: var(--ob-primary);
        box-shadow: 0 0 0 3px var(--ob-primary-softer);
    }
    #practice-show .ob-doctors-filter {
        position: relative;
        flex: 0 0 auto;
    }
    #practice-show .ob-doctors-filter select {
        appearance: none;
        padding: .45rem 2rem .45rem .75rem;
        border: 1px solid #e2e0ea;
        border-radius: .5rem;
        background: #fff;
        font-size: .85rem;
        color: var(--ob-text);
        cursor: pointer;
        background-repeat: no-repeat;
        background-position: right .6rem center;
        background-size: 10px 10px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M1 3l4 4 4-4' stroke='%236e6b7b' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    }
    #practice-show .ob-doctors-filter select:focus {
        outline: none;
        border-color: var(--ob-primary);
        box-shadow: 0 0 0 3px var(--ob-primary-softer);
    }
    #practice-show .ob-bulk-actions {
        display: inline-flex; align-items: center; gap: .4rem;
    }
    #practice-show .ob-contact-line {
        display: inline-flex; align-items: center; gap: 0.4rem;
        color: var(--ob-text);
    }
    #practice-show .ob-contact-line svg { width: 13px; height: 13px; color: #9a9aab; flex: 0 0 auto; }
    #practice-show .ob-contact-line a { color: var(--ob-text); text-decoration: none; }
    #practice-show .ob-contact-line a:hover { color: var(--ob-primary); text-decoration: underline; }
    #practice-show .ob-bulk-btn {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .45rem .85rem;
        border-radius: .5rem;
        font-size: .8rem;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        transition: background 120ms ease, color 120ms ease, border-color 120ms ease, transform 120ms ease;
        background: #fff;
    }
    #practice-show .ob-bulk-btn svg { width: 14px; height: 14px; }
    #practice-show .ob-bulk-btn--approve {
        background: #2eb85c;
        color: #fff;
        border-color: #2eb85c;
    }
    #practice-show .ob-bulk-btn--approve:hover:not(:disabled) {
        background: #289c4f;
        border-color: #289c4f;
        transform: translateY(-1px);
    }
    #practice-show .ob-bulk-btn--reject {
        background: #fff;
        color: #c53030;
        border-color: #f5caca;
    }
    #practice-show .ob-bulk-btn--reject:hover:not(:disabled) {
        background: #fde7e7;
        transform: translateY(-1px);
    }
    #practice-show .ob-bulk-btn:disabled {
        opacity: .55;
        cursor: not-allowed;
        transform: none;
    }
    #practice-show .ob-empty-row td {
        padding: 1.5rem 1.25rem;
        text-align: center;
        color: var(--ob-muted);
        font-size: .85rem;
    }
</style>
@endpush

@section('content')
@php
    $logoUrl = $practice->logoUrl();
    $initials = strtoupper(mb_substr(trim($practice->name ?? ''), 0, 2));
    $statusToBadge = [
        'ACTIVE'   => ['label' => 'Active',   'tone' => 'success'],
        'INACTIVE' => ['label' => 'Inactive', 'tone' => 'secondary'],
    ];
    $badge = $statusToBadge[$practice->status] ?? ['label' => $practice->status, 'tone' => 'secondary'];

    $phoneDisplay = trim(($practice->phone_country_code ? '+' . ltrim($practice->phone_country_code, '+') . ' ' : '') . ($practice->phone_number ?? ''));
    $websiteHref = $practice->website
        ? (\Illuminate\Support\Str::startsWith($practice->website, ['http://', 'https://']) ? $practice->website : 'http://' . $practice->website)
        : null;

    $addressLines = array_values(array_filter([
        $practice->street_address_1,
        $practice->street_address_2,
        trim(collect([$practice->city?->name, $practice->state?->name, $practice->zipcode?->code])->filter()->implode(', ')),
        $practice->country?->name,
    ]));
@endphp

<section id="practice-show">
    <a href="{{ route('admin.practices.index') }}" class="ob-back">
        <i data-feather="arrow-left"></i> Back to Practices
    </a>

    {{-- Hero card --}}
    <div class="ob-card">
        <div class="ob-hero">
            <span class="ob-logo-lg">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $practice->name }}">
                @else
                    {{ $initials ?: 'P' }}
                @endif
            </span>
            <div class="flex-grow-1">
                <h4 class="ob-hero-name">{{ $practice->name }}</h4>
                <div class="ob-hero-sub">
                    Added {{ $practice->created_at?->toFormattedDateString() ?? '—' }}
                </div>
            </div>
            <div class="ob-hero-status">
                <label class="ob-hero-status-label">Practice status</label>
                <select id="practiceStatusSelect"
                        class="ob-status-select ob-status-select--{{ $badge['tone'] }}"
                        data-url="{{ route('admin.practices.status', $practice) }}"
                        data-practice-name="{{ $practice->name }}"
                        data-current="{{ $practice->status }}">
                    <option value="ACTIVE"   @selected($practice->status === 'ACTIVE')>Active</option>
                    <option value="INACTIVE" @selected($practice->status === 'INACTIVE')>Inactive</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Contact + Address --}}
    <div class="ob-card">
        <div class="ob-card-head">
            <h5 class="ob-card-title">Practice details</h5>
            <a href="{{ route('admin.practices.edit', $practice) }}" class="btn btn-sm btn-outline-primary">
                <i data-feather="edit-2"></i> Edit details
            </a>
        </div>
        <div class="ob-card-body">
            <div class="ob-grid">
                <div>
                    <span class="ob-field-label">Phone</span>
                    <div class="ob-field-value">{{ $phoneDisplay !== '' ? $phoneDisplay : '—' }}</div>
                </div>
                <div>
                    <span class="ob-field-label">Website</span>
                    <div class="ob-field-value">
                        @if ($practice->website)
                            <a href="{{ $websiteHref }}" target="_blank" rel="noopener">{{ $practice->website }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <span class="ob-field-label">Address</span>
                    <div class="ob-field-value">
                        @if (count($addressLines))
                            @foreach ($addressLines as $line)
                                {{ $line }}@if (!$loop->last)<br>@endif
                            @endforeach
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Doctors at this practice (status + per-doctor approval dropdown) --}}
    @php
        $pivotRows = $practice->doctors
            ->sortBy(fn ($d) => match ($d->pivot->approval_status ?? 'PENDING') {
                'PENDING'   => 0,
                'APPROVED'  => 1,
                'SUSPENDED' => 2,
                'REJECTED'  => 3,
                default     => 4,
            })
            ->values();
        $pendingPivotCount = $pivotRows->where('pivot.approval_status', 'PENDING')->count();

        // Transitions table — must mirror DoctorPracticeController::updatePivotStatus.
        $transitions = [
            'PENDING'   => [
                ['value' => 'APPROVED', 'label' => 'Approve'],
                ['value' => 'REJECTED', 'label' => 'Reject'],
            ],
            'APPROVED'  => [
                ['value' => 'SUSPENDED', 'label' => 'Suspend'],
            ],
            'REJECTED'  => [
                ['value' => 'APPROVED', 'label' => 'Re-approve'],
            ],
            'SUSPENDED' => [
                ['value' => 'APPROVED', 'label' => 'Re-approve'],
            ],
        ];
        $stateLabel = [
            'PENDING'   => 'Pending',
            'APPROVED'  => 'Active',
            'REJECTED'  => 'Rejected',
            'SUSPENDED' => 'Suspended',
            'CANCELLED' => 'Cancelled',
            'LEFT'      => 'Left',
        ];
        $stateTone = [
            'PENDING'   => 'pending',
            'APPROVED'  => 'approved',
            'REJECTED'  => 'rejected',
            'SUSPENDED' => 'suspended',
            'CANCELLED' => 'suspended',
            'LEFT'      => 'suspended',
        ];
    @endphp
    <div class="ob-card">
        <div class="ob-card-head">
            <h5 class="ob-card-title">
                Doctors ({{ $pivotRows->count() }})
                @if ($pendingPivotCount > 0)
                    <span class="ob-pivot-pill ob-pivot-pill--pending ms-2" id="pendingPivotBadge">{{ $pendingPivotCount }} pending</span>
                @endif
            </h5>
        </div>
        @if ($pivotRows->count())
            <div class="ob-doctors-toolbar">
                <div class="ob-doctors-search">
                    <i data-feather="search"></i>
                    <input type="text" id="doctorSearchInput" placeholder="Search by doctor name or email…" autocomplete="off">
                </div>
                <div class="ob-doctors-filter">
                    <select id="doctorRelationshipFilter" aria-label="Filter doctors by primary or secondary relationship">
                        <option value="all">All doctors</option>
                        <option value="primary">Primary only</option>
                        <option value="secondary">Secondary only</option>
                    </select>
                </div>
                <div class="ob-bulk-actions">
                    <button type="button"
                            id="bulkApproveBtn"
                            class="ob-bulk-btn ob-bulk-btn--approve"
                            data-url="{{ route('admin.practices.doctors.bulk', $practice) }}"
                            data-practice-name="{{ $practice->name }}"
                            @disabled($pendingPivotCount === 0)>
                        <i data-feather="check"></i> Approve all
                    </button>
                    <button type="button"
                            id="bulkRejectBtn"
                            class="ob-bulk-btn ob-bulk-btn--reject"
                            data-url="{{ route('admin.practices.doctors.bulk', $practice) }}"
                            data-practice-name="{{ $practice->name }}"
                            @disabled($pendingPivotCount === 0)>
                        <i data-feather="x"></i> Reject all
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="ob-members-list">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Email</th>
                            <th>Requested</th>
                            <th class="text-end">Approval</th>
                        </tr>
                    </thead>
                    <tbody id="doctorRowsBody">
                        @foreach ($pivotRows as $d)
                            @php
                                $cur         = $d->pivot->approval_status ?? 'PENDING';
                                $label       = $stateLabel[$cur] ?? ucfirst(strtolower($cur));
                                $tone        = $stateTone[$cur] ?? 'pending';
                                $requestedAt = $d->pivot->requested_at ?? $d->pivot->created_at;
                                $actions     = $transitions[$cur] ?? [];
                                $doctorEmail = $d->user?->email ?? $d->doctor_contact_email ?? '';
                                $searchHaystack = mb_strtolower(trim('Dr. ' . $d->first_name . ' ' . $d->last_name . ' ' . $doctorEmail));
                            @endphp
                            <tr data-doctor-row data-search="{{ $searchHaystack }}" data-relationship="{{ $d->pivot->is_primary ? 'primary' : 'secondary' }}">
                                <td>
                                    Dr. {{ $d->first_name }} {{ $d->last_name }}
                                    @if($d->pivot->is_primary)
                                        <span class="ob-pivot-pill ob-pivot-pill--approved ms-1" title="This practice is the doctor's primary practice">primary</span>
                                    @else
                                        <span class="ob-pivot-pill ob-pivot-pill--left ms-1" title="This practice is one of the doctor's secondary practices">secondary</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ob-contact-line">
                                        <i data-feather="mail"></i>
                                        @if ($doctorEmail !== '')
                                            <a href="mailto:{{ $doctorEmail }}"
                                               title="Email Dr. {{ $d->first_name }} {{ $d->last_name }}">{{ $doctorEmail }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($requestedAt)
                                        {{ \Carbon\Carbon::parse($requestedAt)->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                    @if ($cur === 'REJECTED' && $d->pivot->rejection_reason)
                                        <div class="text-muted" style="font-size:.72rem;margin-top:.15rem;" title="{{ $d->pivot->rejection_reason }}">
                                            {{ \Illuminate\Support\Str::limit($d->pivot->rejection_reason, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($practice->status === 'INACTIVE' && $cur === 'APPROVED')
                                        {{-- Practice is paused: per-doctor approval still APPROVED in DB,
                                             but we surface the practice-level hold so admin sees the system state. --}}
                                        <span class="ob-pivot-pill ob-pivot-pill--onhold" title="Practice is currently inactive">
                                            On Hold
                                        </span>
                                    @elseif (count($actions))
                                        <select
                                            class="ob-doctor-state ob-doctor-state--{{ $tone }}"
                                            data-url="{{ route('admin.doctors.practices.status', [$d, $d->pivot->id]) }}"
                                            data-doctor-name="Dr. {{ $d->first_name }} {{ $d->last_name }}"
                                            data-practice-name="{{ $practice->name }}"
                                            data-current="{{ $cur }}">
                                            <option value="" selected>{{ $label }}</option>
                                            @foreach ($actions as $a)
                                                <option value="{{ $a['value'] }}">{{ $a['label'] }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="ob-pivot-pill ob-pivot-pill--{{ $tone }}">{{ $label }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr id="doctorSearchEmpty" hidden>
                            <td colspan="4" class="ob-empty-row">No doctors match your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="ob-empty-sub">No doctor has requested access to this practice yet.</div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function jsonPost(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body || {})
        }).then(function (r) {
            if (r.ok) return r.json();
            return r.json().catch(function () { return {}; }).then(function (data) {
                return Promise.reject(data.message || 'Request failed.');
            });
        });
    }

    // Practice-level ACTIVE/INACTIVE dropdown in the hero card.
    var pSel = document.getElementById('practiceStatusSelect');
    if (pSel) {
        pSel.addEventListener('change', function () {
            var previous = pSel.getAttribute('data-current');
            var next     = pSel.value;
            if (previous === next) return;

            var practiceName = pSel.getAttribute('data-practice-name') || 'this practice';
            var confirmText = next === 'INACTIVE'
                ? 'Setting "' + practiceName + '" to Inactive will immediately revoke case access for every approved doctor at this practice.'
                : 'Setting "' + practiceName + '" to Active will approve any pending doctor requests and restore access for approved doctors.';

            Swal.fire({
                title: 'Change practice status?',
                text:  confirmText,
                icon:  'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, set ' + (next === 'ACTIVE' ? 'Active' : 'Inactive'),
                cancelButtonText:  'Cancel',
                customClass: {
                    confirmButton: 'btn ' + (next === 'INACTIVE' ? 'btn-danger' : 'btn-primary'),
                    cancelButton:  'btn btn-outline-secondary ms-1'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (!result.value) { pSel.value = previous; return; }
                pSel.disabled = true;
                jsonPost(pSel.getAttribute('data-url'), { status: next })
                    .then(function (res) {
                        pSel.setAttribute('data-current', res.status);
                        pSel.classList.remove('ob-status-select--success', 'ob-status-select--secondary');
                        pSel.classList.add(res.status === 'ACTIVE' ? 'ob-status-select--success' : 'ob-status-select--secondary');
                        // Reload to refresh the doctor card — cascade may have flipped pivots.
                        window.location.reload();
                    })
                    .catch(function (msg) {
                        pSel.value = previous;
                        Swal.fire({ icon: 'error', title: 'Update failed', text: String(msg) });
                    })
                    .finally(function () { pSel.disabled = false; });
            });
        });
    }

    // Per-doctor state dropdowns.
    var toneFor = {
        APPROVED:  'approved',
        REJECTED:  'rejected',
        SUSPENDED: 'suspended',
        PENDING:   'pending'
    };
    var labelFor = {
        APPROVED:  'Active',
        REJECTED:  'Rejected',
        SUSPENDED: 'Suspended',
        PENDING:   'Pending'
    };

    function reapplyTone(sel, newStatus) {
        sel.classList.remove('ob-doctor-state--pending', 'ob-doctor-state--approved',
                              'ob-doctor-state--rejected', 'ob-doctor-state--suspended');
        sel.classList.add('ob-doctor-state--' + (toneFor[newStatus] || 'pending'));
    }

    document.querySelectorAll('.ob-doctor-state').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var target = sel.value;
            if (!target) return;
            var current      = sel.getAttribute('data-current');
            var url          = sel.getAttribute('data-url');
            var doctorName   = sel.getAttribute('data-doctor-name') || 'this doctor';
            var practiceName = sel.getAttribute('data-practice-name') || 'this practice';

            var needsReason = (target === 'REJECTED' || target === 'SUSPENDED');
            var actionLabel = { APPROVED: 'Approve', REJECTED: 'Reject', SUSPENDED: 'Suspend' }[target] || target;

            var swalPromise;
            if (needsReason) {
                swalPromise = Swal.fire({
                    title: actionLabel + ' ' + doctorName + '?',
                    input: 'textarea',
                    inputLabel: 'Reason (shown to the doctor)',
                    inputPlaceholder: 'Brief reason...',
                    inputAttributes: { maxlength: 500 },
                    showCancelButton: true,
                    confirmButtonText: actionLabel,
                    cancelButtonText:  'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton:  'btn btn-outline-secondary ms-1'
                    },
                    buttonsStyling: false,
                    inputValidator: function (v) {
                        if (!v || !v.trim()) return 'A reason is required.';
                    }
                });
            } else {
                swalPromise = Swal.fire({
                    title: actionLabel + ' ' + doctorName + '?',
                    text:  target === 'APPROVED'
                        ? 'Grants access to ' + practiceName + '.'
                        : '',
                    icon:  'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, ' + actionLabel.toLowerCase(),
                    cancelButtonText:  'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton:  'btn btn-outline-secondary ms-1'
                    },
                    buttonsStyling: false
                });
            }

            swalPromise.then(function (result) {
                if (!result.value) {
                    // Reset select to the "current state" option (the empty-value default).
                    sel.value = '';
                    return;
                }
                var reason = needsReason ? (result.value || '').trim() : null;
                sel.disabled = true;
                jsonPost(url, { status: target, reason: reason })
                    .then(function () {
                        // Reload so the pending badge, bulk-button enabled state, and
                        // valid transitions all stay in sync without bespoke client diffing.
                        Swal.fire({ icon: 'success', title: actionLabel + 'd.', timer: 900, showConfirmButton: false })
                            .then(function () { window.location.reload(); });
                        setTimeout(function () { window.location.reload(); }, 1000);
                    })
                    .catch(function (msg) {
                        sel.value = '';
                        sel.disabled = false;
                        Swal.fire({ icon: 'error', title: 'Update failed', text: String(msg) });
                    });
            });
        });
    });

    // ── Doctor search + relationship filter (client-side) ────────────
    var searchInput      = document.getElementById('doctorSearchInput');
    var relationshipSel  = document.getElementById('doctorRelationshipFilter');
    var emptyRow         = document.getElementById('doctorSearchEmpty');
    function applyDoctorFilters() {
        var q    = searchInput ? (searchInput.value || '').trim().toLowerCase() : '';
        var rel  = relationshipSel ? relationshipSel.value : 'all';
        var visible = 0;
        document.querySelectorAll('tr[data-doctor-row]').forEach(function (row) {
            var hay        = row.getAttribute('data-search') || '';
            var rowRel     = row.getAttribute('data-relationship') || '';
            var matchText  = q === '' || hay.indexOf(q) !== -1;
            var matchRel   = rel === 'all' || rowRel === rel;
            var match      = matchText && matchRel;
            row.hidden = !match;
            if (match) visible++;
        });
        if (emptyRow) emptyRow.hidden = visible !== 0;
    }
    if (searchInput)     searchInput.addEventListener('input', applyDoctorFilters);
    if (relationshipSel) relationshipSel.addEventListener('change', applyDoctorFilters);

    // ── Bulk approve / reject all PENDING doctors ────────────────────
    function runBulk(btn, action) {
        var url          = btn.getAttribute('data-url');
        var practiceName = btn.getAttribute('data-practice-name') || 'this practice';
        var isReject     = action === 'REJECT';
        var actionWord   = isReject ? 'Reject' : 'Approve';

        var swalCfg = {
            title: actionWord + ' all pending doctors?',
            showCancelButton: true,
            confirmButtonText: actionWord + ' all',
            cancelButtonText:  'Cancel',
            customClass: {
                confirmButton: 'btn ' + (isReject ? 'btn-danger' : 'btn-success'),
                cancelButton:  'btn btn-outline-secondary ms-1'
            },
            buttonsStyling: false
        };

        if (isReject) {
            swalCfg.input = 'textarea';
            swalCfg.inputLabel = 'Rejection reason (sent to every doctor)';
            swalCfg.inputPlaceholder = 'Brief reason…';
            swalCfg.inputAttributes = { maxlength: 500 };
            swalCfg.inputValidator = function (v) {
                if (!v || !v.trim()) return 'A reason is required.';
            };
        } else {
            swalCfg.icon = 'question';
            swalCfg.text = 'Every pending doctor at ' + practiceName + ' will be approved.';
        }

        Swal.fire(swalCfg).then(function (result) {
            if (!result.value) return;
            var reason = isReject ? (result.value || '').trim() : null;

            btn.disabled = true;
            jsonPost(url, { action: action, reason: reason })
                .then(function (res) {
                    var n = (res && res.count) || 0;
                    Swal.fire({
                        icon: 'success',
                        title: actionWord + 'd ' + n + ' ' + (n === 1 ? 'doctor' : 'doctors') + '.',
                        timer: 1100,
                        showConfirmButton: false
                    }).then(function () { window.location.reload(); });
                    setTimeout(function () { window.location.reload(); }, 1200);
                })
                .catch(function (msg) {
                    btn.disabled = false;
                    Swal.fire({ icon: 'error', title: 'Bulk update failed', text: String(msg) });
                });
        });
    }

    var bulkApproveBtn = document.getElementById('bulkApproveBtn');
    var bulkRejectBtn  = document.getElementById('bulkRejectBtn');
    if (bulkApproveBtn) bulkApproveBtn.addEventListener('click', function () { runBulk(bulkApproveBtn, 'APPROVE'); });
    if (bulkRejectBtn)  bulkRejectBtn .addEventListener('click', function () { runBulk(bulkRejectBtn,  'REJECT');  });
})();
</script>
@endpush
