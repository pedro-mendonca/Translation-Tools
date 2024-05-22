<?php
/**
 * Class file for the Translation Tools Gettext.
 *
 * Use Gettext/Gettext v.4.8.1 library to extract .po translations and generate .json files.
 * https://github.com/php-gettext/Gettext/tree/4.x
 *
 * Based on WP-CLi i18n Command 'wp i18n make-json'.
 * https://github.com/wp-cli/i18n-command/blob/master/src/MakeJsonCommand.php
 * https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/plugins/wporg-gp-customizations/inc/cli/class-language-pack.php#L435
 *
 * @package Translation_Tools
 *
 * @since 1.0.0
 */

namespace Translation_Tools;

use Gettext\Translations;
use Gettext\Generators\Jed;
use WP_Error;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Gettext' ) ) {

	/**
	 * Class Gettext.
	 */
	class Gettext {


		/**
		 * Options passed to json_encode().
		 *
		 * @var int JSON options.
		 */
		protected $json_options = 0;

		/**
		 * Splits a single PO file into multiple JSON files.
		 *
		 * Based on WP-CLi Command 'wp i18n make-json'.
		 * https://github.com/wp-cli/i18n-command/blob/master/src/MakeJsonCommand.php#L120
		 *
		 * The default .json file names are ${domain}-${locale}-${md5}.json.
		 * For WordPress core translation files, all .json files must be named as ${locale}-${md5}.json.
		 * In this case the WP_Locale used is the one "Site Language".
		 * https://developer.wordpress.org/block-editor/developers/internationalization/#create-translation-file
		 *
		 * @since 1.0.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param string       $destination      Local destination of the language file. ( e.g: local/site/wp-content/languages/ ).
		 * @param array        $project          Project array.
		 * @param Locale       $locale           Locale object.
		 * @param Translations $translations     Extracted Gettext Translations to export.
		 * @param bool         $include_domain   Include the ${domain} in the file name. Set to true or false. Defaults to true.
		 *
		 * @return array                   List of created JSON files.
		 */
		public function make_json( $destination, $project, $locale, $translations, $include_domain = true ) {

			$mapping = array();
			$result  = array();

			// Set the file naming convention. ( e.g.: {domain}-{locale}-{hash}.json ).
			// If $include_domain is set to false, use file name convention ${locale}-${md5}.json.
			$domain         = $project['Domain'] && $include_domain ? $project['Domain'] . '-' : '';
			$base_file_name = $domain . $locale->wp_locale;

			foreach ( $translations as $translation ) {

				// Find all unique JavaScript sources this translation originates from.
				$sources = array_map(
					function ( $reference ) {

						// Get only the file name, without the line number.
						$file = $reference[0];

						// Check if reference is a minified JavaScript file.
						if ( substr( $file, - 7 ) === '.min.js' ) {
							return substr( $file, 0, - 7 ) . '.js';
						}

						// Check if reference is a JavaScript file.
						if ( substr( $file, - 3 ) === '.js' ) {
							return $file;
						}

						// Return empty source.
						return null;
					},
					// Get translation references.
					$translation->getReferences()
				);

				// Remove duplicate source files and empty (null) entries.
				$sources = array_unique( array_filter( $sources ) );

				foreach ( $sources as $source ) {
					if ( ! isset( $mapping[ $source ] ) ) {
						$mapping[ $source ] = new Translations();

						$mapping[ $source ]->setDomain( strval( $translations->getDomain() ) );
						$mapping[ $source ]->setHeader( 'Language', strval( $translations->getLanguage() ) );
						$mapping[ $source ]->setHeader( 'PO-Revision-Date', strval( $translations->getHeader( 'PO-Revision-Date' ) ) );
						$plural_forms = $translations->getPluralForms();

						if ( $plural_forms ) {
							list( $count, $rule ) = $plural_forms;
							$mapping[ $source ]->setPluralForms( $count, $rule );
						}
					}

					$mapping[ $source ][] = $translation;
				}
			}

			$result += $this->build_json_files( $mapping, $base_file_name, $destination );
			return $result;
		}


		/**
		 * Builds a mapping of JS file names to translation entries.
		 *
		 * Exports translations for each JS file to a separate translation file.
		 *
		 * Based on WP-CLi Command 'wp i18n make-json'.
		 * https://github.com/wp-cli/i18n-command/blob/master/src/MakeJsonCommand.php#L192
		 *
		 * @param array  $mapping         A mapping of files to translation entries.
		 * @param string $base_file_name  Base file name for JSON files.
		 * @param string $destination     Path to the destination directory.
		 *
		 * @return array List of created JSON files.
		 */
		protected function build_json_files( $mapping, $base_file_name, $destination ) {

			global $wp_filesystem;

			// Define variable.
			$result = array();

			$result['log'] = array();

			$result['data'] = true;

			// Check if JavaScript translations exist to generate '.json' files.
			if ( empty( $mapping ) ) {

				// Report message.
				$result['log'][] = sprintf(
					/* translators: %s: File type. */
					esc_html__( 'No JavaScript translations found. No %s file was generated.', 'translation-tools' ),
					'<code>.json</code>'
				);

				return $result;
			}

			foreach ( $mapping as $file => $translations ) {

				$hash             = md5( $file );
				$destination_file = "{$destination}{$base_file_name}-{$hash}.json";

				// Report message.
				$result['log'][] = sprintf(
					/* translators: %s: File name. */
					esc_html__( 'Saving file %s…', 'translation-tools' ),
					'<code>' . esc_html( $base_file_name ) . '-' . esc_html( $hash ) . '.json</code>'
				);

				/**
				 * Merge translations into an existing JSON file.
				 * Based on meta changeset https://meta.trac.wordpress.org/changeset/10064
				 *
				 * Some strings occur in multiple source files which may be used on the frontend
				 * or in the admin or both, thus they can be part of different translation
				 * projects (wp/dev, wp/dev/admin, wp/dev/admin/network).
				 * Unlike in PHP with gettext, where translations from multiple MO files are merged
				 * automatically, we have do merge the translations before shipping the
				 * single JSON file per reference.
				 */
				// Check if file already exists.
				if ( $wp_filesystem->exists( $destination_file ) ) {
					// TODO: Only check for existent files if it's the second pass and the previous file was just created.
					// TODO: Or merge all .po files, and export all JSON files in one single pass.

					// Decode translations JSON.
					$json_content_decoded = json_decode( Jed::toString( $translations ) );

					// Get existing file translations, to prepare for merge with next pass.
					$existing_json_content_decoded = json_decode( $wp_filesystem->get_contents( $destination_file ) );

					if ( isset( $existing_json_content_decoded->locale_data->messages ) ) {

						foreach ( $existing_json_content_decoded->locale_data->messages as $key => $existing_translations ) {
							if ( ! isset( $json_content_decoded->messages->{ $key } ) ) {

								// Loop all translations, singular and plurals, if exist.
								foreach ( $existing_translations as $existing_translation ) {
									// Add translations from the existing file that don't exist in the current translations.
									$translations->insert( null, $key )->setTranslation( $existing_translation );
								}
							}
						}
					}
				}

				$success = Gettext_JedGenerator::toFile(
					$translations,
					$destination_file,
					array(
						'json'   => $this->json_options,
						'source' => $file,
					)
				);

				if ( ! $success ) {

					// Report message.
					$result['data'] = new WP_Error(
						'generate-json',
						esc_html__( 'Could not create file.', 'translation-tools' )
					);
					return $result;

				}
			}

			return $result;
		}
	}

}
