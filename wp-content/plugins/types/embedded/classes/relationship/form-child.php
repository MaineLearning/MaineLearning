<?php
/*
 * Relationship form class.
 * 
 * Used to render child forms
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';

/**
 * Relationship form class.
 * 
 * Used on post edit page to show children rows
 */
class WPCF_Relationship_Child_Form
{

    /**
     * Current post.
     * 
     * @var type object
     */
    var $post;

    /**
     * Field object.
     * 
     * @var type array
     */
    var $cf = array();

    /**
     * Saved data.
     * 
     * @var type array
     */
    var $data = array();

    /**
     * Child post object.
     * 
     * @var type 
     */
    var $child_post_type_object;
    var $parent;
    var $parent_post_type;
    var $child_post_type;
    var $model;
    var $children;
    var $headers = array();

    /**
     * Construct function.
     */
    function __construct( $parent_post, $child_post_type, $data ) {
        $this->parent = $parent_post;
        $this->parent_post_type = $parent_post->post_type;
        $this->child_post_type = $child_post_type;
        $this->data = $data;
// Clean data
        if ( empty( $this->data['fields_setting'] ) ) {
            $this->data['fields_setting'] = 'all_cf';
        }
        $this->model = new WPCF_Relationship_Model();
        $this->cf = new WPCF_Field();
        $this->cf->context = 'relationship';
        $this->children = $this->model->get_children( $this->parent,
                $this->child_post_type, $this->data );
        $this->child_post_type_object = get_post_type_object( $this->child_post_type );
    }

    /**
     * Sets form.
     * 
     * @param type $o
     */
    function _set( $child ) {
        $this->child = $child;
    }

    /**
     * Returns HTML formatted form.
     * 
     * Renders children per row.
     * 
     * @todo move all here
     * 
     * @return type string (HTML formatted)
     */
    function render() {
        static $count = false;
        if ( !$count ) {
            $count = 1;
        }

        /*
         * Pagination will slice children
         */
        $this->pagination();
        $rows = $this->rows();
        $headers = $this->headers();

        // Capture template output
        ob_start();
        include WPCF_EMBEDDED_INC_ABSPATH . '/relationship/child-table.php';
        $table = ob_get_contents();
        ob_end_clean();

        $count++;
        return $table;
    }

    /**
     * Pagination
     */
    function pagination() {

        global $wpcf;

        // Pagination
        $total_items = count( $this->children );
        $per_page = $wpcf->relationship->get_items_per_page( $this->parent_post_type,
                $this->child_post_type );
        $page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 1;
        $numberposts = $page == 1 ? 1 : ($page - 1) * $per_page;
        $slice = $page == 1 ? 0 : ($page - 1) * $per_page;
        $next = count( $this->children ) > $numberposts + $per_page;
        $prev = $page == 1 ? false : true;
        if ( $total_items > $per_page ) {
            $this->children = array_splice( $this->children, $slice, $per_page );
        }

        $this->pagination_top = wpcf_pr_admin_has_pagination( $this->parent,
                $this->child_post_type, $page, $prev, $next, $per_page,
                $total_items );
        /*
         * 
         * 
         * Add pagination bottom
         */
        $options = array(__( 'All', 'wpcf' ) => 'all', 5 => 5, 10 => 10, 15 => 15);
// Add sorting
        $add_data = isset( $_GET['sort'] ) && isset( $_GET['field'] ) ? '&sort=' . strval( $_GET['sort'] ) . '&field='
                . strval( $_GET['field'] ) : '';
        $this->pagination_bottom = wpcf_form_simple( array(
            'pagination' => array(
                '#type' => 'select',
                '#before' => __( 'Show', 'wpcf' ),
                '#after' => $this->child_post_type_object->labels->name,
                '#id' => 'wpcf_relationship_num_' . wpcf_unique_id( serialize( $this->children ) ),
                '#name' => $wpcf->relationship->items_per_page_option_name,
                '#options' => $options,
                '#default_value' => $per_page,
                '#attributes' => array(
                    'class' => 'wpcf-relationship-items-per-page',
                    'data-action' => 'action=wpcf_ajax&wpcf_action=pr_pagination'
                    . '&post_id=' . $this->parent->ID . '&post_type='
                    . $this->child_post_type
                    . '&_wpnonce=' . wp_create_nonce( 'pr_pagination' ) . $add_data,
                ),
            ),
                ) );
    }

