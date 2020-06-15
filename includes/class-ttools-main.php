<?php
/**
 * Primary class file for the Translation Tools plugin.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TTools_Main' ) ) {

	/**
	 * Class TTools_Main.
	 */
	class TTools_Main {


		/**
		 * Translations API.
		 *
		 * @var object
		 */
		protected $translations_api;

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
			new TTools_Update_Core();

			// Instantiate Translation Tools Options General.
			$this->options_general = new TTools_Options_General();

			// Instantiate Translation Tools Translations API.
			$this->translations_api = new TTools_Translations_API();

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
					TTOOLS_DIR_URL . 'js/ttools-update-core' . $suffix . '.js',
					array(
						'jquery',
					),
					TTOOLS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-tools-update-core' );

				wp_localize_script(
					'translation-tools-update-core',
					'ttools',
					$vars
				);

			}

			// Check for General Settings page and Profile page.
			if ( 'options-general.php' === $hook || 'profile.php' === $hook ) {

				// Provide minified version if SCRIPT_DEBUG is not set to true.
				$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

				wp_register_script(
					'translation-tools-options-general',
					TTOOLS_DIR_URL . 'js/ttools-options-general' . $suffix . '.js',
					array(
						'jquery',
					),
					TTOOLS_VERSION,
					false
				);

				wp_enqueue_script( 'translation-tools-options-general' );

				// Get Locales with no Language Packs.
				$locales_no_lang_packs = $this->translations_api->get_locales_with_no_lang_packs();

				// Get the desired available Locales list.
				remove_filter( 'get_available_languages', array( $this->options_general, 'update_available_languages' ) );
				$available_languages = $this->options_general->available_languages();
				add_filter( 'get_available_languages', array( $this->options_general, 'update_available_languages' ) );

				// Variables to send to JavaScript.
				$vars = array(
					'available_languages'          => $available_languages,
					'optgroup_lang_packs_title'    => esc_html_x( 'Available (Language Packs)', 'Languages group label', 'translation-tools' ),
					'optgroup_no_lang_packs_title' => esc_html_x( 'Available (No Language Packs)', 'Languages group label', 'translation-tools' ),
					'locales_no_lang_packs'        => $locales_no_lang_packs,
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
			if ( 'update-core.php' === $hook || 'options-general.php' === $hook || 'profile.php' === $hook ) {
				return true;
			}

			return false;

		}

	}

}
