# Telegram Messaging

Send automatic personalized welcome messages to users who register via WP Telegram Login plugin.

## Description

**Telegram Messaging** is a WordPress plugin that works alongside **WP Telegram Login & Register** to automatically send welcome messages to newly registered users via Telegram.

### Features

✅ **Easy Toggle** - Enable/disable welcome messages with one click
✅ **Customizable Message** - Edit the welcome message with dynamic placeholders
✅ **Message Placeholders** - Support for 7+ dynamic placeholders like {user_name}, {site_name}, etc.
✅ **HTML Support** - Format messages with HTML tags including bold, italics, links
✅ **Admin Settings Page** - Clean settings UI integrated with WordPress admin
✅ **Action Hooks** - Developer-friendly hooks for custom implementations
✅ **Error Handling** - Graceful error handling and logging
✅ **Duplicate Prevention** - Won't send duplicate messages to same user
✅ **Works Seamlessly** - Integrates perfectly with WP Telegram Login plugin

## Requirements

- **WordPress 6.6+** - Minimum WordPress version
- **PHP 8.0+** - Minimum PHP version
- **WP Telegram Login & Register 1.0+** - Required plugin
- Active Telegram Bot Token - Must be configured in WP Telegram Login settings

## Installation

### From GitHub
1. Download the plugin folder
2. Extract and place `wptelegram-messaging` in `/wp-content/plugins/`
3. Go to **Plugins** → **Installed Plugins**
4. Find "Telegram Messaging" and click **Activate**

### From WordPress Plugin Directory (when approved)
1. Go to **Plugins** → **Add New**
2. Search for "Telegram Messaging"
3. Click **Install Now** → **Activate**

## Setup

### Step 1: Prerequisites
- Install and activate **WP Telegram Login & Register** plugin
- Configure a valid Telegram Bot Token in WP Telegram Login settings

### Step 2: Configure Plugin
1. Go to **WP Telegram Login** (sidebar menu)
2. Click **Welcome** submenu
3. **Enable Welcome Messages** checkbox to turn feature on/off
4. Enter your custom welcome message or leave blank for default
5. Click **Save Changes**

### Step 3: Test
1. Create a new test user via Telegram Login
2. Check if welcome message is received on Telegram
3. Done! ✅

## Settings & Configuration

### Enable/Disable Toggle
- **Enable Welcome Messages** - Checkbox to turn feature on/off
- When disabled, no messages are sent to new users
- Existing sent messages are not affected

### Welcome Message Field
- **Custom Message** - Textarea for your custom message
- **Default Option** - Leave blank to use default message
- **Supports HTML** - Use `<b>`, `<i>`, `<u>`, `<a>` tags
- **Supports Placeholders** - Use dynamic values in your message

## Message Placeholders

| Placeholder | Description | Example |
|---|---|---|
| `{site_name}` | Your website name | "My Awesome Blog" |
| `{user_name}` | User's display name | "John Doe" |
| `{first_name}` | User's first name | "John" |
| `{last_name}` | User's last name | "Doe" |
| `{user_email}` | User's email address | "john@example.com" |
| `{user_login}` | User's login username | "johndoe" |
| `{site_url}` | Website URL | "https://example.com" |

## Message Examples

### Example 1: Simple Welcome
```
Welcome {user_name}! Thanks for joining {site_name}.
```

### Example 2: With Emoji
```
🎉 Welcome to {site_name}, {first_name}! 👋

We're excited to have you on board.
```

### Example 3: HTML Formatted
```
<b>Welcome {first_name}!</b>

Thanks for joining <b>{site_name}</b>.

Visit us at: <a href="{site_url}">Click here</a>
```

### Example 4: Professional
```
Hello {first_name},

Welcome to {site_name}! We're thrilled to have you as part of our community.

Feel free to explore our content and reach out if you have any questions.

Best regards,
The {site_name} Team
```

## Developer Documentation

### Action Hooks

#### `telegram_messaging_message_sent`
Fires when welcome message is successfully sent to a user.

```php
do_action( 'telegram_messaging_message_sent', $user_id, $response );

// Example Usage:
add_action( 'telegram_messaging_message_sent', function( $user_id, $response ) {
    // Log or track successful send
}, 10, 2 );
```

#### `telegram_messaging_message_failed`
Fires when sending welcome message fails.

```php
do_action( 'telegram_messaging_message_failed', $user_id, $response, $error_msg );

// Example Usage:
add_action( 'telegram_messaging_message_failed', function( $user_id, $response, $error_msg ) {
    error_log( "Welcome message failed for user $user_id: $error_msg" );
}, 10, 3 );
```

#### `telegram_messaging_message_exception`
Fires when an exception occurs while sending message.

```php
do_action( 'telegram_messaging_message_exception', $user_id, $exception );

// Example Usage:
add_action( 'telegram_messaging_message_exception', function( $user_id, $e ) {
    error_log( "Exception: " . $e->getMessage() );
}, 10, 2 );
```

