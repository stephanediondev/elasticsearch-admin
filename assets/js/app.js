require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');

require('select2');

import { saveAs } from 'file-saver';
var slug = require('slug');
slug.charmap['/'] = '-';
slug.charmap['?'] = '-';
slug.charmap['='] = '-';
global.slug = slug;

global.sleep = function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

global.createToast = function createToast(body) {
    var toast = `<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
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
    if('serviceWorker' in navigator && 'https:' == window.location.protocol) {
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

if('serviceWorker' in navigator && 'https:' == window.location.protocol) {
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
    $('label.required').append(' <small class="badge bg-dark text-light ml-1">' + trans_required + '</small>');

    $('select').select2({
        theme: 'bootstrap4',
    });

    $(document).on('click', '.dashboard-table-expand', function(event) {
        event.preventDefault();
        $(this).remove();
        var table = $($(this).attr('href'));
        table.find('tr').removeClass('d-none');
    });
});
