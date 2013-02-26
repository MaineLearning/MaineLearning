<?php
if( !defined('ABSPATH') ) die('Security check');
if(!current_user_can('manage_options')) {
	die('Access Denied');
}

global $wpdb;

// what we added to the db

// tables
$wprc_db_tables=array(
$wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES,
$wpdb->prefix.WPRC_DB_TABLE_EXTENSION_TYPES,
$wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS,
$wpdb->prefix.WPRC_DB_TABLE_EXTENSIONS,
$wpdb->prefix.WPRC_DB_TABLE_CACHED_REQUESTS
);
$wprc_db_tables=$wpdb->get_results("SHOW TABLES LIKE '".$wpdb->prefix."wprc_%'",ARRAY_N);

// option entries
$wprc_db_options=$wpdb->get_results('SELECT option_name FROM '.$wpdb->prefix.'options WHERE option_name LIKE "wprc_%"');
$wprc_db_options=array_merge($wprc_db_options,$wpdb->get_results('SELECT option_name FROM '.$wpdb->prefix.'options WHERE option_name LIKE "%transient_wprc_%"'));

$template_mode = 'wprc-uninstall-step-1';

if (isset($_REQUEST['wprc_uninstall_action']))
{
	switch ($_REQUEST['wprc_uninstall_action'])
	{
		case  __('UNINSTALL Installer', 'installer'):
			if (isset($_REQUEST['wprc_uninstall_yes']) && $_REQUEST['wprc_uninstall_yes']=='yes')
			{
                $nonce=$_REQUEST['_wpnonce'];
                if (! wp_verify_nonce($nonce, 'installer-uninstall-form') ) die("Security check");
				
                $plugin = WPRC_PLUGIN_BASENAME;
				deactivate_plugins( $plugin );
				
				$results_db_tables=array();
				foreach($wprc_db_tables as $table) {
					$result=$wpdb->query("DROP TABLE ".$table[0]);
					if ($result===false)
						$results_db_tables[$table[0]]=false;
					else
						$results_db_tables[$table[0]]=true;
				}
				$results_db_options=array();
				foreach($wprc_db_options as $option) {
					$result=$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name='".$option->option_name."'");
					if ($result===false)
						$results_db_options[$option->option_name]=false;
					else
						$results_db_options[$option->option_name]=true;
				}
				/*$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=repo_manager/wp-installer.php';
				if(function_exists('wp_nonce_url')) { 
					$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_repo_manager/wp-installer.php');
				}*/
				
				$template_mode = 'wprc-uninstall-step-2';
			}
			break;
	}
}
else
	$template_mode = 'wprc-uninstall-step-1';

include_once(WPRC_TEMPLATES_DIR.'/uninstall-view.tpl.php');
exit;
?>