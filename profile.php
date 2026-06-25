<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please log in to view profiles.';
    echo "<script>window.location.href='login.php';</script>"; exit();
}

// Auto-create profile tables (safe no-op if already exist)
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

$me = intval($_SESSION['user_id']);
$view_id = isset($_GET['id']) ? intval($_GET['id']) : $me;

// Load target user
$us = $conn->prepare("SELECT id, fullname, email, membership_type, created_at FROM users WHERE id=?");
$us->bind_param("i", $view_id);
$us->execute();
$user = $us->get_result()->fetch_assoc();
if (!$user) { echo "<script>window.location.href='community.php';</script>"; exit(); }

$is_own = ($view_id === $me);

// --- POST HANDLERS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Follow
    if ($_POST['action'] === 'follow_user') {
        $target = intval($_POST['target_id']);
        if ($target !== $me) {
            $s = $conn->prepare("INSERT IGNORE INTO user_follows (follower_id, following_id) VALUES (?, ?)");
            $s->bind_param("ii", $me, $target);
            $s->execute();
        }
        echo "<script>window.location.href='profile.php?id=$target';</script>"; exit();
    }

    // Unfollow
    if ($_POST['action'] === 'unfollow_user') {
        $target = intval($_POST['target_id']);
        $s = $conn->prepare("DELETE FROM user_follows WHERE follower_id=? AND following_id=?");
        $s->bind_param("ii", $me, $target);
        $s->execute();
        echo "<script>window.location.href='profile.php?id=$target';</script>"; exit();
    }

    // Update bio/location/website
    if ($_POST['action'] === 'update_profile' && $is_own) {
        $bio      = substr(trim($_POST['bio'] ?? ''), 0, 300);
        $location = substr(trim($_POST['location'] ?? ''), 0, 100);
        $website  = substr(trim($_POST['website'] ?? ''), 0, 200);
        $s = $conn->prepare("INSERT INTO user_profiles (user_id, bio, location, website) VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE bio=VALUES(bio), location=VALUES(location), website=VALUES(website)");
        $s->bind_param("isss", $me, $bio, $location, $website);
        $s->execute();
        echo "<script>window.location.href='profile.php?id=$me&saved=1';</script>"; exit();
    }

    // Upload avatar
    if ($_POST['action'] === 'upload_avatar' && $is_own) {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $dir = "uploads/avatars/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $ext  = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $valid = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $valid) && $_FILES['avatar']['size'] < 5*1024*1024) {
                $fname = "av_{$me}_" . time() . ".$ext";
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $fname)) {
                    $url = $dir . $fname;
                    $s = $conn->prepare("INSERT INTO user_profiles (user_id, avatar_url) VALUES (?, ?)
                                        ON DUPLICATE KEY UPDATE avatar_url=VALUES(avatar_url)");
                    $s->bind_param("is", $me, $url);
                    $s->execute();
                }
            }
        }
        echo "<script>window.location.href='profile.php?id=$me&uploaded=avatar';</script>"; exit();
    }

    // Upload cover
    if ($_POST['action'] === 'upload_cover' && $is_own) {
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $dir = "uploads/covers/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $ext  = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            $valid = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $valid) && $_FILES['cover']['size'] < 8*1024*1024) {
                $fname = "cov_{$me}_" . time() . ".$ext";
                if (move_uploaded_file($_FILES['cover']['tmp_name'], $dir . $fname)) {
                    $url = $dir . $fname;
                    $s = $conn->prepare("INSERT INTO user_profiles (user_id, cover_url) VALUES (?, ?)
                                        ON DUPLICATE KEY UPDATE cover_url=VALUES(cover_url)");
                    $s->bind_param("is", $me, $url);
                    $s->execute();
                }
            }
        }
        echo "<script>window.location.href='profile.php?id=$me&uploaded=cover';</script>"; exit();
    }
}

// --- LOAD PROFILE DATA ---
$pp = $conn->query("SELECT * FROM user_profiles WHERE user_id=$view_id");
$profile = ($pp && $pp->num_rows > 0) ? $pp->fetch_assoc() : [];

