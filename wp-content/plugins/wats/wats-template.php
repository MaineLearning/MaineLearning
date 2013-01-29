<?php

/************************************************************/
/*                                                          */
/* Fonction de vérification de l'ouverture des commentaires */
/*                                                          */
/************************************************************/

function wats_get_ticket_update_rights()
{
	global $wats_settings, $current_user, $post;
	
	wats_load_settings();
	
	if ($wats_settings['ticket_status_key_enabled'] == 1 && get_post_meta($post->ID,'wats_ticket_status',true) == wats_get_closed_status_id() && !current_user_can('administrator'))
		return false;
	else if ($wats_settings['visibility'] == 2 && (!is_user_logged_in() || (is_user_logged_in() && !current_user_can('administrator') && $current_user->ID != $post->post_author)))
		return false;

	return true;
}

/****************************************************************************/
/*                                                                          */
/* Fonction de renvoi du message d'erreur pour l'ouverture des commentaires */
/*                                                                          */
/****************************************************************************/

function wats_get_ticket_update_rights_message()
{
	global $wats_settings, $current_user, $post;
	
	wats_load_settings();
	
	$output = '';
	if ($wats_settings['ticket_status_key_enabled'] == 1 && get_post_meta($post->ID,'wats_ticket_status',true) == wats_get_closed_status_id() && !current_user_can('administrator'))
	{
		$output .= '<div id="ticket_is_closed">'.__('The ticket is closed. Only administrators could reopen it.','WATS').'</div>';
	}
	else if ($wats_settings['visibility'] == 2 && (!is_user_logged_in() || (is_user_logged_in() && !current_user_can('administrator') && $current_user->ID != $post->post_author)))
	{
		$output .= '<div id="ticket_is_read_only">'.__('Only admins and ticket author can update this ticket.','WATS').'</div>';
	}
		
	return $output;
}


/*********************************************************/
/*                                                       */
/* Fonction de vérification de la visibilité des tickets */
/*                                                       */
/*********************************************************/

function wats_check_visibility_rights()
{
	global $wats_settings, $current_user, $post;

	if ($wats_settings['visibility'] == 0)
		return true;
	else if ($wats_settings['visibility'] == 1 && is_user_logged_in())
		return true;
	else if ($wats_settings['visibility'] == 2 && is_user_logged_in() && (current_user_can('administrator') || $current_user->ID == $post->post_author || (!is_admin() && $wats_settings['ticket_visibility_read_only_capability'] == 1 && current_user_can('wats_ticket_read_only'))))
		return true;
		
	return false;
}

/********************************************************************************/
/*                                                                              */
/* Fonction de filtrage sur le contenu pour l'affichage de la table des tickets */
/*                                                                              */
/********************************************************************************/

function wats_list_tickets_filter($content)
{
    return (preg_replace_callback(WATS_TICKET_LIST_REGEXP, 'wats_list_tickets_args', $content));
}

/********************************************************************************/
/*                                                                              */
/* Fonction de filtrage des paramètres pour l'affichage de la table des tickets */
/*                                                                              */
/********************************************************************************/

function wats_list_tickets_args($args)
{
	global $wpdb, $post, $wats_ticket_list_shortcode, $wats_settings, $current_user;
	
	$output = '';
	
	if (WATS_PREMIUM == false)
	{
		$output = __('The frontend ticket listing feature is only available in the premium release. Don\'t hesitate to <a href="http://www.ticket-system.net/order/">order it</a>!','WATS');
	}
	
	return ($output);
}

/************************************************************************************************/
/*                                                                                              */
/* Fonction de filtrage sur le contenu pour l'affichage du formulaire de soumission des tickets */
/*                                                                                              */
/************************************************************************************************/

function wats_ticket_submit_form_filter($content)
{
	return (preg_replace_callback(WATS_TICKET_SUBMIT_FORM, 'wats_ticket_submit_form', $content));
}

/****************************************************************/
/*                                                              */
/* Fonction d'affichage du formulaire de soumission des tickets */
/*                                                              */
/****************************************************************/

