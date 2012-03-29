<?php
/*
 * Conditional display embedded code.
 */
add_filter('wpcf_post_edit_field', 'wpcf_cd_post_edit_field_filter', 10, 4);
add_filter('wpcf_post_groups', 'wpcf_cd_post_groups_filter', 10, 3);

if (!function_exists('wplogger')) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/wplogger.php';
}

/**
 * Filters groups on post edit page.
 * 
 * @param type $groups
 * @param type $post
 * @return type 
 */
function wpcf_cd_post_groups_filter($groups, $post, $context) {
    if ($context != 'group') {
        return $groups;
    }
    foreach ($groups as $key => $group) {
        $group['conditional_display'] = get_post_meta($group['id'],
                '_wpcf_conditional_display', true);
        if (!empty($group['conditional_display']['conditions'])) {
            if (empty($post)) {
                unset($groups[$key]);
                continue;
            }
            $passed = true;
            if (isset($group['conditional_display']['custom_use'])) {
                if (empty($group['conditional_display']['custom'])) {
                    unset($groups[$key]);
                }
                preg_match_all('/\$([^\s]*)/',
                        $group['conditional_display']['custom'], $matches);
                if (empty($matches)) {
                    return array();
                }
                $fields = array();
                foreach ($matches[1] as $key => $field_name) {
                    $fields[$field_name] = wpcf_types_get_meta_prefix(wpcf_admin_fields_get_field($field_name)) . $field_name;
                }
                $fields['evaluate'] = trim($group['conditional_display']['custom']);
                $check = wpv_condition($fields);
                if (!is_bool($check)) {
                    unset($groups[$key]);
                }
                $passed = $check;
            } else {
                $passed_all = true;
                $passed_one = false;
                foreach ($group['conditional_display']['conditions'] as $condition) {
                    $value = get_post_meta($post->ID,
                            wpcf_types_get_meta_prefix($condition['field']) . $condition['field'],
                            true);
                    $check = wpcf_cd_admin_compare($condition['operation'],
                            $value, $condition['value']);
                    if (!$check) {
                        $passed_all = false;
                    } else {
                        $passed_one = true;
                    }
                }
                if (!$passed_all && $group['conditional_display']['relation'] == 'AND') {
                    $passed = false;
                }
                if (!$passed_one && $group['conditional_display']['relation'] == 'OR') {
                    $passed = false;
                }
            }
            if (!$passed) {
                unset($groups[$key]);
            }
        }
    }
    return $groups;
}

/**
 * Checks if there is conditional display.
 * 
 * @param type $element
 * @param type $field
 * @param type $post
 * @return type 
 */
function wpcf_cd_post_edit_field_filter($element, $field, $post,
        $context = 'group') {
    if ($context != 'group') {
        return $element;
    }
    if (!empty($field['data']['conditional_display']['conditions'])) {
        if (empty($post)) {
            return array();
        }
        $passed = true;
        if (isset($field['data']['conditional_display']['custom_use'])) {
            if (empty($field['data']['conditional_display']['custom'])) {
                return array();
            }
            preg_match_all('/\$([^\s]*)/',
                    $field['data']['conditional_display']['custom'], $matches);
            if (empty($matches)) {
                return array();
            }
            $fields = array();
            foreach ($matches[1] as $key => $field_name) {
                $fields[$field_name] = wpcf_types_get_meta_prefix(wpcf_admin_fields_get_field($field_name)) . $field_name;
            }
            $fields['evaluate'] = trim($field['data']['conditional_display']['custom']);
            $check = wpv_condition($fields);
            if (!is_bool($check)) {
                return array();
            }
            $passed = $check;
        } else {
            $passed_all = true;
            $passed_one = false;
            foreach ($field['data']['conditional_display']['conditions'] as $condition) {
                $value = get_post_meta($post->ID,
                        wpcf_types_get_meta_prefix($condition['field']) . $condition['field'],
                        true);
                $check = wpcf_cd_admin_compare($condition['operation'], $value,
                        $condition['value']);
                if (!$check) {
                    $passed_all = false;
                } else {
                    $passed_one = true;
                }
            }
            if (!$passed_all && $field['data']['conditional_display']['relation'] == 'AND') {
                $passed = false;
            }
            if (!$passed_one && $field['data']['conditional_display']['relation'] == 'OR') {
                $passed = false;
            }
        }
        if (!$passed) {
            return array();
        }
    }
    return $element;
}

/**
 * Operations.
 * 
 * @return type 
 */
function wpcf_cd_admin_operations() {
    return array(
        '=' => __('Equal to', 'wpcf'),
        '>' => __('Larger than', 'wpcf'),
        '<' => __('Less than', 'wpcf'),
        '>=' => __('Larger or equal to', 'wpcf'),
        '<=' => __('Less or equal to', 'wpcf'),
        '===' => __('Identical to', 'wpcf'),
        '<>' => __('Not identical to', 'wpcf'),
        '!==' => __('Strictly not equal', 'wpcf'),
//        'between' => __('Between', 'wpcf'),
    );
}

/**
 * Compares values.
 * 
 * @param type $operation
 * @return type 
 */
function wpcf_cd_admin_compare($operation) {
    $args = func_get_args();
    switch ($operation) {
        case '=':
            return $args[1] == $args[2];
            break;

        case '>':
            return intval($args[1]) > intval($args[2]);
            break;

        case '>=':
            return intval($args[1]) >= intval($args[2]);
            break;

        case '<':
            return intval($args[1]) < intval($args[2]);
            break;

        case '<=':
            return intval($args[1]) <= intval($args[2]);
            break;

        case '===':
            return $args[1] === $args[2];
            break;

        case '!==':
            return $args[1] !== $args[2];
            break;

        case '<>':
            return $args[1] <> $args[2];
            break;

        case 'between':
            return intval($args[1]) > intval($args[2]) && intval($args[1]) < intval($args[3]);
            break;

        default:
            break;
    }
    return true;
}