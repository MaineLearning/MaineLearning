<?php

/**
 *  Class for text controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
if (!defined('RECAPTCHA_API_SERVER'))
    require_once(dirname(dirname(__FILE__)).'/recaptcha-php-1.11/recaptchalib.php');

class MyZebra_Form_Recaptcha extends MyZebra_Form_Control
{

    /**
     *  Adds an <input type="text"> control to the form.
     *
     *  <b>Do not instantiate this class directly! Use the {@link MyZebra_Form::add() add()} method instead!</b>
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
     *                                  // for a control named "my_text", one would use:
     *                                  echo $my_text;
     *                                  </code>
     *
     *  @param  string  $default        (Optional) Default value of the text box.
     *
     *  @param  array   $attributes     (Optional) An array of attributes valid for
     *                                  {@link http://www.w3.org/TR/REC-html40/interact/forms.html#h-17.4 input}
     *                                  controls (size, readonly, style, etc)
     *
     *                                  Must be specified as an associative array, in the form of <i>attribute => value</i>.
     *                                  <code>
     *                                  // setting the "readonly" attribute
     *                                  $obj = &$form->add(
     *                                      'text',
     *                                      'my_text',
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
    
    // Get a key from https://www.google.com/recaptcha/admin/create
    
    var $_captcha_error=null;
    
    function MyZebra_Form_Recaptcha($id, $messages, $attributes = '')
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
            'default_value',
            'open',
            'repeatable',
            'value',
            'public_key',
            'private_key',
            'error_message',
            'show_link',
            'no_keys'

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(

                'disable_spam_filter'=>false,
                'disable_xss_filters'=>false,
		        'type'      =>  'text',
                'name'      =>  $id,
                'id'        =>  preg_replace('/\[.*\]$/', '', $id),
                'value'     =>  '',
                'class'     =>  'myzebra-control myzebra-recaptcha',
                'open'      => false,
                'public_key'=>'',
                'private_key'=>'',
                'error_message'=>'',
                'show_link'=>'',
                'no_keys'=>''

		    )

		);
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
    }
    
    function get_submitted_value()
    {
        if (empty($this->attributes['public_key']) || empty($this->attributes['private_key']))
            return;
            
        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};
        
        $this->submitted_value=null;
        if (array_key_exists("recaptcha_response_field",$method) && array_key_exists("recaptcha_challenge_field",$method))
        {
            $this->submitted_value=array(
                            'response'=>$method["recaptcha_response_field"],
                            'challenge'=>$method["recaptcha_challenge_field"]
                            );
            
            //unset($method["recaptcha_response_field"]);
            //unset($method["recaptcha_challenge_field"]);
        }
    }
    
    function validate()
    {
        
        if (empty($this->attributes['public_key']) || empty($this->attributes['private_key']))
            return true;  // captcha not active with no keys
            
        // was there a reCAPTCHA response?
        $this->get_submitted_value();
        $this->valid=false;
        $valid=false;
        if (isset($this->submitted_value)) 
        {
            $resp = recaptcha_check_answer ($this->attributes['private_key'],
                                            $_SERVER["REMOTE_ADDR"],
                                            $this->submitted_value['challenge'],
                                            $this->submitted_value['response']);

            if (!$resp->is_valid) {
                // set the error code so that we can display it
                $this->_captcha_error = $resp->error;
            }
            $valid=$resp->is_valid;
        }
        $this->valid=$valid;
        if (!$this->valid)
        {
            $this->form->add_error($this->attributes['id'], $this->attributes['error_message']);
        }
        return $this->valid;
    }
    
    function _getJS()
    {
        ob_start();
        ?>
          jQuery('#<?php echo $this->attributes['id']; ?>').find('.myzebra-captcha_show').eq(0).click(function(){
            jQuery('.myzebra-captcha_show').show();
            jQuery(this).hide();
            Recaptcha.create('<?php echo $this->attributes['public_key']; ?>', '<?php echo $this->attributes['id'].'_actual_captcha'; ?>', {
                //callback: Recaptcha.focus_response_field,
                tabindex: 1,
                theme: "white"
            });
          });
          <?php if ($this->attributes['open']) { ?>
          // open it
          jQuery('#<?php echo $this->attributes['id']; ?>').find('.myzebra-captcha_show').eq(0).trigger('click');
          <?php } ?>
        <?php
        return ob_get_clean();
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
        if (empty($this->attributes['public_key']) || empty($this->attributes['private_key']))
            return '<strong>'.$this->attributes['no_keys'].'</strong>';
            
        //return recaptcha_get_html($this->publickey, $this->_captcha_error);
        unset($this->attributes['value']);
        ob_start();
        ?>
        <div <?php echo $this->_render_attributes(); ?>>
        <a href='javascript:void(0);' class='myzebra-captcha_show'><?php echo $this->attributes['show_link']; ?></a>
        <div id='<?php echo $this->attributes['id'].'_actual_captcha'; ?>'>
        </div>
        </div>
        <?php
        
        return ob_get_clean();
    }

}

?>