function wats_ticket_submit_form()
{
	global $current_user, $wats_settings, $wats_frontend_submission_form_shortcode;
	
	$wats_frontend_submission_form_shortcode = true;

	if (WATS_PREMIUM == false)
	{
		$output = __('The frontend submission form feature is only available in the premium release. Don\'t hesitate to <a href="http://www.ticket-system.net/order/">order it</a>!','WATS');
	}
	
	return ($output);
}

/****************************************/
/*                                      */
/* Fonction de vérification d'un ticket */
/*                                      */
/****************************************/

function wats_is_ticket($post)
{
	if (get_post_type($post) == "ticket")
		return true;
	
	return false;
}

/******************************/
/*                            */
/* Fonction d'ajout du footer */
/*                            */
/******************************/

function wats_wp_footer()
{
	if (WATS_PREMIUM == false && is_front_page() && (!is_paged()))
		echo '<div style="text-align:center;">Wordpress advanced <a href="'.WATS_BACKLINK.'">'.WATS_ANCHOR.'</a></div>';
		
	return;
}

/*********************************************************/
/*                                                       */
/* Fonction de redirection de la template pour un ticket */
/*                                                       */
/*********************************************************/

function wats_ticket_template_loader($template)
{
	global $wp_query, $wats_settings;

	if (is_singular() && wats_is_ticket($wp_query->post) == true)
	{
		if (wats_check_visibility_rights())
		{
			if ($wats_settings['template_selector'] == 0)
			{
				if (get_single_template())
					$template = str_replace('single-ticket','single',get_single_template());
				add_filter('the_content','wats_single_ticket_content_filter',10,1);
			}
			else
			{
				if (file_exists(get_stylesheet_directory().'/single-ticket.php')) $template = get_stylesheet_directory().'/single-ticket.php';
				else $template = WATS_THEME_PATH.'/single-ticket.php';
			}
		}
		else
		{
			if (file_exists(get_stylesheet_directory().'/ticket-access-denied.php')) $template = get_stylesheet_directory().'/ticket-access-denied.php';
			else $template = WATS_THEME_PATH.'/ticket-access-denied.php';
		}
	}
	
	return($template);
}

/**************************************************************/
/*                                                            */
/* Fonction d'affichage du message d'accès interdit au ticket */
/*                                                            */
/**************************************************************/

function wats_ticket_access_denied()
{
	global $post;

	if (!is_user_logged_in())
	{
		$output = '<div id="wats_single_ticket_access_denied"><p>'.__('Please authenticate yourself to view this ticket.', 'WATS').'</p>';
		$output .= '<form action="'.site_url().'/wp-login.php" method="post">';
		$output .= '<table class="wats_submit_form_login_table"><tbody>';
		$output .= '<tr><td>'.__('User','WATS').'</td><td><input type="text" class="input" name="log" id="log" style="width:12em;" /></td></tr>';
		$output .= '<tr><td>'.__('Password','WATS').'</td><td><input type="password" class="input" name="pwd" id="pwd" style="width:12em;" /></td></tr>';
		$output .= '<tr><td><input type="submit" name="submit" value="'.__('Log In').'" class="button" /></td>';
		$output .= '<td><input name="rememberme" id="rememberme" type="checkbox" value="forever" /> '.__('Remember Me').'</td></tr></tbody></table>';
		$output .= '<input type="hidden" name="redirect_to" value="'.$_SERVER['REQUEST_URI'].'"/>';
		$output .= '</form></div>';
		echo apply_filters('wats_filter_single_ticket_access_login_form',$output,$post);
	}
	else
	{
		$output = '<div id="wats_single_ticket_access_denied"><blockquote>'.__('Sorry, you don\'t have the rights to browse this ticket.','WATS').' ';
		$output .= __('If you believe that you should have access to it, please contact the website administrator.','WATS').'<blockquote></div>';
		echo apply_filters('wats_filter_single_ticket_access_denied',$output,$post);
	}
	
	return;
}

