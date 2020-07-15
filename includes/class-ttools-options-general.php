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

			// Remove Locales with no Language Packs from the Themes and Plugins available update languages.
			add_filter( 'plugins_update_check_locales', array( $this, 'reset_available_languages' ) );
			add_filter( 'themes_update_check_locales', array( $this, 'reset_available_languages' ) );

			// Fallback for Core Locale if it has no Language Packs.
			add_filter( 'core_version_check_locale', array( $this, 'core_version_check_locale' ) );

			// Add Site Language description.
			add_action( 'load-options-general.php', array( $this, 'settings_site_language' ) );

			// Add Profile and User Edit Language description.
			add_action( 'personal_options', array( $this, 'settings_site_language' ) );

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
		 * @since 1.2.0  Get Locales from library.
		 *               Use Locale 'wp_locale' as array key.
		 *
		 * @param array $languages  Languages array.
		 *
		 * @return array            Filtered languages array.
		 */
		public function update_available_languages( $languages ) {

			$locales = TTools_Locales::locales();

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
			$locale = $this->translations_api->locale( $wp_locale );

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
		 *
		 * @param object $user  User object.
		 *
		 * @return void
		 */
		public function settings_site_language( $user ) {

			// Show formated Locale if DEBUG is true.
			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
				?>
				<style>
					option[data-has-lang-packs="false"],
					#ttools_language_select_description .has-no-lang-packs code {
						background-color: rgb(195, 34, 131, .1); /* Traslation Tools secondary color 10% */
					}
				</style>
				<?php
			}

			// Get user ID.
			$user_id = ! empty( $user ) ? $user->ID : null;

			// Get user Locale, fallsback to site Locale.
			$wp_locale = get_user_locale( $user_id );

			// Check if current Locale is 'en_US'.
			if ( 'en_US' === $wp_locale ) {
				return;
			}

			// Get Locale data.
			$locale = $this->translations_api->locale( $wp_locale );

			// Set class.
			$lang_packs_class = isset( $locale->translations ) ? 'has-lang-packs' : 'has-no-lang-packs';

			$name = $locale->native_name;
			?>

			<div id="ttools_language_select_description" style="display: none;">
				<p class="description <?php echo esc_attr( $lang_packs_class ); ?>" id="ttools_current_locale_description">

					<?php
					// Check if Locale have Language Packs (available translations).
					if ( isset( $locale->translations ) ) {
						$locale_info = sprintf(
							/* translators: %s: Locale name. */
							__( 'The Locale %s has Language Packs.', 'translation-tools' ),
							'<code>' . $name . ' [' . $wp_locale . ']</code>'
						);
					} else {
						$locale_info = sprintf(
							/* translators: %s: Locale name. */
							__( 'The Locale %s has no Language Packs.', 'translation-tools' ),
							'<code>' . $name . ' [' . $wp_locale . ']</code>'
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
			$locales = TTools_Locales::locales();

			$languages = array();

			foreach ( $locales as $key => $locale ) {
				// Set 'lang' option attrib to the first Locale 'lang_code_iso_639' code, empty if none.
				$lang = isset( $locale->translations ) ? array_values( $locale->translations['iso'] )[0] : '';

				// Check if Language Packs are available.
				$lang_packs = isset( $locale->translations ) ? true : false;

				// Language name is 'native_name', append 'wp_locale' if WP_DEBUG is set. Example: 'Português [pt_PT]'.
				$name = $locale->native_name;
				if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
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
