<?php 
abstract class WPRC_Requester
{
    protected $RepositoryConnector = null;
    
    public function __construct()
    {       
       $rc = WPRC_Loader::getRepositoryConnector();
       $this->RepositoryConnector = $rc; 
    }

/**
 * Get site information for the reports
 *
 * @param bool get enviroment information only (without site id)
 */     
    protected function getSiteInfo($get_enviroment_only = false)
    {
        return WPRC_Installer::getSiteInfo($get_enviroment_only); 
    }
    
/**
 * Filter text fields
 * 
 * @param string text to filter
 * 
 * @return string filtered text
 */     
    protected function filterText($text)
    {
        return addslashes(strip_tags($text));
    }
    
/**
 * Prepare report data from the post data
 *
 * @param array raw data
 * 
 * @return array report
 */     
  public abstract function prepareRequest(array $post_data);

/**
 * Send report to the server
 * 
 * @param array report
 */   
  public abstract function sendRequest(array $report);
  
 /**
 * Form uninstall reason report structure
 * 
 * @param array array of report data
 * 
 * @return array associative array of specified format
 */     
    public abstract function formRequestStructure(array $report_data);
}
?>