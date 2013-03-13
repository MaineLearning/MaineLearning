<?php
/*
 * Conditional class
 * 
 * Few break-points summarized:
 * 1. Wrapping fields when in form
 * 2. Filtering AJAX check
 * 3. Calling JS
 */

/**
 * Conditional class.
 * 
 * Very useful, should be used to finish small tasks for conditional field.
 * 
 * Example:
 * 
 * // Setup field
 * global $wpcf;
 * $my_field = new WPCF_Conditional();
 * $my_field->set($wpcf->post, wpcf_admin_fields_get_field('image'));
 * 
 * // Use it
 * $is_valid = $my_field->evaluate();
 * 
 * Generic instance can be found in global $wpcf.
 * global $wpcf;
 * $wpcf->conditional->set(...);
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Conditional extends WPCF_Field
{

    /**
     * Holds all processed fields using one instance.
     */
    var $collected = array();

    /**
     * Holds all triggers on which check should fire.
     * 
     * @var type 
     */
    var $triggers = array();

    /**
     * Marks if currently processed field is valid.
     * 
     * 
     * @var type 
     */
    var $passed = true;

    /**
     * Trigger CSS class.
     * @var type 
     */
    var $css_class_trigger = 'wpcf-conditional-check-trigger';

    /**
     * Field CSS class.
     * @var type 
     */
    var $css_class_field = 'wpcf-conditional';

    /**
     * Evaluate object.
     * 
     * @var type 
     */
    var $evaluate = null;

    function __construct() {
        parent::__construct();
        $fields = new WPCF_Fields();
        foreach ( $fields->fields->all as $f_id => $f ) {
            if ( !empty( $f['data']['conditional_display']['conditions'] ) ) {
                foreach ( $f['data']['conditional_display']['conditions'] as
                            $condition ) {
                    $this->collected[$f_id][] = $condition;
                    if ( !empty( $condition['field'] ) ) {
                        $this->triggers[$condition['field']][$f_id][] = $condition;
                    }
                }
            }
        }
    }

    function set( $post, $cf ) {
        parent::set( $post, $cf );

        // Check and record call
        $this->fields[$this->slug] = $this->passed = $this->evaluate();
    }

    /**
     * Checks if field is conditional.
     * 
     * @param type $field
     * @return type 
     */
    function is_conditional( $field = array() ) {
        return !empty( $field['data']['conditional_display']['conditions'] );
    }

    /**
     * Checks if field is check trigger.
     * 
     * @param type $field
     * @return type 
     */
    function is_trigger( $field = array() ) {
        return !empty( $this->triggers[$field['id']] );
    }

    /**
     * Enqueues scripts. 
     */
    function add_js() {
        wp_enqueue_script( 'wpcf-conditional',
                WPCF_EMBEDDED_RES_RELPATH . '/js/conditional.js',
                array('jquery'), WPCF_VERSION );
        wpcf_admin_add_js_settings( 'wpcfConditionalVerify_nonce',
                wp_create_nonce( 'cd_verify' )
        );
    }

    /**
     * Wraps each trigger check field with $this->css_class_trigger
     * and corespondive classes.
     * 
     * @param type $element
     * @return type 
     */
    function wrap_trigger( $element = array() ) {

        // Set attribute class to $this->css_class_trigger
        if ( isset( $element['#attributes']['class'] ) ) {
            $element['#attributes']['class'] .= ' ' . $this->css_class_trigger;
        } else {
            $element['#attributes']['class'] = $this->css_class_trigger;
        }

        return apply_filters( 'types_conditional_field_trigger', $element, $this );
    }

    /**
     * Wraps each field with $this->css_class_field and corespondive classes.
     * 
     * @param type $element
     * @return type 
     */
    function wrap( $element = array() ) {
        if ( !empty( $element ) ) {
            $passed = $this->passed;
            if ( !$passed ) {
                $wrap = '<div class="' . $this->css_class_field . ' '
                        . $this->css_class_field . '-failed" style="display:none;">';
            } else {
                $wrap = '<div class="' . $this->css_class_field . ' '
                        . $this->css_class_field . '-passed">';
            }
            if ( isset( $element['#before'] ) ) {
                $element['#before'] = $wrap . $element['#before'];
            } else {
                $element['#before'] = $wrap;
            }
            if ( isset( $element['#after'] ) ) {
                $element['#after'] = $element['#after'] . '</div>';
            } else {
                $element['#after'] = '</div>';
            }
        }

        return apply_filters( 'types_conditional_field', $element, $this );
    }

    /**
     * Returns JS for specified field.
     * 
     * @todo Move repetitive to filter
     * @param type $field_name
     * @return string 
     */
    function render_js_hide( $field_name = null ) {
        if ( is_null( $field_name ) && isset( $this->cf['slug]'] ) ) {
            $field_name = $this->cf['slug'];
        }
        if ( empty( $field_name ) ) {
            return '';
        }
        $js = '';
        $js .= 'jQuery(\'[name^="' . $field_name . '"]\').parents(\'.wpcf-repetitive-wrapper\').hide();';
        $js .= 'jQuery(\'[name^="' . $field_name . '"]\').parents(\'.'
                . $this->css_class_field . '\').hide().addClass(\''
                . $this->css_class_field . '-failed\').removeClass(\''
                . $this->css_class_field . '-passed\');' . " ";
        return apply_filters( 'types_conditional_js_hide', $js, $this );
    }

    /**
     * Returns JS for specified field.
     * 
     * @todo Move repetitive to filter
     * @param type $field_name
     * @return string 
     */
    function render_js_show( $field_name = null ) {
        if ( is_null( $field_name ) && isset( $this->cf['slug]'] ) ) {
            $field_name = $this->cf['slug'];
        }
        if ( empty( $field_name ) ) {
            return '';
        }
        $js = '';
        $js .= 'jQuery(\'[name^="' . $field_name . '"]\').parents(\'.'
                . $this->css_class_field . '\').show().removeClass(\''
                . $this->css_class_field . '-failed\').addClass(\''
                . $this->css_class_field . '-passed\');' . " ";
        $js .= 'jQuery(\'[name^="' . $field_name
                . '"]\').parents(\'.wpcf-repetitive-wrapper\').show();';
        return apply_filters( 'types_conditional_js_show', $js, $this );
    }

    /**
     * Evaluates if check passed.
     * 
     * @return type 
     */
    function evaluate() {
        if ( is_null( $this->evaluate ) ) {
            require_once DIRNAME( __FILE__ ) . '/conditional/evaluate.php';
            $this->evaluate = new WPCF_Evaluate();
        }
        $this->passed = $this->evaluate->evaluate( $this );
        return $this->passed;
    }

}