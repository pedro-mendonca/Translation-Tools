<?php
/**
 * Class file for the Translation Tools compatibility with Preferred Languages plugin.
 *
 * @package Translation_Tools
 *
 * @since 1.2.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Compatible_Preferred_Languages' ) ) {

	/**
	 * Class Compatible_Preferred_Languages.
	 */
	class Compatible_Preferred_Languages extends Compatible {


		/**
		 * Plugin file.
		 *
		 * @var string
		 */
		public $plugin_file = 'preferred-languages/preferred-languages.php';


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Preferred Languages plugin selected languages.
			add_filter( 'translation_tools_core_update_locales', array( $this, 'preferred_languages_selected_languages' ) );

			// Add Preferred Languages plugin Site Languages to Translation Tools Site Health data.
			add_filter( 'translation_tools_site_health_site_language', array( $this, 'preferred_languages_site_languages' ) );

			// Add Preferred Languages plugin User Languages to Translation Tools Site Health data.
			add_filter( 'translation_tools_site_health_user_language', array( $this, 'preferred_languages_user_languages' ) );

			// Format Preferred Languages list of languages.
			add_filter( 'preferred_languages_all_languages', array( $this, 'preferred_languages_all_languages' ) );
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
			if ( ! self::is_compatible( $this->plugin_file ) ) {
				// If incompatible, return unfiltered $wp_locales array.
				return $wp_locales;
			}

			// Define arrays.
			$pl_site_languages = array();
			$pl_user_languages = array();

			// Get Site languages selected on Preferred Languages plugin.
			if ( function_exists( 'preferred_languages_get_site_list' ) ) { // Double check for function.
				$pl_site_languages = preferred_languages_get_site_list();
				$pl_site_languages = $pl_site_languages ? $pl_site_languages : array();
			}

			// Get current user languages selected on Preferred Languages plugin.
			if ( function_exists( 'preferred_languages_get_user_list' ) ) { // Double check for function.
				$pl_user_languages = preferred_languages_get_user_list( get_current_user_id() );
				$pl_user_languages = $pl_user_languages ? $pl_user_languages : array();
			}

			// Merge Preferred Languages Locales.
			$pl_all_languages = array_merge( $pl_site_languages, $pl_user_languages );

			// Remove duplicates.
			$pl_all_languages = array_unique( $pl_all_languages );

			// Sort ascending.
			sort( $pl_all_languages );

			return $pl_all_languages;
		}


		/**
		 * Get the languages selected for site user in Preferred Languages plugin.
		 *
		 * @since 1.3.0
		 *
		 * @param string $site_language   Site locale.
		 *
		 * @return array|string   Array of Site Preferred Languages. Defaults to Site Locale if empty.
		 */
		public function preferred_languages_site_languages( $site_language ) {

			// Check plugin compatibility.
			if ( ! self::is_compatible( $this->plugin_file ) ) {
				// If incompatible, return unfiltered $wp_locales array.
				return $site_language;
			}

			// Define array.
			$pl_site_languages = array();

			// Get Site languages selected on Preferred Languages plugin.
			if ( function_exists( 'preferred_languages_get_site_list' ) ) { // Double check for function.
				$pl_site_languages = preferred_languages_get_site_list();
			}

			if ( $pl_site_languages ) {

				// Define array.
				$site_languages = array();

				foreach ( $pl_site_languages as $pl_site_language ) {
					$site_languages[ $pl_site_language ] = Site_Health::locale_lang_pack_status( $pl_site_language );
				}

				$site_language = array(
					'label' => __( 'Site Preferred Languages', 'translation-tools' ),
					'value' => $site_languages,
				);

			}

			return $site_language;
		}


		/**
		 * Get the languages selected for current user in Preferred Languages plugin.
		 *
		 * @since 1.3.0
		 *
		 * @param string $user_language   User locale.
		 *
		 * @return array|string   Array of User Preferred Languages. Defaults to User Locale if empty.
		 */
		public function preferred_languages_user_languages( $user_language ) {

			// Check plugin compatibility.
			if ( ! self::is_compatible( $this->plugin_file ) ) {
				// If incompatible, return unfiltered $wp_locales array.
				return $user_language;
			}

			// Define array.
			$pl_user_languages = array();

			// Get current user languages selected on Preferred Languages plugin.
			if ( function_exists( 'preferred_languages_get_user_list' ) ) { // Double check for function.
				$pl_user_languages = preferred_languages_get_user_list( get_current_user_id() );
			}

			if ( $pl_user_languages ) {

				// Define array.
				$user_languages = array();

				foreach ( $pl_user_languages as $pl_user_language ) {
					$user_languages[ $pl_user_language ] = Site_Health::locale_lang_pack_status( $pl_user_language );
				}

				$user_language = array(
					'label' => __( 'User Preferred Languages', 'translation-tools' ),
					'value' => $user_languages,
				);

			}

			return $user_language;
		}


		/**
		 * Format the Preferred Languages list of Languages.
		 *
		 * @since 1.6.0
		 *
		 * @param array $all_languages   List of languages.
		 *
		 * @return array   Array of Preferred Languages list of languages with formatted names.
		 */
		public function preferred_languages_all_languages( $all_languages ) {

			if ( empty( $all_languages ) ) {
				return $all_languages;
			}

			$formatted_languages = Options_General::all_languages();

			foreach ( $all_languages as $key => $language ) {

				$all_languages[ $key ]['lang']       = $formatted_languages[ $language['locale'] ]['lang'];
				$all_languages[ $key ]['nativeName'] = $formatted_languages[ $language['locale'] ]['name'];
				$all_languages[ $key ]['langPacks']  = $formatted_languages[ $language['locale'] ]['lang_packs']; // TODO: For future use.

			}

			return $all_languages;
		}
	}

}
