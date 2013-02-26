<?php
class WPRC_Model_Extensions extends WPRC_Model
{
/**
  * Name of the extensions table
  */ 
    private $table_name = '';

    // Singleton
    private static $instance=null;
    
    public static function getInstance()
    {
        if (self::$instance===null)
            self::$instance=new WPRC_Model_Extensions();
        return self::$instance;
    }

/**
 * Class constructor
 */     
    public function __construct()
    {
        parent::__construct();
        
        $this->table_name = $this->wpdb->prefix.WPRC_DB_TABLE_EXTENSIONS;
    } 
    
/**
 * Get plugin list
 */ 
    public function getPlugins()
    {
        if(!function_exists('get_plugins'))
        {
            WPRC_Loader::includeWPPluginFile();
        }
        return get_plugins();
    }

/**
 * Get theme list
 */     
    public function getThemes()
    {
        global $wp_version;
		
		$themes=null;
		if(!function_exists('wp_get_themes'))
        {
            $themes= get_themes();
        }
        else
        {
            $themes= wp_get_themes();
        }
		
		if (version_compare($wp_version,'3.4','>='))
		{
			foreach ($themes as $key=>$theme)
			{
				$themes[$key]=$this->WP_ThemeToArray($theme);
			}
		}
		return $themes;
    }

/**
 * Get plugin list without specified plugins
 * 
 * @param string plugin name
 */     
    public function getPluginsWithoutSpecifiedPlugins(array $specified_plugins)
    {        
        $plugins = $this->getPlugins(); 
        
        if(count($specified_plugins) == 0)  
        {
            $results = $plugins;
        }  
        else
        {
            $results = $plugins;
            
            for($i=0; $i<count($specified_plugins); $i++)
            {
                $results = $this->removeFromArrayByKey($results, $specified_plugins[$i]);
            }  
        }
        
        return $results;
    }

    
 /**
 * Get theme list without current theme
 * 
 */      
    public function getThemesWithoutCurrent()
    {
		//$current_theme = get_option('theme_switched');
		$current_theme = get_option('current_theme');
        $themes = $this->getThemes(); 

        if(!$current_theme)  
        {
            $result = $themes;
        }  
        else
        {
            $result = $this->removeFromArrayByKey($themes, $current_theme);
        }
        
        return $result;    
    }

/**
 * Remove array item by the key
 * 
 * @param array input array
 * @param string array item key
 * 
 * @return mixed
 */     
    private function removeFromArrayByKey($array, $key)
    {
        if(is_array($array) && array_key_exists($key, $array))
        {
            unset($array[$key]);
        }
    
        return $array;
    }

/**
 * Return list of active plugins
 */ 
    public function getActivePlugins()
    {
        $active_plugins = get_option('active_plugins');
        $reordered_active_plugins = array();
        foreach ( $active_plugins as $plugin ) {
            $reordered_active_plugins[] = $plugin;
        }
        return $reordered_active_plugins;
    }

    /**
     * Return all active extensions
     *
     * @return array
     */
    public function getActiveExtensions()
    {
        $active_extensions = array();

        $active_extensions['plugins'] = $this->getActivePlugins();
        $active_extensions['themes'][] = $this->getCurrentThemeName();

        return $active_extensions;
    }

/**
 * Return data of specified plugins
 * 
 * @param array plugins names array
 */     
    public function getPluginsData(array $input_plugins_names)
    {
        $all_plugins = $this->getPlugins();

        $plugins = array();
        foreach($all_plugins AS $plugin_name => $plugin_attr)
        {            
            if(in_array($plugin_name, $input_plugins_names))
            {
                $plugins[$plugin_name] = $plugin_attr;
            } 
        }
        
        return $plugins;
    }

/**
 * Get current theme name
 */     
    public function getCurrentThemeName()
    {
        return get_option('current_theme');
    }
    
/**
 * Get switch theme name
 */     
    public function getSwitchedThemeName()
    {
        return get_option('theme_switched');
    }

/**
 * Get current theme info
 */     
    public function getCurrentTheme()
    {
        $themes = $this->getThemes();
        $current_theme = $this->getCurrentTheme();
        
        if(!array_key_exists($current_theme, $themes))
        {
            return false;
        }
        
        return $themes[$current_theme]; 
    }
    
    public function getFilteredCurrentTheme()
    {
        $current_theme = $this->getCurrentTheme();
        
        if(!$current_theme)
        {
            return false;
        }

        return $this->filterThemeName($current_theme); 
    }
    
