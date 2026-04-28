@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════
   OrthoBrain Doctor Dashboard  ·  Clinical Command Center
   Palette: Soft Medical Blue · Clinical Teal · Enamel White
   ═══════════════════════════════════════════════════════════════════ */

#ob-dash {
  --d-navy:      #1E3A5F;
  --d-teal:      #0EA5C5;
  --d-teal-dk:   #0B7A94;
  --d-blue:      #2563EB;
  --d-purple:    #7C3AED;
  --d-green:     #059669;
  --d-amber:     #D97706;
  --d-red:       #DC2626;
  --d-slate:     #64748B;
  --d-border:    rgba(14,100,165,.1);
  --d-shadow:    0 2px 16px rgba(14,50,90,.08);
  --d-shadow-lg: 0 6px 36px rgba(14,50,90,.15);
  --d-r:         16px;
  --d-r-sm:      10px;
}

/* Beat the global h1,h2{color:#111!important} override */
body #ob-dash .ob-hero,
body #ob-dash .ob-hero * { color: #fff !important; }
body #ob-dash .ob-hero .ob-hero-eyebrow { color: rgba(255,255,255,.62) !important; }
body #ob-dash .ob-hero .ob-hero-sub     { color: rgba(255,255,255,.72) !important; }
body #ob-dash .ob-inbox,
body #ob-dash .ob-inbox *               { color: #fff; }
body #ob-dash .ob-inbox-title           { color: rgba(255,255,255,.5) !important; }
body #ob-dash .ob-inbox-row-sub         { color: rgba(255,255,255,.55); }

/* ─── Animations ───────────────────────────────────────────── */
@keyframes ob-fade-up {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes ob-pulse-dot {
  0%   { box-shadow: 0 0 0 0 rgba(74,222,128,.7); }
  70%  { box-shadow: 0 0 0 8px rgba(74,222,128,0); }
  100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); }
}
@keyframes ob-tooth-float {
  0%,100% { transform: translateY(0) rotate(-1.2deg); }
  50%     { transform: translateY(-8px) rotate(1.2deg); }
}
@keyframes ob-arch-glow {
  0%,100% { opacity: .35; }
  50%     { opacity: .9; }
}
@keyframes ob-stepper-fill {
  from { width: 0; }
}
@keyframes ob-spark-pulse {
  0%,100% { opacity: 0; transform: scale(.6); }
  50%     { opacity: 1; transform: scale(1.2); }
}
.ob-anim-1 { animation: ob-fade-up .45s ease .05s both; }
.ob-anim-2 { animation: ob-fade-up .45s ease .10s both; }
.ob-anim-3 { animation: ob-fade-up .45s ease .15s both; }
.ob-anim-4 { animation: ob-fade-up .45s ease .20s both; }
.ob-anim-5 { animation: ob-fade-up .45s ease .25s both; }
.ob-anim-6 { animation: ob-fade-up .45s ease .30s both; }
.ob-anim-7 { animation: ob-fade-up .45s ease .35s both; }

/* ─── Hero ─────────────────────────────────────────────────── */
.ob-hero {
  background: linear-gradient(135deg,#164E7A 0%,#1E6FA8 50%,#0EA5C5 100%);
  border-radius: var(--d-r);
  padding: 2rem 2.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 178px;
  box-shadow: var(--d-shadow-lg);
  animation: ob-fade-up .4s ease both;
}
.ob-hero::before {
  content:''; position:absolute; top:-90px; right:-70px;
  width:360px; height:360px;
  background: radial-gradient(circle, rgba(14,165,197,.17) 0%, transparent 68%);
  pointer-events:none;
}
.ob-hero::after {
  content:''; position:absolute; bottom:-90px; left:35%;
  width:260px; height:260px;
  background: radial-gradient(circle, rgba(99,102,241,.12) 0%, transparent 68%);
  pointer-events:none;
}
.ob-hero-left  { position:relative; z-index:2; max-width: 62%; }
.ob-hero-right { position:relative; z-index:2; flex-shrink:0; margin-left:1.5rem; }
.ob-hero-eyebrow {
  font-size:.72rem; font-weight:600; letter-spacing:1.3px; text-transform:uppercase;
  opacity:.6; margin:0 0 .35rem; display:flex; align-items:center; gap:.4rem;
}
.ob-hero-heading {
  font-size:1.85rem; font-weight:800; margin:0 0 .35rem;
  letter-spacing:-.5px; line-height:1.15;
}
.ob-hero-sub { font-size:.88rem; opacity:.68; margin:0 0 1rem; font-weight:400; }
.ob-hero-pills { display:flex; flex-wrap:wrap; gap:.5rem; }
.ob-hero-pill {
  display:inline-flex; align-items:center; gap:.5rem;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.22);
  backdrop-filter: blur(8px);
  border-radius:30px; padding:.3rem 1rem;
  font-size:.7rem; font-weight:700; letter-spacing:.7px; text-transform:uppercase;
}
.ob-hero-pill--live .ob-hero-dot {
  width:8px; height:8px; background:#4ADE80; border-radius:50%;
  animation: ob-pulse-dot 2s infinite;
}
.ob-hero-pill svg { width:12px !important; height:12px !important; }
.ob-tooth-anim { animation: ob-tooth-float 4.5s ease-in-out infinite; filter: drop-shadow(0 6px 18px rgba(14,165,197,.35)); }

/* ─── Doctor Character (hero) ─────────────────────────────── */
.ob-doc-stage {
  position: relative;
  width: 180px; height: 200px;
  display: flex; align-items: flex-end; justify-content: center;
}
.ob-doc-glow {
  position: absolute; inset: 10% 0 0 0;
  background: radial-gradient(ellipse at center, rgba(125,211,252,.35) 0%, transparent 65%);
  filter: blur(8px);
  pointer-events: none;
}
.ob-doc-char {
  position: relative; z-index: 1;
  animation: ob-doc-bob 3.6s ease-in-out infinite;
  filter: drop-shadow(0 10px 18px rgba(8,28,60,.35));
}
.ob-doc-body  { transform-origin: 85px 200px; animation: ob-doc-sway 5s ease-in-out infinite; }
.ob-doc-head  { transform-origin: 85px 110px; animation: ob-doc-nod 4.2s ease-in-out infinite; }
.ob-doc-hand  { transform-origin: 42px 160px; animation: ob-doc-wave 2.2s ease-in-out infinite; }
.ob-doc-eye   { transform-origin: center; animation: ob-doc-blink 4.8s infinite; }
.ob-doc-eye--r { animation-delay: .02s; }
.ob-doc-orbit { transform-origin: 85px 100px; animation: ob-doc-orbit-spin 8s linear infinite; }

