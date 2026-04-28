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
			'wptelegram-login', // Parent menu slug from WP Telegram Login
			esc_html__( 'Welcome Messages', 'wptelegram-messaging' ), // Page title
			esc_html__( 'Welcome', 'wptelegram-messaging' ), // Menu title
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
							sprintf(
								__( '<strong>Bot Token not configured.</strong> Please configure a valid Telegram bot token in <a href="%s">WP Telegram Login settings</a>.', 'wptelegram-messaging' ),
								esc_url( admin_url( 'edit.php?post_type=wptelegram_chat&page=wptelegram-login' ) )
							)
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
				</tbody>
			</table>
		</div>
		<?php
	}
}
