<?php
/**
 * Class file for the Translation Tools Update Core.
 *
 * @package Translation_Tools
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
		 * Update Translations.
		 *
		 * @var object
		 */
		protected $update_translations;


		/**
		 * Constructor.
		 */
		public function __construct() {

			// Instantiate Translation Tools Update Translations.
			$this->update_translations = new Update_Translations();

			// Add WordPress translations info and WordPress/Plugins/Themes update buttons to update-core page.
			add_action( 'core_upgrade_preamble', array( $this, 'update_core_bottom_section' ) );

			// Add update-core custom update action page.
			add_action( 'update-core-custom_force-translation-upgrade', array( $this, 'action_force_translation_upgrade' ) );

			// Ajax load translations updater section.
			add_action( 'wp_ajax_force_upgrade_translations_section', array( $this, 'force_upgrade_translations_section' ) );

			// Filter 'update_core' transient to prevent update of previous WordPress version language pack.
			add_filter( 'pre_set_site_transient_update_core', array( $this, 'remove_previous_wp_translation' ) );
		}


		/**
		 * Add WordPress core info and update buttons on the Updates page bottom.
		 *
		 * @since 1.0.0
		 * @since 1.5.0   Renamed 'updates_wp_translation_notice' to 'update_core_bottom_section'.
		 *
		 * @return void
		 */
		public function update_core_bottom_section() {

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

				// Show the Translation Tools admin notice for WordPress core translation status and update buttons.
				$this->update_core_bottom_section_content();
				?>

			</div>

			<?php
		}


		/**
		 * Set custom update-core action page.
		 *
		 * @since 1.5.0
		 *
		 * @return void
		 */
		public function action_force_translation_upgrade() {

			// Check user capability to install languages.
			if ( ! current_user_can( 'install_languages' ) ) {
				return;
			}

			// Check nonce.
			check_admin_referer( 'translation-tools-update', 'translation_tools_nonce' );

			// Load admin header.
			require_once ABSPATH . 'wp-admin/admin-header.php';
			?>

			<div class="wrap">

				<h1><?php esc_html_e( 'Update Translations', 'translation-tools' ); ?></h1>

				<?php

				// Get site and user core update Locales.
				$wp_locales = self::core_update_locales();

				// If Locales array is empty, do nothing.
				if ( empty( $wp_locales ) ) {
					return;
				}

				// Define variable.
				$update_locales = array();

				// Format Locales. Don't use $wp_locale because it conflicts with global $wp_locale (WP_Locale object) loaded above on 'wp-admin/admin-header.php'.
				foreach ( $wp_locales as $wordpress_locale ) {
					// Get Locale data.
					$locale = Translations_API::locale( $wordpress_locale );

					// Get the formatted Locale name.
					$formatted_name = Options_General::locale_name_format( $locale );

					$update_locales[] = $formatted_name;
				}

				// Check for which translation to update on POST data. Defaults to WordPress translations for update links on General Settings and User Settings.
				if ( ! isset( $_POST['update-translations'] ) ) { // phpcs:ignore
					$update_translations = 'wp';
				} else {
					$update_translations = $_POST['update-translations']; // phpcs:ignore.
				}

				$types = array(
					'wp'      => esc_html__( 'WordPress translations', 'translation-tools' ),
					'plugins' => esc_html__( 'Plugins translations', 'translation-tools' ),
					'themes'  => esc_html__( 'Themes translations', 'translation-tools' ),
				);

				foreach ( $types as $type => $title ) {

					// Check if type is selected, or 'all'.
					if ( 'all' === $update_translations || $type === $update_translations ) {

						// Show updates section.
						?>
						<div class="translation-tools-section" data-type="<?php echo esc_attr( $type ); ?>">
							<h2><?php echo esc_html( $title ); ?></h2>
							<?php

							$admin_notice = array(
								'type'        => 'warning-spin',
								'notice-alt'  => false,
								'inline'      => true,
								'update-icon' => true,
								'css-class'   => 'translation-tools-loading update-' . $type,
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
							Admin_Notice::message( $admin_notice );
							?>

						</div>
						<?php
					}
				}
				?>

				<p>
					<a href="<?php echo esc_url( self_admin_url( 'update-core.php' ) ); ?>" target="_parent"><?php esc_html_e( 'Go to WordPress Updates page', 'translation-tools' ); ?></a>
				</p>

			</div>

			<?php
			// Load admin footer.
			require_once ABSPATH . 'wp-admin/admin-footer.php';
		}


		/**
		 * WordPress updates translation info message.
		 *
		 * @since 1.0.0
		 * @since 1.5.0   Renamed from 'updates_wp_translation_notice_message' to 'update_core_bottom_section_content'.
		 *                Removed $notice_args.
		 *
		 * @return void
		 */
		public function update_core_bottom_section_content() {

			// Get WordPress major version ( e.g.: '5.5' ).
			$wp_major_version = Translations_API::major_version( get_bloginfo( 'version' ) );

			// Force an update check when requested.
			$force_check = ! empty( $_GET['force-check'] ); // phpcs:ignore

			// Get installed WordPress core translation project.
			$translation_project = Translations_API::get_core_translation_project( $wp_major_version, $force_check );

			$notice_messages   = array();
			$admin_notice_type = 'info';

			// Check if API is available.
			if ( ! is_wp_error( $translation_project['data'] ) ) {

				// Add form with action button to update WordPress, Plugins and Themes translations.
				$this->update_core_bottom_section_form();

				// Get translation project major version.
				$translation_project_version = Translations_API::major_version( $translation_project['data']->name );

				/*
				 * Check if translation project is already available for the installed version.
				 * It's usually available strings hard freeze.
				 */
				if ( $wp_major_version !== $translation_project_version ) {

					$notice_messages[] = sprintf(
						wp_kses_post(
							/* translators: %s: WordPress version. */
							__( 'WordPress %s is not available for translation yet.', 'translation-tools' )
						),
						'<strong>' . esc_html( $wp_major_version ) . '</strong>'
					) . '<br><br>';

					$admin_notice_type = 'warning';

				}

				// Get site and user core update Locales.
				$wp_locales = self::core_update_locales();

				$locales = array();

				foreach ( $wp_locales as $wp_locale ) {
					// Get Locale data.
					$locales[] = Translations_API::locale( $wp_locale );
				}

				foreach ( $locales as $locale ) {

					if ( $locale->has_translations() ) {
						$locale_translations_version = Translations_API::major_version( $locale->translations['version'] );
					}

					// Set language name to 'native_name'.
					$formatted_name = Options_General::locale_name_format( $locale );

					// Check if Language Packs exist for the Locale and if the Language Pack major version is the same as the WordPress installed major version.
					if ( $locale->has_translations() && isset( $locale_translations_version ) && $translation_project_version === $locale_translations_version ) {

						$notice_messages[] = sprintf(
							wp_kses_post(
								/* translators: 1: WordPress version. 2: Locale name. 3: Date the language pack was created. */
								__( 'The translation of WordPress %1$s for %2$s has Language Pack updated on %3$s.', 'translation-tools' )
							),
							'<strong>' . esc_html( $translation_project_version ) . '</strong>',
							'<strong>' . esc_html( $formatted_name ) . '</strong>',
							'<code>' . esc_html( $locale->translations['updated'] ) . '</code>'
						);

					} else {

						$notice_messages[] = sprintf(
							'%s %s',
							sprintf(
								wp_kses_post(
									/* translators: 1: WordPress version. 2: Locale name. */
									__( 'The translation of WordPress %1$s for %2$s has no Language Pack yet.', 'translation-tools' )
								),
								'<strong>' . esc_html( $translation_project_version ) . '</strong>',
								'<strong>' . esc_html( $formatted_name ) . '</strong>'
							),
							sprintf(
								wp_kses_post(
									/* translators: 1: Opening link tag <a href="[link]">. 2: Closing link tag </a>. 3: Opening link tag <a href="[link]">. 4: Locale name. */
									__( 'Please register at %1$sTranslating WordPress%2$s and join the %3$sTranslation Team%2$s to help translating WordPress to %4$s!', 'translation-tools' )
								),
								'<a href="https://translate.wordpress.org/locale/' . esc_html( $locale->locale_slug ) . '/' . esc_html( $translation_project['data']->path ) . '/" target="_blank">',
								sprintf(
									' <span class="screen-reader-text">%s</span><span aria-hidden="true" class="dashicons dashicons-external" style="text-decoration: none;"></span></a>',
									/* translators: Accessibility text. */
									esc_html__( '(opens in a new tab)', 'translation-tools' )
								),
								'<a href="https://make.wordpress.org/polyglots/teams/?locale=' . esc_attr( $locale->wp_locale ) . '" target="_blank">',
								'<strong>' . esc_html( $formatted_name ) . '</strong>'
							)
						);

					}
				}

				// Admin notice footer.
				$notice_messages[] = '<br>' . sprintf(
					/* translators: 1: Button label. 2: WordPress version number. */
					esc_html__( 'Click the &#8220;%1$s&#8221; button to force update the latest approved translations of WordPress %2$s.', 'translation-tools' ),
					'<strong>' . __( 'Update WordPress Translations', 'translation-tools' ) . '</strong>',
					'<strong>' . esc_html( $translation_project_version ) . '</strong>'
				);

			} else {

				// API is not available.
				$error_message     = $translation_project['data']->get_error_message();
				$notice_messages[] = sprintf(
					wp_kses_post(
						/* translators: %s: Error message. */
						__( '<strong>Error:</strong> %s', 'translation-tools' )
					),
					$error_message
				);
				$admin_notice_type = 'error';
				$admin_notice_icon = true;

			}

			// Admin notice render.
			$count   = 0;
			$message = '';
			foreach ( $notice_messages as $notice_message ) {
				++$count;
				$wrap_start = 1 === $count ? '' : '<p>';
				$wrap_end   = count( $notice_messages ) !== $count ? '</p>' : '';
				$message   .= $wrap_start . $notice_message . $wrap_end;
			}

			$admin_notice = array(
				'type'        => $admin_notice_type,
				'update-icon' => isset( $admin_notice_icon ) ? $admin_notice_icon : null,
				'message'     => $message,
			);
			Admin_Notice::message( $admin_notice );
		}


		/**
		 * Add form with action buttons to update WordPress, Plugins and Themes translations.
		 *
		 * @since 1.5.0
		 *
		 * @return void
		 */
		public function update_core_bottom_section_form() {

			// Set Form Action url query arguments.
			$form_action = esc_url_raw(
				add_query_arg(
					array(
						'action' => 'force-translation-upgrade',
					),
					'update-core.php'
				)
			);
			?>

			<style>
			div.translation-tools-update-core-info p:not(:first-child) button {
				margin-right: 0.5em;
				margin-top: 0.5em;
			}
			div.translation-tools-update-core-info p button span.dashicons {
				vertical-align: text-top;
				font-size: medium;
			}
			</style>

			<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="force-update-translations" class="upgrade">
				<?php
				// Add nonce check.
				wp_nonce_field( 'translation-tools-update', 'translation_tools_nonce' );
				?>
				<p>
					<button type="submit" name="update-translations" class="button button-primary" value="all">
						<?php esc_attr_e( 'Update All Translations', 'translation-tools' ); ?>
					</button>
					<button type="submit" name="update-translations" class="button button-secondary" value="wp">
						<span class="dashicons dashicons-wordpress-alt"></span> <?php esc_attr_e( 'Update WordPress Translations', 'translation-tools' ); ?>
					</button>
					<button type="submit" name="update-translations" class="button button-secondary" value="plugins">
						<span class="dashicons dashicons-admin-plugins"></span> <?php esc_attr_e( 'Update Plugins Translations', 'translation-tools' ); ?>
					</button>
					<button type="submit" name="update-translations" class="button button-secondary" value="themes">
						<span class="dashicons dashicons-admin-appearance"></span> <?php esc_attr_e( 'Update Themes Translations', 'translation-tools' ); ?>
					</button>
				</p>
			</form>

			<?php
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
		 * Ajax load translations updater section.
		 *
		 * @since 1.5.0
		 *
		 * @return void
		 */
		public function force_upgrade_translations_section() {

			// Check for GET data.
			if ( ! isset( $_GET['section'] ) ) { // phpcs:ignore
				return;
			}

			// Get section ID.
			$section = $_GET['section']; // phpcs:ignore

			switch ( $section ) {

				case 'wp':
					// Update WordPress translation subprojects.
					$this->force_upgrade_translations_section_content( 'wp', Translations_API::get_wordpress_subprojects() );
					?>

					<p>
						<?php
						esc_html_e( 'WordPress translations updates have been completed.', 'translation-tools' );
						?>
					</p>
					<?php
					break;

				case 'plugins':
					// Update Plugins translation projects.
					$this->force_upgrade_translations_section_content( 'plugins', Translations_API::get_wordpress_plugins() );
					?>

					<p>
						<?php
						esc_html_e( 'Plugins translations updates have been completed.', 'translation-tools' );
						?>
					</p>

					<?php
					break;

				case 'themes':
					// Update Themes translation projects.
					$this->force_upgrade_translations_section_content( 'themes', Translations_API::get_wordpress_themes() );
					?>

					<p>
						<?php
						esc_html_e( 'Themes translations updates have been completed.', 'translation-tools' );
						?>
					</p>
					<?php
					break;

			}
			wp_die();
		}


		/**
		 * Load updating translations section content.
		 *
		 * @since 1.5.0
		 *
		 * @param string $type       Type of translation project ( e.g.: 'wp', 'themes', 'plugins' ).
		 * @param array  $projects   Projects to update.
		 *
		 * @return void
		 */
		public function force_upgrade_translations_section_content( $type, $projects ) {

			$result = array();

			// Get site and user core update Locales.
			$wp_locales = self::core_update_locales();

			// Loop all the installed Locales.
			foreach ( $wp_locales as $wp_locale ) {

				$project_count = 0;

				// Loop all the translation projects.
				foreach ( $projects as $slug => $project ) {

					// Add project key as Slug.
					$project['Slug'] = $slug;

					// Check if custom 'Domain' is set, as for WordPress core. Defaults to project 'Slug'.
					$project['Domain'] = isset( $project['Domain'] ) ? $project['Domain'] : $slug; // Project domain.

					++$project_count;
					?>

					<h4>
						<?php
						printf(
							/* translators: 1: Translation name. 2: WordPress Locale. 3: Number of the translation. 4: Total number of translations being updated. */
							esc_html__( 'Updating translations for %1$s (%2$s) (%3$d/%4$d)', 'translation-tools' ),
							'<em>' . esc_html( $project['Name'] ) . '</em>',
							esc_html( $wp_locale ),
							intval( $project_count ),
							intval( count( $projects ) )
						);
						?>
					</h4>

					<?php
					$result = $this->update_translations->update_translation( $type, $project, $wp_locale, true, true, true );

					$log_display = is_wp_error( $result['data'] ) ? 'block' : 'none';
					?>

					<div class="update-messages hide-if-js" id="progress-<?php echo esc_attr( $type ) . '-' . esc_attr( $wp_locale ) . '-' . intval( $project_count ); ?>" style="display: <?php echo esc_attr( $log_display ); ?>;">
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
								'<em>' . esc_html( $project['Name'] ) . '</em>',
								'<strong>' . esc_html( $error_message ) . '</strong>'
							),
						);
						Admin_Notice::message( $admin_notice );

					} else {
						?>

						<div class="updated js-update-details" data-update-details="progress-<?php echo esc_attr( $type ) . '-' . esc_attr( $wp_locale ) . '-' . intval( $project_count ); ?>">
							<p>
								<?php
								printf(
									/* translators: %s: Project name. */
									esc_html__( '%s updated successfully.', 'translation-tools' ),
									'<em>' . esc_html( $project['Name'] ) . '</em>'
								);
								?>
								<button type="button" class="hide-if-no-js button-link js-update-details-toggle" aria-expanded="false"><?php esc_attr_e( 'More details.', 'translation-tools' ); ?><span class="dashicons dashicons-arrow-down" aria-hidden="true"></span></button>
							</p>
						</div>

						<?php
					}
				}
			}
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