    public function filterThemeName($theme_name)
    {
        return preg_replace('/\s+/','_', $current_theme); 
    }

/** 
 * Return plugin path by plugin name
 * 
 * @param string plugin name
 */    
    public function getPluginPathByName($plugin_name)
    {
        $plugins = $this->getPlugins();
        
        if(count($plugins)>0)
        {
            foreach($plugins AS $plugin_path => $plugin)
            {
                if($plugin['Name'] == $plugin_name)
                {
                    return $plugin_path; 
                }
            }
        }
        
        return false;
    }

/**
 * Check extension existence 
 * (checks by extension slug, extension type id and repository id)
 * 
 * @param string extension slug
 * @param int extension type id
 * @param int repository id
 */  
    public function isExtensionExists($extension_slug, $extension_type_id, $repository_id)
    {
        $q = "SELECT id 
        FROM {$this->table_name}
        WHERE extension_slug = %s AND extension_type_id = %d AND repository_id = %d";

        $pq = $this->wpdb->prepare($q, $extension_slug, $extension_type_id, $repository_id);
        $id = $this->wpdb->query($pq);
   
        if($id>0 && isset($id))
        {
            return true;
        }

        return false;
    }

/**
 * Insert extenstion to the database
 * 
 * @param string extension name
 * @param string extension slug
 * @param string extension path
 * @param string extension type ( 'plugins' | 'themes' )
 * @param int repository id 
 */     
    public function addExtension($extension_name, $extension_slug, $extension_path, $extension_type, $repository_id)
    {   
        // get extension type id
        $et_model = WPRC_Loader::getModel('extension-types');
        $types = $et_model->getExtensionTypesList();
        
        if(!array_key_exists($extension_type,$types))
        { 
            return false;
        }
        
        $extension_type_id = $types[$extension_type]['id'];
        
        $extension_exists = $this->isExtensionExists($extension_slug, $extension_type_id, $repository_id);
        
        if($extension_exists)
        { 
            return false;
        }
 
        // insert extension
        $insert_array = array(
            'extension_name' => $extension_name,
            'extension_slug' => $extension_slug,
            'extension_path' => $extension_path, 
            'extension_type_id' => $extension_type_id,
            'repository_id' => $repository_id
        );
        
        $insert_format_array = array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%d'
        );
      
        return $this->wpdb->insert($this->table_name, $insert_array, $insert_format_array);
    }

    public function addExtensionInstalled($extension_name, $extension_slug, $extension_path, $extension_type, $repository_id, $ext_was_installed)
    {   
        // get extension type id
        $et_model = WPRC_Loader::getModel('extension-types');
        $types = $et_model->getExtensionTypesList();
        
        if(!array_key_exists($extension_type,$types))
        { 
            return false;
        }
        
        $extension_type_id = $types[$extension_type]['id'];
        
        $extension_exists = $this->isExtensionExists($extension_slug, $extension_type_id, $repository_id);
        
        // insert extension
        $insert_array = array(
            'extension_name' => $extension_name,
            'extension_slug' => $extension_slug,
            'extension_path' => $extension_path, 
            'extension_type_id' => $extension_type_id,
            'repository_id' => $repository_id,
            'extension_was_installed' => $ext_was_installed
        );
        
        $insert_format_array = array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d'
        );
        
        $where=array(
            'extension_name' => $extension_name
        );
        $where_format=array(
            '%s'
        );
      
        if($extension_exists)
        { 
            return $this->wpdb->update($this->table_name, $insert_array, $where, $insert_format_array, $where_format);
        }
        return $this->wpdb->insert($this->table_name, $insert_array, $insert_format_array);
    }
/** 
 * Update extension
 * 
 * @param array data to update
 * @param array where condition array
 * @param array data format array
 * @param array where condition format array
 */     
    public function updateExtension($data, $where, $format = null, $where_format = null)
    {        
        return $this->wpdb->update($this->table_name, $data, $where, $format, $where_format);
    }

/**
 * Update extension path 
 * 
 * @param string extension path
 * @param string extension slug
 * @param int repository id
 */     
    public function updateExtensionPath($extension_path, $extension_slug, $repository_id)
    {
        $extension_was_installed = 0;
        
        if($extension_path<>'')
        {
            $extension_was_installed = 1;
        }
        
        $data = array(
            'extension_path' => $extension_path, 
            'extension_was_installed' => $extension_was_installed
        );
        
        $where = array(
            'extension_slug' => $extension_slug, 
            'repository_id' => $repository_id
        );
        
        return $this->updateExtension($data, $where, array('%s', '%d'), array('%s', '%d'));
    }
    

