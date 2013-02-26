<?php
if( !defined('ABSPATH') ) die('Security check');

$repository_id = '';
$repository_name = '';
$repository_enabled = 0;
$repository_requires_login = 0;
$repository_logo = '';
$repository_description = '';
$repository_site_url = '';
$repository_endpoint_url = '';
$repository_username = '';
$repository_password = '';
$login_link='';
$clear_link='';

switch($template_mode)
{
    case 'add':
        $page_title = __('Add repository', 'installer');
        $button_caption = __('Add repository', 'installer');
        $form_action = admin_url().'admin.php?wprc_c=repositories&wprc_action=addRepository';
        break;

    case 'edit':
        $page_title = __('Edit repository', 'installer');
        $button_caption = __('Save changes', 'installer');
        $form_action = admin_url().'admin.php?wprc_c=repositories&wprc_action=updateRepository';

$nonce_login = wp_create_nonce('installer-login-link');
$nonce_clear = wp_create_nonce('installer-clear-link');
if ($repository->repository_username=='' && $repository->repository_password=='')
    $login_link='<a class="thickbox button-primary" title="' . __('Log in', 'installer') . '" href="'.admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin&amp;repository_id=' .$repository->id.'&amp;_wpnonce='.$nonce_login).'">'.__('Log in', 'installer').'</a>';
else
    $clear_link='<a onclick="return wprc.repositories.clearLoginInfo(this,\''.$repository->repository_name.'\');" class="button-secondary" href="'.admin_url('admin.php?wprc_c=repositories&amp;wprc_action=clearLoginInfo&amp;repository_id='.$repository->id.'&amp;_wpnonce='.$nonce_clear).'">'.__('Log out','installer').'</a>';
                
        $repository_id = $repository->id;
        $repository_name = $repository->repository_name;
        $repository_endpoint_url = $repository->repository_endpoint_url;
        $repository_logo = $repository->repository_logo;
        $repository_enabled = $repository->repository_enabled;
        $repository_requires_login = $repository->requires_login;
        $repository_username = $repository->repository_username;
        $repository_password = $repository->repository_password;
        $repository_description = stripslashes($repository->repository_description);
        $repository_site_url = $repository->repository_website_url;
        break;
}
?>

<script language="javascript">

jQuery(document).ready(function($) {  
    $('#upload_logo_button').click(function() {  
        tb_show('Upload a logo', 'media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true', false);  
        return false; 
    });

    wprc.repositories.renderExtensionTypes( '#extension_types_multiselect', '<?php echo $json_types; ?>' );

    window.send_to_editor = function(html) { 
        var image_url = $('img',html).attr('src');  
        $('#logo_url').val(image_url);  
        tb_remove(); 
        $('.logo_preview img').attr('src',image_url);
    }  

    $('input[name=repository_requires_login]').click(function() {
        if ( $(this).attr('checked') )
            $('#login_details_table').show();
        else
            $('#login_details_table').hide();
    })
    
}); 
</script>
        
<div class="wrap">
    <h2><?php echo $page_title; ?></h2><br />
    <p>
    </p>
    <form method="post" action="<?php echo $form_action; ?>" id="repositories_form">
        <a href="<?php echo admin_url( 'options-general.php?page=installer/pages/repositories.php' ); ?>" ><?php printf( __( '%s Back to repositories list', 'installer' ), '&laquo;' ); ?></a>
        <table style="margin-top:20px;" class="form-table">
        <tr>
            <th><?php echo __('Repository name*', 'installer'); ?></th>
            <td>
                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('installer-repositories-form'); ?>" />
                <input type="hidden" value="<?php echo esc_attr($repository_id); ?>" name="repository_id">
                <input type="text" value="<?php echo esc_attr($repository_name); ?>" name="repository_name">
            </td>
        </tr>
        
        <tr>
            <th><?php echo __('Repository end point url*', 'installer'); ?></th>
            <td><input type="text" value="<?php echo esc_attr($repository_endpoint_url); ?>" name="repository_endpoint_url"></td>
        </tr>
        
        <tr>
            <th><?php echo __('Repository types', 'installer'); ?></th>
            <td>
                <div id="extension_types_multiselect"></div>
            </td>
        </tr>
        
        <tr>
            <th><?php echo __('Repository logo', 'installer'); ?></th>
            <td>
                <div class="logo_preview">
                    <img src="<?php echo esc_url($repository_logo); ?>" />
                </div>
                <input type="text" name="repository_logo" id="logo_url" value="<?php echo esc_url($repository_logo); ?>" />
                <input type="button" id="upload_logo_button" class="button-secondary" value="<?php _e( 'Upload image', 'installer' ); ?>"/>
                <span class="description"><?php _e( 'Ideal logo image is 285x115 pixels', 'installer' ); ?></span>
            </td>
        </tr>

        <tr>
            <th><?php echo __('Repository Description', 'installer'); ?></th>
            <td><textarea rows="8" name="repository_description"><?php echo $repository_description; ?></textarea></td>
        </tr>

        <tr>
            <th><?php echo __('Repository URL', 'installer'); ?></th>
            <td><input type="text" name="repository_site_url" value="<?php echo esc_url($repository_site_url); ?>"></td>
        </tr>

        

        <tr>
            <th><?php echo __('Repository is enabled', 'installer'); ?></th>
            <td><input type="checkbox" name="repository_enabled" value="<?php esc_attr($repository_enabled); ?>" <?php if($repository_enabled) { echo 'checked="checked"'; } ?>></td>
        </tr>

        <tr>
            <th><?php echo __('This repository allows logging in', 'installer'); ?></th>
            <td><input type="checkbox" name="repository_requires_login" value="<?php esc_attr($repository_requires_login); ?>" <?php if($repository_requires_login) { echo 'checked="checked"'; } ?>></td>
        </tr>
        
        <tr>
        <td colspan="2">
        <p class="submit">
            <input type="button" onclick="wprc.repositories.validateForm('repositories_form')" value="<?php echo esc_attr($button_caption); ?>" class="button-primary">
            <a class="button-secondary" href="<?php echo admin_url( 'admin.php?page=installer/pages/repositories.php'); ?>"><?php _e('Cancel', 'installer' ); ?></a>
        </p>
        </td>
        </tr>
        </table>
    </form>
<?php if ( $template_mode=='edit' ) : ?>
        <br />
        <table class="form-table" id="login_details_table" style="<?php echo ( $repository_requires_login ) ? '' : 'display:none' ?>">
        <tr>
            <th><?php echo __('User name', 'installer'); ?></th>
            <td><span style='color:gray;'><?php echo esc_attr($repository_username); ?></span></td>
        </tr>
        
        <tr>
            <th><?php echo __('Password', 'installer'); ?></th>
            <td><span style='color:gray;'><?php if ($repository_password<>'') _e('Encrypted and Saved','installer'); ?></span></td>
        </tr>
        
        <tr>
        <td colspan="2">
        <div class='wprc-loader' style="display:none"></div>
        <br />
        <?php
        if ($repository_username=='' && $repository_password=='')
            echo $login_link;
        else
            echo $clear_link;
        ?>
        </td>
        </tr>
        </table>
<?php endif; ?>
</div>