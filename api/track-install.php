<?php
// api/track-install.php
// Records PWA install funnel events into install_events.
// Accepts POST JSON: { event_type, device_type, browser, source }
// Returns: { success: true } or { success: false, error: "..." }

session_start();

// ── CORS (same-domain AJAX) ──────────────────────────────────────
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host   = $_SERVER['HTTP_HOST']   ?? '';
if ($origin && parse_url($origin, PHP_URL_HOST) === $host) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Pre-flight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

// ── Read JSON body (fallback to form-encoded) ────────────────────
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

// ── Validate event_type against ENUM ─────────────────────────────
$valid_events  = ['page_view', 'install_clicked', 'installed', 'uninstalled', 'ios_instructions_shown'];
$valid_devices = ['android', 'ios', 'desktop', 'other'];

$event_type = $input['event_type'] ?? '';
if (!in_array($event_type, $valid_events, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid event_type']);
    exit;
}

$device_type = $input['device_type'] ?? 'other';
if (!in_array($device_type, $valid_devices, true)) {
    $device_type = 'other';
}

// ── Sanitize remaining inputs ────────────────────────────────────
$browser = substr(trim((string)($input['browser'] ?? '')), 0, 50);

// source: JSON body → ?source= URL param → 'direct'
$source = $input['source'] ?? ($_GET['source'] ?? 'direct');
$source = preg_replace('/[^a-zA-Z0-9_\-]/', '', substr((string)$source, 0, 100));
if ($source === '') { $source = 'direct'; }

$user_ip    = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
$user_agent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000);
$referrer   = substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 1000);
$user_id    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// ── Insert (prepared statement) ──────────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO install_events
       (event_type, device_type, browser, source, user_ip, user_agent, referrer, user_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'DB prepare failed']);
    exit;
}
$stmt->bind_param(
    'sssssssi',
    $event_type, $device_type, $browser, $source,
    $user_ip, $user_agent, $referrer, $user_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Insert failed']);
}
$stmt->close();
