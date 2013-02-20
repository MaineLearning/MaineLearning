<?php
class CRED_Forms_Controller extends CRED_Abstract_Controller
{
	public function updateFormFields($get,$post)
    {
		$form_id = $post['form_id'];
        $fields = $post['fields'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $fm->updateFormCustomFields($form_id,$fields);
        
        echo json_encode(true);
        die();
    }
    
	public function updateFormField($get,$post)
    {
		$form_id = $post['form_id'];
        $field = $post['field'];
        $value = $post['value'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $fm->updateFormCustomField($form_id,$field,$value);
        
        echo json_encode(true);
        die();
    }
    
    public function getPostFields($get,$post)
	{
		$post_type=$post['post_type'];
		
		$fields_model=CRED_Loader::get('MODEL/Fields');
		$fields_all = $fields_model->getFields($post_type);
        echo json_encode($fields_all);
		die();
	}
	
    public function getFormFields($get,$post)
    {
		$form_id = $post['form_id'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $fields = $fm->getFormCustomFields($form_id);
        
        echo json_encode($fields);
        die();
    }
    
    public function getFormField($get,$post)
    {
		$form_id = $post['form_id'];
        $field = $post['field'];
        $fm=CRED_Loader::get('MODEL/Forms');
        $value = $fm->getFormCustomField($form_id,$field);
        
        echo json_encode($value);
        die();
    }
    
    
    // export forms to XML and download
    public function exportForm($get,$post)
    {
        if (isset($get['form']) && isset($get['_wpnonce']))
        {
            if (wp_verify_nonce($get['_wpnonce'],'cred-export-'.$get['form']))
            {
                CRED_Loader::load('CLASS/XML_Processor');
                $filename=isset($get['filename'])?urldecode($get['filename']):'';
                CRED_XML_Processor::exportToXML(array($get['form']), isset($get['ajax']), $filename);
                die();
            }
        }
        die();
    }
    
    public function exportSelected($get,$post)
    {
        if (isset($_REQUEST['checked']) && is_array($_REQUEST['checked']))
        {
            check_admin_referer('cred-bulk-selected-action','cred-bulk-selected-field');
            CRED_Loader::load('CLASS/XML_Processor');
            $filename=isset($_REQUEST['filename'])?urldecode($_REQUEST['filename']):'';
            CRED_XML_Processor::exportToXML((array)$_REQUEST['checked'], isset($get['ajax']), $filename);
            die();
        }
        die();
    }
    
    public function exportAll($get,$post)
    {
        if (isset($get['all']) && isset($get['_wpnonce']))
        {
            if (wp_verify_nonce($get['_wpnonce'],'cred-export-all'))
            {
                CRED_Loader::load('CLASS/XML_Processor');
                $filename=isset($get['filename'])?urldecode($get['filename']):'';
                CRED_XML_Processor::exportToXML('all', isset($get['ajax']), $filename);
                die();
            }
        }
        die();
    }
}
?>