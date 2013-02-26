<?php
if( !defined('ABSPATH') ) die('Security check');
if(!current_user_can('manage_options')) {
	die('Access Denied');
}


function installer_repositories_page_tabs( $current = 'default' ) {
     if ( isset ( $_GET['rtab'] ) )
          $current = $_GET['rtab'];
    
    $repo_model = WPRC_Loader::getModel('repositories');
    $not_delete_counts = count($repo_model -> getAllRepositoriesNotDeleted());
    $delete_counts = count($repo_model -> getAllRepositoriesDeleted());

     $tabs  = array(
          'default' => sprintf( __( 'Active repositories (%s)', 'installer' ), $not_delete_counts ),
          'trash' => sprintf( __( 'Inactive repositories (%s)', 'installer' ), $delete_counts )
     );

     $links = array();
     foreach( $tabs as $tab => $name ) :
          if ( $tab == $current ) :
               $links[] = '<a class="nav-tab nav-tab-active" href="?page=installer/pages/repositories.php&rtab=' . $tab . '">' . $name . '</a>';
          else :
               $links[] = '<a class="nav-tab" href="?page=installer/pages/repositories.php&rtab=' . $tab . '">' . $name . '</a>';
          endif;
     endforeach;
     
     echo '<div id="icon-repositories-installer" class="icon32"><br></div>';
     
     echo '<h2 class="nav-tab-wrapper">';
     foreach ( $links as $link )
          echo $link;
     echo '</h2>';
}


$result = '';
$result_msg = ''; 

if(array_key_exists('result',$_GET))
{
    $result = $_GET['result'];
    
    switch($result)
    {
        case 'success':
            $result_msg = __('Settings are saved', 'installer');
            break;
            
        case 'failure':
           // $result_msg = __('Settings are not saved. Change a values of the settings please', 'installer');
            $result_msg = __('Settings are saved', 'installer');
            break;
    }
}

// get settings 
$settings_model = WPRC_Loader::getModel('settings');
$settings = $settings_model->getSettings();

$wp_list_table = WPRC_Loader::getListTable('repositories');
     
//Handle bulk deletes
$doaction = $wp_list_table->current_action();

if($doaction)
{
    $repository_model = WPRC_Loader::getModel('repositories');
}

$url = admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/repositories.php';

if($doaction)
{
    switch($doaction)
    {
        case 'delete':
            if(!isset($_REQUEST['id']))
            {
                break;
            }
            
            $repository_id = (int) $_REQUEST['id'];
            $repository_model->softdeleteRepository($repository_id);
            break;
            
        case 'undelete':
            if(!isset($_REQUEST['id']))
            {
                break;
            }
            
            $repository_id = (int) $_REQUEST['id'];
            $repository_model->softundeleteRepository($repository_id);
            break;
        case 'edit':
            if(!isset($_REQUEST['id']))
            {
                break;
            }
            
            // set variables for the template
            $repository_id = (int) $_REQUEST['id'];
            
            $repository = $repository_model->getRepository($repository_id);
            
            $repository_types = $repository->extension_types;
            
            $types_model = WPRC_Loader::getModel('extension-types');
            $types = $types_model->getExtensionTypesList();

            if(count($types) > 0)
            {
                foreach($types AS $type_name => $type)
                {
                    if(!array_key_exists($type_name, $repository_types))
                    {
                        $types[$type_name]['type_enabled'] = 0;
                        continue;
                    }

                    if($repository_types[$type_name]['type_enabled'] == 1)
                    {
                        $types[$type_name]['type_enabled'] = 1; 
                    }
                    else
                    {
                        $types[$type_name]['type_enabled'] = 0; 
                    }
                } 
            }
            $json_types = json_encode($types);
           
            $template_mode = 'edit';
            include_once(WPRC_TEMPLATES_DIR.'/repository-view.tpl.php');
            WPRC_Loader::includeAdminBottom();
            
            exit;
            break;
            
         case 'add':
                     
            // set variables for the template
            $et_model = WPRC_Loader::getModel('extension-types');
            $types = $et_model->getExtensionTypesList();
            $json_types = json_encode($types);
           
            $template_mode = 'add';
            include_once(WPRC_TEMPLATES_DIR.'/repository-view.tpl.php');
            WPRC_Loader::includeAdminBottom();
  
            exit;
            break;
    }
    
}

$show_msg = '';
if(array_key_exists('warning', $_GET))
{
    $show_msg = $_GET['warning'];
}
$msg = '';
switch($show_msg)
{
    case 'https_not_provided':
        $msg = __('HTTPS is not provided by server. Please connect with your site administrator','installer');
        break;
}

$wp_list_table->prepare_items();

echo '<div class="wrap installer-repositories-list">';
echo '<h2>'.__('Repositories', 'installer').'<a class="add-new-h2" href="'.$url.'&action=add">'.__('Add New', 'installer').'</a></h2>';

installer_repositories_page_tabs();

if($msg<>'')
{
    echo '<div id="message" class="updated"><p>'.$msg.'</p></div>';
}
if($result_msg<>'')
{
    echo '<div id="message" class="updated"><p>'.$result_msg.'</p></div>';
}

$wp_list_table->views();

echo '<form id="list" action="" method="post">';


    
$wp_list_table->display();

echo '</form>';
echo '</div>';




?>