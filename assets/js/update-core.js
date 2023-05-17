/* global document, translationTools */

jQuery( document ).ready( function( $ ) {
	console.log( 'Loaded update-core.js' );

	// Load Ajax on each loading div.
	$( 'div.translation-tools-loading' ).each( function() {
		var type = $( this ).closest( 'div.translation-tools-section' ).attr( 'data-type' );
		ttoolsUpdateTranslations( type );
	} );

	/**
	 * Load section translations.
	 *
	 * @since 1.5.0
	 * @param {string} type Updates section type ID.
	 */
	function ttoolsUpdateTranslations( type ) {
		$.ajax( {

			url: translationTools.ajaxurl,
			type: 'GET',
			data: {
				// Universal action with 'section' passed in $_GET.
				action: 'force_upgrade_translations_section',
				section: type,
			},
			beforeSend: function() {
				console.log( 'Start ' + type + ' section translations update.' );
			},

		} ).done( function( ttoolsResponse ) {
			$( 'div.translation-tools-section[data-type=' + type + '] div.translation-tools-loading' ).removeClass( 'notice notice-warning notice-alt inline update-message updating-message' );
			$( 'div.translation-tools-section[data-type=' + type + '] div.translation-tools-loading' ).html( ttoolsResponse );

			console.log( 'End ' + type + ' section translations update.' );
		} ).fail( function() {
			console.log( 'Translation Tools Ajax Error.' );
		} );
	}
} );