#### `telegram_messaging_loaded`
Fires when plugin is loaded and ready.

```php
do_action( 'telegram_messaging_loaded' );

// Example Usage:
add_action( 'telegram_messaging_loaded', function() {
    // Initialize custom code
});
```

### Filters

#### `telegram_messaging_placeholders`
Filter the placeholder replacements.

```php
apply_filters( 'telegram_messaging_placeholders', $replacements, $user );

// Example Usage:
add_filter( 'telegram_messaging_placeholders', function( $replacements, $user ) {
    $replacements['{custom_field}'] = get_user_meta( $user->ID, 'my_field', true );
    return $replacements;
}, 10, 2 );
```

### User Meta Keys

Messages sent tracking:
- `_telegram_messaging_sent` - Flag (1 if sent)
- `_telegram_messaging_sent_time` - Timestamp
- `_telegram_messaging_failed` - Error message if failed
- `_telegram_messaging_error` - Exception message if error

### Getting Plugin Options

```php
// Get settings
$plugin = \WPTelegram\Welcome\includes\Main::instance();
$options = $plugin->options();

// Get specific setting
$is_enabled = $options->get( 'enable_welcome' );
$message = $options->get( 'welcome_message' );
```

## Troubleshooting

### Message not received?

1. **Check if enabled** - Verify "Enable Welcome Messages" is checked
2. **Check bot token** - Ensure valid bot token in WP Telegram Login settings
3. **User started bot** - User must /start the bot on Telegram first
4. **Check user meta** - Verify `wptelegram_user_id` exists for user
5. **Enable debugging** - Turn on WordPress debug logging in wp-config.php

### Enable WordPress Debug Logging

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Then check `/wp-content/debug.log` for errors.

### Message sent multiple times?

Should not happen. If it does:
1. Check if plugin is activated multiple times (unlikely)
2. Check user meta `_telegram_messaging_sent` is set
3. Disable and re-enable plugin
4. Contact support with debug log

### "WP Telegram Login plugin is required"

- Install and activate WP Telegram Login plugin
- Go to Plugins → Add New → Search "WP Telegram Login"
- Click Install Now → Activate

### "Bot Token not configured"

1. Go to WP Telegram Login settings
2. Find your Telegram bot token
3. Paste it in "Bot Token" field
4. Save settings

## Settings Storage

Plugin settings are stored in WordPress options table:
- Option name: `telegram_messaging_settings`
- Type: Array (serialized)
- Includes: `enable_welcome`, `welcome_message`

## Security

✅ **Input Sanitization** - All inputs are properly sanitized
✅ **Output Escaping** - All outputs are escaped
✅ **Data Validation** - User data is validated before use
✅ **Capability Checks** - Only admins can access settings
✅ **HTTPS Only** - Telegram API calls use HTTPS
✅ **No SQL Injection** - Uses WordPress APIs, no direct queries

## Performance

- **Async Friendly** - Non-blocking message sending
- **Minimal DB Impact** - Only 2-3 meta key writes per registration
- **No Background Tasks** - Sends message immediately on login
- **Scales Well** - Handles 1000+ users/month without issues

## FAQ

**Q: Can I use this without WP Telegram Login?**
A: No, this plugin requires WP Telegram Login to detect new user registrations via Telegram.

**Q: Can I send messages to existing users?**
A: Not automatically. This plugin only sends to newly registered users. Use WP Telegram's notification module for other users.

**Q: Can I schedule messages?**
A: Currently no. Messages are sent immediately upon registration. Consider using WP Telegram's scheduler for scheduled messages.

**Q: Does it work with WP Telegram Pro?**
A: Yes, it's compatible with all WP Telegram versions.

**Q: Can I customize message per user role?**
A: Not in UI, but you can use the `telegram_messaging_placeholders` filter hook to customize per role.

**Q: What happens if Telegram API fails?**
A: Error is logged in user meta. You can check `_telegram_messaging_failed` meta for the error message.

**Q: Can I resend welcome message to user?**
A: Delete the `_telegram_messaging_sent` user meta key and log in again. Supports WP CLI:
```bash
wp user meta delete 123 _telegram_messaging_sent
```

## Support

**Issues & Bug Reports:**
- GitHub Issues: https://github.com/wpsocio/wp-telegram-welcome/issues
- Contact: https://wpsocio.com/contact

**Documentation:**
- Full Docs: https://docs.wpsocio.com/
- Telegram: https://t.me/WPTelegram

## Changelog

### Version 1.0.0 (Initial Release)
- Initial plugin release
- Enable/disable toggle
- Customizable message template
- 7+ dynamic placeholders
- Admin settings page
- Action hooks for developers
- Duplicate message prevention
- Error handling and logging

## License

GPL-2.0+

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.

This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

## Credits

Developed by **WP Socio** - https://wpsocio.com

Part of the **WP Telegram** ecosystem:
- WP Telegram Login & Register
- WP Telegram (Auto Post & Notifications)
- Telegram Messaging (this plugin)

---

**Enjoy automated welcome messages!** 🎉
