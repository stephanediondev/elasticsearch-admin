require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');

import { saveAs } from 'file-saver';
var slug = require('slug');
slug.charmap['/'] = '-';
slug.charmap['?'] = '-';
slug.charmap['='] = '-';
global.slug = slug;

global.serviceWorkerAvailable = function serviceWorkerAvailable() {
    return 'serviceWorker' in navigator && 'https:' == window.location.protocol;
}

global.sleep = function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

global.createToast = function createToast(body) {
    var toast = `<div class="toast bg-dark text-light border border-secondary" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
            ${body}
        </div>
    </div>`;
    $('#toast-container').prepend(toast);
    var toastObject = $('#toast-container .toast').first();
    toastObject.toast({'autohide': true, 'delay': 5000});
    toastObject.toast('show')
}

function messageToServiceWorker(content) {
    if (true === serviceWorkerAvailable()) {
        navigator.serviceWorker.ready.then(function() {
            return new Promise(function(resolve, reject) {
                var messageChannel = new MessageChannel();
                messageChannel.port1.onmessage = function(event) {
                    if(event.data.error) {
                        reject(event.data.error);
                    } else {
                        resolve(event.data);
                    }
                };
                if(navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage(content, [messageChannel.port2]);
                }
            });
        });
    }
}

var buttonInstall = document.getElementById('button_install');
if (buttonInstall) {
    buttonInstall.addEventListener('click', function(event) {
        event.preventDefault();
    });
}

if (true === serviceWorkerAvailable()) {
    navigator.serviceWorker.register(app_base_url + 'serviceworker.js')
    .then(function(ServiceWorkerRegistration) {
        if (buttonInstall) {
            var standalone = window.matchMedia('(display-mode: standalone)');
            if (false === standalone.matches) {
                buttonInstall.classList.remove('d-none');

                window.addEventListener('beforeinstallprompt', function(BeforeInstallPromptEvent) {
                    BeforeInstallPromptEvent.preventDefault();

                    buttonInstall.addEventListener('click', function() {
                        BeforeInstallPromptEvent.prompt();

                        BeforeInstallPromptEvent.userChoice.then(function(AppBannerPromptResult) {
                            if ('dismissed' == AppBannerPromptResult.outcome) {
                                buttonInstall.classList.add('d-none');
                            }
                        });
                    });
                });
            }
        }

        ServiceWorkerRegistration.addEventListener('updatefound', function() {
            messageToServiceWorker({'command': 'reload'});
        });
    });

    navigator.serviceWorker.addEventListener('message', function(MessageEvent) {
        switch(MessageEvent.data.type) {
            case 'reload':
                document.location.reload(true);
                break;
        }
    });
}

$(document).ready(function () {
    $('label.required').append(' <small class="badge bg-light text-dark ml-1">' + trans_required + '</small>');

    $(document).on('click', '.dashboard-table-expand', function(event) {
        event.preventDefault();
        $(this).remove();
        var table = $($(this).attr('href'));
        table.find('tr').removeClass('d-none');
    });
});
