<?php
/**
 * MainClass
 *
 * Main class of the plugin
 * Class encapsulates all hook handlers
 *
 */
class CRED_CRED
{

    private static $cred_wpml_option='_cred_cred_wpml_active';
    public static $help=array();
    public static $help_link_target='_blank';
    private static $prefix='_cred_';
    public static $settingsPage=null;
    private static $screens=array();
    private static $caps=array();

/**
 * Initialize plugin enviroment
 */
   public static function init()
   {
        global $wp_version, $post;

        // load translations from locale
        load_plugin_textdomain('wp-cred', false, CRED_LOCALE_PATH);

        // load help settings (once)
        self::$help=CRED_Loader::getHelpSettings();

        // set up models and db settings
        self::prepareDB();
		self::$settingsPage=admin_url('admin.php').'?page=CRED_Settings';

        if(is_admin())
        {
            // setup js, css assets
            add_action('admin_enqueue_scripts',array('CRED_CRED','cred_admin_head'));

            // add plugin menus
            add_action('admin_menu', array('CRED_CRED', 'addMenuItems'));

            // add media buttons for cred forms at editor
            if (version_compare($wp_version, '3.1.4', '>'))
                add_action('media_buttons', array('CRED_CRED', 'addFormsButton'),20, 2);
            else
                add_action('media_buttons_context', array('CRED_CRED', 'addFormsButton'), 20, 2);

            // integrate with Views
            add_filter('wpv_meta_html_add_form_button', array('CRED_CRED', 'addCREDButton'), 20, 2);

            // add custom meta boxes for cred forms
            add_action('add_meta_boxes_' . CRED_FORMS_CUSTOM_POST_NAME, array('CRED_CRED', 'addMetaBoxes'), 20, 1);

            // save custom fields of cred forms
            add_action('save_post', array('CRED_CRED', 'saveFormCustomFields'), 10, 2);
            //add_filter( 'default_content', array('CRED_CRED','formDefaultValues'), 10, 2 );
            add_filter('wp_insert_post_data', array('CRED_CRED','forcePrivateforForms'));

            // add custom js on certain pages
            if (version_compare($wp_version, '3.3', '>=')) {
                //add_action('admin_head-edit.php', array($this, 'admin_add_help'));
                add_action('admin_head-post.php', array('CRED_CRED', 'jsForCredCustomPost'));
                add_action('admin_head-post-new.php', array('CRED_CRED', 'jsForCredCustomPost'));
            }

            if (version_compare($wp_version, '3.2', '>=')) {
                if (isset($post) && $post->post_type==CRED_FORMS_CUSTOM_POST_NAME)
                    remove_action( 'pre_post_update', 'wp_save_post_revision');
            }
        }
        else
        {
            // add form short code hooks and filters, to display forms on front end
            self::addShortcodesandFilters();
        }

        // stub wpml-string shortcode
        if (!self::check_wpml_string())
        {
            // WPML string translation is not active
            // Add our own do nothing shortcode
            add_shortcode('wpml-string', array('CRED_CRED','stub_wpml_string_shortcode'));
        }
        else
        {
            $wpml_was_active=get_option(self::$cred_wpml_option);
            // if changes before wpml activated, re-process all forms
            if ($wpml_was_active && $wpml_was_active=='no')
            {
                //cred_log('process all forms');
                CRED_Loader::load('CLASS/Form_Processor');
                $cfp=new CRED_Form_Processor(null,null);
                $cfp->processAllFormsForStrings();
                update_option(self::$cred_wpml_option,'yes');
            }
        }
        // setup custom capabilities
        self::setupCustomCaps();
        // setup extra admin hooks for other plugins
        self::setupExtraHooks();

        if (!is_admin())
        {
            // init form processing to check for submits
            CRED_Loader::load('CLASS/Form_Builder');
            CRED_Form_Builder::init();
        }

        // handle custom routes, for (quasi) admin section
        CRED_Loader::load('CLASS/Router');
        CRED_Router::init();
        // handle Ajax calls
        CRED_Loader::load('CLASS/Ajax_Router');
        CRED_Ajax_Router::init();
   }


    private static function setupExtraHooks()
    {
        // setup module manager hooks and actions
        if (defined('MODMAN_PLUGIN_NAME'))
        {
            $section_id=_CRED_MODULE_MANAGER_KEY_;
            add_filter('wpmodules_register_sections', array('CRED_CRED','register_modules_cred_sections'),10,1);
            add_filter('wpmodules_register_items_'.$section_id, array('CRED_CRED','register_modules_cred_items'), 10, 1);
            add_filter('wpmodules_export_items_'.$section_id, array('CRED_CRED','export_modules_cred_items'), 10, 2);
            add_filter('wpmodules_import_items_'.$section_id, array('CRED_CRED','import_modules_cred_items'), 10, 3);
            add_filter('wpmodules_items_check_'.$section_id, array('CRED_CRED','modules_cred_items_exist'), 10, 1);
        }
    }

    public static function register_modules_cred_sections($sections)
    {
        $sections[_CRED_MODULE_MANAGER_KEY_]=array(
           'title'=>__('CRED Forms','wp-cred'),
           'icon'=>CRED_ASSETS_URL.'/images/cred_12x12_color.png'
        );
        
        return $sections;
    }
    
    public static function register_modules_cred_items($items)
    {
        $forms=self::getAllFormsCached();
        
        foreach ($forms as $form)
        {
            if ('edit'==$form->meta->form_type)
                $details=sprintf(__('This form edits posts of post type "%s".'),$form->meta->post_type);
            else
                $details=sprintf(__('This form creates posts of post type "%s".'),$form->meta->post_type);
                
            $items[]=array(
                'id'=>_CRED_MODULE_MANAGER_KEY_.$form->ID,
                'title'=>$form->post_title,
                'details'=>'<p style="padding:5px;">'.$details.'</p>'
            );
        }
        return $items;
    }
    
    public static function export_modules_cred_items($res, $items)
    {
        foreach ($items as $ii=>$item)
        {
            $items[$ii]=str_replace(_CRED_MODULE_MANAGER_KEY_,'',$item);
        }
        CRED_Loader::load('CLASS/XML_Processor');
        $xmlstring=CRED_XML_Processor::exportToXMLString($items);
        return $xmlstring;
    }
    
    public static function import_modules_cred_items($res, $xmlstring, $items=false)
    {
        CRED_Loader::load('CLASS/XML_Processor');
        if (false!==$items && is_array($items))
        {
            $import_items=array();
            foreach ($items as $item)
                $import_items[]=str_replace(_CRED_MODULE_MANAGER_KEY_,'',$item);
            unset($items);
            $results=CRED_XML_Processor::importFromXMLString($xmlstring, array('overwrite_forms'=>true, 'items'=>$import_items));
        }
        else
        {
            $results=CRED_XML_Processor::importFromXMLString($xmlstring);
        }
        
        if (false===$results || is_wp_error($results))
        {
            $error=(false===$results)?__('Error during CRED import','wp-cred'):$results->get_error_message($results->get_error_code());
            $results=array('new'=>0,'updated'=>0,'failed'=>0,'errors'=>array($error));
        }
        unset($results['settings']);
            
        return $results;
    }
    
    public static function modules_cred_items_exist($items)
    {
        foreach ($items as $key=>$item)
        {
            // item exists already
            if (get_page_by_title( $item['title'], OBJECT, CRED_FORMS_CUSTOM_POST_NAME ))
            {
                $items[$key]['exists']=true;
            }
            else
            {
                $items[$key]['exists']=false;
            }
        }
        return $items;
    }

    private static function getAllFormsCached()
    {
        static $cache=null;

        if (null===$cache)
        {
            $cache=CRED_Loader::get('MODEL/Forms')->getFormsForTable(1,-1);
        }
        return $cache;
    }

