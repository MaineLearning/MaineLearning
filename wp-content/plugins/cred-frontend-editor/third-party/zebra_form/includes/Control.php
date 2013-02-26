<?php

/**
 *  A generic class containing common methods, shared by all the controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Generic
 */
class MyZebra_Form_Control extends XSS_Clean
{

    // container into which this control is embedded
    var $_parent=null;
    
    var $_is_container=false;
    
    var $_isDiscarded=false;
    
    var $custom_valid=true;
    
    var $prime_name=null;
    
    var $escape = false;
    
    var $__pattern = array(
        'hostname' => '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)'
    );
    /**
     *  Array of HTML attributes of the element
     *
     *  @var array
     *
     *  @access private
     */
    var $attributes;

    /**
     *  Array of HTML attributes that the control's {@link render_attributes()} method should skip
     *
     *  @var array
     *
     *  @access private
     */
    var $private_attributes;

    /**
     *  Array of validation rules set for the control
     *
     *  @var array
     *
     *  @access private
     */
    var $rules;

    /**
     *  Constructor of the class
     *
     *  @return void
     *
     *  @access private
     */
     
     var $actions=array();
     
     var $valid=true;
     
    function MyZebra_Form_Control()
    {

        $this->attributes = array(

            'locked' => false,
            'disable_xss_filters' => false,

        );

        $this->private_attributes = array();

        $this->rules = array();
        $this->_parent=null;
        $this->_isDiscarded=false;
    }
    
    function isContainer()
    {
        return $this->_is_container;
    }
    
    function setParent(&$cont)
    {
        $this->_parent=$cont;
    }
    
    function getParent()
    {
        return $this->_parent;
    }
    
    function setPrimeName($name)
    {
        $this->prime_name=$name;
    }
    
    function getPrimeName()
    {
        return $this->prime_name;
    }
    
    /**
     *  Call this method to instruct the script to force all letters typed by the user, to either uppercase or lowercase,
     *  in real-time.
     *
     *  Works only on {@link MyZebra_Form_Text text}, {@link MyZebra_Form_Textarea textarea} and
     *  {@link MyZebra_Form_Password password} controls.
     *
     *  <code>
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // add a text control to the form
     *  // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
     *  // for PHP 5+ there is no need for it
     *  $obj = &$form->add('text', 'my_text');
     *
     *  // entered characters will be upper-case
     *  $obj->change_case('upper');
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
     *  @param  string  $case   The case to convert all entered characters to.
     *
     *                          Can be (case-insensitive) "upper" or "lower".
     *
     *  @since  2.8
     *
     *  @return void
     */
    function change_case($case)
    {

        // make sure the argument is lowercase
        $case = strtolower($case);

        // if valid case specified
        if ($case == 'upper' || $case == 'lower')

            // add an extra class to the element
            $this->set_attributes(array('class' => 'myzebra-modifier-' . $case . 'case'), false);

    }

    /**
     *  Disables the SPAM filter for the control.
     *
     *  By default, for checkboxes, radio buttons and select boxes, the library will prevent the submission of other
     *  values than those declared when creating the form, by triggering the error: "SPAM attempt detected!". Therefore,
     *  if you plan on adding/removing values dynamically, from JavaScript, you will have to call this method to prevent
     *  that from happening.
     *
     *  Works only for {@link MyZebra_Form_Checkbox checkbox}, {@link MyZebra_Form_Radio radio} and
     *  {@link MyZebra_Form_Select select} controls.
     *
     *  @return void
     */
    function disable_spam_filter()
    {

        // set the "disable_xss_filters" private attribute of the control
        $this->set_attributes(array('disable_spam_filter' => true));

    }

    /**
     *  Disables XSS filtering of the control's submitted value.
     *
     *  By default, all submitted values are filtered for XSS (Cross Site Scripting) injections. The script will
     *  automatically remove possibly malicious content (event handlers, javascript code, etc). While in general this is
     *  the right thing to do, there may be the case where this behaviour is not wanted: for example, for a CMS where
     *  the WYSIWYG editor inserts JavaScript code.
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->disable_xss_filters();
     *  </code>
     *
     *  @return void
     */
    function disable_xss_filters()
    {

        // set the "disable_xss_filters" private attribute of the control
        $this->set_attributes(array('disable_xss_filters' => true));

    }
    
    // provide additional XSS validation/sanitization methods
    function extra_xss($context, $data)
    {
        return $data; // disable extra escaping
        if (!$this->escape)  return $data;
        
        $extra_xss=$this->form->getExtraXSS();
        if ($extra_xss && isset($extra_xss[$context]) && is_callable($extra_xss[$context]))
        {
            return call_user_func($extra_xss[$context], $data);
        }
        return $data;
    }
    
    /**
     *  Returns the values of requested attributes.
     *
     *  <code>
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // add a text field to the form
     *  $obj = &$form->add('text', 'my_text');
     *
     *  // set some attributes for the text field
     *  $obj->set_attributes(array(
     *      'readonly'  => 'readonly',
     *      'style'     => 'font-size:20px',
     *  ));
     *
     *  // retrieve the attributes
     *  $attributes = $obj->get_attributes(array('readonly', 'style'));
     *
     *  // the result will be an associative array
     *  //
     *  // $attributes = Array(
     *  //      [readonly]  => "readonly",
     *  //      [style]     => "font-size:20px"
     *  // )
     *  </code>
     *
     *  @param  mixed   $attributes     A single or an array of attributes for which the values to be returned.
     *
     *  @return array                   Returns an associative array where keys are the attributes and the values are
     *                                  each attribute's value, respectively.
     */
    function get_attributes($attributes)
    {

        // initialize the array that will be returned
        $result = array();

        // if the request was for a single attribute,
        // treat it as an array of attributes
        if (!is_array($attributes)) $attributes = array($attributes);

        // iterate through the array of attributes to look for
        foreach ($attributes as $attribute)

            // if attribute exists
            if (array_key_exists($attribute, $this->attributes))

                // populate the $result array
                $result[$attribute] = $this->attributes[$attribute];

        // return the results
        return $result;

    }

    /**
     *  Returns the control's value <b>after</b> the form is submitted.
     *
     *  <i>This method is automatically called by the form's {@link MyZebra_Form::validate() validate()} method!</i>
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->get_submitted_value();
     *  </code>
     *
     *  @return void
     */
    function get_submitted_value()
    {
        return $this->_get_submitted_value();
    }
    
    function _get_submitted_value()
    {

        // get some attributes of the control
        $attribute = $this->get_attributes(array('name', 'type', 'value', 'disable_xss_filters', 'locked'));

        // if control's value is not locked to the default value
        if ($attribute['locked'] !== true) {

            // strip any [] from the control's name (usually used in conjunction with multi-select select boxes and
            // checkboxes)
            $attribute['name'] = preg_replace('/\[.*\]/', '', $attribute['name']);

            // reference to the form submission method
            global ${'_' . $this->form_properties['method']};

            $method = & ${'_' . $this->form_properties['method']};

            // if form was submitted
            if (

                isset($method[$this->form_properties['identifier']]) &&

                $method[$this->form_properties['identifier']] == $this->form_properties['name']

            ) {

                // if control is a time picker control
                if ($attribute['type'] == 'time') {

                    // combine hour, minutes and seconds into one single string (values separated by :)
                    // hours
                    $combined = (isset($method[$attribute['name'] . '_hours']) ? $method[$attribute['name'] . '_hours'] : '');
                    // minutes
                    $combined .= (isset($method[$attribute['name'] . '_minutes']) ? ($combined != '' ? ':' : '') . $method[$attribute['name'] . '_minutes'] : '');
                    // seconds
                    $combined .= (isset($method[$attribute['name'] . '_seconds']) ? ($combined != '' ? ':' : '') . $method[$attribute['name'] . '_seconds'] : '');
                    // AM/PM
                    $combined .= (isset($method[$attribute['name'] . '_ampm']) ? ($combined != '' ? ' ' : '') . $method[$attribute['name'] . '_ampm'] : '');

                    // create a super global having the name of our time picker control
                    // (remember, we don't have a control with the time picker's control name but three other controls
                    // having the time picker's control name as prefix and _hours, _minutes and _seconds respectively
                    // as suffix)
                    // we need to do this so that the values will also be filtered for XSS injection
                    $method[$attribute['name']] = $combined;

                    // unset the three temporary fields as we want to return to the user the result in a single field
                    // having the name he supplied
                    unset($method[$attribute['name'] . '_hours']);
                    unset($method[$attribute['name'] . '_minutes']);
                    unset($method[$attribute['name'] . '_seconds']);
                    unset($method[$attribute['name'] . '_ampm']);

                }

                // if control is a file upload control and a file was indeed uploaded
                if ($attribute['type'] == 'file' && (isset($_FILES[$attribute['name']]) || isset($method[$attribute['name']])))
               {
                    if (isset($_FILES[$attribute['name']]))
                        $this->file_data=array($attribute['name']=>$_FILES[$attribute['name']]);
                    else
                        $this->file_data=false;
                    $this->submitted_value = true; // if control was submitted
                    if (isset($method[$attribute['name']]))  // text field
                    {
                        $this->text_field->submitted_value=$method[$attribute['name']];
                        $this->text_field->set_attributes(array('value' => $this->text_field->submitted_value));
                    }
                    else
                        $this->submitted_value = false; // if control was submitted
                }
                elseif ($attribute['type'] == 'skype')
                {
                    // create the submitted_value property for the control and
                    // assign to it the submitted value of the control
                    
                    // and also, if magic_quotes_gpc is on (meaning that both
                    // single and double quotes are escaped)
                    // strip those slashes
                    if (isset($method[$attribute['name']]) && array_key_exists('skypename',$method[$attribute['name']]) && array_key_exists('style',$method[$attribute['name']]))
                    {
                        $this->submitted_value = $method[$attribute['name']];
                        if (get_magic_quotes_gpc()) 
                        {
                            $this->submitted_value['skypename'] = stripslashes($this->submitted_value['skypename']);
                            $this->submitted_value['style'] = stripslashes($this->submitted_value['style']);
                        }
                    }
                    else
                        $this->submitted_value=false;
                }
                elseif (isset($method[$attribute['name']])) {

                    // create the submitted_value property for the control and
                    // assign to it the submitted value of the control
                    $this->submitted_value = $method[$attribute['name']];

                    // if submitted value is an array
                    if (is_array($this->submitted_value)) {

                        // iterate through the submitted values
                        foreach ($this->submitted_value as $key => $value)

                            // and also, if magic_quotes_gpc is on (meaning that
                            // both single and double quotes are escaped)
                            // strip those slashes
                            if (get_magic_quotes_gpc()) $this->submitted_value[$key] = stripslashes($value);

                    // if submitted value is not an array
                    } else

                        // and also, if magic_quotes_gpc is on (meaning that both
                        // single and double quotes are escaped)
                        // strip those slashes
                        if (get_magic_quotes_gpc()) $this->submitted_value = stripslashes($this->submitted_value);

                    // since 1.1
                    // if XSS filtering is not disabled
                    if ($attribute['disable_xss_filters'] !== true) {

                        // if submitted value is an array
                        if (is_array($this->submitted_value))

                            // iterate through the submitted values
                            foreach ($this->submitted_value as $key => $value)

                                // filter the control's value for XSS injection
                                $this->submitted_value[$key] = htmlspecialchars($this->sanitize($value));

                        // if submitted value is not an array, filter the control's value for XSS injection
                        else $this->submitted_value = htmlspecialchars($this->sanitize($this->submitted_value));

                        // set the respective $_POST/$_GET value to the filtered value
                        $method[$attribute['name']] = $this->submitted_value;

                    }

                } 

                // if control was not submitted
                // we set this for those controls that are not submitted even
                // when the form they reside in is (i.e. unchecked checkboxes)
                // so that we know that they were indeed submitted but they
                // just don't have a value
                else $this->submitted_value = false;

                if (

                    //if type is password, textarea or text OR
                    ($attribute['type'] == 'password' || $attribute['type'] == 'textarea' || $attribute['type'] == 'text') &&
                    
                    isset($this->attributes['class']) &&
                    // control has the "uppercase" or "lowercase" modifier set
                    preg_match('/\bmyzebra\-modifier\-uppercase\b|\bmyzebra\-modifier\-lowercase\b/i', $this->attributes['class'], $modifiers)

                ) {

                    // if string must be uppercase, update the value accordingly
                    if ($modifiers[0] == 'myzebra-modifier-uppercase') $this->submitted_value = strtoupper($this->submitted_value);

                    // otherwise, string needs to be lowercase
                    else $this->submitted_value = strtolower($this->submitted_value);

                    // set the respective $_POST/$_GET value to the updated value
                    $method[$attribute['name']] = $this->submitted_value;

                }

            }

            // if control was submitted
            if (isset($this->submitted_value)) {

                // the assignment of the submitted value is type dependant
                switch ($attribute['type']) {

                    // if control is a checkbox
                    case 'skype':
                        $this->attributes['value']=$this->submitted_value;
                        break;
                    case 'checkbox':
                        //print_r($this->submitted_value);
                        if (isset($this->checkboxes))
                        {
                            foreach ($this->checkboxes as &$checkbox)
                            {
                                if (
                                (
                                    // if is submitted value is an array
                                    is_array($this->submitted_value) &&

                                    // and the checkbox's value is in the array
                                    in_array($checkbox->attributes['value'], $this->submitted_value)

                                    // OR
                                    ) ||

                                    // assume submitted value is not an array and the
                                    // checkbox's value is the same as the submitted value
                                    $checkbox->attributes['value'] == $this->submitted_value

                                // set the "checked" attribute of the control
                                )
                                {
                                    $checkbox->set_attributes(array('checked' => 'checked'));
                                }
                                elseif (isset($checkbox->attributes['checked'])) unset($checkbox->attributes['checked']);
                                
                                //print_r($checkbox->attributes['value'].' '.$checkbox->attributes['checked']);
                            }
                        }
                        else
                        {
                            if (

                                (

                                    // if is submitted value is an array
                                    is_array($this->submitted_value) &&

                                    // and the checkbox's value is in the array
                                    in_array($attribute['value'], $this->submitted_value)

                                // OR
                                ) ||

                                // assume submitted value is not an array and the
                                // checkbox's value is the same as the submitted value
                                $attribute['value'] == $this->submitted_value

                            // set the "checked" attribute of the control
                            ) $this->set_attributes(array('checked' => 'checked'));

                            // if checkbox was "submitted" as not checked
                            // and if control's default state is checked, uncheck it
                            elseif (isset($this->attributes['checked'])) unset($this->attributes['checked']);
                        }
                        break;

                    // if control is a radio button
                    case 'radio':

                        if (isset($this->radios))
                        {
                            foreach ($this->radios as &$radio)
                            {
                                if ($radio->attributes['value']==$this->submitted_value)
                                {
                                    $radio->set_attributes(array('checked' => 'checked'));
                                    //break;
                                }
                                elseif (isset($radio->attributes['checked'])) unset($radio->attributes['checked']);
                            }
                        }
                        else
                        {
                            if (

                                // if the radio button's value is the same as the
                                // submitted value
                                ($attribute['value'] == $this->submitted_value)

                            // set the "checked" attribute of the control
                            ) $this->set_attributes(array('checked' => 'checked'));
                            
                            elseif (isset($this->attributes['checked'])) unset($this->attributes['checked']);
                        }
                        break;

                    // if control is a select box
                    case 'select':

                        // set the "value" private attribute of the control
                        // the attribute will be handled by the
                        // MyZebra_Form_Select::_render_attributes() method
                        $this->set_attributes(array('value' => $this->submitted_value));

                        break;

                    // if control is a file upload control, a hidden control, a password field, a text field or a textarea control
                    case 'file':
                    case 'hidden':
                    case 'password':
                    case 'text':
                    case 'textarea':
                    case 'time':

                        // set the "value" standard HTML attribute of the control
                        $this->set_attributes(array('value' => $this->submitted_value));

                        break;

                }

            }

        }

    }

