<?php
include 'includes/db.php';
session_start();

// Set Timezone
date_default_timezone_set('Asia/Bangkok');

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. SEND MESSAGE
if ($action == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) { die("Unauthorized"); }

    $user_id = $_SESSION['user_id'];
    $message = clean($_POST['message']);

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();
    }
    exit();
}

// 2. FETCH MESSAGES
if ($action == 'fetch') {
    // Get last 50 messages + User Info + Membership Type
    $sql = "SELECT m.message, m.created_at, u.fullname, u.membership_type 
            FROM chat_messages m 
            JOIN users u ON m.user_id = u.id 
            ORDER BY m.created_at ASC 
            LIMIT 50";
    
    $result = $conn->query($sql);
    $messages = [];

    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'user' => htmlspecialchars($row['fullname']),
            'msg'  => htmlspecialchars($row['message']),
            'type' => $row['membership_type'],
            'time' => date('h:i A', strtotime($row['created_at']))
        ];
    }

    echo json_encode($messages);
    exit();
}
?>