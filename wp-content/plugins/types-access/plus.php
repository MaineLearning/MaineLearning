<?php
/*
 * Plus functions.
 */

add_action('plugins_loaded', 'wpcf_access_plugins_loaded', 11);
register_deactivation_hook(__FILE__, 'wpcf_access_deactivation');

/**
 * Init function. 
 */
function wpcf_access_plugins_loaded() {

    // Force roles initialization
    // WP is lazy and it does not initialize $wp_roles if user is not logged in.
    global $wp_roles;
    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }

    // Types plugin is active?
    if (!defined('WPCF_VERSION') || defined('WPCF_RUNNING_EMBEDDED')) {
        add_action('admin_notices', 'wpcf_access_admin_warning_types_inactive');
    } else if (!defined('WPCF_RUNNING_EMBEDDED')) {
        define('WPCF_PLUS', true);
        define('WPCF_ACCESS_VERSION', '1.1.1');
        define('WPCF_ACCESS_ABSPATH', dirname(__FILE__));
        define('WPCF_ACCESS_RELPATH',
                plugins_url() . '/' . basename(WPCF_ACCESS_ABSPATH));
        define('WPCF_ACCESS_INC', WPCF_ACCESS_ABSPATH . '/includes');
        if (!defined('WPCF_ACCESS_DEBUG')) {
            define('WPCF_ACCESS_DEBUG', false);
        }

        // TODO Not used yet
        // Take a snapshot (to restore on deactivation???)
        $snapshot = get_option('wpcf_access_snapshot', array());
        if (empty($snapshot)) {
            $snapshot = get_option('wp_user_roles', array());
            update_option('wpcf_access_snapshot', $snapshot);
        }

        // Set main global $wpcf_access
        global $wpcf_access;
        $wpcf_access = new stdClass();

        // Settings
        $wpcf_access->settings = new stdClass();
        $settings_custom_types = array();
        foreach (get_option('wpcf-custom-types', array()) as $type => $data) {
            $settings_custom_types[$type] = isset($data['_wpcf_access_capabilities']) ? $data['_wpcf_access_capabilities'] : array();
        }
        $wpcf_access->settings->types = array_merge(
                get_option('wpcf-access-types', array()), $settings_custom_types
        );
        $settings_custom_tax = array();
        foreach (get_option('wpcf-custom-types', array()) as $tax => $data) {
            $settings_custom_tax[$type] = isset($data['_wpcf_access_capabilities']) ? $data['_wpcf_access_capabilities'] : array();
        }
        $wpcf_access->settings->tax = array_merge(
                get_option('wpcf-access-taxonomies', array()),
                $settings_custom_tax
        );
        $wpcf_access->settings->third_party = get_option('wpcf-access-3rd-party',
                array());

        // Third party
        $wpcf_access->third_party = array();
        $wpcf_access->third_party_post = array();

        // Rules
        $wpcf_access->rules = new stdClass();
        $wpcf_access->rules->types = array();
        $wpcf_access->rules->taxonomies = array();

        // Other
        $wpcf_access->errors = array();
        $wpcf_access->shared_taxonomies = array();
        $wpcf_access->upload_files = array();
        $wpcf_access->debug = array();
        $wpcf_access->debug_hooks_with_args = array();
        $wpcf_access->debug_all_hooks = array();

        $wpcf_access = apply_filters('types_access', $wpcf_access);

        // Load admin code
        if (is_admin()) {
            require_once WPCF_ACCESS_ABSPATH . '/admin.php';
        }

        // Set locale
        $locale = get_locale();
        load_textdomain('wpcf_access',
                WPCF_ACCESS_ABSPATH . '/locale/types-access-' . $locale . '.mo');

        add_action('init', 'wpcf_access_init', 9);
        add_action('init', 'wpcf_access_late_init', 9999);
        add_action('init', 'wpcf_access_get_taxonomies_shared', 19);

        require_once WPCF_ACCESS_INC . '/ajax.php';
        require_once WPCF_ACCESS_INC . '/collect.php';
        require_once WPCF_ACCESS_INC . '/check.php';
        require_once WPCF_ACCESS_INC . '/exceptions.php';
        require_once WPCF_ACCESS_INC . '/hooks.php';
        require_once WPCF_ACCESS_INC . '/dependencies.php';
        require_once WPCF_ACCESS_INC . '/upload_files.php';
        require_once WPCF_ACCESS_INC . '/debug.php';

        do_action('wpcf_access_plugins_loaded');
    }
}

