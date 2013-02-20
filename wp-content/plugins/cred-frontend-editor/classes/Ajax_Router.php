<?php
class CRED_Ajax_Router
{
    const _PREFIX_=CRED_NAME;
    
    public static function init()
    {
        if(is_admin())
        {    
            // skype ajax bridge to Types
            add_action('wp_ajax_cred_skype_ajax', array('CRED_Ajax_Router','cred_skype_ajax'));
            add_action('wp_ajax_nopriv_cred_skype_ajax', array('CRED_Ajax_Router','cred_skype_ajax'));
            
            // ajax taxonomy suggest for all users, logged in or not
            add_action('wp_ajax_cred-ajax-tag-search', array('CRED_Ajax_Router','cred_ajax_tag_search'));
            add_action('wp_ajax_nopriv_cred-ajax-tag-search', array('CRED_Ajax_Router','cred_ajax_tag_search'));
            
            // ajax delete post handler for logged in and not
            add_action('wp_ajax_cred-ajax-delete-post', array('CRED_Ajax_Router','cred_ajax_delete_post'));
            add_action('wp_ajax_nopriv_cred-ajax-delete-post', array('CRED_Ajax_Router','cred_ajax_delete_post'));
        }
    }
    
    // duplicated from wp ajax function
    public static function cred_ajax_tag_search() 
    {
        global $wpdb;

        if ( isset( $_GET['tax'] ) ) {
            $taxonomy = sanitize_key( $_GET['tax'] );
            $tax = get_taxonomy( $taxonomy );
            if ( ! $tax )
                wp_die( 0 );
            // possible issue here, anyway bypass for now
            /*if ( ! current_user_can( $tax->cap->assign_terms ) )
                wp_die( -1);*/
        } else {
            wp_die( 0 );
        }

        $s = stripslashes( $_GET['q'] );

        $comma = _x( ',', 'tag delimiter' );
        if ( ',' !== $comma )
            $s = str_replace( $comma, ',', $s );
        if ( false !== strpos( $s, ',' ) ) {
            $s = explode( ',', $s );
            $s = $s[count( $s ) - 1];
        }
        $s = trim( $s );
        if ( strlen( $s ) < 2 )
            wp_die(); // require 2 chars for matching

        $results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . like_escape( $s ) . '%' ) );

        echo join( $results, "\n" );
        wp_die();
    }
    
    public static function cred_ajax_delete_post()
    {
        CRED_Loader::get("CONTROLLER/Posts", false)->deletePost($_GET,$_POST);
        wp_die();
    }
    
    // link CRED ajax call to wp-types ajax call (use wp-types for this)
    public static function cred_skype_ajax()
    {
        do_action('wp_ajax_wpcf_ajax');
        wp_die();
    }
}
?>