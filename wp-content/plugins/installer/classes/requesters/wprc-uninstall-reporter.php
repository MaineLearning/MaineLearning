<?php
class WPRC_UninstallReporter extends WPRC_Requester
{
/**
 * Report about uninstallation of extension to the server
 * 
 * @param array associative array of uninstall reason report
 */ 
    public function sendRequest(array $uninstall_reason_report)
    {        
        $response = $this->RepositoryConnector->sendWPRCRequestWithRetries('post',$uninstall_reason_report);
        
        return $response;
    }
    
 /**
 * Prepare uninstall data for the sending
 * 
 * @param array array of posted data
 * 
 * @return array associative array of specified format
 */     
    public function prepareRequest(array $post_array)
    {        
        if(!array_key_exists('extension_keys',$post_array) || !array_key_exists('extension_type',$post_array) || !array_key_exists('uninstall_reason_code',$post_array))
        {
            return false;    
        }
        
        if(!is_array($post_array['extension_keys']))
        {
            return false;
        }
        
        $extension_keys = $post_array['extension_keys'];
        
        // disable request sending if several plugins were deactivated
        if(count($extension_keys)>1)
        {
            //return false;
        }
        
        $extension_type = preg_match('/^(plugin|theme)$/',$post_array['extension_type']) ? $post_array['extension_type'] : '';
        
        // set uninstall reason code
        $uninstall_reason_code = $post_array['uninstall_reason_code'];
        
        if(array_key_exists('uninstall_reason_code_child',$post_array) && $post_array['uninstall_reason_code_child']<>'')
        {
            $uninstall_reason_code .= '-'.$post_array['uninstall_reason_code_child'];
        }
     
        // set uninstall reason description
        $uninstall_reason_description = $this->filterText($post_array['uninstall_reason_description']);
        
        // set uninstall reason items -------------------------------
        $problem_array_name = '';
        $unistall_reason_items_type = '';
        switch($uninstall_reason_code)
        {            
            case '3-1':
                $problem_array_name = 'problem_themes';
                $unistall_reason_items_type = 'theme';
                break;
                
            case '3-2':
                $problem_array_name = 'problem_plugins';
                $unistall_reason_items_type = 'plugin';
                break;
        }
        
        $uninstall_reason_items_names = array();
        $uninstall_reason_items = array();
        if(isset($problem_array_name) && array_key_exists($problem_array_name,$post_array))
        {
            if(is_array($post_array[$problem_array_name]) && count($post_array[$problem_array_name]) > 0)
            {
                $uninstall_reason_items_names = $post_array[$problem_array_name];
            }
        }

        // get all extension data
        $ext_model = WPRC_Loader::getModel('extensions');
        $all_extensions = $ext_model->getFullExtensionsTree();
        $all_extensions['themes'] = $ext_model->changeExtensionsKey($all_extensions['themes'],'Name');
        
        // form plural extension type name
        $unistall_reason_items_type_plural = $unistall_reason_items_type.'s';
        
        // set all information for uninstall reason items
        $extension_path = '';
        for($i=0; $i<count($uninstall_reason_items_names); $i++)
        {
            $extension = $all_extensions[$unistall_reason_items_type_plural][$uninstall_reason_items_names[$i]];
            
            if($unistall_reason_items_type == 'plugin')
            {
                $extension_path = $uninstall_reason_items_names[$i];
            }            
            
            $uninstall_reason_items[$uninstall_reason_items_names[$i]] = array(
                'name' => $extension['Name'],
                'type' => $unistall_reason_items_type,
                'path' => $extension_path, 
                'version' => $extension['Version'],
                'slug' => $extension['extension_slug'],
                'repository_url' => $extension['repository_endpoint_url']
            );
        }   

        // set report data array ------------------------------------
        $extensions = array();
        
        // form plural extension type name
        $extension_type_plural = $extension_type.'s';
             
        $extension_path = '';
        for($i=0; $i<count($extension_keys); $i++)
        {
			if (!isset($all_extensions[$extension_type_plural][$extension_keys[$i]])) continue;
			$extension = $all_extensions[$extension_type_plural][$extension_keys[$i]];
            
            if($extension_type == 'plugin')
            {
                $extension_path = $extension_keys[$i];
            }            

            $slug = '';
            if(array_key_exists('extension_slug',$extension))
            {
                $slug = $extension['extension_slug'];
            }

            $repository_endpoint_url = '';
            if(array_key_exists('repository_endpoint_url',$extension))
            {
                $repository_endpoint_url = $extension['repository_endpoint_url'];
            }

            $extensions[$extension_keys[$i]] = array(
                'name' => $extension['Name'],
                'type' => $extension_type,
                'path' => $extension_path, 
                'version' => $extension['Version'],
                'slug' => $slug,
                'repository_url' => $repository_endpoint_url
            );
        }   

        $report_data = array(
            'extensions' => $extensions,
            'uninstall_reason_code' => $uninstall_reason_code,
            'uninstall_reason_description' => $uninstall_reason_description,
            'uninstall_reason_items' => $uninstall_reason_items,
        );

        $report = $this->formRequestStructure($report_data);

		//file_put_contents(WPRC_PLUGIN_PATH.'/debug_uninstall.txt',print_r($report_data,true),FILE_APPEND);
		return $report;
    }
    
/**
 * Form uninstall reason report structure
 * 
 * @param array array of report data
 * 
 * @return array associative array of specified format
 */     
    public function formRequestStructure(array $report_data)
    {
        $site_info = $this->getSiteInfo();
        
        $report = array(
            'action' => 'uninstall_report',
            'request' =>
                array(
                'site' => $site_info,
                'extensions' => $report_data['extensions'], 
                'uninstall_reason' => array(
                    'code' => $report_data['uninstall_reason_code'],
                    'description' => $report_data['uninstall_reason_description'],
                    'items' => $report_data['uninstall_reason_items']
                    )
                )
        );
        
        return $report;
    }
}
?>