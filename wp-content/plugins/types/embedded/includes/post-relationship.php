<?php
/*
 * Post relationship code.
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/editor-support/post-relationship-editor-support.php';

add_action( 'wpcf_admin_post_init', 'wpcf_pr_admin_post_init_action', 10, 4 );
add_action( 'save_post', 'wpcf_pr_admin_save_post_hook', 11, 2 ); // Trigger afer main hook
add_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10, 4 );

/**
 * Init function.
 * 
 * Enqueues styles and scripts on post edit page.
 * 
 * @param type $post_type
 * @param type $post
 * @param type $groups
 * @param type $wpcf_active 
 */
function wpcf_pr_admin_post_init_action( $post_type, $post, $groups,
        $wpcf_active ) {

    // See if any data
    $has = wpcf_pr_admin_get_has( $post_type );
    $belongs = wpcf_pr_admin_get_belongs( $post_type );

    /*
     * 
     * Enqueue styles and scripts
     */
    if ( !empty( $has ) || !empty( $belongs ) ) {
        add_action( 'admin_head', 'wpcf_pr_add_field_js' );
        $output = wpcf_pr_admin_post_meta_box_output( $post,
                array('post_type' => $post_type, 'has' => $has, 'belongs' => $belongs) );
        add_meta_box( 'wpcf-post-relationship', __( 'Fields table', 'wpcf' ),
                'wpcf_pr_admin_post_meta_box', $post_type, 'normal', 'default',
                array('output' => $output) );
        if ( !empty( $output ) ) {
            wp_enqueue_script( 'wpcf-post-relationship',
                    WPCF_EMBEDDED_RELPATH . '/resources/js/post-relationship.js',
                    array('jquery'), WPCF_VERSION );
            wp_enqueue_style( 'wpcf-post-relationship',
                    WPCF_EMBEDDED_RELPATH . '/resources/css/post-relationship.css',
                    array(), WPCF_VERSION );
            if ( !$wpcf_active ) {
                wpcf_enqueue_scripts();
                wp_enqueue_style( 'wpcf-pr-post',
                        WPCF_EMBEDDED_RES_RELPATH . '/css/fields-post.css',
                        array(), WPCF_VERSION );
                wp_enqueue_script( 'wpcf-form-validation',
                        WPCF_EMBEDDED_RES_RELPATH . '/js/'
                        . 'jquery-form-validation/jquery.validate.min.js',
                        array('jquery'), WPCF_VERSION );
                wp_enqueue_script( 'wpcf-form-validation-additional',
                        WPCF_EMBEDDED_RES_RELPATH . '/js/'
                        . 'jquery-form-validation/additional-methods.min.js',
                        array('jquery'), WPCF_VERSION );
            }
            wpcf_admin_add_js_settings( 'wpcf_pr_del_warning',
                    '\'' . __( 'Are you sure about deleting this post?', 'wpcf' ) . '\'' );
            wpcf_admin_add_js_settings( 'wpcf_pr_pagination_warning',
                    '\'' . __( 'If you continue without saving your changes, it might get lost.',
                            'wpcf' ) . '\'' );
        }
    }
}

/**
 * Gets post types that belong to current post type.
 * 
 * @param type $post_type
 * @return type 
 */
function wpcf_pr_admin_get_has( $post_type ) {
    if ( empty( $post_type ) ) {
        return false;
    }
    $relationships = get_option( 'wpcf_post_relationship', array() );
    if ( empty( $relationships[$post_type] ) ) {
        return false;
    }
    // See if enabled
    foreach ( $relationships[$post_type] as $temp_post_type =>
                $temp_post_type_data ) {
        $active = get_post_type_object( $temp_post_type );
        if ( !$active ) {
            unset( $relationships[$post_type][$temp_post_type] );
        }
    }
    return !empty( $relationships[$post_type] ) ? $relationships[$post_type] : false;
}

/**
 * Gets post types that current post type belongs to.
 * 
 * @param type $post_type
 * @return type 
 */
