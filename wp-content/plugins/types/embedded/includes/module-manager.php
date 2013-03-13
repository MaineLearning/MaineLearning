<?php
/*
 * Module Manager
 * 
 * Since Types 1.2
 */

define( '_TYPES_MODULE_MANAGER_KEY_', 'types' );
define( '_POSTS_MODULE_MANAGER_KEY_', 'posts' );
define( '_GROUPS_MODULE_MANAGER_KEY_', 'groups' );
define( '_FIELDS_MODULE_MANAGER_KEY_', 'fields' );
define( '_TAX_MODULE_MANAGER_KEY_', 'taxonomies' );

/*
 * 
 * Add inline tables on admin screens
 */
add_action( 'wpcf_admin_footer_wpcf-edit', 'wpcf_module_inline_table_fields' );

add_action( 'wpcf_admin_footer_wpcf-edit-type',
        'wpcf_module_inline_table_post_types' );

add_action( 'wpcf_admin_footer_wpcf-edit-tax',
        'wpcf_module_inline_table_post_taxonomies' );

/**
 * Fields table.
 */
function wpcf_module_inline_table_fields() {
    // dont add module manager meta box on new post type form
    if ( defined( 'MODMAN_PLUGIN_NAME' ) && isset( $_GET['group_id'] ) ) {
        $_custom_groups = wpcf_admin_fields_get_groups();
        foreach ( $_custom_groups as $_group ) {
            if ( $_group['id'] == $_GET['group_id'] ) {
                // add module manager meta box to post type form
                $element = array('id' => '12' . _GROUPS_MODULE_MANAGER_KEY_ . '21' . $_group['id'],
                    'title' => $_group['name'], 'section' => _GROUPS_MODULE_MANAGER_KEY_);
                echo '<table class="widefat modman-inline-table"><thead><tr><th>'
                . __( 'Module Manager', 'wpcf' )
                . '</th></tr></thead><tbody><tr><td>';
                do_action( 'wpmodules_inline_element_gui', $element );
                echo '</td></tr></tbody></table>';
                break;
            }
        }
    }
}

/**
 * Post types table.
 */
function wpcf_module_inline_table_post_types() {
    // dont add module manager meta box on new post type form
    if ( defined( 'MODMAN_PLUGIN_NAME' ) && isset( $_GET['wpcf-post-type'] ) ) {
        $_custom_types = get_option( 'wpcf-custom-types', array() );
        if ( isset( $_custom_types[$_GET['wpcf-post-type']] ) ) {
            $_post_type = $_custom_types[$_GET['wpcf-post-type']];
            // add module manager meta box to post type form
            $element = array('id' => '12' . _TYPES_MODULE_MANAGER_KEY_ . '21' . $_post_type['slug'], 'title' => $_post_type['labels']['singular_name'], 'section' => _TYPES_MODULE_MANAGER_KEY_);
            echo '<br /><br /><table class="wpcf-types-form-table widefat"><thead><tr><th colspan="2">' . __( 'Module Manager',
                    'wpcf' ) . '</th></tr></thead><tbody><tr><td>';
            do_action( 'wpmodules_inline_element_gui', $element );
            echo '</td></tr></tbody></table>';
        }
    }
}

/**
 * Taxonomies table.
 */
function wpcf_module_inline_table_post_taxonomies() {
    // dont add module manager meta box on new post type form
    if ( defined( 'MODMAN_PLUGIN_NAME' ) && isset( $_GET['wpcf-tax'] ) ) {
        $_custom_taxes = get_option( 'wpcf-custom-taxonomies', array() );
        if ( isset( $_custom_taxes[$_GET['wpcf-tax']] ) ) {
            $_tax = $_custom_taxes[$_GET['wpcf-tax']];
            // add module manager meta box to post type form
            $element = array('id' => '12' . _TAX_MODULE_MANAGER_KEY_ . '21' . $_tax['slug'],
                'title' => $_tax['labels']['singular_name'], 'section' => _TAX_MODULE_MANAGER_KEY_);
            echo '<br /><br /><table class="wpcf-types-form-table widefat"><thead><tr><th colspan="2">' . __( 'Module Manager',
                    'wpcf' ) . '</th></tr></thead><tbody><tr><td>';
            do_action( 'wpmodules_inline_element_gui', $element );
            echo '</td></tr></tbody></table>';
        }
    }
}

