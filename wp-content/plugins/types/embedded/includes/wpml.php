<?php
/*
 * @since Types 1.2
 * 
 * All WPML specific functions should be moved here.
 * 
 * Mind wpml_action parameter for field.
 * Values:
 * 0 nothing, 1 copy, 2 translate
 */

// Only when WPML active
if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {

    // Relationship filter get_children query
    add_filter( 'wpcf_relationship_get_children_query',
            'wpcf_wpml_relationship_get_children_query', 10, 5 );

    add_filter( 'types_fields', 'wpcf_wpml_fields_filter', 10, 3 );
    add_filter( 'types_post_type', 'wpcf_wpml_post_types_translate', 10, 3 );
    add_filter( 'types_taxonomy', 'wpcf_wpml_taxonomy_translate', 10, 3 );
}

/**
 * WPML translate call.
 * 
 * @param type $name
 * @param type $string
 * @return type 
 */
function wpcf_translate( $name, $string, $context = 'plugin Types' ) {
    if ( !function_exists( 'icl_t' ) ) {
        return $string;
    }
    return icl_t( $context, $name, stripslashes( $string ) );
}

/**
 * Registers WPML translation string.
 * 
 * @param type $context
 * @param type $name
 * @param type $value 
 */
function wpcf_translate_register_string( $context, $name, $value,
        $allow_empty_value = false ) {
    if ( function_exists( 'icl_register_string' ) ) {
        icl_register_string( $context, $name, stripslashes( $value ),
                $allow_empty_value );
    }
}

/**
 * Relationship filter get_children query.
 * 
 * @param type $_query string for get_posts()
 * @param type $parent Parent
 * @param type $post_type Children post type
 * @param type $data Saved data
 * @param type $field Ordering field (optional)
 */
function wpcf_wpml_relationship_get_children_query( $_query, $parent,
        $post_type, $data, $field = null ) {

    global $sitepress;

    // Check if children post type is translatable
    if ( !$sitepress->is_translated_post_type( $post_type ) ) {
        // Parse string
        parse_str( $_query, $query );
        // Set 'lang' to 'all'
        $query['lang'] = 'all';
        return wpcf_parse_array_to_string( $query );
    }

    return $_query;
}

/**
 * WPML editor filter
 * 
 * @param type $cf_name
 * @return type 
 */
function wpcf_icl_editor_cf_name_filter( $cf_name ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    if ( empty( $fields ) ) {
        return $cf_name;
    }
    $cf_name = substr( $cf_name, 6 );
    if ( strpos( $cf_name, WPCF_META_PREFIX ) == 0 ) {
        $cf_name = str_replace( WPCF_META_PREFIX, '', $cf_name );
    }
    if ( isset( $fields[$cf_name]['name'] ) ) {
        $cf_name = wpcf_translate( 'field ' . $fields[$cf_name]['id'] . ' name',
                $fields[$cf_name]['name'] );
    }
    return $cf_name;
}

/**
 * WPML editor filter
 * 
 * @param type $cf_name
 * @param type $description
 * @return type 
 */
function wpcf_icl_editor_cf_description_filter( $description, $cf_name ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    if ( empty( $fields ) ) {
        return $description;
    }
    $cf_name = substr( $cf_name, 6 );
    if ( strpos( $cf_name, WPCF_META_PREFIX ) == 0 ) {
        $cf_name = str_replace( WPCF_META_PREFIX, '', $cf_name );
    }
    if ( isset( $fields[$cf_name]['description'] ) ) {
        $description = wpcf_translate( 'field ' . $fields[$cf_name]['id'] . ' description',
                $fields[$cf_name]['description'] );
    }

    return $description;
}

/**
 * WPML editor filter
 * 
 * @param type $cf_name
 * @param type $style
 * @return type 
 */
