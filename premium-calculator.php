<?php include 'includes/header.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

/* ═══════════════════════════════════════════════════════════
   PREMIUM FINANCIAL FREEDOM CALCULATOR — ULTRA REDESIGN
   ═══════════════════════════════════════════════════════════ */
:root{
  --gold:#d4af37; --gold-l:#facc15; --green:#22c55e; --green-l:#4ade80;
  --navy:#020617; --card:rgba(6,12,28,.92); --brd:rgba(255,255,255,.07);
  --text:#e2e8f0; --muted:#475569;
  --ff-head:'Space Grotesk',system-ui,sans-serif;
  --ff-body:'DM Sans',system-ui,sans-serif;
  --ff-mono:'JetBrains Mono',monospace;
}

/* Keyframes */
@keyframes pcFadeUp  { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
@keyframes pcShimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes pcGridDrift{from{background-position:0 0} to{background-position:56px 56px}}
@keyframes pcOrb     { 0%,100%{transform:translate(0,0)} 50%{transform:translate(14px,-12px)} }
@keyframes pcPulse   { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(.6)} }
@keyframes pcCrownGlow{ 0%,100%{box-shadow:0 0 30px rgba(212,175,55,.2)} 50%{box-shadow:0 0 60px rgba(212,175,55,.5)} }
@keyframes pcCardIn  { from{opacity:0;transform:translateY(22px) scale(.98)} to{opacity:1;transform:none} }

*{box-sizing:border-box;margin:0;padding:0}
html,body{
  background:var(--navy); color:var(--text);
  font-family:var(--ff-body);
  -webkit-font-smoothing:antialiased;
}

/* ── Fixed BG ────────────────────────────── */
.pc-bg{
  position:fixed; inset:0; pointer-events:none; z-index:0;
  background:
    linear-gradient(rgba(212,175,55,.018) 1px,transparent 1px),
    linear-gradient(90deg,rgba(212,175,55,.018) 1px,transparent 1px);
  background-size:56px 56px; animation:pcGridDrift 28s linear infinite;
}
.pc-bg::after{
  content:''; position:absolute; inset:0;
  background:
    radial-gradient(ellipse 70% 50% at 50% -5%,rgba(212,175,55,.12) 0%,transparent 55%),
    radial-gradient(ellipse 50% 40% at 8%  90%,rgba(34,197,94,.04) 0%,transparent 50%),
    radial-gradient(ellipse 45% 40% at 92% 60%,rgba(212,175,55,.05) 0%,transparent 50%);
}
.pc-orb{
  position:fixed; border-radius:50%; filter:blur(100px);
  pointer-events:none; z-index:0; animation:pcOrb ease-in-out infinite;
}
.pc-orb1{width:500px;height:500px;background:rgba(212,175,55,.05);top:-160px;left:-100px;animation-duration:14s}
.pc-orb2{width:380px;height:380px;background:rgba(34,197,94,.03);bottom:-80px;right:-80px;animation-duration:18s;animation-delay:-9s}

/* ── Page wrapper ────────────────────────── */
.ff-wrapper{
  position:relative; z-index:1; min-height:100vh;
  padding:60px 20px 80px;
  display:flex; justify-content:center;
}
.ff-container{ width:100%; max-width:740px; }

