<?php
// api/load-backtest.php
// GET ?token=xxx  or  ?id=123
// Returns saved backtest config + results JSON

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

$user_id = (int)$_SESSION['user_id'];
$token   = trim($_GET['token'] ?? '');
$id      = (int)($_GET['id']   ?? 0);

if (!$token && !$id) {
    echo json_encode(['error' => 'Provide token or id']); exit;
}

if ($token) {
    $stmt = $conn->prepare(
        "SELECT id, user_id, title, config_json, results_json, is_public, created_at
         FROM saved_backtests WHERE share_token=? LIMIT 1"
    );
    $stmt->bind_param('s', $token);
} else {
    $stmt = $conn->prepare(
        "SELECT id, user_id, title, config_json, results_json, is_public, created_at
         FROM saved_backtests WHERE id=? LIMIT 1"
    );
    $stmt->bind_param('i', $id);
}
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['error' => 'Backtest not found']); exit;
}

// Only allow owner to load private backtests
if (!$row['is_public'] && $row['user_id'] != $user_id) {
    echo json_encode(['error' => 'Access denied']); exit;
}

echo json_encode([
    'ok'          => true,
    'id'          => $row['id'],
    'title'       => $row['title'],
    'config'      => json_decode($row['config_json'],  true),
    'results'     => json_decode($row['results_json'], true),
    'created_at'  => $row['created_at'],
    'is_owner'    => ($row['user_id'] == $user_id)
]);
