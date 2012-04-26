jQuery(document).ready(function() {
	var tokenFocus;

	// Activate tooltips
	jQuery('.ttip').tinyTips('light', 'title');

	// Create 'value tables' - i.e. dropdown comboboxes from multiple select lists with class 'ti-value-table'
	ti_create_vt();

	// Create editable tables
	ti_create_edit_tables();

	// If hidden 'disabled' field is set, disable all input controls, e.g. when displaying import results
	if (typeof(til10n) != 'undefined' && til10n.disable) {
		jQuery('#form_import :input').attr('disabled', true);
	}

	// Accordion class (e.g. for dropdown info about memory usage)
	jQuery('.ti-accordion').click(function() {
	   jQuery(this).next().toggle();
	});

	// When token-enabled field gets focus set tokenFocus, otherwise clear it
	jQuery(':input').focus(function () {
		if (jQuery(this).hasClass('ti-token-field')) {
			tokenFocus = this.id;
		} else {
			tokenFocus = null;
		}
	});

	// The textarea for the post content is output by WP so it doesn't have the 'ti-token-field' class
	// So, trap events by ID instead
	jQuery('#ti_post_content').focus(function() {
	   tokenFocus = 'ti_post_content';
	});

	// When TinyMCE is active, the textarea is converted to an iframe
	// So trap events by the iframe ID instead (live is used because iframe is not always initialized when document is loaded)
	jQuery('#ti_post_content_ifr').live('focus', function() {
		tokenFocus = 'ti_post_content';
	});


	// Insert tokens in last tokenFocus field
	jQuery('#ti_tokens a').click(function() {
		var token = "#" + jQuery(this).text() + "#";
		ti_insert(tokenFocus, token);
		return false;
	});
});

