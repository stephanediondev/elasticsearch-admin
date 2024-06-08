import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import './styles/app.css';

import { Dropdown, Modal, Toast } from 'bootstrap';

const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new Dropdown(dropdownToggleEl));

(() => {
    'use strict'

    const storedTheme = localStorage.getItem('theme')

    const getPreferredTheme = () => {
        if (storedTheme) {
            return storedTheme
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = function (theme) {
        if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark')
        } else {
            document.documentElement.setAttribute('data-bs-theme', theme)
        }
    }

    setTheme(getPreferredTheme())

    const showActiveTheme = theme => {
        const activeThemeIcon = document.querySelector('.theme-icon-active i')
        const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`);

        activeThemeIcon.className = btnToActive.querySelector('i').className;

        document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
            element.classList.remove('active');
        })

        btnToActive.classList.add('active');
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (storedTheme !== 'light' || storedTheme !== 'dark') {
            setTheme(getPreferredTheme());
        }
    })

    window.addEventListener('DOMContentLoaded', () => {
        showActiveTheme(getPreferredTheme())

        document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const theme = toggle.getAttribute('data-bs-theme-value');
                localStorage.setItem('theme', theme);
                setTheme(theme);
                showActiveTheme(theme);
            });
        });
    })
})();

import $ from 'jquery';

import saveAs from 'file-saver';

import slug from 'slug';

slug.charmap['/'] = '-';
slug.charmap['?'] = '-';
slug.charmap['='] = '-';

export function createToast(body) {
    var toast = `<div class="toast bg-dark text-light border border-secondary" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
            ${body}
        </div>
    </div>`;
    $('#toast-container').prepend(toast);
    var toastEl = $('#toast-container .toast').first();
    if (toastEl) {
        var toast = new Toast(toastEl, {'autohide': true, 'delay': 2500});
        toast.show();
    }
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

export let serviceWorkerEnabled = false;

if('serviceWorker' in navigator && 'https:' == window.location.protocol) {
    navigator.serviceWorker.register(app_base_url + 'serviceworker.js')
    .then(function(ServiceWorkerRegistration) {
        serviceWorkerEnabled = true;

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
    $('label.required').append(' <small class="form-required badge bg-light text-dark ml-1">' + trans_required + '</small>');

    $(document).on('click', '.dashboard-table-expand', function(event) {
        event.preventDefault();
        $(this).remove();
        var table = $($(this).attr('href'));
        table.find('tr').removeClass('d-none');
    });
});
