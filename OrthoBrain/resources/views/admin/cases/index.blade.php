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
@endphp

@section('content')
<style>
    .cs-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .cs-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .cs-status--success   { background: rgba(var(--bs-success-rgb), .14);   color: var(--bs-success); }
    .cs-status--warning   { background: rgba(var(--bs-warning-rgb), .14);   color: var(--bs-warning); }
    .cs-status--danger    { background: rgba(var(--bs-danger-rgb), .14);    color: var(--bs-danger); }
    .cs-status--info      { background: rgba(var(--bs-info-rgb), .14);      color: var(--bs-info); }
    .cs-status--secondary { background: rgba(var(--bs-secondary-rgb), .14); color: var(--bs-secondary); }
</style>
<section id="admin-cases-list">

  {{-- ── KPI strip ───────────────────────────────────────────── --}}
  @include('admin._partials.stat_cards', [
      'cards' => [
          ['label' => 'Total',     'value' => $totalCount ?? 0,                            'icon' => 'folder',       'tone' => 'primary'],
          ['label' => 'In Review', 'value' => (int) ($statusCounts['IN_REVIEW'] ?? 0),     'icon' => 'clock',        'tone' => 'warning'],
          ['label' => 'Approved',  'value' => (int) ($statusCounts['APPROVED']  ?? 0),     'icon' => 'check-circle', 'tone' => 'success'],
      ],
  ])

  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title mb-0">{{ $heading }}</h4>
      <span class="text-muted small">{{ $cases->total() }} total</span>
    </div>

    <div class="card-body py-1">
      <form method="GET" action="{{ route('admin.cases.index') }}" id="adminCasesFilter" class="row g-1 py-1">
        <div class="col-md-2">
          <select name="status" class="form-select js-searchable" data-placeholder="All statuses" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach($statusOptions as $s)
              <option value="{{ $s }}" @selected($statusFilter === $s)>{{ $statusLabels[$s] ?? $s }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="doctor_id" id="adminCasesDoctorSelect" class="form-select" data-placeholder="All doctors">
            <option value="">All doctors</option>
            @if($selectedDoctor)
              <option value="{{ $selectedDoctor->id }}" selected>
                {{ trim($selectedDoctor->first_name . ' ' . $selectedDoctor->last_name) }}@if($selectedDoctor->practice) — {{ $selectedDoctor->practice->name }}@endif
              </option>
            @endif
          </select>
        </div>
        <div class="col-md-3">
          <select name="patient_id" id="adminCasesPatientSelect" class="form-select" data-placeholder="All patients">
            <option value="">All patients</option>
            @if($selectedPatient)
              <option value="{{ $selectedPatient->id }}" selected>
                {{ trim($selectedPatient->first_name . ' ' . $selectedPatient->last_name) }}@if($selectedPatient->chart_id) — {{ $selectedPatient->chart_id }}@endif
              </option>
            @endif
          </select>
        </div>
        <div class="col-md-2">
          <select name="order" class="form-select" aria-label="Sort order" onchange="this.form.submit()">
            <option value="newest" @selected(request('order', 'newest') === 'newest')>Newest first</option>
            <option value="oldest" @selected(request('order') === 'oldest')>Oldest first</option>
          </select>
        </div>
        <div class="col-md-2">
          <a href="{{ route('admin.cases.index') }}" class="ob-btn-clear w-100">
            <i data-feather="x"></i> Clear
          </a>
        </div>
      </form>
    </div>

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
              <td colspan="8" class="text-center text-muted py-2">
                No cases match the current filters.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($cases->hasPages())
      <div class="card-body">{{ $cases->links() }}</div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    if (typeof $.fn.select2 !== 'function') return;

    const $doctor = $('#adminCasesDoctorSelect');
    if ($doctor.length) {
        $doctor.select2({
            placeholder: 'All doctors',
            allowClear: true,
            width: '100%',
            ajax: {
                url: @json(route('admin.ajax.doctors-search')),
                dataType: 'json',
                delay: 200,
                data: (params) => ({ q: params.term || '' }),
                processResults: (data) => ({ results: data }),
                cache: true,
            },
        });
        $doctor.on('select2:open', function () {
            setTimeout(function () {
                const field = document.querySelector('.select2-container--open .select2-search__field');
                if (field) field.focus();
            }, 0);
        });
        $doctor.on('change', function () {
            document.getElementById('adminCasesFilter').submit();
        });
    }

    const $patient = $('#adminCasesPatientSelect');
    if ($patient.length) {
        $patient.select2({
            placeholder: 'All patients',
            allowClear: true,
            width: '100%',
            ajax: {
                url: @json(route('admin.ajax.patients-search')),
                dataType: 'json',
                delay: 200,
                data: (params) => ({ q: params.term || '' }),
                processResults: (data) => ({ results: data }),
                cache: true,
            },
        });
        $patient.on('select2:open', function () {
            setTimeout(function () {
                const field = document.querySelector('.select2-container--open .select2-search__field');
                if (field) field.focus();
            }, 0);
        });
        $patient.on('change', function () {
            document.getElementById('adminCasesFilter').submit();
        });
    }
})();
</script>
@endpush
