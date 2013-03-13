<?php
/*
 * Conditional display embedded code.
 */

/*
 * Post page filters
 */
add_filter( 'wpcf_post_edit_field', 'wpcf_cd_post_edit_field_filter', 10, 4 );
add_filter( 'wpcf_post_groups', 'wpcf_cd_post_groups_filter', 10, 3 );

/*
 * 
 * These hooks check if conditional failed
 * but form allowed to be saved
 * Since Types 1.2
 */
add_filter( 'wpcf_post_form_error', 'wpcf_conditional_post_form_error_filter',
        10, 2 );


/*
 * Logger
 */
if ( !function_exists( 'wplogger' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/wplogger.php';
}
if ( !function_exists( 'wpv_filter_parse_date' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/wpv-filter-date-embedded.php';
}

wpcf_admin_add_js_settings( 'wpcfConditionalVerifyGroup',
        wp_create_nonce( 'cd_group_verify' ) );

/*
 * since Types 1.2 Filter validation JS
 */
add_filter( 'wpcf_validation_js_invalid_handler',
        'wpcf_conditional_validation_js_invalid_handler_filter', 10, 3 );

/**
 * Filters groups on post edit page.
 * 
 * @param type $groups
 * @param type $post
 * @return type 
 */
function wpcf_cd_post_groups_filter( $groups, $post, $context ) {
    if ( $context != 'group' ) {
        return $groups;
    }

    foreach ( $groups as $key => &$group ) {
        $meta_conditional = !isset( $group['conditional_display'] ) ? get_post_meta( $group['id'],
                        '_wpcf_conditional_display', true ) : $group['conditional_display'];
        if ( !empty( $meta_conditional['conditions'] ) ) {
            $group['conditional_display'] = $meta_conditional;
            add_action( 'admin_head', 'wpcf_cd_add_group_js' );
            if ( empty( $post->ID ) ) {
                $group['_conditional_display'] = 'failed';
                continue;
            }
            $passed = true;
            if ( isset( $group['conditional_display']['custom_use'] ) ) {
                if ( empty( $group['conditional_display']['custom'] ) ) {
                    $group['_conditional_display'] = 'failed';
                    continue;
                }

                $evaluate = trim( stripslashes( $group['conditional_display']['custom'] ) );
                // Check dates
                $evaluate = wpv_filter_parse_date( $evaluate );
                // Add quotes = > < >= <= === <> !==
                $strings_count = preg_match_all( '/[=|==|===|<=|<==|<===|>=|>==|>===|\!===|\!==|\!=|<>]\s(?!\$)(\w*)[\)|\$|\W]/',
                        $evaluate, $matches );
                if ( !empty( $matches[1] ) ) {
                    foreach ( $matches[1] as $temp_match ) {
                        $temp_replace = is_numeric( $temp_match ) ? $temp_match : '\'' . $temp_match . '\'';
                        $evaluate = str_replace( ' ' . $temp_match . ')',
                                ' ' . $temp_replace . ')', $evaluate );
                    }
                }
                preg_match_all( '/\$([^\s]*)/',
                        $group['conditional_display']['custom'], $matches );
                if ( empty( $matches ) ) {
                    $group['_conditional_display'] = 'failed';
                    continue;
                }
                $fields = array();
                foreach ( $matches[1] as $key => $field_name ) {
                    $fields[$field_name] = wpcf_types_get_meta_prefix( wpcf_admin_fields_get_field( $field_name ) ) . $field_name;
                    wpcf_cd_add_group_js( 'add', $field_name, '', '',
                            $group['id'] );
                }
                $fields['evaluate'] = $evaluate;
                $check = wpv_condition( $fields );
                $passed = $check;
                if ( !is_bool( $check ) ) {
                    $passed = false;
                    $group['_conditional_display'] = 'failed';
                } else if ( $check ) {
                    $group['_conditional_display'] = 'passed';
                } else {
                    $group['_conditional_display'] = 'failed';
                }
            } else {
                $passed_all = true;
                $passed_one = false;
                foreach ( $group['conditional_display']['conditions'] as
                            $condition ) {
                    // Load field
                    $field = wpcf_admin_fields_get_field( $condition['field'] );
                    wpcf_fields_type_action( $field['type'] );

                    wpcf_cd_add_group_js( 'add', $condition['field'],
                            $condition['value'], $condition['operation'],
                            $group['id'] );
                    $value = get_post_meta( $post->ID,
                            wpcf_types_get_meta_prefix( $condition['field'] ) . $condition['field'],
                            true );
                    $value = apply_filters( 'wpcf_conditional_display_compare_meta_value',
                            $value, $condition['field'],
                            $condition['operation'], $key, $post );
                    $condition['value'] = apply_filters( 'wpcf_conditional_display_compare_condition_value',
                            $condition['value'], $condition['field'],
                            $condition['operation'], $key, $post );
                    $check = wpcf_cd_admin_compare( $condition['operation'],
                            $value, $condition['value'] );
                    if ( !$check ) {
                        $passed_all = false;
                    } else {
                        $passed_one = true;
                    }
                }
                if ( !$passed_all && $group['conditional_display']['relation'] == 'AND' ) {
                    $passed = false;
                }
                if ( !$passed_one && $group['conditional_display']['relation'] == 'OR' ) {
                    $passed = false;
                }
            }
            if ( !$passed ) {
                $group['_conditional_display'] = 'failed';
            } else {
                $group['_conditional_display'] = 'passed';
            }
        }
    }
    return $groups;
}

/**
 * Checks if there is conditional display.
 * 
 * This function filters all fields that appear in form.
 * It checks if field is Check Trigger or Conditional.
 * Since Types 1.2 this functin is simplified and should stay that way.
 * It's important core action.
 * 
 * 
 * @param type $element
 * @param type $field
 * @param type $post
 * @return type 
 */
function wpcf_cd_post_edit_field_filter( $element, $field, $post,
        $context = 'group' ) {

    // Do not use on repetitive
    if ( defined( 'DOING_AJAX' ) && $context == 'repetitive' ) {
        return $element;
    }

    global $wpcf;

    /*
     * 
     * 
     * Since Types 1.2
     * Automatically evaluates WPCF_Conditional::set()
     * Evaluation moved to WPCF_Conditional::evaluate()
     */
    if ( $wpcf->conditional->is_conditional( $field )
            || $wpcf->conditional->is_trigger( $field ) ) {

        wpcf_conditional_add_js();
        $wpcf->conditional->set( $post, $field );

        /*
         * Check if field is check trigger and wrap it
         * (add CSS class 'wpcf-conditonal-check-trigger')
         */
        if ( $wpcf->conditional->is_trigger( $field ) ) {
            $element = $wpcf->conditional->wrap_trigger( $element );
        }

        /*
         * If conditional
         */
        if ( $wpcf->conditional->is_conditional( $field ) ) {
            $element = $wpcf->conditional->wrap( $element );
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
        '=' => __( 'Equal to', 'wpcf' ),
        '>' => __( 'Larger than', 'wpcf' ),
        '<' => __( 'Less than', 'wpcf' ),
        '>=' => __( 'Larger or equal to', 'wpcf' ),
        '<=' => __( 'Less or equal to', 'wpcf' ),
        '===' => __( 'Identical to', 'wpcf' ),
        '<>' => __( 'Not identical to', 'wpcf' ),
        '!==' => __( 'Strictly not equal', 'wpcf' ),
//        'between' => __('Between', 'wpcf'),
    );
}

/**
 * Compares values.
 * 
 * @param type $operation
 * @return type 
 */
function wpcf_cd_admin_compare( $operation ) {
    $args = func_get_args();
    switch ( $operation ) {
        case '=':
            return $args[1] == $args[2];
            break;

        case '>':
            return intval( $args[1] ) > intval( $args[2] );
            break;

        case '>=':
            return intval( $args[1] ) >= intval( $args[2] );
            break;

        case '<':
            return intval( $args[1] ) < intval( $args[2] );
            break;

        case '<=':
            return intval( $args[1] ) <= intval( $args[2] );
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
            return intval( $args[1] ) > intval( $args[2] ) && intval( $args[1] ) < intval( $args[3] );
            break;

        default:
            break;
    }
    return true;
}

/**
 * Setsa all JS. 
 */
function wpcf_conditional_add_js() {
    wpcf_cd_add_field_js();
}

/**
 * JS for fields AJAX.
 */
function wpcf_cd_add_field_js() {
    global $wpcf;
    $wpcf->conditional->add_js();
}

/**
 * Register JS for groups AJAX.
 * 
 * @staticvar array $conditions
 * @param type $call
 * @param type $field
 * @param type $value
 * @param type $condition
 * @param type $group_id
 * @return string 
 */
function wpcf_cd_add_group_js( $call, $field = false, $value = false,
        $condition = false, $group_id = false ) {
    static $conditions = array();
    if ( $call == 'add' ) {
        /*
         * Since Types 1.2 We changed array structure (nested in group_id)
         */
        $conditions[$group_id][$field] = array(
            'field' => $field,
            'value' => $value,
            'condition' => $condition,
            'group_id' => $group_id
        );
        return '';
    }
    wpcf_cd_add_group_js_render( $conditions );
}

/**
 * JS for groups AJAX.
 * 
 * @param type $conditions 
 */
function wpcf_cd_add_group_js_render( $conditions = array() ) {

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
    <?php
    foreach ( $conditions as $groups ) {
        foreach ( $groups as $field => $data ) {

            ?>
                            if (jQuery('[name="wpcf[<?php echo $field; ?>]"]').hasClass('radio')
                                || jQuery('[name="wpcf[<?php echo $field; ?>]"]').hasClass('checkbox')) {
                                jQuery('[name="wpcf[<?php echo $field; ?>]"]').bind('click', function(){
                                    wpcfCdGroupVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val(), <?php echo $data['group_id']; ?>);
                                });
                            } else if (jQuery('[name="wpcf[<?php echo $field; ?>]"]').hasClass('select')) {
                                jQuery('[name="wpcf[<?php echo $field; ?>]"]').bind('change', function(){
                                    wpcfCdGroupVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val(), <?php echo $data['group_id']; ?>);
                                });
                            } else if (jQuery('[name="wpcf[<?php echo $field; ?>]"]').hasClass('wpcf-datepicker')) {
                                jQuery('[name="wpcf[<?php echo $field; ?>]"]').bind('wpcfDateBlur', function(){
                                    wpcfCdGroupVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val(), <?php echo $data['group_id']; ?>);
                                });
                            } else {
                                jQuery('[name="wpcf[<?php echo $field; ?>]"]').bind('blur', function(){
                                    wpcfCdGroupVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val(), <?php echo $data['group_id']; ?>);
                                });
                            }
            <?php
        }
    }

    ?>
            jQuery('.wpcf-cd-group-failed').parents('.postbox').hide();
        });
    </script>
    <?php
}

