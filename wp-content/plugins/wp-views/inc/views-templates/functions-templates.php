<?php
  
function wpv_register_type_view_template() 
{
  $labels = array(
    'name' => _x('View templates', 'post type general name'),
    'singular_name' => _x('View template', 'post type singular name'),
    'add_new' => _x('Add New', 'book'),
    'add_new_item' => __('Add New View Template'),
    'edit_item' => __('Edit View Template'),
    'new_item' => __('New View Template'),
    'view_item' => __('View Views-Templates'),
    'search_items' => __('Search View Templates'),
    'not_found' =>  __('No view templates found'),
    'not_found_in_trash' => __('No view templates found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => 'View Templates'

  );
  $args = array(
    'labels' => $labels,
    'public' => false,
    'publicly_queryable' => false,
    'show_ui' => true, 
    'show_in_menu' => false, 
    'query_var' => false,
    'rewrite' => false,
    'can_export' => false,
    'capability_type' => 'post',
    'has_archive' => false, 
    'hierarchical' => false,
    'menu_position' => null,
	'menu_icon' => WPV_URL .'/res/img/views-18.png',
    'supports' => array('title','editor','author')
  ); 
  register_post_type('view-template',$args);
}
