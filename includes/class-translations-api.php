<?php
/**
 * Class file for the Translation Tools translate.wordpress.org API.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

namespace Translation_Tools;

use WP_Error;

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
		public static function get_wordpress_subprojects() {

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
		 * @since 1.4.0  Add $wp_major_version parameter to allow custom query for WP major version.
		 *               Add support to $force_check to force update transient.
		 *               Return a specified project or fallback to the latest WordPress core translation sub-project.
		 *
		 * @param string $wp_major_version   WordPress core major version (e.g.: 5.5.x). Defaults to installed version.
		 * @param bool   $force_check  Set to 'true' to force update the transient. Defaults to false.
		 *
		 * @return array|WP_Error      Array with log and data of latest or specified WordPress translation sub-project, WP_Error if API is unreachable.
		 */
		public static function get_core_translation_project( $wp_major_version = null, $force_check = false ) {

			// Set the transient name.
			$translation_project_transient = 'wordpress_translation_project';

			// Get WordPress core translation project transient data.
			$translation_sub_projects = get_transient( TRANSLATION_TOOLS_TRANSIENTS_PREFIX . $translation_project_transient );

			// If there is no unexpired transient data or force_check is set to 'true', get translation projects from the API.
			if ( false === $translation_sub_projects || true === $force_check ) {

				// Get WordPress translation project API URL.
				$source = self::translate_url( 'wp', true );

				// Report message.
				$result['log'] = sprintf(
					/* translators: %s: URL. */
					esc_html__( 'Downloading translations data from %s…', 'translation-tools' ),
					'<code>' . esc_html( $source ) . '</code>'
				);

				// Get the translation project data.
				$response = wp_remote_get( $source );

				// Check if WordPress translation project is reachable.
				if ( ! is_array( $response ) || 'application/json' !== $response['headers']['content-type'] ) {

					// Report message.
					$result['data'] = new WP_Error(
						'translations-api-unavailable',
						sprintf(
							/* translators: %s: URL. */
							esc_html__( 'WordPress Translation API is not available on %s.', 'translation-tools' ),
							sprintf(
								'<a href="%1$s" target="_blank">%1$s<span class="screen-reader-text">%2$s</span></a>',
								esc_url( $source ),
								/* translators: Accessibility text. */
								esc_html__( '(opens in a new tab)', 'translation-tools' )
							)
						)
					);

					// Delete transient.
					delete_transient( TRANSLATION_TOOLS_TRANSIENTS_PREFIX . $translation_project_transient );

					return $result;

				}

				// Decode JSON.
				$response = json_decode( $response['body'] );

				// Get the translation sub-projects.
				$translation_sub_projects = $response->sub_projects;

				// Set WordPress core translation project data transient for 1h.
				set_transient( TRANSLATION_TOOLS_TRANSIENTS_PREFIX . $translation_project_transient, $translation_sub_projects, HOUR_IN_SECONDS );

			}

			// Check if major version is provided.
			if ( null === $wp_major_version ) {
				// Get currently installed WordPress major version ( e.g.: '5.5' ).
				$wp_major_version = self::major_version( get_bloginfo( 'version' ) );
			}

			// Fallback to the latest sub-project.
			$result['data'] = $translation_sub_projects[0];

			foreach ( $translation_sub_projects as $sub_project ) {

				$translation_version = self::major_version( $sub_project->name );

				// Check for the WordPress installed major version translation project.
				if ( $wp_major_version === $translation_version ) {
					$result['data'] = $sub_project;
				}
			}

			return $result;

		}


		/**
		 * Get major version number.
		 *
		 * @since 1.2.2
		 *
		 * @param string $wp_version  The version number (e.g.: 5.5.x).
		 *
		 * @return string          Returns major version (e.g.: 5.5).
		 */
		public static function major_version( $wp_version ) {

			$wp_major_version = substr( $wp_version, 0, 3 );

			return $wp_major_version;
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
			 * Filters the translate site URL. Defaults to Translating WordPress.org site.
			 * This allows to override with a private GlotPress install with the same exact WP core structure as https://translate.w.org/projects/wp/
			 * Example: 'https://translate.my-site.com/glotpress/'
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
				'languages' => 'languages/',           // Translating WordPress languages slug (deprecated).
				'wp'        => 'projects/wp/',         // Translating WordPress core slug.
				'plugins'   => 'projects/wp-plugins/', // Translating WordPress plugins slug.
				'themes'    => 'projects/wp-themes/',  // Translating WordPress themes slug.
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
		 * @since 1.2.0  Use Locale object.
		 * @since 1.2.3  Rename filter 'ttools_get_wp_translations_status' to 'translation_tools_get_wp_translations_status'.
		 *
		 * @param array  $project   Project array.
		 * @param object $locale    Locale object.
		 *
		 * @return string|null      File path to get source.
		 */
		public static function translation_path( $project, $locale ) {

			// Get current WordPress major version ( e.g.: '5.5' ).
			$wp_major_version = self::major_version( get_bloginfo( 'version' ) );

			// Get WordPress translation project, currently installed version, fallback to latest existent, no force update.
			$translation_project = self::get_core_translation_project( $wp_major_version, false );

			$translation_path = esc_url_raw(
				add_query_arg(
					array(
						/**
						 * Filter the status of the translations strings to get.
						 * Examples of useful status:
						 *   - 'current'                                       Gets all currently translated strings (Default).
						 *   - 'current_or_fuzzy'                              Gets all currently translated and fuzzy strings.
						 *   - 'current_or_waiting'                            Gets all currently translated and waiting strings.
						 *   - 'current_or_waiting_or_fuzzy'                   Gets all currently translated, fuzzy and waiting strings.
						 *   - 'current_or_waiting_or_fuzzy_or_untranslated'   Gets all currently translated, fuzzy, waiting and untranslated strings.
						 *
						 * @since 1.2.3
						 */
						'filters[status]' => apply_filters( 'translation_tools_get_wp_translations_status', 'current' ),
						'format'          => 'po',
					),
					self::translate_url( 'wp', false ) . $translation_project['data']->slug . '/' . $project['slug'] . $locale->locale_slug . '/export-translations'
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
