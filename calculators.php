<?php
include 'includes/db.php';
include 'includes/header.php';

if (file_exists('includes/functions.php')) {
    include 'includes/functions.php';
}

if (isset($_GET['get_stock_data'])) {
    ob_clean();
    $ticker = $_GET['get_stock_data'];
    $data = function_exists('getStockQuote') ? getStockQuote($ticker, $conn) : ['price' => 0, 'change' => 0, 'percent' => 0];
    echo json_encode($data);
    exit();
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

/* ══════════════════════════════════════════════════════════
   WISE INVEST CALCULATOR — WORLD-CLASS REDESIGN
   The most premium financial calculator interface on earth.
   Font: Space Grotesk + DM Sans + JetBrains Mono
   ══════════════════════════════════════════════════════════ */

/* ── 01. DESIGN TOKENS ───────────────────────────────── */
:root {
  /* Palette */
  --gold:    #d4af37;
  --gold-l:  #facc15;
  --gold-d:  #a8862a;
  --cyan:    #22d3ee;
  --cyan-l:  #6ee7ff;
  --cyan-d:  #0891b2;
  --violet:  #a78bfa;
  --violet-d:#7c3aed;
  --green:   #34d399;
  --green-l: #6ee7b7;
  --red:     #f87171;
  --amber:   #fbbf24;
  --navy:    #020617;
  --card:    rgba(6,12,28,.92);
  --ink:     #f1f5f9;
  --muted:   #475569;
  --muted2:  rgba(244,247,255,.55);
  --line:    rgba(255,255,255,.07);

  /* Type */
  --ff-head: 'Space Grotesk', system-ui, sans-serif;
  --ff-body: 'DM Sans', system-ui, sans-serif;
  --ff-mono: 'JetBrains Mono', monospace;

  /* Geometry */
  --radius: 22px;
  --radius-sm: 13px;
  --radius-pill: 999px;

  /* Shadows */
  --shadow-card: 0 28px 70px rgba(0,0,0,.6), inset 0 1px 0 rgba(255,255,255,.06);
  --shadow-btn:  0 8px 28px rgba(34,211,238,.25);
  --shadow-gold: 0 8px 28px rgba(212,175,55,.3);

  /* Header height compensation */
  --header-offset: 115px;
}

/* ── 02. GLOBAL RESET ────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }
input, button, select, textarea { font-family: inherit; }
input, button { font-size: 16px; } /* Prevents iOS zoom */
body {
  background: var(--navy); color: var(--ink);
  font-family: var(--ff-body); margin: 0;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* ── 03. KEYFRAMES ───────────────────────────────────── */
@keyframes fadeInDown      { from{opacity:0;transform:translateY(-22px)} to{opacity:1;transform:none} }
@keyframes fadeInUp        { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
@keyframes workspaceEnter  { 0%{opacity:0;transform:translateY(28px) scale(.985)} 100%{opacity:1;transform:none} }
@keyframes resEnter        { from{opacity:0;transform:translateY(16px) scale(.98)} to{opacity:1;transform:none} }
@keyframes resCardIn       { from{opacity:0;transform:translateY(10px) scale(.96)} to{opacity:1;transform:none} }
@keyframes cwShake         { 10%,90%{transform:translateX(-2px)} 20%,80%{transform:translateX(3px)} 30%,50%,70%{transform:translateX(-5px)} 40%,60%{transform:translateX(5px)} }
@keyframes shimmer         { from{background-position:-300% center} to{background-position:300% center} }
@keyframes tabShimmer      { 0%{transform:translateX(-200%)} 100%{transform:translateX(200%)} }
@keyframes scanSweep       { from{top:-90px;opacity:1} to{top:115%;opacity:0} }
@keyframes iconFloat       { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-6px) rotate(3deg)} }
@keyframes dotPulse        { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.35;transform:scale(.55)} }
@keyframes glowPulse       { 0%,100%{box-shadow:0 0 0 0 rgba(34,211,238,0)} 50%{box-shadow:0 0 0 8px rgba(34,211,238,0)} }
@keyframes rippleOut       { to{transform:scale(6);opacity:0} }
@keyframes gridDrift       { from{background-position:0 0} to{background-position:56px 56px} }
@keyframes orbFloat        { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(14px,-12px) scale(1.04)} }
@keyframes glitch1 {
  0%,86%,100%{clip-path:inset(0 0 100% 0);transform:none;opacity:0}
  88%{clip-path:inset(10% 0 62% 0);transform:translate(-3px,2px);opacity:.7}
  90%{clip-path:inset(56% 0 10% 0);transform:translate(3px,-2px);opacity:.7}
  92%{clip-path:inset(30% 0 40% 0);transform:translate(-2px,1px);opacity:.7}
  94%{clip-path:inset(0 0 100% 0);transform:none;opacity:0}
}
@keyframes heroTagIn       { from{opacity:0;transform:translateY(12px) scale(.9)} to{opacity:1;transform:none} }
@keyframes borderGlow      { 0%,100%{border-color:rgba(34,211,238,.2)} 50%{border-color:rgba(34,211,238,.55)} }
@keyframes statPop         { 0%{opacity:0;transform:scale(.7) translateY(8px)} 70%{transform:scale(1.06)} 100%{opacity:1;transform:none} }
@keyframes resultGlow      { from{box-shadow:0 0 0 rgba(34,211,238,0)} to{box-shadow:0 0 28px rgba(34,211,238,.08)} }

/* ── 04. ANIMATED BACKGROUND ─────────────────────────── */
.calc-bg-fx {
  position: fixed; inset: 0; z-index: -1; pointer-events: none; overflow: hidden;
  background:
    radial-gradient(ellipse 65% 40% at 15% 12%, rgba(34,211,238,.065) 0%, transparent 55%),
    radial-gradient(ellipse 55% 45% at 88% 20%, rgba(167,139,250,.06)  0%, transparent 55%),
    radial-gradient(ellipse 50% 50% at 50% 115%,rgba(212,175,55,.055) 0%, transparent 60%),
    linear-gradient(180deg, #050b1a, #020617);
}
/* Animated grid overlay */
.calc-bg-fx::before {
  content: ''; position: absolute; inset: 0;
  background:
    linear-gradient(rgba(34,211,238,.016) 1px, transparent 1px),
    linear-gradient(90deg, rgba(34,211,238,.016) 1px, transparent 1px);
  background-size: 56px 56px;
  animation: gridDrift 28s linear infinite;
}
/* Floating orbs */
.calc-bg-fx::after {
  content: ''; position: absolute;
  width: 600px; height: 600px; border-radius: 50%;
  filter: blur(110px); pointer-events: none;
  background: radial-gradient(circle, rgba(167,139,250,.06) 0%, transparent 65%);
  top: 20%; right: -200px;
  animation: orbFloat 16s ease-in-out infinite;
}

#cw-particles { position: absolute; inset: 0; opacity: .28; }
#cw-glow {
  position: absolute; width: 700px; height: 700px;
  top: 50%; left: 50%; transform: translate(-50%, -50%);
  opacity: .4; mix-blend-mode: screen;
  background: radial-gradient(circle, rgba(34,211,238,.14) 0%, rgba(167,139,250,.05) 40%, transparent 70%);
  transition: opacity .3s; pointer-events: none;
}

/* ── 05. PAGE SHELL ──────────────────────────────────── */
.calc-shell {
  position: relative; z-index: 1; min-height: 100vh; padding-bottom: 120px;
}

/* ── 06. HERO ────────────────────────────────────────── */
.calc-hero {
  padding: 88px 5% 56px;
  text-align: center;
  animation: fadeInDown .75s cubic-bezier(.4,0,.2,1) both;
}

/* Badge */
.hero-badge {
  display: inline-flex; align-items: center; gap: 10px;
  font-family: var(--ff-head); font-size: .7rem; font-weight: 700;
  letter-spacing: 3px; text-transform: uppercase; color: var(--cyan);
  background: rgba(34,211,238,.07); border: 1px solid rgba(34,211,238,.22);
  padding: 7px 20px; border-radius: var(--radius-pill); margin-bottom: 24px;
}
.hb-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--cyan); box-shadow: 0 0 10px var(--cyan);
  animation: dotPulse 1.8s ease-in-out infinite;
}