function wpcf_pr_admin_get_belongs( $post_type ) {
    if ( empty( $post_type ) ) {
        return false;
    }
    $relationships = get_option( 'wpcf_post_relationship', array() );
    $results = array();
    if ( is_array( $relationships ) ) {
        foreach ( $relationships as $has => $belongs ) {
            // See if enabled
            $active = get_post_type_object( $has );
            if ( !$active ) {
                continue;
            }
            if ( array_key_exists( $post_type, $belongs ) ) {
                $results[$has] = $belongs[$post_type];
            }
        }
    }
    return !empty( $results ) ? $results : false;
}

/**
 * Meta boxes contents.
 * 
 * @param type $post
 * @param type $args 
 */
function wpcf_pr_admin_post_meta_box( $post, $args ) {
    if ( !empty( $args['args']['output'] ) ) {
        echo $args['args']['output'];
    } else {
        _e( 'You will be able to add/edit child posts after saving the parent post.',
                'wpcf' );
    }
}

/**
 * Meta boxes contents output.
 * 
 * @param type $post
 * @param type $args 
 */
function wpcf_pr_admin_post_meta_box_output( $post, $args ) {

    if ( empty( $post->ID ) ) {
        return false;
    }

    global $wpcf;

    $output = '';
    $relationships = $args;
    $post_id = !empty( $post->ID ) ? $post->ID : -1;
    $current_post_type = wpcf_admin_get_post_type( $post );
    /*
     * 
     * 
     * 
     * Render has form (child form)
     */
    if ( !empty( $relationships['has'] ) ) {
        foreach ( $relationships['has'] as $post_type => $data ) {
            $output .= $wpcf->relationship->child_meta_form( $post, $post_type,
                    $data );
        }
    }
    /*
     * 
     * 
     * 
     * Render belongs form (parent form)
     */
    if ( !empty( $relationships['belongs'] ) ) {
        $meta = get_post_custom( $post_id );
        $belongs = array('belongs' => array(), 'posts' => array());
        foreach ( $meta as $meta_key => $meta_value ) {
            if ( strpos( $meta_key, '_wpcf_belongs_' ) === 0 ) {
                $temp_post = get_post( $meta_value[0] );
                if ( !empty( $temp_post ) ) {
                    $belongs['posts'][$temp_post->ID] = $temp_post;
                    $belongs['belongs'][$temp_post->post_type] = $temp_post->ID;
                }
            }
        }
        $output_temp = '';
        foreach ( $relationships['belongs'] as $post_type => $data ) {
            $output_temp .= wpcf_form_simple( wpcf_pr_admin_post_meta_box_belongs_form( $post,
                            $post_type, $belongs ) );
        }
        if ( !empty( $output_temp ) ) {
            $output .= '<div style="margin: 20px 0 10px 0">' . sprintf( __( 'This %s belongs to:',
                                    'wpcf' ), $current_post_type ) . '</div>' . $output_temp;
        }
    }
    return $output;
}

/**
 * Post relationship has form headers.
 * 
 * @todo since Types 1.2 it is moved to WPCF_Relationship_Child_Form class
 * @see WPCF_Relationship_Child_Form::headers()
 * 
 * @global type $wpcf_post_relationship_headers
 * @param type $post
 * @param type $post_type
 * @param type $parent_post_type
 * @param type $data
 * @return string 
 */
