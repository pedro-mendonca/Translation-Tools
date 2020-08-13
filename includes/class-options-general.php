<?php
/**
 * Class file for the Translation Tools general options.
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

if ( ! class_exists( __NAMESPACE__ . '\Options_General' ) ) {

	/**
	 * Class Options_General.
	 */
	class Options_General {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Locales with no Language Packs to the available languages.
			add_filter( 'get_available_languages', array( $this, 'update_available_languages' ) );

			// Remove Locales with no Language Packs from the Themes and Plugins available update languages.
			add_filter( 'plugins_update_check_locales', array( $this, 'reset_available_languages' ) );
			add_filter( 'themes_update_check_locales', array( $this, 'reset_available_languages' ) );

			// Fallback for Core Locale if it has no Language Packs.
			add_filter( 'core_version_check_locale', array( $this, 'core_version_check_locale' ) );

			// Add Site Language description.
			add_action( 'load-options-general.php', array( $this, 'settings_site_language' ) );
			// Add Site Language css.
			add_action( 'load-options-general.php', array( $this, 'settings_site_language_css' ) );

			// Add Profile and User Edit Language description.
			add_action( 'personal_options', array( $this, 'settings_site_language' ) );
			// Add Profile and User Edit Language css.
			add_action( 'personal_options', array( $this, 'settings_site_language_css' ) );

			// Add Translation Stats Language css.
			add_action( 'settings_page_translation-stats', array( $this, 'settings_site_language_css' ) );

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

			// If the current Locale has no files installed, keep it anyway in the available languages.
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
		 * @since 1.2.0  Get Locales from library.
		 *               Use Locale 'wp_locale' as array key.
		 *
		 * @param array $languages  Languages array.
		 *
		 * @return array            Filtered languages array.
		 */
		public function update_available_languages( $languages ) {

			$locales = Locales::locales();

			foreach ( $locales as $locale ) {
				if ( ! isset( $locale->translations ) ) {
					$languages[] = $locale->wp_locale;
				}
			}

			// Remove duplicate locales, from WPLANG, installed and custom locales.
			$languages = array_unique( $languages );

			// Sort ascending.
			sort( $languages );

			return $languages;

		}


		/**
		 * Remove additional languages from the themes and plugins languages updates.
		 *
		 * @since 1.1.0
		 *
		 * @param array $languages  Languages array.
		 *
		 * @return array            Filtered languages array.
		 */
		public function reset_available_languages( $languages ) {

			// Remove update_available_languages filter.
			remove_filter( 'get_available_languages', array( $this, 'update_available_languages' ) );

			// Get the standard available languages.
			$languages = $this->available_languages();

			return $languages;

		}


		/**
		 * Set Core Update Locale to default 'en_US' if it has no Language Packs.
		 *
		 * @since 1.1.0
		 * @since 1.2.0  Use Locale object.
		 *
		 * @param string $wp_locale  Core WP Locale.
		 *
		 * @return string            Core WP Locale, defaults to 'en_US' if no localized version available.
		 */
		public function core_version_check_locale( $wp_locale ) {

			// Get Translation Tools Locale data.
			$locale = Translations_API::locale( $wp_locale );

			// If the current locale has no Language Packs, set the core update to default 'en_US'.
			if ( ! isset( $locale->translations ) ) {
				$wp_locale = 'en_US';
			}

			return $wp_locale;

		}


		/**
		 * Render description for settings Language select field.
		 *
		 * @since 1.1.0
		 * @since 1.2.0  Use user object to get user Locale.
		 *               Loaded on 'personal_options' hook to allow use of $user.
		 * @since 1.2.2  Remove $user param.
		 *
		 * @return void
		 */
		public function settings_site_language() {

			// Get site and user core update Locales.
			$wp_locales = Update_Core::core_update_locales();

			// If Locales array is empty, do nothing.
			if ( empty( $wp_locales ) ) {
				return;
			}

			foreach ( $wp_locales as $wp_locale ) {

				// Get Locale data.
				$locale = Translations_API::locale( $wp_locale );

				$native_name = $locale->native_name;

				if ( isset( $locale->translations ) ) {

					// Format Locale name.
					$locales_with_lang_packs[] = $native_name . ' [' . $wp_locale . ']';
				} else {
					// Format Locale name.
					$locales_with_no_lang_packs[] = $native_name . ' [' . $wp_locale . ']';
				}
			}
			?>

			<div id="ttools_language_select_description" style="display: none;">

				<?php
				// Check for Locales with Language Packs (available translations).
				if ( ! empty( $locales_with_lang_packs ) ) {
					?>
					<p class="description has-lang-packs">
						<?php
						echo wp_kses_post(
							wp_sprintf(
								/* translators: %l: Coma separated list of Locales. */
								_n(
									'<strong>%l</strong> has Language Packs.',
									'<strong>%l</strong> have Language Packs.',
									count( $locales_with_lang_packs ),
									'translation-tools'
								),
								$locales_with_lang_packs
							)
						);
						?>

					</p>
					<?php
				}
				// Check for Locales with no Language Packs (available translations).
				if ( ! empty( $locales_with_no_lang_packs ) ) {
					?>
					<p class="description has-no-lang-packs">
						<?php
						echo wp_kses_post(
							wp_sprintf(
								/* translators: %l: Coma separated list of Locales. */
								_n(
									'<strong>%l</strong> has no Language Packs.',
									'<strong>%l</strong> have no Language Packs.',
									count( $locales_with_no_lang_packs ),
									'translation-tools'
								),
								$locales_with_no_lang_packs
							)
						);
						?>

					</p>
					<?php

				}

				printf(
					'<p>%s<br>%s</p>',
					sprintf(
						/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
						esc_html__( 'To update the WordPress translations %1$sclick here%2$s.', 'translation-tools' ),
						'<a href="' . esc_url( admin_url( 'update-core.php?ttools=force_update_core' ) ) . '">',
						'</a>'
					),
					sprintf(
						/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
						esc_html__( 'Learn more about %1$sLanguage Packs%2$s.', 'translation-tools' ),
						'<a href="' . esc_url( 'https://make.wordpress.org/polyglots/teams/' ) . '" target="_blank">',
						'</a>'
					)
				);
			?>

			</div>

			<?php
		}


		/**
		 * Render description for settings Language select field.
		 *
		 * @since 1.1.0
		 * @since 1.2.0  Use user object to get user Locale.
		 *               Loaded on 'personal_options' hook to allow use of $user.
		 * @since 1.2.2  Remove $user param.
		 *
		 * @return void
		 */
		public function settings_site_language_css() {

			/**
			 * Filter to highlight Locales with no language packs in the language select fields. Defaults to false.
			 *
			 * Filter example: add_filter( 'translation_tools_show_locale_colors', '__return_true' );
			 *
			 * @since 1.2.3
			 */
			$show_locale_colors = apply_filters( 'translation_tools_show_locale_colors', false );

			// Show formated Locale if DEBUG is true or if filter 'translation_tools_show_locale_colors' is true.
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG || $show_locale_colors ) {
				?>
				<style>
					select option[data-has-lang-packs="false"],
					ul li[data-has-lang-packs="false"], /* Preferred Languages selected list items. */
					#ttools_language_select_description .has-no-lang-packs strong {
						background-color: rgb(195, 34, 131, .1); /* Traslation Tools secondary color 10% */
					}
				</style>
				<?php
			}

		}


		/**
		 * Get the available languages to populate the languages dropdown.
		 * Use 'wp_locale' as key as used in the 'available_translations' data.
		 *
		 * @since 1.2.0
		 *
		 * @return array  Array of the available languages.
		 */
		public static function all_languages() {

			// Get Locales.
			$locales = Locales::locales();

			$languages = array();

			foreach ( $locales as $locale ) {
				// Set 'lang' option attrib to the first Locale 'lang_code_iso_639' code, empty if none.
				$lang = isset( $locale->translations ) ? array_values( $locale->translations['iso'] )[0] : '';

				// Check if Language Packs are available.
				$lang_packs = isset( $locale->translations ) ? true : false;

				/**
				 * Filter to show Locales codes in the language select fields. Defaults to false.
				 * Output example: 'Português [pt_PT]'.
				 *
				 * Filter example: add_filter( 'translation_tools_show_locale_codes', '__return_true' );
				 *
				 * @since 1.2.3
				 */
				$show_locale_codes = apply_filters( 'translation_tools_show_locale_codes', false );

				// Set language name to 'native_name'.
				$name = $locale->native_name;

				// Append 'wp_locale' if DEBUG is true or if filter 'translation_tools_show_locale_codes' is true.
				if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG || $show_locale_codes ) {
					$name .= ' [' . $locale->wp_locale . ']';
				}

				$language = array(
					'value'      => $locale->wp_locale, // Option 'value'.
					'lang'       => $lang,              // Option 'lang' attrib.
					'lang_packs' => $lang_packs,        // Option 'data-has-lang-packs' attrib.
					'name'       => $name,              // Option text.
				);
				// Set language with 'wp_locale' as key, as used in the 'available_translations' data.
				$languages[ $locale->wp_locale ] = $language;

			}

			return $languages;

		}

	}

}
