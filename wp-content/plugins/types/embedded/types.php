<?php
/*
 * Main file.
 */

// Bootstrap
require_once dirname( __FILE__ ) . '/bootstrap.php';

/**
 * after_setup_theme hook.
 */
function wpcf_embedded_after_setup_theme_hook() {
    $custom_types = get_option( 'wpcf-custom-types', array() );
    if ( !empty( $custom_types ) ) {
        foreach ( $custom_types as $post_type => $data ) {
            if ( !empty( $data['supports']['thumbnail'] ) ) {
                if ( !current_theme_supports( 'post-thumbnails' ) ) {
                    add_theme_support( 'post-thumbnails' );
                    remove_post_type_support( 'post', 'thumbnail' );
                    remove_post_type_support( 'page', 'thumbnail' );
                } else {
                    add_post_type_support( $post_type, 'thumbnail' );
                }
            }
        }
    }
}

/**
 * Inits custom types and taxonomies.
 */
function wpcf_init_custom_types_taxonomies() {
    $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
    if ( !empty( $custom_taxonomies ) ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';
        wpcf_custom_taxonomies_init();
    }
    $custom_types = get_option( 'wpcf-custom-types', array() );
    if ( !empty( $custom_types ) ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
        wpcf_custom_types_init();
    }
}

/**
 * Returns meta_key type for specific field type.
 * 
 * @param type $type
 * @return type 
 */
function types_get_field_type( $type ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $data = wpcf_fields_type_action( $type );
    if ( !empty( $data['meta_key_type'] ) ) {
        return $data['meta_key_type'];
    }
    return 'CHAR';
}

/**
 * Imports settings.
 */
function wpcf_embedded_check_import() {
    if ( file_exists( WPCF_EMBEDDED_ABSPATH . '/settings.php' ) ) {
        require_once WPCF_EMBEDDED_ABSPATH . '/admin.php';
        require_once WPCF_EMBEDDED_ABSPATH . '/settings.php';
        $dismissed = get_option( 'wpcf_dismissed_messages', array() );
        if ( in_array( $timestamp, $dismissed ) ) {
            return false;
        }
        if ( $timestamp > get_option( 'wpcf-types-embedded-import', 0 ) ) {
            if ( !$auto_import ) {
                $link = "<a href=\"" . admin_url( '?types-embedded-import=1&amp;_wpnonce=' . wp_create_nonce( 'embedded-import' ) ) . "\">";
                $text = sprintf( __( 'You have Types import pending. %sClick here to import.%s %sDismiss message.%s',
                                'wpcf' ), $link, '</a>',
                        "<a onclick=\"jQuery(this).parent().parent().fadeOut();\" class=\"wpcf-ajax-link\" href=\""
                        . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=dismiss_message&amp;id='
                                . $timestamp . '&amp;_wpnonce=' . wp_create_nonce( 'dismiss_message' ) ) . "\">",
                        '</a>' );
                wpcf_admin_message( $text );
            }
            if ( $auto_import || (isset( $_GET['types-embedded-import'] ) && isset( $_GET['_wpnonce'] )
                    && wp_verify_nonce( $_GET['_wpnonce'], 'embedded-import' )) ) {
                if ( file_exists( WPCF_EMBEDDED_ABSPATH . '/settings.xml' ) ) {
                    $_POST['overwrite-groups'] = 1;
                    $_POST['overwrite-fields'] = 1;
                    $_POST['overwrite-types'] = 1;
                    $_POST['overwrite-tax'] = 1;
//                    $_POST['delete-groups'] = 0;
//                    $_POST['delete-fields'] = 0;
//                    $_POST['delete-types'] = 0;
//                    $_POST['delete-tax'] = 0;
                    $_POST['post_relationship'] = 1;
                    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
                    require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';
                    $data = @file_get_contents( WPCF_EMBEDDED_ABSPATH . '/settings.xml' );
                    wpcf_admin_import_data( $data, false );
                    update_option( 'wpcf-types-embedded-import', $timestamp );
                    wp_redirect( admin_url() );
                } else {
                    $code = __( 'settings.xml file missing', 'wpcf' );
                    wpcf_admin_message( $code, 'error' );
                }
            }
        }
    }
}

/**
 * Display information about upgrading to the plugin version of types.
 *
 */
