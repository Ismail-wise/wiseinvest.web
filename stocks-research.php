<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit(); }
include 'includes/db.php';
include 'includes/header.php';
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

/* ═══════════════════════════════════════════════════════════
   STOCKS RESEARCH — CINEMATIC REDESIGN v3
   ═══════════════════════════════════════════════════════════ */
:root{
  --gold:#d4af37; --gold-l:#facc15; --navy:#020617;
  --card:rgba(6,12,28,.92); --brd:rgba(212,175,55,.1);
  --text:#e2e8f0; --muted:#475569;
  --green:#34d399; --red:#f87171; --amber:#fbbf24; --blue:#60a5fa;
  --ff-head:'Space Grotesk',system-ui,sans-serif;
  --ff-body:'DM Sans',system-ui,sans-serif;
  --ff-mono:'JetBrains Mono',monospace;
}
*{box-sizing:border-box}
body{
  background:var(--navy); color:var(--text);
  font-family:var(--ff-body); margin:0; overflow-x:hidden;
  -webkit-font-smoothing:antialiased;
}

/* ── Keyframes ─────────────────────────────────── */
@keyframes srFloatUp { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:none} }
@keyframes srShimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes srPulse   { 0%,100%{opacity:1;box-shadow:0 0 8px rgba(52,211,153,.8)} 50%{opacity:.4;box-shadow:none} }
@keyframes srOrb     { 0%,100%{transform:translateY(0) scale(1)} 50%{transform:translateY(-24px) scale(1.04)} }
@keyframes srGridDrift{from{background-position:0 0} to{background-position:60px 60px}}
@keyframes srCardIn  { from{opacity:0;transform:translateY(22px) scale(.97)} to{opacity:1;transform:none} }
@keyframes srTickScroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
@keyframes srPricePop{ 0%{opacity:0;transform:scale(.9)} 60%{transform:scale(1.04)} 100%{opacity:1;transform:scale(1)} }
@keyframes srBorderPulse{ 0%,100%{box-shadow:0 0 0 1px rgba(212,175,55,.1)} 50%{box-shadow:0 0 0 1px rgba(212,175,55,.35),0 0 20px rgba(212,175,55,.08)} }
@keyframes srScan{ 0%{transform:translateY(-100%)} 100%{transform:translateY(200%)} }

/* ── BG ─────────────────────────────────────────── */
.sr2-bg{
  position:fixed; inset:0; pointer-events:none; z-index:0;
  background:linear-gradient(rgba(212,175,55,.018) 1px,transparent 1px),
             linear-gradient(90deg,rgba(212,175,55,.018) 1px,transparent 1px);
  background-size:56px 56px; animation:srGridDrift 28s linear infinite;
}
.sr2-bg::after{
  content:''; position:absolute; inset:0;
  background:
    radial-gradient(ellipse 60% 50% at 15% 20%,rgba(212,175,55,.08) 0%,transparent 55%),
    radial-gradient(ellipse 45% 40% at 85% 80%,rgba(56,189,248,.04) 0%,transparent 55%);
}
.sr2-orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;animation:srOrb ease-in-out infinite}
.sr2-orb1{width:440px;height:440px;background:rgba(212,175,55,.035);top:-100px;left:-80px;animation-duration:14s}
.sr2-orb2{width:340px;height:340px;background:rgba(56,189,248,.025);bottom:60px;right:-70px;animation-duration:18s;animation-delay:-7s}

/* ── Layout ─────────────────────────────────────── */
.sr2-wrap{display:flex;min-height:100vh;position:relative;z-index:1}
.sr2-main{flex:1;padding:0;min-width:0;display:flex;flex-direction:column}

