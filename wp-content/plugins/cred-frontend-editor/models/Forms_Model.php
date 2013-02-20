<?php
/**************************************************

Cred form model

(uses custom posts and fields to store form data)
**************************************************/

class CRED_Forms_Model extends CRED_Abstract_Model
{

	private $post_type_name='';
    private $form_meta_fields=array('form_settings','wizard','notification','extra');
    private $prefix='_cred_';

/**
 * Class constructor
 */     
    public function __construct()
    {
        parent::__construct();
        
        $this->post_type_name = CRED_FORMS_CUSTOM_POST_NAME;
    } 
	
	public function prepareDB()
	{
		$this->register_form_type();
	}
	
    public function getDefaultMessages()
    {
        static $messages=false;
        
        if (!$messages)
        {
            $messages=array(
                /*'cred_message_add_new_repeatable_field' =>  array(
                    'msg'=>'Add Another',
                    'desc'=>__('Add another repetitive field','wp-cred')
                    ),
                'cred_message_remove_repeatable_field'   =>  array(
                    'msg'=>'Remove',
                    'desc'=>__('Remove repetitive field','wp-cred')
                    ),
                'cred_message_cancel_upload_text' => array(
                    'msg'=>'Retry Upload',
                    'desc'=>__('Retry Upload file/image','wp-cred')
                    ),*/
                'cred_message_post_saved'=>array(
                    'msg'=>'Post Saved',
                    'desc'=>__('Post saved message','wp-cred')
                    ),
                'cred_message_post_not_saved'=>array(
                    'msg'=>'Post Not Saved',
                    'desc'=>__('Post not saved message','wp-cred')
                    ),
                'cred_message_notification_was_sent'=>array(
                    'msg'=>'Notification was sent',
                    'desc'=>__('Notification sent message','wp-cred')
                    ),
                'cred_message_notification_failed'=>array(
                    'msg'=>'Notification failed',
                    'desc'=>__('Notification failed message','wp-cred')
                    ),
                'cred_message_invalid_form_submission'=>array(
                    'msg'=>'Invalid Form Submission (nonce failure)',
                    'desc'=>__('Invalid submission message','wp-cred')
                    ),
                'cred_message_upload_failed'=>array(
                    'msg'=>'Upload Failed',
                    'desc'=>__('Upload failed message','wp-cred')
                    ),
                'cred_message_field_required'=>array(
                    'msg'=>'This Field is required',
                    'desc'=>__('Required field message','wp-cred')
                    ),
                'cred_message_enter_valid_date'=>array(
                    'msg'=>'Please enter a valid date',
                    'desc'=>__('Invalid date message','wp-cred')
                    ),
                'cred_message_values_do_not_match'=>array(
                    'msg'=>'Field values do not match',
                    'desc'=>__('Invalid hidden field value message','wp-cred')
                    ),
                'cred_message_enter_valid_email'=>array(
                    'msg'=>'Please enter a valid email address',
                    'desc'=>__('Invalid email message','wp-cred')
                    ),
                'cred_message_enter_valid_number'=>array(
                    'msg'=>'Please enter numeric data',
                    'desc'=>__('Invalid numeric field message','wp-cred')
                    ),
                'cred_message_enter_valid_url'=>array(
                    'msg'=>'Please enter a valid URL address',
                    'desc'=>__('Invalid URL message','wp-cred')
                    ),
                'cred_message_enter_valid_captcha'=>array(
                    'msg'=>'Wrong CAPTCHA',
                    'desc'=>__('Invalid captcha message','wp-cred')
                    ),
                'cred_message_show_captcha'=>array(
                    'msg'=>'Show CAPTCHA',
                    'desc'=>__('Show captcha button','wp-cred')
                    ),
                'cred_message_edit_skype_button'=>array(
                    'msg'=>'Edit Skype Button',
                    'desc'=>__('Edit skype button','wp-cred')
                    ),
                'cred_message_not_valid_image'=>array(
                    'msg'=>'Not Valid Image',
                    'desc'=>__('Invalid image message','wp-cred')
                    ),
                'cred_message_file_type_not_allowed'=>array(
                    'msg'=>'File type not allowed',
                    'desc'=>__('Invalid file type message','wp-cred')
                    ),
                'cred_message_image_width_larger'=>array(
                    'msg'=>'Image width larger than %dpx',
                    'desc'=>__('Invalid image width message','wp-cred')
                    ),
                'cred_message_image_height_larger'=>array(
                    'msg'=>'Image height larger than %dpx',
                    'desc'=>__('Invalid image height message','wp-cred')
                    ),
                'cred_message_show_popular'=>array(
                    'msg'=>'Show Popular',
                    'desc'=>__('Taxonomy show popular message','wp-cred')
                    ),
                'cred_message_hide_popular'=>array(
                    'msg'=>'Hide Popular',
                    'desc'=>__('Taxonomy hide popular message','wp-cred')
                    ),
                'cred_message_add_taxonomy'=>array(
                    'msg'=>'Add',
                    'desc'=>__('Add taxonomy term','wp-cred')
                    ),
                'cred_message_remove_taxonomy'=>array(
                    'msg'=>'Remove',
                    'desc'=>__('Remove taxonomy term','wp-cred')
                    ),
                'cred_message_add_new_taxonomy'=>array(
                    'msg'=>'Add New',
                    'desc'=>__('Add new taxonomy message','wp-cred')
                    )
            );
        }
        return $messages;
    }
    