$followers_count = intval($conn->query("SELECT COUNT(*) as c FROM user_follows WHERE following_id=$view_id")->fetch_assoc()['c']);
$following_count = intval($conn->query("SELECT COUNT(*) as c FROM user_follows WHERE follower_id=$view_id")->fetch_assoc()['c']);
$posts_count     = intval($conn->query("SELECT COUNT(*) as c FROM social_posts WHERE user_id=$view_id")->fetch_assoc()['c']);
$i_follow        = intval($conn->query("SELECT COUNT(*) as c FROM user_follows WHERE follower_id=$me AND following_id=$view_id")->fetch_assoc()['c']);

// --- FOLLOWERS LIST (for modal) ---
$followers_list  = $conn->query("SELECT u.id, u.fullname, p.avatar_url, p.bio FROM user_follows f JOIN users u ON u.id=f.follower_id LEFT JOIN user_profiles p ON p.user_id=u.id WHERE f.following_id=$view_id ORDER BY f.created_at DESC LIMIT 50");
$following_list  = $conn->query("SELECT u.id, u.fullname, p.avatar_url, p.bio FROM user_follows f JOIN users u ON u.id=f.following_id LEFT JOIN user_profiles p ON p.user_id=u.id WHERE f.follower_id=$view_id ORDER BY f.created_at DESC LIMIT 50");

// --- POSTS ---
$posts_sql = "SELECT p.*, u.fullname FROM social_posts p JOIN users u ON p.user_id=u.id WHERE p.user_id=$view_id ORDER BY p.created_at DESC LIMIT 20";
$posts_res = $conn->query($posts_sql);
?>

<style>
/* ============================================================
   PROFILE PAGE — WISE INVEST DARK FINTECH
============================================================ */
*, *::before, *::after { box-sizing: border-box; }

.profile-wrapper {
    min-height: calc(100vh - 90px);
    padding: 0 0 80px;
    color: #e2e8f0;
    background:
        radial-gradient(ellipse at 15% 0%, rgba(56,189,248,0.09), transparent 42%),
        radial-gradient(ellipse at 85% 10%, rgba(34,211,238,0.06), transparent 42%),
        linear-gradient(180deg, #020617 0%, #030712 100%);
    position: relative;
}
.profile-wrapper::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(rgba(56,189,248,0.018) 1px, transparent 1px),
        linear-gradient(90deg, rgba(56,189,248,0.018) 1px, transparent 1px);
    background-size: 64px 64px;
    pointer-events: none;
    z-index: 0;
}
.profile-wrapper > * { position: relative; z-index: 1; }

/* ---- Cover ---- */
.profile-cover {
    width: 100%;
    height: 240px;
    background: linear-gradient(135deg, rgba(56,189,248,0.12), rgba(212,175,55,0.08), rgba(34,211,238,0.10));
    position: relative;
    overflow: hidden;
}
.profile-cover img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.cover-upload-btn {
    position: absolute;
    bottom: 14px; right: 18px;
    background: rgba(2,6,23,0.75);
    border: 1px solid rgba(56,189,248,0.35);
    color: #38bdf8;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    backdrop-filter: blur(8px);
    transition: all 0.22s;
}
.cover-upload-btn:hover { background: rgba(56,189,248,0.12); }
.cover-upload-btn input { display: none; }

