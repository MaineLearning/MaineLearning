<?php
abstract class WPRC_Extensions_API
{
    private $calls_number = 0;
    
/**
 * Form arguments for extensions_api function
 * 
 * @param mixed arguments array
 * @param string action
 */ 
    public abstract function extensionsApiArgs($args, $action);

/**
 * Search plugins in multiple repositories 
 * This method replaces 'plugins_api' and 'themes_api' function
 */ 
  //  public abstract function extensionsApi($state, $action, $args);
    
/**
 * Form structure of the extensions api results
 */    
    public abstract function extensionsApiResult($api_results, $action, $args);
    
/**
 * Display search results
 */     
    public abstract function displaySearchResults();
     
 /**
  * Render additional search UI
  * 
  * @param string extension type ( 'plugins' | 'themes' )
  */    
    public function renderAdditionalSearchUI($extension_type)
    {
        if(!isset($extension_type))
        {
            return false;
        }
        
        echo '<style type="text/css">#search-'.$extension_type.'{display:none;}</style>';
        
        $rm = WPRC_Loader::getModel('repositories');
        $extension_type2='plugins_not_deleted';
		switch ($extension_type)
		{
			case 'plugins':
						$extension_type2='plugins_not_deleted';
						break;
			case 'themes':
						$extension_type2='themes_not_deleted';
						break;
		}
		$repos = $rm->getRepositoriesListByType($extension_type2);

        
        if(array_key_exists('repos', $_REQUEST))
        {
            $selected_repos = (array)$_REQUEST['repos'];
            
            if(count($selected_repos) > 0)
            {
                // reassign enabled repositories
                for($i=0; $i<count($repos); $i++)
                {
                    if(in_array($repos[$i]->id, $selected_repos))
                    {
                        $repos[$i]->repository_enabled = 1;
                    } 
                    else
                    {
                        $repos[$i]->repository_enabled = 0;
                    }
                }
            }
        }

        $free = __('Free', 'installer');
        $paid = __('Paid', 'installer');
        $prices_items = array($free => 1, $paid => 1);
        
        if(array_key_exists('prices', $_REQUEST))
        {
            $selected_prices = (array)$_REQUEST['prices'];
            
            if(count($selected_prices) > 0)
            {
                // reassign enabled price filters
                foreach($prices_items AS $price => $filter_enabled)
                {
                    if(in_array($price, $selected_prices))
                    {
                        $prices_items[$price] = 1;
                    } 
                    else
                    {
                        $prices_items[$price] = 0;
                    }
                }
            }
        }
        
        // --- set caption of the prices list
        
        // include language class
        WPRC_Loader::includeLanguageContainer();
        $wprcLang = WPRC_LanguageContainer::getLanguageArray();
        
        if($prices_items['Free'] && $prices_items['Paid'])
        {
            $prices_control_caption = $wprcLang['search_free_and_paid_'.$extension_type];
        }
        else
        {
            if($prices_items['Free'])
            {
                $prices_control_caption = $wprcLang['search_free_'.$extension_type];
            } 
            
            if($prices_items['Paid'])
            {
                $prices_control_caption = $wprcLang['search_paid_'.$extension_type];
            } 
        }
        
        $prices_items_selector = array();
        foreach ( $prices_items as $key => $item ) {
            $prices_items_selector[$key] = array(
                'id' => $key,
                'name' => $key,
                'enabled' => $item
            );
        }

        $json_repos = json_encode($repos);
        $json_prices = json_encode($prices_items_selector);
        /*$jsrepos=array();
		foreach ($repos as $repo)
		{
			$jsrepos[]='repos[]='.$repo->id;
		}
		$jsrepos='"'.implode('&',$jsrepos).'";';*/
		
        $wprc_debug = (defined( 'WPRC_DEBUG' ) && WPRC_DEBUG === true) ? 1 : 0;

		echo "<script language=\"javascript\">
        jQuery(document).ready(function()
        {
			function getRepos()
			{
				var repos_list='';
				var repos=[];
				var repos_ret='';
				/*var selector='';
				if (jQuery('form#search-themes').length>0)
				{
					selector='form#search-themes';
				}
				else if (jQuery('form#search-plugins').length>0)
				{
					selector='form#search-plugins';
				}
				else
				{
					selector='foo';
				}*/
				//select[name=\"repos[]\"]
				repos=jQuery('#repos').val();
				//repos=repos_list.split(',');
				for (var i=0; i<repos.length; i++)
				{
					if (i==repos.length-1)
						repos_ret+='repos[]='+repos[i];
					else
						repos_ret+='repos[]='+repos[i]+'&';
				}
				return ({str:repos_ret,arr:repos});
			}
			
			/* add the repositories from select list dynamically */";
            ?>
                wprc.search.renderAdditionalUI('<?php echo $json_repos; ?>', '<?php echo $json_prices; ?>', '<?php echo $extension_type; ?>', '<?php echo $prices_control_caption; ?>' );
            <?php
            echo 'wprc.search.renderClearCacheUI('.$wprc_debug.', "'.$extension_type.'");';
			echo "jQuery('.popular-tags a').click(function(){
				var link=jQuery(this).attr('href');
				var repos=getRepos().str;
				link=link.replace('type=tag','type=tag&'+repos);
				jQuery(this).attr('href',link);
			});
			
			/* add the repositories from select list dynamically */
			var featureform=jQuery('.feature-filter').eq(0).parent('form');
			featureform.submit(function(){ //listen for submit event
				var repos=getRepos().arr;
				jQuery.each(repos, function(i,repo){
					jQuery('<input />').attr('type', 'checkbox')
						.attr('name', 'repos[]')
						.attr('value', repo)
						.attr('checked', 'checked')
						.css({display:'none'})
						.appendTo(featureform);
				});

				return true;
			});
            
        });
        </script>";
    }
    
    /**
     * AJAX function that deletes the search cache
     */
    public static function clear_extension_search_cache() {

        WPRC_Installer::clear_all_cache();

        _e( 'The cache has been cleaned up. Reloading page...', 'installer' );

        die();

    }
