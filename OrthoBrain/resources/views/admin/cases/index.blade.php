@extends('layouts.admin')

@section('title', 'Cases')
@section('page_title', 'Cases')

@php
  $statusBadge = function ($status) {
    return match ($status) {
      'DRAFT'     => 'badge rounded-pill badge-light-secondary',
      'SUBMITTED' => 'badge rounded-pill badge-light-info',
      'IN_REVIEW' => 'badge rounded-pill badge-light-warning',
      'APPROVED'  => 'badge rounded-pill badge-light-success',
      'REJECTED'  => 'badge rounded-pill badge-light-danger',
      default     => 'badge rounded-pill badge-light-secondary',
    };
  };
@endphp

@section('content')
<section id="admin-cases-list">
  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title mb-0">All Cases</h4>
      <span class="text-muted small">{{ $cases->total() }} total</span>
    </div>

    <div class="card-body py-1">
      <form method="GET" action="{{ route('admin.cases.index') }}" id="adminCasesFilter" class="row g-1 py-1">
        <div class="col-md-3">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach($statusOptions as $s)
              <option value="{{ $s }}" @selected($statusFilter === $s)>{{ $s }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5">
          <select name="doctor_id" class="form-select" onchange="this.form.submit()">
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
      <table class="table table-hover mb-0 align-middle">
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
              <td><span class="{{ $statusBadge($case->status) }}">{{ $case->status }}</span></td>
              <td>{{ $case->created_at?->format('Y-m-d H:i') }}</td>
              <td>{{ $case->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="text-end">
                <a href="{{ route('admin.cases.edit', $case->id) }}"
                   class="btn btn-icon btn-sm btn-outline-success"
                   title="View case">
                  <i data-feather="eye"></i>
                </a>
                <a href="{{ route('admin.cases.edit', $case->id) }}"
                   class="btn btn-icon btn-sm btn-outline-primary"
                   title="Edit case">
                  <i data-feather="edit-2"></i>
                </a>
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
