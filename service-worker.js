'use strict';

const CACHE_NAME = 'wiseinvest-v11';

const PRECACHE_URLS = [
  '/',
  '/style.css',
  '/main.js',
  '/offline.html',
  '/icons/web-app-manifest-192x192.png',
  '/icons/web-app-manifest-512x512.png',
  '/icons/favicon.ico'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method !== 'GET') return;
  if (url.origin !== self.location.origin) return;

  const path = url.pathname;

  // Network-first: PHP pages and API routes (always fresh data)
  if (path.startsWith('/api/') || path.endsWith('.php')) {
    event.respondWith(
      fetch(request).catch(() =>
        caches.match(request).then(cached =>
          cached || caches.match('/offline.html')
        )
      )
    );
    return;
  }

  // Cache-first: static assets
  if (/\.(css|js|png|jpg|jpeg|svg|ico|woff|woff2|ttf)$/i.test(path)) {
    event.respondWith(
      caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(response => {
          if (!response || response.status !== 200 || response.type === 'opaque') return response;
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
          return response;
        });
      })
    );
    return;
  }

  // Navigation: network-first, fall back to offline.html
  event.respondWith(
    fetch(request).catch(() =>
      caches.match(request).then(cached =>
        cached || (request.mode === 'navigate' ? caches.match('/offline.html') : Response.error())
      )
    )
  );
});

// Push notification support (replaces sw.js)
self.addEventListener('push', event => {
  let data = { title: 'WiseInvest', body: 'You have a new message.' };
  try { data = event.data.json(); } catch (e) {}
  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/icons/web-app-manifest-192x192.png',
      badge: '/icons/favicon-96x96.png',
      data: data.url || '/'
    })
  );
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil(clients.openWindow(event.notification.data || '/'));
});
