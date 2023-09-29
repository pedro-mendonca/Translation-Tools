<?php
/**
 * Class file for the Translation Tools translate.wordpress.org API.
 *
 * @package Translation_Tools
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

			// Get current WordPress major version ( e.g.: '5.5' ).
			$wp_major_version = self::major_version( get_bloginfo( 'version' ) );

			// Get WordPress translation project, currently installed version, fallback to latest existent, no force update.
			$translation_project = self::get_core_translation_project( $wp_major_version, false );

			// Get the WordPress translation project version slug ( e.g.: 'dev', '5.8.x', 5.7.x, etc. ).
			$project_slug = $translation_project['data']->slug;

			// All the WordPress core translation projects, the key is the project/subproject slug.
			$subprojects = array(
				$project_slug                    => array(
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'Name'   => _x( 'Development', 'Subproject name', 'translation-tools' ),
					'Domain' => '',
				),
				$project_slug . '/admin'         => array(
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'Name'   => _x( 'Administration', 'Subproject name', 'translation-tools' ),
					'Domain' => 'admin',
				),
				$project_slug . '/admin/network' => array(
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'Name'   => _x( 'Network Admin', 'Subproject name', 'translation-tools' ),
					'Domain' => 'admin-network',
				),
				$project_slug . '/cc'            => array(
					/* translators: Subproject name in translate.wordpress.org, do not translate! */
					'Name'   => _x( 'Continents & Cities', 'Subproject name', 'translation-tools' ),
					'Domain' => 'continents-cities',
				),
			);

			return $subprojects;
		}


		/**
		 * Get the list of installed Plugins hosted on WordPress.org.
		 *
		 * @since 1.5.0
		 *
		 * @return array $plugins   Returns an array of Plugins, with 'slug' as key, 'Name' and language file 'Domain'.
		 */
		public static function get_wordpress_plugins() {

			// Get installed plugins.
			$installed_plugins = get_plugins(); // Key is theme slug.

			$plugins = array();

			foreach ( $installed_plugins as $file => $installed_plugin ) {

				// Check if plugin exist in WordPress.org updates list.
				$update_plugins = get_site_transient( 'update_plugins' );

				// Merge plugins with updates with plugins with no updates.
				$wporg_plugins = array_merge( $update_plugins->response, $update_plugins->no_update );

				if ( array_key_exists( $file, $wporg_plugins ) ) {
					$plugins[ $wporg_plugins[ $file ]->slug ] = array(
						// Set translation project name.
						'Name' => $installed_plugin['Name'],
					);
				}
			}

			/**
			 * Filter the Plugins list to update translations and generate language files.
			 *
			 * @since 1.5.0
			 */
			$plugins = apply_filters( 'translation_tools_update_plugins_list', $plugins );

			return $plugins;
		}


		/**
		 * Get the list of installed themes hosted on WordPress.org.
		 *
		 * @since 1.5.0
		 *
		 * @return array $themes   Returns an array of Themes, with 'slug' as key, 'Name' and language file 'Domain'.
		 */
		public static function get_wordpress_themes() {

			// Get installed themes.
			$installed_themes = wp_get_themes(); // Key is theme slug.

			$themes = array();

			foreach ( $installed_themes as $slug => $installed_theme ) {

				// Check if theme exist in WordPress.org updates list.
				$update_themes = get_site_transient( 'update_themes' );

				// Merge themes with updates with plugins with no updates.
				$wporg_themes = array_merge( $update_themes->response, $update_themes->no_update );

				if ( array_key_exists( $slug, $wporg_themes ) ) {
					$themes[ $slug ] = array(
						// Set translation project name.
						'Name' => $installed_theme->name,
					);
				}
			}

			/**
			 * Filter the Themes list to update translations and generate language files.
			 *
			 * @since 1.5.0
			 */
			$themes = apply_filters( 'translation_tools_update_themes_list', $themes );

			return $themes;
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

			// Define variable.
			$result = array();

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

				/**
				 * Filters the timeout limit, in seconds. Default is increased to 15 seconds.
				 * If your're having timeouts on big translation projects and or very slow internet connections, feel free to increase this value.
				 *
				 * @since 1.5.3
				 */
				$args = array(
					'timeout' => apply_filters( 'translation_tools_download_timeout', 15 ),
				);

				// Get the translation project data.
				$response = wp_remote_get( $source, $args );

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
		 * Get the Translate WordPress site URL.
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
		 * @since 1.5.0  New $type parameter.
		 *               Returns an array of filepath and alternative, useful for plugins ( 'Stable', 'Dev' ).
		 *
		 * @param string $type      Type of translation project ( e.g.: 'wp', 'plugins', 'themes' ).
		 * @param array  $project   Project array.
		 * @param Locale $locale    Locale object.
		 *
		 * @return array            Array of file paths (primary and alternative) to get source.
		 */
		public static function translation_path( $type, $project, $locale ) {

			// Set empty array.
			$translation_paths = array();

			// Add 'stable' or 'dev' subproject to plugin path.
			$plugin_subprojects = array(
				'stable',
				'dev',
			);

			/**
			 * Filter the Plugins subprojects default order. Defaults to 'Stable' first and 'Development' second.
			 * Return false to reverse order.
			 *
			 * @since 1.5.0
			 */
			$plugin_subprojects = apply_filters( 'translation_tools_plugin_stable_first', true ) ? $plugin_subprojects : array_reverse( $plugin_subprojects );

			$types = array(
				'wp'      => false,
				'plugins' => $plugin_subprojects,
				'themes'  => false,
			);

			// Define variable.
			$project_paths = array();

			if ( ! empty( $types[ $type ] ) ) {

				foreach ( $types[ $type ] as $subproject ) {
					$project_paths[] = $project['Slug'] . '/' . $subproject;
				}
			} else {

				$project_paths[] = $project['Slug'];

			}

			foreach ( $project_paths as $project_path ) {

				$translation_paths[] = esc_url_raw(
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
						self::translate_url( $type, false ) . $project_path . '/' . $locale->locale_slug . '/export-translations'
					)
				);

			}

			return $translation_paths;
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
		 * @param string $wp_locale   WP_Locale ( e.g. 'pt_PT' ).
		 *
		 * @return Locale   Return selected Locale object data from Translation Tools and wordpress.org (e.g. 'english_name', 'native_name', 'lang_code_iso_639_1', 'country_code', 'wp_locale', 'slug', etc. ).
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


		/**
		 * Get the local path of the translation file.
		 *
		 * @since 1.5.0
		 *
		 * @param string $project   Set the translation project local destination.
		 *
		 * @return string|null      File path to get source.
		 */
		public static function get_translation_destination( $project = null ) {

			WP_Filesystem();
			global $wp_filesystem;

			// Get the destination of translation files.
			$destination = $wp_filesystem->wp_lang_dir();

			/**
			 * Filters the translation files local path destination.
			 *
			 * @since 1.5.0
			 */
			$destination = apply_filters( 'translation_tools_translation_destination', $destination );

			// WordPress local translation projects destinations.
			$wp_project_types = array(
				'wp'      => '',        // WordPress core translations destination.
				'plugins' => 'plugins/', // Plugins translations destination.
				'themes'  => 'themes/',  // Themes translations destination.
			);

			// Check if project is one of the known ones.
			if ( array_key_exists( $project, $wp_project_types ) ) {
				// Add project destination to translate local path.
				$destination .= $wp_project_types[ $project ];
			}

			return $destination;
		}


		/**
		 * Get the Translate WordPress error.
		 *
		 * @since 1.5.3
		 *
		 * @param int $error_code   The Set the translation project local destination.
		 *
		 * @return string           The error message.
		 */
		public static function get_translations_api_error( $error_code = null ) {

			// Known error codes.
			$errors = array(
				'404' => esc_html__( 'Translation project not found.', 'translation-tools' ),
			);

			// Check if error exist in the known errors list.
			if ( array_key_exists( $error_code, $errors ) ) {

				// Return known error message.
				return $errors[ $error_code ];
			}

			// Return unknown error message.
			return esc_html__( 'Unknown error.', 'translation-tools' );
		}
	}
}
