<?php
/**
 * PHPStan bootstrap file
 *
 * @package Translation_Tools
 */


// Set Translation Tools plugin version.
if ( ! defined( 'TRANSLATION_TOOLS_VERSION' ) ) {
	define( 'TRANSLATION_TOOLS_VERSION', '1.0.0' );
}

// Set Translation Tools required PHP version. Needed for PHP compatibility check for WordPress < 5.1.
if ( ! defined( 'TRANSLATION_TOOLS_REQUIRED_PHP' ) ) {
	define( 'TRANSLATION_TOOLS_REQUIRED_PHP', '5.6' );
}

// Set Translation Tools development mode.
if ( ! defined( 'TRANSLATION_TOOLS_DEBUG' ) ) {
	define( 'TRANSLATION_TOOLS_DEBUG', true );
}


// Require plugin main file.
require_once 'translation-tools.php';
