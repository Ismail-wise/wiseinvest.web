<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];

/* ── CHECK IF TRACKER TABLE EXISTS ──────────────────────────── */
$tbl_chk         = $conn->query("SHOW TABLES LIKE 'daily_expenses'");
$tracker_enabled = ($tbl_chk && $tbl_chk->num_rows > 0);

/* ── HANDLE POST ────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_expense' && $tracker_enabled) {
        $exp_date = $_POST['expense_date'] ?? date('Y-m-d');
        $category = $_POST['category'] ?? 'other';
        $amount   = floatval($_POST['amount'] ?? 0);
        $note     = trim($_POST['note'] ?? '');
        $valid    = ['food','transport','utilities','entertainment','other'];
        if (!in_array($category, $valid)) $category = 'other';
        if ($amount > 0) {
            $st = $conn->prepare("INSERT INTO daily_expenses (user_id,expense_date,category,amount,note) VALUES (?,?,?,?,?)");
            $st->bind_param("issds", $user_id, $exp_date, $category, $amount, $note);
            $st->execute();
        }
        header("Location: emergency-fund.php"); exit();

    } elseif ($action === 'delete_expense' && $tracker_enabled) {
        $exp_id = intval($_POST['expense_id'] ?? 0);
        if ($exp_id > 0) {
            $st = $conn->prepare("DELETE FROM daily_expenses WHERE id=? AND user_id=?");
            $st->bind_param("ii", $exp_id, $user_id);
            $st->execute();
        }
        header("Location: emergency-fund.php"); exit();

    } else {
        /* Save emergency fund settings */
        $mexp    = floatval($_POST['monthly_expense'] ?? 0);
        $mmonths = max(1, intval($_POST['months'] ?? 6));
        $mcurr   = floatval($_POST['current'] ?? 0);
        $mtarget = $mexp * $mmonths;
        $chk     = $conn->prepare("SELECT id FROM emergency_fund WHERE user_id=?");
        $chk->bind_param("i", $user_id); $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $st = $conn->prepare("UPDATE emergency_fund SET monthly_expense=?,months=?,target_amount=?,current_amount=? WHERE user_id=?");
            $st->bind_param("diddi", $mexp, $mmonths, $mtarget, $mcurr, $user_id);
        } else {
            $st = $conn->prepare("INSERT INTO emergency_fund (user_id,monthly_expense,months,target_amount,current_amount) VALUES (?,?,?,?,?)");
            $st->bind_param("ididd", $user_id, $mexp, $mmonths, $mtarget, $mcurr);
        }
        $st->execute();
        header("Location: emergency-fund.php"); exit();
    }
}

/* ── EMERGENCY FUND DATA ────────────────────────────────────── */
$ef_res      = $conn->query("SELECT * FROM emergency_fund WHERE user_id=$user_id");
$ef_data     = $ef_res ? $ef_res->fetch_assoc() : [];
$ef_monthly  = (float)($ef_data['monthly_expense'] ?? 0);
$ef_months   = (int)($ef_data['months'] ?? 6);
$ef_current  = (float)($ef_data['current_amount'] ?? 0);
$ef_target   = (float)($ef_data['target_amount'] ?? 0);
$ef_progress = $ef_target > 0 ? min(100, ($ef_current / $ef_target) * 100) : 0;

