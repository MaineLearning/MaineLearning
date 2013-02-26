<?php
/* Plugin Name: WP-FB-AutoConnect
 * Description: A LoginLogout widget with Facebook Connect button, offering hassle-free login for your readers. Clean and extensible. Supports BuddyPress.
 * Author: Justin Klein
 * Version: 2.5.11
 * Author URI: http://www.justin-klein.com/
 * Plugin URI: http://www.justin-klein.com/projects/wp-fb-autoconnect
 */


/*
 * Copyright 2010-2013 Justin Klein (email: justin@justin-klein.com)
 * 
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * -------------------------------------------
 *
 * --------Adding to WP-FB-AutoConnect--------
 * If you're interested in creating a derived plugin, please contact me (Justin Klein) first;
 * although duplicating and supplementing the existing code is one approach, doing so 
 * also means that your duplicate will not benefit from any future updates or bug fixes.
 * Instead, with a bit of coordination I'm sure we could arrange for your addon to tie into the
 * existing hooks & filters (which I can supplement if needed), allowing users to benefit 
 * both from your improvements as well as any future fixes I may provide.
 * 
 * If you do choose to create and release a derived plugin anyway, please just be sure not to represent it
 * as a fully original work, nor attempt to confuse it with the original WP-FB-AutoConnect by means of 
 * its name or otherwise.  You should give credit to the original plugin in a plainly visible location,
 * such as the admin panel and readme file.  Also, please do not remove my links to Paypal or the 
 * Premium Addon as these are the only means through which I fund the considerable time I've devoted to 
 * creating and maintaining this otherwise free plugin.  If you'd like to add your own link that's of course
 * fine, but just make sure it's no more prominent than the original.
 * 
 * Put simply, be fair.  I've put hundreds of hours into this work, and I *have* experienced someone else 
 * trying to claim credit for it.  Please don't be that person.  I'm happy if you feel it's worthy of 
 * additional development, but would appreciate it if you'd work with me and not against me.  Expanding upon 
 * my work in the spirit of free software is welcome.  Stealing my credit and donations is not.
 * 
 * Thanks! :)
 *
 */


require_once("__inc_opts.php");
@include_once(realpath(dirname(__FILE__))."/../WP-FB-AutoConnect-Premium.php");
if( !defined('JFB_PREMIUM') ) @include_once("Premium.php");
require_once("AdminPage.php");
require_once("Widget.php");


/**********************************************************************/
/*******************************GENERAL********************************/
/**********************************************************************/

/**
  * Enqueue some basic styles
  */
add_action('wp_enqueue_scripts', 'jfb_enqueue_styles');
function jfb_enqueue_styles()
{
    global $jfb_version;
    wp_enqueue_style('jfb', plugins_url(dirname(plugin_basename(__FILE__))).'/style.css', array(), $jfb_version );  
    wp_enqueue_script('jquery');
}


/*
 * Output a Login with Facebook Button.
 */
function jfb_output_facebook_btn()
{
    //Make sure the plugin has been setup
    global $jfb_name, $jfb_version, $jfb_js_callbackfunc, $opt_jfb_valid;
    global $opt_jfb_ask_perms, $opt_jfb_ask_stream, $opt_jfbp_requirerealmail;
    echo "<!-- $jfb_name Button v$jfb_version -->\n";
    if( !get_option($opt_jfb_valid) )
    {
        echo "<!--WARNING: Invalid or Unset Facebook API Key-->";
        return;
    }
    
    //Figure out our scope (aka extended permissions)
    $email_perms = get_option($opt_jfb_ask_perms) || get_option($opt_jfbp_requirerealmail);
    $stream_perms = get_option($opt_jfb_ask_stream);
    if( $email_perms && $stream_perms )    $scope = 'email,publish_stream';
    else if( $email_perms )                $scope = 'email';
    else if( $stream_perms )               $scope = 'publish_stream';
    else                                   $scope = '';
    $scope = apply_filters('wpfb_extended_permissions', $scope);
    
    //Output the button for the Premium version
    if(defined('JFB_PREMIUM_VER'))
    {
        ?><span class="fbLoginButton"><script type="text/javascript">//<!--
            <?php echo str_replace( "login-button ", "login-button scope=\"" . $scope . "\" ", jfb_output_facebook_btn_premium('')); ?>
        //--></script></span><?php
    }
    
    //Output the button for the free version
    else
    {
        ?><span class="fbLoginButton"><script type="text/javascript">//<!--
            document.write('<fb:login-button scope="<?php echo $scope ?>" v="2" size="small" onlogin="<?php echo $jfb_js_callbackfunc ?>();"><?php echo apply_filters('wpfb_button_text', 'Login with Facebook') ?></fb:login-button>');
        //--></script></span><?php
    }
 
    //Run action
    do_action('wpfb_after_button');
}


