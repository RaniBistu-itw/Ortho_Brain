@extends('layouts.admin')

@section('title', 'Cases')
@section('page_title', 'Cases')

@php
  $statusTone = [
    'DRAFT'     => 'secondary',
    'SUBMITTED' => 'info',
    'IN_REVIEW' => 'warning',
    'APPROVED'  => 'success',
    'REJECTED'  => 'danger',
  ];

  $statusLabel = $statusFilter
    ? \Illuminate\Support\Str::of($statusFilter)->lower()->replace('_', ' ')->title() . ' Cases'
    : 'All Cases';

  $doctorName = $selectedDoctor
    ? trim($selectedDoctor->first_name . ' ' . $selectedDoctor->last_name)
    : null;

  $heading = $doctorName ? "{$statusLabel} — Dr. {$doctorName}" : $statusLabel;

  // Tab href: preserve search filters, swap status
  $tabHref = fn ($key) => route('admin.cases.index', array_merge(
      request()->only(['doctor_id', 'patient_id', 'practice_id', 'case_id', 'order', 'sort', 'dir']),
      $key ? ['status' => $key] : []
  ));

  // Chip href: preserve everything, drop one param
  $chipQuery  = request()->except('page');
  $chipHref   = fn ($p) => route('admin.cases.index', \Illuminate\Support\Arr::except($chipQuery, $p));

  $anyFilter = $doctorFilter || $patientFilter || $practiceFilter || $caseIdFilter;
@endphp

