<?php

class WPV_Widget extends WP_Widget{
    
    function WPV_Widget(){
        $widget_ops = array('classname' => 'widget_wp_views', 'description' => __( 'WP Views widget', 'wpv-views') );
        $this->WP_Widget('wp_views', __('WP Views', 'wpv-views'), $widget_ops);
    }
    
    function widget( $args, $instance ) {
        global $WP_Views;
        extract($args);
        $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		$WP_Views->set_widget_view_id($instance['view']);
		
        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;

        $out = $WP_Views->render_view_ex($instance['view'], $instance['view']);
        $out = wpv_do_shortcode($out);
        
    	$post_type_object = get_post_type_object( 'view' );
    	if ( current_user_can( $post_type_object->cap->edit_post, $instance['view'] ) ) {
            $out .= $WP_Views->edit_post_link('', $instance['view']);
        }
        
        echo $out;

        echo $after_widget;

		$WP_Views->set_widget_view_id(0);
    }
    
    function form( $instance ) {
        global $WP_Views;
        $views = $WP_Views->get_views();        
        $instance = wp_parse_args( (array) $instance, 
            array( 
                'title' => '',
                'view'  => false
            ) 
        );
        $title = $instance['title'];
        $view  = $instance['view'];
         ?>
        
        <?php if($views): ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
        
            <p style="float: right;">
            <?php _e('View:', 'wpv-views'); ?> <select name="<?php echo $this->get_field_name('view'); ?>">
            <?php foreach($views as $v): ?>
            <option value="<?php echo $v->ID ?>"<?php if($view == $v->ID): ?> selected="selected"<?php endif;?>><?php echo esc_html($v->post_title) ?></option>
            <?php endforeach;?>             
            </select>
            </p>
            <br clear="all">
        <?php else: ?>
            <?php
                if (!$WP_Views->is_embedded()) {
                    printf(__('No views defined. You can add them <a%s>here</a>.'), ' href="' . admin_url('edit.php?post_type=view'). '"');
                }
            ?>
        <?php endif;?>
        <?php
    }
    
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $new_instance = wp_parse_args((array) $new_instance, 
            array( 
                'title' => '',
                'view'  => false
            ) 
        );
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['view']  = $new_instance['view'];
        
        return $instance;
    }
    
}
  
?>
