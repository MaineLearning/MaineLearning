<?php
/**
 * Form Builder Class
 * 
 */

class CRED_Form_Builder
{

       // constants
       const METHOD='POST';                                         // form method POST
       const NONCE='_cred_cred_wpnonce';                            // nonce field name
       const PREFIX='_cred_cred_prefix_';                           // prefix for various hidden auxiliary fields
       const POST_CONTENT_TAG='%__CRED__CRED__POST__CONTENT__%';    // placeholder for post content
       const FORM_TAG='%__CRED__CRED__FORM___FORM__%';              // 
       const DELAY=0;                                               // seconds delay before redirection
       
       // STATIC Properties
       private static $ASSETS_PATH;                                 // physical path to files needed for Zebra form
       private static $ASSETS_URL;                                  // url for this physical path
       private static $msgpre='Message_';
       private static $localized_strings=null;                      // string localization
       private static $form_count=0;                                // number of forms rendered on same page
       private static $recaptcha_settings=false;                    // settings for recaptcha API
       private static $css_loaded=array();                          // references to CSS files that have been loaded
       private static $recaptcha_js_loaded=false;                   // flag indicating whether recaptcha API has been loaded
       private static $form_cache=array();                          // cache rendered forms here for future reference (eg by shortcodes)
       private static $_current_user=null;                          // info about current user using this form
       
       // Public Instance  properties
       public $hasRecaptcha=false;                                  // flag indicates if specific form uses recaptcha API
       public $css_to_use='';                                       // custom CSS link for specific form
       
       // Private Instance properties
       private $_shortcode_parser=null;                             // shortcode parser class
       private $_current_user_instance=null;                        // info about current user using this form
       private $_field_values_map=array();                          // map between values in DB and values in HTML for some fields in which these differ
       private $_post_content='';                                   // holds the post_content of post editied by this form
       private $_notification_data='';                              // formatted form data used in notifications
       private $wp_mimes;                                           // WP allowed mime types (for file uploads)
       private $error=false;                                        // form error message
       private $_post_data=false;                                   // if edit form, this keeps the initial post data
       private $_post_id=false;                                     // ID of post to EDIT (if edit form) or new ID for new post
       private $_post_id_field=false;
       private $_nonce_field=false;
       private $_form_id;                                           // ID of this form
       private $_form_id_field;
       private $_form_count_field;
       private $_conditionals=array();                              // references to conditional display blocks
       private $_redirect_delay;                                    // seconds before this form redirects (if such option is set)
       private $_redirect_url;                                      // url to redirect to (if such option set)
       private $_hide_comments=false;                               // whether this form should try to hide comments section in templates (experimental)
       private $_form_js='';                                        // extra javascript to render for this form (eg conditional logic data)
       private $_form_count;                                        // ordinal number of this form in the page
       private $_form;                                              // actual form data and settings as stored in DB
       private $_post_type;                                         // Post type this form operates on
       private $_form_type;                                         // edit or new
       private $_extra=false;                                       // extra assets like css or js
       private $_fields;                                            // field settings related to this post type
       private $_form_fields=array();                               // references to fields that are actually rendered
       private $_form_fields_qualia=array();                        // additinal info about final rendered fields
       private $_generic_fields=array();                            // refernces to fields that are generic
       private $_myzebra_form;                                      // reference to MyZebra Form framework, to render forntend forms
       //private $_controls=array();                                  // references to form controls html (that relate to form fields)
       private $_current_group=null;                                // store conditional groups references
       private $_child_groups=null;                                // store conditional groups references
       private $_form_content='';                                   // the raw content of this form (the shortcodes)
       private $_form_attributes=array();                           // html attributes of this form
       private $_content='';                                        // the whole raw content of this form (including other shortcodes)
       private $_taxonomy_aux=array('taxonomy'=>array(),'aux'=>array());    // reference map for taxonomy fields and their accompanying auxiliary fields
       private $supported_date_formats = array('F j, Y', //December 23, 2011
                'Y/m/d', // 2011/12/23
                'm/d/Y', // 12/23/2011
                'd/m/Y' // 23/12/2011
            );
       
       /*================= STATIC METHODS ========================================*/
       
       // true if forms have been built for current page
       public static function has_form()
       {
            return (self::$form_count>0);
       }
        
       // init public function
       public static function init()
       {
            add_action('wp_loaded',array('CRED_Form_Builder','_init'),10);
       }
       
       // check for form submissions on init
       public static function _init()
       {
            // check for cred form submissions
            if (!is_admin())
            {
                // reference to the form submission method
                global ${'_' . self::METHOD};

                $method = & ${'_' . self::METHOD};
                
                if (array_key_exists(self::PREFIX.'form_id',$method) && array_key_exists(self::PREFIX.'form_count',$method))
                {
                    $form=intval($method[self::PREFIX.'form_id']);
                    $form_count=intval($method[self::PREFIX.'form_count']);
                    
                    // edit form
                    if (array_key_exists(self::PREFIX.'post_id',$method))
                        $post_id=intval($method[self::PREFIX.'post_id']);
                    else
                        $post_id=false;
                        
                    // preview form
                    if (array_key_exists(self::PREFIX.'form_preview_content',$method))
                        $preview=true;
                    else
                        $preview=false;
                    
                    // parse and cache form
                    $fb=new CRED_Form_Builder($form, $post_id, $preview, $form_count);
                    self::$form_cache[$form.'_'.$form_count]=array(
                        'form' =>  $fb->form(),
                        'form_count' => $form_count,
                        'hide_comments' =>  $fb->getHideComments(),
                        'css_to_use' => $fb->css_to_use,
                        'extra' => $fb->getExtra(),
                        'form_js' => $fb->getFormJS(),
                        'hasRecaptcha' =>  $fb->hasRecaptcha
                    );
                }
            }
       }
       
        // load frontend assets on init
        public static function load_cred_form_frontend_assets() 
        {
            // register assets and assign them to footer
            if (defined('CRED_DEV')&&CRED_DEV)
            {
                wp_register_script( 'cred_myzebra_form', CRED_PLUGIN_URL.'/third-party/zebra_form/public/javascript/zebra_form_dev.js',
                array('jquery','suggest','thickbox'), CRED_FE_VERSION, 1);
                wp_register_script( 'cred_myzebra_parser', CRED_PLUGIN_URL.'/third-party/zebra_form/public/javascript/zebra_parser.js',
                array('cred_myzebra_form'), CRED_FE_VERSION, 1);
            }
            else
            {
                wp_register_script( 'cred_myzebra_form', CRED_PLUGIN_URL.'/third-party/zebra_form/public/javascript/zebra_form.js',
                array('jquery','suggest','thickbox'), CRED_FE_VERSION, 1);
            }
            wp_register_script( 're_captcha_ajax', 'http://www.google.com/recaptcha/api/js/recaptcha_ajax.js',
            array('cred_myzebra_form'), CRED_FE_VERSION, 1);
            
            wp_enqueue_script('cred_myzebra_form');
        }
         

        // unload frontend assets if no form rendered on page
        public static function unload_cred_form_frontend_assets() 
        {
            //wp_deregister_script('jquery');
            
            // unload them when not needed
            if (self::$form_count==0)
            {
                wp_dequeue_script('cred_myzebra_form');
                wp_deregister_script('cred_myzebra_form');
            }
            else
            {
                // load css first 
                wp_enqueue_style('thickbox');
                foreach (self::$form_cache as $form_data)
                {
                    // if this css is not already loaded, load it
                    if (!in_array($form_data['css_to_use'], self::$css_loaded))
                    {            
                        wp_enqueue_style(
                            'cred_form_custom_css_'.$form_data['form_count'],
                            $form_data['css_to_use'], null, CRED_FE_VERSION
                        );
                        self::$css_loaded[]=$form_data['css_to_use'];
                        wp_print_styles(
                            'cred_form_custom_css_'.$form_data['form_count']
                        );
                    }
                }
                
                // include client side assets (just in time)
                $myzebra_js_settings=array(
                        'add_new_repeatable_field' =>  self::$localized_strings['add_new_repeatable_field'],
                        'remove_repeatable_field'   =>  self::$localized_strings['remove_repeatable_field'],
                        'cancel_upload_text' => self::$localized_strings['cancel_upload_text'],
                        'days' => self::$localized_strings['days'],
                        'months' => self::$localized_strings['months'],
                        'insertMediaIconURL' => admin_url().'/images/media-button.png',
                        'insertMediaPopupURL' => admin_url().'/media-upload.php',
                        'PREFIX'=>self::PREFIX,
                        'parser_info'=>array('user'=>self::$_current_user)
                );
                
                
                // check jquery dependency
                $doing_jquery = wp_script_is('jquery', 'registered');
                if (!$doing_jquery)
                    wp_enqueue_script('jquery', admin_url().'/wp-includes/js/jquery/jquery.js',null, CRED_FE_VERSION, 1);
                
                wp_localize_script('cred_myzebra_form', 'myzebra', $myzebra_js_settings );
                wp_print_scripts('cred_myzebra_form');
                if (defined('CRED_DEV')&&CRED_DEV)
                    wp_print_scripts('cred_myzebra_parser');
                
                //cred_log(self::$form_cache);
                // add additional only if it is rendered
                foreach (self::$form_cache as $form_data)
                {
                    if ($form_data['hasRecaptcha'] && !self::$recaptcha_js_loaded)
                    {
                        wp_print_scripts('re_captcha_ajax');
                        self::$recaptcha_js_loaded=true;
                        //break;
                    }
                    
                    if (isset($form_data['extra']))
                    {
                        if (isset($form_data['extra']['css']) && !empty($form_data['extra']['css']))
                        {
                            echo "\n<style type='text/css'>\n";
                            echo $form_data['extra']['css']."\n";
                            echo "</style>\n";
                        }
                        if (isset($form_data['extra']['js']) && !empty($form_data['extra']['js']))
                        {
                            echo "\n<script type='text/javascript'>\n";
                            echo $form_data['extra']['js']."\n";
                            echo "</script>\n";
                        }
                    }
                }
                
                // echo specific inline javascript for each form
                foreach (self::$form_cache as $form_data)
                    echo $form_data['form_js'];
            }
        }
      
        public static function makeCommentsClosed($open,$post_id)
        {
            return false;
        }
        
        public static function noComments($comments,$post_id)
        {
            return array();
        }

        private static function hideComments()
        {
            global $post, $wp_query;
            // hide comments
            if (isset($post))
            {
                //global $_wp_post_type_features;
                remove_post_type_support($post->post_type,'comments');
                remove_post_type_support($post->post_type,'trackbacks');
                $post->comment_status="closed";
                $post->ping_status="closed";
                $post->comment_count=0;
                $wp_query->comment_count=0;
                $wp_query->comments=array();
                add_filter('comments_open', array('CRED_Form_Builder', 'makeCommentsClosed'), 100, 2);
                add_filter('pings_open', array('CRED_Form_Builder', 'makeCommentsClosed'), 100, 2);
                add_filter('comments_array', array('CRED_Form_Builder', 'noComments'), 100, 2);
                // as a last resort, use the template hook
                //add_filter('comments_template', STYLESHEETPATH . $file );
            }
        }
      
      // get form html output for given form (form is processed if data submitted)
       public static function getForm($form, $post_id=null, $preview=false)
       {
            self::initVars();
            ++self::$form_count;
            
            //add_filter('cred_form_validate',array($this,'check_hooks'),10);
            if (is_string($form) && !is_numeric($form))
            {
                $form_p=get_page_by_title( $form, OBJECT, CRED_FORMS_CUSTOM_POST_NAME );
                if ($form_p && is_object($form_p))
                    $form=$form_p->ID;
                else return '';
            }
                
            if (!array_key_exists($form.'_'.self::$form_count,self::$form_cache))
            {
                // parse and cache form
                $fb=new CRED_Form_Builder($form,$post_id,$preview);
                self::$form_cache[$form.'_'.self::$form_count]=array(
                    'form' =>  $fb->form(),
                    'form_count' => self::$form_count,
                    'hide_comments' =>  $fb->getHideComments(),
                    'css_to_use' => $fb->css_to_use,
                    'extra' => $fb->getExtra(),
                    'form_js' => $fb->getFormJS(),
                    'hasRecaptcha' =>  $fb->hasRecaptcha
                );
            }
            
            // add filter to hide comments (new method)
            if (self::$form_cache[$form.'_'.self::$form_count]['hide_comments'])
              self::hideComments();
            return  self::$form_cache[$form.'_'.self::$form_count]['form'];
       }
       
       // extra sanitization methods to be used by form framework
       public static function esc_js($data) {return esc_js($data);}
       public static function esc_attr($data) {return esc_attr($data);}
       public static function esc_textarea($data) {return esc_textarea($data);}
       public static function esc_html($data) {return esc_html($data);}
       public static function esc_url($data) {return esc_url($data);}
       public static function esc_url_raw($data) {return esc_url_raw($data);}
       public static function esc_sql($data) {return esc_sql($data);}
        
        // utility methods
        private static function getUserRolesByID( $user_id ) 
        {
            $user = get_userdata( $user_id );
            return empty( $user ) ? array() : $user->roles;
        }
       
       private function getLocalisedMessage($id)
       {
            $id='cred_message_'.$id;
            return cred_translate(self::$msgpre.$id, $this->_extra['messages'][$id]['msg'], 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID);
       }
       
       // initialize some vars that are used by all instances
       private static function initVars()
       {
            if (self::$localized_strings===null)
            {
                self::$localized_strings=array(
                    'clear_date'    => __('Clear','wp-cred'),
                    'csrf_detected' => __('There was a problem with your submission!<br>Possible causes may be that the submission has taken too long, or it represents a duplicate request.<br>Please try again.','wp-cred'),
                    'days'          => array(__('Sunday','wp-cred'),__('Monday','wp-cred'),__('Tuesday','wp-cred'),__('Wednesday','wp-cred'),__('Thursday','wp-cred'),__('Friday','wp-cred'),__('Saturday','wp-cred')),
                    'months'        => array(__('January','wp-cred'),__('February','wp-cred'),__('March','wp-cred'),__('April','wp-cred'),__('May','wp-cred'),__('June','wp-cred'),__('July','wp-cred'),__('August','wp-cred'),__('September','wp-cred'),__('October','wp-cred'),__('November','wp-cred'),__('December','wp-cred')),
                    //'days'          => array(__('myday','wp-cred'),__('myotherday','wp-cred'),__('Tuesday','wp-cred'),__('Wednesday','wp-cred'),__('Thursday','wp-cred'),__('Friday','wp-cred'),__('Saturday','wp-cred')),
                    //'months'        => array(__('mymonth','wp-cred'),__('myothermonth','wp-cred'),__('March','wp-cred'),__('April','wp-cred'),__('May','wp-cred'),__('June','wp-cred'),__('July','wp-cred'),__('August','wp-cred'),__('September','wp-cred'),__('October','wp-cred'),__('November','wp-cred'),__('December','wp-cred')),
                    'other'         => __('Other...','wp-cred'),
                    'select'        => __('- select -','wp-cred'),
                    'add_new_repeatable_field' =>  __('Add Another','wp-cred'),
                    'remove_repeatable_field'   =>  __('Remove','wp-cred'),
                    'cancel_upload_text' => __('Retry Upload','wp-cred'),
                    'spam_detected' => __('Possible spam attempt detected. The posted form data was rejected.','wp-cred'),
                    '_days' => array('Sunday'=>__('Sunday','wp-cred'),'Monday'=>__('Monday','wp-cred'),'Tuesday'=>__('Tuesday','wp-cred'),'Wednesday'=>__('Wednesday','wp-cred'),'Thursday'=>__('Thursday','wp-cred'),'Friday'=>__('Friday','wp-cred'),'Saturday'=>__('Saturday','wp-cred')),
                    '_months' => array('January'=>__('January','wp-cred'),'February'=>__('February','wp-cred'),'March'=>__('March','wp-cred'),'April'=>__('April','wp-cred'),'May'=>__('May','wp-cred'),'June'=>__('June','wp-cred'),'July'=>__('July','wp-cred'),'August'=>__('August','wp-cred'),'September'=>__('September','wp-cred'),'October'=>__('October','wp-cred'),'November'=>__('November','wp-cred'),'December'=>__('December','wp-cred'))     
                );
            }
            if (self::$_current_user===null)
            {
                self::$_current_user=self::getCurrentUserData();
            }

       }

