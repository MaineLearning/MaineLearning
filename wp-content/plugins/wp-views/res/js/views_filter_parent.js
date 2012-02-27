var previous_parent_mode;
var previous_parent_id;
var previous_post_type;

function wpv_show_filter_parent_edit() {

    previous_parent_mode = jQuery('input[name=_wpv_settings\\[parent_mode\\]\\[\\]]:checked');
    previous_parent_id = jQuery('select[name=_wpv_settings\\[parent_id\\]]').val();
    previous_post_type = jQuery('#wpv_parent_post_type').val();
    
    jQuery('#wpv-filter-parent-edit').parent().parent().css('background-color', jQuery('#wpv-filter-parent-edit').css('background-color'));

    jQuery('#wpv-filter-parent-edit').show();
    jQuery('#wpv-filter-parent-show').hide();
    
    jQuery(document).ready(function($){
        jQuery('#wpv_parent_post_type').change(wpv_on_post_parent_change);
        
    });
    
}

                                               
function wpv_on_post_parent_change() {
     // Update the parents for the selected type.
     var data = {
         action : 'wpv_get_posts_select',
         post_type : jQuery('#wpv_parent_post_type').val(),
         wpv_nonce : jQuery('#wpv_get_posts_select_nonce').attr('value')
     };
     
     jQuery('#wpv_update_parent').show();
     jQuery.post(ajaxurl, data, function(response) {
         jQuery('select[name=_wpv_settings\\[parent_id\\]]').remove();
         jQuery('#wpv_parent_post_type').after(response);
         jQuery('#wpv_update_parent').hide();
     });
}

function wpv_on_post_parent_change_add() {
     // Update the parents for the selected type.
     // This is for in the popup.
     var data = {
         action : 'wpv_get_posts_select',
         post_type : jQuery('#wpv_parent_post_type_add').val(),
         wpv_nonce : jQuery('#wpv_get_posts_select_nonce').attr('value')
     };
     
     jQuery('#wpv_update_parent').show();
     jQuery.post(ajaxurl, data, function(response) {
         jQuery('select[name=wpv_parent_id_add]').remove();
         response = response.replace('_wpv_settings[parent_id]', 'wpv_parent_id_add');
         jQuery('#wpv_parent_post_type_add').after(response);
         jQuery('#wpv_update_parent').hide();
     });
}


function wpv_show_filter_parent_edit_ok() {

    // find the filter row in the table.
    var tr = jQuery('#wpv-filter-parent-show').parent().parent();
    var row = tr.attr('id').substr(15);
    
    var data = {
        action : 'wpv_get_table_row_ui',
        type_data : 'post_parent',
        row : row,
        parent_mode : jQuery('input[name=_wpv_settings\\[parent_mode\\]\\[\\]]:checked').val(),
        parent_id : jQuery('select[name=_wpv_settings\\[parent_id\\]]').val(),
        wpv_nonce : jQuery('#wpv_get_table_row_ui_nonce').attr('value')
    };
    
    var td = '';
    jQuery.post(ajaxurl, data, function(response) {
        td = response;
        jQuery('#wpv_filter_row_' + row).html(td);
        jQuery('#wpv-filter-parent-edit').parent().parent().css('background-color', '');
        jQuery('#wpv-filter-parent-edit').hide();
        jQuery('#wpv-filter-parent-show').show();
        on_generate_wpv_filter();
    });

}

function wpv_show_filter_parent_edit_cancel() {

    jQuery('input[name=_wpv_settings\\[parent_mode\\]\\[\\]]').each( function(index) {
        jQuery(this).attr('checked', false); 
    });
    previous_parent_mode.attr('checked', true);
    
    if (jQuery('#wpv_parent_post_type').val() != previous_post_type) {
        var data = {
            action : 'wpv_get_posts_select',
            post_type : previous_post_type,
            wpv_nonce : jQuery('#wpv_get_posts_select_nonce').attr('value')
        };
        
        jQuery.ajaxSetup({async:false});
        jQuery.post(ajaxurl, data, function(response) {
            jQuery('select[name=_wpv_settings\\[parent_id\\]]').remove();
            jQuery('#wpv_parent_post_type').after(response);
            jQuery('#wpv_parent_post_type').val(previous_post_type);
            jQuery('select[name=_wpv_settings\\[parent_id\\]]').val(previous_parent_id);
        });
    } else {
        jQuery('select[name=_wpv_settings\\[parent_id\\]]').val(previous_parent_id);
    }

    jQuery('#wpv-filter-parent-edit').parent().parent().css('background-color', '');
    jQuery('#wpv-filter-parent-edit').hide();
    jQuery('#wpv-filter-parent-show').show();
}


jQuery(document).ready(function($){
    jQuery('#wpv_parent_post_type_add').change(wpv_on_post_parent_change_add);
    
});
    