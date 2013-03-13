<?php
/*
 * Conditional class
 * 
 * Few break-points summarized:
 * 1. Wrapping fields when in form
 * 2. Filtering AJAX check
 * 3. Calling JS
 */

/**
 * Conditional class.
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Conditional
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Evaluate
{

    /**
     * Main conditinal evaluation function.
     * 
     * This is important break-point.
     * 
     * @since 1.2
     * @version 0.1
     * @param type $o
     * @return boolean 
     */
    function evaluate( $o ) {

        // Set vars
        $post = $o->post;
        $field = $o->cf;

        /*
         * 
         * Since Types 1.2
         * We force initial value to be FALSE.
         * Better to have restricted than allowed because of sensitive data.
         * If conditional is set on field and it goes wrong - better to abort
         * so user can report bug without exposing his content.
         */
        $passed = false;

        if ( empty( $post->ID ) ) {
            /*
             * 
             * Keep all forbidden if post is not saved.
             */
            $passed = false;
            /*
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * VIEWS
             * 
             * Custom call uses Views code
             * wpv_filter_parse_date()
             * wpv_condition()
             */
        } else if ( isset( $field['data']['conditional_display']['custom_use'] ) ) {
            /*
             * 
             * 
             * More malformed forbids
             */
            if ( empty( $field['data']['conditional_display']['custom'] ) ) {
                return false;
            }

            /*
             * 
             * 
             * Filter meta values (switch them with $_POST values)
             * Used by Views, Types do not need it.
             */
            add_filter( 'get_post_metadata',
                    'wpcf_cd_meta_ajax_validation_filter', 10, 4 );

            /*
             * 
             * Set statement
             */
            $evaluate = trim( stripslashes( $field['data']['conditional_display']['custom'] ) );
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
                    $field['data']['conditional_display']['custom'], $matches );



            if ( empty( $matches ) ) {
                /*
                 * 
                 * If statement false
                 */
                $passed = false;
            } else {
                /*
                 * 
                 * 
                 * If statement right, check condition
                 */
                $fields = array();
                foreach ( $matches[1] as $field_name ) {
                    /*
                     * 
                     * 
                     * This field value is checked
                     */
                    $f = wpcf_admin_fields_get_field( trim( strval( $field_name ) ) );
                    if ( empty( $f ) ) {
                        return false;
                    }

                    $c = new WPCF_Field();
                    $c->set( $post, $f );

                    // Set field
                    $fields[$field_name] = $c->slug;
                }
                $fields['evaluate'] = $evaluate;
                $check = wpv_condition( $fields );

                /*
                 * 
                 * 
                 * Views return string malformed,
                 * boolean if call completed.
                 */
                if ( !is_bool( $check ) ) {
                    $passed = false;
                } else {
                    $passed = $check;
                }
            }

            /*
             * 
             * 
             * Remove filter meta values
             */
            remove_filter( 'get_post_metadata',
                    'wpcf_cd_meta_ajax_validation_filter', 10, 4 );
        } else {
            /*
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * TYPES
             * 
             * If not custom code, use Types built-in check.
             * wpcf_cd_admin_compare()
             */
            $passed_all = true;
            $passed_one = false;

            // Basic check
            if ( empty( $field['data']['conditional_display']['conditions'] ) ) {
                return false;
            }

            // Keep count to see if OR/AND relation needed
            $count = count( $field['data']['conditional_display']['conditions'] );

            foreach ( $field['data']['conditional_display']['conditions'] as
                        $condition ) {
                /*
                 * 
                 * 
                 * Malformed condition and should be treated as forbidden
                 */
                if ( !isset( $condition['field'] ) || !isset( $condition['operation'] )
                        || !isset( $condition['value'] ) ) {
                    $passed_one = false;
                    continue;
                }
                /*
                 * 
                 * 
                 * This field value is checked
                 */
                $f = wpcf_admin_fields_get_field( trim( strval( $condition['field'] ) ) );
                if ( empty( $f ) ) {
                    return false;
                }

                $c = new WPCF_Field();
                $c->set( $post, $f );

                /*
                 * 
                 * Since Types 1.2
                 * meta is property of WPCF_Field::$__meta
                 * 
                 * BREAKPOINT
                 * This is where values for evaluation are set.
                 * Please do not allow other places - use hooks.
                 */
                $value = defined( 'DOING_AJAX' ) ? $c->_get_meta( 'POST' ) : $c->__meta;

                /*
                 * 
                 * Apply filters
                 */
                $value = apply_filters( 'wpcf_conditional_display_compare_meta_value',
                        $value, $c->cf['id'], $condition['operation'], $c->slug,
                        $post );
                $condition['value'] = apply_filters( 'wpcf_conditional_display_compare_condition_value',
                        $condition['value'], $c->cf['id'],
                        $condition['operation'], $c->slug, $post );

                /*
                 * 
                 * 
                 * Call built-in Types compare func
                 */
                $passed = wpcf_cd_admin_compare( $condition['operation'],
                        $value, $condition['value'] );

                if ( !$passed ) {
                    $passed_all = false;
                } else {
                    $passed_one = true;
                }
            }

            /*
             * 
             * 
             * Check OR/AND relation
             */
            if ( $count > 1 ) {
                if ( !$passed_all && $field['data']['conditional_display']['relation'] == 'AND' ) {
                    $passed = false;
                }
                if ( !$passed_one && $field['data']['conditional_display']['relation'] == 'OR' ) {
                    $passed = false;
                }
            }
        }

        return (bool) $passed;
    }

}