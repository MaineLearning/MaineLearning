function wpv_view_filter_meta_html() {
    jQuery('#wpv_filter_meta_html_admin_show').hide();
    jQuery('#wpv_filter_meta_html_admin_edit').show();
    jQuery('#wpv_filter_meta_html_state').val('on');

    HTMLEditor['wpv_filter_meta_html_content'].refresh();
    HTMLEditor['wpv_filter_meta_html_content'].focus();
	// See if there are user controls and submit button is hidden.
    // change to work with CodeMirror
    // var c = jQuery('textarea#wpv_filter_meta_html_content').val();
    var c = HTMLEditor['wpv_filter_meta_html_content'].getValue();
	if (c.search(/\[wpv-control.*?\]/g) != -1) {
		if (c.search(/\[wpv-filter-submit[^\]]*?hide="true"/) != -1) {
 	
			wpv_filter_submit_hidden_warning();
		}
	}
}
function wpv_view_filter_meta_html_close() {
    jQuery('#wpv_filter_meta_html_admin_show').show();
    jQuery('#wpv_filter_meta_html_admin_edit').hide();
    jQuery('#wpv_filter_meta_html_state').val('off');

    jQuery('#wpv_filter_meta_html_content_error').hide();
    jQuery('#wpv_filter_control_meta_html_content_error').hide();
    
}

function wpv_view_filter_meta_html_extra(where) {
    var par = jQuery(where).parent().attr('id');
    jQuery('#' + par + '_edit').css({'height': 'auto', 'padding': '0 10px'});
    jQuery(where).hide();
    jQuery('#' + par + '_close').show();
    jQuery('#' + par + '_state').val('on');
}

function wpv_view_filter_meta_html_extra_css_close() {
    jQuery('#wpv_filter_meta_html_extra_css_edit').css({'height': '0', 'overflow': 'hidden', 'padding': '0'});
    jQuery('.wpv_filter_meta_html_extra_css_edit').show();
    jQuery('#wpv_filter_meta_html_extra_css_state').val('off');
}

function wpv_view_filter_meta_html_extra_js_close() {
    jQuery('#wpv_filter_meta_html_extra_js_edit').css({'height': '0', 'overflow': 'hidden', 'padding': '0'});
    jQuery('.wpv_filter_meta_html_extra_js_edit').show();
    jQuery('#wpv_filter_meta_html_extra_js_state').val('off');
}

function wpv_view_filter_meta_html_extra_img_close() {
	jQuery('#wpv_filter_meta_html_extra_img_edit').css({'height': '0', 'overflow': 'hidden', 'padding': '0'});
	jQuery('.wpv_filter_meta_html_extra_img_edit').show();
	jQuery('#wpv_filter_meta_html_extra_img_state').val('off');
}

jQuery(document).ready(function($){
    jQuery('#wpv_filter_meta_html_content').keyup(function(event) {
        jQuery('#wpv_filter_meta_html_notice').show();
       	show_view_changed_message();
    });
    
    HTMLEditor['wpv_filter_meta_html_content'] = CodeMirror.fromTextArea(document.getElementById("wpv_filter_meta_html_content"), {mode: "myshortcodes", tabMode: "indent", lineWrapping: true, lineNumbers: true, autofocus:true});
    HTMLEditor['wpv_filter_meta_html_content'].refresh();
    HTMLEditor['wpv_filter_meta_html_content'].focus();
    
    jQuery('#wpv_filter_meta_html_content').bind('paste', function(e){
        if(HTMLCodeMirrorActive){
            InsertAtCursor(this.value, 'wpv_filter_meta_html_content');
        }
    });
    
    if ('on' == jQuery('#wpv_filter_meta_html_state').val()) {
      wpv_view_filter_meta_html();
    }
    
    jQuery('#wpv_filter_meta_html_extra_css_edit').css({'height': '0', 'overflow': 'hidden', 'padding': '0'});
    jQuery('#wpv_filter_meta_html_extra_js_edit').css({'height': '0', 'overflow': 'hidden', 'padding': '0'});
    jQuery('#wpv_filter_meta_html_extra_img_edit').css({'height': '0', 'overflow': 'hidden', 'padding': '0'});
    
    if ('' != jQuery('#wpv_filter_meta_html_css').val() && 'on' == jQuery('#wpv_filter_meta_html_extra_css_state').val()) {
      wpv_view_filter_meta_html_extra('.wpv_filter_meta_html_extra_css_edit');
    }
    if ('' != jQuery('#wpv_filter_meta_html_js').val() && 'on' == jQuery('#wpv_filter_meta_html_extra_js_state').val()) {
      wpv_view_filter_meta_html_extra('.wpv_filter_meta_html_extra_js_edit');
    }
    if (jQuery('.wpv_table_attachments').length > 0 && 'on' == jQuery('#wpv_filter_meta_html_extra_img_state').val()) {
	    wpv_view_filter_meta_html_extra('.wpv_filter_meta_html_extra_img_edit');
    }
    
    var CSSQueryEditor = CodeMirror.fromTextArea(document.getElementById("wpv_filter_meta_html_css"), {mode: "css", tabMode: "indent", lineWrapping: true, lineNumbers: true});
    var JSQueryEditor = CodeMirror.fromTextArea(document.getElementById("wpv_filter_meta_html_js"), {mode: "javascript", tabMode: "indent", lineWrapping: true, lineNumbers: true});
    
    jQuery('.CodeMirror').css({'background-color': '#fff', 'border': '1px solid #999999'});

    jQuery('#wpv_filter_meta_html_extra_css textarea').keyup(function(event) {
      jQuery('#wpv_filter_meta_html_extra_css_notice').show();
      show_view_changed_message();
    });
    jQuery('#wpv_filter_meta_html_extra_js textarea').keyup(function(event) {
      jQuery('#wpv_filter_meta_html_extra_js_notice').show();
      show_view_changed_message();
    });
});

function wpv_filter_meta_html_generate_new() {
    jQuery('#wpv_filter_meta_html_content_old').val(jQuery('#wpv_filter_meta_html_content').val());
    jQuery('#wpv_filter_meta_html_content_old_div').show();
    jQuery('#wpv_filter_meta_html_content_error').hide();
    jQuery('#wpv_filter_control_meta_html_content_error').hide();
    on_generate_wpv_filter(true);
}

function wpv_filter_meta_html_old_dismiss() {
    jQuery('#wpv_filter_meta_html_content_old_div').hide();
}