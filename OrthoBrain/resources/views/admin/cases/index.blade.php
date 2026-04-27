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

  $doctorName = null;
  if ($doctorFilter) {
    $selectedDoctor = $doctors->firstWhere('id', (int) $doctorFilter);
    if ($selectedDoctor) {
      $doctorName = trim($selectedDoctor->first_name . ' ' . $selectedDoctor->last_name);
    }
  }

  $heading = $doctorName ? "{$statusLabel} — Dr. {$doctorName}" : $statusLabel;
@endphp

@section('content')
<section id="admin-cases-list">
  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title mb-0">{{ $heading }}</h4>
      <span class="text-muted small">{{ $cases->total() }} total</span>
    </div>

    <div class="card-body py-1">
      <form method="GET" action="{{ route('admin.cases.index') }}" id="adminCasesFilter" class="row g-1 py-1">
        <div class="col-md-3">
          <select name="status" class="form-select js-searchable" data-placeholder="All statuses" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach($statusOptions as $s)
              <option value="{{ $s }}" @selected($statusFilter === $s)>{{ $s }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5">
          <select name="doctor_id" class="form-select js-searchable" data-placeholder="All doctors" onchange="this.form.submit()">
            <option value="">All doctors</option>
            @foreach($doctors as $doc)
              <option value="{{ $doc->id }}" @selected((string) $doctorFilter === (string) $doc->id)>
                {{ trim($doc->first_name . ' ' . $doc->last_name) }}
                @if($doc->practice) — {{ $doc->practice->name }} @endif
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <a href="{{ route('admin.cases.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table ob-admin-table mb-0 align-middle">
        <thead>
          <tr>
            <th>Case ID</th>
            <th>Code</th>
            <th>Doctor</th>
            <th>Practice</th>
            <th>Status</th>
            <th>Created</th>
            <th>Submitted</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cases as $case)
            <tr>
              <td><span class="fw-bolder">#{{ $case->id }}</span></td>
              <td>{{ $case->case_code ?? '—' }}</td>
              <td>
                @if($case->doctor)
                  {{ trim($case->doctor->first_name . ' ' . $case->doctor->last_name) }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ $case->doctor?->practice?->name ?? '—' }}</td>
              <td>
                <span class="ob-status ob-status--{{ $statusTone[$case->status] ?? 'secondary' }}">
                  {{ $case->status }}
                </span>
              </td>
              <td>{{ $case->created_at?->format('Y-m-d H:i') }}</td>
              <td>{{ $case->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="text-end">
                <div class="ob-row-actions">
                  <a href="{{ route('admin.cases.edit', $case->id) }}"
                     class="ob-icon-btn"
                     title="View case">
                    <i data-feather="eye"></i>
                  </a>
                  <a href="{{ route('admin.cases.edit', $case->id) }}"
                     class="ob-icon-btn"
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
