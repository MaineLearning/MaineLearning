<?php

/**************************************/
/*                                    */
/* Fonction de chargement des options */
/*                                    */
/**************************************/

function wats_load_settings()
{
    global $wats_settings, $wats_default_ticket_status, $wats_version, $wats_default_ticket_priority, $wats_default_ticket_type, $wats_default_sla, $wats_default_ticket_listing_columns;

    if (!get_option('wats'))
	{
		foreach ($wats_default_ticket_status as $key => $value)
		{
			$default['wats_statuses'][$key] = __($value,'WATS');
		}
		foreach ($wats_default_ticket_priority as $key => $value)
		{
			$default['wats_priorities'][$key] = __($value,'WATS');
		}
		foreach ($wats_default_ticket_type as $key => $value)
		{
			$default['wats_types'][$key] = __($value,'WATS');
		}
		foreach ($wats_default_sla as $key => $value)
		{
			$default['wats_slas'][$key] = __($value,'WATS');
		}

		$default['numerotation'] = 0;
		$default['wats_version'] = $wats_version;
		$default['wats_guest_user'] = -1;
		$default['wats_home_display'] = 1;
		$default['visibility'] = 0;
		$default['ticket_assign'] = 0;
		$default['new_ticket_notification_admin'] = 0;
		$default['comment_menuitem_visibility'] = 0;
		$default['tickets_tagging'] = 0;
		$default['tickets_custom_fields'] = 0;
		$default['ticket_edition_media_upload'] = 0;
		$default['ticket_edition_media_upload_tabs'] = 0;
		$default['ticket_assign_user_list'] = 0;
		$default['ticket_update_notification_all_tickets'] = 0;
		$default['ticket_update_notification_my_tickets'] = 0;
		$default['call_center_ticket_creation'] = 0;
		$default['user_selector_format'] = 'user_login';
		$default['filter_ticket_listing'] = 0;
		$default['filter_ticket_listing_meta_key'] = 'None';
		$default['meta_column_ticket_listing'] = 0;
		$default['meta_column_ticket_listing_meta_key'] = 'None';
		$default['notification_signature'] = 'Regards,<br /><br />WATS Notification engine';
		$default['user_selector_order_1'] = 'last_name';
		$default['user_selector_order_2'] = 'first_name';
		$default['frontend_submit_form_access'] = 0;
		$default['frontend_submit_form_ticket_status'] = 0;
		$default['submit_form_default_author'] = wats_get_first_admin_login();
		$default['ms_ticket_submission'] = 0;
		$default['ms_mail_server'] = 'mail.example.com';
		$default['ms_port_server'] = '110';
		$default['ms_mail_address'] = 'login@example.com';
		$default['ms_mail_password'] = 'password';
		$closed = 0;
		foreach ($wats_default_ticket_status as $key => $value)
		{
			if ($value == "Closed")
				$closed = $key;
		}
		$default['closed_status_id'] = $closed;
		$default['ticket_notification_bypass_mode'] = 0;
		$default['default_ticket_type'] = 1;
		$default['default_ticket_status'] = 1;
		$default['default_ticket_priority'] = 1;
		$default['source_email_address'] = 0;
		$default['prevent_user_profile_mail_modification'] = 0;
		$default['ticket_product_key_enabled'] = 0;
		$default['ticket_status_key_enabled'] = 1;
		$default['ticket_priority_key_enabled'] = 1;
		$default['ticket_type_key_enabled'] = 1;
		$default['default_ticket_product'] = 1;
		$default['profile_country_enabled'] = 0;
		$default['country_meta_key_profile'] = 'country';
		$default['user_expiration_date_enabled'] = 0;
		$default['profile_company_enabled'] = 0;
		$default['company_meta_key_profile'] = 'company_name';
		$default['profile_sla_enabled'] = 0;
		$default['ticket_visibility_read_only_capability'] = 0;
		$default['ticket_notification_custom_list'] = 0;
		$default['ticket_visibility_same_company'] = 0;
		$default['internal_comment_visibility'] = 0;
		$default['template_selector'] = 0;
		
		$wats_default_ticket_listing_active_columns = array();
		foreach ($wats_default_ticket_listing_columns AS $column => $value)
		{
			$wats_default_ticket_listing_active_columns[$column] = 1;
		}
		$default['wats_default_ticket_listing_active_columns'] = $wats_default_ticket_listing_active_columns;
		
		$wats_default_ticket_listing_default_query = array();
		$wats_default_ticket_listing_default_query['type'] = 0;
		$wats_default_ticket_listing_default_query['priority'] = 0;
		$wats_default_ticket_listing_default_query['status_op'] = 0;
		$wats_default_ticket_listing_default_query['status'] = 0;
		$wats_default_ticket_listing_default_query['product'] = 0;
		$wats_default_ticket_listing_default_query['author'] = 0;
		$wats_default_ticket_listing_default_query['owner'] = 0;
		$default['wats_default_ticket_listing_default_query'] = $wats_default_ticket_listing_default_query;
		$default['display_list_not_authenticated'] = 1;		
		$default['drop_down_user_selector_format'] = 0;
		$default['wats_ticket_custom_fields'] = array();
		$default['wats_categories'] = array();
		$default['fsf_success_init'] = 1;
		$default['fsf_success_redirect_url'] = '';
		
   	    add_option('wats', $default);
	}
        
    $wats_settings = get_option('wats');

	// Mise à jour des options après installation d'une nouvelle version
	if ($wats_settings['wats_version'] != $wats_version)
	{
		if (!isset($wats_settings['wats_home_display']))
		{
			$wats_settings['wats_home_display'] = 1;
		}

		if (!isset($wats_settings['visibility']))
		{
			$wats_settings['visibility'] = 0;
		}

		if (!isset($wats_settings['ticket_assign']))
		{
			$wats_settings['ticket_assign'] = 0;
		}

		if (!isset($wats_settings['new_ticket_notification_admin']))
		{
			$wats_settings['new_ticket_notification_admin'] = 0;
		}
		
		if (!isset($wats_settings['comment_menuitem_visibility']))
		{
			$wats_settings['comment_menuitem_visibility'] = 0;
		}
		
		if (!isset($wats_settings['tickets_tagging']))
		{
			$wats_settings['tickets_tagging'] = 0;
		}
		
		if (!isset($wats_settings['tickets_custom_fields']))
		{
			$wats_settings['tickets_custom_fields'] = 0;
		}
		
		if (!isset($wats_settings['ticket_edition_media_upload']))
		{
			$wats_settings['ticket_edition_media_upload'] = 0;
		}
		
		if (!isset($wats_settings['ticket_edition_media_upload_tabs']))
		{
			$wats_settings['ticket_edition_media_upload_tabs'] = 0;
		}
		
		if (!isset($wats_settings['ticket_assign_user_list']))
		{
			$wats_settings['ticket_assign_user_list'] = 0;
		}
		
		if (!isset($wats_settings['ticket_update_notification_all_tickets']))
		{
			$wats_settings['ticket_update_notification_all_tickets'] = 0;
		}
		
		if (!isset($wats_settings['ticket_update_notification_my_tickets']))
		{
			$wats_settings['ticket_update_notification_my_tickets'] = 0;
		}
		
		if (!isset($wats_settings['call_center_ticket_creation']))
		{
			$wats_settings['call_center_ticket_creation'] = 0;
		}
		
		if (!isset($wats_settings['user_selector_format']))
		{
			$wats_settings['user_selector_format'] = 'user_login';
		}
		
		if (!isset($wats_settings['filter_ticket_listing']))
		{
			$wats_settings['filter_ticket_listing'] = 0;
		}
		
		if (!isset($wats_settings['filter_ticket_listing_meta_key']))
		{
			$wats_settings['filter_ticket_listing_meta_key'] = 'None';
		}
		
		if (!isset($wats_settings['meta_column_ticket_listing']))
		{
			$wats_settings['meta_column_ticket_listing'] = 0;
		}
		
		if (!isset($wats_settings['meta_column_ticket_listing_meta_key']))
		{
			$wats_settings['meta_column_ticket_listing_meta_key'] = 'None';
		}
		
		if (!isset($wats_settings['notification_signature']))
		{
			$wats_settings['notification_signature'] = 'Regards,<br /><br />WATS Notification engine';
		}
		
		if (!isset($wats_settings['user_selector_order_1']))
		{
			$wats_settings['user_selector_order_1'] = 'last_name';
		}
		
		if (!isset($wats_settings['user_selector_order_2']))
		{
			$wats_settings['user_selector_order_2'] = 'first_name';
		}
		
		if (!isset($wats_settings['frontend_submit_form_access']))
		{
			$wats_settings['frontend_submit_form_access'] = 0;
		}
		
		if (!isset($wats_settings['frontend_submit_form_ticket_status']))
		{
			$wats_settings['frontend_submit_form_ticket_status'] = 0;
		}

		if (!isset($wats_settings['frontend_submit_form_ticket_status']))
		{
			$wats_settings['frontend_submit_form_ticket_status'] = 0;
		}		

		if (!isset($wats_settings['submit_form_default_author']))
		{
			$wats_settings['submit_form_default_author'] = wats_get_first_admin_login();
		}

		if (!isset($wats_settings['ms_ticket_submission']))
		{
			$wats_settings['ms_ticket_submission'] = 0;
		}
		
		if (!isset($wats_settings['ms_mail_server']))
		{
			$wats_settings['ms_mail_server'] = 'mail.example.com';
		}
		
		if (!isset($wats_settings['ms_port_server']))
		{
			$wats_settings['ms_port_server'] = '110';
		}
		
		if (!isset($wats_settings['ms_mail_address']))
		{
			$wats_settings['ms_mail_address'] = 'login@example.com';
		}
		
		if (!isset($wats_settings['ms_mail_password']))
		{
			$wats_settings['ms_mail_password'] = 'password';
		}
		
		if (!isset($wats_settings['closed_status_id']))
		{
			$wats_ticket_status = $wats_settings['wats_statuses'];
			$closed = 0;
			foreach ($wats_ticket_status as $key => $value)
			{
				if ($value == "Closed")
					$closed = $key;
			}
			$wats_settings['closed_status_id'] = $closed;
		}
		
		if (!isset($wats_settings['ticket_notification_bypass_mode']))
		{
			$wats_settings['ticket_notification_bypass_mode'] = 0;
		}
		
		if (!isset($wats_settings['default_ticket_type']))
		{
			$wats_ticket_types = $wats_settings['wats_types'];
			$wats_settings['default_ticket_type'] = key($wats_ticket_types);
		}
		
		if (!isset($wats_settings['default_ticket_status']))
		{
			$wats_ticket_statuses = $wats_settings['wats_statuses'];
			$wats_settings['default_ticket_status'] = key($wats_ticket_statuses);
		}
		
		if (!isset($wats_settings['default_ticket_priority']))
		{
			$wats_ticket_priorities = $wats_settings['wats_priorities'];
			$wats_settings['default_ticket_priority'] = key($wats_ticket_priorities);
		}
		
		if (!isset($wats_settings['source_email_address']))
		{
			$wats_settings['source_email_address'] = 0;
		}
		
		if (!isset($wats_settings['prevent_user_profile_mail_modification']))
		{
			$wats_settings['prevent_user_profile_mail_modification'] = 0;
		}
		
		if (!isset($wats_settings['ticket_product_key_enabled']))
		{
			$wats_settings['ticket_product_key_enabled'] = 0;
		}
		
		if (!isset($wats_settings['ticket_status_key_enabled']))
		{
			$wats_settings['ticket_status_key_enabled'] = 1;
		}
		
		if (!isset($wats_settings['ticket_priority_key_enabled']))
		{
			$wats_settings['ticket_priority_key_enabled'] = 1;
		}
		
		if (!isset($wats_settings['ticket_type_key_enabled']))
		{
			$wats_settings['ticket_type_key_enabled'] = 1;
		}
		
		if (!isset($wats_settings['default_ticket_product']))
		{
			$wats_settings['default_ticket_product'] = 1;
		}
		
		if (!isset($wats_settings['profile_country_enabled']))
		{
			$wats_settings['profile_country_enabled'] = 0;
		}
		
		if (!isset($wats_settings['country_meta_key_profile']))
		{
			$wats_settings['country_meta_key_profile'] = 'country';
		}
		
		if (!isset($wats_settings['user_expiration_date_enabled']))
		{
			$wats_settings['user_expiration_date_enabled'] = 0;
		}
		
		if (!isset($wats_settings['profile_company_enabled']))
		{
			$wats_settings['profile_company_enabled'] = 0;
		}
		
		if (!isset($wats_settings['company_meta_key_profile']))
		{
			$wats_settings['company_meta_key_profile'] = 'company_name';
		}
		
		if (!isset($wats_settings['wats_slas']))
		{
			foreach ($wats_default_sla as $key => $value)
			{
				$wats_settings['wats_slas'][$key] = __($value,'WATS');
			}
		}
		
		if (!isset($wats_settings['profile_sla_enabled']))
		{
			$wats_settings['profile_sla_enabled'] = 0;
		}
		
		if (!isset($wats_settings['ticket_visibility_read_only_capability']))
		{
			$wats_settings['ticket_visibility_read_only_capability'] = 0;
		}
		
		if (!isset($wats_settings['ticket_notification_custom_list']))
		{
			$wats_settings['ticket_notification_custom_list'] = 0;
		}
		
		if (!isset($wats_settings['wats_default_ticket_listing_active_columns']))
		{
			$wats_default_ticket_listing_active_columns = array();
			foreach ($wats_default_ticket_listing_columns AS $column => $value)
			{
				$wats_default_ticket_listing_active_columns[$column] = 1;
			}
			$wats_settings['wats_default_ticket_listing_active_columns'] = $wats_default_ticket_listing_active_columns;
		}
		
		if (!isset($wats_settings['ticket_visibility_same_company']))
		{
			$wats_settings['ticket_visibility_same_company'] = 0;
		}
		
		if (!isset($wats_settings['internal_comment_visibility']))
		{
			$wats_settings['internal_comment_visibility'] = 0;
		}
		
		if (!isset($wats_settings['wats_default_ticket_listing_default_query']))
		{
			$wats_default_ticket_listing_default_query = array();
			$wats_default_ticket_listing_default_query['type'] = 0;
			$wats_default_ticket_listing_default_query['priority'] = 0;
			$wats_default_ticket_listing_default_query['status_op'] = 0;
			$wats_default_ticket_listing_default_query['status'] = 0;
			$wats_default_ticket_listing_default_query['product'] = 0;
			$wats_default_ticket_listing_default_query['author'] = 0;
			$wats_default_ticket_listing_default_query['owner'] = 0;
			$wats_settings['wats_default_ticket_listing_default_query'] = $wats_default_ticket_listing_default_query;
		}
		
		if (!isset($wats_settings['template_selector']))
		{
			$wats_settings['template_selector'] = 0;
		}
		
		if (!isset($wats_settings['display_list_not_authenticated']))
		{
			$wats_settings['display_list_not_authenticated'] = 1;
		}
		
		if (!isset($wats_settings['drop_down_user_selector_format']))
		{
			$wats_settings['drop_down_user_selector_format'] = 0;
		}
		
		if (!isset($wats_settings['wats_ticket_custom_fields']))
		{
			$wats_settings['wats_ticket_custom_fields'] = array();
		}
		
		if (!isset($wats_settings['wats_categories']))
		{
			$wats_settings['wats_categories'] = array();
		}
		
		if (!isset($wats_settings['fsf_success_init']))
		{
			$wats_settings['fsf_success_init'] = 1;
		}
		
		if (!isset($wats_settings['fsf_success_redirect_url']))
		{
			$wats_settings['fsf_success_redirect_url'] = '';
		}
		
		if ($wats_settings['wats_version'] < '1.0.58')
		{
			wats_update_notification_rules_format();
		}
		
		$wats_settings['wats_version'] = $wats_version;
		update_option('wats', $wats_settings);
	}
	
	return;
}

