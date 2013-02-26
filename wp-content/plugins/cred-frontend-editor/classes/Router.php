<?php
class CRED_Router
{
    const _PREFIX_=CRED_NAME;
    
    public static function init()
    {
        // RESTful service only for logged users quasi-wp-admin
        if ( current_user_can(CRED_CAPABILITY) )
            add_filter( 'wp_headers', array('CRED_Router', 'handleRoute'), 100 , 1 );
    }
    
    public static function handleRoute($args=null)
    {
        if ( current_user_can(CRED_CAPABILITY) )
            self::execute();
        return $args;
    }
    
    private static function execute($controller = false, $action = false)
    {
        global $wp_query;
        
        if ( !current_user_can(CRED_CAPABILITY) && !isset($_GET['ajax']) ) return;
        
        // process pseudo-admin routes, needs care here to handle check correctly !!
        if ( !$wp_query->query || $wp_query->is_404 )
        {
            if ( !$controller || !$action )
            { 
                list($controller, $action)=self::processRoute();
                if ( !$controller || !$action )
                    return; // no route to handle
            }

            $controllerObject = CRED_Loader::get("CONTROLLER/$controller", false);

            if($controllerObject)
            { 
                if(method_exists($controllerObject, $action))
                {
                    if (is_callable(array($controllerObject, $action)))
                    {
                        call_user_func_array(array($controllerObject, $action), array($_GET, $_POST));
                        $wp_query->is_404=false;
                    }
                }
            }
        }
    }
    
    private static function processRoute()
    {
        $path=array();
        if ( isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME']))
        {
            $requestURI = explode('/', parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH));
            $base_url = explode('/', parse_url(home_url('index.php'),  PHP_URL_PATH));
            //$requestURI = explode('?', $_SERVER['REQUEST_URI']);
            //$requestURI = explode('/', $requestURI[0]);
            //$requestURI = str_replace(home_url('index.php'))
            //$scriptName = explode('/', $_SERVER['SCRIPT_NAME']);

            for($i= 0;$i < sizeof($base_url)/*sizeof($scriptName)*/;$i++)
            {
                if ( $requestURI[$i] == $base_url[$i]/*$scriptName[$i]*/ )
                {
                    unset($requestURI[$i]);
                }
            }
            $path = array_values($requestURI);
        }
        elseif ( isset($_SERVER['PATH_INFO']) )
        {
            $path=explode('?', $_SERVER['PATH_INFO']);
            $path=explode('/', $path[0]); 
            unset($path[0]); // remove item at index 0
            $path = array_values($path);
        }
        
        $controller=false;
        $action=false;
        // actually routes as are constructed , just use the wp-admin folder, but not the admin section
        if ( 
            //isset($path[0]) && 'wp-admin'==$path[0] && 
            // use wp-admin/index.php to support multisite
            isset($path[0]) && self::_PREFIX_==$path[0] 
            )
        {
            // handle requesst as it includes our prefix
            $controller = (isset($path[1])&&!empty($path[1]))?$path[1]:false;
            $action = (isset($path[2])&&!empty($path[2]))?$path[2]:false;
        }
        
        return array($controller, $action);
    }
    
    private static function getParam($param_name)
    {
        if(!array_key_exists($param_name, $_GET))
        {
            return false;
        }
      
        $param = strip_tags($_GET[$param_name]);
        
        if(!preg_match('/^[-a-zA-Z_]*/',$param))
        {
            return false;
        }
        
        return $param;  
    }
}
?>