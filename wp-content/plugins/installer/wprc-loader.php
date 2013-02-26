<?php
/**
 * WPRC_Loader
 * 
 * This class is responsible for including of all files and getting of instances of all objects
 *
 */
class WPRC_Loader
{
 /**
  * Include file
  * 
  * @param string file name
  */    
    private static function includeFile($file_name)
    {
        if(!file_exists($file_name))
        {
            printf(__('File "%s" doesn\'t exist!','installer'), $file_name);
            return false;
        }

        include_once($file_name);
    }

/**
 * Include file of WP Repository Client plugin
 */     
    public static function includePluginFile($file_name)
    {
        $file_name = WPRC_PLUGIN_PATH.'/'.$file_name;
        self::includeFile($file_name);
    }

/**
 * Include plugin.php file
 */ 
    public static function includeWPPluginFile()
    {
        self::includeFile(ABSPATH.'/wp-admin/includes/plugin.php');
    }
    
    public static function includeDebug()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-debug.php');    
    }
    
    public static function includeClass($file)
    {
        self::includeFile(WPRC_CLASSES_DIR.'/'.$file);    
    }
/**
 * Include template by name
 *
 * @param string template name
 */    
    public static function includeTemplate($template_name, $template_dir='')
    {
        $template_dir_path = ($template_dir <> '') ? $template_dir.'/' : '';
        $template_path = WPRC_TEMPLATES_DIR.'/'.$template_dir_path.$template_name.'.tpl.php';
        
        self::includeFile($template_path);
    }

/**
 * Include page by name
 *
 * @param string page name
 * @param string page display mode
 */     
    public static function includePage($page_name)
    {
        $page_path = WPRC_PAGES_DIR.'/'.$page_name.'.php';
        
        self::includeFile($page_path);
    }

 
 /**
  * Include admin panel header
  * 
  * @param bool disable admin panel menus
  */    
    public static function includeAdminTop($disable_menus = false)
    {
        $disable_menus_css = '<style type="text/css">   #adminmenuwrap, #screen-meta-links {display:none;}   </style>';
        
        self::includeFile(ABSPATH . 'wp-admin/admin-header.php');
        
        if($disable_menus)
        {
            echo $disable_menus_css;
        }
    }

    public static function includeAdminBottom($disable_menus = false)
    {
        
        self::includeFile(ABSPATH . 'wp-admin/admin-footer.php');
    }

/**
 * Get istance of RepositoryConnector
 * 
 */    
    public static function getRepositoryConnector()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-repository-connector.php');
        //return new WPRC_RepositoryConnector();
        return /*$model_class_name::getInstance();*/call_user_func(array('WPRC_RepositoryConnector', 'getInstance'));
    }
    
/**
 * IncludeWPRC_UrlAnalyzer class
 * 
 */    
    public static function includeUrlAnalyzer()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-url-analyzer.php');
    }
    
/**
 * Include RepositoryClient class
 * 
 */    
    public static function includeRepositoryClient()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-installer.php');
    }
    
    public static function includeAdminNotifier()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-admin-notifier.php');
    }

    /*public static function includeSecurity()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-security.php');
    }*/

/**
 * Include wordpress http.php file
 */   
    public static function includeWordpressHttp()
    {
        self::includeFile(ABSPATH.WPINC.'/http.php');
    }
 
 /**
  * Include LanguageContainer
  */    
    public static function includeLanguageContainer()
    {
        self::includePluginFile('languages/wprc-language-container.php');
    }

/**
 * Include LanguageManager
 */     
    public static function includeLanguageManager()
    {
        self::includePluginFile('languages/wprc-language-manager.php');
    }

    public static function includeExtensionDataManager()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-extension-data-manager.php');
    }

/**
 * Get WPRC_ExtensionDataManager instance
 * 
 * @param string extension type (plugin|theme)
 * @param string extension name
 * 
 * @return object WPRC_ExtensionDataManager instance
 */     
    public static function getExtensionDataManager($extension_type, $extension_name)
    {
        self::includeExtensionDataManager();
        return new WPRC_ExtensionDataManager($extension_type, $extension_name);
    }

/**
 * Include ExtensionTimer class
 */      
    public static function includeExtensionTimer()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-extension-timer.php');
    }

/**
 * Include plugin router
 */     
    public static function includeRouter()
    {
        self::includePluginFile('core/wprc-router.php');
    }

/**
 * Form class name from the file name
 * 
 * e.g. 'repository-reporter' => 'RepositoryReporter'
 * 
 * @access private
 * @param string file name
 * 
 * @return string class name
 */ 
    private static function getClassNameFromFileName($file_name)
    {
        if($file_name=='')
        {
            return false;
        }
        
        $name_parts = explode('-',$file_name);
        
        for($i=0; $i<count($name_parts); $i++)
        {
            $name_parts[$i] = ucfirst($name_parts[$i]);
        }
        
        return implode('',$name_parts);
    }
    

/**
 * Return instance of controller by name
 * 
 * @param string short controller name (without 'controller-' prefix)
 * 
 * @return object Controller instance
 */     
    public static function getController($short_file_name)
    {   
        $controller_class_name = 'WPRC_Controller_'.self::getClassNameFromFileName($short_file_name); 

        if(!$controller_class_name)
        {
            return false;
        }
        
        $controller_file_name = 'wprc-controller-'.$short_file_name;
        $controller_path = 'controllers/'.$controller_file_name.'.php';
        
        self::includePluginFile('core/wprc-controller.php');
        self::includePluginFile($controller_path);
        
        if(!class_exists($controller_class_name))
        {
            return false;
        }
        
        //return new $controller_class_name();
        return /*$model_class_name::getInstance();*/call_user_func(array($controller_class_name, 'getInstance'));
    }
    
