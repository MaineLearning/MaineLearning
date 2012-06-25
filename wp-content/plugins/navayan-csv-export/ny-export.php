<?php
/*
Plugin Name: Navayan CSV Export
Plugin URI: http://blog.navayan.com/
Description: Navayan CSV Export to export all your table data in CSV (Comma Separate Value) format. You can save an exported CSV file of any table in 'table_name_YYYYMMDD_HHMMSS.csv' format
Version: 1.0.6
Author: Amol Nirmala Waman
Author URI: http://www.navayan.com/
*/
include_once ('ny-csv-define.php'); 

/* ADD SETTINGS LINK */
function ny_csv_actions($links, $file){
	static $this_plugin;
	if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);
	if( $file == $this_plugin ){
		$settings_link = '<a href="tools.php?page">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links);
	}
	return $links;
}

/* ADD MENU TO TOOLS */
if(!function_exists('ny_csv_menu')){
    function ny_csv_menu() {
        if ( function_exists('add_submenu_page') ){
            add_management_page( __( NY_PLUGIN_NAME, NY_PLUGIN_SLUG), __( NY_PLUGIN_NAME, NY_PLUGIN_SLUG), 'manage_options', '', 'fn_ny_csv_ui');
						add_filter('plugin_action_links', 'ny_csv_actions', 10, 2);
        }
    }
}

/* GENERATE USER INTERFACE */
if(!function_exists('fn_ny_csv_ui')){
    function fn_ny_csv_ui(){
        if(file_exists(WP_PLUGIN_DIR.'/'.NY_PLUGIN_SLUG.'/ny-csv-ui.php')){
            include_once(WP_PLUGIN_DIR.'/'.NY_PLUGIN_SLUG.'/ny-csv-ui.php');
        }
    }
}

/* GET CSV DATA */
if(!function_exists('fn_ny_csv_export')){
    function fn_ny_csv_export(){        
        include_once(WP_PLUGIN_DIR . '/'.NY_PLUGIN_SLUG.'/ny-csv-generate.php');
        $req_table = isset($_REQUEST['nycsv']) ? $_REQUEST['nycsv'] : '';
        if ($req_table){          
            echo fn_ny_csv_gen($req_table);
            exit;
        }
    }
}
add_action('admin_menu', 'ny_csv_menu');
add_action('init', 'fn_ny_csv_export');
?>