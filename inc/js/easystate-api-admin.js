jQuery(document).ready(function($) {
    $('#easystate-fetch-data').on('click', function(e) {
        e.preventDefault();

        $('#loading-spinner').show();

        const startTime = new Date().getTime();
        const timerInterval = setInterval(function () {
            const currentTime = new Date().getTime();
            const elapsedTime = Math.floor((currentTime - startTime) / 1000);
            $('#easy-state-timer').text('Time elapsed: ' + elapsedTime + ' seconds');
        }, 1000);

        const data = {
            action: 'easystate_ajax_fetch_data',
            security: $('#easystate_api_call_nonce_field').val()
        };

        // Perform AJAX request
        $.post(easystate_ajax_obj.ajax_url, data, function(response) {

            clearInterval(timerInterval);

            $('#loading-spinner').hide();

            alert('Data fetched successfully');
            const formattedJson = JSON.stringify(response, null, 2);
            $('#api-data-container').html('<pre>' + formattedJson + '</pre>');

        }).fail(function() {
            clearInterval(timerInterval);
            alert( "error" );
        });
    });

    $('#clear_queue').on('click', function(e) {
        e.preventDefault();

        const data = {
            action: 'easystate_ajax_clear_queue',
        };

        // Perform AJAX request
        $.post(easystate_ajax_obj.ajax_url, data, function(response) {
            alert('Data Cleared successfully');

        }).fail(function() {
            alert( "error" );
        });
    });

    $('#es_api_settings\\[es_agent_select\\]').select2({
        allowClear: true
    });

    $('#check_api_credentials').on('click', function(e) {
        e.preventDefault();
        const clientId = $('#es_api_credentials\\[es_client_id\\]').val();
        const clientSecret = $('#es_api_credentials\\[es_client_secret\\]').val();

        const data = {
            action: 'easystate_ajax_check_credentials',
            es_client_id: clientId,
            es_client_secret: clientSecret,
        };

        $.post(easystate_ajax_obj.ajax_url, data, function(response) {
            console.log(response);
            if (response.success) {
                alert(response.data);
            } else {
                alert(response.data);
            }
        }).fail(function() {
            alert("An error occurred. Please try again.");
        });
    });

});
