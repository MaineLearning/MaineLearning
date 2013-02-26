<?php
/**************************************************

Cred fields model

(get custom fields for post types)
**************************************************/

class CRED_Fields_Model extends CRED_Abstract_Model
{

    private $_custom_posts_option='__CRED_CUSTOM_POSTS';
    private $_custom_fields_option='__CRED_CUSTOM_FIELDS';

/**
 * Class constructor
 */     
    public function __construct()
    {
        parent::__construct();
        
    }

    
	public function getTypesDefaultFields($_custom=false)
    {
        if (!$_custom)
        {
            return array(
                'checkbox'=> array ( 'title'=>__('Checkbox','wp-cred'), 'type' => 'checkbox', 'parameters'=>array('name'=>true,'default'=>true)),
                'checkboxes'=> array ( 'title'=>__('Checkboxes','wp-cred'), 'type' => 'checkboxes', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),
                'select'=> array ( 'title'=>__('Select','wp-cred'), 'type' => 'select', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),
                'multiselect'=> array ( 'title'=>__('Multi Select','wp-cred'), 'type' => 'multiselect', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),
                'radio'=> array ( 'title'=>__('Radio','wp-cred'), 'type' => 'radio', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),
                'date'=> array ( 'title'=>__('Date','wp-cred'), 'type' => 'date', 'parameters'=>array('name'=>true,'default'=>true,'format'=>true)),
                'email'=> array ( 'title'=>__('Email','wp-cred'), 'type' => 'email', 'parameters'=>array('name'=>true,'default'=>true)),
                'url'=> array ( 'title'=>__('URL','wp-cred'), 'type' => 'url', 'parameters'=>array('name'=>true,'default'=>true)),
                'skype'=> array ( 'title'=>__('Skype','wp-cred'), 'type' => 'skype', 'parameters'=>array('name'=>true,'skypename'=>true,'style'=>true)),
                'phone'=> array ( 'title'=>__('Phone','wp-cred'), 'type' => 'phone', 'parameters'=>array('name'=>true,'default'=>true)),
                'textfield'=> array ( 'title'=>__('Single Line','wp-cred'), 'type' => 'textfield', 'parameters'=>array('name'=>true,'default'=>true)),
                'hidden'=> array ( 'title'=>__('Hidden','wp-cred'), 'type' => 'hidden', 'parameters'=>array('name'=>true,'default'=>true)),
                'password'=> array ( 'title'=>__('Password','wp-cred'), 'type' => 'password', 'parameters'=>array('name'=>true)),
                'textarea'=> array ( 'title'=>__('Multiple Lines','wp-cred'), 'type' => 'textarea', 'parameters'=>array('name'=>true,'default'=>true)),
                'wysiwyg'=> array ( 'title'=>__('WYSIWYG','wp-cred'), 'type' => 'wysiwyg', 'parameters'=>array('name'=>true,'default'=>true)),
                'numeric'=> array ( 'title'=>__('Numeric','wp-cred'), 'type' => 'numeric', 'parameters'=>array('name'=>true,'default'=>true)),
                'file'=> array ( 'title'=>__('File','wp-cred'), 'type' => 'file', 'parameters'=>array('name'=>true)),
                'image'=> array ( 'title'=>__('Image','wp-cred'), 'type' => 'image', 'parameters'=>array('name'=>true)),
            );
        }
        else
        {
            return array(
                'checkbox'=> array ( 'title'=>__('Checkbox','wp-cred'), 'type' => 'checkbox', 'parameters'=>array('name'=>true,'default'=>true)),
                'checkboxes'=> array ( 'title'=>__('Checkboxes','wp-cred'), 'type' => 'checkboxes', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),
                'select'=> array ( 'title'=>__('Select','wp-cred'), 'type' => 'select', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),
                /*'multiselect'=> array ( 'title'=>__('Multi Select','wp-cred'), 'type' => 'multiselect', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),*/
                'radio'=> array ( 'title'=>__('Radio','wp-cred'), 'type' => 'radio', 'parameters'=>array('name'=>true,'options'=>true,'labels'=>true,'default'=>true)),
                'date'=> array ( 'title'=>__('Date','wp-cred'), 'type' => 'date', 'parameters'=>array('name'=>true,'default'=>true,'format'=>true)),
                'email'=> array ( 'title'=>__('Email','wp-cred'), 'type' => 'email', 'parameters'=>array('name'=>true,'default'=>true)),
                'url'=> array ( 'title'=>__('URL','wp-cred'), 'type' => 'url', 'parameters'=>array('name'=>true,'default'=>true)),
                'skype'=> array ( 'title'=>__('Skype','wp-cred'), 'type' => 'skype', 'parameters'=>array('name'=>true,'skypename'=>true,'style'=>true)),
                'phone'=> array ( 'title'=>__('Phone','wp-cred'), 'type' => 'phone', 'parameters'=>array('name'=>true,'default'=>true)),
                'textfield'=> array ( 'title'=>__('Single Line','wp-cred'), 'type' => 'textfield', 'parameters'=>array('name'=>true,'default'=>true)),
                'hidden'=> array ( 'title'=>__('Hidden','wp-cred'), 'type' => 'hidden', 'parameters'=>array('name'=>true,'default'=>true)),
                'password'=> array ( 'title'=>__('Password','wp-cred'), 'type' => 'password', 'parameters'=>array('name'=>true)),
                'textarea'=> array ( 'title'=>__('Multiple Lines','wp-cred'), 'type' => 'textarea', 'parameters'=>array('name'=>true,'default'=>true)),
                'wysiwyg'=> array ( 'title'=>__('WYSIWYG','wp-cred'), 'type' => 'wysiwyg', 'parameters'=>array('name'=>true,'default'=>true)),
                'numeric'=> array ( 'title'=>__('Numeric','wp-cred'), 'type' => 'numeric', 'parameters'=>array('name'=>true,'default'=>true)),
                'file'=> array ( 'title'=>__('File','wp-cred'), 'type' => 'file', 'parameters'=>array('name'=>true)),
                'image'=> array ( 'title'=>__('Image','wp-cred'), 'type' => 'image', 'parameters'=>array('name'=>true)),
            );
        }
    }
    