/* ── Ticker strip ───────────────────────────────── */
.sr2-ticker-strip{
  background:rgba(3,8,20,.97); border-bottom:1px solid rgba(212,175,55,.1);
  overflow:hidden; white-space:nowrap; backdrop-filter:blur(20px);
  position:relative; flex-shrink:0;
}
.sr2-ticker-strip::before{
  content:''; position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.4),transparent);
  z-index:2;
}
.sr2-ticker-inner{display:inline-flex;animation:srTickScroll 40s linear infinite;gap:0}
.sr2-ticker-inner:hover{animation-play-state:paused}
.sr2-tick-item{
  display:inline-flex; align-items:center; gap:10px;
  padding:11px 26px; border-right:1px solid rgba(255,255,255,.04);
  font-size:.75rem; white-space:nowrap;
}
.sr2-tick-sym{font-family:var(--ff-mono);font-weight:700;color:#f1f5f9;letter-spacing:.8px}
.sr2-tick-pr{font-family:var(--ff-mono);color:var(--muted);font-variant-numeric:tabular-nums}
.sr2-tick-chg{
  font-family:var(--ff-mono); font-weight:700; font-size:.7rem;
  padding:2px 8px; border-radius:99px;
}
.sr2-tick-chg.up{color:var(--green);background:rgba(52,211,153,.1)}
.sr2-tick-chg.dn{color:var(--red);background:rgba(248,113,113,.1)}

/* ── Content ────────────────────────────────────── */
.sr2-content{padding:44px 48px 60px;flex:1}

/* ── Hero ───────────────────────────────────────── */
.sr2-hero{margin-bottom:40px;animation:srFloatUp .6s ease both}
.sr2-eyebrow{
  display:inline-flex; align-items:center; gap:9px;
  font-family:var(--ff-head); font-size:.66rem; font-weight:700;
  letter-spacing:3.5px; text-transform:uppercase;
  color:var(--green); margin-bottom:16px; padding:7px 18px;
  background:rgba(52,211,153,.07); border:1px solid rgba(52,211,153,.2);
  border-radius:99px;
}
.sr2-live-dot{
  width:7px;height:7px;border-radius:50%;background:var(--green);
  animation:srPulse 1.8s ease-in-out infinite;
}
.sr2-title{
  font-family:var(--ff-head); font-weight:800;
  font-size:clamp(2.2rem,4vw,3.2rem); line-height:1.05;
  margin:0 0 12px; letter-spacing:-.5px;
  background:linear-gradient(130deg,#fff 20%,#f1f5f9 40%,var(--gold) 70%,var(--gold-l) 92%);
  background-size:200% auto;
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
  animation:srShimmer 6s linear infinite;
}
.sr2-sub{
  font-family:var(--ff-body); color:var(--muted);
  font-size:.95rem; max-width:540px; line-height:1.7; margin-bottom:0;
}

/* ── Search ─────────────────────────────────────── */
.sr2-search-wrap{margin-bottom:40px;animation:srFloatUp .65s .06s ease both}
.sr2-search-box{
  display:flex; align-items:center; gap:14px;
  background:var(--card); border:1px solid rgba(212,175,55,.1);
  border-radius:20px; padding:8px 8px 8px 24px;
  backdrop-filter:blur(20px); max-width:640px;
  transition:border-color .3s, box-shadow .3s;
  box-shadow:0 10px 40px rgba(0,0,0,.3);
}
.sr2-search-box:focus-within{
  border-color:rgba(212,175,55,.4);
  box-shadow:0 0 0 3px rgba(212,175,55,.09),0 16px 50px rgba(0,0,0,.4);
}
.sr2-search-box i{color:var(--muted);font-size:1rem;flex-shrink:0;transition:color .3s}
.sr2-search-box:focus-within i{color:var(--gold)}
#srInput{
  flex:1; background:none; border:none; outline:none;
  font-family:var(--ff-body); color:var(--text); font-size:1rem; font-weight:400;
  padding:12px 0;
}
#srInput::placeholder{color:var(--muted)}
.sr2-search-btn{
  font-family:var(--ff-head); font-size:.85rem; font-weight:700;
  background:linear-gradient(135deg,var(--gold),var(--gold-l));
  border:none; padding:13px 28px; border-radius:14px;
  color:#020617; cursor:pointer; letter-spacing:.3px;
  transition:transform .25s cubic-bezier(.34,1.56,.64,1),box-shadow .25s;
  display:flex; align-items:center; gap:8px; white-space:nowrap;
  box-shadow:0 4px 16px rgba(212,175,55,.3);
}
.sr2-search-btn:hover{transform:scale(1.05);box-shadow:0 8px 28px rgba(212,175,55,.5)}
.sr2-search-btn:active{transform:scale(1)}

/* ── Section label ──────────────────────────────── */
.sr2-sec-label{display:flex;align-items:center;gap:16px;margin-bottom:22px}
.sr2-sec-line{flex:1;height:1px;background:linear-gradient(90deg,rgba(212,175,55,.3),transparent)}
.sr2-sec-text{
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  letter-spacing:2.5px; text-transform:uppercase; color:var(--gold);
  white-space:nowrap; display:flex; align-items:center; gap:7px;
}

/* ── Market summary ─────────────────────────────── */
.sr2-mkt-row{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:40px}
.sr2-mkt-card{
  flex:1; min-width:130px;
  background:linear-gradient(160deg,rgba(8,14,32,.95),rgba(4,9,22,.95));
  border:1px solid rgba(255,255,255,.07); border-radius:18px;
  padding:18px 20px; backdrop-filter:blur(16px);
  transition:transform .3s,border-color .3s,box-shadow .3s;
  animation:srFloatUp .6s ease both;
  position:relative; overflow:hidden;
}
.sr2-mkt-card::before{
  content:''; position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.2),transparent);
}
.sr2-mkt-card:hover{
  transform:translateY(-4px); border-color:rgba(212,175,55,.2);
  box-shadow:0 16px 40px rgba(0,0,0,.4);
}
.sr2-mkt-name{
  font-family:var(--ff-head); font-size:.62rem; font-weight:700;
  text-transform:uppercase; letter-spacing:2px; color:var(--muted); margin-bottom:10px;
}
.sr2-mkt-val{
  font-family:var(--ff-mono); font-size:1.25rem; font-weight:700;
  color:#f1f5f9; font-variant-numeric:tabular-nums; letter-spacing:-.3px;
}
.sr2-mkt-chg{
  font-family:var(--ff-mono); font-size:.75rem; font-weight:600; margin-top:5px;
}
.sr2-mkt-chg.up{color:var(--green)}.sr2-mkt-chg.dn{color:var(--red)}

