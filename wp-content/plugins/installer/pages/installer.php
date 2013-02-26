<?php
if( !defined('ABSPATH') ) die('Security check');
if(!current_user_can('manage_options')) {
	die('Access Denied');
}


$result = '';
$result_msg = ''; 

if(array_key_exists('result',$_GET))
{
    $result = $_GET['result'];
    
    switch($result)
    {
        case 'success':
            $result_msg = __('Settings are saved', 'installer');
            break;
            
        case 'failure':
           // $result_msg = __('Settings are not saved. Change a values of the settings please', 'installer');
            $result_msg = __('Settings are saved', 'installer');
            break;
    }
}

$settings_model = WPRC_Loader::getModel('settings');
$settings = $settings_model->getSettings();

$show_msg = '';
if(array_key_exists('warning', $_GET))
{
    $show_msg = $_GET['warning'];
}

$msg = '';
switch($show_msg)
{
    case 'https_not_provided':
        $msg = __('HTTPS is not provided by server. Please connect with your site administrator','installer');
        break;
}
?>

<div class="wrap">
	<h2><?php _e('Installer settings', 'installer'); ?></h2>
	<form action="<?php echo admin_url().'admin.php?wprc_c=settings&wprc_action=save';?>" method="post">
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('installer-settings-form'); ?>" />
		<h3 class="title"><?php _e('Settings', 'installer'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="settings[allow_compatibility_reporting]"><?php echo __('Enable sending compatibility reports', 'installer'); ?></label></th>
					<td><input name="settings[allow_compatibility_reporting]" type="checkbox" value="1" class="small-text" <?php if($settings['allow_compatibility_reporting'] == 1) echo ' checked="checked"'; ?> /> <span class="description"><?php _e( 'Explicit confirmation will be prompted for each report', 'installer' ); ?></span></td>
				</tr>
			</tbody>
		</table><br/>
		<h3 class="title"><?php _e('Uninstall Installer', 'installer'); ?></h3>
		<p>
			<?php printf(__('If you want to uninstall Installer Plugin click <a class="button-secondary" href="%1$s">%2$s</a>','installer'),admin_url().'options-general.php?page='.WPRC_PLUGIN_FOLDER.'/pages/uninstall.php',__( 'Uninstall','installer' )); ?>
		</p>
		<br/>
		<input type="submit" value="<?php echo __('Save changes', 'installer'); ?>" class="button-primary" />
	</form>
</div>

	






