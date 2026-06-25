<?php
session_start();
include_once 'includes/db.php';

/* ================= SAVE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_budget') {

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
echo json_encode(["status"=>"error","msg"=>"User not logged in"]);
exit();
}

$uid = intval($_SESSION['user_id']);
$month = $_POST['month'];
$goal = floatval($_POST['goal']);
$transactions = json_encode(json_decode($_POST['transactions']), JSON_UNESCAPED_UNICODE);

$conn->query("CREATE TABLE IF NOT EXISTS user_budgets(
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
month_year VARCHAR(7) NOT NULL,
savings_goal DECIMAL(10,2),
transactions JSON,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
UNIQUE KEY(user_id,month_year)
)");

$stmt = $conn->prepare("INSERT INTO user_budgets(user_id,month_year,savings_goal,transactions)
VALUES(?,?,?,?)
ON DUPLICATE KEY UPDATE
savings_goal=VALUES(savings_goal),
transactions=VALUES(transactions)");

$stmt->bind_param("isds",$uid,$month,$goal,$transactions);

if($stmt->execute()){
echo json_encode(["status"=>"success"]);
}else{
echo json_encode(["status"=>"error"]);
}

exit();
}

/* ================= LOAD ================= */
if(($_GET['action'] ?? '') === 'load_budget'){

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
echo json_encode(["status"=>"error"]);
exit();
}

$uid = intval($_SESSION['user_id']);
$month = $_GET['month'];

$stmt = $conn->prepare("SELECT savings_goal,transactions FROM user_budgets
WHERE user_id=? AND month_year=?");

$stmt->bind_param("is",$uid,$month);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){

echo json_encode([
"status"=>"found",
"goal"=>$row['savings_goal'],
"transactions"=>json_decode($row['transactions'])
]);

}else{

echo json_encode(["status"=>"empty"]);

}

exit();
}

include_once 'includes/header.php';

$_paid_types = ['premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond'];
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $_paid_types)) {
echo "<script>location.href='services.php';</script>";
exit();
}
?>

<style>
/* ═══════════════════════════════════════════════════
   BUDGET PLANNER — PREMIUM REDESIGN
   ═══════════════════════════════════════════════════ */
@keyframes bp-fadeUp  { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
@keyframes bp-fadeIn  { from{opacity:0} to{opacity:1} }
@keyframes bp-shimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes bp-orb     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.1) translate(10px,-8px)} }
@keyframes bp-grid    { from{background-position:0 0} to{background-position:60px 60px} }
@keyframes bp-bar     { from{width:0} to{width:var(--w,0%)} }
@keyframes bp-count   { from{transform:translateY(8px);opacity:0} to{transform:none;opacity:1} }
@keyframes bp-ring    { from{stroke-dashoffset:var(--full)} to{stroke-dashoffset:var(--target)} }
@keyframes bp-toast-in { from{opacity:0;transform:translateX(40px)} to{opacity:1;transform:translateX(0)} }
@keyframes bp-toast-out{ to{opacity:0;transform:translateX(40px)} }
@keyframes bp-row-in  { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:none} }

