<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once 'includes/db.php';
$user_id  = (int)$_SESSION['user_id'];
$username = $_SESSION['user_name'] ?? 'User';

// Pre-load a saved backtest if share token in URL
$preload_config  = null;
$preload_results = null;
$preload_title   = '';
if (!empty($_GET['backtest_id'])) {
    $tk   = trim($_GET['backtest_id']);
    $stmt = $conn->prepare(
        "SELECT user_id, title, config_json, results_json, is_public
         FROM saved_backtests WHERE share_token=? LIMIT 1"
    );
    $stmt->bind_param('s', $tk);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && ($row['is_public'] || $row['user_id'] == $user_id)) {
        $preload_config  = $row['config_json'];
        $preload_results = $row['results_json'];
        $preload_title   = $row['title'];
    }
}

include 'includes/header.php';
?>
<style>
/* ════════════════════════════════════════════════════
   PORTFOLIO BACKTESTER — gold/dark theme
   ════════════════════════════════════════════════════ */
:root {
  --bg:   #020617; --card: rgba(10,18,38,.88); --bdr: rgba(255,255,255,.07);
  --gold: #d4af37; --gold2:#f5d679; --cyan: #38bdf8; --vio: #a78bfa;
  --grn:  #10b981; --red:  #ef4444; --muted:rgba(148,163,184,.75); --ink:#f1f5f9;
}
@keyframes bUp { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:none} }
body  { background:var(--bg); color:var(--ink); font-family:'Padauk','Segoe UI',sans-serif; margin:0; }
.bt-shell { display:flex; gap:24px; padding:26px 26px 70px; min-height:100vh; position:relative; z-index:1; }
.bt-main  { flex:1; min-width:0; }

