<?php
define ( 'BP_CHAT_IS_INSTALLED', 1 );
define ( 'BP_CHAT_DB_VERSION', '21' );
if ( !defined( 'BP_CHAT_SLUG' ) )
	define ( 'BP_CHAT_SLUG', 'chat' );

if ( !defined( 'BP_CHAT_CONSTANT' ) )
    define ( 'BP_CHAT_CONSTANT', BP_CHAT_VERSION );

#ini_set('display_errors', 1); 
#error_reporting(E_ALL);

if ( file_exists( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/languages/' . get_locale() . '.mo' ) )
	load_textdomain( 'bp-chat', WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/languages/' . get_locale() . '.mo' );

require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-classes.php' );
require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-ajax.php' );
require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-cssjs.php' );
require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-templatetags.php' );
require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-notifications.php' );
require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-filters.php' );
require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-groups.php' );
require ( WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/bp-chat-admin.php' );

/**
 * bp_chat_install()
 *
 * Installs and/or upgrades the database tables for your component
 */
function bp_chat_install() {
	global $wpdb, $bp;
	
	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
	
    $sql[] = "CREATE TABLE ajax_chat_online (
	userID INT(11) NOT NULL,
	userName VARCHAR(64) NOT NULL,
	userRole INT(1) NOT NULL,
	channel INT(11) NOT NULL,
	dateTime DATETIME NOT NULL,
	ip VARBINARY(16) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	dbDelta($sql);

    $sql[] = "CREATE TABLE ajax_chat_messages (
	id INT(11) NOT NULL AUTO_INCREMENT,
	userID INT(11) NOT NULL,
	userName VARCHAR(64) NOT NULL,
	userRole INT(1) NOT NULL,
	channel INT(11) NOT NULL,
	dateTime DATETIME NOT NULL,
	ip VARBINARY(16) NOT NULL,
	text TEXT,
	PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	dbDelta($sql);

    $sql[] = "CREATE TABLE ajax_chat_bans (
	userID INT(11) NOT NULL,
	userName VARCHAR(64) NOT NULL,
	dateTime DATETIME NOT NULL,
	ip VARBINARY(16) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
	dbDelta($sql);

    $sql[] = "CREATE TABLE ajax_chat_invitations (
	userID INT(11) NOT NULL,
	channel INT(11) NOT NULL,
	dateTime DATETIME NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

	dbDelta($sql);
	
    update_site_option( 'bp-chat-db-version', BP_CHAT_DB_VERSION );
}
	
/**
 * bp_chat_setup_globals()
 *
 * Sets up global variables for your component.
 */
function bp_chat_setup_globals() {
	global $bp, $wpdb;

	
	$bp->chat->table_name = $wpdb->base_prefix . 'bp_chat';
	$bp->chat->image_base = WP_PLUGIN_URL . '/buddypress-ajax-chat/bp-chat/images';
	$bp->chat->format_activity_function = 'bp_chat_format_activity';
	$bp->chat->format_notification_function = 'bp_chat_format_notifications';
	$bp->chat->slug = BP_CHAT_SLUG;

	$bp->version_numbers->chat = BP_CHAT_VERSION;
}
add_action( 'plugins_loaded', 'bp_chat_setup_globals', 5 );	
add_action( 'admin_menu', 'bp_chat_setup_globals', 1 );
add_action( 'network_admin_menu', 'bp_chat_setup_globals', 1 );


/**
 * bp_chat_check_installed()
 *
 * Checks to see if the DB tables exist or if you are running an old version
 * of the component. If it matches, it will run the installation function.
 */
function bp_chat_check_installed() {	
    global $wpdb, $bp;

	if ( !$bp->loggedin_user->is_site_admin )
		return false;
	
	/***
	 * If you call your admin functionality here, it will only be loaded when the user is in the
	 * wp-admin area, not on every page load.
	 */

	/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
	if ( get_site_option('bp-chat-db-version') < BP_CHAT_DB_VERSION )
		bp_chat_install();

    create_config_file();
}
add_action( 'admin_menu', 'bp_chat_check_installed' );
add_action( 'network_admin_menu', 'bp_chat_check_installed' );

/**
 * bp_chat_setup_nav()
 *
 * Sets up the navigation items for the component. This adds the top level nav
 * item and all the sub level nav items to the navigation array. This is then
 * rendered in the template.
 */
function bp_chat_setup_nav() {
    global $bp,$current_blog,$group_object;

    #$chat_link = $bp->loggedin_user->domain . $bp->chat->slug . '/';
    $chat_link = get_bloginfo( 'wpurl' ) . $bp->chat->slug . '/';

    if (function_exists('anygig_orig_check_supporter')) 
        if ( false == anygig_orig_check_supporter('Chat', 'bp-chat', $bp->chat->slug, 80, $bp->chat->id, $chat_link))
	    return;	

	if (function_exists('bp_core_new_nav_item')) {
            bp_core_new_nav_item(array(
                'name'=> __( 'Chat', 'bp-chat' ),
                'slug'=> $bp->chat->slug,
                'screen_function'=>'bp_chat_screen_one',
                'default_subnav_slug'=>'chat',
                'user_has_access' => bp_is_home()
            ));
	} else {	
	    /* Add 'Chat' to the main navigation */
	    bp_core_add_nav_item( 
		__( 'Chat', 'bp-chat' ), /* The display name */ 
		$bp->chat->slug /* The slug */
	    );

	    /* Set a specific sub nav item as the default when the top level item is clicked */
	    bp_core_add_nav_default( 
		$bp->chat->slug, /* The slug of the parent nav item */ 
		'bp_chat_screen_one', /* The function to run when clicked */ 
		'chat-one' /* The slug of the sub nav item to make default */ 
	    );
    }

	if( class_exists('BP_Groups_Group') ) {

        if ( $group_id = BP_Groups_Group::group_exists($bp->current_action) ) {

            /* This is a single group page. */
            $bp->is_single_item = true;
            $bp->groups->current_group = &new BP_Groups_Group( $group_id );

        }	

        $groups_link = $bp->root_domain . '/' . $bp->groups->slug . '/' . $bp->groups->current_group->slug . '/';

        /* Add the subnav item only to the single group nav item*/
        if ( $bp->is_single_item )
        bp_core_new_subnav_item( array( 
            'name' => __( 'Chat', 'bp-chat' ), 
            'slug' => $bp->chat->slug, 
            'parent_url' => $groups_link, 
            'parent_slug' => $bp->groups->slug, 
            'screen_function' => 'bp_chat_screen_one', 
            'position' => 35, 
            'user_has_access' => $bp->groups->current_group->user_has_access,
            'item_css_id' => 'bp-chat' ) );

        do_action('bp_group_documents_nav_setup');
    }

    #print "<pre>";
    #print_r ($bp->groups);
    #print "</pre>";



	/* Only execute the following code if we are actually viewing this component (e.g. http://chat.org/chat) */
	if ( $bp->current_component == $bp->chat->slug ) {
		if ( bp_is_home() ) {
			/* If the user is viewing their own profile area set the title to "My Chat" */
			$bp->bp_options_title = __( 'My Chat', 'bp-chat' );
		} else {
			/* If the user is viewing someone elses profile area, set the title to "[user fullname]" */
			//$bp->bp_options_avatar = bp_core_get_avatar( $bp->displayed_user->id, 1 );
			$bp->bp_options_title = $bp->displayed_user->fullname;
		}
	}
}
add_action( 'wp', 'bp_chat_setup_nav', 2 );
add_action( 'admin_menu', 'bp_chat_setup_nav', 2 );
add_action( 'network_admin_menu', 'bp_chat_setup_nav', 2 );


/**
 * The following functions are "Screen" functions. This means that they will be run when their
 * corresponding navigation menu item is clicked, they should therefore pass through to a template
 * file to display output to the user.
 */

/**
 * bp_chat_screen_one()
 *
 * Sets up and displays the screen output for the sub nav item "chat/screen-one"
 */
function bp_chat_screen_one() {
    global $bp;

    
	
	/**
	 * There are three global variables that you should know about and you will 
	 * find yourself using often.
	 *
	 * $bp->current_component (string)
	 * This will tell you the current component the user is viewing.
	 *  
	 * Chat: If the user was on the page http://chat.org/members/andy/groups/my-groups
	 *          $bp->current_component would equal 'groups'.
	 *
	 * $bp->current_action (string)
	 * This will tell you the current action the user is carrying out within a component.
	 *  
	 * Chat: If the user was on the page: http://chat.org/members/andy/groups/leave/34
	 *          $bp->current_action would equal 'leave'.
	 *
	 * $bp->action_variables (array)
	 * This will tell you which action variables are set for a specific action
	 * 
	 * Chat: If the user was on the page: http://chat.org/members/andy/groups/join/34
	 *          $bp->action_variables would equal array( '34' );
	 */
	
	/* Add a do action here, so your component can be extended by others. */
	do_action( 'bp_chat_screen_one' );
	
	/** 
	 * Finally, load the template file. In this chat it would load:
	 *    "wp-content/bp-themes/[active-member-theme]/chat/screen-one.php"
	 *
	 * The filter gives theme designers the ability to override template names
	 * and define their own theme filenames and structure
	 */
	#bp_core_load_template( apply_filters( 'bp_chat_template_screen_one', 'chat/screen-one' ) );
	
	/* ---- OR ----- */
	 
	 /**
	  * To get content into the template file without editing it, we use actions.
	  * There are three actions in the template file, the first is for header text where you can
	  * place nav items if needed. The second is the page title, and the third is the body content
	  * of the page.
      */
	 add_action( 'bp_template_title', 'bp_chat_screen_one_title' );
	 add_action( 'bp_template_content', 'bp_chat_screen_one_content' );
		
     /* Finally load the plugin template file. */
     if ( strcmp(BP_VERSION, "1.1.3") <= 0 )
    {
        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'plugin-template' ) );
    } else {
         if ($bp->loggedin_user->id == $bp->displayed_user->id) 
        {
            bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
        } else if ($bp->groups->current_group->user_has_access)
        {
            bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'groups/single/plugins' ) );
        }
    }
}

	/***
	 * The second argument of each of the above add_action() calls is a function that will
	 * display the corresponding information. The functions are presented below:
	 */

	function bp_chat_screen_one_header() {
		#_e( 'Chat', 'bp-chat' );
	}

	function bp_chat_screen_one_title() {
		#_e( 'Chat', 'bp-chat' );
	}

	function bp_chat_screen_one_content() {
		global $bp, $current_user, $user_ID;
		global $user_level;

	# Roles section, should tie in to WP_ROLES and WP_USER, but for now just check if admin or not
	#
	$role = "user";
	if ($bp->loggedin_user->fullname == "admin")
	{
		$role = "admin";
	}

    $bp_chat_logout_url = urlencode(wp_logout_url( site_url() ));    

	$bp_chat_friends_url = wp_nonce_url( $bp->loggedin_user->domain . $bp->chat->slug . '?show_xml_friends=Yeah', 'bp_chat_show_xml_friends' );
	$bp_chat_channels_url = wp_nonce_url( $bp->loggedin_user->domain . $bp->chat->slug . '?show_xml_groups=Yeah', 'bp_chat_show_xml_channels' );
	$bp_chat_all_channels_url = wp_nonce_url( $bp->loggedin_user->domain . $bp->chat->slug . '?show_xml_all_groups=Yeah', 'bp_chat_show_all_xml_channels' );

	$user_fullname = bp_core_get_user_displayname( $bp->displayed_user->id, false );

	get_currentuserinfo();

        $bp_chat_username = "";
      	if ('' != $user_ID) {
         	$bp_chat_username = $current_user->user_nicename;
        }

        #print "<pre>";
        #print_r ($bp->groups);
        #print "</pre>";

	if ( is_user_logged_in() && (($bp->loggedin_user->id == $bp->displayed_user->id) || ($bp->groups->current_group->user_has_access && $bp->groups->current_group->is_user_member))) 
    {
        $forumName = bp_chat_get_channel_name();
        if ( is_user_logged_in() && (int)get_site_option( 'bp-chat-setting-popout-full-blown-chat' ) == 1 )
        {
            echo __('<p>Notice a popup window will try to appear.<br />  If you are using I.E. you might need to hold down the CTRL key when clicking on chat to allow the popup screen to appear.</p><p>If all else fails you can <a target=\'_new\' href="', 'bp-chat');
	    echo get_bloginfo('wpurl') . "/wp-content/plugins/buddypress-ajax-chat/bp-chat/chat/index.php?channel=$forumName" . '"'; 
	    echo __('> click here</a> for a new chat window.', 'bp-chat');

            echo "<script>window.open('" . get_bloginfo('wpurl') . "/wp-content/plugins/buddypress-ajax-chat/bp-chat/chat/index.php?channel=" . $forumName . "', 'BuddypressAjaxChat', 'menubar=no,resizable=no,status=no,toolbar=no,location=no,directories=no,height=600,width=700');</script>";
            
            return;
        }
		echo "<iframe id='chatFrame' src='" . get_bloginfo('wpurl') . "/wp-content/plugins/buddypress-ajax-chat/bp-chat/chat/index.php?channel=".$forumName."'  width='100%' height='600' frameborder='0' scrolling='no'></iframe>";
    } else {
        if ($bp->groups->current_group->user_has_access && !$bp->groups->current_group->is_user_member) 
        {
		    echo "<br /><br /><p>" . __("Only members can chat in this group.", "bp_chat") . "</p>";
        } else {
		    echo "<br /><br /><p>" . __("Only ", "bp_chat") . $bp_chat_username . __(" can look at ", "bp_chat") . $bp_chat_username . __("'s chat.", "bp_chat"). "</p>";
        }
	}
}

