<?php
include 'includes/db.php';
include 'includes/header.php';

// ── Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='articles.php';</script>";
    exit();
}

$id = intval($_GET['id']);

// ── Ensure views column exists (auto-migration)
$vc = $conn->query("SHOW COLUMNS FROM `articles` LIKE 'views'");
if (!$vc || $vc->num_rows === 0) {
    $conn->query("ALTER TABLE `articles` ADD COLUMN `views` INT UNSIGNED NOT NULL DEFAULT 0");
}

// ── Increment view count
$conn->query("UPDATE `articles` SET `views` = `views` + 1 WHERE `id` = $id");

// ── Fetch article
$art = $conn->query("SELECT *, summary AS title, published_date AS created_at, COALESCE(views,0) AS views FROM articles WHERE id = $id")->fetch_assoc();
if (!$art) {
    echo "<div style='padding:80px;text-align:center;color:#94a3b8;background:#020617;min-height:60vh;'>
        <i class='fas fa-file-times' style='font-size:3rem;color:#d4af37;opacity:.3;display:block;margin-bottom:18px;'></i>
        <h2 style='color:#fff;'>Article not found</h2>
        <a href='articles.php' style='color:#d4af37;font-weight:700;text-decoration:none;'>← Back to Articles</a>
    </div>";
    include 'includes/footer.php';
    exit();
}

$title    = htmlspecialchars($art['title'], ENT_QUOTES, 'UTF-8');
$raw_content = $art['content'];
$date     = date('F j, Y', strtotime($art['created_at']));
$date_iso = date('Y-m-d', strtotime($art['created_at']));
$cat      = htmlspecialchars($art['category'], ENT_QUOTES, 'UTF-8');
$img      = !empty($art['image']) ? $art['image'] : 'https://images.unsplash.com/photo-1611974765270-ca12586343bb?q=80&w=1600';

// ── Reading time
$word_count   = str_word_count(strip_tags($raw_content));
$reading_time = max(1, ceil($word_count / 200));

// ── Content: preserve line breaks, allow basic HTML, escape the rest
$content_html = nl2br(htmlspecialchars($raw_content, ENT_QUOTES, 'UTF-8'));

// ── Related articles (same category, different id)
$rel_q = $conn->query("SELECT id, summary AS title, category, image, published_date AS created_at FROM articles
    WHERE category = '" . $conn->real_escape_string($art['category']) . "'
    AND id != $id ORDER BY published_date DESC LIMIT 3");

// ── Prev / Next
$prev_q = $conn->query("SELECT id, summary AS title FROM articles WHERE id < $id ORDER BY id DESC LIMIT 1");
$next_q = $conn->query("SELECT id, summary AS title FROM articles WHERE id > $id ORDER BY id ASC  LIMIT 1");
$prev   = $prev_q ? $prev_q->fetch_assoc() : null;
$next   = $next_q ? $next_q->fetch_assoc() : null;
?>

<style>
/* ════════════════════════════════════════════════
   READ-ARTICLE — FUTURISTIC DARK EDITION
   ════════════════════════════════════════════════ */

/* ── Keyframes ─── */
@keyframes raFadeUp   { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }
@keyframes raShimmer  { from{background-position:-300% center} to{background-position:300% center} }
@keyframes raGlow     { 0%,100%{opacity:.5} 50%{opacity:1} }
@keyframes raProgress { from{width:0%} to{width:100%} }
@keyframes raPulse    { 0%,100%{transform:scale(1)} 50%{transform:scale(1.06)} }
@keyframes raOrb      { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.15) translate(10px,-10px)} }
@keyframes raScan     { 0%{transform:translateY(-100%)} 100%{transform:translateY(100vh)} }

/* ── Reading progress bar ─── */
#ra-progress-wrap {
    position: fixed;
    top: var(--header-total-height, 80px);
    left: 0; right: 0;
    height: 3px;
    background: rgba(255,255,255,0.05);
    z-index: 999;
}
#ra-progress-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #d4af37, #facc15, #f59e0b);
    border-radius: 0 3px 3px 0;
    box-shadow: 0 0 12px rgba(212,175,55,0.6);
    transition: width 0.1s linear;
}

