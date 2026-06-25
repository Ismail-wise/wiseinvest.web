<?php
session_start();

if(!isset($_SESSION['user_id'])){
  header("Location: login.php");
  exit();
}

$username = $_SESSION['user_name'];
include 'includes/header.php';

/* ── Load CSV ── */
$stocks = [];
if(($handle = fopen("stocks.csv","r")) !== FALSE){
  $headers = fgetcsv($handle);
  while(($row = fgetcsv($handle)) !== FALSE){
    $stocks[] = array_combine($headers, $row);
  }
  fclose($handle);
}

/* ── Calculate WiseInvest Score ── */
foreach($stocks as &$stock){
  $score = 0;
  $altman    = floatval($stock["Altman Z-Score"]);
  $piotroski = intval($stock["Piotroski F Score"]);
  $debt      = floatval($stock["Debt / Equity"]);
  $margin    = floatval(str_replace("%","",$stock["Net Margin"]));
  $roe       = floatval(str_replace("%","",$stock["Return on Equity"]));
  $roic      = floatval(str_replace("%","",$stock["ROIC"]));

  if($altman > 3)     $score += 20;
  if($piotroski >= 7) $score += 20;
  if($roe > 20)       $score += 20;
  if($roic > 15)      $score += 20;
  if($debt < 1)       $score += 10;
  if($margin > 15)    $score += 10;

  $stock["score"] = $score;
}
unset($stock);

usort($stocks, fn($a,$b) => $b["score"] - $a["score"]);
$topStocks = array_slice($stocks, 0, 5);

/* ── Rank config ── */
$rankConfig = [
  1 => ['label'=>'Gold Pick',   'icon'=>'fas fa-trophy', 'color'=>'#d4af37','glow'=>'rgba(212,175,55,.3)','ring'=>'#d4af37','grad'=>'#d4af37,#facc15'],
  2 => ['label'=>'Silver Pick', 'icon'=>'fas fa-medal',  'color'=>'#94a3b8','glow'=>'rgba(148,163,184,.25)','ring'=>'#94a3b8','grad'=>'#94a3b8,#cbd5e1'],
  3 => ['label'=>'Bronze Pick', 'icon'=>'fas fa-award',  'color'=>'#cd7c4a','glow'=>'rgba(205,124,74,.25)','ring'=>'#cd7c4a','grad'=>'#cd7c4a,#f59e0b'],
  4 => ['label'=>'Top Pick',    'icon'=>'fas fa-star',   'color'=>'#60a5fa','glow'=>'rgba(96,165,250,.2)','ring'=>'#60a5fa','grad'=>'#60a5fa,#93c5fd'],
  5 => ['label'=>'Top Pick',    'icon'=>'fas fa-star',   'color'=>'#a78bfa','glow'=>'rgba(167,139,250,.2)','ring'=>'#a78bfa','grad'=>'#a78bfa,#c4b5fd'],
];
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

/* ═══════════════════════════════════════════════════════════
   CONSULTANT'S CHOICE — ULTRA PREMIUM REDESIGN v3
   Font: Space Grotesk + DM Sans + JetBrains Mono
   ═══════════════════════════════════════════════════════════ */
