jQuery( document ).ready( function( $ ) {
	console.log( 'Loaded ttools-options-general.js' );

	// Set Translation Tools custom languages field.
	var ttoolsCustomLanguage = $( "label[for='TTools_Aditional_Language']" ).parent().parent();

	// Set WordPress previous setting field.
	var anchorField = $( "label[for='timezone_string']" ).parent().parent();

	// Reorder Translation Tools custom languages field before 'anchorField' field.
	ttoolsCustomLanguage.insertBefore( anchorField );

	console.log( 'Translation Tools custom language field reordered.' );
} );
