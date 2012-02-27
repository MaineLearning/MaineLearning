<?php
/**
 * Creates options and outputs admin menu and options page
 */

// Include & setup custom metabox and fields
add_filter( 'ntg_settings_builder', 'gsb_admin_settings' );
function gsb_admin_settings( $admin_pages ) {
    
    $prefix = ''; // start with an underscore to hide fields from custom fields list
    
    
	$admin_pages[] = array(
            'settings' => array(
                'page_id'          => 'gsb-settings',
                'menu_ops'         => array(
                    'submenu' => array(
                        'parent_slug' => 'genesis',
                        'page_title'  => __('Simple Breadcrumbs', 'gsb'),
                        'menu_title'  => __('Simple Breadcrumbs', 'gsb'),
                        'capability'  => 'manage_options',
                    )
                ),
                'page_ops'         => array(
                    
                ),
                'settings_field'   => GSB_SETTINGS_FIELD,
                'default_settings' => array(
			'home'						=> __( 'Home', 'genesis' ),
			'sep'						=> ' / ',
			'list_sep'					=> ', ',
			'prefix'					=> '<div class=\'breadcrumb\'>',
			'suffix'					=> '</div>',
			'heirarchial_attachments'	=> true,
			'heirarchial_categories'	=> true,
			'display'					=> true,
				'label_prefix'	=> __( 'You are here: ', 'genesis' ),
				'author'	=> __( 'Archives for ', 'genesis' ),
				'category'	=> __( 'Archives for ', 'genesis' ),
				'tag'		=> __( 'Archives for ', 'genesis' ),
				'date'		=> __( 'Archives for ', 'genesis' ),
				'search'	=> __( 'Search for ', 'genesis' ),
				'tax'		=> __( 'Archives for ', 'genesis' ),
				'post_type'	=> __( 'Archives for ', 'genesis' ),
				'404'		=> __( 'Not found: ', 'genesis' )
                            
                ),
                
            ),
            'sanatize' => array(
                'no_html'   => array(
                        'home',
			'sep',
			'list_sep',
			'heirarchial_attachments',
			'heirarchial_categories',
			'display',
                        'label_prefix',
                        'author',
                        'category',
                        'tag',
                        'date',
                        'search',
                        'tax',
                        'post_type',
                        '404',
                ),
                'safe_html' => array(
                        'prefix',
			'suffix'
                )
            ),
            'help'       => array(
                
            ),
            'meta_boxes' => array(
                
                'id'         => 'gsc_settings',
                'title'      => 'Genesis Simple Comments Settings',
                'context'    => 'main',
                'priority'   => 'high',/**/
                'show_names' => true, // Show field names on the left
                'fields'     => array(
                    
                    array(
                        'name' => __( 'Default Settings', 'gsb' ),
                        'desc' => '',
                        'type' => 'title'
                    ),
                    array(
                        'name' => __("Home Link Text:", 'gsb'),
                        'desc' => '',
                        'id'   => 'home',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Trail Seperator:", 'gsb'),
                        'desc' => '',
                        'id'   => 'sep',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("List Seperator:", 'gsb'),
                        'desc' => '',
                        'id'   => 'list_sep',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Prefix:", 'gsb'),
                        'desc' => '',
                        'id'   => 'prefix',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Suffix:", 'gsb'),
                        'desc' => '',
                        'id'   => 'suffix',
                        'type' => 'text'
                    ),
                    array(
                        'name' => '',
                        'desc' => '',
                        'type' => 'title'
                    ),
                    array(
                        'name' => __("Enable Hierarchial Attachments?", 'gsb'),
                        'desc' => '',
                        'id'   => 'heirarchial_attachments',
                        'type' => 'checkbox'
                    ),
                    array(
                        'name' => __("Enable Hierarchial Categories?", 'gsb'),
                        'desc' => '',
                        'id'   => 'heirarchial_categories',
                        'type' => 'checkbox'
                    ),
                    array(
                        'name' => __( 'Labels', 'gsb' ),
                        'desc' => '',
                        'type' => 'title'
                    ),
                    array(
                        'name' => __("Prefix:", 'gsb'),
                        'desc' => '',
                        'id'   => 'label_prefix',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Author:", 'gsb'),
                        'desc' => '',
                        'id'   => 'author',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Category:", 'gsb'),
                        'desc' => '',
                        'id'   => 'category',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Tag:", 'gsb'),
                        'desc' => '',
                        'id'   => 'tag',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Date:", 'gsb'),
                        'desc' => '',
                        'id'   => 'date',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Search:", 'gsb'),
                        'desc' => '',
                        'id'   => 'search',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("Taxonomy:", 'gsb'),
                        'desc' => '',
                        'id'   => 'tax',
                        'type' => 'checkbox'
                    ),
                    array(
                        'name' => __("Post Type:", 'gsb'),
                        'desc' => '',
                        'id'   => 'post_type',
                        'type' => 'text'
                    ),
                    array(
                        'name' => __("404:", 'gsb'),
                        'desc' => '',
                        'id'   => '404',
                        'type' => 'text'
                    )
                ))
	);
	
	return $admin_pages;
}