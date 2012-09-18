jQuery(document).ready(function() {
	var tokenFocus;

	// Activate tooltips
	jQuery('.ttip').tinyTips('light', 'title');

	// If l10n 'disabled' field is set, disable all input controls, e.g. when displaying import results
	if (typeof(til10n) != 'undefined' && til10n.disable) {
		jQuery('#form_import :input').attr('disabled', true);
	} else {
		// Create editable tables only if not disabled
		jQuery('.edt').edt();
	}

	// Create dropdown multiselects
	jQuery('.ddms').ddms();

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


/**
* Plugin for editable tables (with add/delete links)
*
* Usage: jQuery('element').edt( options )
*
* The element should be an html table
*
* Options:
*   labels : l10n labels, specify as { add : '', del : ''}
*
*/
jQuery.fn.edt = function( options ) {

	// Create some defaults, extending them with any options that were provided
	var settings = jQuery.extend( {
		l10n    : {add : '+ Add more', del : 'delete'}
	}, options);

	return this.each(function() {

		var element = this;

		// Set up the add/delete buttons
		// Create an 'add' button just below the table
		jQuery(this).parent().append("<a href='#' class='ti-table-add'>" + settings.l10n.add + "</a>");

		// Create a delete button at the end of each row
		var delButton = jQuery("<td><a href='#' class='ti-table-delete'>" + settings.l10n.del + "</a></td>");
		jQuery('tr', element).append(delButton);

		// If the table has only 1 row, hide the delete button
		if (jQuery('tr', element).length < 2) {
			jQuery('.ti-table-delete', element).hide();
		}

		// Process 'add' clicks
		jQuery(this).next('.ti-table-add').click(function() {
			var newRow = jQuery('tr', element).first().clone();

			// Add the new row to the end of the table
			jQuery(element).append(newRow);

			// Should be OK to show delete links, since we have > 1 row now
			jQuery('.ti-table-delete', element).show();
			return false;
		});

		// Process 'delete' clicks within the table rows
		jQuery(this).click(function(e) {
			if (jQuery(e.target).hasClass('ti-table-delete')) {
				jQuery(e.target).closest('tr').remove();

				// if there's only 1 row left then hide the 'delete' links
				if (jQuery('tr', this).length < 2) {
					jQuery('.ti-table-delete', this).hide();
				}
				return false;
			}
		});
	});
}


/**
* Plugin for drop-down multi-select
*
* Usage: jQuery('element').ddms( options )
*
* The element should be a standard select with the 'multiple' attribute:
* <select multiple="multiple">
*
* Options:
*   labels : l10n labels, specify as { selectAll : '', select : ''}
*
*/
jQuery.fn.ddms = function( options ) {

	// Create some defaults, extending them with any options that were provided
	var settings = jQuery.extend( {
		l10n    : {selectAll : 'Select All', select : 'Click to select'}
	}, options);

	return this.each(function() {

		// Get the name of the original dropdown, we'll use the same name after it's replaced
		var name = jQuery(this).attr('name');
		var width = jQuery(this).width();

		// Generate the html.  The input is always readonly, but it's also disabled if the original element was disabled
		var disabled = (jQuery(this).attr('disabled') == 'disabled') ? "disabled='disabled'" : "";
		var html = "<div class='ddms-container'>" +
			"<input value='' readonly='readonly' " + disabled + " class='ddms-selected' />";

		// If the list is empty, there's nothing else to do - just display an empty field
		if (jQuery('option', this).length == 0) {
			html += "</div>";
			jQuery(this).replaceWith(html);
			return;
		}

		// Add the 'select all' entry to the top of the dropdown
		html += "<div class='ddms-dropdown'>";
		html += "<div><label><input type='checkbox' class='ddms-selectall' />" + settings.l10n.selectAll + "</label><hr/></div>";

		// Build the list of checkboxes as <divs>
		html += "<div class='ddms-options'>";
		jQuery('option', this).each(function() {
			var checked = (jQuery(this).attr('selected') == "selected") ? "checked='checked'" : "";

			// Note that the label for each element is also stored in the title attribute of the <input>, to make it easier to retrieve later
			var label = jQuery(this).text();
			html += "<div><label>" +
				"<input type='checkbox' name='" + name + "' value='" + jQuery(this).val() + "' " + checked + " title='" + label + "' />" +
				label + "</label></div>";
		});
		html += "</div>";

		// Create a container element and replace the original dropdown with it
		var container = jQuery(html);
		jQuery(this).replaceWith(container);

		// Select all/none
		jQuery('.ddms-selectall', container).click(function(e) {
			jQuery('.ddms-options :checkbox', container).attr('checked', this.checked);

			// Trigger the change event to update the list of values
			jQuery('.ddms-options :checkbox', container).change();
		});

		// On changes to the checkboxes, update the list of values
		jQuery('.ddms-options', container).change(function(event) {
			var checked = new Array();

			jQuery(':checkbox:checked', this).each(function() {
				// Assume the text for the checkbox is in the preceding label
				checked.push(jQuery(this).attr('title'));
			});

			var values = checked.join(", ");
			if (!values || values == '') {
				values = settings.l10n.select;
			}

			jQuery('.ddms-selected', container).val(values);
		});

		// Trigger an initial change event on all the list to populate the 'selected' element with the checked checkboxes
		jQuery('.ddms-options', container).change();

		//
		// To make the dropdowns open & close properly...
		// ----------------------------------------------------------
		//

		// When the document body is clicked, hide the dropdown.
		jQuery('body').click(function() {
			jQuery('.ddms-dropdown', container).hide();
		});

		// Prevent click events on the container (like checkboxes) from propagating up to the body
		jQuery('.ddms-dropdown', container).click(function(event) {
			event.stopPropagation();
		});

		// When the selector is clicked, toggle the dropdown
		jQuery('.ddms-selected', container).click(function(event) {
			// Get the current dropdown visibility
			var display = jQuery('.ddms-dropdown', container).css('display');

			// We want all the OTHER dropdowns on the screen to close, so close all dropdowns here
			jQuery('.ddms-dropdown').hide();

			// Toggle the dropdown for this ddms
			if (display == 'none')
				jQuery('.ddms-dropdown', container).show();

			// The event has to be stopped with return false, otherwise the body will close it again
			return false;
		});
	});
};
