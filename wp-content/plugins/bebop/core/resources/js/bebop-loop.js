 /*This code below deals with the first load of the activity stream in the OER page,
   it has been directly taken from the global.js buddypress file in the activity section
   and modified due to lack of pratical hooks. Taken from bp_activity_request(scope, filter).*/ 


function bebop_activity_cookie_modify(scope,filter) {
	/* Save the type and filter to a session cookie */
	jq.cookie( 'bp-activity-scope', scope, {path: '/'} );
	jq.cookie( 'bp-activity-filter', filter, {path: '/'} );
	jq.cookie( 'bp-activity-oldestpage', 1, {path: '/'} );

	/* Remove selected and loading classes from tabs */
	jq('div.item-list-tabs li').each( function() {
		jq(this).removeClass('selected loading');
	});
	
	/* Set the correct selected nav and filter */
	jq('li#activity-' + scope + ', div.item-list-tabs li.current').addClass('selected');
	jq('div#object-nav.item-list-tabs li.selected, div.activity-type-tabs li.selected').addClass('loading');
	jq('#activity-filter-select select option[value="' + filter + '"]').prop( 'selected', true );

	/* Reload the activity stream based on the selection */
	jq('.widget_bp_activity_widget h2 span.ajax-loader').show();

	if ( bp_ajax_request )
		bp_ajax_request.abort();

	bp_ajax_request = jq.post( ajaxurl, {
		action: 'activity_widget_filter',
		'cookie': encodeURIComponent(document.cookie),
		'_wpnonce_activity_filter': jq("input#_wpnonce_activity_filter").val(),
		'scope': scope,
		'filter': filter
		},
		function(response) {
			jq('.widget_bp_activity_widget h2 span.ajax-loader').hide();
			jq('div.activity').fadeOut( 0, function() {
			jq(this).html(response.contents);
			jq(this).fadeIn(0);

			/* Selectively hide comments */
			bp_dtheme_hide_comments();
		});

		/* Update the feed link */
		if ( null != response.feed_url )
			jq('.directory div#subnav li.feed a, .home-page div#subnav li.feed a').attr('href', response.feed_url);

		jq('div.item-list-tabs li.selected').removeClass('loading');
	}, 'json' );		
}