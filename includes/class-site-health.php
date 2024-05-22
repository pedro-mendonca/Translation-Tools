<?php
/**
 * Class file for the Translation Tools Site Health.
 * Inspired by:
 *  - https://core.trac.wordpress.org/ticket/51039#comment:14
 *
 * Documentation about Site Health:
 *  - https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 *
 * @package Translation_Tools
 *
 * @since 1.3.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Site_Health' ) ) {

	/**
	 * Class Site_Health.
	 */
	class Site_Health {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Translation Tools Site Health tests.
			new Site_Health_Tests();

			// Add Translation Tools Site Health debug information.
			new Site_Health_Debug_Info();

			// Add some simple Polyglots styling to Translations tests.
			add_action( 'admin_head-site-health.php', array( $this, 'add_site_health_style' ) );
		}


		/**
		 * Add some simple Polyglots styling to Translations tests.
		 * Load only on 'admin_head-site-health.php'.
		 * Color based on WP Polyglots badge color:
		 * https://make.wordpress.org/meta/handbook/documentation/profile-badges/
		 *  HEX: #c32283
		 *  RGB: rgb(195, 34, 131)
		 *  RGB 80%: rgba(195, 34, 131, 0.8)
		 *
		 * @since 1.4.0
		 *
		 * @return void
		 */
		public function add_site_health_style() {
			?>
			<style>
			.health-check-accordion-trigger .badge.wp-polyglots-pink {
				border: 1px solid #c32283;
			}
			</style>
			<?php
		}


		/**
		 * Get Locale data for Site Health debug info with Locale formatted name and Language Pack current status.
		 *
		 * @since 1.3.0
		 *
		 * @param string $wp_locale  Core WP Locale.
		 *
		 * @return string  Locale formatted data with Language Packs current status.
		 */
		public static function locale_lang_pack_status( $wp_locale ) {

			// Get Locale data.
			$locale = Translations_API::locale( $wp_locale );

			// Get the formatted Locale name.
			$formatted_name = Options_General::locale_name_format( $locale );

			if ( 'en_US' === $wp_locale ) {

				return esc_html__( 'WordPress default language, has no translation.', 'translation-tools' );

			} elseif ( $locale->has_translations() ) {

				return sprintf(
					/* translators: %s: Locale name. */
					esc_html__( '%s has Language Packs.', 'translation-tools' ),
					$formatted_name
				);

			} else {

				return sprintf(
					/* translators: %s: Locale name. */
					esc_html__( '%s has no Language Packs.', 'translation-tools' ),
					$formatted_name
				);

			}
		}
	}

}