/**
 * Init function. 
 */
function wpcf_access_init() {

    // Add debug info
    if (WPCF_ACCESS_DEBUG) {
        wp_enqueue_style('types-debug', WPCF_ACCESS_RELPATH . '/css/pre.css',
                array(), WPCF_ACCESS_VERSION);
        wp_enqueue_script('jquery');
        add_action('admin_footer', 'wpcf_access_debug');
        add_action('wp_footer', 'wpcf_access_debug');
    }

    // Include all required files
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';

    // Filter WP default capabilities for current user on 'init' hook
    // 
    // We need to remove some capabilities added because of user role.
    // Example: editor has upload_files but may be restricted
    // because of Access settings.
    wpcf_access_user_filter_caps();

    do_action('wpcf_access_init');
}

/**
 * Post init function. 
 */
function wpcf_access_late_init() {

    // Register all 3rd party hooks now
    // 
    // All 3rd party hooks should be registered all the time.
    // Otherwise they won't be called.
    wpcf_access_hooks_collect();

    do_action('wpcf_access_late_init');
}

/**
 * Returns specific post access settings.
 * 
 * @global type $post
 * @param type $post_id
 * @param type $area
 * @param type $group
 * @param type $cap_id
 * @return type 
 */
function wpcf_access_get_post_access($post_id = null, $area = null,
        $group = null, $cap_id = null) {
    if (is_null($post_id)) {
        global $post;
        if (empty($post->ID)) {
            return array();
        }
        $post_id = $post->ID;
    }
    $meta = get_post_custom($post_id, 'wpcf_access', true);
    if (empty($meta)) {
        return array();
    }
    if (!empty($area) && empty($group)) {
        return !empty($meta[$area]) ? $meta[$area] : array();
    }
    if (!empty($area) && !empty($group) && empty($cap_id)) {
        return !empty($meta[$area][$group]) ? $meta[$area][$group] : array();
    }
    if (!empty($area) && !empty($group) && !empty($cap_id)) {
        return !empty($meta[$area][$group]['permissions'][$cap_id]) ? $meta[$area][$group]['permissions'][$cap_id] : array();
    }
    return array();
}

/**
 * Renders warning when Types plugin is not active. 
 */
function wpcf_access_admin_warning_types_inactive() {
    echo '<div class="message error"><p>'
    . __('Types plugin is required in order to make Access plugin work',
            'wpcf_access')
    . '</p></div>';
}

/**
 * Sorts default capabilities by predefined key.
 * 
 * @return type 
 */
function wpcf_access_sort_default_types_caps_by_predefined() {
    $default_caps = wpcf_access_types_caps();
    $caps = array();
    foreach ($default_caps as $cap => $cap_data) {
        $caps[$cap_data['predefined']][] = $cap;
    }
    return $caps;
}

/**
 * Adds or removes caps for roles down to level.
 * 
 * @param type $role
 * @param type $cap
 * @param type $allow
 * @param type $distinct 
 */
function wpcf_access_assign_cap_by_level($role, $cap) {
    $ordered_roles = wpcf_access_order_roles_by_level(wpcf_get_editable_roles());
    $flag = $found = false;
    foreach ($ordered_roles as $level => $roles) {
        foreach ($roles as $role_name => $role_data) {
            $role_set = get_role($role_name);
            if (!$flag) {
                $role_set->add_cap($cap);
            } else {
                $role_set->remove_cap($cap);
            }
            if ($role == $role_name) {
                $found = true;
            }
        }
        if ($found) {
            $flag = true;
        }
    }
}

/**
 * Deactivation hook.
 * 
 * Reverts wp_user_roles option to snapshot created on activation.
 * Removes snapshot. 
 */
function wpcf_access_deactivation() {
//    $snapshot = get_option('wpcf_access_snapshot', array());
//    if (!empty($snapshot)) {
//        update_option('wp_user_roles', $snapshot);
//    }
//    delete_option('wpcf_access_snapshot');
}

