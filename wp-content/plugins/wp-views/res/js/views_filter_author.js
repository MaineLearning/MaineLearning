jQuery(document).ready(function($){
    wpv_register_add_filter_callback('wpv_post_author_add_filter');
    jQuery('.wpv_author_url_param_missing').hide();
    jQuery('.wpv_author_url_param_ilegal').hide();
    jQuery('.wpv_author_shortcode_param_missing').hide();
    jQuery('.wpv_author_shortcode_param_ilegal').hide();
    jQuery('.wpv_author_helper').hide();
    jQuery('input[name=author_mode\\[\\]]').change(function() {
      jQuery('.wpv_author_url_param_missing').hide();
      jQuery('.wpv_author_url_param_ilegal').hide();
      jQuery('.wpv_author_shortcode_param_missing').hide();
      jQuery('.wpv_author_shortcode_param_ilegal').hide();
      if (jQuery('input[name=author_mode\\[\\]]:checked').val() == 'by_url') {
	wpv_add_author_help();
      } else if (jQuery('input[name=author_mode\\[\\]]:checked').val() == 'shortcode') {
	wpv_add_author_help();
      } else {
	jQuery('.wpv_author_helper').hide();
      }
    });
    jQuery('select[name=wpv_author_url_type_add]').change(wpv_add_author_help);
    jQuery('input[name=author_url]').change(wpv_add_author_help);
    jQuery('select[name=wpv_author_shortcode_type_add]').change(wpv_add_author_help);
    jQuery('input[name=author_shortcode]').change(wpv_add_author_help);
});

function wpv_add_author_help() {
  jQuery('.wpv_author_url_param_missing').hide();
  jQuery('.wpv_author_url_param_ilegal').hide();
  jQuery('.wpv_author_shortcode_param_missing').hide();
  jQuery('.wpv_author_shortcode_param_ilegal').hide();
  if (jQuery('input[name=author_mode\\[\\]]:checked').val() == 'by_url') {
    var url_mode = jQuery('select[name=wpv_author_url_type_add]').val();
    var url_value = jQuery('input[name=author_url]').val();
    if (url_value == '') {
      jQuery('.wpv_author_url_param_missing').show();
      jQuery('.wpv_author_helper').html('');
    } else {
      var pat = /^[a-z0-9\-\_]+$/;
      if (pat.test(url_value) == false) {
	jQuery('.wpv_author_helper').html('');
	jQuery('.wpv_author_url_param_ilegal').show();
      } else {
	var url_example = [];
	url_example['id'] = '1';
	url_example['username'] = 'admin';
	var author_help = '<small>To control the author, link to the page that includes this View with the argument set as <strong class="author_url_param">\''+ url_value + '\'</strong>. For example:';
	author_help += '<br />yoursite/page-with-this-view/?<strong class="author_url_param">'+ url_value + '</strong>=' + url_example[url_mode];
	author_help += '<br />This will filter by author with ' + url_mode + '=' + url_example[url_mode] + '</small>';
	jQuery('.wpv_author_helper').show();
	jQuery('.wpv_author_helper').html(author_help);
	jQuery('.wpv_author_url_param_ilegal').hide();
      }
    }
  }
  if (jQuery('input[name=author_mode\\[\\]]:checked').val() == 'shortcode') {
    var view_name = jQuery('input[name=post_title]').val();
    var short_mode = jQuery('select[name=wpv_author_shortcode_type_add]').val();
    var short_value = jQuery('input[name=author_shortcode]').val();
    if (short_value == '') {
      jQuery('.wpv_author_shortcode_param_missing').show();
      jQuery('.wpv_author_helper').html('');
    } else {
      var pat = /^[a-z0-9]+$/;
      if (pat.test(short_value) == false) {
	jQuery('.wpv_author_helper').html('');
	jQuery('.wpv_author_shortcode_param_ilegal').show();
      } else {
	var short_example = [];
	short_example['id'] = '1';
	short_example['username'] = 'admin';
	var short_help = '<small>To control the author, edit the shortcode to this View and add the <strong class="author_url_param">\''+ short_value + '\'</strong> attribute to it. For example:';
	short_help += '<br />[wpv-view name="' + view_name + '" <strong class="author_url_param">'+ short_value + '</strong>="' + short_example[short_mode] + '"]';
	short_help += '<br />This will filter by author with ' + short_mode + '=' + short_example[short_mode] + '</small>';
	jQuery('.wpv_author_helper').show();
	jQuery('.wpv_author_helper').html(short_help);
	jQuery('.wpv_author_shortcode_param_ilegal').hide();
      }
    }
  }
}