/* ── Page hero ───────────────────────────── */
.pc-hero{
  text-align:center; margin-bottom:44px;
  animation:pcFadeUp .65s ease both;
}
.pc-hero-icon{
  display:inline-flex; align-items:center; justify-content:center;
  width:72px; height:72px; border-radius:50%; margin-bottom:20px;
  background:linear-gradient(135deg,rgba(212,175,55,.2),rgba(212,175,55,.05));
  border:1px solid rgba(212,175,55,.35);
  font-size:1.9rem; color:var(--gold);
  animation:pcCrownGlow 3.5s ease-in-out infinite;
}
.pc-hero-badge{
  display:inline-flex; align-items:center; gap:8px;
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  letter-spacing:3px; text-transform:uppercase; color:var(--gold);
  padding:5px 16px; background:rgba(212,175,55,.07);
  border:1px solid rgba(212,175,55,.22); border-radius:99px; margin-bottom:16px;
}
.pc-hero-dot{
  width:6px; height:6px; border-radius:50%; background:var(--gold);
  box-shadow:0 0 8px rgba(212,175,55,.8); animation:pcPulse 2s ease-in-out infinite;
}
.pc-hero h1{
  font-family:var(--ff-head); font-weight:800;
  font-size:clamp(2rem,4vw,3rem); line-height:1.1; margin-bottom:12px;
  letter-spacing:-.5px;
  background:linear-gradient(135deg,#fff 20%,var(--gold) 60%,var(--gold-l) 90%);
  background-size:200% auto; -webkit-background-clip:text;
  -webkit-text-fill-color:transparent; background-clip:text;
  animation:pcShimmer 6s linear infinite;
}
.pc-hero p{
  font-family:var(--ff-body); color:var(--muted); font-size:.93rem;
  max-width:500px; margin:0 auto; line-height:1.7;
}

/* ── Cards ───────────────────────────────── */
.ff-card{
  background:linear-gradient(160deg,rgba(8,14,32,.95),rgba(4,9,22,.95));
  border:1px solid rgba(255,255,255,.07); border-radius:24px;
  padding:30px 32px; margin-bottom:20px;
  position:relative; overflow:hidden;
  transition:border-color .3s, box-shadow .3s;
  animation:pcCardIn .6s ease both;
}
.ff-card::before{
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
  background:var(--card-accent,linear-gradient(90deg,transparent,rgba(212,175,55,.4),transparent));
}
.ff-card:hover{ border-color:rgba(212,175,55,.18); box-shadow:0 24px 60px rgba(0,0,0,.5); }
.ff-card:nth-child(1){animation-delay:.05s}
.ff-card:nth-child(2){animation-delay:.12s}
.ff-card:nth-child(3){animation-delay:.19s}
.ff-card:nth-child(4){animation-delay:.26s}
.ff-card:nth-child(5){animation-delay:.33s}
.ff-card:nth-child(6){animation-delay:.40s}

/* Card header row */
.pc-card-head{ display:flex; align-items:center; gap:14px; margin-bottom:22px; }
.pc-step-badge{
  width:42px; height:42px; border-radius:13px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-family:var(--ff-head); font-size:1.1rem; font-weight:800;
  background:var(--step-bg,rgba(212,175,55,.1));
  border:1px solid var(--step-brd,rgba(212,175,55,.2));
  color:var(--step-color,var(--gold));
}
.pc-step-info h2{
  font-family:var(--ff-head); font-size:.98rem; font-weight:700;
  color:#f1f5f9; margin:0 0 3px; line-height:1.3;
}
.pc-step-info p{ font-family:var(--ff-body); font-size:.77rem; color:var(--muted); margin:0; }

/* ── Labels & Inputs ──────────────────────── */
.ff-card label{
  display:block; margin:14px 0 8px;
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  text-transform:uppercase; letter-spacing:1.8px; color:var(--muted);
}
.ff-card input, .ff-card select{
  width:100%; padding:13px 16px; border-radius:13px;
  border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.04);
  font-family:var(--ff-mono); color:#f1f5f9; font-size:.9rem;
  transition:border-color .2s, box-shadow .2s, background .2s; outline:none;
  -webkit-appearance:none; appearance:none;
}
.ff-card input:focus, .ff-card select:focus{
  border-color:rgba(212,175,55,.4); background:rgba(212,175,55,.04);
  box-shadow:0 0 0 3px rgba(212,175,55,.1);
}
.ff-card input[type=range]{
  -webkit-appearance:auto; appearance:auto; padding:0; height:6px;
  border:none; background:none; accent-color:var(--gold); cursor:pointer;
}
.ff-card select option{ background:#0d1b2e; }
#returnLabel{
  font-family:var(--ff-mono); font-weight:700; font-size:1.1rem; color:var(--gold);
  margin-left:8px; text-shadow:0 0 12px rgba(212,175,55,.4);
}