/*********************************************/
/*                                           */
/* Fonction Ajax de mise à jour d'une option */
/*                                           */
/*********************************************/

function wats_admin_update_option_entry()
{
	global $wats_settings;

	wats_load_settings();
	$idvalue = stripslashes_deep($_POST['idvalue']);
	$idprevvalue = stripslashes_deep($_POST['idprevvalue']);
	$idtable = $_POST['idtable'];
	
	if (!current_user_can('administrator'))
		die('-1');
	
	check_ajax_referer('update-wats-options');
	
	if (strlen($_POST['idvalue']) == 0)
	{
		$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => __("Error : please enter an entry!",'WATS'));
	}
	else
    {
		$res = 0;
		switch($idtable)
		{
			case "tabletype" : $type = "wats_types"; break;
			case "tablepriority" : $type = "wats_priorities"; break;
			case "tablestatus" : $type = "wats_statuses"; break;
			case "tableproduct" : $type = "wats_products"; break;
			case "tablesla" : $type = "wats_slas"; break;
			default : $res = 1; break;
		}
		
		if ($res == 1)
			$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => __("Error : please enter an entry!",'WATS'));
		else
		{
			$wats_options = $wats_settings[$type];
			foreach ($wats_options as $key => $value)
			{
				if ($value == html_entity_decode($idprevvalue))
					$res = $key;
			}
			
			foreach ($wats_options as $key => $value)
			{
				if ($value == $idvalue)
					$res = -1;
			}

			if ($res == 0)
			{
				$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => __("Error : entry not found!",'WATS'));
			}
			else if ($res == -1)
			{
				$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => __("Error : another entry has the same value!",'WATS'));
			}
			else
			{
				$wats_options[$res] = html_entity_decode($idvalue);
				$wats_settings[$type] = $wats_options;
				update_option('wats', $wats_settings);

				$message_result = array('id' => "", 'idvalue' => esc_html($idvalue),'success' => "TRUE", 'error' => __("Entry successfully updated!",'WATS'));
			}
        }
	}
	
	echo json_encode($message_result);
	exit;
}

/*********************************************/
/*                                           */
/* Fonction Ajax de suppression d'une option */
/*                                           */
/*********************************************/

function wats_admin_remove_option_entry()
{
	global $wats_settings;

	$idvalue = stripslashes_deep($_POST['idvalue']);
	$type = $_POST['type'];
		
	if (!current_user_can('administrator'))
		die('-1');
	
	check_ajax_referer('update-wats-options');
	wp_cache_flush();
	wats_load_settings();
	
	if ($type == "wats_custom_selector")
	{
		$idcat = isset($_POST['idcat']) ? $_POST['idcat'] : 0;
		$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
		$error = '';
		
		if (isset($wats_ticket_custom_field_values[$idcat]))
		{
			$table = $wats_ticket_custom_field_values[$idcat];

			if (isset($table['type']) && $table['type'] == 1)
			{
				$values = $table['values'];
				if (isset($values[$idvalue]))
				{
					unset($values[$idvalue]);
					$table['values'] = $values;
					$wats_ticket_custom_field_values[$idcat] = $table;
					$wats_settings['wats_ticket_custom_fields'] = $wats_ticket_custom_field_values;
					update_option('wats', $wats_settings);
				}
				else
					$error = __("Error : entry not existing!",'WATS');
			}
			else
				$error = __("Error : Invalid custom field selected!",'WATS');
		}
		else
			$error = __("Error : Invalid custom field selected!",'WATS');
			
		if (strlen($error) > 0)
			$message_result = array('id' => $idvalue, 'success' => "FALSE", 'error' => $error);
		else
			$message_result = array('id' => $idvalue, 'success' => "TRUE", 'error' => __("Entry successfully removed!",'WATS'));
	}
	else
	{
		$wats_options = $wats_settings[$type];
		if ($wats_options[$idvalue])
		{
			unset($wats_options[$idvalue]);
			$wats_settings[$type] = $wats_options;
			update_option('wats', $wats_settings);
			$message_result = array('id' => $idvalue,'success' => "TRUE", 'error' => __("Entry successfully removed!",'WATS'));
		}
		else
		{
			$message_result = array('id' => $idvalue,'success' => "FALSE", 'error' => __("Error : entry not existing!",'WATS'));
		}
	}
	
	echo json_encode($message_result);
	exit;
}

/*********************************/
/*                               */
/* Fonction d'ajout d'une option */
/*                               */
/*********************************/

function wats_admin_insert_option_entry()
{
	global $wats_settings;

	wats_load_settings();
	$idvalue = stripslashes_deep($_POST['idvalue']);
	$type = $_POST['type'];
	$idcat = $_POST['idcat'];
	
	if (!current_user_can('administrator'))
		die('-1');
	
	check_ajax_referer('update-wats-options');
	
	if (strlen($_POST['idvalue']) == 0)
	{
		$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => __("Error : please enter an entry!",'WATS'));
	}
	else
    {
		if ($type == "wats_custom_selector")
		{
			$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
			$length = 0;
			$error = '';
			if (isset($wats_ticket_custom_field_values[$idcat]))
			{
				$table = $wats_ticket_custom_field_values[$idcat];
				if (isset($table['type']) && $table['type'] == 1)
				{
					$values = $table['values'];
					foreach ($values as $key => $value)
					{
						if ($key > $length)
							$length = $key;
						if ($value == $idvalue)
							$error = __("Error : already existing entry!",'WATS');
					}
				}
				else
					$error = __("Error : Invalid custom field selected!",'WATS');
			}
			else
				$error = __("Error : Invalid custom field selected!",'WATS');
			
			if (strlen($error) > 0)
			{
				$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => $error);
			}
			else
			{
				$length++;
				$values[$length] = $idvalue;
				$table['values'] = $values;
				$wats_ticket_custom_field_values[$idcat] = $table;
				$wats_settings['wats_ticket_custom_fields'] = $wats_ticket_custom_field_values;
				update_option('wats', $wats_settings);
				$message_result = array('id' => $length, 'idvalue' => $idvalue,'success' => "TRUE", 'error' => __("Entry successfully added!",'WATS'));
			}
		}
		else
		{
			$res = 0;
			$length = 0;
			if ($wats_settings[$type])
			{
				$wats_options = $wats_settings[$type];
				foreach ($wats_options as $key => $value)
				{
					if ($key > $length)
						$length = $key;
					if (($value == $idvalue) || ($key == $idcat))
					{
						$res = 1;
					}
				}
			}

			if ($res == 1)
			{
				$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => __("Error : already existing entry!",'WATS'));
			}
			else
			{
				if ($idcat > 0)
					$length = $idcat;
				else
					$length++;
				$wats_options[$length] = $idvalue;
				$wats_settings[$type] = $wats_options;
				update_option('wats', $wats_settings);
				$message_result = array('id' => $length, 'idvalue' => $idvalue,'success' => "TRUE", 'error' => __("Entry successfully added!",'WATS'));
			}
		}
	}
	
	echo json_encode($message_result);
	exit;
}

/*********************************************/
/*                                           */
/* Fonction Ajax de suppression d'une règle de notification */
/*                                           */
/*********************************************/

function wats_admin_remove_notification_rule_entry()
{
	$idvalue = $_POST['idvalue'];
	
	if (!current_user_can('administrator'))
		die('-1');
	
	check_ajax_referer('update-wats-options');
	
	wp_cache_flush();
	$wats_notification_rules = get_option('wats_notification_rules');
	if ($wats_notification_rules[$idvalue])
	{
		unset($wats_notification_rules[$idvalue]);
		update_option('wats_notification_rules', $wats_notification_rules);
		$message_result = array('id' => $idvalue,'success' => "TRUE", 'error' => __("Rule successfully removed!",'WATS'));
	}
	else
	{
		$message_result = array('id' => $idvalue,'success' => "FALSE", 'error' => __("Error : rule not existing!",'WATS'));
	}
	
	echo json_encode($message_result);
	exit;
}

/************************************************/
/*                                              */
/* Fonction d'ajout d'une règle de notification */
/*                               			    */
/************************************************/

function wats_admin_insert_notification_rule_entry()
{
	global $wats_settings;
	
	wats_load_settings();

	$listvalue = stripslashes_deep($_POST['listvalue']);
	
	$idrulescope = stripslashes_deep($_POST['idrulescope']);
	
	if ($wats_settings['ticket_type_key_enabled'] == 1)
		$idtype = stripslashes_deep($_POST['idtype']);
	
	if ($wats_settings['ticket_priority_key_enabled'] == 1)
		$idpriority = stripslashes_deep($_POST['idpriority']);
	
	if ($wats_settings['ticket_status_key_enabled'] == 1)
		$idstatus = stripslashes_deep($_POST['idstatus']);
	
	if ($wats_settings['ticket_product_key_enabled'] == 1)
		$idproduct = stripslashes_deep($_POST['idproduct']);
	
	if ($wats_settings['profile_country_enabled'] == 1)
		$idcountry = stripslashes_deep($_POST['idcountry']);

	if ($wats_settings['profile_company_enabled'] == 1)
		$idcompany = stripslashes_deep($_POST['idcompany']);
	
	$idcategorie = stripslashes_deep($_POST['idcategorie']);
	
	if (!current_user_can('administrator'))
		die('-1');
		
	check_ajax_referer('update-wats-options');
	
	if (strlen($_POST['listvalue']) == 0)
	{
		$message_result = array('id' => "", 'idvalue' => "",'success' => "FALSE", 'error' => __("Error : please enter an entry!",'WATS'));
	}
	else
    {
		$wats_notification_rules = get_option('wats_notification_rules');
		$rule = "";
		$rule .= "scope:".$idrulescope.";";
		if ($wats_settings['ticket_type_key_enabled'] == 1)
			$rule .= "type:".$idtype.";";
		if ($wats_settings['ticket_priority_key_enabled'] == 1)
			$rule .= "priority:".$idpriority.";";
		if ($wats_settings['ticket_status_key_enabled'] == 1)
			$rule .= "status:".$idstatus.";";
		if ($wats_settings['ticket_product_key_enabled'] == 1)
			$rule .= "product:".$idproduct.";";
		if ($wats_settings['profile_country_enabled'] == 1)
			$rule .= "country:".$idcountry.";";
		if ($wats_settings['profile_company_enabled'] == 1)
			$rule .= "company:".$idcompany.";";
		$rule .= "category:".$idcategorie.";";

		$wats_notification_rules[] = array($rule => $listvalue);
		
		update_option('wats_notification_rules', $wats_notification_rules);
		
		end($wats_notification_rules);
		$last_id = key($wats_notification_rules);
		$message_result = array('id' => $last_id, 'rule' => wats_admin_display_notification_rule(wats_admin_build_notification_rule($rule)), 'list' => $listvalue, 'success' => "TRUE", 'error' => __("Rule successfully added!",'WATS'));
	}
	
	echo json_encode($message_result);
	exit;
}

/*******************************************************/
/*                                                     */
/* Fonction d'ajout d'un custom field pour les tickets */
/*                               			           */
/*******************************************************/

function wats_admin_insert_ticket_custom_field($idvalue)
{
	global $wats_settings;
	
	wats_load_settings();
	
	if (!wats_is_numeric($idvalue))
		$idvalue = -1;
	
	$idfsf = stripslashes_deep($_POST['idfsf']);
	$idatef = stripslashes_deep($_POST['idatef']);
	$idftdt = stripslashes_deep($_POST['idftdt']);
	$idftuf = stripslashes_deep($_POST['idftuf']);
	$idftlf = stripslashes_deep($_POST['idftlf']);
	$idftltc = stripslashes_deep($_POST['idftltc']);
	$idtype = stripslashes_deep($_POST['idtype']);
	$customfieldname = stripslashes_deep($_POST['customfieldname']);
	$customfieldmetakey = stripslashes_deep($_POST['customfieldmetakey']);
	
	if (!current_user_can('administrator'))
		die('-1');
		
	check_ajax_referer('update-wats-options');
	
	if (strlen($customfieldname) == 0)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please enter a name for the custom field!",'WATS'));
	}
	else if (strlen($customfieldmetakey) == 0)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please enter a value for the meta key!",'WATS'));
	}
	else if ($idfsf != 0 && $idfsf != 4 && $idfsf != 5)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid value for frontend submission form custom field visibility!",'WATS'));
	}
	else if ($idatef != 0 && $idatef != 1 && $idatef != 2 && $idatef != 3 && $idatef != 4 && $idatef != 5)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid value for admin ticket edition page custom field visibility!",'WATS'));
	}
	else if ($idftdt != 0 && $idftdt != 1 && $idftdt != 2)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid value for frontend ticket display template custom field visibility!",'WATS'));
	}
	else if ($idftuf != 0 && $idftuf != 4 && $idftuf != 5)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid value for frontend ticket update form custom field visibility!",'WATS'));
	}
	else if ($idftlf != 0 && $idftlf != 1 && $idftlf != 2)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid value for frontend ticket listing filter custom field visibility!",'WATS'));
	}
	else if ($idftltc != 0 && $idftltc != 1 && $idftltc != 2)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid value for frontend ticket listing table column custom field visibility!",'WATS'));
	}
	else if ($idtype != 0 && $idtype != 1)
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid value for custom field type!",'WATS'));
	}
	else
	{
		$customfieldmetakey = str_replace(" ","_",esc_html($customfieldmetakey));
		$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
		$error = '';

		foreach ($wats_ticket_custom_field_values as $key => $table)
		{
			if (($table['name'] == $customfieldname || $table['meta_key'] == $customfieldmetakey) && ($idvalue == -1 || ($idvalue != -1 && $key != $idvalue)))
				$error = __('Error : already existing entry!','WATS');
		}
		
		if (strlen($error) > 0)
			$message_result = array('success' => "FALSE", 'error' => $error);
		else
		{
			if ($idvalue == -1)
			{
				$wats_ticket_custom_field_values[] = array('name' => $customfieldname,
														   'meta_key' => $customfieldmetakey,
														   'type' => $idtype,
														   'values' => array(),
														   'default_value' => 1,
														   'fsf' => $idfsf,
														   'atef' => $idatef,
														   'ftdt' => $idftdt,
														   'ftuf' => $idftuf,
														   'ftlf' => $idftlf,
														   'ftltc' => $idftltc);
				$wats_settings['wats_ticket_custom_fields'] = $wats_ticket_custom_field_values;
				update_option('wats', $wats_settings);
				$output = wats_options_display_custom_fields_table();
				$message_result = array('success' => "TRUE", 'output' => $output, 'error' => __("Entry successfully added!",'WATS'));
			}
			else
			{
				$table = $wats_ticket_custom_field_values[$idvalue];
				$table['name'] = $customfieldname;
				$table['meta_key'] = $customfieldmetakey;
				$table['type'] = $idtype;
				$table['fsf'] = $idfsf;
				$table['atef'] = $idatef;
				$table['ftdt'] = $idftdt;
				$table['ftuf'] = $idftuf;
				$table['ftlf'] = $idftlf;
				$table['ftltc'] = $idftltc;
				$wats_ticket_custom_field_values[$idvalue] = $table;
				$wats_settings['wats_ticket_custom_fields'] = $wats_ticket_custom_field_values;
				update_option('wats', $wats_settings);
				$output = wats_options_display_custom_fields_table();
				$message_result = array('success' => "TRUE", 'output' => $output, 'error' => __("Entry successfully updated!",'WATS'));
			}
		}
	}

	echo json_encode($message_result);
	exit;
}

