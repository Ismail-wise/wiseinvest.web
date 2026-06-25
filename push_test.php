<?php
/**
 * Push Notification Diagnostic — visit this page to troubleshoot.
 * Delete this file once everything is working.
 */
session_start();
require_once 'includes/db.php';
if (!defined('VAPID_PUBLIC_KEY')) require_once 'config/keys.php';

$results = [];

// 1. HTTPS check
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
$results[] = ['HTTPS / Secure context', $isHttps ? 'YES' : 'NO — Web Push REQUIRES HTTPS (or localhost)', $isHttps];

// 2. VAPID keys
$vapidOk  = defined('VAPID_PUBLIC_KEY') && VAPID_PUBLIC_KEY !== 'YOUR_VAPID_PUBLIC_KEY';
$privOk   = defined('VAPID_PRIVATE_KEY') && VAPID_PRIVATE_KEY !== 'YOUR_VAPID_PRIVATE_KEY_PEM';
$results[] = ['VAPID public key set',  $vapidOk ? 'OK (' . substr(VAPID_PUBLIC_KEY,0,20) . '...)' : 'MISSING — run generate_vapid.php', $vapidOk];
$results[] = ['VAPID private key set', $privOk  ? 'OK' : 'MISSING — run generate_vapid.php', $privOk];

// 3. OpenSSL + cURL
$results[] = ['OpenSSL extension', extension_loaded('openssl') ? 'OK' : 'NOT LOADED', extension_loaded('openssl')];
$results[] = ['cURL extension',    extension_loaded('curl')    ? 'OK' : 'NOT LOADED', extension_loaded('curl')];

// 4. push_subscriptions table
$tableExists = false;
$subCount    = 0;
$subCountUser = 0;
$tableCheck = $conn->query("SHOW TABLES LIKE 'push_subscriptions'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    $tableExists = true;
    $r = $conn->query("SELECT COUNT(*) FROM push_subscriptions");
    if ($r) $subCount = (int)$r->fetch_row()[0];
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM push_subscriptions WHERE user_id=?");
        $stmt->bind_param("i", (int)$_SESSION['user_id']);
        $stmt->execute();
        $subCountUser = (int)$stmt->get_result()->fetch_row()[0];
    }
}
$results[] = ['push_subscriptions table', $tableExists ? "Exists ($subCount total subscriptions)" : 'Does NOT exist — user must open chat first to create it', $tableExists];
if (isset($_SESSION['user_id'])) {
    $results[] = ['Subscriptions for YOU (user #' . $_SESSION['user_id'] . ')', $subCountUser > 0 ? "$subCountUser device(s) registered" : 'NONE — you must open the chat widget and allow notifications', $subCountUser > 0];
}

// 5. sw.js reachable
$swPath = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . '/sw.js';
$results[] = ['sw.js file exists on disk', file_exists($swPath) ? 'OK' : 'NOT FOUND at ' . $swPath, file_exists($swPath)];

// 6. Test send (if user has subscriptions and keys are OK)
$testSent = null;
if (isset($_POST['send_test']) && $vapidOk && $privOk && $tableExists) {
    require_once 'includes/web_push.php';
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : intval($_POST['test_uid'] ?? 0);
    if ($uid) {
        push_notify_user($uid, $conn);
        $testSent = "Test push fired for user #$uid — check the device!";
    } else {
        $testSent = 'No user selected.';
    }
}

