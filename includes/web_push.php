<?php
/**
 * Minimal Web Push / VAPID helper.
 * Sends a push notification (no encrypted payload — SW shows static text).
 */

function _wp_b64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function _wp_der_to_raw_sig(string $der): ?string {
    if (strlen($der) < 2 || ord($der[0]) !== 0x30) return null;
    $offset = 1;
    $len    = ord($der[$offset++]);
    if ($len & 0x80) { $offset += ($len & 0x7f); }

    // r
    if (ord($der[$offset++]) !== 0x02) return null;
    $rLen = ord($der[$offset++]);
    $r    = substr($der, $offset, $rLen);
    $offset += $rLen;

    // s
    if (ord($der[$offset++]) !== 0x02) return null;
    $sLen = ord($der[$offset++]);
    $s    = substr($der, $offset, $sLen);

    $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
    $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);
    return $r . $s;
}

function _wp_send_one(string $endpoint, string $privatePem, string $publicB64url, string $subject): int {
    $parts    = parse_url($endpoint);
    $audience = $parts['scheme'] . '://' . $parts['host'];
    $expiry   = time() + 43200;

    $hdr  = _wp_b64url_encode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
    $pay  = _wp_b64url_encode(json_encode(['aud' => $audience, 'exp' => $expiry, 'sub' => $subject]));
    $sign = $hdr . '.' . $pay;

    $pk = openssl_pkey_get_private($privatePem);
    if (!$pk) return 0;

    openssl_sign($sign, $derSig, $pk, OPENSSL_ALGO_SHA256);
    $rawSig = _wp_der_to_raw_sig($derSig);
    if (!$rawSig) return 0;

    $jwt = $sign . '.' . _wp_b64url_encode($rawSig);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_HTTPHEADER     => [
            'Authorization: vapid t=' . $jwt . ',k=' . $publicB64url,
            'Content-Type: application/octet-stream',
            'Content-Length: 0',
            'TTL: 86400',
            'Urgency: high',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $code;
}

/**
 * Send a push notification to every subscribed device of a user.
 * Silently cleans up expired / invalid subscriptions.
 */
function push_notify_user(int $userId, mysqli $conn): void {
    if (!function_exists('curl_init') || !function_exists('openssl_sign')) return;

    if (!defined('VAPID_PUBLIC_KEY')) {
        $cfg = __DIR__ . '/../config/keys.php';
        if (file_exists($cfg)) require_once $cfg;
    }

    if (!defined('VAPID_PUBLIC_KEY')
        || VAPID_PUBLIC_KEY   === 'YOUR_VAPID_PUBLIC_KEY'
        || !defined('VAPID_PRIVATE_KEY')
        || !defined('VAPID_SUBJECT')) {
        return;
    }

    $stmt = $conn->prepare("SELECT endpoint FROM push_subscriptions WHERE user_id = ?");
    if (!$stmt) return;
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res  = $stmt->get_result();
    $dead = [];

    while ($row = $res->fetch_assoc()) {
        $code = _wp_send_one($row['endpoint'], VAPID_PRIVATE_KEY, VAPID_PUBLIC_KEY, VAPID_SUBJECT);
        // 404/410 = subscription permanently gone; everything else (429, 5xx, 0) is transient
        if ($code === 404 || $code === 410) $dead[] = $row['endpoint'];
    }

    foreach ($dead as $ep) {
        $d = $conn->prepare("DELETE FROM push_subscriptions WHERE user_id=? AND endpoint=?");
        $d->bind_param("is", $userId, $ep);
        $d->execute();
    }
}