/* Title */
.calc-hero h1 {
  font-family: var(--ff-head); font-weight: 800;
  font-size: clamp(2.6rem, 6vw, 4.2rem);
  line-height: 1.05; letter-spacing: -.8px;
  margin: 0 0 14px; position: relative;
  background: linear-gradient(135deg, #fff 15%, #f1f5f9 35%, var(--cyan-l) 60%, var(--violet) 85%);
  background-size: 200% auto;
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  animation: shimmer 7s linear infinite;
}
.calc-hero h1::before {
  content: attr(data-text); position: absolute; inset: 0;
  background: linear-gradient(135deg, #fff, var(--cyan));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  animation: glitch1 10s infinite; opacity: 0;
}

/* Subtitle */
.calc-hero > p {
  font-family: var(--ff-body); font-size: 1rem; font-weight: 400;
  color: var(--muted); letter-spacing: .3px; margin-bottom: 0;
}
.calc-hero > p span { color: rgba(34,211,238,.6); }

/* Floating data tags */
.hero-tags {
  display: flex; justify-content: center; flex-wrap: wrap; gap: 10px;
  margin-top: 22px; margin-bottom: 0;
}
.hero-tag {
  display: inline-flex; align-items: center; gap: 7px;
  font-family: var(--ff-mono); font-size: .7rem; font-weight: 600; letter-spacing: .3px;
  padding: 6px 14px; border-radius: var(--radius-pill);
  border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.025);
  color: var(--muted); animation: heroTagIn .6s ease both;
}
.hero-tag:nth-child(1){animation-delay:.2s} .hero-tag:nth-child(2){animation-delay:.3s}
.hero-tag:nth-child(3){animation-delay:.4s} .hero-tag:nth-child(4){animation-delay:.5s}
.hero-tag i { font-size: .65rem; }
.hero-tag.tg-c { color:var(--cyan);  border-color:rgba(34,211,238,.2);  background:rgba(34,211,238,.04); }
.hero-tag.tg-g { color:var(--green); border-color:rgba(52,211,153,.2);  background:rgba(52,211,153,.04); }
.hero-tag.tg-v { color:var(--violet);border-color:rgba(167,139,250,.2); background:rgba(167,139,250,.04); }
.hero-tag.tg-a { color:var(--amber); border-color:rgba(251,191,36,.2);  background:rgba(251,191,36,.04); }

/* Stats row */
.hero-stats {
  display: flex; justify-content: center; align-items: stretch;
  gap: 0; margin-top: 44px; flex-wrap: wrap;
  background: rgba(255,255,255,.02); border: 1px solid rgba(255,255,255,.07);
  border-radius: 18px; overflow: hidden; display: inline-flex;
  animation: fadeInDown 1s .3s ease both;
}
.hero-stat {
  text-align: center; padding: 20px 36px; position: relative;
  transition: background .25s;
}
.hero-stat:hover { background: rgba(34,211,238,.04); }
.hero-stat::before {
  content: ''; position: absolute; top: 18px; bottom: 18px;
  right: 0; width: 1px; background: rgba(255,255,255,.07);
}
.hero-stat:last-child::before { display: none; }
.hs-num {
  display: block; font-family: var(--ff-mono); font-size: 2rem; font-weight: 700;
  color: var(--cyan); text-shadow: 0 0 28px rgba(34,211,238,.35);
  line-height: 1; animation: statPop .6s ease both;
}
.hs-num:nth-child(1){animation-delay:.35s}
.hs-label {
  display: block; font-family: var(--ff-head); font-size: .6rem; font-weight: 700;
  color: var(--muted); text-transform: uppercase; letter-spacing: 2px; margin-top: 6px;
}
.hero-divider { display: none; }

/* ── 07. STICKY NAVIGATION ───────────────────────────── */
.calc-nav-wrapper {
  position: sticky; top: var(--header-offset); z-index: 50;
  padding: 14px 5%;
  background: rgba(4,8,20,.82);
  border-top: 1px solid rgba(255,255,255,.04);
  border-bottom: 1px solid rgba(255,255,255,.08);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}
.calc-nav-wrapper::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(34,211,238,.3), transparent);
}

.calc-tabs {
  display: flex; flex-wrap: wrap; justify-content: center;
  gap: 8px; max-width: 1240px; margin: 0 auto;
}

.calc-tab-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 18px; border-radius: var(--radius-pill);
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.025);
  font-family: var(--ff-head); font-size: .78rem; font-weight: 700; letter-spacing: .3px;
  color: var(--muted); cursor: pointer; user-select: none;
  transition: all .25s cubic-bezier(.4,0,.2,1);
  position: relative; overflow: hidden;
}
.calc-tab-btn i { font-size: .78rem; opacity: .65; transition: opacity .2s, filter .2s; }
.calc-tab-btn:hover {
  transform: translateY(-2px);
  color: #e2e8f0; background: rgba(255,255,255,.07);
  border-color: rgba(34,211,238,.25);
  box-shadow: 0 8px 20px rgba(0,0,0,.35);
}
.calc-tab-btn:hover i { opacity: 1; filter: drop-shadow(0 0 4px var(--cyan)); color: var(--cyan); }

/* Active tab */
.calc-tab-btn.active {
  background: linear-gradient(135deg, rgba(34,211,238,.18), rgba(167,139,250,.14));
  border-color: rgba(34,211,238,.45);
  color: #fff;
  box-shadow: 0 0 18px rgba(34,211,238,.16), inset 0 0 0 1px rgba(34,211,238,.18);
  transform: scale(1.04);
}
.calc-tab-btn.active i { opacity: 1; color: var(--cyan); filter: drop-shadow(0 0 5px var(--cyan)); }
/* Shimmer sweep on active */
.calc-tab-btn.active::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(105deg, transparent 30%, rgba(34,211,238,.2) 50%, transparent 70%);
  animation: tabShimmer 2.5s linear infinite;
}

/* ── 08. WORKSPACE DASHBOARD ─────────────────────────── */
.calc-dashboard {
  padding: 52px 5% 80px;
  display: flex; justify-content: center;
}

/* Workspace panel */
.calc-workspace {
  width: 100%; max-width: 900px;
  padding: 48px;
  background: var(--card);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: var(--radius);
  box-shadow: var(--shadow-card);
  color: var(--ink);
  position: relative;
  overflow: hidden;

  display: none !important;
  opacity: 0; transform: translateY(20px);
}
.calc-workspace.active {
  display: block !important;
  opacity: 1; transform: none;
  animation: workspaceEnter .55s cubic-bezier(.34,1.56,.64,1) both;
}

/* Inner grid bg */
.calc-workspace::after {
  content: ''; position: absolute; inset: 0; pointer-events: none; z-index: 0;
  background:
    linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px);
  background-size: 32px 32px;
}

/* Top iridescent accent bar */
.calc-workspace::before {
  content: ''; position: absolute; left: 0; top: 0; width: 100%; height: 3px; z-index: 3;
  background: linear-gradient(90deg, transparent 0%, var(--cyan) 25%, var(--violet) 55%, var(--gold) 80%, transparent 100%);
  opacity: .75;
}

