<?php

/********************************************************************************/
/*                                                                              */
/* Fonction de remplissage du widget de statistiques des tickets dans le frontend */
/*                                                                              */
/********************************************************************************/

function wats_frontend_widget_stats_content()
{
	global $current_user;

	echo '<strong>'.__('Global stats :','WATS').'</strong><br /><ul>';
	echo '<li>'.__('Tickets created :','WATS');
	echo ' '.wats_get_number_of_tickets_by_status(0,0).'</li>';
	echo '<li>'.__('Tickets closed :','WATS');
	echo ' '.wats_get_number_of_tickets_by_status(wats_get_closed_status_id(),0).'</li></ul>';

	if (is_user_logged_in())
	{
		echo '<strong>'.__('Your stats :','WATS').'</strong><br /><ul>';
		echo '<li>'.__('Tickets created :','WATS');
		echo ' '.wats_get_number_of_tickets_by_status(0,$current_user->ID).'</li>';
		echo '<li>'.__('Tickets closed :','WATS');
		echo ' '.wats_get_number_of_tickets_by_status(wats_get_closed_status_id(),$current_user->ID).'</li></ul>';
	}

	return;
}

/********************************************************************************/
/*                                                                              */
/* Fonction d'initialisation du widget de statistiques des tickets dans le frontend */
/*                                                                              */
/********************************************************************************/

function wats_frontend_widget_stats($args)
{
	
	extract($args);
  
	echo $before_widget;
	echo $before_title.__('Tickets','WATS').$after_title;
	wats_frontend_widget_stats_content();
	echo $after_widget;
	
	return;
}

/********************************************************************************/
/*                                                                              */
/* Fonction d'enregistrement du widget de statistiques des tickets dans le frontend */
/*                                                                              */
/********************************************************************************/

function wats_frontend_widget_stats_init()
{
   wp_register_sidebar_widget('wats_frontend_stats',__('Tickets','WATS'), 'wats_frontend_widget_stats');
}

add_action('plugins_loaded','wats_frontend_widget_stats_init');

/********************************************************************************/
/*                                                                              */
/* Fonction pour remplir le widget de statistiques des tickets sur le dashboard */
/*                                                                              */
/********************************************************************************/

function wats_dashboard_widget_tickets()
{
	global $current_user, $wats_settings;

	$role = array_shift($current_user->roles);
	$user_can_view_stats = 0;
	if (isset($wats_settings['dashboard_stats_widget_'.$role]) && $wats_settings['dashboard_stats_widget_'.$role] == 1)
		$user_can_view_stats = 1;
		
	if ($user_can_view_stats == 1)
	{
		echo '<div>'.__('Global stats :','WATS').'<br /><br />';
		echo '<li class="wats">'.__('Number of tickets created : ','WATS');
		echo ' '.wats_get_number_of_tickets_by_status(0,0).'</li>';
		echo '<li class="wats">'.__('Number of tickets closed : ','WATS');
		echo ' '.wats_get_number_of_tickets_by_status(wats_get_closed_status_id(),0).'</li></div><br /><br />';
	}
	
	echo '<div>'.__('Your stats :','WATS').'<br /><br />';
	echo '<li class="wats">'.__('Number of tickets created : ','WATS');
	echo ' '.wats_get_number_of_tickets_by_status(0,$current_user->ID).'</li>';
	echo '<li class="wats">'.__('Number of tickets closed : ','WATS');
	echo ' '.wats_get_number_of_tickets_by_status(wats_get_closed_status_id(),$current_user->ID).'</li></div>';
	
	return;
}

/****************************************************/
/*                                                  */
/* Fonction pour le widget des commentaires récents */
/*                                                  */
/****************************************************/

