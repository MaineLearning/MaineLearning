jQuery(document).ready(function() {
	jQuery(window).load(function(){
		if ( jQuery('#pb_importbuddy_working').is(':visible') ) {
			jQuery('#pb_importbuddy_working').replaceWith(
				jQuery('#pb_importbuddy_blankalert').html().replace( '#TITLE#', 'PHP Timeout or Fatal Error Occurred' ).replace( '#MESSAGE#', 'The page did not finish loading as expected.  The most common cause for this is the PHP process taking more time than it has been allowed by your host (php.ini setting <i>max_execution_time</i>). If a PHP error is displayed above this can also cause this error.' )
			);
		}
	});
});

jQuery(document).ready(function() {
	jQuery('.pluginbuddy_tip').tooltip({
		track: true,
		delay: 0,
		showURL: false,
		showBody: " - ",
		fade: 250
	});
	
	jQuery('.toggle').click(function(e) {
		jQuery( '#toggle-' + jQuery(this).attr('id') ).slideToggle();
	});
	
	jQuery('.option_toggle').change(function(e) {
		if (jQuery(this).attr('checked')) {
			jQuery('.' + jQuery(this).attr('id') + '_toggle' ).slideToggle();
		} else {
			jQuery('.' + jQuery(this).attr('id') + '_toggle' ).slideToggle();
		}
	});
	
});