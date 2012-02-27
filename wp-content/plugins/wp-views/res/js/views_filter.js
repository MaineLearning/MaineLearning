jQuery(document).ready(function($){
    var c = jQuery('textarea#wpv_filter_meta_html_content').val();
    
    if (c == '') {

        var data = wpv_get_filter_code();
    
        c = add_wpv_filter_data_to_content(c, data);
        jQuery('textarea#wpv_filter_meta_html_content').val(c);
        jQuery('textarea#wpv_generated_filter_meta_html_content').val(c);
    }
    
    // remove the "Save Draft" and "Preview" buttons.
    jQuery('#minor-publishing-actions').hide();
    jQuery('#misc-publishing-actions').hide();
    jQuery('#publishing-action input[name=publish]').val(wpv_save_button_text);
    
    jQuery('input[name=wpv_duplicate_view]').click(wpv_duplicate_view_click);
    wpv_duplicate_view_click();

    
});

function wpv_add_initial_filter_shortcode() {
    c = jQuery('textarea#content').val();
    if (c == '') {
        c += '[wpv-filter-meta-html]\n[wpv-layout-meta-html]\n';
        jQuery('textarea#content').val(c);
    }    
}

function on_generate_wpv_filter(force) {
    
    jQuery('#wpv_generating_filter').show();
    
    var data = wpv_get_filter_code();

    var c = jQuery('textarea#wpv_filter_meta_html_content').val();
    
    if (force || check_if_previous_filter_has_changed(c)) {
    
        c = add_wpv_filter_data_to_content(c, data);
        jQuery('textarea#wpv_filter_meta_html_content').val(c);
    }
    
    // save the generated value so we can compare later.
    jQuery('textarea#wpv_generated_filter_meta_html_content').val(data);
    
    jQuery('#wpv_generating_filter').hide();
}

function add_wpv_filter_data_to_content(c, data) {
    if (c.search(/\[wpv-filter-start.*?\][\s\S]*\[wpv-filter-end]/g) == -1) {
        // not there so we need to add to the start.
        c = data + c;
    } else {
        c = c.replace(/\[wpv-filter-start.*?\][\s\S]*\[wpv-filter-end]/g, data);
    }
    
    return c;
}
    


function wpv_get_filter_code() {
    
    var controls = '';
    jQuery('#wpv_filter_table').find('.wpv_interface_select').each(function(){
        if (jQuery(this).val() != 'none') {
            var name = jQuery(this).attr('name');
            var output_text = jQuery(this).attr('output_text');
            var short_code = jQuery(this).attr('short_code');
            
            if (this.tagName == 'SELECT') {
                controls += output_text + ' [' + short_code + ' style="' + jQuery(this).val() + '"]\n<br />\n';
            } else if (this.tagName == 'INPUT' && jQuery(this).attr('checked')) {
                controls += output_text + ' [' + short_code + ']\n<br />\n';
            }
        }
    });
    
    controls += wpv_search_box_code();
    
    var no_user_controls = controls.length == 0;

    controls += wpv_get_pagination_code();
    
    var out = '';

    var no_controls = controls.length == 0;
    
    controls = '\n' + controls;
    
    if (no_user_controls) {
        controls += '[wpv-filter-submit name="Apply" hide="true"]\n';
    } else {
        controls += '[wpv-filter-submit name="Apply"]\n';
    }
        
    if (no_controls) {
        // hide the form if there are know other controls.
        out += '[wpv-filter-start hide="true"]';
    } else {
        out += '[wpv-filter-start hide="false"]';
    }
        
    out += controls;
        
    out += '[wpv-filter-end]';
    
    return out;
    
}

function check_if_previous_filter_has_changed(body) {
    // find the filter info
    var match = /\[wpv-filter-start.*?\]([\s\S]*)\[wpv-filter-end\]/.exec(body);
    
    var original = jQuery('textarea#wpv_generated_filter_meta_html_content').val();
    var match_original = /\[wpv-filter-start.*?\]([\s\S]*)\[wpv-filter-end\]/.exec(original);
        
    if (match && match_original) {
        if (match_original[1] != match[1]) {
            // something has changed
            jQuery('#wpv_filter_meta_html_content_error').show();
            wpv_view_filter_meta_html();
            return false;
        }
    }

    jQuery('#wpv_filter_meta_html_content_error').hide();
    return true;
}

