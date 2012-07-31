<?php 

/**
 * RSS widget class
 *
 * @since 2.8.0
 */
class learning_registry_search extends WP_Widget_RSS {

	function learning_registry_search() {
	
		$widget_ops = array( 'description' => __('Searches the learning registry for content') );
		$this->WP_Widget( 'learning_registry_search', __('Learning Registry Search'), $widget_ops);
		
	}

	function widget($args, $instance) {
	
		global $post;

		if ( isset($instance['error']) && $instance['error'] )
			return;
			
		$words = array();	
				
		if(!is_home()){
						
			global $post;
		
			$post_categories = wp_get_post_categories($post->ID);
								
			while($post_category = array_pop($post_categories)){

				$category = get_category( $post_category );
				
				array_push($words,$category->name);
			
			}	
		
		}else{			

			$args = array(
				'type'                     => 'post',
				'orderby'                  => 'count',
				'order'                    => 'DESC',
				'hide_empty'               => 1,
				'taxonomy'                 => 'category',
				'pad_counts'               => 1,
				'number'				   => 1);
				
			$categories = get_categories( $args );	
			
			$words[] = $categories[0]->slug;

		}
		
		?>
		
		<script type="text/javascript" language="javascript">
					
			lreg_call('<?PHP echo implode(",",$words); ?>', "learning_registry_widget", '<?PHP echo $instance["node"]; ?>', '<?PHP echo $instance["number_items"]; ?>');
				
		</script>						
			
		<li id='learning_registry_widget' class="widget-container"></li>	
			
		<?PHP
				
	}

	function form($instance) {		
	
		echo '<div id="learning_registry_search-widget-form">';		
		echo '<p><label for="' . $this->get_field_id("node") .'">Learning Registry Node:</label>';
		echo '<input type="text" name="' . $this->get_field_name("node") . '" size="35" '; 
		echo 'id="' . $this->get_field_id("node") . '" value="' . $instance["node"] . '" /></p>';
		echo '<p><label for="' . $this->get_field_id("number_items") .'">Number of items:</label>';
		echo '<input type="text" name="' . $this->get_field_name("number_items") . '" '; 
		echo 'id="' . $this->get_field_id("number_items") . '" value="' . $instance["number_items"] . '" /></p>';
		echo '</div>';
	}

	
	
	function update($new_instance, $old_instance) {
	
		$instance = $old_instance;		
		$instance['node'] = $new_instance['node'];
		$instance['number_items'] = $new_instance['number_items'];	
		return $instance;
		
	}
	
}

 ?>