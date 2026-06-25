<?php
/*
 * ═══════════════════════════════════════════════════════════
 *  WISE INVEST — TELEGRAM ALERT HELPER
 *  Sends a notification to your team's Telegram group
 *  whenever a user submits a support message.
 *
 *  HOW TO SET UP (one-time, ~5 minutes):
 *  ─────────────────────────────────────
 *  1. Open Telegram → search @BotFather → send /newbot
 *     Follow the prompts. Copy the token it gives you.
 *     Paste it in config/keys.php as TELEGRAM_BOT_TOKEN.
 *
 *  2. Add your new bot to your support group.
 *     (Open the group → Add member → search your bot's name)
 *
 *  3. Get the group's Chat ID:
 *     a. Send any message in the group.
 *     b. Visit this URL in your browser (replace TOKEN):
 *        https://api.telegram.org/bot<TOKEN>/getUpdates
 *     c. Look for "chat":{"id": -123456789 ...}
 *        Copy that number (it starts with a minus sign for groups).
 *     d. Paste it in config/keys.php as TELEGRAM_CHAT_ID.
 *
 *  4. Done! New user support messages will now ping your group.
 * ═══════════════════════════════════════════════════════════
 */

if (!defined('TELEGRAM_BOT_TOKEN')) {
    require_once __DIR__ . '/../config/keys.php';
}

/**
 * Write a timestamped line to telegram_log.txt (root dir).
 * Used for debugging — safe to leave in production.
 */
if (!function_exists('tgLog')) {
    function tgLog($msg) {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
        @file_put_contents(__DIR__ . '/../telegram_log.txt', $line, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Send an alert to the configured Telegram group.
 *
 * @param string $userName    Display name of the user who sent the message
 * @param string $userEmail   Email of the user
 * @param string $message     The support message content
 * @param int    $userId      User ID (for the admin panel link)
 * @return bool               true on success, false on failure / not configured
 */
function sendSupportAlert($userName, $userEmail, $message, $userId) {
    $token  = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
    $chatId = defined('TELEGRAM_CHAT_ID')   ? TELEGRAM_CHAT_ID   : '';

    // Skip silently if not configured yet
    if (!$token || $token === 'YOUR_BOT_TOKEN_HERE' ||
        !$chatId || $chatId === 'YOUR_GROUP_CHAT_ID_HERE') {
        return false;
    }

    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'yoursite.com';
    $adminUrl = $scheme . '://' . $host . '/admin.php?tab=chat';

    tgLog("sendSupportAlert — to=$chatId user='$userName' url=$adminUrl");

    $time = date('d M Y · H:i', time());

    // Trim message for preview (max 300 chars)
    $preview = mb_strlen($message) > 300
        ? mb_substr($message, 0, 297) . '…'
        : $message;

    // Escape only for Telegram HTML — do NOT double-encode (message is already plain text)
    $safeUser    = htmlspecialchars($userName,  ENT_XML1, 'UTF-8');
    $safeEmail   = htmlspecialchars($userEmail, ENT_XML1, 'UTF-8');
    $safeMessage = htmlspecialchars($preview,   ENT_XML1, 'UTF-8');
    $safeUrl     = htmlspecialchars($adminUrl,  ENT_XML1, 'UTF-8');

    $text = "🔔 <b>New Support Message</b>\n"
          . "━━━━━━━━━━━━━━━━━━━━━━\n\n"
          . "👤 <b>From:</b> {$safeUser}\n"
          . "📧 <b>Email:</b> {$safeEmail}\n"
          . "⏰ <b>Time:</b> {$time}\n\n"
          . "💬 <b>Message:</b>\n"
          . "{$safeMessage}\n\n"
          . "━━━━━━━━━━━━━━━━━━━━━━\n"
          . "<a href=\"{$safeUrl}\">📋 Open Admin Chat Panel</a>";

    $payload = json_encode([
        'chat_id'                  => $chatId,
        'text'                     => $text,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true,
    ]);

    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) {
        tgLog("cURL error: $err");
        error_log("[Telegram] cURL error: $err");
        return false;
    }

    $result = json_decode($response, true);
    if (!($result['ok'] ?? false)) {
        $desc = $result['description'] ?? $response;
        tgLog("API error (HTTP $httpCode): $desc");
        error_log("[Telegram] API error: $desc");
        return false;
    }

    tgLog("Alert sent OK (HTTP $httpCode)");
    return true;
}