/****************************************************/
/*                                                  */
/* Fonction de filtrage du contenu du single ticket */
/*                                                  */
/****************************************************/

function wats_single_ticket_content_filter($content)
{
	global $wats_settings, $post;

	$output = '';
	if (wats_check_visibility_rights())
	{
		$output .= '<div id="wats_single_ticket_metas">';
		if ($wats_settings['ticket_priority_key_enabled'] == 1)
			$output .= '<div id="wats_single_ticket_priority" class="wats_priority_'.get_post_meta($post->ID,'wats_ticket_priority',true).'"><label class="wats_label">'.__("Current priority : ",'WATS').'</label>'.wats_ticket_get_priority($post).'</div>';
		if ($wats_settings['ticket_status_key_enabled'] == 1)
			$output .= '<div id="wats_single_ticket_status" class="wats_status_'.get_post_meta($post->ID,'wats_ticket_status',true).'"><label class="wats_label">'.__("Current status : ",'WATS').'</label>'.wats_ticket_get_status($post).'</div>';
		if ($wats_settings['ticket_type_key_enabled'] == 1)
			$output .= '<div id="wats_single_ticket_type" class="wats_type_'.get_post_meta($post->ID,'wats_ticket_type',true).'"><label class="wats_label">'.__("Ticket type : ",'WATS').'</label>'.wats_ticket_get_type($post).'</div>';
		if ($wats_settings['ticket_product_key_enabled'] == 1)
			$output .= '<div id="wats_single_ticket_product" class="wats_product_'.get_post_meta($post->ID,'wats_ticket_product',true).'"><label class="wats_label">'.__("Ticket product : ",'WATS').'</label>'.wats_ticket_get_product($post).'</div>';
		$output .= '<div id="wats_single_ticket_originator"><label class="wats_label">'.__("Ticket originator : ",'WATS').'</label>'.get_the_author().'</div>';
		if (current_user_can('administrator'))
		{
			$ticket_author_name = get_post_meta($post->ID,'wats_ticket_author_name',true);
			if ($ticket_author_name)
				$output .= '<div id="wats_single_ticket_author_name"><label class="wats_label">'.__('Ticket author name : ','WATS').'</label>'.$ticket_author_name.'</div>';
			$ticket_author_email = get_post_meta($post->ID,'wats_ticket_author_email',true);
			if ($ticket_author_email)
				$output .= '<div id="wats_single_ticket_author_email"><label class="wats_label">'.__('Ticket author email : ','WATS').'</label>'.'<a href="mailto:'.$ticket_author_email.'">'.$ticket_author_email.'</a></div>';
			$ticket_author_url = get_post_meta($post->ID,'wats_ticket_author_url',true);
			if ($ticket_author_url)
				$output .= '<div id="wats_single_ticket_author_url"><label class="wats_label">'.__('Ticket author url : ','WATS').'</label>'.'<a href="'.$ticket_author_url.'">'.$ticket_author_url.'</a></div>';
		}
		$output .= '</div>';
		$output = apply_filters('wats_single_ticket_content_metas_output_filter',$output);
		$content = $output.$content;
		remove_filter('the_content','wats_single_ticket_content_filter');
	}
	else
	{
		wp_die(__('You are not allowed to view this ticket.','WATS'));
	}

	return $content;
}

/*************************************************/
/*                                               */
/* Fonction de filtrage pour inclure les tickets */
/*                                               */
/*************************************************/

