@php
    $links = \Illuminate\Support\Facades\DB::table('doctor_practice as dp')
        ->join('practices as p', 'p.id', '=', 'dp.practice_id')
        ->leftJoin('admins as a', 'a.id', '=', 'dp.approved_by_admin_id')
        ->where('dp.doctor_id', $doctor->id)
        ->whereNull('p.deleted_at')
        ->select([
            'dp.id as link_id',
            'dp.approval_status',
            'dp.is_primary',
            'dp.requested_at',
            'dp.approved_at',
            'dp.rejected_at',
            'dp.rejection_reason',
            'p.id as practice_id',
            'p.name as practice_name',
            'p.website',
            'p.phone_country_code',
            'p.phone_number',
            'p.owner_id',
            'a.first_name as admin_first_name',
            'a.last_name as admin_last_name',
        ])
        ->orderByDesc('dp.is_primary')
        ->orderBy('dp.created_at')
        ->get();

    $pendingCount = $links->where('approval_status', 'PENDING')->count();
@endphp

@push('styles')
<style>
.ob-practice-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ob-practice-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid var(--bs-border-color);
    border-left-width: 4px;
    transition: box-shadow 0.15s ease;
}

.ob-practice-item:hover {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}

.ob-practice-item--approved  { border-left-color: #28c76f; }
.ob-practice-item--pending   { border-left-color: #ff9f43; background-color: rgba(255, 159, 67, 0.06); }
.ob-practice-item--rejected  { border-left-color: #ea5455; }
.ob-practice-item--cancelled,
.ob-practice-item--left      { border-left-color: var(--bs-secondary-bg, #b2b9c4); }

.ob-practice-info {
    flex: 1 1 0;
    min-width: 0;
}

.ob-practice-name {
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 1.3;
}

.ob-practice-badges {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.3rem;
    margin-bottom: 0.25rem;
}

.ob-practice-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.3rem;
}

.ob-practice-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.775rem;
}

.ob-practice-meta-item i[data-feather] {
    width: 11px;
    height: 11px;
    flex-shrink: 0;
}

/* Right side: status badge + optional sub-text + action buttons */
.ob-practice-right {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.ob-practice-status {
    text-align: end;
    white-space: nowrap;
}
</style>
@endpush

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h5 class="card-title mb-0">
                <i data-feather="briefcase" class="me-50"></i>
                Practices ({{ $links->count() }})
            </h5>
            @if($pendingCount > 0)
                <span class="badge rounded-pill badge-light-warning d-inline-flex align-items-center gap-1">
                    <i data-feather="clock" style="width:10px;height:10px;"></i>
                    {{ $pendingCount }} pending review
                </span>
            @endif
        </div>
        <p class="text-muted mb-3" style="font-size:0.82rem;">
            Each practice this doctor is linked to. Approve or reject each one individually.
        </p>

        @if($links->isEmpty())
            <div class="text-center text-muted py-4">
                <i data-feather="briefcase" style="width:36px;height:36px;opacity:0.35;"></i>
                <div class="mt-2" style="font-size:0.88rem;">No practice links yet.</div>
            </div>
        @else
            <div class="ob-practice-list">
                @foreach($links as $link)
                    @php $statusClass = strtolower($link->approval_status); @endphp

                    <div class="ob-practice-item ob-practice-item--{{ $statusClass }}">

                        {{-- Left: Practice name, type badges, contact meta --}}
                        <div class="ob-practice-info">
                            <div class="ob-practice-badges">
                                <span class="ob-practice-name">{{ $link->practice_name }}</span>
                                @if($link->is_primary)
                                    <span class="badge bg-primary" style="font-size:0.65rem;">Primary</span>
                                @endif
                                @if($link->owner_id == $doctor->id)
                                    <span class="badge bg-light-info" style="font-size:0.65rem;"
                                          title="Doctor created this practice at registration">Owner</span>
                                @else
                                    <span class="badge bg-light-secondary" style="font-size:0.65rem;"
                                          title="Doctor claimed an existing practice">Claim</span>
                                @endif
                            </div>
                            @if($link->website || $link->phone_number)
                                <div class="ob-practice-meta">
                                    @if($link->website)
                                        <span class="ob-practice-meta-item text-muted">
                                            <i data-feather="globe"></i>
                                            <span class="text-truncate" style="max-width:200px;">{{ $link->website }}</span>
                                        </span>
                                    @endif
                                    @if($link->phone_number)
                                        <span class="ob-practice-meta-item text-muted">
                                            <i data-feather="phone"></i>
                                            {{ $link->phone_country_code ? '+' . $link->phone_country_code . ' ' : '' }}{{ $link->phone_number }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Right: status badge + sub-info + action buttons --}}
                        <div class="ob-practice-right">

                            {{-- Status badge + sub-line --}}
                            <div class="ob-practice-status">
                                @php
                                    $badge = match ($link->approval_status) {
                                        'APPROVED'  => '<span class="badge rounded-pill badge-light-success">APPROVED</span>',
                                        'PENDING'   => '<span class="badge rounded-pill badge-light-warning">PENDING</span>',
                                        'REJECTED'  => '<span class="badge rounded-pill badge-light-danger">REJECTED</span>',
                                        'CANCELLED' => '<span class="badge rounded-pill badge-light-secondary">CANCELLED</span>',
                                        'LEFT'      => '<span class="badge rounded-pill badge-light-secondary">LEFT</span>',
                                        default     => '<span class="badge rounded-pill badge-light-secondary">' . e($link->approval_status) . '</span>',
                                    };
                                @endphp
                                {!! $badge !!}

                                @if($link->approval_status === 'APPROVED')
                                    @if($link->admin_first_name)
                                        <div class="text-muted mt-25" style="font-size:0.75rem;">
                                            by {{ $link->admin_first_name }} {{ $link->admin_last_name }}
                                            @if($link->approved_at)
                                                &middot; {{ \Carbon\Carbon::parse($link->approved_at)->diffForHumans() }}
                                            @endif
                                        </div>
                                    @endif
                                @elseif($link->approval_status === 'REJECTED')
                                    @if($link->rejection_reason)
                                        <div class="text-muted mt-25" style="font-size:0.75rem;"
                                             title="{{ $link->rejection_reason }}">
                                            "{{ \Illuminate\Support\Str::limit($link->rejection_reason, 40) }}"
                                        </div>
                                    @endif
                                @elseif($link->approval_status === 'PENDING' && $link->requested_at)
                                    <div class="text-muted mt-25" style="font-size:0.75rem;">
                                        {{ \Carbon\Carbon::parse($link->requested_at)->diffForHumans() }}
                                    </div>
                                @endif
                            </div>

                            {{-- Action buttons (PENDING only) --}}
                            @if($link->approval_status === 'PENDING')
                                <form method="POST"
                                      action="{{ route('admin.doctors.practices.approve', [$doctor, $link->link_id]) }}"
                                      class="m-0">
                                    @csrf
                                    <button class="btn btn-sm btn-success" type="submit" title="Approve">
                                        <i data-feather="check" style="width:13px;height:13px;"></i>
                                        Approve
                                    </button>
                                </form>

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectPracticeModal-{{ $link->link_id }}"
                                        title="Reject">
                                    <i data-feather="x" style="width:13px;height:13px;"></i>
                                    Reject
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Reject modal --}}
                    @if($link->approval_status === 'PENDING')
                        <div class="modal fade" id="rejectPracticeModal-{{ $link->link_id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST"
                                      action="{{ route('admin.doctors.practices.reject', [$doctor, $link->link_id]) }}"
                                      class="modal-content">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject practice request</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-muted mb-3" style="font-size:0.88rem;">
                                            Reject <strong>{{ $doctor->first_name }} {{ $doctor->last_name }}</strong>'s
                                            request to link with <strong>{{ $link->practice_name }}</strong>?
                                            The doctor will be notified with the reason below.
                                        </p>
                                        <label class="form-label fw-semibold">
                                            Reason <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="rejection_reason"
                                                  class="form-control"
                                                  rows="3"
                                                  required
                                                  maxlength="500"
                                                  placeholder="Why is this being rejected?"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i data-feather="x-circle" style="width:14px;height:14px;" class="me-50"></i>
                                            Confirm Reject
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