//function wpcf_pr_admin_post_meta_box_has_form_headers( $post, $post_type,
//        $parent_post_type, $data ) {
//    // Sorting
//    $dir = isset( $_GET['sort'] ) && $_GET['sort'] == 'ASC' ? 'DESC' : 'ASC';
//    $dir_default = 'ASC';
//    $sort_field = isset( $_GET['field'] ) ? $_GET['field'] : '';
//
//    $headers = array();
//    $wpcf_fields = wpcf_admin_fields_get_fields( true );
//    if ( empty( $data['fields_setting'] ) ) {
//        $data['fields_setting'] = 'all_cf';
//    }
//    if ( $data['fields_setting'] == 'specific' ) {
//        $keys = array_keys( $data['fields'] );
//        foreach ( $keys as $k => $header ) {
//            if ( $header == '_wpcf_pr_parents' ) {
//                continue;
//            }
//            if ( $header == '_wp_title' ) {
//                $title_dir = $sort_field == '_wp_title' ? $dir : 'ASC';
//                $headers[$header] = '';
//                $headers[$header] .= $sort_field == '_wp_title' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                                . '_wp_title&amp;sort=' . $title_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                                . $post_type . '&amp;_wpnonce='
//                                . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Title' ) . '</a>';
//            } else if ( $header == '_wp_body' ) {
//                $body_dir = $sort_field == '_wp_body' ? $dir : $dir_default;
//                $headers[$header] = '';
//                $headers[$header] .= $sort_field == '_wp_body' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                                . '_wp_body&amp;sort=' . $body_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                                . $post_type . '&amp;_wpnonce='
//                                . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Body' ) . '</a>';
//            } else if ( strpos( $header, WPCF_META_PREFIX ) === 0
//                    && isset( $wpcf_fields[str_replace( WPCF_META_PREFIX, '',
//                                    $header )] ) ) {
//                wpcf_admin_post_field_load_js_css( $post,
//                        wpcf_fields_type_action( $wpcf_fields[str_replace( WPCF_META_PREFIX,
//                                        '', $header )]['type'] ) );
//                $field_dir = $sort_field == $header ? $dir : $dir_default;
//                $headers[$header] = '';
//                $headers[$header] .= $sort_field == $header ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                                . $header . '&amp;sort=' . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                                . $post_type . '&amp;_wpnonce='
//                                . wp_create_nonce( 'pr_sort' ) ) . '">' . stripslashes( $wpcf_fields[str_replace( WPCF_META_PREFIX,
//                                        '', $header )]['name'] ) . '</a>';
//                if ( wpcf_admin_is_repetitive( $wpcf_fields[str_replace( WPCF_META_PREFIX,
//                                        '', $header )] ) ) {
//                    $repetitive_warning = true;
//                }
//            } else {
//                $field_dir = $sort_field == $header ? $dir : $dir_default;
//                $headers[$header] = '';
//                $headers[$header] .= $sort_field == $header ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                                . $header . '&amp;sort=' . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                                . $post_type . '&amp;_wpnonce='
//                                . wp_create_nonce( 'pr_sort' ) ) . '">'
//                        . stripslashes( $header ) . '</a>';
//            }
//        }
//        if ( !empty( $data['fields']['_wpcf_pr_parents'] ) ) {
//            foreach ( $data['fields']['_wpcf_pr_parents'] as $temp_parent =>
//                        $temp_data ) {
//                if ( $temp_parent == $parent_post_type ) {
//                    continue;
//                }
//                $temp_parent_type = get_post_type_object( $temp_parent );
//                if ( empty( $temp_parent_type ) ) {
//                    continue;
//                }
//                $parent_dir = $sort_field == '_wpcf_pr_parent' ? $dir : $dir_default;
//                $headers['_wpcf_pr_parent_' . $temp_parent] = $sort_field == '_wpcf_pr_parent' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//                $headers['_wpcf_pr_parent_' . $temp_parent] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                                . '_wpcf_pr_parent&amp;sort='
//                                . $parent_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                                . $post_type . '&amp;post_type_sort_parent='
//                                . $temp_parent . '&amp;_wpnonce='
//                                . wp_create_nonce( 'pr_sort' ) ) . '">' . $temp_parent_type->label . '</a>';
//            }
//        }
//    } else {
//        $item = new stdClass();
//        $item->ID = 'new_' . wpcf_unique_id( serialize( $post ) );
//        $item->post_title = '';
//        $item->post_content = '';
//        $item->post_type = $post_type;
//        $groups = wpcf_admin_post_get_post_groups_fields( $item,
//                'post_relationships_header' );
//        $title_dir = $sort_field == '_wp_title' ? $dir : $dir_default;
//        $headers['_wp_title'] = '';
//        $headers['_wp_title'] .= $sort_field == '_wp_title' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//        $headers['_wp_title'] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                        . '_wp_title&amp;sort=' . $title_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                        . $post_type . '&amp;_wpnonce='
//                        . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Title' ) . '</a>';
//        if ( $data['fields_setting'] == 'all_cf_standard' ) {
//            $body_dir = $sort_field == '_wp_body' ? $dir : $dir_default;
//            $headers['_wp_body'] = '';
//            $headers['_wp_body'] .= $sort_field == '_wp_body' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//            $headers['_wp_body'] = '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                            . '_wp_body&amp;sort=' . $body_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                            . $post_type . '&amp;_wpnonce='
//                            . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Body' ) . '</a>';
//        }
//        foreach ( $groups as $group ) {
//            foreach ( $group['fields'] as $field ) {
//                if ( wpcf_admin_is_repetitive( $field ) ) {
//                    $repetitive_warning = true;
//                }
//                $header_key = wpcf_types_get_meta_prefix( $field ) . $field['slug'];
//                wpcf_admin_post_field_load_js_css( $post,
//                        wpcf_fields_type_action( $field['type'] ) );
//                $field_dir = $sort_field == wpcf_types_get_meta_prefix( $field ) . $field['slug'] ? $dir : $dir_default;
//                $headers[$header_key] = '';
//                $headers[$header_key] .= $sort_field == wpcf_types_get_meta_prefix( $field ) . $field['slug'] ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
//                $headers[$header_key] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                                . wpcf_types_get_meta_prefix( $field ) . $field['slug'] . '&amp;sort='
//                                . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                                . $post_type . '&amp;_wpnonce='
//                                . wp_create_nonce( 'pr_sort' ) ) . '">'
//                        . stripslashes( $field['name'] ) . '</a>';
//            }
//        }
//        // Get all parents
//        $item_parents = wpcf_pr_admin_get_belongs( $post_type );
//        if ( $item_parents ) {
//            foreach ( $item_parents as $temp_parent => $temp_data ) {
//                if ( $temp_parent == $parent_post_type ) {
//                    continue;
//                }
//                $temp_parent_type = get_post_type_object( $temp_parent );
//                $parent_dir = $sort_field == '_wpcf_pr_parent' ? $dir : $dir_default;
//                $headers['_wpcf_pr_parent_' . $temp_parent] = '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
//                                . '_wpcf_pr_parent&amp;sort='
//                                . $parent_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
//                                . $post_type . '&amp;post_type_sort_parent='
//                                . $temp_parent . '&amp;_wpnonce='
//                                . wp_create_nonce( 'pr_sort' ) ) . '">' . $temp_parent_type->label . '</a>';
//            }
//        }
//    }
//    return $headers;
//}

