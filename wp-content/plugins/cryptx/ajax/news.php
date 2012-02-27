<?php	
if( !class_exists( 'WP_Http' ) )
    include_once( ABSPATH . WPINC. '/class-http.php' );
    
    
    $request = new WP_Http;
	$result = $request->request( 'http://weber-nrw.de/category/wordpress/plugins/cryptx/' );

	if($result['response']['code'] != 200) exit;
	preg_match_all('/<h1>(.*?)<\/h1>/i', $result['body'], $news);
	$latest = implode("</li><li>", array_slice($news[1], 0, 3));
	if ( $latest ) { 
		echo  '<ul style="list-style: disc; padding-left:20px;"><li>'.$latest.'</li></ul>';
	}
?>