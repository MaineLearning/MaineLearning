<?php
/**
 * Types API
 * 
 * API should be call to /classes/ wrapper.
 * Use global $wpcf_api to add new objects.
 * 
 * @since Types 1.2
 */

/**
 * Gets field meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @return type 
 */
function wpcf_api_field_meta_value( $field, $post_id = null, $raw = false ) {
    global $wpcf;

    // See if repetitive
    if ( wpcf_admin_is_repetitive( $field ) ) {
        return wpcf_api_field_meta_value_repetitive( $field, $post_id );
    }

    // Set post
    $post = $wpcf->api->set_post( $post_id );

    // Set field
    $wpcf->field->set( $post, $field );
    return $raw ? $wpcf->field->__meta : $wpcf->field->meta;
}

/**
 * Gets field meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @return type 
 */
function types_field_meta_value( $field, $post_id = null, $raw = false ) {
    return wpcf_api_field_meta_value( $field, $post_id, $raw );
}

/**
 * Gets repetitive meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @param type $meta_id
 * @return type 
 */
function wpcf_api_field_meta_value_repetitive( $field, $post_id = null,
        $meta_id = null ) {
    global $wpcf;

    // Set post
    $post = $wpcf->api->set_post( $post_id );

    // Set field
    $wpcf->repeater->set( $post, $field );
    $meta = $wpcf->repeater->meta;

    // See if single
    if ( !wpcf_admin_is_repetitive( $field ) ) {
        return isset( $meta['single'] ) ? $meta['single'] : null;
    }

    // Return single repetitive field value if meta_id specified
    if ( !is_null( $meta_id ) && isset( $meta['by_meta_id'][$meta_id] ) ) {
        return $meta['by_meta_id'][$meta_id];
    }

    // Return ordered
    if ( isset( $wpcf->repeater->meta['custom_order'] ) ) {
        return $wpcf->repeater->meta['custom_order'];
    }

    // Return by_meta_id
    if ( isset( $wpcf->repeater->meta['by_meta_id'] ) ) {
        return $wpcf->repeater->meta['by_meta_id'];
    }

    return array();
}

/**
 * Gets repetitive meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @param type $meta_id
 * @return type 
 */
function types_field_meta_value_repetitive( $field, $post_id = null,
        $meta_id = null ) {
    return wpcf_api_field_meta_value_repetitive( $field, $post_id, $meta_id );
}

/**
 * Saves repeater fields.
 * 
 * Repeater class checks:
 * $_POST['wpcf'][$field_slug_full]
 * e.g. 'wpcf-img'
 *  * If field slug do not exist in $_POST['wpcf'] - won't be saved.
 * 
 * @global type $wpcf_api
 * @param type $post
 * @param type $field
 * @return type 
 */
function wpcf_api_repetitive_save( $post, $field ) {
    global $wpcf;
    $wpcf->repeater->set( $post, $field );
    return $wpcf->repeater->save();
}

/**
 * Fetches saved meta for post.
 * 
 * Please debug output to get familiar with results returned:
 * 'single'
 * 'by_meta_id'
 * 'by_meta_key'
 * 'custom_order' (optional)
 * 
 * @global type $wpcf_api
 * @param type $post
 * @param type $field
 * @return type 
 */
function wpcf_api_repetitive_get_meta( $post, $field ) {
    global $wpcf;
    $wpcf->repeater->set( $post, $field );
    return $wpcf->repeater->_get_meta();
}

/**
 * Used for processing conditional statements.
 * 
 * Wrapper for wpcf_cd_post_edit_field_filter()
 * core function.
 * 
 * @param type $element
 * @param type $field
 * @param type $post
 * @param string $context
 * @return type 
 */
function wpcf_conditional_evaluate( $post, $field ) {
    global $wpcf;
    /*
     * 
     * Do not mess around with main $wpcf->conditional instance.
     * Clone and use own.
     */
    $e = clone $wpcf->conditional;
    $e->set( $post, $field );
    return $e->evaluate();
}

/**
 * Used for processing conditional statements.
 * 
 * Wrapper for wpcf_cd_post_edit_field_filter()
 * core function.
 * 
 * @param type $element
 * @param type $field
 * @param type $post
 * @param string $context
 * @return type 
 */
function types_conditional_evaluate( $post, $field ) {
    return wpcf_conditional_evaluate( $post, $field );
}

/**
 * Get fields.
 * 
 * @global type $wpcf
 * @param mixed $args Various args
 * @param string $toolset Useful in hooks
 * @return All filtered fields
 */
function types_get_fields( $args = array(), $toolset = 'types' ){
    global $wpcf;
    static $cache = array();
    $cache_key = md5( serialize( func_get_args() ) );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }
    $fields = $wpcf->fields->get_fields( $args, $toolset );
    $cache[$cache_key]['fields'] = $fields->all;
    return $cache[$cache_key]['fields'];
}

/**
 * API function for adding items in editor menu.
 * 
 * Follow these core functions:
 * 
 * // PHP function for adding field
 * @see wpcf_admin_post_add_to_editor( $field )
 * 
 * // PHP function that renders JS
 * @see wpcf_admin_post_add_to_editor_js()
 * 
 * // PHP function fetches all groups
 * @see wpcf_admin_post_get_post_groups_fields( $post )
 * 
 * // PHP functon for rendering editor popup
 * @see wpcf_fields_$type_editor_callback()
 * 
 * // Called on popup submit (common code)
 * @see editor_admin_popup_insert_shortcode_js($shortcode);
 * 
 * // PHP function returning generated shortcode
 * @see wpcf_fields_get_shortcode($field, $add)
 *          $add are parameters appended to shortcode
 * 
 * @todo Try to compile and see minimal parameters needed for this to work
 * @param type $menu_id
 * @param type $items
 */
function types_admin_add_to_editor( $menu_id, $items ) {
    require_once(WPCF_EMBEDDED_ABSPATH . '/common/visual-editor/editor-addon.class.php');
    wp_enqueue_style( 'wpcf-scroll',
            WPCF_EMBEDDED_RELPATH . '/common/visual-editor/res/css/scroll.css' );
}