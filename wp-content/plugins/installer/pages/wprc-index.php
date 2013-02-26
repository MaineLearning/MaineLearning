<?php     
if( !defined('ABSPATH') ) die('Security check');
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
?>
<div class="wrap" style="height:auto;">
<h1><?php echo __('Installer','installer'); ?></h1><br />
<h2><?php echo __('Settings','installer'); ?></h2>
<?php
if($result_msg<>'')
{
    echo '<div id="message" class="updated"><p>'.$result_msg.'</p></div>';
}
?>

<form action="<?php echo admin_url().'admin.php?wprc_c=settings&wprc_action=save';?>" method="post">
<label><input type="checkbox" value="1" name="settings[allow_compatibility_reporting]" <?php if($settings['allow_compatibility_reporting'] == 1) echo ' checked="checked"'; ?> /> <?php echo __('Enable sending compatibility reports. (Explicit confirmation will be prompted for each report)', 'installer'); ?></label>
<br /><br />
<input type="submit" value="<?php echo __('Save changes', 'installer'); ?>" class="button-primary" />
</form>
<?php
// <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*$model = WPRC_Loader::getModel('extensions');
$tree = $model->getDBExtensionsTree('themes');

WPRC_Loader::includeDebug();
WPRC_Debug::print_r($tree, __FILE__, __LINE__);*/

// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

/* -------------------------------------------
$extension_type = 'plugin';
$extension_name = 'test_plugin/test_plugin.php';

$edm = WPRC_Loader::getExtensionDataManager($extension_type,$extension_name);
//$edm->updateActivationDate();

$extension_name2 = 'testtest/testtest.php';
$edm2 = WPRC_Loader::getExtensionDataManager($extension_type,$extension_name2);
//$edm2->updateActivationDate();


WPRC_Loader::includeExtensionTimer();
//WPRC_ExtensionTimer::setTimers( array($extension_name, $extension_name2 ), 60);

echo 'Check timers: ';
$res = WPRC_ExtensionTimer::checkTimers( array($extension_name, $extension_name2) );

echo '<pre>'; print_r($res); echo '</pre>';
echo '<br>';

 ------------------------------- */
 
//WPRC_Loader::includeRepositoryClient();
//WPRC_Installer::onPluginActivation();

//$model = WPRC_Loader::getModel('extensions');
//echo 'CURRENT_THEME: '; echo $model->getFilteredCurrentTheme();
 
//-----------------------------------------------------------------------
// SITE INFO 

