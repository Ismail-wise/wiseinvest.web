<?php include 'includes/header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

/* ══════════════════════════════════════════════════════════════
   INSTITUTIONAL WEALTH SIMULATOR — DEEP-SPACE TERMINAL v3
   Font: Space Grotesk · DM Sans · JetBrains Mono
   ══════════════════════════════════════════════════════════════ */
:root {
  --gold:    #d4af37;
  --gold-l:  #facc15;
  --cyan:    #22d3ee;
  --cyan-l:  #67e8f9;
  --violet:  #a78bfa;
  --green:   #34d399;
  --red:     #f87171;
  --amber:   #fbbf24;
  --navy:    #020617;
  --card:    rgba(4,9,22,.92);
  --ink:     #f1f5f9;
  --muted:   #475569;
  --ff-h:    'Space Grotesk', system-ui, sans-serif;
  --ff-b:    'DM Sans', system-ui, sans-serif;
  --ff-m:    'JetBrains Mono', monospace;
}

/* ── Keyframes ─────────────────────────────────────────── */
@keyframes vUp      { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:none} }
@keyframes vShim    { from{background-position:-300% center} to{background-position:300% center} }
@keyframes vGrid    { from{background-position:0 0} to{background-position:56px 56px} }
@keyframes vOrb     { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(14px,-12px) scale(1.05)} }
@keyframes vPulse   { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(.55)} }
@keyframes vCardIn  { from{opacity:0;transform:translateY(20px) scale(.97)} to{opacity:1;transform:none} }
@keyframes vScan    { from{top:-80px;opacity:1} to{top:115%;opacity:0} }
@keyframes vGlow    { from{box-shadow:0 0 0 rgba(34,211,238,0)} to{box-shadow:0 0 30px rgba(34,211,238,.07)} }
@keyframes vBorderPulse { 0%,100%{opacity:.4} 50%{opacity:1} }
@keyframes vMetricPop { 0%{opacity:0;transform:scale(.8) translateY(8px)} 70%{transform:scale(1.05)} 100%{opacity:1;transform:none} }
@keyframes vBarFlow { from{transform:translateX(-100%)} to{transform:translateX(100%)} }
@keyframes vRowIn   { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:none} }
@keyframes vFadeIn  { from{opacity:0} to{opacity:1} }
@keyframes vRotate  { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
@keyframes vFloat   { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }

*, *::before, *::after { box-sizing: border-box; }
input, button, select { font-family: inherit; }
body {
  background: var(--navy); color: var(--ink);
  font-family: var(--ff-b); margin: 0;
  -webkit-font-smoothing: antialiased;
}

/* ── Fixed Background ──────────────────────────────────── */
.vac-bg {
  position: fixed; inset: 0; pointer-events: none; z-index: 0;
  background:
    linear-gradient(rgba(34,211,238,.015) 1px, transparent 1px),
    linear-gradient(90deg, rgba(34,211,238,.015) 1px, transparent 1px);
  background-size: 56px 56px;
  animation: vGrid 28s linear infinite;
}
.vac-bg::after {
  content: ''; position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 50% at 65% -5%,  rgba(34,211,238,.07) 0%, transparent 55%),
    radial-gradient(ellipse 55% 45% at  5% 80%,  rgba(212,175,55,.05) 0%, transparent 50%),
    radial-gradient(ellipse 45% 40% at 95% 60%,  rgba(167,139,250,.04) 0%, transparent 50%);
}
#vac-particles {
  position: fixed; inset: 0; z-index: 0;
  pointer-events: none; opacity: .25;
}
.vac-orb {
  position: fixed; border-radius: 50%; filter: blur(100px);
  pointer-events: none; z-index: 0; animation: vOrb ease-in-out infinite;
}
.vac-orb1 { width:520px;height:520px;background:rgba(34,211,238,.04); top:-160px;right:-100px;animation-duration:14s; }
.vac-orb2 { width:380px;height:380px;background:rgba(212,175,55,.04); bottom:-80px;left:-80px;animation-duration:18s;animation-delay:-9s; }

/* ── Layout ─────────────────────────────────────────────── */
.dashboard-wrapper { display:flex; min-height:100vh; position:relative; z-index:1; }
.main { flex:1; padding:32px 40px 80px; min-width:0; }

