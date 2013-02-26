<?php
define('CRED_PLUGIN_PATH', dirname(__FILE__));
define('CRED_PLUGIN_FOLDER', basename(CRED_PLUGIN_PATH));
define('CRED_PLUGIN_URL',plugins_url().'/'.CRED_PLUGIN_FOLDER);
define('CRED_ASSETS_URL',CRED_PLUGIN_URL.'/assets');
define('CRED_ASSETS_PATH',CRED_PLUGIN_PATH.'/assets');
define('CRED_INI_PATH',CRED_PLUGIN_PATH.'/ini');
define('CRED_LOCALE_PATH',CRED_PLUGIN_FOLDER.'/ini/locale');
define('CRED_VIEWS_PATH',CRED_PLUGIN_PATH.'/views');
define('CRED_VIEWS_PATH2',CRED_PLUGIN_FOLDER.'/views');
define('CRED_TEMPLATES_PATH',CRED_PLUGIN_PATH.'/views/templates');
define('CRED_TABLES_PATH',CRED_PLUGIN_PATH.'/views/tables');
define('CRED_CLASSES_PATH',CRED_PLUGIN_PATH.'/classes');
define('CRED_CONTROLLERS_PATH',CRED_PLUGIN_PATH.'/controllers');
define('CRED_MODELS_PATH',CRED_PLUGIN_PATH.'/models');
define('CRED_LOGS_PATH',CRED_PLUGIN_PATH.'/logs');
define('CRED_THIRDPARTY_PATH',CRED_PLUGIN_PATH.'/third-party');

// for module manager cred support
define('_CRED_MODULE_MANAGER_KEY_','cred');

//define('CRED_DEBUG',true);
//define('CRED_DEV',true);
?>