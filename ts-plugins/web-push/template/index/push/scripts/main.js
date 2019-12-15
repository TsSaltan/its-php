/*
*
*  Push Notifications codelab
*  Copyright 2015 Google Inc. All rights reserved.
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*      https://www.apache.org/licenses/LICENSE-2.0
*
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License
*
*/
'use strict';

var WebPush = {
    /**
     * Web Push public key
     * @type String
     */
    publicKey: "", // applicationServerPublicKey
    swPath: "/service-worker.js",

    /**
     * Is current client subscribed
     * @type Boolean
     */
    isSubscribed: false,

    swRegistration: null,

    subscriptionData: null,

    urlB64ToUint8Array: function(base64String){
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
    },

    init: function(callback){
        callback = callback || function(){};
        var that = this;
        if('serviceWorker' in navigator && 'PushManager' in window){
            console.log('[WebPush] Service Worker and Push is supported');

            navigator.serviceWorker.register(that.swPath)
            .then(function(swReg) {
                console.log('[WebPush] Service Worker is registered', swReg);

                that.swRegistration = swReg;
                that.checkSubscription(function(){
                    callback();
                });
            })
            .catch(function(error) {
                console.error('[WebPush] Service Worker Error', error);
                callback();
            });
        } else {    
            console.warn('[WebPush] Push messaging is not supported');
            callback();
        }
    },

    checkSubscription: function(onChecked){
        onChecked = onChecked || function(){};
        var that = this;
        this.swRegistration.pushManager.getSubscription()
        .then(function(subscription){
            that.isSubscribed = subscription !== null;
            that.subscriptionData = subscription;

            if (that.isSubscribed) {
                console.log('[WebPush] Client IS subscribed.');
            } else {
                console.log('[WebPush] Client is NOT subscribed.');
            }

            onChecked();
        })
        .catch(function(err){
            onChecked();
        });
    },

    subscribe: function(onSuccess, onError){
        onSuccess = onSuccess || function(){};
        onError = onError || function(){};

        if(this.isSubscribed && this.subscriptionData !== null){
            return onSuccess(this.subscriptionData);
        }
        try {
            var that = this;
            const applicationServerKey = this.urlB64ToUint8Array(this.publicKey);
            this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            })
            .then(function(subscription) {
                console.log('[WebPush] Client subscribed.');
                that.subscriptionData = subscription;
                that.isSubscribed = true;
                onSuccess(subscription);
            })
            .catch(function(err) {
                console.log('[WebPush] Failed to subscribe the client: ', err);
                onError(err);
            });
        }
        catch(err){
            console.log('[WebPush] Failed to init subscribtion: ', err);
            onError(err);
        }
    }
};