<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user_name'];
include 'includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

/* ═══════════════════════════════════════════════════════════
   STOCK SCREENER — CINEMATIC REDESIGN v3
   Font: Space Grotesk + DM Sans + JetBrains Mono
   ═══════════════════════════════════════════════════════════ */

:root {
  --gold:       #d4af37;
  --gold-l:     #facc15;
  --gold-dim:   rgba(212,175,55,.12);
  --navy:       #020617;
  --card:       rgba(6,12,28,.92);
  --brd:        rgba(212,175,55,.1);
  --text:       #e2e8f0;
  --muted:      #475569;
  --green:      #34d399;
  --amber:      #fbbf24;
  --red:        #f87171;
  --blue:       #60a5fa;
  --ff-head:    'Space Grotesk', system-ui, sans-serif;
  --ff-body:    'DM Sans', system-ui, sans-serif;
  --ff-mono:    'JetBrains Mono', monospace;
}

/* ── Keyframes ─────────────────────────────────────────── */
@keyframes scFloatUp  { from{opacity:0;transform:translateY(32px)} to{opacity:1;transform:none} }
@keyframes scSlideIn  { from{opacity:0;transform:translateX(-18px)} to{opacity:1;transform:none} }
@keyframes scPulse    { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }
@keyframes scShimmer  { from{background-position:-300% center} to{background-position:300% center} }
@keyframes scRowIn    { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:none} }
@keyframes scFadeIn   { from{opacity:0} to{opacity:1} }
@keyframes scModalIn  { from{opacity:0;transform:scale(.9) translateY(24px)} to{opacity:1;transform:scale(1) translateY(0)} }
@keyframes scGridDrift{ from{background-position:0 0} to{background-position:60px 60px} }
@keyframes scOrb      { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(14px,-12px) scale(1.06)} }
@keyframes scBadgePop { 0%{opacity:0;transform:scale(.8) translateY(6px)} 70%{transform:scale(1.04)} 100%{opacity:1;transform:scale(1)} }
@keyframes scTagFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }

*{ box-sizing:border-box; }
body{
  background:var(--navy); color:var(--text);
  font-family:var(--ff-body); margin:0;
  -webkit-font-smoothing:antialiased;
}

/* ── Animated BG ─────────────────────────────────── */
.sc-bg {
  position:fixed; inset:0; pointer-events:none; z-index:0;
  background:
    linear-gradient(rgba(212,175,55,.022) 1px, transparent 1px),
    linear-gradient(90deg,rgba(212,175,55,.022) 1px, transparent 1px);
  background-size:56px 56px;
  animation:scGridDrift 28s linear infinite;
}
.sc-bg::after {
  content:''; position:absolute; inset:0;
  background:
    radial-gradient(ellipse 65% 50% at 18% 25%, rgba(212,175,55,.07) 0%, transparent 55%),
    radial-gradient(ellipse 48% 55% at 82% 72%, rgba(56,189,248,.04) 0%, transparent 55%);
}
.sc-orb {
  position:fixed; border-radius:50%; filter:blur(90px);
  pointer-events:none; z-index:0; animation:scOrb ease-in-out infinite;
}
.sc-orb-1{ width:480px;height:480px;background:rgba(212,175,55,.04);top:-120px;left:-80px;animation-duration:12s; }
.sc-orb-2{ width:360px;height:360px;background:rgba(56,189,248,.03);bottom:-60px;right:-60px;animation-duration:16s;animation-delay:-8s; }

/* ── Layout ─────────────────────────────────────── */
.sc-wrapper{ display:flex; min-height:100vh; position:relative; z-index:1; }
.sc-main   { flex:1; padding:40px 44px 60px; min-width:0; }

/* ── Hero ────────────────────────────────────────── */
.sc-hero-wrap{ margin-bottom:40px; animation:scFloatUp .6s ease both; }

.sc-eyebrow {
  display:inline-flex; align-items:center; gap:9px;
  font-family:var(--ff-head); font-size:.68rem; font-weight:700;
  letter-spacing:3.5px; text-transform:uppercase; color:var(--gold);
  margin-bottom:16px; padding:7px 18px;
  background:rgba(212,175,55,.07); border:1px solid rgba(212,175,55,.22);
  border-radius:99px;
}
.sc-eyebrow-dot{
  width:7px;height:7px;border-radius:50%;background:var(--gold);
  box-shadow:0 0 8px rgba(212,175,55,.8);
  animation:scPulse 2s ease-in-out infinite;
}

