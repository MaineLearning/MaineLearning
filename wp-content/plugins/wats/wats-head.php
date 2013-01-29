<?php

/********************************************************/
/*                                                      */
/* Fonction de remplissage des variables globales traduites */
/*                                                      */
/********************************************************/

function wats_translate_global_vars()
{
	global $wats_custom_fields_selectors;

	$wats_custom_fields_selectors = array(0 => __('Hide for any user','WATS'), 
									  1 => __('Read access for any user','WATS'),
									  2 => __('Read access for admins only','WATS'),
									  3 => __('Read access for regular user and write access for admins','WATS'),
									  4 => __('Write access for any user','WATS'),
									  5 => __('Write access for admins only','WATS'));

	return;
}									  

/*****************************************/
/*                                       */
/* Fonction d'accroche dans l'admin head */
/*                                       */
/*****************************************/

function wats_admin_head()
{
	
	return;
}

/*********************************************/
/*                                           */
/* Fonction d'accroche dans le frontend head */
/*                                           */
/*********************************************/

function wats_enqueue_script_frontend()
{
	
	return;
}

/*********************************/
/*                               */
/* Fonction de chargement du css */
/*                               */
/*********************************/

function wats_add_my_stylesheet()
{
    global $post;

    if ((is_object($post) && ($post->post_type == "ticket" || strstr($post->post_content, '[WATS_TICKET_SUBMIT_FORM]') || strstr($post->post_content, '[WATS_TICKET_LIST'))) || is_admin())
	{
	    $plugin_url = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) .'/';
		$myStyleFile = $plugin_url."css/wats.css";
		wp_register_style('wats_css', $myStyleFile); 
		wp_enqueue_style('wats_css');
	}

	return;
}

/*********************************************/
/*                                           */
/* Fonction de chargement des scripts jquery */
/*                                           */
/*********************************************/

function wats_admin_scripts()
{
	$plugin_url = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) .'/';

	wp_enqueue_script('jquery');

	$editableurl = $plugin_url.'js/jquery.editable.js';
	wp_enqueue_script('editable',$editableurl,array('jquery'));

	return;
}

/************************************************************/
/*                                                          */
/* Fonction de gestion de l'utilisateur invité dans l'admin */
/*                                                          */
/************************************************************/

function wats_customize_guest_admin()
{
	global $menu;
	
	foreach ($menu as $key => $value)
	{
		unset($menu[$key]);
	}
	
    if (!empty($_SERVER["REQUEST_URI"]))
		$requesteduri = $_SERVER["REQUEST_URI"];
    else
		$requesteduri = getenv('REQUEST_URI');

	$targeturi = admin_url().'post-new.php?post_type=ticket';
	$subtargeturi = substr_replace($targeturi,'',0,strlen(get_option('siteurl')));
	$result = strpos($requesteduri,$subtargeturi);

	if ($result === false)
		wp_safe_redirect($targeturi);
	
	return;
}

/************************************************/
/*                                              */
/* Fonction pour ajouter les menus dans l'admin */
/*                                              */
/************************************************/

