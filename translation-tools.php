<?php
/**
 * Translation Tools
 *
 * @package      Translation Tools
 * @link         https://github.com/pedro-mendonca/Translation-Tools
 * @author       Pedro Mendonça
 * @copyright    2020 Pedro Mendonça
 * @license      GPLv2
 *
 * @wordpress-plugin
 * Plugin Name:       Translation Tools
 * Plugin URI:        https://wordpress.org/plugins/translation-tools/
 * GitHub Plugin URI: https://github.com/pedro-mendonca/Translation-Tools
 * Description:       Translation tools for your WordPress install.
 * Version:           1.2.1
 * Author:            Pedro Mendonça
 * Author URI:        https://profiles.wordpress.org/pedromendonca/
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       translation-tools
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Set Translation Tools plugin version.
define( 'TTOOLS_VERSION', '1.2.1' );

// Set Translation Tools required PHP version. Needed for PHP compatibility check for WordPress < 5.1.
define( 'TTOOLS_REQUIRED_PHP', '5.6' );

// Set Translation Tools settings database version.
// define( 'TTOOLS_SETTINGS_VERSION', '1.1' ); // phpcs:ignore.

// Set the WordPress option to store Translation Tools settings.
// define( 'TTOOLS_WP_OPTION', 'ttools_settings' ); // phpcs:ignore.

// Set Translation Tools settings page slug.
// define( 'TTOOLS_SETTINGS_PAGE', 'translation-tools' ); // phpcs:ignore.

// Set Translation Tools transients prefix.
define( 'TTOOLS_TRANSIENTS_PREFIX', 'translation_tools_' );

// Set Translation Tools transients 1 week expiration for Locales data.
define( 'TTOOLS_TRANSIENTS_LOCALES_EXPIRATION', WEEK_IN_SECONDS );

// Set Translation Tools plugin URL.
define( 'TTOOLS_DIR_URL', plugin_dir_url( __FILE__ ) );

// Set Translation Tools plugin filesystem path.
define( 'TTOOLS_DIR_PATH', plugin_dir_path( __FILE__ ) );

// Set Translation Tools file path.
define( 'TTOOLS_FILE', plugin_basename( __FILE__ ) );


/**
 * Require wordpress.org Locales list since translate.wp.org Languages API (https://translate.wordpress.org/api/languages/) was disabled on meta changeset #10056 (https://meta.trac.wordpress.org/changeset/10056).
 * Copy of https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php
 *
 * Updated on 2020-06-28.
 */
require_once 'lib/wp.org/locales.php';


/**
 * Register classes autoloader function.
 *
 * @since 1.0.0
 */
spl_autoload_register( 'ttools_class_autoload' );


/**
 * Class autoloader.
 *
 * @since 1.0.0
 *
 * @param string $class_name   Class name.
 *
 * @return bool  True if class found, false if not found.
 */
function ttools_class_autoload( $class_name ) {

	// Set class file path and name.
	$ttools_class_path = TTOOLS_DIR_PATH . 'includes/';
	$ttools_class_file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';
	$ttools_class      = $ttools_class_path . $ttools_class_file;

	if ( ! file_exists( $ttools_class ) ) {
		return false;
	}

	return require_once $ttools_class;
}


// Include Composer autoload.
require_once TTOOLS_DIR_PATH . 'vendor/autoload.php';

// Initialize the plugin.
new TTools_Main();
