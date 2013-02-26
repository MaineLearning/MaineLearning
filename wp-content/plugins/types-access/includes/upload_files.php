<?php
/*
 * Added to handle 3.5 changes
 */

global $wp_version;
if (version_compare($wp_version, '3.4.3', '<')) {
    add_action('wpcf_access_late_init', 'wpcf_access_user_can_upload_files');
} else {
    add_action('wpcf_access_late_init', 'wpcf_access_user_can_upload_files_update');
}
add_action('types_access_check_override', 'wpcf_access_files_override', 10, 2);

/**
 * Handle uploads.
 * 
 * Set 'upload_files' capability for current user on 'init' hook.
 * After we set default capabilities, we dynamically set upload_files
 * to match current action.
 * 
 * @global type $current_user
 * @global type $wpcf_access 
 */
function wpcf_access_user_can_upload_files() {
    global $wpcf_access;
    $current_user = wp_get_current_user();
    list($role, $level) = wpcf_access_rank_user($current_user->ID);

    // Enqueue
    add_filter('wpcf_access_exceptions', 'wpcf_access_exceptions_upload_files',
            10, 4);
    add_filter('types_access_check_override',
            'wpcf_access_upload_files_check_override');

    // First detect if attachment
    $post_type = wpcf_access_attachment_parent_type();

    // Determine post_type
    if (empty($post_type)) {
        $post_id = wpcf_access_determine_post_id();
        if ($post_id) {
            $post_type = get_post_type(get_post($post_id));
        } else {
            $post_type = wpcf_access_determine_post_type();
        }
        if (empty($post_type)) {
            $post_type = 'post';
        }
    }

    $wpcf_access->upload_files['post_type'] = $post_type;

    // If rule for post_type exists - follow it
    if (!empty($current_user->allcaps) && !empty($post_type)) {
        
        // TODO Monitor this
        $post_type_obj = get_post_type_object($post_type);
        if (is_null($post_type_obj)) {
            $wpcf_access->errors['post_type_object_missing'][] = $post_type;
            return false;
        }
        $wpcf_access->upload_files['post_type_cap'] = $post_type_obj->cap;
        if (!empty($post_type_obj->cap->edit_posts)) {
            $cap_found = wpcf_access_search_cap($post_type_obj->cap->edit_posts);
            if (!empty($cap_found)) {
                $wpcf_access->upload_files['cap_found'] = $cap_found;
                $allow = wpcf_access_is_role_ranked_higher($role,
                        $cap_found['role']);
                if (!$allow) {
                    $allow = in_array($current_user->ID, $cap_found['users']);
                }
                if (!$allow) {
                    unset($current_user->allcaps['upload_files']);
                    unset($current_user->caps['upload_files']);
                } else {
                    $current_user->allcaps['upload_files'] = 1;
                    $current_user->caps['upload_files'] = 1;
                }
                $wpcf_access->upload_files['allow'] = (bool) $allow ? 1 : 0;
                // If found return $allow
                return $allow;
            }
        }
    }
    $wpcf_access->upload_files['handled'] = 0;
    $wpcf_access->upload_files['allow'] = !empty($current_user->allcaps['upload_files']) ? 1 : 0;
    // Return default setting if not found
    return !empty($current_user->allcaps['upload_files']);
}

/**
 * Filters exceptions check.
 * 
 * @param type $args
 * @return type 
 */
function wpcf_access_exceptions_upload_files($args) {
    global $wpcf_access;
    $capability_requested = $args[0];
    $parse_args = $args[1];
    $found = $args[3];
    $args = $args[2];

    // This is case when user uploads file from post edit screen
    // or on Media Library screen
    if (!empty($found) && is_admin()
            && (strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/media-upload.php') !== false
            || strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/upload.php') !== false
            || strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/async-upload.php') !== false
            || strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/media-new.php') !== false
            )
    ) {
        $temp = array();
        $post_id = wpcf_access_determine_post_id();

        // If attachment_id is present
        if (isset($_POST['attachment_id'])) {
            $post_id = intval($_POST['attachment_id']);
        }

        // Get post
        $post = get_post($post_id);

        // If post exists and is attachment - process it
        if (!empty($post) && $post->post_type == 'attachment') {
            $temp['capability_requested'] = $capability_requested;

            //
            //
            //
            // This is Media Library screen
            //
            //
            //
            if (strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/upload.php') !== false
                    || strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/media-new.php') !== false) {

                // If Media post_type exists use built-in WP check
                if (wpcf_access_is_media_registered()) {
                    if (isset($post->post_parent)) {
                        $temp['is_attachment'] = 1;
                    }
                    $capability_requested = wpcf_access_map_cap($capability_requested,
                            $post_id);
                    $temp['capability_converted'] = $capability_requested;
                } else {


                    // If version 3.4 check if user can edit parent post type
                    // TODO check if post is attached to multiple posts
                    // (looks like only first parent is saved)
                    // Attachment follows parent post type
                    if (isset($post->post_parent)) {
                        $temp['is_attachment'] = 1;
                        $capability_requested = wpcf_access_map_cap($capability_requested,
                                $post->post_parent);
                        $temp['capability_converted'] = $capability_requested;
                    } else {// This happens in case item is newly added to media library
                        $temp['parent'] = 'no_parent';
                        $capability_requested = wpcf_access_map_cap($capability_requested,
                                $post_id);
                        $temp['capability_converted'] = $capability_requested;
                    }
                }
            } else {
                //
                //
                //
                //
                //
                // This is upload screen
                //
                //
                //
                
                // No matter if Media post_type is registered,
                // on upload screens we always convert capability to match
                // parent post type
                // TODO check if post is attached to multiple posts
                // (looks like only first parent is saved)
                // Attachment follows parent post type
                if (isset($post->post_parent)) {
                    $temp['is_attachment'] = 1;
                    $capability_requested = wpcf_access_map_cap($capability_requested,
                            $post->post_parent);
                    $temp['capability_converted'] = $capability_requested;
                } else {
                    $temp['parent'] = 'no_parent';
                    $capability_requested = wpcf_access_map_cap($capability_requested,
                            $post_id);
                    $temp['capability_converted'] = $capability_requested;
                }
            }
            $wpcf_access->upload_files['exceptions.php']['media_screen'][] = $temp;
        }
    } else {

        // Simply check if post is attachment and map it to parent cap
        $temp = array();
        $temp['capability_requested'] = $capability_requested;
        $post_id = wpcf_access_determine_post_id();
        $post = get_post($post_id);
        if (!empty($post) && $post->post_type == 'attachment') {
            if (isset($post->post_parent)) {
                $temp['is_attachment'] = 1;
                $capability_requested = wpcf_access_map_cap($capability_requested,
                        $post->post_parent);
                $temp['capability_converted'] = $capability_requested;
            } else {
                $temp['parent'] = 'no_parent';
                $capability_requested = wpcf_access_map_cap($capability_requested,
                        $post_id);
                $temp['capability_converted'] = $capability_requested;
            }
            $wpcf_access->upload_files['exceptions.php']['attachments'][] = $temp;
        }
    }

    return array($capability_requested, $parse_args, $args);
}

