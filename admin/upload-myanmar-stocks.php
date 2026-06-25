<?php
session_start();
require_once dirname(__DIR__) . '/includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

include dirname(__DIR__) . '/includes/header.php';

$user_id = (int)$_SESSION['user_id'];
$msg     = '';
$msg_type= 'ok';

// ── Handle CSV Upload ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $ticker       = strtoupper(trim($_POST['ticker'] ?? ''));
    $company_name = trim($_POST['company_name'] ?? '');

    if (!$ticker || !preg_match('/^[A-Z0-9\-]{1,20}$/', $ticker)) {
        $msg = 'Invalid ticker symbol.'; $msg_type = 'err';
    } elseif ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $msg = 'File upload error: ' . $_FILES['csv_file']['error']; $msg_type = 'err';
    } else {
        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $msg = 'Only CSV files are allowed.'; $msg_type = 'err';
        } else {
            $fp      = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $header  = fgetcsv($fp); // skip header row
            if (!$header) { $msg = 'CSV appears empty.'; $msg_type = 'err'; fclose($fp); goto done; }

            // Normalize header: find date, close, volume columns
            $hdr = array_map('strtolower', array_map('trim', $header));
            $date_col   = false; $close_col  = false; $vol_col = false;
            foreach ($hdr as $ci => $col) {
                if (in_array($col, ['date','price_date','day']))           $date_col  = $ci;
                if (in_array($col, ['close','close_price','price','adj close','adjclose'])) $close_col = $ci;
                if (in_array($col, ['volume','vol']))                       $vol_col   = $ci;
            }
            if ($date_col === false || $close_col === false) {
                $msg = 'CSV must have "date" and "close" columns (case-insensitive). Found: ' . implode(', ', $hdr);
                $msg_type = 'err'; fclose($fp); goto done;
            }

            $stmt = $conn->prepare(
                "INSERT INTO myanmar_stocks
                 (ticker, company_name, price_date, close_price, volume, uploaded_by)
                 VALUES (?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE close_price=VALUES(close_price), volume=VALUES(volume), uploaded_by=VALUES(uploaded_by)"
            );
            $inserted = 0; $skipped = 0; $errors = 0;

            while (($row = fgetcsv($fp)) !== false) {
                if (count($row) < 2) { $skipped++; continue; }
                $raw_date  = trim($row[$date_col]  ?? '');
                $raw_close = trim($row[$close_col] ?? '');
                $raw_vol   = isset($vol_col) && $vol_col !== false ? (int)str_replace(',', '', $row[$vol_col] ?? 0) : 0;

                // Parse date (try multiple formats)
                $ts = strtotime($raw_date);
                if (!$ts || $ts < 0) {
                    // Try YYYY/MM/DD and DD/MM/YYYY
                    $ts = strtotime(str_replace('/', '-', $raw_date));
                }
                if (!$ts || $ts < 0) { $skipped++; continue; }
                $price_date = date('Y-m-d', $ts);

                $close = floatval(str_replace(',', '', $raw_close));
                if ($close <= 0) { $skipped++; continue; }

                $stmt->bind_param('sssdii', $ticker, $company_name, $price_date, $close, $raw_vol, $user_id);
                if ($stmt->execute()) $inserted++;
                else { $errors++; }
            }
            fclose($fp);

            // Invalidate any Yahoo Finance cache for this ticker
            $del = $conn->prepare("DELETE FROM backtest_price_cache WHERE ticker=?");
            $del->bind_param('s', $ticker);
            $del->execute();

            $msg = "Upload complete for {$ticker}: {$inserted} rows inserted/updated, {$skipped} skipped, {$errors} errors.";
            $msg_type = 'ok';
        }
    }
}
done:

// ── Get existing Myanmar tickers ───────────────────────────────
$tickers_q = $conn->query(
    "SELECT ticker, company_name, COUNT(*) AS rows, MIN(price_date) AS first_d, MAX(price_date) AS last_d
     FROM myanmar_stocks GROUP BY ticker, company_name ORDER BY ticker"
);
$myanmar_tickers = $tickers_q ? $tickers_q->fetch_all(MYSQLI_ASSOC) : [];
?>
<style>
body{background:#020617;color:#f1f5f9;font-family:'Padauk','Segoe UI',sans-serif;margin:0}
.wrap{max-width:860px;margin:60px auto;padding:24px}
.card{background:rgba(10,18,38,.9);border:1px solid rgba(255,255,255,.08);border-radius:18px;padding:28px;margin-bottom:24px}
h1{font-size:1.5rem;color:#d4af37;margin:0 0 4px}
.sub{color:rgba(148,163,184,.7);font-size:.85rem;margin-bottom:24px}
label{display:block;font-size:.82rem;font-weight:600;color:rgba(148,163,184,.85);margin-bottom:6px}
input,select{width:100%;padding:10px 14px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#f1f5f9;font-size:.9rem;box-sizing:border-box;margin-bottom:16px}
input[type=file]{padding:8px 14px}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.btn{padding:11px 24px;background:linear-gradient(135deg,#d4af37,#f5d679);border:none;border-radius:10px;color:#020617;font-weight:700;font-size:.9rem;cursor:pointer;width:100%}
.msg{padding:12px 16px;border-radius:10px;font-size:.85rem;margin-bottom:20px}
.msg.ok {background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#10b981}
.msg.err{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#ef4444}
table{width:100%;border-collapse:collapse;font-size:.84rem}
th{text-align:left;padding:10px 14px;background:rgba(255,255,255,.05);color:rgba(148,163,184,.8);font-weight:700;font-size:.72rem;text-transform:uppercase;letter-spacing:1px}
td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04);color:#e2e8f0}
.del-btn{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#ef4444;padding:4px 12px;border-radius:8px;cursor:pointer;font-size:.78rem}
</style>

<div class="wrap">
  <h1><i class="fas fa-upload"></i> Myanmar Stock Upload</h1>
  <p class="sub">Upload CSV historical data for YSX (Myanmar Stock Exchange) tickers. Format: date, close, volume.</p>

  <?php if ($msg): ?>
  <div class="msg <?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="post" enctype="multipart/form-data">
      <div class="row2">
        <div>
          <label>Ticker Symbol *</label>
          <input type="text" name="ticker" placeholder="e.g. FMI, KBZ, TMH" required maxlength="20"
                 style="text-transform:uppercase" value="<?= htmlspecialchars($_POST['ticker'] ?? '') ?>">
        </div>
        <div>
          <label>Company Name (optional)</label>
          <input type="text" name="company_name" placeholder="e.g. First Myanmar Investment"
                 value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
        </div>
      </div>
      <label>CSV File * (must have columns: date, close, volume)</label>
      <input type="file" name="csv_file" accept=".csv" required>
      <p style="font-size:.78rem;color:rgba(148,163,184,.6);margin-top:-10px;margin-bottom:16px">
        Supported date formats: YYYY-MM-DD, MM/DD/YYYY, DD-MM-YYYY.<br>
        Existing rows for same ticker+date will be overwritten.
      </p>
      <button type="submit" class="btn"><i class="fas fa-upload"></i> Upload CSV</button>
    </form>
  </div>

  <?php if (!empty($myanmar_tickers)): ?>
  <div class="card">
    <h2 style="font-size:1rem;margin:0 0 16px;color:#f1f5f9"><i class="fas fa-database" style="color:#d4af37"></i> Uploaded Tickers</h2>
    <table>
      <thead><tr><th>Ticker</th><th>Company</th><th>Rows</th><th>First Date</th><th>Last Date</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($myanmar_tickers as $t): ?>
      <tr>
        <td><strong><?= htmlspecialchars($t['ticker']) ?></strong></td>
        <td><?= htmlspecialchars($t['company_name'] ?? '—') ?></td>
        <td><?= number_format($t['rows']) ?></td>
        <td><?= htmlspecialchars($t['first_d']) ?></td>
        <td><?= htmlspecialchars($t['last_d']) ?></td>
        <td>
          <form method="post" style="margin:0" onsubmit="return confirm('Delete all data for <?= htmlspecialchars($t['ticker']) ?>?')">
            <input type="hidden" name="delete_ticker" value="<?= htmlspecialchars($t['ticker']) ?>">
            <button type="submit" class="del-btn">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_ticker'])) {
    $dt = strtoupper(trim($_POST['delete_ticker']));
    $d  = $conn->prepare("DELETE FROM myanmar_stocks WHERE ticker=?");
    $d->bind_param('s', $dt);
    $d->execute();
    $d2 = $conn->prepare("DELETE FROM backtest_price_cache WHERE ticker=?");
    $d2->bind_param('s', $dt);
    $d2->execute();
    echo "<script>location.reload()</script>";
}
include dirname(__DIR__) . '/includes/footer.php';
?>