/**
 * Passes $_POST values for AJAX call.
 * 
 * @param type $null
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type 
 */
function wpcf_cd_meta_ajax_validation_filter( $null, $object_id, $meta_key,
        $single ) {
    $meta_key = str_replace( 'wpcf-', '', $meta_key );
    $field = wpcf_admin_fields_get_field( $meta_key );
    if ( isset( $_POST['wpcf'][$meta_key] ) && !empty( $field ) && $field['type'] == 'date' ) {
        $time = strtotime( $_POST['wpcf'][$meta_key] );
        if ( $time ) {
            return $time;
        }
    }
    return isset( $_POST['wpcf'][$meta_key] ) ? $_POST['wpcf'][$meta_key] : '';
}

/**
 * Passes $_POST values for AJAX call.
 * 
 * @param type $null
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type 
 */
function wpcf_cd_pr_meta_ajax_validation_filter( $null, $object_id, $meta_key,
        $single ) {
    $field = wpcf_admin_fields_get_field( $meta_key );
    if ( isset( $_POST['wpcf_post_relationship'][$meta_key] ) && !empty( $field ) && $field['type'] == 'date' ) {
        $time = strtotime( $_POST['wpcf_post_relationship'][$meta_key] );
        if ( $time ) {
            return $time;
        }
    }
    return isset( $_POST['wpcf_post_relationship'][$meta_key] ) ? $_POST['wpcf_post_relationship'][$meta_key] : '';
}