function wats_posts_where($where)
{
	global $wpdb, $wats_settings, $current_user;
	
	if ((!is_home() || $wats_settings['wats_home_display'] == 1) && (!is_admin()) && (!is_page()) && (!is_search()))
	{
		if ($wats_settings['visibility'] == 0)
			$where = str_replace($wpdb->posts.".post_type = 'post' AND","(".$wpdb->posts.".post_type = 'post' OR ".$wpdb->posts.".post_type = 'ticket') AND", $where);
		else if ($wats_settings['visibility'] == 1 && is_user_logged_in())
			$where = str_replace($wpdb->posts.".post_type = 'post' AND","(".$wpdb->posts.".post_type = 'post' OR ".$wpdb->posts.".post_type = 'ticket') AND", $where);
		else if ($wats_settings['visibility'] == 2 && is_user_logged_in() && (current_user_can('administrator') || ($wats_settings['ticket_visibility_read_only_capability'] == 1 && current_user_can('wats_ticket_read_only'))))
			$where = str_replace($wpdb->posts.".post_type = 'post' AND","(".$wpdb->posts.".post_type = 'post' OR ".$wpdb->posts.".post_type = 'ticket') AND", $where);
		else if ($wats_settings['visibility'] == 2 && is_user_logged_in())
			$where = str_replace($wpdb->posts.".post_type = 'post' AND","(".$wpdb->posts.".post_type = 'post' OR (".$wpdb->posts.".post_type = 'ticket' AND ".$wpdb->posts.".post_author = ".$current_user->ID.")) AND", $where);
	}
	
	if (is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == 'ticket')
	{
		if ($wats_settings['visibility'] == 2 && !current_user_can('administrator'))
		{
			$where = str_replace($wpdb->posts.".post_type = 'ticket' AND",$wpdb->posts.".post_type = 'ticket' AND ".$wpdb->posts.".post_author = ".$current_user->ID." AND", $where);
		}
	}

	if (is_search())
	{
		if ($wats_settings['visibility'] == 1 && !is_user_logged_in())
			$where = str_replace(", 'ticket'","", $where);
		else if ($wats_settings['visibility'] == 2 && is_user_logged_in() && !current_user_can('administrator') && ($wats_settings['ticket_visibility_read_only_capability'] == 0 || !current_user_can('wats_ticket_read_only')))
		{
			$where = str_replace(", 'ticket'","", $where);
			$where .= " OR (".$wpdb->posts.".post_type = 'ticket' AND ".$wpdb->posts.".post_author = ".$current_user->ID.")";
		}
		else if ($wats_settings['visibility'] == 2 && !is_user_logged_in())
		{
			$where = str_replace(", 'ticket'","", $where);
		}
	}

	return($where);
}

/*******************************************************************/
/*                                                                 */
/* Fonction de filtrage pour inclure les tickets dans les archives */
/*                                                                 */
/*******************************************************************/

function wats_get_archives($where)
{
	$where = str_replace( " post_type = 'post' AND", " (post_type = 'post' OR post_type = 'ticket') AND", $where);

	return($where);
}

/****************************************************************************/
/*                                                                          */
/* Fonction de redirection de la template pour les commentaires d'un ticket */
/*                                                                          */
/****************************************************************************/

function wats_comments_template($template)
{
	global $wp_query, $wats_settings;

	if (wats_is_ticket($wp_query->post) == true)
	{
		if (wats_check_visibility_rights())
		{
			if ($wats_settings['template_selector'] == 0)
			{
				add_filter('comments_open','wats_ticket_comments_open',10,2);
				add_action('comment_form_comments_closed','wats_ticket_comments_closed',10);
			
				if (wats_get_ticket_update_rights() == true)
					add_filter('comment_form_field_comment','wats_comment_form_after_fields',10,1);
			}
			else
			{
				if (file_exists(get_stylesheet_directory().'/comments-ticket.php')) $template = get_stylesheet_directory().'/comments-ticket.php';
				else $template = WATS_THEME_PATH.'/comments-ticket.php';
			}
		}
		else
		{
			wp_die(__('You are not allowed to view this ticket.','WATS'));
		}
	}

	add_filter('comment_class','wats_comment_class');
	
	return($template);
}

/****************************************************************************/
/*                                                                          */
/* Fonction de fermeture des commentaires WP */
/*                                                                          */
/****************************************************************************/

function wats_ticket_comments_open($open,$post_id)
{
	if (wats_get_ticket_update_rights() == false)
		return false;
	else
		return true;
}

/****************************************************************************/
/*                                                                          */
/* Fonction d'affichage de la raison de la fermeture des commentaires sur le ticket */
/*                                                                          */
/****************************************************************************/

