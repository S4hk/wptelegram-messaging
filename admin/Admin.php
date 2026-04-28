<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\admin
 */

namespace WPTelegram\Messaging\admin;

use WPSocio\WPUtils\Options;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for hooking into WordPress.
 *
 * @package    WPTelegram\Messaging
 * @subpackage WPTelegram\Messaging\admin
 * @author     s4hk
 */
class Admin {

	/**
	 * The plugin name used as unique identifier.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The name of the plugin.
	 */
	protected $plugin_name;

	/**
	 * The plugin version.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The plugin options handler.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Options    $options    The plugin options.
	 */
	protected $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name       The name of this plugin.
	 * @param string $version    The version of this plugin.
	 * @param Options $options   The plugin options handler.
	 */
	public function __construct( $plugin_name, $version, Options $options ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->options     = $options;
	}

	/**
	 * Add admin menu for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'wptelegram', // Parent menu slug from WP Telegram Core
			esc_html__( 'Telegram Messaging', 'wptelegram-messaging' ), // Page title
			esc_html__( 'Telegram Messaging', 'wptelegram-messaging' ), // Menu title
			'manage_options', // Capability
			'wptelegram-messaging', // Menu slug
			[ $this, 'render_settings_page' ] // Callback
		);
	}

	/**
	 * Register settings and fields.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting(
			'wptelegram_messaging_settings_group', // Option group
			'wptelegram_messaging_settings', // Option name
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'show_in_rest'      => false,
			]
		);

		// Enable/Disable section
		add_settings_section(
			'wptelegram_messaging_main',
			esc_html__( 'Welcome Message Settings', 'wptelegram-messaging' ),
			[ $this, 'render_main_section' ],
			'wptelegram_messaging_settings_group'
		);

		// Enable toggle field
		add_settings_field(
			'wptelegram_messaging_enable',
			esc_html__( 'Enable Welcome Messages', 'wptelegram-messaging' ),
			[ $this, 'render_enable_field' ],
			'wptelegram_messaging_settings_group',
			'wptelegram_messaging_main'
		);

		// Message text field
		add_settings_field(
			'wptelegram_messaging_message',
			esc_html__( 'Welcome Message', 'wptelegram-messaging' ),
			[ $this, 'render_message_field' ],
			'wptelegram_messaging_settings_group',
			'wptelegram_messaging_main'
		);

		// Add hooks for user list
		add_filter( 'manage_users_columns', [ $this, 'add_user_columns' ] );
		add_filter( 'manage_users_custom_column', [ $this, 'render_user_column' ], 10, 3 );
		add_filter( 'user_row_actions', [ $this, 'add_user_row_actions' ], 10, 2 );

		// AJAX handler for manual sending
		add_action( 'wp_ajax_wptelegram_messaging_send_manual', [ $this, 'handle_manual_send' ] );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Sanitize settings.
	 *
	 * @since    1.0.0
	 * @param array $input The input to sanitize.
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			$input = [];
		}

		$settings = [];

		// Sanitize enable field
		$settings['enable_welcome'] = isset( $input['enable_welcome'] ) ? 1 : 0;

		// Sanitize message field
		$settings['welcome_message'] = isset( $input['welcome_message'] ) ? wp_kses_post( $input['welcome_message'] ) : '';

		return $settings;
	}

	/**
	 * Render main section description.
	 *
	 * @since    1.0.0
	 */
	public function render_main_section() {
		echo '<p>' . esc_html__( 'Configure automatic welcome messages for users who register via Telegram Login.', 'wptelegram-messaging' ) . '</p>';
		echo '<p style="color: #666; font-size: 12px;">';
		esc_html_e( 'Supported placeholders: {site_name}, {user_name}, {user_email}, {user_login}, {first_name}, {last_name}, {site_url}', 'wptelegram-messaging' );
		echo '</p>';
	}

	/**
	 * Render enable toggle field.
	 *
	 * @since    1.0.0
	 */
	public function render_enable_field() {
		$settings      = get_option( 'wptelegram_messaging_settings', [] );
		$is_enabled    = isset( $settings['enable_welcome'] ) ? $settings['enable_welcome'] : 1;
		$checked_attr  = checked( $is_enabled, 1, false );

		?>
		<label>
			<input type="checkbox" name="wptelegram_messaging_settings[enable_welcome]" value="1" <?php echo $checked_attr; ?> />
			<span><?php esc_html_e( 'Send welcome messages to newly registered users', 'wptelegram-messaging' ); ?></span>
		</label>
		<p class="description">
			<?php esc_html_e( 'When disabled, no welcome messages will be sent. Existing messages already sent will not be affected.', 'wptelegram-messaging' ); ?>
		</p>
		<?php
	}

