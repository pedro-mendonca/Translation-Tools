<?php
/**
 * Class file for the Translation Tools general options.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TTools_Options_General' ) ) {

	/**
	 * Class TTools_Options_General.
	 */
	class TTools_Options_General {


		/**
		 * Translations API.
		 *
		 * @var object
		 */
		protected $translations_api;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Tools Translations API.
			$this->translations_api = new TTools_Translations_API();

			// Add Locales with no Language Packs to the available languages.
			add_filter( 'get_available_languages', array( $this, 'update_available_languages' ) );

			// Add Site Language description.
			add_action( 'load-options-general.php', array( $this, 'settings_site_language' ) );

			// Add User Language description.
			add_action( 'load-profile.php', array( $this, 'settings_site_language' ) );

		}


		/**
		 * Get the available languages to populate the Installed group.
		 *
		 * @since 1.1.0
		 *
		 * @return array  Array of the available languages.
		 */
		public function available_languages() {

			// Get list of installed languages.
			$languages = get_available_languages();

			// If the current locale has no files installed, keep it anyway in the available languages.
			if ( ! in_array( get_locale(), $languages, true ) && 'en_US' !== get_locale() ) {
				$languages[] = get_locale();
			}

			// Sort ascending.
			sort( $languages );

			// Remove duplicate locales, from WPLANG, installed and custom locales.
			$languages = array_unique( $languages );

			return $languages;

		}


		/**
		 * Add Locales with no Language Packs to the available languages.
		 *
		 * @since 1.1.0
		 *
		 * @param array $languages  Languages array.
		 *
		 * @return array            Filtered languages array.
		 */
		public function update_available_languages( $languages ) {

			$additional_locales = $this->translations_api->get_locales_with_no_lang_packs();

			foreach ( $additional_locales as $key => $additional_locale ) {
				$languages[] = $key;
			}

			// Remove duplicate locales, from WPLANG, installed and custom locales.
			$languages = array_unique( $languages );

			// Sort ascending.
			sort( $languages );

			return $languages;

		}


		/**
		 * Render description for Site Language and User Language settings.
		 *
		 * @since 1.1.0
		 *
		 * @return void
		 */
		public function settings_site_language() {

			$locale = get_user_locale();

			// Check if current Locale is 'en_US'.
			if ( 'en_US' === $locale ) {
				return;
			}
			?>

			<div id="ttools_language_select_description" style="display: none;">
				<p class="description" id="ttools_current_locale_description">
					<?php
					// Get Locales with no Language Packs.
					$locales_no_lang_packs = $this->translations_api->get_locales_with_no_lang_packs();
					$locale_has_langpacks  = array_key_exists( $locale, $locales_no_lang_packs ) ? false : true;

					if ( $locale_has_langpacks ) {
						$locale_info = sprintf(
							/* translators: %s: Locale name. */
							__( 'The Locale %s has Language Packs.', 'translation-tools' ),
							'<code>' . $locale . '</code>'
						);
					} else {
						$locale_info = sprintf(
							/* translators: %s: Locale name. */
							__( 'The Locale %s has no Language Packs.', 'translation-tools' ),
							'<code>' . $locale . '</code>'
						);
					}
					printf(
						'%s %s<br>%s',
						wp_kses_post( $locale_info ),
						sprintf(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
							esc_html__( 'To update the WordPress translation, please %1$sclick here%2$s.', 'translation-tools' ),
							'<a href="' . esc_url( admin_url( 'update-core.php?ttools=force_update_core' ) ) . '">',
							'</a>'
						),
						'<a href="' . esc_url( 'https://make.wordpress.org/polyglots/teams/' ) . '" target="_blank">' . esc_html__( 'Learn more about Language Packs', 'translation-tools' ) . '</a>'
					);
					?>
				</p>
			</div>

			<?php
		}

	}

}
