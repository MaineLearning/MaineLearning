<?php
/*
 * Hooks to collect and map settings.
 */
add_filter('wpcf_type', 'wpcf_access_init_types_rules', 10, 2);
add_action('wpcf_type_registered', 'wpcf_access_collect_types_rules');
add_filter('wpcf_taxonomy_data', 'wpcf_access_init_tax_rules', 10, 3);
add_action('wpcf_taxonomy_registered', 'wpcf_access_collect_tax_rules');
add_action('registered_post_type', 'wpcf_access_registered_post_type_hook', 10,
        2);
add_action('registered_taxonomy', 'wpcf_access_registered_taxonomy_hook', 10, 3);
add_filter('types_access_check', 'wpcf_access_filter_rules', 15, 3);

/**
 * Adds capabilities on WPCF types before registration hook.
 * 
 * Access insists on using map_meta_cap true. It sets all post types to use
 * mapped capabilities.
 * 
 * Examples:
 * 'edit_posts => 'edit_types'
 * 'edit_others_posts => 'edit_others_views'
 * 'edit_published_posts => 'edit_published_cred'
 * 
 * This prevents using shared capabilities across post types
 * and so matching wrong settings.
 * 
 * If in debug mode, debug output will show if any capabilities are overlapping.
 * 
 * @param type $data
 * @param type $post_type
 * @return boolean 
 */
function wpcf_access_init_types_rules($data, $post_type) {
    $types = get_option('wpcf-custom-types', array());

    // Check if managed
    if (isset($types[$post_type]['_wpcf_access_capabilities']['mode'])) {
        if ($types[$post_type]['_wpcf_access_capabilities']['mode'] === 'not_managed') {
            return $data;
        }

        // Set capability type (singular and plural names needed)
        $data['capability_type'] = array(
            sanitize_title($data['labels']['singular_name']),
            sanitize_title($data['labels']['name'])
        );

        // Flag WP to use meta mapping
        $data['map_meta_cap'] = true;
    }
    return $data;
}

/**
 * Adds capabilities on WPCF taxonomies before registration hook.
 * 
 * Same as for post types. Create own capabilities for each taxonomy.
 * 
 * @global type $wpcf_access->rules->taxonomies
 * @param type $data
 * @param type $taxonomy
 * @param type $object_types
 * @return type 
 */
function wpcf_access_init_tax_rules($data, $taxonomy, $object_types) {
    global $wpcf_access;

    // Check if managed
    if (empty($data['_wpcf_access_capabilities']['mode'])) {
        return $data;
    }
    $settings = $data['_wpcf_access_capabilities'];
    $mode = isset($settings['mode']) ? $settings['mode'] : 'not_managed';
    if ($mode == 'not_managed') {
        return $data;
    }

    // Match only predefined capabilities
    $caps = wpcf_access_tax_caps();
    foreach ($caps as $cap_slug => $cap_data) {

        // Create capability slug
        $new_cap_slug = str_replace('_terms',
                '_' . sanitize_title($data['labels']['name']), $cap_slug);
        $data['capabilities'][$cap_slug] = $new_cap_slug;
        // Set mode
        $wpcf_access->rules->taxonomies[$new_cap_slug]['follow'] = $mode == 'follow';

        // If mode is not 'folow' and settings are determined
        if ($mode != 'follow' && isset($settings['permissions'][$cap_slug])) {
            $wpcf_access->rules->taxonomies[$new_cap_slug]['role'] = $settings['permissions'][$cap_slug]['role'];
            $wpcf_access->rules->taxonomies[$new_cap_slug]['users'] = isset($settings['permissions'][$cap_slug]['users']) ? $settings['permissions'][$cap_slug]['users'] : array();
        }

        // Add to rules
        $wpcf_access->rules->taxonomies[$new_cap_slug]['taxonomy'] = $taxonomy;
    }
    return $data;
}

/**
 * Sets rules for WPCF types after registration hook.
 * 
 * @global type $wpcf_access_types_rules
 * @param type $data 
 */
