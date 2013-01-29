<?php

/*
Plugin Name: YouTube API
Description: Check links to YouTube videos using the YouTube API.
Version: 1.1
Author: Janis Elsts

ModuleID: youtube-checker
ModuleCategory: checker
ModuleContext: on-demand
ModuleLazyInit: true
ModuleClassName: blcYouTubeChecker
ModulePriority: 100

ModuleCheckerUrlPattern: @^http://(?:([\w\d]+\.)*youtube\.[^/]+/watch\?.*v=[^/#]|youtu\.be/[^/#\?]+)@i
*/

class blcYouTubeChecker extends blcChecker {
	var $youtube_developer_key = 'AI39si4OM05fWUMbt1g8hBdYPRTGpNbOWVD0-7sKwShqZTOpKigo7Moj1YGk7dMk95-VWB1Iue2aiTNJb655L32-QGM2xq_yVQ';
	var $api_grace_period = 0.3; //How long to wait between YouTube API requests.
	var $last_api_request = 0;   //Timestamp of the last request.
	
	function can_check($url, $parsed){
		return true;
	}
	
	function check($url){
		//Throttle API requests to avoid getting blocked due to quota violation.
		$delta = microtime_float() - $this->last_api_request; 
		if ( $delta < $this->api_grace_period ) {
			usleep(($this->api_grace_period - $delta) * 1000000);
		}
		
		$result = array(
			'final_url' => $url,
			'redirect_count' => 0,
			'timeout' => false,
			'broken' => false,
			'log' => "<em>(Using YouTube API)</em>\n\n",
			'result_hash' => '',
		);
		
		//Extract the video ID from the URL
		$components = @parse_url($url);
		if ( strtolower($components['host']) === 'youtu.be' ) {
			$video_id = trim($components['path'], '/');
		} else {
			parse_str($components['query'], $query);
			$video_id = $query['v'];
		}

		//Fetch video data from the YouTube API
		$api_url = 'http://gdata.youtube.com/feeds/api/videos/' . $video_id . '?key=' . urlencode($this->youtube_developer_key);
		$conf = blc_get_configuration();
		$args = array( 'timeout' => $conf->options['timeout'], );
		
		$start = microtime_float();
		$response = wp_remote_get($api_url, $args);
		$result['request_duration'] = microtime_float() - $start;
		$this->last_api_request = $start;
		
		//Placeholders for video restriction data
		$state_name = $state_reason = '';
		
		//Got anything?
		if ( is_wp_error($response) ){
			$result['log'] .= "Error.\n" . $response->get_error_message();
			//WP doesn't make it easy to distinguish between different internal errors.
        	$result['broken'] = true;
        	$result['http_code'] = 0;
		} else {
			$result['http_code'] = intval($response['response']['code']);
			
			switch($result['http_code']){
				case 404 : //Not found
					$result['log'] .= __('Video Not Found', 'broken-link-checker');
					$result['broken'] = true;
					$result['http_code'] = 0;
					$result['status_text'] = __('Video Not Found', 'broken-link-checker');
					$result['status_code'] = BLC_LINK_STATUS_ERROR;
					break;
					
				case 403 : //Forbidden. Usually means that the video has been removed. Body contains details.
					$result['log'] .= $response['body'];
					$result['broken'] = true;
					$result['http_code'] = 0;
					$result['status_text'] = __('Video Removed', 'broken-link-checker');
					$result['status_code'] = BLC_LINK_STATUS_ERROR;
					break;
					
				case 400 : //Bad request. Usually means that the video ID is incorrect. Body contains details.
					$result['log'] .= $response['body'];
					$result['broken'] = true;
					$result['http_code'] = 0;
					$result['status_text'] = __('Invalid Video ID', 'broken-link-checker');
					$result['status_code'] = BLC_LINK_STATUS_WARNING;
					break;
					
				case 200 : //Video exists, but may be restricted. Check for <yt:state> tags.
					//See http://code.google.com/apis/youtube/2.0/reference.html#youtube_data_api_tag_yt:state
				
					//Can we count on an XML parser being installed? No, probably not.
					//Back to our makeshift tag "parser" we go.
					$state = blcUtility::extract_tags($response['body'], 'yt:state', false);
					if ( empty($state) ){
						//Phew, no restrictions.
						$result['log'] .= __("Video OK", 'broken-link-checker');
						$result['status_text'] = __('OK', 'link status', 'broken-link-checker');
						$result['status_code'] = BLC_LINK_STATUS_OK;
						$result['http_code'] = 0;
					} else {
						
						//Get the state name and code and append them to the log
						$state = reset($state);
						$state_name = $state['attributes']['name'];
						$state_reason = isset($state['attributes']['reasonCode'])?$state['attributes']['reasonCode']:'';
						
						$result['result_hash'] = 'youtube_api|' . $state_name . '|' . $state_reason; 
						
						$result['log'] .= sprintf(
							__('Video status : %s%s', 'broken-link-checker'),
							$state_name,
							$state_reason ? ' ['.$state_reason.']':''
						);
						
						//A couple of restricted states are not that bad
						$state_ok = ($state_name == 'processing') ||    //Video still processing; temporary. 
						            (
									    $state_name == 'restricted' &&  
						                $state_reason == 'limitedSyndication' //Only available in browser
						            );
            			
            			if ( $state_ok ) {
            				$result['broken'] = false;
            				$result['status_text'] = __('OK', 'link status', 'broken-link-checker');
							$result['status_code'] = BLC_LINK_STATUS_OK;
							$result['http_code'] = 0;
            			} else {
            				$result['broken'] = true;
            				$result['status_text'] = __('Video Restricted', 'broken-link-checker');
							$result['status_code'] = BLC_LINK_STATUS_WARNING;
							$result['http_code'] = 0;
            			}
					}
					
					//Add the video title to the log, purely for information.
					//http://code.google.com/apis/youtube/2.0/reference.html#youtube_data_api_tag_media:title
					$title = blcUtility::extract_tags($response['body'], 'media:title', false);
					if ( !empty($title) ){
						$result['log'] .= "\n\nTitle : \"" . $title[0]['contents'] . '"';
					}
					 
					break;
				
				default:
					$result['log'] .= $result['http_code'] . $response['response']['message'];
					$result['log'] .= "\n" . __('Unknown YouTube API response received.'); 
					break;
			}			
		}

		//The hash should contain info about all pieces of data that pertain to determining if the 
		//link is working.  
        $result['result_hash'] = implode('|', array(
        	'youtube',
			$result['http_code'],
			$result['broken']?'broken':'0', 
			$result['timeout']?'timeout':'0',
			$state_name,
			$state_reason
		));
        
        return $result;
	}

}
