<?php
/**
 * Class file for the Translation Tools Site Health Test WordPress Translations Version.
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

if ( ! class_exists( __NAMESPACE__ . '\Site_Health_Test_WordPress_Translations_Version' ) ) {

	/**
	 * Class Site_Health_Test_WordPress_Translations_Version.
	 */
	class Site_Health_Test_WordPress_Translations_Version extends Site_Health_Test {


		/**
		 * The unique name of the test.
		 *
		 * @var string
		 */
		protected $test_id = 'translation_tools_test_wordpress_translations_version';

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
		 * Run test for WordPress translations version.
		 *
		 * @since 1.4.0
		 *
		 * @return void.
		 */
		public function run_test() {

			// Get installed WordPress core translation project, don't need to force check because the required API Test just updated the transient.
			$translation_project = Translations_API::get_core_translation_project( $this->wp_major_version, false );

			// Get translation project major version.
			$translation_project_version = Translations_API::major_version( $translation_project['data']->name );

			/*
			 * Check if translation project is already available for the installed version.
			 * It's usually available strings hard freeze.
			 */
			if ( $this->wp_major_version === $translation_project_version ) {

				$this->test_status = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_GOOD;
				$this->test_label  = sprintf(
					wp_kses_post(
						/* translators: %s: WordPress version. */
						__( 'WordPress %s is available for translation.', 'translation-tools' )
					),
					esc_html( $this->wp_major_version )
				);
				$this->test_description = sprintf(
					'<p>%s</p>',
					sprintf(
						wp_kses_post(
							/* translators: 1: WordPress version. 2: URL link. */
							__( 'WordPress %1$s translation project is available on %2$s.', 'translation-tools' )
						),
						'<strong>' . esc_html( $this->wp_major_version ) . '</strong>',
						sprintf(
							'<a href="%1$s" target="_blank">%1$s<span class="screen-reader-text">%2$s</span></a>',
							esc_url( Translations_API::translate_url( 'wp', false ) . $translation_project['data']->slug ),
							/* translators: Accessibility text. */
							esc_html__( '(opens in a new tab)', 'translation-tools' )
						)
					)
				);

				return;

			}

			$this->test_status = self::TRANSLATION_TOOLS_SITE_HEALTH_STATUS_CRITICAL;
			$this->test_label  = sprintf(
				/* translators: %s: WordPress version. */
				__( 'WordPress %s is not available for translation yet.', 'translation-tools' ),
				esc_html( $this->wp_major_version )
			);
			$this->test_description = sprintf(
				'<p>%s</p>',
				sprintf(
					wp_kses_post(
						/* translators: 1: WordPress version. 2: URL link. */
						__( 'WordPress %1$s translation project is not available yet on %2$s.', 'translation-tools' )
					),
					'<strong>' . esc_html( $this->wp_major_version ) . '</strong>',
					sprintf(
						'<a href="%1$s" target="_blank">%1$s<span class="screen-reader-text">%2$s</span></a>',
						esc_url( Translations_API::translate_url( 'wp', false ) ),
						/* translators: Accessibility text. */
						esc_html__( '(opens in a new tab)', 'translation-tools' )
					)
				)
			);
		}
	}

}
