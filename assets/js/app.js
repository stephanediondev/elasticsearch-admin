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

$(document).ready(function () {
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
});
