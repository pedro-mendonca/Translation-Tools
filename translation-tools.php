<?php
/**
 * Translation Tools
 *
 * @package           Translation_Tools
 * @link              https://github.com/pedro-mendonca/Translation-Tools
 * @author            Pedro Mendonça
 * @copyright         2020 Pedro Mendonça
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Translation Tools
 * Plugin URI:        https://wordpress.org/plugins/translation-tools/
 * GitHub Plugin URI: https://github.com/pedro-mendonca/Translation-Tools
 * Description:       Translation tools for your WordPress install.
 * Version:           1.7.2
 * Requires at least: 4.9
 * Tested up to:      6.6
 * Requires PHP:      7.4
 * Author:            Pedro Mendonça
 * Author URI:        https://profiles.wordpress.org/pedromendonca/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       translation-tools
 * Domain Path:       /languages
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Check if get_plugin_data() function exists.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Get plugin headers data.
$translation_tools_data = get_plugin_data( __FILE__, false, false );


// Set Translation Tools plugin version.
if ( ! defined( 'TRANSLATION_TOOLS_VERSION' ) ) {
	define( 'TRANSLATION_TOOLS_VERSION', $translation_tools_data['Version'] );
}

// Set Translation Tools required PHP version. Needed for PHP compatibility check for WordPress < 5.1.
if ( ! defined( 'TRANSLATION_TOOLS_REQUIRED_PHP' ) ) {
	define( 'TRANSLATION_TOOLS_REQUIRED_PHP', $translation_tools_data['RequiresPHP'] );
}

// Set Translation Tools settings database version.
// define( 'TRANSLATION_TOOLS_SETTINGS_VERSION', '1.1' ); // phpcs:ignore.

// Set the WordPress option to store Translation Tools settings.
// define( 'TRANSLATION_TOOLS_WP_OPTION', 'translation_tools_settings' ); // phpcs:ignore.

// Set Translation Tools settings page slug.
// define( 'TRANSLATION_TOOLS_SETTINGS_PAGE', 'translation-tools' ); // phpcs:ignore.

// Set Translation Tools transients prefix.
define( 'TRANSLATION_TOOLS_TRANSIENTS_PREFIX', 'translation_tools_' );

// Set Translation Tools plugin URL.
define( 'TRANSLATION_TOOLS_DIR_URL', plugin_dir_url( __FILE__ ) );

// Set Translation Tools plugin filesystem path.
define( 'TRANSLATION_TOOLS_DIR_PATH', plugin_dir_path( __FILE__ ) );

// Set Translation Tools file path.
define( 'TRANSLATION_TOOLS_FILE', plugin_basename( __FILE__ ) );

// Include Composer autoload.
require_once TRANSLATION_TOOLS_DIR_PATH . 'vendor/autoload.php';

// Initialize the plugin.
// TODO: Load via 'plugins_loaded'.
new Translation_Tools();
