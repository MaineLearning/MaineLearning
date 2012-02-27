<?php

if ( !class_exists( 'BP_Group_Reviews' ) ) :

class BP_Group_Reviews {
	function bp_group_reviews() {
		$this->construct();
	}

	function __construct() {
		// The plugin won't run without groups
		// For now, reviews are pegged to activity. Todo: move to cpts
		if ( !bp_is_active( 'groups' ) || !bp_is_active( 'activity' ) )
			return false;

		$this->includes();

		add_action( 'bp_init', array( $this, 'maybe_update' ) );

		add_action( 'bp_setup_globals', array( $this, 'setup_globals' ) );
		add_action( 'groups_setup_nav', array( $this, 'current_group_set_available' ) );
		add_action( 'groups_setup_nav', array( $this, 'setup_current_group_globals' ) );
		add_action( 'wp_print_scripts', array( $this, 'load_js' ) );
		add_action( 'wp_head', array( $this, 'maybe_previous_data' ), 999 );
		add_action( 'wp_print_styles', array( $this, 'load_styles' ) );
		add_action( 'bp_actions', array( $this, 'grab_cookie' ), 1 );
		add_filter( 'bp_has_activities', array( $this, 'activities_template_data' ) );
		add_filter( 'bp_has_groups', array( $this, 'groups_template_data' ) );

		// For BP 1.5+
		add_action( 'bp_activity_before_action_delete_activity', array( $this, 'delete_activity' ), 10, 2 );

		// For BP < 1.5
		add_action( 'bp_activity_action_delete_activity', array( $this, 'delete_activity' ), 10, 2 );

		add_action( 'bp_activity_excerpt_length', array( $this, 'activity_excerpt_length' ) );
		add_action( 'bp_get_activity_content_body', array( $this, 'strip_star_tags' ) );
	}

	function includes() {
		require_once( BP_GROUP_REVIEWS_DIR . 'includes/settings.php' );
		require_once( BP_GROUP_REVIEWS_DIR . 'includes/classes.php' );
		require_once( BP_GROUP_REVIEWS_DIR . 'includes/templatetags.php' );
		require_once( BP_GROUP_REVIEWS_DIR . 'includes/widgets.php' );
	}

	function maybe_update() {
		if ( get_option( 'bp_group_reviews_version' ) < BP_GROUP_REVIEWS_VERSION ) {
			require_once( BP_GROUP_REVIEWS_DIR . 'includes/upgrade.php' );
		}
	}

	function load_js() {
		wp_register_script( 'bp-group-reviews', BP_GROUP_REVIEWS_URL . 'js/group-reviews.js', array( "jquery" ) );
		wp_enqueue_script( 'bp-group-reviews' );

		$params = array(
			'star' => bpgr_get_star_img(),
			'star_half' => bpgr_get_star_half_img(),
			'star_off' => bpgr_get_star_off_img()
		);
		wp_localize_script( 'bp-group-reviews', 'bpgr', $params );
	}

	function maybe_previous_data() {
		if ( bpgr_has_previous_data() ) {
		?>
			<script type="text/javascript">
				jQuery(document).ready( function() {
					jQuery("#review-post-form").css('display','block');
				});
			</script>
		<?php
		}
	}

	function load_styles() {
		wp_register_style( 'bp-group-reviews', BP_GROUP_REVIEWS_URL . 'css/group-reviews.css' );
		wp_enqueue_style( 'bp-group-reviews' );
	}


	function setup_globals() {
		global $bp;

		$bp->group_reviews->slug = BP_GROUP_REVIEWS_SLUG;

		$image_types = array(
			'star',
			'star_half',
			'star_off'
		);

		foreach( $image_types as $image_type ) {
			$bp->group_reviews->images[$image_type] = apply_filters( "bpgr-$image_type", BP_GROUP_REVIEWS_URL . 'images/' . $image_type . '.png' );
		}
	}

	function setup_current_group_globals() {
		global $bp;

		if ( isset( $bp->groups->current_group->id ) ) {
			$rating = groups_get_groupmeta( $bp->groups->current_group->id, 'bpgr_rating' );
			$how_many = groups_get_groupmeta( $bp->groups->current_group->id, 'bpgr_how_many_ratings' );

			if ( !empty( $rating ) )
				$bp->groups->current_group->rating_avg_score = $rating;

			if ( !empty( $how_many ) )
				$bp->groups->current_group->rating_number = $how_many;

		}
	}