:root{
  --gold:#d4af37; --gold-l:#facc15; --navy:#020617;
  --card:rgba(5,10,26,.94); --text:#e2e8f0; --muted:#475569;
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
@keyframes ccFadeUp    { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:none} }
@keyframes ccShimmer   { from{background-position:-300% center} to{background-position:300% center} }
@keyframes ccOrbFloat  { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(10px,-16px) scale(1.05)} }
@keyframes ccGridDrift { from{background-position:0 0} to{background-position:60px 60px} }
@keyframes ccParticle  { 0%{opacity:0;transform:translateY(100vh) scale(0)} 10%{opacity:.5} 90%{opacity:.15} 100%{opacity:0;transform:translateY(-10vh) scale(1.5)} }
@keyframes ccCrownGlow { 0%,100%{box-shadow:0 0 40px rgba(212,175,55,.18),0 0 0 0 rgba(212,175,55,.08)} 50%{box-shadow:0 0 80px rgba(212,175,55,.38),0 0 0 16px rgba(212,175,55,0)} }
@keyframes ccCardIn    { from{opacity:0;transform:translateX(-28px) scale(.97)} to{opacity:1;transform:none} }
@keyframes medalPulse  { 0%,100%{transform:scale(1);opacity:.35} 50%{transform:scale(1.2);opacity:0} }
@keyframes ccBarFill   { from{width:0} to{width:var(--w)} }
@keyframes ccCountUp   { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
@keyframes ccRingIn    { from{stroke-dashoffset:var(--full)} to{stroke-dashoffset:var(--target)} }
@keyframes ccGlow      { 0%,100%{filter:drop-shadow(0 0 4px var(--gc))} 50%{filter:drop-shadow(0 0 12px var(--gc))} }
@keyframes ccScanLine  { 0%{top:-30%} 100%{top:130%} }

/* ── Background ─────────────────────────────────── */
.cc-bg{
  position:fixed; inset:0; pointer-events:none; z-index:0;
  background:
    linear-gradient(rgba(212,175,55,.02) 1px, transparent 1px),
    linear-gradient(90deg,rgba(212,175,55,.02) 1px, transparent 1px);
  background-size:60px 60px; animation:ccGridDrift 28s linear infinite;
}
.cc-bg::after{
  content:''; position:absolute; inset:0;
  background:
    radial-gradient(ellipse 70% 50% at 50% -5%,  rgba(212,175,55,.14) 0%,transparent 55%),
    radial-gradient(ellipse 50% 40% at 8%  80%,  rgba(96,165,250,.04) 0%,transparent 50%),
    radial-gradient(ellipse 40% 35% at 92% 60%,  rgba(212,175,55,.05) 0%,transparent 50%);
}
.cc-orb{
  position:fixed; border-radius:50%; filter:blur(100px);
  pointer-events:none; z-index:0; animation:ccOrbFloat ease-in-out infinite;
}
.cc-orb-1{width:500px;height:500px;background:rgba(212,175,55,.04);top:-150px;left:-100px;animation-duration:14s}
.cc-orb-2{width:380px;height:380px;background:rgba(167,139,250,.025);bottom:-80px;right:-80px;animation-duration:18s;animation-delay:-9s}

/* Floating particles */
.cc-particle{
  position:fixed; border-radius:50%; pointer-events:none;
  z-index:0; animation:ccParticle linear infinite;
}

/* ── Layout ─────────────────────────────────────── */
.cc-wrapper{display:flex;min-height:100vh;position:relative;z-index:1}
.cc-main{flex:1;padding:40px 44px 70px;position:relative;z-index:1;min-width:0}

/* ── Hero ───────────────────────────────────────── */
.cc-hero{
  text-align:center; padding:48px 20px 56px;
  position:relative; margin-bottom:52px;
  animation:ccFadeUp .65s ease both;
}
.cc-hero-crown{
  display:inline-flex; align-items:center; justify-content:center;
  width:88px; height:88px; border-radius:50%;
  background:linear-gradient(135deg,rgba(212,175,55,.22),rgba(212,175,55,.05));
  border:1px solid rgba(212,175,55,.38);
  margin:0 auto 22px;
  font-size:2.2rem; color:var(--gold);
  animation:ccCrownGlow 3.5s ease-in-out infinite;
  position:relative;
}
.cc-hero-crown::before{
  content:''; position:absolute; inset:-8px; border-radius:50%;
  border:1px solid rgba(212,175,55,.12);
  animation:medalPulse 3s ease-in-out infinite;
}
.cc-hero-eyebrow{
  display:inline-flex; align-items:center; gap:9px;
  font-family:var(--ff-head); font-size:.66rem; font-weight:700;
  letter-spacing:3.5px; text-transform:uppercase; color:var(--gold);
  padding:6px 18px;
  background:rgba(212,175,55,.07); border:1px solid rgba(212,175,55,.22);
  border-radius:99px; margin-bottom:16px;
}
.cc-hero-eyebrow i{font-size:.7rem}
.cc-hero-title{
  font-family:var(--ff-head); font-weight:800;
  font-size:clamp(2.2rem,4.5vw,3.4rem);
  margin:0 0 14px; line-height:1.05; letter-spacing:-.5px;
  background:linear-gradient(135deg,#fff 15%,#f8fafc 35%,var(--gold) 60%,var(--gold-l) 85%);
  background-size:200% auto;
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
  animation:ccShimmer 6s linear infinite;
}
.cc-hero-sub{
  font-family:var(--ff-body); color:var(--muted);
  font-size:.95rem; max-width:540px; margin:0 auto; line-height:1.75;
}

/* Hero meta stats */
.cc-hero-meta{
  display:inline-flex; gap:0; margin-top:28px;
  background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08);
  border-radius:16px; overflow:hidden;
}
.cc-hero-meta-item{
  padding:14px 24px; border-right:1px solid rgba(255,255,255,.06); text-align:center;
}
.cc-hero-meta-item:last-child{border-right:none}
.cc-hero-meta-item strong{
  font-family:var(--ff-head); display:block; font-size:1.4rem;
  font-weight:800; color:var(--gold); line-height:1;
}
.cc-hero-meta-item span{
  font-family:var(--ff-body); font-size:.62rem; font-weight:600;
  color:var(--muted); text-transform:uppercase; letter-spacing:1.5px;
  margin-top:5px; display:block;
}

/* ── Section divider ────────────────────────────── */
.cc-divider{display:flex;align-items:center;gap:18px;margin-bottom:36px}
.cc-divider-line{flex:1;height:1px;background:linear-gradient(90deg,transparent,rgba(212,175,55,.25),transparent)}
.cc-divider-chip{
  display:flex; align-items:center; gap:8px;
  font-family:var(--ff-head); font-size:.65rem; font-weight:700;
  letter-spacing:2.5px; text-transform:uppercase; color:var(--gold);
  padding:6px 16px;
  background:rgba(212,175,55,.07); border:1px solid rgba(212,175,55,.18);
  border-radius:99px; white-space:nowrap;
}

/* ── Cards stack ────────────────────────────────── */
.cc-stack{
  display:flex; flex-direction:column; gap:22px;
  max-width:920px; margin:0 auto;
}

/* ── Single ranking card ────────────────────────── */
.cc-card{
  background:var(--card);
  border-radius:28px;
  border:1px solid rgba(255,255,255,.07);
  overflow:hidden; position:relative;
  backdrop-filter:blur(24px);
  transition:transform .4s cubic-bezier(.34,1.56,.64,1),
             box-shadow .4s, border-color .4s;
  animation:ccCardIn .7s ease both;
}
.cc-card:nth-child(1){animation-delay:.1s}
.cc-card:nth-child(2){animation-delay:.22s}
.cc-card:nth-child(3){animation-delay:.34s}
.cc-card:nth-child(4){animation-delay:.46s}
.cc-card:nth-child(5){animation-delay:.58s}
.cc-card:hover{ transform:translateY(-6px) scale(1.004); }

/* Scan line on hover */
.cc-card::after{
  content:''; position:absolute; left:0; right:0; height:80px;
  background:linear-gradient(transparent,rgba(255,255,255,.01),transparent);
  opacity:0; transition:opacity .3s; pointer-events:none;
  animation:ccScanLine 2.8s linear infinite; top:-30%;
}
.cc-card:hover::after{opacity:1}

/* Card top bar */
.cc-card-bar{height:3px;width:100%}

/* Card inner */
.cc-card-inner{padding:30px 32px 28px}

/* ── Header row ─────────────────────────────────── */
.cc-card-head{
  display:flex; align-items:center; gap:22px; margin-bottom:24px; flex-wrap:wrap;
}

/* Medal */
.cc-rank-medal{
  display:flex; flex-direction:column; align-items:center; gap:5px; flex-shrink:0;
}
.cc-medal-icon{
  width:56px; height:56px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:1.5rem; border:2px solid; position:relative;
}
.cc-medal-icon::after{
  content:''; position:absolute; inset:-6px; border-radius:50%;
  border:1px solid; animation:medalPulse 2.8s ease-in-out infinite;
}
.cc-medal-num{
  font-family:var(--ff-head); font-size:.62rem; font-weight:800;
  letter-spacing:1px; text-transform:uppercase;
}

/* Identity */
.cc-identity{flex:1;min-width:0}
.cc-ticker-row{
  display:flex; align-items:center; gap:12px; margin-bottom:5px; flex-wrap:wrap;
}
.cc-ticker{
  font-family:var(--ff-mono); font-size:1.55rem; font-weight:700;
  color:#f1f5f9; letter-spacing:.5px;
}
.cc-company{
  font-family:var(--ff-body); font-size:.88rem; color:var(--muted);
  overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:340px;
}
.cc-rank-badge{
  display:inline-flex; align-items:center; gap:6px;
  font-family:var(--ff-head); font-size:.6rem; font-weight:700;
  letter-spacing:1px; text-transform:uppercase;
  padding:4px 11px; border-radius:99px; border:1px solid;
}
.cc-rank-badge i{font-size:.55rem}

/* Score arc */
.cc-score-display{
  display:flex; flex-direction:column; align-items:center; gap:4px; flex-shrink:0;
}
.cc-score-arc{position:relative;width:80px;height:80px}
.cc-score-arc svg{width:80px;height:80px;transform:rotate(-90deg)}
.cc-score-arc .arc-track{fill:none;stroke:rgba(255,255,255,.05);stroke-width:6}
.cc-score-arc .arc-fill{
  fill:none; stroke-width:6; stroke-linecap:round;
  stroke-dasharray:208; stroke-dashoffset:208;
  transition:stroke-dashoffset 1.6s cubic-bezier(.4,0,.2,1);
}
.cc-score-center{
  position:absolute; inset:0; display:flex;
  flex-direction:column; align-items:center; justify-content:center;
}
.cc-score-val{
  font-family:var(--ff-head); font-size:1.25rem; font-weight:800; line-height:1;
}
.cc-score-lbl{
  font-family:var(--ff-mono); font-size:.5rem; text-transform:uppercase;
  letter-spacing:1.2px; color:var(--muted); margin-top:2px;
}

/* ── Metrics grid ───────────────────────────────── */
.cc-metrics{
  display:grid; grid-template-columns:repeat(4,1fr);
  gap:10px; margin-bottom:22px;
}
.cc-metric{
  background:rgba(255,255,255,.025);
  border:1px solid rgba(255,255,255,.05); border-radius:14px;
  padding:14px 12px; text-align:center;
  transition:border-color .3s, background .3s, transform .3s;
  position:relative; overflow:hidden;
}
.cc-metric::before{
  content:''; position:absolute; top:0; left:50%; transform:translateX(-50%);
  width:40%; height:1px; background:var(--mc, rgba(212,175,55,.2));
}
.cc-card:hover .cc-metric{
  border-color:rgba(212,175,55,.12); background:rgba(212,175,55,.03);
  transform:translateY(-1px);
}
.cc-metric-val{
  font-family:var(--ff-mono); font-size:1.05rem; font-weight:700;
  line-height:1; margin-bottom:5px;
}
.cc-metric-lbl{
  font-family:var(--ff-head); font-size:.58rem; color:var(--muted);
  text-transform:uppercase; letter-spacing:1px;
}

/* ── Metric bars ────────────────────────────────── */
.cc-metric-bars{
  display:flex; flex-direction:column; gap:10px; margin-bottom:22px;
  padding:16px 18px;
  background:rgba(255,255,255,.015); border:1px solid rgba(255,255,255,.05);
  border-radius:14px;
}
.cc-bar-row{display:flex;align-items:center;gap:12px}
.cc-bar-label{
  font-family:var(--ff-body); font-size:.72rem; color:var(--muted);
  width:80px; flex-shrink:0; text-align:right;
}
.cc-bar-track{
  flex:1; height:5px; background:rgba(255,255,255,.06);
  border-radius:99px; overflow:hidden;
}
.cc-bar-fill{
  height:100%; border-radius:99px; width:0;
  transition:width 1.2s cubic-bezier(.4,0,.2,1);
  background:var(--bf, linear-gradient(90deg,#d4af37,#facc15));
}
.cc-bar-val{
  font-family:var(--ff-mono); font-size:.7rem; font-weight:600;
  color:#e2e8f0; width:50px; flex-shrink:0;
}

/* ── Analysis toggle ────────────────────────────── */
.cc-analysis-toggle{
  display:flex; align-items:center; gap:9px;
  width:100%; background:rgba(255,255,255,.025);
  border:1px solid rgba(255,255,255,.06); border-radius:12px;
  padding:12px 16px; color:var(--muted);
  font-family:var(--ff-head); font-size:.8rem; font-weight:600;
  cursor:pointer; transition:all .25s; text-align:left; letter-spacing:.3px;
}
.cc-analysis-toggle:hover{
  background:rgba(212,175,55,.07); border-color:rgba(212,175,55,.22); color:var(--gold);
}
.cc-analysis-toggle.open{
  background:rgba(212,175,55,.06); border-color:rgba(212,175,55,.2); color:var(--gold);
}
.cc-analysis-toggle i.cc-tog-icon{transition:transform .35s cubic-bezier(.34,1.56,.64,1);font-size:.72rem}
.cc-analysis-toggle.open i.cc-tog-icon{transform:rotate(180deg)}

.cc-analysis-body{max-height:0;overflow:hidden;transition:max-height .45s ease}
.cc-analysis-body.open{max-height:320px}

.cc-analysis-text{padding:18px 16px 6px}
.cc-analysis-point{
  display:flex; align-items:flex-start; gap:10px;
  margin-bottom:10px; animation:ccFadeUp .4s ease both;
}
.cc-analysis-point i{font-size:.72rem;margin-top:4px;flex-shrink:0;color:var(--gold)}
.cc-analysis-point span{
  font-family:var(--ff-body); font-size:.86rem; color:#94a3b8; line-height:1.75;
}
.cc-analysis-point span strong{color:#e2e8f0;font-weight:600}

/* ── Bottom disclaimer ──────────────────────────── */
.cc-disclaimer{
  text-align:center; margin:52px auto 0; max-width:520px;
  color:var(--muted); font-family:var(--ff-body); font-size:.8rem;
  line-height:1.75; animation:ccFadeUp .6s .7s ease both;
  padding:18px 24px;
  background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.05);
  border-radius:16px;
}
.cc-disclaimer i{color:var(--gold);margin-right:7px}

/* ── Mobile ─────────────────────────────────────── */
@media(max-width:700px){
  .cc-main{padding:24px 16px 50px}
  .cc-card-inner{padding:22px 18px 20px}
  .cc-metrics{grid-template-columns:repeat(2,1fr)}
  .cc-company{max-width:200px}
  .cc-hero{padding:28px 12px 36px}
  .cc-hero-meta{flex-direction:column}
  .cc-hero-meta-item{border-right:none;border-bottom:1px solid rgba(255,255,255,.06)}
  .cc-hero-meta-item:last-child{border-bottom:none}
}
</style>

<!-- Background layers -->
<div class="cc-bg"></div>
<div class="cc-orb cc-orb-1"></div>
<div class="cc-orb cc-orb-2"></div>

<script>
/* Floating particles */
(function(){
  const colors=['rgba(212,175,55,.6)','rgba(250,204,21,.4)','rgba(212,175,55,.3)','rgba(255,255,255,.15)'];
  for(let i=0;i<24;i++){
    const p=document.createElement('div');
    p.className='cc-particle';
    const size=Math.random()*3+1;
    p.style.cssText=`left:${Math.random()*100}%;width:${size}px;height:${size}px;background:${colors[i%colors.length]};animation-duration:${Math.random()*16+10}s;animation-delay:${Math.random()*12}s`;
    document.body.appendChild(p);
  }
})();
</script>

<div class="cc-wrapper">

  <?php include 'includes/sidebar.php'; ?>

  <div class="cc-main">

    <!-- Hero -->
    <div class="cc-hero">
      <div class="cc-hero-crown"><i class="fas fa-crown"></i></div>
      <div class="cc-hero-eyebrow"><i class="fas fa-wand-magic-sparkles"></i> AI-Powered Selection</div>
      <h1 class="cc-hero-title">Consultant's Choice</h1>
      <p class="cc-hero-sub">
        WiseInvest Screener Data အပေါ် အခြေခံ၍ Altman Z-Score, Piotroski Score, ROE, ROIC
        တို့ဖြင့် အရည်အသွေးအမြင့်ဆုံး ကုမ္ပဏီများကို အလိုအလျောက် ရွေးချယ်ထားသည်
      </p>
      <div class="cc-hero-meta">
        <div class="cc-hero-meta-item">
          <strong><?= count($topStocks) ?></strong>
          <span>Top Picks</span>
        </div>
        <div class="cc-hero-meta-item">
          <strong>4</strong>
          <span>Criteria</span>
        </div>
        <div class="cc-hero-meta-item">
          <strong>100</strong>
          <span>Max Score</span>
        </div>
        <div class="cc-hero-meta-item">
          <strong>AI</strong>
          <span>Powered</span>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="cc-divider">
      <div class="cc-divider-line"></div>
      <div class="cc-divider-chip">
        <i class="fas fa-trophy"></i>
        Top <?= count($topStocks) ?> Quality Picks
      </div>
      <div class="cc-divider-line"></div>
    </div>

    <!-- Cards -->
    <div class="cc-stack">

      <?php foreach($topStocks as $rank => $stock):
        $r      = $rank + 1;
        $cfg    = $rankConfig[$r] ?? $rankConfig[5];
        $score  = $stock["score"];
        $altman = $stock["Altman Z-Score"];
        $pio    = $stock["Piotroski F Score"];
        $roe    = $stock["Return on Equity"];
        $roic   = $stock["ROIC"];
        $debt   = $stock["Debt / Equity"];
        $margin = $stock["Net Margin"];

        /* SVG arc: r=34, circumference ≈ 214 */
        $arcCircum = 214;
        $arcOffset = round($arcCircum - ($score/100)*$arcCircum, 1);

        /* Bar widths */
        $altmanPct  = min(100, floatval($altman) / 10 * 100);
        $pioPct     = min(100, intval($pio) / 9 * 100);
        $roePct     = min(100, max(0, floatval(str_replace('%','',$roe)) / 60 * 100));
        $roicPct    = min(100, max(0, floatval(str_replace('%','',$roic)) / 40 * 100));
      ?>

      <div class="cc-card"
           style="box-shadow:0 0 0 1px <?= $cfg['color'] ?>20;"
           onmouseenter="this.style.boxShadow='0 24px 70px <?= $cfg['glow'] ?>,0 0 0 1px <?= $cfg['color'] ?>44'"
           onmouseleave="this.style.boxShadow='0 0 0 1px <?= $cfg['color'] ?>20'">

        <!-- Top accent bar -->
        <div class="cc-card-bar"
             style="background:linear-gradient(90deg,<?= $cfg['grad'] ?>,transparent)"></div>

        <div class="cc-card-inner">

          <!-- Head -->
          <div class="cc-card-head">

            <!-- Medal -->
            <div class="cc-rank-medal">
              <div class="cc-medal-icon"
                   style="color:<?= $cfg['color'] ?>;border-color:<?= $cfg['color'] ?>55;background:<?= $cfg['color'] ?>14;">
                <i class="<?= $cfg['icon'] ?>"></i>
              </div>
              <div class="cc-medal-num" style="color:<?= $cfg['color'] ?>">#<?= $r ?></div>
            </div>

            <!-- Identity -->
            <div class="cc-identity">
              <div class="cc-ticker-row">
                <span class="cc-ticker"><?= htmlspecialchars($stock["Ticker"]) ?></span>
                <span class="cc-rank-badge"
                      style="color:<?= $cfg['color'] ?>;border-color:<?= $cfg['color'] ?>44;background:<?= $cfg['color'] ?>10;">
                  <i class="<?= $cfg['icon'] ?>" style="font-size:.55rem"></i>
                  <?= htmlspecialchars($cfg['label']) ?>
                </span>
              </div>
              <div class="cc-company"><?= htmlspecialchars($stock["Company"]) ?></div>
            </div>

            <!-- Score arc -->
            <div class="cc-score-display">
              <div class="cc-score-arc">
                <svg viewBox="0 0 80 80">
                  <circle class="arc-track" cx="40" cy="40" r="34"/>
                  <circle class="arc-fill" cx="40" cy="40" r="34"
                          style="stroke:<?= $cfg['ring'] ?>;--gc:<?= $cfg['ring'] ?>;animation:ccGlow 3s ease-in-out infinite"
                          data-offset="<?= $arcOffset ?>"/>
                </svg>
                <div class="cc-score-center">
                  <div class="cc-score-val" style="color:<?= $cfg['color'] ?>"><?= $score ?></div>
                  <div class="cc-score-lbl">/100</div>
                </div>
              </div>
            </div>

          </div>

          <!-- Metrics grid -->
          <div class="cc-metrics">
            <div class="cc-metric" style="--mc:rgba(52,211,153,.25)">
              <div class="cc-metric-val" style="color:#34d399"><?= htmlspecialchars($altman) ?></div>
              <div class="cc-metric-lbl">Altman Z</div>
            </div>
            <div class="cc-metric" style="--mc:rgba(96,165,250,.25)">
              <div class="cc-metric-val" style="color:#60a5fa"><?= htmlspecialchars($pio) ?></div>
              <div class="cc-metric-lbl">Piotroski</div>
            </div>
            <div class="cc-metric" style="--mc:rgba(212,175,55,.25)">
              <div class="cc-metric-val" style="color:var(--gold)"><?= htmlspecialchars($roe) ?></div>
              <div class="cc-metric-lbl">ROE</div>
            </div>
            <div class="cc-metric" style="--mc:rgba(167,139,250,.25)">
              <div class="cc-metric-val" style="color:#a78bfa"><?= htmlspecialchars($roic) ?></div>
              <div class="cc-metric-lbl">ROIC</div>
            </div>
          </div>

          <!-- Visual metric bars -->
          <div class="cc-metric-bars">
            <div class="cc-bar-row">
              <span class="cc-bar-label">Altman Z</span>
              <div class="cc-bar-track"><div class="cc-bar-fill" data-w="<?= $altmanPct ?>" style="--bf:linear-gradient(90deg,#34d399,#10b981)"></div></div>
              <span class="cc-bar-val"><?= $altman ?></span>
            </div>
            <div class="cc-bar-row">
              <span class="cc-bar-label">Piotroski</span>
              <div class="cc-bar-track"><div class="cc-bar-fill" data-w="<?= $pioPct ?>" style="--bf:linear-gradient(90deg,#60a5fa,#3b82f6)"></div></div>
              <span class="cc-bar-val"><?= $pio ?>/9</span>
            </div>
            <div class="cc-bar-row">
              <span class="cc-bar-label">ROE</span>
              <div class="cc-bar-track"><div class="cc-bar-fill" data-w="<?= $roePct ?>" style="--bf:linear-gradient(90deg,#d4af37,#facc15)"></div></div>
              <span class="cc-bar-val"><?= $roe ?></span>
            </div>
            <div class="cc-bar-row">
              <span class="cc-bar-label">ROIC</span>
              <div class="cc-bar-track"><div class="cc-bar-fill" data-w="<?= $roicPct ?>" style="--bf:linear-gradient(90deg,#a78bfa,#8b5cf6)"></div></div>
              <span class="cc-bar-val"><?= $roic ?></span>
            </div>
          </div>

          <!-- Analysis toggle -->
          <button class="cc-analysis-toggle" onclick="ccToggle(this)">
            <i class="fas fa-file-lines" style="color:var(--gold);font-size:.78rem"></i>
            WiseInvest Analysis — ကြည့်မည်
            <i class="fas fa-chevron-down cc-tog-icon" style="margin-left:auto"></i>
          </button>

          <div class="cc-analysis-body">
            <div class="cc-analysis-text">
              <div class="cc-analysis-point">
                <i class="fas fa-check-circle"></i>
                <span>Altman Z-Score <strong><?= htmlspecialchars($altman) ?></strong>
                ဖြစ်သောကြောင့် ကုမ္ပဏီ၏ Financial Stability အလွန်ခိုင်မာပြီး Bankruptcy Risk မရှိသည်နှင့် အလားတူသည်</span>
              </div>
              <div class="cc-analysis-point">
                <i class="fas fa-check-circle"></i>
                <span>Piotroski F-Score <strong><?= htmlspecialchars($pio) ?></strong>
                ဖြစ်သောကြောင့် Financial Quality ကောင်းမွန်ပြီး Operating Efficiency မြင့်မားသည်</span>
              </div>
              <div class="cc-analysis-point">
                <i class="fas fa-check-circle"></i>
                <span>ROE <strong><?= htmlspecialchars($roe) ?></strong> နှင့်
                ROIC <strong><?= htmlspecialchars($roic) ?></strong>
                ရှိသောကြောင့် Capital Allocation ထိရောက်ပြီး Shareholder Value မြင့်မားစွာ ဖန်တီးနိုင်သည်</span>
              </div>
              <div class="cc-analysis-point">
                <i class="fas fa-star" style="color:var(--gold)"></i>
                <span>ဤအချက်များကြောင့် WiseInvest System မှ
                <strong style="color:var(--gold)">Quality Long-Term Investment</strong>
                အဖြစ် သတ်မှတ်သည်</span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <?php endforeach; ?>
    </div>

    <!-- Disclaimer -->
    <div class="cc-disclaimer">
      <i class="fas fa-info-circle"></i>
      ဤရွေးချယ်မှုများသည် quantitative data အပေါ်သာ အခြေခံသည်။
      Final investment decision မပြုမီ due diligence ပြုလုပ်ရန် အကြံပြုသည်။
    </div>

  </div>
</div>

<script>
function ccToggle(btn) {
  btn.classList.toggle('open');
  btn.nextElementSibling.classList.toggle('open');
}

/* ── Animate score arcs ── */
function animateArcs() {
  document.querySelectorAll('.arc-fill').forEach(arc => {
    const offset = parseFloat(arc.dataset.offset);
    setTimeout(() => { arc.style.strokeDashoffset = offset; }, 500);
  });
}

/* ── Animate metric bars ── */
function animateBars() {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if(!e.isIntersecting) return;
      e.target.querySelectorAll('.cc-bar-fill').forEach((bar, i) => {
        setTimeout(() => {
          bar.style.width = bar.dataset.w + '%';
        }, 200 + i * 120);
      });
      obs.unobserve(e.target);
    });
  }, {threshold: 0.3});
  document.querySelectorAll('.cc-card').forEach(c => obs.observe(c));
}

if(document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => { animateArcs(); animateBars(); });
} else {
  animateArcs(); animateBars();
}
</script>

<?php include 'includes/footer.php'; ?>
