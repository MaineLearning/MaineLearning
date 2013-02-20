<?php
/*
 * Exceptions.
 */

add_filter('types_access_check', 'wpcf_access_exceptions_check', 10, 3);

/**
 * Filters cap.
 * 
 * @param type $capability_requested
 * @return string 
 */
function wpcf_access_exceptions_check() {
    $args = func_get_args();
    $capability_requested = $args[0][0];
    $parse_args = $args[0][1];
    $args = $args[0][2];
    $found = wpcf_access_search_cap($capability_requested);
    // Allow filtering
    list($capability_requested, $parse_args, $args) = apply_filters('wpcf_access_exceptions',
            array($capability_requested, $parse_args, $args, $found));

    switch ($capability_requested) {
        case 'edit_comment':
            $capability_requested = 'edit_posts';
            $parse_args['caps'] = array('edit_published_posts', 'edit_comment');
            break;

        case 'moderate_comments':
            $capability_requested = 'edit_others_posts';
            $parse_args['caps'] = array('edit_published_posts', 'edit_comment');
            break;

//        case 'delete_post':
//        case 'edit_post':
        default:
            // TODO Wachout for more!
            if (isset($args[1]) && isset($args[2])) {
                $user = get_userdata(intval($args[1]));
                $post_id = intval($args[2]);
                $post = get_post($post_id);

                if (!empty($user->ID) && !empty($post)) {
                    $parse_args_clone = $parse_args;
                    $args_clone = $args;
                    // check post id is valid, avoid capabilities warning
                    if (intval($post->ID)>0) {
                        $map = map_meta_cap($capability_requested, $user->ID,
                                $post->ID);
                        if (is_array($map) && !empty($map[0])) {
                            foreach ($map as $cap) {
                                $args_clone = array($cap);
                                $result = wpcf_access_check($parse_args_clone['allcaps'],
                                        $map, $args_clone, false);
                                if (!$result) {
                                    $parse_args['caps'] = array();
                                }
                            }
                        }
                    }
                    // Not sure why we didn't use this mapping before
                    $capability_requested = wpcf_access_map_cap($capability_requested,
                            $post_id);
                }

                if (WPCF_ACCESS_DEBUG) {
                    global $wpcf_access;
                    $wpcf_access->debug_hooks_with_args[$capability_requested][] = array(
                        'args' => $args,
                    );
                }
            }
            break;
    }
    return array($capability_requested, $parse_args, $args);
}