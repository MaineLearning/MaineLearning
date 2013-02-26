<?php
class WPRC_Model_Settings extends WPRC_Model
{
    private $settings_array_name = 'wprc_settings';
    

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Model_Settings();
        return self::$instance;
    }
    
/**
 * Save settings
 * 
 * @param array settings array
 */ 
    public function save(array $_settings)
    {
        $settings = array();
        if(array_key_exists('allow_compatibility_reporting',$_settings))
        {
            $settings['allow_compatibility_reporting'] = 1;   
        }
        else
        { 
            $settings['allow_compatibility_reporting'] = 0;
        }
        
        return update_option($this->settings_array_name, $settings);     
    }

/**
 * Return associative array of wprc settings
 */     
    public function getSettings()
    {
        return get_option($this->settings_array_name);
    }

/**
 * Get setting by name
 * 
 * @param string setting name
 */     
    public function getSetting($setting_name)
    {
        $settings = $this->getSettings();
        
        if(!is_array($settings))
        {
            return false;
        }
        
        if(!array_key_exists($setting_name, $settings))
        {
            return false;
        }
        
        return $settings[$setting_name];
    }

/**
 * Return list of predefined records
 */     
    public function getPredifinedRecords()
    {
        $settings = array(
            'allow_compatibility_reporting' => 1
        );
        
        return $settings;
    }
/**
 * Prepare database
 */     
    public function prepareDB()
    {
        $predifined_records = $this->getPredifinedRecords();
        
        return $this->save($predifined_records);
    }
}
?>