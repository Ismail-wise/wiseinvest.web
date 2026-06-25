<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit(); }
?>
<?php include 'includes/header.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

/* ═══════════════════════════════════════════════════════════
   FINANCIAL FREEDOM JOURNEY — PREMIUM REDESIGN
   ═══════════════════════════════════════════════════════════ */
:root {
  --gold:#d4af37; --gold-l:#facc15; --green:#22c55e; --green-l:#4ade80;
  --navy:#020617; --card:rgba(6,12,28,.92); --brd:rgba(255,255,255,.07);
  --text:#e2e8f0; --muted:#475569;
  --ff-head:'Space Grotesk',system-ui,sans-serif;
  --ff-body:'DM Sans',system-ui,sans-serif;
  --ff-mono:'JetBrains Mono',monospace;
}

/* Keyframes */
@keyframes ffjFadeUp  { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
@keyframes ffjShimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes ffjGridDrift{ from{background-position:0 0} to{background-position:56px 56px} }
@keyframes ffjOrb     { 0%,100%{transform:translate(0,0)} 50%{transform:translate(12px,-10px)} }
@keyframes ffjPulse   { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.6)} }
@keyframes ffjBarFill { from{width:0} to{width:var(--w,0%)} }
@keyframes ffjCardIn  { from{opacity:0;transform:translateY(20px) scale(.98)} to{opacity:1;transform:none} }
@keyframes ffjScan    { from{top:-100%} to{top:120%} }

*{box-sizing:border-box}
html,body{
  background:var(--navy); color:var(--text);
  font-family:var(--ff-body); margin:0; padding:0;
  -webkit-font-smoothing:antialiased;
}

/* ── BG ─────────────────────────────────── */
.ffj-bg{
  position:fixed; inset:0; pointer-events:none; z-index:0;
  background:
    linear-gradient(rgba(212,175,55,.018) 1px,transparent 1px),
    linear-gradient(90deg,rgba(212,175,55,.018) 1px,transparent 1px);
  background-size:56px 56px; animation:ffjGridDrift 28s linear infinite;
}
.ffj-bg::after{
  content:''; position:absolute; inset:0;
  background:
    radial-gradient(ellipse 60% 40% at 10% 20%,rgba(212,175,55,.07) 0%,transparent 55%),
    radial-gradient(ellipse 50% 40% at 90% 80%,rgba(34,197,94,.04) 0%,transparent 55%);
}
.ffj-orb{
  position:fixed; border-radius:50%; filter:blur(90px);
  pointer-events:none; z-index:0; animation:ffjOrb ease-in-out infinite;
}
.ffj-orb1{width:440px;height:440px;background:rgba(212,175,55,.04);top:-120px;left:-80px;animation-duration:14s}
.ffj-orb2{width:320px;height:320px;background:rgba(34,197,94,.03);bottom:-60px;right:-60px;animation-duration:18s;animation-delay:-8s}

/* ── Layout ─────────────────────────────── */
.dashboard-wrapper{display:flex;min-height:100vh;position:relative;z-index:1}
.main{flex:1;padding:32px 40px 70px;min-width:0}
.ff-container{max-width:860px;margin:0 auto}