/* ── HERO ────────────────────────────────────────────────── */
.vac-hero {
  position: relative; border-radius: 26px; overflow: hidden;
  background: linear-gradient(145deg, #070f22 0%, #030a18 100%);
  border: 1px solid rgba(255,255,255,.07);
  padding: 42px 44px 38px; margin-bottom: 28px;
  animation: vUp .65s ease both;
  box-shadow: 0 28px 70px rgba(0,0,0,.55);
}
/* Animated top accent */
.vac-hero::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--cyan), var(--violet), var(--gold), transparent);
  animation: vBorderPulse 4s ease-in-out infinite;
}
/* Subtle inner grid */
.vac-hero::after {
  content: ''; position: absolute; inset: 0; pointer-events: none;
  background:
    linear-gradient(rgba(255,255,255,.012) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.012) 1px, transparent 1px);
  background-size: 32px 32px;
}
.vac-hero-inner { position: relative; z-index: 2; }

/* Badge */
.vac-hero-badge {
  display: inline-flex; align-items: center; gap: 9px;
  font-family: var(--ff-h); font-size: .65rem; font-weight: 700;
  letter-spacing: 3px; text-transform: uppercase; color: var(--cyan);
  padding: 6px 18px; background: rgba(34,211,238,.07);
  border: 1px solid rgba(34,211,238,.22); border-radius: 99px; margin-bottom: 16px;
}
.vac-live-dot {
  width: 7px; height: 7px; border-radius: 50%; background: var(--cyan);
  box-shadow: 0 0 10px var(--cyan); animation: vPulse 1.8s ease-in-out infinite;
}

/* Title */
.vac-hero h1 {
  font-family: var(--ff-h); font-weight: 800;
  font-size: clamp(2rem, 3.5vw, 3rem); line-height: 1.05;
  margin: 0 0 12px; letter-spacing: -.6px;
  background: linear-gradient(135deg, #fff 15%, #f1f5f9 35%, var(--cyan-l) 60%, var(--violet) 90%);
  background-size: 200% auto;
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  animation: vShim 7s linear infinite;
}
.vac-hero p {
  font-family: var(--ff-b); color: var(--muted); font-size: .92rem;
  margin: 0 0 28px; line-height: 1.65; max-width: 560px;
}

/* Hero stats strip */
.vac-hero-stats {
  display: flex; gap: 0; flex-wrap: wrap;
  background: rgba(255,255,255,.02); border: 1px solid rgba(255,255,255,.07);
  border-radius: 14px; overflow: hidden; display: inline-flex;
}
.vac-hstat {
  padding: 14px 28px; text-align: center; position: relative;
  transition: background .25s;
}
.vac-hstat:hover { background: rgba(34,211,238,.05); }
.vac-hstat::after {
  content: ''; position: absolute; top: 16px; bottom: 16px; right: 0;
  width: 1px; background: rgba(255,255,255,.07);
}
.vac-hstat:last-child::after { display: none; }
.vac-hstat-num {
  display: block; font-family: var(--ff-m); font-size: 1.5rem; font-weight: 700;
  color: var(--cyan); text-shadow: 0 0 22px rgba(34,211,238,.4); line-height: 1;
}
.vac-hstat-lbl {
  display: block; font-family: var(--ff-h); font-size: .58rem; font-weight: 700;
  color: var(--muted); text-transform: uppercase; letter-spacing: 1.8px; margin-top: 5px;
}

/* ── INPUT PANEL ─────────────────────────────────────────── */
.vac-panel {
  background: linear-gradient(160deg, rgba(6,11,26,.95), rgba(3,7,18,.97));
  border: 1px solid rgba(255,255,255,.07); border-radius: 24px;
  padding: 32px 36px 36px; margin-bottom: 24px;
  position: relative; overflow: hidden;
  animation: vUp .65s .06s ease both;
  box-shadow: 0 24px 65px rgba(0,0,0,.5);
}
.vac-panel::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(34,211,238,.25), transparent);
}
/* Scan line injected on simulation run */
.vac-scan {
  position: absolute; left: 0; right: 0; height: 80px;
  background: linear-gradient(transparent, rgba(34,211,238,.04), transparent);
  pointer-events: none; top: -80px; opacity: 0;
}
.vac-scan.run { animation: vScan 1.4s ease-out forwards; }

