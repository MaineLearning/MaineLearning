<?php
/**
 * Mail Handler Class
 * 
 * 
 */
class CRED_Mail_Handler
{

    private $_content_type=false;
    private $_to=array();
    private $_from='';
    private $_headers=array();
    private $_attachments=array();
    private $_body='';
    private $_subject='';
    
    
    public function __construct()
    {
        $this->setHTML(false);
    }
    
    public function setHTML($bool)
    {
        $this->_content_type=($bool)?"Content-Type: text/html":"Content-Type: text/plain";
    }
    
    public function setSubject($sub)
    {
        $this->_subject=$sub;
    }
    
    public function setBody($body)
    {
        $this->_body=$body;
    }
    
    public function addHeader($hdr)
    {
        $this->_headers[]=$hdr;
    }
    
    /*public function setAttachment($attach)
    {
    }*/
    
    public function setFrom($addr, $name, $safe=true)
    {
        if ($safe)
            $this->_from="From: $addr";
        else
            $this->_from="From: $name <$addr>";
    }
    
    public function addAddress($addr)
    {
        $this->_to[]=$addr;
    }
    
    protected function buildMail()
    {
        // build header
        $header=array();
        if (!empty($this->_from))
            $header=array_merge($header,array($this->_from));
        $header=array_merge($header,$this->_headers);
        $header=array_merge($header,array($this->_content_type));
        //$header=implode('\r\n',$header).'\r\n';
        
        // build subject
        $subject=$this->_subject;
        
        // build body
        $body=$this->_body;
        
        // build recipient addresses
        $to=$this->_to; //implode(',',$this->_to);
        
        return array('to'=>$to, 'subject'=>$subject, 'body'=>$body, 'header'=>$header);
    }
    
    public function reset()
    {
        $this->_to=array();
        $this->_from='';
        $this->_headers=array();
        $this->_attachments=array();
        $this->_body='';
        $this->_subject='';
        $this->setHTML(false);
    }
    
    public function send()
    {
        extract($this->buildMail());
        
        if (count($to)==0)
            return false;
        
        /*cred_log(print_r($to,true));
        cred_log(print_r($subject,true));
        cred_log(print_r($body,true));*/
        return wp_mail($to, $subject, $body, $header);
    }
}
?>
