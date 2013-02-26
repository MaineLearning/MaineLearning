<?php
/*
 * Dependencies definitions.
 */

add_action('admin_footer', 'wpcf_access_dependencies_render_js');
add_action('wp_footer', 'wpcf_access_dependencies_render_js');
add_filter('types_access_dependencies', 'wpcf_access_dependencies_filter');

/**
 * Defines dependencies.
 * 
 * @return array 
 */
function wpcf_access_dependencies() {
    $deps = array(
        'edit_own' => array(
            'true_allow' => array('read'),
            'false_disallow' => array('edit_any', 'publish')
        ),
        'edit_any' => array(
            'true_allow' => array('read', 'edit_own'),
        ),
        'publish' => array(
            'true_allow' => array('read', 'edit_own', 'delete_own'),
        ),
        'delete_own' => array(
            'true_allow' => array('read'),
            'false_disallow' => array('delete_any', 'publish'),
        ),
        'delete_any' => array(
            'true_allow' => array('read', 'delete_own'),
        ),
        'read' => array(
            'false_disallow' => array('edit_own', 'delete_own', 'edit_any',
                'delete_any', 'publish'),
        ),
        'edit_terms' => array(
            'false_disallow' => array('manage_terms'),
        ),
        'manage_terms' => array(
            'true_allow' => array('edit_terms', 'delete_terms')
        ),
        'delete_terms' => array(
            'true_allow' => array('manage_terms')
        ),
        'assign_terms' => array()/*,
        'edit_comments' => array('edit_any','edit_own')*/
    );
    return $deps;
}

/**
 * Renders JS 
 */
function wpcf_access_dependencies_render_js() {
    $deps = wpcf_access_dependencies();
    $output = '';
    $output .= "\n\n" . '<script type="text/javascript">' . "\n";
    $active = array();
    $inactive = array();
    $active_message = array();
    $inactive_message = array();

    $output .= 'var wpcf_access_dep_active_messages_pattern_singular = "'
            . __("Since you enabled '%cap', '%dcaps' has also been enabled.",
                    'wpcf')
            . '";' . "\n";
    $output .= 'var wpcf_access_dep_active_messages_pattern_plural = "'
            . __("Since you enabled '%cap', '%dcaps' have also been enabled.",
                    'wpcf')
            . '";' . "\n";
    $output .= 'var wpcf_access_dep_inactive_messages_pattern_singular = "'
            . __("Since you disabled '%cap', '%dcaps' has also been disabled.",
                    'wpcf')
            . '";' . "\n";
    $output .= 'var wpcf_access_dep_inactive_messages_pattern_plural = "'
            . __("Since you disabled '%cap', '%dcaps' have also been disabled.",
                    'wpcf')
            . '";' . "\n";
    /*$output .= 'var wpcf_access_edit_comments_inactive = "'
            . __("Since you disabled '%dcaps' user/role will not be able to edit comments also.",
                    'wpcf')
            . '";' . "\n";*/

    foreach ($deps as $dep => $data) {
        $dep_data = wpcf_access_get_cap_predefined_settings($dep);
        $output .= 'var wpcf_access_dep_' . $dep . '_title = "'
                . $dep_data['title']
                . '";' . "\n";
        foreach ($data as $dep_active => $dep_set) {
            if (strpos($dep_active, 'true_') === 0) {
                $active[$dep][] = '\'' . implode('\', \'', $dep_set) . '\'';
                foreach ($dep_set as $cap) {
                    $_cap = wpcf_access_get_cap_predefined_settings($cap);
                    $active_message[$dep][] = $_cap['title'];
                }
            } else {
                $inactive[$dep][] = '\'' . implode('\', \'', $dep_set) . '\'';
                foreach ($dep_set as $cap) {
                    $_cap = wpcf_access_get_cap_predefined_settings($cap);
                    $inactive_message[$dep][] = $_cap['title'];
                }
            }
        }
    }

    foreach ($active as $dep => $array) {
        $output .= 'var wpcf_access_dep_true_' . $dep . ' = ['
                . implode(',', $array) . '];' . "\n";
        $output .= 'var wpcf_access_dep_true_' . $dep . '_message = [\''
                . implode('\',\'', $active_message[$dep]) . '\'];' . "\n";
    }

    foreach ($inactive as $dep => $array) {
        $output .= 'var wpcf_access_dep_false_' . $dep . ' = ['
                . implode(',', $array) . '];' . "\n";
        $output .= 'var wpcf_access_dep_false_' . $dep . '_message = [\''
                . implode('\',\'', $inactive_message[$dep]) . '\'];' . "\n";
    }

    $output .= '</script>' . "\n\n";
    echo $output;
}

/**
 * Returns specific cap dependencies.
 * 
 * @param type $cap
 * @param type $true
 * @return type 
 */
function wpcf_access_dependencies_get($cap, $true = true) {
    $deps = wpcf_access_dependencies();
    $_deps = array();
    if (isset($deps[$cap])) {
        foreach ($deps[$cap] as $dep_active => $data) {
            if ($true && strpos($dep_active, 'true_') === 0) {
                $_deps[substr($dep_active, 5)] = $data;
            } else {
                $_deps[substr($dep_active, 6)] = $data;
            }
        }
    }
    return $_deps;
}

/**
 * Filters dependencies.
 * 
 * @param type $args 
 */
function wpcf_access_dependencies_filter($args) {
    $allow = $args[0];
    $disallow = $args[1];
    $set = $args[2];
    foreach ($set as $data) {
        $context = $data['context'] == 'taxonomies' ? 'taxonomy' : 'post_type';
        $name = $data['parent'];
        $caps = $data['caps'];

        // Check dependencies and map them to WP readable
        foreach ($caps as $_cap => $true) {
            $true = (bool) $true;

            // Get dependencies settings by cap
            $deps = wpcf_access_dependencies_get($_cap, $true);

            // Map to WP rules
            if (!empty($deps['allow'])) {
                foreach ($deps['allow'] as $__cap) {
                    $caps_readable = wpcf_access_predefined_to_wp_caps($context,
                            $name, $__cap);
                    $allow = $caps_readable + $allow;
                }
            }
            if (!empty($deps['disallow'])) {
                foreach ($deps['disallow'] as $__cap) {
                    $caps_readable = wpcf_access_predefined_to_wp_caps($context,
                            $name, $__cap);
                    $disallow = $caps_readable + $disallow;
                }
            }
        }
    }
    return array($allow, $disallow);
}