    public function disable_richedit_for_cred_forms( $default ) 
    {
        global $post;
        if ( $this->post_type_name == get_post_type( $post ) )
            return false;
        return $default;
    }
	
    private function getFieldkeys($with_quotes='', $with_prefix=true)
    {
        if ($with_prefix)
            $prefix=$this->prefix;
        else
            $prefix='';
        
        $keys=array();
        foreach ($this->form_meta_fields as $fkey)
        {
            $keys[]=$with_quotes.$prefix.$fkey.$with_quotes;
        }
        return $keys;
    }
    
    private function register_form_type() 
	{
		$labels = array(
		'name' => __('CRED Forms', 'wp-cred'),
		'singular_name' => __('CRED Form', 'wp-cred'),
		'add_new' => __('Add New', 'wp-cred'),
		'add_new_item' => __('Add New CRED Form', 'wp-cred'),
		'edit_item' => __('Edit CRED Form', 'wp-cred'),
		'new_item' => __('New CRED Form', 'wp-cred'),
		'view_item' => __('View CRED Form', 'wp-cred'),
		'search_items' => __('Search CRED Forms', 'wp-cred'),
		'not_found' =>  __('No forms found', 'wp-cred'),
		'not_found_in_trash' => __('No form found in Trash', 'wp-cred'), 
		'parent_item_colon' => '',
		'menu_name' => 'CRED Forms'

		);
		$args = array(
		'labels' => $labels,
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => true, 
		'show_in_menu' => false, 
		'query_var' => false,
		'rewrite' => false,
		'can_export' => false,
		'capability_type' => 'post',
		'has_archive' => false, 
		'hierarchical' => false,
		'menu_position' => 80,
		//'supports' => array()
		'supports' => array('title','editor'/*,'author'*/)
		); 
		register_post_type($this->post_type_name,$args);
        
        add_filter( 'user_can_richedit', array($this,'disable_richedit_for_cred_forms') );
	}
	
    public function getForm($id_or_title)
	{
		$form=false;
        if (is_string($id_or_title) && !is_numeric($id_or_title))
            $form=get_page_by_title( $id_or_title, OBJECT, $this->post_type_name );
        elseif (is_numeric($id_or_title))
            $form=get_post(intval($id_or_title));
        
        if ($form)
            $id=$form->ID;
        else
            return false;
            
		$fieldsraw=get_post_custom($id);
		$fields=array();
		foreach ($fieldsraw as $key=>$fieldraw)
		{
            $key=preg_replace('/^' . preg_quote($this->prefix, '/') . '/', '', $key);
            if (in_array($key,$this->form_meta_fields))
                $fields[$key]=maybe_unserialize($fieldraw[0]);
		}
		unset($fieldsraw);
		
		$formObj=new stdClass;
		$formObj->form=$form;
		$formObj->fields=$fields;
		return $formObj;
	}
	
