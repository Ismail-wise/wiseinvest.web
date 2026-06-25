<?php $current = basename($_SERVER['PHP_SELF']); ?>

<style>
/* ═══════════════════════════════════════════════════
   SIDEBAR — FUTURISTIC REDESIGN
   ═══════════════════════════════════════════════════ */

.sidebar {
  width: 230px;
  flex-shrink: 0;
  background: rgba(10,14,28,0.92);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 20px;
  padding: 0 0 24px;
  height: fit-content;
  box-shadow: 0 20px 55px rgba(0,0,0,.55), inset 0 1px 0 rgba(255,255,255,.05);
  animation: sbFadeIn .7s ease;
  position: sticky;
  top: 24px;
}
@keyframes sbFadeIn { from{opacity:0;transform:translateX(-12px)} to{opacity:1;transform:none} }

/* Brand bar */
.sb-brand {
  padding: 22px 22px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  margin-bottom: 10px;
}
.sb-logo {
  font-size: .68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 2px;
  color: rgba(244,247,255,.4); margin-bottom: 6px;
}
.sb-title {
  font-size: 1.1rem; font-weight: 900; color: #f1f5f9;
  background: linear-gradient(135deg,#f1f5f9,#38bdf8);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  display: flex; align-items: center; gap: 9px;
}
.sb-title-icon {
  width: 30px; height: 30px;
  background: linear-gradient(135deg,#38bdf8,#a78bfa);
  border-radius: 8px; display: flex; align-items: center; justify-content: center;
  font-size: .85rem; color: #020617; -webkit-text-fill-color: #020617;
  box-shadow: 0 0 12px rgba(56,189,248,.3);
  flex-shrink: 0;
}
.sb-version {
  font-size: .62rem; color: rgba(244,247,255,.28);
  letter-spacing: 1px; margin-top: 4px;
}

/* Nav area */
.sb-nav { padding: 0 12px; }

/* Links */
.sidebar a {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; margin-bottom: 3px;
  color: rgba(244,247,255,.55);
  text-decoration: none; border-radius: 10px;
  font-size: .85rem; font-weight: 500;
  transition: all .22s ease;
  position: relative;
}
.sidebar a i.sb-ico {
  width: 20px; text-align: center;
  font-size: .9rem; flex-shrink: 0;
  transition: all .22s ease;
}
.sidebar a:hover {
  background: rgba(56,189,248,.08);
  color: #e2e8f0;
  transform: translateX(4px);
}
.sidebar a:hover i.sb-ico { color: #38bdf8; filter: drop-shadow(0 0 5px #38bdf8); }
.sidebar a.active {
  background: rgba(56,189,248,.12);
  color: #f1f5f9; font-weight: 700;
  box-shadow: inset 3px 0 0 #38bdf8;
}
.sidebar a.active i.sb-ico { color: #38bdf8; }

/* Section label */
.sb-section {
  font-size: .6rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 1.5px; color: rgba(244,247,255,.22);
  padding: 14px 12px 6px;
}

/* Submenu */
.submenu { margin-bottom: 3px; }
.submenu-title {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; border-radius: 10px;
  color: rgba(244,247,255,.55); font-size: .85rem; font-weight: 500;
  cursor: pointer; transition: all .22s;
  user-select: none;
}
.submenu-title i.sb-ico { width:20px;text-align:center;font-size:.9rem;flex-shrink:0;transition:.22s; }
.submenu-title .sb-caret {
  margin-left: auto; font-size: .65rem; opacity: .5;
  transition: transform .3s ease;
}
.submenu-title:hover {
  background: rgba(56,189,248,.08); color: #e2e8f0;
  transform: translateX(4px);
}
.submenu-title:hover i.sb-ico { color:#38bdf8; }
.submenu.open .sb-caret { transform: rotate(180deg); }

.submenu-items {
  padding-left: 14px; max-height: 0;
  overflow: hidden; transition: max-height .35s ease;
}
.submenu.open .submenu-items { max-height: 400px; }
.submenu-items a {
  font-size: .8rem; padding: 8px 12px; color: rgba(244,247,255,.4);
}
.submenu-items a:hover { color: #e2e8f0; background: rgba(56,189,248,.07); }
.submenu-items a.active { color: #38bdf8; background: rgba(56,189,248,.1); }

/* Divider */
.sb-divider {
  height: 1px; background: rgba(255,255,255,.06);
  margin: 10px 12px;
}

/* Logout */
.sidebar a.sb-logout {
  color: rgba(248,113,113,.7);
  margin-top: 4px;
}
.sidebar a.sb-logout:hover {
  background: rgba(239,68,68,.1); color: #f87171;
}
.sidebar a.sb-logout i.sb-ico { color: #f87171 !important; filter:none !important; }

/* ═══════════════════════════════════════════════════
   MOBILE DRAWER (≤1024px) — left slide-out
   Reuses .sidebar as the drawer body (display:block/none, no transforms)
   ═══════════════════════════════════════════════════ */
#dbham, #dbback, #dbclose { display:none; }

@media (max-width:1024px){
  /* Hamburger — fixed top-left, gold/dark theme */
  #dbham {
    display:flex; flex-direction:column; justify-content:space-around;
    position:fixed; top:130px; left:14px;
    width:40px; height:40px; padding:9px;
    background:linear-gradient(135deg, rgba(212,175,55,.15), rgba(10,14,28,.95));
    backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
    border:1px solid rgba(212,175,55,.4); border-radius:11px;
    cursor:pointer; z-index:10001; box-shadow:0 4px 16px rgba(212,175,55,.25);
  }
  #dbham span { display:block; width:100%; height:3px; border-radius:2px;
    background:linear-gradient(135deg, #d4af37, #facc15); }

  /* Overlay */
  #dbback { position:fixed; inset:0; background:rgba(2,6,23,.7); z-index:10002; }
  #dbback.show { display:block; }

  /* Promote .sidebar to a fixed left drawer (hidden by default) */
  .sidebar {
    display:none;
    position:fixed; top:0; left:0;
    width:270px; max-width:85vw; height:100vh;
    margin:0; border-radius:0;
    border:none; border-right:2px solid rgba(212,175,55,.4);
    z-index:10003; overflow-y:auto; animation:none;
  }
  .sidebar.dbopen { display:block; }

  /* Close X inside the drawer */
  #dbclose {
    display:block;
    position:absolute; top:14px; right:14px;
    width:38px; height:38px;
    background:transparent; color:#d4af37;
    border:2px solid rgba(212,175,55,.5); border-radius:50%;
    font-size:20px; line-height:1; cursor:pointer; z-index:1;
  }
}
</style>

<!-- MOBILE DRAWER controls (≤1024px) -->
<button id="dbham" aria-label="Open menu"><span></span><span></span><span></span></button>
<div id="dbback"></div>

<div class="sidebar">

  <!-- Close X (mobile drawer only) -->
  <button id="dbclose" aria-label="Close menu">&times;</button>

  <!-- BRAND -->
  <div class="sb-brand">
    <div class="sb-logo">Platform</div>
    <div class="sb-title">
      <span class="sb-title-icon"><i class="fas fa-chart-line"></i></span>
      WiseInvest
    </div>
    <div class="sb-version">v2.0 &nbsp;·&nbsp; Member Portal</div>
  </div>

  <!-- NAV -->
  <nav class="sb-nav">

    <a href="dashboard.php" class="<?php echo $current=='dashboard.php'?'active':''; ?>">
      <i class="fas fa-house sb-ico"></i> Dashboard
    </a>

    <div class="sb-section">Finance</div>

    <a href="budgetplanner.php" class="<?php echo $current=='budgetplanner.php'?'active':''; ?>">
      <i class="fas fa-wallet sb-ico"></i> Budget Planner
    </a>

    <a href="emergency-fund.php" class="<?php echo $current=='emergency-fund.php'?'active':''; ?>">
      <i class="fas fa-shield-alt sb-ico"></i> Emergency Fund
    </a>

    <!-- CALCULATORS -->
    <div class="submenu" id="calcMenu">
      <div class="submenu-title">
        <i class="fas fa-calculator sb-ico"></i> Calculators
        <i class="fas fa-chevron-down sb-caret"></i>
      </div>
      <div class="submenu-items">
        <a href="ffj.php"     class="<?php echo $current=='ffj.php'?'active':''; ?>"><i class="fas fa-flag" style="margin-right:6px;font-size:.75rem"></i>F.F.J Calculator</a>
        <a href="vacancy.php" class="<?php echo $current=='vacancy.php'?'active':''; ?>"><i class="fas fa-building" style="margin-right:6px;font-size:.75rem"></i>Vacancy Calculator</a>
        <a href="calculators.php" class="<?php echo $current=='calculators.php'?'active':''; ?>"><i class="fas fa-infinity" style="margin-right:6px;font-size:.75rem"></i>All Calculators</a>
      </div>
    </div>

    <div class="sb-section">Investing</div>

    <!-- PORTFOLIOS -->
    <div class="submenu" id="portfolioMenu">
      <div class="submenu-title">
        <i class="fas fa-briefcase sb-ico"></i> Portfolios
        <i class="fas fa-chevron-down sb-caret"></i>
      </div>
      <div class="submenu-items">
        <a href="actual-portfolio.php"  class="<?php echo $current=='actual-portfolio.php'?'active':''; ?>"><i class="fas fa-chart-pie" style="margin-right:6px;font-size:.75rem"></i>Actual Portfolio</a>
        <a href="target-portfolio.php"  class="<?php echo $current=='target-portfolio.php'?'active':''; ?>"><i class="fas fa-bullseye" style="margin-right:6px;font-size:.75rem"></i>Target Portfolio</a>
        <a href="backtester.php"        class="<?php echo $current=='backtester.php'?'active':''; ?>"><i class="fas fa-flask" style="margin-right:6px;font-size:.75rem"></i>Portfolio Backtester</a>
      </div>
    </div>

    <!-- STOCKS -->
    <div class="submenu" id="stocksMenu">
      <div class="submenu-title">
        <i class="fas fa-chart-bar sb-ico"></i> Stocks
        <i class="fas fa-chevron-down sb-caret"></i>
      </div>
      <div class="submenu-items">
        <a href="stocks.php"             class="<?php echo $current=='stocks.php'?'active':''; ?>"><i class="fas fa-magnifying-glass-dollar" style="margin-right:6px;font-size:.75rem"></i>Stock Filter</a>
        <a href="stocks-research.php"    class="<?php echo $current=='stocks-research.php'?'active':''; ?>"><i class="fas fa-microscope" style="margin-right:6px;font-size:.75rem"></i>Stocks Research</a>
        <a href="consultants-choice.php" class="<?php echo $current=='consultants-choice.php'?'active':''; ?>"><i class="fas fa-star" style="margin-right:6px;font-size:.75rem"></i>Consultant's Choice</a>
      </div>
    </div>

    <!-- BROKERAGE -->
    <div class="submenu" id="brokerMenu">
      <div class="submenu-title">
        <i class="fas fa-building-columns sb-ico"></i> Brokerage
        <i class="fas fa-chevron-down sb-caret"></i>
      </div>
      <div class="submenu-items">
        <a href="broker-comparison.php" class="<?php echo $current=='broker-comparison.php'?'active':''; ?>"><i class="fas fa-scale-balanced" style="margin-right:6px;font-size:.75rem"></i>Comparisons</a>
        <a href="guides.php"            class="<?php echo $current=='guides.php'?'active':''; ?>"><i class="fas fa-book-open" style="margin-right:6px;font-size:.75rem"></i>Guides</a>
      </div>
    </div>

    <div class="sb-section">Community</div>

    <a href="community.php" class="<?php echo $current=='community.php'?'active':''; ?>">
      <i class="fas fa-users sb-ico"></i> Community
    </a>

    <a href="encouragement.php" class="<?php echo $current=='encouragement.php'?'active':''; ?>">
      <i class="fas fa-heart sb-ico"></i> Encouragement
    </a>

    <div class="sb-divider"></div>

    <a href="logout.php" class="sb-logout">
      <i class="fas fa-right-from-bracket sb-ico"></i> Logout
    </a>

  </nav>
</div>

<script>
(function(){
  const menus = [
    ['calcMenu',      0],
    ['portfolioMenu', 1],
    ['stocksMenu',    2],
    ['brokerMenu',    3],
  ];
  menus.forEach(([id]) => {
    const wrap = document.getElementById(id);
    if(!wrap) return;
    wrap.querySelector('.submenu-title').addEventListener('click', () => {
      wrap.classList.toggle('open');
    });
    // Auto-open if a child is active
    if(wrap.querySelector('.active')) wrap.classList.add('open');
  });

  // ── Mobile drawer toggle (≤1024px) — display:block/none, no transforms ──
  var ham   = document.getElementById('dbham');
  var back  = document.getElementById('dbback');
  var close = document.getElementById('dbclose');
  var panel = document.querySelector('.sidebar');
  function openDrawer(){ panel.classList.add('dbopen'); back.classList.add('show'); document.body.style.overflow='hidden'; }
  function closeDrawer(){ panel.classList.remove('dbopen'); back.classList.remove('show'); document.body.style.overflow=''; }
  if(ham)   ham.addEventListener('click', openDrawer);
  if(back)  back.addEventListener('click', closeDrawer);
  if(close) close.addEventListener('click', closeDrawer);
  // Tap any nav link → close (submenu-title toggles are divs, so they won't close it)
  if(panel){ panel.querySelectorAll('a').forEach(function(a){ a.addEventListener('click', closeDrawer); }); }
})();
</script>