/* ── Stock card grid ────────────────────────────── */
.sr2-grid{
  display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr));
  gap:22px; margin-bottom:60px;
}

/* ── Stock card ─────────────────────────────────── */
.sr2-card{
  background:linear-gradient(160deg,rgba(8,14,32,.95),rgba(4,9,22,.95));
  border:1px solid rgba(255,255,255,.07); border-radius:24px;
  overflow:hidden; backdrop-filter:blur(20px); position:relative;
  transition:transform .4s cubic-bezier(.34,1.56,.64,1),border-color .3s,box-shadow .3s;
  animation:srCardIn .6s ease both;
}
.sr2-card:nth-child(1){animation-delay:.05s}.sr2-card:nth-child(2){animation-delay:.12s}
.sr2-card:nth-child(3){animation-delay:.19s}.sr2-card:nth-child(4){animation-delay:.26s}
.sr2-card:nth-child(5){animation-delay:.33s}.sr2-card:nth-child(6){animation-delay:.40s}
.sr2-card:hover{
  transform:translateY(-10px);
  border-color:rgba(212,175,55,.28);
  box-shadow:0 32px 70px rgba(0,0,0,.6), 0 0 50px rgba(212,175,55,.06);
}
/* Scan line effect */
.sr2-card::after{
  content:''; position:absolute; left:0; right:0; height:40%;
  background:linear-gradient(transparent,rgba(212,175,55,.015),transparent);
  opacity:0; transition:opacity .3s;
  animation:srScan 3s linear infinite; pointer-events:none;
}
.sr2-card:hover::after{opacity:1}
/* Top bar */
.sr2-card-bar{height:3px;width:100%;flex-shrink:0}

.sr2-card-inner{padding:26px}