/* ── Page header ────────────────────────── */
.page-header{
  margin-bottom:36px; padding-bottom:28px;
  border-bottom:1px solid rgba(255,255,255,.06);
  animation:ffjFadeUp .6s ease both;
}
.page-header-badge{
  display:inline-flex; align-items:center; gap:8px;
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  letter-spacing:3px; text-transform:uppercase; color:var(--gold);
  padding:5px 16px; background:rgba(212,175,55,.07);
  border:1px solid rgba(212,175,55,.22); border-radius:99px; margin-bottom:14px;
}
.page-header-dot{
  width:6px;height:6px;border-radius:50%;background:var(--gold);
  box-shadow:0 0 8px rgba(212,175,55,.8);animation:ffjPulse 2s ease-in-out infinite;
}
.page-header h1{
  font-family:var(--ff-head); font-weight:800;
  font-size:clamp(1.8rem,3vw,2.6rem); line-height:1.1;
  margin:0 0 10px; letter-spacing:-.5px;
  background:linear-gradient(135deg,#fff 25%,var(--gold) 65%,var(--gold-l) 100%);
  background-size:200% auto; -webkit-background-clip:text;
  -webkit-text-fill-color:transparent; background-clip:text;
  animation:ffjShimmer 6s linear infinite;
}
.page-header p{
  font-family:var(--ff-body); color:var(--muted); font-size:.92rem;
  margin:0; line-height:1.6;
}

/* ── Currency card ──────────────────────── */
.ffj-currency-card{
  background:linear-gradient(160deg,rgba(8,14,32,.95),rgba(4,9,22,.95));
  border:1px solid rgba(255,255,255,.07); border-radius:18px;
  padding:18px 22px; margin-bottom:20px;
  display:flex; align-items:center; gap:16px;
  animation:ffjFadeUp .55s .05s ease both;
}
.ffj-currency-label{
  font-family:var(--ff-head); font-size:.68rem; font-weight:700;
  text-transform:uppercase; letter-spacing:2px; color:var(--muted); white-space:nowrap;
}

/* ── Cards ──────────────────────────────── */
.ff-card{
  background:linear-gradient(160deg,rgba(8,14,32,.95),rgba(4,9,22,.95));
  border:1px solid rgba(255,255,255,.07); border-radius:24px;
  padding:28px 30px; margin-bottom:20px;
  position:relative; overflow:hidden;
  transition:border-color .3s, box-shadow .3s;
  animation:ffjCardIn .6s ease both;
}
.ff-card::before{
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
  background:var(--card-accent, linear-gradient(90deg,transparent,rgba(212,175,55,.4),transparent));
}
.ff-card::after{
  content:''; position:absolute; left:0; right:0; height:60px;
  background:linear-gradient(transparent,rgba(212,175,55,.012),transparent);
  pointer-events:none; animation:ffjScan 3s ease-out forwards; opacity:0;
}
.ff-card:hover{ border-color:rgba(212,175,55,.18); box-shadow:0 20px 50px rgba(0,0,0,.45); }
.ff-card:hover::after{ opacity:1; }
.ff-card:nth-child(2){animation-delay:.08s}
.ff-card:nth-child(3){animation-delay:.16s}
.ff-card:nth-child(4){animation-delay:.24s}
.ff-card:nth-child(5){animation-delay:.32s}
.ff-card:nth-child(6){animation-delay:.40s}

/* Card header */
.ff-card-head{
  display:flex; align-items:center; gap:14px; margin-bottom:22px;
}
.ff-step-num{
  width:40px;height:40px;border-radius:12px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--ff-head);font-size:1.1rem;font-weight:800;
  background:var(--step-bg,rgba(212,175,55,.1));
  border:1px solid var(--step-brd,rgba(212,175,55,.2));
  color:var(--step-color,#d4af37);
}
.ff-step-text h2{
  font-family:var(--ff-head); font-size:1rem; font-weight:700;
  color:#f1f5f9; margin:0 0 3px; line-height:1.3;
}
.ff-step-text p{ font-family:var(--ff-body); font-size:.78rem; color:var(--muted); margin:0; }

/* Input field */
.input-field{ margin-bottom:18px; }
.ff-card label{
  display:block; margin-bottom:8px;
  font-family:var(--ff-head); font-size:.68rem; font-weight:700;
  text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);
}
.ff-card input,.ff-card select{
  width:100%; padding:12px 16px; border-radius:12px;
  border:1px solid rgba(255,255,255,.08);
  background:rgba(255,255,255,.04);
  font-family:var(--ff-mono); color:#f1f5f9; font-size:.9rem;
  transition:border-color .2s, box-shadow .2s, background .2s;
  outline:none; -webkit-appearance:none; appearance:none;
}
.ff-card input:focus,.ff-card select:focus{
  border-color:rgba(212,175,55,.4); background:rgba(212,175,55,.04);
  box-shadow:0 0 0 3px rgba(212,175,55,.1);
}
.ff-card input[type=range]{
  -webkit-appearance:auto; appearance:auto;
  padding:0; height:6px; border:none; background:none;
  accent-color:var(--gold); cursor:pointer;
}
.ff-card select option{background:#0d1b2e}

/* Return label */
#returnLabel{
  font-family:var(--ff-mono); font-weight:700; font-size:1rem; color:var(--gold);
  margin-left:8px; text-shadow:0 0 12px rgba(212,175,55,.4);
}

