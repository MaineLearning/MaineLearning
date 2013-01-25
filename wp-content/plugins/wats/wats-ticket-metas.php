<?php

/*******************************************************/
/*                                                     */
/* Fonction de récupération de la priorité d'un ticket */
/*                                                     */
/*******************************************************/

function wats_ticket_get_priority($post)
{
	global $wats_settings;
	
	$wats_ticket_priority = isset($wats_settings['wats_priorities']) ? $wats_settings['wats_priorities'] : 0;
	
	$priority = get_post_meta($post->ID,'wats_ticket_priority',true);
	
	if (wats_is_numeric($priority) && isset($wats_ticket_priority[$priority]))
		$output = esc_html__($wats_ticket_priority[$priority],'WATS');
	else
		$output = '';
	
	return($output);
}

/************************************************/
/*                                              */
/* Fonction de récupération du type d'un ticket */
/*                                              */
/************************************************/

function wats_ticket_get_type($post)
{
	global $wats_settings;
	
	$wats_ticket_type = isset($wats_settings['wats_types']) ? $wats_settings['wats_types'] : 0;

	$type = get_post_meta($post->ID,'wats_ticket_type',true);
	
	if (wats_is_numeric($type) && isset($wats_ticket_type[$type]))
		$output = esc_html__($wats_ticket_type[$type],'WATS');
	else
		$output = '';
	
	return($output);
}

/**************************************************/
/*                                                */
/* Fonction de récupération du status d'un ticket */
/*                                                */
/**************************************************/

function wats_ticket_get_status($post)
{
	global $wats_settings;
	
	$wats_ticket_status = isset($wats_settings['wats_statuses']) ? $wats_settings['wats_statuses'] : 0;
	
	$status = get_post_meta($post->ID,'wats_ticket_status',true);
	
	if (wats_is_numeric($status) && isset($wats_ticket_status[$status]))
		$output = esc_html__($wats_ticket_status[$status],'WATS');
	else
		$output = '';
	
	return($output);
}

/***************************************************/
/*                                                 */
/* Fonction de récupération du produit d'un ticket */
/*                                                 */
/***************************************************/

function wats_ticket_get_product($post)
{
	global $wats_settings;
	
	$wats_ticket_product = isset($wats_settings['wats_products']) ? $wats_settings['wats_products'] : 0;
	
	$product = get_post_meta($post->ID,'wats_ticket_product',true);
	
	if (wats_is_numeric($product) && isset($wats_ticket_product[$product]))
		$output = esc_html__($wats_ticket_product[$product],'WATS');
	else
		$output = '';
	
	return($output);
}

/****************************************************************/
/*                                                              */
/* Fonction de récupération de la date de fermeture d'un ticket */
/*                                                              */
/****************************************************************/

function wats_ticket_get_closure_date($post)
{
	global $wats_settings;

	$output = mysql2date('M d, Y',get_post_meta($post->ID,'wats_ticket_closure_date',true),false);
	
	return($output);
}

/*******************************************/
/*                                         */
/* Fonction de calcul de l'âge d'un ticket */
/*                                         */
/*******************************************/

function wats_ticket_get_age($post)
{
	global $wpdb,$wats_settings;
	
	$age = array();
	if ($wats_settings['ticket_status_key_enabled'] == 1 && get_post_meta($post->ID,'wats_ticket_status',true) != wats_get_closed_status_id())
	{
		$total = strtotime(date('Y-m-d  h:i:s A')) - strtotime(get_post_time('Y-m-d  h:i:s A',true,$post->ID,false));
		$age['days'] = floor($total / (60 * 60 * 24));
		$age['hours'] = floor(($total - ($age['days'] * 24 * 60 * 60)) / (60 * 60));
		$age['minutes'] = floor(($total - ($age['days'] * 24 * 60 * 60) - ($age['hours'] * 60 * 60)) / 60);
	}
	else if ($wats_settings['ticket_status_key_enabled'] == 1 && get_post_meta($post->ID,'wats_ticket_status',true) == wats_get_closed_status_id())
	{
		$closure_date = get_post_meta($post->ID,'wats_ticket_closure_date',true);
		if (!strlen($closure_date))
		{
			$comments = $wpdb->get_row($wpdb->prepare("SELECT comment_ID, comment_date FROM $wpdb->comments WHERE comment_post_ID = %d ORDER BY comment_date DESC LIMIT 1",$post->ID));
			if (!is_object($comments))
				$closure_date = date('Y-m-d  h:i:s A');
			else
				$closure_date = mysql2date('Y-m-d  h:i:s A',$comments->comment_date,false);
		}
		else
			$closure_date = mysql2date('Y-m-d  h:i:s A',$closure_date,false);
		$total = strtotime($closure_date) - strtotime(get_post_time('Y-m-d  h:i:s A',true,$post->ID,false));
		$age['days'] = floor($total / (60 * 60 * 24));
		$age['hours'] = floor(($total - ($age['days'] * 24 * 60 * 60)) / (60 * 60));
		$age['minutes'] = floor(($total - ($age['days'] * 24 * 60 * 60) - ($age['hours'] * 60 * 60)) / 60);
	}

	return $age;
}

