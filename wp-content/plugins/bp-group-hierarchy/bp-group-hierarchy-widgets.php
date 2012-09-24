<?php

/* Register widgets for groups component */
add_action('widgets_init', 'bp_group_hierarchy_init_widgets' );

function bp_group_hierarchy_init_widgets() {
	register_widget('BP_Toplevel_Groups_Widget');
	register_widget('BP_Group_Navigator_Widget');
}

/*** TOPLEVEL GROUPS WIDGET *****************/
class BP_Toplevel_Groups_Widget extends WP_Widget {
	function bp_toplevel_groups_widget() {
		parent::WP_Widget( false, $name = __( 'Toplevel Groups', 'bp-group-hierarchy' ), array( 'description' => __( 'A list of top-level BuddyPress groups', 'bp-group-hierarchy' ) ) );
	}

	function widget($args, $instance) {
		global $bp;

	    extract( $args );

		echo $before_widget;
		echo $before_title
		   . $instance['title']
		   . $after_title; ?>
		<?php if( ! class_exists('BP_Groups_Group') ) {
			 _e( 'You must enable Groups component to use this widget.', 'bp-group-hierarchy' );
			 return; 
		} ?>
		<?php if ( bp_has_groups_hierarchy( 'type=' . $instance['sort_type'] . '&per_page=' . $instance['max_groups'] . '&max=' . $instance['max_groups'] . '&parent_id=0' ) ) : ?>

			<ul id="toplevel-groups-list" class="item-list">
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li>
						<div class="item-avatar">
							<a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar_thumb() ?></a>
						</div>

						<div class="item">
							<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php echo strip_tags(bp_get_group_description_excerpt()) ?>"><?php bp_group_name() ?></a></div>
							<div class="item-meta"><span class="activity">
								<?php switch($instance['sort_type']) {
										case 'newest':
											printf( __( 'created %s', 'buddypress' ), bp_get_group_date_created() );
											break;
										case 'alphabetical':
										case 'active':
											printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() );
											break;
										case 'popular':
											bp_group_member_count();
											break;
										case 'prolific':
											printf( _n( '%d member group', '%d member groups', bp_group_hierarchy_has_subgroups(), 'bp-group-hierarchy'), bp_group_hierarchy_has_subgroups() );
									}
										
								?>
							</span></div>
							<?php if($instance['show_desc']) { ?>
							<div class="item-desc"><?php bp_group_description_excerpt() ?></div>
							<?php } ?>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
			<?php wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' ); ?>
			<input type="hidden" name="toplevel_groups_widget_max" id="toplevel_groups_widget_max" value="<?php echo esc_attr( $instance['max_groups'] ); ?>" />

		<?php else: ?>

			<div class="widget-error">
				<?php _e('There are no groups to display.', 'buddypress') ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_groups'] = strip_tags( $new_instance['max_groups'] );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['sort_type'] = strip_tags( $new_instance['sort_type'] );
		$instance['show_desc'] = isset($new_instance['show_desc']) ? true : false;

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'max_groups' => 5, 'title'	=> __('Groups'), 'sort_type' => 'active' ) );
		$max_groups = strip_tags( $instance['max_groups'] );
		$title = strip_tags( $instance['title'] );
		$sort_type = strip_tags( $instance['sort_type'] );
		$show_desc = $instance['show_desc'] ? true : false;
		?>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_groups' ); ?>"><?php _e('Max groups to show:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_groups' ); ?>" name="<?php echo $this->get_field_name( 'max_groups' ); ?>" type="text" value="<?php echo esc_attr( $max_groups ); ?>" style="width: 30%" /></label></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort_type' ); ?>">
				<?php _e('Order by:', 'buddypress'); ?>
				<select name="<?php echo $this->get_field_name( 'sort_type' ); ?>">
					<option value="alphabetical" <?php selected($sort_type,'alphabetical') ?>><?php _e( 'Alphabetical', 'buddypress' ) ?></option>
					<option value="newest" <?php selected($sort_type,'newest') ?>><?php _e( 'Newly Created', 'buddypress' ) ?></option>
					<option value="active" <?php selected($sort_type,'active') ?>><?php _e( 'Last Active', 'buddypress' ) ?></option>
					<option value="popular" <?php selected($sort_type,'popular') ?>><?php _e( 'Most Popular', 'buddypress' ) ?></option>
					<option value="prolific" <?php selected($sort_type,'prolific') ?>><?php _e( 'Most Member Groups', 'bp-group-hierarchy' ) ?></option>
				</select>
			</label>
		</p>
		<p><label for="<?php echo $this->get_field_id( 'show_desc' ); ?>"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_desc' ); ?>" name="<?php echo $this->get_field_name( 'show_desc' ); ?>"<?php if($show_desc) echo ' checked'; ?> /> <?php _e('Show descriptions:', 'bp-group-hierarchy'); ?></label></p>
		<?php
	}
}

class BP_Group_Navigator_Widget extends WP_Widget {

