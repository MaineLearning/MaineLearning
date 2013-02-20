<?php
abstract class CRED_Abstract_Model
{
    protected $wpdb = null;
    
    function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
    }

    public function getWPDB()
    {
        return $this->wpdb;
    }
}
?>