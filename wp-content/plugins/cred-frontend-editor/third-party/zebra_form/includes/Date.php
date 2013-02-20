<?php

/**
 *  Class for date controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Date extends MyZebra_Form_Control
{

    /**
     *  Adds a date control to the form.
     *
     *  <b>Do not instantiate this class directly! Use the {@link MyZebra_Form::add() add()} method instead!</b>
     *
     *  The output of this control will be a {@link MyZebra_Form_Text textbox} control with an icon to the right of it.<br>
     *  Clicking the icon will open an inline JavaScript date picker.<br>
     *
     *  <code>
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // add a date control to the form
     *  // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
     *  // for PHP 5+ there is no need for it
     *  $obj = &$form->add('date', 'my_date', date('Y-m-d'));
     *
     *  // set the date's format
     *  $obj->format('Y-m-d');
     *
     *  // don't forget to always call this method before rendering the form
     *  if ($form->validate()) {
     *      // put code here
     *  }
     *
     *  // output the form using an automatically generated template
     *  $form->render();
     *  </code>
     *
     *  @param  string  $id             Unique name to identify the control in the form.
     *
     *                                  The control's <b>name</b> attribute will be the same as the <b>id</b> attribute!
     *
     *                                  This is the name to be used when referring to the control's value in the
     *                                  POST/GET superglobals, after the form is submitted.
     *
     *                                  This is also the name of the variable to be used in custom template files, in
     *                                  order to display the control.
     *
     *                                  <code>
     *                                  // in a template file, in order to print the generated HTML
     *                                  // for a control named "my_date", one would use:
     *                                  echo $my_date;
     *                                  </code>
     *
     *  @param  string  $default        (Optional) Default date, formatted according to {@link format() format}.
     *
     *  @param  array   $attributes     (Optional) An array of attributes valid for
     *                                  {@link http://www.w3.org/TR/REC-html40/interact/forms.html#h-17.4 input}
     *                                  controls (size, readonly, style, etc)
     *
     *                                  Must be specified as an associative array, in the form of <i>attribute => value</i>.
     *                                  <code>
     *                                  // setting the "readonly" attribute
     *                                  $obj = &$form->add(
     *                                      'date',
     *                                      'my_date',
     *                                      '',
     *                                      array(
     *                                          'readonly' => 'readonly'
     *                                      )
     *                                  );
     *                                  </code>
     *
     *                                  See {@link MyZebra_Form_Control::set_attributes() set_attributes()} on how to set
     *                                  attributes, other than through the constructor.
     *
     *                                  The following attributes are automatically set when the control is created and
     *                                  should not be altered manually:<br>
     *
     *                                  <b>type</b>, <b>id</b>, <b>name</b>, <b>value</b>, <b>class</b>
     *
     *  @return void
     */
    function MyZebra_Form_Date($id, $default = '', $attributes = '')
    {

        // call the constructor of the parent class
        parent::MyZebra_Form_Control();

        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        // and will not be rendered by the _render_attributes() method
        $this->private_attributes = array(

            'disable_spam_filter',
            'locked',
            'disable_xss_filters',
            'date',
            'always_show_clear',
            'always_visible',
            'days',
            'direction',
            'disabled_dates',
            'first_day_of_week',
            'format',
            'inside_icon',
            'months',
            'offset',
            'pair',
            'readonly_element',
            'show_week_number',
            'start_date',
            'view',
            'weekend_days',
            'repeatable'

        );

        // set the javascript attributes of this control
        // these attributes will be used by the JavaScript date picker object
        $this->javascript_attributes = array(

            'always_show_clear',
            'always_visible',
            'days',
            'disabled_dates',
            'direction',
            'first_day_of_week',
            'format',
            'inside_icon',
            'months',
            'offset',
            'pair',
            'readonly_element',
            'show_week_number',
            'start_date',
            'view',
            'weekend_days',

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(

            array(

                'disable_spam_filter'=>false,
                'disable_xss_filters'=>false,
                'type'                  =>  'text',
                'name'                  =>  $id,
                'id'                    =>  preg_replace('/\[.*\]$/', '', $id),
                'value'                 =>  $default,
                'class'                 =>  'myzebra-control myzebra-text myzebra-date',

                'always_show_clear'     =>  null,
                'always_visible'        =>  null,
                'days'                  =>  null,
                'direction'             =>  null,
                'disabled_dates'        =>  null,
                'first_day_of_week'     =>  null,
                'format'                =>  'Y-m-d',
                'inside_icon'           =>  null,
                'months'                =>  null,
                'offset'                =>  null,
                'pair'                  =>  null,
                'readonly_element'      =>  null,
                'show_week_number'      =>  null,
                'start_date'            =>  null,
                'view'                  =>  null,
                'weekend_days'          =>  null,

            )

        );

        // sets user specified attributes for the control
        $this->set_attributes($attributes);

    }

    /**
     *  Direction of the calendar.
     *
     *  <code>
     *  $obj = $form->add('date', 'mydate')
     *
     *  // calendar starts tomorrow and seven days after that are selectable
     *  $obj->direction(array(1, 7));
     *
     *  // calendar starts today and seven days after that are selectable
     *  $obj->direction(array(true, 7));
     *
     *  // calendar starts on January 1st 2013 and has no ending date
     *  // (assuming “format” is YYYY-MM-DD)
     *  $obj->direction(array('2013-01-01', false));
     *
     *  // calendar ends today and starts on January 1st 2012
     *  // assuming “format” is YYYY-MM-DD)
     *  $obj->direction(array(false, '2012-01-01'));
     *  </code>
     *
     *  @param  mixed   $direction      A positive or negative integer:
     *
     *                                  -   n (a positive integer) creates a future-only calendar beginning at n days
     *                                      after today;
     *
     *                                  -   -n (a negative integer) creates a past-only calendar ending at n days
     *                                      before today;
     *
     *                                  -   if n is 0, the calendar has no restrictions.
     *
     *                                  Use boolean TRUE for a future-only calendar starting with today and use boolean
     *                                  FALSE for a past-only calendar ending today.
     *
     *                                  You may also set this property to an array with two elements in the following
     *                                  combinations:
     *
     *                                  -   first item is boolean TRUE (calendar starts today), an integer > 0 (calendar
     *                                      starts n days after today), or a valid date given in the format defined by
     *                                      the “format” attribute (calendar starts at the specified date), and the second
     *                                      item is boolean FALSE (the calendar has no ending date), an integer > 0 (calendar
     *                                      ends n days after the starting date), or a valid date given in the format
     *                                      defined by the “format” attribute and which occurs after the starting date
     *                                      (calendar ends at the specified date)
     *
     *                                  -   first item is boolean FALSE (calendar ends today), an integer < 0 (calendar
     *                                      ends n days before today), or a valid date given in the format defined by the
     *                                      "format" attribute (calendar ends at the specified date), and the second item
     *                                      is an integer > 0 (calendar ends n days before the ending date), or a valid
     *                                      date given in the format defined by the “format” attribute and which occurs
     *                                      before the starting date (calendar starts at the specified date)
     *
     *
     *                                  Note that {@link disabled_dates()} will still apply!
     *
     *                                  Default is 0 (no restrictions).
     *
     *  @return void
     */
    function direction($direction)
    {

        // set the date picker's attribute
        $this->set_attributes(array('direction' => $direction));

    }

    /**
     *  Disables selection of specific dates or range of dates in the calendar.
     *
     *  <code>
     *  $obj = $form->add('date', 'mydate')
     *
     *  // disable January 1, 2012
     *  $obj->disabled_date(array('1 1 2012'));
     *
     *  // disable all days in January 2012
     *  $obj->disabled_date(array('* 1 2012'));
     *
     *  // disable January 1 through 10 in 2012
     *  $obj->disabled_date(array('1-10 1 2012'));
     *
     *  // disable January 1 and 10 in 2012
     *  $obj->disabled_date(array('1,10 1 2012'));
     *
     *  // disable 1 through 10, and then 20th, 22nd and 24th
     *  // of January through March for every year
     *  $obj->disabled_date(array('1-10,20,22,24 1-3 *'));
     *
     *  // disable all Saturdays and Sundays
     *  $obj->disabled_date(array('* * * 0,6'));
     *
     *  // disable 1st and 2nd of July 2012,
     *  // and all of August of 2012;
     *  $obj->disabled_date(array('01 07 2012', '02 07 2012', '* 08 2012'));
     *  </code>
     *
     *  @param  array   $disabled_dates     An array of strings representing disabled dates. Values in the string have
     *                                      to be in the following format: "day month year weekday" where "weekday" is
     *                                      optional and can be 0-6 (Saturday to Sunday); The syntax is similar to
     *                                      cron's syntax: the values are separated by spaces and may contain * (asterisk)
     *                                      -&nbsp;(dash) and , (comma) delimiters.
     *
     *                                      Default is FALSE, no disabled dates.
     *
     *  @return void
     */
    function disabled_dates($disabled_dates) {

        // set the date picker's attribute
        $this->set_attributes(array('disabled_dates' => $disabled_dates));

    }

    /**
     *  Week's starting day.
     *
     *  @param  integer $day    Valid values are 0 to 6, Sunday to Saturday.
     *
     *                          Default is 1, Monday.
     *
     *  @return void
     */
    function first_day_of_week($day)
    {

        // set the date picker's attribute
        $this->set_attributes(array('first_day_of_week' => $day));

    }

    /**
     *  Sets the format of the returned date.
     *
     *  @param  string  $format     Format of the returned date.
     *
     *                              Accepts the following characters for date formatting: d, D, j, l, N, w, S, F, m, M,
     *                              n, Y, y borrowing syntax from ({@link http://www.php.net/manual/en/function.date.php
     *                              PHP's date function})
     *
     *                              Note that when setting a date format without days (‘d’, ‘j’), the users will be able
     *                              to select only years and months, and when setting a format without months and days
     *                              (‘F’, ‘m’, ‘M’, ‘n’, ‘t’, ‘d’, ‘j’), the users will be able to select only years.
     *
     *                              Also note that the value of the “view” property (see below) may be overridden if it
     *                              is the case: a value of “days” for the “view” property makes no sense if the date
     *                              format doesn’t allow the selection of days.
     *
     *                              Default format is <b>Y-m-d</b>
     *
     *  @return void
     */
    function format($format) {

        // set the date picker's attribute
        $this->set_attributes(array('format' => $format));

    }

    /**
     *  <b>To be used after the form is submitted!</b>
     *
     *  Returns submitted date in the YYYY-MM-DD format so that it's directly usable with a database engine or with
     *  PHP's {@link http://php.net/manual/en/function.strtotime.php strtotime} function.
     *
     *  @return string  Returns submitted date in the YYYY-MM-DD format, or <b>an empty string</b> if control was
     *                  submitted with no value (empty).
     */
    function get_date()
    {

        $result = $this->get_attributes('date');

        // if control had a value return it, or return an empty string otherwise
        return (isset($result['date'])) ? $result['date'] : '';

    }

    /**
     *  Sets whether the icon for opening the datepicker should be inside or outside the element.
     *
     *  @param  boolean $value      If set to FALSE, the icon will be placed to the right of the parent element, while
     *                              if set to TRUE it will be placed to the right of the parent element, but *inside* the
     *                              element itself.
     *
     *                              Default TRUE.
     *
     *  @return void
     */
    function inside($value) {

        // set the date picker's attribute
        // ("inside" is a "reserved" attribute so we'll pick something else)
        $this->set_attributes(array('inside_icon' => $value));


    }

    /**
     *  Sets the offset, in pixels (x, y), to shift the date picker’s position relative to the top-left of the icon that
     *  toggles the date picker.
     *
     *  @param  array  $value       An array indicating the offset, in pixels (x, y), to shift the date picker’s position
     *                              relative to the top-left of the icon that toggles the date picker.
     *
     *                              Default is array(20, -5).
     *
     *  @return void
     */
    function offset($value) {

        // set the date picker's attribute
        $this->set_attributes(array('offset' => $value));

    }

    /**
     *  Pairs the date element with another date element from the page, so that the other date element will use the current
     *  date element’s value as starting date.
     *
     *  <code>
     *  // let's assume this will be the starting date
     *  $date1 = $form->add('date', 'starting_date');
     *
     *  // dates are selectable in the future, starting with today
     *  $date1->direction(true);
     *
     *  // indicate another date element that will use this
     *  // element's value as starting date
     *  $date1->pair('ending_date');
     *
     *  // the other date element
     *  $date2 = $form->add('date', 'ending_date');
     *
     *  // start one day after the reference date
     *  // (that is, one day after whaterver is selected in the first element)
     *  $date2->direction(1);   
     *  </code>
     *
     *  @param  string  $value      The ID of another "date" element which will use the current date element's value as
     *                              starting date.
     *
     *                              Note that the rules set in the “direction” property will still apply, only that the
     *                              reference date will not be the current system date but the value selected in the
     *                              current date picker.
     *
     *                              Default is FALSE (not paired with another date picker)
     *
     *  @return void
     */
    function pair($value) {

        // set the date picker's attribute
        $this->set_attributes(array('pair' => $value));

    }

    /**
     *  Sets whether the element the calendar is attached to should be read-only.
     *
     *  @param  boolean $value      The setting's value
     *
     *                              If set to TRUE, a date can be set only through the date picker and cannot be enetered
     *                              manually.
     *
     *                              Default is TRUE.
     *
     *  @return void
     */
    function readonly_element($value) {

        // set the date picker's attribute
        $this->set_attributes(array('readonly_element' => $value));

    }

    /**
     *  Sets whether an extra column should be shown, showing the number of each week.
     *
     *  @param  string  $value      Anything other than FALSE will enable this feature, and use the given value as column
     *                              title. For example, show_week_number: ‘Wk’ would enable this feature and have “Wk” as
     *                              the column’s title.
     *
     *                              Default is FALSE.
     *
     *  @return void
     */
    function show_week_number($value) {

        // set the date picker's attribute
        $this->set_attributes(array('show_week_number' => $value));

    }

    /**
     *  Sets a default date to start the date picker with.
     *
     *  @param  date    $value      A default date to start the date picker with,
     *
     *                              Must be specified in the format defined by the “format” property, or it will be
     *                              ignored!
     *
     *                              Note that this value is used only if there is no value in the field the date picker
     *                              is attached to!
     *
     *                              Default is FALSE.
     *
     *  @return void
     */
    function start_date($value) {

        // set the date picker's attribute
        $this->set_attributes(array('start_date' => $value));

    }

    /**
     *  Sets how should the date picker start.
     *
     *  @param  string  $view       How should the date picker start.
     *
     *                              Valid values are "days", "months" and "years".
     *
     *                              Note that the date picker is always cycling days-months-years when clicking in the
     *                              date picker's header, and years-months-days when selecting dates (unless one or more
     *                              of the views are missing due to the date's format)
     *
     *                              Also note that the value of the "view" property may be overridden if the date's format
     *                              requires so! (i.e. "days" for the "view" property makes no sense if the date format
     *                              doesn't allow the selection of days)
     *
     *                              Default is "days".
     *
     *  @return void
     */
    function view($view) {

        // set the date picker's attribute
        $this->set_attributes(array('view' => $view));

    }

    /**
     *  Sets the days of the week that are to be considered  as "weekend days".
     *
     *  @param  array   $days       An array of days of the week that are to be considered  as "weekend days".
     *
     *                              Valid values are 0 to 6 (Sunday to Saturday)
     *
     *                              Default is array(0,6) (Saturday and Sunday).
     *
     *  @return void
     */
    function weekend_days($days) {

        // set the date picker's attribute
        $this->set_attributes(array('weekend_days' => $days));

    }

    function getJSOptions()
    {

        $this->attributes['days'] = $this->form_properties['language']['days'];

        $this->attributes['months'] = $this->form_properties['language']['months'];

        $properties = '';

        // iterate through control's attributes
        foreach ($this->attributes as $attribute => $value) {

            // if attribute is an attribute intended for the javascript object and is not null
            if (in_array($attribute, $this->javascript_attributes) && ($this->attributes[$attribute] !== null || ($attribute == 'direction' && $value === false))) {

                // append to the properties list (we change "inside_icon" to "inside" as "inside" is reserved)
                $properties .= ($properties != '' ? ',' : '') . ($attribute == 'inside_icon' ? 'inside' : $attribute) . ':';

                // if value is an array
                if (is_array($value)) {

                    // format accordingly
                    $properties .= '[';

                    foreach ($value as $val)

                        $properties .= ($val === true ? 'true' : ($val === false ? 'false' : '\'' . $val . '\'')) . ',';

                    $properties = rtrim($properties, ',') . ']';

                // if value is a string but is not a javascript object
                } elseif (is_string($value) && !preg_match('/^\{.*\}$/', trim($value)))

                    // format accordingly
                    $properties .= '\'' . $value . '\'';

                // for any other case (javascript object, boolean)
                else

                    // format accordingly
                    $properties .= ($value === true ? 'true' : ($value === false ? 'false' : $value));

            }

        }

        // wrap up the javascript object
        return ($properties != '' ? '{' . $properties . '}' : '');

    }
    /**
     *  Generates the control's HTML code.
     *
     *  <i>This method is automatically called by the {@link MyZebra_Form::render() render()} method!</i>
     *
     *  @return string  The control's HTML code
     */
    function _toHTML()
    {

        // all date controls must have the "date" rule set or we trigger an error
       // if (!isset($this->rules['date'])) _myzebra_form_show_error('The control named <strong>"' . $this->attributes['name'] . '"</strong> in form <strong>"' . $this->form_properties['name'] . '"</strong> must have the <em>"date"</em> rule set', E_USER_ERROR);

        $_atts=$this->attributes;  // backup
        $this->attributes['value']=stripslashes($this->attributes['value']);
        $output= '
            <span class="myzebra-date-container">
                <input ' . $this->_render_attributes() . ($this->form_properties['doctype'] == 'xhtml' ? '/' : '') . '>
            </span>
        ';
        $this->attributes=$_atts;
        return $output;

    }

}

?>
