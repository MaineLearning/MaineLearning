<?php
class CRED_Settings_Controller extends CRED_Abstract_Controller
{
    public function disableWizard($get, $post)
    {
		if (isset($post['cred_wizard']) && $post['cred_wizard']=='false')
        {
            $sm=CRED_Loader::get('MODEL/Settings');
            $settings=$sm->getSettings();
            $settings['wizard']=false;
            $sm->updateSettings($settings);
            
            echo "true";
            die(0);
        }
    } 
    
    public function toggleHighlight($get, $post)
    {
		if (isset($post['cred_highlight']))
        {
            $sm=CRED_Loader::get('MODEL/Settings');
            $settings=$sm->getSettings();
            if ($post['cred_highlight']=='1')
                $settings['syntax_highlight']=1;
            else
                $settings['syntax_highlight']=0;
            $sm->updateSettings($settings);
            //echo $post['cred_highlight'];
            die(0);
        }
    } 
}
?>