function wpcf_icl_editor_cf_style_filter( $style, $cf_name ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();

    if ( empty( $fields ) ) {
        return $style;
    }

    $cf_name = substr( $cf_name, 6 );

    if ( strpos( $cf_name, WPCF_META_PREFIX ) == 0 ) {
        $cf_name = str_replace( WPCF_META_PREFIX, '', $cf_name );
    }
    if ( isset( $fields[$cf_name]['type'] ) && $fields[$cf_name]['type'] == 'textarea' ) {
        $style = 1;
    }
    if ( isset( $fields[$cf_name]['type'] ) && $fields[$cf_name]['type'] == 'wysiwyg' ) {
        $style = 2;
    }
    return $style;
}

/**
 * Bulk translation. 
 */
function wpcf_admin_bulk_string_translation() {
    if ( !function_exists( 'icl_register_string' ) ) {
        return false;
    }
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';

    // Register groups
    $groups = wpcf_admin_fields_get_groups();
    foreach ( $groups as $group_id => $group ) {
        wpcf_translate_register_string( 'plugin Types',
                'group ' . $group_id . ' name', $group['name'] );
        if ( isset( $group['description'] ) ) {
            wpcf_translate_register_string( 'plugin Types',
                    'group ' . $group_id . ' description', $group['description'] );
        }
    }

    // Register fields
    $fields = wpcf_admin_fields_get_fields();
    foreach ( $fields as $field_id => $field ) {
        wpcf_translate_register_string( 'plugin Types',
                'field ' . $field_id . ' name', $field['name'] );
        if ( isset( $field['description'] ) ) {
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' description', $field['description'] );
        }

        // For radios or select
        if ( !empty( $field['data']['options'] ) ) {
            foreach ( $field['data']['options'] as $name => $option ) {
                if ( $name == 'default' ) {
                    continue;
                }
                if ( isset( $option['title'] ) ) {
                    wpcf_translate_register_string( 'plugin Types',
                            'field ' . $field_id . ' option ' . $name . ' title',
                            $option['title'] );
                }
                if ( isset( $option['value'] ) ) {
                    wpcf_translate_register_string( 'plugin Types',
                            'field ' . $field_id . ' option ' . $name . ' value',
                            $option['value'] );
                }
                if ( isset( $option['display_value'] ) ) {
                    wpcf_translate_register_string( 'plugin Types',
                            'field ' . $field_id . ' option ' . $name . ' display value',
                            $option['display_value'] );
                }
            }
        }

        if ( $field['type'] == 'checkbox' && (isset( $field['set_value'] ) && $field['set_value'] != '1') ) {
            // we need to translate the check box value to store
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' checkbox value',
                    $field['set_value'] );
        }

        if ( $field['type'] == 'checkbox' && !empty( $field['display_value_selected'] ) ) {
            // we need to translate the check box value to store
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' checkbox value selected',
                    $field['display_value_selected'] );
        }

        if ( $field['type'] == 'checkbox' && !empty( $field['display_value_not_selected'] ) ) {
            // we need to translate the check box value to store
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' checkbox value not selected',
                    $field['display_value_not_selected'] );
        }

        // Validation message
        if ( !empty( $field['data']['validate'] ) ) {
            foreach ( $field['data']['validate'] as $method => $validation ) {
                if ( !empty( $validation['message'] ) ) {
                    // Skip if it's same as default
                    $default_message = wpcf_admin_validation_messages( $method );
                    if ( $validation['message'] != $default_message ) {
                        wpcf_translate_register_string( 'plugin Types',
                                'field ' . $field_id . ' validation message ' . $method,
                                $validation['message'] );
                    }
                }
            }
        }
    }

    // Register types
    $custom_types = get_option( 'wpcf-custom-types', array() );
    foreach ( $custom_types as $post_type => $data ) {
        wpcf_custom_types_register_translation( $post_type, $data );
    }

    // Register taxonomies
    $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
    foreach ( $custom_taxonomies as $taxonomy => $data ) {
        wpcf_custom_taxonimies_register_translation( $taxonomy, $data );
    }
}

