<?php
/**
 * Class file for the Translation Tools Update Translations.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

namespace Translation_Tools;

use Gettext\Translations as Translations;
use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Update_Translations' ) ) {

	/**
	 * Class Update_Translations.
	 */
	class Update_Translations {


		/**
		 * Translations API.
		 *
		 * @var object
		 */
		protected $translations_api;

		/**
		 * Gettext.
		 *
		 * @var object
		 */
		protected $gettext;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Tools Translations API.
			$this->translations_api = new Translations_API();

			// Instantiate Translation Tools Gettext.
			$this->gettext = new Gettext();

		}

		// TODO: Message for when the downloaded .po has no strings.


		/**
		 * Update project translation.
		 * Download .po file, extract with Gettext to generate .po and .json files.
		 *
		 * @since 1.0.0
		 *
		 * @param string $destination   Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project       Project array.
		 * @param string $wp_locale     WP Locale ( e.g.: 'pt_PT' ).
		 *
		 * @return array|WP_Error       Array on success, WP_Error on failure.
		 */
		public function update_translation( $destination, $project, $wp_locale ) {

			// Set array of log entries.
			$result['log'] = array();

			// Get Translation Tools Locale data.
			$locale = $this->translations_api->locale( $wp_locale );

			// Download file from WordPress.org translation table.
			$download = $this->download_translations( $project, $locale );
			array_push( $result['log'], $download['log'] );
			$result['data'] = $download['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Generate .po from WordPress.org response.
			$generate_po = $this->generate_po( $destination, $project, $locale, $download['data'] );
			array_push( $result['log'], $generate_po['log'] );
			$result['data'] = $generate_po['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Extract translations from file.
			$translations = $this->extract_translations( $destination, $project, $locale );
			array_push( $result['log'], $translations['log'] );
			$result['data'] = $translations['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Generate .mo file from extracted translations.
			$generate_mo = $this->generate_mo( $destination, $project, $locale, $translations['data'] );
			array_push( $result['log'], $generate_mo['log'] );
			$result['data'] = $generate_mo['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			// Generate .json files from extracted translations.
			$generate_jsons = $this->gettext->make_json( $destination, $project, $locale, $translations['data'], false );
			$result['log']  = array_merge( $result['log'], $generate_jsons['log'] );
			$result['data'] = $generate_jsons['data'];
			if ( is_wp_error( $result['data'] ) ) {
				return $result;
			}

			array_push( $result['log'], esc_html__( 'Translation updated successfully.', 'translation-tools' ) );

			return $result;

		}


		/**
		 * Download file from WordPress.org translation table.
		 *
		 * @since 1.0.0
		 *
		 * @param array $project    Project array.
		 * @param array $locale     Locale array.
		 *
		 * @return array|WP_Error   Array on success, WP_Error on failure.
		 */
		public function download_translations( $project, $locale ) {

			// Set translation data path.
			$source = Translations_API::translation_path( $project, $locale );

			// Report message.
			$result['log'] = sprintf(
				/* translators: %s: URL. */
				esc_html__( 'Downloading translation from %s…', 'translation-tools' ),
				'<code>' . esc_html( $source ) . '</code>'
			);

			// Get the translation data.
			$response = wp_remote_get( $source );

			if ( ! is_array( $response ) || 'application/octet-stream' !== $response['headers']['content-type'] ) {

				// Report message.
				$result['data'] = new WP_Error(
					'download-translation',
					sprintf(
						'%s %s',
						esc_html__( 'Download failed.', 'translation-tools' ),
						esc_html__( 'A valid URL was not provided.', 'translation-tools' )
					)
				);
				return $result;

			}

			$result['data'] = $response;

			return $result;

		}


		/**
		 * Generate .po from WordPress.org response.
		 *
		 * @since 1.0.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param string $destination   Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project       Project array.
		 * @param object $locale        Locale object.
		 * @param array  $response      HTTP response.
		 *
		 * @return array|WP_Error       Array on success, WP_Error on failure.
		 */
		public function generate_po( $destination, $project, $locale, $response ) {

			// Set the file naming convention. ( e.g.: {domain}-{locale}.po ).
			$domain    = $project['domain'] ? $project['domain'] . '-' : '';
			$file_name = $domain . $locale->wp_locale . '.po';

			// Report message.
			$result['log'] = sprintf(
				/* translators: %s: File name. */
				esc_html__( 'Saving file %s…', 'translation-tools' ),
				'<code>' . esc_html( $file_name ) . '</code>'
			);

			// Generate .po file.
			$success = file_put_contents( $destination . $file_name, $response['body'] ); // phpcs:ignore

			if ( ! $success ) {

				// Report message.
				$result['data'] = new WP_Error(
					'generate-po',
					esc_html__( 'Could not create file.', 'translation-tools' )
				);
				return $result;

			}

			$result['data'] = true;

			return $result;

		}


		/**
		 * Extract translations from file.
		 *
		 * @since 1.0.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param string $destination   Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project       Project array.
		 * @param object $locale        Locale object.
		 *
		 * @return array|WP_Error       Array on success, WP_Error on failure.
		 */
		public function extract_translations( $destination, $project, $locale ) {

			// Set the file naming convention. ( e.g.: {domain}-{locale}.po ).
			$domain    = $project['domain'] ? $project['domain'] . '-' : '';
			$file_name = $domain . $locale->wp_locale . '.po';

			// Report message.
			$result['log'] = sprintf(
				/* translators: %s: File name. */
				esc_html__( 'Extracting translations from file %s…', 'translation-tools' ),
				'<code>' . esc_html( $file_name ) . '</code>'
			);

			$translations = Translations::fromPoFile( $destination . $file_name );

			if ( ! is_object( $translations ) ) {

				// Report message.
				$result['data'] = new WP_Error(
					'extract-translations',
					esc_html__( 'Could not extract translations from file.', 'translation-tools' )
				);

				return $result;

			}

			$result['data'] = $translations;

			return $result;

		}


		/**
		 * Generate .mo file from extracted translations.
		 *
		 * @since 1.0.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param string $destination    Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array  $project        Project array.
		 * @param object $locale         Locale object.
		 * @param object $translations   Extracted translations to export.
		 *
		 * @return array|WP_Error        Array on success, WP_Error on failure.
		 */
		public function generate_mo( $destination, $project, $locale, $translations ) {

			// Set the file naming convention. ( e.g.: {domain}-{locale}.po ).
			$domain    = $project['domain'] ? $project['domain'] . '-' : '';
			$file_name = $domain . $locale->wp_locale . '.mo';

			// Report message.
			$result['log'] = sprintf(
				/* translators: %s: File name. */
				esc_html__( 'Saving file %s…', 'translation-tools' ),
				'<code>' . $file_name . '</code>'
			);

			// Generate .mo file.
			$generate = $translations->toMoFile( $destination . $file_name );

			if ( ! $generate ) {

				// Report message.
				$result['data'] = new WP_Error(
					'generate-mo',
					esc_html__( 'Could not create file.', 'translation-tools' )
				);
				return $result;

			}

			$result['data'] = true;

			return $result;

		}

	}

}
