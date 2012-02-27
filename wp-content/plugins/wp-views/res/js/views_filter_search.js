
var previous_search_text = '';
var previous_search_mode;


function wpv_show_filter_search_edit() {
    previous_search_text = jQuery('input[name="_wpv_settings\\[post_search_value\\]"]').val();
    previous_search_mode = jQuery('input[name=_wpv_settings\\[search_mode\\]\\[\\]]:checked'); 
    
    jQuery('#wpv-filter-search-edit').parent().parent().css('background-color', jQuery('#wpv-filter-search-edit').css('background-color'));

    jQuery('#wpv-filter-search-edit').show();
    jQuery('#wpv-filter-search-show').hide();
}

function wpv_show_filter_search_edit_ok() {

    // find the filter row in the table.
    var tr = jQuery('#wpv-filter-search-show').parent().parent();
    var row = tr.attr('id').substr(15);
    
    var data = {
        action : 'wpv_get_table_row_ui',
        type_data : 'post_search',
        row : row,
        search : jQuery('input[name="_wpv_settings\\[post_search_value\\]"]').val(),
        mode : jQuery('input[name=_wpv_settings\\[search_mode\\]\\[\\]]:checked').val(),
        wpv_nonce : jQuery('#wpv_get_table_row_ui_nonce').attr('value')
    };
    
    var td = '';
    jQuery.post(ajaxurl, data, function(response) {
        td = response;
        jQuery('#wpv_filter_row_' + row).html(td);
        jQuery('#wpv-filter-search-edit').parent().parent().css('background-color', '');
        jQuery('#wpv-filter-search-edit').hide();
        jQuery('#wpv-filter-search-show').show();
        on_generate_wpv_filter();
    });

}

function wpv_show_filter_search_edit_cancel() {

    jQuery('input[name="_wpv_settings\\[post_search_value\\]"]').val(previous_search_text);
    
    jQuery('input[name=_wpv_settings\\[search_mode\\]\\[\\]]').each( function(index) {
        jQuery(this).attr('checked', false); 
    });
    previous_search_mode.attr('checked', true);

    
    jQuery('#wpv-filter-search-edit').parent().parent().css('background-color', '');
    jQuery('#wpv-filter-search-edit').hide();
    jQuery('#wpv-filter-search-show').show();
}

function wpv_search_box_code() {
    if (jQuery('input[name=_wpv_settings\\[search_mode\\]\\[\\]]:checked').val() == 'visitor') {
        return '[wpml-string context="wpv-views"]Search: [wpv-filter-search-box][/wpml-string]\n';
    } else {
        return '';
    }
}

