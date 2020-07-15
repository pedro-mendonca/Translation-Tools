jQuery( document ).ready( function( $ ) {
	console.log( 'Loaded ttools-options-general.js' );

	// Relocate Site Language description data on General Settings page.
	ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', '.options-general-php select#WPLANG' );

	// Relocate Site Language description data on Profile page.
	ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', '.profile-php select#locale' );

	// Relocate Site Language description data on User Edit page.
	ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', '.user-edit-php select#locale' );

	// Check each option of installed languages on General Settings language select.
	$( '#WPLANG > optgroup:eq(0) > option' ).each( function() {
		// Add all Locales to the available languages list.
		ttoolsAddAllLocales();

		var value = $( this ).prop( 'value' );
		var selectID = '.options-general-php select#WPLANG > optgroup:eq(0)';

		// Check if the Locale should be on the Installed languages group.
		if ( ! ttools.available_languages.includes( value ) && '' !== value ) {
			// Remove Locales that are not installed.
			ttoolsRemoveLocaleOption( selectID, value );
		} else {
			// Rename Locale and add attributes.
			ttoolsRenameLocaleOption( selectID, value );
		}
	} );

	// Check each option of available languages on General Settings language select.
	$( '#WPLANG > optgroup:eq(1) > option' ).each( function() {
		var value = $( this ).prop( 'value' );
		var selectID = '.options-general-php select#WPLANG > optgroup:eq(1)';

		// Rename Locale and add attributes.
		ttoolsRenameLocaleOption( selectID, value );
	} );

	// Check each option of installed languages on Profile and User Edit language select.
	$( '#locale > option' ).each( function() {
		var value = $( this ).prop( 'value' );
		var selectID = 'select#locale';

		// Check if the Locale should be on the Installed languages group.
		if ( ! ttools.available_languages.includes( value ) && '' !== value && 'site-default' !== value ) {
			// Remove Locales that are not installed.
			ttoolsRemoveLocaleOption( selectID, value );
		} else {
			// Rename Locale and add attributes.
			ttoolsRenameLocaleOption( selectID, value );
		}
	} );

	/**
	 * Relocate description data and show.
	 *
	 * @since 1.1.0
	 *
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
	 *
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
	 *
	 * @param {string} selectID - Select field ID.
	 * @param {string} value    - Option value.
	 */
	function ttoolsRenameLocaleOption( selectID, value ) {
		// Rename Locales except 'en_US' (with empty value) and 'site-default'.
		if ( '' !== value && 'site-default' !== value ) {
			// Set Locale name format: "Native name [wp_locale]".
			var language = ttools.all_languages[ value ];

			// Set option name and attributes.
			$( selectID + ' > option[value="' + value + '"]' ).text( language.name ).attr( 'lang', language.lang ).attr( 'data-has-lang-packs', language.lang_packs );

			console.log( 'Rename Locale option from "' + language.value + '" to "' + language.name + '"' );
		}
	}

	/**
	 * Add all Locales the available languages list.
	 *
	 * @since 1.2.0
	 */
	function ttoolsAddAllLocales() {
		// Get all languages.
		var languages = ttools.all_languages;

		// Create options.
		var options = '';

		Object.values( languages ).forEach( function( language ) {
			options += '<option value="' + language.value + '">' + language.value + '</option>';
		} );

		// Set available languages list.
		$( '#WPLANG > optgroup:eq(1)' ).html( options );

		console.log( 'Add Locales to the available languages list.' );

		console.log( 'Total Locales: ' + Object.keys( ttools.all_languages ).length );
	}
} );