// setup module manager hooks and actions
if ( defined( 'MODMAN_PLUGIN_NAME' ) ) {
    add_filter( 'wpmodules_register_sections', 'wpcf_register_modules_sections',
            10, 1 );
    add_filter( 'wpmodules_register_items_' . _TYPES_MODULE_MANAGER_KEY_,
            'wpcf_register_modules_items_types', 10, 1 );
    add_filter( 'wpmodules_export_items_' . _TYPES_MODULE_MANAGER_KEY_,
            'wpcf_export_modules_items_types', 10, 2 );
    add_filter( 'wpmodules_import_items_' . _TYPES_MODULE_MANAGER_KEY_,
            'wpcf_import_modules_items_types', 10, 2 );
    add_filter( 'wpmodules_register_items_' . _GROUPS_MODULE_MANAGER_KEY_,
            'wpcf_register_modules_items_groups', 10, 1 );
    add_filter( 'wpmodules_export_items_' . _GROUPS_MODULE_MANAGER_KEY_,
            'wpcf_export_modules_items_groups', 10, 2 );
    add_filter( 'wpmodules_import_items_' . _GROUPS_MODULE_MANAGER_KEY_,
            'wpcf_import_modules_items_groups', 10, 2 );
    add_filter( 'wpmodules_register_items_' . _TAX_MODULE_MANAGER_KEY_,
            'wpcf_register_modules_items_taxonomies', 10, 1 );
    add_filter( 'wpmodules_export_items_' . _TAX_MODULE_MANAGER_KEY_,
            'wpcf_export_modules_items_taxonomies', 10, 2 );
    add_filter( 'wpmodules_import_items_' . _TAX_MODULE_MANAGER_KEY_,
            'wpcf_import_modules_items_taxonomies', 10, 2 );

    // Check items
    add_filter( 'wpmodules_items_check_' . _TYPES_MODULE_MANAGER_KEY_,
            'wpcf_modman_items_check_custom_post_types', 10, 2 );
    add_filter( 'wpmodules_items_check_' . _GROUPS_MODULE_MANAGER_KEY_,
            'wpcf_modman_items_check_groups', 10, 2 );
    add_filter( 'wpmodules_items_check_' . _TAX_MODULE_MANAGER_KEY_,
            'wpcf_modman_items_check_taxonomies', 10, 2 );

    /*
     * Module Manager Functions
     */

    function wpcf_register_modules_sections( $sections ) {
        $sections[_TYPES_MODULE_MANAGER_KEY_] = array(
            'title' => __( 'Post Types', 'wpcf' ),
            'icon' => WPCF_RES_RELPATH . '/images/logo-12.png'
        );
        $sections[_GROUPS_MODULE_MANAGER_KEY_] = array(
            'title' => __( 'Field Groups', 'wpcf' ),
            'icon' => WPCF_RES_RELPATH . '/images/logo-12.png'
        );
        // no individual fields are exported
        /* $sections[_FIELDS_MODULE_MANAGER_KEY_]=array(
          'title'=>__('Fields','wpcf'),
          'icon'=>WPCF_RES_RELPATH.'/images/logo-12.png'
          ); */
        $sections[_TAX_MODULE_MANAGER_KEY_] = array(
            'title' => __( 'Taxonomies', 'wpcf' ),
            'icon' => WPCF_RES_RELPATH . '/images/logo-12.png'
        );

        return $sections;
    }

    function wpcf_register_modules_items_types( $items ) {
        $custom_types = get_option( 'wpcf-custom-types', array() );
        foreach ( $custom_types as $type ) {
            $_details = sprintf( __( '%s custom post type: %s', 'wpcf' ),
                    ucfirst( $type['public'] ), $type['labels']['name'] );
            $details = !empty( $type['description'] ) ? $type['description'] : $_details;
            $items[] = array(
                'id' => '12' . _TYPES_MODULE_MANAGER_KEY_ . '21' . $type['slug'],
                'title' => $type['labels']['singular_name'],
                'details' => '<p style="padding:5px;">' . $details . '</p>',
                '__types_id' => $type['slug'],
            );
        }
        return $items;
    }

    function wpcf_export_modules_items_types( $res, $items ) {
        foreach ( $items as $ii => $item ) {
            $items[$ii] = str_replace( '12' . _TYPES_MODULE_MANAGER_KEY_ . '21',
                    '', $item );
        }
        require_once WPCF_INC_ABSPATH . '/import-export.php';
        $xmlstring = wpcf_admin_export_selected_data( $items, 'types',
                'module_manager' );
        return $xmlstring;
    }

    function wpcf_import_modules_items_types( $result, $xmlstring ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';
        $result2 = wpcf_admin_import_data_from_xmlstring( $xmlstring, 'types' );
        if ( false === $result2 || is_wp_error( $result2 ) )
            return (false === $result2) ? __( 'Error during Post Types import',
                            'wpcf' ) : $result2->get_error_message( $result2->get_error_code() );

        return $result2;
    }

    function wpcf_register_modules_items_groups( $items ) {
        $groups = wpcf_admin_fields_get_groups();
        foreach ( $groups as $group ) {
            $_details = sprintf( __( 'Fields group: %s', 'wpcf' ),
                    $group['name'] );
            $details = !empty( $group['description'] ) ? $group['description'] : $_details;
            $items[] = array(
                'id' => '12' . _GROUPS_MODULE_MANAGER_KEY_ . '21' . $group['id'],
                'title' => $group['name'],
                'details' => '<p style="padding:5px;">' . $details . '</p>',
                '__types_id' => $group['id'],
            );
        }
        return $items;
    }

    function wpcf_export_modules_items_groups( $res, $items ) {
        foreach ( $items as $ii => $item ) {
            $items[$ii] = intval( str_replace( '12' . _GROUPS_MODULE_MANAGER_KEY_ . '21',
                            '', $item['id'] ) );
        }
        require_once WPCF_INC_ABSPATH . '/import-export.php';
        $xmlstring = wpcf_admin_export_selected_data( $items, 'groups',
                'module_manager' );
        return $xmlstring;
    }

    function wpcf_import_modules_items_groups( $result, $xmlstring ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';
        $result2 = wpcf_admin_import_data_from_xmlstring( $xmlstring, 'groups' );
        if ( false === $result2 || is_wp_error( $result2 ) )
            return (false === $result2) ? __( 'Error during Field Groups import',
                            'wpcf' ) : $result2->get_error_message( $result2->get_error_code() );

        return $result2;
    }

    function wpcf_register_modules_items_taxonomies( $items ) {
        $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );

        foreach ( $custom_taxonomies as $tax ) {
            $_details = sprintf( __( 'Fields group: %s', 'wpcf' ),
                    $tax['labels']['name'] );
            $details = !empty( $tax['description'] ) ? $tax['description'] : $_details;
            $items[] = array(
                'id' => '12' . _TAX_MODULE_MANAGER_KEY_ . '21' . $tax['slug'],
                'title' => $tax['labels']['singular_name'],
                'details' => '<p style="padding:5px;">' . $details . '</p>',
                '__types_id' => $tax['slug'],
            );
        }
        return $items;
    }

    function wpcf_export_modules_items_taxonomies( $res, $items ) {
        foreach ( $items as $ii => $item ) {
            $items[$ii] = str_replace( '12' . _TAX_MODULE_MANAGER_KEY_ . '21',
                    '', $item );
        }
        require_once WPCF_INC_ABSPATH . '/import-export.php';
        $xmlstring = wpcf_admin_export_selected_data( $items, 'taxonomies',
                'module_manager' );
        return $xmlstring;
    }

    function wpcf_import_modules_items_taxonomies( $result, $xmlstring ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';
        $result2 = wpcf_admin_import_data_from_xmlstring( $xmlstring,
                'taxonomies' );
        if ( false === $result2 || is_wp_error( $result2 ) )
            return (false === $result2) ? __( 'Error during Taxonomies import',
                            'wpcf' ) : $result2->get_error_message( $result2->get_error_code() );

        return $result2;
    }

}

