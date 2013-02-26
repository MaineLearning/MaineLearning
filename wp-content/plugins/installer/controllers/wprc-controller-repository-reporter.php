<?php
class WPRC_Controller_RepositoryReporter extends WPRC_Controller
{
    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Controller_RepositoryReporter();
        return self::$instance;
    }
	
    public function sendUninstallReport($get, $post)
    {
        if ( isset( $post['uninstall_no_more_reports'] ) ) {
            $model_settings = WPRC_Loader::getModel('settings');
            $model_settings -> save( array() );
        }


        $msg=sprintf('Repository Reporter uninstall report enter');
        WPRC_Functions::log($msg,'controller','controller.log');
        
        //$nonce=$post['_wpnonce'];
        if (!array_key_exists('_wpnonce',$post) || !wp_verify_nonce($post['_wpnonce'], 'installer-deactivation-form') ) die("Security check");
        
        $reporter = WPRC_Loader::getRequester('uninstall-reporter');
                    
        $uninstall_reason_report = $reporter->prepareRequest($post); 
        
        if($uninstall_reason_report)
        {
            $reporter->sendRequest($uninstall_reason_report);
        } 
        
        unset($reporter);
        
        self::send_correct_work_report();

		// redirect to the deactivation page ---------------------------------------
        //$this->redirectToDeactivationPage();
        $msg=sprintf('Repository Reporter uninstall report complete');
        WPRC_Functions::log($msg,'controller','controller.log');
        exit;
    }

    private static function send_correct_work_report() {

        $extension_type = 'extension';
        $reporter = WPRC_Loader::getRequester('correct-work-reporter');
         
        $data = array(
            'extension_type' => $extension_type
        );
       
        try
        { 
            $report = $reporter->prepareRequest($data);

            if($report)
            {
                $res = $reporter->sendRequest($report);
            }
        }
        catch(Exception $e)
        {
            WPRC_AdminNotifier::addInstantMessage('<strong>WPRC_CorrectWorkReporter '.__('Error','installer').'</strong>: '.$e->getMessage(),'error');
        }

    }
    
    private function redirectToDeactivationPage()
    {
        $url = $_SERVER['HTTP_REFERER'];
                                 
        if (isset($_POST['bulk_deactivate']) && $_POST['bulk_deactivate']=='1')
		{
			header("location: $url");
		}
		else
		{
			WPRC_Loader::includeUrlAnalyzer();
			$params = WPRC_UrlAnalyzer::getExtensionFromUrl($url);
							
			if(array_key_exists('action',$params) && array_key_exists('type',$params))
			{
				if(($params['action'] == 'deactivate' || ($params['action'] == 'activate' && $params['type'] == 'theme')) && $params['type'] <> '')
				{
					header("location: $url&reported=true");
				}
			}
		}
    }    
    
    public function skipUninstallReport($get, $post)
    {
        if ( isset( $post['uninstall_no_more_reports'] ) ) {
            $model_settings = WPRC_Loader::getModel('settings');
            $model_settings -> save( array() );
        }
        
        // redirect to the deactivation page 
        $this->redirectToDeactivationPage();
    }
 // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<  TEST CALL <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< //
    /*public function testCall($get, $post)
    {
        echo 'Test<hr>';

        $model = WPRC_Loader::getModel('extensions');
        $tree = $model->getFullExtensionsTree();

        WPRC_Loader::includeDebug();
        //WPRC_Debug::print_r($tree, __FILE__, __LINE__);

       // $extension_path =
    }*/


    public function checkCompatibility($get, $post)
    {
        $msg=sprintf('Repository Reporter check compatibility enter');
        WPRC_Functions::log($msg,'controller','controller.log');
        
        $reporter = WPRC_Loader::getRequester('compatibility-reporter');
        $check_extension_type = $get['extension_type_singular'];
        $check_extension_name = $get['extension_name'];
        $check_extension_repository_url = $get['repository_url'];
        $check_extension_version = $get['extension_version'];
        $check_extension_slug = isset( $get['extension_slug'] ) ? $get['extension_slug'] : '';
        $parameters = array(
            'check_extension_name' => $check_extension_name,
            'check_extension_type' => $check_extension_type,
            'check_extension_repository_url' => $check_extension_repository_url,
            'check_extension_version' => $check_extension_version,
            'check_extension_slug' => $check_extension_slug
        );

        $report = $reporter->prepareRequest($parameters);

        // send request only once even if it fails
		$response = $reporter->sendRequest($report,true);

        // layout
        if (isset($response) && isset($response->body) && is_array($response->body) && array_key_exists('left_extensions',$response->body))
		{
			$left_extensions = $response->body['left_extensions'];

			$right_extensions = array();
			if(is_array($left_extensions) && count($left_extensions)>0)
			{
				$left_extension = array_shift($left_extensions);
				$right_extensions = $left_extension['compatibility_info'];
			}

			$no_compatibility_information = false;
			if(count($right_extensions) == 0)
			{
				$no_compatibility_information = true;
			}

            $version_found = $response->body['version_found'];
		}
		else
			$no_compatibility_information = true;

        // Additional attributes to show later the table in a right way
        $report_extensions = $report['request']['right_extensions']; 
        
        $new_right_extensions = array();
        if (isset($right_extensions) && is_array($right_extensions)) {
            foreach ( $report_extensions as $report_key => $report_extension ) {
                $name = $report_extension['name'];
                $version = $report_extension['version'];
                $extension_counts = 0;
                $last_key = '';
                $first_key = '';
                $works = 0;
                $broken = 0;
                $score = 0;
                foreach( $right_extensions as $key => $right_extension ) {

                    if ( $extension_counts == 0 )
                        $first_key = $key;

                    if ( $right_extension['name'] == $name ) {
                        $extension_counts++;
                        $last_key = $key;
                        $works += $right_extensions[$key]['works'];
                        $broken += $right_extensions[$key]['broken'];
                        $score += $right_extensions[$key]['score'];
                    }

                }
                if ( $extension_counts == 1 ) {
                    $new_right_extensions[$last_key] = $right_extensions[$last_key];
                }
                elseif ( $extension_counts > 1 ) {
                    $right_extensions[$first_key]['hide'] = false;
                    $right_extensions[$first_key]['link'] = true;
                    $new_right_extensions[$first_key] = $right_extensions[$first_key];
                    $new_right_extensions[$report_key.'_total'] = $right_extensions[$last_key];
                    $new_right_extensions[$report_key.'_total']['works'] = $works;
                    $new_right_extensions[$report_key.'_total']['broken'] = $broken;
                    $new_right_extensions[$report_key.'_total']['score'] = number_format( ( $works ) * 100 / ( $works + $broken ), 0);
                    $new_right_extensions[$report_key.'_total']['total'] = true;
                    unset($new_right_extensions[$report_key.'_total']['version']);
                }
            }
        }
        
        require_once(WPRC_TEMPLATES_DIR.'/extension-compatibility-information.tpl.php');


        //WPRC_Loader::includePage('check-compatibility');

        $msg=sprintf('Repository Reporter check compatibility complete');
        WPRC_Functions::log($msg,'controller','controller.log');
   }
