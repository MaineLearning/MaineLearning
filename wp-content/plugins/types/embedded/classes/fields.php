<?php
/*
 * 
 * 
 * Fields class.
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';

/**
 * Fields class.
 * 
 * Holds available data about field types and created fields.
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Fields
{

    var $field_types;
    var $fields;

    function __construct() {
        $this->field_types = $this->get_fields_types();
        $this->fields = $this->get_fields();
    }

    function get_fields_types() {
        return wpcf_admin_fields_get_available_types();
    }

    /**
     * Get fields.
     * 
     * Returns fields object
     * $fields->all - contains all fields
     * $fields->active - contains only active
     * $fields->inactive - contains only inactive
     * $fields->disabled - disabled by Types
     * 
     * Parameters for
     * wpcf_admin_fields_get_fields()
     * 
     * $only_active = false
     * $disabled_by_type = false
     * $strictly_active = false
     * 
     * @return \stdClass 
     */
    function get_fields( $args = array(), $toolset = 'types' ) {
        $r = new stdClass();
        $r->all = apply_filters( 'types_fields',
                wpcf_admin_fields_get_fields( false, true, false ), $args,
                $toolset );

        return $r;
    }

}