/* ── 09. WORKSPACE HEADER ────────────────────────────── */
.cw-header {
  text-align: center; margin-bottom: 36px; position: relative; z-index: 1;
}
.cw-icon {
  font-size: 2.8rem; display: block; margin-bottom: 16px; line-height: 1;
  animation: iconFloat 5s ease-in-out infinite;
  filter: drop-shadow(0 0 14px currentColor);
}
.cw-title {
  font-family: var(--ff-head); font-weight: 800;
  font-size: 2.4rem; line-height: 1.1; letter-spacing: -.4px;
  margin: 0 0 10px;
  background: linear-gradient(135deg, #fff 25%, var(--cyan-l) 70%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.cw-desc {
  font-family: var(--ff-body); font-size: 1rem; font-weight: 400;
  color: var(--muted); margin: 0; line-height: 1.6;
}

/* Scan-line (injected by JS) */
.scan-line {
  position: absolute; left: 0; top: -90px; width: 100%; height: 90px; z-index: 2;
  background: linear-gradient(180deg, transparent, rgba(34,211,238,.055), transparent);
  pointer-events: none;
}
.calc-workspace.active .scan-line { animation: scanSweep 1.8s ease-out forwards; }

/* ── 10. FORM GRID & INPUTS ──────────────────────────── */
.cw-grid {
  display: grid; grid-template-columns: repeat(2, minmax(0,1fr));
  gap: 22px; position: relative; z-index: 1;
}

.cw-label {
  display: block; margin-bottom: 9px;
  font-family: var(--ff-head); font-size: .65rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 2px; color: var(--muted);
}

/* Input wrapper (injected by JS for prefix) */
.cw-input-wrap { position: relative; }
.cw-pfx {
  position: absolute; left: 15px; top: 50%; transform: translateY(-50%); z-index: 1;
  font-family: var(--ff-mono); font-size: .88rem; font-weight: 600;
  color: var(--cyan); opacity: .45; pointer-events: none;
  transition: opacity .2s, color .2s;
}
.cw-input-wrap:focus-within .cw-pfx { opacity: 1; color: var(--cyan); }
.cw-input-wrap .cw-input { padding-left: 30px; }

.cw-input {
  width: 100%; height: 54px; padding: 0 18px;
  border-radius: var(--radius-sm);
  border: 1px solid rgba(255,255,255,.1);
  background: rgba(0,0,0,.22);
  font-family: var(--ff-mono); color: var(--ink);
  font-size: .92rem; font-weight: 500;
  outline: none; transition: all .28s cubic-bezier(.4,0,.2,1);
  -webkit-appearance: none; appearance: none;
}
.cw-input::placeholder {
  color: rgba(255,255,255,.2); font-family: var(--ff-body); font-weight: 400;
}
.cw-input:hover {
  border-color: rgba(255,255,255,.22);
  background: rgba(0,0,0,.3);
}
.cw-input:focus {
  border-color: var(--cyan);
  background: rgba(34,211,238,.04);
  box-shadow: 0 0 0 3px rgba(34,211,238,.12), inset 0 0 0 1px var(--cyan);
  transform: translateY(-2px);
}
.cw-input.cw-invalid {
  border-color: var(--red);
  box-shadow: 0 0 0 3px rgba(248,113,113,.15);
  animation: cwShake .4s cubic-bezier(.36,.07,.19,.97) both;
}

/* ── 11. ACTION BUTTONS ──────────────────────────────── */
.cw-actions {
  display: flex; gap: 14px; margin-top: 28px;
  position: relative; z-index: 1;
}

/* Primary – iridescent gradient */
.btn-primary {
  flex: 1; height: 54px; border-radius: var(--radius-sm);
  border: none; cursor: pointer;
  font-family: var(--ff-head); font-weight: 800; font-size: .92rem;
  letter-spacing: 1.2px; text-transform: uppercase; color: #030e18;
  background: linear-gradient(135deg, var(--cyan) 0%, var(--cyan-l) 30%, var(--violet) 70%, var(--cyan) 100%);
  background-size: 200% 100%;
  box-shadow: var(--shadow-btn);
  transition: all .3s cubic-bezier(.4,0,.2,1);
  position: relative; overflow: hidden;
}
/* Shine sweep */
.btn-primary::before {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,.35) 50%, transparent 65%);
  transform: translateX(-200%); transition: transform .5s ease;
}
.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 38px rgba(34,211,238,.45);
  background-position: 100% 0;
}
.btn-primary:hover::before { transform: translateX(200%); }
.btn-primary:active { transform: translateY(1px) scale(.98); }

/* Ghost */
.btn-ghost {
  width: 140px; height: 54px; border-radius: var(--radius-sm);
  border: 1px solid rgba(255,255,255,.18); background: transparent;
  font-family: var(--ff-head); font-weight: 700; font-size: .88rem; letter-spacing: .3px;
  color: var(--ink); cursor: pointer;
  transition: all .25s cubic-bezier(.4,0,.2,1);
}
.btn-ghost:hover {
  background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.35);
  transform: translateY(-2px);
}
.btn-ghost:active { transform: scale(.97); }

/* Ripple (injected by JS) */
.btn-ripple {
  position: absolute; border-radius: 50%;
  background: rgba(255,255,255,.22); pointer-events: none;
  transform: scale(0); animation: rippleOut .55s ease-out forwards;
}

/* ── 12. RESULTS PANEL ───────────────────────────────── */
.cw-result {
  margin-top: 28px; border-radius: var(--radius-sm);
  border: 1px solid rgba(34,211,238,.18);
  background: linear-gradient(175deg, rgba(0,0,0,.45), rgba(0,0,0,.6));
  position: relative; overflow: hidden; z-index: 1;
  display: none; opacity: 0; transform: translateY(14px);
  transition: all .35s ease;
}
.cw-result.show {
  display: block; opacity: 1; transform: translateY(0);
  animation: resEnter .42s cubic-bezier(.34,1.56,.64,1) both,
             resultGlow 3s 1s infinite alternate;
}
/* Gold accent line along top */
.cw-result.show::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; z-index: 2;
  background: linear-gradient(90deg, transparent, var(--cyan), var(--violet), transparent);
}

/* Result header */
.res-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 13px 20px;
  background: rgba(0,0,0,.32); border-bottom: 1px solid rgba(255,255,255,.05);
}
.res-title {
  display: flex; align-items: center; gap: 9px;
  font-family: var(--ff-head); font-size: .65rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 2px; color: var(--muted2);
}
.res-title::before {
  content: ''; width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
  background: var(--cyan); box-shadow: 0 0 10px var(--cyan);
  animation: dotPulse 1.8s infinite;
}

/* Copy button */
.res-copy-btn {
  display: inline-flex; align-items: center; gap: 6px;
  font-family: var(--ff-head); font-size: .68rem; font-weight: 700; letter-spacing: .5px;
  background: rgba(34,211,238,.07); border: 1px solid rgba(34,211,238,.2);
  color: var(--cyan); border-radius: 8px; padding: 5px 13px;
  cursor: pointer; transition: all .2s; user-select: none;
}
.res-copy-btn:hover { background: rgba(34,211,238,.18); border-color: rgba(34,211,238,.45); }
.res-copy-btn.copied { background: rgba(52,211,153,.12); border-color: rgba(52,211,153,.35); color: var(--green); }

/* Metric grid */
.res-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1px; background: rgba(255,255,255,.04);
}

/* Individual metric card */
.res-card {
  padding: 20px 22px; background: rgba(4,8,20,.86);
  position: relative; overflow: hidden;
  animation: resCardIn .38s ease both;
  transition: background .25s;
}
.res-card:hover { background: rgba(8,14,30,.95); }
.res-card:nth-child(1){animation-delay:.04s}
.res-card:nth-child(2){animation-delay:.09s}
.res-card:nth-child(3){animation-delay:.14s}
.res-card:nth-child(4){animation-delay:.19s}
.res-card:nth-child(5){animation-delay:.24s}

/* Top micro-line per card */
.res-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, var(--cyan), var(--violet)); opacity: .4;
}

.res-card-label {
  display: block; font-family: var(--ff-head); font-size: .6rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1.8px; color: var(--muted2); margin-bottom: 10px;
}

/* Result value (numbers) */
.cw-result .res-card strong {
  display: block !important; font-family: var(--ff-mono) !important;
  font-size: 1.7rem !important; font-weight: 700 !important;
  color: var(--cyan) !important; letter-spacing: -.3px !important;
  text-shadow: 0 0 18px rgba(34,211,238,.3) !important;
  line-height: 1.1 !important; margin: 0 !important;
}

/* Semantic variants */
.res-card.pos::before { background: var(--green); opacity:.4; }
.res-card.pos strong { color: var(--green) !important; text-shadow: 0 0 18px rgba(52,211,153,.3) !important; }
.res-card.neg::before { background: var(--red); opacity:.35; }
.res-card.neg strong { color: var(--red) !important; text-shadow: 0 0 18px rgba(248,113,113,.3) !important; }
.res-card.gold::before { background: linear-gradient(90deg,var(--gold),var(--gold-l)); opacity:.5; }
.res-card.gold strong { color: var(--gold) !important; text-shadow: 0 0 18px rgba(212,175,55,.3) !important; }

/* Sub text */
.cw-sub {
  display: block; margin-top: 8px;
  font-family: var(--ff-body); font-size: .8rem; color: var(--muted); line-height: 1.6;
}

