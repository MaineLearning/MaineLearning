<?php

/**
 *  Class for text controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Taxonomyhierarchical extends MyZebra_Form_Control
{

    var $chosenvalues='';
    var $newvalues='';
    
    function MyZebra_Form_Taxonomyhierarchical($id, $default = '', $attributes = '')
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
            'terms',
            'all',
            'repeatable',
            'single_display'

        );

        // set the default attributes for the text control
        // put them in the order you'd like them rendered
        $this->set_attributes(
        
            array(

                'disable_spam_filter'=>false,
                'disable_xss_filters'=>false,
		        'type'      =>  'checkbox',
                'single_display' => false,
                'name'      =>  $id,
                'id'        =>  preg_replace('/\[.*\]$/', '', $id),
                'value'     =>  $default,
                'class'     =>  'myzebra-taxonomy-hierarchical-control myzebra-taxonomy',
                'terms'     => array(),
                'all'   =>  array(),
		    )

		);
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
        //$this->submitted_value=null;
    }
    
    function get_submitted_value()
    {
        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $name=preg_replace('/\[.*\]$/', '', $this->attributes['name']);
        $method = & ${'_' . $this->form_properties['method']};
        
        if (array_key_exists($name,$method))
        {
            $tmp=(array)$method[$name];//array_map('intval',(array)$method[$name]);
            $values=array();
            foreach ($tmp as $tid)
            {
                $values[$tid]=true;
            }
            unset($tmp);
            $this->chosenvalues=implode(',',(array)$method[$name]);
            $this->newvalues=$method[$name.'_hierarchy'];
            $this->submitted_value=&$values;
        }
        else
        {
            $values=array();
            $terms=&$this->attributes['terms'];
            foreach ($terms as $term)
            {
                $values[$term['term_id']]=true;
            }
            $this->chosenvalues='';
            $this->newvalues='';
            $this->submitted_value=&$values;
        }
    }
    
    function get_values_for_conditions()
    {
        $values=array();
        $terms=&$this->attributes['terms'];
        $all=&$this->attributes['all'];
        $names=array();
        foreach ($all as $term)
        {
            $names[$term['term_id']]=$term['name'];
        }
        foreach ($this->submitted_value as $tid=>$val)
        {
            if (isset($names[$tid]))
                $values[]=$names[$tid];
            else
                $values[]=$tid;
        }
        return $values;
    }
    
    function get_values()
    {
        return $this->submitted_value;
    }
    
    function set_values($vals)
    {
        $this->submitted_value=$vals;
    }
    
    function reset()
    {
        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $name=preg_replace('/\[.*\]$/', '', $this->attributes['name']);
        $method = & ${'_' . $this->form_properties['method']};
        
        $this->submitted_value=array();
        $this->newvalues='';
        $this->chosenvalues='';
        unset($method[$name]);
    }
    
    function validate($only=false)
    {
         // bypass
         if ($this->isDiscarded())
            return true;
        
        if (!$only)
            $this->get_submitted_value();
        $this->valid=true;
        return ($this->valid && $this->custom_valid);
    }
    
    private function buildCheckboxes($index, &$childs, &$names, $name, &$values)
    {
        $output='';
        ob_start();
        if (isset($childs[$index]))
        {
            foreach ($childs[$index] as $tid)
            {
                ?>
                <div style='position:relative;line-height:0.9em;margin:2px 0;<?php if ($tid!=0) echo 'margin-left:15px'; ?>' class='myzebra-taxonomy-hierarchical-checkbox'>
                    <label class='myzebra-style-label'><input type='checkbox' class='myzebra-checkbox' name='<?php echo $name; ?>' value='<?php echo $this->extra_xss('attr',$tid); ?>' <?php if (isset($values[$tid])) echo 'checked="checked"'; ?> /><span class="myzebra-checkbox-replace"></span>
                    <span class='myzebra-checkbox-label-span' style='position:relative;font-size:12px;display:inline-block;margin:0;padding:0;margin-left:15px'><?php echo $this->extra_xss('html',$names[$tid]); ?></span></label>
                    <?php
                        if (isset($childs[$tid]))
                            echo $this->buildCheckboxes($tid,$childs,$names,$name,$values);
                    ?>
                </div>
                <?php
            }
        }
        $output=ob_get_clean();
        return $output;
    }
    
    private function buildSelect(&$names, $name, &$values)
    {
        ob_start();
        if ($this->attributes['single_select'])
            echo '<select name='.$name.' class="myzebra-select myzebra-keep-original">';
        else
            echo '<select name='.$name.' class="myzebra-select myzebra-keep-original" multiple="multiple">';
        foreach ($names as $tid=>$term_name)
        {
            ?>
            <option value='<?php echo $this->extra_xss('attr',$tid); ?>' <?php if (isset($values[$tid])) echo 'selected="selected"'; ?>><?php echo $this->extra_xss('html',$term_name); ?></option>
            <?php
        }
        echo '</select>';
        return ob_get_clean();
    }
    
    
    function _getJS()
    {
        ob_start();
        ?>
                jQuery('#<?php echo $this->attributes['id']; ?>').MyZebra_Form_TaxonomyHierarchical();
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
        $this->get_submitted_value();
        // sort taxonomies according to parent/child relation
        $all=&$this->attributes['all'];
        if ($this->attributes['type']=='select')
        {
            $names=array();
            foreach ($all as $term)
            {
                $names[$term['term_id']]=$term['name'];
            }
        }
        else
        {
            $childs=array();
            $names=array();
            foreach ($all as $term)
            {
                $names[$term['term_id']]=$term['name'];
                if (!isset($childs[$term['parent']]) || !is_array($childs[$term['parent']]))
                    $childs[$term['parent']]=array();
                $childs[$term['parent']][]=$term['term_id'];
            }
        }
        ob_start();
        ?>
        <div id='<?php echo $this->attributes['id']; ?>' class='<?php echo $this->attributes['class']; ?>'>
            <div class='myzebra-taxonomy-hierarchical'>
                <?php 
                if ($this->attributes['type']=='select')
                {
                    echo "<div class='myzebra-taxonomy-hierarchical-select-container'>";
                    echo $this->buildSelect($names,$this->attributes['name'].'[]',$this->submitted_value);
                    echo "</div>";
                }
                else
                {
                    echo "<div class='myzebra-taxonomy-hierarchical-checkbox-container'>";
                    echo $this->buildCheckboxes(0,$childs,$names,$this->attributes['name'].'[]',$this->submitted_value); 
                    echo "</div>";
                }
                ?>
                <input type='hidden' id='<?php echo $this->attributes['id']; ?>_hierarchy' name='<?php echo $this->attributes['name']; ?>_hierarchy' style='display:none' value='<?php echo $this->extra_xss('html',$this->newvalues); ?>' />
                <input type='hidden' id='<?php echo $this->attributes['id']; ?>_chosen' style='display:none' value='<?php echo $this->extra_xss('html',$this->chosenvalues); ?>' />
                <span class='myzebra-taxonomy-hierarchical-name-parameter' style='visibility:hidden;display:none'><?php echo $this->attributes['name'].'[]' ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}

?>