        private static function getCurrentUserData()
        {
            global $current_user;
            
            $user_data=new stdClass;
            
            $user_data->ID=isset($current_user->ID)?$current_user->ID:0;
            $user_data->roles=isset($current_user->roles)?$current_user->roles:array();
            $user_data->role=isset($current_user->roles[0])?$current_user->roles[0]:'';
            $user_data->login=isset($current_user->data->user_login)?$current_user->data->user_login:'';
            $user_data->display_name=isset($current_user->data->display_name)?$current_user->data->display_name:'';
         
            //print_r($user_data);
            return $user_data;
        }
       
       /*================ INSTANCE Methods ==============================*/
       
       // constuctor, return a CRED form object
       public function __construct($form, $post_id=null, $preview=false, $force_form_count=false)
       {
            global $post, $current_user;
            
            // reference to the form submission method
            global ${'_' . self::METHOD};

            $method = & ${'_' . self::METHOD};
            
            // if types is not active, no CRED
            if (!function_exists('wpcf_init') || !defined('WPCF_ABSPATH'))
            {
                $this->error=__('Types plugin not active','wp-cred');
                return;
            }
            
            self::initVars();

            // get inputs
            if (isset($post_id) && !empty($post_id) && $post_id!=false && !$preview)
                $post_id=intval($post_id);
            elseif (isset($post->ID) && !$preview)
                $post_id=$post->ID;
            else
                $post_id=false;
            
            // get recaptcha settings
            if (!self::$recaptcha_settings)
            {
                $sm=CRED_Loader::get('MODEL/Settings');
                $gen_setts=$sm->getSettings();
                if (
                    isset($gen_setts['recaptcha']['public_key']) && 
                    isset($gen_setts['recaptcha']['private_key']) && 
                    !empty($gen_setts['recaptcha']['public_key']) &&
                    !empty($gen_setts['recaptcha']['private_key'])
                    )
                self::$recaptcha_settings=$gen_setts['recaptcha'];
            }
            // load form data
            require_once(ABSPATH.'/wp-admin/includes/post.php');
            $fm=CRED_Loader::get('MODEL/Forms');
            $this->_form= $fm->getForm($form);
            if ($this->_form===false)
            {
                $this->error=__('Form does not exist!','wp-cred');
                return;
            }
            
            $this->_form_id=$this->_form->form->ID;
            // preview when form is not saved at all
            //print_r($this->_form);
            if (
            !isset($this->_form->fields) || !is_array($this->_form->fields) || empty($this->_form->fields) ||
            !isset($this->_form->fields['form_settings'])
            )
            {
                $this->_form->fields=array(
                    'form_settings'=>new stdClass,
                    'extra'=>new stdClass,
                    'notification'=>new stdClass,
                    //'wizard'=>-1
                );
                
                if ($preview)
                {
                    $this->error=__('Form preview does not exist. Try saving your form first','wp-cred');
                    return;
                }
            }
            $this->_redirect_delay=isset($this->_form->fields['form_settings']->redirect_delay)?intval($this->_form->fields['form_settings']->redirect_delay):self::DELAY;
            $this->_hide_comments=(isset($this->_form->fields['form_settings']->hide_comments)&&$this->_form->fields['form_settings']->hide_comments)?true:false;
                
            $form_id=$this->_form->form->ID;
            
            $cred_css_themes=array(
                'minimal'=>CRED_PLUGIN_URL.'/third-party/zebra_form/public/css/minimal.css',
                'styled'=>CRED_PLUGIN_URL.'/third-party/zebra_form/public/css/styled.css'
            );
            
            $this->_extra=array();
            
            if ($preview)
            {
                if (array_key_exists(self::PREFIX.'form_preview_post_type',$method))
                    $this->_post_type=$this->_form->fields['form_settings']->post_type=stripslashes($method[self::PREFIX.'form_preview_post_type']);
                else
                {
                    $this->error=__('Preview post type not provided','wp-cred');
                    return;
                }
                
                if (array_key_exists(self::PREFIX.'form_preview_form_type',$method))
                    $this->_form_type=stripslashes($method[self::PREFIX.'form_preview_form_type']);
                else
                {
                    $this->error=__('Preview form type not provided','wp-cred');
                    return;
                }
                if (array_key_exists(self::PREFIX.'form_preview_content',$method))
                {
                    $this->_preview_content=stripslashes($method[self::PREFIX.'form_preview_content']);
                    $this->_content=stripslashes($method[self::PREFIX.'form_preview_content']);
                }
                else
                {
                    $this->error=__('No preview form content provided','wp-cred');
                    return;
                }
                if (array_key_exists(self::PREFIX.'form_css_to_use',$method))
                {
                    $this->css_to_use=trim(stripslashes($method[self::PREFIX.'form_css_to_use']));
                    if (in_array($this->css_to_use, array_keys($cred_css_themes)))
                        $this->css_to_use=$cred_css_themes[$this->css_to_use];
                    else
                        $this->css_to_use=$cred_css_themes['minimal'];
                }
                else
                {
                    $this->css_to_use=$cred_css_themes['minimal'];
                }
                if (array_key_exists(self::PREFIX.'extra_css_to_use',$method))
                {
                    $this->_extra['css']=trim(stripslashes($method[self::PREFIX.'extra_css_to_use']));
                }
                if (array_key_exists(self::PREFIX.'extra_js_to_use',$method))
                {
                    $this->_extra['js']=trim(stripslashes($method[self::PREFIX.'extra_js_to_use']));
                }
            }
            else
            {
                $this->_post_type=$this->_form->fields['form_settings']->post_type;
                $this->_form_type=$this->_form->fields['form_settings']->form_type;
                $this->_extra=isset($this->_form->fields['extra'])?(array)($this->_form->fields['extra']):array();
                
                 // get form content in order to replace it with actual form
                $this->_content=$this->_form->form->post_content;
                
                if (isset($this->_form->fields['form_settings']->cred_theme_css) && 
                        in_array($this->_form->fields['form_settings']->cred_theme_css,array_keys($cred_css_themes)))
                    $this->css_to_use=$cred_css_themes[$this->_form->fields['form_settings']->cred_theme_css];
                else
                    $this->css_to_use=$cred_css_themes['minimal'];
            }
            
            if (!isset($this->_extra['messages']))
            {
                if (isset($this->_form->fields['extra']) && isset($this->_form->fields['extra']->messages))
                    $this->_extra['messages']=$this->_form->fields['extra']->messages;
                else
                    $this->_extra['messages']=CRED_Loader::get('MODEL/Forms')->getDefaultMessages();
            }
            
            // if this is an edit form and no post id given
            if ($this->_form_type=='edit' && $post_id===false && !$preview)
            {
                $this->error=__('No post specified','wp-cred');
                return;
            }
            
            // if this is a new form and post id given
            if ($this->_form_type=='new' && !$preview /*&& $post_id==false*/)
            {
                if (isset($method[self::PREFIX.'post_id']) && intval($method[self::PREFIX.'post_id'])>0)
                    $post_id=intval($method[self::PREFIX.'post_id']);
                else
                    $post_id=get_default_post_to_edit( $this->_post_type, true )->ID;
            }
            
            $this->_post_id=$post_id;
            
            // increase counter
            //self::$form_count++;
            
            if ($force_form_count!==false)
                $this->_form_count=$force_form_count;
            else
                $this->_form_count=self::$form_count;
                
            // dependencies, uses Zebra_Form framework (see folder for details)
            CRED_Loader::load('THIRDPARTY/MyZebra_Parser');
            CRED_Loader::load('THIRDPARTY/MyZebra_Form');
            // instantiate form
            $this->_myzebra_form=new MyZebra_Form('cred_form_'.$form_id.'_'.$this->_form_count, self::METHOD, $this->currentURI(array(
                '_tt'=>time() // add time get bypass cache
            ),array(
                '_success'  // remove previous success get if set
            )), '', array(
                // extra XSS methods, passed to zebra form, disabled
                /*'html'=>array('CRED_Form_Builder','esc_html'),
                'textarea'=>array('CRED_Form_Builder','esc_textarea'),
                'attr'=>array('CRED_Form_Builder','esc_attr'),
                'js'=>array('CRED_Form_Builder','esc_js'),
                'url'=>array('CRED_Form_Builder','esc_url'),
                'url_raw'=>array('CRED_Form_Builder','esc_url_raw'),
                'sql'=>array('CRED_Form_Builder','esc_sql'),*/
            ));
            
            if ($preview)
                $this->_myzebra_form->preview=true;
            else
                $this->_myzebra_form->preview=false;
            
            // form properties
            self::$ASSETS_PATH=DIRECTORY_SEPARATOR.'third-party'.DIRECTORY_SEPARATOR.'zebra_form'.DIRECTORY_SEPARATOR;
            self::$ASSETS_URL='/third-party/zebra_form/';
            $this->_myzebra_form->doctype('xhtml');            
            $this->_myzebra_form->client_side_validation(true);
            $this->_myzebra_form->show_all_error_messages(true);
            $this->_myzebra_form->assets_path(CRED_PLUGIN_PATH.self::$ASSETS_PATH, plugins_url().'/'.CRED_PLUGIN_FOLDER.self::$ASSETS_URL);
            $locale=self::$localized_strings;
            $this->_myzebra_form->language($locale);
            
            
            // get custom post fields
            $ffm=CRED_Loader::get('MODEL/Fields');
            $this->_fields= $ffm->getFields($this->_post_type);
            // in CRED 1.1 post_fields and custom_fields are different keys, merge them together to keep consistency
            $this->_fields['_post_fields']=$this->_fields['post_fields'];
            $this->_fields['post_fields']=array_merge($this->_fields['post_fields'],$this->_fields['custom_fields']);
            
            //cred_log(print_r($this->_fields,true));
            
            // get existing post data if edit form and post given
            if ($this->_form_type=='edit')
            {
                if ($post_id)
                {
            
                    $res = $fm->getPost($post_id);
                    if ($res && isset($res[0]))
                    {
                        $mypost=$res[0];
                        cred_log(array('edit_own_posts_with_cred_'.$form_id=>current_user_can('edit_own_posts_with_cred_'.$form_id),'current_user'=>$current_user->ID,'author'=>$mypost->post_author), 'access.log');
                        cred_log(array('edit_other_posts_with_cred_'.$form_id=>current_user_can('edit_other_posts_with_cred_'.$form_id),'current_user'=>$current_user->ID,'author'=>$mypost->post_author), 'access.log');
                        if (!current_user_can('edit_own_posts_with_cred_'.$form_id) && $current_user->ID == $mypost->post_author)
                        {
                                //$this->error=__('Do not have permission (edit own with this form)','wp-cred');
                                $this->error=' ';
                                return;
                        }
                        if (!current_user_can('edit_other_posts_with_cred_'.$form_id) && $current_user->ID != $mypost->post_author)
                        {
                                //$this->error=__('Do not have permission (edit other with this form)','wp-cred');
                                $this->error=' ';
                                return;
                        }
                        //cred_log($mypost->post_content);
                        if ($mypost->post_type!=$this->_post_type)
                        {
                            $this->error=__('Form type and post type do not match','wp-cred');
                            return;
                        }
                        $myfields=isset($res[1])?$res[1]:array();
                        $mytaxs=isset($res[2])?$res[2]:array();
                        $myextra=isset($res[3])?$res[3]:array();
                        $myfields['post_title']=array($mypost->post_title);
                        $myfields['post_content']=array($mypost->post_content);
                        if (isset($mypost->post_excerpt))
                            $myfields['post_excerpt']=array($mypost->post_excerpt);
                        $this->_post_data=array(
                                'fields'=>&$myfields,
                                'post'=>&$mypost,
                                'taxonomies'=>&$mytaxs,
                                'extra'=>&$myextra
                                );
                        //cred_log(print_r($mytaxs,true));
                        //cred_log(print_r($mypost,true)/*.print_r($myfields,true).print_r($myterms,true)*/);
                        //exit;
                    }
                }
            }
            elseif ($this->_form_type=='new')
            {
                cred_log(array('create_posts_with_cred_'.$form_id=>current_user_can('create_posts_with_cred_'.$form_id),'current_user'=>$current_user->ID), 'access.log');
                if (!current_user_can('create_posts_with_cred_'.$form_id))
                {
                    //$this->error=__('Do not have permission (create with this form)','wp-cred');
                    $this->error=' ';
                    return;
                }
            }
            
            
            $this->_form_content='';
            
            // set allowed file types
            $mimes=get_allowed_mime_types();
            $this->wp_mimes=array();
            foreach ($mimes as $exts=>$mime)
            {
                $exts_a=explode('|',$exts);
                foreach ($exts_a as $single_ext)
                {
                    //$this->form_mimes[$single_ext]=$mime;
                    $this->wp_mimes[]=$single_ext;
                }
            }
            $this->wp_mimes=implode(',',$this->wp_mimes);
            unset($mimes);
            $this->_shortcode_parser=CRED_Loader::get('CLASS/Shortcode_Parser', false);
       }
       
       // whether this form tries to hide comments
       public function getHideComments() {return $this->_hide_comments;}
       
       // get extra javascript needed by this form
       public function getFormJS() {return $this->_form_js;}
       
       // get extra javascript needed by this form
       public function getExtra() {return $this->_extra;}
       
       // get current url under which this is executed
       private function currentURI($replace_get=array(), $remove_get=array()) 
       {
            $request_uri=$_SERVER["REQUEST_URI"];
            if (!empty($replace_get))
            {
                $request_uri=explode('?',$request_uri,2);
                $request_uri=$request_uri[0];
                
                parse_str($_SERVER['QUERY_STRING'], $get_params);
                if (empty($get_params)) $get_params=array();
                
                foreach ($replace_get as $key=>$value)
                {
                    $get_params[$key]=$value;
                }
                if (!empty($remove_get))
                {
                    foreach ($get_params as $key=>$value)
                    {
                        if (isset($remove_get[$key]))
                            unset($get_params[$key]);
                    }
                }
                if (!empty($get_params))
                    $request_uri.='?'.http_build_query($get_params, '', '&');
            }
            return $request_uri;
            
            /*
            $pageURL = 'http';
            if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") 
            {
                $pageURL .= "s";
            }
            $pageURL .= "://";
            if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") 
            {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$request_uri;
            } 
            else 
            {
                $pageURL .= $_SERVER["SERVER_NAME"].$request_uri;
            }
            return $pageURL;*/
        }
        
