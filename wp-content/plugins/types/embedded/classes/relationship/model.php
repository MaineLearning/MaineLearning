<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class WPCF_Relationship_Model
{

    /**
     * Gets children.
     * 
     * @global type $wpdb
     * @param type $post
     * @param type $post_type
     * @param string $data
     * @return type
     */
    function get_children( $post, $post_type, $data ) {
        if ( empty( $post ) ) {
            return array();
        }

        global $wpdb;

        $items = array();
        // TODO Warning
//        $repetitive_warning = false;
        // Cleanup data
        if ( empty( $data['fields_setting'] ) ) {
            $data['fields_setting'] = 'all_cf';
        }

        // List items
        if ( isset( $_GET['sort'] ) && isset( $_GET['field'] ) ) {

            if ( $_GET['field'] == '_wp_title' ) {

                // Allow filtering
                $_query = 'post_type=' . $post_type
                        . '&numberposts=-1&post_status=null&meta_key='
                        . '_wpcf_belongs_' . $post->post_type . '_id&meta_value='
                        . $post->ID . '&orderby=title&suppress_filters=0&order='
                        . strval( $_GET['sort'] );
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $_query, $post, $post_type, $data,
                        esc_attr( $_GET['field'] ) );

                $items = get_posts( $query );
            } else if ( $_GET['field'] == '_wpcf_pr_parent' ) {

                /*
                 * TODO We have two get_posts here
                 * See different filter hooks
                 */
                // Allow filtering
                $_query = 'post_type='
                        . $post_type . '&numberposts=-1&post_status=null&meta_key='
                        . '_wpcf_belongs_' . $post->post_type . '_id&meta_value='
                        . $post->ID . '&suppress_filters=0';
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $_query, $post, $post_type, $data,
                        esc_attr( $_GET['field'] ) );

                $items = get_posts( $query );

                if ( !empty( $items ) ) {
                    $include = array();
                    $additional = array();
                    foreach ( $items as $key => $item ) {
                        $meta = get_post_meta( $item->ID,
                                '_wpcf_belongs_'
                                . esc_attr( $_GET['post_type_sort_parent'] )
                                . '_id', true );
                        if ( empty( $meta ) ) {
                            $additional[] = $item;
                            continue;
                        }
                        $include[] = $item->ID;
                    }
                    if ( !empty( $include ) ) {

                        // Allow filtering
                        $_query = 'post_type='
                                . $post_type . '&numberposts=-1&post_status=null'
                                . '&meta_key=_wpcf_belongs_'
                                . esc_attr( $_GET['post_type_sort_parent'] )
                                . '_id'
                                . '&orderby=meta_value_num&order='
                                . esc_attr( strtoupper( $_GET['sort'] ) )
                                . '&suppress_filters=0&include=' . implode( ',',
                                        $include );
                        $query = apply_filters( 'wpcf_relationship_get_children_query_2',
                                $_query, $post, $post_type, $data,
                                esc_attr( $_GET['field'] ) );

                        /*
                         * TODO Document why we overwrite it here
                         */
                        $items = get_posts( $query );
                        $items = array_merge( $items, $additional );
                    }
                }
            } else if ( $_GET['field'] == '_wp_body' ) {

                // Allow filtering
                $_query = "
                    SELECT p.ID, p.post_title, p.post_content, p.post_type
                    FROM $wpdb->posts p
                    WHERE p.post_type = %s
                    AND p.post_status <> 'auto-draft'
                    GROUP BY p.ID
                    ORDER BY p.post_content " . esc_attr( strtoupper( $_GET['sort'] ) );
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $_query, $post, $post_type, $data,
                        esc_attr( $_GET['field'] ) );

                $items = $wpdb->get_results( $wpdb->prepare( $query, $post_type ) );
            } else {
                $field = wpcf_admin_fields_get_field( str_replace( 'wpcf-', '',
                                $_GET['field'] ) );
                $orderby = isset( $field['type'] ) && in_array( $field['type'],
                                array('numeric', 'date') ) ? 'meta_value_num' : 'meta_value';

                // Allow filtering
                $_query = 'post_type='
                        . $post_type . '&numberposts=-1&post_status=null&meta_key='
                        . '_wpcf_belongs_' . $post->post_type . '_id&meta_value='
                        . $post->ID . '&suppress_filters=0';
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $_query, $post, $post_type, $data,
                        esc_attr( $_GET['field'] ) );

                $items = get_posts( $query );
                if ( !empty( $items ) ) {
                    $include = array();
                    $additional = array();
                    foreach ( $items as $key => $item ) {
                        $meta = get_post_meta( $item->ID, $_GET['field'], true );
                        if ( empty( $meta ) ) {
                            $additional[] = $item;
                            continue;
                        }
                        $check = wpcf_cd_post_edit_field_filter( array(),
                                $field, $item, 'post-relationship-sort' );
                        if ( isset( $check['__wpcf_cd_status'] )
                                && $check['__wpcf_cd_status'] == 'failed' ) {
                            $additional[] = $item;
                        } else {
                            $include[] = $item->ID;
                        }
                    }
                    if ( !empty( $include ) ) {

                        // Allow filtering
                        $_query = 'post_type='
                                . $post_type . '&numberposts=-1&post_status=null&meta_key='
                                . $_GET['field'] . '&orderby=' . $orderby . '&order='
                                . esc_attr( strtoupper( $_GET['sort'] ) )
                                . '&suppress_filters=0&include=' . implode( ',',
                                        $include );
                        $query = apply_filters( 'wpcf_relationship_get_children_query_2',
                                $_query, $post, $post_type, $data,
                                esc_attr( $_GET['field'] ) );

                        /*
                         * TODO Document why we overwrite it here
                         */
                        $items = get_posts( $query );
                        $items = array_merge( $items, $additional );
                    }
                }
            }
        } else {

            // Allow filtering
            $_query = 'post_type=' . $post_type
                    . '&numberposts=-1&post_status=null&meta_key='
                    . '_wpcf_belongs_' . $post->post_type
                    . '_id&suppress_filters=0&meta_value=' . $post->ID;
            $query = apply_filters( 'wpcf_relationship_get_children_query', $_query,
                    $post, $post_type, $data );

            $items = get_posts( $query );
        }
        return $items;
            }

}