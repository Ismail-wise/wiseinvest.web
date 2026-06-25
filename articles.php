<?php
include 'includes/db.php';
include 'includes/header.php';

// ── Auto-add views column if missing (one-time migration)
$vc = $conn->query("SHOW COLUMNS FROM `articles` LIKE 'views'");
if (!$vc || $vc->num_rows === 0) {
    $conn->query("ALTER TABLE `articles` ADD COLUMN `views` INT UNSIGNED NOT NULL DEFAULT 0");
}

// ── View count formatter
function fmtViews($n) {
    $n = (int)$n;
    if ($n >= 1000000) return round($n / 1000000, 1) . 'M';
    if ($n >= 1000)    return round($n / 1000, 1) . 'K';
    return $n > 0 ? $n : '—';
}

// ── Filters
$search     = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_cat = isset($_GET['cat'])    ? trim($_GET['cat'])    : '';
$s_esc      = $conn->real_escape_string($search);
$c_esc      = $conn->real_escape_string($filter_cat);

// ── Hero preview: latest 3 articles
$hero_res  = $conn->query("SELECT id, summary AS title, category, image, published_date AS created_at FROM articles ORDER BY published_date DESC LIMIT 3");
$hero_arts = [];
while ($row = $hero_res->fetch_assoc()) $hero_arts[] = $row;

// ── Main query (include views)
$where = "WHERE 1=1";
if ($search)     $where .= " AND (summary LIKE '%$s_esc%' OR content LIKE '%$s_esc%' OR category LIKE '%$s_esc%')";
if ($filter_cat) $where .= " AND category = '$c_esc'";
$articles_res = $conn->query("SELECT *, summary AS title, published_date AS created_at, COALESCE(views,0) AS views FROM articles $where ORDER BY published_date DESC");

// ── Categories
$cats_res   = $conn->query("SELECT DISTINCT category FROM articles WHERE category != '' ORDER BY category ASC");
$categories = [];
while ($row = $cats_res->fetch_assoc()) $categories[] = $row['category'];
$total_count = (int)$conn->query("SELECT COUNT(*) FROM articles")->fetch_row()[0];

// ── Total reads & trending IDs
$total_views = (int)($conn->query("SELECT COALESCE(SUM(views),0) FROM articles")->fetch_row()[0] ?? 0);
$trending_ids = [];
$tr = $conn->query("SELECT id FROM articles WHERE COALESCE(views,0) > 0 ORDER BY COALESCE(views,0) DESC LIMIT 3");
if ($tr) while ($t = $tr->fetch_assoc()) $trending_ids[] = (int)$t['id'];
?>
<style>
/* ════════════════════════════════════════════════
   ARTICLES PAGE  — Premium Dark v4 (with Views)
   ════════════════════════════════════════════════ */