/**
 * Form input args for 'query_extensions' requests ( 'query_plugins' | 'query_themes')
 * 
 * @param array input arguments array
 * @param int number of search items per page
 *
 * @return array processed input arguments array
 */ 
    protected function extensionApiArgsQueryExtensions($args, $per_page)
    {
        // set per_page value 
        if(array_key_exists('repos', $_REQUEST))
        {
            $args->repositories = (array)$_REQUEST['repos'];
        }

        // set prices filter
        $prices = array();
        if(array_key_exists('prices', $_REQUEST))
        {
            $prices = (array)$_REQUEST['prices'];
        }
                
        $price_array = $this->getPricesFilterValues($prices);
                            
        $args->hide_free = $price_array['hide_free'];
        $args->hide_paid = $price_array['hide_paid'];

        $args -> exact = 0;
        if(array_key_exists('exact', $_REQUEST))
        {
            $args -> exact = 1;
        }

        $args->per_page = $per_page;

        return $args;
    }

/**
 * Form results for 'query_extensions' requests ( 'query_plugins' | 'query_themes')
 * 
 * @param array request results array
 * @param string extension type ( 'plugins' | 'themes' )
 *
 * @return array processed request results array
 */ 
    protected function extensionApiResultsQueryExtensions($api_results, $extension_type)
    { 
        
		//return($api_results);
		// default wp behaviour for tabs other than search
		if (isset($_GET['tab']))
		{
			if ($_GET['tab']!='search' /*&& $_GET['tab']!='plugin-information' && $_GET['tab']!='theme-information'*/)
			{
				$general_results = new stdClass;
				$general_results->info = $api_results->info;
				$general_results->$extension_type = $api_results->$extension_type;
				return $general_results; 
			}
			
			/*if ($_GET['tab']=='plugin-information' || $_GET['tab']=='theme-information')
			{
				return $api_results;
			}*/
		}

		// increment calls number of this method
        // if calls_number == 1 then WP (Plugin|Theme) Table List calls this method (wp table list classes have hardcoded per_page number)
        $this->calls_number++; 
        
        $rm = WPRC_Loader::getModel('repositories');
                
       $general_results = new stdClass;
       $plugins = array();

       // filter by price filter value
       $prices = array();
       if(array_key_exists('prices', $_GET))
       {
            $prices = $_GET['prices'];
       }
       $price_array = self::getPricesFilterValues($prices);
                
       $hide_free = $price_array['hide_free'];
       $hide_paid = $price_array['hide_paid'];

       $pagination_info = array();
		
       $all_results = 0;

        if(isset($api_results->results) && count($api_results->results) > 0)
        {

            //if ($_GET['tab']=='search')
            	$repos=$rm->getAllRepositoriesNotDeleted();
            //else
            	//$repos=$rm->getAllRepositories();
            	
            $repo_map=array();
            foreach ($repos as $repo)
            {
            	$repo_map[$repo->repository_endpoint_url]=$repo;
            }

            // Saves max number of results
            $max_results = 0;
            $results_per_repo = array();
            foreach($api_results->results AS $repository_url => $result)
            {    

                if ( isset( $result -> info['results'] ) )
                    $max_results = max( $max_results, (int)$result -> info['results'] );

                $repo_results = ( isset( $result->info['results'] ) ) ? $result->info['results'] : 0;
                $all_results += $repo_results;

                $results_per_repo[ $repo_map[ $repository_url ] -> id ] = array(
                    'results' => $repo_results,
                    'pages' => isset( $result -> info['pages'] ) ? (int)$result -> info['pages'] : 0,
                    'id' => $repo_map[ $repository_url ] -> id
                );
                $filtered_plugins = array();
                // get source id from the local database
                $repository = $repo_map[$repository_url]; //$rm->getRepositoryByField('repository_endpoint_url',$repository_url);

             // set source to each plugin
                //$extension = new stdClass;
                if(isset($result->$extension_type) && count($result->$extension_type) > 0)
                {
                    foreach($result->$extension_type AS $extension)
                    { 
                        $extension->repository = $repository; 
                                
                        if(!isset($extension->price))
                        {
                            $extension->price = 0;
                        }
                            
                        if($hide_free && $extension->price == 0)
                        {
                            continue;
                        }
                                
                        if($hide_paid && $extension->price > 0)
                        {
                            continue;
                        }
                              
                        $filtered_plugins[] = $extension;
                    }
                }
                
                // get pagination info from the repositories responses
                if((count($filtered_plugins) > 0))
                {
                    $pagination_info[$repository_url] = $result->info;
                }
                        
                if(is_array($filtered_plugins) && count($filtered_plugins) > 0)
                {
                    $plugins = array_merge($plugins, $filtered_plugins);
                }
                
                unset($filtered_plugins);
           }
       }
                
       // sort the plugins
       $slugs = array();
       $names = array();
       $authors = array();
                
       foreach ($plugins as $key => $item) 
       {
            $slugs[$key]  = $item->slug;
            $names[$key] = $item->name;
            $authors[$key] = $item->name;
       }
       //array_multisort($slugs, SORT_ASC, SORT_STRING, $authors, SORT_ASC, SORT_STRING, $names, SORT_ASC, SORT_STRING, $plugins);
                
       // ------------ calculate general pagination parameters --------------
       $repository_pages = array();
       
       if(count($pagination_info) > 0)
       {     
           foreach($pagination_info AS $repository_url => $pagination_attr)
           { 
                $info = $pagination_attr;
                $repository_pages[] = $pagination_attr['pages'];
                //$all_results += $pagination_attr['results'];
           }
       }


       if(count($plugins) == 0)
       {
            $info['page'] = 0;
            $info['pages'] = 0;
            $info['results'] = 0;
            $info['total_pages'] = 0;
            
       }
       else
       {
            $general_pages_number = max($repository_pages);
            
            if($this->calls_number == 1)  
            {
                $multiplier = 1;
                if($extension_type == 'plugins')
                {
                    $multiplier = 36 / WPRC_PLUGINS_API_QUERY_PLUGINS_PER_PAGE;
                }
                
                if($extension_type == 'themes')
                {
                    $multiplier = 36 / WPRC_THEMES_API_QUERY_THEMES_PER_PAGE;
                }
                $info['results'] = $all_results*$multiplier; // in the WP Table list classes per_page is set to 36. Current plugin has per_page which is equal 6
            }      
            else
            {
                $info['results'] = $all_results;
            }


            $info['pages'] = $general_pages_number;

        }

        $general_results->info = $info;
        $general_results->results_per_repo = $results_per_repo;
        $general_results->$extension_type = $plugins;
        
        return $general_results; 

    }
    