/**
 * Return names of the keys for extension types
 */      
    public function getExtensionTypeKeys()
    {
        global $wp_version;
		
		if (version_compare($wp_version,'3.4','>='))
		{
			return array(
				'plugins' => 'extension_path',
				'themes' => 'extension_path'
			);
		}
		else
		{
			return array(
				'plugins' => 'extension_path',
				'themes' => 'extension_name'
			);
		}
    }
 
    public function getActiveExtensionTypeKeys()
    {
        global $wp_version;
		
		return array(
			'plugins' => 'Path',
			'themes' => 'Name'
		);
    }
/**
 * Return extensions tree (data from the table extensions only)
 * 
 * @param string extension type ( 'plugins' | 'themes' )
 * @param string $use_extension_name_as_key column name which will used as a key in the output array
 */    
    public function getDBExtensionsTree($extension_type = '', $use_extension_name_as_key = false)
    {
        $condition = 'WHERE e.extension_was_installed=1 ';
        if($extension_type<>'')
        {
            $condition .= "AND et.type_name = %s";
			$extension_tree = array();
        }
		else
			 $extension_tree = array(
			   'plugins' => array(),
			   'themes' => array()
			);

       if(function_exists('is_plugin_active'))
        {
            if(!is_plugin_active(WPRC_PLUGIN_NAME))
            {
                $extension_tree = array(
                   'plugins' => array(),
                   'themes' => array()
                );

                return $extension_tree;
            }
        }

        $q = "SELECT e.extension_name, e.extension_slug, e.extension_path, et.type_name, e.extension_was_installed, r.id, r.repository_username, r.repository_password, r.repository_authsalt, r.repository_enabled, r.repository_deleted, r.repository_name, r.repository_endpoint_url
        FROM ({$this->table_name} AS e INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_EXTENSION_TYPES." AS et ON e.extension_type_id = et.id)
        INNER JOIN ".$this->wpdb->prefix.WPRC_DB_TABLE_REPOSITORIES." AS r ON e.repository_id = r.id ".$condition;

        if($extension_type<>'')
        {
            $pq = $this->wpdb->prepare($q, $extension_type);
        }
        else
        {
            $pq = $q;
        }
        $extensions = $this->wpdb->get_results($pq, ARRAY_A);

        $use_key = '';
        if(!$use_extension_name_as_key)
        {
            $extension_type_keys = $this->getExtensionTypeKeys();
        }
        else
        {
            $use_key = 'extension_name';
        }


        for($i=0; $i<count($extensions); $i++)
        {
            $type_name = $extensions[$i]['type_name'];
			if (!isset($extension_tree[$type_name]))
			{
				$extension_tree[$type_name]=array();
			}
            
            // get the name of the key
            if($use_key == '')
            {
                $key = $extension_type_keys[$type_name];
            }
            else
            {
                $key = $use_key;
            }

            // push extension to the tree
            $key_field = $extensions[$i][$key];
            
            if($key_field<>'')
            {
                $extension_tree[$type_name][$key_field] = $extensions[$i];
            } 
        }        

        return $extension_tree;
    }
    
	protected function WP_ThemeToArray($theme)
	{
		$atheme=array();
		
		$atheme['Author']=$theme->Author;
		$atheme['AuthorURI']=$theme->AuthorURI;
		$atheme['Author URI']=$theme->AuthorURI;
		$atheme['Version']=$theme->Version;
		$atheme['Description']=$theme->Description;
		$atheme['DomainPath']=$theme->DomainPath;
		$atheme['Domain Path']=$theme->DomainPath;
		$atheme['Name']=$theme->Name;
		$atheme['Status']=$theme->Status;
		$atheme['Tags']=$theme->Tags;
		$atheme['Template']=$theme->Template;
		$atheme['TextDomain']=$theme->TextDomain;
		$atheme['ThemeURI']=$theme->ThemeURI;
		$atheme['Theme URI']=$theme->ThemeURI;
		$atheme['Stylesheet']=$theme->Stylesheet;
		
		return $atheme;
	}
	
/**
 * Return all extension data by extension type
 * 
 * @param string extension type ( 'plugins' | 'themes' )
 */ 
    public function getFullExtensionsTree($use_extension_name_as_key = false)
    {   
        global $wp_version;
		
		$db_extensions = array();
        $db_extensions = $this->getDBExtensionsTree();
       
        $file_extensions = array();
        $file_extensions['plugins'] = $this->getPlugins();
        $file_extensions['themes'] = $this->getThemes();
        
        $full_extensions = array();
       
        if(count($file_extensions)>0)
        {
            // cycle on extension types
            foreach($file_extensions AS $type => $extensions)
            {
                // if such extension type exists in extensions list from the db
                if(array_key_exists($type, $db_extensions) && count($file_extensions[$type])>0)
                {
                    foreach($file_extensions[$type] AS $key => $file_extension)
                    {
                        /*if (version_compare($wp_version, "3.4", ">=") && $type=='themes')
						{
							$file_extension=$this->WP_ThemeToArray($file_extension);
							$file_extensions[$type][$key]=$file_extension;
						}*/
						
						if(array_key_exists($key, $db_extensions[$type]) && is_array($db_extensions[$type][$key]))
                        {
                            $array_to_merge = array(
                                'extension_name' => $db_extensions[$type][$key]['extension_name'],
                                'extension_path' => $db_extensions[$type][$key]['extension_path'],
                                'extension_slug' => $db_extensions[$type][$key]['extension_slug'],
                                'repository_id' => $db_extensions[$type][$key]['id'],
                                'repository_user' => $db_extensions[$type][$key]['repository_username'],
                                'repository_pass' => $db_extensions[$type][$key]['repository_password'],
                                'repository_salt' => $db_extensions[$type][$key]['repository_authsalt'],
                                'repository_enabled' => $db_extensions[$type][$key]['repository_enabled'],
                                'repository_deleted' => $db_extensions[$type][$key]['repository_deleted'],
                                'repository_endpoint_url' => $db_extensions[$type][$key]['repository_endpoint_url'],
                                'repository_name' => $db_extensions[$type][$key]['repository_name'],
                                'extension_was_installed' => $db_extensions[$type][$key]['extension_was_installed'],
                                'type_name' => $type
                            );
                        }
                        else
                        {
                           $array_to_merge = array(
                                'extension_name' => null,
                                'extension_path' => null,
                                'extension_slug' => null,
                                'repository_id' => null,
                                'repository_user' => null,
                                'repository_pass' => null,
                                'repository_salt' => null,
                                'repository_enabled' => null,
                                'repository_deleted' => null,
                                'repository_endpoint_url' => null,
                                'repository_name' => null,
                                'extension_was_installed' => null,
                                'type_name' => $type
                            ); 
                        }

                        if(is_array($file_extensions[$type][$key]))
                        {
                            $file_extensions[$type][$key] = array_merge($file_extensions[$type][$key], $array_to_merge);
                        }
                    }
                }
            }
        }
		// mark missing extensions as deleted
		$deleted='';
		foreach($db_extensions as $type => $exts)
		{
			foreach ($exts as $key=>$ext)
			{
				$key2=$key;
				/*if ($type=='themes') // maybe stylesheet ??
					$key2=$ext['extension_slug'];*/
				if (!array_key_exists($key2,$file_extensions[$type]))
				{
					$deleted.=",'".$ext['extension_slug']."'";
				}
			}
		}
        // update db table
		if ($deleted<>'')
		{
			$deleted=substr($deleted,1);
			$q="UPDATE $this->table_name SET extension_was_installed=0 WHERE extension_slug IN ($deleted)";
			$this->wpdb->query($q);
		}
		
		/*$new_file_extensions=array();
		if ($use_extension_name_as_key)
		{
			foreach ($file_extensions as $etype=>$extensions)
			{
				if (!isset($new_file_extensions[$etype]))
					$new_file_extensions[$etype]=array();
				
				foreach ($extensions as $key=>$extension)
				{
					$new_file_extensions[$etype][$extension['Name']]=$extension;
				}
			}
			
			return $new_file_extensions;
		}*/
		return $file_extensions;
    }
	
    public function changeExtensionsKey($extensions,$newkey)
	{
		$new_extensions=array();
		foreach ($extensions as $key=>$extension)
		{
			$new_extensions[$extension[$newkey]]=$extension;
		}
		return $new_extensions;
	}
	
	public function findExtensionwithKey($extensions_tree,$type,$findkey,$value)
	{
		foreach ($extensions_tree[$type] as $key=>$extension)
		{
			if ($key==$value || (isset($extension[$findkey]) && $extension[$findkey]==$value))
				return $extension;
		}
		return false;
	}


    public function get_extension_repository( $ext_path ) {
        $repositories_table = $this->wpdb->prefix . WPRC_DB_TABLE_REPOSITORIES;
        $pq = $this -> wpdb -> prepare( "SELECT repos.* FROM $this->table_name ext
            JOIN $repositories_table repos
            ON repos.id = ext.repository_id
            WHERE extension_path = %s", $ext_path );
        return $this -> wpdb -> get_row( $pq );
    }
}
?>