/**
 * Custom Export function for Module Manager.
 * 
 * Exports selected items (by ID) and of specified type (eg views, view-templates).
 * Returns xml string.
 * 
 * @global type $iclTranslationManagement
 * @param array $items
 * @param type $_type
 * @param type $return mixed array|xml|download
 * @return string
 */
function wpcf_admin_export_selected_data( array $items, $_type = 'all',
        $return = 'download' ) {

    global $wpcf;

    require_once WPCF_EMBEDDED_ABSPATH . '/common/array2xml.php';
    $xml = new ICL_Array2XML();
    $data = array();

    if ( 'groups' == $_type || 'all' == $_type ) {
        // Get groups
        if ( empty( $items ) ) {
            $groups = get_posts( 'post_type=wp-types-group&post_status=null&numberposts=-1' );
        } else {
            /*
             * 
             * This fails
             * $items are in form of:
             * 0 => array('id' => 'pt', ...)
             */
            foreach ( $items as $k => $item ) {
                if ( isset( $item['id'] ) ) {
                    $items[$k] = intval( wpcf_modman_get_submitted_id( 'groups',
                                    $item['id'] ) );
                }
            }
            $args = array(
                'post__in' => $items,
                'post_type' => 'wp-types-group',
                'post_status' => 'all',
                'posts_per_page' => -1
            );
            $groups = get_posts( $args );
        }
        if ( !empty( $groups ) ) {
            $data['groups'] = array('__key' => 'group');
            foreach ( $groups as $key => $post ) {
                $post = (array) $post;
                $post_data = array();
                $copy_data = array('ID', 'post_content', 'post_title',
                    'post_excerpt', 'post_type', 'post_status');
                foreach ( $copy_data as $copy ) {
                    if ( isset( $post[$copy] ) ) {
                        $post_data[$copy] = $post[$copy];
                    }
                }
                $_data = $post_data;
                $meta = get_post_custom( $post['ID'] );
                if ( !empty( $meta ) ) {
                    $_meta = array();
                    foreach ( $meta as $meta_key => $meta_value ) {
                        if ( in_array( $meta_key,
                                        array(
                                    '_wp_types_group_terms',
                                    '_wp_types_group_post_types',
                                    '_wp_types_group_fields',
                                    '_wp_types_group_templates',
                                    '_wpcf_conditional_display',
                                        )
                                )
                        ) {
                            $_meta[$meta_key] = $meta_value[0];
                        }
                    }
                    if ( !empty( $_meta ) ) {
                        $_data['meta'] = $_meta;
                    }
                }
                $_data['checksum'] = $_data['hash'] = $wpcf->export->generate_checksum( 'group',
                        $post['ID'] );
                $data['groups']['group-' . $post['ID']] = $_data;
            }
        }

        if ( !empty( $items ) ) {
            // Get fields by group
            // TODO Document why we use by_group
            $fields = array();
            foreach ( $groups as $key => $post ) {
                $fields = array_merge( $fields,
                        wpcf_admin_fields_get_fields_by_group( $post->ID,
                                'slug', false, false, false ) );
            }
        } else {
            // Get fields
            $fields = wpcf_admin_fields_get_fields();
        }
        if ( !empty( $fields ) ) {

            // Add checksums before WPML
            foreach ( $fields as $field_id => $field ) {
                // TODO WPML and others should use hook
                $fields[$field_id] = apply_filters( 'wpcf_export_field',
                        $fields[$field_id] );

                $fields[$field_id]['checksum'] = $fields[$field_id]['hash'] = $wpcf->export->generate_checksum(
                        'field', $field_id
                );
            }

            // WPML
            global $iclTranslationManagement;
            if ( !empty( $iclTranslationManagement ) ) {
                foreach ( $fields as $field_id => $field ) {
                    // TODO Check this for all fields
                    if ( isset( $iclTranslationManagement->settings['custom_fields_translation'][wpcf_types_get_meta_prefix( $field ) . $field_id] ) ) {
                        $fields[$field_id]['wpml_action'] = $iclTranslationManagement->settings['custom_fields_translation'][wpcf_types_get_meta_prefix( $field ) . $field_id];
                    }
                }
            }

            $data['fields'] = $fields;
            $data['fields']['__key'] = 'field';
        }
    }

    // Get custom types
    if ( 'types' == $_type || 'all' == $_type ) {
        // Get custom types
        // TODO Document $items
        if ( !empty( $items ) ) {
            /*
             * 
             * This fails
             * $items are in form of:
             * 0 => array('id' => 'pt', ...)
             */
//            $custom_types = array_intersect_key( get_option( 'wpcf-custom-types',
//                            array() ), array_flip( $items ) );
            $_items = array();
            foreach ( $items as $item ) {
                $_items[$item['id']] = true;
            }
            $custom_types = array_intersect_key( get_option( 'wpcf-custom-types',
                            array() ), $_items );
        } else {
            $custom_types = get_option( 'wpcf-custom-types', array() );
        }
        // Get custom types
        if ( !empty( $custom_types ) ) {
            foreach ( $custom_types as $key => $type ) {
                $custom_types[$key]['id'] = $key;
                $custom_types[$key] = apply_filters( 'wpcf_export_custom_post_type',
                        $custom_types[$key] );
                $custom_types[$key]['checksum'] = $custom_types[$key]['hash'] = $wpcf->export->generate_checksum(
                        'custom_post_type', $key, $type
                );
            }
            $data['types'] = $custom_types;
            $data['types']['__key'] = 'type';
        }

        if ( !empty( $items ) ) {
            // Get post relationships only for items
            $relationships_all = get_option( 'wpcf_post_relationship', array() );
            $relationships = array();
            foreach ( $relationships_all as $parent => $children ) {
                if ( in_array( $parent, $items ) ) {
                    foreach ( $children as $child => $childdata ) {
                        if ( in_array( $child, $items ) ) {
                            if ( !isset( $relationships[$parent] ) )
                                $relationships[$parent] = array();
                            $relationships[$parent][$child] = $childdata;
                        }
                    }
                }
            }
        } else {
            // Get post relationships
            $relationships = get_option( 'wpcf_post_relationship', array() );
        }
        if ( !empty( $relationships ) ) {
            $data['post_relationships']['data'] = serialize( $relationships );
            $data['post_relationships']['__key'] = 'post_relationship';
        }
    }

    // Get custom tax
    if ( 'taxonomies' == $_type || 'all' == $_type ) {
        if ( !empty( $items ) ) {
            /*
             * 
             * This fails
             * $items are in form of:
             * 0 => array('id' => 'pt', ...)
             */
//            $custom_taxonomies = array_intersect_key( get_option( 'wpcf-custom-taxonomies',
//                            array() ), array_flip( $items ) );
            $_items = array();
            foreach ( $items as $item ) {
                $_items[$item['id']] = true;
            }
            $custom_taxonomies = array_intersect_key( get_option( 'wpcf-custom-taxonomies',
                            array() ), $_items );
        } else {
            // Get custom tax
            $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
        }
        if ( !empty( $custom_taxonomies ) ) {
            foreach ( $custom_taxonomies as $key => $tax ) {
                $custom_taxonomies[$key]['id'] = $key;
                $custom_taxonomies[$key] = apply_filters( 'wpcf_export_custom_post_type',
                        $custom_taxonomies[$key] );
                $custom_taxonomies[$key]['checksum'] = $wpcf->export->generate_checksum(
                        'custom_taxonomy', $key, $tax
                );
            }
            $data['taxonomies'] = $custom_taxonomies;
            $data['taxonomies']['__key'] = 'taxonomy';
        }
    }

    /*
     * 
     * Since Types 1.2
     */
    if ( $return == 'array' ) {
        return $data;
    } else if ( $return == 'xml' ) {
        return $xml->array2xml( $data, 'types' );
    } else if ( $return == 'module_manager' ) {
        // Add checksums to items
        foreach ( $data as $type ) {
            foreach ( $type as $_data ) {
                if ( isset( $_data['__types_id'] )
                        && isset( $items[$_data['__types_id']] ) ) {
                    $items[$_data['__types_id']] = $_data['checksum'];
                }
            }
        }
        return array(
            'xml' => $xml->array2xml( $data, 'types' ),
            'items' => $items,
        );
    }

    // Offer for download
    $data = $xml->array2xml( $data, 'types' );

    $sitename = sanitize_title( get_bloginfo( 'name' ) );
    if ( !empty( $sitename ) ) {
        $sitename .= '.';
    }
    $filename = $sitename . 'types.' . date( 'Y-m-d' ) . '.xml';
    $code = "<?php\r\n";
    $code .= '$timestamp = ' . time() . ';' . "\r\n";
    $code .= '$auto_import = ';
    $code .= (isset( $_POST['embedded-settings'] ) && $_POST['embedded-settings'] == 'ask') ? 0 : 1;
    $code .= ';' . "\r\n";
    $code .= "\r\n?>";

    if ( class_exists( 'ZipArchive' ) ) {
        $zipname = $sitename . 'types.' . date( 'Y-m-d' ) . '.zip';
        $temp_dir = sys_get_temp_dir();
        $file = tempnam( $temp_dir, "zip" );
        $zip = new ZipArchive();
        $zip->open( $file, ZipArchive::OVERWRITE );

        $zip->addFromString( 'settings.xml', $data );
        $zip->addFromString( 'settings.php', $code );
        $zip->close();
        $data = file_get_contents( $file );
        header( "Content-Description: File Transfer" );
        header( "Content-Disposition: attachment; filename=" . $zipname );
        header( "Content-Type: application/zip" );
        header( "Content-length: " . strlen( $data ) . "\n\n" );
        header( "Content-Transfer-Encoding: binary" );
        echo $data;
        unlink( $file );
        die();
    } else {
        // download the xml.

        header( "Content-Description: File Transfer" );
        header( "Content-Disposition: attachment; filename=" . $filename );
        header( "Content-Type: application/xml" );
        header( "Content-length: " . strlen( $data ) . "\n\n" );
        echo $data;
        die();
    }
}

