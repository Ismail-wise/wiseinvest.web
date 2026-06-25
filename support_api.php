<?php
// session_start() MUST be first
session_start();
date_default_timezone_set('Asia/Bangkok');
ignore_user_abort(true); // keep running even if browser disconnects
set_time_limit(30);

include 'includes/db.php';
include 'includes/telegram.php';
include 'includes/web_push.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- 1. SEND MESSAGE (User or Admin) ---
if ($action == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Admin replying (must have target_id + admin role)
    if (isset($_POST['target_id']) && !empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $user_id = intval($_POST['target_id']);
        $sender  = 'admin';
    }
    // User sending
    elseif (!empty($_SESSION['user_id'])) {
        $user_id = intval($_SESSION['user_id']);
        $sender  = 'user';
    } else {
        tgLog("BLOCKED: no session user_id, action=send");
        die("Unauthorized");
    }

    // Raw message for Telegram (before DB escaping)
    $raw_message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Cleaned for DB
    $message = clean($raw_message);

    if (!empty($raw_message)) {
        $stmt = $conn->prepare("INSERT INTO support_messages (user_id, message, sender) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $message, $sender);
        $stmt->execute();

        tgLog("DB saved — user_id=$user_id sender=$sender msg=" . substr($raw_message, 0, 60));

        // ── Push notification: only for admin replies ──
        if ($sender === 'admin') {
            push_notify_user($user_id, $conn);
        }

        // ── Telegram alert: only for user messages ──
        if ($sender === 'user') {
            // Look up user info — use fallback so alert always fires
            $name  = 'Unknown User (#' . $user_id . ')';
            $email = 'unknown';
            $uq = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
            if ($uq) {
                $uq->bind_param("i", $user_id);
                $uq->execute();
                $urow = $uq->get_result()->fetch_assoc();
                if ($urow) {
                    $name  = $urow['fullname'];
                    $email = $urow['email'];
                }
            }
            tgLog("Calling sendSupportAlert for user '$name'");
            $ok = sendSupportAlert($name, $email, $raw_message, $user_id);
            tgLog("sendSupportAlert result: " . ($ok ? 'SUCCESS' : 'FAILED'));
        }
    } else {
        tgLog("Empty message — skipped");
    }
    exit();
}

// --- 2. FETCH MESSAGES ---
if ($action == 'fetch') {
    // Admin fetching a specific user's chat
    if (isset($_GET['target_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $user_id = intval($_GET['target_id']);
    } 
    // User fetching their own chat
    elseif (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        echo json_encode([]); exit();
    }

    $stmt = $conn->prepare("SELECT message, sender, created_at FROM support_messages WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'msg' => htmlspecialchars($row['message']),
            'sender' => $row['sender'], // 'user' or 'admin'
            'time' => date('h:i A', strtotime($row['created_at']))
        ];
    }
    echo json_encode($messages);
    exit();
}

// --- 3. UNREAD COUNTS (admin sidebar polling) ---
if ($action == 'unread_counts' && !empty($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $res = $conn->query("
        SELECT user_id,
            COUNT(*) AS unread
        FROM support_messages sm
        WHERE sender='user'
          AND id > COALESCE(
            (SELECT MAX(id) FROM support_messages sm2
             WHERE sm2.user_id=sm.user_id AND sm2.sender='admin'), 0)
        GROUP BY user_id
    ");
    $counts = [];
    if ($res) { while ($row = $res->fetch_assoc()) $counts[$row['user_id']] = (int)$row['unread']; }
    echo json_encode($counts);
    exit();
}
?>