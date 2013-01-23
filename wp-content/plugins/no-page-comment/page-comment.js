jQuery(document).ready( function($) {
	// Run WordPress Database Comment Modification
	$('.sta_ajax_modify').click( function(e) {
		e.preventDefault();
		confirmresult = confirm('Are you sure you want to do this? This request cannot be undone.\n\nIt is highly recommended that you backup your database before proceeding.');
		if(confirmresult == true ) {
			nonce = $(this).attr('data-nonce');
			post_type = $(this).attr('data-post_type');
			post_label = $(this).attr('data-post_label');
			comment_type = $(this).attr('data-comment_type');
			comment_status = $(this).attr('data-comment_status');
			$.ajax({
				type : 'post',
				dataType : 'json',
				url : myAjax.ajaxurl,
				data : {
					action: 'sta_npc_mod',
					nonce: nonce,
					post_type: post_type,
					post_label: post_label,
					comment_type: comment_type,
					comment_status: comment_status
				},
				success: function(response) {
					if(response.type == 'success') {
						alert(response.message);
						console.log(response);
					} else {
						alert(response.message);
						console.log(response);
					}
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					console.log(errorThrown);
				}
			});
		}
	});
});