/**
 * Checks if taxonomy is shared.
 * 
 * @param type $taxonomy
 * @return type 
 */
function wpcf_access_is_taxonomy_shared($taxonomy) {
    $shared = wpcf_access_get_taxonomies_shared();
    return !empty($shared[$taxonomy]) ? $shared[$taxonomy] : false;
}

/**
 * Sets taxonomy mode.
 * 
 * @param type $taxonomy
 * @param type $mode
 * @return type 
 */
function wpcf_access_get_taxonomy_mode($taxonomy, $mode = 'follow') {
    return wpcf_access_is_taxonomy_shared($taxonomy) ? 'permissions' : $mode;
}

/**
 * Sets shared taxonomies check.
 * 
 * @global type $wpcf_access
 * @staticvar null $cache
 * @return null 
 */
function wpcf_access_get_taxonomies_shared() {
    global $wpcf_access;
    static $cache = null;
    if (!is_null($cache)) {
        return $cache;
    }
    $found = array();
    $taxonomies = get_taxonomies(null, 'objects');
    foreach ($taxonomies as $slug => $data) {
        if (count($data->object_type) > 1) {
            $found[$slug] = $data->object_type;
        }
    }
    $cache = $wpcf_access->shared_taxonomies = $found;
    return $cache;
}

/**
 * Hides post type on frontend.
 * 
 * Checks if user is logged and if has required level to read posts.
 * This is determined only by role.
 * 
 * @todo Check if checking by user_id is needed
 * 
 * @global type $wpcf_access
 * @global type $wp_post_types
 * @global type $current_user
 * @param type $role
 * @param type $post_type 
 */
function wpcf_access_hide_post_type($role, $post_type) {
    global $wpcf_access, $wp_post_types;
    $current_user = wp_get_current_user();
    $hide = false;

    // Hide posts if user not logged and role is different than 'guest'
    if (empty($current_user->ID) && $role != 'guest') {
        $hide = true;
    }

    // Check if user has required level according to role.
    // Instead may use:
    // wpcf_access_is_role_ranked_higher($role, $compare);
    // /embedded.php
    $level = wpcf_access_role_to_level($role);
    if ($level && (empty($current_user->ID)
            || !array_key_exists($level, $current_user->allcaps))) {
        $hide = true;
    }

    // Set post type properties to hide on frontend
    if ($hide && isset($wp_post_types[$post_type])) {
        $wp_post_types[$post_type]->public = false;
        $wp_post_types[$post_type]->publicly_queryable = false;
        $wp_post_types[$post_type]->show_in_nav_menus = false;
        $wp_post_types[$post_type]->exclude_from_search = true;
        $wpcf_access->debug_hidden_post_types[] = $post_type;

        // Trigger change for posts and pages
        // Built-in post types can only be excluded from search
        // using following filters: 'posts_where', 'get_pages', 'the_comments'
        if (in_array($post_type, array('post', 'page'))) {

            // If debug mode - record call
            $wpcf_access->hide_built_in[] = $post_type;

            // Register filters
            add_filter('posts_where', 'wpcf_access_filter_posts');
            add_filter('get_pages', 'wpcf_access_exclude_pages');
            add_filter('the_comments', 'wpcf_access_filter_comments');
        }
    } else if ($wp_post_types[$post_type]) {
        $wp_post_types[$post_type]->public = true;
        $wp_post_types[$post_type]->publicly_queryable = true;
        $wp_post_types[$post_type]->show_in_nav_menus = true;
        $wp_post_types[$post_type]->exclude_from_search = false;
        $wpcf_access->debug_visible_post_types[] = $post_type;
    }
}

/**
 * Returns cap settings declared in embedded.php
 * 
 * @param type $cap
 * @return type 
 */
function wpcf_access_get_cap_settings($cap) {
    $caps_types = wpcf_access_types_caps();
    if (isset($caps_types[$cap])) {
        return $caps_types[$cap];
    }
    $caps_tax = wpcf_access_tax_caps();
    if (isset($caps_tax[$cap])) {
        return $caps_tax[$cap];
    }
    return array(
        'title' => $cap,
        'role' => 'administrator',
        'predefined' => 'edit_any',
    );
}

