<?php
/**
 * Class file for the Translation Tools Update Core.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TTools_Update_Core' ) ) {

	/**
	 * Class TTools_Update_Core.
	 */
	class TTools_Update_Core {


		/**
		 * Globals.
		 *
		 * @var object
		 */
		protected $globals;

		/**
		 * Notices.
		 *
		 * @var object
		 */
		protected $notices;

		/**
		 * Translations API.
		 *
		 * @var object
		 */
		protected $translations_api;

		/**
		 * Update Translations.
		 *
		 * @var object
		 */
		protected $update_translations;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Tools Globals.
			$this->globals = new TTools_Globals();

			// Instantiate Translation Tools Notices.
			$this->notices = new TTools_Notices();

			// Instantiate Translation Tools Translations API.
			$this->translations_api = new TTools_Translations_API();

			// Instantiate Translation Tools Update Translations.
			$this->update_translations = new TTools_Update_Translations();

			// Add WordPress translation info and update button to updates page.
			add_action( 'core_upgrade_preamble', array( $this, 'updates_wp_translation_notice' ) );

			// Load WordPress translation updater.
			add_action( 'wp_ajax_update_core_content_load', array( $this, 'update_core_content_load' ) );

			// Filter 'update_core' transient to prevent update of previous WordPress version language pack.
			add_filter( 'pre_set_site_transient_update_core', array( $this, 'remove_previous_wp_translation' ) );

		}


		/**
		 * Add WordPress core info and update button on the Updates page bottom.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function updates_wp_translation_notice() {

			// End if Translation Tools locale is 'en_US'.
			if ( $this->globals->locale_is_english() ) {
				// Do nothing.
				return;
			}

			// Check if user can update languages.
			if ( ! current_user_can( 'update_languages' ) ) {
				// Do nothing.
				return;
			}

			// Get core update transient data.
			$update_core = get_site_transient( 'update_core' );
			// Check if there is a core translation available to autoupdate.
			if ( isset( $update_core->translations[0]['autoupdate'] ) ) {
				// Do nothing.
				return;
			}

			// Get Translation Tools Locale data.
			$locale = $this->translations_api->locale( get_locale() );

			// Check if $locale is set.
			if ( empty( $locale ) ) {
				// Do nothing.
				return;
			}
			?>

			<div class="translation-tools-update-core-info">

				<?php
				// Add form with action button to update WordPress core translation.
				echo wp_kses( $this->form_update_wordpress_translation(), $this->globals->allowed_html() );

				// Show the Translation Tools admin notice for WordPress core translation status.
				$notice_args = array();
				$this->updates_wp_translation_notice_message( $notice_args );
				?>

			</div>

			<?php

			if ( ! isset( $_GET['ttools'] ) ) { // phpcs:ignore
				return;
			}

			$url_var = $_GET['ttools']; // phpcs:ignore

			// Check for correct URL parameter.
			if ( 'force_update_core' !== $url_var ) {
				return;
			}

			$this->update_core_content();

		}


		/**
		 * Add form with action button to update WordPress core translation.
		 *
		 * @since 1.0.0
		 *
		 * @return string  HTML of button to update core translation.
		 */
		public function form_update_wordpress_translation() {

			// Show force update WordPress translation button.
			$form_action = 'update-core.php?ttools=force_update_core';
			ob_start();
			?>

			<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-wordpress-translation" class="upgrade">
				<?php wp_nonce_field( 'upgrade-wordpress-translation' ); ?>
				<p>
					<input type="submit" name="force_update_core" class="button button-primary" value="<?php esc_attr_e( 'Update WordPress Translation', 'translation-tools' ); ?>">
				</p>
			</form>

			<?php
			$form = ob_get_clean();
			// Check that string isn't empty.
			if ( $form ) {
				return $form;
			}
			return '';
		}


		/**
		 * WordPress updates translation info message.
		 *
		 * @since 1.0.0
		 *
		 * @param array $notice_args  Arguments for admin notice.
		 *
		 * @return void
		 */
		public function updates_wp_translation_notice_message( $notice_args ) {

			// Get Translation Tools Locale data.
			$locale = $this->translations_api->locale( get_locale() );

			// Get WordPress core version info.
			$wp_version = $this->translations_api->get_wordpress_version();

			// Get available translations transient data.
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			$available_translations = wp_get_available_translations();

			// Initialize variable.
			$translations_date = '';

			// Check for translations update in core update data.
			if ( isset( $available_translations[ $locale->wp_locale ]['updated'] ) ) {
				// Get language pack creation date.
				$translations_date = $available_translations[ $locale->wp_locale ]['updated'];
			}

			$notice_type           = 'info';
			$notice_message_status = sprintf(
				wp_kses_post(
					/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
					__( 'The translation <em>language pack</em> of WordPress %1$s for %2$s was updated on %3$s.', 'translation-tools' )
				),
				'<strong>' . esc_html( $wp_version['name'] ) . '</strong>',
				'<strong>' . esc_html( $locale->native_name ) . '</strong>',
				'<code>' . esc_html( $translations_date ) . '</code>'
			);

			$notice_message_forceupdate = sprintf(
				/* translators: %s: Button label. */
				esc_html__( 'Click the %s button to force update the latest approved translations.', 'translation-tools' ),
				'<strong>&#8220;' . __( 'Update WordPress Translation', 'translation-tools' ) . '&#8221;</strong>'
			);

			// TODO: Check the logic for when there is an update of WordPress and a language with no language pack, and project version link.
			// Check if the current translation exist, if the current translation version is different from the WordPress installed version and is not beta.
			if ( ! isset( $available_translations[ $locale->wp_locale ] ) || ( substr( $available_translations[ $locale->wp_locale ]['version'], 0, 3 ) !== substr( $wp_version['number'], 0, 3 ) && false === strpos( $wp_version['number'], 'beta' ) ) ) {

				$notice_type           = 'warning';
				$notice_message_status = sprintf(
					'%s<br>%s',
					sprintf(
						wp_kses_post(
							/* translators: 1: WordPress version. 2: Locale name. */
							__( 'The translation of WordPress %1$s for %2$s is not complete.', 'translation-tools' )
						),
						'<strong>' . esc_html( $wp_version['name'] ) . '</strong>',
						'<strong>' . esc_html( $locale->native_name ) . '</strong>'
					),
					sprintf(
						wp_kses_post(
							/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. 3: Opening link tag <a href="[link]">. 4: Locale name. */
							__( 'Please register at %1$sTranslating WordPress%2$s and join the %3$sTranslation Team%2$s to help translating WordPress to %4$s!', 'translation-tools' )
						),
						'<a href="https://translate.wordpress.org/locale/' . esc_html( $locale->locale_slug ) . '/wp/' . esc_html( $wp_version['slug'] ) . '/" target="_blank">',
						'</a>',
						'<a href="https://make.wordpress.org/polyglots/teams/?locale=' . esc_attr( $locale->wp_locale ) . '" target="_blank">',
						'<strong>' . esc_html( $locale->native_name ) . '</strong>'
					)
				);

			}

			$admin_notice = array(
				'type'        => $notice_type,
				'inline'      => isset( $notice_args['inline'] ) ? $notice_args['inline'] : null,
				'dismissible' => isset( $notice_args['dismissible'] ) ? $notice_args['dismissible'] : null,
				'message'     => sprintf(
					'%s</p><p>%s',
					$notice_message_status,
					$notice_message_forceupdate
				),
			);
			$this->notices->notice_message( $admin_notice );

		}


		/**
		 * Load WordPress core update loading placeholder.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function update_core_content() {

			$admin_notice = array(
				'type'        => 'warning-spin',
				'notice-alt'  => false,
				'inline'      => false,
				'update-icon' => true,
				'css-class'   => 'translation-tools-loading update-core',
				'message'     => esc_html__( 'The update process is starting. This process may take a while on some hosts, so please be patient.', 'translation-tools' ),
			);
			$this->notices->notice_message( $admin_notice );

		}


		/**
		 * Load WordPress core update content.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function update_core_content_load() {

			$result = array();

			$projects = $this->translations_api->get_wordpress_subprojects();

			$wp_locale = get_locale();

			$project_count = 0;

			WP_Filesystem();
			global $wp_filesystem;
			// Destination of translation files.
			$destination = $wp_filesystem->wp_lang_dir();

			foreach ( $projects as $project ) {

				$project_count ++;
				?>

				<h4>
					<?php
					printf(
						/* translators: 1: Translation name. 2: WordPress Locale. 3: Number of the translation. 4: Total number of translations being updated. */
						esc_html__( 'Updating translations for %1$s (%2$s) (%3$d/%4$d)', 'translation-tools' ),
						'<em>' . esc_html( $project['name'] ) . '</em>',
						esc_html( $wp_locale ),
						intval( $project_count ),
						intval( count( $projects ) )
					);
					?>
				</h4>

				<?php
				$result = $this->update_translations->update_translation( $destination, $project, $wp_locale );

				$log_display = is_wp_error( $result['data'] ) ? 'block' : 'none';
				?>

				<div class="update-messages hide-if-js" id="progress-<?php echo intval( $project_count ); ?>" style="display: <?php echo esc_attr( $log_display ); ?>;">
					<p>
						<?php
						foreach ( $result['log'] as $result_log_item ) {
							echo wp_kses_post( $result_log_item ) . '<br>';
						}
						?>
					</p>
				</div>

				<?php
				if ( is_wp_error( $result['data'] ) ) {

					$error_message = $result['data']->get_error_message();
					$admin_notice  = array(
						'type'    => 'error',
						'message' => sprintf(
							/* translators: 1: Title of an update. 2: Error message. */
							esc_html__( 'An error occurred while updating %1$s: %2$s', 'translation-tools' ),
							'<em>' . esc_html( $project['name'] ) . '</em>',
							'<strong>' . esc_html( $error_message ) . '</strong>'
						),
					);
					$this->notices->notice_message( $admin_notice );

				} else {
					?>

					<div class="updated js-update-details" data-update-details="progress-<?php echo intval( $project_count ); ?>">
						<p>
							<?php
							printf(
								/* translators: %s: Project name. */
								esc_html__( '%s updated successfully.', 'translation-tools' ),
								'<em>' . esc_html( $project['name'] ) . '</em>'
							);
							?>
							<button type="button" class="hide-if-no-js button-link js-update-details-toggle" aria-expanded="false"><?php esc_attr_e( 'Show details.', 'translation-tools' ); ?></button>
						</p>
					</div>

					<?php
				}
			}
			?>

			<p>
				<?php
				esc_html_e( 'All updates have been completed.', 'translation-tools' );
				?>
			</p>

			<?php
			wp_die();

		}


		/**
		 * Filter 'update_core' to remove language pack update info of previous WordPress version.
		 *
		 * @since 1.0.0
		 *
		 * @param object $transient  The 'update_core' transient object.
		 *
		 * @return object            The same or a modified version of the transient.
		 */
		public function remove_previous_wp_translation( $transient ) {

			if ( ! empty( $transient->translations ) && isset( $transient->version_checked ) ) {
				if ( $transient->version_checked !== $transient->translations[0]['version'] ) {
					// Empty update info of language pack for previous WordPress version.
					$transient->translations = array();
				}
			}

			return $transient;
		}

	}

}
