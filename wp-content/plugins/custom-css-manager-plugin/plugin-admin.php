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
		
		
		
		// register installer function
		register_activation_hook(CCM_LOADER, array(&$this, 'activateCustomCSSManager'));
		
		// add plugin "Settings" action on plugin list
		add_action('plugin_action_links_' . plugin_basename(CCM_LOADER), array(&$this, 'add_plugin_actions'));
		
		// add links for plugin help, donations,...
		add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
		
		// push options page link, when generating admin menu
		add_action('admin_menu', array(&$this, 'admin_menu'));

		//add help menu
		add_filter('contextual_help', array(&$this,'admin_help'), 10, 3);
			
		
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
			$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
			$links[] = '<a href="'.$rate_url.'" target="_blank" title="Click Here to Rate and Review this Plugin on WordPress.org">Rate This Plugin</a>';
		}
		
		return $links;
	}
	
	/**
	 * Add menu entry 
	 */
	public function admin_menu() {		
		// add option in admin menu, for setting options
		global $custom_css_manager_admin_page;
		$custom_css_manager_admin_page = add_options_page('Custom CSS Manager', 'Custom CSS Manager', 'manage_options', __FILE__, array(&$this, 'optionsPage'));
		
		add_action( 'admin_head', array(&$this, 'add_custom_css') );
		add_action( 'admin_head', array(&$this, 'add_codemirror_support') );
	}
		

	public function admin_help($contextual_help, $screen_id, $screen){
	
		global $custom_css_manager_admin_page;
		
		if ($screen_id == $custom_css_manager_admin_page) {
			
			
			$support_the_dev = $this->display_support_us();
			$screen->add_help_tab(array(
				'id' => 'developer-support',
				'title' => "Support the Developer",
				'content' => "<h2>Support the Developer</h2><p>".$support_the_dev."</p>"
			));
			
			$screen->add_help_tab(array(
				'id' => 'plugin-support',
				'title' => "Plugin Support",
				'content' => "<h2>Support</h2><p>For Plugin Support please visit <a href='http://mywebsiteadvisor.com/support/' target='_blank'>MyWebsiteAdvisor.com</a></p>"
			));
			
	
			$screen->set_help_sidebar("<p>Please Visit us online for more Free WordPress Plugins!</p><p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/' target='_blank'>MyWebsiteAdvisor.com</a></p><br>");
			//$contextual_help = 'HELP!';
		}
			
		//return $contextual_help;

	}	
	
	
	
	public function display_support_us(){
				
		$string = '<p><b>Thank You for using the Custom CSS Manager Plugin for WordPress!</b></p>';
		$string .= "<p>Please take a moment to <b>Support the Developer</b> by doing some of the following items:</p>";
		
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
		$string .= "<li><a href='$rate_url' target='_blank' title='Click Here to Rate and Review this Plugin on WordPress.org'>Click Here</a> to Rate and Review this Plugin on WordPress.org!</li>";
		
		$string .= "<li><a href='http://facebook.com/MyWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Facebook'>Click Here</a> to Follow MyWebsiteAdvisor on Facebook!</li>";
		$string .= "<li><a href='http://twitter.com/MWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Twitter'>Click Here</a> to Follow MyWebsiteAdvisor on Twitter!</li>";
		$string .= "<li><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/' target='_blank' title='Click Here to Purchase one of our Premium WordPress Plugins'>Click Here</a> to Purchase Premium WordPress Plugins!</li>";
	
		return $string;
	}






	function html_print_box_header($id, $title, $right = false) {
		
		?>
		<div id="<?php echo $id; ?>" class="postbox">
			<h3 class="hndle"><span><?php echo $title ?></span></h3>
			<div class="inside">
		<?php
		
		
	}
	
	function html_print_box_footer( $right = false) {
		?>
			</div>
		</div>
		<?php
		
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


<style>

.fb_edge_widget_with_comment {
	position: absolute;
	top: 0px;
	right: 200px;
}

</style>

<div  style="height:20px; vertical-align:top; width:50%; float:right; text-align:right; margin-top:5px; padding-right:16px; position:relative;">

	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=253053091425708";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	
	<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
	
	
	<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>


</div>




<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Custom CSS Manager</h2>




	<form method="post" action="">


			<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
			
<?php $this->html_print_box_header('pl_diag',__('Plugin Diagnostic Check','diagnostic'),true); ?>

				<?php
				
				echo "<p>Plugin Version: $this->version</p>";
				
				echo "<p>Server OS: ".PHP_OS."</p>";
						
				echo "<p>Required PHP Version: 5.0+<br>";
				echo "Current PHP Version: " . phpversion() . "</p>";
				
	
				echo "<p>Memory Use: " . number_format(memory_get_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
				
				echo "<p>Peak Memory Use: " . number_format(memory_get_peak_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
		
		
				
				if(function_exists('sys_getloadavg')){
					$lav = sys_getloadavg();
					echo "<p>Server Load Average: ".$lav[0].", ".$lav[1].", ".$lav[2]."</p>";
				}	
				
				
				?>

<?php $this->html_print_box_footer(true); ?>



<?php $this->html_print_box_header('pl_resources',__('Plugin Resources','resources'),true); ?>
	<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/custom-css-manager-plugin/' target='_blank'>Plugin Homepage</a></p>
	<p><a href='http://mywebsiteadvisor.com/support/'  target='_blank'>Plugin Support</a></p>
	<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Contact Us</a></p>
	<p><a href='http://wordpress.org/support/view/plugin-reviews/custom-css-manager-plugin?rate=5#postform'  target='_blank'>Rate and Review This Plugin</a></p>
<?php $this->html_print_box_footer(true); ?>





<?php $this->html_print_box_header('more_plugins',__('More Plugins','more_plugins'),true); ?>
	
	<p><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/'  target='_blank'>Premium WordPress Plugins!</a></p>
	<p><a href='http://profiles.wordpress.org/MyWebsiteAdvisor/'  target='_blank'>Free Plugins on Wordpress.org!</a></p>
	<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/'  target='_blank'>Free Plugins on MyWebsiteAdvisor.com!</a></p>	
				
<?php $this->html_print_box_footer(true); ?>


<?php $this->html_print_box_header('follow',__('Follow MyWebsiteAdvisor','follow'),true); ?>

	<p><a href='http://facebook.com/MyWebsiteAdvisor/'  target='_blank'>Follow us on Facebook!</a></p>
	<p><a href='http://twitter.com/MWebsiteAdvisor/'  target='_blank'>Follow us on Twitter!</a></p>
	<p><a href='http://www.youtube.com/mywebsiteadvisor'  target='_blank'>Watch us on YouTube!</a></p>
	<p><a href='http://MyWebsiteAdvisor.com/'  target='_blank'>Visit our Website!</a></p>	
	
<?php $this->html_print_box_footer(true); ?>

</div>
</div>



	<div class="has-sidebar sm-padded" >			
		<div id="post-body-content" class="has-sidebar-content">
			<div class="meta-box-sortabless">
								
								
				
	
		
		<?php $this->html_print_box_header('custom_css',__('Custom CSS','custom-ss'),false); ?>




	
	<?php $custom_css_code = $this->get_option('custom_css_code'); ?>

	Custom CSS Code:<br />
	 <textarea rows="20" cols="50"  name="custom_css_code" id="custom_css_code"  ><?php echo stripslashes($custom_css_code); ?></textarea>
	 
	<script language="javascript">var editor = CodeMirror.fromTextArea(document.getElementById("custom_css_code"), { lineNumbers: true });</script>
	
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
			</p>



<?php $this->html_print_box_footer(true); ?>


	</div></div></div></div>

		</form>
	

</div>
<?php
	}





	function add_custom_css() {
		$custom_css_code = get_option('custom_css_code');
		if (!empty($custom_css_code)) {
			echo "<!-- Custom CSS Manager Plguin -->\n<style type=\"text/css\">\n".stripslashes($custom_css_code)."</style>\n<!-- Custom CSS Manager Plguin End -->";
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