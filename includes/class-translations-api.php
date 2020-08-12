<?php
/**
 * Class file for the Translation Tools translate.wordpress.org API.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Translations_API' ) ) {


	/**
	 * Class Translations_API.
	 */
	class Translations_API {


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
		 * Get WordPress core translation sub-project data.
		 *
		 * @since 1.2.2
		 * @since 1.2.3  Use transient to store WordPress core translation project for 24h.
		 *
		 * @return object|null  Object of WordPress translation sub-project, null if API is unreachable.
		 */
		public static function get_core_translation_project() {

			// Set the transient name.
			$translation_project_transient = 'wordpress_translation_project';

			// Get WordPress core translation project transient data.
			$translation_project = get_transient( TRANSLATION_TOOLS_TRANSIENTS_PREFIX . $translation_project_transient );

			// Check if transient data exist, otherwise get new data and set transient.
			if ( false === $translation_project ) {

				// Get WordPress translation project API URL.
				$source = self::translate_url( 'wp', true );

				// Get the translation project data.
				$response = wp_remote_get( $source );

				// Default response.
				$translation_project = null;

				// Check if WordPress translation project is reachable.
				if ( ! is_array( $response ) || 'application/json' !== $response['headers']['content-type'] ) {
					return $translation_project;
				}

				// Decode JSON.
				$response = json_decode( $response['body'] );

				// Get the translation sub-projects.
				$projects = $response->sub_projects;

				// Get WordPress major version ( e.g.: '5.5' ).
				$wp_version = self::major_version( get_bloginfo( 'version' ) );

				foreach ( $projects as $project ) {

					$translation_version = self::major_version( $project->name );

					// Check for the WordPress installed major version translation project.
					if ( $wp_version === $translation_version ) {
						$translation_project = $project;
					}
				}

				// Set WordPress core translation project data transient for 24h.
				set_transient( TRANSLATION_TOOLS_TRANSIENTS_PREFIX . $translation_project_transient, $translation_project, DAY_IN_SECONDS );
			}

			return $translation_project;

		}


		/**
		 * Get major version number.
		 *
		 * @since 1.2.2
		 *
		 * @param string $version  The version number (e.g.: 5.5.x).
		 *
		 * @return string          Returns major version (e.g.: 5.5).
		 */
		public static function major_version( $version ) {

			$major_version = substr( $version, 0, 3 );

			return $major_version;
		}


		/**
		 * Get the translate site URL.
		 *
		 * Example for WordPress.org plugins URL (normal URL, not API URL):
		 * $url = Translations_API::translate_url( 'plugins', false );
		 *
		 * @since 1.2.3
		 *
		 * @param string $project  Set the project URL you want to get. Defaults to null.
		 * @param bool   $api      Set to 'true' to get the API URL. Defaults to false.
		 *
		 * @return string          Returns URL.
		 */
		public static function translate_url( $project = null, $api = false ) {

			// Set WordPress.org translate site URL.
			$translate_url = 'https://translate.wordpress.org/';

			/**
			 * Filters the translate site URL.
			 *
			 * @since 1.2.3
			 */
			$translate_url = apply_filters( 'translation_tools_translate_url', $translate_url );

			// Check if the request is for an API URL.
			if ( true === $api ) {
				// Add the API slug.
				$translate_url .= 'api/';
			}

			// WordPress.org translate known projects slugs.
			$wporg_projects = array(
				'languages' => 'languages/',           // Translate languages slug (deprecated).
				'wp'        => 'projects/wp/',         // Translate WordPress slug.
				'plugins'   => 'projects/wp-plugins/', // Translate plugins slug.
				'themes'    => 'projects/wp-themes/',  // Translate themes slug.
			);

			// Check if project is one of the known ones.
			if ( array_key_exists( $project, $wporg_projects ) ) {
				// Add project slug to translate URL.
				$translate_url .= $wporg_projects[ $project ];
			}

			return $translate_url;

		}


		/**
		 * Set the path to get the translation file.
		 *
		 * @since 1.0.0
		 * @since 1.0.1  Increase translate.wp.org languages API timeout to 20 seconds.
		 * @since 1.2.0  Use Locale object.
		 * @since 1.2.3  Rename filter 'ttools_get_wp_translations_status' to 'translation_tools_get_wp_translations_status'.
		 *
		 * @param array  $project   Project array.
		 * @param object $locale    Locale object.
		 *
		 * @return string|null      File path to get source.
		 */
		public static function translation_path( $project, $locale ) {

			// Get WordPress translation project.
			$translation_project = self::get_core_translation_project();

			$translation_path = esc_url_raw(
				add_query_arg(
					array(
						// Filter 'translation_tools_get_wp_translations_status' allows to set another status ( e.g.: 'current_or_waiting_or_fuzzy' ).
						'filters[status]' => apply_filters( 'translation_tools_get_wp_translations_status', 'current' ),
						'format'          => 'po',
					),
					self::translate_url( 'wp', false ) . $translation_project->slug . '/' . $project['slug'] . $locale->locale_slug . '/export-translations'
				)
			);

			return $translation_path;
		}


		/**
		 * Get locale data from wordpress.org and Translation Tools.
		 *
		 * Example:
		 * $locale = Translations_API::locale( 'pt_PT' );
		 * $locale_english_name = $locale->english_name.
		 *
		 * @since 1.0.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param string $wp_locale  Locale ( e.g. 'pt_PT' ).
		 *
		 * @return object            Return selected Locale object data from Translation Tools and wordpress.org (e.g. 'english_name', 'native_name', 'lang_code_iso_639_1', 'country_code', 'wp_locale', 'slug', etc. ).
		 */
		public static function locale( $wp_locale ) {

			// Get wordpress.org Locales.
			$locales = Locales::locales();

			$current_locale = null;

			foreach ( $locales as $locale ) {

				if ( $locale->wp_locale === $wp_locale ) {

					$current_locale = $locale;
					break;

				}
			}

			return $current_locale;
		}

	}

}
