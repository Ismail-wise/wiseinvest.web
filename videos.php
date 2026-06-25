<?php
include 'includes/header.php';
include 'includes/db.php';

// ── Pagination only (no folder filtering — free videos are not categorised by sim classes)
$per_page = 12;
$page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// ── Count & paginate
$total       = (int)$conn->query("SELECT COUNT(*) FROM premium_content WHERE video_type='free'")->fetch_row()[0];
$total_pages = max(1, ceil($total / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $per_page;

// ── Videos
$videos_q  = $conn->query("SELECT * FROM premium_content WHERE video_type='free' ORDER BY id DESC LIMIT $per_page OFFSET $offset");
$total_all = $total;

// ── Hero: latest 3 videos for floating preview cards
$hero_vids_q = $conn->query("SELECT id, title, video_url FROM premium_content WHERE video_type='free' ORDER BY id DESC LIMIT 3");
$hero_vids   = [];
if ($hero_vids_q) {
    while ($hv = $hero_vids_q->fetch_assoc()) {
        $yt_id = '';
        if (strpos($hv['video_url'], 'youtube') !== false || strpos($hv['video_url'], 'youtu.be') !== false) {
            preg_match('~(?:youtu\.be/|v=)([^&?/\s]+)~', $hv['video_url'], $m);
            $yt_id = $m[1] ?? '';
        }
        $hv['yt_id'] = $yt_id;
        $hv['thumb'] = $yt_id ? "https://img.youtube.com/vi/{$yt_id}/hqdefault.jpg" : '';
        $hero_vids[] = $hv;
    }
}
?>

<style>
/* ═══════════════════════════════════════════════════
   VIDEOS PAGE — PREMIUM DARK
   ═══════════════════════════════════════════════════ */

/* ── Keyframes ── */
@keyframes vidFadeUp    { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
@keyframes vidShimmer   { from{background-position:-400% center} to{background-position:400% center} }
@keyframes vidRing      { 0%{transform:translate(-50%,-50%) scale(1);opacity:.6} 100%{transform:translate(-50%,-50%) scale(2.2);opacity:0} }
@keyframes vFloat       { 0%,100%{transform:rotate(var(--r)) translateY(0)} 50%{transform:rotate(var(--r)) translateY(-14px)} }
@keyframes vOrb         { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.12) translate(18px,-12px)} }
@keyframes vGridLines   { 0%{opacity:0.3} 100%{opacity:0.06} }
@keyframes vPlayPulse   { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.5)} 70%{box-shadow:0 0 0 14px rgba(212,175,55,0)} }
@keyframes vCountUp     { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

/* ══════════════════════════════════════
   HERO — SPLIT LAYOUT
   ══════════════════════════════════════ */
.vh-hero {
    position: relative;
    background: #020617;
    overflow: hidden;
    padding: 70px 0 0;
}

/* Grid lines background */
.vh-grid-bg {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(212,175,55,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212,175,55,0.04) 1px, transparent 1px);
    background-size: 60px 60px;
    animation: vGridLines 4s ease-in-out infinite alternate;
}

/* Orbs */
.vh-orb {
    position: absolute; border-radius: 50%;
    pointer-events: none; filter: blur(90px);
}
.vh-orb-1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(212,175,55,0.14) 0%, transparent 70%);
    top: -200px; left: -150px;
    animation: vOrb 8s ease-in-out infinite;
}
.vh-orb-2 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
    bottom: 0; right: -100px;
    animation: vOrb 10s ease-in-out infinite reverse;
}

/* Inner wrapper */
.vh-hero-inner {
    max-width: 1320px; margin: 0 auto;
    padding: 0 40px 70px;
    display: grid;
    grid-template-columns: 1fr 480px;
    gap: 60px;
    align-items: center;
    position: relative; z-index: 2;
}

/* ── LEFT column ── */
.vh-left { }

.vh-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(212,175,55,0.1);
    border: 1px solid rgba(212,175,55,0.3);
    color: #d4af37;
    padding: 6px 20px; border-radius: 99px;
    font-size: 0.72rem; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;
    margin-bottom: 22px;
    animation: vidFadeUp 0.6s 0.1s ease both;
}

