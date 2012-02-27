<?php
/* 
 * Filters the Breadcrumb on front end
 */

add_filter( 'genesis_breadcrumb_args', 'gsb_breadcrumb_args' );
/**
 * Changes the Breadcrumb arguments based on the Plugin Options
 * 
 * @author Nick Croft
 * @since 0.1
 * @version 0.1
 * @param array $args
 * @return array 
 */
function gsb_breadcrumb_args( $args ) {
    $args['home']                    = gsb_get_option('home');
    $args['sep']                     = gsb_get_option('sep');
    $args['list_sep']                = gsb_get_option('list_sep'); // Genesis 1.5 and later
    $args['prefix']                  = gsb_get_option('prefix');
    $args['suffix']                  = gsb_get_option('suffix');
    $args['heirarchial_attachments'] = gsb_get_option('heirarchial_attachments'); // Genesis 1.5 and later
    $args['heirarchial_categories']  = gsb_get_option('heirarchial_categories'); // Genesis 1.5 and later
    $args['display']                 = gsb_get_option('display');
    $args['labels']['prefix']        = gsb_get_option('label_prefix');
    $args['labels']['author']        = gsb_get_option('author');
    $args['labels']['category']      = gsb_get_option('category'); // Genesis 1.6 and later
    $args['labels']['tag']           = gsb_get_option('tag');
    $args['labels']['date']          = gsb_get_option('date');
    $args['labels']['search']        = gsb_get_option('search');
    $args['labels']['tax']           = gsb_get_option('tax');
    $args['labels']['404']           = gsb_get_option('404'); // Genesis 1.5 and later

    return $args;
}
