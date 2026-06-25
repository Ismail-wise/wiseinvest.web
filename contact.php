<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'includes/db.php';
require_once __DIR__ . '/includes/translations.php';

$ct_status  = '';
$ct_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ct_submit'])) {
    $name    = trim($_POST['ct_name']    ?? '');
    $email   = trim($_POST['ct_email']   ?? '');
    $subject = trim($_POST['ct_subject'] ?? '');
    $msg     = trim($_POST['ct_msg']     ?? '');

    if (!$name || !$email || !$msg) {
        $ct_status  = 'error';
        $ct_message = t('ct_err_fields');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $ct_status  = 'error';
        $ct_message = t('ct_err_email');
    } else {
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, package_interest, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $msg);
            if ($stmt->execute()) {
                $ct_status  = 'success';
                $ct_message = t('ct_success_msg');
            } else {
                $ct_status  = 'error';
                $ct_message = t('ct_err_send');
            }
        } else {
            $ct_status  = 'error';
            $ct_message = t('ct_err_send');
        }
    }
}

include 'includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.1/vanilla-tilt.min.js"></script>

<style>
/* ═══════════════════════════════════════════════════════════
   CONTACT PAGE
   ═══════════════════════════════════════════════════════════ */
:root {
  --ct-gold:   #d4af37;
  --ct-gold2:  #facc15;
  --ct-ink:    #f4f7ff;
  --ct-muted:  rgba(244,247,255,.6);
  --ct-bg:     #020617;
  --ct-card:   rgba(255,255,255,.03);
  --ct-border: rgba(255,255,255,.08);
}

/* ── keyframes ─────────────────────────────────────────────── */
@keyframes ct-grid-pan    { to { background-position: 52px 52px; } }
@keyframes ct-orb-drift   { from{transform:translate(0,0) scale(1)} to{transform:translate(30px,20px) scale(1.08)} }
@keyframes ct-hero-up     { from{opacity:0;transform:translateY(26px)} to{opacity:1;transform:none} }
@keyframes ct-spin        { to { transform: rotate(360deg); } }
@keyframes ct-particle    { 0%{opacity:0;transform:translateY(0)} 15%{opacity:1} 100%{opacity:0;transform:translateY(-90vh)} }
@keyframes ct-toast-drain { from{transform:scaleX(1)} to{transform:scaleX(0)} }
@keyframes ct-shimmer-btn { from{left:-60%} to{left:110%} }
@keyframes ct-badge-pop   { 0%{transform:scale(.7);opacity:0} 80%{transform:scale(1.06)} 100%{transform:scale(1);opacity:1} }
@keyframes ct-divider-grow{ from{transform:scaleX(0)} to{transform:scaleX(1)} }
@keyframes ct-chip-in     { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:none} }
@keyframes ct-border-flow {
  0%   { background-position: 0% 50%; }
  50%  { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
@keyframes ct-glow-pulse  { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.15)} 50%{box-shadow:0 0 20px 4px rgba(212,175,55,.08)} }
@keyframes ct-icon-bounce { 0%,100%{transform:translateY(0)} 40%{transform:translateY(-3px)} }

/* ── page wrapper ──────────────────────────────────────────── */
.ct-wrap {
  background: var(--ct-bg);
  color: var(--ct-ink);
  min-height: 100vh;
  padding: 140px 5% 100px;
  position: relative;
  overflow: hidden;
  font-family: 'Inter', sans-serif;
}

/* ── grid bg ───────────────────────────────────────────────── */
.ct-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(212,175,55,.035) 1px, transparent 1px),
    linear-gradient(90deg, rgba(212,175,55,.035) 1px, transparent 1px);
  background-size: 52px 52px;
  animation: ct-grid-pan 20s linear infinite;
  pointer-events: none; z-index: 0;
}

/* ── orbs ──────────────────────────────────────────────────── */
.ct-orb {
  position: absolute; border-radius: 50%;
  filter: blur(80px); pointer-events: none; z-index: 0;
}
.ct-orb-1 {
  width: 480px; height: 480px;
  background: radial-gradient(circle, rgba(212,175,55,.13), transparent 70%);
  top: -120px; left: -120px;
  animation: ct-orb-drift 12s ease-in-out infinite alternate;
}
.ct-orb-2 {
  width: 360px; height: 360px;
  background: radial-gradient(circle, rgba(250,204,21,.08), transparent 70%);
  bottom: 40px; right: -80px;
  animation: ct-orb-drift 16s ease-in-out infinite alternate-reverse;
}
.ct-orb-3 {
  width: 280px; height: 280px;
  background: radial-gradient(circle, rgba(99,102,241,.07), transparent 70%);
  top: 55%; left: 48%;
  animation: ct-orb-drift 19s ease-in-out infinite alternate;
}