@keyframes ob-doc-bob  { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
@keyframes ob-doc-sway { 0%,100% { transform: rotate(-1deg); } 50% { transform: rotate(1deg); } }
@keyframes ob-doc-nod  { 0%,100% { transform: rotate(-2deg); } 50% { transform: rotate(2deg); } }
@keyframes ob-doc-wave {
  0%,100% { transform: rotate(0deg); }
  25%     { transform: rotate(-18deg); }
  75%     { transform: rotate(10deg); }
}
@keyframes ob-doc-blink {
  0%, 92%, 100% { transform: scaleY(1); }
  94%, 98%      { transform: scaleY(.08); }
}
@keyframes ob-doc-orbit-spin {
  0%   { transform: rotate(0deg) translateY(0); }
  50%  { transform: rotate(180deg) translateY(-3px); }
  100% { transform: rotate(360deg) translateY(0); }
}

/* ─── KPI Cards ───────────────────────────────────────────── */
.ob-kpi {
  display:block; border-radius: var(--d-r); padding:1.4rem 1.5rem;
  color:#fff; text-decoration:none; position:relative; overflow:hidden;
  transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s;
  box-shadow: var(--d-shadow);
}
.ob-kpi:hover { transform: translateY(-6px); box-shadow: var(--d-shadow-lg); color:#fff; }
.ob-kpi::after {
  content:''; position:absolute; right:-28px; bottom:-28px;
  width:150px; height:150px; background: rgba(255,255,255,.09);
  border-radius:50%; pointer-events:none;
}
.ob-kpi::before {
  content:''; position:absolute; right:55px; bottom:-60px;
  width:100px; height:100px; background: rgba(255,255,255,.06);
  border-radius:50%; pointer-events:none;
}
.ob-kpi--active   { background: linear-gradient(135deg,#0B7A94 0%,#0EA5C5 60%,#5ECBDD 100%); }
.ob-kpi--review   { background: linear-gradient(135deg,#5B1F9E 0%,#8B5CF6 60%,#B89EF8 100%); }
.ob-kpi--approved { background: linear-gradient(135deg,#0C6B4A 0%,#10A372 60%,#30D49B 100%); }
.ob-kpi--attention{ background: linear-gradient(135deg,#B26010 0%,#E8920A 60%,#FBCA55 100%); }
.ob-kpi-icon-wrap {
  position:absolute; top:1.2rem; right:1.2rem;
  width:46px; height:46px; background: rgba(255,255,255,.16);
  border-radius:12px; display:flex; align-items:center; justify-content:center;
  backdrop-filter: blur(4px);
}
.ob-kpi-icon-wrap svg { width:22px !important; height:22px !important; }
.ob-kpi-badge {
  display:flex; align-items:center; gap:.35rem;
  font-size:.69rem; font-weight:700; letter-spacing:.9px;
  text-transform:uppercase; opacity:.82; margin-bottom:.45rem;
}
.ob-kpi-badge svg { width:11px !important; height:11px !important; }
.ob-kpi-num {
  font-size:2.75rem; font-weight:900; line-height:1;
  letter-spacing:-1.5px; margin:.15rem 0 .4rem; color:#fff;
}
.ob-kpi-sub { font-size:.78rem; opacity:.8; font-weight:500; }

/* ─── White Section Cards ─────────────────────────────────── */
.ob-card {
  background:#fff; border-radius: var(--d-r);
  box-shadow: var(--d-shadow); padding:1.5rem;
  border: 1px solid var(--d-border);
}
/* Last card in a flex-column stack grows to fill its column — keeps
   the stacked side visually aligned with the dental card next to it. */
.col-xl-6.d-flex.flex-column > .ob-card:last-child,
.col-xl-6.d-flex.flex-column > .ob-inbox:last-child {
  flex: 1 1 auto;
}
.ob-card-title {
  display:flex; align-items:center; gap:.5rem;
  font-size:.7rem; font-weight:800; letter-spacing:1.3px;
  text-transform:uppercase; color: var(--d-slate);
  margin-bottom:1.15rem;
}
.ob-card-title-bar { width:4px; height:18px; border-radius:3px; flex-shrink:0; }
.ob-card-more {
  margin-left:auto; font-size:.7rem; font-weight:700;
  text-decoration:none; color: var(--d-teal); letter-spacing:.3px;
  display:inline-flex; align-items:center; gap:.2rem;
}
.ob-card-more svg { width:12px !important; height:12px !important; }

/* ─── Today's Focus Rail ──────────────────────────────────── */
.ob-focus-list { display:flex; flex-direction:column; gap:.55rem; }
.ob-focus-item {
  display:flex; align-items:center; gap:.9rem;
  padding:.8rem .95rem;
  background:#F4F8FD; border:1px solid #E2EAF4;
  border-radius:12px; text-decoration:none; color:inherit;
  transition: background .15s, border-color .15s, transform .15s;
  position:relative;
}
.ob-focus-item:hover {
  background:#EAF2FA; border-color:#C7DAF0; color:inherit;
  transform: translateX(3px);
}
.ob-focus-icon {
  width:40px; height:40px; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0;
}
.ob-focus-icon svg { width:18px !important; height:18px !important; }
.ob-focus--danger  .ob-focus-icon { background:#FEE2E2; color:#DC2626; }
.ob-focus--danger                 { border-left:3px solid #DC2626; }
.ob-focus--warn    .ob-focus-icon { background:#FEF3C7; color:#D97706; }
.ob-focus--warn                   { border-left:3px solid #D97706; }
.ob-focus--success .ob-focus-icon { background:#D1FAE5; color:#059669; }
.ob-focus--success                { border-left:3px solid #059669; }
.ob-focus-body { flex:1; min-width:0; }
.ob-focus-title { font-size:.9rem; font-weight:700; color:#1E293B; line-height:1.2; }
.ob-focus-meta {
  font-size:.73rem; color: var(--d-slate); margin-top:.15rem;
  display:flex; align-items:center; gap:.4rem;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.ob-focus-code {
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size:.7rem; font-weight:700; background:#fff;
  padding:.1rem .45rem; border-radius:6px;
  border:1px solid #E2EAF4; color:#1E293B;
}
.ob-focus-arrow {
  color: var(--d-slate); flex-shrink:0;
  transition: transform .15s, color .15s;
}
.ob-focus-arrow svg { width:16px !important; height:16px !important; }
.ob-focus-item:hover .ob-focus-arrow { color: var(--d-teal); transform: translateX(2px); }

.ob-focus-empty {
  padding:2rem 1rem; text-align:center; color: var(--d-slate);
  background:#F4F8FD; border:1px dashed #C7DAF0; border-radius:12px;
}
.ob-focus-empty svg { width:28px !important; height:28px !important; margin-bottom:.5rem; color:#0EA5C5; }
.ob-focus-empty-title { font-size:.9rem; font-weight:700; color:#1E293B; }
.ob-focus-empty-sub   { font-size:.78rem; margin-top:.25rem; }

/* ─── Priority Inbox (dark card) ──────────────────────────── */
.ob-inbox {
  background: linear-gradient(155deg,#0F172A 0%,#1E293B 100%);
  border-radius: var(--d-r);
  padding:1.5rem; box-shadow: var(--d-shadow-lg);
  position:relative; overflow:hidden;
}
.ob-inbox::before {
  content:''; position:absolute; top:-55px; right:-55px;
  width:210px; height:210px;
  background: radial-gradient(circle, rgba(14,165,197,.15) 0%, transparent 65%);
  pointer-events:none;
}
.ob-inbox::after {
  content:''; position:absolute; bottom:-65px; left:-35px;
  width:190px; height:190px;
  background: radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 65%);
  pointer-events:none;
}
.ob-inbox-title {
  display:flex; align-items:center; gap:.5rem;
  font-size:.7rem; font-weight:800; letter-spacing:1.3px;
  text-transform:uppercase; margin-bottom:1.15rem;
}
.ob-inbox-title-bar { width:4px; height:18px; background:#0EA5C5; border-radius:3px; flex-shrink:0; }
.ob-inbox-list { display:flex; flex-direction:column; gap:.55rem; position:relative; z-index:1; }
.ob-inbox-row {
  display:flex; align-items:center; gap:.85rem;
  background: rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.08);
  border-radius:12px; padding:.75rem .85rem;
  transition: background .15s, border-color .15s;
}
.ob-inbox-row:hover { background: rgba(14,165,197,.1); border-color: rgba(14,165,197,.3); }
.ob-inbox-icon {
  width:36px; height:36px; border-radius:10px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.ob-inbox-icon svg { width:16px !important; height:16px !important; }
.ob-inbox--danger  .ob-inbox-icon { background: rgba(220,38,38,.18);  color:#FCA5A5; }
.ob-inbox--warn    .ob-inbox-icon { background: rgba(217,119,6,.18);  color:#FBBF77; }
.ob-inbox--success .ob-inbox-icon { background: rgba(5,150,105,.18);  color:#6EE7B7; }
.ob-inbox--neutral .ob-inbox-icon { background: rgba(14,165,197,.18); color:#7DD3FC; }
.ob-inbox-body { flex:1; min-width:0; }
.ob-inbox-row-title { font-size:.85rem; font-weight:700; line-height:1.2; color:#fff; }
.ob-inbox-row-sub   { font-size:.72rem; margin-top:.1rem; }
.ob-inbox-action {
  font-size:.7rem; font-weight:700; letter-spacing:.3px;
  background:#0EA5C5; color:#fff; text-decoration:none;
  padding:.35rem .75rem; border-radius:8px;
  transition: background .15s, transform .15s;
}
.ob-inbox-action:hover { background:#0891B2; color:#fff; transform: translateY(-1px); }

.ob-inbox-tooth {
  position:relative; margin-top:1rem; height:110px;
  display:flex; align-items:center; justify-content:center;
}
.ob-inbox-tooth svg { width:72px; height:82px; animation: ob-tooth-float 4s ease-in-out infinite; filter: drop-shadow(0 8px 14px rgba(14,165,197,.25)); }
.ob-inbox-spark { position:absolute; width:4px; height:4px; border-radius:50%;
  background:#7DD3FC; box-shadow: 0 0 8px rgba(125,211,252,.9);
  opacity:0; animation: ob-spark-pulse 2.8s ease-in-out infinite;
}
.ob-inbox-spark.s1 { top:18%; left:30%; animation-delay:0s; }
.ob-inbox-spark.s2 { top:30%; right:26%; animation-delay:.9s; }
.ob-inbox-spark.s3 { bottom:30%; left:28%; animation-delay:1.8s; }

/* ─── 3D Dental Model (Three.js WebGL canvas) ─────────────── */
.ob-mouth-stage {
  position: relative;
  padding: 0;
  background:
    radial-gradient(ellipse 55% 50% at 50% 55%, #F6FAFD 0%, #E6F0F8 55%, #D3E1EE 100%);
  border-radius: 14px;
  overflow: hidden;
  height: 280px;
  display: flex; align-items: center; justify-content: center;
  flex: 0 0 280px;   /* fixed — don't stretch to match the sibling column */
}
.ob-mouth-stage::before {
  /* subtle top-left key-light glow */
  content:''; position:absolute; inset:0;
  background: radial-gradient(ellipse at 30% 18%, rgba(255,255,255,.95) 0%, transparent 55%);
  pointer-events:none;
  z-index:0;
}
.ob-mouth-stage::after {
  /* soft floor contact shadow */
  content:''; position:absolute;
  left: 18%; right: 18%; bottom: 8%;
  height: 18px;
  background: radial-gradient(ellipse at center, rgba(14,50,90,.3) 0%, transparent 70%);
  filter: blur(12px);
  pointer-events:none;
  z-index: 0;
}
.ob-mouth-3d {
  position: relative; z-index: 1;
  width: 100%; height: 100%;
  cursor: grab;
}
.ob-mouth-3d:active { cursor: grabbing; }
.ob-mouth-canvas { display: block; width: 100%; height: 100%; }

.ob-mouth-loader {
  position: absolute; inset: 0;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: .85rem;
  background: transparent;
  z-index: 2;
  pointer-events: none;
  transition: opacity .4s ease;
}
.ob-mouth-loader.is-hidden { opacity: 0; pointer-events: none; }
.ob-mouth-loader-ring {
  width: 44px; height: 44px;
  border-radius: 50%;
  border: 3px solid rgba(14,165,197,.2);
  border-top-color: #0EA5C5;
  animation: ob-mouth-spin-ring 1s linear infinite;
}
.ob-mouth-loader-text {
  font-size: .72rem; font-weight: 700;
  letter-spacing: .6px; text-transform: uppercase;
  color: var(--d-slate);
}
@keyframes ob-mouth-spin-ring { to { transform: rotate(360deg); } }

.ob-mouth-hint {
  position: absolute; bottom: .6rem; right: .9rem;
  z-index: 1;
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: .68rem; font-weight: 700;
  letter-spacing: .5px; text-transform: uppercase;
  color: var(--d-slate);
  background: rgba(255,255,255,.8);
  backdrop-filter: blur(4px);
  padding: .28rem .6rem;
  border-radius: 20px;
  border: 1px solid rgba(14,100,165,.12);
  pointer-events: none;
}
.ob-mouth-hint svg { width: 12px !important; height: 12px !important; }

.ob-arch-wrap { position:relative; }
.dark-layout #ob-dash .ob-mouth-stage {
  background: radial-gradient(ellipse 55% 50% at 50% 55%, #2a3145 0%, #1d2335 100%);
}
.dark-layout #ob-dash .ob-mouth-hint {
  background: rgba(30,40,60,.7); color: #d0d2d6; border-color: rgba(255,255,255,.08);
}
.ob-arch-legend {
  display:flex; flex-wrap:wrap; gap:.85rem;
  margin-top:1rem; padding-top:1rem;
  border-top:1px solid #F1F5F9;
}
.ob-arch-legend-item {
  display:inline-flex; align-items:center; gap:.4rem;
  font-size:.72rem; font-weight:700; color:#1E293B;
}

/* ─── Recent Pipeline (compact strip below 3D viewer) ─────── */
.ob-arch-pipeline {
  margin-top: .85rem;
  display: flex; flex-direction: column;
  flex: 1 1 auto;   /* absorbs whatever vertical space is left in the card */
  min-height: 0;
}
.ob-arch-pipeline-title {
  display: flex; align-items: center; gap: .5rem;
  font-size: .7rem; font-weight: 800; letter-spacing: 1.3px;
  text-transform: uppercase; color: var(--d-slate);
  margin-bottom: .6rem;
}
.ob-arch-pipeline-more {
  margin-left: auto; font-size: .7rem; font-weight: 700;
  text-decoration: none; color: var(--d-teal); letter-spacing: .3px;
}
.ob-arch-pipeline-more:hover { text-decoration: underline; }
.ob-arch-pipeline-list {
  display: flex; flex-direction: column; gap: .4rem;
  flex: 1 1 auto;
}
.ob-arch-pipeline-row {
  display: grid;
  grid-template-columns: auto auto 1fr;
  gap: .75rem;
  align-items: center;
  padding: .55rem .75rem;
  background: #F8FAFC;
  border: 1px solid var(--d-border);
  border-radius: 10px;
  text-decoration: none;
  transition: background .15s ease, border-color .15s ease, transform .15s ease;
}
.ob-arch-pipeline-row:hover {
  background: #F1F5F9;
  border-color: #CBD5E1;
  transform: translateX(2px);
}
.ob-arch-pipeline-code {
  font-size: .78rem; font-weight: 800; color: #1E293B;
  letter-spacing: .3px;
}
.ob-arch-pipeline-status {
  font-size: .62rem; font-weight: 800;
  padding: .18rem .55rem;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: .4px;
  white-space: nowrap;
}
.ob-arch-pipeline-status--submitted { background: #DBEAFE; color: #1D4ED8; }
.ob-arch-pipeline-status--in_review { background: #E0E7FF; color: #4338CA; }
.ob-arch-pipeline-status--approved  { background: #D1FAE5; color: #047857; }
.ob-arch-pipeline-when {
  justify-self: end;
  font-size: .68rem; font-weight: 600;
  color: var(--d-slate);
}
.ob-arch-pipeline-empty {
  display: flex; align-items: center; gap: .6rem;
  padding: 1rem;
  background: #F8FAFC;
  border: 1px dashed var(--d-border);
  border-radius: 10px;
  color: var(--d-slate);
  font-size: .75rem; font-weight: 600;
  flex: 1 1 auto;
}
.ob-arch-pipeline-empty svg { width: 18px !important; height: 18px !important; flex-shrink: 0; }

.dark-layout #ob-dash .ob-arch-pipeline-row {
  background: #283046; border-color: rgba(255,255,255,.08);
}
.dark-layout #ob-dash .ob-arch-pipeline-row:hover { background: #2f3651; }
.dark-layout #ob-dash .ob-arch-pipeline-code { color: #d0d2d6; }
.dark-layout #ob-dash .ob-arch-pipeline-empty {
  background: #283046; border-color: rgba(255,255,255,.1);
}

/* ─── Patient Pulse ───────────────────────────────────────── */
.ob-pulse-head {
  display:flex; align-items:baseline; justify-content:space-between;
  margin-bottom:.6rem; gap:.5rem;
}
.ob-pulse-big {
  font-size:2rem; font-weight:900; line-height:1; color:#1E293B;
  letter-spacing:-.8px;
}
.ob-pulse-delta {
  font-size:.72rem; font-weight:800; letter-spacing:.4px;
  padding:.15rem .55rem; border-radius:30px;
  background:#D1FAE5; color:#059669; text-transform:uppercase;
  display:inline-flex; align-items:center; gap:.2rem;
}
.ob-pulse-delta svg { width:10px !important; height:10px !important; }
.ob-pulse-delta--down { background:#FEE2E2; color:#DC2626; }
.ob-pulse-caption {
  font-size:.75rem; color: var(--d-slate); margin-bottom:.8rem;
}
.ob-pulse-chart { width:100%; height:90px; display:block; }
.ob-pulse-scale {
  display:flex; justify-content:space-between;
  font-size:.62rem; font-weight:700; color: var(--d-slate);
  letter-spacing:.4px; text-transform:uppercase;
  margin-top:.35rem;
}

/* ─── Quick Actions ───────────────────────────────────────── */
.ob-qa-grid {
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  grid-auto-rows: minmax(96px, 1fr);
  gap:.8rem;
}
/* When the QA card is the grow-card in a stack, let the grid fill vertically */
.col-xl-6.d-flex.flex-column > .ob-card:last-child {
  display: flex;
  flex-direction: column;
}
.col-xl-6.d-flex.flex-column > .ob-card:last-child > .ob-qa-grid { flex: 1 1 auto; }
.ob-qa-btn {
  display:flex; flex-direction:column; align-items:center;
  gap:.5rem; padding:1rem .5rem;
  background:#F4F8FD; border:1.5px solid #E2EAF4;
  border-radius: var(--d-r-sm);
  text-decoration:none; color:#1E293B;
  font-size:.74rem; font-weight:700;
  text-align:center; line-height:1.25;
  transition: all .2s cubic-bezier(.34,1.56,.64,1);
}
.ob-qa-btn:hover {
  background:#1E293B; border-color:#1E293B; color:#fff;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(30,41,59,.22);
}
.ob-qa-icon {
  width:44px; height:44px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  transition: background .2s;
}
.ob-qa-icon svg { width:20px !important; height:20px !important; }
.ob-qa-btn:hover .ob-qa-icon { background: rgba(255,255,255,.15) !important; }

/* ─── Dark mode ───────────────────────────────────────────── */
.dark-layout #ob-dash .ob-card          { background:#283046; border-color: rgba(255,255,255,.06); }
.dark-layout #ob-dash .ob-card-title    { color: rgba(255,255,255,.5); }
.dark-layout #ob-dash .ob-focus-item    { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.06); color:#d0d2d6; }
.dark-layout #ob-dash .ob-focus-item:hover { background: rgba(255,255,255,.07); }
.dark-layout #ob-dash .ob-focus-title   { color:#d0d2d6; }
.dark-layout #ob-dash .ob-focus-code    { background: rgba(0,0,0,.25); color:#d0d2d6; border-color: rgba(255,255,255,.08); }
.dark-layout #ob-dash .ob-focus-empty   { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.1); }
.dark-layout #ob-dash .ob-focus-empty-title { color:#d0d2d6; }
.dark-layout #ob-dash .ob-pulse-big     { color:#d0d2d6; }
.dark-layout #ob-dash .ob-qa-btn        { background:#3b4253; border-color: rgba(255,255,255,.08); color:#d0d2d6; }
.dark-layout #ob-dash .ob-qa-btn:hover  { background:#0EA5C5; border-color:#0EA5C5; color:#fff; }
.dark-layout #ob-dash .ob-arch-legend   { border-top-color: rgba(255,255,255,.08); }
.dark-layout #ob-dash .ob-arch-legend-item { color:#d0d2d6; }

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 991.98px) {
  .ob-hero { padding:1.5rem; flex-direction:column; align-items:flex-start; gap:1rem; }
  .ob-hero-left { max-width:100%; }
  .ob-hero-right { margin-left:0; align-self:center; }
  .ob-hero-heading { font-size:1.5rem; }
  .ob-kpi-num { font-size:2.25rem; }
}
</style>
@endpush

@section('content')
@php
    $hour       = now()->hour;
    $greet      = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $firstName  = $doctor?->first_name ?? auth()->user()->name ?? '';
    $heroName   = $firstName ? ', Dr. ' . $firstName : '';
    $practiceNm = $practice?->name;

    // (3D dental model is now rendered as a single image with CSS 3D transforms.)
@endphp

<div id="ob-dash">

    {{-- ══════════════════════════════════════════════════════
         HERO BANNER
    ══════════════════════════════════════════════════════ --}}
    <div class="ob-hero">
        <div class="ob-hero-left">
            <p class="ob-hero-eyebrow">
                <i data-feather="calendar"></i>
                {{ now()->format('l, F j, Y') }}
                &nbsp;·&nbsp;
                <span id="ob-clock"></span>
            </p>
            <h1 class="ob-hero-heading">{{ $greet }}{{ $heroName }}</h1>
            <p class="ob-hero-sub">
                Here's your clinical overview for today
                @if($practiceNm)— practising at <strong>{{ $practiceNm }}</strong>@endif.
            </p>
            <div class="ob-hero-pills">
                <div class="ob-hero-pill ob-hero-pill--live">
                    <span class="ob-hero-dot"></span>
                    Clinic online
                </div>
                @if($attentionCount > 0)
                <div class="ob-hero-pill">
                    <i data-feather="alert-circle"></i>
                    {{ $attentionCount }} need{{ $attentionCount === 1 ? 's' : '' }} attention
                </div>
                @endif
                <div class="ob-hero-pill">
                    <i data-feather="folder"></i>
                    {{ $stats['total'] }} total case{{ $stats['total'] === 1 ? '' : 's' }}
                </div>
            </div>
        </div>

        <div class="ob-hero-right d-none d-lg-flex align-items-end">
            {{-- Animated Doctor Character (bobbing, blinking, waving) --}}
            <div class="ob-doc-stage">
                <div class="ob-doc-glow"></div>
                <svg class="ob-doc-char" width="170" height="190" viewBox="0 0 170 200"
                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <linearGradient id="drCoat" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#FFFFFF"/>
                            <stop offset="100%" stop-color="#D8E6F3"/>
                        </linearGradient>
                        <linearGradient id="drSkin" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#FFD9BA"/>
                            <stop offset="100%" stop-color="#E8B07D"/>
                        </linearGradient>
                        <linearGradient id="drHair" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#3F2A1E"/>
                            <stop offset="100%" stop-color="#241509"/>
                        </linearGradient>
                        <linearGradient id="drScope" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#475569"/>
                            <stop offset="100%" stop-color="#1E293B"/>
                        </linearGradient>
                        <radialGradient id="drCheek" cx="50%" cy="50%" r="50%">
                            <stop offset="0%"   stop-color="#FFB199" stop-opacity=".65"/>
                            <stop offset="100%" stop-color="#FFB199" stop-opacity="0"/>
                        </radialGradient>
                        <filter id="drCharShadow" x="-25%" y="-10%" width="150%" height="130%">
                            <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#0B2541" flood-opacity=".35"/>
                        </filter>
                    </defs>

                    {{-- Ground shadow --}}
                    <ellipse cx="85" cy="196" rx="48" ry="4" fill="rgba(0,0,0,.28)"/>

                    <g class="ob-doc-body" filter="url(#drCharShadow)">
                        {{-- Coat body --}}
                        <path d="M25 200 L35 140 Q50 128 85 128 Q120 128 135 140 L145 200 Z" fill="url(#drCoat)"/>
                        {{-- Coat lapels (V-neck) --}}
                        <path d="M65 131 L85 160 L105 131 L98 128 L85 148 L72 128 Z" fill="#B9CEE2"/>
                        {{-- Pocket --}}
                        <rect x="110" y="162" width="18" height="14" rx="2" fill="none" stroke="#B9CEE2" stroke-width="1.2"/>
                        <rect x="114" y="158" width="3" height="10" rx="1" fill="#0EA5C5"/>
                        <rect x="119" y="158" width="3" height="10" rx="1" fill="#0EA5C5"/>

                        {{-- Stethoscope drape --}}
                        <path d="M70 135 Q58 152 62 172 Q66 185 80 186" stroke="url(#drScope)" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <path d="M100 135 Q112 150 108 170" stroke="url(#drScope)" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <circle cx="80" cy="188" r="6" fill="url(#drScope)"/>
                        <circle cx="80" cy="188" r="3" fill="#0EA5C5"/>
                        <circle cx="108" cy="172" r="3" fill="#475569"/>

                        {{-- Neck --}}
                        <rect x="76" y="110" width="18" height="22" rx="4" fill="url(#drSkin)"/>

                        {{-- Head --}}
                        <g class="ob-doc-head">
                            {{-- Face --}}
                            <ellipse cx="85" cy="80" rx="36" ry="40" fill="url(#drSkin)"/>
                            {{-- Ears --}}
                            <ellipse cx="49" cy="82" rx="5" ry="8" fill="url(#drSkin)"/>
                            <ellipse cx="121" cy="82" rx="5" ry="8" fill="url(#drSkin)"/>

                            {{-- Hair (short, modern) --}}
                            <path d="M52 68 Q54 42 85 40 Q116 42 118 68 Q116 60 108 58 Q102 54 92 54 Q82 58 78 56 Q72 54 66 58 Q58 60 52 68 Z"
                                  fill="url(#drHair)"/>
                            {{-- Forehead medical-headband accent --}}
                            <path d="M54 64 Q85 54 116 64" stroke="#0EA5C5" stroke-width="2.2" fill="none" stroke-linecap="round" opacity=".75"/>
                            <circle cx="85" cy="58" r="2.5" fill="#fff" stroke="#0EA5C5" stroke-width="1.2"/>

                            {{-- Cheeks --}}
                            <ellipse cx="65" cy="92" rx="8" ry="5" fill="url(#drCheek)"/>
                            <ellipse cx="105" cy="92" rx="8" ry="5" fill="url(#drCheek)"/>

                            {{-- Eyes (animated blink via scaleY) --}}
                            <g class="ob-doc-eye ob-doc-eye--l">
                                <ellipse cx="72" cy="82" rx="3.5" ry="4" fill="#1E293B"/>
                                <circle  cx="73" cy="80.5" r="1" fill="#fff"/>
                            </g>
                            <g class="ob-doc-eye ob-doc-eye--r">
                                <ellipse cx="98" cy="82" rx="3.5" ry="4" fill="#1E293B"/>
                                <circle  cx="99" cy="80.5" r="1" fill="#fff"/>
                            </g>

                            {{-- Eyebrows --}}
                            <path d="M67 74 Q72 71 77 74" stroke="#241509" stroke-width="1.6" fill="none" stroke-linecap="round"/>
                            <path d="M93 74 Q98 71 103 74" stroke="#241509" stroke-width="1.6" fill="none" stroke-linecap="round"/>

                            {{-- Nose --}}
                            <path d="M85 88 Q82 96 85 100" stroke="#C99472" stroke-width="1.3" fill="none" stroke-linecap="round"/>

                            {{-- Smile --}}
                            <path d="M74 104 Q85 113 96 104" stroke="#8B3A3A" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                            <path d="M76 105 Q85 110 94 105 L94 106 Q85 108 76 106 Z" fill="#fff"/>
                        </g>

                        {{-- Waving hand (animated) --}}
                        <g class="ob-doc-hand">
                            <path d="M28 152 Q22 140 26 128 Q30 118 38 118 Q45 118 46 126 Q46 138 42 150 Q38 158 28 152 Z"
                                  fill="url(#drSkin)"/>
                            <path d="M26 136 L22 122" stroke="#C99472" stroke-width="1.2" stroke-linecap="round"/>
                            <path d="M34 122 L33 110" stroke="#C99472" stroke-width="1.2" stroke-linecap="round"/>
                            <path d="M42 124 L44 112" stroke="#C99472" stroke-width="1.2" stroke-linecap="round"/>
                            {{-- Sleeve cuff --}}
                            <path d="M25 148 Q34 158 48 154 L44 164 Q34 166 28 160 Z" fill="#D8E6F3"/>
                        </g>
                    </g>

                    {{-- Tiny orbiting tooth accent (keeps brand continuity) --}}
                    <g class="ob-doc-orbit">
                        <svg x="130" y="30" width="28" height="30" viewBox="0 0 30 32">
                            <path d="M6 10 C5 5 8 2 12 2 C14 1 16 5 18 5 C20 5 22 1 24 2 C28 2 27 10 26 14 C25 20 22 26 20 27 C17 28 13 28 10 27 C8 26 7 20 6 10 Z"
                                  fill="#fff" stroke="rgba(14,165,197,.6)" stroke-width=".8"/>
                            <path d="M11 8 Q15 12 19 8" stroke="rgba(14,165,197,.7)" stroke-width=".9" fill="none" stroke-linecap="round"/>
                        </svg>
                    </g>
                </svg>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         KPI ROW (4)
    ══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-2">
        <div class="col-xl-3 col-sm-6 ob-anim-1">
            <a href="{{ route('doctor.cases.index', ['status' => 'ACTIVE']) }}" class="ob-kpi ob-kpi--active">
                <div class="ob-kpi-icon-wrap"><i data-feather="activity"></i></div>
                <div class="ob-kpi-badge"><i data-feather="folder"></i>&nbsp;Active Cases</div>
                <div class="ob-kpi-num" data-count-to="{{ $activeCount }}">{{ $activeCount }}</div>
                <div class="ob-kpi-sub">Currently in your caseload</div>
            </a>
        </div>
        <div class="col-xl-3 col-sm-6 ob-anim-2">
            <a href="{{ route('doctor.cases.index') }}?status=IN_REVIEW" class="ob-kpi ob-kpi--review">
                <div class="ob-kpi-icon-wrap"><i data-feather="eye"></i></div>
                <div class="ob-kpi-badge"><i data-feather="clock"></i>&nbsp;In Review</div>
                <div class="ob-kpi-num" data-count-to="{{ $inReviewCount }}">{{ $inReviewCount }}</div>
                <div class="ob-kpi-sub">Awaiting clinical review</div>
            </a>
        </div>
        <div class="col-xl-3 col-sm-6 ob-anim-3">
            <a href="{{ route('doctor.cases.index') }}?status=APPROVED" class="ob-kpi ob-kpi--approved">
                <div class="ob-kpi-icon-wrap"><i data-feather="check-circle"></i></div>
                <div class="ob-kpi-badge"><i data-feather="thumbs-up"></i>&nbsp;Approved</div>
                <div class="ob-kpi-num" data-count-to="{{ $stats['approved'] }}">{{ $stats['approved'] }}</div>
                <div class="ob-kpi-sub">Plans ready for treatment</div>
            </a>
        </div>
        <div class="col-xl-3 col-sm-6 ob-anim-4">
            <a href="{{ route('doctor.cases.index') }}?status=REJECTED" class="ob-kpi ob-kpi--attention">
                <div class="ob-kpi-icon-wrap"><i data-feather="alert-triangle"></i></div>
                <div class="ob-kpi-badge"><i data-feather="bell"></i>&nbsp;Needs Attention</div>
                <div class="ob-kpi-num" data-count-to="{{ $attentionCount }}">{{ $attentionCount }}</div>
                <div class="ob-kpi-sub">Unapproved + stale drafts</div>
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         TODAY'S FOCUS (8)  +  PRIORITY INBOX (4)
    ══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-2">
        {{-- Today's Focus --}}
        <div class="col-xl-8 ob-anim-5">
            <div class="ob-card h-100">
                <div class="ob-card-title">
                    <span class="ob-card-title-bar" style="background:#0EA5C5"></span>
                    Today's Focus
                    <a href="{{ route('doctor.cases.index') }}" class="ob-card-more">
                        View all <i data-feather="arrow-right"></i>
                    </a>
                </div>

                @if($focus->isEmpty())
                    <div class="ob-focus-empty">
                        <i data-feather="coffee"></i>
                        <div class="ob-focus-empty-title">You're all caught up</div>
                        <div class="ob-focus-empty-sub">No action items need your attention right now.</div>
                    </div>
                @else
                    <div class="ob-focus-list">
                        @foreach($focus as $f)
                            <a href="{{ $f['route'] }}" class="ob-focus-item ob-focus--{{ $f['tone'] }}">
                                <div class="ob-focus-icon">
                                    <i data-feather="{{ $f['type'] === 'resubmit' ? 'rotate-ccw' : ($f['type'] === 'complete' ? 'edit-3' : 'truck') }}"></i>
                                </div>
                                <div class="ob-focus-body">
                                    <div class="ob-focus-title">{{ $f['title'] }}</div>
                                    <div class="ob-focus-meta">
                                        <span class="ob-focus-code">{{ $f['code'] }}</span>
                                        <span>{{ $f['meta'] }}</span>
                                        <span>·</span>
                                        <span>{{ $f['when'] }}</span>
                                    </div>
                                </div>
                                <span class="ob-focus-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Priority Inbox --}}
        <div class="col-xl-4 ob-anim-6">
            <div class="ob-inbox h-100">
                <div class="ob-inbox-title">
                    <span class="ob-inbox-title-bar"></span>
                    Priority Inbox
                </div>
                <div class="ob-inbox-list">
                    @foreach($alerts as $a)
                        <div class="ob-inbox-row ob-inbox--{{ $a['tone'] }}">
                            <div class="ob-inbox-icon"><i data-feather="{{ $a['icon'] }}"></i></div>
                            <div class="ob-inbox-body">
                                <div class="ob-inbox-row-title">{{ $a['title'] }}</div>
                                <div class="ob-inbox-row-sub">{{ $a['sub'] }}</div>
                            </div>
                            @if(!empty($a['action']) && !empty($a['route']))
                                <a href="{{ $a['route'] }}" class="ob-inbox-action">{{ $a['action'] }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="ob-inbox-tooth">
                    <span class="ob-inbox-spark s1"></span>
                    <span class="ob-inbox-spark s2"></span>
                    <span class="ob-inbox-spark s3"></span>
                    <svg viewBox="0 0 120 140" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="drInboxBody" x1="0.15" y1="0.05" x2="0.85" y2="1">
                                <stop offset="0"    stop-color="#FFFFFF"/>
                                <stop offset="0.35" stop-color="#E6F4FA"/>
                                <stop offset="0.75" stop-color="#9CCBDE"/>
                                <stop offset="1"    stop-color="#4E8CA6"/>
                            </linearGradient>
                            <radialGradient id="drInboxShine" cx="0.3" cy="0.25" r="0.55">
                                <stop offset="0"   stop-color="#FFFFFF" stop-opacity="0.85"/>
                                <stop offset="0.6" stop-color="#FFFFFF" stop-opacity="0.15"/>
                                <stop offset="1"   stop-color="#FFFFFF" stop-opacity="0"/>
                            </radialGradient>
                        </defs>
                        <path d="M60 12 C34 12,20 28,20 54 C20 72,23 86,26 94 C28 108,34 130,42 130
                                 C50 130,54 116,56 100 C57 94,58 92,60 92 C62 92,63 94,64 100
                                 C66 116,70 130,78 130 C86 130,92 108,94 94 C97 86,100 72,100 54
                                 C100 28,86 12,60 12 Z"
                              fill="url(#drInboxBody)" stroke="rgba(255,255,255,0.25)" stroke-width="0.8"/>
                        <ellipse cx="45" cy="42" rx="16" ry="26" fill="url(#drInboxShine)"/>
                        <path d="M14 65 Q60 72 106 65" stroke="#22D3EE" stroke-width="1.4" fill="none" opacity="0.7"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         DENTAL ARCH MAP (6)  +  TREATMENT PIPELINE (6)
    ══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-2">
        {{-- Dental Arch Map --}}
        <div class="col-xl-6 ob-anim-5">
            <div class="ob-card h-100 d-flex flex-column">
                <div class="ob-card-title">
                    <span class="ob-card-title-bar" style="background:#7C3AED"></span>
                    Dental Arch Map
                    <span class="ob-card-more" style="color:var(--d-slate); cursor:default">
                        Drag to rotate
                    </span>
                </div>

                <div class="ob-mouth-stage" id="obMouthStage"
                     data-obj="{{ asset('models/dental/base.obj') }}"
                     data-diffuse="{{ asset('models/dental/texture_diffuse.png') }}"
                     data-normal="{{ asset('models/dental/texture_normal.png') }}"
                     data-roughness="{{ asset('models/dental/texture_roughness.png') }}"
                     data-metallic="{{ asset('models/dental/texture_metallic.png') }}">
                    <div class="ob-mouth-3d" id="obMouth3d">
                        <canvas class="ob-mouth-canvas" id="obMouthCanvas"></canvas>
                    </div>
                    <div class="ob-mouth-loader" id="obMouthLoader">
                        <div class="ob-mouth-loader-ring"></div>
                        <div class="ob-mouth-loader-text">Loading 3D model…</div>
                    </div>
                    <div class="ob-mouth-hint">
                        <i data-feather="move"></i> Click &amp; drag to rotate
                    </div>
                </div>

                <div class="ob-arch-legend" style="margin-top:.5rem;">
                    <span class="ob-arch-legend-item" style="color:var(--d-slate); font-weight:600">
                        <i data-feather="box" style="width:14px !important; height:14px !important;"></i>
                        Real-time WebGL render
                    </span>
                    <span class="ob-arch-legend-item" style="margin-left:auto; color:var(--d-slate); font-weight:600">
                        PBR materials · Upper &amp; lower arch
                    </span>
                </div>

                {{-- Treatment Pipeline (compact) — fills remaining card height with useful data --}}
                <div class="ob-arch-pipeline">
                    <div class="ob-arch-pipeline-title">
                        <span class="ob-card-title-bar" style="background:#0EA5C5"></span>
                        Recent Pipeline
                        <a href="{{ route('doctor.cases.index') }}" class="ob-arch-pipeline-more">View all</a>
                    </div>

                    @if($pipeline->count())
                        <div class="ob-arch-pipeline-list">
                            @foreach($pipeline as $item)
                                <a href="{{ $item['route'] }}" class="ob-arch-pipeline-row">
                                    <span class="ob-arch-pipeline-code">{{ $item['code'] }}</span>
                                    <span class="ob-arch-pipeline-status ob-arch-pipeline-status--{{ strtolower($item['status']) }}">
                                        {{ ucwords(strtolower(str_replace('_', ' ', $item['status']))) }}
                                    </span>
                                    <span class="ob-arch-pipeline-when">{{ $item['when'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="ob-arch-pipeline-empty">
                            <i data-feather="inbox"></i>
                            <div>No active cases yet — start one from Quick Actions.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Patient Pulse stacked over Quick Actions --}}
        <div class="col-xl-6 ob-anim-6 d-flex flex-column gap-3">

            {{-- Patient Pulse --}}
            <div class="ob-card">
                <div class="ob-card-title">
                    <span class="ob-card-title-bar" style="background:#0EA5C5"></span>
                    Patient Pulse
                    <span class="ob-card-more" style="color:var(--d-slate); cursor:default">Last 14 days</span>
                </div>

                @php
                    $total14  = array_sum(array_column($pulse, 'n'));
                    $first7   = array_sum(array_slice(array_column($pulse, 'n'), 0, 7));
                    $last7    = array_sum(array_slice(array_column($pulse, 'n'), 7, 7));
                    $delta    = $first7 > 0 ? round((($last7 - $first7) / $first7) * 100) : ($last7 > 0 ? 100 : 0);
                    $deltaDir = $delta >= 0 ? 'up' : 'down';
                @endphp

                <div class="ob-pulse-head">
                    <div>
                        <div class="ob-pulse-big" data-count-to="{{ $total14 }}">{{ $total14 }}</div>
                        <div class="ob-pulse-caption">New cases logged in the last 14 days</div>
                    </div>
                    <span class="ob-pulse-delta ob-pulse-delta--{{ $deltaDir }}">
                        <i data-feather="{{ $deltaDir === 'up' ? 'trending-up' : 'trending-down' }}"></i>
                        {{ $delta >= 0 ? '+' : '' }}{{ $delta }}% WoW
                    </span>
                </div>

                <svg class="ob-pulse-chart" viewBox="0 0 600 120" preserveAspectRatio="none" id="obPulseChart"
                     data-pulse='@json(array_column($pulse, "n"))'>
                    <defs>
                        <linearGradient id="drPulseFill" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%"   stop-color="#0EA5C5" stop-opacity=".35"/>
                            <stop offset="100%" stop-color="#0EA5C5" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="ob-pulse-scale">
                    <span>{{ \Carbon\Carbon::parse($pulse[0]['date'])->format('M j') }}</span>
                    <span>{{ \Carbon\Carbon::parse($pulse[6]['date'])->format('M j') }}</span>
                    <span>{{ \Carbon\Carbon::parse($pulse[13]['date'])->format('M j') }}</span>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="ob-card">
                <div class="ob-card-title">
                    <span class="ob-card-title-bar" style="background:#1E293B"></span>
                    Quick Actions
                </div>
                <div class="ob-qa-grid">
                    @php
                        $qaItems = [
                            ['label' => 'New Case',    'route' => 'doctor.cases.create',     'icon' => 'plus-circle', 'bg' => '#EFF6FF', 'color' => '#2563EB'],
                            ['label' => 'All Cases',   'route' => 'doctor.cases.index',      'icon' => 'folder',      'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                            ['label' => 'Profile',     'route' => 'doctor.profile.index',    'icon' => 'user',        'bg' => '#ECFDF5', 'color' => '#059669'],
                            ['label' => 'Settings',    'route' => 'doctor.profile.settings', 'icon' => 'settings',    'bg' => '#FFFBEB', 'color' => '#D97706'],
                        ];
                    @endphp
                    @foreach($qaItems as $qa)
                        <a href="{{ route($qa['route']) }}" class="ob-qa-btn">
                            <div class="ob-qa-icon" style="background:{{ $qa['bg'] }}; color:{{ $qa['color'] }}">
                                <i data-feather="{{ $qa['icon'] }}"></i>
                            </div>
                            <span>{{ $qa['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Live clock ────────────────────────────────────────────────
    var clockEl = document.getElementById('ob-clock');
    function tickClock() {
        if (!clockEl) return;
        clockEl.textContent = new Date().toLocaleTimeString([], {
            hour: '2-digit', minute: '2-digit'
        });
    }
    tickClock();
    setInterval(tickClock, 1000);

    // ── Count-up animation ────────────────────────────────────────
    function animateCount(el) {
        var target = parseInt(el.dataset.countTo, 10);
        if (isNaN(target)) return;
        var duration = 1100, start = null;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            var ease = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('[data-count-to]').forEach(animateCount);

    // (Three.js WebGL render is initialized in a separate module block below.)

    // ── Patient Pulse sparkline (SVG path built from data) ────────
    var pulseEl = document.getElementById('obPulseChart');
    if (pulseEl) {
        var data;
        try { data = JSON.parse(pulseEl.dataset.pulse); } catch (e) { data = []; }
        if (data && data.length) {
            var W = 600, H = 120, pad = 6;
            var max = Math.max.apply(null, data.concat([1]));
            var step = (W - pad * 2) / (data.length - 1 || 1);

            var pts = data.map(function (v, i) {
                var x = pad + i * step;
                var y = H - pad - (v / max) * (H - pad * 2);
                return [x, y];
            });

            // Smooth path via simple Catmull-Rom → Bezier
            function smoothPath(points) {
                if (points.length < 2) return '';
                var d = 'M ' + points[0][0] + ' ' + points[0][1];
                for (var i = 0; i < points.length - 1; i++) {
                    var p0 = points[i - 1] || points[i];
                    var p1 = points[i];
                    var p2 = points[i + 1];
                    var p3 = points[i + 2] || p2;
                    var c1x = p1[0] + (p2[0] - p0[0]) / 6;
                    var c1y = p1[1] + (p2[1] - p0[1]) / 6;
                    var c2x = p2[0] - (p3[0] - p1[0]) / 6;
                    var c2y = p2[1] - (p3[1] - p1[1]) / 6;
                    d += ' C ' + c1x + ' ' + c1y + ', ' + c2x + ' ' + c2y + ', ' + p2[0] + ' ' + p2[1];
                }
                return d;
            }

            var line = smoothPath(pts);
            var area = line + ' L ' + pts[pts.length - 1][0] + ' ' + (H - pad) +
                              ' L ' + pts[0][0]               + ' ' + (H - pad) + ' Z';

            var SVGNS = 'http://www.w3.org/2000/svg';
            var areaEl = document.createElementNS(SVGNS, 'path');
            areaEl.setAttribute('d', area);
            areaEl.setAttribute('fill', 'url(#drPulseFill)');
            pulseEl.appendChild(areaEl);

            var lineEl = document.createElementNS(SVGNS, 'path');
            lineEl.setAttribute('d', line);
            lineEl.setAttribute('fill', 'none');
            lineEl.setAttribute('stroke', '#0EA5C5');
            lineEl.setAttribute('stroke-width', '2.2');
            lineEl.setAttribute('stroke-linecap', 'round');
            lineEl.setAttribute('stroke-linejoin', 'round');
            pulseEl.appendChild(lineEl);

            // Endpoint marker
            var last = pts[pts.length - 1];
            var dot = document.createElementNS(SVGNS, 'circle');
            dot.setAttribute('cx', last[0]);
            dot.setAttribute('cy', last[1]);
            dot.setAttribute('r', 4);
            dot.setAttribute('fill', '#0EA5C5');
            dot.setAttribute('stroke', '#fff');
            dot.setAttribute('stroke-width', '2');
            pulseEl.appendChild(dot);
        }
    }
})();
</script>

{{-- ─── Three.js 3D Dental Model ──────────────────────────────── --}}
<script type="importmap">
{
  "imports": {
    "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
    "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
  }
}
</script>
<script type="module">
import * as THREE from 'three';
import { OBJLoader }     from 'three/addons/loaders/OBJLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

(function init3DDentalModel () {
    const stage  = document.getElementById('obMouthStage');
    const canvas = document.getElementById('obMouthCanvas');
    const loader = document.getElementById('obMouthLoader');
    if (!stage || !canvas) return;

    const urls = {
        obj:       stage.dataset.obj,
        diffuse:   stage.dataset.diffuse,
        normal:    stage.dataset.normal,
        roughness: stage.dataset.roughness,
        metallic:  stage.dataset.metallic,
    };

    // ── Renderer ─────────────────────────────────────────────
    const renderer = new THREE.WebGLRenderer({
        canvas, antialias: true, alpha: true, powerPreference: 'high-performance'
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace   = THREE.SRGBColorSpace;
    renderer.toneMapping        = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.15;

    const scene = new THREE.Scene();

    const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
    camera.position.set(0, 0.2, 4.2);

    // ── Lighting ─────────────────────────────────────────────
    scene.add(new THREE.AmbientLight(0xffffff, 0.45));
    const hemi = new THREE.HemisphereLight(0xffffff, 0xdbe9f4, 0.55);
    scene.add(hemi);

    const keyLight = new THREE.DirectionalLight(0xffffff, 1.2);
    keyLight.position.set(3, 4, 5);
    scene.add(keyLight);

    const fillLight = new THREE.DirectionalLight(0xffe5d5, 0.45);
    fillLight.position.set(-3, -1, 4);
    scene.add(fillLight);

    const rimLight = new THREE.DirectionalLight(0x9ecfff, 0.5);
    rimLight.position.set(0, 2, -5);
    scene.add(rimLight);

    // ── Textures (PBR) ───────────────────────────────────────
    const tex = new THREE.TextureLoader();
    const diffuseMap   = tex.load(urls.diffuse);
    const normalMap    = tex.load(urls.normal);
    const roughnessMap = tex.load(urls.roughness);
    const metallicMap  = tex.load(urls.metallic);

    diffuseMap.colorSpace = THREE.SRGBColorSpace;
    [diffuseMap, normalMap, roughnessMap, metallicMap].forEach(t => {
        t.anisotropy = renderer.capabilities.getMaxAnisotropy();
        t.flipY = true;  // OBJ + PNG textures: stay with default flip
    });

    // ── Orbit Controls ───────────────────────────────────────
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.enablePan     = false;
    controls.enableZoom    = false;
    controls.rotateSpeed   = 0.85;
    controls.autoRotate    = true;
    controls.autoRotateSpeed = 0.7;
    controls.minPolarAngle = Math.PI * 0.30;
    controls.maxPolarAngle = Math.PI * 0.75;

    let autoRotateTimer;
    controls.addEventListener('start', () => {
        controls.autoRotate = false;
        if (autoRotateTimer) clearTimeout(autoRotateTimer);
    });
    controls.addEventListener('end', () => {
        autoRotateTimer = setTimeout(() => { controls.autoRotate = true; }, 2500);
    });

    // ── Load OBJ ─────────────────────────────────────────────
    const objLoader = new OBJLoader();
    objLoader.load(
        urls.obj,
        (group) => {
            group.traverse((child) => {
                if (child.isMesh) {
                    child.material = new THREE.MeshStandardMaterial({
                        map:          diffuseMap,
                        normalMap:    normalMap,
                        roughnessMap: roughnessMap,
                        metalnessMap: metallicMap,
                        roughness: 1.0,
                        metalness: 0.25,
                        normalScale: new THREE.Vector2(0.8, 0.8),
                    });
                    child.castShadow = false;
                    child.receiveShadow = false;
                }
            });

            // Center and normalize scale so it fills the view nicely
            const box = new THREE.Box3().setFromObject(group);
            const size = new THREE.Vector3(); box.getSize(size);
            const center = new THREE.Vector3(); box.getCenter(center);
            group.position.sub(center);
            const maxDim = Math.max(size.x, size.y, size.z);
            if (maxDim > 0) group.scale.setScalar(2.4 / maxDim);

            // Slight initial tilt so it reads as 3D instantly
            group.rotation.x = 0.15;

            scene.add(group);
            if (loader) loader.classList.add('is-hidden');
        },
        (evt) => {
            if (loader && evt.lengthComputable) {
                const pct = Math.round((evt.loaded / evt.total) * 100);
                const txt = loader.querySelector('.ob-mouth-loader-text');
                if (txt) txt.textContent = 'Loading 3D model… ' + pct + '%';
            }
        },
        (err) => {
            console.error('[dental-model] OBJ load failed:', err);
            if (loader) {
                const txt = loader.querySelector('.ob-mouth-loader-text');
                if (txt) txt.textContent = 'Failed to load model';
            }
        }
    );

    // ── Size & render loop ───────────────────────────────────
    function resize () {
        const w = stage.clientWidth;
        const h = stage.clientHeight;
        if (w === 0 || h === 0) return;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    const ro = new ResizeObserver(resize);
    ro.observe(stage);

    function animate () {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }
    animate();
})();
</script>
@endpush
