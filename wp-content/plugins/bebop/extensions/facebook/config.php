<?php

/*Config file for an extension*/

function get_facebook_config() {
	$config = array(
		'name' 						=> 'facebook',
		'display_name'				=> 'Facebook',
		'pages'						=> array( 'settings', ),
		'defaultpage'				=> 'settings',
		'request_token_url' 		=> 'https://www.facebook.com/dialog/oauth?client_id=APP_ID&redirect_uri=REDIRECT_URI&state=STATE',
		'access_token_url' 			=> 'https://graph.facebook.com/oauth/access_token?client_id=APP_ID&redirect_uri=REDIRECT_URI&client_secret=APP_SECRET&code=CODE',
		'extend_access_token_url'	=> 'https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id=APP_ID&client_secret=APP_SECRET&fb_exchange_token=SHORT_TOKEN',
		'people_data'				=> 'https://graph.facebook.com/',
		'data_feed' 				=> 'https://graph.facebook.com/me/feed',
		'content_type' 				=> __( 'Facebook post', 'bebop' ),
		'content_oembed' 			=> false,
	);
	return $config;
}
?>