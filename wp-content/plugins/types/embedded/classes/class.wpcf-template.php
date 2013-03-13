<?php
/*
 * Template Class
 */

/**
 * Template Class
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Template
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Template
{

    /**
     * Template shortname.
     * 
     * @var type string
     */
    protected $_template = '';

    /**
     * Data object.
     * 
     * @var type object
     */
    private $_data = '';

    /**
     * Temporary ob capture.
     * 
     * @var type string
     */
    protected $_capture = '';

    /**
     * Set defaults
     */
    function __construct() {
        $this->_data = new stdClass();
    }

    /**
     * Set instance.
     * 
     * @param type $template
     * @param type $data
     */
    function set( $template, $data = array() ) {
        $this->_template = strval( $template );
        $this->_data = (object) $data;
    }

    /**
     * Returns HTML formatted output.
     * 
     * @param type $template
     * @return string
     */
    function get( $template = null ) {

        if ( is_null( $template ) ) {
            $template = $this->template;
        }

        $file = WPCF_EMBEDDED_ABSPATH . '/templates/' . strval( $template ) . '.html.php';
        if ( !file_exists( $file ) ) {
            return '<code>missing_template</code>';
        }

        ob_start();
        include $file;
        $output = ob_get_contents();
        ob_get_clean();
        return apply_filters( 'wpcf_get_template', $output, $this->_template,
                        $this->_data );
        }

    /**
     * Echoes AJAX HTML header.
     */
    function ajax_header() {

        // Enqueue Types scripts
        wpcf_enqueue_scripts();

        /*
         * 
         * TODO Resolve migration from Thickbox
         * Since 1.2 we clone header and footer until we resolve future Thickbox
         * handling.
         */
//        include ABSPATH . '/wp-admin/admin-header.php';
        include WPCF_EMBEDDED_ABSPATH . '/includes/ajax/admin-header.php';
    }

    /**
     * Echoes WP footer.
     */
    function ajax_footer() {
        do_action( 'admin_footer_wpcf_ajax' );
        include WPCF_EMBEDDED_ABSPATH . '/includes/ajax/admin-footer.php';
    }

}