    /**
     * Returns rows.
     * 
     * @return type
     */
    function rows() {
        $rows = array();
        foreach ( $this->children as $child ) {
            $this->_set( $child );
            $rows[$child->ID] = $this->row();
        }
        return $rows;
    }

    /**
     * Returns HTML formatted row
     * 
     * While generating rows we collect headers too.
     * 
     * @return type
     */
    function row() {
        /*
         * Start output.
         * Output is returned as array - each element is <td> content.
         */
        $row = array();

        /*
         * LOOP over fields
         * Custom settings (specific)
         */
        if ( $this->data['fields_setting'] == 'specific'
                && !empty( $this->data['fields'] ) ) {
            // Set title
            if ( isset( $this->data['fields']['_wp_title'] ) ) {
                $this->headers[] = '_wp_title';
                $row[] = $this->title();
            }
            // Set body
            if ( isset( $this->data['fields']['_wp_body'] ) ) {
                $this->headers[] = '_wp_body';
                $row[] = $this->body();
            }
            // Loop over Types fields
            foreach ( $this->data['fields'] as $field_key => $true ) {
                // Skip parents
                if ( in_array( $field_key,
                                array('_wp_title', '_wp_body', '_wpcf_pr_parents') ) ) {
                    continue;
                } else {
                    /*
                     * Set field
                     */
                    $field_key = $this->cf->__get_slug_no_prefix( $field_key );
                    $this->cf->set( $this->child, $field_key );
                    $row[] = $this->field_form();
                    $this->_field_triggers();
                    // Add to header
                    $this->headers[] = $field_key;
                }
            }
            // Add parent forms
            if ( !empty( $this->data['fields']['_wpcf_pr_parents'] ) ) {
                $_temp = (array) $this->data['fields']['_wpcf_pr_parents'];
                foreach ( $_temp as $_parent => $_true ) {
                    $row[] = $this->_parent_form( $_parent );
                    // Add to header
                    $this->headers['__parents'][$_parent] = $_true;
                }
            }
            /*
             * 
             * 
             * 
             * 
             * DEFAULT SETTINGS
             */
        } else {
            // Set title
            $row[] = $this->title();
            $this->headers[] = '_wp_title';
            /*
             * Loop over groups and fields
             */
            // Get groups
            $groups = wpcf_admin_post_get_post_groups_fields( $this->child,
                    'post_relationships' );
            foreach ( $groups as $group ) {
                /*
                 * Loop fields
                 */
                foreach ( $group['fields'] as $field_key => $field ) {
                    /*
                     * Set field
                     */
                    $field_key = $this->cf->__get_slug_no_prefix( $field_key );
                    $this->cf->set( $this->child, $field_key );
                    $row[] = $this->field_form();
                    $this->_field_triggers();
                    // Add to header{
                    $this->headers[] = $field_key;
                }
            }
        }

        return $row;
    }

    /**
     * Add here various triggers for field
     */
    function _field_triggers() {
        /*
         * Check if repetitive - add warning
         */
        if ( wpcf_admin_is_repetitive( $this->cf->cf ) ) {
            $this->repetitive_warning = true;
        }
        /*
         * Check if date - trigger it
         * TODO Move to date
         */
        if ( $this->cf->cf['type'] == 'date' ) {
            $this->trigger_date = true;
        }
    }

    /**
     * Returns HTML formatted title field.
     * 
     * @param type $post
     * @return type
     */
    function title() {
        return wpcf_form_simple(
                        array('field' => array(
                                '#type' => 'textfield',
                                '#id' => 'wpcf_post_relationship_'
                                . $this->child->ID . '_wp_title',
                                '#name' => 'wpcf_post_relationship['
                                . $this->parent->ID . ']['
                                . $this->child->ID . '][_wp_title]',
                                '#value' => $this->child->post_title,
                                '#inline' => true,
                            )
                        )
        );
    }

