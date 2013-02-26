<?php
class WPRC_RepositoryConnector
{


    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_RepositoryConnector();
        return self::$instance;
    }

/**
 * Class constructor
 * 
 */     
    public function __construct()
    {

    }

/**
 * Send request to the WPRC server only with retries
 * 
 * @param string method
 * @param mixed arguments to send
 */ 
    public function sendWPRCRequestWithRetries($method,$args,$timeout=5)
    {
        $url = WPRC_SERVER_URL;
        
		$send_result = false;
		$failed=0;
		
		$timer=get_transient('wprc_report_failed_timer');
		if ($timer!=false && $timer!='' && $timer!=null)
			$timer=intval($timer);
		else
			$timer=0;
		
		$timenow=time();
		
		if ($timer-$timenow>0) return false; // discard report
		
		while ($send_result===false && $failed<2) //retry
		{
			$send_result = $this->sendRequest($method,$url,$args,$timeout);
		
			if ($send_result===false)
			{
				$failed++;
				if ($failed<2)
					usleep(rand(100,300)); // wait 1 to 3 seconds
			}
		}
		if ($send_result===false)
		{
			set_transient('wprc_report_failed_timer',time()+5*60*60);
		}
		else
		{
			// reset flags
			set_transient('wprc_report_failed_timer',0);
		}
		return $send_result;
    }

	/**
 * Send request to the WPRC server only
 * 
 * @param string method
 * @param mixed arguments to send
 */ 
    public function sendWPRCRequest($method,$args,$timeout=15)
    {
        $url = WPRC_SERVER_URL;
        
        return $this->sendRequest($method,$url,$args,$timeout);
    }
    
/**
 * Send request to any file
 * 
 * @param string method
 * @param string url to send
 * @param mixed arguments to send
 */ 
    public function sendRequest($method,$url,$args,$timeout=15)
    {
        WPRC_Loader::includeWordpressHttp();
        
        if (isset($args['request']))
		{
			$body_array = array(
				'action' => $args['action'],
				'request' => serialize($args['request'])
			);
		}
		else
		{
			$body_array = array(
				'action' => $args['action'],
			);
        }
        
        // log
        $debug_msg = sprintf('SERVER REQUEST, method: %s, timeout: %d, uri: %s, args: %s',$method,$timeout,$url,print_r($args, true));
        WPRC_Functions::log($debug_msg,'server','server.log');
        
        switch($method)
        {
            case 'post':
                $request = wp_remote_post($url, array( 'timeout' => $timeout, 'body' => $body_array) );
  
                // log
                $debug_msg = sprintf('SERVER REQUEST, response: %s',print_r($request, true));
                WPRC_Functions::log($debug_msg,'server','server.log');
				
                if ( is_wp_error( $request ) || 200 != wp_remote_retrieve_response_code( $request ) )
				{
                    // log
                     if ( is_wp_error( $request ))
                    $debug_msg = sprintf('SERVER REQUEST, response error: %s',print_r($request->get_error_message(), true));
                    else
                    $debug_msg = sprintf('SERVER REQUEST, response error code: %s',print_r(wp_remote_retrieve_response_code( $request ), true));
                    WPRC_Functions::log($debug_msg,'server','server.log');
                    
            	   // connection failed
				   return false;
                }
				$res = @unserialize( wp_remote_retrieve_body( $request ) );
               	if ( false === $res )
                {
            	   $res = new WP_Error('repository_connector_error', __('An unknown error occurred.', 'installer'), wp_remote_retrieve_body( $request ) );
                    // log
                    $debug_msg = sprintf('SERVER REQUEST, response unknown error: %s',print_r(wp_remote_retrieve_body( $request ), true));
                    WPRC_Functions::log($debug_msg,'server','server.log');
                }
                break;
        }

        return $res;
    }
}
?>