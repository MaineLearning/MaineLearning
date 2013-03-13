<?php
/*
 * Admin functions
 */
require_once WPCF_ABSPATH . '/marketing.php';
/*
 * 
 * 
 * 
 * This needs to be called after main 'init' hook.
 * Main init hook calls required Types code for frontend.
 * Admin init hook only in admin area.
 * 
 * TODO Revise it to change to 'admin_init'
 */
add_action( 'admin_init', 'wpcf_admin_init_hook', 11 );
add_action( 'admin_menu', 'wpcf_admin_menu_hook' );
add_action( 'wpcf_admin_page_init', 'wpcf_enqueue_scripts' );

wpcf_admin_load_teasers( array('types-access.php') );
if ( defined( 'DOING_AJAX' ) ) {
    require_once WPCF_INC_ABSPATH . '/ajax.php';
}

/**
 * admin_init hook.
 */
function wpcf_admin_init_hook() {
    wpcf_types_plugin_redirect();
    wp_enqueue_style( 'wpcf-promo-tabs',
            WPCF_EMBEDDED_RES_RELPATH . '/css/tabs.css', array(), WPCF_VERSION );
    /* wp_enqueue_style('wpcf-promo-tabs',
      WPCF_RES_RELPATH . '/css/tabs.css', array(), WPCF_VERSION); */
}

/**
 * admin_menu hook.
 */