	function grab_cookie() {
		global $bp;
		
		if ( empty( $bp->group_reviews->previous_data ) && isset( $_COOKIE['bpgr-data'] ) ) {
			$bp->group_reviews->previous_data = maybe_unserialize( json_decode( stripslashes( $_COOKIE['bpgr-data'] ) ) );
		}

		setcookie( 'bpgr-data', false, time() - 1000, COOKIEPATH );
	}

	/**
	 * Fetch review data when the activity stream is called. Done in one fell swoop to minimize
	 * database queries. Todo: remove if an extras parameter is added to bp_has_activities()
	 * in BP core
	 *
	 * @package BP Group Reviews
	 * @uses $activities_template The global activity stream object
	 * @param bool $has_activities Must be returned in order for content to render
	 * @return bool $has_activities
	 */
	function activities_template_data( $has_activities ) {
		global $activities_template, $wpdb, $bp;

		$activity_ids = array();
		foreach( $activities_template->activities as $activity ) {
			$activity_ids[] = $activity->id;
		}
		$activity_ids = implode( ',', $activity_ids );

		if ( empty( $activity_ids ) )
			return $has_activities;

		$sql = apply_filters( 'bpgr_activities_data_sql', $wpdb->prepare( "SELECT activity_id, meta_value AS rating FROM {$bp->activity->table_name_meta} WHERE activity_id IN ({$activity_ids}) AND meta_key = 'bpgr_rating'" ) );
		$ratings_raw = $wpdb->get_results( $sql, ARRAY_A );

		// Arrange the results in a properly-keyed array
		$ratings = array();
		foreach( $ratings_raw as $rating ) {
			$id = $rating['activity_id'];
			$ratings[$id] = $rating['rating'];
		}


		foreach( $activities_template->activities as $key => $activity ) {
			if ( $activity->type != 'review' )
				continue;

			$id = $activity->id;

			$activities_template->activities[$key]->rating = !empty( $ratings[$id] ) ? $ratings[$id] : '';
		}

		return $has_activities;
	}

	/**
	 * Fetch review data when the group loop is created. Done in one fell swoop to minimize
	 * database queries.
	 *
	 * @package BP Group Reviews
	 * @uses $groups_template The global groups loop object
	 * @param bool $has_groups Must be returned in order for content to render
	 * @return bool $has_groups
	 */
	function groups_template_data( $has_groups ) {
		global $groups_template, $wpdb, $bp;

		if ( !bp_is_directory() )
			return $has_groups;

		$group_ids = array();
		foreach( (array)$groups_template->groups as $group ) {
			$group_ids[] = $group->id;
		}
		$group_ids = implode( ',', $group_ids );

		if ( empty( $group_ids ) )
			return $has_groups;

		$sql = apply_filters( 'bpgr_groups_data_sql', $wpdb->prepare( "
			SELECT m1.group_id, m1.meta_value AS rating, m2.meta_value AS rating_count, m3.meta_value AS ratings_enabled
			FROM {$bp->groups->table_name_groupmeta} m1
			LEFT JOIN {$bp->groups->table_name_groupmeta} m2 ON (m1.group_id = m2.group_id)
			LEFT JOIN {$bp->groups->table_name_groupmeta} m3 ON (m1.group_id = m3.group_id)
			WHERE m1.group_id IN ({$group_ids})
			AND m1.meta_key = 'bpgr_rating'
			AND m2.meta_key = 'bpgr_how_many_ratings'
			AND m3.meta_key = 'bpgr_is_reviewable'"
		) );
		$ratings_raw = $wpdb->get_results( $sql, ARRAY_A );

		// Arrange the results in a properly-keyed array
		$ratings = array();
		foreach( $ratings_raw as $rating ) {
			$id = $rating['group_id'];
			$ratings[$id]['rating'] = $rating['rating'];
			$ratings[$id]['rating_count'] = $rating['rating_count'];
			$ratings[$id]['ratings_enabled'] = $rating['ratings_enabled'];
		}

		foreach( $groups_template->groups as $key => $group ) {
			$id = $group->id;

			$groups_template->groups[$key]->rating = !empty( $ratings[$id]['rating'] ) ? $ratings[$id]['rating'] : '';
			$groups_template->groups[$key]->rating_count = !empty( $ratings[$id]['rating_count'] ) ? $ratings[$id]['rating_count'] : '';
			$groups_template->groups[$key]->ratings_enabled = !empty( $ratings[$id]['ratings_enabled'] ) ? $ratings[$id]['ratings_enabled'] : '';
		}

		return $has_groups;
	}

