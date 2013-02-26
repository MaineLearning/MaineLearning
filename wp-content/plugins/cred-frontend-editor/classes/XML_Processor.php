<?php
/**
*   CRED XML Processor
*   handles import-export to/from XML
*
**/
class CRED_XML_Processor
{

    public static $use_zip_if_available=true;
    
    private static $add_CDATA=false;
    private static $root='forms';
    private static $filename='';
    
    private static function parseArray($array, &$depth, $parent)
    {
        $output = '';
        $indent = str_repeat(' ', $depth * 4);
        $child_key = false;
        if (isset($array['__key'])) {
            $child_key = $array['__key'];
            unset($array['__key']);
        }
        foreach ($array as $key => $value) 
        {
            if (empty($key) && $key!==0) continue;
            
            if (!($key=='settings' && $parent==self::$root))
                $key = $child_key ? $child_key : $key;
            if (is_numeric($key))
                $key=$parent.'_item';//.$key;
            if (!is_array($value) && !is_object($value)) 
            {
                if (self::$add_CDATA && !is_numeric($value) && !empty($value))
                    $output .= $indent . "<$key><![CDATA[" . htmlspecialchars($value, ENT_QUOTES) . "]]></$key>\r\n";
                else
                    $output .= $indent . "<$key>" . htmlspecialchars($value, ENT_QUOTES) . "</$key>\r\n";
            } 
            else 
            {
                if (is_object($value))
                    $value=(array)$value;
                    
                $depth++;
                $output_temp = self::parseArray($value, $depth, $key);
                if (!empty($output_temp)) {
                    $output .= $indent . "<$key>\r\n";
                    $output .= $output_temp;
                    $output .= $indent . "</$key>\r\n";
                }
                $depth--;
            }
        }
        return $output;
    }
    
    private static function array2xml($array, $root_element)
    {
        if (empty($array))
            return "";
        $depth = 1;
        $xml = "";
        $xml .= "<?xml version=\"1.0\" encoding=\"". get_option('blog_charset'). "\"?>\r\n";
        $xml .= "<$root_element>\r\n";
            $xml .= self::parseArray($array[$root_element], $depth, $root_element);
        $xml .="</$root_element>";
        return $xml;
    }
    
    private static function getSelectedFormsForExport($form_ids=array(), &$mode)
    {
        if (empty($form_ids))
            return array();
        
        $data=array();
        
        $forms=CRED_Loader::get('MODEL/Forms')->getFormsForExport($form_ids);
        
        $mode='forms';
        if (!empty($forms) && count($forms)>0)
        {
            if ('all'==$form_ids)
            {
                $mode='all-forms';
            }
            elseif (count($forms)==1)
            {
                $mode=sanitize_title($forms[0]->post_title);
            }
            else
            {
                $mode='selected-forms';
            }
        }
        
        if (!empty($forms)) 
        {
            $export_tags = array('ID', 'post_content', 'post_title', 'post_name', 'post_type');
            $data[self::$root] = array('__key' => 'form');
            foreach ($forms as $key => $form) {
                $form = (array) $form;
                if ($form['post_name']) 
                {
                    $form_data = array();
                    foreach ($export_tags as $e_tag) {
                        if (isset($form[$e_tag])) {
                            $form_data[$e_tag] = $form[$e_tag];
                        }
                    }
                    $data['forms']['form-' . $form['ID']] = $form_data;
                    if (!empty($form['meta'])) 
                    {
                        $data['form']['form-' . $form['ID']]['meta'] = array();
                        foreach ($form['meta'] as $meta_key => $meta_value) 
                        {
                            $data[self::$root]['form-' . $form['ID']]['meta'][$meta_key] = maybe_unserialize($meta_value);
                        }
                        if (empty($data[self::$root]['form-' . $form['ID']]['meta'])) {
                            unset($data[self::$root]['form-' . $form['ID']]['meta']);
                        }
                    }
                }
            }
        }
        return $data;
    }
    
