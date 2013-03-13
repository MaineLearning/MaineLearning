<?php

/**
 * All AJAX calls go here.
 */
function wpcf_ajax_embedded() {
    if ( !isset( $_REQUEST['_wpnonce'] )
            || !wp_verify_nonce( $_REQUEST['_wpnonce'], $_REQUEST['wpcf_action'] ) ) {
        die( 'Verification failed' );
    }

    global $wpcf;

    switch ( $_REQUEST['wpcf_action'] ) {

        case 'editor_insert_date':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/date.php';
            wpcf_fields_date_editor_form();
            break;

        case 'insert_skype_button':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/skype.php';
            wpcf_fields_skype_meta_box_ajax();
            break;

        case 'editor_callback':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            $field = wpcf_admin_fields_get_field( $_GET['field_id'] );
            if ( !empty( $field ) ) {
                $function = 'wpcf_fields_' . $field['type'] . '_editor_callback';
                if ( function_exists( $function ) ) {
                    call_user_func( $function );
                }
            }
            break;

        case 'dismiss_message':
            if ( isset( $_GET['id'] ) ) {
                $messages = get_option( 'wpcf_dismissed_messages', array() );
                $messages[] = $_GET['id'];
                update_option( 'wpcf_dismissed_messages', $messages );
            }
            break;

        case 'pr_add_child_post':
            $output = 'Passed wrong parameters';
            if ( isset( $_GET['post_id'] )
                    && isset( $_GET['post_type_child'] )
                    && isset( $_GET['post_type_parent'] ) ) {
                $relationships = get_option( 'wpcf_post_relationship', array() );
                $post = get_post( intval( $_GET['post_id'] ) );
                if ( !empty( $post->ID ) ) {
                    $post_type = strval( $_GET['post_type_child'] );
                    $parent_post_type = strval( $_GET['post_type_parent'] );
                    $data = $relationships[$parent_post_type][$post_type];
                    /*
                     * Since Types 1.1.5
                     * 
                     * We save new post
                     * CHECKPOINT
                     */
                    $id = $wpcf->relationship->add_new_child( $post->ID,
                            $post_type );

                    if ( !is_wp_error( $id ) ) {
                        /*
                         * Here we set Relationship
                         * CHECKPOINT
                         */
                        $parent = get_post( intval( $_GET['post_id'] ) );
                        $child = get_post( $id );
                        if ( !empty( $parent->ID ) && !empty( $child->ID ) ) {

                            // Set post
                            $wpcf->post = $child;

                            // Set relationship :)
                            $wpcf->relationship->_set( $parent, $child, $data );

                            // Render new row
                            $output = $wpcf->relationship->child_row( $post->ID,
                                    $id, $data );
                        } else {
                            $output = __( 'Error creating post relationship',
                                    'wpcf' );
                        }
                    } else {
                        $output = $id->get_error_message();
                    }
                } else {
                    $output = __( 'Error getting parent post', 'wpcf' );
                }
            }
            echo json_encode( array(
                'output' => $output . wpcf_form_render_js_validation( '#post',
                        false ),
            ) );
            break;

        case 'pr_save_all':
            $output = '';
            if ( isset( $_POST['post_id'] ) ) {
                $parent_id = intval( $_POST['post_id'] );
                if ( isset( $_POST['wpcf_post_relationship'][$parent_id] ) ) {
                    $wpcf->relationship->save_children( $parent_id,
                            (array) $_POST['wpcf_post_relationship'][$parent_id] );
                    $output = $wpcf->relationship->child_meta_form(
                            $parent_id, strval( $_POST['post_type'] )
                    );
                }
            }
            // TODO Move to conditional
            $output .= '<script type="text/javascript">wpcfConditionalInit();</script>';
            echo json_encode( array(
                'output' => $output,
            ) );
            break;

        case 'pr_save_child_post':
            ob_start(); // Try to catch any errors
            $output = '';
            if ( isset( $_GET['post_id'] )
                    && isset( $_GET['parent_id'] )
                    && isset( $_GET['post_type_parent'] )
                    && isset( $_GET['post_type_child'] )
                    && isset( $_POST['wpcf_post_relationship'] ) ) {
                $parent_id = intval( $_GET['parent_id'] );
                $child_id = intval( $_GET['post_id'] );
                $parent_post_type = strval( $_GET['post_type_parent'] );
                $child_post_type = strval( $_GET['post_type_child'] );
                if ( isset( $_POST['wpcf_post_relationship'][$parent_id][$child_id] ) ) {
                    $fields = (array) $_POST['wpcf_post_relationship'][$parent_id][$child_id];
                    $wpcf->relationship->save_child( $parent_id, $child_id,
                            $fields );
                    $output = $wpcf->relationship->child_row( $parent_id,
                            $child_id,
                            $wpcf->relationship->settings( $parent_post_type,
                                    $child_post_type ) );
                    // TODO Move to conditional
                    $output .= '<script type="text/javascript">wpcfConditionalInit(\'#types-child-row-'
                            . $child_id . '\');</script>';
                }
            }
            $errors = ob_get_clean();
            echo json_encode( array(
                'output' => $output,
                'errors' => $errors
            ) );
            break;

        case 'pr_delete_child_post':
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if ( isset( $_GET['post_id'] ) ) {
                $output = wpcf_pr_admin_delete_child_item( intval( $_GET['post_id'] ) );
            }
            echo json_encode( array(
                'output' => $output,
            ) );
            break;

        case 'pr-update-belongs':
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if ( isset( $_POST['post_id'] ) && isset( $_POST['wpcf_pr_belongs'][$_POST['post_id']] ) ) {
                $post_id = intval( $_POST['post_id'] );
                $output = wpcf_pr_admin_update_belongs( $post_id,
                        $_POST['wpcf_pr_belongs'][$post_id] );
            }
            echo json_encode( array(
                'output' => $output,
            ) );
            break;

        case 'pr_pagination':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
            require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
            $output = 'Passed wrong parameters';
            if ( isset( $_GET['post_id'] ) && isset( $_GET['post_type'] ) ) {
                global $wpcf;
                $parent = get_post( esc_attr( $_GET['post_id'] ) );
                $child_post_type = esc_attr( $_GET['post_type'] );

                if ( !empty( $parent->ID ) ) {

                    // Set post in loop
                    $wpcf->post = $parent;

                    // Save items_per_page
                    $wpcf->relationship->save_items_per_page(
                            $parent->post_type, $child_post_type,
                            intval( $_GET[$wpcf->relationship->items_per_page_option_name] )
                    );

                    $output = $wpcf->relationship->child_meta_form(
                            $parent->ID, $child_post_type
                    );
                }
            }
            echo json_encode( array(
                'output' => $output,
            ) );
            break;

        case 'pr_sort':
            $output = 'Passed wrong parameters';
            if ( isset( $_GET['field'] ) && isset( $_GET['sort'] ) && isset( $_GET['post_id'] ) && isset( $_GET['post_type'] ) ) {
                $output = $wpcf->relationship->child_meta_form(
                        intval( $_GET['post_id'] ), strval( $_GET['post_type'] )
                );
            }
            echo json_encode( array(
                'output' => $output,
            ) );
            break;

        case 'pr_sort_parent':
            $output = 'Passed wrong parameters';
            if ( isset( $_GET['field'] ) && isset( $_GET['sort'] ) && isset( $_GET['post_id'] ) && isset( $_GET['post_type'] ) ) {
                $output = $wpcf->relationship->child_meta_form(
                        intval( $_GET['post_id'] ), strval( $_GET['post_type'] )
                );
            }
            echo json_encode( array(
                'output' => $output,
            ) );
            break;

        case 'repetitive_add':
            if ( isset( $_GET['field_id'] ) ) {
                require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
                require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
                $field = wpcf_admin_fields_get_field( $_GET['field_id'] );
                $post_id = intval( $_GET['post_id'] );
                $post = get_post( $post_id );

                global $wpcf;
                $wpcf->repeater->set( $post, $field );
                /*
                 * 
                 * Force empty values!
                 */
                $wpcf->repeater->cf['value'] = null;
                $wpcf->repeater->meta = null;
                $form = $wpcf->repeater->get_field_form( null, true );

                echo json_encode( array(
                    'output' => wpcf_form_simple( $form )
                    . wpcf_form_render_js_validation( '#post', false ),
                ) );
            } else {
                echo json_encode( array(
                    'output' => 'params missing',
                ) );
            }
            break;

        case 'repetitive_delete':
            if ( isset( $_POST['post_id'] ) && isset( $_POST['field_id'] ) ) {
                require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
                $post = get_post( $_POST['post_id'] );
                $field = wpcf_admin_fields_get_field( $_POST['field_id'] );
                $meta_id = $_POST['meta_id'];
                if ( !empty( $field ) && !empty( $post->ID ) && !empty( $meta_id ) ) {
                    /*
                     * 
                     * 
                     * Changed.
                     * Since Types 1.2
                     */
                    global $wpcf;
                    $wpcf->repeater->set( $post, $field );
                    $wpcf->repeater->delete( $meta_id );

                    echo json_encode( array(
                        'output' => 'deleted',
                    ) );
                } else {
                    echo json_encode( array(
                        'output' => 'field or post not found',
                    ) );
                }
            } else {
                echo json_encode( array(
                    'output' => 'params missing',
                ) );
            }
            break;

        case 'cd_verify':

            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/conditional-display.php';

            global $wpcf;

            $post = null;
            $fields = array();
            $passed_fields = array();
            $failed_fields = array();
            $_flag_relationship = false;

            /*
             * We're accepting main form and others too
             * (like 'wpcf_post_relationship')
             * $fields = apply_filters( 'conditional_submitted_data', $data );
             */
            if ( !empty( $_POST['wpcf'] ) ) {
                $fields = apply_filters( 'types_ajax_conditional_post',
                        $_POST['wpcf'] );
            }

            // TODO Move this to conditional and use hooks
            if ( empty( $fields ) && empty( $_POST['wpcf_post_relationship'] ) ) {
                die();
            } else if ( empty( $fields ) && !empty( $_POST['wpcf_post_relationship'] ) ) {
                /*
                 * 
                 * 
                 * Relationship case
                 * TODO Move to relationship or elsewhere.
                 */
                $_temp = $_POST['wpcf_post_relationship'];
                $parent_id = key( $_temp );
                $_data = array_shift( $_temp );
                $post_id = key( $_data );
                $post = get_post( $post_id );
                $fields = $_data[$post_id];

                /*
                 * TODO This is temporary fix. Find better way to get fields
                 * rendered in child form
                 */
                $_all_fields = wpcf_admin_fields_get_fields();
                foreach ( $_all_fields as $_field ) {
                    if ( !isset( $fields[$_field['slug']] ) ) {
                        $fields[$_field['slug']] = null;
                    }
                }

                $_flag_relationship = true;

                /*
                 * 
                 * 
                 * 
                 * 
                 * Regular submission
                 * TODO Make better?
                 */
            } else {
                if ( isset( $_POST['wpcf_main_post_id'] ) ) {
                    $_POST['post_ID'] = intval( $_POST['wpcf_main_post_id'] );
                    $post = get_post( $_POST['post_ID'] );
                } else if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                    $split = explode( '?', $_SERVER['HTTP_REFERER'] );
                    if ( isset( $split[1] ) ) {
                        parse_str( $split[1], $vars );
                        if ( isset( $vars['post'] ) ) {
                            $_POST['post_ID'] = $vars['post'];
                            $post = get_post( $vars['post'] );
                        }
                    }
                }
                /*
                 * 
                 * Get fields by post and group.
                 */
//                $group_id = isset( $_POST['wpcf_group'] ) ? $_POST['wpcf_group'] : false;
//                if ( $group_id ) {
//                    $group = wpcf_admin_fields_get_group( $group_id );
//                    if ( !empty( $group ) ) {
//                        $_fields = wpcf_admin_fields_get_fields_by_group( $group['id'] );
//                        /*
//                         * Set missing fields to null (checkboxes and radios)
//                         */
//                        foreach ( $_fields as $_field ) {
//                            if ( !isset( $fields[$_field['slug']] ) ) {
//                                $fields[$_field['slug']] = null;
//                            }
//                        }
//                    }
//                }
                // We need all fields
                $_all_fields = wpcf_admin_fields_get_fields();
                foreach ( $_all_fields as $_field ) {
                    if ( !isset( $fields[$_field['slug']] ) ) {
                        $fields[$_field['slug']] = null;
                    }
                }
            }

            // Dummy post
            if ( empty( $post->ID ) ) {
                $post = new stdClass();
                $post->ID = 1;
            }

            foreach ( $fields as $field_id => $field_value ) {

                // Set conditional
                $wpcf->conditional->set( $post, $field_id );


                if ( !empty( $wpcf->conditional->cf['data']['conditional_display']['conditions'] ) ) {

                    if ( $_flag_relationship ) {
                        /*
                         * We need parent and child
                         */
                        $_relationship_name = false;

                        // Set name
                        $parent = get_post( $parent_id );
                        if ( !empty( $parent->ID ) ) {
                            $wpcf->relationship->set( $parent, $post );
                            $wpcf->relationship->cf->set( $post, $field_id );
                            $_child = $wpcf->relationship->get_child();
                            $_child->form->cf->set( $post, $field_id );
                            $_relationship_name = $_child->form->alter_form_name( 'wpcf[' . $wpcf->conditional->cf['id'] );
                        }

                        if ( !$_relationship_name ) {
                            continue;
                        }

                        add_filter( 'types_field_get_submitted_data',
                                'wpcf_relationship_ajax_data_filter', 10, 2 );

                        $name = $_relationship_name;
                    } else {
                        $name = 'wpcf[' . $wpcf->conditional->cf['id'];
                    }

                    /*
                     * Since Types 1.2
                     * Moved to WPCF_Conditional class.
                     */
                    // Evaluate
                    $passed = $wpcf->conditional->evaluate();

                    if ( $passed ) {
                        $passed_fields[] = $name;
                    } else {
                        $failed_fields[] = $name;
                    }
                }
            }

            /*
             * 
             * 
             * Render JS
             */
            if ( !empty( $passed_fields ) || !empty( $failed_fields ) ) {
                $execute = '';
                foreach ( $passed_fields as $field_name ) {
                    $execute .= $wpcf->conditional->render_js_show( $field_name );
                }
                foreach ( $failed_fields as $field_name ) {
                    $execute .= $wpcf->conditional->render_js_hide( $field_name );
                }
                echo json_encode( array(
                    'output' => '',
                    'execute' => $execute,
                    'wpcf_nonce_ajax_callback' => wp_create_nonce( 'execute' ),
                ) );
            }
            die();
            break;

        case 'cd_group_verify':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/conditional-display.php';
            $group = wpcf_admin_fields_get_group( $_POST['group_id'] );
            if ( empty( $group ) ) {
                echo json_encode( array(
                    'output' => ''
                ) );
                die();
            }
            $execute = '';
            $group['conditional_display'] = get_post_meta( $group['id'],
                    '_wpcf_conditional_display', true );
            // Filter meta values (switch them with $_POST values)
            add_filter( 'get_post_metadata',
                    'wpcf_cd_meta_ajax_validation_filter', 10, 4 );
            $post = false;
            if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                $split = explode( '?', $_SERVER['HTTP_REFERER'] );
                if ( isset( $split[1] ) ) {
                    parse_str( $split[1], $vars );
                    if ( isset( $vars['post'] ) ) {
                        $_POST['post_ID'] = $vars['post'];
                        $post = get_post( $vars['post'] );
                    }
                }
            }
            // Dummy post
            if ( !$post ) {
                $post = new stdClass();
                $post->ID = 1;
            }
            if ( !empty( $group['conditional_display']['conditions'] ) ) {
                $result = wpcf_cd_post_groups_filter( array(0 => $group), $post,
                        'group' );
                if ( !empty( $result ) ) {
                    $result = array_shift( $result );
                    $passed = $result['_conditional_display'] == 'passed' ? true : false;
                } else {
                    $passed = false;
                }
                if ( !$passed ) {
                    $execute = 'jQuery("#' . $group['slug']
                            . '").slideUp().find(".wpcf-cd-group")'
                            . '.addClass(\'wpcf-cd-group-failed\')'
                            . '.removeClass(\'wpcf-cd-group-passed\').hide();';
                } else {
                    $execute = 'jQuery("#' . $group['slug']
                            . '").show().find(".wpcf-cd-group")'
                            . '.addClass(\'wpcf-cd-group-passed\')'
                            . '.removeClass(\'wpcf-cd-group-failed\').slideDown();';
                }
            }
            // Remove filter meta values (switch them with $_POST values)
            remove_filter( 'get_post_metadata',
                    'wpcf_cd_meta_ajax_validation_filter', 10, 4 );
            echo json_encode( array(
                'output' => '',
                'execute' => $execute,
                'wpcf_nonce_ajax_callback' => wp_create_nonce( 'execute' ),
            ) );
            break;

        case 'pr_verify':
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/conditional-display.php';
            $passed_fields = array();
            $failed_fields = array();
            $post = false;
            if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
                $split = explode( '?', $_SERVER['HTTP_REFERER'] );
                if ( isset( $split[1] ) ) {
                    parse_str( $split[1], $vars );
                    if ( isset( $vars['post'] ) ) {
                        $_POST['post_ID'] = $vars['post'];
                        $post = get_post( $vars['post'] );
                    }
                }
            }
            // Dummy post
            if ( !$post ) {
                $post = new stdClass();
                $post->ID = 1;
            }
            // Filter meta values (switch them with $_POST values)
            add_filter( 'get_post_metadata',
                    'wpcf_cd_pr_meta_ajax_validation_filter', 10, 4 );
