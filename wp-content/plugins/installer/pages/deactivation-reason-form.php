<?php 
if( !defined('ABSPATH') ) die('Security check');
// prepare fields values for the form in the template
$site_id = WPRC_Installer::getSiteId();

// get lists of extensions
$em = WPRC_Loader::getModel('extensions');

$extension_keys = array();
$bulk_deactivate=0;
$bulk_data=array();

// get extenstions to deactivate
if(array_key_exists('checked', $_POST) /*&& $_POST['action2']=="deactivate-selected"*/)
{
    // bulk deactivation
    
    // bulk deactivation accessible for plugins only
    $extension_type = 'plugin';
    
    $extension_keys = $_POST['checked'];
	$bulk_deactivate=1;
	$bulk_data['_wpnonce']=$_POST['_wpnonce'];
	$bulk_data['_wp_http_referer']=$_POST['_wp_http_referer'];
	$bulk_data['checked']=$_POST['checked'];
	$bulk_data['action']=$_POST['action'];
	$bulk_data['action2']=$_POST['action2'];
	$url=admin_url('plugins.php');
}
else
{
    // single extension deactivation
    
    $url = $_SERVER['REQUEST_URI'];
    $extension = WPRC_UrlAnalyzer::getExtensionFromUrl($url);
    
    $extension_type = $extension['type'];

    $extension_path = $extension['name'];
    $all_plugins = get_plugins();
    $extension_name = ( isset( $all_plugins[ $extension_path ]['Name'] ) ) ? $all_plugins[ $extension_path ]['Name'] : '';

    
    switch($extension_type)
    {
        case 'plugin':
            if(array_key_exists('name', $extension))
            {
                // it is not a name, it's a extension_path (placed in $extension['name'])
                $extension_keys[] = $extension['name'];  
            }
            break;
            
        case 'theme':
			// send uninstall report for previous theme not current theme
			//$extension_keys[] = $em->getCurrentThemeName();
            $extension_keys[] = $em->getSwitchedThemeName();
            break;
    }
    
}

$plugins = $em->getPluginsWithoutSpecifiedPlugins($extension_keys); 
$themes = $em->getThemesWithoutCurrent();
$themes = $em->changeExtensionsKey($themes,'Name');

// set languages texts for current extension type
$language = array();
switch($extension_type)
{
    case 'plugin':
        $language['i_dont_need_what_this_extension_do'] = __('I don\'t need what this plugin is doing','installer');
        $language['i_dont_like_how_the_extension_performs_its_tasks'] = __('I don\'t like how the plugin performs its tasks','installer');
        $language['extension_is_not_working_right_on_my_site'] = __('The plugin is not working right on my site','installer');
        $language['describe_uninstalling_reason'] = __('Please tell us:', 'installer');//Can you describe the reasons why you are switching from the theme
        $language['report_and_uninstall'] = __('Report & Deactivate', 'installer');
        
        $show_themes_list = true;
        break;
        
    case 'theme':
        $language['i_dont_need_what_this_extension_do'] = __('I don\'t need what this theme is doing','installer');
        $language['i_dont_like_how_the_extension_performs_its_tasks'] = __('I don\'t like how the theme performs its tasks','installer');
        $language['extension_is_not_working_right_on_my_site'] = __('The theme is not working right on my site','installer');
        $language['describe_uninstalling_reason'] = __('Please tell us:', 'installer');
        $language['report_and_uninstall'] = __('Report & switch the theme', 'installer');
        
        $show_themes_list = false;
        break;
}

//print_r($_REQUEST);

// include template
require_once(WPRC_TEMPLATES_DIR.'/deactivation-reason-form.tpl.php');
?>