function wpcf_promote_types_admin() {
    $custom_types = get_option( 'wpcf-custom-types', array() );

    ?>

    <?php
    if ( sizeof( $custom_types ) > 0 ) {
        echo '<p>' . __( 'Types creates Custom Post Types. These are user-defined WordPress content types. On  your theme the following Types are defined:',
                'wpcf' ) . "</p>\n";
        echo "<ul style='margin-left:20px;'>\n";
        foreach ( $custom_types as $type ) {
            echo "<li>" . $type['labels']['name'] . "</li>\n";
        }
        echo "</ul>\n";
    }

    ?>
    <p><?php
    echo sprintf( __( 'If you want to edit these or create your own you can download the full version of <strong>Types</strong> from <a href="%s">%s</a>',
                    'wpcf' ), 'http://wordpress.org/extend/plugins/types/',
            'http://wordpress.org/extend/plugins/types/' );

    ?></p>

    <?php
}

/**
 * Actions for outside fields control.
 * 
 * @param type $action 
 */
function wpcf_types_cf_under_control( $action = 'add', $args = array() ) {
    global $wpcf_types_under_control;
    $wpcf_types_under_control['errors'] = array();
    switch ( $action ) {
        case 'add':
            $fields = wpcf_admin_fields_get_fields();
            foreach ( $args['fields'] as $field_id ) {
                $field_type = !empty( $args['type'] ) ? $args['type'] : 'textfield';
                if ( strpos( $field_id, md5( 'wpcf_not_controlled' ) ) !== false ) {
                    $field_id_add = str_replace( '_' . md5( 'wpcf_not_controlled' ),
                            '', $field_id );
                    // Activating field that previously existed in Types
                    if ( array_key_exists( $field_id_add, $fields ) ) {
                        $fields[$field_id_add]['data']['disabled'] = 0;
                    } else { // Adding from outside
                        $fields[$field_id_add]['id'] = $field_id_add;
                        $fields[$field_id_add]['type'] = $field_type;
                        $fields[$field_id_add]['name'] = $field_id_add;
                        $fields[$field_id_add]['slug'] = $field_id_add;
                        $fields[$field_id_add]['description'] = '';
                        $fields[$field_id_add]['data'] = array();
                        // @TODO WATCH THIS! MUST NOT BE DROPPED IN ANY CASE
                        $fields[$field_id_add]['data']['controlled'] = 1;
                    }
                    $unset_key = array_search( $field_id, $args['fields'] );
                    if ( $unset_key !== false ) {
                        unset( $args['fields'][$unset_key] );
                        $args['fields'][$unset_key] = $field_id_add;
                    }
                }
            }
            wpcf_admin_fields_save_fields( $fields, true );
            return $args['fields'];
            break;

        case 'check_exists':
            $fields = wpcf_admin_fields_get_fields();
            $field = $args;
            if ( array_key_exists( $field, $fields ) && empty( $fields[$field]['data']['disabled'] ) ) {
                return true;
            }
            return false;
            break;

        case 'check_outsider':
            $fields = wpcf_admin_fields_get_fields();
            $field = $args;
            if ( array_key_exists( $field, $fields ) && !empty( $fields[$field]['data']['controlled'] ) ) {
                return true;
            }
            return false;
            break;

        default:
            break;
    }
}

/**
 * Controlls meta prefix.
 * 
 * @param array $field
 */
function wpcf_types_get_meta_prefix( $field = array() ) {
    if ( empty( $field ) ) {
        return WPCF_META_PREFIX;
    }
    if ( !empty( $field['data']['controlled'] ) ) {
        return '';
    }
    return WPCF_META_PREFIX;
}

/**
 * Compares WP versions
 * @global type $wp_version
 * @param type $version
 * @param type $operator
 * @return type 
 */
function wpcf_compare_wp_version( $version = '3.2.1', $operator = '>' ) {
    global $wp_version;
    return version_compare( $wp_version, $version, $operator );
}

/**
 * Gets post type with data to which belongs.
 * 
 * @param type $post_type
 * @return type 
 */
function wpcf_pr_get_belongs( $post_type ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    return wpcf_pr_admin_get_belongs( $post_type );
}

/**
 * Gets all post types and data that owns.
 * 
 * @param type $post_type
 * @return type 
 */
function wpcf_pr_get_has( $post_type ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    return wpcf_pr_admin_get_has( $post_type );
}

/**
 * Gets individual post ID to which queried post belongs.
 * 
 * @param type $post_id
 * @param type $post_type Post type of owner
 * @return type 
 */
function wpcf_pr_post_get_belongs( $post_id, $post_type ) {
    return get_post_meta( $post_id, '_wpcf_belongs_' . $post_type . '_id', true );
}

/**
 * Gets all posts that belong to queried post, grouped by post type.
 * 
 * @param type $post_id
 * @param type $post_type
 * @return type 
 */