/* Legacy direct strong (non-card) */
.cw-result > strong {
  display: block; font-family: var(--ff-mono) !important;
  font-size: 2.2rem; color: var(--cyan);
  letter-spacing: .5px; text-shadow: 0 0 24px rgba(34,211,238,.3);
  margin-top: 5px;
}

/* ── 13. EXTRAS (injected by JS) ─────────────────────── */

/* Enter key hint */
.enter-hint {
  text-align: center; margin-top: 13px; position: relative; z-index: 1;
  font-family: var(--ff-body); font-size: .72rem; color: var(--muted);
}
.enter-hint kbd {
  display: inline-flex; align-items: center;
  background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.15);
  border-radius: 6px; padding: 2px 8px; margin: 0 2px;
  font-family: var(--ff-head); font-size: .65rem; font-weight: 700; color: var(--cyan);
}

/* Footer note */
.calc-footer-note {
  text-align: center; padding: 28px 5%;
  font-family: var(--ff-body); font-size: .8rem; color: rgba(244,247,255,.3);
  letter-spacing: .3px;
}
.calc-footer-note i { color: var(--cyan); margin-right: 6px; opacity: .7; }

/* ── 14. ANIMATIONS ──────────────────────────────────── */
@keyframes resultGlow {
  from { box-shadow: 0 0 0 rgba(34,211,238,0); }
  to   { box-shadow: 0 0 28px rgba(34,211,238,.08); }
}

/* ── 15. RESPONSIVE ──────────────────────────────────── */
@media (max-width: 900px) {
  :root { --header-offset: 70px; }
  .calc-workspace { padding: 32px 24px; }
  .cw-grid { grid-template-columns: 1fr; gap: 16px; }
  .cw-title { font-size: 1.9rem; }
  .calc-hero { padding: 60px 5% 36px; }
  .hero-stats { width: 100%; border-radius: 14px; }
  .hero-stat { padding: 16px 24px; flex: 1; min-width: 120px; }
  .hs-num { font-size: 1.6rem; }
}
@media (max-width: 640px) {
  .calc-hero h1 { font-size: 2.2rem; }
  .calc-workspace { padding: 24px 18px; }
  .res-grid { grid-template-columns: 1fr 1fr; }
  .btn-ghost { width: 100px; }
  .hero-tags { display: none; }
}
@media (prefers-reduced-motion: reduce) {
  * { animation-duration: .01ms !important; transition-duration: .01ms !important; }
}
</style>

<!-- ═══════════════════════════════════════════════════
     ANIMATED BACKGROUND LAYER
     ═══════════════════════════════════════════════════ -->
<div class="calc-bg-fx">
  <canvas id="cw-particles"></canvas>
  <div id="cw-glow"></div>
</div>

<!-- ═══════════════════════════════════════════════════
     PAGE SHELL
     ═══════════════════════════════════════════════════ -->
