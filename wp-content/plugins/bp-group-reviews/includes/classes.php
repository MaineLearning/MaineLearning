<?php

class BP_Group_Reviews_Extension extends BP_Group_Extension {

	function bp_group_reviews_extension() {
		global $bp;
		
		$this->group_id = BP_Groups_Group::group_exists($bp->current_item);
		
		$this->name = __( 'Reviews', 'bpgr' );
		$this->slug = $bp->group_reviews->slug;
		
		$this->nav_item_position = 22;
		
		$this->enable_create_step = false;
		
		$this->enable_nav_item = BP_Group_Reviews::current_group_is_available();
		$this->enable_edit_item = false;

		if ( isset( $_POST['review_submit'] ) ) {
			check_admin_referer( 'review_submit' );

			$has_posted = '';
			
			if ( empty( $_POST['review_content'] ) || !(int)$_POST['rating'] ) {
				// Something has gone wrong. Save the user's submitted data to reinsert into the post box after redirect
				$cookie_data = array( 'review_content' => $_POST['review_content'], 'rating' => $_POST['rating'] );				
				$cookie = json_encode( $cookie_data );
				setcookie( 'bpgr-data', $cookie, time()+60*60*24, COOKIEPATH );
				
				bp_core_add_message( __( "Please make sure you fill in the review, and don't forget to provide a rating!", 'bpgr' ), 'error' );
			} else {
				/* Auto join this user if they are not yet a member of this group */
				if ( !is_super_admin() && 'public' == $bp->groups->current_group->status && !groups_is_user_member( $bp->loggedin_user->id, $bp->groups->current_group->id ) )
					groups_join_group( $bp->groups->current_group->id, $bp->loggedin_user->id );

				if ( $rating_id = $this->post_review( array( 'content' => $_POST['review_content'], 'rating' => (int)$_POST['rating'] ) ) ) {
					bp_core_add_message( "Your review was posted successfully!" );

					$has_posted = groups_get_groupmeta( $bp->groups->current_group->id, 'posted_review' );
					if ( !in_array( (int)$bp->loggedin_user->id, (array)$has_posted ) ) {
						$has_posted[] = (int)$bp->loggedin_user->id;
					}
					groups_update_groupmeta( $bp->groups->current_group->id, 'posted_review', $has_posted );

					if ( (int)$_POST['rating'] < 0 )
						$_POST['rating'] = 1;

					if ( (int)$_POST['rating'] > 5 )
						$_POST['rating'] = 5;
				} else {
					bp_core_add_message( "There was a problem posting your review, please try again.", 'error' );
				}
			}
		
			bp_core_redirect( apply_filters( 'bpgr_after_post_redirect', trailingslashit( bp_get_group_permalink( $bp->groups->current_group ) . $this->slug, $has_posted ) ) );
		}
	}

	function display() {
		global $bp;

		include( apply_filters( 'bpgr_index_template', BP_GROUP_REVIEWS_DIR . 'templates/index.php' ) );
	}
	
	function post_review( $args = '' ) {
		global $bp;
	
		$defaults = array(
			'content' => false,
			'rating' => false,
			'user_id' => $bp->loggedin_user->id,
			'group_id' => $bp->groups->current_group->id
		);
	
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
	
		if ( empty( $content ) || !strlen( trim( $content ) ) || empty( $user_id ) || empty( $group_id ) )
			return false;
	
		// Be sure the user is a member of the group before posting.
		if ( !is_super_admin() && !groups_is_user_member( $user_id, $group_id ) )
			return false;
	
		// Record this in activity streams
		$activity_action = sprintf( __( '%s reviewed %s:', 'bpgr'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . esc_html( $bp->groups->current_group->name ) . '</a>' );
	
		$rating_content = false;
		if ( !empty( $rating ) )
			$rating_content = '<span class="p-rating">' . bpgr_get_review_rating_html( $rating ) . '</span>';
	
		$activity_content = $rating_content . $content;
	
		$activity_id = groups_record_activity( array(
			'user_id' => $user_id,
			'action' => $activity_action,
			'content' => $activity_content,
			'type' => 'review',
			'item_id' => $group_id
		) );
	
		$this->add_rating( array( 'score' => $rating, 'activity_id' => $activity_id, 'group_id' => $group_id ) );
	
		groups_update_groupmeta( $group_id, 'last_activity', gmdate( "Y-m-d H:i:s" ) );
	
		do_action( 'bpgr_posted_review', $args, $activity_id );
	
		return $activity_id;
	}
	
	function add_rating( $args = '' ) {
		global $bp;
	
		$defaults = array(
			'group_id' => $bp->groups->current_group->id,
			'score' => false,
			'user_id' => $bp->loggedin_user->id,
			'activity_id' => false
		);
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		
		if ( empty( $score ) )
			return false;
		
		// First record the activity meta for this particular rating
		bp_activity_update_meta( $activity_id, 'bpgr_rating', $score );
		
		// Then add this item to the list of reviews for this group
		if ( !$ratings = groups_get_groupmeta( $group_id, 'bpgr_ratings' ) )
			$ratings = array();
		
		$ratings[$activity_id] = $score;
		
		// Pull the composite scores and recalculate
		if ( !$rating = groups_get_groupmeta( $group_id, 'bpgr_rating' ) )
			$avg_score = 0;
		if ( !$how_many = (int)groups_get_groupmeta( $group_id, 'bpgr_how_many_ratings' ) )
			$how_many = 0;
		
		$how_many++;
		groups_update_groupmeta( $group_id, 'bpgr_how_many_ratings', $how_many );
		
		$raw_score = 0;
		foreach( $ratings as $score ) {
			$raw_score += (int)$score;
		}
		$rating = $raw_score / $how_many;
				
		groups_update_groupmeta( $group_id, 'bpgr_rating', $rating );
			
		groups_update_groupmeta( $group_id, 'bpgr_ratings', $ratings );
		
	}
}
bp_register_group_extension( 'BP_Group_Reviews_Extension' );

?>
