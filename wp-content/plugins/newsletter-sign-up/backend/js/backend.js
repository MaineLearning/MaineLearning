jQuery(document).ready(function(){
	
	var api_rows = jQuery("tbody.api_rows");
	var form_rows = jQuery("tbody.form_rows");
	var mc_groupings_rows = jQuery("tbody.mc_groupings_rows");
	var name_dependent_rows = jQuery('tr.name_dependent');
	
	jQuery("#use_api").change(function(){
		if(jQuery(this).attr('checked')) {
			api_rows.show();	
			form_rows.hide();
		} else {
			api_rows.hide();
			form_rows.show();
		}
	});

	jQuery("#mc_use_groupings").change(function(){
		if(jQuery(this).attr('checked')) {
			mc_groupings_rows.show();
		} else {
			mc_groupings_rows.hide();
		}
	});
	
	jQuery("#subscribe_with_name").change(function(){
		if(jQuery(this).attr('checked')) {
			name_dependent_rows.show();	
		} else {
			name_dependent_rows.hide();
		}
	});
	
});
