<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit();
}

include 'includes/db.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

/* FETCH FOLDERS */
$folders = $conn->query("SELECT * FROM guide_folders ORDER BY name ASC");
$folder_list = [];
while($f = $folders->fetch_assoc()) $folder_list[] = $f;

/* FETCH GUIDES */
$guides_res = $conn->query("
SELECT g.*, f.name AS folder_name
FROM guides g
LEFT JOIN guide_folders f ON g.folder_id = f.id
ORDER BY g.id DESC
");
$guide_list = [];
while($g = $guides_res->fetch_assoc()) $guide_list[] = $g;

/* STATS */
$total_guides  = count($guide_list);
$locked_guides = array_sum(array_column($guide_list, 'is_locked'));
$free_guides   = $total_guides - $locked_guides;
?>
<style>
/* ═══════════════════════════════════════════════════════
   GUIDES PAGE — PREMIUM REDESIGN
   ═══════════════════════════════════════════════════════ */
@keyframes g-fadeUp  { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
@keyframes g-fadeIn  { from{opacity:0} to{opacity:1} }
@keyframes g-shimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes g-orb     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.1) translate(10px,-8px)} }
@keyframes g-grid    { from{background-position:0 0} to{background-position:60px 60px} }
@keyframes g-pop     { 0%{opacity:0;transform:scale(.9) translateY(18px)} 70%{transform:scale(1.02)} 100%{opacity:1;transform:scale(1) translateY(0)} }
@keyframes g-rise    { 0%{opacity:0;transform:translateY(50px)} 20%{opacity:1} 80%{opacity:.4} 100%{opacity:0;transform:translateY(-160px)} }

