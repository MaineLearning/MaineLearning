<?php
class WPRC_Model
{
    protected $wpdb = null;
    
    private $cached_requests_table;
    private $cached_repositories_table;
    private $cached_extension_types_table;
    private $cached_extensions_table;
    private $cached_repositories_relationships_table;

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Model();
        return self::$instance;
    }
    
    function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;

        $this -> cached_requests_table = $this -> wpdb -> prefix.WPRC_DB_TABLE_CACHED_REQUESTS;
        $this -> repositories_table = $this -> wpdb -> prefix.WPRC_DB_TABLE_REPOSITORIES;
        $this -> extension_types_table = $this -> wpdb -> prefix.WPRC_DB_TABLE_EXTENSION_TYPES;
        $this -> extensions_table = $this -> wpdb -> prefix.WPRC_DB_TABLE_EXTENSIONS;
        $this -> repositories_relationships_table = $this -> wpdb -> prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS;

    }

    public function getWPDB()
    {
        return $this->wpdb;
    }

    public function prepareDB() {

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $this->repositories_table (
            id int(8) NOT NULL AUTO_INCREMENT,
            repository_name varchar(100) NOT NULL,
            repository_endpoint_url varchar(255) NOT NULL,
            repository_username varchar(100) NOT NULL,
            repository_password varchar(100) NOT NULL,
            repository_authsalt varchar(15) NOT NULL,
            repository_enabled tinyint(1) NOT NULL DEFAULT '0',
            repository_deleted tinyint(1) NOT NULL DEFAULT '0',
            repository_logo varchar(255) DEFAULT '',
            repository_description text NOT NULL,
            repository_website_url varchar(255) DEFAULT '',
            requires_login tinyint(1) DEFAULT '0',
            PRIMARY KEY  (id)
            )
            ENGINE=MyISAM 
            DEFAULT CHARSET=utf8;
            CREATE TABLE $this->cached_requests_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            request_hash text NOT NULL,
            request_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            request_data longtext NOT NULL,
            PRIMARY KEY  (id)
            )
            ENGINE=MyISAM 
            DEFAULT CHARSET=utf8;
            CREATE TABLE $this->extensions_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            extension_name varchar(255) NOT NULL,
            extension_slug varchar(255) NOT NULL,
            extension_path varchar(255) NOT NULL,
            extension_type_id int(8) NOT NULL,
            repository_id int(8) NOT NULL,
            extension_was_installed tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY  (id)
            )
            ENGINE=MyISAM 
            DEFAULT CHARSET=utf8;
            CREATE TABLE $this->extension_types_table (
            id int(8) NOT NULL AUTO_INCREMENT,
            type_name varchar(20) NOT NULL,
            type_caption varchar(20) NOT NULL,
            type_enabled tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY  (id)
            )
            ENGINE=MyISAM 
            DEFAULT CHARSET=utf8;
            CREATE TABLE $this->repositories_relationships_table (
            id int(8) NOT NULL AUTO_INCREMENT,
            repository_id int(8) NOT NULL,
            extension_type_id int(8) NOT NULL,
            PRIMARY KEY  (id)
            )
            ENGINE=MyISAM 
            DEFAULT CHARSET=utf8;";
        
        dbDelta($sql);

        //wp_die($this -> cached_requests_table);
    }
}
?>