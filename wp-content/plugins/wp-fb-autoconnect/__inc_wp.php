<?php

//If you've moved your wp-content folder and this plugin isn't working,
//you can specify the ABSOLUTE PATH to wp-blog-header.php here.
//By default, it lives in the root wordpress directory.
$searchFile = "wp-blog-header.php";

/************Don't edit below this line*************/

//Try to include the header by searching UP the directory structure.
for($i = 0; $i < 10; $i++)
{
    if( file_exists($searchFile) )
    {
        require_once($searchFile);
        break;    
    }
    $searchFile = "../" . $searchFile;
}

//If it couldn't be found, try one last possible scenario: wp-content was moved above a "wordpress" folder
$searchFile = "../../../wordpress/wp-blog-header.php";
if(file_exists($searchFile)) require_once($searchFile);

//Make sure we got it
if( !defined('WPINC') )
{
    $message = "Failed to locate wp-blog-header.php.<br/>".
               "If you're seeing this message, it probably means you moved your wp-content folder somewhere non-default;<br/>".
               "Please open the file \"__inc_wp.php\" in the WP-FB-AutoConnect plugin directory, and specify the path to your wp-blog-header.php."; 
    if( function_exists('j_die') )  j_die($message);
    else                            die($message);
}

//Include the User Registration code so we can use wp_insert_user
if( !function_exists('wp_insert_user') )
    require_once(ABSPATH . WPINC . '/registration.php');
if( !function_exists('wp_insert_user') )
{
    if( function_exists('j_die') ) j_die("Failed to include registration.php.");
    else                           die(  "Failed to include registration.php.");
}


?>