	public function deleteForm($id)
	{
		/*$sql = 'DELETE FROM '.$this->wpdb->posts.' WHERE post_type="'.$this->post_type_name.'" AND ID = '.intval($id);
		$res1=$this->wpdb->query($sql);
		$sql = 'DELETE FROM '.$this->wpdb->postmeta.' WHERE post_id='.intval($id);
		if ($res1)
			$res2=$this->wpdb->query($sql);
		if ($res1&$res2) return true;
		return false;*/
		return !(wp_delete_post($id,true)===false);
	}
	
    public function saveForm($form, $fields)
	{
		global $user_ID;
		
        $new_post = array(
			'ID' => '',
            'post_title' => $form->post_title,
			'post_content' => $form->post_content,
			'post_status' => 'private',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_date' => date('Y-m-d H:i:s'),
			'post_author' => $user_ID,
			'post_type' => $this->post_type_name,
			'post_category' => array(0)
		);
		$post_id = wp_insert_post($new_post);
		
        //cred_log(print_r($fields,true));
        $fields=$this->esc_data($fields);
		foreach ($fields as $meta_key=>$meta_value)
		{
            add_post_meta($post_id, $this->prefix.$meta_key, $meta_value, false /*$unique*/);
		}
		return ($post_id);
	}
	
    public function updateForm($form,$fields)
	{
		global $user_ID;
		
		$up_post = array(
			'ID' => $form->ID,
			'post_title' => $form->post_title,
			'post_content' => $form->post_content,
			'post_status' => 'private',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_date' => date('Y-m-d H:i:s'),
			'post_author' => $user_ID,
			'post_type' => $this->post_type_name,
			'post_category' => array(0)
		);
		$post_id = wp_insert_post($up_post);
        $fields=$this->esc_data($fields);
		foreach ($fields as $meta_key=>$meta_value)
		{
            delete_post_meta($post_id, $meta_key);
            update_post_meta($post_id, $this->prefix.$meta_key, $meta_value, false /*$unique*/);
		}
		return ($post_id);
	}
	
	public function cloneForm($form_id,$cloned_form_title=null)
    {
        $form=$this->getForm($form_id);
        if ($form)
        {
            if ($cloned_form_title==null || empty($cloned_form_title))
                $cloned_form_title=$form->form->post_title.' Copy';
            $form->form->post_title=preg_replace('/[^\w\-_\. ]/','',$cloned_form_title);
            $form->form->ID='';
            //cred_log(print_r($form->fields,true));
            return $this->saveForm($form->form,$form->fields);
        }
        return false;
    }
    
    public function getForms($page=0,$perpage=10,$with_fields=false)
	{
		/*$sql = 'SELECT * FROM '.$this->wpdb->posts.' WHERE post_type="'.$this->post_type_name.'" ORDER BY post_date';
		if ($perpage>0)
		{
			$page=intval($page);
			$perpage=intval($perpage);
			$sql .= ' LIMIT '.$page*$perpage.','.($page+1)*$perpage;
		}
		$forms=$this->wpdb->get_results($sql);
		*/
		$args = array(
			'numberposts' => intval($perpage), 'offset' => intval($page)*intval($perpage),
			'category' => 0, 'orderby' => 'post_date',
			'order' => 'DESC', 'include' => array(),
			'exclude' => array(), 'meta_key' => '',
			'meta_value' =>'', 'post_type' => $this->post_type_name,
            'post_status'=>'private',
			'suppress_filters' => true
		);
		$forms=get_posts($args);
		
		return $forms;
	}
	
    public function getAllForms()
	{
		$args = array(
			'numberposts' => -1,
			'category' => 0, 'orderby' => 'post_date',
			'order' => 'DESC', 'include' => array(),
			'exclude' => array(), 'meta_key' => '',
			'meta_value' =>'', 'post_type' => $this->post_type_name,
            'post_status'=>'private',
			'suppress_filters' => true
		);
		$forms=get_posts($args);
		
		return $forms;
	}
	
    public function getFormsCount()
    {
        $sql = 'SELECT count(*) FROM '.$this->wpdb->posts.' WHERE post_type="'.$this->post_type_name.'" AND post_name<>"auto-draft" AND post_status="private" ORDER BY post_date DESC';
        $count=$this->wpdb->get_var($sql);
        return intval($count);
    }
    
