<?php 
include 'includes/db.php';
include 'includes/header.php';

// Auto-create social profile tables
$conn->query("CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT PRIMARY KEY,
    bio TEXT,
    location VARCHAR(100),
    website VARCHAR(200),
    avatar_url VARCHAR(300),
    cover_url VARCHAR(300),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS user_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (follower_id, following_id)
)");

// Add title column to social_posts if not present
$_tc = $conn->query("SHOW COLUMNS FROM social_posts LIKE 'title'");
if ($_tc && $_tc->num_rows === 0)
    $conn->query("ALTER TABLE social_posts ADD COLUMN title VARCHAR(200) NULL DEFAULT NULL AFTER user_id");

// ── Content cleaner: fixes legacy \r\n stored as literal text ──
function cleanContent($text) {
    $text = str_replace(['\\r\\n','\\r','\\n'], ["\n",'',"\n"], $text);
    return stripslashes($text);
}

// SECURITY: Must be logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please log in to join the community.';
    header("Location: login.php");
    exit();
}

// --- HANDLE SUBMISSIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // 1. CREATE POST
    if ($_POST['action'] === 'create_post') {
        $title   = trim($_POST['post_title'] ?? '');
        $content = trim($_POST['content']);
        $uid = $_SESSION['user_id'];
        $video_link = '';
        $image_url = '';

        if (!empty($_POST['video_link'])) {
            $v = $conn->real_escape_string($_POST['video_link']);
            if (strpos($v, 'watch?v=') !== false) {
                $video_link = str_replace('watch?v=', 'embed/', $v);
                $video_link = explode('&', $video_link)[0];
            } elseif (strpos($v, 'youtu.be/') !== false) {
                $video_link = str_replace('youtu.be/', 'www.youtube.com/embed/', $v);
            }
        }

        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            $file_name = time() . '_' . basename($_FILES["post_image"]["name"]);
            $target_file = $target_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $valid_ext = array("jpg", "jpeg", "png", "gif", "webp");
            if(in_array($file_type, $valid_ext)){
                if(move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)){
                    $image_url = $target_file;
                }
            }
        }

        if ($content || $image_url || $video_link) {
            $stmt = $conn->prepare("INSERT INTO social_posts (user_id, title, content, image_url, video_link) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $uid, $title, $content, $image_url, $video_link);
            $stmt->execute();
        }
        echo "<script>window.location.href='community.php';</script>";
        exit();
    }

    // 2. ADD COMMENT
    if ($_POST['action'] === 'add_comment') {
        $pid = intval($_POST['post_id']);
        $uid = $_SESSION['user_id'];
        $cmt = trim($_POST['comment']);
        if ($cmt) {
            $stmt = $conn->prepare("INSERT INTO social_comments (post_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $pid, $uid, $cmt);
            $stmt->execute();
        }
        echo "<script>window.location.href='community.php';</script>";
        exit();
    }

    // 3. HANDLE REACTION (AJAX)
    if ($_POST['action'] === 'react') {
        $pid  = intval($_POST['post_id']);
        $type = trim($_POST['type']);
        $uid  = $_SESSION['user_id'];

        $chk = $conn->prepare("SELECT id FROM post_reactions WHERE post_id=? AND user_id=?");
        $chk->bind_param("ii", $pid, $uid);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $upd = $conn->prepare("UPDATE post_reactions SET reaction_type=? WHERE post_id=? AND user_id=?");
            $upd->bind_param("sii", $type, $pid, $uid);
            $upd->execute();
        } else {
            $ins = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)");
            $ins->bind_param("iis", $pid, $uid, $type);
            $ins->execute();
        }
        echo "success";
        exit();
    }

    // 4. DELETE POST
    if ($_POST['action'] === 'delete_post') {
        $pid = intval($_POST['post_id']);
        $uid = $_SESSION['user_id'];
        $stmt = $conn->prepare("DELETE FROM social_posts WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $pid, $uid);
        $stmt->execute();
        echo "<script>window.location.href='community.php';</script>";
        exit();
    }

    // 5. EDIT POST
    if ($_POST['action'] === 'edit_post') {
        $pid         = intval($_POST['post_id']);
        $uid         = $_SESSION['user_id'];
        $new_content = trim($_POST['content']);
        $new_title   = trim($_POST['post_title'] ?? '');
        $stmt = $conn->prepare("UPDATE social_posts SET title=?, content=? WHERE id=? AND user_id=?");
        $stmt->bind_param("ssii", $new_title, $new_content, $pid, $uid);
        $stmt->execute();
        echo "<script>window.location.href='community.php';</script>";
        exit();
    }

    // 6. FOLLOW USER
    if ($_POST['action'] === 'follow_user') {
        $target = intval($_POST['target_id']);
        $uid    = $_SESSION['user_id'];
        if ($target !== $uid) {
            $stmt = $conn->prepare("INSERT IGNORE INTO user_follows (follower_id, following_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $uid, $target);
            $stmt->execute();
        }
        echo "<script>window.location.href='community.php?tab=people';</script>"; exit();
    }

    // 7. UNFOLLOW USER
    if ($_POST['action'] === 'unfollow_user') {
        $target = intval($_POST['target_id']);
        $uid    = $_SESSION['user_id'];
        $stmt = $conn->prepare("DELETE FROM user_follows WHERE follower_id=? AND following_id=?");
        $stmt->bind_param("ii", $uid, $target);
        $stmt->execute();
        echo "<script>window.location.href='community.php?tab=people';</script>"; exit();
    }
}
?>

<style>
/* =====================================================
   WISE INVEST COMMUNITY — DARK FINTECH THEME (CLEAN)
   Single consolidated stylesheet — no duplicate rules
====================================================== */

/* ---------- RESET & BASE ---------- */
*, *::before, *::after { box-sizing: border-box; }

/* ── Extra keyframes ── */
@keyframes cfSlideDown { from{opacity:0;transform:translateY(-14px)} to{opacity:1;transform:none} }
@keyframes cfPop       { 0%{transform:scale(1)} 40%{transform:scale(1.35)} 100%{transform:scale(1)} }
@keyframes cfGlow      { 0%,100%{box-shadow:0 0 6px rgba(212,175,55,.2)} 50%{box-shadow:0 0 22px rgba(212,175,55,.55)} }
@keyframes cfShimmer   { from{background-position:-300% center} to{background-position:300% center} }
@keyframes cfRing      { 0%{transform:scale(1);opacity:.7} 100%{transform:scale(2.2);opacity:0} }

/* ---------- PAGE WRAPPER ---------- */
.community-wrapper {
    min-height: calc(100vh - 90px);
    padding: 36px 5% 60px;
    position: relative;
    overflow-x: hidden;
    color: #e2e8f0;
    background:
        radial-gradient(ellipse at 12% 0%,  rgba(212,175,55,.08), transparent 45%),
        radial-gradient(ellipse at 88% 10%, rgba(167,139,250,.06),transparent 45%),
        radial-gradient(ellipse at 50% 90%, rgba(56,189,248,.04), transparent 55%),
        linear-gradient(180deg, #020617 0%, #030712 100%);
}

/* Subtle grid overlay */
.community-wrapper::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(56,189,248,0.022) 1px, transparent 1px),
        linear-gradient(90deg, rgba(56,189,248,0.022) 1px, transparent 1px);
    background-size: 64px 64px;
    pointer-events: none;
    z-index: 0;
}
.community-wrapper > * { position: relative; z-index: 1; }

