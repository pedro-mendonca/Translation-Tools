jQuery( document ).ready( function( $ ) {
	console.log( 'Loaded ttools-options-general.js' );

	// Relocate Site Language description data on General Settings page.
	ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', '.options-general-php select#WPLANG' );

	// Relocate Site Language description data on Profile page.
	ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', '.profile-php select#locale' );

	// Relocate Site Language description data on User Edit page.
	ttoolsRelocateAfterTarget( 'div#ttools_language_select_description', '.user-edit-php select#locale' );

	// Rename group of 'Available' languages.
	$( '#WPLANG > optgroup:eq(1)' ).attr( 'label', ttools.optgroup_lang_packs_title );

	// Append group of Locales with no Language Packs to the end the available languages list.
	ttoolsAddLocalesGroup();

	// Check each option of installed languages on General Settings language select.
	$( '#WPLANG > optgroup:eq(0) > option' ).each( function() {
		var value = $( this ).prop( 'value' );
		var text = $( this ).prop( 'text' );
		var selectID = '.options-general-php select#WPLANG > optgroup:eq(0)';

		// Check if the Locale should be on the Installed languages group.
		if ( ! ttools.available_languages.includes( value ) && '' !== value ) {
			// Remove Locales that are not installed.
			ttoolsRemoveLocaleOption( selectID, value );
		// Check if Locale don't have Native name.
		} else if ( value === text ) {
			// Rename Locales that have only the value as name.
			ttoolsRenameLocaleOption( selectID, value );
		}
	} );

	// Check each option of installed languages on Profile language select.
	$( '#locale > option' ).each( function() {
		var value = $( this ).prop( 'value' );
		var text = $( this ).prop( 'text' );
		var selectID = '.profile-php select#locale';

		// Check if the Locale should be on the Installed languages group.
		if ( ! ttools.available_languages.includes( value ) && '' !== value && 'site-default' !== value ) {
			// Remove Locales that are not installed.
			ttoolsRemoveLocaleOption( selectID, value );
		// Check if Locale don't have Native name.
		} else if ( value === text ) {
			// Rename Locales that have only the value as name.
			ttoolsRenameLocaleOption( selectID, value );
		}
	} );

	// Check each option of installed languages on User Edit language select.
	$( '#locale > option' ).each( function() {
		var value = $( this ).prop( 'value' );
		var text = $( this ).prop( 'text' );
		var selectID = '.user-edit-php select#locale';

		// Check if the Locale should be on the Installed languages group.
		if ( ! ttools.available_languages.includes( value ) && '' !== value && 'site-default' !== value ) {
			// Remove Locales that are not installed.
			ttoolsRemoveLocaleOption( selectID, value );
		// Check if Locale don't have Native name.
		} else if ( value === text ) {
			// Rename Locales that have only the value as name.
			ttoolsRenameLocaleOption( selectID, value );
		}
	} );

	/**
	 * Append group of Locales with no Language Packs to the end the available languages list.
	 *
	 * @since 1.1.0
	 */
	function ttoolsAddLocalesGroup() {
		// Generate option group of Locales with no Language Packs.
		var optgroup = '<optgroup label="' + ttools.optgroup_no_lang_packs_title + '">';

		Object.keys( ttools.locales_no_lang_packs ).forEach( function( item ) {
			value = ttools.locales_no_lang_packs[ item ].wp_locale;
			name = ttools.locales_no_lang_packs[ item ].native_name;
			if ( ! ttools.available_languages.includes( value ) ) {
				optgroup += '<option value="' + value + '" data-has-lang-packs="false">' + name + ' [' + value + ']' + '</option>';
			}
		} );

		optgroup += '</optgroup>';

		// Append group of Locales with no Language Packs to the end the available languages list.
		$( '#WPLANG' ).append( optgroup );
		console.log( 'Locales appended to the end of the available languages list.' );
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
		console.log( 'Remove Locale from the available languages: ' + value );
	}

	/**
	 * Rename Locales that have only the value as name from a target Select field.
	 *
	 * @since 1.1.0
	 *
	 * @param {string} selectID - Select field ID.
	 * @param {string} value    - Option value.
	 */
	function ttoolsRenameLocaleOption( selectID, value ) {
		// Set Locale name format: "Native name [wp_locale]".
		var localeName = ttools.locales_no_lang_packs[ value ].native_name + ' [' + ttools.locales_no_lang_packs[ value ].wp_locale + ']';

		// Set option name.
		$( selectID + ' > option[value="' + value + '"]' ).text( localeName );
		console.log( 'Option text renamed from "' + value + '" to "' + localeName + '"' );
	}

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
} );
