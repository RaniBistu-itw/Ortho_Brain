<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>orthobrain — Orthodontics for Your Dental Practice</title>

    <link rel="apple-touch-icon" href="{{ asset('vuexy/images/ico/favicon-32x32.png') }}"/>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('vuexy/images/ico/favicon.ico') }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>

    <style>
    :root {
        --ob-primary:      #005d90;
        --ob-primary-hv:   #004b74;
        --ob-primary-soft: #cde5ff;
        --ob-teal:         #0EA5C5;
        --ob-mint:         #006c48;
        --ob-mint-soft:    #92f7c3;
        --ob-ink:          #191c1d;
        --ob-ink-soft:     #404850;
        --ob-muted:        #707881;
        --ob-border:       #e1e3e4;
        --ob-border-soft:  #eef0f2;
        --ob-bg:           #f8f9fa;
        --ob-surface-low:  #f3f4f5;
        --ob-white:        #ffffff;
        --ob-shadow:       0 4px 20px rgba(0,93,144,.06), 0 1px 3px rgba(0,93,144,.04);
        --ob-shadow-lg:    0 24px 60px rgba(0,93,144,.14), 0 4px 12px rgba(0,93,144,.05);
        --ob-shadow-xl:    0 32px 80px rgba(0,93,144,.22);
        --ob-r:            .5rem;
        --ob-r-lg:         .75rem;
        --ob-r-xl:         1.25rem;
        --ob-r-2xl:        1.75rem;
        --ob-container:    1200px;
    }

    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, sans-serif;
        background: var(--ob-bg);
        color: var(--ob-ink);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        font-size: 16px;
    }
    h1,h2,h3,h4 {
        font-family: Manrope, Inter, sans-serif;
        color: var(--ob-ink);
        letter-spacing: -.02em;
        line-height: 1.15;
        margin: 0;
    }
    p { margin: 0; }
    a { color: inherit; text-decoration: none; }
    img { max-width: 100%; display: block; }
    button { font-family: inherit; }

    /* Initial state for animated elements; Motion One sets final state on entry */
    .anim { opacity: 0; transform: translateY(24px); }
    .anim-left  { opacity: 0; transform: translateX(-28px); }
    .anim-right { opacity: 0; transform: translateX(28px); }
    .anim-zoom  { opacity: 0; transform: scale(.96); }

    /* Container */
    .ob-container { max-width: var(--ob-container); margin: 0 auto; padding: 0 1.5rem; }

    /* ═══════ NAV ═════════════════════════════════════════════ */
    .ob-nav {
        position: sticky; top: 0; z-index: 50;
        background: rgba(255,255,255,.82);
        backdrop-filter: saturate(180%) blur(16px);
        border-bottom: 1px solid var(--ob-border-soft);
    }
    .ob-nav-row {
        display: flex; align-items: center; justify-content: space-between;
        height: 72px;
    }
    .ob-brand { display: inline-flex; align-items: center; gap: .55rem; }
    .ob-brand-mark {
        width: 34px; height: 34px; border-radius: 10px;
        background: linear-gradient(135deg, var(--ob-primary) 0%, var(--ob-teal) 100%);
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff;
        box-shadow: 0 6px 14px rgba(0,93,144,.28);
    }
    .ob-brand-text { font-family: Manrope; font-weight: 800; font-size: 1.18rem; letter-spacing: -.5px; }
    .ob-brand-text .ob-brain { color: var(--ob-primary); }

    .ob-nav-menu { display: flex; align-items: center; gap: .3rem; }
    .ob-nav-link {
        font-size: .9rem; font-weight: 600; color: var(--ob-ink-soft);
        padding: .6rem .9rem; border-radius: var(--ob-r);
        transition: color .15s, background .15s;
    }
    .ob-nav-link:hover { color: var(--ob-primary); background: var(--ob-primary-soft); }

    .ob-nav-actions { display: flex; align-items: center; gap: .55rem; }

    .ob-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: .5rem;
        padding: .7rem 1.25rem; border-radius: var(--ob-r);
        font-family: Manrope, Inter, sans-serif;
        font-weight: 700; font-size: .9rem; letter-spacing: .02em;
        border: 1px solid transparent; cursor: pointer;
        transition: transform .15s ease, background .15s, box-shadow .2s, color .15s, border-color .15s;
        white-space: nowrap;
    }
    .ob-btn i { font-size: 1rem; line-height: 1; }
    .ob-btn-primary {
        background: var(--ob-primary); color: #fff;
        box-shadow: 0 6px 16px rgba(0,93,144,.25);
    }
    .ob-btn-primary:hover { background: var(--ob-primary-hv); transform: translateY(-2px); box-shadow: 0 10px 22px rgba(0,93,144,.35); color: #fff; }
    .ob-btn-ghost { background: transparent; color: var(--ob-ink); }
    .ob-btn-ghost:hover { color: var(--ob-primary); background: var(--ob-primary-soft); }
    .ob-btn-outline {
        background: var(--ob-white); color: var(--ob-ink);
        border-color: var(--ob-border);
    }
    .ob-btn-outline:hover { border-color: var(--ob-primary); color: var(--ob-primary); transform: translateY(-2px); }
    .ob-btn-lg { padding: .9rem 1.6rem; font-size: .95rem; border-radius: var(--ob-r-lg); }
    .ob-btn-mint { background: var(--ob-mint); color: #fff; box-shadow: 0 6px 16px rgba(0,108,72,.28); }
    .ob-btn-mint:hover { background: #005235; transform: translateY(-2px); color: #fff; }

    /* ═══════ HERO ════════════════════════════════════════════ */
    .ob-hero { position: relative; padding: 4.5rem 0 5rem; overflow: hidden; }
    .ob-hero::before {
        content:''; position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 900px 540px at 10% -5%, rgba(148,204,255,.35), transparent 60%),
            radial-gradient(ellipse 680px 460px at 95% 8%, rgba(146,247,195,.18), transparent 65%);
        pointer-events: none;
    }
    .ob-hero-grid {
        position: relative;
        display: grid; grid-template-columns: 1.05fr .95fr; gap: 4rem; align-items: center;
    }
    .ob-eyebrow {
        display: inline-flex; align-items: center; gap: .45rem;
        font-family: Inter; font-size: .72rem; font-weight: 700;
        color: var(--ob-mint); letter-spacing: .12em; text-transform: uppercase;
        background: var(--ob-mint-soft);
        padding: .42rem .85rem; border-radius: 100px;
        margin-bottom: 1.4rem;
    }
    .ob-eyebrow i { font-size: .9rem; }
    .ob-hero-title {
        font-size: clamp(2.5rem, 5.2vw, 4.2rem);
        font-weight: 800; line-height: 1.06;
        letter-spacing: -.03em;
        color: var(--ob-ink);
        margin-bottom: 1.35rem;
    }
    .ob-hero-title em {
        font-style: normal;
        background: linear-gradient(135deg, var(--ob-primary) 0%, var(--ob-teal) 100%);
        -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .ob-hero-sub {
        font-size: 1.12rem; color: var(--ob-ink-soft);
        max-width: 530px; margin-bottom: 2rem;
    }
    .ob-hero-ctas { display: flex; flex-wrap: wrap; gap: .75rem; margin-bottom: 2.2rem; }
    .ob-hero-trust {
        display: flex; align-items: center; gap: 1.5rem;
        font-size: .87rem; color: var(--ob-muted);
        flex-wrap: wrap;
    }
    .ob-hero-trust-item { display: inline-flex; align-items: center; gap: .4rem; font-weight: 500; }
    .ob-hero-trust-item i { color: var(--ob-mint); font-size: 1.05rem; }

    /* Hero visual */
    .ob-hero-visual { position: relative; }
    .ob-hero-photo {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 5;
        border-radius: var(--ob-r-2xl);
        overflow: hidden;
        box-shadow: var(--ob-shadow-xl);
    }
    .ob-hero-photo img {
        width: 100%; height: 100%; object-fit: cover;
        transform: scale(1.08);
        transition: transform .5s ease;
    }
    .ob-hero-visual:hover .ob-hero-photo img { transform: scale(1.12); }
    .ob-hero-photo::after {
        content:''; position: absolute; inset: 0;
        background: linear-gradient(160deg, transparent 55%, rgba(0,93,144,.18) 100%);
        pointer-events: none;
    }

    .ob-hero-floater {
        position: absolute; z-index: 2;
        background: #fff; border-radius: var(--ob-r-lg);
        padding: .9rem 1.1rem;
        box-shadow: var(--ob-shadow-lg);
        border: 1px solid var(--ob-border-soft);
    }
    .ob-hero-floater-a {
        top: 6%; left: -8%;
        display: flex; align-items: center; gap: .8rem;
    }
    .ob-hero-floater-b {
        bottom: 10%; right: -6%;
        min-width: 220px;
    }
    .ob-floater-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }
    .ob-floater-icon--mint { background: var(--ob-mint-soft); color: var(--ob-mint); }
    .ob-floater-icon--blue { background: var(--ob-primary-soft); color: var(--ob-primary); }
    .ob-floater-label { font-family: Inter; font-size: .7rem; font-weight: 700; color: var(--ob-muted); letter-spacing: .08em; text-transform: uppercase; }
    .ob-floater-val   { font-family: Manrope; font-size: 1.45rem; font-weight: 800; color: var(--ob-ink); line-height: 1; margin-top: .15rem; letter-spacing: -.02em; }
    .ob-floater-caption { font-size: .8rem; color: var(--ob-muted); margin-top: .25rem; }
    .ob-floater-row { display: flex; align-items: center; gap: .55rem; font-size: .88rem; font-weight: 600; color: var(--ob-ink); }
    .ob-floater-row + .ob-floater-row { margin-top: .5rem; }
    .ob-floater-row i { color: var(--ob-mint); font-size: 1rem; }

    /* ═══════ SECTION HEADER ══════════════════════════════════ */
    .ob-section { padding: 5.5rem 0; position: relative; }
    .ob-section-hd { text-align: center; max-width: 700px; margin: 0 auto 3rem; }
    .ob-kicker {
        display: inline-block; font-family: Inter;
        font-size: .72rem; font-weight: 700; color: var(--ob-primary);
        letter-spacing: .14em; text-transform: uppercase;
        margin-bottom: .85rem;
    }
    .ob-section-title {
        font-size: clamp(1.8rem, 3.6vw, 2.75rem);
        font-weight: 700; letter-spacing: -.02em;
        margin-bottom: .75rem;
    }
    .ob-section-sub {
        font-size: 1.02rem; color: var(--ob-ink-soft);
        max-width: 620px; margin: 0 auto;
    }

    /* ═══════ EXPERTISE (Services) ════════════════════════════ */
    .ob-exp-grid {
        display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.25rem;
    }
    .ob-exp-hero {
        position: relative; border-radius: var(--ob-r-xl);
        overflow: hidden; min-height: 480px;
        box-shadow: var(--ob-shadow-lg);
    }
    .ob-exp-hero img {
        position: absolute; inset: 0;
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .7s ease;
    }
    .ob-exp-hero:hover img { transform: scale(1.05); }
    .ob-exp-hero::before {
        content:''; position: absolute; inset: 0;
        background: linear-gradient(180deg, transparent 40%, rgba(0,32,54,.85) 100%);
        z-index: 1;
    }
    .ob-exp-hero-body {
        position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
        padding: 2rem;
        color: #fff;
    }
    .ob-exp-hero-tag {
        display: inline-block;
        background: rgba(255,255,255,.15); backdrop-filter: blur(8px);
        color: #fff; padding: .3rem .75rem; border-radius: 100px;
        font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        margin-bottom: .85rem;
    }
    .ob-exp-hero h3 {
        color: #fff;
        font-size: 1.9rem; font-weight: 800;
        margin-bottom: .55rem;
    }
    .ob-exp-hero p {
        color: rgba(255,255,255,.82);
        font-size: .95rem; max-width: 460px;
        margin-bottom: 1.1rem;
    }
    .ob-exp-hero a {
        display: inline-flex; align-items: center; gap: .4rem;
        color: #fff; font-weight: 700;
        border-bottom: 1px solid rgba(255,255,255,.4);
        padding-bottom: .15rem;
        transition: border-color .2s, gap .2s;
    }
    .ob-exp-hero a:hover { border-color: #fff; gap: .6rem; }

    .ob-exp-col { display: flex; flex-direction: column; gap: 1.25rem; }
    .ob-exp-card {
        background: #fff; border-radius: var(--ob-r-xl);
        border: 1px solid var(--ob-border-soft);
        padding: 2rem;
        box-shadow: var(--ob-shadow);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s;
        flex: 1;
    }
    .ob-exp-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--ob-shadow-lg);
        border-color: var(--ob-primary-soft);
    }
    .ob-exp-icon {
        width: 52px; height: 52px; border-radius: 14px;
        background: var(--ob-primary); color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.45rem;
        margin-bottom: 1.1rem;
        box-shadow: 0 8px 18px rgba(0,93,144,.28);
    }
    .ob-exp-card h4 { font-size: 1.25rem; font-weight: 700; margin-bottom: .55rem; }
    .ob-exp-card p { color: var(--ob-ink-soft); font-size: .95rem; margin-bottom: 1.1rem; }
    .ob-exp-link {
        display: inline-flex; align-items: center; gap: .35rem;
        color: var(--ob-primary); font-weight: 700; font-size: .9rem;
        transition: gap .2s;
    }
    .ob-exp-link:hover { gap: .55rem; }

    /* ═══════ STATS BAND ══════════════════════════════════════ */
    .ob-stats {
        background: linear-gradient(135deg, #003b5d 0%, #005d90 55%, #0077b6 100%);
        color: #fff;
        position: relative; overflow: hidden;
    }
    .ob-stats::before {
        content:''; position: absolute;
        top: -120px; right: -80px;
        width: 420px; height: 420px;
        background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 65%);
        pointer-events: none;
    }
    .ob-stats::after {
        content:''; position: absolute;
        bottom: -140px; left: -80px;
        width: 380px; height: 380px;
        background: radial-gradient(circle, rgba(146,247,195,.14) 0%, transparent 65%);
        pointer-events: none;
    }
    .ob-stats .ob-container {
        position: relative; z-index: 1;
        padding-top: 4rem; padding-bottom: 4rem;
    }
    .ob-stats-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;
    }
    .ob-stat { text-align: center; }
    .ob-stat-v {
        font-family: Manrope; font-size: clamp(2rem, 3.8vw, 3rem);
        font-weight: 800; letter-spacing: -.03em; line-height: 1;
    }
    .ob-stat-l {
        font-size: .82rem; font-weight: 600;
        color: rgba(255,255,255,.75);
        margin-top: .6rem;
        letter-spacing: .06em; text-transform: uppercase;
    }

    /* ═══════ TESTIMONIALS ════════════════════════════════════ */
    .ob-testi { background: var(--ob-bg); }
    .ob-testi-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem;
    }
    .ob-testi-card {
        background: #fff; border: 1px solid var(--ob-border-soft);
        border-radius: var(--ob-r-xl);
        padding: 1.75rem;
        box-shadow: var(--ob-shadow);
        display: flex; flex-direction: column;
        transition: transform .25s, box-shadow .25s;
        position: relative;
        overflow: hidden;
    }
    .ob-testi-card:hover { transform: translateY(-4px); box-shadow: var(--ob-shadow-lg); }
    .ob-testi-card::before {
        content: '\201C';
        position: absolute;
        top: -2rem; right: 1rem;
        font-family: Manrope; font-size: 7rem; line-height: 1;
        color: var(--ob-primary-soft);
        opacity: .5;
        pointer-events: none;
    }
    .ob-testi-stars {
        color: #F6B81A; font-size: 1rem; margin-bottom: .85rem;
        display: inline-flex; gap: .1rem;
        position: relative; z-index: 1;
    }
    .ob-testi-quote {
        font-size: .95rem; color: var(--ob-ink-soft); line-height: 1.65;
        margin-bottom: 1.35rem; flex: 1;
        position: relative; z-index: 1;
    }
    .ob-testi-author {
        display: flex; align-items: center; gap: .85rem;
        padding-top: 1rem;
        border-top: 1px solid var(--ob-border-soft);
        position: relative; z-index: 1;
    }
    .ob-testi-avatar {
        width: 46px; height: 46px; border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,93,144,.15);
    }
    .ob-testi-name { font-family: Manrope; font-weight: 700; color: var(--ob-ink); font-size: .95rem; }
    .ob-testi-role { font-size: .78rem; color: var(--ob-muted); letter-spacing: .04em; text-transform: uppercase; font-weight: 600; margin-top: .1rem; }

    /* ═══════ TEAM ════════════════════════════════════════════ */
    .ob-team { background: var(--ob-surface-low); }
    .ob-team-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 2rem; margin-bottom: 2.75rem; }
    .ob-team-head-left { max-width: 620px; }
    .ob-team-head-right a {
        display: inline-flex; align-items: center; gap: .4rem;
        color: var(--ob-primary); font-weight: 700; font-size: .9rem;
        transition: gap .2s;
    }
    .ob-team-head-right a:hover { gap: .6rem; }

    .ob-team-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .ob-team-card {
        background: #fff; border-radius: var(--ob-r-xl);
        border: 1px solid var(--ob-border-soft);
        overflow: hidden;
        display: grid; grid-template-columns: 160px 1fr;
        box-shadow: var(--ob-shadow);
        transition: transform .25s, box-shadow .25s, border-color .25s;
    }
    .ob-team-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--ob-shadow-lg);
        border-color: var(--ob-primary-soft);
    }
    .ob-team-photo { width: 100%; height: 100%; object-fit: cover; }
    .ob-team-body { padding: 1.4rem 1.6rem; display: flex; flex-direction: column; }
    .ob-team-role {
        font-size: .7rem; font-weight: 700;
        color: var(--ob-primary);
        letter-spacing: .12em; text-transform: uppercase;
        margin-bottom: .35rem;
    }
    .ob-team-name { font-family: Manrope; font-weight: 800; font-size: 1.2rem; color: var(--ob-ink); margin-bottom: .45rem; }
    .ob-team-bio { font-size: .9rem; color: var(--ob-ink-soft); flex: 1; margin-bottom: 1rem; line-height: 1.55; }
    .ob-team-link {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .85rem; font-weight: 700; color: var(--ob-primary);
        padding: .45rem .9rem; border-radius: var(--ob-r);
        border: 1px solid var(--ob-border);
        transition: background .2s, border-color .2s, gap .2s;
        width: fit-content;
    }
    .ob-team-link:hover { border-color: var(--ob-primary); background: var(--ob-primary-soft); gap: .55rem; }

    /* ═══════ FINAL CTA ═══════════════════════════════════════ */
    .ob-finalcta {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, #003b5d 0%, #005d90 55%, #0077b6 100%);
        border-radius: var(--ob-r-2xl);
        margin: 5.5rem auto;
        max-width: 1100px;
        padding: 4rem 3rem;
        color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        gap: 2.5rem; flex-wrap: wrap;
        box-shadow: 0 40px 80px rgba(0,93,144,.32);
    }
    .ob-finalcta::before {
        content:''; position: absolute; top: -100px; right: -60px;
        width: 380px; height: 380px;
        background: radial-gradient(circle, rgba(146,247,195,.2) 0%, transparent 65%);
        pointer-events: none;
    }
    .ob-finalcta::after {
        content:''; position: absolute; bottom: -140px; left: -100px;
        width: 420px; height: 420px;
        background: radial-gradient(circle, rgba(148,204,255,.18) 0%, transparent 65%);
        pointer-events: none;
    }
    .ob-finalcta-content { position: relative; z-index: 1; max-width: 620px; }
    .ob-finalcta-content h2 { color: #fff; font-size: clamp(1.6rem, 3vw, 2.1rem); font-weight: 800; margin-bottom: .7rem; }
    .ob-finalcta-content p { color: rgba(255,255,255,.85); font-size: 1.05rem; }
    .ob-finalcta-actions { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: .75rem; }

    /* ═══════ FOOTER ══════════════════════════════════════════ */
    .ob-foot {
        background: #fff; border-top: 1px solid var(--ob-border-soft);
        padding: 2.5rem 0;
    }
    .ob-foot-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1.5rem; flex-wrap: wrap;
    }
    .ob-foot-links { display: flex; gap: 1.75rem; flex-wrap: wrap; }
    .ob-foot-links a { font-size: .88rem; color: var(--ob-muted); font-weight: 500; }
    .ob-foot-links a:hover { color: var(--ob-primary); }
    .ob-foot-copy { font-size: .85rem; color: var(--ob-muted); }

    /* ═══════ RESPONSIVE ══════════════════════════════════════ */
    @media (max-width: 960px) {
        .ob-hero { padding: 3rem 0 4rem; }
        .ob-hero-grid { grid-template-columns: 1fr; gap: 3rem; }
        .ob-exp-grid { grid-template-columns: 1fr; }
        .ob-exp-hero { min-height: 360px; }
        .ob-stats-grid { grid-template-columns: repeat(2,1fr); gap: 2rem; }
        .ob-testi-grid { grid-template-columns: 1fr; }
        .ob-team-head { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .ob-team-grid { grid-template-columns: 1fr; }
        .ob-team-card { grid-template-columns: 130px 1fr; }
        .ob-finalcta { padding: 2.5rem 1.75rem; }
    }
    @media (max-width: 640px) {
        .ob-nav-menu { display: none; }
        .ob-section { padding: 4rem 0; }
        .ob-hero-title { font-size: 2.3rem; }
        .ob-hero-floater-a { left: -4%; }
        .ob-hero-floater-b { right: -2%; }
        .ob-team-card { grid-template-columns: 110px 1fr; }
    }

    /* Reduced motion — respect user preference */
    @media (prefers-reduced-motion: reduce) {
        * { animation: none !important; transition: none !important; }
        .anim, .anim-left, .anim-right, .anim-zoom { opacity: 1 !important; transform: none !important; }
    }
    </style>
</head>
<body>

    {{-- ══════════════════ NAV ══════════════════ --}}
    <header class="ob-nav">
        <div class="ob-container ob-nav-row">
            <a class="ob-brand" href="/">
                <span class="ob-brand-mark">
                    <svg width="20" height="20" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z"/>
                    </svg>
                </span>
                <span class="ob-brand-text">
                    ortho<span class="ob-brain">brain</span>
                </span>
            </a>

            <nav class="ob-nav-menu">
                <a href="#platform"    class="ob-nav-link">Platform</a>
                <a href="#testimonials" class="ob-nav-link">Testimonials</a>
                <a href="#team"        class="ob-nav-link">Team</a>
                <a href="#contact"     class="ob-nav-link">Contact</a>
            </nav>

            <div class="ob-nav-actions">
                <a href="{{ route('login') }}" class="ob-btn ob-btn-ghost">Sign in</a>
                <a href="/register/doctor" class="ob-btn ob-btn-primary">Get started</a>
            </div>
        </div>
    </header>

    {{-- ══════════════════ HERO ══════════════════ --}}
    <section class="ob-hero">
        <div class="ob-container ob-hero-grid">
            <div>
                <span class="ob-eyebrow anim" data-ob-anim="fade-up">
                    <i class="bi bi-award-fill"></i> Trusted by 1,200+ clinicians
                </span>
                <h1 class="ob-hero-title anim" data-ob-anim="fade-up">
                    Orthodontic excellence,<br>
                    <em>built for your practice.</em>
                </h1>
                <p class="ob-hero-sub anim" data-ob-anim="fade-up">
                    orthobrain unifies case submission, treatment planning, and patient tracking
                    into a single, clinically-polished workspace — so your team can focus on smiles,
                    not software.
                </p>
                <div class="ob-hero-ctas anim" data-ob-anim="fade-up">
                    <a href="/register/doctor" class="ob-btn ob-btn-primary ob-btn-lg">
                        Create doctor account <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#platform" class="ob-btn ob-btn-outline ob-btn-lg">
                        <i class="bi bi-play-circle"></i> See the platform
                    </a>
                </div>
                <div class="ob-hero-trust anim" data-ob-anim="fade-up">
                    <span class="ob-hero-trust-item"><i class="bi bi-check-circle-fill"></i> HIPAA-ready</span>
                    <span class="ob-hero-trust-item"><i class="bi bi-check-circle-fill"></i> No setup fees</span>
                    <span class="ob-hero-trust-item"><i class="bi bi-check-circle-fill"></i> Cancel anytime</span>
                </div>
            </div>

            <div class="ob-hero-visual">
                <div class="ob-hero-photo anim-zoom" data-ob-anim="zoom" id="heroPhoto">
                    <img src="{{ asset('images/landing/l1.png') }}" alt="Smiling patient"/>
                </div>

                <div class="ob-hero-floater ob-hero-floater-a anim-left" data-ob-anim="fade-left" data-ob-delay="0.35">
                    <span class="ob-floater-icon ob-floater-icon--mint">
                        <i class="bi bi-check2-circle"></i>
                    </span>
                    <div>
                        <div class="ob-floater-label">Case approved</div>
                        <div class="ob-floater-val" style="font-size: 1rem; font-family: Inter; font-weight: 700;">in 22 hours</div>
                    </div>
                </div>

                <div class="ob-hero-floater ob-hero-floater-b anim-right" data-ob-anim="fade-right" data-ob-delay="0.55">
                    <div class="ob-floater-label">Treatment plans this week</div>
                    <div class="ob-floater-val" data-count-to="142">0</div>
                    <div class="ob-floater-caption">
                        <span style="color: var(--ob-mint); font-weight: 700;">▲ 18%</span> vs. last week
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════ EXPERTISE ══════════════════ --}}
    <section class="ob-section" id="platform">
        <div class="ob-container">
            <div class="ob-section-hd">
                <span class="ob-kicker anim" data-ob-anim="fade-up">Comprehensive Platform</span>
                <h2 class="ob-section-title anim" data-ob-anim="fade-up">Everything your practice needs, in one place</h2>
                <p class="ob-section-sub anim" data-ob-anim="fade-up">
                    From case intake to delivery, orthobrain organises the entire treatment workflow around
                    how clinicians actually work — so your team spends less time in software and more time in chair.
                </p>
            </div>

            <div class="ob-exp-grid">
                <div class="ob-exp-hero anim" data-ob-anim="fade-up">
                    <img src="{{ asset('images/landing/l2.png') }}" alt="Clinical precision"/>
                    <div class="ob-exp-hero-body">
                        <span class="ob-exp-hero-tag">Flagship workflow</span>
                        <h3>Case Submission &amp; Planning</h3>
                        <p>A guided intake for photos, x-rays and prescriptions — then a dedicated reviewer returns a treatment plan in under 24 hours.</p>
                        <a href="#">Explore the workflow <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                <div class="ob-exp-col">
                    <div class="ob-exp-card anim" data-ob-anim="fade-up" data-ob-delay="0.1">
                        <span class="ob-exp-icon"><i class="bi bi-activity"></i></span>
                        <h4>Live treatment tracking</h4>
                        <p>Every case moves through submission, review, approval, and delivery — with real-time pipeline status at a glance.</p>
                        <a href="#" class="ob-exp-link">See how it works <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="ob-exp-card anim" data-ob-anim="fade-up" data-ob-delay="0.2">
                        <span class="ob-exp-icon" style="background: var(--ob-mint);"><i class="bi bi-shield-check"></i></span>
                        <h4>Secure by default</h4>
                        <p>Role-based access, audited uploads, and practice-level data isolation. Your patients' records stay private.</p>
                        <a href="#" class="ob-exp-link">Our security story <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════ STATS BAND ══════════════════ --}}
    <section class="ob-stats">
        <div class="ob-container">
            <div class="ob-stats-grid">
                <div class="ob-stat anim" data-ob-anim="fade-up">
                    <div class="ob-stat-v" data-count-to="1200" data-count-suffix="+">0</div>
                    <div class="ob-stat-l">Doctors onboarded</div>
                </div>
                <div class="ob-stat anim" data-ob-anim="fade-up" data-ob-delay="0.08">
                    <div class="ob-stat-v" data-count-to="24" data-count-suffix="h">0</div>
                    <div class="ob-stat-l">Avg. plan turnaround</div>
                </div>
                <div class="ob-stat anim" data-ob-anim="fade-up" data-ob-delay="0.16">
                    <div class="ob-stat-v" data-count-to="98" data-count-suffix="%">0</div>
                    <div class="ob-stat-l">Doctor satisfaction</div>
                </div>
                <div class="ob-stat anim" data-ob-anim="fade-up" data-ob-delay="0.24">
                    <div class="ob-stat-v" data-count-to="50" data-count-suffix=" states">0</div>
                    <div class="ob-stat-l">Nationwide coverage</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════ TESTIMONIALS ══════════════════ --}}
    <section class="ob-section ob-testi" id="testimonials">
        <div class="ob-container">
            <div class="ob-section-hd">
                <span class="ob-kicker anim" data-ob-anim="fade-up">Doctor Stories</span>
                <h2 class="ob-section-title anim" data-ob-anim="fade-up">Don't just take our word for it</h2>
                <p class="ob-section-sub anim" data-ob-anim="fade-up">
                    Hear from practices that made orthobrain a part of their daily workflow — and never looked back.
                </p>
            </div>

            <div class="ob-testi-grid">
                @php
                    $testimonials = [
                        [
                            'avatar' => 'l5.png', 'name' => 'Dr. Alex Carter', 'role' => 'General dentistry',
                            'quote'  => 'Case turnaround dropped from 3 days to under 24 hours. The pipeline view alone changed how our front office communicates with patients.',
                        ],
                        [
                            'avatar' => 'l6.png', 'name' => 'Dr. Margaret Hale', 'role' => 'Family practice',
                            'quote'  => "After 22 years in practice, I've finally found software that doesn't fight me. The submission flow feels like it was designed by a dentist.",
                        ],
                        [
                            'avatar' => 'l7.png', 'name' => 'Dr. Priya Sharma', 'role' => 'Cosmetic & ortho',
                            'quote'  => 'We added orthodontic services without hiring an in-house specialist. Plans come back clean, fast, and ready to discuss with the patient.',
                        ],
                    ];
                @endphp
                @foreach($testimonials as $i => $t)
                    <div class="ob-testi-card anim" data-ob-anim="fade-up" data-ob-delay="{{ $i * 0.12 }}">
                        <div class="ob-testi-stars">
                            @for($s = 0; $s < 5; $s++)<i class="bi bi-star-fill"></i>@endfor
                        </div>
                        <p class="ob-testi-quote">"{{ $t['quote'] }}"</p>
                        <div class="ob-testi-author">
                            <img class="ob-testi-avatar" src="{{ asset('images/landing/'.$t['avatar']) }}" alt="{{ $t['name'] }}"/>
                            <div>
                                <div class="ob-testi-name">{{ $t['name'] }}</div>
                                <div class="ob-testi-role">{{ $t['role'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════ TEAM ══════════════════ --}}
    <section class="ob-section ob-team" id="team">
        <div class="ob-container">
            <div class="ob-team-head">
                <div class="ob-team-head-left">
                    <span class="ob-kicker anim" data-ob-anim="fade-up">Clinical Advisors</span>
                    <h2 class="ob-section-title anim" data-ob-anim="fade-up">Behind every plan, a specialist</h2>
                    <p class="ob-section-sub anim" data-ob-anim="fade-up" style="text-align: left; margin: 0;">
                        Our clinical advisory team reviews every treatment plan — so your patients always get the benefit of experienced orthodontic judgement.
                    </p>
                </div>
                <div class="ob-team-head-right anim" data-ob-anim="fade-up">
                    <a href="#">Meet the full team <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <div class="ob-team-grid">
                <div class="ob-team-card anim" data-ob-anim="fade-up">
                    <img class="ob-team-photo" src="{{ asset('images/landing/l3.png') }}" alt="Dr. Michael Chen"/>
                    <div class="ob-team-body">
                        <div class="ob-team-role">Lead Clinical Reviewer</div>
                        <div class="ob-team-name">Dr. Michael Chen</div>
                        <p class="ob-team-bio">
                            Over 15 years of orthodontic experience. Leads the clinical review team and sets the quality bar
                            for every plan that leaves our platform.
                        </p>
                        <a href="#" class="ob-team-link">View profile <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="ob-team-card anim" data-ob-anim="fade-up" data-ob-delay="0.12">
                    <img class="ob-team-photo" src="{{ asset('images/landing/l4.png') }}" alt="Dr. Olivia Reynolds"/>
                    <div class="ob-team-body">
                        <div class="ob-team-role">Director of Clinical Strategy</div>
                        <div class="ob-team-name">Dr. Olivia Reynolds</div>
                        <p class="ob-team-bio">
                            Specializes in aesthetic transformations and complex adult cases. Architects the treatment-planning
                            protocols our reviewers use every day.
                        </p>
                        <a href="#" class="ob-team-link">View profile <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════ FINAL CTA ══════════════════ --}}
    <section id="contact">
        <div class="ob-container">
            <div class="ob-finalcta anim" data-ob-anim="fade-up">
                <div class="ob-finalcta-content">
                    <h2>Ready to see it in your practice?</h2>
                    <p>Create a free doctor account in under two minutes — no credit card, no setup call required.</p>
                </div>
                <div class="ob-finalcta-actions">
                    <a href="/register/doctor" class="ob-btn ob-btn-mint ob-btn-lg" style="background:#92f7c3; color:#00734d;">
                        Create free account <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="{{ route('login') }}" class="ob-btn ob-btn-outline ob-btn-lg" style="background: rgba(255,255,255,.1); color: #fff; border-color: rgba(255,255,255,.35);">
                        Sign in
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════ FOOTER ══════════════════ --}}
    <footer class="ob-foot">
        <div class="ob-container ob-foot-row">
            <a class="ob-brand" href="/">
                <span class="ob-brand-mark">
                    <svg width="18" height="18" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z"/>
                    </svg>
                </span>
                <span class="ob-brand-text">ortho<span class="ob-brain">brain</span></span>
            </a>
            <div class="ob-foot-links">
                <a href="#platform">Platform</a>
                <a href="#testimonials">Testimonials</a>
                <a href="#team">Team</a>
                <a href="{{ route('login') }}">Sign in</a>
                <a href="/register/doctor">Get started</a>
            </div>
            <div class="ob-foot-copy">
                &copy; {{ date('Y') }} orthobrain. Orthodontics for Your Dental Practice.
            </div>
        </div>
    </footer>

    {{-- ══════════════════ MOTION ONE ══════════════════ --}}
    <script type="module">
        import { animate, inView, stagger, scroll } from "https://cdn.jsdelivr.net/npm/motion@11.11.17/+esm";

        const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        if (reduce) {
            document.querySelectorAll('.anim, .anim-left, .anim-right, .anim-zoom').forEach(el => {
                el.style.opacity = 1; el.style.transform = 'none';
            });
        } else {

            // ── Hero staggered entrance ──
            const heroItems = document.querySelectorAll('.ob-hero [data-ob-anim]');
            heroItems.forEach((el, i) => {
                const type  = el.dataset.obAnim || 'fade-up';
                const delay = parseFloat(el.dataset.obDelay) || (i * 0.08);
                let from = { opacity: 0, y: 24 };
                if (type === 'fade-left')  from = { opacity: 0, x: -28 };
                if (type === 'fade-right') from = { opacity: 0, x: 28 };
                if (type === 'zoom')       from = { opacity: 0, scale: 0.96 };

                animate(
                    el,
                    { opacity: [0, 1], x: [from.x ?? 0, 0], y: [from.y ?? 0, 0], scale: [from.scale ?? 1, 1] },
                    { duration: 0.85, delay: 0.1 + delay, easing: [0.22, 1, 0.36, 1] }
                );
            });

            // ── Scroll-triggered reveals for sections ──
            document.querySelectorAll('.ob-section [data-ob-anim], .ob-stats [data-ob-anim], #contact [data-ob-anim]').forEach(el => {
                inView(el, () => {
                    const type  = el.dataset.obAnim || 'fade-up';
                    const delay = parseFloat(el.dataset.obDelay) || 0;
                    let from = { opacity: 0, y: 28 };
                    if (type === 'fade-left')  from = { opacity: 0, x: -32 };
                    if (type === 'fade-right') from = { opacity: 0, x: 32 };
                    if (type === 'zoom')       from = { opacity: 0, scale: 0.95 };

                    animate(
                        el,
                        { opacity: [0, 1], x: [from.x ?? 0, 0], y: [from.y ?? 0, 0], scale: [from.scale ?? 1, 1] },
                        { duration: 0.75, delay, easing: [0.22, 1, 0.36, 1] }
                    );
                }, { amount: 0.2 });
            });

            // ── Hero image gentle parallax on scroll ──
            const heroPhoto = document.getElementById('heroPhoto');
            if (heroPhoto) {
                scroll(
                    animate(heroPhoto, { transform: ['translateY(0px)', 'translateY(-48px)'] }, { easing: 'linear' }),
                    { target: heroPhoto, offset: ['start end', 'end start'] }
                );
            }

            // ── Count-up numbers when in view ──
            const counters = document.querySelectorAll('[data-count-to]');
            counters.forEach(el => {
                inView(el, () => {
                    const target   = parseFloat(el.dataset.countTo);
                    const suffix   = el.dataset.countSuffix || '';
                    const duration = 1600;
                    const start    = performance.now();
                    function tick(now) {
                        const p    = Math.min((now - start) / duration, 1);
                        const ease = 1 - Math.pow(1 - p, 3);
                        const v    = Math.floor(ease * target);
                        el.textContent = v.toLocaleString() + suffix;
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