/*************************************************/
/*                                               */
/* Fonction de mise à jour des metas d'un ticket */
/*                                               */
/*************************************************/

function wats_comment_update_meta($comment_id)
{
	global $wats_settings;
	
	wats_load_settings();
	
	$comment = get_comment($comment_id); 
	$status = $comment->comment_approved; 
	$post_id =  $comment->comment_post_ID;
	if ($status !== "spam" && wats_is_ticket($post_id))
	{
		wats_ticket_save_meta($post_id,get_post($post_id),$comment->comment_author_email);
	}

	return;
}

/******************************************************/
/*                                                    */
/* Fonction de preprocessing d'un nouveau commentaire */
/*                                                    */
/******************************************************/

function wats_pre_comment_on_post($comment_post_id)
{
	global $wats_settings, $current_user;
	
	$post = get_post($comment_post_id);
	if ($post->post_type == 'ticket')
	{
		if ($wats_settings['ticket_status_key_enabled'] == 1 && get_post_meta($comment_post_id,'wats_ticket_status',true) == wats_get_closed_status_id() && !current_user_can('administrator'))
			wp_die(__('Sorry, you can\'t update this ticket.','WATS'));
		else if ($wats_settings['visibility'] == 1 && !is_user_logged_in())
			wp_die(__('Sorry, you must be logged in to update this ticket.','WATS'));
		else if ($wats_settings['visibility'] == 2 && (!is_user_logged_in() || (is_user_logged_in() && !current_user_can('administrator') && $current_user->ID != $post->post_author)))
			wp_die(__('Sorry, you don\'t have the rights to update this ticket.','WATS'));
	}
		
	return;
}

/************************************************/
/*                                              */
/* Fonction de sauvegarde des metas d'un ticket */
/*                                              */
/************************************************/