/**
 * Custom Import function for Module Manager.
 * 
 * Import selected items given by xmlstring.
 * 
 * @global type $wpdb
 * @global type $iclTranslationManagement
 * @param type $data
 * @param type $_type
 * @return \WP_Error|boolean
 */
function wpcf_admin_import_data_from_xmlstring( $data = '', $_type = 'types' ) {

    global $wpdb, $wpcf;

    /*
     * 
     * TODO Types 1.2.1
     * Merge with wpcf_admin_import_data()
     */

    $result = array(
        'updated' => 0,
        'new' => 0,
        'failed' => 0,
        'errors' => array(),
    );

    libxml_use_internal_errors( true );
    $data = simplexml_load_string( $data );
    if ( !$data ) {
        echo '<div class="message error"><p>' . __( 'Error parsing XML', 'wpcf' ) . '</p></div>';
        foreach ( libxml_get_errors() as $error ) {
            return new WP_Error( 'error_parsing_xml', __( 'Error parsing XML',
                                    'wpcf' ) . ' ' . $error->message );
        }
        libxml_clear_errors();
        return false;
    }
    $errors = array();
    $imported = false;
    // Process groups

    if ( !empty( $data->groups ) && 'groups' == $_type ) {
        $imported = true;

        $groups = array();
        // Set insert data from XML
        foreach ( $data->groups->group as $group ) {
            $group = (array) $group;
            $_id = wpcf_modman_set_submitted_id( _GROUPS_MODULE_MANAGER_KEY_,
                    $group['ID'] );
            if ( !isset( $_POST['items']['groups'][$_id] ) ) {
                continue;
            }
            $group = wpcf_admin_import_export_simplexml2array( $group );
            $groups[$group['ID']] = $group;
        }
        foreach ( $groups as $group_id => $group ) {
            $groups[$group_id]['add'] = true;
            $groups[$group_id]['update'] = false;
        }

        // Insert groups
        foreach ( $groups as $group_id => $group ) {
            $post = array(
                'post_status' => $group['post_status'],
                'post_type' => 'wp-types-group',
                'post_title' => $group['post_title'],
                'post_content' => !empty( $group['post_content'] ) ? $group['post_content'] : '',
            );
            if ( (isset( $group['add'] ) && $group['add'] ) ) {
                $post_to_update = $wpdb->get_var( $wpdb->prepare(
                                "SELECT ID FROM $wpdb->posts
                    WHERE post_title = %s AND post_type = %s",
                                $group['post_title'], 'wp-types-group' ) );
                // Update (may be forced by bulk action)
                if ( $group['update'] || (!empty( $post_to_update )) ) {
                    if ( !empty( $post_to_update ) ) {
                        $post['ID'] = $post_to_update;

                        /*
                         * 
                         * Compare checksum to see if updated
                         */
                        $_checksum = $wpcf->import->checksum( 'group',
                                $post_to_update, $group['checksum'] );

                        $group_wp_id = wp_update_post( $post );
                        if ( !$group_wp_id ) {
                            $errors[] = new WP_Error( 'group_update_failed', sprintf( __( 'Group "%s" update failed',
                                                            'wpcf' ),
                                                    $group['post_title'] ) );
                            $result['errors'][] = sprintf( __( 'Group %s update failed',
                                            'wpcf' ), $group['post_title'] );
                            $result['failed'] += 1;
                        } else {
                            if ( !$_checksum ) {
                                $result['updated'] += 1;
                            } else {
                                
                            }
                        }
                    } else {
                        $errors[] = new WP_Error( 'group_update_failed', sprintf( __( 'Group "%s" update failed',
                                                        'wpcf' ),
                                                $group['post_title'] ) );
                    }
                } else { // Insert
                    $group_wp_id = wp_insert_post( $post, true );
                    if ( is_wp_error( $group_wp_id ) ) {
                        $errors[] = new WP_Error( 'group_insert_failed', sprintf( __( 'Group "%s" insert failed',
                                                        'wpcf' ),
                                                $group['post_title'] ) );
                        $result['errors'][] = sprintf( __( 'Group %s insert failed',
                                        'wpcf' ), $group['post_title'] );
                        $result['failed'] += 1;
                    } else {
                        $result['new'] += 1;
                    }
                }
                // Update meta
                if ( !empty( $group['meta'] ) ) {
                    foreach ( $group['meta'] as $meta_key => $meta_value ) {
                        update_post_meta( $group_wp_id, $meta_key, $meta_value );
                    }
                }
                $group_check[] = $group_wp_id;
                if ( !empty( $post_to_update ) ) {
                    $group_check[] = $post_to_update;
                }
            }
        }

        // Process fields
        if ( !empty( $data->fields ) ) {
            $fields_existing = wpcf_admin_fields_get_fields();
            $fields = array();
            $fields_check = array();
            // Set insert data from XML
            foreach ( $data->fields->field as $field ) {
                $field = wpcf_admin_import_export_simplexml2array( $field );
                $fields[$field['id']] = $field;
            }
            // Insert fields
            foreach ( $fields as $field_id => $field ) {
                if ( (isset( $field['add'] ) && !$field['add']) && !$overwrite_fields ) {
                    continue;
                }
                if ( empty( $field['id'] ) || empty( $field['name'] ) || empty( $field['slug'] ) ) {
                    continue;
                }
                $field_data = array();
                $field_data['id'] = $field['id'];
                $field_data['name'] = $field['name'];
                $field_data['description'] = isset( $field['description'] ) ? $field['description'] : '';
                $field_data['type'] = $field['type'];
                $field_data['slug'] = $field['slug'];
                $field_data['data'] = (isset( $field['data'] ) && is_array( $field['data'] )) ? $field['data'] : array();
                $fields_existing[$field_id] = $field_data;
                $fields_check[] = $field_id;

                // WPML
                global $iclTranslationManagement;
                if ( !empty( $iclTranslationManagement ) && isset( $field['wpml_action'] ) ) {
                    $iclTranslationManagement->settings['custom_fields_translation'][wpcf_types_get_meta_prefix( $field ) . $field_id] = $field['wpml_action'];
                    $iclTranslationManagement->save_settings();
                }
            }
            update_option( 'wpcf-fields', $fields_existing );
        }
    }


    // Process types

    if ( !empty( $data->types ) && 'types' == $_type ) {
        $imported = true;

        $types_existing = get_option( 'wpcf-custom-types', array() );
        $types = array();
        $types_check = array();
        // Set insert data from XML
        foreach ( $data->types->type as $type ) {
            $type = (array) $type;
            $type = wpcf_admin_import_export_simplexml2array( $type );
            $_id = wpcf_modman_set_submitted_id( _TYPES_MODULE_MANAGER_KEY_,
                    $type['id'] );
            if ( !isset( $_POST['items']['types'][$_id] ) ) {
                continue;
            }
            $types[$type['id']] = $type;
        }
        // Insert types
        foreach ( $types as $type_id => $type ) {
            if ( (isset( $type['add'] ) && !$type['add'] ) ) {
                continue;
            }

            if ( isset( $types_existing[$type_id] ) ) {
                /*
                 * 
                 * Compare checksum to see if updated
                 */
                $_checksum = $wpcf->import->checksum( 'custom_post_type',
                        $type_id, $type['checksum'] );

                if ( !$_checksum ) {
                    $result['updated'] += 1;
                }
            } else {
                $result['new'] += 1;
            }

            /*
             * Set type
             */
            unset( $type['add'], $type['update'], $type['checksum'] );
            $types_existing[$type_id] = $type;
            $types_check[] = $type_id;
        }
        update_option( 'wpcf-custom-types', $types_existing );

        // Add relationships
        if ( !empty( $data->post_relationships ) ) {
            $relationship_existing = get_option( 'wpcf_post_relationship',
                    array() );
            foreach ( $data->post_relationships->post_relationship as
                        $relationship ) {
                $relationship = unserialize( $relationship );
                $relationship = array_merge( $relationship_existing,
                        $relationship );
                update_option( 'wpcf_post_relationship', $relationship );
                break;
            }
        }
    }

    // Process taxonomies

    if ( !empty( $data->taxonomies ) && 'taxonomies' == $_type ) {
        $imported = true;

        $taxonomies_existing = get_option( 'wpcf-custom-taxonomies', array() );
        $taxonomies = array();
        $taxonomies_check = array();
        // Set insert data from XML
        foreach ( $data->taxonomies->taxonomy as $taxonomy ) {
            $_id = wpcf_modman_set_submitted_id( _TAX_MODULE_MANAGER_KEY_,
                    $taxonomy['id'] );
            if ( !isset( $_POST['items']['taxonomies'][$_id] ) ) {
                continue;
            }
            $taxonomy = wpcf_admin_import_export_simplexml2array( $taxonomy );
            $taxonomies[$taxonomy['id']] = $taxonomy;
        }
        // Insert taxonomies
        foreach ( $taxonomies as $taxonomy_id => $taxonomy ) {
            if ( (isset( $taxonomy['add'] ) && !$taxonomy['add']) && !$overwrite_tax ) {
                continue;
            }

            if ( isset( $taxonomies_existing[$taxonomy_id] ) ) {
                /*
                 * 
                 * Compare checksum to see if updated
                 */
                $_checksum = $wpcf->import->checksum( 'custom_taxonomy',
                        $taxonomy_id, $taxonomy['checksum'] );
                if ( !$_checksum ) {
                    $result['updated'] += 1;
                }
            } else {
                $result['new'] += 1;
            }

            // Set tax
            unset( $taxonomy['add'], $taxonomy['update'], $taxonomy['checksum'] );
            $taxonomies_existing[$taxonomy_id] = $taxonomy;
            $taxonomies_check[] = $taxonomy_id;
        }
        update_option( 'wpcf-custom-taxonomies', $taxonomies_existing );
    }

    if ( $imported ) {
        // WPML bulk registration
        // TODO WPML move
        if ( wpcf_get_settings( 'register_translations_on_import' ) ) {
            wpcf_admin_bulk_string_translation();
        }

        // Flush rewrite rules
        wpcf_init_custom_types_taxonomies();
        flush_rewrite_rules();
    }

    return $result;
}

