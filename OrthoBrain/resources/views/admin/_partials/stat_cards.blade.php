@php
    /**
     * Gradient KPI cards mirroring the admin dashboard hero KPI strip.
     *
     * @var array $cards Each entry:
     *   - label    (string)            Top-left badge text (uppercase)
     *   - value    (string|int)        Big number
     *   - icon     (string)            feather icon name (top-right)
     *   - tone     (string)            primary | success | danger | warning | info | slate
     *   - sub      (string|null)       Optional sub-text under the number
     *   - stat_key (string|null)       Optional value for data-stat="..."
     *   - route    (string|null)       Optional URL — renders as <a>
     */
@endphp

@once
@push('styles')
<style>
    .ob-stat-grid { display: grid; gap: 1rem; margin-bottom: 1.25rem; }
    .ob-stat-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ob-stat-grid--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    @media (max-width: 991.98px) {
        .ob-stat-grid--3, .ob-stat-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        .ob-stat-grid--3, .ob-stat-grid--4 { grid-template-columns: 1fr; }
    }

    .ob-stat-card {
        display: block;
        border-radius: 16px;
        padding: 1.4rem 1.5rem;
        color: #fff;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(14, 50, 90, .08);
        transition: transform .22s cubic-bezier(.34, 1.56, .64, 1), box-shadow .22s;
    }
    .ob-stat-card:hover { color: #fff; }
    a.ob-stat-card:hover { transform: translateY(-6px); box-shadow: 0 6px 36px rgba(14, 50, 90, .15); }
    .ob-stat-card::after {
        content: ''; position: absolute;
        right: -28px; bottom: -28px;
        width: 150px; height: 150px;
        background: rgba(255, 255, 255, .09);
        border-radius: 50%;
        pointer-events: none;
    }
    .ob-stat-card::before {
        content: ''; position: absolute;
        right: 55px; bottom: -60px;
        width: 100px; height: 100px;
        background: rgba(255, 255, 255, .06);
        border-radius: 50%;
        pointer-events: none;
    }

    .ob-stat-card--primary { background: linear-gradient(135deg, #1252A3 0%, #2575D0 60%, #41A8D8 100%); }
    .ob-stat-card--success { background: linear-gradient(135deg, #0C6B4A 0%, #10A372 60%, #30D49B 100%); }
    .ob-stat-card--info    { background: linear-gradient(135deg, #5B1F9E 0%, #8B5CF6 60%, #B89EF8 100%); }
    .ob-stat-card--warning { background: linear-gradient(135deg, #B26010 0%, #E8920A 60%, #FBCA55 100%); }
    .ob-stat-card--danger  { background: linear-gradient(135deg, #9B1B1B 0%, #DC2626 60%, #F87171 100%); }
    .ob-stat-card--slate   { background: linear-gradient(135deg, #1E293B 0%, #475569 60%, #94A3B8 100%); }

    .ob-stat-card__icon {
        position: absolute;
        top: 1.2rem; right: 1.2rem;
        width: 46px; height: 46px;
        background: rgba(255, 255, 255, .16);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        backdrop-filter: blur(4px);
        z-index: 2;
    }
    .ob-stat-card__icon svg { width: 22px !important; height: 22px !important; }

    .ob-stat-card__label {
        font-size: .69rem;
        font-weight: 700;
        letter-spacing: .9px;
        text-transform: uppercase;
        opacity: .82;
        margin-bottom: .45rem;
        position: relative; z-index: 1;
    }

    .ob-stat-card__value {
        font-size: 2.5rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -1.2px;
        margin: .15rem 0 .4rem;
        position: relative; z-index: 1;
    }

    .ob-stat-card__sub {
        font-size: .78rem;
        opacity: .75;
        font-weight: 500;
        position: relative; z-index: 1;
    }

    .dark-layout .ob-stat-card { box-shadow: 0 2px 16px rgba(0, 0, 0, .3); }
</style>
@endpush
@endonce

@php
    $count = count($cards ?? []);
    $gridClass = $count === 4 ? 'ob-stat-grid--4' : 'ob-stat-grid--3';
@endphp

<div class="ob-stat-grid {{ $gridClass }}">
    @foreach ($cards as $card)
        @php
            $tag      = !empty($card['route']) ? 'a' : 'div';
            $tone     = $card['tone'] ?? 'primary';
            $statAttr = !empty($card['stat_key']) ? ' data-stat="' . e($card['stat_key']) . '"' : '';
        @endphp
        <{{ $tag }} class="ob-stat-card ob-stat-card--{{ $tone }}"
            @if (!empty($card['route'])) href="{{ $card['route'] }}" @endif>
            @if (!empty($card['icon']))
                <div class="ob-stat-card__icon"><i data-feather="{{ $card['icon'] }}"></i></div>
            @endif
            <div class="ob-stat-card__label">{{ $card['label'] }}</div>
            <div class="ob-stat-card__value"{!! $statAttr !!}>{{ $card['value'] }}</div>
            @if (!empty($card['sub']))
                <div class="ob-stat-card__sub">{{ $card['sub'] }}</div>
            @endif
        </{{ $tag }}>
    @endforeach
</div>
