<?php
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

$user_id = 1; // Change if needed
$telegram_id = get_user_meta($user_id, 'wptelegram_user_id', true);
$bot_token = '';
if (function_exists('WPTG')) {
    $bot_token = WPTG()->options()->get('bot_token');
}

echo "User ID: $user_id\n";
echo "Telegram ID: $telegram_id\n";
echo "Core Bot Token: " . ($bot_token ? 'EXISTS' : 'EMPTY') . "\n";
?>