/* Card header */
.sr2-card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.sr2-card-ident{display:flex;align-items:center;gap:14px}
.sr2-card-logo{
  width:46px; height:46px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  font-family:var(--ff-head); font-size:1.1rem; font-weight:800;
  color:var(--gold); flex-shrink:0;
  background:linear-gradient(135deg,rgba(212,175,55,.15),rgba(212,175,55,.04));
  border:1px solid rgba(212,175,55,.2);
}
.sr2-card-sym{
  font-family:var(--ff-head); font-size:1.2rem; font-weight:800;
  color:#f1f5f9; letter-spacing:.5px;
}
.sr2-card-name{
  font-family:var(--ff-body); font-size:.73rem; font-weight:400;
  color:var(--muted); margin-top:3px;
}
.sr2-live-badge{
  display:flex; align-items:center; gap:5px;
  font-family:var(--ff-head); font-size:.6rem; font-weight:700;
  letter-spacing:1.2px; text-transform:uppercase; color:var(--green);
  background:rgba(52,211,153,.08); border:1px solid rgba(52,211,153,.18);
  padding:5px 10px; border-radius:99px;
}

/* Price display */
.sr2-price{
  font-family:var(--ff-mono); font-size:2.2rem; font-weight:700;
  color:#f1f5f9; line-height:1; margin-bottom:10px;
  letter-spacing:-1px; font-variant-numeric:tabular-nums;
}
.sr2-price.loaded{animation:srPricePop .4s ease both}
.sr2-change{
  display:inline-flex; align-items:center; gap:6px;
  font-family:var(--ff-mono); font-size:.82rem; font-weight:700;
  padding:5px 13px; border-radius:99px; margin-bottom:20px;
  letter-spacing:.3px;
}
.sr2-change.up  {color:var(--green);background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.15)}
.sr2-change.down{color:var(--red);background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.15)}
.sr2-change.flat{color:#94a3b8;background:rgba(148,163,184,.06);border:1px solid rgba(148,163,184,.12)}

/* Divider */
.sr2-div{height:1px;background:rgba(255,255,255,.05);margin-bottom:18px}

/* AI insight */
.sr2-ai{
  background:rgba(0,0,0,.3); border:1px solid rgba(255,255,255,.06);
  border-left:2px solid var(--gold); border-radius:12px;
  padding:13px 15px; display:flex; align-items:flex-start; gap:10px;
}
.sr2-ai-icon{
  color:var(--gold); font-size:.82rem; margin-top:1px; flex-shrink:0;
}
.sr2-ai span{
  font-family:var(--ff-body); font-size:.8rem; color:#94a3b8; line-height:1.65;
}

/* Chart toggle */
.sr2-chart-btn{
  display:flex; align-items:center; justify-content:center; gap:8px;
  width:100%; margin-top:14px; padding:11px;
  font-family:var(--ff-head); font-size:.76rem; font-weight:600;
  background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07);
  border-radius:12px; color:var(--muted); cursor:pointer;
  transition:all .22s; letter-spacing:.3px;
}
.sr2-chart-btn:hover{
  background:rgba(212,175,55,.07); border-color:rgba(212,175,55,.25); color:var(--gold);
}
.sr2-chart-btn i{transition:transform .3s;font-size:.7rem}
.sr2-chart-btn.open i{transform:rotate(180deg)}
.sr2-chart-panel{max-height:0;overflow:hidden;transition:max-height .4s ease}
.sr2-chart-panel.open{max-height:380px}
.sr2-chart-panel iframe{
  display:block; width:100%; height:350px; border:0; margin-top:12px;
  border-radius:12px; overflow:hidden;
}