<div class="calc-shell" id="calc-shell">

  <!-- ── HERO ───────────────────────────────────────── -->
  <div class="calc-hero">
    <div class="hero-badge"><span class="hb-dot"></span> WiseInvest &bull; Premium Financial Tools</div>

    <h1 data-text="Investment Tools">Investment Tools</h1>

    <p>Live computation &nbsp;·&nbsp; Zero server round-trips &nbsp;·&nbsp; 100% private</p>

    <div class="hero-tags">
      <span class="hero-tag tg-c"><i class="fas fa-bolt"></i> Instant Results</span>
      <span class="hero-tag tg-g"><i class="fas fa-lock"></i> Privacy First</span>
      <span class="hero-tag tg-v"><i class="fas fa-infinity"></i> Unlimited Free Uses</span>
      <span class="hero-tag tg-a"><i class="fas fa-keyboard"></i> Keyboard Friendly</span>
    </div>

    <div class="hero-stats">
      <div class="hero-stat">
        <span class="hs-num">11</span>
        <span class="hs-label">Calculators</span>
      </div>
      <div class="hero-stat">
        <span class="hs-num">∞</span>
        <span class="hs-label">Free Uses</span>
      </div>
      <div class="hero-stat">
        <span class="hs-num">&lt;1ms</span>
        <span class="hs-label">Compute</span>
      </div>
      <div class="hero-stat">
        <span class="hs-num">100%</span>
        <span class="hs-label">Private</span>
      </div>
    </div>
  </div>

  <!-- ── STICKY TABS ────────────────────────────────── -->
  <div class="calc-nav-wrapper">
    <div class="calc-tabs">
      <button type="button" class="calc-tab-btn active" onclick="switchCalc('freedom',this)">
        <i class="fas fa-flag"></i>Fin. Freedom
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('hist',this)">
        <i class="fas fa-chart-line"></i>Historical
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('future',this)">
        <i class="fas fa-rocket"></i>Future Wealth
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('roi',this)">
        <i class="fas fa-percentage"></i>ROI
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('pl',this)">
        <i class="fas fa-balance-scale"></i>Profit/Loss
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('be',this)">
        <i class="fas fa-crosshairs"></i>Break-Even
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('ci',this)">
        <i class="fas fa-infinity"></i>Compound
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('sip',this)">
        <i class="fas fa-piggy-bank"></i>SIP
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('rr',this)">
        <i class="fas fa-shield-alt"></i>Risk Ratio
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('cagr',this)">
        <i class="fas fa-chart-bar"></i>CAGR
      </button>
      <button type="button" class="calc-tab-btn" onclick="switchCalc('div',this)">
        <i class="fas fa-gem"></i>Dividend
      </button>
    </div>
  </div>

  <!-- ── CALCULATOR WORKSPACES ─────────────────────── -->
  <section class="calc-dashboard">

    <!-- 01 · Financial Freedom -->
    <div class="calc-workspace active" id="calc-freedom">
      <div class="cw-header">
        <h2 class="cw-title">Financial Freedom</h2>
        <p class="cw-desc">Calculate your freedom number using monthly expenses and safe withdrawal rate.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Monthly Expenses ($)</label>
          <input class="cw-input" type="number" step="any" id="ff-expenses" placeholder="e.g., 2000">
        </div>
        <div>
          <label class="cw-label">Safe Withdrawal Rate (%)</label>
          <input class="cw-input" type="number" step="any" id="ff-swr" placeholder="e.g., 4">
        </div>
        <div>
          <label class="cw-label">Current Invested Assets ($) (opt)</label>
          <input class="cw-input" type="number" step="any" id="ff-assets" placeholder="e.g., 50000">
        </div>
        <div>
          <label class="cw-label">Expected Annual Return (%) (opt)</label>
          <input class="cw-input" type="number" step="any" id="ff-return" placeholder="e.g., 8">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="freedom">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="ff">Reset</button>
      </div>
      <div class="cw-result" id="ff-result"></div>
    </div>

    <!-- 02 · Historical Growth -->
    <div class="calc-workspace" id="calc-hist">
      <div class="cw-header">
        <h2 class="cw-title">Historical Growth</h2>
        <p class="cw-desc">See how an initial investment compounds over any time period.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Initial Investment ($)</label>
          <input class="cw-input" type="number" step="any" id="hist-initial" placeholder="e.g., 1000">
        </div>
        <div>
          <label class="cw-label">Annual Rate (%)</label>
          <input class="cw-input" type="number" step="any" id="hist-rate" placeholder="e.g., 10">
        </div>
        <div>
          <label class="cw-label">Years</label>
          <input class="cw-input" type="number" step="1" id="hist-years" placeholder="e.g., 10">
        </div>
        <div>
          <label class="cw-label">Compounds / Year (optional)</label>
          <input class="cw-input" type="number" step="1" id="hist-comp" placeholder="e.g., 1">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="historical">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="hist">Reset</button>
      </div>
      <div class="cw-result" id="hist-result"></div>
    </div>

    <!-- 03 · Future Wealth -->
    <div class="calc-workspace" id="calc-future">
      <div class="cw-header">
        <h2 class="cw-title">Future Wealth</h2>
        <p class="cw-desc">Project your portfolio with initial investment plus monthly contributions.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Initial Investment ($)</label>
          <input class="cw-input" type="number" step="any" id="fut-initial" placeholder="e.g., 5000">
        </div>
        <div>
          <label class="cw-label">Monthly Contribution ($)</label>
          <input class="cw-input" type="number" step="any" id="fut-monthly" placeholder="e.g., 200">
        </div>
        <div>
          <label class="cw-label">Annual Rate (%)</label>
          <input class="cw-input" type="number" step="any" id="fut-rate" placeholder="e.g., 8">
        </div>
        <div>
          <label class="cw-label">Years</label>
          <input class="cw-input" type="number" step="1" id="fut-years" placeholder="e.g., 15">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="future">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="fut">Reset</button>
      </div>
      <div class="cw-result" id="fut-result"></div>
    </div>

    <!-- 04 · ROI -->
    <div class="calc-workspace" id="calc-roi">
      <div class="cw-header">
        <h2 class="cw-title">Return on Investment</h2>
        <p class="cw-desc">Measure performance with net gain, ROI%, and multiplier from initial and final value.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Initial Value ($)</label>
          <input class="cw-input" type="number" step="any" id="roi-initial" placeholder="e.g., 1000">
        </div>
        <div>
          <label class="cw-label">Current Value ($)</label>
          <input class="cw-input" type="number" step="any" id="roi-current" placeholder="e.g., 1250">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="roi">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="roi">Reset</button>
      </div>
      <div class="cw-result" id="roi-result"></div>
    </div>

    <!-- 05 · Profit / Loss -->
    <div class="calc-workspace" id="calc-pl">
      <div class="cw-header">
        <h2 class="cw-title">Profit / Loss</h2>
        <p class="cw-desc">Net P&amp;L breakdown including buy price, sell price, quantity and fees.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Buy Price ($)</label>
          <input class="cw-input" type="number" step="any" id="pl-buy" placeholder="e.g., 10">
        </div>
        <div>
          <label class="cw-label">Sell Price ($)</label>
          <input class="cw-input" type="number" step="any" id="pl-sell" placeholder="e.g., 12">
        </div>
        <div>
          <label class="cw-label">Quantity</label>
          <input class="cw-input" type="number" step="any" id="pl-qty" placeholder="e.g., 100">
        </div>
        <div>
          <label class="cw-label">Fees ($)</label>
          <input class="cw-input" type="number" step="any" id="pl-fees" placeholder="e.g., 5">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="pl">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="pl">Reset</button>
      </div>
      <div class="cw-result" id="pl-result"></div>
    </div>

    <!-- 06 · Break-Even -->
    <div class="calc-workspace" id="calc-be">
      <div class="cw-header">
        <h2 class="cw-title">Break-Even</h2>
        <p class="cw-desc">Find your exact break-even price factoring in fees and additional costs.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Buy Price ($)</label>
          <input class="cw-input" type="number" step="any" id="be-buy" placeholder="e.g., 10">
        </div>
        <div>
          <label class="cw-label">Quantity</label>
          <input class="cw-input" type="number" step="any" id="be-qty" placeholder="e.g., 100">
        </div>
        <div>
          <label class="cw-label">Fees ($)</label>
          <input class="cw-input" type="number" step="any" id="be-fees" placeholder="e.g., 5">
        </div>
        <div>
          <label class="cw-label">Extra Cost ($) (optional)</label>
          <input class="cw-input" type="number" step="any" id="be-extra" placeholder="e.g., 0">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="be">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="be">Reset</button>
      </div>
      <div class="cw-result" id="be-result"></div>
    </div>

    <!-- 07 · Compound Interest -->
    <div class="calc-workspace" id="calc-ci">
      <div class="cw-header">
        <h2 class="cw-title">Compound Interest</h2>
        <p class="cw-desc">Monthly compounding with initial lump sum and regular contributions.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Initial ($)</label>
          <input class="cw-input" type="number" step="any" id="ci-initial" placeholder="e.g., 1000">
        </div>
        <div>
          <label class="cw-label">Monthly ($)</label>
          <input class="cw-input" type="number" step="any" id="ci-monthly" placeholder="e.g., 100">
        </div>
        <div>
          <label class="cw-label">Annual Rate (%)</label>
          <input class="cw-input" type="number" step="any" id="ci-rate" placeholder="e.g., 8">
        </div>
        <div>
          <label class="cw-label">Years</label>
          <input class="cw-input" type="number" step="1" id="ci-years" placeholder="e.g., 10">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="ci">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="ci">Reset</button>
      </div>
      <div class="cw-result" id="ci-result"></div>
    </div>

    <!-- 08 · SIP Calculator -->
    <div class="calc-workspace" id="calc-sip">
      <div class="cw-header">
        <h2 class="cw-title">SIP Calculator</h2>
        <p class="cw-desc">Systematic investment plan maturity value with optional annual step-up.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Monthly SIP Amount ($)</label>
          <input class="cw-input" type="number" step="any" id="sip-amount" placeholder="e.g., 200">
        </div>
        <div>
          <label class="cw-label">Annual Rate (%)</label>
          <input class="cw-input" type="number" step="any" id="sip-rate" placeholder="e.g., 10">
        </div>
        <div>
          <label class="cw-label">Years</label>
          <input class="cw-input" type="number" step="1" id="sip-years" placeholder="e.g., 15">
        </div>
        <div>
          <label class="cw-label">Step-Up %/Year (optional)</label>
          <input class="cw-input" type="number" step="any" id="sip-step" placeholder="e.g., 0">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="sip">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="sip">Reset</button>
      </div>
      <div class="cw-result" id="sip-result"></div>
    </div>

    <!-- 09 · Risk / Reward -->
    <div class="calc-workspace" id="calc-rr">
      <div class="cw-header">
        <h2 class="cw-title">Risk / Reward</h2>
        <p class="cw-desc">Evaluate trade quality by comparing potential reward vs maximum risk.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Entry</label>
          <input class="cw-input" type="number" step="any" id="rr-entry" placeholder="e.g., 100">
        </div>
        <div>
          <label class="cw-label">Stop Loss</label>
          <input class="cw-input" type="number" step="any" id="rr-stop" placeholder="e.g., 95">
        </div>
        <div>
          <label class="cw-label">Target</label>
          <input class="cw-input" type="number" step="any" id="rr-target" placeholder="e.g., 115">
        </div>
        <div>
          <label class="cw-label">Position Size (optional)</label>
          <input class="cw-input" type="number" step="any" id="rr-size" placeholder="e.g., 10">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="rr">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="rr">Reset</button>
      </div>
      <div class="cw-result" id="rr-result"></div>
    </div>

    <!-- 10 · CAGR -->
    <div class="calc-workspace" id="calc-cagr">
      <div class="cw-header">
        <h2 class="cw-title">CAGR</h2>
        <p class="cw-desc">Compound annual growth rate — the smoothed annual return over any period.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Start Value ($)</label>
          <input class="cw-input" type="number" step="any" id="cagr-start" placeholder="e.g., 1000">
        </div>
        <div>
          <label class="cw-label">End Value ($)</label>
          <input class="cw-input" type="number" step="any" id="cagr-end" placeholder="e.g., 2000">
        </div>
        <div>
          <label class="cw-label">Years</label>
          <input class="cw-input" type="number" step="1" id="cagr-years" placeholder="e.g., 5">
        </div>
        <div>
          <label class="cw-label">Show Total Return? (1=yes, 0=no)</label>
          <input class="cw-input" type="number" step="1" id="cagr-show" placeholder="e.g., 1">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="cagr">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="cagr">Reset</button>
      </div>
      <div class="cw-result" id="cagr-result"></div>
    </div>

    <!-- 11 · Dividend Yield -->
    <div class="calc-workspace" id="calc-div">
      <div class="cw-header">
        <h2 class="cw-title">Dividend Yield</h2>
        <p class="cw-desc">Calculate annual dividend yield, gross income, and after-tax return.</p>
      </div>
      <div class="cw-grid">
        <div>
          <label class="cw-label">Annual Dividend / Share ($)</label>
          <input class="cw-input" type="number" step="any" id="div-amount" placeholder="e.g., 2">
        </div>
        <div>
          <label class="cw-label">Share Price ($)</label>
          <input class="cw-input" type="number" step="any" id="div-price" placeholder="e.g., 50">
        </div>
        <div>
          <label class="cw-label">Shares (optional)</label>
          <input class="cw-input" type="number" step="any" id="div-shares" placeholder="e.g., 100">
        </div>
        <div>
          <label class="cw-label">Tax Rate % (optional)</label>
          <input class="cw-input" type="number" step="any" id="div-tax" placeholder="e.g., 10">
        </div>
      </div>
      <div class="cw-actions">
        <button class="btn-primary" type="button" data-calc="div">
          <i class="fas fa-calculator" style="margin-right:8px;font-size:.85rem"></i>Calculate
        </button>
        <button class="btn-ghost" type="button" data-reset="div">Reset</button>
      </div>
      <div class="cw-result" id="div-result"></div>
    </div>

  </section>
