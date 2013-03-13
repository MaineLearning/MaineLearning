/* 
 * Filter box on fields edit page.
 */
jQuery(document).ready(function(){
    wpcfFieldsFormFiltersSummary();
});
    
function wpcfFieldsFormFiltersSummary() {
    if (jQuery('#wpcf-fields-form-filters-association-form').find("input:checked").val() == 'all') {
        var string = wpcf_filters_association_and;
    } else {
        var string = wpcf_filters_association_or;
    }
    var pt = new Array();
    jQuery('#wpcf-form-fields-post_types').find("input:checked").each(function(){
        pt.push(jQuery(this).next().html());
    });
    var tx = new Array();
    jQuery('#wpcf-form-fields-taxonomies').find("input:checked").each(function(){
        tx.push(jQuery(this).next().html());
    });
    var vt = new Array();
    jQuery('#wpcf-form-fields-templates').find("input:checked").each(function(){
        vt.push(jQuery(this).next().html());
    });
    if (pt.length < 1) {
        pt.push(wpcf_filters_association_all_pages);
    }
    if (tx.length < 1) {
        tx.push(wpcf_filters_association_all_taxonomies);
    }
    if (vt.length < 1) {
        vt.push(wpcf_filters_association_all_templates);
    }
    string = string.replace('%pt%', pt.join(', '));
    string = string.replace('%tx%', tx.join(', '));
    string = string.replace('%vt%', vt.join(', '));
    jQuery('#wpcf-fields-form-filters-association-summary').html(string);
}
    
// Title func
function _wpcfFilterTitle(e, title, title_not_empty, title_empty) {
    if (e == 'empty') {
        return title + ' ' + title_empty;
    } else {
        return title + ' ' + title_not_empty;
    }
}


// Edit Button
//
//
//
function wpcfFilterEditClick(object, edit, title, title_not_empty, title_empty) {
    var parent = object.parents('.wpcf-filter-wrap');
    var toggle = parent.next();
    /*
     * 
     * Built-in filters
     * 
     * 
     * Custom types
     */
    if (edit == 'custom_post_types') {
        
        /*
         *
         * Take a snapshot
         */
        window.wpcfPostTypesText = new Array();
        window.wpcfFormGroupsSupportPostTypesState = new Array();
        toggle.slideToggle().find('.checkbox').each(function(index){
            if (jQuery(this).is(':checked')) {
                window.wpcfPostTypesText.push(jQuery(this).next().html());
                window.wpcfFormGroupsSupportPostTypesState.push(jQuery(this).attr('id'));
            }
        });
    /*
         *
         *
         *
         *
         *
         *
         * Do taxonomies
         */
    } else if (edit == 'custom_taxonomies') {
        /*
         *
         * Take a snapshot
         */
        window.wpcfTaxText = new Array();
        window.wpcfFormGroupsSupportTaxState = new Array();
        toggle.slideToggle().find('.checkbox').each(function(index){
            if (jQuery(this).is(':checked')) {
                window.wpcfTaxText.push(jQuery(this).next().html());
                window.wpcfFormGroupsSupportTaxState.push(jQuery(this).attr('id'));
            }
        });
    } else if (edit == 'templates') {
        window.wpcfTemplatesText = new Array();
        window.wpcfFormGroupsTemplatesState = new Array();
        toggle.slideToggle().find('.checkbox').each(function(index){
            if (jQuery(this).is(':checked')) {
                window.wpcfTemplatesText.push(jQuery(this).next().html());
                window.wpcfFormGroupsTemplatesState.push(jQuery(this).attr('id'));
            }
        });
        jQuery(this).css('visibility', 'hidden');
    }
    
    // Hide until OK or Cancel
    object.css('visibility', 'hidden');
}






