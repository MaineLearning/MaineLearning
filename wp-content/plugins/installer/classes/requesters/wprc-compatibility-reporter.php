<?php
class WPRC_CompatibilityReporter extends WPRC_Requester
{    
/**
 * Prepare report data from the post data
 *
 * @param array $post_data raw data
 * 
 * @return array report
 */     
    public function prepareRequest(array $post_data)
    {
        // get key and type of extension that need to be checked
        if(!array_key_exists('check_extension_name', $post_data) || !array_key_exists('check_extension_type', $post_data))
        {
            return false;
        }
		
        $check_extension_name = $post_data['check_extension_name'];
        $check_extension_type = $post_data['check_extension_type'];
        $check_extension_repository_url = $post_data['check_extension_repository_url'];
        $check_extension_version = $post_data['check_extension_version'];
        $check_extension_slug = $post_data['check_extension_slug'];

        $extension_model = WPRC_Loader::getModel('extensions');

        $extensions_tree = $extension_model->getFullExtensionsTree();
        $extensions_tree['themes'] = $extension_model->changeExtensionsKey($extensions_tree['themes'],'Name');

		$typekeys = $extension_model->getExtensionTypeKeys();
		$activetypekeys = $extension_model->getActiveExtensionTypeKeys();
		
		
        // ---------- left extensions ------------
        if(array_key_exists($check_extension_type, $extensions_tree) && is_array($check_extension))
        {
            $check_extension = $extensions_tree[$check_extension_type][$check_extension_name];
            $left_extension = $this->prepareExtensionInfo($check_extension_name, $check_extension_type, $check_extension);
        }
        else
        {
            $left_extension = array(
                'name' => $check_extension_name,
                'type' => $check_extension_type,
                'repository_url' => $check_extension_repository_url,
                'version' => $check_extension_version,
                'slug' => $check_extension_slug
            );
        }

        $left_extensions = array(
            $check_extension_name => $left_extension
        );

        // ---------- right extensions -----------
        $active_extensions = $extension_model->getActiveExtensions();

        // cycle on each extension type
        if(count($active_extensions)==0)
        {
            return false;
        }

        $right_extensions = array();
        foreach($active_extensions AS $extension_type => $extensions)
        {
            for($i=0; $i<count($extensions); $i++)
            {
                $key=$activetypekeys[$extension_type];
                //$key=$typekeys[$extension_type];
				$extension=$extension_model->findExtensionwithKey($extensions_tree,$extension_type,$key,$extensions[$i]);
				if ($extension===false) continue;
				//if (!isset($extensions_tree[$extension_type][$extensions[$i]])) continue;
				//$extension = $extensions_tree[$extension_type][$extensions[$i]];
                $right_extensions[$extension['Name']] = $this->prepareExtensionInfo($extensions[$i], $extension_type, $extension);
            }
        }

        // prepare report data
        $report_data = array(
            'left_extensions' => $left_extensions,
            'right_extensions' => $right_extensions
        );

        $creport= $this->formRequestStructure($report_data);
		//file_put_contents(WPRC_PLUGIN_PATH.'/debug_comp.txt',print_r($report_data,true),FILE_APPEND);
		return $creport;
    }

    /**
     * Prepare extension info
     *
     * @param string $extension_key extension key
     * @param array $all_extension_info full extension info array
     * @return array
     */
    private function prepareExtensionInfo($extension_key, $extension_type, $all_extension_info)
    {
        switch($extension_type)
        {
            case 'plugins':
                $extension_type_singular = 'plugin';
                $extension_path = $extension_key;
                break;

            case 'themes':
                $extension_type_singular = 'theme';
                $extension_path = '';
                break;
        }

        $slug = '';
        if(array_key_exists('extension_slug', $all_extension_info))
        {
            $slug = $all_extension_info['extension_slug'];
        }

        $repository_endpoint_url = '';
        if(array_key_exists('repository_endpoint_url', $all_extension_info))
        {
            $repository_endpoint_url = $all_extension_info['repository_endpoint_url'];
        }

        $filtered_extension = array(
            'name' => $all_extension_info['Name'],
            'type' => $extension_type_singular,
            'path' => $extension_path,
            'version' => $all_extension_info['Version'],
            'slug' => $slug,
            'repository_url' => $repository_endpoint_url
        );

        return $filtered_extension;
    }

/**
 * Send report to the server
 * 
 * @param array report
 * @return mixed response
 */   
    public function sendRequest(array $report,$once=false)
    {
        if (!$once)
			$response = $this->RepositoryConnector->sendWPRCRequestWithRetries('post',$report);
        else
			$response = $this->RepositoryConnector->sendWPRCRequest('post',$report);
        return $response;
    }
  
 /**
 * Form report structure
 * 
 * @param array array of report data
 * 
 * @return array associative array of specified format
 */     
    public function formRequestStructure(array $report_data)
    {
        $site_info = $this->getSiteInfo();

        /*
        $request = array(
            'site' => $site_info,

            'left_extensions' => array(
                'collabpress/cp-loader.php' => array(
                    'name' => 'CollabPress',
                    'type' => 'plugin',
                    'path' => 'collabpress/cp-loader.php',
                    'version' => '1.2.1',
                    'slug' => 'collabpress',
                    'repository_url' => 'http://api.wordpress.org/plugins/info/1.0/'
                )
            ),

            'right_extensions' => array(
                'new-googleplusone/GooglePlusOne.php' => array
                (
                    'name' => 'New GooglePlusOne',
                    'type' => 'plugin',
                    'path' => 'new-googleplusone/GooglePlusOne.php',
                    'version' => '1.4',
                    'slug' => '',
                    'repository_url' => ''
                ),

                'post-recommendation/pluginroot.php' => array
                (
                    'name' => 'Post Recommendation',
                    'type' => 'plugin',
                    'path' => 'post-recommendation/pluginroot.php',
                    'version' => '1.0',
                    'slug' => 'post-recommendation',
                    'repository_url' => 'http://api.wordpress.org/plugins/info/1.0/'
                ),

                'wp-installer/wp-installer.php' => array
                (
                    'name' => 'WP Repository Client',
                    'type' => 'plugin',
                    'path' => 'wp-installer/wp-installer.php',
                    'version' => '0.2.0',
                    'slug' => '',
                    'repository_url' => ''
                )
            )
        );
        */

        $request = array(
            'site' => $site_info,
            'left_extensions' => $report_data['left_extensions'],
            'right_extensions' => $report_data['right_extensions']
        );

        $report = array(
            'action' => 'check_compatibility',
            'request' => $request
        );

        return $report;
    }
}
?>