/* ── Global bg ─── */
.ra-page {
    background: #020617;
    color: #e2e8f0;
    font-family: inherit;
}

/* ── HERO ─── */
.ra-hero {
    position: relative;
    height: 75vh;
    min-height: 480px;
    max-height: 700px;
    overflow: hidden;
    display: flex; align-items: flex-end;
}

.ra-hero-img {
    position: absolute; inset: 0;
    background: url('<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>') center/cover no-repeat;
    transform: scale(1.08);
    transition: transform 0.1s linear;
    will-change: transform;
}

.ra-hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(2,6,23,0.3) 0%,
        rgba(2,6,23,0.6) 50%,
        rgba(2,6,23,0.97) 90%,
        #020617 100%
    );
}

/* Scan line effect */
.ra-scanline {
    position: absolute; inset: 0;
    overflow: hidden; pointer-events: none;
}
.ra-scanline::after {
    content: '';
    position: absolute; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(212,175,55,0.15), transparent);
    animation: raScan 6s linear infinite;
}

/* Hero orbs */
.ra-hero-orb {
    position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px);
}
.ra-hero-orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%);
    top: -100px; right: -100px;
    animation: raOrb 8s ease-in-out infinite;
}

.ra-hero-content {
    position: relative; z-index: 2;
    max-width: 900px; margin: 0 auto;
    padding: 0 40px 64px;
    width: 100%;
    animation: raFadeUp 0.8s 0.2s ease both;
}

.ra-cat-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(212,175,55,0.15);
    border: 1px solid rgba(212,175,55,0.4);
    color: #d4af37;
    padding: 5px 18px; border-radius: 99px;
    font-size: 0.72rem; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;
    margin-bottom: 20px;
}

.ra-hero-title {
    font-size: clamp(1.8rem, 4.5vw, 3rem);
    font-weight: 900; color: #fff; margin: 0 0 22px; line-height: 1.18;
    text-shadow: 0 4px 30px rgba(0,0,0,0.6);
}