function bp_chat_get_channel_name()
{
    global $bp;
    
    $forumName = "Public";

    if ($bp->groups->current_group->user_has_access && $bp->groups->current_group->is_user_member) 
        $forumName = str_replace(" ","_",$bp->groups->current_group->name);
    if ($bp->loggedin_user->id == $bp->displayed_user->id)
        $forumName = "Public";

    return $forumName;
}

/**
 * bp_chat_load_buddypress()
 *
 * When we activate the component, we must make sure BuddyPress is loaded first (if active)
 * If it's not active, then the plugin should not be activated.
 */
function bp_chat_load_buddypress() {
	if ( function_exists( 'bp_core_setup_globals' ) )
		return true;
	
	/* Get the list of active sitewide plugins */
	$active_sitewide_plugins = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );
	if ( isset( $active_sidewide_plugins['buddypress/bp-loader.php'] ) && !function_exists( 'bp_core_setup_globals' ) ) {
		require_once( WP_PLUGIN_DIR . '/buddypress/bp-loader.php' );
		return true;
	}
	
	/* If we get to here, BuddyPress is not active, so we need to deactive the plugin and redirect. */
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( file_exists( ABSPATH . 'wp-admin/includes/mu.php' ) )
		require_once( ABSPATH . 'wp-admin/includes/mu.php' );

	deactivate_plugins( basename(__FILE__), true );
	if ( function_exists( 'deactivate_sitewide_plugin') )
		deactivate_sitewide_plugin( basename(__FILE__), true );

	wp_redirect( get_bloginfo('wpurl') . '/wp-admin/plugins.php' );
}
add_action( 'plugins_loaded', 'bp_chat_load_buddypress', 11 );

