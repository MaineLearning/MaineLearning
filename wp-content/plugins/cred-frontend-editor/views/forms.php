<?php
if (!defined('ABSPATH'))  die('Security check');
if(!current_user_can('manage_options')) {
	die('Access Denied');
}

// include needed files
$wp_list_table = CRED_Loader::get('TABLE/Forms');
     
$doaction = $wp_list_table->current_action();

$form_id = '';
$form_name = '';
$form_type = '';
$post_type = '';
$form_content= '';
$fields='';
if($doaction)
{
    $forms_model = CRED_Loader::get('MODEL/Forms');
}

$url = admin_url().'post-new.php?post_type='.CRED_FORMS_CUSTOM_POST_NAME;

if($doaction)
{
    switch($doaction)
    {
        case 'delete-selected':
            if (isset($_REQUEST['checked']) && is_array($_REQUEST['checked']))
            {
                if (check_admin_referer('cred-bulk-selected-action','cred-bulk-selected-field'))
                {
                    foreach ($_REQUEST['checked'] as $form_id)
                    {
                        $forms_model->deleteForm((int)$form_id);
                    }
                }
            }
            break;
            
        case 'clone-selected':
            if (isset($_REQUEST['checked']) && is_array($_REQUEST['checked']))
            {
                if (check_admin_referer('cred-bulk-selected-action','cred-bulk-selected-field'))
                {
                    foreach ($_REQUEST['checked'] as $form_id)
                    {
                        $forms_model->cloneForm((int)$form_id);
                    }
                }
            }
            break;
        
        case 'delete':
            if(!isset($_REQUEST['id']))
            {
                break;
            }
            
            $form_id = (int) $_REQUEST['id'];
            if (check_admin_referer('delete-form_'.$form_id,'_wpnonce'))
            {
                $forms_model->deleteForm($form_id);
            }
            break;
            
        case 'clone':
            if(!isset($_REQUEST['id']))
            {
                break;
            }
            
            $form_id = (int) $_REQUEST['id'];
            if (check_admin_referer('clone-form_'.$form_id,'_wpnonce'))
            {
                if (array_key_exists('cred_form_title',$_REQUEST) && ($cred_form_title=trim(urldecode($_REQUEST['cred_form_title'])))!='')
                    $forms_model->cloneForm($form_id,$cred_form_title);
                else
                    $forms_model->cloneForm($form_id);
            }
            break;
        
        /*case 'edit':
            if(!isset($_REQUEST['id']))
            {
                break;
            }
            
            // set variables for the template
            $form_id = (int) $_REQUEST['id'];
            
            $form = $forms_model->getForm($form_id);
			$post_types=get_post_types(array('public'=>true,'publicly_queryable'=>true),'names'); 
			$form_id = $form->form->ID;
			$form_name = $form->form->post_title;
			$form_type = $form->fields['_cred_form_settings']->form_type;
			$post_type = $form->fields['_cred_form_settings']->post_type;
			$form_content = $form->form->post_content;
			$wpcf_fields=get_option('wpcf-fields');
			$fields1 = $fields_model->getFields($post_type);
            $fields='';
			/*foreach ($fields1 as $key=>$field)
			{
				$fields .= "<a href='#' class='button cred_field_add' data-type='".$field['type']."' title='".$field['description']."'>".$field['name']."</a>";
			}/
			$template_mode = 'edit';
            include_once(CRED_TEMPLATES_DIR.'/form.tpl.php');
            
            exit;
            break;*/
            
         /*case 'add':
                     
           
            $template_mode = 'add';
			$post_types=get_post_types(array('public'=>true,'publicly_queryable'=>true),'names'); 
			$wpcf_fields=get_option('wpcf-fields');
            
			include_once(CRED_TEMPLATES_DIR.'/form.tpl.php');
  
            exit;
            break;*/
    }
    
}


$wp_list_table->prepare_items();

echo '<div class="cred_overlay_loader"></div>';
echo '<div class="wrap cred-wrap">';
echo '<h2 class="cred-h2">'.__('CRED Forms', 'wp-cred').'<a class="add-new-h2" href="'.$url.'">'.__('Add New', 'wp-cred').'</a></h2>';

echo '<a class="cred-help-link" style="position:relative;" href="'.CRED_CRED::$help['add_forms_to_site']['link'].'" target="_blank" title="'.CRED_CRED::$help['add_forms_to_site']['text'].'">'.CRED_CRED::$help['add_forms_to_site']['text'].'</a>';
echo '<form id="list" action="" method="post">';
if ( function_exists('wp_nonce_field') ) 
	wp_nonce_field('cred-bulk-selected-action','cred-bulk-selected-field'); 
    
$wp_list_table->display();
    
echo '</form>';
echo '</div>';
?>
<script type='text/javascript'>
(function($){
    $(function(){
        //$('.cred_ajax_loader_small').hide();
        var overlay_loader=$('.cred_overlay_loader'); //$('<div class="cred_overlay_loader"></div>');
        overlay_loader/*.appendTo($('body'))*/.hide();
        $('form#list').on('click','a.submitexport',function(event){
            event.preventDefault();
            var linkHref = $(this).attr('href')+'&ajax=1';
            $.fileDownload(linkHref,{
                successCallback:function()
                {//$('.cred_ajax_loader_small').hide();
                overlay_loader.hide();},
                beforeDownloadCallback:function()
                {$('.cred_ajax_loader_small').show();
                overlay_loader.show();},
                failCallback:function()
                {
                    //$('.cred_ajax_loader_small').hide();
                    overlay_loader.hide();
                    alert('<?php _e('An error occurred please try again','wp-cred'); ?>');
                }
            });
            return false; //this is critical to stop the click event which will trigger a normal file download!
        });
        
        $('form#list').on('click','a.cred-export-all',function(event){
            event.preventDefault();
            var linkHref = $(this).attr('href')+'&ajax=1';
            $.fileDownload(linkHref,{
                successCallback:function()
                {//$('.cred_ajax_loader_small').hide();
                overlay_loader.hide();},
                beforeDownloadCallback:function()
                {$('.cred_ajax_loader_small').show();
                overlay_loader.show();},
                failCallback:function()
                {
                    //$('.cred_ajax_loader_small').hide();
                    overlay_loader.hide();
                    alert('<?php _e('An error occurred please try again','wp-cred'); ?>');
                }
            });
            return false; //this is critical to stop the click event which will trigger a normal file download!
        });
        
        $('form#list').submit(function(event){
            var action=$('form#list select[name="action"]').val();
            if (action=='export-selected')
            {
                event.preventDefault();
                $.fileDownload('<?php echo cred_route('/Forms/exportSelected?ajax=1'); ?>',{
                    successCallback:function()
                    {//$('.cred_ajax_loader_small').hide();
                    overlay_loader.hide();},
                    beforeDownloadCallback:function()
                    {$('.cred_ajax_loader_small').show();
                    overlay_loader.show();},
                    failCallback:function()
                    {
                        //$('.cred_ajax_loader_small').hide();
                        overlay_loader.hide();
                        alert('<?php _e('An error occurred please try again','wp-cred'); ?>');
                    },
                    httpMethod: "POST",
                    data: $(this).serialize()
                });
                return false; //this is critical to stop the click event which will trigger a normal file download!
            }
            else if (action=='delete-selected')
            {
                if (confirm('<?php _e( "Are you sure that you want to delete the selected forms?\\n\\n Click [Cancel] to stop, [OK] to delete.", "wp-cred" ); ?>'))
                    return true;
                else
                {
                    event.preventDefault();
                    return false;
                }
            }
            else
                return true;
        });
    });
})(jQuery);
</script>