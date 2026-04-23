@php
    // Self-contained: pulls the doctor's pivot rows directly so the parent
    // page doesn't need to pass anything extra.
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

    $statusPill = fn ($s) => match ($s) {
        'APPROVED'  => '<span class="badge bg-success">APPROVED</span>',
        'PENDING'   => '<span class="badge bg-warning">PENDING</span>',
        'REJECTED'  => '<span class="badge bg-danger">REJECTED</span>',
        'CANCELLED' => '<span class="badge bg-secondary">CANCELLED</span>',
        'LEFT'      => '<span class="badge bg-secondary">LEFT</span>',
        default     => '<span class="badge bg-light-secondary">' . e($s) . '</span>',
    };

    $pendingCount = $links->where('approval_status', 'PENDING')->count();
@endphp

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <h5 class="card-title mb-0">
                <i data-feather="briefcase" class="me-50"></i>
                Practices ({{ $links->count() }})
            </h5>
            @if($pendingCount > 0)
                <span class="badge bg-light-warning">{{ $pendingCount }} pending review</span>
            @endif
        </div>
        <p class="text-muted" style="font-size:0.85rem;">
            Each practice this doctor is linked to. Approve/reject each one individually.
        </p>

        @if($links->isEmpty())
            <div class="text-center text-muted py-3">
                <i data-feather="inbox" style="width:32px;height:32px;"></i>
                <div class="mt-1">No practice links yet.</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Practice</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($links as $link)
                            <tr>
                                <td>
                                    <strong>{{ $link->practice_name }}</strong>
                                    @if($link->is_primary)
                                        <span class="badge bg-light-primary ms-1">primary</span>
                                    @endif
                                    <small class="text-muted d-block">
                                        @if($link->website)<i data-feather="globe" style="width:12px;height:12px;"></i> {{ $link->website }} @endif
                                        @if($link->phone_number)<span class="ms-2"><i data-feather="phone" style="width:12px;height:12px;"></i> {{ $link->phone_number }}</span>@endif
                                    </small>
                                </td>
                                <td>
                                    @if($link->owner_id == $doctor->id)
                                        <span class="badge bg-light-info" title="Doctor created this practice at registration">Owner</span>
                                    @else
                                        <span class="badge bg-light-secondary" title="Doctor claimed an existing practice">Claim</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $statusPill($link->approval_status) !!}
                                    @if($link->approval_status === 'APPROVED' && $link->admin_first_name)
                                        <small class="text-muted d-block">by {{ $link->admin_first_name }} {{ $link->admin_last_name }}</small>
                                    @elseif($link->approval_status === 'REJECTED' && $link->rejection_reason)
                                        <small class="text-muted d-block" title="{{ $link->rejection_reason }}">{{ \Illuminate\Support\Str::limit($link->rejection_reason, 50) }}</small>
                                    @elseif($link->approval_status === 'PENDING' && $link->requested_at)
                                        <small class="text-muted d-block">Requested {{ \Carbon\Carbon::parse($link->requested_at)->diffForHumans() }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($link->approval_status === 'PENDING')
                                        <form method="POST" action="{{ route('admin.doctors.practices.approve', [$doctor, $link->link_id]) }}" class="d-inline m-0">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">
                                                <i data-feather="check" style="width:14px;height:14px;"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#rejectPracticeModal-{{ $link->link_id }}">
                                            <i data-feather="x" style="width:14px;height:14px;"></i> Reject
                                        </button>

                                        <div class="modal fade" id="rejectPracticeModal-{{ $link->link_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('admin.doctors.practices.reject', [$doctor, $link->link_id]) }}" class="modal-content">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject practice request</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-muted mb-2">
                                                            Reject <strong>{{ $doctor->first_name }} {{ $doctor->last_name }}</strong>'s link to <strong>{{ $link->practice_name }}</strong>?
                                                        </p>
                                                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" required maxlength="500"
                                                                  placeholder="Why is this being rejected? The doctor will see this message."></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Confirm Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
