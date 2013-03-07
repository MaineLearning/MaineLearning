<?php


class Custom_CSS_Manager_Plugin{

	//plugin version number
	private $version = "1.5";
	


	//holds simple security settings page class
	private $settings_page;
	

	
	//options are: edit, upload, link-manager, pages, comments, themes, plugins, users, tools, options-general
	private $page_icon = "options-general"; 	
	
	//settings page title, to be displayed in menu and page headline
	private $plugin_title = "Custom CSS Manager";
	
	//page name
	private $plugin_name = "custom-css-manager";
	
	//will be used as option name to save all options
	private $setting_name = "custom-css-manager-settings";
	

	
	//holds plugin options
	private $opt = array();




	//initialize the plugin class
	public function __construct() {
		
		$this->opt = get_option($this->setting_name);
		
		
		add_action( 'admin_init', array(&$this, 'update_css_manager_settings') );
		
		//initialize plugin settings
        add_action( 'admin_init', array(&$this, 'settings_page_init') );
		
		//create menu in wp admin menu
        add_action( 'admin_menu', array(&$this, 'admin_menu') );
		
		//add help menu to settings page
		add_filter( 'contextual_help', array(&$this,'admin_help'), 10, 3);	
		
		// add plugin "Settings" action on plugin list
		add_action('plugin_action_links_' . plugin_basename(CCM_LOADER), array(&$this, 'add_plugin_actions'));
		
		// add links for plugin help, donations,...
		add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
		
		
		add_action( 'wp_head', array(&$this, 'add_custom_css') );
		add_action( 'admin_head', array(&$this, 'add_custom_css') );
		//add_action( 'admin_head', array(&$this, 'add_codemirror_support') );

		
		//add_action( 'admin_head', array($backup_manager, 'screen_options') );
		//add_action( 'admin_menu', array($backup_manager, 'simple_backup_admin_menu') );

		
	}
	
	
	
	
	public function update_css_manager_settings(){

		$old_options = get_option('custom_css_code');
		$new_options = get_option($this->setting_name);
		
		if( !isset($new_options['css_settings']['css_code']) && isset($old_options) && $old_options <> ''){
		
			$options['css_settings']['css_code'] = stripslashes($old_options);
			
			update_option($this->setting_name, $options);
			//delete_option('custom_css_code');
			
		}
		
	}



	//setup the plugin settings page
	public function settings_page_init() {

		$this->settings_page  = new Custom_CSS_Manager_Settings_Page( $this->setting_name );
		
        //set the settings
        $this->settings_page->set_sections( $this->get_settings_sections() );
        $this->settings_page->set_fields( $this->get_settings_fields() );
		$this->settings_page->set_sidebar( $this->get_settings_sidebar() );

		//$this->build_optional_tabs();
		
        //initialize settings
        $this->settings_page->init();
    }




   /**
     * Returns all of the settings sections
     *
     * @return array settings sections
     */
    public function get_settings_sections() {
	
		$settings_sections = array(
			array(
				'id' => 'css_settings',
				'title' => __( 'Cascading Style Sheet', $this->plugin_name )
			)
		);

								
        return $settings_sections;
    }


