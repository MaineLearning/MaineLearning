<?php

/**
 * Implementation of highest-rated groups widget
 *
 * @package BP Group Reviews
 * @since 1.2
 */
class RatingWidget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @package BP Group Reviews
	 * @since 1.2
	 */
	function RatingWidget() {
		parent::WP_Widget( false, __( 'BP Group Ratings', 'bpgr' ) );
	}

	/**
	 * Renders the widget on the front end
	 *
	 * @package BP Group Reviews
	 * @since 1.2
	 */
	function widget( $args, $instance ) {
		global $bp, $wpdb, $groups_template;
		
		extract( $args );
		
		$title = esc_attr( $instance['title'] );
		$number = empty( $instance['number'] ) ? 3 : (int)$instance['number'];
		
		$sql = apply_filters( 'bpgr_groups_data_sql', $wpdb->prepare( "
		SELECT m1.group_id, m1.meta_value AS rating, m2.meta_value AS rating_count  
		FROM {$bp->groups->table_name_groupmeta} m1 
		LEFT JOIN {$bp->groups->table_name_groupmeta} m2 ON (m1.group_id = m2.group_id) 
		WHERE m1.meta_key = 'bpgr_rating'
		AND m2.meta_key = 'bpgr_how_many_ratings'
		ORDER BY rating DESC
		LIMIT 0, %d",
		$number
		) );
		
		$ratings = $wpdb->get_results( $sql, ARRAY_A );
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul class="item-list">';
		
		foreach( $ratings as $rating ) {
			$group = new BP_Groups_Group( $rating['group_id'] );
			$groups_template->group = $group; ?>
			<li>
			 <a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar_mini(); ?></a>
			 <a href="<?php bp_group_permalink(); ?>"><?php bp_group_name(); ?></a>
			 <span><?php echo bpgr_get_plugin_rating_html($rating['rating'], $rating['rating_count']); ?></span>
			 </li>
			<?php
		}
		
		echo "</ul>";
		echo $after_widget;
	}
	
	function form( $instance ) {				
		$title = empty( $instance['title'] ) ? __( 'Highest Rated Groups', 'bpgr' ) : esc_attr( $instance['title'] );
		$number = empty( $instance['number'] ) ? 3 : (int)$instance['number'];

		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'bpgr' ); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of groups to display:', 'bpgr' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" /></label></p>
		<?php 
	}
}

add_action('widgets_init', create_function('', 'return register_widget("RatingWidget");'));

?>