/* Panel section header */
.vac-section-head {
  display: flex; align-items: center; gap: 13px; margin-bottom: 26px;
}
.vac-section-icon {
  width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
  background: rgba(34,211,238,.1); border: 1px solid rgba(34,211,238,.22);
  display: flex; align-items: center; justify-content: center;
  color: var(--cyan); font-size: .9rem;
  animation: vFloat 5s ease-in-out infinite;
}
.vac-section-title {
  font-family: var(--ff-h); font-size: 1rem; font-weight: 700; color: #f1f5f9;
}
.vac-section-sub {
  font-family: var(--ff-b); font-size: .78rem; color: var(--muted); margin-top: 2px;
}

/* Input grid */
.vac-input-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
  gap: 18px; margin-bottom: 28px; padding-bottom: 28px;
  border-bottom: 1px solid rgba(255,255,255,.05);
}
.vac-field { display: flex; flex-direction: column; gap: 8px; }
.vac-field-label {
  font-family: var(--ff-h); font-size: .62rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 2px; color: var(--muted);
  display: flex; align-items: center; gap: 7px;
}
.vac-field-label i {
  font-size: .65rem; color: var(--cyan); opacity: .6; transition: opacity .2s;
}
.vac-field:focus-within .vac-field-label i { opacity: 1; }

.vac-field input, .vac-field select {
  font-family: var(--ff-m); font-size: .88rem; font-weight: 500;
  background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.09);
  border-radius: 12px; padding: 12px 15px; color: var(--ink); outline: none;
  transition: border-color .25s, box-shadow .25s, background .25s, transform .2s;
  -webkit-appearance: none; appearance: none; width: 100%;
}
.vac-field input::placeholder { color: rgba(255,255,255,.2); font-family: var(--ff-b); }
.vac-field input:hover, .vac-field select:hover { border-color: rgba(255,255,255,.2); }
.vac-field input:focus, .vac-field select:focus {
  border-color: var(--cyan);
  background: rgba(34,211,238,.04);
  box-shadow: 0 0 0 3px rgba(34,211,238,.12), inset 0 0 0 1px var(--cyan);
  transform: translateY(-1px);
}
.vac-field select option { background: #0c1930; }

/* ── METRIC CARDS ────────────────────────────────────────── */
.vac-metrics {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 16px; margin-bottom: 24px;
}
.vac-metric {
  border-radius: 20px; padding: 22px 18px 20px;
  background: linear-gradient(160deg, rgba(6,11,26,.98), rgba(3,7,18,.98));
  border: 1px solid rgba(255,255,255,.07);
  position: relative; overflow: hidden; text-align: center;
  transition: transform .4s cubic-bezier(.34,1.56,.64,1), border-color .3s, box-shadow .3s;
  animation: vCardIn .55s ease both;
  cursor: default;
}
.vac-metric::before {
  content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%);
  width: 60%; height: 2px;
  background: var(--m-accent, linear-gradient(90deg, transparent, var(--gold), transparent));
}
.vac-metric:hover {
  transform: translateY(-8px);
  border-color: var(--m-border, rgba(212,175,55,.3));
  box-shadow: 0 24px 56px rgba(0,0,0,.5), 0 0 30px var(--m-glow, rgba(212,175,55,.06));
}
/* Shimmer bar on update */
.vac-metric::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,.04) 50%, transparent 65%);
  transform: translateX(-200%); pointer-events: none;
}
.vac-metric.updated::after { animation: vBarFlow .5s ease-out; }

.vac-metric:nth-child(1) { --m-accent:linear-gradient(90deg,transparent,var(--amber),transparent); --m-border:rgba(251,191,36,.3); --m-glow:rgba(251,191,36,.07); animation-delay:.05s }
.vac-metric:nth-child(2) { --m-accent:linear-gradient(90deg,transparent,var(--cyan),transparent);  --m-border:rgba(34,211,238,.3);  --m-glow:rgba(34,211,238,.07);  animation-delay:.1s  }
.vac-metric:nth-child(3) { --m-accent:linear-gradient(90deg,transparent,var(--violet),transparent);--m-border:rgba(167,139,250,.3); --m-glow:rgba(167,139,250,.07); animation-delay:.15s }
.vac-metric:nth-child(4) { --m-accent:linear-gradient(90deg,transparent,var(--green),transparent);  --m-border:rgba(52,211,153,.3);  --m-glow:rgba(52,211,153,.07);  animation-delay:.2s  }

