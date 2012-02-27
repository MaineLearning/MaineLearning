<?php
/*
 * Common functions.
 */
define('ICL_COMMON_FUNCTIONS', true);
/**
 * Calculates relative path for given file.
 * 
 * @param type $file Absolute path to file
 * @return string Relative path
 */
function icl_get_file_relpath($file) {
    $is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
    $http_protocol = $is_https ? 'https' : 'http';
    $base_root = $http_protocol . '://' . $_SERVER['HTTP_HOST'];
    $base_url = $base_root;
    $dir = rtrim(dirname($file), '\/');
    if ($dir) {
        $base_path = $dir;
        $base_url .= $base_path;
        $base_path .= '/';
    } else {
        $base_path = '/';
    }
    $relpath = $base_root
            . str_replace(
                    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'])
                    , '', str_replace('\\', '/', dirname($file))
    );
    return $relpath;
}

/**
 * Fix WP's multiarray parsing.
 * 
 * @param type $arg
 * @param type $defaults
 * @return type 
 */
function wpv_parse_args_recursive($arg, $defaults) {
    $temp = false;
    if (isset($arg[0])) {
        $temp = $arg[0];
    } else if (isset($defaults[0])) {
        $temp = $defaults[0];
    }
    $arg = wp_parse_args($arg, $defaults);
    if ($temp) {
        $arg[0] = $temp;
    }
    foreach ($defaults as $default_setting_parent => $default_setting) {
        if (!is_array($default_setting)) {
            if (!isset($arg[$default_setting_parent])) {
                $arg[$default_setting_parent] = $default_setting;
            }
            continue;
        }
        if (!isset($arg[$default_setting_parent])) {
            $arg[$default_setting_parent] = $defaults[$default_setting_parent];
        }
        $arg[$default_setting_parent] = wpv_parse_args_recursive($arg[$default_setting_parent], $defaults[$default_setting_parent]);
    }
    
    return $arg;
}