/**
 * Returns cap settings declared in embedded.php
 * 
 * @param type $cap
 * @return type 
 */
function wpcf_access_get_cap_predefined_settings($cap) {
    $predefined = wpcf_access_types_caps_predefined();
    if (isset($predefined[$cap])) {
        return $predefined[$cap];
    }
    // If not found, try other caps
    return wpcf_access_get_cap_settings($cap);
}

/**
 * Filters posts.
 * 
 * @global type $wpcf_access
 * @global type $wpdb
 * @param type $args
 * @return type 
 */
function wpcf_access_filter_posts($args) {
    global $wpcf_access, $wpdb;
    if (!empty($wpcf_access->hide_built_in)) {
        foreach ($wpcf_access->hide_built_in as $post_type) {
            $args .= " AND $wpdb->posts.post_type <> '$post_type'";
        }
    }
    return $args;
}

/**
 * Excludes pages if necessary.
 * 
 * @global type $wpcf_access
 * @param type $pages
 * @return type 
 */
function wpcf_access_exclude_pages($pages) {
    global $wpcf_access;
    if (!empty($wpcf_access->hide_built_in)) {
        if (in_array('page', $wpcf_access->hide_built_in)) {
            return array();
        }
    }
    return $pages;
}

/**
 * Filters comments.
 * 
 * @global type $wpcf_access
 * @param type $comments
 * @return type 
 */
function wpcf_access_filter_comments($comments) {
    global $wpcf_access;
    if (!empty($wpcf_access->hide_built_in)) {
        foreach ($comments as $key => $comment) {
            // TODO Monitor this: only posts comment missing post_type?
            // Set 'post' as default
            if (!isset($comment->post_type)) {
                $wpcf_access->errors['filter_comments_no_post_type'][] = $comment;
                $comment->post_type = get_post_type($comment->comment_post_ID);
            }
            if (in_array($comment->post_type, $wpcf_access->hide_built_in)) {
                unset($comments[$key]);
            }
        }
    }
    return $comments;
}

/**
 * Filters default WP capabilities for user.
 * 
 * WP adds default capabilities depending on built-in role
 * that sometimes by-pass user_can() check.
 * 
 * @todo Check if upload_files should be suspended from 3.5
 * @global type $current_user
 * @global type $wpcf_access 
 */
function wpcf_access_user_filter_caps() {
    $current_user = wp_get_current_user();
    if (!empty($current_user->allcaps)) {
        list($role, $level) = wpcf_access_rank_user($current_user->ID);
        foreach ($current_user->allcaps as $cap => $true) {
            $cap_found = wpcf_access_search_cap($cap);
            if (!empty($cap_found)) {
                $allow = wpcf_access_is_role_ranked_higher($role,
                        $cap_found['role']);
                if (!$allow) {
                    $allow = in_array($current_user->ID, $cap_found['users']);
                }
                if (!$allow) {
                    unset($current_user->allcaps[$cap]);
                }
            }
        }
    }
}

/**
 * Determines post type.
 * 
 * @global type $post
 * @global type $pagenow
 * @return string 
 */
function wpcf_access_determine_post_type() {
    global $post;
    $post_type = false;
    $post_id = wpcf_access_determine_post_id();
    if (!empty($post) || !empty($post_id)) {
        if (get_post($post_id)) {
            return get_post_type($post_id);
        }
        $post_type = get_post_type($post);
    } else if (isset($_GET['post_type'])) {
        $post_type = $_GET['post_type'];
    } else if (isset($_POST['post_type'])) {
        $post_type = $_POST['post_type'];
    } else if (isset($_GET['post'])) {
        $post_type = get_post_type($_GET['post']);
    } else if (isset($_GET['post_id'])) {
        $post_type = get_post_type($_GET['post_id']);
    } else if (isset($_POST['post_id'])) {
        $post_type = get_post_type($_POST['post_id']);
    } else if (isset($_POST['post'])) {
        $post_type = get_post_type($_POST['post']);
    } else if (isset($_SERVER['HTTP_REFERER'])) {
        $split = explode('?', $_SERVER['HTTP_REFERER']);
        if (isset($split[1])) {
            parse_str($split[1], $vars);
            if (isset($vars['post_type'])) {
                $post_type = $vars['post_type'];
            } else if (isset($vars['post'])) {
                $post_type = get_post_type($vars['post']);
            } else if (strpos($split[1], 'post-new.php') !== false) {
                $post_type = 'post';
            }
        } else if (strpos($_SERVER['HTTP_REFERER'], 'post-new.php') !== false
                || strpos($_SERVER['HTTP_REFERER'], 'edit-tags.php') !== false
                || strpos($_SERVER['HTTP_REFERER'], 'edit.php') !== false) {
            $post_type = 'post';
        }
    }
    return $post_type;
}

