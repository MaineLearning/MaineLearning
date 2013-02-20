<?php

/**
 *  Class for text controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Taxonomy extends MyZebra_Form_Control
{

    function MyZebra_Form_Taxonomy($id, $default = '', $attributes = '')
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
            'add_text',
            'remove_text',
            'auto_suggest',
            'ajax_url',
            'repeatable'

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
                'value'     =>  $default,
                'class'     =>  'myzebra-taxonomy-control myzebra-taxonomy',
                'terms'     => array(),
                'add_text'  =>'Add',
                'remove_text'=>'Remove',
                'auto_suggest'=>false,
                'ajax_url'=>''
		    )

		);
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
        $this->submitted_value='add{}remove{}';
    }
    
    function get_submitted_value()
    {
        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};
        
        $this->submitted_value=(array_key_exists($this->attributes['name'],$method))?$method[$this->attributes['name']]:'add{}remove{}';
    }
    
    function reset()
    {
        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};
        
        $this->submitted_value='add{}remove{}';
        
        unset($method[$this->attributes['name']]);
    }
    
    function get_values()
    {
        $oldterms=array();
        foreach ($this->attributes['terms'] as $term)
        {
            $oldterms[]=$term['name'];
        }
        preg_match('/^add\{(.*?)\}remove\{(.*?)\}$/i',$this->submitted_value,$matches);
        $add=!empty($matches[1])?explode(',',$matches[1]):array();
        $remove=!empty($matches[2])?explode(',',$matches[2]):array();
        $terms=$oldterms;
        if (!empty($remove))
            $terms=array_diff($terms,$remove);
        if (!empty($add))
            $terms=array_merge($terms,$add);
        return $terms;
    }
    
    function set_values($terms)
    {
        $remove=array();
        foreach ($this->attributes['terms'] as $term)
        {
            $remove[]=$term['name'];
        }
        $newterms=array(
            'add'=>$terms,
            'remove'=>$remove
        );
        $this->submitted_value='add{'.implode(',',$newterms['add']).'}remove{'.implode(',',$newterms['remove']).'}';
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
    
    function _getJS()
    {
        ob_start();
        ?>
                jQuery('#<?php echo $this->attributes['id']; ?>').MyZebra_Form_Taxonomy({
                    'remove_text':"<?php echo $this->attributes['remove_text']; ?>",
                    'auto_suggest':<?php echo ($this->attributes['auto_suggest']==true)?'true':'false'; ?>,
                    'ajax_url':<?php echo '"'.$this->attributes['ajax_url'].'"'; ?>
                });
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
        $terms=&$this->attributes['terms'];
        ob_start();
        ?>
            <div id='<?php echo $this->attributes['id']; ?>' class='<?php echo $this->attributes['class']; ?>'>
            <input type='text' class='myzebra-text' value='' /> <a class='myzebra-add-new-term' href='javascript:void(0);'><?php echo $this->attributes['add_text']; ?></a>
            <input type='hidden' class='myzebra-hidden myzebra-taxonomy-addremove' name='<?php echo $this->attributes['name']; ?>' value='<?php echo $this->extra_xss('html',$this->submitted_value); ?>' />
            <div class='myzebra-terms'>
                <?php
                    foreach ($terms as $term)
                    {
                        echo '<div class="myzebra-term-wrapper"><a class="myzebra-term-remove" href="javascript:void(0);" title="'.$this->attributes['remove_text'].'"></a><span class="myzebra-term">'.$this->extra_xss('html',$term['name']).'</span></div>'; 
                    }
                ?>
            </div>
            </div>
        <?php
        return ob_get_clean();
    }
}
?>
