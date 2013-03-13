<?php
//add_action('wpcf_relationship_save_child', 'wpcf_fields_checkbox_save_check',
//        10, 3);

add_action( 'save_post', 'wpcf_fields_checkbox_save_check', 10, 3 );

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_checkbox() {
    return array(
        'id' => 'wpcf-checkbox',
        'title' => __( 'Checkbox', 'wpcf' ),
        'description' => __( 'Checkbox', 'wpcf' ),
        'validate' => array('required'),
        'meta_key_type' => 'BINARY',
    );
}

/**
 * Form data for post edit page.
 * 
 * @param type $field 
 */
function wpcf_fields_checkbox_meta_box_form( $field, $field_object ) {
    global $wpcf;
    $checked = false;
    $field['data']['set_value'] = stripslashes( $field['data']['set_value'] );
    if ( $field['value'] == $field['data']['set_value'] ) {
        $checked = true;
    }
    // If post is new check if it's checked by default
    global $pagenow;
    if ( $pagenow == 'post-new.php' && !empty( $field['data']['checked'] ) ) {
        $checked = true;
    }
    return array(
        '#type' => 'checkbox',
        '#value' => $field['data']['set_value'],
        '#default_value' => $checked,
        '#after' => '<input type="hidden" name="_wpcf_check_checkbox['
        . $field_object->post->ID . '][' . $field_object->slug
        . ']" value="1" />',
    );
}

/**
 * Editor callback form.
 */
function wpcf_fields_checkbox_editor_callback() {

    wp_enqueue_script( 'jquery' );

    $form = array();
    $value_not_selected = '';
    $value_selected = '';
    if ( isset( $_GET['field_id'] ) ) {
        $field = wpcf_admin_fields_get_field( $_GET['field_id'] );
        if ( !empty( $field ) ) {
            if ( isset( $field['data']['display_value_not_selected'] ) ) {
                $value_not_selected = $field['data']['display_value_not_selected'];
            }
            if ( isset( $field['data']['display_value_selected'] ) ) {
                $value_selected = $field['data']['display_value_selected'];
            }
        }
    }
    $form['#form']['callback'] = 'wpcf_fields_checkbox_editor_submit';
    $form['display'] = array(
        '#type' => 'radios',
        '#default_value' => 'db',
        '#name' => 'display',
        '#options' => array(
            'display_from_db' => array(
                '#title' => __( 'Display the value of this field from the database',
                        'wpcf' ),
                '#name' => 'display',
                '#value' => 'db',
                '#inline' => true,
                '#after' => '<br />'
            ),
            'display_values' => array(
                '#title' => __( 'Enter values for \'selected\' and \'not selected\' states',
                        'wpcf' ),
                '#name' => 'display',
                '#value' => 'value',
                '#inline' => true,
            ),
        ),
        '#inline' => true,
    );

    $form['states-start'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="wpcf-checkbox-states" style="display:none;margin-left:20px">',
    );
    $form['display-value-1'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Not selected:', 'wpcf' ),
        '#name' => 'display_value_not_selected',
        '#value' => $value_not_selected,
        '#inline' => true,
        '#pattern' => '
            <table>
            <tr>
            <td style="text-align:right;"><TITLE></td>
            <td><ELEMENT></td>
            </tr>',
    );
    $form['display-value-2'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Selected:', 'wpcf' ),
        '#name' => 'display_value_selected',
        '#value' => $value_selected,
        '#inline' => true,
        '#pattern' => '
            <tr>
            <td style="text-align:right;"><TITLE></td>
            <td><ELEMENT></td>
            </tr>
            </table>',
    );
    $form['states-end'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );

    $form = wpcf_form_popup_add_optional( $form );

    $help = array('url' => "http://wp-types.com/documentation/functions/checkbox/",
        'text' => __( 'Checkbox help', 'wpcf' ));

    $form = wpcf_form_popup_helper( $form, __( 'Insert', 'wpcf' ),
            __( 'Cancel', 'wpcf' ), $help );

    $f = wpcf_form( 'wpcf-form', $form );
    add_action( 'admin_head_wpcf_ajax', 'wpcf_fields_checkbox_form_script' );
    wpcf_admin_ajax_head( 'Insert checkbox', 'wpcf' );
    echo '<form method="post" action="">';
    echo $f->renderForm();
    echo '</form>';
    wpcf_admin_ajax_footer();
}

/**
 * AJAX window JS.
 */
function wpcf_fields_checkbox_form_script() {

    ?>
    <script type="text/javascript">
        // <![CDATA[
        jQuery(document).ready(function(){
            jQuery('input[name="display"]').change(function(){
                if (jQuery(this).val() == 'value') {
                    jQuery('#wpcf-checkbox-states').slideDown();
                } else {
                    jQuery('#wpcf-checkbox-states').slideUp();
                }
            });
        });
        // ]]>
    </script>

    <?php

}

/**
 * Editor callback form submit.
 */