/* ── Education section ──────────────────────────── */
.sr2-edu{margin-bottom:60px}
.sr2-edu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:18px}
.sr2-edu-card{
  background:linear-gradient(160deg,rgba(8,14,32,.9),rgba(4,9,22,.9));
  border:1px solid rgba(255,255,255,.07); border-radius:20px;
  padding:28px; backdrop-filter:blur(12px);
  transition:transform .35s cubic-bezier(.34,1.56,.64,1),border-color .3s,box-shadow .3s;
  animation:srCardIn .6s ease both; position:relative; overflow:hidden;
}
.sr2-edu-card::before{
  content:''; position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.15),transparent);
}
.sr2-edu-card:hover{
  transform:translateY(-6px); border-color:rgba(212,175,55,.22);
  box-shadow:0 20px 50px rgba(0,0,0,.45);
}
.sr2-edu-icon{
  width:48px; height:48px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  font-size:1.1rem; color:var(--gold);
  background:rgba(212,175,55,.08); border:1px solid rgba(212,175,55,.15);
  margin-bottom:18px;
}
.sr2-edu-card h3{
  font-family:var(--ff-head); font-size:1rem; font-weight:700;
  color:#f1f5f9; margin:0 0 10px;
}
.sr2-edu-card p{
  font-family:var(--ff-body); font-size:.82rem; color:var(--muted); line-height:1.7; margin:0;
}

/* ── Responsive ─────────────────────────────────── */
@media(max-width:900px){
  .sr2-content{padding:28px 20px}
  .sr2-grid{grid-template-columns:1fr 1fr}
  .sr2-mkt-row{gap:10px}
}
@media(max-width:600px){
  .sr2-grid{grid-template-columns:1fr}
  .sr2-mkt-row{flex-wrap:wrap}
  .sr2-content{padding:20px 16px}
}
</style>

<div class="sr2-bg"></div>
<div class="sr2-orb sr2-orb1"></div>
<div class="sr2-orb sr2-orb2"></div>

