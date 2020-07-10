<?php
/**
 * Class file for the Translation Tools translate.wordpress.org API.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TTools_Translations_API' ) ) {


	/**
	 * Class TTools_Translations_API.
	 */
	class TTools_Translations_API {


		/**
		 * Set the translate.wordpress.org WordPress core subprojects structure with 'slug', 'name' and language file 'domain'.
		 *
		 * @since 1.0.0
		 *
		 * @return array $subprojects  Returns array of the supported WordPress translation known subprojects.
		 */
		public function get_wordpress_subprojects() {

			$subprojects = array(
				array(
					'slug'   => '',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Development', 'Subproject name', 'translation-tools' ),
					'domain' => '',
				),
				array(
					'slug'   => 'admin/',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Administration', 'Subproject name', 'translation-tools' ),
					'domain' => 'admin',
				),
				array(
					'slug'   => 'admin/network/',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Network Admin', 'Subproject name', 'translation-tools' ),
					'domain' => 'admin-network',
				),
				array(
					'slug'   => 'cc/',
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'name'   => _x( 'Continents & Cities', 'Subproject name', 'translation-tools' ),
					'domain' => 'continents-cities',
				),
			);

			return $subprojects;
		}


		/**
		 * Get WordPress core translation version info.
		 *
		 * @since 1.0.0
		 *
		 * @return array $wp_version  Array of WordPress installed version.
		 */
		public function get_wordpress_version() {

			// Get install WordPress version.
			$current_version = get_bloginfo( 'version' );

			// Get available core updates.
			$updates = get_core_updates();
			if ( ! is_array( $updates ) ) {
				return array();
			}

			$wp_version = array();

			// Check if WordPress install is current is the current major relase.
			if ( substr( $current_version, 0, 3 ) === substr( $updates[0]->version, 0, 3 ) ) {
				$wp_version['slug']   = 'dev';
				$wp_version['name']   = substr( $current_version, 0, 3 ) . '.x';
				$wp_version['number'] = $current_version;
				$wp_version['latest'] = true;
			} else {
				$wp_version['slug']   = substr( $current_version, 0, 3 ) . '.x';
				$wp_version['name']   = substr( $current_version, 0, 3 ) . '.x';
				$wp_version['number'] = $current_version;
				$wp_version['latest'] = false;
			}

			return $wp_version;

		}


		/**
		 * Get Translate API URL.
		 *
		 * Example:
		 * $api_url = $this->translations_api->translations_api_url( 'plugins' );
		 *
		 * @since 1.0.0
		 *
		 * @param string $project   Set the project API URL you want to get.
		 *
		 * @return string $api_url  Returns API URL.
		 */
		public function translations_api_url( $project ) {

			$translations_api_url = array(
				'wp'        => 'https://translate.wordpress.org/api/projects/wp/',         // Translate API WordPress core URL.
				'languages' => 'https://translate.wordpress.org/api/languages',            // Translate API languages URL.
				'plugins'   => 'https://translate.wordpress.org/api/projects/wp-plugins/', // Translate API plugins URL.
				'themes'    => 'https://translate.wordpress.org/api/projects/wp-themes/',  // Translate API themes URL.
			);

			$api_url = $translations_api_url[ $project ];

			return $api_url;

		}


		/**
		 * Get Translate URL.
		 *
		 * Example:
		 * $url = $this->translations_api->translations_url( 'plugins' );
		 *
		 * @since 1.0.0
		 *
		 * @param string $project  Set the project URL you want to get.
		 *
		 * @return string $url     Returns URL.
		 */
		public function translations_url( $project ) {

			$translations_url = array(
				'wp'      => 'https://translate.wordpress.org/projects/wp/',         // Translate WordPress core URL.
				'plugins' => 'https://translate.wordpress.org/projects/wp-plugins/', // Translate plugins URL.
				'themes'  => 'https://translate.wordpress.org/projects/wp-themes/',  // Translate themes URL.
			);

			$url = $translations_url[ $project ];

			return $url;

		}


		/**
		 * Set the path to get the translation file.
		 *
		 * @since 1.0.0
		 * @since 1.0.1  Increase translate.wp.org languages API timeout to 20 seconds.
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param array  $project   Project array.
		 * @param object $locale    Locale object.
		 *
		 * @return string|null     File path to get source.
		 */
		public function translation_path( $project, $locale ) {

			// Get WordPress core version info.
			$wp_version = $this->get_wordpress_version();

			/**
			 * TODO:
			 *
			 * Let users choose witch filter to use.
			 * $filters = '?filters[status]=current_or_waiting_or_fuzzy';
			 * $filters = '?filters[status]=current';
			 *
			 * Import from JED format to improve speed.
			 * $format  = '&format=jed';
			 */
			$filters = '?filters[status]=current';
			$format  = '&format=po';
			$args    = $filters . $format;

			$translation_path = esc_url_raw( $this->translations_url( 'wp' ) . $wp_version['slug'] . '/' . $project['slug'] . $locale->locale_slug . '/export-translations' . $args );

			return $translation_path;
		}


		/**
		 * Get locale data from wordpress.org and Translation Tools.
		 *
		 * Example:
		 * $locale = $this->translations_api->locale( 'pt_PT' );
		 * $locale_english_name = $locale->english_name.
		 *
		 * @since 1.0.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param string $wp_locale  Locale ( e.g. 'pt_PT' ).
		 *
		 * @return object            Return selected Locale object data from Translation Tools and wordpress.org (e.g. 'english_name', 'native_name', 'lang_code_iso_639_1', 'country_code', 'wp_locale', 'slug', etc. ).
		 */
		public function locale( $wp_locale ) {

			// Get wordpress.org Locales.
			$locales = TTools_Locales::locales();

			$current_locale = null;

			foreach ( $locales as $key => $locale ) {

				if ( $locale->wp_locale === $wp_locale ) {

					$current_locale = $locale;
					break;

				}
			}

			return $current_locale;
		}


		/**
		 * Get Locales with no Language Pack support.
		 *
		 * @since 1.1.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @return array  Array of Locale objects with no language packs.
		 */
		public function get_locales_with_no_lang_packs() {

			// Get wordpress.org Locales.
			$locales = TTools_Locales::locales();

			$locales_with_no_lang_packs = array();

			foreach ( $locales as $locale ) {
				if ( ! isset( $locale->translations ) ) {
					$locales_with_no_lang_packs[ $locale->wp_locale ] = $locale;
				}
			}

			// Remove 'en_US' Locale.
			unset( $locales_with_no_lang_packs['en_US'] );

			return $locales_with_no_lang_packs;
		}

	}

}
