<?php
/**************************************************

Cred settings model

**************************************************/

class CRED_Settings_Model extends CRED_Abstract_Model
{

    private $option_name = 'cred_cred_settings';
    
/**
 * Class constructor
 */     
    public function __construct()
    {
        parent::__construct();
        
    }
    
    public function prepareDB()
    {
        $defaults=array(
            'wizard' => 1,
            'syntax_highlight' => 1,
            'cache_notice'=>1,
            'export_settings'=>1,
            'recaptcha'=>array(
                'public_key'=>'',
                'private_key'=>''
            )
        );
        
        $settings = get_option($this->option_name);
        
        if ($settings==false || $settings==null)
            update_option($this->option_name,$defaults);
    }
    
    public function getSettings()
    {
        return get_option($this->option_name);
    }
    
    public function updateSettings($settings)
    {
        return update_option($this->option_name,$settings);
    }
}
?>