function wpcf_admin_menu_hook() {
    add_menu_page( 'Types', 'Types', 'manage_options', 'wpcf',
            'wpcf_admin_menu_summary', WPCF_RES_RELPATH . '/images/logo-16.png' );

    // Custom fields
    $hook = add_submenu_page( 'wpcf', __( 'Custom Fields', 'wpcf' ),
            __( 'Custom Fields', 'wpcf' ), 'manage_options', 'wpcf-cf',
            'wpcf_admin_menu_summary' );
    wpcf_admin_plugin_help( $hook, 'wpcf-cf' );
    add_action( 'load-' . $hook, 'wpcf_admin_menu_summary_hook' );
    // Custom types and tax
    $hook = add_submenu_page( 'wpcf',
            __( 'Custom Types and Taxonomies', 'wpcf' ),
            __( 'Custom Types and Taxonomies', 'wpcf' ), 'manage_options',
            'wpcf-ctt', 'wpcf_admin_menu_summary_ctt' );
    add_action( 'load-' . $hook, 'wpcf_admin_menu_summary_ctt_hook' );
    wpcf_admin_plugin_help( $hook, 'wpcf-ctt' );
    // Import/Export
    $hook = add_submenu_page( 'wpcf', __( 'Import/Export', 'wpcf' ),
            __( 'Import/Export', 'wpcf' ), 'manage_options',
            'wpcf-import-export', 'wpcf_admin_menu_import_export' );
    add_action( 'load-' . $hook, 'wpcf_admin_menu_import_export_hook' );
    wpcf_admin_plugin_help( $hook, 'wpcf-import-export' );
    // Custom Fields Control
    $hook = add_submenu_page( 'wpcf', __( 'Custom Fields Control', 'wpcf' ),
            __( 'Custom Fields Control', 'wpcf' ), 'manage_options',
            'wpcf-custom-fields-control',
            'wpcf_admin_menu_custom_fields_control' );
    add_action( 'load-' . $hook, 'wpcf_admin_menu_custom_fields_control_hook' );
    wpcf_admin_plugin_help( $hook, 'wpcf-custom-fields-control' );
    // Settings
    $hook = add_submenu_page( 'wpcf', __( 'Settings', 'wpcf' ),
            __( 'Settings', 'wpcf' ), 'manage_options', 'wpcf-custom-settings',
            'wpcf_admin_menu_settings' );
    add_action( 'load-' . $hook, 'wpcf_admin_menu_settings_hook' );

    if ( isset( $_GET['page'] ) ) {
        switch ( $_GET['page'] ) {
            case 'wpcf-edit':
                $title = isset( $_GET['group_id'] ) ? __( 'Edit Group', 'wpcf' ) : __( 'Add New Group',
                                'wpcf' );
                $hook = add_submenu_page( 'wpcf', $title, $title,
                        'manage_options', 'wpcf-edit',
                        'wpcf_admin_menu_edit_fields' );
                add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_fields_hook' );
                wpcf_admin_plugin_help( $hook, 'wpcf-edit' );
                break;

            case 'wpcf-edit-type':
                $title = isset( $_GET['wpcf-post-type'] ) ? __( 'Edit Custom Post Type',
                                'wpcf' ) : __( 'Add New Custom Post Type',
                                'wpcf' );
                $hook = add_submenu_page( 'wpcf', $title, $title,
                        'manage_options', 'wpcf-edit-type',
                        'wpcf_admin_menu_edit_type' );
                add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_type_hook' );
                wpcf_admin_plugin_help( $hook, 'wpcf-edit-type' );
                break;

            case 'wpcf-edit-tax':
                $title = isset( $_GET['wpcf-tax'] ) ? __( 'Edit Taxonomy',
                                'wpcf' ) : __( 'Add New Taxonomy', 'wpcf' );
                $hook = add_submenu_page( 'wpcf', $title, $title,
                        'manage_options', 'wpcf-edit-tax',
                        'wpcf_admin_menu_edit_tax' );
                add_action( 'load-' . $hook, 'wpcf_admin_menu_edit_tax_hook' );
                wpcf_admin_plugin_help( $hook, 'wpcf-edit-tax' );
                break;
        }
    }

    // Check if migration from other plugin is needed
    if ( class_exists( 'Acf' ) || defined( 'CPT_VERSION' ) ) {
        $hook = add_submenu_page( 'wpcf', __( 'Migration', 'wpcf' ),
                __( 'Migration', 'wpcf' ), 'manage_options', 'wpcf-migration',
                'wpcf_admin_menu_migration' );
        add_action( 'load-' . $hook, 'wpcf_admin_menu_migration_hook' );
        wpcf_admin_plugin_help( $hook, 'wpcf-migration' );
    }

    do_action( 'wpcf_menu_plus' );

    // Introduction
    $hook = add_submenu_page( 'wpcf', __( 'Help', 'wpcf' ),
            __( 'Help', 'wpcf' ), 'manage_options', 'wpcf-help',
            'wpcf_admin_menu_introduction' );
    wpcf_admin_plugin_help( $hook, 'wpcf' );
    add_action( 'load-' . $hook, 'wpcf_admin_menu_introduction_hook' );

    // remove the repeating Types submenu
    remove_submenu_page( 'wpcf', 'wpcf' );
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_introduction_hook() {
    do_action( 'wpcf_admin_page_init' );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_introduction() {
    require_once WPCF_INC_ABSPATH . '/introduction.php';
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_summary_hook() {
    do_action( 'wpcf_admin_page_init' );
    wpcf_admin_load_collapsible();
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_summary() {
    echo wpcf_add_admin_header( __( 'Custom Fields', 'wpcf' ) );
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/fields-list.php';
    $to_display = wpcf_admin_fields_get_fields();
    if ( !empty( $to_display ) ) {
        add_action( 'wpcf_groups_list_table_after',
                'wpcf_admin_promotional_text' );
    }
    wpcf_admin_fields_list();
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_edit_fields_hook() {
    do_action( 'wpcf_admin_page_init' );

    /*
     * Enqueue scripts
     */
    // Group filter
    wp_enqueue_script( 'wpcf-filter-js',
            WPCF_EMBEDDED_RES_RELPATH
            . '/js/custom-fields-form-filter.js', array('jquery'), WPCF_VERSION );
    // Form
    wp_enqueue_script( 'wpcf-form-validation',
            WPCF_EMBEDDED_RES_RELPATH . '/js/'
            . 'jquery-form-validation/jquery.validate.min.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation-additional',
            WPCF_EMBEDDED_RES_RELPATH . '/js/'
            . 'jquery-form-validation/additional-methods.min.js',
            array('jquery'), WPCF_VERSION );
    // Scroll
    wp_enqueue_script( 'wpcf-scrollbar',
            WPCF_EMBEDDED_RELPATH . '/common/visual-editor/res/js/scrollbar.js',
            array('jquery') );
    wp_enqueue_script( 'wpcf-mousewheel',
            WPCF_EMBEDDED_RELPATH . '/common/visual-editor/res/js/mousewheel.js',
            array('wpcf-scrollbar') );
    // MAIN
    wp_enqueue_script( 'wpcf-fields-form',
            WPCF_EMBEDDED_RES_RELPATH
            . '/js/fields-form.js', array('wpcf-js') );

    /*
     * Enqueue styles
     */
    wp_enqueue_style( 'wpcf-scroll',
            WPCF_EMBEDDED_RELPATH . '/common/visual-editor/res/css/scroll.css' );

    add_action( 'admin_footer', 'wpcf_admin_fields_form_js_validation' );
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/fields-form.php';
    $form = wpcf_admin_fields_form();
    wpcf_form( 'wpcf_form_fields', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_edit_fields() {
    if ( isset( $_GET['group_id'] ) ) {
        $title = __( 'Edit Group', 'wpcf' );
    } else {
        $title = __( 'Add New Group', 'wpcf' );
    }
    echo wpcf_add_admin_header( $title );

    $form = wpcf_form( 'wpcf_form_fields' );
    echo '<br /><form method="post" action="" class="wpcf-fields-form '
    . 'wpcf-form-validate" onsubmit="';
    echo 'if (jQuery(\'#wpcf-group-name\').val() == \'' . __( 'Enter group title',
            'wpcf' ) . '\') { jQuery(\'#wpcf-group-name\').val(\'\'); }';
    echo 'if (jQuery(\'#wpcf-group-description\').val() == \'' . __( 'Enter a description for this group',
            'wpcf' ) . '\') { jQuery(\'#wpcf-group-description\').val(\'\'); }';
    echo 'jQuery(\'.wpcf-forms-set-legend\').each(function(){
        if (jQuery(this).val() == \'' . __( 'Enter field name',
            'wpcf' ) . '\') {
            jQuery(this).val(\'\');
        }
        if (jQuery(this).next().val() == \'' . __( 'Enter field slug',
            'wpcf' ) . '\') {
            jQuery(this).next().val(\'\');
        }
        if (jQuery(this).next().next().val() == \'' . __( 'Describe this field',
            'wpcf' ) . '\') {
            jQuery(this).next().next().val(\'\');
        }
});';
    echo '">';
    echo $form->renderForm();
    echo '</form>';
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_summary_ctt_hook() {
    do_action( 'wpcf_admin_page_init' );
    wp_enqueue_style( 'wpcf-promo-tabs', WPCF_RES_RELPATH . '/css/tabs.css',
            array(), WPCF_VERSION );
    wpcf_admin_load_collapsible();
    require_once WPCF_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
    require_once WPCF_INC_ABSPATH . '/custom-types-taxonomies-list.php';
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_summary_ctt() {
    echo wpcf_add_admin_header( __( 'Custom Post Types and Taxonomies', 'wpcf' ) );
    $to_display_posts = get_option( 'wpcf-custom-types', array() );
    $to_display_tax = get_option( 'wpcf-custom-taxonomies', array() );
    if ( !empty( $to_display_posts ) || !empty( $to_display_tax ) ) {
        add_action( 'wpcf_types_tax_list_table_after',
                'wpcf_admin_promotional_text' );
    }
    wpcf_admin_ctt_list();
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_edit_type_hook() {
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_INC_ABSPATH . '/custom-types-form.php';
    require_once WPCF_INC_ABSPATH . '/post-relationship.php';
    wp_enqueue_script( 'wpcf-custom-types-form',
            WPCF_RES_RELPATH . '/js/'
            . 'custom-types-form.js', array('jquery'), WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/jquery.validate.min.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation-additional',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/additional-methods.min.js',
            array('jquery'), WPCF_VERSION );
    add_action( 'admin_footer', 'wpcf_admin_types_form_js_validation' );
    wpcf_post_relationship_init();
    $form = wpcf_admin_custom_types_form();
    wpcf_form( 'wpcf_form_types', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_edit_type() {
    if ( isset( $_GET['wpcf-post-type'] ) ) {
        $title = __( 'Edit Custom Post Type', 'wpcf' );
    } else {
        $title = __( 'Add New Custom Post Type', 'wpcf' );
    }
    echo wpcf_add_admin_header( $title );
    $form = wpcf_form( 'wpcf_form_types' );
    echo '<br /><form method="post" action="" class="wpcf-types-form '
    . 'wpcf-form-validate">';
    echo $form->renderForm();
    echo '</form>';
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_edit_tax_hook() {
    do_action( 'wpcf_admin_page_init' );
    wp_enqueue_script( 'wpcf-form-validation',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/jquery.validate.min.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_script( 'wpcf-form-validation-additional',
            WPCF_RES_RELPATH . '/js/'
            . 'jquery-form-validation/additional-methods.min.js',
            array('jquery'), WPCF_VERSION );
    add_action( 'admin_footer', 'wpcf_admin_tax_form_js_validation' );
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';
    require_once WPCF_INC_ABSPATH . '/custom-taxonomies-form.php';
    $form = wpcf_admin_custom_taxonomies_form();
    wpcf_form( 'wpcf_form_tax', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_edit_tax() {
    if ( isset( $_GET['wpcf-tax'] ) ) {
        $title = __( 'Edit Taxonomy', 'wpcf' );
    } else {
        $title = __( 'Add New Taxonomy', 'wpcf' );
    }
    echo wpcf_add_admin_header( $title );
    $form = wpcf_form( 'wpcf_form_tax' );
    echo '<br /><form method="post" action="" class="wpcf-tax-form '
    . 'wpcf-form-validate">';
    echo $form->renderForm();
    echo '</form>';
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_import_export_hook() {
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/import-export.php';
    if ( extension_loaded( 'simplexml' ) && isset( $_POST['export'] )
            && wp_verify_nonce( $_POST['_wpnonce'], 'wpcf_import' ) ) {
        wpcf_admin_export_data();
        die();
    }
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_import_export() {
    echo wpcf_add_admin_header( __( 'Import/Export', 'wpcf' ) );
    echo '<br /><form method="post" action="" class="wpcf-import-export-form '
    . 'wpcf-form-validate" enctype="multipart/form-data">';
    echo wpcf_form_simple( wpcf_admin_import_export_form() );
    echo '</form>';
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_custom_fields_control_hook() {
    do_action( 'wpcf_admin_page_init' );
    add_action( 'admin_head', 'wpcf_admin_custom_fields_control_js' );
    add_thickbox();
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/fields-control.php';

    if ( isset( $_REQUEST['_wpnonce'] )
            && wp_verify_nonce( $_REQUEST['_wpnonce'],
                    'custom_fields_control_bulk' )
            && (isset( $_POST['action'] ) || isset( $_POST['action2'] )) && !empty( $_POST['fields'] ) ) {
        $action = $_POST['action'] == '-1' ? $_POST['action2'] : $_POST['action'];
        wpcf_admin_custom_fields_control_bulk_actions( $action );
    }

    global $wpcf_control_table;
    $wpcf_control_table = new WPCF_Custom_Fields_Control_Table( array(
                'ajax' => true,
                'singular' => __( 'Custom Field', 'wpcf' ),
                'plural' => __( 'Custom Fields', 'wpcf' ),
                    ) );
    $wpcf_control_table->prepare_items();
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_custom_fields_control() {
    global $wpcf_control_table;
    echo wpcf_add_admin_header( __( 'Custom Fields Control', 'wpcf' ) );
    echo '<br /><form method="post" action="" id="wpcf-custom-fields-control-form" class="wpcf-custom-fields-control-form '
    . 'wpcf-form-validate" enctype="multipart/form-data">';
    echo wpcf_admin_custom_fields_control_form( $wpcf_control_table );
    wp_nonce_field( 'custom_fields_control_bulk' );
    echo '</form>';
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_migration_hook() {
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_INC_ABSPATH . '/fields.php';
    require_once WPCF_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
    require_once WPCF_INC_ABSPATH . '/migration.php';
    $form = wpcf_admin_migration_form();
    wpcf_form( 'wpcf_form_migration', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_migration() {
    echo wpcf_add_admin_header( __( 'Migration', 'wpcf' ) );
    echo '<br /><form method="post" action="" id="wpcf-migration-form" class="wpcf-migration-form '
    . 'wpcf-form-validate" enctype="multipart/form-data">';
    $form = wpcf_form( 'wpcf_form_migration' );
    echo $form->renderForm();
    echo '</form>';
    echo wpcf_add_admin_footer();
}

/**
 * Menu page hook.
 */
function wpcf_admin_menu_settings_hook() {
    do_action( 'wpcf_admin_page_init' );
    require_once WPCF_INC_ABSPATH . '/settings.php';
    $form = wpcf_admin_image_settings_form();
    wpcf_form( 'wpcf_form_image_settings', $form );
    $form = wpcf_admin_general_settings_form();
    wpcf_form( 'wpcf_form_general_settings', $form );
}

/**
 * Menu page display.
 */
function wpcf_admin_menu_settings() {
    ob_start();
    echo wpcf_add_admin_header( __( 'Settings', 'wpcf' ) );

    ?>
    <p style="font-weight: bold;"><?php
    _e( 'This screen contains the Types settings for your site.', 'wpcf' );

    ?></p>
    <ul class="horlist">
        <li><a href="#types-image-settings"><?php
    _e( 'Image Settings', 'wpcf' );

    ?></a></li>
        <li><a href="#types-general-settings"><?php
            _e( 'General Setings', 'wpcf' );

    ?></a></li>
    </ul>
    <br style='clear:both'/><br /><br />  
    <a id="types-image-settings"></a>
    <table class="widefat" id="types_image_settings_table">
        <thead>
            <tr>
                <th><?php
            _e( 'Image Settings', 'wpcf' );

    ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <?php
                    echo '<br /><form method="post" action="" id="wpcf-image-settings-form" class="wpcf-settings-form '
                    . 'wpcf-form-validate">';
                    $form = wpcf_form( 'wpcf_form_image_settings' );
                    echo $form->renderForm();
                    echo '</form>';

                    ?>
                </td>
            </tr>
        </tbody>
    </table>  
    <br /><br />
    <a id="types-general-settings"></a>
    <table class="widefat" id="types_general_settings_table">
        <thead>
            <tr>
                <th><?php
                _e( 'General Settings', 'wpcf' );

                    ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <?php
                    echo '<br /><form method="post" action="" id="wpcf-general-settings-form" class="wpcf-settings-form '
                    . 'wpcf-form-validate">';
                    $form = wpcf_form( 'wpcf_form_general_settings' );
                    echo $form->renderForm();
                    echo '</form>';

                    ?>
                </td>
            </tr>
        </tbody>
    </table>  
    <?php
    echo wpcf_add_admin_footer();

    echo ob_get_clean();
}

/**
 * Adds typical header on admin pages.
 *
 * @param string $title
 * @param string $icon_id Custom icon
 * @return string
 */
function wpcf_add_admin_header( $title, $icon_id = 'icon-wpcf' ) {
    echo "\r\n" . '<div class="wrap">
	<div id="' . $icon_id . '" class="icon32"><br /></div>
    <h2>' . $title . '</h2>' . "\r\n";
    do_action( 'wpcf_admin_header' );
    do_action( 'wpcf_admin_header_' . $_GET['page'] );
}

/**
 * Adds footer on admin pages.
 *
 * <b>Strongly recomended</b> if wpcf_add_admin_header() is called before.
 * Otherwise invalid HTML formatting will occur.
 */
function wpcf_add_admin_footer() {
    do_action( 'wpcf_admin_footer_' . $_GET['page'] );
    do_action( 'wpcf_admin_footer' );
    echo "\r\n" . '</div>' . "\r\n";
}

/**
 * Returns HTML formatted 'widefat' table.
 * 
 * @param type $ID
 * @param type $header
 * @param type $rows
 * @param type $empty_message 
 */
function wpcf_admin_widefat_table( $ID, $header, $rows = array(),
        $empty_message = 'No results' ) {
    $head = '';
    $footer = '';
    foreach ( $header as $key => $value ) {
        $head .= '<th id="wpcf-table-' . $key . '">' . $value . '</th>' . "\r\n";
        $footer .= '<th>' . $value . '</th>' . "\r\n";
    }
    echo '<table id="' . $ID . '" class="widefat" cellspacing="0">
            <thead>
                <tr>
                  ' . $head . '
                </tr>
            </thead>
            <tfoot>
                <tr>
                  ' . $footer . '
                </tr>
            </tfoot>
            <tbody>
              ';
    $row = '';
    if ( empty( $rows ) ) {
        echo '<tr><td colspan="' . count( $header ) . '">' . $empty_message
        . '</td></tr>';
    } else {
        foreach ( $rows as $row ) {
            echo '<tr>';
            foreach ( $row as $column_name => $column_value ) {
                echo '<td class="wpcf-table-column-' . $column_name . '">';
                echo $column_value;
                echo '</td>' . "\r\n";
            }
            echo '</tr>' . "\r\n";
        }
    }
    echo '
            </tbody>
          </table>' . "\r\n";
}

/**
 * Admin tabs.
 * 
 * @param type $tabs
 * @param type $page
 * @param type $default
 * @param type $current
 * @return string 
 */
function wpcf_admin_tabs( $tabs, $page, $default = '', $current = '' ) {
    if ( empty( $current ) && isset( $_GET['tab'] ) ) {
        $current = $_GET['tab'];
    } else {
        $current = $default;
    }
    $output = '<h2 class="nav-tab-wrapper">';
    foreach ( $tabs as $tab => $name ) {
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        $output .= "<a class='nav-tab$class' href='?page=$page&tab=$tab'>$name</a>";
    }
    $output .= '</h2>';
    return $output;
}

/**
 * Saves open fieldsets.
 * 
 * @param type $action
 * @param type $fieldset
 */
function wpcf_admin_form_fieldset_save_toggle( $action, $fieldset ) {
    $data = get_user_meta( get_current_user_id(), 'wpcf-form-fieldsets-toggle',
            true );
    if ( $action == 'open' ) {
        $data[$fieldset] = 1;
    } else if ( $action == 'close' ) {
        unset( $data[$fieldset] );
    }
    update_user_meta( get_current_user_id(), 'wpcf-form-fieldsets-toggle', $data );
}

/**
 * Check if fieldset is saved as open.
 * 
 * @param type $fieldset
 */
function wpcf_admin_form_fieldset_is_collapsed( $fieldset ) {
    $data = get_user_meta( get_current_user_id(), 'wpcf-form-fieldsets-toggle',
            true );
    if ( empty( $data ) ) {
        return true;
    }
    return array_key_exists( $fieldset, $data ) ? false : true;
}

/**
 * Adds help on admin pages.
 * 
 * @param type $contextual_help
 * @param type $screen_id
 * @param type $screen
 * @return type 
 */
function wpcf_admin_plugin_help( $hook, $page ) {
    global $wp_version;
    $call = false;
    $contextual_help = '';
    $page = $page;
    if ( isset( $page ) && isset( $_GET['page'] ) && $_GET['page'] == $page ) {
        switch ( $page ) {
            case 'wpcf-cf':
                $call = 'custom_fields';
                break;

            case 'wpcf-ctt':
                $call = 'custom_types_and_taxonomies';
                break;

            case 'wpcf-import-export':
                $call = 'import_export';
                break;

            case 'wpcf-edit':
                $call = 'edit_group';
                break;

            case 'wpcf-edit-type':
                $call = 'edit_type';
                break;

            case 'wpcf-edit-tax':
                $call = 'edit_tax';
                break;
            case 'wpcf':
                $call = 'wpcf';
                break;
        }
    }
    if ( $call ) {
        require_once WPCF_ABSPATH . '/help.php';
        $contextual_help = wpcf_admin_help( $call, $contextual_help );
        // WP 3.3 changes
        if ( version_compare( $wp_version, '3.2.1', '>' ) ) {
            set_current_screen( $hook );
            $screen = get_current_screen();
            if ( !is_null( $screen ) ) {
                $args = array(
                    'title' => __( 'Types', 'wpcf' ),
                    'id' => 'wpcf',
                    'content' => $contextual_help,
                    'callback' => false,
                );
                $screen->add_help_tab( $args );
            }
        } else {
            add_contextual_help( $hook, $contextual_help );
        }
    }
}

/**
 * Promo texts
 * 
 * @todo Move!
 */
function wpcf_admin_promotional_text() {
    $promo_tabs = get_option( '_wpcf_promo_tabs', false );
    // random selection every one hour
    if ( $promo_tabs ) {
        $time = time();
        $time_check = intval( $promo_tabs['time'] ) + 60 * 60;
        if ( $time > $time_check ) {
            $selected = mt_rand( 0, 3 );
            $promo_tabs['selected'] = $selected;
            $promo_tabs['time'] = $time;
            update_option( '_wpcf_promo_tabs', $promo_tabs );
        } else {
            $selected = $promo_tabs['selected'];
        }
    } else {
        $promo_tabs = array();
        $selected = mt_rand( 0, 3 );
        $promo_tabs['selected'] = $selected;
        $promo_tabs['time'] = time();
        update_option( '_wpcf_promo_tabs', $promo_tabs );
    }
    include WPCF_ABSPATH . '/marketing/helpful-links.php';
}

/**
 * Collapsible scripts. 
 */
function wpcf_admin_load_collapsible() {
    wp_enqueue_script( 'wpcf-collapsible',
            WPCF_RES_RELPATH . '/js/collapsible.js', array('jquery'),
            WPCF_VERSION );
    wp_enqueue_style( 'wpcf-collapsible',
            WPCF_RES_RELPATH . '/css/collapsible.css', array(), WPCF_VERSION );
    $option = get_option( 'wpcf_toggle', array() );
    if ( !empty( $option ) ) {
        $setting = 'new Array("' . implode( '", "', array_keys( $option ) ) . '")';
        wpcf_admin_add_js_settings( 'wpcf_collapsed', $setting );
    }
}

/**
 * Toggle button.
 * 
 * @param type $div_id
 * @return type 
 */
function wpcf_admin_toggle_button( $div_id ) {
    return '<a href="'
            . admin_url( 'admin-ajax.php?action=wpcf_ajax&wpcf_action=toggle&div='
                    . $div_id . '-toggle&_wpnonce='
                    . wp_create_nonce( 'toggle' ) )
            . '" id="' . $div_id
            . '" class="wpcf-collapsible-button"></a>';
}

/**
 * Various delete/deactivate content actions.
 * 
 * @param type $type
 * @param type $arg
 * @param type $action 
 */
function wpcf_admin_deactivate_content( $type, $arg, $action = 'delete' ) {
    switch ( $type ) {
        case 'post_type':
            // Clean tax relations
            if ( $action == 'delete' ) {
                $custom = get_option( 'wpcf-custom-taxonomies', array() );
                foreach ( $custom as $post_type => $data ) {
                    if ( empty( $data['supports'] ) ) {
                        continue;
                    }
                    if ( array_key_exists( $arg, $data['supports'] ) ) {
                        unset( $custom[$post_type]['supports'][$arg] );
                    }
                }
                update_option( 'wpcf-custom-taxonomies', $custom );
            }
            break;

        case 'taxonomy':
            // Clean post relations
            if ( $action == 'delete' ) {
                $custom = get_option( 'wpcf-custom-types', array() );
                foreach ( $custom as $post_type => $data ) {
                    if ( empty( $data['taxonomies'] ) ) {
                        continue;
                    }
                    if ( array_key_exists( $arg, $data['taxonomies'] ) ) {
                        unset( $custom[$post_type]['taxonomies'][$arg] );
                    }
                }
                update_option( 'wpcf-custom-types', $custom );
            }
            break;

        default:
            break;
    }
}

/**
 * Loads teasers.
 * 
 * @param type $teasers 
 */
function wpcf_admin_load_teasers( $teasers ) {
    foreach ( $teasers as $teaser ) {
        $file = WPCF_ABSPATH . '/plus/' . $teaser;
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}