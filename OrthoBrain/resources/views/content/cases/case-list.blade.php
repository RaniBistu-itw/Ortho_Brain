@extends('layouts.app')

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

  $statusLabel = function ($status) {
    return match ($status) {
      'DRAFT'     => 'Draft',
      'SUBMITTED' => 'Submitted',
      'IN_REVIEW' => 'In Review',
      'APPROVED'  => 'Approved',
      'REJECTED'  => 'Rejected',
      'ACTIVE'    => 'Active',
      default     => $status,
    };
  };
@endphp

@section('content')
<section id="cases-list">
  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title mb-0">Cases</h4>
      <a href="{{ route('doctor.cases.create') }}" class="btn btn-primary">
        <i data-feather="plus" class="me-25"></i> New Case
      </a>
    </div>

    <div class="card-body border-bottom py-1">
      <div class="d-flex flex-wrap align-items-center gap-50">
        <a href="{{ route('doctor.cases.index') }}"
           class="btn btn-sm {{ $activeStatus === null ? 'btn-primary' : 'btn-outline-secondary' }}">
          All
        </a>
        <a href="{{ route('doctor.cases.index', ['status' => 'ACTIVE']) }}"
           class="btn btn-sm {{ $activeStatus === 'ACTIVE' ? 'btn-primary' : 'btn-outline-secondary' }}">
          Active
        </a>
        @foreach($statuses as $s)
          <a href="{{ route('doctor.cases.index', ['status' => $s]) }}"
             class="btn btn-sm {{ $activeStatus === $s && ! $staleOnly ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $statusLabel($s) }}
          </a>
        @endforeach
        @if($staleOnly)
          <a href="{{ route('doctor.cases.index', ['status' => 'DRAFT', 'stale' => 1]) }}"
             class="btn btn-sm btn-warning">
            Stale drafts only
          </a>
        @endif
        @if($activeStatus)
          <span class="text-muted small ms-1">
            Showing
            <strong>
              @if($staleOnly) Stale drafts @else {{ $statusLabel($activeStatus) }} @endif
            </strong>
            ({{ $cases->total() }})
            @if($staleOnly)
              · <a href="{{ route('doctor.cases.index', ['status' => 'DRAFT']) }}">show all drafts</a>
            @endif
          </span>
        @endif
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>Case ID</th>
            <th>Code</th>
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
              <td><span class="{{ $statusBadge($case->status) }}">{{ $case->status }}</span></td>
              <td>{{ $case->created_at?->format('Y-m-d H:i') }}</td>
              <td>{{ $case->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="text-end">
                @if($case->status === 'DRAFT')
                  <a href="{{ route('doctor.cases.edit', $case->id) }}"
                     class="btn btn-icon btn-sm btn-outline-primary"
                     title="Continue editing">
                    <i data-feather="edit-2"></i>
                  </a>
                @else
                  <a href="{{ route('doctor.cases.edit', $case->id) }}"
                     class="btn btn-icon btn-sm btn-outline-success"
                     title="View case">
                    <i data-feather="eye"></i>
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-2">
                @if($staleOnly)
                  No stale drafts. <a href="{{ route('doctor.cases.index') }}">Clear filter</a>.
                @elseif($activeStatus)
                  No cases with status <strong>{{ $statusLabel($activeStatus) }}</strong>.
                  <a href="{{ route('doctor.cases.index') }}">Clear filter</a>.
                @else
                  No cases yet. Click <strong>New Case</strong> above to get started.
                @endif
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
document.addEventListener('DOMContentLoaded', function () {
  var flashRaw = sessionStorage.getItem('caseSubmittedFlash')
  if (!flashRaw) return
  sessionStorage.removeItem('caseSubmittedFlash')

  try {
    var flash = JSON.parse(flashRaw)
    if (!flash.message) return
    showSuccessToast(flash.message)
  } catch (err) {
    console.error('Failed to parse flash:', err)
  }
})

function showSuccessToast(message) {
  var toastHtml = '<div class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-4"' +
    ' role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1080;">' +
    '<div class="d-flex">' +
    '<div class="toast-body">' + message + '</div>' +
    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
    '</div></div>'
  var wrapper = document.createElement('div')
  wrapper.innerHTML = toastHtml
  var toastEl = wrapper.firstElementChild
  document.body.appendChild(toastEl)
  var toast = new bootstrap.Toast(toastEl, { delay: 5000 })
  toast.show()
  toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove() })
}
</script>
@endpush