	function __construct() {
		parent::WP_Widget( false, $name = __( 'Group Navigator', 'bp-group-hierarchy' ), array('description' => __( 'A list of member groups of the current group, or top-level groups anywhere else.', 'bp-group-hierarchy' ) ) );
	}
	
	function widget( $args, $instance ) {
		global $bp;
		
	    extract( $args );
		
		$parent_id = isset($bp->groups->current_group->id) ? $bp->groups->current_group->id : 0;
		
		echo $before_widget;
		echo $before_title;
		if($parent_id == 0) {
			echo $instance['title'];
		} else {
			echo $instance['sub_title'];
		}
		echo $after_title; ?>
		<?php if( ! class_exists('BP_Groups_Group') ) {
			 _e( 'You must enable Groups component to use this widget.', 'bp-group-hierarchy' );
			 return; 
		} ?>
		<?php if ( bp_has_groups_hierarchy( 'type=' . $instance['sort_type'] . '&per_page=' . $instance['max_groups'] . '&max=' . $instance['max_groups'] . '&parent_id=' . $parent_id ) ) : ?>

			<ul id="toplevel-groups-list" class="item-list">
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li>
						<div class="item-avatar">
							<a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar_thumb() ?></a>
						</div>

						<div class="item">
							<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php echo strip_tags(bp_get_group_description_excerpt()) ?>"><?php bp_group_name() ?></a></div>
							<div class="item-meta"><span class="activity">
								<?php switch($instance['sort_type']) {
										case 'newest':
											printf( __( 'created %s', 'buddypress' ), bp_get_group_date_created() );
											break;
										case 'alphabetical':
										case 'active':
											printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() );
											break;
										case 'popular':
											bp_group_member_count();
											break;
										case 'prolific':
											printf( _n( '%d member group', '%d member groups', bp_group_hierarchy_has_subgroups(), 'bp-group-hierarchy'), bp_group_hierarchy_has_subgroups() );
									}
										
								?>
							</span></div>
							<?php if($instance['show_desc']) { ?>
							<div class="item-desc"><?php bp_group_description_excerpt() ?></div>
							<?php } ?>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
			<?php wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' ); ?>
			<input type="hidden" name="toplevel_groups_widget_max" id="toplevel_groups_widget_max" value="<?php echo esc_attr( $instance['max_groups'] ); ?>" />

		<?php else: ?>

			<div class="widget-error">
				<?php _e('There are no groups to display.', 'buddypress') ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
		<?php
	}
	
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'max_groups' => 5, 'title'	=> __('Groups'), 'sub_title' => __('Member Groups', 'bp-group-hierarchy'), 'sort_type' => 'active' ) );
		$max_groups = strip_tags( $instance['max_groups'] );
		$title = strip_tags( $instance['title'] );
		$sub_title = strip_tags( $instance['sub_title'] );
		$sort_type = strip_tags( $instance['sort_type'] );
		$show_desc = $instance['show_desc'] ? true : false;
		?>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'sub_title' ); ?>"><?php _e('Title when on a group page:', 'bp-group-hierarchy'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'sub_title' ); ?>" name="<?php echo $this->get_field_name( 'sub_title' ); ?>" type="text" value="<?php echo esc_attr( $sub_title ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_groups' ); ?>"><?php _e('Max groups to show:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_groups' ); ?>" name="<?php echo $this->get_field_name( 'max_groups' ); ?>" type="text" value="<?php echo esc_attr( $max_groups ); ?>" style="width: 30%" /></label></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort_type' ); ?>">
				<?php _e('Order by:', 'buddypress'); ?>
				<select name="<?php echo $this->get_field_name( 'sort_type' ); ?>">
					<option value="alphabetical" <?php selected($sort_type,'alphabetical') ?>><?php _e( 'Alphabetical', 'buddypress' ) ?></option>
					<option value="newest" <?php selected($sort_type,'newest') ?>><?php _e( 'Newly Created', 'buddypress' ) ?></option>
					<option value="active" <?php selected($sort_type,'active') ?>><?php _e( 'Last Active', 'buddypress' ) ?></option>
					<option value="popular" <?php selected($sort_type,'popular') ?>><?php _e( 'Most Popular', 'buddypress' ) ?></option>
					<option value="prolific" <?php selected($sort_type,'prolific') ?>><?php _e( 'Most Member Groups', 'bp-group-hierarchy' ) ?></option>
				</select>
			</label>
		</p>
		<p><label for="<?php echo $this->get_field_id( 'show_desc' ); ?>"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_desc' ); ?>" name="<?php echo $this->get_field_name( 'show_desc' ); ?>"<?php if($show_desc) echo ' checked'; ?> /> <?php _e('Show descriptions:', 'bp-group-hierarchy'); ?></label></p>
		<?php
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_groups'] = strip_tags( $new_instance['max_groups'] );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['sub_title'] = strip_tags( $new_instance['sub_title'] );
		$instance['sort_type'] = strip_tags( $new_instance['sort_type'] );
		$instance['show_desc'] = isset($new_instance['show_desc']) ? true : false;

		return $instance;
	}
	
}

?>
