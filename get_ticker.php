<?php
// ============================================================
// get_ticker.php
// Outputs cached stock data from stock_cache as JSON for the
// ticker bar. Always returns a JSON array; never errors out.
// ============================================================

require_once 'includes/db.php'; // provides $conn (mysqli)

header('Content-Type: application/json');

$out = [];

$result = $conn->query("
    SELECT symbol, display_name, price, change_amount, change_percent
    FROM stock_cache
    ORDER BY symbol
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $out[] = [
            'symbol'  => $row['symbol'],
            'name'    => $row['display_name'],
            'price'   => (float)$row['price'],
            'change'  => (float)$row['change_amount'],
            'percent' => (float)$row['change_percent'],
        ];
    }
}

echo json_encode($out);
?>
