<?php 
//return;
/*
	Plugin Name: Installer
	Plugin URI: http://wp-compatibility.com/installer-plugin/
	Description: Need help buying, installing and upgrading commercial themes and plugins? **Installer** handles all this for you, right from the WordPress admin. Installer lets you find themes and plugins from different sources, then, buy them from within the WordPress admin. Instead of manually uploading and unpacking, you'll see those themes and plugins available, just like any other plugin you're getting from WordPress.org.
	Version: 0.5
	Author: OnTheGoSystems Inc.	 
	Author URI: http://www.onthegosystems.com/
*/

define('WPRC_VERSION','0.5');
// <<<<<<<<<<<< includes -------------------------------------------------
require_once(dirname(__FILE__).'/wprc-config.php');
require_once(WPRC_PLUGIN_PATH.'/wprc-loader.php'); 

// define plugin name (path)
define('WPRC_PLUGIN_NAME',WPRC_PLUGIN_FOLDER.'/'.basename(__FILE__));
define('WPRC_PLUGIN_BASENAME',plugin_basename( __FILE__ ));

define('WPRC_DB_VERSION', '0.4.1');

WPRC_Loader::includeRepositoryClient();
WPRC_Loader::includeAdminNotifier();
//WPRC_Loader::includeSecurity();
WPRC_Loader::includePluginFile('wprc-functions.php');



$wprc_rc = 'WPRC_Installer';
$wprc_an = 'WPRC_AdminNotifier';
// >>>>>>>>>>>> -----------------------------------------------------------      

// <<<<<<<<<<<< functions -------------------------------------------------
/**
 * Initialize plugin enviroment
 */ 
add_action('init', array($wprc_rc, 'init'));
add_action('init', array($wprc_an, 'init'));

/**
* Print language texts for javascript
*/ 
add_action('wp_print_scripts', array($wprc_rc, 'printJsLanguage'));

/**
* Add plugin menu items
*/ 
add_action('admin_menu', array($wprc_rc, 'addMenuItems'));

/**
 * Add handlers on extension deactivation event (uninstall reports)
 */ 
add_action('deactivate_plugin', array($wprc_rc, 'onExtensionDeactivation'));
add_action('switch_theme', array($wprc_rc, 'onExtensionDeactivation'));


// -------------------------------------------------------------------------
// Correct work reports
/**
 *  Init correct-work-reporter
 */ 
add_action('activate_plugin', array($wprc_rc, 'onPluginActivation'));
add_action('deactivate_plugin', array($wprc_rc, 'onPluginDeactivation'));
add_action('switch_theme', array($wprc_rc, 'onSwitchTheme'));


/**
 * Send reports if it is necessary
 */ 
add_action('init', array($wprc_rc, 'on_view_wp_page'));
// -------------------------------------------------------------------------

/**
 * On Plugin activation
 */ 
register_activation_hook(__FILE__, array($wprc_rc, 'onActivation'));
register_deactivation_hook(__FILE__, 'wprc_unschedule_cron');

//------------------------
// repositories
$wprc_plugins_api = WPRC_Loader::getExtensionsApi('plugins');

add_action('install_plugins_pre_search', array($wprc_plugins_api, 'displaySearchResults'), 1);

// rendering of additional search UI
add_action('install_plugins_dashboard', array($wprc_plugins_api, 'renderAdditionalSearchUI'));
add_action('install_plugins_search', array($wprc_plugins_api, 'renderAdditionalSearchUI'));

// themes ----------------------
$wprc_themes_api = WPRC_Loader::getExtensionsApi('themes');

add_action('install_themes_pre_search', array($wprc_themes_api, 'displaySearchResults'), 1);

// rendering of additional search UI
add_action('install_themes_dashboard', array($wprc_themes_api, 'renderAdditionalSearchUI'));
add_action('install_themes_search', array($wprc_themes_api, 'renderAdditionalSearchUI'));

/**
 * On plugin install from the search results page
 * 
 * Add plugin to the extensions table
 */ 
