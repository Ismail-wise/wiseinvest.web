<?php
// ==========================================
// HEADER.PHP — SESSION & LANGUAGE SYSTEM
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

if (isset($_GET['lang'])) {
    $lang = ($_GET['lang'] === 'mm') ? 'mm' : 'en';
    $_SESSION['language'] = $lang;
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $url");
    exit();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$root = $protocol . "://" . $_SERVER['HTTP_HOST'];

if (file_exists('includes/db.php')) {
    include_once 'includes/db.php';
}

require_once __DIR__ . '/translations.php';

// Refresh membership type from DB on every page so admin upgrades take effect immediately
if (isset($_SESSION['user_id']) && isset($conn)) {
    $__uid = intval($_SESSION['user_id']);
    $__row = $conn->query("SELECT membership_type FROM users WHERE id=$__uid");
    if ($__row && $__row->num_rows > 0) {
        $_SESSION['user_type'] = $__row->fetch_row()[0] ?? 'free';
    }
}

// Helper used in nav conditions below
$_paid_types     = ['premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond'];
$_is_any_premium = in_array($_SESSION['user_type'] ?? 'free', $_paid_types);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['language'] ?? 'en'); ?>">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>html,body{background:#020617;}</style>
<title>Wise Invest - Global Market Intelligence</title>

<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#d4af37">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="WiseInvest">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<link rel="icon" type="image/x-icon" href="/icons/favicon.ico">
<link rel="icon" type="image/svg+xml" href="/icons/favicon.svg">
<link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo $root; ?>/style.css?v=<?php echo time(); ?>">

<style>
/* ============================================================
   HEADER — SELF-CONTAINED STYLES + ANIMATIONS
   Click-only dropdowns. No hover-open.
   ============================================================ */

/* ── Keyframe animations ─────────────────────────────────────── */

@keyframes logoGlow {
    0%, 100% { box-shadow: 0 0 8px rgba(212,175,55,0.2); }
    50%       { box-shadow: 0 0 22px rgba(212,175,55,0.55); }
}

@keyframes headerSlideDown {
    from { transform: translateY(-100%); opacity: 0; }
    to   { transform: translateY(0);     opacity: 1; }
}

@keyframes dropdownOpen {
    from { opacity: 0; transform: translateX(-50%) translateY(-10px) scale(0.97); }
    to   { opacity: 1; transform: translateX(-50%) translateY(0)     scale(1); }
}
@keyframes dropdownOpenRight {
    from { opacity: 0; transform: translateY(-10px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0)     scale(1); }
}

@keyframes dropItemSlide {
    from { opacity: 0; transform: translateX(-10px); }
    to   { opacity: 1; transform: translateX(0); }
}

@keyframes btnShine {
    0%   { background-position: -200% center; }
    100% { background-position: 200% center; }
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(212,175,55,0.4); }
    50%       { box-shadow: 0 0 0 6px rgba(212,175,55,0); }
}

@keyframes mobileOverlayIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* ── CSS variables ───────────────────────────────────────────── */
:root {
    --gold:                #d4af37;
    --dark-blue:           #0f172a;
    --text-light:          #e2e8f0;
    --border-glass:        rgba(255,255,255,0.08);
    --header-height:       70px;
    --ticker-height:       45px;
    --header-total-height: calc(var(--header-height) + var(--ticker-height));
    --drop-duration:       0.28s;
}

body { padding-top: var(--header-total-height); }
body.no-scroll { overflow: hidden; }

/* ── Fixed header ────────────────────────────────────────────── */
#main-header {
    position: fixed;
    top: 0; left: 0; width: 100%;
    z-index: 1000;
    animation: headerSlideDown 0.45s ease both;
}

