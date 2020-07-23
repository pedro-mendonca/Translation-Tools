<?php
/**
 * Class file for the Translation Tools compatibility with Preferred Languages plugin.
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

if ( ! class_exists( 'TTools_Compatible_Preferred_Languages' ) ) {

	/**
	 * Class TTools_Compatible_Preferred_Languages.
	 */
	class TTools_Compatible_Preferred_Languages extends TTools_Compatible {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Preferred Language plugin selected languages.
			add_filter( 'ttools_core_update_locales', array( $this, 'preferred_languages_selected_languages' ) );

		}


		/**
		 * Get the languages selected for site and current user in Preferred Languages plugin.
		 *
		 * @since 1.2.0
		 *
		 * @param array $wp_locales  Array of Locales for core translation update.
		 *
		 * @return array             Array of selected languages as 'wp_locale'.
		 */
		public function preferred_languages_selected_languages( $wp_locales ) {

			// Check plugin compatibility.
			$plugin_file = 'preferred-languages/preferred-languages.php';
			if ( ! self::is_compatible( $plugin_file ) ) {
				// If incompatible, return unfiltered $wp_locales array.
				return $wp_locales;
			}

			// Define arrays.
			$pl_site_languages = array();
			$pl_user_languages = array();

			// Get Site languages selected on Preferred Languages plugin.
			if ( function_exists( 'preferred_languages_get_site_list' ) ) { // Double check for funcion.
				$pl_site_languages = preferred_languages_get_site_list();
			}

			// Get current user languages selected on Preferred Languages plugin.
			if ( function_exists( 'preferred_languages_get_user_list' ) ) { // Double check for funcion.
				$pl_user_languages = preferred_languages_get_user_list( get_current_user_id() );
			}

			// Merge Preferred Languages Locales.
			$pl_all_languages = array_merge( $pl_site_languages, $pl_user_languages );

			// Remove duplicates.
			$pl_all_languages = array_unique( $pl_all_languages );

			// Sort ascending.
			sort( $pl_all_languages );

			return $pl_all_languages;
		}

	}

}
