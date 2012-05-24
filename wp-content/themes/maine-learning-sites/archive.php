<?php
/*
Template Name: Object Archive
*/

add_action('genesis_before_loop', 'include_title'); 
function include_title() { 
    global $wp_query; 
    if ( ! is_category() && ! is_tag() && ! is_tax() ) 
        return; 

    $headline = $intro_text = ''; 
    if ( single_cat_title("", false) ) 
        $headline = sprintf( '<h1>%s</h1>', single_cat_title("", false) ); 
    if ( category_description() ) 
        $intro_text = category_description(); 
    if ( $headline || $intro_text ) 
        printf( '<div class="taxonomy-description">%s</div>', $headline . $intro_text );     
};  

remove_action( 'genesis_before_post_content', 'genesis_post_info' );
remove_action( 'genesis_post_content', 'genesis_do_post_image' );
remove_action( 'genesis_after_post_content', 'genesis_post_meta' );

add_action( 'genesis_post_content', 'object_fields' );
function object_fields() {
    genesis_custom_field( 'wpcf-abstract-excerpt' );
}

genesis();