/***
 * Object Caching Support ----
 * 
 * It's a good idea to implement object caching support in your component if it is fairly database
 * intensive. This is not a requirement, but it will help ensure your component works better under
 * high load environments.
 *
 * In parts of this chat component you will see calls to wp_cache_get() often in template tags
 * or custom loops where database access is common. This is where cached data is being fetched instead
 * of querying the database.
 *
 * However, you will need to make sure the cache is cleared and updated when something changes. For chat,
 * the groups component caches groups details (such as description, name, news, number of members etc).
 * But when those details are updated by a group admin, we need to clear the group's cache so the new
 * details are shown when users view the group or find it in search results.
 *
 * We know that there is a do_action() call when the group details are updated called 'groups_settings_updated'
 * and the group_id is passed in that action. We need to create a function that will clear the cache for the
 * group, and then add an action that calls that function when the 'groups_settings_updated' is fired.
 *
 * Chat:
 *
 *   function groups_clear_group_object_cache( $group_id ) {
 *	     wp_cache_delete( 'groups_group_' . $group_id );
 *	 }
 *	 add_action( 'groups_settings_updated', 'groups_clear_group_object_cache' );
 *
 * The "'groups_group_' . $group_id" part refers to the unique identifier you gave the cached object in the
 * wp_cache_set() call in your code.
 *
 * If this has completely confused you, check the function documentation here:
 * http://codex.wordpress.org/Function_Reference/WP_Cache
 *
 * If you're still confused, check how it works in other BuddyPress components, or just don't use it,
 * but you should try to if you can (it makes a big difference). :)
 */

