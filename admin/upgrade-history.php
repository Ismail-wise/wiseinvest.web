<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';

// ── Admin gate (same pattern as install-analytics.php) ───────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /index.php'); exit;
}

// ── Ensure table exists (defensive) ──────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS upgrade_log (
    id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
    previous_level VARCHAR(50) NULL, new_level VARCHAR(50) NOT NULL,
    expires_at DATETIME NULL, upgraded_by INT NOT NULL, notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id), INDEX idx_admin (upgraded_by), INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$table_ok  = $conn->query("SHOW TABLES LIKE 'upgrade_log'");
$has_table = $table_ok && $table_ok->num_rows > 0;

// ── Friendly tier label ──────────────────────────────────────────
function lvl_label($l) {
    $m = ['free'=>'Free','premium'=>'Legacy','b1_premium'=>'B-1','b2_premium'=>'B-2',
          'b3_premium'=>'B-3','b4_premium'=>'B-4','b5_premium'=>'B-5','diamond'=>'Diamond'];
    return $m[$l] ?? strtoupper((string)$l);
}

// ── Prepared-select helper (dynamic WHERE) ───────────────────────
function runq($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    if ($types !== '') { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    return $stmt->get_result();
}

// ── Read & validate filters ──────────────────────────────────────
$f_admin = isset($_GET['admin']) ? (int)$_GET['admin'] : 0;
$f_from  = isset($_GET['from'])  ? trim($_GET['from'])  : '';
$f_to    = isset($_GET['to'])    ? trim($_GET['to'])    : '';
$f_user  = isset($_GET['user'])  ? trim($_GET['user'])  : '';

$conds = []; $params = []; $types = '';
if ($f_admin > 0)                         { $conds[] = 'l.upgraded_by = ?'; $params[] = $f_admin; $types .= 'i'; }
if ($f_from !== '' && strtotime($f_from)) { $conds[] = 'l.created_at >= ?'; $params[] = date('Y-m-d 00:00:00', strtotime($f_from)); $types .= 's'; }
if ($f_to   !== '' && strtotime($f_to))   { $conds[] = 'l.created_at <= ?'; $params[] = date('Y-m-d 23:59:59', strtotime($f_to));   $types .= 's'; }
if ($f_user !== '')                       { $conds[] = '(u.fullname LIKE ? OR u.email LIKE ?)'; $lk = '%'.$f_user.'%'; $params[] = $lk; $params[] = $lk; $types .= 'ss'; }
$where = $conds ? ' WHERE ' . implode(' AND ', $conds) : '';

$base_sql = "FROM upgrade_log l
             LEFT JOIN users u ON u.id = l.user_id
             LEFT JOIN users a ON a.id = l.upgraded_by" . $where;

// ── CSV export (filtered, before any HTML) ───────────────────────
if ($has_table && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="upgrade_log_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date','User','Email','Previous','New','Expires','Admin','Notes']);
    $res = runq($conn,
        "SELECT l.created_at, u.fullname user_name, u.email user_email,
                l.previous_level, l.new_level, l.expires_at, a.fullname admin_name, l.notes
         $base_sql ORDER BY l.created_at DESC", $types, $params);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [
                $r['created_at'],
                $r['user_name']  ?? '',
                $r['user_email'] ?? '',
                lvl_label($r['previous_level']),
                lvl_label($r['new_level']),
                $r['expires_at'] ?: 'Lifetime',
                $r['admin_name'] ?? '',
                $r['notes'] ?? '',
            ]);
        }
    }
    fclose($out);
    exit;
}