/* ── Result display ─────────────────────── */
.ff-result{
  margin-top:16px; padding:16px 20px;
  border-radius:14px; display:none;
  border-left:3px solid var(--green);
  background:rgba(34,197,94,.06);
  border-top:1px solid rgba(34,197,94,.12);
  border-right:1px solid rgba(34,197,94,.08);
  border-bottom:1px solid rgba(34,197,94,.08);
  animation:ffjFadeUp .4s ease both;
}
.ff-result-label{
  font-family:var(--ff-head); font-size:.62rem; font-weight:700;
  text-transform:uppercase; letter-spacing:2px; color:var(--green);
  margin-bottom:6px; opacity:.8;
}
.ff-result b{
  font-family:var(--ff-mono); font-size:1.6rem; font-weight:700; display:block;
  background:linear-gradient(90deg,var(--green),var(--green-l));
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}

/* ── Progress bar ───────────────────────── */
.ff-progress-wrap{ margin:18px 0 10px; }
.ff-progress-meta{
  display:flex; justify-content:space-between; margin-bottom:8px;
}
.ff-progress-meta-label{
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);
}
.ff-progress-pct{
  font-family:var(--ff-mono); font-size:.8rem; font-weight:700; color:var(--gold);
}
.ff-progress{
  height:8px; background:rgba(255,255,255,.06);
  border-radius:99px; overflow:hidden; border:none;
}
.ff-bar{
  height:100%; width:0%; border-radius:99px;
  background:linear-gradient(90deg,var(--green),var(--green-l),var(--gold));
  transition:width .9s cubic-bezier(.4,0,.2,1);
}

/* ── Chart box ──────────────────────────── */
.chart-box{
  margin-top:22px; background:rgba(0,0,0,.25);
  border:1px solid rgba(255,255,255,.06); border-radius:16px; padding:18px;
}
.chart-box-header{
  display:flex; align-items:center; gap:10px; margin-bottom:14px;
}
.chart-box-header i{ color:var(--green); }
.chart-box-header h3{
  font-family:var(--ff-head); font-size:.85rem; font-weight:700; color:#f1f5f9; margin:0;
}
canvas#growthChart{ width:100%; height:220px; display:block; }

