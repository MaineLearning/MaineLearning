<?php
/*
 * AJAX calls.
 */

add_action('wp_ajax_wpcf_access_save_settings', 'wpcf_access_save_settings');
add_action('wp_ajax_wpcf_access_ajax_reset_to_default',
        'wpcf_access_ajax_reset_to_default');
add_action('wp_ajax_wpcf_access_suggest_user',
        'wpcf_access_wpcf_access_suggest_user_ajax');
add_action('wp_ajax_wpcf_access_ajax_set_level', 'wpcf_access_ajax_set_level');
add_action('wp_ajax_wpcf_access_add_role', 'wpcf_access_add_role_ajax');
add_action('wp_ajax_wpcf_access_delete_role', 'wpcf_access_delete_role_ajax');

/**
 * AJAX revert to default call. 
 */
function wpcf_access_ajax_reset_to_default() {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'],
                    'wpcf_access_ajax_reset_to_default')) {
        die('verification failed');
    }
    if ($_GET['type'] == 'type') {
        $caps = wpcf_access_types_caps_predefined();
    } else if ($_GET['type'] == 'tax') {
        $caps = wpcf_access_tax_caps();
    }
    if (!empty($caps) && isset($_GET['button_id'])) {
        $output = array();
        foreach ($caps as $cap => $cap_data) {
            $output[$cap] = $cap_data['role'];
        }
        echo json_encode(array(
            'output' => $output,
            'type' => $_GET['type'],
            'button_id' => $_GET['button_id'],
        ));
    }
    die();
}

/**
 * AJAX set levels default call. 
 */
function wpcf_access_ajax_set_level() {
    if (!isset($_POST['_wpnonce'])
            || !wp_verify_nonce($_POST['_wpnonce'], 'execute')) {
        die('verification failed');
    }
    require_once WPCF_ACCESS_INC . '/admin-edit-access.php';
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
    echo json_encode(array(
        'output' => wpcf_access_admin_set_custom_roles_level_form(wpcf_get_editable_roles(),
                true),
    ));
    die();
}

/**
 * Suggest user AJAX. 
 */
function wpcf_access_wpcf_access_suggest_user_ajax() {
    global $wpdb;
    $users = array();
    $q = $wpdb->escape(trim($_POST['q']));
    $q = like_escape($q);
    $found = $wpdb->get_results("SELECT ID, display_name, user_login FROM $wpdb->users WHERE user_nicename LIKE '%%$q%%' OR user_login LIKE '%%$q%%' OR display_name LIKE '%%$q%%' OR user_email LIKE '%%$q%%' LIMIT 10");
    if (!empty($found)) {
        foreach ($found as $user) {
            $users[$user->ID] = $user->display_name . ' (' . $user->user_login . ')';
        }
    }
    echo json_encode($users);
    die();
}

/**
 * Adds new custom role. 
 */
function wpcf_access_add_role_ajax() {
    require_once WPCF_ACCESS_INC . '/admin-edit-access.php';
    $capabilities = array('level_0' => true, 'read' => true);
    $caps = wpcf_access_types_caps();
    foreach ($caps as $cap => $data) {
        if ($data['predefined'] == 'read') {
            $capabilities[$cap] = true;
        }
    }
    $success = add_role(str_replace('-', '_', sanitize_title($_POST['role'])),
            $_POST['role'], $capabilities);
    echo json_encode(array(
        'error' => is_null($success) ? 'true' : 'false',
        'output' => is_null($success) ? '<div class="error"><p>' . __('Role already exists',
                        'wpcf_access') . '</p></div>' : wpcf_access_admin_set_custom_roles_level_form(wpcf_get_editable_roles()),
    ));
    die();
}

/**
 * Deletes custom role. 
 */
function wpcf_access_delete_role_ajax() {
    if (!isset($_POST['wpcf_access_delete_role_nonce'])
            || !wp_verify_nonce($_POST['wpcf_access_delete_role_nonce'],
                    'delete_role')) {
        die('verification failed');
    }
    if (in_array(strtolower(trim($_POST['wpcf_access_delete_role'])),
                    array('administrator', 'editor', 'author', 'contributor', 'subscriber'))) {
        $error = 'true';
        $output = '<div class="error"><p>' . __('Role can not be deleted',
                        'wpcf_access') . '</p></div>';
    } else {
        require_once WPCF_ACCESS_INC . '/admin-edit-access.php';
        if ($_POST['wpcf_reassign'] != 'ignore') {
            $users = get_users('role=' . $_POST['wpcf_access_delete_role']);
            foreach ($users as $user) {
                $user = new WP_User($user->ID);
                $user->add_role($_POST['wpcf_reassign']);
            }
        }
        remove_role($_POST['wpcf_access_delete_role']);
        $error = 'false';
        $output = wpcf_access_admin_set_custom_roles_level_form(wpcf_get_editable_roles());
    }
    echo json_encode(array(
        'error' => $error,
        'output' => $output,
    ));
    die();
}