/**************************************************************/
/*                                                            */
/* Fonction de mise à jour d'un custom field pour les tickets */
/*                               			                  */
/**************************************************************/

function wats_admin_update_ticket_custom_field()
{
	global $wats_settings;
	
	wats_load_settings();
	
	$idvalue = stripslashes_deep($_POST['idvalue']);
	
	if (!current_user_can('administrator'))
		die('-1');
		
	check_ajax_referer('update-wats-options');
	
	$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
	if (!isset($wats_ticket_custom_field_values[$idvalue]))
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid custom field to modify!",'WATS'));
		echo json_encode($message_result);
		exit;
	}
	
	wats_admin_insert_ticket_custom_field($idvalue);

	exit;
}

/************************************************/
/*                                              */
/* Fonction de suppression d'un custom field pour les tickets */
/*                               			    */
/************************************************/

function wats_admin_remove_ticket_custom_field()
{
	global $wats_settings;
	
	$idvalue = $_POST['idvalue'];
	
	if (!current_user_can('administrator'))
		die('-1');
	
	check_ajax_referer('update-wats-options');
	
	wp_cache_flush();
	wats_load_settings();
	
	$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
	if ($wats_ticket_custom_field_values[$idvalue])
	{
		unset($wats_ticket_custom_field_values[$idvalue]);
		$wats_settings['wats_ticket_custom_fields'] = $wats_ticket_custom_field_values;
		update_option('wats', $wats_settings);
		$message_result = array('id' => $idvalue,'success' => "TRUE", 'error' => __("Entry successfully removed!",'WATS'));
	}
	else
	{
		$message_result = array('id' => $idvalue,'success' => "FALSE", 'error' => __("Error : entry not existing!",'WATS'));
	}
	
	echo json_encode($message_result);
	exit;
}

/**************************************************************************/
/*                                                                        */
/* Fonction d'affichage de l'interface d'ajout des règles de notification */
/*                                                                        */
/**************************************************************************/

function wats_admin_add_notification_rules_interface($resultsup,$resultadd,$idsup,$idadd,$value,$input)
{
	global $wats_settings,$wats_rule_scope;
	
	$wats_ticket_priority = isset($wats_settings['wats_priorities']) ? $wats_settings['wats_priorities'] : 0;
	$wats_ticket_type = isset($wats_settings['wats_types']) ? $wats_settings['wats_types'] : 0;
	$wats_ticket_status = isset($wats_settings['wats_statuses']) ? $wats_settings['wats_statuses'] : 0;
	$wats_ticket_product = isset($wats_settings['wats_products']) ? $wats_settings['wats_products'] : 0;
	
	echo '<input type="submit" class="button-primary" id="'.$idsup.'" value="'.__('Remove selected rules','WATS').'" /><div id="'.$resultsup.'"></div><br /><br />';
	echo '<table class="wats-form-table" cellspacing="1" cellpadding="1">';
	
	echo '<tr><th><label>'.__('Rule scope','WATS').'</label></th><td>';
	echo '<select name="notification_rules_select_rule_scope" id="notification_rules_select_rule_scope">';
	foreach ($wats_rule_scope as $key => $value)
		echo '<option value="'.$key.'">'.esc_html($value).'</option>';
	echo '</select></td><td></td></tr>';
	
	if ($wats_settings['ticket_type_key_enabled'] == 1)
	{
		echo '<tr><th><label>'.__('Ticket type','WATS').'</label></th><td>';
		echo '<select name="notification_rules_select_ticket_type" id="notification_rules_select_ticket_type">';
		echo '<option value="0">'.esc_html__('Any','WATS').'</option>';
		if (is_array($wats_ticket_type))
		foreach ($wats_ticket_type as $key => $value)
			echo '<option value="'.$key.'">'.esc_html__($value,'WATS').'</option>';
		echo '</select></td><td></td></tr>';
	}
	
	if ($wats_settings['ticket_priority_key_enabled'] == 1)
	{
		echo '<tr><th><label>'.__('Ticket priority','WATS').'</label></th><td>';
		echo '<select name="notification_rules_select_ticket_priority" id="notification_rules_select_ticket_priority">';
		echo '<option value="0">'.esc_html__('Any','WATS').'</option>';
		if (is_array($wats_ticket_priority))
		foreach ($wats_ticket_priority as $key => $value)
			echo '<option value="'.$key.'">'.esc_html__($value,'WATS').'</option>';
		echo '</select></td><td></td></tr>';
	}
	
	if ($wats_settings['ticket_status_key_enabled'] == 1)
	{
		echo '<tr><th><label>'.__('Ticket status','WATS').'</label></th><td>';
		echo '<select name="notification_rules_select_ticket_status" id="notification_rules_select_ticket_status">';
		echo '<option value="0">'.esc_html__('Any','WATS').'</option>';
		if (is_array($wats_ticket_status))
		foreach ($wats_ticket_status as $key => $value)
			echo '<option value="'.$key.'">'.esc_html__($value,'WATS').'</option>';
		echo '</select></td><td></td></tr>';
	}
	
	if ($wats_settings['ticket_product_key_enabled'] == 1)
	{
		echo '<tr><th><label>'.__('Ticket product','WATS').'</label></th><td>';
		echo '<select name="notification_rules_select_ticket_product" id="notification_rules_select_ticket_product">';
		echo '<option value="0">'.esc_html__('Any','WATS').'</option>';
		if (is_array($wats_ticket_product))
		foreach ($wats_ticket_product as $key => $value)
			echo '<option value="'.$key.'">'.esc_html__($value,'WATS').'</option>';
		echo '</select></td><td></td></tr>';
	}
	
	if ($wats_settings['profile_country_enabled'] == 1)
	{
		$country_list = wats_build_country_list();
		echo '<tr><th><label>'.__('Country','WATS').'</label></th>';
		echo '<td><select name="notification_rules_select_ticket_country" id="notification_rules_select_ticket_country">';
		echo '<option value="0">'.__('Any','WATS').'</option>';
		foreach ($country_list as $key => $value)
		{
			echo '<option value="'.esc_attr($key).'">'.esc_html($key).'</option>';
		}
		echo '</select></td></tr>';
	}
	
	if ($wats_settings['profile_company_enabled'] == 1)
	{
		if (function_exists('wats_build_company_list'))
		{
			$company_list = wats_build_company_list(0);
			echo '<tr><th><label>'.__('Company','WATS').'</label></th>';
			echo '<td><select name="notification_rules_select_ticket_company" id="notification_rules_select_ticket_company">';
			echo '<option value="0">'.__('Any','WATS').'</option>';
			foreach ($company_list AS $key => $value)
			{
				echo '<option value="'.esc_attr($key).'">'.esc_html($key).'</option>';
			}
			echo '</select></td></tr>';
		}
	}
	
	echo '<tr><th><label>'.__('Category','WATS').'</label></th>';
	echo '<td><select name="notification_rules_select_category" id="notification_rules_select_category">';
	echo '<option value="0">'.__('Any','WATS').'</option>';
	add_filter('list_terms_exclusions','wats_list_terms_exclusions');
	$categories = get_categories('type=post&hide_empty=0');
	foreach ($categories as $category)
		echo '<option value="'.$category->cat_ID.'" >'.esc_html($category->cat_name).'</option>';
	echo '</select></td></tr>';
	
	echo '<tr><th><label>'.__('Mailing list','WATS').'</label></th><td><input type="text" name="rule_mailing_list" id="rule_mailing_list" size="30" class="regular-text" /></td><td></td></tr>';
	echo '</table><br />';
	echo '<input type="submit" id="'.$idadd.'" value="'.__('Add this rule','WATS').'" class="button-primary" /><div id="'.$resultadd.'"></div>';

	return;
}

/*************************************************************/
/*                                                           */
/* Fonction d'affichage de l'interface d'ajout de catégories */
/*                                                           */
/*************************************************************/

function wats_admin_add_category_interface($resultsup,$resultadd,$idsup,$idadd,$value,$input)
{

	echo '<input type="submit" class="button-primary" id="'.$idsup.'" value="'.__('Remove selected categories','WATS').'" /><div id="'.$resultsup.'"></div><br /><br />';

	echo '<table class="wats-form-table" cellspacing="1" cellpadding="1">';
	echo '<tr><th><label>'.__($value,'WATS').'</label></th><td>';
	echo '<select name="catlist" id="catlist" size="1">';
	$categories = get_categories('type=post&hide_empty=0');
	foreach ($categories as $category)
	{
        echo '<option value="'.$category->cat_ID.'" >'.esc_html($category->cat_name).'</option>';
	}
	echo '</select></td><td></td></tr>';
	echo '</table><br />';
	echo '<input type="submit" id="'.$idadd.'" value="'.__('Add this category','WATS').'" class="button-primary" /><div id="'.$resultadd.'"></div>';

	return;
}

/************************************************************/
/*                                                          */
/* Fonction d'affichage des tables dans la page des options */
/*                                                          */
/************************************************************/

function wats_admin_add_table_interface($resultsup,$resultadd,$idsup,$idadd,$value,$input)
{

	echo '<input type="submit" class="button-primary" id="'.$idsup.'" value="'.__('Remove selected items','WATS').'" /><div id="'.$resultsup.'"></div><br /><br />';

	echo '<table class="wats-form-table" cellspacing="1" cellpadding="1">';
	echo '<tr><th><label>'.__($value,'WATS').'</label></th><td><input type="text" name="'.$input.'" id="'.$input.'" size="30" class="regular-text" /></td><td></td></tr>';
	echo '</table><br />';
	echo '<input type="submit" id="'.$idadd.'" value="'.__('Add this entry','WATS').'" class="button-primary" /><div id="'.$resultadd.'"></div><br /><br />';

	return;
}

/***************************************************************************/
/*                                                                         */
/* Fonction de remplissage de la table des règles dans la page des options */
/*                                                                         */
/***************************************************************************/

function wats_admin_display_notification_rules_list()
{
	$wats_notification_rules = get_option('wats_notification_rules');
    $x = 0;
    $alt = false;
	if (is_array($wats_notification_rules))
	{
		foreach (array_keys($wats_notification_rules) AS $key)
		{
			foreach ($wats_notification_rules[$key] AS $rule => $list)
			{
				$x = 1;
				$rule = wats_admin_display_notification_rule(wats_admin_build_notification_rule($rule));
				echo '<tr valign="middle"';
				echo ($alt == true) ? ' class="alternate"' : '';
				echo '>';
				echo '<td>'.$key.'</td>';
				echo '<td>'.esc_html($rule).'</td>';
				echo '<td>'.esc_html($list).'</td>';
				echo '<td><input type="checkbox" name="notification_rule_check" id="notification_rule_check" value="'.$key.'" /></td>';
				echo '</tr>';

				$alt = !$alt;
			}
		}
	}

    if ($x == 0)
    {
        echo '<tr valign="middle"><td colspan="4" style="text-align:center">'.__('No entry','WATS').'</td></tr>';
    }
	echo '</tbody></table><br />';
	
    return;
}

/***************************************************************/
/*                                                             */
/* Fonction de remplissage des tables dans la page des options */
/*                                                             */
/***************************************************************/

function wats_admin_display_options_list($type,$check,$defaultvalue)
{
    global $wats_settings;
	
    $x = 0;
    $alt = false;
	if (isset($wats_settings[$type]))
	{
		$wats_options = $wats_settings[$type];
		foreach ($wats_options AS $key => $value)
		{
			$x = 1;
		
			echo '<tr valign="middle"';
			echo ($alt == true) ? ' class="alternate"' : '';
			echo '>';
			echo '<td>'.$key.'</td>';
			echo '<td';
			if ($type != 'wats_categories')
				echo ' class="wats_editable"';
			echo '>'.esc_html($value).'</td>';
			if ($type != 'wats_categories' && $type != 'wats_slas')
			{
				echo '<td><input type="radio" value="'.$key.'" name="group_default_'.$type.'" ';
				echo ($defaultvalue == $key) ? 'checked' : '';
				echo '></td>';
			}
			echo '<td><input type="checkbox" name="'.$check.'" id="'.$check.'" value="'.$key.'" /></td>';
			echo '</tr>';

			$alt = !$alt;
		}
    }

    if ($x == 0)
    {
		if ($type != 'wats_categories' && $type != 'wats_slas')
			$colspan = 4;
		else
			$colspan = 3;
        echo '<tr valign="middle"><td colspan="'.$colspan.'" style="text-align:center">'.__('No entry','WATS').'</td></tr>';
    }
	echo '</tbody></table><br />';
	
    return;
}

/*******************************************************************************/
/*                                                                             */
/* Fonction de récupération d'une ligne de la table des custom fields à éditer */
/*                                                                             */
/*******************************************************************************/

function wats_admin_options_get_custom_field_table_row()
{
	global $wats_settings;
	
	wats_load_settings();
	
	$idvalue = stripslashes_deep($_POST['idvalue']);
	
	if (!current_user_can('administrator'))
		die('-1');
		
	check_ajax_referer('update-wats-options');
	
	$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
	if (!isset($wats_ticket_custom_field_values[$idvalue]))
	{
		$message_result = array('success' => "FALSE", 'error' => __("Error : please select a valid custom field to modify!",'WATS'));
	}
	else
	{
		$table = $wats_ticket_custom_field_values[$idvalue];
		$output = '<td>'.$idvalue.'</td><td><input type="text" id="customfieldsdisplayname'.$idvalue.'" size=30 value="'.esc_attr($table['name']).'" /></td><td><input type="text" id="customfieldsmetakey'.$idvalue.'" size=30 value="'.esc_attr($table['meta_key']).'" /></td>';
		$output .= '<td><select name="wats_custom_field_type_'.$idvalue.'" id ="wats_custom_field_type_'.$idvalue.'" size="1"><option value="0"';
		if (isset($table['type']) && $table['type'] == 0)
			$output .= ' selected';
		$output .= '>'.__('Text input','WATS').'</option><option value="1"';
		if (isset($table['type']) && $table['type'] == 1)
			$output .= ' selected';
		$output .= '>'.__('Drop down selector','WATS').'</option></select></td>';
		$output .= '<td>'.wats_options_display_custom_fields_selectors_field(array(0,4,5),'fsf'.$idvalue,$table['fsf']).'</td>';
		$output .= '<td>'.wats_options_display_custom_fields_selectors_field(array(0,1,2,3,4,5),'atef'.$idvalue,$table['atef']).'</td>';
		$output .= '<td>'.wats_options_display_custom_fields_selectors_field(array(0,1,2),'ftdt'.$idvalue,$table['ftdt']).'</td>';
		$output .= '<td>'.wats_options_display_custom_fields_selectors_field(array(0,4,5),'ftuf'.$idvalue,$table['ftuf']).'</td>';
		$output .= '<td>'.wats_options_display_custom_fields_selectors_field(array(0,1,2),'ftlf'.$idvalue,$table['ftlf']).'</td>';
		$output .= '<td>'.wats_options_display_custom_fields_selectors_field(array(0,1,2),'ftltc'.$idvalue,$table['ftltc']).'</td>';
		$output .= '<td><input type="submit" name="wats_save_custom_field'.$idvalue.'" id="wats_save_custom_field'.$idvalue.'" value="'.__('Save','WATS').'" class="button-primary" /></td>';
		$output .= '<td><input type="checkbox" name="customfieldcheck" id="customfieldcheck'.$idvalue.'" value="'.$idvalue.'" /></td>';
		
		$message_result = array('success' => "TRUE", 'output' => $output);
	}

	echo json_encode($message_result);
	exit;	
}

