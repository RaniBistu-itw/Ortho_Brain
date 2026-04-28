@php
    $statusToBadge = [
        'PENDING'   => ['label' => 'Pending',   'tone' => 'warning'],
        'APPROVED'  => ['label' => 'Approved',  'tone' => 'success'],
        'REJECTED'  => ['label' => 'Rejected',  'tone' => 'danger'],
        'SUSPENDED' => ['label' => 'Suspended', 'tone' => 'secondary'],
    ];
    $selectedPractice = request('practice_id')
        ? optional($practices->firstWhere('id', (int) request('practice_id')))->name
        : null;
    $searchTerm = trim((string) request('search', ''));
    $hasActiveFilters = $selectedPractice || $searchTerm !== '';

    $queryWithout = function (array $remove) {
        $q = request()->query();
        foreach ($remove as $k) { unset($q[$k]); }
        return route('admin.doctors.index', $q);
    };
@endphp

@if ($hasActiveFilters)
    <div class="ob-active-filters" style="padding: 0.5rem 1.25rem 0;">
        @if ($searchTerm !== '')
            <span class="ob-chip">
                <span class="ob-chip-label">Search:</span> {{ $searchTerm }}
                <a href="{{ $queryWithout(['search']) }}" title="Remove filter"><i data-feather="x"></i></a>
            </span>
        @endif
        @if ($selectedPractice)
            <span class="ob-chip">
                <span class="ob-chip-label">Practice:</span> {{ $selectedPractice }}
                <a href="{{ $queryWithout(['practice_id']) }}" title="Remove filter"><i data-feather="x"></i></a>
            </span>
        @endif
    </div>
@endif

<div class="table-responsive">
    <table class="table ob-admin-table">
        <thead>
            <tr>
                <th>@include('admin._partials.sort_th', ['label' => 'Doctor', 'key' => 'doctor', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                <th>@include('admin._partials.sort_th', ['label' => 'Practice', 'key' => 'practice', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                <th>@include('admin._partials.sort_th', ['label' => 'Contact', 'key' => 'contact', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                <th>@include('admin._partials.sort_th', ['label' => 'Registered', 'key' => 'created_at', 'default' => 'created_at', 'defaultDir' => 'desc'])</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($doctors as $doctor)
                @php
                    $initials  = strtoupper(substr($doctor->first_name, 0, 1) . substr($doctor->last_name, 0, 1));
                    $badge     = $statusToBadge[$doctor->approval_status] ?? ['label' => $doctor->approval_status, 'tone' => 'secondary'];
                    $avatarUrl = $doctor->avatarUrl();
                @endphp
                <tr>
                    <td>
                        <div class="ob-doctor-cell">
                            <span class="ob-avatar">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}"
                                         data-preview-src="{{ $avatarUrl }}" data-preview-size="lg">
                                @else
                                    {{ $initials }}
                                @endif
                            </span>
                            <div>
                                <span class="ob-doctor-name">Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}</span>
                                @if ($doctor->other_email)
                                    <span class="ob-doctor-sub">{{ $doctor->other_email }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ob-practice">{{ $doctor->practice?->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="ob-contact-line">
                            <i data-feather="mail"></i>
                            @if ($doctor->doctor_contact_email)
                                <a href="mailto:{{ $doctor->doctor_contact_email }}"
                                   title="Email Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}">{{ $doctor->doctor_contact_email }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                        @if ($doctor->doctor_cell_phone)
                            <br>
                            <span class="ob-contact-line is-sub">
                                <i data-feather="phone"></i>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $doctor->doctor_cell_phone) }}"
                                   title="Call Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}">{{ $doctor->doctor_cell_phone }}</a>
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="ob-when" title="{{ $doctor->created_at?->toDayDateTimeString() }}">
                            <i data-feather="calendar"></i>
                            {{ $doctor->created_at?->diffForHumans() ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <span class="dr-status dr-status--{{ $badge['tone'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="ob-row-actions">
                            <a href="{{ route('admin.doctors.show', $doctor) }}"
                               class="ob-icon-btn" title="View details">
                                <i data-feather="eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="ob-empty">
                            <div class="ob-empty-icon"><i data-feather="users"></i></div>
                            <div class="ob-empty-title">No doctors found</div>
                            <div class="ob-empty-sub">
                                @if ($hasActiveFilters)
                                    Try adjusting your filters or <a href="{{ route('admin.doctors.index') }}">clear all filters</a>.
                                @else
                                    Get started by adding your first doctor.
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($doctors->total() > 0)
    <div class="ob-foot">
        <div class="ob-foot-meta">
            Showing <strong>{{ $doctors->firstItem() }}</strong>–<strong>{{ $doctors->lastItem() }}</strong>
            of <strong>{{ number_format($doctors->total()) }}</strong>
            {{ \Illuminate\Support\Str::plural('doctor', $doctors->total()) }}
        </div>
        <div>{{ $doctors->links() }}</div>
    </div>
@endif