/* ── Tooltip ────────────────────────────── */
.tip{
  display:inline-flex; align-items:center; justify-content:center;
  width:18px; height:18px; border-radius:50%;
  background:rgba(34,197,94,.15); border:1px solid rgba(34,197,94,.3);
  color:var(--green); font-family:var(--ff-mono); font-size:.65rem; font-weight:700;
  cursor:pointer; margin-left:6px; position:relative;
  transition:transform .2s, box-shadow .2s;
}
.tip:hover{ transform:scale(1.15); box-shadow:0 0 10px rgba(34,197,94,.4); }
.tip-text{
  position:absolute; bottom:140%; left:50%;
  transform:translateX(-50%) translateY(8px);
  background:linear-gradient(160deg,#0d1b2e,#060f1c);
  border:1px solid rgba(255,255,255,.1); color:#e2e8f0;
  padding:12px 16px; border-radius:12px;
  font-family:var(--ff-body); font-size:.78rem; width:250px; max-width:90vw;
  box-shadow:0 20px 50px rgba(0,0,0,.6); opacity:0;
  pointer-events:none; transition:all .2s ease; z-index:100; line-height:1.5;
}
.tip:hover .tip-text{ opacity:1; transform:translateX(-50%) translateY(0); }

/* ── Summary card ───────────────────────── */
.ff-summary{
  background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.07);
  border-radius:14px; padding:18px 20px; margin-top:16px;
  font-family:var(--ff-body); font-size:.88rem; line-height:2;
  color:#94a3b8;
}
.ff-summary b{ color:#e2e8f0; font-weight:600; font-family:var(--ff-mono); }

/* ── Reset button ───────────────────────── */
.reset-btn{
  width:100%; padding:14px; border-radius:14px; border:none;
  font-family:var(--ff-head); font-size:.88rem; font-weight:700;
  letter-spacing:.5px; cursor:pointer; margin-top:20px;
  background:linear-gradient(135deg,#ef4444,#dc2626);
  color:#fff; box-shadow:0 8px 24px rgba(239,68,68,.3);
  transition:transform .2s, box-shadow .2s;
}
.reset-btn:hover{ transform:translateY(-2px); box-shadow:0 14px 36px rgba(239,68,68,.5); }
.reset-btn:active{ transform:scale(.98); }

/* ── Responsive ─────────────────────────── */
@media(max-width:960px){
  /* Stack sidebar above content so it no longer squeezes the page (mirrors emergency-fund .ef-shell) */
  .dashboard-wrapper{ flex-direction:column; }
}
@media(max-width:768px){
  .main{ padding:20px 16px; }
  .ff-card{ padding:20px; }
  .page-header h1{ font-size:1.8rem; }
  /* Allow flex rows to wrap instead of overflowing on narrow screens */
  .ffj-currency-card{ flex-wrap:wrap; }
  .ff-card-head{ flex-wrap:wrap; }
  .chart-box-header{ flex-wrap:wrap; }
}
@media(max-width:480px){
  .page-header h1{ font-size:1.5rem; }
  .page-header p{ font-size:.85rem; }
}
</style>

<div class="ffj-bg"></div>
<div class="ffj-orb ffj-orb1"></div>
<div class="ffj-orb ffj-orb2"></div>

<div class="dashboard-wrapper">
  <?php include 'includes/sidebar.php'; ?>

  <div class="main">
    <div class="ff-container">

      <!-- Page Header -->
      <div class="page-header">
        <div class="page-header-badge"><span class="page-header-dot"></span> Finance · Journey Planner</div>
        <h1>Financial Freedom Journey</h1>
        <p>Step-by-step inflation-adjusted capital planning — calculate your exact freedom number.</p>
      </div>

      <!-- Currency Selector -->
      <div class="ffj-currency-card">
        <span class="ffj-currency-label"><i class="fas fa-coins" style="color:var(--gold);margin-right:7px"></i>Currency</span>
        <select id="currency" style="max-width:240px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:9px 14px;color:#f1f5f9;font-family:var(--ff-body);outline:none;cursor:pointer">
          <option value="$">USD — United States Dollar</option>
          <option value="฿">THB — Thai Baht</option>
          <option value="¥">JPY — Japanese Yen</option>
          <option value="MMK">MMK — Myanmar Kyat</option>
        </select>
      </div>

      <!-- Step 1 -->
      <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(212,175,55,.5),transparent)">
        <div class="ff-card-head">
          <div class="ff-step-num" style="--step-bg:rgba(212,175,55,.12);--step-brd:rgba(212,175,55,.25);--step-color:#d4af37">1</div>
          <div class="ff-step-text">
            <h2>Inflation-Adjusted Annual Expenses</h2>
            <p>Project your future annual cost of living</p>
          </div>
        </div>

        <div class="input-field">
          <label>Annual Expenses (Today) <span class="tip">ⓘ<span class="tip-text">သင်ယနေ့ အသုံးပြုနေသော တစ်နှစ်တာ အသုံးစရိတ် (စားစရိတ်၊ အိမ်စရိတ်၊ သွားလာစရိတ်)</span></span></label>
          <input type="number" id="c1_expense" placeholder="e.g. 24000">
        </div>
        <div class="input-field">
          <label>Number of Years <span class="tip">ⓘ<span class="tip-text">ဘယ်နှစ်နှစ်အကြာတွင် အသုံးစရိတ်ကို ခန့်မှန်းချင်ပါသလဲ</span></span></label>
          <input type="number" id="c1_years" placeholder="e.g. 20">
        </div>
        <div class="input-field">
          <label>Inflation Rate (%) <span class="tip">ⓘ<span class="tip-text">ငွေဖောင်းပွမှုနှုန်း။ ပုံမှန်အားဖြင့် ၂% မှ ၅% အတွင်း ဖြစ်တတ်သည်</span></span></label>
          <input type="number" id="c1_inflation" value="5" placeholder="e.g. 5">
        </div>
        <div class="ff-result" id="r1">
          <div class="ff-result-label">Inflation-Adjusted Annual Expenses</div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(34,197,94,.4),transparent)">
        <div class="ff-card-head">
          <div class="ff-step-num" style="--step-bg:rgba(34,197,94,.1);--step-brd:rgba(34,197,94,.2);--step-color:#22c55e">2</div>
          <div class="ff-step-text">
            <h2>Financial Freedom Capital (MFFC)</h2>
            <p>Your minimum freedom fund requirement</p>
          </div>
        </div>

        <div class="input-field">
          <label>Years Expenses Must Last <span class="tip">ⓘ<span class="tip-text">Financial Freedom ရရှိပြီးနောက် သင့်အသုံးစရိတ်ကို ဘယ်နှစ်နှစ် ထောက်ပံ့မည်</span></span></label>
          <input type="number" id="c2_years" value="25" placeholder="e.g. 25">
        </div>
        <div class="input-field">
          <label>Expected Annual Return: <span id="returnLabel" style="color:var(--green)">7%</span> <span class="tip">ⓘ<span class="tip-text">သင့်ရင်းနှီးမြှုပ်နှံမှုမှ မျှော်လင့်ထားသော နှစ်စဉ်အမြတ်နှုန်း</span></span></label>
          <input type="range" min="2" max="15" value="7" id="c2_return">
        </div>
        <div class="ff-result" id="r2">
          <div class="ff-result-label">Minimum Freedom Capital Required</div>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(96,165,250,.4),transparent)">
        <div class="ff-card-head">
          <div class="ff-step-num" style="--step-bg:rgba(96,165,250,.1);--step-brd:rgba(96,165,250,.2);--step-color:#60a5fa">3</div>
          <div class="ff-step-text">
            <h2>Required Rate of Return</h2>
            <p>What return rate do you need to hit your target?</p>
          </div>
        </div>

        <div class="input-field">
          <label>Current Liquid Assets <span class="tip">ⓘ<span class="tip-text">အလွယ်တကူ အသုံးပြုနိုင်သော ငွေသားနှင့် ရင်းနှီးမြှုပ်နှံမှုများ</span></span></label>
          <input type="number" id="c3_assets" placeholder="e.g. 50000">
        </div>
        <div class="input-field">
          <label>Annual Savings <span class="tip">ⓘ<span class="tip-text">တစ်နှစ်အတွင်း သင် သိမ်းဆည်းနိုင်သော ငွေပမာဏ</span></span></label>
          <input type="number" id="c3_savings" placeholder="e.g. 12000">
        </div>
        <div class="input-field">
          <label>Time Target (Years) <span class="tip">ⓘ<span class="tip-text">Financial Freedom ရရှိချင်သော အချိန်ကာလ</span></span></label>
          <input type="number" id="c3_years" placeholder="e.g. 15">
        </div>
        <div class="ff-result" id="r3">
          <div class="ff-result-label">Required Annual Return Rate</div>
        </div>
      </div>

      <!-- Step 4 -->
      <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(167,139,250,.4),transparent)">
        <div class="ff-card-head">
          <div class="ff-step-num" style="--step-bg:rgba(167,139,250,.1);--step-brd:rgba(167,139,250,.2);--step-color:#a78bfa">4</div>
          <div class="ff-step-text">
            <h2>Years to Financial Freedom</h2>
            <p>How long until you reach your freedom number?</p>
          </div>
        </div>

        <div class="input-field">
          <label>Current Liquid Assets <span class="tip">ⓘ<span class="tip-text">လက်ရှိ ရင်းနှီးမြှုပ်နှံထားသော စုစုပေါင်း ပိုင်ဆိုင်မှု</span></span></label>
          <input type="number" id="c4_assets" placeholder="e.g. 50000">
        </div>
        <div class="input-field">
          <label>Annual Savings <span class="tip">ⓘ<span class="tip-text">တစ်နှစ်အတွင်း ထပ်မံရင်းနှီးမြှုပ်နှံမည့် ငွေပမာဏ</span></span></label>
          <input type="number" id="c4_savings" placeholder="e.g. 12000">
        </div>
        <div class="input-field">
          <label>Expected Annual Return (%) <span class="tip">ⓘ<span class="tip-text">Portfolio မှ မျှော်လင့်ထားသော နှစ်စဉ်အမြတ်နှုန်း</span></span></label>
          <input type="number" id="c4_return" value="10" placeholder="e.g. 10">
        </div>

        <div class="ff-result" id="r4">
          <div class="ff-result-label">Years Until Financial Freedom</div>
        </div>

        <div class="ff-progress-wrap">
          <div class="ff-progress-meta">
            <span class="ff-progress-meta-label"><i class="fas fa-chart-line" style="color:var(--gold);margin-right:5px"></i>Progress to Freedom</span>
            <span class="ff-progress-pct" id="progressText"></span>
          </div>
          <div class="ff-progress"><div class="ff-bar" id="progressBar"></div></div>
        </div>

        <div class="chart-box">
          <div class="chart-box-header">
            <i class="fas fa-chart-area"></i>
            <h3>Wealth Growth Projection</h3>
          </div>
          <canvas id="growthChart"></canvas>
        </div>
      </div>

      <!-- Summary -->
      <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(212,175,55,.3),transparent)">
        <div class="ff-card-head">
          <div class="ff-step-num" style="--step-bg:rgba(212,175,55,.1);--step-brd:rgba(212,175,55,.2);--step-color:#d4af37">
            <i class="fas fa-chart-bar" style="font-size:.9rem"></i>
          </div>
          <div class="ff-step-text">
            <h2>Financial Freedom Summary</h2>
            <p>Your complete picture at a glance</p>
          </div>
        </div>
        <div class="ff-summary" id="summary">Awaiting input data…</div>
        <button class="reset-btn" onclick="window.location.reload()">
          <i class="fas fa-rotate-left"></i> Reset Calculator
        </button>
      </div>

    </div>
  </div>
