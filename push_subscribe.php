<?php
/**
 * Receives a Web Push subscription from the browser and stores it in the DB.
 * Called via fetch() from the front-end after the user grants notification permission.
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'not logged in']);
    exit();
}

include 'includes/db.php';

// Auto-create table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS push_subscriptions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    endpoint   TEXT NOT NULL,
    p256dh     TEXT NOT NULL,
    auth       TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_endpoint (user_id, endpoint(500))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$endpoint = $data['endpoint'] ?? '';
$p256dh   = $data['keys']['p256dh'] ?? '';
$auth     = $data['keys']['auth']   ?? '';

if (!$endpoint || !$p256dh || !$auth) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid payload']);
    exit();
}

$uid  = intval($_SESSION['user_id']);
$stmt = $conn->prepare(
    "INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE p256dh=VALUES(p256dh), auth=VALUES(auth), created_at=NOW()"
);
$stmt->bind_param("isss", $uid, $endpoint, $p256dh, $auth);
$stmt->execute();

echo json_encode(['ok' => true]);