function wpcf_post_relationship_set_translated_children( $parent_post_id ) {
    // WPML check if it's translation of a child
    // Fix up the parent if it's the child of a related post and it doesn't yet have a parent
    if ( function_exists( 'icl_object_id' ) ) {

        $post = get_post( $parent_post_id );

        global $sitepress;
        $ulanguage = $sitepress->get_language_for_element( $parent_post_id,
                'post_' . $post->post_type );

        remove_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10, 4 );

        $original_post_id = icl_object_id( $parent_post_id, $post->post_type,
                false );
        if ( !empty( $original_post_id ) ) {
            // it has a translation
            $original_post = get_post( $original_post_id );
            if ( !empty( $original_post ) ) {

                // look for _wpcf_belongs_xxxx_id fields.

                $meta_key = '_wpcf_belongs_' . $original_post->post_type . '_id';

                global $wpdb;

                $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key= %s AND meta_value= %d";
                $original_children = $wpdb->get_col( $wpdb->prepare( $query,
                                $meta_key, $original_post_id ) );

                foreach ( $original_children as $child_id ) {

                    $child_post = get_post( $child_id );

                    // set if the child is tranlated
                    $translated_child_id = icl_object_id( $child_id,
                            $child_post->post_type, false, $ulanguage );
                    if ( $translated_child_id ) {
                        // Set the parent to be the translated parent
                        update_post_meta( $translated_child_id, $meta_key,
                                $parent_post_id );
                    }
                }
            }
        }

        add_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10, 4 );
    }
}

function wpcf_post_relationship_set_translated_parent( $child_post_id ) {
    // WPML check if it's translation of a child
    // Fix up the parent if it's the child of a related post and it doesn't yet have a parent
    if ( function_exists( 'icl_object_id' ) ) {

        remove_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10, 4 );

        $post = get_post( $child_post_id );
        $original_post_id = icl_object_id( $child_post_id, $post->post_type,
                false );
        if ( !empty( $original_post_id ) ) {
            // it has a translation
            $original_post = get_post( $original_post_id );
            if ( !empty( $original_post ) ) {

                // look for _wpcf_belongs_xxxx_id fields.

                $metas = get_post_custom( $original_post->ID );
                foreach ( $metas as $meta_key => $meta ) {
                    if ( strpos( $meta_key, '_wpcf_belongs_' ) !== false ) {
                        $meta_post = get_post( $meta[0] );
                        if ( !empty( $meta_post ) ) {
                            global $sitepress;
                            $ulanguage = $sitepress->get_language_for_element( $child_post_id,
                                    'post_' . $post->post_type );
                            $meta_translated_id = icl_object_id( $meta_post->ID,
                                    $meta_post->post_type, false, $ulanguage );
                            if ( $meta_translated_id ) {
                                // Set the parent to be the translated parent
                                update_post_meta( $child_post_id, $meta_key,
                                        $meta_translated_id );
                            }
                        }
                    }
                }
            }
        }

        add_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10, 4 );
    }
}

/**
 * Returns translated '_wpcf_belongs_XXX_id' if any.
 * 
 * @global type $sitepress
 * @param type $value
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type 
 */
function wpcf_wpml_relationship_meta_belongs_filter( $value, $object_id,
        $meta_key, $single ) {
    // WPML check if it's translation of a child
    // Only force if meta is not already set
    if ( empty( $value ) && function_exists( 'icl_object_id' ) && strpos( $meta_key,
                    '_wpcf_belongs_' ) !== false ) {
        $post = get_post( $object_id );
        $original_post_id = icl_object_id( $object_id, $post->post_type, false );
        if ( !empty( $original_post_id ) ) {
            remove_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter',
                    10, 4 );
            $original_post_meta = get_post_meta( $original_post_id, $meta_key,
                    true );
            add_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10,
                    4 );
            if ( !empty( $original_post_meta ) ) {
                $meta_post = get_post( $original_post_meta );
                if ( !empty( $meta_post ) ) {
                    global $sitepress;
                    $ulanguage = $sitepress->get_language_for_element( $object_id,
                            'post_' . $post->post_type );
                    $meta_translated_id = icl_object_id( $meta_post->ID,
                            $meta_post->post_type, false, $ulanguage );
                    if ( !empty( $meta_translated_id ) ) {
                        $value = $meta_translated_id;
                    }
                }
            }
        }
    }
    return $value;
}