.vac-metric-icon {
  width: 38px; height: 38px; border-radius: 11px; margin: 0 auto 14px;
  display: flex; align-items: center; justify-content: center; font-size: .9rem;
  background: var(--m-icon-bg, rgba(212,175,55,.1));
  border: 1px solid var(--m-icon-brd, rgba(212,175,55,.2));
  color: var(--m-icon-c, var(--amber));
}
.vac-metric:nth-child(1) .vac-metric-icon { --m-icon-bg:rgba(251,191,36,.1); --m-icon-brd:rgba(251,191,36,.2); --m-icon-c:var(--amber); }
.vac-metric:nth-child(2) .vac-metric-icon { --m-icon-bg:rgba(34,211,238,.1); --m-icon-brd:rgba(34,211,238,.2); --m-icon-c:var(--cyan); }
.vac-metric:nth-child(3) .vac-metric-icon { --m-icon-bg:rgba(167,139,250,.1);--m-icon-brd:rgba(167,139,250,.2);--m-icon-c:var(--violet); }
.vac-metric:nth-child(4) .vac-metric-icon { --m-icon-bg:rgba(52,211,153,.1); --m-icon-brd:rgba(52,211,153,.2); --m-icon-c:var(--green); }

.vac-metric-label {
  font-family: var(--ff-h); font-size: .58rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 2.2px; color: var(--muted);
  margin-bottom: 10px;
}
.vac-metric-value {
  font-family: var(--ff-m); font-size: 1.55rem; font-weight: 700;
  line-height: 1; letter-spacing: -.5px;
  color: var(--m-val-c, var(--amber));
  text-shadow: 0 0 22px var(--m-val-glow, rgba(251,191,36,.3));
  transition: color .4s, text-shadow .4s;
}
.vac-metric:nth-child(1) .vac-metric-value { --m-val-c:var(--amber);  --m-val-glow:rgba(251,191,36,.3); }
.vac-metric:nth-child(2) .vac-metric-value { --m-val-c:var(--cyan);   --m-val-glow:rgba(34,211,238,.3); }
.vac-metric:nth-child(3) .vac-metric-value { --m-val-c:var(--violet); --m-val-glow:rgba(167,139,250,.3); }
.vac-metric:nth-child(4) .vac-metric-value { --m-val-c:var(--green);  --m-val-glow:rgba(52,211,153,.3); }

/* Success rate dynamic coloring */
.vac-metric:nth-child(4).rate-good   .vac-metric-value { color: var(--green);  text-shadow: 0 0 22px rgba(52,211,153,.4); }
.vac-metric:nth-child(4).rate-ok     .vac-metric-value { color: var(--amber);  text-shadow: 0 0 22px rgba(251,191,36,.4); }
.vac-metric:nth-child(4).rate-bad    .vac-metric-value { color: var(--red);    text-shadow: 0 0 22px rgba(248,113,113,.4); }

