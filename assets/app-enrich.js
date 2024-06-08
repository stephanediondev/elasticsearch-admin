function refresh() {
    var indices = $('#data_indices').val();
    if (0 < indices.length) {
        indices = indices.join(',');

        fetch(app_base_url + 'admin/indices/' + indices + '/mappings/fetch', {
            credentials: 'include',
            method: 'get'
        }).then(function(response) {
            return response.json();
        }).then(function(json) {
            var options = [];
            for (var k in json) {
                if (false == options.includes(json[k])) {
                    options.push(json[k]);
                }
            }
            options.sort();

            $(document).find('.update-fields').each(function() {
                var select = $(this);
                var selectedOptions = [];
                select.find('option:selected').each(function() {
                    selectedOptions.push($(this).val());
                });

                select.find('option').remove();

                if (typeof select.attr('multiple') == 'undefined') {
                    var newOption = new Option('-', '', false, false);
                    select.append(newOption).trigger('change');
                }

                for (var k in options) {
                    var selected = false;
                    if (true == selectedOptions.includes(options[k])) {
                        selected = true;
                    }
                    var newOption = new Option(options[k], options[k], selected, selected);
                    select.append(newOption).trigger('change');
                }
            });
        }).catch(function() {
        });
    } else {
        $(document).find('.update-fields').each(function() {
            $(this).find('option').remove();
        });
    }
}

refresh();

$(document).on('change', '#data_indices', function(event) {
    refresh();
});