/* ---- Profile info row ---- */
.profile-info-row {
    max-width: 880px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: flex-end;
    gap: 24px;
    margin-top: -48px;
    position: relative;
    z-index: 2;
}
.profile-avatar-wrap {
    position: relative;
    flex-shrink: 0;
}
.profile-avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8, #22d3ee);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.8rem;
    font-weight: 800;
    color: #020617;
    border: 4px solid #020617;
    box-shadow: 0 0 0 3px rgba(56,189,248,0.38);
    overflow: hidden;
}
.profile-avatar img { width:100%; height:100%; object-fit:cover; }
.avatar-upload-btn {
    position: absolute;
    bottom: 4px; right: 4px;
    width: 30px; height: 30px;
    background: linear-gradient(135deg,#38bdf8,#22d3ee);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    border: 2px solid #020617;
    transition: transform 0.2s;
}
.avatar-upload-btn:hover { transform: scale(1.15); }
.avatar-upload-btn i { color:#020617; font-size:0.72rem; pointer-events:none; }
.avatar-upload-btn input { display:none; }

.profile-meta { flex: 1; padding-bottom: 10px; }
.profile-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0 0 4px;
}
.profile-tag { color: #64748b; font-size: 0.85rem; margin-bottom: 8px; }
.profile-badge-row { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.p-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 0.72rem; font-weight: 700; padding: 3px 10px;
    border-radius: 20px; letter-spacing: 0.3px;
}
.p-badge-premium { background:rgba(212,175,55,0.14); color:#d4af37; border:1px solid rgba(212,175,55,0.3); }
.p-badge-free { background:rgba(100,116,139,0.14); color:#94a3b8; border:1px solid rgba(100,116,139,0.25); }
.p-badge-member { background:rgba(56,189,248,0.08); color:#38bdf8; border:1px solid rgba(56,189,248,0.18); }

.profile-actions { display:flex; gap:10px; align-items:flex-end; padding-bottom:10px; flex-shrink:0; }
.btn-follow-profile, .btn-unfollow-profile, .btn-edit-profile-page, .btn-message-profile {
    padding: 10px 22px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
}
.btn-follow-profile {
    background: linear-gradient(135deg,#38bdf8,#22d3ee);
    color: #020617;
    box-shadow: 0 0 18px rgba(56,189,248,0.32);
}
.btn-follow-profile:hover { transform:translateY(-2px); box-shadow:0 0 28px rgba(56,189,248,0.55); }
.btn-unfollow-profile {
    background: rgba(100,116,139,0.14);
    color: #94a3b8;
    border: 1px solid rgba(100,116,139,0.3);
}
.btn-unfollow-profile:hover { background:rgba(239,68,68,0.12); color:#f87171; border-color:rgba(239,68,68,0.35); }
.btn-edit-profile-page {
    background: rgba(56,189,248,0.10);
    color: #38bdf8;
    border: 1px solid rgba(56,189,248,0.28);
}
.btn-edit-profile-page:hover { background:rgba(56,189,248,0.18); transform:translateY(-1px); }

/* ---- Profile body ---- */
.profile-body {
    max-width: 880px;
    margin: 28px auto 0;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 28px;
    align-items: start;
}
@media(max-width: 720px) {
    .profile-body { grid-template-columns: 1fr; }
    .profile-info-row { flex-wrap: wrap; }
}

/* ---- Sidebar card ---- */
.profile-sidebar .p-card {
    background: rgba(15,23,42,0.82);
    backdrop-filter: blur(18px);
    border: 1px solid rgba(56,189,248,0.14);
    border-radius: 18px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 14px 40px rgba(0,0,0,0.40);
}
.p-card-title {
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #38bdf8;
    margin: 0 0 16px;
}
.p-bio-text { color: #cbd5e1; line-height: 1.7; font-size: 0.9rem; margin: 0; }
.p-detail-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    color: #94a3b8;
    font-size: 0.87rem;
}
.p-detail-row i { color: #38bdf8; width: 16px; }
.p-detail-row a { color: #38bdf8; text-decoration: none; }
.p-detail-row a:hover { text-decoration: underline; }

/* Stats row */
.p-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.p-stat-box {
    text-align: center;
    background: rgba(56,189,248,0.05);
    border: 1px solid rgba(56,189,248,0.10);
    border-radius: 12px;
    padding: 14px 8px;
    cursor: pointer;
    transition: background 0.2s;
}
.p-stat-box:hover { background: rgba(56,189,248,0.10); }
.p-stat-box strong { display:block; font-size:1.3rem; color:#38bdf8; font-weight:800; }
.p-stat-box small { color:#64748b; font-size:0.75rem; }

/* ---- Posts feed ---- */
.profile-posts { }
.profile-posts-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.profile-posts-head h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    background: linear-gradient(135deg,#f1f5f9,#38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Post card (reuse community style) */
.p-post-card {
    background: rgba(15,23,42,0.82);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(56,189,248,0.12);
    border-radius: 18px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 12px 36px rgba(0,0,0,0.40);
    transition: box-shadow 0.28s, border-color 0.28s;
}
.p-post-card:hover {
    border-color: rgba(56,189,248,0.28);
    box-shadow: 0 0 0 1px rgba(56,189,248,0.14), 0 20px 48px rgba(0,0,0,0.55);
}
.p-post-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}
.p-post-av {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg,#38bdf8,#22d3ee);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; color: #020617; font-size: 0.95rem;
    flex-shrink: 0; overflow: hidden;
}
.p-post-av img { width:100%; height:100%; object-fit:cover; }
.p-post-info h4 { margin:0 0 2px; font-size:0.95rem; color:#f1f5f9; }
.p-post-info span { font-size:0.75rem; color:#64748b; }
.p-post-content { color:#cbd5e1; line-height:1.7; font-size:0.9rem; white-space:pre-wrap; word-break:break-word; }
.p-post-img { margin-top:14px; border-radius:12px; overflow:hidden; max-height:400px; }
.p-post-img img { width:100%; object-fit:cover; }
.p-post-video { margin-top:14px; border-radius:12px; overflow:hidden; position:relative; padding-top:56.25%; }
.p-post-video iframe { position:absolute; top:0;left:0;width:100%;height:100%; border:none; }

.empty-posts {
    text-align:center; padding:60px 20px;
}
.empty-posts i { font-size:42px; color:#38bdf8; opacity:0.22; display:block; margin-bottom:14px; }
.empty-posts p { color:#64748b; }

/* ---- Edit profile modal ---- */
.edit-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(2,6,23,0.85);
    backdrop-filter: blur(8px);
    z-index: 9998;
    align-items: center;
    justify-content: center;
}
.edit-modal-overlay.open { display: flex; }
.edit-modal {
    background: rgba(15,23,42,0.98);
    border: 1px solid rgba(56,189,248,0.25);
    border-radius: 22px;
    width: 92%;
    max-width: 480px;
    box-shadow: 0 40px 90px rgba(0,0,0,0.75);
    overflow: hidden;
    position: relative;
}
.edit-modal::before {
    content:"";
    position:absolute;
    top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,#d4af37,#38bdf8,#22d3ee,#d4af37);
    background-size:200% 100%;
    animation:shimmer 4s linear infinite;
}
@keyframes shimmer {
    0%{background-position:0% 0%;}
    100%{background-position:200% 0%;}
}
.edit-modal-head {
    padding: 22px 24px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(56,189,248,0.10);
}
.edit-modal-head h3 {
    margin: 0;
    font-family: 'Playfair Display', serif;
    background: linear-gradient(135deg,#f1f5f9,#38bdf8);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}
.modal-close-btn {
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    font-size: 1.1rem;
    transition: color 0.2s;
}
.modal-close-btn:hover { color: #e2e8f0; }
.edit-modal-body { padding: 24px; }
.em-field { margin-bottom: 18px; }
.em-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    margin-bottom: 7px;
}
.em-input, .em-textarea {
    width: 100%;
    padding: 12px 16px;
    background: rgba(2,6,23,0.70);
    border: 1px solid rgba(56,189,248,0.18);
    border-radius: 10px;
    color: #e2e8f0;
    font-family: inherit;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.22s, box-shadow 0.22s;
}
.em-input:focus, .em-textarea:focus {
    border-color: rgba(56,189,248,0.50);
    box-shadow: 0 0 0 3px rgba(56,189,248,0.10);
}
.em-textarea { resize: vertical; min-height: 90px; }
.em-save-btn {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg,#38bdf8,#22d3ee);
    color: #020617;
    border: none;
    border-radius: 50px;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.25s;
    box-shadow: 0 0 18px rgba(56,189,248,0.30);
    margin-top: 8px;
}
.em-save-btn:hover { transform:translateY(-2px); box-shadow:0 0 28px rgba(56,189,248,0.55); }

/* FF modals */
.ff-modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(2,6,23,0.82); backdrop-filter:blur(6px);
    z-index:9999; align-items:center; justify-content:center;
}
.ff-modal-overlay.open { display:flex; }
.ff-modal {
    background:rgba(15,23,42,0.98);
    border:1px solid rgba(56,189,248,0.22);
    border-radius:20px; width:92%; max-width:400px;
    max-height:80vh; overflow:hidden; display:flex; flex-direction:column;
    box-shadow:0 32px 80px rgba(0,0,0,0.7);
}
.ff-modal-head {
    padding:20px 24px;
    border-bottom:1px solid rgba(56,189,248,0.1);
    display:flex; align-items:center; justify-content:space-between;
}
.ff-modal-head h3 { margin:0; color:#f1f5f9; font-size:1rem; }
.ff-modal-close { background:none; border:none; color:#64748b; cursor:pointer; font-size:1.1rem; }
.ff-modal-close:hover { color:#e2e8f0; }
.ff-modal-list { overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; }
.ff-user-row {
    display:flex; align-items:center; gap:12px; padding:10px 12px;
    border-radius:12px; background:rgba(56,189,248,0.04);
    border:1px solid rgba(56,189,248,0.08); text-decoration:none; transition:background 0.2s;
}
.ff-user-row:hover { background:rgba(56,189,248,0.10); }
.ff-avatar {
    width:40px; height:40px; border-radius:50%;
    background:linear-gradient(135deg,#38bdf8,#22d3ee);
    display:flex; align-items:center; justify-content:center;
    font-size:0.95rem; font-weight:800; color:#020617; flex-shrink:0; overflow:hidden;
}
.ff-avatar img { width:100%;height:100%;object-fit:cover; }
.ff-user-name { color:#f1f5f9; font-weight:600; font-size:0.9rem; }
.ff-user-bio { color:#64748b; font-size:0.76rem; }
.ff-empty { text-align:center; color:#64748b; padding:30px; font-size:0.9rem; }

/* Flash notice */
.profile-flash {
    max-width:880px; margin:20px auto 0; padding:0 24px;
}
.flash-saved {
    background:rgba(56,189,248,0.10); border:1px solid rgba(56,189,248,0.28);
    color:#38bdf8; padding:12px 18px; border-radius:10px; font-size:0.87rem; font-weight:600;
}

@media(max-width:560px) {
    .profile-cover { height:160px; }
    .profile-avatar { width:80px; height:80px; font-size:2rem; }
    .profile-info-row { margin-top:-36px; gap:14px; }
    .profile-name { font-size:1.2rem; }
    .profile-actions { flex-wrap:wrap; }
}

/* ============ PROFILE PAGE ANIMATIONS ============ */

/* Cover slides down from above */
@keyframes coverReveal {
    from { opacity:0; transform:scaleY(0.85) translateY(-20px); }
    to   { opacity:1; transform:scaleY(1) translateY(0); }
}
.profile-cover {
    animation: coverReveal 0.6s cubic-bezier(0.22,1,0.36,1) both;
    transform-origin: top center;
}

/* Avatar pops in with spring */
@keyframes avatarPop {
    0%   { opacity:0; transform:scale(0.55); }
    70%  { transform:scale(1.08); }
    100% { opacity:1; transform:scale(1); }
}
.profile-avatar {
    animation: avatarPop 0.55s cubic-bezier(0.34,1.56,0.64,1) 0.35s both;
}

/* Info row slides up */
@keyframes infoSlideUp {
    from { opacity:0; transform:translateY(18px); }
    to   { opacity:1; transform:translateY(0); }
}
.profile-meta {
    animation: infoSlideUp 0.5s ease 0.45s both;
}
.profile-actions {
    animation: infoSlideUp 0.5s ease 0.55s both;
}

/* Sidebar slides in from left */
@keyframes sidebarIn {
    from { opacity:0; transform:translateX(-22px); }
    to   { opacity:1; transform:translateX(0); }
}
.profile-sidebar {
    animation: sidebarIn 0.55s ease 0.55s both;
}

/* Posts stagger in from below */
@keyframes postIn {
    from { opacity:0; transform:translateY(22px); }
    to   { opacity:1; transform:translateY(0); }
}
.p-post-card {
    animation: postIn 0.48s ease both;
}

/* Flash notice pulse */
@keyframes flashPulse {
    0%,100% { box-shadow:0 0 0 0 rgba(56,189,248,0); }
    50%     { box-shadow:0 0 0 5px rgba(56,189,248,0.15); }
}
.flash-saved {
    animation: flashPulse 1.8s ease 0.3s 2;
}

/* Follow button pulse effect on hover */
.btn-follow-profile {
    position: relative;
    overflow: hidden;
}
.btn-follow-profile::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.15);
    border-radius: inherit;
    opacity: 0;
    transform: scale(0);
    transition: opacity 0.4s, transform 0.4s;
}
.btn-follow-profile:active::after {
    opacity: 1;
    transform: scale(1);
    transition: 0s;
}

/* Stat box hover glow */
.p-stat-box {
    transition: background 0.22s, box-shadow 0.22s, transform 0.22s;
}
.p-stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 18px rgba(56,189,248,0.18);
}
</style>

<div class="profile-wrapper">

<?php if (isset($_GET['saved'])): ?>
<div class="profile-flash">
    <div class="flash-saved"><i class="fas fa-check-circle" style="margin-right:6px;"></i>Profile updated successfully!</div>
</div>
<?php endif; ?>

<!-- ===== COVER PHOTO ===== -->
<div class="profile-cover">
    <?php if (!empty($profile['cover_url'])): ?>
        <img src="<?php echo htmlspecialchars($profile['cover_url']); ?>" alt="Cover">
    <?php endif; ?>
    <?php if ($is_own): ?>
    <form method="POST" enctype="multipart/form-data" id="cover-form">
        <input type="hidden" name="action" value="upload_cover">
        <label class="cover-upload-btn" title="Change cover photo">
            <i class="fas fa-camera"></i> <?php echo empty($profile['cover_url']) ? 'Add Cover Photo' : 'Change Cover'; ?>
            <input type="file" name="cover" accept="image/*" onchange="document.getElementById('cover-form').submit();">
        </label>
    </form>
    <?php endif; ?>
</div>

<!-- ===== PROFILE INFO ROW ===== -->
<div class="profile-info-row">
    <!-- Avatar -->
    <div class="profile-avatar-wrap">
        <div class="profile-avatar">
            <?php if (!empty($profile['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($profile['avatar_url']); ?>" alt="Avatar">
            <?php else: ?>
                <?php echo strtoupper(substr($user['fullname'],0,1)); ?>
            <?php endif; ?>
        </div>
        <?php if ($is_own): ?>
        <form method="POST" enctype="multipart/form-data" id="avatar-form">
            <input type="hidden" name="action" value="upload_avatar">
            <label class="avatar-upload-btn" title="Change profile photo">
                <i class="fas fa-camera"></i>
                <input type="file" name="avatar" accept="image/*" onchange="document.getElementById('avatar-form').submit();">
            </label>
        </form>
        <?php endif; ?>
    </div>

    <!-- Name & info -->
    <div class="profile-meta">
        <h1 class="profile-name"><?php echo htmlspecialchars($user['fullname']); ?></h1>
        <p class="profile-tag">@<?php echo strtolower(str_replace(' ','_',htmlspecialchars($user['fullname']))); ?></p>
        <div class="profile-badge-row">
            <?php if ($user['membership_type'] === 'premium'): ?>
                <span class="p-badge p-badge-premium"><i class="fas fa-crown"></i> Premium</span>
            <?php else: ?>
                <span class="p-badge p-badge-free">Free Member</span>
            <?php endif; ?>
            <span class="p-badge p-badge-member"><i class="fas fa-calendar-alt"></i> Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
        </div>
    </div>

    <!-- Action buttons -->
    <div class="profile-actions">
        <?php if ($is_own): ?>
            <button class="btn-edit-profile-page" onclick="openEditModal()">
                <i class="fas fa-pen"></i> Edit Profile
            </button>
        <?php else: ?>
            <?php if ($i_follow): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="unfollow_user">
                    <input type="hidden" name="target_id" value="<?php echo $view_id; ?>">
                    <button type="submit" class="btn-unfollow-profile"><i class="fas fa-user-check"></i> Following</button>
                </form>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="follow_user">
                    <input type="hidden" name="target_id" value="<?php echo $view_id; ?>">
                    <button type="submit" class="btn-follow-profile"><i class="fas fa-user-plus"></i> Follow</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ===== PROFILE BODY ===== -->
<div class="profile-body">

    <!-- Sidebar -->
    <div class="profile-sidebar">

        <!-- Stats -->
        <div class="p-card">
            <p class="p-card-title"><i class="fas fa-chart-bar" style="margin-right:6px;"></i>Activity</p>
            <div class="p-stats-grid">
                <div class="p-stat-box">
                    <strong><?php echo $posts_count; ?></strong>
                    <small>Posts</small>
                </div>
                <div class="p-stat-box" onclick="openFFModal('followers')">
                    <strong><?php echo $followers_count; ?></strong>
                    <small>Followers</small>
                </div>
                <div class="p-stat-box" onclick="openFFModal('following')">
                    <strong><?php echo $following_count; ?></strong>
                    <small>Following</small>
                </div>
            </div>
        </div>

        <!-- Bio card -->
        <div class="p-card">
            <p class="p-card-title"><i class="fas fa-user" style="margin-right:6px;"></i>About</p>
            <?php if (!empty($profile['bio'])): ?>
                <p class="p-bio-text"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
            <?php else: ?>
                <p class="p-bio-text" style="color:#475569; font-style:italic;">
                    <?php echo $is_own ? 'No bio yet — click Edit Profile to add one.' : 'No bio added yet.'; ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($profile['location']) || !empty($profile['website'])): ?>
            <div style="margin-top:16px; border-top:1px solid rgba(56,189,248,0.08); padding-top:14px;">
                <?php if (!empty($profile['location'])): ?>
                <div class="p-detail-row">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($profile['location']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['website'])): ?>
                <div class="p-detail-row">
                    <i class="fas fa-link"></i>
                    <a href="<?php echo htmlspecialchars($profile['website']); ?>" target="_blank" rel="noopener">
                        <?php echo htmlspecialchars(preg_replace('#^https?://#','',$profile['website'])); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Back link -->
        <a href="community.php?tab=people" style="display:inline-flex;align-items:center;gap:7px;color:#64748b;font-size:0.85rem;text-decoration:none;transition:color 0.2s;">
            <i class="fas fa-arrow-left"></i> Back to Community
        </a>
    </div>

    <!-- Posts -->
    <div class="profile-posts">
        <div class="profile-posts-head">
            <h3><i class="fas fa-th-list" style="font-size:0.85em;margin-right:8px;"></i>Posts</h3>
        </div>

        <?php if (!$posts_res || $posts_res->num_rows === 0): ?>
            <div class="empty-posts">
                <i class="fas fa-pen-nib"></i>
                <p><?php echo $is_own ? "You haven't posted anything yet." : "No posts yet."; ?></p>
            </div>
        <?php else: ?>
            <?php while ($post = $posts_res->fetch_assoc()): ?>
            <div class="p-post-card">
                <div class="p-post-header">
                    <div class="p-post-av">
                        <?php if (!empty($profile['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($profile['avatar_url']); ?>" alt="">
                        <?php else: ?>
                            <?php echo strtoupper(substr($post['fullname'],0,1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="p-post-info">
                        <h4><?php echo htmlspecialchars($post['fullname']); ?></h4>
                        <span><?php echo date('M j, Y \a\t g:i a', strtotime($post['created_at'])); ?></span>
                    </div>
                </div>
                <?php if (!empty($post['content'])): ?>
                    <div class="p-post-content"><?php echo htmlspecialchars($post['content']); ?></div>
                <?php endif; ?>
                <?php if (!empty($post['image_url'])): ?>
                    <div class="p-post-img"><img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt=""></div>
                <?php endif; ?>
                <?php if (!empty($post['video_link'])): ?>
                    <div class="p-post-video">
                        <iframe src="<?php echo htmlspecialchars($post['video_link']); ?>" allowfullscreen loading="lazy"></iframe>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</div><!-- /profile-body -->

</div><!-- /profile-wrapper -->

<!-- ===== EDIT PROFILE MODAL ===== -->
<?php if ($is_own): ?>
<div class="edit-modal-overlay" id="edit-modal-overlay">
    <div class="edit-modal">
        <div class="edit-modal-head">
            <h3>Edit Profile</h3>
            <button class="modal-close-btn" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="edit-modal-body">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="em-field">
                    <label class="em-label">Bio</label>
                    <textarea name="bio" class="em-textarea" placeholder="Tell the community about yourself..." maxlength="300"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                    <small style="color:#475569;font-size:0.76rem;">Max 300 characters</small>
                </div>
                <div class="em-field">
                    <label class="em-label">Location</label>
                    <input type="text" name="location" class="em-input" placeholder="City, Country" maxlength="100" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>">
                </div>
                <div class="em-field">
                    <label class="em-label">Website / Social Link</label>
                    <input type="url" name="website" class="em-input" placeholder="https://yoursite.com" maxlength="200" value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>">
                </div>
                <button type="submit" class="em-save-btn"><i class="fas fa-save" style="margin-right:6px;"></i>Save Changes</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== FOLLOWERS MODAL ===== -->
<div class="ff-modal-overlay" id="followers-modal">
    <div class="ff-modal">
        <div class="ff-modal-head">
            <h3>Followers</h3>
            <button class="ff-modal-close" onclick="closeFFModal('followers')"><i class="fas fa-times"></i></button>
        </div>
        <div class="ff-modal-list">
            <?php
            $followers_list->data_seek(0);
            if ($followers_list->num_rows === 0):
            ?>
                <p class="ff-empty">No followers yet.</p>
            <?php else: while ($fu = $followers_list->fetch_assoc()): ?>
                <a href="profile.php?id=<?php echo $fu['id']; ?>" class="ff-user-row">
                    <div class="ff-avatar">
                        <?php if (!empty($fu['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($fu['avatar_url']); ?>" alt="">
                        <?php else: ?>
                            <?php echo strtoupper(substr($fu['fullname'],0,1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="ff-user-name"><?php echo htmlspecialchars($fu['fullname']); ?></div>
                        <?php if (!empty($fu['bio'])): ?>
                            <div class="ff-user-bio"><?php echo htmlspecialchars(substr($fu['bio'],0,60)); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endwhile; endif; ?>
        </div>
    </div>
</div>

<!-- ===== FOLLOWING MODAL ===== -->
<div class="ff-modal-overlay" id="following-modal">
    <div class="ff-modal">
        <div class="ff-modal-head">
            <h3>Following</h3>
            <button class="ff-modal-close" onclick="closeFFModal('following')"><i class="fas fa-times"></i></button>
        </div>
        <div class="ff-modal-list">
            <?php
            $following_list->data_seek(0);
            if ($following_list->num_rows === 0):
            ?>
                <p class="ff-empty">Not following anyone yet.</p>
            <?php else: while ($fu = $following_list->fetch_assoc()): ?>
                <a href="profile.php?id=<?php echo $fu['id']; ?>" class="ff-user-row">
                    <div class="ff-avatar">
                        <?php if (!empty($fu['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($fu['avatar_url']); ?>" alt="">
                        <?php else: ?>
                            <?php echo strtoupper(substr($fu['fullname'],0,1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="ff-user-name"><?php echo htmlspecialchars($fu['fullname']); ?></div>
                        <?php if (!empty($fu['bio'])): ?>
                            <div class="ff-user-bio"><?php echo htmlspecialchars(substr($fu['bio'],0,60)); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endwhile; endif; ?>
        </div>
    </div>
</div>

<script>
function openEditModal()  { document.getElementById('edit-modal-overlay').classList.add('open'); }
function closeEditModal() { document.getElementById('edit-modal-overlay').classList.remove('open'); }
function openFFModal(type) {
    document.getElementById(type + '-modal').classList.add('open');
}
function closeFFModal(type) {
    document.getElementById(type + '-modal').classList.remove('open');
}
// Close modals on overlay click
document.querySelectorAll('.ff-modal-overlay, .edit-modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

// Stagger post animations
document.querySelectorAll('.p-post-card').forEach((card, i) => {
    card.style.animationDelay = (0.65 + i * 0.1) + 's';
});

// Auto-dismiss flash notice
const flash = document.querySelector('.flash-saved');
if (flash) setTimeout(() => {
    flash.style.transition = 'opacity 0.6s, transform 0.6s';
    flash.style.opacity = '0';
    flash.style.transform = 'translateY(-8px)';
    setTimeout(() => flash.parentElement && (flash.parentElement.style.display='none'), 700);
}, 3500);
</script>

<?php include 'includes/footer.php'; ?>