    public function getFormsForTable($page, $perpage, $orderby='post_title', $order='asc')
	{
		$p=intval($page);
		if ($p<=0) $p=1;
		$pp=intval($perpage);
		$limit='';
        if ($pp != -1 && $pp<=0) $pp=10;
        if ($pp!=-1)
            $limit = 'LIMIT '.($p-1)*$pp.','.$pp;
        
        if (!in_array($orderby,array('post_title','post_date')))
            $orderby='post_title';
            
        $order=strtoupper($order);
        if (!in_array($order,array('ASC','DESC')))
            $order='ASC';
            
		// AND p.post_status="private"
		$sql = "
        SELECT p.ID, p.post_title, p.post_name, pm.meta_value as meta FROM {$this->wpdb->posts}  p, {$this->wpdb->postmeta} pm  
        WHERE (
            p.ID=pm.post_id
            AND
            pm.meta_key='{$this->prefix}form_settings'
            AND
            p.post_type='{$this->post_type_name}' 
            AND 
            p.post_name<>'auto-draft' 
            AND 
            p.post_status='private'
        ) 
        ORDER BY p.{$orderby} {$order} 
        {$limit}
        ";
		
        $forms=$this->wpdb->get_results($sql);
        foreach ($forms as $key=>$form)
        {
            $forms[$key]->meta=maybe_unserialize($forms[$key]->meta);
        }
		return $forms;
	}
	
    public function getFormsForExport($ids)
	{
        if ($ids!='all')
            $ids=implode(',',$ids);
        $meta_keys=implode(',',$this->getFieldkeys('"',true));
        
		// AND p.post_status="private"
		if ($ids!='all')
            $sql1 = 'SELECT p.* FROM '.$this->wpdb->posts.' AS p WHERE (p.post_type="'.$this->post_type_name.'" AND p.post_name<>"auto-draft" AND p.post_status="private" AND p.ID IN ('.$ids.'))';
        else
            $sql1 = 'SELECT p.* FROM '.$this->wpdb->posts.' AS p WHERE (p.post_type="'.$this->post_type_name.'" AND p.post_name<>"auto-draft" AND p.post_status="private")';
		
		if ($ids!='all')
            $sql2 = 'SELECT p.ID, pm.meta_key, pm.meta_value FROM '.$this->wpdb->posts.' AS p INNER JOIN '.$this->wpdb->postmeta.' AS pm ON p.ID=pm.post_id WHERE (p.post_type="'.$this->post_type_name.'" AND p.post_name<>"auto-draft" AND p.post_status="private"  ANd p.ID IN ('.$ids.') AND pm.meta_key IN ('.$meta_keys.'))';
        else
            $sql2 = 'SELECT p.ID, pm.meta_key, pm.meta_value FROM '.$this->wpdb->posts.' AS p INNER JOIN '.$this->wpdb->postmeta.' AS pm ON p.ID=pm.post_id WHERE (p.post_type="'.$this->post_type_name.'" AND p.post_name<>"auto-draft" AND p.post_status="private"  AND pm.meta_key IN ('.$meta_keys.'))';
		
        $forms=$this->wpdb->get_results($sql1);
        $meta=$this->wpdb->get_results($sql2);
        foreach ($forms as $key=>$form)
        {
            $forms[$key]->meta=array();
            foreach($meta as $m)
            {
                if ($form->ID==$m->ID)
                {
                    $meta_key=preg_replace('/^' . preg_quote($this->prefix, '/') . '/', '', $m->meta_key);
                    $forms[$key]->meta[$meta_key]=maybe_unserialize($m->meta_value);
                }
            }
        }
		return $forms;
	}
	
    public function updateFormCustomFields($id,$fields)
    {
		foreach ($fields as $meta_key=>$meta_value)
		{
            update_post_meta($id, $this->prefix.$meta_key, $meta_value, false /*$unique*/);
		}
    }
    
    public function updateFormCustomField($id,$field,$value)
    {
        update_post_meta($id, $this->prefix.$field, $value, false /*$unique*/);
    }
    
    public function getFormCustomFields($id)
    {
		$fieldsraw=get_post_custom(intval($id));
		$fields=array();
		foreach ($fieldsraw as $key=>$fieldraw)
		{
            $key=preg_replace('/^' . preg_quote($this->prefix, '/') . '/', '', $key);
            if (in_array($key,$this->form_meta_fields))
                $fields[$key]=maybe_unserialize($fieldraw[0]);
		}
		unset($fieldsraw);
		
		return $fields;
    }
    