/**
 * Determines post ID.
 * 
 * @global type $post
 * @global type $pagenow
 * @return string bbbb
 */
function wpcf_access_determine_post_id() {
    global $post;
    if (!empty($post)) {
        return $post->ID;
    } else if (isset($_GET['post'])) {
        return intval($_GET['post']);
    } else if (isset($_POST['post'])) {
        return intval($_POST['post']);
    } else if (isset($_GET['post_id'])) {
        return intval($_GET['post_id']);
    } else if (isset($_POST['post_id'])) {
        return intval($_POST['post_id']);
    } else if (defined('DOING_AJAX') && isset($_SERVER['HTTP_REFERER'])) {
        $split = explode('?', $_SERVER['HTTP_REFERER']);
        if (isset($split[1])) {
            parse_str($split[1], $vars);
            if (isset($vars['post'])) {
                return intval($vars['post']);
            } else if (isset($vars['post_id'])) {
                return intval($vars['post_id']);
            }
        }
    }
    return false;
}

/**
 * Gets attachment parent post type.
 * 
 * @return boolean
 */
function wpcf_access_attachment_parent_type() {
    if (isset($_POST['attachment_id'])) {
        $post_id = $_POST['attachment_id'];
    } else if (isset($_GET['attachment_id'])) {
        $post_id = $_GET['attachment_id'];
    } else {
        return false;
    }
    $post = get_post($post_id);
    if (!empty($post->post_parent)) {
        $post_parent = get_post($post->post_parent);
        if (!empty($post_parent->post_type)) {
            return $post_parent->post_type;
        }
    }
    return false;
}

/**
 * Maps predefinied capabilities to specific post_type or taxonomy capability.
 * 
 * Example in case of Page post type:
 * edit_post => edit_page
 * 
 * @param type $context
 * @param type $name
 * @param type $cap
 * @return type 
 */
function wpcf_access_predefined_to_wp_caps($context = 'post_type',
        $name = 'post', $cap = 'read') {

    // Get WP type object data
    $data = $context == 'taxonomy' ? get_taxonomy($name) : get_post_type_object($name);
    if (empty($data)) {
        return array();
    }

    // Get defined capabilities
    $caps = $context == 'taxonomy' ? wpcf_access_tax_caps() : wpcf_access_types_caps();

    // Set mapped WP capabilities
    $caps_mapped = array();
    
    foreach ($caps as $_cap => $_data) {
        if ($_data['predefined'] == $cap) {
            if (!empty($data->cap->{$_cap})) {
                $caps_mapped[$data->cap->{$_cap}] = $data->cap->{$_cap};
            }
        }
    }
    return array_keys($caps_mapped);
}

/**
 * Check Media post type.
 * 
 * @global type $wp_version
 * @return type 
 */
function wpcf_access_is_media_registered() {
    global $wp_version;
    // WP 3.5
    return version_compare($wp_version, '3.4.3', '>');
}

/**
 * Maps capability according to current user and post_id.
 * 
 * @param type $parse_args
 * @param type $post_id
 * @return type 
 */
function wpcf_access_map_cap($cap, $post_id) {
    $current_user = wp_get_current_user();
    // do check for 0 post id
    if (intval($post_id)>0)
    {
        $map = map_meta_cap($cap, $current_user->ID, $post_id);
        if (is_array($map) && !empty($map[0])) {
            return $map[0];
        }
    }
    return $cap;
}