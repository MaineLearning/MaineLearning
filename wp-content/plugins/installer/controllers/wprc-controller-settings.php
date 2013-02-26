<?php
class WPRC_Controller_Settings extends WPRC_Controller
{

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Controller_Settings();
        return self::$instance;
    }

/**
  * Save wprc settings
  * 
  * @param array $_GET array
  * @param array $_POST array
  */  
    public function save($get, $post)
    {
        $settings = array();
        
        if (!array_key_exists('_wpnonce',$post) || !wp_verify_nonce($post['_wpnonce'], 'installer-settings-form') ) die("Security check");
        
        if(array_key_exists('settings',$post) && is_array($post['settings']))
        {
            $settings = $post['settings'];
        }
        
        $settings_model = WPRC_Loader::getModel('settings');
        $res = $settings_model->save($settings);
        
        $flag = 'failure';
        if($res)
        {
            $flag = 'success';
        }
        
        $this->redirect_to_index($flag);
    }

/**
 * Redirect to the index page
 * 
 * @param string result flag
 */     
    public function redirect_to_index($flag)
    {
        //$index_page = admin_url().'admin.php?page='.WPRC_PLUGIN_FOLDER.'/pages/wprc-index.php&result='.$flag;
        $index_page = admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/installer.php&result='.$flag;
        header('location: '.$index_page);
    }
}
?>