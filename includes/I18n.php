<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 */

namespace WPTelegram\Messaging\includes;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 * @author     s4hk
 */
class I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wptelegram-messaging',
			false,
			dirname( WPTELEGRAM_MESSAGING_BASENAME ) . '/languages/'
		);
	}
}