    /**
     *  Locks the control's value. A <i>locked</i> control will preserve its default value after the form is submitted
     *  even if the user altered it.
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->lock();
     *  </code>
     *
     *  @return void
     */
    function lock() {

        // set the "locked" private attribute of the control
        $this->set_attributes(array('locked' => true));

    }

    /**
     *  Resets the control's submitted value (empties text fields, unchecks radio buttons/checkboxes, etc).
     *
     *  <i>This method also resets the associated POST/GET/FILES superglobals!</i>
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->reset();
     *  </code>
     *
     *  @return void
     */
    function reset()
    {

        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};

        // get some attributes of the control
        $attributes = $this->get_attributes(array('type', 'name', 'other'));

        // sanitize the control's name
        $attributes['name'] = preg_replace('/\[.*\]/', '', $attributes['name']);

        // see of what type is the current control
        switch ($attributes['type']) {

            // control is any of the types below
            case 'skype':
                $this->attributes['value']=array();
                // unset the associated superglobal
                unset($method[$attributes['name']]);
                break;
            case 'checkbox':
            case 'radio':

                // unset the "checked" attribute
                unset($this->attributes['checked']);

                // unset the associated superglobal
                unset($method[$attributes['name']]);

                break;

            // control is any of the types below
            case 'date':
            case 'hidden':
            case 'password':
            case 'select':
            case 'text':
            case 'textarea':

                // simply empty the "value" attribute
                $this->attributes['value'] = '';

                // unset the associated superglobal
                unset($method[$attributes['name']]);

                // if control has the "other" attribute set
                if (isset($attributes['other']))

                    // clear the associated superglobal's value
                    unset($method[$attributes['name'] . '_other']);

                break;

            // control is a file upload control
            case 'file':

                // unset the related superglobal
                unset($_FILES[$attributes['name']]);
                $this->attributes['value'] = '';
                if (isset($this->text_field))
                    $this->text_field->reset();
                
                break;

            // for any other control types
            default:

                // as long as control is not label, note nor captcha
                if (

                    $attributes['type'] != 'label' &&
                    $attributes['type'] != 'note' &&
                    $attributes['type'] != 'captcha'

                // unset the associated superglobal
                ) unset($method[$attributes['name']]);

        }

    }

    /**
     *  Sets one or more of the control's attributes.
     *
     *  <code>
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // add a text field to the form
     *  $obj = &$form->add('text', 'my_text');
     *
     *  // set some attributes for the text field
     *  $obj->set_attributes(array(
     *      'readonly'  => 'readonly',
     *      'style'     => 'font-size:20px',
     *  ));
     *
     *  // retrieve the attributes
     *  $attributes = $obj->get_attributes(array('readonly', 'style'));
     *
     *  // the result will be an associative array
     *  //
     *  // $attributes = Array(
     *  //      [readonly]  => "readonly",
     *  //      [style]     => "font-size:20px"
     *  // )
     *  </code>
     *
     *  @param  array       $attributes     An associative array, in the form of <i>attribute => value</i>.
     *
     *  @param  boolean     $overwrite      Setting this argument to FALSE will instruct the script to append the values
     *                                      of the attributes to the already existing ones (if any) rather then overwriting
     *                                      them.
     *
     *                                      Useful, for adding an extra CSS class to the already existing ones.
     *
     *                                      For example, the {@link MyZebra_Form_Text text} control has, by default, the
     *                                      <b>class</b> attribute set and already containing some classes needed both
     *                                      for styling and for JavaScript functionality. If there's the need to add one
     *                                      more class to the existing ones, without breaking styles nor functionality,
     *                                      one would use:
     *
     *                                      <code>
     *                                          // obj is a reference to a control
     *                                          $obj->set_attributes(array('class'=>'my_class'), false);
     *                                      </code>
     *
     *                                      Default is TRUE
     *
     *  @return void
     */
    function set_attributes($attributes, $overwrite = true)
    {

        // check if $attributes is given as an array
        if (is_array($attributes))

            // iterate through the given attributes array
            foreach ($attributes as $attribute => $value)

                // if the value is to be appended to the already existing one
                // and there is a value set for the specified attribute
                // and the values do not represent an array
                if (!$overwrite && isset($this->attributes[$attribute]) && !is_array($this->attributes[$attribute]))
                {
                    // append the value
                    $this->attributes[$attribute] = $this->attributes[$attribute] . ' ' . $value;
                    // prevent duplicates
                    $tmp=$this->attributes[$attribute];
                    $tmp= implode(' ',array_unique(explode(' ', $tmp)));
                    $this->attributes[$attribute]=$tmp;
                }
                // otherwise, add attribute to attributes array
                else $this->attributes[$attribute] = $value;

    }

    /**
     *  Sets a single or an array of validation rules for the control.
     *
     *  <code>
     *      // $obj is a reference to a control
     *      $obj->set_rule(array(
     *          'rule #1'    =>  array($arg1, $arg2, ... $argn),
     *          'rule #2'    =>  array($arg1, $arg2, ... $argn),
     *          ...
     *          ...
     *          'rule #n'    =>  array($arg1, $arg2, ... $argn),
     *      ));
     *      // where 'rule #1', 'rule #2', 'rule #n' are any of the rules listed below
     *      // and $arg1, $arg2, $argn are arguments specific to each rule
     *  </code>
     *
     *  When a validation rule is not passed, a variable becomes available in the template file, having the name
     *  as specified by the rule's <b>error_block</b> argument and having the value as specified by the rule's
     *  <b>error_message</b> argument.
     *
     *  <samp>Validation rules are checked in the given order. The exceptions are the "required" rule which is *always*
     *  checked first, and the "upload" rule which is *always* checked first if there's no "required" rule, or second if the
     *  "required" rule exists.</samp>
     *
     *  I usually have at the top of my templates something like (assuming all errors are sent to an error block named
     *  "error"):
     *
     *  <code>
     *  echo (isset($error) ? $error : '');
     *  </code>
     *
     *  One or all error messages can be displayed in an error block.
     *  See the {@link MyZebra_Form::show_all_error_messages() show_all_error_messages()} method.
     *
     *  <b>Everything related to error blocks applies only for server-side validation.</b><br>
     *  <b>See the {@link MyZebra_Form::client_side_validation() client_side_validation()} method for configuring how errors
     *  are to be displayed to the user upon client-side validation.</b>
     *
     *  Available rules are
     *  -   alphabet
     *  -   alphanumeric
     *  -   captcha
     *  -   compare
     *  -   convert
     *  -   custom
     *  -   date
     *  -   datecompare
     *  -   digits
     *  -   email
     *  -   emails
     *  -   filesize
     *  -   filetype
     *  -   float
     *  -   image
     *  -   length
     *  -   number
     *  -   regexp
     *  -   required
     *  -   resize
     *  -   upload
     *  -   max-width
     *  -   max-height
     *  -   url
     *
     *  Rules description:
     *
     *  -   <b>alphabet</b>
     *
     *  <code>'alphabet' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides the alphabet (provide
     *      an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only characters from the alphabet (case-insensitive a to z) <b>plus</b> characters
     *  given as additional characters (if any).
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'alphabet' => array(
     *          '-'                                     // allow alphabet plus dash
     *          'error',                                // variable to add the error message to
     *          'Only alphabetic characters allowed!'   // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>alphanumeric</b>
     *
     *  <code>'alphanumeric' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides the alphabet and
     *      digits 0 to 9 (provide an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only characters from the alphabet (case-insensitive a to z) and digits (0 to 9)
     *  <b>plus</b> characters given as additional characters (if any).
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'alphanumeric' => array(
     *          '-'                                     // allow alphabet, digits and dash
     *          'error',                                // variable to add the error message to
     *          'Only alphanumeric characters allowed!' // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>captcha</b>
     *
     *  <code>'captcha' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value matches the characters seen in the {@link MyZebra_Form_Captcha captcha} image
     *  (therefore, there must be a {@link MyZebra_Form_Captcha captcha} image on the form)
     *
     *  Available only for the {@link MyZebra_Form_Text text} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'captcha' => array(
     *          'error',                            // variable to add the error message to
     *          'Characters not entered correctly!' // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>compare</b>
     *
     *  <code>'compare' => array($control, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>control</i> is the name of a control on the form to compare values with
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value is the same as the value of the control indicated by <i>control</i>.
     *
     *  Useful for password confirmation.
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'compare' => array(
     *          'password'                          // name of the control to compare values with
     *          'error',                            // variable to add the error message to
     *          'Password not confirmed correctly!' // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>convert</b>
     *
     *  <samp>This rule requires the prior inclusion of the {@link http://stefangabos.ro/php-libraries/zebra-image MyZebra_Image}
     *  library!</samp>
     *
     *  <code>'convert' => array($type, $jpeg_quality, $preserve_original_file, $overwrite, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>type</i> the type to convert the image to; can be (case-insensitive) JPG, PNG or GIF
     *
     *  -   <i>jpeg_quality</i>: Indicates the quality of the output image (better quality means bigger file size).
     *
     *      Range is 0 - 100
     *
     *      Available only if <b>type</b> is "jpg".
     *
     *  -   <i>preserve_original_file</i>: Should the original file be preserved after the conversion is done?
     *
     *  -   <i>$overwrite</i>: If a file with the same name as the converted file already exists, should it be
     *      overwritten or should the name be automatically computed.
     *
     *      If a file with the same name as the converted file already exists and this argument is FALSE, a suffix of
     *      "_n" (where n is an integer) will be appended to the file name.
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  This rule will convert an image file uploaded using the <b>upload</b> rule from whatever its type (as long as is one
     *  of the supported types) to the type indicated by <i>type</i>.
     *
     *  Validates if the uploaded file is an image file and <i>type</i> is valid.
     *
     *  This is not actually a "rule", but because it can generate an error message it is included here
     *
     *  You should use this rule in conjunction with the <b>upload</b> and <b>image</b> rules.
     *
     *  If you are also using the <b>resize</b> rule, make sure you are using it AFTER the <b>convert</b> rule!
     *
     *  Available only for the {@link MyZebra_Form_File file} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  // $obj is a reference to a file upload control
     *  $obj->set_rule(
     *       'convert' => array(
     *          'jpg',                          // type to convert to
     *          85,                             // converted file quality
     *          false,                          // preserve original file?
     *          false,                          // overwrite if converted file already exists?
     *          'error',                        // variable to add the error message to
     *          'File could not be uploaded!'   // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>custom</b>
     *
     *  Using this rule, custom rules can be applied to the submitted values.
     *
     *  <code>'custom'=>array($callback_function_name, [optional arguments to be passed to the function], $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>callback_function_name</i> is the name of the callback function
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  <i>The callback function's first argument must ALWAYS be the control's submitted value. The optional arguments to
     *  be passed to the callback function will start as of the second argument!</i>
     *
     *  <i>The callback function MUST return TRUE on success or FALSE on failure!</i>
     *
     *  Multiple custom rules can also be set through an array of callback functions:
     *
     *  <code>
     *  'custom' => array(
     *
     *      array($callback_function_name1, [optional arguments to be passed to the function], $error_block, $error_message),
     *      array($callback_function_name1, [optional arguments to be passed to the function], $error_block, $error_message)
     *
     *  )
     *  </code>
     *
     *  <b>If {@link MyZebra_Form::client_side_validation() client-side validation} is enabled (enabled by default), the
     *  custom function needs to also be available in JavaScript, with the exact same name as the function in PHP!</b>
     *
     *  For example, here's a custom rule for checking that an entered value is an integer, greater than 21:
     *
     *  <code>
     *  // the custom function in JavaScript
     *  <script type="text/javascript">
     *      function is_valid_number(value)
     *      {
     *          // return false if the value is less than 21
     *          if (value < 21) return false;
     *          // return true otherwise
     *          return true;
     *      }
     *  <&92;script>
     *  </code>
     *
     *  <code>
     *  // the callback function in PHP
     *  function is_valid_number($value)
     *  {
     *      // return false if the value is less than 21
     *      if ($value < 21) return false;
     *      // return true otherwise
     *      return true;
     *  }
     *
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // add a text control to the form
     *  // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
     *  // for PHP 5+ there is no need for it
     *  $obj = &$form->add('text', 'my_text');
     *
     *  // set two rules:
     *  // on that requires the value to be an integer
     *  // and a custom rule that requires the value to be greater than 21
     *  $obj->set_rule(
     *      'number'    =>  array('', 'error', 'Value must be an integer!'),
     *      'custom'    =>  array(
     *          'is_valid_number',
     *          'error',
     *          'Value must be greater than 21!'
     *      )
     *  );
     *  </code>
     *
     *  And here's how I do validations using <b>AJAX</b>:
     *
     *  In my website's main JavaScript file I have something like:
     *
     *  <code>
     *  var valid = null;
     *
     *  // I have functions like these for everything that I need checked through AJAX; note that they are in the global
     *  // namespace and outside the DOM-ready event
     *
     *  // functions have to return TRUE in order for the rule to be considered as obeyed
     *  function username_not_taken(username) {
     *      $.ajax({data: 'username=' + username});
     *      return valid;
     *  }
     *
     *  function emailaddress_not_taken(email) {
     *      $.ajax({data: 'email=' + email});
     *      return valid;
     *  }
     *
     *  // in the DOM ready event
     *  $(document).ready(function() {
     *
     *      // I setup an AJAX object that will handle all my AJAX calls
     *      $.ajaxSetup({
     *          url: 'path/to/validator/',  // actual work will be done in PHP
     *          type: 'post',
     *          dataType: 'text',
     *          async: false,               // this is important!
     *          global: false,
     *          beforeSend: function() {
     *              valid = null;
     *          },
     *          success: function(data, textStatus) {
     *              if (data == 'valid') valid = true;
     *              else valid = false;
     *          }
     *      });
     *
     *      // ...other JavaScript code for your website...
     *
     *  }
     *  </code>
     *
     *  I also have a "validation.php" "helper" file which contains the PHP functions that do the actual checkings. This
     *  file is included both in the page where I create the form (used by the server-side validation) and also by the
     *  file defined by the "url" property of the AJAX object (used for client-side validation). This might look something
     *  like:
     *
     *  <code>
     *  function username_not_taken($username) {
     *      // check for username and return TRUE if it's NOT taken, or FALSE otherwise
     *  }
     *
     *  function emailaddress_not_taken($email) {
     *      // check for email address and return TRUE if it's NOT taken, or FALSE otherwise
     *  }
     *  </code>
     *
     *  As stated above, when I create a form I include this "helper" file at the top, because the functions in it will
     *  be used by the server-side validation, and set the custom rules like this:
     *
     *  <code>
     *  $obj->set_rule(array(
     *      'custom'  =>  array(
     *          'username_not_taken',
     *          'error',
     *          'This user name is already taken!'
     *      ),
     *  ));
     *  </code>
     *
     *  ...and finally, at the "url" set in the AJAX object, I have something like:
     *
     *  <code>
     *  // include the "helper" file
     *  require 'path/to/validation.php';
     *
     *  if (
     *
     *      // make sure it's an AJAX request
     *      isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
     *
     *      // make sure it has a referrer
     *      isset($_SERVER['HTTP_REFERER']) &&
     *
     *      // make sure it comes from your website
     *      strpos($_SERVER['HTTP_REFERER'], 'your/website/base/url') === 0
     *
     *  ) {
     *
     *      if (
     *
     *          // i run functions depending on what's in the $_POST and also make some extra sanity checks
     *          (isset($_POST['username']) && count($_POST) == 1 && username_not_taken($_POST['username'])) ||
     *          (isset($_POST['email']) && count($_POST) == 1 && emailaddress_not_taken($_POST['email']))
     *
     *      // if whatever I'm checking is OK, I just echo "valid"
     *      // (this will be later used by the AJAX object)
     *      ) echo 'valid';
     *
     *  }
     *
     *  // do nothing for any other case
     *
     *  </code>
     *
     *  -   <b>date</b>
     *
     *  <code>'date' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value is a propper date, formated according to the format set through the
     *  {@link MyZebra_Form_Date::format() format()} method.
     *
     *  Available only for the {@link MyZebra_Form_Date date} control.
     *
     *  <i>Note that the validation is language dependant: if the form's language is other than English and month names
     *  are expected, the script will expect the month names to be given in that particular language, as set in the
     *  language file!</i>
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'date' => array(
     *          'error',        // variable to add the error message to
     *          'Invalid date!' // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>datecompare</b>
     *
     *  <code>'datecompare' => array($control, $comparison_operator, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>control</i> is the name of a date control on the form to compare values with
     *
     *  -   <i>comparison_operator</i> indicates how the value should be, compared to the value of <i>control</i>.<br>
     *      Possible values are <, <=, >, >=
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value satisfies the comparison operator when compared to the other date control's value.
     *
     *  Available only for the {@link MyZebra_Form_Date date} control.
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'datecompare' => array(
     *          'another_date'                      // name of another date control on the form
     *          '>',                                // comparison operator
     *          'error',                            // variable to add the error message to
     *          'Date must be after another_date!'  // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>digits</b>
     *
     *  <code>'digits' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides digits (provide
     *      an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only digits (0 to 9) <b>plus</b> characters given as additional characters (if any).
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'digits' => array(
     *          '-'                         // allow digits and dash
     *          'error',                    // variable to add the error message to
     *          'Only digits are allowed!'  // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>email</b>
     *
     *  <code>'email' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value is a properly formatted email address.
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'email' => array(
     *          'error',                    // variable to add the error message to
     *          'Invalid email address!'    // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>emails</b>
     *
     *  <code>'emails' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value is a properly formatted email address <b>or</b> a comma separated list of properly
     *  formatted email addresses.
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'emails' => array(
     *          'error',                        // variable to add the error message to
     *          'Invalid email address(es)!'    // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>filesize</b>
     *
     *  <code>'filesize' => array($file_size, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>file_size</i> is the allowed file size, in bytes
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the size (in bytes) of the uploaded file is not larger than the value (in bytes) specified by
     *  <i>file_size</i>.
     *
     *  <b>Note that $file_size should be lesser or equal to the value of upload_max_filesize set in php.ini!</b>
     *
     *  Available only for the {@link MyZebra_Form_File file} control.
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'filesize' => array(
     *          '102400',                           // maximum allowed file size (in bytes)
     *          'error',                            // variable to add the error message to
     *          'File size must not exceed 100Kb!'  // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>filetype</b>
     *
     *  <b>If you want to check for images use the dedicated "image" rule instead!</b>
     *
     *  <code>'filetype' => array($file_types, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>file_types</i> is a string of comma separated file extensions representing uploadable file types
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  Validates only if the uploaded file's MIME type matches the MIME types associated with the extensions set by
     *  <i>file_types</i> as defined in <i>mimes.json</i> file.
     *
     *  Note that for PHP versions 5.3.0+, compiled with the "php_fileinfo" extension, the uploaded file's mime type is
     *  determined using PHP's {@link http://php.net/manual/en/function.finfo-file.php finfo_file} function; Otherwise,
     *  the library relies on information available in the $_FILES super-global for determining an uploaded file's MIME
     *  type, which, as it turns out, is determined solely by the file's extension, representing a potential security
     *  risk;
     *
     *  Available only for the {@link MyZebra_Form_File file} control.
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'filetype' => array(
     *          'xls, xlsx'                 // allow only EXCEL files to be uploaded
     *          'error',                    // variable to add the error message to
     *          'Not a valid Excel file!'   // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>float</b>
     *
     *  <code>'float' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides digits, one dot and one
     *      minus sign (provide an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only digits (0 to 9) and/or <b>one</b> dot (but not as the very first character)
     *  and/or <b>one</b> minus sign (but only if it is the very first character) <b>plus</b> characters given as
     *  additional characters (if any).
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'float' => array(
     *          ''                  // don't allow any extra characters
     *          'error',            // variable to add the error message to
     *          'Invalid number!'   // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>image</b>
     *
     *  <code>'image' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates only if the uploaded file is a valid GIF, PNG or JPEG image file.
     *
     *  Available only for the {@link MyZebra_Form_File file} control.
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'image' => array(
     *          'error',                                // variable to add the error message to
     *          'Not a valid GIF, PNG or JPEG file!'    // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>length</b>
     *
     *  <code>'length' => array($minimum_length, $maximum_length, $error_block, $error_message, $show_counter)</code>
     *
     *  where
     *
     *  -   <i>minimum_length</i> is the minimum number of characters the values should contain
     *
     *  -   <i>maximum_length</i> is the maximum number of characters the values should contain
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  -   <i>show_counter</i> if set to TRUE, a counter showing the remaining characters will be displayed along with
     *      the element
     *
     *      <i>If you want to change the counter's position, do so by setting margins for the .MyZebra_Character_Counter
     *      class in the zebra_form.css file</i>
     *
     *
     *  Validates only if the number of characters of the value is between $minimum_length and $maximum_length.
     *
     *  If an exact length is needed, set both $minimum_length and $maximum_length to the same value.
     *
     *  Set $maximum_length to 0 (zero) if no upper limit needs to be set for the value's length.
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'length' => array(
     *          3,                                              // minimum length
     *          6,                                              // maximum length
     *          'error',                                        // variable to add the error message to
     *          'Value must have between 3 and 6 characters!'   // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>number</b>
     *
     *  <code>'number' => array($additional_characters, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>additional_characters</i> is a list of additionally allowed characters besides digits and one
     *      minus sign (provide an empty string if none)
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value contains only digits (0 to 9) and/or <b>one</b> minus sign (but only if it is the very
     *  first character) <b>plus</b> characters given as additional characters (if any).
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'number' => array(
     *          ''                  // don't allow any extra characters
     *          'error',            // variable to add the error message to
     *          'Invalid integer!'  // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>regexp</b>
     *
     *  <code>'regexp' => array($regular_expression, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>regular_expression</i> is the regular expression pattern (without delimiters) to be tested on the value
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates if the value satisfies the given regular expression
     *
     *  Available for the following controls: {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Text text},
     *  {@link MyZebra_Form_Textarea textarea}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'regexp' => array(
     *          '^0123'                         // the regular expression
     *          'error',                        // variable to add the error message to
     *          'Value must begin with "0123"'  // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>required</b>
     *
     *  <code>'required' => array($error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *
     *  Validates only if a value exists.
     *
     *  Available for the following controls: {@link MyZebra_Form_Checkbox checkbox}, {@link MyZebra_Form_Date date},
     *  {@link MyZebra_Form_File file}, {@link MyZebra_Form_Password password}, {@link MyZebra_Form_Radio radio},
     *  {@link MyZebra_Form_Select select}, {@link MyZebra_Form_Text text}, {@link MyZebra_Form_Textarea textarea},
     *  {@link MyZebra_Form_Time time}
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'required' => array(
     *          'error',            // variable to add the error message to
     *          'Field is required' // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  -   <b>resize</b>
     *
     *  <samp>This rule requires the prior inclusion of the {@link http://stefangabos.ro/php-libraries/zebra-image MyZebra_Image}
     *  library!</samp>
     *
     *  <code>'resize' => array(
     *      $prefix,
     *      $width,
     *      $height,
     *      $preserve_aspect_ratio,
     *      $method,
     *      $background_color,
     *      $enlarge_smaller_images,
     *      $jpeg_quality,
     *      $error_block,
     *      $error_message,
     *  )
     *  </code>
     *
     *  where
     *
     *  -   <i>prefix</i>: If the resized image is to be saved as a new file and the originally uploaded file needs to be
     *      preserved, specify a prefix to be used for the new file. This way, the resized image will have the same name as
     *      the original file but prefixed with the given value (i.e. "thumb_").
     *
     *      Specifying an empty string as argument will instruct the script to apply the resizing to the uploaded image
     *      and therefore overwriting the originally uploaded file.
     *
     *  -   <i>width</i> is the width to resize the image to.
     *
     *      If set to <b>0</b>, the width will be automatically adjusted, depending on the value of the <b>height</b>
     *      argument so that the image preserves its aspect ratio.
     *
     *      If <b>preserve_aspect_ratio</b> is set to TRUE and both this and the <b>height</b> arguments are values
     *      greater than <b>0</b>, the image will be resized to the exact required width and height and the aspect ratio
     *      will be preserved (see the description for the <b>method</b> argument below on how can this be done).
     *
     *      If <b>preserve_aspect_ratio</b> is set to FALSE, the image will be resized to the required width and the
     *      aspect ratio will be ignored.
     *
     *      If both <b>width</b> and <b>height</b> are set to <b>0</b>, a copy of the source image will be created
     *      (<b>jpeg_quality</b> will still apply).
     *
     *      If either <b>width</b> or <b>height</b> are set to <b>0</b>, the script will consider the value of the
     *      <b>preserve_aspect_ratio</b> to bet set to TRUE regardless of its actual value!
     *
     *  -   <i>height</i> is the height to resize the image to.
     *
     *      If set to <b>0</b>, the height will be automatically adjusted, depending on the value of the <b>width</b>
     *      argument so that the image preserves its aspect ratio.
     *
     *      If <b>preserve_aspect_ratio</b> is set to TRUE and both this and the <b>width</b> arguments are values greater
     *      than <b>0</b>, the image will be resized to the exact required width and height and the aspect ratio will be
     *      preserved (see the description for the <b>method</b> argument below on how can this be done).
     *
     *      If <b>preserve_aspect_ratio</b> is set to FALSE, the image will be resized to the required height and the
     *      aspect ratio will be ignored.
     *
     *      If both <b>height</b> and <b>width</b> are set to <b>0</b>, a copy of the source image will be created
     *      (<b>jpeg_quality</b> will still apply).
     *
     *      If either <b>height</b> or <b>width</b> are set to <b>0</b>, the script will consider the value of the
     *      <b>preserve_aspect_ratio</b> to bet set to TRUE regardless of its actual value!
     *
     *  -   <i>preserve_aspect_ratio</i>: If set to TRUE, the image will be resized to the given width and height and the
     *      aspect ratio will be preserved.
     *
     *      Set this to FALSE if you want the image forcefully resized to the exact dimensions given by width and height
     *      ignoring the aspect ratio
     *
     *  -   <i>method</i>: is the method to use when resizing images to exact width and height while preserving aspect
     *      ratio.
     *
     *      If the <b>preserve_aspect_ratio</b> property is set to TRUE and both the <b>width</b> and <b>height</b>
     *      arguments are values greater than <b>0</b>, the image will be resized to the exact given width and height
     *      and the aspect ratio will be preserved by using on of the following methods:
     *
     *  -   <b>ZEBRA_IMAGE_BOXED</b> - the image will be scalled so that it will fit in a box with the given width and
     *      height (both width/height will be smaller or equal to the required width/height) and then it will be centered
     *      both horizontally and vertically. The blank area will be filled with the color specified by the
     *      <b>background_color</b> argument. (the blank area will be filled only if the image is not transparent!)
     *
     *  -   <b>ZEBRA_IMAGE_NOT_BOXED</b> - the image will be scalled so that it <i>could</i> fit in a box with the given
     *      width and height but will not be enclosed in a box with given width and height. The new width/height will be
     *      both smaller or equal to the required width/height
     *
     *  -   <b>ZEBRA_IMAGE_CROP_TOPLEFT</b>
     *  -   <b>ZEBRA_IMAGE_CROP_TOPCENTER</b>
     *  -   <b>ZEBRA_IMAGE_CROP_TOPRIGHT</b>
     *  -   <b>ZEBRA_IMAGE_CROP_MIDDLELEFT</b>
     *  -   <b>ZEBRA_IMAGE_CROP_CENTER</b>
     *  -   <b>ZEBRA_IMAGE_CROP_MIDDLERIGHT</b>
     *  -   <b>ZEBRA_IMAGE_CROP_BOTTOMLEFT</b>
     *  -   <b>ZEBRA_IMAGE_CROP_BOTTOMCENTER</b>
     *  -   <b>ZEBRA_IMAGE_CROP_BOTTOMRIGHT</b>
     *
     *  For the methods involving crop, first the image is scaled so that both its sides are equal or greater than the
     *  respective sizes of the bounding box; next, a region of required width and height will be cropped from indicated
     *  region of the resulted image.
     *
     *  -   <i>background_color</i> is the hexadecimal color of the blank area (without the #). See the <b>method</b>
     *      argument.
     *
     *  -   <i>enlarge_smaller_images</i>: if set to FALSE, images having both width and height smaller than the required
     *      width and height, will be left untouched (<b>jpeg_quality</b> will still apply).
     *
     *  -   <i>jpeg_quality</i> indicates the quality of the output image (better quality means bigger file size).
     *
     *      Range is 0 - 100
     *
     *      Available only for JPEG files.
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  <i>This rule must come</i> <b>after</b> <i>the</i> <b>upload</b> <i>rule!</i>
     *
     *  This is not an actual "rule", but because it can generate an error message it is included here.
     *
     *  Available only for the {@link MyZebra_Form_File file} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'resize' => array(
     *          'thumb_',                           // prefix
     *          '150',                              // width
     *          '150',                              // height
     *          true,                               // preserve aspect ratio
     *          ZEBRA_IMAGE_BOXED,                  // method to be used
     *          'FFFFFF',                           // background color
     *          true,                               // enlarge smaller images
     *          85,                                 // jpeg quality
     *          'error',                            // variable to add the error message to
     *          'Thumbnail could not be created!'   // error message if value doesn't validate
     *       )
     *  );
     *
     *  // for multiple resizes, use an array of arrays:
     *  $obj->set_rule(
     *       'resize' => array(
     *          array('thumb1_', 150, 150, true, ZEBRA_IMAGE_BOXED, 'FFFFFF', true, 85, 'error', 'Error!'),
     *          array('thumb2_', 300, 300, true, ZEBRA_IMAGE_BOXED, 'FFFFFF', true, 85, 'error', 'Error!'),
     *       )
     *  );
     *  </code>
     *
     *  -   <b>upload</b>
     *
     *  <code>'upload' => array($upload_path, $file_name, $permissions, $error_block, $error_message)</code>
     *
     *  where
     *
     *  -   <i>upload_path</i> the path where to upload the file to (relative to your working path)
     *
     *  -   <i>file_name</i>: specifies whether the uploaded file's original name should be preserved, should it be
     *      prefixed with a string, or should it be randomly generated.
     *
     *      Possible values can be <b>TRUE</b>: the uploaded file's original name will be preserved; <b>FALSE</b> (or,
     *      for better code readability, you should use the "MYZEBRA_FORM_UPLOAD_RANDOM_NAMES" constant instead of "FALSE")
     *      : the uploaded file will have a randomly generated name; <b>a string</b>: the uploaded file's original name
     *      will be preserved but it will be prefixed with the given string (i.e. "original_", or "tmp_").
     *
     *      Note that when set to TRUE or a string, a suffix of "_n" (where n is an integer) will be appended to the
     *      file name if a file with the same name already exists at the given path.
     *
     *  -   <i>error_block</i> is the PHP variable to append the error message to, in case the rule does not validate
     *
     *  -   <i>error_message</i> is the error message to be shown when rule is not obeyed
     *
     *  Validates if the file was successfully uploaded to the folder specified by <b>upload_path</b>.
     *
     *  <i>Remember to check the form's {@link MyZebra_Form::$file_upload $file_upload} property for information about the
     *  uploaded file after the form is submitted!</i>
     *
     *  <i>Remember to check the form's {@link MyZebra_Form::$file_upload_permissions $file_upload_permissions} property
     *  for how to set the filesystem permissions of the uploaded files!</i>

     *  <i>Note that once this rule is run client-side, the DOM element it is attached to will get an attribute called</i>
     *  <b>file_info</b> <i>which will contain information about the uploaded file, usable in the JavaScript part of a
     *  custom function. In the JavaScript part of a custom rule, run after the "upload" rule, you could use Firebug's
     *  console tab to see the values of 'file_info':</i>
     *
     *  <code>
     *  console.log($('#element_id').data('file_info'))
     *  </code>
     *
     *  This is not actually a "rule", but because it can generate an error message it is included here
     *
     *  You should use this rule in conjunction with the <b>filesize</b> rule
     *
     *  Available only for the {@link MyZebra_Form_File file} control
     *
     *  <i>This rule is not available client-side!</i>
     *
     *  <code>
     *  // $obj is a reference to a control
     *  $obj->set_rule(
     *       'upload' => array(
     *          'tmp',                              // path to upload file to
     *          MYZEBRA_FORM_UPLOAD_RANDOM_NAMES,     // upload file with random-generated name
     *          'error',                            // variable to add the error message to
     *          'File could not be uploaded!'       // error message if value doesn't validate
     *       )
     *  );
     *  </code>
     *
     *  @param  array   $rules  An associative array
     *
     *                          See above how it needs to be specified for each rule
     *
     *  @return void
     */
    function _getURLRegEx($strict = false) {
        $this->__populateIp();
        $validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=~[]') . '\/0-9a-z\p{L}\p{N}]|(%[0-9a-f]{2}))';
        $regex = '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)' . (!empty($strict) ? '' : '?') .
                '(?:' . $this->__pattern['IPv4'] . '|\[' . $this->__pattern['IPv6'] . '\]|' . $this->__pattern['hostname'] . ')' .
                '(?::[1-9][0-9]{0,4})?' .
                '(?:\/?|\/' . $validChars . '*)?' .
                '(?:\?' . $validChars . '*)?' .
                '(?:#' . $validChars . '*)?$/iu';
        return $regex;
    }
    
    function __populateIp() {
        if (!isset($this->__pattern['IPv6'])) {
            $pattern = '((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}';
            $pattern .= '(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})';
            $pattern .= '|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})';
            $pattern .= '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)';
            $pattern .= '{4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
            $pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}';
            $pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|';
            $pattern .= '((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}';
            $pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
            $pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4})';
            $pattern .= '{0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)';
            $pattern .= '|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]';
            $pattern .= '\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4})';
            $pattern .= '{1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?';

            $this->__pattern['IPv6'] = $pattern;
        }
        if (!isset($this->__pattern['IPv4'])) {
            $pattern = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
            $this->__pattern['IPv4'] = $pattern;
        }
    }
    
    
    function set_rule($rules)
    {
        $this->_set_rule($rules);
    }
    
    
    function _set_rule($rules)
    {

        // continue only if argument is an array
        if (is_array($rules))

            // iterate through the given rules
            foreach ($rules as $rule_name => $rule_properties) {

                // make sure the rule's name is lowercase
                $rule_name = strtolower($rule_name);

                // if custom rule
                if ($rule_name == 'custom')

                    // if more custom rules are specified at once
                    if (is_array($rule_properties[0]))

                        // iterate through the custom rules
                        // and add them one by one
                        foreach ($rule_properties as $rule) $this->rules[$rule_name][] = $rule;

                    // if a single custom rule is specified
                    // save the custom rule to the "custom" rules array
                    else $this->rules[$rule_name][] = $rule_properties;

                elseif ($rule_name == 'url')
                {
                    $regex=$this->_getURLRegEx();
                    $regex_rule_properties=array(
                     $regex,              // the regular expression
                     $rule_properties[0], // variable to add the error message to
                     $rule_properties[1]  // error message if value doesn't validate
                    );
                    $this->rules['url'] = $regex_rule_properties;
                }
                // for all the other rules
                // add the rule to the rules array
                else $this->rules[$rule_name] = $rule_properties;

                // for some rules we do some additional settings
                switch ($rule_name) {

                    // we set a reserved attribute for the control by which we're telling the
                    // _render_attributes() method to append a special class to the control when rendering it
                    // so that we can also control user input from javascript
                    case 'alphabet':
                    case 'digits':
                    case 'alphanumeric':
                    case 'number':
                    case 'float':

                        $this->set_attributes(array('onkeypress' => 'javascript:return jQuery(\'#' . $this->form_properties['name'] . '\').data(\'MyZebra_Form\').filter_input(\'' . $rule_name . '\', event' . ($rule_properties[0] != '' ? ', \'' . addcslashes($rule_properties[0], '\'') . '\'' : '') . ');'));

                        break;

                    // if the rule is about the length of the input
                    case 'length':

                        // if there is a maximum of allowed characters
                        if ($rule_properties[1] > 0) {

                            // set the maxlength attribute of the control
                            $this->set_attributes(array('maxlength' => $rule_properties[1]));

                            // if there is a 5th argument to the rule, the argument is boolean true
                            if (isset($rule_properties[4]) && $rule_properties[4] === true) {

                                // add an extra class so that the JavaScript library will know to show the character counter
                                $this->set_attributes(array('class' => 'myzebra-show-character-counter'), false);

                            }

                        }

                        break;

                }

            }

    }
    
    function before_render(&$clientside_error_messages, &$datepicker_javascript, &$additional)
    {
        $this->_before_render($clientside_error_messages, $datepicker_javascript, $additional);
    }
    
    function _before_render(&$clientside_error_messages, &$datepicker_javascript, &$additional)
    {
        // get some attributes for each control
        //cred_log($key);
        $attributes = $this->get_attributes(array('type', 'for', 'name', 'id', 'multiple', 'other', 'class', 'default_other'));
        // sanitize the control's name
        $attributes['name'] = preg_replace('/\[.*\]/', '', $attributes['name']);

        // if client-side validation is enabled and control has any rules attached to it
        if (is_array($this->form_properties['clientside_validation']) && !empty($this->rules)) {

            // if variable not created yet, create the variable holding client-side error messages
            if (!isset($clientside_error_messages)) $clientside_error_messages = '';

            // add error message
            if ($attributes['type']=='skype')
                $clientside_error_messages .= ($clientside_error_messages != '' ? ',' : '') . '"' . $attributes['id'].'-skypename' . '":{';
            else
                $clientside_error_messages .= ($clientside_error_messages != '' ? ',' : '') . '"' . $attributes['id'] . '":{';

            $class = $rules = '';

            // we need to make sure that rules are in propper order - "required" must always be checked first when
            // present and "upload" needs to precede any other upload-related rules;

            // if the upload rule exists
            if (isset($this->rules['upload'])) {
            
                // remove it from wherever it is
                $rule = array_splice($this->rules, array_search('upload', array_keys($this->rules)), 1, array());

                // and make sure it's the first rule
                $this->rules = array_merge($rule, $this->rules);
            }

            // if the "requried" rule exists
            if (isset($this->rules['required'])) {

                // remove it from wherever it is
                $rule = array_splice($this->rules, array_search('required', array_keys($this->rules)), 1, array());

                // and make sure it's the first rule (it has to checked prior to the "upload" rule)
                $this->rules = array_merge($rule, $this->rules);
            }

            // iterate through the rules attached to the control
            foreach ($this->rules as $rule => $properties) {

                // these rules are not checked client side
                if ($rule == 'resize' || $rule == 'convert') continue;

                // start preparing the class that is to be added to the control
                $class .= ($class != '' ? ',' : '') . $rule;

                // for some rules we perform some additional tasks
                switch ($rule) {

                    case 'regexp':

                        if (trim($properties[0]) != '') {

                            // the class name also contains the regular expression
                            $class .= '(' . preg_replace(array('/\[/', '/\]/', '/\,/', '/\(/', '/\)/'), array('lsqb;', 'rsqb;', 'comma;', 'lsb;', 'rsb;'), $properties[0]) . ')';

                        }

                        break;
                    
                    case 'url':
                        //$additional.="var url_reg_ex="."'".$properties[0]."'".";";
                        break;
                        
                    // for some of the rules
                    case 'alphabet':
                    case 'alphanumeric':
                    case 'compare':
                    case 'digits':
                    case 'filesize':
                    case 'filetype':
                    case 'float':
                    case 'number':

                        // the class name also contains the extra parameter(s)
                        $class .= '(' . preg_replace('/\,/', 'comma;', $properties[0]) . ')';

                        break;
                    case 'image_max_width':
                    case 'image_max_height':

                        // the class name also contains the extra parameter(s)
                        $class .= '(' . $properties[0] . ')';

                        break;

                    // for the custom rule
                    case 'custom':

                        $messages = '';

                        // as custom rules are always given as an array
                        // iterate through the available custom rules
                        foreach ($properties as $counter => $values) {

                            // if custom function is given as a function created with create_function
                            if ((ord($values[0][0]) == 0 && preg_match('/.{1}lambda\_[0-9]+/', $values[0])))

                                // function name is the control's id and the number of custom function as suffix
                                $function_name = $attributes['id'] . ($counter + 1);

                            // if custom function is given as the name of a function
                            // the function's name is the given name
                            else $function_name = $values[0];

                            // generate the validation rules
                            $class .= ($counter > 0 ? ',' : '(') . $function_name . 'comma;';

                            // the custom arguments to be passed to the custom functions
                            $class .= implode('comma;', array_map(create_function('$value', 'return preg_replace(array("/\,/"), array("mark;"), $value);'), array_slice($values, 1, -2)));

                            // remove any trailing "comma;" that may have left over if there are no custom arguments to the function
                            $class = preg_replace('/comma\;.*?$/', '', $class);

                            // the error message
                            $messages .= ($counter > 0 ? ',' : '') . '"custom_' . $function_name . '":"' . $values[count($values) - 1] . '"';

                        }

                        // wrap up
                        $class .= ')';

                        break;

                    // for the date_compare rule
                    case 'datecompare':

                        // the class name also contains the control to compare the date to and the operator
                        $class .= '(' . $properties[0] . ',' . $properties[1] . ')';

                        break;

                    // for the length rule
                    case 'length':

                        // the class name also contain min/max
                        $class .= '(' . $properties[0] . ',' . $properties[1] . ')';

                        // if max is greater than 0
                        if ($properties[1] > 0)

                            // we also set the maxlength attribute of the control
                            $this->set_attributes(array('maxlength' => $properties[1]));

                        break;

                    case 'upload':

                        // the class name also contains the extra parameter(s)
                        //$class .= '(' . rawurlencode(trim('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST']. dirname($_SERVER['PHP_SELF']), '/') . '/' . $properties[0]) . ')';
                        $class .= '(' . rawurlencode( $properties[0].'/') . ','.rawurlencode( $properties[1].'/').')';

                        break;

                }

                // if custom rule
                if ($rule == 'custom')

                    // add the error message to the javascript object
                    $rules .= ($rules != '' ? ',' : '') . $messages;

                // for other rules
                // (except those which do not have equivalents in JavaScript)
                else

                    // add the error message to the javascript object
                    $rules .= ($rules != '' ? ',' : '') . '"' . $rule . '":"' . addcslashes($properties[count($properties) - ($rule == 'length' && count($properties) == 5 ? 2 : 1)], '"') . '"';

            }

            // wrap up client-side error messages
            if ($attributes['type']=='file')
            {
                $value=false;
                if (isset($this->text_field->attributes['value']) && is_string($this->text_field->attributes['value']) && !empty($this->text_field->attributes['value']))
                    $value=$this->text_field->attributes['value'];
                elseif (isset($this->attributes['value']) && is_string($this->attributes['value']) && !empty($this->attributes['value']))
                    $value=$this->attributes['value'];
                
                if ($value && !empty($value))
                    $clientside_error_messages .= $rules .= ($rules!=''?',':'').'file_value:"'.$value.'"'.'}';
                else
                    $clientside_error_messages .= $rules .= '}';
                
            }
            else
                $clientside_error_messages .= $rules .= '}';

            // add a class so that the javascript validator knows that it has to validate the control
            $this->set_attributes(array('class' => 'validate[' . $class . ']'), false);

        }
        
        // if control is a date control
        if (isset($attributes['type']) && $attributes['type'] == 'text' && isset($attributes['class']) && preg_match('/\bmyzebra-date\b/i', $attributes['class'])) {

            // if variable is not yet defined. define it
            if (!isset($datepicker_javascript)) $datepicker_javascript = array();

            // append the new date picker object
            $datepicker_javascript[$attributes['id']] = '';
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
            // add clear date localized string
            $properties .= ($properties != '' ? ',' : '') . 'lang_clear_date' . ':' . '"'.$this->form_properties['language']['clear_date'].'"';
            // wrap up the javascript object
            $datepicker_javascript[$attributes['id']] = ($properties != '' ? '{' . $properties . '}' : '') . '';;
        }

    }
    
    /**
     *  Converts the array with control's attributes to valid HTML markup interpreted by the {@link toHTML()} method
     *
     *  Note that this method skips {@link $private_attributes}
     *
     *  @return string  Returns a string with the control's attributes
     *
     *  @access private
     */
    function _render_attributes()
    {

        // the string to be returned
        $attributes = '';

        // if
        if (

            // control has the "disabled" attribute set
            isset($this->attributes['disabled']) &&

            $this->attributes['disabled'] == 'disabled' &&

            // control is not a radio button
            $this->attributes['type'] != 'radio' &&

            // control is not a checkbox
            $this->attributes['type'] != 'checkbox'

        // add another class to the control
        ) $this->set_attributes(array('class' => 'myzebra-disabled'), false);

        // iterates through the control's attributes
        foreach ($this->attributes as $attribute => $value)

            if (

                // if control has no private attributes or the attribute is not  a private attribute
                (!isset($this->private_attributes) || !in_array($attribute, $this->private_attributes)) &&

                // and control has no private javascript attributes or the attribute is not in a javascript private attribute
                (!isset($this->javascript_attributes) || !in_array($attribute, $this->javascript_attributes))

            )
            {

                // do escaping
                if ($this->escape)
                {
                    switch($attribute)
                    {
                        case 'src':
                        case 'href':
                            // add attribute => value pair to the return string
                            $attributes .=
                                ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $this->extra_xss('url',$value)) . '"';
                            break;
                            
                        case 'value':
                            if ('text'==$this->attributes['type'])
                            {
                                // add attribute => value pair to the return string
                                $attributes .=
                                    ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $this->extra_xss('attr',$value)) . '"';
                            }
                            elseif ('textarea'==$this->attributes['type'])
                            {
                                // add attribute => value pair to the return string
                                $attributes .=
                                    ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $this->extra_xss('textarea',$value)) . '"';
                            }
                            else
                            {
                                $attributes .=
                                ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $value) . '"';  
                            }
                            break;
                            
                        default: // no escape for other attributes
                                $attributes .=
                                ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $value) . '"';  
                                break;
                    }
                }
                else
                {
                    $attributes .=
                    ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $value) . '"';  
                }
            }

        // returns string
        return $attributes;

    }
    
    function validate($only=false)
    {
         
         // bypass
         if ($this->isDiscarded())
            return true;
            
         // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};

        // at this point, we assume that the control is not valid
        $valid = false;

        // continue only if form was submitted
        if (

            isset($method[$this->form_properties['identifier']]) &&

            $method[$this->form_properties['identifier']] == $this->form_properties['name']

        ) {

            if (!$only)
                // manage submitted value
                $this->get_submitted_value();
            
            // at this point, we assume that the control is valid
            $valid = $this->_validate();
        }
        
        //if (!$valid)
            //cred_log(print_r($this,true));
        return $valid;
    }
    
    
    function _validate()
    {

        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};

        // at this point, we assume that the control is not valid
        $valid = false;

        // continue only if form was submitted
        if (

            isset($method[$this->form_properties['identifier']]) &&

            $method[$this->form_properties['identifier']] == $this->form_properties['name']

        ) {

            // at this point, we assume that the control is valid
            $valid = true;


            // manage submitted value
            //$this->get_submitted_value();

            // get some attributes of the control
            $attribute = $this->get_attributes(array('name', 'id', 'type', 'value', 'multiple', 'format', 'disable_spam_filter'));
            // sanitize the control's name
            $attribute['name'] = preg_replace('/\[.*\]/', '', $attribute['name']);
            
            //if ($attribute['type']=='file' && !$this->file_data)
                //$this->file_data=array($attribute['name']=>$_FILES[$attribute['name']]);
                
            // if control doesn't have the SPAM filter disabled
            if (!isset($attribute['disable_spam_filter']) || $attribute['disable_spam_filter'] !== true) {

                // check to see if there is SPAM/INJECTION attempt by checking if the values in select boxes, radio buttons
                // and checkboxes are in the list of allowable values, as set when initializing the controls

                // check controls by type
                switch ($attribute['type']) {

                    // if control is a select box
                    case 'select':

                        // if control was submitted
                        // (as there can also be no selections for a select box with the "multiple" attribute set, case in
                        // which there's no submission)
                        if ($this->submitted_value) {

                            // flatten array (in case we have select groups)
                            $values = $this->_extract_values($this->attributes['options']);

                            // if an array was submitted and there are values that are not in the list allowable values
                            if (is_array($this->submitted_value) && $this->submitted_value != array_intersect($this->submitted_value, $values))

                                // set a flag accordingly
                                $valid = false;

                            // if submitted value is not an array and submitted value is not in the list of allowable values
                            if (!is_array($this->submitted_value) && !in_array($this->submitted_value, $values))

                                // set a flag accordingly
                                $valid = false;

                        }

                        break;

                    // if control is a checkbox control or a radio button
                    case 'checkbox':
                    case 'radio':

                        // if control was submitted
                        if ($this->submitted_value) {

                            $values = array();

                            $conts=array();
                            if (isset($this->checkboxes))
                                $conts=&$this->checkboxes;
                            if (isset($this->radios))
                                $conts=&$this->radios;
                            // iterate through all the form's controls
                            if (!empty($conts))
                            {
                            foreach ($conts as $element)

                                // if control is of the same type and has the same name
                                //if ($element->attributes['type'] == $attribute['type'] && $element->attributes['name'] == $attribute['name'])

                                    // add the control's value to the list of valid values
                                    $values[] = $element->attributes['value'];
                            }
                            else
                            {
                                $values[]=$this->attributes['value'];
                            }
                            // if an array was submitted and there are values that are not in the list allowable values
                            if (is_array($this->submitted_value) && $this->submitted_value != array_intersect($this->submitted_value, $values))

                                // set a flag accordingly
                                $valid = false;

                            // if submitted value is not an array and submitted value is not in the list of allowable values
                            if (!is_array($this->submitted_value) && !in_array($this->submitted_value, $values))

                                // set a flag accordingly
                                $valid = false;

                        }

                        break;
                    
                }

                // if spam attempt was detected
                if (!$valid) {

                    // set the error message
                    $this->form->add_error('*spam*', $this->form_properties['language']['spam_detected']);

                    // don't look further
                    return false;

                }

            }

            // if
            if (

                // control was submitted and has rules assigned
                isset($this->submitted_value) && !empty($this->rules)

            ) {

                // iterate through rules assigned to the control
                foreach ($this->rules as $rule_name => $rule_attributes) {

                    // make sure the rule name is in lowercase
                    $rule_name = strtolower($rule_name);

                    // check the rule's name
                    switch ($rule_name) {

                        // if control is a hidden, do custom hidden validation
                        case 'hidden':
                            if (

                                // control was submitted and has rules assigned
                                isset($this->submitted_value) && !empty($this->rules)
                                && isset($this->attributes['default'])
                            ) {

                                    // custom validation rule for hidden: value must remain same
                                    if ($this->attributes['default']!=$this->submitted_value)
                                    {
                                        $valid = false;
                                        $this->form->add_error($attribute['id'], $rule_attributes[1]);
                                    }
                                }
                        break;
                        
                        // if rule is 'alphabet'
                        case 'alphabet':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                // control does not contain only letters from the alphabet (and other allowed characters, if any)
                                !preg_match('/^[a-z' . preg_quote($rule_attributes[0]) . ']+$/i', $attribute['value'])

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if rule is 'alphanumeric'
                        case 'alphanumeric':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                // control does not contain only allowed characters
                                !preg_match('/^[a-z0-9' . preg_quote($rule_attributes[0]) . ']+$/i', $attribute['value'])

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if 'captcha'
                        case 'captcha':

                            if (

                                // control is 'text'
                                $attribute['type'] == 'text' &&

                                // control's value is not the one showed in the picture
                                md5(md5(md5(strtolower($this->submitted_value)))) !=  @$_COOKIE['captcha']

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if 'compare'
                        case 'compare':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) && (

                                    // the control to compare to was not submitted
                                    !isset($method[$rule_attributes[0]]) ||

                                    // OR
                                    (

                                        // the control to compare to was submitted
                                        isset($method[$rule_attributes[0]]) &&

                                        // and the values don't match
                                        $this->submitted_value != $method[$rule_attributes[0]]

                                    )

                                )

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if 'convert'
                        case 'convert':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                $this->file_data && isset($this->file_data[$attribute['name']]) &&

                                // and file was uploaded without any errors
                                //$_FILES[$attribute['name']]['error'] == 0
                                $this->file_data[$attribute['name']]['error'] == 0  &&
                                
                                !$this->form->preview

                            ) {

                                // as conversions are done only when the form is valid
                                // for now we only save some data that will be processed if the form is valid
                                // (we're adding keys so that we don't have duplicate actions if validate_control method is called repeatedly)
                                $this->actions[$attribute['name'] . '_convert'] = array(

                                    '_convert',                                             //  method to be called
                                    //$attribute['name'],                                     //  the file upload control's name
                                    'extension'                 =>  $rule_attributes[0],    //  extension to convert to
                                    'quality'                   =>  $rule_attributes[1],    //  quality (available only for JPEG files)
                                    'preserve_original_file'    =>  $rule_attributes[2],    //  preserve original file?
                                    'overwrite'                 =>  $rule_attributes[3],    //  overwrite if file with new extension exists
                                    'block'                     =>  $rule_attributes[4],    //  error block
                                    'message'                   =>  $rule_attributes[5],    //  error message

                                );

                            }

                            break;

                        // if 'custom' rule
                        case 'custom':

                            // custom rules are stored as an array
                            // iterate through the custom rules
                            foreach ($rule_attributes as $custom_rule_attributes) {

                                // if custom function exists
                                if (function_exists($custom_rule_attributes[0])) {

                                    // the arguments that we are passing to the custom function are the control's
                                    // submitted value and all other arguments passed when setting the custom rule
                                    // except the first one which is the custom function's and the last two which are
                                    // the error block name and the error message respectively
                                    $arguments = array_merge(array($this->submitted_value), array_slice($custom_rule_attributes, 1, -2));

                                    // run the custom function
                                    // and if the function returns false
                                    if (!call_user_func_array($custom_rule_attributes[0], $arguments)) {

                                        // count the arguments passed when declaring the rules
                                        $attributes_count = count($custom_rule_attributes);

                                        // add error message to indicated error block
                                        //$this->form->add_error($custom_rule_attributes[$attributes_count - 2], $custom_rule_attributes[$attributes_count - 1]);
                                        $this->form->add_error($attribute['id'], $custom_rule_attributes[$attributes_count - 1]);

                                        // the control does not validate
                                        $valid = false;

                                        // no further checking needs to be done for the control, making sure that only one
                                        // error message is displayed at a time for each erroneous control
                                        break 3;

                                    }

                                // if custom function doesn't exist, trigger an error message
                                } else _myzebra_form_show_error('Function <strong>' . $custom_rule_attributes[0] . '()</strong> doesn\'t exist.', E_USER_ERROR);

                            }

                            break;

                        // if date
                        case 'date':

                            if (

                                // control is 'text'
                                $attribute['type'] == 'text' &&

                                // is a 'date' control
                                isset($attribute['format']) &&

                                // a value was entered
                                $attribute['value'] != ''

                            ) {

                                // the format we expect the date to be in (white spaces removed)
                                // we are removing spaces so that a format like, for example, "M d Y, H:i" (note the space after the comma)
                                // will validate also if hour and minute come right after the comma
                                // also, escape characters that would make sense as regular expression
                                $format = preg_replace('/\s/', '', preg_quote($this->attributes['format']));

                                // parse the format and extract the characters that define the format
                                // (note that we're also capturing the offsets)
                                preg_match_all('/[dDjlNSwFmMnYyGHghaAisU]{1}/', $format, $matches, PREG_OFFSET_CAPTURE);

                                $regexp = array();

                                // iterate through the found characters
                                // and create the regular expression that we will use to see if the entered date is ok
                                foreach ($matches[0] as $match) {

                                    switch ($match[0]) {

                                        // day of the month, 2 digits with leading zeros, 01 to 31
                                        case 'd': $regexp[] = '0[1-9]|[12][0-9]|3[01]'; break;

                                        // a textual representation of a day, three letters, mon through sun
                                        case 'D': $regexp[] = '[a-z]{3}'; break;

                                        // day of the month without leading zeros, 1 to 31
                                        case 'j': $regexp[] = '[1-9]|[12][0-9]|3[01]'; break;

                                        // a full textual representation of the day of the week, sunday through saturday
                                        case 'l': $regexp[] = '[a-z]+'; break;

                                        // ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0), 1 (for Monday) through 7 (for Sunday)
                                        case 'N': $regexp[] = '[1-7]'; break;

                                        // english ordinal suffix for the day of the month, 2 characters: st, nd, rd or th. works well with j
                                        case 'S': $regexp[] = 'st|nd|rd|th'; break;

                                        // numeric representation of the day of the week, 0 (for sunday) through 6 (for saturday)
                                        case 'w': $regexp[] = '[0-6]'; break;

                                        // a full textual representation of a month, such as january or march
                                        case 'F': $regexp[] = '[a-z]+'; break;

                                        // numeric representation of a month, with leading zeros, 01 through 12
                                        case 'm': $regexp[] = '0[1-9]|1[012]+'; break;

                                        // a short textual representation of a month, three letters, jan through dec
                                        case 'M': $regexp[] = '[a-z]{3}'; break;

                                        // numeric representation of a month, without leading zeros, 1 through 12
                                        case 'n': $regexp[] = '[1-9]|1[012]'; break;

                                        // a full numeric representation of a year, 4 digits examples: 1999 or 2003
                                        case 'Y': $regexp[] = '[0-9]{4}'; break;

                                        // a two digit representation of a year examples: 99 or 03
                                        case 'y': $regexp[] = '[0-9]{2}'; break;

                                        // 24-hour format of an hour without leading zeros, 0 through 23
                        				case 'G':

                                        // 24-hour format of an hour with leading zeros, 00 through 23
                        				case 'H':

                                        // 12-hour format of an hour without leading zeros, 1 through 12
                        				case 'g':

                                        // 12-hour format of an hour with leading zeros, 01 through 12
                        				case 'h': $regexp[] = '[0-9]{1,2}'; break;

                                        // lowercase ante meridiem and post meridiem am or pm
                        				case 'a':
                        				case 'A': $regexp[] = '(am|pm)'; break;

                                        // minutes with leading zeros, 00 to 59
                        				case 'i':

                                        // seconds, with leading zeros 00 through 59
                        				case 's': $regexp[] = '[012345][0-9]'; break;

                                    }

                                }

                                // if format is defined
                                if (!empty($regexp)) {

                                    // we will replace every format-related character in the format expression with
                                    // the appropriate regular expression in order to see that valid data was entered
                                    // as required by the character
                                    // we are replacing from finish to start so that we don't mess up the offsets
                                    // therefore, we need to reverse the array first
                                    $matches[0] = array_reverse($matches[0]);

                                    // how many characters to replace
                                    $chars = count($matches[0]);

                                    // iterate through the characters
                                    foreach ($matches[0] as $index => $char)

                                        // and replace them with the appropriate regular expression
                                        $format = substr_replace($format, '(' . $regexp[$chars - $index - 1] . ')', $matches[0][$index][1], 1);

                                    // the final regular expression to math the date against
                                    $format = '/^' . str_replace('/', '\/', $format) . '$/i';

                                    // if entered value (with spaces removed) seems to be ok
                                    if (preg_match($format, preg_replace('/\s/', '', $attribute['value']), $segments)) {

                                        $original_day = $original_month = $original_year = 0;

                                        // english names for days and months
                                        $english_days   = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
                                        $english_months = array('January','February','March','April','May','June','July','August','September','October','November','December');

                                        // reverse the characters in the format (remember that we reversed them above)
                                        $matches[0] = array_reverse($matches[0]);

                                        $valid = true;

                                        // iterate through the characters in the format
                                        // to see if months and days are correct
                                        // i.e. if for month we entered "abc" it would pass our regular expression but
                                        // now we will check if the three letter text is an actual month
                                        foreach ($matches[0] as $index => $match) {

                                            switch ($match[0]) {

                                                // numeric representation of a month, with leading zeros, 01 through 12
                                                case 'm':
                                                // numeric representation of a month, without leading zeros, 1 through 12
                                                case 'n':

                                                    $original_month = (int)($segments[$index + 1] - 1);

                                                    break;

                                                // day of the month, 2 digits with leading zeros, 01 to 31
                                                case 'd':
                                                // day of the month without leading zeros, 1 to 31
                                                case 'j':

                                                    $original_day = (int)($segments[$index + 1]);

                                                    break;

                                                // a textual representation of a day, three letters, mon through sun
                                                case 'D':
                                                // a full textual representation of the day of the week, sunday through saturday
                                                case 'l':
                                                // a full textual representation of a month, such as january or march
                                                case 'F':
                                                // a short textual representation of a month, three letters, jan through dec
                                                case 'M':

                                                    // by default, we assume that the text is invalid
                                                    $valid = false;

                                                    // iterate through the values in the language file
                                                    foreach ($this->form->language[($match[0] == 'F' || $match[0] == 'M' ? 'months' : 'days')] as $key => $value) {

                                                        // if value matches the value from the language file
                                                        if (strtolower($segments[$index + 1]) == strtolower(substr($value, 0, ($match[0] == 'D' || $match[0] == 'M' ? 3 : strlen($value))))) {

                                                            // replace with the english value
                                                            // this is because later on we'll run strtotime of the entered value and strtotime parses english dates
                                                            switch ($match[0]) {
                                                                case 'D': $segments[$index + 1] = substr($english_days[$key], 0, 3); break;
                                                                case 'l': $segments[$index + 1] = $english_days[$key]; break;
                                                                case 'F': $segments[$index + 1] = $english_months[$key]; $original_month = $key; break;
                                                                case 'M': $segments[$index + 1] = substr($english_months[$key], 0, 3); $original_month = $key; break;
                                                            }

                                                            // flag the value as valid
                                                            $valid = true;

                                                            // don't look further
                                                            break;

                                                        }

                                                    }

                                                    // if an invalid was found don't look any further
                                                    if (!$valid) break 2;

                                                    break;

                                                // a full numeric representation of a year, 4 digits examples: 1999 or 2003
                                                case 'Y':

                                                    $original_year = (int)($segments[$index + 1]);

                                                    break;

                                                // a two digit representation of a year examples: 99 or 03
                                                case 'y':

                                                    $original_year = (int)('19' . $segments[$index + 1]);

                                                    break;

                                            }

                                        }

                                        // if entered value seems valid
                                        if ($valid) {

                                            // if date is still valid after we process it with strtotime
                                            // (we do this because, so far, a date like "Feb 31 2010" would be valid
                                            // but strtotime would turn that to "Mar 03 2010")
                                            if (

                                                $english_months[$original_month] . ' ' . str_pad($original_day, 2, '0', STR_PAD_LEFT) . ', ' . $original_year ==
                                                date('F d, Y', strtotime($english_months[$original_month] . ' ' . $original_day . ', ' . $original_year))

                                            ) {

                                                // make sure we also return the date as YYYY-MM-DD so that it can be
                                                // easily used with a database or with PHP's strtotime function
                                                $this->attributes['date'] = $original_year . '-' . str_pad($original_month + 1, 2, '0', STR_PAD_LEFT) . '-' . str_pad($original_day, 2, '0', STR_PAD_LEFT);

                                                // control is valid
                                                break;

                                            }

                                        }

                                    }

                                }

                                // if scripts gets this far, it means there was an error somewhere

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if "datecompare"
                        case 'datecompare':

                            if (

                                // control is 'text'
                                $attribute['type'] == 'text' &&

                                // is a 'date' control
                                isset($attribute['format']) &&

                                // control to compare with, exists
                                isset($this->form->controls[$rule_attributes[0]]) &&

                                // control to compare with, is a 'text' control
                                $this->form->controls[$rule_attributes[0]]->attributes['type'] == 'text' &&

                                // control to compare with, is a 'date' control
                                ($this->form->controls[$rule_attributes[0]]->attributes['format']) &&

                                // control validates
                                $this->form->controls[$rule_attributes[0]]->validate()

                            ) {

                                // we assume the control is invalid
                                $valid = false;

                                // compare the controls according to the comparison operator
                                switch ($rule_attributes[1]) {
                                    case '>':
                                        $valid = ($this->attributes['date'] > $this->form->controls[$rule_attributes[0]]->attributes['date']);
                                        break;
                                    case '>=':
                                        $valid = ($this->attributes['date'] >= $this->form->controls[$rule_attributes[0]]->attributes['date']);
                                        break;
                                    case '<':
                                        $valid = ($this->attributes['date'] < $this->form->controls[$rule_attributes[0]]->attributes['date']);
                                        break;
                                    case '<=':
                                        $valid = ($this->attributes['date'] <= $this->form->controls[$rule_attributes[0]]->attributes['date']);
                                        break;
                                }

                                // if invalid
                                if (!$valid) {

                                    // add error message to indicated error block
                                    //$this->form->add_error($rule_attributes[2], $rule_attributes[3]);
                                    $this->form->add_error($attribute['id'], $rule_attributes[3]);

                                    // the control does not validate
                                    $valid = false;

                                    // no further checking needs to be done for the control, making sure that only one
                                    // error message is displayed at a time for each erroneous control
                                    break 2;

                                }

                            }

                            break;

                        // if rule is 'digits'
                        case 'digits':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                // but entered value does not contain digits only (and other allowed characters, if any)
                                !preg_match('/^[0-9' . preg_quote($rule_attributes[0]) . ']+$/', $attribute['value'])

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if "email"
                        case 'email':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                // but is not a valid email address
                                !preg_match('/^([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+@{1}([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+\.[A-Za-z0-9]{2,}$/', $attribute['value'])

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if "list of emails"
                        case 'emails':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != ''

                            ) {

                                // convert string to an array of addresses
                                $addresses = explode(',', $attribute['value']);

                                // iterate through the addresses
                                foreach ($addresses as $address)

                                    // not a valid email address
                                    if (!preg_match('/^([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+@{1}([a-zA-Z0-9_\-\+\~\^\{\}]+[\.]?)+\.[A-Za-z0-9]{2,}$/', trim($address))) {

                                        // add error message to indicated error block
                                        //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                        $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                        // the control does not validate
                                        $valid = false;

                                        // no further checking needs to be done for the control, making sure that only one
                                        // error message is displayed at a time for each erroneous control
                                        break 3;

                                    }

                            }

                            break;

                        // if "filesize"
                        case 'filesize':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                isset($this->file_data[$attribute['name']]) &&

                                (

                                    // uploaded file size exceeds the size imposed when creating the form
                                    //$_FILES[$attribute['name']]['size'] > $rule_attributes[0] ||

                                    // the uploaded file exceeds the upload_max_filesize directive in php.ini
                                    //$_FILES[$attribute['name']]['error'] == 1 ||

                                    // the uploaded file exceeds the MAX_FILE_SIZE directive that was specified
                                    // in the HTML form
                                    //$_FILES[$attribute['name']]['error'] == 2
                                    
                                    // uploaded file size exceeds the size imposed when creating the form
                                    $this->file_data[$attribute['name']]['size'] > $rule_attributes[0] ||

                                    // the uploaded file exceeds the upload_max_filesize directive in php.ini
                                    $this->file_data[$attribute['name']]['error'] == 1 ||

                                    // the uploaded file exceeds the MAX_FILE_SIZE directive that was specified
                                    // in the HTML form
                                    $this->file_data[$attribute['name']]['error'] == 2

                                )

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if "filetype"
                        case 'filetype':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                isset($this->file_data[$attribute['name']]) &&

                                // and file was uploaded without errors
                                //$_FILES[$attribute['name']]['error'] == 0
                                $this->file_data[$attribute['name']]['error'] == 0

                            ) {

                                // if file with mime types was not already loaded
                                if (!isset($this->form->mimes)) {

                                    // read file into an array
                                    $rows = file($this->form_properties['assets_server_path'] . 'mimes.json');

                                    // convert JSON to array
                                    // i'm aware that in PHP 5.2+ there is json_decode, but i want this library to be
                                    // as backward compatible as possible so, since the values in mimes.json has a
                                    // specific structure, i wrote my own decoder
                                    $this->form->mimes = array();

                                    // iterate through all the rows
                                    foreach ($rows as $row) {

                                        // if valid row found
                                        if (strpos($row, ':') !== false) {

                                            // explode the string by :
                                            $items = explode(':', $row);

                                            // the file type (extension)
                                            $index = trim(str_replace('"', '', $items[0]));

                                            // if there are more mime types attached
                                            if (strpos($items[1], '[') !== false)

                                                // convert to array
                                                $value = array_diff(array_map(create_function('&$value', 'return trim($value);'), explode(',', str_replace(array('[', ']', '"', '\/'), array('', '', '', '/'), $items[1]))), array(''));

                                            // if a single mime type is attached
                                            else

                                                // convert to string
                                                $value = trim(str_replace(array('"', ',', '\/'), array('', '', '/'), $items[1]));

                                            // save entry
                                            $this->form->mimes[$index] = $value;

                                        }

                                    }

                                }

                                // if "finfo_open" function exists (from PHP 5.3.0)
                                if (function_exists('finfo_open')) {

                                    // determine the "true" mime type of the uploaded file
                                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                    //$mime = finfo_file($finfo, $_FILES[$attribute['name']]['tmp_name']);
                                    $mime = finfo_file($finfo, $this->file_data[$attribute['name']]['tmp_name']);
                                    finfo_close($finfo);

                                // otherwise, rely on the information returned by $_FILES which uses the file's
                                // extension to determine the uploaded file's mime type and is therefore unreliable
                                } else $mime = $this->file_data[$attribute['name']]['type'];//$_FILES[$attribute['name']]['type'];

                                // get the allowed file types
                                $allowed_file_types = array_map(create_function('$value', 'return trim($value);'), explode(',', $rule_attributes[0]));

                                // this will contain an array of file types that match for the currently uploaded file's
                                // mime type
                                $matching_file_types = array();

                                // iterate through the known mime types
                                foreach ($this->form->mimes as $extension => $type)

                                    // if
                                    if (

                                        // there are more mime types associated with the file extension and
                                        // the uploaded file's type is among them
                                        is_array($type) && in_array($mime, $type) ||

                                        // a single mime type is associated with the file extension and
                                        // the uploaded file's type matches the mime type
                                        !is_array($type) && $type == $mime

                                    // add file type to the list of file types that match for the currently uploaded
                                    // file's mime type
                                    ) $matching_file_types[] = $extension;

                                // is the file allowed?
                                $matches = array_intersect($matching_file_types, $allowed_file_types);

                                // if file is not allowed
                                if (empty($matches)) {

                                    // add error message to indicated error block
                                    //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                    $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                    // the control does not validate
                                    $valid = false;

                                    // no further checking needs to be done for the control, making sure that only one
                                    // error message is displayed at a time for each erroneous control
                                    break 2;

                                }

                            }

                            break;

                        // if rule is 'float'
                        case 'float':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                (

                                    // only a dot given
                                    trim($attribute['value']) == '.' ||

                                    // only minus given
                                    trim($attribute['value']) == '-' ||

                                    // has too many minus sign
                                    preg_match_all('/\-/', $attribute['value'], $matches) > 1 ||

                                    // has too many dots in it
                                    preg_match_all('/\./', $attribute['value'], $matches) > 1 ||

                                    // not a floating point number
                                    !preg_match('/^[0-9\-\.' . preg_quote($rule_attributes[0]) . ']+$/', $attribute['value']) ||

                                    // has a minus sign in it but is not at the very beginning
                                    (strpos($attribute['value'], '-') !== false && strpos($attribute['value'], '-') > 0)

                                )

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if "image"
                        case 'image':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                $this->file_data && isset($this->file_data[$attribute['name']]) &&

                                // and file was uploaded without errors
                                //$_FILES[$attribute['name']]['error'] == 0
                                $this->file_data[$attribute['name']]['error'] == 0

                            ) {

                                // get some information about the file
                                //list($width, $height, $type, $attr) = @getimagesize($_FILES[$attribute['name']]['tmp_name']);
                                list($width, $height, $type, $attr) = @getimagesize($this->file_data[$attribute['name']]['tmp_name']);

                                // if file is not an image or image is not gif, png or jpeg
                                if ($type === false || $type < 1 || $type > 3) {

                                    // add error message to indicated error block
                                    //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                    $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                    // the control does not validate
                                    $valid = false;

                                    // no further checking needs to be done for the control, making sure that only one
                                    // error message is displayed at a time for each erroneous control
                                    break 2;

                                }

                            }
                            elseif (
                                $attribute['type'] == 'file' &&
                                isset($this->text_field) &&
                                $this->text_field->attributes['value']!=''
                            )
                            {
                                // get some information about the file using URL
                                //list($width, $height, $type, $attr) = @getimagesize($_FILES[$attribute['name']]['tmp_name']);
                                $_result = @getimagesize($this->text_field->attributes['value']);

                                if ($_result!==false)
                                {
                                    list($width, $height, $type, $attr)=$_result;
                                    // if file is not an image or image is not gif, png or jpeg
                                    if ($type === false || $type < 1 || $type > 3) {

                                        // add error message to indicated error block
                                        //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                        $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                        // the control does not validate
                                        $valid = false;

                                        // no further checking needs to be done for the control, making sure that only one
                                        // error message is displayed at a time for each erroneous control
                                        break 2;

                                    }
                                }
                            }

                            break;

                        case 'image_max_width':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                $this->file_data && isset($this->file_data[$attribute['name']]) &&

                                // and file was uploaded without errors
                                //$_FILES[$attribute['name']]['error'] == 0
                                $this->file_data[$attribute['name']]['error'] == 0

                            ) {

                                // get some information about the file
                                //list($width, $height, $type, $attr) = @getimagesize($_FILES[$attribute['name']]['tmp_name']);
                                list($width, $height, $type, $attr) = @getimagesize($this->file_data[$attribute['name']]['tmp_name']);

                                // if file is  an image or image is  gif, png or jpeg and larger than max-width
                                if (($type !== false && $type >= 1 && $type <= 3) && ($width > $rule_attributes[0])) 
                                {

                                    // add error message to indicated error block
                                    //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                    $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                    // the control does not validate
                                    $valid = false;

                                    // no further checking needs to be done for the control, making sure that only one
                                    // error message is displayed at a time for each erroneous control
                                    break 2;

                                }

                            }
                            elseif (
                                $attribute['type'] == 'file' &&
                                isset($this->text_field) &&
                                $this->text_field->attributes['value']!=''
                            )
                            {
                                // get some information about the file using URL
                                //list($width, $height, $type, $attr) = @getimagesize($_FILES[$attribute['name']]['tmp_name']);
                                $_result = @getimagesize($this->text_field->attributes['value']);

                                if ($_result!==false)
                                {
                                    list($width, $height, $type, $attr)=$_result;
                                    // if file is  an image or image is  gif, png or jpeg and larger than max-width
                                    if (($type !== false && $type >= 1 && $type <= 3) && ($width > $rule_attributes[0])) 
                                    {

                                        // add error message to indicated error block
                                        //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                        $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                        // the control does not validate
                                        $valid = false;

                                        // no further checking needs to be done for the control, making sure that only one
                                        // error message is displayed at a time for each erroneous control
                                        break 2;

                                    }
                                }
                            }

                            break;
                        
                        case 'image_max_height':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                $this->file_data && isset($this->file_data[$attribute['name']]) &&

                                // and file was uploaded without errors
                                //$_FILES[$attribute['name']]['error'] == 0
                                $this->file_data[$attribute['name']]['error'] == 0

                            ) {

                                // get some information about the file
                                //list($width, $height, $type, $attr) = @getimagesize($_FILES[$attribute['name']]['tmp_name']);
                                list($width, $height, $type, $attr) = @getimagesize($this->file_data[$attribute['name']]['tmp_name']);

                                // if file is  an image or image is  gif, png or jpeg and larger than max-height
                                if (($type !== false && $type >= 1 && $type <= 3) && ($height > $rule_attributes[0])) {

                                    // add error message to indicated error block
                                    //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                    $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                    // the control does not validate
                                    $valid = false;

                                    // no further checking needs to be done for the control, making sure that only one
                                    // error message is displayed at a time for each erroneous control
                                    break 2;

                                }

                            }
                            elseif (
                                $attribute['type'] == 'file' &&
                                isset($this->text_field) &&
                                $this->text_field->attributes['value']!=''
                            )
                            {
                                // get some information about the file using URL
                                //list($width, $height, $type, $attr) = @getimagesize($_FILES[$attribute['name']]['tmp_name']);
                                $_result = @getimagesize($this->text_field->attributes['value']);

                                if ($_result!==false)
                                {
                                    list($width, $height, $type, $attr)=$_result;
                                    // if file is  an image or image is  gif, png or jpeg and larger than max-height
                                    if (($type !== false && $type >= 1 && $type <= 3) && ($height > $rule_attributes[0])) 
                                    {

                                        // add error message to indicated error block
                                        //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                        $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                        // the control does not validate
                                        $valid = false;

                                        // no further checking needs to be done for the control, making sure that only one
                                        // error message is displayed at a time for each erroneous control
                                        break 2;

                                    }
                                }
                            }

                            break;
                        
                        // if "length"
                        case 'length':

                            // the rule will be considered as not obeyed when
                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                (
                                    // the length of the value exceeds boundaries
                                    strlen($attribute['value']) < $rule_attributes[0] ||

                                    // we use the utf8_decode because some characters have 2 bytes and some 3 bytes
                                    // read more at http://globalizer.wordpress.com/2007/01/16/utf-8-and-string-length-limitations/
                                    ($rule_attributes[1] > 0 && strlen(utf8_decode($attribute['value'])) > $rule_attributes[1])

                                )

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[2], $rule_attributes[3]);
                                $this->form->add_error($attribute['id'], $rule_attributes[3]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if rule is 'number'
                        case 'number':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                (

                                    // only minus given
                                    trim($attribute['value']) == '-' ||

                                    // has too many minus sign
                                    preg_match_all('/\-/', $attribute['value'], $matches) > 1 ||

                                    // not a number
                                    !preg_match('/^[0-9\-' . preg_quote($rule_attributes[0]) . ']+$/', $attribute['value']) ||

                                    // has a minus sign in it but is not at the very beginning
                                    (strpos($attribute['value'], '-') !== false && strpos($attribute['value'], '-') > 0)

                                )

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if "regexp"
                        case 'regexp':

                            if (

                                (
                                    // control is 'password'
                                    $attribute['type'] == 'password' ||

                                    // control is 'text'
                                    $attribute['type'] == 'text' ||

                                    // control is 'textarea'
                                    $attribute['type'] == 'textarea'

                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                // value does not match regular expression
                                !preg_match('/' . $rule_attributes[0] . '/', $attribute['value'])

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;

                        // if "regexp"
                        case 'url':

                            if (

                                (
                                    // control is 'text'
                                    $attribute['type'] == 'text'


                                ) &&

                                // a value was entered
                                $attribute['value'] != '' &&

                                // value does not match regular expression
                                !preg_match($rule_attributes[0], $attribute['value'])

                            ) {

                                // add error message to indicated error block
                                //$this->form->add_error($rule_attributes[1], $rule_attributes[2]);
                                $this->form->add_error($attribute['id'], $rule_attributes[2]);

                                // the control does not validate
                                $valid = false;

                                // no further checking needs to be done for the control, making sure that only one
                                // error message is displayed at a time for each erroneous control
                                break 2;

                            }

                            break;
                        
                        // if "required"
                        case 'required':

                            // if it's a drop-down that is part of a time control
                            if ($attribute['type'] == 'time') {

                                // if invalid format specified, revert to the default "hm"
                                if (preg_match('/^[hmsg]+$/i', $attribute['format']) == 0 || strlen(preg_replace('/([a-z]{2,})/i', '$1', $attribute['format'])) != strlen($attribute['format'])) $attribute['format'] = 'hm';

                                $regexp = '';

                                // build the regular expression for validating the time
                                for ($i = 0; $i < strlen($attribute['format']); $i++) {

                                    // for each characher in the format we use a particular regular expression
                                    switch (strtolower(substr($attribute['format'], $i, 1))) {

                                        case 'h':

                                            // if 12 hour format is used use this expression...
                                            if (strpos(strtolower($attribute['format']), 'g')) $regexp .= '0[1-9]|1[012]';

                                            // ...and different expression for the 24 hour format
                                            else $regexp .= '([0-1][0-9]|2[0-3])';

                                            break;

                                        case 'm':
                                        case 's':

                                            // regular expression for validating minutes and seconds
                                            $regexp .= '[0-5][0-9]';

                                            break;

                                        case 'g':

                                            // validate am/pm
                                            $regexp .= '(am|pm)';

                                            break;

                                    }

                                }

                                // if time does not validate
                                if (preg_match('/' . $regexp . '/i', str_replace(array(':', ' '), '', $attribute['value'])) == 0) {

                                    // add error message to indicated error block
                                    //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                    $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                    // the control does not validate
                                    $valid = false;

                                    // no further checking needs to be done for the control, making sure that only one
                                    // error message is displayed at a time for each erroneous control
                                    break 2;

                                }

                            // for other controls
                            } else {

                                // if control is 'select'
                                if ($attribute['type'] == 'select') {

                                    // as of PHP 5.3, array_shift required the argument to be a variable and not the result
                                    // of a function so we need this intermediary step
                                    $notSelectedIndex = array_keys($this->attributes['options']);

                                    // get the index which when selected indicated that 'nothing is selected'
                                    $notSelectedIndex = array_shift($notSelectedIndex);

                                }

                                // the rule will be considered as not obeyed when
                                if (

                                    // control is 'skype' and skypename is empty
                                    (($attribute['type'] == 'skype') && trim($attribute['value']['skypename']) == '') ||
                                    
                                    // control is 'password' or 'text' or 'textarea' and the 'value' attribute is empty
                                    (($attribute['type'] == 'password' || $attribute['type'] == 'text' || $attribute['type'] == 'textarea') && trim($attribute['value']) == '') ||

                                    // control is 'file' and no file specified
                                    //($attribute['type'] == 'file' && isset($_FILES[$attribute['name']]) && trim($_FILES[$attribute['name']]['name']) == '') ||

                                    ($attribute['type'] == 'file' && ((isset($this->file_data[$attribute['name']]) && trim($this->file_data[$attribute['name']]['name']) == '') || trim($this->text_field->attributes['value'])=='')) ||
                                    
                                    // control is 'checkbox' or 'radio' and the control was not submitted
                                    (($attribute['type'] == 'checkbox' || $attribute['type'] == 'radio') && $this->submitted_value === false) ||

                                    // control is 'select', the 'multiple' attribute is set and control was not submitted
                                    ($attribute['type'] == 'select' && isset($attribute['multiple']) && $this->submitted_value === false) ||

                                    // control is 'select', the 'multiple' attribute is not set and the select control's first value is selected
                                    ($attribute['type'] == 'select' && !isset($attribute['multiple']) && (is_array($this->submitted_value) || /*strcmp($control->submitted_value, $notSelectedIndex) == 0)*/ $this->submitted_value=='')) ||

                                    // control is 'select', the 'multiple' attribute is not set, the select control's value is "other" and the "other" control is empty
                                    ($attribute['type'] == 'select' && !isset($attribute['multiple']) && $this->submitted_value == 'other' && trim($method[$attribute['name'] . $this->form_properties['other_suffix']]) == '')

                                ) {

                                    // add error message to indicated error block
                                    //$this->form->add_error($rule_attributes[0], $rule_attributes[1]);
                                    $this->form->add_error($attribute['id'], $rule_attributes[1]);

                                    // the control does not validate
                                    $valid = false;

                                    // no further checking needs to be done for the control, making sure that only one
                                    // error message is displayed at a time for each erroneous control
                                    break 2;

                                }

                            }

                            break;

                        // if 'resize'
                        case 'resize':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                $this->file_data && isset($this->file_data[$attribute['name']]) &&

                                // and file was uploaded without any errors
                                //$_FILES[$attribute['name']]['error'] == 0
                                $this->file_data[$attribute['name']]['error'] == 0  &&
                                
                                !$this->form->preview


                            ) {

                                // as of PHP 5.3, array_shift required the argument to be a variable and not the result
                                // of a function so we need this intermediary step
                                $tmp = array_values($rule_attributes);

                                // if not multiple resize calls
                                // make it look like multiple resize call
                                if (!is_array(array_shift($tmp)))

                                    $rule_attributes = array($rule_attributes);

                                // iterate through the resize calls
                                foreach ($rule_attributes as $index => $rule_attribute)

                                    // as resizes are done only when the form is valid and after the file has been
                                    // uploaded, for now we only save some data that will be processed if the form is valid
                                    // (we're adding keys so that we don't have duplicate actions if validate_control method is called repeatedly)
                                    $this->actions[$attribute['name'] . '_resize_' . $index] = array(

                                        '_resize',          //  method that needs to be called
                                        //$attribute['name'], //  the file upload control's name
                                        $rule_attribute[0], //  prefix for the resized file
                                        $rule_attribute[1], //  width
                                        $rule_attribute[2], //  height
                                        $rule_attribute[3], //  preserve aspect ratio?
                                        $rule_attribute[4], //  method,
                                        $rule_attribute[5], //  background color
                                        $rule_attribute[6], //  enlarge smaller images?
                                        $rule_attribute[7], //  jpeg quality
                                        'block'     =>  $rule_attribute[8],  //  error block
                                        'message'   =>  $rule_attribute[9],  //  error message

                                    );

                            }

                            break;

                        // if 'upload'
                        case 'upload':

                            if (

                                // control is 'file'
                                $attribute['type'] == 'file' &&

                                // and a file was uploaded
                                //isset($_FILES[$attribute['name']]) &&
                                $this->file_data && isset($this->file_data[$attribute['name']]) &&

                                // and file was uploaded without any errors
                                //$_FILES[$attribute['name']]['error'] == 0
                                $this->file_data[$attribute['name']]['error'] == 0 &&
                                
                                !$this->form->preview


                            )

                                // as uploads are done only when the form is valid
                                // for now we only save some data that will be processed if the form is valid
                                // (we're adding keys so that we don't have duplicate actions if validate_control method is called repeatedly)
                                $this->actions[$attribute['name'] . '_upload'] = array(

                                    '_upload',                              //  method to be called
                                    //$attribute['name'],                   //  the file upload control's name
                                    $rule_attributes[0],                    //  the folder where the file to be uploaded to
                                    $rule_attributes[1],                    //  the folder where the file to be uploaded to
                                    $rule_attributes[2],                    //  should the original file name be preserved
                                    'block'     =>  $rule_attributes[3],    //  error block
                                    'message'   =>  $rule_attributes[4],    //  error message

                                );
                                elseif (
                                // control is 'file'
                                $attribute['type'] == 'file')
                                    $valid=$this->text_field->_validate();


                            break;

                    }

                }

            }

        }

        // do some extra checkings and cleanup
        if (

            //if type is password OR
            $attribute['type'] == 'password' ||

            //if type is text and has the "captcha" rule set
            ($attribute['type'] == 'text' && isset($this->rules['captcha']))

        // clear the value in the field
        ) $this->set_attributes(array('value' => ''));
        
        //if (!$valid)
          //  cred_log(print_r($this,true));
        $this->valid=$valid;
        return ($valid && $this->custom_valid);

    }
    
    /*function add_error()
    {
    }*/
    
    function _toHTML()
    {
    }
    
    function isDiscarded()
    {
        return $this->_isDiscarded;
    }
    
    function discard()
    {
        if ($this->form->isSubmitted())
        {
            // reference to the form submission method
            global ${'_' . $this->form_properties['method']};

            $method = & ${'_' . $this->form_properties['method']};
            
            $primename=$this->getPrimeName();
            if ($primename!==null && isset($method[$primename]))
                unset($method[$primename]);
            if (isset($this->attributes['name']) && isset($method[$this->attributes['name']]))
                unset($method[$this->attributes['name']]);
            if ($primename!==null && isset($_FILES[$primename]))
                unset($_FILES[$primename]);
            if (isset($this->attributes['name']) && isset($_FILES[$this->attributes['name']]))
                unset($_FILES[$this->attributes['name']]);
        }
        
        $this->_isDiscarded=true;
    }
    
    function addError($error_message)
    {
        $this->custom_valid=false;
        if (is_array($error_message))
        {
            foreach ($error_message as $errmsg)
                $this->form->add_error($this->attributes['id'], $errmsg);
        }    
        else
            $this->form->add_error($this->attributes['id'], $error_message);
    }
    
    function get_values_for_conditions()
    {
        return $this->get_values();
    }
    
    function get_values()
    {
        if (array_key_exists('value',$this->attributes))
        {
            $value=$this->attributes['value'];
            return $value;
        }
        return '';
    }
    
    function set_values($val)
    {
        $this->attributes['value']=$val;
    }
    
    function _getJS()
    {
        return '';
    }
    
    function getJS()
    {
        return $this->_getJS();
    }
    
    function toHTML()
    {
        // disable extra escaping for now
        /*if (isset($this->attributes['escape']) && $this->attributes['escape'])
        {
            $this->escape=true;
            unset($this->attributes['escape']);
        }*/
        
        $output='';
        //$output.=($this->isDiscarded())?"<br />DISCARDED<br />":"";
        //$output='<div class="row">';
        if (!($this->valid && $this->custom_valid))
        {
            $output.='<div class="myzebra-validation-error">';
            $errs='';
            if (isset($this->form->errors[$this->attributes['id']]))
            {
                $errs.='<ul class="myzebra-field-error-messages">';
                foreach ($this->form->errors[$this->attributes['id']] as $error_msg)
                {
                    $errs.='<li>'.$error_msg.'</li>';
                }
                $errs.='</ul>';
                
                unset($this->form->errors[$this->attributes['id']]);
            }
            $output.=$errs;
        }
        if (!empty($this->prime_name))
            $this->set_attributes(array(
                'class'=>'myzebra-prime-name-'.$this->prime_name
            ),false);
            
        $output.=$this->_toHTML();
        if (!($this->valid && $this->custom_valid))
        {
            $output.='</div>';
        }
        //$output.='</div>';
        return $output;
    }
    
    function doActions()
    {
        // if there are any actions to be performed when the form is valid
        // (file upload, resize, convert)
        $form_is_valid=true;
        if (isset($this->actions) && !empty($this->actions) && !$this->form->preview)

            // iterate through the actions
            foreach ($this->actions as $actions)

                // if the respective action (method) exists
                if (method_exists($this, $actions[0])) {

                    // if the method was erroneous
                    if (!call_user_func_array(array(&$this,$actions[0]), array_slice($actions, 1))) {

                        // add error message to indicated error block
                        //$this->form->add_error($actions['block'], $actions['message']);
                        $this->form->add_error($this->attributes['id'], $actions['message']);

                        // set the form as not being valid
                        $form_is_valid = false;
                        
                        break;

                    }

                // if the task (method) could not be found, trigger an error message
                } else _myzebra_form_show_error('Method ' . $actions[0] . ' does not exist!', E_USER_ERROR);
        
        return $form_is_valid;
    }

    /**
     *  Helper method for validating select boxes. It extract all the values from an infinitely nested array and puts
     *  them in an uni-dimensional array so that we can check if the submitted value is allowed.
     *
     *  @param  array   $array  The array to transform.
     *
     *  @return array           Returns the flat array.
     *
     *  @access private
     */
    function _extract_values($array)
    {

        $result = array();

        // iterate through the array's values
        foreach ($array as $index => $value)

            // if entry is an array, flatten array recursively
            if (is_array($value)) $result = array_merge($result, $this->_extract_values($value));

            // otherwise, add the index to the result array
            else $result[] = $index;

        // return found values
        return $result;

    }
    
    /**
     *  Converts an image from one type to another.
     *
     *  Note that this method will update the entries in the {@link $file_upload} property as the converted file will
     *  become the "uploaded" file!
     *
     *  @param  string  $control                The file upload control's name
     *
     *  @param  string  $type                   Type to convert an image to.
     *
     *                                          Can be (case-insensitive) JPG, PNG or GIF
     *
     *  @param  integer $jpeg_quality           (Optional) Indicates the quality of the output image (better quality
     *                                          means bigger file size).
     *
     *                                          Range is 0 - 100
     *
     *                                          Available only if <b>type</b> is "jpg".
     *
     *                                          Default is 85.
     *
     *  @param  integer $preserve_original_file (Optional) Should the original file be preserved after the conversion
     *                                          is done?
     *
     *                                          Default is FALSE.
     *
     *  @param  boolean $overwrite              (Optional) If a file with the same name as the converted file already
     *                                          exists, should it be overwritten or should the name be automatically
     *                                          computed.
     *
     *                                          If a file with the same name as the converted file already exists and
     *                                          this argument is FALSE, a suffix of "_n" (where n is an integer) will
     *                                          be appended to the file name.
     *
     *                                          Default is FALSE
     *
     *  @return boolean                         Returns TRUE on success or FALSE otherwise
     *
     *  @access private
     */
    function _convert($type, $jpeg_quality = 85, $preserve_original_file = false, $overwrite = false)
    {

        $attributes=$this->get_attributes(array('id','name'));
        // sanitize the control's name
        $attributes['name'] = preg_replace('/\[.*\]/', '', $attributes['name']);
        
        // if
        if (

            // file was uploaded
            isset($this->file_upload[$attributes['name']]) &&

            // and file is indeed an image file
            isset($this->file_upload[$attributes['name']]['imageinfo']) &&

            // we're trying to convert to a supported file type
            ($type == 'gif' || $type == 'png' || $type == 'jpg')

        ) {

            // get file's current name
            $current_file_name = substr($this->file_upload[$attributes['name']]['file_name'], 0, strrpos($this->file_upload[$attributes['name']]['file_name'], '.'));

            // get file's current extension
            $current_file_extension = strtolower(substr($this->file_data[$attributes['name']]['file_name'], strrpos($this->file_upload[$attributes['name']]['file_name'] + 1, '.')));

            // if extension is a variation of "jpeg", revert to default "jpg"
            if ($current_file_extension == 'jpeg') $current_file_extension = 'jpg';

            // make sure the new extension is also lowercase
            $type = strtolower($type);

            // if new extension is different than the file's current extension
            if ($type != $current_file_extension) {

                // if no overwrite and a file with the same name as the converted file already exists
                if (!$overwrite && is_file($this->file_upload[$attributes['name']]['path'] . $current_file_name . '.' . $type)) {

                    $suffix = '';

                    // knowing the suffix...
                    // loop as long as
                    while (

                        // a file with the same name exists in the upload folder
                        // (file_exists returns also TRUE if a folder with that name exists)
                        is_file($this->file_upload[$attributes['name']]['path'] . $current_file_name . $suffix . '.' . $type)

                    )

                        // if no suffix was yet set
                        if ($suffix === '')

                            // start the suffix like this
                            $suffix = '_1';

                        // if suffix was already initialized
                        else {

                            // drop the "_" from the suffix
                            $suffix = str_replace('_', '', $suffix);

                            // increment the suffix
                            $suffix = '_' . ++$suffix;

                        }

                    // the final file name
                    $current_file_name = $current_file_name . $suffix;

                }

                // if the image transformation class was not already instantiated
                if (!isset($this->form->MyZebra_Image))

                    // create a new instance of the image transformation class
                    $this->form->MyZebra_Image = new MyZebra_Image();

                // set the source file
                $this->MyZebra_Image->source_path = $this->file_upload[$attributes['name']]['path'] . $this->file_upload[$attributes['name']]['file_name'];

                // set the target file
                $this->form->MyZebra_Image->target_path = $this->file_upload[$attributes['name']]['path'] . $current_file_name . '.' . $type;

                // set the quality of the output image (better quality means bigger file size)
                // available only for jpeg files; ignored for other image types
                $this->form->MyZebra_Image->jpeg_quality = $jpeg_quality;

                // if there was an error when resizing the image, return false
                if (!$this->form->MyZebra_Image->resize(0, 0)) return false;

                // update entries in the file_upload property

                // get the size of the new file
                $this->file_upload[$attributes['name']]['size'] = filesize($this->form->MyZebra_Image->target_path);

                // update the file name (the file was converted and has a new extension)
                $this->file_upload[$attributes['name']]['file_name'] = $current_file_name . '.' . $type;

                // get some info about the new file
                $imageinfo = @getimagesize($this->form->MyZebra_Image->target_path);

                // rename some of the attributes returned by getimagesize
                $imageinfo['width'] = $imageinfo[0]; unset($imageinfo[0]);

                $imageinfo['height'] = $imageinfo[1]; unset($imageinfo[1]);

                $imageinfo['type'] = $imageinfo[2]; unset($imageinfo[2]);

                $imageinfo['html'] = $imageinfo[3]; unset($imageinfo[3]);

                // append image info to the file_upload property
                $this->file_upload[$$attributes['name']]['imageinfo'] = $imageinfo;

                // update the mime type as returned by getimagesize
                $this->file_upload[$attributes['name']]['type'] = $imageinfo['mime'];

                // if original file is not to be preserved, delete original file
                if (!$preserve_original_file && !$overwrite) @unlink($this->form->MyZebra_Image->source_path);

            }

        }

        // if the script gets this far, it means that everything went as planned and we return true
        return true;

    }

    /**
     *  Resize an uploaded image
     *
     *  This method will do nothing if the file is not a supported image file.
     *
     *  @param  string  $control                The file upload control's name
     *
     *  @param  string  $prefix                 If the resized image is to be saved as a new file and the originally
     *                                          uploaded file needs to be preserved, specify a prefix to be used for the
     *                                          new file. This way, the resized image will have the same name as the
     *                                          original file but prefixed with the given value (i.e. "thumb_").
     *
     *                                          Specifying an empty string as argument will instruct the script to apply
     *                                          the resizing to the uploaded image and therefore overwriting the
     *                                          originally uploaded file.
     *
     *  @param  integer $width                  The width to resize the image to.
     *
     *                                          If set to <b>0</b>, the width will be automatically adjusted, depending
     *                                          on the value of the <b>height</b> argument so that the image preserves
     *                                          its aspect ratio.
     *
     *                                          If <b>preserve_aspect_ratio</b> is set to TRUE and both this and the
     *                                          <b>height</b> arguments are values greater than <b>0</b>, the image will
     *                                          be resized to the exact required width and height and the aspect ratio
     *                                          will be preserved (see the description for the <b>method</b> argument
     *                                          below on how can this be done).
     *
     *                                          If <b>preserve_aspect_ratio</b> is set to FALSE, the image will be
     *                                          resized to the required width and the aspect ratio will be ignored.
     *
     *                                          If both <b>width</b> and <b>height</b> are set to <b>0</b>, a copy of
     *                                          the source image will be created (<b>jpeg_quality</b> will still apply).
     *
     *                                          If either <b>width</b> or <b>height</b> are set to <b>0</b>, the script
     *                                          will consider the value of the {@link preserve_aspect_ratio} to bet set
     *                                          to TRUE regardless of its actual value!
     *
     *  @param  integer $height                 The height to resize the image to.
     *
     *                                          If set to <b>0</b>, the height will be automatically adjusted, depending
     *                                          on the value of the <b>width</b> argument so that the image preserves
     *                                          its aspect ratio.
     *
     *                                          If <b>preserve_aspect_ratio</b> is set to TRUE and both this and the
     *                                          <b>width</b> arguments are values greater than <b>0</b>, the image will
     *                                          be resized to the exact required width and height and the aspect ratio
     *                                          will be preserved (see the description for the <b>method</b> argument
     *                                          below on how can this be done).
     *
     *                                          If <b>preserve_aspect_ratio</b> is set to FALSE, the image will be
     *                                          resized to the required height and the aspect ratio will be ignored.
     *
     *                                          If both <b>height</b> and <b>width</b> are set to <b>0</b>, a copy of
     *                                          the source image will be created (<b>jpeg_quality</b> will still apply).
     *
     *                                          If either <b>height</b> or <b>width</b> are set to <b>0</b>, the script
     *                                          will consider the value of the {@link preserve_aspect_ratio} to bet set
     *                                          to TRUE regardless of its actual value!
     *
     *  @param  boolean $preserve_aspect_ratio  (Optional) If set to TRUE, the image will be resized to the given width
     *                                          and height and the aspect ratio will be preserved.
     *
     *                                          Set this to FALSE if you want the image forcefully resized to the exact
     *                                          dimensions given by width and height ignoring the aspect ratio
     *
     *                                          Default is TRUE.
     *
     *  @param  int     $method                 (Optional) Method to use when resizing images to exact width and height
     *                                          while preserving aspect ratio.
     *
     *                                          If the $preserve_aspect_ratio property is set to TRUE and both the
     *                                          <b>width</b> and <b>height</b> arguments are values greater than <b>0</b>,
     *                                          the image will be resized to the exact given width and height and the
     *                                          aspect ratio will be preserved by using on of the following methods:
     *
     *                                          -   <b>ZEBRA_IMAGE_BOXED</b> - the image will be scalled so that it will
     *                                              fit in a box with the given width and height (both width/height will
     *                                              be smaller or equal to the required width/height) and then it will
     *                                              be centered both horizontally and vertically. The blank area will be
     *                                              filled with the color specified by the <b>$background_color</b>
     *                                              argument. (the blank area will be filled only if the image is not
     *                                              transparent!)
     *
     *                                          -   <b>ZEBRA_IMAGE_NOT_BOXED</b> - the image will be scalled so that it
     *                                              <i>could</i> fit in a box with the given width and height but will
     *                                              not be enclosed in a box with given width and height. The new width/
     *                                              height will be both smaller or equal to the required width/height
     *
     *                                          -   <b>ZEBRA_IMAGE_CROP_TOPLEFT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_TOPCENTER</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_TOPRIGHT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_MIDDLELEFT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_CENTER</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_MIDDLERIGHT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_BOTTOMLEFT</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_BOTTOMCENTER</b>
     *                                          -   <b>ZEBRA_IMAGE_CROP_BOTTOMRIGHT</b>
     *
     *                                          For the methods involving crop, first the image is scaled so that both
     *                                          its sides are equal or greater than the respective sizes of the bounding
     *                                          box; next, a region of required width and height will be cropped from
     *                                          indicated region of the resulted image.
     *
     *                                          Default is ZEBRA_IMAGE_BOXED
     *
     *  @param  boolean $background_color       (Optional) The hexadecimal color of the blank area (without the #).
     *                                          See the <b>method</b> argument.
     *
     *                                          Default is 'FFFFFF'
     *
     *  @param  boolean $enlarge_smaller_images (Optional) If set to FALSE, images having both width and height smaller
     *                                          than the required width and height, will be left untouched ({@link jpeg_quality}
     *                                          will still apply).
     *
     *                                          Default is TRUE
     *
     *  @param  boolean $quality                (Optional) Indicates the quality of the output image (better quality
     *                                          means bigger file size).
     *
     *                                          Range is 0 - 100
     *
     *                                          Available only for JPEG files.
     *
     *                                          Default is 85
     *
     *  @return boolean                         Returns TRUE on success or FALSE otherwise
     *
     *  @access private
     */
    function _resize($prefix, $width, $height, $preserve_aspect_ratio = true, $method = ZEBRA_IMAGE_BOXED, $background_color = 'FFFFFF', $enlarge_smaller_images = true, $jpeg_quality = 85)
    {

        $attributes=$this->get_attributes(array('id','name'));
        // sanitize the control's name
        $attributes['name'] = preg_replace('/\[.*\]/', '', $attributes['name']);
        
        // if
        if (

            // file was uploaded
            isset($this->file_upload[$attributes['name']]) &&

            // and file is indeed an image file
            isset($this->file_upload[$attributes['name']]['imageinfo'])

        ) {

            // if the image transformation class was not already instantiated
            if (!isset($this->form->MyZebra_Image))

                // create a new instance of the image transformation class
                $this->form->MyZebra_Image = new MyZebra_Image();

            // set the file permissions as per MyZebra_Form's settings
            $this->form->MyZebra_Image->chmod_value = $this->form->file_upload_permissions;

            // set the source file
            $this->form->MyZebra_Image->source_path = $this->file_upload[$attributes['name']]['path'] . $this->file_upload[$attributes['name']]['file_name'];

            // set the target file
            $this->form->MyZebra_Image->target_path = $this->file_upload[$attributes['name']]['path'] . trim($prefix) . $this->file_upload[$attributes['name']]['file_name'];

            // set whether aspect ratio should be maintained or not
            $this->form->MyZebra_Image->maintain_ratio = $preserve_aspect_ratio;

            // set the quality of the output image (better quality means bigger file size)
            // available only for jpeg files; ignored for other image types
            $this->form->MyZebra_Image->jpeg_quality = $jpeg_quality;

            // should smaller images be enlarged?
            $this->form->MyZebra_Image->enlarge_smaller_images = $enlarge_smaller_images;

            // if there was an error when resizing the image, return false
            if (!$this->form->MyZebra_Image->resize($width, $height, $method, $background_color)) return false;

        }

        // if the script gets this far, it means that everything went as planned and we return true
        return true;

    }

    /**
     *  Uploads a file
     *
     *  @param  string  $control                The file upload control's name
     *
     *  @param  string  $path                   The path where the file to be uploaded to
     *
     *  @param  boolean $filename               (Optional) Specifies whether the uploaded file's original name should be
     *                                          preserved, should it be prefixed with a string, or should it be randomly
     *                                          generated.
     *
     *                                          Possible values can be
     *
     *                                          -   TRUE - the uploaded file's original name will be preserved;
     *                                          -   FALSE (or, for better code readability, you should use the "MYZEBRA_FORM_UPLOAD_RANDOM_NAMES"
     *                                              constant instead of "FALSE")- the uploaded file will have a randomly generated name;
     *                                          -   a string - the uploaded file's original name will be preserved but
     *                                              it will be prefixed with the given string (i.e. "original_", or "tmp_")
     *
     *                                          Note that when set to TRUE or a string, a suffix of "_n" (where n is an
     *                                          integer) will be appended to the file name if a file with the same name
     *                                          already exists at the given path.
     *
     *                                          Default is TRUE
     *
     *  @return boolean                         Returns TRUE on success or FALSE otherwise
     *
     *  @access private
     */
    function _upload($folder, $url, $filename = true)
    {
        //print_r('External Upload: '.$this->attributes['external_upload']);
        //return true;
        if (isset($this->attributes['external_upload']) && $this->attributes['external_upload'])
            return true; // bypass upload
        
        $attributes=$this->get_attributes(array('id','name'));
        // sanitize the control's name
        $attributes['name'] = preg_replace('/\[.*\]/', '', $attributes['name']);
        
        // trim trailing slash from folder
        $folder = rtrim($folder, '\\/');

        // if upload folder does not have a trailing slash, add the trailing slash
        $folder = $folder . (substr($folder, -1) != DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '');

        // if
        if (

            // the file upload control with the given name exists
            isset($this->file_data[$attributes['name']]) &&

            // file is ready to be uploaded
            $this->file_data[$attributes['name']]['error'] == 0 &&

            // the upload folder exists
            is_dir($folder)

        ) {

            // if file names should be random
            if ($filename === MYZEBRA_FORM_UPLOAD_RANDOM_NAMES)

                // generate a random name for the file we're about to upload
                $file_name = md5(mt_rand() . microtime() . $this->file_data[$attributes['name']]['name']) . (strrpos($this->file_data[$attributes['name']]['name'], '.') !== false ? substr($this->file_data[$attributes['name']]['name'], strrpos($this->file_data[$attributes['name']]['name'], '.')) : '');

            // if file names are to be preserved
            else {

                // if the file we are about to upload does have an extension
                if (strrpos($this->file_data[$attributes['name']]['name'], '.') !== false) {

                    // split the file name into "file name"...
                    $file_name = substr($this->file_data[$attributes['name']]['name'], 0, strrpos($this->file_data[$attributes['name']]['name'], '.'));

                    // ...and "file extension"
                    $file_extension = substr($this->file_data[$attributes['name']]['name'], strrpos($this->file_data[$attributes['name']]['name'], '.'));

                // if the file we are about to upload does not have an extension
                } else {

                    // the file name will be the actual file name...
                    $file_name = $this->file_data[$attributes['name']]['name'];

                    // ...while the extension will be an empty string
                    $file_extension = '';

                }

                // prefix the file name if required
                $file_name = ($filename !== true ? $filename : '') . $file_name;

                $suffix = '';

                // knowing the suffix...
                // loop as long as
                while (

                    // a file with the same name exists in the upload folder
                    // (file_exists returns also TRUE if a folder with that name exists)
                    is_file($folder . $file_name . $suffix . $file_extension)

                ) {

                    // if no suffix was yet set
                    if ($suffix === '')

                        // start the suffix like this
                        $suffix = '_1';

                    // if suffix was already initialized
                    else {

                        // drop the "_" from the suffix
                        $suffix = str_replace('_', '', $suffix);

                        // increment the suffix
                        $suffix = '_' . ++$suffix;

                    }

                }

                // the final file name
                $file_name = $file_name . $suffix . $file_extension;

            }

            // if file could be uploaded
            if (@move_uploaded_file($this->file_data[$attributes['name']]['tmp_name'], $folder . $file_name)) {
               //cred_log($folder . $file_name);
                // get a list of functions disabled via configuration
                $disabled_functions = @ini_get('disable_functions');

                // if the 'chmod' function is not disabled via configuration
                if ($disabled_functions != '' && strpos('chmod', $disabled_functions) === false)

                    // chmod the file
                    chmod($folder . $file_name, intval($this->form->file_upload_permissions, 8));

                // set a special property
                // the value of the property will be an array will information about the uploaded file
                $this->file_upload=array();
                $this->file_upload[$attributes['name']] = $this->file_data[$attributes['name']];

                $this->file_upload[$attributes['name']]['path'] = $folder;
                $this->file_upload[$attributes['name']]['url'] = $url;

                $this->file_upload[$attributes['name']]['file_name'] = $file_name;

                // if uploaded file is an image
                if ($imageinfo = @getimagesize($folder . $this->file_upload[$attributes['name']]['file_name'])) {

                    // rename some of the attributes returned by getimagesize
                    $imageinfo['width'] = $imageinfo[0]; unset($imageinfo[0]);

                    $imageinfo['height'] = $imageinfo[1]; unset($imageinfo[1]);

                    $imageinfo['type'] = $imageinfo[2]; unset($imageinfo[2]);

                    $imageinfo['html'] = $imageinfo[3]; unset($imageinfo[3]);

                    // append image info to the file_upload property
                    $this->file_upload[$attributes['name']]['imageinfo'] = $imageinfo;

                }

                // return true, as everything went as planned
                return true;

            }

        }

        // if script gets this far, return false as something must've gone wrong
       //cred_log('Upload failed');
        return false;

    }


}

?>
