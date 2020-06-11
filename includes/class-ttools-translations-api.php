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
		 *
		 * @param array $project   Project array.
		 * @param array $locale    Locale array.
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

			$translation_path = esc_url_raw( $this->translations_url( 'wp' ) . $wp_version['slug'] . '/' . $project['slug'] . $locale['slug']['locale'] . '/' . $locale['slug']['variant'] . '/export-translations' . $args );

			return $translation_path;
		}


		/**
		 * Get available translations locales data from translate.WordPress.org API.
		 * Store the available translation locales in transient.
		 *
		 * @since 1.0.0
		 *
		 * @return array  Returns all the locales with 'wp_locale' available in translate.WordPress.org.
		 */
		public function get_locales() {
			// Translate API languages URL.
			$url = $this->translations_api_url( 'languages' );

			// Translation Tools languages transient name.
			$transient_name = 'available_translations';

			// Check languages transients.
			$locales = get_transient( TTOOLS_TRANSIENTS_PREFIX . $transient_name );

			if ( empty( $locales ) ) {

				// Increase remote request timeout from default 5 to 15 seconds.
				$args['timeout'] = 15;

				$json = wp_remote_get( $url, $args );

				if ( is_wp_error( $json ) || wp_remote_retrieve_response_code( $json ) !== 200 ) {

					// API Unreachable: Error 404 or timeout.
					$locales = false;

				} else {

					$body = json_decode( $json['body'], true );
					if ( empty( $body ) ) {

						// No languages found.
						$locales = false;

					} else {

						$locales = array();
						foreach ( $body as $key => $locale ) {

							// List locales based on existent 'wp_locale'.
							if ( $locale['wp_locale'] ) {
								unset( $key );
								$locales[ $locale['wp_locale'] ] = $locale;
							}
						}

						// Only set transient if $locales remote request is succesfull.
						set_transient( TTOOLS_TRANSIENTS_PREFIX . $transient_name, $locales, TTOOLS_TRANSIENTS_LOCALES_EXPIRATION );
					}
				}
			}

			return $locales;
		}


		/**
		 * Get locale data.
		 *
		 * Example:
		 * $locale = $this->translations_api->locale( 'pt_PT' );
		 * $locale_english_name = $locale['english_name'].
		 *
		 * @since 1.0.0
		 *
		 * @param string $wp_locale   WordPress Locale ( e.g. 'pt_PT' ).
		 *
		 * @return false|array        Returns false if translate API is unreachable, or locale array from GlotPress (e.g. 'english_name', 'native_name', 'lang_code_iso_639_1', 'country_code', 'wp_locale', 'slug', etc. ).
		 */
		public function locale( $wp_locale ) {

			$locales = $this->get_locales();

			$locale = null;

			if ( empty( $locales ) ) {
				return false;
			}

			foreach ( $locales as $key => $value ) {
				if ( $value['wp_locale'] === $wp_locale ) {
					unset( $key );
					$locale = $value;

					// Set an array for 'slug' to separate 'locale' and 'variant' slugs.
					$locale['slug'] = $this->locale_slug( $locale );

					// Add 'wporg_subdomain'.
					$locale['wporg_subdomain'] = $this->wporg_subdomain( $locale );

				}
			}
			return $locale;

		}


		/**
		 * Separate Locale slug in array( locale, variant ) to support GlotPress 2.x pseudo-variants.
		 *
		 * Example:
		 * GlotPress Slug: 'slug' => 'pt/ao90'.
		 * TTools Slug: 'slug' => array( 'locale' => 'pt', 'variant' => 'ao90').
		 *
		 * @since 1.0.0
		 *
		 * @param array $locale   Locale array.
		 *
		 * @return array $locale  Returns locale array with slug separated in array.
		 */
		public function locale_slug( $locale ) {

			$locale_slug = $locale['slug'];

			// Check if slug contain '/' and non default variant.
			if ( false !== strpos( $locale_slug, '/' ) ) {

				// In case there is a '/' separator, set the slug as an array 'locale' and 'variant'.
				$locale_slug = array(
					'locale'  => substr( $locale_slug, 0, strpos( $locale_slug, '/' ) ),
					'variant' => substr( $locale_slug, 1 + strpos( $locale_slug, '/' ) ),
				);

			} else {

				// In case there is no '/' separator, set slug as array with pseudo-variant as 'default.
				$locale_slug = array(
					'locale'  => $locale_slug,
					'variant' => 'default',
				);

			}

			return $locale_slug;

		}


		/**
		 * Add WordPress.org Locale subdomain to $locale.
		 * Defaults to Locale 'slug'.
		 * Custom subdomains use custom criteria from Translation Teams page (https://make.wordpress.org/polyglots/teams/) and 'locales.php' in https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php.
		 * Updated on 2019-04-17.
		 *
		 * Example: 'pt_BR' => 'br'.
		 *
		 * @since 1.0.0
		 *
		 * @param array $locale             Locale array.
		 *
		 * @return string $wporg_subdomain  Returns WordPress Locale Subdomain.
		 */
		public function wporg_subdomain( $locale ) {

			// Set default criteria.
			$wporg_subdomain = $locale['slug']['locale'];

			/**
			 * The below Variants aren't included in the array because Translation Tools separates the Locale Slug from the Variant Slug in locale_slug().
			 * The subdomain of the Variants fallbacks automatically to its parent subdomain.
			 *
			 * 'ca_valencia'    => 'ca',    // Variant. Fallback to parent subdomain.
			 * 'nl_NL_formal'   => 'nl',    // Variant. Fallback to parent subdomain.
			 * 'de_DE_formal'   => 'de',    // Variant. Fallback to parent subdomain.
			 * 'de_CH_informal' => 'de-ch', // Variant. Fallback to parent subdomain.
			 * 'pt_PT_ao90'     => 'pt',    // Variant. Fallback to parent subdomain.
			 */
			$wporg_custom_subdomains = array(
				'ba'         => null,
				'bre'        => 'bre',   // As in 'wp_locale'.
				'zh_CN'      => 'cn',    // Custom, doesn't exist in GlotPress.
				'zh_TW'      => 'tw',    // As in 'country_code'.
				'art_xemoji' => 'emoji', // Custom, doesn't exist in GlotPress.
				'ewe'        => null,
				'fo'         => 'fo',    // As in 'country_code'.
				'gn'         => null,
				'haw_US'     => null,
				'ckb'        => 'ku',    // As in 'lang_code_iso_639_1'.
				'lb_LU'      => 'ltz',   // Custom, doesn't exist in GlotPress.
				'xmf'        => null,
				'mn'         => 'khk',   // As in 'lang_code_iso_639_3', doesn't exist in GlotPress.
				'pt_BR'      => 'br',    // As in 'country_code'.
				'pa_IN'      => 'pan',   // As in 'lang_code_iso_639_2'.
				'rue'        => null,
				'sa_IN'      => 'sa',    // As in 'lang_code_iso_639_1'.
				'es_CL'      => 'cl',    // As in 'country_code'.
				'es_PE'      => 'pe',    // As in 'country_code'.
				'es_VE'      => 've',    // As in 'country_code'.
				'gsw'        => null,
				'wa'         => null,
			);

			// Check if 'wp_locale' exist in the custom subdomain criteria array.
			if ( array_key_exists( $locale['wp_locale'], $wporg_custom_subdomains ) ) {
				$wporg_subdomain = $wporg_custom_subdomains[ $locale['wp_locale'] ];
			}

			return $wporg_subdomain;

		}

	}

}
