<?php
include 'includes/header.php';
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Always read membership from DB — session can be stale after admin upgrades the user
$user_type = 'free';
if (!empty($_SESSION['user_id'])) {
    $uid_q = intval($_SESSION['user_id']);
    $uid_r = $conn->query("SELECT membership_type FROM users WHERE id=$uid_q");
    if ($uid_r && $uid_r->num_rows > 0) {
        $user_type = $uid_r->fetch_row()[0] ?? 'free';
        // Keep session in sync
        $_SESSION['user_type'] = $user_type;
    }
}

// Helper: can this user watch a specific batch's videos?
function user_can_access_batch($user_type, $video_batch) {
    $all_paid = ['premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond'];
    if (!in_array($user_type, $all_paid)) return false;  // free user — no access
    if ($user_type === 'diamond' || $user_type === 'premium') return true; // full access
    // Batch-specific: unlabelled videos are open to all paid tiers
    if ($video_batch === null || $video_batch === '') return true;
    return ($user_type === $video_batch . '_premium');   // exact batch match
}

$is_paid = in_array($user_type, ['premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond']);

// Category filter
$cat  = isset($_GET['cat'])  ? (int)$_GET['cat']  : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$per_page = 9;

// WHERE clause
$where = "WHERE pc.video_type='premium'";
if ($cat > 0) $where .= " AND pc.folder_id=$cat";

// Count
$count       = $conn->query("SELECT COUNT(*) total FROM premium_content pc $where")->fetch_assoc()['total'];
$total_pages = max(1, ceil($count / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// Fetch with folder name
$videos = $conn->query("
    SELECT pc.*, vf.name AS folder_name
    FROM premium_content pc
    LEFT JOIN video_folders vf ON pc.folder_id = vf.id
    $where
    ORDER BY pc.folder_id, pc.id DESC
    LIMIT $per_page OFFSET $offset
");

// Categories
$cats_q = $conn->query("SELECT * FROM video_folders ORDER BY name ASC");
$cats = [];
if ($cats_q) while ($c = $cats_q->fetch_assoc()) $cats[] = $c;

$total_all = (int)$conn->query("SELECT COUNT(*) FROM premium_content WHERE video_type='premium'")->fetch_row()[0];
$cat_count = count($cats);
?>

<!-- ══════════════════════════════════════════════════════
     PREMIUM VIDEO VAULT — REDESIGNED
══════════════════════════════════════════════════════ -->

<style>
/* ── Base ── */
.pv-wrap { background:#020617; min-height:100vh; }

/* ════════════════════════ KEYFRAMES ════════════════════════ */
@keyframes pvFadeUp   { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:none} }
@keyframes pvShimmer  { 0%{background-position:-200% center} 100%{background-position:200% center} }
@keyframes pvOrb1     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.15) translate(24px,-20px)} }
@keyframes pvOrb2     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.1) translate(-18px,22px)} }
@keyframes pvOrb3     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.08) translate(14px,16px)} }
@keyframes pvGridFade { 0%,100%{opacity:.07} 50%{opacity:.025} }
@keyframes pvRing     { 0%{transform:scale(1);opacity:.7} 100%{transform:scale(2.4);opacity:0} }
@keyframes pvPulse    { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.45)} 70%{box-shadow:0 0 0 16px rgba(212,175,55,0)} }
@keyframes pvLockIn   { from{opacity:0;transform:scale(.85) translateY(10px)} to{opacity:1;transform:none} }
@keyframes pvCardIn   { from{opacity:0;transform:translateY(28px) scale(.97)} to{opacity:1;transform:none} }
@keyframes pvModalIn  { from{opacity:0;transform:scale(.94)} to{opacity:1;transform:scale(1)} }
@keyframes pvOverlayIn{ from{opacity:0} to{opacity:1} }
@keyframes pvDotBlink { 0%,100%{opacity:1} 50%{opacity:.3} }