</div><!-- /.calc-shell -->

<script>
/* ==========================================================
   JS — FUTURISTIC UPGRADE (ALL LOGIC PRESERVED)
   ========================================================== */

// ── Tab Switching ──────────────────────────────────────
function switchCalc(id, btn) {
  document.querySelectorAll('.calc-workspace').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.calc-tab-btn').forEach(el => el.classList.remove('active'));
  const target = document.getElementById('calc-' + id);
  if (target) {
    setTimeout(() => {
      target.classList.add('active');
      const sl = target.querySelector('.scan-line');
      if (sl) { sl.style.animation = 'none'; void sl.offsetWidth; sl.style.animation = ''; }
    }, 10);
  }
  if (btn) btn.classList.add('active');
  try { history.replaceState(null, "", "#" + id); } catch(e) {}
  if (target && window.innerWidth < 900) {
    const offset = document.querySelector('.calc-nav-wrapper').offsetHeight + 20;
    window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - offset, behavior: "smooth" });
  }
}

// ── Helpers ────────────────────────────────────────────
const moneyFmt = new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 });
const intFmt   = new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 });
function $(id) { return document.getElementById(id); }
function num(id) { const el=$(id); if(!el)return NaN; const v=parseFloat(el.value); return Number.isFinite(v)?v:NaN; }

// ── Core Helpers ────────────────────────────────────────
function markInvalid(ids) {
  ids.forEach(id => { const el=$(id); if(el)el.classList.add("cw-invalid"); });
  setTimeout(()=>ids.forEach(id=>{const el=$(id);if(el)el.classList.remove("cw-invalid");}),650);
}
function clearResult(id) { const b=$(id); if(!b)return; b.classList.remove("show"); b.innerHTML=""; }
function resetByPrefix(prefix) {
  document.querySelectorAll(`[id^="${prefix}-"]`).forEach(el=>{
    if(el.tagName==="INPUT")el.value=""; el.classList.remove("cw-invalid");
  });
}
function easeOutCubic(t) { return 1-Math.pow(1-t,3); }
function formatAnimated(type, value) {
  if(!Number.isFinite(value))return "—";
  if(type==="currency")return "$"+moneyFmt.format(value);
  if(type==="percent")return value.toFixed(2)+"%";
  if(type==="number")return moneyFmt.format(value);
  return String(value);
}
function animateCounters(container) {
  if(!container)return;
  container.querySelectorAll("strong[data-animate][data-value]").forEach(node=>{
    const type=node.getAttribute("data-animate")||"number";
    const end=parseFloat(node.getAttribute("data-value"));
    if(!Number.isFinite(end)){node.textContent="—";return;}
    const dur=700, t0=performance.now();
    function frame(now){
      const t=Math.min(1,(now-t0)/dur);
      node.textContent=formatAnimated(type, end*easeOutCubic(t));
      if(t<1)requestAnimationFrame(frame); else node.textContent=formatAnimated(type,end);
    }
    requestAnimationFrame(frame);
  });
}
function showResultBox(id,html){const b=$(id);if(!b)return;b.innerHTML=html;b.classList.add("show");animateCounters(b);}
function debounce(fn,wait=300){let t;return(...args)=>{clearTimeout(t);t=setTimeout(()=>fn(...args),wait);};}

// ── Result Cards ────────────────────────────────────────
function buildCard(label, animType, rawValue, extraClass='') {
  const cls=['res-card',extraClass].filter(Boolean).join(' ');
  if(animType)
    return `<div class="${cls}"><div class="res-card-label">${label}</div><strong data-animate="${animType}" data-value="${rawValue}"></strong></div>`;
  return `<div class="${cls}"><div class="res-card-label">${label}</div><strong>${rawValue}</strong></div>`;
}
function showCards(id, title, cardsHtml) {
  showResultBox(id, `
    <div class="res-header">
      <span class="res-title">${title}</span>
      <button class="res-copy-btn"><i class="fas fa-copy"></i> Copy</button>
    </div>
    <div class="res-grid">${cardsHtml}</div>
  `);
}

// ── Calculator Logic ─────────────────────────────────────
function calcFreedom() {
  const monthly=num("ff-expenses"),swr=num("ff-swr"),assets=num("ff-assets"),ret=num("ff-return");
  if(!Number.isFinite(monthly)||!Number.isFinite(swr)||monthly<=0||swr<=0){
    markInvalid(["ff-expenses","ff-swr"]);
    return showCards("ff-result","Financial Freedom",buildCard("Invalid Input","","Expenses & SWR must be &gt; 0","neg"));
  }
  const freedom=(monthly*12)/(swr/100);
  let c=buildCard("Freedom Number","currency",freedom,"gold");
  if(Number.isFinite(assets)&&assets>0){
    const gap=Math.max(0,freedom-assets);
    c+=buildCard("Current Assets","currency",assets);
    c+=buildCard("Gap to Freedom","currency",gap,gap>0?"neg":"pos");
    if(Number.isFinite(ret)&&ret>0&&gap>0){
      const yrs=Math.log(freedom/assets)/Math.log(1+(ret/100));
      if(Number.isFinite(yrs)&&yrs>0)c+=buildCard("Years to Freedom","number",+yrs.toFixed(1));
    }
  }
  showCards("ff-result","Financial Freedom",c);
}

function calcHistorical() {
  const p=num("hist-initial"),rate=num("hist-rate"),y=num("hist-years");
  let m=num("hist-comp");
  if(![p,rate,y].every(Number.isFinite)||p<0||rate<0||y<0){
    markInvalid(["hist-initial","hist-rate","hist-years"]);
    return showCards("hist-result","Historical Growth",buildCard("Error","","Fill all required fields.","neg"));
  }
  if(!Number.isFinite(m)||m<=0)m=1;
  const fv=p*Math.pow(1+((rate/100)/m),m*y);
  let c=buildCard("Final Value","currency",fv,"gold");
  c+=buildCard("Initial","currency",p);
  c+=buildCard("Total Gain","currency",fv-p,(fv-p)>=0?"pos":"neg");
  c+=buildCard("Multiplier","number",+(fv/p).toFixed(2));
  showCards("hist-result","Historical Growth",c);
}

function calcFuture() {
  const p=num("fut-initial"),m=num("fut-monthly"),rate=num("fut-rate"),years=num("fut-years");
  if(![p,m,rate,years].every(Number.isFinite)||years<0){
    markInvalid(["fut-initial","fut-monthly","fut-rate","fut-years"]);
    return showCards("fut-result","Future Wealth",buildCard("Error","","Fill all required fields.","neg"));
  }
  const r=(rate/100)/12,n=Math.floor(years*12);
  let fv=p; for(let i=1;i<=n;i++)fv=fv*(1+r)+m;
  const totalIn=p+(m*n);
  let c=buildCard("Future Value","currency",fv,"gold");
  c+=buildCard("Total Contributed","currency",totalIn);
  c+=buildCard("Interest Earned","currency",fv-totalIn,"pos");
  showCards("fut-result","Future Wealth",c);
}

function calcROI() {
  const initial=num("roi-initial"),current=num("roi-current");
  if(![initial,current].every(Number.isFinite)||initial<=0){
    markInvalid(["roi-initial","roi-current"]);
    return showCards("roi-result","Return on Investment",buildCard("Error","","Invalid input.","neg"));
  }
  const roi=((current-initial)/initial)*100;
  const gain=current-initial;
  let c=buildCard("ROI","percent",roi,roi>=0?"pos":"neg");
  c+=buildCard("Net Gain / Loss","currency",gain,gain>=0?"pos":"neg");
  c+=buildCard("Multiplier","number",+(current/initial).toFixed(3));
  showCards("roi-result","Return on Investment",c);
}

