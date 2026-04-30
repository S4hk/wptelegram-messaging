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

        // Handle bulk send click
        $('#wptelegram-messaging-send-bulk').on('click', function() {
            var $button = $(this);
            var $spinner = $button.siblings('.spinner');
            var $results = $('#bulk-send-results');
            var message = $('#bulk_message').val();
            var nonce = $('#bulk_send_nonce').val();
            var roles = [];

            $('#bulk_roles input:checked').each(function() {
                roles.push($(this).val());
            });

            if (!message) {
                alert('Please enter a message.');
                return;
            }

            if (roles.length === 0) {
                alert('Please select at least one role.');
                return;
            }

            if (!confirm('Are you sure you want to send this message to all selected users?')) {
                return;
            }

            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $results.hide();

            $.ajax({
                url: (typeof ajaxurl !== 'undefined') ? ajaxurl : wptelegramMessagingAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wptelegram_messaging_send_bulk',
                    message: message,
                    roles: roles,
                    nonce: nonce
                },
                success: function(response) {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');

                    if (response.success) {
                        $results.find('.sent-count').text(response.data.sent);
                        $results.find('.failed-count').text(response.data.failed);
                        $results.find('.skipped-count').text(response.data.skipped);
                        $results.show();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                    alert('An error occurred while sending bulk messages.');
                }
            });
        });
    });

})(jQuery);
