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
 * @package Translation_Tools
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
		 * Force check the test and update the Transient.
		 *
		 * @var bool
		 */
		protected $force_check = true;

		/**
		 * The required dependency test and status to enable the current Test.
		 * Based on https://developer.wordpress.org/reference/classes/wp_site_health/perform_test
		 *
		 * @var array $required_test {
		 *     Array containing the required test and statuses.
		 *
		 *     @type string $test     The name of test required to run previously.
		 *     @type string $status   The status of the test, which can be a value of `good`, `recommended` or `critical`.
		 * }
		 */
		protected $required_test = array(
			'test'   => '',
			'status' => '',
		);

		/**
		 * Get WordPress install major version ( e.g.: '5.8' ).
		 *
		 * @var string
		 */
		protected $wp_major_version = null;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Add new Site Health test.
			add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );

			$this->wp_major_version = Translations_API::major_version( get_bloginfo( 'version' ) );
		}


		/**
		 * Add new Site Health test.
		 *
		 * @since 1.3.0
		 * @since 1.4.0   Add support for required parent Tests.
		 *
		 * @param array $tests   Tests array.
		 *
		 * @return array   Return filtered tests.
		 */
		public function add_site_health_test( $tests ) {

			// Get required test and status.
			$required_test = $this->get_required_test();

			$required_test_id = $required_test['test'];

			// Check if exist a required Test.
			if ( empty( $required_test_id ) ) { // Do if a previous Test is not required.

				$tests = $this->add_test( $tests );

			} elseif ( array_key_exists( $required_test_id, $tests['direct'] ) ) { // Do if a previous Test is not required and exist in Tests array.

				$required_status = $required_test['status'];
				$current_status  = $tests['direct'][ $required_test_id ]['test'][0]->test_status;

				// Check if exist a required Test status.
				if ( empty( $required_status ) ) { // Do if previous Test status is not required.

					$tests = $this->add_test( $tests );

				} elseif ( $required_status === $current_status ) { // Do if previous Test status matches the required status.

					$tests = $this->add_test( $tests );

				}
			}

			return $tests;
		}


		/**
		 * Add new Site Health test.
		 *
		 * @since 1.4.0
		 *
		 * @param array $tests   Tests array.
		 *
		 * @return array   Return filtered tests.
		 */
		public function add_test( $tests ) {

			// Set the default values for Translation Tools Site Health tests.
			$this->test_defaults();

			// Run the test, force check.
			$this->run_test();

			$tests['direct'][ $this->test_id ] = array(
				'test' => array( $this, 'get_test_result' ),
			);

			return $tests;
		}


		/**
		 * The result of the Test as an array, with defaults, r.
		 * Get defaults, run the test add footer.
		 *
		 * @since 1.4.0
		 *
		 * @return array   The complete Site Health test result.
		 */
		public function get_test_result() {

			// Set the default values for Translation Tools Site Health tests.
			$this->test_defaults();

			// Run the test, don't force check to avoid second HTTP request on the callback run.
			$this->force_check = false;
			$this->run_test();

			// Add text footer.
			$this->add_test_footer();

			// Set test result.
			$result = array(
				'label'       => $this->test_label,
				'status'      => $this->test_status,
				'badge'       => $this->test_badge,
				'description' => $this->test_description,
				'actions'     => $this->test_actions,
				'test'        => $this->test_id,
				'require'     => $this->get_required_test(),
			);

			return $result;
		}


		/**
		 * The default values for Translation Tools Site Health tests.
		 *
		 * @since 1.4.0
		 *
		 * @return void
		 */
		public function test_defaults() {

			// Set default Test values.
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
		 * Color setting from add_site_health_style().
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


		/**
		 * Get the required Test and ensure usable values are set.
		 *
		 * @since 1.4.0
		 *
		 * @return array   The proper formatted required test.
		 */
		protected function get_required_test() {

			// Check if Test parent is set.
			if ( ! is_array( $this->required_test ) ) {
				$this->required_test = array();
			}

			// Check if Test parent ID is set.
			if ( empty( $this->required_test['test'] ) ) {
				$this->required_test['test'] = '';
			}

			// Check if Test parent status is set.
			if ( empty( $this->required_test['status'] ) ) {
				$this->required_test['status'] = '';
			}

			return $this->required_test;
		}
	}

}
