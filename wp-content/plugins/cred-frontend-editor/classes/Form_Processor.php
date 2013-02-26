<?php
/**
 * CRED Form Processor
 * 
 * 
 */
class CRED_Form_Processor
{
    private $_form_data;
    private $_strings=array();
    private $_prefix='';

    public function __construct($form_id,$form_name)
    {
        $this->setFormData($form_id,$form_name);
    }

    public function setFormData($form_id,$form_name)
    {
        $this->_form_data=array('ID'=>$form_id,'name'=>$form_name);
    }
    
    public function registerString($name,$value)
    {
        cred_translate_register_string('cred-form-'.$this->_form_data['name'].'-'.$this->_form_data['ID'], $name, $value, false);
    }
    
    public function processFormForStrings($content, $prefix='')
    {
        $this->_prefix=$prefix;
        $shorts=cred_disable_shortcodes();
        add_shortcode('cred-field',array(&$this,'check_strings_in_shortcodes'));
        do_shortcode($content);
        remove_shortcode('cred-field',array(&$this,'check_strings_in_shortcodes'));
        cred_re_enable_shortcodes($shorts);
    }

    public function check_strings_in_shortcodes($atts)
    {
        extract( shortcode_atts( array(
            'post' => '',
            'field' => '',
            'value' => null,
            'taxonomy'=>null,
            'type'  => null,
            'display'=>null
        ), $atts ) );
        
        if ($value!==null && !empty($value) && is_string($value))
        {
            cred_translate_register_string('cred-form-'.$this->_form_data['name'].'-'.$this->_form_data['ID'], $this->_prefix.$value, $value, false);
        }
    }
    
    public function processAllFormsForStrings()
    {
        $fm=CRED_Loader::get('MODEL/Forms');
        $forms=$fm->getAllForms();
        foreach ($forms as $form)
        {
            $this->setFormData($form->ID,$form->post_title);
            $notification=$fm->getFormCustomField($form->ID,'notification');
            $settings=$fm->getFormCustomField($form->ID,'form_settings');
            $this->processFormForStrings($form->post_content, 'Value: ');
            // register form title
            $this->registerString('Form Title: '.$form->post_title, $form->post_title);
            if ($settings && isset($settings->message))
                $this->registerString('Display Message: '.$form->post_title, $settings->message);
            // register Notification Data also
            if ($notification && isset($notification->notifications) && is_array($notification->notifications))
            {
                foreach ($notification->notifications as $ii=>$nott)
                {
                    switch($nott['mail_to_type'])
                    {
                        case 'wp_user':
                            $this->registerString('CRED Notification '.$ii.' Mail To', $nott['mail_to_user']);
                            break;
                        case 'specific_mail':
                            $this->registerString('CRED Notification '.$ii.' Mail To', $nott['mail_to_specific']);
                            break;
                        default:
                            break;
                    }
                    $this->registerString('CRED Notification '.$ii.' Subject', $nott['subject']);
                    $this->registerString('CRED Notification '.$ii.' Body', $nott['body']);
                }
            }
            $extra=$fm->getFormCustomField($form->ID,'extra');
            if ($extra && isset($extra->messages))
            {
                // register messages also
                foreach ($extra->messages as $msgid=>$msg)
                {
                    $this->registerString('Message_'.$msgid, $msg['msg']);
                }
            }
        }
    }   
}
?>