    private static function setupCustomCaps()
    {
        global $wp_roles;

        if (function_exists('wpcf_access_register_caps')) // integrate with Types Access
        {
            cred_log('Access Active', 'access.log');
            add_filter('types-access-area', array('CRED_CRED','register_access_cred_area'));
            add_filter('types-access-group', array('CRED_CRED','register_access_cred_group'), 10, 2);
            add_filter('types-access-cap', array('CRED_CRED','register_access_cred_caps'), 10, 3);
        }
        elseif (function_exists('ure_not_edit_admin') /* user role ditor plugin */ || class_exists('Members_Load') /* Members plugin */) // export custom cred caps to admin role for other plugins to manipulate them (eg User Role Editor or Members)
        {
            if (!isset($wp_roles) && class_exists('WP_Roles'))
            {
                $wp_roles = new WP_Roles();
            }
            $wp_roles->use_db = true;
            if ($wp_roles->is_role('administrator'))
                $administrator = $wp_roles->get_role('administrator');
            else
            {
                $administrator=false;
                trigger_error(__('Administrator Role not found! CRED capabilities will not work','wp-cred'),E_USER_NOTICE);
            }

            if ($administrator)
            {
                $forms=self::getAllFormsCached();
                // register custom CRED Frontend capabilities specific to each form type
                //foreach ($wp_roles as $role)
                //{
                foreach ($forms as $form)
                {
                    $settings=isset($form->meta)?maybe_unserialize($form->meta):false;
                    // caps for forms that create
                    if ($settings && $settings->form_type=='new')
                    {
                        $cred_cap='create_posts_with_cred_'.$form->ID;
                        if (!$administrator->has_cap($cred_cap))
                            $wp_roles->add_cap('administrator', $cred_cap);
                        /*if (!$role->has_cap($cred_cap))
                            $role->add_cap($cred_cap);*/
                    }
                    elseif ($settings && $settings->form_type=='edit')
                    {
                        $cred_cap='edit_own_posts_with_cred_'.$form->ID;
                        if (!$administrator->has_cap($cred_cap))
                            $wp_roles->add_cap('administrator', $cred_cap);
                        /*if (!$role->has_cap($cred_cap))
                            $role->add_cap($cred_cap);*/
                        $cred_cap='edit_other_posts_with_cred_'.$form->ID;
                        if (!$administrator->has_cap($cred_cap))
                            $wp_roles->add_cap('administrator', $cred_cap);
                        /*if (!$role->has_cap($cred_cap))
                            $role->add_cap($cred_cap);*/
                    }
                }
                // these caps do not require a specific form
                $cred_cap='delete_own_posts_with_cred';
                if (!$administrator->has_cap($cred_cap))
                    $wp_roles->add_cap('administrator', $cred_cap);
                /*if (!$role->has_cap($cred_cap))
                   $role->add_cap($cred_cap);*/
                $cred_cap='delete_other_posts_with_cred';
                if (!$administrator->has_cap($cred_cap))
                    $wp_roles->add_cap('administrator', $cred_cap);
                /*if (!$role->has_cap($cred_cap))
                    $role->add_cap($cred_cap);*/
            }
            //}
        }
        else
        {
            $forms=self::getAllFormsCached();
            // register custom CRED Frontend capabilities specific to each form type
            foreach ($forms as $form)
            {
                $settings=isset($form->meta)?maybe_unserialize($form->meta):false;
                // caps for forms that create
                if ($settings && $settings->form_type=='new')
                {
                    $cred_cap='create_posts_with_cred_'.$form->ID;
                    self::$caps[]=$cred_cap;
                }
                elseif ($settings && $settings->form_type=='edit')
                {
                    $cred_cap='edit_own_posts_with_cred_'.$form->ID;
                    self::$caps[]=$cred_cap;
                    $cred_cap='edit_other_posts_with_cred_'.$form->ID;
                    self::$caps[]=$cred_cap;
                }
            }
            // these caps do not require a specific form
            $cred_cap='delete_own_posts_with_cred';
            self::$caps[]=$cred_cap;
            $cred_cap='delete_other_posts_with_cred';
            self::$caps[]=$cred_cap;
            add_filter('user_has_cap', array('CRED_CRED','default_cred_caps_filter'),5,3);
        }
    }

    // default cred caps filter, all true
    public static function default_cred_caps_filter($allcaps, $caps, $args)
    {
        foreach (self::$caps as $cred_cap)
            $allcaps[$cred_cap]=true;
        return $allcaps;
    }

    // register a new Types Access Area for custom CRED Frontend capabilities
    public static function register_access_cred_area($areas)
    {
        $CRED_ACCESS_AREA_NAME=__('CRED Frontend Access','wp-cred');
        $CRED_ACCESS_AREA_ID='__CRED_CRED';
        $CRED_ACCESS_GROUP_NAME=__('CRED Frontend Access Group','wp-cred');
        $CRED_ACCESS_GROUP_ID='__CRED_CRED_GROUP';
        $areas[] = array('id' => $CRED_ACCESS_AREA_ID, 'name' => $CRED_ACCESS_AREA_NAME);
        cred_log('Access Areas after CRED', 'access.log');
        cred_log($areas, 'access.log');
        return $areas;
    }

    // register a new Types Access Group within Area for custom CRED Frontend capabilities
    public static function register_access_cred_group($groups, $id)
    {
        $CRED_ACCESS_AREA_NAME=__('CRED Frontend Access','wp-cred');
        $CRED_ACCESS_AREA_ID='__CRED_CRED';
        $CRED_ACCESS_GROUP_NAME=__('CRED Frontend Access Group','wp-cred');
        $CRED_ACCESS_GROUP_ID='__CRED_CRED_GROUP';
        if ($id == $CRED_ACCESS_AREA_ID)
        {
            $groups[] = array('id' => $CRED_ACCESS_GROUP_ID, 'name' => $CRED_ACCESS_GROUP_NAME);
            cred_log('Access Groups after CRED', 'access.log');
            cred_log($groups, 'access.log');
        }
        return $groups;
    }

    // register custom CRED Frontend capabilities specific to each form type
    public static function register_access_cred_caps($caps, $area_id, $group_id)
    {
        $CRED_ACCESS_AREA_NAME=__('CRED Frontend Access','wp-cred');
        $CRED_ACCESS_AREA_ID='__CRED_CRED';
        $CRED_ACCESS_GROUP_NAME=__('CRED Frontend Access Group','wp-cred');
        $CRED_ACCESS_GROUP_ID='__CRED_CRED_GROUP';
        $default_role='guest'; //'administrator';

        if ($area_id == $CRED_ACCESS_AREA_ID && $group_id == $CRED_ACCESS_GROUP_ID)
        {
            $forms=self::getAllFormsCached();
            foreach ($forms as $form)
            {
                $settings=isset($form->meta)?maybe_unserialize($form->meta):false;
                // caps for forms that create
                if ($settings && $settings->form_type=='new')
                {
                    $cred_cap='create_posts_with_cred_'.$form->ID;
                    $caps[$cred_cap] = array(
                        'cap_id' => $cred_cap,
                        'title' => sprintf(__('Create Custom Post with CRED Form "%s"','wp-cred'),$form->post_title),
                        'default_role' => $default_role
                    );
                }
                elseif ($settings && $settings->form_type=='edit')
                {
                    $cred_cap='edit_own_posts_with_cred_'.$form->ID;
                    $caps[$cred_cap] = array(
                        'cap_id' => $cred_cap,
                        'title' => sprintf(__('Edit Own Custom Post with CRED Form "%s"','wp-cred'),$form->post_title),
                        'default_role' => $default_role
                    );
                    $cred_cap='edit_other_posts_with_cred_'.$form->ID;
                    $caps[$cred_cap] = array(
                        'cap_id' => $cred_cap,
                        'title' => sprintf(__('Edit Others Custom Post with CRED Form "%s"','wp-cred'),$form->post_title),
                        'default_role' => $default_role
                    );
                }
            }
            // these caps do not require a specific form
            $caps['delete_own_posts_with_cred'] = array(
                'cap_id' => 'delete_own_posts_with_cred',
                'title' => __('Delete Own Posts using CRED','wp-cred'),
                'default_role' => $default_role
            );
            $caps['delete_other_posts_with_cred'] = array(
                'cap_id' => 'delete_other_posts_with_cred',
                'title' => __('Delete Others Posts using CRED','wp-cred'),
                'default_role' => $default_role
            );
            cred_log('Access Caps after CRED', 'access.log');
            cred_log($caps, 'access.log');
        }
        return $caps;
    }

