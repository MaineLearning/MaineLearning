<?php
class WPRC_Plugins_API extends WPRC_Extensions_API
{
/**
  * Extension type of this class
  */ 
    private $extension_type = 'plugins';
  
    protected function extensionApiArgsQueryExtensions($args)
    {
        return parent::extensionApiArgsQueryExtensions($args, WPRC_PLUGINS_API_QUERY_PLUGINS_PER_PAGE); 
    }
    
/**
 * Form arguments for plugin_api function
 * 
 * @param mixed arguments array
 * @param string action
 */ 
    public function extensionsApiArgs($args, $action)
    {
        switch($action)
        {
            case 'query_plugins':
                $args = $this->extensionApiArgsQueryExtensions($args);
                if (!isset($args->repositories))
				{
					if(isset($_REQUEST['repos']) && is_array($_REQUEST['repos']))
					{
						$args->repositories = (array)$_REQUEST['repos'];
					}
					else
					{
						$rm = WPRC_Loader::getModel('repositories');
						$repos = $rm->getRepositoriesIds('enabled_repositories','plugins');
						$args->repositories = (array)$repos;
					}
				}
                break;
                
            case 'plugin_information':
                if (!isset($args->repositories))
				{
                    if(array_key_exists('repository_id', $_REQUEST) && !empty($_REQUEST['repository_id']))
                    {
                        $args->repositories = array($_REQUEST['repository_id']);
                    }
                    else
                    {
                        $rm = WPRC_Loader::getModel('repositories');
                        if ( isset( $_GET['plugin'] ) )
                            $repos=$rm->getRepositoryByExtension($_GET['plugin']);
                        
                        if (isset($repos) && $repos!==false && count($repos)>0)
                        {
                            $args->repositories = array($repos[0]['id']);						
                        }
                        else
                        {
                            $repo=$rm->getRepositoryByField('repository_endpoint_url',WPRC_WP_PLUGINS_REPO);
                            if (isset($repo) && $repo!==false)
                            {
                                $args->repositories = array($repo->id);						
                            }
                        }
                    }
				}
				//$args->slug=$_GET['plugin'];
                break;    
                
            case 'hot_tags':
                $rm = WPRC_Loader::getModel('repositories');                    
                $args->repositories = $rm->getRepositoriesIds('enabled_repositories','plugins');
                break;
        }

        $args = apply_filters('wprc_plugins_api_args_'.$action, $args);

        return $args;
    }

/**
 * Form structure of the plugins api results
 */    
    public function extensionsApiResult($api_results, $action, $args)
    {
        switch($action)
        {
            case 'query_plugins':          
                $result = $this->extensionApiResultsQueryExtensions($api_results, $this->extension_type);
            break;
        
            case 'plugin_information':
                $plugins_information = array_pop($api_results->results);
                
                $result = $plugins_information;                                
                break;
            
            case 'hot_tags':
                $input_tags = !empty($api_results->results) ? $api_results->results : array();

                if(count($input_tags) == 0)
                {
                    return new WP_Error('error', __('Hot Tags request returned empty result.', 'installer'));
                }
                
                $all_tags = array();
                $all_tags_count = array();
                foreach($input_tags AS $repository_url => $tags)
                {                  
                    if(!is_wp_error($tags))
                    {
                        foreach($tags AS $tag => $tag_attributes)
                        {
                            if(array_key_exists($tag, $all_tags_count))
                            {
                                if (isset($tag_attributes['count']))
								$all_tags_count[$tag] += $tag_attributes['count'];
                            }
                            else
                            {
                                if (isset($tag_attributes['count']))
                                $all_tags_count[$tag] = $tag_attributes['count'];
                            }
                        }
                        $all_tags = array_merge($all_tags, $tags);
                    }
                }

                if(count($all_tags) > 0)
                {                   
                    // set general number of a tags from the all repositories
                    foreach($all_tags AS $tag => $tag_attributes)
                    {
                        if (isset($all_tags_count[$tag]))
						$all_tags[$tag]['count'] = $all_tags_count[$tag];
						else
						$all_tags[$tag]['count'] = 0;
                    }
                }        
                       
                $result = $all_tags;                                
                break;
        }
        
        $result = apply_filters('wprc_plugins_api_results_'.$action, $result);
        
        return $result;
    }
  
