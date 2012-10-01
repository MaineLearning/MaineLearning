<?php
/* 
 * This is a helper class designed to be used when oauth is not being used, but data is still retrieved from an API.
 * This class builtd the query string, and sends off the results using the CURL method. data ais then returned to the importer file.
 */
 
class bebop_data {
	
	function execute_request( $url, $parameters = null ) {
		if( isset( $parameters ) ) {
			$url = $url . '?' . http_build_query( $parameters );
		}
		$result = wp_remote_get( $url );
		if ( is_wp_error( $result ) ) {
			echo '<pre>';
			echo $url;
			var_dump($result['body']);
			echo '</pre>';
		}
		return $result['body'];
	}
}
?>