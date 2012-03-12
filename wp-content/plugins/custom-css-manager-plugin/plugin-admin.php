<?php

class Custom_CSS_Manager_Admin extends Custom_CSS_Manager {
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
		
		add_action('wp_head', array(&$this, 'add_custom_css'));
		
		
		$allowed_options = array(
			
		);
		
		
		if(array_key_exists('option_name', $_GET) && array_key_exists('option_value', $_GET)
			&& in_array($_GET['option_name'], $allowed_options)) {
			update_option($_GET['option_name'], $_GET['option_value']);
			
			header("Location: " . $this->_settings_url);
			die();	
		
		} else {
			// register installer function
			register_activation_hook(CCM_LOADER, array(&$this, 'activateCustomCSSManager'));
			
			// add plugin "Settings" action on plugin list
			add_action('plugin_action_links_' . plugin_basename(CCM_LOADER), array(&$this, 'add_plugin_actions'));
			
			// add links for plugin help, donations,...
			add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
			
			// push options page link, when generating admin menu
			add_action('admin_menu', array(&$this, 'adminMenu'));
	
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
		if($file == plugin_basename(CCM_LOADER)) {
			$links[] = '<a href="http://MyWebsiteAdvisor.com">Premium Plugins</a>';
		}
		
		return $links;
	}
	
	/**
	 * Add menu entry 
	 */
	public function adminMenu() {		
		// add option in admin menu, for setting options
		$plugin_page = add_options_page('Custom CSS Manager', 'Custom CSS Manager', 8, __FILE__, array(&$this, 'optionsPage'));
		
		add_action( 'admin_head', array(&$this, 'add_custom_css') );
		add_action( 'admin_head', array(&$this, 'add_codemirror_support') );
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
	<h2>Custom CSS Manager</h2>

	<form method="post" action="">

	
	<?php $custom_css_code = $this->get_option('custom_css_code'); ?>

	Custom CSS Code:<br />
	 <textarea rows="20" cols="50"  name="custom_css_code" id="custom_css_code"  ><?php echo $custom_css_code; ?></textarea>
	 
	<script language="javascript">var editor = CodeMirror.fromTextArea(document.getElementById("custom_css_code"), { lineNumbers: true });</script>
	
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
			</p>


		</form>
	

</div>
<?php
	}





	function add_custom_css() {
		$custom_css_code = get_option('custom_css_code');
		if (!empty($custom_css_code)) {
			echo "<!-- Custom CSS Manager Plguin -->\n<style type=\"text/css\">\n".$custom_css_code."</style>\n<!-- Custom CSS Manager Plguin End -->";
		}
	}



	function add_codemirror_support() { ?>
		<!-- Codemirror Support Start -->
		<link type="text/css" rel="stylesheet" href="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/codemirror.css"></link>
		<link type="text/css" rel="stylesheet" href="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/default.css"></link>
		<script language="javascript" src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/codemirror.js"></script>
		<script language="javascript" src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/css.js"></script>
		<!-- Codemirror Support End -->
	<?php }



}

$css_manager = new Custom_CSS_Manager_Admin();
?>