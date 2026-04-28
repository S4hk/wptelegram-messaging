# WP Telegram Messaging - Complete File Structure

## Plugin Root Directory
```
wptelegram-messaging/
```

## Core Plugin Files

### wptelegram-messaging.php (★ MAIN PLUGIN FILE)
- **Purpose:** Main plugin file with WordPress plugin header
- **Size:** ~2 KB
- **Contains:**
  - Plugin metadata (name, version, author, etc.)
  - Plugin constants definition
  - Activation/deactivation hooks
  - Plugin initialization code
- **Key Functions:**
  - `activate_wptelegram_messaging()` - Plugin activation
  - `deactivate_wptelegram_messaging()` - Plugin deactivation
  - `run_wptelegram_messaging()` - Plugin startup

### autoload.php
- **Purpose:** PSR-4 autoloader for classes
- **Size:** ~500 bytes
- **Contains:**
  - Autoloader function `wptelegram_messaging_autoloader()`
  - Namespace to file path conversion
  - SPL autoload registration
- **Registers:** `spl_autoload_register()`

### vendor/autoload.php
- **Purpose:** Vendor autoload stub (in case vendor folder missing)
- **Size:** ~200 bytes
- **Contains:**
  - Mock autoloader to prevent fatal errors
  - Utility functions

## Admin Directory (Admin Settings)

### admin/Admin.php (★ SETTINGS PAGE)
- **Purpose:** Handles admin settings page and configuration
- **Size:** ~8 KB
- **Contains:**
  - Admin class with settings registration
  - Settings field rendering
  - Input sanitization
  - Settings page HTML
- **Key Methods:**
  - `add_admin_menu()` - Adds settings submenu
  - `register_settings()` - Registers WordPress settings
  - `render_settings_page()` - Renders settings UI
  - `render_enable_field()` - Renders toggle checkbox
  - `render_message_field()` - Renders message textarea
  - `sanitize_settings()` - Sanitizes user input

### admin/index.php
- **Purpose:** Security file (prevents directory listing)
- **Size:** ~50 bytes
- **Contains:** Silence comment

## Includes Directory (Core Classes)

### includes/Main.php (★ CORE PLUGIN CLASS)
- **Purpose:** Main plugin functionality and logic
- **Size:** ~10 KB
- **Contains:**
  - Main class (singleton pattern)
  - Plugin initialization
  - Hooks registration
  - Welcome message sending logic
  - Placeholder replacement
  - Telegram API integration
- **Key Methods:**
  - `instance()` - Singleton instance getter
  - `send_welcome_on_login()` - Main welcome function
  - `get_default_message()` - Default message template
  - `replace_placeholders()` - Replace {placeholders} with values
  - `send_telegram_message()` - Send message via API

### includes/I18n.php (Translations)
- **Purpose:** Handles plugin internationalization/translations
- **Size:** ~500 bytes
- **Contains:**
  - I18n class
  - Text domain loader
- **Key Methods:**
  - `load_plugin_textdomain()` - Load translation files

### includes/Activator.php (Plugin Activation)
- **Purpose:** Handles plugin activation events
- **Size:** ~600 bytes
- **Contains:**
  - Activator class
  - Default options initialization
- **Key Methods:**
  - `activate()` - Set default options on activation
- **Fires:** `wptelegram_messaging_activated` hook

### includes/Deactivator.php (Plugin Deactivation)
- **Purpose:** Handles plugin deactivation events
- **Size:** ~500 bytes
- **Contains:**
  - Deactivator class
  - Cleanup logic
- **Key Methods:**
  - `deactivate()` - Cleanup on deactivation
- **Fires:** `wptelegram_messaging_deactivated` hook

### includes/index.php
- **Purpose:** Security file (prevents directory listing)
- **Size:** ~50 bytes
- **Contains:** Silence comment

## Languages Directory (Translations)

### languages/wptelegram-messaging.pot
- **Purpose:** Translation template (Portable Objects Template)
- **Size:** ~4 KB
- **Contains:**
  - All translatable strings
  - Context and location information
  - Message IDs and template strings
- **For:** Creating translations in other languages
- **Format:** WPML/Gettext standard

## Documentation Files

### README.md (★ MAIN DOCUMENTATION)
- **Purpose:** Complete plugin documentation
- **Size:** ~12 KB
- **Contains:**
  - Plugin description and features
  - Installation instructions
  - Configuration guide
  - Usage examples
  - Message placeholders reference
  - Developer documentation
  - Action hooks and filters
  - Troubleshooting guide
  - FAQ
  - Support information