</div>

<script>
let adjustedExpense = 0;
let mffc = 0;

function getCurrency() { return document.getElementById("currency").value; }

function animateValue(el, start, end, duration) {
    let startTime = null;
    function animation(currentTime) {
        if (!startTime) startTime = currentTime;
        const progress = Math.min((currentTime - startTime) / duration, 1);
        const value = start + (end - start) * progress;
        el.style.display = "block";
        el.querySelector('b') ? el.querySelector('b').innerHTML = getCurrency() + " " + value.toLocaleString(undefined, {maximumFractionDigits: 0}) : (el.innerHTML = (el.querySelector('.ff-result-label') ? el.querySelector('.ff-result-label').outerHTML : '') + getCurrency() + " " + value.toLocaleString(undefined, {maximumFractionDigits: 0}));
        if (progress < 1) requestAnimationFrame(animation);
    }
    el.style.display = "block";
    if(!el.querySelector('b')){ const b=document.createElement('b'); el.appendChild(b); }
    requestAnimationFrame(animation);
}

function calc1() {
    const e = +c1_expense.value;
    const y = +c1_years.value;
    const i = +c1_inflation.value / 100;
    if (!e || y < 0) return;
    adjustedExpense = e * Math.pow(1 + i, y);
    animateValue(r1, 0, adjustedExpense, 600);
    updateSummary();
}