// In IE (document).ready() seems to hang if an alert box is issued
// So any checks requiring alerts need to be here
jQuery(window).load(function() {
	// Delete template button
	jQuery('input[name="button_delete_template"]').click(function() {
		var template_id = jQuery('select[name="template_load_id"]').val();
		if (!template_id) {
			alert (til10n.no_template);
			return false;
		}

		return confirm(til10n.confirm_template_delete.replace('%s', template_id));
	});

	// Save template button
	jQuery('input[name="button_save_template"]').click(function() {
		var id = jQuery('input[name="template_save_id"]');
		if (!id.val()) {
			id.addClass('ti-error');
			id.focus();
			return false;
		}
		return true;
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

	jQuery('input[name="button_process"]').click(function() {

		if (!confirm(til10n.confirm_process)) {
			return false;
		}

		// Show the twizzler and disable the button
		jQuery(this).hide();
		jQuery('#ti_twizzler').show();
	});
});

function ti_insert(id, value) {
	var field, sel;

	//  If id is blank, return
	if (id == null || id == "")
		return;

	// For the post content, use tinyMCE commands for insert
	if (id == 'ti_post_content' && tinyMCE && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
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


/*
	------------------------------------------------------------
	Create dropdown value tables
	------------------------------------------------------------
*/
function ti_create_vt() {
	jQuery('.ti-value-table').each(function() {
		// Build an array of the options in the original multiple select list
		var options = new Array();
		jQuery('option', this).each(function() {
			options.push({
				value : jQuery(this).val(),
				text : jQuery(this).text(),
				checked : (jQuery(this).attr('selected') == 'selected' ? "checked='checked'" : "")
			});
		});

		// Apply wrappers
		jQuery(this).wrap("<div class='ti-vt-wrapper'><div class='ti-vt-scroll'>");

		// Add the trigger <input> element (readonly) - it shows the currently-selected values from the checkboxes
		jQuery(this).parent().before("<input value='' readonly='readonly' class='ti-vt-selected' style=''/>");

		// Get the name of the original dropdown, the same name is used on all of the checkboxes so they'll POST correctly
		var name = jQuery(this).attr('name');

		// Build the list of checkboxes
		var list = "<div class='ti-vt-list'>";
		for (var i=0; i < options.length; i++) {
			var id = name + options[i].value;
			list += "<div>" +
				"<input type='checkbox' name='" + name + "' value='" + options[i].value + "' id='" + id + "' " + options[i].checked + "/>" +
				"<label for='" + id + "'>" + options[i].text + "</label></div>";
		}
		list += "</div>";
		jQuery(this).after(list);

		// Add the 'select all' entry to the top of the dropdown (but above the scrolling 'list' part)
		var id = name + 'selectall';
		jQuery(this).after("<div><input type='checkbox' class='ti-vt-select' id='" + id + "'/><label for='" + id + "'>" + til10n.select_all + "</label><hr/></div>");

		// Remove the original dropdown that we've replaced
		jQuery(this).remove();
	});

	// Open/close the dropdown when the <input> containing the selected values is clicked
	jQuery('.ti-vt-selected').click(function() {
		jQuery(this).parent().find('.ti-vt-scroll').toggle();
	});

	// Close the dropdown when any other part of the screen is clicked
	// Unfortunately this doesn't work cross-browser; only IE sets the relatedTarget and there's no other way to detect if the parent element lost focus
	// For now, users must click the field to open/close instead.  For reference here's the code:
	// jQuery('.ti-vt-wrapper').focusout(function(e) {
	// if (e.relatedTarget && jQuery(e.relatedTarget).parents('.ti-vt-wrapper').length == 0) {
	//	jQuery(this).parent().find('.ti-vt-scroll').hide();
	// }

	// Select all/none
	jQuery('.ti-vt-select').click(function(e) {
		var values = jQuery(this).parent().next('.ti-vt-list');
		jQuery(':checkbox', values).attr('checked', this.checked);

		// Trigger a change to the list element (just setting the checkboxes won't do it)
		jQuery(values).change();
	});

	// On changes to the checkboxes, update the list of values in the trigger element
	jQuery('.ti-vt-list').change(function() {
		var checked = new Array();

		jQuery(':checkbox:checked', this).each(function() {
			// The text for the checkbox is in the next element (assume it's plain text)
			checked.push(jQuery(this).next().text());
		});

		var wrapper = jQuery(this).parents('.ti-vt-wrapper');
		var values = checked.join(", ");
		if (!values || values == '') {
			values = til10n.click_to_select;
		}

		wrapper.children('.ti-vt-selected').val(values);
	});

	// Trigger an initial change event on all the lists to populate the 'selected' element with the checked checkboxes
	jQuery('.ti-vt-list').change();

	// Initially hide all of the dropdowns
	jQuery('.ti-vt-scroll').hide();
}

/*
	------------------------------------------------------------
	Create editable tables with 'add more' and 'delete' links
	------------------------------------------------------------
*/
function ti_create_edit_tables() {
	// Create editable tables with add/delete buttons
	jQuery('.ti-edit-table').each(function() {
		// Create an 'add' button just below the table
		var addButton = jQuery("<a href='#' class='ti-table-add'>+ Add more</a>");
		jQuery(this).parent().append(addButton);

		// Create a delete button at the end of each row
		var delButton = jQuery("<td><a href='#' class='ti-table-delete'>delete</a></td>");
		jQuery('tr', this).append(delButton);

		// If the table has onle 1 row, hide the delete button
		if (jQuery('tr', this).length < 2) {
			jQuery('.ti-table-delete', this).hide();
		}
	});

	// Process 'add' clicks
	jQuery('.ti-table-add').click(function() {
		var t = jQuery(this).prev('table');
		var newRow = jQuery('tr', t).first().clone();

		// It's very difficult to completely clear the row; for this application, just remove selected="selected" from the options
		newRow.find('option').attr('selected', false);

		// Add the new row to the end of the table
		jQuery(t).append(newRow);

		// Should be OK to show delete links, since we have > 1 row now
		jQuery('.ti-table-delete', t).show();
		return false;
	});

	// Process other clicks through the table (through bubbling, we caputre row clicks here)
	jQuery('.ti-edit-table').click(function(e) {
		if (jQuery(e.target).hasClass('ti-table-delete')) {
			jQuery(e.target).closest('tr').remove();

			// A row was deleted, if there's only 1 row left then hide the 'delete' links
			if (jQuery('tr', this).length < 2) {
				jQuery('.ti-table-delete', this).hide();
			}
			return false;
		}
	});
}