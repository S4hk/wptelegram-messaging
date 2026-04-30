<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 */

namespace WPTelegram\Messaging\includes;

use WPTelegram\Messaging\admin\Admin;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\includes
 * @author     s4hk
 */
class Main {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var   Main $instance Class instance.
	 */
	protected static $instance = null;

	/**
	 * Title of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $title    Title of the plugin
	 */
	protected $title;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;



	/**
	 * Main class Instance.
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->version      = WPTELEGRAM_MESSAGING_VER;
		$this->plugin_name  = 'wptelegram-messaging';
		$this->title        = 'WP Telegram Messaging';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Loader. Orchestrates the hooks of the plugin.
	 * - I18n. Defines internationalization functionality.
	 * - Admin. Defines all hooks for the admin area.
	 * - Frontend. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used throughout this plugin
	 * to register the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {
		// Dependencies loaded.
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {
		$i18n = new I18n();
		add_action( 'plugins_loaded', [ $i18n, 'load_plugin_textdomain' ] );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {
		$admin_class = new Admin( $this->plugin_name, $this->version );
		add_action( 'admin_menu', [ $admin_class, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $admin_class, 'register_settings' ] );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_public_hooks() {
		// Hook into WP Telegram Login after user login.
		add_action( 'wptelegram_login_after_user_login', [ $this, 'send_welcome_on_login' ] );

		// Hook into standard WordPress registration.
		add_action( 'user_register', [ $this, 'send_welcome_on_login' ] );

		// Hook into WP Telegram Login after data is saved (most reliable for new registrations).
		add_action( 'wptelegram_login_after_save_user_data', [ $this, 'send_welcome_on_login' ] );
	}

	/**
	 * Send welcome message when user logs in.
	 *
	 * @since    1.0.0
	 * @param int $user_id The WordPress user ID.
	 */
	public function send_welcome_on_login( $user_id, $force = false ) {
		// Get plugin settings directly.
		$settings = get_option( 'wptelegram_messaging_settings', [] );
		$is_enabled = isset( $settings['enable_welcome'] ) ? $settings['enable_welcome'] : 1;

		// Check if feature is enabled.
		if ( ! $force && ! $is_enabled ) {
			return;
		}

		// Get the user object.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return;
		}

		// Role Check
		$target_roles = isset( $settings['target_roles'] ) ? (array) $settings['target_roles'] : [];
		if ( ! empty( $target_roles ) ) {
			$user_roles = (array) $user->roles;
			$intersect  = array_intersect( $user_roles, $target_roles );
			if ( empty( $intersect ) ) {
				// User does not have any of the target roles, abort.
				return;
			}
		}

		// Get Telegram user ID from meta.
		$telegram_user_id = get_user_meta( $user_id, WPTELEGRAM_USER_ID_META_KEY, true );
		if ( empty( $telegram_user_id ) ) {
			update_user_meta( $user_id, '_wptelegram_messaging_failed', __( 'User does not have a connected Telegram account.', 'wptelegram-messaging' ) );
			return;
		}

		// Get bot token.
		$bot_token = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';
		
		// Try WP Telegram Login first if setting is empty.
		if ( empty( $bot_token ) && function_exists( 'WPTG_Login' ) ) {
			$bot_token = WPTG_Login()->options()->get( 'bot_token' );
		}

		// Try WP Telegram core if still empty.
		if ( empty( $bot_token ) && function_exists( 'WPTG' ) ) {
			$bot_token = WPTG()->options()->get( 'bot_token' );
		}

		// Direct fallbacks if functions fail.
		if ( empty( $bot_token ) ) {
			$login_opts = get_option( 'wptelegram_login', [] );
			$bot_token  = isset( $login_opts['bot_token'] ) ? $login_opts['bot_token'] : '';
		}

		if ( empty( $bot_token ) ) {
			$core_opts = get_option( 'wptelegram', [] );
			$bot_token = isset( $core_opts['bot_token'] ) ? $core_opts['bot_token'] : '';
		}

		if ( empty( $bot_token ) ) {
			update_user_meta( $user_id, '_wptelegram_messaging_failed', __( 'Bot token not found in settings.', 'wptelegram-messaging' ) );
			return;
		}

		// Check if welcome already sent.
		if ( ! $force && get_user_meta( $user_id, '_wptelegram_messaging_sent', true ) ) {
			return;
		}

		// Get custom message or use default.
		$message = isset( $settings['welcome_message'] ) ? $settings['welcome_message'] : '';
		if ( empty( $message ) ) {
			$message = $this->get_default_message( $user );
		}

		// Replace placeholders.
		$message = $this->replace_placeholders( $message, $user );

		/**
		 * Filter the welcome message text before sending.
		 *
		 * @param string   $message The message text.
		 * @param \WP_User $user    The user object.
		 */
		$message = apply_filters( 'wptelegram_messaging_welcome_message', $message, $user );