function wp_dashboard_wats_recent_comments()
{
	global $wpdb, $wats_settings, $current_user;

	if (current_user_can('edit_posts'))
		$allowed_states = array('0', '1');
	else
		$allowed_states = array('1');

	// Select all comment types and filter out spam later for better query performance.
	$comments = array();
	$start = 0;
	
	$join = " AS wp1 ";
	$where = " WHERE NOT EXISTS (SELECT * FROM ".$wpdb->commentmeta." AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ";
	
	if ($wats_settings['visibility'] == 0 || $wats_settings['visibility'] == 1)
		$query = "SELECT * FROM $wpdb->comments AS wp1 WHERE NOT EXISTS (SELECT * FROM $wpdb->commentmeta AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ORDER BY comment_date_gmt DESC LIMIT $start, 50";
	else if ($wats_settings['visibility'] == 2 && current_user_can('administrator'))
		$query = "SELECT * FROM $wpdb->comments ORDER BY comment_date_gmt DESC LIMIT $start, 50";
	else if ($wats_settings['visibility'] == 2)
		$query = "SELECT * FROM $wpdb->comments AS wp1 LEFT JOIN $wpdb->posts ON wp1.comment_post_ID = $wpdb->posts.ID WHERE $wpdb->posts.post_author = ".$current_user->ID." AND NOT EXISTS (SELECT * FROM ".$wpdb->commentmeta." AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ORDER BY comment_date_gmt DESC LIMIT ".$start.", 50";
			
	while (count($comments) < 5 && $possible = $wpdb->get_results($query))
	{
		foreach ($possible as $comment)
		{
			if (count($comments) >= 5)
				break;
			if (in_array($comment->comment_approved, $allowed_states))
				$comments[] = $comment;
		}
		$start = $start + 50;
		if ($wats_settings['visibility'] == 0 || $wats_settings['visibility'] == 1)
			$query = "SELECT * FROM $wpdb->comments AS wp1 WHERE NOT EXISTS (SELECT * FROM $wpdb->commentmeta AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ORDER BY comment_date_gmt DESC LIMIT $start, 50";
		else if ($wats_settings['visibility'] == 2 && current_user_can('administrator'))
			$query = "SELECT * FROM $wpdb->comments ORDER BY comment_date_gmt DESC LIMIT $start, 50";
		else if ($wats_settings['visibility'] == 2)
			$query = "SELECT * FROM $wpdb->comments AS wp1 LEFT JOIN $wpdb->posts ON wp1.comment_post_ID = $wpdb->posts.ID WHERE $wpdb->posts.post_author = ".$current_user->ID." AND NOT EXISTS (SELECT * FROM ".$wpdb->commentmeta." AS wp2 WHERE wp1.comment_ID = wp2.comment_id AND wp2.meta_key = 'wats_internal_update' AND wp2.meta_value = 1) ORDER BY comment_date_gmt DESC LIMIT ".$start.", 50";
	}

	if ($comments) :
?>
		<div id="the-comment-list" class="list:comment">
<?php
		foreach ($comments as $comment)
			_wp_dashboard_recent_comments_row($comment);
?>
		</div>
<?php
		if (current_user_can('moderate_comments') || ($wats_settings['comment_menuitem_visibility'] == 0))
		{ ?>
			<p class="textright"><a href="edit-comments.php" class="button"><?php _e('View all'); ?></a></p>
<?php	}
		wp_comment_reply( -1, false, 'dashboard', false );
	else :
?>
	<p><?php _e( 'No comments yet.' ); ?></p>
<?php
	endif; // $comments;
}

/********************************************************************************/
/*                                                                              */
/* Fonction pour ajouter le widget de statistiques des tickets sur le dashboard */
/*                                                                              */
/********************************************************************************/

function wats_dashboard_setup()
{
	global $wp_meta_boxes, $current_user, $wats_settings;

	wp_add_dashboard_widget('my_wp_dashboard_wats', __('Tickets','WATS'), 'wats_dashboard_widget_tickets');
		
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	$recent_comments_title = __( 'Recent Comments' );
	wp_add_dashboard_widget( 'dashboard_recent_comments', $recent_comments_title, 'wp_dashboard_wats_recent_comments' );

	return;
}

?>