<?php
/*
 * Post relationship class.
 */
require_once dirname( __FILE__ ) . '/relationship/model.php';

/**
 * Post relationship class
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Relationship
 * @author srdjan <srdjan@icanlocalize.com>
 *
 */
class WPCF_Relationship
{

    var $model;
//    var $parent;
//    var $parents = array();
//    var $child;
//    var $children = array();
//    var $post_type = null;

    /**
     * Custom field
     * 
     * @var type 
     */
    var $cf = array();
    var $data = array();

    /**
     * Settings
     * 
     * @var type 
     */
    var $settings = array();
    var $items_per_page = 5;
    var $items_per_page_option_name = '_wpcf_relationship_items_per_page';
    var $child_form = null;

    /**
     * Construct function.
     */
    function __construct() {
        $this->cf = new WPCF_Field;
        $this->model = new WPCF_Relationship_Model();
        $this->settings = get_option( 'wpcf_post_relationship', array() );
    }

    /**
     * Sets current data.
     * 
     * @param type $parent
     * @param type $child
     * @param type $field
     * @param type $data
     */
    function set( $parent, $child, $data = array() ) {
        return $this->_set( $parent, $child, $data );
    }

    /**
     * Sets current data.
     * 
     * @param type $parent
     * @param type $child
     * @param type $field
     * @param type $data
     */
    function _set( $parent, $child, $data = array() ) {
        $this->parent = $parent;
        $this->child = $child;
        $this->cf = new WPCF_Field;
        // TODO Revise usage
        $this->data = $data;
    }

    /**
     * Meta box form on post edit page.
     * 
     * @param type $parent Parent post
     * @param type $post_type Child post type
     * @return type string HTML formatted form table
     */
    function child_meta_form( $parent, $post_type ) {
        if ( is_integer( $parent ) ) {
            $parent = get_post( $parent );
        }
        $output = '';
        require_once dirname( __FILE__ ) . '/relationship/form-child.php';
        $this->child_form = new WPCF_Relationship_Child_Form(
                        $parent,
                        $post_type,
                        $this->settings( $parent->post_type, $post_type )
        );
        $output .= $this->child_form->render();

        return $output;
    }

    /**
     * Child row rendered on AJAX 'Add New Child' call.
     * 
     * @param type $parent_id
     * @param type $child_id
     * @return type
     */
    function child_row( $parent_id, $child_id ) {
        $parent = get_post( intval( $parent_id ) );
        $child = get_post( intval( $child_id ) );
        if ( empty( $parent ) || empty( $child ) ) {
            return new WP_Error( 'wpcf-relationship-save-child', 'no parent/child post' );
        }
        $output = '';
        $this->child_form = $this->_get_child_form( $parent, $child );
        $output .= $this->child_form->child_row( $child );

        return $output;
    }

    function _get_child_form( $parent, $child ) {
        require_once dirname( __FILE__ ) . '/relationship/form-child.php';
        return new WPCF_Relationship_Child_Form(
                        $parent,
                        $child->post_type,
                        $this->settings( $parent->post_type, $child->post_type )
        );
    }

    function get_child() {
        $r = $this->child;
        $r->parent = $this->parent;
        $r->form = $this->_get_child_form( $r->parent, $this->child );
        return $r;
    }

    /**
     * Meta box form on post edit page.
     * 
     * @param type $key Field key as stored
     * @return array
     */
//    function parent_meta_form($post, $post_type, $data) {
//        $output = '';
//        require_once dirname(__FILE__) . '/relationship/form-parent.php';
//        $this->parent_form = new WPCF_Relationship_Parent_Form($post, $post_type, $data);
//        $output .= $this->parent_form->render();
//
//        return $output;
//    }

    /**
     * Save items_per_page settings.
     * 
     * @param type $parent
     * @param type $child
     * @param int $num 
     */
    function save_items_per_page( $parent, $child, $num ) {
        if ( post_type_exists( $parent ) && post_type_exists( $child ) ) {
            $option_name = $this->items_per_page_option_name . '_' . $parent . '_' . $child;
            if ( $num == 'all' ) {
                $num = 9999999999999999;
            }
            update_option( $option_name, intval( $num ) );
        }
    }

    /**
     * Return items_per_page settings
     * 
     * @param type $parent
     * @param type $child
     * @return type 
     */
    function get_items_per_page( $parent, $child ) {
        $per_page = get_option( $this->items_per_page_option_name . '_' . $parent . '_' . $child,
                $this->items_per_page );
        return empty( $per_page ) ? $this->items_per_page : $per_page;
    }

    /**
     * Adjusts post name when saving.
     * 
     * @todo Revise (not used?)
     * @param type $post
     * @return type
     */
    function get_insert_post_name( $post ) {
        if ( empty( $post->post_title ) ) {
            return $post->post_type . '-' . $post->ID;
        }
        return $post->post_title;
    }

