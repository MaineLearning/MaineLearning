// AJAX Functions

jQuery(document).ready( function() {
	var j = jQuery;

	/**** Page Load Actions **********************/

	/* Link filter and scope set. */
	bp_init_objects( [ 'links' ] );

	/* Clear cookies on logout */
	j('a.logout').click( function() {
		j.cookie('bp-links-scope', null );
		j.cookie('bp-links-filter', null );
		j.cookie('bp-links-extras', null );
	});

	/**** Directory ******************************/

	/* When the category filter select box is changed, re-query */
	j('select#links-category-filter').change( function(e)
	{
		var extras, el_cat = j(this);
		
		if ( el_cat.val().length ) {
			extras = 'category-' + el_cat.val();
		}

		bp_filter_request( 'links', j.cookie('bp-links-filter'), j.cookie('bp-links-scope'), 'div.links', j('#links_search').val(), 1, extras );

		e.preventDefault();
		e.stopPropagation();
	});

	/* When the order  select box is changed, re-query */
	j('select#links-order-by').change( function(e)
	{
		bp_filter_request( 'links', j(this).val(), j.cookie('bp-links-scope'), 'div.links', j('#links_search').val(), 1, j.cookie('bp-links-extras') );

		e.preventDefault();
		e.stopPropagation();
	});
	
	/**** Links Navigation *********************/
	
	j('div#link-dir-pag a').live( 'click', function( e ) {
		// page num is 1 by default
		var page_number = 1;
		// determine *real* page num
		if ( j(this).hasClass('next') ) {
			page_number = Number( j(this).siblings('span.current').html() ) + 1;
		} else if ( j(this).hasClass('prev') ) {
			page_number = Number( j(this).siblings('span.current').html() ) - 1;
		} else {
			page_number = Number( j(this).html() );
		}
		// send ajax request
		bp_filter_request( 'links', j.cookie('bp-links-filter'), j.cookie('bp-links-scope'), 'div.links', j('#links_search').val(), page_number, j.cookie('bp-links-extras') );
		// kill any other events
		e.preventDefault();
		e.stopPropagation();
	});

	/**** Lightbox ****************************/

	j("a.link-play").live('click',
		function() {

			var link = j(this).attr('id')
			link = link.split('-');

			j.post( ajaxurl, {
				action: 'link_lightbox',
				'cookie': encodeURIComponent(document.cookie),
				'link_id': link[2]
			},
			function(response)
			{
				var rs = bpl_split_response(response);

				if ( rs[0] >= 1 ) {
					j.fn.colorbox({
						html: rs[1],
						maxWidth: '90%',
						maxHeight: '90%',
						scalePhotos: false,
						onComplete: function(){
							var children = j('#cboxLoadedContent').children();
							if ( children.length == 1 && children.first().is('img') ) {
								j.colorbox.resize({
									innerHeight: j('#cboxLoadedContent > img').outerHeight() + 18,
									innerWidth: j('#cboxLoadedContent > img').outerWidth() + 18
								});
							}
						}
					});
				}
			});

			return false;
		}
	);

	/**** Voting ******************************/

	j("div.link-vote-panel a.vote").live('click',
		function() {

			bpl_get_loader().toggle();

			var link = j(this).attr('id')
			link = link.split('-');

			j.post( ajaxurl, {
				action: 'link_vote',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': j("input#_wpnonce-link-vote").val(),
				'up_or_down': link[1],
				'link_id': link[2]
			},
			function(response)
			{
				var rs = bpl_split_response(response);

				j("div#link-vote-panel-" + link[2]).fadeOut(200,
					function() {
						bpl_remove_msg();

						if ( rs[0] <= -1 ) {
							bpl_list_item_msg(link[2], 'error', rs[1]);
						} else if ( rs[0] == 0 ) {
							bpl_list_item_msg(link[2], 'updated', rs[1]);
						} else {
							bpl_list_item_msg(link[2], 'updated', rs[1]);
							j("div.link-vote-panel div#vote-total-" + link[2]).html(rs[2]);
							j("div.link-vote-panel span#vote-count-" + link[2]).html(rs[3]);
						}

						j("div#link-vote-panel-" + link[2]).fadeIn(200);
					}
				);

				bpl_get_loader().toggle();
			});

			return false;
		}
	);

});

/*** Helpers **************************************************************/

function bpl_get_loader(id)
{
	var x_id = (id) ? '#' + id : null;
	return jQuery('.ajax-loader' + x_id);
}

function bpl_split_response(str)
{
	return str.split('[[split]]');
}

function bpl_remove_msg()
{
	jQuery('#message').remove();
}

function bpl_list_item_msg(lid, type, msg)
{
	jQuery('ul#link-list li#linklistitem-' + lid)
		.prepend('<div id="message" class="' + type + ' fade"><p>' + msg + '</p></div>');
}