       // manage form submission / validation and rendering and return rendered html 
       public function form()
       {
            // if some error happened, display a message instead
            if ($this->error!==false)
                return "<strong>".$this->error."</strong>";
                
            $id='';
            if ($this->_post_data!==false && isset($this->_post_data['post']))
                $id=$this->_post_data['post']->ID;
            
           $this->build();
            $message=false;
            
            // show success message from previous submit of same create form (P-R-G pattern)
            if (
                !$this->_myzebra_form->preview && 
                $this->_form->fields['form_settings']->form_type!='edit' && 
                isset($_GET['_success']) &&
                $_GET['_success']==$this->_form_id
                )
            {
                $this->_myzebra_form->add_form_message('data-saved',$this->getLocalisedMessage('post_saved'));
            }
            
            if ($this->validate())
            {
                if (!$this->_myzebra_form->preview)
                {
                    // save post data
                    $result=$this->saveData($id);
                    if (is_int($result))
                    {
                        $this->_myzebra_form->add_form_message('data-saved',$this->getLocalisedMessage('post_saved'));
                        
                        // send notification
                        if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                        {
                            $this->sendNotifications($result);
                        }
                        
                        // reset form if needed
                        if ($this->_form->fields['form_settings']->form_type!='edit')
                        {
                            // restore nonce value
                            $_nonce=$this->_myzebra_form->controls[$this->_nonce_field]->attributes['value'];
                            $this->_myzebra_form->reset();
                            $this->_myzebra_form->controls[$this->_nonce_field]->set_attributes(array('value'=>$_nonce));
                            
                            // regenerate dummy post id
                            $this->_post_id=get_default_post_to_edit( $this->_post_type, true )->ID;
                            if ($this->_post_id_field)
                                $this->_myzebra_form->controls[$this->_post_id_field]->set_attributes(array('value'=>$this->_post_id),true);
                            
                            // restore form_id
                            $this->_myzebra_form->controls[$this->_form_id_field]->set_attributes(array('value'=>$this->_form_id));
                            // restore form_count
                            $this->_myzebra_form->controls[$this->_form_count_field]->set_attributes(array('value'=>$this->_form_count));
                        }
                        
                        $thisform=array(
                            'id'=>$this->_form_id,
                            'post_type'=>$this->_post_type,
                            'form_type'=>$this->_form_type
                        );
                        // do action here
                        // user can redirect, display messages, overwrite page etc..
                        do_action('cred_submit_complete_'.$this->_form_id, $result, $thisform);
                        do_action('cred_submit_complete', $result, $thisform);
                        
                        // do success action
                        $credaction=$this->_form->fields['form_settings']->form_action;
                        switch($credaction)
                        {
                            case 'post':
                                $url=get_permalink($result);
                                break;
                            case 'page':
                                $url=(!empty($this->_form->fields['form_settings']->form_action_page))?get_permalink($this->_form->fields['form_settings']->form_action_page):false;
                                break;
                            case 'message':
                                $message=do_shortcode(cred_translate('Display Message: '.$this->_form->form->post_title, $this->_form->fields['form_settings']->message, 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID));
                            case 'form':
                            default:
                                // PRG (POST-REDIRECT-GET) pattern, to avoid resubmit on browser refresh issue, and also keep defaults on new form !! :)
                                if ($this->_form->fields['form_settings']->form_type!='edit')
                                {
                                    $url=$this->currentURI(array(
                                        '_tt'=>time(),
                                        '_success'=>$this->_form_id
                                        ));
                                    $this->_redirect_delay=0;
                                    if (!headers_sent())   header("HTTP/1.1 303 See Other");
                                }
                                else
                                {
                                    $url=false;
                                }
                                break;
                        }
                        
                        if ($url!==false)
                        {
                            if ('form'!=$credaction)
                            {
                                $url = apply_filters('cred_success_redirect_'.$this->_form_id,$url, $result, $thisform);
                                $url = apply_filters('cred_success_redirect',$url, $result, $thisform);
                            }
                            $this->_redirect_url=$url;
                            
                            if ($url!==false)
                            {
                                if ($this->_redirect_delay < 0)
                                    $this->_redirect_delay=0;
                                
                                if (!headers_sent())
                                {
                                    if ($this->_redirect_delay<=0)
                                    {
                                        header("Location: $url");
                                        exit();
                                    }
                                    // redirect after a delay
                                    else
                                    {
                                        //header("Refresh: ".self::DELAY."; url='$url'");
                                        add_action('wp_head', array(&$this,'doDelayedRedirect'),100);
                                    }
                                    
                                 }
                                // simulate redirection with js
                                else
                                {
                                    if ($message!==false)
                                    {
                                        if ($this->_redirect_delay<=0)
                                        {
                                            echo sprintf("<script type='text/javascript'>document.location='%s';</script>", $url);
                                            exit();
                                        }
                                        // redirect after a delay
                                        else
                                        {
                                            echo sprintf("<script type='text/javascript'>setTimeout(function(){document.location='%s';},%d);</script>", $url, $this->_redirect_delay*1000);
                                        }
                                    }
                                }
                            }
                        }                        
                        // else just show the form again
                    }
                    else
                    {
                        $this->_myzebra_form->add_form_message('data-saved',$this->getLocalisedMessage('post_not_saved'));
                    }
                }
                else
                {
                    $this->_myzebra_form->add_form_message('preview-form',__('Preview Form submitted','wp-cred'));
                }
            }
            else if ($this->isSubmitted())
            {
                $this->_myzebra_form->add_form_message('data-saved',$this->getLocalisedMessage('post_not_saved'));
            }
            
            if ($message!==false)
                $output=$message;
            else
                $output=$this->render();
            return $output;
       }
       
       // hook to add html head meta tag for delayed redirect
       public function doDelayedRedirect()
       {
            echo sprintf("<meta http-equiv='refresh' content='%d;url=%s'>", $this->_redirect_delay, $this->_redirect_url);
       }
       
       // translate codes in notification fields of cred form (like %%POST_ID%% to post id etc..)
       private function translate_notification_field($field,$data)
       {
            return str_replace(array_keys($data),array_values($data),$field);
       }
       
       // render notification data for this form and send them through wp_mail
       private function sendNotifications($result)
       {
            // send notification
            if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
            {
                $parent_link=$this->cred_parent(array('get'=>'url'));
                $parent_title=$this->cred_parent(array('get'=>'title'));
                $link=get_permalink( $result );
                $title=get_the_title( $result );
                $data_all=array(
                '%%USER_LOGIN_NAME%%'=>self::$_current_user->login,
                '%%USER_DISPLAY_NAME%%'=>self::$_current_user->display_name,
                '%%POST_PARENT_TITLE%%'=>$parent_title,
                '%%POST_PARENT_LINK%%'=>$parent_link,
                '%%POST_ID%%'=>$result,
                '%%POST_TITLE%%'=>$title,
                '%%POST_LINK%%'=>$link,
                '%%FORM_NAME%%'=>$this->_form->form->post_title,
                '%%FORM_DATA%%'=>$this->_notification_data,
                '%%DATE_TIME%%'=>date('d/m/Y H:i:s'),
                '%%POST_ADMIN_LINK%%'=>admin_url('post.php').'?action=edit&post='.$result
                );
                $data_restricted=array(
                '%%USER_LOGIN_NAME%%'=>self::$_current_user->login,
                '%%USER_DISPLAY_NAME%%'=>self::$_current_user->display_name,
                '%%POST_PARENT_TITLE%%'=>$parent_title,
                '%%POST_ID%%'=>$result,
                '%%POST_TITLE%%'=>$title,
                '%%FORM_NAME%%'=>$this->_form->form->post_title,
                '%%DATE_TIME%%'=>date('d/m/Y H:i:s')
                );
                $mh=CRED_Loader::get('CLASS/Mail_Handler', false);
                
                // send notifications
                foreach ($this->_form->fields['notification']->notifications as $ii=>$_notification)
                {
                    $mh->reset();
                    $mh->setHTML(true);
                    // provide WPML translations for notification fields also
                    if ($_notification['mail_to_type']=='mail_field' && isset($this->_form_fields[$_notification['mail_to_field']]))
                    {
                        $mailcontrol=$this->_myzebra_form->controls[$this->_form_fields[$_notification['mail_to_field']][0]];
                        if (isset($mailcontrol->controls)) // repetitive control
                        {
                            // take 1st field
                            $_addr=$mailcontrol->controls[0]->attributes['value'];
                        }
                        else
                        {
                            $_addr=$mailcontrol->attributes['value'];;
                        }
                    }
                    elseif ($_notification['mail_to_type']=='wp_user')
                    {
                        $_addr=cred_translate('CRED Notification '.$ii.' Mail To', $_notification['mail_to_user'], 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID);
                    }
                    elseif ($_notification['mail_to_type']=='specific_mail')
                    {
                        $_addr=cred_translate('CRED Notification '.$ii.' Mail To', $_notification['mail_to_specific'], 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID);
                    }
                    else  continue;
                    
                    //cred_log('Notification to '.$_notification['mail_to'].' ='.$_addr);
                    
                    $_subj=$this->translate_notification_field(cred_translate('CRED Notification '.$ii.' Subject', $_notification['subject'], 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID),$data_restricted);
                    // allow shortcodes in body message
                    $_bod=do_shortcode($this->translate_notification_field(cred_translate('CRED Notification '.$ii.' Body', $_notification['body'], 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID),$data_all));
                    $mh->addAddress($_addr);
                    $mh->setSubject($_subj);
                    $mh->setBody($_bod);
                    
                    //cred_log($_bod);
                    
                    if ($mh->send())
                    {
                        $this->_myzebra_form->add_form_message('notification_'.$ii,$this->getLocalisedMessage('notification_was_sent'));
                    }
                    else
                    {
                        $this->_myzebra_form->add_form_error('notification_'.$ii,$this->getLocalisedMessage('notification_failed'));
                    }
                }
            }
       }
       
       // build form (parse CRED shortcodes and build Zebra_Form object)
       private function build()
       {
            if ($this->_myzebra_form->preview)
                $preview_content=$this->_content;
                
            // do WP shortcode here for final output, moved here to avoid replacing post_content
            $this->_content=do_shortcode($this->_content);
            // parse all shortcodes internally
            $this->_shortcode_parser->remove_all_shortcodes();
            $this->_shortcode_parser->add_shortcode( 'credform', array(&$this,'cred_form_shortcode') );
            $this->_content=$this->_shortcode_parser->do_shortcode($this->_content);
            $this->_shortcode_parser->remove_shortcode( 'credform', array(&$this,'cred_form_shortcode') );
            
            // add any custom attributes eg class
            if (
                isset($this->_myzebra_form->form_properties['attributes']) 
                && is_array($this->_myzebra_form->form_properties['attributes']) 
                && !empty($this->_myzebra_form->form_properties['attributes'])
                )
                $this->_myzebra_form->form_properties['attributes']=array_merge($this->_myzebra_form->form_properties['attributes'],$this->_form_attributes);
            else
                $this->_myzebra_form->form_properties['attributes']=$this->_form_attributes;
            
            // render any third-party shortcodes first (enables using shortcodes as values to cred shortcodes)
            $this->_form_content=do_shortcode($this->_form_content);
            // build shortcode
            $this->_shortcode_parser->add_shortcode( 'cred-field', array(&$this,'cred_field_shortcodes') );
            $this->_shortcode_parser->add_shortcode( 'cred-generic-field', array(&$this,'cred_generic_field_shortcodes') );
            $this->_shortcode_parser->add_shortcode( 'cred-show-group', array(&$this,'cred_conditional_shortcodes') );
            $this->_child_groups=array();
            $this->_form_content=$this->_shortcode_parser->do_recursive_shortcode('cred-show-group', $this->_form_content);
            $this->_child_groups=array();
            $this->_form_content=$this->_shortcode_parser->do_shortcode($this->_form_content);
            $this->_shortcode_parser->remove_shortcode( 'cred-show-group', array(&$this,'cred_conditional_shortcodes') );
            $this->_shortcode_parser->remove_shortcode( 'cred-generic-field', array(&$this,'cred_generic_field_shortcodes') );
            $this->_shortcode_parser->remove_shortcode( 'cred-field', array(&$this,'cred_field_shortcodes') );
            // add some auxilliary fields to form
            // add nonce hidden field
            $nonceobj=$this->_myzebra_form->add('hidden',self::NONCE,wp_create_nonce($this->_myzebra_form->form_properties['name']),array('style'=>'display:none;'));
            $this->_nonce_field=$nonceobj->attributes['id'];
            
            // add post_id hidden field
            if ($this->_post_id)
            {
                $post_id_obj=$this->_myzebra_form->add('hidden',self::PREFIX.'post_id',$this->_post_id,array('style'=>'display:none;'));
                $this->_post_id_field=$post_id_obj->attributes['id'];
            }
            // add to form
            //$this->_form_content.=$nonceobj->toHTML();
            if ($this->_myzebra_form->preview)
            {
                // add temporary content for form preview
                $obj=$this->_myzebra_form->add('textarea',self::PREFIX.'form_preview_content',$preview_content,array('style'=>'display:none;'));
                // add temporary content for form preview
                $this->_form_content.=$obj->toHTML();
                $obj=$this->_myzebra_form->add('hidden',self::PREFIX.'form_preview_post_type',$this->_post_type,array('style'=>'display:none;'));
                // add temporary content for form preview
                //$this->_form_content.=$obj->toHTML();
                $obj=$this->_myzebra_form->add('hidden',self::PREFIX.'form_preview_form_type',$this->_form_type,array('style'=>'display:none;'));
                // add temporary content for form preview
                //$this->_form_content.=$obj->toHTML();
                
                if ($this->_form->fields['form_settings']->has_media_button)
                    $this->_myzebra_form->add_form_error('preview_media',__('Media Upload will not work with form preview','wp-cred'));
                
                $this->_myzebra_form->add_form_message('preview_mode',__('Form Preview Mode','wp-cred'));
            }
            // add form id
            $obj=$this->_myzebra_form->add('hidden',self::PREFIX.'form_id',$this->_form_id,array('style'=>'display:none;'));
            $this->_form_id_field=$obj->attributes['id'];
            // add to form
            //$this->_form_content.=$obj->toHTML();
            // add form count
            $obj=$this->_myzebra_form->add('hidden',self::PREFIX.'form_count',$this->_form_count,array('style'=>'display:none;'));
            $this->_form_count_field=$obj->attributes['id'];
            // add to form
            //$this->_form_content.=$obj->toHTML();
            /*if ($this->_post_id)
            {
                // add post id
                $obj=$this->_myzebra_form->add('hidden',self::PREFIX.'post_id',$this->_post_id,array('style'=>'display:none;'));
                // add to form
                $this->_form_content.=$obj->toHTML();
            }*/
            
            // check conditional expressions for javascript
            $this->doConditionalExpressions();
       }
       