//            add_filter( 'types_field_get_submitted_data',
//                    'wpcf_cd_pr_meta_ajax_validation_filter', 10, 4 );

            if ( isset( $_POST['wpcf_post_relationship'] ) ) {
                $child_post_id = key( $_POST['wpcf_post_relationship'] );
                $data = $_POST['wpcf_post_relationship'] = array_shift( $_POST['wpcf_post_relationship'] );
                foreach ( $data as $field_id => $field_value ) {
                    $element = array();
                    $field = wpcf_admin_fields_get_field( str_replace( WPCF_META_PREFIX,
                                    '', $field_id ) );
                    if ( !empty( $field['data']['conditional_display']['conditions'] ) ) {
                        $element = wpcf_cd_post_edit_field_filter( $element,
                                $field, $post, 'group' );
                        if ( isset( $element['__wpcf_cd_status'] ) && $element['__wpcf_cd_status'] == 'passed' ) {
                            $passed_fields[] = 'wpcf_post_relationship_'
                                    . $child_post_id . '_' . $field['id'];
                        } else {
                            $failed_fields[] = 'wpcf_post_relationship_'
                                    . $child_post_id . '_' . $field['id'];
                        }
                    }
                }
            }

            // Remove filter meta values (switch them with $_POST values)
            remove_filter( 'get_post_metadata',
                    'wpcf_cd_pr_meta_ajax_validation_filter', 10, 4 );
//            remove_filter( 'types_field_get_submitted_data',
//                    'wpcf_cd_pr_meta_ajax_validation_filter', 10, 4 );

            if ( !empty( $passed_fields ) || !empty( $failed_fields ) ) {
                $execute = '';
                foreach ( $passed_fields as $field_name ) {
                    $execute .= 'jQuery(\'#' . $field_name . '\').parents(\'.wpcf-cd\').show().removeClass(\'wpcf-cd-failed\').addClass(\'wpcf-cd-passed\');' . " ";
                }
                foreach ( $failed_fields as $field_name ) {
                    $execute .= 'jQuery(\'#' . $field_name . '\').parents(\'.wpcf-cd\').hide().addClass(\'wpcf-cd-failed\').removeClass(\'wpcf-cd-passed\');' . " ";
                }
                echo json_encode( array(
                    'output' => '',
                    'execute' => $execute,
                    'wpcf_nonce_ajax_callback' => wp_create_nonce( 'execute' ),
                ) );
            }
            die();
            break;

        default:
            break;
    }
    if ( function_exists( 'wpcf_ajax' ) ) {
        wpcf_ajax();
    }
    die();
}