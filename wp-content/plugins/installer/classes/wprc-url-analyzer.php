<?php
class WPRC_UrlAnalyzer
{
    
/**
 * Parse query string from the specified url and return associative array of url parameters
 * 
 * @param string url
 * 
 * @return array associative array of url parameters
 */     
    public static function getUrlParams($url)
    {
        $url = htmlspecialchars_decode($url);
        $url_arr = parse_url($url); 
        if (isset($url_arr['query']))
		{
			parse_str($url_arr['query'], $url_params);
			
			return $url_params;
		}
		else return array();
    }
    
/** 
 * Return extension data from the url
 * 
 * @param string url
 * 
 * @return array associative array of extension data
 */ 
    public static function getExtensionFromUrl($url)
    {
        $url_params = self::getUrlParams($url);
        
        $extension_name='';
        $extension_type='';
        $extension_action='';
        
        if(array_key_exists('plugin',$url_params))
        {
            $extension_type = 'plugin';
            $extension_name = $url_params['plugin'];
        }
        
        if(array_key_exists('template',$url_params) && array_key_exists('stylesheet',$url_params) )
        {
            $extension_type = 'theme';
            $extension_name = $url_params['template'];
        }
        
        if(array_key_exists('action',$url_params))
        {
            $extension_action = $url_params['action'];
        }
        
        $extension = array(
            'name' => $extension_name, 
            'type' => $extension_type,
            'action' => $extension_action
            );
            
        return $extension;
    }
    
 /**
  * Get parameter from url by key
  * 
  * @param string url
  * @param string parameter key
  * 
  * @param string value of paramater
  */ 
    public static function getUrlParamByKey($url,$key)
    {
        $url_params = self::getUrlParams($url);

        if(!is_array($url_params) || !array_key_exists($key, $url_params))
        {
            return false;
        }
        
        return $url_params[$key];
    }

/**
 * Get file name (without filename extension) of the file from the specified url
 * 
 * @param string url
 */     
    public static function getUrlFile($url)
    {
        preg_match('/\/([^\/]*)\.php/', $url, $matches);
        
        return $matches[1];
    }
}
?>