.sc-title {
  font-family:var(--ff-head); font-weight:800;
  font-size:clamp(2.2rem,4vw,3.2rem); line-height:1.05;
  margin:0 0 12px; letter-spacing:-.5px;
  background:linear-gradient(135deg,#fff 20%,#f1f5f9 40%,var(--gold) 70%,var(--gold-l) 100%);
  background-size:200% auto;
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
  animation:scShimmer 6s linear infinite;
}

.sc-sub {
  font-family:var(--ff-body); font-size:.95rem; font-weight:400;
  color:var(--muted); max-width:580px; line-height:1.7; margin-bottom:0;
}

/* Floating data tags in hero */
.sc-hero-tags {
  display:flex; flex-wrap:wrap; gap:10px; margin-top:22px;
}
.sc-hero-tag {
  display:inline-flex; align-items:center; gap:7px;
  font-family:var(--ff-mono); font-size:.72rem; font-weight:600;
  padding:6px 14px; border-radius:99px;
  border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.03);
  color:#475569; letter-spacing:.3px;
  animation:scBadgePop .5s ease both, scTagFloat 4s ease-in-out infinite;
}
.sc-hero-tag:nth-child(2){ animation-delay:.1s, .4s; }
.sc-hero-tag:nth-child(3){ animation-delay:.2s, .8s; }
.sc-hero-tag:nth-child(4){ animation-delay:.3s, 1.2s; }
.sc-hero-tag i{ font-size:.65rem; }
.sc-hero-tag.tg-green{ color:#34d399; border-color:rgba(52,211,153,.2); background:rgba(52,211,153,.05); }
.sc-hero-tag.tg-gold { color:var(--gold); border-color:rgba(212,175,55,.2); background:rgba(212,175,55,.05); }
.sc-hero-tag.tg-blue { color:#60a5fa; border-color:rgba(96,165,250,.2); background:rgba(96,165,250,.05); }
.sc-hero-tag.tg-red  { color:#f87171; border-color:rgba(248,113,113,.2); background:rgba(248,113,113,.05); }

/* ── Filter Panel ────────────────────────────────── */
.sc-panel {
  background:rgba(6,12,28,.85);
  border:1px solid rgba(212,175,55,.1);
  border-radius:24px; padding:28px 32px;
  backdrop-filter:blur(20px);
  box-shadow:0 24px 60px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.04);
  margin-bottom:24px;
  animation:scFloatUp .65s .08s ease both;
  position:relative; overflow:hidden;
}
.sc-panel::before{
  content:''; position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.35),transparent);
}

.sc-panel-head{
  display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;
}
.sc-panel-htitle{
  display:flex; align-items:center; gap:11px;
  font-family:var(--ff-head); font-size:1rem; font-weight:700; color:#f1f5f9;
}
.sc-panel-htitle i{
  width:34px; height:34px; border-radius:10px;
  background:rgba(212,175,55,.1); border:1px solid rgba(212,175,55,.2);
  display:flex; align-items:center; justify-content:center;
  color:var(--gold); font-size:.85rem;
}

.sc-collapse-btn{
  background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
  color:var(--muted); padding:7px 16px; border-radius:99px;
  font-family:var(--ff-body); font-size:.78rem; font-weight:600;
  cursor:pointer; display:flex; align-items:center; gap:7px;
  transition:all .25s;
}
.sc-collapse-btn:hover{ border-color:rgba(212,175,55,.3); color:var(--gold); background:rgba(212,175,55,.05); }
.sc-collapse-btn i{ transition:transform .35s cubic-bezier(.34,1.56,.64,1); }

.sc-panel-body{ max-height:900px; opacity:1; transition:max-height .4s ease, opacity .3s ease; overflow:hidden; }
.sc-panel-body.collapsed{ max-height:0; opacity:0; }

/* ── Presets ─────────────────────────────────────── */
.sc-presets{
  display:flex; gap:10px; flex-wrap:wrap; margin-bottom:26px;
  padding-bottom:22px; border-bottom:1px solid rgba(255,255,255,.05);
  align-items:center;
}
.sc-preset-lbl{
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  color:var(--muted); text-transform:uppercase; letter-spacing:2px;
  margin-right:2px; white-space:nowrap;
}
.sc-preset {
  padding:8px 18px; border-radius:99px;
  border:1px solid rgba(255,255,255,.08);
  background:transparent; color:var(--muted);
  font-family:var(--ff-head); font-size:.78rem; font-weight:600;
  cursor:pointer; transition:all .22s; white-space:nowrap;
  display:inline-flex; align-items:center; gap:7px;
}
.sc-preset:hover{
  border-color:rgba(212,175,55,.4); color:var(--gold);
  background:rgba(212,175,55,.06); transform:translateY(-2px);
}
.sc-preset.active{
  background:rgba(212,175,55,.12); border-color:rgba(212,175,55,.45);
  color:var(--gold); box-shadow:0 4px 16px rgba(212,175,55,.15);
}

