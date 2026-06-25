<?php
/**
 * Telegram Bot Connection Test
 * Visit this page in your browser: yoursite.com/telegram_test.php
 * Delete this file once everything is working.
 */
session_start();
require_once 'config/keys.php';

$token  = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : '';
$chatId = defined('TELEGRAM_CHAT_ID')   ? TELEGRAM_CHAT_ID   : '';

$results = [];

// 1. Check credentials are filled
$tokenOk  = $token  && $token  !== 'YOUR_BOT_TOKEN_HERE';
$chatOk   = $chatId && $chatId !== 'YOUR_GROUP_CHAT_ID_HERE';
$results[] = ['cURL extension', function_exists('curl_version') ? 'OK' : 'NOT FOUND', function_exists('curl_version')];
$results[] = ['Bot token set',  $tokenOk  ? 'OK ('.substr($token,0,10).'...)' : 'MISSING — fill in config/keys.php', $tokenOk];
$results[] = ['Chat ID set',    $chatOk   ? 'OK ('.$chatId.')' : 'MISSING — fill in config/keys.php', $chatOk];

// 2. Test the bot token via getMe
$botName = '—';
if ($tokenOk && function_exists('curl_version')) {
    $ch = curl_init("https://api.telegram.org/bot{$token}/getMe");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>8, CURLOPT_SSL_VERIFYPEER=>true]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        $results[] = ['Bot token valid', 'cURL error: '.$err, false];
    } else {
        $r = json_decode($raw, true);
        if ($r['ok'] ?? false) {
            $botName = '@'.($r['result']['username'] ?? '?');
            $results[] = ['Bot token valid', 'YES — bot is '.$botName, true];
        } else {
            $results[] = ['Bot token valid', 'NO — '.($r['description'] ?? $raw), false];
        }
    }
}

// 3. Send a test message to the group
$sendResult = '—';
$sendOk = false;
if ($tokenOk && $chatOk && function_exists('curl_version')) {
    $text = "✅ <b>Wise Invest — Telegram Test</b>\n\nThis is a test message from your website. If you see this, alerts are working correctly!\n\n🕐 ".date('d M Y H:i:s');
    $payload = json_encode(['chat_id'=>$chatId,'text'=>$text,'parse_mode'=>'HTML','disable_web_page_preview'=>true]);
    $ch = curl_init("https://api.telegram.org/bot{$token}/sendMessage");
    curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>true]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        $sendResult = 'cURL error: '.$err;
    } else {
        $r = json_decode($raw, true);
        $sendOk = $r['ok'] ?? false;
        $sendResult = $sendOk ? 'Message sent to group!' : ('Error: '.($r['description'] ?? $raw));
    }
    $results[] = ['Send test message', $sendResult, $sendOk];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Telegram Test — Wise Invest</title>
<style>
body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:40px;max-width:700px;margin:0 auto}
h1{color:#d4af37;margin-bottom:8px}
p{color:#64748b;margin:0 0 32px}
.row{display:flex;align-items:flex-start;gap:14px;padding:14px 18px;border-radius:10px;margin-bottom:10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07)}
.ok{border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.07)}
.fail{border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.07)}
.icon{font-size:1.1rem;flex-shrink:0;margin-top:2px}
.label{font-weight:700;color:#f1f5f9;margin-bottom:3px}
.value{font-size:.85rem;color:#94a3b8}
.value.ok{color:#4ade80}
.value.fail{color:#f87171}
.fix{margin-top:32px;background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.25);border-radius:12px;padding:20px 24px}
.fix h2{color:#d4af37;margin:0 0 12px;font-size:1rem}
.fix code{background:rgba(0,0,0,.4);padding:2px 7px;border-radius:5px;color:#a78bfa;font-size:.9rem}
.fix pre{background:rgba(0,0,0,.5);padding:14px;border-radius:8px;overflow-x:auto;color:#d4af37;font-size:.82rem;margin:8px 0 0}
a{color:#38bdf8}
</style>
</head>
<body>
<h1>🤖 Telegram Alert — Connection Test</h1>
<p>Checking your bot configuration and sending a test message to your group.</p>

<?php foreach ($results as [$label, $value, $ok]): ?>
<div class="row <?= $ok ? 'ok' : 'fail' ?>">
    <div class="icon"><?= $ok ? '✅' : '❌' ?></div>
    <div>
        <div class="label"><?= htmlspecialchars($label) ?></div>
        <div class="value <?= $ok ? 'ok' : 'fail' ?>"><?= htmlspecialchars($value) ?></div>
    </div>
</div>
<?php endforeach; ?>

<?php if (!$tokenOk || !$chatOk): ?>
<div class="fix">
    <h2>📋 How to configure</h2>
    <p style="color:#94a3b8;font-size:.85rem;margin:0 0 12px">
        Open <code>config/keys.php</code> and fill in your real values:
    </p>
    <pre>define('TELEGRAM_BOT_TOKEN', 'paste-your-bot-token-here');
define('TELEGRAM_CHAT_ID',   '-paste-your-group-chat-id-here');</pre>
    <p style="color:#64748b;font-size:.8rem;margin:12px 0 0">
        To get the Chat ID: send a message in your group, then open<br>
        <a href="https://api.telegram.org/bot<?= htmlspecialchars($token ?: 'YOUR_TOKEN') ?>/getUpdates" target="_blank">
            https://api.telegram.org/bot<?= htmlspecialchars($token ?: 'YOUR_TOKEN') ?>/getUpdates
        </a>
        and look for <code>"chat":{"id":-123456789}</code>
    </p>
</div>
<?php endif; ?>

<?php if ($sendOk): ?>
<div style="margin-top:24px;padding:18px 22px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);border-radius:12px;color:#4ade80;font-weight:700">
    ✅ Everything is working! Check your Telegram group — a test message should have arrived.<br>
    <span style="font-weight:400;font-size:.85rem;color:#86efac">Delete this file once confirmed: <code style="color:#a78bfa">telegram_test.php</code></span>
</div>
<?php endif; ?>

</body>
</html>