/* ── RESULT INSIGHT BOXES ────────────────────────────────── */
.vac-result-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 18px; margin-bottom: 24px;
}
.vac-result-box {
  background: rgba(255,255,255,.02); border-radius: 20px;
  border: 1px solid rgba(255,255,255,.07);
  padding: 22px 24px; position: relative; overflow: hidden;
  transition: border-color .3s, box-shadow .3s;
  animation: vCardIn .6s .25s ease both;
}
.vac-result-box::before {
  content: ''; position: absolute; top: 0; left: 0; bottom: 0; width: 3px;
  background: var(--rb-line, linear-gradient(180deg, var(--cyan), var(--cyan-l)));
  border-radius: 3px 0 0 3px;
}
.vac-result-box:nth-child(2)::before { --rb-line: linear-gradient(180deg, var(--violet), #c4b5fd); }
.vac-result-box:hover {
  border-color: rgba(34,211,238,.18);
  box-shadow: 0 12px 40px rgba(0,0,0,.35);
}
.vac-result-box:nth-child(2):hover { border-color: rgba(167,139,250,.18); }

.vac-result-head {
  display: flex; align-items: center; gap: 9px; margin-bottom: 14px;
}
.vac-result-dot {
  width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
  background: var(--cyan); box-shadow: 0 0 8px var(--cyan);
  animation: vPulse 2s ease-in-out infinite;
}
.vac-result-box:nth-child(2) .vac-result-dot { background: var(--violet); box-shadow: 0 0 8px var(--violet); }
.vac-result-label {
  font-family: var(--ff-h); font-size: .62rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 2px; color: var(--cyan);
}
.vac-result-box:nth-child(2) .vac-result-label { color: var(--violet); }

#eng, #bur {
  font-family: var(--ff-b); font-size: .85rem; color: #64748b; line-height: 1.8;
  transition: color .4s;
}
#eng.populated, #bur.populated { color: #94a3b8; animation: vFadeIn .5s ease; }
#eng b, #bur b { color: #e2e8f0; font-weight: 600; font-family: var(--ff-m); }

/* ── CHART SECTION ───────────────────────────────────────── */
.vac-chart-card {
  background: linear-gradient(160deg, rgba(6,11,26,.97), rgba(3,7,18,.98));
  border: 1px solid rgba(255,255,255,.07); border-radius: 24px;
  padding: 28px 32px 32px; position: relative; overflow: hidden;
  animation: vCardIn .65s .35s ease both;
  box-shadow: 0 24px 65px rgba(0,0,0,.5);
}
.vac-chart-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(34,211,238,.2), var(--violet), transparent);
}
.vac-chart-head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}
.vac-chart-title {
  display: flex; align-items: center; gap: 12px;
}
.vac-chart-title-icon {
  width: 38px; height: 38px; border-radius: 11px;
  background: rgba(34,211,238,.1); border: 1px solid rgba(34,211,238,.22);
  display: flex; align-items: center; justify-content: center;
  color: var(--cyan); font-size: .9rem;
}
.vac-chart-title h3 {
  font-family: var(--ff-h); font-size: 1rem; font-weight: 700; color: #f1f5f9; margin: 0;
}
.vac-chart-title p {
  font-family: var(--ff-b); font-size: .75rem; color: var(--muted); margin: 2px 0 0;
}
.vac-chart-badge {
  display: flex; align-items: center; gap: 7px;
  font-family: var(--ff-h); font-size: .63rem; font-weight: 700;
  letter-spacing: 1.5px; text-transform: uppercase; color: var(--muted);
  background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08);
  padding: 6px 14px; border-radius: 99px;
}
.vac-chart-badge span {
  width: 6px; height: 6px; border-radius: 50%; background: var(--green);
  box-shadow: 0 0 6px rgba(52,211,153,.8); animation: vPulse 2s ease-in-out infinite;
}
canvas { max-height: 420px; }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media(max-width:1100px){ .vac-metrics{ grid-template-columns:1fr 1fr; } }
@media(max-width:900px) { .vac-result-grid{ grid-template-columns:1fr; } .vac-hero-stats{ flex-wrap:wrap; } }
@media(max-width:768px) { .main{ padding:20px 18px 60px; } .vac-panel{ padding:24px 20px; } .vac-hero{ padding:28px 22px 24px; } }
@media(max-width:540px) { .vac-metrics{ grid-template-columns:1fr; } .vac-input-grid{ grid-template-columns:1fr; } }
</style>

<!-- ═══ BACKGROUND LAYERS ═══ -->
<canvas id="vac-particles"></canvas>
<div class="vac-bg"></div>
<div class="vac-orb vac-orb1"></div>
<div class="vac-orb vac-orb2"></div>

