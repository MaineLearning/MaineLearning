jQuery(document).ready( function() {
	var jq = jQuery;
	
	// Make the Read More on the already-rated box have a unique class
	var arm = jq('.already-rated .activity-read-more');
	jq(arm).removeClass('activity-read-more').addClass('already-rated-read-more');
	
	jq('.star').mouseover( function() {
		var num = jq(this).attr('id').substr( 4, jq(this).attr('id').length );
		for ( var i=1; i<=num; i++ )
			jq('#star' + i ).attr( 'src', bpgr.star );
	});
	
	jq('div#review-rating').mouseout( function() {
		for ( var i=1; i<=5; i++ )
			jq('#star' + i ).attr( 'src', bpgr.star_off );
	});
	
	jq('.star').click( function() {
		var num = jq(this).attr('id').substr( 4, jq(this).attr('id').length );
		for ( var i=1; i<=5; i++ )
			jq('#star' + i ).attr( 'src', bpgr.star_off );
		for ( var i=1; i<=num; i++ )
			jq('#star' + i ).attr( 'src', bpgr.star );
	
		jq('.star').unbind( 'mouseover' );
		jq('div#review-rating').unbind( 'mouseout' );
	
		jq('input#rating').attr( 'value', num );
	});
	
	jq('.already-rated-read-more a').live('click', function(event) {
		var target = jq(event.target);
		
		var link_id = target.parent().attr('id').split('-');
		var a_id = link_id[3];

		var a_inner = '.already-rated blockquote p';

		jq(target).addClass('loading');

		jq.post( ajaxurl, {
			action: 'get_single_activity_content',
			'activity_id': a_id
		},
		function(response) {
			jq(a_inner).slideUp(300).html(response).slideDown(300);
		});
		
		return false;
	});
});