/**
 * Override 'upload_files' check.
 * 
 * Fixes upload_files check.
 * 
 * @param type $null
 * @param type $parse_args 
 */
function wpcf_access_upload_files_check_override($null, $parse_args = array()) {
    // Fix upload files
    if (isset($parse_args['cap']) && $parse_args['cap'] == 'upload_files') {
        return wpcf_access_parse_caps((bool) wpcf_access_user_can_upload_files(),
                        $parse_args);
    }
    return $null;
}

/**
 * Updated upload_files filter.
 * 
 * WP introduced built-in Media post type. This sometimes overlaps with
 * upload_files capability. We must see how and if we need to make any decisions
 * how these two capabilities should work in conjuction.
 * 
 * Debug property
 * $wpcf_access->upload_files
 * 
 * @since WP 3.5
 * @global type $wpcf_access 
 */
function wpcf_access_user_can_upload_files_update() {
    global $wpcf_access;
    $wpcf_access->upload_files['new_media_post_type'] = true;
    wpcf_access_user_can_upload_files();
}

/**
 * WP 3.5 This is fix for inserting to editor.
 * 
 * New GUI checks if current use can 'edit_post' with certain ID
 * even if attachment is in question.
 * 
 * Access logic requires that attachment in this case can be inserted
 * in parent post if user can edit parent post_type.
 * 
 * @param type $null
 * @param type $parse_args
 * @return type 
 */
function wpcf_access_files_override($null, $parse_args) {
    // To check if on media upload screen use
    // either basename($_SERVER['SCRIPT_NAME']) == 'async-upload.php'
    // or strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/async-upload.php') !== false
    // Fix types upload
    if ($parse_args['cap'] == 'upload_files' &&
            !isset($_REQUEST['action']) &&
            isset($_POST['post_id']) &&
            isset($_SERVER['SCRIPT_NAME']) &&
            strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/async-upload.php') !== false) {
        // This should be the end of a types image upload
        // temporarily set the $_REQUEST['action'] and process the same as send-attachment-to-editor
        $_REQUEST['action'] = 'types-end-image-upload';
    }

    if ($parse_args['cap'] == 'upload_files' &&
            isset($_REQUEST['fetch']) &&
            isset($_SERVER['SCRIPT_NAME']) &&
            strpos($_SERVER['SCRIPT_NAME'], '/wp-admin/async-upload.php') !== false) {
        // This should be the crunching part types image upload
        // We assume that if we got here then this request is ok.
        return wpcf_access_parse_caps(true, $parse_args);
    }

    // Fix ending to editor
    if (isset($_REQUEST['action'])) {
        $action = strval($_REQUEST['action']);
        switch ($action) {
            case 'send-attachment-to-editor':
            case 'types-end-image-upload':
                if ($_REQUEST['action'] == 'types-end-image-upload') {
                    // remove the temporary action.
                    unset($_REQUEST['action']);
                }

                $parent_id = intval($_POST['post_id']);
                // If user can edit parent post
                // than he can edit attachment too (at least in this case)
                $map = map_meta_cap($parse_args['cap'], get_current_user_id(),
                        $parent_id);
                $result = wpcf_access_check($parse_args['allcaps'], $map,
                        $parse_args['args'], false);

                if (!$result) {
                    return wpcf_access_parse_caps(false, $parse_args);
                } else {
                    return wpcf_access_parse_caps(true, $parse_args);
                }

                break;


            default:
                break;
        }
    }
    return $null;
}