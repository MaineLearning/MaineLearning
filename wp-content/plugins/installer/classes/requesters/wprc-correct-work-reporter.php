<?php
class WPRC_CorrectWorkReporter extends WPRC_Requester
{
    private $sent_reports_list_option_name = 'wprc_sent_reports_list';

/**
 * Return option name of sent reports list
 */     
    private function getSentReportsListOptionName()
    {
        return $this->sent_reports_list_option_name;
    }

/**
 * Send report to the server
 * 
 * @param array report
 */     
    public function sendRequest(array $report)
    {  
        // validate input data
        if(!array_key_exists('request', $report))
        {
            return false;
        }
        
        if(!array_key_exists('reports', $report['request']))
        { 
            return false;
        }
        
        // get extensions array from the report
        $extensions = array_keys($report['request']['reports']);

        if(count($extensions) <= 0)
        {  
            return false;
        }
        
		$send_result = $this->RepositoryConnector->sendWPRCRequestWithRetries('post', $report);
		
		if(!$send_result)
        {
            return false;
        }

        // add extension to the sent reports list
        return $this->addSentReportsListItems($extensions);
    }
    
/**
 * Prepare data for report
 * 
 * Save list of activated plugins and last activation date, set timer for the specified plugin
 * 
 * @param string plugin name
 */ 
    public function initRequest($extension_name)
    {
        if($extension_name == '')
        {
            return false;
        }
        
        // get list of active plugins
        $active_plugins = array();
        
        $extension_model = WPRC_Loader::getModel('extensions');
        $active_plugins_names = $extension_model->getActivePlugins();
        
        $active_plugins_names[] = $extension_name; // add current extension to the list of active plugins
        
        // get all active plugins data
        $extensions_tree = $extension_model->getFullExtensionsTree();
        $extensions_tree['themes'] = $extension_model->changeExtensionsKey($extensions_tree['themes'],'Name');
        
		$active_plugins_all_data=array();
        if(array_key_exists('plugins', $extensions_tree))
        {
            if(count($extensions_tree['plugins'])>0)
            {
                
                foreach ( $active_plugins_names as $active_plugin ) {
                    if(!array_key_exists($active_plugin,$extensions_tree['plugins']))
                    {
                        continue;
                    }
                    $active_plugins_all_data[$active_plugin] = $extensions_tree['plugins'][$active_plugin];
                }

                //for($i=0; $i<count($active_plugins_names); $i++)
                //{
                //    if(!array_key_exists($active_plugins_names[$i],$extensions_tree['plugins']))
                //    {
                //        continue;
                //    }
                //    
                //    $active_plugins_all_data[$active_plugins_names[$i]] = $extensions_tree['plugins'][$active_plugins_names[$i]];
                //}
            }
        }       
        
        if(count($active_plugins_all_data)>0)
        {  
            foreach($active_plugins_all_data AS $plugin_path => $plugin_attr)
            {                
                if(!is_array($plugin_attr))
                {
                    continue;
                }
                
                $version = '';
                if(array_key_exists('Version', $plugin_attr))
                {
                    $version = $plugin_attr['Version'];
                }

                $name = '';
                if(array_key_exists('Name', $plugin_attr))
                {
                    $name = $plugin_attr['Name'];
                }

                $slug = '';
                if(array_key_exists('extension_slug', $plugin_attr))
                {
                    $slug = $plugin_attr['extension_slug'];
                }

                $repository_url = '';
                if(array_key_exists('repository_endpoint_url', $plugin_attr))
                {
                    $repository_url = $plugin_attr['repository_endpoint_url'];
                }

                $active_plugins[$plugin_path] = array(
                    'name' => $name,
                    'type' => 'plugin',
                    'path' => $plugin_path, 
                    'version' => $version,
                    'slug' => $slug,
                    'repository_url' => $repository_url
                );
            }
        }
        
        //$current_theme = $extension_model->getCurrentTheme();
        $current_theme_name = $extension_model->getCurrentThemeName();

        $current_theme = array();
        if(isset($extensions_tree['themes'][$current_theme_name]))
       {
            $current_theme = $extensions_tree['themes'][$current_theme_name];
        }

        if($extension_name <> $current_theme_name) // current extension is not a theme
        {
            $version = '';
            if(array_key_exists('Version', $current_theme))
            {
                $version = $current_theme['Version']; 
            }

            $slug = '';
            if(array_key_exists('extension_slug', $current_theme))
            {
                $slug = $current_theme['extension_slug'];
            }

            $repository_url = '';
            if(array_key_exists('repository_endpoint_url', $current_theme))
            {
                $repository_url = $current_theme['repository_endpoint_url'];
            }

            if(array_key_exists('Name', $current_theme))
            {
                $active_plugins[$current_theme['Name']] = array(
                    'name' => $current_theme['Name'],
                    'type' => 'theme',
                    'path' => null,
                    'version' => $version,
                    'slug' => $slug,
                    'repository_url' => $repository_url
                );
            }
        }
   
        // save list of extensions and update last activation date
        $edm = WPRC_Loader::getExtensionDataManager('extension', $extension_name);
        
        $extension_data = array(
            'activated_extensions' => $active_plugins, 
            'last_activation_date' => time()
        );
        
        $edm->updateExtensionData($extension_data);
        
        // set timer for extension
        WPRC_Loader::includeExtensionTimer();
        $timer_is_set = WPRC_ExtensionTimer::setTimer($extension_name);
        
        $msg = '';
        if(!$timer_is_set)
        {
            $msg =  'Timer wasn\'t set!<br>';
            return false;
        }
        
        // remove extension from the list of plugins
        $this->removeSentReportsListItem($extension_name);
    }

/**
 * Render debug screen for all extensions
 * 
 * There is no report sending here
 */     
    public function getStatus()
    {
        // ------------------------------------- timers --------------------------------
        // get data of all plugins
        WPRC_Loader::includeExtensionDataManager();
        $all_data = WPRC_ExtensionDataManager::getAllData('extension');
        
        WPRC_Loader::includeExtensionTimer();
        $timers = WPRC_ExtensionTimer::checkTimers();
      
        $sent_reports_list = $this->getSentReportsList(); 
       
        // if there are no timers registered in the system
        if(count($timers) == 0)
        {
            echo '<p align="center">There are no timers registered in the system</p><br>';
        }
        else
        {
            $correct_work_period = WPRC_ExtensionTimer::getDefaultPeriod();  
            
            echo '<style type="text/css">
            /* debug */
            table.debug{font-family: Verdana,Arial,Helvetica,sans-serif;color:#444;border-width: 1px;border-spacing: 2px;border-style: solid;border-color: #EEEEEE;border-collapse: collapse;background-color: #ffffff;}
            table.debug th{border-width: 1px;padding: 5px;border-style: solid;border-color: #EFEFEF;background-color: #FAFAFA;color: #538cc6;font-size:11px;}
            table.debug td{border-width: 1px;padding: 0px;border-style: solid;border-color: #EFEFEF;background-color: #ffffff;padding:5px 5px 3px 5px;font-size:11px;}
            </style>';            
            
            echo '<table border="1" class="debug">
            <tr><th>Notes</th></tr>
            <tr><td>Note to the: \'<em>is report need to be sent?</em>\' column</td></tr>
            <tr><td>Report need to be sent if left side more or equal right side<br>
            (time()-last_activation_date) >= default period<br></td></tr></table><br>';
            
            $table = '<table border="1" class="debug" align="center"> 
            <tr>
                <th>extension</th>
                <th>timer<br>expired</th>
                <th>report<br>already<br>sent</th>
                <th>Do report need to be sent?</th>
                <th>sent extensions OR extensions that will be sent<br><br><small> not crossed out extensions will be sent<br>There are no data (last activation date) for crossed out extensions</small></th>
            </tr>';
            
            foreach($timers AS $extension => $timer_expired)
            {
                $expired = $timer_expired == 1 ? 'YES' : 'NO'; 
                
                if($sent_reports_list) // if such option exists
                { 
                    $report_already_sent = in_array($extension, $sent_reports_list) ? 'YES' : 'NO';
                }
                else
                {
                    $report_already_sent = 'NO';
                }
                
                $last_activation_date = $all_data[$extension]['last_activation_date'];
                
                $time_left_to_send = '';
                if($report_already_sent == 'NO')
                {
                    $time_left_to_send = time() - $last_activation_date.'  >= '.$correct_work_period;
                }
                
                $table .= '<tr>
                    <td>'.$extension.'</td>
                    <td align="center">'.$expired.'</td>
                    <td align="center">'.$report_already_sent.'</td>
                    <td align="center">'.$time_left_to_send.'</td>
                    <td>';
                    
                    if(array_key_exists($extension, $all_data) && $timer_expired)
                    {
                        $activated_extensions = array_keys($all_data[$extension]['activated_extensions']);
                        $table .= '<ol>'; 
                        
                        for($i=0; $i<count($activated_extensions); $i++)
                        {
                            
                            // check last activation dates of extensions in the saved list
                            if(!array_key_exists($activated_extensions[$i], $all_data) || !isset($all_data[$activated_extensions[$i]]) || !array_key_exists('last_activation_date', $all_data[$activated_extensions[$i]]))
                            {
                                $table .= '<li><s>'.$activated_extensions[$i].'</s></li>';
                                continue;
                            }
                            
                            if((time() - $all_data[$activated_extensions[$i]]['last_activation_date']) >= $correct_work_period)
                            {
                                $table .= '<li>'.$activated_extensions[$i].'</li>';
                            }
                            
                        }
                        
                        $table .= '</ol>';
                    }
                    
                    $table .= '</td>
                </tr>';    
            }
            $table .= '</table>';
            
            echo $table;

        }
        
    }
    
/**
 * Prepare report data
 *
 * @param array input data
 */      
    public function prepareRequest(array $input_data)
    {
        // validate input data
        if(!array_key_exists('extension_type',$input_data))
        {
            throw new Exception(__('Please set extension_type','installer'));
            return false;
        }
        
        // set extension type
        $extension_type = $input_data['extension_type'];
        
        // check timers
        WPRC_Loader::includeExtensionTimer();
        $timers = WPRC_ExtensionTimer::checkTimers();
      
        // if there are no timers registered in the system
        if(count($timers) == 0)
        {
            return false;
        }
               
        // get data of all plugins
        WPRC_Loader::includeExtensionDataManager();
        $all_data = WPRC_ExtensionDataManager::getAllData('extension');
        
        // if there are no saved data for plugins
        if(!is_array($all_data))
        { 
            return false;
        }
        
        $report_data = array();
        
        $correct_work_period = WPRC_ExtensionTimer::getDefaultPeriod(); 
        
        // cycle for expired timers
        foreach($timers AS $extension => $timer_expired)
        {
            if($timer_expired)
            {
                // prepare report data --------------------
                
                // get saved list of activated plugins
                if(!array_key_exists($extension, $all_data))
                {
                    continue;
                }
                
                $extension_data = $all_data[$extension];
                
                if(!array_key_exists('activated_extensions', $extension_data) || (count($extension_data) == 0))
                {
                    continue;
                }
                
                // check presence of extension with expired timer in the sent reports list (was extension sent?)
                if($this->isSentReportsListItemExists($extension))
                {
                    continue;
                }
   
                // cycle for activated plugins of each plugin with expired timer                
                if(count($extension_data['activated_extensions'])>0)
                {
                    foreach($extension_data['activated_extensions'] AS $activated_extension => $activated_extension_data)
                    {
                        // check last activation dates of extensions in the saved list
                        /*if(!isset($all_data[$activated_extension]) || !isset($extension_data['activated_extensions']) || !array_key_exists('last_activation_date', $all_data[$activated_extension]))
                        {
                            continue;
                        }*/
                       
                        if (isset($all_data[$activated_extension]) && isset($all_data[$activated_extension]['last_activation_date']))
							$last_activation_date = $all_data[$activated_extension]['last_activation_date'];
						else if (is_array($activated_extension_data))
						// include activated extensions even thougb they were not activated through extension manager
						// since obviously they work together
							$last_activation_date=time()-$correct_work_period;
                        else
                            continue;
                        
						// add extension to the report data
                        if((time() - $last_activation_date) >= $correct_work_period)
                        {
                            if (!isset($report_data[$extension]))
								$report_data[$extension]=array();
                            if (!isset($report_data[$extension]['activated_extensions']))
								$report_data[$extension]['activated_extensions']=array();
								
							$report_data[$extension]['activated_extensions'][$activated_extension] = $activated_extension_data;
                        }
    
                    }
                }
                
                if(count($report_data) == 0)
                { 
                    continue;
                }
            }
        } // end of timers FOREACH
        
        // form array for the formRequestStructure method
        $raw_report_data = array(
            'reports' => $report_data
        );

        // form report structure
        $report = $this->formRequestStructure($raw_report_data);
        
		//file_put_contents(WPRC_PLUGIN_PATH.'/debug_correct.txt',print_r($report,true),FILE_APPEND);
        return $report;
    }

/**
 * Form the structure of the report
 * 
 * @param array report data array
 */     
    public function formRequestStructure(array $report_data)
    {
        $site_info = $this->getSiteInfo();
        
         $report = array(
            'action' => 'correct_work_report',
            'request' => array(
                'site' => $site_info,
                'reports' => $report_data['reports']
                )
        );
        
        return $report;
    }

/**
 * Get sent reports list
 */ 
    public function getSentReportsList()
    {
        $option_name = $this->getSentReportsListOptionName();
        return get_option($option_name);  
    }

/**
 * Update sent reports list
 * 
 * @param array sent reports list
 */     
    private function updateSentReportsList($sent_reports_list)
    {
        $option_name = $this->getSentReportsListOptionName();
        return update_option($option_name, $sent_reports_list);          
    }
    
/**
 * Add extension to the sent report list
 * 
 * @param string extension name
 * 
 * @return bool result of operation
 */     
    public function addSentReportsListItem($extension)
    {
        if($extension=='')
        {
            return false;    
        }

        $sent_reports_list = $this->getSentReportsList();
        
        if(!$sent_reports_list)
        {
            $sent_reports_list = array($extension); 
        }
        else
        {
            if(!$this->isSentReportsListItemExists($extension))
            {
                array_push($sent_reports_list, $extension);
            }
        }
        
        return $this->updateSentReportsList($sent_reports_list);
    }

/**
 * Add extension to the sent report list
 * 
 * @param array array of the extension names
 * 
 * @return bool result of operation
 */     
    public function addSentReportsListItems(array $extensions)
    {
        if(!is_array($extensions))
        {
            return false;    
        }
        
        for($i=0; $i<count($extensions); $i++)
        {
            $this->addSentReportsListItem($extensions[$i]);
        }
        
        return true;
    }
    
/**
 * Remove extension from the sent reports list
 * 
 * @param string extension name
 * 
 * @return bool result of operation
 */     
    public function removeSentReportsListItem($extension)
    {
        $sent_reports_list = $this->getSentReportsList();

        if(!$sent_reports_list)
        {
            return false;
        }

        foreach($sent_reports_list AS $key => $item)
        {
            if($item == $extension)
            { 
                unset($sent_reports_list[$key]);   
                break;
            }
        }

        return $this->updateSentReportsList($sent_reports_list);
    }

/**
 * Check existance of extension in the sent reports list
 * 
 * @param string extension name
 *
 * @return bool result of checking
 */      
    public function isSentReportsListItemExists($extension)
    {
        $sent_reports_list = $this->getSentReportsList();
        
        if(!$sent_reports_list)
        {
            return false;
        }
        
        return in_array($extension, $sent_reports_list);
    }

/**
 * Reset theme timer
 * 
 * This method should be called on 'switch_theme' hook
 */ 
    public function resetThemeTimer()
    {
        $extension_model = WPRC_Loader::getModel('extensions');
        $swithed_theme = $extension_model->getSwitchedThemeName();
        
        WPRC_Loader::includeExtensionTimer();
        WPRC_ExtensionTimer::deleteTimer($swithed_theme);
    }
    
/**
 * Reset plugin timer
 * 
 * This method should be called on 'deactivate_plugin' hook
 * 
 * @param string plugin name
 */ 
    public function resetPluginTimer($plugin_name)
    {
        WPRC_Loader::includeExtensionTimer();
        WPRC_ExtensionTimer::deleteTimer($plugin_name);
    }
}
?>