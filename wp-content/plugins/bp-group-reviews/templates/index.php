<?php do_action( 'bp_before_reviews' ) ?>

<div id="group-reviews" class="activity">
	<?php if ( is_user_logged_in() ) : ?>
		<?php include( apply_filters( 'bpgr_post_template', BP_GROUP_REVIEWS_DIR . 'templates/post.php' ) ) ?>
	<?php endif ?>

	<?php
		if ( bpgr_is_group_reviews() ) {
			$per_page = 15; $max = false;
		} else {
			$per_page = 8; $max = 8;
		}
	?>

	<?php if ( bp_has_activities( 'action=review&per_page=' . $per_page . '&max=' . $max ) ) : ?>

		<?php if ( bpgr_is_group_reviews()  ) : ?>
			<div class="pagination no-ajax">
				<div class="pag-count"><?php echo str_replace( 'item', 'review', bp_get_activity_pagination_count() ) ?></div>
				<div class="pagination-links"><?php bp_activity_pagination_links() ?></div>
			</div>
		<?php endif; ?>

		<ul id="activity-stream" class="activity-list item-list">

			<?php while ( bp_activities() ) : bp_the_activity(); ?>

				<?php include( apply_filters( 'bpgr_entry_template', BP_GROUP_REVIEWS_DIR . 'templates/entry.php' ) ) ?>

			<?php endwhile; ?>

		</ul>

	<?php else : ?>

		<div id="message" class="info">
			<p><?php printf( __( "There are no reviews yet. Why not <a href=\"%s\">be the first to write one</a>?", 'bpgr' ), bp_get_group_permalink() . $bp->group_reviews->slug . '/write/' ) ?></p>
		</div>

	<?php endif; ?>
</div>
