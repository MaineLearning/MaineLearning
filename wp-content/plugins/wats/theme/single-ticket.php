<?php get_header(); ?>

	<div id="primary">
		<div id="content" role="main">
		
	<?php 
	if (have_posts()) : while (have_posts()) : the_post(); 
	
	if (wats_check_visibility_rights())
	{
	?>
		<nav id="nav-single">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentyeleven' ); ?></h3>
			<span class="nav-previous"><?php previous_post_link('&larr; %link'); ?></span>
			<span class="nav-next"><?php next_post_link('%link &rarr;'); ?></span>
		</nav>
	
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-meta">
				<?php echo get_the_date(); ?>
			</div><!-- .entry-meta -->
		</header><!-- .entry-header -->
		
		<div class="entry-content">
			<div id="wats_single_ticket_metas">
			<?php if ($wats_settings['ticket_priority_key_enabled'] == 1)
					echo '<div id="wats_single_ticket_priority" class="wats_priority_'.get_post_meta($post->ID,'wats_ticket_priority',true).'"><label class="wats_label">'.__("Current priority : ",'WATS').'</label>'.wats_ticket_get_priority($post).'</div>';
				if ($wats_settings['ticket_status_key_enabled'] == 1)
					echo '<div id="wats_single_ticket_status" class="wats_status_'.get_post_meta($post->ID,'wats_ticket_status',true).'"><label class="wats_label">'.__("Current status : ",'WATS').'</label>'.wats_ticket_get_status($post).'</div>';
				if ($wats_settings['ticket_type_key_enabled'] == 1)
					echo '<div id="wats_single_ticket_type" class="wats_type_'.get_post_meta($post->ID,'wats_ticket_type',true).'"><label class="wats_label">'.__("Ticket type : ",'WATS').'</label>'.wats_ticket_get_type($post).'</div>';
				if ($wats_settings['ticket_product_key_enabled'] == 1)
					echo '<div id="wats_single_ticket_product" class="wats_product_'.get_post_meta($post->ID,'wats_ticket_product',true).'"><label class="wats_label">'.__("Ticket product : ",'WATS').'</label>'.wats_ticket_get_product($post).'</div>';
				echo '<div id="wats_single_ticket_originator"><label class="wats_label">'.__("Ticket originator : ",'WATS').'</label>'; the_author(); echo '</div>';
				if (current_user_can('administrator'))
				{
					$ticket_author_name = get_post_meta($post->ID,'wats_ticket_author_name',true);
					if ($ticket_author_name)
						echo '<div id="wats_single_ticket_author_name"><label class="wats_label">'.__('Ticket author name : ','WATS').'</label>'.$ticket_author_name.'</div>';
					$ticket_author_email = get_post_meta($post->ID,'wats_ticket_author_email',true);
					if ($ticket_author_email)
						echo '<div id="wats_single_ticket_author_email"><label class="wats_label">'.__('Ticket author email : ','WATS').'</label>'.'<a href="mailto:'.$ticket_author_email.'">'.$ticket_author_email.'</a></div>';
					$ticket_author_url = get_post_meta($post->ID,'wats_ticket_author_url',true);
					if ($ticket_author_url)
						echo '<div id="wats_single_ticket_author_url"><label class="wats_label">'.__('Ticket author url : ','WATS').'</label>'.'<a href="'.$ticket_author_url.'">'.$ticket_author_url.'</a></div>'; 
				} ?>
			</div>
			<div class="entry">
				<?php the_content('<p class="serif">Read the rest of the ticket &raquo;</p>'); ?>
				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
				<?php the_tags( '<p>'.__('Tags','WATS').'&nbsp;: ', ', ', '</p>'); ?> 
				<p class="postmetadata alt">
					<small>
						<?php printf(__('This entry was submited on %1$s at %2$s and is filed under %3$s.', 'WATS'), get_the_time(__('l, F jS, Y', 'WATS')), get_the_time(), get_the_category_list(', ')); ?>
						<?php printf(__("You can follow any responses to this entry through the <a href='%s'>RSS 2.0</a> feed.", "WATS"), get_post_comments_feed_link()); ?> 
						<?php if ( comments_open() && pings_open() ) {
							// Both Comments and Pings are open ?>
							<?php printf(__('You can <a href="#respond">leave an update</a>, or <a href="%s" rel="trackback">trackback</a> from your own site.', 'WATS'), get_trackback_url()); ?>

						<?php } elseif ( !comments_open() && pings_open() ) {
							// Only Pings are Open ?>
							<?php printf(__('Responses are currently closed, but you can <a href="%s" rel="trackback">trackback</a> from your own site.', 'WATS'), trackback_url(false)); ?>

						<?php } elseif ( comments_open() && !pings_open() ) {
							// Comments are open, Pings are not ?>
							<?php _e('You can skip to the end and leave an update. Pinging is currently not allowed.', 'WATS'); ?>

						<?php } elseif ( !comments_open() && !pings_open() ) {
							// Neither Comments, nor Pings are open ?>
							<?php _e('Both comments and pings are currently closed.', 'WATS'); ?>

						<?php } wats_edit_ticket_link(__('Edit this entry', 'WATS'),'','.'); ?>
					</small>				
				</p>
			</div>
		</div>
	<?php 
		comments_template(); 
	}
	else
	{
		wats_ticket_access_denied();
	}
	?>

	<?php endwhile; else: ?>
		<p><?php _e('Sorry, no tickets matched your criteria.', 'WATS'); ?></p>
	<?php endif; ?>

</div>
</div>
<?php get_footer(); ?>