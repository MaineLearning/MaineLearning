<?php
class WPRC_Model_CachedRequests extends WPRC_Model
{
    /**
     * Name of the repositories table
     */
    private $table_name = '';

    private $table_records = array();

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Model_CachedRequests();
        return self::$instance;
    }
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->table_name = $this->wpdb->prefix.WPRC_DB_TABLE_CACHED_REQUESTS;
    }

    /**
     * Cache request
     *
     * @param $server_url
     * @param $request_name
     * @param $request_params
     * @param $request_data
     * @return mixed
     */
    public function cacheRequest($server_url, $request_name, $request_params, $request_data)
    {
        $hash = $this->formHash($server_url, $request_name, $request_params);

        $serialized_data = $this->serializeRequestData($request_data);
        
        //$msg=$serialized_data;
        //WPRC_Functions::log($msg,'api','cache.log');
        return $this->insertRecord($hash, $serialized_data);
    }

    private function serializeRequestData($request_data)
    {
        return @serialize($request_data);
    }

    private function unserializeRequestData($request_data)
    {
        
        //$return = (is_serialized($request_data))?@unserialize($request_data):$request_data;
        //return $return;
        
        return @unserialize($request_data);
    }

        /**
     * Form request hash
     *
     * @param $server_url
     * @param $request_name
     * @param $request_params
     * @return string
     */
    private function formHash($server_url, $request_name, $request_params)
    {
        //return md5($server_url.$request_name.serialize($request_params));
        return $server_url.'_'.$request_name.'_'.md5(@serialize($request_params));
    }

    /**
     * Get request results by hash
     *
     * @param $server_url
     * @param $request_name
     * @param $request_params
     * @return bool
     */
    public function getCachedRequest($server_url, $request_name, $request_params)
    {
        $hash = $this->formHash($server_url, $request_name, $request_params);

        $record = $this->isRecordExists($hash);

        if($record==false)
        {
            return false;
        }

        $cached_request = $record; //$this->getRecord($hash);

        if(!array_key_exists('request_data', $cached_request))
        {
            return false;
        }
        
        //$msg=$cached_request['request_data'];
        //WPRC_Functions::log($msg,'api','cache.log');
        
        return $this->unserializeRequestData($cached_request['request_data']);
    }

    /**
     * Insert cache record into the table
     *
     * @param $request_hash
     * @param $request_data
     * @return mixed
     */
    private function insertRecord($request_hash, $request_data)
    {
        // check record existence
        $row = $this->isRecordExists($request_hash);

        if($row!=false)
        {
            return false;
        }

        $data = array(
            'request_hash' => $request_hash,
            'request_data' => $request_data
        );

        $format = array(
            '%s',
            '%s'
        );

        return $this->wpdb->insert($this->table_name, $data, $format);
    }

    /**
     * Return request data by hash
     *
     * @param $hash
     */
    private function getRecord($hash)
    {
        $q = "SELECT *
        FROM {$this->table_name}
        WHERE request_hash = %s";

        $pq = $this->wpdb->prepare($q, $hash);

        return $this->wpdb->get_row($pq, ARRAY_A);
    }

    /**
     * Return all records
     *
     * @return mixed
     */
    public function getHashedRecords()
    {
        $q = "SELECT *
        FROM {$this->table_name}";

        $rows = $this->wpdb->get_results($q, ARRAY_A);

        $results = array();
        for($i=0; $i<count($rows); $i++)
        {
            $results[$rows[$i]['request_hash']] = $rows[$i];
        }

        return $results;
    }

    public function getHashedRecord($hash)
    {
        $q = "SELECT *
        FROM {$this->table_name} WHERE request_hash=%s";

        $rows = $this->wpdb->get_results($this->wpdb->prepare($q,$hash), ARRAY_A);

        $results = array();
        for($i=0; $i<count($rows); $i++)
        {
            $results[$rows[$i]['request_hash']] = $rows[$i];
        }

        return $results;
    }
    
    /**
     * Check record existence by request_hash
     * If such records exists then record will returned, otherwise return false
     *
     * @param $hash
     */
    public function isRecordExists($hash)
    {
        /*if(count($this->table_records) == 0)
        {
            $this->table_records = $this->getHashedRecords();
        }

        if(array_key_exists($hash, $this->table_records))
        {
            return $this->table_records[$hash];
        }
        else
        {
            return false;
        }*/
        $result = $this->getHashedRecord($hash);
        // log
        
        //$msg=sprintf("Cacher hash %s, result: %s, key exists: %s",$hash,print_r(array_keys($result),true),(array_key_exists($hash, $result))?'YES':'NO');
        //WPRC_Functions::log($msg,'api','api.log');
        
        if(array_key_exists($hash, $result))
        {
            return $result[$hash];
        }
        else
        {
            return false;
        }
    }

    /**
     * Clean cache and optimize the cache table after records deleting
     */
    public function cleanCache()
    {
        $q = "DELETE
        FROM {$this->table_name}";

        $this->wpdb->query($q);


        $q2 = "OPTIMIZE TABLE {$this->table_name}";
        $this->wpdb->query($q2);
    }
}
?>