// ── KPIs (overall, unfiltered) ───────────────────────────────────
function scalar($conn, $sql) { $r = $conn->query($sql); if (!$r) return 0; $row = $r->fetch_row(); return $row ? $row[0] : 0; }
$total_up = (int)scalar($conn, "SELECT COUNT(*) FROM upgrade_log");
$month_up = (int)scalar($conn, "SELECT COUNT(*) FROM upgrade_log WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())");
$today_up = (int)scalar($conn, "SELECT COUNT(*) FROM upgrade_log WHERE DATE(created_at)=CURDATE()");
$top_admin = '—';
$ta = $conn->query("SELECT a.fullname, COUNT(*) c FROM upgrade_log l LEFT JOIN users a ON a.id=l.upgraded_by GROUP BY l.upgraded_by ORDER BY c DESC LIMIT 1");
if ($ta && $ta->num_rows) { $row = $ta->fetch_assoc(); $top_admin = ($row['fullname'] ?? 'Unknown') . ' (' . (int)$row['c'] . ')'; }

// ── Admin dropdown options ───────────────────────────────────────
$admins = [];
$ar = $conn->query("SELECT DISTINCT a.id, a.fullname FROM upgrade_log l JOIN users a ON a.id=l.upgraded_by ORDER BY a.fullname ASC");
if ($ar) { while ($row = $ar->fetch_assoc()) { $admins[] = $row; } }

// ── Chart: upgrades per day, last 30 days (overall) ──────────────
$clabels = $cdata = []; $cmap = [];
$cr = $conn->query("SELECT DATE(created_at) d, COUNT(*) c FROM upgrade_log WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(created_at)");
if ($cr) { while ($row = $cr->fetch_assoc()) { $cmap[$row['d']] = (int)$row['c']; } }
for ($i = 29; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $clabels[] = date('M j', strtotime($d)); $cdata[] = $cmap[$d] ?? 0; }

// ── Pagination (server-side, 50/page) ────────────────────────────
$per_page = 50;
$page     = max(1, (int)($_GET['page'] ?? 1));
$cnt_res  = runq($conn, "SELECT COUNT(*) c $base_sql", $types, $params);
$total    = (int)(($cnt_res && ($cr2 = $cnt_res->fetch_assoc())) ? $cr2['c'] : 0);
$pages    = max(1, (int)ceil($total / $per_page));
if ($page > $pages) $page = $pages;
$offset   = ($page - 1) * $per_page;

$rows_sql = "SELECT l.*, u.fullname user_name, u.email user_email, a.fullname admin_name
             $base_sql ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
$rtypes  = $types . 'ii';
$rparams = array_merge($params, [$per_page, $offset]);
$logs    = runq($conn, $rows_sql, $rtypes, $rparams);

// Build filter-preserving, ABSOLUTE links for pager / CSV (page lives in /admin/)
$self  = '/admin/upgrade-history.php';
$qs = $_GET; unset($qs['page']);
$query = http_build_query($qs);
$qbase = $self . ($query !== '' ? '?' . $query : '');

