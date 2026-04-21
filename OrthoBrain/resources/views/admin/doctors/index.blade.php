@extends('layouts.admin')
@section('title', 'Doctors')
@section('page_title', 'Doctors')

@push('styles')
<style>
    /* Doctor listing — reuses existing card / table / badge styles. */
    .ob-doctor-tabs {
        border-bottom: 1px solid #ebe9f1;
        padding: 0 1.5rem;
    }
    .ob-doctor-tabs .nav-link {
        border: 0;
        color: #6e6b7b;
        font-weight: 500;
        padding: 0.9rem 1rem;
        border-bottom: 2px solid transparent;
        background: transparent;
    }
    .ob-doctor-tabs .nav-link:hover { color: #5e50ee; }
    .ob-doctor-tabs .nav-link.active {
        color: #5e50ee;
        border-bottom-color: #5e50ee;
        background: transparent;
    }
    .ob-doctor-tabs .nav-link .tab-count {
        display: inline-block;
        margin-left: 0.35rem;
        padding: 0.05rem 0.5rem;
        font-size: 0.72rem;
        background: #f3f2f7;
        color: #6e6b7b;
        border-radius: 10rem;
        font-weight: 600;
    }
    .ob-doctor-tabs .nav-link.active .tab-count {
        background: rgba(94, 80, 238, 0.12);
        color: #5e50ee;
    }
    .ob-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #ece9fb;
        color: #5e50ee;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
        flex: 0 0 auto;
    }
    .ob-avatar.ob-avatar-lg {
        width: 80px; height: 80px;
        font-size: 1.6rem;
    }
    .ob-avatar img {
        width: 100%; height: 100%; object-fit: cover; border-radius: 50%;
    }
    .ob-pending-pill {
        background: rgba(255, 159, 67, 0.12);
        color: #ff9f43;
        border: 1px solid rgba(255, 159, 67, 0.25);
        padding: 0.35rem 0.85rem;
        border-radius: 10rem;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex; align-items: center; gap: 0.4rem;
        text-decoration: none;
        transition: background 0.15s ease;
    }
    .ob-pending-pill:hover { background: rgba(255, 159, 67, 0.2); color: #ff9f43; }
</style>
@endpush

@section('content')
@php
    $tabs = [
        ['key' => null,         'label' => 'All',       'badgeClass' => ''],
        ['key' => 'PENDING',    'label' => 'Pending',   'badgeClass' => 'warning'],
        ['key' => 'APPROVED',   'label' => 'Approved',  'badgeClass' => 'success'],
        ['key' => 'REJECTED',   'label' => 'Rejected',  'badgeClass' => 'danger'],
        ['key' => 'SUSPENDED',  'label' => 'Suspended', 'badgeClass' => 'secondary'],
    ];
    $tabCount = fn ($key) => $key === null ? ($totalCount ?? 0) : (int) ($statusCounts[$key] ?? 0);
    $statusToBadge = [
        'PENDING'   => ['label' => 'Pending',   'class' => 'warning'],
        'APPROVED'  => ['label' => 'Approved',  'class' => 'success'],
        'REJECTED'  => ['label' => 'Rejected',  'class' => 'danger'],
        'SUSPENDED' => ['label' => 'Suspended', 'class' => 'secondary'],
    ];
@endphp

<section id="doctors-list">
    <div class="card">
        <div class="card-header border-bottom align-items-center">
            <h4 class="card-title mb-0">Doctors</h4>

            <div class="d-flex align-items-center gap-1">
                @if (!empty($pendingCount))
                    <a href="{{ route('admin.doctors.index', ['status' => 'PENDING']) }}" class="ob-pending-pill">
                        <i data-feather="clock"></i>
                        {{ $pendingCount }} pending review
                    </a>
                @endif
                <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary">
                    <i data-feather="plus" class="me-25"></i> Add Doctor
                </a>
            </div>
        </div>

        <ul class="nav ob-doctor-tabs flex-wrap">
            @foreach ($tabs as $tab)
                @php
                    $isActive = ($currentStatus ?? null) === $tab['key'];
                    $href = $tab['key']
                        ? route('admin.doctors.index', ['status' => $tab['key']])
                        : route('admin.doctors.index');
                @endphp
                <li class="nav-item">
                    <a href="{{ $href }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                        {{ $tab['label'] }}
                        <span class="tab-count">{{ $tabCount($tab['key']) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="card-body py-1">
            <form id="doctorsFilter" method="GET" class="row g-1 py-1">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="col-md-5">
                    <select name="practice_id" class="js-searchable form-select">
                        <option value="">All practices</option>
                        @foreach ($practices as $p)
                            <option value="{{ $p->id }}" @selected(request('practice_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" placeholder="Search by name, email, or phone"
                           value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Practice</th>
                        <th>Contact</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $doctor)
                        @php
                            $initials = strtoupper(substr($doctor->first_name, 0, 1) . substr($doctor->last_name, 0, 1));
                            $badge = $statusToBadge[$doctor->approval_status] ?? ['label' => $doctor->approval_status, 'class' => 'secondary'];
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="ob-avatar me-1">{{ $initials }}</span>
                                    <div>
                                        <div class="fw-bolder">Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}</div>
                                        @if ($doctor->other_email)
                                            <div class="text-muted" style="font-size: 0.78rem;">{{ $doctor->other_email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $doctor->practice?->name ?? '—' }}</td>
                            <td>
                                <div>{{ $doctor->doctor_contact_email }}</div>
                                @if ($doctor->doctor_cell_phone)
                                    <div class="text-muted" style="font-size: 0.78rem;">{{ $doctor->doctor_cell_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $doctor->created_at?->toDayDateTimeString() }}">
                                    {{ $doctor->created_at?->diffForHumans() ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $badge['class'] }}">
                                    {{ strtoupper($badge['label']) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.doctors.show', $doctor) }}"
                                   class="btn btn-icon btn-sm btn-outline-success" title="View">
                                    <i data-feather="eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-2">No doctors match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">{{ $doctors->links() }}</div>
    </div>
</section>

@push('scripts')<script>obAutoFilter('#doctorsFilter');</script>@endpush
@endsection