/*
 * function: wpv_author_add_filter
 *
 * Add the author settings to the ajax data when we add a filter
 * Six variables:
 * - mode: for current user,selected user or based on URL parameter
 * - id: for the second mode, the selected user ID
 * - url: for the third mode, the URL parameter to be used
 * - url_type: for the third mode, what kind of user data are we expecting
 * - shortcode: for the fourth mode, the shortcode parameter to be use
 * - shortcode_type: for the fourth mode, what kind of user data are we expecting
 */

function wpv_post_author_add_filter(data) {
    
    // get author if set
    var author_mode = '';
    var author_id = '';
    var author_url = '';
    var author_url_type = '';
    var author_shortcode = '';
    var author_shortcode_type = '';
    if (jQuery('input[name=author_mode\\[\\]]').length) {
        author_mode = jQuery('input[name=author_mode\\[\\]]:checked').val();
        author_id = jQuery('select[name=wpv_author_id_add]').val();
	author_url = jQuery('input[name=author_url]').val();
	author_url_type = jQuery('select[name=wpv_author_url_type_add]').val();
	author_shortcode = jQuery('input[name=author_shortcode]').val();
	author_shortcode_type = jQuery('select[name=wpv_author_shortcode_type_add]').val();
    }
    
    if (author_mode != '') {
        data['author_mode'] = author_mode;
        data['author_id'] = author_id;
	data['author_url'] = author_url;
	data['author_url_type'] = author_url_type;
	data['author_shortcode'] = author_shortcode;
	data['author_shortcode_type'] = author_shortcode_type;
    }
    
    return data;
}

var previous_author_mode;
var previous_author_id;
var previous_author_url;
var previous_author_url_type;
var previous_author_shortcode;
var previous_author_shortcode_type;

/* Show the edit screen */

function wpv_show_filter_author_edit() {

    previous_author_mode = jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked');
    previous_author_id = jQuery('select[name=_wpv_settings\\[author_id\\]]').val();
    previous_author_url = jQuery('input[name=_wpv_settings\\[author_url\\]]').val();
    previous_author_url_type = jQuery('select[name=_wpv_settings\\[author_url_type\\]]').val();
    previous_author_shortcode = jQuery('input[name=_wpv_settings\\[author_shortcode\\]]').val();
    previous_author_shortcode_type = jQuery('select[name=_wpv_settings\\[author_shortcode_type\\]]').val();
    jQuery('.wpv_author_url_param_missing').hide();
    jQuery('.wpv_author_shortcode_param_missing').hide();
    
    jQuery('#wpv-filter-author-edit').parent().parent().css('background-color', jQuery('#wpv-filter-author-edit').css('background-color'));

    jQuery('#wpv-filter-author-edit').show();
    jQuery('#wpv-filter-author-show').hide();
    
    if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'by_url') {
      wpv_edit_author_help();
    } else if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'shortcode') {
      wpv_edit_author_help();
    }
    
    jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]').change(function() {
      jQuery('.wpv_author_url_param_missing').hide();
      jQuery('.wpv_author_url_param_ilegal').hide();
      jQuery('.wpv_author_shortcode_param_missing').hide();
      jQuery('.wpv_author_shortcode_param_ilegal').hide();
      if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'by_url') {
	wpv_edit_author_help();
      } else if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'shortcode') {
	wpv_edit_author_help();
      } else {
	jQuery('.wpv_author_helper').hide();
      }
    });
    jQuery('select[name=_wpv_settings\\[author_url_type\\]]').change(wpv_edit_author_help);
    jQuery('input[name=_wpv_settings\\[author_url\\]]').change(wpv_edit_author_help);
    jQuery('select[name=_wpv_settings\\[author_shortcode_type\\]]').change(wpv_edit_author_help);
    jQuery('input[name=_wpv_settings\\[author_shortcode\\]]').change(wpv_edit_author_help);
    
}

