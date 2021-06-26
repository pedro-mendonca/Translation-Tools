<?php
/**
 * Class file for the Translation Tools Site Health Test.
 *
 * Documentation about Site Health:
 *  - https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
 *
 * Inspired by Yoast Site Health checks:
 *  - https://github.com/Yoast/wordpress-seo/blob/trunk/inc/health-check.php
 *
 * @package Translation Tools
 *
 * @since 1.3.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Site_Health_Test' ) ) {

	/**
	 * Class Site_Health_Test.
	 */
	abstract class Site_Health_Test {


		/**
		 * The Site Health check section in which 'good' results should be shown.
		 *
		 * @var string
		 */
		const TRANSLATION_TOOLS_SITE_HEALTH_STATUS_GOOD = 'good';

		/**
		 * The Site Health check section in which 'recommended' results should be shown.
		 *
		 * @var string
		 */
		const TRANSLATION_TOOLS_SITE_HEALTH_STATUS_RECOMMENDED = 'recommended';

		/**
		 * The Site Health check section in which 'critical' results should be shown.
		 *
		 * @var string
		 */
		const TRANSLATION_TOOLS_SITE_HEALTH_STATUS_CRITICAL = 'critical';

		/**
		 * The label of the Test.
		 *
		 * @var string
		 */
		protected $test_label = null;

		/**
		 * The status of the Test.
		 *
		 * @var string
		 */
		protected $test_status = null;

		/**
		 * The default badge text and color of the Test.
		 *
		 * @var array
		 */
		protected $test_badge = array();

		/**
		 * The description of the Test.
		 *
		 * @var string
		 */
		protected $test_description = null;

		/**
		 * The actions of the Test.
		 *
		 * @var string
		 */
		protected $test_actions = null;

		/**
		 * The unique name of the Test.
		 *
		 * @var string
		 */
		protected $test_id = null;

		/**
		 * The WordPress Locale translations to test.
		 *
		 * @var string
		 */
		protected $wp_locale = null;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add Translation Tools tests.
			add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );

		}


		/**
		 * Add a Site Health test.
		 *
		 * @since 1.3.0
		 *
		 * @param array $tests  Tests array.
		 *
		 * @return array  Return tests.
		 */
		public function add_site_health_test( $tests ) {

			$tests['direct'][ $this->test_id ] = array(
				'test' => array( $this, 'test_result' ),
			);

			return $tests;
		}


		/**
		 * The result of the Test as an array.
		 *
		 * @since 1.3.0
		 *
		 * @return array  The test result.
		 */
		public function test_result() {

			// Add the default values for Translation Tools Site Health tests.
			$this->load_test_defaults();

			// Run the actual test.
			$this->run_test();

			// Add text footer.
			$this->add_test_footer();

			$result = array(
				'label'       => $this->test_label,
				'status'      => $this->test_status,
				'badge'       => $this->test_badge,
				'description' => $this->test_description,
				'actions'     => $this->test_actions,
				'test'        => $this->test_id,
			);

			return $result;
		}


		/**
		 * Add the default values for Translation Tools Site Health tests.
		 *
		 * @since 1.3.0
		 *
		 * @return void
		 */
		public function load_test_defaults() {

			$this->test_badge = array(
				'label' => __( 'Translations', 'translation-tools' ),
				'color' => 'wp-polyglots-pink',
			);

		}


		/**
		 * Runs the test and returns the result.
		 *
		 * @since 1.3.0
		 *
		 * @return void
		 */
		abstract public function run_test();


		/**
		 * Add a Translation Tools footer text to the bottom of the Site Health test.
		 *
		 * @since 1.3.0
		 *
		 * @return void
		 */
		protected function add_test_footer() {
			$this->test_actions .= sprintf(
				'%s%s%s',
				'<p style="font-size: 12px; color: rgba(195, 34, 131, 0.8); text-align: right">',
				/* translators: Plugin name, do not translate. */
				esc_html__( 'Translation Tools', 'translation-tools' ),
				'</p>'
			);
		}

	}

}
