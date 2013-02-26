/**
 *  MyZebra_Form (original is Zebra_Form)
 *
 *  Client-side validation for MyZebra_Form
 *
 *  Visit {@link http://stefangabos.ro/php-libraries/zebra-form/} for more information.
 *
 *  For more resources visit {@link http://stefangabos.ro/}
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @version    2.8.5 (last revision: July 23, 2012)
 *  @copyright  (c) 2011 - 2012 Stefan Gabos
 *  @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package    MyZebra_Form
 */
/**
*
*   Modified and Extended by Nikos M. (nikos.m@icanlocalize.com)
*
**/
 
/**
 * Zebra_DatePicker plugin
 * 
 */
/**
 *  MyZebra_DatePicker
 *
 *  MyZebra_DatePicker is a small, compact and highly configurable date picker plugin for jQuery
 *
 *  Visit {@link http://stefangabos.ro/jquery/MyZebra-datepicker/} for more information.
 *
 *  For more resources visit {@link http://stefangabos.ro/}
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @version    1.4.1 (last revision: July 29, 2012)
 *  @copyright  (c) 2011 - 2012 Stefan Gabos
 *  @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package    MyZebra_DatePicker
 */
;(function($) {

    $.MyZebra_DatePicker = function(element, options) {

        var defaults = {

            //  by default, the button for clearing a previously selected date is shown only if a previously selected date
            //  already exists; this means that if the input the date picker is attached to is empty, and the user selects
            //  a date for the first time, this button will not be visible; once the user picked a date and opens the date
            //  picker again, this time the button will be visible.
            //
            //  setting this property to TRUE will make this button visible all the time
            always_show_clear:  false,

            //  setting this property to a jQuery element, will result in the date picker being always visible, the indicated
            //  element being the date picker's container;
            //  note that when this property is set to TRUE, the "always_show_clear" property will automatically be set to TRUE
            always_visible:     false,

            //  days of the week; Sunday to Saturday
            days:               ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],

            //  direction of the calendar
            //
            //  a positive or negative integer: n (a positive integer) creates a future-only calendar beginning at n days
            //  after today; -n (a negative integer); if n is 0, the calendar has no restrictions. use boolean true for
            //  a future-only calendar starting with today and use boolean false for a past-only calendar ending today.
            //
            //  you may also set this property to an array with two elements in the following combinations:
            //
            //  -   first item is boolean TRUE (calendar starts today), an integer > 0 (calendar starts n days after
            //      today), or a valid date given in the format defined by the "format" attribute (calendar starts at the
            //      specified date), and the second item is boolean FALSE (the calendar has no ending date), an integer
            //      > 0 (calendar ends n days after the starting date), or a valid date given in the format defined by
            //      the "format" attribute and which occurs after the starting date (calendar ends at the specified date)
            //
            //  -   first item is boolean FALSE (calendar ends today), an integer < 0 (calendar ends n days before today),
            //      or a valid date given in the format defined by the "format" attribute (calendar ends at the specified
            //      date), and the second item is an integer > 0 (calendar ends n days before the ending date), or a valid
            //      date given in the format defined by the "format" attribute and which occurs before the starting date
            //      (calendar starts at the specified date)
            //
            //  [1, 7] - calendar starts tomorrow and ends seven days after that
            //  [true, 7] - calendar starts today and ends seven days after that
            //  ['2013-01-01', false] - calendar starts on January 1st 2013 and has no ending date ("format" is YYYY-MM-DD)
            //  [false, '2012-01-01'] - calendar ends today and starts on January 1st 2012 ("format" is YYYY-MM-DD)
            //
            //  note that "disabled_dates" property will still apply!
            //
            //  default is 0 (no restrictions)
            direction:          0,

            //  an array of disabled dates in the following format: 'day month year weekday' where "weekday" is optional
            //  and can be 0-6 (Saturday to Sunday); the syntax is similar to cron's syntax: the values are separated by
            //  spaces and may contain * (asterisk) - (dash) and , (comma) delimiters:
            //
            //  ['1 1 2012'] would disable January 1, 2012;
            //  ['* 1 2012'] would disable all days in January 2012;
            //  ['1-10 1 2012'] would disable January 1 through 10 in 2012;
            //  ['1,10 1 2012'] would disable January 1 and 10 in 2012;
            //  ['1-10,20,22,24 1-3 *'] would disable 1 through 10, plus the 22nd and 24th of January through March for every year;
            //  ['* * * 0,6'] would disable all Saturdays and Sundays;
            //  ['01 07 2012', '02 07 2012', '* 08 2012'] would disable 1st and 2nd of July 2012, and all of August of 2012
            //
            //  default is FALSE, no disabled dates
            disabled_dates:     false,

            //  week's starting day
            //
            //  valid values are 0 to 6, Sunday to Saturday
            //
            //  default is 1, Monday
            first_day_of_week:  1,

            //  format of the returned date
            //
            //  accepts the following characters for date formatting: d, D, j, l, N, w, S, F, m, M, n, Y, y borrowing
            //  syntax from (PHP's date function)
            //
            //  note that when setting a date format without days ('d', 'j'), the users will be able to select only years
            //  and months, and when setting a format without months and days ('F', 'm', 'M', 'n', 't', 'd', 'j'), the
            //  users will be able to select only years.
            //
            //  also note that the value of the "view" property (see below) may be overridden if it is the case: a value of
            //  "days" for the "view" property makes no sense if the date format doesn't allow the selection of days.
            //
            //  default is Y-m-d
            format:             'Y-m-d',

            //  should the icon for opening the datepicker be inside the element?
            //  if set to FALSE, the icon will be placed to the right of the parent element, while if set to TRUE it will
            //  be placed to the right of the parent element, but *inside* the element itself
            //
            //  default is TRUE
            inside:             true,

            //  the caption for the "Clear" button
            lang_clear_date:    'Clear',

            //  months names
            months:             ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],

            //  the offset, in pixels (x, y), to shift the date picker's position relative to the top-left of the icon
            //  that toggles the date picker
            //
            //  default is [20, -5]
            offset:             [20, -5],

            //  if set as a jQuery element with a MyZebra_Datepicker attached, that particular date picker will use the
            //  current date picker's value as starting date
            //  note that the rules set in the "direction" property will still apply, only that the reference date will
            //  not be the current system date but the value selected in the current date picker
            //  default is FALSE (not paired with another date picker)
            pair:              false,

            //  should the element the calendar is attached to, be read-only?
            //  if set to TRUE, a date can be set only through the date picker and cannot be entered manually
            //
            //  default is TRUE
            readonly_element:   true,

            //  should an extra column be shown, showing the number of each week?
            //  anything other than FALSE will enable this feature, and use the given value as column title
            //  i.e. show_week_number: 'Wk' would enable this feature and have "Wk" as the column's title
            //
            //  default is FALSE
            show_week_number:   false,

            //  a default date to start the date picker with
            //  must be specified in the format defined by the "format" property, or it will be ignored!
            //  note that this value is used only if there is no value in the field the date picker is attached to!
            start_date:         false,

            //  how should the date picker start; valid values are "days", "months" and "years"
            //  note that the date picker is always cycling days-months-years when clicking in the date picker's header,
            //  and years-months-days when selecting dates (unless one or more of the views are missing due to the date's
            //  format)
            //
            //  also note that the value of the "view" property may be overridden if the date's format requires so! (i.e.
            //  "days" for the "view" property makes no sense if the date format doesn't allow the selection of days)
            //
            //  default is "days"
            view:               'days',

            //  days of the week that are considered "weekend days"
            //  valid values are 0 to 6, Sunday to Saturday
            //
            //  default values are 0 and 6 (Saturday and Sunday)
            weekend_days:       [0, 6],

            //  callback function to be executed when a date is selected
            //  the callback function takes 3 parameters:
            //  -   the date in the format specified by the "format" attribute;
            //  -   the date in YYYY-MM-DD format
            //  -   the date as a JavaScript Date object
            onSelect:           null

        }

        // private properties
        var view, datepicker, icon, header, daypicker, monthpicker, yearpicker, footer, current_system_month, current_system_year,
            current_system_day, first_selectable_month, first_selectable_year, first_selectable_day, selected_month, selected_year,
            default_day, default_month, default_year, disabled_dates, shim, start_date, end_date, last_selectable_day,
            last_selectable_year, last_selectable_month, daypicker_cells, monthpicker_cells, yearpicker_cells, views;

        var plugin = this;

        plugin.settings = {}

        // the jQuery version of the element
        // "element" (without the $) will point to the DOM element
        var $element = $(element);

        /**
         *  Constructor method. Initializes the date picker.
         *
         *  @return void
         */
        var init = function(update) {

            // merge default settings with user-settings (unless we're just updating settings)
            if (!update) plugin.settings = $.extend({}, defaults, options);

            // if the element should be read-only, set the "readonly" attribute
            if (plugin.settings.readonly_element) $element.attr('readonly', 'readonly');

            // determine the views the user can cycle through, depending on the format
            // that is, if the format doesn't contain the day, the user will be able to cycle only through years and months,
            // whereas if the format doesn't contain months nor days, the user will only be able to select years

            var

                // the characters that may be present in the date format and that represent days, months and years
                date_chars = {
                    days:   ['d', 'j'],
                    months: ['F', 'm', 'M', 'n', 't'],
                    years:  ['o', 'Y', 'y']
                },

                // some defaults
                has_days = false,
                has_months = false,
                has_years = false;

            // iterate through all the character blocks
            for (type in date_chars)

                // iterate through the characters of each block
                $.each(date_chars[type], function(index, character) {

                    // if current character exists in the "format" property
                    if (plugin.settings.format.indexOf(character) > -1)

                        // set to TRUE the appropriate flag
                        if (type == 'days') has_days = true;
                        else if (type == 'months') has_months = true;
                        else if (type == 'years') has_years = true;

                });

            // if user can cycle through all the views, set the flag accordingly
            if (has_days && has_months && has_years) views = ['years', 'months', 'days'];

            // if user can cycle only through year and months, set the flag accordingly
            else if (!has_days && has_months && has_years) views = ['years', 'months'];

            // if user can only see the year picker, set the flag accordingly
            else if (!has_days && !has_months && has_years) views = ['years'];

            // if invalid format (no days, no months, no years) use the default where the user is able to cycle through
            // all the views
            else views = ['years', 'months', 'days'];

            // if the starting view is not amongst the views the user can cycle through, set the correct starting view
            if ($.inArray(plugin.settings.view, views) == -1) plugin.settings.view = views[views.length - 1];

            var

                // cache the current system date
                date = new Date(),

                // when the date picker's starting date depends on the value of another date picker, this value will be
                // set by the other date picker
                // this value will be used as base for all calculations (if not set, will be the same as the current
                // system date)
                reference_date = (!plugin.settings.reference_date ? ($element.data('zdp_reference_date') ? $element.data('zdp_reference_date') : date) : plugin.settings.reference_date),
                tmp_start_date, tmp_end_date;

            // reset these values here as this method might be called more than once during a date picker's lifetime
            // (when the selectable dates depend on the values from another date picker)
            start_date = undefined; end_date = undefined;

            // extract the date parts
            // also, save the current system month/day/year - we'll use them to highlight the current system date
            first_selectable_month = reference_date.getMonth();
            current_system_month = date.getMonth();
            first_selectable_year = reference_date.getFullYear();
            current_system_year = date.getFullYear();
            first_selectable_day = reference_date.getDate();
            current_system_day = date.getDate();

            // check if the calendar has any restrictions

            // calendar is future-only, starting today
            // it means we have a starting date (the current system date), but no ending date
            if (plugin.settings.direction === true) start_date = reference_date;

            // calendar is past only, ending today
            else if (plugin.settings.direction === false) {

                // it means we have an ending date (the reference date), but no starting date
                end_date = reference_date;

                // extract the date parts
                last_selectable_month = end_date.getMonth();
                last_selectable_year = end_date.getFullYear();
                last_selectable_day = end_date.getDate();

            } else if (

                // if direction is not given as an array and the value is an integer > 0
                (!$.isArray(plugin.settings.direction) && is_integer(plugin.settings.direction) && to_int(plugin.settings.direction) > 0) ||

                // or direction is given as an array
                ($.isArray(plugin.settings.direction) && (

                    // and first entry is boolean TRUE
                    plugin.settings.direction[0] === true ||
                    // or an integer > 0
                    (is_integer(plugin.settings.direction[0]) && plugin.settings.direction[0] > 0) ||
                    // or a valid date
                    (tmp_start_date = check_date(plugin.settings.direction[0]))

                ) && (

                    // and second entry is boolean FALSE
                    plugin.settings.direction[1] === false ||
                    // or integer >= 0
                    (is_integer(plugin.settings.direction[1]) && plugin.settings.direction[1] >= 0) ||
                    // or a valid date
                    (tmp_end_date = check_date(plugin.settings.direction[1]))

                ))

            ) {


                // if an exact starting date was given, use that as a starting date
                if (tmp_start_date) start_date = tmp_start_date;

                // otherwise
                else

                    // figure out the starting date
                    // use the Date object to normalize the date
                    // for example, 2011 05 33 will be transformed to 2011 06 02
                    start_date = new Date(
                        first_selectable_year,
                        first_selectable_month,
                        first_selectable_day + (!$.isArray(plugin.settings.direction) ? to_int(plugin.settings.direction) : to_int(plugin.settings.direction[0] === true ? 0 : plugin.settings.direction[0]))
                    );

                // re-extract the date parts
                first_selectable_month = start_date.getMonth();
                first_selectable_year = start_date.getFullYear();
                first_selectable_day = start_date.getDate();

                // if an exact ending date was given and the date is after the starting date, use that as a ending date
                if (tmp_end_date && +tmp_end_date > +start_date) end_date = tmp_end_date;

                // if have information about the ending date
                else if (!tmp_end_date && plugin.settings.direction[1] !== false && $.isArray(plugin.settings.direction))

                    // figure out the ending date
                    // use the Date object to normalize the date
                    // for example, 2011 05 33 will be transformed to 2011 06 02
                    end_date = new Date(
                        first_selectable_year,
                        first_selectable_month,
                        first_selectable_day + to_int(plugin.settings.direction[1])
                    );

                // if a valid ending date exists
                if (end_date) {

                    // extract the date parts
                    last_selectable_month = end_date.getMonth();
                    last_selectable_year = end_date.getFullYear();
                    last_selectable_day = end_date.getDate();

                }

            } else if (

                // if direction is not given as an array and the value is an integer < 0
                (!$.isArray(plugin.settings.direction) && is_integer(plugin.settings.direction) && to_int(plugin.settings.direction) < 0) ||

                // or direction is given as an array
                ($.isArray(plugin.settings.direction) && (

                    // and first entry is boolean FALSE
                    plugin.settings.direction[0] === false ||
                    // or an integer < 0
                    (is_integer(plugin.settings.direction[0]) && plugin.settings.direction[0] < 0)

                ) && (

                    // and second entry is integer >= 0
                    (is_integer(plugin.settings.direction[1]) && plugin.settings.direction[1] >= 0) ||
                    // or a valid date
                    (tmp_start_date = check_date(plugin.settings.direction[1]))

                ))

            ) {

                // figure out the ending date
                // use the Date object to normalize the date
                // for example, 2011 05 33 will be transformed to 2011 06 02
                end_date = new Date(
                    first_selectable_year,
                    first_selectable_month,
                    first_selectable_day + (!$.isArray(plugin.settings.direction) ? to_int(plugin.settings.direction) : to_int(plugin.settings.direction[0] === false ? 0 : plugin.settings.direction[0]))
                );

                // re-extract the date parts
                last_selectable_month = end_date.getMonth();
                last_selectable_year = end_date.getFullYear();
                last_selectable_day = end_date.getDate();

                // if an exact starting date was given, and the date is before the ending date, use that as a starting date
                if (tmp_start_date && +tmp_start_date < +end_date) start_date = tmp_start_date;

                // if have information about the starting date
                else if (!tmp_start_date && $.isArray(plugin.settings.direction))

                    // figure out the staring date
                    // use the Date object to normalize the date
                    // for example, 2011 05 33 will be transformed to 2011 06 02
                    start_date = new Date(
                        last_selectable_year,
                        last_selectable_month,
                        last_selectable_day - to_int(plugin.settings.direction[1])
                    );

                // if a valid starting date exists
                if (start_date) {

                    // extract the date parts
                    first_selectable_month = start_date.getMonth();
                    first_selectable_year = start_date.getFullYear();
                    first_selectable_day = start_date.getDate();

                }

            }

            // if a first selectable date exists but is disabled, find the actual first selectable date
            if (start_date && is_disabled(first_selectable_year, first_selectable_month, first_selectable_day)) {

                // loop until we find the first selectable year
                while (is_disabled(first_selectable_year)) {

                    // if calendar is past-only, decrement the year
                    if (!start_date) first_selectable_year--;

                    // otherwise, increment the year
                    else first_selectable_year++;

                    // because we've changed years, reset the month to January
                    first_selectable_month = 0;

                }

                // loop until we find the first selectable month
                while (is_disabled(first_selectable_year, first_selectable_month)) {

                    // if calendar is past-only, decrement the month
                    if (!start_date) first_selectable_month--;

                    // otherwise, increment the month
                    else first_selectable_month++;

                    // if we moved to a following year
                    if (first_selectable_month > 11) {

                        // increment the year
                        first_selectable_year++;

                        // reset the month to January
                        first_selectable_month = 0;

                    // if we moved to a previous year
                    } else if (first_selectable_month < 0) {

                        // decrement the year
                        first_selectable_year--;

                        // reset the month to January
                        first_selectable_month = 0;

                    }

                    // because we've changed months, reset the day to the first day of the month
                    first_selectable_day = 1;

                }

                // loop until we find the first selectable day
                while (is_disabled(first_selectable_year, first_selectable_month, first_selectable_day))

                    // if calendar is past-only, decrement the day
                    if (!start_date) first_selectable_day--;

                    // otherwise, increment the day
                    else first_selectable_day++;

                // use the Date object to normalize the date
                // for example, 2011 05 33 will be transformed to 2011 06 02
                date = new Date(first_selectable_year, first_selectable_month, first_selectable_day);

                // re-extract date parts from the normalized date
                // as we use them in the current loop
                first_selectable_year = date.getFullYear();
                first_selectable_month = date.getMonth();
                first_selectable_day = date.getDate();

            }

            // parse the rules for disabling dates and turn them into arrays of arrays

            // array that will hold the rules for disabling dates
            disabled_dates = [];

            // iterate through the rules for disabling dates
            $.each(plugin.settings.disabled_dates, function() {

                // split the values in rule by white space
                var rules = this.split(' ');

                // there can be a maximum of 4 rules (days, months, years and, optionally, day of the week)
                for (var i = 0; i < 4; i++) {

                    // if one of the values is not available
                    // replace it with a * (wildcard)
                    if (!rules[i]) rules[i] = '*';

                    // if rule contains a comma, create a new array by splitting the rule by commas
                    // if there are no commas create an array containing the rule's string
                    rules[i] = (rules[i].indexOf(',') > -1 ? rules[i].split(',') : new Array(rules[i]));

                    // iterate through the items in the rule
                    for (var j = 0; j < rules[i].length; j++)

                        // if item contains a dash (defining a range)
                        if (rules[i][j].indexOf('-') > -1) {

                            // get the lower and upper limits of the range
                            var limits = rules[i][j].match(/^([0-9]+)\-([0-9]+)/);

                            // if range is valid
                            if (null != limits) {

                                // iterate through the range
                                for (var k = to_int(limits[1]); k <= to_int(limits[2]); k++)

                                    // if value is not already among the values of the rule
                                    // add it to the rule
                                    if ($.inArray(k, rules[i]) == -1) rules[i].push(k + '');

                                // remove the range indicator
                                rules[i].splice(j, 1);

                            }

                        }

                    // iterate through the items in the rule
                    // and make sure that numbers are numbers
                    for (j = 0; j < rules[i].length; j++) rules[i][j] = (isNaN(to_int(rules[i][j])) ? rules[i][j] : to_int(rules[i][j]));

                }

                // add to the list of processed rules
                disabled_dates.push(rules);

            });

            // get the default date, from the element, and check if it represents a valid date, according to the required format
            var default_date = check_date($element.val() || (plugin.settings.start_date ? plugin.settings.start_date : ''));

            // if there is a default date but it is disabled
            if (default_date && is_disabled(default_date.getFullYear(), default_date.getMonth(), default_date.getDate()))
            {

                // clear the value of the parent element
                $element.val('');
                //$element.trigger('change');
            }

            // updates value for the date picker whose starting date depends on the selected date (if any)
            update_dependent(default_date);

            // if we just needed to recompute the things above, return now
            if (update) return;

            // if date picker is not always visible
            if (!plugin.settings.always_visible) {

                // create the calendar icon (show a disabled icon if the element is disabled)
                var html = '<button type="button" class="MyZebra_DatePicker_Icon' + ($element.attr('disabled') == 'disabled' ? ' MyZebra_DatePicker_Icon_Disabled' : '') + '">Pick a date</button>';

                // convert to a jQuery object
                icon = $(html);

                // a reference to the icon, as a global property
                plugin.icon = icon;

                // by default, only clicking the calendar icon shows the date picker
                // if text box is read-only, clicking it, will also show the date picker

                // attach the click event
                (plugin.settings.readonly_element ? icon.add($element) : icon).bind('click', function(e) {

                    e.preventDefault();

                    // if element is not disabled
                    if (!$element.attr('disabled'))

                        // if the date picker is visible, hide it
                        if (datepicker.css('display') != 'none') plugin.hide();

                        // if the date picker is not visible, show it
                        else plugin.show();

                });

                // inject the icon into the DOM
                // element uses a container, so icon position can be styled fully with css
                icon.insertAfter(element);

                /*var

                    // get element's position relative to the offset parent
                    element_position = $(element).position(),

                    // get element's width and height
                    element_height = $(element).outerHeight(true),
                    element_width = $(element).outerWidth(true);

                    // get icon's width and height
                    icon_width = icon.outerWidth(true),
                    icon_height = icon.outerHeight(true);

                // if icon is to be placed *inside* the element
                if (plugin.settings.inside) {

                    // add an extra class to the icon
                    icon.addClass('MyZebra_DatePicker_Icon_Inside');

                    // position the icon accordingly
                    icon.css({
                        'left': element_position.left + element_width - icon_width,
                        'top': element_position.top + ((element_height - icon_height) / 2)
                    });

                // if icon is to be placed to the right of the element
                } else

                    // position the icon accordingly
                    icon.css({
                        'left': element_position.left + element_width,
                        'top': element_position.top + ((element_height - icon_height) / 2)
                    });
                */
            }

            // generate the container that will hold everything
            var html = '' +
                '<div class="MyZebra_DatePicker">' +
                    '<table class="dp_header">' +
                        '<tr>' +
                            '<td class="dp_previous">&laquo;</td>' +
                            '<td class="dp_caption">&nbsp;</td>' +
                            '<td class="dp_next">&raquo;</td>' +
                        '</tr>' +
                    '</table>' +
                    '<table class="dp_daypicker"></table>' +
                    '<table class="dp_monthpicker"></table>' +
                    '<table class="dp_yearpicker"></table>' +
                    '<table class="dp_footer">' +
                        '<tr><td>' + plugin.settings.lang_clear_date + '</td></tr>' +
                    '</table>' +
                '</div>';

            // create a jQuery object out of the HTML above and create a reference to it
            datepicker = $(html);

            // a reference to the calendar, as a global property
            plugin.datepicker = datepicker;

            // create references to the different parts of the date picker
            header = $('table.dp_header', datepicker);
            daypicker = $('table.dp_daypicker', datepicker);
            monthpicker = $('table.dp_monthpicker', datepicker);
            yearpicker = $('table.dp_yearpicker', datepicker);
            footer = $('table.dp_footer', datepicker);

            // if date picker is not always visible
            if (!plugin.settings.always_visible)

                // inject the container into the DOM
                $('body').append(datepicker);

            // otherwise, if element is not disabled
            else if (!$element.attr('disabled')) {

                // inject the date picker into the designated container element
                plugin.settings.always_visible.append(datepicker);

                // and make it visible right away
                plugin.show();

            }

            // add the mouseover/mousevents to all to the date picker's cells
            // except those that are not selectable
            datepicker.
                delegate('td:not(.dp_disabled, .dp_weekend_disabled, .dp_not_in_month, .dp_blocked, .dp_week_number)', 'mouseover', function() {
                    $(this).addClass('dp_hover');
                }).
                delegate('td:not(.dp_disabled, .dp_weekend_disabled, .dp_not_in_month, .dp_blocked, .dp_week_number)', 'mouseout', function() {
                    $(this).removeClass('dp_hover');
                });

            // prevent text highlighting for the text in the header
            // (for the case when user keeps clicking the "next" and "previous" buttons)
            disable_text_select($('td', header));

            // event for when clicking the "previous" button
            $('.dp_previous', header).bind('click', function() {

                // if button is not disabled
                if (!$(this).hasClass('dp_blocked')) {

                    // if view is "months"
                    // decrement year by one
                    if (view == 'months') selected_year--;

                    // if view is "years"
                    // decrement years by 12
                    else if (view == 'years') selected_year -= 12;

                    // if view is "days"
                    // decrement the month and
                    // if month is out of range
                    else if (--selected_month < 0) {

                        // go to the last month of the previous year
                        selected_month = 11;
                        selected_year--;

                    }

                    // generate the appropriate view
                    manage_views();

                }

            });

            // attach a click event to the caption in header
            $('.dp_caption', header).bind('click', function() {

                // if current view is "days", take the user to the next view, depending on the format
                if (view == 'days') view = ($.inArray('months', views) > -1 ? 'months' : ($.inArray('years', views) > -1 ? 'years' : 'days'));

                // if current view is "months", take the user to the next view, depending on the format
                else if (view == 'months') view = ($.inArray('years', views) > -1 ? 'years' : ($.inArray('days', views) > -1 ? 'days' : 'months'));

                // if current view is "years", take the user to the next view, depending on the format
                else view = ($.inArray('days', views) > -1 ? 'days' : ($.inArray('months', views) > -1 ? 'months' : 'years'));

                // generate the appropriate view
                manage_views();

            });

            // event for when clicking the "next" button
            $('.dp_next', header).bind('click', function() {

                // if button is not disabled
                if (!$(this).hasClass('dp_blocked')) {

                    // if view is "months"
                    // increment year by 1
                    if (view == 'months') selected_year++;

                    // if view is "years"
                    // increment years by 12
                    else if (view == 'years') selected_year += 12;

                    // if view is "days"
                    // increment the month and
                    // if month is out of range
                    else if (++selected_month == 12) {

                        // go to the first month of the next year
                        selected_month = 0;
                        selected_year++;

                    }

                    // generate the appropriate view
                    manage_views();

                }

            });

            // attach a click event for the cells in the day picker
            daypicker.delegate('td:not(.dp_disabled, .dp_weekend_disabled, .dp_not_in_month, .dp_week_number)', 'click', function() {

                // put selected date in the element the plugin is attached to, and hide the date picker
                select_date(selected_year, selected_month, to_int($(this).html()), 'days', $(this));

            });

            // attach a click event for the cells in the month picker
            monthpicker.delegate('td:not(.dp_disabled)', 'click', function() {

                // get the month we've clicked on
                var matches = $(this).attr('class').match(/dp\_month\_([0-9]+)/);

                // set the selected month
                selected_month = to_int(matches[1]);

                // if user can select only years and months
                if ($.inArray('days', views) == -1)

                    // put selected date in the element the plugin is attached to, and hide the date picker
                    select_date(selected_year, selected_month, 1, 'months', $(this));

                else {

                    // direct the user to the "days" view
                    view = 'days';

                    // if date picker is always visible
                    // empty the value in the text box the date picker is attached to
                    if (plugin.settings.always_visible) {$element.val('');/*$element.trigger('change');*/}

                    // generate the appropriate view
                    manage_views();

                }

            });

            // attach a click event for the cells in the year picker
            yearpicker.delegate('td:not(.dp_disabled)', 'click', function() {

                // set the selected year
                selected_year = to_int($(this).html());

                // if user can select only years
                if ($.inArray('months', views) == -1)

                    // put selected date in the element the plugin is attached to, and hide the date picker
                    select_date(selected_year, 1, 1, 'years', $(this));

                else {

                    // direct the user to the "months" view
                    view = 'months';

                    // if date picker is always visible
                    // empty the value in the text box the date picker is attached to
                    if (plugin.settings.always_visible) {$element.val('');/*$element.trigger('change');*/}

                    // generate the appropriate view
                    manage_views();

                }

            });

            // bind a function to the onClick event on the table cell in the footer
            $('td', footer).bind('click', function(e) {

                e.preventDefault();

                // clear the element's value
                $element.val('');
                //$element.trigger('change');

                // if date picker is not always visible
                if (!plugin.settings.always_visible) {

                    // reset these values
                    default_day = null; default_month = null; default_year = null; selected_month = null; selected_year = null;

                    // remove the footer element
                    footer.css('display', 'none');

                }

                // hide the date picker
                plugin.hide();

            });

            // if date picker is not always visible
            if (!plugin.settings.always_visible)

                // bind some events to the document
                $(document).bind({

                    //whenever anything is clicked on the page or a key is pressed
                    'mousedown': plugin._mousedown,
                    'keyup': plugin._keyup

                });

            // last thing is to pre-render some of the date picker right away
            manage_views();

        }

        /**
         *  Hides the date picker.
         *
         *  @return void
         */
        plugin.hide = function() {

            // if date picker is not always visible
            if (!plugin.settings.always_visible) {

                // hide the iFrameShim in Internet Explorer 6
                iframeShim('hide');

                // hide the date picker
                datepicker.css('display', 'none');

            }

        }

        /**
         *  Shows the date picker.
         *
         *  @return void
         */
        plugin.show = function() {

            // always show the view defined in settings
            view = plugin.settings.view;

            // get the default date, from the element, and check if it represents a valid date, according to the required format
            var default_date = check_date($element.val() || (plugin.settings.start_date ? plugin.settings.start_date : ''));

            // if the value represents a valid date
            if (default_date) {

                // extract the date parts
                // we'll use these to highlight the default date in the date picker and as starting point to
                // what year and month to start the date picker with
                // why separate values? because selected_* will change as user navigates within the date picker
                default_month = default_date.getMonth();
                selected_month = default_date.getMonth();
                default_year = default_date.getFullYear();
                selected_year = default_date.getFullYear();
                default_day = default_date.getDate();

                // if the default date represents a disabled date
                if (is_disabled(default_year, default_month, default_day)) {

                    // clear the value of the parent element
                    $element.val('');
                    //$element.trigger('change');

                    // the calendar will start with the first selectable year/month
                    selected_month = first_selectable_month;
                    selected_year = first_selectable_year;

                }

            // if a default value is not available, or value does not represent a valid date
            } else {

                // the calendar will start with the first selectable year/month
                selected_month = first_selectable_month;
                selected_year = first_selectable_year;

            }

            // generate the appropriate view
            manage_views();

            // if date picker is not always visible
            if (!plugin.settings.always_visible) {

                var

                    // get the date picker width and height
                    datepicker_width = datepicker.outerWidth(),
                    datepicker_height = datepicker.outerHeight(),

                    // compute the date picker's default left and top
                    left = icon.offset().left + plugin.settings.offset[0],
                    top = icon.offset().top - datepicker_height + plugin.settings.offset[1],

                    // get browser window's width and height
                    window_width = $(window).width(),
                    window_height = $(window).height(),

                    // get browser window's horizontal and vertical scroll offsets
                    window_scroll_top = $(window).scrollTop(),
                    window_scroll_left = $(window).scrollLeft();

                // if date picker is outside the viewport, adjust its position so that it is visible
                if (left + datepicker_width > window_scroll_left + window_width) left = window_scroll_left + window_width - datepicker_width;
                if (left < window_scroll_left) left = window_scroll_left;
                if (top + datepicker_height > window_scroll_top + window_height) top = window_scroll_top + window_height - datepicker_height;
                if (top < window_scroll_top) top = window_scroll_top;

                // make the date picker visible
                datepicker.css({
                    'left':     left,
                    'top':      top
                });

                // fade-in the date picker
                // for Internet Explorer < 9 show the date picker instantly or fading alters the font's weight
                datepicker.fadeIn($.browser.msie && $.browser.version.match(/^[6-8]/) ? 0 : 150, 'linear');

                // show the iFrameShim in Internet Explorer 6
                iframeShim();

            // if date picker is always visible, show it
            } else datepicker.css('display', 'block');

        }

        /**
         *  Updates the configuration options given as argument
         *
         *  @param  object  values  An object containing any number of configuration options to be updated
         *
         *  @return void
         */
        plugin.update = function(values) {

            // if original direction not saved, save it now
            if (plugin.original_direction) plugin.original_direction = plugin.direction;

            // update configuration options
            plugin.settings = $.extend(plugin.settings, values);

            // re-initialize the object with the new options
            init(true);

        }

        /**
         *  Checks if a string represents a valid date according to the format defined by the "format" property.
         *
         *  @param  string  str_date    A string representing a date, formatted accordingly to the "format" property.
         *                              For example, if "format" is "Y-m-d" the string should look like "2011-06-01"
         *
         *  @return boolean             Returns TRUE if string represents a valid date according formatted according to
         *                              the "format" property or FALSE otherwise.
         *
         *  @access private
         */
        var check_date = function(str_date) {

            // treat argument as a string
            str_date += '';

            // if value is given
            if ($.trim(str_date) != '') {

                var

                    // prepare the format by removing white space from it
                    // and also escape characters that could have special meaning in a regular expression
                    format = escape_regexp(plugin.settings.format.replace(/\s/g, '')),

                    // allowed characters in date's format
                    format_chars = ['d','D','j','l','N','S','w','F','m','M','n','Y','y'],

                    // "matches" will contain the characters defining the date's format
                    matches = new Array,

                    // "regexp" will contain the regular expression built for each of the characters used in the date's format
                    regexp = new Array;

                // iterate through the allowed characters in date's format
                for (var i = 0; i < format_chars.length; i++)

                    // if character is found in the date's format
                    if ((position = format.indexOf(format_chars[i])) > -1)

                        // save it, alongside the character's position
                        matches.push({character: format_chars[i], position: position});

                // sort characters defining the date's format based on their position, ascending
                matches.sort(function(a, b){ return a.position - b.position });

                // iterate through the characters defining the date's format
                $.each(matches, function(index, match) {

                    // add to the array of regular expressions, based on the character
                    switch (match.character) {

                        case 'd': regexp.push('0[1-9]|[12][0-9]|3[01]'); break;
                        case 'D': regexp.push('[a-z]{3}'); break;
                        case 'j': regexp.push('[1-9]|[12][0-9]|3[01]'); break;
                        case 'l': regexp.push('[a-z]+'); break;
                        case 'N': regexp.push('[1-7]'); break;
                        case 'S': regexp.push('st|nd|rd|th'); break;
                        case 'w': regexp.push('[0-6]'); break;
                        case 'F': regexp.push('[a-z]+'); break;
                        case 'm': regexp.push('0[1-9]|1[012]+'); break;
                        case 'M': regexp.push('[a-z]{3}'); break;
                        case 'n': regexp.push('[1-9]|1[012]'); break;
                        case 'Y': regexp.push('[0-9]{4}'); break;
                        case 'y': regexp.push('[0-9]{2}'); break;

                    }

                });

                // if we have an array of regular expressions
                if (regexp.length) {

                    // we will replace characters in the date's format in reversed order
                    matches.reverse();

                    // iterate through the characters in date's format
                    $.each(matches, function(index, match) {

                        // replace each character with the appropriate regular expression
                        format = format.replace(match.character, '(' + regexp[regexp.length - index - 1] + ')');

                    });

                    // the final regular expression
                    regexp = new RegExp('^' + format + '$', 'ig');

                    // if regular expression was matched
                    if ((segments = regexp.exec(str_date.replace(/\s/g, '')))) {

                        // check if date is a valid date (i.e. there's no February 31)

                        var original_day,
                            original_month,
                            original_year,
                            english_days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                            english_months = ['January','February','March','April','May','June','July','August','September','October','November','December'],
                            iterable,

                            // by default, we assume the date is valid
                            valid = true;

                        // reverse back the characters in the date's format
                        matches.reverse();

                        // iterate through the characters in the date's format
                        $.each(matches, function(index, match) {

                            // if the date is not valid, don't look further
                            if (!valid) return true;

                            // based on the character
                            switch (match.character) {

                                case 'm':
                                case 'n':

                                    // extract the month from the value entered by the user
                                    original_month = to_int(segments[index + 1]);

                                    break;

                                case 'd':
                                case 'j':

                                    // extract the day from the value entered by the user
                                    original_day = to_int(segments[index + 1]);

                                    break;

                                case 'D':
                                case 'l':
                                case 'F':
                                case 'M':

                                    // if day is given as day name, we'll check against the names in the used language
                                    if (match.character == 'D' || match.character == 'l') iterable = plugin.settings.days;

                                    // if month is given as month name, we'll check against the names in the used language
                                    else iterable = plugin.settings.months;

                                    // by default, we assume the day or month was not entered correctly
                                    valid = false;

                                    // iterate through the month/days in the used language
                                    $.each(iterable, function(key, value) {

                                        // if month/day was entered correctly, don't look further
                                        if (valid) return true;

                                        // if month/day was entered correctly
                                        if (segments[index + 1].toLowerCase() == value.substring(0, (match.character == 'D' || match.character == 'M' ? 3 : value.length)).toLowerCase()) {

                                            // extract the day/month from the value entered by the user
                                            switch (match.character) {

                                                case 'D': segments[index + 1] = english_days[key].substring(0, 3); break;
                                                case 'l': segments[index + 1] = english_days[key]; break;
                                                case 'F': segments[index + 1] = english_months[key]; original_month = key + 1; break;
                                                case 'M': segments[index + 1] = english_months[key].substring(0, 3); original_month = key + 1; break;

                                            }

                                            // day/month value is valid
                                            valid = true;

                                        }

                                    });

                                    break;

                                case 'Y':

                                    // extract the year from the value entered by the user
                                    original_year = to_int(segments[index + 1]);

                                    break;

                                case 'y':

                                    // extract the year from the value entered by the user
                                    original_year = '19' + to_int(segments[index + 1]);

                                    break;

                            }
                        });

                        // if everything is ok so far
                        if (valid) {

                            // generate a Date object using the values entered by the user
                            // (handle also the case when original_month and/or original_day are undefined - i.e date format is "Y-m" or "Y")
                            var date = new Date(original_year, (original_month || 1) - 1, original_day || 1);

                            // if, after that, the date is the same as the date entered by the user
                            if (date.getFullYear() == original_year && date.getDate() == (original_day || 1) && date.getMonth() == ((original_month || 1) - 1))

                                // return the date as JavaScript date object
                                return date;

                        }

                    }

                }

                // if script gets this far, return false as something must've went wrong
                return false;

            }

        }

        /**
         *  Prevents the possibility of selecting text on a given element. Used on the "previous" and "next" buttons
         *  where text might get accidentally selected when user quickly clicks on the buttons.
         *
         *  Code by http://chris-barr.com/index.php/entry/disable_text_selection_with_jquery/
         *
         *  @param  jQuery Element  el  A jQuery element on which to prevents text selection.
         *
         *  @return void
         *
         *  @access private
         */
        var disable_text_select = function(el) {

            // if browser is Firefox
			if ($.browser.mozilla) el.css('MozUserSelect', 'none');

            // if browser is Internet Explorer
            else if ($.browser.msie) el.bind('selectstart', function() { return false });

            // for the other browsers
			else el.mousedown(function() { return false });

        }

        /**
         *  Escapes special characters in a string, preparing it for use in a regular expression.
         *
         *  @param  string  str     The string in which special characters should be escaped.
         *
         *  @return string          Returns the string with escaped special characters.
         *
         *  @access private
         */
        var escape_regexp = function(str) {

            // return string with special characters escaped
            return str.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1');

        }

        /**
         *  Formats a JavaScript date object to the format specified by the "format" property.
         *  Code taken from http://electricprism.com/aeron/calendar/
         *
         *  @param  date    date    A valid JavaScript date object
         *
         *  @return string          Returns a string containing the formatted date
         *
         *  @access private
         */
        var format = function(date) {

            var result = '',

                // extract parts of the date:
                // day number, 1 - 31
                j = date.getDate(),

                // day of the week, 0 - 6, Sunday - Saturday
                w = date.getDay(),

                // the name of the day of the week Sunday - Saturday
                l = plugin.settings.days[w],

                // the month number, 1 - 12
                n = date.getMonth() + 1,

                // the month name, January - December
                f = plugin.settings.months[n - 1],

                // the year (as a string)
                y = date.getFullYear() + '';

            // iterate through the characters in the format
            for (var i = 0; i < plugin.settings.format.length; i++) {

                // extract the current character
                var chr = plugin.settings.format.charAt(i);

                // see what character it is
                switch(chr) {

                    // year as two digits
                    case 'y': y = y.substr(2);

                    // year as four digits
                    case 'Y': result += y; break;

                    // month number, prefixed with 0
                    case 'm': n = str_pad(n, 2);

                    // month number, not prefixed with 0
                    case 'n': result += n; break;

                    // month name, three letters
                    case 'M': f = f.substr(0, 3);

                    // full month name
                    case 'F': result += f; break;

                    // day number, prefixed with 0
                    case 'd': j = str_pad(j, 2);

                    // day number not prefixed with 0
                    case 'j': result += j; break;

                    // day name, three letters
                    case 'D': l = l.substr(0, 3);

                    // full day name
                    case 'l': result += l; break;

                    // ISO-8601 numeric representation of the day of the week, 1 - 7
                    case 'N': w++;

                    // day of the week, 0 - 6
                    case 'w': result += w; break;

                    // English ordinal suffix for the day of the month, 2 characters
                    // (st, nd, rd or th (works well with j))
                    case 'S':

                        if (j % 10 == 1 && j != '11') result += 'st';

                        else if (j % 10 == 2 && j != '12') result += 'nd';

                        else if (j % 10 == 3 && j != '13') result += 'rd';

                        else result += 'th';

                        break;

                    // this is probably the separator
                    default: result += chr;

                }

            }

            // return formated date
            return result;

        }

        /**
         *  Generates the day picker view, and displays it
         *
         *  @return void
         *
         *  @access private
         */
        var generate_daypicker = function() {

            var

                // get the number of days in the selected month
                days_in_month = new Date(selected_year, selected_month + 1, 0).getDate(),

                // get the selected month's starting day (from 0 to 6)
                first_day = new Date(selected_year, selected_month, 1).getDay(),

                // how many days are there in the previous month
                days_in_previous_month = new Date(selected_year, selected_month, 0).getDate(),

                // how many days are there to be shown from the previous month
                days_from_previous_month = first_day - plugin.settings.first_day_of_week;

            // the final value of how many days are there to be shown from the previous month
            days_from_previous_month = days_from_previous_month < 0 ? 7 + days_from_previous_month : days_from_previous_month;

            // manage header caption and enable/disable navigation buttons if necessary
            manage_header(plugin.settings.months[selected_month] + ', ' + selected_year);

            // start generating the HTML
            var html = '<tr>';

            // if a column featuring the number of the week is to be shown
            if (plugin.settings.show_week_number)

                // column title
                html += '<th>' + plugin.settings.show_week_number + '</th>';

            // name of week days
            // show only the first two letters
            // and also, take in account the value of the "first_day_of_week" property
            for (var i = 0; i < 7; i++)

                html += '<th>' + plugin.settings.days[(plugin.settings.first_day_of_week + i) % 7].substr(0, 2) + '</th>';

            html += '</tr><tr>';

            // the calendar shows a total of 42 days
            for (var i = 0; i < 42; i++) {

                // seven days per row
                if (i > 0 && i % 7 == 0) html += '</tr><tr>';

                // if week number is to be shown
                if (i % 7 == 0 && plugin.settings.show_week_number) {

                    // get the ISO 8601 week number
                    // code taken from http://www.epoch-calendar.com/support/getting_iso_week.html

                    var

                        // current normalized date
                        current_date = new Date(selected_year, selected_month, (i - days_from_previous_month + 1)),

                        // create a new date object representing january 1st of the currently selected year
                        year_start_date = new Date(selected_year, 0, 1),

                        // the day of week day the year begins with (0 to 6)
                        // (taking locale into account)
                        start_weekday = year_start_date.getDay() - plugin.settings.first_day_of_week,

                        // the number of the current day
                        current_day_number = Math.floor(
                            (
                                current_date.getTime() - year_start_date.getTime() -
                                (current_date.getTimezoneOffset() - year_start_date.getTimezoneOffset()) * 60000
                            ) / 86400000
                        ) + 1,

                        // this will be the current week number
                        week_number;

                    // normalize starting day of the year in case it is < 0
                    start_weekday = (start_weekday >= 0 ? start_weekday : start_weekday + 7);

                    //if the year starts before the middle of a week
                    if (start_weekday < 4) {

                        // get the week's number
                        week_number = Math.floor((current_day_number + start_weekday - 1) / 7) + 1;

                        // if week is > 52 then we have to figure out if it is the 53rd week of the current year
                        // or the 1st week of the next year
                        if (week_number + 1 > 52) {

                            var

                                // create a date object represnting january 1st of the next year
                                tmp_year = new Date(current_date.getFullYear() + 1, 0, 1),

                                // the day of week day the year begins with (0 to 6)
                                // (taking locale into account)
                                tmp_day = nYear.getDay() - plugin.settings.first_day_of_week;

                            // normalize starting day of the year in case it is < 0
                            tmp_day = (tmp_day >= 0 ? tmp_day : tmp_day + 7);

                            // if the next year starts before the middle of the week,
                            // the week number represents the 1st week of that year
                            week_number = (tmp_day < 4 ? 1 : 53);

                        }

                    // otherwise, this is the week's number
                    } else week_number = Math.floor((current_day_number + start_weekday - 1) / 7)

                    // add week number
                    html += '<td class="dp_week_number">' + week_number + '</td>';

                }

                // the number of the day in month
                var day = (i - days_from_previous_month + 1);

                // if this is a day from the previous month
                if (i < days_from_previous_month)

                    html += '<td class="dp_not_in_month">' + (days_in_previous_month - days_from_previous_month + i + 1) + '</td>';

                // if this is a day from the next month
                else if (day > days_in_month)

                    html += '<td class="dp_not_in_month">' + (day - days_in_month) + '</td>';

                // if this is a day from the current month
                else {

                    var

                        // get the week day (0 to 6, Sunday to Saturday)
                        weekday = (plugin.settings.first_day_of_week + i) % 7,

                        class_name = '';

                    // if date needs to be disabled
                    if (is_disabled(selected_year, selected_month, day)) {

                        // if day is in weekend
                        if ($.inArray(weekday, plugin.settings.weekend_days) > -1) class_name = 'dp_weekend_disabled';

                        // if work day
                        else class_name += ' dp_disabled';

                        // highlight the current system date
                        if (selected_month == current_system_month && selected_year == current_system_year && current_system_day == day) class_name += ' dp_disabled_current';

                    // if there are no restrictions
                    } else {

                        // if day is in weekend
                        if ($.inArray(weekday, plugin.settings.weekend_days) > -1) class_name = 'dp_weekend';

                        // highlight the currently selected date
                        if (selected_month == default_month && selected_year == default_year && default_day == day) class_name += ' dp_selected';

                        // highlight the current system date
                        if (selected_month == current_system_month && selected_year == current_system_year && current_system_day == day) class_name += ' dp_current';

                    }

                    // print the day of the month
                    html += '<td' + (class_name != '' ? ' class="' + $.trim(class_name) + '"' : '') + '>' + str_pad(day, 2) + '</td>';

                }

            }

            // wrap up generating the day picker
            html += '</tr>';

            // inject the day picker into the DOM
            daypicker.html($(html));

            // if date picker is always visible
            if (plugin.settings.always_visible)

                // cache all the cells
                // (we need them so that we can easily remove the "dp_selected" class from all of them when user selects a date)
                daypicker_cells = $('td:not(.dp_disabled, .dp_weekend_disabled, .dp_not_in_month, .dp_blocked, .dp_week_number)', daypicker);

            // make the day picker visible
            daypicker.css('display', '');

        }

        /**
         *  Generates the month picker view, and displays it
         *
         *  @return void
         *
         *  @access private
         */
        var generate_monthpicker = function() {

            // manage header caption and enable/disable navigation buttons if necessary
            manage_header(selected_year);

            // start generating the HTML
            var html = '<tr>';

            // iterate through all the months
            for (var i = 0; i < 12; i++) {

                // three month per row
                if (i > 0 && i % 3 == 0) html += '</tr><tr>';

                var class_name = 'dp_month_' + i;

                // if month needs to be disabled
                if (is_disabled(selected_year, i)) class_name += ' dp_disabled';

                // else, if a date is already selected and this is that particular month, highlight it
                else if (default_month !== false && default_month == i) class_name += ' dp_selected';

                // else, if this the current system month, highlight it
                else if (current_system_month == i && current_system_year == selected_year) class_name += ' dp_current';

                // first three letters of the month's name
                html += '<td class="' + $.trim(class_name) + '">' + plugin.settings.months[i].substr(0, 3) + '</td>';

            }

            // wrap up
            html += '</tr>';

            // inject into the DOM
            monthpicker.html($(html));

            // if date picker is always visible
            if (plugin.settings.always_visible)

                // cache all the cells
                // (we need them so that we can easily remove the "dp_selected" class from all of them when user selects a month)
                monthpicker_cells = $('td:not(.dp_disabled)', monthpicker);

            // make the month picker visible
            monthpicker.css('display', '');

        }

        /**
         *  Generates the year picker view, and displays it
         *
         *  @return void
         *
         *  @access private
         */
        var generate_yearpicker = function() {

            // manage header caption and enable/disable navigation buttons if necessary
            manage_header(selected_year - 7 + ' - ' + (selected_year + 4));

            // start generating the HTML
            var html = '<tr>';

            // we're showing 9 years at a time, current year in the middle
            for (var i = 0; i < 12; i++) {

                // three years per row
                if (i > 0 && i % 3 == 0) html += '</tr><tr>';

                var class_name = '';

                // if year needs to be disabled
                if (is_disabled(selected_year - 7 + i)) class_name += ' dp_disabled';

                // else, if a date is already selected and this is that particular year, highlight it
                else if (default_year && default_year == selected_year - 7 + i) class_name += ' dp_selected'

                // else, if this is the current system year, highlight it
                else if (current_system_year == (selected_year - 7 + i)) class_name += ' dp_current';

                // first three letters of the month's name
                html += '<td' + ($.trim(class_name) != '' ? ' class="' + $.trim(class_name) + '"' : '') + '>' + (selected_year - 7 + i) + '</td>';

            }

            // wrap up
            html += '</tr>';

            // inject into the DOM
            yearpicker.html($(html));

            // if date picker is always visible
            if (plugin.settings.always_visible)

                // cache all the cells
                // (we need them so that we can easily remove the "dp_selected" class from all of them when user selects a year)
                yearpicker_cells = $('td:not(.dp_disabled)', yearpicker);

            // make the year picker visible
            yearpicker.css('display', '');

        }

        /**
         *  Generates an iFrame shim in Internet Explorer 6 so that the date picker appears above select boxes.
         *
         *  @return void
         *
         *  @access private
         */
        var iframeShim = function(action) {

            // this is necessary only if browser is Internet Explorer 6
    		if ($.browser.msie && $.browser.version.match(/^6/)) {

                // if the iFrame was not yet created
                // "undefined" evaluates as FALSE
                if (!shim) {

                    // the iFrame has to have the element's zIndex minus 1
                    var zIndex = to_int(datepicker.css('zIndex')) - 1;

                    // create the iFrame
                    shim = jQuery('<iframe>', {
                        'src':                  'javascript:document.write("")',
                        'scrolling':            'no',
                        'frameborder':          0,
                        'allowtransparency':    'true',
                        css: {
                            'zIndex':       zIndex,
                            'position':     'absolute',
                            'top':          -1000,
                            'left':         -1000,
                            'width':        datepicker.outerWidth(),
                            'height':       datepicker.outerHeight(),
                            'filter':       'progid:DXImageTransform.Microsoft.Alpha(opacity=0)',
                            'display':      'none'
                        }
                    });

                    // inject iFrame into DOM
                    $('body').append(shim);

                }

                // what do we need to do
                switch (action) {

                    // hide the iFrame?
                    case 'hide':

                        // set the iFrame's display property to "none"
                        shim.css('display', 'none');

                        break;

                    // show the iFrame?
                    default:

                        // get date picker top and left position
                        var offset = datepicker.offset();

                        // position the iFrame shim right underneath the date picker
                        // and set its display to "block"
                        shim.css({
                            'top':      offset.top,
                            'left':     offset.left,
                            'display':  'block'
                        });

                }

            }

        }

        /**
         *  Checks if, according to the restrictions of the calendar and/or the values defined by the "disabled_dates"
         *  property, a day, a month or a year needs to be disabled.
         *
         *  @param  integer     year    The year to check
         *  @param  integer     month   The month to check
         *  @param  integer     day     The day to check
         *
         *  @return boolean         Returns TRUE if the given value is not disabled or FALSE otherwise
         *
         *  @access private
         */
        var is_disabled = function(year, month, day) {

            // if calendar has direction restrictions
            if (!(!$.isArray(plugin.settings.direction) && to_int(plugin.settings.direction) === 0)) {

                var
                    // normalize and merge arguments then transform the result to an integer
                    now = to_int(str_concat(year, (typeof month != 'undefined' ? str_pad(month, 2) : ''), (typeof day != 'undefined' ? str_pad(day, 2) : ''))),

                    // get the length of the argument
                    len = (now + '').length;

                // if we're checking days
                if (len == 8 && (

                    // day is before the first selectable date
                    (typeof start_date != 'undefined' && now < to_int(str_concat(first_selectable_year, str_pad(first_selectable_month, 2), str_pad(first_selectable_day, 2)))) ||

                    // or day is after the last selectable date
                    (typeof end_date != 'undefined' && now > to_int(str_concat(last_selectable_year, str_pad(last_selectable_month, 2), str_pad(last_selectable_day, 2))))

                // day needs to be disabled
                )) return true;

                // if we're checking months
                else if (len == 6 && (

                    // month is before the first selectable month
                    (typeof start_date != 'undefined' && now < to_int(str_concat(first_selectable_year, str_pad(first_selectable_month, 2)))) ||

                    // or day is after the last selectable date
                    (typeof end_date != 'undefined' && now > to_int(str_concat(last_selectable_year, str_pad(last_selectable_month, 2))))

                // month needs to be disabled
                )) return true;

                // if we're checking years
                else if (len == 4 && (

                    // year is before the first selectable year
                    (typeof start_date != 'undefined' && now < first_selectable_year) ||

                    // or day is after the last selectable date
                    (typeof end_date != 'undefined'  && now > last_selectable_year)

                // year needs to be disabled
                )) return true;

            }

            // if there are rules for disabling dates
            if (disabled_dates) {

                // if month is given as argument, increment it (as JavaScript uses 0 for January, 1 for February...)
                if (typeof month != 'undefined') month = month + 1

                // by default, we assume the day/month/year is not to be disabled
                var disabled = false;

                // iterate through the rules for disabling dates
                $.each(disabled_dates, function() {

                    // if the date is to be disabled, don't look any further
                    if (disabled) return;

                    var rule = this;

                    // if the rules apply for the current year
                    if ($.inArray(year, rule[2]) > -1 || $.inArray('*', rule[2]) > -1)

                        // if the rules apply for the current month
                        if ((typeof month != 'undefined' && $.inArray(month, rule[1]) > -1) || $.inArray('*', rule[1]) > -1)

                            // if the rules apply for the current day
                            if ((typeof day != 'undefined' && $.inArray(day, rule[0]) > -1) || $.inArray('*', rule[0]) > -1) {

                                // if day is to be disabled whatever the day
                                // don't look any further
                                if (rule[3] == '*') return (disabled = true);

                                // get the weekday
                                var weekday = new Date(year, month - 1, day).getDay();

                                // if weekday is to be disabled
                                // don't look any further
                                if ($.inArray(weekday, rule[3]) > -1) return (disabled = true);

                            }

                });

                // if the day/month/year needs to be disabled
                if (disabled) return true;

            }

            // if script gets this far it means that the day/month/year doesn't need to be disabled
            return false;

        }

        /**
         *  Checks whether a value is an integer number.
         *
         *  @param  mixed   value   Value to check
         *
         *  @return                 Returns TRUE if the value represents an integer number, or FALSE otherwise
         *
         *  @access private
         */
        var is_integer = function(value) {

            // return TRUE if value represents an integer number, or FALSE otherwise
            return (value + '').match(/^\-?[0-9]+$/) ? true : false;

        }

        /**
         *  Sets the caption in the header of the date picker and enables or disables navigation buttons when necessary.
         *
         *  @param  string  caption     String that needs to be displayed in the header
         *
         *  @return void
         *
         *  @access private
         */
        var manage_header = function(caption) {

            // update the caption in the header
            $('.dp_caption', header).html(caption);

            // if calendar has direction restrictions
            if (!(!$.isArray(plugin.settings.direction) && to_int(plugin.settings.direction) === 0)) {

                // get the current year and month
                var year = selected_year,
                    month = selected_month,
                    next, previous;

                // if current view is showing days
                if (view == 'days') {

                    // clicking on "previous" should take us to the previous month
                    // (will check later if that particular month is available)
                    previous = (month - 1 < 0 ? str_concat(year - 1, '11') : str_concat(year, str_pad(month - 1, 2)));

                    // clicking on "next" should take us to the next month
                    // (will check later if that particular month is available)
                    next = (month + 1 > 11 ? str_concat(year + 1, '00') : str_concat(year, str_pad(month + 1, 2)));

                // if current view is showing months
                } else if (view == 'months') {

                    // clicking on "previous" should take us to the previous year
                    // (will check later if that particular year is available)
                    previous = year - 1;

                    // clicking on "next" should take us to the next year
                    // (will check later if that particular year is available)
                    next = year + 1;

                // if current view is showing years
                } else if (view == 'years') {

                    // clicking on "previous" should show a list with some previous years
                    // (will check later if that particular list of years contains selectable years)
                    previous = year - 7;

                    // clicking on "next" should show a list with some following years
                    // (will check later if that particular list of years contains selectable years)
                    next = year + 7;

                }

                // if the previous month/year is not selectable or, in case of years, if the list doesn't contain selectable years
                if (is_disabled(previous)) {

                    // disable the "previous" button
                    $('.dp_previous', header).addClass('dp_blocked');
                    $('.dp_previous', header).removeClass('dp_hover');

                // otherwise enable the "previous" button
                } else $('.dp_previous', header).removeClass('dp_blocked');

                // if the next month/year is not selectable or, in case of years, if the list doesn't contain selectable years
                if (is_disabled(next)) {

                    // disable the "next" button
                    $('.dp_next', header).addClass('dp_blocked');
                    $('.dp_next', header).removeClass('dp_hover');

                // otherwise enable the "next" button
                } else $('.dp_next', header).removeClass('dp_blocked');

            }

        }

        /**
         *  Shows the appropriate view (days, months or years) according to the current value of the "view" property.
         *
         *  @return void
         *
         *  @access private
         */
		var manage_views = function() {

            // if the day picker was not yet generated
            if (daypicker.text() == '' || view == 'days') {

                // if the day picker was not yet generated
                if (daypicker.text() == '') {

                    // if date picker is not always visible
                    if (!plugin.settings.always_visible)

                        // temporarily set the date picker's left outside of view
                        // so that we can later grab its width and height
                        datepicker.css('left', -1000);

                    // temporarily make the date picker visible
                    // so that we can later grab its width and height
                    datepicker.css({
                        'display':  'block'
                    });

    				// generate the day picker
    				generate_daypicker();

                    // get the day picker's width and height
                    var width = daypicker.outerWidth(),
                        height = daypicker.outerHeight();

                    // adjust the size of the header
                    header.css('width', width);

                    // make the month picker have the same size as the day picker
                    monthpicker.css({
                        'width':    width,
                        'height':   height
                    });

                    // make the year picker have the same size as the day picker
                    yearpicker.css({
                        'width':    width,
                        'height':   height
                    });

                    // adjust the size of the footer
                    footer.css('width', width);

                    // hide the date picker again
                    datepicker.css({
                        'display':  'none'
                    });

                // if the day picker was previously generated at least once
				// generate the day picker
                } else generate_daypicker();

                // hide the year and the month pickers
                monthpicker.css('display', 'none');
                yearpicker.css('display', 'none');

            // if the view is "months"
            } else if (view == 'months') {

                // generate the month picker
                generate_monthpicker();

                // hide the day and the year pickers
                daypicker.css('display', 'none');
                yearpicker.css('display', 'none');

            // if the view is "years"
            } else if (view == 'years') {

                // generate the year picker
                generate_yearpicker();

                // hide the day and the month pickers
                daypicker.css('display', 'none');
                monthpicker.css('display', 'none');

            }

            // if the button for clearing a previously selected date needs to be visible all the time,
            // or the date picker is always visible - case in which the "clear" button is always visible -
            // or there is content in the element the date picker is attached to
            // and the footer is not visible
            if ((plugin.settings.always_show_clear || plugin.settings.always_visible || $element.val() != '') && footer.css('display') != 'block')

                // show the footer
                footer.css('display', '');

            // hide the footer otherwise
            else footer.css('display', 'none');

		}

        /**
         *  Puts the specified date in the element the plugin is attached to, and hides the date picker.
         *
         *  @param  integer     year    The year
         *
         *  @param  integer     month   The month
         *
         *  @param  integer     day     The day
         *
         *  @param  string      view    The view from where the method was called
         *
         *  @param  object      cell    The element that was clicked
         *
         *  @return void
         *
         *  @access private
         */
        var select_date = function(year, month, day, view, cell) {

            var

                // construct a new date object from the arguments
                default_date = new Date(year, month, day),

                // pointer to the cells in the current view
                view_cells = (view == 'days' ? daypicker_cells : (view == 'months' ? monthpicker_cells : yearpicker_cells)),

                // the selected date, formatted correctly
                selected_value = format(default_date);

            // set the currently selected and formated date as the value of the element the plugin is attached to
            $element.val(selected_value);
            $element.trigger('change');

            // if date picker is always visible
            if (plugin.settings.always_visible) {

                // extract the date parts and re-assign values to these variables
                // so that everything will be correctly highlighted
                default_month = default_date.getMonth();
                selected_month = default_date.getMonth();
                default_year = default_date.getFullYear();
                selected_year = default_date.getFullYear();
                default_day = default_date.getDate();

                // remove the "selected" class from all cells in the current view
                view_cells.removeClass('dp_selected');

                // add the "selected" class to the currently selected cell
                cell.addClass('dp_selected');

            }

            // hide the date picker
            plugin.hide();

            // updates value for the date picker whose starting date depends on the selected date (if any)
            update_dependent(default_date);

            // if a callback function exists for when selecting a date
            if (plugin.settings.onSelect && typeof plugin.settings.onSelect == 'function')

                // execute the callback function
                plugin.settings.onSelect(selected_value, year + '-' + str_pad(month + 1, 2) + '-' + str_pad(day, 2), new Date(year, month, day));

        }

        /**
         *  Concatenates any number of arguments and returns them as string.
         *
         *  @return string  Returns the concatenated values.
         *
         *  @access private
         */
        var str_concat = function() {

            var str = '';

            // concatenate as string
            for (var i = 0; i < arguments.length; i++) str += (arguments[i] + '');

            // return the concatenated values
            return str;

        }

        /**
         *  Left-pad a string to a certain length with zeroes.
         *
         *  @param  string  str     The string to be padded.
         *
         *  @param  integer len     The length to which the string must be padded
         *
         *  @return string          Returns the string left-padded with leading zeroes
         *
         *  @access private
         */
        var str_pad = function(str, len) {

            // make sure argument is a string
            str += '';

            // pad with leading zeroes until we get to the desired length
            while (str.length < len) str = '0' + str;

            // return padded string
            return str;

        }

        /**
         *  Returns the integer representation of a string
         *
         *  @return int     Returns the integer representation of the string given as argument
         *
         *  @access private
         */
        var to_int = function(str) {

            // return the integer representation of the string given as argument
            return parseInt(str , 10);

        }

        /**
         *  Updates the paired date picker (whose starting date depends on the value of the current date picker)
         *
         *  @param  date    date    A JavaScript date object representing the currently selected date
         *
         *  @return void
         *
         *  @access private
         */
        var update_dependent = function(date) {

            // if the pair element exists
            if (plugin.settings.pair) {

                // chances are that at the beginning the pair element doesn't have the MyZebra_DatePicker attached to it yet
                // (as the "start" element is usually created before the "end" element)
                // so we'll have to rely on "data" to send the starting date to the pair element

                // therefore, if MyZebra_DatePicker is not yet attached
                if (!(plugin.settings.pair.data && plugin.settings.pair.data('MyZebra_DatePicker')))

                    // set the starting date like this
                    plugin.settings.pair.data('zdp_reference_date', date);

                // if MyZebra_DatePicker is attached to the pair element
                else {

                    // reference the date picker object attached to the other element
                    var dp = plugin.settings.pair.data('MyZebra_DatePicker');

                    // update the other date picker's starting date
                    // the value depends on the original value of the "direction" attribute
                    dp.update({
                        'reference_date': date
                    });

                    // if the other date picker is always visible, update the visuals now
                    if (dp.settings.always_visible) dp.show()

                }

            }

        }

        /**
         *  Function to be called when the "onKeyUp" event occurs
         *
         *  Why as a separate function and not inline when binding the event? Because only this way we can "unbind" it
         *  if the date picker is destroyed
         *
         *  @return boolean     Returns TRUE
         *
         *  @access private
         */
        plugin._keyup = function(e) {

            // if the date picker is visible
            // and the pressed key is ESC
            // hide the date picker
            if (datepicker.css('display') == 'block' || e.which == 27) plugin.hide();

            return true;

        }

        /**
         *  Function to be called when the "onMouseDown" event occurs
         *
         *  Why as a separate function and not inline when binding the event? Because only this way we can "unbind" it
         *  if the date picker is destroyed
         *
         *  @return boolean     Returns TRUE
         *
         *  @access private
         */
        plugin._mousedown = function(e) {

            // if the date picker is visible
            if (datepicker.css('display') == 'block') {

                // if we clicked the date picker's icon, let the onClick event of the icon to handle the event
                // (we want it to toggle the date picker)
                if ($(e.target).get(0) === icon.get(0)) return true;

                // if what's clicked is not inside the date picker
                // hide the date picker
                if ($(e.target).parents().filter('.MyZebra_DatePicker').length == 0) plugin.hide();

            }

            return true;

        }

        // initialize the plugin
        init();

    }

    $.fn.MyZebra_DatePicker = function(options) {

        return this.each(function() {

            // if element has a date picker already attached
            if (undefined != $(this).data('MyZebra_DatePicker')) {

                // get reference to the previously attached date picker
                var plugin = $(this).data('MyZebra_DatePicker');

                // remove the attached icon and calendar
                plugin.icon.remove();
                plugin.datepicker.remove();

                // remove associated event handlers from the document
                $(document).unbind('keyup', plugin._keyup);
                $(document).unbind('mousedown', plugin._mousedown);

            }

            // create a new instance of the plugin
            var plugin = new $.MyZebra_DatePicker(this, options);

            // save a reference to the newly created object
            $(this).data('MyZebra_DatePicker', plugin);

        });

    }

})(jQuery);

