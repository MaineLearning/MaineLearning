/* 
 * Conditional JS.
 */

jQuery(document).ready(function(){
    // Trigger main func
    wpcfConditionalInit();
});

/**
 * Loop through each check trigger field
 * (marked with .wpcf-conditional-trigger
 */
function wpcfConditionalInit(selector) {
    selector = typeof selector !== 'undefined' ? selector+' ' : '';
    jQuery(selector+'.wpcf-conditional-check-trigger').each(function(){
        
        
        /*
         *
         * Why are we triggering on all inputs?
         */
        
        // If triggered from relationship table send just row
        if (jQuery(this).parents('.wpcf-pr-table-wrapper').length > -1) {
            var inputs = jQuery(this).parents('tr').find(':input');
        } else {
            var inputs = jQuery(this).parents('.inside').find(':input');
        }
        
        // Find all inputs inside same meta-box
        //        inputs.each(function(){
            
        // Already binded!
        if (jQuery(this).hasClass('wpcf-cd-binded')) {
            return false;
        }
            
        // Mark as binded
        jQuery(this).addClass('wpcf-cd-binded');
            
        // Bind actions according to form element type
        if (jQuery(this).hasClass('radio')
            || jQuery(this).hasClass('checkbox')) {
            jQuery(this).bind('click', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        } else if (jQuery(this).hasClass('select')) {
            jQuery(this).bind('change', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        } else if (jQuery(this).hasClass('wpcf-datepicker')) {
            jQuery(this).bind('wpcfDateBlur', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        } else {
            jQuery(this).bind('blur', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        }
        //        });
        /*
         * 
         * 
         * 
         * TODO Not sure what this check is about
         * 
         * Whynew post have to have init?
         * Don't all?
         */
        //        if (typeof adminpage !== 'undefined' && adminpage == 'post-new-php') {
        //            wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
        //        }
        wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
        
        /*
         * Since Types 1.1.5
         * We're triggering groups
         */
        wpcfCdGroupVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val(), jQuery(this).parents('.postbox').one().attr('id'));
    });
}
                                                                                                                                        
function wpcfConditionalVerify(object, name, value) {
    
    /*
     * 
     * Skip post relationship entries
     * TODO Obsolete - all fields on screen processed
     */
    if (object.hasClass('wpcf-pr-binded')) {
        return false;
    }
    
    // Define Form
    var form = object.parents('.postbox').find(':input');
    
    // Get group slug
    var group = object.parents('.postbox').attr('id');
    
    // If triggered from relationship table send just row
    if (object.parents('.wpcf-pr-table-wrapper').length > 0) {
        form = object.parents('tr').find(':input');
        group = 'relationship';
    }

    // Check if form is found
    if (form.length > 0) {
        
        // Do AJAX call
        /*
         * 
         * 
         * AJAX should return JSON.execute JS script.
         * TODO Review safety
         */
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: form.serialize()+'&wpcf_group='+group+'&wpcf_main_post_id='+jQuery('#post_ID').val()+'&action=wpcf_ajax&wpcf_action=cd_verify&_wpnonce='+wpcfConditionalVerify_nonce,
            cache: false,
            beforeSend: function() {
            },
            success: function(data) {
                if (data != null) {
                    
                    // See if data.execute exists and eval() it
                    // TODO Review safety
                    if (typeof data.execute != 'undefined'
                        && (typeof data.wpcf_nonce_ajax_callback != 'undefined'
                            && data.wpcf_nonce_ajax_callback == wpcf_nonce_ajax_callback)) {
                        eval(data.execute);
                    }
                }
            }
        });
    }
}

/**
 * Disables 'Add Condition' field.
 */
function wpcfDisableAddCondition(id) {
    jQuery('#wpcf_conditional_add_condition_field_'+id)
    .attr('disabled', 'disabled').unbind('click')
    .removeClass('wpcf-ajax-link').attr('onclick', '');
}

/**
 * Checks if group is valid
 */
function wpcfCdGroupVerify(object, name, value, group_id) {
    var form = jQuery('#post');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: form.serialize()+'&group_id='+group_id+'&action=wpcf_ajax&wpcf_action=cd_group_verify&_wpnonce='+window.wpcfConditionalVerifyGroup,
        cache: false,
        beforeSend: function() {
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.execute != 'undefined'
                    && (typeof data.wpcf_nonce_ajax_callback != 'undefined'
                        && data.wpcf_nonce_ajax_callback == wpcf_nonce_ajax_callback)) {
                    eval(data.execute);
                }
            }
        }
    });
}

/*
 * 
 * 
 * 
 * Since Types 1.1.5 we moved this from
 */

/**
 * Trigger JS
 */