var post_types_selected = Array();
function wpv_show_post_type_edit() {

    // record checked items just in case the operation is cancelled.    
    post_types_selected = jQuery('input[name="_wpv_settings\\[post_type\\]\\[\\]"]:checked');
    
    jQuery('#wpv-filter-post-type-edit').parent().parent().css('background-color', jQuery('#wpv-filter-post-type-edit').css('background-color'));
    
    jQuery('#wpv-filter-post-type-edit').show();
    jQuery('#wpv-filter-post-type-show').hide();
}

function wpv_show_post_type_edit_ok() {
    
    // get selected post types.
    var selected = new Array;
    jQuery('input[name="_wpv_settings\\[post_type\\]\\[\\]"]:checked').each( function(index) {
        selected.push(jQuery(this).attr('value'));
    });

    // get the order by
    var orderby = jQuery('select[name=_wpv_settings\\[orderby\\]]').val();
    var order = jQuery('select[name=_wpv_settings\\[order\\]]').val();
    
    var data = {
        action : 'wpv_get_post_filter_summary',
        selected : selected,
        orderby : orderby,
        order : order,
        wpv_nonce : jQuery('#wpv_post_filter_nonce').attr('value')
        
    };
    
    jQuery.post(ajaxurl, data, function(response) {
        
        jQuery('#wpv-filter-post-type-edit').parent().parent().css('background-color', '');
        jQuery('#wpv-filter-post-type-show').html(response);
        jQuery('#wpv-filter-post-type-edit').hide();
        jQuery('#wpv-filter-post-type-show').show();
    });
    
}

function wpv_show_post_type_edit_cancel() {
    // uncheck any that may have been checked.
    
    jQuery('input[name="_wpv_settings\\[post_type\\]\\[\\]"]').each( function(index) {
        jQuery(this).attr('checked', false);
    });
    post_types_selected.each( function(index) {
        jQuery(this).attr('checked', true);
    });
    
    jQuery('#wpv-filter-post-type-edit').parent().parent().css('background-color', '');
    jQuery('#wpv-filter-post-type-edit').hide();
    jQuery('#wpv-filter-post-type-show').show();
}

function wpv_filter_show_edit_mode(id) {
    jQuery('#wpv-filter-' + id + '-edit').parent().parent().css('background-color', jQuery('#wpv-filter-' + id + '-edit').css('background-color'));
    
    jQuery('#wpv-filter-' + id + '-edit').show();
    jQuery('#wpv-filter-' + id + '-show').hide();
   
}

function wpv_hide_filter_edit_mode(id) {
    jQuery('#wpv-filter-' + id + '-edit').parent().parent().css('background-color', '');
    jQuery('#wpv-filter-' + id + '-edit').hide();
    jQuery('#wpv-filter-' + id + '-show').show();
}

var post_status_selected = Array();
function wpv_show_filter_status_edit() {

    // record checked items just in case the operation is cancelled.    
    post_status_selected = jQuery('input[name="_wpv_settings\\[post_status\\]\\[\\]"]:checked');
    
    wpv_filter_show_edit_mode('status');
}

function wpv_show_filter_status_edit_ok() {
    
    // get selected post status.
    var selected = new Array;
    jQuery('input[name="_wpv_settings\\[post_status\\]\\[\\]"]:checked').each( function(index) {
        selected.push(jQuery(this).attr('value'));
    });

    // find the filter row in the table.
    var tr = jQuery('#wpv-filter-status-show').parent().parent();
    var row = tr.attr('id').substr(15);

    var data = {
        action : 'wpv_get_table_row_ui',
        type_data : 'post_status',
        row : row,
        checkboxes : selected,
        wpv_nonce : jQuery('#wpv_get_table_row_ui_nonce').attr('value')
    };

    var td = '';
    jQuery.post(ajaxurl, data, function(response) {
        td = response;
        jQuery('#wpv_filter_row_' + row).html(td);
        wpv_hide_filter_edit_mode('status');
    });
   
}

function wpv_show_filter_status_edit_cancel() {
    // uncheck any that may have been checked.
    
    jQuery('input[name="_wpv_settings\\[post_status\\]\\[\\]"]').each( function(index) {
        jQuery(this).attr('checked', false);
    });
    post_status_selected.each( function(index) {
        jQuery(this).attr('checked', true);
    });
    
    wpv_hide_filter_edit_mode('status');
}

function wpv_duplicate_view_click() {
    
    
    if (jQuery('.wpv_duplicate_from_original').is(":checked")) {
        jQuery('#wpv_view_query_controls_over').show();
        jQuery('#wpv_view_layout_controls_over').show();
    } else {
        jQuery('#wpv_view_query_controls_over').hide();
        jQuery('#wpv_view_layout_controls_over').hide();
    }
    
}