	function current_group_set_available() {
		global $bp;

		if ( !empty( $bp->groups->current_group ) ) {
			if ( $this->current_group_is_available() ) {
				$bp->groups->current_group->is_reviewable = '1';
			} else {
				$bp->groups->current_group->is_reviewable = '0';
			}
		}
	}

	/**
	 * Check whether the group reviews tab is available for the current group
	 *
	 * A member specific check is done first. This should probably be moved out in the future
	 *
	 * @package BP Group Reviews
	 * @since 1.0.3
	 *
	 * @return bool $is_available True if the reviews tab is available for the group
	 */
	function current_group_is_available() {
		global $bp;

		// Check to see whether it's already in the global
		if ( isset( $bp->groups->current_group->is_reviewable ) ) {
			$is_available = $bp->groups->current_group->is_reviewable ? true : false;
		} else {
			// If the current user doesn't have access to the group, don't bother
			// checking whether reviews are turned on
			if ( empty( $bp->groups->current_group->user_has_access ) ) {
				$is_available = false;
			} else {
				$is_available = BP_Group_Reviews::group_is_reviewable( $bp->groups->current_group->id );
			}
		}

		return apply_filters( 'bpgr_current_group_is_available', $is_available );
	}

	function group_is_reviewable( $group_id ) {
		if ( empty( $group_id ) )
			return false;

		$is_reviewable = groups_get_groupmeta( $group_id, 'bpgr_is_reviewable' ) == 'yes' ? true : false;

		return apply_filters( 'bpgr_group_is_reviewable', $is_reviewable, $group_id );
	}

	/**
	 * Runs when an activity item is deleted
	 *
	 * Recalculates total ratings for the group, and removes the user in question from the list
	 * of reviewers for the group
	 *
	 * @param int $activity_id The id number of the activity item
	 * @param int $user_id The id number of the user
	 */
	function delete_activity( $activity_id, $user_id ) {
		$activity = new BP_Activity_Activity( $activity_id );
		$group_id = $activity->item_id;

		// Nothing to see here. Should only happen on BP 1.5 because of changed hooks.
		if ( empty( $group_id ) )
			return;

		// First, remove the user from the list of users who have previously reviewed
		// Get the list
		$has_posted = (array)groups_get_groupmeta( $group_id, 'posted_review' );

		// Because of a previous bug, we have to remove *all* instances
		$keys = array_keys( $has_posted, $user_id );
		foreach( (array)$keys as $key ) {
			unset( $has_posted[$key] );
		}

		// Resave the list of members who have reviewed
		$has_posted = array_values( $has_posted );
		groups_update_groupmeta( $group_id, 'posted_review', $has_posted );

		// Next, remove the rating from the list of ratings
		$group_ratings = groups_get_groupmeta( $group_id, 'bpgr_ratings' );

		if ( !empty( $group_ratings[$activity_id] ) )
			unset( $group_ratings[$activity_id] );
		groups_update_groupmeta( $group_id, 'bpgr_ratings', $group_ratings );

		// In order to account for recording errors, we will recalculate based on data
		$raw_score = 0;
		foreach( (array)$group_ratings as $score ) {
			$raw_score += (int)$score;
		}
		$how_many = count( $group_ratings );
		$rating = $how_many === 0 ? 0 : $raw_score / $how_many;

		groups_update_groupmeta( $group_id, 'bpgr_how_many_ratings', $how_many );
		groups_update_groupmeta( $group_id, 'bpgr_rating', $rating );
	}

	/**
	 * Makes activity entry excerpts longer
	 *
	 * BuddyPress 1.5+ limits the length of activity entries, and provides a 'Read More' link.
	 * Because this excerpt is based on character count, and because BPGR activity items are
	 * prepended with the HTML of the star rating, the excerpt ends up being far too short.
	 *
	 * @package BP Group Reviews
	 * @since 1.3
	 *
	 * @param int $length
	 * @return int
	 */
	function activity_excerpt_length( $length ) {
		global $activities_template;

		if ( isset( $activities_template->activity->type ) && 'review' == $activities_template->activity->type )
			$length = 1000;

		return $length;
	}

	/**
	 * On the BPGR tab, we show the rating markup separately, so we must strip it from the
	 * activity content.
	 *
	 * @package BP Group Reviews
	 * @since 1.3
	 *
	 * @param str $content The content of the activity item
	 * @return str $content The content, perhaps sans étoiles
	 */
	function strip_star_tags( $content ) {
		if ( bpgr_is_group_reviews() ) {
			$content = preg_replace( '/<span class="p\-rating">.*<\/span><\/span>/', '', $content );
		}

		return $content;
	}
}

endif;

$bp_group_reviews = new BP_Group_Reviews;

?>
