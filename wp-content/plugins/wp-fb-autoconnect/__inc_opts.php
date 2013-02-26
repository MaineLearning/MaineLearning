<?php

//General Info
global $jfb_name, $jfb_version, $jfb_homepage;
$jfb_name       = "WP-FB AutoConnect";
$jfb_version    = "2.5.11";
$jfb_homepage   = "http://www.justin-klein.com/projects/wp-fb-autoconnect";
$jfb_data_url   = plugins_url(dirname(plugin_basename(__FILE__)));


//Database options
//Note: Premium options are included by the addon itself, if present.
global $opt_jfb_app_id, $opt_jfb_api_key, $opt_jfb_api_sec, $opt_jfb_email_to, $opt_jfb_email_logs, $opt_jfb_delay_redir, $opt_jfb_ask_perms, $opt_jfb_ask_stream, $opt_jfb_stream_content;
global $opt_jfb_mod_done, $opt_jfb_valid, $opt_jfb_app_token;
global $opt_jfb_bp_avatars, $opt_jfb_wp_avatars, $opt_jfb_fulllogerr, $opt_jfb_disablenonce, $opt_jfb_show_credit;
global $opt_jfb_username_style, $opt_jfb_invalids;
global $opt_jfb_logincount, $opt_jfb_logincount_recent;
$opt_jfb_app_id     = "jfb_app_id";
$opt_jfb_api_key    = "jfb_api_key";
$opt_jfb_api_sec    = "jfb_api_sec";
$opt_jfb_email_to   = "jfb_email_to";
$opt_jfb_email_logs = "jfb_email_logs";
$opt_jfb_delay_redir= "jfb_delay_redirect";
$opt_jfb_ask_perms  = "jfb_ask_permissions";
$opt_jfb_ask_stream = "jfb_ask_stream";
$opt_jfb_stream_content = "jfb_stream_content";
$opt_jfb_mod_done   = "jfb_modrewrite_done";
$opt_jfb_valid      = "jfb_session_valid";
$opt_jfb_app_token  = 'jfb_app_token';
$opt_jfb_fulllogerr = "jfb_full_log_on_error";
$opt_jfb_disablenonce="jfb_disablenonce";
$opt_jfb_bp_avatars = "jfb_bp_avatars";
$opt_jfb_wp_avatars = "jfb_wp_avatars";
$opt_jfb_show_credit= "jfb_credit";
$opt_jfb_username_style = "jfb_username_style"; 
$opt_jfb_hidesponsor = "jfb_hidesponsor";
$opt_jfb_logincount = "jfb_logincount";
$opt_jfb_logincount_recent = "jfb_logincount_recent";
$opt_jfb_invalids = "jfb_invalids";

//Shouldn't ever need to change these
global $jfb_nonce_name, $jfb_uid_meta_name, $jfb_js_callbackfunc, $jfb_default_email;
$jfb_nonce_name     = "autoconnect_nonce";
$jfb_uid_meta_name  = "facebook_uid";
$jfb_js_callbackfunc= "jfb_js_login_callback";
$jfb_default_email  = '@unknown.com';

//List to remember how many times we've called jfb_output_facebook_callback(), preventing duplicates
$jfb_callback_list = array(); 


//A wrapper function to GET data from the Facebook Graph API
function jfb_api_get($url)
{
    return jfb_api_process( wp_remote_get($url, array( 'sslverify' => false )) );
}

//A wrapper function to POST data to the Facebook Graph API
function jfb_api_post($url)
{
    return jfb_api_process( wp_remote_post($url, array( 'sslverify' => false )) );
}

//Process the result of GETting or POSTing the Graph API
function jfb_api_process($result)
{   
    //In some situations, Wordpress may unexpectedly return WP_Error.  If so, I'll create a Facebook-style error array
    //so my Facebook-style error handling will pick it up without special cases everywhere.
    if(is_wp_error($result))
    {
        $errResult = array();
        $errResult['error']['message'] = "wp_remote_get() failed!";
        if( method_exists($result, 'get_error_message')) $errResult['error']['message'] .= " Message: " . $result->get_error_message();
        return $errResult;
    }
    
    //Otherwise, decode the JSON text provided by Facebook into a PHP object.
    return json_decode($result['body'], true);
}


//Error reporting function
function j_die($msg)
{
    j_mail("FB Login Error on " . get_bloginfo('name'), $msg);
    global $jfb_log, $opt_jfb_fulllogerr;
    if( isset($jfb_log) && get_option($opt_jfb_fulllogerr) )
        $msg .= "<pre>---LOG:---\n" . $jfb_log . "</pre>";
    die($msg);
}

/*
 * Log reporting function: If enabled, email a detailed log to the site admin
 */
