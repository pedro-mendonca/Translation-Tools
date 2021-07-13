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

				// Provide minified version if SCRIPT_DEBUG is not set to true.
				$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_register_script(
					'translation-tools-update-core',
					TRANSLATION_TOOLS_DIR_URL . 'js/ttools-update-core' . $suffix . '.js',
					array(
						'jquery',
					),
					TRANSLATION_TOOLS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-tools-update-core' );

				wp_localize_script(
					'translation-tools-update-core',
					'ttools',
					$vars
				);

			}

			// Check for General Settings page, Profile page and User Edit page.
			if ( 'options-general.php' === $hook || 'profile.php' === $hook || 'user-edit.php' === $hook || 'settings_page_translation-stats' === $hook ) {

				// Provide minified version if SCRIPT_DEBUG is not set to true.
				$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_register_script(
					'translation-tools-options-general',
					TRANSLATION_TOOLS_DIR_URL . 'js/ttools-options-general' . $suffix . '.js',
					array(
						'jquery',
					),
					TRANSLATION_TOOLS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-tools-options-general' );

				// Get the standard available Locales list.
				remove_filter( 'get_available_languages', array( $this->options_general, 'update_available_languages' ) );
				$available_languages = $this->options_general->available_languages();
				add_filter( 'get_available_languages', array( $this->options_general, 'update_available_languages' ) );

				// Variables to send to JavaScript.
				$vars = array(
					'available_languages' => $available_languages,                 // Get installed languages.
					'all_languages'       => Options_General::all_languages(),     // Get all languages.
					'current_screen'      => get_current_screen()->id,             // Get current screen.
					'compatible_plugins'  => Compatible::get_compatible_plugins(), // Get compatible plugins data.
				);

				wp_localize_script(
					'translation-tools-options-general',
					'ttools',
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
			if ( 'update-core.php' === $hook || 'options-general.php' === $hook || 'profile.php' === $hook || 'user-edit.php' === $hook || 'settings_page_translation-stats' === $hook ) {
				return true;
			}

			return false;

		}

	}

}