//add_filter('wprc_plugins_api_args_plugin_information','add_repository_url');
add_filter('wprc_plugins_api_results_plugin_information',array($wprc_rc, 'addPlugin'));
add_filter('install_plugin_complete_actions', array($wprc_rc, 'updatePluginExtensionPath'));


/**
 * On theme install from the search results page
 * 
 * It fixes the theme install links (addThemesApiArgs)
 */ 
add_action('wprc_themes_api_args_theme_information', array($wprc_rc, 'addThemesApiArgs'));
add_action('wprc_themes_api_results_theme_information', array($wprc_rc, 'updateThemeName'));
add_filter('install_theme_complete_actions', array($wprc_rc, 'updateThemeExtensionPath'),20,4);


// check compatibility links
add_filter('theme_action_links', array($wprc_rc, 'addThemeCompatibilityLink'),1 , 2);
add_filter('plugin_action_links', array($wprc_rc, 'addPluginCompatibilityLink'), 1, 4);

/**
 * API request caching
 */
add_filter('wprc_extensions_api_before_each_repository', array($wprc_rc, 'getCachedApiRequest'), 1, 3);
add_filter('wprc_extensions_api_after_each_repository', array($wprc_rc, 'cacheApiRequest'), 1, 4);
// >>>>>>>>>>>> -----------------------------------------------------------

add_action('wprc_schedule_update_plugins', array($wprc_rc,'wprc_update_plugins'));
add_action('wprc_schedule_update_themes', array($wprc_rc,'wprc_update_themes'));


add_action( 'plugins_loaded', array( $wprc_rc, 'check_db') );

/*function wprc_schedule_update_plugins()
{
    WPRC_Installer::wprc_update_plugins();
}
function wprc_schedule_update_themes()
{
	WPRC_Installer::wprc_update_themes();
}*/

function wprc_reschedule_update_checks() {
    if ( wp_next_scheduled('wp_update_plugins') && !defined('WP_INSTALLING') )
		wp_clear_scheduled_hook( 'wp_update_plugins' );		
	if ( !wp_next_scheduled('wprc_schedule_update_plugins') && !defined('WP_INSTALLING') )
		wp_schedule_event(time(), 'twicedaily', 'wprc_schedule_update_plugins');

	if ( wp_next_scheduled('wp_update_themes') && !defined('WP_INSTALLING') )
		wp_clear_scheduled_hook( 'wp_update_themes' );		
	if ( !wp_next_scheduled('wprc_schedule_update_themes') && !defined('WP_INSTALLING') )
		wp_schedule_event(time(), 'twicedaily', 'wprc_schedule_update_themes');
}

function wprc_unschedule_cron() {
    wp_clear_scheduled_hook('wprc_schedule_update_plugins');
	wp_clear_scheduled_hook('wprc_schedule_update_themes');
}