function j_mail($subj, $msg='')
{
    global $opt_jfb_email_to, $opt_jfb_email_logs, $jfb_log;
	global $jfb_debug_array;
    if( get_option($opt_jfb_email_logs) && get_option($opt_jfb_email_to) )
    {
        if( $msg )            $msg .= "\n\n";
        if( isset($jfb_log) ) $msg .= "---LOG:---\n" . $jfb_log;
		
		jfb_debug_checkpoint('final');
		$count = count($jfb_debug_array);
		$keys = array_keys($jfb_debug_array);
		
		$msg .= "\n---TIME:---\n";
		for($i=0; $i<$count; $i++)
		{
			if($i==0) $msg .= sprintf("%-9s", $keys[$i]) . ") +0s\n";
			else 	  $msg .= sprintf("%-9s", $keys[$i]) . ") +" . round($jfb_debug_array[$keys[$i]]['time']-$jfb_debug_array[$keys[$i-1]]['time'],2) . "s\n";
		}
		$msg .= "TOTAL    ) " . round($jfb_debug_array[$keys[$count-1]]['time']-$jfb_debug_array[$keys[0]]['time'],2) . "s\n";
		
		$msg .= "\n---MEMORY:---\n";
		for($i=0; $i<$count; $i++)
		{
			$value = $jfb_debug_array[$keys[$i]]['mem'];
			if($i==0) $msg .= sprintf("%-9s", $keys[$i]) . ") " . round( $value / (1024*1024), 2) . "M\n";
			else      $msg .= sprintf("%-9s", $keys[$i]) . ") " . round( $value / (1024*1024), 2) . "M (+".round(($value-$jfb_debug_array[$keys[$i-1]]['mem'])/(1024*1024),2)."M)\n";
		}
		$msg .= "LIMIT    ) " . ini_get('memory_limit') . "\n";
		
		$msg .= "\n---USER AGENT:---\n" . $_SERVER['HTTP_USER_AGENT'] . "\n";
        $msg .= "\n---REQUEST:---\n" . print_r($_REQUEST, true);
        wp_mail(get_option($opt_jfb_email_to), $subj, $msg);
    }
}


/**
  * A function for debuging time/memory usage at various points in script execution.
  * Calling this function (with a label) will add a "checkpoint."  All checkpoints will be
  * included in the final log sent to the admin by j_mail 
  */
function jfb_debug_checkpoint($label)
{
	global $jfb_debug_array;
	if(!is_array($jfb_debug_array)) $jfb_debug_array = array();
    $time = explode (' ',microtime()); 
    $time = (double)($time[0] + $time[1]);
	$jfb_debug_array[$label] = array('time'=>$time, 'mem'=>memory_get_usage()); 
}


/**
 * Test if this has the "Premium" features
 */
function jfb_premium()
{
    return defined('JFB_PREMIUM');
}


/**
 * Simple browser detection, for logging (from http://php.net/manual/en/function.get-browser.php)
 * (Doesn't require browscap.ini to be installed on the server, like standard PHP get_browser())
 */
function jfb_get_browser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $shortname = 'Unknown';
    $platform = 'Unknown';
    $version= "";
	
	//Get platform
	if     (preg_match('/android/i', $u_agent))                                                 $platform = 'Android';	//Must come BEFORE 'linux'
	elseif (preg_match('/linux/i', $u_agent))                                                   $platform = 'Linux';
    elseif (preg_match('/iphone/i',$u_agent))                                                   $platform = 'iPhone'; 	//Must come BEFORE 'mac'
	elseif (preg_match('/ipad/i',$u_agent))                                                     $platform = 'iPad';		//Must come BEFORE 'mac'
    elseif (preg_match('/macintosh|mac os x/i', $u_agent) && !preg_match('/iPhone/i',$u_agent)) $platform = 'Mac';
    elseif (preg_match('/windows|win32/i', $u_agent))                                           $platform = 'Windows';
	
	//Get name and shortname
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {$bname = 'Internet Explorer'; $shortname = "MSIE"; }
    elseif(preg_match('/Firefox/i',$u_agent))                              {$bname = 'Mozilla Firefox'; $shortname = "Firefox"; }
    elseif(preg_match('/Chrome/i',$u_agent))                               {$bname = 'Google Chrome'; $shortname = "Chrome"; }
    elseif(preg_match('/Safari/i',$u_agent))                               {$bname = 'Apple Safari'; $shortname = "Safari"; }
    elseif(preg_match('/Opera/i',$u_agent))                                {$bname = 'Opera'; $shortname = "Opera"; }
    elseif(preg_match('/Netscape/i',$u_agent))                             {$bname = 'Netscape'; $shortname = "Netscape"; }

	//Get version
    $known = array('Version', $shortname, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    @preg_match_all($pattern, $u_agent, $matches);
    $i = count($matches['browser']);
    if ($i != 1 && strripos($u_agent,"Version") < strripos($u_agent,$shortname))$version= $matches['version'][0]; 
    else if($i != 1)                                                       		$version= $matches['version'][1];
    else                                                                   		$version= $matches['version'][0];
    if ($version==null || $version=="") {$version="?";}
	
	//Done - return!
    return array('name'=>$bname, 'shortname'=>$shortname, 'version'=>$version, 'platform'=>$platform );
} 

?>