/* Meta row */
.ra-meta {
    display: flex; align-items: center; flex-wrap: wrap; gap: 18px;
}
.ra-meta-item {
    display: flex; align-items: center; gap: 7px;
    color: #64748b; font-size: 0.83rem;
}
.ra-meta-item i { color: #d4af37; font-size: 0.8rem; }
.ra-meta-item strong { color: #94a3b8; }

/* Reading time pill */
.ra-read-time {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(212,175,55,0.1);
    border: 1px solid rgba(212,175,55,0.25);
    color: #d4af37;
    padding: 4px 14px; border-radius: 99px;
    font-size: 0.78rem; font-weight: 700;
}

/* ── CONTENT LAYOUT ─── */
.ra-layout {
    max-width: 900px; margin: 0 auto;
    padding: 60px 40px 100px;
    position: relative;
}

/* Left accent line */
.ra-layout::before {
    content: '';
    position: absolute;
    left: 0; top: 80px; bottom: 80px;
    width: 2px;
    background: linear-gradient(to bottom, transparent, rgba(212,175,55,0.3), transparent);
    border-radius: 2px;
}

/* ── Article body text ─── */
.ra-body {
    font-size: 1.08rem;
    line-height: 1.85;
    color: #cbd5e1;
    font-family: 'Georgia', 'Times New Roman', serif;
    animation: raFadeUp 0.7s 0.3s ease both;
}

.ra-body p { margin: 0 0 1.6em; }

.ra-body strong, .ra-body b {
    color: #f1f5f9; font-weight: 700;
}

.ra-body em, .ra-body i {
    color: #94a3b8; font-style: italic;
}

.ra-body a {
    color: #d4af37; text-decoration: underline; text-underline-offset: 3px;
}
.ra-body a:hover { color: #facc15; }

/* Drop cap */
.ra-body > p:first-child::first-letter {
    float: left;
    font-size: 4em;
    line-height: 0.8;
    margin: 0.05em 0.12em 0 0;
    color: #d4af37;
    font-weight: 900;
    font-family: 'Georgia', serif;
    text-shadow: 0 0 30px rgba(212,175,55,0.4);
}

/* Quote blocks */
.ra-body blockquote {
    margin: 2em 0;
    padding: 24px 28px;
    background: rgba(212,175,55,0.05);
    border-left: 3px solid #d4af37;
    border-radius: 0 12px 12px 0;
    color: #94a3b8;
    font-style: italic;
    font-size: 1.05em;
}

/* ── Divider ─── */
.ra-divider {
    display: flex; align-items: center; gap: 16px;
    margin: 52px 0;
}
.ra-divider span {
    flex: 1; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
}
.ra-divider i { color: rgba(212,175,55,0.4); font-size: 0.9rem; }

/* ── Share section ─── */
.ra-share {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 52px;
    text-align: center;
    animation: raFadeUp 0.6s ease both;
}
.ra-share h4 {
    color: #94a3b8; font-size: 0.78rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase; margin: 0 0 20px;
}
.ra-share-btns {
    display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;
}
.ra-share-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 12px;
    font-size: 0.84rem; font-weight: 700; text-decoration: none;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
}
.ra-share-btn:hover { transform: translateY(-3px); }
.ra-share-fb { background: #1877f2; color: #fff; }
.ra-share-fb:hover { box-shadow: 0 10px 30px rgba(24,119,242,0.4); }
.ra-share-tw { background: #1da1f2; color: #fff; }
.ra-share-tw:hover { box-shadow: 0 10px 30px rgba(29,161,242,0.4); }
.ra-share-wa { background: #25d366; color: #fff; }
.ra-share-wa:hover { box-shadow: 0 10px 30px rgba(37,211,102,0.4); }
.ra-share-cp {
    background: rgba(212,175,55,0.12);
    border: 1px solid rgba(212,175,55,0.3);
    color: #d4af37;
}
.ra-share-cp:hover { background: rgba(212,175,55,0.2); box-shadow: 0 10px 30px rgba(212,175,55,0.2); }

/* ── Prev / Next nav ─── */
.ra-nav {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    margin-bottom: 60px;
}
.ra-nav-link {
    display: flex; flex-direction: column; gap: 6px;
    padding: 20px 22px; border-radius: 16px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    text-decoration: none;
    transition: all 0.3s;
}
.ra-nav-link:hover {
    background: rgba(212,175,55,0.06);
    border-color: rgba(212,175,55,0.25);
    transform: translateY(-3px);
}
.ra-nav-link.next { text-align: right; }
.ra-nav-label {
    font-size: 0.72rem; font-weight: 700; color: #475569;
    letter-spacing: 1.5px; text-transform: uppercase;
    display: flex; align-items: center; gap: 6px;
}
.ra-nav-link.next .ra-nav-label { justify-content: flex-end; }
.ra-nav-title {
    font-size: 0.9rem; font-weight: 700; color: #e2e8f0;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.ra-nav-link:hover .ra-nav-title { color: #d4af37; }

/* ── Related articles ─── */
.ra-related { padding: 0 40px 80px; max-width: 900px; margin: 0 auto; }
.ra-related-head {
    display: flex; align-items: center; gap: 16px; margin-bottom: 30px;
}
.ra-related-head h3 {
    font-size: 1.1rem; font-weight: 800; color: #fff; margin: 0;
}
.ra-related-head::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, rgba(212,175,55,0.3), transparent);
}

.ra-related-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(240px,1fr)); gap: 20px;
}

.ra-rel-card {
    background: #0a1628;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px; overflow: hidden;
    text-decoration: none;
    display: block;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), border-color 0.3s, box-shadow 0.3s;
    opacity: 0; transform: translateY(20px);
}
.ra-rel-card.visible {
    opacity: 1; transform: translateY(0);
}
.ra-rel-card:hover {
    transform: translateY(-6px);
    border-color: rgba(212,175,55,0.3);
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
}

.ra-rel-img {
    aspect-ratio: 16/9; overflow: hidden;
    background: #0f172a;
}
.ra-rel-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.4s ease;
}
.ra-rel-card:hover .ra-rel-img img { transform: scale(1.06); }

