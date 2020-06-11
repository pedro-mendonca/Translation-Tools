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
		 * Constructor.
		 */
		public function __construct() {

			// Register and enqueue plugin style sheet.
			add_action( 'admin_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

			// Initialize the Update Core page metadata view.
			new TTools_Update_Core();

			// Initialize the General Options class.
			new TTools_Options_General();

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

			// Check for general settings page.
			if ( 'options-general.php' === $hook ) {

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

			// Check for Updates page and General Options page.
			if ( 'update-core.php' === $hook || 'options-general.php' === $hook ) {
				return true;
			}

			return false;

		}

	}

}