    public function suggestPostsByTitle($text, $post_type=null, $limit=20)
    {
        $post_status="('publish','private')";
        $not_in_post_types="('view','view-template','attachment','revision','".CRED_FORMS_CUSTOM_POST_NAME."')";
        $text=esc_sql(like_escape($text));
        $sql="SELECT ID, post_title FROM {$this->wpdb->posts} WHERE post_title LIKE '%$text%' AND post_status IN $post_status AND post_type NOT IN $not_in_post_types";
        if ($post_type!==null)
            $sql.= $this->wpdb->prepare(' AND post_type="%s"',$post_type);
        $limit=intval($limit);
        if ($limit>0)
            $sql.=" LIMIT 0, $limit";
        
        $results=$this->wpdb->get_results($sql);
        
        return $results;
    }
    
    public function getPostTypes($custom_exclude=array())
    {
        $exclude=array('revision','attachment','nav_menu_item');
        if (!empty($custom_exclude))
            $exclude=array_merge($exclude,$custom_exclude);
        
        $post_types=get_post_types(array('public'=>true,'publicly_queryable'=>true,'show_ui'=>true),'names'); 
        $default_post_types=get_post_types(array('public'=>true,'_builtin' => true, ),'names','and'); 
        $post_types=array_merge($post_types,$default_post_types);
        $post_types=array_diff($post_types,$exclude);
        sort($post_types,SORT_STRING);
        return $post_types;
    }
    
    public function getPostTypesWithoutTypes()
    {
        $wpcf_custom_types=get_option('wpcf-custom-types', false);
        if ($wpcf_custom_types)
            return $this->getPostTypes(array_keys($wpcf_custom_types));
        else
            return $this->getPostTypes();
    }
    