    private static function check_wpml_string()
    {
        global $WPML_String_Translation;

        if (!isset($WPML_String_Translation) || !function_exists('icl_register_string')) {
            return false;
        }
        return true;
    }

    public static function stub_wpml_string_shortcode($atts, $value)
    {
        // return un-processed.
        return do_shortcode($value);
    }

    public static function has_form()
    {
        CRED_Loader::load('CLASS/Form_builder');
        return CRED_Form_Builder::has_form();
    }

    public static function addShortcodesandFilters()
    {
        // check to see if form preview is required
        //add_action('pre_get_posts', array('CRED_CRED','preview_form'),5000);
        add_filter('the_posts',array('CRED_CRED','preview_form'),5000);

        // load front end form assets
        add_action('wp_head',array('CRED_Form_Builder','load_cred_form_frontend_assets'));
        add_action('wp_footer',array('CRED_Form_Builder','unload_cred_form_frontend_assets'));

        // delete post link shortcode
        add_shortcode('cred-delete-post-link',array('CRED_CRED', 'credDeletePostLinkShortcode'));

        // edit post form link shortcode
        add_shortcode('cred-link-form',array('CRED_CRED', 'credFormLinkShortcode'));

        // link to child form
        add_shortcode('cred-child-link-form',array('CRED_CRED', 'credChildFormLinkShortcode'));

        // form display shortcode
        add_shortcode('cred-form',array('CRED_CRED', 'credFormShortcode'));

        // replace content when preview or edit form
        add_action('template_redirect',array('CRED_CRED','overrideContentFilter'),1000);
        //add_action( 'after_setup_theme',array('CRED_CRED','overrideContentFilter'),1000);
    }

    public static function preview_form($posts /*&$query*/)
    {
        global $wp;
        global $wp_query, $post;
        
        // allow preview only if form preview key set
        if (!array_key_exists('cred_form_preview',$_GET)) return $posts;

        /*$query->query_vars['post__in']=array(intval($_GET['cred_form_preview']));
        $query->query_vars['p']=intval($_GET['cred_form_preview']);
        $query->query_vars['post_status']='any';
        $query->query_vars['post_type']=CRED_FORMS_CUSTOM_POST_NAME;

        // reset other queries which might have been set due to preview form submission
        $query->is_404 = false;*/
        $posts=array();
        $posts[]=get_post(intval($_GET['cred_form_preview']));
        //$wp_query->is_page = true;
        //Not sure if this one is necessary but might as well set it like a true page
        $wp_query->is_singular = true;
        //$wp_query->is_home = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        //Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
        unset($wp_query->query["error"]);
        $wp_query->query_vars["error"]="";
        $wp_query->is_404=false;

        return $posts;
    }

    public static function overrideContentFilter()
    {
        global $wp_query, $post;

        // if it is front page and form preview is required
        if ((array_key_exists('cred_form_preview',$_GET))
            // if post edit url is given
            || (array_key_exists('cred-edit-form',$_GET) && is_singular()))
        {
            // remove prev filters
            cred_disable_filters_for('the_content');
            // replace post content with edit form if post editing url is given
            add_filter('the_content',array('CRED_CRED', 'credReplaceContentWithForm'),1000);
        }
    }

    public static function credReplaceContentWithForm($content)
    {
        global $post, $wp_query;

        // if it is fornt page and form preview is required
        if (array_key_exists('cred_form_preview',$_GET) /*&& is_front_page()*/)
            return self::getForm(intval($_GET['cred_form_preview']),true);

        // if post edit url is given
        if (array_key_exists('cred-edit-form',$_GET) /*&& is_singular()*/ && !is_admin())
            return self::getForm(intval($_GET['cred-edit-form']),false,$post->ID);

        // else do nothing
        return $content;
    }

    public static function cred_delete_post_link($post_id=false, $text='', $action='', $class='', $style='')
    {
        global $post,$current_user;
        static $idcount=0;

        if (!current_user_can('delete_own_posts_with_cred') && $current_user->ID == $post->post_author)
        {
                //return '<strong>'.__('Do not have permission (delete own)','wp-cred').'</strong>';
                return '';
        }
        if (!current_user_can('delete_other_posts_with_cred') && $current_user->ID != $post->post_author)
        {
                //return '<strong>'.__('Do not have permission (delete other)','wp-cred').'</strong>';
                return '';
        }

        if ($post_id===false || empty($post_id) || !isset($post_id) || !is_numeric($post_id))
        {
            if (!isset($post->ID))
                return '<strong>'.__('No post specified','wp-cred').'</strong>';
            else
                $post_id=$post->ID;
        }

        $post_id=intval($post_id);
        $text=str_replace(array('%TITLE%','%ID%'),array(get_the_title($post_id),$post_id),$text);

        $link_id='_cred_cred_'.$post_id.'_'.++$idcount.'_'.rand(1,10);
        $_wpnonce=wp_create_nonce($link_id.'_'.$action);
        $link=cred_ajax_route('cred-ajax-delete-post&cred_post_id='.$post_id.'&cred_action='.$action.'&_wpnonce='.$_wpnonce);

        $_atts=array();
        if (!empty($class))
            $_atts[]='class="'.esc_attr(str_replace('"',"'",$class)).'"';
        if (!empty($style))
            $_atts[]='style="'.esc_attr(str_replace('"',"'",$style)).'"';

        return CRED_Loader::renderTemplate('delete-post-link', array(
            'link' => $link,
            'text'=> $text,
            'link_id'=>$link_id,
            'link_atts'=>(!empty($_atts))?implode(' ',$_atts):false,
            'include_js'=>($idcount==1)
        ));
    }

/**
 * CRED-Shortcode: cred-delete-post-link
 *
 * Description: Display a link to delete a post
 *
 * Parameters:
 * 'action'=> either 'trash' (sent post to Trash) or 'delete' (completely delete post)
 * 'post' => [optional] Post ID of post to delete (if post is omitted then current post_id will be used, for example inside Loop)
 * 'text'=> [optional] Text to use for link (can use meta-variables like %TITLE% and %ID%)
 * 'class'=> [optional] css class to apply to link
 * 'style'=> [optional] css style to apply to link
 *
 * Example usage:
 *
 *  Display link for deleting car custom post with ID 145
 * [cred-delete-post-link post="145" text="Delete this car"]
 *
 * There is also a php tag to use in templates and themes that has the same functionality as the shortcode
 * <?php cred_delete_post_link($post_id, $text, $action, $class, $style); ?>
 *
 **/
    public static function credDeletePostLinkShortcode($atts)
    {
        global $post,$current_user;

        $params=shortcode_atts( array(
            'post'=>'',
            'text'=>'',
            'action'=>'',
            'class'=>'',
            'style'=>''
        ), $atts );

       return self::cred_delete_post_link($params['post'],$params['text'],$params['action'],$params['class'],$params['style']);
    }

    public static function cred_edit_post_link($form, $post_id=false, $text='', $class='', $style='', $target='', $attributes='')
    {
        global $post,$current_user;

        if (empty($form))
            return '<strong>'.__('No form specified','wp-cred').'</strong>';

        if ($post_id===false || empty($post_id) || !isset($post_id) || !is_numeric($post_id))
        {
            if (!isset($post->ID))
                return '<strong>'.__('No post specified','wp-cred').'</strong>';
            else
                $post_id=$post->ID;
        }

        $post_id=intval($post_id);
        if (!is_numeric($form))
        {
            $form_post=get_page_by_title($form, OBJECT, CRED_FORMS_CUSTOM_POST_NAME);
            if (!$form_post)
                return '<strong>'.__('Form does not exist','wp-cred').'</strong>';
            $form=$form_post->ID;
        }
        else
            $form=intval($form);

        if (!current_user_can('edit_own_posts_with_cred_'.$form) && $current_user->ID == $post->post_author)
        {
                //return '<strong>'.__('Do not have permission (edit own with this form)','wp-cred').'</strong>';
                return '';
        }
        if (!current_user_can('edit_other_posts_with_cred_'.$form) && $current_user->ID != $post->post_author)
        {
                //return '<strong>'.__('Do not have permission (edit other with this form)','wp-cred').'</strong>';
                return '';
        }

        $link=get_permalink($post_id);
        $link=add_query_arg( array('cred-edit-form'=>$form), $link );
        $text=str_replace(array('%TITLE%','%ID%'),array(get_the_title($post_id),$post_id),$text);

        $_atts=array();
        if (!empty($class))
            $_atts[]='class="'.esc_attr(str_replace('"',"'",$class)).'"';
        if (!empty($style))
            $_atts[]='style="'.esc_attr(str_replace('"',"'",$style)).'"';
        if (!empty($target))
            $_atts[]='target="'.esc_attr(str_replace('"',"'",$target)).'"';
        if (!empty($attributes))
        {
            $_atts[]=str_replace(array('%eq%','%dbquo%','%quot%'),array("=",'"',"'"),$attributes);
        }
        return "<a href='{$link}' ".implode(' ',$_atts).">".$text."</a>";
    }