/* ── Results ─────────────────────────────── */
.ff-result{
  margin-top:18px; padding:16px 20px; border-radius:14px; display:none;
  border-left:3px solid var(--green); background:rgba(34,197,94,.06);
  border-top:1px solid rgba(34,197,94,.1);
  border-right:1px solid rgba(34,197,94,.06);
  border-bottom:1px solid rgba(34,197,94,.06);
}
.pc-result-label{
  font-family:var(--ff-head); font-size:.62rem; font-weight:700;
  text-transform:uppercase; letter-spacing:2px; color:var(--green); opacity:.8; margin-bottom:6px;
}
.ff-result b{
  display:block; font-family:var(--ff-mono); font-size:1.65rem; font-weight:700;
  background:linear-gradient(90deg,var(--green),var(--green-l));
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.ff-error{ background:rgba(239,68,68,.08); border-left-color:#ef4444; color:#f87171; }

/* ── Tooltip ──────────────────────────────── */
.tip{
  display:inline-flex; align-items:center; justify-content:center;
  width:18px; height:18px; border-radius:50%;
  background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.25);
  color:var(--green); font-family:var(--ff-mono); font-size:.65rem; font-weight:700;
  cursor:pointer; margin-left:6px; position:relative;
  transition:transform .2s,box-shadow .2s;
}
.tip:hover{ transform:scale(1.15); box-shadow:0 0 10px rgba(34,197,94,.4); }
.tip-text{
  position:absolute; bottom:140%; left:50%;
  transform:translateX(-50%) translateY(8px);
  background:linear-gradient(160deg,#0d1b2e,#060f1c);
  border:1px solid rgba(255,255,255,.1); color:#e2e8f0;
  padding:12px 16px; border-radius:12px;
  font-family:var(--ff-body); font-size:.78rem; width:250px;
  box-shadow:0 20px 50px rgba(0,0,0,.6); opacity:0;
  pointer-events:none; transition:all .22s ease; z-index:100; line-height:1.5;
}
.tip.active .tip-text{ opacity:1; transform:translateX(-50%) translateY(0); pointer-events:auto; }
.tip-text::after{
  content:''; position:absolute; bottom:-6px; left:50%; transform:translateX(-50%);
  border:6px solid transparent; border-top-color:#0d1b2e;
}

/* ── Progress ─────────────────────────────── */
.ff-progress-wrap{ margin:18px 0 10px; }
.ff-progress-header{
  display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;
}
.ff-progress-title{
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);
}
#progressText{
  font-family:var(--ff-mono); font-size:.8rem; font-weight:700; color:var(--gold);
}
.ff-progress{
  height:8px; background:rgba(255,255,255,.06);
  border-radius:99px; overflow:hidden;
}
.ff-bar{
  height:100%; width:0%; border-radius:99px;
  background:linear-gradient(90deg,var(--green),var(--green-l),var(--gold));
  transition:width .9s cubic-bezier(.4,0,.2,1);
}

/* ── Chart ────────────────────────────────── */
.chart-box{
  margin-top:22px;
  background:rgba(0,0,0,.2); border:1px solid rgba(255,255,255,.06);
  border-radius:16px; padding:18px;
}
.chart-box-head{
  display:flex; align-items:center; gap:9px; margin-bottom:14px;
}
.chart-box-head i{ color:var(--green); }
.chart-box-head h3{
  font-family:var(--ff-head); font-size:.85rem; font-weight:700; color:#f1f5f9; margin:0;
}
#growthChart{ width:100%; height:260px; display:block; }