.vh-title {
    font-size: clamp(2.2rem, 4.5vw, 3.6rem);
    font-weight: 900; color: #fff; margin: 0 0 18px; line-height: 1.1;
    animation: vidFadeUp 0.6s 0.2s ease both;
}
.vh-title .vh-gold {
    background: linear-gradient(135deg, #d4af37 0%, #facc15 40%, #f59e0b 70%, #d4af37 100%);
    background-size: 200% auto;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: vidShimmer 3.5s linear infinite;
}

.vh-desc {
    color: #64748b; font-size: 1.05rem; line-height: 1.7;
    max-width: 500px; margin: 0 0 36px;
    animation: vidFadeUp 0.6s 0.3s ease both;
}

/* Stats row */
.vh-stats {
    display: flex; gap: 0; flex-wrap: wrap;
    animation: vidFadeUp 0.6s 0.4s ease both;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px; overflow: hidden;
    background: rgba(255,255,255,0.02);
    max-width: 460px;
}
.vh-stat {
    flex: 1; min-width: 100px;
    padding: 20px 18px;
    text-align: center;
    border-right: 1px solid rgba(255,255,255,0.06);
    position: relative;
}
.vh-stat:last-child { border-right: none; }
.vh-stat::before {
    content: '';
    position: absolute; top: 0; left: 50%; transform: translateX(-50%);
    width: 40%; height: 2px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    border-radius: 0 0 2px 2px;
}
.vh-stat strong {
    display: block; font-size: 1.9rem; font-weight: 900; color: #d4af37;
    line-height: 1; margin-bottom: 4px;
}
.vh-stat span { font-size: 0.72rem; color: #475569; letter-spacing: 1.5px; text-transform: uppercase; }

/* CTA buttons */
.vh-ctas {
    display: flex; gap: 14px; flex-wrap: wrap; margin-top: 28px;
    animation: vidFadeUp 0.6s 0.5s ease both;
}
.vh-btn-main {
    display: inline-flex; align-items: center; gap: 9px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    color: #020617; font-weight: 800; font-size: 0.9rem;
    padding: 13px 28px; border-radius: 12px; text-decoration: none;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
    box-shadow: 0 8px 28px rgba(212,175,55,0.3);
}
.vh-btn-main:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 40px rgba(212,175,55,0.5);
}
.vh-btn-sec {
    display: inline-flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    color: #94a3b8; font-weight: 600; font-size: 0.9rem;
    padding: 13px 24px; border-radius: 12px; text-decoration: none;
    transition: all 0.25s;
}
.vh-btn-sec:hover { background: rgba(255,255,255,0.09); color: #e2e8f0; }

/* ── RIGHT column — floating video preview cards ── */
.vh-right {
    position: relative;
    height: 440px;
    animation: vidFadeUp 0.7s 0.35s ease both;
}

.vh-preview {
    position: absolute;
    width: 240px;
    background: #0a1628;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    overflow: hidden;
    transform: rotate(var(--r));
    animation: vFloat var(--dur) var(--del) ease-in-out infinite;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.04);
    transition: box-shadow 0.3s;
}
.vh-preview:nth-child(1) { --r:-4deg; --dur:6s;   --del:0s;   top:0;    left:20px; z-index:3; }
.vh-preview:nth-child(2) { --r: 3deg; --dur:7.5s; --del:1.2s; top:120px;left:170px;z-index:2; }
.vh-preview:nth-child(3) { --r:-2deg; --dur:5.5s; --del:0.6s; top:250px;left:30px; z-index:1; }

.vh-preview:hover {
    z-index: 10;
    box-shadow: 0 30px 80px rgba(0,0,0,0.7), 0 0 0 1px rgba(212,175,55,0.3);
}

.vh-prev-thumb {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #0f172a;
}
.vh-prev-thumb img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.4s ease;
}
.vh-preview:hover .vh-prev-thumb img { transform: scale(1.06); }