	/**
	 * Render message text field.
	 *
	 * @since    1.0.0
	 */
	public function render_message_field() {
		$settings = get_option( 'wptelegram_messaging_settings', [] );
		$message  = isset( $settings['welcome_message'] ) ? $settings['welcome_message'] : '';

		?>
		<textarea 
			name="wptelegram_messaging_settings[welcome_message]" 
			rows="8" 
			cols="50" 
			class="large-text code"
			placeholder="<?php esc_attr_e( 'Enter your custom welcome message here. Leave empty to use default message.', 'wptelegram-messaging' ); ?>"
		><?php echo esc_textarea( $message ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Leave empty to use the default welcome message. Supports HTML tags and the placeholders listed above.', 'wptelegram-messaging' ); ?>
		</p>
		<details style="margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 3px;">
			<summary style="cursor: pointer; font-weight: bold;">
				<?php esc_html_e( 'Example Messages', 'wptelegram-messaging' ); ?>
			</summary>
			<div style="margin-top: 10px;">
				<p><strong><?php esc_html_e( 'Example 1 - Simple', 'wptelegram-messaging' ); ?>:</strong></p>
				<code>Welcome {user_name}! Visit us at {site_url}</code>
				
				<p><strong><?php esc_html_e( 'Example 2 - With Emoji', 'wptelegram-messaging' ); ?>:</strong></p>
				<code>🎉 Welcome to {site_name}, {first_name}! 👋</code>
				
				<p><strong><?php esc_html_e( 'Example 3 - HTML Formatted', 'wptelegram-messaging' ); ?>:</strong></p>
				<code>&lt;b&gt;Welcome {first_name}!&lt;/b&gt;&lt;br&gt;&lt;br&gt;Thanks for joining {site_name}.</code>
			</div>
		</details>
		<?php
	}

	/**
	 * Render the settings page.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_page() {
		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wptelegram-messaging' ) );
		}

		// Check if WP Telegram Login is active
		if ( ! function_exists( 'WPTG_Login' ) ) {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WP Telegram Messaging', 'wptelegram-messaging' ); ?></h1>
				<div class="notice notice-error">
					<p>
						<?php 
						echo wp_kses_post(
							__( '<strong>WP Telegram Login plugin is required.</strong> Please install and activate WP Telegram Login plugin to use this feature.', 'wptelegram-messaging' )
						);
						?>
					</p>
				</div>
			</div>
			<?php
			return;
		}

		// Check if bot token is configured
		$bot_token = WPTG_Login()->options()->get( 'bot_token' );
		if ( empty( $bot_token ) ) {
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WP Telegram Messaging', 'wptelegram-messaging' ); ?></h1>
				<div class="notice notice-warning">
					<p>
						<?php 
						echo wp_kses_post(
							__( '<strong>Bot Token not configured.</strong> Please configure a valid Telegram bot token in WP Telegram or WP Telegram Login settings.', 'wptelegram-messaging' )
						);
						?>
					</p>
				</div>
			</div>
			<?php
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Telegram Messaging', 'wptelegram-messaging' ); ?></h1>
			<p style="font-size: 14px; color: #666;">
				<?php esc_html_e( 'Configure automatic welcome messages for users who register via WP Telegram Login.', 'wptelegram-messaging' ); ?>
			</p>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'wptelegram_messaging_settings_group' );
					do_settings_sections( 'wptelegram_messaging_settings_group' );
					submit_button();
				?>
			</form>

			<hr style="margin: 30px 0;">

			<h2><?php esc_html_e( 'Available Placeholders', 'wptelegram-messaging' ); ?></h2>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Placeholder', 'wptelegram-messaging' ); ?></th>
						<th><?php esc_html_e( 'Description', 'wptelegram-messaging' ); ?></th>
						<th><?php esc_html_e( 'Example', 'wptelegram-messaging' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>{site_name}</code></td>
						<td><?php esc_html_e( 'Your website name', 'wptelegram-messaging' ); ?></td>
						<td>My Awesome Blog</td>
					</tr>
					<tr>
						<td><code>{site_description}</code></td>
						<td><?php esc_html_e( 'Your website tagline/description', 'wptelegram-messaging' ); ?></td>
						<td>Just another WordPress site</td>
					</tr>
					<tr>
						<td><code>{user_name}</code></td>
						<td><?php esc_html_e( 'User display name', 'wptelegram-messaging' ); ?></td>
						<td>John Doe</td>
					</tr>
					<tr>
						<td><code>{first_name}</code></td>
						<td><?php esc_html_e( 'User first name', 'wptelegram-messaging' ); ?></td>
						<td>John</td>
					</tr>
					<tr>
						<td><code>{last_name}</code></td>
						<td><?php esc_html_e( 'User last name', 'wptelegram-messaging' ); ?></td>
						<td>Doe</td>
					</tr>
					<tr>
						<td><code>{user_email}</code></td>
						<td><?php esc_html_e( 'User email address', 'wptelegram-messaging' ); ?></td>
						<td>john@example.com</td>
					</tr>
					<tr>
						<td><code>{user_login}</code></td>
						<td><?php esc_html_e( 'User login username', 'wptelegram-messaging' ); ?></td>
						<td>johndoe</td>
					</tr>
					<tr>
						<td><code>{site_url}</code></td>
						<td><?php esc_html_e( 'Your website URL', 'wptelegram-messaging' ); ?></td>
						<td>https://example.com</td>
					</tr>
					<tr>
						<td><code>{admin_email}</code></td>
						<td><?php esc_html_e( 'Admin email address', 'wptelegram-messaging' ); ?></td>
						<td>admin@example.com</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
	/**
	 * Add custom column to users table.
	 *
	 * @since    1.0.0
	 * @param array $columns The existing columns.
	 * @return array
	 */
	public function add_user_columns( $columns ) {
		$columns['wptelegram_welcome'] = esc_html__( 'Welcome Sent', 'wptelegram-messaging' );
		return $columns;
	}

	/**
	 * Render custom column content.
	 *
	 * @since    1.0.0
	 * @param string $output      The column output.
	 * @param string $column_name The column name.
	 * @param int    $user_id     The user ID.
	 * @return string
	 */
	public function render_user_column( $output, $column_name, $user_id ) {
		if ( 'wptelegram_welcome' !== $column_name ) {
			return $output;
		}

		$sent = get_user_meta( $user_id, '_wptelegram_messaging_sent', true );

		if ( $sent ) {
			$time = get_user_meta( $user_id, '_wptelegram_messaging_sent_time', true );
			$output = '<span class="dashicons dashicons-yes-alt" style="color: #46b450;" title="' . esc_attr( $time ) . '"></span>';
			$output .= ' <small>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $time ) ) ) . '</small>';
		} else {
			$failed = get_user_meta( $user_id, '_wptelegram_messaging_failed', true );
			if ( $failed ) {
				$output = '<span class="dashicons dashicons-warning" style="color: #dc3232;" title="' . esc_attr( $failed ) . '"></span>';
				$output .= ' <small>' . esc_html__( 'Failed', 'wptelegram-messaging' ) . '</small>';
			} else {
				$output = '<span class="dashicons dashicons-minus" style="color: #ccc;"></span>';
			}
		}

		return $output;
	}

	/**
	 * Add row actions to users table.
	 *
	 * @since    1.0.0
	 * @param array    $actions The existing actions.
	 * @param \WP_User $user    The user object.
	 * @return array
	 */
	public function add_user_row_actions( $actions, $user ) {
		$telegram_id = get_user_meta( $user->ID, WPTELEGRAM_USER_ID_META_KEY, true );

		if ( ! empty( $telegram_id ) && current_user_can( 'manage_options' ) ) {
			$nonce = wp_create_nonce( 'wptelegram_messaging_manual_send_' . $user->ID );
			$url   = admin_url( 'admin-ajax.php?action=wptelegram_messaging_send_manual&user_id=' . $user->ID . '&_wpnonce=' . $nonce );

			$actions['send_telegram_welcome'] = sprintf(
				'<a href="%s" class="wptelegram-messaging-send-manual" data-user-id="%d" data-nonce="%s">%s</a>',
				esc_url( $url ),
				$user->ID,
				$nonce,
				esc_html__( 'Send Welcome', 'wptelegram-messaging' )
			);
		}

		return $actions;
	}

	/**
	 * Handle AJAX request for manual message sending.
	 *
	 * @since    1.0.0
	 */
	public function handle_manual_send() {
		$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
		$nonce   = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'wptelegram_messaging_manual_send_' . $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid security token.', 'wptelegram-messaging' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'wptelegram-messaging' ) ] );
		}

		// Use the Main class to send the message
		$main = \WPTelegram\Messaging\includes\Main::instance();
		
		// Force send even if already sent
		delete_user_meta( $user_id, '_wptelegram_messaging_sent' );
		
		$main->send_welcome_on_login( $user_id );

		$sent = get_user_meta( $user_id, '_wptelegram_messaging_sent', true );

		if ( $sent ) {
			wp_send_json_success( [ 'message' => __( 'Welcome message sent successfully!', 'wptelegram-messaging' ) ] );
		} else {
			$failed = get_user_meta( $user_id, '_wptelegram_messaging_failed', true );
			$error  = get_user_meta( $user_id, '_wptelegram_messaging_error', true );
			$msg    = $failed ? $failed : ( $error ? $error : __( 'Failed to send message. Check if bot is started.', 'wptelegram-messaging' ) );
			wp_send_json_error( [ 'message' => $msg ] );
		}
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since    1.0.0
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'users.php' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name . '-admin',
			WPTELEGRAM_MESSAGING_URL . '/assets/js/admin.js',
			[ 'jquery' ],
			$this->version,
			true
		);

		// Add some styles for the 'updating' state
		wp_add_inline_style( 'common', '.wptelegram-messaging-send-manual.updating { opacity: 0.5; pointer-events: none; }' );
	}
}
