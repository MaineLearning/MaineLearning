jQuery(document).ready( function() {
	var j = jQuery;

	j("#whats-new-options").animate({height:'40px'});
	j("form#whats-new-form textarea").animate({height:'50px'});
	j("#aw-whats-new-submit").prop("disabled", false);

	/* Activity update AJAX posting */
	j("input#aw-whats-new-submit").click( function() {
		tinyMCE.triggerSave();
		j('#whats-new_ifr').contents().find('#tinymce').html('');
	});

	/* Message reply AJAX posting */
	j("input#send_reply_button").click( function() {
		tinyMCE.triggerSave();
		j('#whats-new_ifr').contents().find('#tinymce').html('');
	});
});