		// Send message.
		$this->send_telegram_message( $telegram_user_id, $message, $bot_token, $user_id );
	}

	/**
	 * Get default welcome message.
	 *
	 * @since    1.0.0
	 * @param \WP_User $user The user object.
	 * @return string
	 */
	private function get_default_message( $user ) {
		$site_name = get_bloginfo( 'name' );
		$user_name = ! empty( $user->first_name ) ? $user->first_name : $user->display_name;

		return sprintf(
			__( "🎉 Welcome to %s, %s!\n\nThank you for registering. We're excited to have you on board. Feel free to explore our website and get in touch if you have any questions.", 'wptelegram-messaging' ),
			esc_html( $site_name ),
			esc_html( $user_name )
		);
	}

	/**
	 * Replace placeholders in message.
	 *
	 * Supported placeholders:
	 * {site_name} - Website name
	 * {user_name} - User's display name
	 * {user_email} - User's email
	 * {user_login} - User's login/username
	 * {first_name} - User's first name
	 * {last_name} - User's last name
	 * {site_url} - Website URL
	 *
	 * @since    1.0.0
	 * @param string   $message The message with placeholders.
	 * @param \WP_User $user    The user object.
	 * @return string
	 */
	private function replace_placeholders( $message, $user ) {
		$replacements = [
			'{site_name}'        => get_bloginfo( 'name' ),
			'{site_description}' => get_bloginfo( 'description' ),
			'{user_name}'        => $user->display_name,
			'{user_email}'       => $user->user_email,
			'{user_login}'       => $user->user_login,
			'{first_name}'       => ! empty( $user->first_name ) ? $user->first_name : $user->display_name,
			'{last_name}'        => ! empty( $user->last_name ) ? $user->last_name : '',
			'{site_url}'         => home_url(),
			'{admin_email}'      => get_option( 'admin_email' ),
		];

		/**
		 * Filter the placeholder replacements.
		 *
		 * @param array    $replacements The placeholder replacements.
		 * @param \WP_User $user         The user object.
		 */
		$replacements = apply_filters( 'wptelegram_messaging_placeholders', $replacements, $user );

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $message );
	}

	/**
	 * Send message to Telegram user.
	 *
	 * @since    1.0.0
	 * @param int    $chat_id      Telegram user ID.
	 * @param string $message      Message text.
	 * @param string $bot_token    Bot token.
	 * @param int    $user_id      WordPress user ID.
	 * @return bool|void
	 */
	private function send_telegram_message( $chat_id, $message, $bot_token, $user_id ) {
		if ( empty( $chat_id ) || empty( $message ) || empty( $bot_token ) ) {
			return false;
		}

		// Build the API URL.
		$api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

		$args = [
			'chat_id'    => (string) $chat_id, // Cast to string to handle large 64-bit IDs
			'text'       => wp_kses_post( $message ),
			'parse_mode' => 'HTML',
		];

		// Check if we can use BotAPI.
		if ( class_exists( '\WPTelegram\BotAPI\API' ) ) {
			try {
				$telegram_api = new \WPTelegram\BotAPI\API( $bot_token );
				$response = $telegram_api->sendMessage( $args );

				if ( $telegram_api->is_success( $response ) ) {
					update_user_meta( $user_id, '_wptelegram_messaging_sent', '1' );
					update_user_meta( $user_id, '_wptelegram_messaging_sent_time', current_time( 'mysql' ) );
					do_action( 'wptelegram_messaging_message_sent', $user_id, $response );
					return true;
				} else {
					$error_msg = ! empty( $response['description'] ) ? $response['description'] : 'Unknown error';
					update_user_meta( $user_id, '_wptelegram_messaging_failed', $error_msg );
					do_action( 'wptelegram_messaging_message_failed', $user_id, $response, $error_msg );
					return false;
				}
			} catch ( \Exception $e ) {
				update_user_meta( $user_id, '_wptelegram_messaging_error', $e->getMessage() );
				do_action( 'wptelegram_messaging_message_exception', $user_id, $e );
				return false;
			}
		}

		// Fallback to native wp_remote_post
		$response = wp_remote_post(
			$api_url,
			[
				'body'    => $args,
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) ) {
			update_user_meta( $user_id, '_wptelegram_messaging_error', $response->get_error_message() );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['ok'] ) && $data['ok'] ) {
			update_user_meta( $user_id, '_wptelegram_messaging_sent', '1' );
			update_user_meta( $user_id, '_wptelegram_messaging_sent_time', current_time( 'mysql' ) );
			do_action( 'wptelegram_messaging_message_sent', $user_id, $data );
			return true;
		} else {
			$error_msg = ! empty( $data['description'] ) ? $data['description'] : 'Unknown error';
			update_user_meta( $user_id, '_wptelegram_messaging_failed', $error_msg );
			do_action( 'wptelegram_messaging_message_failed', $user_id, $data, $error_msg );
			return false;
		}
	}

	/**
	 * Get the plugin name.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get the plugin version.
	 *
	 * @since     1.0.0
	 * @return    string    The version of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}



	/**
	 * Run the plugin.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		do_action( 'wptelegram_messaging_loaded' );
	}
}