- **Audience:** End users and developers

## Directory Structure Summary

```
wptelegram-messaging/
│
├─ 📄 wptelegram-messaging.php      Main plugin file (★)
├─ 📄 autoload.php                  Class autoloader
├─ 📄 README.md                      Full documentation (★)
│
├─ 📁 admin/
│  ├─ 📄 Admin.php                   Settings page (★)
│  └─ 📄 index.php                   Security file
│
├─ 📁 includes/
│  ├─ 📄 Main.php                    Core plugin class (★)
│  ├─ 📄 I18n.php                    Translations
│  ├─ 📄 Activator.php               Activation hook
│  ├─ 📄 Deactivator.php             Deactivation hook
│  └─ 📄 index.php                   Security file
│
├─ 📁 languages/
│  └─ 📄 wptelegram-messaging.pot     Translation template
│
└─ 📁 vendor/
   └─ 📄 autoload.php                Vendor stub
```

## File Purpose Summary

| File | Purpose | Type | Size |
|------|---------|------|------|
| wptelegram-messaging.php | Plugin entry point | Core | 2 KB |
| autoload.php | Class autoloading | Core | 500 B |
| admin/Admin.php | Settings page | Feature | 8 KB |
| includes/Main.php | Main logic | Core | 10 KB |
| includes/I18n.php | Translations | Support | 500 B |
| includes/Activator.php | Activation logic | Lifecycle | 600 B |
| includes/Deactivator.php | Deactivation logic | Lifecycle | 500 B |
| languages/*.pot | Translations | Support | 4 KB |
| README.md | Documentation | Docs | 12 KB |
| vendor/autoload.php | Vendor stub | Support | 200 B |

## ★ Most Important Files

The files marked with ★ are the most important:

1. **wptelegram-messaging.php** - The main plugin file (WordPress reads this)
2. **admin/Admin.php** - The settings page where users configure the feature
3. **includes/Main.php** - The core logic that sends welcome messages
4. **README.md** - Documentation for users and developers

## File Sizes

```
Total plugin size: ~55 KB (including docs)
Core plugin size: ~25 KB (without docs)
```

## PHP Classes & Namespaces

```
\WPTelegram\Messaging\admin\Admin
\WPTelegram\Messaging\includes\Main (Singleton)
\WPTelegram\Messaging\includes\I18n
\WPTelegram\Messaging\includes\Activator
\WPTelegram\Messaging\includes\Deactivator
```

## Constants Defined

```php
WPTELEGRAM_MESSAGING_VER              // Plugin version (1.0.0)
WPTELEGRAM_MESSAGING_MAIN_FILE        // Main plugin file path
WPTELEGRAM_MESSAGING_BASENAME         // Plugin basename
WPTELEGRAM_MESSAGING_DIR              // Plugin directory path
WPTELEGRAM_MESSAGING_URL              // Plugin URL
WPTELEGRAM_USER_ID_META_KEY           // Meta key for Telegram user ID
```

## Options Stored

In WordPress `wp_options` table:
- `wptelegram_messaging_settings` - Array containing:
  - `enable_welcome` - Boolean (1 or 0)
  - `welcome_message` - String (custom message)

## User Meta Keys Created

When message is sent:
- `_wptelegram_messaging_sent` - Flag (1)
- `_wptelegram_messaging_sent_time` - Timestamp
- `_wptelegram_messaging_failed` - Error message (if failed)
- `_wptelegram_messaging_error` - Exception text (if error)

## Action Hooks Provided

1. `wptelegram_messaging_loaded` - When plugin is initialized
2. `wptelegram_messaging_activated` - When plugin is activated
3. `wptelegram_messaging_deactivated` - When plugin is deactivated
4. `wptelegram_messaging_message_sent` - When message sent successfully
5. `wptelegram_messaging_message_failed` - When send fails
6. `wptelegram_messaging_message_exception` - When exception occurs

## Filter Hooks Provided

1. `wptelegram_messaging_placeholders` - Filter placeholder replacements

## Total Lines of Code

```
Core Code:    ~1000 lines
Settings UI:  ~200 lines
Docs:         ~1500 lines
Total:        ~2700 lines
```

## Development Notes

- **Coding Standard:** WordPress Coding Standards
- **Namespace:** WPTelegram\Messaging\
- **Architecture:** Singleton pattern for Main class
- **Database:** Uses WordPress options and usermeta tables
- **Security:** Input sanitization and output escaping
- **Compatibility:** PHP 8.0+, WordPress 6.6+

---

**All files are included in the wptelegram-messaging plugin folder.** 🎉
