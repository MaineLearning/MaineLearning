<?php

/*Config file for an extension*/

function get_flickr_config() {
	$config = array(
		'name' 					=> 'flickr',
		'display_name'			=> 'Flickr',
		'pages'					=> array( 'settings', ),
		'defaultpage'			=> 'settings',
		'data_feed' 			=> 'http://api.flickr.com/services/rest/',
		'content_type' 			=> 'Flickr photo',
		'content_oembed' 		=> true,
		'action_link' 		=> 'http://www.flickr.com/photos/',
	);
	return $config;
}

?>
