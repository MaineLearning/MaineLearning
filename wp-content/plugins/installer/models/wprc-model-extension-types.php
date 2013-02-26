<?php
class WPRC_Model_ExtensionTypes extends WPRC_Model
{
    /**
     * Name of the repositories table
     */ 
    private $table_name = '';

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Model_ExtensionTypes();
        return self::$instance;
    }

/**
 * Class constructor
 */     
    public function __construct()
    {
        parent::__construct();
        
        $this->table_name = $this->wpdb->prefix.WPRC_DB_TABLE_EXTENSION_TYPES;
    } 
    
    public function getExtensionTypesList()
    {
        $q = "SELECT * 
        FROM {$this->table_name}
        ORDER BY type_caption";

        $types = $this->wpdb->get_results($q, ARRAY_A);
        $out_types = array();
        
        for($i=0; $i<count($types); $i++)
        {
            $out_types[$types[$i]['type_name']] = $types[$i];
        }
        
        return $out_types; 
    }
    
 /**
 * Create repositories table
 */     
    public function createExtensionTypesTable()
    {       
        $q = "CREATE TABLE IF NOT EXISTS `{$this->table_name}` (
              `id` int(8) NOT NULL AUTO_INCREMENT,
              `type_name` varchar(20) NOT NULL,
              `type_caption` varchar(20) NOT NULL,
              `type_enabled` tinyint(1) NOT NULL DEFAULT '1',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";  
        
        return $this->wpdb->query($q);
    }

/**
 * Return list of predefined repositories
 * 
 * @return array
 */    
    public function getPredefinedExtensionTypes()
    {
        $extension_types = array(
            array(
                'id' => 1,
                'type_caption' => 'Plugins',
                'type_name' => 'plugins',
                'type_enabled' => 1
            ),
            array(
                'id' => 2,
                'type_caption' => 'Themes',
                'type_name' => 'themes',
                'type_enabled' => 1
            )
        );
        
        return $extension_types;
    }

/**
 * Insert list of repositories    
 * 
 * @param array repositories list
 */ 
    public function insertExtensionTypes(array $types)
    {        
        $insert_query = "INSERT INTO $this->table_name (
        `id`,
        `type_name`, 
        `type_caption`, 
        `type_enabled`
        )
        VALUES ";

        $insert_array = array();
        for($i=0; $i<count($types); $i++)
        {    
            $id = 'NULL';
            
            if(array_key_exists('id', $types[$i]) && isset($types[$i]['id']))
            {
                $id = $types[$i]['id'];    
            }
            
            $name = $types[$i]['type_name'];
            $caption = $types[$i]['type_caption'];
            $enabled = $types[$i]['type_enabled'];
            
            $insert_array[] = "($id, '$name', '$caption', $enabled)";
        }

        if(count($insert_array)>0)
        {
            $insert_query .= implode(',',$insert_array).';';

            return $this->wpdb->query($insert_query);
        }
        else
        {
            return false;
        }
    }        
    
/**
 * Filter types list on existing
 * 
 * @param array types list
 */    
    public function getNotExistingExtensionTypes(array $types)
    {
        $select_query = "SELECT type_name 
        FROM $this->table_name";
        
        $ids = $this->wpdb->get_col($select_query);
        
        $not_existing_types = array();
        
        for($i=0; $i<count($types); $i++)
        {
            if(!in_array($types[$i]['type_name'],$ids))
            {
                $not_existing_types[] = $types[$i];
            }
        }
        
        return $not_existing_types;
    }
    
 /**
 * Prepare DB
 */     
    public function prepareDB()
    {        
        $predef_records = $this->getPredefinedExtensionTypes();
        
        // filter predefined records on existing
        $insert_records = $this->getNotExistingExtensionTypes($predef_records);
        
        // insert not existing records only
        $this->insertExtensionTypes($insert_records);
    }
}
?>