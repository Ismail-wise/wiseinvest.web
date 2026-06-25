<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db.php';

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

/* ── PORTFOLIO ─────────────────────────────── */
$portfolio_query = $conn->query("SELECT shares,avg_price FROM user_portfolio WHERE user_id=$user_id");
$portfolio_value = 0;
while ($row = $portfolio_query->fetch_assoc())
    $portfolio_value += $row['shares'] * $row['avg_price'];

/* ── EMERGENCY FUND ─────────────────────────── */
$emergency_query = $conn->query("SELECT current_amount FROM emergency_fund WHERE user_id=$user_id");
$emergency_data  = $emergency_query ? $emergency_query->fetch_assoc() : [];
$emergency_fund  = $emergency_data['current_amount'] ?? 0;

/* ── DERIVED ────────────────────────────────── */
$net_worth          = $portfolio_value + $emergency_fund;
$monthly_investment = $portfolio_value * 0.05;
$freedom_goal       = 500000;
$freedom_pct        = $freedom_goal > 0 ? min(100, ($net_worth / $freedom_goal) * 100) : 0;
$freedom_remaining  = max(0, $freedom_goal - $net_worth);

/* ── MEMBERSHIP ──────────────────────────────── */
$mem_q    = $conn->query("SELECT membership_type, premium_expiry FROM users WHERE id=$user_id");
$mem_data = $mem_q ? $mem_q->fetch_assoc() : [];
$user_type   = $mem_data['membership_type'] ?? 'free';
$user_expiry = $mem_data['premium_expiry']  ?? null;
$_SESSION['user_type'] = $user_type; // keep session fresh

/* ── CONTENT COUNTS ─────────────────────────── */
$cnt_free_vids = (int)$conn->query("SELECT COUNT(*) FROM premium_content WHERE video_type='free'")->fetch_row()[0];
$cnt_prem_vids = (int)$conn->query("SELECT COUNT(*) FROM premium_content WHERE video_type='premium'")->fetch_row()[0];
$cnt_articles  = (int)$conn->query("SELECT COUNT(*) FROM articles")->fetch_row()[0];
$cnt_guides    = (int)$conn->query("SELECT COUNT(*) FROM guides")->fetch_row()[0];
$cnt_glossary  = ($conn->query("SHOW TABLES LIKE 'glossary'")->num_rows > 0)
                 ? (int)$conn->query("SELECT COUNT(*) FROM glossary")->fetch_row()[0] : 0;

/* ── TIER META ───────────────────────────────── */
$paid_types  = ['premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond'];
$is_paid     = in_array($user_type, $paid_types);
$tier_meta   = [
    'free'       => ['FREE',          'fas fa-user',      '#64748b','#f1f5f9',    'rgba(100,116,139,.15)'],
    'b1_premium' => ['BATCH-1',       'fas fa-crown',     '#d4af37','rgba(212,175,55,.15)','rgba(212,175,55,.08)'],
    'b2_premium' => ['BATCH-2',       'fas fa-crown',     '#3b82f6','rgba(59,130,246,.15)','rgba(59,130,246,.08)'],
    'b3_premium' => ['BATCH-3',       'fas fa-crown',     '#10b981','rgba(16,185,129,.15)','rgba(16,185,129,.08)'],
    'b4_premium' => ['BATCH-4',       'fas fa-crown',     '#f59e0b','rgba(245,158,11,.15)','rgba(245,158,11,.08)'],
    'b5_premium' => ['BATCH-5',       'fas fa-crown',     '#ef4444','rgba(239,68,68,.15)', 'rgba(239,68,68,.08)'],
    'diamond'    => ['DIAMOND',       'fas fa-gem',       '#a78bfa','rgba(167,139,250,.2)','rgba(167,139,250,.09)'],
    'premium'    => ['PREMIUM',       'fas fa-crown',     '#d4af37','rgba(212,175,55,.15)','rgba(212,175,55,.08)'],
];
[$tier_lbl,$tier_icon,$tier_color,$tier_bg,$tier_bg2] = $tier_meta[$user_type] ?? $tier_meta['free'];

/* ── ACCESSIBLE VIDEOS ───────────────────────── */
$accessible_vids = $cnt_free_vids; // always has free
if ($user_type === 'diamond' || $user_type === 'premium') {
    $accessible_vids += $cnt_prem_vids;
} elseif ($is_paid && $user_type !== 'free') {
    $bk = str_replace('_premium', '', $user_type); // b1, b2 ...
    $bv = (int)$conn->query("SELECT COUNT(*) FROM premium_content WHERE video_type='premium' AND (batch='$bk' OR batch IS NULL OR batch='')")->fetch_row()[0];
    $accessible_vids += $bv;
}

include 'includes/header.php';
?>
<style>
/* ═══════════════════════════════════════════════════════
   USER DASHBOARD — WISE INVEST PREMIUM DARK
   ═══════════════════════════════════════════════════════ */

/* ── Keyframes ── */
@keyframes dUp   { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:none} }
@keyframes dIn   { from{opacity:0;transform:scale(.94) translateY(14px)} to{opacity:1;transform:none} }
@keyframes dOrb  { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(18px,-14px) scale(1.08)} }
@keyframes dShim { from{background-position:-300% center} to{background-position:300% center} }
@keyframes dPulse{ 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.6)} }
@keyframes dRing { 0%{transform:scale(1);opacity:.7} 100%{transform:scale(2.2);opacity:0} }
@keyframes dSpin { to{transform:rotate(360deg)} }
@keyframes dBar  { from{width:0} }

