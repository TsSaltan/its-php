/*
 *  Push Notifications
 *  Language: JavaScript 
 *  @todo JSON decoding
 *  @todo click URL
 */

'use strict';
const applicationServerPublicKey = <?=json_encode($publicKey)?>;

function urlB64ToUint8Array(base64String){
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

var clickLink = null;

self.addEventListener('push', function(event) {
    console.log(`[Service Worker] Incoming push message: "${event.data.text()}"`);
    
    var data = null,
        title = "Browser push notification",
        body = event.data.text(),
        icon = "<?=($this->getURI('/push/icon.png'))?>";

    clickLink = "<?=($this->makeURI('/', [], 'from=push'))?>";

    try {
        data = event.data.json();

        if(typeof data === 'object'){
            if('title' in data) title = data.title;
            if('body' in data) body = data.body;
            if('icon' in data) icon = data.icon;
            if('link' in data) clickLink = data.link;
        } 
    } catch (error){
    }

    event.waitUntil(self.registration.showNotification(title, {
        body: body,
        icon: icon
    }));
});

self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click Received.');
    console.log(event);

    event.notification.close();

    event.waitUntil(
        clients.openWindow(clickLink)
    );
});