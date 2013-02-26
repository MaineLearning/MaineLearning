<?php

/**
 *  Class for CAPTCHA controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */

class MyZebra_Form_Captcha extends MyZebra_Form_Control
{

    /**
     *  Adds a CAPTCHA image to the form.
     *
     *  <b>Do not instantiate this class directly! Use the {@link MyZebra_Form::add() add()} method instead!</b>
     *
     *  <b>You must also place a {@link MyZebra_Form_Text textbox} control on the form and set the "captcha" rule to it!
     *  (through {@link set_rule()})</b>
     *
     *  Properties of the CAPTCHA image can be altered by editing the file includes/captcha.php.
     *
     *  <code>
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // add a CAPTCHA image
     *  $form->add('captcha', 'my_captcha', 'my_text');
     *
     *  // add a label for the textbox
     *  $form->add('label', 'label_my_text', 'my_text', 'Are you human?');
     *
     *  // add a CAPTCHA to the form
     *  // the "&" symbol is there so that $obj will be a reference to the object in PHP 4;
     *  // for PHP 5+ there is no need for it
     *  $obj = &$form->add('text', 'my_text');
     *
     *  // set the "captcha" rule to the textbox
     *  $obj->set_rule(array(
     *      'captcha' => array('error', 'Characters not entered correctly!')
     *  ));
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
     *                                  This is the name of the variable to be used in the template file, containing
     *                                  the generated HTML for the control.
     *
     *                                  <code>
     *                                  // in a template file, in order to print the generated HTML
     *                                  // for a control named "my_captcha", one would use:
     *                                  echo $my_captcha;
     *                                  </code>
     *
     *  @param  string  $attach_to      The <b>id</b> attribute of the {@link MyZebra_Form_Text textbox} control to attach
     *                                  the CAPTCHA image to.
     *
     *  @return void
     */
    function MyZebra_Form_Captcha($id, $attach_to)
    {

        // call the constructor of the parent class
        parent::MyZebra_Form_Control();
        
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        // and will not be rendered by the _render_attributes() method
        $this->private_attributes = array(

            'disable_xss_filters',
            'for',
            'locked',
            'repeatable'

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(
            
                'type'      =>  'captcha',
                'name'      =>  $id,
                'id'        =>  preg_replace('/\[.*\]$/', '', $id),
                'for'       =>  $attach_to,

            )
            
        );

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
    
        return '<img src="' . $this->form_properties['assets_url'] . 'process.php?captcha=1&amp;nocache=' . time() . '" alt=""' . ($this->form_properties['doctype'] == 'xhtml' ? '/' : '') . '>';
    
    }
    
}

?>