/* ── Keyframes ── */
@keyframes aFadeUp   { from{opacity:0;transform:translateY(36px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes aShimmer  { from{background-position:-400% center} to{background-position:400% center} }
@keyframes aFloat    { 0%,100%{transform:translateY(0) rotate(var(--r,0deg))} 50%{transform:translateY(-18px) rotate(var(--r,0deg))} }
@keyframes aOrb1     { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(40px,-30px) scale(1.1)} 66%{transform:translate(-25px,20px) scale(.93)} }
@keyframes aOrb2     { 0%,100%{transform:translate(0,0) scale(1)} 40%{transform:translate(-38px,28px) scale(1.08)} 70%{transform:translate(28px,-18px) scale(.95)} }
@keyframes aOrb3     { 0%,100%{transform:translate(0,0)} 50%{transform:translate(20px,30px)} }
@keyframes aBadgePop { 0%{transform:scale(.8);opacity:0} 70%{transform:scale(1.06)} 100%{transform:scale(1);opacity:1} }
@keyframes aGlowDot  { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.5)} 50%{box-shadow:0 0 0 7px rgba(212,175,55,0)} }
@keyframes aFire     { 0%,100%{transform:scale(1) rotate(-5deg)} 50%{transform:scale(1.25) rotate(6deg)} }
@keyframes aRipple   { to{transform:scale(3.5);opacity:0} }
@keyframes aViewIn   { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
@keyframes aTrendPop { 0%{transform:scale(0) rotate(-10deg)} 70%{transform:scale(1.1) rotate(2deg)} 100%{transform:scale(1) rotate(0)} }
@keyframes aGlow     { 0%,100%{box-shadow:0 0 8px rgba(212,175,55,.15)} 50%{box-shadow:0 0 24px rgba(212,175,55,.4)} }

/* ── Hero ── */
.ah-hero {
    position:relative; overflow:hidden;
    background:linear-gradient(148deg,#010510 0%,#040d1c 40%,#091422 75%,#050c18 100%);
    padding:96px 52px 84px;
    display:grid;
    grid-template-columns:1fr 450px;
    gap:68px;
    align-items:center;
}
.ah-hero-bg {
    position:absolute; inset:0; pointer-events:none;
    background-image:radial-gradient(rgba(212,175,55,.2) 1px,transparent 1px);
    background-size:38px 38px;
    opacity:.26;
}
.ah-hero-bg::after {
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(ellipse 90% 90% at 50% 50%,transparent 20%,#020617 100%);
}
.ah-orb { position:absolute; border-radius:50%; pointer-events:none; filter:blur(90px); }
.ah-orb-1 {
    width:560px; height:560px;
    background:radial-gradient(circle,rgba(212,175,55,.13) 0%,transparent 70%);
    top:-140px; left:-100px;
    animation:aOrb1 20s ease-in-out infinite;
}
.ah-orb-2 {
    width:420px; height:420px;
    background:radial-gradient(circle,rgba(99,102,241,.1) 0%,transparent 70%);
    top:60px; right:-80px;
    animation:aOrb2 15s ease-in-out infinite;
}
.ah-orb-3 {
    width:320px; height:320px;
    background:radial-gradient(circle,rgba(212,175,55,.07) 0%,transparent 70%);
    bottom:-60px; left:38%;
    animation:aOrb3 22s 4s ease-in-out infinite;
}

/* Left content */
.ah-hero-left { position:relative; z-index:2; }
.ah-badge {
    display:inline-flex; align-items:center; gap:9px;
    background:rgba(212,175,55,.09); border:1px solid rgba(212,175,55,.22);
    color:#d4af37; padding:7px 22px; border-radius:99px;
    font-size:.71rem; font-weight:800; letter-spacing:2.5px; text-transform:uppercase;
    margin-bottom:26px;
    animation:aBadgePop .7s .05s ease both;
}
.ah-badge-dot {
    width:7px; height:7px; border-radius:50%;
    background:#d4af37; flex-shrink:0;
    animation:aGlowDot 2s ease-in-out infinite;
}
.ah-hero h1 {
    font-size:clamp(2.4rem,4vw,3.9rem); font-weight:900; color:#fff;
    line-height:1.1; margin:0 0 20px;
    font-family:'Playfair Display',serif;
    animation:aFadeUp .7s .15s ease both;
}
.ah-hero h1 em {
    font-style:normal;
    background:linear-gradient(100deg,#9a6a10 0%,#d4af37 20%,#facc15 42%,#fffde0 52%,#facc15 65%,#d4af37 82%,#9a6a10 100%);
    background-size:300% auto;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    animation:aShimmer 5s linear infinite;
}
.ah-hero-desc {
    color:#7a90ab; font-size:1.04rem; line-height:1.78; max-width:500px;
    margin:0 0 38px;
    animation:aFadeUp .7s .28s ease both;
}

/* Search */
.ah-search-wrap { animation:aFadeUp .7s .38s ease both; margin-bottom:42px; }
.ah-search {
    display:flex; max-width:490px;
    background:rgba(255,255,255,.035);
    border:1px solid rgba(212,175,55,.2);
    border-radius:50px; overflow:hidden;
    transition:border-color .32s, box-shadow .35s, background .32s;
    box-shadow:0 4px 24px rgba(0,0,0,.35);
}
.ah-search:focus-within {
    border-color:#d4af37;
    box-shadow:0 0 0 4px rgba(212,175,55,.1), 0 10px 36px rgba(0,0,0,.45);
    background:rgba(255,255,255,.055);
}
.ah-search input {
    flex:1; background:none; border:none; outline:none;
    color:#f1f5f9; font-size:.95rem; padding:14px 24px; font-family:inherit;
}
.ah-search input::placeholder { color:#2e3e55; }
.ah-search button {
    background:linear-gradient(135deg,#b8820e,#facc15);
    border:none; cursor:pointer; padding:0 28px;
    color:#020617; font-size:.95rem; font-weight:800;
    transition:opacity .2s, transform .18s;
    position:relative; overflow:hidden;
}
.ah-search button:hover { opacity:.88; transform:scale(1.04); }

/* Stats */
.ah-stats { display:flex; gap:34px; flex-wrap:wrap; animation:aFadeUp .7s .48s ease both; }
.ah-stat { display:flex; flex-direction:column; gap:3px; }
.ah-stat strong {
    font-size:1.85rem; font-weight:900; color:#d4af37; line-height:1;
    font-variant-numeric:tabular-nums;
}
.ah-stat span { font-size:.68rem; color:#2e3e55; letter-spacing:2px; text-transform:uppercase; font-weight:700; }
.ah-stat.reads strong { color:#f97316; }

/* Right — floating previews */
.ah-hero-right { position:relative; z-index:2; height:410px; }
.ah-preview {
    position:absolute; width:234px;
    background:rgba(8,16,38,.82);
    backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.09);
    border-radius:18px; overflow:hidden; text-decoration:none;
    box-shadow:0 28px 70px rgba(0,0,0,.75);
    transition:box-shadow .35s, border-color .3s, transform .3s;
}
.ah-preview:hover {
    box-shadow:0 36px 90px rgba(0,0,0,.9), 0 0 0 1px rgba(212,175,55,.4);
    border-color:rgba(212,175,55,.28);
}
.ah-preview img { width:100%; height:122px; object-fit:cover; display:block; }
.ah-preview-body { padding:14px; }
.ah-preview-cat { font-size:.63rem; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; color:#d4af37; margin-bottom:6px; }
.ah-preview-title { font-size:.83rem; font-weight:700; color:#e2e8f0; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.ah-preview:nth-child(1) { --r:-4deg; top:0;    left:0;    animation:aFloat 6.5s 0s   ease-in-out infinite; }
.ah-preview:nth-child(2) { --r: 3deg; top:95px; left:175px;animation:aFloat 7.5s 1.2s ease-in-out infinite; }
.ah-preview:nth-child(3) { --r:-2deg; top:238px;left:28px; animation:aFloat 5.8s .6s  ease-in-out infinite; }

/* ── Filter bar ── */
.ah-filter-bar {
    background:rgba(2,6,23,.97);
    backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
    border-bottom:1px solid rgba(255,255,255,.05);
    position:sticky; top:var(--header-total-height); z-index:50;
}
.ah-filter-inner {
    max-width:1340px; margin:0 auto;
    display:flex; align-items:center; gap:8px;
    padding:12px 26px; overflow-x:auto; scrollbar-width:none;
}
.ah-filter-inner::-webkit-scrollbar { display:none; }
.ah-pill {
    flex-shrink:0; padding:8px 20px; border-radius:99px;
    background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.07);
    color:#445e7a; font-size:.82rem; font-weight:600;
    text-decoration:none; white-space:nowrap;
    transition:all .28s cubic-bezier(.34,1.56,.64,1);
    letter-spacing:.3px; position:relative; overflow:hidden;
}
.ah-pill:hover {
    color:#d4e2f0; border-color:rgba(255,255,255,.18);
    background:rgba(255,255,255,.06); transform:translateY(-2px);
}
.ah-pill.active {
    background:linear-gradient(135deg,#b8820e,#facc15);
    border-color:transparent; color:#020617; font-weight:800;
    box-shadow:0 4px 22px rgba(212,175,55,.32);
    transform:translateY(-2px);
}

/* ── Body ── */
.ah-body { background:#020617; padding:54px 26px 110px; min-height:60vh; }
.ah-container {
    max-width:1340px; margin:0 auto;
    display:grid; grid-template-columns:1fr 304px; gap:46px; align-items:start;
}

/* ── Card grid ── */
.ah-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:26px; }

/* ── Article card ── */
.ah-card {
    background:linear-gradient(148deg,#07121f 0%,#091828 100%);
    border:1px solid rgba(255,255,255,.065);
    border-radius:18px; overflow:hidden;
    display:flex; flex-direction:column;
    opacity:0; transform:translateY(34px) scale(.97);
    transition:box-shadow .4s ease, border-color .32s ease, transform .5s cubic-bezier(.34,1.56,.64,1), opacity .5s ease;
    will-change:transform, opacity;
    position:relative; isolation:isolate;
}
.ah-card.vis { opacity:1; transform:translateY(0) scale(1); }

/* Gold accent line on hover */
.ah-card::after {
    content:''; position:absolute; top:0; left:18%; right:18%; height:1.5px;
    background:linear-gradient(90deg,transparent,rgba(212,175,55,.55),transparent);
    opacity:0; transform:scaleX(0); transform-origin:center; transition:none;
}
.ah-card.vis:hover::after { opacity:1; transform:scaleX(1); transition:opacity .3s, transform .4s ease; }

/* Trending card glow */
.ah-card.trending { animation:aGlow 3s ease-in-out infinite; }
.ah-card.no-tilt:hover {
    transform:translateY(-8px) scale(1.01);
    box-shadow:0 28px 64px rgba(0,0,0,.65), 0 0 0 1px rgba(212,175,55,.25);
    border-color:rgba(212,175,55,.2);
}
.ah-glare { position:absolute; inset:0; border-radius:inherit; pointer-events:none; z-index:10; opacity:0; transition:opacity .25s; }

/* Featured */
.ah-card.feat { grid-column:1/-1; flex-direction:row; }
.ah-card.feat .ah-card-img { width:44%; min-height:290px; flex-shrink:0; }
.ah-card.feat .ah-card-body { padding:38px; justify-content:center; }
.ah-card.feat .ah-card-title { font-size:1.52rem; }
.ah-feat-badge {
    display:inline-flex; align-items:center; gap:7px;
    background:linear-gradient(135deg,rgba(212,175,55,.14),rgba(212,175,55,.05));
    border:1px solid rgba(212,175,55,.28);
    color:#d4af37; font-size:.67rem; font-weight:800;
    letter-spacing:1.5px; text-transform:uppercase;
    padding:5px 14px; border-radius:99px; margin-bottom:14px;
}

/* Image */
.ah-card-img { position:relative; height:214px; overflow:hidden; }
.ah-card-img img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .65s cubic-bezier(.25,.46,.45,.94); }
.ah-card:hover .ah-card-img img { transform:scale(1.09); }
.ah-card-img-grad { position:absolute; inset:0; background:linear-gradient(to top,rgba(7,18,31,.95) 0%,rgba(7,18,31,.28) 55%,transparent 100%); }
.ah-card-cat {
    position:absolute; top:13px; left:13px;
    font-size:.63rem; font-weight:800; letter-spacing:1.5px; text-transform:uppercase;
    padding:4px 12px; border-radius:99px;
    background:rgba(212,175,55,.9); color:#020617;
    backdrop-filter:blur(6px); transition:transform .2s;
}
.ah-card:hover .ah-card-cat { transform:scale(1.05); }

/* Trending badge */
.ah-trend-badge {
    position:absolute; top:13px; right:13px;
    display:flex; align-items:center; gap:5px;
    background:rgba(249,115,22,.9);
    backdrop-filter:blur(6px);
    color:#fff; font-size:.62rem; font-weight:800;
    letter-spacing:1px; text-transform:uppercase;
    padding:4px 10px; border-radius:99px;
    animation:aTrendPop .5s ease both;
}
.ah-trend-badge i { animation:aFire .7s ease-in-out infinite; }

/* Body */
.ah-card-body { padding:22px; flex:1; display:flex; flex-direction:column; }
.ah-card-title { font-size:1.07rem; font-weight:700; color:#eef2f8; line-height:1.46; margin:0 0 10px; }
.ah-card-title a { text-decoration:none; color:inherit; transition:color .22s; }
.ah-card-title a:hover { color:#d4af37; }
.ah-card-exc {
    color:#374f68; font-size:.875rem; line-height:1.7; flex:1; margin:0 0 18px;
    display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;
}
.ah-card-foot {
    display:flex; align-items:center; justify-content:space-between;
    padding-top:14px; border-top:1px solid rgba(255,255,255,.055); gap:8px;
}
.ah-card-meta { display:flex; gap:10px; color:#2d4057; font-size:.77rem; flex-wrap:wrap; align-items:center; }
.ah-card-meta span { display:flex; align-items:center; gap:4px; }
/* Views count highlight */
.ah-views-badge {
    display:inline-flex; align-items:center; gap:4px;
    color:#7a90ab; font-size:.77rem; font-weight:600;
    opacity:0;
    animation:aViewIn .4s ease both;
}
.ah-views-badge.loaded { opacity:1; }
.ah-views-badge i { color:#d4af37; font-size:.72rem; }

.ah-read-btn {
    display:inline-flex; align-items:center; gap:6px;
    color:#d4af37; font-size:.79rem; font-weight:800;
    padding:7px 16px; border-radius:99px;
    border:1px solid rgba(212,175,55,.28);
    text-decoration:none;
    transition:all .24s cubic-bezier(.34,1.56,.64,1);
    white-space:nowrap; flex-shrink:0;
    background:rgba(212,175,55,.04);
    position:relative; overflow:hidden;
}
.ah-read-btn:hover {
    background:#d4af37; color:#020617; border-color:#d4af37;
    box-shadow:0 6px 20px rgba(212,175,55,.35); transform:scale(1.06);
}
.ah-read-btn i { font-size:.72rem; transition:transform .22s; }
.ah-read-btn:hover i { transform:translateX(4px); }

/* ── Sidebar ── */
.ah-sidebar { position:sticky; top:calc(var(--header-total-height) + 68px); }
.ah-sbox {
    background:linear-gradient(148deg,#07121f,#091828);
    border:1px solid rgba(255,255,255,.065);
    border-radius:16px; padding:24px; margin-bottom:22px;
    position:relative; overflow:hidden;
}
.ah-sbox::before {
    content:''; position:absolute; top:0; left:14%; right:14%; height:1px;
    background:linear-gradient(90deg,transparent,rgba(212,175,55,.35),transparent);
}
.ah-stitle {
    font-size:.67rem; font-weight:800; letter-spacing:2.5px; text-transform:uppercase;
    color:#d4af37; margin:0 0 18px; padding-bottom:13px;
    border-bottom:1px solid rgba(212,175,55,.1);
    display:flex; align-items:center; gap:8px;
}
.ah-scat-list { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:3px; }
.ah-scat-list a {
    display:flex; align-items:center; justify-content:space-between;
    padding:10px 12px; border-radius:10px; color:#445e7a;
    font-size:.875rem; text-decoration:none;
    border:1px solid transparent; transition:all .22s; gap:8px;
}
.ah-scat-list a:hover, .ah-scat-list a.active { background:rgba(212,175,55,.07); border-color:rgba(212,175,55,.16); color:#d4af37; }
.ah-scat-list .cnt {
    background:rgba(255,255,255,.045); padding:2px 9px;
    border-radius:99px; font-size:.69rem; color:#253547;
    flex-shrink:0; min-width:28px; text-align:center;
    transition:background .22s, color .22s;
}
.ah-scat-list a.active .cnt { background:rgba(212,175,55,.15); color:#d4af37; }

/* Latest */
.ah-latest-item {
    display:flex; align-items:center; gap:12px;
    padding:11px 6px; border-bottom:1px solid rgba(255,255,255,.04);
    text-decoration:none; transition:background .2s; border-radius:8px; margin:0 -6px;
}
.ah-latest-item:last-child { border-bottom:none; }
.ah-latest-item:hover { background:rgba(255,255,255,.03); }
.ah-latest-num { font-size:1.18rem; font-weight:900; color:rgba(212,175,55,.18); line-height:1; min-width:24px; text-align:center; transition:color .22s; font-variant-numeric:tabular-nums; }
.ah-latest-item:hover .ah-latest-num { color:#d4af37; }
.ah-latest-info { flex:1; min-width:0; }
.ah-latest-title { font-size:.82rem; color:#445e7a; line-height:1.44; transition:color .22s; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.ah-latest-item:hover .ah-latest-title { color:#e2e8f0; }
.ah-latest-views { font-size:.7rem; color:#2d3e57; margin-top:3px; display:flex; align-items:center; gap:3px; }
.ah-latest-views i { color:rgba(212,175,55,.4); font-size:.65rem; }

/* Most-read sidebar box */
.ah-sbox.hot .ah-stitle { color:#f97316; border-color:rgba(249,115,22,.15); }
.ah-sbox.hot .ah-stitle i { animation:aFire .8s ease-in-out infinite; }

/* ── Empty state ── */
.ah-empty { grid-column:1/-1; text-align:center; padding:96px 20px; animation:aFadeUp .6s ease both; }
.ah-empty-icon { width:84px; height:84px; border-radius:50%; background:rgba(212,175,55,.05); border:1px solid rgba(212,175,55,.1); display:flex; align-items:center; justify-content:center; margin:0 auto 22px; }
.ah-empty-icon i { font-size:2.2rem; color:rgba(212,175,55,.25); }
.ah-empty h3 { color:#445e7a; margin:0 0 8px; font-size:1.12rem; }
.ah-empty p { color:#253547; font-size:.9rem; margin:0; }
.ah-empty-link { display:inline-block; margin-top:20px; color:#d4af37; font-weight:700; text-decoration:none; padding:10px 24px; border:1px solid rgba(212,175,55,.28); border-radius:99px; transition:all .24s cubic-bezier(.34,1.56,.64,1); font-size:.88rem; }
.ah-empty-link:hover { background:#d4af37; color:#020617; transform:scale(1.04); }

/* Ripple */
.ah-ripple { position:absolute; border-radius:50%; background:rgba(255,255,255,.25); transform:scale(0); animation:aRipple .55s ease; pointer-events:none; }

/* ── Responsive ── */
@media(max-width:1100px) {
    .ah-hero { grid-template-columns:1fr; padding:72px 30px 60px; }
    .ah-hero-right { display:none; }
    .ah-container { grid-template-columns:1fr; }
    .ah-sidebar { position:static; }
    .ah-card.feat { flex-direction:column; }
    .ah-card.feat .ah-card-img { width:100%; min-height:240px; }
}
@media(max-width:640px) {
    .ah-grid { grid-template-columns:1fr; }
    .ah-hero h1 { font-size:2.1rem; }
    .ah-hero { padding:62px 20px 52px; }
    .ah-body { padding:36px 16px 80px; }
}
</style>

<!-- ══════════ HERO ══════════ -->
<section class="ah-hero">
    <div class="ah-hero-bg"></div>
    <div class="ah-orb ah-orb-1"></div>
    <div class="ah-orb ah-orb-2"></div>
    <div class="ah-orb ah-orb-3"></div>

    <div class="ah-hero-left">
        <span class="ah-badge"><span class="ah-badge-dot"></span> Market Intelligence</span>
        <h1>Expert Articles &amp;<br><em>Investment Insights</em></h1>
        <p class="ah-hero-desc">In-depth market analysis, financial education, and investment strategies curated for global investors.</p>

        <div class="ah-search-wrap">
            <form class="ah-search" action="articles.php" method="GET">
                <input type="text" name="search" placeholder="Search topics, markets, strategies…"
                       value="<?php echo htmlspecialchars($search,'ENT_QUOTES','UTF-8'); ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <?php $cat_count = count($categories); ?>
        <div class="ah-stats">
            <div class="ah-stat">
                <strong data-count="<?php echo $total_count; ?>">0</strong>
                <span>Articles</span>
            </div>
            <div class="ah-stat">
                <strong data-count="<?php echo $cat_count; ?>">0</strong>
                <span>Topics</span>
            </div>
            <div class="ah-stat reads">
                <strong data-count="<?php echo $total_views; ?>">0</strong>
                <span>Total Reads</span>
            </div>
            <div class="ah-stat">
                <strong>Daily</strong>
                <span>Updates</span>
            </div>
        </div>
    </div>

    <!-- Right: floating preview cards -->
    <div class="ah-hero-right">
        <?php foreach ($hero_arts as $ha):
            $ha_img   = !empty($ha['image']) ? htmlspecialchars($ha['image']) : 'https://images.unsplash.com/photo-1611974765270-ca12586343bb?q=80&w=400';
            $ha_cat   = htmlspecialchars($ha['category'],'ENT_QUOTES','UTF-8');
            $ha_title = htmlspecialchars($ha['title'],'ENT_QUOTES','UTF-8');
        ?>
        <a href="read-article.php?id=<?php echo $ha['id']; ?>" class="ah-preview">
            <img src="<?php echo $ha_img; ?>" alt="" loading="lazy">
            <div class="ah-preview-body">
                <?php if($ha_cat): ?><div class="ah-preview-cat"><?php echo $ha_cat; ?></div><?php endif; ?>
                <div class="ah-preview-title"><?php echo $ha_title; ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ══════════ FILTER PILLS ══════════ -->
<div class="ah-filter-bar">
    <div class="ah-filter-inner">
        <a href="articles.php" class="ah-pill <?php echo !$filter_cat?'active':''; ?>">
            <i class="fas fa-th-large"></i> All
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="articles.php?cat=<?php echo urlencode($cat); ?>"
           class="ah-pill <?php echo $filter_cat===$cat?'active':''; ?>">
            <?php echo htmlspecialchars($cat,'ENT_QUOTES','UTF-8'); ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ══════════ BODY ══════════ -->
<div class="ah-body">
<div class="ah-container">

    <!-- Grid -->
    <div class="ah-grid" id="ahGrid">
    <?php if ($articles_res && $articles_res->num_rows > 0):
        $i = 0;
        while ($art = $articles_res->fetch_assoc()):
            $id       = $art['id'];
            $title    = htmlspecialchars($art['title'],'ENT_QUOTES','UTF-8');
            $cat      = htmlspecialchars($art['category'],'ENT_QUOTES','UTF-8');
            $date     = date('M d, Y', strtotime($art['created_at']));
            $excerpt  = mb_substr(strip_tags($art['content']), 0, 160, 'UTF-8') . '…';
            $img      = !empty($art['image']) ? htmlspecialchars($art['image']) : 'https://images.unsplash.com/photo-1611974765270-ca12586343bb?q=80&w=800';
            $rt       = max(1, ceil(str_word_count(strip_tags($art['content'])) / 200));
            $feat     = ($i === 0);
            $views    = (int)$art['views'];
            $is_trend = in_array((int)$id, $trending_ids) && $views > 0;
    ?>
    <article class="ah-card <?php echo $feat?'feat':''; ?> <?php echo $is_trend?'trending':''; ?>"
             data-d="<?php echo min($i*55,500); ?>"
             data-views="<?php echo $views; ?>"
             data-cat="<?php echo $cat; ?>">
        <div class="ah-card-img">
            <img src="<?php echo $img; ?>" alt="<?php echo $title; ?>" loading="lazy">
            <div class="ah-card-img-grad"></div>
            <?php if($cat): ?><span class="ah-card-cat"><?php echo $cat; ?></span><?php endif; ?>
            <?php if($is_trend): ?>
            <span class="ah-trend-badge"><i class="fas fa-fire"></i> Trending</span>
            <?php endif; ?>
        </div>
        <div class="ah-card-body">
            <?php if($feat): ?><span class="ah-feat-badge"><i class="fas fa-star"></i> Featured</span><?php endif; ?>
            <h3 class="ah-card-title"><a href="read-article.php?id=<?php echo $id; ?>"><?php echo $title; ?></a></h3>
            <p class="ah-card-exc"><?php echo htmlspecialchars($excerpt,'ENT_QUOTES','UTF-8'); ?></p>
            <div class="ah-card-foot">
                <div class="ah-card-meta">
                    <span><i class="far fa-calendar-alt"></i> <?php echo $date; ?></span>
                    <span><i class="far fa-clock"></i> <?php echo $rt; ?> min</span>
                    <span class="ah-views-badge" data-views="<?php echo $views; ?>">
                        <i class="fas fa-eye"></i>
                        <span class="ah-views-num"><?php echo fmtViews($views); ?></span>
                    </span>
                </div>
                <a href="read-article.php?id=<?php echo $id; ?>" class="ah-read-btn">
                    Read <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </article>
    <?php $i++; endwhile; ?>
    <?php else: ?>
    <div class="ah-empty">
        <div class="ah-empty-icon"><i class="fas fa-newspaper"></i></div>
        <h3>No articles found</h3>
        <p><?php echo $search ? 'Try different keywords.' : 'Articles will appear here once published.'; ?></p>
        <?php if($search||$filter_cat): ?>
        <a href="articles.php" class="ah-empty-link">← Clear filters</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <aside class="ah-sidebar">

        <div class="ah-sbox">
            <h4 class="ah-stitle"><i class="fas fa-folder-open"></i> Categories</h4>
            <ul class="ah-scat-list">
                <li><a href="articles.php" class="<?php echo !$filter_cat?'active':''; ?>">
                    <span>All Articles</span><span class="cnt"><?php echo $total_count; ?></span>
                </a></li>
                <?php foreach ($categories as $cat):
                    $cnt = (int)$conn->query("SELECT COUNT(*) FROM articles WHERE category='".addslashes($cat)."'")->fetch_row()[0];
                ?>
                <li><a href="articles.php?cat=<?php echo urlencode($cat); ?>" class="<?php echo $filter_cat===$cat?'active':''; ?>">
                    <span><?php echo htmlspecialchars($cat,'ENT_QUOTES','UTF-8'); ?></span>
                    <span class="cnt"><?php echo $cnt; ?></span>
                </a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Most Read -->
        <div class="ah-sbox hot">
            <h4 class="ah-stitle"><i class="fas fa-fire-alt"></i> Most Read</h4>
            <?php
            $most_read = $conn->query("SELECT id, summary AS title, COALESCE(views,0) AS views FROM articles WHERE COALESCE(views,0) > 0 ORDER BY views DESC LIMIT 5");
            if ($most_read && $most_read->num_rows > 0):
                $n = 1;
                while ($mr = $most_read->fetch_assoc()):
            ?>
            <a href="read-article.php?id=<?php echo $mr['id']; ?>" class="ah-latest-item">
                <span class="ah-latest-num"><?php echo str_pad($n++,2,'0',STR_PAD_LEFT); ?></span>
                <div class="ah-latest-info">
                    <div class="ah-latest-title"><?php echo htmlspecialchars($mr['title'],'ENT_QUOTES','UTF-8'); ?></div>
                    <div class="ah-latest-views"><i class="fas fa-eye"></i> <?php echo fmtViews($mr['views']); ?> reads</div>
                </div>
            </a>
            <?php endwhile;
            else: ?>
            <p style="color:#2d3e57;font-size:.82rem;margin:0;text-align:center;padding:10px 0;">No reads yet — be the first!</p>
            <?php endif; ?>
        </div>

        <!-- Latest -->
        <div class="ah-sbox">
            <h4 class="ah-stitle"><i class="fas fa-clock"></i> Latest</h4>
            <?php
            $latest = $conn->query("SELECT id, summary AS title, COALESCE(views,0) AS views FROM articles ORDER BY published_date DESC LIMIT 5");
            $n = 1;
            while ($l = $latest->fetch_assoc()):
            ?>
            <a href="read-article.php?id=<?php echo $l['id']; ?>" class="ah-latest-item">
                <span class="ah-latest-num"><?php echo str_pad($n++,2,'0',STR_PAD_LEFT); ?></span>
                <div class="ah-latest-info">
                    <div class="ah-latest-title"><?php echo htmlspecialchars($l['title'],'ENT_QUOTES','UTF-8'); ?></div>
                    <div class="ah-latest-views"><i class="fas fa-eye"></i> <?php echo fmtViews($l['views']); ?> reads</div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>

    </aside>

</div>
</div>

<script>
(function(){
    'use strict';

    /* ── 1. Card entrance ── */
    var cards = document.querySelectorAll('.ah-card');
    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(!e.isIntersecting) return;
            var d = parseInt(e.target.dataset.d) || 0;
            setTimeout(function(){
                e.target.classList.add('vis');
                // Animate views badge in after card appears
                var vb = e.target.querySelector('.ah-views-badge');
                if(vb) setTimeout(function(){ vb.classList.add('loaded'); }, 200);
            }, d);
            obs.unobserve(e.target);
        });
    }, {threshold:.06});
    cards.forEach(function(c){ obs.observe(c); });

    /* ── 2. Hero stat counter ── */
    var cntObs = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(!e.isIntersecting) return;
            var el = e.target;
            var target = parseInt(el.dataset.count) || 0;
            if(!target){ el.textContent='0'; cntObs.unobserve(el); return; }
            var start=null, dur=1500;
            (function tick(ts){
                if(!start) start=ts;
                var p=Math.min((ts-start)/dur,1);
                var ease=1-Math.pow(1-p,3);
                el.textContent=Math.round(ease*target);
                if(p<1) requestAnimationFrame(tick);
                else el.textContent=target;
            })(performance.now());
            cntObs.unobserve(el);
        });
    },{threshold:.5});
    document.querySelectorAll('.ah-stat strong[data-count]').forEach(function(c){ cntObs.observe(c); });

    /* ── 3. 3D tilt + glare ── */
    if(window.matchMedia('(hover: hover) and (pointer: fine)').matches){
        cards.forEach(function(card){
            var glare=document.createElement('div');
            glare.className='ah-glare';
            card.appendChild(glare);
            var raf=null, moving=false;
            card.addEventListener('mousemove',function(e){
                if(raf) return;
                raf=requestAnimationFrame(function(){
                    raf=null;
                    if(!moving){ card.style.transition='box-shadow .3s,border-color .25s'; moving=true; }
                    var r=card.getBoundingClientRect();
                    var x=(e.clientX-r.left)/r.width;
                    var y=(e.clientY-r.top)/r.height;
                    card.style.transform='perspective(900px) rotateX('+((y-.5)*-14)+'deg) rotateY('+((x-.5)*17)+'deg) translateY(-8px) scale(1.015)';
                    card.style.boxShadow='0 30px 68px rgba(0,0,0,.7),0 0 0 1px rgba(212,175,55,.28)';
                    card.style.borderColor='rgba(212,175,55,.24)';
                    glare.style.opacity='1';
                    glare.style.background='radial-gradient(circle at '+(x*100)+'% '+(y*100)+'%,rgba(255,255,255,.11) 0%,transparent 62%)';
                });
            });
            card.addEventListener('mouseleave',function(){
                moving=false;
                if(raf){cancelAnimationFrame(raf);raf=null;}
                card.style.transition='transform .5s cubic-bezier(.34,1.56,.64,1),box-shadow .4s,border-color .3s';
                card.style.transform=card.style.boxShadow=card.style.borderColor='';
                glare.style.opacity='0';
            });
        });
    } else {
        cards.forEach(function(c){ c.classList.add('no-tilt'); });
    }

    /* ── 4. Category color coding ── */
    var palette=['#d4af37','#6366f1','#22c55e','#f59e0b','#ec4899','#14b8a6','#f97316','#8b5cf6','#ef4444','#0ea5e9'];
    function hash(s){ var h=0; for(var i=0;i<s.length;i++) h=Math.imul(31,h)+s.charCodeAt(i)|0; return Math.abs(h); }
    document.querySelectorAll('.ah-card-cat').forEach(function(el){
        var col=palette[hash(el.textContent.trim())%palette.length];
        el.style.background=col; el.style.color='#020617';
    });
    document.querySelectorAll('.ah-preview-cat').forEach(function(el){
        el.style.color=palette[hash(el.textContent.trim())%palette.length];
    });

    /* ── 5. Ripple effect on pills + read buttons ── */
    document.querySelectorAll('.ah-pill, .ah-read-btn, .ah-search button').forEach(function(el){
        el.addEventListener('click',function(e){
            var r=el.getBoundingClientRect();
            var size=Math.max(r.width,r.height);
            var rip=document.createElement('span');
            rip.className='ah-ripple';
            rip.style.cssText='width:'+size+'px;height:'+size+'px;left:'+(e.clientX-r.left-size/2)+'px;top:'+(e.clientY-r.top-size/2)+'px;';
            el.appendChild(rip);
            setTimeout(function(){rip.remove();},600);
        });
    });

}());
</script>

<?php include 'includes/footer.php'; ?>