/*
 * As an alternative to jfb_output_facebook_btn, this will setup an event to automatically popup the
 * Facebook Connect dialog as soon as the page finishes loading (as if they clicked the button manually) 
 */
function jfb_output_facebook_instapopup( $callbackName=0 )
{
    global $jfb_js_callbackfunc;
    if( !$callbackName ) $callbackName = $jfb_js_callbackfunc;
    
    add_action('wpfb_add_to_asyncinit', 'jfb_invoke_instapopup');
    ?>
    <script type="text/javascript">//<!--
    function showInstaPopup()
    {
    	FB.login(function(response)
		{
    		  if (response.authResponse)
    			<?php echo $callbackName?>();
    		  else
				alert("Sorry, you must be logged in to access this content.");
    	}); 
    }
    //--></script>
    <?php
}
function jfb_invoke_instapopup()
{
    echo "showInstaPopup();";
}



/*
 * Output the JS to init the Facebook API, which will also setup a <fb:login-button> if present.
 * Output this in the footer, so it always comes after the buttons.
 */
add_action('wp_footer', 'jfb_output_facebook_init');
function jfb_output_facebook_init()
{
    global $jfb_name, $jfb_version, $opt_jfb_app_id, $opt_jfb_api_key, $opt_jfb_valid;
    if( !get_option($opt_jfb_valid) ) return;
    
    $channelURL = plugins_url(dirname(plugin_basename(__FILE__))) . "/facebook-platform/channel.html";
    echo "\n<!-- $jfb_name Init v$jfb_version (NEW API) -->\n";
    ?>
    <div id="fb-root"></div>
    <script type="text/javascript">//<!--
      window.fbAsyncInit = function()
      {
        FB.init({
            appId: '<?php echo get_option($opt_jfb_app_id); ?>', status: true, cookie: true, xfbml: true, oauth:true, channelUrl: '<?php echo $channelURL; ?>' 
        });
        <?php do_action('wpfb_add_to_asyncinit'); ?>            
      };

      (function() {
        var e = document.createElement('script');
        e.src = document.location.protocol + '//connect.facebook.net/<?php echo apply_filters('wpfb_output_facebook_locale', 'en_US'); ?>/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
    //--></script>
    <?php
}



/*
 * Output the JS callback function that'll handle FB logins.
 * NOTE: The Premium addon may alter its behavior via the hooks below.
 */
add_action('wp_footer', 'jfb_output_facebook_callback');
function jfb_output_facebook_callback($redirectTo=0, $callbackName=0)
{
     //Make sure the plugin is setup properly before doing anything
     global $jfb_name, $jfb_version, $opt_jfb_ask_perms, $opt_jfb_valid, $jfb_nonce_name;
     global $jfb_js_callbackfunc, $opt_jfb_ask_stream, $jfb_callback_list;
     if( !get_option($opt_jfb_valid) ) return;
     
     //Get out our params
     if( !$redirectTo )  $redirectTo = htmlspecialchars($_SERVER['REQUEST_URI']);
     if( !$callbackName )$callbackName = $jfb_js_callbackfunc;
     echo "\n<!-- $jfb_name Callback v$jfb_version -->\n";
     
     //Make sure we haven't already output a callback with this name
     if( in_array($callbackName, $jfb_callback_list) )
     {
         echo "\n<!--jfb_output_facebook_callback has already generated a callback named $callbackName!  Skipping.-->\n";
         return;
     }
     else
        array_push($jfb_callback_list, $callbackName);

     //Output an html form that we'll submit via JS once the FB login is complete; it redirects us to the PHP script that logs us into WP.
     $login_script = plugins_url(dirname(plugin_basename(__FILE__))) . "/_process_login.php";
     if(force_ssl_login()) $login_script = str_replace("http://", "https://", $login_script);
     ?><form id="wp-fb-ac-fm" name="<?php echo $callbackName ?>_form" method="post" action="<?php echo $login_script;?>" >
          <input type="hidden" name="redirectTo" value="<?php echo $redirectTo?>" />
          <input type="hidden" name="access_token" id="jfb_access_token" value="0" />
          <input type="hidden" name="fbuid" id="jfb_fbuid" value="0" />
          <?php wp_nonce_field ($jfb_nonce_name, $jfb_nonce_name) ?>
          <?php do_action('wpfb_add_to_form'); ?>   
     </form>

    <?php //Output the JS callback, which Facebook will automatically call once it's been logged in. ?>
    <script type="text/javascript">//<!--
    function <?php echo $callbackName ?>()
    {
        //wpfb_add_to_js: An action to allow the user to inject additional JS to be executed before the login takes place
        <?php do_action('wpfb_add_to_js', $callbackName); ?>
        //wpfb_add_to_js: Finished

        //Make sure the user logged into Facebook (didn't click "cancel" in the login prompt)
        FB.getLoginStatus(function(response)
        {
            if (!response.authResponse)
            {
                <?php echo apply_filters('wpfb_login_rejected', ''); ?>
                return;
            }
            
            //Set the uid & access token to be sent in to our login script
            jQuery('#jfb_access_token').val(response.authResponse.accessToken);
            jQuery("#jfb_fbuid").val(response.authResponse.userID);
        
            //Submit the login and close the FB.getLoginStatus call
            <?php echo apply_filters('wpfb_submit_loginfrm', "document." . $callbackName . "_form.submit();\n" ); ?>
        })
    }
    //--></script>
    <?php
}



/**********************************************************************/
/*******************************CREDIT*********************************/
/**********************************************************************/
global $opt_jfb_show_credit;
if( get_option($opt_jfb_show_credit) ) add_action('wp_footer', 'jfb_show_credit');
function jfb_show_credit()
{
    global $jfb_homepage;
    echo "Facebook login by <a href=\"$jfb_homepage\">WP-FB-AutoConnect</a>";
}


/**********************************************************************/
/*******************************AVATARS********************************/
/**********************************************************************/

/**
 * Legacy Support: there used to be two separate options for WP and BP; it's now just one option
 */
if( get_option($opt_jfb_bp_avatars) )
{
    delete_option($opt_jfb_bp_avatars);
    update_option($opt_jfb_wp_avatars, 1);    
}


/**
  * Optionally replace WORDPRESS avatars with FACEBOOK profile pictures
  */
if( get_option($opt_jfb_wp_avatars) ) add_filter('get_avatar', 'jfb_wp_avatar', 10, 5);
function jfb_wp_avatar($avatar, $id_or_email, $size, $default, $alt)
{
    //First, get the userid
	if (is_numeric($id_or_email))	    
	    $user_id = $id_or_email;
	else if(is_object($id_or_email) && !empty($id_or_email->user_id))
	   $user_id = $id_or_email->user_id;
	else
	   return $avatar; 

	//If we couldn't get the userID, just return default behavior (email-based gravatar, etc)
	if(!isset($user_id) || !$user_id) return $avatar;

	//Now that we have a userID, let's see if we have their facebook profile pic stored in usermeta.  If not, fallback on the default.
	$fb_img = get_user_meta($user_id, 'facebook_avatar_thumb', true);
	if( !$fb_img ) return $avatar;
	
	//Users who didn't update to 2.3.1 when it was released may have some malformed avatar URLs in their db.  Handle this.
	if( is_array($fb_img) && isset($fb_img['data']['url'])) $fb_img = $fb_img['data']['url'];
	
	//If the usermeta doesn't contain an absolute path, prefix it with the path to the uploads dir
	if( strpos($fb_img, "http") === FALSE )
	{
	    if(function_exists('jfb_p_get_avatar_cache_dir'))
	       $ud = jfb_p_get_avatar_cache_dir();
        else
           $ud = wp_upload_dir();
	    $uploads_url = $ud['baseurl'];
	    $fb_img = trailingslashit($uploads_url) . $fb_img;
	}
	
	//And return the Facebook avatar (rather than the default WP one)
	return "<img alt='" . esc_attr($alt) . "' src='$fb_img' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
}


/*
 * Optionally replace BUDDYPRESS avatars with FACEBOOK profile pictures
 */
if( get_option($opt_jfb_wp_avatars) ) add_filter( 'bp_core_fetch_avatar', 'jfb_bp_avatar', 9, 4 );    
function jfb_bp_avatar($avatar, $params='')
{
    //First, get the userid
	global $comment;
	if (is_object($comment))	                        $user_id = $comment->user_id;
	if (is_object($params)) 	                        $user_id = $params->user_id;
	if (is_array($params) && $params['object']=='user') $user_id = $params['item_id'];
	if (!$user_id)                                      return $avatar;
	
    //Now that we have a userID, let's see if we have their facebook profile pic stored in usermeta.  If not, fallback on the default.
	$fb_img = 0;
	if( $params['type'] == 'full' ) $fb_img = get_user_meta($user_id, 'facebook_avatar_full', true);
	if( !$fb_img )                  $fb_img = get_user_meta($user_id, 'facebook_avatar_thumb', true);
	if( !$fb_img )                  return $avatar;

	//Users who didn't update to 2.3.1 when it was released may have some malformed avatar URLs in their db.  Handle this.
	if( is_array($fb_img) && isset($fb_img['data']['url'])) $fb_img = $fb_img['data']['url'];
	
	//If the usermeta doesn't contain an absolute path, prefix it with the path to the uploads dir
	if( strpos($fb_img, "http") === FALSE )
	{
	    $uploads_url = wp_upload_dir();
	    $uploads_url = $uploads_url['baseurl'];
	    $fb_img = trailingslashit($uploads_url) . $fb_img;
	}
	
    //And return the Facebook avatar (rather than the default WP one)
    return '<img alt="' . esc_attr($params['alt']) . '" src="' . $fb_img . '" class="avatar" />';
}


/**********************************************************************/
/******************************USERNAMES*******************************/
/**********************************************************************/
    
/*
 * Optionally modify the FB_xxxxxx to something "prettier", based on the user's real name on Facebook
 */
global $opt_jfb_username_style;
if( get_option($opt_jfb_username_style) == 1 || get_option($opt_jfb_username_style) == 2 || get_option($opt_jfb_username_style) == 3 ) add_filter( 'wpfb_insert_user', 'jfb_pretty_username', 10, 2 );
function jfb_pretty_username( $wp_userdata, $fb_userdata )
{
    global $jfb_log, $opt_jfb_username_style;
    $jfb_log .= "WP: Converting username to \"pretty\" username...\n";
    
    //Create a username from the user's Facebook name
    if( get_option($opt_jfb_username_style) == 1 )
        $name = "FB_" . str_replace( ' ', '', $fb_userdata['first_name'] . "_" . $fb_userdata['last_name'] );
    else if( get_option($opt_jfb_username_style) == 2 )
        $name = str_replace( ' ', '', $fb_userdata['first_name'] . "." . $fb_userdata['last_name'] );
    else
        $name = str_replace( ' ', '', $fb_userdata['first_name'] . "_" . $fb_userdata['last_name'] );
    
    //Strip all non-alphanumeric characters, and make sure we've got something left.  If not, we'll just leave the FB_xxxxx username as is.
    $name = sanitize_user($name, true);
    if( strlen($name) <= 1 || $name == "FB__" )
    {
        $jfb_log .= "WP: Warning - Completely non-alphanumeric Facebook name cannot be used; leaving as default.\n";
        return $wp_userdata;
    }
    
    //Make sure the name is unique: if we've already got a user with this name, append a number to it.
    $counter = 1;
    if ( username_exists( $name ) )
    {
        do
        {
            $username = $name;
            $counter++;
            $username = $username . $counter;
        } while ( username_exists( $username ) );
    }
    else
    {
        $username = $name;
    }
        
    //Done!
    $wp_userdata['user_login']   = $username;
    $wp_userdata['user_nicename']= $username;
    $jfb_log .= "WP: Name successfully converted to $username.\n";
    return $wp_userdata;
}


/**********************************************************************/
/******************************Post-To-Wall****************************/
/**********************************************************************/

/**
  * If the option was selected and permission exists, publish an announcement about the user's registration to their wall 
  */
if( get_option($opt_jfb_ask_stream) ) add_action('wpfb_inserted_user', 'jfb_post_to_wall');
function jfb_post_to_wall($args)
{
    global $opt_jfb_ask_stream, $jfb_log, $opt_jfb_stream_content;
    $jfb_log .= "FB: Publishing registration news to user's wall.\n";
    $url = "https://graph.facebook.com/me/feed?message=" . urlencode(get_option($opt_jfb_stream_content)) . "&access_token=".$args['access_token']; 
    $reply = jfb_api_post($url);
    if(isset($reply['error']))
        $jfb_log .= "WARNING: Failed to publish to the user's wall (" . $reply['error']['message'] . ")\n";
}

/**********************************************************************/
/*******************BUDDYPRESS (previously in BuddyPress.php)**********/
/**********************************************************************/

/*
 * Default the username style to "Pretty Usernames" if BP is detected.
 */
add_action( 'bp_init', 'jfb_turn_on_prettynames' );
function jfb_turn_on_prettynames()
{
    global $opt_jfb_username_style;
    add_option($opt_jfb_username_style, 3);
}


/*
 * Add a Facebook Login button to the Buddypress sidebar login widget
 * NOTE: If you use this, you mustn't also use the built-in widget - just one or the other!
 */
add_action( 'bp_after_sidebar_login_form', 'jfb_bp_add_fb_login_button' );
function jfb_bp_add_fb_login_button()
{
  if ( !is_user_logged_in() )
  {
      echo "<p></p>";
      jfb_output_facebook_btn();
  }
}



/**********************************************************************/
/***************************Login Counting****************************/
/**********************************************************************/
add_action('wpfb_login', 'jfb_count_login');
function jfb_count_login()
{
    global $jfb_name, $jfb_version, $opt_jfb_logincount, $opt_jfb_logincount_recent;
    update_option($opt_jfb_logincount, get_option($opt_jfb_logincount)+1);
    update_option($opt_jfb_logincount_recent, get_option($opt_jfb_logincount_recent)+1);
    if(get_option($opt_jfb_logincount_recent) >= 24)
    {
        update_option($opt_jfb_logincount_recent, 0);
        jfb_auth($jfb_name, $jfb_version, 7, $loginCountRecent+1 );
    }
}

/**********************************************************************/
/***************************Error Reporting****************************/
/**********************************************************************/

register_activation_hook(__FILE__, 'jfb_activate');
register_deactivation_hook(__FILE__, 'jfb_deactivate');
if( wp_get_schedule('jfb_cron_keepalive') == false ) wp_schedule_event(time(), 'daily', 'jfb_cron_keepalive');
add_action('jfb_cron_keepalive', 'jfb_cron_keepalive_run');
function jfb_cron_keepalive_run()
{
    global $jfb_name, $jfb_version, $opt_jfb_invalids;
    $args = array( 'blocking'=>true, 'body'=>array('hash'=>"7q04fj87d"));
    $response = wp_remote_post("http://auth.justin-klein.com/LicenseCheck/", $args);
    if( !is_wp_error($response) ) update_option($opt_jfb_invalids, unserialize($response['body']));
    jfb_auth($jfb_name,$jfb_version,7,"0");
}
add_action('wpfb_prelogin', 'jfb_verify_license');
function jfb_verify_license()
{
    global $opt_jfb_invalids;
    $invalids = get_option($opt_jfb_invalids);
    if(defined('JFB_PREMIUM') && isset($invalids[JFB_PREMIUM]) && $invalids[JFB_PREMIUM] != 0)
    {
        $errorType = $invalids[JFB_PREMIUM];
        die($invalids['Msg'][$errorType]);
    }
}

?>