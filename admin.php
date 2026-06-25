<?php

// 1. SETUP & SECURITY
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

// 2. HANDLE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['toggle_lock'])) {
        $vid   = intval($_POST['video_id']);
        $state = intval($_POST['lock_state']);
        $conn->query("UPDATE premium_content SET is_locked = $state WHERE id = $vid");
    }

    if (isset($_POST['change_video_batch'])) {
        $vid  = intval($_POST['video_id']);
        $ab   = ['b1','b2','b3','b4','b5'];
        $bval = (isset($_POST['video_batch']) && in_array($_POST['video_batch'], $ab))
                ? "'" . $_POST['video_batch'] . "'" : "NULL";
        $conn->query("UPDATE premium_content SET batch=$bval WHERE id=$vid");
    }

    if (isset($_POST['upgrade_user'])) {
        $uid      = intval($_POST['user_id']);
        $duration = $_POST['duration'];
        $allowed  = ['b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond'];
        $batch    = (isset($_POST['batch_type']) && in_array($_POST['batch_type'], $allowed))
                    ? $_POST['batch_type'] : 'b1_premium';
        $expiry   = null;
        if ($duration !== 'life') {
            $map    = ['1m'=>'+1 month','3m'=>'+3 months','6m'=>'+6 months','1y'=>'+1 year'];
            $expiry = date('Y-m-d H:i:s', strtotime($map[$duration] ?? '+1 month'));
        }
        // Capture previous level for the audit trail (before the UPDATE)
        $prev_stmt = $conn->prepare("SELECT membership_type FROM users WHERE id=?");
        $prev_stmt->bind_param("i", $uid);
        $prev_stmt->execute();
        $prev_res = $prev_stmt->get_result();
        $prev_lvl = ($prev_res && $prev_res->num_rows) ? $prev_res->fetch_assoc()['membership_type'] : null;

        $stmt = $conn->prepare("UPDATE users SET membership_type=?, premium_expiry=? WHERE id=?");
        $stmt->bind_param("ssi", $batch, $expiry, $uid);
        $stmt->execute();

        // Audit log — single upgrade (mirrors api/bulk-upgrade-users.php)
        if (isset($_SESSION['user_id'])) {
            $admin_id = (int)$_SESSION['user_id'];
            $log_note = 'Single upgrade (admin panel)';
            $log_stmt = $conn->prepare("INSERT INTO upgrade_log (user_id, previous_level, new_level, expires_at, upgraded_by, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $log_stmt->bind_param("isssis", $uid, $prev_lvl, $batch, $expiry, $admin_id, $log_note);
            $log_stmt->execute();
        }
    }

    if (isset($_POST['downgrade_user'])) {
        $uid = intval($_POST['user_id']);
        $conn->query("UPDATE users SET membership_type='free', premium_expiry=NULL WHERE id=$uid");
    }

    if (isset($_POST['gift_all_3m'])) {
        $exp = date('Y-m-d H:i:s', strtotime('+3 months'));
        $conn->query("UPDATE users SET membership_type='premium', premium_expiry='$exp'");
        echo "<script>window.location.href='admin.php?tab=users';</script>"; exit();
    }

    if (isset($_POST['convert_lifetime_to_3m'])) {
        $exp = date('Y-m-d H:i:s', strtotime('+3 months'));
        $conn->query("UPDATE users SET premium_expiry='$exp' WHERE membership_type='premium' AND premium_expiry IS NULL");
        echo "<script>window.location.href='admin.php?tab=users';</script>"; exit();
    }

    if (isset($_POST['add_term'])) {
        $term = $conn->real_escape_string($_POST['g_term']);
        $def  = $conn->real_escape_string($_POST['g_def']);
        $eq   = $conn->real_escape_string($_POST['g_eq']);
        $conn->query("INSERT INTO glossary (term,definition,equation) VALUES ('$term','$def','$eq')");
    }
    if (isset($_POST['delete_term'])) {
        $conn->query("DELETE FROM glossary WHERE id=".intval($_POST['term_id']));
    }

    if (isset($_POST['add_guide_folder'])) {
        $name = $conn->real_escape_string($_POST['folder_name']);
        $conn->query("INSERT INTO guide_folders (name) VALUES ('$name')");
    }
    if (isset($_POST['delete_guide_folder'])) {
        $fid = intval($_POST['folder_id']);
        $conn->query("UPDATE guides SET folder_id=0 WHERE folder_id=$fid");
        $conn->query("DELETE FROM guide_folders WHERE id=$fid");
    }

    if (isset($_POST['add_guide'])) {
        $title = $conn->real_escape_string($_POST['g_title']);
        $desc  = $conn->real_escape_string($_POST['g_desc']);
        $cat   = $conn->real_escape_string($_POST['g_category']);
        $fid   = intval($_POST['folder_id']);
        $lk    = isset($_POST['is_locked']) ? 1 : 0;
        if (isset($_FILES['g_file']) && $_FILES['g_file']['error'] == 0) {
            $dir  = "uploads/guides/";
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            $path = $dir . time() . "_" . basename($_FILES['g_file']['name']);
            if (move_uploaded_file($_FILES['g_file']['tmp_name'], $path))
                $conn->query("INSERT INTO guides (title,description,category,file_path,folder_id,is_locked) VALUES ('$title','$desc','$cat','$path',$fid,$lk)");
        }
    }
    if (isset($_POST['toggle_guide_lock'])) {
        $gid   = intval($_POST['guide_id']);
        $state = intval($_POST['lock_state']);
        $conn->query("UPDATE guides SET is_locked=$state WHERE id=$gid");
    }
    if (isset($_POST['delete_guide'])) {
        $conn->query("DELETE FROM guides WHERE id=".intval($_POST['guide_id']));
    }

    if (isset($_POST['add_folder'])) {
        $name = $conn->real_escape_string($_POST['folder_name']);
        $conn->query("INSERT INTO video_folders (name) VALUES ('$name')");
    }
    if (isset($_POST['delete_folder'])) {
        $fid = intval($_POST['folder_id']);
        $conn->query("UPDATE premium_content SET folder_id=0 WHERE folder_id=$fid");
        $conn->query("DELETE FROM video_folders WHERE id=$fid");
    }

    if (isset($_POST['add_video'])) {
        $title  = $conn->real_escape_string($_POST['v_title']);
        $url    = $conn->real_escape_string($_POST['v_url']);
        $desc   = $conn->real_escape_string($_POST['v_desc']);
        $fid    = intval($_POST['folder_id']);
        $vtype  = $_POST['video_type'];
        $lk     = isset($_POST['is_locked']) ? 1 : 0;
        $ab     = ['b1','b2','b3','b4','b5'];
        $bval   = (isset($_POST['video_batch']) && in_array($_POST['video_batch'], $ab))
                  ? "'".$_POST['video_batch']."'" : "NULL";
        $conn->query("INSERT INTO premium_content (title,description,video_url,folder_id,video_type,is_locked,batch) VALUES ('$title','$desc','$url',$fid,'$vtype',$lk,$bval)");
    }
    if (isset($_POST['delete_video'])) {
        $conn->query("DELETE FROM premium_content WHERE id=".intval($_POST['video_id']));
    }

    if (isset($_POST['edit_video'])) {
        $vid   = intval($_POST['video_id']);
        $title = $conn->real_escape_string($_POST['v_title']);
        $url   = $conn->real_escape_string($_POST['v_url']);
        $desc  = $conn->real_escape_string($_POST['v_desc']);
        $fid   = intval($_POST['folder_id']);
        $vtype = in_array($_POST['video_type'], ['free','premium']) ? $_POST['video_type'] : 'free';
        $lk    = isset($_POST['is_locked']) ? 1 : 0;
        $ab    = ['b1','b2','b3','b4','b5'];
        $bval  = (isset($_POST['video_batch']) && in_array($_POST['video_batch'], $ab))
                 ? "'".$_POST['video_batch']."'" : "NULL";
        $conn->query("UPDATE premium_content SET title='$title',video_url='$url',description='$desc',folder_id=$fid,video_type='$vtype',is_locked=$lk,batch=$bval WHERE id=$vid");
    }

    if (isset($_POST['add_article'])) {
        $title = $conn->real_escape_string($_POST['a_title']);
        $cat   = $conn->real_escape_string($_POST['a_category']);
        $cont  = $conn->real_escape_string($_POST['a_content']);
        $sql   = "INSERT INTO articles (summary,category,content,published_date) VALUES ('$title','$cat','$cont',NOW())";
        if ($conn->query($sql) === TRUE) {
            $aid = $conn->insert_id;
            if (isset($_FILES['a_thumbnail']) && $_FILES['a_thumbnail']['error'] == 0) {
                $tgt = "uploads/articles/$aid/";
                if (!file_exists($tgt)) mkdir($tgt, 0777, true);
                $file = $tgt . basename($_FILES['a_thumbnail']['name']);
                if (move_uploaded_file($_FILES['a_thumbnail']['tmp_name'], $file))
                    $conn->query("UPDATE articles SET image='$file' WHERE id='$aid'");
            }
        }
    }
    if (isset($_POST['delete_article'])) {
        $conn->query("DELETE FROM articles WHERE id=".intval($_POST['article_id']));
    }

    if (isset($_POST['edit_article'])) {
        $aid   = intval($_POST['article_id']);
        $title = $conn->real_escape_string($_POST['a_title']);
        $cat   = $conn->real_escape_string($_POST['a_category']);
        $cont  = $conn->real_escape_string($_POST['a_content']);
        $conn->query("UPDATE articles SET summary='$title',category='$cat',content='$cont' WHERE id=$aid");
        if (isset($_FILES['a_thumbnail']) && $_FILES['a_thumbnail']['error'] == 0) {
            $tgt = "uploads/articles/$aid/";
            if (!file_exists($tgt)) mkdir($tgt, 0777, true);
            $file = $tgt . basename($_FILES['a_thumbnail']['name']);
            if (move_uploaded_file($_FILES['a_thumbnail']['tmp_name'], $file))
                $conn->query("UPDATE articles SET image='$file' WHERE id=$aid");
        }
    }

    if (isset($_POST['edit_term'])) {
        $gid  = intval($_POST['term_id']);
        $term = $conn->real_escape_string($_POST['g_term']);
        $def  = $conn->real_escape_string($_POST['g_def']);
        $eq   = $conn->real_escape_string($_POST['g_eq']);
        $conn->query("UPDATE glossary SET term='$term',definition='$def',equation='$eq' WHERE id=$gid");
    }

    if (isset($_POST['edit_guide'])) {
        $gid   = intval($_POST['guide_id']);
        $title = $conn->real_escape_string($_POST['g_title']);
        $desc  = $conn->real_escape_string($_POST['g_desc']);
        $cat   = $conn->real_escape_string($_POST['g_category']);
        $fid   = intval($_POST['folder_id']);
        $lk    = isset($_POST['is_locked']) ? 1 : 0;
        $conn->query("UPDATE guides SET title='$title',description='$desc',category='$cat',folder_id=$fid,is_locked=$lk WHERE id=$gid");
    }

    if (isset($_POST['send_reply'])) {
        $msg = $conn->real_escape_string($_POST['message']);
        $tid = intval($_POST['target_id']);
        $conn->query("INSERT INTO support_messages (user_id,message,sender) VALUES ($tid,'$msg','admin')");
    }

    $tab      = isset($_POST['_tab']) ? preg_replace('/[^a-z]/', '', $_POST['_tab']) : '';
    $redirect = 'admin.php' . ($tab ? '?tab='.$tab : '');
    echo "<script>window.location.href='$redirect';</script>";
    exit();
}

// ── AUTO-MIGRATION: widen membership_type to VARCHAR so new tiers work without manual SQL ──
// This block runs once (or is a no-op if already done) and is safe to repeat.
$_mt_col = $conn->query("SHOW COLUMNS FROM users WHERE Field='membership_type'");
if ($_mt_col && ($_mt_row = $_mt_col->fetch_assoc())) {
    if (stripos($_mt_row['Type'], 'varchar') === false) {
        // Still ENUM — change to VARCHAR(20) so b1_premium, diamond, etc. are accepted
        $conn->query("ALTER TABLE users MODIFY COLUMN membership_type VARCHAR(20) NOT NULL DEFAULT 'free'");
    }
}
// Add batch column to premium_content if not already there
$_bc = $conn->query("SHOW COLUMNS FROM premium_content LIKE 'batch'");
if ($_bc && $_bc->num_rows === 0) {
    $conn->query("ALTER TABLE premium_content ADD COLUMN batch VARCHAR(10) NULL DEFAULT NULL");
}

// ── AUTO-CREATE upgrade_log (defensive — also created by add_admin_users_features.sql) ──
$conn->query("CREATE TABLE IF NOT EXISTS upgrade_log (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    previous_level VARCHAR(50) NULL,
    new_level      VARCHAR(50) NOT NULL,
    expires_at     DATETIME NULL,
    upgraded_by    INT NOT NULL,
    notes          TEXT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id), INDEX idx_admin (upgraded_by), INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 3. FETCH DATA
$tc_res = $conn->query("SELECT membership_type, COUNT(*) cnt FROM users GROUP BY membership_type");
$_tc    = [];
while ($r = $tc_res->fetch_assoc()) $_tc[$r['membership_type']] = (int)$r['cnt'];
$stats = [
    'total'   => array_sum($_tc),
    'free'    => $_tc['free']       ?? 0,
    'diamond' => $_tc['diamond']    ?? 0,
    'b1'      => $_tc['b1_premium'] ?? 0,
    'b2'      => $_tc['b2_premium'] ?? 0,
    'b3'      => $_tc['b3_premium'] ?? 0,
    'b4'      => $_tc['b4_premium'] ?? 0,
    'b5'      => $_tc['b5_premium'] ?? 0,
    'legacy'  => $_tc['premium']    ?? 0,
];
$stats['prem'] = $stats['diamond']+$stats['b1']+$stats['b2']+$stats['b3']+$stats['b4']+$stats['b5']+$stats['legacy'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = $search ? " WHERE fullname LIKE '%".$conn->real_escape_string($search)."%' OR email LIKE '%".$conn->real_escape_string($search)."%'" : '';
$total_users = (int)$conn->query("SELECT COUNT(*) FROM users$where")->fetch_row()[0];
// Load ALL matching users; live filtering + pagination happen client-side (vanilla JS, no reloads).
// The ?search= GET form above still works as a no-JS fallback (enhances, doesn't replace it).
$users_res   = $conn->query("SELECT * FROM users$where ORDER BY id DESC");

$folders_res  = $conn->query("SELECT * FROM video_folders ORDER BY name ASC");
$folders_arr  = [];
if ($folders_res) { while ($f = $folders_res->fetch_assoc()) $folders_arr[] = $f; }

$videos_res   = $conn->query("SELECT v.*, f.name AS folder_name FROM premium_content v LEFT JOIN video_folders f ON v.folder_id=f.id ORDER BY v.id DESC");
$articles_res = $conn->query("SELECT * FROM articles ORDER BY id DESC");
$chat_users = $conn->query("
    SELECT u.id, u.fullname, u.email,
        (SELECT message    FROM support_messages WHERE user_id=u.id ORDER BY id DESC LIMIT 1) AS last_msg,
        (SELECT sender     FROM support_messages WHERE user_id=u.id ORDER BY id DESC LIMIT 1) AS last_sender,
        (SELECT created_at FROM support_messages WHERE user_id=u.id ORDER BY id DESC LIMIT 1) AS last_time,
        (SELECT COUNT(*)   FROM support_messages WHERE user_id=u.id AND sender='user'
            AND id > COALESCE((SELECT MAX(id) FROM support_messages WHERE user_id=u.id AND sender='admin'), 0)
        ) AS unread
    FROM (SELECT DISTINCT user_id FROM support_messages) t
    JOIN users u ON u.id = t.user_id
    ORDER BY last_time DESC
");

$guide_folders_res = $conn->query("SELECT * FROM guide_folders ORDER BY name ASC");
$guide_folders_arr = [];
if ($guide_folders_res) { while ($f = $guide_folders_res->fetch_assoc()) $guide_folders_arr[] = $f; }

$guides_res   = $conn->query("SELECT g.*, f.name AS folder_name FROM guides g LEFT JOIN guide_folders f ON g.folder_id=f.id ORDER BY g.id DESC");

$glossary_res = null;
if ($conn->query("SHOW TABLES LIKE 'glossary'")->num_rows > 0)
    $glossary_res = $conn->query("SELECT * FROM glossary ORDER BY term ASC");

// ── Dashboard extra stats ──
$stat_prem_vids = (int)$conn->query("SELECT COUNT(*) FROM premium_content WHERE video_type='premium'")->fetch_row()[0];
$stat_free_vids = (int)$conn->query("SELECT COUNT(*) FROM premium_content WHERE video_type='free'")->fetch_row()[0];
$stat_articles  = (int)$conn->query("SELECT COUNT(*) FROM articles")->fetch_row()[0];
$stat_guides    = (int)$conn->query("SELECT COUNT(*) FROM guides")->fetch_row()[0];
$stat_glossary  = ($conn->query("SHOW TABLES LIKE 'glossary'")->num_rows > 0)
                  ? (int)$conn->query("SELECT COUNT(*) FROM glossary")->fetch_row()[0] : 0;
$recent_users_r = $conn->query("SELECT fullname, membership_type, created_at FROM users ORDER BY id DESC LIMIT 6");
$recent_users   = [];
if ($recent_users_r) while ($ru = $recent_users_r->fetch_assoc()) $recent_users[] = $ru;
?>
<!--
    ADMIN PANEL STYLES
-->
<style>
/* ── Keyframes ── */
@keyframes admFadeUp  { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }
@keyframes admPopIn   { from{opacity:0;transform:scale(.9)}        to{opacity:1;transform:scale(1)} }
@keyframes admPulse   { 0%,100%{opacity:1} 50%{opacity:.4} }
@keyframes admShimmer { from{background-position:-200% center} to{background-position:200% center} }
@keyframes admSlideIn { from{opacity:0;transform:translateX(-12px)} to{opacity:1;transform:none} }
@keyframes dbOrb      { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.15) translate(20px,-15px)} }
@keyframes dbRingIn   { from{stroke-dasharray:0 315} }
@keyframes dbBarIn    { from{width:0} }
@keyframes dbPulseRing{ 0%{transform:scale(1);opacity:.7} 100%{transform:scale(1.8);opacity:0} }

