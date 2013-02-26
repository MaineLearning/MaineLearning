<?php
class WPRC_Router
{
    public static function execute($controller = '', $action = '')
    {
        if(($controller<>'' && $action=='') || ($controller=='' && $action<>''))
        { 
            return false;
        }

       $do = ($action=='') ? self::getParam('wprc_action') : $action;
        $controller_name = ($controller=='') ? self::getParam('wprc_c') : $controller;         

        if($do<>'')
        {
            if($controller_name)
            {  
                $controllerObject = WPRC_Loader::getController($controller_name);

                if($controllerObject)
                { 
                    if(method_exists($controllerObject, $do)) // method exists
                    {
                        if (@is_callable(array($controllerObject, $do))) //is public method
                            call_user_func_array(array($controllerObject, $do), array($_GET, $_POST));
                    }
                }
            }
            exit;
        }
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