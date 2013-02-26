<?php
/**
 * WPRC_Model_RepositoriesRelationships
 * 
 * Class encapsulates all operaions with RepositoriesRelantionships table
 */ 
class WPRC_Model_RepositoriesRelationships extends WPRC_Model
{
    /**
     * Name of the table
     */ 
    private $table_name = '';

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Model_RepositoriesRelationships();
        return self::$instance;
    }

/**
 * Class constructor
 */     
    public function __construct()
    {
        parent::__construct();
        
        $this->table_name = $this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS;
    } 


/**
 * Return list of predefined records
 * 
 * @return array
 */    
    public function getPredefinedRecords()
    {
        $extension_types = array(
            array(
                'id' => 1,
                'repository_id' => 1,
                'extension_type_id' => 1
            ),
            array(
                'id' => 2,
                'repository_id' => 2,
                'extension_type_id' => 2
            )
        );
        
        return $extension_types;
    }

/**
 * Insert list of repositories    
 * 
 * @param array repositories list
 */ 
    public function insertRecords(array $types)
    {        
        $insert_query = "INSERT INTO $this->table_name (
        `id`,
        `repository_id`, 
        `extension_type_id`
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
            
            $repo_id = $types[$i]['repository_id'];
            $ext_type_id = $types[$i]['extension_type_id'];
            
            $insert_array[] = "($id, $repo_id, $ext_type_id)";
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
 * Filter records on existing
 * 
 * @param array records list
 */    
    public function getNotExistingRecords(array $records)
    {
        $select_query = "SELECT id 
        FROM $this->table_name";
        
        $ids = $this->wpdb->get_col($select_query);
        
        $not_existing_records = array();
        
        for($i=0; $i<count($records); $i++)
        {
            if(!in_array($records[$i]['id'],$ids))
            {
                $not_existing_records[] = $records[$i];
            }
        }
        
        return $not_existing_records;
    }
    
 /**
 * Prepare DB
 */     
    public function prepareDB()
    {        
        $predef_records = $this->getPredefinedRecords();
        
        // filter predefined records on existing
        $insert_records = $this->getNotExistingRecords($predef_records);
        
        // insert not existing records only
        $this->insertRecords($insert_records);
    }
}
?>