function addChatCss()
{
    if ( !is_user_logged_in() )
        return;

	echo '<link rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/buddypress-ajax-chat/bp-chat/css/shoutbox.css" type="text/css" media="screen" />';
	echo '<link rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/buddypress-ajax-chat/bp-chat/css/structure.css" type="text/css" media="screen" />';
    echo '<link rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/buddypress-ajax-chat/bp-chat/config/bp-chat-config-style.php" type="text/css" />';
}

function getChatContent() {

    // URL to the chat directory:
	if(!defined('AJAX_CHAT_URL')) {
		define('AJAX_CHAT_URL', get_bloginfo('wpurl') .'/wp-content/plugins/buddypress-ajax-chat/bp-chat/chat/');
	}
	
	// Path to the chat directory:
	if(!defined('AJAX_CHAT_PATH')) {
		define('AJAX_CHAT_PATH', realpath(dirname(__FILE__).'/buddypress-ajax-chat/bp-chat/chat/').'/');
	}

	global $bp, $current_user, $user_ID;

	// Validate the path to the chat:
	if(@is_file(AJAX_CHAT_PATH.'lib/classes.php')) {

		// Include custom libraries and initialization code:
		require(AJAX_CHAT_PATH.'lib/custom.php');

		// Include Class libraries:
		require_once(AJAX_CHAT_PATH.'lib/classes.php');
		
		// Initialize the chat:
		$ajaxChat = new CustomAJAXChat();
	}
	
	return null;
}