/* ── Summary ──────────────────────────────── */
.ff-summary{
  background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.07);
  border-radius:14px; padding:18px 20px; margin-top:16px;
  font-family:var(--ff-body); font-size:.88rem; line-height:2; color:#94a3b8;
}
.ff-summary b{ color:#e2e8f0; font-weight:600; font-family:var(--ff-mono); }

/* ── Reset ────────────────────────────────── */
.reset-btn{
  margin-top:22px; width:100%; padding:14px; border-radius:14px; border:none;
  font-family:var(--ff-head); font-size:.88rem; font-weight:700; letter-spacing:.4px;
  cursor:pointer; color:#fff;
  background:linear-gradient(135deg,#ef4444,#dc2626);
  box-shadow:0 8px 24px rgba(239,68,68,.3);
  transition:transform .2s, box-shadow .2s;
}
.reset-btn:hover{ transform:translateY(-2px); box-shadow:0 14px 36px rgba(239,68,68,.5); }
.reset-btn:active{ transform:scale(.98); }

@media(max-width:700px){
  .ff-wrapper{ padding:40px 16px 60px; }
  .ff-card{ padding:22px 18px; }
  .pc-hero h1{ font-size:1.9rem; }
}
</style>

<div class="pc-bg"></div>
<div class="pc-orb pc-orb1"></div>
<div class="pc-orb pc-orb2"></div>

<div class="ff-wrapper">
<div class="ff-container">

  <!-- Hero -->
  <div class="pc-hero">
    <div class="pc-hero-icon"><i class="fas fa-crown"></i></div>
    <div class="pc-hero-badge"><span class="pc-hero-dot"></span> Premium · Financial Planner</div>
    <h1>Financial Freedom Calculator</h1>
    <p>Precision calculations for your path to financial independence — inflation-adjusted, capital-optimized.</p>
  </div>

  <!-- Currency -->
  <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(212,175,55,.3),transparent)">
    <div class="pc-card-head">
      <div class="pc-step-badge" style="--step-bg:rgba(212,175,55,.1);--step-brd:rgba(212,175,55,.2);--step-color:var(--gold)">
        <i class="fas fa-coins" style="font-size:.9rem"></i>
      </div>
      <div class="pc-step-info">
        <h2>Currency</h2>
        <p>Select your working currency</p>
      </div>
    </div>
    <select id="currency">
      <option value="$">USD — United States Dollar</option>
      <option value="฿">THB — Thai Baht</option>
      <option value="¥">JPY — Japanese Yen</option>
      <option value="MMK">MMK — Myanmar Kyat</option>
    </select>
  </div>

  <!-- Step 1 -->
  <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(212,175,55,.45),transparent)">
    <div class="pc-card-head">
      <div class="pc-step-badge" style="--step-bg:rgba(212,175,55,.12);--step-brd:rgba(212,175,55,.25);--step-color:var(--gold)">1</div>
      <div class="pc-step-info">
        <h2>Inflation-Adjusted Annual Expenses</h2>
        <p>Project future cost of living from today's spending</p>
      </div>
    </div>
    <label>Annual Expenses (Today)
      <span class="tip">ⓘ<span class="tip-text">သင်ယနေ့ အသုံးပြုနေသော တစ်နှစ်တာ အသုံးစရိတ် (စားစရိတ်၊ အိမ်စရိတ်၊ သွားလာစရိတ်)</span></span>
    </label>
    <input type="number" id="c1_expense">
    <label>Number of Years
      <span class="tip">ⓘ<span class="tip-text">ဘယ်နှစ်နှစ်အကြာတွင် အသုံးစရိတ်ကို ခန့်မှန်းချင်ပါသလဲ</span></span>
    </label>
    <input type="number" id="c1_years">
    <label>Inflation Rate (%)
      <span class="tip">ⓘ<span class="tip-text">ငွေဖောင်းပွမှုနှုန်း။ ပုံမှန်အားဖြင့် ၂% မှ ၅% အတွင်း ဖြစ်တတ်သည်</span></span>
    </label>
    <input type="number" id="c1_inflation">
    <div class="ff-result" id="r1">
      <div class="pc-result-label">Inflation-Adjusted Annual Expenses</div>
    </div>
  </div>

  <!-- Step 2 -->
  <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(34,197,94,.4),transparent)">
    <div class="pc-card-head">
      <div class="pc-step-badge" style="--step-bg:rgba(34,197,94,.1);--step-brd:rgba(34,197,94,.2);--step-color:var(--green)">2</div>
      <div class="pc-step-info">
        <h2>Financial Freedom Capital (MFFC)</h2>
        <p>Minimum capital needed to sustain your lifestyle</p>
      </div>
    </div>
    <label>Years Expenses Must Last
      <span class="tip">ⓘ<span class="tip-text">Financial Freedom ရရှိပြီးနောက် သင့်အသုံးစရိတ်ကို ဘယ်နှစ်နှစ် ထောက်ပံ့မည်</span></span>
    </label>
    <input type="number" id="c2_years">
    <label>Expected Annual Return (%)
      <span class="tip">ⓘ<span class="tip-text">သင့်ရင်းနှီးမြှုပ်နှံမှုမှ မျှော်လင့်ထားသော နှစ်စဉ်အမြတ်နှုန်း</span></span>
    </label>
    <input type="range" min="2" max="15" value="7" id="c2_return">
    <span id="returnLabel">7%</span>
    <div class="ff-result" id="r2">
      <div class="pc-result-label">Minimum Freedom Capital Required</div>
    </div>
  </div>

  <!-- Step 3 -->
  <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(96,165,250,.4),transparent)">
    <div class="pc-card-head">
      <div class="pc-step-badge" style="--step-bg:rgba(96,165,250,.1);--step-brd:rgba(96,165,250,.2);--step-color:#60a5fa">3</div>
      <div class="pc-step-info">
        <h2>Required Rate of Return</h2>
        <p>What annual return do you need to hit your goal?</p>
      </div>
    </div>
    <label>Current Liquid Assets
      <span class="tip">ⓘ<span class="tip-text">အလွယ်တကူ အသုံးပြုနိုင်သော ငွေသားနှင့် ရင်းနှီးမြှုပ်နှံမှုများ</span></span>
    </label>
    <input type="number" id="c3_assets">
    <label>Annual Savings
      <span class="tip">ⓘ<span class="tip-text">တစ်နှစ်အတွင်း သင် သိမ်းဆည်းနိုင်သော ငွေပမာဏ</span></span>
    </label>
    <input type="number" id="c3_savings">
    <label>Time Target (Years)
      <span class="tip">ⓘ<span class="tip-text">Financial Freedom ရရှိချင်သော အချိန်ကာလ</span></span>
    </label>
    <input type="number" id="c3_years">
    <div class="ff-result" id="r3">
      <div class="pc-result-label">Required Annual Return Rate</div>
    </div>
  </div>

  <!-- Step 4 -->
  <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(167,139,250,.4),transparent)">
    <div class="pc-card-head">
      <div class="pc-step-badge" style="--step-bg:rgba(167,139,250,.1);--step-brd:rgba(167,139,250,.2);--step-color:#a78bfa">4</div>
      <div class="pc-step-info">
        <h2>Years to Financial Freedom</h2>
        <p>How long until you reach your freedom number?</p>
      </div>
    </div>
    <label>Current Liquid Assets
      <span class="tip">ⓘ<span class="tip-text">လက်ရှိ ရင်းနှီးမြှုပ်နှံထားသော စုစုပေါင်း ပိုင်ဆိုင်မှု</span></span>
    </label>
    <input type="number" id="c4_assets">
    <label>Annual Savings
      <span class="tip">ⓘ<span class="tip-text">တစ်နှစ်အတွင်း ထပ်မံရင်းနှီးမြှုပ်နှံမည့် ငွေပမာဏ</span></span>
    </label>
    <input type="number" id="c4_savings">
    <label>Expected Annual Return (%)
      <span class="tip">ⓘ<span class="tip-text">Portfolio မှ မျှော်လင့်ထားသော နှစ်စဉ်အမြတ်နှုန်း</span></span>
    </label>
    <input type="number" id="c4_return">
    <div class="ff-result" id="r4">
      <div class="pc-result-label">Years Until Financial Freedom</div>
    </div>

    <div class="ff-progress-wrap">
      <div class="ff-progress-header">
        <span class="ff-progress-title"><i class="fas fa-flag" style="color:var(--gold);margin-right:5px"></i>Progress to Freedom</span>
        <span id="progressText"></span>
      </div>
      <div class="ff-progress"><div class="ff-bar" id="progressBar"></div></div>
    </div>

    <div class="chart-box">
      <div class="chart-box-head">
        <i class="fas fa-chart-area"></i>
        <h3>Wealth Growth Projection</h3>
      </div>
      <canvas id="growthChart"></canvas>
    </div>
  </div>

  <!-- Summary -->
  <div class="ff-card" style="--card-accent:linear-gradient(90deg,transparent,rgba(212,175,55,.3),transparent)">
    <div class="pc-card-head">
      <div class="pc-step-badge" style="--step-bg:rgba(212,175,55,.1);--step-brd:rgba(212,175,55,.2);--step-color:var(--gold)">
        <i class="fas fa-chart-bar" style="font-size:.9rem"></i>
      </div>
      <div class="pc-step-info">
        <h2>Financial Freedom Summary</h2>
        <p>Your complete picture at a glance</p>
      </div>
    </div>
    <div class="ff-summary" id="summary">Waiting for calculations...</div>
    <button class="reset-btn" onclick="location.reload()">
      <i class="fas fa-rotate-left"></i> Reset Calculator
    </button>
  </div>

