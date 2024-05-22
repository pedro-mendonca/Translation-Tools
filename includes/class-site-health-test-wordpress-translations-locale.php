<?php
/**
 * Class file for the Translation Tools Site Health Test WordPress Translations Locale.
 *
 * Documentation about Site Health:
 *  - https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 *
 * @package Translation_Tools
 *
 * @since 1.3.0
 * @since 1.4.0    Rename filter 'Site_Health_Test_WordPress_Translations' to 'Site_Health_Test_WordPress_Translations_Locale'.
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Site_Health_Test_WordPress_Translations_Locale' ) ) {

	/**
	 * Class Site_Health_Test_WordPress_Translations_Locale.
	 */
	class Site_Health_Test_WordPress_Translations_Locale extends Site_Health_Test {


		/**
		 * The unique name of the test.
		 *
		 * @var string
		 */
		protected $test_id = 'translation_tools_test_wordpress_translations_locale';

		/**
		 * The required dependency test and status to enable the current Test.
		 *
		 * @var array
		 */
		protected $required_test = array(
			'test'   => 'translation_tools_test_wordpress_translations_api',
			'status' => self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_GOOD,
		);

		/**
		 * The WordPress Locale translations to test.
		 *
		 * @var string
		 */
		protected $wp_locale = null;


		/**
		 * Constructor.
		 *
		 * @param string $wp_locale   WordPress Locale to test.
		 */
		public function __construct( $wp_locale ) {

			// Load parent construct.
			parent::__construct();

			if ( $wp_locale ) {

				// Set $wp_locale.
				$this->wp_locale = $wp_locale;

				// Add Locale sufix to test ID.
				$this->test_id = $this->test_id . '_' . $wp_locale;
			}
		}


		/**
		 * Run test for WordPress translations.
		 *
		 * @since 1.4.0
		 *
		 * @return void.
		 */
		public function run_test() {

			$locale = Translations_API::locale( $this->wp_locale );

			// Get installed WordPress core translation project, don't need to force check because the required API Test just updated the transient.
			$translation_project = Translations_API::get_core_translation_project( $this->wp_major_version, false );

			// Get translation project major version.
			$translation_project_version = Translations_API::major_version( $translation_project['data']->name );

			// Get translation project major version.
			if ( $locale->has_translations() ) {
				$locale_translations_version = Translations_API::major_version( $locale->translations['version'] );
			}

			// Set language name to 'native_name'.
			$formatted_name = Options_General::locale_name_format( $locale );

			// Check if Language Packs exist for the Locale and if the Language Pack major version is the same as the WordPress installed major version.
			if ( $locale->has_translations() && isset( $locale_translations_version ) && $translation_project_version === $locale_translations_version ) {

				$this->test_status = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_GOOD;
				$this->test_label  = sprintf(
					wp_kses_post(
						/* translators: 1: WordPress version. 2: Locale name. */
						__( 'The translation of WordPress %1$s for %2$s has Language Packs.', 'translation-tools' )
					),
					esc_html( $translation_project_version ),
					esc_html( $formatted_name )
				);
				$this->test_description = sprintf(
					'<p>%s</p>',
					sprintf(
						wp_kses_post(
							/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
							__( 'The translation of WordPress %1$s for %2$s has Language Pack updated on %3$s.', 'translation-tools' )
						),
						'<strong>' . esc_html( $translation_project_version ) . '</strong>',
						'<strong>' . esc_html( $formatted_name ) . '</strong>',
						'<code>' . esc_html( $locale->translations['updated'] ) . '</code>'
					)
				);

				$this->test_actions = sprintf(
					/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
					esc_html__( 'To update the WordPress translations %1$sclick here%2$s.', 'translation-tools' ),
					'<a href="' . esc_url( wp_nonce_url( admin_url( 'update-core.php?action=force-translation-upgrade' ), 'translation-tools-update', 'translation_tools_nonce' ) ) . '">',
					'</a>'
				);

				return;

			}

			$this->test_status = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_RECOMMENDED;
			$this->test_label  = sprintf(
				wp_kses_post(
					/* translators: 1: WordPress version. 2: Locale name. */
					__( 'The translation of WordPress %1$s for %2$s has no Language Pack yet.', 'translation-tools' )
				),
				esc_html( $translation_project_version ),
				esc_html( $formatted_name )
			);
			$this->test_description = sprintf(
				'<p><strong>%s</strong></p>',
				sprintf(
					/* translators: %s: Locale name. */
					__( 'Translate WordPress to %s!', 'translation-tools' ),
					esc_html( $formatted_name )
				)
			);
			$this->test_description .= sprintf(
				'<p>%s<br>%s</p>',
				sprintf(
					/* translators: %s: Locale name. */
					__( 'It looks like you understand %s. You can help translate WordPress and its plugins/themes in your language.', 'translation-tools' ),
					'<strong>' . esc_html( $formatted_name ) . '</strong>'
				),
				sprintf(
					wp_kses_post(
						/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
						__( 'To get started, please %1$svisit this page%2$s', 'translation-tools' )
					),
					'<a href="https://make.wordpress.org/polyglots/handbook/translating/first-steps/" target="_blank">',
					sprintf(
						' <span class="screen-reader-text">%s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
						/* translators: Accessibility text. */
						esc_html__( '(opens in a new tab)', 'translation-tools' )
					)
				)
			);

			$this->test_actions = sprintf(
				wp_kses_post(
					/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. 3: Opening link tag <a href="[link]">. 4: Locale name. */
					__( 'Please register at %1$sTranslating WordPress%2$s and join the %3$sTranslation Team%2$s to help translating WordPress to %4$s!', 'translation-tools' )
				),
				'<a href="https://translate.wordpress.org/locale/' . esc_html( $locale->locale_slug ) . '/' . esc_html( $translation_project['data']->path ) . '/" target="_blank">',
				sprintf(
					' <span class="screen-reader-text">%s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
					/* translators: Accessibility text. */
					esc_html__( '(opens in a new tab)', 'translation-tools' )
				),
				'<a href="https://make.wordpress.org/polyglots/teams/?locale=' . esc_attr( $locale->wp_locale ) . '" target="_blank">',
				'<strong>' . esc_html( $formatted_name ) . '</strong>'
			);

			$this->test_actions .= '<br><br>' . sprintf(
				/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. */
				esc_html__( 'To update the WordPress translations %1$sclick here%2$s.', 'translation-tools' ),
				'<a href="' . esc_url( wp_nonce_url( admin_url( 'update-core.php?action=force-translation-upgrade' ), 'translation-tools-update', 'translation_tools_nonce' ) ) . '">',
				'</a>'
			);
		}
	}

}