    public static function cred_child_link_form($form, $parent_id=null, /*$parent_type='',*/ $text='', $class='', $style='', $target='', $attributes='')
    {
        global $post;

        if (empty($form) || !is_numeric($form))
            return '<strong>'.__('No Child Form Page specified','wp-cred').'</strong>';

        $form=intval($form);

        $link=get_permalink($form);

        if ($parent_id!==null)
        {
            $parent_id=intval($parent_id);

            if ($parent_id<0 /*&& $post->post_type==$parent_type*/)
                $parent_id=$post->ID;
            /*elseif ($parent_id<0)
                $parent_id=null;*/
        }

        if ($parent_id!==null)
        {
            $parent_type=get_post_type( $parent_id );
            if ($parent_type===false)
                return __('Unknown Parent Type','wp-cred');
            $link=add_query_arg( array('parent_'.$parent_type.'_id'=>$parent_id), $link );
        }

        $_atts=array();
        if (!empty($class))
            $_atts[]='class="'.esc_attr(str_replace('"',"'",$class)).'"';
        if (!empty($style))
            $_atts[]='style="'.esc_attr(str_replace('"',"'",$style)).'"';
        if (!empty($target))
            $_atts[]='target="'.esc_attr(str_replace('"',"'",$target)).'"';
        if (!empty($attributes))
        {
            $_atts[]=str_replace(array('%eq%','%dbquo%','%quot%'),array("=",'"',"'"),$attributes);
        }
        return "<a href='{$link}' ".implode(' ',$_atts).">".$text."</a>";
    }

/**
 * CRED-Shortcode: cred-link-form
 *
 * Description: Display a link to edit a post with given form
 *
 * Parameters:
 * 'form' => Form Title or Form ID of form to use.
 * 'post' => [optional] Post ID of post to edit with this form (if post is omitted then current post_id will be used, for example inside Loop)
 * 'text'=> [optional] Text to use for link (can use meta-variables like %TITLE% and %ID%)
 * 'class'=> [optional] css class to apply to link
 * 'style'=> [optional] css style to apply to link
 * 'target'=> [optional] open link in the specific target (_blank,_self,_top)
 * 'attributes'=> [optional] additional html attrubutes (eg onclick)
 *
 * Example usage:
 *
 *  Display link for editing car custom post with ID 145 (use form with title "Edit Car")
 * [cred-link-form form="Edit Car" post="145" text="Edit this car"]
 *
 * There is also a php tag to use in templates and themes that has the same functionality as the shortcode
 * <?php cred_edit_post_link($form, $post_id, $text, $class, $style, $target, $attributes); ?>
 *
 **/
    public static function credFormLinkShortcode($atts)
    {
        global $post;

        $params=shortcode_atts( array(
            'form' => '',
            'post'=>'',
            'text'=>'',
            'class'=>'',
            'style'=>'',
            'target'=>'',
            'attributes'=>''
        ), $atts );

        return self::cred_edit_post_link($params['form'],$params['post'],$params['text'],$params['class'],$params['style'],$params['target'],$params['attributes']);
    }

/**
 * CRED-Shortcode: cred-child-link-form
 *
 * Description: Display a link to create a child post with given form and parent
 *
 * Parameters:
 * 'form' => Page ID containing teh child form.
 * 'parent_type' => Post Type of Parent
 * 'parent_id' => [optional] Parent to set for the child
 * 'text'=> [optional] Text to use for link
 * 'class'=> [optional] css class to apply to link
 * 'style'=> [optional] css style to apply to link
 * 'target'=> [optional] open link in the specific target (_blank,_self,_top)
 * 'attributes'=> [optional] additional html attrubutes (eg onclick)
 *
 * Example usage:
 *
 *  Display link for editing car custom post with ID 145 (use form with title "Edit Car")
 * [cred-child-link-form form="New  Review" parent="145" parent_type='book' text="Add new Review"]
 *
 *
 **/
    public static function credChildFormLinkShortcode($atts)
    {
        global $post;

        $params=shortcode_atts( array(
            'form' => null,
            /*'parent_type' => null,*/
            'parent_id' => -1,
            'text'=> '',
            'class'=> '',
            'style'=> '',
            'target'=> '_self',
            'attributes'=> ''
        ), $atts );

        return self::cred_child_link_form($params['form'],$params['parent_id']/*,$params['parent_type']*/,$params['text'],$params['class'],$params['style'],$params['target'],$params['attributes']);
    }

    public static function cred_form($form, $post_id=false)
    {
        global $post;

        if (empty($form))
            return '<strong>'.__('No form specified','wp-cred').'</strong>';

        if (empty($post_id) || $post_id===false || !is_numeric($post_id))
        {
            if (isset($post->ID))
                $post_id=$post->ID;
        }

        // prevent recursion if form shortcode inside posts content
        remove_shortcode('cred-form',array('CRED_CRED', 'credFormShortcode'));
        $output = self::getForm($form, false, $post_id);
        add_shortcode('cred-form',array('CRED_CRED', 'credFormShortcode'));
        return $output;
    }

/**
 * CRED-Shortcode: cred-form
 *
 * Description: Display a CRED form
 *
 * Parameters:
 * 'form' => Form Title or Form ID of form to display.
 * 'post' => [optional] Post ID of post to edit with this form (if form is an edit form, if post is omitted and form is an edit form, then current post_id will be used, for example inside Loop)
 *
 * Example usage:
 *
 *  Display form for editing car custom post with ID 145 (use form with title "Edit Car")
 * [cred-form form="Edit Car" post="145"]
 *  Display form to create a car post (use form with title "Create Car")
 * [cred-form form="Create Car"]
 *  Display form with ID 120
 * [cred-form form="120"]
 *
 * There is also a php tag to use in templates and themes that has the same functionality as the shortcode
 * <?php cred_form($form,$post); ?>
 *
 **/
    public static function credFormShortcode($atts)
    {
        global $post;

        $params=shortcode_atts( array(
            'form' => '',
            'post'=>'',
        ), $atts );

        return self::cred_form($params['form'],$params['post']);
    }

    // display actual form according to ID or Title
    public static function getForm($form, $preview=false, $post=null)
    {
        CRED_Loader::load('CLASS/Form_Builder');
        return CRED_Form_Builder::getForm($form,$post,$preview);
    }