       // parse and check conditional expressions to be used in javascript
       private function doConditionalExpressions()
       {
            global $user_ID;
            
            $roles=self::getUserRolesByID($user_ID);
            
            // check expression is valid
            $formfields=array_keys($this->_form_fields);
            $conditional_js_data=array();
            $affected_fields=array();
            $kk=0;
            foreach ($this->_conditionals as $key=>$cond)
            {
                ++$kk;
                $this->_conditionals[$key]['valid']=true;
                $replace=array('original'=>array(),'original_name'=>array(),'field_reference'=>array(),'field_name'=>array(),'values_map'=>array(),/*'original_quote'=>array(),*/'replace'=>array());
                
                if (preg_match_all('/\$\(([a-z_][a-z_\-\d]*:?)\)/si',$cond['condition'],$matches))
                {
                    foreach ($matches[1] as $k=>$m)
                    {
                        if (!in_array($m,$formfields))
                        {
                            if (in_array('administrator',$roles) && $this->_myzebra_form->preview)
                                $this->_myzebra_form->add_form_error('condition'.$key.$k,sprintf(__('Variable {%1$s} in Expression {%2$s} does not refer to an existing form field','wp-cred'),htmlspecialchars($m),htmlspecialchars($cond['condition'])));
                            $this->_conditionals[$key]['valid']=false;
                        }
                        else if (
                            $this->_form_fields_qualia[$m]['type']=='file' ||
                            $this->_form_fields_qualia[$m]['type']=='image' ||
                            $this->_form_fields_qualia[$m]['type']=='recaptcha' ||
                            $this->_form_fields_qualia[$m]['type']=='skype' ||
                            $this->_form_fields_qualia[$m]['type']=='form_messages' ||
                            $this->_form_fields_qualia[$m]['repetitive'] 
                            )
                        {
                            if (in_array('administrator',$roles) && $this->_myzebra_form->preview)
                                $this->_myzebra_form->add_form_error('condition'.$key.$k,sprintf(__('Variable {%1$s} in Expression {%2$s} refers to a field that cannot be used in conditional expressions','wp-cred'),htmlspecialchars($m),htmlspecialchars($cond['condition'])));
                            $this->_conditionals[$key]['valid']=false;
                        }
                        else
                        {
                            if (!in_array($m,$replace['original_name']))
                            {
                                $name=$this->_form_fields_qualia[$m]['name'];
                                $replace['original'][]=$matches[0][$k];
                                $replace['original_name'][]=$m;
                                $replace['field_reference'][]=$this->_form_fields[$m][0]; // field id this var references
                                $replace['field_name'][]=$name; // field name this var references
                                //$replace['original_quote'][]='/'.preg_quote($m).'/';
                                $replace['replace'][]='$v'.$kk.$k;
                                if (isset($this->_field_values_map[$m]))
                                    $replace['values_map'][]=$this->_field_values_map[$m];
                                else
                                    $replace['values_map'][]=false;
                            }
                        }
                    }
                }
                if ($this->_conditionals[$key]['valid'])
                {
                    if (!empty($replace['replace']))
                        $this->_conditionals[$key]['replaced_condition']=str_replace($replace['original'],$replace['replace'],$this->_conditionals[$key]['condition']);
                    else
                    {
                        $this->_conditionals[$key]['replaced_condition']=$this->_conditionals[$key]['condition'];
                        if (in_array('administrator',$roles) && $this->_myzebra_form->preview)
                            $this->_myzebra_form->add_form_error('condition'.$key,sprintf(__('Expression {%1$s} has no variables that refer to form fields, the evaluated result is constant','wp-cred'),htmlspecialchars($cond['condition'])));
                    }
                    $this->_conditionals[$key]['var_field_map']=$replace;
                    
                    // format for js
                    $tmp=array(
                        'condition' => $this->_conditionals[$key]['replaced_condition'],
                        'group' => $this->_conditionals[$key]['container_id'],
                        'mode' => $this->_conditionals[$key]['mode'],
                        'affected_fields' => $replace['field_reference'],
                        'affected_fields_names' => $replace['field_name'],
                        'map' => array()
                    );
                    foreach ($replace['replace'] as $ii=>$var)
                        $tmp['map'][]=array('variable'=>$replace['replace'][$ii],'field'=>$replace['field_reference'][$ii],'field_name'=>$replace['field_name'][$ii],'values_map'=>$replace['values_map'][$ii]);
                    $conditional_js_data[]=$tmp;
                    
                    // group all affected fields in one place for easy reference
                    $affected_fields=array_merge($affected_fields,array_diff($replace['field_reference'],$affected_fields));
                }
                if (isset($this->_myzebra_form->controls[$key]) && $this->_myzebra_form->controls[$key]->isContainer())
                    $this->_myzebra_form->controls[$key]->setConditionData($this->_conditionals[$key]);
            }
            //print_r($this->_field_values_map);
            //print_r($conditional_js_data);
            
            $extra_parameters=array('parser_info'=>array('user'=>(array)self::$_current_user));
            $this->_myzebra_form->set_extra_parameters($extra_parameters);
            if (!empty($conditional_js_data))
            {
                $this->_myzebra_form->add_conditional_settings($conditional_js_data, $affected_fields);
                //$extra_parameters['js_data']=array('conditional_js'=>$conditional_js_data,'affected_fields'=>$affected_fields);
            }
       }
       
       // render form (return actual HTML code)
       private function render()
       {
            /*$_replace_content=false;
            if (array_key_exists('post_content',$this->_form_fields))
            {
                $this->_post_content=$this->_myzebra_form->controls[$this->_form_fields['post_content'][0]]->attributes['value'];
                $this->_myzebra_form->controls[$this->_form_fields['post_content'][0]]->attributes['value']=self::POST_CONTENT_TAG.'_'.$this->_myzebra_form->form_properties['name'].'%';
                $_replace_content=true;
            }*/
            $this->_shortcode_parser->remove_all_shortcodes();
            $this->_shortcode_parser->add_shortcode( 'render-cred-field', array(&$this,'render_cred_field_shortcodes') );
            list($this->_form_content, $this->_form_js)=$this->_myzebra_form->render(array(&$this,'render_callback'),true);
            $this->_shortcode_parser->remove_shortcode( 'render-cred-field', array(&$this,'render_cred_field_shortcodes') );
            $this->_content=str_replace(self::FORM_TAG.'_'.$this->_myzebra_form->form_properties['name'].'%',$this->_form_content, $this->_content);
            $this->_shortcode_parser->add_shortcode( 'cred-post-parent', array(&$this,'cred_parent') );
            $this->_content=$this->_shortcode_parser->do_shortcode($this->_content);
            $this->_shortcode_parser->remove_shortcode( 'cred-post-parent', array(&$this,'cred_parent') );
            /*if ($_replace_content) // render raw post content if exists
                $this->_content=str_replace(self::POST_CONTENT_TAG.'_'.$this->_myzebra_form->form_properties['name'].'%',stripslashes($this->_post_content),$this->_content);
            global $current_user;*/
            return /*rint_r($current_user,true).'<hr /> '.*/$this->_content;
       }
       
       // check if submitted
       private function isSubmitted()
       {
            return ($this->_myzebra_form->isSubmitted());
       }
       
       // validate form
       private function validate()
       {
            // reference to the form submission method
            global ${'_' . self::METHOD};

            $method = & ${'_' . self::METHOD};
            
            $result=false;
            if ($this->_myzebra_form->isSubmitted())
            {
                // verify nonce field
                if (!array_key_exists(self::NONCE, $method) || !wp_verify_nonce($method[self::NONCE], $this->_myzebra_form->form_properties['name']))
                {
                     $this->_myzebra_form->add_form_error('security',$this->getLocalisedMessage('invalid_form_submission'));
                     $result=false;
                     return $result;
                }
                // get values
                $this->_myzebra_form->get_submitted_values();
                $fields=$this->get_form_field_values();
                $thisform=array(
                    'id'=>$this->_form_id,
                    'post_type'=>$this->_post_type,
                    'form_type'=>$this->_form_type
                );
                $errors=array();
                list($fields,$errors)=apply_filters('cred_form_validate_'.$this->_form_id,array($fields,$errors),$thisform);
                list($fields,$errors)=apply_filters('cred_form_validate',array($fields,$errors),$thisform);
                if (!empty($errors))
                {
                    foreach ($errors as $fname=>$err)
                    {
                        if (array_key_exists($fname,$this->_form_fields))
                            $this->_myzebra_form->controls[$this->_form_fields[$fname][0]]->addError($err);
                    }
                }
                else
                {
                    $this->set_form_field_values($fields);
                }
                $result=$this->_myzebra_form->validate(true);
            }
            return $result;
       }
       
       // save form data (if form valid)
       private function saveData($id=null)
       {
            $thisform=array(
                'id'=>$this->_form_id,
                'post_type'=>$this->_post_type,
                'form_type'=>$this->_form_type
            );
            // do custom actions on post save
            do_action('cred_before_save_data_'.$this->_form_id,$thisform);
            do_action('cred_before_save_data',$thisform);
            
            // reference to the form submission method
            global ${'_' . self::METHOD}, $user_ID;

            $method = & ${'_' . self::METHOD};
            
            // cred form data
            $_cred_form_data=array();
            
            // main post fields
            $post=new stdClass;
            
            // if an ID is already generated previously
            if (
                ($id===null || $id===false || !isset($id) || empty($id)) &&
                (isset($method[self::PREFIX.'post_id']) && is_numeric($method[self::PREFIX.'post_id']))
                )
                $post->ID=intval($method[self::PREFIX.'post_id']);
            elseif (isset($id) && !empty($id) && is_numeric($id))
                $post->ID=intval($id);
            else
                $post->ID='';
            
            if ($this->_form->fields['form_settings']->form_type=='new')
                $post->post_author=$user_ID;
                
            // post title
            if (
                array_key_exists('post_title',$this->_form_fields) && 
                array_key_exists('post_title',$method) &&
                !$this->_myzebra_form->controls[$this->_form_fields['post_title'][0]]->isDiscarded()
                )
                {
                    $post->post_title=(array_key_exists('post_title',$method))?stripslashes($method['post_title']):'';
                    unset($method['post_title']);
                }
            
            // post content
            if (
                array_key_exists('post_content',$this->_form_fields) && 
                array_key_exists('post_content',$method) &&
                !$this->_myzebra_form->controls[$this->_form_fields['post_content'][0]]->isDiscarded()
                )
                {
                    //cred_log($method['post_content']);
                    $post->post_content=(array_key_exists('post_content',$method))?stripslashes($method['post_content']):'';
                    unset($method['post_content']);
                }
            
            // post excerpt
            if (
                array_key_exists('post_excerpt',$this->_form_fields) && 
                array_key_exists('post_excerpt',$method) &&
                !$this->_myzebra_form->controls[$this->_form_fields['post_excerpt'][0]]->isDiscarded()
                )
                {
                    $post->post_excerpt=(array_key_exists('post_excerpt',$method))?stripslashes($method['post_excerpt']):'';
                    unset($method['post_excerpt']);
                }
            
            // post parent
            if (
                array_key_exists('post_parent',$this->_form_fields) && 
                array_key_exists('post_parent',$method) &&
                !$this->_myzebra_form->controls[$this->_form_fields['post_parent'][0]]->isDiscarded() &&
                /*$this->_form->fields['form_settings']->post_type=='page' &&*/
                isset($this->_fields['parents']) && isset($this->_fields['parents']['post_parent']) &&
                intval($method['post_parent'])>=0
                )
                {
                    $post->post_parent=intval($method['post_parent']);
                    unset($method['post_parent']);
                }
            
            // post type
            $post->post_type=$this->_form->fields['form_settings']->post_type;
            
            // post status
            if (
                !isset($this->_form->fields['form_settings']->post_status) ||
                !in_array($this->_form->fields['form_settings']->post_status,array('draft','private','publish','original'))
                )
                $this->_form->fields['form_settings']->post_status='draft';
            
            if (
                isset($this->_form->fields['form_settings']->post_status) && 
                $this->_form->fields['form_settings']->post_status=='original' && 
                $this->_form_type!='edit'
                )
                $this->_form->fields['form_settings']->post_status='draft';
            
            if (
                $this->_form->fields['form_settings']->post_status!='original'
                )
                $post->post_status=(isset($this->_form->fields['form_settings']->post_status))?$this->_form->fields['form_settings']->post_status:'draft';
            
            
            // track form data for notification mail
            if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
            {
                if (isset($post->post_title))
                    $_cred_form_data[]='Post Title: '.$post->post_title;
                if (isset($post->post_content))
                    $_cred_form_data[]='Post Content: '.$post->post_content;
            }
            
            // custom fields
            $fields=array();
            $fieldsInfo=array();
            $files=array();
            foreach ($this->_fields['post_fields'] as $key=>$field)
            {
                $field_label=$field['name'];
                $done_data=false;
                
                // if this field was not rendered in this specific form, bypass it
                if (!array_key_exists($key,$this->_form_fields)) continue;
                
                // if this field was discarded due to some conditional logic, bypass it
                if ($this->_myzebra_form->controls[$this->_form_fields[$key][0]]->isDiscarded())  continue;
                
                $key11=$key;
                if (isset($field['plugin_type_prefix']))
                    $key=/*'wpcf-'*/$field['plugin_type_prefix'].$key;
                    
                $fieldsInfo[$key]=array('save_single'=>false);
                
                if ($field['type']=='checkboxes' && isset($field['data']['save_empty']) && $field['data']['save_empty']=='yes' && !array_key_exists($key,$method))
                {
                    $values=array();
                    foreach ($field['data']['options'] as $optionkey=>$optiondata)
                    {
                        $values[$optionkey]='0';
                    }

                    // let model serialize once, fix Types-CRED mapping issue with checkboxes
                    $fieldsInfo[$key]['save_single']=true;
                    $fields[$key]=$values;
                }
                elseif ($field['type']=='checkbox' && isset($field['data']['save_empty']) && $field['data']['save_empty']=='yes' && !array_key_exists($key,$method))
                {
                    $fields[$key]='0';
                }
                elseif (array_key_exists($key,$method))
                {
                    $values=$method[$key];
                    if ($field['type']=='file' || $field['type']=='image')
                    {
                        $files[$key]=$this->_myzebra_form->controls[$this->_form_fields[$key11][0]]->get_values();
                        $files[$key]['name_orig']=$key11;
                        $files[$key]['label']=$field['name'];
                        if ($this->_form_fields_qualia[$key11]['repetitive'])
                            $files[$key]['repetitive']=true;
                        else
                            $files[$key]['repetitive']=false;
                    }
                    
                    if ($field['type']=='textarea' || $field['type']=='wysiwyg')
                    {
                        // stripslashes for textarea, wysiwyg fields
                        if (is_array($values))
                            $values=array_map('stripslashes',$values);
                        else
                            $values=stripslashes($values);
                    }
                    if ($field['type']=='textfield' || $field['type']=='text' || $field['type']=='date')
                    {
                        // stripslashes for textarea, wysiwyg fields
                        if (is_array($values))
                            $values=array_map('stripslashes',$values);
                        else
                            $values=stripslashes($values);
                    }
                    
                    // track form data for notification mail
                    if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable && isset($field['plugin_type']) && $field['plugin_type']='types')
                    {
                        $tmp_data=null;
                        if ($field['type']=='checkbox')
                        {
                            if ($field['data']['display']=='db')
                                $tmp_data=$values;
                            else
                            {
                                $tmp_data=$field['data']['display_value_selected'];
                            }
                        }
                        elseif ($field['type']=='radio' || $field['type']=='select')
                        {
                            $tmp_data=$field['data']['options'][$values]['title'];
                        }
                        elseif ($field['type']=='checkboxes')
                        {
                            $tmp_data=array();
                            foreach ($values as $tmp_val)
                                $tmp_data[]=$field['data']['options'][$tmp_val]['title'];
                            $tmp_data=implode(', ',$tmp_data);
                            unset($tmp_val);
                        }
                        if ($tmp_data!==null)
                        {
                            $_cred_form_data[]=$field_label.': '.$tmp_data;
                            $done_data=true;
                        }
                    }
                    if ($field['type']=='checkboxes' || $field['type']=='multiselect')
                    {
                        $result=array();
                        //foreach ($values as $val)
                        foreach ($field['data']['options'] as $optionkey=>$optiondata)
                        {
                            if (isset($field['data']['save_empty']) && $field['data']['save_empty']=='yes' && !in_array($optionkey,$values))
                                $result[$optionkey]='0';
                            elseif (in_array($optionkey,$values))
                                $result[$optionkey]=$optiondata['set_value'];
                        }

                        $values=/*serialize(*/$result/*)*/;
                        $fieldsInfo[$key]['save_single']=true;
                    }
                    elseif ($field['type']=='radio' || $field['type']=='select')
                    {
                        $values=$field['data']['options'][$values]['value'];
                    }
                    elseif ($field['type']=='date')
                    {
                        $date_format='';
                        if (isset($field['data']) && isset($field['data']['validate']))
                            $date_format=$field['data']['validate']['date']['format'];
                        if (!in_array($date_format,$this->supported_date_formats))
                            $date_format='F j, Y';
                        if (!is_array($values))  $tmp=array($values);
                        else    $tmp=$values;
                        // track form data for notification mail
                        if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                        {
                            $tmp_data='';
                            $tmp_data.=$field_label.': ';
                        }
                        
                        MyZebra_DateParser::setDateLocaleStrings(self::$localized_strings['days'], self::$localized_strings['months']);
                        
                        foreach ($tmp as $ii=>$val)
                        {
                            // track form data for notification mail
                            if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                            {
                                $tmp_data.='['.$val.']  ';
                            }
                            /*if ($date_format == 'd/m/Y') {
                                // strtotime requires a dash or dot separator to determine dd/mm/yyyy format
                                $val = str_replace('/', '-', $val);
                            }
                            $val=strtotime(strval($val));*/
                            $val = MyZebra_DateParser::parseDate($val, $date_format);
                            if ($val !== false)  // succesfull
                                $val=$val->getNormalizedTimestamp();
                            else continue;    
                            
                            $tmp[$ii]=$val;
                        }
                        
                        if (!is_array($values))  $values=$tmp[0];
                        else $values=$tmp;
                        // track form data for notification mail
                        if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                        {
                            $_cred_form_data[]=$tmp_data;
                            $done_data=true;
                        }
                    }
                    elseif ($field['type']=='skype')
                    {
                        if (array_key_exists('skypename',$values) && array_key_exists('style',$values))
                        {
                            $tmp_data=array();
                            $new_values=array();
                            $values['skypename']=(array)$values['skypename'];
                            $values['style']=(array)$values['style'];
                            foreach ($values['skypename'] as $ii=>$val)
                            {
                                $new_values[]=array(
                                    'skypename'=>$values['skypename'][$ii],
                                    'style'=>$values['style'][$ii]
                                );
                                if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                                {
                                    $tmp_data[]='{'.$values['skypename'][$ii].','.$values['style'][$ii].'}';
                                }
                                
                            }
                            $values=$new_values;
                            unset($new_values);
                            if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                            {
                                $_cred_form_data[]=$field_label.': '.implode(' ',$tmp_data);
                                $done_data=true;
                            }
                        }
                    }
                    // track form data for notification mail
                    if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                    {
                        // dont track file/image data now but after we upload them..
                        if (!$done_data && $field['type']!='file' && $field['type']!='image')
                        {
                            if (is_array($values))
                            {
                                $_cred_form_data[]=$field_label.': '.implode(', ',$values);
                            }
                            else
                            {
                                $_cred_form_data[]=$field_label.': '.$values;
                            }
                        }
                    }
                    $fields[$key]=$values;
                }
            }
            // custom parents (Types feature)
            foreach ($this->_fields['parents'] as $key=>$field)
            {
                $field_label=$field['name'];
                
                // overwrite parent setting by url, even though no fields might be set
                if (!array_key_exists($key,$this->_form_fields) && array_key_exists('parent_'.$field['data']['post_type'].'_id',$_GET) && is_numeric($_GET['parent_'.$field['data']['post_type'].'_id']))
                {
                    $fieldsInfo[$key]=array('save_single'=>false);
                    $fields[$key]=intval($_GET['parent_'.$field['data']['post_type'].'_id']);
                    continue;
                }
                // if this field was not rendered in this specific form, bypass it
                if (!array_key_exists($key,$this->_form_fields)) continue;
                
                // if this field was discarded due to some conditional logic, bypass it
                if ($this->_myzebra_form->controls[$this->_form_fields[$key][0]]->isDiscarded())  continue;
                
                    
                
                if (array_key_exists($key,$method) && intval($method[$key])>=0)
                {
                    $fieldsInfo[$key]=array('save_single'=>false);
                    $fields[$key]=intval($method[$key]);
                }
            }
            // taxonomies
            $taxonomies=array('flat'=>array(),'hierarchical'=>array());
            foreach ($this->_fields['taxonomies'] as $key=>$field)
            {
                // if this field was not rendered in this specific form, bypass it
                if (!array_key_exists($key,$this->_form_fields)) continue;
                
                // if this field was discarded due to some conditional logic, bypass it
                if ($this->_myzebra_form->controls[$this->_form_fields[$key][0]]->isDiscarded())  continue;
                
                if (array_key_exists($key,$method) || ($field['hierarchical'] && isset($method[$key.'_hierarchy'])) )
                {
                    if ($field['hierarchical'] /*&& is_array($method[$key])*/)
                    {
                        $values=isset($method[$key])?$method[$key]:array();
                        if (isset($method[$key.'_hierarchy']))
                        {
                            $add_new=array();
                            preg_match_all("/\{([^\{\}]+?),([^\{\}]+?)\}/",$method[$key.'_hierarchy'],$tmp_a_n);
                            for ($ii=0; $ii<count($tmp_a_n[1]); $ii++)
                            {
                                $add_new[]=array(
                                    'parent'=>$tmp_a_n[1][$ii],
                                    'term'=>$tmp_a_n[2][$ii]
                                );
                            }
                            unset($tmp_a_n);
                        }
                        else
                            $add_new=array();
                        
                        //cred_log(print_r($add_new,true));
                        
                        $taxonomies['hierarchical'][]=array(
                            'name'=>$key,
                            'terms'=>$values, 
                            'add_new'=>$add_new
                        );
                        // track form data for notification mail
                        if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                        {
                            $tmp_data=array();
                            foreach ($field['all'] as $tmp_tax)
                            {
                                if (in_array($tmp_tax['term_taxonomy_id'],$values))
                                    $tmp_data[]=$tmp_tax['name'];
                            }
                            // add also new terms created
                            foreach ($values as $val)
                            {
                                if (is_string($val) && !is_numeric($val))
                                    $tmp_data[]=$val;
                            }
                            $_cred_form_data[]=$field['label'].': '.implode(', ',$tmp_data);
                            unset($tmp_data);
                        }
                    }
                    elseif (!$field['hierarchical'])
                    {
                        $values=$method[$key];
                        // find which to add and which to remove
                        //$sanit=create_function('$a', 'return preg_replace("/[\*\(\)\{\}\[\]\+\,\.]|\s+/g","",$a);');
                        preg_match("/^add\{(.*?)\}remove\{(.*?)\}$/i",$values,$matches);
                        // allow white space in tax terms
                        $tax_add=(!empty($matches[1]))?preg_replace("/[\*\(\)\{\}\[\]\+\,\.]|[\f\n\r\t\v\x{00A0}\x{2028}\x{2029}]+/u","",explode(',',$matches[1])):array();
                        $tax_remove=(!empty($matches[2]))?preg_replace("/[\*\(\)\{\}\[\]\+\,\.]|[\f\n\r\t\v\x{00A0}\x{2028}\x{2029}]+/u","",explode(',',$matches[2])):array();
                        $taxonomies['flat'][]=array('name'=>$key,'add'=>$tax_add,'remove'=>$tax_remove);
                        // track form data for notification mail
                        if (isset($this->_form->fields['notification']->enable))
                        {
                            $_cred_form_data[]=$field['label'].': '.'added=>('.implode(', ',$tax_add).') removed=>('.implode(', ',$tax_remove).')';
                        }
                    }
                }
            }
            