    private static function output($xml, $ajax, $mode)
    {
        $sitename = sanitize_key(get_bloginfo('name'));
        if (!empty($sitename)) {
            $sitename .= '-';
        }
        
        $filename = $sitename . $mode . '-' . date('Y-m-d') . '.xml';
        
        $data=$xml;
        
        if (self::$use_zip_if_available && class_exists('ZipArchive')) 
        { 
            $zipname = $filename . '.zip';
            $zip = new ZipArchive();
            $tmp='tmp';
            // http://php.net/manual/en/function.tempnam.php#93256
            if (function_exists('sys_get_temp_dir'))
                $tmp=sys_get_temp_dir();
            $file = tempnam($tmp, "zip");
            $zip->open($file, ZipArchive::OVERWRITE);
        
            $res = $zip->addFromString($filename, $xml);
            $zip->close();
            $data = file_get_contents($file);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . $zipname);
            header("Content-Type: application/zip");
            header("Content-length: " . strlen($data) . "\n\n");
            header("Content-Transfer-Encoding: binary");
            if ($ajax)
                header("Set-Cookie: __CREDExportDownload=true; path=/");
            echo $data;
            unlink($file);
            die();
        } 
        else 
        {
            // download the xml.
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . $filename);
            header("Content-Type: application/xml");
            header("Content-length: " . strlen($xml) . "\n\n");
            if ($ajax)
                header("Set-Cookie: __CREDExportDownload=true; path=/");
            echo $data;
            die();
        }
    }
    
    private static function simplexml2array($element) 
    {
        $element = is_string($element) ? htmlspecialchars_decode(trim($element), ENT_QUOTES) : $element;
        if (!empty($element) && is_object($element)) 
        {
            $element = (array) $element;
        }
        if (empty($element)) 
        {
            $element = '';
        } 
        if (is_array($element)) 
        {
            foreach ($element as $k => $v) 
            {
                $v = is_string($v) ? htmlspecialchars_decode(trim($v), ENT_QUOTES) : $v;
                if (empty($v)) 
                {
                    $element[$k] = '';
                    continue;
                }
                $add = self::simplexml2array($v);
                if (!empty($add)) 
                {
                    $element[$k] = $add;
                } 
                else 
                {
                    $element[$k] = '';
                }
            }
        }

        if (empty($element)) 
        {
            $element = '';
        }

        return $element;
    }
    
    private static function readXML($file)
    {
        $data = array();
        $info = pathinfo($file['name']);
        $is_zip = $info['extension'] == 'zip' ? true : false;
        if ($is_zip) 
        {
            $zip = zip_open(urldecode($file['tmp_name']));
            if (is_resource($zip)) 
            {
                $zip_entry = zip_read($zip);
                if (is_resource($zip_entry) && zip_entry_open($zip, $zip_entry))
                {
                    $data = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    zip_entry_close ( $zip_entry );
                }
                else
                    return new WP_Error('could_not_open_file', __('No zip entry', 'wp-cred'));
            } 
            else 
            {
                return new WP_Error('could_not_open_file', __('Unable to open zip file', 'wp-cred'));
            }
        } 
        else 
        {
            $fh = fopen($file['tmp_name'], 'r');
            if ($fh) 
            {
                $data = fread($fh, $file['size']);
                fclose($fh);
            }
        }
        
        if (!empty($data)) 
        {

            if (!function_exists('simplexml_load_string')) 
            {
                return new WP_Error('xml_missing', __('The Simple XML library is missing.','wp-cred'));
            }
            $xml = simplexml_load_string($data);
            //print_r($xml);

            if (!$xml) 
            {
                return new WP_Error('not_xml_file', sprintf(__('The XML file (%s) could not be read.','wp-cred'), $file['name']));
            }

            $import_data = self::simplexml2array($xml);
            //print_r($import_data);
            return $import_data;

        } 
        else 
        {
            return new WP_Error('could_not_open_file', __('Could not read the import file.','wp-cred'));
        }
        return new WP_Error('unknown error', __('Unknown error during import','wp-cred'));
    }
    
    private static function loadImportedData($data, $options)
    {
        $results=array(
            'settings'=>0,
            'updated'=>0,
            'new'=>0,
            'failed'=>0,
            'errors'=>array()
        );
        
        if (isset($data['settings']) && isset($options['overwrite_settings']) && $options['overwrite_settings'])
        {
            $setmodel=CRED_Loader::get('MODEL/Settings');
            $oldsettings=$setmodel->getSettings();
            $newsettings=array();
            $newsettings['wizard']=isset($data['settings']['wizard'])?$data['settings']['wizard']:$oldsettings['wizard'];
            $newsettings['syntax_highlight']=isset($data['settings']['syntax_highlight'])?$data['settings']['syntax_highlight']:$oldsettings['syntax_highlight'];
            $newsettings['export_settings']=isset($data['settings']['export_settings'])?$data['settings']['export_settings']:$oldsettings['export_settings'];
            $newsettings['recaptcha']=isset($data['settings']['recaptcha'])?$data['settings']['recaptcha']:$oldsettings['recaptcha'];
            $setmodel->updateSettings($newsettings);
            $results['settings']=1;
            unset($oldsettings);
        }
        
        if (isset($data['settings']))
            unset($data['settings']);
            
        $fmodel=CRED_Loader::get('MODEL/Forms');
        
        if (isset($data['form']) && !empty($data['form']) && is_array($data['form']))
        {
            if (!isset($options['items']))
                $items=false;
            else
                $items=$options['items'];
                
            if (!isset($data['form'][0]))   $data['form']=array($data['form']); // make it array
            foreach ($data['form'] as $key=>$form_data)
            {
                if (!isset($form_data['post_title'])) continue;
                // import only selected items
                if (false!==$items && !in_array($form_data['ID'], $items))  continue;
                
                $form=new stdClass;
                $form->ID='';
                $form->post_title=$form_data['post_title'];
                $form->post_content=isset($form_data['post_content'])?$form_data['post_content']:'';
                $form->post_status='private';
                $form->post_type=CRED_FORMS_CUSTOM_POST_NAME;
                
                $fields=array();
                if (isset($form_data['meta']) && is_array($form_data['meta']) && !empty($form_data['meta']))
                {
                    $fields['form_settings']=new stdClass;
                    $fields['form_settings']->form_type=isset($form_data['meta']['form_settings']['form_type'])?$form_data['meta']['form_settings']['form_type']:'';
                    $fields['form_settings']->form_action=isset($form_data['meta']['form_settings']['form_action'])?$form_data['meta']['form_settings']['form_action']:'';
                    $fields['form_settings']->form_action_page=isset($form_data['meta']['form_settings']['form_action_page'])?$form_data['meta']['form_settings']['form_action_page']:'';
                    $fields['form_settings']->redirect_delay=isset($form_data['meta']['form_settings']['redirect_delay'])?intval($form_data['meta']['form_settings']['redirect_delay']):0;
                    $fields['form_settings']->message=isset($form_data['meta']['form_settings']['message'])?$form_data['meta']['form_settings']['message']:'';
                    $fields['form_settings']->hide_comments=(isset($form_data['meta']['form_settings']['hide_comments'])&&$form_data['meta']['form_settings']['hide_comments']=='1')?1:0;
                    $fields['form_settings']->include_captcha_scaffold=(isset($form_data['meta']['form_settings']['include_captcha_scaffold'])&&$form_data['meta']['form_settings']['include_captcha_scaffold']=='1')?1:0;
                    $fields['form_settings']->include_wpml_scaffold=(isset($form_data['meta']['form_settings']['include_wpml_scaffold'])&&$form_data['meta']['form_settings']['include_wpml_scaffold']=='1')?1:0;
                    $fields['form_settings']->has_media_button=(isset($form_data['meta']['form_settings']['has_media_button'])&&$form_data['meta']['form_settings']['has_media_button']=='1')?1:0;
                    $fields['form_settings']->post_type=isset($form_data['meta']['form_settings']['post_type'])?$form_data['meta']['form_settings']['post_type']:'';
                    $fields['form_settings']->post_status=isset($form_data['meta']['form_settings']['post_status'])?$form_data['meta']['form_settings']['post_status']:'draft';
                    $fields['form_settings']->cred_theme_css=isset($form_data['meta']['form_settings']['cred_theme_css'])?$form_data['meta']['form_settings']['cred_theme_css']:'minimal';
                    
                    $fields['wizard']=isset($form_data['meta']['wizard'])?intval($form_data['meta']['wizard']):-1;
                    
                    $fields['extra']=new stdClass;
                    $fields['extra']->css=isset($form_data['meta']['extra']['css'])?$form_data['meta']['extra']['css']:'';
                    $fields['extra']->js=isset($form_data['meta']['extra']['js'])?$form_data['meta']['extra']['js']:'';
                    
                    $fields['extra']->messages=CRED_Loader::get('MODEL/Forms')->getDefaultMessages();
                    if (isset($form_data['meta']['extra']['messages']['messages_item']))
                    {
                        // make it array
                        if (!isset($form_data['meta']['extra']['messages']['messages_item'][0]))
                            $form_data['meta']['extra']['messages']['messages_item']=array($form_data['meta']['extra']['messages']['messages_item']);
                        
                        foreach ($form_data['meta']['extra']['messages']['messages_item'] as $msg)
                        {
                            foreach (array_keys($fields['extra']->messages) as $msgid)
                            {
                                if (isset($msg[$msgid]))
                                    $fields['extra']->messages[$msgid]=$msg;
                            }
                        }
                    }
                    $fields['notification']=new stdClass;
                    $fields['notification']->notifications=array();
                    if (isset($form_data['meta']['notification']['notifications']['notifications_item']))
                    {
                        // make it array
                        if (!isset($form_data['meta']['notification']['notifications']['notifications_item'][0]))
                            $form_data['meta']['notification']['notifications']['notifications_item']=array($form_data['meta']['notification']['notifications']['notifications_item']);
                        
                        foreach ($form_data['meta']['notification']['notifications']['notifications_item'] as $notif)
                        {
                            $tmp=array();
                            $tmp['mail_to_type']=isset($notif['mail_to_type'])?$notif['mail_to_type']:'';
                            $tmp['mail_to_user']=isset($notif['mail_to_user'])?$notif['mail_to_user']:'';
                            $tmp['mail_to_field']=isset($notif['mail_to_field'])?$notif['mail_to_field']:'';
                            $tmp['mail_to_specific']=isset($notif['mail_to_specific'])?$notif['mail_to_specific']:'';
                            $tmp['subject']=isset($notif['subject'])?$notif['subject']:'';
                            $tmp['body']=isset($notif['body'])?$notif['body']:'';
                            $fields['notification']->notifications[]=$tmp;
                        }
                    }
                    $fields['notification']->enable=(isset($form_data['meta']['notification']['enable'])&&$form_data['meta']['notification']['enable']=='1')?1:0;
                }
                if (isset($options['overwrite_forms']) && $options['overwrite_forms'])
                {
                    $old_form=get_page_by_title( $form->post_title, OBJECT, CRED_FORMS_CUSTOM_POST_NAME );
                    if ($old_form)
                    {
                        $form->ID=$old_form->ID;
                        if ($fmodel->updateForm($form,$fields))
                        {
                            $results['updated']++;
                        }
                        else
                        {
                            $results['failed']++;
                            $results['errors'][]=sprintf(__('Item %s could not be saved','wp-cred'),$form->post_title);
                        }
                    }
                    else
                    {
                        if($fmodel->saveForm($form,$fields))
                        {
                            $results['new']++;
                        }
                        else
                        {
                            $results['failed']++;
                            $results['errors'][]=sprintf(__('Item %s could not be saved','wp-cred'),$form->post_title);
                        }
                    }
                }
                else
                {
                    $fmodel->saveForm($form,$fields);
                    $results['new']++;
                }
            }
        }
        return $results;
    }
    
    // public wrapper methods to use
    
    public static function exportToXML($forms, $ajax=false)
    {
        $mode='forms';
        $data=self::getSelectedFormsForExport($forms, $mode);
        $setts=CRED_Loader::get('MODEL/Settings')->getSettings();
        if (isset($setts['export_settings']) && $setts['export_settings'])
            $data[self::$root]['settings']=$setts;
        $xml=self::array2xml($data,self::$root);
        self::output($xml, $ajax, $mode);
    }
    
    public static function exportToXMLString($forms)
    {
        $mode='forms';
        $data=self::getSelectedFormsForExport($forms, $mode);
        $setts=CRED_Loader::get('MODEL/Settings')->getSettings();
        if (isset($setts['export_settings']) && $setts['export_settings'])
            $data[self::$root]['settings']=$setts;
        $xml=self::array2xml($data,self::$root);
        return $xml;
    }
    
    public static function importFromXML($file, $options=array())
    {
        $dataresult=self::readXML($file);
        if ($dataresult!==false && !is_wp_error($dataresult))
        {
           $results = self::loadImportedData($dataresult, $options);
           return $results;
        }
        else
        {
            return $dataresult;
        }
    }
    
    public static function importFromXMLString($xmlstring, $options=array())
    {
        if (!function_exists('simplexml_load_string')) 
        {
            return new WP_Error('xml_missing', __('The Simple XML library is missing.','wp-cred'));
        }
        $xml = simplexml_load_string($xmlstring);
        
        $dataresult=self::simplexml2array($xml);

        if ($dataresult!==false && !is_wp_error($dataresult))
        {
           $results = self::loadImportedData($dataresult, $options);
           return $results;
        }
        else
        {
            return $dataresult;
        }
    }
}
?>