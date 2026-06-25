<?php
// FMP API Key (Keep this file private)
define('FMP_API_KEY', 'IBerQG3Y5BPP6ck50JGPREV5w4iiHJ60');

// ── Telegram Support Alert Bot ──────────────────────────
// Step 1: Message @BotFather on Telegram → /newbot → copy the token below
// Step 2: Add your bot to your support group
// Step 3: Get the group chat ID (see instructions in includes/telegram.php)
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');
define('TELEGRAM_CHAT_ID',   'YOUR_GROUP_CHAT_ID_HERE');

// ── Web Push (VAPID) ──────────────────────────────────────
// Run generate_vapid.php ONCE to generate your keys, then paste below and delete that file.
define('VAPID_PUBLIC_KEY',  'YOUR_VAPID_PUBLIC_KEY');
define('VAPID_PRIVATE_KEY', 'YOUR_VAPID_PRIVATE_KEY_PEM');
define('VAPID_SUBJECT',     'mailto:support@wiseinvest.com');
?>