.ra-rel-info { padding: 14px 16px; }
.ra-rel-cat {
    font-size: 0.68rem; font-weight: 800; color: #d4af37;
    letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 6px;
}
.ra-rel-title {
    font-size: 0.88rem; font-weight: 700; color: #e2e8f0;
    line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    margin: 0 0 8px;
}
.ra-rel-date { font-size: 0.72rem; color: #475569; }

/* ── Back link ─── */
.ra-back-wrap {
    max-width: 900px; margin: 0 auto; padding: 20px 40px 0;
}
.ra-back {
    display: inline-flex; align-items: center; gap: 8px;
    color: #475569; font-size: 0.85rem; font-weight: 600;
    text-decoration: none;
    transition: color 0.2s, transform 0.2s;
}
.ra-back:hover { color: #d4af37; transform: translateX(-3px); }
.ra-back i { font-size: 0.78rem; }

/* ── Floating share sidebar (desktop) ─── */
.ra-float-share {
    position: fixed;
    right: 28px;
    top: 50%;
    transform: translateY(-50%);
    display: flex; flex-direction: column; gap: 10px;
    z-index: 100;
    opacity: 0;
    transition: opacity 0.4s;
}
.ra-float-share.show { opacity: 1; }
.ra-float-btn {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; font-size: 1rem;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
    position: relative;
}
.ra-float-btn:hover { transform: scale(1.15) translateX(-3px); }
.ra-float-btn::after {
    content: attr(data-tip);
    position: absolute; right: calc(100% + 10px);
    background: #1e293b; color: #e2e8f0;
    font-size: 0.72rem; font-weight: 700;
    padding: 5px 10px; border-radius: 7px; white-space: nowrap;
    opacity: 0; pointer-events: none;
    transition: opacity 0.2s;
}
.ra-float-btn:hover::after { opacity: 1; }
.ra-float-fb  { background: #1877f2; color: #fff; }
.ra-float-tw  { background: #1da1f2; color: #fff; }
.ra-float-wa  { background: #25d366; color: #fff; }
.ra-float-cp  { background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.3); color: #d4af37; }

/* ── Responsive ─── */
@media (max-width: 768px) {
    .ra-hero-content { padding: 0 20px 48px; }
    .ra-layout { padding: 40px 20px 60px; }
    .ra-layout::before { display: none; }
    .ra-related { padding: 0 20px 60px; }
    .ra-nav { grid-template-columns: 1fr; }
    .ra-nav-link.next { text-align: left; }
    .ra-nav-link.next .ra-nav-label { justify-content: flex-start; }
    .ra-float-share { display: none; }
    .ra-back-wrap { padding: 20px 20px 0; }
}
</style>

<!-- ── Progress bar ── -->
<div id="ra-progress-wrap"><div id="ra-progress-bar"></div></div>

<div class="ra-page">

<!-- ── Floating share sidebar ── -->
<div class="ra-float-share" id="raFloatShare">
    <a class="ra-float-btn ra-float-fb" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener" data-tip="Facebook"><i class="fab fa-facebook-f"></i></a>
    <a class="ra-float-btn ra-float-tw" href="https://twitter.com/intent/tweet?text=<?php echo urlencode($title); ?>&url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener" data-tip="Twitter"><i class="fab fa-twitter"></i></a>
    <a class="ra-float-btn ra-float-wa" href="https://wa.me/?text=<?php echo urlencode($title.' — http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" target="_blank" rel="noopener" data-tip="WhatsApp"><i class="fab fa-whatsapp"></i></a>
    <a class="ra-float-btn ra-float-cp" href="#" onclick="copyLink(event)" data-tip="Copy link"><i class="fas fa-link"></i></a>
</div>

<!-- ════════════════ HERO ════════════════ -->
<section class="ra-hero">
    <div class="ra-hero-img" id="raParallax"></div>
    <div class="ra-hero-overlay"></div>
    <div class="ra-scanline"></div>
    <div class="ra-hero-orb ra-hero-orb-1"></div>

    <div class="ra-hero-content">
        <div class="ra-cat-badge">
            <i class="fas fa-tag"></i> <?php echo $cat; ?>
        </div>
        <h1 class="ra-hero-title"><?php echo $title; ?></h1>
        <div class="ra-meta">
            <div class="ra-meta-item">
                <i class="fas fa-calendar-alt"></i>
                <strong><?php echo $date; ?></strong>
            </div>
            <div class="ra-meta-item">
                <i class="fas fa-book-reader"></i>
                <strong><?php echo $word_count; ?> words</strong>
            </div>
            <div class="ra-read-time">
                <i class="fas fa-clock"></i> <?php echo $reading_time; ?> min read
            </div>
            <div class="ra-meta-item" style="background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.2);padding:4px 14px;border-radius:99px;">
                <i class="fas fa-eye"></i>
                <strong style="color:#d4af37;"><?php
                    $v = (int)$art['views'];
                    if ($v >= 1000000) echo round($v/1000000,1).'M';
                    elseif ($v >= 1000) echo round($v/1000,1).'K';
                    else echo $v;
                ?></strong>
                <span style="color:#64748b;font-size:.78rem;">reads</span>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════ BACK LINK ════════════════ -->
<div class="ra-back-wrap">
    <a href="articles.php" class="ra-back">
        <i class="fas fa-arrow-left"></i> Back to Articles
    </a>
</div>

<!-- ════════════════ CONTENT ════════════════ -->
<div class="ra-layout">
    <article class="ra-body">
        <?php echo $content_html; ?>
    </article>

    <div class="ra-divider">
        <span></span><i class="fas fa-star"></i><span></span>
    </div>

    <!-- ── Share ── -->
    <div class="ra-share" id="ra-share-section">
        <h4><i class="fas fa-share-alt"></i> &nbsp;Share this article</h4>
        <div class="ra-share-btns">
            <a class="ra-share-btn ra-share-fb"
               href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"
               target="_blank" rel="noopener">
                <i class="fab fa-facebook-f"></i> Facebook
            </a>
            <a class="ra-share-btn ra-share-tw"
               href="https://twitter.com/intent/tweet?text=<?php echo urlencode($title); ?>&url=<?php echo urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"
               target="_blank" rel="noopener">
                <i class="fab fa-twitter"></i> Twitter
            </a>
            <a class="ra-share-btn ra-share-wa"
               href="https://wa.me/?text=<?php echo urlencode($title.' — http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"
               target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a class="ra-share-btn ra-share-cp" href="#" onclick="copyLink(event)">
                <i class="fas fa-link"></i> Copy Link
            </a>
        </div>
    </div>

    <!-- ── Prev / Next ── -->
    <div class="ra-nav">
        <?php if ($prev): ?>
        <a href="read-article.php?id=<?php echo $prev['id']; ?>" class="ra-nav-link prev">
            <div class="ra-nav-label"><i class="fas fa-arrow-left"></i> Previous</div>
            <div class="ra-nav-title"><?php echo htmlspecialchars($prev['title'], ENT_QUOTES, 'UTF-8'); ?></div>
        </a>
        <?php else: ?>
        <div></div>
        <?php endif; ?>

        <?php if ($next): ?>
        <a href="read-article.php?id=<?php echo $next['id']; ?>" class="ra-nav-link next">
            <div class="ra-nav-label">Next <i class="fas fa-arrow-right"></i></div>
            <div class="ra-nav-title"><?php echo htmlspecialchars($next['title'], ENT_QUOTES, 'UTF-8'); ?></div>
        </a>
        <?php else: ?>
        <div></div>
        <?php endif; ?>
    </div>

</div><!-- /.ra-layout -->

<!-- ════════════════ RELATED ════════════════ -->
<?php if ($rel_q && $rel_q->num_rows > 0): ?>
<div class="ra-related">
    <div class="ra-related-head">
        <h3><i class="fas fa-layer-group" style="color:#d4af37;margin-right:8px;"></i>Related Articles</h3>
    </div>
    <div class="ra-related-grid">
        <?php $ri = 0; while ($r = $rel_q->fetch_assoc()):
            $r_img  = !empty($r['image']) ? $r['image'] : 'https://images.unsplash.com/photo-1611974765270-ca12586343bb?q=80&w=600';
            $r_date = date('M j, Y', strtotime($r['created_at']));
        ?>
        <a href="read-article.php?id=<?php echo $r['id']; ?>"
           class="ra-rel-card"
           data-rdelay="<?php echo $ri * 80; ?>">
            <div class="ra-rel-img">
                <img src="<?php echo htmlspecialchars($r_img, ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8'); ?>"
                     loading="lazy">
            </div>
            <div class="ra-rel-info">
                <div class="ra-rel-cat"><?php echo htmlspecialchars($r['category'], ENT_QUOTES, 'UTF-8'); ?></div>
                <p class="ra-rel-title"><?php echo htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="ra-rel-date"><i class="fas fa-clock" style="margin-right:4px;"></i><?php echo $r_date; ?></div>
            </div>
        </a>
        <?php $ri++; endwhile; ?>
    </div>
</div>
<?php endif; ?>

</div><!-- /.ra-page -->

<script>
(function () {

    /* ── Reading progress bar ── */
    var bar = document.getElementById('ra-progress-bar');
    function updateProgress() {
        var scrolled = window.scrollY;
        var total    = document.body.scrollHeight - window.innerHeight;
        bar.style.width = (total > 0 ? Math.min(100, (scrolled / total) * 100) : 0) + '%';
    }
    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();

    /* ── Parallax hero ── */
    var parallax = document.getElementById('raParallax');
    function updateParallax() {
        if (!parallax) return;
        var y = window.scrollY * 0.35;
        parallax.style.transform = 'scale(1.08) translateY(' + y + 'px)';
    }
    window.addEventListener('scroll', updateParallax, { passive: true });

    /* ── Floating share sidebar visibility ── */
    var floatShare = document.getElementById('raFloatShare');
    function toggleFloatShare() {
        if (window.scrollY > 300) {
            floatShare.classList.add('show');
        } else {
            floatShare.classList.remove('show');
        }
    }
    window.addEventListener('scroll', toggleFloatShare, { passive: true });
    toggleFloatShare();

    /* ── Scroll-reveal related cards ── */
    var relCards = document.querySelectorAll('.ra-rel-card');
    if (relCards.length) {
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    var d = parseInt(e.target.dataset.rdelay) || 0;
                    setTimeout(function () {
                        e.target.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        e.target.classList.add('visible');
                    }, d);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });
        relCards.forEach(function (c) { obs.observe(c); });
    }

}());

/* ── Copy link ── */
function copyLink(e) {
    e.preventDefault();
    var url = window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () { showCopied(); });
    } else {
        var ta = document.createElement('textarea');
        ta.value = url; document.body.appendChild(ta);
        ta.select(); document.execCommand('copy');
        document.body.removeChild(ta);
        showCopied();
    }
}

function showCopied() {
    var toast = document.createElement('div');
    toast.textContent = 'Link copied!';
    toast.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%);' +
        'background:linear-gradient(135deg,#d4af37,#facc15);color:#020617;' +
        'font-weight:800;font-size:0.85rem;padding:10px 24px;border-radius:12px;' +
        'box-shadow:0 8px 30px rgba(212,175,55,0.4);z-index:9999;' +
        'animation:raFadeUp 0.3s ease;';
    document.body.appendChild(toast);
    setTimeout(function () { toast.remove(); }, 2200);
}
</script>

<?php include 'includes/footer.php'; ?>