function wpcf_access_collect_types_rules($data) {
    global $wpcf_access, $wp_post_types, $current_user;
    if (!isset($data->_wpcf_access_capabilities)) {
        return false;
    }
    $settings = $data->_wpcf_access_capabilities;
    if ($settings['mode'] == 'not_managed' || empty($settings['permissions'])) {
        return false;
    }
    $caps = wpcf_access_types_caps();
    $mapped = array();

    // Map predefined to existing capabilities
    foreach ($caps as $cap_slug => $cap_spec) {
        if (isset($settings['permissions'][$cap_spec['predefined']])) {
            $mapped[$cap_slug] = $settings['permissions'][$cap_spec['predefined']];
        } else {
            $mapped[$cap_slug] = $cap_spec['predefined'];
        }
    }

    // Set rule settings for post type by pre-defined caps
    foreach ($data->cap as $cap_slug => $cap_spec) {
        if (isset($mapped[$cap_slug])) {
            if (isset($mapped[$cap_slug]['role'])) {
                $wpcf_access->rules->types[$cap_spec]['role'] = $mapped[$cap_slug]['role'];
            } else {
                $wpcf_access->rules->types[$cap_spec]['role'] = 'administrator';
            }
            $wpcf_access->rules->types[$cap_spec]['users'] = isset($mapped[$cap_slug]['users']) ? $mapped[$cap_slug]['users'] : array();
            $wpcf_access->rules->types[$cap_spec]['types'][$data->slug] = 1;
        }
    }

    // Check read permissions
    // Check unlogged user settings
    $check_read = false;
    if (isset($data->_wpcf_access_capabilities)) {
        $caps = $data->_wpcf_access_capabilities;
        $check_read = true;
    }
    if ($check_read) {
        if ($caps['mode'] == 'not_managed') {
            return false;
        }

        // Mark post type as hidden
        if (isset($caps['permissions']['read']['role'])) {
            wpcf_access_hide_post_type($caps['permissions']['read']['role'],
                    $data->name);
        } else {
            // Missed setting? Debug that!
            $wpcf_access->errors['missing_settings']['read'][] = array(
                'caps' => $caps,
                'data' => $data,
            );
        }
    }
}

/**
 * Maps rules and settings for post types registered outside of Types.
 * 
 * @param type $post_type
 * @param type $args 
 */
function wpcf_access_registered_post_type_hook($post_type, $args) {
    global $wpcf_access, $wp_post_types;
    $settings_access = get_option('wpcf-access-types', array());
    if (isset($settings_access[$post_type])) {
        $data = $settings_access[$post_type];

        // Mark that will inherit post settings
        // TODO New types to be added
        if (!in_array($post_type, array('post', 'page', 'attachment', 'media'))
                && (empty($wp_post_types[$post_type]->capability_type)
                || $wp_post_types[$post_type]->capability_type == 'post')) {
            $wp_post_types[$post_type]->_wpcf_access_inherits_post_cap = 1;
        }

        if ($data['mode'] == 'not_managed') {
            return false;
        }

        // Force map meta caps
        $wp_post_types[$post_type]->capability_type = array(
            sanitize_title($wp_post_types[$post_type]->labels->singular_name),
            sanitize_title($wp_post_types[$post_type]->labels->name)
        );
        $wp_post_types[$post_type]->map_meta_cap = true;
        $wp_post_types[$post_type]->capabilities = array();
        $wp_post_types[$post_type]->cap = get_post_type_capabilities($wp_post_types[$post_type]);
        unset($wp_post_types[$post_type]->capabilities);

        $caps = wpcf_access_types_caps();
        $mapped = array();

        // Map predefined
        foreach ($caps as $cap_slug => $cap_spec) {
            if (isset($data['permissions'][$cap_spec['predefined']])) {
                $mapped[$cap_slug] = $data['permissions'][$cap_spec['predefined']];
            } else {
                $mapped[$cap_slug] = $cap_spec['predefined'];
            }
        }

        // Set rule settings for post type by pre-defined caps
        foreach ($args->cap as $cap_slug => $cap_spec) {
            if (isset($mapped[$cap_slug])) {
                if (isset($mapped[$cap_slug]['role'])) {
                    $wpcf_access->rules->types[$cap_spec]['role'] = $mapped[$cap_slug]['role'];
                } else {
                    $wpcf_access->rules->types[$cap_spec]['role'] = 'administrator';
                }
                
                $wpcf_access->rules->types[$cap_spec]['users'] = isset($mapped[$cap_slug]['users']) ? $mapped[$cap_slug]['users'] : array();
                $wpcf_access->rules->types[$cap_spec]['types'][$args->name] = 1;
            }
        }
        
        // TODO create_posts set manually for now
        // Monitor WP changes
        if (!isset($wpcf_access->rules->types['create_posts'])) {
            $wpcf_access->rules->types['create_posts'] = $wpcf_access->rules->types['edit_posts'];
        }
        if (!isset($wpcf_access->rules->types['create_post'])) {
            $wpcf_access->rules->types['create_post'] = $wpcf_access->rules->types['edit_post'];
        }
    }

    // Check read permissions
    // Check unlogged user settings
    $check_read = false;
    if (isset($settings_access[$post_type])) {
        $data = $settings_access[$post_type];
        $check_read = true;
    }
    if ($check_read) {
        if ($data['mode'] == 'not_managed') {
            return false;
        }
        // Mark post type as hidden
        if (!empty($data['permissions']['read']['role'])) {
            wpcf_access_hide_post_type($data['permissions']['read']['role'],
                    $post_type);
        } else {
            // Missed setting? Debug that!
            $wpcf_access->errors['hide_post'][$post_type] = array(
                'data' => $data,
            );
        }
    }
}

/**
 * Maps rules and settings for taxonomies registered outside of Types.
 * 
 * @param type $post_type
 * @param type $args 
 */
