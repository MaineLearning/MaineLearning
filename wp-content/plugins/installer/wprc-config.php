<?php
define('WPRC_PLUGIN_PATH', dirname(__FILE__));
define('WPRC_PLUGIN_FOLDER', basename(WPRC_PLUGIN_PATH));
define('WPRC_LOCALE_FOLDER',WPRC_PLUGIN_FOLDER.'/locale');


if(file_exists(WPRC_PLUGIN_PATH.'/wprc-sandbox-config.php'))
{
    require_once(WPRC_PLUGIN_PATH.'/wprc-sandbox-config.php');
}

define('WPRC_PLUGIN_URL',plugins_url().'/'.WPRC_PLUGIN_FOLDER);
define('WPRC_ASSETS_URL',WPRC_PLUGIN_URL.'/assets');
define('WPRC_ASSETS_DIR',WPRC_PLUGIN_PATH.'/assets');
define('WPRC_PAGES_DIR',WPRC_PLUGIN_PATH.'/pages');
define('WPRC_PAGES_DIR2',WPRC_PLUGIN_FOLDER.'/pages');
define('WPRC_TEMPLATES_DIR',WPRC_PLUGIN_PATH.'/pages/templates');
define('WPRC_CLASSES_DIR',WPRC_PLUGIN_PATH.'/classes');
define('WPRC_TABLES_DIR',WPRC_PLUGIN_PATH.'/tables');
define('WPRC_MODELS_DIR',WPRC_PLUGIN_PATH.'/models');
define('WPRC_LOGS_DIR',WPRC_PLUGIN_PATH.'/logs');

define('WPRC_PLUGINS_API_QUERY_PLUGINS_PER_PAGE',30); // the number of pages from each repository
define('WPRC_THEMES_API_QUERY_THEMES_PER_PAGE',12); // the number of pages from each repository

// --- correct work reports --- 
// when this period will be reached the correct work report will be sent
define('WPRC_TIMER_DEFAULT_PERIOD',604800); //604800; //30 one week by default


// jquery 
define('WPRC_JQUERY_UI_THEME', 'smoothness');

define('WPRC_SKIP_BULK_DEACTIVATION_REPORT',true);

// link to the compatibility server
define('WPRC_SERVER_URL','http://wp-compatibility.com/wp-content/plugins/wp-repository-server/wprs-entry.php?wprs_c=server&wprs_action=execute');

// database
define('WPRC_DB_TABLE_REPOSITORIES', 'wprc_repositories');
define('WPRC_DB_TABLE_EXTENSION_TYPES', 'wprc_extension_types');
define('WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS', 'wprc_repositories_relationships');
define('WPRC_DB_TABLE_EXTENSIONS', 'wprc_extensions');
define('WPRC_DB_TABLE_CACHED_REQUESTS', 'wprc_cached_requests');

define('WPRC_WP_PLUGINS_REPO','http://api.wordpress.org/plugins/info/1.0/');
define('WPRC_WP_THEMES_REPO','http://api.wordpress.org/themes/info/1.0/');
define('WPRC_WP_PLUGINS_SITE','http://wordpress.org/extend/plugins/');
define('WPRC_WP_THEMES_SITE','http://wordpress.org/extend/themes/');
define('WPRC_WP_PLUGINS_UPDATE_REPO','http://api.wordpress.org/plugins/update-check/1.0/');
define('WPRC_WP_THEMES_UPDATE_REPO','http://api.wordpress.org/themes/update-check/1.0/');
define('WPRC_UPDATE_REPOS_URL',WPRC_SERVER_URL);
// period between repository updates from compatibility server
define('WPRC_REPO_UPDATE_PERIOD',172800); // 2 days

// javascript timeout for ajax requests, note: that this is not very reliable it may timeout but still the request be processed
define('WPRC_JS_TIMEOUT',50000); // 50 secs, should be quite long to cover for potential delays

// WPRC_DEBUG enables debug globally (this should be enabled for every other debug to function)
//define('WPRC_DEBUG',true);
//define('WPRC_DEBUG_JS',true);
//define('WPRC_DEBUG_SERVER_REQUESTS',true);
//define('WPRC_DEBUG_API_REQUESTS',true);
//define('WPRC_DEBUG_CONTROLLERS',true);

?>