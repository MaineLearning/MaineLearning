<?php global $bp ?>

<form action="<?php echo trailingslashit( bp_get_group_permalink( groups_get_current_group() ) . $bp->group_reviews->slug ) ?>" method="post" id="whats-new-form" name="whats-new-form" class="review-form">

	<?php do_action( 'bp_before_activity_post_form' ) ?>

	<div id="whats-new-avatar">
		<a href="<?php echo bp_loggedin_user_domain() ?>">
			<?php bp_loggedin_user_avatar( 'width=60&height=60' ) ?>
		</a>
	</div>

	<?php if ( bpgr_has_written_review() && !bpgr_allow_multiple_reviews() ) : ?>
	
	<?php if ( bp_has_activities( bpgr_user_previous_review_args() ) ) : while ( bp_activities() ) : bp_the_activity() ?> 
	
	<div class="already-rated">	
		<h5><?php printf( __( "You rated %s on %s.", 'bpgr' ), bp_get_group_name(), bpgr_get_activity_date_recorded() ) ?></h5>
		
		<blockquote>
			<?php echo bp_get_activity_content_body() ?>
			
			<div class="rest-stars">
				<?php echo bpgr_get_review_rating_html( bpgr_get_review_rating() ) ?> 
			</div>
		</blockquote>
		
		<p><?php _e( "To leave another review, you must delete your existing review.", 'bpgr' ) ?> <?php bp_activity_delete_link() ?></p>
	</div>
	
	<?php endwhile; endif ?>

	<?php else : ?>

	<h5><?php printf( __( 'What are your thoughts on %s, %s?', 'bpgr'), bp_get_group_name(), bp_get_user_firstname() ) ?></h5>

	<div id="whats-new-content">
		<div id="whats-new-textarea">
			<div>
				<textarea name="review_content" id="whats-new" value="" /><?php if ( !empty( $bp->group_reviews->previous_data->review_content ) ) : ?><?php echo esc_html(  $bp->group_reviews->previous_data->review_content ) ?> <?php endif; ?></textarea>
			</div>
		</div>

		<div id="review-rating">
			<?php _e( 'Rate it:', 'bpgr' ) ?> <img id="star1" class="star" src="<?php bpgr_star_off_img() . '" alt="' . __('1 stars') ?>" /><img id="star2" class="star" src="<?php bpgr_star_off_img() . '" alt="' . __('2 stars') ?>" /><img id="star3" class="star" src="<?php bpgr_star_off_img() . '" alt="' . __('3 stars') ?>" /><img id="star4" class="star" src="<?php bpgr_star_off_img() . '" alt="' . __('4 stars') ?>" /><img id="star5" class="star" src="<?php bpgr_star_off_img() . '" alt="' . __('5 stars') ?>" />
		</div>

		<div id="whats-new-options">
			<div id="whats-new-submit">
				<span class="ajax-loader"></span> &nbsp;
				<input type="submit" name="review_submit" id="whats-new-submit" value="<?php _e( 'Post My Review', 'bpgr' ) ?>" />
			</div>

			<?php do_action( 'bp_activity_post_form_options' ) ?>

		</div><!-- #whats-new-options -->
	</div><!-- #whats-new-content -->

	<input type="hidden" name="rating" id="rating" value="0" />

	<?php wp_nonce_field( 'review_submit' ); ?>
	
	<?php endif ?>
	
	<?php do_action( 'bp_after_activity_post_form' ) ?>
	
</form><!-- #whats-new-form -->
