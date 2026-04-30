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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
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

		// Bot Token field
		add_settings_field(
			'wptelegram_messaging_bot_token',
			esc_html__( 'Bot Token', 'wptelegram-messaging' ),
			[ $this, 'render_bot_token_field' ],
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

		// Target Roles field
		add_settings_field(
			'wptelegram_messaging_target_roles',
			esc_html__( 'Target Roles', 'wptelegram-messaging' ),
			[ $this, 'render_target_roles_field' ],
			'wptelegram_messaging_settings_group',
			'wptelegram_messaging_main'
		);

		// Add hooks for user list
		add_filter( 'manage_users_columns', [ $this, 'add_user_columns' ] );
		add_filter( 'manage_users_custom_column', [ $this, 'render_user_column' ], 10, 3 );
		add_filter( 'user_row_actions', [ $this, 'add_user_row_actions' ], 10, 2 );

		// AJAX handler for manual sending
		add_action( 'wp_ajax_wptelegram_messaging_send_manual', [ $this, 'handle_manual_send' ] );

		// AJAX handler for bulk sending
		add_action( 'wp_ajax_wptelegram_messaging_send_bulk', [ $this, 'handle_bulk_send' ] );

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

		$settings = get_option( 'wptelegram_messaging_settings', [] );

		// Sanitize enable field
		$settings['enable_welcome'] = isset( $input['enable_welcome'] ) ? 1 : 0;

		// Sanitize bot token
		if ( isset( $input['bot_token'] ) ) {
			$settings['bot_token'] = sanitize_text_field( $input['bot_token'] );
		}

		// Sanitize message field
		$settings['welcome_message'] = isset( $input['welcome_message'] ) ? wp_kses_post( $input['welcome_message'] ) : '';

		// Sanitize target roles
		$settings['target_roles'] = [];
		if ( isset( $input['target_roles'] ) && is_array( $input['target_roles'] ) ) {
			$settings['target_roles'] = array_map( 'sanitize_text_field', $input['target_roles'] );
		}

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
	 * Render bot token field.
	 *
	 * @since    1.1.0
	 */
	public function render_bot_token_field() {
		$settings = get_option( 'wptelegram_messaging_settings', [] );
		$token    = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';

		// Check if an external token is available.
		$external_token = $this->get_external_bot_token();

		if ( ! empty( $external_token ) ) {
			echo '<p style="color: green;"><strong>&#10003; ' . esc_html__( 'Bot Token automatically inherited from WP Telegram.', 'wptelegram-messaging' ) . '</strong></p>';
			echo '<p class="description">' . esc_html__( 'You do not need to enter a token here unless you want to use a different bot.', 'wptelegram-messaging' ) . '</p>';
		}
		?>
		<input 
			type="password" 
			name="wptelegram_messaging_settings[bot_token]" 
			value="<?php echo esc_attr( $token ); ?>" 
			class="regular-text"
			placeholder="<?php esc_attr_e( 'Enter Bot Token', 'wptelegram-messaging' ); ?>"
		/>
		<?php if ( empty( $external_token ) && empty( $token ) ) : ?>
			<p class="description" style="color: #dc3232;">
				<?php esc_html_e( 'No bot token found. Please enter your Telegram Bot Token here, or install and configure WP Telegram / WP Telegram Login.', 'wptelegram-messaging' ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Try to get bot token from WP Telegram Login or WP Telegram core.
	 *
	 * Uses safe function_exists checks to avoid fatal errors if those
	 * plugins are not installed.
	 *
	 * @since    1.1.0
	 * @return string Bot token or empty string.
	 */
	private function get_external_bot_token() {
		// Try WP Telegram Login.
		if ( function_exists( 'WPTG_Login' ) ) {
			$token = WPTG_Login()->options()->get( 'bot_token' );
			if ( ! empty( $token ) ) {
				return $token;
			}
		}

		// Try WP Telegram core.
		if ( function_exists( 'WPTG' ) ) {
			$token = WPTG()->options()->get( 'bot_token' );
			if ( ! empty( $token ) ) {
				return $token;
			}
		}

		return '';
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
	 * Render target roles field.
	 *
	 * @since    1.1.0
	 */
	public function render_target_roles_field() {
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		$settings     = get_option( 'wptelegram_messaging_settings', [] );
		$target_roles = isset( $settings['target_roles'] ) ? (array) $settings['target_roles'] : [];

		echo '<fieldset>';
		foreach ( $wp_roles->roles as $role_key => $role_details ) {
			$checked = in_array( $role_key, $target_roles, true ) ? 'checked="checked"' : '';
			echo '<label style="display: block; margin-bottom: 5px;">';
			echo '<input type="checkbox" name="wptelegram_messaging_settings[target_roles][]" value="' . esc_attr( $role_key ) . '" ' . $checked . ' /> ';
			echo esc_html( translate_user_role( $role_details['name'] ) );
			echo '</label>';
		}
		echo '</fieldset>';
		echo '<p class="description">' . esc_html__( 'Select which user roles should receive the welcome message. If none are selected, ALL roles will receive it.', 'wptelegram-messaging' ) . '</p>';
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

		$settings  = get_option( 'wptelegram_messaging_settings', [] );
		$own_token = isset( $settings['bot_token'] ) ? $settings['bot_token'] : '';

		// Check if WP Telegram Login is active
		$has_wptg_login = function_exists( 'WPTG_Login' );

		// Check if any token is available
		$external_token = $this->get_external_bot_token();
		$has_any_token  = ! empty( $own_token ) || ! empty( $external_token );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Telegram Messaging', 'wptelegram-messaging' ); ?></h1>
			<p style="font-size: 14px; color: #666;">
				<?php esc_html_e( 'Configure automatic welcome messages for users who register via WP Telegram Login.', 'wptelegram-messaging' ); ?>
			</p>

			<?php if ( ! $has_wptg_login ) : ?>
				<div class="notice notice-warning">
					<p>
						<?php
						echo wp_kses_post(
							__( '<strong>WP Telegram Login plugin is not active.</strong> For automatic welcome messages on registration, please install and activate WP Telegram Login. You can still configure settings and send manual messages if you provide a Bot Token below.', 'wptelegram-messaging' )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( ! $has_any_token ) : ?>
				<div class="notice notice-error">
					<p>
						<?php
						echo wp_kses_post(
							__( '<strong>No Bot Token configured.</strong> Please enter a Telegram Bot Token below, or configure one in WP Telegram / WP Telegram Login settings.', 'wptelegram-messaging' )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

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

			<hr style="margin: 30px 0;">

			<h2><?php esc_html_e( 'Bulk Messaging', 'wptelegram-messaging' ); ?></h2>
			<p><?php esc_html_e( 'Send a custom message to all users with selected roles who have connected their Telegram account.', 'wptelegram-messaging' ); ?></p>
			
			<div id="wptelegram-messaging-bulk-form" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<table class="form-table">
					<tr>
						<th scope="row"><label for="bulk_message"><?php esc_html_e( 'Message', 'wptelegram-messaging' ); ?></label></th>
						<td>
							<textarea id="bulk_message" rows="5" cols="50" class="large-text code" placeholder="<?php esc_attr_e( 'Enter your custom bulk message here...', 'wptelegram-messaging' ); ?>"></textarea>
							<p class="description"><?php esc_html_e( 'Supports the same placeholders as the welcome message.', 'wptelegram-messaging' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Target Roles', 'wptelegram-messaging' ); ?></th>
						<td>
							<fieldset id="bulk_roles">
								<?php
								global $wp_roles;
								foreach ( $wp_roles->roles as $role_key => $role_details ) {
									echo '<label style="display: block; margin-bottom: 5px;">';
									echo '<input type="checkbox" name="bulk_roles[]" value="' . esc_attr( $role_key ) . '" /> ';
									echo esc_html( translate_user_role( $role_details['name'] ) );
									echo '</label>';
								}
								?>
							</fieldset>
							<p class="description"><?php esc_html_e( 'Select which roles should receive this message. If none selected, message will NOT be sent.', 'wptelegram-messaging' ); ?></p>
						</td>
					</tr>
				</table>
				
				<?php wp_nonce_field( 'wptelegram_messaging_bulk_send', 'bulk_send_nonce' ); ?>
				<p class="submit">
					<button type="button" id="wptelegram-messaging-send-bulk" class="button button-primary">
						<?php esc_html_e( 'Send Bulk Message', 'wptelegram-messaging' ); ?>
					</button>
					<span class="spinner"></span>
				</p>
				
				<div id="bulk-send-results" style="display: none; margin-top: 20px; padding: 10px; background: #f0f0f0; border-left: 4px solid #00a0d2;">
					<p><strong><?php esc_html_e( 'Bulk Send Status:', 'wptelegram-messaging' ); ?></strong></p>
					<ul style="margin: 0;">
						<li><?php esc_html_e( 'Sent:', 'wptelegram-messaging' ); ?> <span class="sent-count">0</span></li>
						<li><?php esc_html_e( 'Failed:', 'wptelegram-messaging' ); ?> <span class="failed-count">0</span></li>
						<li><?php esc_html_e( 'Skipped (no Telegram):', 'wptelegram-messaging' ); ?> <span class="skipped-count">0</span></li>
					</ul>
				</div>
			</div>
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
		$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wptelegram_messaging_manual_send_' . $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid security token.', 'wptelegram-messaging' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'wptelegram-messaging' ) ] );
		}

		// Clear previous error/failure meta before retrying.
		delete_user_meta( $user_id, '_wptelegram_messaging_failed' );
		delete_user_meta( $user_id, '_wptelegram_messaging_error' );

		// Use the Main class to send the message (force = true).
		$main = \WPTelegram\Messaging\includes\Main::instance();
		$main->send_welcome_on_login( $user_id, true );

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
	 * Handle AJAX request for bulk message sending.
	 *
	 * @since    1.1.0
	 */
	public function handle_bulk_send() {
		$message = isset( $_POST['message'] ) ? wp_kses_post( wp_unslash( $_POST['message'] ) ) : '';
		$roles   = isset( $_POST['roles'] ) ? array_map( 'sanitize_text_field', (array) $_POST['roles'] ) : [];
		$nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'wptelegram_messaging_bulk_send' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid security token.', 'wptelegram-messaging' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'wptelegram-messaging' ) ] );
		}

		if ( empty( $message ) ) {
			wp_send_json_error( [ 'message' => __( 'Message cannot be empty.', 'wptelegram-messaging' ) ] );
		}

		if ( empty( $roles ) ) {
			wp_send_json_error( [ 'message' => __( 'Please select at least one target role.', 'wptelegram-messaging' ) ] );
		}

		$main    = \WPTelegram\Messaging\includes\Main::instance();
		$results = $main->send_bulk_message( $message, $roles );

		wp_send_json_success( $results );
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