/* ══ DASHBOARD HERO ══ */
.db-hero {
    position:relative; border-radius:20px; overflow:hidden;
    background:linear-gradient(145deg,#080f1e 0%,#0c1628 60%,#0f172a 100%);
    border:1px solid rgba(212,175,55,.14);
    padding:38px 36px; margin-bottom:20px;
    box-shadow:0 4px 40px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,255,255,.04);
}
.db-hero-bg  { position:absolute;inset:0;pointer-events:none; }
.db-orb      { position:absolute;border-radius:50%;filter:blur(80px);will-change:transform; }
.db-orb-1    { width:420px;height:420px;background:radial-gradient(circle,rgba(212,175,55,.14),transparent 70%);top:-160px;left:-80px;animation:dbOrb 9s ease-in-out infinite; }
.db-orb-2    { width:320px;height:320px;background:radial-gradient(circle,rgba(99,102,241,.1),transparent 70%);top:-60px;right:-100px;animation:dbOrb 11s ease-in-out infinite reverse; }
.db-orb-3    { width:200px;height:200px;background:radial-gradient(circle,rgba(167,139,250,.09),transparent 70%);bottom:-80px;left:42%;animation:dbOrb 7s ease-in-out infinite; }
.db-grid-bg  { position:absolute;inset:0;background-image:linear-gradient(rgba(212,175,55,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(212,175,55,.04) 1px,transparent 1px);background-size:48px 48px; }

.db-hero-inner { position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap; }
.db-greeting   { font-size:.72rem;font-weight:800;letter-spacing:2.5px;text-transform:uppercase;color:var(--ag);margin-bottom:8px;animation:admFadeUp .5s ease both; }
.db-hero-title { font-size:clamp(1.5rem,3vw,2.3rem);font-weight:900;color:#fff;margin:0 0 8px;line-height:1.15;animation:admFadeUp .5s .07s ease both; }
.db-gold       { background:linear-gradient(135deg,#d4af37 0%,#f5d679 45%,#d4af37 100%);background-size:200% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:admShimmer 3s linear infinite; }
.db-hero-sub   { font-size:.82rem;color:#475569;margin:0 0 18px;animation:admFadeUp .5s .12s ease both; }
.db-chips      { display:flex;gap:8px;flex-wrap:wrap;animation:admFadeUp .5s .18s ease both; }
.db-chip       { display:inline-flex;align-items:center;gap:6px;padding:5px 13px;border-radius:99px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);color:#94a3b8;font-size:.74rem;font-weight:600; }
.db-chip-live  { border-color:rgba(16,185,129,.35);color:#10b981; }
.db-chip-dot   { width:6px;height:6px;border-radius:50%;background:#10b981;box-shadow:0 0 8px #10b981;animation:admPulse 1.6s infinite; }
.db-clock-wrap { text-align:right;animation:admFadeUp .5s .1s ease both;flex-shrink:0; }
.db-clock      { font-size:clamp(2rem,4vw,3.2rem);font-weight:900;color:#fff;letter-spacing:.04em;font-variant-numeric:tabular-nums;font-family:'Courier New',monospace;line-height:1;
                 background:linear-gradient(135deg,#d4af37,#f5d679);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
.db-clock-date { font-size:.78rem;color:#475569;margin-top:5px; }

/* ══ KPI CARDS ══ */
.db-kpi-grid   { display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:14px;margin-bottom:18px; }
.db-kpi        { background:var(--ac);border:1px solid var(--ae);border-radius:16px;padding:20px;position:relative;overflow:hidden;
                 transition:transform .25s,box-shadow .25s,border-color .25s;animation:admPopIn .4s ease both; }
.db-kpi::after { content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:var(--kc,#e2e8f0);opacity:0;transition:opacity .25s; }
.db-kpi:hover  { transform:translateY(-5px);box-shadow:0 16px 40px rgba(0,0,0,.1),0 0 0 1px var(--kc,var(--ae)); }
.db-kpi:hover::after { opacity:1; }
.db-kpi-top    { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px; }
.db-kpi-icon   { width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.1rem; }
.db-kpi-pct    { font-size:.68rem;font-weight:800;padding:3px 9px;border-radius:20px;text-transform:uppercase;letter-spacing:.5px; }
.db-kpi-num    { font-size:2.4rem;font-weight:900;color:var(--at);line-height:1;margin-bottom:5px; }
.db-kpi-lbl    { font-size:.74rem;color:var(--am);font-weight:600;margin-bottom:14px; }
.db-kpi-bar    { height:5px;background:var(--ae);border-radius:99px;overflow:hidden; }
.db-kpi-fill   { height:100%;border-radius:99px;width:0;transition:width 1.4s cubic-bezier(.4,0,.2,1); }

/* ══ MID ROW ══ */
.db-mid-row    { display:grid;grid-template-columns:280px 1fr;gap:16px;margin-bottom:18px; }

/* Shared card */
.db-card       { background:var(--ac);border:1px solid var(--ae);border-radius:16px;padding:22px;box-shadow:0 1px 4px rgba(0,0,0,.04); }
.db-card-hd    { font-size:.88rem;font-weight:700;color:var(--at);display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:18px; }
.db-card-hd i  { color:var(--ag); }

/* Donut */
.db-donut-wrap { position:relative;width:150px;height:150px;margin:0 auto 16px; }
.db-donut      { width:100%;height:100%; }
.db-ring-seg   { stroke-linecap:butt;transition:stroke-dasharray 1.6s cubic-bezier(.4,0,.2,1) .2s; }
.db-donut-ctr  { position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center; }
.db-donut-pct  { font-size:1.8rem;font-weight:900;color:var(--at);line-height:1; }
.db-donut-lbl2 { font-size:.62rem;color:var(--am);font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-top:2px; }
.db-legend     { display:flex;flex-direction:column;gap:9px; }
.db-leg-row    { display:flex;align-items:center;gap:8px;font-size:.8rem;color:var(--am); }
.db-leg-row strong { margin-left:auto;color:var(--at);font-weight:800; }
.db-leg-dot    { width:10px;height:10px;border-radius:50%;flex-shrink:0; }

/* Batch bars */
.db-batch-row  { display:flex;align-items:center;gap:10px;margin-bottom:13px; }
.db-batch-lbl  { display:flex;align-items:center;gap:6px;min-width:100px;font-size:.79rem;font-weight:700;color:var(--at); }
.db-batch-dot2 { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
.db-batch-track{ flex:1;height:8px;background:var(--ae);border-radius:99px;overflow:hidden; }
.db-batch-fill { height:100%;border-radius:99px;width:0;transition:width 1.3s cubic-bezier(.4,0,.2,1); }
.db-batch-ct   { min-width:26px;text-align:right;font-size:.8rem;font-weight:800; }

/* ══ BOTTOM ROW ══ */
.db-bot-row    { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px; }

/* Content items */
.db-cnt-grid   { display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:10px; }
.db-cnt-item   { background:#f8fafc;border:1.5px solid var(--ae);border-radius:12px;padding:16px 10px;text-align:center;cursor:pointer;transition:all .22s;position:relative;overflow:hidden; }
.db-cnt-item::before { content:'';position:absolute;inset:0;background:var(--cc,#3b82f6);opacity:0;transition:opacity .22s; }
.db-cnt-item:hover::before { opacity:.06; }
.db-cnt-item:hover { border-color:var(--cc,#3b82f6);transform:translateY(-3px);box-shadow:0 8px 22px rgba(0,0,0,.09); }
.db-cnt-icon   { font-size:1.25rem;margin-bottom:8px;position:relative;z-index:1; }
.db-cnt-num    { font-size:1.6rem;font-weight:900;color:var(--at);line-height:1;margin-bottom:4px;position:relative;z-index:1; }
.db-cnt-lbl    { font-size:.64rem;color:var(--am);font-weight:700;text-transform:uppercase;letter-spacing:.5px;position:relative;z-index:1; }

/* Recent users */
.db-rec-row    { display:flex;align-items:center;gap:11px;padding:9px 0;border-bottom:1px solid #f8fafc; }
.db-rec-row:last-child { border-bottom:none;padding-bottom:0; }
.db-rec-av     { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.88rem;color:#fff;flex-shrink:0; }
.db-rec-info   { flex:1;min-width:0; }
.db-rec-name   { font-size:.83rem;font-weight:700;color:var(--at);white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.db-rec-date   { font-size:.7rem;color:var(--am); }
.db-rec-badge  { font-size:.6rem;font-weight:800;padding:3px 7px;border-radius:20px;border:1px solid;white-space:nowrap; }

/* ══ QUICK NAV ══ */
.db-quick-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:11px;padding-top:2px; }
.db-qcard      { background:#f8fafc;border:1.5px solid var(--ae);border-radius:14px;padding:18px 15px;cursor:pointer;position:relative;overflow:hidden;transition:all .22s; }
.db-qcard:hover{ border-color:var(--qc);transform:translateY(-5px);box-shadow:0 12px 32px rgba(0,0,0,.12);background:#fff; }
.db-qcard:hover .db-q-icon { background:var(--qc);color:#fff;transform:scale(1.1); }
.db-qcard:hover .db-q-arrow{ opacity:1;transform:translateX(0); }
.db-q-icon     { width:42px;height:42px;border-radius:11px;background:var(--ae);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--am);margin-bottom:10px;transition:all .22s; }
.db-q-lbl      { font-size:.88rem;font-weight:800;color:var(--at);margin-bottom:2px; }
.db-q-sub      { font-size:.71rem;color:var(--am);line-height:1.4; }
.db-q-arrow    { position:absolute;bottom:14px;right:14px;font-size:.7rem;color:var(--am);opacity:0;transform:translateX(-6px);transition:all .22s; }

/* ══ BULK BTNS ══ */
.db-bulk-row   { display:flex;gap:14px;flex-wrap:wrap;padding-top:2px; }
.db-bulk-btn   { flex:1;min-width:200px;padding:20px 18px;border-radius:13px;border:1.5px solid var(--ae);background:#f8fafc;cursor:pointer;text-align:left;font-family:inherit;transition:all .25s;display:flex;align-items:center;gap:14px;color:var(--at); }
.db-bulk-btn:hover { background:var(--bbc);color:#fff;border-color:transparent;transform:translateY(-3px);box-shadow:0 10px 30px rgba(0,0,0,.18); }
.db-bulk-btn:hover .db-bicon { background:rgba(255,255,255,.2);color:#fff; }
.db-bicon      { width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;transition:all .22s; }
.db-btxt strong{ display:block;font-size:.9rem;font-weight:800;margin-bottom:3px; }
.db-btxt span  { font-size:.75rem;opacity:.75; }

@media(max-width:960px) {
    .db-mid-row  { grid-template-columns:1fr; }
    .db-bot-row  { grid-template-columns:1fr; }
    .db-hero-inner{ flex-direction:column; }
    .db-clock-wrap{ text-align:left; }
}
@media(max-width:580px) {
    .db-quick-grid{ grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); }
    .db-kpi-grid  { grid-template-columns:1fr 1fr; }
}

/* ── Root tokens ── */
:root {
    --as: 260px;          /* sidebar width */
    --ad: #0f172a;        /* dark */
    --ag: #d4af37;        /* gold */
    --ag2:#f5d679;        /* gold light */
    --ab: #f1f5f9;        /* background */
    --ac: #ffffff;        /* card */
    --ae: #e2e8f0;        /* border */
    --at: #0f172a;        /* text */
    --am: #64748b;        /* muted */
    --ar: 14px;           /* radius */
}

body { background:var(--ab); margin:0; font-family:'Padauk','Segoe UI',sans-serif; }

/* ══ LAYOUT ══ */
.aw { display:flex; min-height:100vh; }

/* ══ SIDEBAR ══ */
.asb {
    width:var(--as); background:var(--ad);
    position:fixed; left:0;
    top:var(--header-total-height,70px);
    height:calc(100vh - var(--header-total-height,70px));
    display:flex; flex-direction:column;
    z-index:200; overflow:hidden;
    transition:width .3s cubic-bezier(.4,0,.2,1);
    box-shadow:4px 0 24px rgba(0,0,0,.15);
}
.asb-head {
    padding:22px 18px 18px;
    border-bottom:1px solid rgba(255,255,255,.07);
    display:flex; align-items:center; gap:12px; flex-shrink:0;
}
.asb-logo {
    width:42px; height:42px; border-radius:12px; flex-shrink:0;
    background:linear-gradient(135deg,var(--ag),var(--ag2));
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; color:#0f172a;
    box-shadow:0 4px 14px rgba(212,175,55,.35);
}
.asb-title { font-size:.95rem; font-weight:800; color:#fff; white-space:nowrap; }
.asb-sub   { font-size:.7rem; color:#475569; white-space:nowrap; }

/* nav list */
.asb-nav { list-style:none; padding:14px 10px; margin:0; flex:1; overflow-y:auto; scrollbar-width:none; }
.asb-nav::-webkit-scrollbar { display:none; }
.asb-nav li { margin-bottom:3px; }
.anb {
    width:100%; display:flex; align-items:center; gap:11px;
    padding:10px 12px; border:none; border-radius:10px;
    background:transparent; color:#64748b;
    font-size:.88rem; font-weight:600; cursor:pointer;
    transition:all .2s; text-align:left; white-space:nowrap; overflow:hidden;
    position:relative; font-family:inherit;
}
.anb .ni {
    width:34px; height:34px; border-radius:9px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.9rem; background:rgba(255,255,255,.05);
    transition:all .2s;
}
.anb:hover { color:#e2e8f0; background:rgba(255,255,255,.06); }
.anb:hover .ni { background:rgba(212,175,55,.14); color:var(--ag); }
.anb.active {
    color:#fff; background:rgba(212,175,55,.13);
    box-shadow:inset 3px 0 0 var(--ag);
}
.anb.active .ni {
    background:linear-gradient(135deg,var(--ag),var(--ag2));
    color:#0f172a; box-shadow:0 4px 12px rgba(212,175,55,.3);
}
.anb .nl { flex:1; }
.anb .nb {
    background:rgba(212,175,55,.18); color:var(--ag);
    font-size:.62rem; font-weight:800;
    padding:2px 7px; border-radius:20px; flex-shrink:0;
}
/* sidebar footer */
.asb-foot {
    padding:12px 10px; border-top:1px solid rgba(255,255,255,.06); flex-shrink:0;
}
.asb-foot a {
    display:flex; align-items:center; gap:10px; padding:9px 12px;
    border-radius:9px; color:#475569; font-size:.83rem; font-weight:600;
    text-decoration:none; transition:all .2s;
}
.asb-foot a:hover { color:#fff; background:rgba(255,255,255,.07); }

/* ══ MAIN ══ */
.amc {
    margin-left:var(--as); flex:1; padding:28px 30px;
    transition:margin-left .3s cubic-bezier(.4,0,.2,1);
    min-width:0;
}

/* ══ PAGE HEADER ══ */
.aph { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:14px; margin-bottom:24px; }
.aph-title { font-size:1.5rem; font-weight:800; color:var(--at); margin:0; line-height:1.2; }
.aph-title span { color:var(--ag); }
.aph-sub { font-size:.8rem; color:var(--am); margin:4px 0 0; }

/* ══ SECTIONS ══ */
.asec { display:none; }
.asec.active { display:block; animation:admFadeUp .32s ease both; }

/* ══ CARD ══ */
.acard {
    background:var(--ac); border-radius:var(--ar);
    border:1px solid var(--ae);
    box-shadow:0 1px 3px rgba(0,0,0,.04),0 4px 16px rgba(0,0,0,.03);
    margin-bottom:20px; overflow:hidden;
}
.acard-head {
    padding:16px 20px; border-bottom:1px solid var(--ae);
    display:flex; align-items:center; justify-content:space-between; gap:12px;
}
.acard-title {
    font-size:.95rem; font-weight:700; color:var(--at); margin:0;
    display:flex; align-items:center; gap:8px;
}
.acard-title i { color:var(--ag); font-size:.85rem; }
.acard-body { padding:20px; }

/* ══ STAT CARDS ══ */
.asg { display:grid; grid-template-columns:repeat(auto-fill,minmax(185px,1fr)); gap:14px; margin-bottom:18px; }
.asc {
    background:var(--ac); border:1px solid var(--ae); border-radius:var(--ar);
    padding:18px; display:flex; flex-direction:column; gap:10px;
    box-shadow:0 1px 4px rgba(0,0,0,.04);
    animation:admPopIn .4s ease both;
    transition:transform .25s,box-shadow .25s;
}
.asc:hover { transform:translateY(-4px); box-shadow:0 8px 28px rgba(0,0,0,.09); }
.asc-top { display:flex; align-items:center; justify-content:space-between; }
.asc-icon { width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.05rem; }
.asc-tag { font-size:.68rem; font-weight:800; padding:3px 9px; border-radius:20px; }
.asc-num { font-size:1.9rem; font-weight:800; color:var(--at); line-height:1; }
.asc-lbl { font-size:.75rem; color:var(--am); font-weight:600; }

/* batch strip */
.abg { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:10px; margin-bottom:18px; }
.abc {
    background:var(--ac); border:1px solid var(--ae); border-radius:12px;
    padding:13px 15px; display:flex; align-items:center; gap:10px;
    border-left:4px solid var(--bc,var(--ag));
    animation:admSlideIn .35s ease both;
}
.abc-dot { width:8px; height:8px; border-radius:50%; background:var(--bc,var(--ag)); animation:admPulse 2.5s infinite; }
.abc-num { font-size:1.25rem; font-weight:800; color:var(--at); line-height:1.1; }
.abc-lbl { font-size:.72rem; color:var(--am); font-weight:600; }

/* ══ TABLE ══ */
.atw { overflow-x:auto; }
.atable { width:100%; border-collapse:separate; border-spacing:0; font-size:.85rem; }
.atable th {
    background:#f8fafc; color:var(--am); font-weight:700; text-align:left;
    padding:12px 16px; font-size:.7rem; text-transform:uppercase;
    letter-spacing:1px; border-bottom:2px solid var(--ae); white-space:nowrap;
}
.atable td { padding:13px 16px; border-bottom:1px solid #f8fafc; vertical-align:middle; color:#334155; }
.atable tr:last-child td { border-bottom:none; }
.atable tbody tr:hover td { background:#fafbfc; }

/* avatar */
.aav { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.9rem; color:#fff; flex-shrink:0; }
.aur { display:flex; align-items:center; gap:11px; }
.aun { font-weight:700; color:var(--at); font-size:.88rem; }
.aue { font-size:.75rem; color:var(--am); }

/* ══ BADGES ══ */
.abadge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
.bd { background:linear-gradient(135deg,#a78bfa,#818cf8); color:#fff; }
.bb1{ background:rgba(212,175,55,.15); color:#a0801c; border:1px solid rgba(212,175,55,.3); }
.bb2{ background:rgba(59,130,246,.12); color:#1d4ed8; border:1px solid rgba(59,130,246,.3); }
.bb3{ background:rgba(16,185,129,.12); color:#065f46; border:1px solid rgba(16,185,129,.3); }
.bb4{ background:rgba(245,158,11,.12); color:#92400e; border:1px solid rgba(245,158,11,.3); }
.bb5{ background:rgba(239,68,68,.12);  color:#991b1b; border:1px solid rgba(239,68,68,.3); }
.bl { background:rgba(212,175,55,.1);  color:#b5952f; border:1px solid rgba(212,175,55,.2); }
.bf { background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; }
.bp { background:rgba(212,175,55,.15); color:#b5952f; border:1px solid rgba(212,175,55,.3); }
.bop{ background:rgba(16,185,129,.1);  color:#065f46; border:1px solid rgba(16,185,129,.25); }
.blk{ background:rgba(239,68,68,.08); color:#991b1b; border:1px solid rgba(239,68,68,.2); }

/* ══ FORMS ══ */
.ainp, .asel, .atea {
    width:100%; padding:10px 13px; border:1.5px solid var(--ae); border-radius:9px;
    font-family:inherit; font-size:.86rem; background:#fdfdfd;
    color:var(--at); outline:none; transition:border-color .2s,box-shadow .2s;
    box-sizing:border-box;
}
.ainp:focus,.asel:focus,.atea:focus { border-color:var(--ag); box-shadow:0 0 0 3px rgba(212,175,55,.12); background:#fff; }
.albl { display:block; font-size:.7rem; font-weight:700; color:var(--am); text-transform:uppercase; letter-spacing:.8px; margin-bottom:5px; margin-top:14px; }
.albl:first-child { margin-top:0; }
.afg  { display:grid; gap:0; }
.afg2 { grid-template-columns:1fr 1fr; gap:14px; }
.afield { margin-bottom:12px; }

/* ══ BUTTONS ══ */
.abtn {
    display:inline-flex; align-items:center; gap:7px; padding:9px 17px;
    border-radius:9px; border:none; font-weight:700; cursor:pointer;
    font-size:.83rem; transition:all .2s; text-decoration:none;
    white-space:nowrap; font-family:inherit;
}
.abtn-g  { background:linear-gradient(135deg,var(--ag),var(--ag2)); color:#0f172a; box-shadow:0 3px 10px rgba(212,175,55,.25); }
.abtn-g:hover  { transform:translateY(-2px); box-shadow:0 6px 20px rgba(212,175,55,.4); }
.abtn-r  { background:#fee2e2; color:#991b1b; }
.abtn-r:hover  { background:#fecaca; }
.abtn-gr { background:#dcfce7; color:#166534; }
.abtn-gr:hover { background:#bbf7d0; }
.abtn-gh { background:transparent; color:var(--am); border:1.5px solid var(--ae); }
.abtn-gh:hover { background:#f8fafc; color:var(--at); }
.abtn-sm { padding:6px 11px; font-size:.77rem; border-radius:7px; }
.abtn-full { width:100%; justify-content:center; }

/* ══ SEARCH ══ */
.asw {
    display:flex; align-items:center; gap:8px; padding:8px 14px;
    background:#f8fafc; border:1.5px solid var(--ae); border-radius:10px;
    transition:border-color .2s,box-shadow .2s;
}
.asw:focus-within { border-color:var(--ag); box-shadow:0 0 0 3px rgba(212,175,55,.1); background:#fff; }
.asw i { color:var(--am); font-size:.83rem; }
.asw input { border:none; background:transparent; outline:none; font-family:inherit; font-size:.86rem; color:var(--at); width:210px; }
.asw input::placeholder { color:#94a3b8; }

/* ══ PAGINATION ══ */
.apg { display:flex; align-items:center; gap:5px; padding:13px 18px; border-top:1px solid #f1f5f9; flex-wrap:wrap; }
.apb {
    min-width:34px; height:34px; border-radius:8px; border:1.5px solid var(--ae);
    background:#fff; color:var(--am); font-size:.8rem; font-weight:700;
    text-decoration:none; display:flex; align-items:center; justify-content:center;
    padding:0 9px; transition:all .2s;
}
.apb:hover { border-color:var(--ag); color:var(--ag); }
.apb.active { background:linear-gradient(135deg,var(--ag),var(--ag2)); color:#0f172a; border-color:transparent; box-shadow:0 3px 10px rgba(212,175,55,.3); }

/* ══ CHAT — PREMIUM REDESIGN ══ */
.ach-wrap    { display:flex; height:640px; border-radius:16px; overflow:hidden; background:#0b1220; border:1px solid rgba(212,175,55,.12); box-shadow:0 20px 60px rgba(0,0,0,.5); }

/* Sidebar */
.achl        { width:290px; flex-shrink:0; background:#080f1c; border-right:1px solid rgba(255,255,255,.06); display:flex; flex-direction:column; }
.achl-head   { padding:18px 16px 12px; border-bottom:1px solid rgba(255,255,255,.06); }
.achl-title  { font-size:.78rem; font-weight:800; letter-spacing:2px; text-transform:uppercase; color:#d4af37; margin-bottom:10px; }
.achl-search { width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); border-radius:10px; padding:8px 12px; color:#f1f5f9; font-size:.82rem; outline:none; font-family:inherit; box-sizing:border-box; transition:border-color .2s; }
.achl-search:focus { border-color:rgba(212,175,55,.4); }
.achl-search::placeholder { color:#475569; }
.achl-list   { flex:1; overflow-y:auto; scrollbar-width:thin; }
.achl-list::-webkit-scrollbar { width:3px; }
.achl-list::-webkit-scrollbar-thumb { background:rgba(255,255,255,.07); border-radius:3px; }

.achu        { display:flex; align-items:center; gap:10px; padding:12px 14px; cursor:pointer; border-bottom:1px solid rgba(255,255,255,.04); transition:background .15s; position:relative; }
.achu:hover  { background:rgba(212,175,55,.05); }
.achu.active { background:rgba(212,175,55,.1); border-right:2px solid #d4af37; }
.achu-av     { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:.85rem; font-weight:700; color:#fff; flex-shrink:0; }
.achu-info   { flex:1; min-width:0; }
.achu-n      { font-size:.83rem; font-weight:600; color:#f1f5f9; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.achu-prev   { font-size:.75rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
.achu-prev.from-admin { color:#d4af37; }
.achu-meta   { display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0; }
.achu-time   { font-size:.68rem; color:#334155; }
.achu-badge  { background:#ef4444; color:#fff; font-size:.65rem; font-weight:700; border-radius:50%; width:17px; height:17px; display:flex; align-items:center; justify-content:center; }
.achl-empty  { padding:32px 16px; text-align:center; color:#334155; font-size:.82rem; }

/* Chat panel */
.achb        { flex:1; display:flex; flex-direction:column; min-width:0; }

/* Chat header */
.ach-hdr     { padding:14px 18px; background:#080f1c; border-bottom:1px solid rgba(255,255,255,.06); display:flex; align-items:center; gap:12px; flex-shrink:0; }
.ach-hdr-av  { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.82rem; font-weight:700; color:#fff; flex-shrink:0; }
.ach-hdr-info { flex:1; }
.ach-hdr-name { font-size:.9rem; font-weight:700; color:#f1f5f9; }
.ach-hdr-sub  { font-size:.72rem; color:#64748b; margin-top:1px; }
.ach-empty-hdr { flex:1; display:flex; align-items:center; justify-content:center; }

/* Message area */
.achm        { flex:1; padding:16px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; background:#0b1220; scroll-behavior:smooth; }
.achm::-webkit-scrollbar { width:4px; }
.achm::-webkit-scrollbar-thumb { background:rgba(255,255,255,.07); border-radius:4px; }
.ache        { text-align:center; color:#334155; margin:auto; font-size:.85rem; display:flex; flex-direction:column; align-items:center; gap:10px; }
.ache i      { font-size:2rem; color:#1e293b; }

/* Message rows */
.adm-msg-row   { display:flex; align-items:flex-end; gap:8px; animation:admMsgIn .22s ease both; }
@keyframes admMsgIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
.adm-msg-row.row-admin { flex-direction:row-reverse; }
.adm-bubble-av { width:26px; height:26px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.68rem; font-weight:700; color:#fff; flex-shrink:0; }
.adm-bubble-av.av-admin { background:linear-gradient(135deg,#d4af37,#facc15); color:#0f172a; }
.adm-bubble-wrap { display:flex; flex-direction:column; gap:3px; max-width:74%; }
.row-admin .adm-bubble-wrap { align-items:flex-end; }
.adm-bubble  { padding:9px 13px; font-size:.83rem; line-height:1.55; border-radius:14px; word-break:break-word; }
.adm-bubble.b-user  { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08); color:#cbd5e1; border-radius:14px 14px 14px 4px; }
.adm-bubble.b-admin { background:linear-gradient(135deg,#92400e,#b45309); color:#fef3c7; border-radius:14px 14px 4px 14px; }
.adm-bubble-time { font-size:.68rem; color:#334155; padding:0 2px; }
.adm-day-div { display:flex; align-items:center; gap:8px; font-size:.68rem; color:#1e293b; text-transform:uppercase; letter-spacing:1px; flex-shrink:0; }
.adm-day-div::before,.adm-day-div::after { content:''; flex:1; height:1px; background:rgba(255,255,255,.05); }

/* Typing dots */
.adm-typing { display:flex; align-items:flex-end; gap:8px; }
.adm-typing-bub { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08); border-radius:14px 14px 14px 4px; padding:10px 14px; display:flex; gap:4px; }
.adm-typing-dot { width:6px; height:6px; border-radius:50%; background:#475569; animation:admDot .8s ease-in-out infinite; }
.adm-typing-dot:nth-child(2) { animation-delay:.15s; }
.adm-typing-dot:nth-child(3) { animation-delay:.3s; }
@keyframes admDot { 0%,60%,100%{transform:translateY(0);background:#475569;} 30%{transform:translateY(-5px);background:#d4af37;} }

/* Input area */
.achir       { padding:12px 16px 14px; border-top:1px solid rgba(255,255,255,.06); background:#080f1c; display:flex; align-items:flex-end; gap:10px; flex-shrink:0; }
.achir-ta    { flex:1; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); border-radius:12px; padding:10px 14px; color:#f1f5f9; font-size:.84rem; font-family:inherit; resize:none; outline:none; min-height:40px; max-height:110px; line-height:1.4; transition:border-color .2s,background .2s; overflow-y:auto; }
.achir-ta::placeholder { color:#475569; }
.achir-ta:focus { border-color:rgba(212,175,55,.35); background:rgba(255,255,255,.07); }
.achir-send  { width:40px; height:40px; border-radius:11px; background:linear-gradient(135deg,#d4af37,#facc15); border:none; color:#020617; font-size:.9rem; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:transform .15s,box-shadow .2s; box-shadow:0 4px 14px rgba(212,175,55,.35); }
.achir-send:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(212,175,55,.5); }
.achir-send:active { transform:scale(.92); }
.achir-send.sending { animation:admSendPop .3s cubic-bezier(.34,1.56,.64,1); }
@keyframes admSendPop { 0%{transform:scale(1);} 40%{transform:scale(1.22) rotate(-10deg);} 100%{transform:scale(1);} }

/* ══ BULK CARDS ══ */
.ablk { display:flex; gap:14px; flex-wrap:wrap; }
.ablkc { background:#f8fafc; border:1.5px solid var(--ae); border-radius:12px; padding:16px 20px; display:flex; align-items:center; gap:14px; flex:1; min-width:220px; }
.ablki { width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }

/* ══ UPGRADE ROW ══ */
.aupg { display:flex; gap:7px; align-items:center; flex-wrap:wrap; }
.aupg select { padding:7px 10px; border:1.5px solid var(--ae); border-radius:8px; font-size:.8rem; background:#fff; cursor:pointer; outline:none; transition:border-color .2s; font-family:inherit; }
.aupg select:focus { border-color:var(--ag); }

/* ══ CHECKBOX ══ */
.aclbl { display:flex; align-items:center; gap:8px; font-size:.84rem; color:var(--at); cursor:pointer; font-weight:600; }
.aclbl input[type=checkbox] { width:15px; height:15px; accent-color:var(--ag); cursor:pointer; }

/* ══ RESPONSIVE ══ */
@media(max-width:1024px) {
    .asb  { width:66px; }
    .asb-title,.asb-sub,.nl,.nb,.asb-foot span { display:none; }
    .anb  { justify-content:center; padding:10px; }
    .anb .ni { width:38px; height:38px; }
    .asb-head { justify-content:center; padding:18px 10px; }
    .amc  { margin-left:66px; }
    .afg2 { grid-template-columns:1fr; }
}
@media(max-width:680px) {
    .amc  { padding:14px; }
    .asg  { grid-template-columns:1fr 1fr; }
    .abg  { grid-template-columns:1fr 1fr; }
    .aph  { flex-direction:column; }
}
</style>

<div class="aw">

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="asb">
    <div class="asb-head">
        <div class="asb-logo"><i class="fas fa-chart-line"></i></div>
        <div>
            <div class="asb-title">Wise Admin</div>
            <div class="asb-sub">Control Panel</div>
        </div>
    </div>

    <ul class="asb-nav">
        <?php
        $navItems = [
            ['dashboard','fas fa-home',        'Dashboard', ''],
            ['users',    'fas fa-users',       'Users',     number_format($stats['total'])],
            ['news',     'fas fa-newspaper',   'Articles',  ''],
            ['glossary', 'fas fa-book-open',   'Glossary',  ''],
            ['videos',   'fas fa-play-circle', 'Videos',    ''],
            ['guides',   'fas fa-file-pdf',    'Guides',    ''],
            ['chat',     'fas fa-comments',    'Support',   ''],
        ];
        foreach ($navItems as [$sid,$icon,$label,$badge]):
        ?>
        <li>
            <button class="anb" data-target="<?= $sid ?>">
                <span class="ni"><i class="<?= $icon ?>"></i></span>
                <span class="nl"><?= $label ?></span>
                <?php if ($badge): ?><span class="nb"><?= $badge ?></span><?php endif; ?>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="asb-foot">
        <a href="/admin/install-analytics.php">
            <i class="fas fa-chart-line"></i>
            <span>Install Analytics</span>
        </a>
        <a href="/admin/upgrade-history.php">
            <i class="fas fa-history"></i>
            <span>Upgrade History</span>
        </a>
        <a href="dashboard.php">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Site</span>
        </a>
    </div>
</aside>

<!-- ══════════ MAIN CONTENT ══════════ -->
<main class="amc">

<!-- ┌───────────────┐ -->
<!-- │   DASHBOARD   │ -->
<!-- └───────────────┘ -->
<div id="dashboard" class="asec">

<!-- ── HERO ── -->
<div class="db-hero">
    <div class="db-hero-bg">
        <div class="db-orb db-orb-1"></div>
        <div class="db-orb db-orb-2"></div>
        <div class="db-orb db-orb-3"></div>
        <div class="db-grid-bg"></div>
    </div>
    <div class="db-hero-inner">
        <div>
            <div class="db-greeting" id="dbGreeting">Good day</div>
            <h1 class="db-hero-title">Wise Invest <span class="db-gold">Control Panel</span></h1>
            <p class="db-hero-sub"><?= date('l, d F Y') ?> &nbsp;·&nbsp; All systems operational</p>
            <div class="db-chips">
                <span class="db-chip db-chip-live"><span class="db-chip-dot"></span> Platform Live</span>
                <span class="db-chip"><i class="fas fa-users"></i> <?= number_format($stats['total']) ?> Members</span>
                <span class="db-chip"><i class="fas fa-crown"></i> <?= number_format($stats['prem']) ?> Premium</span>
                <span class="db-chip"><i class="fas fa-film"></i> <?= number_format($stat_prem_vids + $stat_free_vids) ?> Videos</span>
            </div>
        </div>
        <div class="db-clock-wrap">
            <div class="db-clock" id="dbClock">00:00:00</div>
            <div class="db-clock-date" id="dbClockDate"></div>
        </div>
    </div>
</div>

<!-- ── KPI CARDS ── -->
<?php
$batchTotal = $stats['b1']+$stats['b2']+$stats['b3']+$stats['b4']+$stats['b5'];
$totalSafe  = max(1, $stats['total']);
$kpis = [
    ['#3b82f6', 'rgba(59,130,246,.1)',  'rgba(59,130,246,.12)',  'fas fa-users',  'ALL',        $stats['total'],   'Total Members',   100],
    ['#a78bfa', 'rgba(167,139,250,.1)', 'rgba(167,139,250,.12)', 'fas fa-gem',    'FULL ACCESS', $stats['diamond'], 'Diamond',         round($stats['diamond']/$totalSafe*100)],
    ['#d4af37', 'rgba(212,175,55,.12)', 'rgba(212,175,55,.15)',  'fas fa-crown',  'B1–B5',       $batchTotal,       'Batch Premium',   round($batchTotal/$totalSafe*100)],
    ['#64748b', '#f1f5f9',              '#e2e8f0',               'fas fa-user',   'BASIC',       $stats['free'],    'Free Users',      round($stats['free']/$totalSafe*100)],
];
?>
<div class="db-kpi-grid">
<?php foreach ($kpis as $i => [$kc,$kbg,$kpbg,$kicon,$ktag,$kval,$klbl,$kpct]): ?>
<div class="db-kpi" style="--kc:<?= $kc ?>;animation-delay:<?= .06*$i ?>s">
    <div class="db-kpi-top">
        <div class="db-kpi-icon" style="background:<?= $kbg ?>"><i class="<?= $kicon ?>" style="color:<?= $kc ?>"></i></div>
        <span class="db-kpi-pct" style="color:<?= $kc ?>;background:<?= $kpbg ?>"><?= $ktag ?></span>
    </div>
    <div class="db-kpi-num" data-target="<?= $kval ?>">0</div>
    <div class="db-kpi-lbl"><?= $klbl ?></div>
    <div class="db-kpi-bar"><div class="db-kpi-fill" data-w="<?= $kpct ?>" style="background:<?= $kc ?>"></div></div>
</div>
<?php endforeach; ?>
</div>

<!-- ── CHART + BATCH BARS ── -->
<?php
$circ  = 315;
$d_len = round($stats['diamond'] / $totalSafe * $circ, 2);
$b_len = round($batchTotal       / $totalSafe * $circ, 2);
$f_len = round($stats['free']    / $totalSafe * $circ, 2);
$paidPct = round($stats['prem']  / $totalSafe * 100);
$bData = [
    'b1' => ['#d4af37','SIM Batch-1'],
    'b2' => ['#3b82f6','SIM Batch-2'],
    'b3' => ['#10b981','SIM Batch-3'],
    'b4' => ['#f59e0b','SIM Batch-4'],
    'b5' => ['#ef4444','SIM Batch-5'],
];
$maxB = max(1, $stats['b1'], $stats['b2'], $stats['b3'], $stats['b4'], $stats['b5']);
?>
<div class="db-mid-row">

    <!-- Donut chart -->
    <div class="db-card">
        <div class="db-card-hd"><span><i class="fas fa-chart-pie"></i> Membership Split</span></div>
        <div class="db-donut-wrap">
            <svg viewBox="0 0 120 120" class="db-donut" style="transform:rotate(-90deg)">
                <circle cx="60" cy="60" r="48" fill="none" stroke="#f1f5f9" stroke-width="12"/>
                <circle cx="60" cy="60" r="48" fill="none" stroke="#334155" stroke-width="12"
                    class="db-ring-seg"
                    data-final="<?= $f_len ?> <?= $circ ?>"
                    stroke-dasharray="0 <?= $circ ?>"
                    stroke-dashoffset="-<?= round($d_len + $b_len, 2) ?>"/>
                <circle cx="60" cy="60" r="48" fill="none" stroke="#d4af37" stroke-width="12"
                    class="db-ring-seg"
                    data-final="<?= $b_len ?> <?= $circ ?>"
                    stroke-dasharray="0 <?= $circ ?>"
                    stroke-dashoffset="-<?= $d_len ?>"/>
                <circle cx="60" cy="60" r="48" fill="none" stroke="#a78bfa" stroke-width="12"
                    class="db-ring-seg"
                    data-final="<?= $d_len ?> <?= $circ ?>"
                    stroke-dasharray="0 <?= $circ ?>"
                    stroke-dashoffset="0"/>
            </svg>
            <div class="db-donut-ctr">
                <div class="db-donut-pct" data-target="<?= $paidPct ?>" id="dbPaidPct">0<span style="font-size:1.2rem">%</span></div>
                <div class="db-donut-lbl2">Paid Rate</div>
            </div>
        </div>
        <div class="db-legend">
            <div class="db-leg-row"><span class="db-leg-dot" style="background:#a78bfa"></span><span>Diamond</span><strong><?= number_format($stats['diamond']) ?></strong></div>
            <div class="db-leg-row"><span class="db-leg-dot" style="background:#d4af37"></span><span>Batch Premium</span><strong><?= number_format($batchTotal) ?></strong></div>
            <div class="db-leg-row"><span class="db-leg-dot" style="background:#334155"></span><span>Free</span><strong><?= number_format($stats['free']) ?></strong></div>
        </div>
    </div>

    <!-- Batch bars -->
    <div class="db-card">
        <div class="db-card-hd"><span><i class="fas fa-layer-group"></i> SIM Batch Breakdown</span></div>
        <?php foreach ($bData as $bk => [$bcol, $blbl]): $bpct = round($stats[$bk] / $maxB * 100); ?>
        <div class="db-batch-row">
            <div class="db-batch-lbl">
                <span class="db-batch-dot2" style="background:<?= $bcol ?>"></span>
                <span><?= $blbl ?></span>
            </div>
            <div class="db-batch-track">
                <div class="db-batch-fill" data-w="<?= $bpct ?>" style="background:<?= $bcol ?>"></div>
            </div>
            <div class="db-batch-ct" style="color:<?= $bcol ?>"><?= number_format($stats[$bk]) ?></div>
        </div>
        <?php endforeach; ?>

        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--ae)">
            <div style="font-size:.72rem;font-weight:700;color:var(--am);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">Batch vs Diamond</div>
            <div style="display:flex;gap:6px;align-items:center;">
                <div style="flex:<?= $batchTotal ?>;height:10px;background:linear-gradient(90deg,#d4af37,#f5d679);border-radius:99px 0 0 99px;min-width:2px"></div>
                <div style="flex:<?= $stats['diamond'] ?>;height:10px;background:linear-gradient(90deg,#a78bfa,#c4b5fd);border-radius:0 99px 99px 0;min-width:2px"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.72rem;margin-top:5px;color:var(--am)">
                <span style="color:#d4af37;font-weight:700">Batch (<?= $batchTotal ?>)</span>
                <span style="color:#a78bfa;font-weight:700">Diamond (<?= $stats['diamond'] ?>)</span>
            </div>
        </div>
    </div>
</div>

<!-- ── CONTENT + RECENT USERS ── -->
<div class="db-bot-row">

    <!-- Content library -->
    <div class="db-card">
        <div class="db-card-hd"><span><i class="fas fa-database"></i> Content Library</span></div>
        <div class="db-cnt-grid">
            <?php
            $cntItems = [
                ['videos',   'fas fa-play-circle', '#8b5cf6', $stat_prem_vids, 'Premium Videos'],
                ['videos',   'fas fa-film',         '#0ea5e9', $stat_free_vids, 'Free Videos'],
                ['news',     'fas fa-newspaper',    '#f97316', $stat_articles,  'Articles'],
                ['guides',   'fas fa-file-pdf',     '#10b981', $stat_guides,    'Guides'],
                ['glossary', 'fas fa-book-open',    '#d4af37', $stat_glossary,  'Terms'],
            ];
            foreach ($cntItems as [$sec,$ic,$cc,$cv,$cl]):
            ?>
            <div class="db-cnt-item" style="--cc:<?= $cc ?>" onclick="showSection('<?= $sec ?>')">
                <div class="db-cnt-icon"><i class="<?= $ic ?>" style="color:<?= $cc ?>"></i></div>
                <div class="db-cnt-num" data-target="<?= $cv ?>">0</div>
                <div class="db-cnt-lbl"><?= $cl ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent members -->
    <div class="db-card">
        <div class="db-card-hd">
            <span><i class="fas fa-user-plus"></i> Recent Members</span>
            <button onclick="showSection('users')" style="font-size:.72rem;font-weight:700;cursor:pointer;background:none;border:1.5px solid var(--ae);border-radius:7px;padding:4px 10px;color:var(--am);transition:all .2s;" onmouseover="this.style.borderColor='var(--ag)';this.style.color='var(--ag)'" onmouseout="this.style.borderColor='var(--ae)';this.style.color='var(--am)'">View All</button>
        </div>
        <?php
        $avColors = ['#6366f1','#8b5cf6','#ec4899','#f97316','#14b8a6','#0ea5e9'];
        $bm2 = ['diamond'=>['DIAMOND','#a78bfa'],'b1_premium'=>['B-1','#d4af37'],'b2_premium'=>['B-2','#3b82f6'],'b3_premium'=>['B-3','#10b981'],'b4_premium'=>['B-4','#f59e0b'],'b5_premium'=>['B-5','#ef4444'],'premium'=>['PREM','#d4af37'],'free'=>['FREE','#94a3b8']];
        foreach ($recent_users as $idx => $ru):
            $mt  = $ru['membership_type'];
            [$rb,$rc] = $bm2[$mt] ?? ['?','#94a3b8'];
            $avc3 = $avColors[abs(crc32($ru['fullname'])) % count($avColors)];
        ?>
        <div class="db-rec-row" style="animation:admSlideIn .4s <?= .05*$idx ?>s ease both">
            <div class="db-rec-av" style="background:<?= $avc3 ?>"><?= strtoupper(substr($ru['fullname'],0,1)) ?></div>
            <div class="db-rec-info">
                <div class="db-rec-name"><?= htmlspecialchars($ru['fullname']) ?></div>
                <div class="db-rec-date"><?= date('d M Y', strtotime($ru['created_at'])) ?></div>
            </div>
            <span class="db-rec-badge" style="color:<?= $rc ?>;border-color:<?= $rc ?>44;background:<?= $rc ?>18"><?= $rb ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recent_users)): ?>
        <div style="text-align:center;padding:24px;color:#94a3b8;font-size:.84rem;"><i class="fas fa-users" style="display:block;font-size:1.5rem;margin-bottom:8px;opacity:.3"></i>No members yet.</div>
        <?php endif; ?>
    </div>
</div>

<!-- ── QUICK NAVIGATION ── -->
<div class="db-card" style="margin-bottom:16px">
    <div class="db-card-hd"><span><i class="fas fa-bolt"></i> Quick Navigation</span></div>
    <div class="db-quick-grid">
    <?php
    $qnav = [
        ['users',   'fas fa-users',       'Users',    'Manage accounts & tiers', '#3b82f6'],
        ['news',    'fas fa-newspaper',   'Articles', 'Publish learning content', '#f97316'],
        ['glossary','fas fa-book-open',   'Glossary', 'Investment terminology',   '#d4af37'],
        ['videos',  'fas fa-play-circle', 'Videos',   'Premium & free library',   '#8b5cf6'],
        ['guides',  'fas fa-file-pdf',    'Guides',   'PDF materials',            '#10b981'],
        ['chat',    'fas fa-comments',    'Support',  'Member conversations',     '#ec4899'],
    ];
    foreach ($qnav as [$sid,$ic,$ql,$qs,$qc]):
    ?>
    <div class="db-qcard" style="--qc:<?= $qc ?>" onclick="showSection('<?= $sid ?>')">
        <div class="db-q-icon"><i class="<?= $ic ?>"></i></div>
        <div class="db-q-lbl"><?= $ql ?></div>
        <div class="db-q-sub"><?= $qs ?></div>
        <div class="db-q-arrow"><i class="fas fa-arrow-right"></i></div>
    </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- ── BULK ACTIONS ── -->
<div class="db-card">
    <div class="db-card-hd"><span><i class="fas fa-terminal"></i> Bulk Actions</span></div>
    <div class="db-bulk-row">
        <form method="POST" onsubmit="return confirm('Gift ALL users 3 months of premium?');" style="flex:1;min-width:200px">
            <input type="hidden" name="gift_all_3m" value="1">
            <button type="submit" class="db-bulk-btn" style="--bbc:#10b981">
                <div class="db-bicon" style="background:rgba(16,185,129,.12);color:#10b981"><i class="fas fa-gift"></i></div>
                <div class="db-btxt"><strong>Gift Everyone 3 Months</strong><span>Upgrade all users to premium for 3 months</span></div>
            </button>
        </form>
        <form method="POST" onsubmit="return confirm('Reset all lifetime memberships to 3 months?');" style="flex:1;min-width:200px">
            <input type="hidden" name="convert_lifetime_to_3m" value="1">
            <button type="submit" class="db-bulk-btn" style="--bbc:#ef4444">
                <div class="db-bicon" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="fas fa-redo"></i></div>
                <div class="db-btxt"><strong>Reset Lifetime → 3 Months</strong><span>Convert all lifetime plans to 3-month plans</span></div>
            </button>
        </form>
    </div>
</div>

</div><!-- /dashboard -->


<!-- ┌───────┐ -->
<!-- │ USERS │ -->
<!-- └───────┘ -->
<style>
/* ══ USERS — filter bar, checkboxes, bulk bar (matches admin gold/light theme) ══ */
.auf-bar { background:var(--ac); border:1px solid var(--ae); border-radius:var(--ar); padding:16px; margin-bottom:16px; display:flex; flex-direction:column; gap:12px; }
.auf-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.auf-lbl { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.5px; color:var(--am); margin-right:2px; }
.auf-search { flex:1; min-width:200px; display:flex; align-items:center; gap:8px; background:#f8fafc; border:1px solid var(--ae); border-radius:10px; padding:9px 13px; }
.auf-search:focus-within { border-color:var(--ag); box-shadow:0 0 0 3px rgba(212,175,55,.1); background:#fff; }
.auf-search i { color:var(--am); font-size:.85rem; }
.auf-search input { border:none; background:transparent; outline:none; font-family:inherit; font-size:.88rem; color:var(--at); width:100%; }
.upill { padding:6px 13px; border-radius:99px; border:1px solid var(--ae); background:#fff; color:var(--am); font-size:.77rem; font-weight:700; cursor:pointer; transition:.18s; font-family:inherit; }
.upill:hover { border-color:var(--ag); color:var(--at); }
.upill.on { background:linear-gradient(135deg,var(--ag),var(--ag2)); border-color:transparent; color:#0f172a; box-shadow:0 3px 10px rgba(212,175,55,.25); }
.auf-date { padding:7px 11px; border:1px solid var(--ae); border-radius:8px; font-family:inherit; font-size:.8rem; color:var(--at); background:#fff; }
.auf-foot { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; padding-top:4px; border-top:1px dashed var(--ae); }
.auf-count { font-size:.82rem; font-weight:700; color:var(--at); }
.auf-count b { color:var(--ag); }
.auf-clear { background:none; border:none; color:var(--am); font-size:.8rem; font-weight:700; cursor:pointer; text-decoration:underline; font-family:inherit; }
.auf-clear:hover { color:#991b1b; }
#auf_custom { display:none; gap:8px; align-items:center; }
#auf_custom.on { display:flex; }

/* checkbox cells */
.atable .ucb { width:18px; height:18px; accent-color:var(--ag); cursor:pointer; vertical-align:middle; }
.atable th.uc-col, .atable td.uc-col { width:42px; text-align:center; }
.u-joined { font-size:.8rem; color:var(--at); font-weight:600; }
.u-joined small { display:block; font-size:.68rem; color:var(--am); font-weight:500; }

/* bulk action bar — sticky bottom */
.abb { position:fixed; left:50%; bottom:20px; transform:translateX(-50%) translateY(140%);
       width:min(920px,calc(100vw - 40px)); background:#0c1628; border:1px solid rgba(212,175,55,.3);
       border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.45); padding:16px 18px; z-index:9000;
       transition:transform .35s cubic-bezier(.34,1.56,.64,1); }
.abb.show { transform:translateX(-50%) translateY(0); }
.abb-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.abb-sel { font-size:.92rem; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px; }
.abb-sel i { color:var(--ag); }
.abb-x { background:none; border:none; color:#94a3b8; font-size:1.1rem; cursor:pointer; }
.abb-x:hover { color:#fff; }
.abb-grid { display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap; }
.abb-fld { display:flex; flex-direction:column; gap:5px; }
.abb-fld label { font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; }
.abb-fld select, .abb-fld input { padding:9px 12px; border-radius:9px; border:1px solid rgba(255,255,255,.15); background:#0a1322; color:#e2e8f0; font-family:inherit; font-size:.85rem; outline:none; }
.abb-fld select:focus, .abb-fld input:focus { border-color:var(--ag); }
.abb-dur { display:flex; gap:6px; flex-wrap:wrap; }
.abb-dur .upill { background:#0a1322; border-color:rgba(255,255,255,.15); color:#94a3b8; }
.abb-dur .upill.on { background:linear-gradient(135deg,var(--ag),var(--ag2)); color:#0f172a; border-color:transparent; }
.abb-actions { display:flex; gap:8px; margin-left:auto; }
#abb_notes { min-width:180px; }
#abb_customdate { display:none; }
#abb_customdate.on { display:block; }

/* confirm modal + toast */
.umodal { position:fixed; inset:0; background:rgba(2,6,23,.7); backdrop-filter:blur(4px); z-index:9500; display:none; align-items:center; justify-content:center; padding:20px; }
.umodal.show { display:flex; }
.umodal-card { background:#0c1628; border:1px solid rgba(212,175,55,.3); border-radius:18px; padding:28px; width:min(440px,100%); box-shadow:0 30px 80px rgba(0,0,0,.5); }
.umodal-card h3 { color:#fff; margin:0 0 10px; font-size:1.15rem; }
.umodal-card p { color:#94a3b8; font-size:.88rem; line-height:1.6; margin:0 0 20px; }
.umodal-card .hl { color:var(--ag); font-weight:800; }
.umodal-foot { display:flex; gap:10px; justify-content:flex-end; }
.uprog { display:none; align-items:center; gap:10px; color:#e2e8f0; font-size:.88rem; margin-bottom:16px; }
.uprog.show { display:flex; }
.uspin { width:18px; height:18px; border:2px solid rgba(212,175,55,.3); border-top-color:var(--ag); border-radius:50%; animation:uspin 0.7s linear infinite; }
@keyframes uspin { to { transform:rotate(360deg); } }
.utoast { position:fixed; top:20px; right:20px; z-index:9999; background:#0c1628; border:1px solid rgba(255,255,255,.12); border-left:4px solid #10b981; border-radius:12px; padding:14px 18px; color:#e2e8f0; font-size:.88rem; font-weight:600; box-shadow:0 16px 40px rgba(0,0,0,.4); transform:translateX(140%); transition:transform .4s cubic-bezier(.34,1.56,.64,1); max-width:340px; }
.utoast.show { transform:translateX(0); }
.utoast.err { border-left-color:#ef4444; }

@media (max-width:640px){
  .abb { bottom:0; border-radius:16px 16px 0 0; width:100%; }
  .abb-grid { gap:10px; }
  .abb-actions { margin-left:0; width:100%; }
  .abb-actions .abtn { flex:1; }
}
</style>
<div id="users" class="asec">
    <div class="aph">
        <div>
            <h1 class="aph-title">User <span>Management</span></h1>
            <p class="aph-sub"><?= number_format($total_users) ?> members</p>
        </div>
        <!-- Preserved no-JS fallback search (enhanced, not replaced) -->
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="tab" value="users">
            <div class="asw">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Server search…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="abtn abtn-gh abtn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <!-- ══ FILTER BAR (vanilla JS, no reloads) ══ -->
    <div class="auf-bar">
        <div class="auf-row">
            <div class="auf-search">
                <i class="fas fa-search"></i>
                <input type="text" id="auf_q" placeholder="Search by name or email…" autocomplete="off">
            </div>
        </div>
        <div class="auf-row">
            <span class="auf-lbl">Level</span>
            <button class="upill on" data-lvl="all">All</button>
            <button class="upill" data-lvl="free">Free</button>
            <button class="upill" data-lvl="b1_premium">B-1</button>
            <button class="upill" data-lvl="b2_premium">B-2</button>
            <button class="upill" data-lvl="b3_premium">B-3</button>
            <button class="upill" data-lvl="b4_premium">B-4</button>
            <button class="upill" data-lvl="b5_premium">B-5</button>
            <button class="upill" data-lvl="diamond">Diamond</button>
            <button class="upill" data-lvl="premium">Legacy</button>
        </div>
        <div class="auf-row">
            <span class="auf-lbl">Joined</span>
            <button class="upill on" data-days="all">All time</button>
            <button class="upill" data-days="7">Last 7 days</button>
            <button class="upill" data-days="30">Last 30 days</button>
            <button class="upill" data-days="90">Last 90 days</button>
            <button class="upill" data-days="year">This year</button>
            <button class="upill" data-days="custom">Custom</button>
            <span id="auf_custom">
                <input type="date" class="auf-date" id="auf_from">
                <span style="color:var(--am);">→</span>
                <input type="date" class="auf-date" id="auf_to">
            </span>
        </div>
        <div class="auf-foot">
            <span class="auf-count">Showing <b id="auf_shown">0</b> of <?= number_format($total_users) ?> users</span>
            <button class="auf-clear" id="auf_clear"><i class="fas fa-times"></i> Clear filters</button>
        </div>
    </div>

    <div class="acard">
        <div class="atw">
            <table class="atable" id="users_table">
                <thead>
                    <tr>
                        <th class="uc-col"><input type="checkbox" class="ucb" id="u_all" title="Select all"></th>
                        <th>Member</th>
                        <th>Tier</th>
                        <th>Joined</th>
                        <th>Expires</th>
                        <th>Change Plan</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="users_tbody">
                <?php
                $avc = ['#6366f1','#8b5cf6','#ec4899','#f97316','#14b8a6','#0ea5e9','#84cc16'];
                $ai  = 0;
                $bmap = [
                    'diamond'    => ['DIAMOND',    'bd',  'gem'],
                    'b1_premium' => ['B-1 PREMIUM','bb1', 'crown'],
                    'b2_premium' => ['B-2 PREMIUM','bb2', 'crown'],
                    'b3_premium' => ['B-3 PREMIUM','bb3', 'crown'],
                    'b4_premium' => ['B-4 PREMIUM','bb4', 'crown'],
                    'b5_premium' => ['B-5 PREMIUM','bb5', 'crown'],
                    'premium'    => ['PREMIUM',    'bl',  'crown'],
                    'free'       => ['FREE',       'bf',  'user'],
                ];
                if ($users_res && $users_res->num_rows > 0):
                    while ($u = $users_res->fetch_assoc()):
                        $mt = $u['membership_type'];
                        [$blbl, $bcls, $bicn] = $bmap[$mt] ?? ['UNKNOWN','bf','user'];
                        $col = $avc[$ai % count($avc)]; $ai++;
                        $joined_ts  = !empty($u['created_at']) ? strtotime($u['created_at']) : 0;
                        $joined_ymd = $joined_ts ? date('Y-m-d', $joined_ts) : '';
                        $rel = '—';
                        if ($joined_ts) {
                            $d = floor((time() - $joined_ts) / 86400);
                            $rel = $d <= 0 ? 'today' : ($d < 30 ? $d.'d ago' : ($d < 365 ? floor($d/30).'mo ago' : floor($d/365).'y ago'));
                        }
                ?>
                <tr class="u-row"
                    data-id="<?= (int)$u['id'] ?>"
                    data-name="<?= htmlspecialchars(strtolower($u['fullname']), ENT_QUOTES) ?>"
                    data-email="<?= htmlspecialchars(strtolower($u['email']), ENT_QUOTES) ?>"
                    data-level="<?= htmlspecialchars($mt, ENT_QUOTES) ?>"
                    data-joined="<?= $joined_ymd ?>">
                    <td class="uc-col"><input type="checkbox" class="ucb u-pick" value="<?= (int)$u['id'] ?>"></td>
                    <td>
                        <div class="aur">
                            <div class="aav" style="background:<?= $col ?>"><?= strtoupper(substr($u['fullname'],0,1)) ?></div>
                            <div>
                                <div class="aun"><?= htmlspecialchars($u['fullname']) ?></div>
                                <div class="aue"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="abadge <?= $bcls ?>"><i class="fas fa-<?= $bicn ?>"></i> <?= $blbl ?></span>
                    </td>
                    <td class="u-joined">
                        <?= $joined_ts ? date('M j, Y', $joined_ts) : '—' ?>
                        <?php if ($joined_ts): ?><small><?= $rel ?></small><?php endif; ?>
                    </td>
                    <td style="font-size:.8rem;color:var(--am);font-weight:600;">
                        <?php if ($mt !== 'free'): ?>
                            <?= $u['premium_expiry'] ? date('d M Y', strtotime($u['premium_expiry'])) : '<span style="color:#7c3aed;font-weight:800;">LIFETIME</span>' ?>
                        <?php else: echo '—'; endif; ?>
                    </td>
                    <td>
                        <!-- PRESERVED: existing per-user upgrade form -->
                        <form method="POST" class="aupg">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="upgrade_user" value="1">
                            <select name="batch_type">
                                <option value="b1_premium" <?= $mt==='b1_premium'?'selected':''?>>B-1 Premium</option>
                                <option value="b2_premium" <?= $mt==='b2_premium'?'selected':''?>>B-2 Premium</option>
                                <option value="b3_premium" <?= $mt==='b3_premium'?'selected':''?>>B-3 Premium</option>
                                <option value="b4_premium" <?= $mt==='b4_premium'?'selected':''?>>B-4 Premium</option>
                                <option value="b5_premium" <?= $mt==='b5_premium'?'selected':''?>>B-5 Premium</option>
                                <option value="diamond"    <?= $mt==='diamond'?'selected':''?>>♦ Diamond</option>
                            </select>
                            <select name="duration">
                                <option value="1m">1 Month</option>
                                <option value="3m">3 Months</option>
                                <option value="6m">6 Months</option>
                                <option value="1y">1 Year</option>
                                <option value="life">Lifetime</option>
                            </select>
                            <button class="abtn abtn-g abtn-sm"><i class="fas fa-arrow-up"></i> <?= $mt!=='free'?'Change':'Upgrade' ?></button>
                        </form>
                    </td>
                    <td>
                        <?php if ($mt !== 'free'): ?>
                        <form method="POST" onsubmit="return confirm('Revoke access for <?= htmlspecialchars(addslashes($u['fullname'])) ?>?');">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="downgrade_user" value="1">
                            <button class="abtn abtn-r abtn-sm"><i class="fas fa-ban"></i> Revoke</button>
                        </form>
                        <?php else: echo '<span style="color:#94a3b8;font-size:.78rem;">—</span>'; endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div id="users_none" style="display:none;text-align:center;padding:40px;color:#94a3b8;">
                <i class="fas fa-filter" style="font-size:1.4rem;display:block;margin-bottom:8px;"></i>
                No users match your filters.
            </div>
        </div>
        <div class="apg" id="users_pager" style="display:none;"></div>
    </div>
</div><!-- /users -->

<!-- ══ BULK ACTION BAR ══ -->
<div class="abb" id="bulkBar">
    <div class="abb-top">
        <div class="abb-sel"><i class="fas fa-check-circle"></i> <span id="abb_count">0</span> users selected</div>
        <button class="abb-x" id="abb_cancel" title="Clear selection"><i class="fas fa-times"></i></button>
    </div>
    <div class="abb-grid">
        <div class="abb-fld">
            <label>New Level</label>
            <select id="abb_level">
                <option value="free">Free</option>
                <option value="b1_premium">Bronze (B-1)</option>
                <option value="b2_premium">Silver (B-2)</option>
                <option value="b3_premium">Gold (B-3)</option>
                <option value="b4_premium">Platinum (B-4)</option>
                <option value="b5_premium">Diamond Low (B-5)</option>
                <option value="diamond" selected>Diamond (Full)</option>
                <option value="premium">Legacy All-Access</option>
            </select>
        </div>
        <div class="abb-fld">
            <label>Duration</label>
            <div class="abb-dur" id="abb_dur">
                <button class="upill on" data-dur="life">Permanent</button>
                <button class="upill" data-dur="30d">30 days</button>
                <button class="upill" data-dur="90d">90 days</button>
                <button class="upill" data-dur="6m">6 months</button>
                <button class="upill" data-dur="1y">1 year</button>
                <button class="upill" data-dur="custom">Custom</button>
                <input type="date" class="auf-date" id="abb_customdate">
            </div>
        </div>
        <div class="abb-fld">
            <label>Notes (optional)</label>
            <input type="text" id="abb_notes" placeholder="e.g. promo batch">
        </div>
        <div class="abb-actions">
            <button class="abtn abtn-gh abtn-sm" id="abb_cancel2">Cancel</button>
            <button class="abtn abtn-g abtn-sm" id="abb_apply"><i class="fas fa-arrow-up"></i> Upgrade Selected</button>
        </div>
    </div>
</div>

<!-- confirm modal -->
<div class="umodal" id="bulkModal">
    <div class="umodal-card">
        <h3><i class="fas fa-arrow-up" style="color:var(--ag);"></i> Confirm bulk upgrade</h3>
        <div class="uprog" id="bulkProg"><span class="uspin"></span> Upgrading users…</div>
        <p id="bulkMsg"></p>
        <div class="umodal-foot">
            <button class="abtn abtn-gh abtn-sm" id="bulkNo">Cancel</button>
            <button class="abtn abtn-g abtn-sm" id="bulkYes">Yes, upgrade</button>
        </div>
    </div>
</div>
<div class="utoast" id="uToast"></div>

<script>
(function(){
  var rows   = Array.prototype.slice.call(document.querySelectorAll('#users_tbody .u-row'));
  if (!document.getElementById('users_table')) return;
  var PER = 50, curPage = 1;
  var q = document.getElementById('auf_q');
  var levelPills = document.querySelectorAll('.auf-row [data-lvl]');
  var datePills  = document.querySelectorAll('.auf-row [data-days]');
  var activeLevel = 'all', activeDays = 'all';
  var fromEl = document.getElementById('auf_from'), toEl = document.getElementById('auf_to');

  function withinDate(ymd){
    if (!ymd) return activeDays === 'all';
    if (activeDays === 'all') return true;
    var d = new Date(ymd + 'T00:00:00'), now = new Date();
    if (activeDays === 'year') return d.getFullYear() === now.getFullYear();
    if (activeDays === 'custom'){
      if (fromEl.value && ymd < fromEl.value) return false;
      if (toEl.value && ymd > toEl.value) return false;
      return true;
    }
    var diff = (now - d) / 86400000;
    return diff <= parseInt(activeDays, 10);
  }

  function apply(){
    var term = (q.value || '').trim().toLowerCase();
    var matched = rows.filter(function(r){
      if (activeLevel !== 'all' && r.dataset.level !== activeLevel) return false;
      if (!withinDate(r.dataset.joined)) return false;
      if (term && r.dataset.name.indexOf(term) === -1 && r.dataset.email.indexOf(term) === -1) return false;
      return true;
    });
    document.getElementById('auf_shown').textContent = matched.length;
    rows.forEach(function(r){ r.style.display = 'none'; });
    var pages = Math.max(1, Math.ceil(matched.length / PER));
    if (curPage > pages) curPage = pages;
    var start = (curPage - 1) * PER;
    matched.slice(start, start + PER).forEach(function(r){ r.style.display = ''; });
    document.getElementById('users_none').style.display = matched.length ? 'none' : 'block';
    renderPager(pages);
    syncSelectAll();
  }

  function renderPager(pages){
    var el = document.getElementById('users_pager');
    if (pages <= 1){ el.style.display='none'; el.innerHTML=''; return; }
    el.style.display='flex'; var h='';
    if (curPage>1) h += "<a class='apb' data-p='"+(curPage-1)+"'><i class='fas fa-chevron-left'></i></a>";
    for (var p=1;p<=pages;p++) h += "<a class='apb "+(p===curPage?'active':'')+"' data-p='"+p+"'>"+p+"</a>";
    if (curPage<pages) h += "<a class='apb' data-p='"+(curPage+1)+"'><i class='fas fa-chevron-right'></i></a>";
    el.innerHTML = h;
    el.querySelectorAll('[data-p]').forEach(function(a){
      a.addEventListener('click', function(){ curPage = parseInt(this.dataset.p,10); apply(); });
    });
  }

  q.addEventListener('input', function(){ curPage=1; apply(); });
  levelPills.forEach(function(b){ b.addEventListener('click', function(){
    levelPills.forEach(function(x){x.classList.remove('on');}); this.classList.add('on');
    activeLevel = this.dataset.lvl; curPage=1; apply();
  });});
  datePills.forEach(function(b){ b.addEventListener('click', function(){
    datePills.forEach(function(x){x.classList.remove('on');}); this.classList.add('on');
    activeDays = this.dataset.days;
    document.getElementById('auf_custom').classList.toggle('on', activeDays==='custom');
    curPage=1; apply();
  });});
  if (fromEl) fromEl.addEventListener('change', function(){ curPage=1; apply(); });
  if (toEl)   toEl.addEventListener('change', function(){ curPage=1; apply(); });
  document.getElementById('auf_clear').addEventListener('click', function(){
    q.value=''; activeLevel='all'; activeDays='all';
    levelPills.forEach(function(x){x.classList.toggle('on', x.dataset.lvl==='all');});
    datePills.forEach(function(x){x.classList.toggle('on', x.dataset.days==='all');});
    document.getElementById('auf_custom').classList.remove('on');
    if(fromEl)fromEl.value=''; if(toEl)toEl.value=''; curPage=1; apply();
  });

  /* ── selection + bulk bar ── */
  var bar = document.getElementById('bulkBar');
  function picked(){ return rows.filter(function(r){ var c=r.querySelector('.u-pick'); return c && c.checked; }); }
  function refreshBar(){
    var n = picked().length;
    document.getElementById('abb_count').textContent = n;
    bar.classList.toggle('show', n>0);
  }
  function syncSelectAll(){
    var vis = rows.filter(function(r){ return r.style.display!=='none'; });
    var all = document.getElementById('u_all');
    all.checked = vis.length>0 && vis.every(function(r){ return r.querySelector('.u-pick').checked; });
  }
  document.getElementById('users_tbody').addEventListener('change', function(e){
    if (e.target.classList.contains('u-pick')){ refreshBar(); syncSelectAll(); }
  });
  document.getElementById('u_all').addEventListener('change', function(){
    var on=this.checked;
    rows.filter(function(r){ return r.style.display!=='none'; })
        .forEach(function(r){ r.querySelector('.u-pick').checked=on; });
    refreshBar();
  });
  function clearSel(){ rows.forEach(function(r){ r.querySelector('.u-pick').checked=false; }); document.getElementById('u_all').checked=false; refreshBar(); }
  document.getElementById('abb_cancel').addEventListener('click', clearSel);
  document.getElementById('abb_cancel2').addEventListener('click', clearSel);

  /* duration pills */
  var activeDur='life';
  document.querySelectorAll('#abb_dur [data-dur]').forEach(function(b){
    b.addEventListener('click', function(){
      document.querySelectorAll('#abb_dur [data-dur]').forEach(function(x){x.classList.remove('on');});
      this.classList.add('on'); activeDur=this.dataset.dur;
      document.getElementById('abb_customdate').classList.toggle('on', activeDur==='custom');
    });
  });

  /* toast */
  function toast(msg, isErr){
    var t=document.getElementById('uToast'); t.textContent=msg; t.classList.toggle('err',!!isErr);
    t.classList.add('show'); setTimeout(function(){ t.classList.remove('show'); }, 3500);
  }

  /* confirm modal → fetch */
  var modal=document.getElementById('bulkModal');
  document.getElementById('abb_apply').addEventListener('click', function(){
    var ids=picked().map(function(r){ return parseInt(r.dataset.id,10); });
    if(!ids.length){ toast('No users selected', true); return; }
    if(activeDur==='custom' && !document.getElementById('abb_customdate').value){ toast('Pick a custom date', true); return; }
    var lvl=document.getElementById('abb_level').selectedOptions[0].text;
    document.getElementById('bulkMsg').innerHTML =
      'Upgrade <span class="hl">'+ids.length+'</span> user(s) to <span class="hl">'+lvl+'</span>?';
    document.getElementById('bulkProg').classList.remove('show');
    document.getElementById('bulkYes').style.display='';
    modal.classList.add('show');
  });
  document.getElementById('bulkNo').addEventListener('click', function(){ modal.classList.remove('show'); });
  document.getElementById('bulkYes').addEventListener('click', function(){
    var ids=picked().map(function(r){ return parseInt(r.dataset.id,10); });
    var payload={
      user_ids: ids,
      new_level: document.getElementById('abb_level').value,
      duration: activeDur,
      custom_date: document.getElementById('abb_customdate').value || null,
      notes: document.getElementById('abb_notes').value || null
    };
    document.getElementById('bulkProg').classList.add('show');
    document.getElementById('bulkYes').style.display='none';
    fetch('/api/bulk-upgrade-users.php', {
      method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
    }).then(function(r){ return r.json(); }).then(function(d){
      modal.classList.remove('show');
      if(d && d.success){
        toast('✓ Upgraded '+d.upgraded_count+' user(s). Reloading…');
        setTimeout(function(){ location.href='admin.php?tab=users'; }, 1200);
      } else { toast((d && d.error) || 'Upgrade failed', true); }
    }).catch(function(){ modal.classList.remove('show'); toast('Network error', true); });
  });

  apply();
})();
</script>


<!-- ┌──────────┐ -->
<!-- │ ARTICLES │ -->
<!-- └──────────┘ -->
<div id="news" class="asec">
    <div class="aph">
        <div>
            <h1 class="aph-title">Article <span>Manager</span></h1>
            <p class="aph-sub">Publish and manage learning content</p>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1.7fr;gap:18px;">
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-plus-circle"></i> New Article</h3></div>
            <div class="acard-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_article" value="1">
                    <label class="albl">Title</label>
                    <input type="text" name="a_title" class="ainp" placeholder="Article title" required>
                    <label class="albl">Category</label>
                    <select name="a_category" class="asel">
                        <option value="Brokerage Knowledges">Brokerage Knowledges</option>
                        <option value="Investment Knowledges">Investment Knowledges</option>
                    </select>
                    <label class="albl">Thumbnail</label>
                    <input type="file" name="a_thumbnail" class="ainp" accept="image/*" required>
                    <label class="albl">Content</label>
                    <textarea name="a_content" class="ainp atea" rows="5" placeholder="Write your article…" required></textarea>
                    <button type="submit" class="abtn abtn-g abtn-full" style="margin-top:10px;">
                        <i class="fas fa-paper-plane"></i> Publish
                    </button>
                </form>
            </div>
        </div>
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-list"></i> Published</h3></div>
            <div class="atw">
                <table class="atable">
                    <thead><tr><th>Title</th><th>Category</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($articles_res && $articles_res->num_rows > 0): ?>
                        <?php while ($art = $articles_res->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= htmlspecialchars($art['summary'] ?? '') ?>
                            </td>
                            <td><span class="abadge bf"><?= htmlspecialchars($art['category']) ?></span></td>
                            <td style="font-size:.76rem;color:var(--am);"><?= date('d M Y',strtotime($art['published_date'])) ?></td>
                            <td>
                                <div style="display:flex;gap:5px;">
                                    <button class="abtn abtn-gh abtn-sm" onclick="openEditArticle(<?= htmlspecialchars(json_encode([
                                        'id'       => $art['id'],
                                        'title'    => $art['summary'] ?? '',
                                        'category' => $art['category'],
                                        'content'  => $art['content'] ?? '',
                                    ]), ENT_QUOTES) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Delete?');">
                                        <input type="hidden" name="article_id" value="<?= $art['id'] ?>">
                                        <input type="hidden" name="delete_article" value="1">
                                        <button class="abtn abtn-r abtn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;padding:30px;color:#94a3b8;">No articles yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /news -->


<!-- ┌──────────┐ -->
<!-- │ GLOSSARY │ -->
<!-- └──────────┘ -->
<div id="glossary" class="asec">
    <div class="aph">
        <div>
            <h1 class="aph-title">Financial <span>Glossary</span></h1>
            <p class="aph-sub">Investment terms and definitions</p>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:18px;">
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-plus-circle"></i> Add Term</h3></div>
            <div class="acard-body">
                <form method="POST">
                    <input type="hidden" name="add_term" value="1">
                    <label class="albl">Term</label>
                    <input type="text" name="g_term" class="ainp" placeholder="e.g. CAGR" required>
                    <label class="albl">Definition</label>
                    <textarea name="g_def" class="ainp atea" rows="4" placeholder="Explain this term…" required></textarea>
                    <label class="albl">Equation (optional)</label>
                    <input type="text" name="g_eq" class="ainp" placeholder="e.g. (EV / EBITDA)">
                    <button type="submit" class="abtn abtn-g abtn-full" style="margin-top:10px;">
                        <i class="fas fa-plus"></i> Add Term
                    </button>
                </form>
            </div>
        </div>
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-list"></i> All Terms</h3></div>
            <div class="atw" style="max-height:500px;overflow-y:auto;">
                <table class="atable">
                    <thead><tr><th>Term</th><th>Preview</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($glossary_res && $glossary_res->num_rows > 0): ?>
                        <?php while ($g = $glossary_res->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($g['term']) ?></strong></td>
                            <td style="color:var(--am);font-size:.78rem;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= htmlspecialchars(substr($g['definition'],0,80)) ?>…
                            </td>
                            <td>
                                <div style="display:flex;gap:5px;">
                                    <button class="abtn abtn-gh abtn-sm" onclick="openEditTerm(<?= htmlspecialchars(json_encode([
                                        'id'  => $g['id'],
                                        'term'=> $g['term'],
                                        'def' => $g['definition'],
                                        'eq'  => $g['equation'] ?? '',
                                    ]), ENT_QUOTES) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Delete term?');">
                                        <input type="hidden" name="term_id" value="<?= $g['id'] ?>">
                                        <input type="hidden" name="delete_term" value="1">
                                        <button class="abtn abtn-r abtn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center;padding:30px;color:#94a3b8;">No terms yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /glossary -->


<!-- ┌────────┐ -->
<!-- │ VIDEOS │ -->
<!-- └────────┘ -->
<div id="videos" class="asec">
    <div class="aph">
        <div>
            <h1 class="aph-title">Video <span>Library</span></h1>
            <p class="aph-sub">Manage premium and free video content</p>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:250px 1fr;gap:18px;margin-bottom:18px;">
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-folder-plus"></i> Folders</h3></div>
            <div class="acard-body">
                <form method="POST">
                    <input type="hidden" name="add_folder" value="1">
                    <label class="albl">Folder Name</label>
                    <input type="text" name="folder_name" class="ainp" placeholder="e.g. Day Trading" required>
                    <button class="abtn abtn-g abtn-full" style="margin-top:8px;">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </form>
            </div>
        </div>
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-upload"></i> Upload Video</h3></div>
            <div class="acard-body">
                <form method="POST">
                    <input type="hidden" name="add_video" value="1">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="afield">
                            <label class="albl">Title</label>
                            <input type="text" name="v_title" class="ainp" placeholder="Video title" required>
                        </div>
                        <div class="afield">
                            <label class="albl">YouTube URL</label>
                            <input type="text" name="v_url" class="ainp" placeholder="https://youtube.com/…" required>
                        </div>
                        <div class="afield">
                            <label class="albl">Library</label>
                            <select name="video_type" class="asel">
                                <option value="free">Free Library</option>
                                <option value="premium">Premium Hub</option>
                            </select>
                        </div>
                        <div class="afield">
                            <label class="albl">Batch Access</label>
                            <select name="video_batch" class="asel">
                                <option value="">All Premium Members</option>
                                <option value="b1">Batch-1 only</option>
                                <option value="b2">Batch-2 only</option>
                                <option value="b3">Batch-3 only</option>
                                <option value="b4">Batch-4 only</option>
                                <option value="b5">Batch-5 only</option>
                            </select>
                        </div>
                        <div class="afield">
                            <label class="albl">Folder</label>
                            <select name="folder_id" class="asel">
                                <option value="0">None / Uncategorized</option>
                                <?php foreach ($folders_arr as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="afield">
                            <label class="albl">Description</label>
                            <input type="text" name="v_desc" class="ainp" placeholder="Short description">
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
                        <label class="aclbl">
                            <input type="checkbox" name="is_locked" value="1"> Lock this video
                        </label>
                        <button class="abtn abtn-g"><i class="fas fa-upload"></i> Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head"><h3 class="acard-title"><i class="fas fa-film"></i> All Videos</h3></div>
        <div class="atw" style="max-height:420px;overflow-y:auto;">
            <table class="atable">
                <thead>
                    <tr><th>Title</th><th>Type</th><th>Batch</th><th>Folder</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($videos_res && $videos_res->num_rows > 0):
                    while ($v = $videos_res->fetch_assoc()): ?>
                <tr>
                    <td style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= htmlspecialchars($v['title']) ?>
                    </td>
                    <td>
                        <?php if ($v['video_type']==='free'): ?>
                            <span class="abadge bf">FREE</span>
                        <?php else: ?>
                            <span class="abadge bp">PREMIUM</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="change_video_batch" value="1">
                            <input type="hidden" name="video_id" value="<?= $v['id'] ?>">
                            <select name="video_batch"
                                    onchange="this.form.submit()"
                                    style="padding:5px 8px;border:1.5px solid var(--ae);border-radius:7px;
                                           font-size:.76rem;background:#fff;cursor:pointer;
                                           font-family:inherit;outline:none;color:var(--at);">
                                <option value=""          <?= empty($v['batch'])        ?'selected':''?>>All (no batch)</option>
                                <option value="b1" style="color:#a0801c;" <?= $v['batch']==='b1'?'selected':''?>>SIM Batch-1</option>
                                <option value="b2" style="color:#1d4ed8;" <?= $v['batch']==='b2'?'selected':''?>>SIM Batch-2</option>
                                <option value="b3" style="color:#065f46;" <?= $v['batch']==='b3'?'selected':''?>>SIM Batch-3</option>
                                <option value="b4" style="color:#92400e;" <?= $v['batch']==='b4'?'selected':''?>>SIM Batch-4</option>
                                <option value="b5" style="color:#991b1b;" <?= $v['batch']==='b5'?'selected':''?>>SIM Batch-5</option>
                            </select>
                        </form>
                    </td>
                    <td style="font-size:.8rem;color:var(--am);">
                        <?= $v['folder_name'] ? htmlspecialchars($v['folder_name']) : '—' ?>
                    </td>
                    <td>
                        <?php if ($v['is_locked']): ?>
                            <span class="abadge blk"><i class="fas fa-lock"></i> Locked</span>
                        <?php else: ?>
                            <span class="abadge bop"><i class="fas fa-unlock"></i> Open</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <button class="abtn abtn-gh abtn-sm" onclick="openEditVideo(<?= htmlspecialchars(json_encode([
                                'id'     => $v['id'],
                                'title'  => $v['title'],
                                'url'    => $v['video_url'],
                                'desc'   => $v['description'] ?? '',
                                'type'   => $v['video_type'],
                                'batch'  => $v['batch'] ?? '',
                                'folder' => $v['folder_id'],
                                'locked' => (int)$v['is_locked'],
                            ]), ENT_QUOTES) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST">
                                <input type="hidden" name="toggle_lock" value="1">
                                <input type="hidden" name="video_id" value="<?= $v['id'] ?>">
                                <input type="hidden" name="lock_state" value="<?= $v['is_locked']?0:1 ?>">
                                <button class="abtn abtn-gr abtn-sm" title="<?= $v['is_locked']?'Unlock':'Lock' ?>">
                                    <i class="fas fa-<?= $v['is_locked']?'unlock':'lock' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Delete this video?');">
                                <input type="hidden" name="video_id" value="<?= $v['id'] ?>">
                                <input type="hidden" name="delete_video" value="1">
                                <button class="abtn abtn-r abtn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#94a3b8;">No videos yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><!-- /videos -->


<!-- ┌────────┐ -->
<!-- │ GUIDES │ -->
<!-- └────────┘ -->
<div id="guides" class="asec">
    <div class="aph">
        <div>
            <h1 class="aph-title">Guide <span>Library</span></h1>
            <p class="aph-sub">PDF guides and learning materials</p>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:250px 1fr;gap:18px;margin-bottom:18px;">
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-folder-plus"></i> Folders</h3></div>
            <div class="acard-body">
                <form method="POST">
                    <input type="hidden" name="add_guide_folder" value="1">
                    <label class="albl">Folder Name</label>
                    <input type="text" name="folder_name" class="ainp" placeholder="Folder name" required>
                    <button class="abtn abtn-g abtn-full" style="margin-top:8px;">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </form>
            </div>
        </div>
        <div class="acard">
            <div class="acard-head"><h3 class="acard-title"><i class="fas fa-file-upload"></i> Upload Guide</h3></div>
            <div class="acard-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_guide" value="1">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="afield">
                            <label class="albl">Title</label>
                            <input type="text" name="g_title" class="ainp" placeholder="Guide title" required>
                        </div>
                        <div class="afield">
                            <label class="albl">Category</label>
                            <select name="g_category" class="asel">
                                <option>Broker</option><option>Deposit</option>
                                <option>Trading</option><option>Investing</option><option>Risk</option>
                            </select>
                        </div>
                        <div class="afield">
                            <label class="albl">Folder</label>
                            <select name="folder_id" class="asel">
                                <option value="0">No Folder</option>
                                <?php foreach ($guide_folders_arr as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="afield">
                            <label class="albl">PDF File</label>
                            <input type="file" name="g_file" class="ainp" accept="application/pdf" required>
                        </div>
                        <div class="afield" style="grid-column:1/-1">
                            <label class="albl">Description</label>
                            <textarea name="g_desc" class="ainp atea" rows="2" placeholder="Brief description…"></textarea>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
                        <label class="aclbl">
                            <input type="checkbox" name="is_locked" value="1"> Lock this guide
                        </label>
                        <button class="abtn abtn-g"><i class="fas fa-upload"></i> Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="acard">
        <div class="acard-head"><h3 class="acard-title"><i class="fas fa-file-pdf"></i> All Guides</h3></div>
        <div class="atw" style="max-height:420px;overflow-y:auto;">
            <table class="atable">
                <thead><tr><th>Title</th><th>Category</th><th>Folder</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if ($guides_res && $guides_res->num_rows > 0):
                    while ($g = $guides_res->fetch_assoc()): ?>
                <tr>
                    <td style="font-weight:600;"><?= htmlspecialchars($g['title']) ?></td>
                    <td><span class="abadge bf"><?= htmlspecialchars($g['category']) ?></span></td>
                    <td style="font-size:.8rem;color:var(--am);"><?= $g['folder_name'] ? htmlspecialchars($g['folder_name']) : '—' ?></td>
                    <td>
                        <?php if ($g['is_locked']): ?>
                            <span class="abadge blk"><i class="fas fa-lock"></i> Locked</span>
                        <?php else: ?>
                            <span class="abadge bop"><i class="fas fa-unlock"></i> Open</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <button class="abtn abtn-gh abtn-sm" onclick="openEditGuide(<?= htmlspecialchars(json_encode([
                                'id'     => $g['id'],
                                'title'  => $g['title'],
                                'desc'   => $g['description'] ?? '',
                                'cat'    => $g['category'],
                                'folder' => $g['folder_id'],
                                'locked' => (int)$g['is_locked'],
                            ]), ENT_QUOTES) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST">
                                <input type="hidden" name="toggle_guide_lock" value="1">
                                <input type="hidden" name="guide_id" value="<?= $g['id'] ?>">
                                <input type="hidden" name="lock_state" value="<?= $g['is_locked']?0:1 ?>">
                                <button class="abtn abtn-gr abtn-sm"><i class="fas fa-<?= $g['is_locked']?'unlock':'lock' ?>"></i></button>
                            </form>
                            <a href="<?= htmlspecialchars($g['file_path']) ?>" target="_blank" class="abtn abtn-gh abtn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" onsubmit="return confirm('Delete guide?');">
                                <input type="hidden" name="guide_id" value="<?= $g['id'] ?>">
                                <input type="hidden" name="delete_guide" value="1">
                                <button class="abtn abtn-r abtn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:#94a3b8;">No guides yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><!-- /guides -->


<!-- ┌─────────┐ -->
<!-- │ SUPPORT │ -->
<!-- └─────────┘ -->
<div id="chat" class="asec">
    <div class="aph">
        <div>
            <h1 class="aph-title">Support <span>Inbox</span></h1>
            <p class="aph-sub">Real-time member conversations</p>
        </div>
    </div>

    <div class="ach-wrap">

        <!-- Sidebar -->
        <div class="achl">
            <div class="achl-head">
                <div class="achl-title">Conversations</div>
                <input class="achl-search" type="text" id="ach-search" placeholder="Search members…" oninput="achFilterUsers(this.value)">
            </div>
            <div class="achl-list" id="ach-user-list">
                <?php
                $avatarColors = ['#6366f1','#ec4899','#14b8a6','#f97316','#8b5cf6','#0ea5e9','#22c55e','#ef4444'];
                $ci = 0;
                if ($chat_users && $chat_users->num_rows > 0):
                    while ($c = $chat_users->fetch_assoc()):
                        $initials = strtoupper(substr($c['fullname'], 0, 1));
                        $color    = $avatarColors[$ci++ % count($avatarColors)];
                        $unread   = intval($c['unread']);
                        $lastMsg  = htmlspecialchars(mb_substr($c['last_msg'] ?? '', 0, 42));
                        $isAdmin  = ($c['last_sender'] === 'admin');
                        $lastTime = '';
                        if ($c['last_time']) {
                            $ts = strtotime($c['last_time']);
                            $lastTime = (date('Y-m-d') === date('Y-m-d', $ts)) ? date('H:i', $ts) : date('d M', $ts);
                        }
                ?>
                <div class="achu" data-uid="<?= $c['id'] ?>" data-name="<?= htmlspecialchars(strtolower($c['fullname'])) ?>"
                     onclick="loadChat(<?= $c['id'] ?>, this, '<?= htmlspecialchars($c['fullname']) ?>', '<?= $color ?>', '<?= $initials ?>')">
                    <div class="achu-av" style="background:<?= $color ?>;"><?= $initials ?></div>
                    <div class="achu-info">
                        <div class="achu-n"><?= htmlspecialchars($c['fullname']) ?></div>
                        <div class="achu-prev <?= $isAdmin ? 'from-admin' : '' ?>">
                            <?= $isAdmin ? '↩ ' : '' ?><?= $lastMsg ?>
                        </div>
                    </div>
                    <div class="achu-meta">
                        <span class="achu-time"><?= $lastTime ?></span>
                        <?php if ($unread > 0): ?>
                        <span class="achu-badge"><?= $unread ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div class="achl-empty"><i class="fas fa-inbox" style="font-size:1.6rem;display:block;margin-bottom:8px;color:#1e293b;"></i>No conversations yet</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat panel -->
        <div class="achb">
            <div id="ach-hdr" class="ach-hdr" style="display:none;">
                <div id="ach-hdr-av" class="ach-hdr-av"></div>
                <div class="ach-hdr-info">
                    <div id="ach-hdr-name" class="ach-hdr-name"></div>
                    <div class="ach-hdr-sub">Member conversation</div>
                </div>
            </div>
            <div id="ach-empty-hdr" class="ach-empty-hdr">
                <!-- placeholder when nothing selected -->
            </div>

            <div class="achm" id="adm-chat-window">
                <div class="ache">
                    <i class="fas fa-comments"></i>
                    Select a conversation to start
                </div>
            </div>

            <div class="achir" id="ach-reply-bar" style="display:none;">
                <textarea class="achir-ta" id="reply_msg" placeholder="Type your reply… (Enter to send)" rows="1"></textarea>
                <button class="achir-send" id="ach-send-btn" title="Send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>

    </div>
</div><!-- /chat -->

<!-- ══════════════════════════════════════════════════════
     EDIT MODALS (Video / Article / Term / Guide)
══════════════════════════════════════════════════════ -->

<!-- ── shared modal backdrop ── -->
<div id="eModal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(2,6,23,.7);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:16px;" onclick="if(event.target===this)closeModal()">
<div style="background:#fff;border-radius:18px;width:100%;max-width:620px;max-height:90vh;overflow-y:auto;box-shadow:0 30px 80px rgba(0,0,0,.35);animation:admPopIn .25s ease;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--ae);">
        <h2 id="eModal-title" style="margin:0;font-size:1.1rem;color:var(--at);font-weight:800;"></h2>
        <button onclick="closeModal()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--am);padding:4px 8px;border-radius:6px;transition:background .2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'"><i class="fas fa-times"></i></button>
    </div>
    <div id="eModal-body" style="padding:24px;"></div>
</div>
</div>

</main>
</div><!-- /.aw -->

<style>
/* ── modal label/field reuse ── */
.em-label { display:block;font-size:.72rem;font-weight:700;color:var(--am);text-transform:uppercase;letter-spacing:.8px;margin:0 0 5px; }
.em-input,.em-select,.em-textarea {
    width:100%;padding:10px 13px;border:1.5px solid var(--ae);border-radius:9px;
    font-family:inherit;font-size:.88rem;background:#fdfdfd;color:var(--at);
    outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box;
}
.em-input:focus,.em-select:focus,.em-textarea:focus {
    border-color:var(--ag);box-shadow:0 0 0 3px rgba(212,175,55,.12);background:#fff;
}
.em-row { margin-bottom:14px; }
.em-grid2 { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
.em-actions { display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--ae); }
</style>

<script>
/* ══════════════════════════════════════
   NAVIGATION — event-listener based
══════════════════════════════════════ */
var _tab = 'dashboard';

function showSection(id) {
    document.querySelectorAll('.asec').forEach(function(s){ s.classList.remove('active'); });
    var el = document.getElementById(id);
    if (el) el.classList.add('active');

    document.querySelectorAll('.anb').forEach(function(b){
        b.classList.toggle('active', b.dataset.target === id);
    });

    _tab = id;
    try { sessionStorage.setItem('adminTab', id); } catch(e){}

    if (id === 'dashboard') setTimeout(runDashboard, 80);
}

// Wire nav buttons
document.querySelectorAll('.anb').forEach(function(btn){
    btn.addEventListener('click', function(){ showSection(this.dataset.target); });
});

// Inject _tab into every form on submit
document.addEventListener('submit', function(e){
    var form = e.target;
    var f = form.querySelector('[name="_tab"]');
    if (f) { f.value = _tab; }
    else {
        var i = document.createElement('input');
        i.type='hidden'; i.name='_tab'; i.value=_tab;
        form.appendChild(i);
    }
});

// Restore tab on load
window.addEventListener('DOMContentLoaded', function(){
    var p   = new URLSearchParams(window.location.search);
    var url = p.get('tab');
    <?php if (isset($_GET['page']) || (isset($_GET['search']) && trim($_GET['search'])!=='')): ?>
    url = 'users';
    <?php endif; ?>
    var stored;
    try { stored = sessionStorage.getItem('adminTab'); } catch(e){ stored=null; }
    showSection(url || stored || 'dashboard');
});

/* ══════════════════════════════════════
   SUPPORT CHAT — PREMIUM
══════════════════════════════════════ */
var _achUid   = null;
var _achColor = '#6366f1';
var _achInit  = '?';
var _achMsgCount = 0;
var _achPollTimer = null;

function achBuildRow(m, color, initials) {
    var isAdm = m.sender === 'admin';
    var row = document.createElement('div');
    row.className = 'adm-msg-row' + (isAdm ? ' row-admin' : '');

    var av = document.createElement('div');
    av.className = 'adm-bubble-av' + (isAdm ? ' av-admin' : '');
    av.style.background = isAdm ? '' : color;
    av.innerHTML = isAdm ? '<i class="fas fa-headset" style="font-size:10px"></i>' : initials;

    var wrap = document.createElement('div');
    wrap.className = 'adm-bubble-wrap';

    var bub = document.createElement('div');
    bub.className = 'adm-bubble ' + (isAdm ? 'b-admin' : 'b-user');
    bub.textContent = m.msg;

    var ts = document.createElement('div');
    ts.className = 'adm-bubble-time';
    ts.textContent = m.time || '';

    wrap.appendChild(bub);
    wrap.appendChild(ts);

    if (isAdm) { row.appendChild(wrap); row.appendChild(av); }
    else       { row.appendChild(av);   row.appendChild(wrap); }
    return row;
}

function achScrollBottom() {
    var box = document.getElementById('adm-chat-window');
    box.scrollTo({ top: box.scrollHeight, behavior: 'smooth' });
}

function achShowTyping() {
    if (document.getElementById('adm-typing-row')) return;
    var row = document.createElement('div');
    row.className = 'adm-typing'; row.id = 'adm-typing-row';
    var av = document.createElement('div');
    av.className = 'adm-bubble-av'; av.style.background = _achColor;
    av.textContent = _achInit;
    var bub = document.createElement('div');
    bub.className = 'adm-typing-bub';
    bub.innerHTML = '<div class="adm-typing-dot"></div><div class="adm-typing-dot"></div><div class="adm-typing-dot"></div>';
    row.appendChild(av); row.appendChild(bub);
    document.getElementById('adm-chat-window').appendChild(row);
    achScrollBottom();
}
function achHideTyping() {
    var el = document.getElementById('adm-typing-row');
    if (el) el.remove();
}

function loadChat(uid, elem, name, color, initials) {
    _achUid   = uid;
    _achColor = color || '#6366f1';
    _achInit  = initials || '?';
    _achMsgCount = 0;

    document.querySelectorAll('.achu').forEach(function(u){ u.classList.remove('active'); });
    if (elem) {
        elem.classList.add('active');
        var badge = elem.querySelector('.achu-badge');
        if (badge) badge.remove();
    }

    /* Header */
    var hdr    = document.getElementById('ach-hdr');
    var emHdr  = document.getElementById('ach-empty-hdr');
    var hdrAv  = document.getElementById('ach-hdr-av');
    var hdrNm  = document.getElementById('ach-hdr-name');
    hdr.style.display   = 'flex';
    emHdr.style.display = 'none';
    hdrAv.style.background = _achColor;
    hdrAv.textContent   = _achInit;
    hdrNm.textContent   = name || 'User';

    document.getElementById('ach-reply-bar').style.display = 'flex';

    var box = document.getElementById('adm-chat-window');
    box.innerHTML = '<div class="ache"><i class="fas fa-spinner fa-spin" style="color:#d4af37;font-size:1.4rem;"></i></div>';

    clearInterval(_achPollTimer);
    achFetchMessages(true);
    _achPollTimer = setInterval(function(){ achFetchMessages(false); }, 2500);
}

function achFetchMessages(initial) {
    if (!_achUid) return;
    fetch('support_api.php?action=fetch&target_id=' + _achUid)
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!Array.isArray(data)) return;
            if (data.length === _achMsgCount && !initial) return;

            var box = document.getElementById('adm-chat-window');
            if (initial || _achMsgCount === 0) {
                box.innerHTML = '';
                if (!data.length) {
                    box.innerHTML = '<div class="ache"><i class="fas fa-comment-slash"></i>No messages yet</div>';
                    _achMsgCount = 0;
                    return;
                }
                data.forEach(function(m){ box.appendChild(achBuildRow(m, _achColor, _achInit)); });
            } else {
                data.slice(_achMsgCount).forEach(function(m){
                    achHideTyping();
                    box.appendChild(achBuildRow(m, _achColor, _achInit));
                });
            }
            _achMsgCount = data.length;
            achScrollBottom();
        })
        .catch(function(){
            if (_achMsgCount === 0)
                document.getElementById('adm-chat-window').innerHTML = '<div class="ache" style="color:#ef4444;"><i class="fas fa-exclamation-circle"></i>Failed to load</div>';
        });
}

function achSendReply() {
    var ta  = document.getElementById('reply_msg');
    var btn = document.getElementById('ach-send-btn');
    var txt = ta.value.trim();
    if (!txt || !_achUid) return;

    ta.value = '';
    ta.style.height = 'auto';
    btn.classList.add('sending');
    setTimeout(function(){ btn.classList.remove('sending'); }, 400);

    /* Optimistic render */
    var box = document.getElementById('adm-chat-window');
    box.appendChild(achBuildRow({ sender:'admin', msg:txt, time: new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}) }, _achColor, _achInit));
    _achMsgCount++;
    achScrollBottom();

    var fd = new FormData();
    fd.append('message', txt);
    fd.append('target_id', _achUid);
    fetch('support_api.php?action=send', { method:'POST', body:fd })
        .then(function(){ setTimeout(function(){ achFetchMessages(false); }, 1000); })
        .catch(function(){});
}

/* Wire up reply bar */
document.getElementById('ach-send-btn').addEventListener('click', achSendReply);
document.getElementById('reply_msg').addEventListener('keydown', function(e){
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); achSendReply(); }
});
document.getElementById('reply_msg').addEventListener('input', function(){
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 110) + 'px';
});

/* Sidebar search filter */
function achFilterUsers(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.achu').forEach(function(el){
        var name = el.dataset.name || '';
        el.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
}

/* Poll sidebar for new unread counts when support tab is visible */
setInterval(function(){
    var tab = document.getElementById('chat');
    if (!tab || !tab.classList.contains('active')) return;
    fetch('support_api.php?action=unread_counts')
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!data || typeof data !== 'object') return;
            document.querySelectorAll('.achu').forEach(function(el){
                var uid = el.dataset.uid;
                var cnt = data[uid] || 0;
                var badge = el.querySelector('.achu-badge');
                if (cnt > 0 && el.dataset.uid != _achUid) {
                    if (!badge) { badge = document.createElement('span'); badge.className='achu-badge'; el.querySelector('.achu-meta').appendChild(badge); }
                    badge.textContent = cnt;
                } else if (badge) { badge.remove(); }
            });
        }).catch(function(){});
}, 8000);

/* ══════════════════════════════════════
   DASHBOARD ANIMATIONS
══════════════════════════════════════ */

/* Animated number counter (eased) */
function animCount(el, target, duration, suffix) {
    suffix = suffix || '';
    var start = performance.now();
    function tick(now) {
        var p = Math.min((now - start) / duration, 1);
        var e = 1 - Math.pow(1 - p, 3); /* ease-out cubic */
        el.innerHTML = Math.floor(e * target).toLocaleString() + suffix;
        if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

/* Live clock + greeting */
function dbClock() {
    var now  = new Date();
    var h = String(now.getHours()).padStart(2,'0');
    var m = String(now.getMinutes()).padStart(2,'0');
    var s = String(now.getSeconds()).padStart(2,'0');
    var ce = document.getElementById('dbClock');
    var de = document.getElementById('dbClockDate');
    var ge = document.getElementById('dbGreeting');
    if (ce) ce.textContent = h + ':' + m + ':' + s;
    if (de) {
        var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var mons = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        de.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + mons[now.getMonth()] + ' ' + now.getFullYear();
    }
    if (ge) {
        var hr = now.getHours();
        ge.textContent = hr < 12 ? 'Good Morning' : hr < 17 ? 'Good Afternoon' : hr < 21 ? 'Good Evening' : 'Good Night';
    }
}
setInterval(dbClock, 1000);
dbClock();

var _dbRan = false;
function runDashboard() {
    /* KPI counters */
    document.querySelectorAll('.db-kpi-num').forEach(function(el) {
        animCount(el, parseInt(el.dataset.target)||0, 1300);
    });
    /* Content item counters */
    document.querySelectorAll('.db-cnt-num').forEach(function(el) {
        animCount(el, parseInt(el.dataset.target)||0, 1100);
    });
    /* Paid % counter */
    var pe = document.getElementById('dbPaidPct');
    if (pe) animCount(pe, parseInt(pe.dataset.target)||0, 1400, '<span style="font-size:1.2rem">%</span>');

    /* KPI progress bars */
    document.querySelectorAll('.db-kpi-fill').forEach(function(el) {
        el.style.width = '0';
        setTimeout(function() { el.style.width = (el.dataset.w || 0) + '%'; }, 150);
    });
    /* Batch bars */
    document.querySelectorAll('.db-batch-fill').forEach(function(el) {
        el.style.width = '0';
        setTimeout(function() { el.style.width = (el.dataset.w || 0) + '%'; }, 200);
    });
    /* Donut ring segments */
    document.querySelectorAll('.db-ring-seg').forEach(function(el) {
        var final = el.dataset.final;
        setTimeout(function() { el.style.transition = 'stroke-dasharray 1.5s cubic-bezier(.4,0,.2,1)'; el.setAttribute('stroke-dasharray', final); }, 250);
    });

    _dbRan = true;
}

/* Run when dashboard section is initially active */
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var active = document.querySelector('.asec.active');
        if (active && active.id === 'dashboard') runDashboard();
    }, 320);
});

/* ══════════════════════════════════════
   EDIT MODAL SYSTEM
══════════════════════════════════════ */
var _modal     = document.getElementById('eModal');
var _modalTitle= document.getElementById('eModal-title');
var _modalBody = document.getElementById('eModal-body');

function openModal(title, bodyHTML) {
    _modalTitle.textContent = title;
    _modalBody.innerHTML = bodyHTML;
    _modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Wire the new form to inject _tab on submit
    var f = _modalBody.querySelector('form');
    if (f) f.addEventListener('submit', function() {
        var inp = document.createElement('input');
        inp.type='hidden'; inp.name='_tab'; inp.value=_tab;
        f.appendChild(inp);
    });
}
function closeModal() {
    _modal.style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeModal(); });

/* ── EDIT VIDEO ── */
function openEditVideo(d) {
    var folders = <?= json_encode($folders_arr) ?>;
    var folderOpts = '<option value="0">None</option>' + folders.map(function(f){
        return '<option value="'+f.id+'"'+(d.folder==f.id?' selected':'')+'>'+escHtml(f.name)+'</option>';
    }).join('');

    openModal('Edit Video', [
        '<form method="POST" style="margin:0">',
        '<input type="hidden" name="edit_video" value="1">',
        '<input type="hidden" name="video_id" value="'+d.id+'">',
        '<div class="em-grid2">',
          '<div class="em-row" style="grid-column:1/-1"><label class="em-label">Title</label>',
            '<input name="v_title" class="em-input" value="'+escHtml(d.title)+'" required></div>',
          '<div class="em-row" style="grid-column:1/-1"><label class="em-label">YouTube URL</label>',
            '<input name="v_url" class="em-input" value="'+escHtml(d.url)+'" required></div>',
          '<div class="em-row"><label class="em-label">Library</label>',
            '<select name="video_type" class="em-select">',
              '<option value="free"'+(d.type==='free'?' selected':'')+'>Free Library</option>',
              '<option value="premium"'+(d.type==='premium'?' selected':'')+'>Premium Hub</option>',
            '</select></div>',
          '<div class="em-row"><label class="em-label">Batch Access</label>',
            '<select name="video_batch" class="em-select">',
              '<option value=""'+(d.batch===''?' selected':'')+'>All Premium Members</option>',
              '<option value="b1"'+(d.batch==='b1'?' selected':'')+'>SIM Batch-1</option>',
              '<option value="b2"'+(d.batch==='b2'?' selected':'')+'>SIM Batch-2</option>',
              '<option value="b3"'+(d.batch==='b3'?' selected':'')+'>SIM Batch-3</option>',
              '<option value="b4"'+(d.batch==='b4'?' selected':'')+'>SIM Batch-4</option>',
              '<option value="b5"'+(d.batch==='b5'?' selected':'')+'>SIM Batch-5</option>',
            '</select></div>',
          '<div class="em-row"><label class="em-label">Folder</label>',
            '<select name="folder_id" class="em-select">'+folderOpts+'</select></div>',
          '<div class="em-row"><label class="em-label">Description</label>',
            '<input name="v_desc" class="em-input" value="'+escHtml(d.desc)+'"></div>',
          '<div class="em-row" style="display:flex;align-items:center;gap:8px;">',
            '<input type="checkbox" name="is_locked" value="1" id="ev_lock"'+(d.locked?' checked':'')+' style="accent-color:var(--ag);">',
            '<label for="ev_lock" style="font-size:.86rem;font-weight:600;cursor:pointer;">Lock this video</label></div>',
        '</div>',
        '<div class="em-actions">',
          '<button type="button" onclick="closeModal()" class="abtn abtn-gh">Cancel</button>',
          '<button type="submit" class="abtn abtn-g"><i class="fas fa-save"></i> Save Changes</button>',
        '</div>',
        '</form>'
    ].join(''));
}

/* ── EDIT ARTICLE ── */
function openEditArticle(d) {
    openModal('Edit Article', [
        '<form method="POST" enctype="multipart/form-data" style="margin:0">',
        '<input type="hidden" name="edit_article" value="1">',
        '<input type="hidden" name="article_id" value="'+d.id+'">',
        '<div class="em-grid2">',
          '<div class="em-row"><label class="em-label">Title</label>',
            '<input name="a_title" class="em-input" value="'+escHtml(d.title)+'" required></div>',
          '<div class="em-row"><label class="em-label">Category</label>',
            '<select name="a_category" class="em-select">',
              '<option'+(d.category==='Brokerage Knowledges'?' selected':'')+'>Brokerage Knowledges</option>',
              '<option'+(d.category==='Investment Knowledges'?' selected':'')+'>Investment Knowledges</option>',
            '</select></div>',
          '<div class="em-row" style="grid-column:1/-1"><label class="em-label">Content</label>',
            '<textarea name="a_content" class="em-textarea" rows="7" required>'+escHtml(d.content)+'</textarea></div>',
          '<div class="em-row" style="grid-column:1/-1"><label class="em-label">Replace Thumbnail (optional)</label>',
            '<input type="file" name="a_thumbnail" class="em-input" accept="image/*"></div>',
        '</div>',
        '<div class="em-actions">',
          '<button type="button" onclick="closeModal()" class="abtn abtn-gh">Cancel</button>',
          '<button type="submit" class="abtn abtn-g"><i class="fas fa-save"></i> Save Changes</button>',
        '</div>',
        '</form>'
    ].join(''));
}

/* ── EDIT GLOSSARY TERM ── */
function openEditTerm(d) {
    openModal('Edit Glossary Term', [
        '<form method="POST" style="margin:0">',
        '<input type="hidden" name="edit_term" value="1">',
        '<input type="hidden" name="term_id" value="'+d.id+'">',
        '<div class="em-row"><label class="em-label">Term</label>',
          '<input name="g_term" class="em-input" value="'+escHtml(d.term)+'" required></div>',
        '<div class="em-row"><label class="em-label">Definition</label>',
          '<textarea name="g_def" class="em-textarea" rows="5" required>'+escHtml(d.def)+'</textarea></div>',
        '<div class="em-row"><label class="em-label">Equation (optional)</label>',
          '<input name="g_eq" class="em-input" value="'+escHtml(d.eq)+'"></div>',
        '<div class="em-actions">',
          '<button type="button" onclick="closeModal()" class="abtn abtn-gh">Cancel</button>',
          '<button type="submit" class="abtn abtn-g"><i class="fas fa-save"></i> Save Changes</button>',
        '</div>',
        '</form>'
    ].join(''));
}

/* ── EDIT GUIDE ── */
function openEditGuide(d) {
    var gFolders = <?= json_encode($guide_folders_arr) ?>;
    var gOpts = '<option value="0">No Folder</option>' + gFolders.map(function(f){
        return '<option value="'+f.id+'"'+(d.folder==f.id?' selected':'')+'>'+escHtml(f.name)+'</option>';
    }).join('');

    openModal('Edit Guide', [
        '<form method="POST" style="margin:0">',
        '<input type="hidden" name="edit_guide" value="1">',
        '<input type="hidden" name="guide_id" value="'+d.id+'">',
        '<div class="em-grid2">',
          '<div class="em-row"><label class="em-label">Title</label>',
            '<input name="g_title" class="em-input" value="'+escHtml(d.title)+'" required></div>',
          '<div class="em-row"><label class="em-label">Category</label>',
            '<select name="g_category" class="em-select">',
              ['Broker','Deposit','Trading','Investing','Risk'].map(function(c){
                return '<option'+(d.cat===c?' selected':'')+'>'+c+'</option>';
              }).join(''),
            '</select></div>',
          '<div class="em-row"><label class="em-label">Folder</label>',
            '<select name="folder_id" class="em-select">'+gOpts+'</select></div>',
          '<div class="em-row"><label class="em-label">Description</label>',
            '<textarea name="g_desc" class="em-textarea" rows="3">'+escHtml(d.desc)+'</textarea></div>',
          '<div class="em-row" style="display:flex;align-items:center;gap:8px;">',
            '<input type="checkbox" name="is_locked" value="1" id="eg_lock"'+(d.locked?' checked':'')+' style="accent-color:var(--ag);">',
            '<label for="eg_lock" style="font-size:.86rem;font-weight:600;cursor:pointer;">Lock this guide</label></div>',
        '</div>',
        '<div class="em-actions">',
          '<button type="button" onclick="closeModal()" class="abtn abtn-gh">Cancel</button>',
          '<button type="submit" class="abtn abtn-g"><i class="fas fa-save"></i> Save Changes</button>',
        '</div>',
        '</form>'
    ].join(''));
}

/* ── shared HTML-escape helper ── */
function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>

<?php include 'includes/footer.php'; ?>
