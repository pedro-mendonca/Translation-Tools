<?php
/**
 * Primary class file for the Translation Tools plugin.
 *
 * @package Translation_Tools
 *
 * @since 1.0.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Translation_Tools' ) ) {

	/**
	 * Class Translation_Tools.
	 */
	class Translation_Tools {


		/**
		 * General Options.
		 *
		 * @var object
		 */
		protected $options_general;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Register and enqueue plugin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

			// Initialize the Update Core page metadata view.
			new Update_Core();

			// Instantiate Translation Tools Options General.
			$this->options_general = new Options_General();

			// Initialize Class file for the Translation Tools compatibility with Preferred Languages plugin.
			new Compatible_Preferred_Languages();

			// Initialize Class file for the Translation Tools compatibility with Translation Stats plugin.
			new Compatible_Translation_Stats();

			// Initialize Class file for the Translation Tools Site Health.
			new Site_Health();
		}


		/**
		 * Register and enqueue scripts.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook  Hook.
		 *
		 * @return void
		 */
		public function register_plugin_scripts( $hook ) {

			if ( ! $this->allowed_pages( $hook ) ) {
				return;
			}

			// Variables to send to JavaScript.
			$vars = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			);

			// Check for updates page.
			if ( 'update-core.php' === $hook ) {

				wp_register_script(
					'translation-tools-update-core',
					Utils::get_asset_url( 'js/update-core.js', true ),
					array(
						'jquery',
					),
					TRANSLATION_TOOLS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-tools-update-core' );

				wp_localize_script(
					'translation-tools-update-core',
					'translationTools',
					$vars
				);

			}

			// Check for General Settings page, Profile page, User Edit page and Translation Stats plugin settings.
			if ( 'options-general.php' === $hook || 'profile.php' === $hook || 'user-edit.php' === $hook || 'settings_page_translation-stats' === $hook ) {

				wp_register_script(
					'translation-tools-language-settings',
					Utils::get_asset_url( 'js/language-settings.js', true ),
					array(
						'jquery',
					),
					TRANSLATION_TOOLS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-tools-language-settings' );

				// Get the standard available Locales list.
				remove_filter( 'get_available_languages', array( $this->options_general, 'update_available_languages' ) );
				$available_languages = Options_General::available_languages();
				add_filter( 'get_available_languages', array( $this->options_general, 'update_available_languages' ) );

				// Get all languages.
				$all_languages = Options_General::all_languages();
				// Exclude 'en_US' from the Locales array.
				unset( $all_languages['en'] );

				// Variables to send to JavaScript.
				$vars = array(
					'available_languages' => $available_languages,                      // Get installed languages.
					'all_languages'       => $all_languages,                            // Get all languages.
					'current_screen'      => get_current_screen()->id,                  // Get current screen.
					'compatible_plugins'  => Compatible::get_compatible_plugins(),      // Get compatible plugins data.
					'wp_version'          => substr( get_bloginfo( 'version' ), 0, 3 ), // Get current WordPress major version.
				);

				wp_localize_script(
					'translation-tools-language-settings',
					'translationTools',
					$vars
				);

			}
		}


		/**
		 * Set admin pages where to load Translation Tools styles and scripts.
		 *
		 * @since 1.0.0
		 *
		 * @param string $hook  Hook.
		 *
		 * @return bool  Return true if current page is allowed, false if isn't allowed.
		 */
		public function allowed_pages( $hook ) {

			// Check for Updates page, General Options page and Profile page.
			return in_array( $hook, array( 'update-core.php', 'options-general.php', 'profile.php', 'user-edit.php', 'settings_page_translation-stats' ), true );
		}
	}

}
