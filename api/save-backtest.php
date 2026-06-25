<?php
// api/save-backtest.php
// POST JSON {title, config_json, results_json, is_public}
// → saves to saved_backtests → returns {id, share_token, share_url}

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

$raw = file_get_contents('php://input');
if (!$raw) { echo json_encode(['error' => 'Empty request']); exit; }

$input = json_decode($raw, true);
if (!$input) { echo json_encode(['error' => 'Invalid JSON']); exit; }

$user_id     = (int)$_SESSION['user_id'];
$title       = trim($input['title']       ?? 'Untitled Backtest');
$config_json = $input['config_json']      ?? '';
$results_json= $input['results_json']     ?? '';
$is_public   = !empty($input['is_public']) ? 1 : 0;

if (!$config_json || !$results_json) {
    echo json_encode(['error' => 'config_json and results_json are required']); exit;
}

// Validate JSON strings
if (!json_decode($config_json) || !json_decode($results_json)) {
    echo json_encode(['error' => 'Invalid JSON in config or results']); exit;
}

// Generate unique share token
$share_token = md5($user_id . microtime(true) . rand());

$stmt = $conn->prepare(
    "INSERT INTO saved_backtests
     (user_id, title, config_json, results_json, share_token, is_public)
     VALUES (?,?,?,?,?,?)"
);
$stmt->bind_param('issssi', $user_id, $title, $config_json, $results_json, $share_token, $is_public);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Database error: ' . $stmt->error]); exit;
}

$id = $stmt->insert_id;

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base_url  = $protocol . '://' . $_SERVER['HTTP_HOST'];
$share_url = $base_url . '/backtester.php?backtest_id=' . urlencode($share_token);

echo json_encode([
    'ok'          => true,
    'id'          => $id,
    'share_token' => $share_token,
    'share_url'   => $share_url
]);
