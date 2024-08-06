jQuery(document).ready(function($) {
    $('#check-updates').on('click', function() {
        $.ajax({
            url: ajaxurl, // This variable is provided by WordPress
            type: 'POST',
            data: {
                action: 'check_updates'
            },
            success: function(response) {
                alert('Update check complete: ' + response);
            }
        });
    });
});
