<?php
class WPRC_Controller
{
    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Controller();
        return self::$instance;
    }
}
?>