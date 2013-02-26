<?php
/**
 * WPRC_Model_Repositories
 * 
 * Class encapsulates all operaions with Repositories table
 */ 
class WPRC_Model_Repositories extends WPRC_Model
{
    /**
     * Name of the repositories table
     */ 
    private $repository_table = '';

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Model_Repositories();
        return self::$instance;
    }

/**
 * Class constructor
 */     
    public function __construct()
    {
        parent::__construct();
        
        $this->repository_table = $this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES;
    } 

/**
 * Filter repositories list on existing
 * 
 * @param array repositories list
 */    
    public function getNotExistingRepositories(array $repositories)
    {
        $repo_select_query = "SELECT repository_endpoint_url 
        FROM $this->repository_table";
        
        $db_repos_urls = $this->wpdb->get_col($repo_select_query);

        $not_existing_repositories = array();

        for($i=0; $i<count($repositories); $i++)
        {
            if(!in_array($repositories[$i]['repository_endpoint_url'],$db_repos_urls))
            {
                $not_existing_repositories[] = $repositories[$i];
            }
        }

        return $not_existing_repositories;
    }
    
/**
 * Insert list of repositories    
 * 
 * @param array repositories list
 */ 
    public function insertRepositories(array $repositories)
    {        
        $insert_query = "INSERT INTO $this->repository_table (
        `id`,
        `repository_name`,
        `repository_endpoint_url`,
        `repository_username`,
        `repository_password`,
        `repository_authsalt`,
        `repository_enabled`,
        `repository_deleted` ,
        `repository_logo`,
        `repository_description`,
        `repository_website_url`       
        )
        VALUES ";

        $insert_array = array();
        for($i=0; $i<count($repositories); $i++)
        {    
            $id = 'NULL';
            
            if(array_key_exists('id', $repositories[$i]) && isset($repositories[$i]['id']))
            {
                $id = $repositories[$i]['id'];    
            }
            
            $name = $repositories[$i]['repository_name'];
            $endpoint_url = $repositories[$i]['repository_endpoint_url'];
            $username = $repositories[$i]['repository_username'];
            $pass = $repositories[$i]['repository_password'];
            if (!(isset($repositories[$i]['repository_authsalt']) && $repositories[$i]['repository_authsalt']!=''))
				$authsalt=$this->_gensalt(8);
			else
				$authsalt = $repositories[$i]['repository_authsalt'];
            $enabled = $repositories[$i]['repository_enabled'];
            $logo = esc_url($repositories[$i]['repository_logo']);
            $description = esc_textarea($repositories[$i]['repository_description']);
            $website_url = esc_url($repositories[$i]['repository_website_url']);
            $insert_array[] = "($id, '$name', '$endpoint_url', '$username', '$pass', '$authsalt', $enabled, 0, '$logo', '$description', '$website_url' )";
        }

        if(count($insert_array)>0)
        {
            $insert_query .= implode(',',$insert_array).';';
            $result = $this->wpdb->query($insert_query);
            return $result;

        }
        else
        {
            return false;
        }
    }
    
/**
 * Return list of predefined repositories
 * 
 * @return array
 */    
    public function getPredefinedRepositories()
    {
        $predefined_repos = array(
            array(
                'repository_name' => 'Wordress.org Plugins',
                'repository_endpoint_url' => WPRC_WP_PLUGINS_REPO,
                'repository_username' => '',
                'repository_website_url' => WPRC_WP_PLUGINS_SITE,
                'repository_description' => __( 'Free plugins from various authors, hosted on WordPress.org.','installer'),
                'repository_logo' => WPRC_ASSETS_URL . '/images/wp-placeholder.png',
                'repository_password' => '',
                'repository_authsalt' => '',
                'repository_enabled' => 1,
                'repository_deleted' => 0,
                'requires_login' => 0
                ),
            array(
                'repository_name' => 'Wordress.org Themes',
                'repository_endpoint_url' => WPRC_WP_THEMES_REPO,
                'repository_username' => '',
                'repository_website_url' => WPRC_WP_THEMES_SITE,
                'repository_description' => __('Free themes from various authors, hosted on WordPress.org.','installer'),
                'repository_logo' => WPRC_ASSETS_URL . '/images/wp-placeholder.png',
                'repository_password' => '',
                'repository_authsalt' => '',
                'repository_enabled' => 1,
                'repository_deleted' => 0,
                'requires_login' => 0
                )
        );
        
        return $predefined_repos;
    }
    
    
