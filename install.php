<?php /* Standalone PWA install landing page — no header/footer includes */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Capture the install prompt as early as possible.
       Must run before beforeinstallprompt can fire (e.g. on repeat visits
       where the service worker is already active and the event fires during
       initial parse). Stored on window so the button handler below can use it. -->
  <script>
    window.deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      window.deferredPrompt = e;
    });
  </script>

  <title>Install WiseInvest App</title>
  <meta name="description" content="Install WiseInvest — Financial education and brokerage account setup for Burmese investors. Works like a native app, no app store needed.">
  <meta name="theme-color" content="#d4af37">
  <link rel="manifest" href="/manifest.json">
  <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
  <link rel="icon" type="image/x-icon" href="/icons/favicon.ico">

  <!-- Open Graph / Twitter -->
  <meta property="og:title" content="Install WiseInvest App">
  <meta property="og:description" content="Get global brokerage education on your phone. Works like a native app, 100% free.">
  <meta property="og:image" content="https://wiseinvest.it.com/icons/web-app-manifest-512x512.png">
  <meta property="og:url" content="https://wiseinvest.it.com/install.php">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Install WiseInvest App">
  <meta name="twitter:description" content="Get global brokerage education on your phone.">
  <meta name="twitter:image" content="https://wiseinvest.it.com/icons/web-app-manifest-512x512.png">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --gold:   #d4af37;
      --gold-l: #facc15;
      --dark:   #0a0a0a;
      --dark2:  #0f172a;
      --dark3:  #1e293b;
      --text:   #e2e8f0;
      --muted:  #94a3b8;
      --dim:    #64748b;
      --border: rgba(212,175,55,0.2);
    }

    html { scroll-behavior: smooth; }

    body {
      background: var(--dark);
      color: var(--text);
      font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Hide pwa-register.js floating elements on this page */
    #pwa-install-btn,
    #ios-install-tip { display: none !important; }

    /* ── Hero entrance animations ───────────────────────────── */
    @keyframes heroIn {
      from { opacity: 0; transform: translateY(22px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes logoFloat {
      0%,100% { transform: translateY(0);    box-shadow: 0 0 40px rgba(212,175,55,.18), 0 16px 48px rgba(0,0,0,.5); }
      50%      { transform: translateY(-8px); box-shadow: 0 0 56px rgba(212,175,55,.3),  0 24px 56px rgba(0,0,0,.5); }
    }
    @keyframes shimmer {
      0%   { background-position: 0%   center; }
      100% { background-position: 200% center; }
    }

    /* ── Scroll fade-up ─────────────────────────────────────── */
    .fade-up {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    .fade-up.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* ── Hero ───────────────────────────────────────────────── */
    .hero {
      min-height: 100svh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 60px 24px 52px;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute;
      top: -140px; left: 50%;
      transform: translateX(-50%);
      width: 700px; height: 700px;
      background: radial-gradient(ellipse, rgba(212,175,55,0.07) 0%, transparent 65%);
      pointer-events: none;
    }

    .hero-logo {
      width: 120px; height: 120px;
      border-radius: 24px;
      border: 2px solid rgba(212,175,55,0.45);
      margin-bottom: 28px;
      display: block;
      animation: heroIn 0.65s ease 0.1s both, logoFloat 4s ease-in-out 1.2s infinite;
    }

    .hero-title {
      font-size: clamp(1.9rem, 5vw, 2.7rem);
      font-weight: 800;
      letter-spacing: -0.5px;
      background: linear-gradient(135deg, var(--gold) 0%, var(--gold-l) 50%, var(--gold) 100%);
      background-size: 200% auto;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: heroIn 0.65s ease 0.2s both, shimmer 3.5s linear 1.2s infinite;
      margin-bottom: 12px;
    }

    .hero-sub-mm {
      font-size: 1rem;
      color: var(--muted);
      margin-bottom: 5px;
      animation: heroIn 0.65s ease 0.3s both;
    }
    .hero-sub-en {
      font-size: 1rem;
      color: var(--dim);
      margin-bottom: 36px;
      animation: heroIn 0.65s ease 0.4s both;
    }

    #main-install-btn {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: linear-gradient(135deg, var(--gold) 0%, var(--gold-l) 50%, var(--gold) 100%);
      background-size: 200% auto;
      color: #0a0a0a;
      font-weight: 800;
      font-size: 1.1rem;
      padding: 16px 42px;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      box-shadow: 0 6px 24px rgba(212,175,55,0.45);
      animation: heroIn 0.65s ease 0.5s both, shimmer 3.5s linear 1.2s infinite;
      transition: transform 0.2s, box-shadow 0.2s;
      text-decoration: none;
      font-family: inherit;
    }
    #main-install-btn:hover {
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 12px 36px rgba(212,175,55,0.65);
    }
    #main-install-btn:active { transform: scale(0.97); }
    #main-install-btn .btn-icon { font-size: 1.25rem; }

    .installed-badge {
      display: none;
      align-items: center;
      gap: 10px;
      background: rgba(34,197,94,0.1);
      border: 1px solid rgba(34,197,94,0.3);
      border-radius: 50px;
      padding: 14px 28px;
      color: #22c55e;
      font-weight: 600;
      font-size: 0.95rem;
      animation: heroIn 0.5s ease both;
    }
    .installed-badge.show { display: inline-flex; }
    .installed-badge a { color: inherit; text-decoration: underline; margin-left: 6px; }

    /* ── Section base ───────────────────────────────────────── */
    section { padding: 72px 24px; position: relative; }
    .section-inner { max-width: 680px; margin: 0 auto; }

    .section-title {
      font-size: clamp(1.3rem, 3.5vw, 1.6rem);
      font-weight: 700;
      color: var(--text);
      text-align: center;
      margin-bottom: 6px;
    }
    .section-title span { color: var(--gold); }

    .section-sub {
      text-align: center;
      color: var(--dim);
      font-size: 0.93rem;
      margin-bottom: 8px;
    }

    .section-divider {
      width: 44px; height: 2px;
      background: linear-gradient(90deg, transparent, var(--gold), transparent);
      margin: 16px auto 32px;
      border-radius: 2px;
    }

    .section-alt {
      background: rgba(212,175,55,0.025);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }

    /* ── Device instruction blocks ──────────────────────────── */
    .device-block { display: none; }
    .device-block.active { display: block; }

    .steps { display: flex; flex-direction: column; gap: 14px; }

    .step {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 14px;
      padding: 18px 20px;
      transition: border-color 0.2s, background 0.2s;
    }
    .step:hover {
      border-color: var(--border);
      background: rgba(212,175,55,0.05);
    }
    .step-num {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--gold), var(--gold-l));
      color: #0a0a0a;
      font-weight: 800;
      font-size: 0.9rem;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .step-icon {
      font-size: 1.9rem;
      flex-shrink: 0;
      margin-top: 2px;
    }
    .step-text strong {
      display: block;
      color: var(--text);
      font-size: 0.93rem;
      font-weight: 600;
      margin-bottom: 3px;
    }
    .step-text span { color: var(--dim); font-size: 0.87rem; }

    .unsupported-msg {
      background: rgba(239,68,68,0.07);
      border: 1px solid rgba(239,68,68,0.2);
      border-radius: 14px;
      padding: 28px 24px;
      text-align: center;
    }
    .unsupported-msg .u-icon { font-size: 2.5rem; margin-bottom: 14px; }
    .unsupported-msg p { color: var(--muted); line-height: 1.8; font-size: 0.95rem; }
    .unsupported-msg strong { color: var(--text); }

    /* ── Features grid ──────────────────────────────────────── */
    .features-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    @media (max-width: 460px) {
      .features-grid { grid-template-columns: 1fr; }
    }

    .feature-card {
      background: rgba(255,255,255,0.025);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 16px;
      padding: 22px 18px;
      transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
    }
    .feature-card:hover {
      transform: translateY(-4px);
      border-color: var(--border);
      box-shadow: 0 8px 28px rgba(0,0,0,0.3);
    }
    .feature-card .f-icon { font-size: 1.8rem; margin-bottom: 10px; display: block; }
    .feature-card .f-en  { font-size: 0.9rem; font-weight: 600; color: var(--text); margin-bottom: 4px; }
    .feature-card .f-mm  { font-size: 0.82rem; color: var(--dim); }

    /* ── QR section ─────────────────────────────────────────── */
    .qr-card {
      background: #fff;
      border-radius: 20px;
      padding: 20px;
      width: 200px; height: 200px;
      margin: 0 auto 28px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 8px 32px rgba(0,0,0,0.4), 0 0 0 1px rgba(212,175,55,0.2);
    }

    .share-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }

    .share-btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 10px 20px;
      border-radius: 30px;
      font-size: 0.88rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
      font-family: inherit;
      text-decoration: none;
      transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
    }
    .share-btn:hover { transform: translateY(-2px); opacity: 0.9; }
    .share-btn:active { transform: scale(0.96); }

    .btn-copy {
      background: rgba(212,175,55,0.1);
      border: 1px solid var(--border);
      color: var(--gold);
    }
    .btn-copy.copied {
      background: rgba(34,197,94,0.12);
      border-color: rgba(34,197,94,0.3);
      color: #22c55e;
    }
    .btn-fb  { background: #1877f2; color: #fff; box-shadow: 0 4px 14px rgba(24,119,242,0.3); }
    .btn-tg  { background: #229ed9; color: #fff; box-shadow: 0 4px 14px rgba(34,158,217,0.3); }

    /* ── FAQ accordion ──────────────────────────────────────── */
    .faq-list { display: flex; flex-direction: column; gap: 10px; }

    .faq-item {
      background: rgba(255,255,255,0.025);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 14px;
      overflow: hidden;
      transition: border-color 0.2s;
    }
    .faq-item.open { border-color: var(--border); }

    .faq-q {
      width: 100%;
      background: none;
      border: none;
      color: var(--text);
      text-align: left;
      padding: 18px 20px;
      cursor: pointer;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 14px;
      font-family: inherit;
      transition: color 0.2s;
    }
    .faq-q:hover { color: var(--gold); }
    .faq-item.open .faq-q { color: var(--gold); }

    .faq-q-text { font-size: 0.95rem; font-weight: 600; }
    .faq-q-text small {
      display: block;
      font-size: 0.8rem;
      font-weight: 400;
      color: var(--dim);
      margin-top: 3px;
    }
    .faq-item.open .faq-q-text small { color: rgba(212,175,55,0.55); }

    .faq-chevron {
      flex-shrink: 0;
      font-size: 0.72rem;
      margin-top: 5px;
      color: var(--dim);
      transition: transform 0.3s ease, color 0.2s;
    }
    .faq-item.open .faq-chevron { transform: rotate(180deg); color: var(--gold); }

    .faq-a { max-height: 0; overflow: hidden; transition: max-height 0.35s ease; }
    .faq-item.open .faq-a { max-height: 320px; }

    .faq-a-inner {
      padding: 14px 20px 18px;
      color: var(--muted);
      font-size: 0.88rem;
      line-height: 1.8;
      border-top: 1px solid rgba(255,255,255,0.05);
    }

    /* ── Page footer ────────────────────────────────────────── */
    .page-footer {
      text-align: center;
      padding: 40px 24px 52px;
      border-top: 1px solid rgba(255,255,255,0.05);
    }
    .footer-back {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      color: var(--gold);
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95rem;
      margin-bottom: 18px;
      transition: gap 0.2s, opacity 0.2s;
    }
    .footer-back:hover { gap: 12px; opacity: 0.8; }
    .footer-copy { color: var(--dim); font-size: 0.82rem; }
    .footer-pwa  { color: rgba(212,175,55,0.3); font-size: 0.75rem; margin-top: 6px; }

    /* ════════════ iOS install enhancements (banner · popup · cards) ════════════ */

    /* ── Component A: Chrome-on-iPhone warning banner ── */
    #ios-browser-warning {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100000;
      display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
      padding: 12px 16px;
      background: linear-gradient(135deg, #d4af37 0%, #b91c1c 100%);
      color: #0a0a0a; font-weight: 600;
      box-shadow: 0 4px 20px rgba(0,0,0,0.45);
    }
    #ios-browser-warning[hidden] { display: none; }
    .warn-icon { font-size: 1.5rem; flex-shrink: 0; }
    .warn-text { flex: 1; min-width: 160px; display: flex; flex-direction: column; line-height: 1.4; font-size: 0.9rem; }
    .warn-text .warn-mm { font-size: 0.82rem; opacity: 0.85; }
    .warn-actions { display: flex; gap: 8px; align-items: center; margin-left: auto; }
    .warn-copy {
      background: #0a0a0a; color: var(--gold); border: none; border-radius: 20px;
      padding: 8px 16px; font-weight: 700; font-size: 0.82rem; cursor: pointer; font-family: inherit;
    }
    .warn-copy.copied { background: #14532d; color: #4ade80; }
    .warn-dismiss {
      background: rgba(0,0,0,0.2); color: #0a0a0a; border: none; border-radius: 50%;
      width: 30px; height: 30px; font-size: 0.95rem; cursor: pointer; flex-shrink: 0; line-height: 1;
    }
    body.has-warning { padding-top: 84px; }

    /* ── Component C: animated iOS guide cards ── */
    .ios-guide { display: flex; flex-direction: column; gap: 16px; }
    .ios-card {
      background: var(--dark2);
      border: 1px solid rgba(212,175,55,0.3);
      border-radius: 18px;
      padding: 26px 22px;
      text-align: center;
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .ios-card:hover {
      transform: scale(1.02);
      border-color: rgba(212,175,55,0.55);
      box-shadow: 0 0 28px rgba(212,175,55,0.25);
    }
    .ios-card-num {
      width: 56px; height: 56px; border-radius: 50%; margin: 0 auto 14px;
      background: linear-gradient(135deg, var(--gold), var(--gold-l));
      color: #0a0a0a; font-size: 1.8rem; font-weight: 800;
      display: flex; align-items: center; justify-content: center;
    }
    .ios-card-svg { width: 72px; height: 72px; margin: 0 auto 14px; display: block; }
    .ios-card-title { font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .ios-card-title small { display: block; font-size: 0.82rem; font-weight: 400; color: var(--gold); margin-top: 2px; }
    .ios-card-desc { font-size: 0.88rem; color: var(--muted); margin-bottom: 6px; }
    .ios-card-desc small { display: block; font-size: 0.8rem; color: var(--dim); margin-top: 2px; }
    .ios-card-tip { font-size: 0.78rem; color: var(--dim); font-style: italic; }

    /* ── Component B: auto-popup modal ── */
    .ios-popup-overlay {
      position: fixed; inset: 0; z-index: 100001;
      background: rgba(0,0,0,0.85);
      display: flex; align-items: center; justify-content: center;
      padding: 20px;
      animation: iosOverlayIn 0.3s ease both;
    }
    .ios-popup-overlay[hidden] { display: none; }
    .ios-popup-card {
      position: relative;
      width: 100%; max-width: 360px;
      background: var(--dark2);
      border: 1px solid rgba(212,175,55,0.4);
      border-radius: 22px;
      padding: 30px 24px 24px;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,0.6);
      animation: iosCardIn 0.4s cubic-bezier(0.16,1,0.3,1) both;
    }
    .ios-popup-close {
      position: absolute; top: 12px; right: 12px;
      width: 32px; height: 32px; border-radius: 50%;
      background: rgba(255,255,255,0.08); color: var(--text);
      border: none; font-size: 1rem; cursor: pointer; line-height: 1;
    }
    .ios-slides { position: relative; min-height: 250px; }
    .ios-slide {
      position: absolute; inset: 0;
      opacity: 0; transform: translateX(20px); pointer-events: none;
      transition: opacity 0.4s ease, transform 0.4s ease;
    }
    .ios-slide.active { opacity: 1; transform: translateX(0); pointer-events: auto; position: relative; }
    .ios-svg { width: 110px; height: auto; margin: 0 auto 16px; display: block; }
    .ios-slide h3 { font-size: 1.05rem; color: var(--gold); margin-bottom: 4px; }
    .ios-slide .mm { font-size: 0.85rem; color: var(--muted); margin-bottom: 10px; }
    .ios-slide .sub { font-size: 0.82rem; color: var(--dim); line-height: 1.6; }
    .ios-slide .sub .mm { display: block; font-size: 0.8rem; margin-top: 2px; }
    .ios-nav {
      position: absolute; top: 110px; transform: translateY(-50%);
      width: 36px; height: 36px; border-radius: 50%;
      background: rgba(212,175,55,0.12); color: var(--gold);
      border: 1px solid rgba(212,175,55,0.3); font-size: 1.3rem; cursor: pointer; line-height: 1;
    }
    .ios-nav.prev { left: -6px; }
    .ios-nav.next { right: -6px; }
    .ios-dots { display: flex; gap: 8px; justify-content: center; margin: 18px 0 16px; }
    .ios-dots .dot {
      width: 9px; height: 9px; border-radius: 50%; padding: 0;
      background: rgba(212,175,55,0.3); border: none; cursor: pointer; transition: background 0.2s, transform 0.2s;
    }
    .ios-dots .dot.active { background: var(--gold); transform: scale(1.3); }
    .ios-popup-cta {
      width: 100%; padding: 14px; border: none; border-radius: 50px;
      background: linear-gradient(135deg, var(--gold), var(--gold-l));
      color: #0a0a0a; font-weight: 800; font-size: 1rem; cursor: pointer; font-family: inherit;
    }
    .ios-popup-dna {
      display: flex; align-items: center; justify-content: center; gap: 7px;
      margin-top: 14px; font-size: 0.8rem; color: var(--dim); cursor: pointer;
    }
    .ios-popup-dna input { accent-color: var(--gold); }

    /* Highlighted/animated SVG parts (transform · opacity · filter only) */
    .svg-pulse  { animation: glowPulse 2s ease-in-out infinite; transform-origin: center; }
    .svg-arrow  { animation: arrowBounce 1.4s ease-in-out infinite; }
    .svg-scroll { animation: scrollHint 1.8s ease-in-out infinite; }
    .svg-pop    { animation: iconPop 3s ease-in-out infinite; transform-origin: center; }
    .anim-glow   { animation: glowPulse 2s ease-in-out infinite; }
    .anim-scroll { animation: scrollHint 1.8s ease-in-out infinite; }
    .anim-bounce { animation: softBounce 3s ease-in-out infinite; transform-origin: center; }

    @keyframes iosOverlayIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes iosCardIn { from { opacity: 0; transform: translateY(30px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes glowPulse { 0%,100% { filter: drop-shadow(0 0 0 rgba(212,175,55,0)); } 50% { filter: drop-shadow(0 0 9px rgba(212,175,55,0.85)); } }
    @keyframes arrowBounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
    @keyframes scrollHint { 0%,100% { transform: translateY(0); } 50% { transform: translateY(8px); } }
    @keyframes softBounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    @keyframes iconPop { 0%,100% { transform: scale(1); } 50% { transform: scale(1.12); } }

    /* Accessibility: honour reduced-motion preference */
    @media (prefers-reduced-motion: reduce) {
      .svg-pulse, .svg-arrow, .svg-scroll, .svg-pop,
      .anim-glow, .anim-scroll, .anim-bounce,
      .ios-popup-overlay, .ios-popup-card, .ios-slide {
        animation: none !important; transition: none !important;
      }
    }
  </style>
</head>
<body>

<!-- ══════════════════════════════════════════
     COMPONENT A — Chrome-on-iPhone warning banner
     Revealed by JS only when iOS && !Safari
══════════════════════════════════════════ -->
<div id="ios-browser-warning" role="alert" hidden>
  <span class="warn-icon" aria-hidden="true">⚠️</span>
  <div class="warn-text">
    <strong>Chrome on iPhone can't install apps. Open this page in Safari instead.</strong>
    <span class="warn-mm">iPhone တွင် Chrome ဖြင့် install မရပါ။ Safari ဖြင့်ဖွင့်ပါ။</span>
  </div>
  <div class="warn-actions">
    <button type="button" class="warn-copy" id="warn-copy-btn" onclick="copyInstallLink()" aria-label="Copy install link">🔗 Copy Link</button>
    <button type="button" class="warn-dismiss" aria-label="Dismiss warning"
            onclick="this.closest('#ios-browser-warning').hidden=true;document.body.classList.remove('has-warning');">✕</button>
  </div>
</div>

<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="hero">
  <img src="/icons/web-app-manifest-512x512.png" alt="WiseInvest" class="hero-logo">
  <h1 class="hero-title">Install WiseInvest App</h1>
  <p class="hero-sub-mm">WiseInvest App ကို install လုပ်ပါ</p>
  <p class="hero-sub-en">Get global brokerage education on your phone</p>

  <button id="main-install-btn">
    <span class="btn-icon">📲</span>
    <span id="install-btn-text">Install Now</span>
  </button>

  <div class="installed-badge" id="installed-badge">
    <span>✅</span>
    App is installed!
    <a href="/">Open WiseInvest</a>
  </div>
</section>


<!-- ══════════════════════════════════════════
     HOW TO INSTALL (device-specific)
══════════════════════════════════════════ -->
<section class="section-alt">
  <div class="section-inner">
    <h2 class="section-title fade-up">How to <span>Install</span></h2>
    <div class="section-divider fade-up"></div>

    <!-- Android Chrome -->
    <div class="device-block" id="block-android">
      <div class="steps">
        <div class="step fade-up">
          <div class="step-num">1</div>
          <div class="step-text">
            <strong>Tap the Install Now button above</strong>
            <span>Chrome will show an install prompt</span>
          </div>
        </div>
        <div class="step fade-up">
          <div class="step-num">2</div>
          <div class="step-text">
            <strong>Confirm in the popup</strong>
            <span>Tap "Install" in the Chrome dialog</span>
          </div>
        </div>
        <div class="step fade-up">
          <div class="step-num">3</div>
          <div class="step-text">
            <strong>WiseInvest icon will appear on your home screen</strong>
            <span>Launch it like any Android app — no app store needed</span>
          </div>
        </div>
      </div>
    </div>

    <!-- iOS Safari — animated visual guide (Component C) -->
    <div class="device-block" id="block-ios">
      <div class="ios-guide">

        <!-- Card 1: Share -->
        <article class="ios-card fade-up">
          <div class="ios-card-num">1</div>
          <svg class="ios-card-svg anim-glow" viewBox="0 0 64 64" fill="none" stroke="#d4af37" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M32 8 v28"/>
            <path d="M22 18 l10 -10 l10 10"/>
            <path d="M16 30 v22 a4 4 0 0 0 4 4 h24 a4 4 0 0 0 4 -4 v-22"/>
          </svg>
          <h3 class="ios-card-title">Tap the Share button <small>Share ခလုတ်ကို နှိပ်ပါ</small></h3>
          <p class="ios-card-desc">The square with an upward arrow ↗ <small>အပေါ်သို့ မြှားပါသော လေးထောင့်ကွက်</small></p>
          <p class="ios-card-tip">Older iPhones: top of screen · Newer iPhones: bottom of screen</p>
        </article>

        <!-- Card 2: Add to Home Screen -->
        <article class="ios-card fade-up">
          <div class="ios-card-num">2</div>
          <svg class="ios-card-svg" viewBox="0 0 64 64" fill="none" aria-hidden="true">
            <rect x="10" y="6" width="44" height="52" rx="6" stroke="#475569" stroke-width="2"/>
            <line x1="16" y1="16" x2="40" y2="16" stroke="#475569" stroke-width="2" stroke-linecap="round"/>
            <line x1="16" y1="22" x2="34" y2="22" stroke="#475569" stroke-width="2" stroke-linecap="round"/>
            <rect x="15" y="30" width="34" height="11" rx="3" fill="rgba(212,175,55,0.18)" stroke="#d4af37" stroke-width="2"/>
            <path d="M22 35 h7 M25.5 31.5 v7" stroke="#d4af37" stroke-width="2" stroke-linecap="round"/>
            <path class="anim-scroll" d="M32 45 v6 M28 48 l4 4 l4 -4" stroke="#d4af37" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h3 class="ios-card-title">Scroll down → "Add to Home Screen" <small>အောက်ဆွဲ၍ "Add to Home Screen" နှိပ်ပါ</small></h3>
          <p class="ios-card-desc">It's below the apps, in the actions list <small>App များ၏အောက်၊ လုပ်ဆောင်ချက်စာရင်းတွင်</small></p>
          <p class="ios-card-tip">Swipe up inside the share menu to find it</p>
        </article>

        <!-- Card 3: Add / Done -->
        <article class="ios-card fade-up">
          <div class="ios-card-num">3</div>
          <svg class="ios-card-svg" viewBox="0 0 64 64" fill="none" aria-hidden="true">
            <rect x="8" y="4" width="48" height="56" rx="8" stroke="#475569" stroke-width="2"/>
            <rect class="anim-bounce" x="24" y="18" width="16" height="16" rx="4" fill="rgba(212,175,55,0.18)" stroke="#d4af37" stroke-width="2"/>
            <path class="anim-bounce" d="M29 26 l2.5 2.5 l4.5 -5" stroke="#d4af37" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="22" y1="44" x2="42" y2="44" stroke="#475569" stroke-width="2" stroke-linecap="round"/>
            <line x1="26" y1="50" x2="38" y2="50" stroke="#475569" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <h3 class="ios-card-title">Tap "Add" → Done! ✨ <small>"Add" နှိပ်ပါ → ပြီးပါပြီ! ✨</small></h3>
          <p class="ios-card-desc">WiseInvest appears on your home screen <small>WiseInvest သင့်ဖုန်းမျက်နှာပြင်တွင် ပေါ်လာမည်</small></p>
          <p class="ios-card-tip">Launch it like any other app — no app store needed</p>
        </article>

      </div>
    </div>

    <!-- Desktop Chrome / Edge -->
    <div class="device-block" id="block-desktop">
      <div class="steps">
        <div class="step fade-up">
          <div class="step-num">1</div>
          <div class="step-text">
            <strong>Click the Install button above</strong>
            <span>Or look for the &#8853; install icon in your browser's address bar</span>
          </div>
        </div>
        <div class="step fade-up">
          <div class="step-num">2</div>
          <div class="step-text">
            <strong>Confirm installation</strong>
            <span>Click "Install" in Chrome or Edge's dialog</span>
          </div>
        </div>
        <div class="step fade-up">
          <div class="step-num">3</div>
          <div class="step-text">
            <strong>WiseInvest opens as a desktop app</strong>
            <span>Find it in your Start menu or Applications folder</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Unsupported browser -->
    <div class="device-block" id="block-unsupported">
      <div class="unsupported-msg fade-up">
        <div class="u-icon">🌐</div>
        <p>
          <strong>Your current browser doesn't support app installation.</strong><br><br>
          On <strong>Android</strong>: open this page in <strong>Google Chrome</strong><br>
          On <strong>iPhone / iPad</strong>: open this page in <strong>Safari</strong><br><br>
          <span style="color:var(--dim);font-size:0.85rem;">wiseinvest.it.com/install.php</span>
        </p>
      </div>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     FEATURES — WHY INSTALL?
══════════════════════════════════════════ -->
<section>
  <div class="section-inner">
    <h2 class="section-title fade-up">Why <span>Install?</span></h2>
    <p class="section-sub fade-up">ဘာကြောင့် install လုပ်ရမလဲ?</p>
    <div class="section-divider fade-up"></div>

    <div class="features-grid">
      <div class="feature-card fade-up">
        <span class="f-icon">📱</span>
        <div class="f-en">Works like a native app</div>
        <div class="f-mm">Native app ကဲ့သို့ အလုပ်လုပ်</div>
      </div>
      <div class="feature-card fade-up">
        <span class="f-icon">⚡</span>
        <div class="f-en">Lightning fast</div>
        <div class="f-mm">လျှပ်စစ်လို မြန်</div>
      </div>
      <div class="feature-card fade-up">
        <span class="f-icon">🔔</span>
        <div class="f-en">Push notifications</div>
        <div class="f-mm">သတင်းပေးပို့မှု</div>
      </div>
      <div class="feature-card fade-up">
        <span class="f-icon">💾</span>
        <div class="f-en">Works offline</div>
        <div class="f-mm">Offline မှာလည်း အလုပ်လုပ်</div>
      </div>
      <div class="feature-card fade-up">
        <span class="f-icon">🆓</span>
        <div class="f-en">100% free, no app store</div>
        <div class="f-mm">လုံးဝ အခမဲ့</div>
      </div>
      <div class="feature-card fade-up">
        <span class="f-icon">🔒</span>
        <div class="f-en">Safe and secure</div>
        <div class="f-mm">လုံခြုံစိတ်ချရ</div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     QR CODE + SHARE
══════════════════════════════════════════ -->
<section class="section-alt" style="text-align:center;">
  <div class="section-inner">
    <h2 class="section-title fade-up">Share with <span>Friends</span></h2>
    <p class="section-sub fade-up">သူငယ်ချင်းများကို ဝေမျှပါ</p>
    <p style="color:var(--dim);font-size:0.88rem;margin-bottom:28px;" class="fade-up">Scan to install WiseInvest on another device</p>
    <div class="section-divider fade-up"></div>

    <div class="qr-card fade-up">
      <div id="qr-canvas"></div>
    </div>

    <div class="share-row fade-up">
      <button class="share-btn btn-copy" id="copy-link-btn" onclick="copyInstallLink()">
        <span>🔗</span> Copy Link
      </button>
      <a class="share-btn btn-fb"
         href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwiseinvest.it.com%2Finstall.php"
         target="_blank" rel="noopener">
        <span>f</span> Share on Facebook
      </a>
      <a class="share-btn btn-tg"
         href="https://t.me/share/url?url=https%3A%2F%2Fwiseinvest.it.com%2Finstall.php&text=Install%20WiseInvest%20App%20%E2%80%94%20Free%20financial%20education"
         target="_blank" rel="noopener">
        <span>&#9992;</span> Share on Telegram
      </a>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     FAQ
══════════════════════════════════════════ -->
<section>
  <div class="section-inner">
    <h2 class="section-title fade-up">Frequently Asked <span>Questions</span></h2>
    <div class="section-divider fade-up"></div>

    <div class="faq-list">

      <div class="faq-item fade-up">
        <button class="faq-q" onclick="toggleFaq(this)">
          <span class="faq-q-text">
            Is this app safe?
            <small>ဒီ app လုံခြုံပါသလား?</small>
          </span>
          <span class="faq-chevron">&#9660;</span>
        </button>
        <div class="faq-a">
          <div class="faq-a-inner">
            Yes, WiseInvest uses HTTPS encryption and Cloudflare security on every page. Your data is protected with industry-standard practices. This PWA installs directly from our official website — no third-party app stores involved.
          </div>
        </div>
      </div>

      <div class="faq-item fade-up">
        <button class="faq-q" onclick="toggleFaq(this)">
          <span class="faq-q-text">
            Why isn't it on Google Play Store?
            <small>ဘာကြောင့် Play Store မှာ မရှိတာလဲ?</small>
          </span>
          <span class="faq-chevron">&#9660;</span>
        </button>
        <div class="faq-a">
          <div class="faq-a-inner">
            We use Progressive Web App (PWA) technology — no app store needed. You get all the benefits of a native app (home screen icon, offline access, push notifications) without app store fees or approval delays. Updates are instant too.
          </div>
        </div>
      </div>

      <div class="faq-item fade-up">
        <button class="faq-q" onclick="toggleFaq(this)">
          <span class="faq-q-text">
            How do I uninstall?
            <small>ဘယ်လို ဖျက်ရမလဲ?</small>
          </span>
          <span class="faq-chevron">&#9660;</span>
        </button>
        <div class="faq-a">
          <div class="faq-a-inner">
            Android: Long-press the WiseInvest icon on your home screen and tap "Uninstall."<br>
            iOS: Long-press the icon and tap "Remove App."<br>
            Desktop (Chrome/Edge): Settings &#8594; Apps &#8594; WiseInvest &#8594; Uninstall.
          </div>
        </div>
      </div>

      <div class="faq-item fade-up">
        <button class="faq-q" onclick="toggleFaq(this)">
          <span class="faq-q-text">
            Does it work without internet?
            <small>Internet မရှိရင် အလုပ်လုပ်လား?</small>
          </span>
          <span class="faq-chevron">&#9660;</span>
        </button>
        <div class="faq-a">
          <div class="faq-a-inner">
            Yes. Content you've already visited is cached and works offline thanks to our service worker. Live market data and new articles need a connection, but the app shell and previously loaded pages remain accessible offline.
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     FOOTER
══════════════════════════════════════════ -->
<footer class="page-footer">
  <a href="https://wiseinvest.it.com" class="footer-back">&#8592; Back to Website</a>
  <p class="footer-copy">&copy; <?php echo date('Y'); ?> WiseInvest. All rights reserved.</p>
  <p class="footer-pwa">Powered by Progressive Web App technology</p>
</footer>


<!-- ══════════════════════════════════════════
     COMPONENT B — iOS auto-popup install guide
     Revealed by JS only on iOS Safari, first visit, after 2s.
     Placed before the scripts so getElementById resolves at parse time.
══════════════════════════════════════════ -->
<div id="ios-popup" class="ios-popup-overlay" hidden role="dialog" aria-modal="true" aria-label="How to install WiseInvest on iPhone">
  <div class="ios-popup-card">
    <button class="ios-popup-close" aria-label="Close install guide" onclick="closeIosPopup()">✕</button>

    <div class="ios-slides">

      <!-- Slide 1: Share -->
      <div class="ios-slide active" data-slide="0">
        <svg class="ios-svg" viewBox="0 0 120 210" fill="none" aria-hidden="true">
          <rect x="20" y="6" width="80" height="170" rx="16" stroke="#475569" stroke-width="3"/>
          <rect x="28" y="148" width="64" height="20" rx="10" fill="rgba(212,175,55,0.10)"/>
          <g class="svg-pulse" stroke="#d4af37" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M60 150 v-22"/>
            <path d="M52 136 l8 -8 l8 8"/>
            <path d="M46 144 v14 a3 3 0 0 0 3 3 h22 a3 3 0 0 0 3 -3 v-14"/>
          </g>
          <path class="svg-arrow" d="M60 188 v14 M54 196 l6 6 l6 -6" stroke="#d4af37" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <h3>Step 1: Tap the Share button</h3>
        <p class="mm">အဆင့် ၁: Share ခလုတ်ကို နှိပ်ပါ</p>
        <p class="sub">The square with an arrow ↗ at the bottom of Safari
          <span class="mm">Safari အောက်ခြေက ↗ မြှားပါသော ခလုတ်</span></p>
      </div>

      <!-- Slide 2: Add to Home Screen -->
      <div class="ios-slide" data-slide="1">
        <svg class="ios-svg" viewBox="0 0 120 210" fill="none" aria-hidden="true">
          <rect x="20" y="6" width="80" height="198" rx="16" stroke="#475569" stroke-width="3"/>
          <g stroke="#475569" stroke-width="2.5" stroke-linecap="round">
            <line x1="34" y1="30" x2="86" y2="30"/>
            <line x1="34" y1="44" x2="74" y2="44"/>
          </g>
          <g class="svg-scroll">
            <rect x="30" y="92" width="60" height="26" rx="8" fill="rgba(212,175,55,0.18)" stroke="#d4af37" stroke-width="2.5"/>
            <path d="M44 105 h12 M50 99 v12" stroke="#d4af37" stroke-width="2.5" stroke-linecap="round"/>
            <line x1="64" y1="105" x2="82" y2="105" stroke="#d4af37" stroke-width="2.5" stroke-linecap="round"/>
          </g>
          <path class="svg-scroll" d="M60 150 v16 M53 159 l7 7 l7 -7" stroke="#d4af37" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <h3>Step 2: Scroll down → Add to Home Screen</h3>
        <p class="mm">အဆင့် ၂: အောက်ဆွဲ၍ "Add to Home Screen" နှိပ်ပါ</p>
        <p class="sub">It's below the apps, in the actions list
          <span class="mm">App များ၏အောက်၊ လုပ်ဆောင်ချက်စာရင်းတွင်</span></p>
      </div>

      <!-- Slide 3: Add / Done -->
      <div class="ios-slide" data-slide="2">
        <svg class="ios-svg" viewBox="0 0 120 210" fill="none" aria-hidden="true">
          <rect x="20" y="6" width="80" height="198" rx="16" stroke="#475569" stroke-width="3"/>
          <g class="svg-pop">
            <rect x="46" y="60" width="28" height="28" rx="7" fill="rgba(212,175,55,0.18)" stroke="#d4af37" stroke-width="2.5"/>
            <path d="M54 74 l4 4 l8 -9" stroke="#d4af37" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
          </g>
          <text x="60" y="104" text-anchor="middle" fill="#94a3b8" font-size="9" font-family="sans-serif">WiseInvest</text>
        </svg>
        <h3>Step 3: Tap "Add" → Done! ✨</h3>
        <p class="mm">အဆင့် ၃: "Add" နှိပ်ပါ → ပြီးပါပြီ! ✨</p>
        <p class="sub">WiseInvest appears on your home screen
          <span class="mm">WiseInvest သင့်ဖုန်းမျက်နှာပြင်တွင် ပေါ်လာမည်</span></p>
      </div>

    </div>

    <button class="ios-nav prev" aria-label="Previous step" onclick="iosPopupStep(-1)">‹</button>
    <button class="ios-nav next" aria-label="Next step" onclick="iosPopupStep(1)">›</button>

    <div class="ios-dots" role="tablist" aria-label="Install steps">
      <button class="dot active" data-go="0" aria-label="Go to step 1"></button>
      <button class="dot" data-go="1" aria-label="Go to step 2"></button>
      <button class="dot" data-go="2" aria-label="Go to step 3"></button>
    </div>

    <button class="ios-popup-cta" onclick="closeIosPopup()">Got it! · ရပါပြီ</button>
    <label class="ios-popup-dna"><input type="checkbox" id="ios-dna"> Don't show again · နောက်ထပ်မပြပါနှင့်</label>
  </div>
</div>

<script src="/pwa-register.js" defer></script>

<script>
(function () {
  'use strict';

  const INSTALL_URL = 'https://wiseinvest.it.com/install.php';

  /* ── Device detection ─────────────────────────────────── */
  const ua        = navigator.userAgent;
  const isIOS     = /iphone|ipad|ipod/i.test(ua);
  const isAndroid = /android/i.test(ua);
  const isChrome  = /chrome|crios/i.test(ua) && !/edg/i.test(ua);
  const isEdge    = /edg/i.test(ua);
  const isSafari  = /safari/i.test(ua) && !/chrome|crios/i.test(ua);
  const isFirefox = /firefox|fxios/i.test(ua);
  const isDesktop = !isIOS && !isAndroid;
  const isInstalled = navigator.standalone === true ||
                      window.matchMedia('(display-mode: standalone)').matches;

  function showBlock(id) {
    document.querySelectorAll('.device-block').forEach(function (b) {
      b.classList.remove('active');
    });
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
  }

  function setBtnText(text) {
    const el = document.getElementById('install-btn-text');
    if (el) el.textContent = text;
  }

  function hideInstallBtn() {
    document.getElementById('main-install-btn').style.display = 'none';
  }

  function showInstalledBadge() {
    hideInstallBtn();
    document.getElementById('installed-badge').classList.add('show');
  }

  /* ── Initial UI state ─────────────────────────────────── */
  if (isInstalled) {
    showInstalledBadge();
    showBlock('block-android');

  } else if (isIOS && isSafari) {
    showBlock('block-ios');
    setBtnText('Show Instructions ↓');
    document.getElementById('main-install-btn').addEventListener('click', function () {
      document.querySelector('.section-alt').scrollIntoView({ behavior: 'smooth' });
    });

  } else if (isIOS) {
    // iOS but NOT Safari (Chrome/Firefox/etc.) — can't install here.
    // Show the warning banner AND keep the visual guide cards visible.
    document.getElementById('ios-browser-warning').hidden = false;
    document.body.classList.add('has-warning');
    showBlock('block-ios');
    setBtnText('Show Instructions ↓');
    document.getElementById('main-install-btn').addEventListener('click', function () {
      document.querySelector('.section-alt').scrollIntoView({ behavior: 'smooth' });
    });

  } else if (isAndroid && isChrome) {
    showBlock('block-android');
    setBtnText('Install Now');

  } else if (isDesktop && (isChrome || isEdge)) {
    showBlock('block-desktop');
    setBtnText('Install on Desktop');

  } else {
    showBlock('block-unsupported');
    hideInstallBtn();
  }

  /* ── beforeinstallprompt ──────────────────────────────── */
  /* The event is captured early in <head> into window.deferredPrompt so it
     isn't missed on repeat visits. The button below just consumes it. */

  document.getElementById('main-install-btn').addEventListener('click', function () {
    // On iOS this button is the "Show Instructions" scroll button (wired
    // separately above) — iOS never fires beforeinstallprompt in any browser.
    if (isIOS) return;
    if (!window.deferredPrompt) {
      alert('Install not available right now. Please refresh the page, or use your browser menu to install.');
      return;
    }
    window.deferredPrompt.prompt();
    window.deferredPrompt.userChoice.then(function (result) {
      if (result.outcome === 'accepted') showInstalledBadge();
      window.deferredPrompt = null;
    });
  });

  window.addEventListener('appinstalled', showInstalledBadge);

  /* ── iOS auto-popup install guide (Component B) ───────── */
  function trackIosPopup() {
    var urlParams = new URLSearchParams(window.location.search);
    var source = urlParams.get('source') || 'direct';
    fetch('/api/track-install.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ event_type: 'ios_popup_shown', device_type: 'ios', source: source })
    }).catch(function () {});
  }

  var iosCur = 0, iosTimer = null;
  function iosGo(n) {
    iosCur = (n + 3) % 3;
    document.querySelectorAll('.ios-slide').forEach(function (s, i) { s.classList.toggle('active', i === iosCur); });
    document.querySelectorAll('#ios-popup .dot').forEach(function (d, i) { d.classList.toggle('active', i === iosCur); });
  }
  function resetIosTimer() {
    clearInterval(iosTimer);
    iosTimer = setInterval(function () { iosGo(iosCur + 1); }, 4000);
  }
  window.iosPopupStep = function (dir) { iosGo(iosCur + dir); resetIosTimer(); };
  window.closeIosPopup = function () {
    var p = document.getElementById('ios-popup');
    if (p) p.hidden = true;
    clearInterval(iosTimer);
    var dna = document.getElementById('ios-dna');
    if (dna && dna.checked) localStorage.setItem('ios_popup_seen', '1');
  };

  var iosPopup = document.getElementById('ios-popup');
  if (iosPopup) {
    iosPopup.querySelectorAll('.dot').forEach(function (d) {
      d.addEventListener('click', function () { iosGo(+d.dataset.go); resetIosTimer(); });
    });
    iosPopup.addEventListener('click', function (e) { if (e.target === iosPopup) window.closeIosPopup(); });

    if (isIOS && isSafari && !isInstalled && !localStorage.getItem('ios_popup_seen')) {
      setTimeout(function () {
        iosPopup.hidden = false;
        resetIosTimer();
        trackIosPopup();
      }, 2000);
    }
  }

  /* ── QR Code ──────────────────────────────────────────── */
  window.addEventListener('load', function () {
    if (typeof QRCode !== 'undefined') {
      new QRCode(document.getElementById('qr-canvas'), {
        text: INSTALL_URL,
        width: 160,
        height: 160,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
    }
  });

  /* ── Copy link ────────────────────────────────────────── */
  window.copyInstallLink = function () {
    function fallback() {
      const ta = document.createElement('textarea');
      ta.value = INSTALL_URL;
      ta.style.cssText = 'position:fixed;opacity:0';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
    }
    function done() {
      const btn = document.getElementById('copy-link-btn');
      btn.classList.add('copied');
      btn.innerHTML = '<span>✓</span> Copied!';
      setTimeout(function () {
        btn.classList.remove('copied');
        btn.innerHTML = '<span>🔗</span> Copy Link';
      }, 2200);
    }
    if (navigator.clipboard) {
      navigator.clipboard.writeText(INSTALL_URL).then(done).catch(function () { fallback(); done(); });
    } else {
      fallback();
      done();
    }
  };

  /* ── FAQ accordion ────────────────────────────────────── */
  window.toggleFaq = function (btn) {
    const item   = btn.closest('.faq-item');
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(function (el) {
      el.classList.remove('open');
    });
    if (!isOpen) item.classList.add('open');
  };

  /* ── Scroll fade-up animations ────────────────────────── */
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.fade-up').forEach(function (el) {
    observer.observe(el);
  });

}());
</script>

<script>
(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var source = urlParams.get('source') || 'direct';
    var device = /android/i.test(navigator.userAgent) ? 'android' :
                 /iphone|ipad|ipod/i.test(navigator.userAgent) ? 'ios' :
                 'desktop';

    fetch('/api/track-install.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            event_type: 'page_view',
            device_type: device,
            source: source
        })
    });
})();
</script>

</body>
</html>
