<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';

// ── Admin gate (same pattern as other admin/*.php) ───────────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /index.php'); exit;
}

// ── Ensure table exists ──────────────────────────────────────────
$table_ok  = $conn->query("SHOW TABLES LIKE 'install_events'");
$has_table = $table_ok && $table_ok->num_rows > 0;

// ── CSV export (must run before any HTML output) ─────────────────
if ($has_table && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="install_events_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','event_type','device_type','browser','source','user_ip','user_agent','referrer','user_id','created_at']);
    $res = $conn->query("SELECT id,event_type,device_type,browser,source,user_ip,user_agent,referrer,user_id,created_at FROM install_events ORDER BY created_at DESC");
    if ($res) { while ($row = $res->fetch_assoc()) { fputcsv($out, $row); } }
    fclose($out);
    exit;
}

// ── Helper: single scalar ────────────────────────────────────────
function scalar($conn, $sql) {
    $r = $conn->query($sql);
    if (!$r) return 0;
    $row = $r->fetch_row();
    return $row ? (int)$row[0] : 0;
}

$total_installs = $month_installs = $today_installs = $page_views = 0;
$conversion = 0.0;
$daily_labels = $daily_data = [];
$device_data = ['android'=>0,'ios'=>0,'desktop'=>0,'other'=>0];
$source_data = [];
$recent = [];

