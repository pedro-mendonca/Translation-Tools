<?php
/**
 * Class file for the Translation Tools Utils.
 *
 * @package Translation_Tools
 *
 * @since 1.0.0
 * @since 1.5.0   Renamed from Globals to Utils.
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Utils' ) ) {

	/**
	 * Class Utils.
	 */
	class Utils {


		/**
		 * Determine if Translation Tools is in development mode.
		 *
		 * Inspired by Yoast (https://github.com/Yoast/wordpress-seo/blob/f174ad88636f9115a8c25f66daafbf84c747679b/inc/class-wpseo-utils.php#L716).
		 *
		 * @since 1.5.0
		 *
		 * @return bool
		 */
		public static function is_development_mode() {

			$development_mode = false;

			// Enable if WP_DEBUG is true.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$development_mode = true;
			}

			// Enable if TRANSLATION_TOOLS_DEBUG is true.
			if ( defined( 'TRANSLATION_TOOLS_DEBUG' ) ) {
				$development_mode = TRANSLATION_TOOLS_DEBUG;
			}

			/**
			 * Filter the Translation Tools development mode status.
			 *
			 * @since 1.5.0
			 *
			 * @param bool $development_mode   Set development mode to true or false.
			 */
			return apply_filters( 'translation_tools_development_mode', $development_mode );
		}


		/**
		 * Get asset URL, according the minification status.
		 *
		 * @since 1.5.0
		 *
		 * @param string $asset    Name of asset excluding the extension.
		 * @param bool   $minify   Determine if the asset has a minified version.
		 *
		 * @return string|false   Complete URL for the asset. Return false if extension is not supported.
		 */
		public static function get_asset_url( $asset, $minify ) {

			$path = pathinfo( $asset );

			// Supported asset types and folders.
			$types = array(
				'css',
				'js',
				'jpg',
				'png',
				'svg',
			);

			// Check if path has dirname and extension.
			if ( ! isset( $path['dirname'] ) || ! isset( $path['extension'] ) ) {
				return false;
			}

			// Check if type is supported.
			if ( ! in_array( $path['extension'], $types, true ) ) {
				return false;
			}

			// Only provide minified assets if in development mode or SCRIPT_DEBUG is set to true.
			$suffix = $minify && ! self::is_development_mode() && ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ? '.min' : '';

			return TRANSLATION_TOOLS_DIR_URL . 'assets/' . $path['dirname'] . '/' . $path['filename'] . $suffix . '.' . $path['extension'];
		}


		/**
		 * Returns array of allowed HTML elements for use in wp_kses().
		 *
		 * @since 1.0.0
		 * @since 1.5.0   Moved to Utils class.
		 *
		 * @return array  Array of allowed HTML elements.
		 */
		public static function allowed_html() {

			$allowed_html = array(
				'a'      => array(
					'href'   => array(),
					'title'  => array(),
					'class'  => array(),
					'data'   => array(),
					'rel'    => array(),
					'target' => array(),
				),
				'br'     => array(),
				'button' => array(
					'aria-expanded' => array(),
					'class'         => array(),
					'id'            => array(),
					'type'          => array(),
				),
				'div'    => array(
					'class' => array(),
					'data'  => array(),
					'style' => array(),
				),
				'em'     => array(),
				'form'   => array(
					'action' => array(),
					'class'  => array(),
					'method' => array(),
					'name'   => array(),
				),
				'img'    => array(
					'alt'    => array(),
					'class'  => array(),
					'height' => array(),
					'src'    => array(),
					'width'  => array(),
				),
				'input'  => array(
					'class' => array(),
					'name'  => array(),
					'type'  => array(),
					'value' => array(),
				),
				'li'     => array(
					'class' => array(),
				),
				'ol'     => array(
					'class' => array(),
				),
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
				'p'      => array(
					'class' => array(),
				),
				'script' => array(),
				'select' => array(
					'id'    => array(),
					'class' => array(),
					'name'  => array(),
				),
				'span'   => array(
					'class' => array(),
					'style' => array(),
				),
				'strong' => array(),
				'style'  => array(),

				'ul'     => array(
					'class' => array(),
				),
			);

			return $allowed_html;
		}
	}
}
