<?php

/**
 *  A generic class containing common methods, shared by all the controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Generic
 */
class MyZebra_Form_Container extends MyZebra_Form_Control
{

    /**
     *  Array of HTML attributes of the element
     *
     *  @var array
     *
     *  @access private
     */
    var $attributes;
    var $javascript_attributes;
    var $controls;
    
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
    function MyZebra_Form_Container($id='')
    {
        // call the constructor of the parent class
        parent::MyZebra_Form_Control();
        
        $this->controls=array();
        
        $this->attributes = array(

            'locked' => false,
            'disable_xss_filters' => false,
            'disable_spam_filters' => false,
            'id'=>$id,
            'type'=>'container',
            'name'=>'',
            'class'=>'myzebra-container-control'
        );

        $this->private_attributes = array(
            'disable_spam_filter',
            'disable_xss_filters',
            'locked',
            'repeatable',
            'name',
            'type'
        );
        
        $this->_is_container=true;
    }

    
    function discard()
    {
        foreach ($this->controls as $ii=>$control)
            $this->controls[$ii]->discard();
            
        $this->_isDiscarded=true;
    }
    
    function addControl(&$control)
    {
        $this->controls[]=$control;
        $control->setParent($this);
    }
    
    function getControls()
    {
        return $this->controls;
    }
    
    function get_submitted_value() { }
    
    function _before_render(&$clientside_error_messages, &$datepicker_javascript, &$additional) { }
    
    function validate($only=false){ return true; }
    
    function get_values() {  return ''; }
    
    function set_values($vals){ }
    
    function reset(){}
    
    function getJS() { return ''; }
    
    function toHTML() {return '';}
    
    function renderBegin()
    {
        return '<div '.$this->_render_attributes().'>';
    }
    
    function renderEnd()
    {
        return '</div>';
    }
}
?>