/**
 CLEditor WYSIWYG HTML Editor v1.3.0
 http://premiumsoftware.net/cleditor
 requires jQuery v1.4.2 or later

 Copyright 2010, Chris Landowski, Premium Software, LLC
 Dual licensed under the MIT or GPL Version 2 licenses.
**/

;;/**
 @preserve CLEditor WYSIWYG HTML Editor v1.3.0
 http://premiumsoftware.net/cleditor
 requires jQuery v1.4.2 or later

 Copyright 2010, Chris Landowski, Premium Software, LLC
 Dual licensed under the MIT or GPL Version 2 licenses.
*/

// ==ClosureCompiler==
// @compilation_level SIMPLE_OPTIMIZATIONS
// @output_file_name jquery.cleditor.min.js
// ==/ClosureCompiler==

(function($) {

  //==============
  // jQuery Plugin
  //==============

  $.cleditor = {

    // Define the defaults used for all new cleditor instances
    defaultOptions: {
      width:        500, // width not including margins, borders or padding
      height:       250, // height not including margins, borders or padding
      controls:     // controls to add to the toolbar
                    "bold italic underline strikethrough subscript superscript | font size " +
                    "style | color highlight removeformat | bullets numbering | outdent " +
                    "indent | alignleft center alignright justify | undo redo | " +
                    "rule image link unlink | cut copy paste pastetext | print source",
      colors:       // colors in the color popup
                    "FFF FCC FC9 FF9 FFC 9F9 9FF CFF CCF FCF " +
                    "CCC F66 F96 FF6 FF3 6F9 3FF 6FF 99F F9F " +
                    "BBB F00 F90 FC6 FF0 3F3 6CC 3CF 66C C6C " +
                    "999 C00 F60 FC3 FC0 3C0 0CC 36F 63F C3C " +
                    "666 900 C60 C93 990 090 399 33F 60C 939 " +
                    "333 600 930 963 660 060 366 009 339 636 " +
                    "000 300 630 633 330 030 033 006 309 303",    
      fonts:        // font names in the font popup
                    "Arial,Arial Black,Comic Sans MS,Courier New,Narrow,Garamond," +
                    "Georgia,Impact,Sans Serif,Serif,Tahoma,Trebuchet MS,Verdana",
      sizes:        // sizes in the font size popup
                    "1,2,3,4,5,6,7",
      styles:       // styles in the style popup
                    [["Paragraph", "<p>"], ["Header 1", "<h1>"], ["Header 2", "<h2>"],
                    ["Header 3", "<h3>"],  ["Header 4","<h4>"],  ["Header 5","<h5>"],
                    ["Header 6","<h6>"]],
      useCSS:       false, // use CSS to style HTML when possible (not supported in ie)
      docType:      // Document type contained within the editor
                    '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
      docCSSFile:   // CSS file used to style the document contained within the editor
                    "", 
      bodyStyle:    // style to assign to document body contained within the editor
                    "margin:4px; font:10pt Arial,Verdana; cursor:text"
    },

    // Define all usable toolbar buttons - the init string property is 
    //   expanded during initialization back into the buttons object and 
    //   seperate object properties are created for each button.
    //   e.g. buttons.size.title = "Font Size"
    buttons: {
      // name,title,command,popupName (""=use name)
      init:
      "bold,,|" +
      "italic,,|" +
      "underline,,|" +
      "strikethrough,,|" +
      "subscript,,|" +
      "superscript,,|" +
      "font,,fontname,|" +
      "size,Font Size,fontsize,|" +
      "style,,formatblock,|" +
      "color,Font Color,forecolor,|" +
      "highlight,Text Highlight Color,hilitecolor,color|" +
      "removeformat,Remove Formatting,|" +
      "bullets,,insertunorderedlist|" +
      "numbering,,insertorderedlist|" +
      "outdent,,|" +
      "indent,,|" +
      "alignleft,Align Text Left,justifyleft|" +
      "center,,justifycenter|" +
      "alignright,Align Text Right,justifyright|" +
      "justify,,justifyfull|" +
      "undo,,|" +
      "redo,,|" +
      "rule,Insert Horizontal Rule,inserthorizontalrule|" +
      "image,Insert Image,insertimage,url|" +
      "link,Insert Hyperlink,createlink,url|" +
      "unlink,Remove Hyperlink,|" +
      "cut,,|" +
      "copy,,|" +
      "paste,,|" +
      "pastetext,Paste as Text,inserthtml,|" +
      "print,,|" +
      "source,Show Source"
    },

    // imagesPath - returns the path to the images folder
    imagesPath: function() { return imagesPath(); }

  };

  // cleditor - creates a new editor for each of the matched textareas
  $.fn.cleditor = function(options) {

    // Create a new jQuery object to hold the results
    var $result = $([]);

    // Loop through all matching textareas and create the editors
    this.each(function(idx, elem) {
      if (elem.tagName == "TEXTAREA") {
        var data = $.data(elem, CLEDITOR);
        if (!data) data = new cleditor(elem, options);
        $result = $result.add(data);
      }
    });

    // return the new jQuery object
    return $result;

  };
    
  //==================
  // Private Variables
  //==================

  var

  // Misc constants
  BACKGROUND_COLOR = "backgroundColor",
  BUTTON           = "button",
  BUTTON_NAME      = "buttonName",
  CHANGE           = "change",
  CLEDITOR         = "cleditor",
  CLICK            = "click",
  DISABLED         = "disabled",
  DIV_TAG          = "<div>",
  TRANSPARENT      = "transparent",
  UNSELECTABLE     = "unselectable",

  // Class name constants
  MAIN_CLASS       = "cleditorMain",    // main containing div
  TOOLBAR_CLASS    = "cleditorToolbar", // toolbar div inside main div
  GROUP_CLASS      = "cleditorGroup",   // group divs inside the toolbar div
  BUTTON_CLASS     = "cleditorButton",  // button divs inside group div
  DISABLED_CLASS   = "cleditorDisabled",// disabled button divs
  DIVIDER_CLASS    = "cleditorDivider", // divider divs inside group div
  POPUP_CLASS      = "cleditorPopup",   // popup divs inside body
  LIST_CLASS       = "cleditorList",    // list popup divs inside body
  COLOR_CLASS      = "cleditorColor",   // color popup div inside body
  PROMPT_CLASS     = "cleditorPrompt",  // prompt popup divs inside body
  MSG_CLASS        = "cleditorMsg",     // message popup div inside body

  // Test for ie
  ie = $.browser.msie,
  ie6 = /msie\s6/i.test(navigator.userAgent),

  // Test for iPhone/iTouch/iPad
  iOS = /iphone|ipad|ipod/i.test(navigator.userAgent),

  // Popups are created once as needed and shared by all editor instances
  popups = {},

  // Used to prevent the document click event from being bound more than once
  documentClickAssigned,

  // Local copy of the buttons object
  buttons = $.cleditor.buttons;

  //===============
  // Initialization
  //===============

  // Expand the buttons.init string back into the buttons object
  //   and create seperate object properties for each button.
  //   e.g. buttons.size.title = "Font Size"
  $.each(buttons.init.split("|"), function(idx, button) {
    var items = button.split(","), name = items[0];
    buttons[name] = {
      stripIndex: idx,
      name: name,
      title: items[1] === "" ? name.charAt(0).toUpperCase() + name.substr(1) : items[1],
      command: items[2] === "" ? name : items[2],
      popupName: items[3] === "" ? name : items[3]
    };
  });
  delete buttons.init;

  //============
  // Constructor
  //============

  // cleditor - creates a new editor for the passed in textarea element
  cleditor = function(area, options) {

    var editor = this;

    // Get the defaults and override with options
    editor.options = options = $.extend({}, $.cleditor.defaultOptions, options);

    // Hide the textarea and associate it with this editor
    var $area = editor.$area = $(area)
      .hide()
      .data(CLEDITOR, editor)
      .blur(function() {
        // Update the iframe when the textarea loses focus
        updateFrame(editor, true);
      });

    // Create the main container and append the textarea
    var $main = editor.$main = $(DIV_TAG)
      .addClass(MAIN_CLASS)
      .width(options.width)
      .height(options.height);

    // Create the toolbar
    var $toolbar = editor.$toolbar = $(DIV_TAG)
      .addClass(TOOLBAR_CLASS)
      .appendTo($main);

    // Add the first group to the toolbar
    var $group = $(DIV_TAG)
      .addClass(GROUP_CLASS)
      .appendTo($toolbar);
    
    // Add the buttons to the toolbar
    $.each(options.controls.split(" "), function(idx, buttonName) {
      if (buttonName === "") return true;

      // Divider
      if (buttonName == "|") {

        // Add a new divider to the group
        var $div = $(DIV_TAG)
          .addClass(DIVIDER_CLASS)
          .appendTo($group);

        // Create a new group
        $group = $(DIV_TAG)
          .addClass(GROUP_CLASS)
          .appendTo($toolbar);

      }

      // Button
      else {
        
        // Get the button definition
        var button = buttons[buttonName];

        // Add a new button to the group
        var $buttonDiv = $(DIV_TAG)
          .data(BUTTON_NAME, button.name)
          .addClass(BUTTON_CLASS)
          .attr("title", button.title)
          .bind(CLICK, $.proxy(buttonClick, editor))
          .appendTo($group)
          .hover(hoverEnter, hoverLeave);

        // Prepare the button image
        var map = {};
        if (button.css) map = button.css;
        else if (button.image) map.backgroundImage = imageUrl(button.image);
        if (button.stripIndex) map.backgroundPosition = button.stripIndex * -24;
        $buttonDiv.css(map);

        // Add the unselectable attribute for ie
        if (ie)
          $buttonDiv.attr(UNSELECTABLE, "on");

        // Create the popup
        if (button.popupName)
          createPopup(button.popupName, options, button.popupClass,
            button.popupContent, button.popupHover);
        
      }

    });

    // Add the main div to the DOM and append the textarea
    $main.insertBefore($area)
      .append($area);

    // Bind the document click event handler
    if (!documentClickAssigned) {
      $(document).click(function(e) {
        // Dismiss all non-prompt popups
        var $target = $(e.target);
        if (!$target.add($target.parents()).is("." + PROMPT_CLASS))
          hidePopups();
      });
      documentClickAssigned = true;
    }

    // Bind the window resize event when the width or height is auto or %
    if (/auto|%/.test("" + options.width + options.height))
      $(window).resize(function() {refresh(editor);});

    // Create the iframe and resize the controls
    refresh(editor);

  };

  //===============
  // Public Methods
  //===============

  var fn = cleditor.prototype,

  // Expose the following private functions as methods on the cleditor object.
  // The closure compiler will rename the private functions. However, the
  // exposed method names on the cleditor object will remain fixed.
  methods = [
    ["clear", clear],
    ["disable", disable],
    ["execCommand", execCommand],
    ["focus", focus],
    ["hidePopups", hidePopups],
    ["sourceMode", sourceMode, true],
    ["refresh", refresh],
    ["select", select],
    ["selectedHTML", selectedHTML, true],
    ["selectedText", selectedText, true],
    ["showMessage", showMessage],
    ["updateFrame", updateFrame],
    ["updateTextArea", updateTextArea]
  ];

  $.each(methods, function(idx, method) {
    fn[method[0]] = function() {
      var editor = this, args = [editor];
      // using each here would cast booleans into objects!
      for(var x = 0; x < arguments.length; x++) {args.push(arguments[x]);}
      var result = method[1].apply(editor, args);
      if (method[2]) return result;
      return editor;
    };
  });

  // change - shortcut for .bind("change", handler) or .trigger("change")
  fn.change = function(handler) {
    var $this = $(this);
    return handler ? $this.bind(CHANGE, handler) : $this.trigger(CHANGE);
  };

  //===============
  // Event Handlers
  //===============

  // buttonClick - click event handler for toolbar buttons
  function buttonClick(e) {

    var editor = this,
        buttonDiv = e.target,
        buttonName = $.data(buttonDiv, BUTTON_NAME),
        button = buttons[buttonName],
        popupName = button.popupName,
        popup = popups[popupName];

    // Check if disabled
    if (editor.disabled || $(buttonDiv).attr(DISABLED) == DISABLED)
      return;

    // Fire the buttonClick event
    var data = {
      editor: editor,
      button: buttonDiv,
      buttonName: buttonName,
      popup: popup,
      popupName: popupName,
      command: button.command,
      useCSS: editor.options.useCSS
    };

    if (button.buttonClick && button.buttonClick(e, data) === false)
      return false;

    // Toggle source
    if (buttonName == "source") {

      // Show the iframe
      if (sourceMode(editor)) {
        delete editor.range;
        editor.$area.hide();
        editor.$frame.show();
        buttonDiv.title = button.title;
      }

      // Show the textarea
      else {
        editor.$frame.hide();
        editor.$area.show();
        buttonDiv.title = "Show Rich Text";
      }

      // Enable or disable the toolbar buttons
      // IE requires the timeout
      setTimeout(function() {refreshButtons(editor);}, 100);

    }

    // Check for rich text mode
    else if (!sourceMode(editor)) {

      // Handle popups
      if (popupName) {
        var $popup = $(popup);

        // URL
        if (popupName == "url") {

          // Check for selection before showing the link url popup
          if (buttonName == "link" && selectedText(editor) === "") {
            showMessage(editor, "A selection is required when inserting a link.", buttonDiv);
            return false;
          }

          // Wire up the submit button click event handler
          $popup.children(":button")
            .unbind(CLICK)
            .bind(CLICK, function() {

              // Insert the image or link if a url was entered
              var $text = $popup.find(":text"),
                url = $.trim($text.val());
              if (url !== "")
                execCommand(editor, data.command, url, null, data.button);

              // Reset the text, hide the popup and set focus
              $text.val("http://");
              hidePopups();
              focus(editor);

            });

        }

        // Paste as Text
        else if (popupName == "pastetext") {

          // Wire up the submit button click event handler
          $popup.children(":button")
            .unbind(CLICK)
            .bind(CLICK, function() {

              // Insert the unformatted text replacing new lines with break tags
              var $textarea = $popup.find("textarea"),
                text = $textarea.val().replace(/\n/g, "<br />");
              if (text !== "")
                execCommand(editor, data.command, text, null, data.button);

              // Reset the text, hide the popup and set focus
              $textarea.val("");
              hidePopups();
              focus(editor);

            });

        }

        // Show the popup if not already showing for this button
        if (buttonDiv !== $.data(popup, BUTTON)) {
          showPopup(editor, popup, buttonDiv);
          return false; // stop propagination to document click
        }

        // propaginate to documnt click
        return;

      }

      // Print
      else if (buttonName == "print")
        editor.$frame[0].contentWindow.print();

      // All other buttons
      else if (!execCommand(editor, data.command, data.value, data.useCSS, buttonDiv))
        return false;

    }

    // Focus the editor
    focus(editor);

  }

  // hoverEnter - mouseenter event handler for buttons and popup items
  function hoverEnter(e) {
    var $div = $(e.target).closest("div");
    $div.css(BACKGROUND_COLOR, $div.data(BUTTON_NAME) ? "#FFF" : "#FFC");
  }

  // hoverLeave - mouseleave event handler for buttons and popup items
  function hoverLeave(e) {
    $(e.target).closest("div").css(BACKGROUND_COLOR, "transparent");
  }

  // popupClick - click event handler for popup items
  function popupClick(e) {

    var editor = this,
        popup = e.data.popup,
        target = e.target;

    // Check for message and prompt popups
    if (popup === popups.msg || $(popup).hasClass(PROMPT_CLASS))
      return;

    // Get the button info
    var buttonDiv = $.data(popup, BUTTON),
        buttonName = $.data(buttonDiv, BUTTON_NAME),
        button = buttons[buttonName],
        command = button.command,
        value,
        useCSS = editor.options.useCSS;

    // Get the command value
    if (buttonName == "font")
      // Opera returns the fontfamily wrapped in quotes
      value = target.style.fontFamily.replace(/"/g, "");
    else if (buttonName == "size") {
      if (target.tagName == "DIV")
        target = target.children[0];
      value = target.innerHTML;
    }
    else if (buttonName == "style")
      value = "<" + target.tagName + ">";
    else if (buttonName == "color")
      value = hex(target.style.backgroundColor);
    else if (buttonName == "highlight") {
      value = hex(target.style.backgroundColor);
      if (ie) command = 'backcolor';
      else useCSS = true;
    }

    // Fire the popupClick event
    var data = {
      editor: editor,
      button: buttonDiv,
      buttonName: buttonName,
      popup: popup,
      popupName: button.popupName,
      command: command,
      value: value,
      useCSS: useCSS
    };

    if (button.popupClick && button.popupClick(e, data) === false)
      return;

    // Execute the command
    if (data.command && !execCommand(editor, data.command, data.value, data.useCSS, buttonDiv))
      return false;

    // Hide the popup and focus the editor
    hidePopups();
    focus(editor);

  }

  //==================
  // Private Functions
  //==================

  // checksum - returns a checksum using the Adler-32 method
  function checksum(text)
  {
    var a = 1, b = 0;
    for (var index = 0; index < text.length; ++index) {
      a = (a + text.charCodeAt(index)) % 65521;
      b = (b + a) % 65521;
    }
    return (b << 16) | a;
  }

  // clear - clears the contents of the editor
  function clear(editor) {
    editor.$area.val("");
    updateFrame(editor);
  }

  // createPopup - creates a popup and adds it to the body
  function createPopup(popupName, options, popupTypeClass, popupContent, popupHover) {

    // Check if popup already exists
    if (popups[popupName])
      return popups[popupName];

    // Create the popup
    var $popup = $(DIV_TAG)
      .hide()
      .addClass(POPUP_CLASS)
      .appendTo("body");

    // Add the content

    // Custom popup
    if (popupContent)
      $popup.html(popupContent);

    // Color
    else if (popupName == "color") {
      var colors = options.colors.split(" ");
      if (colors.length < 10)
        $popup.width("auto");
      $.each(colors, function(idx, color) {
        $(DIV_TAG).appendTo($popup)
          .css(BACKGROUND_COLOR, "#" + color);
      });
      popupTypeClass = COLOR_CLASS;
    }

    // Font
    else if (popupName == "font")
      $.each(options.fonts.split(","), function(idx, font) {
        $(DIV_TAG).appendTo($popup)
          .css("fontFamily", font)
          .html(font);
      });

    // Size
    else if (popupName == "size")
      $.each(options.sizes.split(","), function(idx, size) {
        $(DIV_TAG).appendTo($popup)
          .html("<font size=" + size + ">" + size + "</font>");
      });

    // Style
    else if (popupName == "style")
      $.each(options.styles, function(idx, style) {
        $(DIV_TAG).appendTo($popup)
          .html(style[1] + style[0] + style[1].replace("<", "</"));
      });

    // URL
    else if (popupName == "url") {
      $popup.html('Enter URL:<br><input type=text value="http://" size=35><br><input type=button value="Submit">');
      popupTypeClass = PROMPT_CLASS;
    }

    // Paste as Text
    else if (popupName == "pastetext") {
      $popup.html('Paste your content here and click submit.<br /><textarea cols=40 rows=3></textarea><br /><input type=button value=Submit>');
      popupTypeClass = PROMPT_CLASS;
    }

    // Add the popup type class name
    if (!popupTypeClass && !popupContent)
      popupTypeClass = LIST_CLASS;
    $popup.addClass(popupTypeClass);

    // Add the unselectable attribute to all items
    if (ie) {
      $popup.attr(UNSELECTABLE, "on")
        .find("div,font,p,h1,h2,h3,h4,h5,h6")
        .attr(UNSELECTABLE, "on");
    }

    // Add the hover effect to all items
    if ($popup.hasClass(LIST_CLASS) || popupHover === true)
      $popup.children().hover(hoverEnter, hoverLeave);

    // Add the popup to the array and return it
    popups[popupName] = $popup[0];
    return $popup[0];

  }

  // disable - enables or disables the editor
  function disable(editor, disabled) {

    // Update the textarea and save the state
    if (disabled) {
      editor.$area.attr(DISABLED, DISABLED);
      editor.disabled = true;
    }
    else {
      editor.$area.removeAttr(DISABLED);
      delete editor.disabled;
    }

    // Switch the iframe into design mode.
    // ie6 does not support designMode.
    // ie7 & ie8 do not properly support designMode="off".
    try {
      if (ie) editor.doc.body.contentEditable = !disabled;
      else editor.doc.designMode = !disabled ? "on" : "off";
    }
    // Firefox 1.5 throws an exception that can be ignored
    // when toggling designMode from off to on.
    catch (err) {}

    // Enable or disable the toolbar buttons
    refreshButtons(editor);

  }

  // execCommand - executes a designMode command
  function execCommand(editor, command, value, useCSS, button) {

    // Restore the current ie selection
    restoreRange(editor);

    // Set the styling method
    if (!ie) {
      if (useCSS === undefined || useCSS === null)
        useCSS = editor.options.useCSS;
      editor.doc.execCommand("styleWithCSS", 0, useCSS.toString());
    }

    // Execute the command and check for error
    var success = true, description;
    if (ie && command.toLowerCase() == "inserthtml")
      getRange(editor).pasteHTML(value);
    else {
      try { success = editor.doc.execCommand(command, 0, value || null); }
      catch (err) { description = err.description; success = false; }
      if (!success) {
        if ("cutcopypaste".indexOf(command) > -1)
          showMessage(editor, "For security reasons, your browser does not support the " +
            command + " command. Try using the keyboard shortcut or context menu instead.",
            button);
        else
          showMessage(editor,
            (description ? description : "Error executing the " + command + " command."),
            button);
      }
    }

    // Enable the buttons
    refreshButtons(editor);
    return success;

  }

  // focus - sets focus to either the textarea or iframe
  function focus(editor) {
    setTimeout(function() {
      if (sourceMode(editor)) editor.$area.focus();
      else editor.$frame[0].contentWindow.focus();
      refreshButtons(editor);
    }, 0);
  }

  // getRange - gets the current text range object
  function getRange(editor) {
    if (ie) return getSelection(editor).createRange();
    return getSelection(editor).getRangeAt(0);
  }

  // getSelection - gets the current text range object
  function getSelection(editor) {
    if (ie) return editor.doc.selection;
    return editor.$frame[0].contentWindow.getSelection();
  }

  // Returns the hex value for the passed in string.
  //   hex("rgb(255, 0, 0)"); // #FF0000
  //   hex("#FF0000"); // #FF0000
  //   hex("#F00"); // #FF0000
  function hex(s) {
    var m = /rgba?\((\d+), (\d+), (\d+)/.exec(s),
      c = s.split("");
    if (m) {
      s = ( m[1] << 16 | m[2] << 8 | m[3] ).toString(16);
      while (s.length < 6)
        s = "0" + s;
    }
    return "#" + (s.length == 6 ? s : c[1] + c[1] + c[2] + c[2] + c[3] + c[3]);
  }

  // hidePopups - hides all popups
  function hidePopups() {
    $.each(popups, function(idx, popup) {
      $(popup)
        .hide()
        .unbind(CLICK)
        .removeData(BUTTON);
    });
  }

  // imagesPath - returns the path to the images folder
  function imagesPath() {
    var cssFile = "jquery.cleditor.css",
        href = $("link[href$='" + cssFile +"']").attr("href");
    return (href)?href.substr(0, href.length - cssFile.length) + "images/":'';
  }

  // imageUrl - Returns the css url string for a filemane
  function imageUrl(filename) {
    return "url(" + imagesPath() + filename + ")";
  }

  // refresh - creates the iframe and resizes the controls
  function refresh(editor) {

    var $main = editor.$main,
      options = editor.options;

    // Remove the old iframe
    if (editor.$frame) 
      editor.$frame.remove();

    // Create a new iframe
    var $frame = editor.$frame = $('<iframe frameborder="0" src="javascript:true;">')
      .hide()
      .appendTo($main);

    // Load the iframe document content
    var contentWindow = $frame[0].contentWindow,
      doc = editor.doc = contentWindow.document,
      $doc = $(doc);

    doc.open();
    doc.write(
      options.docType +
      '<html>' +
      ((options.docCSSFile === '') ? '' : '<head><link rel="stylesheet" type="text/css" href="' + options.docCSSFile + '" /></head>') +
      '<body style="' + options.bodyStyle + '"></body></html>'
    );
    doc.close();

    // Work around for bug in IE which causes the editor to lose
    // focus when clicking below the end of the document.
    if (ie)
      $doc.click(function() {focus(editor);});

    // Load the content
    updateFrame(editor);

    // Bind the ie specific iframe event handlers
    if (ie) {

      // Save the current user selection. This code is needed since IE will
      // reset the selection just after the beforedeactivate event and just
      // before the beforeactivate event.
      $doc.bind("beforedeactivate beforeactivate selectionchange keypress", function(e) {
        
        // Flag the editor as inactive
        if (e.type == "beforedeactivate")
          editor.inactive = true;
        
        // Get rid of the bogus selection and flag the editor as active
        else if (e.type == "beforeactivate") {
          if (!editor.inactive && editor.range && editor.range.length > 1)
            editor.range.shift();
          delete editor.inactive;
        }

        // Save the selection when the editor is active
        else if (!editor.inactive) {
          if (!editor.range) 
            editor.range = [];
          editor.range.unshift(getRange(editor));

          // We only need the last 2 selections
          while (editor.range.length > 2)
            editor.range.pop();
        }

      });

      // Restore the text range when the iframe gains focus
      $frame.focus(function() {
        restoreRange(editor);
      });

    }

    // Update the textarea when the iframe loses focus
    ($.browser.mozilla ? $doc : $(contentWindow)).blur(function() {
      updateTextArea(editor, true);
    });

    // Enable the toolbar buttons as the user types or clicks
    $doc.click(hidePopups)
      .bind("keyup mouseup", function() {
        refreshButtons(editor);
      });

    // Show the textarea for iPhone/iTouch/iPad or
    // the iframe when design mode is supported.
    if (iOS) editor.$area.show();
    else $frame.show();

    // Wait for the layout to finish - shortcut for $(document).ready()
    $(function() {

      var $toolbar = editor.$toolbar,
          $group = $toolbar.children("div:last"),
          wid = $main.width();

      // Resize the toolbar
      var hgt = $group.offset().top + $group.outerHeight() - $toolbar.offset().top + 1;
      $toolbar.height(hgt);

      // Resize the iframe
      hgt = (/%/.test("" + options.height) ? $main.height() : parseInt(options.height)) - hgt;
      $frame.width(wid).height(hgt);

      // Resize the textarea. IE6 textareas have a 1px top
      // & bottom margin that cannot be removed using css.
      editor.$area.width(wid).height(ie6 ? hgt - 2 : hgt);

      // Switch the iframe into design mode if enabled
      disable(editor, editor.disabled);

      // Enable or disable the toolbar buttons
      refreshButtons(editor);

    });

  }

  // refreshButtons - enables or disables buttons based on availability
  function refreshButtons(editor) {

    // Webkit requires focus before queryCommandEnabled will return anything but false
    if (!iOS && $.browser.webkit && !editor.focused) {
      editor.$frame[0].contentWindow.focus();
      window.focus();
      editor.focused = true;
    }

    // Get the object used for checking queryCommandEnabled
    var queryObj = editor.doc;
    if (ie) queryObj = getRange(editor);

    // Loop through each button
    var inSourceMode = sourceMode(editor);
    $.each(editor.$toolbar.find("." + BUTTON_CLASS), function(idx, elem) {

      var $elem = $(elem),
        button = $.cleditor.buttons[$.data(elem, BUTTON_NAME)],
        command = button.command,
        enabled = true;

      // Determine the state
      if (editor.disabled)
        enabled = false;
      else if (button.getEnabled) {
        var data = {
          editor: editor,
          button: elem,
          buttonName: button.name,
          popup: popups[button.popupName],
          popupName: button.popupName,
          command: button.command,
          useCSS: editor.options.useCSS
        };
        enabled = button.getEnabled(data);
        if (enabled === undefined)
          enabled = true;
      }
      else if (((inSourceMode || iOS) && button.name != "source") ||
      (ie && (command == "undo" || command == "redo")))
        enabled = false;
      else if (command && command != "print") {
        if (ie && command == "hilitecolor")
          command = "backcolor";
        // IE does not support inserthtml, so it's always enabled
        if (!ie || command != "inserthtml") {
          try {enabled = queryObj.queryCommandEnabled(command);}
          catch (err) {enabled = false;}
        }
      }

      // Enable or disable the button
      if (enabled) {
        $elem.removeClass(DISABLED_CLASS);
        $elem.removeAttr(DISABLED);
      }
      else {
        $elem.addClass(DISABLED_CLASS);
        $elem.attr(DISABLED, DISABLED);
      }

    });
  }

  // restoreRange - restores the current ie selection
  function restoreRange(editor) {
    if (ie && editor.range)
      editor.range[0].select();
  }

  // select - selects all the text in either the textarea or iframe
  function select(editor) {
    setTimeout(function() {
      if (sourceMode(editor)) editor.$area.select();
      else execCommand(editor, "selectall");
    }, 0);
  }

  // selectedHTML - returns the current HTML selection or and empty string
  function selectedHTML(editor) {
    restoreRange(editor);
    var range = getRange(editor);
    if (ie)
      return range.htmlText;
    var layer = $("<layer>")[0];
    layer.appendChild(range.cloneContents());
    var html = layer.innerHTML;
    layer = null;
    return html;
  }

  // selectedText - returns the current text selection or and empty string
  function selectedText(editor) {
    restoreRange(editor);
    if (ie) return getRange(editor).text;
    return getSelection(editor).toString();
  }

  // showMessage - alert replacement
  function showMessage(editor, message, button) {
    var popup = createPopup("msg", editor.options, MSG_CLASS);
    popup.innerHTML = message;
    showPopup(editor, popup, button);
  }

  // showPopup - shows a popup
  function showPopup(editor, popup, button) {

    var offset, left, top, $popup = $(popup);

    // Determine the popup location
    if (button) {
      var $button = $(button);
      offset = $button.offset();
      left = --offset.left;
      top = offset.top + $button.height();
    }
    else {
      var $toolbar = editor.$toolbar;
      offset = $toolbar.offset();
      left = Math.floor(($toolbar.width() - $popup.width()) / 2) + offset.left;
      top = offset.top + $toolbar.height() - 2;
    }

    // Position and show the popup
    hidePopups();
    $popup.css({left: left, top: top})
      .show();

    // Assign the popup button and click event handler
    if (button) {
      $.data(popup, BUTTON, button);
      $popup.bind(CLICK, {popup: popup}, $.proxy(popupClick, editor));
    }

    // Focus the first input element if any
    setTimeout(function() {
      $popup.find(":text,textarea").eq(0).focus().select();
    }, 100);

  }

  // sourceMode - returns true if the textarea is showing
  function sourceMode(editor) {
    return editor.$area.is(":visible");
  }

  // updateFrame - updates the iframe with the textarea contents
  function updateFrame(editor, checkForChange) {
    
    var code = editor.$area.val(),
      options = editor.options,
      updateFrameCallback = options.updateFrame,
      $body = $(editor.doc.body);

    // Check for textarea change to avoid unnecessary firing
    // of potentially heavy updateFrame callbacks.
    if (updateFrameCallback) {
      var sum = checksum(code);
      if (checkForChange && editor.areaChecksum == sum)
        return;
      editor.areaChecksum = sum;
    }

    // Convert the textarea source code into iframe html
    var html = updateFrameCallback ? updateFrameCallback(code) : code;

    // Prevent script injection attacks by html encoding script tags
    html = html.replace(/<(?=\/?script)/ig, "&lt;");

    // Update the iframe checksum
    if (options.updateTextArea)
      editor.frameChecksum = checksum(html);

    // Update the iframe and trigger the change event
    if (html != $body.html()) {
      //console.log(html);
      $body.html(html);
      //console.log($body.html());
      $(editor).triggerHandler(CHANGE);
    }

  }

  // updateTextArea - updates the textarea with the iframe contents
  function updateTextArea(editor, checkForChange) {

    var html = $(editor.doc.body).html(),
      options = editor.options,
      updateTextAreaCallback = options.updateTextArea,
      $area = editor.$area;

    // Check for iframe change to avoid unnecessary firing
    // of potentially heavy updateTextArea callbacks.
    if (updateTextAreaCallback) {
      var sum = checksum(html);
      if (checkForChange && editor.frameChecksum == sum)
        return;
      editor.frameChecksum = sum;
    }

    // Convert the iframe html into textarea source code
    var code = updateTextAreaCallback ? updateTextAreaCallback(html) : html;

    // Update the textarea checksum
    if (options.updateFrame)
      editor.areaChecksum = checksum(code);

    // Update the textarea and trigger the change event
    if (code != $area.val()) {
      $area.val(code);
      $(editor).triggerHandler(CHANGE);
    }

  }

})(jQuery);



/**
 * DropKick
 *
 * Highly customizable <select> lists
 * https://github.com/JamieLottering/DropKick
 *
 * &copy; 2011 Jamie Lottering <http://github.com/JamieLottering>
 *                        <http://twitter.com/JamieLottering>
 * 
 */
//;(function(a,b,c){function l(a,b){var c=a.keyCode,d=b.data("dropkick"),e=b.find(".dk_options"),f=b.hasClass("dk_open"),h=b.find(".dk_option_current"),i=e.find("li").first(),j=e.find("li").last(),k,l;switch(c){case g.enter:f?(m(h.find("a"),b),p(b)):q(b),a.preventDefault();break;case g.up:l=h.prev("li"),f?l.length?n(l,b):n(j,b):q(b),a.preventDefault();break;case g.down:f?(k=h.next("li").first(),k.length?n(k,b):n(i,b)):q(b),a.preventDefault();break;default:}}function m(a,b,c){var d,e,f;d=a.attr("data-dk-dropdown-value"),e=a.text(),f=b.data("dropkick"),$select=f.$select,$select.val(d),b.find(".dk_label").text(e),c=c||!1,f.settings.change&&!c?f.settings.change.call($select,d,e):c||$select.trigger("change")}function n(a,b){b.find(".dk_option_current").removeClass("dk_option_current"),a.addClass("dk_option_current"),o(b,a)}function o(a,b){var c=b.prevAll("li").outerHeight()*b.prevAll("li").length;a.find(".dk_options_inner").animate({scrollTop:c+"px"},0)}function p(a){a.removeClass("dk_open")}function q(a){var b=a.data("dropkick");a.find(".dk_options").css({top:a.find(".dk_toggle").outerHeight()-1}),a.toggleClass("dk_open")}function r(b,c){var d=b,e=[],f;d=d.replace("{{ id }}",c.id),d=d.replace("{{ label }}",c.label),d=d.replace("{{ tabindex }}",c.tabindex);if(c.options&&c.options.length)for(var g=0,h=c.options.length;g<h;g++){var j=a(c.options[g]),k="dk_option_current",l=i;l=l.replace("{{ value }}",j.val()),l=l.replace("{{ current }}",s(j.val())===c.value?k:""),l=l.replace("{{ text }}",j.text()),e[e.length]=l}return f=a(d),f.find(".dk_options_inner").html(e.join("")),f}function s(b){return a.trim(b).length>0?b:!1}var d=!1;a.browser.msie&&a.browser.version.substr(0,1)<7?d=!0:c.documentElement.className=c.documentElement.className+" dk_fouc";var e={},f=[],g={left:37,up:38,right:39,down:40,enter:13},h=['<div class="dk_container_wrapper">','<div class="dk_container" id="dk_container_{{ id }}" tabindex="{{ tabindex }}">','<a class="dk_toggle">','<span class="dk_label">{{ label }}</span>',"</a>",'<div class="dk_options">','<ul class="dk_options_inner">',"</ul>","</div>","</div>","</div>"].join(""),i='<li class="{{ current }}"><a data-dk-dropdown-value="{{ value }}">{{ text }}</a></li>',j={startSpeed:1e3,theme:!1,change:!1},k=!1;e.init=function(b){return b=a.extend({},j,b),this.each(function(){var c=a(this),d=c.find(":selected").first(),e=c.find("option"),g=c.data("dropkick")||{},i=c.attr("id")||c.attr("name"),j=b.width||c.outerWidth(),k=c.attr("tabindex")?c.attr("tabindex"):"",l=!1,m;if(g.id)return c;g.settings=b,g.tabindex=k,g.id=i,g.$original=d,g.$select=c,g.value=s(c.val())||s(d.attr("value")),g.label=d.text(),g.options=e,l=r(h,g),l.find(".dk_toggle").css({width:j+"px"}),c.before(l),l=a("#dk_container_"+i).fadeIn(b.startSpeed),m=b.theme?b.theme:"default",l.addClass("dk_theme_"+m),g.theme=m,g.$dk=l,c.data("dropkick",g),l.data("dropkick",g),f[f.length]=c,l.bind("focus.dropkick",function(a){l.addClass("dk_focus")}).bind("blur.dropkick",function(a){l.removeClass("dk_open dk_focus")}),setTimeout(function(){c.hide()},0)})},e.theme=function(b){var c=a(this),d=c.data("dropkick"),e=d.$dk,f="dk_theme_"+d.theme;e.removeClass(f).addClass("dk_theme_"+b),d.theme=b},e.reset=function(){for(var a=0,b=f.length;a<b;a++){var c=f[a].data("dropkick"),d=c.$dk,e=d.find("li").first();d.find(".dk_label").text(c.label),d.find(".dk_options_inner").animate({scrollTop:0},0),n(e,d),m(e,d,!0)}},a.fn.dropkick=function(a){if(!d){if(e[a])return e[a].apply(this,Array.prototype.slice.call(arguments,1));if(typeof a=="object"||!a)return e.init.apply(this,arguments)}},a(function(){a(".dk_toggle").live("click",function(c){var d=a(this).parents(".dk_container").first();return q(d),"ontouchstart"in b&&(d.addClass("dk_touch"),d.find(".dk_options_inner").addClass("scrollable vertical")),c.preventDefault(),!1}),a(".dk_options a").live(a.browser.msie?"mousedown":"click",function(b){var c=a(this),d=c.parents(".dk_container").first(),e=d.data("dropkick");return p(d),m(c,d),n(c.parent(),d),b.preventDefault(),!1}),a(c).bind("keydown.dk_nav",function(b){var c=a(".dk_container.dk_open"),d=a(".dk_container.dk_focus"),e=null;c.length?e=c:d.length&&!c.length&&(e=d),e&&l(b,e)})})})(jQuery,window,document)
;
(function (a, b, c) {
    function l(a, b) {
        var c = a.keyCode,
            d = b.data("dropkick"),
            e = b.find(".dk_options"),
            f = b.hasClass("dk_open"),
            h = b.find(".dk_option_current"),
            i = e.find("li").first(),
            j = e.find("li").last(),
            k, l;
        switch (c) {
        case g.enter:
            f ? (m(h.find("a"), b), p(b)) : q(b), a.preventDefault();
            break;
        case g.up:
            l = h.prev("li"), f ? l.length ? n(l, b) : n(j, b) : q(b), a.preventDefault();
            break;
        case g.down:
            f ? (k = h.next("li").first(), k.length ? n(k, b) : n(i, b)) : q(b), a.preventDefault();
            break;
        default:
        }
    }
    function m(a, b, c) {
        var d, e, f;
        d = a.attr("data-dk-dropdown-value"), e = a.text(), f = b.data("dropkick"), $select = f.$select, $select.val(d), b.find(".dk_label").text(e), c = c || !1, f.settings.change && !c ? f.settings.change.call($select, d, e) : c || $select.trigger("change")
    }
    function n(a, b) {
        b.find(".dk_option_current").removeClass("dk_option_current"), a.addClass("dk_option_current"), o(b, a)
    }
    function o(a, b) {
        var c = b.prevAll("li").outerHeight() * b.prevAll("li").length;
        a.find(".dk_options_inner").animate({
            scrollTop: c + "px"
        }, 0)
    }
    function p(a) {
        a.removeClass("dk_open")
    }
    function q(a) {
        var b = a.data("dropkick");
        a.find(".dk_options").css({
            top: a.find(".dk_toggle").outerHeight() - 1
        }), a.toggleClass("dk_open")
    }
    function r(b, c) {
        var d = b,
            e = [],
            f;
        d = d.replace("{{ id }}", c.id), d = d.replace("{{ label }}", c.label), d = d.replace("{{ tabindex }}", c.tabindex);
        if (c.options && c.options.length) for (var g = 0, h = c.options.length; g < h; g++) {
            var j = a(c.options[g]),
                k = "dk_option_current",
                l = i;
            l = l.replace("{{ value }}", j.val()), l = l.replace("{{ current }}", s(j.val()) === c.value ? k : ""), l = l.replace("{{ text }}", j.text()), e[e.length] = l
        }
        return f = a(d), f.find(".dk_options_inner").html(e.join("")), f
    }
    function s(b) {
        return a.trim(b).length > 0 ? b : !1
    }
    var d = !1;
    a.browser.msie && a.browser.version.substr(0, 1) < 7 ? d = !0 : c.documentElement.className = c.documentElement.className + " dk_fouc";
    var e = {},
        f = [],
        g = {
            left: 37,
            up: 38,
            right: 39,
            down: 40,
            enter: 13
        },
        h = ['<div class="dk_container_wrapper">', '<div class="dk_container" id="dk_container_{{ id }}" tabindex="{{ tabindex }}">', '<a class="dk_toggle">', '<span class="dk_label">{{ label }}</span>', "</a>", '<div class="dk_options">', '<ul class="dk_options_inner">', "</ul>", "</div>", "</div>", "</div>"].join(""),
        i = '<li class="{{ current }}"><a data-dk-dropdown-value="{{ value }}">{{ text }}</a></li>',
        j = {
            startSpeed: 1e3,
            theme: !1,
            change: !1
        },
        k = !1;
    e.init = function (b) {
        return b = a.extend({}, j, b), this.each(function () {
            var c = a(this),
                d = c.find(":selected").first(),
                e = c.find("option"),
                g = c.data("dropkick") || {},
                i = c.attr("id") || c.attr("name"),
                j = b.width || c.outerWidth(),
                k = c.attr("tabindex") ? c.attr("tabindex") : "",
                l = !1,
                m;
            if (g.id) return c;
            g.settings = b, g.tabindex = k, g.id = i, g.$original = d, g.$select = c, g.value = s(c.val()) || s(d.attr("value")), g.label = d.text(), g.options = e, l = r(h, g), l.find(".dk_toggle").css({
                width: j + "px"
            }), c.before(l), l = a("#dk_container_" + i).fadeIn(b.startSpeed), m = b.theme ? b.theme : "default", l.addClass("dk_theme_" + m), g.theme = m, g.$dk = l, c.data("dropkick", g), l.data("dropkick", g), f[f.length] = c, l.bind("focus.dropkick", function (a) {
                l.addClass("dk_focus")
            }).bind("blur.dropkick", function (a) {
                l.removeClass("dk_open dk_focus")
            }), setTimeout(function () {
                c.hide()
            }, 0)
        })
    }, e.theme = function (b) {
        var c = a(this),
            d = c.data("dropkick"),
            e = d.$dk,
            f = "dk_theme_" + d.theme;
        e.removeClass(f).addClass("dk_theme_" + b), d.theme = b
    }, e.reset = function () {
        for (var a = 0, b = f.length; a < b; a++) {
            var c = f[a].data("dropkick"),
                d = c.$dk,
                e = d.find("li").first();
            d.find(".dk_label").text(c.label), d.find(".dk_options_inner").animate({
                scrollTop: 0
            }, 0), n(e, d), m(e, d, !0)
        }
    }, a.fn.dropkick = function (a) {
        if (!d) {
            if (e[a]) return e[a].apply(this, Array.prototype.slice.call(arguments, 1));
            if (typeof a == "object" || !a) return e.init.apply(this, arguments)
        }
    }, a(function () {
        a(".dk_toggle").live("click", function (c) {
            var d = a(this).parents(".dk_container").first();
            return q(d), "ontouchstart" in b && (d.addClass("dk_touch"), d.find(".dk_options_inner").addClass("scrollable vertical")), c.preventDefault(), !1
        }), a(".dk_options a").live(a.browser.msie ? "mousedown" : "click", function (b) {
            var c = a(this),
                d = c.parents(".dk_container").first(),
                e = d.data("dropkick");
            return p(d), m(c, d), n(c.parent(), d), b.preventDefault(), !1
        }), a(c).bind("keydown.dk_nav", function (b) {
            var c = a(".dk_container.dk_open"),
                d = a(".dk_container.dk_focus"),
                e = null;
            c.length ? e = c : d.length && !c.length && (e = d), e && l(b, e)
        })
    })
})(jQuery, window, document);

/**
 *  jQuery Animation Easing Extended Functios
 * 
 */
;jQuery.extend(jQuery.easing,{linear:function(a,b,c,d){return c+d*a},backEaseIn:function(a,b,c,d){var e=c+d,f=1.70158;return e*(a/=1)*a*((f+1)*a-f)+c},backEaseOut:function(a,b,c,d){var e=c+d,f=1.70158;return e*((a=a/1-1)*a*((f+1)*a+f)+1)+c},backEaseInOut:function(a,b,c,d){var e=c+d,f=1.70158;return(a/=.5)<1?e/2*a*a*(((f*=1.525)+1)*a-f)+c:e/2*((a-=2)*a*(((f*=1.525)+1)*a+f)+2)+c},bounceEaseIn:function(a,b,c,d){var e=c+d,f=this.bounceEaseOut(1-a,1,0,d);return e-f+c},bounceEaseOut:function(a,b,c,d){var e=c+d;return a<1/2.75?e*7.5625*a*a+c:a<2/2.75?e*(7.5625*(a-=1.5/2.75)*a+.75)+c:a<2.5/2.75?e*(7.5625*(a-=2.25/2.75)*a+.9375)+c:e*(7.5625*(a-=2.625/2.75)*a+.984375)+c},circEaseIn:function(a,b,c,d){var e=c+d;return-e*(Math.sqrt(1-(a/=1)*a)-1)+c},circEaseOut:function(a,b,c,d){var e=c+d;return e*Math.sqrt(1-(a=a/1-1)*a)+c},circEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?-e/2*(Math.sqrt(1-a*a)-1)+c:e/2*(Math.sqrt(1-(a-=2)*a)+1)+c},cubicEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a*a+c},cubicEaseOut:function(a,b,c,d){var e=c+d;return e*((a=a/1-1)*a*a+1)+c},cubicEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a*a+c:e/2*((a-=2)*a*a+2)+c},elasticEaseIn:function(a,b,c,d){var e=c+d;if(a==0)return c;if(a==1)return e;var f=.25,g,h=e;return h<Math.abs(e)?(h=e,g=f/4):g=f/(2*Math.PI)*Math.asin(e/h),-(h*Math.pow(2,10*(a-=1))*Math.sin((a*1-g)*2*Math.PI/f))+c},elasticEaseOut:function(a,b,c,d){var e=c+d;if(a==0)return c;if(a==1)return e;var f=.25,g,h=e;return h<Math.abs(e)?(h=e,g=f/4):g=f/(2*Math.PI)*Math.asin(e/h),-(h*Math.pow(2,-10*a)*Math.sin((a*1-g)*2*Math.PI/f))+e},expoEaseIn:function(a,b,c,d){var e=c+d;return a==0?c:e*Math.pow(2,10*(a-1))+c-e*.001},expoEaseOut:function(a,b,c,d){var e=c+d;return a==1?e:d*1.001*(-Math.pow(2,-10*a)+1)+c},expoEaseInOut:function(a,b,c,d){var e=c+d;return a==0?c:a==1?e:(a/=.5)<1?e/2*Math.pow(2,10*(a-1))+c-e*5e-4:e/2*1.0005*(-Math.pow(2,-10*--a)+2)+c},quadEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a+c},quadEaseOut:function(a,b,c,d){var e=c+d;return-e*(a/=1)*(a-2)+c},quadEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a+c:-e/2*(--a*(a-2)-1)+c},quartEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a*a*a+c},quartEaseOut:function(a,b,c,d){var e=c+d;return-e*((a=a/1-1)*a*a*a-1)+c},quartEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a*a*a+c:-e/2*((a-=2)*a*a*a-2)+c},quintEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a*a*a*a+c},quintEaseOut:function(a,b,c,d){var e=c+d;return e*((a=a/1-1)*a*a*a*a+1)+c},quintEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a*a*a*a+c:e/2*((a-=2)*a*a*a*a+2)+c},sineEaseIn:function(a,b,c,d){var e=c+d;return-e*Math.cos(a*(Math.PI/2))+e+c},sineEaseOut:function(a,b,c,d){var e=c+d;return e*Math.sin(a*(Math.PI/2))+c},sineEaseInOut:function(a,b,c,d){var e=c+d;return-e/2*(Math.cos(Math.PI*a)-1)+c}})