function wats_ticket_comments_closed()
{
	echo apply_filters('wats_get_ticket_update_rights_message_filter',wats_get_ticket_update_rights_message());
	
	return;
}

/****************************************************************************/
/*                                                                          */
/* Fonction d'affichage des tickets metas dans le formulaire des commentaires */
/*                                                                          */
/****************************************************************************/

function wats_comment_form_after_fields($args)
{
	global $post;
	
	wats_ticket_details_meta_box($post);
	
	return $args;
}

/************************************/
/*                                  */
/* Fonction d'ajout d'une classe CSS pour les commentaires internes */
/*                                  */
/************************************/

function wats_comment_class($classes)
{

	if (get_comment_meta(get_comment_ID(),'wats_internal_update',true) == 1)
	{
		$classes[] = 'wats_internal_update';
	}
	
	return $classes;
}

/************************************/
/*                                  */
/* Fonction d'ajout d'une taxonomie */
/*                                  */
/************************************/

function wats_register_taxonomy()
{
	global $wats_settings;

	$taxonomies[] =  'category';
	if ($wats_settings['tickets_tagging'] == 1)
		$taxonomies[] = 'post_tag';

	$plugin_url = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) .'/';
	$labels = array('name' => __('Tickets','WATS'),
					'singular_name' => __('ticket','WATS'),
					'add_new' => __('Add New','WATS'),
					'add_new_item' => __('Add New Ticket','WATS'),
					'edit_item' => __('Edit Ticket','WATS'),
					'new_item' => __('New Ticket','WATS'),
					'view_item' => __('View Ticket','WATS'),
					'search_items' => __('Search Ticket','WATS'),
					'not_found' =>  __('No tickets found','WATS'),
					'not_found_in_trash' => __('No tickets found in Trash','WATS'), 
					'parent_item_colon' => '');
	$args = array('labels' => $labels,
				  'public' => true,
				  'publicly_queryable' => true,
				  'show_ui' => true, 
				  'query_var' => true,
				  'rewrite' => true,
				  'capability_type' => 'post',
				  'hierarchical' => false,
				  'menu_position' => null,
				  'menu_icon' => $plugin_url.'img/support.png',
				  'supports' => array('title','editor','comments'),
				  'register_meta_box_cb' => 'wats_ticket_meta_boxes',
				  'taxonomies' => $taxonomies);
	register_post_type('ticket',$args);
	
	return;
}

/*********************************************************/
/*                                                       */
/* Fonction de calcul du nombre d'éléments par catégorie */
/*                                                       */
/*********************************************************/

function wats_update_ticket_term_count($terms)
{
	global $wpdb;
 
    foreach ((array) $terms as $term)
	{
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status = 'publish' AND (post_type = 'ticket' OR post_type = 'post') AND term_taxonomy_id = %d", $term));
        $wpdb->update($wpdb->term_taxonomy, compact('count'), array('term_taxonomy_id' => $term));
    }
}

/******************************************************/
/*                                                    */
/* Fonction d'ajout du numéro de ticket dans le titre */
/*                                                    */
/******************************************************/

function wats_title_insert_ticket_number($title, $postID = 0)
{
	global $wats_printing_inline_data;

	if (get_post_type($postID) == "ticket" && $wats_printing_inline_data == false)
	{
		$value = wats_get_ticket_number($postID);
		
		if ($value)
			return($value." ".$title);
	}
	
	if (is_admin() && $wats_printing_inline_data == false)
		$wats_printing_inline_data = true;

	return ($title);
}

/*******************************************************/
/*                                                     */
/* Fonction de modification des liens previous et next */
/*                                                     */
/*******************************************************/

