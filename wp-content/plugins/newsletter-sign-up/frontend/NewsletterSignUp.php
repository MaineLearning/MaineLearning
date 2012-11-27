<?php
if(!class_exists('NewsletterSignUp')) {
class NewsletterSignUp {
	
	private $options = array();
	private $no_of_forms = 0;
	private $showed_checkbox = FALSE;
	private static $instance;
    private $validation_errors = array();
	
	public function __construct()
	{
		$this->get_options();
		$this->add_hooks();	
	}
        
         private function get_options() {
            $this->options = get_option('nsu');
            $this->options['form'] = get_option('nsu_form');
            $this->options['mailinglist'] = get_option('nsu_mailinglist');
            $this->options['checkbox'] = get_option('nsu_checkbox');
        }
	
	/**
         * Registers the Newsletter Sign-Up Widget
         * @return type 
         */
	function register_widget()
	{
		return register_widget('NewsletterSignUpWidget');
	}
	
        /**
         * Factory method for NewsletterSignUp class. Only instantiate once.
         * @return NewsletterSignUp Instance of Newsletter Sign-Up class 
         */
	public static function getInstance() {
		if(!isset(self::$instance)) self::$instance = new NewsletterSignUp();
		
		return self::$instance;
	}

	public function enqueue_styles()
	{
		// Build stylesheet url --------------
		$stylesheet_opts = '?';

		// Load CSS to reset the checkbox' position?
		if(isset($this->options['checkbox']['css_reset']) && $this->options['checkbox']['css_reset'] == 1) {
			$stylesheet_opts .= 'checkbox_reset=1&';
		}

		// Load CSS to reset label and input fields for the sign-up form?
		if(isset($this->options['form']['load_form_css']) && $this->options['form']['load_form_css'] == 1) {
			$stylesheet_opts .= 'form_css=1&';
		}

		wp_enqueue_style('ns_checkbox_style', plugins_url("/frontend/css/newsletter-sign-up.php$stylesheet_opts", dirname(__FILE__)));
	}
	
	/**
	* Add all the various WP filters and actions
	*/
	function add_hooks()
	{
		// widget hooks
		add_action('widgets_init',array(&$this,'register_widget'));
		add_action('init',array(&$this,'check_for_form_submit'));
		
		// register the shortcode which can be used to output sign-up form
		add_shortcode('newsletter-sign-up-form',array(&$this,'form_shortcode'));
		add_shortcode('nsu-form',array(&$this,'form_shortcode'));
		
        $enqueue = false;

		// Load CSS to reset the checkbox' position?
		if(isset($this->options['checkbox']['css_reset']) && $this->options['checkbox']['css_reset'] == 1) {
            $enqueue = true;
		}
		// Load CSS to reset label and input fields for the sign-up form?
		if(isset($this->options['form']['load_form_css']) && $this->options['form']['load_form_css'] == 1) {
            $enqueue = true;
		}
		
        // Only enqueue stylesheet if asked to by user.
        if($enqueue) { add_action('wp_enqueue_scripts', array(&$this, 'enqueue_styles')); }
		
		// Add to comment form? If so, add necessary actions. Try to add automatically.
		if(isset($this->options['checkbox']['add_to_comment_form']) && $this->options['checkbox']['add_to_comment_form'] == 1) {
			add_action('thesis_hook_after_comment_box',array(&$this,'output_checkbox'),20);
			add_action('comment_form',array(&$this,'output_checkbox'),20);
			add_action('comment_approved_',array(&$this,'grab_email_from_comment'),10,1);
			add_action('comment_post', array(&$this,'grab_email_from_comment'), 50, 2);
		}
		
		// If add_to_reg_form is ticked, add corresponding actions
		if(isset($this->options['checkbox']['add_to_registration_form']) && $this->options['checkbox']['add_to_registration_form'] == 1) {
			add_action('register_form',array(&$this,'output_checkbox'),20);
			add_action('register_post',array(&$this,'grab_email_from_wp_signup'), 50);
		}
		
		// If add_to_bp_form is ticked, add BuddyPress actions
		if(isset($this->options['checkbox']['add_to_buddypress_form']) && $this->options['checkbox']['add_to_buddypress_form'] == 1) {
			add_action('bp_before_registration_submit_buttons',array(&$this,'output_checkbox'),20);
			add_action('bp_complete_signup',array(&$this,'grab_email_from_wp_signup'),20);
		}
		
		// If running a MultiSite, add to registration form and add actions.
		if(isset($this->options['checkbox']['add_to_multisite_form']) && $this->options['checkbox']['add_to_multisite_form'] == 1) {
			add_action('signup_extra_fields',array(&$this,'output_checkbox'),20);
			add_action('signup_blogform',array(&$this,'add_hidden_checkbox'),20);
			add_filter('add_signup_meta',array(&$this,'add_checkbox_to_usermeta'));
			add_action('wpmu_activate_blog',array(&$this,'grab_email_from_ms_blog_signup'),20,5);
			add_action('wpmu_activate_user',array(&$this,'grab_email_from_ms_user_signup'),20,3);
		}
	}
	
	/**
	* Check if ANY Newsletter Sign-Up form has been submitted. 
	*/
	function check_for_form_submit()
	{
        $opts = $this->options['form'];
        $errors = array();   
                
		if(isset($_POST['nsu_submit']))
		{
			$email = (isset($_POST['nsu_email'])) ? $_POST['nsu_email'] : '';
			$name = (isset($_POST['nsu_name'])) ? $_POST['nsu_name'] : '';
			
			if(isset($this->options['mailinglist']['subscribe_with_name']) && $this->options['mailinglist']['subscribe_with_name'] == 1 && isset($opts['name_required']) && $opts['name_required'] == 1 && empty($name)) {
				$errors['name-field'] = 'Please fill in the name field.';
			}
                        
            if(empty($email)) { 
                $errors['email-field'] = 'Please fill in the email address field.';
            } elseif(!is_email($email)) {
                $errors['email-field'] = 'Please enter a valid email address.';
			}
			
            $this->validation_errors = $errors;
                        
             if(count($this->validation_errors) == 0) {
             	$this->send_post_data($email,$name,'form');
             }
                            
		}
		return;
	}
	
	
	/**
	* Output the checkbox
	* Function can only run once.
	*/
	public function output_checkbox() 
	{ 	
        $opts = $this->options['checkbox'];

        // If using option to hide checkbox for subscribers and cookie is set, set instance variable showed_checkbox to true so checkbox won't show.
		if(isset($opts['cookie_hide']) && $opts['cookie_hide'] == 1 && isset($_COOKIE['ns_subscriber'])) $this->showed_checkbox = TRUE;
		
        // User could have rendered the checkbox by manually adding 'the hook 'ns_comment_checkbox()' to their comment form
        // If so, abandon function.
        if($this->showed_checkbox) return false;
	
		?>
		<p id="ns-checkbox">
			<input value="1" id="nsu_checkbox" type="checkbox" name="newsletter-signup-do" <?php if(isset($opts['precheck']) && $opts['precheck'] == 1) echo 'checked="checked" '; ?>/>
			<label for="nsu_checkbox">
				<?php if(!empty($opts['text'])) { echo $opts['text']; } else { echo "Sign me up for the newsletter!"; } ?>
			</label>
		</p>
		<?php 
		
		$this->showed_checkbox = true;
        return true;
	}
	
	/**
	* Adds a hidden checkbox to the second page of the MultiSite sign-up form (the blog sign-up form) containing the checkbox value of the previous screen
	*/
	function add_hidden_checkbox()
	{
		?>
		<input type="hidden" name="newsletter-signup-do" value="<?php echo (isset($_POST['newsletter-signup-do'])) ? 1 : 0; ?>" />
		<?php
	}
	
	/**
	* Save the value of the checkbox to MultiSite sign-ups table
	*/
	function add_checkbox_to_usermeta($meta)
	{
		$meta['newsletter-signup-do'] = (isset($_POST['newsletter-signup-do'])) ? 1 : 0;
		return $meta;
	}
	
	/**
	* Send the post data to the newsletter service, mimic form request
	*/
	function send_post_data($email, $name = '', $type = 'checkbox')
	{	
                $opts = $this->options['mailinglist'];
		// when not using api and no form action has been given, abandon.
		if(empty($opts['use_api']) && empty($opts['form_action'])) return;
		
		$post_data = array();
		
		/* Are we using API? */
		if(isset($opts['use_api']) && $opts['use_api'] == 1) {
			
			switch($opts['provider']) {
				
				/* Send data using the YMLP API */
				case 'ymlp':
					$request_uri = "http://www.ymlp.com/api/Contacts.Add?";
					$request_uri .= "Key=" . $opts['ymlp_api_key'];
					$request_uri .= "&Username=" . $opts['ymlp_username'];
					$request_uri .= "&Email=" . $email;
					$request_uri .= "&GroupID=" . $opts['ymlp_groupid'];
					$request_uri .= $this->add_additional_data(array('format' => 'query_string', 'api' => 'ymlp', 'email' => $email, 'name' => $name));
					$result = wp_remote_get($request_uri);

					if(isset($_POST['_nsu_debug']) || isset($_GET['_nsu_debug'])) {
						var_dump($result); die();
					}  

				break;
				
				/* Send data using the MailChimp API */
				case 'mailchimp':
					$request   = array(
					  'apikey' => $opts['mc_api_key'],
					  'id' => $opts['mc_list_id'],
					  'email_address' => $email,
					  'double_optin' => (isset($opts['mc_no_double_optin']) && $opts['mc_no_double_optin'] == 1) ? FALSE : TRUE,
					  'merge_vars' => array(
							'OPTIN_TIME' => date('Y-M-D H:i:s')
					  )
					);

					if(isset($opts['mc_use_groupings']) && $opts['mc_use_groupings'] == 1 && !empty($opts['mc_groupings_name'])) {
						$request['merge_vars']['GROUPINGS'] = array(
							array( 'name' => $opts['mc_groupings_name'], 'groups' => $opts['mc_groupings_groups'] )
						);
					}
					
					/* Subscribe with name? If so, add name to merge_vars array */
					if(isset($opts['subscribe_with_name']) && $opts['subscribe_with_name'] == 1) {
                        // Try to provide values for First and Lastname fields
                        // These can be overridden, of just ignored by mailchimp.
                        $request['merge_vars']['FNAME'] = substr($name, 0, strpos($name,' '));
                        $request['merge_vars']['LNAME'] = substr($name,strpos($name,' '));
						$request['merge_vars'][$opts['name_id']] = $name;
					}
					// Add any set additional data to merge_vars array
					$request['merge_vars'] = array_merge($request['merge_vars'], $this->add_additional_data(array('email' => $email, 'name' => $name)));
					
					$result = wp_remote_post(
						'http://'.substr($opts['mc_api_key'],-3).'.api.mailchimp.com/1.3/?output=php&method=listSubscribe', 
						array( 'body' => json_encode($request))
					);      

					if(isset($_POST['_nsu_debug']) || isset($_GET['_nsu_debug'])) {
						var_dump($result); die();
					}                             
					
				break;
			
			}
			
		} else {
		/* We are not using API, mimic a normal form request */
			
			$post_data = array(
				$opts['email_id'] => $email,
			);
		
			// Subscribe with name? Add to $post_data array.
			if(isset($opts['subscribe_with_name']) && $opts['subscribe_with_name'] == 1) $post_data[$opts['name_id']] = $name;
			
			// Add list specific data
			switch($opts['provider']) {
				
				case 'aweber':
					$post_data['listname'] = $opts['aweber_list_name'];
					$post_data['redirect'] = get_bloginfo('wpurl');
					$post_data['meta_message'] = '1';
					$post_data['meta_required'] = 'email';
				break;
				
				case 'phplist':
					$post_data['list['.$opts['phplist_list_id'].']'] = 'signup';
					$post_data['subscribe'] = "Subscribe";
					$post_data["htmlemail"] = "1"; 
					$post_data['emailconfirm'] = $email;
					$post_data['makeconfirmed']='0';
				break;
			
			}
			
			$post_data = array_merge($post_data, $this->add_additional_data(array_merge(array('email' => $email, 'name' => $name), $post_data)));

			$result = wp_remote_post($opts['form_action'],
				array( 'body' => $post_data ) 
			);	

			if(isset($_POST['_nsu_debug']) || isset($_GET['_nsu_debug'])) {
				var_dump($result); die();
			} 
			
		}
		
		// store a cookie, if preferred by site owner
		if(isset($opts['cookie_hide']) && $opts['cookie_hide'] == 1) @setcookie('ns_subscriber',TRUE,time() + 9999999);
                
        // Check if we should redirect to a given page
        if($type == 'form' && isset($this->options['form']['redirect_to']) && strlen($this->options['form']['redirect_to']) > 6) {
            wp_redirect( $this->options['form']['redirect_to']);
            exit;
        } elseif($type == 'checkbox' && isset($this->options['checkbox']['redirect_to']) && strlen($this->options['checkbox']['redirect_to']) > 6) {
            wp_redirect( $this->options['checkbox']['redirect_to']);
            exit;
        }
	
	}

	
	/** 
	* Returns array with additional data names as key, values as value. 
	* @param array $args, the normal form data (name, email, list variables)
	*/
	function add_additional_data($args = array())
	{
        $opts = $this->options['mailinglist'];
		$defaults = array(
			'format' => 'array',
			'api' => NULL
		);
		
		$args = wp_parse_args( $args, $defaults );

		if($args['format'] == 'query_string') {
		
			$add_data = "";
			if(isset($opts['extra_data']) && is_array($opts['extra_data'])) {
				foreach($opts['extra_data'] as $key => $value) {
					if($args['api'] == 'ymlp') $value['name'] = str_replace('YMP','Field', $value['name']);

					$value['value'] = str_replace("%%NAME%%", $args['name'], $value['value']);
					$value['value'] = str_replace("%%IP%%", $_SERVER['REMOTE_ADDR'], $value['value']);
					$add_data .= "&".$value['name']."=".$value['value'];
				}		
			}
			return $add_data;
		} 
		
		$add_data = array();
		if(isset($opts['extra_data']) && is_array($opts['extra_data'])) {
			foreach($opts['extra_data'] as $key => $value) {
				$value['value'] = str_replace("%%NAME%%", $args['name'], $value['value']);
				$value['value'] = str_replace("%%IP%%", $_SERVER['REMOTE_ADDR'], $value['value']);
				$add_data[$value['name']] = $value['value'];
			}		
		}

		return $add_data;
	}
	
	/**
	* Perform the sign-up for users that registered trough a MultiSite register form
	* This function differs because of the need to grab the emailadress from the user using get_userdata
	* @param int $user_id : the ID of the new user
	* @param string $password : the password, we don't actually use this
	* @param array $meta : the meta values that belong to this user, holds the value of our 'newsletter-sign-up' checkbox.
	*/
	function grab_email_from_ms_user_signup($user_id, $password = NULL,$meta = NULL){
		if(!isset($meta['newsletter-signup-do']) || $meta['newsletter-signup-do'] != 1) return;
		$user_info = get_userdata($user_id);
		
		$email = $user_info->user_email;
		$naam = $user_info->first_name;
		
		$this->send_post_data($email,$naam);
	}
	
	/**
	* Perform the sign-up for users that registered trough a MultiSite register form
	* This function differs because of the need to grab the emailadress from the user using get_userdata
    * @param int $blog_id The id of the new blow
	* @param int $user_id The ID of the new user
	* @param $a No idea, seriously.
    * @param $b No idea, seriously.
	* @param array $meta The meta values that belong to this user, holds the value of our 'newsletter-sign-up' checkbox.
	*/
	function grab_email_from_ms_blog_signup($blog_id, $user_id, $a, $b ,$meta){
		
		if(!isset($meta['newsletter-signup-do']) || $meta['newsletter-signup-do'] != 1) return;
		$user_info = get_userdata($user_id);
		
		$email = $user_info->user_email;
		$name = $user_info->first_name;
		
		$this->send_post_data($email,$name);
	}
	
	/**
	* Grab the emailadress (and name) from a regular WP or BuddyPress sign-up and then send this to mailinglist.
	*/
	function grab_email_from_wp_signup()
	{
		if($_POST['newsletter-signup-do'] != 1) return;
		
		if(isset($_POST['user_email'])) {
			
			// gather emailadress from user who WordPress registered
			$email = $_POST['user_email'];
			$name = $_POST['user_login'];
		
		} elseif(isset($_POST['signup_email'])) {
		
			// gather emailadress from user who BuddyPress registered
			$email = $_POST['signup_email'];
			$name = $_POST['signup_username'];

		} else { return; }
		
		$this->send_post_data($email,$name);
	}
	
	/**
	* Grab the emailadress and name from comment and then send it to mailinglist.
	* @param int $cid : the ID of the comment
	* @param object $comment : the comment object, optionally
	*/
	function grab_email_from_comment($cid,$comment = NULL)
	{
		if($_POST['newsletter-signup-do'] != 1) return;
		
		$cid = (int) $cid;
		
		// get comment data
		if(!is_object($comment)) $comment = get_comment($cid);

		// if spam, abandon function
		if($comment->comment_karma != 0) return;
		
		$email = $comment->comment_author_email;
		$name = $comment->comment_author;
		
		$this->send_post_data($email, $name);
	}
	
        /**
         * The NSU form shortcode function. Calls the output_form method
         * 
         * @param array $atts Not used
         * @param string $content Not used
         * @return string Form HTML-code 
         */
	function form_shortcode($atts = null,$content = null)
	{ 
		return $this->output_form(false);
	}
	
        /**
         * Generate the HTML for a form
         * @param boolean $echo Should HTML be echo'ed?
         * @return string The generated HTML 
         */
	public function output_form($echo = true)
	{
        $errors = $this->validation_errors;
		$opts = $this->options;
		$additional_fields = '';
		$output = '';
		
		$this->no_of_forms++;
		$formno = $this->no_of_forms;
		
		/* Set up form variables for API usage or normal form */
		if(isset($opts['mailinglist']['use_api']) && $opts['mailinglist']['use_api'] == 1) {
			
			/* Using API, send form request to ANY page */
			$form_action = "";
			$email_id = 'nsu_email';
			$name_id = 'nsu_name';
				
		} else {
				
			/* Using normal form request, set-up using configuration settings */
			$form_action = $opts['mailinglist']['form_action'];
			$email_id = $opts['mailinglist']['email_id'];
				
			if(isset($opts['mailinglist']['name_id'])) {
				$name_id = $opts['mailinglist']['name_id'];
			}
				
		}
			
		/* Set up additional fields */
		
		if(isset($opts['mailinglist']['extra_data']) && is_array($opts['mailinglist']['extra_data'])) :
			$additional_fields = '<div class="hidden">';
			foreach($opts['mailinglist']['extra_data'] as $ed) : 
				if($ed['value'] == '%%NAME%%') continue;
				$ed['value'] = str_replace("%%IP%%", $_SERVER['REMOTE_ADDR'], $ed['value']);
				$additional_fields .= "<input type=\"hidden\" name=\"{$ed['name']}\" value=\"{$ed['value']}\" />";
			endforeach; 
			$additional_fields .= "</div>";
		endif; 
		
		$email_label = (!empty($opts['form']['email_label'])) ? $opts['form']['email_label'] : 'E-mail:';
		$name_label = (!empty($opts['form']['name_label'])) ? $opts['form']['name_label'] : 'Name:';
                
        $email_value = (!empty($opts['form']['email_default_value'])) ? $opts['form']['email_default_value'] : '';
        $name_value = (!empty($opts['form']['name_default_value'])) ? $opts['form']['name_default_value'] : '';
                
		$submit_button = (!empty($opts['form']['submit_button'])) ? $opts['form']['submit_button'] : __('Sign-Up');
                
        $text_after_signup = (!empty($opts['form']['text_after_signup'])) ? $opts['form']['text_after_signup'] : 'Thanks for signing up to our newsletter. Please check your inbox to confirm your email address.';
		$text_after_signup = (isset($opts['form']['wpautop']) && $opts['form']['wpautop'] == 1) ? wpautop(wptexturize($text_after_signup)) : $text_after_signup;
                
                
		
		 if(!isset($_POST['nsu_submit']) || count($errors) > 0) { //form has not been submitted yet 
  
			$output .= "<form class=\"nsu-form\" id=\"nsu-form-$formno\" action=\"$form_action\" method=\"post\">";	
			if(isset($opts['mailinglist']['subscribe_with_name']) && $opts['mailinglist']['subscribe_with_name'] == 1) {	
				$output .= "<p><label for=\"nsu-name-$formno\">$name_label</label><input class=\"nsu-field\" id=\"nsu-name-$formno\" type=\"text\" name=\"$name_id\" value=\"$name_value\" ";
				if($name_value) $output .= "onblur=\"if(!this.value) this.value = '$name_value';\" onfocus=\"if(this.value == '$name_value') this.value=''\" ";
                $output .= "/>";
                if(isset($errors['name-field'])) $output .= '<span class="nsu-error error notice">'.$errors['name-field'].'</span>';
                $output .= "</p>";		
			} 
							
			$output .= "<p><label for=\"nsu-email-$formno\">$email_label</label><input class=\"nsu-field\" id=\"nsu-email-$formno\" type=\"text\" name=\"$email_id\" value=\"$email_value\" ";
            if($email_value) $output .= "onblur=\"if(!this.value) this.value = '$email_value';\" onfocus=\"if(this.value == '$email_value') this.value = ''\" ";
            $output .= "/>";
            if(isset($errors['email-field'])) $output .= '<span class="nsu-error error notice">'.$errors['email-field'].'</span>';
            $output .= "</p>";
			$output .= $additional_fields;
			$output .= "<p><input type=\"submit\" id=\"nsu-submit-$formno\" class=\"nsu-submit\" name=\"nsu_submit\" value=\"$submit_button\" /></p>";
			$output .= "</form>";
				
		} else { // form has been submitted
		
			$output = "<p id=\"nsu-signed-up-$formno\" class=\"nsu-signed-up\">$text_after_signup</p>";		
				
		 }
		 
		 if($echo) {
			echo $output;
		 } 
		
        return $output;
		 
	}
}
}