function setupChatCookies()
{
    global $bp, $current_user, $user_ID;

	# Roles section, should tie in to WP_ROLES and WP_USER, but for now just check if admin or not
	#
	$role = "user";
	if ($bp->loggedin_user->fullname == "admin")
	{
		$role = "admin";
	}

    $bp_chat_logout_url = urlencode(wp_logout_url( site_url() ));    

	get_currentuserinfo();

        $bp_chat_username = "";
      	if ('' != $user_ID) {
         	$bp_chat_username = $current_user->user_nicename;
	}

    $domain = (($_SERVER['SERVER_NAME'] != '127.0.0.1') && ($_SERVER['SERVER_NAME'] != 'localhost')) ? $_SERVER['SERVER_NAME'] : false;
    $path = (($_SERVER['SERVER_NAME'] != '127.0.0.1') && ($_SERVER['SERVER_NAME'] != 'localhost')) ? '/' : false;

	setcookie("loggedin_user_fullname", $bp_chat_username, time()+90000,$path, $domain);
	setcookie("loggedin_user_id", $bp->loggedin_user->id, time()+90000,$path, $domain);
	setcookie("loggedin_user_role", $role, time()+90000,$path, $domain);
	setcookie("xml_logout_url", $bp_chat_logout_url, time()+90000,$path, $domain);
}
add_action( 'plugins_loaded', 'setupChatCookies' );

