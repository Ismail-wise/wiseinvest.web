<?php
session_start();
include 'includes/header.php';
?>

<style>
/* ══════════════════════════════════════════════════════════
   HOME PAGE — PREMIUM REDESIGN v3
   ══════════════════════════════════════════════════════════ */

/* ── Keyframes ─────────────────────────────────────────── */
@keyframes hmFadeUp      { from{opacity:0;transform:translateY(40px)} to{opacity:1;transform:none} }
@keyframes hmFadeLeft    { from{opacity:0;transform:translateX(-40px)} to{opacity:1;transform:none} }
@keyframes hmFadeRight   { from{opacity:0;transform:translateX(40px)} to{opacity:1;transform:none} }
@keyframes hmFadeIn      { from{opacity:0} to{opacity:1} }
@keyframes hmShimmer     { 0%{background-position:-300% center} 100%{background-position:300% center} }
@keyframes hmOrb1        { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.18) translate(28px,-22px)} }
@keyframes hmOrb2        { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.12) translate(-20px,24px)} }
@keyframes hmOrb3        { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.1) translate(14px,18px)} }
@keyframes hmOrb4        { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.08) translate(-12px,-16px)} }
@keyframes hmFloat       { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-14px) rotate(2deg)} }
@keyframes hmPulse       { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.5)} 70%{box-shadow:0 0 0 18px rgba(212,175,55,0)} }
@keyframes hmLine        { from{width:0} to{width:60px} }
@keyframes hmBeam        { 0%{transform:translateX(-100%) skewX(-15deg)} 100%{transform:translateX(400%) skewX(-15deg)} }
@keyframes hmParticle    { 0%{transform:translateY(0) scale(1);opacity:.8} 100%{transform:translateY(-120vh) scale(0);opacity:0} }
@keyframes hmParticleFade{ 0%,100%{opacity:.15} 50%{opacity:.5} }
@keyframes hmSpin        { to{transform:rotate(360deg)} }
@keyframes hmBlink       { 0%,100%{opacity:1} 50%{opacity:0} }
@keyframes hmBorderGlow  { 0%,100%{border-color:rgba(212,175,55,.12)} 50%{border-color:rgba(212,175,55,.45)} }
@keyframes hmScaleIn     { from{opacity:0;transform:scale(.85)} to{opacity:1;transform:scale(1)} }
@keyframes hmStatsCount  { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:none} }
@keyframes hmProgressFill{ from{width:0} to{width:100%} }
@keyframes hmRipple      { 0%{transform:scale(1);opacity:.6} 100%{transform:scale(2.8);opacity:0} }
@keyframes hmScrollBounce{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(8px)} }
@keyframes hmGradMove    { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
@keyframes hmCardFloat   { 0%,100%{transform:translateY(0) rotateX(0deg)} 50%{transform:translateY(-8px) rotateX(2deg)} }
@keyframes hmGlow        { 0%,100%{filter:drop-shadow(0 0 8px rgba(212,175,55,.3))} 50%{filter:drop-shadow(0 0 22px rgba(212,175,55,.7))} }
@keyframes hmTimelineFill{ from{height:0;opacity:0} to{height:100%;opacity:1} }
@keyframes hmFeatureIn   { from{opacity:0;transform:translateY(30px) scale(.96)} to{opacity:1;transform:none} }
@keyframes hmTypewriter  { from{width:0} to{width:100%} }
@keyframes hmWaveText    { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }

/* ── Base ───────────────────────────────────────────────── */
.hm-page { background:#020617; overflow-x:hidden; }

/* ── Shared section header ──────────────────────────────── */
.hm-sec-head { text-align:center; margin-bottom:60px; }
.hm-sec-label {
    display:inline-flex; align-items:center; gap:8px;
    padding:6px 18px; border-radius:999px;
    border:1px solid rgba(212,175,55,.35);
    background:rgba(212,175,55,.08);
    color:#d4af37; font-size:.7rem; font-weight:800;
    letter-spacing:.15em; text-transform:uppercase;
    margin-bottom:20px;
    box-shadow:0 0 20px rgba(212,175,55,.08);
}
.hm-sec-title {
    font-family:'Playfair Display', serif;
    font-size:clamp(2rem,3.8vw,3rem);
    font-weight:700; color:#f1f5f9;
    margin:0 0 14px; line-height:1.15;
}
.hm-sec-title span {
    background:linear-gradient(135deg,#c8971f 0%,#d4af37 25%,#f5d679 50%,#d4af37 75%,#c8971f 100%);
    background-size:300% auto;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    background-clip:text;
    animation:hmShimmer 4s linear infinite;
}
.hm-sec-sub { color:#64748b; font-size:.97rem; max-width:520px; margin:0 auto; line-height:1.75; }
.hm-sec-rule {
    width:0; height:3px; border-radius:3px;
    background:linear-gradient(90deg,transparent,#d4af37,#f5d679,#d4af37,transparent);
    margin:18px auto 0;
    animation:hmLine 1.2s .3s cubic-bezier(.4,0,.2,1) forwards;
}

/* scroll-reveal base */
.hm-reveal { opacity:0; transform:translateY(36px); transition:opacity .7s ease, transform .7s ease; }
.hm-reveal.hm-visible { opacity:1; transform:none; }
.hm-reveal-l { opacity:0; transform:translateX(-36px); transition:opacity .7s ease, transform .7s ease; }
.hm-reveal-l.hm-visible { opacity:1; transform:none; }
.hm-reveal-r { opacity:0; transform:translateX(36px); transition:opacity .7s ease, transform .7s ease; }
.hm-reveal-r.hm-visible { opacity:1; transform:none; }
.hm-reveal-scale { opacity:0; transform:scale(.92); transition:opacity .65s ease, transform .65s cubic-bezier(.34,1.56,.64,1); }
.hm-reveal-scale.hm-visible { opacity:1; transform:scale(1); }

/* ════════════════════════════════════════════════════════
   SECTION 1 — HERO
   ════════════════════════════════════════════════════════ */
.hm-hero {
    position:relative; background:#020617;
    padding:110px 0 0; overflow:hidden;
    min-height:100vh;
    display:flex; flex-direction:column; justify-content:center;
}

/* Animated gradient bg */
.hm-hero::before {
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(ellipse 80% 60% at 65% 40%, rgba(212,175,55,.07) 0%, transparent 60%),
               radial-gradient(ellipse 60% 50% at 15% 70%, rgba(99,102,241,.05) 0%, transparent 60%);
    animation:hmGradMove 12s ease infinite;
    background-size:200% 200%;
    pointer-events:none;
}

/* Grid */
.hm-grid {
    position:absolute; inset:0;
    background-image:
        linear-gradient(rgba(212,175,55,.038) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212,175,55,.038) 1px, transparent 1px);
    background-size:64px 64px;
    mask-image:radial-gradient(ellipse 85% 75% at 50% 50%, black 40%, transparent 100%);
}

/* Orbs */
.hm-orb { position:absolute; border-radius:50%; filter:blur(90px); pointer-events:none; }
.hm-orb-1 { width:800px;height:800px; background:radial-gradient(circle,rgba(212,175,55,.13) 0%,transparent 70%); top:-300px;right:-200px; animation:hmOrb1 12s ease-in-out infinite; }
.hm-orb-2 { width:600px;height:600px; background:radial-gradient(circle,rgba(99,102,241,.08) 0%,transparent 70%); bottom:-100px;left:-160px; animation:hmOrb2 14s ease-in-out infinite; }
.hm-orb-3 { width:380px;height:380px; background:radial-gradient(circle,rgba(212,175,55,.07) 0%,transparent 70%); top:35%;left:42%; animation:hmOrb3 10s ease-in-out infinite; }
.hm-orb-4 { width:260px;height:260px; background:radial-gradient(circle,rgba(56,189,248,.06) 0%,transparent 70%); bottom:20%;right:8%; animation:hmOrb4 9s ease-in-out infinite; }

/* Floating particles */
.hm-particles { position:absolute; inset:0; pointer-events:none; overflow:hidden; }
.hm-particle {
    position:absolute; border-radius:50%;
    background:rgba(212,175,55,.6);
    animation:hmParticle var(--d,12s) var(--dl,0s) linear infinite;
}

/* Inner layout */
.hm-hero-inner {
    position:relative; z-index:2;
    max-width:1380px; margin:0 auto;
    padding:0 56px 110px;
    display:grid; grid-template-columns:1fr 500px; gap:80px;
    align-items:center;
}

/* ── Left column ── */
.hm-hero-left { animation:hmFadeUp .9s .1s ease both; }

.hm-hero-badge {
    display:inline-flex; align-items:center; gap:10px;
    background:rgba(212,175,55,.09);
    border:1px solid rgba(212,175,55,.35);
    color:#d4af37; padding:8px 22px; border-radius:999px;
    font-size:.7rem; font-weight:900; letter-spacing:.18em;
    text-transform:uppercase; margin-bottom:28px;
    position:relative; overflow:hidden;
    box-shadow:0 0 24px rgba(212,175,55,.1);
}
.hm-hero-badge::before {
    content:'';
    position:absolute; top:0; left:-60%; width:40%; height:100%;
    background:linear-gradient(90deg,transparent,rgba(212,175,55,.25),transparent);
    animation:hmBeam 3.5s 1s ease-in-out infinite;
}
.hm-badge-dot {
    width:7px; height:7px; border-radius:50%;
    background:#22c55e;
    box-shadow:0 0 8px rgba(34,197,94,.9);
    animation:hmPulse 2s ease-in-out infinite;
    flex-shrink:0;
}

.hm-hero-title {
    font-family:'Playfair Display', serif;
    font-size:clamp(2.5rem, 5.5vw, 4.2rem);
    font-weight:700; color:#fff;
    margin:0 0 24px; line-height:1.08; letter-spacing:-.8px;
}
.hm-hero-gold {
    background:linear-gradient(135deg,#c8971f 0%,#d4af37 20%,#f5d679 40%,#facc15 60%,#d4af37 80%,#c8971f 100%);
    background-size:300% auto;
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    background-clip:text;
    animation:hmShimmer 4s linear infinite;
    display:block;
}

.hm-hero-desc {
    color:#64748b; font-size:1.08rem; line-height:1.85;
    max-width:500px; margin:0 0 44px;
}

/* CTA buttons */
.hm-hero-ctas { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:52px; }

.hm-btn-gold {
    display:inline-flex; align-items:center; gap:10px;
    background:linear-gradient(135deg,#b8942a,#d4af37,#facc15,#d4af37,#b8942a);
    background-size:300% auto;
    color:#020617; font-weight:900; font-size:.93rem;
    padding:16px 34px; border-radius:16px; text-decoration:none;
    box-shadow:0 8px 32px rgba(212,175,55,.4), 0 0 0 1px rgba(212,175,55,.2);
    transition:transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
    animation:hmShimmer 4s linear infinite;
    position:relative; overflow:hidden;
}
.hm-btn-gold::after {
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(to bottom, rgba(255,255,255,.18), transparent);
    border-radius:inherit;
}
.hm-btn-gold:hover { transform:translateY(-5px) scale(1.02); box-shadow:0 18px 50px rgba(212,175,55,.6), 0 0 0 1px rgba(212,175,55,.4); }
.hm-btn-gold i { position:relative; z-index:1; transition:transform .3s; }
.hm-btn-gold:hover i { transform:rotate(-8deg) scale(1.2); }

.hm-btn-ghost {
    display:inline-flex; align-items:center; gap:10px;
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.12);
    color:#94a3b8; font-weight:600; font-size:.93rem;
    padding:16px 30px; border-radius:16px; text-decoration:none;
    transition:all .3s;
    position:relative; overflow:hidden;
}
.hm-btn-ghost::before {
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(135deg, rgba(212,175,55,.06), rgba(212,175,55,.02));
    opacity:0; transition:opacity .3s;
}
.hm-btn-ghost:hover { background:rgba(255,255,255,.07); color:#f1f5f9; border-color:rgba(212,175,55,.3); transform:translateY(-3px); }
.hm-btn-ghost:hover::before { opacity:1; }

/* Trust badges */
.hm-trust { display:flex; gap:20px; flex-wrap:wrap; }
.hm-trust-item {
    display:flex; align-items:center; gap:8px;
    font-size:.8rem; color:#475569;
    transition:color .25s;
}
.hm-trust-item:hover { color:#94a3b8; }
.hm-trust-icon {
    width:22px; height:22px; border-radius:50%;
    background:rgba(34,197,94,.12);
    border:1px solid rgba(34,197,94,.3);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.hm-trust-icon i { color:#22c55e; font-size:.6rem; }

/* ── Right column — Gallery cards ── */
.hm-hero-right {
    display:flex; flex-direction:column; gap:20px;
    animation:hmFadeRight .9s .3s ease both;
    perspective:1000px;
}

.hm-gallery-card {
    position:relative; background:linear-gradient(155deg,#0d1a30 0%,#060f1f 100%);
    border:1px solid rgba(255,255,255,.07);
    border-radius:24px; overflow:hidden;
    text-decoration:none; display:block;
    transition:transform .45s cubic-bezier(.34,1.56,.64,1),
               border-color .3s, box-shadow .3s;
    animation:hmBorderGlow 5s ease-in-out infinite;
    transform-style:preserve-3d;
}
.hm-gallery-card::before {
    content:'';
    position:absolute; top:0; left:0; right:0; height:1px;
    background:linear-gradient(90deg,transparent,rgba(212,175,55,.4),transparent);
    opacity:0; transition:opacity .3s;
}
.hm-gallery-card:hover { transform:translateY(-8px) scale(1.015) rotateX(2deg); border-color:rgba(212,175,55,.4); box-shadow:0 32px 80px rgba(0,0,0,.6), 0 0 0 1px rgba(212,175,55,.18), 0 0 40px rgba(212,175,55,.08); }
.hm-gallery-card:hover::before { opacity:1; }

.hm-card-thumb { position:relative; height:175px; overflow:hidden; }
.hm-card-thumb img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .9s ease; }
.hm-card-thumb img.active { opacity:1; }
.hm-card-thumb-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(6,15,31,.9) 0%, rgba(6,15,31,.3) 50%, transparent 100%); }
.hm-card-thumb-icon {
    position:absolute; bottom:16px; right:16px;
    width:42px; height:42px; border-radius:50%;
    background:linear-gradient(135deg,#d4af37,#facc15);
    display:flex; align-items:center; justify-content:center;
    color:#020617; font-size:.85rem;
    box-shadow:0 6px 24px rgba(212,175,55,.5);
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
}
.hm-gallery-card:hover .hm-card-thumb-icon { transform:scale(1.2) rotate(8deg); box-shadow:0 10px 32px rgba(212,175,55,.7); }

.hm-card-body { padding:18px 22px 22px; display:flex; align-items:center; justify-content:space-between; }
.hm-card-info h3 { font-size:.97rem; font-weight:700; color:#f1f5f9; margin:0 0 4px; }
.hm-card-info p  { font-size:.75rem; color:#475569; margin:0; }
.hm-card-arrow {
    width:34px; height:34px; border-radius:10px;
    background:rgba(212,175,55,.08); border:1px solid rgba(212,175,55,.2);
    display:flex; align-items:center; justify-content:center;
    color:#d4af37; font-size:.78rem;
    transition:all .3s cubic-bezier(.34,1.56,.64,1);
}
.hm-gallery-card:hover .hm-card-arrow { background:rgba(212,175,55,.25); transform:translateX(4px) scale(1.1); border-color:rgba(212,175,55,.5); }

/* Scroll indicator */
.hm-scroll-indicator {
    position:absolute; bottom:32px; left:50%; transform:translateX(-50%);
    z-index:3; display:flex; flex-direction:column; align-items:center; gap:8px;
    color:#334155; font-size:.65rem; letter-spacing:.1em; text-transform:uppercase;
    opacity:.7;
}
.hm-scroll-arrow {
    width:36px; height:36px; border-radius:50%;
    border:1px solid rgba(212,175,55,.2);
    display:flex; align-items:center; justify-content:center;
    color:#d4af37; font-size:.75rem;
    animation:hmScrollBounce 2s ease-in-out infinite;
}

/* ── Stats strip ── */
.hm-stats-strip {
    position:relative; z-index:2;
    border-top:1px solid rgba(255,255,255,.05);
    background:rgba(5,14,30,.75);
    backdrop-filter:blur(20px);
    -webkit-backdrop-filter:blur(20px);
}
.hm-stats-inner {
    max-width:1380px; margin:0 auto;
    padding:32px 56px;
    display:grid; grid-template-columns:repeat(4,1fr); gap:0;
}
.hm-stat {
    display:flex; align-items:center; gap:18px;
    padding:0 36px 0 0;
    border-right:1px solid rgba(255,255,255,.06);
    opacity:0; transform:translateY(16px);
    transition:opacity .6s ease, transform .6s ease;
}
.hm-stat.hm-visible { opacity:1; transform:none; }
.hm-stat:last-child { border-right:none; padding-right:0; }

.hm-stat-icon {
    width:48px; height:48px; border-radius:14px; flex-shrink:0;
    background:rgba(212,175,55,.1);
    border:1px solid rgba(212,175,55,.2);
    display:flex; align-items:center; justify-content:center;
    color:#d4af37; font-size:1.1rem;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
}
.hm-stat:hover .hm-stat-icon { transform:scale(1.15) rotate(8deg); box-shadow:0 8px 24px rgba(212,175,55,.3); }

.hm-stat-num {
    font-size:1.7rem; font-weight:900; color:#f1f5f9; line-height:1;
    display:block; margin-bottom:3px;
    background:linear-gradient(135deg,#f1f5f9,#d4af37);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.hm-stat-label { font-size:.72rem; color:#475569; letter-spacing:.04em; }

/* ════════════════════════════════════════════════════════
   SECTION 2 — FEATURES
   ════════════════════════════════════════════════════════ */
.hm-features-sec {
    background:linear-gradient(180deg,#020617 0%,#030c1b 50%,#020617 100%);
    padding:110px 24px;
    position:relative; overflow:hidden;
    border-top:1px solid rgba(255,255,255,.04);
}
.hm-features-sec::before {
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(ellipse 70% 60% at 50% 50%, rgba(99,102,241,.04), transparent 70%);
    pointer-events:none;
}

.hm-features-grid {
    max-width:1200px; margin:0 auto;
    display:grid; grid-template-columns:repeat(3,1fr); gap:20px;
}

.hm-feat-card {
    position:relative;
    background:linear-gradient(155deg,rgba(15,26,48,.9) 0%,rgba(8,16,30,.95) 100%);
    border:1px solid rgba(255,255,255,.07); border-radius:24px;
    padding:36px 30px; overflow:hidden;
    transition:transform .4s cubic-bezier(.34,1.56,.64,1), border-color .3s, box-shadow .3s;
    cursor:default;
}
.hm-feat-card::before {
    content:'';
    position:absolute; top:0; left:0; right:0; height:2px;
    background:linear-gradient(90deg,transparent,var(--accent,#d4af37),transparent);
    opacity:0; transition:opacity .35s;
}
.hm-feat-card:hover { transform:translateY(-10px) scale(1.01); border-color:rgba(212,175,55,.25); box-shadow:0 30px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(212,175,55,.1); }
.hm-feat-card:hover::before { opacity:1; }

/* Animated background number */
.hm-feat-bg-num {
    position:absolute; bottom:-10px; right:12px;
    font-size:7rem; font-weight:900; line-height:1;
    opacity:.03; color:#fff; pointer-events:none; user-select:none;
    transition:opacity .3s;
}
.hm-feat-card:hover .hm-feat-bg-num { opacity:.06; }

.hm-feat-icon-wrap {
    width:60px; height:60px; border-radius:18px; margin-bottom:24px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; position:relative;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1);
}
.hm-feat-card:hover .hm-feat-icon-wrap { transform:scale(1.15) rotate(-6deg); }
.hm-feat-icon-wrap::after {
    content:'';
    position:absolute; inset:-4px; border-radius:22px;
    border:1px solid currentColor; opacity:.2;
    animation:hmRipple 2.5s ease-out infinite;
}

.hm-feat-card--gold   .hm-feat-icon-wrap { background:rgba(212,175,55,.12); border:1px solid rgba(212,175,55,.25); color:#d4af37; }
.hm-feat-card--blue   .hm-feat-icon-wrap { background:rgba(56,189,248,.1);  border:1px solid rgba(56,189,248,.22); color:#38bdf8; }
.hm-feat-card--purple .hm-feat-icon-wrap { background:rgba(167,139,250,.1); border:1px solid rgba(167,139,250,.22); color:#a78bfa; }
.hm-feat-card--green  .hm-feat-icon-wrap { background:rgba(34,197,94,.1);   border:1px solid rgba(34,197,94,.22);  color:#4ade80; }
.hm-feat-card--rose   .hm-feat-icon-wrap { background:rgba(251,113,133,.1); border:1px solid rgba(251,113,133,.22);color:#fb7185; }
.hm-feat-card--cyan   .hm-feat-icon-wrap { background:rgba(34,211,238,.1);  border:1px solid rgba(34,211,238,.22); color:#22d3ee; }

.hm-feat-title { font-size:1.05rem; font-weight:800; color:#f1f5f9; margin:0 0 10px; }
.hm-feat-desc  { font-size:.85rem; color:#475569; line-height:1.75; margin:0; }

/* Shine overlay on hover */
.hm-feat-card::after {
    content:'';
    position:absolute; top:0; left:-100%; width:50%; height:100%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.04),transparent);
    transform:skewX(-20deg);
    transition:left .6s ease;
}
.hm-feat-card:hover::after { left:160%; }

/* ════════════════════════════════════════════════════════
   SECTION 3 — HOW IT WORKS
   ════════════════════════════════════════════════════════ */
.hm-hiw-sec {
    background:#020617; padding:110px 24px;
    position:relative; overflow:hidden;
}
.hm-hiw-sec::before {
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(ellipse 80% 55% at 50% 0%, rgba(212,175,55,.06), transparent 65%);
    pointer-events:none;
}

.hm-hiw-grid {
    max-width:1200px; margin:0 auto;
    display:grid; grid-template-columns:repeat(4,1fr);
    gap:0; position:relative;
}

/* Animated connecting line */
.hm-hiw-line {
    position:absolute;
    top:52px; left:calc(12.5% + 30px); right:calc(12.5% + 30px);
    height:2px;
    background:rgba(255,255,255,.06);
    border-radius:2px;
    overflow:hidden;
}
.hm-hiw-line::after {
    content:'';
    position:absolute; inset:0;
    background:linear-gradient(90deg, transparent, #d4af37 30%, #facc15 50%, #d4af37 70%, transparent);
    width:0;
    animation:hmProgressFill 2s 1.5s ease forwards;
}

.hm-hiw-step {
    display:flex; flex-direction:column; align-items:center;
    text-align:center; padding:0 22px;
    opacity:0; transform:translateY(30px);
    transition:opacity .7s ease, transform .7s ease;
}
.hm-hiw-step.hm-visible { opacity:1; transform:none; }

/* Ripple rings */
.hm-hiw-icon-wrap {
    position:relative; width:104px; height:104px; margin-bottom:34px;
}
.hm-hiw-ripple {
    position:absolute; inset:-8px; border-radius:50%;
    border:1px solid rgba(212,175,55,.2);
    animation:hmRipple 3s ease-out infinite;
    opacity:0;
}
.hm-hiw-ripple-2 { animation-delay:1s; }
.hm-hiw-step.hm-visible .hm-hiw-ripple { opacity:1; }

.hm-hiw-num {
    position:absolute; top:-10px; right:-10px;
    width:28px; height:28px; border-radius:50%;
    background:linear-gradient(135deg,#d4af37,#facc15);
    color:#020617; font-size:.66rem; font-weight:900;
    display:flex; align-items:center; justify-content:center;
    z-index:2; box-shadow:0 4px 14px rgba(212,175,55,.6);
}

.hm-hiw-icon {
    width:104px; height:104px; border-radius:28px;
    background:linear-gradient(155deg,#0d1a30,#060e1c);
    border:1px solid rgba(212,175,55,.22);
    display:flex; align-items:center; justify-content:center;
    font-size:2rem; color:#d4af37;
    transition:transform .45s cubic-bezier(.34,1.56,.64,1),
               border-color .3s, box-shadow .3s;
    position:relative; overflow:hidden;
}
.hm-hiw-icon::before {
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(circle at 50% 110%, rgba(212,175,55,.2), transparent 60%);
    opacity:0; transition:opacity .4s;
}
.hm-hiw-step:hover .hm-hiw-icon { transform:translateY(-8px) scale(1.08) rotate(-3deg); border-color:rgba(212,175,55,.6); box-shadow:0 24px 60px rgba(0,0,0,.5), 0 0 0 1px rgba(212,175,55,.2), 0 0 30px rgba(212,175,55,.15); }
.hm-hiw-step:hover .hm-hiw-icon::before { opacity:1; }

/* Shine sweep on icon hover */
.hm-hiw-icon::after {
    content:'';
    position:absolute; top:0; left:-80%; width:50%; height:100%;
    background:linear-gradient(90deg,transparent,rgba(212,175,55,.22),transparent);
    transform:skewX(-20deg);
    transition:left .5s ease;
}
.hm-hiw-step:hover .hm-hiw-icon::after { left:120%; }

.hm-hiw-title { font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; color:#f1f5f9; margin:0 0 10px; }
.hm-hiw-desc  { font-size:.83rem; color:#475569; line-height:1.78; margin:0; }

/* ════════════════════════════════════════════════════════
   SECTION 4 — SOCIAL PROOF / NUMBERS
   ════════════════════════════════════════════════════════ */
.hm-proof-sec {
    padding:100px 24px;
    background:linear-gradient(135deg,#030b18 0%,#020617 50%,#060f1e 100%);
    border-top:1px solid rgba(255,255,255,.04);
    position:relative; overflow:hidden;
}
.hm-proof-sec::before {
    content:'';
    position:absolute; top:-200px; left:50%; transform:translateX(-50%);
    width:700px; height:700px; border-radius:50%;
    background:radial-gradient(circle,rgba(212,175,55,.06) 0%,transparent 70%);
    pointer-events:none;
}

.hm-proof-inner { max-width:1100px; margin:0 auto; }
.hm-proof-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-top:56px; }

.hm-proof-card {
    position:relative; text-align:center;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.07);
    border-radius:22px; padding:40px 20px;
    overflow:hidden;
    transition:transform .4s cubic-bezier(.34,1.56,.64,1), border-color .3s, box-shadow .3s;
    opacity:0; transform:translateY(24px);
    transition-property:opacity,transform,border-color,box-shadow;
}
.hm-proof-card.hm-visible { opacity:1; transform:none; }
.hm-proof-card::before {
    content:'';
    position:absolute; top:0; left:50%; transform:translateX(-50%);
    width:60%; height:2px;
    background:linear-gradient(90deg,transparent,#d4af37,transparent);
    opacity:0; transition:opacity .3s;
}
.hm-proof-card:hover { transform:translateY(-8px); border-color:rgba(212,175,55,.25); box-shadow:0 24px 60px rgba(0,0,0,.4); }
.hm-proof-card:hover::before { opacity:1; }

.hm-proof-icon {
    width:52px; height:52px; border-radius:16px; margin:0 auto 18px;
    background:rgba(212,175,55,.1); border:1px solid rgba(212,175,55,.2);
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem; color:#d4af37;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1);
    animation:hmGlow 3s ease-in-out infinite;
}
.hm-proof-card:hover .hm-proof-icon { transform:scale(1.2) rotate(-8deg); }

.hm-proof-num {
    font-size:2.2rem; font-weight:900; line-height:1;
    background:linear-gradient(135deg,#f1f5f9,#d4af37);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    background-clip:text;
    display:block; margin-bottom:6px;
}
.hm-proof-label { font-size:.78rem; color:#475569; font-weight:600; letter-spacing:.04em; }

/* ════════════════════════════════════════════════════════
   SECTION 5 — CLIENT REVIEWS
   ════════════════════════════════════════════════════════ */
.hm-reviews-sec {
    background:#020617; padding:110px 24px;
    position:relative; overflow:hidden;
    border-top:1px solid rgba(255,255,255,.04);
}
.hm-reviews-sec::before {
    content:'';
    position:absolute; inset:0;
    background:radial-gradient(ellipse 70% 50% at 50% 100%, rgba(99,102,241,.05), transparent 70%);
    pointer-events:none;
}

.hm-reviews-inner { max-width:900px; margin:0 auto; }

/* Carousel */
.hm-carousel-wrap {
    position:relative; overflow:hidden;
    border-radius:24px; margin-bottom:32px;
    border:1px solid rgba(255,255,255,.06);
    box-shadow:0 0 0 1px rgba(212,175,55,.05);
}
.hm-carousel-track { display:flex; transition:transform .6s cubic-bezier(.4,0,.2,1); }

.review-card { min-width:100%; padding:4px; box-sizing:border-box; }
.review-card img {
    width:100%; border-radius:20px;
    display:block; object-fit:contain;
    max-height:520px; background:#08142a;
}

/* Progress bar */
.hm-review-progress {
    height:2px; background:rgba(255,255,255,.06); border-radius:2px;
    margin-bottom:28px; overflow:hidden;
}
.hm-review-progress-bar {
    height:100%; background:linear-gradient(90deg,#d4af37,#facc15);
    border-radius:2px; width:0;
    transition:width .3s ease;
}

/* Controls */
.hm-review-controls { display:flex; align-items:center; justify-content:center; gap:20px; margin-bottom:32px; }
.hm-review-arrow {
    width:50px; height:50px; border-radius:15px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1);
    color:#64748b; font-size:1rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .25s cubic-bezier(.34,1.56,.64,1);
}
.hm-review-arrow:hover { background:rgba(212,175,55,.12); border-color:rgba(212,175,55,.35); color:#d4af37; transform:scale(1.12); }

.hm-dots { display:flex; gap:8px; align-items:center; }
.hm-dot {
    width:8px; height:8px; border-radius:50%;
    background:rgba(255,255,255,.12); border:none; cursor:pointer;
    transition:all .3s ease; padding:0;
}
.hm-dot.active { background:#d4af37; width:28px; border-radius:4px; box-shadow:0 0 12px rgba(212,175,55,.6); }

.hm-fb-cta { text-align:center; }
.hm-fb-btn {
    display:inline-flex; align-items:center; gap:12px;
    padding:14px 30px; border-radius:16px;
    border:1px solid rgba(255,255,255,.1);
    background:rgba(255,255,255,.03);
    color:#64748b; font-size:.88rem; font-weight:500;
    text-decoration:none; transition:all .3s;
}
.hm-fb-btn i:first-child { color:#1877f2; font-size:1.05rem; }
.hm-fb-btn:hover { color:#d4af37; border-color:rgba(212,175,55,.3); background:rgba(212,175,55,.06); transform:translateY(-2px); }

/* ════════════════════════════════════════════════════════
   VIDEO MODAL
   ════════════════════════════════════════════════════════ */
.hm-modal {
    position:fixed; inset:0;
    background:rgba(0,0,0,.88); backdrop-filter:blur(12px);
    display:none; align-items:center; justify-content:center;
    z-index:9999; padding:24px;
    animation:hmFadeIn .25s ease;
}
.hm-modal.active { display:flex; }
.hm-modal-box {
    background:linear-gradient(155deg,#0d1a30,#060e1c);
    border:1px solid rgba(212,175,55,.2);
    width:100%; max-width:920px; border-radius:28px; padding:36px;
    position:relative; box-shadow:0 50px 120px rgba(0,0,0,.8), 0 0 0 1px rgba(212,175,55,.08);
    animation:hmScaleIn .35s cubic-bezier(.34,1.56,.64,1) both;
}
.hm-modal-close {
    position:absolute; top:20px; right:20px;
    width:38px; height:38px; border-radius:12px;
    background:rgba(255,255,255,.07); color:#64748b; border:none;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:1rem; transition:all .25s;
}
.hm-modal-close:hover { background:rgba(239,68,68,.18); color:#f87171; transform:rotate(90deg) scale(1.1); }
.hm-modal-video-wrap { position:relative; padding-top:56.25%; border-radius:18px; overflow:hidden; background:#020617; }
.hm-modal-video-wrap iframe { position:absolute; top:0; left:0; width:100%; height:100%; border:none; }

/* ════════════════════════════════════════════════════════
   RESPONSIVE
   ════════════════════════════════════════════════════════ */
@media (max-width:1200px) {
    .hm-hero-inner { grid-template-columns:1fr; gap:52px; }
    .hm-hero-right { flex-direction:row; }
    .hm-gallery-card { flex:1; }
    .hm-stats-inner { grid-template-columns:repeat(2,1fr); gap:24px 0; padding:28px 32px; }
    .hm-stat { border-right:none; padding-right:0; }
    .hm-stat:nth-child(odd) { border-right:1px solid rgba(255,255,255,.06); padding-right:32px; }
    .hm-features-grid { grid-template-columns:repeat(2,1fr); }
    .hm-proof-grid { grid-template-columns:repeat(2,1fr); }
}
@media (max-width:860px) {
    .hm-hiw-grid { grid-template-columns:repeat(2,1fr); gap:56px 24px; }
    .hm-hiw-line { display:none; }
}
@media (max-width:768px) {
    .hm-hero { padding-top:80px; min-height:auto; }
    .hm-hero-inner { padding:0 22px 60px; }
    .hm-hero-right { flex-direction:column; }
    .hm-hero-ctas { flex-direction:column; }
    .hm-stats-inner { grid-template-columns:1fr 1fr; padding:22px; }
    .hm-stat:nth-child(odd) { border-right:1px solid rgba(255,255,255,.06); padding-right:16px; }
    .hm-scroll-indicator { display:none; }
    .hm-features-grid { grid-template-columns:1fr; }
    .hm-proof-grid { grid-template-columns:1fr 1fr; }
}
@media (max-width:520px) {
    .hm-hiw-grid { grid-template-columns:1fr; gap:44px; }
    .hm-proof-grid { grid-template-columns:1fr; }
    .hm-stats-inner { grid-template-columns:1fr; }
    .hm-stat:nth-child(odd) { border-right:none; padding-right:0; }
    .hm-hero-title { font-size:2.1rem; }
}
</style>

<div class="hm-page">

<!-- ══════════════════════════════════════════
     HERO
     ══════════════════════════════════════════ -->
<section class="hm-hero">
    <div class="hm-grid"></div>
    <div class="hm-orb hm-orb-1"></div>
    <div class="hm-orb hm-orb-2"></div>
    <div class="hm-orb hm-orb-3"></div>
    <div class="hm-orb hm-orb-4"></div>
    <div class="hm-particles" id="hmParticles"></div>

    <div class="hm-hero-inner">

        <!-- LEFT -->
        <div class="hm-hero-left">
            <div class="hm-hero-badge">
                <span class="hm-badge-dot"></span>
                <i class="fas fa-globe-asia" style="font-size:.75rem"></i>
                <?php echo t('idx_badge'); ?>
            </div>

            <h1 class="hm-hero-title">
                <?php echo t('idx_hero_title1'); ?>
                <span class="hm-hero-gold"><?php echo t('idx_hero_title2'); ?></span>
            </h1>

            <p class="hm-hero-desc"><?php echo t('idx_hero_desc'); ?></p>

            <div class="hm-hero-ctas">
                <a href="services.php" class="hm-btn-gold">
                    <i class="fas fa-rocket"></i> <?php echo t('idx_btn_services'); ?>
                </a>
                <a href="contact.php" class="hm-btn-ghost">
                    <i class="fas fa-comments"></i> <?php echo t('idx_btn_talk'); ?>
                </a>
            </div>

            <div class="hm-trust">
                <div class="hm-trust-item">
                    <span class="hm-trust-icon"><i class="fas fa-check"></i></span>
                    <?php echo t('idx_trust_1'); ?>
                </div>
                <div class="hm-trust-item">
                    <span class="hm-trust-icon"><i class="fas fa-check"></i></span>
                    <?php echo t('idx_trust_2'); ?>
                </div>
                <div class="hm-trust-item">
                    <span class="hm-trust-icon"><i class="fas fa-check"></i></span>
                    <?php echo t('idx_trust_3'); ?>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="hm-hero-right">
            <a href="gallery-images.php" class="hm-gallery-card">
                <div class="hm-card-thumb" id="heroImgSlider">
                    <img src="uploads/gallery/thumbs/img1.jpg" class="active" alt="">
                    <img src="uploads/gallery/thumbs/img2.jpg" alt="" loading="lazy">
                    <img src="uploads/gallery/thumbs/img3.jpg" alt="" loading="lazy">
                    <div class="hm-card-thumb-overlay"></div>
                    <div class="hm-card-thumb-icon"><i class="fas fa-images"></i></div>
                </div>
                <div class="hm-card-body">
                    <div class="hm-card-info">
                        <h3><?php echo t('idx_img_gallery'); ?></h3>
                        <p><?php echo t('idx_img_sub'); ?></p>
                    </div>
                    <div class="hm-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
            </a>

            <a href="gallery-videos.php" class="hm-gallery-card">
                <div class="hm-card-thumb" id="heroVidSlider">
                    <img src="https://img.youtube.com/vi/QnEM0NZXaGA/hqdefault.jpg" class="active" alt="">
                    <img src="https://img.youtube.com/vi/AhKR62GBlKw/hqdefault.jpg" alt="" loading="lazy">
                    <img src="https://img.youtube.com/vi/BrEChn_Huos/hqdefault.jpg" alt="" loading="lazy">
                    <div class="hm-card-thumb-overlay"></div>
                    <div class="hm-card-thumb-icon"><i class="fas fa-play"></i></div>
                </div>
                <div class="hm-card-body">
                    <div class="hm-card-info">
                        <h3><?php echo t('idx_vid_gallery'); ?></h3>
                        <p><?php echo t('idx_vid_sub'); ?></p>
                    </div>
                    <div class="hm-card-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
            </a>
        </div>

    </div><!-- /.hm-hero-inner -->

    <!-- Stats strip -->
    <div class="hm-stats-strip">
        <div class="hm-stats-inner">
            <div class="hm-stat">
                <div class="hm-stat-icon"><i class="fas fa-users"></i></div>
                <div class="hm-stat-text">
                    <span class="hm-stat-num" data-target="500" data-suffix="+">0</span>
                    <span class="hm-stat-label"><?php echo t('idx_stat_clients'); ?></span>
                </div>
            </div>
            <div class="hm-stat">
                <div class="hm-stat-icon"><i class="fas fa-globe"></i></div>
                <div class="hm-stat-text">
                    <span class="hm-stat-num" data-target="20" data-suffix="+">0</span>
                    <span class="hm-stat-label"><?php echo t('idx_stat_countries'); ?></span>
                </div>
            </div>
            <div class="hm-stat">
                <div class="hm-stat-icon"><i class="fas fa-briefcase"></i></div>
                <div class="hm-stat-text">
                    <span class="hm-stat-num" data-target="10" data-suffix="+">0</span>
                    <span class="hm-stat-label"><?php echo t('idx_stat_brokers'); ?></span>
                </div>
            </div>
            <div class="hm-stat">
                <div class="hm-stat-icon"><i class="fas fa-star"></i></div>
                <div class="hm-stat-text">
                    <span class="hm-stat-num">5★</span>
                    <span class="hm-stat-label"><?php echo t('idx_stat_rating'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="hm-scroll-indicator">
        <div class="hm-scroll-arrow"><i class="fas fa-chevron-down"></i></div>
    </div>

</section>

<!-- ══════════════════════════════════════════
     FEATURES — WHY WISE INVEST
     ══════════════════════════════════════════ -->
<section class="hm-features-sec">
    <div class="hm-sec-head hm-reveal">
        <div class="hm-sec-label"><i class="fas fa-shield-halved"></i> Why Choose Us</div>
        <h2 class="hm-sec-title">Everything You Need to <span>Invest Globally</span></h2>
        <p class="hm-sec-sub">From account setup to ongoing support — we make global markets accessible to everyone</p>
        <div class="hm-sec-rule"></div>
    </div>

    <div class="hm-features-grid" style="max-width:1200px;margin:0 auto;">

        <div class="hm-feat-card hm-feat-card--gold hm-reveal" style="transition-delay:.05s">
            <div class="hm-feat-icon-wrap"><i class="fas fa-shield-alt"></i></div>
            <h3 class="hm-feat-title">100% Secure &amp; Legal</h3>
            <p class="hm-feat-desc">All accounts are opened through official broker channels with full KYC/AML compliance — no shortcuts, no risks.</p>
            <div class="hm-feat-bg-num">01</div>
        </div>

        <div class="hm-feat-card hm-feat-card--blue hm-reveal" style="transition-delay:.12s">
            <div class="hm-feat-icon-wrap"><i class="fas fa-route"></i></div>
            <h3 class="hm-feat-title">Step-by-Step Guidance</h3>
            <p class="hm-feat-desc">We walk with you from first inquiry to live trading — documents, verification, funding, and beyond.</p>
            <div class="hm-feat-bg-num">02</div>
        </div>

        <div class="hm-feat-card hm-feat-card--purple hm-reveal" style="transition-delay:.19s">
            <div class="hm-feat-icon-wrap"><i class="fas fa-globe"></i></div>
            <h3 class="hm-feat-title">9+ Global Platforms</h3>
            <p class="hm-feat-desc">Access Moomoo, IBKR, Tiger Brokers, Charles Schwab, eToro and more — the world's top brokerages at your fingertips.</p>
            <div class="hm-feat-bg-num">03</div>
        </div>

        <div class="hm-feat-card hm-feat-card--green hm-reveal" style="transition-delay:.26s">
            <div class="hm-feat-icon-wrap"><i class="fas fa-headset"></i></div>
            <h3 class="hm-feat-title">24/7 Live Support</h3>
            <p class="hm-feat-desc">Got a question at midnight? Our team is always available via WhatsApp, Telegram and live chat — real people, real help.</p>
            <div class="hm-feat-bg-num">04</div>
        </div>

        <div class="hm-feat-card hm-feat-card--rose hm-reveal" style="transition-delay:.33s">
            <div class="hm-feat-icon-wrap"><i class="fas fa-graduation-cap"></i></div>
            <h3 class="hm-feat-title">Education &amp; Learning</h3>
            <p class="hm-feat-desc">Premium articles, video classes, calculators, and glossary to sharpen your investment knowledge over time.</p>
            <div class="hm-feat-bg-num">05</div>
        </div>

        <div class="hm-feat-card hm-feat-card--cyan hm-reveal" style="transition-delay:.40s">
            <div class="hm-feat-icon-wrap"><i class="fas fa-chart-line"></i></div>
            <h3 class="hm-feat-title">100% Success Rate</h3>
            <p class="hm-feat-desc">Every single client we've onboarded has successfully opened and funded their brokerage account — zero failures.</p>
            <div class="hm-feat-bg-num">06</div>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════
     HOW IT WORKS
     ══════════════════════════════════════════ -->
<section class="hm-hiw-sec">
    <div class="hm-sec-head hm-reveal">
        <div class="hm-sec-label"><i class="fas fa-route"></i> <?php echo t('idx_hiw_label'); ?></div>
        <h2 class="hm-sec-title"><?php echo t('idx_hiw_title'); ?> <span><?php echo t('idx_hiw_title_gold'); ?></span></h2>
        <p class="hm-sec-sub"><?php echo t('idx_hiw_sub'); ?></p>
        <div class="hm-sec-rule"></div>
    </div>

    <div class="hm-hiw-grid">
        <div class="hm-hiw-line"></div>

        <div class="hm-hiw-step" style="transition-delay:.1s">
            <div class="hm-hiw-icon-wrap">
                <div class="hm-hiw-ripple"></div>
                <div class="hm-hiw-ripple hm-hiw-ripple-2"></div>
                <div class="hm-hiw-num">1</div>
                <div class="hm-hiw-icon"><i class="fas fa-comments"></i></div>
            </div>
            <h3 class="hm-hiw-title"><?php echo t('idx_step1_title'); ?></h3>
            <p class="hm-hiw-desc"><?php echo t('idx_step1_desc'); ?></p>
        </div>

        <div class="hm-hiw-step" style="transition-delay:.22s">
            <div class="hm-hiw-icon-wrap">
                <div class="hm-hiw-ripple"></div>
                <div class="hm-hiw-ripple hm-hiw-ripple-2"></div>
                <div class="hm-hiw-num">2</div>
                <div class="hm-hiw-icon"><i class="fas fa-file-shield"></i></div>
            </div>
            <h3 class="hm-hiw-title"><?php echo t('idx_step2_title'); ?></h3>
            <p class="hm-hiw-desc"><?php echo t('idx_step2_desc'); ?></p>
        </div>

        <div class="hm-hiw-step" style="transition-delay:.34s">
            <div class="hm-hiw-icon-wrap">
                <div class="hm-hiw-ripple"></div>
                <div class="hm-hiw-ripple hm-hiw-ripple-2"></div>
                <div class="hm-hiw-num">3</div>
                <div class="hm-hiw-icon"><i class="fas fa-sliders"></i></div>
            </div>
            <h3 class="hm-hiw-title"><?php echo t('idx_step3_title'); ?></h3>
            <p class="hm-hiw-desc"><?php echo t('idx_step3_desc'); ?></p>
        </div>

        <div class="hm-hiw-step" style="transition-delay:.46s">
            <div class="hm-hiw-icon-wrap">
                <div class="hm-hiw-ripple"></div>
                <div class="hm-hiw-ripple hm-hiw-ripple-2"></div>
                <div class="hm-hiw-num">4</div>
                <div class="hm-hiw-icon"><i class="fas fa-rocket"></i></div>
            </div>
            <h3 class="hm-hiw-title"><?php echo t('idx_step4_title'); ?></h3>
            <p class="hm-hiw-desc"><?php echo t('idx_step4_desc'); ?></p>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     SOCIAL PROOF NUMBERS
     ══════════════════════════════════════════ -->
<section class="hm-proof-sec">
    <div class="hm-proof-inner">
        <div class="hm-sec-head hm-reveal">
            <div class="hm-sec-label"><i class="fas fa-trophy"></i> Track Record</div>
            <h2 class="hm-sec-title">Numbers That <span>Speak for Themselves</span></h2>
            <p class="hm-sec-sub">Real results from real clients who trusted us with their global investing journey</p>
            <div class="hm-sec-rule"></div>
        </div>

        <div class="hm-proof-grid">
            <div class="hm-proof-card" style="transition-delay:.08s">
                <div class="hm-proof-icon"><i class="fas fa-users"></i></div>
                <span class="hm-proof-num" data-target="500" data-suffix="+">0</span>
                <span class="hm-proof-label"><?php echo t('idx_stat_clients'); ?></span>
            </div>
            <div class="hm-proof-card" style="transition-delay:.18s">
                <div class="hm-proof-icon"><i class="fas fa-check-double"></i></div>
                <span class="hm-proof-num">100%</span>
                <span class="hm-proof-label">Success Rate</span>
            </div>
            <div class="hm-proof-card" style="transition-delay:.28s">
                <div class="hm-proof-icon"><i class="fas fa-briefcase"></i></div>
                <span class="hm-proof-num" data-target="9" data-suffix="+">0</span>
                <span class="hm-proof-label"><?php echo t('idx_stat_brokers'); ?></span>
            </div>
            <div class="hm-proof-card" style="transition-delay:.38s">
                <div class="hm-proof-icon"><i class="fas fa-star"></i></div>
                <span class="hm-proof-num">5.0★</span>
                <span class="hm-proof-label"><?php echo t('idx_stat_rating'); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════
     CLIENT REVIEWS
     ══════════════════════════════════════════ -->
<section class="hm-reviews-sec">
<div class="hm-reviews-inner">

    <div class="hm-sec-head hm-reveal">
        <div class="hm-sec-label"><i class="fas fa-star"></i> <?php echo t('idx_reviews_label'); ?></div>
        <h2 class="hm-sec-title"><?php echo t('idx_reviews_title'); ?> <span><?php echo t('idx_reviews_gold'); ?></span></h2>
        <p class="hm-sec-sub"><?php echo t('idx_reviews_sub'); ?></p>
        <div class="hm-sec-rule"></div>
    </div>

    <div class="hm-carousel-wrap hm-reveal">
        <div class="hm-carousel-track" id="reviewTrack"></div>
    </div>

    <div class="hm-review-progress hm-reveal">
        <div class="hm-review-progress-bar" id="reviewProgressBar"></div>
    </div>

    <div class="hm-review-controls hm-reveal">
        <button class="hm-review-arrow" onclick="prevReview()" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="hm-dots" id="hmDots"></div>
        <button class="hm-review-arrow" onclick="nextReview()" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <div class="hm-fb-cta hm-reveal">
        <a href="https://www.facebook.com/profile.php?id=61584267038170"
           target="_blank" rel="noopener" class="hm-fb-btn">
            <i class="fab fa-facebook-f"></i>
            <?php echo t('idx_reviews_fb'); ?>
            <i class="fas fa-external-link-alt" style="font-size:.7rem;opacity:.5"></i>
        </a>
    </div>

</div>
</section>

<!-- VIDEO MODAL -->
<div class="hm-modal" id="galleryModal">
    <div class="hm-modal-box">
        <button class="hm-modal-close" onclick="closeVideo()"><i class="fas fa-times"></i></button>
        <div class="hm-modal-video-wrap" id="galleryContent"></div>
    </div>
</div>

</div><!-- /.hm-page -->

<script>
/* ════════════════════════════════════════════
   PARTICLE SYSTEM
   ════════════════════════════════════════════ */
(function () {
    const container = document.getElementById('hmParticles');
    if (!container) return;
    const count = 22;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.className = 'hm-particle';
        const size = Math.random() * 3 + 1;
        p.style.cssText = `
            width:${size}px; height:${size}px;
            left:${Math.random() * 100}%;
            bottom:${Math.random() * 30}%;
            --d:${Math.random() * 16 + 8}s;
            --dl:${-Math.random() * 16}s;
            opacity:${Math.random() * 0.6 + 0.2};
        `;
        container.appendChild(p);
    }
})();

/* ════════════════════════════════════════════
   SCROLL REVEAL (Intersection Observer)
   ════════════════════════════════════════════ */
(function () {
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('hm-visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    document.querySelectorAll('.hm-reveal, .hm-reveal-l, .hm-reveal-r, .hm-reveal-scale, .hm-stat, .hm-hiw-step, .hm-proof-card').forEach(el => io.observe(el));
})();

/* ════════════════════════════════════════════
   ANIMATED COUNTER
   ════════════════════════════════════════════ */
function animateCounter(el) {
    const target = parseInt(el.dataset.target);
    const suffix = el.dataset.suffix || '';
    if (isNaN(target)) return;
    const duration = 1800;
    const start = performance.now();
    function step(now) {
        const p = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - p, 4);
        el.textContent = Math.floor(ease * target) + suffix;
        if (p < 1) requestAnimationFrame(step);
        else el.textContent = target + suffix;
    }
    requestAnimationFrame(step);
}

const counterObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            const nums = e.target.querySelectorAll('[data-target]');
            nums.forEach(n => animateCounter(n));
            counterObs.unobserve(e.target);
        }
    });
}, { threshold: 0.3 });

document.querySelectorAll('.hm-stats-strip, .hm-proof-grid').forEach(el => counterObs.observe(el));

/* ════════════════════════════════════════════
   THUMB SLIDERS
   ════════════════════════════════════════════ */
function initSlider(id, interval) {
    const el = document.getElementById(id);
    if (!el) return;
    const imgs = el.querySelectorAll('img');
    if (imgs.length < 2) return;
    let i = 0;
    setInterval(() => {
        imgs[i].classList.remove('active');
        i = (i + 1) % imgs.length;
        imgs[i].classList.add('active');
    }, interval || 3000);
}
initSlider('heroImgSlider', 2800);
initSlider('heroVidSlider', 3400);

/* ════════════════════════════════════════════
   VIDEO MODAL
   ════════════════════════════════════════════ */
function openVideo(id) {
    const safeId = encodeURIComponent(id);
    document.getElementById('galleryContent').innerHTML =
        `<iframe src="https://www.youtube.com/embed/${safeId}?autoplay=1&rel=0" allow="autoplay;encrypted-media" allowfullscreen></iframe>`;
    document.getElementById('galleryModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeVideo() {
    document.getElementById('galleryModal').classList.remove('active');
    document.getElementById('galleryContent').innerHTML = '';
    document.body.style.overflow = '';
}
document.getElementById('galleryModal').addEventListener('click', function (e) {
    if (e.target === this) closeVideo();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeVideo(); });

/* ════════════════════════════════════════════
   REVIEW CAROUSEL
   ════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    const track   = document.getElementById('reviewTrack');
    const dotsEl  = document.getElementById('hmDots');
    const progBar = document.getElementById('reviewProgressBar');
    const AUTO_MS = 7000;
    let idx = 0, cards = [], timer, progTimer;

    fetch('gallery-reviews.php')
        .then(r => r.text())
        .then(html => {
            track.innerHTML = html;
            cards = track.querySelectorAll('.review-card');
            buildDots();
            goTo(0);
            startAuto();
            initSwipe();
        });

    function buildDots() {
        dotsEl.innerHTML = '';
        cards.forEach((_, n) => {
            const d = document.createElement('button');
            d.className = 'hm-dot' + (n === 0 ? ' active' : '');
            d.setAttribute('aria-label', 'Review ' + (n + 1));
            d.addEventListener('click', () => { goTo(n); startAuto(); });
            dotsEl.appendChild(d);
        });
    }

    function goTo(n) {
        idx = (n + cards.length) % cards.length;
        track.style.transform = `translateX(-${idx * 100}%)`;
        dotsEl.querySelectorAll('.hm-dot').forEach((d, i) => d.classList.toggle('active', i === idx));
        updateProgress();
    }

    function updateProgress() {
        if (!progBar || !cards.length) return;
        clearInterval(progTimer);
        progBar.style.transition = 'none';
        progBar.style.width = '0%';
        setTimeout(() => {
            progBar.style.transition = `width ${AUTO_MS}ms linear`;
            progBar.style.width = '100%';
        }, 30);
    }

    function startAuto() {
        clearInterval(timer);
        timer = setInterval(() => goTo(idx + 1), AUTO_MS);
    }

    function initSwipe() {
        let sx = 0;
        track.addEventListener('touchstart', e => { sx = e.touches[0].clientX; }, { passive: true });
        track.addEventListener('touchend', e => {
            const diff = sx - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) { goTo(diff > 0 ? idx + 1 : idx - 1); startAuto(); }
        }, { passive: true });
    }

    window.nextReview = () => { goTo(idx + 1); startAuto(); };
    window.prevReview = () => { goTo(idx - 1); startAuto(); };
});

/* ════════════════════════════════════════════
   TILT EFFECT ON GALLERY CARDS
   ════════════════════════════════════════════ */
document.querySelectorAll('.hm-gallery-card').forEach(card => {
    card.addEventListener('mousemove', function (e) {
        if (window.innerWidth < 768) return;
        const r = this.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width  - 0.5;
        const y = (e.clientY - r.top)  / r.height - 0.5;
        this.style.transform = `translateY(-8px) scale(1.015) rotateX(${-y * 6}deg) rotateY(${x * 6}deg)`;
    });
    card.addEventListener('mouseleave', function () {
        this.style.transform = '';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
