<?php
class WPRC_ExtensionTimer
{
    private static $default_period = WPRC_TIMER_DEFAULT_PERIOD;
    private static $timer_prefix = 'wprc_t_';
    private static $expired_prefix = 'expired_';
	private static $all_timers = 'wprc_timers_all';
	
    private static function getTimerPrefix()
    {
        return self::$timer_prefix;
    }
  
    private static function getExpiredTimerPrefix()
    {
        $prefix = self::getTimerPrefix();
        return $prefix.self::$expired_prefix;
    }
    
    private static function prepareExtensionName($extension_name)
    {
        return $extension_name;
        //return str_replace('/','_',$extension_name);
    }
    
    public static function getDefaultPeriod()
    {
        return self::$default_period;
    }

/**
 * Validate option name
 * 
 * If name of the option is more than 64 symbols method returns FALSE
 * 
 * @param string option name
 */   
    private static function validateOptionName($option_name)
    {
        if(strlen($option_name) > 64) // type of 'option_name' column is varchar(64)
        {
            return false;
        }
        
        return true;
    }
  
 /**
  * Get timer name
  * 
  * @param string extension name
  */    
    private static function getTimerName($extension_name)
    {
        $prefix = self::getTimerPrefix();
        $name = self::prepareExtensionName($extension_name);
        $option_name = $prefix.$name;
        
        if(!self::validateOptionName($option_name))
        {
            return false;
        }
        
        return $option_name;
		//return $extension_name;
    }
    
    private static function getExpiredOptionName($extension_name)
    {
        $prefix = self::getTimerPrefix();
        
        $name = self::prepareExtensionName($extension_name);
        $option_name = $prefix.self::$expired_prefix.$name;
        
        if(!self::validateOptionName($option_name))
        {
            return false;
        }
        
        return $option_name;
		//return $extension_name;
    }

/**
 * Return expired period
 * 
 * @param int expired period
 */     
    private static function getPeriod($period)
    {
        return ($period == '') ? self::$default_period : $period;
    }
 
 /**
  * Set last activation timer
  * 
  * @param string extension name
  * @param int expired time
  */    
    public static function setTimer($extension_name, $period = '')
    {
        $period = self::getPeriod($period);
        
        $timer_name = self::getTimerName($extension_name);
        $expired_option_name = self::getExpiredOptionName($extension_name);
        
        if(!$timer_name || !$expired_option_name)
        { 
            // names of the options is too big!
            return false;
        }
		
        // set expired option
        $result_expired = update_option($expired_option_name, 0);
        
        // set transient option
        $result_transient = set_transient($timer_name,$extension_name,$period);
        
		/*
		$alltimers=get_transient($all_timers);
		if (!$alltimers || !is_object($alltimers))
		{
			$alltimers=new stdClass;
			$alltimers->timers=array();
			$alltimers->expired=array();
		}
        */
		//$alltimers->timers[$extension_name]=$period;
		if($result_transient && $result_expired)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

 /**
  * Set last activation timers for array of extensions
  * 
  * @param array extensions array
  * @param int expired time
  */     
    public static function setTimers($extension_names, $period = '')
    {
        $period = self::getPeriod($period);
        
        if(!is_array($extension_names))
        {
            return false;
        }
        
        for($i=0; $i<count($extension_names); $i++)
        {
            $result = self::setTimer($extension_names[$i], $period);
            
            if(!$result)
            {
                return false;
            }
        }
        
        return true;
    }

/**
 * Check timer of specified extension 
 * 
 * @param string extension name
 */ 
    public static function checkTimer($extension_name)
    {
        $timer_name = self::getTimerName($extension_name);
        $expired_option_name = self::getExpiredOptionName($extension_name);
      /*  
        if(!$timer_name || !$expired_option_name)
        {
            return false;
        }
        */
        $transient_option = get_transient($timer_name);
        if($transient_option)
        {
            $res_trans = 1;
        }
        else
        {
            $res_trans = 0;
        }
        
        
        $expired_option = get_option($expired_option_name);
        
        // !$transient_option - transient option is not set OR wasn't set
        // !$expired_option - transient option was set        
        if(!$transient_option && !$expired_option)
        {
            update_option($expired_option_name, 1);
            $expired_option = 1;
        }
        
        return $expired_option;
    }

/**
 * Check timers of specified extensions
 * 
 * @param array array of extensions
 */ 
    public static function checkTimers($extension_names = '')
    {
        if(!is_array($extension_names) || $extension_names == '')
        {
            $extension_names = self::getAllTimers();
        }
        
        $checked_array = array();
        
        for($i=0; $i<count($extension_names); $i++)
        {
            $checked_array[$extension_names[$i]] = self::checkTimer($extension_names[$i]);
        }
        
        return $checked_array;
    }

/**
 * Get all registered timers
 */     
    private static function getAllTimers()
    {
        global $wpdb;
        
        $prefix = self::getTimerPrefix();
        $expired_prefix = self::getExpiredTimerPrefix();
        
        $q = "SELECT * 
            FROM $wpdb->options
            WHERE option_name REGEXP '^$expired_prefix'";
            
        
        $options = $wpdb->get_results($q);
        $timers=array();
        for($i=0; $i<count($options); $i++)
        {
            $timers[] = str_replace($expired_prefix,'',$options[$i]->option_name); 
            //$timers[] = str_replace($prefix,'',$options[$i]->option_name); 
        }
            
        return $timers;
    }

/**
 * Delete timer  
 * 
 * @param string extension name
 */ 
    public static function deleteTimer($extension_name)
    {
        $timer_name = self::getTimerName($extension_name);
        $expired_option_name = self::getExpiredOptionName($extension_name);
        
        // set expired option to 0
        update_option($expired_option_name, 0);
        delete_transient($timer_name);
    }
}
?>