/* ---------- INTRO CARD ---------- */
.community-intro-card {
    max-width: 960px;
    margin: 0 auto 32px;
    background: rgba(15, 23, 42, 0.82);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 32px 36px;
    border: 1px solid rgba(56, 189, 248, 0.18);
    box-shadow:
        0 0 0 1px rgba(56,189,248,0.07),
        0 28px 64px rgba(0,0,0,0.55),
        inset 0 1px 0 rgba(255,255,255,0.04);
    position: relative;
    overflow: hidden;
}
.community-intro-card::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #d4af37, #38bdf8, #22d3ee, #d4af37);
    background-size: 200% 100%;
    animation: shimmer 4s linear infinite;
}
@keyframes shimmer {
    0%   { background-position: 0% 0%; }
    100% { background-position: 200% 0%; }
}
.community-title {
    margin-top: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.75rem;
    background: linear-gradient(135deg, #f1f5f9 0%, #38bdf8 55%, #22d3ee 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.community-intro-text { color: #94a3b8; line-height: 1.8; margin-bottom: 0; }
.disclaimer-box {
    background: rgba(212,175,55,0.07);
    border-left: 4px solid #d4af37;
    padding: 14px 20px;
    border-radius: 8px;
    margin: 20px 0;
    color: #cbd5e1;
    box-shadow: inset 0 0 18px rgba(212,175,55,0.04);
}
.guidelines-title { color: #e2e8f0; margin-bottom: 10px; }
.guidelines-list  { color: #94a3b8; line-height: 1.95; padding-left: 20px; }
.guidelines-list li::marker { color: #38bdf8; }

/* ---------- MARKET TICKER ---------- */
.market-ticker {
    background: rgba(2,6,23,0.92);
    border-top:    1px solid rgba(56,189,248,0.10);
    border-bottom: 1px solid rgba(56,189,248,0.10);
    overflow: hidden;
    padding: 8px 0;
    margin-bottom: 30px;
}
.ticker-track {
    display: flex;
    animation: ticker-scroll 38s linear infinite;
    white-space: nowrap;
    width: max-content;
}
.ticker-track:hover { animation-play-state: paused; }
.ticker-item {
    font-size: 0.78rem;
    font-weight: 600;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0 26px;
    border-right: 1px solid rgba(148,163,184,0.10);
}
.ticker-item .t-sym  { color: #e2e8f0; font-weight: 700; }
.ticker-item .t-up   { color: #22c55e; }
.ticker-item .t-down { color: #ef4444; }
@keyframes ticker-scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* ---------- STATS BAR ---------- */
.community-stats-bar {
    display: flex;
    justify-content: center;
    gap: 14px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}
.stat-pill {
    background: rgba(15,23,42,0.82);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(56,189,248,0.15);
    border-radius: 14px;
    padding: 12px 22px;
    text-align: center;
    min-width: 88px;
    transition: all 0.3s ease;
    cursor: default;
}
.stat-pill:hover {
    border-color: rgba(56,189,248,0.42);
    box-shadow: 0 0 22px rgba(56,189,248,0.14);
    transform: translateY(-2px);
}
.stat-pill .stat-num {
    font-size: 1.45rem;
    font-weight: 800;
    color: #38bdf8;
    display: block;
    text-shadow: 0 0 14px rgba(56,189,248,0.45);
    font-variant-numeric: tabular-nums;
}
.stat-pill .stat-label {
    font-size: 0.68rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1.1px;
    margin-top: 3px;
}

/* ---------- TABS ---------- */
.comm-tabs {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-bottom: 36px;
}
.tab-btn {
    padding: 12px 30px;
    background: rgba(2,6,23,0.72);
    border: 1px solid rgba(148,163,184,0.22);
    border-radius: 50px;
    font-weight: 700;
    color: #94a3b8;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.28s ease;
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
}
.tab-btn:hover { color: #e2e8f0; border-color: rgba(56,189,248,0.35); }
.tab-btn.active {
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    color: #020617;
    border-color: transparent;
    box-shadow: 0 8px 28px rgba(56,189,248,0.32);
    transform: translateY(-1px);
}
.tab-content { display: none; }
.tab-content.active {
    display: block;
    animation: fadeUp 0.38s ease both;
}

/* ---------- FEED CONTAINER ---------- */
.feed-container {
    max-width: 700px;
    margin: 0 auto;
    padding-bottom: 40px;
}

/* ---------- CREATE POST CARD ---------- */
.create-post-card {
    background: rgba(10,16,32,.88);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
    border: 1px solid rgba(212,175,55,.22);
    border-radius: 20px;
    padding: 26px;
    margin-bottom: 28px;
    box-shadow: 0 20px 56px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.04);
    position: relative;
    overflow: hidden;
    transition: box-shadow .3s,transform .3s,border-color .3s;
    animation: cfSlideDown .5s ease both;
}
.create-post-card::before {
    content:"";position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,#d4af37,#a78bfa,#38bdf8,#d4af37);
    background-size:300% 100%;animation:cfShimmer 4s linear infinite;
}
.create-post-card:focus-within {
    border-color:rgba(212,175,55,.45);
    box-shadow:0 0 0 1px rgba(212,175,55,.2),0 28px 64px rgba(0,0,0,.6);
}

/* Composer header */
.cp-header {
    display:flex;align-items:center;gap:12px;margin-bottom:16px;
}
.cp-avatar-small {
    width:38px;height:38px;border-radius:50%;flex-shrink:0;
    background:linear-gradient(135deg,#d4af37,#f5d679);
    color:#020617;display:flex;align-items:center;justify-content:center;
    font-size:.9rem;font-weight:900;
    box-shadow:0 0 0 2px rgba(212,175,55,.3),0 0 14px rgba(212,175,55,.2);
}
.cp-header-text { flex:1; }
.cp-header-title { font-weight:800;color:#f1f5f9;font-size:.95rem; }
.cp-header-sub   { font-size:.7rem;color:#64748b; }

/* Title input */
.cp-title-input {
    width:100%;background:rgba(2,6,23,.6);color:#f1f5f9;
    border:1.5px solid rgba(212,175,55,.18);border-radius:10px;
    padding:12px 16px;font-family:inherit;font-size:1.05rem;font-weight:700;
    outline:none;transition:border-color .22s,box-shadow .22s;
    margin-bottom:10px;box-sizing:border-box;
    letter-spacing:.2px;
}
.cp-title-input::placeholder { color:#334155;font-weight:400; }
.cp-title-input:focus {
    border-color:rgba(212,175,55,.6);
    box-shadow:0 0 0 3px rgba(212,175,55,.1);
    background:rgba(2,6,23,.9);
}

/* Live title preview */
.cp-title-live {
    display:none;font-size:.78rem;color:rgba(212,175,55,.7);
    margin-bottom:6px;padding:0 2px;
    animation:cfSlideDown .22s ease;
}
.cp-title-live.show { display:block; }

.cp-input {
    width:100%;border:1.5px solid rgba(148,163,184,.18);
    background:rgba(2,6,23,.75);color:#e2e8f0;
    padding:13px 16px;border-radius:10px;resize:vertical;
    font-family:inherit;font-size:.93rem;margin-bottom:10px;
    line-height:1.65;transition:border-color .22s,box-shadow .22s;
    min-height:90px;box-sizing:border-box;outline:none;
}
.cp-input::placeholder { color:#334155; }
.cp-input:focus {
    background:rgba(2,6,23,.9);color:#e2e8f0;
    border-color:rgba(56,189,248,.45);
    box-shadow:0 0 0 3px rgba(56,189,248,.08);
}

/* Char counter */
.char-counter { text-align:right;font-size:.72rem;color:#334155;margin-top:-6px;margin-bottom:10px;transition:color .2s; }
.char-counter.warn  { color:#f59e0b; }
.char-counter.limit { color:#ef4444; }

/* Divider */
.cp-divider { height:1px;background:rgba(148,163,184,.08);margin-bottom:14px; }

.cp-tools {
    display:flex;gap:8px;align-items:center;flex-wrap:wrap;
}
.btn-tool {
    background:rgba(255,255,255,.04);color:#64748b;
    padding:8px 15px;border-radius:99px;font-size:.83rem;font-weight:600;
    cursor:pointer;border:1px solid rgba(148,163,184,.15);
    display:flex;align-items:center;gap:6px;transition:all .2s;font-family:inherit;
}
.btn-tool:hover { background:rgba(212,175,55,.1);border-color:rgba(212,175,55,.35);color:#d4af37; }
.btn-tool i { font-size:.9rem; }
.cp-post-btn {
    margin-left:auto;
    background:linear-gradient(135deg,#d4af37,#f5d679);
    color:#020617;border:none;padding:10px 26px;border-radius:10px;
    font-weight:900;font-size:.88rem;cursor:pointer;letter-spacing:.3px;
    box-shadow:0 4px 18px rgba(212,175,55,.3);transition:all .25s;
    display:inline-flex;align-items:center;gap:7px;font-family:inherit;
}
.cp-post-btn:hover { transform:translateY(-2px);box-shadow:0 8px 28px rgba(212,175,55,.5); }
.cp-post-btn:active { transform:translateY(0); }

/* Create post tip */
.create-tip-box {
    background:rgba(212,175,55,.06);border-left:3px solid #d4af37;
    color:#64748b;border-radius:8px;padding:9px 14px;
    margin-bottom:14px;font-size:.82rem;line-height:1.55;
}

/* ---------- POST CARDS ---------- */
@keyframes fadeUp {
    from { opacity:0;transform:translateY(22px) scale(.98); }
    to   { opacity:1;transform:none; }
}

.post-card {
    background:rgba(10,16,32,.82);
    backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
    border:1px solid rgba(255,255,255,.08);
    border-radius:20px;overflow:hidden;margin-bottom:22px;
    box-shadow:0 12px 40px rgba(0,0,0,.45);
    transition:transform .32s cubic-bezier(.22,1,.36,1),box-shadow .32s,border-color .32s;
    animation:fadeUp .48s ease both;
    position:relative;
}
.post-card::after {
    content:"";position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,transparent,rgba(212,175,55,.4),rgba(167,139,250,.35),transparent);
    opacity:0;transition:opacity .3s;
}
.post-card:hover::after { opacity:1; }
.post-card:nth-child(1){animation-delay:.04s}
.post-card:nth-child(2){animation-delay:.09s}
.post-card:nth-child(3){animation-delay:.14s}
.post-card:nth-child(4){animation-delay:.19s}
.post-card:nth-child(5){animation-delay:.23s}
.post-card:nth-child(n+6){animation-delay:.27s}
.post-card:hover {
    transform:translateY(-5px);
    border-color:rgba(212,175,55,.28);
    box-shadow:0 0 0 1px rgba(212,175,55,.14),0 28px 64px rgba(0,0,0,.6),0 0 30px rgba(212,175,55,.07);
}

/* Post title */
.post-title {
    font-size:1.12rem;font-weight:800;color:#f1f5f9;
    line-height:1.35;margin:0 0 8px;letter-spacing:.1px;
    padding:16px 20px 0;
    background:linear-gradient(135deg,#f1f5f9 60%,rgba(212,175,55,.9) 100%);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

/* Post content — no pre-wrap to prevent \r\n artefacts */
.post-content {
    padding:8px 20px 14px;color:#cbd5e1;
    font-size:.93rem;line-height:1.75;
    letter-spacing:.05px;word-break:break-word;
}

.post-header {
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    border-bottom: 1px solid rgba(148,163,184,0.07);
}
.p-avatar {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    color: #020617;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(56,189,248,0.30), 0 0 14px rgba(56,189,248,0.18);
}
.p-info h4  { font-size: 0.95rem; font-weight: 700; color: #e2e8f0; margin: 0 0 2px; }
.p-info span { font-size: 0.73rem; color: #64748b; }
.post-author-link {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    flex-shrink: 0;
    transition: opacity 0.2s;
}
.post-author-link:hover { opacity: 0.82; }
.post-author-link:hover .p-avatar {
    box-shadow: 0 0 0 3px rgba(56,189,248,0.55), 0 0 18px rgba(56,189,248,0.30);
}
.post-author-link:hover .p-info h4 {
    color: #38bdf8;
}

/* Post options menu */
.post-options { margin-left: auto; position: relative; }
.opt-btn {
    background: none;
    border: none;
    font-size: 1.1rem;
    cursor: pointer;
    color: #475569;
    padding: 6px 8px;
    border-radius: 8px;
    transition: all 0.2s;
}
.opt-btn:hover { background: rgba(56,189,248,0.10); color: #94a3b8; }
.opt-menu {
    display: none;
    position: absolute;
    right: 0; top: calc(100% + 4px);
    background: rgba(2,6,23,0.97);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(56,189,248,0.22);
    box-shadow: 0 12px 32px rgba(0,0,0,0.55), 0 0 0 1px rgba(56,189,248,0.08);
    border-radius: 10px;
    width: 130px;
    z-index: 50;
    overflow: hidden;
}
.opt-menu.show { display: block; animation: fadeUp 0.18s ease both; }
.opt-item {
    display: block;
    width: 100%;
    text-align: left;
    padding: 10px 14px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    color: #cbd5e1;
    transition: all 0.18s;
}
.opt-item:hover  { background: rgba(56,189,248,0.10); color: #38bdf8; }
.opt-item.danger { color: #f87171; }
.opt-item.danger:hover { background: rgba(239,68,68,0.10); color: #f87171; }

/* Edit form */
.edit-post-form { padding: 12px 18px 16px; }
.edit-post-form textarea {
    width: 100%;
    background: rgba(2,6,23,0.88);
    color: #e2e8f0;
    border: 1px solid rgba(56,189,248,0.22);
    border-radius: 10px;
    padding: 12px 14px;
    font-family: inherit;
    font-size: 0.95rem;
    resize: none;
    margin-bottom: 10px;
    transition: border-color 0.22s, box-shadow 0.22s;
}
.edit-post-form textarea:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 2px rgba(56,189,248,0.22);
}

/* Post visuals */
.post-visuals {
    width: 100%;
    background: #000;
    display: flex;
    justify-content: center;
}
.post-visuals img    { max-width: 100%; max-height: 500px; object-fit: contain; }
.post-visuals iframe { width: 100%; height: 340px; border: none; }

/* Post footer */
.post-footer {
    background: rgba(2,6,23,0.40);
    padding: 10px 16px 12px;
    border-top: 1px solid rgba(148,163,184,0.07);
}

/* Reaction bar */
.reaction-bar {
    display:flex;gap:5px;padding:10px 0;
    border-bottom:1px solid rgba(255,255,255,.05);
    margin-bottom:12px;flex-wrap:wrap;
}
.react-btn {
    background:transparent;border:none;cursor:pointer;
    display:flex;align-items:center;gap:6px;
    font-size:.85rem;color:#475569;
    padding:7px 14px;border-radius:99px;
    border:1px solid transparent;
    transition:all .22s;font-family:inherit;
}
.react-btn:hover {
    background:rgba(212,175,55,.08);
    border-color:rgba(212,175,55,.25);
    color:#d4af37;transform:translateY(-2px);
}
.react-btn:hover i { animation:cfPop .35s ease; }
.react-btn i { font-size:.95rem;transition:transform .2s; }
.react-btn.active {
    background:rgba(212,175,55,.12);color:#d4af37;
    border-color:rgba(212,175,55,.3);font-weight:700;
    box-shadow:inset 0 0 12px rgba(212,175,55,.1);
}
.react-count { font-size:.76rem;font-weight:800; }

/* Comments */
.comments-area {
    max-height:220px;overflow-y:auto;margin-bottom:10px;
    display:flex;flex-direction:column;gap:7px;
}
.comments-area::-webkit-scrollbar       { width:4px; }
.comments-area::-webkit-scrollbar-track { background:transparent; }
.comments-area::-webkit-scrollbar-thumb { background:rgba(212,175,55,.25);border-radius:4px; }

.comment-single {
    font-size:.85rem;background:rgba(2,6,23,.75);color:#cbd5e1;
    padding:8px 14px;border-radius:12px;
    border:1px solid rgba(255,255,255,.06);
    word-break:break-word;line-height:1.55;
    transition:border-color .2s;animation:fadeUp .25s ease both;
}
.comment-single:hover { border-color:rgba(212,175,55,.18); }
.c-author { font-weight:800;color:#d4af37;margin-right:6px;text-decoration:none;transition:color .18s; }
.c-author:hover { color:#f5d679;text-decoration:underline; }

.comment-form { display:flex;gap:8px;align-items:center; }
.c-box {
    flex:1;padding:10px 16px;border-radius:99px;
    border:1.5px solid rgba(148,163,184,.15);
    background:rgba(2,6,23,.8);color:#e2e8f0;
    font-size:.85rem;outline:none;font-family:inherit;
    transition:border-color .22s,box-shadow .22s;
}
.c-box::placeholder { color:#334155; }
.c-box:focus { border-color:rgba(212,175,55,.45);box-shadow:0 0 0 2px rgba(212,175,55,.1); }
.c-btn {
    background:linear-gradient(135deg,#d4af37,#f5d679);
    color:#020617;border:none;padding:9px 18px;border-radius:99px;
    font-weight:800;font-size:.82rem;cursor:pointer;
    transition:box-shadow .22s,transform .22s;white-space:nowrap;font-family:inherit;
}
.c-btn:hover { box-shadow:0 0 18px rgba(212,175,55,.5);transform:translateY(-1px); }

/* ---------- CHAT SECTION ---------- */
.chat-wrapper-inner {
    height: 620px;
    background: rgba(15,23,42,0.80);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-radius: 20px;
    box-shadow: 0 20px 56px rgba(0,0,0,0.55);
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(148,163,184,0.14);
    max-width: 980px;
    margin: 0 auto;
    overflow: hidden;
}
.chat-header {
    background: rgba(2,6,23,0.90);
    padding: 16px 22px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(56,189,248,0.18);
    flex-shrink: 0;
}
.chat-box {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: rgba(2,6,23,0.55);
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.chat-box::-webkit-scrollbar       { width: 5px; }
.chat-box::-webkit-scrollbar-track { background: transparent; }
.chat-box::-webkit-scrollbar-thumb { background: rgba(56,189,248,0.25); border-radius: 4px; }

.msg-row { display: flex; flex-direction: column; align-items: flex-start; }
.msg-row.me { align-items: flex-end; }
.msg-bubble {
    background: rgba(15,23,42,0.90);
    padding: 10px 16px;
    border-radius: 4px 14px 14px 14px;
    border: 1px solid rgba(148,163,184,0.12);
    max-width: 70%;
    color: #e2e8f0;
    font-size: 0.9rem;
    line-height: 1.55;
    word-break: break-word;
}
.msg-row.me .msg-bubble {
    background: linear-gradient(135deg, rgba(56,189,248,0.16), rgba(34,211,238,0.10));
    border: 1px solid rgba(56,189,248,0.28);
    border-radius: 14px 4px 14px 14px;
    color: #e2e8f0;
}
.msg-bubble.mentioned-me {
    border: 2px solid rgba(212,175,55,0.6);
    box-shadow: 0 0 14px rgba(212,175,55,0.25);
    animation: glow-pulse 2.2s ease-in-out infinite;
}
@keyframes glow-pulse {
    0%, 100% { box-shadow: 0 0 6px rgba(212,175,55,0.2); }
    50%       { box-shadow: 0 0 18px rgba(212,175,55,0.45); }
}

.chat-input-area {
    padding: 18px 20px;
    background: rgba(2,6,23,0.90);
    border-top: 1px solid rgba(56,189,248,0.12);
    display: flex;
    gap: 10px;
    flex-shrink: 0;
}
.c-input {
    flex: 1;
    padding: 12px 18px;
    border: 1px solid rgba(148,163,184,0.18);
    border-radius: 50px;
    outline: none;
    background: rgba(15,23,42,0.88);
    color: #e2e8f0;
    font-family: inherit;
    font-size: 0.9rem;
    transition: border-color 0.22s, box-shadow 0.22s;
}
.c-input::placeholder { color: #475569; }
.c-input:focus {
    border-color: rgba(56,189,248,0.50);
    box-shadow: 0 0 0 2px rgba(56,189,248,0.14);
}
.c-send {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    border: none;
    border-radius: 50%;
    color: #020617;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 0 14px rgba(56,189,248,0.30);
    transition: all 0.25s ease;
    flex-shrink: 0;
}
.c-send:hover {
    box-shadow: 0 0 24px rgba(56,189,248,0.60);
    transform: scale(1.08);
}

/* Mention highlight */
.mention-highlight {
    color: #38bdf8;
    font-weight: 700;
    background: rgba(56,189,248,0.10);
    padding: 0 4px;
    border-radius: 4px;
}

/* ---------- LIVE BADGE ---------- */
.live-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(239,68,68,0.10);
    border: 1px solid rgba(239,68,68,0.38);
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.68rem;
    font-weight: 700;
    color: #f87171;
    letter-spacing: 1.3px;
    text-transform: uppercase;
}
.live-dot {
    width: 7px;
    height: 7px;
    background: #ef4444;
    border-radius: 50%;
    box-shadow: 0 0 7px #ef4444;
    animation: live-pulse 1.5s ease-in-out infinite;
}
@keyframes live-pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50%       { transform: scale(0.55); opacity: 0.5; }
}

/* ---------- EMPTY STATE ---------- */
.empty-feed-state {
    text-align: center;
    padding: 64px 20px;
}
.empty-feed-state i {
    font-size: 48px;
    margin-bottom: 16px;
    display: block;
    color: #38bdf8;
    opacity: 0.30;
}
.empty-feed-state p { color: #64748b; font-size: 0.95rem; }

/* ---------- IMAGE PREVIEW ---------- */
#img-preview img {
    max-height: 150px;
    border-radius: 10px;
    border: 1px solid rgba(56,189,248,0.25);
    box-shadow: 0 0 12px rgba(56,189,248,0.10);
}

/* ========== PEOPLE / PROFILES TAB ========== */
.people-container {
    max-width: 960px;
    margin: 0 auto;
    padding-bottom: 60px;
}

/* My Profile banner */
.my-profile-banner {
    background: rgba(15,23,42,0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(56,189,248,0.18);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 36px;
    display: flex;
    align-items: center;
    gap: 22px;
    box-shadow: 0 20px 52px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.04);
    position: relative;
    overflow: hidden;
}
.my-profile-banner::before {
    content:"";
    position:absolute;
    top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,#d4af37,#38bdf8,#22d3ee,#d4af37);
    background-size:200% 100%;
    animation:shimmer 4s linear infinite;
}
.my-profile-banner-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: 800;
    color: #020617;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(56,189,248,0.35);
    overflow: hidden;
}
.my-profile-banner-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.my-profile-banner-info { flex: 1; }
.my-profile-banner-info h3 {
    margin: 0 0 4px;
    font-size: 1.25rem;
    color: #f1f5f9;
    font-family: 'Playfair Display', serif;
}
.my-profile-banner-info p {
    margin: 0;
    color: #64748b;
    font-size: 0.87rem;
}
.profile-stats-row {
    display: flex;
    gap: 28px;
    margin-top: 10px;
}
.profile-stat { text-align: center; }
.profile-stat strong {
    display: block;
    font-size: 1.15rem;
    color: #38bdf8;
    font-weight: 800;
}
.profile-stat small { color: #64748b; font-size: 0.75rem; }
.btn-edit-profile {
    padding: 10px 24px;
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    color: #020617;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.87rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.25s ease;
    box-shadow: 0 0 18px rgba(56,189,248,0.28);
    flex-shrink: 0;
}
.btn-edit-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 28px rgba(56,189,248,0.55);
}

/* People section heading */
.people-section-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}
.people-section-head h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    background: linear-gradient(135deg, #f1f5f9, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.people-section-head .count-badge {
    background: rgba(56,189,248,0.12);
    border: 1px solid rgba(56,189,248,0.25);
    color: #38bdf8;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

/* User cards grid */
.people-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
@media(max-width: 900px) { .people-grid { grid-template-columns: repeat(2, 1fr); } }
@media(max-width: 560px) { .people-grid { grid-template-columns: 1fr; } }

/* Individual user card */
.user-card {
    background: rgba(15,23,42,0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(56,189,248,0.13);
    border-radius: 18px;
    padding: 24px 20px 20px;
    text-align: center;
    transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
    box-shadow: 0 12px 36px rgba(0,0,0,0.40);
    position: relative;
    overflow: hidden;
}
.user-card:hover {
    transform: translateY(-4px);
    border-color: rgba(56,189,248,0.35);
    box-shadow: 0 0 0 1px rgba(56,189,248,0.18), 0 24px 52px rgba(0,0,0,0.60);
}
.user-card-cover {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 68px;
    background: linear-gradient(135deg, rgba(56,189,248,0.12), rgba(34,211,238,0.08));
    overflow: hidden;
}
.user-card-cover img { width:100%;height:100%;object-fit:cover;opacity:0.6; }

.user-card-avatar {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    font-weight: 800;
    color: #020617;
    margin: 40px auto 12px;
    box-shadow: 0 0 0 3px rgba(2,6,23,0.9), 0 0 0 5px rgba(56,189,248,0.35);
    position: relative;
    z-index: 1;
    overflow: hidden;
    flex-shrink: 0;
}
.user-card-avatar img { width:100%;height:100%;object-fit:cover; }

.user-card-name {
    font-size: 1rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0 0 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.user-card-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 20px;
    margin-bottom: 10px;
    letter-spacing: 0.4px;
}
.badge-premium { background: rgba(212,175,55,0.15); color: #d4af37; border: 1px solid rgba(212,175,55,0.3); }
.badge-free { background: rgba(100,116,139,0.15); color: #94a3b8; border: 1px solid rgba(100,116,139,0.25); }

.user-card-bio {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0 0 14px;
    line-height: 1.5;
    min-height: 36px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.user-card-stats {
    display: flex;
    justify-content: center;
    gap: 18px;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid rgba(56,189,248,0.08);
}
.ucs { text-align: center; }
.ucs strong { display:block; font-size:0.92rem; color:#38bdf8; font-weight:800; }
.ucs small { color:#64748b; font-size:0.72rem; }

.user-card-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
}
.btn-follow, .btn-unfollow, .btn-view-profile {
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
}
.btn-follow {
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    color: #020617;
    box-shadow: 0 0 14px rgba(56,189,248,0.28);
}
.btn-follow:hover { transform:translateY(-1px); box-shadow:0 0 22px rgba(56,189,248,0.5); }
.btn-unfollow {
    background: rgba(100,116,139,0.15);
    color: #94a3b8;
    border: 1px solid rgba(100,116,139,0.28);
}
.btn-unfollow:hover { background:rgba(239,68,68,0.12); color:#f87171; border-color:rgba(239,68,68,0.35); }
.btn-view-profile {
    background: rgba(56,189,248,0.08);
    color: #38bdf8;
    border: 1px solid rgba(56,189,248,0.22);
}
.btn-view-profile:hover { background:rgba(56,189,248,0.15); transform:translateY(-1px); }

/* Followers/Following modal */
.ff-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(2,6,23,0.82);
    backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.ff-modal-overlay.open { display: flex; }
.ff-modal {
    background: rgba(15,23,42,0.98);
    border: 1px solid rgba(56,189,248,0.22);
    border-radius: 20px;
    width: 92%;
    max-width: 400px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 32px 80px rgba(0,0,0,0.7);
}
.ff-modal-head {
    padding: 20px 24px;
    border-bottom: 1px solid rgba(56,189,248,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ff-modal-head h3 { margin:0; color:#f1f5f9; font-size:1rem; }
.ff-modal-close { background:none; border:none; color:#64748b; cursor:pointer; font-size:1.1rem; }
.ff-modal-close:hover { color:#e2e8f0; }
.ff-modal-list { overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:12px; }
.ff-user-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: rgba(56,189,248,0.04);
    border: 1px solid rgba(56,189,248,0.08);
    text-decoration: none;
    transition: background 0.2s;
}
.ff-user-row:hover { background:rgba(56,189,248,0.10); }
.ff-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg,#38bdf8,#22d3ee);
    display: flex; align-items: center; justify-content: center;
    font-size:0.95rem; font-weight:800; color:#020617;
    flex-shrink:0; overflow:hidden;
}
.ff-avatar img { width:100%;height:100%;object-fit:cover; }
.ff-user-name { color:#f1f5f9; font-weight:600; font-size:0.9rem; }
.ff-user-bio { color:#64748b; font-size:0.76rem; }

/* No users state */
.no-people {
    text-align:center; padding:60px 20px;
}
.no-people i { font-size:48px; color:#38bdf8; opacity:0.25; display:block; margin-bottom:16px; }
.no-people p { color:#64748b; }

/* ---- Search bar ---- */
.people-search-wrap {
    position: relative;
    margin-bottom: 32px;
}
.people-search-icon {
    position: absolute;
    left: 18px; top: 50%;
    transform: translateY(-50%);
    color: #38bdf8;
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s;
}
.people-search-input {
    width: 100%;
    padding: 15px 50px 15px 50px;
    background: rgba(15,23,42,0.88);
    border: 1px solid rgba(56,189,248,0.20);
    border-radius: 50px;
    color: #e2e8f0;
    font-family: inherit;
    font-size: 0.97rem;
    outline: none;
    transition: border-color 0.28s, box-shadow 0.28s, background 0.28s;
    backdrop-filter: blur(14px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}
.people-search-input::placeholder { color: #475569; }
.people-search-input:focus {
    border-color: rgba(56,189,248,0.55);
    box-shadow: 0 0 0 3px rgba(56,189,248,0.12), 0 8px 32px rgba(0,0,0,0.35);
    background: rgba(15,23,42,0.95);
}
.people-search-input:focus ~ .people-search-icon { color: #22d3ee; }
.search-clear-btn {
    position: absolute;
    right: 18px; top: 50%;
    transform: translateY(-50%);
    background: rgba(100,116,139,0.15);
    border: 1px solid rgba(100,116,139,0.25);
    color: #94a3b8;
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: 0.75rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.22s, background 0.22s, color 0.22s, transform 0.22s;
}
.search-clear-btn.visible { opacity: 1; pointer-events: all; }
.search-clear-btn:hover { background:rgba(239,68,68,0.15); color:#f87171; border-color:rgba(239,68,68,0.35); transform:translateY(-50%) scale(1.15); }

.search-no-results {
    display: none;
    text-align: center;
    padding: 50px 20px;
    animation: fadeUp 0.3s ease both;
}
.search-no-results i { font-size:40px; color:#38bdf8; opacity:0.22; display:block; margin-bottom:14px; }
.search-no-results p { color:#64748b; font-size:0.92rem; }
.search-no-results strong { color:#94a3b8; }

/* ---- Card entrance animation ---- */
@keyframes cardIn {
    from { opacity:0; transform:translateY(28px) scale(0.96); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
.user-card.card-anim {
    animation: cardIn 0.5s ease both;
}

/* Card search-hide (instant, no animation needed) */
.user-card.search-hidden {
    display: none !important;
}

/* My profile banner entrance */
@keyframes bannerIn {
    from { opacity:0; transform:translateY(-16px); }
    to   { opacity:1; transform:translateY(0); }
}
.my-profile-banner {
    animation: bannerIn 0.45s ease both;
}

/* Responsive banner */
@media(max-width:600px) {
    .my-profile-banner { flex-direction:column; text-align:center; }
    .profile-stats-row { justify-content:center; }
}
</style>

<div class="community-wrapper">
    
    <!-- ================= COMMUNITY INTRO ================= -->
<div class="community-intro-card">
    <h2 class="community-title">Welcome to the Wise Invest Community</h2>

    <p class="community-intro-text">
        A private, education-focused investing community built for serious learners.
        Our goal is to help members understand global markets, manage risk responsibly,
        and grow with discipline — not hype.
    </p>

    <div class="disclaimer-box">
        <strong style="color:#d4af37;">Important:</strong>&nbsp;
        This is <b>NOT</b> a signal group. There are no guaranteed profits, no paid tips, and no financial advice.
        Education and discussion come first.
    </div>

    <h4 class="guidelines-title">Community Guidelines</h4>
    <ul class="guidelines-list">
        <li>Be respectful and professional at all times</li>
        <li>No spam, promotions, or signal selling</li>
        <li>Focus on learning, analysis, and long-term thinking</li>
        <li>Respect different experience levels — beginners are welcome</li>
        <li>Admins reserve the right to remove harmful content</li>
    </ul>
</div>

<!-- ================= MARKET TICKER ================= -->
<div class="market-ticker">
    <div class="ticker-track">
        <!-- First set -->
        <span class="ticker-item"><span class="t-sym">SPX</span> 5,214.08 <span class="t-up">+0.82%</span></span>
        <span class="ticker-item"><span class="t-sym">NASDAQ</span> 16,379.46 <span class="t-up">+1.14%</span></span>
        <span class="ticker-item"><span class="t-sym">BTC/USD</span> 68,240 <span class="t-up">+2.31%</span></span>
        <span class="ticker-item"><span class="t-sym">ETH/USD</span> 3,512 <span class="t-up">+1.87%</span></span>
        <span class="ticker-item"><span class="t-sym">GOLD</span> 2,328.40 <span class="t-down">-0.14%</span></span>
        <span class="ticker-item"><span class="t-sym">EUR/USD</span> 1.0831 <span class="t-down">-0.22%</span></span>
        <span class="ticker-item"><span class="t-sym">OIL/WTI</span> 82.14 <span class="t-up">+0.54%</span></span>
        <span class="ticker-item"><span class="t-sym">DXY</span> 104.72 <span class="t-up">+0.18%</span></span>
        <span class="ticker-item"><span class="t-sym">AAPL</span> 189.30 <span class="t-down">-0.33%</span></span>
        <span class="ticker-item"><span class="t-sym">TSLA</span> 175.21 <span class="t-up">+3.12%</span></span>
        <!-- Duplicate for seamless loop -->
        <span class="ticker-item"><span class="t-sym">SPX</span> 5,214.08 <span class="t-up">+0.82%</span></span>
        <span class="ticker-item"><span class="t-sym">NASDAQ</span> 16,379.46 <span class="t-up">+1.14%</span></span>
        <span class="ticker-item"><span class="t-sym">BTC/USD</span> 68,240 <span class="t-up">+2.31%</span></span>
        <span class="ticker-item"><span class="t-sym">ETH/USD</span> 3,512 <span class="t-up">+1.87%</span></span>
        <span class="ticker-item"><span class="t-sym">GOLD</span> 2,328.40 <span class="t-down">-0.14%</span></span>
        <span class="ticker-item"><span class="t-sym">EUR/USD</span> 1.0831 <span class="t-down">-0.22%</span></span>
        <span class="ticker-item"><span class="t-sym">OIL/WTI</span> 82.14 <span class="t-up">+0.54%</span></span>
        <span class="ticker-item"><span class="t-sym">DXY</span> 104.72 <span class="t-up">+0.18%</span></span>
        <span class="ticker-item"><span class="t-sym">AAPL</span> 189.30 <span class="t-down">-0.33%</span></span>
        <span class="ticker-item"><span class="t-sym">TSLA</span> 175.21 <span class="t-up">+3.12%</span></span>
    </div>
</div>

<!-- ================= STATS BAR ================= -->
<div class="community-stats-bar">
    <div class="stat-pill">
        <span class="stat-num" id="stat-members"><?php
            if(isset($conn)) {
                $mc = $conn->query("SELECT COUNT(*) as c FROM users");
                echo ($mc) ? $mc->fetch_assoc()['c'] : '—';
            }
        ?></span>
        <span class="stat-label">Members</span>
    </div>
    <div class="stat-pill">
        <span class="stat-num" id="stat-posts"><?php
            if(isset($conn)) {
                $pc = $conn->query("SELECT COUNT(*) as c FROM social_posts WHERE DATE(created_at) = CURDATE()");
                echo ($pc) ? $pc->fetch_assoc()['c'] : '0';
            }
        ?></span>
        <span class="stat-label">Posts Today</span>
    </div>
    <div class="stat-pill">
        <span class="stat-num" id="stat-total"><?php
            if(isset($conn)) {
                $tc = $conn->query("SELECT COUNT(*) as c FROM social_posts");
                echo ($tc) ? $tc->fetch_assoc()['c'] : '—';
            }
        ?></span>
        <span class="stat-label">Total Posts</span>
    </div>
    <div class="stat-pill">
        <span class="stat-num" id="stat-comments"><?php
            if(isset($conn)) {
                $cc = $conn->query("SELECT COUNT(*) as c FROM social_comments");
                echo ($cc) ? $cc->fetch_assoc()['c'] : '—';
            }
        ?></span>
        <span class="stat-label">Comments</span>
    </div>
</div>
    
    <?php $active_tab = in_array($_GET['tab'] ?? '', ['feed','chat','people']) ? $_GET['tab'] : 'feed'; ?>
    <div class="comm-tabs">
    <div class="tab-btn <?php echo $active_tab==='feed'?'active':''; ?>" data-tab="feed" onclick="switchTab('feed')">
        <i class="fas fa-rss"></i> Community Feed
    </div>
    <div class="tab-btn <?php echo $active_tab==='chat'?'active':''; ?>" data-tab="chat" onclick="switchTab('chat')">
        <i class="fas fa-comments"></i> Live Discussion
    </div>
    <div class="tab-btn <?php echo $active_tab==='people'?'active':''; ?>" data-tab="people" onclick="switchTab('people')">
        <i class="fas fa-users"></i> People
    </div>
</div>

    <div id="tab-feed" class="tab-content <?php echo $active_tab==='feed'?'active':''; ?>">
        <div class="feed-container">
            
            <div class="create-post-card">
    <div class="cp-header">
        <div class="cp-avatar-small"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
        <div class="cp-header-text">
            <div class="cp-header-title">What's on your mind, <?= htmlspecialchars(explode(' ',$_SESSION['user_name'])[0]) ?>?</div>
            <div class="cp-header-sub">Education-first community &nbsp;·&nbsp; No signals, no tips</div>
        </div>
    </div>

    <div class="create-tip-box">
        <i class="fas fa-lightbulb" style="color:#d4af37;margin-right:6px;"></i>
        Share insights, analysis, or questions. Keep it educational and respectful.
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_post">

        <!-- Title field -->
        <input
            type="text"
            name="post_title"
            id="post-title-input"
            class="cp-title-input"
            maxlength="200"
            placeholder="Give your post a title (optional but recommended)"
            oninput="onTitleInput(this)"
            autocomplete="off"
        >
        <div class="cp-title-live" id="title-live-preview"></div>

        <!-- Content -->
        <textarea
            name="content"
            id="post-textarea"
            class="cp-input"
            rows="4"
            maxlength="2000"
            placeholder="Write your post here… Share analysis, questions, or market observations."
            oninput="onPostInput(this)"
        ></textarea>
        <div class="char-counter" id="char-counter">0 / 2000</div>

        <!-- YouTube link (hidden by default) -->
        <div id="extra-fields" style="display:none;margin-bottom:12px;">
            <input
                type="text"
                name="video_link"
                class="cp-input"
                style="min-height:unset;padding:11px 16px;font-size:.88rem;margin-bottom:0;"
                placeholder="Paste YouTube URL…"
            >
        </div>

        <div class="cp-divider"></div>

        <div class="cp-tools">
            <label class="btn-tool" title="Attach image">
                <i class="fas fa-image" style="color:#4ade80"></i> Photo
                <input type="file" name="post_image" accept="image/*" style="display:none;" onchange="previewImage(this)">
            </label>
            <button type="button" class="btn-tool" onclick="toggleYouTube()" title="Attach YouTube video">
                <i class="fab fa-youtube" style="color:#ef4444"></i> Video
            </button>
            <button type="submit" class="cp-post-btn">
                <i class="fas fa-paper-plane"></i> Publish Post
            </button>
        </div>

        <div id="img-preview" style="margin-top:14px;display:none;">
            <img id="preview-src" src="" style="max-height:150px;border-radius:10px;border:1px solid rgba(212,175,55,.25);">
        </div>
    </form>
</div>


            <?php
            // Fetch Posts
            $posts_sql = "SELECT p.*, u.fullname FROM social_posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 20";
            if (isset($conn)) {
                $posts_res = $conn->query($posts_sql);

                if ($posts_res && $posts_res->num_rows > 0) {
                    while($post = $posts_res->fetch_assoc()) {
                        
                        // REACTION DATA
                        $pid = $post['id'];
                        $uid = $_SESSION['user_id'];
                        
                        $r_sql = "SELECT reaction_type, COUNT(*) as cnt FROM post_reactions WHERE post_id=$pid GROUP BY reaction_type";
                        $r_res = $conn->query($r_sql);
                        $reacts = ['like'=>0, 'love'=>0, 'wow'=>0, 'dislike'=>0];
                        if($r_res) { while($r = $r_res->fetch_assoc()) { $reacts[$r['reaction_type']] = $r['cnt']; } }

                        $my_r_sql = "SELECT reaction_type FROM post_reactions WHERE post_id=$pid AND user_id=$uid";
                        $my_r_res = $conn->query($my_r_sql);
                        $my_react = ($my_r_res && $my_r_res->num_rows > 0) ? $my_r_res->fetch_assoc()['reaction_type'] : '';
                        
                        // NEW: CHECK OWNERSHIP
                        $is_owner = ($post['user_id'] == $_SESSION['user_id']);
                        ?>
                        <?php
                        // Avatar colour per user
                        $avatarColors = ['#6366f1','#8b5cf6','#d4af37','#ec4899','#f97316','#10b981','#0ea5e9'];
                        $avatarCol = $avatarColors[abs(crc32($post['fullname'])) % count($avatarColors)];
                        $cleanedContent = cleanContent($post['content']);
                        $postTitle      = !empty($post['title']) ? stripslashes($post['title']) : '';
                        ?>
                        <div class="post-card">
                            <div class="post-header">
                                <a href="profile.php?id=<?= $post['user_id'] ?>" class="post-author-link">
                                    <div class="p-avatar" style="background:<?= $avatarCol ?>;box-shadow:0 0 0 2px <?= $avatarCol ?>44">
                                        <?= strtoupper(substr($post['fullname'],0,1)) ?>
                                    </div>
                                    <div class="p-info">
                                        <h4><?= htmlspecialchars($post['fullname']) ?></h4>
                                        <span>
                                            <i class="fas fa-clock" style="font-size:.6rem;margin-right:3px;opacity:.5"></i>
                                            <?= date('M j · g:i a', strtotime($post['created_at'])) ?>
                                        </span>
                                    </div>
                                </a>

                                <?php if ($is_owner): ?>
                                <div class="post-options">
                                    <button class="opt-btn" onclick="toggleMenu(<?= $pid ?>)"><i class="fas fa-ellipsis-h"></i></button>
                                    <div id="menu-<?= $pid ?>" class="opt-menu">
                                        <button class="opt-item" onclick="enableEdit(<?= $pid ?>)"><i class="fas fa-pen"></i> Edit</button>
                                        <form method="POST" onsubmit="return confirm('Delete this post?');">
                                            <input type="hidden" name="action" value="delete_post">
                                            <input type="hidden" name="post_id" value="<?= $pid ?>">
                                            <button type="submit" class="opt-item danger"><i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($postTitle): ?>
                            <h3 class="post-title"><?= htmlspecialchars($postTitle) ?></h3>
                            <?php endif; ?>

                            <div class="post-content" id="content-view-<?= $pid ?>">
                                <?= nl2br(htmlspecialchars($cleanedContent)) ?>
                            </div>

                            <?php if ($is_owner): ?>
                            <div id="content-edit-<?= $pid ?>" style="display:none;padding:14px 18px 16px;">
                                <form method="POST" class="edit-post-form">
                                    <input type="hidden" name="action" value="edit_post">
                                    <input type="hidden" name="post_id" value="<?= $pid ?>">
                                    <input type="text" name="post_title" class="cp-title-input"
                                           value="<?= htmlspecialchars($postTitle) ?>"
                                           placeholder="Post title (optional)"
                                           style="margin-bottom:8px;">
                                    <textarea name="content" rows="4"><?= htmlspecialchars($cleanedContent) ?></textarea>
                                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
                                        <button type="button" class="btn-tool" onclick="cancelEdit(<?= $pid ?>)">Cancel</button>
                                        <button type="submit" class="cp-post-btn"><i class="fas fa-save"></i> Save</button>
                                    </div>
                                </form>
                            </div>
                            <?php endif; ?>

                            <?php if(!empty($post['image_url']) || !empty($post['video_link'])): ?>
                                <div class="post-visuals">
                                    <?php if(!empty($post['image_url'])): ?>
                                        <img src="<?php echo $post['image_url']; ?>" alt="Post Image">
                                    <?php endif; ?>
                                    <?php if(!empty($post['video_link'])): ?>
                                        <iframe src="<?php echo $post['video_link']; ?>" allowfullscreen></iframe>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="post-footer">
                                <div class="reaction-bar">
                                    <button class="react-btn <?php echo ($my_react=='like')?'active':''; ?>" onclick="sendReaction(this, <?php echo $pid; ?>, 'like')">
                                        <i class="fas fa-thumbs-up"></i> Like <span class="react-count"><?php echo ($reacts['like']>0)?$reacts['like']:''; ?></span>
                                    </button>
                                    <button class="react-btn <?php echo ($my_react=='love')?'active':''; ?>" onclick="sendReaction(this, <?php echo $pid; ?>, 'love')">
                                        <i class="fas fa-heart" style="color:<?php echo ($my_react=='love')?'#ef4444':''; ?>"></i> Love <span class="react-count"><?php echo ($reacts['love']>0)?$reacts['love']:''; ?></span>
                                    </button>
                                    <button class="react-btn <?php echo ($my_react=='wow')?'active':''; ?>" onclick="sendReaction(this, <?php echo $pid; ?>, 'wow')">
                                        <i class="fas fa-surprise" style="color:<?php echo ($my_react=='wow')?'#eab308':''; ?>"></i> Wow <span class="react-count"><?php echo ($reacts['wow']>0)?$reacts['wow']:''; ?></span>
                                    </button>
                                    <button class="react-btn <?php echo ($my_react=='dislike')?'active':''; ?>" onclick="sendReaction(this, <?php echo $pid; ?>, 'dislike')">
                                        <i class="fas fa-thumbs-down"></i> Dislike <span class="react-count"><?php echo ($reacts['dislike']>0)?$reacts['dislike']:''; ?></span>
                                    </button>
                                </div>

                                <?php
                                $cmt_sql = "SELECT c.*, u.fullname FROM social_comments c JOIN users u ON c.user_id = u.id WHERE post_id = $pid ORDER BY c.created_at ASC";
                                $cmt_res = $conn->query($cmt_sql);
                                ?>
                                <div class="comments-area">
                                    <?php while($cmt = $cmt_res->fetch_assoc()): ?>
                                        <div class="comment-single">
                                            <a href="profile.php?id=<?php echo $cmt['user_id']; ?>" class="c-author"><?php echo htmlspecialchars($cmt['fullname']); ?></a>
                                            <?php echo htmlspecialchars($cmt['comment']); ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>

                                <form method="POST" class="comment-form">
                                    <input type="hidden" name="action" value="add_comment">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="text" name="comment" class="c-box" placeholder="Write a comment..." required>
                                    <button type="submit" class="c-btn">Send</button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='empty-feed-state'><i class='fas fa-satellite-dish'></i><p>No posts yet — be the first to start the conversation.</p></div>";
                }
            }
            ?>
        </div>
    </div>

    <div id="tab-chat" class="tab-content <?php echo $active_tab==='chat'?'active':''; ?>">
        <div class="chat-wrapper-inner">
            <div class="chat-header">
                <div>
                    <h2 style="font-family:'Playfair Display'; margin:0; font-size:1.5rem; background:linear-gradient(135deg,#f1f5f9,#38bdf8); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">Wise Chat</h2>
                    <span style="font-size:0.75rem; color:#64748b; letter-spacing:0.4px;">Type <span style="color:#38bdf8;">@name</span> to mention someone</span>
                </div>
                <div class="live-badge"><span class="live-dot"></span>LIVE</div>
            </div>

            <div class="chat-box" id="chat-box">
                <div style="text-align:center; color:#94a3b8; margin-top:20px;">Loading conversation...</div>
            </div>

            <form id="chat-form" class="chat-input-area">
                <input type="text" id="msg-input" class="c-input" placeholder="Type a message..." autocomplete="off">
                <button type="submit" class="c-send"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <!-- ===== PEOPLE TAB ===== -->
    <div id="tab-people" class="tab-content <?php echo $active_tab==='people'?'active':''; ?>">
        <?php
        // Fetch my profile
        $my_uid = $_SESSION['user_id'];
        $myp = $conn->query("SELECT * FROM user_profiles WHERE user_id=$my_uid");
        $my_profile = ($myp && $myp->num_rows > 0) ? $myp->fetch_assoc() : [];

        $my_followers = $conn->query("SELECT COUNT(*) as c FROM user_follows WHERE following_id=$my_uid")->fetch_assoc()['c'];
        $my_following = $conn->query("SELECT COUNT(*) as c FROM user_follows WHERE follower_id=$my_uid")->fetch_assoc()['c'];
        $my_posts = $conn->query("SELECT COUNT(*) as c FROM social_posts WHERE user_id=$my_uid")->fetch_assoc()['c'];
        ?>
        <div class="people-container">

            <!-- My Profile Banner -->
            <div class="my-profile-banner">
                <div class="my-profile-banner-avatar">
                    <?php if (!empty($my_profile['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($my_profile['avatar_url']); ?>" alt="">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="my-profile-banner-info">
                    <h3><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                    <p><?php echo !empty($my_profile['bio']) ? htmlspecialchars(substr($my_profile['bio'],0,80)) : 'No bio yet — add one to your profile'; ?></p>
                    <div class="profile-stats-row">
                        <div class="profile-stat"><strong><?php echo $my_posts; ?></strong><small>Posts</small></div>
                        <div class="profile-stat"><strong><?php echo $my_followers; ?></strong><small>Followers</small></div>
                        <div class="profile-stat"><strong><?php echo $my_following; ?></strong><small>Following</small></div>
                    </div>
                </div>
                <a href="profile.php?id=<?php echo $my_uid; ?>" class="btn-edit-profile">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
            </div>

            <!-- People Grid -->
            <?php
            $people_sql = "SELECT u.id, u.fullname, u.membership_type,
                                   p.bio, p.avatar_url, p.cover_url,
                                   (SELECT COUNT(*) FROM user_follows WHERE following_id=u.id) AS followers,
                                   (SELECT COUNT(*) FROM user_follows WHERE follower_id=u.id) AS following_count,
                                   (SELECT COUNT(*) FROM social_posts WHERE user_id=u.id) AS post_count,
                                   (SELECT COUNT(*) FROM user_follows WHERE follower_id=? AND following_id=u.id) AS i_follow
                           FROM users u
                           LEFT JOIN user_profiles p ON p.user_id=u.id
                           WHERE u.id != ?
                           ORDER BY followers DESC, u.created_at DESC";
            $ps = $conn->prepare($people_sql);
            $ps->bind_param("ii", $my_uid, $my_uid);
            $ps->execute();
            $people_res = $ps->get_result();
            $people_count = $people_res->num_rows;
            ?>

            <div class="people-section-head">
                <h3><i class="fas fa-user-friends" style="font-size:0.9em; margin-right:6px;"></i>Community Members</h3>
                <span class="count-badge" id="member-count-badge"><?php echo $people_count; ?> members</span>
            </div>

            <!-- Search bar -->
            <div class="people-search-wrap">
                <i class="fas fa-search people-search-icon"></i>
                <input
                    type="text"
                    id="people-search-input"
                    class="people-search-input"
                    placeholder="Search members by name or bio..."
                    oninput="searchPeople(this.value)"
                    autocomplete="off"
                >
                <button class="search-clear-btn" id="search-clear-btn" onclick="clearSearch()" title="Clear search">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <?php if ($people_count === 0): ?>
                <div class="no-people">
                    <i class="fas fa-users"></i>
                    <p>No other members yet. Invite friends to grow the community!</p>
                </div>
            <?php else: ?>
                <div id="search-no-results" class="search-no-results">
                    <i class="fas fa-user-slash"></i>
                    <p>No members found for <strong id="search-query-label"></strong></p>
                </div>
                <div class="people-grid" id="people-grid">
                    <?php while ($person = $people_res->fetch_assoc()): ?>
                    <div class="user-card" data-name="<?php echo strtolower(htmlspecialchars($person['fullname'])); ?>" data-bio="<?php echo strtolower(htmlspecialchars($person['bio'] ?? '')); ?>">
                        <!-- Cover strip -->
                        <div class="user-card-cover">
                            <?php if (!empty($person['cover_url'])): ?>
                                <img src="<?php echo htmlspecialchars($person['cover_url']); ?>" alt="">
                            <?php endif; ?>
                        </div>
                        <!-- Avatar -->
                        <div class="user-card-avatar">
                            <?php if (!empty($person['avatar_url'])): ?>
                                <img src="<?php echo htmlspecialchars($person['avatar_url']); ?>" alt="">
                            <?php else: ?>
                                <?php echo strtoupper(substr($person['fullname'],0,1)); ?>
                            <?php endif; ?>
                        </div>
                        <!-- Name & badge -->
                        <div class="user-card-name"><?php echo htmlspecialchars($person['fullname']); ?></div>
                        <?php if ($person['membership_type'] === 'premium'): ?>
                            <span class="user-card-badge badge-premium"><i class="fas fa-crown"></i> Premium</span>
                        <?php else: ?>
                            <span class="user-card-badge badge-free">Free</span>
                        <?php endif; ?>
                        <!-- Bio -->
                        <p class="user-card-bio"><?php echo !empty($person['bio']) ? htmlspecialchars($person['bio']) : 'Wise Invest member'; ?></p>
                        <!-- Stats -->
                        <div class="user-card-stats">
                            <div class="ucs"><strong><?php echo intval($person['post_count']); ?></strong><small>Posts</small></div>
                            <div class="ucs"><strong><?php echo intval($person['followers']); ?></strong><small>Followers</small></div>
                            <div class="ucs"><strong><?php echo intval($person['following_count']); ?></strong><small>Following</small></div>
                        </div>
                        <!-- Actions -->
                        <div class="user-card-actions">
                            <?php if ($person['i_follow']): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="unfollow_user">
                                    <input type="hidden" name="target_id" value="<?php echo $person['id']; ?>">
                                    <button type="submit" class="btn-unfollow"><i class="fas fa-user-minus"></i> Unfollow</button>
                                </form>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="follow_user">
                                    <input type="hidden" name="target_id" value="<?php echo $person['id']; ?>">
                                    <button type="submit" class="btn-follow"><i class="fas fa-user-plus"></i> Follow</button>
                                </form>
                            <?php endif; ?>
                            <a href="profile.php?id=<?php echo $person['id']; ?>" class="btn-view-profile">
                                <i class="fas fa-eye"></i> Profile
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<script>
// ---- TITLE INPUT: live preview ----
function onTitleInput(el) {
    var preview = document.getElementById('title-live-preview');
    if (!preview) return;
    var v = el.value.trim();
    if (v) {
        preview.textContent = '✦ "' + v + '"';
        preview.classList.add('show');
    } else {
        preview.textContent = '';
        preview.classList.remove('show');
    }
}

// ---- POST TEXTAREA: auto-resize + char counter ----
function onPostInput(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 340) + 'px';
    var len = el.value.length;
    var counter = document.getElementById('char-counter');
    if (!counter) return;
    counter.textContent = len + ' / 2000';
    counter.className = 'char-counter' + (len > 1800 ? ' limit' : len > 1400 ? ' warn' : '');
}

// ---- YOUTUBE TOGGLE ----
function toggleYouTube() {
    var ef = document.getElementById('extra-fields');
    if (!ef) return;
    var showing = ef.style.display !== 'none';
    ef.style.display = showing ? 'none' : 'block';
    if (!showing) ef.querySelector('input').focus();
}

// ---- IMAGE PREVIEW ----
function previewImage(input) {
    var preview = document.getElementById('img-preview');
    var img     = document.getElementById('preview-src');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

// ---- TAB SWITCHER ----
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    const tab = document.getElementById('tab-' + tabName);
    if (tab) tab.classList.add('active');
    const btn = document.querySelector('.tab-btn[data-tab="' + tabName + '"]');
    if (btn) btn.classList.add('active');
    if (tabName === 'chat') fetchMessages();
    if (tabName === 'people') animatePeopleCards();
}

// ---- PEOPLE TAB: CARD ENTRANCE ANIMATIONS ----
function animatePeopleCards() {
    const cards = document.querySelectorAll('.people-grid .user-card:not(.search-hidden)');
    cards.forEach((card, i) => {
        card.classList.remove('card-anim');
        void card.offsetWidth; // force reflow to restart animation
        card.style.animationDelay = (i * 0.07) + 's';
        card.classList.add('card-anim');
    });
}

// ---- PEOPLE SEARCH ----
function searchPeople(query) {
    const q = query.toLowerCase().trim();
    const cards = document.querySelectorAll('.people-grid .user-card');
    const clearBtn = document.getElementById('search-clear-btn');
    const noResults = document.getElementById('search-no-results');
    const badge = document.getElementById('member-count-badge');
    const label = document.getElementById('search-query-label');

    if (clearBtn) clearBtn.classList.toggle('visible', q.length > 0);

    let visible = 0;
    cards.forEach(card => {
        const name = card.dataset.name || '';
        const bio  = card.dataset.bio  || '';
        const match = !q || name.includes(q) || bio.includes(q);
        if (match) {
            card.classList.remove('search-hidden');
            visible++;
        } else {
            card.classList.add('search-hidden');
        }
    });

    if (noResults) {
        noResults.style.display = (visible === 0 && q) ? 'block' : 'none';
        if (label) label.textContent = '"' + query + '"';
    }
    if (badge) badge.textContent = (q ? visible : cards.length) + ' members';

    // Re-animate visible cards after filter
    if (q) {
        const visibleCards = document.querySelectorAll('.people-grid .user-card:not(.search-hidden)');
        visibleCards.forEach((card, i) => {
            card.classList.remove('card-anim');
            void card.offsetWidth;
            card.style.animationDelay = (i * 0.06) + 's';
            card.classList.add('card-anim');
        });
    }
}

function clearSearch() {
    const input = document.getElementById('people-search-input');
    if (input) { input.value = ''; searchPeople(''); input.focus(); }
}

// Trigger card animations on initial load if People tab is active
if ('<?php echo $active_tab; ?>' === 'people') {
    window.addEventListener('DOMContentLoaded', () => setTimeout(animatePeopleCards, 120));
}

// ---- SEND REACTION (optimistic UI) ----
function sendReaction(btn, postId, type) {
    const allBtns = btn.parentNode.querySelectorAll('.react-btn');
    allBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const fd = new FormData();
    fd.append('action', 'react');
    fd.append('post_id', postId);
    fd.append('type', type);

    btn.style.transform = 'scale(1.25)';
    setTimeout(() => btn.style.transform = '', 300);

    fetch('community.php', { method: 'POST', body: fd })
        .then(r => r.text())
        .then(d => { if (d.trim() === 'success') location.reload(); });
}

// ---- POST MENU TOGGLES ----
function toggleMenu(id) {
    const menu = document.getElementById('menu-' + id);
    document.querySelectorAll('.opt-menu').forEach(m => { if (m !== menu) m.classList.remove('show'); });
    menu.classList.toggle('show');
}

document.addEventListener('click', e => {
    if (!e.target.closest('.post-options')) {
        document.querySelectorAll('.opt-menu.show').forEach(m => m.classList.remove('show'));
    }
});

// ---- EDIT MODE ----
function enableEdit(id) {
    document.getElementById('content-view-' + id).style.display = 'none';
    document.getElementById('content-edit-' + id).style.display = 'block';
    const ta = document.querySelector('#content-edit-' + id + ' textarea');
    if (ta) { ta.focus(); ta.setSelectionRange(ta.value.length, ta.value.length); }
}
function cancelEdit(id) {
    document.getElementById('content-view-' + id).style.display = 'block';
    document.getElementById('content-edit-' + id).style.display = 'none';
}

// ---- CHAT ----
const chatBox   = document.getElementById('chat-box');
const chatForm  = document.getElementById('chat-form');
const msgInput  = document.getElementById('msg-input');
const currentUser = "<?php echo addslashes(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''); ?>";
let lastMsgCount = 0;

function fetchMessages() {
    if (!document.getElementById('tab-chat').classList.contains('active')) return;
    fetch('chat_api.php?action=fetch')
        .then(r => r.json())
        .then(data => {
            if (data.length === lastMsgCount) return;
            lastMsgCount = data.length;
            const wasAtBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 60;
            chatBox.innerHTML = '';
            data.forEach(msg => {
                const isMe   = msg.user === currentUser;
                let display  = msg.user;
                if (msg.type === 'premium') display += ' <i class="fas fa-crown" style="color:#d4af37;font-size:0.75em;"></i>';
                let content  = msg.msg.replace(/</g,'&lt;').replace(/>/g,'&gt;')
                                       .replace(/@(\w+)/g, '<span class="mention-highlight">@$1</span>');
                let cls = 'msg-bubble';
                if (msg.msg.toLowerCase().includes('@' + currentUser.toLowerCase())) cls += ' mentioned-me';
                chatBox.innerHTML += `
                    <div class="msg-row${isMe?' me':''}">
                        <small style="color:${isMe?'#38bdf8':'#64748b'};margin:0 6px;font-size:0.7rem;letter-spacing:0.3px;">${display}</small>
                        <div class="${cls}">${content}</div>
                    </div>`;
            });
            if (wasAtBottom) chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(() => {});
}

if (chatForm) {
    chatForm.addEventListener('submit', e => {
        e.preventDefault();
        const text = msgInput.value.trim();
        if (!text) return;
        const fd = new FormData();
        fd.append('message', text);
        msgInput.value = '';
        fetch('chat_api.php?action=send', { method: 'POST', body: fd })
            .then(() => { fetchMessages(); setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 150); });
    });
    msgInput.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); chatForm.dispatchEvent(new Event('submit')); }
    });
}
setInterval(fetchMessages, 3000);

// ---- STAT COUNTER ANIMATION ----
document.querySelectorAll('.stat-num').forEach(el => {
    const target = parseInt(el.textContent.replace(/\D/g, ''), 10);
    if (!isNaN(target) && target > 0) {
        let cur = 0;
        const step = Math.max(1, Math.ceil(target / 60));
        const t = setInterval(() => {
            cur = Math.min(cur + step, target);
            el.textContent = cur.toLocaleString();
            if (cur >= target) clearInterval(t);
        }, 18);
    }
});

// ---- SCROLL-REVEAL for posts ----
if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.post-card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(22px)';
        card.style.transition = `opacity 0.42s ease ${i * 0.07}s, transform 0.42s ease ${i * 0.07}s`;
        io.observe(card);
    });
}
</script>

<?php include 'includes/footer.php'; ?>