    public static function cred_admin_head()
    {
        global $pagenow, $post_type;
        // setup css js
        if
            (
               ($pagenow=='post.php' && $post_type==CRED_FORMS_CUSTOM_POST_NAME) ||
               ($pagenow=='post-new.php' && isset($_GET['post_type']) && $_GET['post_type']==CRED_FORMS_CUSTOM_POST_NAME)
            )
        {
            wp_dequeue_script('autosave');
            wp_deregister_script('autosave');

            wp_enqueue_style('cred_codemirror_style', CRED_ASSETS_URL.'/third-party/codemirror234/lib/codemirror.css',null,CRED_FE_VERSION);
            wp_enqueue_script('cred_codemirror_js', CRED_ASSETS_URL.'/third-party/codemirror234/lib/codemirror.js',null,CRED_FE_VERSION);
            wp_enqueue_script('cred_codemirror_util_overlay', CRED_ASSETS_URL.'/third-party/codemirror234/lib/util/overlay.js',array('cred_codemirror_js'),CRED_FE_VERSION);
            wp_enqueue_script('cred_codemirror_mode_xml', CRED_ASSETS_URL.'/third-party/codemirror234/mode/xml/xml.js',array('cred_codemirror_js'),CRED_FE_VERSION);
            wp_enqueue_script('cred_codemirror_mode_js', CRED_ASSETS_URL.'/third-party/codemirror234/mode/javascript/javascript.js',array('cred_codemirror_js'),CRED_FE_VERSION);
            wp_enqueue_script('cred_codemirror_mode_css', CRED_ASSETS_URL.'/third-party/codemirror234/mode/css/css.js',array('cred_codemirror_js'),CRED_FE_VERSION);
            wp_enqueue_script('cred_codemirror_mode_html', CRED_ASSETS_URL.'/third-party/codemirror234/mode/htmlmixed/htmlmixed.js',array('cred_codemirror_js'),CRED_FE_VERSION);
        }

        if (defined('CRED_DEV')&&CRED_DEV)
        {
            wp_enqueue_script('cred_cred', CRED_ASSETS_URL.'/js/cred_dev.js', array('jquery','jquery-effects-scale'),CRED_FE_VERSION);
            //wp_enqueue_script('cred_suggest', CRED_ASSETS_URL.'/js/cred_suggest.js', array('cred_cred'),CRED_FE_VERSION);
        }
        else
        {
            wp_enqueue_script('cred_cred', CRED_ASSETS_URL.'/js/cred.js', array('jquery','jquery-effects-scale'),CRED_FE_VERSION);
        }
        // inline js settings and config
        add_action('wp_print_scripts', array('CRED_CRED', 'inlineJsSettings'));
        wp_enqueue_style('cred_cred_style', CRED_ASSETS_URL.'/css/cred.css',null,CRED_FE_VERSION);
    }



    // js configuration for admin section
    public static function inlineJsSettings()
    {
        // Translations
        // insert into javascript
        wp_localize_script( 'cred_cred', 'cred_cred_config', array(
            'settings' => array(
                'assets' => CRED_ASSETS_URL
            ),
            'title_explain_text' => __('Set the title for this new form.', 'wp-cred'),
            'content_explain_text' => __('Build the form using HTML and CRED shortcodes. Click on the Scaffold button to auto-generate the form with default fields. Use the Insert Post Fields button to add fields that belong to this post type, or Insert Generic Fields to add any other inputs.', 'wp-cred'),
            'next_text' => __('Next','wp-cred'),
            'prev_text' => __('Previous','wp-cred'),
            'finish_text' => __('Finish','wp-cred'),
            'quit_wizard_text' => __('Exit Wizard Mode','wp-cred'),
            'quit_wizard_confirm_text' => sprintf(__('Do you want to disable the Wizard for this form only, or disable the Wizard for all future forms as well? <br /><br /><span style="font-style:italic">(You can re-enable the Wizard at the %s Settings Page if you change your mind)</span>','wp-cred'),CRED_NAME),
            'quit_wizard_all_forms' => __('All forms','wp-cred'),
            'quit_wizard_this_form' => __('This form','wp-cred'),
            'cancel_text' =>  __('Cancel','wp-cred'),
            'form_type_missing' => __('You must select the form type for the form','wp-cred'),
            'post_type_missing' => __('You must select a post type for the form','wp-cred'),
            'ok_text' => __('OK','wp-cred'),
            'step_1_title' => __('Title','wp-cred'),
            'step_2_title' => __('Settings','wp-cred'),
            'step_3_title' => __('Post Type','wp-cred'),
            'step_4_title' => __('Build Form','wp-cred'),
            'step_5_title' => __('Notifications','wp-cred'),
            'submit_but' => __('Update','wp-cred'),
            'form_content' => __('Form Content','wp-cred'),
            'form_fields' => __('Form Fields','wp-cred'),
            'post_fields' => __('Standard Post Fields','wp-cred'),
            'custom_fields' => __('Custom Fields','wp-cred'),
            'taxonomy_fields' => __('Taxonomies','wp-cred'),
            'parent_fields' => __('Parents','wp-cred'),
            'extra_fields' => __('Extra Fields','wp-cred'),
            'form_types_not_set'=>__('Form Type or Post Type is not set!'),
            'set_form_title' => __('Please set the form Title','wp-cred'),
            'create_new_content_form'=>__('(Create a new-content form first)','wp-cred'),
            'create_edit_content_form'=>__('(Create an edit-content form first)','wp-cred'),
            'show_advanced_options'=>__('Show advanced options','wp-cred'),
            'hide_advanced_options'=>__('Hide advanced options','wp-cred'),
            'select_form'=>__('Please select a form first','wp-cred'),
            'select_post' => __('Please select a post first','wp-cred'),
            'insert_post_id' => __('Please insert a valid post ID','wp-cred'),
            'insert_shortcode'=> __('Click to insert the specified shortcode','wp-cred'),
            'select_shortcode'=>__('Please select a shortcode first','wp-cred'),
            'post_types_dont_match'=>__('This post type is incompatible with the selected form','wp-cred'),
            'post_status_must_be_public'=>__('In order to display the post, post status must be set to Publish','wp-cred'),
            'refresh_done'=>__('Refresh Complete','wp-cred'),
            'enable_popup_for_preview'=>__('You have to enable popup windows in order for Preview to work!','wp-cred'),
            'show_syntax_highlight'=> __('Enable Syntax Highlight','wp-cred'),
            'hide_syntax_highlight'=> __('Revert to default editor','wp-cred'),
            'syntax_highlight_on'=> __('Syntax Highlight On','wp-cred'),
            'syntax_highlight_off'=> __('Syntax Highlight Off','wp-cred'),
            'invalid_title'=>__('Title should contain only letters, numbers and underscores/dashes','wp-cred')
            ));
            wp_localize_script( 'cred_cred', 'cred_cred_help', self::$help);
    }


   // js used in form create and edit admin pages
   public static function jsForCredCustomPost()
   {
        global $post;

        if ((isset($post) && !empty($post) && $post->post_type==CRED_FORMS_CUSTOM_POST_NAME) || (isset($_GET['post_type']) && $_GET['post_type']==CRED_FORMS_CUSTOM_POST_NAME))
        {
            $newform=false;
            if ((isset($_GET['post_type']) && $_GET['post_type']==CRED_FORMS_CUSTOM_POST_NAME))
                $newform=true;

            $sm = CRED_Loader::get('MODEL/Settings');
            $settings = $sm->getSettings();

            // include msgBox
            wp_enqueue_style('msgbox', CRED_ASSETS_URL.'/third-party/msgBox/jquery.msgbox.css',null,CRED_FE_VERSION);
            wp_enqueue_script('msgbox', CRED_ASSETS_URL.'/third-party/msgBox/jquery.msgbox.min.js',array('jquery'),CRED_FE_VERSION);

            $add_wizard=false;
            if ($settings['wizard'])
            {
                $fm = CRED_Loader::get('MODEL/Forms');
                $wizard = $fm->getFormCustomField($post->ID,'wizard');
                if ($wizard==false || $wizard==null || $wizard=='-1') $wizard=-1;
                $wizard=intval($wizard);
                if ($wizard!=0 && $newform) $wizard=0;

                if ($wizard>=0)
                    $add_wizard=true;

                if ($add_wizard)
                {
                    // include wizard
                    if (defined('CRED_DEV')&&CRED_DEV)
                        wp_enqueue_script('cred_wizard', CRED_ASSETS_URL.'/js/wizard_dev.js', array('jquery'),CRED_FE_VERSION);
                    else
                        wp_enqueue_script('cred_wizard', CRED_ASSETS_URL.'/js/wizard.js', array('jquery'),CRED_FE_VERSION);
                }
            }
            if (isset($settings['syntax_highlight']) && $settings['syntax_highlight'])
                $syntaxhi='true';
            else
                $syntaxhi='false';
            ?>
            <script type='text/javascript'>
                /*<![CDATA[ */
                jQuery(function(){
                 // add syntax highlight button
                 if (window.QTags)
                    QTags.addButton(
                        'cred_syntax_highlight',
                        '<?php _e('Syntax Highlight Off','wp-cred'); ?>',
                        '',
                        '',
                        '',
                        '<?php _e('Enable Syntax Highlight','wp-cred'); ?>',
                        800
                    );
                   cred_cred.form_post.init(
                        "<?php echo home_url('/'); ?>",
                        "<?php echo cred_route(); ?>",
                        "<?php echo cred_route('/Forms/getPostFields'); ?>",
                        "<?php echo self::$settingsPage; ?>", <?php echo $syntaxhi; ?>
                    );
            <?php
            if ($add_wizard)
            { ?>
                    cred_cred_wizard.init(
                        "<?php echo cred_route('/Settings/disableWizard'); ?>",
                        "<?php echo admin_url('post.php'); ?>",
                        "<?php echo cred_route('/Forms'); ?>",
                        <?php echo $wizard; ?>,
                        <?php if ($newform) echo 'true'; else echo 'false'; ?>
                        );
            <?php } ?>
                });
                /*]]>*/
            </script>
            <?php
        }
        else
        {
            ?>
            <script type='text/javascript'>
                /*<![CDATA[ */
                jQuery(function(){
                    cred_cred.post.init(
                        "<?php echo cred_route(); ?>"
                    );
                });
                /*]]>*/
            </script>
            <?php
        }
   }

