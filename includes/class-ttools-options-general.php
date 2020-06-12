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

			// Add custom languages.
			add_filter( 'get_available_languages', array( $this, 'available_languages' ) );

			// Register settings fields.
			add_action( 'admin_init', array( $this, 'settings_register' ) );

			// Instantiate Translation Tools Translations API.
			$this->translations_api = new TTools_Translations_API();

		}


		/**
		 * Add custom languages.
		 *
		 * @since 1.0.0
		 *
		 * @param array $languages  Languages array.
		 *
		 * @return array            Filtered languages array.
		 */
		public function available_languages( $languages ) {

			// Get custom language option.
			$options = get_option( TTOOLS_WP_OPTION );

			if ( empty( $options['additional_language'] ) ) {
				return $languages;
			}

			// Add custom language to languages array.
			$languages[] = $options['additional_language'];

			// Sort ascending.
			sort( $languages );

			// Remove duplicate locales, from WPLANG, installed and custom locales.
			$languages = array_unique( $languages );

			return $languages;

		}


		/**
		 * Get Locales with no language pack support.
		 *
		 * @since 1.0.0
		 *
		 * @return array  Array of languages with no language packs.
		 */
		public function get_locales_with_no_lang_packs() {

			// All locales available from Translate API.
			$all_locales = $this->translations_api->get_locales();
			if ( ! $all_locales ) {
				$all_locales = array();
			}

			// Locales with language packs.
			$locales_with_lang_packs = get_site_transient( 'available_translations' );

			// Locales with no language packs.
			$locales_with_no_lang_packs = array_diff_key( $all_locales, $locales_with_lang_packs );

			// Remove 'en_US' locale.
			unset( $locales_with_no_lang_packs['en_US'] );

			return $locales_with_no_lang_packs;
		}


		/**
		 * Register settings.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_register() {

			register_setting(
				'general',
				TTOOLS_WP_OPTION
			);

			add_settings_field(
				'additional_language',
				'<label for="TTools_Aditional_Language"></label>',
				array( $this, 'settings_render' ),
				'general'
			);

		}


		/**
		 * Render Translation Tools settings.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function settings_render() {
			
			// Get Locales with no Language Packs.
			$missing_locales = $this->get_locales_with_no_lang_packs();
			
			// Get Translation Tools settings.
			$options = get_option( TTOOLS_WP_OPTION );
			
			// Check if the 'additional_language' is set.
			$value = isset( $options['additional_language'] ) ? $options['additional_language'] : '';
			?>
			<label>
				<select name="<?php echo esc_attr( TTOOLS_WP_OPTION ) . '[additional_language]'; ?>" id="<?php echo esc_attr( TTOOLS_WP_OPTION ) . '[additional_language]'; ?>" <?php disabled( empty( $missing_locales), true, true ); ?>>
					<option value=""></option>
					<optgroup label="<?php esc_attr_e( 'No Language Packs', 'translation-tools' ); ?>">
						<?php
						foreach ( $missing_locales as $key => $locale ) {
							$option = sprintf(
								/* translators: 1: Locale code. 2: Locale native name. */
								esc_html__( '%1$s (%2$s)', 'translation-tools' ),
								$locale['wp_locale'],
								$locale['native_name']
							);
							?>
							<option value="<?php echo esc_attr( $locale['wp_locale'] ); ?>" <?php selected( $value, $key, true ); ?>><?php echo esc_html( $option ); ?></option>
							<?php
						}
						?>
					</optgroup>

				</select>
				<?php
				esc_html_e( 'Select a Locale with no Language Packs', 'translation-tools' );
				?>
			</label>
			<p class="description" id="TTools_Aditional_Language-description">
				<?php
				// If there are no aditional Locales to show.
				if ( empty( $missing_locales ) ) {
					printf(
						esc_html__( 'There are no %1$smissing Locales%2$s, or the %3$stranslate.wp.org API%4$s is unreachable.', 'translation-tools' ),
						'<a href="https://make.wordpress.org/polyglots/teams/#no-language-pack" target="_blank">',
						'</a>',
						'<a href="' . $this->translations_api->translations_api_url( 'languages' ) . '" target="_blank">',
						'</a>'
					);
				} else {
					printf(
						'%s %s',
						esc_html__( 'Choose one Locale to add to the above available languages.', 'translation-tools' ),
						sprintf(
							'%s%s%s',
							'<a href="https://make.wordpress.org/polyglots/teams/#no-language-pack" target="_blank">',
							esc_html(
								sprintf(
									/* translators: %d: Locales count. */
									_n(
										'There is %d Locale that has no language packs',
										'There are %d Locales that has no language packs',
										count( $missing_locales ),
										'translation-tools'
									),
									count( $missing_locales )
								)
							),
							'</a>'
						)
					);
				}
				?>
			</p>

			<?php
		}


	}

}
