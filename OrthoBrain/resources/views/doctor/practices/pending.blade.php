@extends('layouts.app')

@section('title', 'Practices Pending Approval')
@section('page_title', 'Practices Pending Approval')

@push('styles')
<style>
    /* ── Alert banner ──────────────────────────────────────── */
    .pend-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        background: color-mix(in srgb, #fff8e7 80%, var(--ob-surface));
        border: 1px solid color-mix(in srgb, #f5c249 35%, transparent);
        border-radius: 0.6rem;
        padding: 0.9rem 1rem;
        box-shadow: 0 1px 3px rgba(24,28,40,0.04);
    }
    .pend-alert-icon {
        flex: 0 0 auto;
        width: 2rem;
        height: 2rem;
        border-radius: 0.4rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ee5a47;
        color: #fff;
        font-size: 1rem;
    }
    .pend-alert-body { flex: 1; font-size: 0.88rem; color: var(--ob-text); line-height: 1.45; }
    .pend-alert-body strong { font-weight: 600; color: var(--ob-text); }
    .pend-alert-highlight { color: #d43f3a; font-weight: 600; }

    /* ── Card shell ────────────────────────────────────────── */
    .pend-card {
        background: var(--ob-surface);
        border: 1px solid var(--ob-border);
        border-radius: 0.7rem;
        padding: 1.1rem 1.2rem;
        box-shadow: 0 1px 3px rgba(24,28,40,0.04);
    }

    .pend-list-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .pend-list-head h5 { margin: 0 0 0.15rem; font-weight: 600; color: var(--ob-text); }
    .pend-list-head p { margin: 0; font-size: 0.82rem; color: var(--ob-text-muted); }

    .pend-count-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.65rem;
        background: color-mix(in srgb, var(--ob-primary) 10%, transparent);
        color: var(--ob-primary);
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 999px;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    /* ── Pending row ───────────────────────────────────────── */
    .pend-row {
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        background: var(--ob-surface);
        border: 1px solid var(--ob-border);
        border-left-width: 4px;
        border-radius: 0.55rem;
        box-shadow: 0 1px 2px rgba(24,28,40,0.03);
    }
    .pend-row + .pend-row { margin-top: 0.65rem; }

    /* Rotating accent colors (left border + icon bg), cycling per row. */
    .pend-row-c0 { border-left-color: #ef6e6e; }
    .pend-row-c1 { border-left-color: #5bc48a; }
    .pend-row-c2 { border-left-color: #7e7ce0; }

    .pend-row-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.45rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .pend-row-c0 .pend-row-icon { background: #ef6e6e; }
    .pend-row-c1 .pend-row-icon { background: #5bc48a; }
    .pend-row-c2 .pend-row-icon { background: #7e7ce0; }

    .pend-row-body { min-width: 0; }
    .pend-row-body strong {
        display: block;
        color: var(--ob-text);
        font-weight: 600;
        font-size: 0.95rem;
        line-height: 1.25;
    }
    .pend-row-body small {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: var(--ob-text-muted);
        font-size: 0.78rem;
        margin-top: 0.1rem;
    }

    .pend-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.55rem;
        background: color-mix(in srgb, #f5a623 15%, transparent);
        color: #b07015;
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 999px;
        white-space: nowrap;
    }

    .pend-cancel-ghost {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.65rem;
        background: transparent;
        border: 0;
        color: var(--ob-text-muted);
        font-size: 0.82rem;
        font-weight: 500;
        border-radius: 0.35rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .pend-cancel-ghost:hover { color: var(--ob-danger); background: color-mix(in srgb, var(--ob-danger) 7%, transparent); }
</style>
@endpush

@section('content')
<div class="row g-3">
    <div class="col-12">
        <div class="pend-alert mb-3">
            <span class="pend-alert-icon"><i class="bi bi-hourglass-split"></i></span>
            <div class="pend-alert-body">
                <strong class="d-block mb-1">Account Approval in Progress</strong>
                Your administrator account is currently being verified. You have
                <span class="pend-alert-highlight">{{ $pending->count() }} pending active {{ $pending->count() === 1 ? 'practice' : 'practices' }}</span>
                awaiting system integration.
            </div>
        </div>

        <div class="pend-card mb-3">
            <div class="pend-list-head">
                <div>
                    <h5>Pending Requests</h5>
                    <p>Manage your outgoing invitations to join existing clinical networks.</p>
                </div>
                @if($pending->isNotEmpty())
                    <span class="pend-count-chip">
                        <i class="bi bi-hourglass-split"></i>
                        {{ $pending->count() }} Active {{ $pending->count() === 1 ? 'Request' : 'Requests' }}
                    </span>
                @endif
            </div>

            @if($pending->isEmpty())
                <p class="text-muted mb-0" style="font-size:0.88rem;">No pending requests. Submit a new one from the <a href="{{ route('doctor.profile.index', ['tab' => 'practices']) }}">My Practices</a> tab on your profile.</p>
            @else
                @foreach($pending as $p)
                    @php
                        $cIdx = $loop->index % 3;
                        $icon = ['bi-heart-pulse-fill','bi-emoji-smile-fill','bi-folder-fill'][$cIdx];
                    @endphp
                    <div class="pend-row pend-row-c{{ $cIdx }}">
                        <span class="pend-row-icon"><i class="bi {{ $icon }}"></i></span>
                        <div class="pend-row-body">
                            <strong>{{ $p->name }}</strong>
                            <small><i class="bi bi-clock"></i> Requested {{ \Carbon\Carbon::parse($p->pivot->requested_at ?? $p->pivot->created_at)->diffForHumans() }}</small>
                        </div>
                        <span class="pend-status-pill"><i class="bi bi-hourglass-split"></i> Pending</span>
                        <form method="POST" action="{{ route('doctor.practices.cancel', $p->pivot->id) }}"
                              onsubmit="return confirm('Cancel this request?')" class="m-0">
                            @csrf
                            <button type="submit" class="pend-cancel-ghost">
                                <i class="bi bi-x-circle"></i> Cancel Request
                            </button>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>

        @if($rejected->isNotEmpty())
            <div class="pend-card">
                <div class="pend-list-head">
                    <div><h5>Recently Rejected</h5></div>
                </div>
                @foreach($rejected as $p)
                    <div class="pend-row pend-row-c0">
                        <span class="pend-row-icon" style="background: var(--ob-danger);"><i class="bi bi-x-circle"></i></span>
                        <div class="pend-row-body">
                            <strong>{{ $p->name }}</strong>
                            @if($p->pivot->rejection_reason)
                                <small>Reason: {{ $p->pivot->rejection_reason }}</small>
                            @endif
                        </div>
                        <span></span>
                        <span></span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
