@extends('layouts.admin')
@section('title', 'Practice · ' . $practice->name)
@section('page_title', $practice->name)

@push('styles')
<style>
    #practice-show {
        --ob-primary: #5bc0de;
        --ob-primary-softer: rgba(91, 192, 222, 0.07);
        --ob-accent-soft: rgba(140, 198, 63, 0.14);
        --ob-muted: #6e6b7b;
        --ob-border: #ebe9f1;
        --ob-surface: #ffffff;
        --ob-surface-alt: #f8f8fb;
        --ob-text: #1f1f1f;
    }
    #practice-show .ob-card {
        background: var(--ob-surface);
        border: 1px solid var(--ob-border);
        border-radius: 0.85rem;
        box-shadow: 0 1px 2px rgba(24, 28, 40, 0.04);
        overflow: hidden;
    }
    #practice-show .ob-card + .ob-card { margin-top: 1rem; }
    #practice-show .ob-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        gap: 0.75rem; flex-wrap: wrap;
    }
    #practice-show .ob-card-title { font-weight: 700; color: #111; margin: 0; font-size: 1rem; }
    #practice-show .ob-card-body { padding: 1rem 1.25rem; }

    #practice-show .ob-back {
        display: inline-flex; align-items: center; gap: 0.35rem;
        color: var(--ob-muted);
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 0.75rem;
    }
    #practice-show .ob-back:hover { color: var(--ob-primary); }
    #practice-show .ob-back svg { width: 14px; height: 14px; }

    #practice-show .ob-hero {
        display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;
        padding: 1.25rem;
    }
    #practice-show .ob-logo-lg {
        width: 72px; height: 72px;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #e3f4fa 0%, #c9ebf5 100%);
        color: var(--ob-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1.3rem;
        overflow: hidden;
        box-shadow: 0 0 0 2px #fff, 0 0 0 3px #ebe9f1;
        flex: 0 0 auto;
    }
    #practice-show .ob-logo-lg img { width: 100%; height: 100%; object-fit: cover; }
    #practice-show .ob-hero-name { font-size: 1.2rem; font-weight: 700; color: #111; margin: 0; }
    #practice-show .ob-hero-sub { color: var(--ob-muted); font-size: 0.88rem; margin-top: 0.15rem; }

    #practice-show .ob-status {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem; font-weight: 700;
        letter-spacing: 0.03em; text-transform: uppercase;
        border: 1px solid transparent;
    }
    #practice-show .ob-status::before {
        content: ''; width: 6px; height: 6px;
        border-radius: 50%; background: currentColor; display: inline-block;
    }
    #practice-show .ob-status--success   { background: var(--ob-accent-soft); color: #5a8f21; border-color: rgba(140, 198, 63, 0.28); }
    #practice-show .ob-status--secondary { background: #eef0f4; color: #6c7283; border-color: #e2e4eb; }

    #practice-show .ob-grid {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem 1.5rem;
    }
    @media (max-width: 576px) { #practice-show .ob-grid { grid-template-columns: 1fr; } }

    #practice-show .ob-field-label {
        font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;
        color: var(--ob-muted); text-transform: uppercase;
        display: block; margin-bottom: 0.25rem;
    }
    #practice-show .ob-field-value { color: var(--ob-text); font-size: 0.92rem; }
    #practice-show .ob-field-value a { color: var(--ob-primary); text-decoration: none; }
    #practice-show .ob-field-value a:hover { text-decoration: underline; }

    #practice-show .ob-members-list { width: 100%; }
    #practice-show .ob-members-list th, #practice-show .ob-members-list td {
        padding: 0.65rem 1.25rem;
        border-top: 1px solid #f3f2f7;
        font-size: 0.88rem;
    }
    #practice-show .ob-members-list thead th {
        background: var(--ob-surface-alt);
        color: #555668;
        font-weight: 600;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-top: 0;
    }
    #practice-show .ob-empty-sub { color: var(--ob-muted); padding: 1.25rem; text-align: center; }
</style>
@endpush