function wpcf_pr_post_get_has( $post_id, $post_type_q = null ) {
    $post_type = get_post_type( $post_id );
    $has = array_keys( wpcf_pr_get_has( $post_type ) );
    $add = is_null( $post_type_q ) ? '&post_type=any' : '&post_type=' . $post_type_q;
    $posts = get_posts( 'numberposts=-1&post_status=null&meta_key=_wpcf_belongs_'
            . $post_type . '_id&meta_value=' . $post_id . $add );

    $results = array();
    foreach ( $posts as $post ) {
        if ( !in_array( $post->post_type, $has ) ) {
            continue;
        }
        $results[$post->post_type][] = $post;
    }
    return is_null( $post_type_q ) ? $results : array_shift( $results );
}

/**
 * Gets posts that belongs to current post.
 * 
 * @global type $post
 * @param type $post_type
 * @param type $args
 * @return type 
 */
function types_child_posts( $post_type, $args = array() ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
    global $post, $wp_post_types;
    // WP allows querying inactive post types
    if ( !isset( $wp_post_types[$post_type] )
            || !$wp_post_types[$post_type]->publicly_queryable ) {
        return array();
    }
    $defaults = array(
        'post_type' => $post_type,
        'numberposts' => -1,
        'post_status' => null,
        'meta_key' => '_wpcf_belongs_' . $post->post_type . '_id',
        'meta_value' => $post->ID,
        'suppress_filters' => false,
    );
    $args = wp_parse_args( $args, $defaults );
    $args = apply_filters( 'types_child_posts_args', $args );
    $child_posts = get_posts( $args );
    foreach ( $child_posts as $child_post_key => $child_post ) {
        if ( $child_posts[$child_post_key]->post_status == 'trash' ) {
            unset( $child_posts[$child_post_key] );
            continue;
        }
        $child_posts[$child_post_key]->fields = array();
        $groups = wpcf_admin_post_get_post_groups_fields( $child_post );
        foreach ( $groups as $group ) {
            if ( !empty( $group['fields'] ) ) {
                // Process fields
                foreach ( $group['fields'] as $k => $field ) {
                    if ( isset( $field['data']['repetitive'] ) && $field['data']['repetitive'] )
                        $child_posts[$child_post_key]->fields[$k] = get_post_meta( $child_post->ID,
                                wpcf_types_get_meta_prefix( $field ) . $field['slug'],
                                false ); // get all field instances
                    else
                        $child_posts[$child_post_key]->fields[$k] = get_post_meta( $child_post->ID,
                                wpcf_types_get_meta_prefix( $field ) . $field['slug'],
                                true ); // get single field instance



















                        
// handle checkboxes which are one value serialized
                    if ( $field['type'] == 'checkboxes' && isset( $child_posts[$child_post_key]->fields[$k] ) )
                        $child_posts[$child_post_key]->fields[$k] = maybe_unserialize( $child_posts[$child_post_key]->fields[$k] );
                }
            }
        }
    }
    return $child_posts;
}

/**
 * Gets settings.
 */
function wpcf_get_settings( $specific = false ) {
    $defaults = array(
        'add_resized_images_to_library' => 0,
        'register_translations_on_import' => 1,
        'images_remote' => 0,
        'images_remote_cache_time' => '36',
        'help_box' => 'by_types',
    );
    $settings = wp_parse_args( get_option( 'wpcf_settings', array() ), $defaults );
    $settings = apply_filters( 'types_settings', $settings );
    if ( $specific ) {
        return isset( $settings[$specific] ) ? $settings[$specific] : false;
    }
    return $settings;
}

/**
 * Saves settings.
 */
function wpcf_save_settings( $settings ) {
    update_option( 'wpcf_settings', $settings );
}

/**
 * Check if it can be repetitive
 * @param type $field
 * @return type 
 */
function wpcf_admin_can_be_repetitive( $type ) {
    return !in_array( $type,
                    array('checkbox', 'checkboxes', 'wysiwyg', 'radio', 'select') );
}

/**
 * Check if field is repetitive
 * @param type $type
 * @return type 
 */
function wpcf_admin_is_repetitive( $field ) {
    if ( !isset( $field['data']['repetitive'] ) || !isset( $field['type'] ) ) {
        return false;
    }
    $check = intval( $field['data']['repetitive'] );
    return !empty( $check )
            && wpcf_admin_can_be_repetitive( $field['type'] );
}

/**
 * Returns unique ID.
 * 
 * @staticvar array $cache
 * @param type $cache_key
 * @return type 
 */
function wpcf_unique_id( $cache_key ) {
    $cache_key = md5( strval( $cache_key ) . strval( time() ) );
    static $cache = array();
    if ( !isset( $cache[$cache_key] ) ) {
        $cache[$cache_key] = 1;
    } else {
        $cache[$cache_key] += 1;
    }
    return $cache_key . '-' . $cache[$cache_key];
}

