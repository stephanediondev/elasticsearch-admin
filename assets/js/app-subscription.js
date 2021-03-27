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