    /**
     * Returns all of the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields() {
		$settings_fields = array(
			'css_settings' => array(
				array(
                    'name' => 'css_code',
                    'label' => __( 'CSS Code', $this->plugin_name ),
                    'desc' => __( 'Custom Cascading Style Sheet Code', $this->plugin_name ),
                    'type' => 'textarea'
                )
			)
		);
		
        return $settings_fields;
    }



	

	//plugin settings page template
	public function plugin_settings_page(){
	
		echo "<style> 
		.form-table{ clear:left; } 
		.nav-tab-wrapper{ margin-bottom:0px; }
		</style>";
		
		echo $this->display_social_media(); 
		
        echo '<div class="wrap" >';
		
			echo '<div id="icon-'.$this->page_icon.'" class="icon32"><br /></div>';
			
			echo "<h2>".$this->plugin_title." Plugin Settings</h2>";
			
			$this->settings_page->show_tab_nav();
			
			echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
			
				echo '<div class="inner-sidebar">';
					echo '<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">';
					
						$this->settings_page->show_sidebar();
					
					echo '</div>';
				echo '</div>';
			
				echo '<div class="has-sidebar" >';			
					echo '<div id="post-body-content" class="has-sidebar-content">';
						
						$this->add_codemirror_support();
							
						$this->settings_page->show_settings_forms();
						
						echo "<script language='javascript'>var editor = CodeMirror.fromTextArea(document.getElementById('custom-css-manager-settings[css_settings][css_code]'), { lineNumbers: true });</script>";
						
					echo '</div>';
				echo '</div>';
				
			echo '</div>';
			
        echo '</div>';
		
    }





	function add_codemirror_support() { ?>
		<!-- Codemirror Support Start -->
		<link type="text/css" rel="stylesheet" href="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/codemirror.css"></link>
		<link type="text/css" rel="stylesheet" href="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/default.css"></link>
		<script language="javascript" src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/codemirror.js"></script>
		<script language="javascript" src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>codemirror/css.js"></script>
		<!-- Codemirror Support End -->
	<?php }




	function add_custom_css() {
		$custom_css_code = $this->opt['css_settings']['css_code'];
		
		if (!empty($custom_css_code)) {
			echo "<!-- Custom CSS Manager Plguin -->\n<style type=\"text/css\">\n".stripslashes($custom_css_code)."</style>\n<!-- Custom CSS Manager Plguin End -->";
		}
	}
	
	
	

   	public function admin_menu() {
		
        $this->page_menu = add_options_page( $this->plugin_title, $this->plugin_title, 'manage_options',  $this->setting_name, array($this, 'plugin_settings_page') );
		//add_submenu_page('themes.php','Custom CSS', 'Custom CSS', 'manage_options',  $this->setting_name, array($this, 'plugin_settings_page') );
    }


	public function admin_help($contextual_help, $screen_id, $screen){
		
		if ( $screen_id == $this->page_menu  ) {
				
			$support_the_dev = $this->display_support_us();
			$screen->add_help_tab(array(
				'id' => 'developer-support',
				'title' => "Support the Developer",
				'content' => "<h2>Support the Developer</h2><p>".$support_the_dev."</p>"
			));
			
			$screen->add_help_tab(array(
				'id' => 'plugin-support',
				'title' => "Plugin Support",
				'content' => "<h2>{$this->plugin_title} Support</h2><p>For {$this->plugin_title} Plugin Support please visit <a href='http://mywebsiteadvisor.com/support/' target='_blank'>MyWebsiteAdvisor.com</a></p>"
			));
			
			

			$screen->set_help_sidebar("<p>Please Visit us online for more Free WordPress Plugins!</p><p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/' target='_blank'>MyWebsiteAdvisor.com</a></p><br>");
			
		}
			
		

	}
	
	
	
	private function do_diagnostic_sidebar(){
	
		ob_start();
		
			echo "<p>Plugin Version: $this->version</p>";
				
			echo "<p>Server OS: ".PHP_OS."</p>";
			
			echo "<p>Required PHP Version: 5.2+<br>";
			echo "Current PHP Version: " . phpversion() . "</p>";
		
						
			echo "<p>Memory Use: " . number_format(memory_get_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
			
			echo "<p>Peak Memory Use: " . number_format(memory_get_peak_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
			
			if(function_exists('sys_getloadavg')){
				$lav = sys_getloadavg();
				echo "<p>Server Load Average: ".$lav[0].", ".$lav[1].", ".$lav[2]."</p>";
			}
	
		return ob_get_clean();
				
	}
	
	
	
	
	
	
	private function get_settings_sidebar(){
	
		$plugin_resources = "<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/{$this->plugin_name}/' target='_blank'>Plugin Homepage</a></p>
			<p><a href='http://mywebsiteadvisor.com/support/'  target='_blank'>Plugin Support</a></p>
			<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Contact Us</a></p>
			<p><a href='http://wordpress.org/support/view/plugin-reviews/{$this->plugin_name}-plugin?rate=5#postform'  target='_blank'>Rate and Review This Plugin</a></p>";
	
		$more_plugins = "<p><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/'  target='_blank'>Premium WordPress Plugins!</a></p>
			<p><a href='http://profiles.wordpress.org/MyWebsiteAdvisor/'  target='_blank'>Free Plugins on Wordpress.org!</a></p>
			<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/'  target='_blank'>Free Plugins on MyWebsiteAdvisor.com!</a></p>";
	
		$follow_us = "<p><a href='http://facebook.com/MyWebsiteAdvisor/'  target='_blank'>Follow us on Facebook!</a></p>
			<p><a href='http://twitter.com/MWebsiteAdvisor/'  target='_blank'>Follow us on Twitter!</a></p>
			<p><a href='http://www.youtube.com/mywebsiteadvisor'  target='_blank'>Watch us on YouTube!</a></p>
			<p><a href='http://MyWebsiteAdvisor.com/'  target='_blank'>Visit our Website!</a></p>";
	
		
	
		$sidebar_info = array(
			array(
				'id' => 'diagnostic',
				'title' => 'Plugin Diagnostic Check',
				'content' => $this->do_diagnostic_sidebar()		
			),
			array(
				'id' => 'resources',
				'title' => 'Plugin Resources',
				'content' => $plugin_resources	
			),
			array(
				'id' => 'more_plugins',
				'title' => 'More Plugins',
				'content' => $more_plugins	
			),
			array(
				'id' => 'follow_us',
				'title' => 'Follow MyWebsiteAdvisor',
				'content' => $follow_us	
			)
		);
		
		return $sidebar_info;

	}






		//build optional tabs, using debug tools class worker methods as callbacks
	private function build_optional_tabs(){
	
		//general debug settings
		$plugin_debug = array(
			'id' => 'plugin_debug',
			'title' => __( 'Plugin Settings Debug', $this->plugin_name ),
			'callback' => array(&$this, 'show_plugin_settings')
		);

		//$enabled = isset($this->opt['debug_settings']['enable_display_plugin_settings']) ? $this->opt['debug_settings']['enable_display_plugin_settings'] : 'false';
		//if( $enabled === 'true' ){ 	
		$this->settings_page->add_section( $plugin_debug );
		//}
		
	}
	

 

	// displays the plugin options array
	public function show_plugin_settings(){
				
		echo "<pre>";
			print_r($this->opt);
		echo "</pre>";
			
	}





	/**
	 * Add "Settings" action on installed plugin list
	 */
	public function add_plugin_actions($links) {
		array_unshift($links, '<a href="options-general.php?page=' . $this->setting_name . '">' . __('Settings') . '</a>');
		
		return $links;
	}
	