/**
 * Adjust translated IDs.
 * 
 * @global type $sitepress
 * @param type $parent_post_id
 */
function wpcf_wpml_relationship_save_post_hook( $parent_post_id ){
    // WPML check if it's translation of a child
    // Fix up the parent if it's the child of a related post and it doesn't yet have a parent
    if ( function_exists( 'icl_object_id' ) ) {

        remove_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10, 4 );

        $post = get_post( $parent_post_id );
        $original_post_id = icl_object_id( $parent_post_id, $post->post_type,
                false );
        if ( !empty( $original_post_id ) ) {
            // it has a translation
            $original_post = get_post( $original_post_id );
            if ( !empty( $original_post ) ) {

                // look for _wpcf_belongs_xxxx_id fields.

                $metas = get_post_custom( $original_post->ID );
                foreach ( $metas as $meta_key => $meta ) {
                    if ( strpos( $meta_key, '_wpcf_belongs_' ) !== false ) {
                        $meta_post = get_post( $meta[0] );
                        $exists = get_post_meta( $parent_post_id, $meta_key,
                                true );
                        if ( !empty( $meta_post ) && empty( $exists ) ) {
                            global $sitepress;
                            $ulanguage = $sitepress->get_language_for_element( $parent_post_id,
                                    'post_' . $post->post_type );
                            $meta_translated_id = icl_object_id( $meta_post->ID,
                                    $meta_post->post_type, false, $ulanguage );
                            // Only force if meta is not already set
                            if ( !empty( $meta_translated_id ) ) {
                                update_post_meta( $parent_post_id, $meta_key,
                                        $meta_translated_id );
                            }
                        }
                    }
                }
            }
        }

        add_filter( 'get_post_metadata', 'wpcf_pr_meta_belongs_filter', 10, 4 );
    }
}

/**
 * Registers translation data.
 * 
 * @param type $post_type
 * @param type $data 
 */
function wpcf_custom_types_register_translation( $post_type, $data ) {
    if ( !function_exists( 'icl_register_string' ) ) {
        return $data;
    }
    if ( isset( $data['description'] ) ) {
        wpcf_translate_register_string( 'Types-CPT',
                $post_type . ' description', $data['description'] );
    }
    wpcf_wpml_register_labels( $post_type, $data, 'post_type' );
}

/**
 * Registers translation data.
 * 
 * @param type $post_type
 * @param type $data 
 */
function wpcf_custom_taxonimies_register_translation( $taxonomy, $data ) {
    if ( !function_exists( 'icl_register_string' ) ) {
        return $data;
    }
    if ( isset( $data['description'] ) ) {
        wpcf_translate_register_string( 'Types-TAX', $taxonomy . ' description',
                $data['description'] );
    }
    wpcf_wpml_register_labels( $taxonomy, $data, 'taxonomy' );
}

/**
 * Registers labels.
 * 
 * @param type $prefix
 * @param type $data
 * @param type $context
 */
function wpcf_wpml_register_labels( $prefix, $data, $context = 'post') {
    foreach ( $data['labels'] as $label => $string ) {
        switch ( $context ) {
            case 'taxonomies':
            case 'taxonomy':
            case 'tax':
                $default = wpcf_custom_taxonomies_default();
                if ( $label == 'name' || $label == 'singular_name' ) {
                    wpcf_translate_register_string( 'Types-TAX',
                            $prefix . ' ' . $label, $string );
                    continue;
                }
                if ( isset( $default['labels'][$label] )
                        && $string == $default['labels'][$label] ) {
                    wpcf_translate_register_string( 'Types-TAX', $label, $string );
                } else {
                    wpcf_translate_register_string( 'Types-TAX',
                            $prefix . ' ' . $label, $string );
                }
                break;

            default:
                $default = wpcf_custom_types_default();

                // Name and singular_name
                if ( $label == 'name' || $label == 'singular_name' ) {
                    wpcf_translate_register_string( 'Types-CPT',
                            $prefix . ' ' . $label, $string );
                    continue;
                }

                // Check others for defaults
                if ( isset( $default['labels'][$label] )
                        && $string == $default['labels'][$label] ) {
                    // Register default translation
                    wpcf_translate_register_string( 'Types-CPT', $label, $string );
                } else {
                    wpcf_translate_register_string( 'Types-CPT',
                            $prefix . ' ' . $label, $string );
                }
                break;
        }
    }
}