/* ════════════════════════ HERO ════════════════════════ */
.pv-hero {
    position:relative; padding:100px 20px 80px;
    text-align:center; overflow:hidden;
}
.pv-hero-bg { position:absolute; inset:0; pointer-events:none; }
.pv-grid-lines {
    position:absolute; inset:0;
    background-image:
        linear-gradient(rgba(212,175,55,.05) 1px,transparent 1px),
        linear-gradient(90deg,rgba(212,175,55,.05) 1px,transparent 1px);
    background-size:55px 55px;
    animation:pvGridFade 5s ease-in-out infinite alternate;
}
.pv-orb { position:absolute; border-radius:50%; filter:blur(100px); pointer-events:none; }
.pv-orb-1 { width:700px;height:700px; background:radial-gradient(circle,rgba(212,175,55,.12),transparent 70%); top:-280px;left:-200px; animation:pvOrb1 10s ease-in-out infinite; }
.pv-orb-2 { width:500px;height:500px; background:radial-gradient(circle,rgba(99,102,241,.08),transparent 70%); top:-100px;right:-150px; animation:pvOrb2 12s ease-in-out infinite; }
.pv-orb-3 { width:350px;height:350px; background:radial-gradient(circle,rgba(212,175,55,.07),transparent 70%); bottom:-100px;left:50%; animation:pvOrb3 9s ease-in-out infinite; }

.pv-hero-inner { position:relative; z-index:2; animation:pvFadeUp .8s ease both; }

.pv-badge {
    display:inline-flex; align-items:center; gap:7px;
    padding:7px 18px; border-radius:999px;
    border:1px solid rgba(212,175,55,.35); background:rgba(212,175,55,.08);
    color:#d4af37; font-size:.8rem; font-weight:600;
    letter-spacing:.08em; text-transform:uppercase; margin-bottom:24px;
}

