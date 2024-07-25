<?php
/**
 * Class file for the Translation Tools general options.
 *
 * @package Translation_Tools
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
			add_action( 'admin_head-options-general.php', array( $this, 'settings_language_field_description' ) );
			// Add Site Language css.
			add_action( 'admin_head-options-general.php', array( $this, 'settings_site_language_css' ) );

			// Add Profile and User Edit Language description.
			add_action( 'admin_head-profile.php', array( $this, 'settings_language_field_description' ) );
			// Add Profile and User Edit Language css.
			add_action( 'admin_head-profile.php', array( $this, 'settings_site_language_css' ) );

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
		public static function available_languages() {

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

			// Exclude 'en_US' from the Locales array.
			unset( $locales['en'] );

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
			if ( ! $locale->has_translations() ) {
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
		 * @since 1.6.0  Rename from settings_site_language() to settings_language_field_description().
		 *
		 * @return void
		 */
		public function settings_language_field_description() {

			// Get site and user core update Locales.
			$wp_locales = Update_Core::core_update_locales();

			// If Locales array is empty, do nothing.
			if ( empty( $wp_locales ) ) {
				return;
			}

			// Define variable.
			$locales_with_lang_packs = array();

			// Define variable.
			$locales_with_no_lang_packs = array();

			foreach ( $wp_locales as $wp_locale ) {

				// Get Locale data.
				$locale = Translations_API::locale( $wp_locale );

				// Get the formatted Locale name.
				$formatted_name = self::locale_name_format( $locale );

				if ( $locale->has_translations() ) {
					// Format Locale name.
					$locales_with_lang_packs[] = $formatted_name;
				} else {
					// Format Locale name.
					$locales_with_no_lang_packs[] = $formatted_name;
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
						'<a href="' . esc_url( wp_nonce_url( admin_url( 'update-core.php?action=force-translation-upgrade' ), 'translation-tools-update', 'translation_tools_nonce' ) ) . '">',
						'</a>'
					),
					sprintf(
						/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
						esc_html__( 'Learn more about %1$sLanguage Packs%2$s.', 'translation-tools' ),
						'<a href="' . esc_url( 'https://make.wordpress.org/polyglots/teams/' ) . '" target="_blank">',
						sprintf(
							' <span class="screen-reader-text">%s</span><span aria-hidden="true" class="dashicons dashicons-external" style="text-decoration: none;"></span></a>',
							/* translators: Accessibility text. */
							esc_html__( '(opens in a new tab)', 'translation-tools' )
						)
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

			// Get the filtered Locale naming preferences.
			$locale_name_format = self::locale_name_format();

			// Highlighted Locales with no language packs in the language select fields if 'show_locale_colors' is set to true.
			if ( isset( $locale_name_format['show_locale_colors'] ) && $locale_name_format['show_locale_colors'] ) {
				?>
				<style>
					/* Site Language */
					select#WPLANG option[data-has-lang-packs="false"],
					/* User Language */
					select#locale option[data-has-lang-packs="false"],
					/* Language Setting description */
					#ttools_language_select_description .has-no-lang-packs strong {
						background-color: rgba(195, 34, 131, .1); /* Translation Tools secondary color 10% */
					}

					/* Preferred Languages CSS. */
					#preferred-languages-root div.preferred-languages div.inactive-locales select {
						background-color: rgba(255, 255, 255, 1);
					}
					ul li[data-has-lang-packs="false"], /* Preferred Languages selected list items. */
					#preferred-languages-root div.preferred-languages div.inactive-locales select optgroup option[data-has-lang-packs="false"] {
						background-color: rgba(195, 34, 131, .1); /* Translation Tools secondary color 10% */
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
				if ( ! is_null( $locale->lang_code_iso_639_1 ) ) {
					$lang = $locale->lang_code_iso_639_1;
				} elseif ( ! is_null( $locale->lang_code_iso_639_2 ) ) {
					$lang = $locale->lang_code_iso_639_2;
				} elseif ( ! is_null( $locale->lang_code_iso_639_3 ) ) {
					$lang = $locale->lang_code_iso_639_3;
				} else {
					$lang = '';
				}

				// Check if Language Packs are available.
				$lang_packs = isset( $locale->translations ) ? true : false;

				// Get the formatted Locale name.
				$formatted_name = self::locale_name_format( $locale );

				$language = array(
					'value'      => $locale->wp_locale, // Option 'value'.
					'lang'       => $lang,              // Option 'lang' attrib.
					'lang_packs' => $lang_packs,        // Option 'data-has-lang-packs' attrib.
					'name'       => $formatted_name,     // Option text.
				);
				// Set language with 'wp_locale' as key, as used in the 'available_translations' data.
				$languages[ $locale->wp_locale ] = $language;

			}

			return $languages;
		}


		/**
		 * Format how Locale names are shown in the the languages dropdown and admin notices.
		 * Returns Locale formatted name if $locale is passed.
		 * Returns Locale format settings if no $locale is passed.
		 *
		 * @since 1.2.3
		 *
		 * @param Locale $locale  Locale object. Defaults to null.
		 *
		 * @return string|array   Formatted Locale name if $locale is passed. Array of Locale name format parameters if no $locale passed.
		 */
		public static function locale_name_format( $locale = null ) {

			/**
			 * Filter to show Locales codes in the language select fields.
			 * True if Development Mode is enabled. Defaults to false.
			 *
			 * Output example: 'Português [pt_PT]'.
			 *
			 * Filter example: add_filter( 'translation_tools_show_locale_codes', '__return_true' );
			 *
			 * @since 1.2.3
			 */
			$show_locale_codes = apply_filters( 'translation_tools_show_locale_codes', Utils::is_development_mode() ? true : false );

			/**
			 * Filter to highlight Locales with no language packs in the language select fields.
			 * True if Development Mode is enabled. Defaults to false.
			 *
			 * Filter example: add_filter( 'translation_tools_show_locale_colors', '__return_true' );
			 *
			 * @since 1.2.3
			 */
			$show_locale_colors = apply_filters( 'translation_tools_show_locale_colors', Utils::is_development_mode() ? true : false );

			$name_format = array(
				'show_locale_codes'  => $show_locale_codes,
				'show_locale_colors' => $show_locale_colors,
			);

			// Check for $locale parameter.
			if ( null === $locale ) {
				// Return Locale name format filtered settings.
				return $name_format;
			}

			// Set language name to 'native_name'.
			$formatted_name = $locale->native_name;

			// Append 'wp_locale' if 'show_locale_codes' is true.
			if ( $name_format['show_locale_codes'] ) {
				$formatted_name .= ' [' . $locale->wp_locale . ']';
			}

			return $formatted_name;
		}
	}

}
