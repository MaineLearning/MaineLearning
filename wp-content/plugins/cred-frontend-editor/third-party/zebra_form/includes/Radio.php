<?php

/**
 *  Class for radio button controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Radio extends MyZebra_Form_Control
{

    /**
     *  Adds an <input type="radio"> control to the form.
     *
     *  <b>Do not instantiate this class directly! Use the {@link MyZebra_Form::add() add()} method instead!</b>
     *
     *  <code>
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // single radio button
     *  // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
     *  // for PHP 5+ there is no need for it
     *  $obj = &$form->add('radio', 'my_radio', 'my_radio_value');
     *
     *  // multiple radio buttons
     *  // notice that is "radios" instead of "radio"
     *  // label controls will be automatically created having the names "more_radios_value_1",
     *  // "more_radios_value_2" and so on
     *  // $obj is a reference to the first radio button
     *  // radio button values will be "0", "1" and "2", respectively
     *  $obj = &$form->add('radios', 'more_radios',
     *      array(
     *          'Value 1',
     *          'Value 2',
     *          'Value 3'
     *      )
     *  );
     *
     *  // multiple radio buttons with specific indexes
     *  // radio buttons' values will be "v1", "v2" and "v3", respectively
     *  // label controls will be automatically created having the names "some_more_radios_value_1",
     *  // "some_more_radios_value_2" and so on
     *  $obj = &$form->add('radios', 'some_more_radios',
     *      array(
     *          'v1' => 'Value 1',
     *          'v2' => 'Value 2',
     *          'v3' => 'Value 3'
     *      )
     *  );
     *
     *  // multiple radio buttons with preselected value
     *  // "Value 2" will be the preselected value
     *  // note that for preselecting values you must use the actual indexes of the values, if available, (like
     *  // in the current example) or the default, zero-based index, otherwise (like in the next example)
     *  // label controls will be automatically created having the names "and_some_more_radios_value_v1",
     *  // "and_some_more_radios_value_v2" and so on
     *  $obj = &$form->add('radios', 'and_some_more_radios',
     *      array(
     *          'v1'    =>  'Value 1',
     *          'v2'    =>  'Value 2',
     *          'v3'    =>  'Value 3'
     *      ),
     *      'v2'    // note the index!
     *  );
     *
     *  // "Value 2" will be the preselected value.
     *  // note that for preselecting values you must use the actual indexes of the values, if available, (like
     *  // in the example above) or the default, zero-based index, otherwise (like in the current example)
     *  // label controls will be automatically created having the names "and_some_more_radios_value_0",
     *  // "and_some_more_radios_value_1" and so on
     *  $obj = &$form->add('radios', 'and_some_more_checkboxes',
     *      array(
     *          'Value 1',
     *          'Value 2',
     *          'Value 3'
     *      ),
     *      1    // note the index!
     *  );
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
     *  <samp>By default, for checkboxes, radio buttons and select boxes, the library will prevent the submission of other
     *  values than those declared when creating the form, by triggering the error: "SPAM attempt detected!". Therefore,
     *  if you plan on adding/removing values dynamically, from JavaScript, you will have to call the
     *  {@link MyZebra_Form_Control::disable_spam_filter() disable_spam_filter()} method to prevent that from happening!</samp>
     *
     *  @param  string  $id             Unique name to identify the control in the form.
     *
     *                                  The control's <b>name</b> attribute will be as indicated by <i>$id</i>
     *                                  argument while the control's <b>id</b> attribute will be <i>$id</i> followd by an
     *                                  underscore and followed by <i>$value</i> with all the spaces replaced by
     *                                  <i>underscores</i>.
     *
     *                                  So, if the <i>$id</i> arguments is "my_radio" and the <i>$value</i> argument
     *                                  is "value 1", the control's <b>id</b> attribute will be <b>my_radio_value_1</b>.
     *
     *                                  This is the name to be used when referring to the control's value in the
     *                                  POST/GET superglobals, after the form is submitted.
     *
     *                                  This is also the name of the variable to be used in custom template files, in
     *                                  order to display the control.
     *
     *                                  <code>
     *                                  // in a template file, in order to print the generated HTML
     *                                  // for a control named "my_radio" and having the value of "value 1",
     *                                  // one would use:
     *                                  echo $my_radio_value_1;
     *                                  </code>
     *
     *                                  <i>Note that when adding the required rule to a group of radio buttons (radio
     *                                  buttons sharing the same name), it is sufficient to add the rule to the first
     *                                  radio button!</i>
     *
     *  @param  mixed   $value          Value of the radio button.
     *
     *  @param  array   $attributes     (Optional) An array of attributes valid for
     *                                  {@link http://www.w3.org/TR/REC-html40/interact/forms.html#h-17.4 input}
     *                                  controls (disabled, readonly, style, etc)
     *
     *                                  Must be specified as an associative array, in the form of <i>attribute => value</i>.
     *                                  <code>
     *                                  // setting the "checked" attribute
     *                                  $obj = &$form->add(
     *                                      'radio',
     *                                      'my_radio',
     *                                      'v1',
     *                                      array(
     *                                          'checked' => 'checked'
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
    function MyZebra_Form_Radio($id, $value, $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::MyZebra_Form_Control();
    
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        // and will not be rendered by the _render_attributes() method
        $this->private_attributes = array(

            'disable_spam_filter',
            'disable_xss_filters',
            'locked',
            'repeatable',
            'actual_value'

        );

        // set the default attributes for the radio button control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(

                'disable_spam_filter'=>false,
                'disable_xss_filters'=>false,
		        'type'  =>  'radio',
                'name'  =>  $id,
                'id'    =>  str_replace(' ', '_', preg_replace('/\[.*\]$/', '', $id)) . '_' . str_replace(' ', '_', $value),
                'value' =>  $value,
                'class' =>  'myzebra-control myzebra-radio',
                'actual_value'=>''

		    )

		);

        // sets user specified attributes for the control
        $this->set_attributes($attributes);

    }
    
    function get_values_for_conditions()
    {
        if (isset($this->attributes['checked']) && isset($this->attributes['actual_value']) && !empty($this->attributes['actual_value']))
            return $this->attributes['actual_value'];
        return $this->get_values();
    }
    
    function get_values()
    {
        if (isset($this->attributes['checked']))
            return $this->attributes['value'];
        return '';
    }
    
    function set_values($val)
    {
        if ($this->attributes['value']==$val)
            $radio->set_attributes(array('checked' => 'checked'));
        else
        {
            if (isset($this->attributes['checked']))
                unset($this->attributes['checked']);
        }
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

        //$output=print_r($this->attributes['actual_value'],true);
        return '<label class="myzebra-style-label"><input ' . $this->_render_attributes() . ($this->form_properties['doctype'] == 'xhtml' ? '/' : '') . '><span class="myzebra-radio-replace"></span></label>';

    }

}

?>