function wats_ticket_save_meta($postID,$post,$comment_author_email = '')
{
	global $wats_settings, $wpdb;

	wats_load_settings();
	
	if ($post->post_type == 'ticket')
	{

		$newticket = 0;

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_status == 'auto-draft' || $post->post_status == 'trash')
		{
			return $postID;
		}
		
		
		$newstatus = -1;
		if ($wats_settings['ticket_status_key_enabled'] == 1 && isset($_POST['wats_select_ticket_status']))
		{
			$status = get_post_meta($postID,'wats_ticket_status',true);

			if (get_post_meta($postID,'wats_ticket_status',true) != wats_get_closed_status_id() || current_user_can('administrator'))
			{
				if ($status != $_POST['wats_select_ticket_status'])
					$newstatus = $_POST['wats_select_ticket_status'];
					
				if ($status == wats_get_closed_status_id() && $newstatus != wats_get_closed_status_id())
					delete_post_meta($postID,'wats_ticket_closure_date');

				if ($_POST['wats_select_ticket_status'] > 0)
					update_post_meta($postID,'wats_ticket_status',$_POST['wats_select_ticket_status']);
					
				if ($newstatus == wats_get_closed_status_id())
					update_post_meta($postID,'wats_ticket_closure_date',current_time('mysql'));
			}
		}

		$newtype = -1;
		if ($wats_settings['ticket_type_key_enabled'] == 1 && isset($_POST['wats_select_ticket_type']))
		{
			$type = get_post_meta($postID,'wats_ticket_type',true);
			if ($type != $_POST['wats_select_ticket_type'])
				$newtype = $_POST['wats_select_ticket_type'];
			
			if ($_POST['wats_select_ticket_type'] > 0)
				update_post_meta($postID,'wats_ticket_type',$_POST['wats_select_ticket_type']);
		}
			
		$newpriority = -1;
		if ($wats_settings['ticket_priority_key_enabled'] == 1 && isset($_POST['wats_select_ticket_priority']))
		{
			$priority = get_post_meta($postID,'wats_ticket_priority',true);
			if ($priority != $_POST['wats_select_ticket_priority'])
				$newpriority = $_POST['wats_select_ticket_priority'];
			
			if ($_POST['wats_select_ticket_priority'] > 0)
				update_post_meta($postID,'wats_ticket_priority',$_POST['wats_select_ticket_priority']);
		}
		
		$newproduct = -1;
		if ($wats_settings['ticket_product_key_enabled'] == 1 && isset($_POST['wats_select_ticket_product']))
		{
			$product = get_post_meta($postID,'wats_ticket_product',true);
			if ($product != $_POST['wats_select_ticket_product'])
				$newproduct = $_POST['wats_select_ticket_product'];
			
			if ($_POST['wats_select_ticket_product'] > 0)
				update_post_meta($postID,'wats_ticket_product',$_POST['wats_select_ticket_product']);
		}
		
		if (!get_post_meta($postID,'wats_ticket_number',true))
		{
			add_post_meta($postID,'wats_ticket_number',wats_get_latest_ticket_number()+1);
			$newticket = 1;
		}
		
		if ($newticket == 1)
		{
			if (!isset($_POST['view']) || (isset($_POST['view']) && $_POST['view'] != 1))
			{
				do_action('wats_ticket_admin_submission_saved_meta',$postID);
			}
		}

		do_action('wats_ticket_saved_meta',$postID);
	}
	
	return;
}

/*****************************************************/
/*                                                   */
/* Fonction de hook durant la sauvegarde d'un ticket */
/*                                                   */
/*****************************************************/

function wats_insert_post_data($data)
{
	global $wats_settings, $current_user;

	if (current_user_can('administrator') && $wats_settings['call_center_ticket_creation'] == 1 && isset($_POST['wats_select_ticket_originator']) && $data['post_type'] == "ticket")
		$data['post_author'] = wats_get_user_ID_from_user_login($_POST['wats_select_ticket_originator']);

	if ($data['post_type'] == "ticket")
		$data['comment_status'] = 'open';
	
	return $data;
}


/************************************************/
/*                                              */
/* Fonction d'ajout des meta boxes dans l'admin */
/*                                              */
/************************************************/

function wats_ticket_meta_boxes()
{
	global $wp_meta_boxes, $wats_settings;

	remove_meta_box('commentsdiv', 'ticket', 'normal');
	remove_meta_box('commentstatusdiv', 'ticket', 'normal');
	add_meta_box('ticketdetailsdiv',__('Ticket details','WATS'),'wats_ticket_details_meta_box','ticket','normal','default',array('view' => 0));
	add_meta_box('categorydiv', __('Categories'), 'post_categories_meta_box', 'ticket', 'side', 'core');
	if ($wats_settings['tickets_custom_fields'] == 1)
		add_meta_box('postcustom', __('Custom Fields'), 'post_custom_meta_box', 'ticket', 'normal', 'core');
	if ($wats_settings['tickets_tagging'] == 1)
		add_meta_box('tagsdiv-post_tag', __('Tags'), 'post_tags_meta_box', 'ticket', 'side', 'core');
	
	return;
}

/***************************************************************/
/*                                                             */
/* Fonction d'affichage de l'historique d'un ticket (meta box) */
/*                                                             */
/***************************************************************/

function wats_ticket_history_meta_box($post)
{

	echo __('Here is the ticket history','WATS');

	return;
}

/***************************************************************/
/*                                                             */
/* Fonction de filtrage des messages dans la page d'édition des tickets */
/*                                                             */
/***************************************************************/

function wats_post_updated_messages($messages)
{

	if ((isset($_GET['post_type']) && $_GET['post_type'] == 'ticket') || (isset($_GET['post']) && get_post_type($_GET['post']) == 'ticket'))
	{
		if (isset($_GET['post']))
		{
			$messages['post'][10] = sprintf(__('Ticket draft updated. Please don\'t forget to submit it when edition is complete! <a target="_blank" href="%s">Preview ticket</a>','WATS'), esc_url(add_query_arg('preview','true',get_permalink($_GET['post']))));
		}
	}
	
	return $messages;
}