// OK Button
//
//
//
//
function wpcfFilterOkClick(object, edit, title, title_not_empty, title_empty) {
    var toggle = object.parent();
    var parent = toggle.prev('.wpcf-filter-wrap');
    /*
     * 
     * Built-in filters
     * 
     * 
     * 
     * 
     * Do custom post types
     */
    if (edit == 'custom_post_types') {
        
        /*
         * 
         * Take a snapshot of current state
         */
        window.wpcfPostTypesText = new Array();
        window.wpcfFormGroupsSupportPostTypesState = new Array();
        toggle.slideUp().find('.checkbox').each(function(index){
            if (jQuery(this).is(':checked')) {
                window.wpcfPostTypesText.push(jQuery(this).next().html());
                window.wpcfFormGroupsSupportPostTypesState.push(jQuery(this).attr('id'));
            }
        });
        
        /*
         * 
         * 
         * Set TEXT
         */
        if (window.wpcfPostTypesText.length < 1) { 
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('empty', title, title_not_empty, title_empty)
                );
        } else {
            var title_not_empty = wpcfPostTypesText.join(', ');
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('has', title, title_not_empty, title_empty)    
                );
        };
    
    /*
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * Now do taxonomies
         */

    } else if (edit == 'custom_taxonomies') {
        /*
         * 
         * Take a snapshot of current state
         */
        window.wpcfTaxText = new Array();
        window.wpcfFormGroupsSupportTaxState = new Array();
        toggle.slideToggle().find('.checkbox').each(function(index){
            if (jQuery(this).is(':checked')) {
                window.wpcfTaxText.push(jQuery(this).next().html());
                window.wpcfFormGroupsSupportTaxState.push(jQuery(this).attr('id'));
            }
        });
        
        /*
         *
         * Set TEXT
         */
        if (window.wpcfTaxText.length < 1) {
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('empty', title, title_not_empty, title_empty)
                );
        } else {
            title_not_empty = window.wpcfTaxText.join(', ');
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('has', title, title_not_empty, title_empty)    
                );
        }
    /*
         *
         *
         *
         *
         *
         *
         * Do templates
         */
    } else if (edit == 'templates') {
        /*
         *
         * Take snaphot
         */
        window.wpcfTemplatesText = new Array();
        window.wpcfFormGroupsTemplatesState = new Array();
        toggle.slideUp().find('.checkbox').each(function(index){
            if (jQuery(this).is(':checked')) {
                window.wpcfTemplatesText.push(jQuery(this).next().html());
                window.wpcfFormGroupsTemplatesState.push(jQuery(this).attr('id'));
            }
        });
        /*
         *
         *
         * Set title
         */
        if (window.wpcfTemplatesText.length < 1) {
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('empty', title, title_not_empty, title_empty)
                );
        } else {
            title_not_empty = window.wpcfTemplatesText.join(', ');
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('has', title, title_not_empty, title_empty)    
                );
        }
            
    }
    
    parent.children('a').css('visibility', 'visible');
}

// CANCEL Button
//
//
//
//
function wpcfFilterCancelClick(object, edit, title, title_not_empty, title_empty) {
    var toggle = object.parent();
    var parent = toggle.prev('.wpcf-filter-wrap');
    /*
     * 
     * Built-in filters
     * 
     * 
     * 
     * 
     * Do custom post types
     */
    if (edit == 'custom_post_types') {
        /*
         *
         *
         * Take a snaphot
         */
        toggle.slideUp().find('input').removeAttr('checked');
        if (window.wpcfFormGroupsSupportPostTypesState.length > 0) {
            for (var element in window.wpcfFormGroupsSupportPostTypesState) {
                jQuery('#'+window.wpcfFormGroupsSupportPostTypesState[element])
                .attr('checked', 'checked');
            }
        }
        /*
         *
         *
         * Set title
         */
        if (window.wpcfPostTypesText.length > 0) {
            title_not_empty = window.wpcfPostTypesText.join(', ');
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('has', title, title_not_empty, title_empty)
                );
        } else {
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('empty', title, title_not_empty, title_empty)
                );
        }
    /*
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * Now do taxonomies
         */
    } else if (edit == 'custom_taxonomies') {
        /*
         *
         *
         * Take a snaphot
         */
        toggle.slideUp().find('input').removeAttr('checked');
        if (window.wpcfFormGroupsSupportTaxState.length > 0) {
            for (var element in window.wpcfFormGroupsSupportTaxState) {
                jQuery('#'+window.wpcfFormGroupsSupportTaxState[element])
                .attr('checked', 'checked');
            }
        }
        /*
         *
         *
         * Set title
         */
        if (window.wpcfTaxText.length > 0) {
            title_not_empty = window.wpcfTaxText.join(', ');
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('has', title, title_not_empty, title_empty)
                );
        } else {
            parent.find('.wpcf-filter-ajax-response').html(
                _wpcfFilterTitle('empty', title, title_not_empty, title_empty)
                );
        }
    /*
         * 
         * 
         * 
         * 
         * 
         * 
         * 
         * Do templates
         */
    } else if (edit == 'templates') {
        toggle.slideUp().find('input').removeAttr('checked');
        if (window.wpcfFormGroupsTemplatesState.length > 0) {
            for (var element in window.wpcfFormGroupsTemplatesState) {
                jQuery('#'+window.wpcfFormGroupsTemplatesState[element])
                .attr('checked', 'checked');
            }
        }
        if (window.wpcfTemplatesText.length > 0) {
            title_not_empty = window.wpcfTemplatesText.join(', ');
            parent.find('.wpcf-filter-ajax-response')
            .html(_wpcfFilterTitle('has', title, title_not_empty, title_empty));
        } else {
            parent.find('.wpcf-filter-ajax-response')
            .html(_wpcfFilterTitle('empty', title, title_not_empty, title_empty));
        }
    }
    
    parent.children('a').css('visibility', 'visible');
}