    public static function forcePrivateforForms($post)
    {
        if ($post['post_type'] != CRED_FORMS_CUSTOM_POST_NAME)
            return $post;

        $post['post_status'] = 'private';
        return $post;
    }

   // when form is submitted from admin, save the custom fields which describe the form configuration to DB
   public static function saveFormCustomFields($post_id,$post)
   {
		global $wpdb;

        if ($post->post_type!=CRED_FORMS_CUSTOM_POST_NAME) return;

        if (!current_user_can( 'edit_post', $post_id ) ) return;

        if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if (wp_is_post_revision( $post_id ) ) return;

        // hook not called from admin edit page, return
        if (empty($_POST) || !isset($_POST['cred-admin-post-page-field']) || !wp_verify_nonce($_POST['cred-admin-post-page-field'],'cred-admin-post-page-action'))  return;

        $form_type = isset($_POST['cred_form_type'])?$_POST['cred_form_type']:'';
        $form_action = isset($_POST['cred_form_success_action'])?$_POST['cred_form_success_action']:'';
        $form_action_page = isset($_POST['cred_form_success_action_page'])?$_POST['cred_form_success_action_page']:'';
        $redirect_delay = isset($_POST['cred_form_redirect_delay'])?intval($_POST['cred_form_redirect_delay']):0;
        $message = isset($_POST['cred_form_action_message'])?$_POST['cred_form_action_message']:'';
        $hide_comments = isset($_POST['cred_form_hide_comments'])&&$_POST['cred_form_hide_comments']?1:0;
        $include_captcha_scaffold = isset($_POST['cred_include_captcha_scaffold'])&&$_POST['cred_include_captcha_scaffold']?1:0;
        $include_wpml_scaffold = isset($_POST['cred_include_wpml_scaffold'])&&$_POST['cred_include_wpml_scaffold']?1:0;
        $post_type = isset($_POST['cred_post_type'])?$_POST['cred_post_type']:'';
        $post_status = isset($_POST['cred_post_status'])?$_POST['cred_post_status']:'draft';
        $cred_theme_css = (isset($_POST['cred_theme_css']))?$_POST['cred_theme_css']:'minimal';
        $has_media_button = (isset($_POST['cred_content_has_media_button'])&&$_POST['cred_content_has_media_button'])?1:0;
        $extra_css=isset($_POST['cred-extra-css-editor'])?$_POST['cred-extra-css-editor']:'';
        $extra_js=isset($_POST['cred-extra-js-editor'])?$_POST['cred-extra-js-editor']:'';
        $settings=new stdClass;
        $settings->form_type=$form_type;
        $settings->form_action=$form_action;
        $settings->form_action_page=$form_action_page;
        $settings->redirect_delay=$redirect_delay;
        $settings->message=$message;
        $settings->hide_comments=$hide_comments;
        $settings->include_captcha_scaffold=$include_captcha_scaffold;
        $settings->include_wpml_scaffold=$include_wpml_scaffold;
        $settings->post_type=$post_type;
        $settings->post_status=$post_status;
        $settings->cred_theme_css=$cred_theme_css;
        $settings->has_media_button=$has_media_button;
        $forms_model = CRED_Loader::get('MODEL/Forms');
        $forms_model->updateFormCustomField($post_id,'form_settings',$settings);
        if (array_key_exists(self::$prefix.'wizard',$_POST))
        {
            $forms_model->updateFormCustomField($post_id,'wizard',intval($_POST[self::$prefix.'wizard']));
        }
        $mail_to_type_arr = isset($_POST['cred_mail_to_where_selector'])?(array)$_POST['cred_mail_to_where_selector']:array();
        $mail_to_user_arr = isset($_POST['cred_mail_to_user'])?(array)$_POST['cred_mail_to_user']:array();
        $mail_to_field_arr = isset($_POST['cred_mail_to_field'])?(array)$_POST['cred_mail_to_field']:array();
        $mail_to_specific_arr = isset($_POST['cred_mail_to_specific'])?(array)$_POST['cred_mail_to_specific']:array();
        $subject_arr = isset($_POST['cred_mail_subject'])?(array)$_POST['cred_mail_subject']:array();
        $body_arr = isset($_POST['cred_mail_body'])?(array)$_POST['cred_mail_body']:array();
        $notification=new stdClass;
        $notification->notifications=array();
        foreach (array_keys($mail_to_type_arr) as $ii)
        {
            $tmp=array();
            $tmp['mail_to_type']=isset($mail_to_type_arr[$ii])?$mail_to_type_arr[$ii]:'';
            $tmp['mail_to_user']=isset($mail_to_user_arr[$ii])?$mail_to_user_arr[$ii]:'';
            $tmp['mail_to_field']=isset($mail_to_field_arr[$ii])?$mail_to_field_arr[$ii]:'';
            $tmp['mail_to_specific']=isset($mail_to_specific_arr[$ii])?$mail_to_specific_arr[$ii]:'';
            $tmp['subject']=isset($subject_arr[$ii])?$subject_arr[$ii]:'';
            $tmp['body']=isset($body_arr[$ii])?$body_arr[$ii]:'';
            $notification->notifications[]=$tmp;
        }
        if (isset($_POST['cred_notification_enable']) && $_POST['cred_notification_enable']=='1')
            $notification->enable=1;
        //else
            //$notification->enable=false;
        $forms_model->updateFormCustomField($post_id,'notification',$notification);

        $extra=new stdClass;
        $extra->css=$extra_css;
        $extra->js=$extra_js;
        $messages=CRED_Loader::get('MODEL/Forms')->getDefaultMessages();
        foreach (array_keys($messages) as $msgid)
        {
            if (isset($_POST[$msgid]))
            {
                $messages[$msgid]['msg']=$_POST[$msgid];
            }
        }
        $extra->messages=$messages;
        $forms_model->updateFormCustomField($post_id,'extra',$extra);

        if ($post->post_status=='publish')
        {
            $post->post_status='private';
        }

        // if WMPL string is active, process form content for strings in shortcode attributes for translation
        if (self::check_wpml_string())
        {
            CRED_Loader::load('CLASS/Form_Processor');
            $cfp=new CRED_Form_Processor($post->ID, $post->post_title);
            // register strings in shortcode values
            $cfp->processFormForStrings($post->post_content, 'Value: ');
            // register form title
            $cfp->registerString('Form Title: '.$post->post_title, $post->post_title);
            $cfp->registerString('Display Message: '.$post->post_title, $message);
            // register Notification Data also
            foreach ($notification->notifications as $ii=>$nott)
            {
                switch($nott['mail_to_type'])
                {
                    case 'wp_user':
                        $cfp->registerString('CRED Notification '.$ii.' Mail To', $nott['mail_to_user']);
                        break;
                    case 'specific_mail':
                        $cfp->registerString('CRED Notification '.$ii.' Mail To', $nott['mail_to_specific']);
                        break;
                    default:
                        break;
                }
                $cfp->registerString('CRED Notification '.$ii.' Subject', $nott['subject']);
                $cfp->registerString('CRED Notification '.$ii.' Body', $nott['body']);
            }
            // register messages also
            foreach ($extra->messages as $msgid=>$msg)
            {
                $cfp->registerString('Message_'.$msgid, $msg['msg']);
            }
        }
        else
        {
            update_option(self::$cred_wpml_option,'no');
        }
   }