function getShoutBoxContent() {
	global $bp, $current_user, $user_ID;
	// URL to the chat directory:
	if(!defined('AJAX_CHAT_URL')) {
		define('AJAX_CHAT_URL', get_bloginfo('wpurl') .'/wp-content/plugins/buddypress-ajax-chat/bp-chat/chat/');
	}
	
	// Path to the chat directory:
	if(!defined('AJAX_CHAT_PATH')) {
		define('AJAX_CHAT_PATH', realpath(dirname(__FILE__).'/buddypress-ajax-chat/bp-chat/chat').'/');
	}

	// Validate the path to the chat:
	if(@is_file(AJAX_CHAT_PATH.'lib/classes.php')) {

		// Include custom libraries and initialization code:
		require(AJAX_CHAT_PATH.'lib/custom.php');

		// Include Class libraries:
		require_once(AJAX_CHAT_PATH.'lib/classes.php');
		
		// Initialize the shoutbox:
		$ajaxChat = new CustomAJAXChatShoutBox();
		
		// Parse and return the shoutbox template content:
		return $ajaxChat->getShoutBoxContent();
	}
	
	return null;
}

function addChatShoutbox()
{
    global $bp;

    if ( !is_user_logged_in() )
        return;

	$current_url = site_url() . $_SERVER['REQUEST_URI'];
	$this_chat_url = $bp->loggedin_user->domain . $bp->chat->slug;

	if ((isset( $_REQUEST['close'] )) && ($_REQUEST['close'] == 'yes') )
	{
		#Enable showing chat for this session only
		update_usermeta( (int)$bp->loggedin_user->id, 'chat_hide', attribute_escape("true") );
	}

	if ((isset( $_REQUEST['close'] )) && ($_REQUEST['close'] == 'no') )
	{
		#Disable showing chat for this session only
		update_usermeta( (int)$bp->loggedin_user->id, 'chat_hide', attribute_escape("false") );
	}
	$chat_hide = "true";
	if ( get_usermeta( (int)$bp->loggedin_user->id, 'chat_hide') != "" )
	{
		$chat_hide = get_usermeta( (int)$bp->loggedin_user->id, 'chat_hide');
	}

	echo "<style>";
	echo "#shoutboxwrapper {";
	echo "  width:200px;";
	echo "  height:400px;";
	echo "  position:fixed;";
	echo "  right:0px;";
	echo "  bottom:0px;";
    echo "  z-index:3;";
    if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) != 0 ) {
        echo "display: none";	
    } else {
        if (is_user_logged_in() && ( strcmp($this_chat_url, $current_url) != 0) && (!isset($chat_hide) || ($chat_hide == "false")))
        {
            echo "display: true";	
        } else {
            if ( is_user_logged_in() && (int)get_site_option( 'bp-chat-setting-shoutbox-always-open' ) == 1 )
            {
                echo "display: true";	
            } else {
                echo "display: none";	
            }
        }
    }
    echo "}";
	
	echo "</style>";
	echo "<div id='shoutboxwrapper' class='jqDnR'><div id='moveme' class='jqHandle jqDrag'>" . __("Click and drag", "bp-chat") . "</div><div class='closemetop'><a id='shoutBoxMin' href='#' style='font-size:14px;text-decoration:none'>&#8744;</a><a id='shoutBoxMax' href='#' style='font-size:14px;text-decoration:none'>&#8743;</a><a style='font-size:14px;text-decoration:none' href='?close=yes' id='shoutboxclosetop' alt='Close'>x&nbsp;</a></div>";
	echo "<div id='shoutboxBody'>";
	echo "<iframe id='shoutBoxFrame' src='" . get_bloginfo('wpurl') . "/wp-content/plugins/buddypress-ajax-chat/bp-chat/chat/indexShoutBox.php'  width='100%' height='338' frameborder='0' scrolling='no'></iframe>";
	echo "</div>";
	echo "<div id='shoutboxBottom'>";
	echo "<div style='text-align:center;color:black'><a href='".$bp->loggedin_user->domain . $bp->chat->slug."'>". __("Go to full chat", "bp-chat") ."</a></div><div style='text-align:center;color:black'><a href='https://blueimp.net/ajax/'>". __("Ajax Chat", "bp-chat") . "</a>". __(" for ", "bp-chat") ."<a href='http://buddypress.org'>". __("Buddypress", "bp-chat") ."</a></div><br/></div>";
    echo "</div>";
    echo "<script>\n";
    echo "jQuery('#shoutBoxMin').click(function ()\n"; 
    echo "{\n"; 
    echo "  jQuery('div#shoutboxBody').hide('slow');\n";