/* ── Ticker bar ──────────────────────────────────────────────── */
.top-bar {
    height: var(--ticker-height);
    background: linear-gradient(90deg, #020918 0%, #030c1f 40%, #020918 100%);
    border-bottom: 1px solid rgba(212,175,55,0.18);
    overflow: hidden;
    display: flex;
    align-items: center;
    position: relative;
    box-shadow: 0 2px 20px rgba(0,0,0,0.5);
}

/* Shimmer line along the top edge */
.top-bar::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(212,175,55,0.5) 30%, rgba(212,175,55,0.8) 50%, rgba(212,175,55,0.5) 70%, transparent 100%);
}

/* Left fade mask */
.top-bar::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 100%;
    background: linear-gradient(to left, #020918 0%, transparent 100%);
    pointer-events: none;
    z-index: 2;
}

/* LIVE label badge */
.ticker-label {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 0 14px 0 16px;
    height: 100%;
    background: linear-gradient(135deg, rgba(212,175,55,0.12) 0%, rgba(212,175,55,0.05) 100%);
    border-right: 1px solid rgba(212,175,55,0.2);
    flex-shrink: 0;
    z-index: 3;
    white-space: nowrap;
}
.ticker-label-text {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    color: var(--gold);
    text-transform: uppercase;
    font-family: 'Inter', sans-serif;
}
.ticker-live-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
    flex-shrink: 0;
    box-shadow: 0 0 6px rgba(34,197,94,0.8);
    animation: livePulse 1.6s ease-in-out infinite;
}
@keyframes livePulse {
    0%, 100% { box-shadow: 0 0 4px rgba(34,197,94,0.7); transform: scale(1); }
    50%       { box-shadow: 0 0 10px rgba(34,197,94,1); transform: scale(1.25); }
}

/* Left fade mask on widget side */
.ticker-widget-wrap {
    flex: 1;
    overflow: hidden;
    position: relative;
    height: 100%;
}
.ticker-widget-wrap::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 40px; height: 100%;
    background: linear-gradient(to right, #020918 0%, transparent 100%);
    pointer-events: none;
    z-index: 2;
}

/* ── Main bar ────────────────────────────────────────────────── */
.main-bar {
    height: var(--header-height);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 32px;
    background: rgba(15,23,42,0.82);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-bottom: 1px solid var(--border-glass);
    transition: background 0.35s ease, box-shadow 0.35s ease;
}
#main-header.scrolled .main-bar {
    background: rgba(10,16,35,0.97);
    box-shadow: 0 4px 28px rgba(0,0,0,0.55);
    border-bottom-color: rgba(212,175,55,0.15);
}

/* ── Logo ────────────────────────────────────────────────────── */
.header-logo {
    display: flex; align-items: center; gap: 12px;
    text-decoration: none; flex-shrink: 0;
}
.header-logo img {
    height: 46px; width: 46px; border-radius: 50%;
    border: 2px solid rgba(212,175,55,0.5);
    object-fit: cover;
    animation: logoGlow 3.5s ease-in-out infinite;
    transition: transform 0.3s ease;
}
.header-logo:hover img { transform: scale(1.07) rotate(3deg); }

.logo-text { display: flex; flex-direction: column; line-height: 1.25; }
.logo-title {
    font-size: 1.22rem; font-weight: 700; color: #fff;
    letter-spacing: 0.3px;
}
.logo-subtitle { font-size: 0.62rem; color: var(--gold); opacity: 0.8; }

/* ── Desktop nav links ───────────────────────────────────────── */
.desktop-nav .nav-links {
    display: flex; list-style: none;
    margin: 0; padding: 0; gap: 26px; align-items: center;
}
.desktop-nav .nav-links > li > a {
    position: relative;
    display: flex; align-items: center; gap: 5px;
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.92rem; font-weight: 500;
    padding: 6px 2px;
    transition: color 0.22s;
}
/* Animated underline */
.desktop-nav .nav-links > li > a::after {
    content: '';
    position: absolute;
    bottom: -2px; left: 0;
    width: 0; height: 2px;
    background: var(--gold);
    border-radius: 2px;
    transition: width 0.28s ease;
}
.desktop-nav .nav-links > li > a:hover { color: var(--gold); }
.desktop-nav .nav-links > li > a:hover::after { width: 100%; }