/* ── PAGE HEADER ── */
.bt-hdr { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.bt-hdr h1 { margin:0; font-size:1.4rem; font-weight:900;
  background:linear-gradient(135deg,#f1f5f9 30%,var(--gold) 100%);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.bt-hdr p  { margin:4px 0 0; font-size:.78rem; color:var(--muted); }
.bt-hdr-btns { display:flex; gap:10px; }
.bt-btn { padding:9px 16px; border-radius:10px; font-weight:700; font-size:.82rem;
  border:none; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:7px; transition:all .22s; }
.bt-btn-ghost { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); color:var(--ink); }
.bt-btn-ghost:hover { background:rgba(212,175,55,.1); border-color:rgba(212,175,55,.4); color:var(--gold); }
.bt-btn-gold  { background:linear-gradient(135deg,var(--gold),var(--gold2)); color:#020617; box-shadow:0 4px 18px rgba(212,175,55,.3); }
.bt-btn-gold:hover { transform:translateY(-2px); box-shadow:0 8px 26px rgba(212,175,55,.45); }

/* ── TWO-PANEL LAYOUT ── */
.bt-panels { display:grid; grid-template-columns:320px 1fr; gap:18px; margin-bottom:18px; }

/* ── CARD BASE ── */
.bt-card { background:var(--card); backdrop-filter:blur(18px); border:1px solid var(--bdr);
  border-radius:18px; padding:22px; animation:bUp .5s ease both; }
.bt-card-title { font-size:.82rem; font-weight:800; text-transform:uppercase; letter-spacing:1.2px;
  color:var(--gold); display:flex; align-items:center; gap:8px; margin-bottom:18px; }

/* ── FORM ELEMENTS ── */
.bt-field   { margin-bottom:14px; }
.bt-label   { display:block; font-size:.74rem; font-weight:700; color:var(--muted); margin-bottom:5px; text-transform:uppercase; letter-spacing:.8px; }
.bt-input, .bt-select {
  width:100%; padding:9px 13px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
  border-radius:9px; color:var(--ink); font-size:.87rem; font-family:inherit; box-sizing:border-box;
  transition:border-color .2s; }
.bt-input:focus, .bt-select:focus { outline:none; border-color:rgba(212,175,55,.45); background:rgba(212,175,55,.05); }
.bt-select option { background:#0f172a; }
.bt-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.bt-toggle-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0;
  border-bottom:1px solid rgba(255,255,255,.04); }
.bt-toggle-row label { font-size:.82rem; color:var(--ink); cursor:pointer; }
.bt-toggle { position:relative; width:40px; height:22px; }
.bt-toggle input { opacity:0; width:0; height:0; }
.bt-toggle-slider { position:absolute; inset:0; background:rgba(255,255,255,.12); border-radius:99px; cursor:pointer; transition:.3s; }
.bt-toggle-slider::before { content:""; position:absolute; width:16px; height:16px; left:3px; top:3px;
  background:#fff; border-radius:50%; transition:.3s; }
.bt-toggle input:checked + .bt-toggle-slider { background:var(--gold); }
.bt-toggle input:checked + .bt-toggle-slider::before { transform:translateX(18px); }

/* ── CASHFLOW SECTION ── */
.bt-cf-list { margin-top:12px; }
.bt-cf-item { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:10px;
  padding:10px 12px; margin-bottom:8px; position:relative; }
.bt-cf-item .rm-btn { position:absolute; top:8px; right:10px; background:rgba(239,68,68,.12);
  border:1px solid rgba(239,68,68,.25); color:#ef4444; border-radius:6px; cursor:pointer;
  font-size:.7rem; padding:3px 8px; }
.bt-cf-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-top:8px; }
.bt-add-btn { width:100%; padding:8px; background:rgba(255,255,255,.04); border:1px dashed rgba(255,255,255,.15);
  border-radius:9px; color:var(--muted); font-size:.78rem; cursor:pointer; transition:all .22s; }
.bt-add-btn:hover { background:rgba(212,175,55,.07); border-color:rgba(212,175,55,.3); color:var(--gold); }

/* ── PORTFOLIO CARDS ── */
.bt-port-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.bt-port-card { background:rgba(10,18,38,.9); border:1px solid rgba(255,255,255,.08); border-radius:14px;
  padding:16px; position:relative; transition:border-color .22s; }
.bt-port-card:hover { border-color:rgba(212,175,55,.2); }
.bt-port-head { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.bt-color-dot { width:14px; height:14px; border-radius:50%; flex-shrink:0; cursor:pointer;
  border:2px solid rgba(255,255,255,.2); }
.bt-port-name { flex:1; background:transparent; border:none; border-bottom:1px solid rgba(255,255,255,.1);
  color:var(--ink); font-size:.92rem; font-weight:700; padding:4px 0; font-family:inherit; }
.bt-port-name:focus { outline:none; border-bottom-color:var(--gold); }
.bt-port-del { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.2); color:#ef4444;
  border-radius:7px; padding:4px 9px; cursor:pointer; font-size:.75rem; flex-shrink:0; }

/* ── ASSET TABLE ── */
.bt-asset-tbl { width:100%; border-collapse:collapse; font-size:.8rem; margin-bottom:8px; }
.bt-asset-tbl th { text-align:left; padding:5px 6px; color:var(--muted); font-size:.68rem; text-transform:uppercase; letter-spacing:.8px; font-weight:700; }
.bt-asset-tbl td { padding:5px 6px; }
.bt-asset-tbl td input { padding:4px 8px; border-radius:7px; font-size:.8rem; }
.bt-asset-tbl .tick-in { width:80px; }
.bt-asset-tbl .w-in    { width:60px; text-align:right; }
.bt-w-total { font-size:.75rem; font-weight:700; padding:4px 6px; border-radius:6px; margin-bottom:8px; display:inline-block; }
.bt-w-ok  { background:rgba(16,185,129,.12); color:var(--grn); }
.bt-w-err { background:rgba(239,68,68,.12); color:#ef4444; }
.bt-reb-row { display:flex; align-items:center; gap:8px; margin-top:8px; }
.bt-reb-row label { font-size:.74rem; color:var(--muted); white-space:nowrap; }
.bt-reb-row select { flex:1; padding:5px 9px; font-size:.78rem; border-radius:7px; }

/* Add asset row */
.bt-add-asset-row { display:flex; gap:7px; margin-bottom:6px; }
.bt-add-asset-row input  { flex:1; padding:6px 10px; border-radius:7px; font-size:.8rem; text-transform:uppercase; }
.bt-add-asset-row button { padding:6px 12px; border-radius:7px; font-size:.8rem; white-space:nowrap; }

/* Color picker popup */
.color-picker-wrap { position:relative; }
.color-picker-popup { display:none; position:absolute; left:0; top:20px; z-index:100;
  background:#0f172a; border:1px solid rgba(255,255,255,.12); border-radius:10px; padding:10px; box-shadow:0 8px 32px rgba(0,0,0,.5); }
.color-picker-popup.show { display:flex; gap:6px; flex-wrap:wrap; width:134px; }
.cp-swatch { width:22px; height:22px; border-radius:50%; cursor:pointer; border:2px solid transparent; transition:all .15s; }
.cp-swatch:hover { transform:scale(1.25); border-color:rgba(255,255,255,.4); }
.cp-swatch.active { border-color:#fff; }

/* Portfolio presets/add bar */
.bt-port-toolbar { display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
.bt-preset-btn { padding:7px 13px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1);
  border-radius:8px; color:var(--muted); font-size:.78rem; cursor:pointer; transition:all .2s; }
.bt-preset-btn:hover { background:rgba(56,189,248,.08); border-color:rgba(56,189,248,.3); color:var(--cyan); }

/* ── BACKTEST BUTTON ── */
.bt-run-wrap { text-align:center; margin:0 0 24px; }
.bt-run-btn { padding:14px 48px; font-size:1rem; font-weight:900; border:none; border-radius:14px; cursor:pointer;
  font-family:inherit; background:linear-gradient(135deg,var(--gold),var(--gold2)); color:#020617;
  box-shadow:0 6px 30px rgba(212,175,55,.35); letter-spacing:.5px; transition:all .25s; }
.bt-run-btn:hover { transform:translateY(-3px); box-shadow:0 12px 40px rgba(212,175,55,.5); }
.bt-run-btn:disabled { opacity:.45; transform:none; cursor:not-allowed; }

/* ── LOADING ── */
.bt-loading { display:none; text-align:center; padding:40px 0; }
.bt-spinner { width:48px; height:48px; border:3px solid rgba(212,175,55,.2);
  border-top-color:var(--gold); border-radius:50%; animation:spin .8s linear infinite; margin:0 auto 14px; }
@keyframes spin { to { transform:rotate(360deg); } }
.bt-loading p { color:var(--muted); font-size:.85rem; }

/* ── ERROR BANNER ── */
.bt-error { display:none; background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.3);
  border-radius:12px; padding:14px 18px; color:#ef4444; margin-bottom:18px; font-size:.88rem; }

/* ── RESULTS SECTION ── */
.bt-results { display:none; }
.bt-results.visible { display:block; animation:bUp .5s ease both; }

/* Metrics table */
.bt-metrics-wrap { overflow-x:auto; }
.bt-metrics-tbl { width:100%; border-collapse:collapse; font-size:.85rem; min-width:540px; }
.bt-metrics-tbl th { padding:11px 14px; text-align:left; font-size:.7rem; text-transform:uppercase;
  letter-spacing:1px; color:var(--muted); background:rgba(255,255,255,.03); }
.bt-metrics-tbl td { padding:10px 14px; border-bottom:1px solid rgba(255,255,255,.04); }
.bt-metrics-tbl tr:hover td { background:rgba(255,255,255,.02); }
.bt-metrics-tbl .metric-lbl { color:var(--muted); font-size:.78rem; }
.bt-val-up   { color:var(--grn); font-weight:700; }
.bt-val-down { color:var(--red); font-weight:700; }
.bt-val-neu  { color:var(--ink); font-weight:700; }

/* Annual / heatmap / rolling layout */
.bt-chart-row { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }

/* Monthly heatmap */
.hm-wrap { overflow-x:auto; }
.hm-table { border-collapse:collapse; font-size:.72rem; white-space:nowrap; }
.hm-table th { padding:5px 8px; color:var(--muted); font-weight:700; text-align:center; font-size:.66rem; }
.hm-table td { padding:5px 7px; text-align:center; border-radius:5px; min-width:36px; font-weight:600; }
.hm-yr { color:var(--muted); font-weight:700; padding-right:10px!important; text-align:left!important; }

/* Correlation matrix */
.corr-tbl { border-collapse:collapse; font-size:.8rem; }
.corr-tbl th, .corr-tbl td { padding:9px 14px; text-align:center; }
.corr-tbl th { font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.8px; }
.corr-tbl td { border-radius:8px; font-weight:700; }

/* Save banner */
.bt-save-banner { background:rgba(212,175,55,.07); border:1px solid rgba(212,175,55,.2);
  border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin-bottom:18px; }
.bt-save-banner input { flex:1; min-width:180px; padding:8px 12px; background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12); border-radius:8px; color:var(--ink); font-size:.85rem; }
.bt-share-link { display:none; flex:1; padding:7px 12px; background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.08); border-radius:8px; color:var(--cyan); font-size:.8rem;
  word-break:break-all; }

/* Tabs for heatmap portfolio selector */
.bt-tab-row { display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap; }
.bt-tab { padding:5px 14px; border-radius:8px; border:1px solid rgba(255,255,255,.1);
  background:rgba(255,255,255,.03); color:var(--muted); font-size:.78rem; cursor:pointer; transition:all .2s; }
.bt-tab.active { background:rgba(212,175,55,.1); border-color:rgba(212,175,55,.3); color:var(--gold); }

/* ── RESPONSIVE ── */
@media(max-width:1200px) { .bt-panels { grid-template-columns:1fr; } .bt-port-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:900px)  { .bt-shell { flex-direction:column; padding:14px 14px 60px; } .bt-chart-row { grid-template-columns:1fr; } .bt-port-grid { grid-template-columns:1fr; } }
@media(max-width:600px)  { .bt-port-grid { grid-template-columns:1fr; } }
</style>

<!-- ── BACKGROUND (reuses dashboard particles) ── -->
<div class="d-bg">
  <div class="d-bg-grad"></div>
  <div class="d-bg-grid"></div>
  <div class="d-orb d-orb-1"></div>
  <div class="d-orb d-orb-2"></div>
</div>

<div class="bt-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="bt-main">

    <!-- PAGE HEADER -->
    <div class="bt-hdr">
      <div>
        <h1><i class="fas fa-flask" style="font-size:1.1rem"></i> Portfolio Backtester &nbsp;<span style="font-size:.75rem;color:var(--muted);-webkit-text-fill-color:var(--muted)">နောင်ခံစစ်တမ်း</span></h1>
        <p>Test your portfolios against history. US/SET/YSX markets supported.</p>
      </div>
      <div class="bt-hdr-btns">
        <button class="bt-btn bt-btn-ghost" onclick="loadSavedBacktest()"><i class="fas fa-folder-open"></i> Load</button>
      </div>
    </div>

    <div id="btError" class="bt-error"></div>

    <!-- TWO PANELS: PARAMS + PORTFOLIOS -->
    <div class="bt-panels">

      <!-- ══ PARAMETERS PANEL ══ -->
      <div class="bt-card" id="paramsPanel">
        <div class="bt-card-title"><i class="fas fa-sliders"></i> Parameters</div>

        <!-- Date Range -->
        <div class="bt-field">
          <label class="bt-label">Date Range</label>
          <select id="dateRangeMode" class="bt-select" onchange="toggleCustomDates()">
            <option value="full">Full History (Max)</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        <div id="customDateRow" class="bt-row2" style="display:none">
          <div class="bt-field">
            <label class="bt-label">Start (YYYY-MM)</label>
            <input type="month" id="startDate" class="bt-input" value="2010-01">
          </div>
          <div class="bt-field">
            <label class="bt-label">End (YYYY-MM)</label>
            <input type="month" id="endDate" class="bt-input" value="<?= date('Y-m') ?>">
          </div>
        </div>

        <!-- Initial Value -->
        <div class="bt-field">
          <label class="bt-label">Starting Portfolio Value ($)</label>
          <input type="number" id="initValue" class="bt-input" value="10000" min="100" step="100">
        </div>

        <!-- Inflation toggle -->
        <div class="bt-toggle-row">
          <label for="inflationToggle">Adjust for Inflation (~2.5% CPI)</label>
          <label class="bt-toggle">
            <input type="checkbox" id="inflationToggle">
            <span class="bt-toggle-slider"></span>
          </label>
        </div>

        <!-- Rolling window -->
        <div class="bt-field" style="margin-top:14px">
          <label class="bt-label">Rolling Window (months)</label>
          <input type="number" id="rollingWindow" class="bt-input" value="60" min="12" max="120" step="6">
        </div>

        <!-- Benchmark -->
        <div class="bt-field">
          <label class="bt-label">Benchmark Ticker</label>
          <input type="text" id="benchmarkTicker" class="bt-input" value="SPY"
                 placeholder="SPY / ^SET.BK / custom" style="text-transform:uppercase">
        </div>

        <!-- Recurring Cashflows -->
        <div class="bt-field" style="margin-top:18px">
          <div class="bt-card-title" style="font-size:.74rem;margin-bottom:10px"><i class="fas fa-money-bill-transfer"></i> Recurring Cashflows</div>
          <div id="cfList" class="bt-cf-list"></div>
          <button class="bt-add-btn" onclick="addCashflow()">+ Add Cashflow Leg</button>
        </div>

        <!-- One-time Cashflows -->
        <div class="bt-field" style="margin-top:14px">
          <div class="bt-card-title" style="font-size:.74rem;margin-bottom:10px"><i class="fas fa-calendar-plus"></i> One-Time Cashflows</div>
          <div id="otCfList" class="bt-cf-list"></div>
          <button class="bt-add-btn" onclick="addOnetimeCf()">+ Add One-Time Cashflow</button>
        </div>
      </div>

      <!-- ══ PORTFOLIOS PANEL ══ -->
      <div class="bt-card" id="portfoliosPanel">
        <div class="bt-card-title"><i class="fas fa-briefcase"></i> Portfolios (max 4)</div>

        <div class="bt-port-toolbar">
          <button class="bt-preset-btn" onclick="BT.addPortfolio()"><i class="fas fa-plus"></i> Empty Portfolio</button>
          <button class="bt-preset-btn" onclick="BT.addPreset('60_40')">60/40 Classic</button>
          <button class="bt-preset-btn" onclick="BT.addPreset('all_weather')">All Weather</button>
          <button class="bt-preset-btn" onclick="BT.addPreset('3fund')">3-Fund</button>
          <button class="bt-preset-btn" onclick="BT.addPreset('golden_btfy')">Golden Butterfly</button>
        </div>

        <div id="portGrid" class="bt-port-grid">
          <!-- Portfolio cards inserted by JS -->
        </div>
        <p id="noPortMsg" style="color:var(--muted);font-size:.85rem;text-align:center;padding:20px 0">
          Add a portfolio to begin. <span style="opacity:.6">ပထမဆုံး portfolio ထည့်ပါ။</span>
        </p>
      </div>
    </div><!-- /bt-panels -->

    <!-- BACKTEST BUTTON -->
    <div class="bt-run-wrap">
      <button class="bt-run-btn" id="runBtn" onclick="runBacktest()">
        <i class="fas fa-bolt"></i> &nbsp;BACKTEST &nbsp;<span style="font-size:.78rem;opacity:.7">စစ်ဆေးရန်</span>
      </button>
    </div>

    <!-- LOADING -->
    <div class="bt-loading" id="btLoading">
      <div class="bt-spinner"></div>
      <p>Running backtest — fetching price data &amp; calculating metrics…</p>
    </div>

    <!-- ══ RESULTS ══ -->
    <div class="bt-results" id="btResults">

      <!-- Save banner -->
      <div class="bt-save-banner">
        <i class="fas fa-bookmark" style="color:var(--gold)"></i>
        <input type="text" id="saveTitle" placeholder="Backtest name (optional)" maxlength="120">
        <button class="bt-btn bt-btn-gold" onclick="saveBacktest()"><i class="fas fa-save"></i> Save</button>
        <label style="font-size:.78rem;color:var(--muted);display:flex;align-items:center;gap:6px">
          <input type="checkbox" id="makePublic"> Public link
        </label>
        <span id="shareLink" class="bt-share-link"></span>
      </div>

      <!-- Growth Chart -->
      <div class="bt-card" style="margin-bottom:18px">
        <div class="bt-card-title"><i class="fas fa-chart-area"></i> Portfolio Growth &nbsp;
          <span id="growthDateRange" style="font-size:.7rem;font-weight:400;color:var(--muted)"></span></div>
        <canvas id="growthChart" height="80"></canvas>
      </div>

      <!-- Metrics Table -->
      <div class="bt-card" style="margin-bottom:18px">
        <div class="bt-card-title"><i class="fas fa-table-list"></i> Performance Metrics</div>
        <div class="bt-metrics-wrap">
          <table class="bt-metrics-tbl" id="metricsTable">
            <thead><tr id="metricsTHead"></tr></thead>
            <tbody id="metricsTBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Annual Returns + Drawdown -->
      <div class="bt-chart-row">
        <div class="bt-card">
          <div class="bt-card-title"><i class="fas fa-chart-bar"></i> Annual Returns (%)</div>
          <canvas id="annualChart" height="130"></canvas>
        </div>
        <div class="bt-card">
          <div class="bt-card-title"><i class="fas fa-arrow-trend-down"></i> Drawdown (%)</div>
          <canvas id="drawdownChart" height="130"></canvas>
        </div>
      </div>

      <!-- Monthly Heatmap + Rolling Returns -->
      <div class="bt-chart-row">
        <div class="bt-card">
          <div class="bt-card-title"><i class="fas fa-fire"></i> Monthly Returns Heatmap (%)</div>
          <div class="bt-tab-row" id="hmTabs"></div>
          <div class="hm-wrap" id="heatmapWrap"></div>
        </div>
        <div class="bt-card">
          <div class="bt-card-title"><i class="fas fa-rotate"></i> Rolling Returns (<?= '' ?>CAGR %)</div>
          <canvas id="rollingChart" height="130"></canvas>
        </div>
      </div>

      <!-- Correlation Matrix -->
      <div class="bt-card" id="corrCard" style="display:none;margin-bottom:18px">
        <div class="bt-card-title"><i class="fas fa-grid-2"></i> Portfolio Correlation Matrix</div>
        <div id="corrMatrixWrap"></div>
      </div>

    </div><!-- /bt-results -->
  </main>
</div><!-- /bt-shell -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
/* ═══════════════════════════════════════════════════════════════
   BACKTESTER — STATE & CORE LOGIC
   ═══════════════════════════════════════════════════════════════ */

const BT = {
    portfolios: [],
    charts: {},
    lastResults: null,
    cfCounter:   0,
    otCounter:   0,

    COLORS: ['#d4af37','#38bdf8','#a78bfa','#10b981'],
    SWATCH_COLORS: [
        '#d4af37','#f5d679','#38bdf8','#0ea5e9','#a78bfa',
        '#8b5cf6','#10b981','#34d399','#ef4444','#f97316',
        '#ec4899','#f43f5e','#64748b','#94a3b8'
    ],
    PRESETS: {
        '60_40':      { name:'60/40 Classic',   assets:[{ticker:'VOO',weight:60,name:'Vanguard S&P 500'},{ticker:'BND',weight:40,name:'Vanguard Bonds'}] },
        'all_weather':{ name:'All Weather',      assets:[{ticker:'VTI',weight:30,name:'Total Market'},{ticker:'TLT',weight:40,name:'Long-Term Bond'},{ticker:'IEF',weight:15,name:'Intermediate Bond'},{ticker:'GLD',weight:7.5,name:'Gold'},{ticker:'DBC',weight:7.5,name:'Commodities'}] },
        '3fund':      { name:'3-Fund Portfolio', assets:[{ticker:'VTI',weight:60,name:'Total US'},{ticker:'VXUS',weight:30,name:'International'},{ticker:'BND',weight:10,name:'Bonds'}] },
        'golden_btfy':{ name:'Golden Butterfly', assets:[{ticker:'VTI',weight:20,name:'Total Market'},{ticker:'SHY',weight:20,name:'Short Bond'},{ticker:'TLT',weight:20,name:'Long Bond'},{ticker:'GLD',weight:20,name:'Gold'},{ticker:'IWN',weight:20,name:'Small Value'}] }
    },

    addPortfolio(preset) {
        if (this.portfolios.length >= 4) { showError('Maximum 4 portfolios allowed.'); return; }
        const colorIdx = this.portfolios.length;
        const p = {
            id:        Date.now() + Math.random(),
            name:      preset ? preset.name : ('Portfolio ' + (this.portfolios.length + 1)),
            color:     this.COLORS[colorIdx % this.COLORS.length],
            assets:    preset ? preset.assets.map(a => ({...a})) : [],
            rebalance: 'yearly'
        };
        this.portfolios.push(p);
        this.renderPortfolios();
    },

    addPreset(key) {
        const preset = this.PRESETS[key];
        if (preset) this.addPortfolio(preset);
    },

    removePortfolio(id) {
        this.portfolios = this.portfolios.filter(p => p.id !== id);
        this.renderPortfolios();
    },

    addAsset(pid, ticker, weight, name) {
        const p = this.portfolios.find(p => p.id === pid);
        if (!p) return;
        ticker = ticker.toUpperCase().trim();
        if (!ticker) return;
        const existing = p.assets.find(a => a.ticker === ticker);
        if (existing) { showError('Ticker ' + ticker + ' already in this portfolio.'); return; }
        p.assets.push({ ticker, weight: parseFloat(weight) || 10, name: name || ticker });
        this.renderPortfolios();
    },

    removeAsset(pid, ticker) {
        const p = this.portfolios.find(p => p.id === pid);
        if (!p) return;
        p.assets = p.assets.filter(a => a.ticker !== ticker);
        this.renderPortfolios();
    },

    updateAsset(pid, ticker, field, value) {
        const p  = this.portfolios.find(p => p.id === pid);
        if (!p) return;
        const a = p.assets.find(a => a.ticker === ticker);
        if (!a) return;
        a[field] = (field === 'weight') ? parseFloat(value) || 0 : value;
        this.updateWeightSum(pid);
    },

    updatePortfolioField(pid, field, value) {
        const p = this.portfolios.find(p => p.id === pid);
        if (p) p[field] = value;
    },

    updateWeightSum(pid) {
        const p = this.portfolios.find(p => p.id === pid);
        if (!p) return;
        const sum = p.assets.reduce((s, a) => s + (parseFloat(a.weight) || 0), 0);
        const el  = document.getElementById('wsum_' + pid);
        if (!el) return;
        el.textContent = sum.toFixed(1) + '%';
        el.className   = 'bt-w-total ' + (Math.abs(sum - 100) < 0.1 ? 'bt-w-ok' : 'bt-w-err');
    },

    renderPortfolios() {
        const grid = document.getElementById('portGrid');
        const msg  = document.getElementById('noPortMsg');
        grid.innerHTML = '';
        msg.style.display = this.portfolios.length ? 'none' : 'block';
        this.portfolios.forEach(p => grid.insertAdjacentHTML('beforeend', this.portCardHTML(p)));
        this.portfolios.forEach(p => this.updateWeightSum(p.id));
    },

    portCardHTML(p) {
        const tid = p.id;
        const aRows = p.assets.map(a =>
            `<tr>
                <td><input class="bt-input tick-in" value="${esc(a.ticker)}" readonly style="width:78px;font-weight:700;color:var(--ink)"></td>
                <td><input class="bt-input w-in" type="number" value="${a.weight}" min="0" max="100" step="0.5"
                    oninput="BT.updateAsset(${tid},'${esc(a.ticker)}','weight',this.value)" style="width:62px"></td>
                <td style="color:var(--muted);font-size:.78rem">${esc(a.name||a.ticker)}</td>
                <td><button class="bt-port-del" style="padding:3px 7px;font-size:.68rem" onclick="BT.removeAsset(${tid},'${esc(a.ticker)}')">×</button></td>
            </tr>`
        ).join('');

        const swatches = this.SWATCH_COLORS.map(c =>
            `<div class="cp-swatch ${c===p.color?'active':''}" style="background:${c}"
              onclick="BT.setColor(${tid},'${c}',this)"></div>`
        ).join('');

        return `
        <div class="bt-port-card" id="pc_${tid}">
            <div class="bt-port-head">
                <div class="color-picker-wrap" id="cpw_${tid}">
                    <div class="bt-color-dot" style="background:${p.color}" onclick="toggleColorPicker(${tid})"></div>
                    <div class="color-picker-popup" id="cpp_${tid}">${swatches}</div>
                </div>
                <input class="bt-port-name" value="${esc(p.name)}"
                       oninput="BT.updatePortfolioField(${tid},'name',this.value)"
                       placeholder="Portfolio name">
                <button class="bt-port-del" onclick="BT.removePortfolio(${tid})">×</button>
            </div>
            <table class="bt-asset-tbl">
              <thead><tr><th>Ticker</th><th>Weight</th><th>Name</th><th></th></tr></thead>
              <tbody>${aRows}</tbody>
            </table>
            <div id="wsum_${tid}" class="bt-w-total bt-w-err">0%</div>
            <div class="bt-add-asset-row">
                <input type="text" id="nta_${tid}" class="bt-input" placeholder="Ticker (e.g. AAPL)" maxlength="15"
                       style="text-transform:uppercase"
                       onkeydown="if(event.key==='Enter'){addAssetFromInput(${tid})}">
                <input type="number" id="ntw_${tid}" class="bt-input" placeholder="Wt%" value="10" min="0" max="100" style="width:70px"
                       onkeydown="if(event.key==='Enter'){addAssetFromInput(${tid})}">
                <button class="bt-btn bt-btn-ghost" style="padding:6px 12px;font-size:.78rem"
                        onclick="addAssetFromInput(${tid})">+ Add</button>
            </div>
            <div class="bt-reb-row">
                <label>Rebalance:</label>
                <select class="bt-select" onchange="BT.updatePortfolioField(${tid},'rebalance',this.value)">
                    <option value="none"      ${p.rebalance==='none'     ?'selected':''}>Never</option>
                    <option value="monthly"   ${p.rebalance==='monthly'  ?'selected':''}>Monthly</option>
                    <option value="quarterly" ${p.rebalance==='quarterly'?'selected':''}>Quarterly</option>
                    <option value="yearly"    ${p.rebalance==='yearly'   ?'selected':''}>Yearly</option>
                </select>
            </div>
        </div>`;
    },

    setColor(pid, color, el) {
        const p = this.portfolios.find(p => p.id === pid);
        if (!p) return;
        p.color = color;
        document.querySelector('#pc_' + pid + ' .bt-color-dot').style.background = color;
        document.querySelectorAll('#cpp_' + pid + ' .cp-swatch').forEach(s => {
            s.classList.toggle('active', s.style.background === hexToRgb(color) || s.dataset && s.getAttribute('onclick') && s.getAttribute('onclick').includes("'" + color + "'"));
        });
        document.getElementById('cpp_' + pid).classList.remove('show');
    }
};

function esc(s) { return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function hexToRgb(hex) { /* unused but kept */ return hex; }

function toggleColorPicker(pid) {
    const el = document.getElementById('cpp_' + pid);
    document.querySelectorAll('.color-picker-popup.show').forEach(e => { if (e !== el) e.classList.remove('show'); });
    el.classList.toggle('show');
}
document.addEventListener('click', e => {
    if (!e.target.closest('.color-picker-wrap')) {
        document.querySelectorAll('.color-picker-popup.show').forEach(el => el.classList.remove('show'));
    }
});

function toggleCustomDates() {
    const mode = document.getElementById('dateRangeMode').value;
    document.getElementById('customDateRow').style.display = mode === 'custom' ? 'grid' : 'none';
}

let cfId = 0;
function addCashflow() {
    const id = ++cfId;
    const html = `
    <div class="bt-cf-item" id="cf_${id}">
        <button class="rm-btn" onclick="document.getElementById('cf_${id}').remove()">×</button>
        <div class="bt-cf-grid">
            <div>
                <label class="bt-label">Direction</label>
                <select class="bt-select" id="cfdir_${id}" style="padding:6px 10px;font-size:.8rem">
                    <option value="withdrawal">Withdrawal</option>
                    <option value="contribution">Contribution</option>
                </select>
            </div>
            <div>
                <label class="bt-label">Type</label>
                <select class="bt-select" id="cftype_${id}" style="padding:6px 10px;font-size:.8rem">
                    <option value="fixed">$ Amount</option>
                    <option value="percent">% of Portfolio</option>
                </select>
            </div>
            <div>
                <label class="bt-label">Amount</label>
                <input type="number" id="cfamt_${id}" class="bt-input" value="500" min="0" style="padding:6px 10px;font-size:.8rem">
            </div>
        </div>
        <div class="bt-cf-grid" style="margin-top:4px">
            <div>
                <label class="bt-label">Frequency</label>
                <select class="bt-select" id="cffreq_${id}" style="padding:6px 10px;font-size:.8rem">
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div>
                <label class="bt-label">Start Offset (months)</label>
                <input type="number" id="cfoff_${id}" class="bt-input" value="0" min="0" style="padding:6px 10px;font-size:.8rem">
            </div>
        </div>
    </div>`;
    document.getElementById('cfList').insertAdjacentHTML('beforeend', html);
}

let otId = 0;
function addOnetimeCf() {
    const id = ++otId;
    const html = `
    <div class="bt-cf-item" id="otcf_${id}">
        <button class="rm-btn" onclick="document.getElementById('otcf_${id}').remove()">×</button>
        <div class="bt-cf-grid">
            <div>
                <label class="bt-label">Date (YYYY-MM)</label>
                <input type="month" id="otdate_${id}" class="bt-input" style="padding:6px 10px;font-size:.8rem">
            </div>
            <div>
                <label class="bt-label">Direction</label>
                <select class="bt-select" id="otdir_${id}" style="padding:6px 10px;font-size:.8rem">
                    <option value="withdrawal">Withdrawal</option>
                    <option value="contribution">Contribution</option>
                </select>
            </div>
            <div>
                <label class="bt-label">Amount ($)</label>
                <input type="number" id="otamt_${id}" class="bt-input" value="5000" min="0" style="padding:6px 10px;font-size:.8rem">
            </div>
        </div>
    </div>`;
    document.getElementById('otCfList').insertAdjacentHTML('beforeend', html);
}

function addAssetFromInput(pid) {
    const tickerEl  = document.getElementById('nta_' + pid);
    const weightEl  = document.getElementById('ntw_' + pid);
    const ticker    = tickerEl.value.toUpperCase().trim();
    const weight    = parseFloat(weightEl.value) || 10;
    if (!ticker) return;
    BT.addAsset(pid, ticker, weight, ticker);
    tickerEl.value  = '';
    weightEl.value  = '10';
    tickerEl.focus();
}

function buildConfig() {
    const mode = document.getElementById('dateRangeMode').value;
    const params = {
        initial_value:    parseFloat(document.getElementById('initValue').value)    || 10000,
        inflation_adjust: document.getElementById('inflationToggle').checked,
        rolling_window:   parseInt(document.getElementById('rollingWindow').value)  || 60,
        benchmark:        document.getElementById('benchmarkTicker').value.toUpperCase().trim(),
        start_date:       mode === 'custom' ? document.getElementById('startDate').value : null,
        end_date:         mode === 'custom' ? document.getElementById('endDate').value   : null
    };

    const portfolios = BT.portfolios.map(p => ({
        name:      p.name,
        color:     p.color,
        rebalance: p.rebalance,
        assets:    p.assets.map(a => ({ ticker: a.ticker, weight: a.weight, name: a.name }))
    }));

    // Collect recurring cashflows
    const cashflows = [];
    document.querySelectorAll('#cfList .bt-cf-item').forEach(el => {
        const id = el.id.replace('cf_','');
        const dir  = document.getElementById('cfdir_' + id)?.value  || 'withdrawal';
        const type = document.getElementById('cftype_' + id)?.value || 'fixed';
        const amt  = parseFloat(document.getElementById('cfamt_' + id)?.value) || 0;
        const freq = document.getElementById('cffreq_' + id)?.value || 'yearly';
        const off  = parseInt(document.getElementById('cfoff_' + id)?.value)  || 0;
        if (amt > 0) cashflows.push({ type, direction: dir, amount: amt, frequency: freq, offset: off });
    });
    // One-time cashflows
    document.querySelectorAll('#otCfList .bt-cf-item').forEach(el => {
        const id  = el.id.replace('otcf_','');
        const date = document.getElementById('otdate_' + id)?.value || '';
        const dir  = document.getElementById('otdir_' + id)?.value  || 'withdrawal';
        const amt  = parseFloat(document.getElementById('otamt_' + id)?.value) || 0;
        if (date && amt > 0) cashflows.push({ type: 'onetime', direction: dir, amount: amt, date });
    });

    return { params, portfolios, cashflows };
}

async function runBacktest() {
    if (BT.portfolios.length === 0) { showError('Add at least one portfolio first.'); return; }
    for (const p of BT.portfolios) {
        if (!p.assets.length) { showError('Portfolio "' + p.name + '" has no assets.'); return; }
    }
    hideError();
    setLoading(true);
    document.getElementById('btResults').classList.remove('visible');

    try {
        const config = buildConfig();
        const resp   = await fetch('api/backtest.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(config)
        });
        const data = await resp.json();
        setLoading(false);
        if (data.error) { showError(data.error); return; }
        BT.lastResults = data;
        renderResults(data);
        document.getElementById('btResults').classList.add('visible');
        document.getElementById('btResults').scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch(e) {
        setLoading(false);
        showError('Network error: ' + e.message);
    }
}

/* ── CHART.JS DEFAULTS ── */
Chart.defaults.color        = 'rgba(148,163,184,.7)';
Chart.defaults.borderColor  = 'rgba(255,255,255,.05)';
Chart.defaults.font.family  = "'Segoe UI',sans-serif";
Chart.defaults.font.size    = 11;

function destroyChart(id) {
    if (BT.charts[id]) { BT.charts[id].destroy(); delete BT.charts[id]; }
}
function hexAlpha(hex, a) {
    const r = parseInt(hex.slice(1,3),16);
    const g = parseInt(hex.slice(3,5),16);
    const b = parseInt(hex.slice(5,7),16);
    return `rgba(${r},${g},${b},${a})`;
}
function fmtPct(v)  { return (v > 0 ? '+' : '') + v.toFixed(2) + '%'; }
function fmtDollar(v){ return '$' + Number(v).toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2}); }

function renderResults(data) {
    const portfolios = data.portfolios || [];
    const bm         = data.benchmark;
    const months     = (portfolios[0] || {}).month_labels || data.months || [];

    /* ── Growth Chart ── */
    destroyChart('growth');
    const growthCtx = document.getElementById('growthChart');
    const growthDS  = portfolios.map(p => ({
        label: p.name, data: p.growth_series, borderColor: p.color,
        backgroundColor: hexAlpha(p.color.length === 7 ? p.color : '#d4af37', 0.07),
        borderWidth: 2.2, fill: false, tension: 0.35,
        pointRadius: 0, pointHoverRadius: 5,
        pointBackgroundColor: p.color
    }));
    if (bm) growthDS.push({
        label: bm.name + ' (bm)', data: bm.growth_series, borderColor: '#64748b',
        backgroundColor: 'rgba(100,116,139,.04)', borderWidth: 1.5, borderDash: [5,3],
        fill: false, tension: 0.35, pointRadius: 0, pointHoverRadius: 4
    });

    const shortMonths = months.map(m => m.length >= 7 ? m.slice(0,7) : m);
    BT.charts['growth'] = new Chart(growthCtx, {
        type: 'line',
        data: { labels: shortMonths, datasets: growthDS },
        options: {
            responsive: true, interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, pointStyleWidth: 10, boxHeight: 6 } },
                tooltip: {
                    backgroundColor: 'rgba(7,10,20,.92)', borderColor: 'rgba(212,175,55,.3)', borderWidth: 1, padding: 12,
                    callbacks: { label: ctx => ' ' + ctx.dataset.label + ': ' + fmtDollar(ctx.parsed.y) }
                }
            },
            scales: {
                x: { grid: {color:'rgba(255,255,255,.04)'}, ticks: { maxTicksLimit: 12, color:'rgba(148,163,184,.6)' } },
                y: { grid: {color:'rgba(255,255,255,.04)'}, ticks: { color:'rgba(148,163,184,.6)', callback: v => '$' + v.toLocaleString() } }
            }
        }
    });

    if (months.length > 0) {
        document.getElementById('growthDateRange').textContent = months[0] + ' – ' + months[months.length-1];
    }

    /* ── Metrics Table ── */
    const thead = document.getElementById('metricsTHead');
    const tbody = document.getElementById('metricsTBody');
    thead.innerHTML = '<th class="metric-lbl">Metric</th>';
    portfolios.forEach(p => {
        thead.innerHTML += `<th style="color:${p.color}">${esc(p.name)}</th>`;
    });
    if (bm) thead.innerHTML += `<th style="color:#94a3b8">${esc(bm.name)} <small>(bm)</small></th>`;

    const rows = [
        ['Final Value',       p => fmtDollar(p.metrics.final_value),     'neu'],
        ['Total Return',      p => fmtPct(p.metrics.total_return),        'signed'],
        ['CAGR',              p => fmtPct(p.metrics.cagr),                'signed'],
        ['MWRR',              p => fmtPct(p.metrics.mwrr),                'signed'],
        ['Volatility',        p => p.metrics.volatility.toFixed(2) + '%', 'neu'],
        ['Sharpe Ratio',      p => p.metrics.sharpe.toFixed(3),           'neutral_sharpe'],
        ['Max Drawdown',      p => fmtPct(p.metrics.max_drawdown),        'dd'],
        ['Recovery Period',   p => p.metrics.recovery_months ? p.metrics.recovery_months + ' months' : '—', 'neu'],
        ['Best Year',         p => fmtPct(p.metrics.best_year),           'signed'],
        ['Worst Year',        p => fmtPct(p.metrics.worst_year),          'signed'],
    ];
    tbody.innerHTML = '';
    rows.forEach(([label, fn, cls]) => {
        let row = `<tr><td class="metric-lbl">${label}</td>`;
        const allP = bm ? [...portfolios, bm] : portfolios;
        allP.forEach(p => {
            const val = fn(p);
            let vcls = 'bt-val-neu';
            if (cls === 'signed')         vcls = val.startsWith('+') ? 'bt-val-up' : (val.startsWith('-') ? 'bt-val-down' : 'bt-val-neu');
            if (cls === 'dd')             vcls = val.startsWith('-') ? 'bt-val-down' : 'bt-val-up';
            if (cls === 'neutral_sharpe') vcls = parseFloat(val) >= 0.5 ? 'bt-val-up' : (parseFloat(val) < 0 ? 'bt-val-down' : 'bt-val-neu');
            row += `<td class="${vcls}">${val}</td>`;
        });
        row += '</tr>';
        tbody.innerHTML += row;
    });

    /* ── Annual Returns Bar Chart ── */
    destroyChart('annual');
    const allYears = [...new Set(portfolios.flatMap(p => Object.keys(p.annual_returns || {})))].sort();
    const annualDS = portfolios.map(p => ({
        label: p.name,
        data:  allYears.map(y => p.annual_returns[y] ?? null),
        backgroundColor: hexAlpha(p.color.length === 7 ? p.color : '#d4af37', 0.75),
        borderColor:     p.color, borderWidth: 1.5, borderRadius: 4
    }));
    if (bm) annualDS.push({
        label: bm.name + ' (bm)',
        data:  allYears.map(y => bm.annual_returns[y] ?? null),
        backgroundColor: 'rgba(100,116,139,.45)', borderColor: '#64748b', borderWidth: 1, borderRadius: 4
    });
    BT.charts['annual'] = new Chart(document.getElementById('annualChart'), {
        type: 'bar',
        data: { labels: allYears, datasets: annualDS },
        options: {
            responsive: true, interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyleWidth: 10, boxHeight: 6 } },
                tooltip: { backgroundColor:'rgba(7,10,20,.92)', borderColor:'rgba(212,175,55,.2)', borderWidth:1, padding:10,
                    callbacks: { label: ctx => ' ' + ctx.dataset.label + ': ' + fmtPct(ctx.parsed.y) } } },
            scales: {
                x: { grid:{color:'rgba(255,255,255,.03)'}, ticks:{color:'rgba(148,163,184,.6)'} },
                y: { grid:{color:'rgba(255,255,255,.04)'}, ticks:{color:'rgba(148,163,184,.6)',callback:v=>v+'%'},
                     afterBuildTicks(ax) { ax.ticks.push({value:0}); } }
            }
        }
    });

    /* ── Drawdown Chart ── */
    destroyChart('drawdown');
    const ddDS = portfolios.map(p => ({
        label: p.name, data: p.dd_series, borderColor: p.color,
        backgroundColor: hexAlpha(p.color.length === 7 ? p.color : '#d4af37', 0.08),
        borderWidth: 1.5, fill: true, tension: 0.3, pointRadius: 0, pointHoverRadius: 4
    }));
    if (bm) ddDS.push({
        label: bm.name + ' (bm)', data: bm.dd_series, borderColor: '#64748b',
        backgroundColor: 'rgba(100,116,139,.04)', borderWidth: 1.2, borderDash: [5,3],
        fill: false, tension: 0.3, pointRadius: 0
    });
    BT.charts['drawdown'] = new Chart(document.getElementById('drawdownChart'), {
        type: 'line',
        data: { labels: (portfolios[0]||bm||{}).month_labels || months, datasets: ddDS },
        options: {
            responsive: true, interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels:{usePointStyle:true,pointStyleWidth:10,boxHeight:6} },
                tooltip: { backgroundColor:'rgba(7,10,20,.92)', padding:10,
                    callbacks:{label:ctx=>' '+ctx.dataset.label+': '+fmtPct(ctx.parsed.y)}} },
            scales: {
                x: { grid:{color:'rgba(255,255,255,.03)'}, ticks:{maxTicksLimit:10,color:'rgba(148,163,184,.6)'} },
                y: { grid:{color:'rgba(255,255,255,.04)'}, ticks:{color:'rgba(148,163,184,.6)',callback:v=>v+'%'} }
            }
        }
    });

    /* ── Monthly Heatmap ── */
    const hmTabs = document.getElementById('hmTabs');
    hmTabs.innerHTML = '';
    portfolios.forEach((p, idx) => {
        const tab = document.createElement('button');
        tab.className = 'bt-tab' + (idx === 0 ? ' active' : '');
        tab.innerHTML = `<span style="color:${p.color}">●</span> ${esc(p.name)}`;
        tab.onclick = function() {
            document.querySelectorAll('#hmTabs .bt-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            renderHeatmap(p);
        };
        hmTabs.appendChild(tab);
    });
    if (portfolios.length > 0) renderHeatmap(portfolios[0]);

    /* ── Rolling Returns ── */
    destroyChart('rolling');
    const rollDS = portfolios.map(p => {
        const labels = Object.keys(p.rolling || {});
        const vals   = Object.values(p.rolling || {});
        return { label: p.name, data: vals, labels, borderColor: p.color, borderWidth: 1.8,
                 fill: false, tension: 0.4, pointRadius: 0, pointHoverRadius: 4 };
    });
    if (bm && bm.rolling) {
        const labels = Object.keys(bm.rolling);
        rollDS.push({ label: bm.name + ' (bm)', data: Object.values(bm.rolling), labels,
            borderColor: '#64748b', borderWidth: 1.2, borderDash: [5,3],
            fill: false, tension: 0.4, pointRadius: 0 });
    }
    const rollLabels = portfolios.length ? Object.keys(portfolios[0].rolling || {}) : [];
    BT.charts['rolling'] = new Chart(document.getElementById('rollingChart'), {
        type: 'line',
        data: { labels: rollLabels, datasets: rollDS },
        options: {
            responsive: true, interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels:{usePointStyle:true,pointStyleWidth:10,boxHeight:6} },
                tooltip: { backgroundColor:'rgba(7,10,20,.92)', padding:10,
                    callbacks:{label:ctx=>' '+ctx.dataset.label+': '+fmtPct(ctx.parsed.y)}} },
            scales: {
                x: { grid:{color:'rgba(255,255,255,.03)'}, ticks:{maxTicksLimit:12,color:'rgba(148,163,184,.6)'} },
                y: { grid:{color:'rgba(255,255,255,.04)'}, ticks:{color:'rgba(148,163,184,.6)',callback:v=>v+'%'} }
            }
        }
    });

    /* ── Correlation Matrix ── */
    const corrCard = document.getElementById('corrCard');
    if (data.corr_matrix && portfolios.length > 1) {
        corrCard.style.display = 'block';
        const wrap = document.getElementById('corrMatrixWrap');
        const n    = portfolios.length;
        let html = '<table class="corr-tbl"><thead><tr><th></th>';
        portfolios.forEach(p => { html += `<th style="color:${p.color}">${esc(p.name)}</th>`; });
        html += '</tr></thead><tbody>';
        data.corr_matrix.forEach((row, i) => {
            html += `<tr><th style="color:${portfolios[i].color};text-align:left">${esc(portfolios[i].name)}</th>`;
            row.forEach(v => {
                const vf = v !== null ? v.toFixed(3) : '—';
                const c  = v !== null ? corrColor(v) : 'transparent';
                html += `<td style="background:${c}">${vf}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
        wrap.innerHTML = html;
    } else {
        corrCard.style.display = 'none';
    }
}

function renderHeatmap(p) {
    const wrap = document.getElementById('heatmapWrap');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    if (!p.heatmap || !Object.keys(p.heatmap).length) {
        wrap.innerHTML = '<p style="color:var(--muted);font-size:.8rem;text-align:center;padding:20px 0">No monthly data available.</p>';
        return;
    }
    const years = Object.keys(p.heatmap).sort();
    let html = '<table class="hm-table"><thead><tr><th class="hm-yr">Year</th>';
    months.forEach(m => { html += `<th>${m}</th>`; });
    html += '<th>Full Yr</th></tr></thead><tbody>';
    years.forEach(yr => {
        html += `<tr><td class="hm-yr">${yr}</td>`;
        let yr_prod = 1;
        for (let m = 1; m <= 12; m++) {
            const v = p.heatmap[yr] && p.heatmap[yr][m] !== undefined ? p.heatmap[yr][m] : null;
            if (v !== null) yr_prod *= (1 + v / 100);
            html += v !== null
                ? `<td style="background:${heatColor(v)};color:${Math.abs(v)>2?'#fff':'var(--ink)'}">${v.toFixed(1)}</td>`
                : `<td style="color:rgba(148,163,184,.3)">—</td>`;
        }
        const yr_ret = ((yr_prod - 1) * 100).toFixed(1);
        html += `<td style="font-weight:800;color:${yr_prod>=1?'var(--grn)':'var(--red)'}">${yr_ret >= 0 ? '+' : ''}${yr_ret}%</td>`;
        html += '</tr>';
    });
    html += '</tbody></table>';
    wrap.innerHTML = html;
}

function heatColor(v) {
    if (v > 6)  return 'rgba(16,185,129,.85)';
    if (v > 3)  return 'rgba(16,185,129,.55)';
    if (v > 0)  return 'rgba(16,185,129,.28)';
    if (v > -3) return 'rgba(239,68,68,.28)';
    if (v > -6) return 'rgba(239,68,68,.55)';
    return 'rgba(239,68,68,.85)';
}

function corrColor(v) {
    if (v >= 0.8) return 'rgba(239,68,68,.25)';
    if (v >= 0.5) return 'rgba(245,158,11,.18)';
    if (v >= 0)   return 'rgba(16,185,129,.12)';
    return 'rgba(56,189,248,.18)';
}

/* ── Save / Load ── */
async function saveBacktest() {
    if (!BT.lastResults) { showError('Run a backtest first.'); return; }
    const title    = document.getElementById('saveTitle').value.trim() || 'My Backtest';
    const isPublic = document.getElementById('makePublic').checked ? 1 : 0;
    const config   = buildConfig();
    try {
        const resp = await fetch('api/save-backtest.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                title, is_public: isPublic,
                config_json:  JSON.stringify(config),
                results_json: JSON.stringify(BT.lastResults)
            })
        });
        const data = await resp.json();
        if (data.error) { showError(data.error); return; }
        const linkEl = document.getElementById('shareLink');
        linkEl.style.display = 'block';
        linkEl.textContent   = data.share_url;
        linkEl.title         = 'Click to copy';
        linkEl.onclick       = function() { navigator.clipboard.writeText(data.share_url).then(() => { this.textContent = '✓ Copied!'; setTimeout(() => { this.textContent = data.share_url; }, 2000); }); };
    } catch(e) { showError('Save failed: ' + e.message); }
}

async function loadSavedBacktest() {
    const token = prompt('Enter the share token or backtest ID:');
    if (!token) return;
    try {
        const param = isNaN(token) ? 'token=' + encodeURIComponent(token) : 'id=' + parseInt(token);
        const resp  = await fetch('api/load-backtest.php?' + param);
        const data  = await resp.json();
        if (data.error) { showError(data.error); return; }
        // Restore config
        if (data.config && data.config.portfolios) {
            BT.portfolios = data.config.portfolios.map(p => ({
                id: Date.now() + Math.random(), ...p
            }));
            BT.renderPortfolios();
        }
        document.getElementById('saveTitle').value = data.title || '';
        // Show results
        if (data.results) {
            BT.lastResults = data.results;
            renderResults(data.results);
            document.getElementById('btResults').classList.add('visible');
        }
    } catch(e) { showError('Load failed: ' + e.message); }
}

/* ── UI helpers ── */
function setLoading(on) {
    document.getElementById('btLoading').style.display = on ? 'block' : 'none';
    document.getElementById('runBtn').disabled = on;
}
function showError(msg) {
    const el = document.getElementById('btError');
    el.textContent = '⚠ ' + msg;
    el.style.display = 'block';
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
function hideError() { document.getElementById('btError').style.display = 'none'; }

/* ── Auto-load pre-saved backtest from URL ── */
<?php if ($preload_results): ?>
(function() {
    try {
        const results = <?= $preload_results ?>;
        const config  = <?= $preload_config ?>;
        BT.lastResults = results;
        if (config && config.portfolios) {
            BT.portfolios = config.portfolios.map(p => ({ id: Date.now() + Math.random(), ...p }));
            BT.renderPortfolios();
        }
        document.getElementById('saveTitle').value = <?= json_encode($preload_title) ?>;
        renderResults(results);
        document.getElementById('btResults').classList.add('visible');
    } catch(e) { console.warn('Preload failed', e); }
})();
<?php endif; ?>

// Kick off with one default portfolio
BT.addPortfolio(BT.PRESETS['60_40']);
</script>

<?php include 'includes/footer.php'; ?>
