<?php include 'includes/header.php'; ?>

<style>
/* ══════════════════════════════════════════════════════════════════
   SERVICES PAGE — v4
   ══════════════════════════════════════════════════════════════════ */

@keyframes sv-fadeUp    { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
@keyframes sv-fadeIn    { from{opacity:0} to{opacity:1} }
@keyframes sv-shimmer   { from{background-position:-400% center} to{background-position:400% center} }
@keyframes sv-orb       { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.14) translate(14px,-10px)} }
@keyframes sv-grid      { from{background-position:0 0} to{background-position:60px 60px} }
@keyframes sv-rise      { 0%{opacity:0;transform:translateY(50px)} 20%{opacity:1} 80%{opacity:.5} 100%{opacity:0;transform:translateY(-180px)} }
@keyframes sv-img-in    { from{opacity:0;transform:scale(.97)} to{opacity:1;transform:scale(1)} }
@keyframes sv-rank-pop  { 0%{transform:scale(0) rotate(-15deg)} 70%{transform:scale(1.15) rotate(3deg)} 100%{transform:scale(1) rotate(0)} }
@keyframes sv-row-slide { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
@keyframes sv-bar-grow  { from{width:0} to{width:var(--w)} }
@keyframes sv-count     { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

.sv-page { background:#020617; }

/* ══ HERO ══════════════════════════════════════════════════════════ */
.sv-hero {
    position:relative; padding:104px 24px 96px;
    background:#020617; overflow:hidden; text-align:center;
}
.sv-hero-grid {
    position:absolute; inset:0; pointer-events:none;
    background-image:
        linear-gradient(rgba(212,175,55,.03) 1px,transparent 1px),
        linear-gradient(90deg,rgba(212,175,55,.03) 1px,transparent 1px);
    background-size:60px 60px;
    animation:sv-grid 14s linear infinite;
}
.sv-orb { position:absolute; border-radius:50%; filter:blur(80px); pointer-events:none; }
.sv-orb-1 { width:600px;height:600px; background:radial-gradient(circle,rgba(212,175,55,.13) 0%,transparent 70%); top:-200px;right:-100px; animation:sv-orb 10s ease-in-out infinite; }
.sv-orb-2 { width:380px;height:380px; background:radial-gradient(circle,rgba(99,102,241,.08) 0%,transparent 70%); bottom:-80px;left:-80px; animation:sv-orb 13s ease-in-out infinite reverse; }
.sv-orb-3 { width:240px;height:240px; background:radial-gradient(circle,rgba(56,189,248,.06) 0%,transparent 70%); top:30%;left:10%; animation:sv-orb 8s ease-in-out infinite; }

.sv-particles { position:absolute; inset:0; pointer-events:none; overflow:hidden; }
.sv-particle  { position:absolute; width:3px; height:3px; border-radius:50%; background:rgba(212,175,55,.5); animation:sv-rise var(--d,9s) var(--dl,0s) ease-in infinite; }

.sv-hero-inner { position:relative; z-index:3; max-width:900px; margin:0 auto; }
.sv-badge {
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(212,175,55,.08); border:1px solid rgba(212,175,55,.25);
    color:#d4af37; padding:7px 20px; border-radius:99px;
    font-size:.7rem; font-weight:800; letter-spacing:2.5px; text-transform:uppercase;
    margin-bottom:24px; animation:sv-fadeUp .5s .1s ease both;
}
.sv-badge-dot { width:6px;height:6px;border-radius:50%;background:#d4af37;box-shadow:0 0 8px rgba(212,175,55,.8); }
.sv-hero-title {
    font-size:clamp(2.4rem,5vw,3.8rem); font-weight:900; color:#fff;
    line-height:1.1; margin:0 0 18px; letter-spacing:-1px;
    animation:sv-fadeUp .6s .2s ease both;
}
.sv-gold { background:linear-gradient(135deg,#d4af37 0%,#facc15 40%,#f59e0b 70%,#d4af37 100%); background-size:200% auto; -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; animation:sv-shimmer 4s linear infinite; }
.sv-hero-sub { color:#64748b; font-size:1.06rem; line-height:1.7; max-width:580px; margin:0 auto 38px; animation:sv-fadeUp .6s .3s ease both; }

.sv-trust { display:flex; justify-content:center; flex-wrap:wrap; gap:10px; margin-bottom:44px; animation:sv-fadeUp .6s .4s ease both; }
.sv-trust-pill { display:flex;align-items:center;gap:7px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); color:#94a3b8; padding:8px 16px; border-radius:99px; font-size:.82rem; transition:border-color .2s,color .2s; }
.sv-trust-pill i { color:#d4af37; font-size:.74rem; }
.sv-trust-pill:hover { border-color:rgba(212,175,55,.3); color:#f1f5f9; }

.sv-hero-stats { display:inline-flex; background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.07); border-radius:18px; overflow:hidden; animation:sv-fadeUp .6s .5s ease both; }
.sv-h-stat { padding:18px 32px; text-align:center; border-right:1px solid rgba(255,255,255,.06); position:relative; }
.sv-h-stat:last-child { border-right:none; }
.sv-h-stat::before { content:''; position:absolute; top:0; left:50%; transform:translateX(-50%); width:40%; height:2px; background:linear-gradient(90deg,transparent,#d4af37,transparent); }
.sv-h-stat strong { display:block; font-size:1.6rem; font-weight:900; color:#d4af37; }
.sv-h-stat span   { font-size:.68rem; color:#475569; letter-spacing:1.5px; text-transform:uppercase; }

/* ══ HOW IT WORKS ═══════════════════════════════════════════════════ */
.sv-how { padding:72px 24px; background:#030b18; border-top:1px solid rgba(255,255,255,.04); border-bottom:1px solid rgba(255,255,255,.04); }
.sv-how-inner { max-width:1100px; margin:0 auto; }
.sv-sec { text-align:center; margin-bottom:48px; }
.sv-sec h2 { font-size:1.8rem; font-weight:900; color:#fff; margin:0 0 8px; }
.sv-sec p  { color:#475569; font-size:.95rem; }
.sv-sec-line { width:48px; height:3px; border-radius:3px; background:linear-gradient(90deg,#d4af37,#facc15); margin:14px auto 0; }

.sv-steps { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.06); border-radius:20px; overflow:hidden; }
.sv-step { background:#030b18; padding:36px 24px; text-align:center; position:relative; transition:background .3s; }
.sv-step:hover { background:#0a1628; }
.sv-step-num { width:48px;height:48px;border-radius:14px; background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.2); display:flex;align-items:center;justify-content:center; margin:0 auto 16px; font-size:1.1rem;font-weight:900;color:#d4af37; transition:background .3s,box-shadow .3s; }
.sv-step:hover .sv-step-num { background:rgba(212,175,55,.15); box-shadow:0 0 20px rgba(212,175,55,.2); }
.sv-step h4 { font-size:.9rem; font-weight:700; color:#f1f5f9; margin:0 0 7px; }
.sv-step p  { font-size:.78rem; color:#475569; line-height:1.6; margin:0; }
.sv-step::after { content:'→'; position:absolute; right:-10px; top:50%; transform:translateY(-50%); color:rgba(212,175,55,.2); font-size:1.2rem; z-index:2; }
.sv-step:last-child::after { display:none; }

/* ══ BROKER CAROUSEL ═════════════════════════════════════════════════ */
.sv-brokers { padding:80px 24px 100px; background:#020617; }
.sv-brokers-inner { max-width:700px; margin:0 auto; }

.sv-carousel { position:relative; display:flex; align-items:center; gap:20px; }
.sv-carousel-track-wrap { flex:1; overflow:hidden; border-radius:24px; }
.sv-carousel-track { display:flex; transition:transform .45s cubic-bezier(.65,0,.35,1); }

.sv-card {
    position:relative; min-width:100%; width:100%;
    background:linear-gradient(170deg,#0e1c30 0%,#080f1e 100%);
    border:1px solid rgba(255,255,255,.09); border-radius:24px; overflow:hidden;
    display:flex; flex-direction:column; box-sizing:border-box;
    box-shadow:0 20px 60px rgba(0,0,0,.5); transition:border-color .3s,box-shadow .3s;
}
.sv-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background:linear-gradient(90deg,transparent,#d4af37,transparent);
}
.sv-card-inner {
    display:flex; flex-direction:column; align-items:center;
    padding:52px 40px 36px; gap:22px;
    border-bottom:1px solid rgba(255,255,255,.06); flex:1;
}
.sv-card-logo-box {
    width:130px; height:130px; background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.1); border-radius:28px;
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 40px rgba(0,0,0,.4);
}
.sv-card-logo-box img { max-width:92px; max-height:68px; object-fit:contain; filter:brightness(1.15); }
.sv-card-info { text-align:center; }
.sv-card-name { font-size:1.55rem; font-weight:900; color:#f1f5f9; margin:0 0 12px; letter-spacing:-.3px; }
.sv-card-type {
    display:inline-flex; align-items:center; gap:7px;
    font-size:.75rem; font-weight:600; letter-spacing:1px; text-transform:uppercase;
    color:#64748b; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
    padding:6px 18px; border-radius:99px;
}
.sv-card-type i { color:rgba(212,175,55,.7); font-size:.55rem; }
.sv-card-actions { display:flex; gap:12px; padding:24px 32px 30px; }
.sv-card-btn {
    flex:1; padding:14px 18px; border-radius:13px; font-size:.9rem; font-weight:700;
    border:none; cursor:pointer; text-decoration:none;
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:transform .22s,box-shadow .22s,background .2s,border-color .2s;
    font-family:inherit; white-space:nowrap;
}
.sv-card-btn:hover { transform:translateY(-2px); }
.sv-btn-details { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); color:#cbd5e1; }
.sv-btn-details:hover { background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.22); }
.sv-btn-details[data-hidden="true"] { display:none; }
.sv-btn-service { background:linear-gradient(135deg,#d4af37,#facc15); color:#020617; box-shadow:0 4px 18px rgba(212,175,55,.3); }
.sv-btn-service:hover { box-shadow:0 8px 28px rgba(212,175,55,.55); }

.sv-car-arrow {
    flex-shrink:0; width:52px; height:52px; border-radius:50%;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#94a3b8; font-size:1rem;
    transition:background .2s,border-color .2s,color .2s,transform .2s;
}
.sv-car-arrow:hover { background:rgba(212,175,55,.15); border-color:rgba(212,175,55,.4); color:#facc15; transform:scale(1.08); }
.sv-car-arrow.disabled { opacity:.25; pointer-events:none; }

.sv-car-nav { display:flex; flex-direction:column; align-items:center; gap:14px; margin-top:28px; }
.sv-car-dots { display:flex; gap:8px; align-items:center; }
.sv-car-dot { width:8px; height:8px; border-radius:50%; background:rgba(255,255,255,.15); cursor:pointer; transition:background .25s,width .25s; }
.sv-car-dot.active { background:#d4af37; width:28px; border-radius:4px; }
.sv-car-counter { font-size:.78rem; font-weight:700; color:#334155; letter-spacing:1px; }
.sv-car-counter span { color:#d4af37; }

/* ══ RANKINGS SECTION ════════════════════════════════════════════════ */
.sv-rankings { padding:80px 24px 90px; background:#030b18; border-top:1px solid rgba(255,255,255,.04); }
.sv-rankings-inner { max-width:860px; margin:0 auto; }

.sv-rank-list { display:flex; flex-direction:column; gap:12px; }

.sv-rank-row {
    display:flex; align-items:center; gap:18px;
    background:linear-gradient(135deg,#0a1628 0%,#060f1c 100%);
    border:1px solid rgba(255,255,255,.07); border-radius:16px;
    padding:16px 20px;
    opacity:0; transform:translateX(-24px);
    transition:border-color .3s,box-shadow .3s,transform .3s;
    cursor:default;
}
.sv-rank-row.visible { animation:sv-row-slide .5s ease forwards; }
.sv-rank-row:hover { border-color:rgba(212,175,55,.2); box-shadow:0 8px 32px rgba(0,0,0,.4); transform:translateX(4px) !important; }

/* Rank badge */
.sv-rank-badge {
    width:44px; height:44px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; font-weight:900;
}
.sv-rank-badge.gold   { background:linear-gradient(135deg,#d4af37,#facc15); color:#020617; box-shadow:0 4px 16px rgba(212,175,55,.4); }
.sv-rank-badge.silver { background:linear-gradient(135deg,#94a3b8,#cbd5e1); color:#020617; box-shadow:0 4px 16px rgba(148,163,184,.3); }
.sv-rank-badge.bronze { background:linear-gradient(135deg,#c2773a,#e8965a); color:#020617; box-shadow:0 4px 16px rgba(194,119,58,.3); }
.sv-rank-badge.default{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); color:#475569; }

/* Rank logo */
.sv-rank-logo {
    width:44px; height:44px; border-radius:10px; flex-shrink:0;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
    display:flex; align-items:center; justify-content:center; overflow:hidden;
}
.sv-rank-logo img { max-width:34px; max-height:26px; object-fit:contain; filter:brightness(1.1); }

/* Rank info */
.sv-rank-info { flex:1; min-width:0; }
.sv-rank-name { font-size:.95rem; font-weight:800; color:#f1f5f9; margin:0 0 3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sv-rank-cat  { font-size:.72rem; color:#475569; font-weight:600; }

/* Score bar */
.sv-rank-score { text-align:right; flex-shrink:0; min-width:120px; }
.sv-rank-score-num { font-size:1rem; font-weight:900; color:#d4af37; margin-bottom:6px; }
.sv-rank-bar-wrap { height:5px; border-radius:3px; background:rgba(255,255,255,.06); overflow:hidden; }
.sv-rank-bar { height:100%; border-radius:3px; width:0; background:linear-gradient(90deg,#d4af37,#facc15); transition:width 1.2s cubic-bezier(.4,0,.2,1); }

/* Rank label pill */
.sv-rank-label {
    font-size:.65rem; font-weight:700; letter-spacing:.8px; text-transform:uppercase;
    padding:4px 10px; border-radius:99px;
    white-space:nowrap; flex-shrink:0;
}
.sv-rank-label.best-overall   { background:rgba(212,175,55,.1); color:#d4af37; border:1px solid rgba(212,175,55,.2); }
.sv-rank-label.best-asia      { background:rgba(99,102,241,.1); color:#818cf8; border:1px solid rgba(99,102,241,.2); }
.sv-rank-label.best-social    { background:rgba(34,197,94,.08); color:#4ade80; border:1px solid rgba(34,197,94,.2); }
.sv-rank-label.best-research  { background:rgba(56,189,248,.08); color:#38bdf8; border:1px solid rgba(56,189,248,.2); }
.sv-rank-label.best-us        { background:rgba(249,115,22,.08); color:#fb923c; border:1px solid rgba(249,115,22,.2); }
.sv-rank-label.best-cfd       { background:rgba(168,85,247,.08); color:#c084fc; border:1px solid rgba(168,85,247,.2); }
.sv-rank-label.best-transfer  { background:rgba(20,184,166,.08); color:#2dd4bf; border:1px solid rgba(20,184,166,.2); }
.sv-rank-label.default        { background:rgba(255,255,255,.04); color:#475569; border:1px solid rgba(255,255,255,.07); }

/* ══ COUNTRY TABLE SECTION ═══════════════════════════════════════════ */
.sv-countries { padding:80px 24px 100px; background:#020617; }
.sv-countries-inner { max-width:1100px; margin:0 auto; }

/* Country filter tabs */
.sv-ct-filters {
    display:flex; flex-wrap:wrap; gap:8px; justify-content:center;
    margin-bottom:36px;
}
.sv-ct-filter {
    padding:7px 18px; border-radius:99px; font-size:.78rem; font-weight:700; letter-spacing:.5px;
    border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.03); color:#64748b;
    cursor:pointer; transition:background .2s,border-color .2s,color .2s;
}
.sv-ct-filter:hover   { border-color:rgba(212,175,55,.3); color:#cbd5e1; }
.sv-ct-filter.active  { background:rgba(212,175,55,.12); border-color:rgba(212,175,55,.4); color:#d4af37; }

/* Table wrapper */
.sv-ct-wrap {
    overflow-x:auto; border-radius:18px;
    border:1px solid rgba(255,255,255,.07);
    scrollbar-width:thin; scrollbar-color:rgba(212,175,55,.2) transparent;
}
.sv-ct-wrap::-webkit-scrollbar { height:5px; }
.sv-ct-wrap::-webkit-scrollbar-track { background:transparent; }
.sv-ct-wrap::-webkit-scrollbar-thumb { background:rgba(212,175,55,.25); border-radius:3px; }

.sv-ct-table { width:100%; border-collapse:collapse; min-width:720px; }

/* Header row */
.sv-ct-table thead tr { background:#030b18; }
.sv-ct-table thead th {
    padding:14px 16px; font-size:.72rem; font-weight:800; letter-spacing:1px;
    text-transform:uppercase; color:#475569; text-align:center; white-space:nowrap;
    border-bottom:1px solid rgba(255,255,255,.07);
}
.sv-ct-table thead th:first-child { text-align:left; min-width:180px; }

/* Body rows */
.sv-ct-table tbody tr {
    border-bottom:1px solid rgba(255,255,255,.04);
    transition:background .2s;
    opacity:0; transform:translateX(-16px);
}
.sv-ct-table tbody tr.visible { animation:sv-row-slide .45s ease forwards; }
.sv-ct-table tbody tr:hover { background:rgba(255,255,255,.025); }
.sv-ct-table tbody tr:last-child { border-bottom:none; }

/* Broker name cell */
.sv-ct-broker { display:flex; align-items:center; gap:12px; padding:14px 16px; }
.sv-ct-b-logo {
    width:36px; height:36px; border-radius:9px; flex-shrink:0;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);
    display:flex; align-items:center; justify-content:center;
}
.sv-ct-b-logo img { max-width:28px; max-height:21px; object-fit:contain; filter:brightness(1.1); }
.sv-ct-b-name { font-size:.88rem; font-weight:700; color:#e2e8f0; }

/* Availability cell */
.sv-ct-table tbody td { text-align:center; padding:14px 16px; }
.sv-ct-yes {
    display:inline-flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:8px;
    background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.2); color:#4ade80;
    font-size:.75rem;
}
.sv-ct-no {
    display:inline-flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:8px;
    background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.12); color:#475569;
    font-size:.75rem;
}
.sv-ct-partial {
    display:inline-flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:8px;
    background:rgba(245,158,11,.08); border:1px solid rgba(245,158,11,.2); color:#fbbf24;
    font-size:.75rem;
}

/* Legend */
.sv-ct-legend {
    display:flex; gap:20px; justify-content:center; flex-wrap:wrap;
    margin-top:20px;
}
.sv-ct-legend-item { display:flex; align-items:center; gap:8px; font-size:.75rem; color:#475569; }

/* ══ LIGHTBOX — redesigned side-by-side ══════════════════════════════ */
.sv-lb {
    position:fixed; inset:0; z-index:9999;
    display:none; align-items:center; justify-content:center;
    padding:16px;
}
.sv-lb.active { display:flex; }

.sv-lb-backdrop {
    position:absolute; inset:0;
    background:rgba(0,0,0,.9);
    backdrop-filter:blur(16px);
    animation:sv-fadeIn .25s ease;
}

.sv-lb-panel {
    position:relative; z-index:2;
    width:100%; max-width:1160px;
    height:90vh;
    background:linear-gradient(160deg,#0d1b2e 0%,#060f1c 100%);
    border:1px solid rgba(212,175,55,.18);
    border-radius:22px;
    display:flex; flex-direction:column;
    overflow:hidden;
    box-shadow:0 40px 100px rgba(0,0,0,.8);
    animation:sv-fadeUp .3s ease;
}

.sv-lb-topbar {
    height:2px; flex-shrink:0;
    background:linear-gradient(90deg,transparent,#d4af37,#facc15,#d4af37,transparent);
}

/* Header bar */
.sv-lb-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 22px;
    border-bottom:1px solid rgba(255,255,255,.06);
    flex-shrink:0;
}
.sv-lb-broker-wrap { display:flex; align-items:center; gap:12px; }
.sv-lb-broker-logo {
    width:46px; height:46px; border-radius:11px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09);
    display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;
}
.sv-lb-broker-logo img { max-width:36px; max-height:28px; object-fit:contain; filter:brightness(1.2); }
.sv-lb-title    { font-size:1.05rem; font-weight:800; color:#fff; margin:0; }
.sv-lb-subtitle { font-size:.7rem; color:#475569; margin:2px 0 0; }

.sv-lb-header-right { display:flex; align-items:center; gap:10px; }
.sv-lb-counter {
    font-size:.78rem; font-weight:700; color:#64748b;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);
    padding:4px 11px; border-radius:8px;
}
.sv-lb-counter span { color:#d4af37; }
.sv-lb-close {
    width:34px; height:34px; border-radius:9px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#64748b; font-size:.95rem;
    transition:background .2s,color .2s;
}
.sv-lb-close:hover { background:rgba(239,68,68,.12); color:#f87171; border-color:rgba(239,68,68,.2); }

/* Body: image stage + sidebar */
.sv-lb-body {
    display:flex; flex:1; min-height:0;
}

/* Image stage — takes 75% */
.sv-lb-stage {
    flex:1; position:relative; overflow:hidden;
    display:flex; align-items:center; justify-content:center;
    background:#040c18; min-height:0;
}
.sv-lb-img {
    max-width:100%; max-height:100%;
    width:100%; height:100%;
    object-fit:contain;
    animation:sv-img-in .3s ease;
    display:block; padding:20px; box-sizing:border-box;
}

.sv-lb-arrow {
    position:absolute; top:50%; transform:translateY(-50%);
    width:46px; height:46px; border-radius:50%;
    background:rgba(6,15,28,.85); border:1px solid rgba(255,255,255,.1);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#cbd5e1; font-size:.95rem; z-index:3;
    transition:background .2s,border-color .2s,color .2s;
    backdrop-filter:blur(8px);
}
.sv-lb-arrow:hover { background:rgba(212,175,55,.2); border-color:rgba(212,175,55,.4); color:#facc15; }
.sv-lb-arrow-prev { left:14px; }
.sv-lb-arrow-next { right:14px; }
.sv-lb-arrow.disabled { opacity:.2; pointer-events:none; }

/* No-data */
.sv-lb-nodata { text-align:center; padding:60px 24px; color:#475569; }
.sv-lb-nodata i { font-size:2.5rem; color:rgba(212,175,55,.15); display:block; margin-bottom:14px; }
.sv-lb-nodata h4 { color:#64748b; margin:0 0 6px; }

/* Sidebar — 260px wide */
.sv-lb-sidebar {
    width:260px; flex-shrink:0;
    border-left:1px solid rgba(255,255,255,.06);
    background:rgba(4,12,24,.5);
    display:flex; flex-direction:column;
    overflow:hidden;
}
.sv-lb-sidebar-label {
    padding:16px 18px 10px;
    font-size:.65rem; font-weight:800; letter-spacing:2px; text-transform:uppercase;
    color:#334155;
    border-bottom:1px solid rgba(255,255,255,.04);
    flex-shrink:0;
}
.sv-lb-thumbs {
    flex:1; overflow-y:auto; padding:12px;
    display:flex; flex-direction:column; gap:8px;
    scrollbar-width:thin; scrollbar-color:rgba(212,175,55,.2) transparent;
}
.sv-lb-thumbs::-webkit-scrollbar { width:3px; }
.sv-lb-thumbs::-webkit-scrollbar-thumb { background:rgba(212,175,55,.25); border-radius:2px; }

.sv-lb-thumb {
    border-radius:10px; overflow:hidden;
    border:2px solid rgba(255,255,255,.07);
    cursor:pointer; flex-shrink:0;
    transition:border-color .2s,transform .2s,box-shadow .2s;
    aspect-ratio:16/10;
}
.sv-lb-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.sv-lb-thumb:hover { border-color:rgba(212,175,55,.3); transform:scale(1.02); }
.sv-lb-thumb.active { border-color:#d4af37; box-shadow:0 4px 16px rgba(212,175,55,.3); }

.sv-lb-sidebar-footer {
    padding:14px 16px 18px;
    border-top:1px solid rgba(255,255,255,.05);
    flex-shrink:0; display:flex; flex-direction:column; gap:10px;
}
.sv-lb-hint { font-size:.68rem; color:#1e293b; display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
.sv-lb-hint kbd {
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
    padding:2px 6px; border-radius:4px; font-size:.62rem; color:#334155; font-family:inherit;
}
.sv-lb-cta {
    display:flex; align-items:center; justify-content:center; gap:7px;
    background:linear-gradient(135deg,#d4af37,#facc15);
    color:#020617; font-weight:800; font-size:.84rem;
    padding:11px 16px; border-radius:10px;
    text-decoration:none;
    box-shadow:0 4px 14px rgba(212,175,55,.3);
    transition:transform .2s,box-shadow .2s;
}
.sv-lb-cta:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(212,175,55,.5); }

/* ── Responsive ───────────────────────────────────────────────── */
@media (max-width:760px) {
    .sv-lb-panel { height:95vh; flex-direction:column; }
    .sv-lb-body { flex-direction:column; }
    .sv-lb-sidebar { width:100%; height:160px; flex-direction:row; border-left:none; border-top:1px solid rgba(255,255,255,.06); }
    .sv-lb-sidebar-label { writing-mode:initial; padding:10px 14px 8px; border-bottom:none; border-right:1px solid rgba(255,255,255,.04); min-width:60px; display:flex; align-items:center; }
    .sv-lb-thumbs { flex-direction:row; overflow-x:auto; overflow-y:hidden; padding:10px; }
    .sv-lb-thumb { width:100px; height:70px; aspect-ratio:unset; flex-shrink:0; }
    .sv-lb-sidebar-footer { border-top:none; border-left:1px solid rgba(255,255,255,.04); min-width:140px; justify-content:center; }
}
@media (max-width:600px) {
    .sv-brokers-inner { max-width:100%; }
    .sv-car-arrow { width:40px; height:40px; font-size:.85rem; }
    .sv-card-inner { padding:40px 24px 28px; }
    .sv-card-actions { padding:20px 20px 24px; gap:10px; flex-direction:column; }
    .sv-card-name { font-size:1.3rem; }
    .sv-card-logo-box { width:110px; height:110px; }
    .sv-rank-label { display:none; }
    .sv-rank-score { min-width:80px; }
}
@media (max-width:640px) {
    .sv-hero-stats { flex-wrap:wrap; }
    .sv-h-stat { padding:14px 20px; }
    .sv-steps { grid-template-columns:1fr; }
    .sv-step::after { display:none; }
}
</style>

<div class="sv-page">

<!-- ══ HERO ══════════════════════════════════════════════════════════ -->
<section class="sv-hero">
    <div class="sv-hero-grid"></div>
    <div class="sv-orb sv-orb-1"></div>
    <div class="sv-orb sv-orb-2"></div>
    <div class="sv-orb sv-orb-3"></div>
    <div class="sv-particles">
        <div class="sv-particle" style="left:7%;  --d:9s;  --dl:0s"></div>
        <div class="sv-particle" style="left:19%; --d:12s; --dl:-3s"></div>
        <div class="sv-particle" style="left:34%; --d:8s;  --dl:-6s"></div>
        <div class="sv-particle" style="left:53%; --d:11s; --dl:-1s"></div>
        <div class="sv-particle" style="left:69%; --d:10s; --dl:-5s"></div>
        <div class="sv-particle" style="left:84%; --d:7s;  --dl:-9s"></div>
        <div class="sv-particle" style="left:93%; --d:13s; --dl:-2s"></div>
    </div>
    <div class="sv-hero-inner">
        <div class="sv-badge"><span class="sv-badge-dot"></span> <?php echo t('sv_badge'); ?></div>
        <h1 class="sv-hero-title"><?php echo t('sv_hero_title1'); ?><br><span class="sv-gold"><?php echo t('sv_hero_title2'); ?></span></h1>
        <p class="sv-hero-sub"><?php echo t('sv_hero_sub'); ?></p>
        <div class="sv-trust">
            <span class="sv-trust-pill"><i class="fas fa-shield-alt"></i> <?php echo t('sv_trust_1'); ?></span>
            <span class="sv-trust-pill"><i class="fas fa-globe"></i> <?php echo t('sv_trust_2'); ?></span>
            <span class="sv-trust-pill"><i class="fas fa-user-check"></i> <?php echo t('sv_trust_3'); ?></span>
            <span class="sv-trust-pill"><i class="fas fa-star"></i> <?php echo t('sv_trust_4'); ?></span>
        </div>
        <div class="sv-hero-stats">
            <div class="sv-h-stat"><strong>9+</strong><span><?php echo t('sv_stat_platforms'); ?></span></div>
            <div class="sv-h-stat"><strong>100%</strong><span><?php echo t('sv_stat_success'); ?></span></div>
            <div class="sv-h-stat"><strong>24/7</strong><span><?php echo t('sv_stat_support'); ?></span></div>
        </div>
    </div>
</section>

<!-- ══ HOW IT WORKS ═══════════════════════════════════════════════════ -->
<section class="sv-how">
    <div class="sv-how-inner">
        <div class="sv-sec">
            <h2><?php echo t('sv_hiw_title'); ?></h2>
            <p><?php echo t('sv_hiw_sub'); ?></p>
            <div class="sv-sec-line"></div>
        </div>
        <div class="sv-steps">
            <div class="sv-step">
                <div class="sv-step-num">01</div>
                <h4><?php echo t('sv_step1_title'); ?></h4>
                <p><?php echo t('sv_step1_desc'); ?></p>
            </div>
            <div class="sv-step">
                <div class="sv-step-num">02</div>
                <h4><?php echo t('sv_step2_title'); ?></h4>
                <p><?php echo t('sv_step2_desc'); ?></p>
            </div>
            <div class="sv-step">
                <div class="sv-step-num">03</div>
                <h4><?php echo t('sv_step3_title'); ?></h4>
                <p><?php echo t('sv_step3_desc'); ?></p>
            </div>
            <div class="sv-step">
                <div class="sv-step-num">04</div>
                <h4><?php echo t('sv_step4_title'); ?></h4>
                <p><?php echo t('sv_step4_desc'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ══ BROKER CAROUSEL ════════════════════════════════════════════════ -->
<section class="sv-brokers">
    <div class="sv-brokers-inner">
        <div class="sv-sec" style="margin-bottom:44px;">
            <h2>Supported Brokers & Platforms</h2>
            <p>Use the arrows to browse — click a card to view the setup guide</p>
            <div class="sv-sec-line"></div>
        </div>

        <?php
        $brokers = [
            ["key"=>"wise",         "logo"=>"wise.svg",         "name"=>"Wise",                 "type"=>"Money Transfer"],
            ["key"=>"etoro",        "logo"=>"etoro.svg",        "name"=>"eToro",                "type"=>"Stocks · Crypto · ETFs"],
            ["key"=>"xtb",          "logo"=>"xtb.png",          "name"=>"XTB",                  "type"=>"CFDs · Forex · Stocks"],
            ["key"=>"moomoo",       "logo"=>"moomoo.png",       "name"=>"Moomoo",               "type"=>"Stocks · Options · ETFs"],
            ["key"=>"tiger",        "logo"=>"tiger.png",        "name"=>"Tiger Brokers",        "type"=>"US · HK · SG Markets"],
            ["key"=>"ibkr",         "logo"=>"ibkr.png",         "name"=>"Interactive Brokers",  "type"=>"Global Multi-Asset"],
            ["key"=>"schwab",       "logo"=>"schwab.svg",       "name"=>"Charles Schwab",       "type"=>"US Stocks · ETFs · Bonds"],
            ["key"=>"easyequities", "logo"=>"easyequities.png", "name"=>"EasyEquities",         "type"=>"Fractional Shares"],
            ["key"=>"longbridge",   "logo"=>"longbridge.webp",  "name"=>"Longbridge",           "type"=>"HK · US · SG Markets"],
        ];
        $total = count($brokers);
        ?>

        <div class="sv-carousel">
            <button class="sv-car-arrow disabled" id="carPrev" onclick="carMove(-1)" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="sv-carousel-track-wrap">
                <div class="sv-carousel-track" id="carTrack">
                <?php foreach ($brokers as $i => $b): ?>
                <div class="sv-card" data-broker="<?= $b['key']; ?>">
                    <div class="sv-card-inner">
                        <div class="sv-card-logo-box">
                            <img src="assets/logos/<?= htmlspecialchars($b['logo']); ?>" alt="<?= htmlspecialchars($b['name']); ?>">
                        </div>
                        <div class="sv-card-info">
                            <div class="sv-card-name"><?= htmlspecialchars($b['name']); ?></div>
                            <div class="sv-card-type"><i class="fas fa-circle"></i><?= htmlspecialchars($b['type']); ?></div>
                        </div>
                    </div>
                    <div class="sv-card-actions">
                        <button class="sv-card-btn sv-btn-details"
                            data-broker="<?= $b['key']; ?>"
                            onclick="openLightbox('<?= $b['key']; ?>', '<?= htmlspecialchars($b['name']); ?>', 'assets/logos/<?= htmlspecialchars($b['logo']); ?>')">
                            <i class="fas fa-images"></i> View Setup Guide
                        </button>
                        <a href="contact.php" class="sv-card-btn sv-btn-service">
                            <i class="fas fa-rocket"></i> Get Service
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            <button class="sv-car-arrow" id="carNext" onclick="carMove(1)" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="sv-car-nav">
            <div class="sv-car-dots" id="carDots">
                <?php for ($i = 0; $i < $total; $i++): ?>
                <div class="sv-car-dot <?= $i === 0 ? 'active' : ''; ?>" onclick="carGoTo(<?= $i; ?>)"></div>
                <?php endfor; ?>
            </div>
            <div class="sv-car-counter"><span id="carCurrent">1</span> of <?= $total; ?></div>
        </div>
    </div>
</section>

<!-- ══ BROKER RANKINGS ════════════════════════════════════════════════ -->
<section class="sv-rankings">
    <div class="sv-rankings-inner">
        <div class="sv-sec" style="margin-bottom:44px;">
            <h2>Broker Rankings 2025</h2>
            <p>Our expert rating of the top platforms — scored on fees, features, access & support</p>
            <div class="sv-sec-line"></div>
        </div>

        <div class="sv-rank-list">
            <?php
            $rankings = [
                ["key"=>"ibkr",         "logo"=>"ibkr.png",         "name"=>"Interactive Brokers", "cat"=>"Global Multi-Asset",     "score"=>9.4, "label"=>"Best Overall",   "lclass"=>"best-overall",  "badge"=>"gold",   "n"=>1],
                ["key"=>"tiger",         "logo"=>"tiger.png",       "name"=>"Tiger Brokers",       "cat"=>"US · HK · SG Markets",   "score"=>8.9, "label"=>"Best for Asia",  "lclass"=>"best-asia",    "badge"=>"silver", "n"=>2],
                ["key"=>"moomoo",        "logo"=>"moomoo.png",      "name"=>"Moomoo",              "cat"=>"Stocks · Options · ETFs", "score"=>8.7, "label"=>"Best Research",  "lclass"=>"best-research","badge"=>"bronze", "n"=>3],
                ["key"=>"etoro",         "logo"=>"etoro.svg",       "name"=>"eToro",               "cat"=>"Stocks · Crypto · ETFs", "score"=>8.4, "label"=>"Best Social",    "lclass"=>"best-social",  "badge"=>"default","n"=>4],
                ["key"=>"schwab",        "logo"=>"schwab.svg",      "name"=>"Charles Schwab",      "cat"=>"US Stocks · ETFs",       "score"=>8.2, "label"=>"Best US Broker", "lclass"=>"best-us",      "badge"=>"default","n"=>5],
                ["key"=>"longbridge",    "logo"=>"longbridge.webp", "name"=>"Longbridge",          "cat"=>"HK · US · SG Markets",   "score"=>8.0, "label"=>"Best HK Access", "lclass"=>"best-asia",    "badge"=>"default","n"=>6],
                ["key"=>"xtb",           "logo"=>"xtb.png",         "name"=>"XTB",                 "cat"=>"CFDs · Forex · Stocks",  "score"=>7.8, "label"=>"Best CFD/Forex", "lclass"=>"best-cfd",     "badge"=>"default","n"=>7],
                ["key"=>"easyequities",  "logo"=>"easyequities.png","name"=>"EasyEquities",        "cat"=>"Fractional Shares",      "score"=>7.5, "label"=>"Best Fractional","lclass"=>"default",      "badge"=>"default","n"=>8],
                ["key"=>"wise",          "logo"=>"wise.svg",        "name"=>"Wise",                "cat"=>"Money Transfer",         "score"=>7.3, "label"=>"Best Transfer",  "lclass"=>"best-transfer","badge"=>"default","n"=>9],
            ];
            foreach ($rankings as $idx => $r):
            $pct = round(($r['score']/10)*100);
            ?>
            <div class="sv-rank-row" data-delay="<?= $idx * 70; ?>">
                <div class="sv-rank-badge <?= $r['badge']; ?>"><?= $r['n']; ?></div>
                <div class="sv-rank-logo">
                    <img src="assets/logos/<?= htmlspecialchars($r['logo']); ?>" alt="<?= htmlspecialchars($r['name']); ?>">
                </div>
                <div class="sv-rank-info">
                    <div class="sv-rank-name"><?= htmlspecialchars($r['name']); ?></div>
                    <div class="sv-rank-cat"><?= htmlspecialchars($r['cat']); ?></div>
                </div>
                <div class="sv-rank-score">
                    <div class="sv-rank-score-num"><?= number_format($r['score'],1); ?></div>
                    <div class="sv-rank-bar-wrap">
                        <div class="sv-rank-bar" style="--w:<?= $pct; ?>%"></div>
                    </div>
                </div>
                <div class="sv-rank-label <?= $r['lclass']; ?>"><?= htmlspecialchars($r['label']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ COUNTRY AVAILABILITY ══════════════════════════════════════════ -->
<section class="sv-countries">
    <div class="sv-countries-inner">
        <div class="sv-sec" style="margin-bottom:36px;">
            <h2>Availability by Country</h2>
            <p>Check which brokers you can access based on your country of residence</p>
            <div class="sv-sec-line"></div>
        </div>

        <!-- Filter tabs -->
        <div class="sv-ct-filters">
            <div class="sv-ct-filter active" onclick="ctFilter('all')">All Countries</div>
            <div class="sv-ct-filter" onclick="ctFilter('mm')"><span>🇲🇲</span> Myanmar</div>
            <div class="sv-ct-filter" onclick="ctFilter('sg')"><span>🇸🇬</span> Singapore</div>
            <div class="sv-ct-filter" onclick="ctFilter('th')"><span>🇹🇭</span> Thailand</div>
            <div class="sv-ct-filter" onclick="ctFilter('my')"><span>🇲🇾</span> Malaysia</div>
            <div class="sv-ct-filter" onclick="ctFilter('ph')"><span>🇵🇭</span> Philippines</div>
            <div class="sv-ct-filter" onclick="ctFilter('id')"><span>🇮🇩</span> Indonesia</div>
            <div class="sv-ct-filter" onclick="ctFilter('gb')"><span>🇬🇧</span> United Kingdom</div>
            <div class="sv-ct-filter" onclick="ctFilter('us')"><span>🇺🇸</span> USA</div>
            <div class="sv-ct-filter" onclick="ctFilter('au')"><span>🇦🇺</span> Australia</div>
        </div>

        <?php
        /*
         * availability[broker][country] = 'yes' | 'no' | 'partial'
         * partial = available with restrictions or via our service setup
         */
        $countries = [
            "mm" => ["flag"=>"🇲🇲","label"=>"Myanmar"],
            "sg" => ["flag"=>"🇸🇬","label"=>"Singapore"],
            "th" => ["flag"=>"🇹🇭","label"=>"Thailand"],
            "my" => ["flag"=>"🇲🇾","label"=>"Malaysia"],
            "ph" => ["flag"=>"🇵🇭","label"=>"Philippines"],
            "id" => ["flag"=>"🇮🇩","label"=>"Indonesia"],
            "gb" => ["flag"=>"🇬🇧","label"=>"UK"],
            "us" => ["flag"=>"🇺🇸","label"=>"USA"],
            "au" => ["flag"=>"🇦🇺","label"=>"Australia"],
        ];
        $avail = [
            "wise"         => ["mm"=>"yes","sg"=>"yes","th"=>"yes","my"=>"yes","ph"=>"yes","id"=>"yes","gb"=>"yes","us"=>"yes","au"=>"yes"],
            "etoro"        => ["mm"=>"no", "sg"=>"yes","th"=>"no", "my"=>"no", "ph"=>"partial","id"=>"no","gb"=>"yes","us"=>"partial","au"=>"yes"],
            "xtb"          => ["mm"=>"no", "sg"=>"no", "th"=>"no", "my"=>"no", "ph"=>"no","id"=>"no","gb"=>"yes","us"=>"no","au"=>"no"],
            "moomoo"       => ["mm"=>"no", "sg"=>"yes","th"=>"no", "my"=>"partial","ph"=>"no","id"=>"no","gb"=>"no","us"=>"yes","au"=>"yes"],
            "tiger"        => ["mm"=>"partial","sg"=>"yes","th"=>"no","my"=>"no","ph"=>"no","id"=>"no","gb"=>"no","us"=>"no","au"=>"yes"],
            "ibkr"         => ["mm"=>"partial","sg"=>"yes","th"=>"yes","my"=>"yes","ph"=>"yes","id"=>"yes","gb"=>"yes","us"=>"yes","au"=>"yes"],
            "schwab"       => ["mm"=>"no", "sg"=>"no", "th"=>"no", "my"=>"no", "ph"=>"no","id"=>"no","gb"=>"no","us"=>"yes","au"=>"no"],
            "easyequities" => ["mm"=>"no", "sg"=>"no", "th"=>"no", "my"=>"no", "ph"=>"no","id"=>"no","gb"=>"no","us"=>"no","au"=>"no"],
            "longbridge"   => ["mm"=>"partial","sg"=>"yes","th"=>"no","my"=>"no","ph"=>"no","id"=>"no","gb"=>"no","us"=>"no","au"=>"yes"],
        ];
        $brokerMeta = [
            "wise"         => ["logo"=>"wise.svg",         "name"=>"Wise"],
            "etoro"        => ["logo"=>"etoro.svg",        "name"=>"eToro"],
            "xtb"          => ["logo"=>"xtb.png",          "name"=>"XTB"],
            "moomoo"       => ["logo"=>"moomoo.png",       "name"=>"Moomoo"],
            "tiger"        => ["logo"=>"tiger.png",        "name"=>"Tiger Brokers"],
            "ibkr"         => ["logo"=>"ibkr.png",         "name"=>"Interactive Brokers"],
            "schwab"       => ["logo"=>"schwab.svg",       "name"=>"Charles Schwab"],
            "easyequities" => ["logo"=>"easyequities.png", "name"=>"EasyEquities"],
            "longbridge"   => ["logo"=>"longbridge.webp",  "name"=>"Longbridge"],
        ];
        ?>

        <div class="sv-ct-wrap">
            <table class="sv-ct-table" id="ctTable">
                <thead>
                    <tr>
                        <th>Broker</th>
                        <?php foreach ($countries as $code => $c): ?>
                        <th data-col="<?= $code; ?>"><?= $c['flag']; ?> <?= $c['label']; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($brokerMeta as $key => $bm):
                    $row = $avail[$key];
                ?>
                <tr data-broker="<?= $key; ?>">
                    <td>
                        <div class="sv-ct-broker">
                            <div class="sv-ct-b-logo">
                                <img src="assets/logos/<?= htmlspecialchars($bm['logo']); ?>" alt="<?= htmlspecialchars($bm['name']); ?>">
                            </div>
                            <span class="sv-ct-b-name"><?= htmlspecialchars($bm['name']); ?></span>
                        </div>
                    </td>
                    <?php foreach ($countries as $code => $c): ?>
                    <td data-col="<?= $code; ?>">
                        <?php if ($row[$code] === 'yes'): ?>
                            <span class="sv-ct-yes" title="Available"><i class="fas fa-check"></i></span>
                        <?php elseif ($row[$code] === 'partial'): ?>
                            <span class="sv-ct-partial" title="Available via our service"><i class="fas fa-star-half-alt"></i></span>
                        <?php else: ?>
                            <span class="sv-ct-no" title="Not available"><i class="fas fa-times"></i></span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="sv-ct-legend">
            <div class="sv-ct-legend-item"><span class="sv-ct-yes"><i class="fas fa-check"></i></span> Available directly</div>
            <div class="sv-ct-legend-item"><span class="sv-ct-partial"><i class="fas fa-star-half-alt"></i></span> Available via our service</div>
            <div class="sv-ct-legend-item"><span class="sv-ct-no"><i class="fas fa-times"></i></span> Not available</div>
        </div>
    </div>
</section>

</div><!-- /.sv-page -->

<!-- ══ LIGHTBOX ══════════════════════════════════════════════════════ -->
<div class="sv-lb" id="svLightbox" role="dialog" aria-modal="true">
    <div class="sv-lb-backdrop" onclick="closeLightbox()"></div>
    <div class="sv-lb-panel">
        <div class="sv-lb-topbar"></div>

        <!-- Header -->
        <div class="sv-lb-header">
            <div class="sv-lb-broker-wrap">
                <div class="sv-lb-broker-logo">
                    <img id="lbBrokerLogo" src="" alt="">
                </div>
                <div>
                    <p class="sv-lb-title" id="lbTitle"></p>
                    <p class="sv-lb-subtitle">Step-by-step Setup Guide</p>
                </div>
            </div>
            <div class="sv-lb-header-right">
                <div class="sv-lb-counter">Step <span id="lbCurrent">1</span> of <span id="lbTotal">3</span></div>
                <button class="sv-lb-close" onclick="closeLightbox()" aria-label="Close"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <!-- Body -->
        <div class="sv-lb-body">

            <!-- Main image stage -->
            <div class="sv-lb-stage" id="lbStage">
                <button class="sv-lb-arrow sv-lb-arrow-prev" id="lbPrev" onclick="lbNavigate(-1)" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
                <img class="sv-lb-img" id="lbMainImg" src="" alt="">
                <button class="sv-lb-arrow sv-lb-arrow-next" id="lbNext" onclick="lbNavigate(1)" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
            </div>

            <!-- Sidebar: thumbnails + CTA -->
            <div class="sv-lb-sidebar">
                <div class="sv-lb-sidebar-label">All Steps</div>
                <div class="sv-lb-thumbs" id="lbThumbs"></div>
                <div class="sv-lb-sidebar-footer">
                    <div class="sv-lb-hint">
                        <kbd>←</kbd><kbd>→</kbd> navigate &nbsp; <kbd>Esc</kbd> close
                    </div>
                    <a href="contact.php" class="sv-lb-cta">
                        <i class="fas fa-rocket"></i> Get This Service
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
/* ══ Broker image data ══════════════════════════════════════════════ */
const brokerCards = {
    xtb:          ["assets/broker-cards/xtb/card-1.jpg","assets/broker-cards/xtb/card-2.jpg","assets/broker-cards/xtb/card-3.jpg"],
    etoro:        ["assets/broker-cards/etoro/card-1.jpg","assets/broker-cards/etoro/card-2.jpg","assets/broker-cards/etoro/card-3.jpg"],
    schwab:       ["assets/broker-cards/schwab/card-1.jpg","assets/broker-cards/schwab/card-2.jpg","assets/broker-cards/schwab/card-3.jpg"],
    ibkr:         ["assets/broker-cards/ibkr/card-1.jpg","assets/broker-cards/ibkr/card-2.jpg","assets/broker-cards/ibkr/card-3.jpg"],
    tiger:        ["assets/broker-cards/tiger/card-1.jpg","assets/broker-cards/tiger/card-2.jpg","assets/broker-cards/tiger/card-3.jpg"],
    moomoo:       ["assets/broker-cards/moomoo/card-1.jpg","assets/broker-cards/moomoo/card-2.jpg","assets/broker-cards/moomoo/card-3.jpg"],
    easyequities: ["assets/broker-cards/easyequities/card-1.jpg","assets/broker-cards/easyequities/card-2.jpg","assets/broker-cards/easyequities/card-3.jpg"],
    longbridge:   ["assets/broker-cards/longbridge/card-1.jpg","assets/broker-cards/longbridge/card-2.jpg","assets/broker-cards/longbridge/card-3.jpg"]
};

let lbImages = [];
let lbIndex  = 0;

/* ── Open lightbox ── */
function openLightbox(broker, name, logoSrc) {
    const lb    = document.getElementById('svLightbox');
    const stage = document.getElementById('lbStage');

    document.getElementById('lbTitle').textContent = name;
    document.getElementById('lbBrokerLogo').src    = logoSrc;
    document.getElementById('lbBrokerLogo').alt    = name;

    lbImages = brokerCards[broker] || [];
    lbIndex  = 0;

    /* Remove previous no-data if any */
    const nd = document.getElementById('lbNoData');
    if (nd) nd.remove();

    if (!lbImages.length) {
        document.getElementById('lbMainImg').style.display = 'none';
        document.getElementById('lbPrev').style.display    = 'none';
        document.getElementById('lbNext').style.display    = 'none';
        document.getElementById('lbThumbs').innerHTML      = '';
        document.getElementById('lbCurrent').textContent   = '0';
        document.getElementById('lbTotal').textContent     = '0';
        stage.insertAdjacentHTML('beforeend',
            `<div class="sv-lb-nodata" id="lbNoData">
                <i class="fas fa-clock"></i>
                <h4>Coming Soon</h4>
                <p>Setup guide for this platform will be available shortly.</p>
             </div>`);
    } else {
        document.getElementById('lbMainImg').style.display = 'block';
        document.getElementById('lbPrev').style.display    = 'flex';
        document.getElementById('lbNext').style.display    = 'flex';
        document.getElementById('lbTotal').textContent     = lbImages.length;
        buildThumbs();
        lbShow(0);
    }

    lb.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/* ── Build vertical thumbnail strip ── */
function buildThumbs() {
    const strip = document.getElementById('lbThumbs');
    strip.innerHTML = '';
    lbImages.forEach((src, i) => {
        const t = document.createElement('div');
        t.className = 'sv-lb-thumb' + (i === 0 ? ' active' : '');
        t.innerHTML = `<img src="${src}" alt="Step ${i+1}" loading="lazy">`;
        t.addEventListener('click', () => lbShow(i));
        strip.appendChild(t);
    });
}

/* ── Show image at index ── */
function lbShow(i) {
    if (i < 0 || i >= lbImages.length) return;
    lbIndex = i;
    const img = document.getElementById('lbMainImg');
    img.style.animation = 'none';
    img.offsetHeight;
    img.style.animation = '';
    img.src = lbImages[i];
    img.alt = `Setup step ${i + 1}`;
    document.getElementById('lbCurrent').textContent = i + 1;
    document.getElementById('lbPrev').classList.toggle('disabled', i === 0);
    document.getElementById('lbNext').classList.toggle('disabled', i === lbImages.length - 1);
    document.querySelectorAll('.sv-lb-thumb').forEach((t, ti) => t.classList.toggle('active', ti === i));
}

function lbNavigate(dir) { lbShow(lbIndex + dir); }

function closeLightbox() {
    document.getElementById('svLightbox').classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (!document.getElementById('svLightbox').classList.contains('active')) return;
    if (e.key === 'ArrowLeft')  lbNavigate(-1);
    if (e.key === 'ArrowRight') lbNavigate(1);
    if (e.key === 'Escape')     closeLightbox();
});

/* ── Hide "View Setup Guide" if no images ── */
document.querySelectorAll('.sv-btn-details').forEach(btn => {
    if (!brokerCards[btn.dataset.broker]) btn.setAttribute('data-hidden','true');
});

/* ── Broker Carousel ── */
(function() {
    const track   = document.getElementById('carTrack');
    const dots    = document.querySelectorAll('.sv-car-dot');
    const prev    = document.getElementById('carPrev');
    const next    = document.getElementById('carNext');
    const counter = document.getElementById('carCurrent');
    const total   = dots.length;
    let cur = 0;

    function goTo(i) {
        cur = Math.max(0, Math.min(i, total - 1));
        track.style.transform = `translateX(-${cur * 100}%)`;
        dots.forEach((d, di) => d.classList.toggle('active', di === cur));
        counter.textContent = cur + 1;
        prev.classList.toggle('disabled', cur === 0);
        next.classList.toggle('disabled', cur === total - 1);
    }

    window.carMove = dir => goTo(cur + dir);
    window.carGoTo = i   => goTo(i);

    let startX = 0;
    track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, {passive:true});
    track.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) goTo(cur + (dx < 0 ? 1 : -1));
    });
}());

/* ── Scroll reveal (rankings + country table) ── */
(function() {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const el    = e.target;
            const delay = parseInt(el.dataset.delay) || 0;
            setTimeout(() => {
                el.classList.add('visible');
                /* Animate score bar if it has one */
                const bar = el.querySelector('.sv-rank-bar');
                if (bar) bar.style.width = bar.style.getPropertyValue('--w') || getComputedStyle(bar).getPropertyValue('--w');
            }, delay);
            obs.unobserve(el);
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.sv-rank-row, .sv-ct-table tbody tr').forEach((el, i) => {
        if (!el.dataset.delay) el.dataset.delay = i * 55;
        obs.observe(el);
    });
}());

/* ── Country filter ── */
function ctFilter(col) {
    /* Update active tab */
    document.querySelectorAll('.sv-ct-filter').forEach(f => f.classList.remove('active'));
    event.currentTarget.classList.add('active');

    const table = document.getElementById('ctTable');

    if (col === 'all') {
        table.querySelectorAll('th[data-col], td[data-col]').forEach(el => el.style.display = '');
    } else {
        table.querySelectorAll('th[data-col], td[data-col]').forEach(el => {
            el.style.display = el.dataset.col === col ? '' : 'none';
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
