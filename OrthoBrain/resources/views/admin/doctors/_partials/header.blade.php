@php
    $statusMap = [
        'PENDING'   => ['label' => 'Pending',   'class' => 'warning'],
        'APPROVED'  => ['label' => 'Approved',  'class' => 'success'],
        'REJECTED'  => ['label' => 'Rejected',  'class' => 'danger'],
        'SUSPENDED' => ['label' => 'Suspended', 'class' => 'secondary'],
    ];
    $current = $statusMap[$doctor->approval_status] ?? ['label' => $doctor->approval_status, 'class' => 'secondary'];
    $initials = strtoupper(substr($doctor->first_name, 0, 1) . substr($doctor->last_name, 0, 1));
@endphp
<div class="card ob-doctor-header">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="ob-avatar ob-avatar-lg">{{ $initials }}</span>

            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-1">
                    <h2 class="mb-0 me-1">Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}</h2>
                    <span class="badge rounded-pill badge-light-{{ $current['class'] }}">
                        {{ strtoupper($current['label']) }}
                    </span>
                </div>
                <div class="mt-25 text-muted">
                    <span class="me-1"><i data-feather="mail" class="me-25"></i>{{ $doctor->doctor_contact_email }}</span>
                    @if ($doctor->doctor_cell_phone)
                        <span class="me-1"><i data-feather="phone" class="me-25"></i>{{ $doctor->doctor_cell_phone }}</span>
                    @endif
                    @if ($doctor->practice)
                        <span><i data-feather="briefcase" class="me-25"></i>{{ $doctor->practice->name }}</span>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-50 flex-wrap ob-doctor-actions">
                <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i data-feather="arrow-left" class="me-25"></i> Back
                </a>

                @if ($doctor->approval_status === 'PENDING')
                    <form method="POST" action="{{ route('admin.doctors.approve', $doctor) }}"
                          class="d-inline js-confirm-form"
                          data-confirm-title="Approve Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}?"
                          data-confirm-text="The doctor will be notified and gain access to their account."
                          data-confirm-btn="Approve"
                          data-confirm-class="btn-success">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i data-feather="check" class="me-25"></i> Approve
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger btn-sm"
                            data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i data-feather="x" class="me-25"></i> Reject
                    </button>
                @elseif ($doctor->approval_status === 'APPROVED')
                    <button type="button" class="btn btn-warning btn-sm"
                            data-bs-toggle="modal" data-bs-target="#suspendModal">
                        <i data-feather="pause" class="me-25"></i> Suspend
                    </button>
                @elseif ($doctor->approval_status === 'SUSPENDED')
                    <form method="POST" action="{{ route('admin.doctors.reactivate', $doctor) }}"
                          class="d-inline js-confirm-form"
                          data-confirm-title="Reactivate Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}?"
                          data-confirm-text="The doctor will regain access to their account."
                          data-confirm-btn="Reactivate"
                          data-confirm-class="btn-success">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i data-feather="play" class="me-25"></i> Reactivate
                        </button>
                    </form>
                @elseif ($doctor->approval_status === 'REJECTED')
                    <form method="POST" action="{{ route('admin.doctors.approve', $doctor) }}"
                          class="d-inline js-confirm-form"
                          data-confirm-title="Approve this doctor?"
                          data-confirm-text="This will overturn the previous rejection and grant access."
                          data-confirm-btn="Approve"
                          data-confirm-class="btn-success">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i data-feather="check" class="me-25"></i> Approve
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