function wats_ticket_get_previous_next_post_where($where)
{
	global $wats_settings, $current_user;

	$searched_pattern = array(" AND p.post_type = 'ticket'"," AND p.post_type = 'post'");
	if ($wats_settings['visibility'] == 0)
		$where = str_replace($searched_pattern, " AND (p.post_type = 'post' OR p.post_type = 'ticket')", $where);
	else if ($wats_settings['visibility'] == 1 && is_user_logged_in())
		$where = str_replace($searched_pattern, " AND (p.post_type = 'post' OR p.post_type = 'ticket')", $where);
	else if ($wats_settings['visibility'] == 2 && is_user_logged_in() && (current_user_can('administrator') || ($wats_settings['ticket_visibility_read_only_capability'] == 1 && current_user_can('wats_ticket_read_only'))))
		$where = str_replace($searched_pattern, " AND (p.post_type = 'post' OR p.post_type = 'ticket')", $where);
	else if ($wats_settings['visibility'] == 2 && is_user_logged_in())
		$where = str_replace($searched_pattern, " AND (p.post_type = 'post' OR (p.post_type = 'ticket' AND p.post_author = ".$current_user->ID."))", $where);

	return ($where);
}

/***************************************/
/*                                     */
/* Fonction de filtrage des catégories */
/*                                     */
/***************************************/

function wats_list_terms_exclusions($args)
{
	global $wats_settings;
	
	$where = "";
	if (isset($wats_settings['wats_categories']))
	{
		$list = $wats_settings['wats_categories'];
		$catlist = array();
		foreach ($list as $key => $value)
		{
			$catlist[] = $key;
		}
		if (count($catlist))
		{
			$catlist = implode(',',$catlist);
			$where = " AND t.term_id IN ($catlist)";
		}
		else
			$where = " AND t.term_id IN ('')";
	}
	
	return $where;
}

/****************************************************/
/*                                                  */
/* Fonction de filtrage du flux RSS des commentaire */
/*                                                  */
/****************************************************/

function wats_filter_comments_rss($cwhere)
{

	$cwhere = $cwhere." AND post_type != 'ticket'";
	
	return $cwhere;
}

/****************************************************/
/*                                                  */
/* Fonction pour ajouter des colonnes personnalisée */
/*                                                  */
/****************************************************/

function wats_edit_post_column($defaults)
{
	global $wats_settings;

	if ($defaults)
	{
		if ($wats_settings['ticket_type_key_enabled'] == 1)
			$defaults['type'] = __('Type','WATS');
		if ($wats_settings['ticket_priority_key_enabled'] == 1)
			$defaults['priority'] = __('Priority','WATS');
		if ($wats_settings['ticket_status_key_enabled'] == 1)
			$defaults['status'] = __('Status','WATS');
		$defaults['title'] = __('Ticket','WATS');
		if ($wats_settings['ticket_product_key_enabled'] == 1)
			$defaults['product'] = __('Product','WATS');
		unset($defaults['tags']);
		return $defaults;
	}
	
	return;
}

/*****************************************************/
/*                                                   */
/* Fonction pour remplir les colonnes personnalisées */
/*                                                   */
/*****************************************************/

function wats_edit_post_custom_column($column_name, $post_id)
{
	global $wats_settings;
	
	$wats_ticket_priority = isset($wats_settings['wats_priorities']) ? $wats_settings['wats_priorities'] : 0;
	$wats_ticket_type = isset($wats_settings['wats_types']) ? $wats_settings['wats_types'] : 0;
	$wats_ticket_status = isset($wats_settings['wats_statuses']) ? $wats_settings['wats_statuses'] : 0;
	$wats_ticket_product = isset($wats_settings['wats_products']) ?  $wats_settings['wats_products'] : 0;
	
	if ($column_name == 'priority')
	{
		$ticket_priority = get_post_meta($post_id,'wats_ticket_priority',true);
		if (wats_is_numeric($ticket_priority) && isset($wats_ticket_priority[$ticket_priority]))
			echo $wats_ticket_priority[$ticket_priority];
	}
	else if ($column_name == 'status')
	{
		$ticket_status = get_post_meta($post_id,'wats_ticket_status',true);
		if (wats_is_numeric($ticket_status) && isset($wats_ticket_status[$ticket_status]))
			echo $wats_ticket_status[$ticket_status];
	}
	else if ($column_name == 'type')
	{
		$ticket_type = get_post_meta($post_id,'wats_ticket_type',true);
		if (wats_is_numeric($ticket_type) && isset($wats_ticket_type[$ticket_type]))
			echo $wats_ticket_type[$ticket_type];
	}
	else if ($column_name == 'product')
	{
		$ticket_product = get_post_meta($post_id,'wats_ticket_product',true);
		if (wats_is_numeric($ticket_product) && isset($wats_ticket_product[$ticket_product]))
			echo $wats_ticket_product[$ticket_product];
	}
	
	return;
}

