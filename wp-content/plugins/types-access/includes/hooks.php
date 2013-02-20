<?php
/*
 * Access hooks.
 */

/**
 * Register caps general settings.
 * 
 * @global type $wpcf_access
 * @param type $args
 * @return boolean 
 */
function wpcf_access_register_caps($args) {
    global $wpcf_access;
    foreach (array('area', 'group') as $check) {
        if (empty($args[$check])) {
            return false;
        }
    }
    if (in_array($args['area'], array('types', 'tax'))) {
        return false;
    }
    extract($args);
    if (!isset($caps)) {
        $caps = array($cap_id => $args);
    }
    foreach ($caps as $cap) {
        foreach (array('cap_id', 'title', 'default_role') as $check) {
            if (empty($cap[$check])) {
                continue;
            }
        }
        extract($cap);
        $wpcf_access->third_party[$area][$group]['permissions'][$cap_id] = array(
            'cap_id' => $cap_id,
            'title' => $title,
            'role' => $default_role,
            'saved_data' => isset($wpcf_access->settings->third_party[$area][$group]['permissions'][$cap_id]) ? $wpcf_access->settings->third_party[$area][$group]['permissions'][$cap_id] : array('role' => $default_role),
        );
        return $wpcf_access->third_party[$area][$group]['permissions'][$cap_id];
    }
    return false;
}

/**
 * Register caps per post.
 * 
 * @global type $wpcf_access
 * @param type $args
 * @return boolean 
 */
function wpcf_access_register_caps_post($args) {
    global $wpcf_access, $post;
    foreach (array('area', 'group') as $check) {
        if (empty($args[$check])) {
            return false;
        }
    }
    if (in_array($args['area'], array('types', 'tax'))) {
        return false;
    }
    extract($args);
    if (!isset($caps)) {
        $caps = $args;
    }
    foreach ($caps as $cap) {
        foreach (array('cap_id', 'title', 'default_role') as $check) {
            if (empty($cap[$check])) {
                continue;
            }
        }
        extract($cap);
        $saved_data = wpcf_access_get_post_access($post->ID, $area, $group,
                $cap_id);
        $wpcf_access->third_party_post[$post->ID][$area][$group]['permissions'][$cap_id] = array(
            'cap_id' => $cap_id,
            'title' => $title,
            'role' => $default_role,
            'saved_data' => !empty($saved_data) ? $saved_data : array('role' => $default_role),
        );
    }
}

/**
 * Collect all 3rd party hooks.
 * 
 * @global type $wpcf_access
 * @return type 
 */
function wpcf_access_hooks_collect() {
    global $wpcf_access;
    $r = array();
    $a = apply_filters('types-access-area', array());
    foreach ($a as $area) {
        $g = apply_filters('types-access-group', array(), $area['id']);
        foreach ($g as $group) {
            $c = apply_filters('types-access-cap', array(), $area['id'],
                    $group['id']);
            foreach ($c as $cap) {
                $r[$area['id']][$group['id']][$cap['cap_id']] = $cap;
                $cap['area'] = $area['id'];
                $cap['group'] = $group['id'];
                $cap_reg_data = wpcf_access_register_caps($cap);
                $wpcf_access->third_party_caps[$cap['cap_id']] = $cap_reg_data;
            }
        }
    }
    return $r;
}