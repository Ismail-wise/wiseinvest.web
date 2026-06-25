<?php
// Load keys (VAPID public key must be available when JS is rendered)
if (!defined('VAPID_PUBLIC_KEY')) {
    $__keys = __DIR__ . '/../config/keys.php';
    if (file_exists($__keys)) require_once $__keys;
}

// ── Real-time footer stats ────────────────────────────────────
$_footer_members  = 0;
$_footer_articles = 0;
$_footer_videos   = 0;
if (isset($conn)) {
    $r = $conn->query("SELECT COUNT(*) FROM users");
    if ($r) $_footer_members = (int)$r->fetch_row()[0];

    $r = $conn->query("SELECT COUNT(*) FROM articles");
    if ($r) $_footer_articles = (int)$r->fetch_row()[0];

    $r = $conn->query("SELECT COUNT(*) FROM premium_content");
    if ($r) $_footer_videos = (int)$r->fetch_row()[0];
}
function _wi_fmt(int $n): string {
    if ($n >= 1000) return round($n / 1000, 1) . 'K+';
    return $n . '+';
}
?>
<style>
/* ════════════════════════════════════════════════════════════
   FOOTER — PREMIUM REDESIGN
   ════════════════════════════════════════════════════════════ */

.site-footer {
    background: #04090f;
    color: #94a3b8;
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    font-size: 14px;
    position: relative;
    overflow: hidden;
}

/* Decorative top border */
.site-footer::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(212,175,55,0.3) 15%,
        #d4af37 35%,
        #facc15 50%,
        #d4af37 65%,
        rgba(212,175,55,0.3) 85%,
        transparent 100%);
}

/* Subtle radial glow at top-center */
.site-footer::after {
    content: '';
    position: absolute;
    top: -100px; left: 50%;
    transform: translateX(-50%);
    width: 700px; height: 300px;
    background: radial-gradient(ellipse, rgba(212,175,55,0.06) 0%, transparent 70%);
    pointer-events: none;
}

/* ── Pre-footer CTA strip ──────────────────────────────────── */
.footer-cta-strip {
    background: linear-gradient(135deg, #0d1b2e 0%, #0a1628 50%, #0d1b2e 100%);
    border-bottom: 1px solid rgba(212,175,55,0.1);
    padding: 36px 32px;
    text-align: center;
    position: relative;
}
.footer-cta-strip::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4af37' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.footer-cta-inner { position: relative; max-width: 580px; margin: 0 auto; }
.footer-cta-strip h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 6px;
    letter-spacing: -0.2px;
}
.footer-cta-strip p {
    color: #64748b;
    font-size: 13.5px;
    margin-bottom: 20px;
}
.footer-cta-form {
    display: flex;
    max-width: 420px;
    margin: 0 auto;
    border: 1px solid rgba(212,175,55,0.25);
    border-radius: 50px;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: rgba(255,255,255,0.03);
}
.footer-cta-form:focus-within {
    border-color: #d4af37;
    box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
}
.footer-cta-form input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 12px 20px;
    color: #f1f5f9;
    font-size: 13px;
    font-family: inherit;
}
.footer-cta-form input::placeholder { color: #475569; }
.footer-cta-form button {
    background: linear-gradient(135deg, #d4af37 0%, #facc15 100%);
    border: none;
    padding: 0 22px;
    cursor: pointer;
    color: #020617;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
    transition: opacity 0.2s, transform 0.2s;
    font-family: inherit;
}
.footer-cta-form button:hover { opacity: 0.9; transform: scale(1.02); }

/* ── Main top grid ─────────────────────────────────────────── */
.footer-top {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 56px 48px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 32px 52px;
    position: relative;
    z-index: 1;
}

/* ── Brand column ──────────────────────────────────────────── */
.f-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    text-decoration: none;
}
.f-logo-img {
    width: 42px; height: 42px;
    border-radius: 10px;
    border: 1px solid rgba(212,175,55,0.3);
    object-fit: cover;
    flex-shrink: 0;
}
.f-logo-text {
    font-size: 1.3rem;
    font-weight: 800;
    color: #f1f5f9;
    letter-spacing: -0.4px;
}
.f-logo-text span { color: #d4af37; }

.f-tagline {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(212,175,55,0.6);
    margin-bottom: 14px;
}

.f-desc {
    line-height: 1.8;
    margin-bottom: 24px;
    max-width: 280px;
    color: #64748b;
    font-size: 13.5px;
}

/* Stats row */
.f-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 26px;
}
.f-stat {
    display: flex;
    flex-direction: column;
}
.f-stat-num {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f1f5f9;
    letter-spacing: -0.5px;
}
.f-stat-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #475569;
    margin-top: 1px;
}
.f-divider-mini {
    width: 1px;
    background: rgba(255,255,255,0.07);
    align-self: stretch;
}

