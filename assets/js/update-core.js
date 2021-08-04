jQuery( document ).ready( function( $ ) {
	console.log( 'Loaded update-core.js' );

	// Load Ajax on each loading div.
	$( 'div.translation-tools-loading.update-core' ).each( function() {
		ttoolsWordPressCoreLoadAjax();
	} );

	/**
	 * Load WordPress core translations.
	 *
	 * @since 1.0.0
	 */
	function ttoolsWordPressCoreLoadAjax() {
		$.ajax( {

			url: ttools.ajaxurl,
			type: 'GET',
			data: {
				action: 'update_core_content_load',
			},
			beforeSend: function() {
				console.log( 'Start WordPress translation update.' );
			},

		} ).done( function( ttoolsResponse ) {
			$( 'div.translation-tools-loading.update-core' ).removeClass( 'notice notice-warning notice-alt inline update-message updating-message' );
			$( 'div.translation-tools-loading.update-core' ).html( ttoolsResponse );

			console.log( 'End WordPress translation update.' );
		} ).fail( function() {
			console.log( 'Translation Tools Ajax Error.' );
		} );
	}
} );
