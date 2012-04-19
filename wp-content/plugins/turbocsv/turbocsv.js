jQuery(document).ready(function() {
	var tokenFocus;

	// Tooltips - anything with <sup> tags, print title when clicked
	jQuery('a.ti-tooltip').cluetip({activation: 'click', sticky: true, closePosition: 'title', arrows: true, splitTitle: '|'});

	// Even-odd formatting for tables
//	jQuery('table.ti-stripe tr:odd').each(function() {
//		if ( !(jQuery(this).hasClass('error') || jQuery(this).hasClass('updated')) ) {
//			jQuery(this).addClass('ti-odd');
//		}
//	});

//	jQuery('table.ti-stripe tr:even').each(function() {
//		if ( !(jQuery(this).hasClass('error') || jQuery(this).hasClass('updated')) ) {
//			jQuery(this).addClass('ti-even');
//		}
//	});

	// Checkboxes that set all checks in a table (unable to find reliable JQ to do this generically)
	jQuery("#ti_categories_table thead tr input:checkbox").click(function() {
		jQuery("#ti_categories_table input:checkbox").attr('checked', this.checked);
	})

	jQuery("#ti_tags_table thead tr input:checkbox").click(function() {
		jQuery("#ti_tags_table input:checkbox").attr('checked', this.checked);
	})

	jQuery("#ti_custom_new_table thead tr input:checkbox").click(function() {
		jQuery("#ti_custom_new_table input:checkbox").attr('checked', this.checked);
	})


	// When token-enabled field gets focus set tokenFocus, otherwise clear it
	jQuery(':input').focus(function () {
		if (jQuery(this).hasClass('ti-token-field')) {
			tokenFocus = this.id;
		} else {
			tokenFocus = null;
		}
	});

	// Insert tokens in last tokenFocus field
	jQuery('#ti_tokens a').click(function() {
		var token = "#" + jQuery(this).text() + "#";

		ti_insert(tokenFocus, token);
		return false;
	});

	// Toggle off MCE
	jQuery('input[name=button_editor_html]').click(function() {
		ti_clear_error();
		tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
		return false;
	});

	// Toggle on MCE
	jQuery('input[name=button_editor_visual]').click(function() {
		ti_clear_error();
		 tinyMCE.execCommand('mceAddControl', false, 'post_content');

		 // Add an onclicked event so we know the tinyMCE had the focus
		 tinyMCE.activeEditor.onClick.add(function(ed) {
			 tokenFocus = ed.id;
		 });

		 return false;
	});
}) ;

// In IE (document).ready() seems to hang if an alert box is issued
// So any checks requiring alerts need to be here
jQuery(window).load(function() {
	// Delete template button
	jQuery('input[name=button_delete_template]').click(function() {
		var template_id = jQuery('select[name=template_load_id]').val();
		if (!template_id) {
			alert (til10n.no_template);
			return false;
		}

		return confirm(til10n.confirm_template_delete.replace('%s', template_id));
	});

	// Save template button
	jQuery('input[name=button_save_template]').click(function() {
		if (ti_empty('input[name=template_save_id]')) {
			alert(til10n.no_template_name);
			return false;
		} else {
			return true;
		}
	});

	// Undo link warning
	jQuery('.ti_undo_link').click(function() {
		if (confirm(til10n.confirm_undo)) {
			jQuery('#twizzler').show();

			// Allow normal processing of the button
			return true;
		} else {
			return false;
		}
	});

	// Delete link warning
	jQuery('.ti_delete_link').click(function() {
		if (confirm(til10n.confirm_import_delete)) {
			return true;
		} else {
			return false;
		}
	});

	jQuery('input[name=button_process]').click(function() {

		if (!ti_validate_import(this.name)) {
			return false;
		}

		if (!confirm(til10n.confirm_process)) {
			return false;
		}

		// Show the twizzler and disable the button
		jQuery(this).hide();
		jQuery('#ti_twizzler').show();

		var options = {
			url : ajaxurl,
			timeout : 2 * 60 * 60 * 1000,       // Timeout is 2 hours!
			data: {action : 'ti_process'},
			success : function(data) {

				// Print any error/success message
				alert(data);

				// Reload the page when complete
				window.location.reload(true);
					return;
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				alert('Internal ERROR in import AJAX call.  Response=' + XMLHttpRequest.responseText + ', Status=' + textStatus + ', error=' + errorThrown);
				window.location.reload(true);
			}
		};

		jQuery('#form_import').ajaxSubmit(options);
		return false;
	});
});


// Validate import screen
function ti_validate_import(buttonName) {
	ti_clear_error();

	// Check title not empty
	if (jQuery('input[name=template[post_title]]').length) {
		if (ti_empty('input[name=template[post_title]]')) {
			alert(til10n.no_post_title)
			return false;
		}
	}

	// Check body not empty
	if (jQuery('input[name=template[post_content]]').length) {
		// Save tinyMCE editor contents to textarea
		tinyMCE.triggerSave(false,true);

		if (ti_empty('textarea[name=template[post_content]]')) {
			// If tinyMCE editor is active we can't highlight the textarea, focus the tinyMCE box instead
			if (tinyMCE.activeEditor) {
				var ed = tinyMCE.getInstanceById('post_content');
				ed.getBody().style.backgroundColor = "pink";
				tinyMCE.execCommand('mceFocus', false, 'post_content');
			}
			alert('Enter the post body');
			return false;
		}
	}

	// Checks passed
	return true;
}


function ti_empty(field) {
	if (!jQuery(field).val()){
		jQuery(field).addClass('ti-error');
		jQuery(field).focus();
		return true;
	}
	return false;
}


function ti_clear_error() {
	var ed;

	// Remove any existing error highlights from fields and tinyMCE
	jQuery('input').removeClass('ti-error');

	if (jQuery('input[name=template[post_content]]').length) {
		ed = tinyMCE.getInstanceById('post_content');
		if (ed) {
			ed.getBody().style.backgroundColor = "";
		}
	}
}

function ti_insert(id, value) {
	var field, sel;

	//  If id is blank, return
	if (id == null || id == "")
		return;

	// If tinyMCE is active use its commands for insert
	if (id == 'post_content' && tinyMCE.activeEditor) {
		tinyMCE.execCommand('mceInsertContent', false, value);
		return;
	}

	field = document.getElementById(id);

	// Return if field could not be found
	if (field == null || field == "")
		return;

	//IE support
	if (document.selection) {
		field.focus();
		sel = document.selection.createRange();
		sel.text = value;
		field.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (field.selectionStart || field.selectionStart == '0') {
		var startPos = field.selectionStart;
		var endPos = field.selectionEnd;
		field.value = field.value.substring(0, startPos)
					  + value
					  + field.value.substring(endPos, field.value.length);
	} else {
		field.value += value;
	}
}
