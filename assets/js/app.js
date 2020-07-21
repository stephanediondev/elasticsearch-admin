require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');

require('select2');

import bsCustomFileInput from 'bs-custom-file-input';
global.bsCustomFileInput = bsCustomFileInput;

import { saveAs } from 'file-saver';
var slug = require('slug');
slug.charmap['/'] = '-';
slug.charmap['?'] = '-';
slug.charmap['='] = '-';
global.slug = slug;

function search() {
    //reset
    $(document).find('tbody tr').attr('data-score', 0);

    //.searchCheckbox
    $(document).find('.searchCheckbox').each(function() {
        var value = $(this).val();
        if (true == $(this).is(':checked')) {
        } else {
            var score = $('.' + value).attr('data-score');
            $('.' + value).attr('data-score', parseInt(score) - 1);
        }
    });

    //#searchInput
    var value = false;
    if (0 < $('#searchInput').length) {
        value = $('#searchInput').val();
        value = value.toLowerCase();
    }

    //check
    $('tbody').find('tr').each(function() {
        var row = $(this);

        if (false == value || '' == value) {
            if (0 == parseInt(row.attr('data-score'))) {
                row.removeClass('d-none');
            } else {
                row.addClass('d-none');
            }

        } else {
            var search = row.attr('data-search');
            search = search.toLowerCase();
            if (search.indexOf(value) !== -1 && 0 == parseInt(row.attr('data-score'))) {
                row.removeClass('d-none');
            } else {
                row.addClass('d-none');
            }
        }
    });

    //total
    var total = $('tbody').find('tr:visible').length;
    $(document).find('h3 span.badge').text(total);
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

if('serviceWorker' in navigator && 'https:' == window.location.protocol) {
    navigator.serviceWorker.register(app_base_url + 'serviceworker.js')
    .then(function(ServiceWorkerRegistration) {
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
    bsCustomFileInput.init('input[type="file"]');

    $('label.required').append(' <small class="badge badge-info ml-1">' + trans_required + '</small>');

    $('select').select2({
        theme: 'bootstrap4',
    });

    $(document).on('keyup', '#searchInput', function() {
        search();
    });

    $(document).on('click', '.searchCheckbox', function() {
        search();
    });

    $(document).on('click', '.dashboard-table-expand', function(event) {
        event.preventDefault();
        $(this).remove();
        var table = $($(this).attr('href'));
        table.find('tr').removeClass('d-none');
    });
});