if (!( ( ! is_main_site() && ! is_network_admin() ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ))
{
// remove default wp updaters for plugins and themes
remove_action( 'load-plugins.php', 'wp_update_plugins' );
remove_action( 'load-update.php', 'wp_update_plugins' );
remove_action( 'load-update-core.php', 'wp_update_plugins' );
remove_action( 'admin_init', '_maybe_update_plugins' );
remove_action( 'wp_update_plugins', 'wp_update_plugins' );

remove_action( 'load-themes.php', 'wp_update_themes' );
remove_action( 'load-update.php', 'wp_update_themes' );
remove_action( 'load-update-core.php', 'wp_update_themes' );
remove_action( 'admin_init', '_maybe_update_themes' );
remove_action( 'wp_update_themes', 'wp_update_themes' );

// add our own update handlers for themes and plugins
add_action( 'load-plugins.php', array($wprc_rc,'wprc_update_plugins') );
add_action( 'load-update.php', array($wprc_rc,'wprc_update_plugins') );
add_action( 'load-update-core.php', array($wprc_rc,'wprc_update_plugins') );
add_action( 'admin_init', array($wprc_rc,'wprc_maybe_update_plugins') );
add_action( 'wp_update_plugins', array($wprc_rc,'wprc_update_plugins') );

add_action( 'load-themes.php', array($wprc_rc,'wprc_update_themes') );
add_action( 'load-update.php', array($wprc_rc,'wprc_update_themes') );
add_action( 'load-update-core.php', array($wprc_rc,'wprc_update_themes') );
add_action( 'admin_init', array($wprc_rc,'wprc_maybe_update_themes') );
add_action( 'wp_update_themes', array($wprc_rc,'wprc_update_themes') );

add_action('init', 'wprc_reschedule_update_checks');

}
//add_filter('upgrader_pre_install',array($wprc_rc,'wprc_upgrade_pre_install'),100,2);
//add_filter('upgrader_post_install',array($wprc_rc,'wprc_upgrade_post_install'),100,3);

// repositories regular update
add_action('admin_init', array($wprc_rc,'wprc_maybe_check_server') );

function wprc_disable_filters_for($hook)
{
    global $wp_filter;
    if (isset($wp_filter[$hook]))
	{
		$wp_filter_back=$wp_filter[$hook];
		$wp_filter[$hook]=array();
	}
	else
		$wp_filter_back=array();
    return($wp_filter_back);
}
function wprc_re_enable_filters_for($hook,$back)
{
    global $wp_filter;
    $wp_filter[$hook]=$back;
}

function wprc_filter_uber_alles()
{
global $wprc_plugins_api, $wprc_themes_api,$wprc_rc;

wprc_disable_filters_for('plugins_api_args');
wprc_disable_filters_for('plugins_api');
wprc_disable_filters_for('plugins_api_result');

wprc_disable_filters_for('themes_api_args');
wprc_disable_filters_for('themes_api');
wprc_disable_filters_for('themes_api_result');

/*
// remove views upgrade filter
remove_filter('pre_set_site_transient_update_plugins', 'check_for_views_plugin_updates');
*/
// remove wpml upgrade filter
remove_filter('pre_set_site_transient_update_plugins', 'check_for_WPML_plugin_updates');

// remove all pre transient update filters ??
//wprc_disable_filters_for('pre_set_site_transient_update_plugins');
//wprc_disable_filters_for('pre_set_site_transient_update_themes');

add_filter('plugins_api_args', array($wprc_plugins_api, 'extensionsApiArgs'),1, 2);
add_filter('plugins_api', array($wprc_plugins_api, 'extensionsApi'), 1, 3);
add_filter('plugins_api_result', array($wprc_plugins_api, 'extensionsApiResult'), 1, 3);

add_filter('themes_api_args', array($wprc_themes_api, 'extensionsApiArgs'),1, 2);
add_filter('themes_api', array($wprc_themes_api, 'extensionsApi'), 1, 3);
add_filter('themes_api_result', array($wprc_themes_api, 'extensionsApiResult'), 1, 3);

// for details and update info on our repositories
remove_action('install_plugins_pre_plugin-information', 'install_plugin_information');
add_action('install_plugins_pre_plugin-information', array($wprc_rc,'wprc_install_plugin_information'));
remove_action('install_themes_pre_theme-information', 'install_theme_information');
add_action('install_themes_pre_theme-information', array($wprc_rc,'wprc_install_theme_information'));


//remove_action('wp_ajax_fetch-list','wp_ajax_fetch_list');
//add_action('wp_ajax_fetch-list','wprc_ajax_fetch_list');

WPRC_Loader::includeExtensionsApi();
add_action('wp_ajax_clear-extension-search-cache', array( 'WPRC_Extensions_API', 'clear_extension_search_cache' ) );



}

remove_action( 'admin_init', 'wp_plugin_update_rows' );
add_action( 'admin_init', array($wprc_rc,'wprc_plugin_update_rows'),9000 );
remove_action( 'admin_init', 'wp_theme_update_rows' );
add_action( 'admin_init', array($wprc_rc,'wprc_theme_update_rows'),9000 );

// our filters only
add_action('init', 'wprc_filter_uber_alles',90000);

/*
    To use in Types etc..
*/
function wprc_is_logged_to_repo($repo_url)
{
    return WPRC_Installer::isLoggedToRepo($repo_url);
}
?>