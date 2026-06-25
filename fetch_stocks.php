<?php
// ============================================================
// fetch_stocks.php
// Fetches live quotes from Finnhub and upserts them into the
// stock_cache table. Safe to run from the browser to test.
// ============================================================

require_once 'includes/db.php';         // provides $conn (mysqli)
require_once 'includes/api_config.php'; // provides FINNHUB_API_KEY

header('Content-Type: text/plain; charset=utf-8');

// ── Symbols to track: symbol => display name ──
$symbols = [
    'AAPL'  => 'Apple',
    'MSFT'  => 'Microsoft',
    'NVDA'  => 'Nvidia',
    'AMZN'  => 'Amazon',
    'TSLA'  => 'Tesla',
    'GOOGL' => 'Google',
    'META'  => 'Meta',
    'VOO'   => 'S&P 500 ETF',
    'QQQ'   => 'Nasdaq 100 ETF',
    'DIA'   => 'Dow Jones ETF',
];

// ── Prepared upsert statement ──
$stmt = $conn->prepare("
    INSERT INTO stock_cache (symbol, display_name, price, change_amount, change_percent)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        display_name  = VALUES(display_name),
        price         = VALUES(price),
        change_amount = VALUES(change_amount),
        change_percent = VALUES(change_percent)
");

if (!$stmt) {
    die("Failed to prepare statement: " . $conn->error);
}

$updated = 0;
$skipped = 0;

foreach ($symbols as $symbol => $display_name) {

    $url = "https://finnhub.io/api/v1/quote?symbol=" . urlencode($symbol)
         . "&token=" . FINNHUB_API_KEY;

    // Fetch quote; suppress warnings so one bad call can't crash the loop
    $response = @file_get_contents($url);

    if ($response === false) {
        echo "SKIP  $symbol — no response from API\n";
        $skipped++;
        continue;
    }

    $data = json_decode($response, true);

    // Finnhub: c = current price, d = change amount, dp = change percent
    $price = isset($data['c'])  ? (float)$data['c']  : 0;

    if ($price <= 0) {
        echo "SKIP  $symbol — no valid price (c = 0)\n";
        $skipped++;
        continue;
    }

    $change_amount  = isset($data['d'])  ? (float)$data['d']  : 0;
    $change_percent = isset($data['dp']) ? (float)$data['dp'] : 0;

    $stmt->bind_param("ssddd", $symbol, $display_name, $price, $change_amount, $change_percent);

    if ($stmt->execute()) {
        echo "OK    $symbol ($display_name) — \$$price  ($change_percent%)\n";
        $updated++;
    } else {
        echo "ERROR $symbol — DB error: " . $stmt->error . "\n";
        $skipped++;
    }
}

$stmt->close();

echo "\n--------------------------------------\n";
echo "Done. $updated updated, $skipped skipped, of " . count($symbols) . " symbols.\n";
?>
