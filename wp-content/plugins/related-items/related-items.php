<?php

	class Related_Items {

    
          	protected $_options             = array(
                  'related-items-selected-types'       => array()
                 
		);
          
          
		// Constructor
		public function __construct() {
			
			$this->defineConstants();
						
			// Register hook to save the related items when saving the post
			add_action('save_post', array(&$this, 'save') );
			
			// Start the plugin
			add_action('admin_menu', array(&$this, 'start') );
			
			//automatically add the related items to the bottom of each page
			add_filter('the_content', array(&$this,'displayRelatedItems') );
                  
            		add_action('wp_head', array(&$this, 'loadCSS'));
		}
		
	
		
		// Defines a few static helper values we might need
		protected function defineConstants() {

			define('RELATED_VERSION', '1.1');
			define('RELATED_HOME', 'http://MyWebsiteAdvisor.com/');
			define('RELATED_FILE', plugin_basename(dirname(__FILE__)));
			define('RELATED_ABSPATH', str_replace('\\', '/', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__))));
			define('RELATED_URLPATH', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));
		}

		
		public function displayRelatedItems($content){

			return $content . $this->show(get_the_ID());
		}
			
		
		// Main function
		public function start() {
			
			// Load the scripts
			add_action('admin_print_scripts', array(&$this, 'loadScripts'));
			
			// Load the CSS
			add_action('admin_print_styles', array(&$this, 'loadCSS'));
                  	
			// Adds a meta box for related posts to the edit screen of each post type in WordPress
                  	foreach (get_post_types() as $post_type){
				add_meta_box($post_type . '-related-items-box', 'Related Items', array(&$this, 'displayMetaBox'), $post_type, 'normal', 'high');
                  	}
		}
          
          
                // Load CSS
                public function loadCSS() {
                
                        wp_enqueue_style('related-css', RELATED_URLPATH .'/styles.css', false, RELATED_VERSION, 'all');
                }

          
		// Load Javascript
		public function loadScripts() {
		
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('related-scripts', RELATED_URLPATH .'/scripts.js', false, RELATED_VERSION);
		}


		// Save related posts when saving the post
		public function save($id) {
			
			global $wpdb;
			
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

			if (!isset($_POST['related-items']) || empty($_POST['related-items'])) :
				delete_post_meta($id, 'related_items');
			else :
				update_post_meta($id, 'related_items', $_POST['related-items']);
			endif;			
		}


          
         
          
          
          
		// Creates the output on the post screen
		public function displayMetaBox() {
			
			global $post_ID;
			
			echo '<div id="related-items">';
                  
                  echo "<p>Select items to add a relationship, drag and drop related items to change the order.</p>";
                  
                                 //add new relationship meta box
                  
                 echo "<div class='new_relationship_form'>";
                 //echo "<h3>Add a New Relationship</h3>";
                    
                  echo 'Item Type: <select id="related-items-category-filter-select" name="related-items-category-filter-select">
				<option value="all">All</option>';
                  

                  	$CPTs = get_post_types(array(), "objects");
                  
                  foreach($CPTs as $type){
                     $selected_types = get_option('related-items-selected-types');                               
                    $type_name = $type->name;
                    
                    if(in_array($type_name,  $selected_types)){
                    	$type_label_name = $type->labels->name;
                      $type_name = $type->name;
                    	//echo "<br>";
                      
                      	echo "<option value='$type_name'>" . $type_label_name . "</option>";
                      
                    }
                    
                    
                  }
                  
                  echo "</select>";
                  
                  //echo "</p>";
                  
                  
                  
                  
                  
			//echo '<p>';
                          echo '   Select an Item: <select id="related-items-select" name="related-items-select" >
						<option value="0">Select</option>';
			
			$query = array(
				'nopaging' => true,
				'post__not_in' => array($post_ID),
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'post_type' => 'any',
				'orderby' => 'title',
				'order' => 'ASC'
			);
			
			$p = new WP_Query($query);
			
			if ($p->have_posts()) :
				while ($p->have_posts()) :
					$p->the_post();
					echo '<option value="' . get_the_ID() . '" class="'.get_post_type(get_the_ID()).'">' . get_the_title() . ' (' . ucfirst(get_post_type(get_the_ID())) . ')</option>';
				endwhile;
			endif;
			
			wp_reset_query();
								
			echo '</select>  ';
                  	echo "  <input type='button' id='add_relationship' value='Add Relationship'>";
			echo '</div>';
                  
                  
                  
			
			// Get related posts if existing
			$related_items = get_post_meta($post_ID, 'related_items', true);

                  
                  
                  //display existing relationships meta box
                  echo "<div id='related-items-box' class='related-items-box'>";
                  
                  if (!empty($related_items)) {
                    
                    
                    foreach($related_items as $related_item){
                          $related_post = get_post($related_item);
                      	$type_info = get_post_type_object($related_post->post_type);
                       
                      	
                          $item_data[$type_info->labels->name] .= '<div class="related-item" id="related-item-' . $r . '" title="Drag and Drop to Reorder">
                            <img src="/wp-content/plugins/related-items/images/bullet_green.png" title="This Relationship is Saved!">
                                  <input type="hidden" name="related-items[]" value="' . $related_item . '">
                                  <span class="related-item-title">' . $related_post->post_title . ' (' . ucfirst(get_post_type($related_post->ID)) . ')</span>
                                  <a href="#" title="Remove from list"><img src="/wp-content/plugins/related-items/images/delete.png"></a></div>';
                     }
                    
                    
                    
                    
                    foreach($item_data as $type => $item){
                      //echo "<p>".$type."</p>";
                      echo $item;
                      
                    }
                    
                  }
                
		echo "</div>";
                  echo "</div>";
                  
                  
                  
                  
  
		}


          
          
          
		//displays relationships on posts or pages
          
		public function show($id, $return = false) {

			global $wpdb;

                  	if (!empty($id) && is_numeric($id)) {
				$related_posts = get_post_meta($id, 'related_items', true);
				
                  		if (!empty($related_posts)) {
					$rel = array();
                                  	foreach ($related_posts as $related_post) {
						$post = get_post($related_post);
						$rel[] = $post;
                                        }
					
					// If value should be returned as array, return it
                                        if ($return) {
						return $rel;
						
					// Otherwise return a formatted list
                                        }else {
						$list_output = '<div class="related-items"><h3>Related Items</h3><hr>';
                                                     
                                                foreach ($rel as $related_post) {
                                                     $type = get_post_type_object($related_post->post_type);
                                                     //echo $type->labels->name;
                                                     $list_data[$type->labels->name] .= '<li><a href="' . get_permalink($related_post->ID) . '">' . $related_post->post_title . '</a></li>';
                				}
                  
                                                foreach($list_data as $type=>$data){
                                                  $list_output .= '<h4>'.$type.'</h4>';
                                                  $list_output .= '<ul class="related-items">'.$data.'</ul>';
                                                }
                  
						$list_output .= '</div>';
						
						return $list_output;
        				}
				}else{
					return false;
				}
			}else{
				return 'Invalid item ID specified';
			}
		}
	}
	

?>