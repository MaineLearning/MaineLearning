jQuery(document).ready( function() {
	/** Add tree class to groups panel for AJAX loading */
	jQuery('div.groups').addClass('tree');
	jQuery('.item-subitem-indicator a').live('click',function(event) {

		if( jq(this).html() == '[-]' && jq(this).parents('li').has('div.subitem').length ) {
			jq(this).parent().parent().children('div.subitem').remove();
			jq(this).html('[+]');
			return false;
		}
		
		if(jq(this).html() != '[+]')
			return false;
		
		/** From BuddyPress global.js - modified for subitem loading */
		if ( jQuery(this).hasClass('no-ajax') )
			return;

		/** Find the parent list item of the selected link - this has the scope name in its ID */
		var target = jQuery(event.target).parents('li');
		target = target[0];
		
		if ( jq(target).is('li') ) {
			var css_id = jq(target).attr('id').split( '-' );
			
			/** This is "tree" - we create an AJAX hook for this and use it to build the list we want */
			var object = css_id[0];

			if ( 'activity' == object )
				return false;

			/** This is "childof_{ID}" */
			var scope = css_id[1];
			var filter = jq("#" + object + "-order-select select").val();
			var search_terms = jq("#" + object + "_search").val();
			
			target = jq('<div />').appendTo(target);
			target.addClass('subitem');

			bp_filter_request( object, filter, scope, target, search_terms, 1, jq.cookie('bp-' + object + '-extras') );
		}

		jq(this).html('[-]');
		return false;
	});
});
