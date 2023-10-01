<?php
/**
 * Class file for the Translation Tools Site Health Test WordPress Translations API.
 *
 * Documentation about Site Health:
 *  - https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 *
 * @package Translation_Tools
 *
 * @since 1.4.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Site_Health_Test_WordPress_Translations_API' ) ) {

	/**
	 * Class Site_Health_Test_WordPress_Translations_API.
	 */
	class Site_Health_Test_WordPress_Translations_API extends Site_Health_Test {


		/**
		 * The unique name of the test.
		 *
		 * @var string
		 */
		protected $test_id = 'translation_tools_test_wordpress_translations_api';


		/**
		 * Run test for WordPress translation API availability.
		 *
		 * @since 1.4.0
		 *
		 * @return void.
		 */
		public function run_test() {

			// Get installed WordPress core translation project, force update by default.
			$translation_project = Translations_API::get_core_translation_project( $this->wp_major_version, $this->force_check );

			// Check if API is reachable.
			if ( ! is_wp_error( $translation_project['data'] ) ) {

				$this->test_status      = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_GOOD;
				$this->test_label       = esc_html__( 'WordPress Translation API is available.', 'translation-tools' );
				$this->test_description = sprintf(
					'<p>%s</p>',
					sprintf(
						wp_kses_post(
							/* translators: %s: API URL. */
							__( 'WordPress Translation API is available on %s.', 'translation-tools' )
						),
						sprintf(
							'<a href="%1$s" target="_blank">%1$s<span class="screen-reader-text">%2$s</span></a>',
							esc_url( Translations_API::translate_url( 'wp', true ) ),
							/* translators: Accessibility text. */
							esc_html__( '(opens in a new tab)', 'translation-tools' )
						)
					)
				);

				return;

			}

			$this->test_status      = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_CRITICAL;
			$this->test_label       = esc_html__( 'WordPress Translation API is not available.', 'translation-tools' );
			$this->test_description = sprintf(
				'<p>%s</p>',
				sprintf(
					wp_kses_post(
						/* translators: %s: API URL. */
						__( 'WordPress Translation API is not available on %s.', 'translation-tools' )
					),
					sprintf(
						'<a href="%1$s" target="_blank">%1$s<span class="screen-reader-text">%2$s</span></a>',
						esc_url( Translations_API::translate_url( 'wp', true ) ),
						/* translators: Accessibility text. */
						esc_html__( '(opens in a new tab)', 'translation-tools' )
					)
				)
			);
		}
	}

}
