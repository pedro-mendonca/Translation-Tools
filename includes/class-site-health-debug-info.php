<?php
/**
 * Class file for the Translation Tools Site Health Debug Info.
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

if ( ! class_exists( __NAMESPACE__ . '\Site_Health_Debug_Info' ) ) {

	/**
	 * Class Site_Health_Debug_Info.
	 */
	class Site_Health_Debug_Info {


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Translation Tools debug information.
			add_filter( 'debug_information', array( $this, 'site_health_debug_info' ) );
		}


		/**
		 * Translation Tools Site Health debug information.
		 * Inspired by:
		 *  - https://core.trac.wordpress.org/ticket/51039#comment:14
		 *
		 * @since 1.3.0
		 *
		 * @param array $debug_info  Debug information..
		 *
		 * @return array  Debug information.
		 */
		public function site_health_debug_info( $debug_info ) {

			/**
			 * Filters Site Language data for Site Health.
			 *
			 * @since 1.3.0
			 *
			 * @param array $site_language  An array of the Site Health properties for Site Locale.
			 *
			 * @return array   A filtered array of properties.
			 */
			$site_language = apply_filters(
				'translation_tools_site_health_site_language',
				array(
					'label' => __( 'Site Language', 'translation-tools' ),
					'value' => sprintf(
						'%s: %s',
						get_locale(),
						Site_Health::locale_lang_pack_status( get_locale() )
					),
				)
			);

			/**
			 * Filters User Language data for Site Health.
			 *
			 * @since 1.3.0
			 *
			 * @param array $user_language  An array of the Site Health properties for User Locale.
			 *
			 * @return array   A filtered array of properties.
			 */
			$user_language = apply_filters(
				'translation_tools_site_health_user_language',
				array(
					'label' => __( 'User Language', 'translation-tools' ),
					'value' => sprintf(
						'%s: %s',
						get_user_locale(),
						Site_Health::locale_lang_pack_status( get_user_locale() )
					),
				)
			);

			$debug_info['translation-tools'] = array(
				'label'       => __( 'Translations', 'translation-tools' ),
				'description' => sprintf(
					'%s %s',
					__( 'Details about your WordPress site and user languages.', 'translation-tools' ),
					__( 'Report by plugin Translation Tools', 'translation-tools' )
				),
				'fields'      => array(
					'site_language' => array(
						'label' => $site_language['label'],
						'value' => $site_language['value'],
					),
					'user_language' => array(
						'label' => $user_language['label'],
						'value' => $user_language['value'],
					),
				),
			);

			return $debug_info;
		}
	}

}