include dirname(__DIR__) . '/includes/header.php';
?>
<style>
  .ia-wrap { max-width:1200px; margin:0 auto; padding:24px 16px 60px; color:#e2e8f0; }
  .ia-wrap h1 { color:#d4af37; font-size:1.6rem; margin:0 0 4px; }
  .ia-wrap .ia-sub { color:#94a3b8; font-size:.9rem; margin-bottom:24px; }
  .ia-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
  .ia-card { background:#0f172a; border:1px solid rgba(212,175,55,.25); border-radius:14px; padding:18px 20px; }
  .ia-card .lbl { color:#94a3b8; font-size:.8rem; text-transform:uppercase; letter-spacing:.04em; }
  .ia-card .val { color:#d4af37; font-size:2rem; font-weight:700; margin-top:6px; }
  .ia-card .val.sm { font-size:1.15rem; }
  .ia-panel { background:#0f172a; border:1px solid rgba(212,175,55,.2); border-radius:14px; padding:18px; margin-bottom:28px; }
  .ia-panel h3 { color:#d4af37; font-size:1rem; margin:0 0 14px; }
  .ia-table { width:100%; border-collapse:collapse; font-size:.85rem; }
  .ia-table th, .ia-table td { text-align:left; padding:9px 10px; border-bottom:1px solid rgba(148,163,184,.12); }
  .ia-table th { color:#d4af37; font-weight:600; }
  .ia-table td { color:#cbd5e1; vertical-align:middle; }
  .ia-badge { display:inline-block; padding:2px 9px; border-radius:20px; font-size:.72rem; background:rgba(212,175,55,.14); color:#d4af37; }
  .ia-badge.muted { background:rgba(148,163,184,.14); color:#94a3b8; }
  .ia-arrow { color:#64748b; margin:0 4px; }
  .ia-export { display:inline-block; background:#d4af37; color:#0f172a; font-weight:700; padding:10px 20px; border-radius:10px; text-decoration:none; }
  .ia-filters { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; margin-bottom:24px; }
  .ia-filters .fld { display:flex; flex-direction:column; gap:5px; }
  .ia-filters label { font-size:.72rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.04em; }
  .ia-filters select, .ia-filters input { background:#0b1322; border:1px solid rgba(212,175,55,.25); color:#e2e8f0; border-radius:9px; padding:8px 11px; font-family:inherit; font-size:.85rem; outline:none; }
  .ia-filters select:focus, .ia-filters input:focus { border-color:#d4af37; }
  .ia-btn { background:#d4af37; color:#0f172a; font-weight:700; border:none; padding:9px 18px; border-radius:9px; cursor:pointer; font-family:inherit; font-size:.85rem; }
  .ia-btn.ghost { background:transparent; color:#94a3b8; border:1px solid rgba(148,163,184,.3); text-decoration:none; display:inline-block; }
  .ia-pager { display:flex; gap:6px; flex-wrap:wrap; align-items:center; margin-top:16px; }
  .ia-pager a, .ia-pager span.cur { padding:6px 11px; border-radius:8px; font-size:.82rem; text-decoration:none; color:#cbd5e1; border:1px solid rgba(148,163,184,.2); }
  .ia-pager span.cur { background:#d4af37; color:#0f172a; border-color:transparent; font-weight:700; }
  .ia-warn { background:rgba(220,38,38,.12); border:1px solid rgba(220,38,38,.4); color:#fca5a5; padding:14px 18px; border-radius:12px; margin-bottom:24px; }
  @media (max-width:820px){ .ia-cards{grid-template-columns:repeat(2,1fr);} .ia-filters{flex-direction:column; align-items:stretch;} }
</style>

<div class="ia-wrap">
  <h1>⬆ Upgrade History</h1>
  <div class="ia-sub">Audit log of every membership upgrade — who, what, when &amp; expiry.</div>

  <?php if (!$has_table): ?>
    <div class="ia-warn">⚠️ Table <code>upgrade_log</code> not found. Run <code>add_admin_users_features.sql</code> first.</div>
  <?php endif; ?>

  <div class="ia-cards">
    <div class="ia-card"><div class="lbl">Total Upgrades</div><div class="val"><?php echo number_format($total_up); ?></div></div>
    <div class="ia-card"><div class="lbl">This Month</div><div class="val"><?php echo number_format($month_up); ?></div></div>
    <div class="ia-card"><div class="lbl">Today</div><div class="val"><?php echo number_format($today_up); ?></div></div>
    <div class="ia-card"><div class="lbl">Most Active Admin</div><div class="val sm"><?php echo htmlspecialchars($top_admin); ?></div></div>
  </div>

  <div class="ia-panel"><h3>Upgrades Over Time (30 days)</h3><canvas id="chartUp" height="90"></canvas></div>

  <!-- Filters (server-side GET) -->
  <form method="GET" class="ia-filters">
    <div class="fld">
      <label>Admin</label>
      <select name="admin">
        <option value="0">All admins</option>
        <?php foreach ($admins as $a): ?>
          <option value="<?php echo (int)$a['id']; ?>" <?php echo $f_admin==(int)$a['id']?'selected':''; ?>>
            <?php echo htmlspecialchars($a['fullname'] ?? ('#'.$a['id'])); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="fld"><label>From</label><input type="date" name="from" value="<?php echo htmlspecialchars($f_from); ?>"></div>
    <div class="fld"><label>To</label><input type="date" name="to" value="<?php echo htmlspecialchars($f_to); ?>"></div>
    <div class="fld"><label>User (name/email)</label><input type="text" name="user" value="<?php echo htmlspecialchars($f_user); ?>" placeholder="Search…"></div>
    <button type="submit" class="ia-btn">Filter</button>
    <a href="/admin/upgrade-history.php" class="ia-btn ghost">Clear</a>
    <a class="ia-export" href="<?php echo htmlspecialchars($qbase . (strpos($qbase,'?')!==false ? '&' : '?') . 'export=csv'); ?>">⬇ CSV</a>
  </form>

  <div class="ia-panel">
    <h3>Log <span style="color:#94a3b8;font-weight:400;font-size:.85rem;">(<?php echo number_format($total); ?> entries · page <?php echo $page; ?>/<?php echo $pages; ?>)</span></h3>
    <div style="overflow-x:auto;">
      <table class="ia-table">
        <thead><tr><th>User</th><th>Change</th><th>Expires</th><th>Admin</th><th>Date</th><th>Notes</th></tr></thead>
        <tbody>
        <?php if (!$logs || $logs->num_rows === 0): ?>
          <tr><td colspan="6" style="color:#94a3b8;">No upgrade records match.</td></tr>
        <?php else: while ($r = $logs->fetch_assoc()): ?>
          <tr>
            <td>
              <div style="font-weight:600;color:#e2e8f0;"><?php echo htmlspecialchars($r['user_name'] ?? ('User #'.$r['user_id'])); ?></div>
              <div style="font-size:.74rem;color:#94a3b8;"><?php echo htmlspecialchars($r['user_email'] ?? ''); ?></div>
            </td>
            <td>
              <span class="ia-badge muted"><?php echo htmlspecialchars(lvl_label($r['previous_level'])); ?></span>
              <span class="ia-arrow">→</span>
              <span class="ia-badge"><?php echo htmlspecialchars(lvl_label($r['new_level'])); ?></span>
            </td>
            <td><?php echo $r['expires_at'] ? htmlspecialchars(date('d M Y', strtotime($r['expires_at']))) : '<span style="color:#a78bfa;font-weight:700;">Lifetime</span>'; ?></td>
            <td><?php echo htmlspecialchars($r['admin_name'] ?? ('#'.$r['upgraded_by'])); ?></td>
            <td style="white-space:nowrap;font-size:.8rem;"><?php echo htmlspecialchars(date('d M Y H:i', strtotime($r['created_at']))); ?></td>
            <td style="max-width:220px;font-size:.8rem;"><?php echo htmlspecialchars($r['notes'] ?? '') ?: '<span style="color:#475569;">—</span>'; ?></td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="ia-pager">
      <?php
        $sep = (strpos($qbase,'?') !== false) ? '&' : '?';
        if ($page > 1) echo "<a href='{$qbase}{$sep}page=".($page-1)."'>‹ Prev</a>";
        for ($p = 1; $p <= $pages; $p++) {
            if ($p == $page) echo "<span class='cur'>$p</span>";
            else echo "<a href='{$qbase}{$sep}page=$p'>$p</a>";
        }
        if ($page < $pages) echo "<a href='{$qbase}{$sep}page=".($page+1)."'>Next ›</a>";
      ?>
    </div>
    <?php endif; ?>
  </div>

  <a href="/admin.php?tab=users" class="ia-btn ghost" style="margin-top:6px;">← Back to Users</a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
(function(){
  if (typeof Chart === 'undefined') return;
  Chart.defaults.color = '#94a3b8';
  Chart.defaults.borderColor = 'rgba(148,163,184,.12)';
  new Chart(document.getElementById('chartUp'), {
    type:'line',
    data:{ labels:<?php echo json_encode($clabels); ?>,
      datasets:[{ label:'Upgrades', data:<?php echo json_encode($cdata); ?>,
        borderColor:'#d4af37', backgroundColor:'rgba(212,175,55,.15)', fill:true, tension:.3, pointRadius:2 }] },
    options:{ plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
  });
})();
</script>
</body>
</html>