/* Chevron icon — rotates when dropdown is open */
.desktop-nav .nav-links i.fa-chevron-down {
    font-size: 0.62rem;
    opacity: 0.55;
    transition: transform 0.28s ease, opacity 0.22s;
}
.has-dropdown.dropdown-active > a i.fa-chevron-down {
    transform: rotate(180deg);
    opacity: 1;
}

/* Active trigger style */
.has-dropdown.dropdown-active > a {
    color: var(--gold);
}
.has-dropdown.dropdown-active > a::after { width: 100%; }

.premium-nav-link { color: var(--gold) !important; }

/* ── Dropdown panel ──────────────────────────────────────────── */
.has-dropdown { position: relative; }

.dropdown {
    position: absolute;
    top: calc(100% + 12px);
    left: 50%;
    transform: translateX(-50%) translateY(-10px) scale(0.97);
    min-width: 210px;
    list-style: none; padding: 8px; margin: 0;
    background: linear-gradient(155deg, #111c32 0%, #060d1e 100%);
    border: 1px solid rgba(212,175,55,0.22);
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.03);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    /* no transition here — animation handles open, instant on close */
    z-index: 1100;
}

/* Right-aligned for user avatar */
.user-dropdown {
    left: auto; right: 0;
    transform: translateY(-10px) scale(0.97);
}

/* ── Open state (JS adds dropdown-active) ────────────────────── */
.has-dropdown.dropdown-active > .dropdown {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    animation: dropdownOpen var(--drop-duration) cubic-bezier(0.16,1,0.3,1) forwards;
}
.user-menu.dropdown-active > .dropdown {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    animation: dropdownOpenRight var(--drop-duration) cubic-bezier(0.16,1,0.3,1) forwards;
}

/* ── Staggered dropdown items ────────────────────────────────── */
.has-dropdown.dropdown-active > .dropdown > li {
    opacity: 0;
    animation: dropItemSlide 0.22s ease forwards;
}
.has-dropdown.dropdown-active > .dropdown > li:nth-child(1) { animation-delay: 0.06s; }
.has-dropdown.dropdown-active > .dropdown > li:nth-child(2) { animation-delay: 0.10s; }
.has-dropdown.dropdown-active > .dropdown > li:nth-child(3) { animation-delay: 0.14s; }
.has-dropdown.dropdown-active > .dropdown > li:nth-child(4) { animation-delay: 0.18s; }
.has-dropdown.dropdown-active > .dropdown > li:nth-child(5) { animation-delay: 0.22s; }

