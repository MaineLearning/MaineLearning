<?php

/**
 *  Class for skype controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Skype extends MyZebra_Form_Control
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
    function MyZebra_Form_Skype($id, $default = '', $attributes = '')
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
            'edit_skype_text',
            '_nonce',
            'ajax_url',
            'repeatable'

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(

                'disable_spam_filter'=>false,
                'disable_xss_filters'=>false,
		        'type'      =>  'skype',
                'name'      =>  $id,
                'id'        =>  preg_replace('/\[.*\]$/', '', $id),
                'value'     =>  array(),
                'class'     =>  'myzebra-control myzebra-skype myzebra-text',
                'edit_skype_text'=>'',
                '_nonce'=>'',
                'ajax_url'=>''

		    )

		);
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
        
    }
    
    /**
     * Returns HTML formatted skype button.
     * 
     * @param type $skypename
     * @param type $template
     * @return type 
     */
    function _get_skype_button_html_preview($skypename, $template = '') 
    {

        if (empty($skypename)) {
            return '';
        }
        
        $skypename=$this->extra_xss('attr',$skypename);
        
        switch ($template) {

            case 'btn1':
    // Call me big drawn
                $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
    <a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_green_white_153x63.png" style="border: none;" width="153" height="63" alt="Skype Me™!" /></a>';
                break;

            case 'btn4':
    // Call me small
                $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
    <a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_blue_transparent_34x34.png" style="border: none;" width="34" height="34" alt="Skype Me™!" /></a>';
                break;

            case 'btn3':
    // Call me small drawn
                $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
    <a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_green_white_92x82.png" style="border: none;" width="92" height="82" alt="Skype Me™!" /></a>';
                break;

            case 'btn6':
    // Status
                $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
    <a href="skype:' . $skypename . '?call"><img src="http://mystatus.skype.com/bigclassic/' . $skypename . '" style="border: none;" width="182" height="44" alt="My status" /></a>';
                break;

            case 'btn5':
    // Status drawn
                $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
    <a href="skype:' . $skypename . '?call"><img src="http://mystatus.skype.com/balloon/' . $skypename . '" style="border: none;" width="150" height="60" alt="My status" /></a>';
                break;

            default:
    // Call me big
                $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
    <a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_blue_white_124x52.png" style="border: none;" width="124" height="52" alt="Skype Me™!" /></a>';
                break;
        }

        return $output;
    }
   
   function get_values()
   {
        return $this->attributes['value'];
   }
   
   function set_values($val)
   {
        $this->attributes['value']=$val;
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
        if ($this->attributes['value'])
        {
            $skypename=isset($this->attributes['value']['skypename'])?$this->attributes['value']['skypename']:'';
            $style=isset($this->attributes['value']['style'])?$this->attributes['value']['style']:'';
        }
        else
        {
            $skypename='';
            $style='';
        }
        $text=$this->attributes['edit_skype_text'];
        $ajax_url=$this->attributes['ajax_url'];
        $nonce=$this->attributes['_nonce'];
        $id=$this->attributes['id'];
        $name=$this->attributes['name'];
        if (preg_match('/(\[[^\[\]]*\]$)/',$name,$aa))
        {
            $aa=$aa[1];
            $name=str_replace($aa,'',$name);
        }
        else
            $aa='';
        $preview=$this->_get_skype_button_html_preview($skypename, $style);
        ob_start();
        ?>
        <div id="<?php echo $id; ?>" class="myzebra-skype-container">
            <input type="text" name="<?php echo $name.'[skypename]'.$aa; ?>" id="<?php echo $id; ?>-skypename" class="<?php echo $this->attributes['class']; ?>" value="<?php echo $this->extra_xss('attr',$skypename); ?>" />
            <input name="<?php echo $name.'[style]'.$aa; ?>" id="<?php echo $id; ?>-style" type="hidden" class='myzebra-hidden' value="<?php echo $this->extra_xss('attr', $style); ?>" />
            <div id="<?php echo $id; ?>-preview" class="myzebra-skype-preview-image-container">
                <?php if ($preview) echo $preview; ?>
            </div>
            <a title="<?php echo $text; ?>" class="myzebra-edit-skype myzebra-wp-button-secondary thickbox" href="<?php echo $ajax_url; ?>?action=cred_skype_ajax&amp;wpcf_action=insert_skype_button&amp;_wpnonce=<?php echo $nonce; ?>&amp;update=<?php echo $id; ?>&amp;skypename=<?php echo urlencode($this->extra_xss('attr',$skypename)); ?>&amp;style=btn5&amp;keepThis=true&amp;TB_iframe=true&amp;width=640&amp;height=450"><?php echo $text; ?></a>
        </div>        
        <?php
        return ob_get_clean();
    }

}
?>
