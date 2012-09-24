<?php

/*Config file for an extension*/

function get_slideshare_config() {
	$config = array(
		'name' 					=> 'slideshare',
		'display_name'			=> 'SlideShare',
		'pages'					=> array( 'settings', ),
		'defaultpage'			=> 'settings',
		'data_feed' 			=> 'http://www.slideshare.net/api/2/get_slideshows_by_user',
		'content_type' 			=> 'SlideShare slideshow',
		'content_oembed' 		=> true,
	);
	return $config;
}
?>