jQuery(document).ready(function(){
    jQuery('.wpcf-cd-fieldset, #wpcf-cd-group').each(function(){
        if (jQuery(this).find('.wpcf-cd-entry').length > 1) {
            jQuery(this).find('.toggle-cd').show();
            jQuery(this).find('.wpcf-cd-relation').show();
        }
    });
});

/**
 * Create conditional statement
 */
function wpcfCdCreateSummary(id) {
    var condition = '';
    var skip = true;
    jQuery('#'+id).parents('fieldset, #wpcf-cd-group').find('.wpcf-cd-entry').each(function(){
        //                if (jQuery(this).parent().find('.wpcf-cd-relation').length > 0) {
        if (!skip) {
            condition += jQuery(this).parent().parent().find('input[type=radio]:checked').val() + ' ';
        }
        skip = false;
        //                }
        condition += '($'+jQuery(this).find('.wpcf-cd-field').val();
        condition += ' ' + jQuery(this).find('.wpcf-cd-operation').val();
        condition += ' ' + jQuery(this).find('.wpcf-cd-value').val() + ') ';
    });
    jQuery('#'+id).val(condition);
}

/**
 * Add New Condition AJAX call
 */
function wpcfCdAddCondition(object, isGroup) {
    if (object.parent().parent().find('.wpcf-cd-entry').length > 0) {
        object.parent().parent().find('.toggle-cd').show();
        object.parent().parent().find('.wpcf-cd-relation').show();
    }
    var url = object.attr('href')+'&count='+object.parent().parent().find('input[type=hidden]').val();
    if (isGroup) {
        url += '&group=1';
    } else {
        url += '&field='+object.parent().parent().attr('id');
    }
    jQuery.get(url, function(data) {
        if (typeof data.output != 'undefined') {
            object.parent().find('.wpcf-cd-entries').append(data.output);
            var count = object.parent().find('input[type=hidden]').val();
            object.parent().find('input[type=hidden]').val(parseInt(count)+1);
        }
    }, "json");
}

/**
 * Remove Condition AJAX call
 */
function wpcfCdRemoveCondition(object) {
    object.parent().fadeOut(function(){
        jQuery(this).remove();
    });
    var count = object.parent().parent().parent().find('input[type=hidden]').val();
    object.parent().parent().parent().find('input[type=hidden]').val(parseInt(count)-1);
    if (object.parent().parent().find('.wpcf-cd-entry').length < 3) {
        var customConditions = object.parent().parent().parent().find('.toggle-cd');
        customConditions.hide().find('.checkbox').removeAttr('checked');
        customConditions.find('.textarea').val('');
        object.parent().parent().parent().find('.wpcf-cd-relation').hide();
    }
}

/**
 * Performed on #post edit pages.
 * @todo Check wpcfConditionalPassed and wpcfConditionalHiddenFailed
 * @todo Loop preventing not needed
 */
function wpcfConditionalInvalidHandler(selector, elements, _form, validator) {
    if (selector.indexOf('#post') !== -1
        && typeof window.wpcfConditionalHiddenCached == 'undefined') {
        
        var form = jQuery(selector);
        var element_id = 0;
        var element = null;
        var passed = new Array();
        var failed = new Array();
        var failedHidden = new Array();

        for (var i = 0; i < elements.length; i++) {
            selector = elements[i];
            element = jQuery('#'+selector);
            /*
             * If element found
             * TODO add debug code
             */
            if (element.length > 0) {
                /*
                 * TODO Check this!
                 * Remove previous data
                 */
                jQuery('#wpcf_conditional_hidden_check_'+element.attr('id')).remove();
                if (wpcfConditionalIsHidden(element)) {
                    window.wpcfConditionalPassed.push(selector);
                    failedHidden.push(selector);
                } else {
                    window.wpcfConditionalHiddenFailed.push(selector);
                    failed.push(selector);
                }
            }
        }
        
        if (failed.length > 0) {
            return false;
        } else if (failedHidden.length > 0) {
            return true;
        }
        return false;
    }
    
    return false;
}

/**
 * Determine if object is conditional and hidden
 */
function wpcfConditionalIsHidden(object) {
    // Check if meta-box is hidden
    /*
     * TODO This is not exact match if meta-box is collapsed
     */
    if (object.parents('.wpcf-conditional').length > 0
        && object.parents('.inside').is(':hidden')) {
        object.parents('.handlediv').trigger('click');
//        return object.delay(500).is(':hidden');
        return false;
    } else {
        return object.parents('.wpcf-conditional').length > 0 && object.is(':hidden');
    }
}

/*
 * TODO Not used?
 */
window.wpcfConditional = new Array();
window.wpcfConditionalPassed = new Array();
window.wpcfConditionalHiddenFailed = new Array();