@push('styles')
<style>
    #admin-cases-list {
        --ob-primary-softer: rgba(59, 130, 246, 0.07);
        --ob-muted: var(--ob-text-muted);
        --ob-surface-alt: var(--ob-surface-2);
        --ob-shadow-sm: 0 1px 2px rgba(24, 28, 40, 0.04);
    }

    /* ── Card shell ─────────────────────────────────────────── */
    .ob-list-card {
        background: var(--ob-surface-1);
        border: 1px solid var(--ob-border);
        border-radius: 0.85rem;
        box-shadow: var(--ob-shadow-sm);
        overflow: hidden;
    }
    .ob-card-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        flex-wrap: wrap;
    }
    .ob-card-head-title { font-size: 1.05rem; font-weight: 700; color: var(--ob-text); margin: 0; }
    .ob-card-head-meta  { color: var(--ob-muted); font-size: 0.82rem; font-weight: 500; }

    /* ── Status tabs ────────────────────────────────────────── */
    .ob-tabs {
        display: flex; flex-wrap: wrap;
        padding: 0.85rem 1.25rem 0;
        gap: 0.35rem;
        border-bottom: 1px solid var(--ob-border);
        list-style: none; margin: 0;
    }
    .ob-tabs .ob-tab {
        display: inline-flex; align-items: center; gap: 0.45rem;
        padding: 0.55rem 0.9rem;
        color: var(--ob-muted); font-weight: 600; font-size: 0.87rem;
        border-radius: 0.5rem 0.5rem 0 0;
        border: 0; background: transparent; text-decoration: none;
        position: relative;
        transition: color 140ms ease, background 140ms ease;
    }
    .ob-tabs .ob-tab:hover { color: var(--ob-primary); background: var(--ob-primary-softer); }
    .ob-tabs .ob-tab.is-active { color: var(--ob-primary); background: var(--ob-primary-softer); }
    .ob-tabs .ob-tab.is-active::after {
        content: ''; position: absolute;
        left: 0.6rem; right: 0.6rem; bottom: -1px; height: 2px;
        background: var(--ob-primary); border-radius: 2px 2px 0 0;
    }
    .ob-tabs .ob-tab-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 1.35rem; height: 1.35rem; padding: 0 0.45rem;
        font-size: 0.7rem; font-weight: 700;
        background: var(--ob-surface-2); color: var(--ob-text-muted);
        border-radius: 999px;
    }
    .ob-tabs .ob-tab.is-active .ob-tab-count { background: var(--ob-primary-soft); color: var(--ob-primary); }

    /* ── Filter toolbar ─────────────────────────────────────── */
    .ob-toolbar {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        background: var(--ob-surface-alt);
    }

    /* Flexbox filter row — no Bootstrap grid, prevents wrapping bugs */
    .ob-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 0.625rem;
        flex-wrap: wrap;
    }
    .ob-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        flex: 1 1 160px;
        min-width: 0;
    }
    .ob-filter-field--sm  { flex: 0 0 130px; }
    .ob-filter-field--xs  { flex: 0 0 100px; }
    .ob-filter-label {
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--ob-text-muted);
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin: 0;
        white-space: nowrap;
        padding-left: 0.1rem;
    }
    .ob-filter-sep {
        width: 1px;
        height: 34px;
        background: var(--ob-border);
        flex: 0 0 1px;
        align-self: flex-end;
        margin-bottom: 1px;
    }
    .ob-filter-actions {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        flex: 0 0 auto;
    }

    .ob-input-icon { position: relative; }
    .ob-input-icon > svg {
        position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
        width: 14px; height: 14px; color: var(--ob-text-muted); pointer-events: none;
    }
    .ob-input-icon .form-control { padding-left: 2.1rem; }

    .ob-toolbar .form-control,
    .ob-toolbar .form-select { border-radius: 0.5rem; }
    .ob-toolbar .form-control:focus,
    .ob-toolbar .form-select:focus {
        border-color: var(--ob-primary);
        box-shadow: 0 0 0 3px var(--ob-primary-softer);
    }
    /* .ob-btn-clear defined globally in vuexy/css/orthobrain-overrides.css */

    /* ── Active-filter chips ────────────────────────────────── */
    .ob-active-filters { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.75rem; }
    .ob-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.25rem 0.55rem 0.25rem 0.7rem;
        background: var(--ob-surface-1); border: 1px solid var(--ob-border);
        border-radius: 999px; font-size: 0.76rem; font-weight: 600; color: var(--ob-text);
    }
    .ob-chip .ob-chip-label { color: var(--ob-text-muted); font-weight: 500; margin-right: 0.15rem; }
    .ob-chip a {
        display: inline-flex; align-items: center; justify-content: center;
        width: 16px; height: 16px; border-radius: 50%;
        background: var(--ob-surface-2); color: var(--ob-text-muted);
        transition: background 120ms ease, color 120ms ease;
    }
    .ob-chip a:hover { background: #ea5455; color: #fff; }
    .ob-chip a svg { width: 10px; height: 10px; }

    /* ── Status pills ───────────────────────────────────────── */
    .cs-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .cs-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .cs-status--success   { background: rgba(var(--bs-success-rgb), .14);   color: var(--bs-success); }
    .cs-status--warning   { background: rgba(var(--bs-warning-rgb), .14);   color: var(--bs-warning); }
    .cs-status--danger    { background: rgba(var(--bs-danger-rgb), .14);    color: var(--bs-danger); }
    .cs-status--info      { background: rgba(var(--bs-info-rgb), .14);      color: var(--bs-info); }
    .cs-status--secondary { background: rgba(var(--bs-secondary-rgb), .14); color: var(--bs-secondary); }

    /* ── Pagination footer ──────────────────────────────────── */
    .ob-foot {
        padding: 0.85rem 1.25rem;
        display: flex; align-items: center; justify-content: flex-end;
        border-top: 1px solid var(--ob-border);
        background: var(--ob-surface-alt);
    }
    .ob-foot .pagination { margin: 0; }
</style>
@endpush

@section('content')
<section id="admin-cases-list">

  {{-- ── KPI strip ───────────────────────────────────────────── --}}
  @include('admin._partials.stat_cards', [
      'cards' => [
          ['label' => 'Total',     'value' => $totalCount ?? 0,                            'icon' => 'folder',       'tone' => 'primary'],
          ['label' => 'In Review', 'value' => (int) ($statusCounts['IN_REVIEW'] ?? 0),     'icon' => 'clock',        'tone' => 'warning'],
          ['label' => 'Approved',  'value' => (int) ($statusCounts['APPROVED']  ?? 0),     'icon' => 'check-circle', 'tone' => 'success'],
      ],
  ])

  <div class="ob-list-card">

    {{-- ── Card header ─────────────────────────────────────── --}}
    <div class="ob-card-head">
      <h4 class="ob-card-head-title">{{ $heading }}</h4>
      <span class="ob-card-head-meta">{{ $cases->total() }} {{ Str::plural('case', $cases->total()) }}</span>
    </div>

    {{-- ── Status tabs ─────────────────────────────────────── --}}
    <ul class="ob-tabs">
      <li>
        <a href="{{ $tabHref(null) }}" class="ob-tab {{ !$statusFilter ? 'is-active' : '' }}">
          All <span class="ob-tab-count">{{ $totalCount }}</span>
        </a>
      </li>
      @foreach($statusOptions as $s)
        <li>
          <a href="{{ $tabHref($s) }}" class="ob-tab {{ $statusFilter === $s ? 'is-active' : '' }}">
            {{ $statusLabels[$s] ?? $s }}
            <span class="ob-tab-count">{{ (int) ($statusCounts[$s] ?? 0) }}</span>
          </a>
        </li>
      @endforeach
    </ul>

    {{-- ── Toolbar ──────────────────────────────────────────── --}}
    <div class="ob-toolbar">
      <form method="GET" action="{{ route('admin.cases.index') }}" id="adminCasesFilter">
        @if($statusFilter)
          <input type="hidden" name="status" value="{{ $statusFilter }}">
        @endif

        <div class="ob-filter-row">

          {{-- Doctor --}}
          <div class="ob-filter-field">
            <label class="ob-filter-label">Doctor</label>
            <select name="doctor_id" id="adminCasesDoctorSelect" class="form-select" data-placeholder="All doctors">
              <option value="">All doctors</option>
              @if($selectedDoctor)
                <option value="{{ $selectedDoctor->id }}" selected>
                  {{ trim($selectedDoctor->first_name . ' ' . $selectedDoctor->last_name) }}@if($selectedDoctor->practice) — {{ $selectedDoctor->practice->name }}@endif
                </option>
              @endif
            </select>
          </div>

          {{-- Patient --}}
          <div class="ob-filter-field">
            <label class="ob-filter-label">Patient</label>
            <select name="patient_id" id="adminCasesPatientSelect" class="form-select" data-placeholder="All patients">
              <option value="">All patients</option>
              @if($selectedPatient)
                <option value="{{ $selectedPatient->id }}" selected>
                  {{ trim($selectedPatient->first_name . ' ' . $selectedPatient->last_name) }}@if($selectedPatient->chart_id) — {{ $selectedPatient->chart_id }}@endif
                </option>
              @endif
            </select>
          </div>

          {{-- Practice --}}
          <div class="ob-filter-field">
            <label class="ob-filter-label">Practice</label>
            <select name="practice_id" id="adminCasesPracticeSelect" class="form-select" data-placeholder="All practices">
              <option value="">All practices</option>
              @if($selectedPractice)
                <option value="{{ $selectedPractice->id }}" selected>{{ $selectedPractice->name }}</option>
              @endif
            </select>
          </div>

          {{-- Case ID --}}
          <div class="ob-filter-field ob-filter-field--sm">
            <label class="ob-filter-label">Case ID</label>
            <div class="ob-input-icon">
              <i data-feather="hash"></i>
              <input type="text"
                     name="case_id"
                     id="adminCaseIdInput"
                     class="form-control"
                     placeholder="e.g. 1234"
                     value="{{ $caseIdFilter ?? '' }}"
                     inputmode="numeric"
                     pattern="[0-9]*"
                     autocomplete="off">
            </div>
          </div>

          {{-- Separator --}}
          <div class="ob-filter-sep" aria-hidden="true"></div>

          {{-- Sort + Clear --}}
          <div class="ob-filter-actions">
            <div class="ob-filter-field ob-filter-field--sm">
              <label class="ob-filter-label">Sort</label>
              <select name="order" class="form-select" aria-label="Sort order" onchange="this.form.submit()">
                <option value="newest" @selected(request('order', 'newest') === 'newest')>Newest first</option>
                <option value="oldest" @selected(request('order') === 'oldest')>Oldest first</option>
              </select>
            </div>
            <a href="{{ route('admin.cases.index') }}" class="ob-btn-clear">
              <i data-feather="x"></i> Clear
            </a>
          </div>

        </div>
      </form>

      {{-- Active filter chips --}}
      @if($anyFilter)
        <div class="ob-active-filters">
          @if($selectedDoctor)
            <span class="ob-chip">
              <span class="ob-chip-label">Doctor:</span>
              Dr. {{ trim($selectedDoctor->first_name . ' ' . $selectedDoctor->last_name) }}
              <a href="{{ $chipHref('doctor_id') }}" title="Remove"><i data-feather="x"></i></a>
            </span>
          @endif
          @if($selectedPatient)
            <span class="ob-chip">
              <span class="ob-chip-label">Patient:</span>
              {{ trim($selectedPatient->first_name . ' ' . $selectedPatient->last_name) }}
              <a href="{{ $chipHref('patient_id') }}" title="Remove"><i data-feather="x"></i></a>
            </span>
          @endif
          @if($selectedPractice)
            <span class="ob-chip">
              <span class="ob-chip-label">Practice:</span>
              {{ $selectedPractice->name }}
              <a href="{{ $chipHref('practice_id') }}" title="Remove"><i data-feather="x"></i></a>
            </span>
          @endif
          @if($caseIdFilter)
            <span class="ob-chip">
              <span class="ob-chip-label">Case ID:</span>
              #{{ $caseIdFilter }}
              <a href="{{ $chipHref('case_id') }}" title="Remove"><i data-feather="x"></i></a>
            </span>
          @endif
        </div>
      @endif
    </div>

    {{-- ── Table ────────────────────────────────────────────── --}}
    <div class="table-responsive">
      <table class="table ob-admin-table mb-0 align-middle">
        <thead>
          <tr>
            <th>@include('admin._partials.sort_th', ['label' => 'Case ID', 'key' => 'id', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Patient Name', 'key' => 'patient', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Doctor', 'key' => 'doctor', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Practice', 'key' => 'practice', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>Status</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Created', 'key' => 'created_at', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Submitted', 'key' => 'submitted_at', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cases as $case)
            <tr data-row-href="{{ route('admin.cases.edit', $case->id) }}">
              <td><span class="fw-bolder">#{{ $case->id }}</span></td>
              <td>
                @if($case->patient)
                  {{ trim($case->patient->first_name . ' ' . $case->patient->last_name) ?: '—' }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>
                @if($case->doctor)
                  {{ trim($case->doctor->first_name . ' ' . $case->doctor->last_name) }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ $case->doctor?->practice?->name ?? '—' }}</td>
              <td>
                <span class="cs-status cs-status--{{ $statusTone[$case->status] ?? 'secondary' }}">
                  {{ $statusLabels[$case->status] ?? $case->status }}
                </span>
              </td>
              <td>{{ $case->created_at?->format('Y-m-d H:i') }}</td>
              <td>{{ $case->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="text-end">
                <div class="ob-row-actions">
                  <a href="{{ route('admin.cases.edit', $case->id) }}"
                     class="ob-icon-btn ob-icon-btn--view"
                     title="View case">
                    <i data-feather="eye"></i>
                  </a>
                  <a href="{{ route('admin.cases.edit', $case->id) }}"
                     class="ob-icon-btn ob-icon-btn--edit"
                     title="Edit case">
                    <i data-feather="edit-2"></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                No cases match the current filters.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ── Pagination ───────────────────────────────────────── --}}
    @if($cases->hasPages())
      <div class="ob-foot">{{ $cases->links() }}</div>
    @endif

  </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    if (typeof $.fn.select2 !== 'function') return;

    const form = document.getElementById('adminCasesFilter');

    function initAjaxSelect2($el, placeholder, url) {
        if (!$el.length) return;
        $el.select2({
            placeholder,
            allowClear: true,
            width: '100%',
            ajax: {
                url,
                dataType: 'json',
                delay: 200,
                data: (params) => ({ q: params.term || '' }),
                processResults: (data) => ({ results: data }),
                cache: true,
            },
        });
        $el.on('select2:open', function () {
            setTimeout(function () {
                const field = document.querySelector('.select2-container--open .select2-search__field');
                if (field) field.focus();
            }, 0);
        });
        $el.on('change', function () { form.submit(); });
    }

    initAjaxSelect2($('#adminCasesDoctorSelect'),   'All doctors',   @json(route('admin.ajax.doctors-search')));
    initAjaxSelect2($('#adminCasesPatientSelect'),  'All patients',  @json(route('admin.ajax.patients-search')));
    initAjaxSelect2($('#adminCasesPracticeSelect'), 'All practices', @json(route('admin.ajax.practices-search')));

    // Case ID: auto-submit after 600 ms idle, only for valid numeric input
    const caseIdInput = document.getElementById('adminCaseIdInput');
    if (caseIdInput) {
        let timer;
        caseIdInput.addEventListener('input', function () {
            clearTimeout(timer);
            const val = this.value.trim();
            if (val === '' || /^\d+$/.test(val)) {
                timer = setTimeout(() => form.submit(), 600);
            }
        });
    }
})();
</script>
@endpush