:root{ --gold:#d4af37; }
*{ box-sizing:border-box; }

body{ background:#020617; color:white; font-family:'Inter',Segoe UI,sans-serif; }

.dashboard-wrapper{ display:flex; gap:0; min-height:100vh; }

/* ── Main content ── */
.bp-main { flex:1; min-width:0; padding:28px 32px 60px; background:#020617; overflow-x:hidden; }
.bp-page { max-width:1200px; margin:0 auto; }

/* ── Gold text ── */
.bp-gold {
  background:linear-gradient(135deg,#d4af37 0%,#facc15 45%,#f59e0b 70%,#d4af37 100%);
  background-size:200% auto; -webkit-background-clip:text;
  -webkit-text-fill-color:transparent; background-clip:text;
  animation:bp-shimmer 4s linear infinite;
}

/* ══ HERO ═══════════════════════════════════════════ */
.bp-hero {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(145deg,#080f1e 0%,#040c18 100%);
  border:1px solid rgba(255,255,255,.08);
  padding:40px 44px 36px; margin-bottom:28px;
}
.bp-hero-grid {
  position:absolute; inset:0; pointer-events:none;
  background-image:linear-gradient(rgba(212,175,55,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(212,175,55,.025) 1px,transparent 1px);
  background-size:60px 60px; animation:bp-grid 18s linear infinite;
}
.bp-hero-orb-1{ position:absolute;width:480px;height:480px;border-radius:50%;filter:blur(100px);pointer-events:none;background:radial-gradient(circle,rgba(212,175,55,.09) 0%,transparent 70%);top:-180px;right:-80px;animation:bp-orb 10s ease-in-out infinite; }
.bp-hero-orb-2{ position:absolute;width:300px;height:300px;border-radius:50%;filter:blur(80px);pointer-events:none;background:radial-gradient(circle,rgba(16,185,129,.06) 0%,transparent 70%);bottom:-80px;left:-40px;animation:bp-orb 13s ease-in-out infinite reverse; }
.bp-hero-inner{ position:relative; z-index:2; display:flex; align-items:center; justify-content:space-between; gap:32px; flex-wrap:wrap; }
.bp-hero-badge {
  display:inline-flex; align-items:center; gap:7px;
  background:rgba(212,175,55,.09); border:1px solid rgba(212,175,55,.25);
  color:#d4af37; padding:5px 16px; border-radius:99px;
  font-size:.65rem; font-weight:800; letter-spacing:2px; text-transform:uppercase;
  margin-bottom:14px; animation:bp-fadeUp .5s .1s ease both;
}
.bp-hero-badge-dot{ width:6px;height:6px;border-radius:50%;background:#d4af37;box-shadow:0 0 6px rgba(212,175,55,.9); }
.bp-hero h1{ font-size:clamp(1.7rem,3vw,2.4rem);font-weight:900;color:#fff;margin:0 0 8px;line-height:1.2;animation:bp-fadeUp .55s .15s ease both; }
.bp-hero-sub{ color:#475569;font-size:.9rem;max-width:520px;animation:bp-fadeUp .55s .25s ease both; }

/* Month nav pill */
.bp-month-nav{
  display:flex; align-items:center; gap:10px;
  background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08);
  border-radius:16px; padding:14px 20px;
  animation:bp-fadeUp .55s .3s ease both;
}
.bp-month-btn{
  width:34px;height:34px;border-radius:9px;border:1px solid rgba(255,255,255,.1);
  background:rgba(255,255,255,.05);color:#94a3b8;cursor:pointer;
  display:flex;align-items:center;justify-content:center;font-size:.9rem;
  transition:background .2s,color .2s,border-color .2s;
}
.bp-month-btn:hover{ background:rgba(212,175,55,.15);border-color:rgba(212,175,55,.3);color:#d4af37; }
#month-picker{
  background:transparent;border:none;color:#f1f5f9;font-size:.95rem;font-weight:700;
  font-family:inherit;cursor:pointer;outline:none;
  padding:4px 6px; border-radius:6px;
}
#month-picker::-webkit-calendar-picker-indicator{ filter:invert(.5) sepia(1) saturate(2) hue-rotate(20deg); cursor:pointer; }

/* ══ SUMMARY CARDS ════════════════════════════════════ */
.bp-summary{
  display:grid; grid-template-columns:repeat(5,1fr);
  gap:14px; margin-bottom:24px;
}
.bp-card{
  border-radius:18px; padding:18px 16px 16px;
  background:linear-gradient(160deg,#0d1829 0%,#080f1e 100%);
  border:1px solid rgba(255,255,255,.07);
  position:relative; overflow:hidden;
  transition:transform .3s,box-shadow .3s,border-color .3s;
  animation:bp-fadeUp .5s ease both;
}
.bp-card::before{ content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--accent-line); }
.bp-card:hover{ transform:translateY(-4px);border-color:var(--accent-border);box-shadow:0 16px 40px rgba(0,0,0,.5); }
.bp-card-icon{ width:36px;height:36px;border-radius:10px;background:var(--accent-bg);display:flex;align-items:center;justify-content:center;font-size:.9rem;color:var(--accent-color);margin-bottom:12px; }
.bp-card-label{ font-size:.62rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#334155;margin-bottom:6px; }
.bp-card-value{ font-size:1.4rem;font-weight:900;color:#f1f5f9;animation:bp-count .3s ease; }

.bp-card-income  { --accent-line:linear-gradient(90deg,#10b981,#34d399); --accent-bg:rgba(16,185,129,.1); --accent-color:#10b981; --accent-border:rgba(16,185,129,.2); }
.bp-card-expense { --accent-line:linear-gradient(90deg,#ef4444,#f87171); --accent-bg:rgba(239,68,68,.1);  --accent-color:#ef4444; --accent-border:rgba(239,68,68,.2); }
.bp-card-balance { --accent-line:linear-gradient(90deg,#3b82f6,#60a5fa); --accent-bg:rgba(59,130,246,.1); --accent-color:#3b82f6; --accent-border:rgba(59,130,246,.2); }
.bp-card-goal    { --accent-line:linear-gradient(90deg,#d4af37,#facc15); --accent-bg:rgba(212,175,55,.1); --accent-color:#d4af37; --accent-border:rgba(212,175,55,.2); }
.bp-card-diff    { --accent-line:linear-gradient(90deg,#8b5cf6,#a78bfa); --accent-bg:rgba(139,92,246,.1); --accent-color:#8b5cf6; --accent-border:rgba(139,92,246,.2); }

.bp-card-diff .bp-card-value.positive{ color:#10b981; }
.bp-card-diff .bp-card-value.negative{ color:#ef4444; }

/* ══ PROGRESS BAR ═════════════════════════════════════ */
.bp-progress-wrap{ margin-bottom:28px; }
.bp-progress-meta{ display:flex;justify-content:space-between;align-items:center;margin-bottom:10px; }
.bp-progress-label{ font-size:.78rem;font-weight:700;color:#475569; }
.bp-progress-pct{ font-size:.82rem;font-weight:800;color:#d4af37; }
.bp-progress-track{ height:10px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;position:relative; }
.bp-progress-fill{
  height:100%; border-radius:99px; transition:width .6s cubic-bezier(.4,0,.2,1);
  background:linear-gradient(90deg,#22c55e,#4ade80,#d4af37);
  position:relative;
}
.bp-progress-fill::after{ content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.3) 50%,transparent 100%);border-radius:99px; }

/* ══ MAIN PANEL ═══════════════════════════════════════ */
.bp-panel{
  background:linear-gradient(160deg,#0d1829 0%,#080f1e 100%);
  border:1px solid rgba(255,255,255,.07); border-radius:22px;
  padding:28px 28px 32px; margin-bottom:24px;
}
.bp-panel-head{ display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:24px; }
.bp-panel-title{ display:flex;align-items:center;gap:10px; }
.bp-panel-title-icon{ width:36px;height:36px;border-radius:10px;background:rgba(212,175,55,.1);display:flex;align-items:center;justify-content:center;color:#d4af37;font-size:.9rem; }
.bp-panel-title h2{ font-size:1.05rem;font-weight:800;color:#f1f5f9;margin:0; }
.bp-panel-actions{ display:flex;gap:10px;flex-wrap:wrap; }

/* Buttons */
.bp-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 18px;border-radius:10px;font-size:.8rem;font-weight:700;
  border:none;cursor:pointer;font-family:inherit;text-decoration:none;
  transition:transform .2s,box-shadow .2s,background .2s;
}
.bp-btn:hover{ transform:translateY(-2px); }
.bp-btn-add   { background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#cbd5e1; }
.bp-btn-add:hover{ background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.18); }
.bp-btn-save  { background:linear-gradient(135deg,#d4af37,#facc15);color:#020617;box-shadow:0 3px 14px rgba(212,175,55,.3); }
.bp-btn-save:hover{ box-shadow:0 6px 22px rgba(212,175,55,.55); }
.bp-btn-pdf   { background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.25);color:#60a5fa; }
.bp-btn-pdf:hover{ background:rgba(59,130,246,.18);border-color:rgba(59,130,246,.4); }

/* Goal row */
.bp-goal-row{ display:flex;align-items:center;gap:10px; }
.bp-goal-row label{ font-size:.75rem;font-weight:700;color:#475569;white-space:nowrap; }
#goal{
  background:rgba(255,255,255,.04);border:1px solid rgba(212,175,55,.3);
  color:#f1f5f9;border-radius:9px;padding:9px 12px;font-size:.88rem;font-weight:700;
  width:140px;font-family:inherit;transition:border-color .2s,box-shadow .2s;outline:none;
}
#goal:focus{ border-color:#d4af37;box-shadow:0 0 0 3px rgba(212,175,55,.15); }

/* ══ TABLE ════════════════════════════════════════════ */
.bp-table-wrap{ overflow-x:auto; border-radius:14px;border:1px solid rgba(255,255,255,.06); }
.bp-table{ width:100%;border-collapse:collapse;min-width:560px; }
.bp-table thead tr{ background:#030b18; }
.bp-table thead th{
  padding:12px 14px;font-size:.65rem;font-weight:800;letter-spacing:1px;
  text-transform:uppercase;color:#334155;text-align:left;
  border-bottom:1px solid rgba(255,255,255,.06);white-space:nowrap;
}
.bp-table tbody tr{
  border-bottom:1px solid rgba(255,255,255,.04);
  transition:background .18s;
  animation:bp-row-in .25s ease both;
}
.bp-table tbody tr:last-child{ border-bottom:none; }
.bp-table tbody tr:hover{ background:rgba(255,255,255,.025); }
.bp-table td{ padding:10px 14px;vertical-align:middle; }

/* Inline cell inputs */
.bp-cell-input{
  background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);
  color:#e2e8f0;border-radius:8px;padding:8px 10px;font-size:.82rem;
  font-family:inherit;width:100%;outline:none;transition:border-color .2s,background .2s;
}
.bp-cell-input:focus{ border-color:rgba(212,175,55,.4);background:rgba(212,175,55,.04); }

.bp-cell-select{
  background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);
  color:#e2e8f0;border-radius:8px;padding:8px 10px;font-size:.82rem;
  font-family:inherit;outline:none;cursor:pointer;transition:border-color .2s;
  -webkit-appearance:none; appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2364748b'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 10px center;padding-right:28px;
}
.bp-cell-select.is-income{ border-color:rgba(16,185,129,.3);color:#34d399; }
.bp-cell-select.is-expense{ border-color:rgba(239,68,68,.25);color:#f87171; }
.bp-cell-select:focus{ border-color:rgba(212,175,55,.4); }

.bp-type-badge{
  display:inline-flex;align-items:center;gap:5px;
  font-size:.68rem;font-weight:800;letter-spacing:.5px;text-transform:uppercase;
  padding:3px 9px;border-radius:6px;
}
.bp-type-badge.in { background:rgba(16,185,129,.1);color:#10b981;border:1px solid rgba(16,185,129,.2); }
.bp-type-badge.out{ background:rgba(239,68,68,.08);color:#ef4444;border:1px solid rgba(239,68,68,.18); }

.bp-del-btn{
  width:30px;height:30px;border-radius:8px;border:none;cursor:pointer;
  background:rgba(239,68,68,.08);color:#f87171;font-size:.85rem;
  display:flex;align-items:center;justify-content:center;
  transition:background .2s,color .2s;
}
.bp-del-btn:hover{ background:rgba(239,68,68,.2);color:#fca5a5; }

/* Empty state */
.bp-empty{
  text-align:center;padding:50px 20px;color:#1e293b;
}
.bp-empty i{ font-size:2.5rem;color:rgba(212,175,55,.1);display:block;margin-bottom:14px; }
.bp-empty p{ font-size:.88rem;color:#1e293b; }

/* ══ CHART + INSIGHTS ROW ══════════════════════════════ */
.bp-bottom-row{ display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px; }

.bp-donut-panel{
  background:linear-gradient(160deg,#0d1829 0%,#080f1e 100%);
  border:1px solid rgba(255,255,255,.07);border-radius:22px;padding:24px;
}
.bp-donut-panel h3{ font-size:.85rem;font-weight:800;color:#f1f5f9;margin:0 0 20px; }
.bp-donut-wrap{ display:flex;align-items:center;gap:24px; }
#bp-donut{ flex-shrink:0; }
.bp-donut-legend{ display:flex;flex-direction:column;gap:10px; }
.bp-legend-item{ display:flex;align-items:center;gap:9px; }
.bp-legend-dot{ width:10px;height:10px;border-radius:50%;flex-shrink:0; }
.bp-legend-name{ font-size:.75rem;color:#64748b; }
.bp-legend-val{ font-size:.78rem;font-weight:700;color:#e2e8f0;margin-left:auto; }

.bp-insights-panel{
  background:linear-gradient(160deg,#0d1829 0%,#080f1e 100%);
  border:1px solid rgba(255,255,255,.07);border-radius:22px;padding:24px;
  display:flex;flex-direction:column;gap:14px;
}
.bp-insights-panel h3{ font-size:.85rem;font-weight:800;color:#f1f5f9;margin:0; }
.bp-insight-item{
  display:flex;align-items:flex-start;gap:12px;
  padding:12px 14px;border-radius:12px;background:rgba(255,255,255,.02);
  border:1px solid rgba(255,255,255,.05);
}
.bp-insight-icon{ width:32px;height:32px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.8rem; }
.bp-insight-text{ font-size:.78rem;color:#64748b;line-height:1.5; }
.bp-insight-text strong{ color:#e2e8f0;display:block;margin-bottom:2px;font-size:.8rem; }

/* ══ TOAST ════════════════════════════════════════════ */
#bp-toast-container{ position:fixed;bottom:28px;right:28px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none; }
.bp-toast{
  display:flex;align-items:center;gap:12px;
  padding:12px 18px;border-radius:13px;min-width:220px;max-width:320px;
  border:1px solid rgba(255,255,255,.08);
  box-shadow:0 16px 40px rgba(0,0,0,.5);
  backdrop-filter:blur(16px);
  animation:bp-toast-in .35s cubic-bezier(.34,1.56,.64,1) both;
  pointer-events:auto;
}
.bp-toast.out{ animation:bp-toast-out .3s ease forwards; }
.bp-toast-success{ background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.3); }
.bp-toast-error  { background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.3); }
.bp-toast-icon{ font-size:1rem; }
.bp-toast-success .bp-toast-icon{ color:#10b981; }
.bp-toast-error   .bp-toast-icon{ color:#ef4444; }
.bp-toast-msg{ font-size:.82rem;font-weight:600;color:#e2e8f0; }

/* ══ RESPONSIVE ═══════════════════════════════════════ */
@media(max-width:1100px){ .bp-summary{ grid-template-columns:repeat(3,1fr); } }
@media(max-width:960px){
  /* Stack sidebar above content so it no longer squeezes the page (mirrors emergency-fund .ef-shell) */
  .dashboard-wrapper{ flex-direction:column; }
}
@media(max-width:900px){ .bp-bottom-row{ grid-template-columns:1fr; } }
@media(max-width:680px){
  .bp-main{ padding:18px 16px 40px; }
  .bp-hero{ padding:28px 20px 24px; }
  .bp-summary{ grid-template-columns:repeat(2,1fr); }
  .bp-hero-orb-1,.bp-hero-orb-2{ display:none; }
}
@media(max-width:420px){
  .bp-summary{ grid-template-columns:1fr; }
  .bp-goal-row input{ width:100%; max-width:100%; }
}
</style>

<!-- Toast container -->
<div id="bp-toast-container"></div>

<div class="dashboard-wrapper">

<?php include 'includes/sidebar.php'; ?>

<div class="bp-main">
<div class="bp-page">

<!-- ══ HERO ══════════════════════════════════════════ -->
<div class="bp-hero">
  <div class="bp-hero-grid"></div>
  <div class="bp-hero-orb-1"></div>
  <div class="bp-hero-orb-2"></div>
  <div class="bp-hero-inner">
    <div>
      <div class="bp-hero-badge"><span class="bp-hero-badge-dot"></span> Finance · Budget Planner</div>
      <h1>Monthly <span class="bp-gold">Wealth Planner</span></h1>
      <p class="bp-hero-sub">Track income, expenses and hit your savings goal every month.</p>
    </div>
    <div class="bp-month-nav">
      <button class="bp-month-btn" onclick="prevMonth()" title="Previous month"><i class="fas fa-chevron-left"></i></button>
      <input type="month" id="month-picker">
      <button class="bp-month-btn" onclick="nextMonth()" title="Next month"><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>
</div>

<!-- ══ SUMMARY CARDS ════════════════════════════════ -->
<div class="bp-summary">
  <div class="bp-card bp-card-income" style="animation-delay:.08s">
    <div class="bp-card-icon"><i class="fas fa-arrow-trend-up"></i></div>
    <div class="bp-card-label">Total Income</div>
    <div class="bp-card-value" id="income">$0</div>
  </div>
  <div class="bp-card bp-card-expense" style="animation-delay:.14s">
    <div class="bp-card-icon"><i class="fas fa-arrow-trend-down"></i></div>
    <div class="bp-card-label">Total Expense</div>
    <div class="bp-card-value" id="expense">$0</div>
  </div>
  <div class="bp-card bp-card-balance" style="animation-delay:.2s">
    <div class="bp-card-icon"><i class="fas fa-scale-balanced"></i></div>
    <div class="bp-card-label">Balance</div>
    <div class="bp-card-value" id="balance">$0</div>
  </div>
  <div class="bp-card bp-card-goal" style="animation-delay:.26s">
    <div class="bp-card-icon"><i class="fas fa-bullseye"></i></div>
    <div class="bp-card-label">Savings Goal</div>
    <div class="bp-card-value" id="goal-display">$0</div>
  </div>
  <div class="bp-card bp-card-diff" style="animation-delay:.32s">
    <div class="bp-card-icon"><i class="fas fa-chart-line"></i></div>
    <div class="bp-card-label">vs Goal</div>
    <div class="bp-card-value" id="difference">$0</div>
  </div>
</div>

<!-- ══ PROGRESS BAR ══════════════════════════════════ -->
<div class="bp-progress-wrap">
  <div class="bp-progress-meta">
    <span class="bp-progress-label"><i class="fas fa-flag" style="color:#d4af37;margin-right:6px"></i>Goal Progress</span>
    <span class="bp-progress-pct" id="pct-label">0%</span>
  </div>
  <div class="bp-progress-track">
    <div id="progress" class="bp-progress-fill" style="width:0%"></div>
  </div>
</div>

<!-- ══ TRANSACTION PANEL ═════════════════════════════ -->
<div class="bp-panel">
  <div class="bp-panel-head">
    <div class="bp-panel-title">
      <div class="bp-panel-title-icon"><i class="fas fa-list-ul"></i></div>
      <h2>Transactions <span id="tx-count" style="font-size:.75rem;font-weight:600;color:#334155;margin-left:4px"></span></h2>
    </div>
    <div class="bp-panel-actions">
      <div class="bp-goal-row">
        <label for="goal"><i class="fas fa-bullseye"></i> Goal ($)</label>
        <input type="number" id="goal" placeholder="0">
      </div>
      <button class="bp-btn bp-btn-add" onclick="addRow()"><i class="fas fa-plus"></i> Add Entry</button>
      <button class="bp-btn bp-btn-save" onclick="save()"><i class="fas fa-floppy-disk"></i> Save</button>
      <button class="bp-btn bp-btn-pdf" onclick="downloadPDF()"><i class="fas fa-file-arrow-down"></i> PDF</button>
    </div>
  </div>

  <div class="bp-table-wrap">
    <table class="bp-table">
      <thead>
        <tr>
          <th style="width:35%">Description</th>
          <th style="width:20%">Category</th>
          <th style="width:14%">Type</th>
          <th style="width:18%">Amount ($)</th>
          <th style="width:6%"></th>
        </tr>
      </thead>
      <tbody id="table"></tbody>
    </table>
  </div>
</div>

<!-- ══ DONUT CHART + INSIGHTS ═══════════════════════ -->
<div class="bp-bottom-row">

  <div class="bp-donut-panel">
    <h3><i class="fas fa-chart-pie" style="color:#d4af37;margin-right:8px"></i>Income vs Expense</h3>
    <div class="bp-donut-wrap">
      <canvas id="bp-donut" width="120" height="120"></canvas>
      <div class="bp-donut-legend">
        <div class="bp-legend-item">
          <span class="bp-legend-dot" style="background:#10b981"></span>
          <span class="bp-legend-name">Income</span>
          <span class="bp-legend-val" id="legend-income">$0</span>
        </div>
        <div class="bp-legend-item">
          <span class="bp-legend-dot" style="background:#ef4444"></span>
          <span class="bp-legend-name">Expense</span>
          <span class="bp-legend-val" id="legend-expense">$0</span>
        </div>
        <div class="bp-legend-item">
          <span class="bp-legend-dot" style="background:#3b82f6"></span>
          <span class="bp-legend-name">Savings</span>
          <span class="bp-legend-val" id="legend-balance">$0</span>
        </div>
      </div>
    </div>
  </div>

  <div class="bp-insights-panel">
    <h3><i class="fas fa-lightbulb" style="color:#d4af37;margin-right:8px"></i>Smart Insights</h3>
    <div id="bp-insights-list"></div>
  </div>

</div>

</div><!-- /.bp-page -->
</div><!-- /.bp-main -->
</div><!-- /.dashboard-wrapper -->

<script>
const monthPicker = document.getElementById("month-picker");
if(!monthPicker.value) monthPicker.value = new Date().toISOString().slice(0,7);

let data = { transactions: [] };

/* ── Helpers ── */
function fmt(n){ return '$' + Number(n).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function showToast(msg, type='success'){
  const c = document.getElementById('bp-toast-container');
  const t = document.createElement('div');
  t.className = `bp-toast bp-toast-${type}`;
  t.innerHTML = `<span class="bp-toast-icon"><i class="fas fa-${type==='success'?'circle-check':'circle-xmark'}"></i></span><span class="bp-toast-msg">${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{ t.classList.add('out'); setTimeout(()=>t.remove(),300); }, 3000);
}

/* ── Add row ── */
function addRow(){
  data.transactions.push({desc:"",cat:"Food",type:"out",amount:0});
  render();
}

/* ── Category icons ── */
const catIcons = {
  'Food':'fa-utensils','Transport':'fa-car','Housing':'fa-house','Health':'fa-heart-pulse',
  'Entertainment':'fa-film','Shopping':'fa-bag-shopping','Education':'fa-graduation-cap',
  'Salary':'fa-briefcase','Investment':'fa-chart-line','Business':'fa-store',
  'Other':'fa-circle-dot','Expense':'fa-receipt','Income':'fa-coins'
};
function catIcon(cat){ return catIcons[cat] || 'fa-tag'; }

/* ── Render ── */
function render(){
  const tb = document.getElementById("table");
  if(!data.transactions.length){
    tb.innerHTML = `<tr><td colspan="5"><div class="bp-empty"><i class="fas fa-wallet"></i><p>No entries yet. Click "Add Entry" to get started.</p></div></td></tr>`;
    calc(); return;
  }
  tb.innerHTML = data.transactions.map((t,i)=>`
    <tr style="animation-delay:${i*0.04}s">
      <td><input class="bp-cell-input" value="${escHtml(t.desc)}" placeholder="Description..." oninput="update(${i},'desc',this.value)"></td>
      <td>
        <input class="bp-cell-input" value="${escHtml(t.cat)}" placeholder="Category" list="bp-cats" oninput="update(${i},'cat',this.value)">
      </td>
      <td>
        <select class="bp-cell-select ${t.type==='in'?'is-income':'is-expense'}" onchange="update(${i},'type',this.value);this.className='bp-cell-select '+(this.value==='in'?'is-income':'is-expense')">
          <option value="in"${t.type==='in'?' selected':''}>Income</option>
          <option value="out"${t.type==='out'?' selected':''}>Expense</option>
        </select>
      </td>
      <td><input class="bp-cell-input" type="number" value="${t.amount}" min="0" step="0.01" oninput="update(${i},'amount',this.value)"></td>
      <td><button class="bp-del-btn" onclick="del(${i})" title="Remove"><i class="fas fa-trash-can"></i></button></td>
    </tr>
  `).join('');

  document.getElementById('tx-count').textContent = `(${data.transactions.length})`;
  calc();
}

function escHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;'); }

function update(i,f,v){ data.transactions[i][f]=v; calc(); }

function del(i){ data.transactions.splice(i,1); render(); }

/* ── Calc ── */
function calc(){
  let inc=0, exp=0;
  data.transactions.forEach(t=>{
    const v=parseFloat(t.amount)||0;
    if(t.type==='in') inc+=v; else exp+=v;
  });
  const balance = inc - exp;
  const goal    = parseFloat(document.getElementById("goal").value)||0;
  const diff    = balance - goal;
  const pct     = goal>0 ? Math.min(100, Math.max(0, (balance/goal)*100)) : 0;

  document.getElementById("income").textContent       = fmt(inc);
  document.getElementById("expense").textContent      = fmt(exp);
  document.getElementById("balance").textContent      = fmt(balance);
  document.getElementById("goal-display").textContent = fmt(goal);

  const diffEl = document.getElementById("difference");
  diffEl.textContent = fmt(diff);
  diffEl.className = 'bp-card-value ' + (diff>=0?'positive':'negative');

  document.getElementById("progress").style.width = pct + '%';
  document.getElementById("pct-label").textContent = Math.round(pct) + '%';

  document.getElementById("legend-income").textContent  = fmt(inc);
  document.getElementById("legend-expense").textContent = fmt(exp);
  document.getElementById("legend-balance").textContent = fmt(balance);

  drawDonut(inc, exp, balance);
  buildInsights(inc, exp, balance, goal, pct);
}

/* ── Donut chart ── */
function drawDonut(inc, exp, balance){
  const canvas = document.getElementById('bp-donut');
  const ctx    = canvas.getContext('2d');
  const cx = 60, cy = 60, r = 48, sw = 14;
  ctx.clearRect(0,0,120,120);

  const total = inc + exp;
  if(total === 0){
    ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2);
    ctx.strokeStyle='rgba(255,255,255,.06)'; ctx.lineWidth=sw; ctx.stroke();
    return;
  }

  const segments = [
    { val:inc,   color:'#10b981' },
    { val:exp,   color:'#ef4444' },
  ];

  let start = -Math.PI/2;
  segments.forEach(s=>{
    if(!s.val) return;
    const arc = (s.val/total) * Math.PI * 2;
    ctx.beginPath();
    ctx.arc(cx,cy,r,start,start+arc);
    ctx.strokeStyle = s.color;
    ctx.lineWidth   = sw;
    ctx.lineCap     = 'butt';
    ctx.stroke();
    start += arc;
  });

  /* centre label */
  const savePct = inc>0 ? Math.round((balance/inc)*100) : 0;
  ctx.font='bold 14px Inter,sans-serif'; ctx.fillStyle='#f1f5f9'; ctx.textAlign='center';
  ctx.fillText(savePct+'%', cx, cy+2);
  ctx.font='9px Inter,sans-serif'; ctx.fillStyle='#334155';
  ctx.fillText('saved', cx, cy+14);
}

/* ── Smart insights ── */
function buildInsights(inc,exp,balance,goal,pct){
  const el = document.getElementById('bp-insights-list');
  const items = [];

  if(inc===0 && exp===0){
    items.push({icon:'fa-plus-circle',bg:'rgba(212,175,55,.1)',col:'#d4af37',title:'Get started',text:'Add your income and expense entries to see smart insights.'});
  } else {
    const saveRate = inc>0 ? (balance/inc)*100 : 0;
    if(saveRate>=20)      items.push({icon:'fa-star',bg:'rgba(16,185,129,.1)',col:'#10b981',title:'Great savings rate!',text:`You are saving ${saveRate.toFixed(0)}% of your income. Keep it up!`});
    else if(saveRate>=0)  items.push({icon:'fa-info-circle',bg:'rgba(59,130,246,.1)',col:'#3b82f6',title:'Savings rate',text:`You are saving ${saveRate.toFixed(0)}% of income. Aim for 20%+.`});
    else                  items.push({icon:'fa-triangle-exclamation',bg:'rgba(239,68,68,.1)',col:'#ef4444',title:'Overspending',text:'Expenses exceed income this month. Review your largest cost categories.'});

    if(goal>0 && pct>=100) items.push({icon:'fa-trophy',bg:'rgba(212,175,55,.1)',col:'#d4af37',title:'Goal reached! 🏆',text:`You hit your $${goal.toLocaleString()} savings goal this month.`});
    else if(goal>0)        items.push({icon:'fa-bullseye',bg:'rgba(139,92,246,.1)',col:'#8b5cf6',title:'Goal tracking',text:`${pct.toFixed(0)}% toward your $${goal.toLocaleString()} goal. ${pct<50?'Push harder!':'Almost there!'}`});

    const ratio = inc>0 ? (exp/inc)*100 : 0;
    if(ratio>80) items.push({icon:'fa-fire',bg:'rgba(249,115,22,.1)',col:'#f97316',title:'High expense ratio',text:`${ratio.toFixed(0)}% of income spent. Try to reduce discretionary spending.`});
  }

  el.innerHTML = items.map(it=>`
    <div class="bp-insight-item">
      <div class="bp-insight-icon" style="background:${it.bg};color:${it.col}"><i class="fas ${it.icon}"></i></div>
      <div class="bp-insight-text"><strong>${it.title}</strong>${it.text}</div>
    </div>`).join('');
}

/* ── Save ── */
function save(){
  const form = new FormData();
  form.append("action","save_budget");
  form.append("month", monthPicker.value);
  form.append("goal", document.getElementById("goal").value);
  form.append("transactions", JSON.stringify(data.transactions));
  fetch("budgetplanner.php",{method:"POST",body:form})
    .then(r=>r.json())
    .then(r=>{ showToast(r.status==='success'?'Budget saved successfully!':'Save failed. Try again.', r.status==='success'?'success':'error'); });
}

/* ── Load ── */
function load(){
  fetch(`budgetplanner.php?action=load_budget&month=${monthPicker.value}`)
    .then(r=>r.json())
    .then(r=>{
      if(r.status==="found"){
        data.transactions = r.transactions || [];
        document.getElementById("goal").value = r.goal || 0;
      } else {
        data.transactions = [];
        document.getElementById("goal").value = 0;
      }
      render();
    });
}

/* ── Month nav ── */
function prevMonth(){
  const d = new Date(monthPicker.value + "-01");
  d.setMonth(d.getMonth()-1);
  monthPicker.value = d.toISOString().slice(0,7);
  load();
}
function nextMonth(){
  const d = new Date(monthPicker.value + "-01");
  d.setMonth(d.getMonth()+1);
  monthPicker.value = d.toISOString().slice(0,7);
  load();
}

/* ── PDF ── */
function downloadPDF(){
  const form = document.createElement("form");
  form.method="POST"; form.action="generate_budget_pdf.php"; form.style.display="none";
  [['month',monthPicker.value],['goal',document.getElementById("goal").value],['transactions',JSON.stringify(data.transactions)]].forEach(([k,v])=>{
    const inp=document.createElement("input"); inp.name=k; inp.value=v; form.appendChild(inp);
  });
  document.body.appendChild(form); form.submit(); document.body.removeChild(form);
}

monthPicker.addEventListener("change", load);
document.getElementById("goal").addEventListener("input", calc);

/* Datalist for categories */
document.body.insertAdjacentHTML('beforeend',`<datalist id="bp-cats">
  <option>Salary</option><option>Business</option><option>Investment</option>
  <option>Food</option><option>Transport</option><option>Housing</option>
  <option>Health</option><option>Entertainment</option><option>Shopping</option>
  <option>Education</option><option>Other</option>
</datalist>`);

load();
</script>

<?php include 'includes/footer.php'; ?>