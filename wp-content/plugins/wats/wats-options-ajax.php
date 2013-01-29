<?php
?>
jQuery(document).ready(function() {
	function wats_options_editable_init()
	{
		jQuery('.wats_editable').editable(
		{
			onEdit:wats_options_editable_begin,
			onSubmit:wats_options_editable_end,
			submitBy:'click'
		});
		
		return;
	}
	
	wats_options_editable_init();
	function wats_options_editable_begin()
	{    
		jQuery(this).addClass("wats_editableaccept");
	}
	
	function wats_options_editable_end(content)
	{
		jQuery(this).removeClass("wats_editableaccept");
		if (content.current != content.previous)
		{
			var id = jQuery(this);
			var idtable = jQuery(this).parent("tr").parent().parent("table").attr("id");
			var idvalue = content.current;
			var idprevvalue = content.previous;
			jQuery.post(ajaxurl, {action:"wats_admin_update_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, idtable:idtable, idprevvalue:idprevvalue},
			function(res)
			{
				var message_result = eval('(' + res + ')');
				alert(message_result.error);
				if (message_result.success == "FALSE")
				{
					jQuery(id).html(content.previous);
				}
				else
				{
					jQuery(id).html(message_result.idvalue);
				}
			});
		}
	}
	
    jQuery('#idaddtype').click(function() {
		jQuery('#idaddtype').attr('disabled','disabled');
		var type = "wats_types";
		var idvalue = jQuery("#idtype").val();
		wats_loading(document.getElementById("resultaddtype"),watsmsg[4]);
		var idcat = 0;
		jQuery.post(ajaxurl, {action:"wats_admin_insert_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
		function(res)
		{
			jQuery('#idaddtype').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=typecheck]").length;
				var liste = [message_result.id,message_result.idvalue];
				var editable = [0,1];
				wats_js_add_table_col_with_default(document.getElementById("tabletype"),liste,"typecheck",x,message_result.id,editable,"group_default_wats_types");
				jQuery("#idtype").val("");
				wats_options_editable_init();
			}
			wats_stop_loading(document.getElementById("resultaddtype"),message_result.error);
		});
		return false;
	});

	jQuery('#idsuptype').click(function() {
		if (jQuery("input[name=typecheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsuptype"),watsmsg[0]);
		else if (jQuery("input[name=typecheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsuptype"),watsmsg[1]);
		var type = "wats_types";
	    jQuery("input[name=typecheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsuptype').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type},
				function(res)
				{
					jQuery('#idsuptype').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsuptype"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=typecheck]").length == 0)
						wats_js_add_blank_cell("tabletype",4,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddpriority').click(function() {
		jQuery('#idaddpriority').attr('disabled','disabled');
		var type = "wats_priorities";
		var idvalue = jQuery("#idpriority").val();
		var idcat = 0;
		wats_loading(document.getElementById("resultaddpriority"),watsmsg[4]);
		jQuery.post(ajaxurl, {action:"wats_admin_insert_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
		function(res)
		{
			jQuery('#idaddpriority').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=prioritycheck]").length;
				var liste = [message_result.id,message_result.idvalue];
				var editable = [0,1];
				wats_js_add_table_col_with_default(document.getElementById("tablepriority"),liste,"prioritycheck",x,message_result.id,editable,"group_default_wats_priorities");
				jQuery("#idpriority").val("");
				wats_options_editable_init();
			}
			wats_stop_loading(document.getElementById("resultaddpriority"),message_result.error);
		});
		return false;
	});

	jQuery('#idsuppriority').click(function() {
		if (jQuery("input[name=prioritycheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsuppriority"),watsmsg[0]);
		else if (jQuery("input[name=prioritycheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsuppriority"),watsmsg[1]);
		var type = "wats_priorities";
	    jQuery("input[name=prioritycheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsuppriority').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type},
				function(res)
				{
					jQuery('#idsuppriority').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsuppriority"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=prioritycheck]").length == 0)
						wats_js_add_blank_cell("tablepriority",4,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddstatus').click(function() {
		jQuery('#idaddstatus').attr('disabled','disabled');
		var type = "wats_statuses";
		var idvalue = jQuery("#idstatus").val();
		wats_loading(document.getElementById("resultaddstatus"),watsmsg[4]);
		var idcat = 0;
		jQuery.post(ajaxurl, {action:"wats_admin_insert_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
		function(res)
		{
			jQuery('#idaddstatus').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=statuscheck]").length;
				var liste = [message_result.id,message_result.idvalue];
				var editable = [0,1];
				wats_js_add_table_col_with_default(document.getElementById("tablestatus"),liste,"statuscheck",x,message_result.id,editable,"group_default_wats_statuses");
				jQuery("#idstatus").val("");
				wats_options_editable_init();
			}
			wats_stop_loading(document.getElementById("resultaddstatus"),message_result.error);
		});
		return false;
	});

	jQuery('#idsupstatus').click(function() {
		if (jQuery("input[name=statuscheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsupstatus"),watsmsg[0]);
		else if (jQuery("input[name=statuscheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsupstatus"),watsmsg[1]);
		var type = "wats_statuses";
	    jQuery("input[name=statuscheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsupstatus').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type},
				function(res)
				{
					jQuery('#idsupstatus').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsupstatus"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=statuscheck]").length == 0)
						wats_js_add_blank_cell("tablestatus",4,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddproduct').click(function() {
		jQuery('#idaddproduct').attr('disabled','disabled');
		var type = "wats_products";
		var idvalue = jQuery("#idproduct").val();
		var idcat = 0;
		wats_loading(document.getElementById("resultaddproduct"),watsmsg[4]);
		jQuery.post(ajaxurl, {action:"wats_admin_insert_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
		function(res)
		{
			jQuery('#idaddproduct').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=productcheck]").length;
				var liste = [message_result.id,message_result.idvalue];
				var editable = [0,1];
				wats_js_add_table_col_with_default(document.getElementById("tableproduct"),liste,"productcheck",x,message_result.id,editable,"group_default_wats_products");
				jQuery("#idproduct").val("");
				wats_options_editable_init();
			}
			wats_stop_loading(document.getElementById("resultaddproduct"),message_result.error);
		});
		return false;
	});

	jQuery('#idsupproduct').click(function() {
		if (jQuery("input[name=productcheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsupproduct"),watsmsg[0]);
		else if (jQuery("input[name=productcheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsupproduct"),watsmsg[1]);
		var type = "wats_products";
	    jQuery("input[name=productcheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsupproduct').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type},
				function(res)
				{
					jQuery('#idsupproduct').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsupproduct"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=productcheck]").length == 0)
						wats_js_add_blank_cell("tableproduct",4,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddsla').click(function() {
		jQuery('#idaddsla').attr('disabled','disabled');
		var type = "wats_slas";
		var idvalue = jQuery("#idsla").val();
		var idcat = 0;
		wats_loading(document.getElementById("resultaddsla"),watsmsg[4]);
		jQuery.post(ajaxurl, {action:"wats_admin_insert_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
		function(res)
		{
			jQuery('#idaddsla').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=slacheck]").length;
				var liste = [message_result.id,message_result.idvalue];
				var editable = [0,1];
				wats_js_add_table_col(document.getElementById("tablesla"),liste,"slacheck",x,message_result.id,editable);
				jQuery("#idsla").val("");
				wats_options_editable_init();
			}
			wats_stop_loading(document.getElementById("resultaddsla"),message_result.error);
		});
		return false;
	});

	jQuery('#idsupsla').click(function() {
		if (jQuery("input[name=slacheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsupsla"),watsmsg[0]);
		else if (jQuery("input[name=slacheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsupsla"),watsmsg[1]);
		var type = "wats_slas";
	    jQuery("input[name=slacheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsupsla').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type},
				function(res)
				{
					jQuery('#idsupsla').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsupsla"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=slacheck]").length == 0)
						wats_js_add_blank_cell("tablesla",3,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddcat').click(function() {
		jQuery('#idaddcat').attr('disabled','disabled');
		var type = "wats_categories";
		var idvalue = jQuery('#catlist option:selected').text();
		var idcat = jQuery('#catlist option:selected').val();
		wats_loading(document.getElementById("resultaddcat"),watsmsg[4]);
		jQuery.post(ajaxurl, {action:"wats_admin_insert_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
		function(res)
		{
			jQuery('#idaddcat').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=catcheck]").length;
				var liste = [message_result.id,message_result.idvalue];
				var editable = [0,0];
				wats_js_add_table_col(document.getElementById("tablecat"),liste,"catcheck",x,message_result.id,editable);
			}
			wats_stop_loading(document.getElementById("resultaddcat"),message_result.error);
		});

		return false;
	});

	jQuery('#idsupcat').click(function() {
		if (jQuery("input[name=catcheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsupcat"),watsmsg[0]);
		else if (jQuery("input[name=catcheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsupcat"),watsmsg[1]);
		var type = "wats_categories";
	    jQuery("input[name=catcheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsupcat').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type},
				function(res)
				{
					jQuery('#idsupcat').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsupcat"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=catcheck]").length == 0)
						wats_js_add_blank_cell("tablecat",3,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddrule').click(function() {
		jQuery('#idaddrule').attr('disabled','disabled');
		var idtype = jQuery('#notification_rules_select_ticket_type option:selected').val();
		var idpriority = jQuery('#notification_rules_select_ticket_priority option:selected').val();
		var idstatus = jQuery('#notification_rules_select_ticket_status option:selected').val();
		var idproduct = jQuery('#notification_rules_select_ticket_product option:selected').val();
		var idcountry = jQuery('#notification_rules_select_ticket_country option:selected').val();
		var idcompany = jQuery('#notification_rules_select_ticket_company option:selected').val();
		var idcategorie = jQuery('#notification_rules_select_category option:selected').val();
		var idrulescope = jQuery('#notification_rules_select_rule_scope option:selected').val();
		var listvalue = jQuery("#rule_mailing_list").val();
		wats_loading(document.getElementById("resultaddrule"),watsmsg[4]);
		jQuery.post(ajaxurl, {action:"wats_admin_insert_notification_rule_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idtype:idtype, idpriority:idpriority, idstatus:idstatus, idproduct:idproduct, idcountry:idcountry, idcompany:idcompany, idcategorie:idcategorie, listvalue:listvalue, idrulescope:idrulescope},
		function(res)
		{
			jQuery('#idaddrule').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=notification_rule_check]").length;
				var liste = [message_result.id,message_result.rule,message_result.list];
				var editable = [0,0,0];
				wats_js_add_table_col(document.getElementById("tablerules"),liste,"notification_rule_check",x,message_result.id,editable);
			}
			wats_stop_loading(document.getElementById("resultaddrule"),message_result.error);
		});

		return false;
	});
	
	jQuery('#idsuprule').click(function() {
		if (jQuery("input[name=notification_rule_check]").length == 0)
			wats_stop_loading(document.getElementById("resultsuprule"),watsmsg[0]);
		else if (jQuery("input[name=notification_rule_check]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsuprule"),watsmsg[1]);
	    jQuery("input[name=notification_rule_check]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsuprule').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_notification_rule_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue},
				function(res)
				{
					jQuery('#idsuprule').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsuprule"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=notification_rule_check]").length == 0)
						wats_js_add_blank_cell("tablerules",4,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddcustomfields').click(function() {
		jQuery('#idaddcustomfields').attr('disabled','disabled');
		var idfsf = jQuery('#fsf option:selected').val();
		var idatef = jQuery('#atef option:selected').val();
		var idftdt = jQuery('#ftdt option:selected').val();
		var idftuf = jQuery('#ftuf option:selected').val();
		var idftlf = jQuery('#ftlf option:selected').val();
		var idftltc = jQuery('#ftltc option:selected').val();
		var idtype = jQuery('#wats_custom_field_type option:selected').val();
		var customfieldname = jQuery("#customfieldsdisplayname").val();
		var customfieldmetakey = jQuery("#customfieldsmetakey").val();
		wats_loading(document.getElementById("resultaddcustomfields"),watsmsg[4]);
		jQuery.post(ajaxurl, {action:"wats_admin_insert_ticket_custom_field", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idfsf:idfsf, idatef:idatef, idftdt:idftdt, idftuf:idftuf, idftlf:idftlf, idftltc:idftltc, customfieldname:customfieldname, customfieldmetakey:customfieldmetakey, idtype:idtype},
		function(res)
		{
			jQuery('#idaddcustomfields').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				jQuery('#divticketcustomfieldstable').html(message_result.output);
				jQuery("#customfieldsdisplayname").val('');
				jQuery("#customfieldsmetakey").val('');
				wats_js_options_bind_edit_custom_field();
			}
			wats_stop_loading(document.getElementById("resultaddcustomfields"),message_result.error);
		});

		return false;
	});
	
	jQuery('#idsupcustomfields').click(function() {
		if (jQuery("input[name=customfieldcheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsupcustomfields"),watsmsg[0]);
		else if (jQuery("input[name=customfieldcheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsupcustomfields"),watsmsg[1]);
	    jQuery("input[name=customfieldcheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsupcustomfields').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_ticket_custom_field", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue},
				function(res)
				{
					jQuery('#idsupcustomfields').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsupcustomfields"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=customfieldcheck]").length == 0)
						wats_js_add_blank_cell("tablecustomfields",10,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#idaddcustomselector').click(function() {
		jQuery('#idaddcustomselector').attr('disabled','disabled');
		var type = "wats_custom_selector";
		var idvalue = jQuery("#idcustomselector").val();
		var idcat = jQuery('#wats_custom_fields_selector option:selected').val();
		wats_loading(document.getElementById("resultaddcustomselector"),watsmsg[4]);
		jQuery.post(ajaxurl, {action:"wats_admin_insert_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
		function(res)
		{
			jQuery('#idaddcustomselector').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				var x = jQuery("input[name=customselectorcheck]").length;
				var liste = [message_result.id,message_result.idvalue];
				var editable = [0,0];
				wats_js_add_table_col_with_default(document.getElementById("custom_fields_selector_values_table"),liste,"customselectorcheck",x,message_result.id,editable,"group_default_custom_selector");
				jQuery("#idcustomselector").val("");
				wats_options_editable_init();
			}
			wats_stop_loading(document.getElementById("resultaddcustomselector"),message_result.error);
		});
		return false;
	});
	
	jQuery('#idsupcustomselector').click(function() {
		if (jQuery("input[name=customselectorcheck]").length == 0)
			wats_stop_loading(document.getElementById("resultsupcustomselector"),watsmsg[0]);
		else if (jQuery("input[name=customselectorcheck]:checked").length == 0)
			wats_stop_loading(document.getElementById("resultsupcustomselector"),watsmsg[1]);
		var type = "wats_custom_selector";
		var idcat = jQuery('#wats_custom_fields_selector option:selected').val();
	    jQuery("input[name=customselectorcheck]:checked").each(function()
		{
		    if (this.checked == true)
			{
				jQuery('#idsupcustomselector').attr('disabled','disabled');
				var idvalue = this.value;
				var nodetoremove = this.parentNode;
				jQuery.post(ajaxurl, {action:"wats_admin_remove_option_entry", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, type:type, idcat:idcat},
				function(res)
				{
					jQuery('#idsupcustomselector').removeAttr('disabled');
					var message_result = eval('(' + res + ')');
					wats_stop_loading(document.getElementById("resultsupcustomselector"),message_result.error);
					if (message_result.success == "TRUE")
					{
						parenttoremove = nodetoremove.parentNode;
						parenttoremove.parentNode.removeChild(parenttoremove);
					}
					if (jQuery("input[name=customselectorcheck]").length == 0)
						wats_js_add_blank_cell("custom_fields_selector_values_table",4,watsmsg[2]);
				});
			}
		});
		return false;
	});
	
	jQuery('#wats_custom_fields_selector').change(function() {
		var idvalue = jQuery('#wats_custom_fields_selector option:selected').val();
		jQuery.post(ajaxurl, {action:"wats_admin_get_custom_fields_selector_values_table", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue},
				function(res)
				{
					var message_result = eval('(' + res + ')');
					if (message_result.success == "TRUE")
					{
						jQuery("#custom_fields_selector_values_table_div").html(message_result.id).hide();
				        jQuery("#custom_fields_selector_values_table_div").fadeIn("slow");
					}
					else
						wats_stop_loading(document.getElementById("custom_fields_selector_values_table_div"),message_result.error);
				});
				
		return false;
	});
	
	jQuery('#wats_custom_fields_selector').change();
	
	var selected_guestlist_ac = 0;
	jQuery('#guestlist_ac').autocomplete({
						source: function(request,response)  {
						jQuery.ajax({
							url: ajaxurl+"?action=wats_ajax_admin_get_user_list",
							dataType: "json",
							data: {	
								value:jQuery('#guestlist_ac').val(),
								type:'guestlist',
								_ajax_nonce:jQuery("#_wpnonce").val(),
								'cookie': encodeURIComponent(document.cookie)
							},
							success: function(data) {
								if (jQuery.isEmptyObject(data) == true)
									jQuery('#guestlist').val("-1");
								response( jQuery.map(data, function(item)
								{ return{value:item.label,label:item.label,hidden:item.value} }));
							}
							});
						},
						select: function(event,ui) {
							selected_guestlist_ac = 1;
							jQuery('#guestlist').val(ui.item.hidden);
						},
						close : function(event,ui) {
							if (selected_guestlist_ac == 0)	
								jQuery('#defaultauthorlist').val("-1");
							selected_guestlist_ac = 0;
						},
						minLength:3,
						delay:300
	});

	var selected_defaultauthorlist_ac = 0;
	jQuery('#defaultauthorlist_ac').autocomplete({
						source: function(request,response)  {
						jQuery.ajax({
							url: ajaxurl+"?action=wats_ajax_admin_get_user_list",
							dataType: "json",
							data: {	
								value:jQuery('#defaultauthorlist_ac').val(),
								type:'defaultauthorlist',
								_ajax_nonce:jQuery("#_wpnonce").val(),
								'cookie': encodeURIComponent(document.cookie)
							},
							success: function(data) {
								if (jQuery.isEmptyObject(data) == true)
									jQuery('#defaultauthorlist').val("-1");
								response(
								jQuery.map(data, function(item)
								{ return{value:item.label,label:item.label,hidden:item.value} }));
							}
							});
						},
						select: function(event,ui) {
							selected_defaultauthorlist_ac = 1;
							jQuery('#defaultauthorlist').val(ui.item.hidden);
						},
						close : function(event,ui) {
							if (selected_defaultauthorlist_ac == 0)	
								jQuery('#defaultauthorlist').val("-1");
							selected_defaultauthorlist_ac = 0;
						},
						minLength:3,
						delay:300
	});
	
function wats_js_options_bind_edit_custom_field()
{
	jQuery('[name^=wats_edit_custom_field]').click(function() {
		jQuery('[name^=wats_edit_custom_field]').attr('disabled','disabled');
		idvalue = jQuery(this).parent('td').next('td').find('input').val();
		jQuery.post(ajaxurl, {action:"wats_admin_options_get_custom_field_table_row", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue},
				function(res)
				{
					var message_result = eval('(' + res + ')');

					if (message_result.success == "TRUE")
					{
						jQuery('#wats_edit_custom_field'+idvalue).parent('td').parent('tr').html(message_result.output);
						wats_js_options_bind_save_custom_field();
					}
					else
						wats_stop_loading(document.getElementById("resultsupcustomfields"),message_result.error);
				});
		return false;
	});
	
	return false;
}

function wats_js_options_bind_save_custom_field()
{
	jQuery('[name^=wats_save_custom_field]').click(function() {
		idvalue = jQuery(this).parent('td').next('td').find('input').val();
		var idfsf = jQuery('#fsf'+idvalue+' option:selected').val();
		var idatef = jQuery('#atef'+idvalue+' option:selected').val();
		var idftdt = jQuery('#ftdt'+idvalue+' option:selected').val();
		var idftuf = jQuery('#ftuf'+idvalue+' option:selected').val();
		var idftlf = jQuery('#ftlf'+idvalue+' option:selected').val();
		var idftltc = jQuery('#ftltc'+idvalue+' option:selected').val();
		var idtype = jQuery('#wats_custom_field_type_'+idvalue+' option:selected').val();
		var customfieldname = jQuery("#customfieldsdisplayname"+idvalue).val();
		var customfieldmetakey = jQuery("#customfieldsmetakey"+idvalue).val();
		
		jQuery.post(ajaxurl, {action:"wats_admin_update_ticket_custom_field", _ajax_nonce:jQuery("#_wpnonce").val(), 'cookie': encodeURIComponent(document.cookie), idvalue:idvalue, idfsf:idfsf, idatef:idatef, idftdt:idftdt, idftuf:idftuf, idftlf:idftlf, idftltc:idftltc, customfieldname:customfieldname, customfieldmetakey:customfieldmetakey, idtype:idtype},
		function(res)
		{
			jQuery('#idaddcustomfields').removeAttr('disabled');
			var message_result = eval('(' + res + ')');
			if (message_result.success == "TRUE")
			{
				jQuery('#divticketcustomfieldstable').html(message_result.output);
				wats_js_options_bind_edit_custom_field();
			}
			wats_stop_loading(document.getElementById("resultsupcustomfields"),message_result.error);
		});
		return false;
	});
	
	return false;
}
	
	wats_js_options_bind_edit_custom_field();
	
	return false;
});