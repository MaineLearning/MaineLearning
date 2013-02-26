<?php
if (!defined('ABSPATH'))  die('Security check');
if(!current_user_can('manage_options')) {
	die('Access Denied');
}
$results='';
$cred_import_file=null;
if (isset($_POST['import']) && $_POST['import'] == __('Import', 'wp-cred') &&
    isset($_POST['cred-import-nonce']) &&
    wp_verify_nonce($_POST['cred-import-nonce'], 'cred-import-nonce'))
{
    if (isset($_FILES['import-file']))
    {
        $cred_import_file = $_FILES['import-file'];

        if ($cred_import_file['error']>0)
        {
            echo '<div style="color:red">'.__('Upload error or file not valid','wp-cred').'</div>';
            $cred_import_file = null;
        }
    }
    else
    {
        $cred_import_file = null;
    }

    if ($cred_import_file!==null && !empty($cred_import_file))
    {
        $options=array();
        if (isset($_POST["cred-overwrite-forms"]))
            $options['overwrite_forms']=1;
        if (isset($_POST["cred-overwrite-settings"]))
            $options['overwrite_settings']=1;
        CRED_Loader::load('CLASS/XML_Processor');
        $results=CRED_XML_Processor::importFromXML($cred_import_file, $options);
    }
}

$settings_model = CRED_Loader::get('MODEL/Settings');