/* ── Dropdown item links ─────────────────────────────────────── */
.dropdown > li > a {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 13px;
    color: var(--text-light);
    font-size: 0.87rem;
    border-radius: 8px;
    text-decoration: none; white-space: nowrap;
    transition: background 0.18s, color 0.18s, padding-left 0.18s;
}
.dropdown > li > a i {
    width: 14px; text-align: center;
    color: var(--gold); opacity: 0.75;
    transition: opacity 0.18s;
}
.dropdown > li > a:hover {
    background: rgba(212,175,55,0.1);
    color: var(--gold);
    padding-left: 17px;
}
.dropdown > li > a:hover i { opacity: 1; }
.logout-link { color: #f87171 !important; }
.logout-link i { color: #f87171 !important; }
.logout-link:hover { background: rgba(248,113,113,0.1) !important; color: #f87171 !important; }

/* ── Divider inside user dropdown ────────────────────────────── */
.dropdown-divider {
    height: 1px;
    background: rgba(255,255,255,0.06);
    margin: 5px 8px;
}

/* ── Header actions ──────────────────────────────────────────── */
.header-actions { display: flex; align-items: center; gap: 12px; }

/* Language button */
.lang-btn {
    display: flex; align-items: center; justify-content: center; gap: 3px;
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(212,175,55,0.3);
    color: var(--text-light);
    font-size: 0.9rem; cursor: pointer;
    transition: border-color 0.22s, color 0.22s, background 0.22s, transform 0.22s;
}
.lang-btn:hover {
    border-color: var(--gold); color: var(--gold);
    background: rgba(212,175,55,0.08);
    transform: rotate(-10deg) scale(1.08);
}
.lang-btn span { font-size: 0.6rem; font-weight: 800; letter-spacing: 0.5px; }

/* Log In button — shine sweep */
.btn-login-new {
    display: inline-block;
    background: linear-gradient(100deg, #c8971f 0%, #facc15 40%, #fffbe6 50%, #facc15 60%, #c8971f 100%);
    background-size: 250% auto;
    color: #020617 !important;
    font-weight: 700; font-size: 0.88rem;
    padding: 9px 22px; border-radius: 30px;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(212,175,55,0.4);
    animation: btnShine 3.5s linear infinite;
    transition: transform 0.2s, box-shadow 0.2s;
    white-space: nowrap;
}
.btn-login-new:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(212,175,55,0.65);
}

/* User avatar */
.user-menu { position: relative; }
.user-avatar {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #d4af37, #facc15);
    color: #020617;
    font-weight: 700; font-size: 1rem;
    text-decoration: none; cursor: pointer;
    border: 2px solid rgba(212,175,55,0.5);
    transition: transform 0.22s, box-shadow 0.22s;
    user-select: none;
}
.user-avatar:hover {
    transform: scale(1.1);
    box-shadow: 0 0 16px rgba(212,175,55,0.6);
}
/* Pulse ring when dropdown is open */
.user-menu.dropdown-active .user-avatar {
    animation: pulse 1.4s ease infinite;
    box-shadow: 0 0 0 3px rgba(212,175,55,0.3);
}

/* ── MOBILE MENU — MINIMAL CLEAN VERSION ─────────────────────── */
#hambtn {
    display: none;
    flex-direction: column;
    justify-content: space-around;
    width: 32px; height: 26px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px;
    z-index: 100;
}
#hambtn span {
    display: block;
    width: 100%; height: 3px;
    background: var(--gold);
    border-radius: 2px;
}

#mobback {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 9998;
}
#mobback.show { display: block; }

#mobnav {
    display: none;
    position: fixed;
    top: 0; right: 0;
    width: 280px;
    max-width: 85vw;
    height: 100vh;
    background: #0a0a0a;
    border-left: 3px solid var(--gold);
    z-index: 9999;
    padding: 60px 20px 20px;
    overflow-y: auto;
}
#mobnav.show { display: block; }

#closebtn {
    position: absolute;
    top: 15px; right: 15px;
    width: 40px; height: 40px;
    background: transparent;
    color: var(--gold);
    border: 2px solid var(--gold);
    border-radius: 50%;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
}

#mobnav ul {
    list-style: none;
    padding: 0;
    margin: 20px 0 0;
}
#mobnav ul li { margin: 6px 0; }
#mobnav ul li a {
    display: block;
    color: #e2e8f0;
    padding: 13px 16px;
    text-decoration: none;
    font-size: 1rem;
    border-radius: 8px;
}
#mobnav ul li a:hover,
#mobnav ul li a:active {
    background: rgba(212,175,55,0.15);
    color: var(--gold);
}
#mobnav .mobhead {
    color: var(--gold);
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    padding: 16px 16px 4px;
    opacity: 0.65;
}
#mobnav .install-nav-link { color: var(--gold); }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .desktop-nav,
    .header-actions   { display: none !important; }
    #hambtn { display: flex !important; }
    .main-bar { padding: 0 16px !important; }
    .header-logo img { height: 40px; width: 40px; }
    .logo-subtitle   { display: none !important; }
}

