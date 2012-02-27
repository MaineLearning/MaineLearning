/**
 * This file controls the behaviours within the Genesis Framework.
 *
 * Note that while this version of the file include 'use strict'; at the function level,
 * the Closure Compiler version strips that away. This is fine, as the compiler may
 * well be doing things that are not use strict compatible.
 *
 * @author   StudioPress
 */

// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @output_file_name admin.js
// @externs_url http://closure-compiler.googlecode.com/svn/trunk/contrib/externs/jquery-1.7.js
// ==/ClosureCompiler==
// http://closure-compiler.appspot.com/home

/*jslint browser: true, devel: true, indent: 4, maxerr: 50, sub: true */
/*global genesis_confirm, confirm, jQuery, genesis, genesis_toggles, genesisL10n */

/**
 * Holds Genesis values in an object to avoid polluting global namespace.
 *
 * @since 1.8.0
 *
 * @constructor
 */
window['genesis'] = {

	/**
	 * Inserts a category checklist toggle button and binds the behaviour.
	 *
	 * @since 1.8.0
	 *
	 * @function
	 */
	category_checklist_toggle_init: function () {
		'use strict';

		// Insert toggle button into DOM wherever there is a category checklist
		jQuery('<p><span id="genesis-category-checklist-toggle" class="button">' + genesisL10n.category_checklist_toggle + '</span></p>').insertBefore('ul.categorychecklist');

		// Bind the behaviour to click
		jQuery(document).on('click.genesis.genesis_category_checklist_toggle', '#genesis-category-checklist-toggle', genesis.category_checklist_toggle);
	},

	/**
	 * Provides the behaviour for the category checklist toggle button.
	 *
	 * On the first click, it checks all checkboxes, and on subsequent clicks it
	 * toggles the checked status of the checkboxes.
	 *
	 * @since 1.8.0
	 *
	 * @function
	 *
	 * @param {jQuery.event} event
	 */
	category_checklist_toggle: function (event) {
		'use strict';

		// Cache the selectors
		var $this = jQuery(event.target),
			checkboxes = $this.parent().next().find(':checkbox');

		// If the button has already been clicked once, clear the checkboxes and remove the flag
		if ($this.data('clicked')) {
			checkboxes.removeAttr('checked');
			$this.data('clicked', false);
		} else { // Mark the checkboxes and add a flag
			checkboxes.attr('checked', 'checked');
			$this.data('clicked', true);
		}
	},

	/**
	 * Grabs the array of toggle settings and loops through them to hook in
	 * the behaviour.
	 *
	 * The genesis_toggles array is filterable in load-scripts.php before being
	 * passed over to JS via wp_localize_script().
	 *
	 * @since 1.8.0
	 *
	 * @function
	 */
	toggle_settings_init: function () {
		'use strict';

		jQuery.each(genesis_toggles, function (k, v) {

			// Prepare data
			var data = {selector: v[0], show_selector: v[1], check_value: v[2]};

			// Setup toggle binding
			jQuery('div.genesis-metaboxes').on('change.genesis.genesis_toggle', v[0], data, genesis.toggle_settings);

			// Trigger the check when page loads too.
			// Can't use triggerHandler here, as that doesn't bubble the event up to div.genesis-metaboxes.
			// We namespace it, so that it doesn't conflict with any other change event attached that
			// we don't want triggered on document ready.
			jQuery(v[0]).trigger('change.genesis_toggle', data);
		});

	},

	/**
	 * Provides the behaviour for the change event for certain settings.
	 *
	 * Three bits of event data is passed - the jQuery selector which has the
	 * behaviour attached, the jQuery selector which to toggle, and the value to
	 * check against.
	 *
	 * The check_value can be a single string or an array (for checking against
	 * multiple values in a dropdown) or a null value (when checking if a checkbox
	 * has been marked).
	 *
	 * @since 1.8.0
	 *
	 * @function
	 *
	 * @param {jQuery.event} event
	 */
	toggle_settings: function (event) {
		'use strict';

		// Cache selectors
		var $selector = jQuery(event.data.selector),
		    $show_selector = jQuery(event.data.show_selector),
		    check_value = event.data.check_value;

		// Compare if a check_value is an  array, and one of them matches the value of the selected option
		// OR the check_value is null, but the checkbox is marked
		// OR it's a string, and that matches the value of the selected option.
		if (
			(jQuery.isArray(check_value) && jQuery.inArray($selector.val(), check_value) > -1) ||
				(check_value === null && $selector.is(':checked')) ||
				(check_value !== null && $selector.val() === check_value)
		) {
			jQuery($show_selector).slideDown('fast');
		} else {
			jQuery($show_selector).slideUp('fast');
		}

	},

	/**
	 * When a input or textarea field field is updated, update the character counter.
	 *
	 * For now, we can assume that the counter has the same ID as the field, with a _chars
	 * suffix. In the future, when the counter is added to the DOM with JS, we can add
	 * a data('counter', 'counter_id_here' ) property to the field element at the same time.
	 *
	 * @since 1.8.0
	 *
	 * @function
	 *
	 * @param {jQuery.event} event
	 */
	update_character_count: function (event) {
		'use strict';
		//
		jQuery('#' + event.target.id + '_chars').html(jQuery(event.target).val().length.toString());
	},

	/**
	 * Provides the behaviour for the layout selector.
	 *
	 * When a layout is selected, the all layout labels get the selected class
	 * removed, and then it is added to the label that was selected.
	 *
	 * @since 1.8.0
	 *
	 * @function
	 *
	 * @param {jQuery.event} event
	 */
	layout_highlighter: function (event) {
		'use strict';

		// Cache class name
		var selected_class = 'selected';

	    // Remove class from all labels
	    jQuery('input[name="' + jQuery(event.target).attr('name') + '"]').parent('label').removeClass(selected_class);

	    // Add class to selected layout
	    jQuery(event.target).parent('label').addClass(selected_class);

	},

	/**
	 * Helper function for confirming a user action.
	 *
	 * @since 1.8.0
	 *
	 * @function
	 *
	 * @param {String} text The text to display.
	 * @returns {Boolean}
	 */
	confirm: function (text) {
		'use strict';

		return confirm(text) ? true : false;

	},

	/**
	 * Initialises all aspects of the scripts.
	 *
	 * Generally ordered with stuff that inserts new elements into the DOM first,
	 * then stuff that triggers an event on existing DOM elements when ready,
	 * followed by stuff that triggers an event only on user interaction. This
	 * keeps any screen jumping from occuring later on.
	 *
	 * @since 1.8.0
	 *
	 * @function
	 */
	ready: function () {
		'use strict';

		// Move all messages below our floated buttons
		jQuery('h2').nextAll('div.updated, div.error').insertAfter('p.top-buttons');

		// Initialise category checklist toggle button
		genesis.category_checklist_toggle_init();

		// Initialise settings that can toggle the display of other settings
		genesis.toggle_settings_init();

		// Bind character counters
		jQuery('#genesis_title, #genesis_description').on('keyup.genesis.genesis_character_count', genesis.update_character_count);

		// Bind layout highlighter behaviour
		jQuery('.genesis-layout-selector').on('change.genesis.genesis_layout_selector', 'input[type="radio"]', genesis.layout_highlighter);

	}

};

jQuery(genesis.ready);

/**
 * Helper function for confirming a user action.
 *
 * This function is deprecated in favour of genesis.confirm(text) which provides
 * the same functionality.
 *
 * @since 1.0.0
 * @deprecated 1.8.0
 */
function genesis_confirm(text) {
	'use strict';
	return genesis.confirm(text);
}