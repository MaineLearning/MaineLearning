<?php
/*
 * Validation helper class
 * 
 * Renders dynamic JS.
 */

/**
 * Validation helper class.
 * 
 * Renders dynamic JS.
 * 
 * @since Types 1.1.5
 * @package Types
 * @subpackage Validation
 * @version 0.1.1
 * @category helper
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Validation_Javascript
{

    /**
     * Returns formatted JS.
     * 
     * @param type $selector Can be CSS class or element ID
     * @return string
     */
    function fields_js( $selector = '.wpcf-form-validate' ) {

        // Get collected validation rules
        $elements = wpcf_form_add_js_validation( 'get' );

        // Not collected!
        if ( empty( $elements ) ) {
            return '';
        }

        /*
         * Start output
         */
        $output = '';

        /*
         * Open tags and trigger jQuery validation.
         */
        $output .= "\r\n" . '<script type="text/javascript">' . "\r\n" . '/* <![CDATA[ */'
                . "\r\n" . 'jQuery(document).ready(function(){' . "\r\n"
                . 'if (jQuery("' . $selector . '").length > 0){' . "\r\n"
                . 'jQuery("' . $selector . '").validate({
        errorPlacement: function(error, element){
            error.insertBefore(element);
        },
        highlight: function(element, errorClass, validClass) {
            jQuery(\'#publishing-action .spinner\').css(\'visibility\', \'hidden\');
            jQuery(\'#publish\').bind(\'click\', function(){
                jQuery(\'#publishing-action .spinner\').css(\'visibility\', \'visible\');
            });
            jQuery(element).parents(\'.collapsible\').slideDown();
            ';

        if ( $selector == '#post' ) {
            $output .= 'var box = jQuery(element).parents(\'.postbox\');
                if (box.hasClass(\'closed\')) {
                    box.find(\'.handlediv\').trigger(\'click\');
                    }';
        }

        $output .= '
            jQuery(element).parents(\'.collapsible\').slideDown();
            jQuery("input#publish").addClass("button-primary-disabled");
            jQuery("input#save-post").addClass("button-disabled");
            jQuery("#save-action .ajax-loading").css("visibility", "hidden");
            jQuery("#publishing-action #ajax-loading").css("visibility", "hidden");
//            jQuery.validator.defaults.highlight(element, errorClass, validClass); // Do not add class to element
		},
        unhighlight: function(element, errorClass, validClass) {
			jQuery("input#publish, input#save-post").removeClass("button-primary-disabled").removeClass("button-disabled");
//            jQuery.validator.defaults.unhighlight(element, errorClass, validClass);
		},';

        /*
         * jQuery invalidHandler
         * http://docs.jquery.com/Plugins/Validation/validate#toptions
         * 
         * Since Types 1.1.5 we apply filters on invalid handler
         */
        $additional_js = apply_filters( 'wpcf_validation_js_invalid_handler',
                '', $elements, $selector, array('form', 'validator') );
        $output .= '
        invalidHandler: function(form, validator) {
        
            elements = new Array();
        
            // validator.errorList contains an array of objects, where each object has properties "element" and "message".  element is the actual HTML Input.
            for (var i=0;i<validator.errorList.length;i++){
                var el = validator.errorList[i].element;
                elements.push(jQuery(el).attr(\'id\'));
            }

            // validator.errorMap is an object mapping input names -> error messages
            //    for (var i in validator.errorMap) {
            //      console.log(i, ":", validator.errorMap[i]);
            //    }

            var form = jQuery(\'' . $selector . '\');
            var passed = false;
            ' . $additional_js . '
//            alert(passed);
            if (passed) {
                jQuery(\'' . $selector . '\').validate().cancelSubmit = true;
                jQuery(\'' . $selector . '\').submit();
            }
        },
        ';

        $output .= 'errorClass: "wpcf-form-error"';
        $output .= '});' . "\r\n";

        /*
         * 
         * 
         * Render JS validation code for each element collected
         */
        foreach ( $elements as $id => $element ) {
            // Basic check
            if ( empty( $element['#validate'] ) ) {
                continue;
            }
            /*
             * 
             * Adjust rules according to field type
             * 
             * TODO Document why radios selects 'name' instead of 'id'
             */
            if ( in_array( $element['#type'], array('radios') ) ) {
                $output .= 'jQuery(\'input[name="' . $element['#name']
                        . '"]\').rules("add", {' . "\r\n";
            } else {
                $output .= 'jQuery("#' . $id . '").rules("add", {' . "\r\n";
            }

            $rules = array(); // Rules output
            $messages = array(); // Messages output
            $collected = array(); // Various collected data (for hooks)

            /*
             * $method is registered jQuery validation method
             * $args['value'] is custom tailored parameter
             * $args['message'] is custom message that will be displayed on failure
             * 
             * $args may be used to pass other useful properties
             */
            foreach ( $element['#validate'] as $method => $args ) {

                // Set generic value 'true'
                if ( !isset( $args['value'] ) ) {
                    $args['value'] = 'true';
                }

                // Set rule
                // since Types 1.1.5 we use element ID
                $rules[$id][$method] = $method . ': ' . $args['value'];

                // Set message
                if ( empty( $args['message'] ) ) {
                    $args['message'] = wpcf_admin_validation_messages( $method );
                }
                // since Types 1.1.5 we use element ID
                $messages[$id][$method] = $method . ': \'' . esc_js( $args['message'] ) . '\'';

                // Collect!
                $collected[$id][$method] = $args;
            }

            /*
             * 
             * Add rules to output
             */
            $_rules_o = apply_filters( 'wpcf_validation_js_fields_rules',
                    $rules, $collected );
            foreach ( $_rules_o as $_rules ) {
                $output .= implode( ',' . "\r\n", $_rules );
            }

            /*
             * 
             * Add messages to output
             */
            if ( !empty( $messages ) ) {
                $_messages_o = apply_filters( 'wpcf_validation_js_fields_messages',
                        $messages, $collected );
                foreach ( $_messages_o as $_messages ) {
                    $output .= ',' . "\r\n" . 'messages: {' . "\r\n"
                            . implode( ',' . "\r\n", $_messages ) . "\r\n" . '},';
                }
            }

            // Close main jQuery function call
            $output .= "\r\n" . '});' . "\r\n";
        }

        // Close tag
        $output .= "\r\n" . '/* ]]> */' . "\r\n" . '}' . "\r\n" . '})' . "\r\n"
                . '</script>' . "\r\n";

        return apply_filters( 'wpcf_validation_js_fields_output', $output,
                        $collected );
        }

}