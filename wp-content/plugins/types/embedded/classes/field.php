<?php
/*
 * 
 * 
 * Field class.
 */

/**
 * Base class.
 * 
 * Fields are our core items and we'll use this class to sort them out a little.
 * Very useful, should be used to finish small tasks for field.
 * 
 * Example:
 * 
 * // Setup field
 * global $wpcf;
 * $my_field = new WPCF_Field();
 * $my_field->set($wpcf->post, wpcf_admin_fields_get_field('image'));
 * 
 * // Use it
 * $my_field->save();
 * 
 * // Generic instance can be found in global $wpcf.
 * global $wpcf;
 * $wpcf->field->set(...);
 * 
 * !! BE CAREFUL !! not to disturb global instance if you suspect processing
 * current item is not finished. Core code sometimes use same instance over
 * few functions and places.
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Field
{

    /**
     * Field structure
     * 
     * This is actually a form array collected from files per specific field:
     * /embedded/includes/fields/$field_type.php
     * /includes/fields/$field_type.php
     * 
     * @var type array
     */
    var $cf = array();

    /**
     * All Types created fields
     * @var type 
     */
    var $fields = null;

    /**
     * Field saved data
     * @var type
     */
    var $meta = null;

    /**
     * Field config.
     * 
     * Use it to set default configuration.
     * 
     * @var type array
     */
    var $config = array(
        'use_form' => false,
    );

    /**
     * Form object
     * @var type array
     */
    var $form = array();

    /**
     * Sets post
     */
    var $post;

    /**
     * CF slug
     * @var type string
     */
    var $slug = '';

    /**
     * Use cache flag
     * @var type boolean
     */
    var $use_cache = true;

    /**
     * Add to editor flas
     * @var type boolean
     */
    var $add_to_editor = true;

    /**
     * Context in which class is called
     * @var type 
     */
    var $context = 'group';

    /**
     * Invalid fields
     * 
     * @todo Revise
     * @var type 
     */
    var $invalid_fields = array();

    /**
     * ID 
     */
    var $ID = '';

    /**
     * Unique ID 
     */
    var $unique_id = '';

    function __construct( $config = array() ) {

        // Parse args
        extract( wp_parse_args( (array) $config, $this->config ) );

        // Config
        if ( !empty( $use_form ) ) {
            require_once WPCF_EMBEDDED_ABSPATH . '/classes/forms.php';
            $this->form = new Enlimbo_Forms_Wpcf();
        }

        // Fields
        $this->__fields_object = new WPCF_Fields();
        $this->__fields_object = $this->__fields_object->get_fields();
        $this->fields = $this->__fields_object->all;
    }

    /**
     * Set current post and field.
     * 
     * @param type $post
     * @param type $cf 
     */
    function set( $post, $cf ) {

        global $wpcf;

        /*
         * 
         * Check if $cf is string
         */
        if ( is_string( $cf ) ) {
            if ( isset( $this->fields[$this->__get_slug_no_prefix( $cf )] ) ) {
                $cf = $this->fields[$this->__get_slug_no_prefix( $cf )];
            } else {
                /*
                 * TODO Check what happens if field is not found
                 */
                $this->_reset();
                return false;
            }
        }

        $this->post = is_integer( $post ) ? get_post( $post ) : $post;
        // If empty post it is new
        if ( empty( $this->post->ID ) ) {
            $this->post = new stdClass();
            $this->post->ID = 0;
        }
        $this->ID = $cf['id'];
        $this->cf = $cf;
        $this->slug = wpcf_types_get_meta_prefix( $this->cf ) . $this->cf['slug'];
        $this->meta = $this->_get_meta();
        $this->config = $this->_get_config();
        $this->unique_id = wpcf_unique_id( serialize( (array) $this ) );
        $this->cf['value'] = $this->meta;

        // Debug
        $wpcf->debug->fieds[$this->unique_id] = $this->cf;
        $wpcf->debug->meta[$this->slug][] = $this->meta;

        // Load files
        $file = WPCF_EMBEDDED_INC_ABSPATH . '/fields/' . $this->cf['type'] . '.php';
        if ( file_exists( $file ) ) {
            include_once $file;
        }
        if ( defined( 'WPCF_INC_ABSPATH' ) ) {
            $file = WPCF_INC_ABSPATH . '/fields/' . $this->cf['type'] . '.php';
            if ( file_exists( $file ) ) {
                include_once $file;
            }
        }
    }

    /**
     * Reset on failure.
     */
    function _reset() {
        $this->ID = '';
        $this->cf = array();
        $this->post = new stdClass();
        $this->slug = '';
        $this->meta = '';
        $this->__meta = '';
        $this->config = array();
        $this->unique_id = '';
        $this->cf['value'] = '';
    }

    /**
     * Set needed but not required form elements.
     * 
     * @param string $cf 
     */
    function _parse_cf_form_element( $cf ) {
        $p = array('#before' => '', '#after' => '', '#description' => '');
        foreach ( $p as $_p => $param ) {
            if ( !isset( $cf[$_p] ) ) {
                $cf[$_p] = $param;
            }
        }
        return $cf;
    }

    /**
     * Fetch and sort fields.
     * 
     * @global type $wpdb 
     */
    function _get_meta( $check_post = false ) {
        global $wpdb;

        // Get straight from DB single value
        $r = $wpdb->get_row(
                $wpdb->prepare(
                        "SELECT * FROM $wpdb->postmeta
                WHERE post_id=%d
                AND meta_key=%s",
                        $this->post->ID, $this->slug )
        );

        // Sort meta
        $meta = array();
        if ( !empty( $r ) ) {
            $meta = maybe_unserialize( $r->meta_value );
            $this->meta_object = $r;
        } else {
            $meta = null;
            $this->meta_object = new stdClass();
            $this->meta_object->meta_id = null;
            $this->meta_object->meta_key = null;
            $this->meta_object->meta_value = null;
        }

        // If forced $_POST
        if ( $check_post ) {
            $meta = $this->get_submitted_data();
        }

        /*
         * Secret public object :)
         * Keeps original data
         */
        $this->__meta = $meta;

        /*
         * 
         * Apply filters
         * !!! IMPORTANT !!!
         * TODO Make this only place where field meta value is filtered
         */
        $meta = apply_filters( 'wpcf_fields_value_get', $meta, $this );
        $meta = apply_filters( 'wpcf_fields_slug_' . $this->cf['slug']
                . '_value_get', $meta, $this );
        $meta = apply_filters( 'wpcf_fields_type_' . $this->cf['type']
                . '_value_get', $meta, $this );

        return $meta;
    }

    /**
     * Gets $_POST data.
     * 
     * @return type 
     */
    function get_submitted_data() {
        $posted = isset( $_POST['wpcf'][$this->cf['slug']] ) ? $_POST['wpcf'][$this->cf['slug']] : null;
        $value = apply_filters( 'types_field_get_submitted_data', $posted, $this );
        return $value;
    }

    /**
     * Save field.
     * 
     * If $value is empty, $_POST will be checked.
     * Since Types 1.2 we do not save fields if empty.
     * 
     * @param type $value 
     */
    function save( $value = null ) {

        // If $value null, look for submitted data
        if ( is_null( $value ) ) {
            $value = $this->get_submitted_data();
        }

        /*
         * 
         * 
         * Since Types 1.2
         * We completely rewrite meta.
         * It has no impact on frontend and covers a lot of cases
         * (e.g. user change mode from single to repetitive)
         */
        delete_post_meta( $this->post->ID, $this->slug );

        // Save
        if ( !empty( $value ) ) {

            // Trim
            if ( is_string( $value ) ) {
                $value = trim( $value );
            }

            // Apply filters
            $_value = $this->_filter_save_value( $value );

            // Save field
            $mid = add_post_meta( $this->post->ID, $this->slug, $_value );

            // CAll HOOKS
            /*
             * 
             * Use these hooks to add future functionality.
             * Do not add any more code to core.
             */
            $this->_action_save( $this->cf, $_value, $mid, $value );
        }
    }

    /**
     * Apply filters to saved value.
     * 
     * @param type $value
     * @return type 
     */
    function _filter_save_value( $value ) {
        // Apply filters
        $value = apply_filters( 'wpcf_fields_value_save', $value,
                $this->cf['type'], $this->cf['slug'], $this->cf, $this );
        $value = apply_filters( 'wpcf_fields_slug_' . $this->cf['slug']
                . '_value_save', $value, $this->cf, $this );
        $value = apply_filters( 'wpcf_fields_type_' . $this->cf['type']
                . '_value_save', $value, $this->cf, $this );

        return $value;
    }

    /**
     * Use these hooks to add future functionality.
     * Do not add any more code to core.
     * 
     * @param type $field
     * @param type $value
     * @param type $meta_id
     */
    function _action_save( $field, $value, $meta_id, $meta_value_original ) {
        do_action( 'wpcf_fields_save', $value, $field, $this, $meta_id );
        do_action( 'wpcf_fields_slug_' . $field['slug'] . '_save', $value,
                $field, $this, $meta_id, $meta_value_original );
        do_action( 'wpcf_fields_type_' . $field['type'] . '_save', $value,
                $field, $this, $meta_id, $meta_value_original );
    }

    /**
     * Sets field config.
     * 
     * @return type 
     */
    function _get_config() {
        $file = WPCF_EMBEDDED_INC_ABSPATH . '/fields/' . $this->cf['type'] . '.php';
        if ( file_exists( $file ) ) {
            include_once $file;
            $func = 'wpcf_fields_' . $this->cf['type'];
            if ( is_callable( $func ) ) {
                return (object) call_user_func( $func );
            }
        }
        return new stdClass();
    }

    /**
     * Discouraged usage.
     * 
     * @return type
     */
    function _deprecated_inherited_allowed() {
        return array(
            'image' => 'file',
            'numeric' => 'textfield',
            'email' => 'textfield',
            'phone' => 'textfield',
            'url' => 'textfield',
        );
    }

    /**
     * Sets field meta box form.
     * 
     * @return type 
     */
    function _get_meta_form( $meta_value = null, $meta_id = null, $wrap = true ) {

        include_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/' . $this->cf['type'] . '.php';

        /*
         * Set value
         * 
         * IMPORTANT
         * Here we set values for form elements
         */
        $this->cf['value'] = is_null( $meta_value ) ? $this->meta : $meta_value;

        $form = array();
        $form_meta_box = array();
        $inherited = array();
        $this->__multiple = false;

        // Open main wrapper
        if ( $wrap ) {
            $form['__meta_form_OPEN'] = array(
                '#type' => 'markup',
                '#markup' => ''
                . '<div id="wpcf_wrapper_' . $this->unique_id
                . '" class="wpcf-wrap wpcf-meta-form">',
            );
        }

        /*
         * 
         * 
         * 
         * 
         * Since Types 1.2
         * Avoid using parent (inherited) type
         * $this->config->inherited_field_type
         */
        // See if inherited data and merge
        if ( isset( $this->config->inherited_field_type ) ) {
            if ( !array_key_exists( $this->cf['type'],
                            $this->_deprecated_inherited_allowed() ) ) {
                _deprecated_argument( 'inherited_field_type', '1.2',
                        'Since Types 1.2 we encourage developers to completely define fields' );
            }
            $file = WPCF_EMBEDDED_INC_ABSPATH . '/fields/'
                    . $this->config->inherited_field_type
                    . '.php';

            if ( file_exists( $file ) ) {
                include_once $file;

                if ( function_exists( 'wpcf_fields_'
                                . $this->config->inherited_field_type
                                . '_meta_box_form' ) ) {
                    $inherited = call_user_func_array( 'wpcf_fields_'
                            . $this->config->inherited_field_type
                            . '_meta_box_form', array($this->cf, $this) );
                    // If single form - convert to array of elements
                    if ( isset( $inherited['#type'] ) ) {
                        $inherited = array('wpcf_field_' . $this->unique_id => $inherited);
                    }

                    // One value?
                    if ( count( $inherited ) > 1 ) {
                        $this->__multiple = true;
                    }

                    $form = $form + $inherited;
                }
            }
        }

        $func = 'wpcf_fields_' . $this->cf['type'] . '_meta_box_form';
        if ( is_callable( $func ) ) {
            /*
             * 
             * From Types 1.2 use complete form setup
             */
            $form_meta_box = call_user_func_array( 'wpcf_fields_'
                    . $this->cf['type'] . '_meta_box_form',
                    array($this->cf, $this) );

            // If single form - convert to array of elements
            if ( isset( $form_meta_box['#type'] ) ) {
                $form_meta_box = array('wpcf_field_' . $this->unique_id => $form_meta_box);
            }

            // One value?
            if ( count( $form_meta_box ) > 1 ) {
                $this->__multiple = true;
            }

            // Merge
            $form = $form + $form_meta_box;
        }

        if ( !empty( $form ) ) {

            // Process each field
            foreach ( $form as $element_key => $element ) {

                /*
                 * 
                 * Start using __ in keys to skip element
                 */
                // Skip protected
                if ( strpos( $element_key, '__' ) === 0 ) {
                    $form[$element_key] = $element;
                    continue;
                }

                // Add title and description
                // TODO WPML
                if ( empty( $started ) ) {
                    $element['#title'] = wpcf_translate( 'field '
                            . $this->cf['id'] . ' name', $this->cf['name'] );
                    $element['#description'] = wpautop( wpcf_translate( 'field '
                                    . $this->cf['id'] . ' description',
                                    $this->cf['description'] ) );
                    $started = true;
                }

                // Set current element
                $this->__current_form_element = $element;

                // Process field
                $temp_processed = wpcf_admin_post_process_field( $this );
                if ( !empty( $temp_processed['element'] ) ) {
                    $element = $temp_processed['element'];
                }

                // Set form element
                $form[$element_key] = apply_filters( 'wpcf_post_edit_field',
                        $element, $this->cf, $this->post, $this->context );
            }

            // Add to editor
            if ( $this->add_to_editor ) {
                wpcf_admin_post_add_to_editor( $this->cf );
            }

            $this->enqueue_script();
            $this->enqueue_style();
        }

        // Close main wrapper
        if ( $wrap ) {
            $form['meta_form_CLOSE'] = array(
                '#type' => 'markup',
                '#markup' => ''
                . '</div>',
            );
        }

        // Add unique IDs
        foreach ( $form as $k => $v ) {
            $_form[$k . '_' . $this->unique_id] = $v;
        }

        return apply_filters( 'wpcf_meta_form', $_form );
    }

    /**
     * Enqueue all files from config
     */
    function enqueue_script( $config = null ) {
        // Use internal
        if ( is_null( $config ) ) {
            $config = $this->config;
        }
        $config = (object) $config;
        // Process JS
        if ( !empty( $config->meta_box_js ) ) {
            foreach ( $config->meta_box_js as $handle => $data_script ) {
                if ( isset( $data_script['inline'] ) ) {
                    add_action( 'admin_footer', $data_script['inline'] );
                    continue;
                }
                $deps = !empty( $data_script['deps'] ) ? $data_script['deps'] : array();
                wp_enqueue_script( $handle, $data_script['src'], $deps,
                        WPCF_VERSION );
            }
        }
    }

    /**
     * Enqueue all files from config
     */
    function enqueue_style( $config = null ) {
        // Use internal
        if ( is_null( $config ) ) {
            $config = $this->config;
        }
        $config = (object) $config;
        if ( !empty( $config->meta_box_css ) ) {
            foreach ( $config->meta_box_css as $handle => $data_script ) {
                $deps = !empty( $data_script['deps'] ) ? $data_script['deps'] : array();
                if ( isset( $data_script['inline'] ) ) {
                    add_action( 'admin_header', $data_script['inline'] );
                    continue;
                }
                wp_enqueue_style( $handle, $data_script['src'], $deps,
                        WPCF_VERSION );
            }
        }
    }

    /**
     * Use this function to add final filters to HTML output.
     * 
     * @param type $output 
     */
    function html( $html ) {
        /*
         * 
         * Process shortcodes
         * 
         * Wachout for remove_shortcode('types');
         * TODO Loop possible?
         */
        $shortcode = do_shortcode( htmlspecialchars( $html ) );
        $html = htmlspecialchars_decode( stripslashes( $shortcode ) );

        return $html;
    }

    /**
     * Determines if field is created with Types.
     * 
     * @param type $field_key
     */
    function is_under_control( $field_key ) {
        /*
         * 
         * We force checking our meta prefix
         */
        $key = $this->__get_slug_no_prefix( $field_key );
        return !empty( $this->fields[$key] );
    }

    /**
     * Return slug.
     * 
     * @param type $field_key
     * @return type
     */
    function __get_slug_no_prefix( $field_key ) {
        return str_replace( WPCF_META_PREFIX, '', $field_key );
    }

    /**
     * Returns altered element form name.
     * 
     * Use $prefix to set name like:
     * wpcf_post_relationship[214][wpcf-my-checkbox]
     * 
     * Or if multi array
     * wpcf_post_relationship[214][wpcf-my-date][datepicker]
     * wpcf_post_relationship[214][wpcf-my-date][hour]
     * 
     * @param type $prefix
     * @param type $name
     * @return type
     */
    function alter_form_name( $prefix, $name ) {
        $temp = array_pop( explode( '[', $name ) );
        $__name = trim( strval( $temp ), ']' );
        /*
         * This means form is single-valued
         */
        if ( $__name == $this->cf['slug'] ) {
            return strval( $prefix ) . '['
                    . $this->post->ID
                    . '][' . $this->slug . ']';
            /*
             * 
             * Multi array case
             */
        } else {
            return strval( $prefix ) . '['
                    . $this->post->ID
                    . '][' . $this->slug . '][' . $__name . ']';
        }
    }

}