function do_filter(){
	var j = jQuery;
	var cookieExpires = new Date();
 	cookieExpires.setTime(cookieExpires.getTime() + (60 * 60 * 1000));
	
	j.blockUI({css:{width: '12%',top:'40%',left:'45%'},
                        message: jQuery('#blockUISpinner').show() });
	var filterby = j('#sortby-dropdown').val();
	if (filterby == '') {
		filterby = 'cat';
	}
	
	var data = {action:'get_posts_by', filterby:filterby};
	
	if (filterby == 'date'){	
		data['startdate'] = j("#startdate").val();
  		data['enddate'] = j("#enddate").val();
		j.cookie( 'anth-startdate', j("#startdate").val(), { expires: cookieExpires } );
		j.cookie( 'anth-enddate', j("#enddate").val(), { expires: cookieExpires } );
	}else{
		var term = j('#filter').val();
		data['term'] = term;
		j.cookie('anth-term', term, { expires: cookieExpires });
	}
	
	j.ajax({
		url: ajaxurl,
		type: 'POST',
		timeout: 10000,
		dataType:'json',
		data: data,
		success: function(response){
			j('#sidebar-posts').empty();
			j.each( response, function(post_id, post_title) {
				var h = '<li class="item"><span class="fromNewId">new-' + post_id + '</span><h3 class="part-item">' + post_title + '</h3></li>';
				j('#sidebar-posts').append(h);
			});
			anthologize.initSidebar();
		},
		complete: function(){
			j.unblockUI();
		}
	});		
}

jQuery(document).ready( function() {
	var j = jQuery;

	// Set parent id for new parts and account for autosave
	var anth_parent = j('#anth_parent_id');
	if (anth_parent.length){
		var wp_parent = j('input[name="parent_id"]').not(anth_parent);
		if (wp_parent.length){
			wp_parent.val(anth_parent.val());
			anth_parent.remove();	
		}else{
			anth_parent.attr('id', 'parent_id');
		}	
	}

	// Put the proper selector on the parent_id box to ensure that it doesn't get wiped on
        // autosave
        if (!j('input#parent_id').length) {
                j('input[name="parent_id"]').first().attr('id', 'parent_id');
        }
	
	// Set filter based on last visit
	var cfilter = j.cookie('anth-filter');
	
	if ( cfilter == 'date' ) {
		j("#termfilter").hide();
		j("#datefilter").show();
		var cstartdate = j.cookie('anth-startdate');
		var cenddate = j.cookie('anth-enddate');
		j("#startdate").val(cstartdate);
		j("#enddate").val(cenddate);
	} else {
		var cterm = j.cookie('anth-term');
	}

	j('#sortby-dropdown').change( function() {

		jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
                        message: jQuery('#blockUISpinner').show() });

		var filter = j('#sortby-dropdown').val();

		var cookieExpires = new Date();
 		cookieExpires.setTime(cookieExpires.getTime() + (60 * 60 * 1000));
		j.cookie('anth-filter', filter, { expires: cookieExpires });

		if (filter == 'date') {
			j("#termfilter").hide();
			j("#datefilter").show();
		} else {
			j("#datefilter").hide();
			j("#startdate").val('');
			j("#enddate").val('');
			j("#termfilter").show();
		}
	
		if (filter == '') {
			j('#filter').empty();
			j('#filter').append('<option value=""> - </option>');
			j('#filter').trigger('change');
			j.unblockUI();
			return true;
		}

		j.ajax({
			url: ajaxurl,
			type: 'POST',
			timeout: 10000,
			data: { action:'get_filterby_terms', filtertype:filter },
			dataType: 'json',
			success: function(response){
				j('#filter').empty();

				if (filter == 'tag') {
					j('#filter').append('<option value="">All Tags</option>');
				} else if (filter == 'category') {
					j('#filter').append('<option value="">All Categories</option>');
				} else if (filter == 'post_type') {
					j('#filter').append('<option value="">All Post Types</option>');				
				} else {
					j('#filter').append('<option value=""> - </option>');
				}
				j.each( response, function(tagcat_index, tagcat) {
					var h = '<option value="' + tagcat_index + '">' + tagcat + '</option>';
					j('#filter').append(h);
				});
			},
			complete: function(){
				j('#filter').trigger('change');
				j.unblockUI();
			}
		});
	});

	j('#filter').change( function() {
		do_filter();
	});

	j("#launch_date_filter").click(function(){
		do_filter();
	});

	j("#startdate").datepicker({dateFormat: 'yy-mm-dd'});
	j("#enddate").datepicker({dateFormat: 'yy-mm-dd'});
	
	j('#project-id-dropdown').change( function() {

		jQuery.blockUI({css:{width: '12%',top:'40%',left:'45%'},
                        message: jQuery('#blockUISpinner').show() });

		var proj_id = j('#project-id-dropdown').val();
		j.ajax({
			url: ajaxurl,
			type: 'POST',
			timeout: 10000,
			dataType:'json',
			data: {action:'get_project_meta',proj_id:proj_id},
			success: function(response){
				var meta = j.parseJSON(response);

				if ( meta['cctype'] )
					j('#cctype').val(meta['cctype']);
				else
					j('#cctype').val('by');
				if ( meta['authors'] )
					j('#authors').val(meta['authors']);
				else
					j('#authors').val('');



				var inputs = j('#export-form').find('input');
				j.each(inputs, function( index, input ) {
					var theid = j(input).attr('id');

					if ( theid == 'export-step' || theid == 'submit' )
						return true;

					if ( meta[theid] )
						j(input).val(meta[theid]);
					else
						j(input).val('');
				}
			)},
			complete: function(){
				j.unblockUI();
			}
		});
	});

	j('.confirm-delete').click( function() {
		var answer = confirm("Are you sure you want to delete this project?")
		if (answer){
			return true;
		}
		else{
			return false;
		}
	});

});