    public function getFormCustomField($id,$field)
    {
		$field = $this->prefix.$field;
        
        /*$sql = 'SELECT meta_value FROM '.$this->wpdb->postmeta.' WHERE post_id='.intval($id) .' AND meta_key=%s';
		$fieldvalue=$this->wpdb->get_var($this->wpdb->prepare($sql,$field));
        if ($fieldvalue!=false && is_serialized($fieldvalue))
            $fieldvalue=(array)maybe_unserialize($fieldvalue);*/
            
        $fieldvalue=get_post_meta(intval($id), $field, true);
        if (false!=$fieldvalue && !empty($fieldvalue))
        {
            $fieldvalue=maybe_unserialize($fieldvalue);
            if (is_object($fieldvalue))
                $fieldvalue=(array)$fieldvalue;
        }
        //cred_log($field);
        //cred_log($fieldvalue);
        return $fieldvalue;
    }
        
    //=================== GENERAL (CUSTOM) POST HANDLING METHODS ====================================================
    
	public function deletePost($post_id, $force_delete=true)
    {
        $result=wp_delete_post( $post_id, $force_delete );
        return ($result!==false);
    }
    
	public function getPost($post_id)
    {
        $post_id=intval($post_id);
        $post=get_post($post_id);
        $fields=get_post_custom($post_id);
        foreach ($fields as $key=>$val)
        {
            if (is_array($val))
            {
                foreach ($val as $ii=>$val_single)
                {
                    $fields[$key][$ii]=maybe_unserialize(maybe_unserialize($val_single));
                }
            }
            else
                $fields[$key]=maybe_unserialize($val);
        }
        //cred_log(print_r($fields,true));
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
            if (!in_array($post->post_type,$taxonomy->object_type)) continue;
            if (in_array($taxonomy->name,array('post_format'))) continue;

            $key=$taxonomy->name;
            $taxonomies[$key]=array(
                                        //'tax' => $taxonomy,
                                        'label'=>$taxonomy->label,
                                        'name'=>$taxonomy->name,
                                        'hierarchical'=>$taxonomy->hierarchical,
                                        );
            $taxonomies[$key]['terms']= $this->buildTerms(wp_get_post_terms($post->ID, $taxonomy->name, array("fields" => "all")));
            /*if ($taxonomy->hierarchical)
            {
                $taxonomies[$key]['all']= $this->buildTerms(get_terms($taxonomy->name,array('hide_empty'=>0,'fields'=>'all')));

            }
            else
            {
                $taxonomies[$key]['most_popular']= $this->buildTerms(get_terms($taxonomy->name,array('number'=>5,'order_by'=>'count','fields'=>'all')));
            }*/
        }
        unset($all_taxonomies);
        $extra=array();
        $extra['featured_img_html']=get_the_post_thumbnail( $post_id, 'thumbnail' /*, $attr*/ );
        return array($post, $fields, $taxonomies, $extra);
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
    
    private function esc_data($data)
    {
        if (is_array($data) || is_object($data))
        {
            foreach ($data as $ii=>$data_val)
            {
                if (is_object($data))
                    $data->$ii=$this->esc_data($data_val);
                elseif (is_array($data))
                    $data[$ii]=$this->esc_data($data_val);
            }
        }
        else
            $data=esc_sql($data);
        return $data;
    }
    
