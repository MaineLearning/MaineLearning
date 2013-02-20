<?php
/*
 * Debug code.
 */

/**
 * Admin footer.
 * 
 * @global type $wpcf_access 
 */
function wpcf_access_debug($plain = false) {
    if (WPCF_ACCESS_DEBUG || isset($_GET['debug'])) {
        global $wpcf_access;
        $clone = clone $wpcf_access;

        if ($plain) {
            ob_start();
        }

        echo '<div style="margin:20px; padding:20px; background-color:#F5F5F5; border: 2px dashed #9E9E9E"><strong>Types Access DEBUG</strong><br /><br />


';
        echo '<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>RULES</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r($clone->rules);
        unset($clone->rules);
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>HOOKS WITH ARGS</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r($clone->debug_hooks_with_args);
        unset($clone->debug_hooks_with_args);
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>HOOKS MATCHED</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r($clone->debug);
        unset($clone->debug);
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>HOOKS ALL</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r($clone->debug_all_hooks);
        unset($clone->debug_all_hooks);
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>SETTINGS</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r($clone->settings);
        unset($clone->settings);
        echo '</pre></div>';

        if (!empty($clone->errors)) {
            echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px; color:Red;"><strong><em>ERRORS</em></strong></div><div style="display:none;"><br /><br /><pre>';
            print_r($clone->errors);
            echo '</pre></div>';
        }
        unset($clone->errors);

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>REST</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r($clone);
        unset($clone);
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>WP Query</em></strong></div><div style="display:none;"><br /><br /><pre>';
        global $wp_query;
        print_r($wp_query);
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>WP User</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r(wp_get_current_user());
        echo '</pre></div>';

        echo '</div>';
        
        if ($plain) {
            $out = ob_get_contents();
            ob_end_clean();
            echo '<pre>'; echo strip_tags($out); echo '</pre>';
        }
    }
}