<?php include 'includes/header.php';

date_default_timezone_set('Asia/Bangkok');
$today = date('Y-m-d');

$all_classes = [
    [
        'id'           => 1,
        'title'        => 'Basic Stock Investment',
        'batch'        => '',
        'start_date'   => '2026-04-21',
        'end_date'     => '',
        'display_date' => 'Apr 21, 2026',
        'image'        => 'https://images.unsplash.com/photo-1611974765270-ca12586343bb?q=80&w=1200',
        'desc'         => 'Master the market fundamentals. Learn trends, picking winning stocks, and portfolio management.',
        'level'        => 'Beginner',
        'duration'     => '2 Days',
        'schedule'     => '',
        'highlights'   => ['Market Basics', 'Stock Picking', 'Portfolio Build'],
    ],
    [
        'id'           => 2,
        'title'        => 'Financial Freedom Journey',
        'batch'        => '',
        'start_date'   => '2026-03-27',
        'end_date'     => '',
        'display_date' => 'Mar 27, 2026',
        'image'        => 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?q=80&w=1200',
        'desc'         => 'Deep dive into technical analysis, options, and risk strategies for serious investors.',
        'level'        => 'Advanced',
        'duration'     => '6 Weeks',
        'schedule'     => 'Every Friday',
        'highlights'   => ['Technical Analysis', 'Options Trading', 'Risk Management'],
    ],
    [
        'id'           => 3,
        'title'        => 'Stock Investment Mastery',
        'batch'        => 'Batch 3',
        'start_date'   => '2026-06-22',
        'end_date'     => '2026-06-26',
        'display_date' => 'Jun 22 – Jun 26, 2026',
        'image'        => 'https://images.unsplash.com/photo-1579621970795-87facc2f976d?q=80&w=1200',
        'desc'         => 'Learn the essential fundamentals of the stock market from the ground up. Understand how stocks work, read price charts, and take your very first steps as an investor.',
        'level'        => 'Beginner',
        'duration'     => '5 Days',
        'schedule'     => 'Jun 22 – Jun 26',
        'highlights'   => ['Market Fundamentals', 'Chart Reading', 'First Portfolio'],
    ],
];

$current_classes  = [];
$upcoming_classes = [];

foreach ($all_classes as $class) {
    if ($today >= $class['start_date']) {
        $current_classes[] = $class;
    } else {
        $upcoming_classes[] = $class;
    }
}
?>

<style>
/* ══════════════════════════════════════════════════════════════════
   CLASSES PAGE
   ══════════════════════════════════════════════════════════════════ */

/* ── Keyframes ─────────────────────────────────────────────────── */
@keyframes cl-fadeUp    { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
@keyframes cl-shimmer   { from{background-position:-400% center} to{background-position:400% center} }
@keyframes cl-orb       { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.14) translate(14px,-10px)} }
@keyframes cl-gridMove  { from{background-position:0 0} to{background-position:60px 60px} }
@keyframes cl-livePulse { 0%,100%{box-shadow:0 0 0 0 rgba(34,197,94,0.5)} 70%{box-shadow:0 0 0 12px rgba(34,197,94,0)} }
@keyframes cl-liveDot   { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.75)} }
@keyframes cl-barFill   { from{width:0%} to{width:var(--w)} }
@keyframes cl-cardIn    { from{opacity:0;transform:translateY(30px) scale(0.97)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes cl-borderRun { 0%{background-position:0% 0%} 100%{background-position:400% 0%} }
@keyframes cl-countdownTick { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-2px)} }
@keyframes cl-particleRise  { 0%{opacity:0;transform:translateY(40px)} 30%{opacity:1} 100%{opacity:0;transform:translateY(-160px)} }
@keyframes cl-announceScan  { 0%{left:-60%} 100%{left:110%} }
@keyframes cl-badgePop      { 0%{transform:scale(0.7);opacity:0} 80%{transform:scale(1.08)} 100%{transform:scale(1);opacity:1} }