/* Save the edit results and get the summary */
                                               
function wpv_show_filter_author_edit_ok() {

    // find the filter row in the table.
    var tr = jQuery('#wpv-filter-author-show').parent().parent();
    var row = tr.attr('id').substr(15);
    
    var data = {
        action : 'wpv_get_table_row_ui',
        type_data : 'post_author',
        row : row,
        author_mode : jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val(),
        author_id : jQuery('select[name=_wpv_settings\\[author_id\\]]').val(),
        author_url : jQuery('input[name=_wpv_settings\\[author_url\\]]').val(),
        author_url_type : jQuery('select[name=_wpv_settings\\[author_url_type\\]]').val(),
        author_shortcode : jQuery('input[name=_wpv_settings\\[author_shortcode\\]]').val(),
        author_shortcode_type : jQuery('select[name=_wpv_settings\\[author_shortcode_type\\]]').val(),
        wpv_nonce : jQuery('#wpv_get_table_row_ui_nonce').attr('value')
    };
    
    var pat = /^[a-z0-9]+$/;
    var t = jQuery('input[name=_wpv_settings\\[author_shortcode\\]]').val();
    var paturl = /^[a-z0-9\-\_]+$/;
    var turl = jQuery('input[name=_wpv_settings\\[author_url\\]]').val();
    
    if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'by_url' && jQuery('input[name=_wpv_settings\\[author_url\\]]').val() == '') {
      jQuery('.wpv_author_url_param_missing').show();
      jQuery('.wpv_author_shortcode_param_missing').hide();
      jQuery('.wpv_author_shortcode_param_ilegal').hide();
    } else if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'by_url' && paturl.test(turl) == false) {
      jQuery('.wpv_author_shortcode_param_missing').hide();
      jQuery('.wpv_author_url_param_missing').hide();
      jQuery('.wpv_author_shortcode_param_ilegal').hide();
      jQuery('.wpv_author_url_param_ilegal').show();
    } else if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'shortcode' && jQuery('input[name=_wpv_settings\\[author_shortcode\\]]').val() == '') {
      jQuery('.wpv_author_shortcode_param_missing').show();
      jQuery('.wpv_author_url_param_missing').hide();
      jQuery('.wpv_author_shortcode_param_ilegal').hide();
    } else if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'shortcode' && pat.test(t) == false) {
      jQuery('.wpv_author_shortcode_param_missing').hide();
      jQuery('.wpv_author_url_param_missing').hide();
      jQuery('.wpv_author_shortcode_param_ilegal').show();
      jQuery('.wpv_author_url_param_ilegal').hide();
    } else {    
    var td = '';
    jQuery.post(ajaxurl, data, function(response) {
        td = response;
        jQuery('#wpv_filter_row_' + row).html(td);
        jQuery('#wpv-filter-author-edit').parent().parent().css('background-color', '');
        jQuery('#wpv-filter-author-edit').hide();
        jQuery('#wpv-filter-author-show').show();
        on_generate_wpv_filter();
    });

   show_view_changed_message();
   
    }
}

