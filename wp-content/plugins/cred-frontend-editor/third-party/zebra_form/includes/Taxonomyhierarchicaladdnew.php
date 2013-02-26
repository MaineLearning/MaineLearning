<?php

/**
 *  Class for text controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Taxonomyhierarchicaladdnew extends MyZebra_Form_Control
{

    function MyZebra_Form_Taxonomyhierarchicaladdnew($id, $default = '', $attributes = '')
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
            'repeatable',
            'master_taxonomy_id',
            'add_new_text',
            'add_text',
            'parent_text'
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
                'class'     =>  'myzebra-control myzebra-taxonomy-hierarchical-addnew',
                'master_taxonomy_id'=>'',
                'add_new_text'=>'',
                'add_text'=>'',
                'parent_text'=>''
		    )

		);
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
    }
    
    function get_submitted_value()
    {
        // reference to the form submission method
        global ${'_' . $this->form_properties['method']};

        $method = & ${'_' . $this->form_properties['method']};
        
        //$this->submitted_value=(array_key_exists($this->attributes['name'],$method))?$method[$this->attributes['name']]:'add{}remove{}';
    }
    
    function get_values()
    {
        return false;
    }
    
    function set_values($val)
    {
    }
    
    function validate($only=false)
    {
        //$this->get_submitted_value();
        $this->valid=true;
        return $this->valid;
    }
    
    function _getJS()
    {
        ob_start();
        ?>
                jQuery('#<?php echo $this->attributes['id']; ?>').MyZebra_Form_TaxonomyHierarchicalAddNew({
                    'mastercontrol' : jQuery('#<?php echo $this->attributes['master_taxonomy_id']; ?>'),
                    'parent_text' : '<?php echo $this->attributes['parent_text']; ?>'
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
        ob_start();
        ?>
            <div id='<?php echo $this->attributes['id']; ?>' class='myzebra-taxonomy-hierarchical-addnew'>
                <a href='javascript:void(0);' style='font-size:11px' class='myzebra-add-new-hierarchical'><?php echo $this->attributes['add_new_text']; ?></a>
                <div class='myzebra-add-new-hierarchical-terms'>
                    <div style='position:relative;'>
                        <input type='text' class='myzebra-text myzebra-add-text' value='' />
                        <a href='javascript:void(0);' style='font-size:11px' class='myzebra-add-hierarchical myzebra-add-new-term'><?php echo $this->attributes['add_text']; ?></a>
                    </div>
                    <select class='myzebra-add-new-hierarchical-terms-select'>
                    </select>
                </div>
            </div>
        <?php
        return ob_get_clean();
    }

}

?>