/**
 * Delete repository
 * 
 * @param int repository id
 */     
    public function deleteRepository($repository_id)
    {        
        $query = "DELETE FROM $this->repository_table WHERE id=".$repository_id;
        return $this->wpdb->query($query);
    }

    public function softdeleteRepository($repository_id)
    {        
        $query = "UPDATE $this->repository_table SET repository_deleted=1 WHERE id=".$repository_id;
        return $this->wpdb->query($query);
    }

    public function softundeleteRepository($repository_id)
    {        
        $query = "UPDATE $this->repository_table SET repository_deleted=0 WHERE id=".$repository_id;
        return $this->wpdb->query($query);
    }
/**
 * Get repository by id
 * 
 * @param int repository id
 */     
    public function getRepository($repository_id)
    {        
        if(!isset($repository_id))
        {
            return false;
        }
        
        $q = "SELECT * 
        FROM $this->repository_table
        WHERE id = %d";

        $pq = $this->wpdb->prepare($q, $repository_id);
        $repository = $this->wpdb->get_row($pq);
        
        $q = "SELECT et.* 
        FROM ($this->repository_table AS r INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." AS rr ON r.id = rr.repository_id) 
        INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_EXTENSION_TYPES." AS et ON rr.extension_type_id = et.id
        WHERE r.id = %d";

        if(isset($repository_id))
        {
            $pq = $this->wpdb->prepare($q, $repository_id);
        }
        else
        {
            $pg = $q;
        }

        $types = $this->wpdb->get_results($pq, ARRAY_A);
        $out_types = array();
        
        for($i=0; $i<count($types); $i++)
        {
            $out_types[$types[$i]['type_name']] = $types[$i];
        }
        
        $repository->extension_types = $out_types; 
        
        return $repository;
    }
    
    public function clearLogin($repository_id)
    {   
        try
        {
            $result=$this->wpdb->query("UPDATE ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES." SET repository_username='',repository_password='' WHERE id=".intval($repository_id));
        }
        catch (Exception $e)
        {
            return false;
        }
        return ($result!==false);
    }
    
 /**
 * Get deleted repositories
 * 
 * @param string field name
 * @param string field value
 */     
    public function getDeletedRepositories()
    {        
        $q = "SELECT * 
        FROM $this->repository_table 
        WHERE repository_deleted = 1";

        return $this->wpdb->get_results($q);
    }
 
  /**
 * Get repository by field (name, value)
 * 
 * @param string field name
 * @param string field value
 */     
    public function getRepositoryByField($field_name, $field_value)
    {        
        if($field_name == '')
        {
            return false;
        }
        
        $q = "SELECT * 
        FROM $this->repository_table 
        WHERE $field_name = %s";

        $pq = $this->wpdb->prepare($q, $field_value);
        return $this->wpdb->get_row($pq);
    }

  /**
 * Get repository by field (name, value)
 * 
 * @param string field name
 * @param string field value
 */     
    public function getRepositoryDataByLikeFields($getfield, $fields=array(), $enabled=true)
    {        
        if(empty($fields))
        {
            return 'FOO';
        }
        
        $likesql=array();
        foreach ($fields as $field=>$like)
            $likesql[]= "$field LIKE '".$like."'";
            
        $sql = "SELECT $getfield 
        FROM $this->repository_table 
        WHERE ".implode(' AND ',$likesql);
        
        if ($enabled)
            $sql.=' AND repository_enabled=1 AND repository_deleted=0';
        return $this->wpdb->get_results($sql);
    }

/**
 * Add repository
 * 
 * @param string repository name
 * @param string repository endpoint url
 * @param string repository user name
 * @param string repository password
 * @param int repository login required (0|1) 
 * @param array array of repository types
 */    
    public function addRepository($repository_name, $repository_endpoint_url, $repository_username, $repository_password, $repository_enabled, $repository_types, $repository_logo, $repository_site_url, $repository_description, $repository_requires_login )
    {        
        $data = array(
            'repository_name' => $repository_name,
            'repository_endpoint_url' => $repository_endpoint_url,
            'repository_username' => $repository_username,
            'repository_password' => $repository_password,
            'repository_authsalt' => $this->_gensalt(8),
            'repository_enabled' => $repository_enabled,
            'repository_deleted' => 0,
            'repository_logo' => $repository_logo,
            'repository_website_url' => $repository_site_url,
            'repository_description' => $repository_description,
            'requires_login' => $repository_requires_login,
        );
        
        $format = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%d'
        );
        
        
        $res = $this->wpdb->insert($this->repository_table, $data, $format);

        $repository_id = $this->wpdb->insert_id;
        
        for($i=0; $i<count($repository_types); $i++)
        {
            $types[] = array(
                'extension_type_id' => $repository_types[$i], 
                'repository_id' => $repository_id
            );
        }
            
        $repo_rel_model = WPRC_Loader::getModel('repositories-relationships');
        $repo_rel_model->insertRecords($types);
        
        return $res;
    }
    
 /**
 * Update repository
 * 
 * @param int repository id
 * @param string repository name
 * @param string repository endpoint url
 * @param string repository user name
 * @param string repository password
 * @param int repository enabled (0|1) 
 * @param array array of repository types
 */    
    public function updateRepository($repository_id, $repository_name, $repository_endpoint_url, $repository_username, $repository_password, $repository_enabled, $repository_types, $repository_deleted=0)
    {     
        $repo = $this->getRepositoryByField('id', $repository_id);
        if(!empty($repository_username) && empty($repo->repository_authsalt)){
            $repository_authsalt = $this->_gensalt(8);
        }else{
            $repository_authsalt = $repo->repository_authsalt;
        }
           
        $data = array(
            'repository_name' => $repository_name,
            'repository_endpoint_url' => $repository_endpoint_url,
            'repository_username' => $repository_username,
            'repository_password' => $repository_password,
            'repository_authsalt' => $repository_authsalt,
            'repository_enabled' => $repository_enabled,
            'repository_deleted' => $repository_deleted
        );
        
        $where = array(
            'id' => $repository_id 
        );
        
        $format = array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d'
        );
        
        $where_format = array(
            '%d'
        );
      //  deleteRepositoryTypes
        $res = $this->wpdb->update($this->repository_table, $data, $where, $format, $where_format);
                    
        // delete all repository types
        $this->deleteRepositoryTypes($repository_id);
        $types = array();
            
        for($i=0; $i<count($repository_types); $i++)
        {
            $types[] = array(
                'extension_type_id' => $repository_types[$i], 
                'repository_id' => $repository_id
            );
        }
            
        $repo_rel_model = WPRC_Loader::getModel('repositories-relationships');
        $repo_rel_model->insertRecords($types);
        
        return $res;
    }
    
    public function updateRepositoryNoLogin($repository_id, $repository_name, $repository_endpoint_url, $repository_enabled, $repository_types, $repository_logo, $repository_site_url, $repository_description, $repository_requires_login,$repository_deleted=0)
    {     
        $repo = $this->getRepositoryByField('id', $repository_id);
        if(empty($repo->repository_authsalt)){
            $repository_authsalt = $this->_gensalt(8);
        }else{
            $repository_authsalt = $repo->repository_authsalt;
        }
           
        $data = array(
            'repository_name' => $repository_name,
            'repository_endpoint_url' => $repository_endpoint_url,
            'repository_authsalt' => $repository_authsalt,
            'repository_enabled' => $repository_enabled,
            'repository_deleted' => $repository_deleted,
            'repository_logo' => $repository_logo,
            'repository_website_url' => $repository_site_url,
            'repository_description' => $repository_description,
            'requires_login' => $repository_requires_login
        );
        
        $where = array(
            'id' => $repository_id 
        );
        
        $format = array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%d'
        );
        
        $where_format = array(
            '%d'
        );
      //  deleteRepositoryTypes
        $res = $this->wpdb->update($this->repository_table, $data, $where, $format, $where_format);

        // delete all repository types
        $this->deleteRepositoryTypes($repository_id);
        $types = array();
            
        for($i=0; $i<count($repository_types); $i++)
        {
            $types[] = array(
                'extension_type_id' => $repository_types[$i], 
                'repository_id' => $repository_id
            );
        }
            
        $repo_rel_model = WPRC_Loader::getModel('repositories-relationships');
        $repo_rel_model->insertRecords($types);
        
        return $res;
    }
    
    function updateRepositoryAuth($repository_id, $username, $password){
        $repo = $this->getRepositoryByField('id', $repository_id);
        $auth_salt = $repo->repository_authsalt;
        if(empty($auth_salt)){
            $auth_salt = $this->_gensalt(8);
        }
        
        //$enc_password = hash_hmac('md5', $password, $auth_salt);
        // For now we keep the plain password
        $enc_password = $password;
        
        $this->wpdb->update($this->repository_table, array(
                                        'repository_username' => $username,
                                        'repository_password' => $enc_password,
                ), array('id' => $repository_id));
            
    }
    
    function testLogin($repository_id, $username, $password, $plain=false){

        //$rm = $this->repo_model;//WPRC_Loader::getModel('repositories');
        $repo = $this->getRepositoryByField('id', $repository_id);
        
		$body_array = array(
			'action' => 'repository_login',
		);
		$salt=$repo->repository_authsalt;
		//WPRC_Loader::includeSecurity();
		//$body_array['auth'] = array('user'=>$username,'pass'=>WPRC_Security::encrypt($salt,$password),'salt'=>$salt);
		$body_array['auth'] = array('user'=>$username,'pass'=>$password,'salt'=>$salt);
        if ($plain)
        {
            $body_array['auth']['_plain']='true';
        }
        
		// log
        if (!$plain)
        $msg=sprintf('Repository Login to %s with auth, timeout: %d, action: %s',$repo->repository_endpoint_url,5,$body_array['action']);
        else
        $msg=sprintf('Repository Login to %s with auth plain, timeout: %d, action: %s',$repo->repository_endpoint_url,5,$body_array['action']);
        WPRC_Functions::log($msg,'controller','controller.log');

        $request = wp_remote_post($repo->repository_endpoint_url, array( 'timeout' => 15, 'body' => $body_array) );

		// log
        $msg=sprintf('Repository Login to %s with auth, response: %s',$repo->repository_endpoint_url,print_r($request,true));
        WPRC_Functions::log($msg,'controller','controller.log');
		
        if ( is_wp_error($request) ) 
		{
			$res = new WP_Error('repository_login_failed', __('An unexpected HTTP Error occurred during the API request.', 'installer'), $request->get_error_message() );
            // log
            $msg=sprintf('Repository Login to %s with auth, response error: %s',$repo->repository_endpoint_url,print_r($request->get_error_message()));
            WPRC_Functions::log($msg,'controller','controller.log');
            
		} 
		else 
		{
			$request_body = wp_remote_retrieve_body( $request );

			if(is_serialized($request_body))
			{
				$res = @unserialize( $request_body );

			}
            
		}
        if (!isset($res) || $res==false || is_wp_error($res))
        {
            // log
            $msg=sprintf('Repository Login to %s with auth, response unserialize error: %s',$repo->repository_endpoint_url,print_r(wp_remote_retrieve_body( $request )));
            WPRC_Functions::log($msg,'controller','controller.log');
			return false;
        }
			
		if(isset($res->error) && !isset($res->success)){
            $response = array('error' => 1, 'message' => $res->error);
        }else{
            $response = array('error' => 0, 'message' => $res->success);
            $doupdate=false;
            if ($plain && isset($res->pass) && $res->pass!='')
            {
                $password = $res->pass;
                $doupdate=true;
            }
            if (!$plain)
            {
                $doupdate=true;
            }
            if ($doupdate)
            {
                $this->updateRepositoryAuth($repository_id, $username, $password);
                // clear cache
                $rmcache = WPRC_Loader::getModel('cached-requests');
                $rmcache->cleanCache();
                // clear update data
                delete_site_transient( 'update_plugins' );
                delete_site_transient( 'update_themes' );
            }
        }
        
        return $response;
    }
    
    function hasLogin(){
        
    }
    

	public function getAllRepositories()
	{
        return $this->wpdb->get_results("SELECT * FROM {$this->repository_table}");
	}
	
	public function getAllRepositoriesNotDeleted()
	{
        return $this->wpdb->get_results("SELECT * FROM {$this->repository_table} WHERE repository_deleted=0");
	}

    public function getAllRepositoriesDeleted()
    {
        return $this->wpdb->get_results("SELECT * FROM {$this->repository_table} WHERE repository_deleted=1");
    }


	
	public function addUpdateRepositories(array $repos)
	{
		foreach ($repos as $repo)
		{
			$val="";
			$val.="null";
			$val.=", '".$repo['repository_name']."'";
			$val.=", '".$repo['repository_endpoint_url']."'";
			$val.=", ''";
			$val.=", ''";
			$val.=", '".$this->_gensalt(8)."'";
			$val.=", '".$repo['repository_enabled']."'";
			$val.=", 0";
            $val.=", '".$repo['repository_description']."'";
            $val.=", '".$repo['repository_website_url']."'";
            $val.=", ".$repo['requires_login'];
            $val.=", '".$repo['repository_logo']."'";
			$this->wpdb->query("INSERT INTO {$this->repository_table} (id, repository_name, repository_endpoint_url, repository_username, repository_password,repository_authsalt, repository_enabled, repository_deleted, repository_description, repository_website_url, requires_login, repository_logo) VALUES ($val)");
			$insertedid=$this->wpdb->insert_id;
			if ($repo['plugins']==true)
			{
				$val="";
				$val.="null";
				$val.=','.$insertedid;
				$val.=",1";
				$this->wpdb->query("INSERT INTO ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." (id,repository_id,extension_type_id) VALUES ($val)");
			}
			if ($repo['themes']==true)
			{
				$val="";
				$val.="null";
				$val.=','.$insertedid;
				$val.=",2";
				$this->wpdb->query("INSERT INTO ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." (id,repository_id,extension_type_id) VALUES ($val)");
			}
		}
	}
	
	public function updateUpdateRepositories(array $repos)
	{
		foreach ($repos as $repo)
		{ 
			$val="";
			$val.="id=".$repo['repository_id'];
			$val.=", repository_name='".$repo['repository_name']."'";
			//$val.=", '".$repo['repository_endpoint_url']."'";
			//$val.=", '".$repo['repository_username']."'";
			//$val.=", '".$repo['repository_password']."'";
			//$val.=", '".$repo['repository_authsalt']."'";
			$val.=", repository_enabled='".$repo['repository_enabled']."'";
			$val.=", repository_deleted=0";
            $val.=", repository_description='".$repo['repository_description']."'";
            $val.=", repository_website_url='".$repo['repository_website_url']."'";
            $val.=", requires_login=".$repo['requires_login'];
            $val.=", repository_logo='".$repo['repository_logo']."'";

			$this->wpdb->query("UPDATE {$this->repository_table} SET $val WHERE id=".(int)$repo['repository_id']);
			$extension_types=$this->wpdb->get_results("SELECT extension_type_id FROM ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." WHERE repository_id=".(int)$repo['repository_id']);
			$repo_ext=array('plugins'=>false,'themes'=>false);
			foreach ($extension_types as $ext)
			{
				if ($ext->extension_type_id==1)
					$repo_ext['plugins']=true;
				if ($ext->extension_type_id==2)
					$repo_ext['themes']=true;
			}
			if ($repo['plugins']==true && $repo_ext['plugins']==false)
			{
				
				$val="";
				$val.="null";
				$val.=','.$repo['repository_id'];
				$val.=",1";
				$this->wpdb->query("INSERT INTO ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." (id,repository_id,extension_type_id) VALUES ($val)");
			}
			if ($repo['plugins']==false && $repo_ext['plugins']==true)
			{
				
				$this->wpdb->query("DELETE FROM ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." WHERE extension_type_id=1 AND repository_id=".(int)$repo['repository_id']);
			}
			if ($repo['themes']==true && $repo_ext['themes']==false)
			{
				
				$val="";
				$val.="null";
				$val.=','.$repo['repository_id'];
				$val.=",2";
				$this->wpdb->query("INSERT INTO ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." (id,repository_id,extension_type_id) VALUES ($val)");
			}
			if ($repo['themes']==false && $repo_ext['themes']==true)
			{
				
				$this->wpdb->query("DELETE FROM ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." WHERE extension_type_id=2 AND repository_id=".(int)$repo['repository_id']);
			}
		}
	}
/**
 * Return array of repository ids
 * 
 * @param string mode
 */ 
    public function getRepositoriesIds($return_results = 'all_not_deleted',$type='all')
    {
       $options = array('all', 'all_not_deleted', 'enabled_repositories');
        
		if(!in_array($return_results, $options))
        {
            return false;
        }
        
        $where = '';
        
        switch($return_results)
        {
            case 'all_not_deleted':
                $where = "WHERE r.repository_deleted=0";
                break;
            case 'enabled_repositories':
                $where = "WHERE r.repository_enabled = 1 AND r.repository_deleted=0";
                break;
        }
		
        $join='';
		switch($type)
        {
            case 'plugins':
                $join .= "INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." AS rr ON r.id=rr.repository_id";
				if ($where != '') $where.=' AND ';
				$where .= "rr.extension_type_id=1";
                break;
            case 'themes':
                $join .= "INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." AS rr ON r.id=rr.repository_id";
				if ($where != '') $where.=' AND ';
				$where .= "rr.extension_type_id=2";
                break;
            default:
                break;
        }
        
		$q = "SELECT DISTINCT(r.id)
        FROM $this->repository_table AS r 
		 $join  
         $where";
        
        return $this->wpdb->get_col($q);
    }
    
/**
 * Return repositories list by extension type name
 * 
 * @param string extension type name ( 'all' | 'plugins' | 'themes' ). If extension type name == 'all' method will return all repositories.
 */ 
    public function getRepositoriesListByType($extension_type)
    {
        $types = array('all', 'all_not_deleted', 'plugins', 'themes', 'plugins_not_deleted', 'themes_not_deleted');
        if(!in_array($extension_type, $types))
        {
            return false;
        }
        
        switch($extension_type)
        {
            case 'all':
                return $this->getRepositoriesList($return_results = 'all', array('id', 'repository_name', 'repository_enabled'));
                break;
                
            case 'all_not_deleted':
                return $this->getRepositoriesList($return_results = 'all_not_deleted', array('id', 'repository_name', 'repository_enabled'));
                break;
            
			case 'plugins_not_deleted':
                $q = "SELECT r.id, r.repository_name, r.repository_enabled
                FROM ({$this->repository_table} AS r INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." AS rr ON r.id = rr.repository_id)
                INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_EXTENSION_TYPES." AS et ON rr.extension_type_id = et.id
                WHERE et.type_name = 'plugins' AND r.repository_deleted=0";
                
                return $this->wpdb->get_results($q);
				break;
			
			case 'themes_not_deleted':
                $q = "SELECT r.id, r.repository_name, r.repository_enabled
                FROM ({$this->repository_table} AS r INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." AS rr ON r.id = rr.repository_id)
                INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_EXTENSION_TYPES." AS et ON rr.extension_type_id = et.id
                WHERE et.type_name = 'themes' AND r.repository_deleted=0";
                
                return $this->wpdb->get_results($q);
				break;
			
			default:
                $q = "SELECT r.id, r.repository_name, r.repository_enabled
                FROM ({$this->repository_table} AS r INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS." AS rr ON r.id = rr.repository_id)
                INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_EXTENSION_TYPES." AS et ON rr.extension_type_id = et.id
                WHERE et.type_name = '$extension_type'";
                
                return $this->wpdb->get_results($q);
				break;
        }     
    }
    
    public function getRepositoriesList($return_results = 'all_not_deleted', $fields_to_select = array())
    {
        $options = array('all', 'all_not_deleted', 'enabled_repositories');
        
        if(!in_array($return_results, $options))
        {
            return false;
        }
        
        $where = '';
        switch($return_results)
        {
            case 'all_not_deleted':
                $where = "WHERE repository_deleted = 0";
                break;
            case 'enabled_repositories':
                $where = "WHERE repository_enabled = 1 AND repository_deleted=0";
                break;
        }
    
        $select_fields = '*';
        if(count($fields_to_select)>0)
        {
            $select_fields = implode(',', $fields_to_select);
        }
           
        $q = "SELECT $select_fields
        FROM $this->repository_table
        $where";
        
        return $this->wpdb->get_results($q);
    }

/**
 * Return repositories by array of ids
 * 
 * @param array array of repositories ids
 * @param array array of fields to select
 */     
    public function getRepositoriesByIds($ids_array, $fields_to_select = array())
    {
        $ids = array();
        $where = '';
        if(count($ids_array)>0)
        {
            $ids = implode(',',$ids_array);
            $where = "WHERE id IN ($ids)";
        }
        else
        {
            return $ids_array;
        }
        
        $select_fields = '*';
        if(count($fields_to_select)>0)
        {
            $select_fields = implode(',', $fields_to_select);
        }
        
        $q = "SELECT $select_fields
        FROM $this->repository_table
        $where";
        
        return $this->wpdb->get_results($q);
    }

/**
 * Delete all repository types by repository id
 * 
 * @param int repository id
 */ 
    private function deleteRepositoryTypes($repository_id)
    {        
        $q = "DELETE FROM ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES_RELATIONSHIPS."
        WHERE repository_id = $repository_id";
        
        return $this->wpdb->query($q);
    }
	
	public function getRepositoryByExtension($slug=null)
	{
		if ($slug===null) return false;
		
		$q = "SELECT r.* 
		FROM ($this->repository_table AS r INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_EXTENSIONS." AS ext ON r.id = ext.repository_id) 
		WHERE ext.extension_slug = %s";
		$pq = $this->wpdb->prepare($q, $slug);
		$repos = $this->wpdb->get_results($pq, ARRAY_A);
		
		return ($repos);
	}

/**
 * Prepare DB
 */     
    public function prepareDB()
    {        
        $predef_repos = $this->getPredefinedRepositories();

        // filter predefined repositories on existing
        $insert_repos = $this->getNotExistingRepositories($predef_repos);
        
        // insert not existing repositories only
        $this->insertRepositories($insert_repos);
    }

    public function update_default_repositories() {
        $predef_repos = $this->getPredefinedRepositories();

        foreach ( $predef_repos as $repo ) {
            $this -> wpdb -> update( 
                $this -> repository_table, 
                array(
                    'repository_name' => $repo['repository_name'],
                    'repository_website_url' => $repo['repository_website_url'],
                    'repository_description' => $repo['repository_description'],
                    'repository_logo' => $repo['repository_logo'],
                    'requires_login' => $repo['requires_login']
                ),
                array( 'repository_endpoint_url' => $repo['repository_endpoint_url'] ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d'
                ),
                array( '%s' )
            );
        }

    }
    
    function _gensalt($length = 8){
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $return = '';

        if ($length > 0) {
            for ($i = 0; $i <= $length; ++$i) {
                $return .= $characters[rand(0, strlen($characters) - 1)];
            }
        }
        
        return $return;
    }
}
?>