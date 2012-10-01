<?php

/*Config file for an extension*/

function get_rss_config() {
	$config = array(
		'name' 				=> 'rss',
		'display_name'		=> 'RSS',
		'type'				=> 'rss',
		'pages'				=> array( 'settings', ),
		'defaultpage'		=> 'settings',
		'content_oembed'	=> false,
		'content_type' 			=> __( 'RSS link', 'bebop' ),
	);
	return $config;
}
?>