function wpcf_access_registered_taxonomy_hook($taxonomy, $object_type, $args) {
    global $wp_taxonomies, $wpcf_access;
    $settings_access = get_option('wpcf-access-taxonomies', array());
    if (isset($settings_access[$taxonomy]) && $wp_taxonomies[$taxonomy]) {
        $data = $settings_access[$taxonomy];
        $mode = isset($data['mode']) ? $data['mode'] : 'not_managed';
        if ($mode == 'not_managed') {
            return false;
        }
        $caps = wpcf_access_tax_caps();

        // Map pre-defined capabilities
        foreach ($caps as $cap_slug => $cap_data) {

            // Create cap slug
            $new_cap_slug = str_replace('_terms',
                    '_' . sanitize_title($args['labels']->name), $cap_slug);

            // Alter if tax is built-in or other has default capability settings
            if (!empty($args['_builtin'])
                    || (isset($args['cap']->$cap_slug)
                    && $args['cap']->$cap_slug == $cap_data['default'])) {
                $wp_taxonomies[$taxonomy]->cap->$cap_slug = $new_cap_slug;
                $wpcf_access->rules->taxonomies[$new_cap_slug]['follow'] = $mode == 'follow';
                if ($mode != 'follow' && isset($data['permissions'][$cap_slug])) {
                    $wpcf_access->rules->taxonomies[$new_cap_slug]['role'] = $data['permissions'][$cap_slug]['role'];
                    $wpcf_access->rules->taxonomies[$new_cap_slug]['users'] = isset($data['permissions'][$cap_slug]['users']) ? $data['permissions'][$cap_slug]['users'] : array();
                }

                // Otherwise just map capabilities
            } else if (isset($args['cap']->$cap_slug)
                    && isset($wpcf_access->rules->taxonomies[$args['cap']->$cap_slug])) {
                $wpcf_access->rules->taxonomies[$args['cap']->$cap_slug]['follow'] = $mode == 'follow';
                if ($mode != 'follow' && isset($data['permissions'][$cap_slug])) {
                    $wpcf_access->rules->taxonomies[$args['cap']->$cap_slug]['role'] = $data['permissions'][$cap_slug]['role'];
                    $wpcf_access->rules->taxonomies[$args['cap']->$cap_slug]['users'] = isset($data['permissions'][$cap_slug]['users']) ? $data['permissions'][$cap_slug]['users'] : array();
                }
            }
            $wpcf_access->rules->taxonomies[$args['cap']->$cap_slug]['taxonomy'] = $taxonomy;
        }
    }
}

/**
 * Filters rules according to sets permitted.
 * 
 * Settings are defined in /includes/dependencies.php
 * Each capability is in relationship with some other and can't be used solely
 * without other.
 * 
 * @global type $current_user
 * @global type $wpcf_access
 * @staticvar null $cache
 * @return null 
 */
function wpcf_access_filter_rules() {
    global $current_user, $wpcf_access;
    static $cache = null;
    $cache_key = md5(serialize(func_get_args()));
    if (!empty($cache[$cache_key])) {
        return $cache[$cache_key];
    }
    $args = func_get_args();
    $cap = $args[0][0];
    $parse_args = $args[0][1];
    $args = $args[0][2];

    $found = wpcf_access_search_cap($cap);
    if ($found) {
        $wpcf_access->debug_fallbacks_found[$cap] = $found;
    } else {
        $wpcf_access->debug_fallbacks_missed[$cap] = 1;
        return array($cap, $parse_args, $args);
    }

    $set = wpcf_access_user_get_caps_by_type($current_user->ID,
            $found['_context']);

    if (empty($set)) {
        $wpcf_access->debug_missing_context[$found['_context']][$cap]['user'] = $current_user->ID;
        return array($cap, $parse_args, $args);
    }

    // Set allowed caps accordin to sets allowed
    // /includes/dependencies.php will hook on 'access_dependencied' filter
    // and map capabilities in two arrays depending on main capability.
    // 
    // Example:
    // 'edit_own' disabled will have:
    // 'disallowed_caps' => ('edit_any', 'delete_any', 'publish')
    // 
    // 'edit_own' enabled will have:
    // 'allowed_caps' => ('read')
    
    $allowed_caps = $disallowed_caps = array();

    // Apply dependencies filter
    list($allowed_caps, $disallowed_caps) = apply_filters('types_access_dependencies',
            array($allowed_caps, $disallowed_caps, $set));

    $filtered = array();

    // TODO Monitor this
    foreach ($disallowed_caps as $disallowed_cap) {
        if (in_array($disallowed_cap, $parse_args['caps'])) {
            // Just messup checked caps
            $filtered['caps'] = array();
            $parse_args = array_merge($parse_args, $filtered);
            $wpcf_access->debug_caps_disallowed[$found['_context']][$cap][] = $disallowed_cap;
            return array($cap, $parse_args);
        }
    }

    // TODO Monitor this
    foreach ($allowed_caps as $allowed_cap) {
        $parse_args['caps'][] = $allowed_cap;
        $filtered['allcaps'][$allowed_cap] = true;
        $wpcf_access->debug_caps_allowed[$found['_context']][$cap][] = $allowed_cap;
    }

    $parse_args = array_merge($parse_args, $filtered);
    $cache[$cache_key] = array($cap, $parse_args);
    return $cache[$cache_key];
}