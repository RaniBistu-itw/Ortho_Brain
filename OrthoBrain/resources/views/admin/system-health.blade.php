@extends('layouts.admin')
@section('title', 'System Health')
@section('page_title', 'System Health')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════
   OrthoBrain System Health Dashboard
   ═══════════════════════════════════════════════════════════════════ */
#ob-health {
  --h-green:     #059669;
  --h-green-bg:  #ECFDF5;
  --h-green-bd:  #A7F3D0;
  --h-amber:     #D97706;
  --h-amber-bg:  #FFFBEB;
  --h-amber-bd:  #FDE68A;
  --h-red:       #DC2626;
  --h-red-bg:    #FEF2F2;
  --h-red-bd:    #FECACA;
  --h-slate:     #64748B;
  --h-border:    rgba(14,100,165,.1);
  --h-shadow:    0 2px 16px rgba(14,50,90,.08);
  --h-r:         16px;
  --h-r-sm:      10px;
}

@keyframes ob-fade-up {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}
#ob-health .ob-anim { animation: ob-fade-up .4s ease both; }
#ob-health .ob-anim-1 { animation-delay: .05s; }
#ob-health .ob-anim-2 { animation-delay: .10s; }
#ob-health .ob-anim-3 { animation-delay: .15s; }
#ob-health .ob-anim-4 { animation-delay: .20s; }
#ob-health .ob-anim-5 { animation-delay: .25s; }
#ob-health .ob-anim-6 { animation-delay: .30s; }

/* ── Hero banner ───────────────────────────────────── */
#ob-health .ob-hero {
  background: linear-gradient(135deg, #1E3A5F 0%, #0EA5C5 100%);
  border-radius: var(--h-r);
  padding: 2rem 2.25rem;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.75rem;
  box-shadow: 0 6px 32px rgba(14,50,90,.18);
}
body #ob-health .ob-hero,
body #ob-health .ob-hero * { color: #fff !important; }
body #ob-health .ob-hero .ob-hero-sub { color: rgba(255,255,255,.72) !important; }

.ob-hero-eyebrow {
  font-size: .68rem;
  font-weight: 800;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: rgba(255,255,255,.65) !important;
  margin-bottom: .3rem;
}
.ob-hero-title {
  font-size: 1.55rem;
  font-weight: 800;
  line-height: 1.2;
  margin: 0;
}
.ob-hero-sub {
  font-size: .85rem;
  margin-top: .3rem;
  opacity: .78;
}
.ob-refresh-btn {
  background: rgba(255,255,255,.15);
  border: 1.5px solid rgba(255,255,255,.35);
  color: #fff !important;
  border-radius: 10px;
  padding: .55rem 1.2rem;
  font-size: .82rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: .4rem;
  text-decoration: none;
  transition: background .15s;
}
.ob-refresh-btn:hover { background: rgba(255,255,255,.25); color: #fff !important; }
.ob-refresh-btn svg { width: 14px; height: 14px; }

/* ── Overall status pill ───────────────────────────── */
.ob-overall {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .45rem 1rem;
  border-radius: 30px;
  font-size: .78rem;
  font-weight: 800;
  letter-spacing: .5px;
  margin-bottom: 1.5rem;
  border: 1.5px solid;
}
.ob-overall--ok      { color: var(--h-green); background: var(--h-green-bg); border-color: var(--h-green-bd); }
.ob-overall--warning { color: var(--h-amber); background: var(--h-amber-bg); border-color: var(--h-amber-bd); }
.ob-overall--failed  { color: var(--h-red);   background: var(--h-red-bg);   border-color: var(--h-red-bd); }
.ob-overall .ob-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* ── Check cards grid ──────────────────────────────── */
.ob-checks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.1rem;
}

.ob-check-card {
  background: #fff;
  border-radius: var(--h-r-sm);
  border: 1.5px solid;
  padding: 1.35rem 1.4rem;
  box-shadow: var(--h-shadow);
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: box-shadow .15s, transform .15s;
}
.ob-check-card:hover { box-shadow: 0 6px 28px rgba(14,50,90,.13); transform: translateY(-1px); }