/* ── Install App nav link ─────────────────────────────────── */
.desktop-nav .nav-links > li > a.install-nav-link {
    color: var(--gold);
    border: 1px solid rgba(212,175,55,0.32);
    border-radius: 20px;
    padding: 5px 14px !important;
    background: rgba(212,175,55,0.07);
    gap: 7px;
}
.desktop-nav .nav-links > li > a.install-nav-link::after {
    display: none;
}
.desktop-nav .nav-links > li > a.install-nav-link:hover {
    color: var(--gold) !important;
    background: rgba(212,175,55,0.16);
    border-color: rgba(212,175,55,0.55);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(212,175,55,0.2);
}
</style>

</head>

<body>

<div id="fb-root"></div>
<script async defer crossorigin="anonymous"
    src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v19.0">
</script>

<header id="main-header">

    <!-- TOP MARKET BAR -->
    <div class="top-bar">
        <div class="ticker-label">
            <span class="ticker-live-dot"></span>
            <span class="ticker-label-text">Markets</span>
        </div>
        <div class="ticker-widget-wrap">
            <div class="tradingview-widget-container" style="height:100%;">
                <div class="tradingview-widget-container__widget"></div>
                <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js" async>
                {
                    "symbols": [
                        { "proName": "FOREXCOM:SPXUSD", "title": "S&P 500" },
                        { "proName": "FOREXCOM:NSXUSD", "title": "US 100" },
                        { "proName": "NASDAQ:AAPL",     "title": "Apple" },
                        { "proName": "NASDAQ:TSLA",     "title": "Tesla" },
                        { "proName": "NASDAQ:NVDA",     "title": "Nvidia" },
                        { "proName": "NASDAQ:AMZN",     "title": "Amazon" },
                        { "proName": "NASDAQ:MSFT",     "title": "Microsoft" },
                        { "proName": "BINANCE:BTCUSDT", "title": "Bitcoin" },
                        { "proName": "BINANCE:ETHUSDT", "title": "Ethereum" },
                        { "proName": "TVC:GOLD",        "title": "Gold" },
                        { "proName": "TVC:DXY",         "title": "USD Index" }
                    ],
                    "showSymbolLogo": true,
                    "isTransparent": true,
                    "displayMode": "adaptive",
                    "colorTheme": "dark",
                    "locale": "en"
                }
                </script>
            </div>
        </div>
    </div>

    <!-- MAIN NAV BAR -->
    <div class="main-bar">

        <!-- Logo -->
        <a href="index.php" class="header-logo">
            <img src="<?php echo $root; ?>/images/logo.jpg" alt="Wise Invest Logo">
            <div class="logo-text">
                <span class="logo-title">Wise Invest</span>
                <span class="logo-subtitle"><?php echo t('logo_subtitle'); ?></span>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <nav class="desktop-nav">
            <ul class="nav-links">

                <li><a href="index.php"><?php echo t('nav_home'); ?></a></li>

                <li class="has-dropdown">
                    <a href="#"><?php echo t('nav_learning'); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown">
                        <li><a href="articles.php"><i class="fas fa-newspaper"></i> <?php echo t('nav_articles'); ?></a></li>
                        <li><a href="classes.php"><i class="fas fa-graduation-cap"></i> <?php echo t('nav_classes'); ?></a></li>
                        <li><a href="videos.php"><i class="fas fa-play-circle"></i> <?php echo t('nav_videos'); ?></a></li>
                        <li><a href="calculators.php"><i class="fas fa-calculator"></i> <?php echo t('nav_calculators'); ?></a></li>
                    </ul>
                </li>

                <li><a href="community.php"><?php echo t('nav_community'); ?></a></li>

                <?php if ($_is_any_premium): ?>
                <li class="has-dropdown">
                    <a href="#" class="premium-nav-link">
                        <i class="fas fa-crown"></i> <?php echo t('nav_premium'); ?> <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown">
                        <li><a href="glossary.php"><i class="fas fa-book"></i> <?php echo t('nav_glossary'); ?></a></li>
                        <li><a href="premium-calculator.php"><i class="fas fa-calculator"></i> <?php echo t('nav_calculator'); ?></a></li>
                        <li><a href="premium-videos.php"><i class="fas fa-film"></i> <?php echo t('nav_premium_videos'); ?></a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <li><a href="services.php"><?php echo t('nav_services'); ?></a></li>
                <li><a href="about.php"><?php echo t('nav_about'); ?></a></li>
                <li><a href="contact.php"><?php echo t('nav_contact'); ?></a></li>

                <?php if (basename($_SERVER['PHP_SELF']) !== 'install.php'): ?>
                <li>
                    <a href="/install.php?source=header" class="install-nav-link">
                        <i class="fas fa-mobile-alt"></i>
                        <?php echo (($_SESSION['language'] ?? 'en') === 'mm') ? 'App Install လုပ်ပါ' : 'Install App'; ?>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>

        <!-- Header Actions -->
        <div class="header-actions">

            <!-- Language switcher -->
            <button id="lang-switcher" class="lang-btn" title="Switch Language">
                <i class="fas fa-globe"></i>
                <span>EN</span>
            </button>

            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- User avatar + dropdown -->
                <div class="user-menu has-dropdown">
                    <a href="#" class="user-avatar" title="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                        <?php echo htmlspecialchars(strtoupper(substr($_SESSION['user_name'], 0, 1))); ?>
                    </a>
                    <ul class="dropdown user-dropdown">
                        <li>
                            <a href="dashboard.php" style="font-weight:600;">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> <?php echo t('nav_dashboard'); ?></a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php"><i class="fas fa-user-shield"></i> <?php echo t('nav_admin'); ?></a></li>
                        <?php endif; ?>
                        <li class="dropdown-divider"></li>
                        <li><a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> <?php echo t('nav_logout'); ?></a></li>
                    </ul>
                </div>

            <?php else: ?>

                <a href="login.php" class="btn-login-new"><?php echo t('nav_login'); ?></a>

            <?php endif; ?>

        </div>

        <!-- Mobile hamburger -->
        <button id="hambtn" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>

    </div>

