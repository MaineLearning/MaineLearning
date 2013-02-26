<?php
/*
 * Check functions.
 * 
 * 'user_has_cap' is main WP filter we use to filter capability check.
 * All changes are done on-the-fly and per call. No caching.
 * 
 * WP accepts $allcaps array of capabilities returned.
 * It’s actually property of $WP_User->allcaps.
 * 
 */
add_filter('user_has_cap', 'wpcf_access_user_has_cap_filter', 15, 3);
//        add_filter('role_has_cap', 'wpcf_access_role_has_cap_filter', 10, 3);

/**
 * 'has_cap' filter.
 * 
 * @global type $current_user
 * @global type $wpcf_access->rules->types
 * @param type $allcaps
 * @param type $caps
 * @param type $args
 * @return array
 */
function wpcf_access_user_has_cap_filter($allcaps, $caps, $args) {
    return wpcf_access_check($allcaps, $caps, $args);
}

/**
 * Main check function.
 * 
 * @global type $wpcf_access
 * @global type $post
 * @global type $pagenow
 * @staticvar null $current_user
 * @param type $allcaps
 * @param type $caps
 * @param type $args
 * @param type $parse true|false to return $allcaps or boolean
 * @return array|boolean 
 */
function wpcf_access_check($allcaps, $caps, $args, $parse = true) {
    global $wpcf_access;
    
    // Set user (changed after noticed WP signon empty user)
    static $current_user = null;
    if (is_null($current_user)) {
        if (isset($_POST['log'])
                && basename($_SERVER['PHP_SELF']) == 'wp-login.php') {
            $current_user = get_user_by('login', esc_sql($_POST['log']));
        } else {
            $current_user = new WP_User(get_current_user_id());
        }
    }
    // Debug if some args[0] is array
    if (WPCF_ACCESS_DEBUG) {
        if (empty($args[0]) || !is_string($args[0])) {
            $wpcf_access->errors['cap_args'][] = array(
                'file' => __FILE__ . ' #' . __LINE__,
                'args' => func_get_args(),
                'debug_backtrace' => debug_backtrace(),
            );
        }
    }
    if (empty($args[0]) || !is_string($args[0])) {
        return $allcaps;
    }

    // Main capability queried
    $capability_requested = $capability_original = $args[0];
	
    // Other capabilities required to be true
    $caps_clone = $caps;

    // All user capabilities
    $allcaps_clone = $allcaps;

    $map = wpcf_access_role_to_level_map();
    $allow = null;
    $parse_args = array(
        'caps' => $caps_clone,
        'allcaps' => $allcaps_clone,
        'data' => array(), // default settings
        'args' => func_get_args(),
        'role' => '',
    );

    // Allow check to be altered
    list($capability_requested, $parse_args) = apply_filters('types_access_check',
            array($capability_requested, $parse_args, $args));

    // TODO Monitor this
    // I saw mixup of $key => $cap and $cap => $true filteres by collect.php
    // Also we're adding sets of capabilities to 'caps'
//    foreach ($parse_args['caps'] as $k => $v) {
//        if (is_string($k)) {
//            $parse_args['caps'][] = $k;
//            unset($parse_args['caps'][$k]);
//        }
//    }
    // Debug
    if ($capability_original != $capability_requested) {
        $wpcf_access->converted[$capability_original][$capability_requested] = 1;
    }

    $parse_args['cap'] = $capability_requested;

    // Allow rules to be altered
    $wpcf_access->rules = apply_filters('types_access_rules',
            $wpcf_access->rules, $parse_args);

    $override = apply_filters('types_access_check_override', null, $parse_args);
    if (!is_null($override)) {
        return $override;
    }

    // Check post_types($wpcf_access->rules->types)
    // See if main requested capability ($capability_requested)
    // is in collected post types rules and process it.

    if (!empty($wpcf_access->rules->types[$capability_requested])) {
        $types = $wpcf_access->rules->types[$capability_requested];
        $types_role = !empty($types['role']) ? $types['role'] : false;
        $types_role_mapped = !empty($map[$types_role]) ? $map[$types_role] : false;
        $types_users = !empty($types['users']) ? $types['users'] : false;
        $parse_args['role'] = $types_role;

        // Return true for guest
        // Presumption that any capability that requires user to be not-logged
        // (guest) should be allowed. Because other roles have level ranked higher
        // than guest, means it's actually unrestricted by any means.
        if ($types_role == 'guest') {
            return $parse ? wpcf_access_parse_caps(true, $parse_args) : true;
        }

        // Set data
        $parse_args['data'] = wpcf_access_types_caps();
        $parse_args['data'] = isset($parse_args['data'][$capability_requested]) ? $parse_args['data'][$capability_requested] : array();
        // Set level and user checks
        $level_needed = $types_role && $types_role_mapped ? $types_role_mapped : false;
        $user_needed = $types_users ? $types_users : false;

        $level_passed = false;

        if ($level_needed || is_array($user_needed)) {
            $allow = false;

            // Check level
            if ($level_needed) {
                if (!empty($current_user->allcaps[$level_needed])) {
                    $allow = $level_passed = true;
                }
            }

            // Check user
            if (!$level_passed && is_array($user_needed)) {
                if (in_array($current_user->ID, $user_needed)) {
                    $allow = true;
                }
            }
        }
        return $parse ?  wpcf_access_parse_caps((bool) $allow, $parse_args) : (bool) $allow;
    }

    // Check taxonomies ($wpcf_access->rules->taxonomies)
    // See if main requested capability ($capability_requested)
    // is in collected taxonomies rules and process it.

    if (!empty($wpcf_access->rules->taxonomies[$capability_requested])) {
        $tax = $wpcf_access->rules->taxonomies[$capability_requested];
        $tax_role = !empty($tax['role']) ? $tax['role'] : false;
        $tax_role_mapped = !empty($map[$tax_role]) ? $map[$tax_role] : false;
        $tax_users = !empty($tax['users']) ? $tax['users'] : false;
        $parse_args['role'] = $tax_role;

        // Check taxonomies 'follow'
        if (!isset($tax['taxonomy'])) {
            $wpcf_access->errors['no_taxonomy_recorded'] = $tax;
        }
        $shared = wpcf_access_is_taxonomy_shared($tax['taxonomy']);
        $follow = $shared ? false : $tax['follow'];

        // Return true for guest (same as for post types)
        if ($tax_role == 'guest') {
            return $parse ? wpcf_access_parse_caps(true, $parse_args) : true;
        }

        // Set level and user
        $level_needed = $tax_role && $tax_role_mapped ? $tax_role_mapped : false;
        $user_needed = $tax_users ? $tax_users : false;

        $level_passed = false;

        // Set data
        $parse_args['data'] = wpcf_access_tax_caps();
        $parse_args['data'] = isset($parse_args['data'][$capability_requested]) ? $parse_args['data'][$capability_requested] : array();

        // Check if taxonomy use 'Same as parent' setting ('follow').
        if (!$follow) {
            if ($level_needed || is_array($user_needed)) {
                $allow = false;
                if ($level_needed) {
                    if (!empty($current_user->allcaps[$level_needed])) {
                        $allow = $level_passed = true;
                    }
                }
                if (!$level_passed && is_array($user_needed)) {
                    if (in_array($current_user->ID, $user_needed)) {
                        $allow = true;
                    }
                }
                return $parse ? wpcf_access_parse_caps((bool) $allow,
                                $parse_args) : (bool) $allow;
            }
        } else {
            global $post, $pagenow;
            // Determine post type
            $post_type = wpcf_access_determine_post_type();

            // If no post type determined, return FALSE
            if (!$post_type) {
                $allow = false;
                return $parse ? wpcf_access_parse_caps((bool) $allow,
                                $parse_args) : (bool) $allow;
            } else {
                $post_type = get_post_type_object($post_type);
                $post_type = sanitize_title($post_type->labels->name);
                $tax_caps = wpcf_access_tax_caps();
                foreach ($tax_caps as $tax_cap_slug => $tax_slug_data) {
                    foreach ($tax_slug_data['match'] as $match => $replace) {
                        $level_passed = true;
                        if (strpos($capability_requested, $match) === 0) {
                            $post_type_check = $post_type;
                            if ($post_type_check
                                    && !empty($wpcf_access->rules->types[$replace['match'] . $post_type_check])) {
                                $level_needed = !empty($wpcf_access->rules->types[$replace['match'] . $post_type_check]['role']) && isset($map[$wpcf_access->rules->types[$replace['match'] . $post_type_check]['role']]) ? $map[$wpcf_access->rules->types[$replace['match'] . $post_type_check]['role']] : false;
                                $user_needed = !empty($wpcf_access->rules->types[$replace['match'] . $post_type_check]['users']) ? $wpcf_access->rules->types[$replace['match'] . $post_type_check]['users'] : false;
                                if ($level_needed || is_array($user_needed)) {
                                    $allow = false;
                                    if ($level_needed) {
                                        if (!empty($current_user->allcaps[$level_needed])) {
                                            $allow = $level_passed = true;
                                        }
                                    }
                                    if (!$level_passed && is_array($user_needed)) {
                                        if (in_array($current_user->ID,
                                                        $user_needed)) {
                                            $allow = true;
                                        }
                                    }
                                    return $parse ? wpcf_access_parse_caps((bool) $allow,
                                                    $parse_args) : (bool) $allow;
                                }
                            } else if (!empty($allcaps_clone[$replace['default']])) {
                                $allow = true;
                                return $parse ? wpcf_access_parse_caps((bool) $allow,
                                                $parse_args) : (bool) $allow;
                            }
                        }
                    }
                }
            }
        }
    }


    // Check 3rd party saved settings (option 'wpcf-access-3rd-party')
    // After that check on-the-fly registered capabilities to use default data
    // This is already collected with wpcf_access_hooks_collect

    if (!empty($wpcf_access->third_party_caps[$capability_requested])) {
        // check only requested cap not all
        $data=$wpcf_access->third_party_caps[$capability_requested];
        //foreach ($wpcf_access->third_party_caps as $cap => $data) {
            $wpcf_access->third_party_debug[$capability_requested] = 1;

            // Set saved role if available
            if (isset($data['saved_data']['role'])) {
                $data['role'] = $data['saved_data']['role'];
            }
            
            $parse_args['role'] = $data['role'];
            // Return true for guest (same as post_types)
            if ($data['role'] == 'guest') {
                return $parse ? wpcf_access_parse_caps(true, $parse_args) : true;
            }
            // removing level testing for custom 3rd party capabilities
            $level_needed = isset($map[$data['role']]) ? $map[$data['role']] : false;
            $user_needed = !empty($data['users']) ? $data['users'] : false;

            $level_passed = false;

            if ($level_needed || is_array($user_needed)) {
                $parse_args['data'] = array();
                $allow = false;
                if ($level_needed) {
                    if (!empty($current_user->allcaps[$level_needed])) {
                        $allow = $level_passed = true;
                    }
                }
                if (!$level_passed && is_array($user_needed)) {
                    if (!in_array($current_user->ID, $user_needed)) {
                        $allow = true;
                    }
                }
                return $parse ? wpcf_access_parse_caps((bool) $allow,
                                $parse_args) : (bool) $allow;
            }
        //}
    }
//    $third_party = get_option('wpcf-access-3rd-party', array());
//    foreach ($third_party as $areas => $area) {
//    foreach ($wpcf_access->third_party as $area) {
//        foreach ($area as $group) {
//            if (isset($group['permissions']) && is_array($group['permissions'])) {
//                foreach ($group['permissions'] as $cap => $data) {
//                    if (isset($caps_clone[0]) && $cap == $caps_clone[0]) {
//                        $parse_args['role'] = $data['role'];
//                        // Return true for guest (same as post_types)
//                        if ($data['role'] == 'guest') {
//                            return $parse ? wpcf_access_parse_caps(true,
//                                            $parse_args) : true;
//                        }
//                        $level_needed = isset($map[$data['role']]) ? $map[$data['role']] : false;
//                        $user_needed = !empty($data['users']) ? $data['users'] : false;
//
//                        $level_passed = false;
//
//                        if ($level_needed || is_array($user_needed)) {
//                            $parse_args['data'] = array();
//                            $allow = false;
//                            if ($level_needed) {
//                                if (!empty($current_user->allcaps[$level_needed])) {
//                                    $allow = $level_passed = true;
//                                }
//                            }
//                            if (!$level_passed && is_array($user_needed)) {
//                                if (!in_array($current_user->ID, $user_needed)) {
//                                    $allow = true;
//                                }
//                            }
//                            return $parse ? wpcf_access_parse_caps((bool) $allow,
//                                            $parse_args) : (bool) $allow;
//                        }
//                        break;
//                    }
//                }
//            }
//        }
//    }
    $wpcf_access->debug_all_hooks[$capability_requested][] = $parse_args;
    return is_null($allow) ? $allcaps : wpcf_access_parse_caps((bool) $allow,
                    $parse_args);
}

