<?php
if( !defined('ABSPATH') ) die('Security check');
if(!current_user_can('manage_options')) {
	die('Access Denied');
}

// include needed files
WPRC_Loader::includeWPListTable();

$wp_list_table = WPRC_Loader::getListTable('deleted-repositories');
     
//Handle bulk deletes
$doaction = $wp_list_table->current_action();

if($doaction)
{
    $repository_model = WPRC_Loader::getModel('repositories');
}

$url = admin_url().'admin.php?page='.WPRC_PLUGIN_FOLDER.'/pages/deleted-repositories.php';

if ( $doaction && isset( $_REQUEST['checked'] )) 
{
    check_admin_referer( 'bulk-list-deleted-repositories' );

    if('undelete' == $doaction) 
    {
    	$bulklinks = (array) $_REQUEST['checked'];
    	foreach ( $bulklinks as $id ) 
        {
    		$repository_id = (int) $id;
            $repository_model->softundeleteRepository($repository_id);
        }
    }
}
elseif($doaction)
{
    switch($doaction)
    {
        case 'undelete':
            if(!isset($_REQUEST['id']))
            {
                break;
            }
            
            $repository_id = (int) $_REQUEST['id'];
            $repository_model->softundeleteRepository($repository_id);
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

echo '<div class="wrap">';
echo '<h2>'.__('Deleted Repositories', 'installer').'</h2>';
if($msg<>'')
{
    echo '<div id="message" class="updated"><p>'.$msg.'</p></div>';
}
echo '<form id="list" action="" method="post">';
    
$wp_list_table->display();
    
echo '</form>';
echo '</div>';

?>