// List all subscribed users
$subbedUsers = [];
if ($tableExists) {
    $r = $conn->query("SELECT ps.user_id, u.fullname, COUNT(*) as devices, MAX(ps.created_at) as last_sub
                        FROM push_subscriptions ps JOIN users u ON u.id=ps.user_id
                        GROUP BY ps.user_id, u.fullname ORDER BY last_sub DESC LIMIT 30");
    if ($r) while ($row = $r->fetch_assoc()) $subbedUsers[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Push Notification Diagnostics — Wise Invest</title>
<style>
*{box-sizing:border-box}
body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:32px;max-width:760px;margin:0 auto}
h1{color:#d4af37;margin:0 0 6px}
p{color:#64748b;margin:0 0 28px}
.row{display:flex;align-items:flex-start;gap:14px;padding:12px 16px;border-radius:10px;margin-bottom:8px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)}
.row.ok{border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.06)}
.row.fail{border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.06)}
.icon{font-size:1.1rem;flex-shrink:0}
.label{font-weight:700;color:#f1f5f9;margin-bottom:2px;font-size:.88rem}
.value{font-size:.8rem;color:#64748b}
.value.ok{color:#4ade80}.value.fail{color:#f87171}
h2{color:#d4af37;font-size:.9rem;letter-spacing:2px;text-transform:uppercase;margin:28px 0 10px}
table{width:100%;border-collapse:collapse;font-size:.8rem}
th{text-align:left;color:#475569;font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;padding:6px 10px;border-bottom:1px solid rgba(255,255,255,.06)}
td{padding:8px 10px;border-bottom:1px solid rgba(255,255,255,.04);color:#94a3b8}
form{margin-top:20px;padding:20px;background:rgba(212,175,55,.06);border:1px solid rgba(212,175,55,.2);border-radius:12px}
input[type=number]{background:#0b1220;border:1px solid rgba(255,255,255,.15);color:#f1f5f9;padding:8px 12px;border-radius:8px;font-family:inherit;font-size:.85rem;outline:none;width:120px}
button{background:linear-gradient(135deg,#d4af37,#facc15);border:none;padding:9px 20px;border-radius:8px;font-weight:700;cursor:pointer;font-family:inherit;font-size:.85rem;color:#020617;margin-left:10px}
.notice{padding:14px 18px;border-radius:10px;margin-bottom:16px;font-size:.85rem}
.notice.info{background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.3);color:#93c5fd}
.notice.success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#4ade80}
.warn-box{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:10px;padding:16px;margin-top:24px;font-size:.82rem;color:#fcd34d;line-height:1.7}
a{color:#38bdf8}
</style>
</head>
<body>
<h1>🔔 Push Notification Diagnostics</h1>
<p>Checks every component in the push pipeline.</p>

<?php if ($testSent): ?>
<div class="notice success">✅ <?= htmlspecialchars($testSent) ?></div>
<?php endif; ?>

<?php foreach ($results as [$label, $value, $ok]): ?>
<div class="row <?= $ok ? 'ok' : 'fail' ?>">
    <div class="icon"><?= $ok ? '✅' : '❌' ?></div>
    <div>
        <div class="label"><?= htmlspecialchars($label) ?></div>
        <div class="value <?= $ok ? 'ok' : 'fail' ?>"><?= htmlspecialchars($value) ?></div>
    </div>
</div>
<?php endforeach; ?>

<?php if (!$vapidOk): ?>
<div class="warn-box">
    <strong>⚡ Action needed:</strong> VAPID keys are not configured.<br>
    1. Visit <a href="generate_vapid.php" target="_blank">generate_vapid.php</a> to generate your keys.<br>
    2. Copy the 3 <code>define()</code> lines into <code>config/keys.php</code>.<br>
    3. Delete <code>generate_vapid.php</code>.<br>
    4. Come back here and verify.
</div>
<?php endif; ?>

<?php if (!$isHttps): ?>
<div class="warn-box" style="border-color:rgba(239,68,68,.4);color:#fca5a5">
    <strong>🔒 HTTPS required:</strong> Web Push notifications only work over HTTPS.<br>
    If you're testing on localhost, it works without HTTPS.<br>
    On a live server you must have an SSL certificate installed.
</div>
<?php endif; ?>

<h2>Browser test</h2>
<div class="notice info" style="font-size:.8rem">
    Open your browser console (F12) on any page of your site, then open the chat widget.<br>
    Look for <strong>[Push]</strong> log lines. Any errors will appear there.
</div>

<?php if ($subbedUsers): ?>
<h2>Subscribed devices</h2>
<table>
<tr><th>User</th><th>ID</th><th>Devices</th><th>Last subscribed</th><th>Action</th></tr>
<?php foreach ($subbedUsers as $u): ?>
<tr>
    <td><?= htmlspecialchars($u['fullname']) ?></td>
    <td><?= $u['user_id'] ?></td>
    <td><?= $u['devices'] ?></td>
    <td><?= date('d M H:i', strtotime($u['last_sub'])) ?></td>
    <td>
        <form method="POST" style="margin:0;padding:0;background:none;border:none;">
            <input type="hidden" name="test_uid" value="<?= $u['user_id'] ?>">
            <button type="submit" name="send_test" style="padding:4px 12px;font-size:.75rem;">Send test</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php elseif ($tableExists): ?>
<div class="notice info">No subscriptions yet. Users must open the chat widget and click "Allow" when the browser asks for notification permission.</div>
<?php endif; ?>

<div class="warn-box" style="border-color:rgba(212,175,55,.25);color:#d4af37;margin-top:32px">
    ⚠️ Delete this file (<code>push_test.php</code>) once everything is confirmed working.
</div>
</body>
</html>