</header>

<!-- Mobile Menu System -->
<div id="mobback"></div>

<nav id="mobnav">
    <button id="closebtn" aria-label="Close">&times;</button>
    <ul>
        <li><a href="index.php"><?php echo t('nav_home'); ?></a></li>

        <li class="mobhead"><?php echo t('nav_learning'); ?></li>
        <li><a href="articles.php"><?php echo t('nav_articles'); ?></a></li>
        <li><a href="classes.php"><?php echo t('nav_classes'); ?></a></li>
        <li><a href="videos.php"><?php echo t('nav_videos'); ?></a></li>
        <li><a href="calculators.php"><?php echo t('nav_calculators'); ?></a></li>

        <li><a href="community.php"><?php echo t('nav_community'); ?></a></li>

        <?php if ($_is_any_premium): ?>
        <li class="mobhead"><i class="fas fa-crown"></i> <?php echo t('nav_premium'); ?></li>
        <li><a href="glossary.php"><?php echo t('nav_glossary'); ?></a></li>
        <li><a href="premium-calculator.php"><?php echo t('nav_calculator'); ?></a></li>
        <li><a href="premium-videos.php"><?php echo t('nav_premium_videos'); ?></a></li>
        <?php endif; ?>

        <li class="mobhead"><?php echo t('nav_more'); ?></li>
        <li><a href="services.php"><?php echo t('nav_services'); ?></a></li>
        <li><a href="about.php"><?php echo t('nav_about'); ?></a></li>
        <li><a href="contact.php"><?php echo t('nav_contact'); ?></a></li>
        <?php if (basename($_SERVER['PHP_SELF']) !== 'install.php'): ?>
        <li><a href="/install.php?source=mobile_menu" class="install-nav-link">
            <i class="fas fa-mobile-alt"></i>
            <?php echo (($_SESSION['language'] ?? 'en') === 'mm') ? 'App Install လုပ်ပါ' : 'Install App'; ?>
        </a></li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <li class="mobhead"><?php echo t('nav_account'); ?></li>
        <li><a href="dashboard.php"><?php echo t('nav_dashboard'); ?></a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li><a href="admin.php"><?php echo t('nav_admin'); ?></a></li>
        <?php endif; ?>
        <li><a href="logout.php" style="color:#f87171;"><?php echo t('nav_logout'); ?></a></li>
        <?php else: ?>
        <li><a href="login.php"><?php echo t('nav_login'); ?></a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- ============================================================
     HEADER JAVASCRIPT
     ============================================================ -->
