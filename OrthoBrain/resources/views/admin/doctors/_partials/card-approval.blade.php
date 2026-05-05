@php
    $statusMap = [
        'PENDING'   => ['label' => 'Pending',   'class' => 'warning',   'icon' => 'clock'],
        'APPROVED'  => ['label' => 'Approved',  'class' => 'success',   'icon' => 'check-circle'],
        'REJECTED'  => ['label' => 'Rejected',  'class' => 'danger',    'icon' => 'x-circle'],
        'SUSPENDED' => ['label' => 'Suspended', 'class' => 'secondary', 'icon' => 'pause-circle'],
    ];
    $current = $statusMap[$doctor->approval_status] ?? ['label' => $doctor->approval_status, 'class' => 'secondary', 'icon' => 'help-circle'];
    $borderClass = match ($doctor->approval_status) {
        'PENDING'   => 'border-warning',
        'APPROVED'  => 'border-success',
        'REJECTED'  => 'border-danger',
        'SUSPENDED' => 'border-secondary',
        default     => '',
    };
@endphp
<div class="card {{ $borderClass }}" style="border-top-width: 3px;">
    <div class="card-header border-bottom">
        <h4 class="card-title mb-0">
            <i data-feather="{{ $current['icon'] }}" class="me-50"></i> Approval
        </h4>
    </div>
    <div class="card-body pt-1">
        <div class="mb-1">
            <label class="form-label">Current Status</label>
            <div>
                <span class="badge rounded-pill badge-light-{{ $current['class'] }}">{{ strtoupper($current['label']) }}</span>
            </div>
        </div>

        @if ($doctor->approved_at)
            <div class="mb-1">
                <label class="form-label">Approved At</label>
                <div class="form-control bg-light-secondary" style="min-height: 38px;">
                    {{ $doctor->approved_at->toDayDateTimeString() }}
                </div>
            </div>
        @endif

        @if ($doctor->approverAdmin)
            <div class="mb-1">
                <label class="form-label">Reviewed By</label>
                <div class="form-control bg-light-secondary" style="min-height: 38px;">
                    {{ $doctor->approverAdmin->first_name }} {{ $doctor->approverAdmin->last_name }}
                </div>
            </div>
        @endif

        @if ($doctor->rejection_reason)
            <div class="mb-1">
                <label class="form-label text-danger">Rejection Reason</label>
                <div class="form-control bg-light-danger" style="min-height: 38px; white-space: pre-wrap;">
                    {{ $doctor->rejection_reason }}
                </div>
            </div>
        @endif
    </div>
</div>