/**
 * Filters jQuery invalidHandler.
 * 
 * Added to allow additonal processing when form is invalid.
 * 
 * @since 1.1.5
 * @param type $string 
 * @param type $elements
 */
function wpcf_conditional_validation_js_invalid_handler_filter( $string,
        $elements, $selector ) {

    if ( empty( $elements ) ) {
        return '';
    }

    global $wpcf;

    /*
     * Get element and check if element is conditional AND hidden.
     * If so - remove rule and submit form.
     * This is done only on #post NOT internal Types forms
     */
    if ( strpos( trim( $selector ), '#post' ) === 0 ) {
        ob_start();

        ?>
        if (wpcfConditionalInvalidHandler('<?php echo $selector; ?>', elements, form, validator)) {
        passed = true;
        }
        <?php
        $string .= ob_get_contents();
        ob_end_clean();
    }

    return $string;
}

/**
 * Post form error filter.
 * 
 * Leave element as not_valid (it will prevent saving) just remove warning.
 * 
 * @global type $wpcf
 * @param type $_error
 * @param type $_not_valid
 * @return boolean
 */
function wpcf_conditional_post_form_error_filter( $_error, $_not_valid ) {
    if ( !empty( $_not_valid ) ) {

        global $wpcf;

        $count = 0;
        $count_non_conditional = 0;
        $error_conditional = false;

        foreach ( $_not_valid as $f ) {
            $field = $f['_field'];
            /*
             * Here we add simple check
             * 
             * TODO Improve this check
             * We can not tell for sure if it failed except to again check
             * conditionals
             */
            // See if field is conditional
            if ( isset( $field->cf['data']['conditional_display'] ) ) {

                // Use Conditional class
                $test = new WPCF_Conditional();
                $test->set( $wpcf->post, $field->cf );

                // See if evaluated right
                $passed = $test->evaluate();

                // If evaluated FALSE that means error is expected
                if ( $passed ) {
                    $error_conditional = true;
                }

                // Count it
                $count++;
            } else {
                $count_non_conditional++;
            }
            /*
             * If non-conditional fields are not valid - return $_error TRUE
             * If at least one conditional failed - return FALSE
             */
            if ( $count_non_conditional > 0 ) {
                return true;
            }
            return $error_conditional;
        }
    }
    return $_error;
}