</div>
</div>

<script>
let adjustedExpense=0
let mffc=0

function currency(){
  return document.getElementById("currency").value
}

function animateValue(el,start,end,duration){
  el.style.display="block";
  if(!el.querySelector('b')){ const b=document.createElement('b'); el.appendChild(b); }
  let startTime=null
  function animation(currentTime){
    if(!startTime) startTime=currentTime
    const progress=Math.min((currentTime-startTime)/duration,1)
    const value=start+(end-start)*progress
    el.querySelector('b').innerHTML=currency()+" "+value.toLocaleString(undefined,{maximumFractionDigits:2})
    if(progress<1) requestAnimationFrame(animation)
  }
  requestAnimationFrame(animation)
}

function drawWealthChart(PV,PMT,r,years){
  const canvas=document.getElementById("growthChart")
  const ctx=canvas.getContext("2d")
  canvas.width=canvas.offsetWidth
  canvas.height=260
  ctx.clearRect(0,0,canvas.width,canvas.height)
  let data=[]
  let wealth=PV
  for(let i=0;i<=years;i++){
    wealth=wealth*(1+r)+PMT
    data.push(wealth)
  }
  const max=Math.max(...data)
  const w=canvas.width
  const h=canvas.height
  ctx.strokeStyle="rgba(255,255,255,.05)"; ctx.lineWidth=1
  for(let i=0;i<5;i++){
    const y=(i/4)*h
    ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(w,y); ctx.stroke()
  }
  ctx.fillStyle="#475569"; ctx.font="11px 'DM Sans',sans-serif"
  for(let i=0;i<5;i++){
    const value=Math.round((max/4)*(4-i))
    ctx.fillText(currency()+" "+value.toLocaleString(),4,(i/4)*h+12)
  }
  ctx.beginPath(); ctx.strokeStyle="#22c55e"; ctx.lineWidth=3
  data.forEach((v,i)=>{
    const x=(i/(data.length-1))*w
    const y=h-(v/max)*h
    if(i===0) ctx.moveTo(x,y)
    else ctx.lineTo(x,y)
  })
  ctx.stroke()
  let grad=ctx.createLinearGradient(0,0,0,h)
  grad.addColorStop(0,"rgba(34,197,94,.25)")
  grad.addColorStop(1,"transparent")
  ctx.lineTo(w,h); ctx.lineTo(0,h); ctx.fillStyle=grad; ctx.fill()
  ctx.fillStyle="#4ade80"
  data.forEach((v,i)=>{
    if(i%Math.ceil(years/5)!==0 && i!==years) return
    const x=(i/(data.length-1))*w
    const y=h-(v/max)*h
    ctx.beginPath(); ctx.arc(x,y,4,0,Math.PI*2); ctx.fill()
    ctx.fillStyle="#e5e7eb"; ctx.font="11px 'DM Sans',sans-serif"
    ctx.fillText("Year "+i,x-12,y-10)
    ctx.fillStyle="#4ade80"
  })
}