/**
 * AJAX delete child item call.
 * 
 * @param type $post_id
 * @return string 
 */
function wpcf_pr_admin_delete_child_item( $post_id ) {
    wp_delete_post( $post_id, true );
    return __( 'Post deleted', 'wpcf' );
}

/**
 * Belongs form.
 * 
 * @param type $post
 * @param type $post_type
 * @param type $data
 * @param type $parent_post_type
 */
function wpcf_pr_admin_post_meta_box_belongs_form( $post, $type, $belongs ) {
    if ( empty( $post ) ) {
        return array();
    }
    $temp_type = get_post_type_object( $type );
    if ( empty( $temp_type ) ) {
        return array();
    }
    $form = array();
    $options = array(
        __( 'Not selected', 'wpcf' ) => 0,
    );
    $items = get_posts( 'post_type=' . $type . '&numberposts=-1&post_status=null&order=ASC&orderby=title&suppress_filters=0' );
    if ( empty( $items ) ) {
        return array();
    }
    foreach ( $items as $temp_post ) {
        if ( $temp_post->post_status == 'auto-draft' ) {
            continue;
        }
        $options[] = array(
            '#title' => $temp_post->post_title,
            '#value' => $temp_post->ID,
        );
    }
    $form[$type] = array(
        '#type' => 'select',
        '#name' => 'wpcf_pr_belongs[' . $post->ID . '][' . $type . ']',
        '#default_value' => isset( $belongs['belongs'][$type] ) ? $belongs['belongs'][$type] : 0,
        '#options' => $options,
        '#prefix' => $temp_type->label . '&nbsp;',
        '#suffix' => '&nbsp;<a href="'
        . admin_url( 'admin-ajax.php?action=wpcf_ajax'
                . '&amp;wpcf_action=pr-update-belongs&amp;_wpnonce='
                . wp_create_nonce( 'pr-update-belongs' )
                . '&amp;post_id=' . $post->ID )
        . '" class="button-secondary wpcf-pr-update-belongs">' . __( 'Update',
                'wpcf' ) . '</a>',
    );
    return $form;
}

