'use strict';

const CACHE_NAME = 'seamlessqrcode-pwa-v2';
const OFFLINE_URL = 'offline.html';
const STATIC_ASSET_PATTERN = /\.(?:css|js|png|jpe?g|svg|webp|ico|woff2?|ttf)$/i;

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll([
                OFFLINE_URL,
                'manifest.webmanifest',
                'uploads/pwa/icon-192.png',
                'uploads/pwa/icon-512.png',
                'uploads/pwa/icon-maskable-512.png'
            ]))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;

    if(request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if(request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if(url.origin === self.location.origin && STATIC_ASSET_PATTERN.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then(cached_response => {
                return cached_response || fetch(request).then(network_response => {
                    if(network_response && network_response.status === 200) {
                        const response_clone = network_response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, response_clone));
                    }

                    return network_response;
                });
            })
        );
    }
});

self.addEventListener('push', event => {
    let data = {};

    try {
        data = event.data ? event.data.json() : {};
    } catch (error) {
        data = {
            title: 'Notification',
            description: event.data ? event.data.text() : '',
        };
    }

    const title = data.title || 'Notification';
    const options = {
        body: data.description || '',
        icon: data.icon || new URL('uploads/pwa/icon-192.png', self.registration.scope).href,
        badge: data.badge || new URL('uploads/pwa/icon-maskable-512.png', self.registration.scope).href,
        data: {
            url: data.url || './'
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();

    const target_url = event.notification.data && event.notification.data.url ? event.notification.data.url : './';
    const absolute_target_url = new URL(target_url, self.registration.scope).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(window_clients => {
            for(const client of window_clients) {
                if(client.url === absolute_target_url && 'focus' in client) {
                    return client.focus();
                }
            }

            if(clients.openWindow) {
                return clients.openWindow(absolute_target_url);
            }
        })
    );
});
