<?php
/**
 * VAPID Key Generator — run ONCE, copy the output into config/keys.php, then DELETE this file.
 * Visit: yoursite.com/generate_vapid.php
 */
if (!extension_loaded('openssl')) {
    die('OpenSSL extension is required but not loaded.');
}

$key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
if (!$key) {
    die('Failed to generate EC key pair. Check OpenSSL support: ' . openssl_error_string());
}

openssl_pkey_export($key, $privatePem);
$details = openssl_pkey_get_details($key);

// Public key: uncompressed point format 0x04 + x + y (65 bytes), base64url
$publicRaw    = "\x04" . $details['ec']['x'] . $details['ec']['y'];
$publicB64url = rtrim(strtr(base64_encode($publicRaw), '+/', '-_'), '=');

// Escape PEM for safe PHP single-quote string
$privatePemSafe = str_replace("'", "\\'", $privatePem);

?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>VAPID Key Generator — Wise Invest</title>
<style>
body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 40px; max-width: 900px; margin: 0 auto; }
h1 { color: #d4af37; }
.box { background: #1e293b; border: 1px solid rgba(212,175,55,.2); border-radius: 12px; padding: 24px; margin: 20px 0; }
pre { background: #0b1220; padding: 16px; border-radius: 8px; overflow-x: auto; color: #a78bfa; font-size: .85rem; white-space: pre-wrap; word-break: break-all; }
.warn { color: #f87171; background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); border-radius: 8px; padding: 12px 16px; margin-top: 20px; }
</style>
</head>
<body>
<h1>🔑 VAPID Key Generator</h1>

<div class="box">
<p>Copy the three lines below and paste them into <code>config/keys.php</code>, replacing the placeholder values.</p>
<pre><?php
echo "define('VAPID_PUBLIC_KEY',  '" . $publicB64url . "');\n";
echo "define('VAPID_PRIVATE_KEY', '" . $privatePemSafe . "');\n";
echo "define('VAPID_SUBJECT',     'mailto:support@wiseinvest.com');";
?></pre>
</div>

<div class="warn">
⚠️ <strong>DELETE this file immediately after copying the keys.</strong> It regenerates new keys on every visit, which would invalidate existing push subscriptions.
</div>
</body>
</html>