/**************************************************************************************************
*   Main Script
**************************************************************************************************/
/**
 *  MyZebra_Form (original is Zebra_Form)
 *
 *  Client-side validation for MyZebra_Form
 *
 *  Visit {@link http://stefangabos.ro/php-libraries/zebra-form/} for more information.
 *
 *  For more resources visit {@link http://stefangabos.ro/}
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @version    2.8.5 (last revision: July 23, 2012)
 *  @copyright  (c) 2011 - 2012 Stefan Gabos
 *  @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package    MyZebra_Form
 */
/**
*
*   Edited, Augmented and Modified by Nikos M. (nikos.m@icanlocalize.com)
*
**/
 

;(function($) {
 	
    // use only CSS and markup to style checkboxes and radios
    /***************************
	  Labels
	***************************/
	/*var jqTransformGetLabel = function(objfield){
		var selfForm = $(objfield.get(0).form);
		var oLabel = objfield.next();
		if(!oLabel.is('label')) {
			oLabel = objfield.prev();
			if(!oLabel.is('label')){
				var inputname = objfield.attr('id');
				if(inputname){
					oLabel = selfForm.find('label[for="'+inputname+'"]');
				} 
			}
		}
		if(oLabel.is('label')){oLabel.css('cursor','pointer'); return oLabel;}
		return false;
	};*/
	
    /***************************
	  style Check Boxes 
	 ***************************/	
	/*$.fn.MyZebra_Form_Style_CheckBox = function(){
		return this.each(function(){
			if($(this).hasClass('myzebra-style-hidden')) {return;}

			var $input = $(this);
			var inputSelf = this;

			var aLink = $('<a href="javascript:void(0)" class="myzebra-style-checkbox"></a>');
			
            //set the click on the label
			var oLabel=jqTransformGetLabel($input);
			oLabel && oLabel.click(function(event){event.preventDefault(); aLink.trigger('click'); return false;});
			
            //wrap and add the link
			$input.addClass('myzebra-style-hidden').wrap('<span class="myzebra-style-checkbox-wrapper"></span>').parent().prepend(aLink);
			//on change, change the class of the link
			$input.bind('change',function(){
				this.checked && aLink.addClass('myzebra-style-checked') || aLink.removeClass('myzebra-style-checked');
				return true;
			});
			// Click Handler, trigger the click and change event on the input
			aLink.bind('click',function(event){
                 event.preventDefault();
				//do nothing if the original input is disabled
				if($input.attr('disabled')){return false;}
				//trigger the events on the input object
				$input.trigger('click').trigger("change");	
				return false;
			});
			
            aLink.bind('blur',function(event){
                 event.preventDefault();
				//do nothing if the original input is disabled
				if($input.attr('disabled')){return false;}
				//trigger the events on the input object
				//console.log($._data($input.get(0), "events"));
                $input.trigger('blur');	
				return false;
			});

			// set the default state
			this.checked && aLink.addClass('myzebra-style-checked');		
			// set the disabled state
			this.disabled && aLink.addClass('myzebra-style-disabled');		
		});
	};*/
	
    /***************************
	  style Radio Buttons 
	 ***************************/	
	/*$.fn.MyZebra_Form_Style_Radio = function(){
		return this.each(function(){
			if($(this).hasClass('myzebra-style-hidden')) {return;}

			var $input = $(this);
			var inputSelf = this;
				
			var aLink = $('<a href="javascript:void(0)" class="myzebra-style-radio" rel="'+ this.name +'"></a>');
			$input.addClass('myzebra-style-hidden').wrap('<span class="myzebra-style-radio-wrapper"></span>').parent().prepend(aLink);
			

			oLabel = jqTransformGetLabel($input);
			oLabel && oLabel.click(function(event){event.preventDefault(); aLink.trigger('click'); return false;});
            
            $input.bind('change',function(){
                inputSelf.checked && aLink.addClass('myzebra-style-checked') || aLink.removeClass('myzebra-style-checked');
				return true;
			});
            
            // Click Handler
			aLink.bind('click',function(event){
                event.preventDefault();
				
                if($input.attr('disabled')){return false;}
				
                $input.trigger('click').trigger('change');
				
                // uncheck all others of same name input radio elements
				$('input[name="'+$input.attr('name')+'"]',inputSelf.form).not($input).each(function(){
					($(this).attr('type')=='radio') && $(this).trigger('change');
				});
	
				return false;					
			});
			
            aLink.bind('blur',function(event){
                 event.preventDefault();
				if($input.attr('disabled')){return false;}
				$input.trigger('blur');
				
                return false;					
			});
			// set the default state
			inputSelf.checked && aLink.addClass('myzebra-style-checked');
			// set the disabled state
			inputSelf.disabled && aLink.addClass('myzebra-style-disabled');
		});
	};*/
    
    // jquery plugin to handle (flat) taxonomy fields
    $.MyZebra_Form_Taxonomy=function(ele, params)
    {
        params = $.extend({
            'remove_text':'',
            'auto_suggest':false,
            'ajax_url':''
        },params);
        
        //alert(params['auto_suggest']);
        //alert(params['ajax_url']);
        
        var esc_term=function(str) {
            // allow white space in tax terms
          return str.replace(/[^\w \d\-_]|[\f\n\r\t\v\u00A0\u2028\u2029]+/g,'');
        };
        
        var $control=ele; //$(ele);
        var hidden=$control.children('.myzebra-taxonomy-addremove').eq(0);
        var tax=hidden.attr('name');
        
        var getHiddenTerms=function()
        {
            //console.log(hidden.val());
            var matches=hidden.val().match(/^add\{(.*?)\}remove\{(.*?)\}$/i);
            var add=matches[1].split(',');
            var remove=matches[2].split(',');
            if (add=='' || add[0]=='' || !add.length) add=[];
            if (remove=='' || remove[0]=='' || !remove.length) remove=[];
            //console.log({'add':add, 'remove':remove});
            return {'add':add, 'remove':remove};
        };
        var setHiddenTerms=function(add,remove)
        {
            hidden.val('add{'+add.join(',')+'}remove{'+remove.join(',')+'}');
        };
        
        var getTerms=function()
        {
            var terms=[];
            $termscontainer.find('.myzebra-term').each(function(){
                terms.push($(this).text());
            });
            
            return terms;
        }
        
        var addTerm=function(val, do_fade, force)
        {
            val=$.trim(val);
            if (val=='') return;
            var ht=getHiddenTerms();
            var terms=val.split(',');
            for (var ii=0; ii<terms.length; ii++)
            {
                var add_to_add=true;
                // sanitize
                terms[ii]=esc_term(terms[ii]);
                if (terms[ii]=='')  continue;
                var ind=$.inArray(terms[ii],ht.remove);
                if (ind>-1)
                {
                    ht.remove.splice(ind,1); // remove term from remove list
                    add_to_add=false;
                }
                if ($termscontainer.find('.myzebra-term').filter(function(){
                    if ($(this).text()==terms[ii]) return true; 
                    else return false;}).length) 
                {
                    continue;
                }
                
                if (!force && add_to_add)
                    ht.add.push(terms[ii]);
                var $newterm=$('<div class="myzebra-term-wrapper"><a class="myzebra-term-remove" href="javascript:void(0);" title="'+params['remove_text']+'"></a><span class="myzebra-term">'+terms[ii]+'</span></div>');
                if (do_fade)
                {
                    $newterm
                    .appendTo($termscontainer)
                    .hide()
                    .fadeIn('slow');
                }
                else
                {
                    $newterm
                    .appendTo($termscontainer);
                }
            }
            if (!force)
                // trigger custom event for conditionals etc..
                $control.trigger('taxonomy.change');

            terms=null; // delete
            setHiddenTerms(ht.add,ht.remove);
        };
        
        var removeTerm=function(el, do_fade, force)
        {
            var $term=el.parent('.myzebra-term-wrapper');
            var term=$term.children('.myzebra-term').eq(0).text();
            var ht=getHiddenTerms();
            term=esc_term(term);
            var ind=$.inArray(term,ht.add);
            var add_to_remove=true;
            if (ind>-1)
            {
                    ht.add.splice(ind,1); // remove term from add list
                    add_to_remove=false;
            }
            if (term!='' && $.inArray(term,ht.remove)==-1 && add_to_remove && !force)
            {
                ht.remove.push(term);
            }
            
            setHiddenTerms(ht.add,ht.remove);
            
            if (do_fade)
                $term.fadeOut('slow',function(){
                    $(this).remove();
                    if (!force)
                        // trigger custom event for conditionals etc..
                        $control.trigger('taxonomy.change');
                });
            else
            {
                $term.remove();
            
                if (!force)
                    // trigger custom event for conditionals etc..
                    $control.trigger('taxonomy.change');
            }
        };
        
        var terminput=$control.children('.myzebra-text').eq(0);
        var $form=$control.parent('form'); // get container form
        var $termscontainer=$control.find('.myzebra-terms').eq(0);
        
        terminput.keyup(function(e){
            if ( 13 == e.which ) {
                addTerm($.trim(jQuery(this).val()),true,false);
                $(this).val('');
                return false;
            }
        }).keypress(function(e){
            if ( 13 == e.which ) {
                e.preventDefault();
                return false;
            }
        });
        
        if (params['auto_suggest'])
        {
            terminput.suggest( params['ajax_url'] + '?action=cred-ajax-tag-search&tax=' + tax, { 
                delay: 500, 
                minchars: 2, 
                multiple: true, 
                multipleSep: ',' + ' ',
                resultsClass : 'myzebra-ac_results',
                selectClass : 'myzebra-ac_over',
                matchClass : 'myzebra-ac_match'
            });
        }
        
        // remove existing term
        $control.on('click', '.myzebra-terms a.myzebra-term-remove',function(){
            removeTerm($(this),true,false);
        });
        
        
        // add new term
        $control.children('.myzebra-add-new-term').click(function(){
            var val=$.trim(terminput.val());
            if (val=='') return false;
            addTerm(val,true,false);
            terminput.val('');
        });
        
        // remove non-existent terms from input
        var ht=getHiddenTerms();
        var ii=0;
        while (ii<ht.add.length)
        {
            var ind=$.inArray(ht.add[ii],ht.remove);
            
            if (ind>-1)
            {
                // remove it from remove list
                ht.remove.splice(ind,1);
            }
            
            if (
                $termscontainer.find('.myzebra-term').filter(function(){
                    if ($(this).text()==ht.add[ii]) 
                    return true; 
                    else return false;
                    }).length>0 // if term already exists
                )
            {
                // remove it from add list
                ht.add.splice(ii,1);
                ii=0;
            }
            else ii++;
        }
        var ii=0;
        while (ii<ht.remove.length)
        {
            if (
                $termscontainer.find('.myzebra-term').filter(function(){
                    if ($(this).text()==ht.remove[ii]) 
                    return true; 
                    else return false;
                    }).length==0 // if term not exists
                )
            {
                // remove it from remove list
                ht.remove.splice(ii,1);
                ii=0;
            }
            else ii++;
        }
        setHiddenTerms(ht.add,ht.remove);
        // add/remove terms if form is reloaded
        var val=$.trim(terminput.val());
        if (val!='')
            addTerm(val,false,false);
        terminput.val('');
        addTerm(getHiddenTerms().add.join(','),false,true);
        var rem=getHiddenTerms().remove;
        for (var ii=0; ii<rem.length; ii++)
            $termscontainer.find('.myzebra-term').filter(function(){
                if ($(this).text()==rem[ii]) 
                return true; 
                else return false;
                }).each(function(){
                removeTerm($(this),false,true);
            });
        $control.data('addTerm',function(term){addTerm(term,true,false);});
        $control.data('getTerms',function(){return getTerms();});
    };
    
    $.fn.MyZebra_Form_Taxonomy = function(options) {

        return this.each(function() {
            $.MyZebra_Form_Taxonomy($(this), options);
        });

    };
    
    // jquery plugin to handle (flat) taxonomy fields show popular terms functionality
    $.MyZebra_Form_TaxonomyPopular=function(ele,params)
    {
        params = $.extend({
            'mastercontrol':null,
            'show_popular_text':'',
            'hide_popular_text':''
        }, params);
        var $mastercontrol=params['mastercontrol'];
        var $control=ele;
        var $terms=$control.find('.myzebra-terms-popular').eq(0);
        $terms.find('.myzebra-term-popular').click(function(){
        
            $mastercontrol.data('addTerm')(jQuery(this).text());
        });
        $control.find('.myzebra-show-hide-popular').click(function(){
            if ($terms.is(':visible'))
            {
                $terms.hide();
                $(this).text(params['show_popular_text']);
            }
            else
            {
                $terms.show();
                $(this).text(params['hide_popular_text']);
            }
        });
        $terms.hide();
    };
    
    $.fn.MyZebra_Form_TaxonomyPopular = function(options) {

        return this.each(function() {
            $.MyZebra_Form_TaxonomyPopular($(this), options);
        });

    };
    
    // jquery plugin to handle (hierarchical) taxonomy fields show popular terms functionality
    $.MyZebra_Form_TaxonomyHierarchical=function(ele,params)
    {
        var $this=$(ele);
        var mastercont=$this.find('.myzebra-taxonomy-hierarchical');
        var msel;
        var mcheck;
        
        msel=mastercont.find('.myzebra-taxonomy-hierarchical-select-container');//find('select');
        mcheck=mastercont.find('.myzebra-taxonomy-hierarchical-checkbox-container');//find('input[type="checkbox"]');
        
        var getTerms=function()
        {
            var terms=[];
            
            if (msel.length)
            {
                msel.find('select option:selected').each(function(){
                    terms.push($(this).text());
                });
            }
            else if (mcheck.length)
            {
                mcheck.find('input[type="checkbox"]:checked').each(function(){
                    terms.push($(this).siblings('.myzebra-checkbox-label-span').text());
                });
            }
            
            //console.log(terms);
            return terms;
        }
        
        $this.data('getTerms',function(){return getTerms();});
        
        // trigger custom events for taxonomy change
        if (msel.length)
        {
            msel.find('select').change(function(event){
                //alert('change');
                event.stopPropagation();
                $this.trigger('taxonomy.change');
            });
        }
        else if (mcheck.length)
        {
            mcheck.on('change','input[type="checkbox"]',function(event){
                //alert('change');
                event.stopPropagation();
                $this.trigger('taxonomy.change');
            });
        }
    };
    
    $.fn.MyZebra_Form_TaxonomyHierarchical = function(options) {

        return this.each(function() {
            $.MyZebra_Form_TaxonomyHierarchical($(this), options);
        });

    };
    
    // jquery plugin to handle (hierarchical) taxonomy fields show popular terms functionality
    $.MyZebra_Form_TaxonomyHierarchicalAddNew=function(ele,params)
    {
        var $thiss=ele;
        var mastercont=params['mastercontrol'].find('.myzebra-taxonomy-hierarchical');
        var masterid=params['mastercontrol'].attr('id');
        var hierarchy=mastercont.find('#'+masterid+'_hierarchy');
        var chosen=mastercont.find('#'+masterid+'_chosen').val().split(',') || [];
        var sel=$thiss.find('select');
        var msel;
        var mcheck;
        var tt=$thiss.find('.myzebra-add-text');
        var addb=$thiss.find('.myzebra-add-new-term');
        $thiss.find('.myzebra-add-new-hierarchical-terms').hide();
        // open taxonomy add new select box
        $thiss.find('.myzebra-add-new-hierarchical').click(function(){
            if (!$(this).hasClass('myzebra-add-new-hierarchical-open'))
            {
                $(this).addClass('myzebra-add-new-hierarchical-open');
                $thiss.find('.myzebra-add-new-hierarchical-terms').show();
                // populate select terms
                msel=mastercont.find('.myzebra-taxonomy-hierarchical-select-container');//find('select');
                mcheck=mastercont.find('.myzebra-taxonomy-hierarchical-checkbox-container');//find('input[type="checkbox"]');
                sel.empty();
                sel.append('<option value="-1">'+params['parent_text']+'</option>');
                if (msel.length) // taxonomy uses select
                {
                    msel.find('select option').each(function(){
                        sel.append(jQuery(this).clone());
                    });
                }
                else if (mcheck.length) // taxonomy uses checkboxes
                {
                    mcheck.find('input[type="checkbox"]').each(function(){
                        sel.append('<option value="'+jQuery(this).val()+'">'+jQuery(this).closest('.myzebra-taxonomy-hierarchical-checkbox').find('.myzebra-checkbox-label-span').eq(0).text()+'</option>');
                    });
                }
            }
            else
            {
                $(this).removeClass('myzebra-add-new-hierarchical-open');
                $thiss.find('.myzebra-add-new-hierarchical-terms').hide();
            }
        });
        
        var addHandler=function(){
            var newtax=$.trim(tt.val().replace(/[^\w\- ]+/g,''));
            if (newtax=='') return false;
            
            var parent=sel.val();
            if (msel.length)
            {
                msel.find('select').prepend('<option value="'+newtax+'" selected="selected">'+newtax+'</option>');
            }
            else if (mcheck.length)
            {
                var name=mastercont.find('.myzebra-taxonomy-hierarchical-name-parameter').text();
                var tmpl=$("<div style='position:relative;line-height:0.9em;margin:2px 0;margin-left:15px;' class='myzebra-taxonomy-hierarchical-checkbox'><label class='myzebra-style-label'><input type='checkbox' class='myzebra-control myzebra-checkbox' name='"+name+"' value='"+newtax+"' checked='checked' /><span class='myzebra-checkbox-replace'></span><span class='myzebra-checkbox-label-span'  style='position:relative;font-size:12px;display:inline-block;margin:0;padding:0;margin-left:15px;'>"+newtax+"</span></label></div>");
                /*mastercont*/mcheck.prepend(tmpl);
                // add style
                //tmpl.find('input[type="checkbox"]').MyZebra_Form_Style_CheckBox();
            }
            tt.val('');
            hierarchy.val(hierarchy.val()+'{'+parent+','+newtax+'}');
            $thiss.find('.myzebra-add-new-hierarchical').removeClass('myzebra-add-new-hierarchical-open');
            $thiss.find('.myzebra-add-new-hierarchical-terms').hide();
            params['mastercontrol'].trigger('taxonomy.change');
            return false;
        };
        
        // add button
        addb.click(addHandler);
        
        // enter key on input
        tt.keyup(function(e){
            if ( 13 == e.which ) {
                addHandler();
                return false;
            }
        }).keypress(function(e){
            if ( 13 == e.which ) {
                e.preventDefault();
                return false;
            }
        });
        
        // add already terms
        if (hierarchy.val()!='')
        {
            msel=mastercont.find('.myzebra-taxonomy-hierarchical-select-container');//find('select');
            mcheck=mastercont.find('.myzebra-taxonomy-hierarchical-checkbox-container');//find('input[type="checkbox"]');
            var newterms=hierarchy.val().match(/\{[^\{\},]+,[^\{\},]+\}/g);
            for (var ii=0; ii<newterms.length; ii++)
            {
                var newterm11=newterms[ii].replace(/\{|\}/g,'');
                var terms=newterm11.split(',');
                if (msel.length)
                {
                    if ($.inArray(terms[1],chosen)!=-1)
                        msel.find('select').prepend('<option value="'+terms[1]+'" selected="selected">'+terms[1]+'</option>');
                    else
                        msel.find('select').prepend('<option value="'+terms[1]+'">'+terms[1]+'</option>');
                }
                else if (mcheck.length)
                {
                    var name=mastercont.find('.myzebra-taxonomy-hierarchical-name-parameter').text();
                    if ($.inArray(terms[1],chosen)!=-1)
                        var tmpl=$("<div style='position:relative;line-height:0.9em;margin:2px 0;margin-left:15px;' class='myzebra-taxonomy-hierarchical-checkbox'><input type='checkbox' class='myzebra-control myzebra-checkbox' name='"+name+"' value='"+terms[1]+"' checked='checked' /><span class='myzebra-checkbox-label-span'  style='position:relative;font-size:12px;display:inline-block;margin:0;padding:0;margin-left:15px;'>"+terms[1]+"</span></div>");
                    else
                        var tmpl=$("<div style='position:relative;line-height:0.9em;margin:2px 0;margin-left:15px;' class='myzebra-taxonomy-hierarchical-checkbox'><input type='checkbox' class='myzebra-control myzebra-checkbox' name='"+name+"' value='"+terms[1]+"' /><span class='myzebra-checkbox-label-span'  style='position:relative;font-size:12px;display:inline-block;margin:0;padding:0;margin-left:15px;'>"+terms[1]+"</span></div>");
                    
                    /*mastercont*/mcheck.prepend(tmpl);
                    // add style
                    //tmpl.find('input[type="checkbox"]').MyZebra_Form_Style_CheckBox();
                }
                
            }
        }
    };
    
    $.fn.MyZebra_Form_TaxonomyHierarchicalAddNew = function(options) {

        return this.each(function() {
            $.MyZebra_Form_TaxonomyHierarchicalAddNew($(this), options);
        });

    };
    
    // hide / show effects
    $.fn.fadeToggle = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).animate({opacity: 'toggle'}, speed, easing, function() { 
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            });
        });      
    };
    
    $.fn.slideFadeToggle = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).animate({opacity: 'toggle', height: 'toggle'}, speed, easing, function() { 
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            }); 
        }); 
    };
    
    $.fn.slideFadeDown = function(speed, easing, callback) { 
        easing = easing || 'linear';
        return this.each(function(){$(this).animate({opacity: 'show', height: 'show'}, speed, easing, function() { 
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            }); 
        }); 
    };
    
    $.fn.slideFadeUp = function(speed, easing, callback) { 
        easing = easing || 'linear';
        return this.each(function(){$(this).animate({opacity: 'hide', height: 'hide'}, speed, easing, function() { 
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            }); 
        }); 
    };

    // Add support for WP Insert Media button to CLEditor
    (function($) {
      // Define the Insert Media button
      $.cleditor.buttons.insertMedia = {
        name:           "insertMedia",  // that is how it will be inserted in CLEditor controls
        //css:          {background:"url(http://localhost/crud/wp-admin/images/media-button.png) no-repeat center center"},
        css:            {background:"url("+myzebra.insertMediaIconURL+") no-repeat center center"},
        title:          "Insert Media",
        command:        "inserthtml",
        popupName:      "insertMedia",
        popupClass:     "cleditorPopup",
        popupContent:   "",
        buttonClick:    insertMediaHandler
      };
          
      // Hook the source button to hide/show resize button (chrome issue)
      $.cleditor.buttons.source.buttonClick = function(e, data){
            var editor=data.editor;
            if (editor.disabled)  return true; // bypass
            var resize_but=editor.$area.closest('.myzebra-rich-editor-container').find('.myzebra-rich-editor-resize');
            if (editor.sourceMode())
            {
                resize_but.show();
            }
            else
            {
                resize_but.hide();
            }
            return true;  // not prevent default behaviour
      };
     
      // Handle the insertMedia button click event
      // REQUIRES thickbox.js to be loaded
      function insertMediaHandler(e, data) 
      {
            // Get the editor
            var editor = data.editor;
            // hide the popup
            editor.hidePopups();
            data.popup && $(data.popup).remove();
            
            // access current form and post_id
            //var $form = editor.$area.closest('form');
            var post_field = editor.$area.closest('form').find('input[name="'+myzebra.PREFIX+'post_id'+'"]');
            if (!post_field.length) 
            {
                //alert('No post id');
                return;
            }
            var post_id = post_field.val();
            
            // define callback function to insert to editor
            window.send_to_editor = function(html) 
            {
                // insert media link to editor
                editor.execCommand(data.command, html, null, data.button);
                // Hide the thickbox and set focus back to the editor
                tb_remove();
                editor.focus();
            }
            // open thickbox for Media Insert
            tb_show('Insert Media', myzebra.insertMediaPopupURL+'?post_id='+post_id+'&amp;type=image&amp;TB_iframe=true');
            return false;
      }
    })($);            
    
    $.MyZebra_Form = function(element, options) {

        var plugin = this;
        
        var hasConditionals=options.conditionals!==false;
        
        var _conditionals=options.conditionals;

        // public properties
        var defaults = {
            scroll_to_error: true,
            tips_position: 'left',
            close_tips: true,
            validate_on_the_fly: false,
            validate_all: false,
            assets_path: null,
            myzebra_datepicker:{}
        }
        
        var myzebra_datepicker={};
        
        plugin.settings = {}
        // used for storing current editor resizing
        plugin.editor_resize=null;
        
        // private properties
        var validation_rules = new Object,
            controls_groups = new Object,
            error_blocks = new Object,
            placeholders = new Array,
            reload = false;

        plugin.remove_field_action=function(link)
        {
            link=$(link);
            var parent=link.closest('.myzebra-repeatable-field');
            var control=parent.find('.myzebra-control').eq(0);
            var id=control.attr('id');
            // remove error message if any
            $('#MyZebra_Form_error_message_' + id).remove();
            // remove spinner if any
            $('#' + id + '_spinner').remove();
            // delete data
            delete validation_rules[id];
            delete controls_groups[id];
            delete error_blocks[id];
            delete plugin.settings.error_messages[id];
            delete myzebra_datepicker[id];
            parent.fadeOut('slow',function(){
                $(this).remove();
            });
        };
        
        // the jQuery version of the element
        // "form" (without the $) will point to the DOM element
        var $form = $(element),
            form = element;

        // code by Joyce Babu
        // found at http://www.weberdev.com/get_example-4437.html
        plugin.filter_input = function(filter_type, evt, custom_chars) {
            var key_code, key, control, filter = '';
            var alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            var digits = '0123456789';
            if (window.event) {
                key_code = window.event.keyCode;
                evt = window.event;
            } else if (evt)
                key_code = evt.which;
            else
                return true;
            switch (filter_type) {
                case 'alphabet':
                    filter = alphabet;
                    break;
                case 'digits':
                case 'number':
                case 'float':
                    filter = digits;
                    break;
                case 'alphanumeric':
                    filter = alphabet + digits;
                    break;
                default:
                    return true;
            }
            if (custom_chars) { filter += custom_chars }
            control = evt.srcElement ? evt.srcElement : evt.target || evt.currentTarget;
            if (key_code==null || key_code==0 || key_code==8 || key_code==9 || key_code==13 || key_code==27) return true;
            key = String.fromCharCode(key_code);
            if (filter.indexOf(key) > -1) return true;
            if (filter_type == 'number' && key == '-' && _get_caret_position(control) == 0) return true;
            if (filter_type == 'float' && ((key == '-' && _get_caret_position(control) == 0) || (key == '.' && _get_caret_position(control) != 0 && control.value.match(/\./) == null))) return true;
            return false;
        }

        /**
         *  Constructor method
         *
         *  @return void
         */
        plugin.element_add=function(element) {

                var /*element = $(this),*/

                    // get some attributes of the element
                    attributes = {'id': element.attr('id'), 'type': _type(element)},

                    // in order to highlight the row that the element is in
                    // get the parent element having the "row" class set
                    parent = element.closest('.row');
                
                element.unbind('click focus blur change keypress');
                
                // if date
                if (element.hasClass('myzebra-date') && !element.is('[readonly]'))
                {
                    element.MyZebra_DatePicker(myzebra_datepicker[attributes['id']]);
                    /*var myzebra_datepicker={'days':myzebra.days,'months':myzebra.months,'clear_date':myzebra['clear_date']};
                    element.MyZebra_DatePicker(myzebra_datepicker);*/
                }
                

                // if a parent element having the "row" class exists
                if (parent.length)

                    // bind these events to the element
                    element.bind({

                        // when the element receives focus
                        // add the "highlight" class to the parent element
                        'focus': function() { parent.addClass('myzebra-highlight') },

                        // when the element receives focus
                        // remove the "highlight" class from the parent element
                        'blur': function() { parent.removeClass('myzebra-highlight') }

                    });
                    
                if (

                    // if element has the "inner-label" class set
                    // meaning that the element's label needs to be shown inside the element, until the element receives focus
                    element.hasClass('myzebra-inner-label') && (

                        // the class is applied to an allowed element type
                        attributes['type'] == 'text' ||
                        attributes['type'] == 'password' ||
                        attributes['type'] == 'textarea'

                    )

                ) {

                    // get element's offset relative to the first positioned parent element
                    var position = element.position();


                    // if element is a text box or a password
                    if (attributes['type'] == 'text' || attributes['type'] == 'password')

                        // create a text element that will float above the element until the the parent element receives the focus
                        var placeholder = jQuery('<input>').attr({
                            'type':         'text',
                            'class':        'MyZebra_Form_Placeholder',
                            'autocomplete': 'off',
                            'value':        element.attr('title')
                        });

                    // if element is a textarea
                    else

                        // create a textarea element that will float above the element until the parent element receives the focus
                        var placeholder = jQuery('<textarea>').attr({
                            'class':        'MyZebra_Form_Placeholder',
                            'autocomplete': 'off'
                        }).html(element.attr('title'));

                    // position the placeholder right above the element
                    // and clone the parent element's styles
                    placeholder.css({
                        'display':          'none',
                        'fontFamily':       element.css('fontFamily'),
                        'fontSize':         element.css('fontSize'),
                        'fontStyle':        element.css('fontStyle'),
                        'fontWeight':       element.css('fontWeight'),
                        'left':             position.left,
                        'top':              position.top,
                        'width':            element.width() + (parseInt(element.css('borderLeftWidth'), 10) || 0) + (parseInt(element.css('borderRightWidth'), 10) || 0),
                        'height':           element.height() + (parseInt(element.css('borderTopWidth'), 10) || 0) + (parseInt(element.css('borderBottomWidth'), 10) || 0),
                        'paddingTop':       parseInt(element.css('paddingTop'), 10) || 0,
                        'paddingRight':     parseInt(element.css('paddingRight'), 10) || 0,
                        'paddingBottom':    parseInt(element.css('paddingBottom'), 10) || 0,
                        'paddingLeft':      parseInt(element.css('paddingLeft'), 10) || 0

                    })

                    // inject the placeholder into the DOM
                    .insertAfter(element);

                    // remove the title attribute on textareas (which was used to store the placeholder's text)
                    element.removeAttr('title');

                    // when the placeholder receives focus
                    placeholder.bind('focus', function() {

                        // pass focus to the element
                        element.focus();

                    });

                    element.bind({

                        // hide the placeholder when the element receives focus
                        'focus':    function() {
                            placeholder.css('display', 'none');
                            //$(this).addClass('highlight');
                        },

                        // if element loses focus but it's empty, show the placeholder
                        'blur':     function() { 
                            if ($(this).val() == '') placeholder.css('display', 'block');
                            //$(this).removeClass('highlight');
                            }

                    });

                    // cache the placeholder element
                    element.data('MyZebra_Form_Placeholder', placeholder);

                    // cache the elements having a placeholder
                    placeholders.push(element);

                // if element has the "other" class set and element is a drop-down
                } else if (element.hasClass('myzebra-other') && attributes['type'] == 'select-one') {

                    // run this private method that shows/hides the "other" text box depending on the selection
                    _show_hide_other_option(element);

                    // whenever the drop-down's value is changed
                    element.change(function() {

                        // run this private method that shows/hides the "other" text box depending on the selection
                        _show_hide_other_option(element);

                    });

                }

                    //console.log(attributes['id']);
                // if there are any validation rules set for this element
                if (undefined != plugin.settings.error_messages && undefined != typeof plugin.settings.error_messages[attributes['id']])
                {

                    //console.log('Register: '+attributes['id']);
                    // register the element
                    plugin.register(element, false);
                }

                // if element is a valid element with the maxlength attribute set
                if ((attributes['type'] == 'text' || attributes['type'] == 'textarea' || attributes['type'] == 'password') && element.attr('maxlength')) {

                    // because PHP and JavaScript treat new lines differently, we need to do some extra work
                    // in PHP new line characters count as 2 characters while in JavaScript as 1
                    // http://www.sitepoint.com/line-endings-in-javascript/
                    // http://drupal.org/node/1267802

                    // first, save the maxlength attribute's original value
                    // (we will dynamically change its value as we go on)
                    element.data('maxlength', element.attr('maxlength'));

                    // handle the onKeyUp event
                    element.bind('keyup', function(e) {

                        var

                            // reference to the textarea element
                            $el = $(this),

                            // the value of the "maxlength" attribute
                            maxlength = $el.data('maxlength'),

                            // the difference between PHP's way of counting and JavaScript's
                            diff = _maxlength_diff($el);

                        // adjust the maxlength attribute to reflect PHP's way of counting, where new lines count as 2
                        // characters; therefore, we need to reduce the value of maxlength with 1 for each new line
                        // character added to compensate
                        $el.attr('maxlength', maxlength - diff);

                        // if the character counter needs to be shown
                        if ($el.hasClass('myzebra-show-character-counter')) {

                            // get the number of characters left
                            var available_chars = maxlength - diff - $el.val().length;

                            // update the character counter
                            character_counter.html(available_chars < 0 ? '<span>' + available_chars + '</span>' : available_chars);

                        }

                    });

                    // if the character counter needs to be shown
                    if (element.hasClass('myzebra-show-character-counter')) {

                        var

                            // get textarea element's position relative to the first positioned element
                            position = element.position(),

                            // create the character counter
                            character_counter = jQuery('<div>', {
                                'class':    'MyZebra_Character_Counter',
                                css:    {
                                    'visibility':   'hidden'
                                }
                            })

                            // use as initial content the value of maxlength attribute
                            // to get the element's width
                            .html(element.data('maxlength'))

                            // inject it into the DOM right after the textarea element
                            // (we need to do this so we can get its width and height)
                            .insertAfter(element);

                            // get the character counter's width and height
                            width = character_counter.outerWidth(),
                            height = character_counter.outerHeight();

                        // position the character counter at the bottom-right of the textarea, and make it visible
                        character_counter.css({
                            'top':  position.top + element.outerHeight() - (height / 1.5),
                            'left':  position.left + element.outerWidth() - (width / 1.5),
                            'width': character_counter.width(),
                            'visibility': 'visible'

                        });

                        // trigger the "onKeyUp" event to do the initial computing if there is already content in the textarea
                        element.trigger('keyup');

                    }

                }

            };
        
        plugin.hideShowGroup = function(group, showGroup, mode)
        {
            var dur='slow';
            var delay=50;
            
            if (showGroup)
            {
                if (!group.parents('.myzebra-conditional-group-hidden').length)
                    group.find('.myzebra-control').not('.myzebra-file-disabled').removeAttr('disabled');
                group.removeClass('myzebra-conditional-group-hidden').addClass('myzebra-conditional-group-visible');
                switch(mode)
                {
                    case 'fade-slide':
                        setTimeout(function(){group.stop(true).slideFadeDown(dur,'quintEaseOut');},delay);
                        break;
                    case 'slide':
                        setTimeout(function(){group.stop(true).slideDown(dur,'quintEaseOut');},delay);
                        break;
                    case 'fade':
                        setTimeout(function(){group.stop(true).fadeIn(dur);},delay);
                        break;
                    case 'none':
                    default:
                        break;
                }
            }
            else
            {
                group.find('.myzebra-control').attr('disabled','disabled');
                group.removeClass('myzebra-conditional-group-visible').addClass('myzebra-conditional-group-hidden');
                switch(mode)
                {
                    case 'fade-slide':
                        setTimeout(function(){group.stop(true).slideFadeUp(dur,'quintEaseIn');},delay);
                        break;
                    case 'slide':
                        setTimeout(function(){group.stop(true).slideUp(dur,'quintEaseIn');},delay);
                        break;
                    case 'fade':
                        setTimeout(function(){group.stop(true).fadeOut(dur);},delay);
                        break;
                    case 'none':
                    default:
                        break;
                }
            }
        };
        
        plugin.getFieldVal = function(field, params)
        {
            var rval=null;
            var val;
            
            if (field.hasClass('myzebra-taxonomy') && field.data('getTerms'))
                rval=field.data('getTerms')();
            //else if (field.hasClass('myzebra-taxonomy'))
               // return null;
            else if (field.hasClass('myzebra-checkbox'))
            {
                rval=[];
                field.filter(function(){
                    if ($(this).is(':checked'))
                    {
                        val=$(this).val();
                        if (params && params[val])
                            val=params[val];
                        rval.push(val);
                        return true;
                    }
                    return false;
                });
            }
            else if (field.hasClass('myzebra-select'))
            {
                rval=[];
                field.find('option').filter(function(){
                    if ($(this).is(':selected'))
                    {
                        val=$(this).val();
                        if (params && params[val])
                            val=params[val];
                        rval.push(val);
                        return true;
                    }
                    return false;
                });
            }
            else if (field.hasClass('myzebra-radio'))
            {
                field.filter(function(){
                    if ($(this).is(':checked'))
                    {
                        val=$(this).val();
                        if (params && params[val])
                            val=params[val];
                        rval=val;
                        return true;
                    }
                    return false;
                });
            }
            else
                rval=field.eq(0).val();
            //console.log('Params ');
            //console.log(params);
            //console.log('VAL ');
            //console.log(rval);
            return rval;
        };
        
        plugin.runConditional = function(condition)
        {
            //return;
            var showGroup, runCondition,  condition,  vars, ii, field, val, changed, group;
            
            vars=[];
            //runCondition=true;
            if (condition.map.length)
            {
                for (ii=0; ii<condition.map.length; ii++)
                {
                    if (!condition.map[ii].formfield) // cache selectors
                    {
                        condition.map[ii].formfield=$form.find('.myzebra-prime-name-'+condition.map[ii].field_name);//find('#'+condition.map[ii].field);
                        changed=true;
                    }
                    //else alert('cache');
                    field=condition.map[ii].formfield;
                    //console.log(condition.map[ii]);
                    val=plugin.getFieldVal(field,condition.map[ii].values_map);
                    /*if (val===null)
                    {
                        // time to load
                        setTimeout(function(){plugin.runConditional(condition);},100);
                        return;
                    }*/
                    if (field.hasClass('myzebra-date'))
                        vars.push({name: condition.map[ii].variable, val: val, withType: 'date', format: plugin.settings.myzebra_datepicker[condition.map[ii].field].format});
                    else if (typeof(val)=='object' && ((val instanceof Array) || Object.prototype.toString.call(val) == '[object Array]'))
                        vars.push({name: condition.map[ii].variable, val: val, withType: 'array'});
                    else
                        vars.push({name: condition.map[ii].variable, val: val, withType: 'string'});
                }
            }
            if (!condition.RESULT)
            {
                try{
                    showGroup=condition.parser(vars);
                }catch(e){
                    // handle parse errors as failed conditions
                    if (typeof(e)=='string' || e instanceof String)
                        plugin.log('Parser Error: ' + e);
                    else
                        plugin.log('Parser Error: ' + e.message);
                    showeGroup=false;
                }
            }
            else
            {
                // expression is constant, do not re-evaluate
                showGroup=condition.RESULT;
            }
            
            // constant expression (no variables), evaluate only once
            if (!condition.map.length && !condition.RESULT)
            {
                condition.RESULT=showGroup;
                changed=true;
            }
                
            if (!condition.$group)
            {
                condition.$group=$form.find('#'+condition.group);
                changed=true;
            }
            
            // if changed from prev state
            if ((showGroup && !condition.$group.hasClass('myzebra-conditional-group-visible')) || (!showGroup && !condition.$group.hasClass('myzebra-conditional-group-hidden')))
                plugin.hideShowGroup(condition.$group, showGroup, condition.mode);
                
            return condition;
        };
        
        plugin.conditionalControl = function()
        {
            //return;
            //alert($(this).attr('class'));
            var conditions=$(this).data('_conditionalControl');
            if (!conditions) return;
            
            var showGroup, runCondition,  condition,  vars, ii, field, val, changed, group;
            
            changed=false;
            for (var cc=0; cc<conditions.length; cc++)
            {
                condition=conditions[cc];
                vars=[];
                //runCondition=true;
                if (condition.map.length)
                {
                    for (ii=0; ii<condition.map.length; ii++)
                    {
                        if (!condition.map[ii].formfield) // cache selectors
                        {
                            condition.map[ii].formfield=$form.find('.myzebra-prime-name-'+condition.map[ii].field_name);//find('#'+condition.map[ii].field);
                            changed=true;
                        }
                        //else alert('cache');
                        field=condition.map[ii].formfield;
                        val=plugin.getFieldVal(field,condition.map[ii].values_map);
                        //console.log(val);
                        if (field.hasClass('myzebra-date'))
                            vars.push({name: condition.map[ii].variable, val: val, withType: 'date', format: plugin.settings.myzebra_datepicker[condition.map[ii].field].format});
                        else if (typeof(val)=='object' && ((val instanceof Array) || Object.prototype.toString.call(val) == '[object Array]'))
                            vars.push({name: condition.map[ii].variable, val: val, withType: 'array'});
                        else
                            vars.push({name: condition.map[ii].variable, val: val, withType: 'string'});
                    }
                }
                //if (runCondition)
                //{
                    if (!condition.RESULT)
                    {
                        try{
                            showGroup=condition.parser(vars);
                        }catch(e){
                            // handle parse errors as failed conditions
                            if (typeof(e)=='string' || e instanceof String)
                                plugin.log('Parser Error: ' + e);
                            else
                                plugin.log('Parser Error: ' + e.message);
                            showeGroup=false;
                        }
                    }
                    else
                    {
                        // expression is constant, do not re-evaluate
                        showGroup=condition.RESULT;
                    }
                    
                    // constant expression (no variables), evaluate only once
                    if (!condition.map.length && !condition.RESULT)
                    {
                        condition.RESULT=showGroup;
                        changed=true;
                    }
                        
                    if (!condition.$group)
                    {
                        condition.$group=$form.find('#'+condition.group);
                        changed=true;
                    }
                    
                    // if changed from prev state
                    if ((showGroup && !condition.$group.hasClass('myzebra-conditional-group-visible')) || (!showGroup && !condition.$group.hasClass('myzebra-conditional-group-hidden')))
                        plugin.hideShowGroup(condition.$group, showGroup, condition.mode);
                //}
                conditions[cc]=condition;
            }
            // save back data
            if (changed)
                $(this).data('_conditionalControl',conditions);
        };
        
        plugin.log = function(m)
        {
            if (window.console && window.console.log)
                window.console.log(m);
        };
        
        plugin.initConditionals = function()
        {
            // handle conditional display of fields
            if (hasConditionals && MyZebraParser)
            {
                //console.log('has conditions');
                // get localized parser
                //console.log(_conditionals);
                var parser;
                
                // add parameters for parser, these must also be used in php parser
                if (myzebra.parser_info)
                    MyZebraParser.setParams(myzebra.parser_info);
                    
                try{
                    parser=new MyZebraParser.Expression().dateLocales(myzebra.days, myzebra.months);
                }
                catch(e){
                    // parser initialization error
                    if (typeof(e)=='string' || e instanceof String)
                        plugin.log('Init Parser Error: ' + e);
                    else
                        plugin.log('Init Parser Error: ' + e.message);
                    return;
                }
                
                var attached_handler={};
                
                for (var ii=0; ii<_conditionals.groups.length; ii++)
                {
                    var _group=_conditionals.groups[ii];
                    //console.log(_group);
                    
                    _group.parser=parser.preCompile(_group.condition);
                    //console.log(_group.condition);
                    for (var ff=0; ff<_group.affected_fields_names.length; ff++)
                    {
                        var _field=_group.affected_fields_names[ff];
                        var _formfield=$form.find('.myzebra-prime-name-'+_field);
                        //console.log(_formfield);
                        if (_formfield.length)
                        {
                            // handle conditional control
                            var cond_data=_formfield.data('_conditionalControl');
                            if (cond_data)
                                cond_data.push(_group);
                            else
                                cond_data=[_group];
                            _formfield.data('_conditionalControl',cond_data);
                            
                            if (!attached_handler[_field])
                            {
                                if (_formfield.hasClass('myzebra-taxonomy'))
                                    _formfield.bind('taxonomy.change',plugin.conditionalControl);
                                else
                                    _formfield.bind('change',plugin.conditionalControl);
                                attached_handler[_field]=true;
                                //console.log('Attach '+_field);
                            }
                        }
                    }
                    //_group=plugin.runConditional(_group);
                }
                // delay init
                setTimeout(function(){
                    for (var ii=0; ii<_conditionals.groups.length; ii++)
                    {
                        var _group=_conditionals.groups[ii];
                         _group=plugin.runConditional(_group);
                    }
  
                },100);
                // run them first time
                /*for (var ff in attached_handler)
                {
                    if (attached_handler.hasOwnProperty(ff))
                        $('#'+ff).trigger('change');
                }*/
            }
        };
        
        plugin.init = function() {

            //console.log(url_reg_ex);
           plugin.settings = $.extend({}, defaults, options);
           myzebra_datepicker=plugin.settings.myzebra_datepicker;

            // find all dummy options and remove them from the DOM
            // we need them"dummy options" to create valid HTML/XHTML output because empty option groups are not
            // allowed; unfortunately, IE does not support neither the "disabled" attribute nor styling these
            // empty options so we need to remove them from the DOM
            $form.find('option.myzebra-dummy').remove();

            // find any error blocks generated by the server-side script and iterate through them
            $('div.myzebra-form-error', $form).each(function() {

                // attach a function to the error block's "close" button
                $('div.myzebra-close a', $(this)).bind('click', function(e) {

                    e.preventDefault();

                    // morph the error block's height and opacity to 0
                    $(this).closest('div.myzebra-form-error').animate({

                        'height'    : 0,
                        'opacity'   : 0

                    }, 250, function() {

                        // remove from DOM when done
                        $(this).remove();

                    });

                });

            });
            // find any error blocks generated by the server-side script and iterate through them
            $('div.myzebra-form-message', $form).each(function() {

                // attach a function to the error block's "close" button
                $('div.myzebra-close a', $(this)).bind('click', function(e) {

                    e.preventDefault();

                    // morph the error block's height and opacity to 0
                    $(this).closest('div.myzebra-form-message').animate({

                        'height'    : 0,
                        'opacity'   : 0

                    }, 250, function() {

                        // remove from DOM when done
                        $(this).remove();

                    });

                });

            });

            // get all the form's elements
            var elements = $('.myzebra-control', $form);

            // iterate through the form's elements
            elements.each(function(){
                plugin.element_add($(this));
            });
            
            // handle dynamic editor resizing on the fly
            $form.on('mousedown','.myzebra-rich-editor-resize', function(event){
                //event.preventDefault();
                var container = $(this).closest('.myzebra-rich-editor-container');
                var element = container.find('.myzebra-wysiwyg');
                if (element.length)
                {
                    var editor=element.data('editor');
                    if (editor && !editor.disabled)
                    {
                        editor.updateTextArea();  // chrome needs that
                        plugin.editor_resize = {element:element, container: container, pX:event.pageX, pY:event.pageY, h:container.height(), w:container.width()};
                    }
                }
                return false;
            });
            
            $(document).bind('mouseup', function(event){
                if (plugin.editor_resize && plugin.editor_resize.element.length)
                {
                    var editor=plugin.editor_resize.element.data('editor');
                    editor.updateFrame(); // chrome needs that
                    editor.refresh();
                    //editor.focus(); // chrome needs that
                    plugin.editor_resize=null;
                }
                plugin.editor_resize=null;
            });

            $(document).bind('mousemove',function(event) {
                if (plugin.editor_resize && plugin.editor_resize.container.length)
                {
                    var parent=plugin.editor_resize.container;
                    parent.height(plugin.editor_resize.h - plugin.editor_resize.pY + event.pageY);
                    parent.width(plugin.editor_resize.w - plugin.editor_resize.pX + event.pageX);
                }
            });

            // add new repeatable elements
            $form.find('.myzebra-add-new-field').bind('click',function(){
                var parent=$(this).parent("div");
                var count=parseInt(parent.attr('class').replace(/myzebra\-count_(\d+)/,'$1'));
                var divs=parent.find('.myzebra-repeatable-field');
                // if skype field
                if (parent.find('.myzebra-skype-container').length)
                {
                    var master_control=divs.eq(0).find('.myzebra-skype-container').eq(0);
                    var master_id=master_control.attr('id').replace(/_repeat_(\d+)/,'');
                    var master_control_actual=master_control.find('.myzebra-control').eq(0);
                    var master_actual_id=master_control_actual.attr('id').replace(/_repeat_(\d+)/,'');
                    var master_control_hidden=master_control.find('.myzebra-hidden').eq(0);
                    var name=master_control_actual.attr('name');//.replace(/\[[^\[\]]*\]$/,'');
                    var name_actual=master_control_actual.attr('name').replace(/\[[^\[\]]*\]$/,'['+count+']');
                    var name_hidden=master_control_hidden.attr('name').replace(/\[[^\[\]]*\]$/,'['+count+']');
                    var id=master_id+'_repeat_'+count;//.replace(/_repeat_(\d+)/,'_repeat_'+count);
                    var clone_div,clone_element;
                    clone_div=divs.eq(0).clone(true);
                    if (clone_div.find('.myzebra-validation-error').length>0)
                    {
                        // remove error messages and wrappers form cloned element
                        clone_div.find('.myzebra-field-error-messages').remove();
                        clone_div.find('.myzebra-validation-error').children(':eq(0)').unwrap();
                    }
                    clone_element=clone_div.find('.myzebra-control').eq(0);
                    clone_element_hidden=clone_div.find('.myzebra-hidden').eq(0);
                    clone_element.removeAttr('disabled');
                    clone_element.removeClass('myzebra-file-disabled');
                    clone_element.removeData();
                    clone_element.attr('id',clone_element.attr('id').replace(master_id,id));
                    clone_element_hidden.attr('id',clone_element_hidden.attr('id').replace(master_id,id));
                    clone_element_link=clone_div.find('.myzebra-edit-skype').eq(0);
                    clone_element_link.attr('href',clone_element_link.attr('href').replace(master_id,id));
                    clone_element.attr('name',name_actual);
                    clone_element_hidden.attr('name',name_hidden);
                    clone_div.find('.myzebra-skype-container').attr('id',clone_div.find('.myzebra-skype-container').attr('id').replace(master_id,id));
                    clone_div.find('.myzebra-skype-preview-image-container').attr('id',clone_div.find('.myzebra-skype-preview-image-container').attr('id').replace(master_id,id));
                    if (plugin.settings.error_messages[master_actual_id])
                        plugin.settings.error_messages[clone_element.attr('id')]=$.extend({},plugin.settings.error_messages[master_actual_id]);
                    //if (vaidation_rules[master_actual_id])
                      //  validation_rules[clone_element.attr('id')]=$.extend({},validation_rules[master_actual_id]);
                    
                    parent.append(clone_div);
                    plugin.element_add(clone_element); //.each(plugin.element_add);
                    // then add remove link
                    var remove_link=$("<a href='javascript:;' class='myzebra-remove-field'>"+myzebra.remove_repeatable_field+"</a>");
                    remove_link.insertAfter(clone_element);
                    remove_link.unbind('click').bind('click',function(){
                    
                        plugin.remove_field_action(this);
                    });
                    parent.attr('class','myzebra-count_'+(++count));
                    clone_div.hide().fadeIn('slow');
                    return;
                }
                
                var master_control=divs.eq(0).find('.myzebra-control').eq(0);
                var master_id=master_control.attr('id').replace(/_repeat_(\d+)/,'');
                var name=master_control.attr('name').replace(/\[[^\[\]]*\]$/,'['+count+']');
                var id=master_id+'_repeat_'+count;//.replace(/_repeat_(\d+)/,'_repeat_'+count);
                var clone_div,clone_element;
                var attributes = {'id': master_control.attr('id'), 'type': _type(master_control)};
                
                clone_div=divs.eq(0).clone(true);
                if (clone_div.children('.myzebra-validation-error').length>0)
                {
                    // remove error messages and wrappers form cloned element
                    clone_div.find('.myzebra-field-error-messages').remove();
                    clone_div.children('.myzebra-validation-error').children(':eq(0)').unwrap();
                }
                if (master_control.hasClass('myzebra-file'))
                {
                    clone_div.find('input[type="text"]').parent('div').remove();
                }
                if (master_control.hasClass('myzebra-date'))
                {
                    clone_div.find('.MyZebra_DatePicker_Icon').remove();
                }
                clone_element=clone_div.find('.myzebra-control').eq(0);
                clone_element.removeAttr('disabled');
                clone_element.removeData();
                clone_element.attr('id',id);
                clone_element.attr('name',name);
                clone_element.val('');
                clone_element.attr('value','');
                
                if (plugin.settings.error_messages[master_id])
                    plugin.settings.error_messages[id]=$.extend({},plugin.settings.error_messages[master_id]);
                //if (vaidation_rules[master_id])
                  //  validation_rules[clone_element.attr('id')]=$.extend({},validation_rules[master_id]);
                if (attributes['type']=='file' && plugin.settings.error_messages[id].file_value)
                    delete plugin.settings.error_messages[id].file_value;
                    
                if (clone_element.hasClass('myzebra-date'))
                {
                    myzebra_datepicker[id]=$.extend({},myzebra_datepicker[master_id]);
                }
                //console.log(myzebra_datepicker);
                parent.append(clone_div);
                plugin.element_add(clone_element); //.each(plugin.element_add);
                // in case element changes as in file controls
                clone_element=clone_div.find('.myzebra-control').eq(0);
                clone_element.css('visibility','visible').show();
                // then add remove link
                var remove_link=$("<a href='javascript:;' class='myzebra-remove-field'>"+myzebra.remove_repeatable_field+"</a>");
                // if date field
                if (clone_element.hasClass('myzebra-date'))
                    remove_link.insertAfter(clone_element.parent('.myzebra-date-container'));
                else
                    remove_link.insertAfter(clone_element);
                remove_link.unbind('click').bind('click',function(){
                
                    plugin.remove_field_action(this);
                });
                parent.attr('class','myzebra-count_'+(++count));
                clone_div.hide().fadeIn('slow');
            });
            

            $form.find('.myzebra-remove-field').each(function(){
                
                var $this=$(this);
                var parent=$this.closest('.myzebra-repeatable-field')
                var control=parent.find('.myzebra-control').eq(0);
                var file_text=parent.find('.myzebra-file-text').eq(0);
                if (file_text.length>0)
                    $this.remove().insertAfter(file_text);
                else
                    $this.remove().insertAfter(control);
                
                $this.unbind('click').bind('click',function(){
                    plugin.remove_field_action(this);
                });
            });
            
            // attach a function to the form's "submit" event
            //$form.bind('submit', function(e) {
            $('body').on('submit', '#'+$form.attr('id'),function(e) {

                // if
                if (

                    // form is not to be simply reloaded
                    reload == false &&

                    // and there are any controls that need to be validated
                    undefined != plugin.settings.error_messages

                ) {

                    // if not all controls validate
                    if (!plugin.validate()) {

                        // prevent the form from being submitted
                        // we check for the existence of "e" because the form can also be submitted by calling the
                        // submit() method, case in which "e" is not available
                        if (undefined != e) e.preventDefault();

                        // show the appropriate error message
                        plugin.show_errors();

                    // if form validates but was submitted by calling the object's submit() method
                    // submit the form
                    } else if (undefined == e) $form.submit();

                }

            });

            // if there are any placeholders on the page,
            // continuously checks for value updates on fields having placeholders.
            // We needs this so that we can hide the placeholders when the fields are updated by the browsers' auto-complete
            // feature.
            if (placeholders.length > 0) setInterval(_check_values, 100);
            
            // if validation errors, scroll to first one
            var validation_error=$form.find('.myzebra-validation-error');
            if (validation_error.length)
            {
                // go to first error
                $('html').scrollTop( validation_error.eq(0).offset().top-80  );
                //$('html').animate({scrollTop:validation_error.eq(0).offset().top-80},500);
            }
            
            plugin.initConditionals();
        }

        /**
         *  Shows an error tooltip, with a custom message, for a given element.
         *
         *  @param  jQuery  element     The form's element to attach the tip to.
         *
         *  @param  string  message     The message to be displayed in the tooltip.
         *
         *  @return void
         */
        plugin.attach_tip = function(element, message) {

    		// get element's ID
            var id = element.attr('id');

    		// bind the message to the target element
            validation_rules[id].message = message;

    		// show the error message
            plugin.show_errors(element);

        }

        /**
         *  Hides all error tooltips.
         *
         *  @return void
         */
        plugin.clear_errors = function() {

            // remove all iFrameShims (if available) from the DOM
            $form.find('.MyZebra_Form_error_iFrameShim').remove();

            // remove all error messages from the DOM
            $form.find('.MyZebra_Form_error_message').remove();

            // remove all error blocks
            error_blocks = [];

        }

        /**
         *  After a file upload occurs, the script will automatically run this method that removes the temporarily created
         *  iFrame element, the spinner and replaces the file upload control with the name of the uploaded file.
         *
         *  @param  object  element     The name (id) of the file upload element
         *
         *  @param  array   file_info   An array of properties of the uploaded file, returned by process.php
         *
         *  @return void
         *
         *  @access private
         */
        plugin.handle_file=function($element,file_info,value,disabled)
        {
            if (typeof disabled=='undefined')
                disabled=false;
            
            //var

                // get the element's coordinates, relative to the document
                //coordinates = $element.offset(),

                // create an element containing the file's name
                // which will replace the container with the file upload control
                /*file_name = jQuery('<div>', {

                    'class':    'MyZebra_Form_filename',
                    css: {
                        'left':         coordinates.left,
                        'top':          coordinates.top,
                        'width':        $element.outerWidth(),
                        'opacity':      0
                    }

                // set the file's name as the content of the newly created element
                }).html(file_info[0]),*/
                var $div = jQuery('<div class="myzebra-file-text"/>').insertAfter($element);
                var file_name_field = jQuery('<input>', {
                    'id'    : $element.attr('id')+'_text',
                    'class' : $element.attr('class'),
                    'style' : "position:relative;",
                    'type'  : "text",
                    'name'  : $element.attr('name'),
                    'value' : value //decodeURIComponent(validation_rules[$element.attr('id')]['rules']['upload'][1])+file_info[0]
                    });
                
                //alert($div.length);                
                $div.append(file_name_field);
                //alert(file_name_field.length);                
                
                if (disabled)
                {
                    $element.attr('disabled','disabled');
                    $element.hasClass('myzebra-file-disabled') || $element.addClass('myzebra-file-disabled');
                }
                    
                $element.hide();

                // add also an "close" button for canceling the file selection
                var cancel_button = jQuery('<a>', {

                    'href': 'javascript:void(0)',
                    'class': 'myzebra-file-text-close',
                    'title': myzebra.cancel_upload_text

                })/*.html('&times;')*/.unbind('click').bind('click', function(e) {

                    // stop default event
                    e.preventDefault();

                    // remove the uploaded file's name from the DOM
                    //file_name.remove();
                    $div.remove();

                    // clear the element's value
                    $element.val('');
                    

                    // if the element has the "file_info" attribute set, remove it
                    if ($element.data('file_info')) $element.removeData('file_info');
                    $('#'+$element.attr('id')+'_spinner').remove();
                    
                    // make the element visible
                    $element.css('visibility', 'visible').show();
                    $element.removeAttr('disabled');
                    element.disabled=false;
                    !$element.hasClass('myzebra-file-disabled') || $element.removeClass('myzebra-file-disabled');
                });
                $div.append(cancel_button);
                
            // inject everything into the DOM
            //$('body').append(file_name.append(cancel_button));

            // fine tune the element's position and make it visible
            /*file_name.css({
                'top':      parseInt(file_name.css('top'), 10) + (($element.outerHeight() - file_name.outerHeight()) / 2),
                'opacity':  1
            });*/
            

        };
        
        plugin.end_file_upload = function(element, file_info) {

            //console.log(element);
            //console.log(file_info.length);
            
            var $element = $('#' + element);

            // if element exists
            if ($element.length) {

                // hide any errors
                plugin.clear_errors();

                // delete the "target" attribute of the form
                $form.removeAttr('target');

                // get the element's ID
                var id = element;

                // remove from the DOM the attached IFrame
                $('#' + id + '_iframe').remove();

                // remove from the DOM the attached spinner
                $('#' + id + '_spinner').remove();

                // if element has rules attached to it
                if (undefined != validation_rules[element]) {

                    // if
                    if (

                        // the method has a second argument
                        undefined != file_info &&

                        // the second argument is an object
                        'object' == typeof(file_info) &&

                        // the second argument is properly formatted
                        undefined != file_info[0] &&
                        undefined != file_info[1] &&
                        undefined != file_info[2] &&
                        undefined != file_info[3] &&
                        undefined != file_info[4] &&
                        undefined != file_info[5]
                    )

                        // set the second argument as a property of the element
                        //console.log('set info');
                        $element.data('file_info', file_info);

                    // if control does not validate
                    if (true !== plugin.validate_control($element)) {

                        //console.log('not valid 2');
                        // clear the element's value
                        $element.val('');

                        // make the element visible (was hidden to show the spinner)
                        $element.css('visibility', 'visible');

                        // show the attached error message
                        plugin.show_errors($element);

                    // if control validates
                    } else {

                        //console.log('handle_file');
                        plugin.handle_file($element,file_info,decodeURIComponent(validation_rules[$element.attr('id')]['rules']['upload'][1])+file_info[0],false);

                    }

                }

            }

        }

        /**
         *  Hides the error tooltip for a given element.
         *
         *  @param  string  element_name    The name (id) of a form's element.
         *
         *  @return void
         */
        plugin.hide_error = function(element_name) {

            if (!element_name) return;
            
            // reference to the jQuery object
            var $element = $('#' + element_name);
            
            if ($element.length)
            {
                // get some attributes of the element
                var attributes = {'id': $element.attr('id'), 'name': $element.attr('name'), 'type': _type($element)};

                var group_name=attributes['name'];
                // sanitize element's name by removing square brackets (if available)
                attributes['name'] = attributes['name'].replace(/\[[^\[\]]*\]$/, '');
            }
            // unless there's a specific request to hide the error message attached to a specific element,
            // and we need to validate elements on the fly and the current element is not valid
            if (undefined == arguments[1] && plugin.settings.validate_on_the_fly && true !== plugin.validate_control($element))

                // we'll use this opportunity to instead show the error message attached to the current element
                // (as this method is called onblur for every element of a form)
                // the second argument instructs the script not to hide other error messages
                plugin.show_errors($element, false);

            // if we need to hide the error block attached to the current element
            else {

                var element_name1=element_name;
                if ($element.length && (attributes['type']=='radio' || attributes['type']=='checkbox'))
                    element_name1=attributes['name'];
                    
                var container = $('#MyZebra_Form_error_message_' + element_name1);
                
                // if an error block exists for the element with the given id
                if (container.length > 0) {
                    // fade out the error block
                    // (which, on complete, destroys the IFrame shim - if it exists - and also the error block itself)
                    container.animate({
                        'opacity'   : 0
                    },
                    250,
                    function() {

                        // get a reference to the iFrame shim (if any)
                        var shim = container.data('shim');

                        // if an attached iFrame shim exists, remove it from the DOM
                        if (undefined != shim) shim.remove();

                        // remove the container from the DOM
                        container.remove()

                        // remove from the error blocks array
                        delete error_blocks[element_name];

                    });

                }

            }

        }

        /**
         *  Registers a form element for validation.
         *
         *  @param  object  element A jQuery element.
         *
         *  @return void
         */
        plugin.register = function(element) {

            // get some attributes of the element
            var attributes = {'id': element.attr('id'), 'name': element.attr('name'), 'type': _type(element)};

            var group_name=attributes['name'];
            // sanitize element's name by removing square brackets (if available)
            attributes['name'] = attributes['name'].replace(/\[[^\[\]]*\]$/, '');
            
            // style checkboxes
            /*if (element.attr('type')=='checkbox')
                element.MyZebra_Form_Style_CheckBox();
                
            // style radios
            if (element.attr('type')=='radio')
                element.MyZebra_Form_Style_Radio();*/
                
            // style select boxes
            if (element.is('select') && (!element.closest('.myzebra-keep-original').length && !element.closest('.cred-keep-original').length))
                element.dropkick();
            
            switch (attributes['type']) {

                case 'radio':
                case 'checkbox':

                    // attach the function to the onClick and onBlur events
                    element.bind({

                        'click': function() { plugin.hide_error(attributes['id']); },
                        'blur': function() { plugin.hide_error(attributes['id']);  }

                    });

                    // we will also keep track of radio buttons and checkboxes sharing the same name
                    if (undefined == controls_groups[attributes['id']])

                        // group together radio buttons and checkboxes sharing the same name
                        controls_groups[attributes['id']] = $form.find('input[name="' + group_name + '"]');

                    break;

                // if element is file
                case 'file':

                    // we replace the original control with a clone, as only file controls created dynamically from
                    // javascript behave as expected

                    // create a clone of the element (along with content and ID)
                    var clone = element.clone(true);
                    var value= (plugin.settings.error_messages[element.attr('id')].file_value)?plugin.settings.error_messages[element.attr('id')].file_value:'';//element.attr('value');
                    
                    // unset the element's value
                    clone.attr('value', '');

                    // replace the original element
                    element.replaceWith(clone);

                    clone.bind({

                        // attach a function to the onKeyPress event
                        'keypress': function(e) {

                            // stop event
                            e.preventDefault();

                            // unset the element's value
                            clone.attr('value', '');

                        },

                        // attach a function to the onChange event
                        'change': function() {

                            // if upload rule exists
                            if (undefined != validation_rules[attributes['id']]['rules']['upload']) {

                                // hide any attached error message
                                plugin.hide_error(attributes['id']);

                                // if the "file_info" attribute is already set for the element
                                if (clone.data('file_info'))

                                    // remove it
                                    clone.removeData('file_info');

                                // create an IFrame that we will use to submit the form to
                                // ("name" and "id" attributes must be submitted like that and not like attributes or it won't work in IE7)
                                var iFrameSubmit = jQuery('<iframe id="' + attributes['id'] + '_iframe' + '" name="' + attributes['id'] + '_iframe' + '">', {
                                    'src':                  'javascript:void(0)',
                                    'scrolling':            'no',
                                    'marginwidth':          0,
                                    'marginheight':         0,
                                    'width':                0,
                                    'height':               0,
                                    'frameborder':          0,
                                    'allowTransparency':    'true'
                                });

                                // inject the newly created IFrame into the DOM
                                $('body').append(iFrameSubmit);

                                // save the form's original action
                                var original_action = $form.attr('action');

                                // alter the action of the form
                                var el_index=element.attr('name').match(/\.*\[(\d+?)\]$/);
                                el_index=(el_index!=null)?el_index[1]:0;
                                $form.attr('action',
                                    decodeURIComponent(plugin.settings.assets_path) + 'process.php' +
                                    '?form=' + $form.attr('id') +
                                    '&control=' + attributes['id'] +
                                    '&index=' + ((element.closest('.myzebra-repeatable-field').length>0)?el_index:0) +
                                    '&controlname='+ attributes['name'] +
                                    '&path=' + encodeURIComponent(decodeURIComponent(validation_rules[attributes['id']]['rules']['upload'][0])) +
                                    '&url=' + encodeURIComponent(decodeURIComponent(validation_rules[attributes['id']]['rules']['upload'][1])) +
                                    '&nocache=' + new Date().getTime());

                                // the form will submit to the IFrame
                                $form.attr('target', attributes['id'] + '_iframe');

                                // hide the element
                                element.css('visibility', 'hidden');

                                var

                                    // get the element's coordinates
                                    coordinates = element.offset(),
                                    //coordinates = element.position(),

                                    // crate the spinner element
                                    // and position it in the same position as the element
                                    spinner = jQuery('<div>', {
                                        'id':       attributes['id'] + '_spinner',
                                        'class':    'MyZebra_Form_spinner',
                                        css: {
                                            'left': coordinates.left,
                                            'top':  coordinates.top
                                        }
                                    });

                                // inject the newly create element into the DOM
                                $('body').append(spinner);
                                //spinner.appendTo(element.parent());

                                // make sure we submit the form without validating it - we just need to submit the uploaded file
                                reload = true;

                                // submit the form
                                $form.trigger('submit');

                                // restore the form's original action
                                $form.attr('action', original_action);

                                // reset the flag
                                reload = false;

                            }

                        },

                        // attach a function to the onBlur event
                        'blur': function() { plugin.hide_error(attributes['id']) }

                    });

                    // element will now reference the clone
                    element = clone;
                    //alert(value);
                    if (value!='')
                        plugin.handle_file(element,null,value,true);


                    break;

                // if element is a select control (single or multi-values)
                case 'select-one':
                case 'select-multiple':

                    // attach the function to the onChange and onBlur events
                    element.unbind('change blur').bind({

                        'change': function() { plugin.hide_error(attributes['id']) },
                        'blur': function() { plugin.hide_error(attributes['id']) }

                    });

                    break;

                // for all other element types (text, textarea, password)
                default:

                    // attach a function to the onBlur event
                    element.unbind('blur').blur(function() { plugin.hide_error(attributes['id']) });

            }

            // get validation rules of the element
            var rules = element.attr('class').match(/validate\[(.+)\]/);

            // if there are any rules
            if (null != rules) {

                //console.log('has rules '+element.attr('id'));
                var

                    // the regular expression used to "split" rules by comma
                    expr = /([^\,]*\(.*?\)|[^\,]+)/g,

                    // this will be the list of rules for the current element
                    rules_list = new Object;

                // iterate over the rules
                while (matches = expr.exec(rules[1])) {

                    var

                        // extract the rule's name
                        rule_name = matches[1].match(/^([^\(]+)/),

                        // extract the rule's arguments
                        rule_arguments = matches[1].match(/\((.*?)\)/);

                    // if there are any arguments to the rule
                    if (rule_arguments) {

                        // split the arguments by commas into an array
                        rule_arguments = rule_arguments[1].split(',');

                        // iterate through the arguments
                        $.each(rule_arguments, function(index) {

                            // replace some special entities set from PHP
                            rule_arguments[index] = rule_arguments[index].replace(/lsqb\;/g, '[');
                            rule_arguments[index] = rule_arguments[index].replace(/rsqb\;/g, ']');
                            rule_arguments[index] = rule_arguments[index].replace(/comma\;/g, ',');
                            rule_arguments[index] = rule_arguments[index].replace(/lsb\;/g, '(');
                            rule_arguments[index] = rule_arguments[index].replace(/rsb\;/g, ')');

                        });

                    // if there are no arguments to the rule
                    // treat arguments as "null"
                    } else rule_arguments = null;

                    // add the rule to the list of rules
                    rules_list[rule_name[1]] = rule_arguments;

                }

                // if a second argument to the method was not provided
                // it means that the script will automatically need to figure out the order in which the element will be
                // validated, based on where it is in the DOM
                if (undefined == arguments[1]) {

                    // get all the form's controls
                    var elements = $form.find('.myzebra-control');

                    // iterate through the form's controls
                    $.each(elements, function(index, el) {

                        // if we've found the element we're registering
                        if (el == element.get(0)) {

                            var

                                // the jQuery object
                                el = $(el),

                                // we need to move backwards and find the previous control in the DOM

                                // the ID of the previous element
                                previous_element_id = null,

                                // the previous control's position in the validation chain
                                position = index - 1;

                            // while
                            while (

                                // "previous_element_id" is null
                                previous_element_id == null &&

                                // a previous element exists
                                undefined != elements[position]

                            ) {

                                // get the ID of the previous element
                                previous_element_id = $(elements[position]).attr('id');

                                // decrement position
                                position--;

                            }

                            // if a previous element doesn't exists
                            if (!validation_rules[previous_element_id]) {

                                // create a temporary object
                                var tmp = new Object;

                                // assign the validation rules
                                tmp[attributes['id']] = {'element': element, 'rules': rules_list};

                                $.extend(validation_rules, tmp);

                            // if a previous element does exist
                            } else {

                                // create a temporary object which will contain the reordered validation rules
                                var new_validation_rules = new Object;

                                // iterate through the already existing validation rules
                                for (index in validation_rules) {

                                    // add each entry to the new array
                                    new_validation_rules[index] = validation_rules[index];

                                    // if we found the previous element
                                    if (previous_element_id == index)

                                        // append the validation rules for the current element
                                        new_validation_rules[attributes['id']] = {'element': element, 'rules': rules_list};

                                }

                                // copy the content of the temporary variable to the validation_rules property
                                validation_rules = new_validation_rules;

                            }

                        }

                    });

                // if a second argument to the method was provided and it is an element
                // it means that the current control needs to be validated after that particular element
                } else if (undefined != arguments[1] && $('#' + arguments[1]).length) {

                    var

                        // get the ID of the element after which the current element needs to be validated
                        id = $('#' + arguments[1]).attr('id'),

                        // create a temporary object which will contain the reordered validation rules
                        new_validation_rules = new Object;

                    // iterate through the already existing validation rules
                    for (index in validation_rules) {

                        // add each entry to the new array
                        new_validation_rules[index] = validation_rules[index];

                        // if we found the previous element
                        if (previous_element_id == index)

                            // append the validation rules for the current element
                            new_validation_rules[attributes['id']] = {'element': element, 'rules': rules_list};

                    }

                    // copy the content of the temporary variable to the validation_rules property
                    validation_rules = new_validation_rules;

                // if a second argument to the method was provided and it is boolean false
                // it means that the element will be validated in the same order as it was registered
                } else if (undefined != arguments[1] && arguments[1] === false)

                    // add the validation rules for the current element
                    validation_rules[attributes['id']] = {'element': element, 'rules': rules_list};

            //console.log('rules '+attributes['id']);
            //console.log(validation_rules[attributes['id']]);
            }
            
            if (element.hasClass('myzebra-wysiwyg'))
            {
                var controls = "bold italic underline strikethrough | bullets numbering style image link | cut copy paste pastetext | undo redo | source";
                
                if (element.hasClass('myzebra-has-media-button'))
                    controls='insertMedia '+controls;
                    
                //element.unbind('focus blur');
                //  http://stackoverflow.com/questions/4725049/jquery-rich-text-editor-how-can-be-fixed-bug-in-rich-text-editor-when-press
                // $("#input").cleditor()[0].doc.execCommand("insertBrOnReturn", false, false);
                // use p tags for firefox, chrome etc.. same as ie
                if (''==$.trim(element.val()))
                    element.val('<p>&nbsp;</p>');
                var editor = element.cleditor({
                    width:        '100%', // width not including margins, borders or padding
                    height:       '100%', // height not including margins, borders or padding
                    controls:     controls,     // controls to add to the toolbar
                    styles:       // styles in the style popup
                                    [["Paragraph", "<p>"], ["Header 1", "<h1>"], ["Header 2", "<h2>"],
                                    ["Header 3", "<h3>"],  ["Header 4","<h4>"],  ["Header 5","<h5>"],
                                    ["Header 6","<h6>"]],
                    useCSS:       false, // use CSS to style HTML when possible (not supported in ie)
                    docType:      // Document type contained within the editor
                                '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
                    docCSSFile: "",   // CSS file used to style the document contained within the editor
                    
                    /*controls:     // controls to add to the toolbar
                                "bold italic underline strikethrough subscript superscript | font size " +
                                "style | color highlight removeformat | bullets numbering | outdent " +
                                "indent | alignleft center alignright justify | undo redo | " +
                                "rule image link unlink | cut copy paste pastetext | print source",*/
                    /*colors:       // colors in the color popup
                                "FFF FCC FC9 FF9 FFC 9F9 9FF CFF CCF FCF " +
                                "CCC F66 F96 FF6 FF3 6F9 3FF 6FF 99F F9F " +
                                "BBB F00 F90 FC6 FF0 3F3 6CC 3CF 66C C6C " +
                                "999 C00 F60 FC3 FC0 3C0 0CC 36F 63F C3C " +
                                "666 900 C60 C93 990 090 399 33F 60C 939 " +
                                "333 600 930 963 660 060 366 009 339 636 " +
                                "000 300 630 633 330 030 033 006 309 303",    
                    fonts:        // font names in the font popup
                                "Arial,Arial Black,Comic Sans MS,Courier New,Narrow,Garamond," +
                                "Georgia,Impact,Sans Serif,Serif,Tahoma,Trebuchet MS,Verdana",
                    sizes:        // sizes in the font size popup
                                "1,2,3,4,5,6,7",*/
                    bodyStyle:'position:relative;'
                    })[0];
                if (element.is('[readonly]') || element.hasClass('myzebra-disabled'))
                    editor.disable(true);
                else
                    editor.disable(false);
                // turn BR into P tags
                if (!$.browser.msie)
                    editor.doc.execCommand("insertBrOnReturn", false, false);
                element.data('editor',editor);
            }

        }

        /**
         *  If the "validate_all" property is set to FALSE, it shows the error message tooltip for the first control that
         *  didn't validate.
         *
         *  If the "validate_all" property is set to TRUE, it will show error message tooltips for all the controls that
         *  didn't validate.
         *
         *  The "validate" or "validate_control" methods need to be called prior to calling this method or calling
         *  this method will produce no results!
         *
         *  @return void
         */
        plugin.show_errors = function() {

            // unless we're showing the error message for a specific element, as part of the on-the-fly validation
            if (!(undefined != arguments[1] && arguments[1] === false))

                // hide all errors tips
                plugin.clear_errors();

            var counter = 0;

            // iterate through the validation rules
            for (index in validation_rules) {

                var

                    // current validation rule
                    validation_rule = validation_rules[index],

                    // current element
                    element = validation_rule['element'],

                    // get some attributes of the element
                    attributes = {'id': element.attr('id'), 'name': element.attr('name'), 'type': _type(element)};

                    // sanitize element's name by removing square brackets (if available)
                    attributes['name'] = attributes['name'].replace(/\[[^\[\]]*\]$/, '');
                    
                    // we'll use this later for associating an error block with the element
                    var id = (attributes['type'] == 'radio' || attributes['type'] == 'checkbox' ? attributes['name'] : attributes['id']);


                // if the method has an element of the form as argument, and the current element is not that particular
                // element, skip the rest
                if (undefined != arguments[0] && arguments[0].get(0) != element.get(0)) continue;

                // if element's value did not validate (there's an error message)
                // and there isn't already an error block shown for the element
                if (undefined != validation_rule.message && undefined == error_blocks[id]) {

                    if (element.hasClass('myzebra-style-hidden'))
                        element=element.parent().children('a').eq(0);
                    // if select is replaced with dropkick use it as element
                    if (element.hasClass('myzebra-select') && $('#dk_container_'+element.attr('id')).length)
                        element=$('#dk_container_'+element.attr('id'));
                    // focus the element
                    // (IE triggers an error if control has display:none)
                    // also, don't focus on the invalid element if we're showing the error message as part of the on-the-fly validation
                    if (

                        element.css('display') != 'none' && !(undefined != arguments[1] && arguments[1] === false) &&

                        // if we have validate_all set to TRUE than focus to the first invalid control
                        !(plugin.settings.validate_all && counter > 0)

                    ) element.focus();
                    

                    // get element's coordinates
                    var element_position = $.extend(element.offset());
                    //var element_position = $.extend(element.position());

                    // find element's "right"
                    element_position = $.extend(element_position, {'right': Math.floor(element_position.left + element.width())});

//                     // weird behaviour...
//                     // if an item somewhere far below in a long list of a dropdown is selected, positions get messed up
//                     // get element's scroll
//                     var element_scroll = element.getScroll();
//
//                     // if element is scrolled vertically
//                     if (element_scroll.y != 0) {
//
//                         // adjust it's top position
//                         element_position.top += element_scroll.y;
//
//                     }

                    var

                        // the main container holding the error message
                        container = jQuery('<div/>', {
                            'class':    'MyZebra_Form_error_message',
                            'id':       'MyZebra_Form_error_message_' + id,
                            css: {
                                opacity: 0
                            }
                        }),

                        // the container of the actual error message
                        // width:auto is for IE6
                        message = jQuery('<div/>', {
                            'class':    'myzebra-message' + (!plugin.settings.close_tips ? ' myzebra-noclose' : ''),
                            css: {
                                '_width': 'auto'
                            }
                        }).

                        // add the error message
                        html(validation_rule.message).

                        // add the message container to the main container
                        appendTo(container);

                    // if a "close" button is required
                    if (plugin.settings.close_tips)

                        var

                            // create the close button
                            close = jQuery('<a/>', {
                                'href':    'javascript:void(0)',
                                'class':    'myzebra-close' + ($.browser.msie && $.browser.version.match(/^6/) ? '-ie6' : '')
                            }).

                            // all it contains is an "x"
                            html('x').

                            // add the close button to the error message
                            appendTo(message).

                            // attach the events
                            unbind('click focus').bind({

                                'click': function(e) { e.preventDefault(); plugin.hide_error($(this).closest('div.MyZebra_Form_error_message').attr('id').replace(/^MyZebra\_Form\_error\_message\_/, ''), true) },
                                'focus': function() { $(this).blur() }

                            });

                    var

                        // create the error messages's arrow
                        arrow = jQuery('<div/>', {

                        'class':    'myzebra-arrow'

                        // add it to the error message
                        }).appendTo(container);

                    // inject the error message into the DOM
                    $('body').append(container);
                    //container.appendTo(element.parent());

                    var

                        // get container's size
                        container_size = {'x': container.outerWidth(), 'y': container.outerHeight()},

                        // get arrow's size
                        arrow_size = {'x': arrow.outerWidth(), 'y': arrow.outerHeight()},

                        // the "left" of the container is set based on the "tips_position" property
                        left = (plugin.settings.tips_position == 'left' ? element_position.left : element_position.right) - (container_size.x / 2);

                    // set the arrow centered horizontally
                    arrow.css('left', (container_size.x / 2) - (arrow_size.x / 2) - 1);

                    // if element is a radio button or a checkbox
                    if (attributes['type'] == 'radio' || attributes['type'] == 'checkbox')

                        // set the "left" of the container centered on the radio button/checkbox
                        left = element_position.right - (container_size.x / 2) - (element.outerWidth() / 2) + 1;

                    // if "left" is outside the visible part of the page, adjust it
                    if (left < 0) left = 2;

                    // set left now because this might lead to text wrapping
                    container.css('left', left);

                    // now get the size again
                    container_size = {'x': container.outerWidth(), 'y': container.outerHeight()};

                    // set the container's "top"
                    var top = (element_position.top - container_size.y + (arrow_size.y / 2) - 1);

                    // if "top" is outside the visible part of the page, adjust it
                    if (top < 0) top = 2;

                    // set the final position of the container
                    container.css({
                        left:   left + 'px',
                        top:    top + 'px',
                        height: (container_size.y - (arrow_size.y / 2)) + 'px'
                    });

                    // add the error to the error blocks array
                    error_blocks[id] = container;

                    // create an IFrame shim for the container (only in IE6)
                    _shim(container);

                    // the error message is slightly transparent
                    container.animate({
                        'opacity'   : .9
                    }, 250);

                    // if this is the first error message, and we have to scroll to it,
                    // and we're not showing the error as part of the on-the-fly validation process
                    if (++counter == 1 && plugin.settings.scroll_to_error && !(undefined != arguments[1] && arguments[1] === false))

                        // scroll so that the element is centered in the viewport
                        $('html, body').animate({scrollTop: Math.max(parseInt(container.css('top'), 10) + (parseInt(container.css('height'), 10) / 2) - ($(window).height() / 2), 0)}, 0);

                    // unless we need to validate all element, don't check any further
                    if (!plugin.settings.validate_all) break;

                }

            }

        }

        /**
         *  Submits the form.
         *
         *  @return void
         */
        plugin.submit = function() {

            // if there are any controls that require validation
            // fire the form's submit event manually
            if (undefined != plugin.settings.error_messages) $form.trigger('submit');

            // otherwise
            // submit the form natively
            else form.submit();

        }

        /**
         *  Checks if an element is valid or not.
         *
         *  @param  object  element     The jQuery element to check.
         *
         *  @return boolean             Returns TRUE if every rule attached to the element was obeyed, FALSE if not.
         */
        plugin.validate_control = function(element) {

            //return true;
            var

                // get the ID and the type of the element
                attributes = {'id': element.attr('id'), 'type': _type(element)},
                
                // by default, we assume the control validates
                control_is_valid = true,

                // get the control's validation rules
                control_validation_rules = validation_rules[attributes['id']];
                //if (element.hasClass('myzebra-skype'))
                  //  console.log(control_validation_rules);
                
            //console.log(control_validation_rules);
            // if
            if (

                // control has any validation rules attached
                undefined != control_validation_rules &&
                (
                    // is not hidden OR if hidden it is a style replacement
                    element.hasClass('myzebra-style-hidden') || element.hasClass('myzebra-select') || (element.attr('disabled')!='disabled' && element.css('display') != 'none' && element.css('visibility') != 'hidden') ||

                    // element is a file control and a file was selected (and currently the element is hidden and the
                    // spinner is shown)
                    element.data('file_info')
                )

            ) {

                var

                    // if a rule is not passed, this variable hold the name of that rule
                    rule_not_passed = null,

                    // if a rule is not passed, and it is a custom rule, this variable hold the name of that rule
                    custom_rule_name = null;

                // delete any error messages for the current control
                delete control_validation_rules.message;

                //console.log('inside_validate');
                
                // iterate through the validation rules
                for (var rule in control_validation_rules['rules']) {

                    // if control is not valid, do not look further
                    if (!control_is_valid) break;

                    //console.log(rule);
                    // check the rule's name
                    switch (rule) {

                        case 'alphabet':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // the regular expression to use:
                                    // a-z plus additional characters (if any), case-insensitive
                                    var exp = new RegExp('^[a-z' + _escape_regexp(control_validation_rules['rules'][rule][0]).replace(/\s/, '\\s') + ']+$', 'ig');

                                    // if value is not an empty string and the regular expression is not matched, the rule doesn't validate
                                    if ($.trim(element.val()) != '' && !exp.test(element.val())) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'alphanumeric':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // the regular expression to use:
                                    // a-z, 0-9 plus additional characters (if any), case-insensitive
                                    var exp = new RegExp('^[a-z0-9' + _escape_regexp(control_validation_rules['rules'][rule][0]).replace(/\s/, '\\s') + ']+$', 'ig');

                                    // if value is not an empty string and the regular expression is not matched, the rule doesn't validate
                                    if ($.trim(element.val()) != '' && !exp.test(element.val())) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'compare':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // if
                                    if (

                                        // element to compare with doesn't exist OR
                                        !$('#' + control_validation_rules['rules'][rule][0]) ||

                                        // element to compare with exists
                                        // but it doesn't have the same value as the current element's value
                                        element.val() != $('#' + control_validation_rules['rules'][rule][0]).val()

                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'custom':

                            var break_inner_loop = false;

                            // iterate through the custom functions
                            $.each(control_validation_rules['rules'][rule], function(index, args) {

                                // exit if we don't need to look any further
                                if (break_inner_loop) return;

                                // create an array out of "args" which, at this point, is a string
                                args = $.map(args.split(','), function(value) { return value.replace(/mark\;/g, ',')});

                                // the final array of arguments will contain, in order, the function's name,
                                // the element's value and any additional arguments
                                args = $.merge($.merge([args.shift()], [element.val()]), args);

                                // see if function is in the global namespace (member of the window object) or in jQuery's namespave
                                var fn = (typeof args[0] == 'function') ? args[0] : (typeof window[args[0]] == 'function' ? window[args[0]] : false);

                                // if custom function exists
                                // call the custom function
                                if (fn !== false) control_is_valid = fn.apply(fn, args.slice(1));

                                // if custom function doesn't exist
                                else {

                                    // consider that the control does not pass validation
                                    control_is_valid = false;

                                    // also throw an error
                                    throw new Error('Function "' + args[0] + '" doesn\'t exist!');

                                }

                                // if the rule doesn't validate, don't check the other custom functions
                                if (!control_is_valid) {

                                    // save the custom function's name
                                    // we'll need it later to retrieve the associated error message
                                    custom_rule_name = args[0];

                                    // don't check any other custom functions
                                    break_inner_loop = true;

                                }

                            });

                            break;

                        case 'date':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'text':

                                    // if element has a value
                                    if ($.trim(element.val()) != '') {

                                        var

                                            // by default, we assume the date is invalid
                                            valid_date = false,

                                            // get the required date format
                                            format = element.data('MyZebra_DatePicker').settings.format.replace(/\s/g, ''),

                                            // allowed characters in date's format
                                            format_chars = ['d','D','j','l','N','S','w','F','m','M','n','Y','y','G','H','g','h','a','A','i','s','U'],

                                            // this array will contain the characters defining the date's format
                                            matches = new Array,

                                            // this array will contain the regular expression built for each of the characters
                                            // used in the date's format
                                            regexp = new Array;

                                        // escape characters that could have special meaning in a regular expression
                                        format = _escape_regexp(format);

                                        // iterate through the allowed characters in date's format
                                        for (var i = 0; i < format_chars.length; i++)

                                            // if character is found in the date's format
                                            if ((position = format.indexOf(format_chars[i])) > -1)

                                                // save it, alongside the character's position
                                                matches.push({character: format_chars[i], position: position});

                                        // sort characters defining the date's format based on their position, ascending
                                        matches.sort(function(a, b){ return a.position - b.position });

                                        // iterate through the characters defining the date's format
                                        $.each(matches, function(index, match) {

                                            // add to the array of regular expressions, based on the character
                                            switch (match.character) {

                                                case 'd': regexp.push('0[1-9]|[12][0-9]|3[01]'); break;
                                                case 'D': regexp.push('[a-z]{3}'); break;
                                                case 'j': regexp.push('[1-9]|[12][0-9]|3[01]'); break;
                                                case 'l': regexp.push('[a-z]+'); break;
                                                case 'N': regexp.push('[1-7]'); break;
                                                case 'S': regexp.push('st|nd|rd|th'); break;
                                                case 'w': regexp.push('[0-6]'); break;
                                                case 'F': regexp.push('[a-z]+'); break;
                                                case 'm': regexp.push('0[1-9]|1[012]+'); break;
                                                case 'M': regexp.push('[a-z]{3}'); break;
                                                case 'n': regexp.push('[1-9]|1[012]'); break;
                                                case 'Y': regexp.push('[0-9]{4}'); break;
                                                case 'y': regexp.push('[0-9]{2}'); break;
                                                case 'G':
                                                case 'H':
                                                case 'g':
                                                case 'h': regexp.push('[0-9]{1,2}'); break;
                                                case 'a':
                                                case 'A': regexp.push('(am|pm)'); break;
                                                case 'i':
                                                case 's': regexp.push('[012345][0-9]'); break;

                                            }

                                        });

                                        // if we have an array of regular expressions
                                        if (regexp.length > 0) {

                                            // we will replace characters in the date's format in reversed order
                                            matches.reverse();

                                            // iterate through the characters in date's format
                                            $.each(matches, function(index, match) {

                                                // replace each character with the appropriate regular expression
                                                format = format.replace(match.character, '(' + regexp[regexp.length - index - 1] + ')');

                                            });

                                            // the final regular expressiom
                                            regexp = new RegExp('^' + format + '$', 'ig');

                                            // if regular expression was matched
                                            if ((segments = regexp.exec(element.val().replace(/\s/g, '')))) {

                                                // check if date is a valid date (i.e. there's no February 31)

                                                var original_day = null,
                                                    original_month = null,
                                                    original_year = null,
                                                    english_days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                                                    english_months = ['January','February','March','April','May','June','July','August','September','October','November','December'],
                                                    iterable = null,

                                                    // by default, we assume the date is valid
                                                    valid = true;

                                                // reverse back the characters in the date's format
                                                matches.reverse();

                                                // iterate through the characters in the date's format
                                                $.each(matches, function(index, match) {

                                                    // if the date is not valid, don't look further
                                                    if (!valid) return true;

                                                    // based on the character
                                                    switch (match.character) {

                                                        case 'm':
                                                        case 'n':

                                                            // extract the month from the value entered by the user
                                                            original_month = parseInt(segments[index + 1], 10);

                                                            break;

                                                        case 'd':
                                                        case 'j':

                                                            // extract the day from the value entered by the user
                                                            original_day = parseInt(segments[index + 1], 10);

                                                            break;

                                                        case 'D':
                                                        case 'l':
                                                        case 'F':
                                                        case 'M':

                                                            // if day is given as day name, we'll check against the names in the used language
                                                            if (match.character == 'D' || match.character == 'l') iterable = element.data('MyZebra_DatePicker').settings.days;

                                                            // if month is given as month name, we'll check against the names in the used language
                                                            else iterable = element.data('MyZebra_DatePicker').settings.months;

                                                            // by default, we assume the day or month was not entered correctly
                                                            valid = false;

                                                            // iterate through the month/days in the used language
                                                            $.each(iterable, function(key, value) {

                                                                // if month/day was entered correctly, don't look further
                                                                if (valid) return true;

                                                                // if month/day was entered correctly
                                                                if (segments[index + 1].toLowerCase() == value.substring(0, (match.character == 'D' || match.character == 'M' ? 3 : value.length)).toLowerCase()) {

                                                                    // extract the day/month from the value entered by the user
                                                                    switch (match.character) {

                                                                        case 'D': segments[index + 1] = english_days[key].substring(0, 3); break;
                                                                        case 'l': segments[index + 1] = english_days[key]; break;
                                                                        case 'F': segments[index + 1] = english_months[key]; original_month = key + 1; break;
                                                                        case 'M': segments[index + 1] = english_months[key].substring(0, 3); original_month = key + 1; break;

                                                                    }

                                                                    // day/month value is valid
                                                                    valid = true;

                                                                }

                                                            });

                                                            break;

                                                        case 'Y':

                                                            // extract the year from the value entered by the user
                                                            original_year = parseInt(segments[index + 1], 10);

                                                            break;

                                                        case 'y':

                                                            // extract the year from the value entered by the user
                                                            original_year = '19' + parseInt(segments[index + 1], 10);

                                                            break;

                                                    }
                                                });

                                                // if everything was ok so far
                                                if (valid) {

                                                    // generate a Date object using the values entered by the user
                                                    var date = new Date(original_year, original_month - 1, original_day);

                                                    // if, after that, the date is the same as the date entered by the user
                                                    if (date.getFullYear() == original_year && date.getDate() == original_day && date.getMonth() == (original_month - 1)) {

                                                        // set the timestamp as a property of the element
                                                        element.data('timestamp', Date.parse(english_months[original_month - 1] + ' ' + original_day + ', ' + original_year));

                                                        // date is valid
                                                        valid_date = true;

                                                    }

                                                }

                                            }

                                        }

                                        // if date is not valid, the rule doesn't validate
                                        if (!valid_date) control_is_valid = false;

                                    }

                                break;
                            }

                            break;

                        case 'datecompare':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // if
                                    if (

                                        // rule is setup correctly
                                        undefined != control_validation_rules['rules'][rule][0] &&
                                        undefined != control_validation_rules['rules'][rule][1] &&

                                        // element to compare to exists
                                        $(control_validation_rules['rules'][rule][0]) &&

                                        // element to compare to has a valid date as the value
                                        plugin.validate_control($(control_validation_rules['rules'][rule][0])) === true &&

                                        // current element was validated and contains a valid date as the value
                                        undefined != element.data('timestamp')

                                    ) {

                                        // compare the two dates according to the comparison operator
                                        switch (control_validation_rules['rules'][rule][1]) {

                                            case '>':

                                                control_is_valid = (element.data('timestamp') > $('#' + control_validation_rules['rules'][rule][0]).data('timestamp'));
                                                break;

                                            case '>=':

                                                control_is_valid = (element.data('timestamp') >= $('#' + control_validation_rules['rules'][rule][0]).data('timestamp'));
                                                break;

                                            case '<':

                                                control_is_valid = (element.data('timestamp') < $('#' + control_validation_rules['rules'][rule][0]).data('timestamp'));
                                                break;

                                            case '<=':

                                                control_is_valid = (element.data('timestamp') <= $('#' + control_validation_rules['rules'][rule][0]).data('timestamp'));
                                                break;

                                        }

                                    // otherwise, there is a problem and thus, the rule does not validate
                                    } else control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'digits':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // the regular expression to use:
                                    // 0-9 plus additional characters (if any)
                                    var exp = new RegExp('^[0-9' + _escape_regexp(control_validation_rules['rules'][rule][0]).replace(/\s/, '\\s') + ']+$', 'ig');

                                    // if value is not an empty string and the regular expression is not matched, the rule doesn't validate
                                    if ($.trim(element.val()) != '' && !exp.test(element.val())) control_is_valid = false;

                                    break;
                            }

                            break;

                        case 'email':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // if value is not an empty string and the regular expression is not matched, the rule doesn't validate
                                    if ($.trim(element.val()) != '' && null == element.val().match(/^([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+@{1}([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+\.[A-Za-z0-9]{2,}$/)) control_is_valid = false;

                                    break;
                            }

                            break;

                        case 'emails':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // split addresses by commas
                                    var addresses = element.val().split(',');

                                    // iterate through the email addresses
                                    addresses.each(function(address) {

                                        // if value is not an empty string and the regular expression is not matched, the rule doesn't validate
                                        if ($.trim(address) != '' && null == $.trim(address).match(/^([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+@{1}([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+\.[A-Za-z0-9]{2,}$/)) control_is_valid = false;

                                    });

                                    break;

                            }

                            break;

                        case 'filesize':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'file':

                                    // see if a file was uploaded
                                    var file_info = element.data('file_info');

                                    // if a file was uploaded
                                    if (file_info)

                                        // if
                                        if (

                                            // there's something wrong with the uploaded file
                                            undefined == file_info[2] ||
                                            undefined == file_info[3] ||

                                            // there was a specific error while uploading the file
                                            file_info[2] != 0 ||

                                            // the uploaded file's size is larger than the allowed size
                                            parseInt(file_info[3], 10) > parseInt(control_validation_rules['rules'][rule][0], 10)

                                        // the rule doesn't validate
                                        ) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'filetype':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'file':

                                    // see if a file was uploaded
                                    var file_info = element.data('file_info');

                                    // if a file was uploaded
                                    if (file_info) {

                                        //console.log('Filetype info');
                                        // if file with mime types was not already loaded
                                        if (undefined == plugin.mimes)

                                        //console.log('Filetype load plugin mimes');
                                            // load file with mime types
                                            $.ajax({
                                                cache: false,
                                                url: decodeURIComponent(plugin.settings.assets_path) + 'mimes.json',
                                                async: false,
                                                success: function(result) {
                                                    plugin.mimes = result;
                                                },
                                                dataType: 'json'
                                            });
                                        //console.log('Filetype loaded plugin mimes');

                                        var

                                            // get the allowed file types
                                            allowed_file_types = $.map(control_validation_rules['rules'][rule][0].split(','), function(value) { return $.trim(value) }),

                                            // this will contain an array of file types that match for the currently uploaded file's mime type
                                            matching_file_types = [];
                                        
                                        //console.log('Filetype allowed types');
                                        
                                        // iterate through the known mime types
                                        $.each(plugin.mimes, function(extension, type) {

                                            // if
                                            if (

                                                // there are more mime types associated with the file extension and
                                                // the uploaded file's type is among them
                                                $.isArray(type) && $.inArray(file_info[1], type) > -1 ||

                                                // a single mime type is associated with the file extension and
                                                // the uploaded file's type matches the mime type
                                                !$.isArray(type) && type == file_info[1]

                                            )

                                                // add file type to the list of file types that match for the currently uploaded
                                                // file's mime type
                                                matching_file_types.push(extension)

                                        });

                                        //console.log('Filetype matching types');

                                        // is the file allowed?

                                        var found = false;

                                        // iterate through the mime types associted with the uploaded file
                                        $.each(matching_file_types, function(index, extension) {

                                            // if uploaded file mime type is allowed, set a flag
                                            if ($.inArray(extension, allowed_file_types) > -1) found = true;

                                        });

                                        //console.log('Filetype found types');
                                        // if file is not allowed
                                        // the rule doesn't validate
                                        if (!found) control_is_valid = false;

                                    }
                                    
                                    break;

                            }

                            break;

                        case 'float':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // the regular expression to use:
                                    // only digits (0 to 9) and/or one dot (but not as the very first character) and/or one minus sign
                                    // (but only if it is the very first character) plus characters given as additional characters (if any).
                                    var exp = new RegExp('^[0-9\-\.' + _escape_regexp(control_validation_rules['rules'][rule][0]).replace(/\s/, '\\s') + ']+$', 'ig');

                                    // if
                                    if (

                                        // value is not an empty string
                                        $.trim(element.val()) != '' &&

                                        (

                                            // value is a minus sign
                                            $.trim(element.val()) == '-' ||

                                            // value is a dot
                                            $.trim(element.val()) == '.' ||

                                            // there are more than one minus signs
                                            (null != element.val().match(/\-/g) && element.val().match(/\-/g).length > 1) ||

                                            // there are more than one dots
                                            (null != element.val().match(/\./g) && element.val().match(/\./g).length > 1) ||

                                            // if the minus sign is not the very first character
                                            element.val().indexOf('-') > 0 ||

                                            // the regular expression is not matched
                                            !exp.test(element.val())

                                        )

                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'image':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'file':

                                    // see if a file was uploaded
                                    var file_info = element.data('file_info');

                                    // if
                                    if (

                                        // a file was uploaded
                                        file_info &&

                                        // uploaded file is not a valid image type
                                        null == file_info[1].match(/image\/(gif|jpeg|png|pjpeg)/i)

                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'image_max_width':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'file':

                                    // see if a file was uploaded
                                    var file_info = element.data('file_info');

                                     // the uploaded file's size is larger than the allowed size
                                    //parseInt(file_info[4], 10) > parseInt(control_validation_rules['rules'][rule][0], 10)
                                   
                                   // if
                                    if (

                                        // a file was uploaded
                                        file_info &&

                                        // uploaded file is not a valid image type
                                        null != file_info[1].match(/image\/(gif|jpeg|png|pjpeg)/i) &&

                                         // the uploaded file's size is larger than the allowed size
                                        parseInt(file_info[4], 10) > parseInt(control_validation_rules['rules'][rule][0], 10)
                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;
                        
                        case 'image_max_height':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'file':

                                    // see if a file was uploaded
                                    var file_info = element.data('file_info');

                                     // the uploaded file's size is larger than the allowed size
                                    //parseInt(file_info[4], 10) > parseInt(control_validation_rules['rules'][rule][0], 10)
                                   
                                   // if
                                    if (

                                        // a file was uploaded
                                        file_info &&

                                        // uploaded file is not a valid image type
                                        null != file_info[1].match(/image\/(gif|jpeg|png|pjpeg)/i) &&

                                         // the uploaded file's size is larger than the allowed size
                                        parseInt(file_info[5], 10) > parseInt(control_validation_rules['rules'][rule][0], 10)
                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;
                        
                        case 'length':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // if
                                    if (

                                        // value is not an empty string
                                        element.val() != '' &&

                                        // lower limit is given and the length of entered value is smaller than it
                                        (undefined != control_validation_rules['rules'][rule][0] && (element.val().length - _maxlength_diff(element)) < control_validation_rules['rules'][rule][0]) ||

                                        // upper limit is given and the length of entered value is greater than it
                                        (undefined != control_validation_rules['rules'][rule][1] && control_validation_rules['rules'][rule][1] > 0 && (element.val().length - _maxlength_diff(element)) > control_validation_rules['rules'][rule][1])

                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'number':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // the regular expression to use:
                                    // digits (0 to 9) and/or one minus sign (but only if it is the very first character) plus
                                    // characters given as additional characters (if any).
                                    var exp = new RegExp('^[0-9\-' + _escape_regexp(control_validation_rules['rules'][rule][0]).replace(/\s/, '\\s') + ']+$', 'ig');

                                    // if
                                    if (

                                        // value is not an empty string
                                        $.trim(element.val()) != '' &&

                                        (

                                            // value is a minus sign
                                            $.trim(element.val()) == '-' ||

                                            // there are more than one minus signs
                                            (null != element.val().match(/\-/g) && element.val().match(/\-/g).length > 1) ||

                                            // the minus sign is not the very first character
                                            element.val().indexOf('-') > 0 ||

                                            // the regular expression is not matched
                                            !exp.test(element.val())

                                        )

                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'regexp':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // the regular expression to use
                                    var exp = new RegExp(control_validation_rules['rules'][rule][0], 'g');

                                    // if value is not an empty string and the regular expression is not matched, the rule doesn't validate
                                    if ($.trim(element.val()) != '' && null == exp.exec(element.val())) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'url':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'text':

                                    // the regular expression to use
                                    var exp = new RegExp(/^(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]$/gi);

                                    // if value is not an empty string and the regular expression is not matched, the rule doesn't validate
                                    if ($.trim(element.val()) != '' && null == exp.exec(element.val())) control_is_valid = false;

                                    //control_is_valid = false;
                                    break;

                            }
                            //control_is_valid = false;

                            break;
                        
                        case 'required':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'checkbox':
                                case 'radio':

                                    // by default, we assume there's nothing checked
                                    var checked = false;
                                    //console.log(controls_groups[attributes['id']]);
                                    // iterate through the controls sharing the same name as the current element
                                    controls_groups[attributes['id']].each(function() {

                                        // if any of them is checked set a flag
                                        if (this.checked && !this.disabled) checked = true;

                                    });
                                    // if nothing is checked, the rule doesn't validate
                                    if (!checked) control_is_valid = false;

                                    break;

                                case 'file':
                                case 'password':
                                case 'text':
                                case 'textarea':

                                    // if value is am empty string, the rule doesn't validate
                                    if ($.trim(element.val()) == '') control_is_valid = false;

                                    break;

                                case 'select-one':

                                    // if select control is part of a time-selection element, and no value is selected
                                    if (element.hasClass('myzebra-time') && element.get(0).selectedIndex == 0) {

                                        // the error message is set for a non-existing control with the name set when
                                        // creating the form; the actual controls have a suffix of "hours", "minutes",
                                        // "seconds" and "ampm"; so, in order to show the error message we need to
                                        // remove the suffix
                                        attributes['id'] = attributes['id'].replace(/\_(hours|minutes|seconds|ampm)$/, '');

                                        // the rule doesn't validate
                                        control_is_valid = false;

                                    // for other select boxes
                                    } else if (

                                        // if
                                        (

                                            // the "other" attribute is set
                                            element.hasClass('myzebra-other') &&

                                            // the "other" value is set
                                            element.val() == 'other' &&

                                            // nothing is entered in the attached "other" field
                                            (!$('#' + attributes['id'] + '_other').length || $.trim($('#' + attributes['id'] + '_other').val()) == '')

                                        ) ||

                                        // nothing is selected
                                        //element.get(0).value == ''
                                        //element.get(0).selectedIndex == 0
                                        (element.val()=='' || element.find('option:selected').attr('disabled'))

                                    // the rule doesn't validate
                                    ) 
                                    {
                                        control_is_valid = false;
                                    }
                                    break;

                                case 'select-multiple':

                                    // if nothing is selected, the rule doesn't validate
                                    if (element.get(0).selectedIndex == -1) control_is_valid = false;

                                    break;

                            }

                            break;

                        case 'upload':

                            // if element type is one of the following
                            switch (attributes['type']) {

                                case 'file':

                                    // see if a file was uploaded
                                    var file_info = element.data('file_info');

                                    // if
                                    if (

                                        // if file upload is required
                                        typeof control_validation_rules['rules'].required != 'undefined' &&

                                        // a file was not successfully uploaded
                                        (!file_info || !file_info[2] || file_info[2] != 0)

                                    // the rule doesn't validate
                                    ) control_is_valid = false;

                                    break;

                            }

                            break;

                    }

                    // if the rule didn't validate
                    if (!control_is_valid) {

                        //console.log('not valid');
                        // the name of the rule that didn't validate
                        rule_not_passed = rule;

                        // set the error message's text
                        control_validation_rules.message = plugin.settings.error_messages[attributes['id']][rule == 'custom' ? 'custom_' + custom_rule_name : rule_not_passed];

                        // save the element's value
                        control_validation_rules.value = element.val();

                    }

                }

            }

            //console.log('return from valid');
            // return TRUE if the all the rules were obeyed or the name of the rule if a rule didn't validate
            return (control_is_valid ? true : rule_not_passed);

        }

        /**
         *  Checks if form is valid or not
         *
         *  @return boolean     Returns TRUE if all the form's controls are valid or FALSE otherwise.
         */
        plugin.validate = function() {
            //return true;
            var control = null,
                id = null,

                // by default, we assume the form validates
                form_is_valid = true;

            // iterate through all the validation rules
            for (index in validation_rules) {

                // if form is not valid, and we don't need to check all controls, don't check any further
                if (!form_is_valid && !plugin.settings.validate_all) break;

                // get the element that needs to be validated
                element = validation_rules[index]['element'];

                // get the element's ID
                id = element.attr('id');

                // if element does not validate, the form is not valid
                if ((rule_not_passed = plugin.validate_control(element)) !== true) form_is_valid = false;

            }

            // if form validates and there's an onValid function to be run, run it
            if (form_is_valid && undefined != plugin.settings.onValid) return plugin.settings.onValid();

            // return the result of the validation
            return form_is_valid;

        }

        /**
         *  Continuously checks for value updates on fields having placeholders.
         *
         *  We needs this so that we can hide the placeholders when the fields are updated by the browsers' auto-complete
         *  feature.
         *
         *  @access private
         */
        var _check_values = function() {

            // iterate through the elements that have placeholders
            $.each(placeholders, function() {

                var

                    // reference to the jQuery version of the element
                    $element = $(this),

                    // reference to the placeholder element
                    $placeholder = $element.data('MyZebra_Form_Placeholder');

                // if element has no value and it doesn't have the focus, display the placeholder
                if ($element.val() == '' && !$element.is(':focus')) $placeholder.css('display', 'block');

                // otherwise, hide the placeholder
                else $placeholder.css('display', 'none');

            });

        }

        /**
         *  Escapes special characters in a string, preparing it for use in a regular expression.
         *
         *  @param  string  str     The string in which special characters should be escaped.
         *
         *  @return string          Returns the string with escaped special characters.
         *
         *  @access private
         */
        var _escape_regexp = function(str) {

		  return str.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1');

        }

        /**
         *  Gets the cursor's position in a text element.
         *
         *  Used by the filter_input method.
         *
         *  @param  object  element     A DOM element
         *
         *  @return integer             Returns the cursor's position in a text or textarea element.
         *
         *  @access private
         */
        var _get_caret_position = function(element) {

            // if selectionStart function exists, return the cursor's position
            // (this is available for most browsers except IE < 9)
    		if (element.selectionStart != null) return element.selectionStart;

            // for IE < 9
    		var range = document.selection.createRange(),
    		    duplicate = range.duplicate();

            // if element is a textbox, return the cursor's position
    		if (element.type == 'text') return (0 - duplicate.moveStart('character', -100000));

            // if element is a textarea
    		else {

                // do some computations...
    			var  value = element.value,
    			     offset = value.length;

    			duplicate.moveToElementText(element);
    			duplicate.setEndPoint('StartToStart', range);

                // return the cursor's position
    			return offset - duplicate.text.length;

            }

        }

        /**
         *  Computes the difference between a string's length when computed by PHP and by JavaScript.
         *
         *  In PHP new line characters have 2 bytes! Read more at
         *  http://www.sitepoint.com/line-endings-in-javascript/ and at
         *  http://drupal.org/node/1267802
         *
         *  @return void
         *
         *  @access private
         */
        var _maxlength_diff = function(el) {

            var

                // get the value in the textarea, if any
                str = el.val(),

                // get the length as computed by JavaScript
                len1 = str.length,

                // get the length as computed by PHP
                len2 = str.replace(/(\r\n|\r|\n)/g, "\r\n").length;

            // return the difference in length
            return len2 - len1;

        }

        /**
         *  Generates an iFrame shim in Internet Explorer 6 so that the tooltips appear above select boxes.
         *
         *  @return void
         *
         *  @access private
         */
        var _shim = function(el) {

            // this is necessary only if browser is Internet Explorer 6
    		if ($.browser.msie && $.browser.version.match(/^6/)) {

                // if an iFrame was not yet attached to the element
                if (!el.data('shim')) {

                    var

                        // get element's top and left position
                        offset = el.offset(),

                        // the iFrame has to have the element's zIndex minus 1
                        zIndex = parseInt(el.css('zIndex'), 10) - 1,

                        // create the iFrame
                        shim = jQuery('<iframe>', {
                            'src':                  'javascript:document.write("")',
                            'scrolling':            'no',
                            'frameborder':          0,
                            'allowTransparency':    'true',
                            'class':                'MyZebra_Form_error_iFrameShim',
                            css: {
                                'zIndex':       zIndex,
                                'position':     'absolute',
                                'top':          offset.top,
                                'left':         offset.left,
                                'width':        el.outerWidth(),
                                'height':       el.outerHeight(),
                                'filter':       'progid:DXImageTransform.Microsoft.Alpha(opacity=0)',
                                'display':      'block'
                            }
                        });

                    // inject iFrame into DOM
                    $('body').append(shim);

                    // attach the shim to the element
                    el.data('shim', shim);

                }

            }

        }

        /**
         *  Shows or hides, as necessary, the "other" options for a "select" control, that has an "other" option set.
         *
         *  @param  DOM_element     element     A  <select> element having the "other" property set.
         *
         *  @return void
         *
         *  @access private
         */
        var _show_hide_other_option = function(select) {

            // reference to the "other option" text box
            // it has the ID of the select control, suffixed by "_other"
            var element = $('#' + select.attr('id') + '_other');

            // if the select control's value is "other"
            // show the "other option" text box
            if (select.val() == 'other') element.css('display', 'block');

            // if the select control's value is different than "other"
            // hide the "other option" text box
            else element.css('display', 'none');

        }

        /**
         *  Returns an element's type
         *
         *  @param  object  element     A jQuery element
         *
         *  @return string              Returns an element's type.
         *
         *                              Possible values are:
         *
         *                              button,
         *                              checkbox,
         *                              file,
         *                              password,
         *                              radio,
         *                              submit,
         *                              text,
         *                              select-one,
         *                              select-multiple,
         *                              textarea
         *
         *  @access private
         */
        var _type = function(element) {

            // values that may be returned by the is() function
            var types = [
                'button',
                'input:checkbox',
                'input:file',
                'input:password',
                'input:radio',
                'input:submit',
                'input:text',
                'select',
                'textarea'
            ];

            // iterate through the possible types
            for (index in types)

                // if we have an element's type
                if (element.is(types[index])) {

                    // if type is "select"
                    if (types[index] == 'select') {

                        // if the "multiple" attribute is set
                        if (element.attr('multiple')) return 'select-multiple';

                        // if the "multiple" attribute is not set
                        else return 'select-one';

                    }

                    // return the element's type, from which we remove the "input:" string
                    return types[index].replace(/input\:/, '');

                }

        }

        // fire up the plugin!
        // call the "constructor" method
        plugin.init();

    }

    $.fn.MyZebra_Form = function(options) {

        return this.each(function() {
            var plugin = new $.MyZebra_Form(this, options);
            $(this).data('MyZebra_Form', plugin);
        });

    }

})(jQuery);
