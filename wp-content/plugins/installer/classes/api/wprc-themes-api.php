<?php
class WPRC_Themes_API extends WPRC_Extensions_API
{
/**
 * Extension type of this class
 */ 
    private $extension_type = 'themes';
    
/**
 * Form arguments for extensions_api function
 * 
 * @param mixed arguments array
 * @param string action
 */ 
    public function extensionsApiArgs($args, $action)
    {
        switch($action)
        {
            case 'query_themes':            
                $args = $this->extensionApiArgsQueryExtensions($args, WPRC_THEMES_API_QUERY_THEMES_PER_PAGE);
                if (!isset($args->repositories))
				{
					if(isset($_REQUEST['repos']) && is_array($_REQUEST['repos']))
					{
						$args->repositories = (array)$_REQUEST['repos'];
					}
					else
					{
						$rm = WPRC_Loader::getModel('repositories');
						$repos = $rm->getRepositoriesIds('enabled_repositories','themes');
						$args->repositories = (array)$repos;
					}
				}
                break;
                
            case 'theme_information':
           
                if (!isset($args->repositories))
				{
				if(array_key_exists('repository_id', $_REQUEST) && !empty($_REQUEST['repository_id']))
                {
                    $args->repositories = array($_REQUEST['repository_id']);
                }
				else
				{
					$rm = WPRC_Loader::getModel('repositories');
					$repos=$rm->getRepositoryByExtension($_GET['theme']);
					if (isset($repos) && $repos!==false && count($repos)>0)
					{
						$args->repositories = array($repos[0]['id']);						
					}
					else
					{
						$repo=$rm->getRepositoryByField('repository_endpoint_url',WPRC_WP_THEMES_REPO);
						if (isset($repo) && $repo!==false)
						{
							$args->repositories = array($repo->id);						
						}
					}
				}
				}
                break;    
                
            case 'feature_list':
                $rm = WPRC_Loader::getModel('repositories');                    
                $args->repositories = $rm->getRepositoriesIds('enabled_repositories','themes');
                break;
        }
        
        $args = apply_filters('wprc_themes_api_args_'.$action, $args);
        
        return $args;
    }

/**
 * Search plugins in multiple repositories 
 * This method replaces 'plugins_api' and 'themes_api' function
 */ 
    public function extensionsApi($state, $action, $args)
    { 
         return parent::extensionsApi($state, $action, $args, $this->extension_type);
    }
    
/**
 * Form structure of the extensions api results
 */    
    public function extensionsApiResult($api_results, $action, $args)
    {
        switch($action)
        {
            case 'query_themes':
                $result = $this->extensionApiResultsQueryExtensions($api_results, $this->extension_type);
            break;

            case 'theme_information':
                $themes_information = array_pop($api_results->results);
                
                $result = $themes_information;                                
                break;
            
            case 'feature_list':
                $input_features = $api_results->results;
                
                $all_features = array();                
                if(count($input_features) > 0)
                {
                    foreach($input_features AS $repository_url => $features)
                    {                  
                        if(!is_wp_error($features))
                        {
                            foreach($features AS $feature_category => $feature_values)
                            {
                              
                                if(!array_key_exists($feature_category, $all_features))
                                {
                                    $all_features[$feature_category] = array();
                                }
                                
                                $all_features[$feature_category] = array_merge($all_features[$feature_category], $feature_values);
                            }
                            
                        }
                    }
                }
                
                // sort all values in all feature categories
                if(count($all_features) > 0)
                { 
                    $sorted_values = array();
                    foreach($all_features AS $feature_category => $feature_values)
                    {
                        asort($feature_values);
                        reset($feature_values);
                        $all_features[$feature_category] = $feature_values;
                    }
                }
                $result = $all_features;                              
                break;
        }
        
        $result = apply_filters('wprc_themes_api_results_'.$action, $result);
        
        return $result;
    }
        
/**
 * Display search results
 */     
    public function displaySearchResults()
    {        
        $wp_list_table = WPRC_Loader::getListTable('theme-install');
        $pagenum = $wp_list_table->get_pagenum();
        $wp_list_table->prepare_items();

        $title = __('Install Themes');
        $parent_file = 'themes.php';
        if ( !is_network_admin() )
        	$submenu_file = 'themes.php';
        
        wp_enqueue_script( 'theme-install' );
        
        add_thickbox();
        wp_enqueue_script( 'theme-preview' );
        
        if(isset($tab))
        {
            $body_id = $tab;
        
            do_action('install_themes_pre_' . $tab); //Used to override the general interface, Eg, install or theme information.
        }
        
        $help = '<p>' . sprintf(__('You can find additional themes for your site by using the Theme Browser/Installer on this screen, which will display themes from the <a href="%s" target="_blank">WordPress.org Theme Directory</a>. These themes are designed and developed by third parties, are available free of charge, and are compatible with the license WordPress uses.','installer'), 'http://wordpress.org/extend/themes/') . '</p>';
        $help .= '<p>' . __('You can Search for themes by keyword, author, or tag, or can get more specific and search by criteria listed in the feature filter. Alternately, you can browse the themes that are Featured, Newest, or Recently Updated. When you find a theme you like, you can preview it or install it.','installer') . '</p>';
        $help .= '<p>' . __('You can Upload a theme manually if you have already downloaded its ZIP archive onto your computer (make sure it is from a trusted and original source). You can also do it the old-fashioned way and copy a downloaded theme&#8217;s folder via FTP into your <code>/wp-content/themes</code> directory.','installer') . '</p>';
        
        get_current_screen()->add_help_tab( array(
        	'id'      => 'overview',
        	'title'   => __('Overview'),
        	'content' => $help
        ) );
        
        get_current_screen()->set_help_sidebar(
        	'<p><strong>' . __('For more information:','installer') . '</strong></p>' .
        	'<p>' . __('<a href="http://codex.wordpress.org/Using_Themes#Adding_New_Themes" target="_blank">Documentation on Adding New Themes</a>','installer') . '</p>' .
        	'<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>','installer') . '</p>'
        );
        
        include(ABSPATH . 'wp-admin/admin-header.php');
        
        $this->renderAdditionalSearchUI();
        
        echo '<div class="wrap">';
        screen_icon();
        
        if ( is_network_admin() ) 
        { 
            echo '<h2>'.esc_html( $title ).'</h2>';
        }
        else 
        { 
            echo '<h2 class="nav-tab-wrapper"><a href="themes.php" class="nav-tab">'.esc_html_x('Manage Themes', 'theme').'</a>
            <a href="theme-install.php" class="nav-tab nav-tab-active">'.esc_html( $title ).'</a></h2>';
        }
        
        $wp_list_table->views(); 
        $wp_list_table->display(); 
        
        echo '<br class="clear" />';
        if(isset($tab) && isset($paged))
        {
            do_action('install_themes_' . $tab, $paged);
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