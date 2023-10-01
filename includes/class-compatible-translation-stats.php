<?php
/**
 * Class file for the Translation Tools compatibility with Translation Stats plugin.
 *
 * @package Translation_Tools
 *
 * @since 1.2.3
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Compatible_Translation_Stats' ) ) {

	/**
	 * Class Compatible_Translation_Stats.
	 */
	class Compatible_Translation_Stats extends Compatible {


		/**
		 * Plugin file.
		 *
		 * @var string
		 */
		public $plugin_file = 'translation-stats/translation-stats.php';


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Check plugin compatibility.
			if ( self::is_compatible( $this->plugin_file ) ) {

				// If incompatible, return unfiltered $compatible_plugins array.
				add_filter( 'translation_tools_compatible_plugins', array( $this, 'translation_stats_settings' ) );

			}
		}


		/**
		 * Get the Translation Stats settings.
		 *
		 * @since 1.2.3
		 *
		 * @param array $compatible_plugins  Array of compatible plugins.
		 *
		 * @return array                     Array of compatible plugins with added data.
		 */
		public function translation_stats_settings( $compatible_plugins ) {

			// Check Translation Stats settings.
			if ( ! defined( 'TRANSLATION_STATS_WP_OPTION' ) ) {
				// If settings not defined, return unfiltered $compatible_plugins array.
				return $compatible_plugins;
			}

			// Get Translation Stats settings.
			$options = get_option( TRANSLATION_STATS_WP_OPTION );

			// Check Translation Stats language setting.
			if ( ! isset( $options['settings']['translation_language'] ) ) {
				// If setting not defined, return unfiltered $compatible_plugins array.
				return $compatible_plugins;
			}

			// Add plugin settings plugin data..
			$compatible_plugins[ $this->plugin_file ]['settings'] = $options['settings'];

			return $compatible_plugins;
		}
	}

}
