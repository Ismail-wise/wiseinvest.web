<?php include 'includes/header.php'; ?>

<style>
/* ══════════════════════════════════════════════════════════════════
   ABOUT PAGE
   ══════════════════════════════════════════════════════════════════ */

@keyframes ab-fadeUp   { from{opacity:0;transform:translateY(32px)} to{opacity:1;transform:translateY(0)} }
@keyframes ab-fadeIn   { from{opacity:0} to{opacity:1} }
@keyframes ab-shimmer  { from{background-position:-300% center} to{background-position:300% center} }
@keyframes ab-orb      { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.14) translate(16px,-12px)} }
@keyframes ab-grid     { from{background-position:0 0} to{background-position:60px 60px} }
@keyframes ab-rise     { 0%{opacity:0;transform:translateY(60px)} 20%{opacity:1} 80%{opacity:.5} 100%{opacity:0;transform:translateY(-200px)} }
@keyframes ab-pulse    { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.3)} 60%{box-shadow:0 0 0 14px rgba(212,175,55,0)} }
@keyframes ab-slide-l  { from{opacity:0;transform:translateX(-36px)} to{opacity:1;transform:translateX(0)} }
@keyframes ab-slide-r  { from{opacity:0;transform:translateX(36px)} to{opacity:1;transform:translateX(0)} }
@keyframes ab-pop      { 0%{opacity:0;transform:scale(.85) translateY(20px)} 70%{transform:scale(1.04) translateY(-2px)} 100%{opacity:1;transform:scale(1) translateY(0)} }
@keyframes ab-line-grow{ from{height:0} to{height:100%} }
@keyframes ab-dot-ping { 0%{transform:scale(1);opacity:1} 70%{transform:scale(2.4);opacity:0} 100%{opacity:0} }

