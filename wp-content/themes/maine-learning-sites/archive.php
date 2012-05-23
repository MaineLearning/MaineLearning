<?php
/*
Template Name: Object Archive
*/

remove_action( 'genesis_before_post_content', 'genesis_post_info' );
remove_action( 'genesis_post_content', 'genesis_do_post_image' );
remove_action( 'genesis_after_post_content', 'genesis_post_meta' );



add_action( 'genesis_post_content', 'object_fields' );
function object_fields() {
    genesis_custom_field( 'wpcf-abstract-excerpt' );
}

genesis();