/**
 * Adds wpml_action property.
 * 
 * @global type $iclTranslationManagement
 * @param type $fields
 * @return int
 */
function wpcf_wpml_fields_filter( $fields, $args, $toolset ) {

    // Iteract only if toolset is WPML
    if ( $toolset == 'wpml' ) {
        global $iclTranslationManagement;
        foreach ( $fields as $field_id => $field ) {
            // Set only if missing
            if ( !isset( $field['wpml_action'] ) ) {
                $action = null;
                if ( defined( 'WPML_TM_VERSION' )
                        && !empty( $iclTranslationManagement ) ) {
                    if ( isset( $iclTranslationManagement->settings['custom_fields_translation'][wpcf_types_get_meta_prefix( $field ) . $field_id] ) ) {
                        $action = intval( $iclTranslationManagement->settings['custom_fields_translation'][wpcf_types_get_meta_prefix( $field ) . $field_id] );
                    }
                }
                if ( is_null( $action ) ) {
                    if ( isset( $field['type'] ) ) {
                        if ( in_array( $field['type'],
                                        array('date', 'skype', 'numeric', 'phone', 'image', 'file', 'email', 'url') ) ) {
                            $action = 1;
                        } else {
                            $action = 2;
                        }
                    }
                }
                $fields[$field_id] = $action;
            }
        }
    }

    return $fields;
}

/**
 * Translates data.
 * 
 * @param type $post_type
 * @param type $data 
 */
function wpcf_wpml_post_types_translate( $data, $post_type ) {
    if ( !function_exists( 'icl_t' ) ) {
        return $data;
    }
    $default = wpcf_custom_types_default();
    if ( !empty( $data['description'] ) ) {
        $data['description'] = wpcf_translate( $post_type . ' description',
                $data['description'], 'Types-CPT' );
    }
    foreach ( $data['labels'] as $label => $string ) {
        if ( $label == 'name' || $label == 'singular_name' ) {
            $data['labels'][$label] = wpcf_translate( $post_type . ' ' . $label,
                    $string, 'Types-CPT' );
            continue;
        }
        if ( !isset( $default['labels'][$label] ) || $string !== $default['labels'][$label] ) {
            $data['labels'][$label] = wpcf_translate( $post_type . ' ' . $label,
                    $string, 'Types-CPT' );
        } else {
            $data['labels'][$label] = wpcf_translate( $label, $string,
                    'Types-CPT' );
        }
    }
    return $data;
}

/**
 * Translates data.
 * 
 * @param type $taxonomy
 * @param type $data 
 */
function wpcf_wpml_taxonomy_translate( $data, $taxonomy ) {
    if ( !function_exists( 'icl_t' ) ) {
        return $data;
    }
    $default = wpcf_custom_taxonomies_default();
    if ( !empty( $data['description'] ) ) {
        $data['description'] = wpcf_translate( $taxonomy . ' description',
                $data['description'], 'Types-TAX' );
    }
    foreach ( $data['labels'] as $label => $string ) {
        if ( $label == 'name' || $label == 'singular_name' ) {
            $data['labels'][$label] = wpcf_translate( $taxonomy . ' ' . $label,
                    $string, 'Types-TAX' );
            continue;
        }
        if ( !isset( $default['labels'][$label] ) || $string !== $default['labels'][$label] ) {
            $data['labels'][$label] = wpcf_translate( $taxonomy . ' ' . $label,
                    $string, 'Types-TAX' );
        } else {
            $data['labels'][$label] = wpcf_translate( $label, $string,
                    'Types-TAX' );
        }
    }
    return $data;
}