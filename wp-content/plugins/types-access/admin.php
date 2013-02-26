<?php
/*
 * Admin functions.
 */
require_once WPCF_ACCESS_INC . '/menu.php';
require_once WPCF_ACCESS_INC . '/post.php';

/**
 * Saves Access settings. 
 */
function wpcf_access_save_settings() {
    if (isset($_POST['_wpnonce'])
            && wp_verify_nonce($_POST['_wpnonce'], 'wpcf-access-edit')) {
            
        $access_bypass_template="<div class='error'><p>".__("<strong>Warning:</strong> The %s <strong>%s</strong> uses the same name for singular name and plural name. Access can't control access to this object. Please use a different name for the singular and plural names.", 'wpcf-access')."</p></div>";
        $access_notices='';
        $_post_types=wpcf_object_to_array( get_post_types(array('show_ui' => true), 'objects') );
        $_taxonomies=wpcf_object_to_array( get_taxonomies(array('show_ui' => true), 'objects') );
        
        if (!empty($_POST['types_access']['types'])) {
            $settings = get_option('wpcf-custom-types', array());
            $settings_access = /*get_option('wpcf-access-types',*/ array();//);
            $caps = wpcf_access_types_caps_predefined();
            foreach ($_POST['types_access']['types'] as $type => $data) {
                $mode = isset($data['mode']) ? $data['mode'] : 'not_managed';
                // Use saved if any and not_managed
                if ($data['mode'] == 'not_managed'
                        && isset($settings[$type]['_wpcf_access_capabilities'])) {
                    $data = $settings[$type]['_wpcf_access_capabilities'];
                }
                $data['mode'] = $mode;
                $data['permissions'] = wpcf_access_parse_permissions($data,
                        $caps);
                
                if (!wpcf_is_object_valid('type',$_post_types[$type])) {
                    $data['mode'] = 'not_managed';
                    $access_notices.=sprintf($access_bypass_template,__('Post Type','wpcf-access'),$_post_types[$type]['labels']['singular_name']);
                }
                if (isset($settings[$type])) {
                    $settings[$type]['_wpcf_access_capabilities'] = $data;
                } else {
                    $settings_access[$type] = $data;
                    //unset($settings[$type]);
                }
            }
            update_option('wpcf-custom-types', $settings);
            update_option('wpcf-access-types', $settings_access);
        }
        if (!empty($_POST['types_access'])) {
            $third_party = get_option('wpcf-access-3rd-party', array());
            foreach ($_POST['types_access'] as $area_id => $area_data) {
                // Skip Types
                if ($area_id == 'types' || $area_id == 'tax') {
                    unset($third_party[$area_id]);
                    continue;
                }
                foreach ($area_data as $group => $group_data) {
                    // Set user IDs
                    $data['permissions'] = wpcf_access_parse_permissions($group_data,
                            $caps, true);
                    $third_party[$area_id][$group] = $data;
                    $third_party[$area_id][$group]['mode'] = 'permissions';
                }
            }
            update_option('wpcf-access-3rd-party', $third_party);
        }
        if (isset($_POST['types_access']['tax'])) {
            $settings = get_option('wpcf-custom-taxonomies', array());
            // Taxonomies settings for non-created by Types
            $settings_access = /*get_option('wpcf-access-taxonomies',*/ array(); //);
            $caps = wpcf_access_tax_caps();
            
            foreach ($_POST['types_access']['tax'] as $tax => $data) {
                if (!isset($data['mode'])) {
                    $data['mode'] = 'permissions';
                }
                if (!isset($data['not_managed'])) {
                    $data['mode'] = 'not_managed';
                }
                $data['mode'] = wpcf_access_get_taxonomy_mode($tax,
                        $data['mode']);
                // Prevent overwriting
                if ($data['mode'] == 'not_managed' || $data['mode'] == 'follow') {
                    if (isset($settings_access[$tax]) && isset($settings_access[$tax]['permissions']))
                        $data['permissions'] = $settings_access[$tax]['permissions'];
                }
                $data['permissions'] = wpcf_access_parse_permissions($data,
                        $caps);
                
                /*if (isset($settings[$tax])) {
                    $settings[$tax]['_wpcf_access_capabilities'] = $data;
                    // ????? IS THIS ERROR/TYPO ????
                    //unset($settings[$type]);
                } else {
                    $settings_access[$tax] = $data;
                }*/
                
                if (!wpcf_is_object_valid('taxonomy',$_taxonomies[$tax])) {
                    $data['mode'] = 'not_managed';
                    $access_notices.=sprintf($access_bypass_template,__('Taxonomy','wpcf-access'),$_taxonomies[$tax]['labels']['singular_name']);
                }
                if (isset($settings[$tax])) {
                    $settings[$tax]['_wpcf_access_capabilities'] = $data;
                } else {
                    $settings_access[$tax] = $data;
                    //unset($settings[$type]);
                }
            }
            update_option('wpcf-custom-taxonomies', $settings);
            update_option('wpcf-access-taxonomies', $settings_access);
        }
        if (!empty($_POST['roles'])) {
            foreach ($_POST['roles'] as $role => $level) {
                $role_data = get_role($role);
                if (!empty($role)) {
                    for ($index = 0; $index < 11; $index++) {
                        if ($index <= intval($level)) {
                            $role_data->add_cap('level_' . $index, 1);
                        } else {
                            $role_data->remove_cap('level_' . $index);
                        }
                    }
                }
            }
        }
        if (defined('DOING_AJAX')) {
            do_action('types_access_save_settings');
            echo __('Access rules saved', 'wpcf_access').$access_notices;
            die();
        }
    }
}

/**
 * Parses submitted data.
 * 
 * @param type $data
 * @return type 
 */
function wpcf_access_parse_permissions($data, $caps, $custom = false) {
    $permissions = array();
    // TODO Monitor this (fails sometimes as 3.5)
    if (empty($data['__permissions'])) {
        return $permissions;
    }
    foreach ($data['__permissions'] as $cap => $data_cap) {
        $users = isset($data_cap['users']) ? $data_cap : array();
        // Check if submitted
        if (isset($data['permissions'][$cap])) {
            $permissions[$cap] = $data['permissions'][$cap];
        } else {
            $permissions[$cap] = $data_cap;
        }
        // Make sure only pre-defined are used on ours, third-party rules
        // can have anything they want.
        if (!$custom && !isset($caps[$cap])) {
            unset($permissions[$cap]);
            continue;
        }
        // Add users
        if (!empty($users)) {
            $permissions[$cap]['users'] = array_flip(array_flip($users));
        }
    }
    return $permissions;
}