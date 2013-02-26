<?php

/**
 * Manage extensions usage data
 */
class WPRC_UsageReporter extends WPRC_Requester {

	/**
	 * Send report to the server
	 * 
	 * @param array report
	 */ 
	public function sendRequest( array $report ) {

		// validate input data
        if(!array_key_exists('request', $report))
        {
            return false;
        }
        
        if(!array_key_exists('reports', $report['request']))
        { 
            return false;
        }

        $response = false;
        $response = $this->RepositoryConnector->sendWPRCRequest('post', $report, 0);

		return $response;

	}


	/**
	 * Prepare report data
	 *
	 * @param array input data
	 */    
	public function prepareRequest( array $input_data ) {

		$month_day = get_option('wprc_usage_sending_month_day');
		$send = false;

		if ( ! $month_day ) {
			// Choose one day of the month between 2 and 27.
			// We'll not send usage reports out of those days
			$month_day = str_pad( rand(2,27), 2, '0', STR_PAD_LEFT );
			update_option( 'wprc_usage_sending_month_day', $month_day );

			$current_day = date_i18n( 'd' );
			if ( strcmp( $current_day, $month_day ) === -1  ) {
				// If month day < today we'll send the report later on, but this same month
				$next_report_date = date_i18n( 'Ym' . $month_day, current_time( 'timestamp' ) );
				set_transient( 'wprc_usage_reports', $next_report_date );
			}
			else {
				$send = true;
			}

		}

		$today = date_i18n( 'Ymd', current_time( 'timestamp' ) );

		$transient = get_transient( 'wprc_usage_reports' );

		if ( ! $transient || strcmp( $today, $transient ) >= 0 ) {
			$next_report_date = date_i18n( 'Ym' . $month_day, strtotime( 'first day of next month', current_time( 'timestamp' ) ) );
			set_transient( 'wprc_usage_reports', $next_report_date );
			$send = true;
		}

		
		
		if ( $send ) {

			// get data of all plugins
	        WPRC_Loader::includeExtensionDataManager();
	        $all_data = WPRC_ExtensionDataManager::getAllData('extension');

	        $value = array();
	        $report = $this -> search_key( $all_data, 'activated_extensions' );

	        $site_info = $this -> getSiteInfo( true );
        	$site_url = $site_info['site_url'];		

			$raw_report_data = array(
	            'reports' => $report
	        );

			if ( ! is_array($report) )
				return false;

	        $report = $this->formRequestStructure($raw_report_data);

	        return $report;


		}

		return false;
		

	}



	/**
	 * Form the structure of the report
	 * 
	 * @param array report data array
	 */   
	public function formRequestStructure( array $report_data ) {

		$site_id = get_option( 'wprc_site_id' );
        return $report = array(
	        'action' => 'usage_report',
	        'request' => array(
	        	'site_id' => $site_id,
	            'reports' => $report_data['reports']
	            )
        );
        
	}

	/**
	 * Searches for a key in an array and returns that array[key]
	 */
	private function search_key($array, $key )
	{
	    $results = array();

	    if (is_array($array))
	    {
	        if (isset($array[$key])) {
	            $results = $array[$key];
	        }

	        foreach ($array as $subarray)
	            $results = array_merge($results, $this -> search_key( $subarray, $key ));
	    }

	    return $results;
	}


}




?>