function wpv_edit_author_help() {
  jQuery('.wpv_author_url_param_missing').hide();
  jQuery('.wpv_author_url_param_ilegal').hide();
  jQuery('.wpv_author_shortcode_param_missing').hide();
  jQuery('.wpv_author_shortcode_param_ilegal').hide();
  if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'by_url') {
    var url_mode = jQuery('select[name=_wpv_settings\\[author_url_type\\]]').val();
    var url_value = jQuery('input[name=_wpv_settings\\[author_url\\]]').val();
    if (url_value == '') {
      jQuery('.wpv_author_url_param_missing').show();
      jQuery('.wpv_author_helper').html('');
    } else {
      var pat = /^[a-z0-9\-\_]+$/;
      if (pat.test(url_value) == false) {
	jQuery('.wpv_author_helper').html('');
	jQuery('.wpv_author_url_param_ilegal').show();
      } else {
	var url_example = [];
	url_example['id'] = '1';
	url_example['username'] = 'admin';
	var author_help = '<small>To control the author, link to the page that includes this View with the argument set as <strong class="author_url_param">\''+ url_value + '\'</strong>. For example:';
	author_help += '<br />yoursite/page-with-this-view/?<strong class="author_url_param">'+ url_value + '</strong>=' + url_example[url_mode];
	author_help += '<br />This will filter by author with ' + url_mode + '=' + url_example[url_mode] + '<br /><br /></small>';
	jQuery('.wpv_author_helper').show();
	jQuery('.wpv_author_helper').html(author_help);
      }
    }
  }
  if (jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]:checked').val() == 'shortcode') {
    var view_name = jQuery('input[name=post_title]').val();
    var short_mode = jQuery('select[name=_wpv_settings\\[author_shortcode_type\\]]').val();
    var short_value = jQuery('input[name=_wpv_settings\\[author_shortcode\\]]').val();
    if (short_value == '') {
      jQuery('.wpv_author_shortcode_param_missing').show();
      jQuery('.wpv_author_helper').html('');
    } else {
      var pat = /^[a-z0-9]+$/;
      if (pat.test(short_value) == false) {
	jQuery('.wpv_author_helper').html('');
	jQuery('.wpv_author_shortcode_param_ilegal').show();
      } else {
	var short_example = [];
	short_example['id'] = '1';
	short_example['username'] = 'admin';
	var short_help = '<small>To control the author, edit the shortcode to this View and add the <strong class="author_url_param">\''+ short_value + '\'</strong> attribute to it. For example:';
	short_help += '<br />[wpv-view name="' + view_name + '" <strong class="author_url_param">'+ short_value + '</strong>="' + short_example[short_mode] + '"]';
	short_help += '<br />This will filter by author with ' + short_mode + '=' + short_example[short_mode] + '<br /><br /></small>';
	jQuery('.wpv_author_helper').show();
	jQuery('.wpv_author_helper').html(short_help);
      }
    }
  }
}

/* Cancel the edit operation and set the values back to the way they were
*/

function wpv_show_filter_author_edit_cancel() {

    jQuery('input[name=_wpv_settings\\[author_mode\\]\\[\\]]').each( function(index) {
        jQuery(this).attr('checked', false); 
    });
    previous_author_mode.attr('checked', true);
    jQuery('select[name=_wpv_settings\\[author_id\\]]').val(previous_author_id);
    jQuery('input[name=_wpv_settings\\[author_url\\]]').val(previous_author_url);
    jQuery('select[name=_wpv_settings\\[author_url_type\\]]').val(previous_author_url_type);
    jQuery('input[name=_wpv_settings\\[author_shortcode\\]]').val(previous_author_shortcode);
    jQuery('select[name=_wpv_settings\\[author_shortcode_type\\]]').val(previous_author_shortcode_type);

    jQuery('#wpv-filter-author-edit').parent().parent().css('background-color', '');
    jQuery('#wpv-filter-author-edit').hide();
    jQuery('#wpv-filter-author-show').show();
}