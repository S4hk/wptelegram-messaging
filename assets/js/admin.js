(function($) {
    'use strict';

    $(function() {
        // Handle manual send click
        $('.wptelegram-messaging-send-manual').on('click', function(e) {
            e.preventDefault();

            var $link = $(this);
            var userId = $link.data('user-id');
            var nonce = $link.data('nonce');
            var originalText = $link.text();

            if ($link.hasClass('updating')) {
                return;
            }

            $link.addClass('updating').text('Sending...');

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'wptelegram_messaging_send_manual',
                    user_id: userId,
                    _wpnonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        // Reload the page to update the status column
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                        $link.removeClass('updating').text(originalText);
                    }
                },
                error: function() {
                    alert('An error occurred while sending the message.');
                    $link.removeClass('updating').text(originalText);
                }
            });
        });
    });

})(jQuery);
