<?php
class WPRC_LanguageManager
{
    /**
     * Print javascript global array of texts
     */ 
	public static function printJsLanguage()
	{
	    WPRC_Loader::includeLanguageContainer();
        $wprcLang = WPRC_LanguageContainer::getLanguageArray();
        
        if(count($wprcLang)==0)
        {
            return false;
        }
        
        foreach($wprcLang AS $key => $value)
        {
            $lang_js[] = "'".$key."' : '".$value."'";
        } 
        $lang_js_html = implode(',',$lang_js);
        
        $js = "<script type=\"text/javascript\">
            var wprcLang = { 
                $lang_js_html 
            };
        </script>";
        
        echo $js;
	}
}
?>