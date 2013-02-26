<?php
class WPRC_ExtensionDataManager
{
    private $extension_type = '';
    private $extension_name = '';
    private $option_name = '';
    private static $prefix = 'wprc_info_';
    
	public function __construct($extension_type, $extension_name)
    {
        $this->extension_type = $extension_type;
        $this->extension_name = $extension_name;
        
        $this->setExtensionOptionName();
    }
    
  /*  
    public function deleteExtensionData($extension_data)
    {
        
    }
*/
/**
 * Return extension option name
 * 
 * @return string extension option name
 */ 
    private function setExtensionOptionName()
    {        
        $this->option_name = self::$prefix.$this->extension_type;//.'_info';
    }

/**
 * Update extension data
 * 
 * @param string extension type (plugin|theme)
 * @param string extension name
 * @param array associative array of extension data
 */     
    public function updateExtensionData($extension_data)
    {         
        $extension_info = get_option($this->option_name);
        $extension = '';
        $extension_db_data = '';
        
        if($extension_info && is_array($extension_info))
        {
            // update option
            if(array_key_exists($this->extension_name, $extension_info))
            {
                $extension_db_data = $extension_info[$this->extension_name];
            }
            
            if(is_array($extension_db_data))
            {
                $result_data = array_merge($extension_db_data, $extension_data);
            }
            else
            { 
                $result_data = $extension_data; 
            }
            
            $extension_info[$this->extension_name] = $result_data;
            $result = update_option($this->option_name, $extension_info);

        }
        else 
        {
            $extensions = array(
                $this->extension_name => $extension_data
            );
            
            // add option
            $result = add_option($this->option_name, $extensions);            
        }
        
        return $result;
    }
/**
 * Update extension item by key
 * If item by key doen't exist it will be added
 * 
 */    
    public function updateExtensionDataItem($key, $value)
    {
        $extension_data = array(
            $key => $value
        );
        
        return $this->updateExtensionData($extension_data);
    }
    
    public function getAllExtensionData()
    {
        return get_option($this->option_name);
    }
    
    private static function getOptionName($extension_type)
    {
        return self::$prefix.$extension_type;//.'_info';
		//return $extension_type.'_info';
    }
    
    public static function getAllData($extension_type)
    {
        $option_name = self::getOptionName($extension_type);
        return get_option($option_name);
    }
    
    public function getExtensionData()
    {
        $extensions = get_option($this->option_name);
        
        if(!array_key_exists($this->extension_name,$extensions))
        {
            return false;
        }
        
        return  $extensions[$this->extension_name]; 
    }
        
/**
 * Update activation date of extension
 * 
 * @param string extension type (plugin|theme)
 * @param string extension name
 */ 
    public function updateActivationDate()
    {
        return $this->updateExtensionDataItem('last_activation_date',time());
    }

/**
 * Update deactivation date of extension
 * 
 * @param string extension type (plugin|theme)
 * @param string extension name
 */     
    public function updateDeactivationDate()
    {
        return $this->updateExtensionDataItem('last_deactivation_date',time());
    }
    
 /**
 * Delete extension item by key
 * 
 */  
 /*  
    public function deleteExtensionItem($key)
    {
        $extension_data = array(
            $key
        );
        
        return $this->deleteExtensionData($extension_data);
    }
*/
 
}
?>