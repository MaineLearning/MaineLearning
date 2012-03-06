function wpv_view_filter_meta_html() {
    jQuery('#wpv_filter_meta_html_admin_show').hide();
    jQuery('#wpv_filter_meta_html_admin_edit').show();
}

function wpv_view_filter_meta_html_close() {
    jQuery('#wpv_filter_meta_html_admin_show').show();
    jQuery('#wpv_filter_meta_html_admin_edit').hide();

    jQuery('#wpv_filter_meta_html_content_error').hide();
    
}


jQuery(document).ready(function($){
    jQuery('#wpv_filter_meta_html_content').keyup(function(event) {
        jQuery('#wpv_filter_meta_html_notice').show();
    });
});

function wpv_filter_meta_html_generate_new() {
    jQuery('#wpv_filter_meta_html_content_old').val(jQuery('#wpv_filter_meta_html_content').val());
    jQuery('#wpv_filter_meta_html_content_old_div').show();
    jQuery('#wpv_filter_meta_html_content_error').hide();
    on_generate_wpv_filter(true);
}

function wpv_filter_meta_html_old_dismiss() {
    jQuery('#wpv_filter_meta_html_content_old_div').hide();
}