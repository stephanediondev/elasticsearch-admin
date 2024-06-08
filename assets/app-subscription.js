import $ from 'jquery';
import { serviceWorkerEnabled, createToast } from 'app';

function deleteSubscription(url) {
    window.location.href = url;
}

function testNotification(url) {
    fetch(url, {
        credentials: 'include',
        method: 'GET',
        mode: 'cors',
    }).then(function(response) {
        return response.json();
    }).then(function(json) {
        if ('undefined' !== typeof json.message) {
            createToast(json.message);
        } else {
            createToast('Sent');
        }
    })
    .catch(function(error) {
        console.log(error);
    });
}

function getSubscription() {
    if (true == serviceWorkerEnabled) {
        navigator.serviceWorker.ready.then(function(ServiceWorkerRegistration) {
            if ('pushManager' in ServiceWorkerRegistration) {
                ServiceWorkerRegistration.pushManager.getSubscription()
                .then(function(PushSubscription) {
                    if (PushSubscription && 'object' === typeof PushSubscription) {
                        $(document).find('.actions').each(function() {
                            var deleteButton = $(this).find('button');
                            var unsubscribeButton = $(this).find('.unsubscribe-button');
                            if (PushSubscription.endpoint == unsubscribeButton.data('endpoint')) {
                                unsubscribeButton.removeClass('d-none');
                                deleteButton.addClass('d-none');
                            }
                        });
                    }
                });
            }
        });
    }
}

function pushManagerUnsubscribe(url) {
    if (true == serviceWorkerEnabled) {
        navigator.serviceWorker.ready.then(function(ServiceWorkerRegistration) {
            if ('pushManager' in ServiceWorkerRegistration) {
                ServiceWorkerRegistration.pushManager.getSubscription()
                .then(function(PushSubscription) {
                    if (PushSubscription && 'object' === typeof PushSubscription) {
                        PushSubscription.unsubscribe()
                        .then(function() {
                            deleteSubscription(url);
                        })
                        .catch(function(error) {
                            console.log(error);
                        });
                    }
                });
            }
        });
    }
}

function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');

    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);

    for(var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function getSubscriptionCreate() {
    if (true == serviceWorkerEnabled) {
        navigator.serviceWorker.ready.then(function(ServiceWorkerRegistration) {
            if ('pushManager' in ServiceWorkerRegistration) {
                ServiceWorkerRegistration.pushManager.getSubscription()
                .then(function(PushSubscription) {
                    if (PushSubscription && 'object' === typeof PushSubscription) {
                        var toJSON = PushSubscription.toJSON();

                        var endpoint = document.getElementById('data_endpoint');
                        endpoint.value = PushSubscription.endpoint;

                        var publicKey = document.getElementById('data_public_key');
                        publicKey.value = toJSON.keys.p256dh;

                        var authenticationSecret = document.getElementById('data_authentication_secret');
                        authenticationSecret.value = toJSON.keys.auth;

                        var contentEncoding = document.getElementById('data_content_encoding');
                        contentEncoding.value = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
                    }
                });
            }
        });
    }
}

function pushManagerSubscribe() {
    if (true == serviceWorkerEnabled) {
        navigator.serviceWorker.ready.then(function(ServiceWorkerRegistration) {
            if ('pushManager' in ServiceWorkerRegistration) {
                ServiceWorkerRegistration.pushManager.permissionState({userVisibleOnly: true}).then(function(permissionState) {
                    if (permissionState == 'prompt' || permissionState == 'granted') {
                        ServiceWorkerRegistration.pushManager.subscribe(
                            {'applicationServerKey': urlBase64ToUint8Array(applicationServerKey), 'userVisibleOnly': true}
                        )
                        .then(function(PushSubscription) {

                            if (PushSubscription && 'object' === typeof PushSubscription) {
                                getSubscriptionCreate();
                            }
                        });
                    }
                });
            }
        });
    }
}

var allowNotifications = document.getElementById('allow-notifications');
if (allowNotifications) {
    allowNotifications.addEventListener('click', function(event) {
        event.preventDefault();

        pushManagerSubscribe();
    });
}

$(document).ready(function() {
    getSubscription();

    $(document).on('click', '.unsubscribe-button', function(event) {
        event.preventDefault();
        pushManagerUnsubscribe($(this).attr('href'));
    });

    $(document).on('click', '.test-notification-push', function(event) {
        event.preventDefault();
        testNotification($(this).attr('href'));
    });
});