/* ── MONTH TO VIEW ──────────────────────────────────────────── */
$view_month = (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month']))
              ? $_GET['month'] : date('Y-m');
$safe_ym    = $conn->real_escape_string($view_month);
$is_current = ($view_month === date('Y-m'));

/* ── EXPENSE AGGREGATES ─────────────────────────────────────── */
$monthly_total = 0;
$cat_totals    = [];
$expense_rows  = [];

if ($tracker_enabled) {
    $cat_q = $conn->query("
        SELECT category, SUM(amount) AS subtotal, COUNT(*) AS cnt
        FROM daily_expenses
        WHERE user_id=$user_id AND DATE_FORMAT(expense_date,'%Y-%m')='$safe_ym'
        GROUP BY category ORDER BY subtotal DESC
    ");
    while ($r = $cat_q->fetch_assoc()) {
        $cat_totals[$r['category']] = ['total' => (float)$r['subtotal'], 'cnt' => (int)$r['cnt']];
        $monthly_total += (float)$r['subtotal'];
    }
    $rows_q = $conn->query("
        SELECT id, expense_date, category, amount, note
        FROM daily_expenses
        WHERE user_id=$user_id AND DATE_FORMAT(expense_date,'%Y-%m')='$safe_ym'
        ORDER BY expense_date DESC, id DESC
    ");
    while ($r = $rows_q->fetch_assoc()) $expense_rows[] = $r;
}

$days_elapsed   = $is_current ? (int)date('j') : (int)date('t', strtotime($view_month.'-01'));
$daily_avg      = $days_elapsed > 0 ? $monthly_total / $days_elapsed : 0;
$highest_cat    = !empty($cat_totals) ? array_key_first($cat_totals) : null;
$highest_amt    = $highest_cat ? $cat_totals[$highest_cat]['total'] : 0;
$recommended_ef = $monthly_total * 6;

/* ── MONTH SELECTOR (last 6 months) ─────────────────────────── */
$month_opts = [];
for ($i = 0; $i < 6; $i++) {
    $m = date('Y-m', strtotime("-$i months"));
    $month_opts[] = ['value' => $m, 'label' => date('F Y', strtotime($m.'-01'))];
}

/* ── CATEGORY META ──────────────────────────────────────────── */
$cat_meta = [
    'food'          => ['icon'=>'fa-utensils', 'color'=>'#f59e0b','en'=>'Food',         'mm'=>'အစားအသောက်'],
    'transport'     => ['icon'=>'fa-car',       'color'=>'#3b82f6','en'=>'Transport',    'mm'=>'သွားလာရေး'],
    'utilities'     => ['icon'=>'fa-bolt',      'color'=>'#8b5cf6','en'=>'Utilities',    'mm'=>'နေအိမ်သုံး'],
    'entertainment' => ['icon'=>'fa-gamepad',   'color'=>'#ec4899','en'=>'Entertainment','mm'=>'ဖျော်ဖြေရေး'],
    'other'         => ['icon'=>'fa-box',       'color'=>'#64748b','en'=>'Other',        'mm'=>'အခြား'],
];

include 'includes/header.php';
?>
<style>
/* ═══════════════════════════════════════════════════════
   EMERGENCY FUND & DAILY EXPENSE TRACKER
   ═══════════════════════════════════════════════════════ */

@keyframes dUp  { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
@keyframes dIn  { from{opacity:0;transform:scale(.94) translateY(12px)} to{opacity:1;transform:none} }
@keyframes dOrb { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(16px,-12px) scale(1.07)} }
@keyframes dBar { from{width:0} }

:root {
  --bg:   #020617;
  --card: rgba(10,18,38,.82);
  --bdr:  rgba(255,255,255,.07);
  --gold: #d4af37;
  --gold2:#f5d679;
  --cyan: #38bdf8;
  --vio:  #a78bfa;
  --grn:  #10b981;
  --red:  #ef4444;
  --muted:rgba(148,163,184,.75);
  --ink:  #f1f5f9;
}
body { background:var(--bg); color:var(--ink); font-family:'Padauk','Segoe UI',sans-serif; margin:0; }

/* ── Background ── */
.ef-bg { position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden; }
.ef-bg-grad { position:absolute;inset:0;
  background:radial-gradient(ellipse 900px 600px at 5% 10%,rgba(212,175,55,.06),transparent 65%),
             radial-gradient(ellipse 600px 500px at 95% 15%,rgba(16,185,129,.04),transparent 65%),
             linear-gradient(180deg,#020617,#030c1e); }
.ef-bg-grid { position:absolute;inset:0;
  background-image:linear-gradient(rgba(212,175,55,.03) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(212,175,55,.03) 1px,transparent 1px);
  background-size:64px 64px; }
.ef-orb { position:absolute;border-radius:50%;filter:blur(90px);pointer-events:none; }
.ef-orb-1 { width:500px;height:500px;background:radial-gradient(circle,rgba(212,175,55,.08),transparent 70%);
  top:-180px;left:-120px;animation:dOrb 11s ease-in-out infinite; }
.ef-orb-2 { width:380px;height:380px;background:radial-gradient(circle,rgba(16,185,129,.06),transparent 70%);
  bottom:-80px;right:-80px;animation:dOrb 14s ease-in-out infinite reverse; }

/* ── Shell ── */
.ef-shell { position:relative;z-index:1;display:flex;gap:24px;padding:26px 26px 70px;min-height:100vh; }
.ef-main  { flex:1;min-width:0; }

/* ── Page header ── */
.ef-page-hdr { display:flex;justify-content:space-between;align-items:center;
  margin-bottom:20px;flex-wrap:wrap;gap:12px;animation:dUp .5s ease both; }
.ef-page-title { font-size:clamp(1.3rem,2.5vw,1.85rem);font-weight:900;
  background:linear-gradient(135deg,#f1f5f9 30%,var(--gold) 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  display:flex;align-items:center;gap:10px; }
.ef-page-title i { -webkit-text-fill-color:var(--gold);color:var(--gold); }
.ef-month-form { display:flex;align-items:center;gap:8px; }
.ef-month-sel  { background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
  color:var(--ink);padding:8px 14px;border-radius:10px;font-size:.82rem;
  cursor:pointer;font-family:inherit;transition:all .2s; }
.ef-month-sel:focus { outline:none;border-color:rgba(212,175,55,.5);
  box-shadow:0 0 0 3px rgba(212,175,55,.1); }
.ef-month-sel option { background:#0f1a35; }

/* ── Notice banner ── */
.ef-notice { background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.25);
  border-radius:14px;padding:16px 20px;display:flex;align-items:flex-start;gap:12px;
  font-size:.84rem;margin-bottom:20px;animation:dIn .5s ease both; }
.ef-notice i { color:#f59e0b;font-size:1.1rem;margin-top:1px;flex-shrink:0; }
.ef-notice p { margin:0;color:var(--muted);line-height:1.65; }
.ef-notice strong { color:var(--ink); }
.ef-notice code  { background:rgba(255,255,255,.08);padding:1px 6px;border-radius:5px;font-size:.82em; }

/* ── KPI grid ── */
.ef-kpi-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px; }
.ef-kpi {
  background:var(--card);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
  border:1px solid var(--bdr);border-radius:18px;padding:22px 20px;
  position:relative;overflow:hidden;transition:all .28s;animation:dIn .6s ease both;
}
.ef-kpi:hover { transform:translateY(-5px);border-color:var(--kc,var(--bdr));
  box-shadow:0 20px 55px rgba(0,0,0,.55),0 0 0 1px var(--kc,transparent); }
.ef-kpi::before { content:"";position:absolute;top:0;left:0;right:0;height:3px;
  background:var(--kg,linear-gradient(90deg,var(--gold),var(--cyan)));opacity:.75; }
.ef-kpi::after  { content:"";position:absolute;bottom:-40px;right:-40px;width:120px;height:120px;
  border-radius:50%;background:radial-gradient(circle,var(--kglow,rgba(212,175,55,.06)),transparent 70%);
  pointer-events:none; }
.kpi-row { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px; }
.kpi-lbl { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted); }
.kpi-ico { width:38px;height:38px;border-radius:11px;display:flex;align-items:center;
  justify-content:center;font-size:1rem; }
.kpi-val  { font-size:1.65rem;font-weight:900;letter-spacing:-.5px;color:var(--ink);margin-bottom:10px; }
.kpi-note { font-size:.7rem;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:5px; }
.kpi-note.g { color:var(--grn); }
.kpi-note.a { color:#f59e0b; }
.kpi-note.c { color:var(--cyan); }

/* ── Main grid ── */
.ef-grid { display:grid;grid-template-columns:1fr 1fr;gap:18px; }
.ef-card {
  background:var(--card);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
  border:1px solid var(--bdr);border-radius:18px;padding:24px;
  position:relative;overflow:hidden;animation:dIn .6s ease both;transition:border-color .25s;
}
.ef-card:hover { border-color:rgba(212,175,55,.18); }
.ef-card::before { content:"";position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,transparent,rgba(212,175,55,.5),rgba(167,139,250,.5),transparent);
  opacity:.45; }
.ef-card.full { grid-column:span 2; }
.ch   { display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px; }
.ch-l h3 { margin:0 0 4px;font-size:.95rem;font-weight:700;color:var(--ink);
  display:flex;align-items:center;gap:8px; }
.ch-l h3 i { color:var(--gold);font-size:.85rem; }
.ch-l p  { margin:0;font-size:.71rem;color:var(--muted); }

/* ── Form elements ── */
.ef-form-group { margin-bottom:15px; }
.ef-form-group label { display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;
  letter-spacing:.8px;color:var(--muted);margin-bottom:6px; }
.ef-input, .ef-select {
  width:100%;box-sizing:border-box;padding:10px 13px;
  background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);
  color:var(--ink);border-radius:10px;font-size:.88rem;font-family:inherit;transition:all .2s;
}
.ef-input:focus, .ef-select:focus { outline:none;border-color:rgba(212,175,55,.5);
  box-shadow:0 0 0 3px rgba(212,175,55,.1);background:rgba(255,255,255,.06); }
.ef-input[readonly] { opacity:.55;cursor:not-allowed;border-style:dashed; }
.ef-select option { background:#0f1a35;color:var(--ink); }
.ef-btn {
  border:none;width:100%;padding:12px;border-radius:11px;font-weight:700;font-size:.88rem;
  cursor:pointer;font-family:inherit;transition:all .24s;display:flex;align-items:center;
  justify-content:center;gap:8px;margin-top:4px;
}
.ef-btn-gold { background:linear-gradient(135deg,var(--gold),var(--gold2));color:#020617;
  box-shadow:0 4px 18px rgba(212,175,55,.25); }
.ef-btn-gold:hover { transform:translateY(-2px);box-shadow:0 8px 28px rgba(212,175,55,.4); }
.ef-btn-grn  { background:linear-gradient(135deg,var(--grn),#059669);color:#fff;
  box-shadow:0 4px 18px rgba(16,185,129,.2); }
.ef-btn-grn:hover { transform:translateY(-2px);box-shadow:0 8px 28px rgba(16,185,129,.35); }

/* ── EF progress ── */
.ef-progress-wrap { margin:16px 0 0; }
.ef-progress-meta { display:flex;justify-content:space-between;font-size:.74rem;
  color:var(--muted);margin-bottom:8px; }
.ef-progress-bar  { height:10px;background:rgba(255,255,255,.07);border-radius:99px;overflow:hidden; }
.ef-progress-fill { height:100%;background:linear-gradient(90deg,var(--grn),var(--cyan));
  border-radius:99px;width:0;transition:width 1.4s ease;box-shadow:0 0 12px rgba(16,185,129,.35); }

/* ── EF stat rows ── */
.ef-stat-row { display:flex;justify-content:space-between;align-items:center;
  padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05); }
.ef-stat-row:last-child { border-bottom:none; }
.ef-stat-l { font-size:.73rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.6px; }
.ef-stat-v { font-size:.9rem;font-weight:800; }
.ef-stat-v.g { color:var(--grn); }
.ef-stat-v.a { color:#f59e0b; }
.ef-stat-v.c { color:var(--cyan); }

/* ── Recommended EF banner ── */
.ef-rec-banner { background:linear-gradient(135deg,rgba(16,185,129,.08),rgba(56,189,248,.06));
  border:1px solid rgba(16,185,129,.22);border-radius:13px;padding:14px 18px;
  display:flex;align-items:center;gap:14px;margin-top:16px; }
.ef-rec-ico  { width:40px;height:40px;background:rgba(16,185,129,.12);border-radius:11px;
  display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--grn);flex-shrink:0; }
.ef-rec-label { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted); }
.ef-rec-val   { font-size:1.1rem;font-weight:900;color:var(--grn); }
.ef-rec-sub   { font-size:.7rem;color:var(--muted);margin-top:2px; }

/* ── Category breakdown ── */
.ef-cat-list { display:flex;flex-direction:column;gap:14px; }
.ef-cat-item { }
.ef-cat-hdr  { display:flex;justify-content:space-between;align-items:center;margin-bottom:6px; }
.ef-cat-name { display:flex;align-items:center;gap:8px;font-size:.82rem;font-weight:700; }
.ef-cat-icon { width:26px;height:26px;border-radius:7px;display:flex;align-items:center;
  justify-content:center;font-size:.7rem;flex-shrink:0; }
.ef-cat-mm   { color:var(--muted);font-size:.73rem;margin-left:2px; }
.ef-cat-cnt  { color:var(--muted);font-size:.7rem; }
.ef-cat-right { display:flex;align-items:center;gap:10px; }
.ef-cat-pct  { font-size:.72rem;color:var(--muted); }
.ef-cat-amt  { font-size:.85rem;font-weight:800; }
.ef-cat-bar-bg   { height:6px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden; }
.ef-cat-bar-fill { height:100%;border-radius:99px;animation:dBar .9s ease both; }

/* ── Expense table ── */
.ef-table-wrap { overflow-x:auto; }
.ef-table { width:100%;border-collapse:collapse;font-size:.83rem; }
.ef-table thead th { padding:10px 12px;text-align:left;font-size:.66rem;font-weight:700;
  text-transform:uppercase;letter-spacing:1px;color:var(--muted);
  border-bottom:1px solid rgba(255,255,255,.08);white-space:nowrap; }
.ef-table thead th.r { text-align:right; }
.ef-table tbody tr { border-bottom:1px solid rgba(255,255,255,.04);transition:background .18s; }
.ef-table tbody tr:hover { background:rgba(255,255,255,.025); }
.ef-table tbody tr:last-child { border-bottom:none; }
.ef-table td { padding:11px 12px;vertical-align:middle; }
.ef-table tfoot td { padding:12px;border-top:2px solid rgba(255,255,255,.09); }
.ef-td-date  { white-space:nowrap;color:var(--muted);font-size:.8rem; }
.ef-td-amt   { font-weight:800;color:var(--ink);text-align:right;white-space:nowrap; }
.ef-td-total { font-weight:900;color:var(--gold);font-size:.95rem;text-align:right;white-space:nowrap; }
.ef-td-lbl   { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--muted); }
.ef-td-note  { color:var(--muted);font-size:.78rem;max-width:180px;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.ef-cat-badge { display:inline-flex;align-items:center;gap:5px;padding:3px 9px;
  border-radius:99px;font-size:.7rem;font-weight:700;white-space:nowrap; }
.ef-del-btn { background:transparent;border:1px solid rgba(239,68,68,.3);color:rgba(239,68,68,.7);
  width:27px;height:27px;border-radius:7px;cursor:pointer;display:inline-flex;align-items:center;
  justify-content:center;font-size:.7rem;transition:all .2s;padding:0;vertical-align:middle; }
.ef-del-btn:hover { background:rgba(239,68,68,.12);border-color:var(--red);color:var(--red); }
.ef-empty { text-align:center;padding:36px 20px;color:var(--muted);font-size:.84rem;line-height:1.7; }
.ef-empty i { font-size:2.4rem;display:block;margin-bottom:12px;opacity:.35; }

/* ── Education cards ── */
.ef-edu-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px; }
.ef-edu-item { background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
  border-radius:12px;padding:18px; }
.ef-edu-item h4 { margin:0 0 8px;font-size:.88rem;font-weight:700;color:var(--grn); }
.ef-edu-item p  { margin:0;font-size:.8rem;line-height:1.7;color:rgba(148,163,184,.9); }

/* ── Responsive ── */
@media(max-width:1280px) { .ef-kpi-grid{grid-template-columns:repeat(2,1fr)} }
@media(max-width:960px) {
  .ef-shell{flex-direction:column;padding:14px 14px 60px}
  .ef-kpi-grid{grid-template-columns:1fr 1fr}
  .ef-grid{grid-template-columns:1fr}
  .ef-card.full{grid-column:span 1}
}
@media(max-width:560px) { .ef-kpi-grid{grid-template-columns:1fr} }
@media(max-width:375px) { .kpi-val{font-size:1.4rem} }
</style>

<!-- ════ BACKGROUND ════ -->
<div class="ef-bg">
  <div class="ef-bg-grad"></div>
  <div class="ef-bg-grid"></div>
  <div class="ef-orb ef-orb-1"></div>
  <div class="ef-orb ef-orb-2"></div>
</div>

<div class="ef-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="ef-main">

    <!-- ── PAGE HEADER ── -->
    <div class="ef-page-hdr">
      <div class="ef-page-title">
        <i class="fas fa-shield-alt"></i>
        Emergency Fund &amp; Daily Expenses
      </div>
      <?php if ($tracker_enabled): ?>
      <form class="ef-month-form" method="GET" action="">
        <select class="ef-month-sel" name="month" onchange="this.form.submit()">
          <?php foreach ($month_opts as $mo): ?>
          <option value="<?= $mo['value'] ?>" <?= $mo['value'] === $view_month ? 'selected' : '' ?>>
            <?= $mo['label'] ?>
          </option>
          <?php endforeach; ?>
        </select>
      </form>
      <?php endif; ?>
    </div>

    <!-- ── MIGRATION NOTICE ── -->
    <?php if (!$tracker_enabled): ?>
    <div class="ef-notice">
      <i class="fas fa-triangle-exclamation"></i>
      <p>
        <strong>Expense Tracker is not active yet.</strong>
        Run <code>add_daily_expenses.sql</code> in phpMyAdmin (cPanel → Databases → phpMyAdmin → SQL tab) to enable the Daily Expenses Tracker. The Emergency Fund calculator below still works.
      </p>
    </div>
    <?php endif; ?>

    <!-- ── KPI SUMMARY CARDS ── -->
    <div class="ef-kpi-grid">

      <!-- This month's total -->
      <div class="ef-kpi" style="--kc:rgba(245,158,11,.35);--kg:linear-gradient(90deg,#f59e0b,#fbbf24);--kglow:rgba(245,158,11,.07);animation-delay:.05s">
        <div class="kpi-row">
          <span class="kpi-lbl">This Month's Expenses</span>
          <span class="kpi-ico" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="fas fa-receipt"></i></span>
        </div>
        <div class="kpi-val">฿<?= number_format($monthly_total, 0) ?></div>
        <div class="kpi-note a"><i class="fas fa-calendar-day"></i> <?= date('F Y', strtotime($view_month.'-01')) ?></div>
      </div>

      <!-- Daily average -->
      <div class="ef-kpi" style="--kc:rgba(56,189,248,.3);--kg:linear-gradient(90deg,var(--cyan),var(--vio));--kglow:rgba(56,189,248,.06);animation-delay:.1s">
        <div class="kpi-row">
          <span class="kpi-lbl">Daily Average</span>
          <span class="kpi-ico" style="background:rgba(56,189,248,.12);color:var(--cyan)"><i class="fas fa-chart-line"></i></span>
        </div>
        <div class="kpi-val">฿<?= number_format($daily_avg, 0) ?></div>
        <div class="kpi-note c"><i class="fas fa-calendar-days"></i> Over <?= $days_elapsed ?> days</div>
      </div>

      <!-- Highest category -->
      <div class="ef-kpi" style="--kc:rgba(236,72,153,.3);--kg:linear-gradient(90deg,#ec4899,var(--vio));--kglow:rgba(236,72,153,.06);animation-delay:.15s">
        <div class="kpi-row">
          <span class="kpi-lbl">Top Spending Category</span>
          <span class="kpi-ico" style="background:rgba(236,72,153,.12);color:#ec4899"><i class="fas fa-fire"></i></span>
        </div>
        <?php if ($highest_cat): $hm = $cat_meta[$highest_cat]; ?>
        <div class="kpi-val" style="font-size:1.2rem"><?= $hm['en'] ?></div>
        <div class="kpi-note a"><i class="fas <?= $hm['icon'] ?>"></i> ฿<?= number_format($highest_amt, 0) ?> &nbsp;·&nbsp; <?= $hm['mm'] ?></div>
        <?php else: ?>
        <div class="kpi-val" style="font-size:1rem;opacity:.5">—</div>
        <div class="kpi-note">No expenses logged</div>
        <?php endif; ?>
      </div>

      <!-- Recommended EF (6× monthly) -->
      <div class="ef-kpi" style="--kc:rgba(16,185,129,.3);--kg:linear-gradient(90deg,var(--grn),var(--cyan));--kglow:rgba(16,185,129,.06);animation-delay:.2s">
        <div class="kpi-row">
          <span class="kpi-lbl">Recommended EF (6 months)</span>
          <span class="kpi-ico" style="background:rgba(16,185,129,.12);color:var(--grn)"><i class="fas fa-shield-alt"></i></span>
        </div>
        <div class="kpi-val">฿<?= number_format($recommended_ef, 0) ?></div>
        <div class="kpi-note g"><i class="fas fa-equals"></i> Monthly spend × 6</div>
      </div>

    </div><!-- /kpi-grid -->

    <!-- ── MAIN 2-COL GRID ── -->
    <div class="ef-grid">

      <!-- ── Emergency Fund Calculator ── -->
      <div class="ef-card" style="animation-delay:.25s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-shield-alt"></i> Emergency Fund Calculator</h3>
            <p>Set your goal and track savings progress</p>
          </div>
        </div>

        <form method="POST">
          <div class="ef-form-group">
            <label>လစဉ်အသုံးစရိတ် / Monthly Expense (฿)</label>
            <input type="number" class="ef-input" name="monthly_expense" id="ef_monthly"
              value="<?= htmlspecialchars($ef_monthly) ?>" step="0.01" min="0" placeholder="0.00">
          </div>
          <div class="ef-form-group">
            <label>အကျုံးဝင်သောလ / Months to Cover</label>
            <input type="number" class="ef-input" name="months" id="ef_months"
              value="<?= $ef_months ?>" min="1" max="24" placeholder="6">
          </div>
          <div class="ef-form-group">
            <label>ပန်းတိုင် / Target Amount (฿)
              <span style="font-weight:400;text-transform:none;font-size:.9em">(auto-calc)</span>
            </label>
            <input type="number" class="ef-input" id="ef_target"
              value="<?= $ef_target ?>" readonly>
          </div>
          <div class="ef-form-group">
            <label>လက်ရှိစုငွေ / Currently Saved (฿)</label>
            <input type="number" class="ef-input" name="current"
              value="<?= htmlspecialchars($ef_current) ?>" step="0.01" min="0" placeholder="0.00">
          </div>
          <button type="submit" class="ef-btn ef-btn-gold">
            <i class="fas fa-save"></i> သိမ်းမည် / Save
          </button>
        </form>

        <!-- Progress -->
        <div class="ef-progress-wrap">
          <div class="ef-progress-meta">
            <span><?= round($ef_progress, 1) ?>% complete</span>
            <span>฿<?= number_format($ef_current, 0) ?> of ฿<?= number_format($ef_target, 0) ?></span>
          </div>
          <div class="ef-progress-bar">
            <div class="ef-progress-fill" id="efProgressFill"></div>
          </div>
        </div>

        <div style="margin-top:14px">
          <div class="ef-stat-row">
            <span class="ef-stat-l">Currently Saved</span>
            <span class="ef-stat-v g">฿<?= number_format($ef_current, 2) ?></span>
          </div>
          <div class="ef-stat-row">
            <span class="ef-stat-l">Target</span>
            <span class="ef-stat-v c">฿<?= number_format($ef_target, 2) ?></span>
          </div>
          <div class="ef-stat-row">
            <span class="ef-stat-l">Still Needed</span>
            <span class="ef-stat-v a">฿<?= number_format(max(0, $ef_target - $ef_current), 2) ?></span>
          </div>
        </div>

        <?php if ($recommended_ef > 0): ?>
        <div class="ef-rec-banner">
          <div class="ef-rec-ico"><i class="fas fa-calculator"></i></div>
          <div>
            <div class="ef-rec-label">Based on tracked expenses</div>
            <div class="ef-rec-val">฿<?= number_format($recommended_ef, 0) ?></div>
            <div class="ef-rec-sub">฿<?= number_format($monthly_total, 0) ?>/mo × 6 months recommended target</div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── Add Expense Form ── -->
      <div class="ef-card" style="animation-delay:.3s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-plus-circle"></i> Log Daily Expense</h3>
            <p>Record today's spending in THB (฿)</p>
          </div>
        </div>

        <?php if ($tracker_enabled): ?>
        <form method="POST">
          <input type="hidden" name="action" value="add_expense">
          <div class="ef-form-group">
            <label>ရက်စွဲ / Date</label>
            <input type="date" class="ef-input" name="expense_date"
              value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
          </div>
          <div class="ef-form-group">
            <label>အမျိုးအစား / Category</label>
            <select class="ef-select" name="category">
              <option value="food">🍜 Food / အစားအသောက်</option>
              <option value="transport">🚗 Transport / သွားလာရေး</option>
              <option value="utilities">💡 Utilities / နေအိမ်သုံး</option>
              <option value="entertainment">🎮 Entertainment / ဖျော်ဖြေရေး</option>
              <option value="other">📦 Other / အခြား</option>
            </select>
          </div>
          <div class="ef-form-group">
            <label>ပမာဏ / Amount (฿ THB)</label>
            <input type="number" class="ef-input" name="amount"
              placeholder="0.00" step="0.01" min="0.01" required>
          </div>
          <div class="ef-form-group">
            <label>မှတ်ချက် / Note (optional)</label>
            <input type="text" class="ef-input" name="note"
              placeholder="e.g. Lunch at MK Restaurant" maxlength="200">
          </div>
          <button type="submit" class="ef-btn ef-btn-grn">
            <i class="fas fa-plus"></i> မှတ်တမ်းတင်မည် / Add Expense
          </button>
        </form>
        <?php else: ?>
        <div class="ef-empty">
          <i class="fas fa-database"></i>
          Run <strong>add_daily_expenses.sql</strong> in phpMyAdmin<br>to enable expense tracking.
        </div>
        <?php endif; ?>
      </div>

      <!-- ── Category Breakdown (full width) ── -->
      <div class="ef-card full" style="animation-delay:.35s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-chart-pie"></i> Category Breakdown</h3>
            <p><?= date('F Y', strtotime($view_month.'-01')) ?>
              &nbsp;·&nbsp; Total: ฿<?= number_format($monthly_total, 0) ?></p>
          </div>
        </div>

        <?php if (!empty($cat_totals)): ?>
        <div class="ef-cat-list">
          <?php
          $cat_idx = 0;
          foreach ($cat_meta as $cat_key => $cm):
            if (!isset($cat_totals[$cat_key])) continue;
            $ct  = $cat_totals[$cat_key];
            $pct = $monthly_total > 0 ? ($ct['total'] / $monthly_total) * 100 : 0;
            $delay = 0.4 + ($cat_idx++ * 0.07);
          ?>
          <div class="ef-cat-item">
            <div class="ef-cat-hdr">
              <div class="ef-cat-name">
                <span class="ef-cat-icon"
                  style="background:<?= $cm['color'] ?>22;color:<?= $cm['color'] ?>">
                  <i class="fas <?= $cm['icon'] ?>"></i>
                </span>
                <?= $cm['en'] ?>
                <span class="ef-cat-mm">(<?= $cm['mm'] ?>)</span>
                <span class="ef-cat-cnt">&nbsp;· <?= $ct['cnt'] ?> entries</span>
              </div>
              <div class="ef-cat-right">
                <span class="ef-cat-pct"><?= round($pct, 1) ?>%</span>
                <span class="ef-cat-amt" style="color:<?= $cm['color'] ?>">
                  ฿<?= number_format($ct['total'], 0) ?>
                </span>
              </div>
            </div>
            <div class="ef-cat-bar-bg">
              <div class="ef-cat-bar-fill"
                style="width:<?= round($pct, 2) ?>%;background:<?= $cm['color'] ?>;animation-delay:<?= $delay ?>s"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="ef-empty">
          <i class="fas fa-chart-pie"></i>
          No expenses logged for <?= date('F Y', strtotime($view_month.'-01')) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── Monthly Expense Table (full width) ── -->
      <div class="ef-card full" style="animation-delay:.4s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-table-list"></i> Expense Log</h3>
            <p><?= count($expense_rows) ?> entries &nbsp;·&nbsp;
              <?= date('F Y', strtotime($view_month.'-01')) ?></p>
          </div>
        </div>

        <?php if (!empty($expense_rows)): ?>
        <div class="ef-table-wrap">
          <table class="ef-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Note</th>
                <th class="r">Amount (฿)</th>
                <?php if ($is_current): ?><th></th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($expense_rows as $row):
                $cm = $cat_meta[$row['category']] ?? $cat_meta['other'];
              ?>
              <tr>
                <td class="ef-td-date"><?= date('d M', strtotime($row['expense_date'])) ?></td>
                <td>
                  <span class="ef-cat-badge"
                    style="background:<?= $cm['color'] ?>18;color:<?= $cm['color'] ?>;border:1px solid <?= $cm['color'] ?>35">
                    <i class="fas <?= $cm['icon'] ?>"></i> <?= $cm['en'] ?>
                  </span>
                </td>
                <td class="ef-td-note"><?= htmlspecialchars($row['note'] ?? '') ?></td>
                <td class="ef-td-amt">฿<?= number_format($row['amount'], 2) ?></td>
                <?php if ($is_current): ?>
                <td style="text-align:center">
                  <form method="POST" style="margin:0;display:inline">
                    <input type="hidden" name="action"     value="delete_expense">
                    <input type="hidden" name="expense_id" value="<?= (int)$row['id'] ?>">
                    <button type="submit" class="ef-del-btn"
                      onclick="return confirm('Delete this expense entry?')">
                      <i class="fas fa-trash-can"></i>
                    </button>
                  </form>
                </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="ef-td-lbl">
                  Monthly Total (<?= count($expense_rows) ?> entries)
                </td>
                <td class="ef-td-total">฿<?= number_format($monthly_total, 2) ?></td>
                <?php if ($is_current): ?><td></td><?php endif; ?>
              </tr>
            </tfoot>
          </table>
        </div>
        <?php else: ?>
        <div class="ef-empty">
          <i class="fas fa-receipt"></i>
          No expenses logged for <?= date('F Y', strtotime($view_month.'-01')) ?>.<br>
          <span style="font-size:.78rem">Use the form above to start tracking your daily spending.</span>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── Education (full width) ── -->
      <div class="ef-card full" style="animation-delay:.45s">
        <div class="ch">
          <div class="ch-l">
            <h3><i class="fas fa-graduation-cap"></i> Emergency Fund ဆိုတာဘာလဲ?</h3>
            <p>Understanding your financial safety net</p>
          </div>
        </div>
        <div class="ef-edu-grid">
          <div class="ef-edu-item">
            <h4>Emergency Fund ဆိုတာ</h4>
            <p>မမျှော်လင့်ထားတဲ့ အရေးပေါ်အခြေအနေတွေမှာ အသုံးပြုရန် ကြိုတင်စုထားတဲ့ ငွေဖြစ်ပါတယ်။</p>
          </div>
          <div class="ef-edu-item">
            <h4>ဘာကြောင့်လိုအပ်လဲ</h4>
            <p>Emergency Fund မရှိပါက အရေးပေါ်အခြေအနေတွင် အကြွေးယူရနိုင်ပါတယ်။</p>
          </div>
          <div class="ef-edu-item">
            <h4>ဘယ်လောက်စုသင့်လဲ</h4>
            <p>လစဉ်အသုံးစရိတ်၏ ၃ လမှ ၆ လအထိ စုထားခြင်းကို အကြံပြုပါသည်။</p>
          </div>
          <div class="ef-edu-item">
            <h4>ဘယ်နေရာမှာထားသင့်လဲ</h4>
            <p>Savings Account သို့မဟုတ် Money Market Account တွင်ထားသင့်သည်။</p>
          </div>
          <div class="ef-edu-item">
            <h4>ဘယ်အချိန်သုံးသင့်လဲ</h4>
            <p>အလုပ်ပျက်ခြင်း၊ မကျန်းမာခြင်း၊ အရေးပေါ်ကုန်ကျစရိတ်များတွင် အသုံးပြုနိုင်သည်။</p>
          </div>
          <div class="ef-edu-item">
            <h4>ဘယ်လိုစတင်စုမလဲ</h4>
            <p>ဝင်ငွေ၏ 10% – 20% ကို Emergency Fund အတွက် စုဆောင်းပါ။</p>
          </div>
        </div>
      </div>

    </div><!-- /ef-grid -->
  </main>
</div><!-- /ef-shell -->

<script>
/* ── EF progress bar ── */
setTimeout(function() {
    var f = document.getElementById('efProgressFill');
    if (f) f.style.width = '<?= round($ef_progress, 2) ?>%';
}, 400);

/* ── Target auto-calculation ── */
var efMonthly = document.getElementById('ef_monthly');
var efMonths  = document.getElementById('ef_months');
var efTarget  = document.getElementById('ef_target');
function recalcTarget() {
    if (!efMonthly || !efMonths || !efTarget) return;
    var m  = parseFloat(efMonthly.value) || 0;
    var mo = parseFloat(efMonths.value)  || 0;
    efTarget.value = (m * mo).toFixed(2);
}
if (efMonthly) efMonthly.addEventListener('input', recalcTarget);
if (efMonths)  efMonths.addEventListener('input',  recalcTarget);
</script>

<?php include 'includes/footer.php'; ?>