    /**
     * Returns HTML formatted body field.
     * 
     * @return type
     */
    function body() {
        return wpcf_form_simple(
                        array('field' => array(
                                '#type' => 'textarea',
                                '#id' => 'wpcf_post_relationship_'
                                . $this->child->ID . '_wp_body',
                                '#name' => 'wpcf_post_relationship['
                                . $this->parent->ID . ']['
                                . $this->child->ID . '][_wp_body]',
                                '#value' => $this->child->post_content,
                                '#attributes' => array('style' => 'width:300px;height:100px;'),
                                '#inline' => true,
                            )
                        )
        );
    }

    /**
     * Returns element form as array.
     * 
     * This is done per field.
     * 
     * @param type $key Field key as stored
     * @return array
     */
    function field_form() {
        /*
         * 
         * Get meta form for field
         */
        $form = $this->cf->_get_meta_form( $this->cf->__meta,
                $this->cf->meta_object->meta_id, false );
        /*
         * 
         * Filter form
         */
        $_filtered_form = $this->__filter_meta_form( $form );

        return wpcf_form_simple( apply_filters( 'wpcf_relationship_child_meta_form',
                                $_filtered_form, $this->cf ) );
    }

    /**
     * Filters meta form.
     * 
     * IMPORTANT: This is place where look of child form is altered.
     * Try not to spread it over other code.
     * 
     * @param string $form
     * @return string
     */
    function __filter_meta_form( $form = array() ) {
        foreach ( $form as $k => &$e ) {
            /*
             * 
             * Filter name
             */
            if ( isset( $e['#name'] ) ) {
                $e['#name'] = $this->cf->alter_form_name( 'wpcf_post_relationship['
                        . $this->parent->ID . ']', $e['#name'] );
            }
            /*
             * Some fields have #options and names set there.
             * Loop over them and adjust.
             */
            if ( !empty( $e['#options'] ) ) {
                foreach ( $e['#options'] as $_k => $_v ) {
                    if ( isset( $_v['#name'] ) ) {
                        $e['#options'][$_k]['#name'] = $this->alter_form_name( $_v['#name'] );
                    }
                }
            }
            if ( isset( $e['#title'] ) ) {
                unset( $e['#title'] );
            }
            if ( isset( $e['#description'] ) ) {
                unset( $e['#description'] );
            }
            $e['#inline'] = true;
        }

        return $form;
    }

    function alter_form_name( $name, $parent_id = null ){
        if ( is_null( $parent_id ) ) {
            $parent_id = $this->parent->ID;
        }
        return $this->cf->alter_form_name(
                        'wpcf_post_relationship[' . $parent_id . ']', $name
        );
    }

    /**
     * Content for choose parent column.
     * 
     * @return boolean
     */
    function _parent_form( $post_parent = '' ) {
        $item_parents = wpcf_pr_admin_get_belongs( $this->child_post_type );
        if ( $item_parents ) {
            foreach ( $item_parents as $parent => $temp_data ) {

                // Skip if only current available
                if ( $parent == $this->parent_post_type ) {
                    continue;
                }

                if ( !empty( $post_parent ) && $parent != $post_parent ) {
                    continue;
                }

                // Get parent ID
                $meta = get_post_meta( $this->child->ID,
                        '_wpcf_belongs_' . $parent . '_id', true );
                $meta = empty( $meta ) ? 0 : $meta;

                // Get form
                $belongs_data = array('belongs' => array($parent => $meta));
                $temp_form = wpcf_pr_admin_post_meta_box_belongs_form( $this->child,
                        $parent, $belongs_data );

                if ( empty( $temp_form ) ) {
                    return '<span class="types-small-italic">' . __( 'No parents available',
                                    'wpcf' ) . '</span>';
                }
                unset(
                        $temp_form[$parent]['#suffix'],
                        $temp_form[$parent]['#prefix'],
                        $temp_form[$parent]['#title']
                );
                $temp_form[$parent]['#name'] = 'wpcf_post_relationship['
                        . $this->parent->ID . '][' . $this->child->ID
                        . '][parents][' . $parent . ']';
                // Return HTML formatted output
                return wpcf_form_simple( $temp_form );
            }
        }
        return '<span class="types-small-italic">' . __( 'No parents available',
                        'wpcf' ) . '</span>';
    }