<div class="dashboard-wrapper">
  <?php include 'includes/sidebar.php'; ?>

  <div class="main">

    <!-- ── HERO ───────────────────────────────────────────── -->
    <div class="vac-hero">
      <div class="vac-hero-inner">
        <div class="vac-hero-badge"><span class="vac-live-dot"></span> Quantitative Finance · Monte Carlo Engine</div>
        <h1>Institutional Wealth Simulator</h1>
        <p>Strategic capital projection powered by 1,000-iteration Monte Carlo stress-testing. Model every possible market scenario and find your true path to financial independence.</p>
        <div class="vac-hero-stats">
          <div class="vac-hstat">
            <span class="vac-hstat-num">1,000</span>
            <span class="vac-hstat-lbl">Simulations</span>
          </div>
          <div class="vac-hstat">
            <span class="vac-hstat-num">±15%</span>
            <span class="vac-hstat-lbl">Market Variance</span>
          </div>
          <div class="vac-hstat">
            <span class="vac-hstat-num">5</span>
            <span class="vac-hstat-lbl">Currencies</span>
          </div>
          <div class="vac-hstat">
            <span class="vac-hstat-num">Real-time</span>
            <span class="vac-hstat-lbl">Computation</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── INPUT + RESULTS PANEL ─────────────────────────── -->
    <div class="vac-panel">
      <div class="vac-scan" id="vacScan"></div>

      <!-- Panel header -->
      <div class="vac-section-head">
        <div class="vac-section-icon"><i class="fas fa-sliders"></i></div>
        <div>
          <div class="vac-section-title">Simulation Parameters</div>
          <div class="vac-section-sub">Adjust any value — results recalculate instantly</div>
        </div>
      </div>

      <!-- Input fields -->
      <div class="vac-input-grid">

        <div class="vac-field">
          <label class="vac-field-label"><i class="fas fa-wallet"></i> Annual Expenses</label>
          <input id="expense" type="number" value="40000" placeholder="40000">
        </div>

        <div class="vac-field">
          <label class="vac-field-label"><i class="fas fa-coins"></i> Currency</label>
          <select id="currency">
            <option value="USD">USD ($)</option>
            <option value="THB">THB (฿)</option>
            <option value="MMK">MMK (Ks)</option>
            <option value="KRW">KRW (₩)</option>
            <option value="JPY">JPY (¥)</option>
          </select>
        </div>

        <div class="vac-field">
          <label class="vac-field-label"><i class="fas fa-arrow-trend-up"></i> Inflation %</label>
          <input id="inflation" type="number" value="3" placeholder="3">
        </div>

        <div class="vac-field">
          <label class="vac-field-label"><i class="fas fa-piggy-bank"></i> Current Assets</label>
          <input id="assets" type="number" value="200000" placeholder="200000">
        </div>

        <div class="vac-field">
          <label class="vac-field-label"><i class="fas fa-chart-line"></i> Annual Savings</label>
          <input id="savings" type="number" value="15000" placeholder="15000">
        </div>

        <div class="vac-field">
          <label class="vac-field-label"><i class="fas fa-percent"></i> Expected Return %</label>
          <input id="return" type="number" value="7" placeholder="7">
        </div>

        <div class="vac-field">
          <label class="vac-field-label"><i class="fas fa-hourglass-half"></i> Life Capacity (Yrs)</label>
          <input id="life" type="number" value="30" placeholder="30">
        </div>

      </div>

      <!-- ── METRIC CARDS ───────────────────────────────────── -->
      <div class="vac-metrics">

        <div class="vac-metric" id="metricExp">
          <div class="vac-metric-icon"><i class="fas fa-receipt"></i></div>
          <div class="vac-metric-label">Adjusted Expenses</div>
          <div class="vac-metric-value" id="futureExp">—</div>
        </div>

        <div class="vac-metric" id="metricTarget">
          <div class="vac-metric-icon"><i class="fas fa-flag-checkered"></i></div>
          <div class="vac-metric-label">Freedom Capital</div>
          <div class="vac-metric-value" id="target">—</div>
        </div>

        <div class="vac-metric" id="metricYears">
          <div class="vac-metric-icon"><i class="fas fa-hourglass"></i></div>
          <div class="vac-metric-label">Years to Freedom</div>
          <div class="vac-metric-value" id="yearsFreedom">—</div>
        </div>

        <div class="vac-metric" id="metricProb">
          <div class="vac-metric-icon"><i class="fas fa-shield-halved"></i></div>
          <div class="vac-metric-label">Success Rate</div>
          <div class="vac-metric-value" id="prob">—</div>
        </div>

      </div>

      <!-- ── RESULT INSIGHT ─────────────────────────────────── -->
      <div class="vac-result-grid">
        <div class="vac-result-box">
          <div class="vac-result-head">
            <span class="vac-result-dot"></span>
            <span class="vac-result-label">Strategic Insight</span>
          </div>
          <div id="eng">Run the simulation to see your strategic analysis.</div>
        </div>
        <div class="vac-result-box">
          <div class="vac-result-head">
            <span class="vac-result-dot"></span>
            <span class="vac-result-label">မြန်မာရှင်းလင်းချက်</span>
          </div>
          <div id="bur">Simulation ပြုလုပ်ပြီးနောက် ရလဒ်ပြပါမည်။</div>
        </div>
      </div>

      <!-- ── CHART ──────────────────────────────────────────── -->
      <div class="vac-chart-card">
        <div class="vac-chart-head">
          <div class="vac-chart-title">
            <div class="vac-chart-title-icon"><i class="fas fa-chart-area"></i></div>
            <div>
              <h3>Wealth Accumulation Curve</h3>
              <p>Projected growth path to financial independence</p>
            </div>
          </div>
          <div class="vac-chart-badge">
            <span></span> Monte Carlo · 1,000 Iterations
          </div>
        </div>
        <canvas id="wealthChart"></canvas>
      </div>

    </div><!-- /.vac-panel -->

  </div><!-- /.main -->