/* ── particles ─────────────────────────────────────────────── */
.ct-particles { position: absolute; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
.ct-particle {
  position: absolute;
  left: var(--x); bottom: -6px;
  width: 2px; height: 2px; border-radius: 50%;
  background: rgba(212,175,55,.55);
  animation: ct-particle var(--dur,10s) var(--del,0s) ease-in infinite;
}

/* ══════════════════════════════════════════════════════════════
   HERO
   ══════════════════════════════════════════════════════════════ */
.ct-hero {
  text-align: center;
  margin-bottom: 64px;
  position: relative; z-index: 10;
}
.ct-hero-label {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: .71rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 3px; color: var(--ct-gold);
  background: rgba(212,175,55,.08);
  border: 1px solid rgba(212,175,55,.22);
  border-radius: 99px; padding: 6px 20px;
  margin-bottom: 20px;
  animation: ct-badge-pop .6s .05s ease both;
}
.ct-hero h1 {
  font-size: clamp(2.4rem,5vw,3.8rem);
  font-weight: 900; letter-spacing: -1px;
  background: linear-gradient(135deg, #fff 20%, var(--ct-gold) 60%, var(--ct-gold2));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  margin-bottom: 14px; line-height: 1.1;
  animation: ct-hero-up .7s .15s ease both;
}
.ct-hero p {
  font-size: 1.02rem; color: var(--ct-muted);
  max-width: 500px; margin: 0 auto;
  line-height: 1.7;
  animation: ct-hero-up .6s .25s ease both;
}

/* Divider under sub */
.ct-hero-divider {
  width: 60px; height: 2px; margin: 24px auto 20px;
  background: linear-gradient(90deg, transparent, var(--ct-gold), transparent);
  transform-origin: center;
  animation: ct-divider-grow .6s .32s ease both;
}

/* Platform chips */
.ct-hero-platforms {
  display: flex; align-items: center; justify-content: center;
  gap: 10px; margin-top: 4px;
  animation: ct-chip-in .5s .4s ease both;
}
.ct-platform-chip {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; color: rgba(244,247,255,.45);
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.07);
  transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s, color .25s, border-color .25s, background .25s;
}
.ct-platform-chip.fb:hover { color:#1877F2; background:rgba(24,119,242,.08); border-color:rgba(24,119,242,.25); box-shadow:0 8px 20px rgba(24,119,242,.2); transform:translateY(-4px); }
.ct-platform-chip.wa:hover { color:#25D366; background:rgba(37,211,102,.08); border-color:rgba(37,211,102,.25); box-shadow:0 8px 20px rgba(37,211,102,.2); transform:translateY(-4px); }
.ct-platform-chip.tg:hover { color:#26A5E4; background:rgba(38,165,228,.08); border-color:rgba(38,165,228,.25); box-shadow:0 8px 20px rgba(38,165,228,.2); transform:translateY(-4px); }
.ct-platform-chip.li:hover { color:#00C300; background:rgba(0,195,0,.08);   border-color:rgba(0,195,0,.25);   box-shadow:0 8px 20px rgba(0,195,0,.2);   transform:translateY(-4px); }
.ct-platform-chip.em:hover { color:var(--ct-gold); background:rgba(212,175,55,.08); border-color:rgba(212,175,55,.25); box-shadow:0 8px 20px rgba(212,175,55,.2); transform:translateY(-4px); }

/* ══════════════════════════════════════════════════════════════
   LAYOUT
   ══════════════════════════════════════════════════════════════ */
.ct-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px;
  max-width: 1100px;
  margin: 0 auto;
  position: relative; z-index: 10;
}
@media (max-width: 860px) { .ct-layout { grid-template-columns: 1fr; } }

/* ── glass panels ──────────────────────────────────────────── */
.ct-panel {
  background: var(--ct-card);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid var(--ct-border);
  border-radius: 26px;
  padding: 36px 34px;
  transition: border-color .35s, box-shadow .35s;
  position: relative; overflow: hidden;
}
/* Animated top edge line */
.ct-panel::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent 0%, var(--ct-gold) 40%, var(--ct-gold2) 60%, transparent 100%);
  background-size: 200% auto;
  opacity: 0;
  transition: opacity .35s;
}
/* Corner glow */
.ct-panel::after {
  content: '';
  position: absolute; bottom: 0; right: 0;
  width: 80px; height: 80px;
  background: radial-gradient(circle at bottom right, rgba(212,175,55,.07), transparent 70%);
  border-radius: 0 0 26px 0;
  pointer-events: none;
}
.ct-panel:hover {
  border-color: rgba(212,175,55,.28);
  box-shadow: 0 24px 64px rgba(0,0,0,.55);
}
.ct-panel:hover::before {
  opacity: 1;
  animation: ct-border-flow 2.5s linear infinite;
}

.ct-panel-title {
  font-size: 1.18rem; font-weight: 800; color: var(--ct-ink);
  margin-bottom: 26px; display: flex; align-items: center; gap: 12px;
}
.ct-panel-title-icon {
  width: 40px; height: 40px; flex-shrink: 0;
  background: linear-gradient(135deg, rgba(212,175,55,.15), rgba(212,175,55,.05));
  border: 1px solid rgba(212,175,55,.22);
  border-radius: 11px; display: flex; align-items: center; justify-content: center;
  font-size: .9rem; color: var(--ct-gold);
  transition: transform .3s, box-shadow .3s;
}
.ct-panel:hover .ct-panel-title-icon {
  transform: rotate(-6deg) scale(1.06);
  box-shadow: 0 6px 18px rgba(212,175,55,.2);
  animation: ct-icon-bounce 1.2s ease infinite;
}

/* ══════════════════════════════════════════════════════════════
   CHANNELS
   ══════════════════════════════════════════════════════════════ */
.ct-channels { display: flex; flex-direction: column; gap: 10px; }

.ct-channel {
  display: flex; align-items: center; justify-content: space-between;
  padding: 13px 16px;
  background: rgba(255,255,255,.025);
  border: 1px solid rgba(255,255,255,.06);
  border-radius: 16px;
  text-decoration: none; color: inherit;
  transition: background .25s, border-color .25s, transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
  position: relative; overflow: hidden;
}
.ct-channel::before {
  content: '';
  position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
  border-radius: 16px 0 0 16px;
  background: var(--ch-accent, var(--ct-gold));
  opacity: 0;
  transition: opacity .25s;
}
.ct-channel:hover {
  background: rgba(255,255,255,.04);
  transform: translateX(6px);
  box-shadow: 0 8px 24px rgba(0,0,0,.3);
}
.ct-channel:hover::before { opacity: 1; }

.ct-channel.fb  { --ch-accent: #1877F2; }
.ct-channel.wa  { --ch-accent: #25D366; }
.ct-channel.tg  { --ch-accent: #26A5E4; }
.ct-channel.li  { --ch-accent: #00C300; }
.ct-channel.em  { --ch-accent: var(--ct-gold); }

.ct-channel.fb:hover  { border-color: rgba(24,119,242,.2); }
.ct-channel.wa:hover  { border-color: rgba(37,211,102,.2); }
.ct-channel.tg:hover  { border-color: rgba(38,165,228,.2); }
.ct-channel.li:hover  { border-color: rgba(0,195,0,.2); }
.ct-channel.em:hover  { border-color: rgba(212,175,55,.2); }

.ct-channel-left { display: flex; align-items: center; gap: 14px; }
.ct-ch-icon {
  width: 46px; height: 46px; border-radius: 13px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.07);
  transition: all .28s;
  flex-shrink: 0;
  color: rgba(244,247,255,.5);
}
.ct-channel.fb:hover .ct-ch-icon { color:#1877F2; background:rgba(24,119,242,.08);  border-color:rgba(24,119,242,.25);  box-shadow:0 0 18px rgba(24,119,242,.25); }
.ct-channel.wa:hover .ct-ch-icon { color:#25D366; background:rgba(37,211,102,.08); border-color:rgba(37,211,102,.25); box-shadow:0 0 18px rgba(37,211,102,.25); }
.ct-channel.tg:hover .ct-ch-icon { color:#26A5E4; background:rgba(38,165,228,.08); border-color:rgba(38,165,228,.25); box-shadow:0 0 18px rgba(38,165,228,.25); }
.ct-channel.li:hover .ct-ch-icon { color:#00C300; background:rgba(0,195,0,.08);    border-color:rgba(0,195,0,.25);    box-shadow:0 0 18px rgba(0,195,0,.25); }
.ct-channel.em:hover .ct-ch-icon { color:var(--ct-gold); background:rgba(212,175,55,.08); border-color:rgba(212,175,55,.25); box-shadow:0 0 18px rgba(212,175,55,.25); }

.ct-channel-info span   { font-size: .68rem; color: var(--ct-muted); display: block; margin-bottom: 2px; letter-spacing: .3px; }
.ct-channel-info strong { font-size: .87rem; font-weight: 700; color: var(--ct-ink); }

.ct-ch-arrow {
  font-size: .72rem; color: var(--ct-muted);
  transition: transform .25s, color .25s;
  flex-shrink: 0;
}
.ct-channel:hover .ct-ch-arrow { color: var(--ct-gold); transform: translate(3px, -3px); }

.ct-copy-btn {
  background: none; border: none; color: var(--ct-muted);
  cursor: pointer; padding: 7px 9px; border-radius: 9px;
  transition: color .2s, background .2s; font-size: .85rem; line-height: 1;
  flex-shrink: 0;
}
.ct-copy-btn:hover { color: var(--ct-gold); background: rgba(212,175,55,.1); }

/* ── Response time box ─────────────────────────────────────── */
.ct-response-box {
  margin-top: 24px;
  padding: 16px 18px;
  background: rgba(212,175,55,.05);
  border: 1px solid rgba(212,175,55,.12);
  border-radius: 16px;
  display: flex; align-items: flex-start; gap: 14px;
  position: relative; overflow: hidden;
}
.ct-response-box::before {
  content: '';
  position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
  background: linear-gradient(to bottom, var(--ct-gold), rgba(212,175,55,.2));
  border-radius: 16px 0 0 16px;
}
.ct-response-icon {
  width: 36px; height: 36px; flex-shrink: 0;
  border-radius: 10px;
  background: rgba(212,175,55,.1);
  border: 1px solid rgba(212,175,55,.18);
  display: flex; align-items: center; justify-content: center;
  color: var(--ct-gold); font-size: .85rem;
  animation: ct-glow-pulse 2.5s ease-in-out infinite;
}
.ct-response-title {
  font-size: .8rem; font-weight: 700;
  color: var(--ct-ink); margin-bottom: 4px;
}
.ct-response-detail {
  font-size: .74rem; color: var(--ct-muted); line-height: 1.5;
}

/* ══════════════════════════════════════════════════════════════
   FORM
   ══════════════════════════════════════════════════════════════ */
.ct-field { margin-bottom: 18px; }
.ct-field label {
  font-size: .72rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 1.2px; color: var(--ct-muted);
  display: block; margin-bottom: 8px;
  transition: color .25s;
}
.ct-field:focus-within label { color: var(--ct-gold); }

/* Input wrapper for icon */
.ct-input-wrap { position: relative; }
.ct-field-ico {
  position: absolute;
  left: 16px; top: 50%; transform: translateY(-50%);
  font-size: .82rem;
  color: rgba(212,175,55,.3);
  pointer-events: none; z-index: 1;
  transition: color .25s, transform .25s;
}
.ct-textarea-wrap .ct-field-ico {
  top: 16px; transform: none;
}
.ct-field:focus-within .ct-field-ico {
  color: rgba(212,175,55,.75);
}
.ct-field:focus-within .ct-input-wrap:not(.ct-textarea-wrap) .ct-field-ico {
  transform: translateY(-50%) scale(1.1);
}

.ct-input {
  width: 100%;
  background: rgba(0,0,0,.4);
  border: 1px solid rgba(255,255,255,.09);
  border-radius: 14px;
  padding: 14px 18px 14px 44px;
  color: #fff; font-size: .9rem;
  font-family: inherit;
  transition: border-color .25s, box-shadow .25s, background .25s;
  box-sizing: border-box;
}
.ct-input::placeholder { color: rgba(244,247,255,.2); }
.ct-input:focus {
  outline: none;
  background: rgba(0,0,0,.55);
  border-color: rgba(212,175,55,.5);
  box-shadow: 0 0 0 3px rgba(212,175,55,.1), inset 0 1px 0 rgba(255,255,255,.04);
}
textarea.ct-input {
  resize: vertical; min-height: 126px;
  padding-bottom: 32px;
}

/* Character counter */
.ct-char-wrap {
  position: absolute; bottom: 10px; right: 14px;
  pointer-events: none;
}
.ct-char-count {
  font-size: .63rem; font-variant-numeric: tabular-nums;
  color: rgba(244,247,255,.2);
  transition: color .2s;
}
.ct-char-count.ct-warn  { color: rgba(251,191,36,.55); }
.ct-char-count.ct-limit { color: rgba(248,113,113,.7); }

/* ── submit button ─────────────────────────────────────────── */
.ct-submit {
  width: 100%; padding: 15px;
  border-radius: 14px; border: none;
  background: linear-gradient(135deg, #b8942a, var(--ct-gold), var(--ct-gold2));
  background-size: 200% auto;
  color: #020617;
  font-weight: 900; font-size: .88rem;
  text-transform: uppercase; letter-spacing: 2px;
  cursor: pointer; position: relative; overflow: hidden;
  transition: letter-spacing .3s, box-shadow .3s, opacity .2s, background-position .4s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.ct-submit-shine {
  position: absolute; top: 0; height: 100%; width: 35%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.22), transparent);
  transform: skewX(-18deg);
  left: -60%;
  pointer-events: none;
}
.ct-submit:hover { letter-spacing: 3px; box-shadow: 0 16px 44px rgba(212,175,55,.4); }
.ct-submit:hover .ct-submit-shine { animation: ct-shimmer-btn .55s ease forwards; }
.ct-submit:disabled { opacity: .65; cursor: not-allowed; letter-spacing: 2px; }
.ct-submit:disabled .ct-submit-shine { display: none; }
.ct-submit.success {
  background: linear-gradient(135deg, #15803d, #22c55e);
  box-shadow: 0 12px 32px rgba(34,197,94,.3);
}

/* ── spinner ───────────────────────────────────────────────── */
.ct-spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(2,6,23,.25);
  border-top-color: #020617;
  border-radius: 50%;
  animation: ct-spin .7s linear infinite;
  display: none; flex-shrink: 0;
}

/* ── toast ─────────────────────────────────────────────────── */
.ct-toast {
  position: fixed; bottom: 32px; right: 32px; z-index: 9999;
  background: rgba(8,12,26,.96);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(212,175,55,.3);
  border-radius: 16px; padding: 16px 22px 18px;
  display: flex; align-items: center; gap: 12px;
  font-size: .88rem; color: var(--ct-ink);
  transform: translateX(110%); opacity: 0;
  transition: transform .45s cubic-bezier(.34,1.56,.64,1), opacity .35s ease;
  pointer-events: none; max-width: 340px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,.6);
}
.ct-toast.show { transform: translateX(0); opacity: 1; pointer-events: auto; }
.ct-toast-icon { font-size: 1.15rem; color: var(--ct-gold); flex-shrink: 0; }
.ct-toast-text { flex: 1; line-height: 1.4; }
.ct-toast-bar {
  position: absolute; bottom: 0; left: 0;
  height: 3px; width: 100%;
  background: var(--ct-gold);
  transform-origin: left;
  border-radius: 0 0 16px 16px;
}
.ct-toast.show .ct-toast-bar {
  animation: ct-toast-drain 4s linear forwards;
}

/* ── scroll-reveal ─────────────────────────────────────────── */
.ct-reveal {
  opacity: 0; transform: translateY(28px);
  transition: opacity .65s ease, transform .65s ease;
}
.ct-reveal.ct-visible { opacity: 1; transform: none; }
.ct-reveal-l {
  opacity: 0; transform: translateX(-32px);
  transition: opacity .65s ease, transform .65s ease;
}
.ct-reveal-l.ct-visible { opacity: 1; transform: none; }
.ct-reveal-r {
  opacity: 0; transform: translateX(32px);
  transition: opacity .65s ease, transform .65s ease;
}
.ct-reveal-r.ct-visible { opacity: 1; transform: none; }

/* ── responsive ────────────────────────────────────────────── */
@media (max-width: 620px) {
  .ct-toast { bottom: 16px; right: 16px; left: 16px; max-width: none; transform: translateY(110%); }
  .ct-toast.show { transform: translateY(0); }
  .ct-hero-platforms { gap: 8px; }
  .ct-platform-chip { width: 36px; height: 36px; border-radius: 10px; font-size: .9rem; }
}
</style>

<div class="ct-wrap">
  <div class="ct-grid"></div>
  <div class="ct-orb ct-orb-1"></div>
  <div class="ct-orb ct-orb-2"></div>
  <div class="ct-orb ct-orb-3"></div>

  <!-- Particles -->
  <div class="ct-particles">
    <div class="ct-particle" style="--x:8%;  --dur:9s;  --del:0s;"></div>
    <div class="ct-particle" style="--x:22%; --dur:12s; --del:-3.5s;"></div>
    <div class="ct-particle" style="--x:40%; --dur:8s;  --del:-7s;"></div>
    <div class="ct-particle" style="--x:58%; --dur:11s; --del:-1.5s;"></div>
    <div class="ct-particle" style="--x:74%; --dur:10s; --del:-5s;"></div>
    <div class="ct-particle" style="--x:90%; --dur:7s;  --del:-9s;"></div>
  </div>

  <!-- HERO -->
  <div class="ct-hero">
    <div class="ct-hero-label"><i class="fas fa-satellite-dish"></i> <?php echo t('ct_label'); ?></div>
    <h1><?php echo t('ct_title'); ?></h1>
    <p><?php echo t('ct_sub'); ?></p>
    <div class="ct-hero-divider"></div>
    <div class="ct-hero-platforms">
      <div class="ct-platform-chip fb"><i class="fab fa-facebook-f"></i></div>
      <div class="ct-platform-chip wa"><i class="fab fa-whatsapp"></i></div>
      <div class="ct-platform-chip tg"><i class="fab fa-telegram-plane"></i></div>
      <div class="ct-platform-chip li"><i class="fab fa-line"></i></div>
      <div class="ct-platform-chip em"><i class="fas fa-envelope"></i></div>
    </div>
  </div>

  <!-- LAYOUT -->
  <div class="ct-layout">

    <!-- LEFT: channels -->
    <div class="ct-panel ct-reveal-l" data-tilt data-tilt-max="4" data-tilt-speed="500" data-tilt-glare data-tilt-max-glare="0.07">
      <div class="ct-panel-title">
        <div class="ct-panel-title-icon"><i class="fas fa-bolt"></i></div>
        <?php echo t('ct_quick_conn'); ?>
      </div>
      <div class="ct-channels">

        <a href="https://facebook.com/yourpage" target="_blank" class="ct-channel fb">
          <div class="ct-channel-left">
            <div class="ct-ch-icon"><i class="fab fa-facebook-f"></i></div>
            <div class="ct-channel-info"><span>Facebook</span><strong>Wise Invest</strong></div>
          </div>
          <i class="fas fa-arrow-up-right-from-square ct-ch-arrow"></i>
        </a>

        <div class="ct-channel wa" style="cursor:default;">
          <div class="ct-channel-left">
            <a href="https://wa.me/yournumber" target="_blank" style="display:flex;align-items:center;gap:14px;text-decoration:none;color:inherit;">
              <div class="ct-ch-icon"><i class="fab fa-whatsapp"></i></div>
              <div class="ct-channel-info"><span>WhatsApp</span><strong>+66 XX XXX XXXX</strong></div>
            </a>
          </div>
          <button class="ct-copy-btn" onclick="ctCopy('+66XXXXXXXXX', this)" title="Copy number"><i class="far fa-copy"></i></button>
        </div>

        <a href="https://t.me/yourusername" target="_blank" class="ct-channel tg">
          <div class="ct-channel-left">
            <div class="ct-ch-icon"><i class="fab fa-telegram-plane"></i></div>
            <div class="ct-channel-info"><span>Telegram</span><strong>@WiseInvest_Official</strong></div>
          </div>
          <i class="fas fa-arrow-up-right-from-square ct-ch-arrow"></i>
        </a>

        <a href="https://line.me/ti/p/yourid" target="_blank" class="ct-channel li">
          <div class="ct-channel-left">
            <div class="ct-ch-icon"><i class="fab fa-line"></i></div>
            <div class="ct-channel-info"><span>Line ID</span><strong>wise_invest</strong></div>
          </div>
          <i class="fas fa-arrow-up-right-from-square ct-ch-arrow"></i>
        </a>

        <div class="ct-channel em" style="cursor:default;">
          <div class="ct-channel-left">
            <a href="mailto:support@wiseinvest.com" style="display:flex;align-items:center;gap:14px;text-decoration:none;color:inherit;">
              <div class="ct-ch-icon"><i class="fas fa-envelope"></i></div>
              <div class="ct-channel-info"><span>Email Support</span><strong>support@wiseinvest.com</strong></div>
            </a>
          </div>
          <button class="ct-copy-btn" onclick="ctCopy('support@wiseinvest.com', this)" title="Copy email"><i class="far fa-copy"></i></button>
        </div>

      </div>

      <!-- Response time -->
      <div class="ct-response-box">
        <div class="ct-response-icon"><i class="fas fa-clock"></i></div>
        <div>
          <div class="ct-response-title"><?php echo t('ct_response_title'); ?></div>
          <div class="ct-response-detail"><?php echo t('ct_response_detail'); ?></div>
        </div>
      </div>
    </div>

    <!-- RIGHT: form -->
    <div class="ct-panel ct-reveal-r" data-tilt data-tilt-max="3" data-tilt-speed="500">
      <div class="ct-panel-title">
        <div class="ct-panel-title-icon"><i class="fas fa-paper-plane"></i></div>
        <?php echo t('ct_send_msg'); ?>
      </div>

      <form id="ctForm" method="POST" action="">
        <input type="hidden" name="ct_submit" value="1">

        <div class="ct-field">
          <label for="ct-name"><?php echo t('ct_field_name'); ?></label>
          <div class="ct-input-wrap">
            <i class="fas fa-user ct-field-ico"></i>
            <input type="text" id="ct-name" name="ct_name" class="ct-input"
                   placeholder="<?php echo t('ct_ph_name'); ?>" required
                   value="<?php echo htmlspecialchars($_POST['ct_name'] ?? '', ENT_QUOTES); ?>">
          </div>
        </div>

        <div class="ct-field">
          <label for="ct-email"><?php echo t('ct_field_email'); ?></label>
          <div class="ct-input-wrap">
            <i class="fas fa-at ct-field-ico"></i>
            <input type="email" id="ct-email" name="ct_email" class="ct-input"
                   placeholder="<?php echo t('ct_ph_email'); ?>" required
                   value="<?php echo htmlspecialchars($_POST['ct_email'] ?? '', ENT_QUOTES); ?>">
          </div>
        </div>

        <div class="ct-field">
          <label for="ct-subject"><?php echo t('ct_field_subject'); ?></label>
          <div class="ct-input-wrap">
            <i class="fas fa-pen ct-field-ico"></i>
            <input type="text" id="ct-subject" name="ct_subject" class="ct-input"
                   placeholder="<?php echo t('ct_ph_subject'); ?>"
                   value="<?php echo htmlspecialchars($_POST['ct_subject'] ?? '', ENT_QUOTES); ?>">
          </div>
        </div>

        <div class="ct-field">
          <label for="ct-msg"><?php echo t('ct_field_message'); ?></label>
          <div class="ct-input-wrap ct-textarea-wrap">
            <i class="fas fa-comment-dots ct-field-ico"></i>
            <textarea id="ct-msg" name="ct_msg" class="ct-input" rows="4"
                      placeholder="<?php echo t('ct_ph_message'); ?>"
                      maxlength="1000"
                      required><?php echo htmlspecialchars($_POST['ct_msg'] ?? '', ENT_QUOTES); ?></textarea>
            <div class="ct-char-wrap">
              <span class="ct-char-count" id="ctCharCount">0 / 1000</span>
            </div>
          </div>
        </div>

        <button type="submit" class="ct-submit" id="ctSubmit">
          <div class="ct-submit-shine"></div>
          <span class="ct-spinner" id="ctSpinner"></span>
          <i class="fas fa-paper-plane" id="ctBtnIcon"></i>
          <span id="ctBtnText"><?php echo t('ct_btn_send'); ?></span>
        </button>
      </form>
    </div>

  </div>
</div>

<!-- TOAST -->
<div class="ct-toast" id="ctToast">
  <i class="fas fa-circle-check ct-toast-icon"></i>
  <span class="ct-toast-text" id="ctToastMsg">Message sent! We'll get back to you shortly.</span>
  <div class="ct-toast-bar" id="ctToastBar"></div>
</div>

<script>
/* ── tilt ── */
if (typeof VanillaTilt !== 'undefined') {
  VanillaTilt.init(document.querySelectorAll('.ct-panel'), { max:4, speed:500, glare:true, 'max-glare':0.07 });
}

/* ── copy ── */
function ctCopy(text, btn) {
  navigator.clipboard.writeText(text).then(() => {
    const i = btn.querySelector('i');
    i.className = 'fas fa-check';
    btn.style.color = 'var(--ct-gold)';
    setTimeout(() => { i.className = 'far fa-copy'; btn.style.color = ''; }, 2000);
  });
}

/* ── toast ── */
function ctShowToast(msg, isError) {
  const t   = document.getElementById('ctToast');
  const m   = document.getElementById('ctToastMsg');
  const ic  = t.querySelector('.ct-toast-icon');
  const bar = document.getElementById('ctToastBar');
  m.textContent = msg;
  if (isError) {
    ic.className = 'fas fa-circle-exclamation ct-toast-icon';
    ic.style.color = '#f87171';
    t.style.borderColor = 'rgba(248,113,113,.3)';
    if (bar) bar.style.background = '#f87171';
  } else {
    ic.className = 'fas fa-circle-check ct-toast-icon';
    ic.style.color = 'var(--ct-gold)';
    t.style.borderColor = 'rgba(212,175,55,.3)';
    if (bar) bar.style.background = 'var(--ct-gold)';
  }
  t.classList.remove('show');
  void t.offsetWidth;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 4000);
}

/* ── PHP status → toast on page load ── */
<?php if ($ct_status === 'success'): ?>
window.addEventListener('DOMContentLoaded', () => {
  ctShowToast(<?php echo json_encode($ct_message); ?>, false);
  const btn  = document.getElementById('ctSubmit');
  const icon = document.getElementById('ctBtnIcon');
  const lbl  = document.getElementById('ctBtnText');
  icon.className = 'fas fa-circle-check';
  lbl.textContent = 'Message Sent!';
  btn.classList.add('success');
  setTimeout(() => {
    btn.classList.remove('success');
    icon.className = 'fas fa-paper-plane';
    lbl.textContent = 'Send Message';
  }, 3500);
});
<?php elseif ($ct_status === 'error'): ?>
window.addEventListener('DOMContentLoaded', () => {
  ctShowToast(<?php echo json_encode($ct_message); ?>, true);
});
<?php endif; ?>

/* ── form submit spinner ── */
document.getElementById('ctForm').addEventListener('submit', function() {
  const btn     = document.getElementById('ctSubmit');
  const spinner = document.getElementById('ctSpinner');
  const icon    = document.getElementById('ctBtnIcon');
  const label   = document.getElementById('ctBtnText');
  btn.disabled        = true;
  spinner.style.display = 'block';
  icon.style.display    = 'none';
  label.textContent     = 'Sending…';
});

/* ── character counter ── */
(function() {
  const area  = document.getElementById('ct-msg');
  const count = document.getElementById('ctCharCount');
  if (!area || !count) return;
  const max = parseInt(area.getAttribute('maxlength')) || 1000;
  area.addEventListener('input', function() {
    const len = this.value.length;
    count.textContent = len + ' / ' + max;
    count.classList.toggle('ct-warn',  len > max * 0.75);
    count.classList.toggle('ct-limit', len >= max);
  });
})();

/* ── scroll-reveal ── */
(function() {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(en => {
      if (en.isIntersecting) {
        const el = en.target;
        const d  = parseFloat(el.dataset.delay || 0);
        setTimeout(() => el.classList.add('ct-visible'), d);
        obs.unobserve(el);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.ct-reveal, .ct-reveal-l, .ct-reveal-r').forEach((el, i) => {
    el.dataset.delay = i * 120;
    obs.observe(el);
  });
})();
</script>

<?php include 'includes/footer.php'; ?>
