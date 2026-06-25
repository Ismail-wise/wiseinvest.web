/* Wise Invest — Service Worker (push notifications) */

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(self.clients.claim()));

self.addEventListener('push', function (event) {
    const title = 'Wise Invest Support';
    const options = {
        body: 'You have a new reply from our support team. Tap to read it.',
        icon: '/images/logo.jpg',
        badge: '/images/logo.jpg',
        vibrate: [200, 100, 200, 100, 200],
        data: { url: '/dashboard.php' },
        requireInteraction: false,
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
            for (var i = 0; i < list.length; i++) {
                var c = list[i];
                if ('focus' in c) return c.focus();
            }
            return clients.openWindow('/dashboard.php');
        })
    );
});
