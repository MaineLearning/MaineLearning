<?php
/* Group blog extension using the BuddyPress group extension API */
if ( class_exists('BP_Group_Extension' ) ) {

	class Group_External_Blogs extends BP_Group_Extension {
		function __construct() {
			global $bp;
			$this->name = __( 'External Blogs', 'bp-groups-externalblogs' );
			$this->slug = 'external-blog-feeds';
			$this->create_step_position = 21;
			$this->enable_nav_item = false;
		}
		function create_screen() {
			global $bp;
			if ( !bp_is_group_creation_step( $this->slug ) )
				return false;
			?>
			<p><?php _e(
				"Add RSS feeds of blogs you'd like to attach to this group in the box below.
				 Any future posts on these blogs will show up on the group page and be recorded
				 in activity streams.", 'bp-groups-externalblogs' ) ?>
			</p>
			<p class="desc"><?php _e( "Seperate URL's with commas.", 'bp-groups-externalblogs' ) ?></span>
			<p>
				<label for="blogfeeds"><?php _e( "Feed URL's:", 'bp-groups-externalblogs' ) ?></label>
				<textarea name="blogfeeds" id="blogfeeds"><?php echo attribute_escape( implode( ', ', (array)groups_get_groupmeta( $bp->groups->current_group->id, 'blogfeeds' ) ) ) ?></textarea>
			</p>
			<?php
			wp_nonce_field( 'groups_create_save_' . $this->slug );
		}
		function create_screen_save() {
			global $bp;
			check_admin_referer( 'groups_create_save_' . $this->slug );
			$unfiltered_feeds = explode( ',', $_POST['blogfeeds'] );
			foreach( (array) $unfiltered_feeds as $blog_feed ) {
				if ( !empty( $blog_feed ) )
					$blog_feeds[] = trim( $blog_feed );
			}
			groups_update_groupmeta( $bp->groups->current_group->id, 'blogfeeds', $blog_feeds );
			groups_update_groupmeta( $bp->groups->current_group->id, 'bp_groupblogs_lastupdate', gmdate( "Y-m-d H:i:s" ) );
			/* Fetch */
			bp_groupblogs_fetch_group_feeds( $bp->groups->current_group->id );
		}
		function edit_screen() {
			global $bp;
			if ( !bp_is_group_admin_screen( $this->slug ) )
				return false; ?>
			<p class="desc"><?php _e( "Enter RSS feed URL's for blogs you would like to attach to this group. Any future posts on these blogs will show on the group activity stream. Seperate URL's with commas.", 'bp-groups-externalblogs' ) ?></span>
			<p>
				<label for="blogfeeds"><?php _e( "Feed URL's:", 'bp-groups-externalblogs' ) ?></label>
				<textarea name="blogfeeds" id="blogfeeds"><?php echo attribute_escape( implode( ', ', (array)groups_get_groupmeta( $bp->groups->current_group->id, 'blogfeeds' ) ) ) ?></textarea>
			</p>
			<input type="submit" name="save" value="<?php _e( "Update Feed URL's", 'bp-groups-externalblogs' ) ?>" />
			<?php
			wp_nonce_field( 'groups_edit_save_' . $this->slug );
		}
		function edit_screen_save() {
			global $bp;
			if ( !isset( $_POST['save'] ) )
				return false;
			check_admin_referer( 'groups_edit_save_' . $this->slug );
			$existing_feeds = (array)groups_get_groupmeta( $bp->groups->current_group->id, 'blogfeeds' );
			$unfiltered_feeds = explode( ',', $_POST['blogfeeds'] );
			foreach( (array) $unfiltered_feeds as $blog_feed ) {
				if ( !empty( $blog_feed ) )
					$blog_feeds[] = trim( $blog_feed );
			}
			/* Loop and find any feeds that have been removed, so we can delete activity stream items */
			if ( !empty( $existing_feeds ) ) {
				foreach( (array) $existing_feeds as $feed ) {
					if ( !in_array( $feed, (array) $blog_feeds ) )
						$removed[] = $feed;
				}
			}
			if ( $removed  ) {
				/* Remove activity stream items for this feed */
				include_once( ABSPATH . WPINC . '/rss.php' );
				foreach( (array) $removed as $feed ) {
					$rss = fetch_rss( trim( $feed ) );
					if ( function_exists( 'bp_activity_delete' ) ) {
						bp_activity_delete( array(
							'item_id' => $bp->groups->current_group->id,
							'secondary_item_id' => wp_hash( $rss->channel['link'] ),
							'component' => $bp->groups->id,
							'type' => 'exb'
						) );
					}
				}
			}
			groups_update_groupmeta( $bp->groups->current_group->id, 'blogfeeds', $blog_feeds );
			groups_update_groupmeta( $bp->groups->current_group->id, 'bp_groupblogs_lastupdate', gmdate( "Y-m-d H:i:s" ) );
			/* Re-fetch */
			bp_groupblogs_fetch_group_feeds( $bp->groups->current_group->id );
			bp_core_add_message( __( 'External blog feeds updated successfully!', 'bp-groups-externalblogs' ) );
			bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
		}
		/* We don't need display functions since the group activity stream handles it all. */
		function display() {}
		function widget_display() {}
	}

	bp_register_group_extension( 'Group_External_Blogs' );

	function bp_groupblogs_fetch_group_feeds( $group_id = false ) {
		global $bp;
		include_once( ABSPATH . 'wp-includes/rss.php' );
		if ( empty( $group_id ) )
			$group_id = $bp->groups->current_group->id;
		if ( $group_id == $bp->groups->current_group->id )
			$group = $bp->groups->current_group;
		else
			$group = new BP_Groups_Group( $group_id );
		if ( !$group )
			return false;
		$group_blogs = groups_get_groupmeta( $group_id, 'blogfeeds' );
		$group_blogs = explode(";",$group_blogs[0]);

		/* Set the visibility */
		$hide_sitewide = ( 'public' != $group->status ) ? true : false;
		foreach ( (array) $group_blogs as $feed_url ) {
			$rss = fetch_feed( trim( $feed_url ) );
			if (!is_wp_error($rss) ) {
				foreach ( $rss->get_items(0,10) as $item ) {;
					$key = $item->get_date( 'U' );
					$items[$key]['title'] = $item->get_title();
					$items[$key]['subtitle'] = $item->get_title();
					//$items[$key]['author'] = $item->get_author()->get_name();
					$items[$key]['blogname'] = $item->get_feed()->get_title();
					$items[$key]['link'] = $item->get_permalink();
					$items[$key]['blogurl'] = $item->get_feed()->get_link();
					$items[$key]['description'] = $item->get_description();
					$items[$key]['source'] = $item->get_source();
					$items[$key]['copyright'] = $item->get_copyright();
				}
			}
		}
		if ( $items ) {
			ksort($items);
			$items = array_reverse($items, true);
		} else {
			return false;
		}
		/* Record found blog posts in activity streams */
		foreach ( (array) $items as $post_date => $post ) {
			//var_dump($post);
			if (substr($post['blogname'],0,7) == "Twitter") {
				$activity_action = sprintf( __( '%s from %s in the group %s', 'bp-groups-externalblogs' ), '<a class="feed-link" href="' . esc_attr( $post['link'] ) . '">Tweet</a>', '<a class="feed-author" href="' . esc_attr( $post['blogurl'] ) . '">' . attribute_escape( $post['blogname'] ) . '</a>', '<a href="' . bp_get_group_permalink( $group ) . '">' . attribute_escape( $group->name ) . '</a>' );
			} else {
				$activity_action = sprintf( __( 'Blog: %s from %s in the group %s', 'bp-groups-externalblogs' ), '<a class="feed-link" href="' . esc_attr( $post['link'] ) . '">' . esc_attr( $post['title'] ) . '</a>', '<a class="feed-author" href="' . esc_attr( $post['blogurl'] ) . '">' . attribute_escape( $post['blogname'] ) . '</a>', '<a href="' . bp_get_group_permalink( $group ) . '">' . attribute_escape( $group->name ) . '</a>' );
			}

			$activity_content = '<div>' . strip_tags( bp_create_excerpt( $post['description'], 175 ) ) . '</div>';
			$activity_content = apply_filters( 'bp_groupblogs_activity_content', $activity_content, $post, $group );
			/* Fetch an existing activity_id if one exists. */
			if ( function_exists( 'bp_activity_get_activity_id' ) )
				$id = bp_activity_get_activity_id( array( 'user_id' => false, 'action' => $activity_action, 'component' => $bp->groups->id, 'type' => 'exb', 'item_id' => $group_id, 'secondary_item_id' => wp_hash( $post['blogurl'] ) ) );
			/* Record or update in activity streams. */
			groups_record_activity( array(
				'id' => $id,
				'user_id' => false,
				'action' => $activity_action,
				'content' => $activity_content,
				'primary_link' => $item->get_link(),
				'type' => 'exb',
				'item_id' => $group_id,
				'secondary_item_id' => wp_hash( $post['blogurl'] ),
				'recorded_time' => gmdate( "Y-m-d H:i:s", $post_date ),
				'hide_sitewide' => $hide_sitewide
			) );
		}
		return $items;
	}

	/* Add a filter option to the filter select box on group activity pages */
	function bp_groupblogs_add_filter() { ?>
		<option value="exb"><?php _e( 'External Blogs', 'bp-groups-externalblogs' ) ?></option><?php
	}
	add_action( 'bp_group_activity_filter_options', 'bp_groupblogs_add_filter' );
	add_action( 'bp_activity_filter_options', 'bp_groupblogs_add_filter' );

	/* Add a filter option groups avatar */
	/* Fetch group twitter posts after 30 mins expires and someone hits the group page */
	function bp_groupblogs_refetch() {
		global $bp;
		$last_refetch = groups_get_groupmeta( $bp->groups->current_group->id, 'bp_groupblogs_lastupdate' );
		if ( strtotime( gmdate( "Y-m-d H:i:s" ) ) >= strtotime( '+30 minutes', strtotime( $last_refetch ) ) )
			add_action( 'wp_footer', 'bp_groupblogs_refetch' );
		/* Refetch the latest group twitter posts via AJAX so we don't stall a page load. */
		function _bp_groupblogs_refetch() {
			global $bp; ?>
			<script type="text/javascript">
				jQuery(document).ready( function() {
					jQuery.post( ajaxurl, {
						action: 'refetch_groupblogs',
						'cookie': encodeURIComponent(document.cookie),
						'group_id': <?php echo $bp->groups->current_group->id ?>
					});
				});
			</script><?php
			groups_update_groupmeta( $bp->groups->current_group->id, 'bp_groupblogs_lastupdate', gmdate( "Y-m-d H:i:s" ) );
		}
	}
	add_action( 'groups_screen_group_home', 'bp_groupblogs_refetch' );

	/* Refresh via an AJAX post for the group */
	function bp_groupblogs_ajax_refresh() {
		bp_groupblogs_fetch_group_feeds( $_POST['group_id'] );
	}
	add_action( 'wp_ajax_refetch_groupblogs', 'bp_groupblogs_ajax_refresh' );

	function bp_groupblogs_cron_refresh() {
		global $bp, $wpdb;
		$group_ids = $wpdb->get_col( $wpdb->prepare( "SELECT group_id FROM " . $bp->groups->table_name_groupmeta . " WHERE meta_key = 'blogfeeds'" ) );
		foreach( $group_ids as $group_id )
			bp_groupblogs_fetch_group_feeds( $group_id );
	}
	add_action( 'bp_groupblogs_cron', 'bp_groupblogs_cron_refresh' );
}

// Add a filter option groups avatar
function bp_groupblogs_avatar_type($var) {
	global $activities_template, $bp;

	if ( $activities_template->activity->type == "exb" ) {
		return 'group';
	} else {
		return $var;
	}
}
add_action( 'bp_get_activity_avatar_object_groups', 'bp_groupblogs_avatar_type');
add_action( 'bp_get_activity_avatar_object_activity', 'bp_groupblogs_avatar_type');

function bp_groupblogs_avatar_id($var) {
	global $activities_template, $bp;

	if ( $activities_template->activity->type == "exb" ) {
		return $activities_template->activity->item_id;
	}

	return $var;

}
add_action( 'bp_get_activity_avatar_item_id', 'bp_groupblogs_avatar_id');

?>