if ($has_table) {
    $total_installs = scalar($conn, "SELECT COUNT(*) FROM install_events WHERE event_type='installed'");
    $month_installs = scalar($conn, "SELECT COUNT(*) FROM install_events WHERE event_type='installed' AND YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())");
    $today_installs = scalar($conn, "SELECT COUNT(*) FROM install_events WHERE event_type='installed' AND DATE(created_at)=CURDATE()");
    $page_views     = scalar($conn, "SELECT COUNT(*) FROM install_events WHERE event_type='page_view'");
    $conversion     = $page_views > 0 ? round($total_installs / $page_views * 100, 1) : 0.0;

    // Daily installs, last 30 days
    $map = [];
    $res = $conn->query("SELECT DATE(created_at) d, COUNT(*) c FROM install_events WHERE event_type='installed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY DATE(created_at)");
    if ($res) { while ($row = $res->fetch_assoc()) { $map[$row['d']] = (int)$row['c']; } }
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i day"));
        $daily_labels[] = date('M j', strtotime($d));
        $daily_data[]   = $map[$d] ?? 0;
    }

    // By device
    $res = $conn->query("SELECT device_type, COUNT(*) c FROM install_events WHERE event_type='installed' GROUP BY device_type");
    if ($res) { while ($row = $res->fetch_assoc()) { $device_data[$row['device_type']] = (int)$row['c']; } }

    // By source
    $res = $conn->query("SELECT source, COUNT(*) c FROM install_events WHERE event_type='installed' GROUP BY source ORDER BY c DESC");
    if ($res) { while ($row = $res->fetch_assoc()) { $source_data[$row['source'] ?: 'direct'] = (int)$row['c']; } }

    // Recent 50 events
    $res = $conn->query("SELECT event_type, device_type, source, user_ip, created_at FROM install_events ORDER BY created_at DESC LIMIT 50");
    if ($res) { while ($row = $res->fetch_assoc()) { $recent[] = $row; } }
}

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
  .ia-grid { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:28px; }
  .ia-panel { background:#0f172a; border:1px solid rgba(212,175,55,.2); border-radius:14px; padding:18px; }
  .ia-panel h3 { color:#d4af37; font-size:1rem; margin:0 0 14px; }
  .ia-row2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:28px; }
  .ia-table { width:100%; border-collapse:collapse; font-size:.85rem; }
  .ia-table th, .ia-table td { text-align:left; padding:9px 10px; border-bottom:1px solid rgba(148,163,184,.12); }
  .ia-table th { color:#d4af37; font-weight:600; }
  .ia-table td { color:#cbd5e1; }
  .ia-badge { display:inline-block; padding:2px 9px; border-radius:20px; font-size:.72rem; background:rgba(212,175,55,.14); color:#d4af37; }
  .ia-export { display:inline-block; background:#d4af37; color:#0f172a; font-weight:700; padding:10px 20px; border-radius:10px; text-decoration:none; }
  .ia-warn { background:rgba(220,38,38,.12); border:1px solid rgba(220,38,38,.4); color:#fca5a5; padding:14px 18px; border-radius:12px; margin-bottom:24px; }
  @media (max-width:820px){
    .ia-cards{grid-template-columns:repeat(2,1fr);}
    .ia-grid,.ia-row2{grid-template-columns:1fr;}
  }
</style>

<div class="ia-wrap">
  <h1>📲 Install Analytics</h1>
  <div class="ia-sub">PWA install funnel — page views, installs, devices &amp; sources.</div>

  <?php if (!$has_table): ?>
    <div class="ia-warn">⚠️ Table <code>install_events</code> not found. Run <code>add_install_analytics.sql</code> first.</div>
  <?php endif; ?>

  <div class="ia-cards">
    <div class="ia-card"><div class="lbl">Total Installs</div><div class="val"><?php echo number_format($total_installs); ?></div></div>
    <div class="ia-card"><div class="lbl">This Month</div><div class="val"><?php echo number_format($month_installs); ?></div></div>
    <div class="ia-card"><div class="lbl">Today</div><div class="val"><?php echo number_format($today_installs); ?></div></div>
    <div class="ia-card"><div class="lbl">Conversion Rate</div><div class="val"><?php echo $conversion; ?>%</div></div>
  </div>

  <div class="ia-grid">
    <div class="ia-panel"><h3>Installs Over Time (30 days)</h3><canvas id="chartTime" height="120"></canvas></div>
    <div class="ia-panel"><h3>Installs by Device</h3><canvas id="chartDevice" height="200"></canvas></div>
  </div>

  <div class="ia-row2">
    <div class="ia-panel"><h3>Installs by Source</h3><canvas id="chartSource" height="160"></canvas></div>
    <div class="ia-panel">
      <h3>Export</h3>
      <p style="color:#94a3b8;font-size:.85rem;margin:0 0 16px;">Download every recorded event as CSV.</p>
      <a class="ia-export" href="?export=csv">⬇ Download CSV</a>
    </div>
  </div>

  <div class="ia-panel">
    <h3>Recent Events (last 50)</h3>
    <div style="overflow-x:auto;">
      <table class="ia-table">
        <thead><tr><th>Time</th><th>Event</th><th>Device</th><th>Source</th><th>IP</th></tr></thead>
        <tbody>
        <?php if (!$recent): ?>
          <tr><td colspan="5" style="color:#94a3b8;">No events yet.</td></tr>
        <?php else: foreach ($recent as $e): ?>
          <tr>
            <td><?php echo htmlspecialchars($e['created_at']); ?></td>
            <td><span class="ia-badge"><?php echo htmlspecialchars($e['event_type']); ?></span></td>
            <td><?php echo htmlspecialchars($e['device_type']); ?></td>
            <td><?php echo htmlspecialchars($e['source']); ?></td>
            <td><?php echo htmlspecialchars($e['user_ip']); ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
(function(){
  if (typeof Chart === 'undefined') return;
  Chart.defaults.color = '#94a3b8';
  Chart.defaults.borderColor = 'rgba(148,163,184,.12)';
  var gold = '#d4af37';

  new Chart(document.getElementById('chartTime'), {
    type:'line',
    data:{ labels:<?php echo json_encode($daily_labels); ?>,
      datasets:[{ label:'Installs', data:<?php echo json_encode($daily_data); ?>,
        borderColor:gold, backgroundColor:'rgba(212,175,55,.15)', fill:true, tension:.3, pointRadius:2 }] },
    options:{ plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
  });

  new Chart(document.getElementById('chartDevice'), {
    type:'pie',
    data:{ labels:['Android','iOS','Desktop','Other'],
      datasets:[{ data:[<?php echo (int)$device_data['android']; ?>,<?php echo (int)$device_data['ios']; ?>,<?php echo (int)$device_data['desktop']; ?>,<?php echo (int)$device_data['other']; ?>],
        backgroundColor:['#3ddc84','#a3aaae','#60a5fa','#64748b'] }] },
    options:{ plugins:{legend:{position:'bottom'}} }
  });

  new Chart(document.getElementById('chartSource'), {
    type:'bar',
    data:{ labels:<?php echo json_encode(array_keys($source_data)); ?>,
      datasets:[{ label:'Installs', data:<?php echo json_encode(array_values($source_data)); ?>,
        backgroundColor:gold }] },
    options:{ plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
  });
})();
</script>
</body>
</html>