   // add meta boxes in admin pages which manipulate forms
   public static function addMetaBoxes($post)
   {
        global $pagenow;

        if (CRED_FORMS_CUSTOM_POST_NAME==$post->post_type)
        {
            $forms_model = CRED_Loader::get('MODEL/Forms');
            $settings = $forms_model->getFormCustomField($post->ID,'form_settings');
            $notification = $forms_model->getFormCustomField($post->ID,'notification');
            $extra = $forms_model->getFormCustomField($post->ID,'extra');
            // form type meta box
            add_meta_box('credformtypediv',__('Form Settings','wp-cred'),array('CRED_CRED', 'addFormSettingsMetaBox'),null,'normal','high',array($settings));
            // post type meta box
            add_meta_box('credposttypediv',__('Post Type Settings','wp-cred'),array('CRED_CRED', 'addPostTypeMetaBox'),null,'normal','high',array($settings));
            // post type meta box
            add_meta_box('credextradiv',__('CSS and JS for this form','wp-cred'),array('CRED_CRED', 'addExtraAssetsMetaBox'),null,'normal','high',array($extra));
            // email notification meta box
            add_meta_box('crednotificationdiv',__('Notification Settings','wp-cred'),array('CRED_CRED', 'addNotificationMetaBox'),null,'normal','high',array($notification));
            // messages meta box
            add_meta_box('credmessagesdiv',__('Form Texts','wp-cred'),array('CRED_CRED', 'addMessagesMetaBox'),null,'normal','high',array($extra));

            if (defined('MODMAN_PLUGIN_NAME') && 'post-new.php'!=$pagenow) // dont add module manager meta box on post-new.php page
            {
                // module manager sidebar meta box
                add_meta_box('modulemanagerdiv',__('Module Manager','wp-cred'),array('CRED_CRED', 'addModManMetaBox'),null,'side','default',array());
            }
        }
   }

   // functions to display actual meta boxes (better to use templates here.., done using template snipetts to separate the code a bit)
   public static function addModManMetaBox($post)
   {
        $element=array('id'=>_CRED_MODULE_MANAGER_KEY_.$post->ID, 'title'=>$post->post_title, 'section'=>_CRED_MODULE_MANAGER_KEY_);
        do_action('wpmodules_inline_element_gui',$element);
   }

   public static function addFormSettingsMetaBox($post,$settings)
   {
        $settings=$settings['args'][0];
        $form_type=isset($settings['form_type'])?$settings['form_type']:'';
        $form_action=isset($settings['form_action'])?$settings['form_action']:'';
        $form_action_page=isset($settings['form_action_page'])?$settings['form_action_page']:'';
        $redirect_delay=isset($settings['redirect_delay'])?intval($settings['redirect_delay']):0;
        $message=isset($settings['message'])?$settings['message']:'';
        $hide_comments=isset($settings['hide_comments'])?$settings['hide_comments']:1;
        $cred_theme_css=isset($settings['cred_theme_css'])?$settings['cred_theme_css']:'minimal';
        $page_query = new WP_Query(array('post_type'=>'page', 'post_status'=>'publish', 'posts_per_page'=>-1));
        ob_start();
        if ($page_query->have_posts())
        {
            while ($page_query->have_posts())
            {
                $page_query->the_post();
                ?>
                <option value="<?php the_ID() ?>" <?php if ($form_action_page==get_the_ID()) echo 'selected="selected"'; ?>><?php the_title(); ?></option>
                <?php
            }
        }
        $form_action_pages=ob_get_clean();
        echo CRED_Loader::renderTemplate('form-settings-meta-box',array(
                'form_type'=>$form_type,
                'form_action'=>$form_action,
                'form_action_pages'=>$form_action_pages,
                'redirect_delay'=>$redirect_delay,
                'message'=>$message,
                'hide_comments'=>$hide_comments,
                'cred_theme_css'=>$cred_theme_css,
                'cred_themes'=>array('minimal'=>__('Basic Theme','wp-cred'),'styled'=>__('Styled Theme','wp-cred')),
                'help'=>self::$help,
                'help_target'=>self::$help_link_target
        ));
   }

   public static function addPostTypeMetaBox($post,$settings)
   {
        $settings=$settings['args'][0];
        $post_type=isset($settings['post_type'])?$settings['post_type']:'';
        $post_status=isset($settings['post_status'])?$settings['post_status']:'';
        $has_media_button=isset($settings['has_media_button'])?$settings['has_media_button']:false;
        echo CRED_Loader::renderTemplate('post-type-meta-box',array(
                'post_types'=>CRED_Loader::get('MODEL/Fields')->getPostTypes(),
                'post_type'=>$post_type,
                'post_status'=>$post_status,
                'has_media_button'=>$has_media_button,
                'help'=>self::$help,
                'help_target'=>self::$help_link_target
        ));
   }

   public static function addNotificationMetaBox($post,$settings)
   {
        $notification=$settings['args'][0];
        $enable=(isset($notification['enable'])&&$notification['enable'])?'checked="checked"':'';
        $notts = isset($notification['notifications'])?(array)$notification['notifications']:array();

        $users = self::getUsersByRole('administrator,editor'); // take administrator and editor user roles
        echo CRED_Loader::renderTemplate('notification-meta-box',array(
                    'users'=>$users,
                    'enable'=>$enable,
                    'notifications'=>$notts,
                    'help'=>self::$help,
                    'help_target'=>self::$help_link_target
        ));
   }

   public static function addExtraAssetsMetaBox($post,$extra)
   {
        $extra=$extra['args'][0];

        echo CRED_Loader::renderTemplate('extra-meta-box',array(
                    'css'=>isset($extra['css'])?$extra['css']:'',
                    'js'=>isset($extra['js'])?$extra['js']:'',
                    'help'=>self::$help,
                    'help_target'=>self::$help_link_target
        ));
   }

   public static function addMessagesMetaBox($post,$extra)
   {
        $extra=$extra['args'][0];
        if (isset($extra['messages']))
            $messages=$extra['messages'];
        else
            $messages=CRED_Loader::get('MODEL/Forms')->getDefaultMessages();

        echo CRED_Loader::renderTemplate('text-settings-meta-box',array(
            'messages'=>$messages
        ));
   }

   // add CRED button in 3rd-party (eg Views)
   public static function addCREDButton($v, $area)
   {
        $fm=CRED_Loader::get('MODEL/Forms');
        $forms=$fm->getFormsForTable(0,-1);

        $shortcode_but='';
        $shortcode_but = CRED_Loader::renderTemplate('insert-form-shortcode-button-extra',array(
                'forms'=>$forms,
                'help'=>self::$help,
                'content'=>$area,
                'help_target'=>self::$help_link_target
        ));

        $out=$shortcode_but;

        return $out;
   }

