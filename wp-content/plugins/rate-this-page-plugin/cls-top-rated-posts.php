<?php

class RTP_TopRatedWidget extends WP_Widget {
	function RTP_TopRatedWidget() {
		parent::WP_Widget( 'rtp-top-rated', 'RTP Top Rated', array( 'description' => 'A widget to display top rated posts or pages' ) );
	}
	
	function widget( $args, $instance ) {
		global $wpdb;
		
		extract( $args );
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		$type = $instance['type'];
		
		$data = rtp_top_rated( $instance['todisplay'], ($type == 'post') ? false : true );
		
		echo $before_widget;
		
		if ( !empty($title) ) { echo $before_title . $title . $after_title; }
		
		?>
		<ul id="rtp-feedback-wpanel" style="padding: 10px 0 10px 10px;">
			<?php if(!empty($data)) : ?>
				<?php foreach( $data as $newdata ) : ?>
				<?php $post_data = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = $newdata->post_id"); ?>
				<li>
					<script>
						jQuery(document).ready(function($) {
							$('#rtp-top-rated<?php echo $newdata->post_id; ?>').raty({
								half:		true,
								path:		'<?php echo RTP_PLUGIN_DIR_IMG; ?>',
								start:		<?php echo $newdata->rate_average; ?>,
								readOnly:	true,
								hintList:	['','','','','']
							});
						});
					</script>
					<div id="rtp-top-rated<?php echo $newdata->post_id; ?>" style="margin: 0;"></div>
					<a href="<?php echo $post_data->guid; ?>"><?php echo $post_data->post_title; ?></a>
				</li>
				<?php endforeach; ?>
			<?php else: ?>
				<li>No Results for <?php echo $title; ?></li>
			<?php endif; ?>
		</ul>
		<?php
			
		echo $after_widget;
	}
	
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
		} else {
			$title = __( 'Top Rated' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('todisplay'); ?>">No. to Display:</label>
			<select id="<?php echo $this->get_field_id('todisplay'); ?>" name="<?php echo $this->get_field_name('todisplay'); ?>" class="widefat" style="width: 25%">
				<?php
					$no_of_display = array( 10, 15, 20 );
					foreach ( $no_of_display as $nod ) {
						echo "<option value='$nod'";
						if ( $instance['todisplay'] == $nod ) {
							echo "selected='selected'";
						}
						echo ">$nod</option>";
					}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('type'); ?>">Post Type:</label>
			<select id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" class="widefat" style="width: 25%">
				<?php
					$post_type = array( 'post', 'page' );
					foreach ( $post_type as $pt ) {
						echo "<option value='$pt'";
						if ( $instance['type'] == $pt ) {
							echo "selected='selected'";
						}
						echo ">".ucfirst($pt)."</option>";
					}
				?>
			</select>
		</p>
		<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['todisplay'] = strip_tags($new_instance['todisplay']);
		$instance['type'] = strip_tags($new_instance['type']);
		return $instance;
	}
}
?>