            // upload data
            $all_ok=true;
            
            require_once(ABSPATH.'/wp-admin/includes/file.php');
            // set featured image only if uploaded
            $fkey='_featured_image';
            $extra_files=array();
            if (array_key_exists($fkey,$this->_form_fields) && array_key_exists($fkey,$_FILES) && isset($_FILES[$fkey]['name']) && $_FILES[$fkey]['name']!='')
            {
                
                $upload = wp_handle_upload($_FILES[$fkey], array('test_form' => false,'test_upload'=>false));
                if(!isset($upload['error']) && isset($upload['file'])) 
                {
                    $extra_files[$fkey]['wp_upload']=$upload;
                    $tmp_data=__('Featured Image').': '.$upload['url'];
                    $this->_myzebra_form->controls[$this->_form_fields[$fkey][0]]->set_values(array('value'=>''));
                    //$this->_myzebra_form->controls[$this->_form_fields[$fkey][0]]->set_values(array('value'=>$upload['url']));
                }
                else
                {
                    $all_ok=false;
                    $tmp_data=__('Featured Image').': '.$_FILES[$fkey]['name'].' ('.$this->getLocalisedMessage('upload_failed').')';;
                    $fields[$fkey]='';
                    $extra_files[$fkey]['upload_fail']=true;
                    $this->_myzebra_form->controls[$this->_form_fields[$fkey][0]]->set_values(array('value'=>''));
                    $this->_myzebra_form->controls[$this->_form_fields[$fkey][0]]->addError($upload['error']);
                }
            }
            foreach ($files as $fkey=>$fdata)
            {
                if ($fdata['repetitive'])
                {
                    $tmp_data=$files[$fkey]['label'].': ';
                    $tmp_first=true;

                    foreach ($fdata as $ii=>$fdata2)
                    {
                        if (!isset($fdata2['file_data'][$fkey]) || !is_array($fdata2['file_data'][$fkey])) continue;
                        $file_data=$fdata2['file_data'][$fkey];
                        $upload = wp_handle_upload($file_data, array('test_form' => false,'test_upload'=>false));
                        if(!isset($upload['error']) && isset($upload['file'])) 
                        {
                            $files[$fkey][$ii]['wp_upload']=$upload;
                            $fields[$fkey][$ii]=$upload['url'];
                            $tmp_data.=(($tmp_first)?' ':', ').$upload['url'];
                            $tmp_first=false;
                            $this->_myzebra_form->controls[$this->_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array($ii=>array('value'=>$upload['url'])));
                        }
                        else
                        {
                            $all_ok=false;
                            $files[$fkey]['upload_fail']=true;
                            $tmp_data.=(($tmp_first)?' ':', ').$fields[$fkey][$ii].' ('.$this->getLocalisedMessage('upload_failed').')';
                            $tmp_first=false;
                            $fields[$fkey][$ii]='';
                            $files[$fkey][$ii]['upload_fail']=true;
                            $this->_myzebra_form->controls[$this->_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array($ii=>array('value'=>'')));
                            $this->_myzebra_form->controls[$this->_form_fields[$files[$fkey]['name_orig']][0]]->addError(array($ii=>$upload['error']));
                        }
                    }
                }
                else
                {
                    if (!isset($fdata['file_data'][$fkey]) || !is_array($fdata['file_data'][$fkey])) continue;
                    
                    $file_data=$fdata['file_data'][$fkey];
                    $upload = wp_handle_upload($file_data, array('test_form' => false,'test_upload'=>false));
                    if(!isset($upload['error']) && isset($upload['file'])) 
                    {
                        $files[$fkey]['wp_upload']=$upload;
                        $fields[$fkey]=$upload['url'];
                        $tmp_data=$files[$fkey]['label'].': '.$upload['url'];
                        $this->_myzebra_form->controls[$this->_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>$upload['url']));
                    }
                    else
                    {
                        $all_ok=false;
                        $tmp_data=$files[$fkey]['label'].': '.$fields[$fkey].' ('.$this->getLocalisedMessage('upload_failed').')';;
                        $fields[$fkey]='';
                        $files[$fkey]['upload_fail']=true;
                        $this->_myzebra_form->controls[$this->_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>''));
                        $this->_myzebra_form->controls[$this->_form_fields[$files[$fkey]['name_orig']][0]]->addError($upload['error']);
                    }
                }
                // track file/image upload data for notifications
                if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                    $_cred_form_data[]=$tmp_data;
            }
            
            // save them 
            if ($all_ok)
            {
                $model=CRED_Loader::get('MODEL/Forms');
                if (empty($post->ID))
                    $result=$model->addPost($post,array('fields'=>$fields,'info'=>$fieldsInfo),$taxonomies);
                else
                    $result=$model->updatePost($post,array('fields'=>$fields,'info'=>$fieldsInfo),$taxonomies,$fieldsInfo);
            }
            else $result=false;
            
            if (is_int($result) && $all_ok)
            {
                foreach ($files as $fkey=>$fdata)
                {
                    if ($files[$fkey]['repetitive'])
                    {
                        foreach ($fdata as $ii=>$fdata2)
                        {
                            if (!isset($fdata2['file_data'][$fkey]) || !is_array($fdata2['file_data'][$fkey])) continue;
                            
                            if (!isset($files[$fkey][$ii]['upload_fail']) || !$files[$fkey][$ii]['upload_fail'])
                            {
                                $filetype   = wp_check_filetype(basename($files[$fkey][$ii]['wp_upload']['file']), null);
                                $title      = $files[$fkey][$ii]['file_data'][$fkey]['name'];
                                $ext        = strrchr($title, '.');
                                $title      = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                                $attachment = array(
                                    'post_mime_type'    => $filetype['type'],
                                    'post_title'        => addslashes($title),
                                    'post_content'      => '',
                                    'post_status'       => 'inherit',
                                    'post_parent'       => $result,
                                    'post_type' => 'attachment',
                                    'guid' => $files[$fkey][$ii]['wp_upload']['url']                                
                                );            
                                $attach_id  = wp_insert_attachment($attachment, $files[$fkey][$ii]['wp_upload']['file']);
                                // you must first include the image.php file
                                // for the function wp_generate_attachment_metadata() to work
                                require_once(ABSPATH . 'wp-admin/includes/image.php');
                                $attach_data = wp_generate_attachment_metadata( $attach_id, $files[$fkey][$ii]['wp_upload']['file'] );
                                wp_update_attachment_metadata( $attach_id, $attach_data );                       
                            }
                        }
                    }
                    else
                    {
                        if (!isset($fdata['file_data'][$fkey]) || !is_array($fdata['file_data'][$fkey])) continue;
                        
                        if (!isset($files[$fkey]['upload_fail']) || !$files[$fkey]['upload_fail'])
                        {
                            $filetype   = wp_check_filetype(basename($files[$fkey]['wp_upload']['file']), null);
                            $title      = $files[$fkey]['file_data'][$fkey]['name'];
                            $ext        = strrchr($title, '.');
                            $title      = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                            $attachment = array(
                                    'post_mime_type'    => $filetype['type'],
                                    'post_title'        => addslashes($title),
                                    'post_content'      => '',
                                    'post_status'       => 'inherit',
                                    'post_parent'       => $result,
                                    'post_type' => 'attachment',
                                    'guid' => $files[$fkey]['wp_upload']['url']                                
                            );            
                            $attach_id  = wp_insert_attachment($attachment, $files[$fkey]['wp_upload']['file']);
                            // you must first include the image.php file
                            // for the function wp_generate_attachment_metadata() to work
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $attach_data = wp_generate_attachment_metadata( $attach_id, $files[$fkey]['wp_upload']['file'] );
                            wp_update_attachment_metadata( $attach_id, $attach_data ); 
                        }
                    }
                }
                foreach ($extra_files as $fkey=>$fdata)
                {
                    if (!isset($extra_files[$fkey]['upload_fail']) || !$extra_files[$fkey]['upload_fail'])
                    {
                        $filetype   = wp_check_filetype(basename($extra_files[$fkey]['wp_upload']['file']), null);
                        $title      = $_FILES[$fkey]['name'];
                        $ext        = strrchr($title, '.');
                        $title      = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                        $attachment = array(
                                'post_mime_type'    => $filetype['type'],
                                'post_title'        => addslashes($title),
                                'post_content'      => '',
                                'post_status'       => 'inherit',
                                'post_parent'       => $result,
                                'post_type' => 'attachment',
                                'guid' => $extra_files[$fkey]['wp_upload']['url']                                
                        );            
                        $attach_id  = wp_insert_attachment($attachment, $extra_files[$fkey]['wp_upload']['file']);
                        // you must first include the image.php file
                        // for the function wp_generate_attachment_metadata() to work
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $extra_files[$fkey]['wp_upload']['file'] );
                        wp_update_attachment_metadata( $attach_id, $attach_data ); 
                        
                        if ($fkey=='_featured_image')
                        {
                            // set current thumbnail
                            update_post_meta( $result, '_thumbnail_id', $attach_id );
                            // get current thumbnail
                            //if (isset($this->_form_fields['_featured_image']) && isset($this->_myzebra_form->controls[$this->_form_fields['_featured_image'][0]]))
                                $this->_myzebra_form->controls[$this->_form_fields['_featured_image'][0]]->set_attributes(array('display_featured_html'=>get_the_post_thumbnail( $result, 'thumbnail' /*, $attr*/ )));
                        }
                    }
                }
                
                // track form data for notification mail
                if (isset($this->_form->fields['notification']->enable) && $this->_form->fields['notification']->enable)
                {
                    $this->_notification_data='<hr />'.implode('<br /><hr /><br />',$_cred_form_data).'<hr />';
                }
                
                $thisform=array(
                    'id'=>$this->_form_id,
                    'post_type'=>$this->_post_type,
                    'form_type'=>$this->_form_type
                );
                // do custom actions on post save
                do_action('cred_save_data_'.$this->_form_id,$result,$thisform);
                do_action('cred_save_data',$result,$thisform);
            }
            // return saved post_id
            return $result;
       }
       
       // get all form field values to be used in validation hooks
       private function get_form_field_values()
       {
            $fields=array();
            foreach ($this->_form_fields as $name=>$id)
            {
                $fields[$name]=array(
                    'value'=>$this->convert_r_c_s_from_types($name,$this->_myzebra_form->controls[$id[0]]->get_values()),
                    'name'=>$name,
                    'type'=>$this->_form_fields_qualia[$name]['type'],
                    'repetitive'=>$this->_form_fields_qualia[$name]['repetitive']);
            }
                   //print_r($fields);
            return $fields;
       }
       
       // set form fields values to new values (after validation)
       private function set_form_field_values($fields)
       {
                    //print_r($fields);
            foreach ($fields as $name=>$data)
            {
                if (isset($this->_form_fields[$name]))
                {
                    $this->_myzebra_form->controls[$this->_form_fields[$name][0]]->set_values($this->convert_r_c_s_to_types($name,$data['value']));
                }
            }
       }
       
       // checkboxes,radios and select must be converted to wp-types format
       private function convert_r_c_s_to_types($field,$vals)
       {
            if (isset($this->_fields['post_fields'][$field]))
            {
                // types field
                $field=$this->_fields['post_fields'][$field];
                //if (!isset($field['plugin_type']) || 'types'!=$field['plugin_type'])
                  //  return $vals;
                switch ($field['type'])
                {
                    case 'select':
                            foreach ($field['data']['options'] as $key=>$option)
                            {
                                if ($vals==$option['value'])
                                {
                                    return $key;
                                }
                            }
                            return '';
                            break;
                    case 'radio':
                            foreach ($field['data']['options'] as $key=>$option)
                            {
                                if ($vals==$option['value'])
                                {
                                    return $key;
                                }
                            }
                            return '';
                            break;
                    case 'checkboxes':
                        $vvals='';
                        $avals=array();
                        foreach ($field['data']['options'] as $key=>$option)
                        {
                           if (is_array($vals))
                           {
                                if (in_array($option['set_value'],$vals))
                                    $avals[]=$key;
                           }
                           else
                           {
                                if ($option['value']==$vals)
                                    $vvals=$key;
                           }
                       }
                       if (is_array($vals)) return $avals;
                       else return $vvals;
                       break;
                    default:
                        return $vals;
                        break;
                }
            }
            else
            {
                // generic
                return $vals;
            }
       }
       
       // checkboxes, radios and select must be transformed from wp-types format
       private function convert_r_c_s_from_types($field,$vals)
       {
            if (isset($this->_fields['post_fields'][$field]))
            {
                // types field
                $field=$this->_fields['post_fields'][$field];
                //if (!isset($field['plugin_type']) || 'types'!=$field['plugin_type'])
                  //  return $vals;

                if ($field['type']=='radio')
                {
                    return isset($field['data']['options'][$vals])?$field['data']['options'][$vals]['value']:'';
                }
                elseif ($field['type']=='select')
                {
                    return isset($field['data']['options'][$vals])?$field['data']['options'][$vals]['value']:'';
                }
                elseif ($field['type']=='checkboxes')
                {
                    $tmp_data=array();
                    foreach ($vals as $tmp_val)
                        if (isset($field['data']['options'][$tmp_val]))
                            $tmp_data[]=$field['data']['options'][$tmp_val]['set_value'];
                    return $tmp_data;
                }
                else
                {
                    return $vals;
                }
            }
            else
            {
                // generic
                return $vals;
            }
       }
       
       // parse form shortcode [credform]
       public function cred_form_shortcode($atts, $content=null)
       {
            extract( shortcode_atts( array(
                'class'=>''
            ), $atts ) );
            
            if (!empty($class))
            $this->_form_attributes['class']=esc_attr($class);
            $this->_form_content=($content===null)?'':$content;
            return self::FORM_TAG.'_'.$this->_myzebra_form->form_properties['name'].'%';
       }
       
       