// >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> //    
//    public function viewPluginInfo($get, $post)
//    {        
//        WPRC_Loader::includeExtensionDataManager();
//        $all_data = WPRC_ExtensionDataManager::getAllData('extension');
//        
//        if(count($all_data)==0)
//        {
//            return false;
//        }
//        
//        foreach($all_data AS $key => $item)
//        {
//            $all_data[$key]['last_activation_date'] = date('Y-m-d H:i:s', $item['last_activation_date']);
//        }
//        echo '<pre>'; print_r($all_data); echo '</pre>';
//    }
    
//    public function testSentReportsList($get, $post)
//    {
//        $reporter = WPRC_Loader::getRequester('correct-work-reporter');
//        $list = $reporter->getSentReportsList();
//        
//        echo '<pre>BEFORE: '; print_r($list); echo '</pre>';
//        
//        // test actions: 
//        
//        //$result = $reporter->addSentReportsListItem('embed-iframe/embediframe.php');
//        $result = $reporter->isSentReportsListItemExists('embed-iframe/embediframe.php');
//        
//        echo '<br>Operation result: ';
//        if($result)
//        {
//            echo 'TRUE';
//        }
//        else
//        {
//            echo 'FALSE';
//        }
//        
//        // ----------------------
//        $list = $reporter->getSentReportsList();
//        echo '<pre>AFTER: '; print_r($list); echo '</pre>';
//    }

    public function getCorrectWorkReportStatus()
    {
        echo '<h1 align="center">getCorrectWorkReportStatus</h1>';
        
        $cwr = WPRC_Loader::getRequester('correct-work-reporter');
        $cwr->getStatus();
    }
}
?>