/* ── Root ── */
:root {
  --bg:    #020617;
  --card:  rgba(10,18,38,.82);
  --bdr:   rgba(255,255,255,.07);
  --gold:  #d4af37;
  --gold2: #f5d679;
  --cyan:  #38bdf8;
  --vio:   #a78bfa;
  --grn:   #10b981;
  --red:   #ef4444;
  --muted: rgba(148,163,184,.75);
  --ink:   #f1f5f9;
  --tc:    <?= $tier_color ?>;
}
body { background:var(--bg); color:var(--ink); font-family:'Padauk','Segoe UI',sans-serif; margin:0; }

/* ── PARTICLE BG ── */
.d-bg { position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden; }
.d-bg-grad { position:absolute;inset:0;
  background:radial-gradient(ellipse 900px 600px at 5% 10%,rgba(212,175,55,.06),transparent 65%),
             radial-gradient(ellipse 700px 500px at 95% 10%,rgba(167,139,250,.05),transparent 65%),
             radial-gradient(ellipse 500px 400px at 50% 95%,rgba(56,189,248,.04),transparent 70%),
             linear-gradient(180deg,#020617,#030c1e); }
.d-bg-grid { position:absolute;inset:0;
  background-image:linear-gradient(rgba(212,175,55,.03) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(212,175,55,.03) 1px,transparent 1px);
  background-size:64px 64px; }
#d-canvas { position:absolute;inset:0;opacity:.25; }
.d-orb { position:absolute;border-radius:50%;filter:blur(90px);pointer-events:none; }
.d-orb-1 { width:600px;height:600px;background:radial-gradient(circle,rgba(212,175,55,.09),transparent 70%);top:-200px;left:-180px;animation:dOrb 11s ease-in-out infinite; }
.d-orb-2 { width:450px;height:450px;background:radial-gradient(circle,rgba(167,139,250,.07),transparent 70%);top:-100px;right:-140px;animation:dOrb 14s ease-in-out infinite reverse; }
.d-orb-3 { width:300px;height:300px;background:radial-gradient(circle,rgba(56,189,248,.05),transparent 70%);bottom:-80px;left:40%;animation:dOrb 9s ease-in-out infinite; }

/* ── SHELL ── */
.d-shell { position:relative;z-index:1;display:flex;gap:24px;padding:26px 26px 70px;min-height:100vh; }
.d-main  { flex:1;min-width:0; }

/* ── HERO ── */
.d-hero {
  position:relative;border-radius:22px;overflow:hidden;
  background:linear-gradient(145deg,#080f1e,#0c1628 60%,#0f1a35);
  border:1px solid rgba(212,175,55,.15);
  padding:36px 38px; margin-bottom:20px;
  box-shadow:0 4px 48px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.04);
  animation:dUp .6s ease both;
}
.d-hero-bg { position:absolute;inset:0;pointer-events:none; }
.d-hero-grid { position:absolute;inset:0;
  background-image:linear-gradient(rgba(212,175,55,.04) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(212,175,55,.04) 1px,transparent 1px);
  background-size:50px 50px; }
.d-hero-orb1 { position:absolute;width:400px;height:400px;border-radius:50%;
  background:radial-gradient(circle,rgba(212,175,55,.12),transparent 70%);
  top:-180px;left:-100px;filter:blur(70px);animation:dOrb 9s ease-in-out infinite; }
.d-hero-orb2 { position:absolute;width:280px;height:280px;border-radius:50%;
  background:radial-gradient(circle,var(--tc),.1,transparent 70%);
  top:-60px;right:-80px;filter:blur(60px);animation:dOrb 12s ease-in-out infinite reverse; }

.d-hero-inner { position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap; }

/* Left side */
.d-hero-l { flex:1;min-width:220px; }
.d-greet-badge {
  display:inline-flex;align-items:center;gap:7px;
  background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.25);
  padding:4px 14px;border-radius:99px;
  font-size:.68rem;font-weight:800;color:var(--gold);
  text-transform:uppercase;letter-spacing:2px;margin-bottom:12px;
  animation:dUp .5s .05s ease both;
}
.d-greet-dot { width:6px;height:6px;border-radius:50%;background:var(--gold);box-shadow:0 0 8px var(--gold);animation:dPulse 1.8s infinite; }
.d-hero-name {
  font-size:clamp(1.8rem,3.5vw,2.6rem);font-weight:900;margin:0 0 6px;line-height:1.1;
  background:linear-gradient(135deg,#f1f5f9 30%,var(--gold) 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  display:flex;align-items:center;gap:14px;
  animation:dUp .5s .1s ease both;
}
.d-av {
  width:52px;height:52px;flex-shrink:0;border-radius:50%;
  background:linear-gradient(135deg,var(--gold),var(--gold2));
  display:flex;align-items:center;justify-content:center;
  font-size:1.3rem;font-weight:900;color:#020617;
  -webkit-text-fill-color:#020617;
  box-shadow:0 0 0 3px rgba(212,175,55,.25),0 0 24px rgba(212,175,55,.3);
}
.d-hero-sub { color:var(--muted);font-size:.84rem;margin:0 0 16px;animation:dUp .5s .15s ease both; }

/* Tier badge */
.d-tier-badge {
  display:inline-flex;align-items:center;gap:9px;
  padding:8px 16px;border-radius:12px;
  background:<?= $tier_bg ?>;border:1px solid <?= $tier_color ?>44;
  animation:dUp .5s .2s ease both;
}
.d-tier-icon { width:30px;height:30px;border-radius:8px;background:<?= $tier_bg2 ?>;
  display:flex;align-items:center;justify-content:center;font-size:.85rem;color:<?= $tier_color ?>; }
.d-tier-text { font-size:.78rem; }
.d-tier-lbl { font-weight:900;color:<?= $tier_color ?>;font-size:.85rem;letter-spacing:.5px; }
.d-tier-sub { color:var(--muted);font-size:.7rem; }

/* Right side: clock */
.d-hero-r { text-align:right;animation:dUp .5s .12s ease both;flex-shrink:0; }
.d-clock {
  font-size:clamp(2.2rem,4.5vw,3.6rem);font-weight:900;line-height:1;
  font-family:'Courier New',monospace;font-variant-numeric:tabular-nums;
  background:linear-gradient(135deg,var(--gold),var(--gold2));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  letter-spacing:.04em;
}
.d-date { font-size:.78rem;color:var(--muted);margin-top:5px; }
.d-hdr-btns { display:flex;gap:10px;margin-top:14px;justify-content:flex-end;flex-wrap:wrap; }
.d-btn {
  padding:9px 17px;border-radius:10px;font-weight:700;font-size:.82rem;
  text-decoration:none;display:inline-flex;align-items:center;gap:7px;
  transition:all .24s;border:none;cursor:pointer;font-family:inherit;
}
.d-btn-ghost { background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:var(--ink); }
.d-btn-ghost:hover { background:rgba(212,175,55,.1);border-color:rgba(212,175,55,.4);color:var(--gold);transform:translateY(-2px); }
.d-btn-gold  { background:linear-gradient(135deg,var(--gold),var(--gold2));color:#020617;box-shadow:0 6px 20px rgba(212,175,55,.3); }
.d-btn-gold:hover  { transform:translateY(-2px);box-shadow:0 10px 30px rgba(212,175,55,.5); }

/* ── KPI GRID ── */
.d-kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px; }
.d-kpi {
  background:var(--card);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
  border:1px solid var(--bdr);border-radius:18px;padding:22px 20px;
  position:relative;overflow:hidden;transition:all .28s;
  animation:dIn .6s ease both;
}
.d-kpi:hover { transform:translateY(-5px);border-color:var(--kc,var(--bdr));
  box-shadow:0 20px 55px rgba(0,0,0,.55),0 0 0 1px var(--kc,transparent); }
.d-kpi::before { content:"";position:absolute;top:0;left:0;right:0;height:3px;
  background:var(--kgrad,linear-gradient(90deg,var(--gold),var(--cyan)));opacity:.75; }
.d-kpi::after  { content:"";position:absolute;bottom:-40px;right:-40px;width:130px;height:130px;
  border-radius:50%;background:radial-gradient(circle,var(--kglow,rgba(212,175,55,.06)),transparent 70%);pointer-events:none; }
.kpi-row { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px; }
.kpi-lbl { font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted); }
.kpi-ico { width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem; }
.kpi-val { font-size:1.85rem;font-weight:900;letter-spacing:-.5px;color:var(--ink);margin-bottom:10px;font-variant-numeric:tabular-nums; }
.kpi-note { font-size:.7rem;font-weight:600;display:flex;align-items:center;gap:5px;color:var(--muted); }
.kpi-note.up  { color:var(--grn); }
.kpi-note.down{ color:var(--red); }

/* ── MAIN GRID ── */
.d-grid { display:grid;grid-template-columns:1fr 1fr;gap:18px; }
.d-card {
  background:var(--card);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
  border:1px solid var(--bdr);border-radius:18px;padding:24px;
  position:relative;overflow:hidden;animation:dIn .6s ease both;
  transition:border-color .25s;
}
.d-card:hover { border-color:rgba(212,175,55,.18); }
.d-card::before { content:"";position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.5),rgba(167,139,250,.5),transparent);opacity:.45; }
.d-card.span2 { grid-column:span 2; }
.ch { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px; }
.ch-l h3 { margin:0 0 4px;font-size:.95rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:8px; }
.ch-l h3 i { color:var(--gold);font-size:.85rem; }
.ch-l p  { margin:0;font-size:.71rem;color:var(--muted); }
.ch-badge { font-size:.62rem;font-weight:800;padding:3px 10px;border-radius:99px;
  letter-spacing:1px;text-transform:uppercase;display:inline-flex;align-items:center;gap:5px; }
