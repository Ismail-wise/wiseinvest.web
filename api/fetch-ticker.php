<?php
// api/fetch-ticker.php
// Proxies Yahoo Finance, caches in DB. CORS-safe for AJAX calls.
// GET ?ticker=VOO              → full monthly price history
// GET ?ticker=VOO&mode=search  → name + last price only (fast)

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

$ticker = strtoupper(trim($_GET['ticker'] ?? ''));
$mode   = $_GET['mode'] ?? 'history'; // 'history' | 'search'

if (!$ticker || !preg_match('/^[A-Z0-9.\-^]{1,15}$/', $ticker)) {
    echo json_encode(['error' => 'Invalid ticker']); exit;
}

// ── Check Myanmar stocks first ─────────────────────────────────
$stmt = $conn->prepare(
    "SELECT MIN(price_date) AS first_d, MAX(price_date) AS last_d, COUNT(*) AS cnt
     FROM myanmar_stocks WHERE ticker=?"
);
$stmt->bind_param('s', $ticker);
$stmt->execute();
$mm = $stmt->get_result()->fetch_assoc();

if ($mm && $mm['cnt'] > 0) {
    if ($mode === 'search') {
        $name_q = $conn->prepare("SELECT company_name FROM myanmar_stocks WHERE ticker=? LIMIT 1");
        $name_q->bind_param('s', $ticker);
        $name_q->execute();
        $nm = $name_q->get_result()->fetch_assoc();
        echo json_encode([
            'ok' => true, 'ticker' => $ticker,
            'name' => $nm['company_name'] ?? $ticker,
            'market' => 'YSX', 'source' => 'myanmar_stocks'
        ]);
        exit;
    }
    // Return full price history
    $pq = $conn->prepare(
        "SELECT DATE_FORMAT(price_date,'%Y-%m') AS ym, close_price
         FROM myanmar_stocks WHERE ticker=? ORDER BY price_date ASC"
    );
    $pq->bind_param('s', $ticker);
    $pq->execute();
    $res = $pq->get_result();
    $prices = [];
    while ($row = $res->fetch_assoc()) $prices[$row['ym']] = floatval($row['close_price']);
    echo json_encode([
        'ok' => true, 'ticker' => $ticker,
        'name' => $ticker, 'source' => 'myanmar_stocks', 'prices' => $prices
    ]);
    exit;
}

// ── Check DB cache (24-hour TTL) ────────────────────────────────
$stmt = $conn->prepare(
    "SELECT price_data, ticker_name, cached_at FROM backtest_price_cache WHERE ticker=?"
);
$stmt->bind_param('s', $ticker);
$stmt->execute();
$cached = $stmt->get_result()->fetch_assoc();

$cache_fresh = $cached && strtotime($cached['cached_at']) > time() - 86400;

if ($cache_fresh) {
    if ($mode === 'search') {
        $prices = json_decode($cached['price_data'], true) ?? [];
        $last   = $prices ? end($prices) : null;
        echo json_encode([
            'ok' => true, 'ticker' => $ticker,
            'name' => $cached['ticker_name'] ?? $ticker,
            'last_price' => $last
        ]);
        exit;
    }
    echo json_encode([
        'ok'     => true,
        'ticker' => $ticker,
        'name'   => $cached['ticker_name'] ?? $ticker,
        'prices' => json_decode($cached['price_data'], true)
    ]);
    exit;
}

// ── Fetch from Yahoo Finance ────────────────────────────────────
$range    = '20y';
$interval = '1mo';
$url = "https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}"
     . "?range={$range}&interval={$interval}&includeAdjustedClose=true";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept: application/json',
        'Accept-Language: en-US,en;q=0.9'
    ]
]);
$raw  = curl_exec($ch);
$err  = curl_error($ch);
curl_close($ch);

if (!$raw || $err) {
    // Return stale cache if available
    if ($cached) {
        echo json_encode([
            'ok' => true, 'ticker' => $ticker,
            'name' => $cached['ticker_name'] ?? $ticker,
            'prices' => json_decode($cached['price_data'], true),
            'stale' => true
        ]);
        exit;
    }
    echo json_encode(['error' => "Could not fetch data for {$ticker}"]); exit;
}

$json   = json_decode($raw, true);
$result = $json['chart']['result'][0] ?? null;
$error  = $json['chart']['error']     ?? null;

if ($error || !$result) {
    echo json_encode(['error' => "Ticker {$ticker} not found on Yahoo Finance"]); exit;
}

$meta       = $result['meta']       ?? [];
$timestamps = $result['timestamp']  ?? [];
$adj_closes = $result['indicators']['adjclose'][0]['adjclose'] ?? [];

if (empty($timestamps)) {
    echo json_encode(['error' => "No price data for {$ticker}"]); exit;
}

$prices    = [];
$ticker_name = $meta['shortName'] ?? $meta['longName'] ?? $ticker;

foreach ($timestamps as $i => $ts) {
    if (!isset($adj_closes[$i]) || $adj_closes[$i] === null) continue;
    $ym = date('Y-m', $ts);
    $prices[$ym] = round(floatval($adj_closes[$i]), 4);
}
ksort($prices);

// ── Save / update cache ─────────────────────────────────────────
$price_json = json_encode($prices);
$stmt = $conn->prepare(
    "INSERT INTO backtest_price_cache (ticker, price_data, ticker_name, cached_at)
     VALUES (?,?,?,NOW())
     ON DUPLICATE KEY UPDATE price_data=?, ticker_name=?, cached_at=NOW()"
);
$stmt->bind_param('sssss', $ticker, $price_json, $ticker_name, $price_json, $ticker_name);
$stmt->execute();

if ($mode === 'search') {
    $last = $prices ? end($prices) : null;
    echo json_encode([
        'ok' => true, 'ticker' => $ticker,
        'name' => $ticker_name, 'last_price' => $last
    ]);
    exit;
}

echo json_encode([
    'ok'     => true,
    'ticker' => $ticker,
    'name'   => $ticker_name,
    'prices' => $prices
]);