<div class="sr2-wrap">
  <?php include 'includes/sidebar.php'; ?>

  <div class="sr2-main">

    <!-- Ticker strip -->
    <div class="sr2-ticker-strip">
      <div class="sr2-ticker-inner" id="tickerInner">
        <?php
        $tickerData = [
          ['AAPL','Apple','$189.25','+1.2%','up'],
          ['MSFT','Microsoft','$420.50','+0.8%','up'],
          ['NVDA','Nvidia','$875.30','+2.4%','up'],
          ['GOOGL','Alphabet','$175.10','-0.3%','dn'],
          ['AMZN','Amazon','$192.80','+1.1%','up'],
          ['META','Meta','$485.60','+0.6%','up'],
          ['TSLA','Tesla','$248.40','-1.8%','dn'],
          ['MA','Mastercard','$472.90','+0.9%','up'],
          ['V','Visa','$275.30','+0.5%','up'],
          ['LLY','Eli Lilly','$820.40','+3.1%','up'],
          ['KLAC','KLA Corp','$690.20','+1.4%','up'],
          ['MCO','Moodys','$455.80','+0.7%','up'],
        ];
        $allTickers = array_merge($tickerData, $tickerData);
        foreach($allTickers as $t): ?>
        <div class="sr2-tick-item">
          <span class="sr2-tick-sym"><?= $t[0] ?></span>
          <span class="sr2-tick-pr"><?= $t[2] ?></span>
          <span class="sr2-tick-chg <?= $t[4] ?>"><?= $t[3] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="sr2-content">

      <!-- Hero -->
      <div class="sr2-hero">
        <div class="sr2-eyebrow"><span class="sr2-live-dot"></span> Live Market Data</div>
        <h1 class="sr2-title">Stock Explorer</h1>
        <p class="sr2-sub">Real-time prices, AI-powered insights နှင့် market intelligence — သင့်ရင်းနှီးမြှုပ်နှံမှုအတွက်</p>
      </div>

      <!-- Search -->
      <div class="sr2-search-wrap">
        <div class="sr2-search-box">
          <i class="fas fa-search"></i>
          <input type="text" id="srInput" placeholder="Stock symbol ရှာမည် — AAPL, TSLA, NVDA …">
          <button class="sr2-search-btn" onclick="searchStock()">
            <i class="fas fa-arrow-right"></i> ရှာမည်
          </button>
        </div>
      </div>

      <!-- Market overview -->
      <div class="sr2-sec-label" style="margin-bottom:20px">
        <span class="sr2-sec-text"><i class="fas fa-globe"></i> Market Overview</span>
        <div class="sr2-sec-line"></div>
      </div>
      <div class="sr2-mkt-row">
        <div class="sr2-mkt-card" style="animation-delay:.05s">
          <div class="sr2-mkt-name">S&amp;P 500</div>
          <div class="sr2-mkt-val" id="mktSP">5,248</div>
          <div class="sr2-mkt-chg up" id="mktSPc">▲ +0.62%</div>
        </div>
        <div class="sr2-mkt-card" style="animation-delay:.1s">
          <div class="sr2-mkt-name">NASDAQ</div>
          <div class="sr2-mkt-val" id="mktNQ">16,490</div>
          <div class="sr2-mkt-chg up" id="mktNQc">▲ +0.88%</div>
        </div>
        <div class="sr2-mkt-card" style="animation-delay:.15s">
          <div class="sr2-mkt-name">DOW</div>
          <div class="sr2-mkt-val" id="mktDJ">38,960</div>
          <div class="sr2-mkt-chg dn" id="mktDJc">▼ −0.14%</div>
        </div>
        <div class="sr2-mkt-card" style="animation-delay:.2s">
          <div class="sr2-mkt-name">VIX</div>
          <div class="sr2-mkt-val" id="mktVIX">14.8</div>
          <div class="sr2-mkt-chg dn" id="mktVIXc">▼ −2.1%</div>
        </div>
      </div>

      <!-- Watchlist -->
      <div class="sr2-sec-label">
        <span class="sr2-sec-text"><i class="fas fa-star"></i> Live Watchlist</span>
        <div class="sr2-sec-line"></div>
      </div>

      <div class="sr2-grid">
        <?php
        $watchlist = [
          ['AAPL','Apple Inc.','A','linear-gradient(90deg,#34d399,#10b981,transparent)'],
          ['TSLA','Tesla, Inc.','T','linear-gradient(90deg,#f87171,#ef4444,transparent)'],
          ['NVDA','NVIDIA Corp.','N','linear-gradient(90deg,#a78bfa,#7c3aed,transparent)'],
          ['MSFT','Microsoft Corp.','M','linear-gradient(90deg,#60a5fa,#3b82f6,transparent)'],
          ['LLY','Eli Lilly','L','linear-gradient(90deg,#fbbf24,#d97706,transparent)'],
          ['MCO','Moodys Corp.','M','linear-gradient(90deg,#22d3ee,#0891b2,transparent)'],
        ];
        foreach($watchlist as $w):
        ?>
        <div class="sr2-card" data-symbol="<?= $w[0] ?>">
          <div class="sr2-card-bar" style="background:<?= $w[3] ?>"></div>
          <div class="sr2-card-inner">
            <div class="sr2-card-head">
              <div class="sr2-card-ident">
                <div class="sr2-card-logo"><?= $w[2] ?></div>
                <div>
                  <div class="sr2-card-sym"><?= $w[0] ?></div>
                  <div class="sr2-card-name"><?= $w[1] ?></div>
                </div>
              </div>
              <div class="sr2-live-badge"><span class="sr2-live-dot"></span>LIVE</div>
            </div>
            <div class="sr2-price">—</div>
            <div class="sr2-change flat">Loading…</div>
            <div class="sr2-div"></div>
            <div class="sr2-ai">
              <i class="fas fa-robot sr2-ai-icon"></i>
              <span>Loading AI insight…</span>
            </div>
            <button class="sr2-chart-btn" onclick="srToggleChart(this,'<?= $w[0] ?>')">
              <i class="fas fa-chart-candlestick"></i> View Chart <i class="fas fa-chevron-down"></i>
            </button>
            <div class="sr2-chart-panel"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Education -->
      <div class="sr2-edu">
        <div class="sr2-sec-label">
          <span class="sr2-sec-text"><i class="fas fa-book-open"></i> Stock Basics</span>
          <div class="sr2-sec-line"></div>
        </div>
        <div class="sr2-edu-grid">
          <div class="sr2-edu-card" style="animation-delay:.3s">
            <div class="sr2-edu-icon"><i class="fas fa-chart-pie"></i></div>
            <h3>စတော့ဆိုတာဘာလဲ</h3>
            <p>ကုမ္ပဏီတစ်ခုရဲ့ ownership fraction ကို share လို့ ခေါ်ပြီး ဝယ်ယူသူသည် ထိုကုမ္ပဏီ၏ fractional owner ဖြစ်လာသည်။</p>
          </div>
          <div class="sr2-edu-card" style="animation-delay:.4s">
            <div class="sr2-edu-icon"><i class="fas fa-seedling"></i></div>
            <h3>ဘာကြောင့် ရင်းနှီးမလဲ</h3>
            <p>Compound Growth ဖြင့် long-term wealth တည်ဆောက်ရန် stocks သည် history အရ အကောင်းဆုံး asset class ဖြစ်သည်။</p>
          </div>
          <div class="sr2-edu-card" style="animation-delay:.5s">
            <div class="sr2-edu-icon"><i class="fas fa-shield-alt"></i></div>
            <h3>Risk Management</h3>
            <p>Diversification, position sizing နှင့် stop-loss strategies များဖြင့် downside risk ကို ထိန်းချုပ်နိုင်သည်။</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
