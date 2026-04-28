<?php
/**
 * The main plugin file.
 *
 * @link              https://github.com/S4hk/wptelegram-messaging
 * @since             1.0.0
 * @package           WPTelegram\Messaging
 *
 * @wordpress-plugin
 * Plugin Name:       WP Telegram Messaging
 * Plugin URI:        https://github.com/S4hk/wptelegram-messaging
 * Description:       Send personalized messages to users who register via WP Telegram Login. Includes customizable message template and enable/disable toggle.
 * Version:           1.0.0
 * Author:            s4hk
 * Author URI:        https://github.com/s4hk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wptelegram-messaging
 * Domain Path:       /languages
 * Requires at least: 6.6
 * Tested up to:      6.8
 * Requires PHP:      8.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WPTELEGRAM_MESSAGING_VER', '1.0.0' );

defined( 'WPTELEGRAM_MESSAGING_MAIN_FILE' ) || define( 'WPTELEGRAM_MESSAGING_MAIN_FILE', __FILE__ );

defined( 'WPTELEGRAM_MESSAGING_BASENAME' ) || define( 'WPTELEGRAM_MESSAGING_BASENAME', plugin_basename( WPTELEGRAM_MESSAGING_MAIN_FILE ) );

define( 'WPTELEGRAM_MESSAGING_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

define( 'WPTELEGRAM_MESSAGING_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

// Telegram user ID meta key (shared with WP Telegram Login).
if ( ! defined( 'WPTELEGRAM_USER_ID_META_KEY' ) ) {
	define( 'WPTELEGRAM_USER_ID_META_KEY', 'wptelegram_user_id' );
}

/**
 * Include autoloader.
 */
require WPTELEGRAM_MESSAGING_DIR . '/autoload.php';
require_once dirname( WPTELEGRAM_MESSAGING_MAIN_FILE ) . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
function activate_wptelegram_messaging() {
	\WPTelegram\Messaging\includes\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wptelegram_messaging() {
	\WPTelegram\Messaging\includes\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wptelegram_messaging' );
register_deactivation_hook( __FILE__, 'deactivate_wptelegram_messaging' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wptelegram_messaging() {
	$plugin = \WPTelegram\Messaging\includes\Main::instance();
	$plugin->run();
}

run_wptelegram_messaging();