/* ── Base ──────────────────────────────────────────────────────── */
.cl2-page { background: #020617; min-height: 100vh; }

/* ══════════════════════════════════════════════════════════════════
   ANNOUNCEMENT BAR
   ══════════════════════════════════════════════════════════════════ */
.cl2-announce {
    position: relative;
    background: linear-gradient(90deg, rgba(16,185,129,0.06) 0%, rgba(212,175,55,0.06) 50%, rgba(16,185,129,0.06) 100%);
    border-bottom: 1px solid rgba(212,175,55,0.12);
    padding: 11px 24px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    overflow: hidden;
}
.cl2-announce::after {
    content: '';
    position: absolute;
    top: 0; bottom: 0;
    width: 50%;
    background: linear-gradient(90deg, transparent, rgba(212,175,55,0.07), transparent);
    left: -60%;
    animation: cl-announceScan 4s ease-in-out infinite;
    pointer-events: none;
}
.cl2-announce-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px rgba(16,185,129,0.7);
    animation: cl-liveDot 1.5s ease-in-out infinite;
    flex-shrink: 0;
}
.cl2-announce-text {
    font-size: 0.78rem;
    color: #64748b;
    letter-spacing: 0.3px;
    position: relative;
    z-index: 1;
}
.cl2-announce-text strong { color: #94a3b8; }
.cl2-announce-link {
    font-size: 0.75rem;
    font-weight: 800;
    color: #d4af37;
    text-decoration: none;
    padding: 3px 12px;
    border: 1px solid rgba(212,175,55,0.3);
    border-radius: 99px;
    letter-spacing: 0.5px;
    position: relative; z-index: 1;
    transition: background 0.2s, border-color 0.2s;
}
.cl2-announce-link:hover {
    background: rgba(212,175,55,0.1);
    border-color: rgba(212,175,55,0.5);
}

/* ══════════════════════════════════════════════════════════════════
   HERO
   ══════════════════════════════════════════════════════════════════ */
.cl2-hero {
    position: relative;
    padding: 100px 24px 90px;
    background: #020617;
    overflow: hidden;
    text-align: center;
}
.cl2-grid-bg {
    position: absolute; inset: 0; pointer-events: none;
    background-image:
        linear-gradient(rgba(212,175,55,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(212,175,55,0.04) 1px, transparent 1px);
    background-size: 60px 60px;
    animation: cl-gridMove 14s linear infinite;
}
.cl2-orb { position: absolute; border-radius: 50%; filter: blur(85px); pointer-events: none; }
.cl2-orb-1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(212,175,55,0.13) 0%, transparent 70%);
    top: -200px; right: -120px;
    animation: cl-orb 10s ease-in-out infinite;
}
.cl2-orb-2 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(99,102,241,0.09) 0%, transparent 70%);
    bottom: -80px; left: -80px;
    animation: cl-orb 13s ease-in-out infinite reverse;
}
.cl2-orb-3 {
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(16,185,129,0.06) 0%, transparent 70%);
    top: 40%; left: 30%;
    animation: cl-orb 16s ease-in-out infinite;
}
.cl2-particles { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.cl2-particle {
    position: absolute; width: 2px; height: 2px; border-radius: 50%;
    background: rgba(212,175,55,0.5);
    animation: cl-particleRise var(--dur,9s) var(--delay,0s) ease-in infinite;
}

.cl2-hero-inner { position: relative; z-index: 3; max-width: 860px; margin: 0 auto; }

.cl2-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(212,175,55,0.08); border: 1px solid rgba(212,175,55,0.25);
    color: #d4af37; padding: 7px 20px; border-radius: 99px;
    font-size: 0.7rem; font-weight: 800; letter-spacing: 2.5px; text-transform: uppercase;
    margin-bottom: 24px;
    animation: cl-fadeUp 0.5s 0.1s ease both;
}
.cl2-hero-title {
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    font-weight: 900; color: #fff; line-height: 1.1;
    margin: 0 0 16px; letter-spacing: -1px;
    animation: cl-fadeUp 0.6s 0.2s ease both;
}
.cl2-gold {
    background: linear-gradient(135deg, #d4af37 0%, #facc15 40%, #f59e0b 70%, #d4af37 100%);
    background-size: 200% auto;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    animation: cl-shimmer 4s linear infinite;
}
.cl2-hero-sub {
    color: #64748b; font-size: 1rem; max-width: 520px;
    margin: 0 auto 40px; line-height: 1.7;
    animation: cl-fadeUp 0.6s 0.3s ease both;
}

/* Stat strip */
.cl2-stats {
    display: inline-flex;
    background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px; overflow: hidden; margin-bottom: 32px;
    animation: cl-fadeUp 0.6s 0.4s ease both;
}
.cl2-stat {
    padding: 18px 30px; text-align: center;
    border-right: 1px solid rgba(255,255,255,0.06);
    position: relative;
}
.cl2-stat:last-child { border-right: none; }
.cl2-stat::before {
    content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%);
    width: 40%; height: 2px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
}
.cl2-stat strong { display: block; font-size: 1.7rem; font-weight: 900; color: #d4af37; line-height: 1; }
.cl2-stat span   { font-size: 0.68rem; color: #475569; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 4px; display: block; }

.cl2-scroll-cue {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    color: #334155; font-size: 0.7rem; letter-spacing: 2px; text-transform: uppercase;
    animation: cl-fadeUp 0.6s 0.6s ease both;
    margin-top: 16px;
}
.cl2-scroll-cue i { font-size: 1.1rem; animation: cl-liveDot 1.5s ease-in-out infinite; }

/* ══════════════════════════════════════════════════════════════════
   BODY
   ══════════════════════════════════════════════════════════════════ */
.cl2-body { background: #020617; padding: 60px 24px 110px; }
.cl2-inner { max-width: 1260px; margin: 0 auto; }

/* Section heading */
.cl2-sec-head {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 36px; position: relative;
}
.cl2-sec-head::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, rgba(212,175,55,0.25), transparent);
}
.cl2-sec-head h2 {
    font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0;
    white-space: nowrap;
}
.cl2-live-badge {
    display: flex; align-items: center; gap: 6px;
    background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.25);
    color: #4ade80; padding: 4px 12px; border-radius: 99px;
    font-size: 0.68rem; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;
}
.cl2-live-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #22c55e;
    animation: cl-liveDot 1.5s ease-in-out infinite;
    box-shadow: 0 0 6px rgba(34,197,94,0.7);
}
.cl2-upcoming-icon { color: #d4af37; font-size: 0.95rem; }

/* ── Card grid ───────────────────────────────────────────────── */
.cl2-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 28px;
    margin-bottom: 80px;
}