.ch-badge.live { background:rgba(16,185,129,.12);color:var(--grn);border:1px solid rgba(16,185,129,.3); }
.ch-badge.live::before { content:"";width:5px;height:5px;background:var(--grn);border-radius:50%;animation:dPulse 1.5s infinite; }
.ch-link { font-size:.78rem;color:var(--gold);text-decoration:none;font-weight:700;transition:color .2s; }
.ch-link:hover { color:var(--gold2); }

/* ── MEMBERSHIP CARD ── */
.mc-hero {
  border-radius:14px;padding:22px;margin-bottom:16px;position:relative;overflow:hidden;
  background:linear-gradient(135deg,<?= $tier_bg ?>,<?= $tier_bg2 ?>);
  border:1px solid <?= $tier_color ?>33;
}
.mc-hero::before { content:"";position:absolute;inset:0;
  background:radial-gradient(ellipse 300px 200px at 80% 50%,<?= $tier_color ?>20,transparent); }
.mc-icon-ring { position:relative;width:64px;height:64px;margin-bottom:14px; }
.mc-ring-svg  { position:absolute;inset:0;animation:dSpin 12s linear infinite; }
.mc-icon      { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
  font-size:1.5rem;color:<?= $tier_color ?>; }
.mc-tier-name { font-size:1.4rem;font-weight:900;color:<?= $tier_color ?>;letter-spacing:.5px;position:relative;z-index:1; }
.mc-tier-lbl  { font-size:.72rem;color:var(--muted);position:relative;z-index:1;margin-top:2px; }
.mc-expiry    { margin-top:10px;font-size:.78rem;color:var(--muted);position:relative;z-index:1; }
.mc-expiry strong { color:var(--ink); }

