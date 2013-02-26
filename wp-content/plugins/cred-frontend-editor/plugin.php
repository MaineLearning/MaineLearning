<?php 
/*
	Plugin Name: CRED Frontend Editor
	Plugin URI: 
	Description: Create Read Edit Delete (custom) posts from the front end using fully customizable forms 
	Version: 1.1.2
	Author: OnTheGoSystems	 
	Author URI: http://www.onthegosystems.com/
*/

// current version
define('CRED_FE_VERSION','1.1.2');
define('CRED_NAME','CRED');
define('CRED_CAPABILITY','manage_options');
define('CRED_FORMS_CUSTOM_POST_NAME','cred-form');

// include loader
require_once(dirname(__FILE__).'/loader.php');
CRED_Loader::init();

// define plugin name (path)
define('CRED_PLUGIN_NAME',CRED_PLUGIN_FOLDER.'/'.basename(__FILE__));
define('CRED_PLUGIN_BASENAME',plugin_basename( __FILE__ ));

// load basic classes
CRED_Loader::load('CLASS/CRED');
// init them
add_action( 'init', array('CRED_CRED', 'init'), 100 ); // late init in order to have all custom post types and taxonomies registered


// define php functions to serve as tags in template files, to display forms and links
function cred_route($path='')
{
    return home_url('index.php/'.CRED_NAME).$path;
}

// define php functions to serve as tags in template files, to display forms and links
function cred_ajax_route($action)
{
    return admin_url('admin-ajax.php').'?action='.$action;
}

function cred_delete_post_link($post_id=false, $text='', $action='', $class='', $style='', $return=false)
{
    $output=CRED_CRED::cred_delete_post_link($post_id, $text, $action, $class, $style);
    if ($return)
        return $output;
    echo $output;
}

function cred_edit_post_link($form, $post_id=false, $text='', $class='', $style='', $target='', $attributes='', $return=false)
{
    $output=CRED_CRED::cred_edit_post_link($form, $post_id, $text, $class, $style, $target, $attributes);
    if ($return)
        return $output;
    echo $output;
}

function cred_form($form, $post_id=false, $return=false)
{
    $output=CRED_CRED::cred_form($form, $post_id);
    if ($return)
        return $output;
    echo $output;
}

// function to be used in templates (eg for hiding comments)
function has_cred_form()
{
    return CRED_CRED::has_form();
}

/**
 * WPML translate call.
 * 
 * @param type $name
 * @param type $string
 * @return type 
 */
function cred_translate($name, $string, $context = 'CRED_CRED') {
    if (!function_exists('icl_t')) {
        return $string;
    }
    return icl_t($context, $name, stripslashes($string));
}

/**
 * Registers WPML translation string.
 * 
 * @param type $context
 * @param type $name
 * @param type $value 
 */
function cred_translate_register_string($context, $name, $value,
        $allow_empty_value = false) {
    if (function_exists('icl_register_string')) {
        icl_register_string($context, $name, stripslashes($value),
                $allow_empty_value);
    }
}
/*
    public API to import from XML string
*/
function cred_import_xml_from_string($xml) {
    CRED_Loader::load('CLASS/XML_Processor');
    $result = CRED_XML_Processor::importFromXMLString($xml);
    return $result;
}
/*
    public API to export to XML string
*/
function cred_export_to_xml_string($forms) {
    CRED_Loader::load('CLASS/XML_Processor');
    $xmlstring = CRED_XML_Processor::exportToXMLString($forms);
    return $xmlstring;
}

// auxilliary global functions
function cred_disable_shortcodes()
{
    global $shortcode_tags;
    
    $shortcode_back=$shortcode_tags;
    $shortcode_tags=array();
    return($shortcode_back);
}
function cred_re_enable_shortcodes($shortcode_back)
{
    global $shortcode_tags;
    
    $shortcode_tags=$shortcode_back;
}
function cred_disable_filters_for($hook)
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
function cred_re_enable_filters_for($hook,$back)
{
    global $wp_filter;
    $wp_filter[$hook]=$back;
}

// logging function
if (!function_exists('cred_log'))
{
if (defined('CRED_DEBUG')&&CRED_DEBUG)
{
    function cred_log($message, $file=null, $type=null, $level=1)
    {
        // debug levels
        $dlevels=array(
            'default' => defined('CRED_DEBUG') && CRED_DEBUG
        );

        // check if we need to log..
        if (!$dlevels['default']) return false;
        if ($type==null) $type='default';
        if (!isset($dlevels[$type]) || !$dlevels[$type]) return false;
        
        // full path to log file
        if ($file==null)
        {
            $file='debug.log';
        }
        $file=CRED_LOGS_PATH.DIRECTORY_SEPARATOR.$file;

        /* backtrace */
        $bTrace = debug_backtrace(); // assoc array

        /* Build the string containing the complete log line. */
        $line = PHP_EOL.sprintf('[%s, <%s>, (%d)]==> %s', 
                                date("Y/m/d h:i:s", mktime()),
                                basename($bTrace[0]['file']), 
                                $bTrace[0]['line'], 
                                print_r($message,true) );
        
        if ($level>1)
        {
            $i=0;
            $line.=PHP_EOL.sprintf('Call Stack : ');
            while (++$i<$level && isset($bTrace[$i]))
            {
                $line.=PHP_EOL.sprintf("\tfile: %s, function: %s, line: %d".PHP_EOL."\targs : %s", 
                                    isset($bTrace[$i]['file'])?basename($bTrace[$i]['file']):'(same as previous)', 
                                    isset($bTrace[$i]['function'])?$bTrace[$i]['function']:'(anonymous)', 
                                    isset($bTrace[$i]['line'])?$bTrace[$i]['line']:'UNKNOWN',
                                    print_r($bTrace[$i]['args'],true));
            }
            $line.=PHP_EOL.sprintf('End Call Stack').PHP_EOL;
        }
        // log to file
        file_put_contents($file,$line,FILE_APPEND);
        
        return true;
    }
}
else
{
    function cred_log()  { }
}
}
?>