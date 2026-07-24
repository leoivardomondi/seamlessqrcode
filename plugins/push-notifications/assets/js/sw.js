self.addEventListener('push', (event) => {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    if(event.data) {
        const notification = event.data.json();

        event.waitUntil(self.registration.showNotification(notification.title, {
            body: notification.description,
            icon: notification.icon,
            data: {
                notifURL: notification.url
            }
        }));
    }
});

self.addEventListener('notificationclick', (event) => {
    event.waitUntil(clients.openWindow(event.notification.data.notifURL));
});