document.getElementById('srInput').addEventListener('keypress', e => { if(e.key==='Enter') searchStock(); });

function searchStock() {
  const sym = document.getElementById('srInput').value.trim().toUpperCase();
  if(!sym) return;
  window.open('https://www.google.com/finance/quote/' + sym + ':NASDAQ', '_blank');
}

function generateInsight(change) {
  if(change > 3)  return 'Strong bullish momentum — sustained buying pressure detected';
  if(change > 1)  return 'Moderate uptrend — market sentiment turning positive';
  if(change > 0)  return 'Slight gain — watch for continuation confirmation';
  if(change < -3) return 'High sell pressure — elevated short-term risk, monitor closely';
  if(change < -1) return 'Bearish move — check key support levels before adding';
  return 'Consolidating — awaiting directional catalyst';
}

document.querySelectorAll('.sr2-card').forEach(card => {
  const symbol = card.dataset.symbol;
  fetch('get-price.php?symbol=' + symbol)
    .then(r => r.json())
    .then(data => {
      const price  = parseFloat(data.price);
      const change = parseFloat(data.change) || 0;
      const priceEl  = card.querySelector('.sr2-price');
      const changeEl = card.querySelector('.sr2-change');
      const aiEl     = card.querySelector('.sr2-ai span');
      if(price) {
        priceEl.innerHTML = '$' + price.toFixed(2);
        priceEl.classList.add('loaded');
        const isUp = change > 0.1, isDn = change < -0.1;
        changeEl.innerHTML = (isUp ? '▲ +' : isDn ? '▼ ' : '') + change.toFixed(2) + '%';
        changeEl.className = 'sr2-change ' + (isUp ? 'up' : isDn ? 'down' : 'flat');
        aiEl.textContent   = generateInsight(change);
      } else {
        priceEl.innerHTML = 'N/A';
        changeEl.className = 'sr2-change flat';
        changeEl.innerHTML = '—';
        aiEl.textContent = 'Data unavailable at this time';
      }
    })
    .catch(() => {
      card.querySelector('.sr2-price').innerHTML = 'N/A';
      card.querySelector('.sr2-ai span').textContent = 'API connection error';
    });
});

function srToggleChart(btn, ticker) {
  btn.classList.toggle('open');
  const panel = btn.nextElementSibling;
  if(panel.classList.toggle('open')) {
    panel.innerHTML = `<iframe src="https://s.tradingview.com/widgetembed/?symbol=${encodeURIComponent(ticker)}&interval=D&theme=dark&style=1&locale=en" allowtransparency="true" scrolling="no"></iframe>`;
  } else {
    panel.innerHTML = '';
  }
}
</script>

<?php include 'includes/footer.php'; ?>