/******************************************************/
/*                                                    */
/* Fonction d'affichage de la table des custom fields */
/*                                                    */
/******************************************************/

function wats_options_display_custom_fields_table()
{
	global $wats_settings, $wats_custom_fields_selectors;

	wats_load_settings();

	$wats_ticket_custom_field_values = (isset($wats_settings['wats_ticket_custom_fields'])) ? $wats_settings['wats_ticket_custom_fields'] : 0;

	$output = '<table class="widefat" cellspacing="0" id="tablecustomfields" style="text-align:center;"><thead><tr class="thead">';
	$output .= '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Custom field display name','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Custom field meta key (DB) identifier','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Custom field type','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Frontend submission form','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Admin ticket edition page','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Frontend ticket display template','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Frontend ticket update form','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Frontend ticket listing filter','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Frontend ticket listing table column','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Edit','WATS').'</th>';
    $output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    $output .= '</tr></thead><tbody class="list:user user-list">';
	$x = 0;
	if (is_array($wats_ticket_custom_field_values))
	foreach ($wats_ticket_custom_field_values as $key => $table)
	{
		$x = 1;
		$output .= '<tr><td>'.$key.'</td><td>'.esc_html($table['name']).'</td><td>'.esc_html($table['meta_key']).'</td>';
		if (!isset($table['type']) || $table['type'] == 0)
			$output .= '<td>'.__('Text input','WATS').'</td>';
		else if ($table['type'] == 1)
			$output .= '<td>'.__('Drop down selector','WATS').'</td>';
		$output .= '<td>'.$wats_custom_fields_selectors[$table['fsf']].'</td>';
		$output .= '<td>'.$wats_custom_fields_selectors[$table['atef']].'</td>';
		$output .= '<td>'.$wats_custom_fields_selectors[$table['ftdt']].'</td>';
		$output .= '<td>'.$wats_custom_fields_selectors[$table['ftuf']].'</td>';
		$output .= '<td>'.$wats_custom_fields_selectors[$table['ftlf']].'</td>';
		$output .= '<td>'.$wats_custom_fields_selectors[$table['ftltc']].'</td>';
		$output .= '<td><input type="submit" name="wats_edit_custom_field'.$key.'" id="wats_edit_custom_field'.$key.'" value="'.__('Edit','WATS').'" class="button-primary" /></td>';
		$output .= '<td><input type="checkbox" name="customfieldcheck" id="customfieldcheck'.$key.'" value="'.$key.'" /></td>';
		$output .= '</tr>';
	}
	
	if ($x == 0)
        $output .= '<tr valign="middle"><td colspan="10" style="text-align:center">'.__('No entry','WATS').'</td></tr>';
	
	$output .= '</tbody></table>';

	return $output;
}

/**************************************************************************/
/*                                                                        */
/* Fonction de préparation de l'affichage des sélecteurs de custom fields */
/*                                                                        */
/**************************************************************************/

function wats_options_display_custom_fields_selectors($table,$id,$label)
{
	global $wats_custom_fields_selectors;
	
	$output = '<tr><th><label>'.$label.'</label></th><td>'.wats_options_display_custom_fields_selectors_field($table,$id,NULL).'</td></tr>';

	return $output;
}

/********************************************************/
/*                                                      */
/* Fonction d'affichage des sélecteurs de custom fields */
/*                                                      */
/********************************************************/

function wats_options_display_custom_fields_selectors_field($table,$id,$selection)
{
	global $wats_custom_fields_selectors;
	
	$output = '<select name="'.$id.'" id="'.$id.'" size="1">';
	foreach ($table as $key)
	{
        $output .= '<option value="'.$key.'" ';
		if ($selection !== NULL && $selection == $key)
			$output .= 'selected';
		$output .= '>'.esc_html($wats_custom_fields_selectors[$key]).'</option>';
	}
	$output .= '</select>';

	return $output;
}



/*****************************************************************/
/*                                                               */
/* Fonction d'affichage de l'interface d'entrée de custom fields */
/*                                                               */
/*****************************************************************/

function wats_options_display_custom_fields_interface()
{
	
	$output = '<br /><input type="submit" class="button-primary" id="idsupcustomfields" value="'.__('Remove selected custom fields','WATS').'" /><div id="resultsupcustomfields"></div><br />';

	$output .= '<table class="wats-form-table" cellspacing="1" cellpadding="1">';
	$output .= '<tr><th><label>'.__('Custom field display name','WATS').'</label></th><td><input type="text" id="customfieldsdisplayname" size=30 /></td></tr>';
	$output .= '<tr><th><label>'.__('Custom field meta key (DB) identifier','WATS').'</label></th><td><input type="text" id="customfieldsmetakey" size=30 /></td></tr>';
	$output .= '<tr><th><label>'.__('Custom field type','WATS').'</label></th><td>';
	$output .= '<select name="wats_custom_field_type" id ="wats_custom_field_type" size="1"><option value="0">'.__('Text input','WATS').'</option><option value="1">'.__('Drop down selector','WATS').'</option></select></td></tr>';
	$output .= wats_options_display_custom_fields_selectors(array(0,4,5),'fsf',__('Frontend submission form','WATS'));
	$output .= wats_options_display_custom_fields_selectors(array(0,1,2,3,4,5),'atef',__('Admin ticket edition page','WATS'));
	$output .= wats_options_display_custom_fields_selectors(array(0,1,2),'ftdt',__('Frontend ticket display template','WATS'));
	$output .= wats_options_display_custom_fields_selectors(array(0,4,5),'ftuf',__('Frontend ticket update form','WATS'));
	$output .= wats_options_display_custom_fields_selectors(array(0,1,2),'ftlf',__('Frontend ticket listing filter','WATS'));
	$output .= wats_options_display_custom_fields_selectors(array(0,1,2),'ftltc',__('Frontend ticket listing table column','WATS'));
	$output .= '</table><br />';
	$output .= '<input type="submit" id="idaddcustomfields" value="'.__('Add this custom field','WATS').'" class="button-primary" /><div id="resultaddcustomfields"></div>';

	return $output;
}

/*********************************************************************************************/
/*                                                               					         */
/* Fonction ajax de récupération de la table des valeurs d'un custom fields de type selector */
/*                                                                                           */
/*********************************************************************************************/

function wats_admin_get_custom_fields_selector_values_table()
{
	global $wats_settings;
	
	$idvalue = $_POST['idvalue'];
	
	if (!current_user_can('administrator'))
		die('-1');
	
	check_ajax_referer('update-wats-options');
	
	wp_cache_flush();
	wats_load_settings();
	
	$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
	if (isset($wats_ticket_custom_field_values[$idvalue]))
	{
		$table = $wats_ticket_custom_field_values[$idvalue];
		if (isset($table['type']) && $table['type'] == 1)
			$message_result = array('id' => wats_admin_display_custom_fields_selector_values_table($idvalue),'success' => "TRUE", 'error' => '');
		else
			$message_result = array('id' => $idvalue,'success' => "FALSE", 'error' => __("Error : not a selector custom field!",'WATS'));
	}
	else
	{
		$message_result = array('id' => $idvalue,'success' => "FALSE", 'error' => __("Error : entry not existing!",'WATS'));
	}
	
	echo json_encode($message_result);

	exit;
}

/************************************************************************************/
/*                                                               					*/
/* Fonction d'affichage de la table des valeurs d'un custom fields de type selector */
/*                                                                                  */
/************************************************************************************/

function wats_admin_display_custom_fields_selector_values_table($id)
{
	global $wats_settings;
	
	$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];

	$output = '<table class="widefat" cellspacing="0" id="custom_fields_selector_values_table" style="text-align:center;"><thead><tr class="thead">';
	$output .= '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Value','WATS').'</th>';
	$output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Default','WATS').'</th>';
    $output .= '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    $output .= '</tr></thead><tbody class="list:user user-list">';
	$x = 0;
	$alt = false;
	if (isset($wats_ticket_custom_field_values[$id]))
	{
		$table = $wats_ticket_custom_field_values[$id];
		if (isset($table['type']) && $table['type'] == 1)
		{
			if (isset($table['values']))
			{
				$values = $table['values'];
				$default_value = (isset($table['default_value'])) ? $table['default_value'] : 1;
				foreach ($values as $key => $value)
				{
					$x = 1;
					$output .= '<tr valign="middle"';
					$output .= ($alt == true) ? ' class="alternate"' : '';
					$output .= '>';
					$output .= '<td>'.$key.'</td>';
					$output .= '<td>'.esc_html($value).'</td>';
					$output .= '<td><input type="radio" value="'.$key.'" name="group_default_custom_selector" ';
					$output .= ($default_value == $key) ? 'checked' : '';
					$output .= ' ></td>';
					$output .= '<td><input type="checkbox" name="customselectorcheck" id="customselectorcheck" value="'.$key.'" /></td></tr>';
					$alt = !$alt;
				}
			}
		}
	}
	if ($x == 0)
		$output .= '<tr valign="middle"><td colspan="4" style="text-align:center">'.__('No entry','WATS').'</td></tr>';
	$output .= 	'</tbody></table>';
	
	return $output;
}

/****************************************************/
/*                                                  */
/* Fonction d'affichage des options de notification */
/*                                                  */
/****************************************************/