<script>
(function () {
    'use strict';

    /* ── Language visuals (runs immediately, before DOMContentLoaded) ── */
    var lang = document.documentElement.lang || 'en';

    document.querySelectorAll('.lang-en').forEach(function (el) {
        el.style.display = (lang === 'mm') ? 'none' : '';
    });
    document.querySelectorAll('.lang-mm').forEach(function (el) {
        el.style.display = (lang === 'mm') ? '' : 'none';
    });
    var langSpan = document.querySelector('#lang-switcher span');
    if (langSpan) langSpan.textContent = lang.toUpperCase();

    /* ── Everything else waits for DOM ── */
    document.addEventListener('DOMContentLoaded', function () {

        /* 1 ── Header scroll effect */
        var header = document.getElementById('main-header');
        if (header) {
            window.addEventListener('scroll', function () {
                header.classList.toggle('scrolled', window.scrollY > 50);
            }, { passive: true });
        }

        /* 2 ── Mobile menu (minimal clean version) */
        var hambtn   = document.getElementById('hambtn');
        var mobnav   = document.getElementById('mobnav');
        var mobback  = document.getElementById('mobback');
        var closebtn = document.getElementById('closebtn');

        function openMenu() {
            mobnav.classList.add('show');
            mobback.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            mobnav.classList.remove('show');
            mobback.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (hambtn)   hambtn.addEventListener('click', openMenu);
        if (closebtn) closebtn.addEventListener('click', closeMenu);
        if (mobback)  mobback.addEventListener('click', closeMenu);
        if (mobnav) {
            mobnav.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', closeMenu);
            });
        }

        /* 3 ── Language switcher */
        var langBtn = document.getElementById('lang-switcher');
        if (langBtn) {
            langBtn.addEventListener('click', function () {
                var newLang = (lang === 'en') ? 'mm' : 'en';
                var path = window.location.pathname + window.location.search;
                /* strip existing lang param */
                path = path.replace(/([?&])lang=[^&]*/g, function (m, sep) {
                    return sep === '?' ? '?' : '';
                }).replace(/\?$/, '');
                var sep = path.indexOf('?') !== -1 ? '&' : '?';
                window.location.href = path + sep + 'lang=' + newLang;
            });
        }

        /* 4 ── Desktop click-only dropdowns */
        function closeAllDropdowns() {
            document.querySelectorAll('.has-dropdown.dropdown-active').forEach(function (d) {
                d.classList.remove('dropdown-active');
            });
        }

        document.querySelectorAll('.has-dropdown').forEach(function (item) {
            var trigger = item.querySelector(':scope > a');
            if (!trigger) return;

            trigger.addEventListener('click', function (e) {
                if (window.innerWidth <= 1024) return; /* mobile uses drawer */
                e.preventDefault();

                var isOpen = item.classList.contains('dropdown-active');
                closeAllDropdowns();
                if (!isOpen) item.classList.add('dropdown-active');
                e.stopPropagation();
            });
        });

        /* Close when clicking anywhere outside a dropdown */
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.has-dropdown')) {
                closeAllDropdowns();
            }
        });

        /* Close on Escape key */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAllDropdowns();
        });

    });

}());
</script>

<main>
