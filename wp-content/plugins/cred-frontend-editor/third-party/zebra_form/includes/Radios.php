<?php

/**
 *  Class for radio button controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Radios extends MyZebra_Form_Control
{

    var $radios=array();
    var $labels=array();
    
    function MyZebra_Form_Radios($id, $radios,$labels)
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
            'repeatable'
        );
        
        $this->radios=$radios;
        $this->labels=$labels;
        // set the default attributes for the radio button control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            $this->radios[0]->attributes
		);
        
        $this->attributes['id']=$id;

        // sets user specified attributes for the control
        //$this->set_attributes($attributes);

    }
    
    function before_render(&$clientside_error_messages, &$datepicker_javascript, &$additional)
    {
        foreach ($this->radios as $ii=>$checkbox)
        {
            $this->radios[$ii]->prime_name=$this->prime_name;
            $this->radios[$ii]->before_render($clientside_error_messages, $datepicker_javascript, $additional);
        }
    }
    
    function set_rule($rule)
    {
        foreach ($this->radios as $ii=>$radio)
        {
            $this->radios[$ii]->set_rule($rule);
        }
    }
    
    function get_values_for_conditions()
    {
        foreach ($this->radios as &$radio)
        {
            if (isset($radio->attributes['checked']))
                return $radio->get_values_for_conditions();
        }
        return '';
    }
    
    function get_values()
    {
        foreach ($this->radios as &$radio)
        {
            if (isset($radio->attributes['checked']))
                return $radio->get_values();
        }
        return '';
    }
    
    function set_values($val)
    {
        foreach ($this->radios as &$radio)
        {
            if (isset($radio->attributes['checked']))
                unset($radio->attributes['checked']);
            if ($radio->attributes['value']==$val)
                $radio->set_attributes(array('checked' => 'checked'));
        }
    }
    
    function reset()
    {
        foreach ($this->radios as $ii=> $radio)
            $this->radios[$ii]->reset();
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

        $output='<div id="'.$this->attributes['id'].'" class="myzebra-radios">';
        $ii=0;
        for ($ii=0; $ii<count($this->radios); $ii++)
        {
            $output.='<div class="myzebra-radios-single">';
            $output.=$this->radios[$ii]->toHTML();
            $output.=$this->labels[$ii]->toHTML();
            $output.='</div>';
        }
        $output.='</div>';
        return $output;

    }

}

?>
