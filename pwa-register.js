(function () {
  'use strict';

  function detectBrowser() {
    var ua = navigator.userAgent;
    if (/chrome/i.test(ua) && !/edge|opr/i.test(ua)) return 'chrome';
    if (/safari/i.test(ua) && !/chrome/i.test(ua)) return 'safari';
    if (/firefox/i.test(ua)) return 'firefox';
    if (/edge/i.test(ua)) return 'edge';
    return 'other';
  }

  function detectDevice() {
    var ua = navigator.userAgent;
    if (/android/i.test(ua)) return 'android';
    if (/iphone|ipad|ipod/i.test(ua)) return 'ios';
    if (/windows|mac|linux/i.test(ua) && !/mobile/i.test(ua)) return 'desktop';
    return 'other';
  }

  function trackEvent(eventType, deviceType) {
    var urlParams = new URLSearchParams(window.location.search);
    var source = urlParams.get('source') || 'direct';
    fetch('/api/track-install.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        event_type: eventType,
        device_type: deviceType,
        browser: detectBrowser(),
        source: source
      })
    }).catch(function (e) { console.log('[PWA] Track error:', e); });
  }

  // Register service worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/service-worker.js')
        .then(function (reg) {
          console.log('[PWA] Service worker registered, scope:', reg.scope);
        })
        .catch(function (err) {
          console.error('[PWA] Service worker registration failed:', err);
        });
    });
  }

  // PWA install funnel tracking
  window.addEventListener('beforeinstallprompt', function () {
    trackEvent('install_clicked', detectDevice());
  });
  window.addEventListener('appinstalled', function () {
    trackEvent('installed', detectDevice());
  });

  // iOS Safari one-time install tooltip
  function isIOS() {
    return /iphone|ipad|ipod/i.test(navigator.userAgent);
  }
  function isStandalone() {
    return (navigator.standalone === true) ||
      window.matchMedia('(display-mode: standalone)').matches;
  }

  if (isIOS() && !isStandalone()) {
    if (!localStorage.getItem('wi_ios_tip')) {
      window.addEventListener('load', function () {
        setTimeout(function () {
          var tip = document.createElement('div');
          tip.id = 'ios-install-tip';
          tip.innerHTML =
            '<div style="font-size:0.87rem;line-height:1.65;">' +
              '<strong style="color:#d4af37;">Install WiseInvest</strong><br>' +
              'Tap <strong>Share</strong> &#11014; then<br>' +
              '<strong>Add to Home Screen</strong> &#10133;' +
            '</div>' +
            '<button onclick="document.getElementById(\'ios-install-tip\').remove()" ' +
              'style="margin-top:10px;background:none;border:1px solid rgba(212,175,55,0.4);' +
              'color:#d4af37;padding:4px 14px;border-radius:20px;cursor:pointer;' +
              'font-size:0.8rem;font-family:inherit;">Got it</button>';
          tip.style.cssText = [
            'position:fixed', 'bottom:16px', 'left:50%', 'transform:translateX(-50%)',
            'background:#0f172a', 'border:1px solid rgba(212,175,55,0.3)',
            'border-radius:14px', 'padding:16px 20px', 'text-align:center',
            'color:#e2e8f0', 'z-index:99999', 'box-shadow:0 8px 32px rgba(0,0,0,0.6)',
            'font-family:Inter,system-ui,sans-serif',
            'max-width:280px', 'width:calc(100vw - 48px)'
          ].join(';');
          document.body.appendChild(tip);
          localStorage.setItem('wi_ios_tip', '1');
          trackEvent('ios_instructions_shown', 'ios');
        }, 3000);
      });
    }
  }
}());
