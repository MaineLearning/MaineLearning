<?php
/*
Template Name: Object Archive
*/

add_action('genesis_before_loop', 'include_title'); 
function include_title() { 
    global $wp_query; 
    if ( ! is_category() && ! is_tag() && ! is_tax() ) 
        return; 
        printf( '<div class="taxonomy-description"><h1>Requested resources</h1></div>', '');     
};  

remove_action('genesis_post_title', 'genesis_do_post_title');
remove_action( 'genesis_before_post_content', 'genesis_post_info' );
remove_action( 'genesis_post_content', 'genesis_do_post_content' );
add_action( 'genesis_post_content', 'the_content' );  
remove_action( 'genesis_after_post_content', 'genesis_post_meta' );

genesis();
