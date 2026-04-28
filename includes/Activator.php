<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 */

namespace WPTelegram\Messaging\includes;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 * @author     s4hk
 */
class Activator {

	/**
	 * Run on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Set default options if they don't exist.
		$plugin_options = get_option( 'wptelegram_messaging_settings', [] );

		if ( empty( $plugin_options ) ) {
			$default_options = [
				'enable_welcome'   => 1,
				'welcome_message'  => '',
			];

			update_option( 'wptelegram_messaging_settings', $default_options );
		}

		/**
		 * Fires when WP Telegram Messaging is activated.
		 */
		do_action( 'wptelegram_messaging_activated' );
	}
}
