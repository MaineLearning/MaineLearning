<?php
/**
 *  CRED_Loader
 * 
 *  This class is responsible for loading/including all files and getting instances of all objects
 *  in an efficient and abstract manner, abstracts all hardcoded paths and dependencies and manages singleton instances
 */
 
require_once(dirname(__FILE__).'/config.php');

// make sure it is unique
if (!class_exists('CRED_Loader'))
{ 
class CRED_Loader
{
    // some configuration constants
    const _LOCALE_='wp-cred';
    const _PREFIX_='%%PREFIX%%';
    const _SUFFIX_='%%SUFFIX%%';
    const _PARENT_='%%PARENT%%';
    const _INI_='INI';
    const _CLASS_='CLASS';
    const _CONTROLLER_='CONTROLLER';
    const _MODEL_='MODEL';
    const _VIEW_='VIEW';
    const _TABLE_='TABLE';
    const _LIST_TABLE_='TABLE';
    const _TEMPLATE_='TEMPLATE';
    const _THIRDPARTY_='THIRDPARTY';
    
    // pool of singleton instances, implement singleton factory
    private static $__instances__ = array();
    
    // some dependencies here
    private static $__dependencies__=array();
    
    public static function init($deps=false)
    {
        // init dependencies paths, if any
        self::$__dependencies__=array(
            self::_CONTROLLER_=>array(
                self::_PREFIX_ => CRED_NAME.'_',
                
                self::_SUFFIX_ => '_Controller',
                
                self::_PARENT_ => array(
                    'class' => 'CRED_Abstract_Controller',
                    'path' => CRED_CONTROLLERS_PATH.'/Abstract_Controller.php'
                ),
                'Forms' => array(
                    array(
                        'class' => 'CRED_Forms_Controller',
                        'path' => CRED_CONTROLLERS_PATH.'/Forms_Controller.php'
                    )
                ),
                'Posts' => array(
                    array(
                        'class' => 'CRED_Posts_Controller',
                        'path' => CRED_CONTROLLERS_PATH.'/Posts_Controller.php'
                    )
                ),
                'Settings' => array(
                    array(
                        'class' => 'CRED_Settings_Controller',
                        'path' => CRED_CONTROLLERS_PATH.'/Settings_Controller.php'
                    )
                ),
                'Generic_Fields' => array(
                    array(
                        'class' => 'CRED_Generic_Fields_Controller',
                        'path' => CRED_CONTROLLERS_PATH.'/Generic_Fields_Controller.php'
                    )
                )
            ),
            self::_MODEL_=>array(
                self::_PREFIX_ => CRED_NAME.'_',
                
                self::_SUFFIX_ => '_Model',
                
                self::_PARENT_ => array(
                    'class' => 'CRED_Abstract_Model',
                    'path' => CRED_MODELS_PATH.'/Abstract_Model.php'
                ),
                'Forms' => array(
                    array(
                        'class' => 'CRED_Forms_Model',
                        'path' => CRED_MODELS_PATH.'/Forms_Model.php'
                    )
                ),
                'Settings' => array(
                    array(
                        'class' => 'CRED_Settings_Model',
                        'path' => CRED_MODELS_PATH.'/Settings_Model.php'
                    )
                ),
                'Fields' => array(
                    array(
                        'class' => 'CRED_Fields_Model',
                        'path' => CRED_MODELS_PATH.'/Fields_Model.php'
                    )
                )
            ),
            self::_LIST_TABLE_=>array(
                self::_PREFIX_ => CRED_NAME.'_',
                
                self::_SUFFIX_ => '_List_Table',
                
                self::_PARENT_ => array(
                    'class' => 'WP_List_Table',
                    'path' => ABSPATH.'/wp-admin/includes/class-wp-list-table.php'
                ),
                'Forms' => array(
                    array(
                        'class' => 'CRED_Forms_List_Table',
                        'path' => CRED_TABLES_PATH.'/Forms_List_Table.php'
                    )
                ),
                'Custom_Fields' => array(
                    array(
                        'class' => 'CRED_Custom_Fields_List_Table',
                        'path' => CRED_TABLES_PATH.'/Custom_Fields_List_Table.php'
                    )
                )
            ),
            self::_CLASS_=>array(
                self::_PREFIX_ => CRED_NAME.'_',
                
                self::_SUFFIX_ => '',
                
                'CRED' => array(
                    array(
                        'class' => 'CRED_CRED',
                        'path' => CRED_CLASSES_PATH.'/CRED.php'
                    )
                ),
                'Form_Builder' => array(
                    array(
                        'class' => 'CRED_Form_Builder',
                        'path' => CRED_CLASSES_PATH.'/Form_Builder.php'
                    )
                ),
                'Form_Processor' => array(
                    array(
                        'class' => 'CRED_Form_Processor',
                        'path' => CRED_CLASSES_PATH.'/Form_Processor.php'
                    )
                ),
                'XML_Processor' => array(
                    array(
                        'class' => 'CRED_XML_Processor',
                        'path' => CRED_CLASSES_PATH.'/XML_Processor.php'
                    )
                ),
                'Mail_Handler' => array(
                    array(
                        'class' => 'CRED_Mail_Handler',
                        'path' => CRED_CLASSES_PATH.'/Mail_Handler.php'
                    )
                ),
                'Shortcode_Parser' => array(
                    array(
                        'class' => 'CRED_Shortcode_Parser',
                        'path' => CRED_CLASSES_PATH.'/Shortcode_Parser.php'
                    )
                ),
                'Router' => array(
                    array(
                        'class' => 'CRED_Router',
                        'path' => CRED_CLASSES_PATH.'/Router.php'
                    )
                ),
                'Ajax_Router' => array(
                    array(
                        'class' => 'CRED_Ajax_Router',
                        'path' => CRED_CLASSES_PATH.'/Ajax_Router.php'
                    )
                )
            ),
            self::_THIRDPARTY_=>array(
                self::_PREFIX_ => '',
                
                self::_SUFFIX_ => '',
                
                'MyZebra_Form' => array(
                    array(
                        'class' => 'MyZebra_Form',
                        'path' => CRED_THIRDPARTY_PATH.'/zebra_form/MyZebra_Form.php'
                    )
                ),
                'MyZebra_Parser' => array(
                    array(
                        'class' => 'MyZebra_Parser',
                        'path' => CRED_THIRDPARTY_PATH.'/zebra_form/MyZebra_Parser.php'
                    )
                )
            ),
            self::_VIEW_=>array(
                self::_PREFIX_ => '',
                
                self::_SUFFIX_ => '',
                
                'custom_fields' => array(
                    array(
                        'path' => CRED_VIEWS_PATH.'/custom_fields.php'
                    )
                ),
                'forms' => array(
                    array(
                        'path' => CRED_VIEWS_PATH.'/forms.php'
                    )
                ),
                'settings' => array(
                    array(
                        'path' => CRED_VIEWS_PATH.'/settings.php'
                    )
                ),
                'help' => array(
                    array(
                        'path' => CRED_VIEWS_PATH.'/help.php'
                    )
                )
            )/*,
            self::_TEMPLATE_=>array(
                // not needed, uses other function
            )*/
        );
    }
    
    // get full class name, specified by $classname
    /*private static function getQualifiedClassName($class, $type)
    {
        if ( isset(self::$__dependencies__[$type]) )
            $class=self::$__dependencies__[$type][self::_PREFIX_].$class.self::$__dependencies__[$type][self::_SUFFIX_];
        return $class;
    }*/
    
    // get full path of class specified by $file, abstract all hardcoded paths here
    /*private static function getQualifiedFileName($file, $type)
    {
        switch ($type)
        {
            case self::_CONTROLLER_:
                return CRED_CONTROLLERS_PATH.'/'.$file.'.php';
            case self::_MODEL_:
                return CRED_MODELS_PATH.'/'.$file.'.php';
            case self::_VIEW_:
                return CRED_VIEWS_PATH.'/'.$file.'.php';
            case self::_TABLE_:
            case self::_LIST_TABLE_:
                return CRED_TABLES_PATH.'/'.$file.'.php';
            case self::_TEMPLATE_:
                return CRED_TEMPLATES_PATH.'/'.$file.'.tpl.php';
            case self::_CLASS_:
                return CRED_CLASSES_PATH.'/'.$file.'.php';
            default:
                return $file;
        }
    }*/
    
    // include a php file
    private static function includeFile($path, $once=true)
    {
        if(!file_exists($path))
        {
            printf(__('File "%s" doesn\'t exist!', self::_LOCALE_), $path);
            return false;
        }
        if ($once)
            require_once($path);
        else
            require($path);
    }

    // import a php class
    private static function importClass($class, $path)
    {
        if ( !class_exists( $class ) )
            self::includeFile( $path );    
    }
    
    // load dependencies needed by some classes, abstract all hardcoded dependencies here
    /*private static function loadDependencies($type)
    {
        switch ($type)
        {
            case self::_MODEL_:
                if ( !class_exists( self::getQualifiedClassName('Abstract_Model', $type) ) )
                    self::includeFile( self::getQualifiedFileName('Abstract_Model', $type) );
                break;
            case self::_CONTROLLER_:
                if ( !class_exists( self::getQualifiedClassName('Abstract_Controller', $type) ) )
                    self::includeFile( self::getQualifiedFileName('Abstract_Controller', $type) );
                break;
            case self::_TABLE_:
            case self::_LIST_TABLE_:
                if( !class_exists('WP_List_Table') )
                    self::includeFile( ABSPATH.'/wp-admin/includes/class-wp-list-table.php' );
                break;
        }
    }*/
    
    // load a class with dependencies if needed
    public static function load($qclass)
    {
        /*self::loadDependencies($type_path);
        
        switch ($type_path)
        {
            case self::_VIEW_:
                self::includeFile( self::getQualifiedFileName($class, $type_path) );
                break;
            case self::_TEMPLATE_:
                self::includeFile( self::getQualifiedFileName($class, $type_path), false );
                break;
            case self::_TABLE_:
            case self::_LIST_TABLE_:
            case self::_MODEL_:
            case self::_CONTROLLER_:
            case self::_CLASS_:
                if ( !class_exists( self::getQualifiedClassName($class, $type_path) ) )
                    self::includeFile( self::getQualifiedFileName($class, $type_path) );
                break;
            default: // arbitrary include class/file
                if ( empty($class) || !class_exists( $class ) )
                    self::includeFile( $type_path );
                break;
        }*/
        
        list($type, $class)=explode('/',$qclass, 2);
        if ( isset(self::$__dependencies__[$type]) )
        {
            $_type=&self::$__dependencies__[$type];
            if ( isset($_type[$class]) )
            {
                if ( isset($_type[self::_PARENT_]) && is_array($_type[self::_PARENT_]) )
                {
                    $_parent=&$_type[self::_PARENT_];
                    self::importClass($_parent['class'], $_parent['path']);
                }
                $_class=&$_type[$class];
                foreach ($_class as $_dep)
                {
                    if (isset($_dep['class']))
                        self::importClass($_dep['class'], $_dep['path']);
                    else
                        self::includeFile($_dep['path']);
                }
            }
        }
        else
        {
            self::includeFile($qclass);
        }
    }
    
    // singleton factory pattern, to enable singleton in php 5.2, etc..
    // http://stackoverflow.com/questions/7902586/extend-a-singleton-with-php-5-2-x
    // http://stackoverflow.com/questions/7987313/how-to-subclass-a-singleton-in-php
    public static function get($qclass, $singleton=true)
    {
        $instance = false;
        list($type, $class)=explode('/', $qclass, 2);
        if ( isset(self::$__dependencies__[$type]) && isset(self::$__dependencies__[$type][$class]) )
        {
            $class=end(array_values(self::$__dependencies__[$type][$class]));
            $class=isset($class['class'])?$class['class']:$qclass;
        }
        
        if ( !$singleton || ( $singleton && !isset(self::$__instances__[$class]) ) )
        {
            self::load($qclass);
            if ( class_exists( $class) )
                $instance = new $class();
            else
                $instance = false;
            
            if ($singleton)
                self::$__instances__[$class] = $instance;
        }
        if ($singleton)
        {
            $instance = self::$__instances__[$class];
        }
        return $instance;
    }
    
    public static function renderTemplate($___template_name, array $___args=array(), $___template_dir='', $___once=false)
    {
        $___template_dir_path = ($___template_dir <> '') ? $___template_dir.'/' : '';
        $___template_path = CRED_TEMPLATES_PATH.'/'.$___template_dir_path.$___template_name.'.tpl.php';
        if (!file_exists($___template_path))
        {
             printf(__('File "%s" doesn\'t exist!', self::_LOCALE_), $___template_path);
            return false;
        }
        
        if ($___once)
        {
            ob_start();
                extract($___args);
                include_once($___template_path);
            return ob_get_clean();
        }
        ob_start();
            extract($___args);
            include($___template_path);
        return ob_get_clean();
    }
    
    public static function getHelpSettings()
    {
        /*$file_handle = fopen(CRED_HELP_DIR."/cred-help.ini", "rb");

        while (!feof($file_handle) ) 
        {
            $line_of_text = fgets($file_handle);
            $parts = array_map('trim',explode($sep, $line_of_text));
            if (
                (isset($parts[0]) && $parts[0]!='')
                    &&
                (isset($parts[1]) && $parts[1]!='')
            )
                $help_settings_array[$parts[0]]=$parts[1];
        }
        fclose($file_handle);*/
        include CRED_INI_PATH."/help.ini.php";
        $vars=get_defined_vars();
        // return first defined var (only one)
        return reset($vars);
    }
}
}
?>