/*************************************************/
/*                                               */
/* Fonction de rewrite du title dans le frontend */
/*                                               */
/*************************************************/

function wats_wp_title($title)
{
	global $post;

	if (is_single() && $post->post_type == 'ticket')
	{
		$title = $post->post_title." | ";
	}
	
	return $title;
}

/*************************************************/
/*                                               */
/* Fonction de filtrage des posts rows actions */
/*                                               */
/*************************************************/

function wats_post_row_actions($actions, $post)
{
	global $wats_printing_inline_data;
	
	$wats_printing_inline_data = false;

	return $actions;
}

/*****************************************/
/*                                       */
/* Fonction de filtrage des commentaires */
/*                                       */
/*****************************************/

function wats_get_comments_clauses($clauses)
{
	global $wpdb, $wats_settings, $current_user;

	if (!current_user_can('administrator'))
	{
		$clauses['where'] = str_replace('comment_approved','wp1.comment_approved',$clauses['where']);
		$clauses['where'] = str_replace('comment_post_ID','wp1.comment_post_ID',$clauses['where']);
		$clauses['where'] = str_replace($wpdb->posts.'.post_status','wp3.post_status',$clauses['where']);
		if ($wats_settings['visibility'] == 2 && is_user_logged_in())
		{
			$clauses['join'] = " AS wp1 LEFT JOIN ".$wpdb->posts." AS wp3 ON wp1.comment_post_id = wp3.ID ";
			$clauses['where'] .= " AND wp3.post_status = 'publish' AND (wp3.post_author = ".$current_user->ID." OR wp3.post_type != 'ticket') AND NOT EXISTS (SELECT * FROM ".$wpdb->commentmeta." AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ";
		}
		else if (($wats_settings['visibility'] == 1 && is_user_logged_in()) || $wats_settings['visibility'] == 0)
		{
			$clauses['join'] = " AS wp1 LEFT JOIN ".$wpdb->posts." AS wp3 ON wp1.comment_post_id = wp3.ID";
			$clauses['where'] .= " AND wp3.post_status = 'publish' AND NOT EXISTS (SELECT * FROM ".$wpdb->commentmeta." AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ";
		}
		else
		{
			$clauses['join'] = " AS wp1 LEFT JOIN ".$wpdb->posts." AS wp3 ON wp1.comment_post_id = wp3.ID ";
			$clauses['where'] .= " AND wp3.post_status = 'publish' AND wp3.post_type != 'ticket' AND NOT EXISTS (SELECT * FROM ".$wpdb->commentmeta." AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ";
		}
	}

	return $clauses;
}

function wats_comments_array($comments,$post_id)
{
	if (!current_user_can('administrator'))
	{
		foreach ($comments AS $key => $comment)
		{
			if (get_comment_meta($comment->comment_ID,'wats_internal_update',true) == 1)
			{
				unset($comments[$key]);
			}
		}
	}
	
	return $comments;	
}

function wats_get_comments_number($count, $post_id)
{
	global $wpdb;

	if (!current_user_can('administrator'))
	{
		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->posts." AS wp1 LEFT JOIN ".$wpdb->comments." AS wp2 ON wp1.ID = wp2.comment_post_ID WHERE wp2.comment_post_ID = %d AND NOT EXISTS (SELECT * FROM ".$wpdb->commentmeta." AS wp3 WHERE wp2.comment_ID = wp3.comment_id AND wp3.meta_key = 'wats_internal_update' AND wp3.meta_value = 1)",$post_id));
	}
	
	return $count;
}

?>