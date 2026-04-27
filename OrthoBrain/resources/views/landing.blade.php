<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>orthobrain — Orthodontics, built into your dental practice.</title>

    <link rel="apple-touch-icon" href="{{ asset('vuexy/images/ico/favicon-32x32.png') }}"/>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('vuexy/images/ico/favicon.ico') }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Public+Sans:wght@600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>

    <style>
    :root {
        --c-primary:   #2563EB;
        --c-primary-h: #1D4ED8;
        --c-tint:      #EFF6FF;
        --c-ortho:     #5bc0de;
        --c-brain:     #8cc63f;
        --c-ink:       #0F172A;
        --c-ink-2:     #334155;
        --c-muted:     #64748B;
        --c-line:      #E5E7EB;
        --c-line-soft: #F1F5F9;

        --shadow-sm:   0 1px 2px rgba(15,23,42,.04);
        --shadow:      0 8px 24px rgba(15,23,42,.06), 0 2px 6px rgba(15,23,42,.04);
        --shadow-lg:   0 16px 36px rgba(15,23,42,.08), 0 4px 10px rgba(15,23,42,.04);

        --r:           10px;
        --r-lg:        14px;
        --r-xl:        20px;

        --w:           1240px;
        --ease:        cubic-bezier(.22, 1, .36, 1);
    }

    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, sans-serif;
        background: #fff;
        color: var(--c-ink);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        font-size: 16px;
    }
    h1, h2, h3, h4 {
        font-family: 'Public Sans', Inter, sans-serif;
        color: var(--c-ink);
        letter-spacing: -.025em;
        line-height: 1.08;
        margin: 0;
        font-weight: 800;
    }
    p { margin: 0; }
    a { color: inherit; text-decoration: none; }
    img { max-width: 100%; display: block; }
    button { font-family: inherit; }

    .anim       { opacity: 0; transform: translateY(14px); }
    .anim-zoom  { opacity: 0; transform: scale(.97); }
    .anim-right { opacity: 0; transform: translateX(18px); }
    .anim-left  { opacity: 0; transform: translateX(-18px); }

    .wrap { max-width: var(--w); margin: 0 auto; padding: 0 1.5rem; }

    /* ───── NAV ───── */
    .nav { border-bottom: 1px solid var(--c-line-soft); background: #fff; position: sticky; top: 0; z-index: 50; }
    .nav-row {
        display: flex; align-items: center; justify-content: space-between;
        height: 72px; gap: 1.5rem;
    }
    .brand { display: inline-flex; align-items: center; gap: .5rem; }
    .brand-mark {
        width: 34px; height: 34px; border-radius: 9px;
        background: #fff; border: 1px solid var(--c-line);
        display: inline-flex; align-items: center; justify-content: center;
    }
    .brand-text {
        font-family: 'Public Sans', Inter, sans-serif;
        font-weight: 700; font-size: 1.3rem; letter-spacing: -.02em; line-height: 1;
        display: inline-flex; align-items: flex-start;
    }
    .brand-text .p1 { color: var(--c-ortho); }
    .brand-text .p2 { color: var(--c-brain); }
    .brand-text .tm { color: var(--c-brain); font-size: .55em; margin-left: 1px; margin-top: .12rem; font-weight: 600; }

    .menu { display: flex; align-items: center; gap: .15rem; }
    .menu a {
        font-size: .95rem; font-weight: 500; color: var(--c-ink-2);
        padding: .55rem 1rem; border-radius: var(--r);
        display: inline-flex; align-items: center; gap: .3rem;
        transition: color .15s, background .15s;
    }
    .menu a:hover { color: var(--c-primary); background: var(--c-tint); }
    .menu a i { font-size: .75rem; }

    .nav-actions { display: flex; align-items: center; gap: .55rem; }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: .5rem;
        padding: .6rem 1.05rem; border-radius: var(--r);
        font-weight: 600; font-size: .92rem;
        border: 1px solid transparent; cursor: pointer;
        transition: transform .18s var(--ease), background .15s, box-shadow .2s, color .15s, border-color .15s;
        white-space: nowrap; line-height: 1.2;
    }
    .btn-primary { background: var(--c-primary); color: #fff; }
    .btn-primary:hover { background: var(--c-primary-h); transform: translateY(-1px); color: #fff; }
    .btn-outline { background: #fff; color: var(--c-ink); border-color: var(--c-line); }
    .btn-outline:hover { border-color: var(--c-ink-2); transform: translateY(-1px); }
    .btn-lg { padding: .85rem 1.4rem; font-size: .95rem; }

    /* ───── HERO ───── */
    .hero { padding: 3rem 0 2.5rem; }
    .hero-grid {
        display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        gap: 2.5rem; align-items: center;
    }
    .pill {
        display: inline-flex; align-items: center; gap: .45rem;
        font-size: .82rem; font-weight: 500; color: var(--c-primary-h);
        background: var(--c-tint);
        padding: .42rem .9rem; border-radius: 100px;
        margin-bottom: 1.5rem;
    }
    .pill i { color: var(--c-primary); }

    .hero h1 {
        font-size: clamp(2.4rem, 5.2vw, 4.2rem);
        font-weight: 800; line-height: 1.04;
        letter-spacing: -.04em;
        color: var(--c-ink);
        margin-bottom: 1.2rem;
    }
    .hero-sub {
        font-size: 1.05rem; color: var(--c-muted);
        max-width: 480px; margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    .feats {
        display: grid; grid-template-columns: repeat(2, max-content); column-gap: 2.5rem; row-gap: .65rem;
        margin-bottom: 1.85rem;
    }
    .feats span {
        display: inline-flex; align-items: center; gap: .55rem;
        font-size: .94rem; color: var(--c-ink-2);
    }
    .feats i { color: var(--c-primary); font-size: 1.1rem; flex-shrink: 0; }

    .ctas { display: flex; flex-wrap: wrap; gap: .65rem; margin-bottom: .75rem; }
    .cc {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .82rem; color: var(--c-muted); margin-bottom: 1.6rem;
    }
    .cc i { color: var(--c-muted); }

    .trust { display: flex; align-items: center; gap: .9rem; }
    .stack { display: inline-flex; align-items: center; }
    .stack img,
    .stack .more {
        width: 38px; height: 38px; border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(15,23,42,.10);
        object-fit: cover;
    }
    .stack > * + * { margin-left: -10px; }
    .stack .more {
        background: #DBEAFE; color: var(--c-primary-h);
        font-size: .72rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .trust-text { font-size: .9rem; color: var(--c-ink-2); line-height: 1.4; }

    /* Hero visual */
    .stage {
        position: relative;
        min-height: 620px;
        border-radius: var(--r-xl);
        overflow: hidden;
        background: #fff;
        isolation: isolate;
    }
    /* Soft halo behind the doctor */
    .stage::before {
        content:''; position: absolute;
        left: 50%; bottom: 4%;
        width: 92%; aspect-ratio: 1 / 1;
        max-width: 580px;
        transform: translateX(-50%);
        background: radial-gradient(circle at 50% 55%, #DBEAFE 0%, #EFF6FF 45%, rgba(255,255,255,0) 70%);
        pointer-events: none;
        z-index: 0;
    }
    /* Subtle ground shadow under figure */
    .stage::after {
        content:''; position: absolute;
        left: 50%; bottom: 6%;
        width: 60%; height: 22px;
        transform: translateX(-50%);
        background: radial-gradient(ellipse at center, rgba(15,23,42,.12) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
        filter: blur(2px);
    }
    .doc {
        position: absolute; inset: 0;
        display: flex; align-items: flex-end; justify-content: center;
        z-index: 1;
    }
    .doc img {
        height: 98%;
        width: auto;
        max-width: 100%;
        object-fit: contain;
        object-position: center bottom;
        filter: drop-shadow(0 22px 36px rgba(15,23,42,.18));
        user-select: none;
        -webkit-user-drag: none;
    }

    /* ── Floating cards ── only in corners, never on the doctor */
    .fc {
        position: absolute; z-index: 3;
        background: #fff; border: 1px solid var(--c-line);
        border-radius: var(--r-lg);
        box-shadow: var(--shadow-lg);
        padding: 1rem 1.1rem;
    }

    /* Top right — Today's Appointments (above doctor's head, off body) */
    .fc-appt { top: 4%; right: 3%; width: 270px; }
    .fc-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .85rem; }
    .fc-title {
        display: inline-flex; align-items: center; gap: .5rem;
        font-size: .82rem; font-weight: 700; color: var(--c-ink);
    }
    .fc-ico {
        width: 26px; height: 26px; border-radius: 7px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .82rem;
    }
    .fc-ico.blue   { background: var(--c-tint);     color: var(--c-primary); }
    .fc-ico.mint   { background: #ECFDF5;          color: #059669; }
    .fc-ico.violet { background: #EDE9FE;          color: #7C3AED; }
    .fc-link { font-size: .76rem; color: var(--c-primary); font-weight: 600; }

    .fc-row { display: flex; align-items: center; gap: .7rem; padding: .35rem 0; }
    .fc-row + .fc-row { border-top: 1px solid var(--c-line-soft); }
    .fc-av {
        width: 30px; height: 30px; border-radius: 50%;
        flex-shrink: 0; object-fit: cover;
        background: var(--c-line-soft); color: var(--c-ink-2);
        font-size: .68rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
        position: relative;
    }
    .fc-av::after {
        content:''; position: absolute; right: -1px; bottom: -1px;
        width: 9px; height: 9px; border-radius: 50%;
        background: #10B981; border: 1.5px solid #fff;
    }
    .fc-av.dot-blue::after   { background: #3B82F6; }
    .fc-av.dot-violet::after { background: #8B5CF6; }
    .fc-name { font-size: .85rem; font-weight: 600; color: var(--c-ink); flex: 1; line-height: 1.2; }
    .fc-time { font-size: .78rem; color: var(--c-muted); font-weight: 500; }

    /* Bottom left — Treatment Plans (in negative space, off doctor's body) */
    .fc-plans { bottom: 6%; left: 3%; width: 215px; }
    .fc-plans .fc-title { color: var(--c-muted); font-size: .76rem; font-weight: 600; margin-bottom: .25rem; }
    .fc-big {
        font-family: 'Public Sans', sans-serif;
        font-size: 2.1rem; font-weight: 800; color: var(--c-ink);
        line-height: 1; letter-spacing: -.04em;
        margin-bottom: .15rem;
    }
    .fc-cap { font-size: .82rem; color: var(--c-muted); }
    .fc-bar {
        height: 4px; border-radius: 4px;
        background: var(--c-line-soft);
        overflow: hidden; margin: .65rem 0 .55rem;
    }
    .fc-bar > i {
        display: block; height: 100%;
        background: linear-gradient(90deg, #10B981, #34D399);
        width: 0;
        transition: width 1.4s var(--ease);
    }
    .fc-trend {
        font-size: .76rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .15rem;
        color: #059669;
    }
    .fc-trend i { font-size: .9rem; }

    /* Bottom right — AI Insights */
    .fc-ai { bottom: 8%; right: 0; width: 230px; }
    .fc-ai .fc-cap.first  { margin-top: .35rem; }
    .fc-spark { height: 32px; margin: .25rem 0 .4rem; }
    .fc-spark svg { width: 100%; height: 100%; display: block; }
    .fc-cta {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .8rem; font-weight: 600; color: var(--c-primary);
    }
    .fc-cta:hover { gap: .5rem; }

    /* ───── SCROLL PROGRESS BAR ───── */
    .scroll-bar {
        position: fixed; top: 0; left: 0; right: 0;
        height: 3px; transform-origin: 0 50%;
        background: linear-gradient(90deg, var(--c-primary) 0%, #60A5FA 50%, var(--c-primary) 100%);
        z-index: 100; transform: scaleX(0);
    }

    /* ───── SECTION HEADER ───── */
    .sec { padding: 5.5rem 0; }
    .sec-hd { max-width: 640px; margin: 0 auto 3rem; text-align: center; }
    .sec-kicker {
        display: inline-block;
        font-size: .76rem; font-weight: 700; color: var(--c-primary);
        letter-spacing: .14em; text-transform: uppercase;
        margin-bottom: .75rem;
    }
    .sec-title {
        font-size: clamp(1.85rem, 3.4vw, 2.6rem);
        font-weight: 800; letter-spacing: -.025em;
        margin-bottom: .65rem;
    }
    .sec-sub { font-size: 1rem; color: var(--c-muted); line-height: 1.65; }

    /* ───── FEATURES ───── */
    .feat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.1rem; }
    .feat {
        background: #fff; border: 1px solid var(--c-line);
        border-radius: var(--r-lg);
        padding: 1.6rem;
        transition: border-color .25s var(--ease), transform .25s var(--ease), box-shadow .25s;
    }
    .feat:hover { border-color: var(--c-primary); transform: translateY(-3px); box-shadow: var(--shadow); }
    .feat-ico {
        width: 44px; height: 44px; border-radius: 11px;
        background: var(--c-tint); color: var(--c-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }
    .feat h4 { font-size: 1.05rem; font-weight: 700; margin-bottom: .35rem; }
    .feat p { font-size: .92rem; color: var(--c-muted); line-height: 1.6; }

    /* ───── HOW IT WORKS ───── */
    .how { background: linear-gradient(180deg, #FFFFFF 0%, #F8FBFF 100%); }
    .step {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 4rem; align-items: center;
        padding: 2rem 0;
    }
    .step + .step { margin-top: 1rem; }
    .step.reverse .step-txt { grid-column: 2; grid-row: 1; }
    .step.reverse .step-vis { grid-column: 1; grid-row: 1; }

    .step-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 10px;
        background: var(--c-tint); color: var(--c-primary);
        font-family: 'Public Sans', sans-serif;
        font-weight: 800; font-size: 1rem;
        margin-bottom: 1rem;
    }
    .step-title { font-size: clamp(1.5rem, 2.4vw, 2rem); font-weight: 800; letter-spacing: -.025em; margin-bottom: .85rem; }
    .step-sub { font-size: 1.02rem; color: var(--c-ink-2); line-height: 1.7; max-width: 460px; margin-bottom: 1.25rem; }
    .step-list { list-style: none; padding: 0; margin: 0; display: grid; gap: .55rem; }
    .step-list li {
        display: inline-flex; align-items: center; gap: .55rem;
        font-size: .94rem; color: var(--c-ink-2);
    }
    .step-list i { color: var(--c-primary); font-size: 1rem; }

    .step-vis {
        position: relative;
        aspect-ratio: 1 / 1;
        max-width: 460px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: visible;
        display: flex; align-items: flex-end; justify-content: center;
        background: radial-gradient(circle at 50% 55%, #DBEAFE 0%, #EFF6FF 55%, rgba(255,255,255,0) 78%);
    }
    .step-vis.tone-mint   { background: radial-gradient(circle at 50% 55%, #D1FAE5 0%, #ECFDF5 55%, rgba(255,255,255,0) 78%); }
    .step-vis.tone-violet { background: radial-gradient(circle at 50% 55%, #EDE9FE 0%, #F5F3FF 55%, rgba(255,255,255,0) 78%); }
    .step-vis::after {
        content:''; position: absolute;
        left: 50%; bottom: 6%;
        width: 50%; height: 16px;
        transform: translateX(-50%);
        background: radial-gradient(ellipse at center, rgba(15,23,42,.12) 0%, transparent 70%);
        filter: blur(2px);
    }
    .step-vis img {
        width: 92%; height: 92%;
        object-fit: contain;
        object-position: center bottom;
        filter: drop-shadow(0 18px 28px rgba(15,23,42,.18));
        position: relative; z-index: 1;
    }

    /* ───── STATS ───── */
    .stats {
        background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 60%, #3B82F6 100%);
        color: #fff; position: relative; overflow: hidden;
    }
    .stats::before {
        content:''; position: absolute; top: -120px; right: -80px;
        width: 380px; height: 380px;
        background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 65%);
        pointer-events: none;
    }
    .stats::after {
        content:''; position: absolute; bottom: -120px; left: -60px;
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(140,198,63,.18) 0%, transparent 65%);
        pointer-events: none;
    }
    .stats .wrap { padding-top: 4rem; padding-bottom: 4rem; position: relative; z-index: 1; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
    .stat { text-align: center; }
    .stat-v {
        font-family: 'Public Sans', sans-serif;
        font-size: clamp(2rem, 3.6vw, 2.8rem);
        font-weight: 800; letter-spacing: -.03em; line-height: 1;
    }
    .stat-l {
        font-size: .82rem; font-weight: 600;
        color: rgba(255,255,255,.78);
        margin-top: .55rem;
        letter-spacing: .06em; text-transform: uppercase;
    }

    /* ───── FINAL CTA ───── */
    .finalcta {
        position: relative; overflow: hidden;
        background: var(--c-ink);
        border-radius: var(--r-xl);
        margin: 4.5rem auto;
        padding: 3rem 3rem;
        color: #fff;
        display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; align-items: center;
    }
    .finalcta::before {
        content:''; position: absolute; top: -80px; right: -60px;
        width: 320px; height: 320px;
        background: radial-gradient(circle, rgba(59,130,246,.30) 0%, transparent 65%);
        pointer-events: none;
    }
    .finalcta::after {
        content:''; position: absolute; bottom: -100px; left: -60px;
        width: 280px; height: 280px;
        background: radial-gradient(circle, rgba(140,198,63,.16) 0%, transparent 65%);
        pointer-events: none;
    }
    .finalcta-body { position: relative; z-index: 1; }
    .finalcta-body h2 { color: #fff; font-size: clamp(1.5rem, 2.6vw, 2rem); font-weight: 800; margin-bottom: .55rem; letter-spacing: -.025em; }
    .finalcta-body p { color: rgba(255,255,255,.72); font-size: .98rem; max-width: 480px; margin-bottom: 1.4rem; }
    .finalcta-actions { display: flex; gap: .65rem; flex-wrap: wrap; }
    .btn-on-dark { background: rgba(255,255,255,.10); color: #fff; border-color: rgba(255,255,255,.20); }
    .btn-on-dark:hover { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.40); color: #fff; }
    .finalcta-vis {
        position: relative; z-index: 1;
        aspect-ratio: 1 / 1;
        max-width: 260px; margin-left: auto;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(59,130,246,.35), 0 0 0 4px rgba(255,255,255,.06);
    }
    .finalcta-vis img {
        width: 100%; height: 100%;
        object-fit: cover;
        object-position: center;
    }

    /* ───── FOOTER ───── */
    .foot { padding: 1.5rem 0; border-top: 1px solid var(--c-line-soft); }
    .foot-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; font-size: .85rem; color: var(--c-muted); }
    .foot a:hover { color: var(--c-primary); }

    /* ───── RESPONSIVE ───── */
    @media (max-width: 1100px) {
        .fc-appt  { right: 0; }
        .fc-plans { left: 0; }
    }
    @media (max-width: 960px) {
        .hero { padding: 2rem 0 1.5rem; }
        .hero-grid { grid-template-columns: 1fr; gap: 2rem; }
        .stage { min-height: 520px; }
        .sec { padding: 4rem 0; }
        .feat-grid { grid-template-columns: 1fr; }
        .step { grid-template-columns: 1fr; gap: 2rem; padding: 1rem 0; }
        .step.reverse .step-txt,
        .step.reverse .step-vis { grid-column: 1; grid-row: auto; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 2rem; }
        .finalcta { grid-template-columns: 1fr; padding: 2.5rem 1.75rem; }
        .finalcta-vis { display: none; }
    }
    @media (max-width: 640px) {
        .menu { display: none; }
        .nav-row { height: 60px; }
        .feats { grid-template-columns: 1fr; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation: none !important; transition: none !important; }
        .anim, .anim-zoom, .anim-left, .anim-right { opacity: 1 !important; transform: none !important; }
    }
    </style>
</head>
<body>

    {{-- SCROLL PROGRESS BAR --}}
    <div class="scroll-bar" id="scrollBar"></div>

    {{-- NAV --}}
    <header class="nav">
        <div class="wrap nav-row">
            <a class="brand" href="/">
                <span class="brand-mark">
                    <svg width="20" height="20" viewBox="0 0 64 64" fill="none" stroke="#94A3B8" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#fff"/>
                        <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7"/>
                    </svg>
                </span>
                <span class="brand-text">
                    <span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span>
                </span>
            </a>

            <nav class="menu">
                <a href="#features">Features</a>
                <a href="#how">How it works</a>
            </nav>

            <div class="nav-actions">
                <a href="{{ route('login') }}" class="btn btn-outline">Log in</a>
                <a href="/register/doctor" class="btn btn-primary">Get started</a>
            </div>
        </div>
    </header>

    {{-- HERO --}}
    <section class="hero">
        <div class="wrap hero-grid">
            <div>
                <span class="pill anim" data-anim="up">
                    <i class="bi bi-stars"></i> The all-in-one platform for orthodontists
                </span>
                <h1 class="anim" data-anim="up">
                    Orthodontics,<br>
                    built into your<br>
                    dental practice.
                </h1>
                <p class="hero-sub anim" data-anim="up">
                    Orthobrain helps orthodontists manage patients, streamline workflows,
                    and grow their practice with confidence.
                </p>
                <div class="feats anim" data-anim="up">
                    <span><i class="bi bi-check-circle-fill"></i> Smart patient management</span>
                    <span><i class="bi bi-check-circle-fill"></i> Seamless scheduling</span>
                    <span><i class="bi bi-check-circle-fill"></i> AI-powered insights</span>
                    <span><i class="bi bi-check-circle-fill"></i> Secure &amp; compliant</span>
                </div>
                <div class="ctas anim" data-anim="up">
                    <a href="/register/doctor" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-right"></i> Get started free
                    </a>
                </div>
                <div class="cc anim" data-anim="up">
                    <i class="bi bi-shield-check"></i> No credit card required
                </div>
                <div class="trust anim" data-anim="up">
                    <div class="stack">
                        <img src="{{ asset('images/landing/l5.png') }}" alt=""/>
                        <img src="{{ asset('images/landing/l6.png') }}" alt=""/>
                        <img src="{{ asset('images/landing/l7.png') }}" alt=""/>
                        <span class="more">2K+</span>
                    </div>
                    <div class="trust-text">
                        Trusted by 2,000+<br>orthodontists worldwide
                    </div>
                </div>
            </div>

            <div class="stage anim-zoom" data-anim="zoom">
                <div class="doc">
                    <img src="{{ asset('images/auth/doctor.png') }}" alt="Orthobrain clinician"/>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="sec" id="features">
        <div class="wrap">
            <div class="sec-hd">
                <span class="sec-kicker anim" data-anim="up">Comprehensive platform</span>
                <h2 class="sec-title anim" data-anim="up">Everything in one workspace</h2>
                <p class="sec-sub anim" data-anim="up">Designed around how clinicians actually work — fewer clicks, fewer tabs, more time in chair.</p>
            </div>
            <div class="feat-grid">
                <div class="feat anim" data-anim="up">
                    <span class="feat-ico"><i class="bi bi-clipboard2-pulse"></i></span>
                    <h4>Case submission &amp; planning</h4>
                    <p>Guided intake for photos, x-rays, and prescriptions — clinical reviewer returns a treatment plan in under 24 hours.</p>
                </div>
                <div class="feat anim" data-anim="up" data-delay="0.08">
                    <span class="feat-ico"><i class="bi bi-stars"></i></span>
                    <h4>AI smile preview</h4>
                    <p>Automated photo quality checks plus AI-generated before/after smile visualizations to share with patients.</p>
                </div>
                <div class="feat anim" data-anim="up" data-delay="0.16">
                    <span class="feat-ico"><i class="bi bi-buildings"></i></span>
                    <h4>Multi-practice workflow</h4>
                    <p>Belong to multiple practices, switch context with one click, and keep cases organised per location.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="sec how" id="how">
        <div class="wrap">
            <div class="sec-hd">
                <span class="sec-kicker anim" data-anim="up">How it works</span>
                <h2 class="sec-title anim" data-anim="up">From submission to smile, in three steps</h2>
                <p class="sec-sub anim" data-anim="up">A workflow built around your day — not the other way around.</p>
            </div>

            <div class="step">
                <div class="step-txt">
                    <span class="step-num anim" data-anim="up">1</span>
                    <h3 class="step-title anim" data-anim="up">Submit your case</h3>
                    <p class="step-sub anim" data-anim="up">
                        Upload photos, x-rays, and prescriptions through our guided intake. Everything you need to capture, in the right order.
                    </p>
                    <ul class="step-list anim" data-anim="up">
                        <li><i class="bi bi-check-circle-fill"></i> Photo quality checks</li>
                        <li><i class="bi bi-check-circle-fill"></i> Auto-saved drafts</li>
                        <li><i class="bi bi-check-circle-fill"></i> Pre-filled prescription</li>
                    </ul>
                </div>
                <div class="step-vis anim-zoom" data-anim="zoom">
                    <img src="{{ asset('images/landing/a1.png') }}" alt=""/>
                </div>
            </div>

            <div class="step reverse">
                <div class="step-txt">
                    <span class="step-num anim" data-anim="up">2</span>
                    <h3 class="step-title anim" data-anim="up">Get your treatment plan</h3>
                    <p class="step-sub anim" data-anim="up">
                        Our clinical team returns a personalized plan in under 24 hours — clear, structured, ready to discuss with the patient.
                    </p>
                    <ul class="step-list anim" data-anim="up">
                        <li><i class="bi bi-check-circle-fill"></i> 24-hour turnaround</li>
                        <li><i class="bi bi-check-circle-fill"></i> Specialist review</li>
                        <li><i class="bi bi-check-circle-fill"></i> Patient-friendly summary</li>
                    </ul>
                </div>
                <div class="step-vis tone-mint anim-zoom" data-anim="zoom">
                    <img src="{{ asset('images/landing/a2.png') }}" alt=""/>
                </div>
            </div>

            <div class="step">
                <div class="step-txt">
                    <span class="step-num anim" data-anim="up">3</span>
                    <h3 class="step-title anim" data-anim="up">Treat with confidence</h3>
                    <p class="step-sub anim" data-anim="up">
                        Track every case from start to finish. Live status, automated updates, and one place for the whole team.
                    </p>
                    <ul class="step-list anim" data-anim="up">
                        <li><i class="bi bi-check-circle-fill"></i> Live pipeline view</li>
                        <li><i class="bi bi-check-circle-fill"></i> Automated reminders</li>
                        <li><i class="bi bi-check-circle-fill"></i> Team-wide visibility</li>
                    </ul>
                </div>
                <div class="step-vis tone-violet anim-zoom" data-anim="zoom">
                    <img src="{{ asset('images/landing/a3.png') }}" alt=""/>
                </div>
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="stats">
        <div class="wrap">
            <div class="stats-grid">
                <div class="stat anim" data-anim="up">
                    <div class="stat-v" data-count-to="2000" data-count-suffix="+">0</div>
                    <div class="stat-l">Doctors onboarded</div>
                </div>
                <div class="stat anim" data-anim="up" data-delay="0.08">
                    <div class="stat-v" data-count-to="24" data-count-suffix="h">0</div>
                    <div class="stat-l">Avg. turnaround</div>
                </div>
                <div class="stat anim" data-anim="up" data-delay="0.16">
                    <div class="stat-v" data-count-to="98" data-count-suffix="%">0</div>
                    <div class="stat-l">Doctor satisfaction</div>
                </div>
                <div class="stat anim" data-anim="up" data-delay="0.24">
                    <div class="stat-v" data-count-to="50" data-count-suffix=" states">0</div>
                    <div class="stat-l">Nationwide</div>
                </div>
            </div>
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section id="contact">
        <div class="wrap">
            <div class="finalcta anim" data-anim="up">
                <div class="finalcta-body">
                    <h2>Ready to see it in your practice?</h2>
                    <p>Create a free doctor account in under two minutes — no credit card, no setup call.</p>
                    <div class="finalcta-actions">
                        <a href="/register/doctor" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-right"></i> Get started free
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-on-dark btn-lg">Sign in</a>
                    </div>
                </div>
                <div class="finalcta-vis anim-zoom" data-anim="zoom">
                    <img src="{{ asset('images/landing/l2.png') }}" alt=""/>
                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="foot">
        <div class="wrap foot-row">
            <div>&copy; {{ date('Y') }} orthobrain</div>
            <div>
                <a href="{{ route('login') }}">Sign in</a>
                &nbsp;·&nbsp;
                <a href="/register/doctor">Get started</a>
            </div>
        </div>
    </footer>

    <script type="module">
        import { animate, inView, scroll } from "https://cdn.jsdelivr.net/npm/motion@11.11.17/+esm";

        const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        if (reduce) {
            document.querySelectorAll('.anim, .anim-zoom, .anim-left, .anim-right').forEach(el => {
                el.style.opacity = 1; el.style.transform = 'none';
            });
            document.querySelectorAll('[data-count-to]').forEach(el => {
                const t = parseFloat(el.dataset.countTo);
                el.textContent = t.toLocaleString() + (el.dataset.countSuffix || '');
            });
        } else {
            // ── Reveal helper ──
            const reveal = (el) => {
                const type  = el.dataset.anim || 'up';
                const delay = parseFloat(el.dataset.delay) || 0;
                let from = { y: 18, x: 0, scale: 1 };
                if (type === 'left')  from = { y: 0, x: -22, scale: 1 };
                if (type === 'right') from = { y: 0, x:  22, scale: 1 };
                if (type === 'zoom')  from = { y: 0, x:   0, scale: 0.94 };
                animate(el,
                    { opacity: [0, 1], x: [from.x, 0], y: [from.y, 0], scale: [from.scale, 1] },
                    { duration: 0.8, delay, easing: [0.22, 1, 0.36, 1] }
                );
            };

            // ── Hero entrance: lightly staggered ──
            document.querySelectorAll('.hero [data-anim]').forEach((el, i) => {
                setTimeout(() => reveal(el), 80 + i * 70);
            });

            // ── Below-fold reveals on scroll ──
            document.querySelectorAll('.sec [data-anim], .stats [data-anim], #contact [data-anim]')
                .forEach(el => inView(el, () => reveal(el), { amount: 0.2 }));

            // ── Top scroll progress bar ──
            const bar = document.getElementById('scrollBar');
            if (bar) {
                scroll(animate(bar, { transform: ['scaleX(0)', 'scaleX(1)'] }, { easing: 'linear' }));
            }

            // ── Hero doctor: continuous gentle floating bob ──
            const heroDoc = document.querySelector('.stage .doc img');
            if (heroDoc) {
                // Wait for entrance, then float forever
                setTimeout(() => {
                    animate(heroDoc,
                        { transform: ['translateY(0px)', 'translateY(-12px)', 'translateY(0px)'] },
                        { duration: 5.5, repeat: Infinity, easing: 'ease-in-out' }
                    );
                }, 1200);
            }

            // ── Hero stage: subtle parallax on scroll ──
            const stage = document.querySelector('.hero .stage');
            if (stage) {
                scroll(
                    animate(stage, { transform: ['translateY(0px)', 'translateY(-40px)'] }, { easing: 'linear' }),
                    { target: stage, offset: ['start end', 'end start'] }
                );
            }

            // ── How-it-works step images: gentle bob, alternating phase ──
            document.querySelectorAll('.step-vis img').forEach((img, i) => {
                inView(img, () => {
                    setTimeout(() => {
                        animate(img,
                            { transform: ['translateY(0px)', 'translateY(-10px)', 'translateY(0px)'] },
                            { duration: 4 + i * 0.3, repeat: Infinity, easing: 'ease-in-out' }
                        );
                    }, 600);
                }, { amount: 0.4 });
            });

            // ── Final CTA doctor: subtle bob ──
            const ctaImg = document.querySelector('.finalcta-vis img');
            if (ctaImg) {
                inView(ctaImg, () => {
                    setTimeout(() => {
                        animate(ctaImg,
                            { transform: ['translateY(0px)', 'translateY(-8px)', 'translateY(0px)'] },
                            { duration: 4.5, repeat: Infinity, easing: 'ease-in-out' }
                        );
                    }, 600);
                }, { amount: 0.4 });
            }

            // ── Count-up numbers ──
            document.querySelectorAll('[data-count-to]').forEach(el => {
                inView(el, () => {
                    const target = parseFloat(el.dataset.countTo);
                    const suffix = el.dataset.countSuffix || '';
                    const start  = performance.now();
                    const dur    = 1500;
                    function tick(now) {
                        const p = Math.min((now - start) / dur, 1);
                        const e = 1 - Math.pow(1 - p, 3);
                        el.textContent = Math.floor(e * target).toLocaleString() + suffix;
                        if (p < 1) requestAnimationFrame(tick);
                        else el.textContent = target.toLocaleString() + suffix;
                    }
                    requestAnimationFrame(tick);
                }, { amount: 0.6 });
            });
        }
    </script>

</body>
</html>