?>
jQuery('#shoutboxwrapper').animate(
    {"bottom": "-=340px"}, 
    "slow"
);
<?php
    echo "});\n";
    echo "\n";
    echo "jQuery('#shoutBoxMax').click(function ()\n"; 
    echo "{\n"; 
    echo "  jQuery('div#shoutboxBody').show('slow');\n";
?>
jQuery('#shoutboxwrapper').animate(
    {"bottom": "+=340px"}, 
    "slow"
);
<?php
    echo "});\n";
    echo "\n";
    echo "</script>\n";
}

function bp_adminbar_chat_menu()
{
	if ( is_user_logged_in() ) {
		global $bp;
		
		echo '<li id="bp-adminbar-chat-menu"><a href="' . $bp->loggedin_user->domain . $bp->chat->slug . '">'. __("Chat", "bp-chat").'</a>';
		echo '<ul>';
		echo '  <li class="alt" id="bp-adminbar-chat-menu-shoutbox">;';
		echo '  </li>';
		echo '  <li id="bp-adminbar-chat-menu-chat"><a href="' . $bp->loggedin_user->domain . $bp->chat->slug . '">'. __("Chat", "bp-chat").'</a></li>';
		echo '</ul>';
		echo '</li>';
?>
		<script>
            jQuery(document).ready(function() {
                if (jQuery('#shoutboxwrapper').css('display') != 'none')
                {
                    jQuery('#bp-admin-chat').parent().append("<ul><?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) != 1 ) { ?><li class='alt'><a href='?close=yes'><?php _e("Shoutbox","bp-chat"); ?></a></li><?php } ?><li><a href='<?php echo $bp->loggedin_user->domain . $bp->chat->slug; ?>'><?php _e("Full Chat", "bp-chat"); ?></a></li></ul>");
                    <?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) != 1 ) { ?>
                        jQuery('#bp-adminbar-chat-menu-shoutbox').html("<li class='alt'><a href='?close=yes'><?php _e("Shoutbox","bp-chat"); ?></a></li>");
                    <?php } ?>
                } else {
                    jQuery('#bp-admin-chat').parent().append("<ul><?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) != 1 ) { ?><li class='alt'><a href='?close=no' ><?php _e("Shoutbox","bp-chat"); ?></a></li><?php } ?><li><a href='<?php echo $bp->loggedin_user->domain . $bp->chat->slug; ?>'><?php _e("Full Chat", "bp-chat"); ?></a></li></ul>");
                    <?php if ( (int)get_site_option( 'bp-chat-setting-disable-shoutbox-chat' ) != 1 ) { ?>
                        jQuery('#bp-adminbar-chat-menu-shoutbox').html("<li class='alt'><a href='?close=no'><?php _e("Shoutbox","bp-chat"); ?></a></li>");
                    <?php } ?>
                }
            });
        </script>
<?php
	}
}

function bp_close_shoutbox()
{
?>
<script>
jQuery(document).ready(function() {
 jQuery('#shoutboxwrapper').hide('slow');
});
</script>
<?php
}


