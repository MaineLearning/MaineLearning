function wpv_get_pagination_code() {
    var controls = ''

    var pagination = jQuery('input[name=_wpv_settings\\[pagination\\]\\[\\]]:checked').val(); 
    var mode = jQuery('select[name=_wpv_settings\\[pagination\\]\\[mode\\]]').val();

    if (pagination == 'enable') {    
        if (mode == 'paged') {
            controls += "[wpv-pagination]\n";
            var page_out = page_x_of_n;
            if (jQuery('#_wpv_settings_include_page_selector_control').attr('checked')) {
                var type = jQuery('select[name="_wpv_settings\\[pagination\\]\\[page_selector_control_type\\]"]').val();
                switch (type) {
                    case 'drop_down':
                        page_out = page_out.replace('1', '[wpv-pager-current-page style="drop_down"]');
                        break;
                    
                    case 'link':
                        page_out = '[wpv-pager-current-page style="link"]';
                        break;
                }
            } else {
                page_out = page_out.replace('1', '[wpv-pager-current-page]');
            }
            page_out = page_out.replace('9', '[wpv-pager-num-page]');
            controls += "<p>" + page_out + ' ';

            if (jQuery('#_wpv_settings_include_prev_next_page_controls').attr('checked')) {
                controls += '[wpv-pager-prev-page]' + page_previous + '[/wpv-pager-prev-page]';
                controls += ' ';
                controls += '[wpv-pager-next-page]' + page_next + '[/wpv-pager-next-page]';
            }
            controls += "</p>\n";
            controls += "[/wpv-pagination]\n";
        }
        
    }
    if (mode == 'rollover') {
        if (jQuery('input[name="_wpv_settings\\[rollover\\]\\[include_page_selector\\]"]').attr('checked')) {
            controls += "[wpv-pagination]\n";
            controls += '[wpv-pager-current-page style="link"]\n';
            controls += "[/wpv-pagination]\n";
        }
    }
    
    return controls;
}

var post_per_page;
var include_page_selector;
var page_selector_type;
var include_prev_next;
var previous_pagination; 
var ajax_previous_pagination; 

function wpv_pagination_edit() {
    // record the state so we can cancel.
    
    post_per_page = jQuery('select[name="_wpv_settings\\[posts_per_page\\]"]').val();
    include_page_selector = jQuery('input[name="_wpv_settings\\[include_page_selector_control\\]"]').attr('checked');
    page_selector_type = jQuery('select[name="_wpv_settings\\[pagination\\]\\[page_selector_control_type\\]"]').val();
    include_prev_next = jQuery('input[name="_wpv_settings\\[include_prev_next_page_controls\\]"]').attr('checked');
    previous_pagination = jQuery('input[name=_wpv_settings\\[pagination\\]\\[\\]]:checked'); 
    ajax_previous_pagination = jQuery('input[name=_wpv_settings\\[ajax_pagination\\]\\[\\]]:checked'); 
    
    
    jQuery('#wpv_pagination_admin_edit').show();
    jQuery('#wpv_pagination_admin_show').hide();
}

function wpv_pagination_edit_ok() {
    
    var data = jQuery('#post').serialize();
    var add_data = '&action=wpv_pagination&wpv_nonce='+jQuery('#wpv_pagination_nonce').attr('value');
    data = data+add_data;
    
    jQuery.ajaxSetup({async:false});
    jQuery.post(ajaxurl, data, function(response) {
        jQuery('#wpv_pagination_admin').html(response);
        
    });
    
    jQuery('#wpv_pagination_admin_show').show();
    jQuery('#wpv_pagination_admin_edit').hide();
    
    on_generate_wpv_filter(false);
}

function wpv_pagination_edit_cancel() {
    jQuery('select[name="_wpv_settings\\[posts_per_page\\]"]').val(post_per_page);
    jQuery('input[name="_wpv_settings\\[include_page_selector_control\\]"]').attr('checked', false);
    jQuery('input[name="_wpv_settings\\[include_prev_next_page_controls\\]"]').attr('checked', false);
    
    if(include_page_selector) {
        jQuery('input[name="_wpv_settings\\[include_page_selector_control\\]"]').attr('checked', true);
    }
    if(include_prev_next) {
        jQuery('input[name="_wpv_settings\\[include_prev_next_page_controls\\]"]').attr('checked', true);
    }

    jQuery('input[name=_wpv_settings\\[pagination\\]\\[\\]]').each( function(index) {
        jQuery(this).attr('checked', false); 
    });
    previous_pagination.attr('checked', true);
    
    jQuery('input[name=_wpv_settings\\[ajax_pagination\\]\\[\\]]').each( function(index) {
        jQuery(this).attr('checked', false); 
    });
    ajax_previous_pagination.attr('checked', true);
    
    
    jQuery('#wpv_pagination_admin_show').show();
    jQuery('#wpv_pagination_admin_edit').hide();
}