    /**
     * HTML formatted row.
     * 
     * @return type
     */
    function child_row( $child ) {
        $child_id = $child->ID;
        $this->_set( $child );
        $row = $this->row();
        ob_start();
        include WPCF_EMBEDDED_INC_ABSPATH . '/relationship/child-table-row.php';
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }

    /**
     * Header HTML formatted output.
     * 
     * Each header <th> is array element. Sortable.
     * 
     * @return array 'header_id' => html
     */
    function headers() {

        // Sorting
        $dir = isset( $_GET['sort'] ) && $_GET['sort'] == 'ASC' ? 'DESC' : 'ASC';
        $dir_default = 'ASC';
        $sort_field = isset( $_GET['field'] ) ? $_GET['field'] : '';

        // Set values
        $post = $this->parent;
        $post_type = $this->child_post_type;
        $parent_post_type = $this->parent_post_type;
        $data = $this->data;

        $wpcf_fields = wpcf_admin_fields_get_fields( true );
        $headers = array();

        foreach ( $this->headers as $k => $header ) {
            if ( $k === '__parents' ) {
                continue;
            }

            if ( $header == '_wp_title' ) {
                $title_dir = $sort_field == '_wp_title' ? $dir : 'ASC';
                $headers[$header] = '';
                $headers[$header] .= $sort_field == '_wp_title' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . '_wp_title&amp;sort=' . $title_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Title' ) . '</a>';
            } else if ( $header == '_wp_body' ) {
                $body_dir = $sort_field == '_wp_body' ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == '_wp_body' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . '_wp_body&amp;sort=' . $body_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Body' ) . '</a>';
            } else if ( strpos( $header, WPCF_META_PREFIX ) === 0
                    && isset( $wpcf_fields[str_replace( WPCF_META_PREFIX, '',
                                    $header )] ) ) {
                wpcf_admin_post_field_load_js_css( $post,
                        wpcf_fields_type_action( $wpcf_fields[str_replace( WPCF_META_PREFIX,
                                        '', $header )]['type'] ) );
                $field_dir = $sort_field == $header ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == $header ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . $header . '&amp;sort=' . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">' . stripslashes( $wpcf_fields[str_replace( WPCF_META_PREFIX,
                                        '', $header )]['name'] ) . '</a>';
                if ( wpcf_admin_is_repetitive( $wpcf_fields[str_replace( WPCF_META_PREFIX,
                                        '', $header )] ) ) {
                    $repetitive_warning = true;
                }
            } else {
                $field_dir = $sort_field == $header ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == $header ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . $header . '&amp;sort=' . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">'
                        . stripslashes( $header ) . '</a>';
            }
        }
        if ( !empty( $this->headers['__parents'] ) ) {
            foreach ( $this->headers['__parents'] as $_parent => $data ) {
                if ( $_parent == $parent_post_type ) {
                    continue;
                }
                $temp_parent_type = get_post_type_object( $_parent );
                if ( empty( $temp_parent_type ) ) {
                    continue;
                }
                $parent_dir = $sort_field == '_wpcf_pr_parent' ? $dir : $dir_default;
                $headers['_wpcf_pr_parent_' . $_parent] = $sort_field == '_wpcf_pr_parent' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers['_wpcf_pr_parent_' . $_parent] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . '_wpcf_pr_parent&amp;sort='
                                . $parent_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;post_type_sort_parent='
                                . $_parent . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">' . $temp_parent_type->label . '</a>';
            }
        }
        return $headers;
    }

}