<?php
class CRED_Generic_Fields_Controller extends CRED_Abstract_Controller
{
    public function getField($get,$post)
    {
        if (isset($get['field']) && ($get['field']!='conditional_group'))
        {
            $fm=CRED_Loader::get('MODEL/Fields');
            $fields=$fm->getTypesDefaultFields();
            
            echo CRED_Loader::renderTemplate('generic-field-shortcode-setup',array(
                'field'=>$fields[$get['field']],
                'help'=>CRED_CRED::$help,
                'help_target'=>CRED_CRED::$help_link_target
            ));
        }
        elseif (isset($get['field']) && ($get['field']=='conditional_group')/* && isset($get['post_type'])*/)
        {
            echo CRED_Loader::renderTemplate('conditional-shortcode-setup',array(
                //'fields'=>$fields,
                'help'=>CRED_CRED::$help,
                'help_target'=>CRED_CRED::$help_link_target
            ));
        }
        die();
    }
    
    public function getCustomField($get,$post)
    {
        $popup_close='false';
        
        $fm=CRED_Loader::get('MODEL/Fields');
        $fields=$fm->getTypesDefaultFields(true);
        
        if (isset($post['field']) && is_array($post['field']))
        {
            $fm=CRED_Loader::get('MODEL/Fields');
            $fm->setCustomField($post['field']);
            $popup_close='true';
        }
        if (isset($get['field_name']))
        {
            $field_name=$get['field_name'];
            $post_type=isset($get['post_type'])?$get['post_type']:'post';
            
            if (isset($get['_reset_']) && '1'==$get['_reset_'])
            {
                $fm->ignoreCustomFields($post_type, array($field_name), 'reset');
                $popup_close='true';
                $data=array();
                $field_type='';//__('Not Set','wp-cred');
            }
            else
            {
                $data=$fm->getCustomField($post_type,$field_name);
                if (isset($get['field']))
                {
                    $field_type=$get['field'];
                    if (isset($data['type']) && $data['type']!=$field_type)
                    {
                        $data=array();
                    }
                }
                else
                {
                    $field_type=isset($data['type'])?$data['type']:'textfield';
                }
            }
            echo CRED_Loader::renderTemplate('custom-field-setup',array(
                'field'=>$fields[$field_type],
                'data'=>$data,
                'popup_close'=>$popup_close,
                'field_name'=>$field_name,
                'post_type'=>$post_type,
                'url'=>cred_route('/Generic_Fields/getCustomField?post_type='.$post_type.'&field_name='.$field_name),
                'fields'=>$fields
            ));
        }
        die();
    }
    
    public function removeCustomField($get,$post)
    {
        if (isset($get['field_name']) && isset($get['post_type']))
        {
            $field_name=$get['field_name'];
            $post_type=$get['post_type'];
            $fm=CRED_Loader::get('MODEL/Fields');
            $fm->ignoreCustomFields($post_type, array($field_name), 'reset');
            echo 'true';
            die();
        }
        die();
    }
}
?>