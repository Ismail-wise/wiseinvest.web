<?php
// Prevent multiple connections
if (!isset($conn)) {

    // ===== DATABASE CONFIG =====
    $host   = 'localhost';
    $user   = 'wisestel_unlock';
    $pass   = 'WiseInvest@2026';
    $dbname = 'wisestel_wiseinvest';
    $port   = 3306;

    // ===== CREATE CONNECTION =====
    $conn = new mysqli($host, $user, $pass, $dbname, $port);

    // ===== CHECK CONNECTION =====
    if ($conn->connect_error) {
        die("❌ Database Connection Failed: " . $conn->connect_error);
    }

    // ===== SET CHARSET (utf8mb4 for Burmese + all Unicode) =====
    $conn->set_charset("utf8mb4");
    $conn->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
    $conn->query("SET CHARACTER SET utf8mb4");
}

// ===== SAFE INPUT CLEANER =====
if (!function_exists('clean')) {
    function clean($data) {
        global $conn;
        return htmlspecialchars(
            mysqli_real_escape_string($conn, trim($data)),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}
?>