/**
 * Include ExtensionModel class
 */      
    public static function getExtensionModel()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-extension-model.php');
        //return new WPRC_ExtensionModel();
        return /*$model_class_name::getInstance();*/call_user_func(array('WPRC_ExtensionModel', 'getInstance'));
    }
    
    public static function includeRequester()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/requesters/wprc-requester.php');
    }
    
    public static function getRequester($short_file_name)
    {
        $reporter_class_name = 'WPRC_'.self::getClassNameFromFileName($short_file_name); 

        if(!$reporter_class_name)
        {
            return false;
        }
        
        $reporter_file_name = 'wprc-'.$short_file_name;
        $reporter_path = WPRC_CLASSES_DIR.'/requesters/'.$reporter_file_name.'.php';
        
        self::includeRequester();
        self::includeFile($reporter_path);
     
        if(!class_exists($reporter_class_name))
        {
            return false;
        }
        
        return new $reporter_class_name();
    }

/**
 * Include class-wp-list-table file
 */     
    public static function includeWPListTable()
    {
        if(!class_exists('WP_List_Table'))
        {
            $file_name = ABSPATH.'/wp-admin/includes/class-wp-list-table.php';
            self::includeFile($file_name);
        }
    }

    public static function includeWPThemeListTable()
    {
        if(!class_exists('WP_Theme_List_Table'))
        {
            $file_name = ABSPATH.'/wp-admin/includes/class-wp-themes-list-table.php';
            self::includeWPListTable();
            self::includeFile($file_name);
        }
    }

/**
 * Include class-wp-list-table file
 */     
    public static function includeWPThemeInstallListTable()
    {
        if(!class_exists('WP_Theme_Install_List_Table'))
        {
            $file_name = ABSPATH.'/wp-admin/includes/class-wp-theme-install-list-table.php';
            self::includeWPThemeListTable();
            self::includeFile($file_name);
        }
    }

    public static function getWPRCThemeInstallListTable($table_name)
    {
       $class_name = self::getClassNameFromFileName($table_name);
       $class_name = 'WPRC_'.$class_name.'_List_Table';
       
       $table_name = 'wprc-'.$table_name.'-list-table';
       
       self::includeWPThemeInstallListTable();
       self::includeListTable($table_name);
       
       return new $class_name();
    }

/**
 * Include WPRC list table by name
 * 
 * @param string table list name
 */    
    public static function includeListTable($table_name)
    {
        self::includeFile(WPRC_TABLES_DIR.'/'.$table_name.'.php');
    }
    
    
    public static function getListTable($table_name)
    {
       $class_name = self::getClassNameFromFileName($table_name);
       $class_name = 'WPRC_'.$class_name.'_List_Table';
       
       $table_name = 'wprc-'.$table_name.'-list-table';
       
       self::includeWPListTable();
       self::includeListTable($table_name);
       
       return new $class_name();
    }
    
 /**
 * Include WPRC model by name
 * 
 * @param string model name
 */    
    public static function getModel($short_file_name)
    {
        $model_class_name = 'WPRC_Model_'.self::getClassNameFromFileName($short_file_name); 

        if(!$model_class_name)
        {
            return false;
        }
        
        $model_file_name = 'wprc-model-'.$short_file_name;
        $model_path = WPRC_MODELS_DIR.'/'.$model_file_name.'.php';
        
        self::includeModel();
        self::includeFile($model_path);
        
        if(!class_exists($model_class_name))
        {
            return false;
        }
        
        //return new $model_class_name();
        return /*$model_class_name::getInstance();*/call_user_func(array($model_class_name, 'getInstance'));
    }

    private static function includeModel()
    {
        self::includePluginFile('core/wprc-model.php');
    }

    public static function getBasicModel()
    {
       self::includeModel();
       //return new WPRC_Model();
        return /*$model_class_name::getInstance();*/call_user_func(array('WPRC_Model', 'getInstance'));
    }


/**
 * Include parent class of all extensions api
 */     
    public static function includeExtensionsApi()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/api/wprc-extensions-api.php');
    }

/**
 * Include api classes by type
 * 
 * @param string extension type 
 */     
    public static function includeExtensionsApiByType($extension_type)
    {
        self::includeFile(WPRC_CLASSES_DIR.'/api/wprc-'.$extension_type.'-api.php');
    }

/**
 * Include extensions api decorator
 */     
    public static function includeExtensionsApiDecorator()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/api/wprc-extensions-api-decorator.php');
    }

    /**
     * Return instance of request cacher class
     */
    public static function getRequestCacher()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-request-cacher.php');
        return new WPRC_RequestCacher();
    }
    
/**
 * Return class which decorate the extensions api classes
 * 
 * @param string extension type
 */     
    public static function getExtensionsApi($extension_type)
    {  
        if(!isset($extension_type))
        { 
            return false;
        }
        
        // include parent class of all api's
        self::includeExtensionsApi(); 
        
        // include concrete api class
        self::includeExtensionsApiByType($extension_type);
        
        // include decorator of the api classes 
        self::includeExtensionsApiDecorator();
        
        // check concrete api class existing
        $ucf_extension_type = ucfirst($extension_type);
        $api_class_name = 'WPRC_'.$ucf_extension_type.'_API';
        
        // check extension api class existing
        if(!class_exists($api_class_name))
        {
            return false;
        }
        
        // get instance of extensions api class
        $api_class_instance = new $api_class_name();
        
        return new WPRC_Extensions_API_Decorator($api_class_instance);
    }
    
    public static function includeMultipleSourcesPaginator()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-multiple-sources-paginator.php');
    }

    public static function includeSiteEnvironment()
    {
        self::includeFile(WPRC_CLASSES_DIR.'/wprc-site-environment.php');
    }
}
?>