    public function getPostTypeCustomFields($post_type, $exclude_fields=array(), $show_private=true, $paged, $perpage=10, $orderby='meta_key', $order='asc')
    {
        /*
            TODO:
                make search incremental to avoid large data issues
        */
        
        $exclude=array('_edit_last','_edit_lock','_wp_old_slug','_thumbnail_id','_wp_page_template');
        if (!empty($exclude_fields))
            $exclude=array_merge($exclude,$exclude_fields);
        
        $exclude="'" . implode("','", $exclude) . "'"; //wrap in quotes
        
        if ($paged<0)
        {
            if ($show_private)
                $sql=$this->wpdb->prepare("
                SELECT COUNT(DISTINCT(pm.meta_key)) FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p 
                WHERE 
                    pm.post_id=p.ID
                AND
                    p.post_type=%s
                AND pm.meta_key NOT IN ({$exclude})
                ", $post_type);
            else
                $sql=$this->wpdb->prepare("
                SELECT COUNT(DISTINCT(pm.meta_key)) FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p 
                WHERE 
                    pm.post_id=p.ID
                AND
                    p.post_type=%s
                AND pm.meta_key NOT IN ({$exclude})
                AND pm.meta_key NOT LIKE '%s'
                ", $post_type, "\_%");
            
            return $this->wpdb->get_var($sql);
        }
        $paged=intval($paged);
        $perpage=intval($perpage);
        $paged--;
        $order=strtoupper($order);
        if (!in_array($order,array('ASC','DESC')))
            $order='ASC';
        if (!in_array($orderby,array('meta_key')))
            $orderby='meta_key';
            
        if ($show_private)
            $sql=$this->wpdb->prepare("
            SELECT DISTINCT(pm.meta_key) FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p 
            WHERE 
                pm.post_id=p.ID
            AND
                p.post_type=%s
            AND pm.meta_key NOT IN ({$exclude})
            ORDER BY pm.{$orderby} {$order}
            LIMIT ".($paged*$perpage).", ".$perpage
            , $post_type);
        else
            $sql=$this->wpdb->prepare("
            SELECT DISTINCT(pm.meta_key) FROM {$this->wpdb->postmeta} as pm, {$this->wpdb->posts} as p 
            WHERE 
                pm.post_id=p.ID
            AND
                p.post_type=%s
            AND pm.meta_key NOT IN ({$exclude})
            AND pm.meta_key NOT LIKE '%s'
            ORDER BY pm.{$orderby} {$order}
            LIMIT ".($paged*$perpage).", ".$perpage
            , $post_type, "\_%");
        
        $fields=$this->wpdb->get_col($sql);
        
        return $fields;
    }
    
    public function getCustomFields($post_type=null)
    {
        $custom_fields=get_option($this->_custom_fields_option, false);
        
        if ($post_type!==null)
        {
            if ($custom_fields && !empty($custom_fields) && isset($custom_fields[$post_type]))
                return $custom_fields[$post_type];
            return array();
        }
        else
        {
            if ($custom_fields && !empty($custom_fields))
                return $custom_fields;
            return array();
        }
    }
    
    public function saveCustomFields($fields)
    {
        update_option($this->_custom_fields_option, $fields);
    }
    
    public function setCustomField($fielddata=null)
    {
        if ($fielddata!==null && isset($fielddata['post_type']))
        {
            $post_type=$fielddata['post_type'];
            $field= array ( 
                'id' => $fielddata['name'],
                'post_type'=>$fielddata['post_type'],
                'cred_custom'=>true, 
                'slug' => $fielddata['name'], 
                'type' => $fielddata['type'], 
                'name' => $fielddata['name'], 
                'data' => array ( 
                        'repetitive' => 0, 
                        'validate' => array ( 
                            'required' => array ( 
                                'active' => isset($fielddata['required']), 
                                'value' => isset($fielddata['required']), 
                                'message' => __('This Field is required','wp-cred') 
                            )
                        ), 
                        'validate_format'=>isset($fielddata['validate_format'])
                    ) 
            );
            
            if (!isset($fielddata['include_scaffold']))
                $field['_cred_ignore']=true;
                
            switch($fielddata['type'])
            {
                case 'checkbox':
                    $field['data']['set_value']=$fielddata['default'];
                    break;
                case 'checkboxes':
                    $field['data']['options']=array();
                    if (!isset($fielddata['options']['value']))
                    {
                        $fielddata['options']=array('value'=>array(),'label'=>array(),'option_default'=>array());
                    }
                    foreach ($fielddata['options']['value'] as $ii=>$option)
                    {
                        $option_id=$option;
                        //$option_id=$atts['field'].'_option_'.$ii;
                        $field['data']['options'][$option_id]=array(
                            'title' => $fielddata['options']['label'][$ii],
                            'set_value' => $option
                        );
                        if (isset($fielddata['options']['option_default']) && in_array($option,$fielddata['options']['option_default']))
                        {
                            $field['data']['options'][$option_id]['checked']=true;
                        }
                    }
                    break;
                case 'date':
                    $field['data']['validate']['date']=array(
                        'active' => isset($fielddata['validate_format']),
                        'format' => 'mdy',
                        'message' => __('Please enter a valid date','wp-cred')
                    );
                    break;
                case 'radio':
                case 'select':
                    $field['data']['options']=array();
                    $default_option='no-default';
                    if (!isset($fielddata['options']['value']))
                    {
                        $fielddata['options']=array('value'=>array(),'label'=>array(),'option_default'=>'');
                    }
                    foreach ($fielddata['options']['value'] as $ii=>$option)
                    {
                        $option_id=$option;
                        //$option_id=$atts['field'].'_option_'.$ii;
                        $field['data']['options'][$option_id]=array(
                            'title' => $fielddata['options']['label'][$ii],
                            'value' => $option,
                            'display_value' => $option
                        );
                        if (isset($fielddata['options']['option_default']) && !empty($fielddata['options']['option_default']) && $fielddata['options']['option_default']==$option)
                            $default_option=$option_id;
                    }
                    $field['data']['options']['default'] = $default_option;
                    break;
                case 'email':
                    $field['data']['validate']['email']=array(
                        'active' => isset($fielddata['validate_format']),
                        'message' => __('Please enter a valid email address','wp-cred')
                    );
                    break;
                case 'numeric':
                    $field['data']['validate']['number']=array(
                        'active' => isset($fielddata['validate_format']),
                        'message' => __('Please enter numeric data','wp-cred')
                    );
                    break;
                case 'url':
                    $field['data']['validate']['url']=array(
                        'active' => isset($fielddata['validate_format']),
                        'message' => __('Please enter a valid URL address','wp-cred')
                    );
                    break;
                default:
                    break;
            }
            $custom_fields=get_option($this->_custom_fields_option);
            
            if ($custom_fields && !empty($custom_fields) && isset($custom_fields[$post_type]))
            {
                if (is_array($custom_fields[$post_type]))
                    $custom_fields[$post_type][$fielddata['name']]=$field;
                else
                {
                    $custom_fields[$post_type]=array($fielddata['name']=>$field);
                }
            }
            else
            {
                $custom_fields=array();
                $custom_fields[$post_type]=array($fielddata['name']=>$field);
            }
            update_option($this->_custom_fields_option,$custom_fields);
        }
    }
    
    public function getCustomField($post_type, $field_name, $typesformat=false)
    {
        $custom_fields=$this->getCustomFields($post_type);
        if (isset($custom_fields[$field_name]))
        {
            if ($typesformat)
                return $custom_fields[$field_name];
            else
            {
                $fielddata=$custom_fields[$field_name];
                $field=array(
                    'post_type' => $post_type,
                    'name' => $field_name,
                    'type' => $fielddata['type'],
                    'required' => isset($fielddata['data']['validate']['required']['active'])&&$fielddata['data']['validate']['required']['active'],
                    'validate_format'=>isset($fielddata['data']['validate_format'])&&$fielddata['data']['validate_format'],
                    'include_scaffold'=>(bool)(!isset($fielddata['_cred_ignore'])||!$fielddata['_cred_ignore'])
                );
                switch($fielddata['type'])
                {
                    case 'checkbox':
                        $field['default']=$fielddata['data']['set_value'];
                        break;
                    case 'checkboxes':
                        $field['options']=array('value'=>array(),'label'=>array(),'option_default'=>array());
                        foreach ($fielddata['data']['options'] as $ii=>$option)
                        {
                            $field['options']['value'][]=$option['set_value'];
                            $field['options']['label'][]=$option['title'];
                            if (isset($option['checked']) && $option['checked'])
                                $field['options']['option_default'][]=$option['set_value'];
                        }
                        break;
                    case 'radio':
                    case 'select':
                        $field['options']=array('value'=>array(),'label'=>array(),'option_default'=>'');
                        foreach ($fielddata['data']['options'] as $ii=>$option)
                        {
                            if ($ii=='default') continue;
                            $field['options']['value'][]=$option['value'];
                            $field['options']['label'][]=$option['title'];
                            if (isset($fielddata['data']['options']['default']) && $option['value']==$fielddata['data']['options']['default'])
                                $field['options']['option_default']=$option['value'];
                        }
                        break;
                    default:
                        break;
                }
                return $field;
            }
        }
        else
            return array();
    }
    
    public function ignoreCustomFields($post_type, $field_names, $action='ignore')
    {
        $custom_fields=$this->getCustomFields();
        /*print_r($custom_fields);
        print_r($field_names);
        print_r($post_type);*/
        if (!$custom_fields || !isset($custom_fields[$post_type])) return;
        
        $cfieldnames=array_keys($custom_fields[$post_type]);
        foreach ($field_names as $f)
        {
            
            if (in_array($f,$cfieldnames))
            {
                switch($action)
                {
                    case 'ignore':
                            $custom_fields[$post_type][$f]['_cred_ignore']=true;
                        break;
                    case 'unignore':
                            unset($custom_fields[$post_type][$f]['_cred_ignore']);
                        break;
                    case 'reset':
                            unset($custom_fields[$post_type][$f]);
                        break;
                }
            }
        }
        $this->saveCustomFields($custom_fields);
    }
    
    
    public function getFields($post_type,$add_default=true)
	{
        if (empty($post_type) || $post_type==null || $post_type==false)
            return array();
        //if (function_exists('wpcf_get_post_meta_field_names')) {
        
        // ALL FIELDS
        $fields_all=array();
        // default post types
        $default_post_types=array('post','page');
        
        // POST FIELDS
        $fields=array();
        $groups=array();
		$post_type_orig=$post_type;
        $post_type='%'.$post_type.'%';
		$wpcf_custom_types=get_option('wpcf-custom-types');
        //cred_log(print_r($wpcf_custom_types,true));
        $isTypesPost=($wpcf_custom_types)?array_key_exists($post_type_orig, $wpcf_custom_types):false;
        $credCustomFields=$this->getCustomFields($post_type_orig);
        $isCredCustomPost=(bool)(!empty($credCustomFields));
        
        if ($isTypesPost || in_array($post_type_orig, $default_post_types))
        {
            //cred_log(print_r($wpcf_custom_types,true));
            $sql = 'SELECT post_id FROM '.$this->wpdb->postmeta.' WHERE meta_key="_wp_types_group_post_types" AND (meta_value LIKE "%s" OR meta_value="all") ORDER BY post_id ASC';
            $post_ids=$this->wpdb->get_col($this->wpdb->prepare($sql,$post_type));
            $post_ids=implode(',',$post_ids);
            //cred_log(print_r($post_ids,true));
            if (empty($post_ids))
            {
                $groups=array();
                $fields=array();
            }
            else
            {
                $sql = 'SELECT P.post_title, M.meta_value FROM '.$this->wpdb->posts.' As P, '.$this->wpdb->postmeta.' As M 
                WHERE P.ID IN ('.$post_ids.') 
                AND M.post_id=P.ID
                AND M.meta_key="_wp_types_group_fields"
                AND NOT (M.meta_value IS NULL)
                ORDER BY ID ASC';
                $group_fields=$this->wpdb->get_results($sql);
                $cc=count($group_fields);
                //cred_log(array($post_ids, $group_fields));
                $fieldnames=array();
                for ($ii=0; $ii<$cc; $ii++)
                {
                    $groups[$group_fields[$ii]->post_title]=trim($group_fields[$ii]->meta_value,' ,');
                    $fieldnames[]=$group_fields[$ii]->meta_value;
                }
                unset($group_fields);
                
                $fieldnames=str_replace(',,',',',trim(implode('',$fieldnames),' ,'));
                $fields = get_option('wpcf-fields');
                //cred_log($fields);
                $field_names=/*wpcf_get_post_meta_field_names();*/explode(',',$fieldnames);
                foreach ($fields as $key=>$field)
                {
                    if (!in_array($key,$field_names))
                    {
                        unset($fields[$key]);
                    }
                }
                
                $plugin='types';
                foreach ($fields as $key=>$field)
                {
                    $fields[$key]['post_type']=$post_type_orig;
                    $fields[$key]['plugin_type']=$plugin;
                    if (isset($fields[$key]['data'])&&isset($fields[$key]['data']['controlled'])&&$fields[$key]['data']['controlled'])
                        // fields simply controlled by types do not have prefix, but original name
                        $fields[$key]['plugin_type_prefix']='';
                    else
                        // native types fields have prefix
                        $fields[$key]['plugin_type_prefix']='wpcf-';
                }
            }
        }
        // add additional cred custom fields
        if ($isCredCustomPost)
        {
            $fields=array_merge($fields, $credCustomFields);
            foreach ($credCustomFields as $f=>$fdata)
            {
                if (!isset($fdata['_cred_ignore']) || !$fdata['_cred_ignore'])
                {
                    $groups['_CRED_Custom_Fields_']=implode(',',array_keys($credCustomFields));
                    // has at least one field not ingored  from scaffold
                    break;
                }
            }
        }
        
		$post_fields=array();
        //cred_log(print_r($wpcf_custom_types[$post_type_orig],true));
        
        if ($add_default)
		{
			$post_fields['post_title']= array ( 'post_type'=>$post_type_orig,'id' => 'post_title', 'wp_default'=>true, 'slug' => 'post_title', 'type' => 'textfield', 'name' => 'Post Title', 'description' => __('Title of Post (default)','wp-cred'), 'data' => array ( 'repetitive' => 0, 'validate' => array ( 'required' => array ( 'active' => 1, 'value' => true, 'message' => __('This Field is required','wp-cred') ) ), 'conditional_display' => array ( ), 'disabled_by_type' => 0 ) );
			$post_fields['post_content']= array ( 'post_type'=>$post_type_orig,'id' => 'post_content', 'wp_default'=>true, 'slug' => 'post_content', 'type' => 'wysiwyg', 'name' => 'Post Content', 'description' => __('Content of Post (default)','wp-cred'), 'data' => array ( /*'repetitive' => 0, 'validate' => array ( 'required' => array ( 'active' => 1, 'value' => true, 'message' => __('This Field is required','wp-cred') ) ), 'conditional_display' => array ( ), 'disabled_by_type' => 0 */) );
			$post_fields['post_excerpt']= array ( 'post_type'=>$post_type_orig,'id' => 'post_excerpt', 'wp_default'=>true, 'slug' => 'post_excerpt', 'type' => 'textarea', 'name' => 'Post Excerpt', 'description' => __('Excerpt of Post (default)','wp-cred'), 'data' => array ( /*'repetitive' => 0, 'validate' => array ( 'required' => array ( 'active' => 1, 'value' => true, 'message' => __('This Field is required','wp-cred') ) ), 'conditional_display' => array ( ), 'disabled_by_type' => 0 */) );
            
            //cred_log('POST :'.$post_type_orig);
            //cred_log(print_r($wpcf_custom_types,true));
            if (
                /*!$isTypesPost || (
                array_key_exists('supports',$wpcf_custom_types[$post_type_orig]) 
                && array_key_exists('editor',$wpcf_custom_types[$post_type_orig]['supports']) 
                && $wpcf_custom_types[$post_type_orig]['supports']['editor'])*/
                post_type_supports($post_type_orig, 'editor')
            )
            {
                $post_fields['post_content']['supports']=true;
            }
            else
            {
                $post_fields['post_content']['supports']=false;
            }
            if (
                /*!$isTypesPost || (
                array_key_exists('supports',$wpcf_custom_types[$post_type_orig]) 
                && array_key_exists('excerpt',$wpcf_custom_types[$post_type_orig]['supports']) 
                && $wpcf_custom_types[$post_type_orig]['supports']['excerpt'])*/
                post_type_supports($post_type_orig, 'excerpt')
            )
            {
                $post_fields['post_excerpt']['supports']=true;
            }
            else
            {
                $post_fields['post_excerpt']['supports']=false;
            }
		}
        
        $parents=array();
        
        // add parent fields
        if ($isTypesPost)
        {
            if (
            array_key_exists('post_relationship',$wpcf_custom_types[$post_type_orig]) &&
            array_key_exists('belongs',$wpcf_custom_types[$post_type_orig]['post_relationship'])
            )
            {
                
                foreach ($wpcf_custom_types[$post_type_orig]['post_relationship']['belongs'] as $ptype=>$belong)
                {
                    if ($belong)
                    {
                        $_slug='_wpcf_belongs_'.$ptype.'_id';
                        $parents[$_slug]=array('is_parent'=>true,'plugin_type'=>'types','data'=>array('post_type'=>$ptype,'repetitive'=>false,'options'=>array()),'id'=>$_slug,'slug'=>$_slug,'name'=>$ptype.' Parent','type'=>'select','description'=>sprintf(__('Set the %s Parent','wp-cred'),$ptype));
                    }
                }
            }
            // hierarchical custom post type, parent of itself
            /*if (isset($wpcf_custom_types[$post_type_orig]['hierarchical']) && $wpcf_custom_types[$post_type_orig]['hierarchical'])
            {
                $_slug='post_parent';
                $ptype=$post_type_orig;
                $parents[$_slug]=array('is_parent'=>true,'data'=>array('post_type'=>$ptype,'repetitive'=>false,'options'=>array()),'id'=>$_slug,'slug'=>$_slug,'name'=>$ptype.' Parent','type'=>'select','description'=>sprintf(__('Set the %s Parent','wp-cred'),$ptype));
            }*/
        }
        if (
            /*$post_type_orig=='page'*/
            post_type_supports($post_type_orig, 'page-attributes')
        )
        {
            $_slug='post_parent';
            $ptype=$post_type_orig;
            $parents[$_slug]=array('is_parent'=>true,'data'=>array('post_type'=>$ptype,'repetitive'=>false,'options'=>array()),'id'=>$_slug,'slug'=>$_slug,'name'=>$ptype.' Parent','type'=>'select','description'=>sprintf(__('Set the %s Parent','wp-cred'),$ptype));
        }
        
        //}
        
        //cred_log(print_r($fields,true));
        
        // EXTRA FIELDS
        $extra_fields=array();
        $extra_fields['recaptcha']=array('id'=>'re_captcha','slug'=>'recaptcha','name'=>'reCaptcha','type'=>'recaptcha','cred_builtin'=>true,'description'=>__('Adds Image Captcha to your forms to prevent automatic submision by bots','wp-cred'));
        $setts=CRED_Loader::get('MODEL/Settings')->getSettings();
        if (
            !isset($setts['recaptcha']['public_key']) ||
            !isset($setts['recaptcha']['private_key']) ||
            empty($setts['recaptcha']['public_key']) ||
            empty($setts['recaptcha']['private_key'])
        )
        {
        // no keys set for API
            $extra_fields['recaptcha']['disabled']=true;
            $extra_fields['recaptcha']['disabled_reason']=sprintf('<a href="%s" target="_blank">%s</a> %s',CRED_CRED::$settingsPage,__('Get and Enter your API keys','wp-cred'),__('to use the Captcha field.','wp-cred'));
        }
        /*else
            $extra_fields['recaptcha']['disabled']=false;*/
        
        // featured image field
        $extra_fields['_featured_image']=array('id'=>'_featured_image','slug'=>'_featured_image','name'=>'Featured Image','type'=>'image','cred_builtin'=>true,'description'=>__('Set Post Featured Image','wp-cred'));
        if (
            /*!$isTypesPost || (
            array_key_exists('supports',$wpcf_custom_types[$post_type_orig]) 
            && array_key_exists('thumbnail',$wpcf_custom_types[$post_type_orig]['supports']) 
            && $wpcf_custom_types[$post_type_orig]['supports']['thumbnail'])*/
            post_type_supports($post_type_orig, 'thumbnail')
        )
        {
            $extra_fields['_featured_image']['supports']=true;
        }
        else
        {
            $extra_fields['_featured_image']['supports']=false;
        }
        
        // BASIC FORM FIELDS
        $form_fields=array();
        $form_fields['form']=array('id'=>'credform','name'=>'Form Container','slug'=>'credform','type'=>'credform','cred_builtin'=>true,'description'=>__('Form (required)','wp-cred','wp-cred'));
        //$form_fields['form_end']=array('id'=>'form_end','name'=>'Form End','slug'=>'form_end','type'=>'form_end','cred_builtin'=>true,'description'=>__('End of Form'));
        $form_fields['form_submit']=array('value'=>__('Submit','wp-cred'),'id'=>'form_submit','name'=>'Form Submit','slug'=>'form_submit','type'=>'form_submit','cred_builtin'=>true,'description'=>__('Form Submit Button','wp-cred'));
        $form_fields['form_messages']=array('value'=>'','id'=>'form_messages','name'=>'Form Messages','slug'=>'form_messages','type'=>'form_messages','cred_builtin'=>true,'description'=>__('Form Messages Container','wp-cred'));
        
        // TAXONOMIES FIELDS
        // get post type taxonomies
        $all_taxonomies=get_taxonomies(array(  
                                        'public'   => true,
                                        '_builtin' => false,
                                        //'object_type'=>array($post_type_orig)
                                        ),'objects','or');
        //$all_taxonomies=get_object_taxonomies($post_type_orig);
        $taxonomies=array();
        foreach($all_taxonomies as $tax)
        {
            $taxonomy=&$tax;
            //$taxonomy = get_taxonomy($tax);
            if (!in_array($post_type_orig,$taxonomy->object_type)) continue;
            if (in_array($taxonomy->name,array('post_format'))) continue;

            $key=$taxonomy->name;
            $taxonomies[$key]=array(
                                        'type'=>($taxonomy->hierarchical)?'taxonomy_hierarchical':'taxonomy_plain',
                                        'label'=>$taxonomy->label,
                                        'name'=>$taxonomy->name,
                                        'hierarchical'=>$taxonomy->hierarchical
                                        );
            if ($taxonomy->hierarchical)
            {
                $taxonomies[$key]['all']= $this->buildTerms(get_terms($taxonomy->name,array('hide_empty'=>0,'fields'=>'all')));

            }
            else
            {
                $taxonomies[$key]['most_popular']= $this->buildTerms(get_terms($taxonomy->name,array('number'=>8,'order_by'=>'count','fields'=>'all')));
            }
        }
        unset($all_taxonomies);
        
        $fields_all['groups']=$groups;
        $fields_all['form_fields']=$form_fields;
        $fields_all['post_fields']=$post_fields;
        $fields_all['custom_fields']=$fields;
        $fields_all['taxonomies']=$taxonomies;
        $fields_all['parents']=$parents;
        $fields_all['extra_fields']=$extra_fields;
        $fields_all['form_fields_count']=count($form_fields);
        $fields_all['post_fields_count']=count($post_fields);
        $fields_all['custom_fields_count']=count($fields);
        $fields_all['taxonomies_count']=count($taxonomies);
        $fields_all['parents_count']=count($parents);
        $fields_all['extra_fields_count']=count($extra_fields);
        
        //cred_log(print_r($fields_all,true));
        return $fields_all;
	}
	
    public function getPotentialParents($post_type, $post_id=null, $results=0, $order='date', $ordering='desc')
    {
        $post_status="('publish','private')";
        
        $order=in_array($order,array('date','title'))?$order:'date';
        $ordering=in_array($ordering,array('asc','desc'))?$ordering:'desc';
        
        if ($order=='date')
            $order='post_date';
        else
            $order='post_title';
            
        $ordering=strtoupper($ordering);

        if (!is_numeric($results) || is_nan($results))
            $results=0;
        else $results=intval($results);
        
        $sql=array(
        'fields'=>array('ID','post_parent','post_title'),
        'from'=>array($this->wpdb->posts),
        'where'=>array("(post_type='%s')","(post_status IN ".$post_status.")"),
        'order'=>array($order, $ordering),
        'limit'=>array(0, $results)
        );
        if (!is_numeric($post_id) || $post_id===null)
            $sql['where'][]="(ID<>%d)";
         
        $sql = sprintf('SELECT %s FROM %s WHERE %s ORDER BY %s',
            implode(',',$sql['fields']),
            implode(',',$sql['from']),
            implode(' AND ',$sql['where']),
            implode(' ',$sql['order'])
        );
        if ($results>0)
            $sql.= ' LIMIT 0,'.$results;
            
        $sql = $this->wpdb->prepare($sql, $post_type, $post_id);
        
        $parents=$this->wpdb->get_results($sql);
        
        return $parents;
    }
    
    private function buildTerms(&$obj_terms)
    {
        $tax_terms=array();
        foreach ($obj_terms as $term)
        {
            $tax_terms[]=array(
                                'name'=>$term->name,
                                'count'=>$term->count,
                                'parent'=>$term->parent,
                                'term_taxonomy_id'=>$term->term_taxonomy_id,
                                'term_id'=>$term->term_id
            );
        }
        return $tax_terms;
    }
	
    public function getAllFields()
	{
		return get_option('wpcf-fields');
	}
	
}
?>