add_action('admin_head', 'addChatCss', 101);
add_action('network_admin_head', 'addChatCss', 101);
add_action('bp_adminbar_menus', 'addChatCss', 1001);
add_action('bp_adminbar_menus', 'addChatShoutbox', 1002);
add_action( 'bp_adminbar_menus', 'bp_adminbar_chat_menu', 1000 );

function create_config_file()
{
    global $wpdb;

    #echo "<pre>";
    #print_r ($wpdb);
    #echo "</pre>";
    $myErrors = new WP_Error();
    
    $myFile = WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/config/bp-chat-config-style.php';
    if (!file_exists($myFile))
    {
        $fh = fopen($myFile, 'w') or wp_die(__("You do not have permission to create $myFile.  <br /><br /><strong>Go and chmod 777 " . WP_PLUGIN_DIR . "/buddypress-ajax-chat/bp-chat/config/</strong>", 'bp-chat'));
        
        $stringData = '<?php' . "\n";
        fwrite($fh, $stringData);

        $stringData = '// declare the output of the file as CSS' . "\n";
        fwrite($fh, $stringData);
        $stringData = "header('Content-type: text/css');" . "\n";
        fwrite($fh, $stringData);
        $stringData = '$custom_buddypress_ajax_chat_css = "' . get_bloginfo('stylesheet_directory') . '/buddypress-ajax-chat/_inc/buddypress-ajax-chat.css";' . "\n";
        fwrite($fh, $stringData);
        $stringData = '$custom_buddypress_ajax_chat_css_file = "' . STYLESHEETPATH . '/buddypress-ajax-chat/_inc/buddypress-ajax-chat.css";' . "\n";
        fwrite($fh, $stringData);
        $stringData = 'if (file_exists($custom_buddypress_ajax_chat_css_file))' . "\n";
        fwrite($fh, $stringData);
        $stringData = '    include ("' . STYLESHEETPATH . '/buddypress-ajax-chat/_inc/buddypress-ajax-chat.css' . '");' . "\n";
        fwrite($fh, $stringData);
        $stringData = '?>' . "\n";
        fwrite($fh, $stringData);

        fclose($fh);
    }

    $myFile = WP_PLUGIN_DIR . '/buddypress-ajax-chat/bp-chat/config/bp-chat-config.php';

    //Check if file exists then leave
    if (file_exists($myFile))
        return;
    $fh = fopen($myFile, 'w') or wp_die(__("You do not have permission to create $myFile.  <br /><br /><strong>Go and chmod 777 " . WP_PLUGIN_DIR . "/buddypress-ajax-chat/bp-chat/config/</strong>", 'bp-chat'));

    $stringData = '<?php' . "\n";
    fwrite($fh, $stringData);
    $stringData = '$bp_chat_config_user = \'' . DB_USER .'\';' . "\n";
    fwrite($fh, $stringData);

    $stringData = '$bp_chat_config_pass = \'' . DB_PASSWORD .'\';' . "\n";
    fwrite($fh, $stringData);

    if ( defined('WP_USE_MULTIPLE_DB') && WP_USE_MULTIPLE_DB )
    {
        $stringData = '$bp_chat_config_db = \'' . DB_NAME .'global\';' . "\n";
    } else {
        $stringData = '$bp_chat_config_db = \'' . DB_NAME .'\';' . "\n";
    }
    fwrite($fh, $stringData);

    $stringData = '$bp_chat_config_db_host = \'' . DB_HOST .'\';' . "\n";
    fwrite($fh, $stringData);

    $stringData = '$bp_chat_config_db_table_prefix = \'' . $wpdb->base_prefix .'\';' . "\n";
    fwrite($fh, $stringData);

    if ( WPLANG != '' )
    {
        $stringData = '$bp_chat_config_language = \'' . WPLANG .'\';' . "\n";
    } else {
        $stringData = '$bp_chat_config_language = \'' . "en" .'\';' . "\n";
    }
    fwrite($fh, $stringData);

    $stringData = '?>' . "\n";
    fwrite($fh, $stringData);

    fclose($fh);
}


?>