	/**
	 * Add links on installed plugin list
	 */
	public function add_plugin_links($links, $file) {
		if($file == plugin_basename(SB_LOADER)) {
			$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
			$links[] = '<a href="'.$rate_url.'" target="_blank" title="Click Here to Rate and Review this Plugin on WordPress.org">Rate This Plugin</a>';
		}
		
		return $links;
	}
	
	
	public function display_support_us(){
				
		$string = '<p><b>Thank You for using the '.$this->plugin_title.' Plugin for WordPress!</b></p>';
		$string .= "<p>Please take a moment to <b>Support the Developer</b> by doing some of the following items:</p>";
		
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
		$string .= "<li><a href='$rate_url' target='_blank' title='Click Here to Rate and Review this Plugin on WordPress.org'>Click Here</a> to Rate and Review this Plugin on WordPress.org!</li>";
		
		$string .= "<li><a href='http://facebook.com/MyWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Facebook'>Click Here</a> to Follow MyWebsiteAdvisor on Facebook!</li>";
		$string .= "<li><a href='http://twitter.com/MWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Twitter'>Click Here</a> to Follow MyWebsiteAdvisor on Twitter!</li>";
		$string .= "<li><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/' target='_blank' title='Click Here to Purchase one of our Premium WordPress Plugins'>Click Here</a> to Purchase Premium WordPress Plugins!</li>";
	
		return $string;
	}
	
	
	
	
	
	public function display_social_media(){
	
		$social = '<style>
	
		.fb_edge_widget_with_comment {
			position: absolute;
			top: 0px;
			right: 200px;
		}
		
		</style>
		
		<div  style="height:20px; vertical-align:top; width:25%; float:right; text-align:right; margin-top:5px; padding-right:16px; position:relative;">
		
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=253053091425708";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, "script", "facebook-jssdk"));</script>
			
			<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
			
			
			<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		
		</div>';
		
		return $social;

	}	

	
}

?>