/* Social buttons */
.f-socials {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.f-social-btn {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 13.5px;
    transition: color 0.2s, background 0.2s, border-color 0.2s, transform 0.2s, box-shadow 0.2s;
}
.f-social-btn:hover {
    color: #020617;
    background: #d4af37;
    border-color: #d4af37;
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(212,175,55,0.35);
}

/* ── Column headings ───────────────────────────────────────── */
.f-head {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #d4af37;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.f-head::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, rgba(212,175,55,0.25), transparent);
}

/* ── Link lists ────────────────────────────────────────────── */
.f-links ul {
    list-style: none;
    padding: 0; margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.f-links ul li a {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    text-decoration: none;
    padding: 6px 0;
    font-size: 13.5px;
    transition: color 0.2s, padding-left 0.2s;
    border-radius: 4px;
}
.f-links ul li a .f-link-dot {
    width: 4px; height: 4px;
    border-radius: 50%;
    background: #1e293b;
    flex-shrink: 0;
    transition: background 0.2s, transform 0.2s;
}
.f-links ul li a:hover { color: #f1f5f9; padding-left: 6px; }
.f-links ul li a:hover .f-link-dot { background: #d4af37; transform: scale(1.4); }

/* ── Contact column ────────────────────────────────────────── */
.f-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
}
.f-icon-wrap {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.15);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.f-icon { color: #d4af37; font-size: 12px; }
.f-text span {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #334155;
    margin-bottom: 2px;
}
.f-text p { color: #cbd5e1; font-size: 13.5px; margin: 0; }

/* ── Quote strip ───────────────────────────────────────────── */
.quote-slider-container {
    background: linear-gradient(90deg, #030c1a 0%, #040f1f 50%, #030c1a 100%);
    border-top: 1px solid rgba(255,255,255,0.04);
    border-bottom: 1px solid rgba(255,255,255,0.04);
    overflow: hidden;
    padding: 0;
    white-space: nowrap;
    position: relative;
}
.quote-slider-container::before,
.quote-slider-container::after {
    content: '';
    position: absolute;
    top: 0; bottom: 0;
    width: 100px;
    z-index: 2;
    pointer-events: none;
}
.quote-slider-container::before {
    left: 0;
    background: linear-gradient(to right, #030c1a, transparent);
}
.quote-slider-container::after {
    right: 0;
    background: linear-gradient(to left, #030c1a, transparent);
}
.quote-slide-track {
    display: flex;
    align-items: center;
    height: 40px;
    animation: footer-scroll 45s linear infinite;
}
.quote-slide {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    color: #334155;
    padding: 0 60px;
    white-space: nowrap;
    flex-shrink: 0;
}
.quote-slide i { color: rgba(212,175,55,0.4); font-size: 11px; }
.quote-slide strong { color: rgba(212,175,55,0.6); font-style: normal; }
@keyframes footer-scroll {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}

/* ── Bottom bar ────────────────────────────────────────────── */
.footer-bottom-wrap {
    border-top: 1px solid rgba(255,255,255,0.05);
    background: rgba(0,0,0,0.2);
}
.footer-bottom {
    padding: 20px 32px;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.footer-bottom-left {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #334155;
}
.footer-bottom-left a { color: #d4af37; text-decoration: none; transition: color 0.2s; }
.footer-bottom-left a:hover { color: #facc15; }
.footer-bottom-links {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.footer-bottom-links a {
    font-size: 11.5px;
    color: #334155;
    text-decoration: none;
    transition: color 0.2s;
}
.footer-bottom-links a:hover { color: #94a3b8; }
.back-to-top {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(212,175,55,0.08);
    border: 1px solid rgba(212,175,55,0.2);
    color: #d4af37;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s, border-color 0.2s, transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
}
.back-to-top:hover {
    background: rgba(212,175,55,0.18);
    border-color: #d4af37;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(212,175,55,0.25);
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .footer-top { grid-template-columns: 1fr 1fr; gap: 40px 36px; }
}
@media (max-width: 640px) {
    .footer-top { grid-template-columns: 1fr; padding: 40px 20px 36px; gap: 36px; }
    .footer-bottom { padding: 16px 20px; justify-content: center; text-align: center; flex-direction: column; gap: 12px; }
    .footer-cta-strip { padding: 28px 20px; }
    .f-stats { gap: 14px; }
    .footer-bottom-links { justify-content: center; gap: 14px; }
}

/* ── Floating widget ───────────────────────────────────────── */
.floating-widget {
    position: fixed;
    bottom: 28px; right: 28px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 9000;
}
.float-btn {
    position: relative;
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.4);
    transition: transform .2s, box-shadow .2s;
}
.float-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.5); }
.btn-login-float { background: #1e293b; color: #f1f5f9; }
.btn-livechat    { background: #d4af37; color: #000; }
.btn-whatsapp    { background: #25d366; color: #fff; }
.float-tooltip {
    position: absolute;
    right: calc(100% + 10px); top: 50%;
    transform: translateY(-50%);
    background: #0f172a;
    color: #f1f5f9;
    font-size: 11.5px;
    padding: 5px 10px;
    border-radius: 6px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transition: opacity .2s;
    border: 1px solid #1e293b;
}
.float-tooltip::after {
    content: '';
    position: absolute;
    left: 100%; top: 50%;
    transform: translateY(-50%);
    border: 5px solid transparent;
    border-left-color: #1e293b;
}
.float-btn:hover .float-tooltip { opacity: 1; }

/* Live chat button pulse ring */
.btn-livechat { position: relative; overflow: visible; }
.btn-livechat::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 16px;
    border: 2px solid rgba(212,175,55,0.6);
    animation: chat-pulse 2s ease-out infinite;
    pointer-events: none;
}
@keyframes chat-pulse {
    0%   { transform: scale(1);   opacity: .8; }
    70%  { transform: scale(1.35); opacity: 0; }
    100% { transform: scale(1.35); opacity: 0; }
}

/* Unread badge on livechat button */
.chat-badge {
    position: absolute;
    top: -5px; right: -5px;
    width: 16px; height: 16px;
    background: #ef4444;
    border-radius: 50%;
    font-size: 9px;
    font-weight: 700;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #0f172a;
    animation: badge-pop .3s cubic-bezier(.34,1.56,.64,1) both;
    display: none;
}
@keyframes badge-pop {
    from { transform: scale(0); }
    to   { transform: scale(1); }
}

/* ── Chat box ──────────────────────────────────────────────── */
#visitor-chat-box {
    position: fixed;
    bottom: 100px; right: 28px;
    width: 370px;
    max-height: calc(100vh - 110px);
    background: #0b1220;
    border-radius: 20px;
    box-shadow:
        0 24px 80px rgba(0,0,0,.7),
        0 0 0 1px rgba(212,175,55,.18),
        inset 0 1px 0 rgba(255,255,255,.05);
    z-index: 9999;
    flex-direction: column;
    display: none;
    overflow: hidden;
    transform-origin: bottom right;
    transform: scale(0.85) translateY(16px);
    opacity: 0;
    transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease;
}
#visitor-chat-box.vc-open {
    display: flex;
    transform: scale(1) translateY(0);
    opacity: 1;
}
#visitor-chat-box.vc-closing {
    transform: scale(0.85) translateY(16px);
    opacity: 0;
}

/* Header */
.vc-header {
    background: linear-gradient(135deg, #0f172a 0%, #131f35 100%);
    padding: 16px 18px 14px;
    border-bottom: 1px solid rgba(212,175,55,.12);
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}
.vc-avatar {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #d4af37 0%, #facc15 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    color: #020617;
    flex-shrink: 0;
    position: relative;
}
.vc-online-dot {
    position: absolute;
    bottom: -2px; right: -2px;
    width: 11px; height: 11px;
    border-radius: 50%;
    background: #22c55e;
    border: 2px solid #0f172a;
    animation: dot-breathe 2.5s ease-in-out infinite;
}
@keyframes dot-breathe {
    0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,.4); }
    50%      { box-shadow: 0 0 0 5px rgba(34,197,94,0); }
}
.vc-header-info { flex: 1; min-width: 0; }
.vc-header-name {
    font-weight: 700;
    font-size: 14px;
    color: #f1f5f9;
    letter-spacing: -.2px;
}
.vc-header-status {
    font-size: 11px;
    color: #22c55e;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}
.vc-header-status span { color: #64748b; }
.vc-header-actions { display: flex; gap: 6px; flex-shrink: 0; }
.vc-close {
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.22);
    color: #f1f5f9;
    width: 32px; height: 32px;
    border-radius: 8px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    transition: background .2s, color .2s, border-color .2s;
}
.vc-close:hover { background: rgba(239,68,68,.3); color: #fff; border-color: rgba(239,68,68,.5); }

/* Message area */
#v-msg-area {
    flex: 1;
    padding: 16px 14px;
    overflow-y: auto;
    background: #0b1220;
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 160px;
    max-height: 380px;
    scroll-behavior: smooth;
}
#v-msg-area::-webkit-scrollbar { width: 4px; }
#v-msg-area::-webkit-scrollbar-track { background: transparent; }
#v-msg-area::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }
#v-msg-area::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.15); }

/* Welcome card */
.chat-welcome-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px 12px 8px;
    text-align: center;
}
.chat-welcome-icon {
    width: 52px; height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(212,175,55,.2) 0%, rgba(212,175,55,.05) 100%);
    border: 1px solid rgba(212,175,55,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    color: #d4af37;
    margin-bottom: 4px;
}
.chat-welcome-title {
    font-size: 15px;
    font-weight: 700;
    color: #f1f5f9;
}
.chat-welcome-sub {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.6;
    max-width: 240px;
}
.chat-quick-btns {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-top: 4px;
}
.chat-quick-btn {
    background: rgba(212,175,55,.08);
    border: 1px solid rgba(212,175,55,.2);
    color: #d4af37;
    font-size: 11.5px;
    padding: 5px 12px;
    border-radius: 20px;
    cursor: pointer;
    font-family: inherit;
    transition: background .2s, border-color .2s, transform .15s;
}
.chat-quick-btn:hover {
    background: rgba(212,175,55,.18);
    border-color: rgba(212,175,55,.4);
    transform: translateY(-1px);
}

/* Message rows */
.vc-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    animation: msg-in .25s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes msg-in {
    from { opacity: 0; transform: translateY(10px) scale(.97); }
    to   { opacity: 1; transform: translateY(0)    scale(1); }
}
.vc-msg-row.row-user { flex-direction: row-reverse; }

.vc-bubble-avatar {
    width: 26px; height: 26px;
    border-radius: 8px;
    background: linear-gradient(135deg, #d4af37, #facc15);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    color: #020617;
    flex-shrink: 0;
}
.vc-bubble-avatar.user-av {
    background: linear-gradient(135deg, #1e3a5f, #2d5a8e);
    color: #93c5fd;
}

.vc-bubble-wrap { display: flex; flex-direction: column; gap: 3px; max-width: 78%; }
.row-user .vc-bubble-wrap { align-items: flex-end; }

.chat-bubble-user,
.chat-bubble-agent {
    padding: 9px 13px;
    font-size: 13px;
    line-height: 1.55;
    word-break: break-word;
    position: relative;
}
.chat-bubble-user {
    background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
    color: #eff6ff;
    border-radius: 14px 14px 4px 14px;
    box-shadow: 0 2px 12px rgba(29,78,216,.3);
}
.chat-bubble-agent {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    color: #cbd5e1;
    border-radius: 14px 14px 14px 4px;
}
.bubble-time {
    font-size: 10px;
    color: #475569;
    padding: 0 2px;
}

/* Typing indicator */
.typing-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    animation: msg-in .2s ease both;
}
.typing-bubble {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px 14px 14px 4px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.typing-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #64748b;
    animation: typing-bounce .8s ease-in-out infinite;
}
.typing-dot:nth-child(2) { animation-delay: .15s; }
.typing-dot:nth-child(3) { animation-delay: .3s; }
@keyframes typing-bounce {
    0%,60%,100% { transform: translateY(0);   background: #475569; }
    30%          { transform: translateY(-5px); background: #d4af37; }
}

/* Day divider */
.chat-day-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 10.5px;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.chat-day-divider::before,
.chat-day-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,.06);
}

/* Input area */
#v-chat-form {
    padding: 12px 14px 14px;
    border-top: 1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.02);
    display: flex;
    align-items: flex-end;
    gap: 8px;
    flex-shrink: 0;
}
#v-input {
    flex: 1;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 12px;
    outline: none;
    padding: 10px 14px;
    font-size: 13px;
    font-family: inherit;
    color: #f1f5f9;
    resize: none;
    min-height: 40px;
    max-height: 100px;
    line-height: 1.4;
    transition: border-color .2s, box-shadow .2s, background .2s;
    overflow-y: auto;
}
#v-input::placeholder { color: #475569; }
#v-input:focus {
    border-color: rgba(212,175,55,.4);
    background: rgba(255,255,255,.07);
    box-shadow: 0 0 0 3px rgba(212,175,55,.08);
}
#v-send-btn {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #d4af37 0%, #facc15 100%);
    border: none;
    cursor: pointer;
    color: #020617;
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: transform .15s, box-shadow .2s, opacity .2s;
    box-shadow: 0 4px 14px rgba(212,175,55,.35);
}
#v-send-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212,175,55,.5); }
#v-send-btn:active { transform: scale(.93); }
#v-send-btn.sending { animation: send-pop .3s cubic-bezier(.34,1.56,.64,1); }
@keyframes send-pop {
    0%   { transform: scale(1); }
    40%  { transform: scale(1.25) rotate(-8deg); }
    100% { transform: scale(1) rotate(0); }
}
#v-send-btn:disabled { opacity: .4; cursor: default; transform: none; box-shadow: none; }

/* Powered-by line */
.vc-powered {
    text-align: center;
    font-size: 10px;
    color: #1e293b;
    padding: 6px 0 10px;
    letter-spacing: .5px;
}
.vc-powered a { color: #334155; text-decoration: none; }

@media (max-width: 420px) {
    #visitor-chat-box { width: calc(100vw - 24px); right: 12px; bottom: 84px; }
}
</style>

<footer class="site-footer">

    <!-- Newsletter CTA strip -->
    <div class="footer-cta-strip">
        <div class="footer-cta-inner">
            <h3><?php echo t('footer_cta_title'); ?></h3>
            <p><?php echo t('footer_cta_sub'); ?></p>
            <form class="footer-cta-form" onsubmit="return false;">
                <input type="email" placeholder="<?php echo t('footer_email_ph'); ?>">
                <button type="submit"><i class="fas fa-paper-plane"></i>&nbsp; <?php echo t('footer_subscribe'); ?></button>
            </form>
        </div>
    </div>

    <div class="footer-top">

        <!-- Brand -->
        <div class="f-brand">
            <a href="index.php" class="f-logo">
                <img src="<?php echo $root; ?>/images/logo.jpg" alt="Wise Invest" class="f-logo-img">
                <span class="f-logo-text">Wise<span>Invest</span></span>
            </a>
            <p class="f-tagline"><?php echo t('footer_tagline'); ?></p>
            <p class="f-desc"><?php echo t('footer_desc'); ?></p>

            <div class="f-stats">
                <div class="f-stat">
                    <span class="f-stat-num"><?php echo _wi_fmt($_footer_members); ?></span>
                    <span class="f-stat-label"><?php echo t('footer_members'); ?></span>
                </div>
                <div class="f-divider-mini"></div>
                <div class="f-stat">
                    <span class="f-stat-num"><?php echo _wi_fmt($_footer_articles); ?></span>
                    <span class="f-stat-label"><?php echo t('footer_articles'); ?></span>
                </div>
                <div class="f-divider-mini"></div>
                <div class="f-stat">
                    <span class="f-stat-num"><?php echo _wi_fmt($_footer_videos); ?></span>
                    <span class="f-stat-label"><?php echo t('footer_videos'); ?></span>
                </div>
            </div>

            <div class="f-socials">
                <a href="#" class="f-social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="f-social-btn" title="Twitter/X"><i class="fab fa-twitter"></i></a>
                <a href="#" class="f-social-btn" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="f-social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="https://wa.me/660933396350" target="_blank" rel="noopener" class="f-social-btn" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="f-links">
            <h3 class="f-head"><?php echo t('footer_company'); ?></h3>
            <ul>
                <li><a href="index.php"><span class="f-link-dot"></span><?php echo t('footer_home'); ?></a></li>
                <li><a href="about.php"><span class="f-link-dot"></span><?php echo t('footer_about'); ?></a></li>
                <li><a href="services.php"><span class="f-link-dot"></span><?php echo t('footer_services'); ?></a></li>
                <li><a href="community.php"><span class="f-link-dot"></span><?php echo t('footer_community'); ?></a></li>
                <li><a href="contact.php"><span class="f-link-dot"></span><?php echo t('footer_contact'); ?></a></li>
                <li><a href="vacancy.php"><span class="f-link-dot"></span><?php echo t('footer_careers'); ?></a></li>
            </ul>
        </div>

        <!-- Learning -->
        <div class="f-links">
            <h3 class="f-head"><?php echo t('footer_learning'); ?></h3>
            <ul>
                <li><a href="classes.php"><span class="f-link-dot"></span><?php echo t('footer_classes'); ?></a></li>
                <li><a href="videos.php"><span class="f-link-dot"></span><?php echo t('footer_video_lib'); ?></a></li>
                <li><a href="articles.php"><span class="f-link-dot"></span><?php echo t('footer_market_news'); ?></a></li>
                <li><a href="calculators.php"><span class="f-link-dot"></span><?php echo t('nav_calculators'); ?></a></li>
                <li><a href="glossary.php"><span class="f-link-dot"></span><?php echo t('footer_glossary'); ?></a></li>
                <li><a href="guides.php"><span class="f-link-dot"></span><?php echo t('footer_guides'); ?></a></li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="f-contact">
            <h3 class="f-head"><?php echo t('footer_contact_hd'); ?></h3>

            <div class="f-contact-item">
                <div class="f-icon-wrap"><i class="fas fa-map-marker-alt f-icon"></i></div>
                <div class="f-text">
                    <span><?php echo t('footer_office_lbl'); ?></span>
                    <p><?php echo t('footer_office_val'); ?></p>
                </div>
            </div>

            <div class="f-contact-item">
                <div class="f-icon-wrap"><i class="fas fa-envelope f-icon"></i></div>
                <div class="f-text">
                    <span><?php echo t('footer_email_lbl'); ?></span>
                    <p>support@wiseinvest.com</p>
                </div>
            </div>

            <div class="f-contact-item">
                <div class="f-icon-wrap"><i class="fab fa-whatsapp f-icon"></i></div>
                <div class="f-text">
                    <span><?php echo t('footer_wa_lbl'); ?></span>
                    <p>+66 09 3339 6350</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Quotes ticker -->
    <div class="quote-slider-container" id="quote-slider">
        <div class="quote-slide-track">
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Price is what you pay. Value is what you get." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Risk comes from not knowing what you're doing." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "In investing, what is comfortable is rarely profitable." &mdash; <strong>Robert Arnott</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "The stock market is a device to transfer money from the impatient to the patient." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "An investment in knowledge pays the best interest." &mdash; <strong>Benjamin Franklin</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Price is what you pay. Value is what you get." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "Risk comes from not knowing what you're doing." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "In investing, what is comfortable is rarely profitable." &mdash; <strong>Robert Arnott</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "The stock market is a device to transfer money from the impatient to the patient." &mdash; <strong>Warren Buffett</strong></span>
            <span class="quote-slide"><i class="fas fa-quote-left"></i> "An investment in knowledge pays the best interest." &mdash; <strong>Benjamin Franklin</strong></span>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="footer-bottom-wrap">
        <div class="footer-bottom">
            <div class="footer-bottom-left">
                <i class="fas fa-shield-alt" style="color:rgba(212,175,55,0.4);font-size:11px;"></i>
                &copy; <?php echo date('Y'); ?> <a href="index.php">Wise Invest</a>. <?php echo t('footer_copyright'); ?>
            </div>
            <nav class="footer-bottom-links">
                <a href="#"><?php echo t('footer_privacy'); ?></a>
                <a href="#"><?php echo t('footer_terms'); ?></a>
                <a href="#"><?php echo t('footer_cookies'); ?></a>
                <a href="contact.php"><?php echo t('footer_support'); ?></a>
            </nav>
            <a href="#" class="back-to-top" title="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">
                <i class="fas fa-arrow-up"></i>
            </a>
        </div>
    </div>

</footer>

<!-- Floating widget -->
<div class="floating-widget">
    <a href="login.php" class="float-btn btn-login-float">
        <i class="fas fa-user"></i>
        <span class="float-tooltip"><?php echo t('float_login'); ?></span>
    </a>
    <button id="chat-toggle-btn" onclick="toggleChatBox()" class="float-btn btn-livechat">
        <i class="fas fa-comments" id="chat-toggle-icon"></i>
        <span class="chat-badge" id="chat-badge">1</span>
        <span class="float-tooltip"><?php echo t('float_support'); ?></span>
    </button>
    <a href="https://wa.me/660933396350" target="_blank" rel="noopener" class="float-btn btn-whatsapp">
        <i class="fab fa-whatsapp"></i>
        <span class="float-tooltip"><?php echo t('float_whatsapp'); ?></span>
    </a>
</div>

<!-- Chat box -->
<div id="visitor-chat-box">
    <div class="vc-header">
        <div class="vc-avatar">
            <i class="fas fa-headset"></i>
            <span class="vc-online-dot"></span>
        </div>
        <div class="vc-header-info">
            <div class="vc-header-name"><?php echo t('chat_header_name'); ?></div>
            <div class="vc-header-status">
                <i class="fas fa-circle" style="font-size:7px;"></i> <?php echo t('chat_online'); ?>
                <span>&nbsp;· <?php echo t('chat_reply_note'); ?></span>
            </div>
        </div>
        <div class="vc-header-actions">
            <button class="vc-close" onclick="toggleChatBox()" title="Close"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div id="v-msg-area">
        <div class="chat-welcome-card">
            <div class="chat-welcome-icon"><i class="fas fa-comments"></i></div>
            <div class="chat-welcome-title"><?php echo t('chat_welcome_title'); ?></div>
            <div class="chat-welcome-sub"><?php echo t('chat_welcome_sub'); ?></div>
            <div class="chat-quick-btns">
                <button class="chat-quick-btn" onclick="vcQuickSend('How do I get started?')"><?php echo t('chat_quick_start'); ?></button>
                <button class="chat-quick-btn" onclick="vcQuickSend('Tell me about premium plans')"><?php echo t('chat_quick_premium'); ?></button>
                <button class="chat-quick-btn" onclick="vcQuickSend('I need help with my account')"><?php echo t('chat_quick_account'); ?></button>
            </div>
        </div>
    </div>

    <form id="v-chat-form">
        <textarea id="v-input" placeholder="<?php echo t('chat_placeholder'); ?>" autocomplete="off" rows="1"></textarea>
        <button type="submit" id="v-send-btn" title="Send"><i class="fas fa-paper-plane"></i></button>
    </form>
    <div class="vc-powered"><?php echo t('chat_powered'); ?> &nbsp;·&nbsp; <a href="#"><?php echo t('footer_privacy'); ?></a></div>
</div>

</main>

<?php if (basename($_SERVER['PHP_SELF']) !== 'calculators.php'): ?>
<script src="<?php echo $root; ?>/main.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>

<script>
(function () {
    const ROOT = '<?php echo $root; ?>';
    let vcOpen = false;
    let lastMsgCount = 0;
    let knownIds = new Set();
    let pollTimer = null;

    /* ── helpers ── */
    function fmt12(dateStr) {
        try {
            const d = new Date(dateStr.replace(' ', 'T'));
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch (e) { return ''; }
    }

    function buildRow(m) {
        const isUser = m.sender === 'user';
        const row = document.createElement('div');
        row.className = 'vc-msg-row ' + (isUser ? 'row-user' : 'row-agent');

        const av = document.createElement('div');
        av.className = 'vc-bubble-avatar' + (isUser ? ' user-av' : '');
        av.innerHTML = isUser ? '<i class="fas fa-user" style="font-size:10px"></i>'
                              : '<i class="fas fa-headset" style="font-size:10px"></i>';

        const wrap = document.createElement('div');
        wrap.className = 'vc-bubble-wrap';

        const bubble = document.createElement('div');
        bubble.className = isUser ? 'chat-bubble-user' : 'chat-bubble-agent';
        bubble.textContent = m.msg;

        const ts = document.createElement('div');
        ts.className = 'bubble-time';
        ts.textContent = m.time || '';

        wrap.appendChild(bubble);
        wrap.appendChild(ts);

        if (isUser) {
            row.appendChild(wrap);
            row.appendChild(av);
        } else {
            row.appendChild(av);
            row.appendChild(wrap);
        }
        return row;
    }

    function showTyping() {
        const row = document.createElement('div');
        row.className = 'typing-row';
        row.id = 'vc-typing';
        const av = document.createElement('div');
        av.className = 'vc-bubble-avatar';
        av.innerHTML = '<i class="fas fa-headset" style="font-size:10px"></i>';
        const bub = document.createElement('div');
        bub.className = 'typing-bubble';
        bub.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
        row.appendChild(av);
        row.appendChild(bub);
        document.getElementById('v-msg-area').appendChild(row);
        vcScrollBottom();
    }
    function hideTyping() {
        const el = document.getElementById('vc-typing');
        if (el) el.remove();
    }

    function vcScrollBottom() {
        const a = document.getElementById('v-msg-area');
        a.scrollTo({ top: a.scrollHeight, behavior: 'smooth' });
    }

    /* ── toggle open/close ── */
    window.toggleChatBox = function () {
        const box = document.getElementById('visitor-chat-box');
        const icon = document.getElementById('chat-toggle-icon');
        const badge = document.getElementById('chat-badge');

        if (!vcOpen) {
            vcOpen = true;
            box.style.display = 'flex';
            /* force reflow then add open class for animation */
            requestAnimationFrame(() => requestAnimationFrame(() => box.classList.add('vc-open')));
            icon.className = 'fas fa-times';
            badge.style.display = 'none';
            fetchVcMessages();
            pollTimer = setInterval(fetchVcMessages, 3000);
            setTimeout(() => document.getElementById('v-input').focus(), 350);
        } else {
            vcOpen = false;
            box.classList.remove('vc-open');
            box.classList.add('vc-closing');
            icon.className = 'fas fa-comments';
            clearInterval(pollTimer);
            setTimeout(() => {
                box.style.display = 'none';
                box.classList.remove('vc-closing');
            }, 280);
        }
    };

    /* ── quick reply chips ── */
    window.vcQuickSend = function (text) {
        const inp = document.getElementById('v-input');
        inp.value = text;
        vcSendMessage();
    };

    /* ── fetch messages ── */
    function fetchVcMessages() {
        fetch(ROOT + '/support_api.php?action=fetch')
            .then(r => r.json())
            .then(msgs => {
                if (!Array.isArray(msgs) || msgs.length === lastMsgCount) return;

                /* new messages arrived — check if agent replied */
                const hadNewAgent = msgs.slice(lastMsgCount).some(m => m.sender !== 'user');

                const area = document.getElementById('v-msg-area');

                if (lastMsgCount === 0) {
                    /* first load — clear welcome card and render all */
                    area.innerHTML = '';
                    msgs.forEach(m => area.appendChild(buildRow(m)));
                } else {
                    /* only append new messages */
                    if (hadNewAgent) {
                        hideTyping();
                    }
                    msgs.slice(lastMsgCount).forEach(m => area.appendChild(buildRow(m)));
                }

                lastMsgCount = msgs.length;
                vcScrollBottom();
            })
            .catch(() => {});
    }

    /* ── send ── */
    function vcSendMessage() {
        const inp = document.getElementById('v-input');
        const btn = document.getElementById('v-send-btn');
        const txt = inp.value.trim();
        if (!txt) return;

        /* optimistic: render immediately */
        const area = document.getElementById('v-msg-area');
        if (lastMsgCount === 0) area.innerHTML = '';
        area.appendChild(buildRow({ sender: 'user', msg: txt, time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }));
        lastMsgCount++;
        vcScrollBottom();

        inp.value = '';
        inp.style.height = 'auto';

        btn.classList.add('sending');
        btn.disabled = true;
        setTimeout(() => { btn.classList.remove('sending'); btn.disabled = false; }, 400);

        /* show typing indicator after a short delay */
        setTimeout(showTyping, 600);

        const fd = new FormData();
        fd.append('message', txt);
        fetch(ROOT + '/support_api.php?action=send', { method: 'POST', body: fd })
            .then(() => {
                /* poll soon to get any instant bot/agent reply */
                setTimeout(fetchVcMessages, 1200);
            })
            .catch(() => { hideTyping(); });
    }

    document.getElementById('v-chat-form').addEventListener('submit', function (e) {
        e.preventDefault();
        vcSendMessage();
    });

    /* Enter sends, Shift+Enter newline */
    document.getElementById('v-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            vcSendMessage();
        }
    });

    /* auto-grow textarea */
    document.getElementById('v-input').addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    /* ── Web Push subscription ── */
    <?php
    $__vapidKey    = (defined('VAPID_PUBLIC_KEY') && VAPID_PUBLIC_KEY !== 'YOUR_VAPID_PUBLIC_KEY') ? VAPID_PUBLIC_KEY : '';
    $__userLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';
    ?>
    var _pushSubscribed  = false;
    var _vapidPublicKey  = '<?php echo $__vapidKey; ?>';
    var _userLoggedIn    = <?php echo $__userLoggedIn; ?>;

    /* base64url → Uint8Array (with proper padding) */
    function b64urlToUint8(b64url) {
        var b64 = b64url.replace(/-/g, '+').replace(/_/g, '/');
        var pad = b64.length % 4;
        if (pad) b64 += '===='.slice(0, 4 - pad);
        var raw = atob(b64);
        var arr = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
        return arr;
    }

    /* Register SW once on page load (does NOT ask for permission yet) */
    var _swReg = null;
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(ROOT + '/service-worker.js').then(function (reg) {
            _swReg = reg;
        }).catch(function (e) {
            console.warn('[Push] SW registration failed:', e);
        });
    }

    /* Called from user gesture (chat button click) */
    function vcSubscribePush() {
        if (_pushSubscribed || !_userLoggedIn) return;
        if (!('PushManager' in window) || !('Notification' in window)) return;
        if (!_vapidPublicKey) {
            console.warn('[Push] VAPID public key not configured — run generate_vapid.php first.');
            return;
        }

        /* requestPermission must be called directly from user gesture */
        var permPromise = (typeof Notification.requestPermission === 'function')
            ? Notification.requestPermission()
            : Promise.resolve(Notification.permission);

        permPromise.then(function (perm) {
            if (perm !== 'granted') {
                console.info('[Push] Notification permission:', perm);
                return;
            }
            var swReady = _swReg
                ? Promise.resolve(_swReg)
                : navigator.serviceWorker.ready;

            swReady.then(function (reg) {
                /* Check if already subscribed */
                return reg.pushManager.getSubscription().then(function (existing) {
                    if (existing) return existing;
                    return reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: b64urlToUint8(_vapidPublicKey)
                    });
                });
            }).then(function (sub) {
                if (!sub) return;
                _pushSubscribed = true;
                fetch(ROOT + '/push_subscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(sub.toJSON())
                }).then(function (r) {
                    if (!r.ok) console.warn('[Push] push_subscribe.php returned', r.status);
                }).catch(function (e) {
                    console.warn('[Push] Subscription save failed:', e);
                });
            }).catch(function (e) {
                console.error('[Push] Subscribe error:', e);
            });
        });
    }

    /* Override toggleChatBox to request push permission on first open */
    var _origToggle = window.toggleChatBox;
    window.toggleChatBox = function () {
        _origToggle();
        if (!_pushSubscribed) vcSubscribePush();
    };
})();
</script>

<script src="/pwa-register.js" async></script>
</body>
</html>