.vh-prev-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(10,22,40,0.95) 0%, transparent 60%);
}

.vh-prev-play {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%,-50%);
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #020617; font-size: 1rem;
    animation: vPlayPulse 2.2s ease-in-out infinite;
}
.vh-prev-play i { margin-left: 3px; }

.vh-prev-info {
    padding: 10px 12px;
}
.vh-prev-title {
    font-size: 0.78rem; font-weight: 700; color: #e2e8f0;
    line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    margin: 0;
}

/* No preview placeholder */
.vh-prev-no-thumb {
    width: 100%; aspect-ratio: 16/9;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    display: flex; align-items: center; justify-content: center;
}

/* Hero bottom wave */
.vh-wave {
    position: relative; z-index: 2;
    height: 60px; margin-top: -1px;
    background: #020617;
}
.vh-wave svg {
    display: block; width: 100%; height: 60px;
}

/* ── Filter bar ─────────────────────────────────── */
.vid-filter-bar {
    background: #020617;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    position: sticky; top: var(--header-total-height); z-index: 50;
}
.vid-filter-inner {
    max-width: 1320px; margin: 0 auto;
    display: flex; align-items: center; gap: 10px;
    padding: 14px 24px;
    overflow-x: auto; scrollbar-width: none;
}
.vid-filter-inner::-webkit-scrollbar { display: none; }

.vid-pill {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 18px; border-radius: 99px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    color: #64748b; font-size: 0.82rem; font-weight: 600;
    text-decoration: none; white-space: nowrap;
    transition: all 0.22s;
}
.vid-pill:hover { color: #e2e8f0; border-color: rgba(255,255,255,0.25); }
.vid-pill.active {
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-color: transparent; color: #020617; font-weight: 700;
}
.vid-pill-count {
    background: rgba(0,0,0,0.2);
    border-radius: 99px; padding: 1px 7px; font-size: 0.72rem;
}
.vid-pill.active .vid-pill-count { background: rgba(0,0,0,0.15); }

/* ── Page body ──────────────────────────────────── */
.vid-body {
    background: #020617;
    padding: 48px 24px 80px;
    min-height: 60vh;
}
.vid-body-inner { max-width: 1320px; margin: 0 auto; }

/* Section header */
.vid-section-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 30px; flex-wrap: wrap; gap: 10px;
}
.vid-section-title { font-size: 1.1rem; font-weight: 700; color: #94a3b8; }
.vid-section-title strong { color: #fff; }
.vid-count-badge {
    background: rgba(212,175,55,0.1);
    border: 1px solid rgba(212,175,55,0.2);
    color: #d4af37; padding: 4px 14px; border-radius: 99px;
    font-size: 0.78rem; font-weight: 700;
}

/* ── Video grid ─────────────────────────────────── */
.vid-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 24px;
}

/* ── Video card ─────────────────────────────────── */
.vid-card {
    position: relative;
    background: #0a1628;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1),
                box-shadow 0.35s ease, border-color 0.3s;
    opacity: 0;
    transform: translateY(24px);
}
.vid-card.visible {
    opacity: 1; transform: translateY(0);
    transition: opacity 0.45s ease, transform 0.45s ease,
                box-shadow 0.35s ease, border-color 0.3s;
}
.vid-card:not(.locked):hover {
    transform: translateY(-8px);
    box-shadow: 0 28px 65px rgba(0,0,0,0.6), 0 0 0 1px rgba(212,175,55,0.25);
    border-color: rgba(212,175,55,0.3);
}
.vid-card.locked { cursor: default; }

/* Thumbnail */
.vid-thumb {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #0f172a;
}
.vid-thumb img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.vid-card:not(.locked):hover .vid-thumb img { transform: scale(1.05); }

/* Gradient over thumbnail */
.vid-thumb-grad {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(10,22,40,0.9) 0%, rgba(10,22,40,0.2) 60%, transparent 100%);
    transition: opacity 0.3s;
}

