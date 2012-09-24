<?php

/*Config file for an extension*/

function get_vimeo_config() {
	$config = array(
		'name' 					=> 'vimeo',
		'display_name'			=> 'Vimeo',
		'pages'					=> array( 'settings', ),
		'defaultpage'			=> 'settings',
		'data_feed' 			=> 'http://vimeo.com/api/v2/',
		'content_type' 			=> 'Vimeo video',
		'content_oembed' 		=> true,
	);
	return $config;
}
?>
