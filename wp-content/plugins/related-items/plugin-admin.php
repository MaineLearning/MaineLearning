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
		$this->_settings_url = 'options-general.php?page=' . plugin_basename(__FILE__);

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
			add_action('admin_menu', array(&$this, 'admin_menu'));
                  
            add_filter('contextual_help', array(&$this,'admin_help'), 10, 3);      	
                 
                  
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
		global $related_items_admin_page;
		$related_items_admin_page = add_options_page('Related Items', 'Related Items', 'manage_options', __FILE__, array(&$this, 'optionsPage'));
	}
	


	public function admin_help($contextual_help, $screen_id, $screen){
	
		global $related_items_admin_page;
		
		if ($screen_id == $related_items_admin_page ) {
			
			$support_the_dev = $this->display_support_us();
			$screen->add_help_tab(array(
				'id' => 'developer-support',
				'title' => "Support the Developer",
				'content' => "<h2>Support the Developer</h2><p>".$support_the_dev."</p>"
			));
			
			
			$screen->add_help_tab(array(
				'id' => 'plugin-support',
				'title' => "Plugin Support",
				'content' => "<h2>Plugin Support</h2><p>For Plugin Support please visit <a href='http://mywebsiteadvisor.com/support/' target='_blank'>MyWebsiteAdvisor.com</a></p>"
			));


			$screen->set_help_sidebar("<p>Please Visit us online for more Free WordPress Plugins!</p><p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/' target='_blank'>MyWebsiteAdvisor.com</a></p><br>");
			
		}
		
	}		
	



	public function display_support_us(){
				
		$html = '<p><b>Thank You for using the Related Items Manager Plugin for WordPress!</b></p>';
		$html .= "<p>Please take a moment to <b>Support the Developer</b> by doing some of the following items:</p>";
		
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
		$html .= "<li><a href='$rate_url' target='_blank' title='Click Here to Rate and Review this Plugin on WordPress.org'>Click Here</a> to Rate and Review this Plugin on WordPress.org!</li>";
		
		$html .= "<li><a href='http://facebook.com/MyWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Facebook'>Click Here</a> to Follow MyWebsiteAdvisor on Facebook!</li>";
		$html .= "<li><a href='http://twitter.com/MWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Twitter'>Click Here</a> to Follow MyWebsiteAdvisor on Twitter!</li>";
		$html .= "<li><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/' target='_blank' title='Click Here to Purchase one of our Premium WordPress Plugins'>Click Here</a> to Purchase Premium WordPress Plugins!</li>";
	
	
		return $html;
	
	}











		
	public function display_social_media(){
	
			$social = '<style>
	
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
		}(document, "script", "facebook-jssdk"));</script>
		
		<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
		
		
		<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	
	
	</div>';
	
	return $social;
	

}		




	public function HtmlPrintBoxHeader($id, $title, $right = false) {
		
		?>
		<div id="<?php echo $id; ?>" class="postbox">
			<h3 class="hndle"><span><?php echo $title ?></span></h3>
			<div class="inside">
		<?php	
	}
	
	public function HtmlPrintBoxFooter( $right = false) {
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
<script type="text/javascript">var wpurl = "<?php get_option('siteurl'); ?>";</script>

<?php echo $this->display_social_media(); ?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Related Items Manager</h2>


	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
			
			
			<?php $this->HtmlPrintBoxHeader('pl_diag',__('Plugin Diagnostic Check','diagnostic'),true); ?>

				<?php
				
				echo "<p>Plugin Version: ".$this->version."</p>";
				
				echo "<p>Server OS: ".PHP_OS."</p>";
				
				echo "<p>Required PHP Version: 5.0+<br>";
				echo "Current PHP Version: " . phpversion() . "</p>";
			
							
				echo "<p>Memory Use: " . number_format(memory_get_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
				
				echo "<p>Peak Memory Use: " . number_format(memory_get_peak_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
				
				$lav = sys_getloadavg();
				echo "<p>Server Load Average: ".$lav[0].", ".$lav[1].", ".$lav[2]."</p>";
				
				?>
				
				<?php $this->HtmlPrintBoxFooter(true); ?>
				
				
				
				<?php $this->HtmlPrintBoxHeader('pl_resources',__('Plugin Resources','resources'),true); ?>
				
					<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/related-items-plugin/' target='_blank'>Plugin Homepage</a></p>
					<p><a href='http://mywebsiteadvisor.com/support/'  target='_blank'>Plugin Support</a></p>
					<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Contact Us</a></p>
					<p><a href='http://wordpress.org/support/view/plugin-reviews/related-items?rate=5#postform'  target='_blank'>Rate and Review This Plugin</a></p>
					
				<?php $this->HtmlPrintBoxFooter(true); ?>
				
				
				<?php $this->HtmlPrintBoxHeader('more_plugins',__('More Plugins','more_plugins'),true); ?>
					
					<p><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/'  target='_blank'>Premium WordPress Plugins!</a></p>
					<p><a href='http://profiles.wordpress.org/MyWebsiteAdvisor/'  target='_blank'>Free Plugins on Wordpress.org!</a></p>
					<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/'  target='_blank'>Free Plugins on Our Website!</a></p>	
								
				<?php $this->HtmlPrintBoxFooter(true); ?>
				
				
				<?php $this->HtmlPrintBoxHeader('follow',__('Follow MyWebsiteAdvisor','follow'),true); ?>
				
					<p><a href='http://facebook.com/MyWebsiteAdvisor/'  target='_blank'>Follow us on Facebook!</a></p>
					<p><a href='http://twitter.com/MWebsiteAdvisor/'  target='_blank'>Follow us on Twitter!</a></p>
					<p><a href='http://www.youtube.com/mywebsiteadvisor'  target='_blank'>Watch us on YouTube!</a></p>
					<p><a href='http://MyWebsiteAdvisor.com/'  target='_blank'>Visit our Website!</a></p>	
					
				<?php $this->HtmlPrintBoxFooter(true); ?>


			
			</div>
		</div>



		<div class="has-sidebar sm-padded" >			
			<div id="post-body-content" class="has-sidebar-content">
				<div class="meta-box-sortabless">
				
			<form method="post" action="">	
			
		
		<?php $this->HtmlPrintBoxHeader('related-items',__('Related Items Manager','related-items'),false); ?>	
		
			
			<p>Enable Relationships for the Following Post Types:</p>
			<?php
                                                    
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

		
		<?php $this->HtmlPrintBoxFooter(false); ?>
		
		
		
		<?php $this->HtmlPrintBoxHeader('related-items',__('Related Items Display','related-items'),false); ?>	
			
				<?php $ri_options = get_option('related_items_options'); ?>
				<?php $checked = isset($ri_options['automatic_after_content']) ? "checked='checked'" : ""; ?>
				<p><b>Related Items Automatic Display:  </b><br />  
				<input type="checkbox" name='related_items_options[automatic_after_content]' <?php echo $checked; ?> />   
				Automatic Display Enabled
				</p>
				
				<p><b>Related Items Shortcode:</b><br /> [related-items]</p>
			
				<p><b>Related Items Template Tag:</b><br />  echo do_shortcode('[related-items]');  </p>
								
				 <p class="submit">
					<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
				</p>
				
		<?php $this->HtmlPrintBoxFooter(false); ?>	
		
			
		</form>
		
 </div></div></div></div>
</div>
<?php
	}

}

                                                   
?>