/**
 * Updates belongs data.
 * 
 * @param type $post_id
 * @param array $data $post_type => $post_id
 * @return string 
 */
function wpcf_pr_admin_update_belongs( $post_id, $data ) {
    $post_type = key( $data );
    $post_owner_id = array_shift( $data );
    if ( !empty( $post_id ) && !empty( $post_type ) && !empty( $post_owner_id ) ) {
        update_post_meta( $post_id, '_wpcf_belongs_' . $post_type . '_id',
                $post_owner_id );
        return __( 'Post updated', 'wpcf' );
    } else if ( intval( $post_owner_id ) == 0 ) {
        delete_post_meta( $post_id, '_wpcf_belongs_' . $post_type . '_id' );
        return __( 'Post updated', 'wpcf' );
    }
    return __( 'Passed wrong parameters', 'wpcf' );
}

/**
 * Pagination link.
 * 
 * @param type $post
 * @param type $post_type
 * @param type $page
 * @param type $prev
 * @param type $next
 * @return string 
 */
function wpcf_pr_admin_has_pagination( $post, $post_type, $page, $prev, $next,
        $per_page = 20, $count = 20 ) {

    global $wpcf;

    $link = '';
    $add = '';
    if ( isset( $_GET['sort'] ) ) {
        $add .= '&sort=' . $_GET['sort'];
    }
    if ( isset( $_GET['field'] ) ) {
        $add .= '&field=' . $_GET['field'];
    }
    if ( isset( $_GET['post_type_sort_parent'] ) ) {
        $add .= '&post_type_sort_parent=' . $_GET['post_type_sort_parent'];
    }
    if ( $prev ) {
        $link .= '<a class="button-secondary wpcf-pr-pagination-link wpcf-pr-prev" href="'
                . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_pagination&amp;page='
                        . ($page - 1) . '&amp;dir=prev&amp;post_id=' . $post->ID . '&amp;post_type='
                        . $post_type
                        . '&amp;' . $wpcf->relationship->items_per_page_option_name
                        . '=' . $wpcf->relationship->items_per_page
                        . '&amp;_wpnonce='
                        . wp_create_nonce( 'pr_pagination' ) . $add ) . '">'
                . __( 'Prev', 'wpcf' ) . '</a>&nbsp;&nbsp;';
    }
    if ( $per_page < $count ) {
        $total_pages = ceil( $count / $per_page );
        $link .= '<select class="wpcf-pr-pagination-select" name="wpcf-pr-pagination-select">';
        for ( $index = 1; $index <= $total_pages; $index++ ) {
            $link .= '<option';
            if ( ($index) == $page ) {
                $link .= ' selected="selected"';
            }
            $link .= ' value="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_pagination&amp;page='
                            . $index . '&amp;dir=next&amp;post_id=' . $post->ID . '&amp;post_type='
                            . $post_type
                            . '&amp;' . $wpcf->relationship->items_per_page_option_name
                            . '=' . $wpcf->relationship->items_per_page
                            . '&amp;_wpnonce='
                            . wp_create_nonce( 'pr_pagination' ) . $add ) . '">' . $index . '</option>';
        }
        $link .= '</select>';
    }
    if ( $next ) {
        $link .= '<a class="button-secondary wpcf-pr-pagination-link wpcf-pr-next" href="'
                . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_pagination&amp;page='
                        . ($page + 1) . '&amp;dir=next&amp;post_id=' . $post->ID . '&amp;post_type='
                        . $post_type
                        . '&amp;' . $wpcf->relationship->items_per_page_option_name
                        . '=' . $wpcf->relationship->items_per_page
                        . '&amp;_wpnonce='
                        . wp_create_nonce( 'pr_pagination' ) . $add ) . '">'
                . __( 'Next', 'wpcf' ) . '</a>';
    }
    return !empty( $link ) ? '<div class="wpcf-pagination-top">' . $link . '</div>' : '';
}