/**
 * Get prices filter value
 * 
 * @param array price array ( array( 'Free'=> (0|1), 'Paid' => (0|1)))
 * 
 * @return array ( array( 'hide_free' => (0|1), 'hide_paid' => (0|1)) )
 */     
    protected function getPricesFilterValues($prices_array)
    {                

        $free_in_array = in_array('Free', $prices_array);
        $paid_in_array = in_array('Paid', $prices_array);

        $hide_free=0;
		$hide_paid=0;
		if($free_in_array)
        {
            $hide_free = 0;
        }
        else
        {
            $hide_free = 1;
        }
                    
        if($paid_in_array)
        {
            $hide_paid = 0;
        }
        else
        {
            $hide_paid = 1;
        }
        
        if($hide_free && $hide_paid)
        {
            $hide_free = 0;
            $hide_paid = 0;
        }
        
        return array(
            'hide_free' => $hide_free,
            'hide_paid' => $hide_paid
        );        
    }
    
 /**
 * Search plugins in multiple repositories 
 * This method replaces 'plugins_api' and 'themes_api' function
 */ 
    public function extensionsApi($state, $action, $args, $extension_type)
    {

		// default wp behaviour for tabs other than search
		//if ($action=='hot_tags') return false;
		if (isset($_GET['tab']))
		{
			if ($_GET['tab']!='dashboard' && $_GET['tab']!='search' && $_GET['tab']!='plugin-information' && $_GET['tab']!='theme-information')
			{
				return false;
			}
		}
       
        $rauth=true;
		$rpass='';
		$ruser='';
		if(isset($_GET['repository_id']) && isset($_GET['user']) && isset($_GET['pass']) && ($action=='plugin_information' || $action=='theme_information'))
		{
			$rm = WPRC_Loader::getModel('repositories');
			$rid=$_GET['repository_id'];
			$repository = $rm->getRepository($rid);
			$ruser=rawurldecode($_GET['user']);
			$rsalt=$repository->repository_authsalt;
			$rpass=rawurldecode($_GET['pass']); //WPRC_Security::decrypt($repository->repository_authsalt,rawurldecode($_GET['pass']));
			
			$login = $rm->testLogin($rid, $ruser, $rpass);
			if($login!=false && empty($login['error'])){
				$rauth=true;
			}else{
				$rauth=false;    
			}
        }
		
		$repositories_ids = array();
        if(isset($args->repositories))
        {
            $repositories_ids = $args->repositories;
            unset($args->repositories);
        }
        
        $rm = WPRC_Loader::getModel('repositories');
        $repos = $rm->getRepositoriesByIds($repositories_ids);
        $results = array();

        // Remade per_page parameters in order to get consistent pagination
        $per_page = 0;
        $repos_number = count($repos);

        if ( $action == 'query_plugins' )
            $per_page = WPRC_PLUGINS_API_QUERY_PLUGINS_PER_PAGE;
        elseif ( $action == 'query_themes' )
            $per_page = WPRC_THEMES_API_QUERY_THEMES_PER_PAGE;        

        $results_per_repo = array();
        for($i = 0; $i < $repos_number; $i++)
        {
            $res = false;

            $server_url = $repos[$i]->repository_endpoint_url;
            $repository_name = $repos[$i]->repository_name;
            $repository_username = $repos[$i]->repository_username;
            $repository_password = $repos[$i]->repository_password;
			$salt=$repos[$i]->repository_authsalt;
			$rid=$repos[$i]->id;
            
			$body_array = array(
                'action' => $action,
            );
            
			if($repository_username<>'' && $repository_password<>'')
            {
                /*$args->username = $repository_username;
                $args->password = $repository_password;*/
				//$send_password=WPRC_Security::encrypt($salt,$repository_password);
				$body_array['auth'] = array('user'=>$repository_username,'pass'=>$repository_password,'salt'=>$salt);
				//$body_array['auth'] = array('user'=>$repository_username,'pass'=>$repository_password,'salt'=>$salt);
            }
            else
            {
                unset($args->username);
                unset($args->password);
            }

            $request_array = $args;
            $request_array -> per_page = $per_page;

            $body_array['request'] = serialize($args);

            if(isset($args->slug))
            {
                $body_array['slug'] = $args->slug;
            }

            
            // debug log
            $reqargs=$body_array;
            if (isset($reqargs['auth'])) 
            {
                $reqargs['auth']='AUTH info';
            }
            $msg=sprintf("API Request to %s, request args: %s",$server_url,print_r($reqargs,true));
            WPRC_Functions::log($msg,'api','api.log');
            unset($reqargs);
            
            $cached_request_results = apply_filters('wprc_extensions_api_before_each_repository', $server_url, $action, $args);

            if($cached_request_results)
            {
               $results[$server_url] = $cached_request_results;
               
               // log
               $msg=sprintf("API Request to %s, using cached results",$server_url);
               WPRC_Functions::log($msg,'api','api.log');
               
               continue;
            }

           	$request = wp_remote_post($server_url, array( 'timeout' => 15, 'body' => $body_array) );
            
            // log
            $msg=sprintf("API Request to %s, timeout: %d, response: %s",$server_url,15,print_r($request,true));
            WPRC_Functions::log($msg,'api','api.log');

            if ( is_wp_error($request) ) 
            {
    			$res = new WP_Error('extensions_api_failed', __('An unexpected HTTP Error occurred during the API request.', 'installer'), $request->get_error_message() );
                // log
                $msg=sprintf("API Request to %s, response error: %s",$server_url,print_r($request->get_error_message(),true));
                WPRC_Functions::log($msg,'api','api.log');
    		} 
            else 
            {
                $request_body = wp_remote_retrieve_body( $request );
                

                if(is_serialized($request_body))
                {
                    $res = @unserialize( $request_body );
                }

    			if ( false === $res )
                {
    				$res = new WP_Error('extensions_api_failed', __('An unknown error occurred.', 'installer'), wp_remote_retrieve_body( $request ) );
                    // log
                    $msg=sprintf("API Request to %s, unknown error in response body: %s",$server_url, print_r($request_body,true));
                    WPRC_Functions::log($msg,'api','api.log');
                }
				else if (is_object($res) && isset($res->error))
                {
    				$res = new WP_Error('extensions_api_failed', $res->error, wp_remote_retrieve_body( $request ) );
                    // log
                    $msg=sprintf("API Request to %s, action not implemented error: %s",$server_url, print_r($res,true));
                    WPRC_Functions::log($msg,'api','api.log');
                }
				else if (is_array($res) && isset($res['error']))
                {
    				$res = new WP_Error('extensions_api_failed', $res['error'], wp_remote_retrieve_body( $request ) );
                    // log
                    $msg=sprintf("API Request to %s, action not implemented error: %s",$server_url, print_r($res,true));
                    WPRC_Functions::log($msg,'api','api.log');
                }
				else
				{
					// add some custom info onto the results (like repository salt etc..)
					if ($action=='query_plugins')
					{
						foreach ($res->plugins as $key=>$extension)
						{
							$res->plugins[$key]->salt=$salt;
							$res->plugins[$key]->repository_id=$rid;
                            // strip non-serializable characters
							$res->plugins[$key]->description=preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\xFF]/u', '', $res->plugins[$key]->description);
						}
					}
					elseif ($action=='query_themes')
					{
						foreach ($res->themes as $key=>$extension)
						{
							$res->themes[$key]->salt=$salt;
							$res->themes[$key]->repository_id=$rid;
                            // strip non-serializable characters
							$res->themes[$key]->description=preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\xFF]/u', '', $res->themes[$key]->description);
						}
					}
					elseif ($action=='plugin_information' || $action=='theme_information' /*|| $action=='feature_list'*/)
					{
							if (is_object($res))
							{
								$res->rauth=$rauth;
								$res->pass=$rpass;
								$res->user=$ruser;
								$res->salt=$salt;
								$res->repository_id=$rid;
							}
					}
				}
    		}

            $cached_them=apply_filters('wprc_extensions_api_after_each_repository', $server_url, $action, $args, $res);
            // log
            $msg=sprintf("API Request to %s, results cached: %s",$server_url, ($cached_them==false)?'NO':'YES');
            WPRC_Functions::log($msg,'api','api.log');
            
            // set source
            $results[$server_url] = $res;    
        }

        $general_results = new stdClass;
        $general_results->results = $results;
        
        return $general_results;
    }
}
?>