.ob-check-card--ok      { border-color: var(--h-green-bd); }
.ob-check-card--warning { border-color: var(--h-amber-bd); }
.ob-check-card--failed  { border-color: var(--h-red-bd); }
.ob-check-card--crashed { border-color: var(--h-red-bd); }
.ob-check-card--skipped { border-color: #E2E8F0; }

.ob-check-icon {
  width: 44px; height: 44px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.ob-check-icon svg { width: 20px; height: 20px; }

.ob-check-card--ok      .ob-check-icon { background: var(--h-green-bg); color: var(--h-green); }
.ob-check-card--warning .ob-check-icon { background: var(--h-amber-bg); color: var(--h-amber); }
.ob-check-card--failed  .ob-check-icon { background: var(--h-red-bg);   color: var(--h-red); }
.ob-check-card--crashed .ob-check-icon { background: var(--h-red-bg);   color: var(--h-red); }
.ob-check-card--skipped .ob-check-icon { background: #F1F5F9;           color: #94A3B8; }

.ob-check-body { flex: 1; min-width: 0; }
.ob-check-label {
  font-size: .72rem;
  font-weight: 800;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: var(--h-slate);
  margin-bottom: .22rem;
}
.ob-check-status {
  font-size: .95rem;
  font-weight: 700;
  margin-bottom: .3rem;
}
.ob-check-card--ok      .ob-check-status { color: var(--h-green); }
.ob-check-card--warning .ob-check-status { color: var(--h-amber); }
.ob-check-card--failed  .ob-check-status { color: var(--h-red); }
.ob-check-card--crashed .ob-check-status { color: var(--h-red); }
.ob-check-card--skipped .ob-check-status { color: #94A3B8; }

.ob-check-message {
  font-size: .8rem;
  color: #64748B;
  line-height: 1.45;
  word-break: break-word;
}

.ob-check-badge {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .18rem .6rem;
  border-radius: 20px;
  font-size: .67rem;
  font-weight: 800;
  letter-spacing: .4px;
  text-transform: uppercase;
  margin-bottom: .3rem;
}
.ob-badge--ok      { background: var(--h-green-bg); color: var(--h-green); }
.ob-badge--warning { background: var(--h-amber-bg); color: var(--h-amber); }
.ob-badge--failed,
.ob-badge--crashed { background: var(--h-red-bg);   color: var(--h-red); }
.ob-badge--skipped { background: #F1F5F9;            color: #94A3B8; }
</style>
@endpush

@section('content')
@php
    // Compute overall worst status across all checks.
    $statusPriority = ['failed' => 3, 'crashed' => 3, 'warning' => 2, 'skipped' => 1, 'ok' => 0];
    $worstPriority  = 0;
    $overallStatus  = 'ok';
    foreach ($results as $row) {
        $s = $row['result']->status->value;
        $p = $statusPriority[$s] ?? 0;
        if ($p > $worstPriority) {
            $worstPriority = $p;
            $overallStatus = $s;
        }
    }

    $overallLabel = match($overallStatus) {
        'ok'             => 'All Systems Operational',
        'warning'        => 'Degraded — Some Checks Need Attention',
        'failed','crashed'=> 'Outage — One or More Checks Failed',
        default          => 'Unknown',
    };

    $iconForName = fn(string $name): string => match(true) {
        str_contains($name, 'Database')  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
        str_contains($name, 'Disk')      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>',
        str_contains($name, 'Queue')     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
        str_contains($name, 'Mail')      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
        str_contains($name, 'Gemini')    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
        str_contains($name, 'Env')       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>',
        default                          => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
    };
@endphp

<div id="ob-health">

    {{-- ── Hero ─────────────────────────────────────────────── --}}
    <div class="ob-hero ob-anim ob-anim-1">
        <div>
            <p class="ob-hero-eyebrow">OrthoBrain · Ops</p>
            <h1 class="ob-hero-title">System Health</h1>
            <p class="ob-hero-sub">Live check results — refreshed on every page load.</p>
        </div>
        <a href="{{ route('admin.system-health') }}" class="ob-refresh-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Refresh
        </a>
    </div>

    {{-- ── Overall status ──────────────────────────────────── --}}
    <div class="ob-anim ob-anim-2">
        @php
            $overallClass = match($overallStatus) {
                'ok'      => 'ob-overall--ok',
                'warning' => 'ob-overall--warning',
                default   => 'ob-overall--failed',
            };
            $dotColor = match($overallStatus) {
                'ok'      => '#059669',
                'warning' => '#D97706',
                default   => '#DC2626',
            };
        @endphp
        <div class="ob-overall {{ $overallClass }}">
            <span class="ob-dot" style="background:{{ $dotColor }}"></span>
            {{ $overallLabel }}
        </div>
        <div class="text-muted" style="font-size:.78rem; margin-bottom:1.25rem;">
            Checked at {{ $checkedAt->format('M j, Y · g:i:s A') }} &nbsp;·&nbsp; {{ $results->count() }} checks
        </div>
    </div>

    {{-- ── Check cards ─────────────────────────────────────── --}}
    <div class="ob-checks-grid">
        @foreach($results as $i => $row)
            @php
                $status  = $row['result']->status->value;
                $summary = $row['result']->shortSummary ?: $row['result']->notificationMessage;
                $delay   = $i + 3;   // stagger after hero (which uses 1-2)
                $cardClass  = "ob-check-card--{$status}";
                $badgeClass = "ob-badge--{$status}";
                $statusLabel = match($status) {
                    'ok'      => 'OK',
                    'warning' => 'Warning',
                    'failed'  => 'Failed',
                    'crashed' => 'Crashed',
                    'skipped' => 'Skipped',
                    default   => ucfirst($status),
                };
                $icon = $iconForName($row['name']);
            @endphp
            <div class="ob-check-card {{ $cardClass }} ob-anim ob-anim-{{ min($delay, 6) }}">
                <div class="ob-check-icon">
                    {!! $icon !!}
                </div>
                <div class="ob-check-body">
                    <div class="ob-check-label">{{ $row['label'] }}</div>
                    <span class="ob-check-badge {{ $badgeClass }}">
                        @if($status === 'ok')
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @elseif($status === 'warning')
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        @endif
                        {{ $statusLabel }}
                    </span>
                    @if($summary)
                        <div class="ob-check-message">{{ $summary }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