/* ══════════════════════════════════════════════════════════════════
   CLASS CARD
   ══════════════════════════════════════════════════════════════════ */
.cl2-card {
    position: relative;
    background: #07101e;
    border-radius: 24px;
    overflow: hidden;
    opacity: 0; transform: translateY(28px);
    transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.35s;
    cursor: default;
}
.cl2-card.visible {
    animation: cl-cardIn 0.55s ease forwards;
}
.cl2-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 32px 80px rgba(0,0,0,0.65);
}
.cl2-card.cl2-card-beginner:hover {
    box-shadow: 0 32px 80px rgba(0,0,0,0.65),
                0 0 0 1px rgba(16,185,129,0.18),
                0 0 60px rgba(16,185,129,0.07);
}
.cl2-card.cl2-card-advanced:hover {
    box-shadow: 0 32px 80px rgba(0,0,0,0.65),
                0 0 0 1px rgba(212,175,55,0.18),
                0 0 60px rgba(212,175,55,0.07);
}

/* Animated border */
.cl2-card-border {
    position: absolute; inset: 0; border-radius: 24px; pointer-events: none;
    border: 1px solid rgba(255,255,255,0.07);
    transition: border-color 0.3s;
}
.cl2-card:hover .cl2-card-border { border-color: rgba(212,175,55,0.25); }
.cl2-card.cl2-card-beginner:hover .cl2-card-border { border-color: rgba(16,185,129,0.25); }

/* LIVE card */
.cl2-card.cl2-card-live .cl2-card-border { border-color: rgba(34,197,94,0.2); }
.cl2-card.cl2-card-live:hover .cl2-card-border {
    border-color: rgba(34,197,94,0.45);
    box-shadow: 0 0 30px rgba(34,197,94,0.06) inset;
}

/* ── Thumbnail ───────────────────────────────────────────────── */
.cl2-thumb {
    position: relative; height: 220px;
    overflow: hidden;
}
.cl2-thumb-img {
    width: 100%; height: 100%;
    object-fit: cover;
    transform-origin: center;
    transition: transform 0.65s cubic-bezier(0.25,0.46,0.45,0.94);
}
.cl2-card:hover .cl2-thumb-img { transform: scale(1.08); }
.cl2-thumb-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(7,16,30,1) 0%, rgba(7,16,30,0.45) 55%, rgba(7,16,30,0.1) 100%);
}

