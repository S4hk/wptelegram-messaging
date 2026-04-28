<?php
/**
 * Autoloader.
 *
 * @package WPTelegram\Messaging
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Autoloader function.
 *
 * @param string $class Class name to autoload.
 */
function wptelegram_messaging_autoloader( $class ) {
	if ( 0 !== strpos( $class, 'WPTelegram\\Messaging\\' ) ) {
		return;
	}

	$class_path = str_replace(
		[ 'WPTelegram\\Messaging\\', '\\' ],
		[ '', DIRECTORY_SEPARATOR ],
		$class
	);

	$file = WPTELEGRAM_MESSAGING_DIR . DIRECTORY_SEPARATOR . $class_path . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

spl_autoload_register( 'wptelegram_messaging_autoloader' );
