jQuery(document).ready(function($) {

	$('.post-shortcodes').hide();
	$('.footer-shortcodes').hide();
	
	// toggles the slickbox on clicking the noted link Â 
	$('a.post-shortcodes-toggle').click(function() {
		$('.post-shortcodes').toggle();
		return false;
	});

	// toggles the slickbox on clicking the noted link Â 
	$('a.footer-shortcodes-toggle').click(function() {
		$('.footer-shortcodes').toggle();
		return false;
	});

});