/**
 * CRED-Shortcode: cred-show-group
 *
 * Description: Show/Hide a group of fields based on conditional logic and values of form fields
 *
 * Parameters:
 * 'if' => Conditional Expression
 * 'mode' => Effect for show/hide group, values are: "fade-slide", "fade", "slide", "none"
 *  
 *   
 * Example usage:
 * 
 *    [cred-show-group if="$(date) gt TODAY()" mode="fade-slide"]
 *       //rest of content to be hidden or shown
 *      // inside the shortcode body..
 *    [/cred-show-group]
 * 
 **/
       // parse conditional shortcodes (nested allowed) [cred-show-group]
       public function cred_conditional_shortcodes($atts,$content=null)
       {
            static $condition_id=0;
            
            shortcode_atts( array(
                'if' => '',
                'mode'=> 'fade-slide'
            ), $atts ); //);
            
            if (empty($atts['if']) || !isset($content) || empty($content))
                return ''; // ignore
            else
            {
                // render conditional group
                ++$condition_id;
                $group=$this->_myzebra_form->add_conditional_group($this->_form_id.'_condition_'.$condition_id);
                
                // add child groups from prev level
                if ($this->_shortcode_parser->depth>0 && isset($this->_shortcode_parser->child_groups[$this->_shortcode_parser->depth-1]))
                {
                    foreach ($this->_child_groups[$this->_shortcode_parser->depth-1] as $child_group)
                    {
                        $group->addControl($child_group);
                    }
                }
                // add this group to child groups for next level
                if (!isset($this->_child_groups[$this->_shortcode_parser->depth]))
                    $this->_child_groups[$this->_shortcode_parser->depth]=array();
                $this->_child_groups[$this->_shortcode_parser->depth][]=$group;
                
                if ($this->_current_group!==null)
                {
                    $this->_current_group->addControl($group);
                }
                $prev_group=$this->_current_group;
                $this->_current_group=$group;
                
                $content=$this->_shortcode_parser->do_shortcode($content);
                
                $this->_current_group=$prev_group;
                // process this later, before render
                $condition=array(
                    'id'=>$group->attributes['id'],
                    'container_id'=>$group->attributes['id'],
                    'condition' => $atts['if'],
                    'replaced_condition'=>'',
                    'mode' => isset($atts['mode'])?$atts['mode']:'fade-slide',
                    'valid'=>false,
                    'var_field_map'=>array()
                    );
                $this->_conditionals[$group->attributes['id']]=$condition;
                return $group->renderBegin().$content.$group->renderEnd();
            }
       }
       
/**
 * CRED-Shortcode: cred-generic-field
 *
 * Description: Render a form generic field (general fields not associated with types plugin)
 *
 * Parameters:
 * 'field' => Field name (name like used in html forms)
 * 'type' => Type of input field (eg checkbox, email, select, radio, checkboxes, date, file, image etc..)
 * 'class'=> [optional] Css class to apply to the element
 *  
 *  Inside shortcode body the necessary options and default values are defined as JSON string (autogenerated by GUI)
 *   
 * Example usage:
 * 
 *    [cred-generic-field field="gmail" type="email" class=""]
 *    {
 *    "required":0,
 *    "validate_format":0,
 *    "default":""
 *    }
 *    [/cred-generic-field]
 * 
 **/
       // parse generic input field shortcodes [cred-generic-field]
       public function cred_generic_field_shortcodes($atts,$content=null)
       {
            shortcode_atts( array(
                'field' => '',
                'type' => '',
                'class'=>''
            ), $atts );
            if (empty($atts['field']) || empty($atts['type']) || $content==null)
                return ''; // ignore
            
            $field_data=json_decode($content, true);
            
            // only for php >= 5.3.0
            if (function_exists('json_last_error') && json_last_error() != JSON_ERROR_NONE)
                return ''; //ignore not valid json
			
            $field= array ( 
                'id' => $atts['field'], 
                'cred_generic'=>true, 
                'slug' => $atts['field'], 
                'type' => $atts['type'], 
                'name' => $atts['field'], 
                'data' => array ( 
                        'repetitive' => 0, 
                        'validate' => array ( 
                            'required' => array ( 
                                'active' => $field_data['required'], 
                                'value' => $field_data['required'], 
                                'message' => $this->getLocalisedMessage('field_required') 
                            )
                        ), 
                        'validate_format'=>$field_data['validate_format'],
                        'persist'=>isset($field_data['persist'])?$field_data['persist']:0
                    ) 
            );
            $default=$field_data['default'];
            switch($atts['type'])
            {
                case 'checkbox':
                    $field['data']['set_value']=$field_data['default'];
                    if ($field_data['checked']!=1)
                        $default=null;
                    break;
                case 'checkboxes':
                    $field['data']['options']=array();
                    foreach ($field_data['options'] as $ii=>$option)
                    {
                        $option_id=$option['value'];
                        //$option_id=$atts['field'].'_option_'.$ii;
                        $field['data']['options'][$option_id]=array(
                            'title' => $option['label'],
                            'set_value' => $option['value']
                        );
                        if (in_array($option['value'],$field_data['default']))
                        {
                            $field['data']['options'][$option_id]['checked']=true;
                        }
                    }
                    $default=null;
                    break;
                case 'date':
                    $field['data']['validate']['date']=array(
                        'active' => $field_data['validate_format'],
                        'format' => 'mdy',
                        'message' => $this->getLocalisedMessage('enter_valid_date')
                    );
                    $default=null;
                    break;
                case 'hidden':
                    $field['data']['validate']['hidden']=array(
                        'active' => $field_data['validate_format'],
                        'message' => $this->getLocalisedMessage('values_do_not_match')
                    );
                    break;
                case 'radio':
                case 'select':
                    $field['data']['options']=array();
                    $default_option='no-default';
                    foreach ($field_data['options'] as $ii=>$option)
                    {
                        $option_id=$option['value'];
                        //$option_id=$atts['field'].'_option_'.$ii;
                        $field['data']['options'][$option_id]=array(
                            'title' => $option['label'],
                            'value' => $option['value'],
                            'display_value' => $option['value']
                        );
                        if (!empty($field_data['default']) && $field_data['default'][0]==$option['value'])
                            $default_option=$option_id;
                    }
                    $field['data']['options']['default'] = $default_option;
                    $default=null;
                    break;
                case 'multiselect':
                    $field['data']['options']=array();
                    $default_option=array();
                    foreach ($field_data['options'] as $ii=>$option)
                    {
                        $option_id=$option['value'];
                        //$option_id=$atts['field'].'_option_'.$ii;
                        $field['data']['options'][$option_id]=array(
                            'title' => $option['label'],
                            'value' => $option['value'],
                            'display_value' => $option['value']
                        );
                        if (!empty($field_data['default']) && in_array($option['value'],$field_data['default']))
                            $default_option[]=$option_id;
                    }
                    $field['data']['options']['default'] = $default_option;
                    $field['data']['is_multiselect'] = 1;
                    $default=null;
                    break;
                case 'email':
                    $field['data']['validate']['email']=array(
                        'active' => $field_data['validate_format'],
                        'message' => $this->getLocalisedMessage('enter_valid_email')
                    );
                    break;
                case 'numeric':
                    $field['data']['validate']['number']=array(
                        'active' => $field_data['validate_format'],
                        'message' => $this->getLocalisedMessage('enter_valid_number')
                    );
                    break;
                case 'url':
                    $field['data']['validate']['url']=array(
                        'active' => $field_data['validate_format'],
                        'message' => $this->getLocalisedMessage('enter_valid_url')
                    );
                    break;
                default:
                    $default=$field_data['default'];
                    break;
            }
            
            $name=$field['slug'];
            if ($atts['type']=='image' || $atts['type']=='file')
            {
                if (isset($field_data['max_width']) && is_numeric($field_data['max_width']))
                    $max_width=intval($field_data['max_width']);
                else
                    $max_width=null;
                if (isset($field_data['max_height']) && is_numeric($field_data['max_height']))
                    $max_height=intval($field_data['max_height']);
                else
                    $max_height=null;
                    
                $ids=$this->translate_field($name,$field,array(
                                                            'preset_value'=>$default,
                                                            'is_tax'=>false,
                                                            'max_width'=>$max_width,
                                                            'max_height'=>$max_height));
            }
            else
                $ids=$this->translate_field($name,$field,array('preset_value'=>$default));
            
            if ($field['data']['persist'])
            {
                // this field is going to be saved as custom field to db
                $this->_fields['post_fields'][$name]=$field;
            }
            // check which fields are actually used in form
            $this->_form_fields[$name]=$ids;
            $this->_form_fields_qualia[$name]=array(
                'type'=>$field['type'],
                'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                'name'=>$name
            );
            $this->_generic_fields[$name]=$ids;
            if (!empty($atts['class']))
            {
                $atts['class']=esc_attr($atts['class']);
                foreach ($ids as $id)
                    $this->_myzebra_form->controls[$id]->set_attributes(array('class'=>$atts['class']),false);
            }
            $out='';
            foreach ($ids as $id)
                $out.= "[render-cred-field field='{$id}']";
            return $out;
        }
        
