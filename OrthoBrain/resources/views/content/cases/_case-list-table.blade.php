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

  $statusBase = $searchTerm !== '' ? ['search' => $searchTerm] : [];
@endphp

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