/**
 * Checks hash.
 * 
 * @param type $items
 */
function wpcf_modman_items_check_custom_post_types( $items ) {

    global $wpcf;

    foreach ( $items as $k => $item ) {
        $item['exists'] = $wpcf->import->item_exists( 'custom_post_type',
                $item['id'] );
        if ( $item['exists'] && isset( $item['hash'] ) ) {
            $item['is_different'] = $wpcf->import->checksum( 'custom_post_type',
                    $item['id'], $item['hash'] );
        }
        $items[$k] = $item;
    }

    return $items;
}

/**
 * Checks hash.
 * 
 * @param type $items
 */
function wpcf_modman_items_check_groups( $items ) {

    global $wpcf;

    foreach ( $items as $k => $item ) {
        $item['exists'] = $wpcf->import->item_exists( 'group', $item['id'] );
        if ( $item['exists'] && isset( $item['hash'] ) ) {
            $item['is_different'] = $wpcf->import->checksum( 'group',
                    $item['id'], $item['hash'] );
        }
        $items[$k] = $item;
    }

    return $items;
}

/**
 * Checks hash.
 * 
 * @param type $items
 */
function wpcf_modman_items_check_taxonomies( $items ) {

    global $wpcf;

    foreach ( $items as $k => $item ) {
        $item['exists'] = $wpcf->import->item_exists( 'custom_taxonomy',
                $item['id'] );
        if ( $item['exists'] && isset( $item['hash'] ) ) {
            $item['is_different'] = $wpcf->import->checksum( 'custom_taxonomy',
                    $item['id'], $item['hash'] );
        }
        $items[$k] = $item;
    }

    return $items;
}

/**
 * Extracts ID.
 * 
 * @param type $item
 * @return type
 */
function wpcf_modman_get_submitted_id( $set, $item ) {
    return str_replace( '12' . $set . '21', '', $item );
}

/**
 * Sets ID.
 * 
 * @param type $id
 * @return type
 */
function wpcf_modman_set_submitted_id( $set, $id ) {
    return '12' . $set . '21' . $id;
}