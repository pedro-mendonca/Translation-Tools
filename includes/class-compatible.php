<?php
/**
 * Class file for the Translation Tools compatible plugins.
 *
 * @package Translation Tools
 *
 * @since 1.2.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TTools_Compatible' ) ) {

	/**
	 * Class TTools_Compatible.
	 */
	class TTools_Compatible {


		/**
		 * Detect active compatible plugins.
		 *
		 * @since 1.2.0
		 *
		 * @return array  Array of compatible plugins.
		 */
		public static function compatible_plugins() {

			// List of compatible plugins.
			$compatible_plugins = array(
				'preferred-languages/preferred-languages.php' => array(
					'name'             => 'Preferred Languages',
					'required_version' => '1.6.0',
				),
			);

			return $compatible_plugins;

		}


		/**
		 * Check plugin compatibility.
		 *
		 * @since 1.2.0
		 *
		 * @param string $plugin_file  Plugin file (e.g.: 'translation-tools/translation-tools.php' ).
		 *
		 * @return true|false  Return true if plugin is active and compatible, false otherwise.
		 */
		public static function is_compatible( $plugin_file = null ) {

			// If no $plugin_file passed as argument, return.
			if ( null === $plugin_file ) {
				// No plugin is set.
				return false;
			}

			// Get compatible plugins.
			$compatible_plugins = self::compatible_plugins();

			// Check if get_plugins() function exists. This is required on the front end of the
			// site, since it is in a file that is normally only loaded in the admin.
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			// Get installed plugins.
			$installed_plugins = get_plugins();

			// Check if plugin is active.
			if ( ! is_plugin_active( $plugin_file ) ) {
				// Plugin is inactive.
				return false;
			}

			// Check if plugin is installed version is older than the compatible version.
			if ( $installed_plugins[ $plugin_file ]['Version'] < $compatible_plugins[ $plugin_file ]['required_version'] ) {
				return false;
			}

			return true;
		}


		/**
		 * Array of installed, active and compatible plugins.
		 *
		 * @since 1.2.0
		 *
		 * @return array  Array of compatible plugins.
		 */
		public static function get_compatible_plugins() {

			// Get compatible plugins.
			$compatible_plugins = self::compatible_plugins();

			$plugins = array();

			foreach ( $compatible_plugins as $compatible_plugin ) {

				if ( self::is_compatible( key( $compatible_plugins ) ) ) {
					$plugins[] = key( $compatible_plugins );
				}
			}

			return $plugins;

		}

	}

}