/**
 * Parses caps.
 * 
 * @param type $allow
 * @param type $cap
 * @param type $caps
 * @param type $allcaps 
 */
function wpcf_access_parse_caps($allow, $args) {

    // Set vars
    $args_clone = $args;
    $cap = $args['cap'];
    $caps = $args['caps'];
    $allcaps = $args['allcaps'];
    $data = $args['data'];
//    $role = $args['role'];
    $args = $args['args'];

    if ($allow) {

        // If true - force all caps to true

        $allcaps[$cap] = 1;
        foreach ($caps as $c) {
            // TODO - this is temporary solution for comments
            if ($cap == 'edit_comment'
                    && (strpos($c, 'edit_others_') === 0
                    || strpos($c, 'edit_published_') === 0)) {
                $allcaps[$c] = 1;
            }
            // TODO Monitor this - tricky, WP requires that all required caps
            // to be true in order to allow cap.
            if (!empty($data['fallback'])) {
                foreach ($data['fallback'] as $fallback) {
                    $allcaps[$fallback] = 1;
                }
            } else {
                $allcaps[$c] = 1;
            }
        }
    } else {
        // If false unset caps in allcaps
        unset($allcaps[$cap]);

        // TODO Monitor this
        // Do we want to unset allcaps?
        foreach ($caps as $c) {
            unset($allcaps[$c]);
        }
    }

    if (WPCF_ACCESS_DEBUG) {
        global $wpcf_access;
        $debug_caps = array();
        foreach ($caps as $cap) {
            $debug_caps[$cap] = isset($allcaps[$cap]) ? $allcaps[$cap] : 0;
        }
        $wpcf_access->debug[$cap][] = array(
            'parse_args' => $args_clone,
            'dcaps' => $debug_caps,
        );
    }
    return $allcaps;
}

/**
 * 'role_has_cap' filter.
 * 
 * @global type $current_user
 * @global type $wpcf_access->rules->types
 * @param type $capabilities
 * @param type $cap
 * @param type $role
 * @return int 
 */
//function wpcf_access_role_has_cap_filter($capabilities, $cap, $role) {}