.mc-access-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.mc-access-item {
  background:rgba(255,255,255,.03);border:1px solid var(--bdr);border-radius:10px;
  padding:12px 14px;display:flex;align-items:center;gap:10px;
  transition:all .22s;cursor:default;
}
.mc-access-item:hover { background:rgba(255,255,255,.05);border-color:rgba(212,175,55,.2); }
.mc-a-icon { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0; }
.mc-a-num  { font-size:1rem;font-weight:800;color:var(--ink);line-height:1; }
.mc-a-lbl  { font-size:.67rem;color:var(--muted);font-weight:600; }

/* ── FREEDOM RING ── */
.fr-body { display:flex;align-items:center;gap:22px;margin-bottom:18px; }
.fr-ring  { position:relative;width:130px;height:130px;flex-shrink:0; }
.fr-svg   { width:130px;height:130px;transform:rotate(-90deg); }
.fr-bg    { fill:none;stroke:rgba(255,255,255,.06);stroke-width:10; }
.fr-arc   { fill:none;stroke:url(#frGrad);stroke-width:10;stroke-linecap:round;
            stroke-dasharray:314;stroke-dashoffset:314;
            transition:stroke-dashoffset 1.6s cubic-bezier(.4,0,.2,1); }
.fr-ctr   { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center; }
.fr-ctr strong { display:block;font-size:1.3rem;font-weight:900;color:var(--gold); }
.fr-ctr small  { font-size:.6rem;text-transform:uppercase;letter-spacing:.8px;color:var(--muted); }
.fr-stats { flex:1;display:flex;flex-direction:column;gap:11px; }
.fsr      { display:flex;justify-content:space-between;align-items:center;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.05); }
.fsr:last-child { border-bottom:none;padding-bottom:0; }
.fsr-l    { font-size:.69rem;text-transform:uppercase;letter-spacing:.8px;color:var(--muted); }
.fsr-v    { font-size:.88rem;font-weight:700; }
.fsr-v.g  { color:var(--grn); }
.fsr-v.r  { color:var(--red); }
.fr-bar   { height:7px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;position:relative; }
.fr-fill  { height:100%;width:0;background:linear-gradient(90deg,var(--gold),var(--vio));
            border-radius:99px;transition:width 1.6s ease;box-shadow:0 0 10px rgba(212,175,55,.4); }
.fr-ms    { position:absolute;top:0;bottom:0;width:1px;background:rgba(255,255,255,.18); }
.fr-ms span { position:absolute;top:-18px;left:50%;transform:translateX(-50%);font-size:.56rem;color:var(--muted);white-space:nowrap; }

/* ── STOCKS ── */
.d-stocks { list-style:none;padding:0;margin:0; }
.d-stocks li { display:flex;justify-content:space-between;align-items:center;
  padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);transition:padding .2s; }
.d-stocks li:last-child { border-bottom:none; }
.d-stocks li:hover { padding-left:5px; }
.s-info { display:flex;align-items:center;gap:10px; }
.s-tick { font-size:.78rem;font-weight:800;color:var(--ink);background:rgba(255,255,255,.05);
          padding:3px 8px;border-radius:6px;min-width:46px;text-align:center;letter-spacing:.5px; }
.s-name { font-size:.76rem;color:var(--muted); }
.s-chg  { font-size:.8rem;font-weight:700;display:flex;align-items:center;gap:3px; }
.s-chg.up   { color:var(--grn); }
.s-chg.down { color:var(--red); }

/* ── ALLOCATION ── */
.alloc-wrap { position:relative;display:flex;flex-direction:column;align-items:center; }
.alloc-ctr  { position:absolute;top:46%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none; }
.alloc-tot  { display:block;font-size:1rem;font-weight:800;color:var(--ink); }
.alloc-lbl  { font-size:.6rem;text-transform:uppercase;letter-spacing:1px;color:var(--muted); }

/* ── QUICK ACTIONS ── */
.qa-grid { display:grid;grid-template-columns:repeat(6,1fr);gap:12px; }
.qa-link {
  display:flex;flex-direction:column;align-items:center;gap:9px;padding:18px 10px;
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
  border-radius:14px;text-decoration:none;color:var(--muted);
  font-size:.74rem;font-weight:600;text-align:center;
  transition:all .25s;
}
.qa-link i { font-size:1.3rem;color:var(--gold);transition:all .25s; }
.qa-link:hover { background:rgba(212,175,55,.07);border-color:rgba(212,175,55,.35);
  color:var(--ink);transform:translateY(-5px);box-shadow:0 14px 32px rgba(0,0,0,.4); }
.qa-link:hover i { transform:scale(1.2);filter:drop-shadow(0 0 7px var(--gold)); }