</div><!-- /.dashboard-wrapper -->

<script>
/* ════════════════════════════════════════════════════════════
   ALL ORIGINAL CALCULATION LOGIC — PRESERVED EXACTLY
   ════════════════════════════════════════════════════════════ */
const currencyConfig = {
    USD: { symbol: "$",  locale: "en-US" },
    THB: { symbol: "฿",  locale: "th-TH" },
    MMK: { symbol: "Ks", locale: "my-MM" },
    KRW: { symbol: "₩",  locale: "ko-KR" },
    JPY: { symbol: "¥",  locale: "ja-JP" }
};

let mainChart = null;

function runSimulation() {
    const expense = Number(document.getElementById("expense").value);
    const infl    = Number(document.getElementById("inflation").value) / 100;
    const assets  = Number(document.getElementById("assets").value);
    const savings = Number(document.getElementById("savings").value);
    const r       = Number(document.getElementById("return").value) / 100;
    const life    = Number(document.getElementById("life").value);
    const simCount = 1000;

    const futureExp = expense * Math.pow(1 + infl, 20);
    const target    = futureExp * ((1 - Math.pow(1 + r, -life)) / r) * (1 + r);

    let currentWealth = assets;
    let yearsNeeded   = 0;
    let dataPoints    = [assets];
    let labelPoints   = ["Year 0"];

    while (yearsNeeded < 50) {
        currentWealth = (currentWealth * (1 + r)) + savings;
        yearsNeeded++;
        dataPoints.push(Math.round(currentWealth));
        labelPoints.push("Yr " + yearsNeeded);
        if (currentWealth >= target) break;
    }

    let successCount = 0;
    for (let i = 0; i < simCount; i++) {
        let simWealth = target;
        for (let y = 0; y < life; y++) {
            let marketVariance = (Math.random() * 2 - 1) * 0.15;
            let actualReturn   = r + marketVariance;
            simWealth = (simWealth * (1 + actualReturn)) - futureExp;
            if (simWealth <= 0) break;
        }
        if (simWealth > 0) successCount++;
    }
    const probability = (successCount / simCount) * 100;

    // Update UI with animation
    setMetricValue("futureExp",    formatCurrency(futureExp),  "metricExp");
    setMetricValue("target",       formatCurrency(target),     "metricTarget");
    setMetricValue("yearsFreedom", yearsNeeded + " yrs",       "metricYears");
    setMetricValue("prob",         probability.toFixed(1) + "%", "metricProb");

    // Color-code success rate
    const probCard = document.getElementById("metricProb");
    probCard.classList.remove("rate-good","rate-ok","rate-bad");
    if (probability >= 80)      probCard.classList.add("rate-good");
    else if (probability >= 60) probCard.classList.add("rate-ok");
    else                        probCard.classList.add("rate-bad");

    updateTexts(probability, target, yearsNeeded);
    renderChart(labelPoints, dataPoints);
    triggerScan();
}

function setMetricValue(id, value, cardId) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
    const card = document.getElementById(cardId);
    if (card) {
        card.classList.remove("updated");
        void card.offsetWidth;
        card.classList.add("updated");
    }
}

function triggerScan() {
    const scan = document.getElementById("vacScan");
    if (!scan) return;
    scan.classList.remove("run");
    void scan.offsetWidth;
    scan.classList.add("run");
}

function updateTexts(prob, target, years) {
    const eng = document.getElementById("eng");
    const bur = document.getElementById("bur");

    eng.innerHTML =
        `To sustain your target lifestyle, you require a capital base of <b>${formatCurrency(target)}</b>. ` +
        `Given your current trajectory, independence is achievable in <b>${years} years</b>. ` +
        `Monte Carlo stress-testing indicates a <b>${prob.toFixed(1)}%</b> confidence level.`;
    eng.classList.add("populated");

    bur.innerHTML =
        `သင်မျှော်မှန်းထားသော လူနေမှုစနစ်အတွက် အရင်းအနှီး <b>${formatCurrency(target)}</b> လိုအပ်ပါသည်။ ` +
        `လက်ရှိ စုဆောင်းမှုနှုန်းအရ <b>${years} နှစ်</b> အတွင်း ပန်းတိုင်ရောက်နိုင်ပြီး၊ ` +
        `ဈေးကွက်အပြောင်းအလဲများအကြား အောင်မြင်နိုင်ခြေ <b>${prob.toFixed(1)}%</b> ရှိပါသည်။`;
    bur.classList.add("populated");
}

