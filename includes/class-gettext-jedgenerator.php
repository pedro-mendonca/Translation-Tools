<?php
/**
 * Class file for the Translation Tools Gettext JedGenerator.
 *
 * Based on WP-CLi i18n Command JedGenerator.
 * https://github.com/wp-cli/i18n-command/blob/master/src/JedGenerator.php
 *
 * @package Translation_Tools
 *
 * @since 1.0.0
 */

namespace Translation_Tools;

use Gettext\Generator\Generator;
use Gettext\Translations;
use Gettext\Headers;
use Gettext\Translation;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Gettext_JedGenerator' ) ) {

	/**
	 * Class Gettext_JedGenerator.
	 *
	 * Adds some more meta data to JED translation files than the default generator.
	 */
	class Gettext_JedGenerator extends Generator {


		/**
		 * Options passed to json_encode().
		 *
		 * @var int JSON options.
		 */
		protected $json_options = 0;


		/**
		 * Saves the translations in a file.
		 *
		 * @param Translations $translations  Array with all translations.
		 * @param array        $options       Options.
		 *
		 * @return string
		 */
		public function generateString( Translations $translations, array $options = array() ) : string { // phpcs:ignore.

			$data     = '';
			$domain   = $translations->getDomain() ? $translations->getDomain() : 'messages';
			$messages = static::buildMessages( $translations );
			$headers  = $translations->getHeaders()->toArray();

			/**
			 * Set the file structure.
			 *
			 * 'wpcli' : wp-cli/i18n-command structure.
			 * 'wporg' : wordpress.org language packs structure.
			 */
			$structure = 'wporg';
			switch ( $structure ) {
				case 'wpcli':
					$configuration = array(
						'' => array(
							'domain'       => $domain,
							'lang'         => $translations->getLanguage() ? $translations->getLanguage() : 'en',
							'plural-forms' => $headers['Plural-Forms'] ? $headers['Plural-Forms'] : 'nplurals=2; plural=(n != 1);',
						),
					);
					$data          = array(
						'translation-revision-date' => $headers['PO-Revision-Date'],
						'generator'                 => 'Translation Tools/' . TRANSLATION_TOOLS_VERSION,
						'source'                    => $translations->getHeaders()->get( 'Source' ),
						'domain'                    => $domain,
						'locale_data'               => array(
							$domain => $configuration + $messages,
						),
					);
					break;
				case 'wporg':
					$configuration = array(
						'' => array(
							'domain'       => $domain,
							'plural-forms' => $headers['Plural-Forms'] ? $headers['Plural-Forms'] : 'nplurals=2; plural=(n != 1);',
							'lang'         => $translations->getLanguage() ? $translations->getLanguage() : 'en',
						),
					);
					$data          = array(
						'translation-revision-date' => $headers['PO-Revision-Date'],
						'generator'                 => 'Translation Tools/' . TRANSLATION_TOOLS_VERSION,
						'domain'                    => $domain,
						'locale_data'               => array(
							$domain => $configuration + $messages,
						),
						'comment'                   => array(
							'reference' => $translations->getHeaders()->get( 'Source' ),
						),
					);
					break;
			}

			return wp_json_encode( $data, $this->json_options ); // phpcs:ignore.
		}


		/**
		 * Generates an array with all translations.
		 *
		 * @param Translations $translations  Array with all translations.
		 *
		 * @return array
		 */
		public static function buildMessages( Translations $translations ) {
			$headers           = $translations->getHeaders()->toArray();
			$plural_forms      = $headers['Plural-Forms'];
			$number_of_plurals = is_array( $plural_forms ) ? ( $plural_forms[0] - 1 ) : null;
			$messages          = array();
			$context_glue      = chr( 4 );

			foreach ( $translations as $translation ) {

				if ( $translation->isDisabled() ) {
					continue;
				}

				$key = $translation->getOriginal();

				if ( $translation->getContext() ) {
					$key = $translation->getContext() . $context_glue . $key;
				}

				// $translation->hasPluralTranslations( true )
				if ( self::hasPluralTranslations( $translation ) ) {
					$message = $translation->getPluralTranslations( $number_of_plurals );
					array_unshift( $message, $translation->getTranslation() );
				} else {
					$message = array( $translation->getTranslation() );
				}

				$messages[ $key ] = $message;
			}

			return $messages;
		}


		/**
		 *
		 * @param bool
		 */
		private static function hasPluralTranslations( Translation $translation )  {
			return implode( '', $translation->getPluralTranslations() ) !== '';
		}

	}

}
