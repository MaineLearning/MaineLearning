//Written by Ronald Huereca
//Last updated August 24th, 2011
jQuery(document).ready(function() {
	var $ = jQuery;
	$( '#ajax-submit' ).submit( function() {
		$.ajaxSetup({async:false});
		$( "#status-bar-container, #status-message" ).show();
		var nonce = $( '#_ajax_nonce' ).val();
		var zip_id = $( '#zip_id' ).val();
		jQuery( '#add-site' ).attr( 'disabled', 'disabled' )
		/**** - STEP 1 - Download WordPress ****/
		$.post( ajaxurl, { action: 'export_ms_site', _ajax_nonce: nonce, step: 1, zip_id: zip_id },
		function( response ) {
			jQuery.bbms_export.update_status( response );
			
			
		/**** - STEP 2 - Extract WordPress to TMP Directory****/
			$.post( ajaxurl, { action: 'export_ms_site', _ajax_nonce: nonce, step: 2, wp_file: response.wp_file, zip_id: zip_id }, 
			function( response ) {
				jQuery.bbms_export.update_status( response );
				
				
		/**** - STEP 3 - Create wp-config file****/
				$.post( ajaxurl, { action: 'export_ms_site', _ajax_nonce: nonce, step: 3, zip_id: zip_id }, 
				function( response ) {
					jQuery.bbms_export.update_status( response );
					
					
		/**** - Step 4 - Copy Over Plugins ****/
					$.post( ajaxurl, { action: 'export_ms_site', _ajax_nonce: nonce, step: 4, zip_id: zip_id }, 
					function( response) {
						jQuery.bbms_export.update_status( response );
						
						
		/**** - Step 5 - Copy over themes ****/
						$.post( ajaxurl, { action: 'export_ms_site', _ajax_nonce: nonce, step: 5, zip_id: zip_id },
						function( response ) {
							jQuery.bbms_export.update_status( response );
							
							
		/**** - Step 6 - Copy over Media ****/
							$.post( ajaxurl, { action: 'export_ms_site', _ajax_nonce: nonce, step: 6, zip_id: zip_id }, 
							function( response ) {
								jQuery.bbms_export.update_status( response );
		/**** - Step 7 - Prepare Database Tables***/
								$.post( ajaxurl, { action: 'export_ms_site', _ajax_nonce: nonce, step: 7, zip_id: zip_id }, 
								function( response ) {
									jQuery.bbms_export.update_status( response );
		/**** - Step 8 - Create ZIP FILE***/
										$.post( ajaxurl, {action: 'export_ms_site', _ajax_nonce: nonce, step: 8, zip_id: zip_id }, 
										function( response ) {
											jQuery.bbms_export.update_status( response );
										}, 'json');
								}, 'json' );
							}, 'json' );
						}, 'json' );
					}, 'json' );
				}, 'json' );
			}, 'json'); 
		}, 'json' );
		
		return false;
	} ); //end export start
	
	$.bbms_export = {
		update_status: function( response ) {  
			$( '#status-bar' ).css( 'width', response.completion + '%' );
			$( '#status-message p strong' ).html( response.message );
			console.log( response );
		}
	}; //end .bbms_export
});