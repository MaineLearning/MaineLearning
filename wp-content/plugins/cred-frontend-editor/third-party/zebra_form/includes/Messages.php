<?php

/**
 *  Class for form messages wrapped as control
 *
 *  @package    Controls
 */
class MyZebra_Form_Messages extends MyZebra_Form_Control
{

    function MyZebra_Form_Messages($id, $value='', $attributes = '')
    {

        // call the constructor of the parent class
        parent::MyZebra_Form_Control();

        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->private_attributes = array(

            'disable_spam_filter',
            'disable_xss_filters',
            'locked',
            'name',
            'type',
            'repeatable',
            'errors',
            'messages'
        );


        // set the default attributes for the HTML control
        $this->set_attributes(

            array(

                'disable_spam_filter'=>false,
                'disable_xss_filters'=>false,
                'class'     =>  'myzebra-messages-control',
                'id'    	=>  preg_replace('/\[.*\]$/', '', $id),
                'name'      =>  $id,
                'type'  	=>  'messages',
                'errors'    =>'',
                'messages'  =>''

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
    function toHTML()
    {
        return '<div ' . $this->_render_attributes() . '>' . $this->attributes['errors'] . $this->attributes['messages'] . '</div>';
    }
}
?>