/***********************************************************/
/*                                                         */
/* Fonction d'affichage des détails d'un ticket (meta box) */
/* view : 0 (comment form et ticket edit/creation admin)   */
/* view : 1 (ticket creation frontend) 					   */
/*                                                         */
/***********************************************************/

function wats_ticket_details_meta_box($post,$view=0)
{
	global $wats_settings, $current_user, $pagenow;

	if (is_array($view))
		$view = $view['args']['view'];

	$wats_ticket_priority = isset($wats_settings['wats_priorities']) ? $wats_settings['wats_priorities'] : 0;
	$wats_ticket_type = isset($wats_settings['wats_types']) ? $wats_settings['wats_types'] : 0;
	$wats_ticket_status = isset($wats_settings['wats_statuses']) ? $wats_settings['wats_statuses'] : 0;
	$wats_ticket_product = isset($wats_settings['wats_products']) ? apply_filters('wats_product_list_filter',$wats_settings['wats_products']) : 0;
	
	if (is_object($post))
	{
		$ticket_priority = get_post_meta($post->ID,'wats_ticket_priority',true);
		$ticket_status = get_post_meta($post->ID,'wats_ticket_status',true);
		$ticket_type = get_post_meta($post->ID,'wats_ticket_type',true);
		$ticket_owner = get_post_meta($post->ID,'wats_ticket_owner',true);
		$ticket_product = get_post_meta($post->ID,'wats_ticket_product',true);
	}
	else
	{
		$ticket_priority = 0;
		$ticket_status = 0;
		$ticket_type = 0;
		$ticket_owner = 0;
		$ticket_product = 0;
	}
	
	$output = '';
	
	if ($wats_settings['ticket_type_key_enabled'] == 1)
	{
		if ($view == 1)
			$output .= '<div class="wats_select_ticket_type_frontend">';
			
		if ($view == 0 && !is_admin())
			$output .= '<div class="wats_select_ticket_type_frontend_update_form">';
			
		if (is_admin())
			$output.= '<br />';
		$output .= '<label class="wats_label">'.__('Ticket type','WATS').' : </label>';
		$output .= '<select name="wats_select_ticket_type" id="wats_select_ticket_type" class="wats_select">';
		if (is_array($wats_ticket_type))
		foreach ($wats_ticket_type as $key => $value)
		{
			$output .= '<option value='.$key;
			if ($key == $ticket_type || (!$ticket_type && $key == $wats_settings['default_ticket_type']))
				$output .= ' selected';
			$output .= '>'.esc_html__($value,'WATS').'</option>';
		}
		$output .= '</select><br /><br />';
		if ($view == 1 || ($view == 0 && !is_admin()))
			$output .= '</div>';
	}
	
	if ($wats_settings['ticket_priority_key_enabled'] == 1)
	{
		if ($view == 1)
			$output .= '<div class="wats_select_ticket_priority_frontend">';
		
		if ($view == 0 && !is_admin())
			$output .= '<div class="wats_select_ticket_priority_frontend_update_form">';
		
		$output .= '<label class="wats_label">'.__('Ticket priority','WATS').' : </label>';
		$output .= '<select name="wats_select_ticket_priority" id="wats_select_ticket_priority" class="wats_select">';
		if (is_array($wats_ticket_priority))
		foreach ($wats_ticket_priority as $key => $value)
		{
			$output .= '<option value='.$key;
			if ($key == $ticket_priority || (!$ticket_priority && $key == $wats_settings['default_ticket_priority']))
				$output .= ' selected';
			$output .= '>'.esc_html__($value,'WATS').'</option>';
		}
		$output .= '</select><br /><br />';
		
		if ($view == 1 || ($view == 0 && !is_admin()))
			$output .= '</div>';
	}
	
	if ($wats_settings['ticket_status_key_enabled'] == 1)
	{
		if ($view == 1)
			$output .= '<div class="wats_select_ticket_status_frontend">';
		
		if ($view == 0 && !is_admin())
			$output .= '<div class="wats_select_ticket_status_frontend_update_form">';
		
		if (is_admin() && is_object($post) && get_post_meta($post->ID,'wats_ticket_status',true) == wats_get_closed_status_id() && !current_user_can('administrator'))
		{
			$output .= __('Ticket status','WATS').' : '.$wats_ticket_status[wats_get_closed_status_id()].'<br /><br />';
		}
		else
		{
			$output .= '<label class="wats_label">'.__('Ticket status','WATS').' : </label>';
			$output .= '<select name="wats_select_ticket_status" id="wats_select_ticket_status" class="wats_select">';
			if (is_array($wats_ticket_status))
			foreach ($wats_ticket_status as $key => $value)
			{
				$output .= '<option value='.$key;
				if ($key == $ticket_status || (!$ticket_status && $key == $wats_settings['default_ticket_status']))
					$output .= ' selected';
				$output .= '>'.esc_html__($value,'WATS').'</option>';
			}
			$output .= '</select><br /><br />';
		}
		
		if ($view == 1 || ($view == 0 && !is_admin()))
			$output .= '</div>';
	}
	
	if ($wats_settings['ticket_product_key_enabled'] == 1)
	{
		if ($view == 1)
			$output .= '<div class="wats_select_ticket_product_frontend">';
		
		if ($view == 0 && !is_admin())
			$output .= '<div class="wats_select_ticket_product_frontend_update_form">';
		
		$output .= '<label class="wats_label">'.__('Ticket product','WATS').' : </label>';
		$output .= '<select name="wats_select_ticket_product" id="wats_select_ticket_product" class="wats_select">';
		if (is_array($wats_ticket_product))
		foreach ($wats_ticket_product as $key => $value)
		{
			$output .= '<option value='.$key;
			if ($key == $ticket_product || (!$ticket_product && $key == $wats_settings['default_ticket_product']))
				$output .= ' selected';
			$output .= '>'.esc_html__($value,'WATS').'</option>';
		}
		$output .= '</select><br /><br />';
		
		if ($view == 1 || ($view == 0 && !is_admin()))
			$output .= '</div>';
	}
	
	if (is_object($post))
		setup_postdata($post);

	if (is_admin())
		$output .= wp_nonce_field('wats-edit-ticket','_wpnonce_wats_edit_ticket',true,false)."\n";

	if ((is_admin() || $view == 1) && current_user_can('administrator') && $wats_settings['call_center_ticket_creation'] == 1)
	{
		if (is_object($post))
			$selected_login = get_the_author_meta('user_login');
		else
			$selected_login = $current_user->user_login;
	
		$output .= '<div id="wats_div_ticket_originator"><label class="wats_label">'.__('Ticket originator : ','WATS').'</label>';
		
		$userlist = wats_build_user_list(0,0);
		
		$output .= '<select name="wats_select_ticket_originator" id="wats_select_ticket_originator" class="wats_select">';
		foreach ($userlist AS $userlogin => $username)
		{
			$output .= '<option value="'.$userlogin.'" ';
			if ($selected_login == $userlogin) $output .= " selected";
			$output .= '>'.$username.'</option>';
		}
		$output .=  '</select></div>';
	}
	else if (is_admin())
	{
		if ($post->ID)
		{
			$output .= '<label class="wats_label">'.__('Ticket originator : ','WATS').'</label>';
			$output .= get_the_author();
		}
		
	}
		
	if (is_admin())
	{
		$ticket_author_name = get_post_meta($post->ID,'wats_ticket_author_name',true);
		if ($ticket_author_name)
			$output .= '<br /><br /><label class="wats_label">'.__('Ticket author name : ','WATS').'</label>'.$ticket_author_name;
		
		$ticket_author_email = get_post_meta($post->ID,'wats_ticket_author_email',true);
		if ($ticket_author_email)
			$output .= '<br /><br /><label class="wats_label">'.__('Ticket author email : ','WATS').'</label>'.'<a href="mailto:'.$ticket_author_email.'">'.$ticket_author_email.'</a>';
		
		$ticket_author_url = get_post_meta($post->ID,'wats_ticket_author_url',true);
		if ($ticket_author_url)
			$output .= '<br /><br /><label class="wats_label">'.__('Ticket author url : ','WATS').'</label>'.'<a href="'.$ticket_author_url.'">'.$ticket_author_url.'</a>';
		$output .= '<br /><br />';
	}

	
	if ($view == 1)
		return ($output);
	else
		echo $output;
}

?>