function wpcf_fields_checkbox_editor_submit() {
    $add = '';
    $field = wpcf_admin_fields_get_field( $_GET['field_id'] );
    if ( !empty( $field ) ) {
        if ( $_POST['display'] == 'value' ) {
            $shortcode = '[types field="' . $field['slug'] . '" state="checked"]'
                    . $_POST['display_value_selected']
                    . '[/types] ';
            $shortcode .= '[types field="' . $field['slug'] . '" state="unchecked"]'
                    . $_POST['display_value_not_selected']
                    . '[/types]';
        } else {
            $shortcode = wpcf_fields_get_shortcode( $field, $add );
        }

        $shortcode = wpcf_fields_add_optionals_to_shortcode( $shortcode );
        echo editor_admin_popup_insert_shortcode_js( $shortcode );
        die();
    }
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_checkbox_view( $params ) {
    $output = '';
    if ( isset( $params['state'] )
            && $params['state'] == 'unchecked'
            && empty( $params['field_value'] ) ) {
        if ( empty( $params['#content'] ) ) {
            return '__wpcf_skip_empty';
        }
        return htmlspecialchars_decode( $params['#content'] );
    } else if ( isset( $params['state'] ) && $params['state'] == 'unchecked' ) {
        return '__wpcf_skip_empty';
    }

    if ( isset( $params['state'] ) && $params['state'] == 'checked' && !empty( $params['field_value'] ) ) {
        if ( empty( $params['#content'] ) ) {
            return '__wpcf_skip_empty';
        }
        return htmlspecialchars_decode( $params['#content'] );
    } else if ( isset( $params['state'] ) && $params['state'] == 'checked' ) {
        return '__wpcf_skip_empty';
    }
    if ( !empty( $params['#content'] )
            && !empty( $params['field_value'] ) ) {
        return htmlspecialchars_decode( $params['#content'] );
    }

    if ( $params['field']['data']['display'] == 'db' && $params['field_value'] != '' ) {
        $field = wpcf_fields_get_field_by_slug( $params['field']['slug'] );
        $output = $field['data']['set_value'];

        // Show the translated value if we have one.
        $output = wpcf_translate( 'field ' . $field['id'] . ' checkbox value',
                $output );
    } else if ( $params['field']['data']['display'] == 'value'
            && $params['field_value'] != '' ) {
        if ( !empty( $params['field']['data']['display_value_selected'] ) ) {
            $output = $params['field']['data']['display_value_selected'];
            $output = wpcf_translate( 'field ' . $params['field']['id'] . ' checkbox value selected',
                    $output );
        }
    } else if ( $params['field']['data']['display'] == 'value' ) {
        if ( !empty( $params['field']['data']['display_value_not_selected'] ) ) {
            $output = $params['field']['data']['display_value_not_selected'];
            $output = wpcf_translate( 'field ' . $params['field']['id'] . ' checkbox value not selected',
                    $output );
        }
    }

    return $output;
}

/**
 * Check if checkbox is submitted.
 * 
 * Currently used on Relationship saving. May be expanded to general code.
 * 
 * @param type $value
 * @param type $field
 * @param type $cf
 */
function wpcf_fields_checkbox_save_check() {
    $meta_to_unset = array();

    /*
     * 
     * We hve several calls on this:
     * 1. Saving post with Update
     * 2. Saving all children
     * 3. Saving child
     */

    $mode = 'save_main';
    if ( defined( 'DOING_AJAX' ) ) {
        if ( isset( $_GET['wpcf_action'] )
                && $_GET['wpcf_action'] == 'pr_save_all' ) {
            $mode = 'save_all';
        } else if ( isset( $_GET['wpcf_action'] )
                && $_GET['wpcf_action'] == 'pr_save_child_post' ) {
            $mode = 'save_child';
        }
    }

    // See if any marked for checking
    if ( isset( $_POST['_wpcf_check_checkbox'] ) ) {

        // Loop and search in $_POST
        foreach ( $_POST['_wpcf_check_checkbox'] as $child_id => $slugs ) {
            foreach ( $slugs as $slug => $true ) {
                $cf = new WPCF_Field();
                $cf->set( $child_id, $cf->__get_slug_no_prefix( $slug ) );

                // First check main post
                if ( $mode == 'save_main'
                        && intval( $child_id ) == wpcf_get_post_id() ) {
                    if ( !isset( $_POST['wpcf'][$cf->cf['slug']] ) ) {
                        $meta_to_unset[intval( $child_id )][$cf->slug] = true;
                    }
                    continue;
                }
                /*
                 * 
                 * Relationship check
                 */
                if ( !isset( $_POST['wpcf_post_relationship'] ) ) {
                    $meta_to_unset[$child_id][$cf->slug] = true;
                } else {
                    foreach ( $_POST['wpcf_post_relationship'] as $_parent =>
                                $_children ) {
                        foreach ( $_children as $_child_id => $_slugs ) {
                            if ( !isset( $_slugs[$slug] ) ) {
                                $meta_to_unset[$_child_id][$cf->slug] = true;
                            }
                        }
                    }
                }
            }
        }
    }

    // After collected - delete them
    foreach ( $meta_to_unset as $child_id => $slugs ) {
        foreach ( $slugs as $slug => $true ) {
            if ( $cf->cf['data']['save_empty'] != 'no' ) {
                update_post_meta( $child_id, $slug, 0 );
            } else {
                delete_post_meta( $child_id, $slug );
            }
        }
    }
}