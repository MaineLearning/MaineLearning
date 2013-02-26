<?php
class WPRC_SiteInformer extends WPRC_Requester
{    
/**
 * Prepare report data from the post data
 *
 * @param array raw data
 * 
 * @return array report
 */     
    public function prepareRequest(array $post_data)
    {
        return $this->formRequestStructure($post_data);
    }

/**
 * Send report to the server
 * 
 * @param array report
 */   
    public function sendRequest(array $report)
    {
        $response = $this->RepositoryConnector->sendWPRCRequest('post',$report);
        
        return $response;
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
        $site_info = $this->getSiteInfo(true);
        $site_url = $site_info['site_url'];
        
        $report = array(
            'action' => 'get_site_id',
            'request' => array(
                'site_url' => $site_url
            )
        );
        
        return $report;
    }
}
?>