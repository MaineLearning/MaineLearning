<?php
/* 
 * This is a helper class designed to be used when oauth is not being used, but data is still retrieved from an API.
 * This class builtd the query string, and sends off the results using the CURL method. data ais then returned to the importer file.
 */
 
class bebop_data {
	function set_parameters ( $params ) {
		$this->paramaters = $params;
	}
	function get_parameters () {
		return $this->paramaters;
	}
	
	function build_query ( $url ) {
		return $url . '?' . http_build_query( $this->get_parameters() );
	}
	
	function execute_request ( $url ) {
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $curl, CURLOPT_HEADER, FALSE );
		//$_GET
		curl_setopt( $curl, CURLOPT_HTTPGET, TRUE );

		curl_setopt( $curl, CURLOPT_URL, $url );
		$response = curl_exec( $curl );
		curl_close( $curl );
		return $response;
	}
}
?>