.pv-hero-title {
    font-family:'Playfair Display',serif;
    font-size:clamp(2.2rem,5vw,3.8rem);
    color:#f1f5f9; margin:0 0 16px; line-height:1.15; font-weight:700;
}
.pv-gold {
    background:linear-gradient(135deg,#d4af37 0%,#f5d679 40%,#d4af37 100%);
    background-size:200% auto;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    animation:pvShimmer 4s linear infinite;
}
.pv-hero-sub { color:#94a3b8; font-size:1.05rem; max-width:560px; margin:0 auto 40px; line-height:1.7; }

.pv-stats {
    display:inline-flex; align-items:center;
    background:rgba(255,255,255,.03); border:1px solid rgba(212,175,55,.15);
    border-radius:16px; padding:18px 36px; backdrop-filter:blur(12px);
}
.pv-stat { display:flex; flex-direction:column; align-items:center; gap:4px; padding:0 28px; }
.pv-stat-num { font-size:1.7rem; font-weight:700; color:#d4af37; line-height:1; }
.pv-stat-label { font-size:.72rem; color:#64748b; text-transform:uppercase; letter-spacing:.08em; }
.pv-stat-divider { width:1px; height:36px; background:rgba(212,175,55,.2); }

/* ════════════════════════ SEARCH + FILTER ════════════════════════ */
.pv-controls {
    position:sticky; top:110px; z-index:50;
    background:rgba(2,6,23,.85); backdrop-filter:blur(20px);
    border-bottom:1px solid rgba(212,175,55,.08);
    padding:16px 24px;
}
.pv-controls-inner {
    max-width:1340px; margin:0 auto;
    display:flex; align-items:center; gap:14px; flex-wrap:wrap;
}

/* Search input */
.pv-search-wrap {
    display:flex; align-items:center; gap:10px;
    background:rgba(15,23,42,.8);
    border:1px solid rgba(212,175,55,.14);
    border-radius:12px; padding:8px 16px;
    flex:1; min-width:200px; max-width:340px;
    transition:border-color .25s, box-shadow .25s;
}
.pv-search-wrap:focus-within {
    border-color:rgba(212,175,55,.4);
    box-shadow:0 0 0 3px rgba(212,175,55,.07);
}
.pv-search-wrap i { color:#475569; font-size:.9rem; transition:color .25s; }
.pv-search-wrap:focus-within i { color:#d4af37; }
#pvSearch {
    flex:1; background:none; border:none; outline:none;
    color:#e2e8f0; font-size:.88rem; font-family:inherit;
}
#pvSearch::placeholder { color:#475569; }

/* Category filter pills */
.pv-filter-bar {
    display:flex; gap:8px; flex-wrap:wrap; align-items:center; flex:1;
}
.pv-filter-btn {
    padding:8px 18px; border-radius:999px;
    color:#94a3b8; font-size:.82rem; font-weight:500;
    text-decoration:none; transition:all .22s; white-space:nowrap;
    border:1px solid rgba(255,255,255,.07);
}
.pv-filter-btn:hover { color:#d4af37; border-color:rgba(212,175,55,.28); background:rgba(212,175,55,.06); }
.pv-filter-btn.active {
    background:linear-gradient(135deg,#d4af37,#f5d679);
    color:#0f172a; font-weight:700; border-color:transparent;
    box-shadow:0 4px 18px rgba(212,175,55,.32);
}

/* Result count */
.pv-result-count {
    font-size:.78rem; color:#475569; white-space:nowrap; margin-left:auto;
}
.pv-result-count span { color:#d4af37; font-weight:700; }

/* ════════════════════════ CONTENT ════════════════════════ */
.pv-content { max-width:1340px; margin:0 auto; padding:36px 24px 80px; }

/* Section label */
.pv-section-lbl {
    display:flex; align-items:center; gap:14px; margin-bottom:28px;
}
.pv-section-lbl-text {
    font-size:.68rem; font-weight:700; letter-spacing:2.5px;
    text-transform:uppercase; color:#d4af37; white-space:nowrap;
}
.pv-section-lbl-line {
    flex:1; height:1px;
    background:linear-gradient(90deg,rgba(212,175,55,.25),transparent);
}
.pv-section-lbl-count {
    font-size:.75rem; color:#475569; white-space:nowrap;
}

/* ════════════════════════ GRID ════════════════════════ */
.pv-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(340px,1fr));
    gap:24px;
}

/* ════════════════════════ CARD ════════════════════════ */
.pv-card {
    position:relative; border-radius:20px; overflow:hidden;
    border:1px solid rgba(255,255,255,.06); background:#0c1628;
    box-shadow:0 16px 48px rgba(0,0,0,.45);
    animation:pvCardIn .5s ease both;
    transition:transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s, border-color .35s;
    display:flex; flex-direction:column;
}
.pv-card:not(.pv-locked) { cursor:pointer; }
.pv-card:not(.pv-locked):hover {
    transform:translateY(-8px) scale(1.015);
    box-shadow:0 28px 72px rgba(212,175,55,.22), 0 0 0 1px rgba(212,175,55,.18);
    border-color:rgba(212,175,55,.2);
}

/* ── Thumbnail ── */
.pv-thumb {
    position:relative; aspect-ratio:16/9; overflow:hidden; flex-shrink:0;
}
.pv-thumb img {
    width:100%; height:100%; object-fit:cover; display:block;
    transition:transform .55s ease;
}
.pv-card:not(.pv-locked):hover .pv-thumb img { transform:scale(1.07); }
.pv-thumb iframe { width:100%; height:100%; border:none; display:block; }
.pv-tiktok-placeholder {
    width:100%; height:100%;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    display:flex; align-items:center; justify-content:center;
    color:#d4af37; font-size:2.5rem;
}

/* Gradient overlay on thumb */
.pv-thumb-overlay {
    position:absolute; inset:0;
    background:linear-gradient(to top, rgba(12,22,40,.95) 0%, rgba(12,22,40,.3) 40%, transparent 70%);
    pointer-events:none;
}

/* Top-right badge */
.pv-corner-badge {
    position:absolute; top:11px; right:11px;
    padding:4px 9px; border-radius:7px;
    background:rgba(212,175,55,.12); border:1px solid rgba(212,175,55,.38);
    color:#d4af37; font-size:.62rem; font-weight:700; letter-spacing:.08em;
    backdrop-filter:blur(8px);
}

/* Category badge on thumb (bottom-left) */
.pv-cat-badge {
    position:absolute; bottom:10px; left:10px;
    padding:3px 10px; border-radius:20px;
    background:rgba(0,0,0,.6); border:1px solid rgba(255,255,255,.1);
    color:#94a3b8; font-size:.62rem; font-weight:600;
    backdrop-filter:blur(8px); letter-spacing:.05em;
    white-space:nowrap; max-width:160px;
    overflow:hidden; text-overflow:ellipsis;
}

/* Play button */
.pv-play-ring {
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    opacity:0; transition:opacity .3s;
}
.pv-card:not(.pv-locked):hover .pv-play-ring { opacity:1; }
.pv-play-btn {
    width:60px; height:60px; border-radius:50%;
    background:linear-gradient(135deg,#d4af37,#f5d679);
    display:flex; align-items:center; justify-content:center;
    color:#0f172a; font-size:1.15rem;
    animation:pvPulse 2s infinite; position:relative; z-index:2;
}
.pv-play-btn::before {
    content:''; position:absolute; inset:-4px; border-radius:50%;
    border:2px solid rgba(212,175,55,.5); animation:pvRing 1.6s ease-out infinite;
}

/* ── Card Info Section ── */
.pv-card-info {
    padding:18px 20px 20px; flex:1; display:flex; flex-direction:column; gap:10px;
}

.pv-card-meta {
    display:flex; align-items:center; justify-content:space-between; gap:8px;
}

.pv-card-folder {
    display:inline-flex; align-items:center; gap:5px;
    font-size:.62rem; font-weight:700; letter-spacing:1.5px; text-transform:uppercase;
    color:#d4af37; opacity:.8;
}
.pv-card-folder i { font-size:.58rem; }

.pv-card-type-dot {
    width:6px; height:6px; border-radius:50%; background:#34d399;
    animation:pvDotBlink 2s ease-in-out infinite; flex-shrink:0;
}

/* Title */
.pv-card-title {
    font-size:1rem; font-weight:700; color:#f1f5f9;
    line-height:1.4; margin:0;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
    transition:color .22s;
}
.pv-card:not(.pv-locked):hover .pv-card-title { color:#d4af37; }

/* Description */
.pv-card-desc {
    font-size:.78rem; color:#64748b; line-height:1.6; margin:0;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}

/* Bottom row */
.pv-card-bottom {
    display:flex; align-items:center; justify-content:space-between;
    margin-top:auto; padding-top:10px;
    border-top:1px solid rgba(255,255,255,.04);
}
.pv-watch-hint {
    display:flex; align-items:center; gap:6px;
    font-size:.72rem; color:#475569; font-weight:500;
    transition:color .22s;
}
.pv-watch-hint i { font-size:.68rem; }
.pv-card:not(.pv-locked):hover .pv-watch-hint { color:#d4af37; }

.pv-premium-tag {
    display:inline-flex; align-items:center; gap:4px;
    font-size:.62rem; font-weight:700; letter-spacing:.05em;
    color:#d4af37; background:rgba(212,175,55,.08);
    border:1px solid rgba(212,175,55,.2); border-radius:20px; padding:3px 9px;
}

/* ── Locked card variant ── */
.pv-locked .pv-thumb img { filter:blur(8px) brightness(.5); transform:scale(1.1); }
.pv-locked .pv-card-title { color:#334155; }
.pv-locked .pv-card-desc  { color:#1e293b; }
.pv-locked .pv-watch-hint { color:#1e293b; }

/* ════════════════════════ LOCK OVERLAY ════════════════════════ */
.pv-lock-overlay {
    position:absolute; inset:0;
    background:radial-gradient(ellipse at 50% 35%,rgba(212,175,55,.07),rgba(2,6,23,.92));
    display:flex; align-items:center; justify-content:center;
    z-index:10;
}
.pv-lock-card {
    background:rgba(10,18,35,.88); border:1px solid rgba(212,175,55,.22);
    border-radius:20px; padding:28px 24px; max-width:230px;
    text-align:center; backdrop-filter:blur(18px);
    box-shadow:0 20px 60px rgba(0,0,0,.6); animation:pvLockIn .45s ease both;
}
.pv-lock-icon-wrap { position:relative; width:60px; height:60px; margin:0 auto 14px; }
.pv-lock-ring { position:absolute; inset:0; border-radius:50%; border:2px solid rgba(212,175,55,.3); animation:pvRing 2s ease-out infinite; }
.pv-lock-icon { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:1.5rem; width:60px; height:60px; color:#d4af37; background:rgba(212,175,55,.1); border-radius:50%; }
.pv-lock-card h3 { color:#f1f5f9; font-size:.95rem; font-weight:700; margin:0 0 7px; }
.pv-lock-card p  { color:#64748b; font-size:.75rem; line-height:1.55; margin:0 0 16px; }
.pv-unlock-btn {
    display:inline-flex; align-items:center; gap:7px; padding:10px 20px;
    border-radius:999px; background:linear-gradient(135deg,#d4af37,#f5d679);
    color:#0f172a; font-weight:700; font-size:.8rem; text-decoration:none;
    transition:all .25s; box-shadow:0 6px 20px rgba(212,175,55,.35);
    width:100%; justify-content:center;
}
.pv-unlock-btn:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(212,175,55,.5); }
.pv-lock-features { display:flex; flex-direction:column; gap:4px; margin-top:12px; }
.pv-lock-features span { font-size:.67rem; color:#334155; }
.pv-lock-features i { color:#d4af37; margin-right:4px; font-size:.62rem; }

/* ════════════════════════ MODAL PLAYER ════════════════════════ */
.pv-modal-overlay {
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(2,6,23,.96); backdrop-filter:blur(20px);
    align-items:center; justify-content:center; padding:20px;
}
.pv-modal-overlay.open {
    display:flex; animation:pvOverlayIn .25s ease;
}
.pv-modal-box {
    width:100%; max-width:920px; position:relative;
    animation:pvModalIn .3s cubic-bezier(.22,1,.36,1) both;
}
.pv-modal-close {
    position:absolute; top:-48px; right:0;
    width:38px; height:38px; border-radius:50%;
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1);
    color:#e2e8f0; font-size:1rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .22s;
}
.pv-modal-close:hover { background:rgba(212,175,55,.15); border-color:rgba(212,175,55,.4); color:#d4af37; }
.pv-modal-title {
    font-size:1rem; font-weight:700; color:#f1f5f9;
    margin-bottom:12px; padding-right:50px; line-height:1.4;
}
.pv-modal-player {
    position:relative; aspect-ratio:16/9; border-radius:14px; overflow:hidden;
    background:#000; border:1px solid rgba(212,175,55,.12);
    box-shadow:0 30px 80px rgba(0,0,0,.7);
}
.pv-modal-player iframe {
    width:100%; height:100%; border:none; display:block;
}
.pv-modal-meta {
    margin-top:14px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;
}
.pv-modal-cat {
    font-size:.68rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;
    color:#d4af37; background:rgba(212,175,55,.1); border:1px solid rgba(212,175,55,.2);
    padding:3px 10px; border-radius:20px;
}
.pv-modal-desc {
    font-size:.83rem; color:#64748b; line-height:1.65; margin-top:4px;
}

/* ════════════════════════ EMPTY ════════════════════════ */
.pv-empty { text-align:center; padding:80px 20px; }
.pv-empty i { font-size:3rem; margin-bottom:16px; display:block; color:#1e293b; }
.pv-empty p { font-size:1rem; color:#475569; }

/* ════════════════════════ PAGINATION ════════════════════════ */
.pv-pagination {
    display:flex; justify-content:center; align-items:center;
    gap:8px; margin-top:52px; flex-wrap:wrap;
}
.pv-page-btn {
    min-width:40px; height:40px; border-radius:10px;
    border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.03);
    color:#64748b; font-size:.85rem; font-weight:600;
    text-decoration:none; display:flex; align-items:center;
    justify-content:center; padding:0 12px; transition:all .22s;
}
.pv-page-btn:hover { border-color:rgba(212,175,55,.3); color:#d4af37; background:rgba(212,175,55,.06); }
.pv-page-btn.active { background:linear-gradient(135deg,#d4af37,#f5d679); color:#0f172a; border-color:transparent; box-shadow:0 4px 18px rgba(212,175,55,.4); font-weight:700; }
.pv-page-nav { background:rgba(212,175,55,.05); border-color:rgba(212,175,55,.15); color:#d4af37; }

/* ════════════════════════ RESPONSIVE ════════════════════════ */
@media (max-width:768px) {
    .pv-hero { padding:70px 16px 60px; }
    .pv-stats { padding:14px 20px; }
    .pv-stat { padding:0 16px; }
    .pv-stat-num { font-size:1.3rem; }
    .pv-grid { grid-template-columns:1fr; gap:18px; }
    .pv-controls { position:static; }
    .pv-controls-inner { flex-direction:column; align-items:stretch; }
    .pv-search-wrap { max-width:100%; }
    .pv-result-count { margin-left:0; }
    .pv-content { padding:24px 16px 60px; }
    .pv-modal-box { max-width:100%; }
}
@media (max-width:480px) {
    .pv-stats { flex-direction:column; gap:14px; border-radius:16px; }
    .pv-stat-divider { width:70px; height:1px; }
}
</style>

<div class="pv-wrap">

<!-- ── HERO ── -->
<section class="pv-hero">
    <div class="pv-hero-bg">
        <div class="pv-orb pv-orb-1"></div>
        <div class="pv-orb pv-orb-2"></div>
        <div class="pv-orb pv-orb-3"></div>
        <div class="pv-grid-lines"></div>
    </div>
    <div class="pv-hero-inner">
        <div class="pv-badge"><i class="fas fa-crown"></i><span>Exclusive Library</span></div>
        <h1 class="pv-hero-title">Premium <span class="pv-gold">Video</span> Vault</h1>
        <p class="pv-hero-sub">Advanced strategies, institutional-grade insights &amp; deep market analysis</p>
        <div class="pv-stats">
            <div class="pv-stat">
                <span class="pv-stat-num"><?= $total_all ?>+</span>
                <span class="pv-stat-label">Lessons</span>
            </div>
            <div class="pv-stat-divider"></div>
            <div class="pv-stat">
                <span class="pv-stat-num"><?= $cat_count ?></span>
                <span class="pv-stat-label">Topics</span>
            </div>
            <div class="pv-stat-divider"></div>
            <div class="pv-stat">
                <span class="pv-stat-num">HD</span>
                <span class="pv-stat-label">Quality</span>
            </div>
        </div>
    </div>
</section>

<!-- ── CONTROLS (search + filter) ── -->
<div class="pv-controls">
    <div class="pv-controls-inner">

        <!-- Search -->
        <div class="pv-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="pvSearch" placeholder="Search videos by title...">
        </div>

        <!-- Category pills -->
        <div class="pv-filter-bar">
            <a href="premium-videos.php" class="pv-filter-btn <?= $cat==0?'active':'' ?>">
                <i class="fas fa-th-large" style="margin-right:4px;font-size:.75rem"></i> All
            </a>
            <?php foreach($cats as $c): ?>
            <a href="?cat=<?= $c['id'] ?>" class="pv-filter-btn <?= $cat==$c['id']?'active':'' ?>">
                <?= htmlspecialchars($c['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="pv-result-count" id="pvCount">
            Showing <span id="pvCountNum"><?= $count ?></span> video<?= $count!=1?'s':'' ?>
        </div>
    </div>
</div>

<!-- ── CONTENT ── -->
<div class="pv-content">

    <?php if ($videos && $videos->num_rows > 0): ?>

    <div class="pv-section-lbl">
        <span class="pv-section-lbl-text">
            <?= $cat > 0 ? htmlspecialchars($cats[array_search($cat, array_column($cats,'id'))]['name'] ?? 'Videos') : 'All Videos' ?>
        </span>
        <div class="pv-section-lbl-line"></div>
        <span class="pv-section-lbl-count">Page <?= $page ?> of <?= $total_pages ?></span>
    </div>

    <div class="pv-grid" id="pvGrid">
    <?php
    $idx = 0;
    while ($v = $videos->fetch_assoc()):
        $admin_locked   = ((int)$v['is_locked'] === 1);
        $video_batch    = $v['batch'] ?? null;
        $has_batch      = user_can_access_batch($user_type, $video_batch);
        $required_batch = strtoupper($video_batch ?? '');

        if (!$has_batch && !$is_paid) {
            $locked      = true;
            $lock_reason = 'not_premium';
        } elseif (!$has_batch) {
            $locked      = true;
            $lock_reason = 'wrong_batch';
        } elseif ($admin_locked) {
            $locked      = true;
            $lock_reason = 'admin_locked';
        } else {
            $locked      = false;
            $lock_reason = '';
        }

        $url       = $v['video_url'];
        $title     = !empty($v['title'])       ? $v['title']       : 'Premium Video';
        $desc      = !empty($v['description']) ? $v['description'] : '';
        $folder    = !empty($v['folder_name']) ? $v['folder_name'] : 'General';
        $yt        = '';

        if (preg_match('~(youtu\.be/|v=)([^&?/\s]+)~', $url, $m)) {
            $yt    = $m[2];
            $thumb = "https://img.youtube.com/vi/$yt/hqdefault.jpg";
        } else {
            $thumb = '';
        }
        $delay = $idx * 55;
        $idx++;

        // Encode for JS
        $jsTitle  = addslashes(htmlspecialchars($title, ENT_QUOTES));
        $jsDesc   = addslashes(htmlspecialchars($desc, ENT_QUOTES));
        $jsFolder = addslashes(htmlspecialchars($folder, ENT_QUOTES));
    ?>

    <div class="pv-card <?= $locked?'pv-locked':'' ?>"
         style="animation-delay:<?= $delay ?>ms"
         data-title="<?= strtolower(htmlspecialchars($title)) ?>"
         <?php if (!$locked && $yt): ?>
         onclick="openModal('<?= $yt ?>', '<?= $jsTitle ?>', '<?= $jsDesc ?>', '<?= $jsFolder ?>')"
         <?php elseif (!$locked && !$yt): ?>
         onclick="playTikTok(this, '<?= htmlspecialchars($url, ENT_QUOTES) ?>')"
         <?php endif; ?>>

        <!-- Thumbnail -->
        <div class="pv-thumb">
            <?php if ($yt): ?>
                <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy">
            <?php else: ?>
                <div class="pv-tiktok-placeholder"><i class="fab fa-tiktok"></i></div>
            <?php endif; ?>

            <div class="pv-thumb-overlay"></div>

            <!-- Top-right: PREMIUM badge -->
            <div class="pv-corner-badge"><i class="fas fa-star"></i> PREMIUM</div>

            <!-- Bottom-left: category -->
            <?php if (!$locked): ?>
            <div class="pv-cat-badge"><i class="fas fa-folder" style="margin-right:4px;font-size:.55rem"></i><?= htmlspecialchars($folder) ?></div>
            <?php endif; ?>

            <!-- Play button (non-locked) -->
            <?php if (!$locked): ?>
            <div class="pv-play-ring">
                <div class="pv-play-btn"><i class="fas fa-play"></i></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Lock overlay -->
        <?php if ($locked): ?>
        <div class="pv-lock-overlay">
            <div class="pv-lock-card">
                <div class="pv-lock-icon-wrap">
                    <div class="pv-lock-ring"></div>
                    <i class="fas fa-<?= $lock_reason === 'wrong_batch' ? 'gem' : 'lock' ?> pv-lock-icon"></i>
                </div>

                <?php if ($lock_reason === 'wrong_batch'): ?>
                    <h3>SIM Batch-<?= htmlspecialchars($required_batch) ?> Content</h3>
                    <p>This video is only available for SIM Batch-<?= htmlspecialchars($required_batch) ?> members. Please upgrade to <strong>Diamond</strong> to access all batches.</p>
                    <a href="services.php" class="pv-unlock-btn"><i class="fas fa-gem"></i> Upgrade to Diamond</a>
                    <div class="pv-lock-features">
                        <span><i class="fas fa-check"></i> Diamond = All SIM Batches</span>
                        <span><i class="fas fa-check"></i> Full lifetime access</span>
                    </div>

                <?php elseif ($lock_reason === 'not_premium'): ?>
                    <h3>Members Only</h3>
                    <p>Upgrade to a premium plan to unlock this content</p>
                    <a href="services.php" class="pv-unlock-btn"><i class="fas fa-crown"></i> Upgrade Now</a>
                    <div class="pv-lock-features">
                        <span><i class="fas fa-check"></i> Batch or Diamond plans</span>
                        <span><i class="fas fa-check"></i> Lifetime access</span>
                    </div>

                <?php else: ?>
                    <h3>Coming Soon</h3>
                    <p>This video will be available shortly — stay tuned.</p>
                    <div class="pv-lock-features">
                        <span><i class="fas fa-check"></i> Check back soon</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card info (title + description) -->
        <div class="pv-card-info">
            <div class="pv-card-meta">
                <div class="pv-card-folder">
                    <i class="fas fa-layer-group"></i>
                    <?= htmlspecialchars($folder) ?>
                </div>
                <?php if (!$locked): ?>
                <div class="pv-card-type-dot" title="Available"></div>
                <?php endif; ?>
            </div>

            <h3 class="pv-card-title"><?= htmlspecialchars($title) ?></h3>

            <?php if ($desc): ?>
            <p class="pv-card-desc"><?= htmlspecialchars($desc) ?></p>
            <?php endif; ?>

            <div class="pv-card-bottom">
                <?php if (!$locked): ?>
                <div class="pv-watch-hint">
                    <i class="fas fa-play-circle"></i>
                    Watch Now
                </div>
                <div class="pv-premium-tag">
                    <i class="fas fa-crown" style="font-size:.55rem"></i> Premium
                </div>
                <?php else: ?>
                <div class="pv-watch-hint">
                    <i class="fas fa-lock"></i>
                    Locked
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="pv-empty">
        <i class="fas fa-video-slash"></i>
        <p>No premium videos available yet.</p>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pv-pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?><?= $cat?'&cat='.$cat:'' ?>" class="pv-page-btn pv-page-nav">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="?page=<?= $p ?><?= $cat?'&cat='.$cat:'' ?>"
           class="pv-page-btn <?= $p==$page?'active':'' ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?><?= $cat?'&cat='.$cat:'' ?>" class="pv-page-btn pv-page-nav">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- /.pv-content -->
</div><!-- /.pv-wrap -->

<!-- ════════════════════════ MODAL PLAYER ════════════════════════ -->
<div class="pv-modal-overlay" id="pvModal" onclick="closeModal(event)">
    <div class="pv-modal-box">
        <button class="pv-modal-close" onclick="closeModalDirect()">
            <i class="fas fa-times"></i>
        </button>
        <div class="pv-modal-title" id="pvModalTitle"></div>
        <div class="pv-modal-player" id="pvModalPlayer"></div>
        <div class="pv-modal-meta">
            <span class="pv-modal-cat" id="pvModalCat"></span>
        </div>
        <p class="pv-modal-desc" id="pvModalDesc"></p>
    </div>
</div>

<script async src="https://www.tiktok.com/embed.js"></script>

<script>
/* ── Modal player ── */
function openModal(ytId, title, desc, folder) {
    document.getElementById('pvModalTitle').textContent  = title;
    document.getElementById('pvModalDesc').textContent   = desc;
    document.getElementById('pvModalCat').textContent    = folder;
    document.getElementById('pvModalPlayer').innerHTML   =
        '<iframe src="https://www.youtube.com/embed/' + ytId +
        '?autoplay=1&rel=0" allowfullscreen allow="autoplay; encrypted-media"></iframe>';
    document.getElementById('pvModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModalDirect() {
    document.getElementById('pvModal').classList.remove('open');
    document.getElementById('pvModalPlayer').innerHTML = '';
    document.body.style.overflow = '';
}

function closeModal(e) {
    if (e.target === document.getElementById('pvModal')) closeModalDirect();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModalDirect(); });

/* ── TikTok fallback ── */
function playTikTok(card, url) {
    var thumb = card.querySelector('.pv-thumb');
    thumb.innerHTML =
        '<blockquote class="tiktok-embed" cite="' + url +
        '" data-video-id="' + url.split('/').pop() + '">' +
        '<section><a href="' + url + '">Loading…</a></section></blockquote>';
    if (window.tiktok) window.tiktok.reload();
}

/* ── Live client-side title search ── */
const searchInput  = document.getElementById('pvSearch');
const countEl      = document.getElementById('pvCountNum');

searchInput.addEventListener('input', function() {
    const q     = this.value.trim().toLowerCase();
    const cards = document.querySelectorAll('.pv-card');
    let visible = 0;

    cards.forEach(card => {
        const titleAttr = card.dataset.title || '';
        const show = !q || titleAttr.includes(q);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    if (countEl) countEl.textContent = visible;
});
</script>

<?php include 'includes/footer.php'; ?>
