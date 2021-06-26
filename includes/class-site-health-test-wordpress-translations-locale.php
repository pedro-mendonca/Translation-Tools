<?php
/**
 * Class file for the Translation Tools Site Health Test WordPress Translations Locale.
 *
 * Documentation about Site Health:
 *  - https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 *
 * @package Translation Tools
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

			if ( $wp_locale ) {

				// Set $wp_locale.
				$this->wp_locale = $wp_locale;

				// Add Locale sufix to test ID.
				$this->test_id = $this->test_id . '_' . $wp_locale;
			}

			// Add Translation Tools tests.
			add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );

		}


		/**
		 * Run test for WordPress translations.
		 *
		 * @since 1.4.0
		 *
		 * @param bool $force_check   Set to 'true' to force update the transient. Defaults to false.
		 *
		 * @return void.
		 */
		public function run_test( $force_check = false ) {

			$locale = Translations_API::locale( $this->wp_locale );

			// Get WordPress major version ( e.g.: '5.5' ).
			$wp_version = Translations_API::major_version( get_bloginfo( 'version' ) );

			// Don't need to force check because the required API Test just updated the transient.
			$force_check = false;

			// Get installed WordPress core translation project, force update by default.
			$translation_project = Translations_API::get_core_translation_project( $wp_version, $force_check );

			// Get translation project major version.
			$translation_project_version = Translations_API::major_version( $translation_project['data']->name );

			// Get translation project major version.
			if ( isset( $locale->translations ) ) {
				$locale_translations_version = Translations_API::major_version( $locale->translations['version'] );
			}

			// Set language name to 'native_name'.
			$formated_name = Options_General::locale_name_format( $locale );

			// Check if Language Packs exist for the Locale and if the Language Pack major version is the same as the WordPress installed major version.
			if ( isset( $locale->translations ) && isset( $locale_translations_version ) && $translation_project_version === $locale_translations_version ) {

				$this->test_status = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_GOOD;
				$this->test_label  = sprintf(
					wp_kses_post(
						/* translators: 1: WordPress version. 2: Locale name. */
						__( 'The translation of WordPress %1$s for %2$s is complete.', 'translation-tools' )
					),
					esc_html( $translation_project_version ),
					esc_html( $formated_name )
				);
				$this->test_description = sprintf(
					'<p>%s</p>',
					sprintf(
						wp_kses_post(
							/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
							__( 'The translation of WordPress %1$s for %2$s was updated on %3$s.', 'translation-tools' )
						),
						'<strong>' . esc_html( $translation_project_version ) . '</strong>',
						'<strong>' . esc_html( $formated_name ) . '</strong>',
						'<code>' . esc_html( $locale->translations['updated'] ) . '</code>'
					)
				);

			} else {

				$this->test_status = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_RECOMMENDED;
				$this->test_label  = sprintf(
					wp_kses_post(
						/* translators: 1: WordPress version. 2: Locale name. */
						__( 'The translation of WordPress %1$s for %2$s is not complete.', 'translation-tools' )
					),
					esc_html( $translation_project_version ),
					esc_html( $formated_name )
				);
				$this->test_description = sprintf(
					'<p>%s</p>',
					sprintf(
						wp_kses_post(
							/* translators: 1: Locale name. 2: Opening link tag <a href="[link]">. 3: Closing link tag </a>. */
							__( '<strong>Translate WordPress to %1$s:</strong> It looks like you understand %1$s. Did you know you can help translate WordPress and its plugins/themes in your language? %2$sVisit this page%3$s to get started.', 'translation-tools' )
						),
						esc_html( $formated_name ),
						'<a href="https://make.wordpress.org/polyglots/handbook/translating/first-steps/" target="_blank">',
						sprintf(
							'<span class="screen-reader-text">%s</span></a>',
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
						'<span class="screen-reader-text">%s</span></a>',
						/* translators: Accessibility text. */
						esc_html__( '(opens in a new tab)', 'translation-tools' )
					),
					'<a href="https://make.wordpress.org/polyglots/teams/?locale=' . esc_attr( $locale->wp_locale ) . '" target="_blank">',
					'<strong>' . esc_html( $formated_name ) . '</strong>'
				);

			}

		}

	}

}
