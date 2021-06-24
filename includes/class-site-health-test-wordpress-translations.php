<?php
/**
 * Class file for the Translation Tools Site Health Test WordPress Translations.
 *
 * Documentation about Site Health:
 *  - https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 *
 * @package Translation Tools
 *
 * @since 1.3.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Site_Health_Test_WordPress_Translations' ) ) {

	/**
	 * Class Site_Health_Test_WordPress_Translations.
	 */
	class Site_Health_Test_WordPress_Translations extends Site_Health_Test {


		/**
		 * The unique name of the test.
		 *
		 * @var string
		 */
		protected $test_id = 'translation-tools-test-wordpress-translations';


		/**
		 * Run test for WordPress translations.
		 *
		 * @since 1.3.0
		 *
		 * @return void.
		 */
		public function run_test() {

			$locale = Translations_API::locale( $this->wp_locale );

			// Get WordPress major version ( e.g.: '5.5' ).
			$wp_version = Translations_API::major_version( get_bloginfo( 'version' ) );

			// Get installed WordPress core translation project.
			$translation_project = Translations_API::get_core_translation_project();

			$translation_version = Translations_API::major_version( $locale->translations['version'] );

			// Set language name to 'native_name'.
			$formated_name = Options_General::locale_name_format( $locale );

			// Check if Language Packs exist for the Locale and if the Language Pack major version is the same as the WordPress installed major version.
			if ( isset( $locale->translations ) && $wp_version === $translation_version ) {

				$this->test_status = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_GOOD;
				$this->test_label  = sprintf(
					wp_kses_post(
						/* translators: 1: WordPress version. 2: Locale name. */
						__( 'The translation of WordPress %1$s for %2$s is complete.', 'translation-tools' )
					),
					esc_html( $translation_project['data']->name ),
					esc_html( $formated_name )
				);
				$this->test_description .= sprintf(
					'<p>%s</p>',
					sprintf(
						wp_kses_post(
							/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
							__( 'The translation of WordPress %1$s for %2$s was updated on %3$s.', 'translation-tools' )
						),
						'<strong>' . esc_html( $translation_project['data']->name ) . '</strong>',
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
					esc_html( $translation_project['data']->name ),
					esc_html( $formated_name )
				);
				$this->test_description .= sprintf(
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

				$this->test_actions .= sprintf(
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