/* ── RESPONSIVE ── */
@media(max-width:1280px) { .d-kpi-grid{grid-template-columns:repeat(2,1fr)} }
@media(max-width:960px)  {
  .d-shell{flex-direction:column;padding:14px 14px 60px}
  .d-kpi-grid{grid-template-columns:1fr 1fr}
  .d-grid{grid-template-columns:1fr}
  .d-card.span2{grid-column:span 1}
  .d-hero-inner{flex-direction:column;gap:16px}
  .d-hero-r{text-align:left}
  .d-hdr-btns{justify-content:flex-start}
  .qa-grid{grid-template-columns:repeat(3,1fr)}
  .mc-access-grid{grid-template-columns:1fr}
}
@media(max-width:560px)  {
  .d-kpi-grid{grid-template-columns:1fr}
  .qa-grid{grid-template-columns:repeat(2,1fr)}
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- ════ PARTICLE BACKGROUND ════ -->
<div class="d-bg">
  <div class="d-bg-grad"></div>
  <div class="d-bg-grid"></div>
  <canvas id="d-canvas"></canvas>
  <div class="d-orb d-orb-1"></div>
  <div class="d-orb d-orb-2"></div>
  <div class="d-orb d-orb-3"></div>
</div>

<div class="d-shell">

  <!-- ════ SIDEBAR ════ -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- ════ MAIN ════ -->
  <main class="d-main">

    <!-- ── HERO ── -->
    <div class="d-hero">
      <div class="d-hero-bg">
        <div class="d-hero-grid"></div>
        <div class="d-hero-orb1"></div>
        <div class="d-hero-orb2"></div>
      </div>
      <div class="d-hero-inner">
        <div class="d-hero-l">
          <div class="d-greet-badge"><span class="d-greet-dot"></span><span id="dGreet">Good Morning</span></div>
          <h1 class="d-hero-name">
            <div class="d-av"><?= strtoupper(substr($username,0,1)) ?></div>
            <?= htmlspecialchars($username) ?>
          </h1>
          <p class="d-hero-sub">Your financial command center &nbsp;·&nbsp; <span id="dDate"></span></p>

          <div class="d-tier-badge">
            <div class="d-tier-icon"><i class="<?= $tier_icon ?>"></i></div>
            <div class="d-tier-text">
              <div class="d-tier-lbl"><?= $tier_lbl ?> MEMBER</div>
              <div class="d-tier-sub">
                <?php if ($user_expiry): ?>Expires <?= date('d M Y', strtotime($user_expiry)) ?>
                <?php elseif ($is_paid): ?>Lifetime Access
                <?php else: ?>Upgrade for premium access
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="d-hero-r">
          <div class="d-clock" id="dClock">00:00:00</div>
          <div class="d-date" id="dClockDate"></div>
          <div class="d-hdr-btns">
            <a href="calculators.php" class="d-btn d-btn-ghost"><i class="fas fa-calculator"></i> Calculators</a>
            <?php if ($is_paid): ?>
            <a href="premium-videos.php" class="d-btn d-btn-gold"><i class="fas fa-play-circle"></i> My Videos</a>
            <?php else: ?>
            <a href="services.php" class="d-btn d-btn-gold"><i class="fas fa-crown"></i> Go Premium</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ── KPI CARDS ── -->
    <div class="d-kpi-grid">

      <div class="d-kpi" style="--kc:rgba(212,175,55,.35);--kgrad:linear-gradient(90deg,var(--gold),var(--gold2));--kglow:rgba(212,175,55,.07);animation-delay:.05s">
        <div class="kpi-row">
          <span class="kpi-lbl">Total Net Worth</span>
          <span class="kpi-ico" style="background:rgba(212,175,55,.12);color:var(--gold)"><i class="fas fa-crown"></i></span>
        </div>
        <div class="kpi-val" data-target="<?= $net_worth ?>" data-pfx="$">$0.00</div>
        <div class="kpi-note up"><i class="fas fa-arrow-trend-up"></i> Portfolio + Emergency Fund</div>
      </div>

      <div class="d-kpi" style="--kc:rgba(56,189,248,.3);--kgrad:linear-gradient(90deg,var(--cyan),var(--vio));--kglow:rgba(56,189,248,.06);animation-delay:.1s">
        <div class="kpi-row">
          <span class="kpi-lbl">Portfolio Value</span>
          <span class="kpi-ico" style="background:rgba(56,189,248,.12);color:var(--cyan)"><i class="fas fa-chart-line"></i></span>
        </div>
        <div class="kpi-val" data-target="<?= $portfolio_value ?>" data-pfx="$">$0.00</div>
        <div class="kpi-note up"><i class="fas fa-arrow-trend-up"></i> Invested assets</div>
      </div>

      <div class="d-kpi" style="--kc:rgba(16,185,129,.3);--kgrad:linear-gradient(90deg,var(--grn),var(--cyan));--kglow:rgba(16,185,129,.06);animation-delay:.15s">
        <div class="kpi-row">
          <span class="kpi-lbl">Emergency Fund</span>
          <span class="kpi-ico" style="background:rgba(16,185,129,.12);color:var(--grn)"><i class="fas fa-shield-alt"></i></span>
        </div>
        <div class="kpi-val" data-target="<?= $emergency_fund ?>" data-pfx="$">$0.00</div>
        <div class="kpi-note"><i class="fas fa-lock"></i> Safety net reserve</div>
      </div>

      <div class="d-kpi" style="--kc:rgba(167,139,250,.3);--kgrad:linear-gradient(90deg,var(--vio),var(--gold));--kglow:rgba(167,139,250,.06);animation-delay:.2s">
        <div class="kpi-row">
          <span class="kpi-lbl">Monthly Investment</span>
          <span class="kpi-ico" style="background:rgba(167,139,250,.12);color:var(--vio)"><i class="fas fa-coins"></i></span>
        </div>
        <div class="kpi-val" data-target="<?= $monthly_investment ?>" data-pfx="$">$0.00</div>
        <div class="kpi-note up"><i class="fas fa-percentage"></i> ~5% of portfolio est.</div>
      </div>

    </div><!-- /kpi-grid -->

    <!-- ── MAIN GRID ── -->
    <div class="d-grid">

      <!-- Portfolio Growth (full width) -->
      <div class="d-card span2" style="animation-delay:.25s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-chart-area"></i> Portfolio Growth</h3>
            <p>5-month performance trajectory</p>
          </div>
          <span class="ch-badge live">Live</span>
        </div>
        <canvas id="portfolioChart" height="72"></canvas>
      </div>

      <!-- Asset Allocation -->
      <div class="d-card" style="animation-delay:.3s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-circle-half-stroke"></i> Asset Allocation</h3>
            <p>Portfolio vs Emergency Fund</p>
          </div>
        </div>
        <div class="alloc-wrap">
          <canvas id="allocationChart" height="185"></canvas>
          <div class="alloc-ctr">
            <span class="alloc-tot">$<?= number_format($net_worth, 0) ?></span>
            <span class="alloc-lbl">Total</span>
          </div>
        </div>
      </div>

      <!-- Membership Status -->
      <div class="d-card" style="animation-delay:.3s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="<?= $tier_icon ?>"></i> Membership Status</h3>
            <p>Your current access level</p>
          </div>
          <?php if (!$is_paid): ?>
          <a href="services.php" class="ch-link">Upgrade →</a>
          <?php endif; ?>
        </div>

        <div class="mc-hero">
          <div class="mc-icon-ring">
            <svg class="mc-ring-svg" viewBox="0 0 64 64">
              <circle cx="32" cy="32" r="28" fill="none" stroke="<?= $tier_color ?>33" stroke-width="2" stroke-dasharray="4 4"/>
            </svg>
            <div class="mc-icon"><i class="<?= $tier_icon ?>"></i></div>
          </div>
          <div class="mc-tier-name"><?= $tier_lbl ?></div>
          <div class="mc-tier-lbl">
            <?php if ($user_type === 'diamond'): ?>All Batches · Full Access
            <?php elseif ($user_type === 'free'): ?>Basic Account · Free Content Only
            <?php else: ?>SIM <?= strtoupper(str_replace('_premium','', $user_type)) ?> · Batch Access
            <?php endif; ?>
          </div>
          <div class="mc-expiry">Validity:
            <strong>
              <?php if ($user_expiry): echo date('d M Y', strtotime($user_expiry));
              elseif ($is_paid): echo 'Lifetime';
              else: echo 'Free Plan'; endif; ?>
            </strong>
          </div>
        </div>

        <div class="mc-access-grid">
          <div class="mc-access-item">
            <div class="mc-a-icon" style="background:rgba(212,175,55,.12);color:var(--gold)"><i class="fas fa-play-circle"></i></div>
            <div><div class="mc-a-num" data-target="<?= $accessible_vids ?>">0</div><div class="mc-a-lbl">Videos Access</div></div>
          </div>
          <div class="mc-access-item">
            <div class="mc-a-icon" style="background:rgba(16,185,129,.1);color:var(--grn)"><i class="fas fa-file-pdf"></i></div>
            <div><div class="mc-a-num" data-target="<?= $cnt_guides ?>">0</div><div class="mc-a-lbl">Guides</div></div>
          </div>
          <div class="mc-access-item">
            <div class="mc-a-icon" style="background:rgba(56,189,248,.1);color:var(--cyan)"><i class="fas fa-newspaper"></i></div>
            <div><div class="mc-a-num" data-target="<?= $cnt_articles ?>">0</div><div class="mc-a-lbl">Articles</div></div>
          </div>
          <div class="mc-access-item">
            <div class="mc-a-icon" style="background:rgba(167,139,250,.1);color:var(--vio)"><i class="fas fa-book-open"></i></div>
            <div><div class="mc-a-num" data-target="<?= $cnt_glossary ?>">0</div><div class="mc-a-lbl">Glossary Terms</div></div>
          </div>
        </div>
      </div>

      <!-- Financial Freedom -->
      <div class="d-card" style="animation-delay:.35s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-flag"></i> Financial Freedom</h3>
            <p>Goal: $500,000 &nbsp;·&nbsp; 4% SWR</p>
          </div>
        </div>
        <div class="fr-body">
          <div class="fr-ring">
            <svg class="fr-svg" viewBox="0 0 120 120">
              <defs>
                <linearGradient id="frGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%"   stop-color="#d4af37"/>
                  <stop offset="100%" stop-color="#a78bfa"/>
                </linearGradient>
              </defs>
              <circle class="fr-bg"  cx="60" cy="60" r="50"/>
              <circle class="fr-arc" cx="60" cy="60" r="50" id="frArc"/>
            </svg>
            <div class="fr-ctr">
              <strong id="frPct">0%</strong>
              <small>Complete</small>
            </div>
          </div>
          <div class="fr-stats">
            <div class="fsr"><span class="fsr-l">Net Worth</span><span class="fsr-v g">$<?= number_format($net_worth, 0) ?></span></div>
            <div class="fsr"><span class="fsr-l">Freedom Goal</span><span class="fsr-v">$500,000</span></div>
            <div class="fsr"><span class="fsr-l">Remaining</span><span class="fsr-v r">$<?= number_format($freedom_remaining, 0) ?></span></div>
          </div>
        </div>
        <div class="fr-bar">
          <div class="fr-fill" id="frFill"></div>
          <div class="fr-ms" style="left:25%"><span>25%</span></div>
          <div class="fr-ms" style="left:50%"><span>50%</span></div>
          <div class="fr-ms" style="left:75%"><span>75%</span></div>
        </div>
      </div>

      <!-- Wise Stocks -->
      <div class="d-card" style="animation-delay:.35s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-star"></i> Wise Approved Stocks</h3>
            <p>Curated picks by our analysts</p>
          </div>
          <a href="stocks.php" class="ch-link">View All →</a>
        </div>
        <ul class="d-stocks">
          <li><div class="s-info"><span class="s-tick">AAPL</span><span class="s-name">Apple Inc.</span></div><span class="s-chg up">+2.4% <i class="fas fa-caret-up"></i></span></li>
          <li><div class="s-info"><span class="s-tick">MSFT</span><span class="s-name">Microsoft Corp.</span></div><span class="s-chg up">+1.8% <i class="fas fa-caret-up"></i></span></li>
          <li><div class="s-info"><span class="s-tick">SCHD</span><span class="s-name">Schwab Dividend</span></div><span class="s-chg up">+0.9% <i class="fas fa-caret-up"></i></span></li>
          <li><div class="s-info"><span class="s-tick">NVDA</span><span class="s-name">NVIDIA Corp.</span></div><span class="s-chg up">+3.1% <i class="fas fa-caret-up"></i></span></li>
          <li><div class="s-info"><span class="s-tick">VOO</span><span class="s-name">Vanguard S&amp;P 500</span></div><span class="s-chg down">-0.3% <i class="fas fa-caret-down"></i></span></li>
        </ul>
      </div>

      <!-- Quick Actions (full width) -->
      <div class="d-card span2" style="animation-delay:.4s">
        <div class="ch">
          <div class="ch-l"><h3><i class="fas fa-bolt"></i> Quick Actions</h3></div>
        </div>
        <div class="qa-grid">
          <a href="budgetplanner.php"    class="qa-link"><i class="fas fa-wallet"></i><span>Budget Planner</span></a>
          <a href="emergency-fund.php"   class="qa-link"><i class="fas fa-shield-alt"></i><span>Emergency Fund</span></a>
          <a href="actual-portfolio.php" class="qa-link"><i class="fas fa-briefcase"></i><span>My Portfolio</span></a>
          <a href="calculators.php"      class="qa-link"><i class="fas fa-calculator"></i><span>Calculators</span></a>
          <a href="<?= $is_paid ? 'premium-videos.php' : 'videos.php' ?>" class="qa-link">
            <i class="fas fa-play-circle"></i><span><?= $is_paid ? 'Premium Videos' : 'Free Videos' ?></span>
          </a>
          <a href="community.php"        class="qa-link"><i class="fas fa-users"></i><span>Community</span></a>
        </div>
      </div>

    </div><!-- /d-grid -->
  </main>
</div><!-- /d-shell -->

<script>
/* ═══════════════════════════════════════════════════════
   DASHBOARD JAVASCRIPT
   ═══════════════════════════════════════════════════════ */

/* ── Live clock + greeting ── */
function tickClock() {
    var now  = new Date();
    var pad  = function(n){ return String(n).padStart(2,'0'); };
    var h=now.getHours(), m=now.getMinutes(), s=now.getSeconds();
    var ce = document.getElementById('dClock');
    var de = document.getElementById('dClockDate');
    var ge = document.getElementById('dGreet');
    if (ce) ce.textContent = pad(h)+':'+pad(m)+':'+pad(s);
    if (de) {
        var days  = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var months= ['January','February','March','April','May','June','July','August','September','October','November','December'];
        de.textContent = days[now.getDay()]+', '+now.getDate()+' '+months[now.getMonth()]+' '+now.getFullYear();
    }
    if (ge) ge.textContent = h<5?'Good Night':h<12?'Good Morning':h<17?'Good Afternoon':h<21?'Good Evening':'Good Night';
    /* also update short date */
    var dd = document.getElementById('dDate');
    if (dd) dd.textContent = new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
}
setInterval(tickClock, 1000);
tickClock();

/* ── Eased counter animation ── */
function countUp(el, target, duration, pfx, dec) {
    pfx = pfx||''; dec = dec||0;
    var t0 = performance.now();
    function step(now) {
        var p = Math.min(1, (now-t0)/duration);
        var e = 1 - Math.pow(1-p, 3);
        var v = e * target;
        el.textContent = pfx + v.toLocaleString('en-US',{minimumFractionDigits:dec,maximumFractionDigits:dec});
        if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

/* KPI vals */
document.querySelectorAll('.kpi-val[data-target]').forEach(function(el) {
    var t = parseFloat(el.getAttribute('data-target'));
    var p = el.getAttribute('data-pfx') || '';
    if (!isFinite(t)) return;
    countUp(el, t, 1400, p, 2);
});

/* Membership access counts */
document.querySelectorAll('.mc-a-num[data-target]').forEach(function(el) {
    countUp(el, parseInt(el.getAttribute('data-target'))||0, 1200);
});

/* ── Chart.js globals ── */
Chart.defaults.color       = 'rgba(148,163,184,.7)';
Chart.defaults.borderColor = 'rgba(255,255,255,.05)';
Chart.defaults.font.family = "'Segoe UI',sans-serif";
Chart.defaults.font.size   = 12;

/* ── Portfolio growth chart ── */
new Chart(document.getElementById('portfolioChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May'],
        datasets: [{
            label: 'Portfolio Value',
            data: [18000, 21000, 25000, 29000, <?= (int)$portfolio_value ?: 32000 ?>],
            borderColor: '#d4af37',
            backgroundColor: function(ctx) {
                var g = ctx.chart.ctx.createLinearGradient(0,0,0,280);
                g.addColorStop(0, 'rgba(212,175,55,.28)');
                g.addColorStop(1, 'rgba(212,175,55,.01)');
                return g;
            },
            tension: 0.45, borderWidth: 2.5, fill: true,
            pointBackgroundColor: '#d4af37',
            pointBorderColor: 'rgba(212,175,55,.3)',
            pointBorderWidth: 3, pointRadius: 5, pointHoverRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(7,10,20,.92)',
                borderColor: 'rgba(212,175,55,.3)', borderWidth: 1, padding: 12,
                callbacks: { label: function(ctx){ return ' $' + ctx.parsed.y.toLocaleString('en-US',{minimumFractionDigits:2}); }}
            }
        },
        scales: {
            x: { grid:{color:'rgba(255,255,255,.04)'}, ticks:{color:'rgba(148,163,184,.6)'} },
            y: { grid:{color:'rgba(255,255,255,.04)'}, ticks:{color:'rgba(148,163,184,.6)', callback: function(v){ return '$'+v.toLocaleString(); }} }
        }
    }
});

/* ── Asset allocation doughnut ── */
new Chart(document.getElementById('allocationChart'), {
    type: 'doughnut',
    data: {
        labels: ['Portfolio', 'Emergency Fund'],
        datasets: [{
            data: [<?= (int)$portfolio_value ?>, <?= (int)$emergency_fund ?>],
            backgroundColor: ['rgba(212,175,55,.7)', 'rgba(16,185,129,.65)'],
            borderColor:     ['#d4af37',            '#10b981'],
            borderWidth: 2, hoverOffset: 10,
        }]
    },
    options: {
        responsive: true, cutout: '70%',
        plugins: {
            legend: { position: 'bottom', labels: {padding:18, usePointStyle:true, pointStyleWidth:10, color:'rgba(148,163,184,.6)'} },
            tooltip: { backgroundColor:'rgba(7,10,20,.92)', borderColor:'rgba(212,175,55,.3)', borderWidth:1, padding:12,
                callbacks: { label: function(ctx){ return ' $'+ctx.parsed.toLocaleString(); } } }
        }
    }
});

/* ── Freedom ring & bar ── */
(function() {
    var pct  = <?= round($freedom_pct, 2) ?>;
    var circ = 2 * Math.PI * 50;
    setTimeout(function() {
        var pa = document.getElementById('frArc');
        var pf = document.getElementById('frFill');
        var pp = document.getElementById('frPct');
        if (pa) pa.style.strokeDashoffset = circ * (1 - pct/100);
        if (pf) pf.style.width = pct + '%';
        if (pp) pp.textContent = pct.toFixed(1) + '%';
    }, 500);
})();

/* ── Particle network canvas ── */
(function() {
    var cv = document.getElementById('d-canvas');
    if (!cv) return;
    var ctx = cv.getContext('2d');
    var pts = [];
    var W = 0, H = 0;
    function resize() {
        W = cv.width  = window.innerWidth;
        H = cv.height = window.innerHeight;
        while (pts.length < 60) pts.push({
            x: Math.random()*W, y: Math.random()*H,
            vx: (Math.random()-.5)*.3, vy: (Math.random()-.5)*.3,
            r: Math.random()*1.4+.4,
            hue: Math.random() < .5 ? 42 : 267
        });
    }
    function draw() {
        ctx.clearRect(0,0,W,H);
        for (var i=0; i<pts.length; i++) {
            for (var j=i+1; j<pts.length; j++) {
                var dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y, d=Math.sqrt(dx*dx+dy*dy);
                if (d < 130) {
                    ctx.beginPath();
                    ctx.strokeStyle = 'rgba(212,175,55,'+(0.1*(1-d/130))+')';
                    ctx.lineWidth = .5;
                    ctx.moveTo(pts[i].x, pts[i].y);
                    ctx.lineTo(pts[j].x, pts[j].y);
                    ctx.stroke();
                }
            }
        }
        for (var k=0; k<pts.length; k++) {
            var p = pts[k];
            p.x += p.vx; p.y += p.vy;
            if (p.x < 0) p.x = W; if (p.x > W) p.x = 0;
            if (p.y < 0) p.y = H; if (p.y > H) p.y = 0;
            ctx.beginPath();
            ctx.fillStyle = 'hsla('+p.hue+',80%,68%,.4)';
            ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
            ctx.fill();
        }
        requestAnimationFrame(draw);
    }
    window.addEventListener('resize', resize);
    resize();
    draw();
})();
</script>

<?php include 'includes/footer.php'; ?>