   // function to handle the media buttons associated to forms, like  Scaffold,Insert Shortcode, etc..
   public static function addFormsButton($context, $text_area = 'textarea#content')
   {
        global $wp_version,$post;
        //static $add_only_once=0;

        if (!isset($post) || empty($post) /*|| $post->post_type!=CRED_FORMS_CUSTOM_POST_NAME*/) return '';

        if ($post->post_type==CRED_FORMS_CUSTOM_POST_NAME)
        {
            // WP 3.3 changes ($context arg is actually a editor ID now)
            if (version_compare($wp_version, '3.1.4', '>') && !empty($context))
            {
                $text_area = $context;
            }

            $addon_buttons = array();
            $shortcode_but='';
            $shortcode_but = CRED_Loader::renderTemplate('insert-field-shortcode-button',array(
                'help'=>self::$help,
                'help_target'=>self::$help_link_target

            ));

            $shortcode2_but='';
            $fm=CRED_Loader::get('MODEL/Fields');
            $shortcode2_but = CRED_Loader::renderTemplate('insert-generic-field-shortcode-button',array(
                'gfields'=>$fm->getTypesDefaultFields(),
                'url'=>cred_route('/Generic_Fields/getField'),
                'help'=>self::$help,
                'help_target'=>self::$help_link_target
            ));

            $forms_model = CRED_Loader::get('MODEL/Forms');
            $settings = $forms_model->getFormCustomField($post->ID,'form_settings');
            $scaffold_but='';
            $scaffold_but = CRED_Loader::renderTemplate('scaffold-button',array(
                'include_captcha_scaffold'=>isset($settings['include_captcha_scaffold'])?$settings['include_captcha_scaffold']:false,
                'include_wpml_scaffold'=>isset($settings['include_wpml_scaffold'])?$settings['include_wpml_scaffold']:false,
                'help'=>self::$help,
                'help_target'=>self::$help_link_target
            ));

            $preview_but='';
            $preview_but = CRED_Loader::renderTemplate('preview-form-button');

            //$syntax_highlight_but='';
            //$syntax_highlight_but = CRED_Loader::renderTemplate('syntax-highlight-button');
            //$addon_buttons[] = $syntax_highlight_but;
            $addon_buttons[] = $scaffold_but;
            $addon_buttons[] = $shortcode_but;
            $addon_buttons[] = $shortcode2_but;
            $addon_buttons[] = $preview_but;
            $addon_buttons=implode('&nbsp;',$addon_buttons);

            $out=$addon_buttons;

            // WP 3.3 changes
            if (version_compare($wp_version, '3.1.4', '>'))
            {
                echo $out;
            }
            else
            {
                return $context . $out;
            }
        }
        else
        {
            if (is_string($context) && 'content'!=$context) // allow button only on main area
            {
                $out='';//self::addCREDButton('', $context);
                // WP 3.3 changes
                if (version_compare($wp_version, '3.1.4', '>'))
                {
                    echo $out;
                    return;
                }
                else
                {
                    return $context.$out;
                }
            }
            $fm=CRED_Loader::get('MODEL/Forms');
            $forms=$fm->getFormsForTable(0,-1);

            // WP 3.3 changes ($context arg is actually a editor ID now)
            if (version_compare($wp_version, '3.1.4', '>') && !empty($context))
            {
                $text_area = $context;
            }

            $addon_buttons = array();
            $shortcode_but='';
            $shortcode_but = CRED_Loader::renderTemplate('insert-form-shortcode-button',array(
                    'forms'=>$forms,
                    'help'=>self::$help,
                    'help_target'=>self::$help_link_target
            ));
            $addon_buttons[] = $shortcode_but;

            $addon_buttons=implode('&nbsp;',$addon_buttons);

            $out=$addon_buttons;

            // WP 3.3 changes
            if (version_compare($wp_version, '3.1.4', '>'))
            {
                echo $out;
            }
            else
            {
                return $context . $out;
            }
        }
   }

    // setup necessary DB model settings
    public static function prepareDB()
    {
        $forms_model = CRED_Loader::get('MODEL/Forms');
        $forms_model->prepareDB();

        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings_model->prepareDB();
    }

    // setup CRED menus in admin
    public static function addMenuItems()
    {
		$menu_label = CRED_NAME; //__( 'CRED','wp-cred' );

        $url = 'post-new.php?post_type='.CRED_FORMS_CUSTOM_POST_NAME;

        $cred_index = 'CRED_Forms'; //CRED_VIEWS_PATH2.'/forms.php';
	    add_menu_page($menu_label, $menu_label, 'manage_options', $cred_index, array('CRED_CRED', 'FormsMenuPage'), CRED_ASSETS_URL .'/images/cred_18x18_color.png');
        add_submenu_page($cred_index, __( 'Forms','wp-cred' ), __( 'Forms','wp-cred' ), CRED_CAPABILITY, 'CRED_Forms', array('CRED_CRED', 'FormsMenuPage'));
        add_submenu_page($cred_index, __( 'New Form','wp-cred' ), __( 'New Form','wp-cred' ),CRED_CAPABILITY, $url);
        add_submenu_page($cred_index, __( 'Custom Fields','wp-cred' ), __( 'Custom Fields','wp-cred' ),CRED_CAPABILITY,'CRED_Fields', array('CRED_CRED', 'FieldsMenuPage'));
        add_submenu_page($cred_index, __( 'Settings/Import','wp-cred' ), __( 'Settings/Import','wp-cred' ),CRED_CAPABILITY,'CRED_Settings', array('CRED_CRED', 'SettingsMenuPage'));
        add_submenu_page($cred_index, __( 'Help','wp-cred' ), __( 'Help','wp-cred' ),CRED_CAPABILITY,'CRED_Help', array('CRED_CRED', 'HelpMenuPage'));

        self::$screens=array($cred_index, CRED_VIEWS_PATH2.'/custom_fields.php');
        foreach (self::$screens as $screen)
        {
            add_action("load-".$screen, array('CRED_CRED','add_screen_options'));
        }
        //add_filter('set-screen-option', array('CRED_CRED', 'set_screen_option'), 10, 3);
    }

    public static function FormsMenuPage()
    {
        CRED_Loader::load('VIEW/forms');
    }

    public static function FieldsMenuPage()
    {
        CRED_Loader::load('VIEW/custom_fields');
    }

    public static function SettingsMenuPage()
    {
        CRED_Loader::load('VIEW/settings');
    }

    public static function HelpMenuPage()
    {
        CRED_Loader::load('VIEW/help');
    }

    public static function set_screen_option($status, $option, $value)
    {
        if ( 'cred_per_page' == $option ) return $value;
    }

    // add screen options to table screens
    public static function add_screen_options()
    {
        $screen = get_current_screen();

        // get out of here if we are not on our settings page
        if(!is_array(self::$screens) || !in_array($screen->id.'.php', self::$screens))
            return;

        /*$value=$screen->get_option('per_page','default');
        if (null===$value)
            $value=10;*/
        $value=10;
        if (isset($_REQUEST['wp_screen_options']))
        {
            if (isset($_REQUEST['wp_screen_options']['option']) && 'cred_per_page'==$_REQUEST['wp_screen_options']['option']
                && isset($_REQUEST['wp_screen_options']['value'])
            )
            $value=intval($_REQUEST['wp_screen_options']['value']);
        }
        elseif (isset($_REQUEST['per_page']))
            $value=intval($_REQUEST['per_page']);

        $args = array(
            'label' => __('Per Page', 'wp-cred'),
            'default' => $value,
            'option' => 'cred_per_page'
        );
        add_screen_option( 'per_page', $args );

        // instantiate table now to take care of column options
        switch($screen->id)
        {
            case CRED_VIEWS_PATH2.'/forms':
                CRED_Loader::get('TABLE/Forms');
                break;
            case CRED_VIEWS_PATH2.'/custom_fields':
                CRED_Loader::get('TABLE/Custom_Fields');
                break;
        }
    }

    // auxiliary function
    private function getUsersByRole( $roles )
    {
        global $wpdb;
        if ( ! is_array( $roles ) ) {
            $roles = explode( ",", $roles );
            array_walk( $roles, 'trim' );
        }
        $sql = '
            SELECT  u.ID, u.display_name, u.user_email
            FROM        ' . $wpdb->users . ' AS u INNER JOIN ' . $wpdb->usermeta . ' AS um
            ON      u.ID  = um.user_id
            WHERE   um.meta_key     =       \'' . $wpdb->prefix . 'capabilities\'
            AND     (
        ';
        $i = 1;
        foreach ( $roles as $role ) {
            $sql .= ' um.meta_value LIKE    \'%"' . $role . '"%\' ';
            if ( $i < count( $roles ) ) $sql .= ' OR ';
            $i++;
        }
        $sql .= ' ) ';
        $sql .= ' ORDER BY u.display_name ';
        $users = $wpdb->get_results( $sql );
        return $users;
    }
}
?>