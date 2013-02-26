<?php

/**
 *  Class for file upload controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_File extends MyZebra_Form_Control
{

    var $file_data=false;
    var $file_upload=false;
    
    var $text_field=false;
    
    /**
     *  Adds an <input type="file"> control to the form.
     *
     *  <b>Do not instantiate this class directly! Use the {@link MyZebra_Form::add() add()} method instead!</b>
     *
     *  <code>
     *  // create a new form
     *  $form = new MyZebra_Form('my_form');
     *
     *  // add a file upload control to the form
     *  // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
     *  // for PHP 5+ there is no need for it
     *  $obj = &$form->add('file', 'my_file_upload');
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
     *                                  // for a control named "my_file_upload", one would use:
     *                                  echo $my_file_upload;
     *                                  </code>
     *
     *  @param  string  $default        (Optional) If a path to an existing file is provided, the control will behave
     *                                  both visually and functionally as if a file was already selected for upload.
     *
     *  @param  array   $attributes     (Optional) An array of attributes valid for
     *                                  {@link http://www.w3.org/TR/REC-html40/interact/forms.html#h-17.4 input}
     *                                  controls (size, readonly, style, etc)
     *
     *                                  Must be specified as an associative array, in the form of <i>attribute => value</i>.
     *                                  <code>
     *                                  // setting the "disabled" attribute
     *                                  $obj = &$form->add(
     *                                      'file',
     *                                      'my_file_upload',
     *                                      '',
     *                                      array(
     *                                          'disabled' => 'disabled'
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
     *                                  <b>type</b>, <b>id</b>, <b>name</b>, <b>class</b>
     *
     *  @return void
     */
    function MyZebra_Form_File($id, $default = '', $attributes = '')
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
            'files',
            'repeatable',
            'external_upload',
            'display_featured',
            'display_featured_html'

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(

                'disable_spam_filter'=>false,
                'disable_xss_filters'=>false,
		        'type'      =>  'file',
                'name'      =>  $id,
                'id'        =>  preg_replace('/\[.*\]$/', '', $id),
                'value'     =>  $default,
                'class'     =>  'myzebra-control myzebra-file',
                'external_upload'=>false,
                'display_featured'=>false,
                'display_featured_html'=>''

		    )

		);
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
        
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
    
        // all file upload controls must have the "upload" rule set or we trigger an error
        if (!isset($this->rules['upload'])) _myzebra_form_show_error('The control named <strong>"' . $this->attributes['name'] . '"</strong> in form <strong>"' . $this->form_properties['name'] . '"</strong> must have the <em>"upload"</em> rule set', E_USER_ERROR);
        // show the file upload control
        $output = '<input ' . $this->_render_attributes() . ($this->form_properties['doctype'] == 'xhtml' ? '/' : '') . '>';
        if (isset($this->attributes['display_featured_html']) && !empty($this->attributes['display_featured_html']))
        {
            $output.='<div class="myzebra-featured-container">';
            $output.=$this->attributes['display_featured_html'];
            $output.='</div>';
        }

        // return the generated output
        return $output;

    }
    
    function get_values()
    {
        if (isset($this->text_field))
        return array(
        'value'=>$this->text_field->attributes['value'],
        'file_data'=>$this->file_data,
        'file_upload'=>$this->file_upload);
        else
        return array(
        'value'=>$this->attributes['value'],
        'file_data'=>$this->file_data,
        'file_upload'=>$this->file_upload);
    }
    
    function set_values($data)
    {
        //$data=(array)$data;
        if (array_key_exists('value',$data))
        {
            $this->attributes['value']=$data['value'];
            if (isset($this->text_field))
            {
                $this->text_field->attributes['value']=$data['value'];
            }
        }
        if (array_key_exists('file_data',$data))
        {
            $this->file_data=$data['file_data'];
        }
        if (array_key_exists('file_upload',$data))
        {
            $this->file_upload=$data['file_upload'];
        }
    }
    
    function set_rule($rules)
    {
        $this->_set_rule($rules);
        if (!$this->text_field)
            $this->add_text_field();
        else
        {
            $rules=array_diff_key($this->rules,array('upload','resize','convert'));
            $this->text_field->rules=$rules;
        }
    }
    
    function add_text_field()
    {
        $attributes=$this->get_attributes(array('id','name'));
        require_once dirname(__FILE__).'/Text.php';
        $this->text_field=new MyZebra_Form_Text($attributes['id']);
        $this->text_field->form_properties=&$this->form_properties;
        $this->text_field->form=&$this->form;
        $rules=array_diff_key($this->rules,array('upload','resize','convert'));
        $this->text_field->rules=$rules;
    }

}

?>