function calcPL() {
  const buy=num("pl-buy"),sell=num("pl-sell"),qty=num("pl-qty"),fees=num("pl-fees");
  if(![buy,sell,qty,fees].every(Number.isFinite)){
    markInvalid(["pl-buy","pl-sell","pl-qty","pl-fees"]);
    return showCards("pl-result","Profit / Loss",buildCard("Error","","Fill all required fields.","neg"));
  }
  const gross=(sell-buy)*qty,net=gross-fees,pct=((sell-buy)/buy)*100;
  let c=buildCard(net>=0?"Net Profit":"Net Loss","currency",net,net>=0?"pos":"neg");
  c+=buildCard("Gross P/L","currency",gross,gross>=0?"pos":"neg");
  c+=buildCard("Return %","percent",pct,pct>=0?"pos":"neg");
  c+=buildCard("Fees Paid","currency",fees);
  showCards("pl-result","Profit / Loss",c);
}

function calcBE() {
  const buy=num("be-buy"),qty=num("be-qty"),fees=num("be-fees"),extra=num("be-extra");
  const ex=Number.isFinite(extra)?extra:0;
  if(![buy,qty,fees].every(Number.isFinite)||qty<=0){
    markInvalid(["be-buy","be-qty","be-fees"]);
    return showCards("be-result","Break-Even",buildCard("Error","","Invalid input.","neg"));
  }
  const be=((buy*qty)+fees+ex)/qty;
  let c=buildCard("Break-Even Price","currency",be,"gold");
  c+=buildCard("Buy Price","currency",buy);
  c+=buildCard("% Above Buy","percent",+((be-buy)/buy*100).toFixed(2));
  c+=buildCard("Total Cost","currency",(buy*qty)+fees+ex);
  showCards("be-result","Break-Even",c);
}

function calcCI() {
  const initial=num("ci-initial"),monthly=num("ci-monthly"),rate=num("ci-rate"),years=num("ci-years");
  if(![initial,monthly,rate,years].every(Number.isFinite)||years<0){
    markInvalid(["ci-initial","ci-monthly","ci-rate","ci-years"]);
    return showCards("ci-result","Compound Interest",buildCard("Error","","Fill all required fields.","neg"));
  }
  const r=(rate/100)/12,n=Math.floor(years*12);
  let fv=initial; for(let i=1;i<=n;i++)fv=fv*(1+r)+monthly;
  const totalIn=initial+(monthly*n);
  let c=buildCard("Final Value","currency",fv,"gold");
  c+=buildCard("Total Deposited","currency",totalIn);
  c+=buildCard("Interest Earned","currency",fv-totalIn,"pos");
  showCards("ci-result","Compound Interest",c);
}

function calcSIP() {
  const amount=num("sip-amount"),rate=num("sip-rate"),years=num("sip-years"),step=num("sip-step");
  if(![amount,rate,years].every(Number.isFinite)||years<0){
    markInvalid(["sip-amount","sip-rate","sip-years"]);
    return showCards("sip-result","SIP Calculator",buildCard("Error","","Fill all required fields.","neg"));
  }
  const r=(rate/100)/12,n=Math.floor(years*12),stepPct=(Number.isFinite(step)&&step>0)?(step/100):0;
  let fv=0,monthly=amount;
  for(let i=1;i<=n;i++){fv=fv*(1+r)+monthly;if(stepPct>0&&i%12===0)monthly*=(1+stepPct);}
  const totalIn=amount*n;
  let c=buildCard("Maturity Value","currency",fv,"gold");
  c+=buildCard("Amount Invested","currency",totalIn);
  c+=buildCard("Wealth Gained","currency",fv-totalIn,"pos");
  if(stepPct>0)c+=buildCard("Step-Up Applied","percent",step);
  showCards("sip-result","SIP Calculator",c);
}

function calcRR() {
  const entry=num("rr-entry"),stop=num("rr-stop"),target=num("rr-target"),size=num("rr-size");
  if(![entry,stop,target].every(Number.isFinite)){
    markInvalid(["rr-entry","rr-stop","rr-target"]);
    return showCards("rr-result","Risk / Reward",buildCard("Error","","Fill all required fields.","neg"));
  }
  const risk=Math.abs(entry-stop),reward=Math.abs(target-entry);
  if(risk===0){markInvalid(["rr-entry","rr-stop"]);return showCards("rr-result","Risk / Reward",buildCard("Error","","Entry and Stop cannot be equal.","neg"));}
  const ratio=reward/risk;
  const rrCls=ratio>=2?"pos":ratio<1?"neg":"";
  let c=buildCard("R:R Ratio","number",+ratio.toFixed(2),rrCls);
  c+=buildCard("Risk per Unit","currency",risk,"neg");
  c+=buildCard("Reward per Unit","currency",reward,"pos");
  if(Number.isFinite(size)&&size>0){
    c+=buildCard("Max Risk ($)","currency",risk*size,"neg");
    c+=buildCard("Max Reward ($)","currency",reward*size,"pos");
  }
  showCards("rr-result","Risk / Reward",c);
}

function calcCAGR() {
  const start=num("cagr-start"),end=num("cagr-end"),years=num("cagr-years"),showTotal=num("cagr-show");
  if(![start,end,years].every(Number.isFinite)||start<=0||years<=0){
    markInvalid(["cagr-start","cagr-end","cagr-years"]);
    return showCards("cagr-result","CAGR",buildCard("Error","","Invalid input.","neg"));
  }
  const cagr=(Math.pow(end/start,1/years)-1)*100;
  let c=buildCard("CAGR","percent",cagr,cagr>=0?"pos":"neg");
  c+=buildCard("Start Value","currency",start);
  c+=buildCard("End Value","currency",end);
  if(showTotal===1)c+=buildCard("Total Return","percent",((end-start)/start)*100,((end-start)/start)>=0?"pos":"neg");
  showCards("cagr-result","CAGR",c);
}

function calcDiv() {
  const amount=num("div-amount"),price=num("div-price"),shares=num("div-shares"),tax=num("div-tax");
  if(![amount,price].every(Number.isFinite)||price<=0){
    markInvalid(["div-amount","div-price"]);
    return showCards("div-result","Dividend Yield",buildCard("Error","","Invalid input.","neg"));
  }
  const yieldPct=(amount/price)*100;
  let c=buildCard("Dividend Yield","percent",yieldPct,"gold");
  c+=buildCard("Annual Div/Share","currency",amount);
  if(Number.isFinite(shares)&&shares>0){
    const gross=amount*shares;
    c+=buildCard("Gross Annual Income","currency",gross,"pos");
    if(Number.isFinite(tax)&&tax>0)c+=buildCard("Net After Tax","currency",gross*(1-tax/100),"pos");
  }
  showCards("div-result","Dividend Yield",c);
}

// ── Event Binding ───────────────────────────────────────
const calcMap={freedom:calcFreedom,historical:calcHistorical,future:calcFuture,roi:calcROI,pl:calcPL,be:calcBE,ci:calcCI,sip:calcSIP,rr:calcRR,cagr:calcCAGR,div:calcDiv};
const liveDebounced={};
Object.keys(calcMap).forEach(k=>liveDebounced[k]=debounce(calcMap[k],300));

function wireLive(workspaceId,calcKey){
  const ws=document.getElementById(workspaceId);if(!ws)return;
  ws.querySelectorAll("input").forEach(inp=>{
    inp.addEventListener("input",()=>liveDebounced[calcKey]());
    inp.addEventListener("change",()=>liveDebounced[calcKey]());
  });
}

function bindButtons() {
  document.querySelectorAll("[data-calc]").forEach(btn=>btn.addEventListener("click",()=>{const k=btn.getAttribute("data-calc");if(calcMap[k])calcMap[k]();}));
  document.querySelectorAll("[data-reset]").forEach(btn=>btn.addEventListener("click",()=>{
    const p=btn.getAttribute("data-reset"); resetByPrefix(p);
    const rm={ff:"ff-result",hist:"hist-result",fut:"fut-result",roi:"roi-result",pl:"pl-result",be:"be-result",ci:"ci-result",sip:"sip-result",rr:"rr-result",cagr:"cagr-result",div:"div-result"};
    if(rm[p])clearResult(rm[p]);
  }));
}