    public function addPost($post,$fields,$taxonomies=null)
	{
		global $user_ID;
		
		//cred_log(print_r($post,true).print_r($fields,true));
        $up_post = array(
			'ID' => $post->ID,
			'post_date' => date('Y-m-d H:i:s'),
			'post_type' => $post->post_type,
			'post_category' => array(0)
		);
        
        if (isset($post->post_author))
            $up_post['post_author'] = $post->post_author;
        if (isset($post->post_title))
            $up_post['post_title'] = $post->post_title;
        if (isset($post->post_content))
            $up_post['post_content'] = $post->post_content;
        if (isset($post->post_excerpt))
            $up_post['post_excerpt'] = $post->post_excerpt;
        if (isset($post->post_status))
            $up_post['post_status'] = $post->post_status;
        if (isset($post->post_parent))
            $up_post['post_parent'] = $post->post_parent;
            
		$post_id = wp_insert_post($up_post);
		$fields['fields']=$this->esc_data($fields['fields']);
        foreach ($fields['fields'] as $meta_key=>$meta_value)
		{
            if (is_array($meta_value) && !$fields['info'][$meta_key]['save_single'])
            {
                foreach ($meta_value as $meta_value_single)
                    add_post_meta($post_id, $meta_key, $meta_value_single, false /*$unique*/);
            }
            else
                add_post_meta($post_id, $meta_key, $meta_value, false /*$unique*/);
		}
        if ($taxonomies)
        {
            $taxonomies=$this->esc_data($taxonomies);
            foreach ($taxonomies['flat'] as $tax)
            {
                // attach them to post
                wp_set_post_terms( $post_id, $tax['add'], $tax['name'], false );
            }
            foreach ($taxonomies['hierarchical'] as $tax)
            {
                foreach ($tax['add_new'] as $ii=>$addnew)
                {
                    if (is_numeric($addnew['parent']))
                    {
                        $pid=(int)$addnew['parent'];
                        if ($pid<0) $pid=0;
                        
                        $result=wp_insert_term( $addnew['term'], $tax['name'], array('parent'=>$pid) );
                        if (!is_wp_error($result))
                        {
                            $tax['add_new'][$ii]['id']=$result['term_id'];
                            $ind=array_search($addnew['term'],$tax['terms']);
                            if ($ind!==false)
                                $tax['terms'][$ind]=$result['term_id'];
                        }
                    }
                    else
                    {
                        $par_id=false;
                        foreach ($tax['add_new'] as $ii2=>$addnew2)
                        {
                            if ($addnew['parent']==$addnew2['term'] && isset($addnew2['id']))
                            {
                               $par_id=$addnew2['id'];
                                break;                               
                            }
                        }
                        if ($par_id!==false)
                        {
                            $pid=(int)$par_id;
                            if ($pid<0) $pid=0;
                            
                            $result=wp_insert_term( $addnew['term'], $tax['name'], array('parent'=>$pid) );
                        }
                        else
                            $result=wp_insert_term( $addnew['term'], $tax['name'], array('parent'=>0) );
                        
                        if (!is_wp_error($result))
                        {
                            $tax['add_new'][$ii]['id']=$result['term_id'];
                            $ind=array_search($addnew['term'],$tax['terms']);
                            if ($ind!==false)
                                $tax['terms'][$ind]=$result['term_id'];
                        }
                    }
                }
                // attach them to post
                wp_set_post_terms( $post_id, $tax['terms'], $tax['name'], false );
            }
        }
		return ($post_id);
	}
	
