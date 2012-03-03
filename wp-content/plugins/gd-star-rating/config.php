<?php

$gdsr_config_extra = dirname(dirname(__FILE__))."/gdsr-config.php";
if (file_exists($gdsr_config_extra)) require_once($gdsr_config_extra);

/**
 * Full path to wp-load file. Use only if the location of wp-content folder is changed.
 * 
 * example: define('STARRATING_WPLOAD', '/home/path/to/wp-load.php');
 */
if (!defined('STARRATING_WPLOAD')) define('STARRATING_WPLOAD', '');

/**
 * Should plugin load and use old format legacy functions.
 */
if (!defined('STARRATING_LEGACY_FUNCTIONS')) define('STARRATING_LEGACY_FUNCTIONS', true);

/**
 * Global control of debug. Set to false will prevent any kind of debug into file.
 */
if (!defined('STARRATING_DEBUG')) define('STARRATING_DEBUG', true);

/**
 * Activate debug version of JS code.
 */
if (!defined('STARRATING_JAVASCRIPT_DEBUG')) define('STARRATING_JAVASCRIPT_DEBUG', false);

/**
 * GD Star Rating is working in AJAX mode or not.
 */
if (!defined('STARRATING_AJAX')) define('STARRATING_AJAX', false);

/**
 * Full path to a text file used to save debug info. File must be writeable.
 */
if (!defined('STARRATING_LOG_PATH')) define('STARRATING_LOG_PATH', dirname(__FILE__).'/debug.txt');

/**
 * Name of the table for T2 templates without prefix. Don't change this!
 */
define('STARRATING_TPLT2_TABLE', 'gdsr_templates');

if (!function_exists('get_gdsr_wpload_path')) {
    /**
    * Returns the path to wp-config.php file
    * 
    * @return string wp-load.php path
    */
    function get_gdsr_wpload_path() {
        if (STARRATING_WPLOAD == '') {
            $d = 0;
            while (!file_exists(str_repeat('../', $d).'wp-load.php'))
                if (++$d > 16) exit;
            return str_repeat('../', $d).'wp-load.php';
        } else return STARRATING_WPLOAD;
    }
}

?>