/**
 * CRED-Shortcode: cred-field
 *
 * Description: Render a form field (using fields defined in wp-types plugin and / or Taxonomies)
 *
 * Parameters:
 * 'field' => Field slug name
 * 'post' => [optional] Post Type where this field is defined 
 * 'value'=> [optional] Preset value (translated automatically if WPML translation exists)
 * 'taxonomy'=> [optional] Used by taxonomy auxilliary fields (eg. "show_popular") to signify to which taxonomy this field belongs (used with "type" option)
 * 'type'=> [optional] Used by taxonomy auxilliary fields (like show_popular) to signify which type of functionality it provides (eg. "show_popular") (used with "taxonomy" option)
 * 'display'=> [optional] Used by fields for Hierarchical Taxonomies (like Categories) to signify the mode of display (ie. "select" or "checkbox")
 * 'single_select'=> [optional] Used by fields for Hierarchical Taxonomies (like Categories) to signify that select field does not support multi-select mode
 * 'max_width'=>[optional] Max Width for image fields
 * 'max_height'=>[optional] Max Height for image fields
 * 'max_results'=>[optional] Max results in parent select field
 * 'order'=>[optional] Order for parent select field (title or date)
 * 'ordering'=>[optional] Ordering for parent select field (asc, desc)
 * 'required'=>[optional] Whether parent field is required, default 'false'
 * 'no_parent_text'=>[optional] Text for no parent selection in parent field
 * 'select_text'=>[optional] Text for required parent selection
 * 'validate_text'=>[optional] Text for error message when parebt not selected
 * 'placeholder'=>[optional] Text to be used as placeholder (HTML5) for text fields, default none
 * 'readonly'=>[optional] Whether this field is readonly (cannot be edited, applies to text fields), default 'false'
 *
 * Example usage:
 *
 *  Render the wp-types field "Mobile" defined for post type Agent
 * [cred-field field="mobile" post="agent" value="555-1234"]
 *
 **/
       // parse field shortcodes [cred-field]
       public function cred_field_shortcodes($atts)
       {
            extract( shortcode_atts( array(
                'post' => '',
                'field' => '',
                'value' => null,
                'placeholder'=>null,
                'escape'=>'false',
                'readonly'=>'false',
                'taxonomy'=>null,
                'single_select'=>null,
                'type'  => null,
                'display'=>null,
                'max_width'=>null,
                'max_height'=>null,
                'max_results'=>null,
                'order'=>null,
                'ordering'=>null,
                'required'=>'false',
                'no_parent_text'=>__('No Parent','wp-cred'),
                'select_text'=>__('-- Please Select --','wp-cred'),
                'validate_text'=>$this->getLocalisedMessage('field_required'),
            ), $atts ) );
            
            // make boolean
            $escape=false; //(bool)(strtoupper($escape)==='TRUE');
            // make boolean
            $readonly=(bool)(strtoupper($readonly)==='TRUE');
            
            if (!$taxonomy)
            {
                if (in_array($field,array_keys($this->_fields['post_fields'])))
                {
                    if ($post!=$this->_post_type) return '';
                    
                    $field=$this->_fields['post_fields'][$field];
                    $name=$name_orig=$field['slug'];
                    if (isset($field['plugin_type_prefix']))
                        $name = /*'wpcf-'*/$field['plugin_type_prefix'].$name;
                    
                    if ($field['type']=='image' || $field['type']=='file')
                        $ids=$this->translate_field($name,$field,array(
                                                'preset_value'=>$value,
                                                'is_tax'=>false,
                                                'max_width'=>$max_width,
                                                'max_height'=>$max_height));
                    else
                        $ids=$this->translate_field($name,$field,array(
                                                'preset_value'=>$value,
                                                'value_escape'=>$escape,
                                                'make_readonly'=>$readonly,
                                                'placeholder'=>$placeholder));
                        
                    // check which fields are actually used in form
                    $this->_form_fields[$name_orig]=$ids;
                    $this->_form_fields_qualia[$name_orig]=array(
                        'type'=>$field['type'],
                        'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                        'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                        'name'=>$name
                    );
                    $out='';
                    foreach ($ids as $id)
                        $out.= "[render-cred-field post='{$post}' field='{$id}']";
                    return $out;
                }
                elseif (in_array($field,array_keys($this->_fields['parents'])))
                {
                    $name=$name_orig=$field;
                    $field=$this->_fields['parents'][$field];
                    $potential_parents=CRED_Loader::get('MODEL/Fields')->getPotentialParents($field['data']['post_type'],$this->_post_id, $max_results, $order, $ordering);
                    $field['data']['options']=array();
                    
                    $default_option='';
                    // enable setting parent form url param
                    if (array_key_exists('parent_'.$field['data']['post_type'].'_id',$_GET))
                        $default_option=$_GET['parent_'.$field['data']['post_type'].'_id'];
                    
                    $required=(bool)(strtoupper($required)==='TRUE');
                    if (!$required)
                    {
                        $field['data']['options']['-1']=array(
                            'title' => $no_parent_text,
                            'value' => '-1',
                            'display_value' => '-1'
                        );
                    }
                    else
                    {
                        $field['data']['options']['-1']=array(
                            'title' => $select_text,
                            'value' => '',
                            'display_value' => '',
                            'dummy'=>true
                        );
                        $field['data']['validate']=array(
                            'required'=>array('message'=>$validate_text,'active'=>1)
                        );
                    }
                    foreach ($potential_parents as $ii=>$option)
                    {
                        $option_id=(string)($option->ID);
                        $field['data']['options'][$option_id]=array(
                            'title' => $option->post_title,
                            'value' => $option_id,
                            'display_value' => $option_id
                        );
                    }
                    $field['data']['options']['default'] = $default_option;
                    //print_r($field['data']);
                    $ids=$this->translate_field($name,$field,array('preset_value'=>$value));
                    // check which fields are actually used in form
                    $this->_form_fields[$name_orig]=$ids;
                    $this->_form_fields_qualia[$name_orig]=array(
                        'type'=>$field['type'],
                        'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                        'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                        'name'=>$name
                    );
                    $out='';
                    foreach ($ids as $id)
                        $out.= "[render-cred-field field='{$id}']";
                    return $out;
                }
                elseif (in_array($field,array_keys($this->_fields['form_fields'])))
                {
                    $name=$name_orig=$field;
                    $field=$this->_fields['form_fields'][$field];
                    $ids=$this->translate_field($name,$field,array('preset_value'=>$value));
                    // check which fields are actually used in form
                    $this->_form_fields[$name_orig]=$ids;
                    $this->_form_fields_qualia[$name_orig]=array(
                        'type'=>$field['type'],
                        'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                        'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                        'name'=>$name
                    );
                    $out='';
                    foreach ($ids as $id)
                        $out.= "[render-cred-field field='{$id}']";
                    return $out;
                }
                elseif (in_array($field,array_keys($this->_fields['extra_fields'])))
                {
                    $field=$this->_fields['extra_fields'][$field];
                    $name=$name_orig=$field['slug'];
                    $ids=$this->translate_field($name,$field,array('preset_value'=>$value));
                    // check which fields are actually used in form
                    $this->_form_fields[$name_orig]=$ids;
                    $this->_form_fields_qualia[$name_orig]=array(
                        'type'=>$field['type'],
                        'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                        'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                        'name'=>$name
                    );
                    $out='';
                    foreach ($ids as $id)
                        $out.= "[render-cred-field field='{$id}']";
                    return $out;
                }
                // taxonomy field
                elseif (in_array($field,array_keys($this->_fields['taxonomies'])))
                {
                    $field=$this->_fields['taxonomies'][$field];
                    $name=$name_orig=$field['name'];
                    $single_select=($single_select==='true');
                    $ids=$this->translate_field($name,$field,array('preset_value'=>$display,'is_tax'=>true,'single_select'=>$single_select));
                    // check which fields are actually used in form
                    $this->_form_fields[$name_orig]=$ids;
                    $this->_form_fields_qualia[$name_orig]=array(
                        'type'=>$field['type'],
                        'repetitive'=>(isset($field['data']['repetitive'])&&$field['data']['repetitive']),
                        'plugin_type'=>(isset($field['plugin_type']))?$field['plugin_type']:'',
                        'name'=>$name
                    );
                    $out='';
                    foreach ($ids as $id)
                        $out.= "[render-cred-field field='{$id}']";
                    return $out;
                }
            }
            else
            {
                if (in_array($taxonomy,array_keys($this->_fields['taxonomies'])) && in_array($type,array('show_popular','add_new')))
                {
                    if ( // auxilliary field type matches taxonomy type
                    ($type=='show_popular' && !$this->_fields['taxonomies'][$taxonomy]['hierarchical']) ||
                    ($type=='add_new' && $this->_fields['taxonomies'][$taxonomy]['hierarchical'])
                    )
                    {
                        $field=array(
                            'taxonomy'=>$this->_fields['taxonomies'][$taxonomy],
                            'type'=>$type,
                            'master_taxonomy'=>$taxonomy
                        );
                        $name=$name_orig=$taxonomy.'_'.$type;
                        $ids=$this->translate_field($name,$field,array('preset_value'=>$value,'is_tax'=>true));
                        // check which fields are actually used in form
                        //$this->_form_fields[$name_orig]=$ids;
                        $out='';
                        foreach ($ids as $id)
                            $out.= "[render-cred-field field='{$id}']";
                        return $out;
                    }
                }
            }
            return '';
        }
        
