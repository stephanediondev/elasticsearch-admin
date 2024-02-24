$(document).on('click', '.back', function(event) {
    event.preventDefault();

    $(this).parents('.step').addClass('d-none');

    var href= $(this).attr('href');
    $(href).removeClass('d-none');
});

$(document).on('submit', 'form', function(event) {
    event.preventDefault();

    var form = $(this);

    var body = $('#form-connect').serialize();
    body = body + '&' + $('#form-mappings').serialize();

    if ('form-mappings' == form.attr('id')) {
        $('#step-mappings').addClass('d-none');
        $('#step-loading').removeClass('d-none');
    }

    fetch(form.attr('action'), {
        'credentials': 'include',
        'headers': {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        'method': 'post',
        'body': body
    }).then(function(response) {
        return response.json();
    }).then(function(json) {
        if (true == json.exception) {
            createToast(json.message);

            if ('form-mappings' == form.attr('id')) {
                $('#step-loading').addClass('d-none');
                $('#step-mappings').removeClass('d-none');
            }

        } else {
            if ('form-connect' == form.attr('id')) {
                var columns = '<option value="">-</option>';
                for (var key in json.columns) {
                    var column = json.columns[key];
                    columns += '<option value="' + column + '">' + column + '</option>';
                }

                $(document).find('.select-column').each(function() {
                    $(this).html(columns);
                    var field = $(this).data('field');
                    if (json.columns.includes(field)) {
                        $(this).val(field);
                    }
                });

                $('#step-connect').addClass('d-none');
                $('#step-mappings').removeClass('d-none');
            }

            if ('form-mappings' == form.attr('id')) {
                $('#step-completed h3 .badge').text(json.documents);

                $('#step-mappings').addClass('d-none');
                $('#step-loading').addClass('d-none');
                $('#step-completed').removeClass('d-none');

                $('#errors').addClass('d-none');
                $('#step-completed tbody').html('');

                if (0 < (json.errors).length) {
                    for (var key in json.errors) {
                        var error = json.errors[key];
                        $('#step-completed tbody').append(`<tr><td>${error['_id']}</td><td>${error['status']}</td><td>${error['message']}</td></tr>`);
                    }

                    $('#errors').removeClass('d-none');
                }
            }
        }
    }).catch(function() {
    });
});