    public function updatePost($post,$fields,$taxonomies=null)
	{
		global $user_ID;
		
        $post_id=$post->ID;
		$up_post = array(
			'ID' => $post->ID,
			//'post_author' => $user_ID,
			'post_type' => $post->post_type
		);
        if (isset($post->post_author))
            $up_post['post_author'] = $post->post_author;
        if (isset($post->post_status))
            $up_post['post_status'] = $post->post_status;
        if (isset($post->post_title))
            $up_post['post_title'] = $post->post_title;
        if (isset($post->post_content))
            $up_post['post_content'] = $post->post_content;
        if (isset($post->post_excerpt))
            $up_post['post_excerpt'] = $post->post_excerpt;
        if (isset($post->post_parent))
            $up_post['post_parent'] = $post->post_parent;

		wp_update_post($up_post);
		$fields['fields']=$this->esc_data($fields['fields']);
		foreach ($fields['fields'] as $meta_key=>$meta_value)
		{
            delete_post_meta($post_id, $meta_key);
            if (is_array($meta_value) && !$fields['info'][$meta_key]['save_single'])
            {
                foreach ($meta_value as $meta_value_single)
                    add_post_meta($post_id, $meta_key, $meta_value_single, false /*$unique*/);
            }
            else
                add_post_meta($post_id, $meta_key, $meta_value, false /*$unique*/);
		}
        if ($taxonomies)
        {
            $taxonomies=$this->esc_data($taxonomies);
            foreach ($taxonomies['flat'] as $tax)
            {
                $old_terms=wp_get_post_terms($post_id, $tax['name'], array("fields" => "names"));
                // remove deleted terms
                $new_terms=array_diff($old_terms,$tax['remove']);
                // add new terms
                $new_terms=array_merge($new_terms,$tax['add']);
                
                // attach them to post
                wp_set_post_terms( $post_id, $new_terms, $tax['name'], false );
            }
            foreach ($taxonomies['hierarchical'] as $tax)
            {
                foreach ($tax['add_new'] as $ii=>$addnew)
                {
                    if (is_numeric($addnew['parent']))
                    {
                        $pid=(int)$addnew['parent'];
                        if ($pid<0) $pid=0;
                        
                        $result=wp_insert_term( $addnew['term'], $tax['name'], array('parent'=>$pid) );
                        if (!is_wp_error($result))
                        {
                            $tax['add_new'][$ii]['id']=$result['term_id'];
                            $ind=array_search($addnew['term'],$tax['terms']);
                            if ($ind!==false)
                                $tax['terms'][$ind]=$result['term_id'];
                        }
                    }
                    else
                    {
                        $par_id=false;
                        foreach ($tax['add_new'] as $ii2=>$addnew2)
                        {
                            if ($addnew['parent']==$addnew2['term'] && isset($addnew2['id']))
                            {
                               $par_id=$addnew2['id'];
                                break;                               
                            }
                        }
                        if ($par_id!==false)
                        {
                            $pid=(int)$par_id;
                            if ($pid<0) $pid=0;
                            
                            $result=wp_insert_term( $addnew['term'], $tax['name'], array('parent'=>$pid) );
                        }
                        else
                            $result=wp_insert_term( $addnew['term'], $tax['name'], array('parent'=>0) );
                        
                        if (!is_wp_error($result))
                        {
                            $tax['add_new'][$ii]['id']=$result['term_id'];
                            $ind=array_search($addnew['term'],$tax['terms']);
                            if ($ind!==false)
                                $tax['terms'][$ind]=$result['term_id'];
                        }
                    }
                }
                // attach them to post
                wp_set_post_terms( $post_id, $tax['terms'], $tax['name'], false );
            }
        }
		return ($post_id);
	}
    
    /*
    public function deleteTaxonomy($term, $taxonomy, $field='name', $args = array())
    {
        $term_obj=get_term_by( $field, $term, $taxonomy );
        if ($term_obj)
            return wp_delete_term( $term_obj->term_id, $taxonomy, $args );
        return false;
    }
    
    // customized from wp function wp_insert_category, to handle add or update of general (custom), hierarchical/or not taxonomies
    public function addUpdateTaxonomy($taxarr, $force_new=true, $wp_error = false) 
    {
        $tax_defaults = array('tax_ID' => 0, 'taxonomy' => 'category', 'name' => '', 'slug'=>'', 'parent'=>0, 'description'=>'');
        $taxarr = wp_parse_args($taxarr, $tax_defaults);
        extract($taxarr, EXTR_SKIP);

        if ( trim( $name ) == '' ) {
            /*if ( ! $wp_error )
                return 0;
            else
                return new WP_Error( 'name', __('You did not enter a taxonomy name.','wp-cred') );* /
            return 0;
        }

        $tax_ID = (int) $tax_ID;

        // Are we updating or creating?
        if ( !empty ($tax_ID) )
        {
            $update = true;
        }
        elseif (!$force_new)
        {
            $term_obj=get_term_by('name',$name, $taxonomy);
            if (!$term_obj)
                $update = false;
            else
            {
                $update=true;
                $tax_ID=$term_obj->term_id;
            }
        }
        else
        {
            $update=false;
        }

        /*$name = $cat_name;
        $description = $category_description;
        $slug = $category_nicename;
        $parent = $category_parent;
        * /
        
        $parent = (int) $parent;
        if ( $parent < 0 )
            $parent = 0;

        if ( empty( $parent ) || ! term_exists( $parent, $taxonomy ) || ( $tax_ID && term_is_ancestor_of( $tax_ID, $parent, $taxonomy ) ) )
            $parent = 0;

        $args = compact('name', 'slug', 'parent', 'description');

        if ( $update )
            $tax_ID = wp_update_term($tax_ID, $taxonomy, $args);
        else
            $tax_ID = wp_insert_term($name, $taxonomy, $args);

        if ( is_wp_error($tax_ID) ) {
            /*if ( $wp_error )
                return $tax_ID;
            else* /
                return 0;
        }

        return $tax_ID['term_id'];
    }*/
}
?>