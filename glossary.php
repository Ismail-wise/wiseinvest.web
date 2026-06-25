<?php
include 'includes/db.php';
include 'includes/header.php';

// Premium check
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'premium') {
    ?>
    <style>
    .gl-gate{min-height:70vh;background:#020617;display:flex;align-items:center;justify-content:center;text-align:center;padding:40px 20px;}
    .gl-gate-card{background:#0a1628;border:1px solid rgba(212,175,55,0.2);border-radius:24px;padding:60px 40px;max-width:440px;}
    .gl-gate-icon{width:72px;height:72px;background:rgba(212,175,55,0.1);border:1px solid rgba(212,175,55,0.3);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:#d4af37;margin-bottom:22px;}
    .gl-gate h2{color:#fff;font-size:1.6rem;margin:0 0 10px;}
    .gl-gate p{color:#64748b;margin:0 0 28px;line-height:1.7;}
    .gl-gate-btn{display:inline-block;padding:13px 32px;background:linear-gradient(135deg,#d4af37,#facc15);color:#020617;font-weight:800;border-radius:12px;text-decoration:none;box-shadow:0 6px 24px rgba(212,175,55,0.3);}
    </style>
    <div class="gl-gate">
        <div class="gl-gate-card">
            <div class="gl-gate-icon"><i class="fas fa-book"></i></div>
            <h2>Premium Access Only</h2>
            <p>Upgrade to Wise Invest Premium to unlock the full Financial Glossary with definitions, equations, and examples.</p>
            <a href="services.php" class="gl-gate-btn"><i class="fas fa-crown" style="margin-right:8px;"></i> Upgrade to Premium</a>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
    exit();
}

// Search
$search_result = null;
$search_term = "";
if (isset($_GET['term'])) {
    $search_term = $conn->real_escape_string($_GET['term']);
    $sql = "SELECT * FROM glossary WHERE term LIKE '%$search_term%' LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $search_result = $result->fetch_assoc();
    }
}

// All terms
$all_terms_res = $conn->query("SELECT term FROM glossary ORDER BY term ASC");
$term_count = $all_terms_res ? $all_terms_res->num_rows : 0;
?>

<style>
/* ════════════════════════════════════════
   GLOSSARY PAGE — PREMIUM DARK
   ════════════════════════════════════════ */
@keyframes glFadeUp  { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }
@keyframes glShimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes glOrb     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.12) translate(12px,-10px)} }

/* ── Hero ── */
.gl-hero {
    position: relative; background: #020617;
    padding: 90px 20px 70px; text-align: center; overflow: hidden;
}
.gl-grid {
    position: absolute; inset: 0; pointer-events: none;
    background-image:
        linear-gradient(rgba(212,175,55,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212,175,55,0.04) 1px, transparent 1px);
    background-size: 60px 60px;
}
.gl-orb {
    position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none;
}
.gl-orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(212,175,55,0.12) 0%, transparent 70%);
    top: -150px; right: -100px; animation: glOrb 9s ease-in-out infinite;
}
.gl-orb-2 {
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(99,102,241,0.07) 0%, transparent 70%);
    bottom: -80px; left: -80px; animation: glOrb 11s ease-in-out infinite reverse;
}
.gl-hero-inner { position: relative; z-index: 2; }
.gl-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.3);
    color: #d4af37; padding: 5px 18px; border-radius: 99px;
    font-size: 0.72rem; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;
    margin-bottom: 20px; animation: glFadeUp 0.5s 0.1s ease both;
}
.gl-title {
    font-size: clamp(2.2rem, 5vw, 3.5rem);
    font-weight: 900; margin: 0 0 14px; line-height: 1.1;
    animation: glFadeUp 0.6s 0.2s ease both;
}
.gl-title-gold {
    background: linear-gradient(135deg, #d4af37 0%, #facc15 40%, #f59e0b 70%, #d4af37 100%);
    background-size: 200% auto;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    animation: glShimmer 4s linear infinite;
}
.gl-sub {
    color: #64748b; max-width: 480px; margin: 0 auto 36px;
    animation: glFadeUp 0.6s 0.3s ease both;
}

/* Search */
.gl-search-wrap {
    max-width: 580px; margin: 0 auto;
    animation: glFadeUp 0.6s 0.4s ease both;
}
.gl-search-form {
    display: flex; gap: 10px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px; padding: 6px 6px 6px 20px;
    transition: border-color 0.25s, box-shadow 0.25s;
}
.gl-search-form:focus-within {
    border-color: rgba(212,175,55,0.4);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.08);
}
.gl-search-input {
    flex: 1; background: none; border: none; outline: none;
    color: #e2e8f0; font-size: 1rem;
}
.gl-search-input::placeholder { color: #334155; }
.gl-search-btn {
    padding: 10px 22px; border-radius: 12px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    color: #020617; font-weight: 800; font-size: 0.88rem;
    border: none; cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
}
.gl-search-btn:hover { transform: scale(1.04); }

/* Term count badge */
.gl-count {
    margin-top: 20px; display: inline-block;
    font-size: 0.78rem; color: #475569;
    animation: glFadeUp 0.6s 0.5s ease both;
}
.gl-count strong { color: #d4af37; }

/* ── Result card ── */
.gl-result-wrap {
    max-width: 800px; margin: -20px auto 0; padding: 0 24px; position: relative; z-index: 5;
}
.gl-result-card {
    background: #0a1628;
    border: 1px solid rgba(212,175,55,0.25);
    border-radius: 20px; padding: 40px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(212,175,55,0.06);
    animation: glFadeUp 0.5s ease both;
}
.gl-result-card::before {
    content: ''; display: block;
    height: 3px; width: 60px; border-radius: 3px;
    background: linear-gradient(90deg, #d4af37, #facc15);
    margin-bottom: 24px;
}
.gl-result-term {
    font-size: 2rem; font-weight: 900; color: #fff; margin: 0 0 16px;
}
.gl-result-def {
    font-size: 1.02rem; line-height: 1.8; color: #94a3b8; margin: 0 0 24px;
}
.gl-result-eq {
    background: rgba(255,255,255,0.03);
    border: 1px dashed rgba(212,175,55,0.2);
    border-radius: 12px; padding: 20px; text-align: center;
}
.gl-result-eq small { display: block; color: #475569; font-size: 0.7rem; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 10px; }
.gl-result-eq code {
    font-size: 1.3rem; font-family: 'Courier New', monospace;
    font-weight: 700; color: #d4af37;
}

/* ── Not found ── */
.gl-not-found {
    text-align: center; padding: 60px 20px; color: #475569;
}
.gl-not-found i { font-size: 3rem; color: rgba(212,175,55,0.15); display: block; margin-bottom: 16px; }
.gl-not-found h3 { color: #94a3b8; margin: 0 0 8px; }

/* ── Terms browser ── */
.gl-browser { background: #020617; padding: 60px 24px 80px; }
.gl-browser-inner { max-width: 900px; margin: 0 auto; }
.gl-browser-head {
    text-align: center; margin-bottom: 32px;
}
.gl-browser-head h3 {
    font-size: 1rem; font-weight: 700; color: #475569;
    letter-spacing: 1.5px; text-transform: uppercase; margin: 0;
}

/* Alphabet filter */
.gl-alpha { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-bottom: 28px; }
.gl-alpha-btn {
    width: 34px; height: 34px; border-radius: 9px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
    color: #64748b; font-size: 0.82rem; font-weight: 700;
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;
}
.gl-alpha-btn:hover, .gl-alpha-btn.active {
    background: rgba(212,175,55,0.12); border-color: rgba(212,175,55,0.3); color: #d4af37;
}

/* Term pills */
.gl-pills { text-align: center; }
.gl-pill {
    display: inline-block;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 8px 18px; border-radius: 99px; margin: 5px;
    color: #64748b; text-decoration: none; font-size: 0.88rem;
    transition: all 0.22s;
}
.gl-pill:hover {
    background: rgba(212,175,55,0.1);
    border-color: rgba(212,175,55,0.3); color: #d4af37;
    transform: translateY(-2px);
}
.gl-pill.active {
    background: linear-gradient(135deg, #d4af37, #facc15);
    border-color: transparent; color: #020617; font-weight: 700;
}
</style>

<!-- HERO -->
<section class="gl-hero">
    <div class="gl-grid"></div>
    <div class="gl-orb gl-orb-1"></div>
    <div class="gl-orb gl-orb-2"></div>

    <div class="gl-hero-inner">
        <div class="gl-badge"><i class="fas fa-book-open"></i> Premium Feature</div>
        <h1 class="gl-title">Financial <span class="gl-title-gold">Glossary</span></h1>
        <p class="gl-sub">Master the language of money. Search definitions, formulas, and key investment terms.</p>

        <div class="gl-search-wrap">
            <form action="glossary.php" method="GET" class="gl-search-form">
                <i class="fas fa-search" style="color:#475569;align-self:center;"></i>
                <input type="text" name="term" value="<?php echo htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8'); ?>"
                       class="gl-search-input" placeholder="Search a term, e.g. CAGR, P/E Ratio...">
                <button type="submit" class="gl-search-btn"><i class="fas fa-arrow-right"></i></button>
            </form>
        </div>

        <div class="gl-count"><strong><?php echo $term_count; ?></strong> terms available</div>
    </div>
</section>

<!-- RESULT -->
<?php if ($search_result): ?>
<div class="gl-result-wrap">
    <div class="gl-result-card">
        <h2 class="gl-result-term"><?php echo htmlspecialchars($search_result['term'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="gl-result-def"><?php echo nl2br(htmlspecialchars($search_result['definition'], ENT_QUOTES, 'UTF-8')); ?></p>
        <?php if (!empty($search_result['equation'])): ?>
        <div class="gl-result-eq">
            <small>Equation</small>
            <code><?php echo htmlspecialchars($search_result['equation'], ENT_QUOTES, 'UTF-8'); ?></code>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php elseif ($search_term !== ""): ?>
<div class="gl-not-found">
    <i class="fas fa-search"></i>
    <h3>Term not found</h3>
    <p>Try a different keyword or browse the full list below.</p>
</div>
<?php endif; ?>

<!-- BROWSER -->
<div class="gl-browser">
<div class="gl-browser-inner">

    <div class="gl-browser-head">
        <h3>Browse All Terms</h3>
    </div>

    <div class="gl-pills" id="termPills">
        <?php if ($all_terms_res && $all_terms_res->num_rows > 0):
            $all_terms_res->data_seek(0);
            while ($t = $all_terms_res->fetch_assoc()): ?>
            <a href="glossary.php?term=<?php echo urlencode($t['term']); ?>"
               class="gl-pill <?php echo ($search_term === $t['term']) ? 'active' : ''; ?>"
               data-letter="<?php echo strtolower(substr($t['term'], 0, 1)); ?>">
                <?php echo htmlspecialchars($t['term'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endwhile; ?>
        <?php else: ?>
        <p style="color:#475569;">No terms added yet.</p>
        <?php endif; ?>
    </div>

</div>
</div>

<script>
/* Quick alpha filter (client-side) */
(function () {
    var pills = document.querySelectorAll('.gl-pill[data-letter]');
    if (!pills.length) return;

    // Build alphabet set
    var letters = new Set();
    pills.forEach(function(p) { letters.add(p.dataset.letter); });

    var alphaDiv = document.createElement('div');
    alphaDiv.className = 'gl-alpha';
    alphaDiv.innerHTML = '<button class="gl-alpha-btn active" data-l="all">All</button>';
    letters.forEach(function(l) {
        alphaDiv.innerHTML += '<button class="gl-alpha-btn" data-l="' + l + '">' + l.toUpperCase() + '</button>';
    });

    document.getElementById('termPills').before(alphaDiv);

    alphaDiv.addEventListener('click', function(e) {
        var btn = e.target.closest('.gl-alpha-btn');
        if (!btn) return;
        var l = btn.dataset.l;
        alphaDiv.querySelectorAll('.gl-alpha-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        pills.forEach(function(p) {
            p.style.display = (l === 'all' || p.dataset.letter === l) ? '' : 'none';
        });
    });
}());
</script>

<?php include 'includes/footer.php'; ?>
