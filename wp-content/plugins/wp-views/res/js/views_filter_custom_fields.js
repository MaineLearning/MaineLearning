
var custom_field_rows = Array();

function wpv_show_filter_custom_field_edit() {
    
    // record the custom field rows so we can undo changes on cancel.
    custom_field_rows = Array();
    jQuery('.wpv_custom_field_edit_row').each( function(index) {
        custom_field_rows[jQuery(this).attr('id')] = jQuery(this).html();
    });
    
    jQuery('input[name="Add another filter term"]').hide();
    
    jQuery('.wpv_custom_field_show_row').hide();
    jQuery('.wpv_custom_field_edit_row').show();
    
    wpv_initialize_filter_select('popup_add_custom_field');    
}

function wpv_show_filter_custom_field_edit_ok() {
    wpv_add_edit_custom_field('', '', 'edit');
    jQuery('.wpv_add_filters_button').show();
}

function wpv_show_filter_custom_field_edit_cancel() {
    // undo any changes by restoring the custom field rows
    for(var index in custom_field_rows) {
        jQuery('#' + index).html(custom_field_rows[index]);
        jQuery('#' + index).attr('class', 'wpv_custom_field_edit_row');
    }
    
    jQuery('.wpv_add_filters_button').show();
    jQuery('.wpv_custom_field_show_row').show();
    jQuery('.wpv_custom_field_edit_row').hide();
}

function wpv_add_edit_custom_field(div_id, type, mode) {
    String.prototype.startsWith = function (str){
        return this.indexOf(str) == 0;
    };
    String.prototype.endsWith = function (str){
        return this.slice(-str.length) == str;
    };


    // get existing custom field data
    var custom_fields_name = Array();
    var custom_fields_compare = Array();
    var custom_fields_type = Array();
    var custom_fields_value = Array();
    jQuery('select').each( function(index) {
        if (mode == 'add' || jQuery(this).is(":visible")) {
            var name = jQuery(this).attr('name');
            if (name && name.startsWith('_wpv_settings[custom-field-') && name.endsWith('_compare]')) {
                custom_fields_name.push(name.slice(27, -9));
                name = name.slice(0, -8);
                name = name.replace('[', '\\[');
                custom_fields_compare.push(jQuery('select[name="' + name + 'compare\\]"]').val());
                custom_fields_type.push(jQuery('select[name="' + name + 'type\\]"]').val());
                custom_fields_value.push(jQuery('input[name="' + name + 'value\\]"]').val());
            }
        }        
    });

    if (type != '') {
        // get the new custom field data
        
        var type_temp = type.replace('[', '\\[');
        type_temp = type_temp.replace(']', '\\]');
        if (jQuery('#TB_ajaxContent select[name=' + type_temp + '_compare]').length) {
            custom_fields_name.push(type_temp.slice(13));
            custom_fields_compare.push(jQuery('#TB_ajaxContent select[name=' + type_temp + '_compare]').val());
            custom_fields_type.push(jQuery('#TB_ajaxContent select[name=' + type_temp + '_type]').val());
            custom_fields_value.push(jQuery('#TB_ajaxContent input[name=' + type_temp + '_value]').val());
        }
    }
    
    var temp_index = -1;
    jQuery('tr.wpv_filter_row').each( function(index) {
        var this_row = jQuery(this).attr('id');
        this_row = parseInt(this_row.substr(15));
        if (this_row > temp_index) {
            temp_index = this_row;
        }
    });
    
    
    // add the custom field relationship
    var custom_fields_relationship = 'OR';
    if(jQuery('select[name="_wpv_settings\\[custom_fields_relationship\\]"]').length) {
        custom_fields_relationship = jQuery('select[name="_wpv_settings\\[custom_fields_relationship\\]"]').val();
    }
    
    var data = {
        action : 'wpv_add_custom_field',
        custom_fields_name : custom_fields_name,
        custom_field_rows : custom_field_rows,
        custom_fields_compare : custom_fields_compare,
        custom_fields_type : custom_fields_type,
        custom_fields_value : custom_fields_value,
        custom_fields_relationship : custom_fields_relationship,
        row : temp_index + 1,
        wpv_nonce : jQuery('#wpv_add_custom_field_nonce').attr('value')
    };
    
    jQuery.ajaxSetup({async:false});
    jQuery.post(ajaxurl, data, function(response) {

        tb_remove();
        
        jQuery('.wpv_custom_field_edit_row').each( function(index) {
            jQuery(this).remove(); 
        });
        jQuery('.wpv_custom_field_show_row').each( function(index) {
            jQuery(this).remove(); 
        });
        
        
        if (div_id == 'popup_add_custom_field') {
            jQuery('#' + div_id).remove();
            jQuery('#' + div_id + '_controls').remove();
        }
        
        jQuery('#wpv_filter_table').append(response);
        
        if (mode == 'add') {
            // re-open the edit mode.
            wpv_show_filter_custom_field_edit();
        }
        
        wpv_update_custom_fields_in_select('popup_add_filter_select');
        wpv_initialize_filter_select('popup_add_filter');    
        wpv_update_custom_fields_in_select('popup_add_custom_field_select');
        wpv_initialize_filter_select('popup_add_custom_field');    
    });
}

function wpv_update_custom_fields_in_select(select_id) {
    
    // first set all the custom fields in the select to be shown.
    jQuery('#' + select_id + ' option').each(function(index) {
        if (jQuery(this).val().substr(0, 13) == 'custom-field-') {
            jQuery(this).show();
        }
    });
    
    jQuery('.wpv_custom_field_edit_row select').each(function(index) {
        if(jQuery(this).attr('name').slice(-6) == '_type]') {
            var field_name = jQuery(this).attr('name').slice(27, -6);
            jQuery('#' + select_id + ' option[value=custom-field-' + field_name + ']').hide();
        }
    });
}