/**
 * Determine if platform is Win
 * 
 * @return type
 */
function wpcf_is_windows() {
    return (PHP_OS == "WIN32" || PHP_OS == "WINNT");
}

/**
 * Parses array as string
 * 
 * @param type $array
 */
function wpcf_parse_array_to_string( $array ) {
    $s = '';
    foreach ( (array) $array as $param => $value ) {
        $s .= strval( $param ) . '=' . urlencode( strval( $value ) ) . '&';
    }
    return trim( $s, '&' );
}

/**
 * Get main post ID.
 * 
 * @param type $context
 * @return type
 */
function wpcf_get_post_id( $context = 'group' ) {
    if ( !is_admin() ) {
        return get_the_ID();
    }
    /*
     * TODO Explore possible usage for $context
     */
    $post = wpcf_admin_get_edited_post();
    return empty( $post->ID ) ? -1 : $post->ID;
}

/**
 * Basic scripts
 */
function wpcf_enqueue_scripts() {

    if ( !wpcf_is_embedded() ) {
        /*
         * 
         * Basic JS
         */
        wp_enqueue_script( 'wpcf-js', WPCF_RES_RELPATH . '/js/basic.js',
                array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-tabs'),
                WPCF_VERSION );
        /*
         * 
         * Basic CSS
         */
        wp_enqueue_style( 'wpcf-css', WPCF_RES_RELPATH . '/css/basic.css',
                array(), WPCF_VERSION );

        /*
         * Settings
         */
        // _nonce for editor callback
        wpcf_admin_add_js_settings( 'wpcfEditorCallbackNonce',
                wp_create_nonce( 'editor_callback' ) );
        // Thickbox generic title
        wpcf_admin_add_js_settings( 'wpcfEditorTbTitle',
                esc_js( __( 'Insert field', 'wpcf' ) ) );
    }
    /*
     * 
     * Basic JS
     */
    wp_enqueue_script( 'wpcf-js-embedded',
            WPCF_EMBEDDED_RES_RELPATH . '/js/basic.js',
            array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-tabs'),
            WPCF_VERSION );
    /*
     * 
     * Basic CSS
     */
    wp_enqueue_style( 'wpcf-css-embedded',
            WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(), WPCF_VERSION );

    /*
     * 
     * Other components
     */

    // Repetitive
    wp_enqueue_script(
            'wpcf-repeater', WPCF_EMBEDDED_RES_RELPATH . '/js/repetitive.js',
            array('wpcf-js-embedded'), WPCF_VERSION
    );
    wp_enqueue_style(
            'wpcf-repeater', WPCF_EMBEDDED_RES_RELPATH . '/css/repetitive.css',
            array('wpcf-css-embedded'), WPCF_VERSION
    );

    // Conditional
    wp_enqueue_script( 'wpcf-conditional',
            WPCF_EMBEDDED_RES_RELPATH . '/js/conditional.js',
            array('wpcf-js-embedded'), WPCF_VERSION );
    wpcf_admin_add_js_settings( 'wpcfConditionalVerify_nonce',
            wp_create_nonce( 'cd_verify' )
    );
}

/**
 * 
 * @since 1.2.1
 * @todo Make loading JS more clear for all components.
 */
function wpcf_edit_post_screen_scripts() {

    wpcf_enqueue_scripts();

    wp_enqueue_script( 'wpcf-fields-post',
            WPCF_EMBEDDED_RES_RELPATH . '/js/fields-post.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_style( 'wpcf-fields-post',
            WPCF_EMBEDDED_RES_RELPATH . '/css/fields-post.css', array(),
            WPCF_VERSION );

    wp_enqueue_script( 'wpcf-form-validation',
            WPCF_EMBEDDED_RES_RELPATH . '/js/'
            . 'jquery-form-validation/jquery.validate.min.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation-additional',
            WPCF_EMBEDDED_RES_RELPATH . '/js/'
            . 'jquery-form-validation/additional-methods.min.js',
            array('jquery'), WPCF_VERSION );
}

/**
 * Check if running embedded version.
 * 
 * @return type
 */
function wpcf_is_embedded() {
    return defined( 'WPCF_RUNNING_EMBEDDED' ) && WPCF_RUNNING_EMBEDDED;
}

// Local debug
if ( ($_SERVER['SERVER_NAME'] == '192.168.1.2' || $_SERVER['SERVER_NAME'] == 'localhost') && !function_exists( 'debug' ) ) {

    function debug( $data, $die = true ) {
        echo '<pre>';
        print_r( $data );
        echo '</pre>';
        if ( $die )
            die();
    }

}