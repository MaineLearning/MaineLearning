<?php
/*
 * Incubator
 * 
 * since Types 1.2
 */

add_action( 'wpcf_type', 'wpcf_filter_type', 10, 2 );

/**
 * Revised rewrite.
 * 
 * We force slugs now. Submitted and sanitized slug. Set slugs localized (WPML).
 * More solid way to force WP slugs.
 * 
 * @see https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/153925180/comments
 * @since 1.1.3.2
 * @param type $data
 * @param type $post_type
 * @return boolean 
 */
function wpcf_filter_type( $data, $post_type ) {
    if ( !empty( $data['rewrite']['enabled'] ) ) {
        $data['rewrite']['with_front'] = !empty( $data['rewrite']['with_front'] );
        $data['rewrite']['feeds'] = !empty( $data['rewrite']['feeds'] );
        $data['rewrite']['pages'] = !empty( $data['rewrite']['pages'] );

        // If slug is not submitted use default slug
        if ( empty( $data['rewrite']['slug'] ) ) {
            $data['rewrite']['slug'] = $data['slug'];
        }

        // Also force default slug if rewrite mode is 'normal'
        if ( !empty( $data['rewrite']['custom'] ) && $data['rewrite']['custom'] != 'normal' ) {
            $data['rewrite']['slug'] = $data['rewrite']['slug'];
        }

        // Register with _x()
        $data['rewrite']['slug'] = _x( $data['rewrite']['slug'], 'URL slug',
                'wpcf' );
        //
        // CHANGED leave it for reference if we need
        // to return handling slugs back to WP.
        // 
        // We unset slug settings and leave WP to handle it himself.
        // Let WP decide what slugs should be!
//        if (!empty($data['rewrite']['custom']) && $data['rewrite']['custom'] != 'normal') {
//            unset($data['rewrite']['slug']);
//        }
        // Just discard non-WP property
        unset( $data['rewrite']['custom'] );
    } else {
        $data['rewrite'] = false;
    }

    return $data;
}

add_action( 'admin_footer', 'wpcf_inc_test' );

function wpcf_inc_test() {
//    debug( types_get_fields( array(), 'wpml' ) );
}