/**
 * CRED-Shortcode: cred-parent
 *
 * Description: Render data relating to pre-selected parent of the post the form will manipulate
 *
 * Parameters:
 * 'post_type' => [optional] Define a specifc parent type (if there are multiple parent types)
 * 'get' => Which information to render (title, url) 
 *
 * Example usage:
 *
 *  
 * [cred-parent get="url"]
 *
 **/
       public function cred_parent($atts)
       {
            extract( shortcode_atts( array(
                'post_type'=>null,
                'get'=>'title'
            ), $atts ) );
            
            $parent_id=null;
            if ($post_type)
            {
                if (isset($this->_fields['parents']['_wpcf_belongs_'.$post_type.'_id']) && isset($_GET['parent_'.$post_type.'_id']))
                {
                    $parent_id=intval($_GET['parent_'.$post_type.'_id']);
                }
            }
            else
            {
                foreach ($this->_fields['parents'] as $parentdata)
                {
                    if (isset($_GET['parent_'.$parentdata['data']['post_type'].'_id']))
                    {
                        $parent_id=intval($_GET['parent_'.$parentdata['data']['post_type'].'_id']);
                        break;
                    }
                }
            }
            
            if ($parent_id!==null)
            {
                switch($get)
                {
                    case 'title':
                        return get_the_title($parent_id);
                    case 'url':
                        return get_permalink($parent_id);
                    default:
                        return '';
                }
            }
             return '';
       }
       
       // parse final shortcodes (internal) which render the actual html fields [render-cred-field]
       public function render_cred_field_shortcodes($atts)
       {
            extract( shortcode_atts( array(
                'post' => '',
                'field' => '',
            ), $atts ) );
            
            if (isset($this->_controls[$field]))
                return $this->_controls[$field];
            
            return '';
        }
        
       // render the whole form (called from Zebra_Form)
       public function render_callback($controls,&$objs)
       {
            $this->_controls=$controls;
            
            // render shortcode
            $this->_form_content=$this->_shortcode_parser->do_shortcode($this->_form_content);
            return $this->_form_content;
       }
        
        // translate each cred field to a customized Zebra_Form field
        private function translate_field($name, &$field, $additional_options=array() /*$preset_value=null,$is_tax=false,$max_width=null,$max_height=null*/)
        {
            // extend additional_options with defaults
            extract(array_merge(
                array(
                    'preset_value'=>null,
                    'placeholder'=>null,
                    'value_escape'=>false,
                    'make_readonly'=>false,
                    'is_tax'=>false,
                    'max_width'=>null,
                    'max_height'=>null,
                    'single_select'=>false
                ),
                $additional_options
            ));
            
            // add the "name" element
            // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
            // for PHP 5+ there is no need for it
            $type='text';
            $attributes=array();
            $value='';
            
            $name_orig=$name;
            if (!$is_tax) // if not taxonomy field
            {
                if ($placeholder && $placeholder!==null && !empty($placeholder) && is_string($placeholder))
                {
                    // use translated value by WPML if exists
                    $placeholder=cred_translate('Value: '.$placeholder, $placeholder, 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID);
                }    
                
                if ($preset_value && $preset_value!==null && is_string($preset_value) && !empty($preset_value))
                {
                    // use translated value by WPML if exists
                    $data_value=cred_translate('Value: '.$preset_value, $preset_value, 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID);
                }                                                                           // allow persisted generic fields to display values
                elseif ($this->_post_data && isset($this->_post_data['fields'][$name_orig]) /*&& !isset($field['cred_generic'])*/)
                {
                    $data_value=$this->_post_data['fields'][$name_orig][0];
                }
                else
                {
                    $data_value=null;
                }
                
                $value='';
                // save a map between options / actual values for these types to be used later
                if ($field['type']=='checkboxes' || $field['type']=='radio' || $field['type']=='select'|| $field['type']=='multiselect')
                {
                    $tmp=array();
                    foreach ($field['data']['options'] as $optionKey=>$optionData)
                    {
                        if ($optionKey!='default' && is_array($optionData))
                            $tmp[$optionKey]=($field['type']=='checkboxes')?$optionData['set_value']:$optionData['value'];
                    }
                    $this->_field_values_map[$field['slug']]=$tmp;
                    unset($tmp);
                    unset($optionKey);
                    unset($optionData);
                }
                switch ($field['type'])
                {
                    case 'form_messages' :   $type='messages';
                                        break;
                    case 'recaptcha':   $type='recaptcha'; 
                                        $value='';
                                        $attributes=array(
                                            'error_message'=>$this->getLocalisedMessage('enter_valid_captcha'),
                                            'show_link'=>$this->getLocalisedMessage('show_captcha'),
                                            'no_keys'=>__('Enter your ReCaptcha keys at the CRED Settings page in order for ReCaptcha API to work','wp-cred')
                                            ); 
                                        if (self::$recaptcha_settings!==false)
                                        {
                                            $attributes['public_key']=self::$recaptcha_settings['public_key'];
                                            $attributes['private_key']=self::$recaptcha_settings['private_key'];
                                        }
                                        if ($this->_form_count==1)
                                            $attributes['open']=true;
                                        // used to load additional js script
                                        $this->hasRecaptcha=true;
                                        break;
                    case 'file':        $type='file';  if ($data_value!==null) $value=$data_value; 
                                        break;
                    case 'image':       $type='file';  if ($data_value!==null) $value=$data_value;
                                        // show previous post featured image thumbnail
                                        if ('_featured_image'==$name)
                                        {
                                            $value='';
                                            if (isset($this->_post_data['extra']['featured_img_html']))
                                            {
                                                $attributes['display_featured_html']=$this->_post_data['extra']['featured_img_html'];
                                            }
                                        }
                                        break;
                    case 'date':        $type='date'; 
                                        $format='';
                                        if (isset($field['data']) && isset($field['data']['validate']))
                                            $format=$field['data']['validate']['date']['format'];
                                        if (!in_array($format,$this->supported_date_formats))
                                            $format='F j, Y';
                                        $attributes['format']=$format;
                                        $attributes['readonly_element']=false;
                                            //cred_log('Date time '.$data_value);
                                        if ($data_value!==null && !empty($data_value) && (is_numeric($data_value) || is_int($data_value) || is_long($data_value)))
                                        {
                                            MyZebra_DateParser::setDateLocaleStrings(self::$localized_strings['days'], self::$localized_strings['months']);
                                            //cred_log('Days '.print_r(self::$localized_strings['days'],true));
                                            //$value = date($format,$data_value);
                                            // format localized date form timestamp 
                                            $value = MyZebra_DateParser::formatDate($data_value, $format, true);
                                            //cred_log('Date value '.$value);
                                        }
                                        break;
                    case 'select':      $type='select';
                                        $value=array();
                                        $attributes=array();
                                        $attributes['options']=array();
                                        $default=array();
                                        foreach ($field['data']['options'] as $key=>$option)
                                        {
                                            $index=$key; //$option['value']; 
                                            if ($key=='default')
                                            {
                                                $default[]=$option;
                                            }
                                            else
                                            {
                                                $attributes['options'][$index]=$option['title'];
                                                if (($data_value!==null) && $data_value==$option['value'])
                                                {
                                                    $value[]=$key;
                                                }
                                                if (isset($option['dummy']) && $option['dummy'])
                                                    $attributes['dummy']=$key;
                                            }
                                        }
                                        if (empty($value) && !empty($default))
                                            $value=$default;
                                        if (isset($this->_field_values_map[$field['slug']]))
                                            $attributes['actual_options']=$this->_field_values_map[$field['slug']];
   
                                        break;
                    case 'multiselect': $type='select';
                                        $value=array();
                                        $attributes=array();
                                        $attributes['options']=array();
                                        $attributes['multiple']='multiple';
                                        $default=array();
                                        foreach ($field['data']['options'] as $key=>$option)
                                        {
                                            $index=$key; //$option['value']; 
                                            if ($key=='default')
                                            {
                                                $default=(array)$option;
                                            }
                                            else
                                            {
                                                $attributes['options'][$index]=$option['title'];
                                                if (($data_value!==null) && $data_value==$option['value'])
                                                {
                                                    $value[]=$key;
                                                }
                                                if (isset($option['dummy']) && $option['dummy'])
                                                    $attributes['dummy']=$key;
                                            }
                                        }
                                        if (empty($value) && !empty($default))
                                            $value=$default;
                                        if (isset($this->_field_values_map[$field['slug']]))
                                            $attributes['actual_options']=$this->_field_values_map[$field['slug']];
   
                                        break;
                    case 'radio':       $type='radios'; 
                                        $value=array();
                                        $attributes='';
                                        $default='';
                                        foreach ($field['data']['options'] as $key=>$option)
                                        {
                                            $index=$key; //$option['display_value'];
                                            if ($key=='default')
                                            {
                                                $default=$option;
                                            }
                                            else
                                            {
                                                $value[$index]=$option['title'];
                                                if (($data_value!==null) && $data_value==$option['value'])
                                                {
                                                    $attributes=$key;
                                                }
                                            }
                                        }
                                        if (($data_value===null) && !empty($default))
                                        {
                                            $attributes=$default;
                                        }
                                        $def=$attributes;
                                        $attributes=array('default'=>$def);
                                       if (isset($this->_field_values_map[$field['slug']]))
                                            $attributes['actual_values']=$this->_field_values_map[$field['slug']];

                                        break;
                    case 'checkboxes':  $type='checkboxes'; $name.='[]'; 
                                        $value=array();
                                        $attributes=array();
                                        if (is_array($data_value))
                                            $data_value=array_keys($data_value);
                                        elseif ($data_value!==null) $data_value=array($data_value);
                                        foreach ($field['data']['options'] as $key=>$option)
                                        {
                                            $index=$key;
                                            $value[$index]=$option['title'];
                                            if (isset($option['checked']) && $option['checked'] && $data_value===null)
                                            {
                                                $attributes[]=$index;
                                            }
                                            elseif (($data_value!==null) && in_array($index,$data_value))
                                            {
                                                $attributes[]=$index;
                                            }
                                        }
                                        $def=$attributes;
                                        $attributes=array('default'=>$def);
                                        if (isset($this->_field_values_map[$field['slug']]))
                                            $attributes['actual_values']=$this->_field_values_map[$field['slug']];
                                        //print_r($attributes);
                                        //print_r($value);
                                        break;
                    case 'checkbox':    $type='checkbox'; $value=$field['data']['set_value']; 
                                        if (($data_value!==null) && $data_value==$value) $attributes=array('checked'=>'checked');
                                        break;
                    case 'textarea':    $type='textarea'; if ($data_value!==null) $value=$data_value; 
                                        break;
                    case 'wysiwyg':     $type='wysiwyg'; if ($data_value!==null) $value=$data_value; 
                                        $attributes=array('disable_xss_filters'=>true);
                                        if ($name=='post_content' && isset($this->_form->fields['form_settings']->has_media_button) && $this->_form->fields['form_settings']->has_media_button)
                                            $attributes['has_media_button']=true;
                                        break;
                    case 'form_submit': $type='submit'; if ($data_value!==null) $value=$data_value; 
                                        break;
                    case 'numeric':     $type='text'; if ($data_value!==null) $value=$data_value; 
                                        break;
                    case 'phone':       $type='text'; if ($data_value!==null) $value=$data_value; 
                                        break;
                    case 'url':         $type='text'; if ($data_value!==null) $value=$data_value; 
                                        break;
                    case 'email':       $type='text'; if ($data_value!==null) $value=$data_value; 
                                        break;
                    case 'textfield':   $type='text'; if ($data_value!==null) $value=$data_value; 
                                        if ($placeholder && null!==$placeholder && !empty($placeholder))
                                            $attributes['placeholder']=$placeholder;
                                        break;
                    case 'password':   $type='password'; if ($data_value!==null) $value=$data_value; 
                                        if ($placeholder && null!==$placeholder && !empty($placeholder))
                                            $attributes['placeholder']=$placeholder;
                                        break;
                    case 'hidden':      $type='hidden'; if ($data_value!==null) $value=$data_value;
                                        break;
                    case 'skype':       $type='skype'; 
                                        if (($data_value!==null) && is_string($data_value))
                                            $data_value=array('skypename'=>$data_value,'style'=>'');
                                        if ($data_value!==null) $value=$data_value;
                                        else
                                            $value=array('skypename'=>'','style'=>'');
                                        $attributes=array(
                                            'ajax_url'=>admin_url('admin-ajax.php'),
                                            'edit_skype_text'=>$this->getLocalisedMessage('edit_skype_button'),
                                            'value' => $data_value,
                                            '_nonce'=>wp_create_nonce('insert_skype_button')
                                            );
                                        break;
                    default:            $type='text'; if ($data_value!==null) $value=$data_value; 
                                        break;
                }
                
                if ($make_readonly)
                {
                    if (!is_array($attributes))
                        $attributes=array();
                    $attributes['readonly']='readonly';
                }
                // no extra escaping, just default framework XSS filter
                /*if ($value_escape)
                {
                    if (!is_array($attributes))
                        $attributes=array();
                    $attributes['escape']=true;
                }*/
                
                // repetitive field (special care)
                if (isset($field['data']['repetitive']) && $field['data']['repetitive']==1)
                {
                    $name.='[]';
                    $objs = & $this->_myzebra_form->add_repeatable($type, $name, $value, $attributes);
                    
                    if (isset($this->_post_data['fields'][$name_orig]) && count($this->_post_data['fields'][$name_orig])>1)
                    for ($ii=1; $ii<count($this->_post_data['fields'][$name_orig]) && count($this->_post_data['fields'][$name_orig]); $ii++)
                    {
                        $data_value=$this->_post_data['fields'][$name_orig][$ii];
                        $atts=array();
                        switch ($type)
                        {
                            case 'skype':
                                    $atts=array(
                                        'value' => $data_value,
                                        );
                                break;
                            case 'date':
                                        $format='';
                                        if (isset($field['data']) && isset($field['data']['validate']))
                                            $format=$field['data']['validate']['date']['format'];
                                        if (!in_array($format,$this->supported_date_formats))
                                            $format='F j, Y';
                                        $atts['format']=$format;
                                        $atts['readonly_element']=false;
                                        if (!empty($data_value))
                                        {
                                            MyZebra_DateParser::setDateLocaleStrings(self::$localized_strings['days'], self::$localized_strings['months']);
                                            //$atts['value'] = date($format,$data_value);
                                            // format localized date form timestamp 
                                            $atts['value'] = MyZebra_DateParser::formatDate($data_value, $format, true);
                                            //cred_log('Date value '.$atts['value']);
                                        }
                                        break;

                            case 'file':
                                        $atts['value']=$data_value;
                                        break;
                            case 'text':
                                        $atts['value']=$data_value;
                                        break;
                            case 'wysiwyg':
                            case 'textarea':
                                        $atts['value']=$data_value;
                                        break;
                            case 'checkbox':
                                    $value=$field['data']['set_value']; 
                                    if ($data_value==$value) $atts=array('checked'=>'checked');
                                    break;
                            case 'select':
                                        $value=array();
                                        foreach ($field['data']['options'] as $key=>$option)
                                        {
                                            $index=$option['value'];//$option['set_value'];
                                            if ($key=='default' && $data_value=='')
                                            {
                                                $value[]=$field['data']['options'][$option]['value'];
                                            }
                                            elseif ($data_value!='')
                                            {
                                                $value[]=$data_value;
                                            }
                                            
                                        }
                                        $atts['value']=$value;
                                        break;
                            default:
                                        $atts['value']=$data_value;
                                        break;
                        }
                        $objs->addControl($atts);
                    }
                }
                else
                {
                    $objs = & $this->_myzebra_form->add($type, $name, $value, $attributes);
                }
                if (!is_array($objs)) $oob=array($objs);
                else    $oob=$objs;
                
                $ids=array();
                // add validation rules if needed
                foreach ($oob as &$obj)
                {
                    $obj->setPrimeName($name_orig);
                    
                    if ('hidden'==$type)
                    {
                        $obj->attributes['user_defined']=true;
                    }
                    
                    // field belongs to a container?
                    if ($this->_current_group!==null)
                    {
                        $this->_current_group->addControl($obj);
                        //$obj->setParent($this->_current_group);
                    }
                        
                    $atts = $obj->get_attributes(array('id','type'));
                    $ids[]=$atts['id'];
                    if ($atts['type']=='label') continue;
                    
                    switch($type)
                    {
                        case 'file':
                            $upload=wp_upload_dir();
                            // set rules
                            $obj->set_rule(array(

                                // error messages will be sent to a variable called "error", usable in custom templates
                                'upload' => array($upload['path'], $upload['url'], true, 'error', $this->getLocalisedMessage('upload_failed')),
                            ));
                            $obj->set_attributes(array('external_upload'=>true)); // we will handle actual upload
                            
                            if ($field['type']=='image')
                            {
                                // set rules
                                $obj->set_rule(array(

                                    // error messages will be sent to a variable called "error", usable in custom templates
                                    'image' => array('error', $this->getLocalisedMessage('not_valid_image')),
                                ));
                                
                            }
                            else
                            {
                                // if general file upload, restrict to Wordpress allowed file types
                                $obj->set_rule(array(

                                    // error messages will be sent to a variable called "error", usable in custom templates
                                    'filetype'=>array($this->wp_mimes,'error',$this->getLocalisedMessage('file_type_not_allowed'))
                                ));
                            }
                            if (null!==$max_width && is_numeric($max_width))
                            {
                                $max_width=intval($max_width);
                                $obj->set_rule(array(

                                    // error messages will be sent to a variable called "error", usable in custom templates
                                    'image_max_width' => array($max_width, sprintf($this->getLocalisedMessage('image_width_larger'),$max_width))
                                ));
                            }
                            if (null!==$max_height && is_numeric($max_height))
                            {
                                $max_height=intval($max_height);
                                $obj->set_rule(array(

                                    // error messages will be sent to a variable called "error", usable in custom templates
                                    'image_max_height' => array($max_height, sprintf($this->getLocalisedMessage('image_height_larger'),$max_height))
                                ));
                            }
                            break;
                    }
                    
                    if (isset($field['data']) && isset($field['data']['validate']))
                    {
                        foreach ($field['data']['validate'] as $method=>$validation)
                        {
                            if ($validation['active']==1)
                            {
                                switch ($method)
                                {
                                    case 'required':
                                        // set rules
                                        $obj->set_rule(array(

                                            // error messages will be sent to a variable called "error", usable in custom templates
                                            'required' => array('error', $this->getLocalisedMessage('field_required') /*$validation['message']*/)

                                        ));
                                        break;
                                    case 'hidden':
                                        // set rules
                                        $obj->set_rule(array(

                                            // error messages will be sent to a variable called "error", usable in custom templates
                                            'hidden' => array('error', $this->getLocalisedMessage('values_do_not_match') /*$validation['message']*/)

                                        ));
                                        // default attribute to check against submitted value
                                        $obj->set_attributes(array('default'=>$obj->attributes['value']));
                                        break;
                                    case 'date':
                                        // set rules
                                        $obj->set_rule(array(

                                            // error messages will be sent to a variable called "error", usable in custom templates
                                            'date' => array('error', $this->getLocalisedMessage('enter_valid_date') /*$validation['message']*/)

                                        ));
                                        break;
                                    case 'email':
                                        // set rules
                                        $obj->set_rule(array(

                                            // error messages will be sent to a variable called "error", usable in custom templates
                                            'email' => array('error', $this->getLocalisedMessage('enter_valid_email') /*$validation['message']*/)

                                        ));
                                        break;
                                    case 'number':
                                        // set rules
                                        $obj->set_rule(array(

                                            // error messages will be sent to a variable called "error", usable in custom templates
                                            'number' => array('','error', $this->getLocalisedMessage('enter_valid_number') /*$validation['message']*/)

                                        ));
                                        break;
                                    case 'image':
                                    case 'file':
                                    break;
                                    case 'url':
                                        // set rules
                                        $obj->set_rule(array(

                                            // error messages will be sent to a variable called "error", usable in custom templates
                                            'url' => array('error', $this->getLocalisedMessage('enter_valid_url') /*$validation['message']*/)

                                        ));
                                        break;
                                }
                            }
                        }
                    }
                }
            }
            else // taxonomy field or auxilliary taxonomy field (eg popular terms etc..)
            {
                if (!array_key_exists('master_taxonomy',$field)) // taxonomy field
                {
                    if ($field['hierarchical'])
                    {
                        if (in_array($preset_value,array('checkbox','select')))
                            $tax_display=$preset_value;
                        else
                            $tax_display='checkbox';
                    }
                    
                    if ($this->_post_data && isset($this->_post_data['taxonomies'][$name_orig]))
                    {
                        if (!$field['hierarchical'])
                        {
                            $data_value=array(
                                'terms'=>$this->_post_data['taxonomies'][$name_orig]['terms'],
                                'add_text'=>$this->getLocalisedMessage('add_taxonomy'),
                                'remove_text'=>$this->getLocalisedMessage('remove_taxonomy'),
                                'ajax_url'=>admin_url('admin-ajax.php'),
                                'auto_suggest'=>true);
                        }
                        else
                        {
                            $data_value=array(
                                'terms'=>$this->_post_data['taxonomies'][$name_orig]['terms'],
                                'all'=>$field['all'],
                                'type'=>$tax_display,
                                'single_select'=>$single_select);
                        }
                    }
                    else
                    {
                        if (!$field['hierarchical'])
                        {
                            $data_value=array(
                                //'terms'=>array(),
                                'add_text'=>$this->getLocalisedMessage('add_taxonomy'),
                                'remove_text'=>$this->getLocalisedMessage('remove_taxonomy'),
                                'ajax_url'=>admin_url('admin-ajax.php'),
                                'auto_suggest'=>true);
                        }
                        else
                        {
                            $data_value=array(
                                'all'=>$field['all'],
                                'type'=>$tax_display,
                                'single_select'=>$single_select);
                        }
                    }
                    
                    // if not hierarchical taxonomy
                    if (!$field['hierarchical'])
                    {
                        $objs = & $this->_myzebra_form->add('taxonomy', $name, $value, $data_value);
                    }
                    else
                    {
                        $objs = & $this->_myzebra_form->add('taxonomyhierarchical', $name, $value, $data_value);
                    }
                    
                    // register this taxonomy field for later use by auxilliary taxonomy fields
                    $this->_taxonomy_aux['taxonomy'][$name_orig]=&$objs;
                    // if a taxonomy auxiliary field exists attached to this taxonomy, add this taxonomy id to it
                    if (isset($this->_taxonomy_aux['aux'][$name_orig]))
                    {
                        $this->_taxonomy_aux['aux'][$name_orig]->set_attributes(array('master_taxonomy_id'=>$objs->attributes['id']));
                    }
                    
                    if (!is_array($objs)) $oob=array($objs);
                    else    $oob=$objs;
                    $ids=array();
                    foreach ($oob as &$obj)
                    {
                        $obj->setPrimeName($name_orig);
                        
                        // field belongs to a container?
                        if ($this->_current_group!==null)
                        {
                            $this->_current_group->addControl($obj);
                            //$obj->setParent($this->_current_group);
                        }
                        
                        $atts = $obj->get_attributes(array('id','type'));
                        $ids[]=$atts['id'];
                    }
                }
                else // taxonomy auxilliary field (eg most popular etc..)
                {
                    if ($preset_value && $preset_value!==null /*&& !empty($preset_value)*/)
                        // use translated value by WPML if exists
                        $data_value=cred_translate('Value: '.$preset_value, $preset_value, 'cred-form-'.$this->_form->form->post_title.'-'.$this->_form->form->ID);
                    else
                        $data_value=null;
                    
                    $ids=array();
                    if (in_array($field['type'],array('show_popular','add_new'))) // these auxilliaries are implemented
                    {
                        if ($field['type']=='show_popular')
                        {
                            $objs = & $this->_myzebra_form->add('taxonomypopular', $name, $value, array(
                                        'popular'=>$field['taxonomy']['most_popular'],
                                        'show_popular_text'=>$this->getLocalisedMessage('show_popular'),
                                        'hide_popular_text'=>$this->getLocalisedMessage('hide_popular')));
                        }
                        elseif ($field['type']=='add_new')
                        {
                            $objs = & $this->_myzebra_form->add('taxonomyhierarchicaladdnew', $name, $value, array(
                                                'add_new_text'=>$this->getLocalisedMessage('add_new_taxonomy'),
                                                'add_text'=>$this->getLocalisedMessage('add_taxonomy'),
                                                'parent_text'=>__('-- Parent --','wp-cred')

                            ));
                        }
                        
                        // register this taxonomy auxilliary field for later use by taxonomy fields
                        $this->_taxonomy_aux['aux'][$field['master_taxonomy']]=&$objs;
                        // if a taxonomy field exists that this field is attached, link to its id here 
                        if (isset($this->_taxonomy_aux['taxonomy'][$field['master_taxonomy']]))
                        {
                            $objs->set_attributes(array('master_taxonomy_id'=>$this->_taxonomy_aux['taxonomy'][$field['master_taxonomy']]->attributes['id']));
                        }
                        
                        if (!is_array($objs)) $oob=array($objs);
                        else    $oob=$objs;
                        foreach ($oob as &$obj)
                        {
                            $atts = $obj->get_attributes(array('id','type'));
                            $ids[]=$atts['id'];
                        }
                    }
                }
            }
            return $ids; // return the ids of the created fields
        }
}
?>