/* New keyframes */
@keyframes ab-float        { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-7px)} }
@keyframes ab-ticker-scroll{ from{transform:translateX(0)} to{transform:translateX(-50%)} }
@keyframes ab-bounce-cue   { 0%,100%{transform:translateY(0)} 50%{transform:translateY(5px)} }
@keyframes ab-btn-shine    {
    0%,60%,100% { transform:skewX(-18deg) translateX(-150%); }
    30%         { transform:skewX(-18deg) translateX(420%); }
}
@keyframes ab-border-anim  { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
@keyframes ab-stat-glow    { 0%,100%{box-shadow:inset 0 1px 0 rgba(212,175,55,.06)} 50%{box-shadow:inset 0 1px 0 rgba(212,175,55,.18)} }
@keyframes ab-tl-grow      { from{height:0;opacity:0} to{height:100%;opacity:1} }
@keyframes ab-scan         { 0%{top:-100%} 100%{top:200%} }

* { box-sizing:border-box; }

/* ══ SHARED ══════════════════════════════════════════════════════ */
.ab-page { background:#020617; overflow-x:hidden; }

.ab-sec-label {
    display:inline-flex; align-items:center; gap:10px;
    font-size:.67rem; font-weight:800; letter-spacing:2.5px; text-transform:uppercase;
    color:#d4af37; margin-bottom:14px;
}
.ab-sec-label::before,
.ab-sec-label::after {
    content:''; flex:1; height:1px;
    background:linear-gradient(90deg, transparent, rgba(212,175,55,.35), transparent);
    min-width:24px;
}
.ab-sec-label-center { display:flex; justify-content:center; }

.ab-sec-title {
    font-size:clamp(1.9rem,3.5vw,2.8rem); font-weight:900; color:#fff;
    margin:0 0 12px; line-height:1.15; letter-spacing:-.5px;
}
.ab-sec-sub { color:#475569; font-size:.98rem; line-height:1.75; max-width:560px; }
.ab-sec-line {
    width:52px; height:3px; border-radius:3px;
    background:linear-gradient(90deg,#d4af37,#facc15);
    margin:18px 0 0;
}
.ab-sec-line-center { margin-left:auto; margin-right:auto; }

.ab-gold-text {
    background:linear-gradient(135deg,#d4af37 0%,#facc15 45%,#f59e0b 70%,#d4af37 100%);
    background-size:200% auto; -webkit-background-clip:text;
    -webkit-text-fill-color:transparent; background-clip:text;
    animation:ab-shimmer 4s linear infinite;
}

/* scroll reveal */
.ab-reveal { opacity:0; transform:translateY(28px); }
.ab-reveal.visible { animation:ab-fadeUp .6s ease forwards; }
.ab-reveal-l { opacity:0; transform:translateX(-36px); }
.ab-reveal-l.visible { animation:ab-slide-l .6s ease forwards; }
.ab-reveal-r { opacity:0; transform:translateX(36px); }
.ab-reveal-r.visible { animation:ab-slide-r .6s ease forwards; }
.ab-reveal-pop { opacity:0; }
.ab-reveal-pop.visible { animation:ab-pop .55s ease forwards; }

/* ══ HERO ══════════════════════════════════════════════════════ */
.ab-hero {
    position:relative; background:#020617;
    padding:110px 24px 100px; text-align:center; overflow:hidden;
}
.ab-hero-grid {
    position:absolute; inset:0; pointer-events:none;
    background-image:
        linear-gradient(rgba(212,175,55,.035) 1px,transparent 1px),
        linear-gradient(90deg,rgba(212,175,55,.035) 1px,transparent 1px);
    background-size:60px 60px;
    animation:ab-grid 16s linear infinite;
}
.ab-orb { position:absolute; border-radius:50%; filter:blur(90px); pointer-events:none; }
.ab-orb-1 { width:640px;height:640px; background:radial-gradient(circle,rgba(212,175,55,.12) 0%,transparent 70%); top:-220px;left:-160px; animation:ab-orb 10s ease-in-out infinite; }
.ab-orb-2 { width:420px;height:420px; background:radial-gradient(circle,rgba(99,102,241,.07) 0%,transparent 70%); bottom:-100px;right:-80px; animation:ab-orb 13s ease-in-out infinite reverse; }
.ab-orb-3 { width:280px;height:280px; background:radial-gradient(circle,rgba(56,189,248,.05) 0%,transparent 70%); top:35%;right:15%; animation:ab-orb 9s ease-in-out infinite 2s; }

.ab-particles { position:absolute; inset:0; pointer-events:none; overflow:hidden; }
.ab-particle  { position:absolute; width:3px;height:3px;border-radius:50%; background:rgba(212,175,55,.55); animation:ab-rise var(--d,10s) var(--dl,0s) ease-in infinite; }

.ab-hero-inner { position:relative; z-index:3; max-width:860px; margin:0 auto; }

.ab-hero-badge {
    display:inline-flex; align-items:center; gap:9px;
    background:rgba(212,175,55,.09); border:1px solid rgba(212,175,55,.28);
    color:#d4af37; padding:7px 22px; border-radius:99px;
    font-size:.68rem; font-weight:800; letter-spacing:2.5px; text-transform:uppercase;
    margin-bottom:26px; animation:ab-fadeUp .5s .1s ease both;
    box-shadow:0 0 24px rgba(212,175,55,.06);
}
.ab-hero-badge-dot {
    width:7px;height:7px;border-radius:50%;
    background:#d4af37;box-shadow:0 0 10px rgba(212,175,55,.9);
    animation:ab-bounce-cue 2s ease-in-out infinite;
}

.ab-hero-title {
    font-size:clamp(2.6rem,5.5vw,4.2rem); font-weight:900; color:#fff;
    line-height:1.08; margin:0 0 20px; letter-spacing:-1.5px;
    animation:ab-fadeUp .65s .2s ease both;
}
.ab-hero-sub {
    color:#64748b; font-size:1.08rem; line-height:1.78; max-width:600px;
    margin:0 auto 48px; animation:ab-fadeUp .65s .3s ease both;
}

/* Hero stat bar */
.ab-hero-stats {
    display:inline-flex; align-items:stretch;
    background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.07);
    border-radius:20px; overflow:hidden;
    animation:ab-fadeUp .65s .45s ease both;
    box-shadow:0 20px 60px rgba(0,0,0,.3);
}
.ab-h-stat {
    padding:22px 38px; text-align:center;
    border-right:1px solid rgba(255,255,255,.06);
    position:relative; transition:background .3s;
}
.ab-h-stat:last-child { border-right:none; }
.ab-h-stat::before {
    content:''; position:absolute; top:0; left:50%; transform:translateX(-50%);
    width:60%; height:2px;
    background:linear-gradient(90deg,transparent,#d4af37,transparent);
}
.ab-h-stat:hover { background:rgba(212,175,55,.04); }
.ab-h-stat strong {
    display:block; font-size:1.85rem; font-weight:900; color:#d4af37; line-height:1;
}
.ab-h-stat span {
    font-size:.63rem; color:#334155; letter-spacing:2px;
    text-transform:uppercase; margin-top:6px; display:block;
}

/* Platform ticker */
.ab-ticker-wrap {
    width:100%; overflow:hidden; margin-top:44px;
    border-top:1px solid rgba(255,255,255,.04);
    padding:14px 0;
    animation:ab-fadeUp .65s .56s ease both;
    mask-image:linear-gradient(90deg,transparent,black 12%,black 88%,transparent);
    -webkit-mask-image:linear-gradient(90deg,transparent,black 12%,black 88%,transparent);
}
.ab-ticker {
    display:inline-flex; align-items:center; gap:28px;
    animation:ab-ticker-scroll 28s linear infinite;
    white-space:nowrap; width:max-content;
}
.ab-ticker-wrap:hover .ab-ticker { animation-play-state:paused; }
.ab-ticker-item {
    font-size:.68rem; font-weight:700; letter-spacing:2px;
    text-transform:uppercase; color:rgba(212,175,55,.28);
    transition:color .25s;
}
.ab-ticker-wrap:hover .ab-ticker-item { color:rgba(212,175,55,.5); }
.ab-ticker-sep {
    width:5px; height:5px; border-radius:50%;
    background:rgba(212,175,55,.12); flex-shrink:0;
}

/* Scroll cue */
.ab-scroll-cue {
    position:absolute; bottom:28px; left:50%; transform:translateX(-50%);
    display:flex; flex-direction:column; align-items:center; gap:5px;
    color:#1e293b; font-size:.62rem; letter-spacing:2px; text-transform:uppercase;
    animation:ab-fadeUp .6s .7s ease both; z-index:3; pointer-events:none;
}
.ab-scroll-cue i { animation:ab-bounce-cue 1.6s ease-in-out infinite; font-size:.85rem; }

/* ══ STORY SECTION ══════════════════════════════════════════════ */
.ab-story {
    padding:96px 24px;
    background:linear-gradient(180deg,#030b18 0%,#030c1a 100%);
    border-top:1px solid rgba(255,255,255,.04);
}
.ab-story-inner { max-width:1100px; margin:0 auto; }

.ab-story-grid {
    display:grid; grid-template-columns:1fr 1fr; gap:72px; align-items:center;
}
.ab-story-text .ab-sec-sub { max-width:100%; margin-bottom:20px; }

/* Quote */
.ab-story-quote {
    margin:28px 0 0; padding:24px 24px 22px;
    background:rgba(212,175,55,.04); border:1px solid rgba(212,175,55,.13);
    border-left:3px solid #d4af37; border-radius:0 16px 16px 0;
    font-style:italic; color:#64748b; font-size:.96rem; line-height:1.75;
    position:relative; overflow:hidden;
}
.ab-story-quote::before {
    content:''; position:absolute; inset:0;
    background:linear-gradient(135deg, rgba(212,175,55,.03), transparent 60%);
    pointer-events:none;
}
.ab-quote-mark {
    display:block; font-size:4.5rem; line-height:.8; color:rgba(212,175,55,.14);
    font-family:Georgia,serif; font-weight:900; margin-bottom:4px;
    font-style:normal;
}
.ab-story-quote cite {
    display:block; margin-top:14px; font-style:normal;
    font-weight:800; color:#d4af37; font-size:.78rem; letter-spacing:1px;
}

/* Timeline */
.ab-timeline { position:relative; padding-left:28px; }
.ab-timeline::before {
    content:''; position:absolute; left:7px; top:10px; bottom:10px; width:2px;
    background:linear-gradient(180deg,#d4af37 0%,rgba(212,175,55,.08) 100%);
    border-radius:2px;
}
.ab-tl-item { position:relative; margin-bottom:34px; }
.ab-tl-item:last-child { margin-bottom:0; }
.ab-tl-dot {
    position:absolute; left:-28px; top:5px;
    width:16px; height:16px; border-radius:50%;
    background:linear-gradient(135deg,#d4af37,#facc15);
    border:2px solid #020617;
    box-shadow:0 0 0 4px rgba(212,175,55,.12);
    transition:box-shadow .3s;
}
.ab-tl-item:hover .ab-tl-dot {
    box-shadow:0 0 0 6px rgba(212,175,55,.18), 0 0 16px rgba(212,175,55,.3);
}
.ab-tl-dot::after {
    content:''; position:absolute; inset:-4px; border-radius:50%;
    border:1px solid rgba(212,175,55,.3);
    animation:ab-dot-ping 2s ease-out infinite;
}
.ab-tl-year {
    font-size:.67rem; font-weight:800; color:#d4af37;
    letter-spacing:1.5px; text-transform:uppercase; margin-bottom:5px;
}
.ab-tl-title  { font-size:.98rem; font-weight:800; color:#f1f5f9; margin-bottom:5px; }
.ab-tl-desc   { font-size:.84rem; color:#475569; line-height:1.65; }

/* Timeline item hover */
.ab-tl-item {
    padding:14px 16px; margin-left:-16px;
    border-radius:12px;
    transition:background .25s;
}
.ab-tl-item:hover { background:rgba(212,175,55,.04); }

/* ══ VISION & MISSION ═══════════════════════════════════════════ */
.ab-vm-section { padding:96px 24px; background:#020617; }
.ab-vm-inner { max-width:1000px; margin:0 auto; }
.ab-vm-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:52px; }

.ab-vm-card {
    position:relative; border-radius:26px; padding:42px 38px;
    overflow:hidden; border:1px solid rgba(255,255,255,.08);
    transition:transform .38s cubic-bezier(.34,1.56,.64,1), box-shadow .3s, border-color .3s;
}
.ab-vm-card::after {
    content:''; position:absolute; inset:0; opacity:0;
    background:linear-gradient(135deg, rgba(255,255,255,.02), transparent);
    transition:opacity .35s; pointer-events:none;
}
.ab-vm-card:hover { transform:translateY(-10px); box-shadow:0 36px 80px rgba(0,0,0,.55); }
.ab-vm-card:hover::after { opacity:1; }

/* Card scan line on hover */
.ab-vm-card::before {
    content:''; position:absolute; top:0;left:0;right:0; height:2px;
}
.ab-vm-card-vision {
    background:linear-gradient(150deg,#0d1829 0%,#060f1c 100%);
}
.ab-vm-card-vision::before {
    background:linear-gradient(90deg,transparent,#d4af37,transparent);
}
.ab-vm-card-vision:hover { border-color:rgba(212,175,55,.3); box-shadow:0 36px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(212,175,55,.12); }

.ab-vm-card-mission {
    background:linear-gradient(150deg,#0c1822 0%,#060f1c 100%);
}
.ab-vm-card-mission::before {
    background:linear-gradient(90deg,transparent,#818cf8,transparent);
}
.ab-vm-card-mission:hover { border-color:rgba(99,102,241,.35); box-shadow:0 36px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(99,102,241,.1); }

.ab-vm-card-icon {
    width:64px; height:64px; border-radius:20px; margin-bottom:24px;
    display:flex; align-items:center; justify-content:center; font-size:1.45rem;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
}
.ab-vm-card:hover .ab-vm-card-icon { transform:scale(1.12) rotate(-5deg); }
.ab-vm-card-vision  .ab-vm-card-icon { background:rgba(212,175,55,.1); border:1px solid rgba(212,175,55,.22); color:#d4af37; }
.ab-vm-card-mission .ab-vm-card-icon { background:rgba(99,102,241,.1); border:1px solid rgba(99,102,241,.22); color:#818cf8; }
.ab-vm-card-vision:hover  .ab-vm-card-icon { box-shadow:0 8px 24px rgba(212,175,55,.25); }
.ab-vm-card-mission:hover .ab-vm-card-icon { box-shadow:0 8px 24px rgba(99,102,241,.25); }

.ab-vm-card-label {
    font-size:.65rem; font-weight:800; letter-spacing:2.5px; text-transform:uppercase; margin-bottom:10px;
}
.ab-vm-card-vision  .ab-vm-card-label { color:#d4af37; }
.ab-vm-card-mission .ab-vm-card-label { color:#818cf8; }
.ab-vm-card h3 { font-size:1.38rem; font-weight:900; color:#fff; margin:0 0 14px; line-height:1.25; }
.ab-vm-card p  { color:#64748b; font-size:.95rem; line-height:1.78; margin:0; }

.ab-vm-card-num {
    position:absolute; bottom:18px; right:22px;
    font-size:5.5rem; font-weight:900; line-height:1;
    opacity:.035; color:#fff; pointer-events:none; user-select:none;
    transition:opacity .3s;
}
.ab-vm-card:hover .ab-vm-card-num { opacity:.06; }

/* ══ CORE VALUES ════════════════════════════════════════════════ */
.ab-values-section {
    padding:96px 24px;
    background:linear-gradient(180deg,#030b18 0%,#030c1a 100%);
    border-top:1px solid rgba(255,255,255,.04);
}
.ab-values-inner { max-width:1100px; margin:0 auto; }
.ab-values-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:22px; margin-top:52px; }

.ab-val-card {
    position:relative; background:linear-gradient(160deg,#0d1829 0%,#060f1c 100%);
    border:1px solid rgba(255,255,255,.07); border-radius:24px;
    padding:42px 30px 38px; text-align:center; overflow:hidden;
    transition:transform .38s cubic-bezier(.34,1.56,.64,1), border-color .3s, box-shadow .3s;
}
.ab-val-card::before {
    content:''; position:absolute; top:0; left:50%; transform:translateX(-50%);
    width:55%; height:2px;
    background:linear-gradient(90deg,transparent,#d4af37,transparent);
    opacity:0; transition:opacity .35s;
}
/* Scan line on hover */
.ab-val-card::after {
    content:''; position:absolute; left:0; right:0; height:60px;
    background:linear-gradient(180deg, transparent, rgba(212,175,55,.03), transparent);
    top:-100%; pointer-events:none;
    transition:none;
}
.ab-val-card:hover::before { opacity:1; }
.ab-val-card:hover::after  { animation:ab-scan 1.8s ease forwards; }
.ab-val-card:hover {
    transform:translateY(-10px);
    border-color:rgba(212,175,55,.28);
    box-shadow:0 32px 72px rgba(0,0,0,.58);
}

.ab-val-icon-wrap {
    width:74px; height:74px; border-radius:22px; margin:0 auto 24px;
    background:rgba(212,175,55,.08); border:1px solid rgba(212,175,55,.2);
    display:flex; align-items:center; justify-content:center;
    font-size:1.65rem; color:#d4af37;
    animation:ab-pulse 3.5s ease-in-out infinite;
    transition:box-shadow .3s;
}
.ab-val-card:hover .ab-val-icon-wrap {
    animation:ab-float 1.8s ease-in-out infinite;
    box-shadow:0 12px 32px rgba(212,175,55,.2);
}
.ab-val-num-badge {
    display:inline-block; font-size:.6rem; font-weight:800; letter-spacing:2px;
    text-transform:uppercase; color:#334155; margin-bottom:10px;
}
.ab-val-card h3 { font-size:1.15rem; font-weight:800; color:#f1f5f9; margin:0 0 12px; }
.ab-val-card p  { font-size:.87rem; color:#64748b; line-height:1.72; margin:0; }
.ab-val-card-bg-num {
    position:absolute; bottom:10px; right:14px;
    font-size:6rem; font-weight:900; line-height:1;
    opacity:.03; color:#fff; pointer-events:none; user-select:none;
    transition:opacity .3s;
}
.ab-val-card:hover .ab-val-card-bg-num { opacity:.07; }

/* ══ WHY CHOOSE US ══════════════════════════════════════════════ */
.ab-why-section { padding:96px 24px; background:#020617; }
.ab-why-inner { max-width:1100px; margin:0 auto; }
.ab-why-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin-top:52px; }

.ab-why-card {
    display:flex; align-items:flex-start; gap:20px;
    background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.07);
    border-radius:20px; padding:28px 26px;
    position:relative; overflow:hidden;
    transition:background .3s, border-color .3s, transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
}
/* Left accent bar */
.ab-why-card::before {
    content:''; position:absolute; left:0; top:0; bottom:0; width:3px;
    background:linear-gradient(180deg, #d4af37, rgba(212,175,55,.1));
    border-radius:20px 0 0 20px;
    transform:scaleY(0); transform-origin:top;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1);
}
.ab-why-card:hover::before { transform:scaleY(1); }
.ab-why-card:hover {
    background:rgba(255,255,255,.04);
    border-color:rgba(212,175,55,.2);
    transform:translateY(-5px);
    box-shadow:0 20px 50px rgba(0,0,0,.4);
}
.ab-why-num {
    position:absolute; top:14px; right:18px;
    font-size:2.8rem; font-weight:900; line-height:1;
    color:rgba(212,175,55,.05); pointer-events:none;
    user-select:none; font-variant-numeric:tabular-nums;
    transition:color .3s;
}
.ab-why-card:hover .ab-why-num { color:rgba(212,175,55,.09); }

.ab-why-icon {
    width:54px; height:54px; border-radius:15px; flex-shrink:0;
    background:rgba(212,175,55,.09); border:1px solid rgba(212,175,55,.18);
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem; color:#d4af37;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
}
.ab-why-card:hover .ab-why-icon {
    transform:scale(1.1) rotate(-5deg);
    box-shadow:0 8px 22px rgba(212,175,55,.2);
}
.ab-why-body h4 { font-size:.98rem; font-weight:800; color:#f1f5f9; margin:0 0 7px; }
.ab-why-body p  { font-size:.84rem; color:#475569; line-height:1.68; margin:0; }

/* ══ TEAM ═══════════════════════════════════════════════════════ */
.ab-team-section {
    padding:96px 24px 80px;
    background:linear-gradient(180deg,#030b18 0%,#030c1a 100%);
    border-top:1px solid rgba(255,255,255,.04);
}
.ab-team-inner   { max-width:980px; margin:0 auto; }
.ab-team-grid    { display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:24px; margin-top:52px; }

.ab-team-card {
    position:relative; text-align:center;
    background:linear-gradient(170deg,#0d1829 0%,#080f1e 100%);
    border:1px solid rgba(255,255,255,.07); border-radius:24px;
    padding:36px 22px 28px; overflow:hidden;
    transition:transform .38s cubic-bezier(.34,1.56,.64,1), border-color .3s, box-shadow .3s;
}
.ab-team-card::before {
    content:''; position:absolute; top:0;left:0;right:0; height:2px;
    background:linear-gradient(90deg,transparent,#d4af37,transparent);
    opacity:0; transition:opacity .3s;
}
/* Corner accent */
.ab-team-card::after {
    content:''; position:absolute; bottom:0; right:0;
    width:60px; height:60px;
    background:radial-gradient(circle at bottom right, rgba(212,175,55,.06), transparent 70%);
    border-radius:0 0 24px 0;
    pointer-events:none;
}
.ab-team-card:hover::before { opacity:1; }
.ab-team-card:hover {
    transform:translateY(-10px);
    border-color:rgba(212,175,55,.28);
    box-shadow:0 30px 68px rgba(0,0,0,.58);
}

.ab-team-avatar-wrap {
    position:relative; width:100px; height:100px; margin:0 auto 20px;
}
.ab-team-avatar-ring {
    position:absolute; inset:-5px; border-radius:50%;
    border:2px solid rgba(212,175,55,.22);
    animation:ab-pulse 3s ease-in-out infinite;
    transition:border-color .3s;
}
.ab-team-card:hover .ab-team-avatar-ring {
    border-color:rgba(212,175,55,.5);
    animation:ab-pulse 1.5s ease-in-out infinite;
}
.ab-team-avatar {
    width:100px; height:100px; border-radius:50%; object-fit:cover;
    border:3px solid rgba(212,175,55,.28);
    background:#0a1628; display:block;
    transition:border-color .3s, transform .35s;
}
.ab-team-card:hover .ab-team-avatar { border-color:rgba(212,175,55,.55); transform:scale(1.04); }
.ab-team-avatar-ph {
    width:100px; height:100px; border-radius:50%;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    border:3px solid rgba(212,175,55,.28);
    display:flex; align-items:center; justify-content:center;
    font-size:2.2rem; color:rgba(212,175,55,.35);
    transition:border-color .3s;
}
.ab-team-card:hover .ab-team-avatar-ph { border-color:rgba(212,175,55,.55); }

.ab-team-name {
    font-size:1rem; font-weight:800; color:#f1f5f9; margin-bottom:6px;
    transition:color .25s;
}
.ab-team-card:hover .ab-team-name { color:#fff; }
.ab-team-role {
    display:inline-block; font-size:.67rem; font-weight:700; letter-spacing:.8px;
    text-transform:uppercase; color:#d4af37; margin-bottom:18px;
    background:rgba(212,175,55,.08); border:1px solid rgba(212,175,55,.18);
    padding:3px 13px; border-radius:99px;
    transition:background .25s, border-color .25s;
}
.ab-team-card:hover .ab-team-role {
    background:rgba(212,175,55,.14);
    border-color:rgba(212,175,55,.3);
}
.ab-team-social { display:flex; justify-content:center; gap:8px; }
.ab-team-social a {
    width:34px; height:34px; border-radius:10px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
    color:#334155; display:flex; align-items:center; justify-content:center;
    font-size:.8rem; text-decoration:none;
    transition:background .22s, border-color .22s, color .22s, transform .28s cubic-bezier(.34,1.56,.64,1);
}
.ab-team-social a:hover {
    background:rgba(212,175,55,.12); border-color:rgba(212,175,55,.3);
    color:#d4af37; transform:translateY(-3px);
}

/* ══ CTA SECTION ════════════════════════════════════════════════ */
.ab-cta-section {
    padding:96px 24px; background:#020617;
    position:relative; overflow:hidden; text-align:center;
}
/* Grid overlay in CTA */
.ab-cta-grid {
    position:absolute; inset:0; pointer-events:none;
    background-image:
        linear-gradient(rgba(212,175,55,.025) 1px,transparent 1px),
        linear-gradient(90deg,rgba(212,175,55,.025) 1px,transparent 1px);
    background-size:52px 52px;
}
.ab-cta-orb-1 { position:absolute; width:520px;height:520px; border-radius:50%; filter:blur(100px); pointer-events:none; background:radial-gradient(circle,rgba(212,175,55,.1) 0%,transparent 70%); top:-200px;left:50%;transform:translateX(-50%); animation:ab-orb 10s ease-in-out infinite; }
.ab-cta-orb-2 { position:absolute; width:340px;height:340px; border-radius:50%; filter:blur(80px); pointer-events:none; background:radial-gradient(circle,rgba(99,102,241,.06) 0%,transparent 70%); bottom:-100px;right:-60px; animation:ab-orb 12s ease-in-out infinite reverse; }

.ab-cta-inner { position:relative; z-index:2; max-width:660px; margin:0 auto; }
.ab-cta-badge {
    display:inline-flex; align-items:center; gap:9px;
    background:rgba(212,175,55,.08); border:1px solid rgba(212,175,55,.22);
    color:#d4af37; padding:7px 20px; border-radius:99px;
    font-size:.68rem; font-weight:800; letter-spacing:2.5px; text-transform:uppercase;
    margin-bottom:22px;
    animation:ab-pulse 3s ease-in-out infinite;
}
.ab-cta-title { font-size:clamp(1.9rem,4vw,3rem); font-weight:900; color:#fff; margin:0 0 16px; line-height:1.15; letter-spacing:-.5px; }
.ab-cta-sub   { color:#475569; font-size:1rem; line-height:1.72; margin-bottom:40px; }
.ab-cta-btns  { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }

.ab-cta-btn-primary {
    display:inline-flex; align-items:center; gap:9px;
    background:linear-gradient(135deg,#b8960c,#d4af37,#facc15);
    background-size:200% auto; color:#020617;
    font-weight:800; font-size:.95rem; padding:15px 34px; border-radius:14px;
    text-decoration:none; box-shadow:0 6px 24px rgba(212,175,55,.35);
    transition:transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s, background-position .4s;
    position:relative; overflow:hidden;
}
.ab-cta-btn-primary .ab-btn-shine {
    position:absolute; top:0; height:100%; width:35%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.26),transparent);
    transform:skewX(-18deg) translateX(-150%);
    animation:ab-btn-shine 4s ease-in-out infinite;
    pointer-events:none;
}
.ab-cta-btn-primary:hover {
    transform:translateY(-4px);
    box-shadow:0 14px 38px rgba(212,175,55,.55);
    background-position:right center;
}
.ab-cta-btn-secondary {
    display:inline-flex; align-items:center; gap:9px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12);
    color:#cbd5e1; font-weight:700; font-size:.95rem; padding:15px 34px; border-radius:14px;
    text-decoration:none; transition:background .22s, border-color .22s, transform .25s cubic-bezier(.34,1.56,.64,1), color .22s;
}
.ab-cta-btn-secondary:hover {
    background:rgba(255,255,255,.09); border-color:rgba(255,255,255,.22);
    color:#fff; transform:translateY(-4px);
}

/* ══ RESPONSIVE ═════════════════════════════════════════════════ */
@media (max-width:900px) {
    .ab-story-grid { grid-template-columns:1fr; gap:48px; }
    .ab-vm-grid    { grid-template-columns:1fr; }
    .ab-values-grid{ grid-template-columns:1fr 1fr; }
    .ab-why-grid   { grid-template-columns:1fr; }
}
@media (max-width:600px) {
    .ab-hero { padding:80px 20px 80px; }
    .ab-hero-stats { flex-wrap:wrap; }
    .ab-h-stat { padding:16px 22px; }
    .ab-values-grid { grid-template-columns:1fr; }
    .ab-vm-card { padding:30px 24px; }
    .ab-cta-btns { flex-direction:column; align-items:center; }
    .ab-scroll-cue { display:none; }
}
</style>

<div class="ab-page">

<!-- ══ HERO ══════════════════════════════════════════════════════ -->
<section class="ab-hero">
    <div class="ab-hero-grid"></div>
    <div class="ab-orb ab-orb-1"></div>
    <div class="ab-orb ab-orb-2"></div>
    <div class="ab-orb ab-orb-3"></div>
    <div class="ab-particles">
        <div class="ab-particle" style="left:8%;  --d:10s; --dl:0s"></div>
        <div class="ab-particle" style="left:22%; --d:13s; --dl:-4s"></div>
        <div class="ab-particle" style="left:38%; --d:8s;  --dl:-7s"></div>
        <div class="ab-particle" style="left:56%; --d:11s; --dl:-2s"></div>
        <div class="ab-particle" style="left:72%; --d:9s;  --dl:-5s"></div>
        <div class="ab-particle" style="left:88%; --d:14s; --dl:-1s"></div>
    </div>

    <div class="ab-hero-inner">
        <div class="ab-hero-badge">
            <span class="ab-hero-badge-dot"></span>
            <span class="lang-en">Our Story</span>
            <span class="lang-mm" style="display:none">ကျွန်ုပ်တို့အကြောင်း</span>
        </div>

        <h1 class="ab-hero-title">
            <span class="lang-en">Built for <span class="ab-gold-text">Myanmar Investors</span></span>
            <span class="lang-mm" style="display:none">မြန်မာ ရင်းနှီးမြှုပ်နှံသူများအတွက် <span class="ab-gold-text">တည်ဆောက်ထားသည်</span></span>
        </h1>
        <p class="ab-hero-sub">
            <span class="lang-en">Wise Invest was created with one mission — to break down the barriers between Myanmar people and the global stock market.</span>
            <span class="lang-mm" style="display:none">မြန်မာ လူမျိုးများနှင့် ကမ္ဘာ့ဈေးကွက်ကြားရှိ အတားအဆီးများကို ဖြိုဖျက်ရန် တည်ထောင်ခဲ့သည်။</span>
        </p>

        <div class="ab-hero-stats">
            <div class="ab-h-stat">
                <strong data-count="500">0</strong>
                <span><span class="lang-en">Clients Helped</span><span class="lang-mm" style="display:none">ဖောက်သည်</span></span>
            </div>
            <div class="ab-h-stat">
                <strong data-count="9">0</strong>
                <span><span class="lang-en">Platforms</span><span class="lang-mm" style="display:none">ပလက်ဖောင်း</span></span>
            </div>
            <div class="ab-h-stat">
                <strong>100%</strong>
                <span><span class="lang-en">Success Rate</span><span class="lang-mm" style="display:none">အောင်မြင်မှု</span></span>
            </div>
            <div class="ab-h-stat">
                <strong>24/7</strong>
                <span><span class="lang-en">Support</span><span class="lang-mm" style="display:none">ပံ့ပိုးမှု</span></span>
            </div>
        </div>

        <!-- Scrolling platform ticker -->
        <div class="ab-ticker-wrap" aria-hidden="true">
            <div class="ab-ticker">
                <span class="ab-ticker-item">Interactive Brokers</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Tiger Brokers</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Moomoo</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">eToro</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Charles Schwab</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">XTB</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Webull</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Tastytrade</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Exante</span>
                <span class="ab-ticker-sep"></span>
                <!-- Duplicate for seamless loop -->
                <span class="ab-ticker-item">Interactive Brokers</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Tiger Brokers</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Moomoo</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">eToro</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Charles Schwab</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">XTB</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Webull</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Tastytrade</span>
                <span class="ab-ticker-sep"></span>
                <span class="ab-ticker-item">Exante</span>
                <span class="ab-ticker-sep"></span>
            </div>
        </div>
    </div>

    <!-- Scroll cue -->
    <div class="ab-scroll-cue">
        <span>Scroll</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- ══ OUR STORY ══════════════════════════════════════════════════ -->
<section class="ab-story">
    <div class="ab-story-inner">
        <div class="ab-story-grid">

            <!-- Text -->
            <div class="ab-reveal-l">
                <div class="ab-sec-label">
                    <span class="lang-en">Our Story</span>
                    <span class="lang-mm" style="display:none">ကျွန်ုပ်တို့ဇာတ်လမ်း</span>
                </div>
                <h2 class="ab-sec-title">
                    <span class="lang-en">Mingalabar —<br>Here's Who We Are</span>
                    <span class="lang-mm" style="display:none">မင်္ဂလာပါ —<br>ကျွန်ုပ်တို့ ဘယ်သူများလဲ</span>
                </h2>
                <p class="ab-sec-sub">
                    <span class="lang-en">Wise Invest started from a simple frustration — talented, ambitious Myanmar people who wanted to grow their wealth but had no clear roadmap into global investing. We became that roadmap.</span>
                    <span class="lang-mm" style="display:none">Wise Invest သည် ရိုးရှင်းသော စိတ်ပျက်မှုတစ်ခုမှ စတင်ခဲ့သည်။ ကမ္ဘာ့ရင်းနှီးမြှုပ်နှံမှုတွင် ပိုက်ဆံကြွယ်ဝချင်သော မြန်မာ လူမျိုးများကို လမ်းညွန်ရန် ကျွန်ုပ်တို့ ဖြစ်ပေါ်လာသည်။</span>
                </p>
                <p class="ab-sec-sub" style="margin-top:14px;">
                    <span class="lang-en">Today we help hundreds of clients open accounts on the world's top brokerages, guiding every step — from signup to first trade.</span>
                    <span class="lang-mm" style="display:none">ယနေ့ ကျွန်ုပ်တို့သည် ကမ္ဘာ့ထိပ်တန်း brokerage များတွင် account ဖွင့်ရန် သုံးရာကျော် Client များကို ကူညီနေသည်။</span>
                </p>
                <blockquote class="ab-story-quote">
                    <span class="ab-quote-mark">"</span>
                    <span class="lang-en">We will share more about Investment and Stocks — because financial freedom belongs to everyone.</span>
                    <span class="lang-mm" style="display:none">ငွေကြေးလွတ်လပ်မှုသည် လူတိုင်းအတွက် ဖြစ်သင့်သည်။</span>
                    <cite>— Wise Invest Team</cite>
                </blockquote>
            </div>

            <!-- Timeline -->
            <div class="ab-reveal-r" style="animation-delay:.1s">
                <div class="ab-timeline">
                    <div class="ab-tl-item">
                        <div class="ab-tl-dot"></div>
                        <div class="ab-tl-year">2022 — Founded</div>
                        <div class="ab-tl-title">
                            <span class="lang-en">Wise Invest is born</span>
                            <span class="lang-mm" style="display:none">Wise Invest ဖွဲ့စည်းသည်</span>
                        </div>
                        <p class="ab-tl-desc">
                            <span class="lang-en">Started with a mission to make global investing accessible for every Myanmar person.</span>
                            <span class="lang-mm" style="display:none">မြန်မာ လူမျိုးတိုင်း ကမ္ဘာ့ ရင်းနှီးမြှုပ်နှံမှုကို ရရှိနိုင်ရန် ဦးတည်ချက်ဖြင့် စတင်ခဲ့သည်။</span>
                        </p>
                    </div>
                    <div class="ab-tl-item">
                        <div class="ab-tl-dot"></div>
                        <div class="ab-tl-year">2023 — Growth</div>
                        <div class="ab-tl-title">
                            <span class="lang-en">First 100 clients served</span>
                            <span class="lang-mm" style="display:none">ပထမ ဖောက်သည် ၁၀၀ ဝန်ဆောင်မှုပေး</span>
                        </div>
                        <p class="ab-tl-desc">
                            <span class="lang-en">Expanded to 5 brokerages including IBKR, Tiger Brokers, and Moomoo.</span>
                            <span class="lang-mm" style="display:none">IBKR, Tiger Brokers, Moomoo အပါ brokerage ၅ ခုသို့ ချဲ့ထွင်ခဲ့သည်။</span>
                        </p>
                    </div>
                    <div class="ab-tl-item">
                        <div class="ab-tl-dot"></div>
                        <div class="ab-tl-year">2024 — Expansion</div>
                        <div class="ab-tl-title">
                            <span class="lang-en">9 platforms, 300+ clients</span>
                            <span class="lang-mm" style="display:none">ပလက်ဖောင်း ၉ ခု၊ ဖောက်သည် ၃၀၀+</span>
                        </div>
                        <p class="ab-tl-desc">
                            <span class="lang-en">Added Charles Schwab, eToro, XTB and more — now serving clients across Southeast Asia.</span>
                            <span class="lang-mm" style="display:none">Charles Schwab, eToro, XTB တို့ထည့်သွင်းကာ အရှေ့တောင်အာရှ Client များကို ဝန်ဆောင်မှုပေးသည်။</span>
                        </p>
                    </div>
                    <div class="ab-tl-item">
                        <div class="ab-tl-dot"></div>
                        <div class="ab-tl-year">2025 — Today</div>
                        <div class="ab-tl-title">
                            <span class="lang-en">500+ clients, 100% success</span>
                            <span class="lang-mm" style="display:none">ဖောက်သည် ၅၀၀+၊ အောင်မြင်မှု ၁၀၀%</span>
                        </div>
                        <p class="ab-tl-desc">
                            <span class="lang-en">Every single client we've onboarded has successfully opened and funded their brokerage account.</span>
                            <span class="lang-mm" style="display:none">ကျွန်ုပ်တို့ ဝန်ဆောင်မှုပေးခဲ့သော Client တိုင်း account ဖွင့်ရောင်းအောင်မြင်ခဲ့သည်။</span>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══ VISION & MISSION ══════════════════════════════════════════ -->
<section class="ab-vm-section">
    <div class="ab-vm-inner">
        <div class="ab-reveal" style="text-align:center">
            <div class="ab-sec-label-center">
                <div class="ab-sec-label">
                    <span class="lang-en">What Drives Us</span>
                    <span class="lang-mm" style="display:none">ကျွန်ုပ်တို့ကို တွန်းအားပေးသည်</span>
                </div>
            </div>
            <h2 class="ab-sec-title" style="text-align:center;max-width:600px;margin:0 auto 0">
                <span class="lang-en">Vision & Mission</span>
                <span class="lang-mm" style="display:none">ရည်မှန်းချက် နှင့် ရည်မှန်းချက်</span>
            </h2>
            <div class="ab-sec-line ab-sec-line-center"></div>
        </div>

        <div class="ab-vm-grid">
            <div class="ab-vm-card ab-vm-card-vision ab-reveal-l" style="animation-delay:.1s">
                <div class="ab-vm-card-icon"><i class="fas fa-eye"></i></div>
                <div class="ab-vm-card-label">Vision</div>
                <h3>
                    <span class="lang-en">No. 1 Investment Service for Myanmar</span>
                    <span class="lang-mm" style="display:none">မြန်မာအတွက် နံပါတ် ၁ ရင်းနှီးမြှုပ်နှံမှု ဝန်ဆောင်မှု</span>
                </h3>
                <p>
                    <span class="lang-en">To become the most trusted and most-used investment service provider for Myanmar people worldwide — making global markets truly accessible.</span>
                    <span class="lang-mm" style="display:none">ကမ္ဘာတစ်ဝှမ်းရှိ မြန်မာ လူမျိုးများအတွက် အယုံကြည်ဆုံးနှင့် အသုံးများဆုံး ရင်းနှီးမြှုပ်နှံမှု ဝန်ဆောင်မှုပေးသူ ဖြစ်လာရန်။</span>
                </p>
                <div class="ab-vm-card-num">01</div>
            </div>

            <div class="ab-vm-card ab-vm-card-mission ab-reveal-r" style="animation-delay:.15s">
                <div class="ab-vm-card-icon"><i class="fas fa-rocket"></i></div>
                <div class="ab-vm-card-label">Mission</div>
                <h3>
                    <span class="lang-en">Help Every Myanmar Person Build Wealth</span>
                    <span class="lang-mm" style="display:none">မြန်မာ လူမျိုးတိုင်း ကြွယ်ဝချမ်းသာမှု တည်ဆောက်နိုင်ရန်</span>
                </h3>
                <p>
                    <span class="lang-en">We exist to guide, support, and empower Myanmar people to invest in global stocks — removing complexity and building confidence at every step.</span>
                    <span class="lang-mm" style="display:none">မြန်မာ လူမျိုးများ ကမ္ဘာ့ shares တွင် ရင်းနှီးမြှုပ်နှံနိုင်ရန် လမ်းညွန်ကူညီရန် ကျွန်ုပ်တို့ ရှိနေသည်။</span>
                </p>
                <div class="ab-vm-card-num">02</div>
            </div>
        </div>
    </div>
</section>

<!-- ══ CORE VALUES ═══════════════════════════════════════════════ -->
<section class="ab-values-section">
    <div class="ab-values-inner">
        <div class="ab-reveal" style="text-align:center">
            <div class="ab-sec-label-center">
                <div class="ab-sec-label">
                    <span class="lang-en">What We Stand For</span>
                    <span class="lang-mm" style="display:none">ကျွန်ုပ်တို့ ကိုင်စွဲသည်</span>
                </div>
            </div>
            <h2 class="ab-sec-title" style="text-align:center;margin:0 auto 0">
                <span class="lang-en">Our Core Values</span>
                <span class="lang-mm" style="display:none">အဓိက တန်ဖိုးများ</span>
            </h2>
            <div class="ab-sec-line ab-sec-line-center"></div>
        </div>

        <div class="ab-values-grid">
            <div class="ab-val-card ab-reveal-pop" data-delay="0">
                <div class="ab-val-icon-wrap"><i class="fas fa-shield-alt"></i></div>
                <div class="ab-val-num-badge">01 — Honesty</div>
                <h3><span class="lang-en">Radical Transparency</span><span class="lang-mm" style="display:none">ဆောင်ရွက်ချက် ပွင့်လင်းမြင်သာမှု</span></h3>
                <p><span class="lang-en">We tell you exactly what to expect — no hidden fees, no vague promises. Just clear, honest guidance every step of the way.</span><span class="lang-mm" style="display:none">ဝှက်ထားသော ကြေးနှင့် မရေရာသော ကတိများမရှိ။ ရိုးသားသော လမ်းညွန်မှု။</span></p>
                <div class="ab-val-card-bg-num">01</div>
            </div>

            <div class="ab-val-card ab-reveal-pop" data-delay="80">
                <div class="ab-val-icon-wrap"><i class="fas fa-bolt"></i></div>
                <div class="ab-val-num-badge">02 — Speed</div>
                <h3><span class="lang-en">Fast Execution</span><span class="lang-mm" style="display:none">အမြန်ဆောင်ရွက်ခြင်း</span></h3>
                <p><span class="lang-en">We don't let opportunity wait. From inquiry to live account, we move at pace — because in investing, timing matters.</span><span class="lang-mm" style="display:none">အခွင့်အရေး မစောင့်တတ်ပါ။ စစ်မေးမှုမှ account တိုင်အောင် မြန်ဆန်စွာ ဆောင်ရွက်သည်။</span></p>
                <div class="ab-val-card-bg-num">02</div>
            </div>

            <div class="ab-val-card ab-reveal-pop" data-delay="160">
                <div class="ab-val-icon-wrap"><i class="fas fa-gem"></i></div>
                <div class="ab-val-num-badge">03 — Value Creation</div>
                <h3><span class="lang-en">Real, Lasting Value</span><span class="lang-mm" style="display:none">စစ်မှန်သော တန်ဖိုးဖန်တီးခြင်း</span></h3>
                <p><span class="lang-en">We're not just opening accounts — we're helping you build financial independence that can last a lifetime.</span><span class="lang-mm" style="display:none">Account မဖွင့်ပေးရုံမက — ဘဝတစ်သက်တာ ငွေကြေးလွတ်လပ်မှု တည်ဆောက်ရန် ကူညီသည်။</span></p>
                <div class="ab-val-card-bg-num">03</div>
            </div>
        </div>
    </div>
</section>

<!-- ══ WHY CHOOSE US ══════════════════════════════════════════════ -->
<section class="ab-why-section">
    <div class="ab-why-inner">
        <div class="ab-reveal" style="text-align:center">
            <div class="ab-sec-label-center">
                <div class="ab-sec-label">
                    <span class="lang-en">Why Wise Invest</span>
                    <span class="lang-mm" style="display:none">ဘာကြောင့် Wise Invest</span>
                </div>
            </div>
            <h2 class="ab-sec-title" style="text-align:center;margin:0 auto 0">
                <span class="lang-en">The Difference Is Clear</span>
                <span class="lang-mm" style="display:none">ကွာခြားချက် ရှင်းလင်းသည်</span>
            </h2>
            <div class="ab-sec-line ab-sec-line-center"></div>
        </div>

        <div class="ab-why-grid">
            <div class="ab-why-card ab-reveal" data-delay="0">
                <span class="ab-why-num">01</span>
                <div class="ab-why-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="ab-why-body">
                    <h4><span class="lang-en">Expert, Local Guidance</span><span class="lang-mm" style="display:none">ကျွမ်းကျင်သော ဒေသဆိုင်ရာ လမ်းညွန်မှု</span></h4>
                    <p><span class="lang-en">We understand the unique challenges Myanmar investors face — language, regulations, and access. We bridge every gap.</span><span class="lang-mm" style="display:none">ဘာသာစကား၊ စည်းမျဉ်း၊ ဝင်ရောက်ခွင့် အစရှိသည်များကို ကျွန်ုပ်တို့ ဖြေရှင်းပေးသည်။</span></p>
                </div>
            </div>

            <div class="ab-why-card ab-reveal" data-delay="60">
                <span class="ab-why-num">02</span>
                <div class="ab-why-icon"><i class="fas fa-list-check"></i></div>
                <div class="ab-why-body">
                    <h4><span class="lang-en">Step-by-Step Setup</span><span class="lang-mm" style="display:none">အဆင့်ဆင့် ဆောင်ရွက်ပေးခြင်း</span></h4>
                    <p><span class="lang-en">From document preparation to first deposit — we walk beside you through the entire process, never leaving you confused.</span><span class="lang-mm" style="display:none">စာရွက်စာတမ်းမှ ပထမဆုံး ငွေသွင်းအထိ — ကျွန်ုပ်တို့ တစ်ဆင့်ချင်းအတူ ဆောင်ရွက်သည်။</span></p>
                </div>
            </div>

            <div class="ab-why-card ab-reveal" data-delay="120">
                <span class="ab-why-num">03</span>
                <div class="ab-why-icon"><i class="fas fa-headset"></i></div>
                <div class="ab-why-body">
                    <h4><span class="lang-en">24/7 Ongoing Support</span><span class="lang-mm" style="display:none">နေ့ညဘက်မဟူဘဲ ပံ့ပိုးမှု</span></h4>
                    <p><span class="lang-en">Our support doesn't stop at account opening. We're here when you have questions about deposits, withdrawals, or anything else.</span><span class="lang-mm" style="display:none">Account ဖွင့်ပြီး၍မဆုံး — ငွေသွင်း၊ ငွေထုတ်မေးခွန်းများအတွက် အမြဲ ရှိနေသည်။</span></p>
                </div>
            </div>

            <div class="ab-why-card ab-reveal" data-delay="180">
                <span class="ab-why-num">04</span>
                <div class="ab-why-icon"><i class="fas fa-globe"></i></div>
                <div class="ab-why-body">
                    <h4><span class="lang-en">9+ Global Platforms</span><span class="lang-mm" style="display:none">ကမ္ဘာ့ ပလက်ဖောင်း ၉ ခုကျော်</span></h4>
                    <p><span class="lang-en">IBKR, Tiger, Moomoo, eToro, Schwab and more — we're certified to guide you on every major platform so you pick the right fit.</span><span class="lang-mm" style="display:none">IBKR, Tiger, Moomoo, eToro, Schwab နှင့် အခြားများ — သင့်အတွက် အသင့်တော်ဆုံးကို ရွေးနိုင်ရန် ကူညီသည်။</span></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ TEAM ═══════════════════════════════════════════════════════ -->
<section class="ab-team-section">
    <div class="ab-team-inner">
        <div class="ab-reveal" style="text-align:center">
            <div class="ab-sec-label-center">
                <div class="ab-sec-label">
                    <span class="lang-en">The People Behind It</span>
                    <span class="lang-mm" style="display:none">နောက်ကွယ်မှ လူများ</span>
                </div>
            </div>
            <h2 class="ab-sec-title" style="text-align:center;margin:0 auto 0">
                <span class="lang-en">Meet Our Experts</span>
                <span class="lang-mm" style="display:none">ကျွမ်းကျင်ပညာရှင်များ</span>
            </h2>
            <p class="ab-sec-sub" style="text-align:center;margin:10px auto 0;">
                <span class="lang-en">A small, dedicated team with one shared goal — your financial success.</span>
                <span class="lang-mm" style="display:none">သင်၏ ငွေကြေးအောင်မြင်မှု တစ်ခုတည်းသော ရည်မှန်းချက်ဖြင့် 헌신ကြသော အဖွဲ့ငယ်။</span>
            </p>
            <div class="ab-sec-line ab-sec-line-center"></div>
        </div>

        <div class="ab-team-grid">
            <?php
            $team = [
                ['img'=>'images/team1.jpg', 'name'=>"Ismai'L",       'role'=>'Customer Relations'],
                ['img'=>'images/team2.jpg', 'name'=>'Aung Khin Sint', 'role'=>'Consultant'],
                ['img'=>'images/team3.jpg', 'name'=>'Aung Ko Ko',     'role'=>'Technician'],
                ['img'=>'images/team4.jpg', 'name'=>'Yin Lae',        'role'=>'Account & Finance'],
            ];
            foreach ($team as $i => $m): ?>
            <div class="ab-team-card ab-reveal-pop" data-delay="<?= $i * 80; ?>">
                <div class="ab-team-avatar-wrap">
                    <div class="ab-team-avatar-ring"></div>
                    <img src="<?= htmlspecialchars($m['img']); ?>"
                         alt="<?= htmlspecialchars($m['name']); ?>"
                         class="ab-team-avatar"
                         onerror="this.outerHTML='<div class=\'ab-team-avatar-ph\'><i class=\'fas fa-user\'></i></div>'">
                </div>
                <div class="ab-team-name"><?= htmlspecialchars($m['name']); ?></div>
                <div class="ab-team-role"><?= htmlspecialchars($m['role']); ?></div>
                <div class="ab-team-social">
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ CTA ═══════════════════════════════════════════════════════ -->
<section class="ab-cta-section">
    <div class="ab-cta-grid"></div>
    <div class="ab-cta-orb-1"></div>
    <div class="ab-cta-orb-2"></div>
    <div class="ab-cta-inner ab-reveal">
        <div class="ab-cta-badge">
            <i class="fas fa-rocket"></i>
            <span class="lang-en">Ready to Invest?</span>
            <span class="lang-mm" style="display:none">ရင်းနှီးမြှုပ်နှံရန် အဆင်သင့်ဖြစ်ပြီလား?</span>
        </div>
        <h2 class="ab-cta-title">
            <span class="lang-en">Start Your Global<br><span class="ab-gold-text">Investment Journey</span></span>
            <span class="lang-mm" style="display:none">ကမ္ဘာ့ <span class="ab-gold-text">ရင်းနှီးမြှုပ်နှံမှု</span> စတင်ပါ</span>
        </h2>
        <p class="ab-cta-sub">
            <span class="lang-en">Join hundreds of Myanmar investors who have already opened their brokerage accounts with our help. It's easier than you think.</span>
            <span class="lang-mm" style="display:none">ကျွန်ုပ်တို့၏ ကူညီမှုဖြင့် brokerage account ဖွင့်ပြီးသော မြန်မာ ရင်းနှီးမြှုပ်နှံသူ သုံးရာကျော်တို့နှင့် ပေါင်းဆည်ပါ။</span>
        </p>
        <div class="ab-cta-btns">
            <a href="services.php" class="ab-cta-btn-primary">
                <span class="ab-btn-shine"></span>
                <i class="fas fa-globe"></i>
                <span class="lang-en">Browse Brokers</span>
                <span class="lang-mm" style="display:none">Broker များကြည့်မည်</span>
            </a>
            <a href="contact.php" class="ab-cta-btn-secondary">
                <i class="fas fa-paper-plane"></i>
                <span class="lang-en">Contact Us</span>
                <span class="lang-mm" style="display:none">ဆက်သွယ်မည်</span>
            </a>
        </div>
    </div>
</section>

</div><!-- /.ab-page -->

<script>
(function () {
    /* ── Scroll-reveal ── */
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const d = parseInt(e.target.dataset.delay) || 0;
            setTimeout(() => e.target.classList.add('visible'), d);
            obs.unobserve(e.target);
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.ab-reveal, .ab-reveal-l, .ab-reveal-r, .ab-reveal-pop').forEach(el => obs.observe(el));

    /* ── Animated stat counters ── */
    const counters = document.querySelectorAll('[data-count]');
    if (counters.length) {
        const cobs = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (!e.isIntersecting) return;
                const el  = e.target;
                const end = parseInt(el.dataset.count);
                const dur = 1800;
                const step = 16;
                const inc  = end / (dur / step);
                let cur  = 0;
                const timer = setInterval(() => {
                    cur += inc;
                    if (cur >= end) { cur = end; clearInterval(timer); }
                    el.textContent = Math.floor(cur) + (end >= 100 ? '+' : '+');
                }, step);
                cobs.unobserve(el);
            });
        }, { threshold: 0.5 });
        counters.forEach(el => cobs.observe(el));
    }
}());
</script>

<?php include 'includes/footer.php'; ?>
