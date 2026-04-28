<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 */

namespace WPTelegram\Messaging\includes;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 * @author     s4hk
 */
class Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		/**
		 * Fires when WP Telegram Messaging is deactivated.
		 */
		do_action( 'wptelegram_messaging_deactivated' );
	}
}