    public function extensionsApi($state, $action, $args)
    {
        return parent::extensionsApi($state, $action, $args, $this->extension_type);
    }
    

/**
 * Display search results
 */     
    public function displaySearchResults()
    {        
        //global $tab, $paged;
        
        if ( ! current_user_can('install_plugins') )
        	wp_die(__('You do not have sufficient permissions to install plugins on this site.', 'installer'));
        
        if ( is_multisite() && ! is_network_admin() ) {
        	wp_redirect( network_admin_url( 'plugin-install.php' ) );
        	exit();
        }
        
        $wp_list_table = WPRC_Loader::getListTable('plugin-install');
        $pagenum = $wp_list_table->get_pagenum();
        $wp_list_table->prepare_items();
        
        $title = __('Install Plugins', 'installer');
        $parent_file = 'plugins.php';
        
        wp_enqueue_script( 'plugin-install' );
        if(isset($tab))
        {
            if ( 'plugin-information' != $tab )
            	add_thickbox();
                
            $body_id = $tab;
            
            do_action('install_plugins_pre_' . $tab); //Used to override the general interface, Eg, install or plugin information.
        }
        
        //get_current_screen()->add_help_tab( array(
        //'id'		=> 'overview',
        //'title'		=> __('Overview', 'installer'),
        //'content'	=>
        //	'<p>' . sprintf(__('Plugins hook into WordPress to extend its functionality with custom features. Plugins are developed independently from the core WordPress application by thousands of developers all over the world. All plugins in the official <a href="%s" target="_blank">WordPress.org Plugin Directory</a> are compatible with the license WordPress uses. You can find new plugins to install by searching or browsing the Directory right here in your own Plugins section.'), 'http:wordpress.org/extend/plugins/') . '</p>'
        //) );
        //get_current_screen()->add_help_tab( array(
        //'id'		=> 'adding-plugins',
        //'title'		=> __('Adding Plugins', 'installer'),
        //'content'	=>
        //	'<p>' . __('If you know what you&#8217;re looking for, Search is your best bet. The Search screen has options to search the WordPress.org Plugin Directory for a particular Term, Author, or Tag. You can also search the directory by selecting a popular tags. Tags in larger type mean more plugins have been labeled with that tag.', 'installer') . '</p>' .
        //	'<p>' . __('If you just want to get an idea of what&#8217;s available, you can browse Featured, Popular, Newest, and Recently Updated plugins by using the links in the upper left of the screen. These sections rotate regularly.', 'installer') . '</p>' .
        //	'<p>' . __('If you want to install a plugin that you&#8217;ve downloaded elsewhere, click the Upload in the upper left. You will be prompted to upload the .zip package, and once uploaded, you can activate the new plugin.', 'installer') . '</p>'
        //) );
        //
        //get_current_screen()->set_help_sidebar(
        //	'<p><strong>' . __('For more information:', 'installer') . '</strong></p>' .
        //	'<p>' . __('<a href="http:codex.wordpress.org/Plugins_Add_New_Screen" target="_blank">Documentation on Installing Plugins</a>', 'installer') . '</p>' .
        //	'<p>' . __('<a href="http:wordpress.org/support/" target="_blank">Support Forums</a>', 'installer') . '</p>'
        //);
        
        include(ABSPATH . 'wp-admin/admin-header.php');
        
        $this->renderAdditionalSearchUI();

        echo '<div class="wrap">';
        screen_icon(); 
        echo '<h2>'.esc_html( $title ).'</h2>';
        
        $wp_list_table->views();
        $wp_list_table->display(); 
                
        echo '<br class="clear" />';
        if(isset($tab) && isset($paged))
        {
            do_action('install_plugins_' . $tab, $paged);
        }
        echo '</div>';
        
        include(ABSPATH . 'wp-admin/admin-footer.php');
        exit;
    }
 
 /**
  * Render additional search UI
  */    
    public function renderAdditionalSearchUI()
    {
        parent::renderAdditionalSearchUI($this->extension_type);    
    } 
}
?>