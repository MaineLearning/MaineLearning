<?php

/*Config file for an extension*/

function get_twitter_config() {
	$config = array(
		'name' 					=> 'twitter',
		'display_name'			=> 'Twitter',
		'pages'					=> array( 'settings', ),
		'defaultpage'			=> 'settings',
		'request_token_url' 	=> 'http://api.twitter.com/oauth/request_token',
		'access_token_url' 		=> 'http://api.twitter.com/oauth/access_token',
		'authorize_url'			=> 'https://api.twitter.com/oauth/authorize',
		'data_feed' 			=> 'http://api.twitter.com/1/statuses/user_timeline.xml',
		'content_type' 			=> __( 'Tweet', 'bebop' ),
		'content_oembed' 		=> false,
		'action_link' 			=> 'http://www.twitter.com/bebop_replace_username/status/',
	);
	return $config;
}
?>
