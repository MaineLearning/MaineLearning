<?php

/**
 *  Class for text controls.
 *
 *  @author     Stefan Gabos <contact@stefangabos.ro>
 *  @copyright  (c) 2006 - 2012 Stefan Gabos
 *  @package    Controls
 */
class MyZebra_Form_Taxonomypopular extends MyZebra_Form_Control
{

    function MyZebra_Form_Taxonomypopular($id, $default = '', $attributes = '')
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
            'popular',
            'master_taxonomy_id',
            'min_font_size',
            'max_font_size',
            'font_unit',
            'show_popular_text',
            'hide_popular_text'

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
                'class'     =>  'myzebra-control myzebra-taxonomy-popular',
                'popular'   => array(),
                'master_taxonomy_id' => '',
                'min_font_size' => 11,
                'max_font_size' => 32,
                'font_unit' => 'pt',
                'show_popular_text'=>'Show Popular',
                'hide_popular_text'=>'Hide Popular',
		    )

		);
        
        // sets user specified attributes for the control
        $this->set_attributes($attributes);
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
        $this->valid=true;
        return $this->valid;
    }
    
    function _getJS()
    {
        ob_start();
        ?>
                jQuery('#<?php echo $this->attributes['id']; ?>').MyZebra_Form_TaxonomyPopular({
                    'mastercontrol' : jQuery('#<?php echo $this->attributes['master_taxonomy_id']; ?>'),
                    'show_popular_text' : "<?php echo $this->attributes['show_popular_text']; ?>",
                    'hide_popular_text' : "<?php echo $this->attributes['hide_popular_text']; ?>"
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
        $popular=&$this->attributes['popular'];
        $min=$max=-1;
        foreach ($popular as $ii=>$pop)
        {
            $popular[$ii]['count']=intval($popular[$ii]['count']);
            if ($min==-1)
                $min=$popular[$ii]['count'];
            elseif ($min>$popular[$ii]['count'])
                $min=$popular[$ii]['count'];
            if ($max==-1)
                $max=$popular[$ii]['count'];
            elseif ($max<$popular[$ii]['count'])
                $max=$popular[$ii]['count'];
        }
        $min_font=intval($this->attributes['min_font_size']);
        $max_font=intval($this->attributes['max_font_size']);
        $unit=$this->attributes['font_unit'];
        ob_start();
        ?>
            <div id='<?php echo $this->attributes['id']; ?>' class='myzebra-taxonomy-popular'>
                <a href='javascript:void(0);' style='font-size:11px' class='myzebra-show-hide-popular'><?php echo $this->attributes['show_popular_text']; ?></a>
                <div class='myzebra-terms-popular'>
                    <?php
                        foreach ($popular as $term)
                        {
                            
                            if ($max==$min)
                                $size=$min_font;
                            else
                            {
                                $fact=($max-$term['count'])/($max-$min);
                                $size=round($min_font*$fact + $max_font*(1-$fact));
                            }
                            echo '<div class="myzebra-term-popular-wrapper"><a class="myzebra-term-popular" style="font-size:'.$size.$unit.';" href="javascript:void(0);">'.$this->extra_xss('html',$term['name']).'</a></div>'; 
                        }
                    ?>
                </div>
            </div>
        <?php
        return ob_get_clean();
    }
}
?>
