<?php
/*
 * 
 * 
 * Types API Class
 */

/**
 * Types API Class
 * 
 * API should be call to /classes/ wrapper.
 * Sets global $wpcf_api object.
 * @see /embedded/includes/api.php
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category API
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Api
{

    /**
     * Local post object.
     * 
     * @var type 
     */
    var $post = null;

    function __construct() {
        $o = new stdClass();
        return $o;
    }

    /**
     * Sets post
     * 
     * @global type $post
     * @param type $post_id
     * @return null 
     */
    function set_post( $post_id = null ) {

        // Set post
        if ( !is_null( $post_id ) ) {
            $_post = get_post( $post_id );
        } else {
            $post_id = wpcf_get_post_id();
            $_post = get_post( $post_id );
        }

        if ( empty( $_post->ID ) ) {
            $this->post = null;
            return null;
        }

        $this->post = $_post;

        return $_post;
    }

}