<?php
/**
 * Class file for the Translation Tools Locale.
 *
 * Extends GP_Locale from:
 * https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php
 *
 * @package Translation_Tools
 *
 * @since 1.6.1
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Locale' ) ) {

	/**
	 * Class Locale.
	 */
	class Locale extends GP_Locale {


		/**
		 * Array of available translations data obtained with wp_get_available_translations() for the locale.
		 *
		 * @var array
		 */
		public $translations;

		/**
		 * Locale slug. Eg.: 'pt/default'.
		 *
		 * @var string
		 */
		public $locale_slug;

		/**
		 * Subdomain of the Locale team page on wp.org.
		 *
		 * @var string
		 */
		public $wporg_subdomain;


		/**
		 * Constructor.
		 *
		 * @param GP_Locale $locale  GP_Locale object.
		 *
		 * @return void
		 */
		public function __construct( $locale ) {

			// Import parent object properties.
			foreach ( get_object_vars( $locale ) as $key => $value ) {
				$this->$key = $value;
			}

			// Add 'wporg_subdomain' property.
			$this->wporg_subdomain = Locales::wporg_subdomain( $locale );

			// Add 'locale_slug' property.
			$this->locale_slug = Locales::locale_slug( $locale );
		}


		/**
		 * Check if Locale has translations, and Language Packs.
		 *
		 * @since 1.7.1
		 *
		 * @return bool
		 */
		public function has_translations() {

			if ( is_null( $this->translations ) ) {
				return false;
			} else {
				return true;
			}
		}
	}

}
