<?php
/*
 * Bootstrap code.
 * 
 * Types plugin or embedded code is initialized here.
 * Here is determined if code is used as plugin or embedded code.
 * 
 * @since Types 1.2
 */

/*
 * 
 * 
 * If WPCF_VERSION is not defined - we're running embedded code
 */
if ( !defined( 'WPCF_VERSION' ) ) {

    // Mark that!
    define( 'WPCF_RUNNING_EMBEDDED', true );

    // INITIALIZE!
    add_action( 'init', 'wpcf_embedded_init' );
}

/*
 * 
 * 
 * Define necessary constants
 */
define( 'WPCF_EMBEDDED_ABSPATH', dirname( __FILE__ ) );
define( 'WPCF_EMBEDDED_INC_ABSPATH', WPCF_EMBEDDED_ABSPATH . '/includes' );
define( 'WPCF_EMBEDDED_RES_ABSPATH', WPCF_EMBEDDED_ABSPATH . '/resources' );

/*
 * 
 * Always se DEBUG as false 
 */
if ( !defined( 'WPCF_DEBUG' ) ) {
    define( 'WPCF_DEBUG', false );
}

/*
 * 
 * Include common code.
 */
if ( !defined( 'ICL_COMMON_FUNCTIONS' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/functions.php';
}

/*
 * 
 * Register theme options
 */
wpcf_embedded_after_setup_theme_hook();

/*
 * 
 * 
 * Set $wpcf global var as generic class
 */
$GLOBALS['wpcf'] = new stdClass();

/**
 * Main init hook.
 * 
 * All rest of init processes are continued here.
 * Sets locale, constants, includes...
 * 
 * @todo Make sure plugin AND embedded code are calling this function on 'init'
 * @todo Test priorities
 */
function wpcf_embedded_init() {

    do_action( 'wpcf_before_init' );

    // Set locale
    $locale = get_locale();
    load_textdomain( 'wpcf',
            WPCF_EMBEDDED_ABSPATH . '/locale/types-' . $locale . '.mo' );
    if ( !defined( 'WPV_VERSION' ) ) {
        load_textdomain( 'wpv-views',
                WPCF_EMBEDDED_ABSPATH . '/locale/locale-views/views-' . $locale . '.mo' );
    }

    // Define necessary constants if plugin is not present
    // This ones are skipped if used as embedded code!
    if ( !defined( 'WPCF_VERSION' ) ) {
        define( 'WPCF_VERSION', '1.2' );
        define( 'WPCF_META_PREFIX', 'wpcf-' );
        define( 'WPCF_EMBEDDED_RELPATH', icl_get_file_relpath( __FILE__ ) );
    } else {
        // Otherwise if plugin code - just define embedded paths
        define( 'WPCF_EMBEDDED_RELPATH', WPCF_RELPATH . '/embedded' );
    }

    // Define embedded paths
    define( 'WPCF_EMBEDDED_INC_RELPATH', WPCF_EMBEDDED_RELPATH . '/includes' );
    define( 'WPCF_EMBEDDED_RES_RELPATH', WPCF_EMBEDDED_RELPATH . '/resources' );

    // TODO INCLUDES!
    // 
    // Please add all required includes here
    // Since Types 1.2 we can consider existing code as core.
    // All new functionalities should be added as includes HERE
    // and marked with @since Types $version.
    // 
    // Thanks!
    //
    
    // Basic
    /*
     * 
     * Mind class extensions queue
     */
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/fields.php';
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/field.php';
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/class.wpcf-template.php';

    // Repeater
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/repeater.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/repetitive-fields-ordering.php';

    // Relationship
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/relationship.php';

    // Conditional
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/conditional.php';

    // API
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/api.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/api.php';

    // Validation
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/validation.php';

    // Post Types
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/class.wpcf-post-types.php';

    // Import Export
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/class.wpcf-import-export.php';

    // Incubator
    require_once WPCF_EMBEDDED_ABSPATH . '/incubator/index.php';

    // Module manager
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/module-manager.php';

    // WPML specific code
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/wpml.php';

    /*
     * 
     * 
     * TODO This is a must for now.
     * See if any fields need to be loaded.
     */
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/checkbox.php';


    /*
     * 
     * 
     * Use this call to load basic scripts and styles if necesary
     * wpcf_enqueue_scripts();
     */

    // Include frontend or admin code
    if ( is_admin() ) {
        require_once WPCF_EMBEDDED_ABSPATH . '/admin.php';

        /*
         * TODO Check if called twice
         * 
         * Watch this! This is actually called twice everytime
         * in both modes (plugin or embedded)
         */
        wpcf_embedded_admin_init_hook();
    } else {
        require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
    }

    global $wpcf;

    // TODO since Types 1.2 Continue adding new functionalities HERE
    /*
     * Consider code already there as core.
     * Use hooks to add new functionalities
     * 
     * Introduced new global object $wpcf
     * Holds useful objects like:
     * $wpcf->field - Field object (base item object)
     * $wpcf->repeater - Repetitive field object
     */

    // Set field object
    $wpcf->field = new WPCF_Field();

    // Set fields object
    $wpcf->fields = new WPCF_Fields();

    // Set template object
    $wpcf->template = new WPCF_Template();

    // Set repeater object
    $wpcf->repeater = new WPCF_Repeater();

    // Set relationship object
    $wpcf->relationship = new WPCF_Relationship();

    // Set conditional object
    $wpcf->conditional = new WPCF_Conditional();

    // Set validate object
    $wpcf->validation = new WPCF_Validation();

    // Set import export objects
    $wpcf->import = new WPCF_Import_Export();
    $wpcf->export = new WPCF_Import_Export();

    // Set API object
    $wpcf->api = new WPCF_Api();

    // Set post object
    $wpcf->post = new stdClass();

    // Set post types object
    $wpcf->post_types = new WPCF_Post_Types();

    // Define exceptions - privileged plugins and their data
    $wpcf->toolset_post_types = array(
        'view', 'view-template', 'cred-form'
    );
    $wpcf->excluded_post_types = array(
        'revision', 'view', 'view-template', 'cred-form', 'nav_menu_item', 'attachment', 'mediapage',
    );

    // Init custom types and taxonomies
    wpcf_init_custom_types_taxonomies();


    /*
     * TODO Check why we enabled this
     * 
     * I think because of CRED or Views using Types admin functions on frontend
     * Does this need review?
     */
    if ( defined( 'DOING_AJAX' ) ) {
        require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
    }

    // Check if import/export request is going on
    wpcf_embedded_check_import();

    // Set debugging
    if ( !defined( 'WPCF_DEBUG' ) ) {
        define( 'WPCF_DEBUG', false );
    }
    if ( WPCF_DEBUG ) {
        $wpcf->debug = new stdClass();
        require WPCF_INC_ABSPATH . '/debug.php';
        add_action( 'wp_footer', 'wpcf_debug', 99999999999999999999999999999999 );
        add_action( 'admin_footer', 'wpcf_debug', 99999999999999999999999999999 );
    }

    do_action( 'wpcf_after_init' );
}