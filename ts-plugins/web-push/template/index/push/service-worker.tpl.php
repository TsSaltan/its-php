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
    var data = event.data.json();
    console.log(data);

    var title = "Browser push notification",
        body = event.data.text(),
        icon = "<?=($this->getURI('/push/icon.png'))?>";

    clickLink = "<?=($this->makeURI('/', [], 'from=push'))?>";

    if(typeof data === 'object'){
        if('title' in data) title = data.title;
        if('body' in data) body = data.body;
        if('icon' in data) icon = data.icon;
        if('link' in data) clickLink = data.link;
    } 

    event.waitUntil(self.registration.showNotification(title, {
        body: body,
        icon: icon,
    }));
});

self.addEventListener('notificationclick', function(event) {
  console.log('[Service Worker] Notification click Received.');
  console.log(event);

  event.notification.close();

  event.waitUntil(
    //clients.openWindow('https://developers.google.com/web/')
    clients.openWindow(clickLink)
  );
});

/*
self.addEventListener('pushsubscriptionchange', function(event) {
  console.log('[Service Worker]: \'pushsubscriptionchange\' event fired.');
  const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
  event.waitUntil(
    self.registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: applicationServerKey
    })
    .then(function(newSubscription) {
      // TODO: Send to application server
      console.log('[Service Worker] New subscription: ', newSubscription);
    })
  );
});
*/