function calc1(){
  const e=+c1_expense.value
  const y=+c1_years.value
  const i=+c1_inflation.value/100
  if(!e || y<0 || i<0) return
  adjustedExpense=e*Math.pow(1+i,y)
  animateValue(r1,0,adjustedExpense,800)
  updateSummary()
}

function calc2(){
  const y=+c2_years.value
  const r=+c2_return.value/100
  if(adjustedExpense<=0||y<=0||r<=0) return
  mffc=adjustedExpense*((1-Math.pow(1+r,-y))/r)*(1+r)
  animateValue(r2,0,mffc,800)
  updateSummary()
}

function calc3(){
  const PV=+c3_assets.value
  const PMT=+c3_savings.value
  const n=+c3_years.value
  if(mffc<=0||PV<0||PMT<0||n<=0) return
  let r=0.10
  for(let i=0;i<1000;i++){
    const A=Math.pow(1+r,n)
    const fv=PV*A+PMT*((A-1)/r)*(1+r)
    const f=fv-mffc
    if(Math.abs(f)<1e-10) break
    const df=PV*n*Math.pow(1+r,n-1)+PMT*(((A-1)/r)+((n*A*r-(A-1))/(r*r)))
    r=r-f/df
  }
  r3.style.display="block"
  if(!r3.querySelector('b')){ const b=document.createElement('b'); r3.appendChild(b); }
  r3.querySelector('b').innerHTML="Required annual return: "+(r*100).toFixed(2)+"%"
}

