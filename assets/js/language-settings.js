/* global document, translationTools */

jQuery( document ).ready( function( $ ) {
	console.log( 'Loaded language-settings.js' );

	console.log( 'Current screen is "' + translationTools.current_screen + '"' );

	console.log( 'Compatible plugins installed: ' + JSON.stringify( translationTools.compatible_plugins ) );

	// Detect if plugin Preferred Languages is active.
	if ( 'preferred-languages/preferred-languages.php' in translationTools.compatible_plugins ) {
		console.log( 'Plugin Preferred Languages detected.' );
		// Load plugin Preferred Languages specific scripts.
		ttoolsPluginPreferredLanguagesSettings();
	} else {
		console.log( 'Plugin Preferred Languages not detected.' );
		// Load Translation Tools default scripts.
		ttoolsSettings();
	}

	// Detect if plugin Translation Stats is active.
	if ( 'translation-stats/translation-stats.php' in translationTools.compatible_plugins ) {
		console.log( 'Plugin Translation Stats detected.' );
		// Load plugin Preferred Languages specific scripts.
		ttoolsPluginTranslationStatsSettings();
	} else {
		console.log( 'Plugin Translation Stats not detected.' );
	}

	/**
	 * Load Translation Tools default scripts.
	 *
	 * @since 1.2.0
	 * @since 1.6.0   Fixed compatibility with WP < 6.2 user language dropdown without Installed/Available language groups.
	 */
	function ttoolsSettings() {
		// Select field ID.
		var selectID = '';

		// Select groups of Installed and Available options. Used always on Site Language, and since WP 6.2 on User Language.
		var selectInstalledGroup = '';
		var selectAvailableGroup = '';
		if ( translationTools.current_screen === 'options-general' || translationTools.wp_version >= '6.2' ) {
			selectInstalledGroup = ' > optgroup:eq(0)';
			selectAvailableGroup = ' > optgroup:eq(1)';
		}

		switch ( translationTools.current_screen ) {
			case 'options-general':
				selectID = '.options-general-php select#WPLANG';
				break;

			case 'profile':
				selectID = '.profile-php select#locale';
				break;

			default:
				return;
		}

		// Add all Locales to the available languages list.
		ttoolsAddAllLocales( selectID );

		// Check each option of installed languages on the language select.
		$( selectID + selectInstalledGroup + ' > option' ).each( function() {
			var value = $( this ).prop( 'value' );

			// Check if the Locale should be on the Installed languages group.
			if ( ! translationTools.available_languages.includes( value ) && '' !== value && 'site-default' !== value ) {
				// Remove Locales that are not installed.
				ttoolsRemoveLocaleOption( selectID + selectInstalledGroup, value );
			} else if ( translationTools.current_screen === 'options-general' || translationTools.wp_version >= '6.2' ) {
				// Remove Locales that are not installed.
				ttoolsRemoveLocaleOption( selectID + selectAvailableGroup, value );
			}
		} );

		// Format all the language names in the list.
		if ( selectInstalledGroup === '' && selectAvailableGroup === '' ) {
			$( selectID + ' > option' ).each( function() {
				var value = $( this ).prop( 'value' );

				// Rename Locale and add attributes.
				ttoolsRenameLocaleOption( selectID, value );
			} );
		} else {
			$( selectID + ' > optgroup > option' ).each( function() {
				var value = $( this ).prop( 'value' );

				// Rename Locale and add attributes.
				ttoolsRenameLocaleOption( selectID + ' > optgroup', value );
			} );
		}

		// Relocate Site Language description data on Settings page.
		ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', selectID );
	}

	/**
	 * Load plugin Preferred Languages specific scripts.
	 *
	 * @since 1.2.0
	 */
	function ttoolsPluginPreferredLanguagesSettings() {
		var selectID = '#preferred-languages-root div.preferred-languages ul.active-locales-list';

		// Check each list item of selected languages on language select.
		// TODO: Check where to hack this in PL.
		$( selectID + ' > li' ).each( function() {
			var value = $( this ).prop( 'id' );

			// Don't rename 'en_US' language.
			if ( 'en_US' !== value ) {
				// Rename Locale and add attributes.
				ttoolsPluginPreferredLanguagesRenameActiveLanguage( selectID, value );
			}
		} );

		// Relocate Site Language description data on General Settings page.
		ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', '#preferred-languages-root div.preferred-languages div.inactive-locales' );
	}

	/**
	 * Load plugin Translation Stats specific scripts.
	 *
	 * @since 1.2.3
	 */
	function ttoolsPluginTranslationStatsSettings() {
		// Select field ID.
		var selectID = '';

		// Plugin file.
		var pluginFile = 'translation-stats/translation-stats.php';

		// Translation Stats language.
		var translationStatsLanguage = translationTools.compatible_plugins[ pluginFile ].settings.translation_language;

		// Add all Locales to the available languages list.
		ttoolsAddAllLocales( 'select#tstats_settings\\[settings\\]\\[translation_language\\]' );

		// Check each option of installed languages on General Settings language select.
		$( 'select#tstats_settings\\[settings\\]\\[translation_language\\] > optgroup:eq(0) > option' ).each( function() {
			var value = $( this ).prop( 'value' );
			selectID = 'select#tstats_settings\\[settings\\]\\[translation_language\\] > optgroup:eq(0)';

			// Check if the Locale should be on the Installed languages group.
			if ( ! translationTools.available_languages.includes( value ) && 'site-default' !== value && translationStatsLanguage !== value ) {
				// Remove Locales that are not installed.
				ttoolsRemoveLocaleOption( selectID, value );
			} else {
				// Rename Locale and add attributes.
				ttoolsRenameLocaleOption( selectID, value );
			}
		} );

		// Check each option of available languages on language select.
		$( 'select#tstats_settings\\[settings\\]\\[translation_language\\] > optgroup:eq(1) > option' ).each( function() {
			var value = $( this ).prop( 'value' );
			selectID = 'select#tstats_settings\\[settings\\]\\[translation_language\\] > optgroup:eq(1)';

			// Rename Locale and add attributes.
			ttoolsRenameLocaleOption( selectID, value );
		} );
	}

	/**
	 * Relocate description data and show.
	 *
	 * @since 1.1.0
	 * @param {string} origin - ID of the source to relocate.
	 * @param {string} target - ID of the target where to relocate after.
	 */
	function ttoolsRelocateAfterTarget( origin, target ) {
		// Translation Tools relocate after target ID.
		$( origin ).insertAfter( $( target ) );

		// Show item.
		$( origin ).show();

		console.log( 'Translation Tools Site Language description relocated.' );
	}

	/**
	 * Remove Locale option from the available languages installed group.
	 *
	 * @since 1.1.0
	 * @param {string} selectID - Select field ID.
	 * @param {string} value    - Option value.
	 */
	function ttoolsRemoveLocaleOption( selectID, value ) {
		// Remove option.
		$( selectID + ' > option[value="' + value + '"]' ).remove();

		console.log( 'Remove Locale option from the available languages: ' + value );
	}

	/**
	 * Set Locale name and option attributes for a target Select field.
	 *
	 * @since 1.1.0
	 * @since 1.2.0  Add option attributes.
	 * @param {string} selectID - Select field ID.
	 * @param {string} value    - Option value.
	 */
	function ttoolsRenameLocaleOption( selectID, value ) {
		// Get language data.
		var language = translationTools.all_languages[ value ];

		// Rename Locales except 'en_US' (with empty value) and 'site-default'.
		if ( '' !== value && 'site-default' !== value ) {
			// Set option name and attributes.
			$( selectID + ' > option[value="' + value + '"]' ).text( language.name ).attr( 'lang', language.lang ).attr( 'data-has-lang-packs', language.lang_packs );

			console.log( 'Rename Locale option from "' + language.value + '" to "' + language.name + '"' );
		}
	}

	/**
	 * Rename Locales that have only the value as name from Preferred Languages plugin unordered list items.
	 *
	 * @since 1.2.0
	 * @since 1.6.0   Renamed from ttoolsRenameLocaleListItem() to ttoolsPluginPreferredLanguagesRenameActiveLanguage().
	 * @param {string} selectID - Select field ID.
	 * @param {string} value    - Option value.
	 */
	function ttoolsPluginPreferredLanguagesRenameActiveLanguage( selectID, value ) {
		// Get language data.
		var language = translationTools.all_languages[ value ];

		// Set option name and attributes.
		$( selectID + ' > li#' + value ).text( language.name ).attr( 'lang', language.lang ).attr( 'data-has-lang-packs', language.lang_packs );

		console.log( 'Rename Preferred Languages Locale list item from "' + language.value + '" to "' + language.name + '"' );
	}

	/**
	 * Add all Locales to the available languages list.
	 *
	 * @since 1.2.0
	 * @param {string} selectID - Select field ID.
	 */
	function ttoolsAddAllLocales( selectID ) {
		// Get all languages.
		var languages = translationTools.all_languages;

		// Create options.
		var options = '';

		Object.values( languages ).forEach( function( language ) {
			options += '<option value="' + language.value + '">' + language.value + '</option>';
		} );

		// Set available languages list.
		$( selectID + ' > optgroup:eq(1)' ).html( options );

		console.log( 'Add Locales to the available languages list.' );

		console.log( 'Total Locales: ' + Object.keys( translationTools.all_languages ).length );
	}
} );