/* Status pill */
.cl2-status {
    position: absolute; top: 16px; left: 16px;
    display: flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 99px;
    font-size: 0.7rem; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase;
    backdrop-filter: blur(10px);
    z-index: 2;
}
.cl2-status-live {
    background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.4);
    color: #4ade80;
    animation: cl-livePulse 2s ease-in-out infinite;
}
.cl2-status-live .cl2-status-dot {
    width: 6px; height: 6px; border-radius: 50%; background: #22c55e;
    animation: cl-liveDot 1.2s ease-in-out infinite;
}
.cl2-status-open {
    background: rgba(212,175,55,0.12); border: 1px solid rgba(212,175,55,0.35);
    color: #d4af37;
}

/* Duration badge (top right) */
.cl2-duration-badge {
    position: absolute; top: 16px; right: 16px;
    background: rgba(2,6,23,0.75); border: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    color: #94a3b8; padding: 5px 12px; border-radius: 8px;
    font-size: 0.72rem; font-weight: 600;
    display: flex; align-items: center; gap: 5px;
    z-index: 2;
}
.cl2-duration-badge i { color: #d4af37; }

/* Progress bar (live card image bottom) */
.cl2-progress-wrap {
    position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
    background: rgba(255,255,255,0.06); z-index: 2;
}
.cl2-progress-bar {
    height: 100%; width: 0;
    background: linear-gradient(90deg, #22c55e, #4ade80);
    border-radius: 0 3px 3px 0;
    transition: width 1.2s 0.5s cubic-bezier(0.4,0,0.2,1);
}

/* ── Card body ───────────────────────────────────────────────── */
.cl2-card-body { padding: 22px 24px 26px; position: relative; }

/* Meta row */
.cl2-meta {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    margin-bottom: 10px;
}
.cl2-meta-item {
    display: flex; align-items: center; gap: 5px;
    font-size: 0.76rem; color: #475569;
}
.cl2-meta-item i { color: #d4af37; font-size: 0.7rem; }

/* Level badge */
.cl2-level-tag {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 11px; border-radius: 99px;
    font-size: 0.67rem; font-weight: 800; letter-spacing: 0.8px;
    text-transform: uppercase; white-space: nowrap;
    animation: cl-badgePop 0.4s 0.8s ease both;
}
.cl2-level-beginner {
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.22);
    color: #34d399;
}
.cl2-level-advanced {
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.22);
    color: #d4af37;
}

/* Schedule row */
.cl2-schedule-row {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.74rem; color: #64748b;
    margin-bottom: 12px;
    padding: 5px 12px;
    background: rgba(212,175,55,0.04);
    border: 1px solid rgba(212,175,55,0.1);
    border-radius: 8px;
}
.cl2-schedule-row i { color: #d4af37; font-size: 0.68rem; }
.cl2-card-beginner .cl2-schedule-row {
    background: rgba(16,185,129,0.04);
    border-color: rgba(16,185,129,0.1);
}
.cl2-card-beginner .cl2-schedule-row i { color: #34d399; }

/* Title row */
.cl2-title-row {
    display: flex; align-items: flex-start; gap: 10px;
    margin-bottom: 8px;
}
.cl2-card-title {
    font-size: 1.12rem; font-weight: 800; color: #fff;
    margin: 0; line-height: 1.3; flex: 1;
}
.cl2-batch-label {
    flex-shrink: 0;
    background: linear-gradient(135deg, #b8960c, #d4af37);
    color: #020617;
    padding: 3px 10px; border-radius: 6px;
    font-size: 0.6rem; font-weight: 900;
    letter-spacing: 1.5px; text-transform: uppercase;
    margin-top: 4px; white-space: nowrap;
    box-shadow: 0 2px 10px rgba(212,175,55,0.3);
    animation: cl-badgePop 0.45s 0.9s ease both;
}

.cl2-card-desc {
    font-size: 0.82rem; color: #64748b; line-height: 1.65;
    margin: 0 0 16px;
}

/* Topic highlights */
.cl2-highlights {
    display: flex; gap: 6px; flex-wrap: wrap;
    margin-bottom: 16px;
}
.cl2-highlight-tag {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 7px;
    font-size: 0.68rem; font-weight: 600;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    color: #475569;
    transition: border-color 0.2s, color 0.2s;
}
.cl2-highlight-tag i { font-size: 0.55rem; color: #22c55e; }
.cl2-card-beginner .cl2-highlight-tag {
    border-color: rgba(16,185,129,0.1);
}
.cl2-card-advanced .cl2-highlight-tag {
    border-color: rgba(212,175,55,0.08);
}
.cl2-card:hover .cl2-highlight-tag {
    color: #64748b;
}

/* Tags (kept for days-away / day counter) */
.cl2-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.cl2-tag {
    padding: 4px 13px; border-radius: 99px;
    font-size: 0.7rem; font-weight: 700;
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.18);
    color: #d4af37;
}
.cl2-tag-green {
    background: rgba(34,197,94,0.08);
    border-color: rgba(34,197,94,0.2);
    color: #4ade80;
}

/* Progress label (live) */
.cl2-progress-label {
    display: flex; justify-content: space-between; align-items: center;
    font-size: 0.72rem; color: #475569; margin-bottom: 6px;
}
.cl2-progress-label strong { color: #94a3b8; }
.cl2-progress-track {
    height: 4px; background: rgba(255,255,255,0.06);
    border-radius: 99px; overflow: hidden; margin-bottom: 20px;
}
.cl2-progress-fill {
    height: 100%; width: 0;
    background: linear-gradient(90deg, #d4af37, #facc15);
    border-radius: 99px;
    transition: width 1.2s 0.5s cubic-bezier(0.4,0,0.2,1);
}

/* Countdown (upcoming) */
.cl2-countdown {
    display: grid; grid-template-columns: repeat(4,1fr); gap: 6px;
    margin-bottom: 20px;
    padding: 12px;
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: 14px;
}
.cl2-cd-block {
    text-align: center;
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 10px; padding: 10px 6px;
    position: relative;
}
.cl2-cd-block::after {
    content: '';
    position: absolute; top: 0; left: 50%; transform: translateX(-50%);
    width: 30%; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(212,175,55,0.4), transparent);
}
.cl2-card-beginner .cl2-cd-block::after {
    background: linear-gradient(90deg, transparent, rgba(16,185,129,0.4), transparent);
}
.cl2-cd-num {
    display: block; font-size: 1.35rem; font-weight: 900; color: #d4af37;
    line-height: 1; animation: cl-countdownTick 1s ease-in-out infinite;
}
.cl2-card-beginner .cl2-cd-num { color: #34d399; }
.cl2-cd-label {
    font-size: 0.56rem; text-transform: uppercase; letter-spacing: 1.5px;
    color: #334155; margin-top: 4px; display: block;
}

/* CTA buttons */
.cl2-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 14px 20px; border-radius: 14px; width: 100%;
    font-weight: 800; font-size: 0.9rem; text-decoration: none;
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
    position: relative; overflow: hidden;
}
.cl2-btn::before {
    content: '';
    position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
    transition: left 0.5s ease;
}
.cl2-btn:hover::before { left: 100%; }
.cl2-btn:hover { transform: translateY(-2px); }

.cl2-btn-live {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #fff;
    box-shadow: 0 4px 18px rgba(34,197,94,0.25);
}
.cl2-btn-live:hover { box-shadow: 0 8px 28px rgba(34,197,94,0.45); }

.cl2-btn-enroll {
    background: linear-gradient(135deg, #d4af37, #facc15);
    color: #020617;
    box-shadow: 0 4px 18px rgba(212,175,55,0.25);
}
.cl2-btn-enroll:hover { box-shadow: 0 8px 28px rgba(212,175,55,0.45); }

/* Beginner upcoming → teal enroll button */
.cl2-card-beginner .cl2-btn-enroll {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    box-shadow: 0 4px 18px rgba(16,185,129,0.28);
}
.cl2-card-beginner .cl2-btn-enroll:hover {
    box-shadow: 0 8px 28px rgba(16,185,129,0.48);
}

/* ── Empty state ─────────────────────────────────────────────── */
.cl2-empty {
    text-align: center; padding: 70px 20px;
    background: rgba(255,255,255,0.015);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 24px; margin-bottom: 70px;
}
.cl2-empty-icon {
    width: 64px; height: 64px; border-radius: 18px;
    background: rgba(212,175,55,0.06); border: 1px solid rgba(212,175,55,0.12);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
}
.cl2-empty-icon i { font-size: 1.8rem; color: rgba(212,175,55,0.3); }
.cl2-empty h3 { color: #64748b; margin: 0 0 8px; font-size: 1rem; }
.cl2-empty p  { color: #334155; font-size: 0.85rem; margin: 0; }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 768px) {
    .cl2-announce { padding: 10px 16px; gap: 7px; }
    .cl2-stats { flex-wrap: wrap; }
    .cl2-stat  { padding: 14px 20px; }
    .cl2-grid  { grid-template-columns: 1fr; }
    .cl2-countdown { padding: 10px; }
    .cl2-cd-num { font-size: 1.15rem; }
}
</style>

<div class="cl2-page">

<!-- ════════════════ ANNOUNCEMENT ════════════════ -->
<div class="cl2-announce">
    <span class="cl2-announce-dot"></span>
    <span class="cl2-announce-text">
        <strong>New Class:</strong> Stock Investment Mastery Batch 3 — Jun 22–26, 2026 · Beginner Friendly
    </span>
    <a href="#upcoming" class="cl2-announce-link">View Details →</a>
</div>

<!-- ════════════════ HERO ════════════════ -->
<section class="cl2-hero">
    <div class="cl2-grid-bg"></div>
    <div class="cl2-orb cl2-orb-1"></div>
    <div class="cl2-orb cl2-orb-2"></div>
    <div class="cl2-orb cl2-orb-3"></div>

    <div class="cl2-particles">
        <div class="cl2-particle" style="left:5%;  --dur:9s;  --delay:0s;"></div>
        <div class="cl2-particle" style="left:18%; --dur:12s; --delay:-4s;"></div>
        <div class="cl2-particle" style="left:33%; --dur:8s;  --delay:-7s;"></div>
        <div class="cl2-particle" style="left:52%; --dur:11s; --delay:-2s;"></div>
        <div class="cl2-particle" style="left:68%; --dur:10s; --delay:-5s;"></div>
        <div class="cl2-particle" style="left:82%; --dur:7s;  --delay:-9s;"></div>
        <div class="cl2-particle" style="left:95%; --dur:13s; --delay:-1s;"></div>
    </div>

    <div class="cl2-hero-inner">
        <div class="cl2-badge"><i class="fas fa-graduation-cap"></i> Wise Invest Academy</div>

        <h1 class="cl2-hero-title">
            Level Up Your<br><span class="cl2-gold">Trading Skills</span>
        </h1>

        <p class="cl2-hero-sub">
            Expert-led master classes, practical strategies, and real results for serious investors.
        </p>

        <div class="cl2-stats">
            <div class="cl2-stat">
                <strong><?php echo count($all_classes); ?></strong>
                <span>Classes</span>
            </div>
            <div class="cl2-stat">
                <strong><?php echo count($current_classes); ?></strong>
                <span>Live Now</span>
            </div>
            <div class="cl2-stat">
                <strong><?php echo count($upcoming_classes); ?></strong>
                <span>Upcoming</span>
            </div>
        </div>

        <div class="cl2-scroll-cue">
            <i class="fas fa-chevron-down"></i>
            <span>Browse Classes</span>
        </div>
    </div>
</section>

<!-- ════════════════ BODY ════════════════ -->
<div class="cl2-body">
<div class="cl2-inner">

    <!-- ── LIVE CLASSES ── -->
    <div class="cl2-sec-head">
        <div class="cl2-live-badge">
            <span class="cl2-live-dot"></span> Live
        </div>
        <h2>Live Classes</h2>
    </div>

    <?php if (empty($current_classes)): ?>
    <div class="cl2-empty">
        <div class="cl2-empty-icon"><i class="fas fa-broadcast-tower"></i></div>
        <h3>No live classes right now</h3>
        <p>Check upcoming classes below or come back soon.</p>
    </div>
    <?php else: ?>
    <div class="cl2-grid">
    <?php foreach ($current_classes as $i => $class):
        $start       = new DateTime($class['start_date']);
        $now         = new DateTime($today);
        $total_days  = 30;
        $days_passed = (int)$start->diff($now)->days;
        $progress    = min(100, round(($days_passed / $total_days) * 100));
        $level_cls   = 'cl2-card-' . strtolower($class['level']);
    ?>
    <div class="cl2-card cl2-card-live <?php echo $level_cls; ?>" data-delay="<?php echo $i * 90; ?>" data-progress="<?php echo $progress; ?>">
        <div class="cl2-card-border"></div>
        <div class="cl2-thumb">
            <img src="<?php echo htmlspecialchars($class['image']); ?>" alt="<?php echo htmlspecialchars($class['title']); ?>" class="cl2-thumb-img" loading="lazy">
            <div class="cl2-thumb-overlay"></div>
            <span class="cl2-status cl2-status-live">
                <span class="cl2-status-dot"></span> LIVE
            </span>
            <div class="cl2-duration-badge">
                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($class['duration']); ?>
            </div>
            <div class="cl2-progress-wrap">
                <div class="cl2-progress-bar" style="--w:<?php echo $progress; ?>%"></div>
            </div>
        </div>
        <div class="cl2-card-body">
            <div class="cl2-meta">
                <span class="cl2-meta-item"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($class['display_date']); ?></span>
                <span class="cl2-level-tag cl2-level-<?php echo strtolower($class['level']); ?>">
                    <i class="fas fa-signal"></i> <?php echo htmlspecialchars($class['level']); ?>
                </span>
            </div>
            <?php if (!empty($class['schedule'])): ?>
            <div class="cl2-schedule-row">
                <i class="fas fa-calendar-week"></i>
                <span><?php echo htmlspecialchars($class['schedule']); ?></span>
            </div>
            <?php endif; ?>
            <div class="cl2-title-row">
                <h3 class="cl2-card-title"><?php echo htmlspecialchars($class['title']); ?></h3>
                <?php if (!empty($class['batch'])): ?>
                <span class="cl2-batch-label"><?php echo htmlspecialchars($class['batch']); ?></span>
                <?php endif; ?>
            </div>
            <p class="cl2-card-desc"><?php echo htmlspecialchars($class['desc']); ?></p>
            <?php if (!empty($class['highlights'])): ?>
            <div class="cl2-highlights">
                <?php foreach ($class['highlights'] as $h): ?>
                <span class="cl2-highlight-tag"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($h); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="cl2-tags">
                <span class="cl2-tag cl2-tag-green">Day <?php echo $days_passed; ?> / <?php echo $total_days; ?></span>
            </div>
            <div class="cl2-progress-label">
                <span>Class Progress</span>
                <strong><?php echo $progress; ?>%</strong>
            </div>
            <div class="cl2-progress-track">
                <div class="cl2-progress-fill" data-width="<?php echo $progress; ?>"></div>
            </div>
            <a href="contact.php?course=<?php echo urlencode($class['title']); ?>" class="cl2-btn cl2-btn-live">
                <i class="fas fa-play-circle"></i> Join Live Class
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── UPCOMING CLASSES ── -->
    <div class="cl2-sec-head" id="upcoming">
        <i class="cl2-upcoming-icon fas fa-calendar-star"></i>
        <h2>Upcoming Classes</h2>
    </div>

    <?php if (empty($upcoming_classes)): ?>
    <div class="cl2-empty">
        <div class="cl2-empty-icon"><i class="fas fa-calendar-times"></i></div>
        <h3>No upcoming classes scheduled</h3>
        <p>Stay tuned — new classes are added regularly.</p>
    </div>
    <?php else: ?>
    <div class="cl2-grid">
    <?php foreach ($upcoming_classes as $i => $class):
        $start      = new DateTime($class['start_date']);
        $now        = new DateTime($today);
        $diff       = $now->diff($start);
        $days_left  = (int)$diff->days;
        $level_cls  = 'cl2-card-' . strtolower($class['level']);
    ?>
    <div class="cl2-card <?php echo $level_cls; ?>" data-delay="<?php echo $i * 90; ?>">
        <div class="cl2-card-border"></div>
        <div class="cl2-thumb">
            <img src="<?php echo htmlspecialchars($class['image']); ?>" alt="<?php echo htmlspecialchars($class['title']); ?>" class="cl2-thumb-img" loading="lazy">
            <div class="cl2-thumb-overlay"></div>
            <span class="cl2-status cl2-status-open">
                <i class="fas fa-door-open" style="font-size:.75rem;"></i> ENROLLING
            </span>
            <div class="cl2-duration-badge">
                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($class['duration']); ?>
            </div>
        </div>
        <div class="cl2-card-body">
            <div class="cl2-meta">
                <span class="cl2-meta-item"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($class['display_date']); ?></span>
                <span class="cl2-level-tag cl2-level-<?php echo strtolower($class['level']); ?>">
                    <i class="fas fa-signal"></i> <?php echo htmlspecialchars($class['level']); ?>
                </span>
            </div>
            <?php if (!empty($class['schedule'])): ?>
            <div class="cl2-schedule-row">
                <i class="fas fa-calendar-week"></i>
                <span><?php echo htmlspecialchars($class['schedule']); ?></span>
            </div>
            <?php endif; ?>
            <div class="cl2-title-row">
                <h3 class="cl2-card-title"><?php echo htmlspecialchars($class['title']); ?></h3>
                <?php if (!empty($class['batch'])): ?>
                <span class="cl2-batch-label"><?php echo htmlspecialchars($class['batch']); ?></span>
                <?php endif; ?>
            </div>
            <p class="cl2-card-desc"><?php echo htmlspecialchars($class['desc']); ?></p>
            <?php if (!empty($class['highlights'])): ?>
            <div class="cl2-highlights">
                <?php foreach ($class['highlights'] as $h): ?>
                <span class="cl2-highlight-tag"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($h); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="cl2-tags">
                <span class="cl2-tag"><?php echo $days_left; ?> days away</span>
            </div>

            <!-- Countdown -->
            <div class="cl2-countdown"
                data-days="<?php echo $days_left; ?>"
                data-hours="<?php echo (int)date('H'); ?>"
                data-mins="<?php echo (int)date('i'); ?>"
                data-secs="<?php echo (int)date('s'); ?>">
                <div class="cl2-cd-block">
                    <span class="cl2-cd-num cl2-cd-d"><?php echo $days_left; ?></span>
                    <span class="cl2-cd-label">Days</span>
                </div>
                <div class="cl2-cd-block">
                    <span class="cl2-cd-num cl2-cd-h"><?php echo (int)date('H'); ?></span>
                    <span class="cl2-cd-label">Hours</span>
                </div>
                <div class="cl2-cd-block">
                    <span class="cl2-cd-num cl2-cd-m"><?php echo (int)date('i'); ?></span>
                    <span class="cl2-cd-label">Mins</span>
                </div>
                <div class="cl2-cd-block">
                    <span class="cl2-cd-num cl2-cd-s"><?php echo (int)date('s'); ?></span>
                    <span class="cl2-cd-label">Secs</span>
                </div>
            </div>

            <a href="contact.php?course=<?php echo urlencode($class['title']); ?>" class="cl2-btn cl2-btn-enroll">
                <i class="fas fa-user-plus"></i> Enroll Now — <?php echo $days_left; ?> days left
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</div>

</div><!-- /.cl2-page -->

<script>
/* ── Scroll reveal ── */
(function() {
    const cards = document.querySelectorAll('.cl2-card');
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                const d = parseInt(e.target.dataset.delay) || 0;
                setTimeout(() => {
                    e.target.classList.add('visible');
                    e.target.querySelectorAll('.cl2-progress-fill').forEach(bar => {
                        bar.style.width = bar.dataset.width + '%';
                    });
                    e.target.querySelectorAll('.cl2-progress-bar').forEach(bar => {
                        bar.style.width = bar.style.getPropertyValue('--w') || '0%';
                    });
                }, d);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    cards.forEach(c => obs.observe(c));
}());

/* ── Live countdown timers ── */
(function() {
    document.querySelectorAll('.cl2-countdown').forEach(function(cd) {
        const daysLeft = parseInt(cd.dataset.days) || 0;
        const target   = new Date();
        target.setDate(target.getDate() + daysLeft);
        target.setHours(9, 0, 0, 0);

        function tick() {
            const now  = new Date();
            const diff = target - now;
            if (diff <= 0) {
                cd.innerHTML = '<span style="color:#4ade80;font-size:.8rem;font-weight:700;grid-column:1/-1;text-align:center;padding:8px;">Starting Soon!</span>';
                return;
            }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            const fmt = n => String(n).padStart(2, '0');
            cd.querySelector('.cl2-cd-d').textContent = fmt(d);
            cd.querySelector('.cl2-cd-h').textContent = fmt(h);
            cd.querySelector('.cl2-cd-m').textContent = fmt(m);
            cd.querySelector('.cl2-cd-s').textContent = fmt(s);
        }
        tick();
        setInterval(tick, 1000);
    });
}());
</script>

<?php include 'includes/footer.php'; ?>