/**
 * Save post hook.
 * 
 * @param type $parent_post_id
 * @return string 
 */
function wpcf_pr_admin_save_post_hook( $parent_post_id ) {

    global $wpcf;
    /*
     * 
     * TODO Monitor this
     */
    // Remove main hook?
    // CHECKPOINT We remove temporarily main hook
//    remove_action( 'save_post', 'wpcf_admin_save_post_hook', 10, 2 );
    // This should be done once per save
    remove_action( 'save_post', 'wpcf_pr_admin_save_post_hook', 11, 2 );

    if ( isset( $_POST['wpcf_post_relationship'][$parent_post_id] ) ) {
        $wpcf->relationship->save_children( $parent_post_id,
                (array) $_POST['wpcf_post_relationship'][$parent_post_id] );
    }
    // Save belongs if any
    if ( isset( $_POST['wpcf_pr_belongs'][intval( $parent_post_id )] ) ) {
        wpcf_pr_admin_update_belongs( intval( $parent_post_id ),
                $_POST['wpcf_pr_belongs'][intval( $parent_post_id )] );
    }

    // WPML
    wpcf_wpml_relationship_save_post_hook( $parent_post_id );

    // Restore main hook?
//    add_action( 'save_post', 'wpcf_admin_save_post_hook', 10, 2 );
}

/**
 * Returned translated '_wpcf_belongs_XXX_id' if any.
 * 
 * @global type $sitepress
 * @param type $value
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type 
 */
function wpcf_pr_meta_belongs_filter( $value, $object_id, $meta_key, $single ) {
    // WPML
    $value = wpcf_wpml_relationship_meta_belongs_filter( $value, $object_id,
            $meta_key, $single );
    return $value;
}

/**
 * JS for fields AJAX.
 */
function wpcf_pr_add_field_js() {

    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            wpcfPrVerifyInit();
        });
                                                                                                                                                                                                                                                
        function wpcfPrVerifyInit() {
            jQuery('.wpcf-pr-has-entries .wpcf-cd').each(function(){
                jQuery(this).parents('tr').find(':input').each(function(){
                    if (jQuery(this).hasClass('wpcf-pr-binded')) {
                        return false;
                    }
                    jQuery(this).addClass('wpcf-pr-binded');
                    if (jQuery(this).hasClass('radio')
                        || jQuery(this).hasClass('checkbox')) {
                        jQuery(this).bind('click', function(){
                            wpcfPrVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
                        });
                    } else if (jQuery(this).hasClass('select')) {
                        jQuery(this).bind('change', function(){
                            wpcfPrVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
                        });
                    } else if (jQuery(this).hasClass('wpcf-datepicker')) {
                        jQuery(this).bind('wpcfDateBlur', function(){
                            wpcfPrVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
                        });
                    } else {
                        jQuery(this).bind('blur', function(){
                            wpcfPrVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
                        });
                    }
                });
            });
        }
                                                                                                                                                                                                                                                                                                        
        function wpcfPrVerify(object, name, value) {
            var form = object.parents('tr').find(':input');
            jQuery.ajax({
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                type: 'post',
                dataType: 'json',
                data: form.serialize()+'<?php echo '&action=wpcf_ajax&wpcf_action=pr_verify&_wpnonce=' . wp_create_nonce( 'pr_verify' ); ?>',
                cache: false,
                beforeSend: function() {
                },
                success: function(data) {
                    if (data != null) {
                        if (typeof data.execute != 'undefined'
                            && (typeof data.wpcf_nonce_ajax_callback != 'undefined'
                            && data.wpcf_nonce_ajax_callback == wpcf_nonce_ajax_callback)) {
                            eval(data.execute);
                        }
                    }
                }
            });
        }
    </script>
    <?php
}

function wpcf_relationship_ajax_data_filter( $posted, $field ) {

    global $wpcf;

    $value = $wpcf->relationship->get_submitted_data(
            $wpcf->relationship->parent->ID, $wpcf->relationship->child->ID,
            $field );

    return is_null( $value ) ? $posted : $value;
}