    /**
     * Bulk saving children.
     * 
     * @param type $parent_id
     * @param type $children
     */
    function save_children( $parent_id, $children ) {
        foreach ( $children as $child_id => $fields ) {
            $this->save_child( $parent_id, $child_id, $fields );
        }
    }

    /**
     * Unified save child function.
     * 
     * @param type $child_id
     * @param type $parent_id
     */
    function save_child( $parent_id, $child_id, $save_fields = array() ) {

        global $wpdb;

        $parent = get_post( intval( $parent_id ) );
        $child = get_post( intval( $child_id ) );
        $post_data = array();

        if ( empty( $parent ) || empty( $child ) ) {
            return new WP_Error( 'wpcf-relationship-save-child', 'no parent/child post' );
        }

        // Save relationship
        update_post_meta( $child->ID,
                '_wpcf_belongs_' . $parent->post_type . '_id', $parent->ID );


        // Check if added via AJAX
        $check = get_post_meta( $child->ID, '_wpcf_relationship_new', true );
        $new = !empty( $check );
        delete_post_meta( $child->ID, '_wpcf_relationship_new' );

        if ( $new ) {
            
        }

        // Set post data
        $post_data['ID'] = $child->ID;

        // Title needs to be checked if submitted at all
        if ( !isset( $save_fields['_wp_title'] ) ) {
            // If not submitted that means it is not offered to be edited
            if ( !empty( $child->post_title ) ) {
                $post_title = $child->post_title;
            } else {
                // DO NOT LET IT BE EMPTY
                $post_title = $child->post_type . ' ' . $child->ID;
            }
        } else {
            $post_title = $save_fields['_wp_title'];
        }
        $post_data['post_title'] = $post_title;
        $post_data['post_content'] = !empty( $save_fields['_wp_body'] ) ? $save_fields['_wp_body'] : '';
        $post_data['post_type'] = $child->post_type;
        // TODO This should be revised
        $post_data['post_status'] = 'publish';

        /*
         * 
         * 
         * 
         * 
         * 
         * 
         * UPDATE POST
         */
        $updated_id = wp_update_post( $post_data );
        if ( empty( $updated_id ) ) {
            return new WP_Error( 'relationship-update-post-failed', 'Updating post failed' );
        }

        // Save parents
        if ( !empty( $save_fields['parents'] ) ) {
            foreach ( $save_fields['parents'] as $parent_post_type =>
                        $parent_post_id ) {
                update_post_meta( $child->ID,
                        '_wpcf_belongs_' . $parent_post_type . '_id',
                        $parent_post_id );
            }
        }

        // Unset non-types
        unset( $save_fields['_wp_title'], $save_fields['_wp_body'],
                $save_fields['parents'] );
        /*
         * 
         * 
         * 
         * 
         * 
         * 
         * UPDATE Loop over fields
         */
        foreach ( $save_fields as $slug => $value ) {
            $this->cf->set( $child, $slug );
            $this->cf->context = 'post_relationship';
            $this->cf->save( $value );
        }

        // Set the language
        // TODO WPML
        // TODO Move this and use hook
        global $sitepress;
        if ( isset( $sitepress ) ) {
            $lang_details = $sitepress->get_element_language_details( $parent->ID,
                    'post_' . $parent->post_type );
            if ( $lang_details ) {
                $sitepress->set_element_language_details( $child->ID,
                        'post_' . $child->post_type, null,
                        $lang_details->language_code );
            }
        }

        do_action( 'wpcf_relationship_save_child', $child, $parent );

        clean_post_cache( $parent->ID );
        clean_post_cache( $child->ID );

        return true;
    }

    /**
     * Saves new child.
     * 
     * @param type $parent_id
     * @param type $post_type
     * @return type
     */
    function add_new_child( $parent_id, $post_type ) {
        $parent = get_post( $parent_id );
        if ( empty( $parent ) ) {
            return new WP_Error( 'wpcf-relationship-no-parent', 'No parent' );
        }
        $new_post = array(
            'post_title' => ' ', // WP requires at least title with space
            'post_type' => $post_type,
            'post_status' => 'draft',
        );
        $id = wp_insert_post( $new_post, true );
        if ( !is_wp_error( $id ) ) {
            // Mark that it is new post
            update_post_meta( $id, '_wpcf_relationship_new', 1 );
            // Save relationship
            update_post_meta( $id,
                    '_wpcf_belongs_' . $parent->post_type . '_id', $parent->ID );
        }
        return $id;
    }

    /**
     * Saved relationship settings.
     * 
     * @param type $parent
     * @param type $child
     * @return type
     */
    function settings( $parent, $child ) {
        return isset( $this->settings[$parent][$child] ) ? $this->settings[$parent][$child] : array();
    }

    /**
     * Fetches submitted data.
     * 
     * @param type $parent_id
     * @param type $child_id
     * @return type
     */
    function get_submitted_data( $parent_id, $child_id, $field ) {
        return isset( $_POST['wpcf_post_relationship'][$parent_id][$child_id][$field->slug] ) ? $_POST['wpcf_post_relationship'][$parent_id][$child_id][$field->slug] : null;
    }

}