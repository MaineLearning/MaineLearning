<?php
/**
 *  A generic class containing common methods, shared by all the controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Generic
 */
class MyZebra_Form_Conditionalgroup extends MyZebra_Form_Container
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
    
    var $_condition_data=null;
    
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
    function MyZebra_Form_Conditionalgroup($id)
    {
        // call the constructor of the parent class
        parent::MyZebra_Form_Container();
        
        $this->controls=array();
        
        $this->attributes = array(

            'locked' => false,
            'disable_xss_filters' => false,
            'disable_spam_filters' => false,
            'id'=>$id,
            'name'=>'',
            'type'=>'oonditional-container',
            'class'=>'myzebra-container-control myzebra-container-conditional-control',
        );

        $this->private_attributes = array(
            'disable_spam_filter',
            'disable_xss_filters',
            'locked',
            'repeatable',
            'condition',
            'name',
            'type'
        );
    }

    function getConditionData()
    {
        return $this->_condition_data;
    }
    
    function setConditionData($data)
    {
        $this->_condition_data=$data;
    }
}
?>