function calc2() {
    const y = +c2_years.value;
    const r = +c2_return.value / 100;
    if (adjustedExpense <= 0 || y <= 0 || r <= 0) return;
    mffc = adjustedExpense * ((1 - Math.pow(1 + r, -y)) / r) * (1 + r);
    animateValue(r2, 0, mffc, 600);
    updateSummary();
}

function calc3() {
    const PV = +c3_assets.value;
    const PMT = +c3_savings.value;
    const n = +c3_years.value;
    if (mffc <= 0 || n <= 0) return;
    let r = 0.10;
    for (let i = 0; i < 100; i++) {
        let A = Math.pow(1 + r, n);
        let fv = PV * A + PMT * ((A - 1) / r) * (1 + r);
        let f = fv - mffc;
        if (Math.abs(f) < 1) break;
        let df = PV * n * Math.pow(1 + r, n - 1) + PMT * (((n * A * r - (A - 1)) / (r * r)) * (1 + r) + ((A - 1) / r));
        r = r - f / df;
    }
    r3.style.display = "block";
    if(!r3.querySelector('b')){ const b=document.createElement('b'); r3.appendChild(b); }
    r3.querySelector('b').innerHTML = (r * 100).toFixed(2) + "%";
}

function calc4() {
    const PV = +c4_assets.value;
    const PMT = +c4_savings.value;
    const r = +c4_return.value / 100;
    if (mffc <= 0 || r <= 0) return;
    let years = 0;
    let currentWealth = PV;
    while (years <= 100) {
        let A = Math.pow(1 + r, years);
        currentWealth = PV * A + PMT * ((A - 1) / r) * (1 + r);
        if (currentWealth >= mffc) break;
        years++;
    }
    const freedomYear = new Date().getFullYear() + years;
    r4.style.display = "block";
    if(!r4.querySelector('b')){ const b=document.createElement('b'); r4.appendChild(b); }
    r4.querySelector('b').innerHTML = years + " years <small style='font-size:.8rem;font-family:var(--ff-body);opacity:.7'>(Year " + freedomYear + ")</small>";
    updateProgress(PV, mffc);
    drawWealthChart(PV, PMT, r, years);
    updateSummary(years, freedomYear);
}