function renderChart(labels, data) {
    const ctx = document.getElementById('wealthChart').getContext('2d');
    if (mainChart) mainChart.destroy();

    const gradient = ctx.createLinearGradient(0, 0, 0, 420);
    gradient.addColorStop(0,   'rgba(34,211,238,.22)');
    gradient.addColorStop(0.6, 'rgba(34,211,238,.06)');
    gradient.addColorStop(1,   'rgba(34,211,238,0)');

    mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                borderColor: '#22d3ee',
                backgroundColor: gradient,
                fill: true, tension: 0.45, borderWidth: 2.5,
                pointRadius: 0, pointHoverRadius: 7,
                pointHoverBackgroundColor: '#d4af37',
                pointHoverBorderColor: 'transparent',
                pointHoverBorderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 800, easing: 'easeInOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(4,9,22,.97)',
                    borderColor: 'rgba(34,211,238,.3)',
                    borderWidth: 1,
                    padding: 12,
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    titleFont: { family: "'Space Grotesk', sans-serif", weight: '700', size: 11 },
                    bodyFont:  { family: "'JetBrains Mono', monospace", size: 13 },
                    callbacks: {
                        label: ctx => '  ' + formatCurrency(ctx.parsed.y)
                    }
                }
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },
                    ticks: {
                        color: '#334155',
                        font: { family: "'JetBrains Mono', monospace", size: 11 },
                        callback: v => formatCurrency(v),
                        maxTicksLimit: 6,
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#334155',
                        font: { family: "'DM Sans', sans-serif", size: 11 },
                        maxTicksLimit: 8,
                    }
                }
            }
        }
    });
}

function formatCurrency(value) {
    const currency = document.getElementById("currency").value;
    const config   = currencyConfig[currency];
    return config.symbol + " " + Math.round(value).toLocaleString(config.locale);
}

document.querySelectorAll("input, select").forEach(el => {
    el.addEventListener("input", runSimulation);
});

window.onload = runSimulation;

/* ════════════════════════════════════════════════════════════
   PARTICLE NETWORK BACKGROUND
   ════════════════════════════════════════════════════════════ */
(function () {
    const canvas = document.getElementById('vac-particles');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let pts = [], w = 0, h = 0;

    function resize() {
        w = canvas.width  = window.innerWidth;
        h = canvas.height = window.innerHeight;
        const target = Math.min(60, Math.floor((w * h) / 20000));
        while (pts.length < target) pts.push({
            x:  Math.random() * w, y: Math.random() * h,
            vx: (Math.random() - .5) * .35, vy: (Math.random() - .5) * .35,
            r:  Math.random() * 1.3 + .4,
            hue: Math.random() < .6 ? 190 : 265
        });
    }

    function draw() {
        ctx.clearRect(0, 0, w, h);
        for (let i = 0; i < pts.length; i++) {
            for (let j = i + 1; j < pts.length; j++) {
                const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y;
                const d  = Math.hypot(dx, dy);
                if (d < 120) {
                    ctx.beginPath();
                    ctx.strokeStyle = `hsla(190,80%,68%,${0.1 * (1 - d / 120)})`;
                    ctx.lineWidth   = .5;
                    ctx.moveTo(pts[i].x, pts[i].y);
                    ctx.lineTo(pts[j].x, pts[j].y);
                    ctx.stroke();
                }
            }
        }
        for (const p of pts) {
            p.x += p.vx; p.y += p.vy;
            if (p.x < 0) p.x = w; if (p.x > w) p.x = 0;
            if (p.y < 0) p.y = h; if (p.y > h) p.y = 0;
            ctx.beginPath();
            ctx.fillStyle = `hsla(${p.hue},85%,68%,.45)`;
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
        }
        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', () => { resize(); }, { passive: true });
    resize(); draw();
}());
</script>

<?php include 'includes/footer.php'; ?>