//WPRC_Installer::checkSiteId();
//--------------------------------------------------------------------------- PAGINATION ----------------------------------------------------------------
/*
$repository_total1 = 16;
$repository_total2 = 11;
$repository_total3 = 7;

$repositories = array(
    'repo1' => array('total_items' => $repository_total1, 'pages_number' => 0, 'pages' => array()),
    'repo2' => array('total_items' => $repository_total2, 'pages_number' => 0, 'pages' => array()),
    'repo3' => array('total_items' => $repository_total3, 'pages_number' => 0, 'pages' => array())
);

// -- now:
// page 1: 5 5 5
// page 2: 5 5 2
// page 3: 5 1 0
// page 4: 1 0 0

// -- need to implemented:
// page 1: 5 5 5
// page 2: 5 5 2
// page 3: 5 1 0
// page 4: 1 0 0
 
// test of calculating pages
//$total_items = 7;
//
//$pages_number = 3;
//$per_page = 5;


//-------------------------------------------------------------------------------------------------------------------------------------
WPRC_Loader::includeMultipleSourcesPaginator();

$per_page = 15;

// get total items number from all repositories
$total_items_number = 0;
$repositories_number = count($repositories);
$per_repository_pages = $per_page / $repositories_number;
$max_pages_number_arr = array();

// count general items number
foreach($repositories AS $repository_name => $repository_attr)
{    
    if(array_key_exists('total_items', $repository_attr))
    {
        $total_items_number += $repository_attr['total_items'];
    }
    
        // count max pages number
    if(array_key_exists('pages_number', $repository_attr))
    {
        $repository_pages_number = ceil($repository_attr['total_items'] / $per_repository_pages);
        
        $max_pages_number_arr[] = $repository_pages_number;
        $repositories[$repository_name]['pages_number'] = $repository_pages_number;
    }
}
$max_repositories_pages_number = max($max_pages_number_arr);

$total_page_number = ceil( $total_items_number / $per_page);
//$max_repositories_pages_number = $total_page_number;

// --------------------------------------- calculate pagination for each repository ------------------------------------------
foreach($repositories AS $repository_name => $repository_attr)
{         
   // echo '<br>REPOSITORY: '.$repository_name;
    try
    {
        $repositories_pages = WPRC_MultipleSourcesPaginator::calculateItemsPerPage($repository_attr['total_items'], $per_repository_pages, $max_repositories_pages_number);
    }
    catch(Exception $e)
    {
        if(WPRC_DEBUG)
        {
            echo 'Paginator error: '.$e->getMessage()."\n";
        }
    }
   
    $pages[$repository_name] = $repositories_pages;
    $repositories[$repository_name]['pages'] = $repositories_pages;
    
    // debug --------------------
  //  WPRC_Loader::includeDebug();
  //  WPRC_Debug::print_r($pages, __FILE__, __LINE__);
    // --------------------------
}

echo 'REPOSITORIES:';
WPRC_Loader::includeDebug();
WPRC_Debug::print_r($repositories, __FILE__, __LINE__);

// ------------------------------- build pagination matrix ------------------------------------------------------------
$pagination_matrix = array();
foreach($repositories AS $repository_name => $repository_attr)
{
//    if($repository_name<>'repo3')
//    {
//     //   continue;
//    }
    
   // echo 'repo: '.$repository_name.'<br>';
    for($page=1; $page <= count($repositories[$repository_name]['pages']); $page++ )
    {
        $pagination_matrix[$page][$repository_name] = $repositories[$repository_name]['pages'][$page];
    }
    
}
echo '<hr>pagination matrix:';
WPRC_Loader::includeDebug();
WPRC_Debug::print_r($pagination_matrix, __FILE__, __LINE__);
echo '<hr>';


// ------------------------ find problem pages -----------------------------------------------
for($page=1; $page <= $max_repositories_pages_number; $page++)
{
    echo '- PAGE#'.$page.'; '.array_sum($pagination_matrix[$page]).' <br>';

    if((array_sum($pagination_matrix[$page]) < $per_page) && ($page <> $max_repositories_pages_number))
    {
        echo 'PROBLEM with repositories: ';
        
        // find problem repositories
        foreach($pagination_matrix[$page] AS $repository_name => $items_on_page)
        {
            if(($items_on_page < $per_repository_pages) && ($items_on_page > 0))
            {
                $matrix_row = $pagination_matrix[$page];
                
                echo $repository_name.', ';
                
                $remained_items = $per_repository_pages - $matrix_row[$repository_name];
                echo '<br>remained_items = '.$remained_items.'<br>';
                
                
                unset($matrix_row[$repository_name]);
                // now matrix have values which need to be recalculated
                // there is a need to arrange $remained_items between the repositories in existing matrix
                
                
                echo 'Recalculate next repos:'; print_r($matrix_row); echo '<br>';
                
                echo '<br>Available items: <br>';
                // count available items for each repository
                //foreach($matrix_row AS $repo_name => $repo_page_number)
//                {
//                   echo $repositories[$repo_name]['total_items'].'<br>';
//                }
                
                // cycle on all next pages
                $available_items = array();
                for($j=($page+1); $j <= $max_repositories_pages_number; $j++)
                {
                    // cycle for each repository
                    foreach($matrix_row AS $repo_name => $repo_page_number)
                    {
                        $available_items[$repo_name] += $pagination_matrix[$j][$repo_name];
                        //echo $pages[$j][$repo_name]['total_items'].'<br>';
                    }
                    echo 'next_page #'.$j.' ';
                    
                   
                }
                 echo 'available items';
                 echo '<pre>'; print_r($available_items); echo '</pre>';
            }
        }
    }
    echo '<hr>';
}

echo '<hr><br>';
echo 'REPOSITORIES NUMBER: '.$repositories_number.'<br>';
echo 'TOTAL ITEMS NUMBER: '.$total_items_number.'<br>';
echo 'PERPAGE: '.$per_page.'<br>';
echo 'PER REPOSITORY PAGES: '.$per_repository_pages.'<br>';
echo 'TOTAL PAGES NUMBER: '.$total_page_number.'<br>'; 

// build pagination map


echo '<hr>';



$show_page = 1;
*/
?>
</div>