@section('content')
@php
    $logoUrl = $practice->logoUrl();
    $initials = strtoupper(mb_substr(trim($practice->name ?? ''), 0, 2));
    $statusToBadge = [
        'ACTIVE'   => ['label' => 'Active',   'tone' => 'success'],
        'INACTIVE' => ['label' => 'Inactive', 'tone' => 'secondary'],
    ];
    $badge = $statusToBadge[$practice->status] ?? ['label' => $practice->status, 'tone' => 'secondary'];

    $phoneDisplay = trim(($practice->phone_country_code ? '+' . ltrim($practice->phone_country_code, '+') . ' ' : '') . ($practice->phone_number ?? ''));
    $websiteHref = $practice->website
        ? (\Illuminate\Support\Str::startsWith($practice->website, ['http://', 'https://']) ? $practice->website : 'http://' . $practice->website)
        : null;

    $addressLines = array_values(array_filter([
        $practice->street_address_1,
        $practice->street_address_2,
        trim(collect([$practice->city?->name, $practice->state?->name, $practice->zipcode?->code])->filter()->implode(', ')),
        $practice->country?->name,
    ]));

    $ownerName = $practice->owner
        ? 'Dr. ' . trim(($practice->owner->first_name ?? '') . ' ' . ($practice->owner->last_name ?? ''))
        : null;
@endphp

<section id="practice-show">
    <a href="{{ route('admin.practices.index') }}" class="ob-back">
        <i data-feather="arrow-left"></i> Back to Practices
    </a>

    {{-- Hero card --}}
    <div class="ob-card">
        <div class="ob-hero">
            <span class="ob-logo-lg">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $practice->name }}">
                @else
                    {{ $initials ?: 'P' }}
                @endif
            </span>
            <div class="flex-grow-1">
                <h4 class="ob-hero-name">{{ $practice->name }}</h4>
                <div class="ob-hero-sub">
                    {{ $ownerName ? 'Owned by ' . $ownerName : 'No owner on file' }}
                    · Added {{ $practice->created_at?->toFormattedDateString() ?? '—' }}
                </div>
            </div>
            <span class="ob-status ob-status--{{ $badge['tone'] }}">{{ $badge['label'] }}</span>
        </div>
    </div>

    {{-- Contact + Address --}}
    <div class="ob-card">
        <div class="ob-card-head">
            <h5 class="ob-card-title">Practice details</h5>
        </div>
        <div class="ob-card-body">
            <div class="ob-grid">
                <div>
                    <span class="ob-field-label">Owner</span>
                    <div class="ob-field-value">{{ $ownerName ?? '—' }}</div>
                </div>
                <div>
                    <span class="ob-field-label">Status</span>
                    <div class="ob-field-value">{{ $badge['label'] }}</div>
                </div>
                <div>
                    <span class="ob-field-label">Phone</span>
                    <div class="ob-field-value">{{ $phoneDisplay !== '' ? $phoneDisplay : '—' }}</div>
                </div>
                <div>
                    <span class="ob-field-label">Website</span>
                    <div class="ob-field-value">
                        @if ($practice->website)
                            <a href="{{ $websiteHref }}" target="_blank" rel="noopener">{{ $practice->website }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <span class="ob-field-label">Address</span>
                    <div class="ob-field-value">
                        @if (count($addressLines))
                            @foreach ($addressLines as $line)
                                {{ $line }}@if (!$loop->last)<br>@endif
                            @endforeach
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Members --}}
    <div class="ob-card">
        <div class="ob-card-head">
            <h5 class="ob-card-title">Members ({{ $practice->members->count() }})</h5>
        </div>
        @if ($practice->members->count())
            <div class="table-responsive">
                <table class="ob-members-list">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($practice->members as $member)
                            <tr>
                                <td>Dr. {{ $member->first_name }} {{ $member->last_name }}</td>
                                <td>{{ $member->doctor_contact_email ?? '—' }}</td>
                                <td>{{ $member->doctor_cell_phone ?? '—' }}</td>
                                <td>{{ $member->approval_status ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="ob-empty-sub">No doctors are linked to this practice yet.</div>
        @endif
    </div>
</section>
@endsection
