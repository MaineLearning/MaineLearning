jQuery(document).ready(function() {
	jQuery('.pluginbuddy_tip').tooltip({ 
		track: true, 
		delay: 0, 
		showURL: false, 
		showBody: " - ", 
		fade: 250 
	});
	
	
	jQuery('.pb_backupbuddy_remotetest').click(function(e) {
		jQuery(this).html('Testing ...');
		jQuery.post( jQuery(this).attr( 'alt' ), jQuery(this).closest( 'form' ).serialize(), 
			function(data) {
				alert( data );
			}
		); //,"json");
		jQuery(this).html('Test these settings');
		return false;
	});
	
	jQuery('.pluginbuddy_pop').click(function(e) {
		showpopup('#'+jQuery(this).attr('href'),'',e);
		return false;
	});
	
	jQuery('.toggle').click(function(e) {
		jQuery( '#toggle-' + jQuery(this).attr('id') ).slideToggle();
	});
});