function wats_add_admin_page()
{
	global $wats_settings, $menu, $current_user;

	wats_load_settings();
	$plugin_url = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) .'/';

	add_filter('media_upload_tabs','wats_media_upload_tabs');
	
	if ($current_user->user_login == $wats_settings['wats_guest_user'])
	{
		wats_customize_guest_admin();
	}
	
	if (function_exists('add_options_page'))
	{
		$page = add_options_page(__('Wats Options','WATS'), __('Wats Options','WATS'),'administrator', basename(__FILE__), 'wats_options_admin_menu');
		add_action('admin_print_scripts-'.$page,'wats_options_admin_head');
	}

	if (function_exists('add_menu_page') && function_exists('add_submenu_page'))
	{
		if ($current_user->user_login == $wats_settings['wats_guest_user'])
		{
			add_filter('list_terms_exclusions','wats_list_terms_exclusions');
		}
		else if (current_user_can('edit_posts') == 1)
		{
			if ((current_user_can('moderate_comments') == 0) && ($wats_settings['comment_menuitem_visibility'] == 1))
			{
				unset($menu[25]);
				if (!empty($_SERVER["REQUEST_URI"]))
					$requesteduri = $_SERVER["REQUEST_URI"];
				else
					$requesteduri = getenv('REQUEST_URI');
    
				$destpage = get_option('siteurl').'/wp-admin/index.php';
				$mypos = strpos($requesteduri,'/wp-admin/edit-comments.php');

				if ($mypos !== false)
					wp_safe_redirect($destpage);
			}
			
			if ((isset($_GET['post_type']) && $_GET['post_type'] == 'ticket') || (isset($_GET['post']) && get_post_type($_GET['post']) == 'ticket'))
			{
				add_action('manage_posts_custom_column','wats_edit_post_custom_column', 10, 2);
				add_action('manage_posts_columns','wats_edit_post_column');
				//add_filter('list_terms_exclusions','wats_list_terms_exclusions');
				add_action('admin_print_scripts','wats_ticket_edit_admin_head');
	
				if ($wats_settings['ticket_edition_media_upload'] == 0)
					remove_action('media_buttons','media_buttons');
				
				if (isset($_GET['post']))
				{
					global $post;
					$post = get_post($_GET['post']);
					if (wats_check_visibility_rights() == false)
					{
						unset($post);
						wp_die( __('You are not allowed to edit this item.') );
					}
				}
			}
		}
	}
	
	add_action('show_user_profile', 'wats_admin_edit_user_profile');
    add_action('edit_user_profile', 'wats_admin_edit_user_profile');
    add_action('profile_update', 'wats_admin_save_user_profile');
		
	return;
}

/***********************************************************/
/*                                                         */
/* Fonction de chargement des scripts de la page d'options */
/*                                                         */
/***********************************************************/

function wats_options_admin_head()
{
	wats_admin_scripts();
?>
<script type="text/javascript">
	var watsmsg = Array();
   	watsmsg[0] = "<?php _e('Error : there is nothing to remove!','WATS'); ?>";
	watsmsg[1] = "<?php _e('Error : please select an entry to remove!','WATS'); ?>";
	watsmsg[2] = "<?php _e('No entry','WATS'); ?>";
	watsmsg[3] = "<?php _e('Please correct the errors','WATS'); ?>";
	watsmsg[4] = "<?php _e('Adding entry','WATS'); ?>";
	watsmsg[5] = "<?php _e('Error : the string contains invalid caracters!','WATS'); ?>";
</script> 
<?php
	$ajaxfileloc = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) . '/wats-options-ajax.php';
    wp_enqueue_script('wats-options-ajax', $ajaxfileloc);

	$ajaxfileloc = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) . '/wats-js-commons.php';
    wp_enqueue_script('wats-js-commons', $ajaxfileloc);
	
	return;
}

/**********************************************************/
/*                                                        */
/* Fonction de chargement des scripts de la page d'édtion */
/*                                                        */
/**********************************************************/

function wats_ticket_edit_admin_head()
{
	global $post;
	
	wats_admin_scripts();
	add_filter('list_terms_exclusions','wats_list_terms_exclusions');
	
	if (isset($post) && $post->post_type == 'ticket')
	{
		?>
		<script type="text/javascript">
		var watsid = "<?php echo $post->ID ?>";
		</script>
		<?php
	}
	
	return;
}

/**********************************************************/
/*                                                        */
/* Fonction de filtrage des tabs pour l'upload des médias */
/*                                                        */
/**********************************************************/

function wats_media_upload_tabs($tabs)
{
	global $wats_settings;
	
	if (isset($_GET['post_id']) && get_post_type($_GET['post_id']) == 'ticket' && $wats_settings['ticket_edition_media_upload_tabs'] == 0)
	{
		unset($tabs['gallery']);
		unset($tabs['library']);
	}

	return($tabs);
}

?>