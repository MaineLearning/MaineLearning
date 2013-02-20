<?php

/**
 *  Class for checkbox controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Checkboxes extends MyZebra_Form_Control
{
    
    var $checkboxes=array();
    var $labels=array();
    
    function MyZebra_Form_Checkboxes($id, $checkboxes, $labels)
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

        $this->checkboxes=$checkboxes;
        $this->labels=$labels;
        
        // set the default attributes for the checkbox control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
                $this->checkboxes[0]->attributes
        , true);
        
        $this->attributes['id']=$id;
        
        //foreach ($this->checkboxes as $ii=>$check)
          //  $this->checkboxes[$ii]->prime_name=$this->prime_name;
        // sets user specified attributes for the control
        //$this->set_attributes($attributes);
        
    }
    
    function before_render(&$clientside_error_messages, &$datepicker_javascript, &$additional)
    {
        foreach ($this->checkboxes as $ii=>$checkbox)
        {
            $this->checkboxes[$ii]->prime_name=$this->prime_name;
            $this->checkboxes[$ii]->before_render($clientside_error_messages, $datepicker_javascript, $additional);
        }
    }
    
    function set_rule($rule)
    {
        foreach ($this->checkboxes as $ii=>$checkbox)
        {
            $this->checkboxes[$ii]->set_rule($rule);
        }
        
        parent::set_rule($rule);
    }
    
    function get_values_for_conditions()
    {
        $vals=array();
        foreach ($this->checkboxes as &$check)
        {
            if (isset($check->attributes['checked']))
                $vals[]=$check->get_values_for_conditions();
        }
        return $vals;
    }
    
    function get_values()
    {
        $vals=array();
        foreach ($this->checkboxes as &$check)
        {
            if (isset($check->attributes['checked']))
                $vals[]=$check->get_values();
        }
        return $vals;
    }
    
    function set_values($vals)
    {
        foreach ($this->checkboxes as &$check)
        {
            if (isset($check->attributes['checked']))
                unset($check->attributes['checked']);
            if (in_array($check->attributes['value'],(array)$vals))
                $check->set_attributes(array('checked' => 'checked'));
        }
    }
    
    function reset()
    {
        foreach ($this->checkboxes as $ii=> $check)
            $this->checkboxes[$ii]->reset();
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
    
        $output='<div id="'.$this->attributes['id'].'" class="myzebra-checkboxes">';
        $ii=0;
        for ($ii=0; $ii<count($this->checkboxes); $ii++)
        {
            $output.='<div class="myzebra-checkboxes-single">';
            $output.=$this->checkboxes[$ii]->toHTML();
            $output.=$this->labels[$ii]->toHTML();
            $output.='</div>';
        }
        $output.='</div>';
        return $output;

    }

}

?>
