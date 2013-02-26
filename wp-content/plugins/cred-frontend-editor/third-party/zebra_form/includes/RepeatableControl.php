<?php

/**
 *  A generic class containing common methods, shared by all the controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Generic
 */
class MyZebra_Form_RepeatableControl extends MyZebra_Form_Control
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
    var $mastercontrol;
    
    var $maxrepeats;
    
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
    function MyZebra_Form_RepeatableControl(&$control)
    {

        // call the constructor of the parent class
        parent::MyZebra_Form_Control();
        
        $this->controls=array();
        $this->mastercontrol=&$control;
        $this->prime_name=$control->prime_name;
        $this->mastercontrol->set_attributes(array('repeatable'=>true));
        if (isset($this->mastercontrol->javascript_attributes))
            $this->javascript_attributes=&$this->mastercontrol->javascript_attributes;
        else
            $this->javascript_attributes=null;
        $this->rules=$this->mastercontrol->rules;
        $this->attributes=$this->mastercontrol->attributes;
        $this->private_attributes = $this->mastercontrol->private_attributes;
        $this->controls[]=clone $this->mastercontrol;
    }

    function get_attributes($attributes)
    {
        return $this->mastercontrol->get_attributes($attributes);
    }
    
    function set_attributes($attributes, $overwrite = true)
    {
        $this->mastercontrol->set_attributes($attributes, $overwrite);
        foreach ($this->controls as $ii=>$cont)
            $this->controls[$ii]->set_attributes($attributes, $overwrite);
        
        $this->attributes=$this->mastercontrol->attributes;
    }
    
    function set_rule($rules)
    {
        $this->mastercontrol->set_rule($rules);
        foreach ($this->controls as $ii=>$control)
            $this->controls[$ii]->set_rule($rules);
        
        $this->rules=$this->mastercontrol->rules;
    }
    
    function reset()
    {
        $this->mastercontrol->reset();
        /*foreach ($this->controls as $ii=>$control)
            $this->controls[$ii]->reset();*/
        //$this->controls=array(clone $this->mastercontrol);
        foreach ($this->controls as $ii=>$control)
        {
            $this->controls[$ii]->reset();
        }
        
        $this->attributes=$this->mastercontrol->attributes;
        $this->rules=$this->mastercontrol->rules;
    }
    
    function addControl($attributes)
    {
        //cred_log(print_r($attributes,true));
        $newcontrol=clone $this->mastercontrol;
        $atts = $this->mastercontrol->get_attributes(array('id','type'));
        $newcontrol->set_attributes($attributes,true);
        if ($atts['type']=='file')
            $newcontrol->add_text_field();
        $this->controls[]=$newcontrol;
        $id=$atts['id'];
        foreach ($this->controls as $ii=>$control)
        {
            if ($ii>0)
                $this->controls[$ii]->set_attributes(array('id'=>$id.'_repeat_'.($ii)));
        //cred_log($ii.print_r($this->controls[$ii]->attributes,true));
        }
    }
    
    function doActions()
    {
        $atts=$this->mastercontrol->get_attributes(array('type'));
        $valid=true;
        foreach ($this->controls as $ii=>$control)
        {
            //if ($atts['type']=='file')
              //  cred_log(print_r($this->controls[$ii]->file_data,true).print_r($this->controls[$ii]->actions,true));
            if (!$this->controls[$ii]->doActions())
                return false;
        }
        return true;
    }
    
    function getNormalizedFILES()
    {
        $newfiles = array();
        foreach($_FILES as $fieldname => $fieldvalue)
            foreach($fieldvalue as $paramname => $paramvalue)
                foreach((array)$paramvalue as $index => $value)
                    $newfiles[$fieldname][$index][$paramname] = $value;
        return $newfiles;
    }
    
    function get_submitted_value()
    {

        //cred_log(print_r($_POST,true));
        // get some attributes of the control
        $attribute = $this->get_attributes(array('name', 'type', 'value', 'disable_xss_filters', 'locked'));

        // if control's value is not locked to the default value
        if ($attribute['locked'] !== true) {

            // strip any [] from the control's name (usually used in conjunction with multi-select select boxes and
            // checkboxes)
            $attribute['name'] = preg_replace('/\[.*\]$/', '', $attribute['name']);

            // reference to the form submission method
            global ${'_' . $this->form_properties['method']};

            $method = & ${'_' . $this->form_properties['method']};

            // if form was submitted
            if (

                isset($method[$this->form_properties['identifier']]) &&

                $method[$this->form_properties['identifier']] == $this->form_properties['name']

            ) {
            
                // if control is a time picker control
                if ($attribute['type'] == 'time') 
                {

                    $times=array();
                    if (
                    isset($method[$attribute['name'] . '_hours'])
                    && isset($method[$attribute['name'] . '_minutes'])
                    && isset($method[$attribute['name'] . '_seconds'])
                    && isset($method[$attribute['name'] . '_ampm'])
                    && is_array($method[$attribute['name'] . '_hours'])
                    && is_array($method[$attribute['name'] . '_minutes'])
                    && is_array($method[$attribute['name'] . '_seconds'])
                    && is_array($method[$attribute['name'] . '_ampm'])
                    )
                    {
                        // merge arrays
                        $times = array_map(null,$method[$attribute['name'] . '_hours'],$method[$attribute['name'] . '_minutes'],$method[$attribute['name'] . '_seconds'],$method[$attribute['name'] . '_ampm']);

                    unset($method[$attribute['name'] . '_hours']);
                    unset($method[$attribute['name'] . '_minutes']);
                    unset($method[$attribute['name'] . '_seconds']);
                    unset($method[$attribute['name'] . '_ampm']);
                    
                    $combined=array();
                    foreach ($times as $ii=>$time)
                    {
                        // combine hour, minutes and seconds into one single string (values separated by :)
                        // hours
                        $combined[$ii] = (isset($time[0]) ? $time[0] : '');
                        // minutes
                        $combined[$ii] .= (isset($time[1]) ? ($combined[$ii] != '' ? ':' : '') . $time[1] : '');
                        // seconds
                        $combined[$ii] .= (isset($time[2]) ? ($combined[$ii] != '' ? ':' : '') . $time[2] : '');
                        // AM/PM
                        $combined[$ii] .= (isset($time[3]) ? ($combined[$ii] != '' ? ' ' : '') . $time[3] : '');
                    }
                    // create a super global having the name of our time picker control
                    // (remember, we don't have a control with the time picker's control name but three other controls
                    // having the time picker's control name as prefix and _hours, _minutes and _seconds respectively
                    // as suffix)
                    // we need to do this so that the values will also be filtered for XSS injection
                    $method[$attribute['name']] = $combined;
                    $this->submitted_value=$combined;
                    
                    // unset the three temporary fields as we want to return to the user the result in a single field
                    // having the name he supplied
                    unset($times);
                    }
                }
                
                // if control is a file upload control and a file was indeed uploaded
                if ($attribute['type'] == 'file' && (isset($_FILES[$attribute['name']]) || isset($method[$attribute['name']])))
                {
                    $files=$this->getNormalizedFILES();
                    if ( 
                    isset($method[$attribute['name']]) 
                    && is_array($method[$attribute['name']]) 
                    )
                        //$this->submitted_value=false;
                    //else
                    {
                        $this->submitted_value=array();
                        $texts=array();
                        foreach ($method[$attribute['name']] as $ii=>$file)
                        {
                            $this->submitted_value[$ii] = true;
                            $texts[$ii]=$method[$attribute['name']][$ii];
                        }
                    }

                    // if control was not submitted
                    // we set this for those controls that are not submitted even
                    // when the form they reside in is (i.e. unchecked checkboxes)
                    // so that we know that they were indeed submitted but they
                    // just don't have a value
                }                // if control was submitted
                elseif ($attribute['type'] == 'skype')
                {
                    // create the submitted_value property for the control and
                    // assign to it the submitted value of the control
                    
                    // and also, if magic_quotes_gpc is on (meaning that both
                    // single and double quotes are escaped)
                    // strip those slashes
                    if (isset($method[$attribute['name']]) && array_key_exists('skypename',$method[$attribute['name']]) && array_key_exists('style',$method[$attribute['name']]))
                    {
                        $this->submitted_value = $method[$attribute['name']];
                        if (get_magic_quotes_gpc()) 
                        {
                            foreach ($this->submitted_value['skypename'] as $ii =>$value)
                                $this->submitted_value['skypename'][$ii] = stripslashes($this->submitted_value['skypename'][$ii]);
                            foreach ($this->submitted_value['style'] as $ii =>$value)
                                $this->submitted_value['style'][$ii] = stripslashes($this->submitted_value['style'][$ii]);
                        }
                    }
                    /*else
                        $this->submitted_value=false;*/
                }
                elseif (isset($method[$attribute['name']])) 
                {
                    if (!is_array($method[$attribute['name']])) // if not repeated field get normal method
                        return $this->_get_submitted_value();
                        
                    // create the submitted_value property for the control and
                    // assign to it the submitted value of the control
                    $this->submitted_value = $method[$attribute['name']];

                    // if submitted value is an array
                    if (is_array($this->submitted_value)) {

                        // iterate through the submitted values
                        foreach ($this->submitted_value as $key => $value)

                            // and also, if magic_quotes_gpc is on (meaning that
                            // both single and double quotes are escaped)
                            // strip those slashes
                            if (get_magic_quotes_gpc()) $this->submitted_value[$key] = stripslashes($value);

                    // if submitted value is not an array
                    } 
                    // since 1.1
                    // if XSS filtering is not disabled
                    if ($attribute['disable_xss_filters'] !== true) {

                        // if submitted value is an array
                        if (is_array($this->submitted_value))

                            // iterate through the submitted values
                            foreach ($this->submitted_value as $key => $value)

                                // filter the control's value for XSS injection
                                $this->submitted_value[$key] = htmlspecialchars($this->sanitize($value));

                        // set the respective $_POST/$_GET value to the filtered value
                        $method[$attribute['name']] = $this->submitted_value;

                    }
                } 
                //else $this->submitted_value = false;
                
                if (

                    //if type is password, textarea or text OR
                    ($attribute['type'] == 'password' || $attribute['type'] == 'textarea' || $attribute['type'] == 'text') &&

                    // control has the "uppercase" or "lowercase" modifier set
                    preg_match('/\bmodifier\-uppercase\b|\bmodifier\-lowercase\b/i', $this->attributes['class'], $modifiers) &&
                    
                    isset($this->submitted_value)

                ) {

                    foreach ($this->submitted_value as $ii=>$val)
                    {
                        // if string must be uppercase, update the value accordingly
                        if ($modifiers[0] == 'modifier-uppercase') $this->submitted_value[$ii] = strtoupper($this->submitted_value[$ii]);

                        // otherwise, string needs to be lowercase
                        else $this->submitted_value[$ii] = strtolower($this->submitted_value[$ii]);
                    }
                    // set the respective $_POST/$_GET value to the updated value
                    $method[$attribute['name']] = $this->submitted_value;

                }
            }

            // if control was submitted
            if (isset($this->submitted_value)) {

                // remove previous controls
                $this->controls=array();
                if ($this->attributes['type']=='skype')
                {
                    foreach ($this->submitted_value['skypename'] as $ii=>$val)
                    {
                        // clone control for each repeated filed
                        $this->controls[$ii]=clone $this->mastercontrol;
                        $atts=$this->mastercontrol->get_attributes(array('id'));
                        if ($ii>0)
                            $this->controls[$ii]->set_attributes(array('id'=>$atts['id'].'_repeat_'.$ii));
                        // get some attributes of the control
                        $attribute = $this->controls[$ii]->get_attributes(array('name', 'type', 'value', 'disable_xss_filters', 'locked'));
                        $this->controls[$ii]->submitted_value=array(
                            'skypename'=>$this->submitted_value['skypename'][$ii],
                            'style'=>$this->submitted_value['style'][$ii]
                            );
                        $this->controls[$ii]->attributes['value']=$this->controls[$ii]->submitted_value;
                    }
                }
                else
                {
                    foreach ($this->submitted_value as $ii=>$val)
                    {
                        // clone control for each repeated filed
                        $this->controls[$ii]=clone $this->mastercontrol;
                        $atts=$this->mastercontrol->get_attributes(array('id'));
                        if ($ii>0)
                            $this->controls[$ii]->set_attributes(array('id'=>$atts['id'].'_repeat_'.$ii));
                        // get some attributes of the control
                        $attribute = $this->controls[$ii]->get_attributes(array('name', 'type', 'value', 'disable_xss_filters', 'locked'));
                        $attribute['name'] = preg_replace('/\[.*\]$/', '', $attribute['name']);
                        
                        // the assignment of the submitted value is type dependant
                        switch ($attribute['type']) {

                            // if control is a checkbox
                            case 'checkbox':

                                if (

                                    /*(

                                        // if is submitted value is an array
                                        is_array($this->submitted_value) &&

                                        // and the checkbox's value is in the array
                                        in_array($attribute['value'], $this->submitted_value)

                                    // OR
                                    ) ||*/

                                    // assume submitted value is not an array and the
                                    // checkbox's value is the same as the submitted value
                                    $attribute['value'] == $val

                                // set the "checked" attribute of the control
                                ) 
                                {
                                $this->controls[$ii]->submitted_value=$val;
                                $this->controls[$ii]->set_attributes(array('checked' => 'checked'));
                                }

                                // if checkbox was "submitted" as not checked
                                // and if control's default state is checked, uncheck it
                                elseif (isset($this->controls[$ii]->attributes['checked'])) unset($this->controls[$ii]->attributes['checked']);

                                break;

                            // if control is a radio button
                            case 'radio':

                                if (

                                    // if the radio button's value is the same as the
                                    // submitted value
                                    ($attribute['value'] == $val)

                                // set the "checked" attribute of the control
                                ) 
                                {
                                $this->controls[$ii]->submitted_value=$val;
                                $this->controls[$ii]->set_attributes(array('checked' => 'checked'));
                                }
                                break;

                            // if control is a select box
                            case 'select':

                                // set the "value" private attribute of the control
                                // the attribute will be handled by the
                                // MyZebra_Form_Select::_render_attributes() method
                                $this->controls[$ii]->set_attributes(array('value' => $val));
                                $this->controls[$ii]->submitted_value=$val;

                                break;

                            // if control is a file upload control, a hidden control, a password field, a text field or a textarea control
                            case 'file':
                            case 'hidden':
                            case 'password':
                            case 'text':
                            case 'textarea':
                            case 'time':

                                // set the "value" standard HTML attribute of the control
                                $this->controls[$ii]->set_attributes(array('value' => $val));
                                $this->controls[$ii]->submitted_value=$val;
                                if ($attribute['type']=='file')
                                {
                                    //cred_log(print_r($files[$attribute['name']][$ii],true));
                                    if (isset($files[$attribute['name']][$ii]))
                                        $this->controls[$ii]->file_data=array($attribute['name']=>$files[$attribute['name']][$ii]);
                                    else
                                        $this->controls[$ii]->file_data=false;
                                    $this->controls[$ii]->add_text_field();
                                    $this->controls[$ii]->text_field->submitted_value=$texts[$ii];
                                    $this->controls[$ii]->text_field->set_attributes(array('value' => $texts[$ii]));
                                }
                                break;

                        }
                    }
                }
            }
        }
    }
    
    function before_render(&$clientside_error_messages, &$datepicker_javascript, &$additional)
    {
        foreach ($this->controls as $ii=>$control)
            $this->controls[$ii]->_before_render($clientside_error_messages, $datepicker_javascript, $additional);
    }
    
    function discard()
    {
        foreach ($this->controls as $ii=>$control)
            $this->controls[$ii]->discard();
            
        $this->_isDiscarded=true;
    }
    
    function validate($only=false)
    {
         // bypass
         if ($this->isDiscarded())
            return true;
         
         // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};

        // at this point, we assume that the control is not valid
        //$valid = false;

        // continue only if form was submitted
        $valid=false;
        if (

            isset($method[$this->form_properties['identifier']]) &&

            $method[$this->form_properties['identifier']] == $this->form_properties['name']

        ) {

            if (!$only)
                // manage submitted value
                $this->get_submitted_value();
            $atts=$this->mastercontrol->get_attributes(array('type'));
            $valid=true;
            foreach ($this->controls as $ii=>$control)
            {
            // at this point, we assume that the control is valid
               if (!$this->controls[$ii]->_validate())
                    $valid= false;
            }
            return $valid;
        }
        
        return ($valid && $this->custom_valid);
    }
    
    function addError($error_message)
    {
        $this->custom_valid=false;
        foreach ($error_message as $ii=>$errmsg)
        {
            if (isset($this->controls[$ii]))
               $this->controls[$ii]->addError($errmsg);
        }        
    }
    
    function get_values()
    {
        $vals=array();
        foreach ($this->controls as &$control)
        {
            $vals[]=$control->get_values();
        }
        return $vals;
    }
    
    function set_values($vals)
    {
        foreach ($vals as $ii=>$val)
        {
            if (isset($this->controls[$ii]))
                $this->controls[$ii]->set_values($val);
        }
    }
    
    function getJS()
    {
        $output='';
        foreach ($this->controls as $ii=>$control)
        {
            $output.=$control->getJS();
        }
        return $output;
    }
    
    function toHTML()
    {
        $attributes=$this->mastercontrol->get_attributes(array('name','id'));
        $name_clean=str_replace(array('[', ']'), '', $attributes['name']);
        $id=$attributes['id'];
        $output='<div class="myzebra-count_'.count($this->controls).'">';
        $output.='<a href="javascript:void(0);" class="myzebra-add-new-field">'.$this->form_properties['language']['add_new_repeatable_field'].'</a><br />';
        foreach ($this->controls as $ii=>$control)
        {
            //cred_log($ii.print_r($this->controls[$ii]->attributes,true));
            $output.='<div class="myzebra-repeatable-field">';
            $name=$name_clean.'['.$ii.']';
            $control->prime_name=$this->prime_name;
            $control->set_attributes(array('name'=>$name));
            if ($ii>0)
                $control->set_attributes(array('id'=>$id."_repeat_".$ii));
            $output.=$control->toHTML();
            if ($ii>0)
                $output.="<a href='javascript:void(0);' class='myzebra-remove-field'>".$this->form_properties['language']['remove_repeatable_field']."</a>";
            $output.='</div>';
        }
        $output.='</div>';
        
        return $output;
    }
}
?>