// ── Neural-Net Particles ────────────────────────────────
function initParticles() {
  const canvas=document.getElementById("cw-particles");if(!canvas)return;
  const ctx=canvas.getContext("2d");
  const pts=[]; let w=0,h=0;
  function resize(){
    w=canvas.width=window.innerWidth; h=canvas.height=window.innerHeight;
    const target=Math.min(70,Math.floor((w*h)/18000));
    while(pts.length<target)pts.push({x:Math.random()*w,y:Math.random()*h,vx:(Math.random()-0.5)*0.38,vy:(Math.random()-0.5)*0.38,r:Math.random()*1.4+0.5,hue:Math.random()<0.5?195:265});
  }
  function step(){
    ctx.clearRect(0,0,w,h);
    for(let i=0;i<pts.length;i++){
      for(let j=i+1;j<pts.length;j++){
        const dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy);
        if(d<130){ctx.beginPath();ctx.strokeStyle=`hsla(195,80%,70%,${0.13*(1-d/130)})`;ctx.lineWidth=0.5;ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y);ctx.stroke();}
      }
    }
    for(let p of pts){
      p.x+=p.vx;p.y+=p.vy;
      if(p.x<0)p.x=w;if(p.x>w)p.x=0;if(p.y<0)p.y=h;if(p.y>h)p.y=0;
      ctx.beginPath();ctx.fillStyle=`hsla(${p.hue},90%,70%,0.5)`;ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fill();
    }
    requestAnimationFrame(step);
  }
  window.addEventListener("resize",debounce(resize,200),{passive:true});
  resize();step();
}

function initMouseGlow() {
  const glow=document.getElementById("cw-glow");if(!glow)return;
  let x=window.innerWidth/2,y=window.innerHeight/2,tx=x,ty=y;
  function tick(){x+=(tx-x)*0.08;y+=(ty-y)*0.08;glow.style.transform=`translate(${x-350}px,${y-350}px)`;requestAnimationFrame(tick);}
  window.addEventListener("mousemove",e=>{tx=e.clientX;ty=e.clientY;},{passive:true});
  tick();
}

// ── Ripple Effect ───────────────────────────────────────
function initRipple() {
  document.addEventListener("click",e=>{
    const btn=e.target.closest(".btn-primary,.btn-ghost");if(!btn)return;
    const r=document.createElement("span");r.className="btn-ripple";
    const rect=btn.getBoundingClientRect(),size=Math.max(rect.width,rect.height);
    r.style.cssText=`width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
    btn.style.position='relative';btn.style.overflow='hidden';
    btn.appendChild(r);setTimeout(()=>r.remove(),600);
  });
}

// ── Enter Key to Calculate ──────────────────────────────
function initEnterKey() {
  const wsMap={'calc-freedom':'freedom','calc-hist':'historical','calc-future':'future','calc-roi':'roi','calc-pl':'pl','calc-be':'be','calc-ci':'ci','calc-sip':'sip','calc-rr':'rr','calc-cagr':'cagr','calc-div':'div'};
  document.addEventListener("keydown",e=>{
    if(e.key!=="Enter"||e.target.tagName!=="INPUT")return;
    const ws=e.target.closest(".calc-workspace");if(!ws)return;
    const key=wsMap[ws.id];if(key&&calcMap[key]){e.preventDefault();calcMap[key]();}
  });
}

// ── Copy Result ─────────────────────────────────────────
function initCopyButtons() {
  document.addEventListener("click",e=>{
    const btn=e.target.closest(".res-copy-btn");if(!btn)return;
    const box=btn.closest(".cw-result");if(!box)return;
    const lines=Array.from(box.querySelectorAll(".res-card")).map(c=>`${c.querySelector(".res-card-label")?.textContent||""}: ${c.querySelector("strong")?.textContent||""}`);
    navigator.clipboard.writeText(lines.join("\n")).then(()=>{
      btn.classList.add("copied");btn.innerHTML='<i class="fas fa-check"></i> Copied!';
      setTimeout(()=>{btn.classList.remove("copied");btn.innerHTML='<i class="fas fa-copy"></i> Copy';},2000);
    }).catch(()=>{});
  });
}

// ── Input Prefix Injection ──────────────────────────────
function initInputPrefixes() {
  document.querySelectorAll(".cw-grid > div").forEach(cell=>{
    const label=cell.querySelector(".cw-label"),input=cell.querySelector(".cw-input");
    if(!label||!input)return;
    const txt=label.textContent;
    let pfx=null;
    if(/\(\$\)/.test(txt))pfx="$";
    else if(/\(%\)|Rate|SWR|Return.*%|Step-Up|Tax Rate/.test(txt))pfx="%";
    if(!pfx)return;
    const wrap=document.createElement("div");wrap.className="cw-input-wrap";
    const span=document.createElement("span");span.className="cw-pfx";span.textContent=pfx;
    cell.insertBefore(wrap,input);wrap.appendChild(span);wrap.appendChild(input);
  });
}

// ── Workspace Icons & Scan-lines ────────────────────────
function initWorkspaceDecorations() {
  const cfg={
    'calc-freedom':['fa-flag','var(--gold)'],       'calc-hist':['fa-chart-line','var(--violet)'],
    'calc-future': ['fa-rocket','var(--cyan)'],      'calc-roi': ['fa-percentage','var(--gold)'],
    'calc-pl':     ['fa-balance-scale','var(--cyan)'],'calc-be': ['fa-crosshairs','var(--cyan)'],
    'calc-ci':     ['fa-infinity','var(--violet)'],  'calc-sip': ['fa-piggy-bank','var(--gold)'],
    'calc-rr':     ['fa-shield-alt','#f87171'],       'calc-cagr':['fa-chart-bar','var(--cyan)'],
    'calc-div':    ['fa-gem','var(--gold)'],
  };
  Object.entries(cfg).forEach(([id,[icon,color]])=>{
    const ws=document.getElementById(id);if(!ws)return;
    const h=ws.querySelector(".cw-header");if(h){const i=document.createElement("i");i.className=`fas ${icon} cw-icon`;i.style.color=color;i.style.filter=`drop-shadow(0 0 12px ${color})`;h.insertBefore(i,h.firstChild);}
    const sl=document.createElement("div");sl.className="scan-line";ws.appendChild(sl);
  });
}

// ── Enter Hints ─────────────────────────────────────────
function initEnterHints() {
  document.querySelectorAll(".cw-actions").forEach(a=>{
    const h=document.createElement("div");h.className="enter-hint";
    h.innerHTML='Press <kbd>&#8629; Enter</kbd> to calculate instantly &nbsp;·&nbsp; <kbd>Tab</kbd> to navigate fields';
    a.insertAdjacentElement("afterend",h);
  });
}

// ── Footer Note ─────────────────────────────────────────
function initFooterNote() {
  const shell=document.getElementById("calc-shell");if(!shell)return;
  const n=document.createElement("div");n.className="calc-footer-note";
  n.innerHTML='<i class="fas fa-lock"></i> All calculations run locally in your browser — no data ever leaves your device.';
  shell.appendChild(n);
}

// ── Boot ────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded",()=>{
  bindButtons();
  ["freedom","historical","future","roi","pl","be","ci","sip","rr","cagr","div"].forEach(k=>{
    const wsId={freedom:"calc-freedom",historical:"calc-hist",future:"calc-future",roi:"calc-roi",pl:"calc-pl",be:"calc-be",ci:"calc-ci",sip:"calc-sip",rr:"calc-rr",cagr:"calc-cagr",div:"calc-div"}[k];
    wireLive(wsId,k);
  });
  initParticles();
  initMouseGlow();
  initRipple();
  initEnterKey();
  initCopyButtons();
  initInputPrefixes();
  initWorkspaceDecorations();
  initEnterHints();
  initFooterNote();

  const hash=(location.hash||"").replace("#","").trim();
  if(hash){
    const btn=Array.from(document.querySelectorAll(".calc-tab-btn")).find(b=>(b.getAttribute("onclick")||"").includes("'"+hash+"'"));
    if(btn)switchCalc(hash,btn);
  }
});
</script>

<?php include 'includes/footer.php'; ?>
