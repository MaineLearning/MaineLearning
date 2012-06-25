<?php

class Related_Items_Admin extends Related_Items {
	/**
	 * Error messages to diplay
	 *
	 * @var array
	 */
	private $_messages = array();
		
	/**
	 * Class constructor
	 *
	 */
	public function __construct() {
		$this->_plugin_dir   = DIRECTORY_SEPARATOR . str_replace(basename(__FILE__), null, plugin_basename(__FILE__));
		$this->_settings_url = 'options-general.php?page=' . plugin_basename(__FILE__);;

		$allowed_options = array();
		
		
		if(array_key_exists('option_name', $_GET) && array_key_exists('option_value', $_GET)
			&& in_array($_GET['option_name'], $allowed_options)) {
			update_option($_GET['option_name'], $_GET['option_value']);
			
			header("Location: " . $this->_settings_url);
			die();	
		
		} else {
			
			// add plugin "Settings" action on plugin list
			add_action('plugin_action_links_' . plugin_basename(RI_LOADER), array(&$this, 'add_plugin_actions'));
			
			// add links for plugin help, donations,...
			add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
			
			// push options page link, when generating admin menu
			add_action('admin_menu', array(&$this, 'adminMenu'));
                  
                  	
                 
                  
			parent::__construct();
                  
		}
          
	}
	
	/**
	 * Add "Settings" action on installed plugin list
	 */
	public function add_plugin_actions($links) {
		array_unshift($links, '<a href="options-general.php?page=' . plugin_basename(__FILE__) . '">' . __('Settings') . '</a>');
		
		return $links;
	}
	
	/**
	 * Add links on installed plugin list
	 */
	public function add_plugin_links($links, $file) {
		if($file == plugin_basename(RI_LOADER)) {
			$links[] = '<a href="http://MyWebsiteAdvisor.com">Premium Plugins</a>';
		}
		
		return $links;
	}
	
	/**
	 * Add menu entry 
	 */
	public function adminMenu() {		
		// add option in admin menu, for setting options
		$plugin_page = add_options_page('Related Items', 'Related Items', 8, __FILE__, array(&$this, 'optionsPage'));


	}
	

	

	
	/**
	 * Display options page
	 */
	public function optionsPage() {
		// if user clicked "Save Changes" save them
		if(isset($_POST['Submit'])) {
			foreach($this->_options as $option => $value) {
				if(array_key_exists($option, $_POST)) {
					update_option($option, $_POST[$option]);
				} else {
					update_option($option, $value);
				}
			}

			$this->_messages['updated'][] = 'Options updated!';
		}

	
		
	
		foreach($this->_messages as $namespace => $messages) {
			foreach($messages as $message) {
?>
<div class="<?php echo $namespace; ?>">
	<p>
		<strong><?php echo $message; ?></strong>
	</p>
</div>
<?php
			}
		}
?>
<script type="text/javascript">var wpurl = "<?php bloginfo('wpurl'); ?>";</script>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Related Items Manager</h2>

	<form method="post" action="">
	<h3>Enable Relationships for the Following Post Types</h3>
	<?
                                                    
                  $CPTs = get_post_types(array(), "objects");
                  
                  foreach($CPTs as $type){
                    $type_name = $type->labels->name;
                    $type_name2 = $type->name; 
                                                    
                     $selected_types = get_option('related-items-selected-types');                               
                                                    
                    if(in_array($type_name2,  $selected_types)){
                    
                       	echo "<p><input type='checkbox' checked='checked' name='related-items-selected-types[]' value='".$type->name."'> ";
                        echo $type->labels->name . " (" . $type->name . ")</p>";                              
                    }else{
                    	echo "<p><input type='checkbox' name='related-items-selected-types[]' value='".$type->name."'> ";
                        echo $type->labels->name . " (" . $type->name . ")</p>"; 
                                                    
                    }

                  }
                                                    
                                                    
                  ?>
	
           <p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
			</p>


		</form>
 
</div>
<?php
	}

}

                                                   
?>