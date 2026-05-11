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
      'REJECTED'  => 'Unapproved',
      'ACTIVE'    => 'Active',
      default     => $status,
    };
  };
@endphp

@push('styles')
<style>
  #cases-list .ob-input-icon { position: relative; }
  #cases-list .ob-input-icon > svg {
    position: absolute;
    left: 0.75rem; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px;
    color: #9a9aab;
    pointer-events: none;
  }
  #cases-list .ob-input-icon .form-control { padding-left: 2.35rem; border-radius: 0.5rem; }
  #cases-list .ob-input-icon .form-control:focus {
    border-color: var(--ob-primary, #00bad1);
    box-shadow: 0 0 0 3px var(--ob-primary-softer, rgba(0, 186, 209, 0.18));
  }
</style>
@endpush

@section('content')
@php
  $statusBase = $searchTerm !== '' ? ['search' => $searchTerm] : [];
@endphp
<section id="cases-list">
  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title mb-0">Cases</h4>
      <a href="{{ route('doctor.cases.create') }}" class="btn btn-primary">
        <i data-feather="plus" class="me-25"></i> New Case
      </a>
    </div>

    <div class="card-body border-bottom py-1">
      <form method="GET" action="{{ route('doctor.cases.index') }}" id="casesSearchForm" class="row g-2 align-items-center">
        @if($activeStatus)
          <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif
        @if($staleOnly)
          <input type="hidden" name="stale" value="1">
        @endif
        @if(request('order'))
          <input type="hidden" name="order" value="{{ request('order') }}">
        @endif
        @if(request('sort'))
          <input type="hidden" name="sort" value="{{ request('sort') }}">
          <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
        @endif
        <div class="col-md-4">
          <div class="ob-input-icon">
            <i data-feather="search"></i>
            <input type="text" name="search" placeholder="Search by Case ID or Patient Name"
                   value="{{ $searchTerm }}" class="form-control" autocomplete="off">
          </div>
        </div>
        @if($searchTerm !== '')
          <div class="col-md-2">
            <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}"
               class="ob-btn-clear w-100">
              <i data-feather="x"></i> Clear
            </a>
          </div>
        @endif
      </form>
    </div>

    <div class="card-body border-bottom py-1">
      <div class="d-flex flex-wrap align-items-center gap-50">
        <a href="{{ route('doctor.cases.index', $statusBase) }}"
           class="btn btn-sm {{ $activeStatus === null ? 'btn-primary' : 'btn-outline-secondary' }}">
          All
        </a>
        <a href="{{ route('doctor.cases.index', array_merge($statusBase, ['status' => 'ACTIVE'])) }}"
           class="btn btn-sm {{ $activeStatus === 'ACTIVE' ? 'btn-primary' : 'btn-outline-secondary' }}">
          Active
        </a>
        @foreach($statuses as $s)
          <a href="{{ route('doctor.cases.index', array_merge($statusBase, ['status' => $s])) }}"
             class="btn btn-sm {{ $activeStatus === $s && ! $staleOnly ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $statusLabel($s) }}
          </a>
        @endforeach
        @if($staleOnly)
          <a href="{{ route('doctor.cases.index', array_merge($statusBase, ['status' => 'DRAFT', 'stale' => 1])) }}"
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
              · <a href="{{ route('doctor.cases.index', array_merge($statusBase, ['status' => 'DRAFT'])) }}">show all drafts</a>
            @endif
          </span>
        @endif

        @php
          $currentOrder = request('order') === 'oldest' ? 'oldest' : 'newest';
          $orderBase    = $statusBase;
          if ($activeStatus) { $orderBase['status'] = $activeStatus; }
          if ($staleOnly)    { $orderBase['stale']  = 1; }
        @endphp
        <div class="ms-auto d-flex flex-wrap align-items-center gap-50">
          <span class="text-muted small me-25">Sort:</span>
          <a href="{{ route('doctor.cases.index', array_merge($orderBase, ['order' => 'newest'])) }}"
             class="btn btn-sm {{ $currentOrder === 'newest' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Newest first
          </a>
          <a href="{{ route('doctor.cases.index', array_merge($orderBase, ['order' => 'oldest'])) }}"
             class="btn btn-sm {{ $currentOrder === 'oldest' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Oldest first
          </a>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th>@include('admin._partials.sort_th', ['label' => 'Case ID', 'key' => 'id', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Patient Name', 'key' => 'patient', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>Status</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Created', 'key' => 'created_at', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th>@include('admin._partials.sort_th', ['label' => 'Submitted', 'key' => 'submitted_at', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cases as $case)
            <tr data-row-href="{{ route('doctor.cases.edit', $case->id) }}" style="cursor:pointer;">
              <td><span class="fw-bolder">#{{ $case->id }}</span></td>
              <td>
                @if($case->patient)
                  {{ trim($case->patient->first_name . ' ' . $case->patient->last_name) ?: '—' }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td><span class="{{ $statusBadge($case->status) }}">{{ $statusLabel($case->status) }}</span></td>
              <td>{{ $case->created_at?->format('Y-m-d H:i') }}</td>
              <td>{{ $case->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="text-end">
                <div class="ob-row-actions">
                  @if($case->status === 'DRAFT')
                    <a href="{{ route('doctor.cases.edit', $case->id) }}"
                       class="ob-icon-btn ob-icon-btn--edit"
                       title="Continue editing">
                      <i data-feather="edit-2"></i>
                    </a>
                  @else
                    <a href="{{ route('doctor.cases.edit', $case->id) }}"
                       class="ob-icon-btn ob-icon-btn--view"
                       title="View case">
                      <i data-feather="eye"></i>
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-2">
                @if($searchTerm !== '')
                  No cases match <strong>"{{ $searchTerm }}"</strong>.
                  <a href="{{ route('doctor.cases.index') }}">Clear filters</a>.
                @elseif($staleOnly)
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
document.addEventListener('click', function (e) {
  var tr = e.target.closest('tr[data-row-href]')
  if (!tr) return
  if (e.target.closest('a, button, form, input, select, textarea, label, [data-no-row-click]')) return
  if (window.getSelection && String(window.getSelection())) return
  var href = tr.getAttribute('data-row-href')
  if (!href) return
  if (e.ctrlKey || e.metaKey) {
    window.open(href, '_blank', 'noopener')
  } else {
    window.location.href = href
  }
})
document.addEventListener('auxclick', function (e) {
  if (e.button !== 1) return
  var tr = e.target.closest('tr[data-row-href]')
  if (!tr) return
  if (e.target.closest('a, button, form, input, select, textarea, label, [data-no-row-click]')) return
  var href = tr.getAttribute('data-row-href')
  if (!href) return
  e.preventDefault()
  window.open(href, '_blank', 'noopener')
})

document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('casesSearchForm')
  if (form) {
    var input = form.querySelector('input[name="search"]')
    if (input) {
      var timer
      input.addEventListener('input', function () {
        clearTimeout(timer)
        timer = setTimeout(function () { form.submit() }, 450)
      })
      // Place cursor at end so the search keeps feeling sticky after reload.
      if (input.value) {
        var len = input.value.length
        input.focus()
        try { input.setSelectionRange(len, len) } catch (e) {}
      }
    }
  }
})

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