$url = admin_url('admin.php').'?page=CRED_Settings';
$doaction = isset($_POST['cred_settings_action'])?$_POST['cred_settings_action']:false;
if($doaction)
{
    switch($doaction)
    {
        case 'edit':
            check_admin_referer('cred-settings-action','cred-settings-field');

            $settings = isset($_POST['settings'])?(array)$_POST['settings']:array();
            if (!isset($settings['wizard']))
                $settings['wizard']=0;
            $settings_model->updateSettings($settings);
            break;
    }
}
else
{
    $settings = $settings_model->getSettings();
}
?>
<div class="wrap cred-wrap">
    <a class="cred-help-link" style="position:absolute;top:0;right:0;" href="<?php echo CRED_CRED::$help['add_forms_to_site']['link']; ?>" target="_blank"><?php echo CRED_CRED::$help['add_forms_to_site']['text']; ?></a>
    <h2 class="cred-h2"><?php _e('CRED Settings','wp-cred') ?></h2><br />
    <p style="font-weight: bold;"><?php _e('This screen contains the CRED settings for your site.','wp-cred'); ?></p>
    <ul class="horlist">
    <li><a href="#cred-general-settings"><?php _e('General Settings','wp-cred'); ?></a></li>
    <li><a href="#cred-import"><?php _e('Import','wp-cred'); ?></a></li>
    </ul>
    <a id="cred-general-settings"></a>
    <form method="post" action="">
    <?php wp_nonce_field('cred-settings-action','cred-settings-field'); ?>
    <table class="widefat" id="cred_general_settings_table">
    <thead>
        <tr>
            <th><?php _e('General Settings','wp-cred'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
            <table>
                <tr>
                    <td colspan=2>
                        <label class='cred-label'><input type="checkbox" class='cred-checkbox-invalid' name="settings[wizard]" value="1" <?php if (isset($settings['wizard']) && $settings['wizard']) echo "checked='checked'"; ?> /><span class='cred-checkbox-replace'></span>
                        <span><?php _e('Create new forms using the CRED Wizard','wp-cred'); ?></span></label>
                    </td>
                </tr>
                <tr>
                    <td colspan=2>
                        <label class='cred-label'><input type="checkbox" class='cred-checkbox-invalid' name="settings[syntax_highlight]" value="1" <?php if (isset($settings['syntax_highlight']) && $settings['syntax_highlight']) echo "checked='checked'"; ?> /><span class='cred-checkbox-replace'></span>
                        <span><?php _e('Enable Syntax Highlight for CRED forms','wp-cred'); ?></span></label>
                    </td>
                </tr>
                <tr>
                    <td colspan=2>
                        <label class='cred-label'><input type="checkbox" class='cred-checkbox-invalid' name="settings[export_settings]" value="1" <?php if (isset($settings['export_settings']) && $settings['export_settings']) echo "checked='checked'"; ?> /><span class='cred-checkbox-replace'></span>
                        <span><?php _e('Export Settings also when exporting Forms','wp-cred'); ?></span></label>
                    </td>
                </tr>
                <tr>
                    <td colspan=2>
                    <p>
						<?php _e('CRED Forms allow integration with ReCaptcha API,','wp-cred'); ?><br>
						<?php _e('a free CAPTCHA service that helps digitize books while protecting against bots messing with your forms','wp-cred'); ?>
					</p>
                    <p><?php _e('The following are needed only if you plan to use ReCaptcha support for your forms (recommended)','wp-cred'); ?></p>
                    <a target="_blank" href='http://www.google.com/recaptcha/whyrecaptcha'><?php _e('Sign Up to use ReCaptcha API','wp-cred'); ?></a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="text" size='50' name="settings[recaptcha][public_key]" value="<?php if (isset($settings['recaptcha']['public_key'])) echo $settings['recaptcha']['public_key']; ?>"  />
                        <strong><?php _e('Public Key for ReCaptcha API','wp-cred'); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="text" size='50' name="settings[recaptcha][private_key]" value="<?php if (isset($settings['recaptcha']['private_key'])) echo $settings['recaptcha']['private_key']; ?>"  />
                        <strong><?php _e('Private Key for ReCaptcha API','wp-cred'); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td colspan=2>
                       <p>
                        	 <input type="hidden" name="cred_settings_action" value="edit" />
                        	<input type="submit" name="submit" value="<?php _e('Update Settings','wp-cred'); ?>" class="button-primary"/>
                        </p>
                    </td>
                </tr>
            </table>
                </td>
            </tr>
        </tbody>
    </table>
    </form>
    <br />
    <a id="cred-import"></a>
    <form name="cred-import-form" enctype="multipart/form-data" action="" method="post">
    <?php wp_nonce_field('cred-settings-action','cred-settings-field'); ?>
    <table class="widefat" id="cred_general_settings_table">
    <thead>
        <tr>
            <th><?php _e('Import CRED Forms','wp-cred'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?php
                if (is_wp_error($results))
                {
                    echo '<div style="color:red;font-weight:bold">'.$results->get_error_message($results->get_error_code()).'</div>';
                }
                elseif (is_array($results))
                {
                ?>
            <ul style="font-style:italic">
                <li><?php _e('Settings Imported','wp-cred'); ?> : <?php echo $results['settings']; ?></li>
                <li><?php _e('Forms overwritten','wp-cred'); ?> : <?php echo $results['updated']; ?></li>
                <li><?php _e('Forms added','wp-cred'); ?> : <?php echo $results['new']; ?></li>
            </ul>
        <?php
    }
    ?>

                <ul>
                    <li>
                        <label class='cred-label'><input id="checkbox-1" type="checkbox" class='cred-checkbox-invalid' name="cred-overwrite-forms" value="1" /><span class='cred-checkbox-replace'></span>
                        <span><?php _e('Overwrite existing forms','wp-cred'); ?></span></label>
                    </li>
                    <!--<li>
                        <input id="checkbox-2" type="checkbox" name="cred-delete-other-forms"  value="1" />
                        <label for="checkbox-2"><?php _e('Delete forms not included in the import','wp-cred'); ?></label>
                    </li>-->
                    <li>
                        <label class='cred-label'>
							<input id="checkbox-5" type="checkbox" class='cred-checkbox-invalid' name="cred-overwrite-settings" value="1" />
							<span class='cred-checkbox-replace'></span>
                        <span><?php _e('Import and Overwrite CRED Settings','wp-cred'); ?></span></label>
                    </li>
                </ul>
                <label for="upload-cred-file"><?php __('Select the xml file to upload:&nbsp;','wp-cred'); ?></label>

                <input type="file" class='cred-filefield' id="upload-cred-file" name="import-file" />

                <input id="cred-import" class="button-primary" type="submit" value="<?php _e('Import','wp-cred'); ?>" name="import" />

                <?php wp_nonce_field('cred-import-nonce', 'cred-import-nonce'); ?>
                </td>
            </tr>
        </tbody>
    </table>
    </form>
</div>
