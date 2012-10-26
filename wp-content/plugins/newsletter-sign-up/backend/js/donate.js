jQuery(document).ready(function() {
	jQuery('#dvk-donate-box').fadeIn();
	
	jQuery('img.dvk-close').click(function() {
		jQuery('#dvk-donate-box').fadeOut();
	});
});