function wats_options_manage_notification_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("notification_admin_tip");>'.__('Notifications','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="new_ticket_notification_admin"';
	if ($wats_settings['new_ticket_notification_admin'] == 1)
		echo ' checked';
	echo '> '.__('Notify admin by email upon new ticket submission','WATS').'</td></tr><tr><td>';
	echo '<tr><td><input type="checkbox" name="ticket_update_notification_all_tickets"';
	if ($wats_settings['ticket_update_notification_all_tickets'] == 1)
		echo ' checked';
	echo '> '.__('Notify admin by email when ticket is updated. Applies to all tickets and will notify all admins.','WATS').'</td></tr><tr><td>';
	echo '<tr><td><input type="checkbox" name="ticket_update_notification_my_tickets"';
	if ($wats_settings['ticket_update_notification_my_tickets'] == 1)
		echo ' checked';
	echo '> '.__('Notify user by email when ticket is updated. Applies only to tickets originated by the user and will notify only ticket originator, ticket owner and ticket updaters.','WATS').'</td></tr><tr><td>';
	echo '<tr><td><input type="checkbox" name="ticket_notification_bypass_mode"';
	if ($wats_settings['ticket_notification_bypass_mode'] == 1)
		echo ' checked';
	echo '> '.__('Enable local user profile notifications options to allow bypass of global options.','WATS').'</td></tr><tr><td>';
	echo '<tr><td><input type="checkbox" name="ticket_notification_custom_list"';
	if ($wats_settings['ticket_notification_custom_list'] == 1)
		echo ' checked';
	echo '> '.__('Enable per ticket custom mail list for update notification.','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="notification_admin_tip">';
	echo __('Check the options according to the notifications you want the system to send to users after specific events happened. ','WATS');
	echo __('If the option is enabled here, then by default, it will be enabled for the user but administrators can disable it under user profile. ','WATS');
	echo __('When a new user is added, the profile option is disabled by default. ','WATS').'<br /><br />';
	echo __('If the option is disabled here, then it will be disabled for everybody and it couldn\'t be enabled individually. ','WATS');
	echo __('The update notification is fired upon the following events : new comment added to a ticket, ownership, priority, status or type change in the ticket edition admin page.','WATS').'<br /><br />';
	echo __('These are global options which can be enabled or disabled individually under user profile if the bypass option is set. ','WATS');
	echo __('If the bypass option isn\'t set, only global notifications options will be relevant and user profile options couldn\'t be modified. ','WATS');
	echo __('Warning : with these options enabled, the system may send a lot of emails, especially if you have many users. So please make sure that you really understand the implications before enabling these.','WATS').'<br /><br />';
	echo __('The latest option allows admins to define a mailing list for each ticket so that specific email addresses can be notified of updates.','WATS');
	echo '</div></td></tr></table><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("notification_signature_tip");>'.__('Mail notifications signature','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><textarea id="notification_signature" name="notification_signature" cols="40" rows="5">';
	echo esc_html(str_replace(array('\r\n','\r','<br />'),"\n",html_entity_decode(stripslashes($wats_settings['notification_signature']))));
	echo '</textarea></td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="notification_signature_tip">';
	echo __('Enter the signature to be put into every notification email sent by the system.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("wats_source_email_tip");>'.__('Source email address for notifications','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="source_email_address"';
	if ($wats_settings['source_email_address'] == 1)
		echo ' checked';
	echo '> '.__('Use users email address as source address','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="wats_source_email_tip">';
	echo __('Check this option if you want notification messages to use the user email as the source address. Otherwise, the global wordpress email address will be used.','WATS').'</div></td></tr></table><br />';

	echo '<br /><h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("notification_rule_tip");>'.__('Ticket notification rules','WATS').' :</a></h3><br />';
	echo '<table class="widefat" cellspacing="0" id="tablerules" style="text-align:center;"><thead><tr class="thead">';
	echo '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Rule','WATS').'</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Mailing list','WATS').'</th>';
    echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    echo '</tr></thead><tbody class="list:user user-list">';
    wats_admin_display_notification_rules_list();
	wats_admin_add_notification_rules_interface('resultsuprule','resultaddrule','idsuprule','idaddrule','Rule','idrule');
	echo '<br />';
	wats_options_premium_only();
	echo '<br /><br /><div class="wats_tip" id="notification_rule_tip">';
	echo __('Associate specific priority, status and type with an email distribution list. Those will get a mail when a new ticket is raised or an existing ticket is updated with the specified values.','WATS').'<br /><br />';
	echo __('Warning : if you enter multiple email addresses, please separate them with a comma ",".','WATS');
	echo '</div>';
	
	return;
}

/**************************************************************/
/*                                                            */
/* Fonction d'affichage des options de soumission des tickets */
/*                                                            */
/**************************************************************/

function wats_options_manage_ticket_submission_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns, $current_user;

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("ticket_edition_media_upload_tip");>'.__('Media upload','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="ticket_edition_media_upload"';
	if ($wats_settings['ticket_edition_media_upload'] == 1)
		echo ' checked';
	echo '> '.__('Allow media upload on ticket creation and edition pages','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="ticket_edition_media_upload_tip">';
	echo __('Check this option if you want to allow media upload while creating and editing tickets. This will allow users to attach media files to tickets.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("ticket_edition_media_upload_tabs_tip");>'.__('Media upload tabs','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="ticket_edition_media_upload_tabs"';
	if ($wats_settings['ticket_edition_media_upload_tabs'] == 1)
		echo ' checked';
	echo '> '.__('Allow media library browsing during media upload','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="ticket_edition_media_upload_tabs_tip">';
	echo __('Check this option if you want to allow media library browsing during media upload while creating and editing tickets. This will allow users to view the library and insert files directly from it.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("guestlist_tip");>'.__('Shared guest user','WATS').' : </a></h3>';
	echo '<table class="wats-form-table"><tr><td>'.__('User','WATS').' : ';
	if ($wats_settings['drop_down_user_selector_format'] == 0)
	{
		echo '<select name="guestlist" id="guestlist" class="wats_select">';
		$userlist = wats_build_user_list(__("None",'WATS'),'edit_posts');
		foreach ($userlist AS $userlogin => $username)
		{
			if ($current_user->user_login !== $userlogin)
			{
				echo '<option value="'.$userlogin.'" ';
				if ($userlogin == $wats_settings['wats_guest_user']) echo 'selected';
					echo '>'.$username.'</option>';
			}
		}
		echo '</select></td></tr><tr><td>';
	}
	else
	{
		if ($wats_settings['wats_guest_user'] === -1)
		{
			$guest_user = __('None','WATS');
			$guest_user_hidden = -1;
		}
		else
		{
			$guest_user = get_user_by('login',$wats_settings['wats_guest_user']);
			$guest_user = wats_build_formatted_name($guest_user->ID);
			$guest_user = $guest_user[$wats_settings['wats_guest_user']];
			$guest_user_hidden = $wats_settings['wats_guest_user'];
		}
		echo '<input class="ui-autocomplete-input" type="text" name="guestlist_ac" id="guestlist_ac" value="'.esc_attr($guest_user).'" />';
		echo '<input type="hidden" name="guestlist" id="guestlist" value="'.esc_attr($guest_user_hidden).'" /></td></tr><tr><td>';
	}
	echo '<div class="wats_tip" id="guestlist_tip">';
	echo __('The shared guest user is a user that must have at least contributor user level. This user will only have access to the ticket creation page on the admin side. You can share the guest user login/password with your visitors so that they can submit tickets without having to register first. This is a shared account.','WATS');
	echo '</div></td></tr></table><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("frontendsubmitformaccess_tip");>'.__('Frontend submission form access','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="radio" name="group5" value="0" ';
	echo ($wats_settings['frontend_submit_form_access'] == 0) ? 'checked' : '';
	echo '>'.__('Disable frontend ticket submission form','WATS').' </td></tr>';
	echo '<tr><td><input type="radio" name="group5" value="1" ';
	echo ($wats_settings['frontend_submit_form_access'] == 1) ? 'checked' : '';
	echo '>'.__('Enable frontend ticket submission form for any visitor with a valid email address','WATS').'</td></tr>';
	echo '<tr><td><input type="radio" name="group5" value="2" ';
	echo ($wats_settings['frontend_submit_form_access'] == 2) ? 'checked' : '';
	echo '>'.__('Enable frontend ticket submission form for registered users only','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="frontendsubmitformaccess_tip">';
	echo __('Set this option to allow users to use a ticket submission form in the frontend to submit new tickets.','WATS');
	echo '<br /><br />'.__('Warning : if option is selected, users will have the opportunity to submit tickets without being authenticated. This could result in large amount of SPAM.','WATS').'</div></td></tr></table><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("frontendsubformstatus_tip");>'.__('Frontend submission form ticket status','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="radio" name="group6" value="0" ';
	echo ($wats_settings['frontend_submit_form_ticket_status'] == 0) ? 'checked' : '';
	echo '>'.__('All tickets submitted will be in \'pending\' status','WATS').' </td></tr>';
	echo '<tr><td><input type="radio" name="group6" value="1" ';
	echo ($wats_settings['frontend_submit_form_ticket_status'] == 1) ? 'checked' : '';
	echo '>'.__('Tickets from unauthenticated users will be submitted in \'pending\' status and tickets from authenticated users will be in \'publish\' status','WATS').'</td></tr>';
	echo '<tr><td><input type="radio" name="group6" value="2" ';
	echo ($wats_settings['frontend_submit_form_ticket_status'] == 2) ? 'checked' : '';
	echo '>'.__('Tickets from unauthenticated users will be submitted in \'pending\' status and tickets from authenticated users will be set according to user level capability','WATS').'</td></tr>';
	echo '<tr><td><input type="radio" name="group6" value="3" ';
	echo ($wats_settings['frontend_submit_form_ticket_status'] == 3) ? 'checked' : '';
	echo '>'.__('All tickets submitted will be in \'publish\' status','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="frontendsubformstatus_tip">';
	echo __('Set this option to define the ticket publications status upon ticket submission. It is advisable to set unauthenticated users tickets status to \'pending\' to allow admin moderation before publication and limit SPAM.','WATS');
	echo '</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("frontendsubformsubmission_tip");>'.__('Frontend submission form successfull submission','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="fsf_success_init" ';
	echo ($wats_settings['fsf_success_init'] == 1) ? 'checked' : '';
	echo '> '.__('Reinitiliaze the form upon successfull submission','WATS').' </td></tr>';
	echo '<tr><td>'.__('Redirect user to this URL upon successfull submission','WATS').': <input type="text" name="fsf_success_redirect_url" id="fsf_success_redirect_url" size="30" value="'.esc_attr($wats_settings['fsf_success_redirect_url']).'" /></td></tr>';
	echo '<tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="frontendsubformsubmission_tip">';
	echo __('The first option allows to initialize the form upon successfull submission of a ticket. The second option allows to redirect user to success page upon successfull submission.','WATS');
	echo '</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("email_ticket_submission_tip");>'.__('Email ticket submission','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="ms_ticket_submission"';
	if ($wats_settings['ms_ticket_submission'] == 1)
		echo ' checked';
	echo '> '.__('Allow ticket submission through email','WATS').'</td></tr><tr><td>';
	echo '<tr><td>'.__('Server : ','WATS').'<input type="text" name="ms_mail_server" value="'.esc_attr(stripslashes($wats_settings['ms_mail_server'])).'" size=30></td></tr><tr><td>';
	echo '<tr><td>'.__('Port : ','WATS').'<input type="text" name="ms_port_server" value="'.esc_attr(stripslashes($wats_settings['ms_port_server'])).'" size=30></td></tr><tr><td>';
	echo '<tr><td>'.__('Login : ','WATS').'<input type="text" name="ms_mail_address" value="'.esc_attr(stripslashes($wats_settings['ms_mail_address'])).'" size=30></td></tr><tr><td>';
	echo '<tr><td>'.__('Password : ','WATS').'<input type="password" name="ms_mail_password" value="'.esc_attr(stripslashes($wats_settings['ms_mail_password'])).'" size=30></td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="email_ticket_submission_tip">';
	echo __('This feature allows users to submit tickets directly through email. You have to define a secret email on a POP3 server.','WATS');
	echo '<br/><br />'.__('Warning : every email received on this account will result in a ticket. Therefore, make sure that your email address isn\'t known by SPAM robots.','WATS');
	echo '</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("submitformdefaultauthor_tip");>'.__('Default author for unregistered visitors tickets','WATS').' : </a></h3>';
	echo '<table class="wats-form-table"><tr><td>'.__('User','WATS').' : ';
	if ($wats_settings['drop_down_user_selector_format'] == 0)
	{
		echo '<select name="defaultauthorlist" id="defaultauthorlist" class="wats_select">';
		$userlist = wats_build_user_list(0,0);
		foreach ($userlist AS $userlogin => $username)
		{
			echo '<option value="'.$userlogin.'" ';
			if ($userlogin == $wats_settings['submit_form_default_author']) echo 'selected';
				echo '>'.$username.'</option>';
		}
		echo '</select></td></tr><tr><td>';
	}
	else
	{
		$author = get_user_by('login',$wats_settings['submit_form_default_author']);
		$author = wats_build_formatted_name($author->ID);
		echo '<input type="text" name="defaultauthorlist_ac" id="defaultauthorlist_ac" value="'.esc_attr($author[$wats_settings['submit_form_default_author']]).'" />';
		echo '<input type="hidden" name="defaultauthorlist" id="defaultauthorlist" value="'.esc_attr($wats_settings['submit_form_default_author']).'" /></td></tr><tr><td>';
	}
	wats_options_premium_only();
	echo '<div class="wats_tip" id="submitformdefaultauthor_tip">';
	echo __('This option will be used to set the author of tickets submitted through the frontend submit form or through email by unregistered users.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("call_center_ticket_creation_tip");>'.__('Call center ticket creation','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="call_center_ticket_creation"';
	if ($wats_settings['call_center_ticket_creation'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to create a ticket on behalf of any user','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="call_center_ticket_creation_tip">';
	echo __('Check this option if you want to allow admins to create tickets on behalf of any user. This will allow them to set the ticket originator while submitting a new ticket.','WATS').'</div></td></tr></table><br />';
	
	return;
}

/************************************************************/
/*                                                          */
/* Fonction d'affichage des options d'affichage des tickets */
/*                                                          */
/************************************************************/

function wats_options_manage_ticket_display_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("group1_tip");>'.__('Ticket numerotation','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="radio" name="group1" value="0" ';
	echo ($wats_settings['numerotation'] == 0) ? 'checked' : '';
	echo '>'.__('None','WATS').' </td></tr>';
	echo '<tr><td><input type="radio" name="group1" value="1" ';
	echo ($wats_settings['numerotation'] == 1) ? 'checked' : '';
	echo '>'.__('Dated','WATS').' (ex : 090601-00001)</td></tr>';
	echo '<tr><td><input type="radio" name="group1" value="2" ';
	echo ($wats_settings['numerotation'] == 2) ? 'checked' : '';
	echo '>'.__('Numbered','WATS').' (ex : 1)</td></tr><tr><td>';
	echo '<div class="wats_tip" id="group1_tip">';
	echo __('Select the preferred option. Based on this, a number will be associated to a ticket and displayed at the beginning of the title.','WATS').'</div></td></tr></table><br />';
	
	if ($wats_settings['numerotation'] > 0)
	{
		echo '<h3>'.__('Latest ticket ID','WATS').' : '.wats_get_latest_ticket_number().'</h3><br />';
	}
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("wats_home_display_tip");>'.__('Tickets display','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="homedisplay"';
	if ($wats_settings['wats_home_display'] == 1)
		echo ' checked';
	echo '> '.__('Include tickets on homepage together with posts','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="wats_home_display_tip">';
	echo __('Check this option if you want to display tickets on homepage along with usual posts. If the option is unchecked, only posts will be displayed.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("group2_tip");>'.__('Tickets visibility','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="radio" name="group2" value="0" ';
	echo ($wats_settings['visibility'] == 0) ? 'checked' : '';
	echo '>'.__('Everybody can see all tickets','WATS').' </td></tr>';
	echo '<tr><td><input type="radio" name="group2" value="1" ';
	echo ($wats_settings['visibility'] == 1) ? 'checked' : '';
	echo '>'.__('Only registered users can see tickets','WATS').'</td></tr>';
	echo '<tr><td><input type="radio" name="group2" value="2" ';
	echo ($wats_settings['visibility'] == 2) ? 'checked' : '';
	echo '>'.__('Only ticket creator and admins can see tickets','WATS').'</td></tr>';
	echo '<tr><td><div style="margin-left:50px;"><input type="checkbox" name="ticket_visibility_read_only_capability"';
	if ($wats_settings['ticket_visibility_read_only_capability'] == 1)
		echo ' checked';
	echo '> '.__('Grant read only access to all tickets for users with "wats_ticket_read_only" capability','WATS').'</div></td></tr>';
		echo '<tr><td><div style="margin-left:50px;"><input type="checkbox" name="ticket_visibility_same_company"';
	if ($wats_settings['ticket_visibility_same_company'] == 1)
		echo ' checked';
	echo '> '.__('Allow user to view and update tickets originated by any user from the same company','WATS').' ';
	wats_options_premium_only_without_div();
	echo '</div></td></tr><tr><td>';
	echo '<div class="wats_tip" id="group2_tip">';
	echo __('Select the preferred option. Tickets access and display in frontend and admin sides will be adjusted based on this option and user privileges.','WATS');
	echo __(' This option will also affect author and owner selectors filters display for the ticket listing table which will be available for everybody, only logged in users or only admins based on the selected option.','WATS').'<br /><br />';
	echo __('If the third option is selected (ticket creator and admins), you can also enable the wats_ticket_read_only capability that you can then assign to specific users under the user profile page to allow them to view all tickets with a read only access (no edition, no update).','WATS');
	echo __(' Then, you can also allow users belonging to a company to view tickets raised by any user from the same company.','WATS');
	echo '</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("comment_menuitem_visibility_tip");>'.__('Comments visibility','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="comment_menuitem_visibility"';
	if ($wats_settings['comment_menuitem_visibility'] == 1)
		echo ' checked';
	echo '> '.__('Block comments menu access for users without moderate_comments capability','WATS').'</td></tr>';
	echo '<tr><td><input type="checkbox" name="internal_comment_visibility"';
	if ($wats_settings['internal_comment_visibility'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to submit internal updates to tickets','WATS').' ';
	wats_options_premium_only_without_div();
	echo '</td></tr><tr><td>';
	echo '<div class="wats_tip" id="comment_menuitem_visibility_tip">';
	echo __('Check the first option if you want to prevent users without the comments moderation capability to browse the comments list page (on this page, they could see updates on all tickets).','WATS').'<br />';
	echo __('Check the second option if you want to allow admins to submit internal updates to tickets that will be only visible to admins.','WATS');
	echo '</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("template_selector_tip");>'.__('Template selector','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="radio" name="group_template_selector" value="0" ';
	echo ($wats_settings['template_selector'] == 0) ? 'checked' : '';
	echo '>'.__('Use active theme default template','WATS').' ('.get_template().') </td></tr>';
	if (file_exists(get_stylesheet_directory().'/single-ticket.php'))
		$output = __('custom files available','WATS');
	else
		$output = __('custom files need to be copied first','WATS');
	echo '<tr><td><input type="radio" name="group_template_selector" value="1" ';
	echo ($wats_settings['template_selector'] == 1) ? 'checked' : '';
	echo '>'.__('Use active theme custom template','WATS').' ('.$output.')</td></tr><tr><td>';
	echo '<div class="wats_tip" id="template_selector_tip">';
	echo __('Select the preferred option. Use the custom template if you want to customize the single ticket display page. ','WATS');
	echo __('To achieve this, you need to copy single-ticket.php and comments-ticket.php from WATS theme subdirectory to your active theme directory and then edit these according to your needs.','WATS');
	echo '</div></td></tr></table><br />';

	return;
}

/***********************************************************/
/*                                                         */
/* Fonction d'affichage des options de listing des tickets */
/*                                                         */
/***********************************************************/

function wats_options_manage_ticket_listing_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;

	$wats_ticket_priority = isset($wats_settings['wats_priorities']) ? $wats_settings['wats_priorities'] : 0;
	$wats_ticket_type = isset($wats_settings['wats_types']) ? $wats_settings['wats_types'] : 0;
	$wats_ticket_status = isset($wats_settings['wats_statuses']) ? $wats_settings['wats_statuses'] : 0;
	$wats_ticket_product = isset($wats_settings['wats_products']) ? $wats_settings['wats_products'] : 0;
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("user_selector_format_tip");>'.__('User selector format','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td>'.__('Format : ','WATS').'<input type="text" name="user_selector_format" value="'.esc_attr(stripslashes($wats_settings['user_selector_format'])).'" size=30></td></tr><tr><td>';
	echo '<div class="wats_tip" id="user_selector_format_tip">';
	echo __('Using user meta keys, set the user format you would like to use for user selectors. This format will be applied to all user selectors. If it is empty, the default key "user_login" will be applied. The following user meta keys can be used : user_login, ','WATS').wats_get_list_of_user_meta_keys(0);
	echo '<br/><br />'.__('Warning : you need to make sure that the combination of keys used will make each entry unique and different from each other. Therefore, it is a good idea to use user_login as this key is unique for each user.','WATS');
	echo '</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("user_selector_order_tip");>'.__('User selector order','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td>'.__('Sort by','WATS').' : <select name="user_selector_order_1" id="user_selector_order_1" size="1">';
	$metakeylist = wats_get_list_of_user_meta_keys(1);
	foreach ($metakeylist AS $metakey)
	{
        echo '<option value="'.$metakey.'" ';
        if ($metakey == $wats_settings['user_selector_order_1']) echo 'selected';
			echo '>'.$metakey.'</option>';
	}
	echo '</select></td></tr><tr><td>'.__('And then','WATS').' : <select name="user_selector_order_2" id="user_selector_order_2" size="1">';
	foreach ($metakeylist AS $metakey)
	{
        echo '<option value="'.$metakey.'" ';
        if ($metakey == $wats_settings['user_selector_order_2']) echo 'selected';
			echo '>'.$metakey.'</option>';
	}
	echo '</select></td></tr><tr><td>';
	echo '<div class="wats_tip" id="user_selector_order_tip">';
	echo __('Select the meta keys used to sort the user selectors.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("filter_ticket_listing_tip");>'.__('Ticket author user meta key selector for ticket listing filtering','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="filter_ticket_listing"';
	if ($wats_settings['filter_ticket_listing'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to filter tickets through author user meta key selector','WATS').'</td></tr><tr><td>';
	echo __('Meta key','WATS').' : <select name="metakeylistfilter" id="metakeylistfilter" size="1">';
	$metakeylist = wats_get_list_of_user_meta_keys(1);
	foreach ($metakeylist AS $metakey)
	{
        echo '<option value="'.$metakey.'" ';
        if ($metakey == $wats_settings['filter_ticket_listing_meta_key']) echo 'selected';
			echo '>'.$metakey.'</option>';
	}
	echo '</select></td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="filter_ticket_listing_tip">';
	echo __('Check this option if you want to allow admins to filter tickets through an additionnal selector which will be filled in with meta values attached to the selected meta key.','WATS').'</div></td></tr></table><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("meta_column_ticket_listing_tip");>'.__('Ticket author user meta key column for tickets listing table','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="meta_column_ticket_listing"';
	if ($wats_settings['meta_column_ticket_listing'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to get another column filled with author meta value in the tickets listing table','WATS').'</td></tr><tr><td>';
	echo __('Meta key','WATS').' : <select name="metakeylistcolumn" id="metakeylistcolumn" size="1">';
	foreach ($metakeylist AS $metakey)
	{
        echo '<option value="'.$metakey.'" ';
        if ($metakey == $wats_settings['meta_column_ticket_listing_meta_key']) echo 'selected';
			echo '>'.$metakey.'</option>';
	}
	echo '<option value="user_email" ';
    if ("user_email" == $wats_settings['meta_column_ticket_listing_meta_key']) echo 'selected';
		echo '>user_email</option>';
	echo '</select></td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="meta_column_ticket_listing_tip">';
	echo __('Check this option if you want to allow admins to get another column in the tickets listing table that will be filled in with user meta values attached to the selected meta key.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("ticket_listing_active_columns_tip");>'.__('Active columns in ticket listing table','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	$x = 0;
	$wats_default_ticket_listing_active_columns = $wats_settings['wats_default_ticket_listing_active_columns'];
	foreach ($wats_default_ticket_listing_columns AS $column => $value)
	{
		if ($x == 0)
			echo '<tr>';
		$x++;
		echo '<td><input type="checkbox" name="ticket_listing_active_'.$column.'"';
		if (isset($wats_default_ticket_listing_active_columns[$column]) && $wats_default_ticket_listing_active_columns[$column] == 1)
			echo ' checked';
		echo '> '.__($value,'WATS').'</td>';
		if ($x == 4)
		{
			echo '</tr>';
			$x = 0;
		}
	}
	echo '<tr><td colspan="4"><div class="wats_tip" id="ticket_listing_active_columns_tip">';
	echo __('Select the columns you want to enable in the frontend ticket listing table.','WATS').'</div></td></tr></table><br />';
	wats_options_premium_only();
	echo '<br />';
	
	$wats_default_ticket_listing_default_query = $wats_settings['wats_default_ticket_listing_default_query'];
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("ticket_listing_default_query_tip");>'.__('Default query','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td>'.__('Ticket type','WATS').' : ';
	echo '<select name="wats_select_ticket_type_tl_query" id="wats_select_ticket_type_tl_query" class="wats_select">';
	echo '<option value="0"';
	if ($wats_default_ticket_listing_default_query['type'] == 0)
		echo ' selected ';
	echo '>'.esc_html__('Any','WATS').'</option>';
	if (is_array($wats_ticket_type))
	foreach ($wats_ticket_type as $key => $value)
	{	
		echo '<option value="'.$key.'"';
		if ($wats_default_ticket_listing_default_query['type'] == $key)
			echo ' selected ';	
		echo '>'.esc_html__($value,'WATS').'</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>'.__('Ticket priority','WATS').' : ';
	echo '<select name="wats_select_ticket_priority_tl_query" id="wats_select_ticket_priority_tl_query" class="wats_select">';
	echo '<option value="0"';
	if ($wats_default_ticket_listing_default_query['priority'] == 0)
		echo ' selected ';
	echo '>'.esc_html__('Any','WATS').'</option>';
	if (is_array($wats_ticket_priority))
	foreach ($wats_ticket_priority as $key => $value)
	{	
		echo '<option value="'.$key.'"';
		if ($wats_default_ticket_listing_default_query['priority'] == $key)
			echo ' selected ';	
		echo '>'.esc_html__($value,'WATS').'</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>'.__('Ticket status','WATS').' : ';
	echo '<select name="wats_select_ticket_status_operator_tl_query" id="wats_select_ticket_status_operator_tl_query" class="wats_select">';
	echo '<option value="0"';
	if ($wats_default_ticket_listing_default_query['status_op'] == 0)
		echo ' selected ';
	echo '>==</option>';
	echo '<option value="1"';
	if ($wats_default_ticket_listing_default_query['status_op'] == 1)
		echo ' selected ';
	echo '>!=</option>';
	echo '</select> ';
	echo '<select name="wats_select_ticket_status_tl_query" id="wats_select_ticket_status_tl_query" class="wats_select">';
	echo '<option value="0">'.esc_html__('Any','WATS').'</option>';
	if (is_array($wats_ticket_status))
	foreach ($wats_ticket_status as $key => $value)
	{	
		echo '<option value="'.$key.'"';
		if ($wats_default_ticket_listing_default_query['status'] == $key)
			echo ' selected ';	
		echo '>'.esc_html__($value,'WATS').'</option>';
	}
	echo '</select></td></tr>';

	
	echo '<tr><td>'.__('Ticket product','WATS').' : ';
	echo '<select name="wats_select_ticket_product_tl_query" id="wats_select_ticket_product_tl_query" class="wats_select">';
	echo '<option value="0"';
	if ($wats_default_ticket_listing_default_query['product'] == 0)
		echo ' selected ';
	echo '>'.esc_html__('Any','WATS').'</option>';
	if (is_array($wats_ticket_product))
	foreach ($wats_ticket_product as $key => $value)
	{	
		echo '<option value="'.$key.'"';
		if ($wats_default_ticket_listing_default_query['product'] == $key)
			echo ' selected ';	
		echo '>'.esc_html__($value,'WATS').'</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>'.__('Ticket author','WATS').' : ';
	echo '<select name="wats_select_ticket_author_tl_query" id="wats_select_ticket_author_tl_query" class="wats_select">';
	echo '<option value="0"';
	if ($wats_default_ticket_listing_default_query['author'] == 0)
		echo ' selected ';
	echo '>'.esc_html__('Any','WATS').'</option>';
	echo '<option value="1"';
	if ($wats_default_ticket_listing_default_query['author'] == 1)
		echo ' selected ';
	echo '>'.esc_html__('Current user','WATS').'</option>';
	echo '</select></td></tr>';
	
	echo '<tr><td>'.__('Ticket owner','WATS').' : ';
	echo '<select name="wats_select_ticket_owner_tl_query" id="wats_select_ticket_owner_tl_query" class="wats_select">';
	echo '<option value="0"';
	if ($wats_default_ticket_listing_default_query['owner'] == 0)
		echo ' selected ';
	echo '>'.esc_html__('Any','WATS').'</option>';
	echo '<option value="1"';
	if ($wats_default_ticket_listing_default_query['owner'] == 1)
		echo ' selected ';
	echo '>'.esc_html__('None','WATS').'</option>';
	echo '<option value="2"';
	if ($wats_default_ticket_listing_default_query['owner'] == 2)
		echo ' selected ';
	echo '>'.esc_html__('Current user','WATS').'</option>';
	echo '</select></td></tr>';
	
	echo '<tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="ticket_listing_default_query_tip">';
	echo __('Select the values you want to use for the default query when the ticket listing is loaded in the frontend. Setting values for disabled keys will not have any impact.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("ticket_listing_display_tip");>'.__('Ticket listing display','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="display_list_not_authenticated"';
	if ($wats_settings['display_list_not_authenticated'] == 1)
		echo ' checked';
	echo '> '.__('Display empty ticket list for not authenticated users beyond the login form','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="ticket_listing_display_tip">';
	echo __('Check this option if you want to allow not authenticated users to view empty ticket listing when the ticket visibility requires an authentication.','WATS').'</div></td></tr></table><br />';
	
	return;
}

/********************************************************/
/*                                                      */
/* Fonction d'affichage des options de clés des tickets */
/*                                                      */
/********************************************************/

function wats_options_manage_ticket_keys_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("tickets_tagging_tip");>'.__('Tickets tagging','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="tickets_tagging"';
	if ($wats_settings['tickets_tagging'] == 1)
		echo ' checked';
	echo '> '.__('Allow tickets tagging','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="tickets_tagging_tip">';
	echo __('Check this option if you want to allow tag association to tickets.','WATS').'</div></td></tr></table><br />';

	echo '<h3>'.__('Categories opened to submission','WATS').' :</h3><br />';
	echo '<table class="widefat" cellspacing="0" id="tablecat" style="text-align:center; clear:none; width:auto;"><thead><tr class="thead">';
	echo '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Category','WATS').'</th>';
    echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    echo '</tr></thead><tbody class="list:user user-list">';
    wats_admin_display_options_list('wats_categories','catcheck',0);
	wats_admin_add_category_interface('resultsupcat','resultaddcat','idsupcat','idaddcat','Category','idcat');
	echo '<br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("ticket_keys_selection_tip");>'.__('Ticket keys','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="ticket_type_key_enabled"';
	if ($wats_settings['ticket_type_key_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Type key','WATS').'</td></tr>';
	echo '<tr><td><input type="checkbox" name="ticket_priority_key_enabled"';
	if ($wats_settings['ticket_priority_key_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Priority key','WATS').'</td></tr>';
	echo '<tr><td><input type="checkbox" name="ticket_status_key_enabled"';
	if ($wats_settings['ticket_status_key_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Status key','WATS').'</td></tr>';
	echo '<tr><td><input type="checkbox" name="ticket_product_key_enabled"';
	if ($wats_settings['ticket_product_key_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Product key','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="ticket_keys_selection_tip">';
	echo __('Check the keys you would like to enable. Selected ones will then appear in the ticket creation, edition and listing pages.','WATS').'</div></td></tr></table><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("tickets_custom_fields_tip");>'.__('Custom fields','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="tickets_custom_fields"';
	if ($wats_settings['tickets_custom_fields'] == 1)
		echo ' checked';
	echo '> '.__('Allow custom fields association to tickets','WATS').' '.__('(through custom fields meta box in the admin ticket edition page)','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="tickets_custom_fields_tip">';
	echo __('Check this option if you want to allow custom fields association to tickets.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("tickets_wats_custom_fields_tip");>'.__('Custom fields management','WATS').' : </a></h3>';
	echo '<div id="divticketcustomfieldstable">'.wats_options_display_custom_fields_table().'</div>';
	echo wats_options_display_custom_fields_interface().'<br />';
	wats_options_premium_only();
	echo '<br /><div class="wats_tip" id="tickets_wats_custom_fields_tip">';
	echo __('Add custom fields and set the visibility options for each area according to your needs.','WATS').'<br />';
	echo __('Warning : when you edit an existing entry, if you modify the meta key value, this will break existing mapping in the DB for existing tickets so make sure you understand this while modifying this.','WATS').'</div><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("tickets_wats_custom_fields_selector_tip");>'.__('Custom fields drop down selector values','WATS').' : </a></h3>';
	$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
	echo __('Custom field','WATS').' : <select name="wats_custom_fields_selector" id="wats_custom_fields_selector" size="1">';
	if (is_array($wats_ticket_custom_field_values))
	foreach ($wats_ticket_custom_field_values as $key => $table)
	{
		if (isset($table['type']) && $table['type'] == 1)
			echo '<option value="'.$key.'">'.esc_html($table['name']).'</option>';
	}
	echo '</select><br /><br />';
	echo '<div id="custom_fields_selector_values_table_div">'.__('Please select a key to customize selector values','WATS').'</div><br />';
	wats_admin_add_table_interface('resultsupcustomselector','resultaddcustomselector','idsupcustomselector','idaddcustomselector','Value','idcustomselector');
	wats_options_premium_only();
	echo '<br /><div class="wats_tip" id="tickets_wats_custom_fields_selector_tip">';
	echo __('Add custom values to each custom field drop down selector according to your needs.','WATS').'</div><br />';
	
	echo '<h3>'.__('Ticket types','WATS').' :</h3><br />';
	echo '<table class="widefat" cellspacing="0" id="tabletype" style="text-align:center;"><thead><tr class="thead">';
	echo '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Type','WATS').'</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Default','WATS').'</th>';
    echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    echo '</tr></thead><tbody class="list:user user-list">';
    wats_admin_display_options_list('wats_types','typecheck',$wats_settings['default_ticket_type']);
	wats_admin_add_table_interface('resultsuptype','resultaddtype','idsuptype','idaddtype','Type','idtype');
	
	echo '<h3>'.__('Ticket priorities','WATS').' :</h3><br />';
	echo '<table class="widefat" cellspacing="0" id="tablepriority" style="text-align:center;"><thead><tr class="thead">';
	echo '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Priority','WATS').'</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Default','WATS').'</th>';
    echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    echo '</tr></thead><tbody class="list:user user-list">';
    wats_admin_display_options_list('wats_priorities','prioritycheck',$wats_settings['default_ticket_priority']);
	wats_admin_add_table_interface('resultsuppriority','resultaddpriority','idsuppriority','idaddpriority','Priority','idpriority');
	
	echo '<h3>'.__('Ticket statuses','WATS').' :</h3><br />';
	echo '<table class="widefat" cellspacing="0" id="tablestatus" style="text-align:center;"><thead><tr class="thead">';
	echo '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Status','WATS').'</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Default','WATS').'</th>';
    echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    echo '</tr></thead><tbody class="list:user user-list">';
    wats_admin_display_options_list('wats_statuses','statuscheck',$wats_settings['default_ticket_status']);
	wats_admin_add_table_interface('resultsupstatus','resultaddstatus','idsupstatus','idaddstatus','Status','idstatus');

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("closed_status_selector_tip");>'.__('Closed status','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td>';
	echo __('Status','WATS').' : <select name="closedstatusselector" id="closedstatusselector" size="1">';
	$wats_status = $wats_settings['wats_statuses'];
	foreach ($wats_status AS $key => $value)
	{
        echo '<option value="'.$key.'" ';
        if ($key == $wats_settings['closed_status_id']) echo 'selected';
			echo '>'.$value.'</option>';
	}
	echo '</select></td></tr><tr><td>';
	echo '<div class="wats_tip" id="closed_status_selector_tip">';
	echo __('Select the status associated to the ticket closure.','WATS').'</div></td></tr></table><br />';
	
	echo '<h3>'.__('Ticket products','WATS').' :</h3><br />';
	echo '<table class="widefat" cellspacing="0" id="tableproduct" style="text-align:center;"><thead><tr class="thead">';
	echo '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Product','WATS').'</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Default','WATS').'</th>';
    echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    echo '</tr></thead><tbody class="list:user user-list">';
    wats_admin_display_options_list('wats_products','productcheck',$wats_settings['default_ticket_product']);
	wats_admin_add_table_interface('resultsupproduct','resultaddproduct','idsupproduct','idaddproduct','Product','idproduct');
	
	return;
}

/***********************************************/
/*                                             */
/* Fonction d'affichage des options d'assignation des tickets */
/*                                             */
/***********************************************/

function wats_options_manage_ticket_assign_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("group3_tip");>'.__('Tickets assignment','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="radio" name="group3" value="0" ';
	echo ($wats_settings['ticket_assign'] == 0) ? 'checked' : '';
	echo '>'.__('No assignment possible','WATS').' </td></tr>';
	echo '<tr><td><input type="radio" name="group3" value="1" ';
	echo ($wats_settings['ticket_assign'] == 1) ? 'checked' : '';
	echo '>'.__('Everybody can assign a ticket','WATS').'</td></tr>';
	echo '<tr><td><input type="radio" name="group3" value="2" ';
	echo ($wats_settings['ticket_assign'] == 2) ? 'checked' : '';
	echo '>'.__('Only registered users can assign a ticket','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="group3_tip">';
	echo __('Select the preferred option. Tickets assignment possibilities in frontend and admin sides will be adjusted based on this option and user privileges.','WATS').'</div></td></tr></table><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("group4_tip");>'.__('Target users for tickets assignment','WATS').' :</a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="radio" name="group4" value="0" ';
	echo ($wats_settings['ticket_assign_user_list'] == 0) ? 'checked' : '';
	echo '>'.__('Any registered user','WATS').' </td></tr>';
	echo '<tr><td><input type="radio" name="group4" value="1" ';
	echo ($wats_settings['ticket_assign_user_list'] == 1) ? 'checked' : '';
	echo '>'.__('Ticket originator and admins','WATS').'</td></tr>';
	echo '<tr><td><input type="radio" name="group4" value="2" ';
	echo ($wats_settings['ticket_assign_user_list'] == 2) ? 'checked' : '';
	echo '>'.__('Ticket originator and any user with wats_ticket_ownership capability','WATS').'</td></tr>';
	echo '<tr><td><input type="radio" name="group4" value="3" ';
	echo ($wats_settings['ticket_assign_user_list'] == 3) ? 'checked' : '';
	echo '>'.__('Any user with wats_ticket_ownership capability and admins','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="group4_tip">';
	echo __('Select the preferred option. The list of users a ticket can be assigned to will be adjusted based on this option. The wats_ticket_ownership capability can be granted under user profile by admins.','WATS').'</div></td></tr></table><br />';

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("ticket_assign_role_tip");>'.__('Roles with ticket assignment capability','WATS').' : </a></h3>';
	echo '<table class="wats-form-table"><tr><td>';
	$roles = get_editable_roles();
	foreach ($roles AS $role)
	{
		echo '<input type="checkbox" name="ticket_assignment_'.strtolower($role['name']).'"';
		if (isset($wats_settings['ticket_assignment_'.strtolower($role['name'])]) && $wats_settings['ticket_assignment_'.strtolower($role['name'])] == 1)
			echo ' checked';
		echo '> '.translate_user_role($role['name']).'<br />';
	}
	echo '</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="ticket_assign_role_tip">';
	echo __('Select the roles. Only selected roles would be able to assign tickets. To learn more about users roles, check out this page : ','WATS').'<a href="http://codex.wordpress.org/Roles_and_Capabilities">WP roles and capabilities</a>.</div></td></tr></table><br />';

	return;
}

/***********************************************/
/*                                             */
/* Fonction d'affichage des options de profil utilisateur */
/*                                             */
/***********************************************/

function wats_options_manage_user_profile_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("user_profile_administration_tip");>'.__('User profile administration','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="prevent_user_profile_mail_modification"';
	if ($wats_settings['prevent_user_profile_mail_modification'] == 1)
		echo ' checked';
	echo '> '.__('Prevent regular users (non admins) from modifying their profile email address','WATS').'</td></tr>';
	echo '<tr><td><input type="checkbox" name="user_expiration_date_enabled"';
	if ($wats_settings['user_expiration_date_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to set an expiration date for each user account','WATS').'</td></tr>';
	echo '<tr><td><input type="checkbox" name="profile_country_enabled"';
	if ($wats_settings['profile_country_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to associate each user with a country. Country meta key : ','WATS').'<input type="text" name="country_meta_key_profile" value="'.esc_attr($wats_settings['country_meta_key_profile']).'" size=30></td></tr>';
	echo '<tr><td><input type="checkbox" name="profile_company_enabled"';
	if ($wats_settings['profile_company_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to associate each user with a company name. Company name key : ','WATS').'<input type="text" name="company_meta_key_profile" value="'.esc_attr($wats_settings['company_meta_key_profile']).'" size=30></td></tr>';
	echo '<tr><td><input type="checkbox" name="profile_sla_enabled"';
	if ($wats_settings['profile_sla_enabled'] == 1)
		echo ' checked';
	echo '> '.__('Allow admins to associate each user with a service level agreement (SLA)','WATS').'</td></tr><tr><td>';
	wats_options_premium_only();
	echo '<div class="wats_tip" id="user_profile_administration_tip">';
	echo __('These options provide you additionnal control on the user profile :','WATS').'<br />';
	echo __('- Email option : check this option if you want to prevent regular users from modifying their email address under the user profile page. If checked, only administrators could update it.','WATS').'<br />';
	echo __('- Expiration option : check this option to allow administrators to set an account expiration date for each user. User won\'t be able to authenticate after the expiration date.','WATS').'<br />';
	echo __('- Country option : check this option to allow administrators to associate each user with a country. You can then customize the country meta key.','WATS').'<br />';
	echo __('- Company option : check this option to allow administrators to associate each user with a company name. You can then customize the company name meta key. When this option is enabled, you can create and manage companies through the company management page.','WATS').'<br />';
	echo __('- SLA option : check this option to allow administrators to associate each user with a service level agreement. When this option is enabled, you can assign SLA to companies through the company management page or individually under the user profile page.','WATS').'<br /><br />';
	echo __('Warning : if you modify the meta key names at a point, existing mapping will be lost so it is better to set these originally and then not modify these afterwards.','WATS').'<br />';
	echo '</div></td></tr></table><br />';

	echo '<br /><h3>'.__('Service level agreement list','WATS').' :</h3><br />';
	echo '<table class="widefat" cellspacing="0" id="tablesla" style="text-align:center; clear:none; width:auto;"><thead><tr class="thead">';
	echo '<th scope="col" class="manage-column" width="10%" style="text-align:center;">ID</th>';
	echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('SLA','WATS').'</th>';
    echo '<th scope="col" class="manage-column" style="text-align:center;">'.__('Selection','WATS').'</th>';
    echo '</tr></thead><tbody class="list:user user-list">';
    wats_admin_display_options_list('wats_slas','slacheck',0);
	wats_admin_add_table_interface('resultsupsla','resultaddsla','idsupsla','idaddsla','SLA','idsla');
	echo '<br />';
	wats_options_premium_only();
	
	return;
}

/****************************************************/
/*                                                  */
/* Fonction d'affichage des options de troubleshoot */
/*                                                  */
/****************************************************/

function wats_options_manage_troubleshoot_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("drop_down_user_selector_format_tip");>'.__('Scalability options','WATS').' : </a></h3>';
	echo '<table class="wats-form-table">';
	echo '<tr><td><input type="checkbox" name="drop_down_user_selector_format"';
	if ($wats_settings['drop_down_user_selector_format'] == 1)
		echo ' checked';
	echo '> '.__('Turn drop down user list into text input with auto complete (better for sites with more than 1.000 users)','WATS').'</td></tr><tr><td>';
	echo '<div class="wats_tip" id="drop_down_user_selector_format_tip">';
	echo __('Check this option if you want to turn drop down user list into text input with auto complete.','WATS').'</div></td></tr></table><br />';
	
	$result_users = count_users();
	echo '<h3>'.__('Site configuration details','WATS').'</h3>';
	echo __('Number of users','WATS').' : '.$result_users['total_users'];
	if ($result_users['total_users'] > 1000)
		echo ', '.__('the number of users is huge, you should probably think about enabling the scalability option above to avoid DB overloading.', 'WATS');
	echo '<br /><br />';
	$posts = wats_get_posts_with_shortcode('[WATS_TICKET_SUBMIT_FORM]');
	echo __('Frontend submission form','WATS').' : ';
	if (is_array($posts) && sizeof($posts) > 0)
	{
		echo '<br /><ul style="list-style-type:disc; padding-left:30px;">';
		foreach ($posts as $post)
			echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></li>';
		echo '</ul>';
	}
	else
		echo __('Not configured','WATS').'<br />';
	$posts = wats_get_posts_with_shortcode('[WATS_TICKET_LIST');
	echo __('Frontend ticket listing','WATS').' : ';
	if (is_array($posts) && sizeof($posts) > 0)
	{
		echo '<br /><ul style="list-style-type:disc; padding-left:30px;">';
		foreach ($posts as $post)
			echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></li>';
		echo '</ul>';
	}
	else
		echo __('Not configured','WATS').'<br />';
	
	echo __('WordPress address','WATS').' : '.site_url().'<br />';
	echo __('Site address','WATS').' : '.home_url().'<br />';
	if (wats_compare_url(site_url(),home_url()) == false)
		echo '<span style="color:red;">'.__('The domain part of the URLs is different, this could cause some problems.','WATS').'</span><br />';
	
	return;
}

/****************************************************/
/*                                                  */
/* Fonction d'affichage des options de statistiques */
/*                                                  */
/****************************************************/

function wats_options_manage_stats_options()
{
	global $wpdb, $wats_settings, $wats_default_ticket_listing_columns;

	echo '<h3><a style="cursor:pointer;" title="'.__('Click to get some help!', 'WATS').'" onclick=javascript:wats_invert_visibility("dashboard_stats_widget_level_tip");>'.__('Global statistics dashboard widget roles visibility','WATS').' : </a></h3>';
	echo '<table class="wats-form-table"><tr><td>';
	$roles = get_editable_roles();
	foreach ($roles AS $role)
	{
		echo '<input type="checkbox" name="dashboard_stats_widget_'.strtolower($role['name']).'"';
		if (isset($wats_settings['dashboard_stats_widget_'.strtolower($role['name'])]) && $wats_settings['dashboard_stats_widget_'.strtolower($role['name'])] == 1)
			echo ' checked';
		echo '> '.translate_user_role($role['name']).'<br />';
	}
	echo '</td></tr><tr><td>';
	echo '<div class="wats_tip" id="dashboard_stats_widget_level_tip">';
	echo __('Select the roles. Only selected roles would be able to view the global statistics under the stats widget on the dashboard. To learn more about users roles, check out this page : ','WATS').'<a href="http://codex.wordpress.org/Roles_and_Capabilities">WP roles and capabilities</a>.</div></td></tr></table><br />';
	
	return;
}

/***********************************************/
/*                                             */
/* Fonction d'affichage des options d'accueil */
/*                                             */
/***********************************************/

function wats_options_manage_home_options()
{

	echo __('Just select a menuitem in the right sidebar widget and set the options according to your needs.','WATS').'<br /><br />';
	echo __('You can also browse the documentation on <a href="http://www.ticket-system.net/">official website</a> to get further help.','WATS');

	if (WATS_PREMIUM == false)
	{
		echo '<h3>'.__('Upgrade to Premium release','WATS').' :</h3>';
		echo __('You are currently using the standard release of WATS. This release is free.','WATS');
		echo __(' There is a Premium release available for you. It contains all the features of the standard release plus many advanced features.','WATS');
		echo __(' You can learn more about the premium release and order it on ','WATS').'<a href="http://www.ticket-system.net">'.__('WATS official website','WATS').'</a>.';
	}
	
	echo '<br /><br />'.__('In order to be always updated on the news around WATS, you can :','WATS');
	echo '<ul style="list-style-type:disc;margin-left:40px;"><li><a href="http://www.ticket-system.net">'.__('Subscribe to the newsletter','WATS').'</a></li>';
	echo '<li><a href="https://twitter.com/wpwats" class="twitter-follow-button" data-show-count="false">Follow @wpwats</a>';
	echo '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li></ul>';

	echo __('If you like WATS, please be social and refer it on the social networks :','WATS').'<br /><br />';
	echo '<div style="float:left;"><a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.ticket-system.net/" data-lang="en" data-text="WordPress Advanced Ticket System from" data-related="anywhereTheJavascriptAPI" data-count="vertical">Tweet</a>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>';
	echo '<div style="float:left; margin-left:10px;"><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.ticket-system.net%2F&amp;send=false&amp;layout=box_count&amp;width=65&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=90" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:65px; height:90px;" allowTransparency="true"></iframe></div>';
	echo '<div style="" class="g-plusone" data-size="tall" data-href="http://www.ticket-system.net/"></div>';
	echo '<script type="text/javascript">
		  (function() {
			var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;
			po.src = "https://apis.google.com/js/plusone.js";
			var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);
		  })();
		  </script>';
	echo '<div style="float:left;margin-right:10px;"><script src="//platform.linkedin.com/in.js" type="text/javascript"></script><script type="IN/Share" data-url="http://www.ticket-system.net/" data-counter="top"></script></div>';
	
	return;
}

function wats_options_premium_only()
{
	if (WATS_PREMIUM == false)
		echo '<div style="font-weight:bold;">'.__('Warning : this feature isn\'t available in the standard release. Please upgrade to benefit from it!','WATS').'</div>';

	return;
}

function wats_options_premium_only_without_div()
{
	if (WATS_PREMIUM == false)
		echo '('.__('Warning : this feature isn\'t available in the standard release. Please upgrade to benefit from it!','WATS').')';

	return;
}

/***********************************************/
/*                                             */
/* Fonction d'affichage de la page des options */
/*                                             */
/***********************************************/

function wats_options_admin_menu()
{
	global $wpdb, $wats_version, $wats_settings, $wats_default_ticket_listing_columns;

	if (!current_user_can('administrator'))
		die('-1');
	
	$submenu = isset($_GET['sub']) ? $_GET['sub'] : '';
	$url = explode('&',$_SERVER['REQUEST_URI']);
	$url = $url[0];
	
	if (isset($_POST['save']))
	{
		check_admin_referer('update-wats-options');
		
		wats_load_settings();
		$wats_settings['wats_version'] = $wats_version;
		
		if ($submenu == 'notifications')
		{
			$wats_settings['new_ticket_notification_admin'] = isset($_POST['new_ticket_notification_admin']) ? 1 : 0;
			$wats_settings['ticket_update_notification_all_tickets'] = isset($_POST['ticket_update_notification_all_tickets']) ? 1 : 0;
			$wats_settings['ticket_update_notification_my_tickets'] = isset($_POST['ticket_update_notification_my_tickets']) ? 1 : 0;
			$wats_settings['notification_signature'] = esc_html(preg_replace("/(\r\n|\n|\r)/", "",nl2br($_POST['notification_signature'])));
			$wats_settings['ticket_notification_bypass_mode'] = isset($_POST['ticket_notification_bypass_mode']) ? 1 : 0;
			$wats_settings['source_email_address'] = isset($_POST['source_email_address']) ? 1 : 0;
			$wats_settings['ticket_notification_custom_list'] = isset($_POST['ticket_notification_custom_list']) ? 1 : 0;
		}
		else if ($submenu == 'ticket-submission')
		{
			if (get_user_by('login', $_POST['guestlist']))
				$wats_settings['wats_guest_user'] = $_POST['guestlist'];
			else
				$wats_settings['wats_guest_user'] = -1;

			$wats_settings['ticket_edition_media_upload'] = isset($_POST['ticket_edition_media_upload']) ? 1 : 0;
			$wats_settings['ticket_edition_media_upload_tabs'] = isset($_POST['ticket_edition_media_upload_tabs']) ? 1 : 0;
			$wats_settings['call_center_ticket_creation'] = isset($_POST['call_center_ticket_creation']) ? 1 : 0;
			$wats_settings['frontend_submit_form_access'] = $_POST['group5'];
			$wats_settings['frontend_submit_form_ticket_status'] = $_POST['group6'];
			
			if (get_user_by('login', $_POST['defaultauthorlist']))
				$wats_settings['submit_form_default_author'] = $_POST['defaultauthorlist'];
			else
				$wats_settings['submit_form_default_author'] = wats_get_first_admin_login();

			$wats_settings['ms_ticket_submission'] = isset($_POST['ms_ticket_submission']) ? 1 : 0;
			$wats_settings['ms_mail_server'] = strlen($_POST['ms_mail_server']) ? esc_html(stripslashes($_POST['ms_mail_server'])) : 'mail.example.com';
			$wats_settings['ms_port_server'] = wats_is_numeric(stripslashes($_POST['ms_port_server'])) ? esc_html(stripslashes($_POST['ms_port_server'])) : '110';
			$wats_settings['ms_mail_address'] = wats_is_string(stripslashes($_POST['ms_mail_address'])) ? esc_html(stripslashes($_POST['ms_mail_address'])) : 'login@example.com';
			$wats_settings['ms_mail_password'] = wats_is_string(stripslashes($_POST['ms_mail_password'])) ? esc_html(stripslashes($_POST['ms_mail_password'])) : 'password';
			$wats_settings['fsf_success_init'] = isset($_POST['fsf_success_init']) ? 1 : 0;
			$wats_settings['fsf_success_redirect_url'] = esc_url($_POST['fsf_success_redirect_url']);
		}
		else if ($submenu == 'ticket-display')
		{
			$wats_settings['numerotation'] = $_POST['group1'];
			$wats_settings['visibility'] = $_POST['group2'];
			$wats_settings['wats_home_display'] = isset($_POST['homedisplay']) ? 1 : 0;
			$wats_settings['comment_menuitem_visibility'] = isset($_POST['comment_menuitem_visibility']) ? 1 : 0;
			$wats_settings['ticket_visibility_read_only_capability'] = isset($_POST['ticket_visibility_read_only_capability']) ? 1 : 0;
			$wats_settings['ticket_visibility_same_company'] = isset($_POST['ticket_visibility_same_company']) ? 1 : 0;
			$wats_settings['internal_comment_visibility'] = isset($_POST['internal_comment_visibility']) ? 1 : 0;
			$wats_settings['template_selector'] = $_POST['group_template_selector'];
		}
		else if ($submenu == 'ticket-listing')
		{
			$wats_settings['user_selector_format'] = wats_is_string(stripslashes($_POST['user_selector_format'])) ? esc_html(stripslashes($_POST['user_selector_format'])) : 'user_login';
			$wats_settings['filter_ticket_listing'] = isset($_POST['filter_ticket_listing']) ? 1 : 0;
			$wats_settings['filter_ticket_listing_meta_key'] = $_POST['metakeylistfilter'];
			$wats_settings['meta_column_ticket_listing'] = isset($_POST['meta_column_ticket_listing']) ? 1 : 0;
			$wats_settings['meta_column_ticket_listing_meta_key'] = $_POST['metakeylistcolumn'];
			$wats_settings['user_selector_order_1'] = $_POST['user_selector_order_1'];
			$wats_settings['user_selector_order_2'] = $_POST['user_selector_order_2'];

			$wats_default_ticket_listing_active_columns = array();
			foreach ($wats_default_ticket_listing_columns AS $column => $value)
			{
				$wats_default_ticket_listing_active_columns[$column] = isset($_POST['ticket_listing_active_'.$column]) ? 1 : 0;
			}
			$wats_settings['wats_default_ticket_listing_active_columns'] = $wats_default_ticket_listing_active_columns;
			
			$wats_default_ticket_listing_default_query = array();
			$wats_default_ticket_listing_default_query['type'] = $_POST['wats_select_ticket_type_tl_query'];
			$wats_default_ticket_listing_default_query['priority'] = $_POST['wats_select_ticket_priority_tl_query'];
			$wats_default_ticket_listing_default_query['status_op'] = $_POST['wats_select_ticket_status_operator_tl_query'];
			$wats_default_ticket_listing_default_query['status'] = $_POST['wats_select_ticket_status_tl_query'];
			$wats_default_ticket_listing_default_query['product'] = $_POST['wats_select_ticket_product_tl_query'];
			$wats_default_ticket_listing_default_query['author'] = $_POST['wats_select_ticket_author_tl_query'];
			$wats_default_ticket_listing_default_query['owner'] = $_POST['wats_select_ticket_owner_tl_query'];
			$wats_settings['wats_default_ticket_listing_default_query'] = $wats_default_ticket_listing_default_query;
			$wats_settings['display_list_not_authenticated'] = isset($_POST['display_list_not_authenticated']) ? 1 : 0;
		}
		else if ($submenu == 'ticket-keys')
		{
			$wats_settings['tickets_tagging'] = isset($_POST['tickets_tagging']) ? 1 : 0;
			$wats_settings['tickets_custom_fields'] = isset($_POST['tickets_custom_fields']) ? 1 : 0;
			$wats_settings['closed_status_id'] = $_POST['closedstatusselector'];
			$wats_settings['default_ticket_type'] = isset($_POST['group_default_wats_types']) ? $_POST['group_default_wats_types'] : 0;
			$wats_settings['default_ticket_status'] = isset($_POST['group_default_wats_statuses']) ? $_POST['group_default_wats_statuses'] : 0;
			$wats_settings['default_ticket_priority'] = isset($_POST['group_default_wats_priorities']) ? $_POST['group_default_wats_priorities'] : 0;
			$wats_settings['ticket_product_key_enabled'] = isset($_POST['ticket_product_key_enabled']) ? 1 : 0;
			$wats_settings['ticket_priority_key_enabled'] = isset($_POST['ticket_priority_key_enabled']) ? 1 : 0;
			$wats_settings['ticket_status_key_enabled'] = isset($_POST['ticket_status_key_enabled']) ? 1 : 0;
			$wats_settings['ticket_type_key_enabled'] = isset($_POST['ticket_type_key_enabled']) ? 1 : 0;
			$wats_settings['default_ticket_product'] = isset($_POST['group_default_wats_products']) ? $_POST['group_default_wats_products'] : 0;
			if (isset($_POST['group_default_custom_selector']) && isset($_POST['wats_custom_fields_selector']))
			{
				$wats_ticket_custom_field_values = $wats_settings['wats_ticket_custom_fields'];
				if (isset($wats_ticket_custom_field_values[$_POST['wats_custom_fields_selector']]))
				{
					$table = $wats_ticket_custom_field_values[$_POST['wats_custom_fields_selector']];
					if (isset($table['type']) && $table['type'] == 1 && isset($table['values']))
					{
						$values = $table['values'];
						if (isset($values[$_POST['group_default_custom_selector']]))
						{
							$table['default_value'] = $_POST['group_default_custom_selector'];
							$wats_ticket_custom_field_values[$_POST['wats_custom_fields_selector']] = $table;
							$wats_settings['wats_ticket_custom_fields'] = $wats_ticket_custom_field_values;
						}
					}
				}
			}
		}
		else if ($submenu == 'ticket-assign')
		{
			$wats_settings['ticket_assign'] = $_POST['group3'];
			$wats_settings['ticket_assign_user_list'] = $_POST['group4'];
			$roles = get_editable_roles();
			foreach ($roles AS $role)
			{
				$rolename = strtolower($role['name']);
				$wats_settings['ticket_assignment_'.$rolename] = isset($_POST['ticket_assignment_'.$rolename ]) ? 1 : 0;
			}
		}
		else if ($submenu == 'user-profile')
		{
			$wats_settings['prevent_user_profile_mail_modification'] = isset($_POST['prevent_user_profile_mail_modification']) ? 1 : 0;		
			$wats_settings['profile_country_enabled'] = isset($_POST['profile_country_enabled']) ? 1 : 0;
			$wats_settings['country_meta_key_profile'] = wats_is_string(stripslashes($_POST['country_meta_key_profile'])) ? str_replace(" ","_",esc_html(stripslashes($_POST['country_meta_key_profile']))) : 'country';
			$wats_settings['user_expiration_date_enabled'] = isset($_POST['user_expiration_date_enabled']) ? 1 : 0;
			$wats_settings['profile_company_enabled'] = isset($_POST['profile_company_enabled']) ? 1 : 0;
			$wats_settings['company_meta_key_profile'] = wats_is_string(stripslashes($_POST['company_meta_key_profile'])) ? str_replace(" ","_",esc_html(stripslashes($_POST['company_meta_key_profile']))) : 'company_name';
			$wats_settings['profile_sla_enabled'] = isset($_POST['profile_sla_enabled']) ? 1 : 0;
		}
		else if ($submenu == 'ticket-stats')
		{
			$roles = get_editable_roles();
			foreach ($roles AS $role)
			{
				$rolename = strtolower($role['name']);
				$wats_settings['dashboard_stats_widget_'.$rolename] = isset($_POST['dashboard_stats_widget_'.$rolename ]) ? 1 : 0;
			}
		}
		else if ($submenu == 'ticket-troubleshoot')
		{
			$wats_settings['drop_down_user_selector_format'] = isset($_POST['drop_down_user_selector_format']) ? 1 : 0;
		}
		
		update_option('wats', $wats_settings);
	}
	
	wats_load_settings();
	if (WATS_PREMIUM == true)
		echo '<H2><div style="text-align:center">WATS Premium '.$wats_settings['wats_version'].'</div></H2><br />';
	else
		echo '<H2><div style="text-align:center">WATS '.$wats_settings['wats_version'].'</div></H2><br />';
	
	echo '<form action="" method="post">';
	wp_nonce_field('update-wats-options');
	
	echo '<div style="float:right;">';
	echo '<ul class="wats-options-menu">';
	echo __('WATS Options','WATS');
	echo '<li '.(($submenu == '') ? 'class="selected"': '').'><a href="'.$url.'">'.__('Home','WATS').'</a></li>';
	echo '<li '.(($submenu == 'ticket-submission') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=ticket-submission">'.__('Ticket submission','WATS').'</a></li>';
	echo '<li '.(($submenu == 'ticket-display') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=ticket-display">'.__('Ticket display and visibility','WATS').'</a></li>';
	echo '<li '.(($submenu == 'ticket-listing') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=ticket-listing">'.__('Ticket listing','WATS').'</a></li>';
	echo '<li '.(($submenu == 'ticket-keys') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=ticket-keys">'.__('Ticket keys','WATS').'</a></li>';
	echo '<li '.(($submenu == 'ticket-assign') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=ticket-assign">'.__('Ticket assign','WATS').'</a></li>';
	echo '<li '.(($submenu == 'user-profile') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=user-profile">'.__('User profile','WATS').'</a></li>';
	echo '<li '.(($submenu == 'notifications') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=notifications">'.__('Notifications','WATS').'</a></li>';
	echo '<li '.(($submenu == 'ticket-stats') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=ticket-stats">'.__('Statistics','WATS').'</a></li>';
	echo '<li '.(($submenu == 'ticket-troubleshoot') ? 'class="selected"': '').'><a href="'.$url.'&amp;sub=ticket-troubleshoot">'.__('Troubleshoot','WATS').'</a></li>';
	echo '</ul>';
	
	echo '<br /><ul class="wats-options-menu">';
	echo __('Help','WATS');
	echo '<li>'.__('If you want to get some details about an option, just click on the option title, this will display some inline details.','WATS').'<br /><br />';
	echo __('In the tables, you can directly edit items by clicking on the following icon : ','WATS').'<img src="'.WATS_URL.'img/modify.png" /><br /><br /></li></ul></div>';

	echo '<br />';
	
	if ($submenu == 'notifications')
		wats_options_manage_notification_options();
	else if ($submenu == 'ticket-submission')
		wats_options_manage_ticket_submission_options();
	else if ($submenu == 'ticket-display')
		wats_options_manage_ticket_display_options();
	else if ($submenu == 'ticket-listing')
		wats_options_manage_ticket_listing_options();
	else if ($submenu == 'ticket-keys')
		wats_options_manage_ticket_keys_options();
	else if ($submenu == 'ticket-assign')
		wats_options_manage_ticket_assign_options();
	else if ($submenu == 'user-profile')
		wats_options_manage_user_profile_options();
	else if ($submenu == 'ticket-stats')
		wats_options_manage_stats_options();
	else if ($submenu == 'ticket-troubleshoot')
		wats_options_manage_troubleshoot_options();
	else if ($submenu == '')
		wats_options_manage_home_options();
	
	if ($submenu != '')
	{
		echo '<p class="submit">';
		echo '<input class="button-primary" type="submit" name="save" value="'.__('Save the options','WATS').'" /></p><br />';
	}
	
	echo '</form><br /><br />';
}
?>