/* ── Filter Groups ───────────────────────────────── */
.sc-groups{
  display:grid; grid-template-columns:repeat(auto-fill,minmax(195px,1fr));
  gap:28px; margin-bottom:26px;
}
.sc-group-title{
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  letter-spacing:2px; text-transform:uppercase;
  color:var(--gold); margin-bottom:16px;
  display:flex; align-items:center; gap:8px;
}
.sc-group-title i{ font-size:.75rem; opacity:.8; }
.sc-inputs{ display:flex; flex-direction:column; gap:13px; }
.sc-field { display:flex; flex-direction:column; gap:6px; }
.sc-field label{
  font-family:var(--ff-body); font-size:.72rem; font-weight:500;
  color:var(--muted); letter-spacing:.3px;
}
.sc-field input,
.sc-field select {
  font-family:var(--ff-mono); font-size:.85rem; font-weight:500;
  background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
  border-radius:10px; padding:10px 14px; color:var(--text);
  transition:border-color .2s, box-shadow .2s, background .2s;
  outline:none; width:100%;
}
.sc-field input:focus,
.sc-field select:focus{
  border-color:rgba(212,175,55,.5); background:rgba(212,175,55,.04);
  box-shadow:0 0 0 3px rgba(212,175,55,.1);
}
.sc-field select option{ background:#0d1b2e; color:var(--text); }

/* ── Actions ─────────────────────────────────────── */
.sc-action-row{ display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.sc-btn-run {
  font-family:var(--ff-head); font-size:.88rem; font-weight:700;
  padding:12px 30px; background:linear-gradient(135deg,#d4af37,#facc15);
  border:none; border-radius:12px; color:#020617;
  cursor:pointer; transition:all .25s;
  display:inline-flex; align-items:center; gap:9px;
  box-shadow:0 4px 20px rgba(212,175,55,.3);
  letter-spacing:.3px;
}
.sc-btn-run:hover{ transform:translateY(-2px); box-shadow:0 8px 32px rgba(212,175,55,.5); }
.sc-btn-run:active{ transform:translateY(0); }
.sc-btn-reset{
  font-family:var(--ff-body); font-size:.85rem; font-weight:600;
  padding:12px 22px; background:transparent;
  border:1px solid rgba(255,255,255,.1); border-radius:12px;
  color:var(--muted); cursor:pointer; transition:all .2s;
  display:inline-flex; align-items:center; gap:8px;
}
.sc-btn-reset:hover{ border-color:rgba(248,113,113,.4); color:#f87171; background:rgba(248,113,113,.05); }

/* ── Results Bar ─────────────────────────────────── */
.sc-results-bar{
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:16px; flex-wrap:wrap; gap:12px;
  animation:scFloatUp .6s .15s ease both;
}
.sc-results-text{
  font-family:var(--ff-body); font-size:.88rem; color:var(--muted);
}
.sc-results-text strong{
  font-family:var(--ff-head); color:var(--text);
  font-size:1.2rem; font-weight:800; margin-right:2px;
}
.sc-status-badge{
  font-family:var(--ff-head); font-size:.72rem; font-weight:700;
  letter-spacing:.5px; padding:5px 16px; border-radius:99px;
  background:rgba(100,116,139,.08); border:1px solid rgba(100,116,139,.18);
  color:var(--muted); transition:all .4s ease;
}
.sc-status-badge.loaded{
  background:rgba(52,211,153,.08); border-color:rgba(52,211,153,.22);
  color:var(--green);
}

/* ── Table ───────────────────────────────────────── */
.sc-table-wrap{
  background:rgba(6,12,28,.88); border:1px solid rgba(212,175,55,.08);
  border-radius:22px; overflow:hidden; backdrop-filter:blur(16px);
  overflow-x:auto; box-shadow:0 28px 70px rgba(0,0,0,.5);
  animation:scFloatUp .65s .2s ease both;
  position:relative;
}
.sc-table-wrap::before{
  content:''; position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.25),transparent);
  z-index:2;
}
table{ width:100%; border-collapse:collapse; min-width:1020px; }
thead{ position:sticky; top:0; z-index:2; }
th{
  background:rgba(3,8,20,.97); padding:14px 18px;
  text-align:left; font-family:var(--ff-head);
  font-size:.65rem; font-weight:700; text-transform:uppercase;
  letter-spacing:1.8px; color:var(--muted);
  border-bottom:1px solid rgba(212,175,55,.1);
  cursor:pointer; user-select:none; white-space:nowrap;
  transition:color .2s; backdrop-filter:blur(16px);
}
th:hover{ color:var(--gold); }
th .si{ display:inline-block; margin-left:6px; opacity:.3; font-size:.6rem; vertical-align:middle; transition:opacity .2s; }
th:hover .si{ opacity:.6; }
th.asc  .si::before{ content:'▲'; opacity:1; color:var(--gold); }
th.desc .si::before{ content:'▼'; opacity:1; color:var(--gold); }
th:not(.asc):not(.desc) .si::before{ content:'⇅'; }

td{
  padding:13px 18px; border-bottom:1px solid rgba(255,255,255,.035);
  font-family:var(--ff-body); font-size:.85rem; white-space:nowrap;
}
tbody tr{
  cursor:pointer; transition:background .15s;
  animation:scRowIn .4s ease both;
  position:relative;
}
tbody tr:hover{ background:rgba(212,175,55,.04); }
tbody tr:hover td:first-child{ border-left:2px solid rgba(212,175,55,.5); }

/* Ticker badge */
.sc-tick{
  display:inline-flex; align-items:center;
  font-family:var(--ff-mono); font-weight:700; font-size:.82rem; letter-spacing:.5px;
  background:rgba(212,175,55,.1); border:1px solid rgba(212,175,55,.22);
  color:var(--gold); padding:4px 11px; border-radius:8px;
  transition:all .2s;
}
tbody tr:hover .sc-tick{
  background:rgba(212,175,55,.2); border-color:rgba(212,175,55,.4);
  box-shadow:0 0 14px rgba(212,175,55,.2);
}

/* Company name */
.sc-company{
  font-family:var(--ff-body); font-weight:500; color:#e2e8f0; font-size:.85rem;
}

/* Metric value coloring — with JetBrains Mono */
.vg{ font-family:var(--ff-mono); color:#34d399; font-weight:600; }
.vw{ font-family:var(--ff-mono); color:#fbbf24; font-weight:600; }
.vb{ font-family:var(--ff-mono); color:#f87171; font-weight:600; }

/* ── Empty State ─────────────────────────────────── */
.sc-empty{ text-align:center; padding:80px 40px; }
.sc-empty-icon{ font-size:2.8rem; color:rgba(212,175,55,.12); display:block; margin-bottom:18px; }
.sc-empty-title{
  font-family:var(--ff-head); font-size:1.1rem; font-weight:700;
  color:var(--muted); margin-bottom:8px;
}
.sc-empty-sub{
  font-family:var(--ff-body); font-size:.85rem; color:var(--muted); opacity:.6;
}

/* ── Chart Modal ─────────────────────────────────── */
.sc-overlay{
  position:fixed; inset:0; background:rgba(0,0,0,.85);
  backdrop-filter:blur(12px); z-index:1000;
  display:none; align-items:center; justify-content:center; padding:16px;
}
.sc-overlay.active{ display:flex; animation:scFadeIn .25s ease; }
.sc-modal-box{
  background:linear-gradient(160deg,#0d1b2e 0%,#060f1c 100%);
  border:1px solid rgba(212,175,55,.2); border-radius:26px;
  padding:32px; width:740px; max-width:calc(100vw - 32px);
  position:relative; animation:scModalIn .35s cubic-bezier(.34,1.56,.64,1);
  box-shadow:0 50px 120px rgba(0,0,0,.7), 0 0 0 1px rgba(212,175,55,.04);
  overflow:hidden;
}
.sc-modal-box::before{
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
  background:linear-gradient(90deg,transparent,#d4af37,#facc15,#d4af37,transparent);
}
.sc-modal-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
.sc-modal-title{
  font-family:var(--ff-head); font-size:1.15rem; font-weight:800;
  background:linear-gradient(135deg,#d4af37,#facc15);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.sc-modal-close{
  width:36px; height:36px; border-radius:10px;
  border:1px solid rgba(255,255,255,.1);
  background:rgba(255,255,255,.04); color:var(--muted);
  cursor:pointer; display:flex; align-items:center; justify-content:center;
  font-size:.95rem; transition:all .2s;
}
.sc-modal-close:hover{ border-color:rgba(248,113,113,.4); color:#f87171; background:rgba(248,113,113,.08); }

/* ── Toast ───────────────────────────────────────── */
.sc-toast{
  position:fixed; bottom:32px; right:32px; z-index:2000;
  background:rgba(6,12,28,.97); border:1px solid rgba(212,175,55,.3);
  border-radius:16px; padding:14px 22px; font-family:var(--ff-body);
  font-size:.88rem; font-weight:600; color:var(--text);
  transform:translateY(80px); opacity:0;
  transition:all .35s cubic-bezier(.34,1.56,.64,1);
  display:flex; align-items:center; gap:11px;
  backdrop-filter:blur(16px); pointer-events:none;
  box-shadow:0 20px 50px rgba(0,0,0,.5);
}
.sc-toast.show{ transform:translateY(0); opacity:1; }
.sc-toast i{ color:var(--gold); }

/* ── Responsive ───────────────────────────────────── */
@media(max-width:900px){
  .sc-main{ padding:24px 16px; }
  .sc-groups{ grid-template-columns:1fr 1fr; }
}
@media(max-width:600px){
  .sc-groups{ grid-template-columns:1fr; }
  .sc-hero-tags{ display:none; }
}
</style>

<div class="sc-bg"></div>
<div class="sc-orb sc-orb-1"></div>
<div class="sc-orb sc-orb-2"></div>

<div class="sc-wrapper">

  <?php include 'includes/sidebar.php'; ?>

  <div class="sc-main">

    <!-- Hero -->
    <div class="sc-hero-wrap">
      <div class="sc-eyebrow">
        <span class="sc-eyebrow-dot"></span>
        WiseInvest Quantitative Screener
      </div>
      <h1 class="sc-title">Stock Screener</h1>
      <p class="sc-sub">Filter high-quality companies using WiseInvest's quantitative metrics — Altman Z, Piotroski, ROE, ROIC and 3-year growth rates.</p>
      <div class="sc-hero-tags">
        <span class="sc-hero-tag tg-green"><i class="fas fa-shield-halved"></i> Quality Scoring</span>
        <span class="sc-hero-tag tg-gold"><i class="fas fa-chart-line"></i> 3Y Growth Filter</span>
        <span class="sc-hero-tag tg-blue"><i class="fas fa-percent"></i> Profitability Screen</span>
        <span class="sc-hero-tag tg-red"><i class="fas fa-exclamation-triangle"></i> Risk Assessment</span>
      </div>
    </div>

    <!-- Filter Panel -->
    <div class="sc-panel">

      <div class="sc-panel-head">
        <div class="sc-panel-htitle">
          <i class="fas fa-sliders"></i>
          Filter Controls
        </div>
        <button class="sc-collapse-btn" onclick="togglePanel(this)">
          <span>Collapse</span><i class="fas fa-chevron-up"></i>
        </button>
      </div>

      <div class="sc-panel-body" id="panelBody">

        <!-- Quick Presets -->
        <div class="sc-presets">
          <span class="sc-preset-lbl">Quick Presets:</span>
          <button class="sc-preset active" data-preset="quality" onclick="applyPreset(this,'quality')">
            <i class="fas fa-star"></i> WiseInvest Quality
          </button>
          <button class="sc-preset" data-preset="growth" onclick="applyPreset(this,'growth')">
            <i class="fas fa-rocket"></i> High Growth
          </button>
          <button class="sc-preset" data-preset="profit" onclick="applyPreset(this,'profit')">
            <i class="fas fa-chart-pie"></i> Profit Champions
          </button>
          <button class="sc-preset" data-preset="showall" onclick="applyPreset(this,'showall')">
            <i class="fas fa-list"></i> Show All
          </button>
        </div>

        <!-- Filter Groups -->
        <div class="sc-groups">

          <div>
            <div class="sc-group-title"><i class="fas fa-shield-halved"></i> Quality Scores</div>
            <div class="sc-inputs">
              <div class="sc-field">
                <label>Min Altman Z Score</label>
                <input type="number" id="altman" value="3" step="0.1" min="0" placeholder="e.g. 3">
              </div>
              <div class="sc-field">
                <label>Min Piotroski Score</label>
                <input type="number" id="piotroski" value="6" min="0" max="9" placeholder="0 – 9">
              </div>
            </div>
          </div>

          <div>
            <div class="sc-group-title"><i class="fas fa-heart-pulse"></i> Financial Health</div>
            <div class="sc-inputs">
              <div class="sc-field">
                <label>Max Debt / Equity</label>
                <input type="number" id="debt" value="5" step="0.1" min="0">
              </div>
              <div class="sc-field">
                <label>Min Net Margin %</label>
                <input type="number" id="margin" value="15" step="1">
              </div>
            </div>
          </div>

          <div>
            <div class="sc-group-title"><i class="fas fa-chart-pie"></i> Profitability</div>
            <div class="sc-inputs">
              <div class="sc-field">
                <label>Min ROE %</label>
                <input type="number" id="roe" value="25" step="1">
              </div>
              <div class="sc-field">
                <label>Min ROIC %</label>
                <input type="number" id="roic" value="15" step="1">
              </div>
            </div>
          </div>

          <div>
            <div class="sc-group-title"><i class="fas fa-chart-line"></i> 3-Year Growth</div>
            <div class="sc-inputs">
              <div class="sc-field">
                <label>Min Sales 3-Yr Avg %</label>
                <input type="number" id="sales3y" value="5" step="1">
              </div>
              <div class="sc-field">
                <label>Min Op. Income 3-Yr Avg %</label>
                <input type="number" id="opinc3y" value="10" step="1">
              </div>
              <div class="sc-field">
                <label>Min EPS 3-Yr Avg %</label>
                <input type="number" id="eps3y" value="10" step="1">
              </div>
            </div>
          </div>

        </div>

        <!-- Actions -->
        <div class="sc-action-row">
          <button class="sc-btn-run" onclick="runScreener()">
            <i class="fas fa-magnifying-glass"></i> Run Screener
          </button>
          <button class="sc-btn-reset" onclick="resetFilters()">
            <i class="fas fa-rotate-left"></i> Reset
          </button>
        </div>

      </div>
    </div>

    <!-- Results Bar -->
    <div class="sc-results-bar">
      <div class="sc-results-text">
        Showing <strong id="resultCount">—</strong> companies
      </div>
      <div class="sc-status-badge" id="statusBadge">Loading data…</div>
    </div>

    <!-- Table -->
    <div class="sc-table-wrap">
      <table id="stockTable">
        <thead>
          <tr>
            <th onclick="sortTable(0)">Ticker <span class="si"></span></th>
            <th onclick="sortTable(1)">Company <span class="si"></span></th>
            <th onclick="sortTable(2)">Altman Z <span class="si"></span></th>
            <th onclick="sortTable(3)">Piotroski <span class="si"></span></th>
            <th onclick="sortTable(4)">D/E <span class="si"></span></th>
            <th onclick="sortTable(5)">Net Margin <span class="si"></span></th>
            <th onclick="sortTable(6)">ROE <span class="si"></span></th>
            <th onclick="sortTable(7)">ROIC <span class="si"></span></th>
            <th onclick="sortTable(8)">Sales 3Y% <span class="si"></span></th>
            <th onclick="sortTable(9)">Op Inc 3Y% <span class="si"></span></th>
            <th onclick="sortTable(10)">EPS 3Y% <span class="si"></span></th>
          </tr>
        </thead>
        <tbody id="stockBody">
          <tr>
            <td colspan="11" style="text-align:center;padding:70px;color:var(--muted)">
              <i class="fas fa-spinner fa-spin" style="font-size:1.8rem;display:block;margin-bottom:14px;color:var(--gold)"></i>
              <span style="font-family:var(--ff-head);font-size:.88rem;letter-spacing:.5px;">Loading stock data…</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Chart Modal -->
<div class="sc-overlay" id="chartOverlay" onclick="overlayClick(event)">
  <div class="sc-modal-box" onclick="event.stopPropagation()">
    <div class="sc-modal-head">
      <div class="sc-modal-title" id="chartTitle">Chart</div>
      <button class="sc-modal-close" onclick="closeChart()"><i class="fas fa-times"></i></button>
    </div>
    <div id="chartContainer"></div>
  </div>
</div>

<!-- Toast -->
<div class="sc-toast" id="scToast">
  <i class="fas fa-check-circle"></i>
  <span id="toastMsg"></span>
</div>

<script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
<script>

let stocks   = [];
let sortDirs = {};

const PRESETS = {
  quality: { altman:3, piotroski:6, debt:5,   margin:15, roe:25, roic:15, sales3y:5,  opinc3y:10, eps3y:10  },
  growth:  { altman:2, piotroski:5, debt:10,  margin:10, roe:20, roic:12, sales3y:12, opinc3y:15, eps3y:15  },
  profit:  { altman:3, piotroski:7, debt:3,   margin:25, roe:40, roic:20, sales3y:5,  opinc3y:10, eps3y:10  },
  showall: { altman:0, piotroski:0, debt:999, margin:0,  roe:0,  roic:0,  sales3y:0,  opinc3y:0,  eps3y:0   }
};

Papa.parse("stocks.csv", {
  download: true, header: true,
  complete: function(results) {
    stocks = results.data.filter(s => s['Ticker'] && s['Ticker'].trim());
    displayStocks(stocks);
    const badge = document.getElementById('statusBadge');
    badge.textContent = stocks.length + ' stocks loaded';
    badge.classList.add('loaded');
  }
});

function v(val) {
  const s = (val !== undefined && val !== null) ? String(val).trim() : '';
  return s !== '' ? val : '—';
}

function pct(val) {
  if(val === undefined || val === null) return 0;
  return parseFloat(String(val).replace('%', '')) || 0;
}

function colorVal(val, type) {
  const n = parseFloat(String(val !== undefined && val !== null ? val : '').replace('%', ''));
  if(isNaN(n)) return '';
  switch(type) {
    case 'altman':  return n >= 3   ? 'vg' : n >= 1.8 ? 'vw' : 'vb';
    case 'piok':    return n >= 7   ? 'vg' : n >= 4   ? 'vw' : 'vb';
    case 'de':      return n <= 1   ? 'vg' : n <= 3   ? 'vw' : 'vb';
    case 'margin':  return n >= 20  ? 'vg' : n >= 12  ? 'vw' : 'vb';
    case 'roe':     return n >= 40  ? 'vg' : n >= 20  ? 'vw' : 'vb';
    case 'roic':    return n >= 20  ? 'vg' : n >= 12  ? 'vw' : 'vb';
    case 'sales3y': return n >= 12  ? 'vg' : n >= 5   ? 'vw' : 'vb';
    case 'opinc3y': return n >= 15  ? 'vg' : n >= 8   ? 'vw' : 'vb';
    case 'eps3y':   return n >= 15  ? 'vg' : n >= 8   ? 'vw' : 'vb';
    default:        return '';
  }
}

function displayStocks(data) {
  const tbody = document.getElementById('stockBody');
  if(!data.length) {
    tbody.innerHTML = `<tr><td colspan="11">
      <div class="sc-empty">
        <i class="fas fa-magnifying-glass sc-empty-icon"></i>
        <div class="sc-empty-title">No stocks match your criteria</div>
        <div class="sc-empty-sub">Try relaxing the filter thresholds or click Reset</div>
      </div>
    </td></tr>`;
    animateCount(0); return;
  }
  let html = '';
  data.forEach((s, i) => {
    const delay = Math.min(i * 0.025, 0.5).toFixed(2);
    const tick  = (s['Ticker'] || '').replace(/'/g, "\\'");
    html += `<tr onclick="showChart('${tick}')" style="animation-delay:${delay}s">
      <td><span class="sc-tick">${v(s['Ticker'])}</span></td>
      <td class="sc-company">${v(s['Company'])}</td>
      <td class="${colorVal(s['Altman Z-Score'],'altman')}">${v(s['Altman Z-Score'])}</td>
      <td class="${colorVal(s['Piotroski F Score'],'piok')}">${v(s['Piotroski F Score'])}</td>
      <td class="${colorVal(s['Debt / Equity'],'de')}">${v(s['Debt / Equity'])}</td>
      <td class="${colorVal(s['Net Margin'],'margin')}">${v(s['Net Margin'])}</td>
      <td class="${colorVal(s['Return on Equity'],'roe')}">${v(s['Return on Equity'])}</td>
      <td class="${colorVal(s['ROIC'],'roic')}">${v(s['ROIC'])}</td>
      <td class="${colorVal(s['Sales 3-Year Avg (%)'],'sales3y')}">${v(s['Sales 3-Year Avg (%)'])}</td>
      <td class="${colorVal(s['Operating Income 3-Year Avg (%)'],'opinc3y')}">${v(s['Operating Income 3-Year Avg (%)'])}</td>
      <td class="${colorVal(s['EPS 3-Year Avg (%)'],'eps3y')}">${v(s['EPS 3-Year Avg (%)'])}</td>
    </tr>`;
  });
  tbody.innerHTML = html;
  animateCount(data.length);
}

function animateCount(target) {
  const el = document.getElementById('resultCount');
  const start = parseInt(el.textContent) || 0;
  const dur = 500, t0 = performance.now();
  (function step(now) {
    const p = Math.min((now - t0) / dur, 1);
    el.textContent = Math.round(start + (target - start) * p);
    if(p < 1) requestAnimationFrame(step);
  })(performance.now());
}

function runScreener() {
  const get = id => parseFloat(document.getElementById(id).value);
  const altman    = get('altman')    || 0;
  const piotroski = get('piotroski') || 0;
  const debt      = isNaN(get('debt'))    ? 999  : get('debt');
  const margin    = isNaN(get('margin'))  ? -999 : get('margin');
  const roe       = isNaN(get('roe'))     ? -999 : get('roe');
  const roic      = isNaN(get('roic'))    ? -999 : get('roic');
  const sales3y   = isNaN(get('sales3y')) ? -999 : get('sales3y');
  const opinc3y   = isNaN(get('opinc3y')) ? -999 : get('opinc3y');
  const eps3y     = isNaN(get('eps3y'))   ? -999 : get('eps3y');
  const filtered = stocks.filter(s =>
    parseFloat(s['Altman Z-Score'])                   >= altman    &&
    parseFloat(s['Piotroski F Score'])                >= piotroski &&
    parseFloat(s['Debt / Equity'])                    <= debt      &&
    pct(s['Net Margin'])                              >= margin    &&
    pct(s['Return on Equity'])                        >= roe       &&
    pct(s['ROIC'])                                    >= roic      &&
    pct(s['Sales 3-Year Avg (%)'])                    >= sales3y   &&
    pct(s['Operating Income 3-Year Avg (%)'])         >= opinc3y   &&
    pct(s['EPS 3-Year Avg (%)'])                      >= eps3y
  );
  displayStocks(filtered);
  showToast(`Found ${filtered.length} matching stock${filtered.length !== 1 ? 's' : ''}`);
}

function resetFilters() {
  applyPreset(document.querySelector('[data-preset="quality"]'), 'quality');
}

function applyPreset(btn, name) {
  const p = PRESETS[name];
  if(!p) return;
  document.getElementById('altman').value    = p.altman;
  document.getElementById('piotroski').value = p.piotroski;
  document.getElementById('debt').value      = p.debt;
  document.getElementById('margin').value    = p.margin;
  document.getElementById('roe').value       = p.roe;
  document.getElementById('roic').value      = p.roic;
  document.getElementById('sales3y').value   = p.sales3y;
  document.getElementById('opinc3y').value   = p.opinc3y;
  document.getElementById('eps3y').value     = p.eps3y;
  document.querySelectorAll('.sc-preset').forEach(b => b.classList.remove('active'));
  if(btn) btn.classList.add('active');
  runScreener();
  if(name !== 'quality') showToast(btn.textContent.trim() + ' preset applied');
}

function sortTable(col) {
  sortDirs[col] = !sortDirs[col];
  const dir = sortDirs[col] ? 1 : -1;
  document.querySelectorAll('th').forEach((th, i) => {
    th.classList.remove('asc','desc');
    if(i === col) th.classList.add(dir === 1 ? 'asc' : 'desc');
  });
  const tbody = document.getElementById('stockBody');
  Array.from(tbody.querySelectorAll('tr'))
    .sort((a, b) => {
      const av = a.cells[col]?.innerText.trim() || '';
      const bv = b.cells[col]?.innerText.trim() || '';
      return av.localeCompare(bv, undefined, {numeric:true}) * dir;
    })
    .forEach(r => tbody.appendChild(r));
}

function showChart(ticker) {
  document.getElementById('chartTitle').textContent = ticker + ' — Price Chart';
  document.getElementById('chartContainer').innerHTML =
    `<iframe src="https://s.tradingview.com/widgetembed/?symbol=${encodeURIComponent(ticker)}&interval=D&theme=dark&style=1&locale=en"
     width="100%" height="420" frameborder="0" allowtransparency="true" scrolling="no"></iframe>`;
  document.getElementById('chartOverlay').classList.add('active');
}

function closeChart() {
  document.getElementById('chartOverlay').classList.remove('active');
  document.getElementById('chartContainer').innerHTML = '';
}

function overlayClick(e) {
  if(e.target === document.getElementById('chartOverlay')) closeChart();
}

document.addEventListener('keydown', e => { if(e.key === 'Escape') closeChart(); });

function togglePanel(btn) {
  const body      = document.getElementById('panelBody');
  const collapsed = body.classList.toggle('collapsed');
  btn.querySelector('span').textContent  = collapsed ? 'Expand' : 'Collapse';
  btn.querySelector('i').style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
}

let _toastTimer;
function showToast(msg) {
  document.getElementById('toastMsg').textContent = msg;
  const t = document.getElementById('scToast');
  t.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}

</script>

<?php include 'includes/footer.php'; ?>