function calc4(){
  const PV=+c4_assets.value
  const PMT=+c4_savings.value
  const r=+c4_return.value/100
  if(mffc<=0||PV<0||PMT<0||r<=0) return
  let years=0
  while(years<=100){
    const A=Math.pow(1+r,years)
    const fv=PV*A+PMT*((A-1)/r)*(1+r)
    if(fv>=mffc) break
    years++
  }
  const freedomYear=new Date().getFullYear()+years
  r4.style.display="block"
  if(!r4.querySelector('b')){ const b=document.createElement('b'); r4.appendChild(b); }
  r4.querySelector('b').innerHTML="Financial freedom in "+years+" years <small style='font-size:.8rem;opacity:.7'>(Year "+freedomYear+")</small>"
  updateProgress(PV,mffc)
  drawWealthChart(PV,PMT,r,years)
  updateSummary(years,freedomYear)
}

function updateProgress(current,target){
  const percent=Math.min((current/target)*100,100)
  progressBar.style.width=percent+"%"
  progressText.innerHTML=percent.toFixed(1)+"%"
}

function updateSummary(years=null,year=null){
  summary.innerHTML=`
    <span style="color:var(--muted)">Future Expenses:</span> <b>${currency()} ${adjustedExpense.toLocaleString()}</b><br>
    <span style="color:var(--muted)">Target Capital:</span> <b>${currency()} ${mffc.toLocaleString()}</b><br>
    <span style="color:var(--muted)">Years to Freedom:</span> <b>${years??"-"}</b><br>
    <span style="color:var(--muted)">Freedom Year:</span> <b>${year??"-"}</b>
  `
}

document.querySelectorAll("input").forEach(input=>{
  input.addEventListener("input",()=>{ calc1();calc2();calc3();calc4() })
})

c2_return.addEventListener("input",()=>{ returnLabel.innerHTML=c2_return.value+"%" })
returnLabel.innerHTML=c2_return.value+"%"

const tooltips=document.querySelectorAll(".tip")
tooltips.forEach(tip=>{
  tip.addEventListener("click",(e)=>{
    e.stopPropagation()
    document.querySelectorAll(".tip").forEach(t=>{ if(t!==tip) t.classList.remove("active") })
    tip.classList.toggle("active")
    smartPosition(tip)
  })
})
document.addEventListener("click",()=>{ document.querySelectorAll(".tip").forEach(t=>t.classList.remove("active")) })

function smartPosition(tip){
  const tooltip=tip.querySelector(".tip-text")
  const rect=tooltip.getBoundingClientRect()
  const padding=10
  if(rect.right>window.innerWidth-padding){ tooltip.style.left="auto"; tooltip.style.right="0"; tooltip.style.transform="translateY(0)" }
  if(rect.left<padding){ tooltip.style.left="0"; tooltip.style.transform="translateY(0)" }
}

window.addEventListener("resize",()=>{ document.querySelectorAll(".tip.active").forEach(tip=>smartPosition(tip)) })
</script>

<?php include 'includes/footer.php'; ?>