/* Duration badge */
.vid-duration {
    position: absolute; bottom: 10px; right: 10px;
    background: rgba(0,0,0,0.75);
    color: #fff; font-size: 0.72rem; font-weight: 700;
    padding: 3px 8px; border-radius: 5px;
    backdrop-filter: blur(4px);
}

/* Play button */
.vid-play {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 58px; height: 58px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #020617;
    box-shadow: 0 8px 30px rgba(212,175,55,0.4);
    transition: transform 0.3s, box-shadow 0.3s;
    z-index: 2;
}
.vid-play::before {
    content: '';
    position: absolute; top: 50%; left: 50%;
    width: 100%; height: 100%;
    background: rgba(212,175,55,0.4);
    border-radius: 50%;
    transform: translate(-50%,-50%);
    animation: vidRing 1.8s ease-out infinite;
}
.vid-card:not(.locked):hover .vid-play {
    transform: translate(-50%,-50%) scale(1.12);
    box-shadow: 0 12px 40px rgba(212,175,55,0.6);
}

/* Premium lock overlay */
.vid-lock-overlay {
    position: absolute; inset: 0;
    background: rgba(2,6,23,0.78);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 10px;
    backdrop-filter: blur(5px);
    z-index: 3;
}
.vid-lock-overlay i { font-size: 2rem; color: #d4af37; }
.vid-lock-overlay strong { color: #fff; font-size: 0.95rem; }
.vid-lock-overlay small { color: #64748b; font-size: 0.8rem; }
.vid-lock-overlay a {
    margin-top: 6px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    color: #020617; font-weight: 700; font-size: 0.8rem;
    padding: 7px 18px; border-radius: 99px; text-decoration: none;
    transition: opacity 0.2s;
}
.vid-lock-overlay a:hover { opacity: 0.88; }
.locked .vid-thumb img { filter: blur(4px); }

/* Card info */
.vid-card-info { padding: 16px 18px; }
.vid-card-title {
    font-size: 0.93rem; font-weight: 700; color: #e2e8f0;
    margin: 0 0 6px; line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.vid-card-desc {
    font-size: 0.78rem; color: #475569; line-height: 1.55;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    margin: 0;
}

/* Active (playing) state */
.vid-card.playing { border-color: rgba(212,175,55,0.5); }
.vid-card.playing .vid-thumb { aspect-ratio: auto; }
.vid-card.playing iframe { display: block; width: 100%; aspect-ratio: 16/9; border: none; }
.vid-card.playing .vid-play,
.vid-card.playing .vid-thumb-grad,
.vid-card.playing .vid-thumb img { display: none; }

/* ── Pagination ─────────────────────────────────── */
.vid-pagination {
    display: flex; align-items: center; justify-content: center;
    gap: 8px; margin-top: 52px; flex-wrap: wrap;
}
.vid-page-btn {
    min-width: 42px; height: 42px; padding: 0 14px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 10px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    color: #64748b; font-size: 0.88rem; font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.vid-page-btn:hover { background: rgba(212,175,55,0.1); border-color: rgba(212,175,55,0.3); color: #d4af37; }
.vid-page-btn.active {
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-color: transparent; color: #020617; font-weight: 800;
    box-shadow: 0 4px 16px rgba(212,175,55,0.35);
}
.vid-page-btn.disabled { opacity: 0.3; pointer-events: none; }

/* ── Empty state ── */
.vid-empty {
    text-align: center; padding: 80px 20px; color: #475569;
}
.vid-empty i { font-size: 3.5rem; color: rgba(212,175,55,0.15); margin-bottom: 18px; display: block; }
.vid-empty h3 { color: #94a3b8; margin: 0 0 8px; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .vh-hero-inner { grid-template-columns: 1fr; gap: 40px; padding-bottom: 50px; }
    .vh-right { height: 360px; }
    .vh-preview:nth-child(3) { display: none; }
    .vh-preview:nth-child(2) { left: 200px; }
}
@media (max-width: 768px) {
    .vh-hero-inner { padding: 0 20px 40px; }
    .vh-right { display: none; }
    .vh-title { font-size: 2rem; }
    .vid-grid { grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); }
}
@media (max-width: 480px) {
    .vid-grid { grid-template-columns: 1fr; }
    .vh-stats { flex-direction: row; }
}
</style>

<!-- ═══════════════════════════════════════════════
     HERO — SPLIT
     ═══════════════════════════════════════════════ -->
<section class="vh-hero">
    <div class="vh-grid-bg"></div>
    <div class="vh-orb vh-orb-1"></div>
    <div class="vh-orb vh-orb-2"></div>

    <div class="vh-hero-inner">

        <!-- LEFT -->
        <div class="vh-left">
            <div class="vh-badge">
                <i class="fas fa-play-circle"></i> Free Video Library
            </div>

<p class="vh-desc">
                Curated investment tutorials, market strategies, and financial psychology —
                handpicked by professionals. All free, all in one place.
            </p>

            <div class="vh-stats">
                <div class="vh-stat">
                    <strong><?php echo $total_all; ?></strong>
                    <span>Videos</span>
                </div>
                <div class="vh-stat">
                    <strong>HD</strong>
                    <span>Quality</span>
                </div>
                <div class="vh-stat">
                    <strong>Free</strong>
                    <span>Access</span>
                </div>
            </div>

            <div class="vh-ctas">
                <a href="#vid-grid-anchor" class="vh-btn-main">
                    <i class="fas fa-play"></i> Browse Videos
                </a>
                <a href="services.php" class="vh-btn-sec">
                    <i class="fas fa-crown"></i> Go Premium
                </a>
            </div>
        </div>

        <!-- RIGHT — floating video previews -->
        <div class="vh-right">
            <?php foreach ($hero_vids as $hv): ?>
            <div class="vh-preview">
                <div class="vh-prev-thumb">
                    <?php if ($hv['thumb']): ?>
                    <img src="<?php echo htmlspecialchars($hv['thumb']); ?>"
                         alt="<?php echo htmlspecialchars($hv['title'], ENT_QUOTES, 'UTF-8'); ?>"
                         loading="lazy">
                    <?php else: ?>
                    <div class="vh-prev-no-thumb">
                        <i class="fas fa-film" style="font-size:2.5rem;color:#d4af37;opacity:0.3;"></i>
                    </div>
                    <?php endif; ?>
                    <div class="vh-prev-overlay"></div>
                    <div class="vh-prev-play"><i class="fas fa-play"></i></div>
                </div>
                <div class="vh-prev-info">
                    <p class="vh-prev-title"><?php echo htmlspecialchars($hv['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($hero_vids)): ?>
            <!-- Placeholder cards when no videos yet -->
            <?php foreach ([['#1e3a5f','Investment Basics 101'],['#1a2a4a','Market Analysis Guide'],['#0f2040','Portfolio Strategy']] as $ph): ?>
            <div class="vh-preview">
                <div class="vh-prev-no-thumb" style="background:<?php echo $ph[0]; ?>;">
                    <i class="fas fa-play-circle" style="font-size:2.5rem;color:#d4af37;opacity:0.4;"></i>
                </div>
                <div class="vh-prev-info">
                    <p class="vh-prev-title"><?php echo $ph[1]; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div><!-- /.vh-hero-inner -->
</section>

<div id="vid-grid-anchor"></div>

<!-- ═══════════════ BODY ═══════════════ -->
<div class="vid-body">
<div class="vid-body-inner">

    <div class="vid-section-head">
        <p class="vid-section-title">
            Showing <strong><?php echo $total; ?></strong> video<?php echo $total !== 1 ? 's' : ''; ?>
        </p>
        <span class="vid-count-badge">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
    </div>

    <?php if ($videos_q && $videos_q->num_rows > 0): ?>
    <div class="vid-grid" id="vidGrid">

    <?php $i = 0; while ($v = $videos_q->fetch_assoc()):
        $url    = $v['video_url'];
        $locked = (bool)$v['is_locked'];
        $title  = htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8');
        $desc   = htmlspecialchars($v['description'] ?? '', ENT_QUOTES, 'UTF-8');

        // Detect YouTube
        $yt_id = '';
        if (strpos($url,'youtube') !== false || strpos($url,'youtu.be') !== false) {
            preg_match('~(?:youtu\.be/|v=)([^&?/\s]+)~', $url, $m);
            $yt_id = $m[1] ?? '';
        }
        $thumb = $yt_id ? "https://img.youtube.com/vi/$yt_id/hqdefault.jpg" : '';
    ?>

    <div class="vid-card <?php echo $locked ? 'locked' : ''; ?>"
         data-ytid="<?php echo $yt_id; ?>"
         data-delay="<?php echo $i * 60; ?>"
         <?php if (!$locked && $yt_id): ?>onclick="playVideo(this)"<?php endif; ?>>

        <div class="vid-thumb">
            <?php if ($thumb): ?>
            <img src="<?php echo $thumb; ?>" alt="<?php echo $title; ?>" loading="lazy">
            <?php else: ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg,#0f172a,#1e293b);display:flex;align-items:center;justify-content:center;">
                <i class="fab fa-youtube" style="font-size:3rem;color:#d4af37;opacity:0.4;"></i>
            </div>
            <?php endif; ?>
            <div class="vid-thumb-grad"></div>

            <?php if (!$locked): ?>
            <div class="vid-play"><i class="fas fa-play" style="margin-left:4px;"></i></div>
            <?php endif; ?>

            <?php if ($locked): ?>
            <div class="vid-lock-overlay">
                <i class="fas fa-lock"></i>
                <strong>Premium Content</strong>
                <small>Upgrade to watch</small>
                <a href="services.php">Get Premium</a>
            </div>
            <?php endif; ?>
        </div>

        <div class="vid-card-info">
<?php if ($desc): ?>
            <p class="vid-card-desc"><?php echo $desc; ?></p>
            <?php endif; ?>
        </div>

    </div>

    <?php $i++; endwhile; ?>
    </div>

    <?php else: ?>
    <div class="vid-empty">
        <i class="fas fa-film"></i>
        <h3>No videos found</h3>
        <p>Check back soon or browse another category.</p>
        </div>
    <?php endif; ?>

    <!-- ── Pagination ── -->
    <?php if ($total_pages > 1): ?>
    <div class="vid-pagination">
        <a href="?page=<?php echo $page-1; ?>"
           class="vid-page-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <i class="fas fa-chevron-left"></i>
        </a>

        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="?page=<?php echo $p; ?>"
           class="vid-page-btn <?php echo $p === $page ? 'active' : ''; ?>">
            <?php echo $p; ?>
        </a>
        <?php endfor; ?>

        <a href="?page=<?php echo $page+1; ?>"
           class="vid-page-btn <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
    <?php endif; ?>

</div>
</div>

<script>
/* ── Scroll-reveal ── */
(function () {
    var cards = document.querySelectorAll('.vid-card');
    if (!cards.length) return;
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                var delay = parseInt(e.target.dataset.delay) || 0;
                setTimeout(function () { e.target.classList.add('visible'); }, delay);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });
    cards.forEach(function (c) { obs.observe(c); });
}());

/* ── Play video inline ── */
function playVideo(card) {
    var ytId = card.dataset.ytid;
    if (!ytId) return;

    document.querySelectorAll('.vid-card.playing').forEach(function (c) {
        if (c !== card) resetCard(c);
    });

    var thumb = card.querySelector('.vid-thumb');
    card.classList.add('playing');

    var iframe = document.createElement('iframe');
    iframe.src = 'https://www.youtube.com/embed/' + ytId + '?autoplay=1&rel=0&modestbranding=1';
    iframe.allow = 'autoplay; encrypted-media; fullscreen';
    iframe.allowFullscreen = true;
    iframe.style.cssText = 'width:100%;aspect-ratio:16/9;border:none;display:block;';
    thumb.prepend(iframe);
}

function resetCard(card) {
    var iframe = card.querySelector('iframe');
    if (iframe) iframe.remove();
    card.classList.remove('playing');
}
</script>

<?php include 'includes/footer.php'; ?>
