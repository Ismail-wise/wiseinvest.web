<?php
/**
 * api/bulk-upgrade-users.php
 * Bulk-upgrade selected users to a chosen membership tier with optional expiry.
 * POST (JSON). Admin only. Writes an audit row to upgrade_log per user.
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once dirname(__DIR__) . '/includes/db.php';

// ── Admin gate ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

// ── Parse JSON body ──────────────────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

// user_ids → unique positive ints
$user_ids = [];
if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
    foreach ($data['user_ids'] as $id) {
        $id = (int)$id;
        if ($id > 0) { $user_ids[$id] = true; }
    }
    $user_ids = array_keys($user_ids);
}
if (empty($user_ids)) {
    echo json_encode(['success' => false, 'error' => 'No users selected']);
    exit;
}

// new_level → must be a known tier
$allowed_levels = ['free','premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond'];
$new_level = isset($data['new_level']) ? trim($data['new_level']) : '';
if (!in_array($new_level, $allowed_levels, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid level']);
    exit;
}

// ── Compute expiry server-side from duration ─────────────────
$duration = isset($data['duration']) ? trim($data['duration']) : 'life';
$expires_at = null; // NULL = lifetime (or free = no expiry)

if ($new_level !== 'free') {
    if ($duration === 'custom') {
        $cd = isset($data['custom_date']) ? trim((string)$data['custom_date']) : '';
        $ts = $cd ? strtotime($cd) : false;
        if (!$ts) {
            echo json_encode(['success' => false, 'error' => 'Invalid custom date']);
            exit;
        }
        $expires_at = date('Y-m-d H:i:s', $ts);
    } elseif ($duration !== 'life') {
        $map = ['30d' => '+30 days', '90d' => '+90 days', '6m' => '+6 months', '1y' => '+1 year'];
        if (!isset($map[$duration])) {
            echo json_encode(['success' => false, 'error' => 'Invalid duration']);
            exit;
        }
        $expires_at = date('Y-m-d H:i:s', strtotime($map[$duration]));
    }
}

// notes (optional, capped)
$notes = isset($data['notes']) && $data['notes'] !== '' ? mb_substr(trim((string)$data['notes']), 0, 1000) : null;
$admin_id = (int)$_SESSION['user_id'];

// ── Defensive: ensure upgrade_log exists ─────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS upgrade_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    previous_level VARCHAR(50) NULL,
    new_level VARCHAR(50) NOT NULL,
    expires_at DATETIME NULL,
    upgraded_by INT NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id), INDEX idx_admin (upgraded_by), INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ── Prepared statements ──────────────────────────────────────
$sel = $conn->prepare("SELECT membership_type FROM users WHERE id = ?");
$upd = $conn->prepare("UPDATE users SET membership_type = ?, premium_expiry = ? WHERE id = ?");
$log = $conn->prepare("INSERT INTO upgrade_log (user_id, previous_level, new_level, expires_at, upgraded_by, notes)
                       VALUES (?, ?, ?, ?, ?, ?)");

if (!$sel || !$upd || !$log) {
    echo json_encode(['success' => false, 'error' => 'DB prepare failed']);
    exit;
}

$upgraded = 0;
$errors   = [];

foreach ($user_ids as $uid) {
    // current level (for audit)
    $sel->bind_param("i", $uid);
    if (!$sel->execute()) { $errors[$uid] = 'lookup failed'; continue; }
    $res = $sel->get_result();
    if (!$res || $res->num_rows === 0) { $errors[$uid] = 'user not found'; continue; }
    $prev = $res->fetch_assoc()['membership_type'];

    // apply upgrade
    $upd->bind_param("ssi", $new_level, $expires_at, $uid);
    if (!$upd->execute()) { $errors[$uid] = 'update failed'; continue; }

    // audit log (failure here is non-fatal to the upgrade)
    $log->bind_param("isssis", $uid, $prev, $new_level, $expires_at, $admin_id, $notes);
    $log->execute();

    $upgraded++;
}

echo json_encode([
    'success'        => $upgraded > 0,
    'upgraded_count' => $upgraded,
    'errors'         => $errors,
    'error'          => $upgraded > 0 ? null : 'No users were upgraded',
]);