function updateProgress(current, target) {
    const percent = Math.min((current / target) * 100, 100);
    progressBar.style.width = percent + "%";
    progressText.innerHTML = percent.toFixed(1) + "%";
}

function updateSummary(years = null, year = null) {
    summary.innerHTML = `
        <span style="color:var(--muted)">Target Annual Expense:</span> <b>${getCurrency()} ${Math.round(adjustedExpense).toLocaleString()}</b><br>
        <span style="color:var(--muted)">Target Capital Required:</span> <b>${getCurrency()} ${Math.round(mffc).toLocaleString()}</b><br>
        <span style="color:var(--muted)">Years Until Freedom:</span> <b>${years ?? "—"}</b><br>
        <span style="color:var(--muted)">Expected Freedom Year:</span> <b>${year ?? "—"}</b>
    `;
}

function drawWealthChart(PV, PMT, r, years) {
    const canvas = document.getElementById("growthChart");
    const ctx = canvas.getContext("2d");
    canvas.width = canvas.offsetWidth;
    canvas.height = 220;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    let data = [];
    for (let i = 0; i <= years; i++) {
        let val = PV * Math.pow(1 + r, i) + PMT * ((Math.pow(1 + r, i) - 1) / r) * (1 + r);
        data.push(val);
    }
    const max = Math.max(...data);
    const w = canvas.width, h = canvas.height;
    ctx.strokeStyle = "rgba(255,255,255,.06)"; ctx.lineWidth = 1;
    for (let i = 0; i < 5; i++) {
        const y = (i / 4) * h;
        ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke();
    }
    ctx.fillStyle = "#475569"; ctx.font = "11px 'DM Sans', sans-serif";
    for (let i = 0; i < 5; i++) {
        const val = Math.round((max / 4) * (4 - i));
        ctx.fillText(getCurrency() + " " + val.toLocaleString(), 4, (i / 4) * h + 12);
    }
    ctx.beginPath(); ctx.strokeStyle = "#22c55e"; ctx.lineWidth = 3;
    data.forEach((v, i) => {
        const x = (i / (data.length - 1 || 1)) * w;
        const y = h - (v / max) * (h - 20);
        if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    });
    ctx.stroke();
    let grad = ctx.createLinearGradient(0, 0, 0, h);
    grad.addColorStop(0, "rgba(34,197,94,.25)"); grad.addColorStop(1, "transparent");
    ctx.lineTo(w, h); ctx.lineTo(0, h); ctx.fillStyle = grad; ctx.fill();
}

document.querySelectorAll("input, select").forEach(el => {
    el.addEventListener("input", () => { calc1(); calc2(); calc3(); calc4(); });
});
c2_return.addEventListener("input", () => {
    document.getElementById("returnLabel").innerHTML = c2_return.value + "%";
});
</script>

<?php include 'includes/footer.php'; ?>