*{ box-sizing:border-box; }
body{ background:#020617; color:white; font-family:'Inter',Segoe UI,sans-serif; }

.dashboard-wrapper{ display:flex; min-height:100vh; background:#020617; }

/* ── Main ── */
.g-main { flex:1; padding:28px 32px 60px; overflow-x:hidden; }
.g-page { max-width:1280px; margin:0 auto; }

/* ── Gold text ── */
.g-gold {
  background:linear-gradient(135deg,#d4af37 0%,#facc15 45%,#f59e0b 70%,#d4af37 100%);
  background-size:200% auto; -webkit-background-clip:text;
  -webkit-text-fill-color:transparent; background-clip:text;
  animation:g-shimmer 4s linear infinite;
}

/* ══ HERO ═══════════════════════════════════════════════ */
.g-hero {
  position:relative; border-radius:24px; overflow:hidden;
  background:linear-gradient(145deg,#080f1e 0%,#040c18 100%);
  border:1px solid rgba(255,255,255,.08);
  padding:44px 44px 40px; margin-bottom:28px;
}
.g-hero-grid{
  position:absolute; inset:0; pointer-events:none;
  background-image:linear-gradient(rgba(59,130,246,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(59,130,246,.025) 1px,transparent 1px);
  background-size:60px 60px; animation:g-grid 18s linear infinite;
}
.g-hero-orb-1{ position:absolute;width:460px;height:460px;border-radius:50%;filter:blur(100px);pointer-events:none;background:radial-gradient(circle,rgba(59,130,246,.09) 0%,transparent 70%);top:-160px;right:-80px;animation:g-orb 10s ease-in-out infinite; }
.g-hero-orb-2{ position:absolute;width:300px;height:300px;border-radius:50%;filter:blur(80px);pointer-events:none;background:radial-gradient(circle,rgba(212,175,55,.06) 0%,transparent 70%);bottom:-80px;left:-40px;animation:g-orb 13s ease-in-out infinite reverse; }
.g-hero-particles{ position:absolute;inset:0;pointer-events:none;overflow:hidden; }
.g-hero-particle{ position:absolute;width:3px;height:3px;border-radius:50%;background:rgba(59,130,246,.4);animation:g-rise var(--d,9s) var(--dl,0s) ease-in infinite; }

.g-hero-inner{ position:relative;z-index:2;display:flex;align-items:flex-start;justify-content:space-between;gap:32px;flex-wrap:wrap; }
.g-hero-badge{
  display:inline-flex;align-items:center;gap:7px;
  background:rgba(59,130,246,.09);border:1px solid rgba(59,130,246,.25);
  color:#60a5fa;padding:5px 16px;border-radius:99px;
  font-size:.65rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;
  margin-bottom:14px;animation:g-fadeUp .5s .1s ease both;
}
.g-hero-badge-dot{ width:6px;height:6px;border-radius:50%;background:#3b82f6;box-shadow:0 0 6px rgba(59,130,246,.9); }
.g-hero h1{ font-size:clamp(1.8rem,3vw,2.5rem);font-weight:900;color:#fff;margin:0 0 10px;line-height:1.2;animation:g-fadeUp .55s .15s ease both; }
.g-hero-sub{ color:#475569;font-size:.9rem;max-width:520px;animation:g-fadeUp .55s .25s ease both;line-height:1.6; }

/* Hero stats */
.g-stats { display:flex;gap:14px;flex-wrap:wrap;margin-top:24px; }
.g-stat{
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
  border-radius:14px;padding:14px 20px;text-align:center;position:relative;
  animation:g-fadeUp .55s ease both;min-width:100px;
}
.g-stat::before{ content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);width:40%;height:2px;background:var(--sc,linear-gradient(90deg,transparent,#3b82f6,transparent)); }
.g-stat strong{ display:block;font-size:1.5rem;font-weight:900;color:var(--sv,#3b82f6);line-height:1; }
.g-stat span{ font-size:.6rem;color:#334155;letter-spacing:1.5px;text-transform:uppercase;margin-top:5px;display:block; }

/* ── Search ── */
.g-search-bar{
  position:relative;margin-bottom:22px;
}
.g-search-bar i{ position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#334155;font-size:.9rem; }
#guideSearch{
  width:100%;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);
  border-radius:14px;padding:13px 16px 13px 44px;color:#e2e8f0;
  font-size:.9rem;font-family:inherit;outline:none;
  transition:border-color .2s,box-shadow .2s,background .2s;
}
#guideSearch:focus{
  border-color:rgba(59,130,246,.4);background:rgba(59,130,246,.03);
  box-shadow:0 0 0 3px rgba(59,130,246,.1);
}
#guideSearch::placeholder{ color:#1e293b; }
.g-search-count{
  position:absolute;right:14px;top:50%;transform:translateY(-50%);
  font-size:.7rem;font-weight:700;color:#334155;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.07);
  padding:3px 10px;border-radius:8px;
}

/* ── Folder filter tabs ── */
.g-folder-bar{ display:flex;flex-wrap:wrap;gap:8px;margin-bottom:28px; }
.g-folder-btn{
  padding:7px 16px;border-radius:99px;font-size:.78rem;font-weight:700;letter-spacing:.3px;
  border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);color:#64748b;
  cursor:pointer;transition:background .2s,border-color .2s,color .2s;user-select:none;
}
.g-folder-btn:hover  { border-color:rgba(59,130,246,.3);color:#cbd5e1; }
.g-folder-btn.active { background:rgba(59,130,246,.12);border-color:rgba(59,130,246,.4);color:#60a5fa; }
.g-folder-btn .g-folder-count{ font-size:.65rem;opacity:.7;margin-left:5px; }

/* ── Section heading ── */
.g-sec-head{ margin-bottom:22px; }
.g-sec-head h2{ font-size:1.2rem;font-weight:900;color:#fff;margin:0 0 4px; }
.g-sec-head p{ color:#475569;font-size:.85rem;margin:0; }
.g-sec-line{ width:40px;height:3px;border-radius:3px;background:linear-gradient(90deg,#3b82f6,#60a5fa);margin-top:10px; }

/* ── Grid ── */
.g-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
  gap:18px;margin-bottom:50px;
}

/* ── Guide Card ── */
.g-card{
  position:relative;
  background:linear-gradient(160deg,#0d1829 0%,#080f1e 100%);
  border:1px solid rgba(255,255,255,.07);border-radius:20px;
  display:flex;flex-direction:column;overflow:hidden;
  transition:transform .35s cubic-bezier(.34,1.56,.64,1),border-color .3s,box-shadow .3s;
  opacity:0;transform:translateY(20px);
}
.g-card.visible{ animation:g-pop .5s ease forwards; }
.g-card:hover{
  transform:translateY(-6px);
  border-color:rgba(59,130,246,.25);
  box-shadow:0 24px 60px rgba(0,0,0,.55),0 0 0 1px rgba(59,130,246,.06);
}
.g-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,transparent,#3b82f6,#60a5fa,transparent);
  opacity:0;transition:opacity .3s;
}
.g-card:hover::before{ opacity:1; }

/* Locked overlay */
.g-card-locked-overlay{
  position:absolute;inset:0;z-index:5;
  background:rgba(2,6,23,.6);backdrop-filter:blur(2px);
  display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;
  border-radius:20px;opacity:0;transition:opacity .3s;pointer-events:none;
}
.g-card.locked:hover .g-card-locked-overlay{ opacity:1; }
.g-card-locked-overlay i{ font-size:1.8rem;color:rgba(239,68,68,.7); }
.g-card-locked-overlay span{ font-size:.75rem;font-weight:700;color:#64748b;letter-spacing:1px;text-transform:uppercase; }

/* Card body */
.g-card-body{ padding:22px 22px 0; flex:1; }
.g-card-folder{
  font-size:.62rem;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;
  color:#3b82f6;margin-bottom:10px;display:flex;align-items:center;gap:6px;
}
.g-card-folder i{ font-size:.7rem;opacity:.8; }
.g-card-title{ font-size:.98rem;font-weight:800;color:#f1f5f9;margin:0 0 10px;line-height:1.4; }
.g-card-desc{ font-size:.82rem;color:#475569;line-height:1.6;margin:0; }

/* Card footer */
.g-card-footer{ padding:16px 22px 20px;margin-top:auto; }
.g-card-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 18px;border-radius:10px;font-size:.8rem;font-weight:700;
  text-decoration:none;transition:transform .2s,box-shadow .2s,background .2s;
  font-family:inherit;border:none;cursor:pointer;
}
.g-card-btn:hover{ transform:translateY(-2px); }
.g-btn-read  { background:linear-gradient(135deg,#3b82f6,#60a5fa);color:#fff;box-shadow:0 3px 12px rgba(59,130,246,.3); }
.g-btn-read:hover{ box-shadow:0 6px 20px rgba(59,130,246,.5); }
.g-btn-locked{ background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);color:#334155;cursor:not-allowed; }

/* Lock badge */
.g-lock-badge{
  position:absolute;top:14px;right:14px;z-index:2;
  font-size:.6rem;font-weight:800;letter-spacing:.5px;text-transform:uppercase;
  padding:4px 10px;border-radius:8px;
  background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;
  display:flex;align-items:center;gap:5px;
}
.g-new-badge{
  position:absolute;top:14px;right:14px;z-index:2;
  font-size:.6rem;font-weight:800;letter-spacing:.5px;text-transform:uppercase;
  padding:4px 10px;border-radius:8px;
  background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#34d399;
}

/* Empty state */
.g-empty-state{
  grid-column:1/-1;text-align:center;padding:60px 20px;
}
.g-empty-state i{ font-size:2.5rem;color:rgba(59,130,246,.12);display:block;margin-bottom:16px; }
.g-empty-state h3{ color:#1e293b;margin:0 0 8px;font-size:1rem; }
.g-empty-state p{ color:#1e293b;font-size:.85rem; }

/* ── Responsive ── */
@media(max-width:900px){ .g-stats{ gap:10px; } }
@media(max-width:680px){
  .g-main{ padding:18px 16px 40px; }
  .g-hero{ padding:28px 20px 24px; }
  .g-grid{ grid-template-columns:1fr; }
  .g-hero-orb-1,.g-hero-orb-2{ display:none; }
}
</style>

<div class="dashboard-wrapper">

<?php include 'includes/sidebar.php'; ?>

<div class="g-main">
<div class="g-page">

<!-- ══ HERO ══════════════════════════════════════════════ -->
<div class="g-hero">
  <div class="g-hero-grid"></div>
  <div class="g-hero-orb-1"></div>
  <div class="g-hero-orb-2"></div>
  <div class="g-hero-particles">
    <div class="g-hero-particle" style="left:12%;--d:9s;--dl:0s"></div>
    <div class="g-hero-particle" style="left:30%;--d:12s;--dl:-3s"></div>
    <div class="g-hero-particle" style="left:55%;--d:8s;--dl:-6s"></div>
    <div class="g-hero-particle" style="left:78%;--d:11s;--dl:-2s"></div>
    <div class="g-hero-particle" style="left:92%;--d:10s;--dl:-5s"></div>
  </div>
  <div class="g-hero-inner">
    <div>
      <div class="g-hero-badge"><span class="g-hero-badge-dot"></span> Brokerage · Learning Hub</div>
      <h1>Investment <span class="g-gold">Guides</span></h1>
      <p class="g-hero-sub">Professional step-by-step guides to help you navigate global brokerage account setup and investing fundamentals.</p>
      <div class="g-stats">
        <div class="g-stat" style="--sc:linear-gradient(90deg,transparent,#3b82f6,transparent);--sv:#60a5fa;animation-delay:.1s">
          <strong><?php echo $total_guides; ?></strong><span>Total Guides</span>
        </div>
        <div class="g-stat" style="--sc:linear-gradient(90deg,transparent,#10b981,transparent);--sv:#10b981;animation-delay:.18s">
          <strong><?php echo $free_guides; ?></strong><span>Available</span>
        </div>
        <div class="g-stat" style="--sc:linear-gradient(90deg,transparent,#ef4444,transparent);--sv:#f87171;animation-delay:.26s">
          <strong><?php echo $locked_guides; ?></strong><span>Premium Only</span>
        </div>
        <div class="g-stat" style="--sc:linear-gradient(90deg,transparent,#d4af37,transparent);--sv:#d4af37;animation-delay:.34s">
          <strong><?php echo count($folder_list); ?></strong><span>Categories</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ SEARCH ════════════════════════════════════════════ -->
<div class="g-search-bar">
  <i class="fas fa-magnifying-glass"></i>
  <input type="text" id="guideSearch" placeholder="Search guides by title or category...">
  <span class="g-search-count" id="searchCount"><?php echo $total_guides; ?> guides</span>
</div>

<!-- ══ FOLDER FILTERS ════════════════════════════════════ -->
<div class="g-folder-bar">
  <button class="g-folder-btn active" onclick="filterFolder('all',this)" data-folder="all">
    All <span class="g-folder-count">(<?php echo $total_guides; ?>)</span>
  </button>
  <?php
  $folder_counts = [];
  foreach($guide_list as $g){
    $fn = $g['folder_name'] ?: 'General';
    $folder_counts[$fn] = ($folder_counts[$fn]??0)+1;
  }
  foreach($folder_list as $f): ?>
  <button class="g-folder-btn" onclick="filterFolder('<?php echo htmlspecialchars($f['name']); ?>',this)" data-folder="<?php echo htmlspecialchars($f['name']); ?>">
    <?php echo htmlspecialchars($f['name']); ?>
    <span class="g-folder-count">(<?php echo $folder_counts[$f['name']]??0; ?>)</span>
  </button>
  <?php endforeach; ?>
</div>

<!-- ══ GUIDES GRID ═══════════════════════════════════════ -->
<div class="g-sec-head">
  <h2>Browse Guides</h2>
  <p>Click any guide to start learning. Locked guides require a premium membership.</p>
  <div class="g-sec-line"></div>
</div>

<div class="g-grid" id="guidesGrid">

<?php foreach($guide_list as $i => $g):
  $folder = $g['folder_name'] ?: 'General';
  $isLocked = (bool)$g['is_locked'];
  $isNew = $i < 3;
?>

<div class="g-card <?php echo $isLocked?'locked':''; ?>"
  data-folder="<?php echo htmlspecialchars($folder); ?>"
  data-title="<?php echo strtolower(htmlspecialchars($g['title'])); ?>"
  data-delay="<?php echo $i * 55; ?>">

  <?php if($isLocked): ?>
  <div class="g-lock-badge"><i class="fas fa-lock"></i> Premium</div>
  <div class="g-card-locked-overlay">
    <i class="fas fa-lock"></i>
    <span>Premium Required</span>
  </div>
  <?php elseif($isNew): ?>
  <div class="g-new-badge">New</div>
  <?php endif; ?>

  <div class="g-card-body">
    <div class="g-card-folder">
      <i class="fas fa-folder-open"></i>
      <?php echo htmlspecialchars($folder); ?>
    </div>
    <h3 class="g-card-title"><?php echo htmlspecialchars($g['title']); ?></h3>
    <p class="g-card-desc"><?php echo htmlspecialchars($g['description']); ?></p>
  </div>

  <div class="g-card-footer">
    <?php if($isLocked): ?>
    <span class="g-card-btn g-btn-locked"><i class="fas fa-lock"></i> Locked</span>
    <?php else: ?>
    <a class="g-card-btn g-btn-read" href="<?php echo htmlspecialchars($g['file_path']); ?>" target="_blank">
      <i class="fas fa-book-open"></i> Read Guide
    </a>
    <?php endif; ?>
  </div>

</div>

<?php endforeach; ?>

<?php if(empty($guide_list)): ?>
<div class="g-empty-state">
  <i class="fas fa-book"></i>
  <h3>No guides yet</h3>
  <p>Guides will appear here once they are published.</p>
</div>
<?php endif; ?>

</div>

</div><!-- /.g-page -->
</div><!-- /.g-main -->
</div><!-- /.dashboard-wrapper -->

<script>
/* ── Filter by folder ── */
function filterFolder(folder, btn){
  document.querySelectorAll('.g-folder-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  let visible = 0;
  document.querySelectorAll('.g-card').forEach(card=>{
    const match = folder==='all' || card.dataset.folder===folder;
    card.style.display = match ? '' : 'none';
    if(match) visible++;
  });
  document.getElementById('searchCount').textContent = visible + ' guide' + (visible!==1?'s':'');
}

/* ── Search ── */
document.getElementById('guideSearch').addEventListener('input', function(){
  const q = this.value.toLowerCase().trim();
  let visible = 0;
  const activeFolder = (document.querySelector('.g-folder-btn.active')||{}).dataset?.folder || 'all';
  document.querySelectorAll('.g-card').forEach(card=>{
    const title  = card.dataset.title || '';
    const folder = card.dataset.folder || '';
    const matchQ = !q || title.includes(q) || folder.toLowerCase().includes(q);
    const matchF = activeFolder==='all' || card.dataset.folder===activeFolder;
    const show = matchQ && matchF;
    card.style.display = show ? '' : 'none';
    if(show) visible++;
  });
  document.getElementById('searchCount').textContent = visible + ' guide' + (visible!==1?'s':'');
});

/* ── Scroll reveal ── */
(function(){
  const obs = new IntersectionObserver(entries=>{
    entries.forEach(e=>{
      if(!e.isIntersecting) return;
      const d = parseInt(e.target.dataset.delay)||0;
      setTimeout(()=>e.target.classList.add('visible'), d);
      obs.unobserve(e.target);
    });
  },{threshold:0.08});
  document.querySelectorAll('.g-card').forEach(el=>obs.observe(el));
}());
</script>

<?php include 'includes/footer.php'; ?>
