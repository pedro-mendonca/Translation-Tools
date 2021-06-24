<?php
/**
 * Class file for the Translation Tools Site Health Tests.
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

if ( ! class_exists( __NAMESPACE__ . '\Site_Health_Tests' ) ) {

	/**
	 * Class Site_Health_Tests.
	 */
	class Site_Health_Tests {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Test WordPress translations for every available language.
			add_action( 'admin_init', array( $this, 'test_wordpress_translations_locales' ) );


		}


		/**
		 * Test WordPress translations for every available language.
		 * Inspired by:
		 *  - https://core.trac.wordpress.org/ticket/51039#comment:14
		 *
		 * @since 1.3.0
		 *
		 * @return void
		 */
		public function test_wordpress_translations_locales() {

			/**
			 * TODO: Check percentage:
			 *  - https://translate.wordpress.org/api/projects/wp/dev/
			 */

			// Get site and user core update Locales.
			$wp_locales = Update_Core::core_update_locales();

			foreach ( $wp_locales as $wp_locale ) {

				// Initialize Class file for the Translation Tools Site Health WordPress Translations for a WP_Locale.
				new Site_Health_Test_WordPress_Translations_Locale( $wp_locale );

			}

		}

	}

}
