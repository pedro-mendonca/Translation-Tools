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
		 * TODO: Use transients to cache core sub-project data.
		 *
		 * @since 1.2.2
		 *
		 * @return object|null  Object of WordPress translation sub-project, null if API is unreachable.
		 */
		public function get_core_translation_project() {

			// Get WordPress translation project path.
			$source = $this->translations_api_url( 'wp' );

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
			$wp_version = $this->major_version( get_bloginfo( 'version' ) );

			foreach ( $projects as $project ) {

				$translation_version = $this->major_version( $project->name );

				// Check for the WordPress installed major version translation project.
				if ( $wp_version === $translation_version ) {
					$translation_project = $project;
				}
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
		public function major_version( $version ) {

			$major_version = substr( $version, 0, 3 );

			return $major_version;
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
		 * @return string           Returns API URL.
		 */
		public function translations_api_url( $project = null ) {

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
		 * @return string          Returns URL.
		 */
		public static function translations_url( $project = null ) {

			$translations_url = 'https://translate.wordpress.org/projects/';

			$project_slug = array(
				'wp'      => 'wp',         // Translate WordPress core URL.
				'plugins' => 'wp-plugins', // Translate plugins URL.
				'themes'  => 'wp-themes',  // Translate themes URL.
			);

			if ( array_key_exists( $project, $project_slug ) ) {
				$translations_url .= $project_slug[ $project ] . '/';
			}

			return $translations_url;

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
		public function translation_path( $project, $locale ) {

			// Get WordPress translation project.
			$translation_project = $this->get_core_translation_project();

			$translation_path = esc_url_raw(
				add_query_arg(
					array(
						// Filter 'translation_tools_get_wp_translations_status' allows to set another status ( e.g.: 'current_or_waiting_or_fuzzy' ).
						'filters[status]' => apply_filters( 'translation_tools_get_wp_translations_status', 'current' ),
						// TODO: Test format 'jed' to improve download speed.
						'format'          => 'po',
					),
					self::translations_url() . $translation_project->path . '/' . $project['slug'] . $locale->locale_slug . '/export-translations'
				)
			);

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
