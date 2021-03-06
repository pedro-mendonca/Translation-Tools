<?php
/**
 * Class file for the Translation Tools Update Core.
 *
 * @package Translation Tools
 *
 * @since 1.0.0
 */

namespace Translation_Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Update_Core' ) ) {

	/**
	 * Class Update_Core.
	 */
	class Update_Core {


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
			$this->globals = new Globals();

			// Instantiate Translation Tools Notices.
			$this->notices = new Notices();

			// Instantiate Translation Tools Update Translations.
			$this->update_translations = new Update_Translations();

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

			// Check user capability to install languages.
			if ( ! current_user_can( 'install_languages' ) && ! current_user_can( 'update_languages' ) ) {
				return;
			}

			// Get site and user core update Locales.
			$wp_locales = self::core_update_locales();

			// If Locales array is empty, do nothing.
			if ( empty( $wp_locales ) ) {
				return;
			}

			// Get core update transient data.
			$update_core = get_site_transient( 'update_core' );
			// Check if there is a core translation available to autoupdate.
			if ( isset( $update_core->translations[0]['autoupdate'] ) ) {
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
					<input type="submit" name="force_update_core" class="button button-primary" value="<?php esc_attr_e( 'Update WordPress Translations', 'translation-tools' ); ?>">
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

			// Get site and user core update Locales.
			$wp_locales = self::core_update_locales();

			$locales = array();

			foreach ( $wp_locales as $wp_locale ) {
				// Get Locale data.
				$locales[] = Translations_API::locale( $wp_locale );
			}

			// Get WordPress major version ( e.g.: '5.5' ).
			$wp_version = Translations_API::major_version( get_bloginfo( 'version' ) );

			// Get WordPress core translation project.
			$translation_project = Translations_API::get_core_translation_project();

			$notice_messages = array();

			foreach ( $locales as $locale ) {

				$translation_version = Translations_API::major_version( $locale->translations['version'] );

				// Set language name to 'native_name'.
				$formated_name = Options_General::locale_name_format( $locale );

				// Check if Language Packs exist for the Locale and if the Language Pack major version is the same as the WordPress installed major version.
				if ( isset( $locale->translations ) && $wp_version === $translation_version ) {

					$notice_messages[] = sprintf(
						wp_kses_post(
							/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
							__( 'The translation of WordPress %1$s for %2$s was updated on %3$s.', 'translation-tools' )
						),
						'<strong>' . esc_html( $translation_project->name ) . '</strong>',
						'<strong>' . esc_html( $formated_name ) . '</strong>',
						'<code>' . esc_html( $locale->translations['updated'] ) . '</code>'
					);

				} else {

					$notice_messages[] = sprintf(
						'%s %s',
						sprintf(
							wp_kses_post(
								/* translators: 1: WordPress version. 2: Locale name. */
								__( 'The translation of WordPress %1$s for %2$s is not complete.', 'translation-tools' )
							),
							'<strong>' . esc_html( $translation_project->name ) . '</strong>',
							'<strong>' . esc_html( $formated_name ) . '</strong>'
						),
						sprintf(
							wp_kses_post(
								/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. 3: Opening link tag <a href="[link]">. 4: Locale name. */
								__( 'Please register at %1$sTranslating WordPress%2$s and join the %3$sTranslation Team%2$s to help translating WordPress to %4$s!', 'translation-tools' )
							),
							'<a href="https://translate.wordpress.org/locale/' . esc_html( $locale->locale_slug ) . '/' . esc_html( $translation_project->path ) . '/" target="_blank">',
							sprintf(
								'<span class="screen-reader-text">%s</span></a>',
								/* translators: Accessibility text. */
								esc_html__( '(opens in a new tab)', 'translation-tools' )
							),
							'<a href="https://make.wordpress.org/polyglots/teams/?locale=' . esc_attr( $locale->wp_locale ) . '" target="_blank">',
							'<strong>' . esc_html( $formated_name ) . '</strong>'
						)
					);

				}
			}

			$notice_messages[] = '<br>' . sprintf(
				/* translators: %s: Button label. */
				esc_html__( 'Click the %s button to force update the latest approved translations.', 'translation-tools' ),
				'<strong>&#8220;' . __( 'Update WordPress Translations', 'translation-tools' ) . '&#8221;</strong>'
			);

			$count   = 0;
			$message = '';
			foreach ( $notice_messages as $notice_message ) {
				$count++;
				$wrap_start = 1 === $count ? '' : '<p>';
				$wrap_end   = count( $notice_messages ) !== $count ? '</p>' : '';
				$message   .= $wrap_start . $notice_message . $wrap_end;
			}

			$admin_notice = array(
				'type'        => 'info',
				'inline'      => isset( $notice_args['inline'] ) ? $notice_args['inline'] : null,
				'dismissible' => isset( $notice_args['dismissible'] ) ? $notice_args['dismissible'] : null,
				'message'     => $message,
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

			// Check user capability to install languages.
			if ( ! current_user_can( 'install_languages' ) ) {
				return;
			}

			// Get site and user core update Locales.
			$wp_locales = self::core_update_locales();

			// If Locales array is empty, do nothing.
			if ( empty( $wp_locales ) ) {
				return;
			}

			foreach ( $wp_locales as $wp_locale ) {
				// Get Locale data.
				$locale = Translations_API::locale( $wp_locale );

				// Get the formated Locale name.
				$formated_name = Options_General::locale_name_format( $locale );

				$update_locales[] = $formated_name;
			}

			$admin_notice = array(
				'type'        => 'warning-spin',
				'notice-alt'  => false,
				'inline'      => false,
				'update-icon' => true,
				'css-class'   => 'translation-tools-loading update-core',
				'message'     => sprintf(
					'%s %s',
					esc_html__( 'The update process is starting. This process may take a while on some hosts, so please be patient.', 'translation-tools' ),
					wp_sprintf(
						/* translators: %l: Coma separated list of Locales. */
						__( 'Downloading translations for <strong>%l</strong>.', 'translation-tools' ),
						$update_locales
					)
				),
			);
			$this->notices->notice_message( $admin_notice );

		}


		/**
		 * Set the Locales for WordPress core translations update.
		 *
		 * @since 1.2.0
		 *
		 * @return array   Filtered array of Locales.
		 */
		public static function core_update_locales() {

			$wp_locales = array();

			// Add User Locale, fallsback to Site Locale.
			if ( 'en_US' !== get_user_locale() ) {
				$wp_locales[] = get_user_locale();
			}

			// Add Site Locale.
			if ( 'en_US' !== get_locale() ) {
				$wp_locales[] = get_locale();
			}

			/**
			 * Filters the list of WordPress core locales to update.
			 *
			 * @since 1.2.0
			 *
			 * @param array $wp_locales   An array of the selected Locales to update.
			 *
			 * @return array   A filtered array of Locales.
			 */
			$wp_locales = apply_filters( 'translation_tools_core_update_locales', $wp_locales );

			// Remove duplicates.
			$wp_locales = array_unique( $wp_locales );

			// Remove 'en_US' from the locales to update.
			$key = array_search( 'en_US', $wp_locales, true );
			if ( false !== $key ) {
				unset( $wp_locales[ $key ] );
			}

			sort( $wp_locales );

			return $wp_locales;
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

			$projects = Translations_API::get_wordpress_subprojects();

			$wp_locales = self::core_update_locales();

			WP_Filesystem();
			global $wp_filesystem;
			// Destination of translation files.
			$destination = $wp_filesystem->wp_lang_dir();

			// Loop all the installed Locales.
			foreach ( $wp_locales as $wp_locale ) {

				$project_count = 0;

				// Loop all the WordPress core sub-projects.
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
