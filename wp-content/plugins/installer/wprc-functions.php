<?php
/**
 * Functions Class
 * 
 * Manages Encryption/Decryption with key
 * 
 * 
 */
class WPRC_Functions
{
	public static function sanitizeURL($url,$scheme=null)
	{
		if ($scheme==null)
			$scheme=array('http','https');
			
		$url=strip_tags($url);
		$p=parse_url($url);
		$p['scheme']=strtolower($p['scheme']);
		if (in_array($p['scheme'],$scheme))
			return $url;
		return '';
	}
	
	public static function sanitizeTEXT($text)
	{
		return strip_tags($text,"<p><br><strong>");
	}
	
	public static function formatMessage($message,$withIcon=true)
	{
		if (isset($message) && $message!='')
		{
			$text='';
			if (isset($message->text))
				$text=self::sanitizeTEXT($message->text).' ';
			$action='';
			if (isset($message->action))
				$action=self::sanitizeTEXT($message->action);
			$url='';
			if (isset($message->url))
				$url=self::sanitizeURL($message->url);
			$icon='';
			if ($withIcon)
			{
				if (isset($message->type))
				{
					switch($message->type)
					{
						case 'notice':
						case 'bugfix':
									$icon='<span class="wprc-message-icon wprc-notify-icon"></span>';
									break;
						case 'security':
									$icon='<span class="wprc-message-icon wprc-security-icon"></span>';
									break;
					}
				}
			}
			$output=$icon.$text."<a href='$url'>$action</a>";
			return $output;
		}
		return '';
	}
    
    public static function log($message,$type=null,$file=null)
    {
        // debug levels
        $dlevels=array(
            'default' => defined('WPRC_DEBUG') && WPRC_DEBUG,
            'api' => defined('WPRC_DEBUG_API_REQUESTS') && WPRC_DEBUG_API_REQUESTS,
            'server' => defined('WPRC_DEBUG_SERVER_REQUESTS') && WPRC_DEBUG_SERVER_REQUESTS,
            'controller' => defined('WPRC_DEBUG_CONTROLLERS') && WPRC_DEBUG_CONTROLLERS
        );

        // check if we need to log..
        if (!$dlevels['default']) return false;
        if ($type==null) $type='default';
        if (!isset($dlevels[$type]) || !$dlevels[$type]) return false;
        
        // full path to log file
        if ($file==null)
        {
            $file='debug.log';
        }
        $file=WPRC_LOGS_DIR.DIRECTORY_SEPARATOR.$file;

        /* backtrace */
		$bTrace = debug_backtrace(); // assoc array
	
        /* Build the string containing the complete log line. */
		$line = PHP_EOL.sprintf('[%s, <%s>, (%d)]==> %s', 
								date("Y/m/d h:i:s", mktime()),
                                basename($bTrace[0]['file']), 
								$bTrace[0]['line'], 
								$message );
        
		// log to file
        file_put_contents($file,$line,FILE_APPEND);
        
        return true;
    }
}
?>