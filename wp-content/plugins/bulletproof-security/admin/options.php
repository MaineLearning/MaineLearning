<?php
// Direct calls to this file are Forbidden when core files are not present
if ( !function_exists('add_action') ){
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}
 
if ( !current_user_can('manage_options') ){ 
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}
?>

<div class="wrap">
<?php $bulletproof_ver = '.46.9'; ?>
<div id="bpsUprade"><strong>
<a href="http://www.ait-pro.com/aitpro-blog/3395/bulletproof-security-pro/bps-free-vs-bps-pro-feature-comparison/" target="_blank" title="Link opens in new browser window">Why Upgrade to BulletProof Security Pro?</a></strong></div>

<!-- Begin Rating CSS - needs to be inline to load on first launch -->
<style type="text/css">
div.bps-star-container { float:right; position: relative; top:-55px; right:-153px; height:19px; width:100px; font-size:19px;}
div.bps-star {height: 100%; position:absolute; top:0px; left:0px; background-color: transparent; letter-spacing:1ex; border:none;}
.bps-star1 {width:20%;} .bps-star2 {width:40%;} .bps-star3 {width:60%;} .bps-star4 {width:80%;} .bps-star5 {width:100%;}
.bps-star.bps-star-rating {background-color: #fc0;}
.bps-star img{display:block; position:absolute; right:0px; border:none; text-decoration:none;}
div.bps-star img {width:19px; height:19px; border-left:1px solid #fff; border-right:1px solid #fff;}
.bps-downloaded {float:right; position: relative; top:-33px; right:0px; }
.bps-star-link {float:right; position: relative; top:-19px; right:-253px; }
</style>
<!-- End Rating CSS - needs to be inline to load on first launch -->

<h2 style="margin-left:70px;"><?php _e('BulletProof Security ~ htaccess Core'); ?></h2>
<?php
if (function_exists('get_transient')) {
require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	if (false === ($bpsapi = get_transient('bulletproof-security_info'))) {
	$bpsapi = plugins_api('plugin_information', array('slug' => stripslashes( 'bulletproof-security' ) ));
	
	if ( !is_wp_error($bpsapi) ) {
	$bpsexpire = 60 * 15; // Cache data for 15 minutes
	set_transient('bulletproof-security_info', $bpsapi, $bpsexpire);
	}
	}
  
	if ( !is_wp_error($bpsapi) ) {
	$plugins_allowedtags = array('a' => array('href' => array(), 'title' => array(), 'target' => array()),
								'abbr' => array('title' => array()), 'acronym' => array('title' => array()),
								'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
								'div' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
								'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
								'img' => array('src' => array(), 'class' => array(), 'alt' => array()));
	//Sanitize HTML
	foreach ( (array)$bpsapi->sections as $section_name => $content )
		$bpsapi->sections[$section_name] = wp_kses($content, $plugins_allowedtags);
	foreach ( array('version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug') as $key )
		$bpsapi->$key = wp_kses($bpsapi->$key, $plugins_allowedtags);

      if ( !empty($bpsapi->downloaded) ) {
        echo '<div class="bps-downloaded">'.sprintf(__('Downloaded %s times'),number_format_i18n($bpsapi->downloaded)).'.</div>';
      }
?>
		<?php if ( !empty($bpsapi->rating) ) : ?>
		<div class="bps-star-container" title="<?php echo esc_attr(sprintf(__('Average Rating (%s ratings)'),number_format_i18n($bpsapi->num_ratings))); ?>">
			<div class="bps-star bps-star-rating" style="width: <?php echo esc_attr($bpsapi->rating) ?>px"></div>
			<div class="bps-star bps-star5"><img src="<?php echo WP_PLUGIN_URL; ?>/bulletproof-security/admin/images/star.png" alt="<?php _e('5 stars') ?>" /></div>
			<div class="bps-star bps-star4"><img src="<?php echo WP_PLUGIN_URL; ?>/bulletproof-security/admin/images/star.png" alt="<?php _e('4 stars') ?>" /></div>
			<div class="bps-star bps-star3"><img src="<?php echo WP_PLUGIN_URL; ?>/bulletproof-security/admin/images/star.png" alt="<?php _e('3 stars') ?>" /></div>
			<div class="bps-star bps-star2"><img src="<?php echo WP_PLUGIN_URL; ?>/bulletproof-security/admin/images/star.png" alt="<?php _e('2 stars') ?>" /></div>
			<div class="bps-star bps-star1"><img src="<?php echo WP_PLUGIN_URL; ?>/bulletproof-security/admin/images/star.png" alt="<?php _e('1 star') ?>" /></div>
		</div>
		<div class="bps-star-link"><small><?php echo sprintf(__('Average Rating (%s ratings)'),number_format_i18n($bpsapi->num_ratings)); ?> <a target="_blank" title="Rating Link opens in new browser window" href="http://wordpress.org/extend/plugins/<?php echo $bpsapi->slug ?>/"> <?php _e('Rate') ?></a></small></div>
        <br />
		<?php endif; 
	  } // if ( !is_wp_error($bpsapi)
 }// end if (function_exists('get_transient'
?>

<div id="message" class="updated" style="border:1px solid #999999; margin-left:70px;">

<?php
// HUD - Heads Up Display - Warnings and Error messages
echo bps_check_php_version_error();
echo bps_check_permalinks_error();
echo bps_check_iis_supports_permalinks();
echo bps_hud_check_bpsbackup();
echo bps_check_safemode();
echo @bps_w3tc_htaccess_check($plugin_var);
echo @bps_wpsc_htaccess_check($plugin_var);

// Form - copy and rename htaccess file to root folder
// BulletProof Security and Default Mode
$bpsecureroot = 'unchecked';
$bpdefaultroot = 'unchecked';
if (isset($_POST['submit12']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_root_copy' );
	
	$old = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';
	$new = ABSPATH . '/.htaccess';
	$old1 = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
	$new1 = ABSPATH . '/.htaccess';
	
	$selected_radio = $_POST['selection12'];
	if ($selected_radio == 'bpsecureroot') {
	$bpsecureroot = 'checked';
		@copy($old1, $new1);
		chmod($new1, 0644);
		if (!copy($old1, $new1)) {
	_e('<font color="red"><strong>Failed to Activate BulletProof Security Root Folder Protection! Your Website is NOT protected with BulletProof Security!</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>BulletProof Security Root Folder Protection Activated. Your website Root folder is now protected with BulletProof Security.</strong></font><br><font color="red"><strong>IMPORTANT!</strong></font><strong> BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder.</strong><br>');
    }
	}
	elseif ($selected_radio == 'bpdefaultroot') {
	$bpdefaultroot = 'checked';
		@copy($old, $new);
		chmod($new, 0644);
		if (!copy($old, $new)) {
	_e('<font color="red"><strong>Failed to Activate Default .htaccess Mode!</strong></font><br>');
   	} else {
	_e('<font color="red"><strong>Warning: Default .htaccess Mode Is Activated In Your Website Root Folder. Your Website Is Not Protected With BulletProof Security.</strong></font>');
	}
	}
}

// Form - copy and rename htaccess file to wp-admin folder
// Do String Replacements for Custom Code AFTER new .htaccess file has been copied to wp-admin
$bpsecurewpadmin = 'unchecked';
$Removebpsecurewpadmin = 'unchecked';
if (isset($_POST['submit13']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_wpadmin_copy' );
	
	$options = get_option('bulletproof_security_options_customcode_WPA');  
	
	$oldadmin1 = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	$newadmin1 = ABSPATH . 'wp-admin/.htaccess';
	$deleteWpadminHtaccess = ABSPATH . 'wp-admin/.htaccess';

	$bpsString1 = "# CCWTOP";
	$bpsString2 = "# CCWPF";
	$bpsReplace1 = htmlspecialchars_decode($options['bps_customcode_one_wpa']);
	$bpsReplace2 = htmlspecialchars_decode($options['bps_customcode_two_wpa']);
	
	$selected_radio = $_POST['selection13'];
	if ($selected_radio == 'bpsecurewpadmin') {
	$bpsecurewpadmin = 'checked';
		@copy($oldadmin1, $newadmin1);
		chmod($newadmin1, 0644);
		if (!copy($oldadmin1, $newadmin1)) {
	_e('<font color="red"><strong>Failed to Activate BulletProof Security wp-admin Folder Protection! Your wp-admin folder is NOT protected with BulletProof Security!</strong></font><br>');
   	} else {
	if (file_exists($newadmin1)) {
	$bpsBaseContent = @file_get_contents($newadmin1);
		$bpsBaseContent = str_replace($bpsString1, $bpsReplace1, $bpsBaseContent);
		$bpsBaseContent = str_replace($bpsString2, $bpsReplace2, $bpsBaseContent);
		@file_put_contents($newadmin1, $bpsBaseContent);
	_e('<font color="green"><strong>BulletProof Security wp-admin Folder Protection Activated. Your wp-admin folder is now protected with BulletProof Security.</strong></font>');
	}
	}
	}
	elseif ($selected_radio == 'Removebpsecurewpadmin') {
	$Removebpsecurewpadmin = 'checked';
	$fh = fopen($deleteWpadminHtaccess, 'a');
	fwrite($fh, 'delete');
	fclose($fh);
	@unlink($deleteWpadminHtaccess);
	if (file_exists($deleteWpadminHtaccess)) {
	_e('<font color="red"><strong>Failed to Delete the wp-admin .htaccess file! The file does not exist. It may have been deleted or renamed already.</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>The wp-admin .htaccess file has been Deleted. </strong></font><font color="red"><strong>Your wp-admin folder is no longer .htaccess protected.</strong></font> If you are testing then be sure to reactivate BulletProof Mode for your wp-admin folder when you are done testing. If you are removing BPS from your website then be sure to also Activate Default Mode for your Root folder. The Root and wp-admin BulletProof Modes must be activated together or removed togeher.</strong></font><br>');
	}
	}
}

// Form rename Deny All htaccess file to .htaccess for the BPS Master htaccess folder
$bps_rename_htaccess_files = 'unchecked';
if (isset($_POST['submit8']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_denyall_master' );
	
	$bps_rename_htaccess = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_renamed = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/.htaccess';
		
	$selected_radio = $_POST['selection8'];
	if ($selected_radio == 'bps_rename_htaccess_files') {
	$bps_rename_htaccess_files = 'checked';
		@copy($bps_rename_htaccess, $bps_rename_htaccess_renamed);
		if (!copy($bps_rename_htaccess, $bps_rename_htaccess_renamed)) {
	_e('<font color="red"><strong>Failed to Activate BulletProof Security Deny All Folder Protection! Your BPS Master htaccess folder is NOT Protected with Deny All htaccess folder protection!</strong></font><br>');
   	} else {
	_e('BulletProof Security Deny All Folder Protection <font color="green"><strong>Activated.</strong></font> Your BPS Master htaccess folder is Now Protected with Deny All htaccess folder protection.');
	}
}
}

// Form copy and rename the Deny All htaccess file to the BPS backup folder
$bps_rename_htaccess_files_backup = 'unchecked';
if (isset($_POST['submit14']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_denyall_bpsbackup' );
	
	$bps_rename_htaccess_backup = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_backup_online = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	
	$selected_radio = $_POST['selection14'];
	if ($selected_radio == 'bps_rename_htaccess_files_backup') {
	$bps_rename_htaccess_files_backup = 'checked';
		copy($bps_rename_htaccess_backup, $bps_rename_htaccess_backup_online);
		if (!copy($bps_rename_htaccess_backup, $bps_rename_htaccess_backup_online)) {
	_e('<font color="red"><strong>Failed to Activate BulletProof Security Deny All Folder Protection! Your BPS /wp-content/bps-backup folder is NOT Protected with Deny All htaccess folder protection!</strong></font><br>');
   	} else {
	_e('BulletProof Security Deny All Folder Protection <font color="green"><strong>Activated.</strong></font> Your BPS /wp-content/bps-backup folder is Now Protected with Deny All htaccess folder protection.');
	}
	}
}

// Form - Backup and rename existing and / or currently active htaccess files from 
// the root and wpadmin folders to /wp-content/bps-backup
$backup_htaccess = 'unchecked';
if (isset($_POST['submit9']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_backup_active_htaccess_files' );
	
	$old_backroot = ABSPATH . '/.htaccess';
	$new_backroot = WP_CONTENT_DIR . '/bps-backup/root.htaccess';
	$old_backwpadmin = ABSPATH . '/wp-admin/.htaccess';
	$new_backwpadmin = WP_CONTENT_DIR . '/bps-backup/wpadmin.htaccess';
	
	$selected_radio = $_POST['selection9'];
	if ($selected_radio == 'backup_htaccess') {
	$backup_htaccess = 'checked';
	if ( !file_exists($old_backroot)) { 
	_e('<font color="red"><strong>You do not currently have an .htaccess file in your Root folder to backup.</strong></font><br>');
	} else {	
	if (file_exists($old_backroot)) { 
 		@copy($old_backroot, $new_backroot);
		if (!copy($old_backroot, $new_backroot)) {
	_e('<font color="red"><strong>Failed to Backup Your Root .htaccess File! File copy function failed. Check the folder permissions for the /wp-content/bps-backup folder. Folder permissions should be set to 755.</strong></font><br><br>');
	} else {
	_e('<font color="green"><strong>Your currently active Root .htaccess file has been backed up successfully!</strong></font><br>Use the Restore feature to restore your .htaccess files if you run into a problem at any time. If you make additional changes or install a plugin that writes to the htaccess files then back them up again. This will overwrite the currently backed up htaccess files. Please read the <font color="red"><strong>CAUTION:</strong></font> Read Me button on the Backup & Restore Page for more detailed information.<br><br>');
	
	if ( !file_exists($old_backwpadmin)) { 
	_e('<font color="red"><strong>You do not currently have an .htaccess file in your wp-admin folder to backup.</strong></font><br>');
	} else {
	if (file_exists($old_backwpadmin)) { 	
		@copy($old_backwpadmin, $new_backwpadmin);
		if (!copy($old_backwpadmin, $new_backwpadmin)) {
	_e('<font color="red"><strong>Failed to Backup Your wp-admin .htaccess File! File copy function failed. Check the folder permissions for the /wp-content/bps-backup folder. Folder permissions should be set to 755.</strong></font><br>');
	} else {
	_e('<font color="green"><strong>Your currently active wp-admin .htaccess file has been backed up successfully!</strong></font><br>');
	}
}}}}}}}

// Form - Restore backed up htaccess files
$restore_htaccess = 'unchecked';
if (isset($_POST['submit10']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_restore_active_htaccess_files' );
	
	$old_restoreroot = WP_CONTENT_DIR . '/bps-backup/root.htaccess';
	$new_restoreroot = ABSPATH . '/.htaccess';
	$old_restorewpadmin = WP_CONTENT_DIR . '/bps-backup/wpadmin.htaccess';
	$new_restorewpadmin = ABSPATH . '/wp-admin/.htaccess';
	
	$selected_radio = $_POST['selection10'];
	if ($selected_radio == 'restore_htaccess') {
		$restore_htaccess = 'checked';
	if (file_exists($old_restoreroot)) { 
 		@copy($old_restoreroot, $new_restoreroot);
		if (!copy($old_restoreroot, $new_restoreroot)) {
	_e('<font color="red"><strong>Failed to Restore Your Root .htaccess File! This is most likely because you DO NOT currently have a Backed up Root .htaccess file.</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>Your Root .htaccess file has been Restored successfully!</strong></font><br>');
	if (file_exists($old_restorewpadmin)) { 	
		@copy($old_restorewpadmin, $new_restorewpadmin);
		if (!copy($old_restorewpadmin, $new_restorewpadmin)) {
	_e('<font color="red"><strong>Failed to Restore Your wp-admin .htaccess File! This is most likely because you DO NOT currently have a Backed up wp-admin .htaccess file.</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>Your wp-admin .htaccess file has been Restored successfully!</strong></font><br>');
	}
}}}}}

// Form - Backup the BPS Master Files to /wp-content/bps-backup/master-backups
$backup_master_htaccess_files = 'unchecked';
if (isset($_POST['submit11']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_backup_master_htaccess_files' );

$default_master = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';
$default_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_default.htaccess';
$secure_master = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
$secure_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_secure.htaccess';
$wpadmin_master = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
$wpadmin_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_wpadmin-secure.htaccess';
$maintenance_master = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
$maintenance_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_maintenance.htaccess';
$bp_maintenance_master = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bp-maintenance.php';
$bp_maintenance_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bp-maintenance.php';
$bps_maintenance_values = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
$bps_maintenance_values_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bps-maintenance-values.php';

	$selected_radio = $_POST['selection11'];
	if ($selected_radio == 'backup_master_htaccess_files') {
		$backup_master_htaccess_files = 'checked';
	if (file_exists($default_master)) { 
 		copy($default_master, $default_master_backup);
		if (!copy($default_master, $default_master_backup)) {
	_e('<font color="red"><strong>Failed to Backup Your default.htaccess File!</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>The default.htaccess file has been backed up successfully!</strong></font><br>');
	}
	if (file_exists($secure_master)) { 	
		copy($secure_master, $secure_master_backup);
		if (!copy($secure_master, $secure_master_backup)) {
	_e('<font color="red"><strong>Failed to Backup Your secure.htaccess File!</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>The secure.htaccess file has been backed up successfully!</strong></font><br>');
	}
	if (file_exists($wpadmin_master)) { 	
		copy($wpadmin_master, $wpadmin_master_backup);
		if (!copy($wpadmin_master, $wpadmin_master_backup)) {
	_e('<font color="red"><strong>Failed to Backup Your wpadmin-secure.htaccess File!</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>The wpadmin-secure.htaccess file has been backed up successfully!</strong></font><br>');
	}
	if (file_exists($maintenance_master)) { 	
		copy($maintenance_master, $maintenance_master_backup);
		if (!copy($maintenance_master, $maintenance_master_backup)) {
	_e('<font color="red"><strong>Failed to Backup Your maintenance.htaccess File!</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>The maintenance.htaccess file has been backed up successfully!</strong></font><br>');
	}
	if (file_exists($bp_maintenance_master)) { 	
		copy($bp_maintenance_master, $bp_maintenance_master_backup);
		if (!copy($bp_maintenance_master, $bp_maintenance_master_backup)) {
	_e('<font color="red"><strong>Failed to Backup Your bp-maintenance.php File!</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>The bp-maintenance.php file has been backed up successfully!</strong></font><br>');
	}
	if (file_exists($bps_maintenance_values)) { 	
		copy($bps_maintenance_values, $bps_maintenance_values_backup);
		if (!copy($bps_maintenance_values, $bps_maintenance_values_backup)) {
	_e('<font color="red"><strong>Failed to Backup Your bps-maintenance-values.php File!</strong></font><br>');
   	} else {
	_e('<font color="green"><strong>The bps-maintenance-values.php file has been backed up successfully!</strong></font><br>');
	}
}}}}}}}}
	
// Form - Activate Maintenance Mode copy and rename maintenance htaccess, bp-maintenance.php and bps-maintenance-values.php to root
$bpmaintenance = 'unchecked';
if (isset($_POST['submit15']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_maintenance_copy' );

$oldmaint = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
$newmaint = ABSPATH . '/.htaccess';
$oldmaint1 = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bp-maintenance.php';
$newmaint1 = ABSPATH . '/bp-maintenance.php';
$oldmaint_values = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
$newmaint_values = ABSPATH . '/bps-maintenance-values.php';

	$selected_radio = $_POST['selection15'];
	if ($selected_radio == 'bpmaintenance') {
	$bpmaintenance = 'checked';
		@copy($oldmaint, $newmaint);
		chmod($newmaint, 0644);
		copy($oldmaint1, $newmaint1);
		copy($oldmaint_values, $newmaint_values);
		if (!copy($oldmaint, $newmaint)) {
	_e('<p><font color="red"><strong>Failed to Activate Maintenance Mode! Your Website is NOT in Maintenance Mode!<br>If your Root .htaccess file is locked you must unlock it first before activating Maintenance Mode.</strong></font></p>');
   	} else {
	_e('<font color="red"><strong>Warning: </strong></font>Maintenance Mode Is Activated. Your website is now displaying the Website Under Maintenance page to everyone except you. To switch out of Maintenance mode activate BulletProof Security Mode. You can log in and out of your Dashboard / WordPress website in Maintenance Mode as long as your current IP address does not change. If your current IP address changes you will have to FTP to your website and delete the .htaccess file in your website root folder (or download the .htaccess file and add your new IP address and upload it back to your root website folder) to be able to log back into your WordPress Dashboard. Your ISP provides your current Public IP address. If you reboot your computer or disconnect from the Internet there is a good chance that you will get a new Public IP address from your ISP.');
	}
	}
}	

// default.htaccess and secure.htaccess fwrite content for all WP site types
$bps_get_wp_root_default = bps_wp_get_root_folder();
$bps_auto_write_default_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';

$bpsSuccessMessageDef = '<font color="green"><strong>Success! Your Default Mode Master htaccess file was created successfully!</strong></font><br><font color="red"><strong>CAUTION: Default Mode should only be activated for testing or troubleshooting purposes. Default Mode does not protect your website with any security protection.</strong></font><br><font color="black"><strong>To activate Default Mode select the Default Mode radio button and click Activate to put your website in Default Mode.</strong></font>';

$bpsFailMessageDef = '<font color="red"><strong>The file ' . "$bps_auto_write_default_file" . ' is not writable or does not exist.</strong></font><br><strong>Check that the file is named default.htaccess and that the file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click <a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here</a> for more help info.</strong><br>';

$bpsBeginWP = "\nRewriteEngine On
RewriteBase $bps_get_wp_root_default
RewriteRule ^index\.php$ - [L]\n\n";

$bps_default_content_top = "#   BULLETPROOF PRO 5.D DEFAULT .HTACCESS      \n
# If you edit the line of code above you will see error messages on the BPS Security Status page
# WARNING!!! THE default.htaccess FILE DOES NOT PROTECT YOUR WEBSITE AGAINST HACKERS
# This is a standard generic htaccess file that does NOT provide any website security
# The DEFAULT .HTACCESS file should be used for testing and troubleshooting purposes only\n
# BEGIN WordPress";

$bps_default_content_bottom = "\n<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase $bps_get_wp_root_default
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . $bps_get_wp_root_default"."index.php [L]
</IfModule>\n
# END WordPress";

$bpsMUEndWP = "# END WordPress";

$bpsMUSDirTop = "# uploaded files
RewriteRule ^([_0-9a-zA-Z-]+/)?files/(.+) wp-includes/ms-files.php?file=$2 [L]\n
# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]\n\n";

$bpsMUSDomTop = "# uploaded files
RewriteRule ^files/(.+) wp-includes/ms-files.php?file=$1 [L]\n\n";

$bpsMUSDirBottom = "RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule  ^[_0-9a-zA-Z-]+/(wp-(content|admin|includes).*) $1 [L]
RewriteRule  ^[_0-9a-zA-Z-]+/(.*\.php)$ $1 [L]
RewriteRule . index.php [L]\n\n";

$bpsMUSDomBottom = "RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule . index.php [L]\n\n";

// Create Default htaccess file - Single Site
if (isset($_POST['bps-auto-write-default']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_default' );

	if (is_writable($bps_auto_write_default_file)) {
	if (!$handle = fopen($bps_auto_write_default_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_default_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_default_content_top.$bps_default_content_bottom) === FALSE) {
    	_e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_default_file" . '</strong></font>');
        exit;
    }
    _e($bpsSuccessMessageDef);
    fclose($handle);
	} else {
    _e($bpsFailMessageDef);
	}
}

// Create Default htaccess file - MU Subdirectory
if (isset($_POST['bps-auto-write-default-MUSDir']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_default_MUSDir' );

	if (is_writable($bps_auto_write_default_file)) {
	if (!$handle = fopen($bps_auto_write_default_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_default_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_default_content_top.$bpsBeginWP.$bpsMUSDirTop.$bpsMUSDirBottom.$bpsMUEndWP) === FALSE) {
    	_e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_default_file" . '</strong></font>');
        exit;
    }
    _e($bpsSuccessMessageDef);
    fclose($handle);
	} else {
    _e($bpsFailMessageDef);
	}
}

// Create Default htaccess file - MU Subdomain
if (isset($_POST['bps-auto-write-default-MUSDom']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_default_MUSDom' );

	if (is_writable($bps_auto_write_default_file)) {
	if (!$handle = fopen($bps_auto_write_default_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_default_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_default_content_top.$bpsBeginWP.$bpsMUSDomTop.$bpsMUSDomBottom.$bpsMUEndWP) === FALSE) {
    	_e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_default_file" . '</strong></font>');
        exit;
    }
    _e($bpsSuccessMessageDef);
    fclose($handle);
	} else {
    _e($bpsFailMessageDef);
	}
}

// secure.htaccess fwrite content for all WP site types
$bps_get_wp_root_secure = bps_wp_get_root_folder();
$bps_auto_write_secure_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';

$bpsSuccessMessageSec = '<font color="green"><strong>Success! Your BulletProof Security Root Master htaccess file was created successfully!</strong></font><br><font color="black"><strong>You can now Activate BulletProof Mode for your Root folder. Select the BulletProof Mode radio button and click Activate to put your website in BulletProof Mode.</strong></font>';

$bpsFailMessageSec = '<font color="red"><strong>The file ' . "$bps_auto_write_secure_file" . ' is not writable or does not exist.</strong></font><br><strong>Check that the file is named secure.htaccess and that the file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click <a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here</a> for more help info.</strong><br>';

$bps_secure_content_top = "#   BULLETPROOF .46.9 >>>>>>> SECURE .HTACCESS     \n
# If you edit the  BULLETPROOF .46.9 >>>>>>> SECURE .HTACCESS text above
# you will see error messages on the BPS Security Status page
# BPS is reading the version number in the htaccess file to validate checks
# If you would like to change what is displayed above you
# will need to edit the BPS /includes/functions.php file to match your changes
# If you update your WordPress Permalinks the code between BEGIN WordPress and
# END WordPress is replaced by WP htaccess code.
# This removes all of the BPS security code and replaces it with just the default WP htaccess code
# To restore this file use BPS Restore or activate BulletProof Mode for your Root folder again.\n
# BEGIN WordPress
# IMPORTANT!!! DO NOT DELETE!!! - BEGIN Wordpress above or END WordPress - text in this file
# They are reference points for WP, BPS and other plugins to write to this htaccess file.
# IMPORTANT!!! DO NOT DELETE!!! - BPSQSE BPS QUERY STRING EXPLOITS - text
# BPS needs to find the - BPSQSE - text string in this file to validate that your security filters exist\n
# TURN OFF YOUR SERVER SIGNATURE
ServerSignature Off\n
# ADD A PHP HANDLER
# If you are using a PHP Handler add your web hosts PHP Handler below\n\n";

$options = get_option('bulletproof_security_options_customcode');
if ($options['bps_customcode_one'] != '') {
$bpsCCTop = 'CustomCodeOne';
}
// AutoMagic - CUSTOM CODE TOP
switch ($bpsCCTop) {
	case "CustomCodeOne":
        $phpiniHCode = "# CUSTOM CODE TOP - Your Custom .htaccess code will be created here with AutoMagic\n".htmlspecialchars_decode($options['bps_customcode_one'])."\n\n";
		break;
	default:
		$phpiniHCode = "# CUSTOM CODE TOP - Your Custom .htaccess code will be created here with AutoMagic\n\n";
}

$bps_secure_content_top_two = "# DO NOT SHOW DIRECTORY LISTING
# If you are getting 500 Errors when activating BPS then comment out Options -Indexes 
# by adding a # sign in front of it. If there is a typo anywhere in this file you will also see 500 errors.
Options -Indexes\n
# DIRECTORY INDEX FORCE INDEX.PHP
# Use index.php as default directory index file
# index.html will be ignored will not load.
DirectoryIndex index.php index.html /index.php\n
# BPS PRO ERROR LOGGING AND TRACKING - Available in BPS Pro only
# BPS Pro has premade 403 Forbidden, 400 Bad Request and 404 Not Found files that are used 
# to track and log 403, 400 and 404 errors that occur on your website. When a hacker attempts to
# hack your website the hackers IP address, Host name, Request Method, Referering link, the file name or
# requested resource, the user agent of the hacker and the query string used in the hack attempt are logged.
# BPS Pro Log files are added to the P-Security All Purpose File Manager to view them.
# All BPS Pro log files are htaccess protected so that only you can view them. 
# The 400.php, 403.php and 404.php files are located in /wp-content/plugins/bulletproof-security/
# The 400 and 403 Error logging files are already set up and will automatically start logging errors
# after you install BPS Pro and have activated BulletProof Mode for your Root folder.
# If you would like to log 404 errors you will need to copy the logging code in the BPS Pro 404.php file
# to your Theme's 404.php template file. Simple instructions are included in the BPS Pro 404.php file.
# You can open the BPS Pro 404.php file using the WP Plugins Editor or by using the BPS Pro File Manager.
# NOTE: By default WordPress automatically looks in your Theme's folder for a 404.php template file.\n
# ErrorDocument 400 $bps_get_wp_root_secure"."wp-content/plugins/bulletproof-security/400.php
# ErrorDocument 403 $bps_get_wp_root_secure"."wp-content/plugins/bulletproof-security/403.php
ErrorDocument 404 $bps_get_wp_root_secure"."404.php\n
# DENY ACCESS TO PROTECTED SERVER FILES - .htaccess, .htpasswd and all file names starting with dot
RedirectMatch 403 /\..*$\n\n";

$bps_secure_content_wpadmin = "RewriteEngine On
RewriteBase $bps_get_wp_root_secure
RewriteRule ^wp-admin/includes/ - [F,L]
RewriteRule !^wp-includes/ - [S=3]
RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]
RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]
RewriteRule ^wp-includes/theme-compat/ - [F,L]\n";

$bps_secure_content_mid_top = "# REQUEST METHODS FILTERED
# This filter is for blocking junk bots and spam bots from making a HEAD request, but may also block some
# HEAD request from bots that you want to allow in certains cases. This is not a security filter and is just
# a nuisance filter. This filter will not block any important bots like the google bot. If you want to allow
# all bots to make a HEAD request then remove HEAD from the Request Method filter.
# The TRACE, DELETE, TRACK and DEBUG request methods should never be allowed against your website.
RewriteEngine On
RewriteCond %{REQUEST_METHOD} ^(HEAD|TRACE|DELETE|TRACK|DEBUG) [NC]
RewriteRule ^(.*)$ - [F,L]\n
# PLUGINS AND VARIOUS EXPLOIT FILTER SKIP RULES
# IMPORTANT!!! If you add or remove a skip rule you must change S= to the new skip number
# Example: If RewriteRule S=5 is deleted than change S=6 to S=5, S=7 to S=6, etc.\n\n";

// AutoMagic - CUSTOM CODE PLUGIN FIXES
$options = get_option('bulletproof_security_options_customcode');
if ($options['bps_customcode_two'] != '') {
$CustomCodeTwo = "# CUSTOM CODE PLUGIN FIXES - Your plugin fixes .htaccess code will be created here with AutoMagic\n".htmlspecialchars_decode($options['bps_customcode_two'])."\n\n";
}

$bps_secure_content_mid_top2 = "# Adminer MySQL management tool data populate
RewriteCond %{REQUEST_URI} ^$bps_get_wp_root_secure"."wp-content/plugins/adminer/ [NC]
RewriteRule . - [S=12]
# Comment Spam Pack MU Plugin - CAPTCHA images not displaying 
RewriteCond %{REQUEST_URI} ^$bps_get_wp_root_secure"."wp-content/mu-plugins/custom-anti-spam/ [NC]
RewriteRule . - [S=11]
# Peters Custom Anti-Spam display CAPTCHA Image
RewriteCond %{REQUEST_URI} ^$bps_get_wp_root_secure"."wp-content/plugins/peters-custom-anti-spam-image/ [NC] 
RewriteRule . - [S=10]
# Status Updater plugin fb connect
RewriteCond %{REQUEST_URI} ^$bps_get_wp_root_secure"."wp-content/plugins/fb-status-updater/ [NC] 
RewriteRule . - [S=9]
# Stream Video Player - Adding FLV Videos Blocked
RewriteCond %{REQUEST_URI} ^$bps_get_wp_root_secure"."wp-content/plugins/stream-video-player/ [NC]
RewriteRule . - [S=8]
# XCloner 404 or 403 error when updating settings
RewriteCond %{REQUEST_URI} ^$bps_get_wp_root_secure"."wp-content/plugins/xcloner-backup-and-restore/ [NC]
RewriteRule . - [S=7]
# BuddyPress Logout Redirect
RewriteCond %{QUERY_STRING} action=logout&redirect_to=http%3A%2F%2F(.*) [NC]
RewriteRule . - [S=6]
# redirect_to=
RewriteCond %{QUERY_STRING} redirect_to=(.*) [NC]
RewriteRule . - [S=5]
# Login Plugins Password Reset And Redirect 1
RewriteCond %{QUERY_STRING} action=resetpass&key=(.*) [NC]
RewriteRule . - [S=4]
# Login Plugins Password Reset And Redirect 2
RewriteCond %{QUERY_STRING} action=rp&key=(.*) [NC]
RewriteRule . - [S=3]\n
# TimThumb Forbid RFI By Host Name But Allow Internal Requests
RewriteCond %{QUERY_STRING} ^.*(http|https|ftp)(%3A|:)(%2F|/)(%2F|/)(w){0,3}.?(blogger|picasa|blogspot|tsunami|petapolitik|photobucket|imgur|imageshack|wordpress\.com|img\.youtube|tinypic\.com|upload\.wikimedia|kkc|start-thegame).*$ [NC,OR]
RewriteCond %{THE_REQUEST} ^.*(http|https|ftp)(%3A|:)(%2F|/)(%2F|/)(w){0,3}.?(blogger|picasa|blogspot|tsunami|petapolitik|photobucket|imgur|imageshack|wordpress\.com|img\.youtube|tinypic\.com|upload\.wikimedia|kkc|start-thegame).*$ [NC]
RewriteRule .* index.php [F,L]
RewriteCond %{REQUEST_URI} (timthumb\.php|phpthumb\.php|thumb\.php|thumbs\.php) [NC]
RewriteRule . - [S=1]\n
# BPSQSE BPS QUERY STRING EXPLOITS
# The libwww-perl User Agent is forbidden - Many bad bots use libwww-perl modules, but some good bots use it too.
# Good sites such as W3C use it for their W3C-LinkChecker. 
# Add or remove user agents temporarily or permanently from the first User Agent filter below.
# If you want a list of bad bots / User Agents to block then scroll to the end of this file.
RewriteCond %{HTTP_USER_AGENT} (libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]
RewriteCond %{HTTP_USER_AGENT} (%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{HTTP_USER_AGENT} (;|<|>|'|".'"'."|\)|\(|%0A|%0D|%22|%27|%28|%3C|%3E|%00).*(libwww-perl|wget|python|nikto|curl|scan|java|winhttp|HTTrack|clshttp|archiver|loader|email|harvest|extract|grab|miner) [NC,OR]
RewriteCond %{THE_REQUEST} \?\ HTTP/ [NC,OR]
RewriteCond %{THE_REQUEST} \/\*\ HTTP/ [NC,OR]
RewriteCond %{THE_REQUEST} etc/passwd [NC,OR]
RewriteCond %{THE_REQUEST} cgi-bin [NC,OR]
RewriteCond %{THE_REQUEST} (%0A|%0D|\\"."\\"."r|\\"."\\"."n) [NC,OR]
RewriteCond %{REQUEST_URI} owssvr\.dll [NC,OR]
RewriteCond %{HTTP_REFERER} (%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{HTTP_REFERER} \.opendirviewer\. [NC,OR]
RewriteCond %{HTTP_REFERER} users\.skynet\.be.* [NC,OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=http:// [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=(\.\.//?)+ [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=/([a-z0-9_.]//?)+ [NC,OR]
RewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC,OR]
RewriteCond %{QUERY_STRING} (\.\./|\.\.) [OR]
RewriteCond %{QUERY_STRING} ftp\: [NC,OR]
RewriteCond %{QUERY_STRING} http\: [NC,OR] 
RewriteCond %{QUERY_STRING} https\: [NC,OR]
RewriteCond %{QUERY_STRING} \=\|w\| [NC,OR]
RewriteCond %{QUERY_STRING} ^(.*)/self/(.*)$ [NC,OR]
RewriteCond %{QUERY_STRING} ^(.*)cPath=http://(.*)$ [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*embed.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^e]*e)+mbed.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*object.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^o]*o)+bject.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*iframe.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^i]*i)+frame.*(>|%3E) [NC,OR] 
RewriteCond %{QUERY_STRING} base64_encode.*\(.*\) [NC,OR]
RewriteCond %{QUERY_STRING} base64_(en|de)code[^(]*\([^)]*\) [NC,OR]
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} ^.*(\[|\]|\(|\)|<|>|%3c|%3e|%5b|%5d).* [NC,OR]
RewriteCond %{QUERY_STRING} ^.*(\\x00|\\x04|\\x08|\\x0d|\\x1b|\\x20|\\x3c|\\x3e|\\x5b|\\x5d|\\x7f).* [NC,OR]
RewriteCond %{QUERY_STRING} (NULL|OUTFILE|LOAD_FILE) [OR]
RewriteCond %{QUERY_STRING} (\./|\../|\.../)+(motd|etc|bin) [NC,OR]
RewriteCond %{QUERY_STRING} (localhost|loopback|127\.0\.0\.1) [NC,OR]
RewriteCond %{QUERY_STRING} (<|>|'|%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{QUERY_STRING} concat[^\(]*\( [NC,OR]
RewriteCond %{QUERY_STRING} union([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} (;|<|>|'|".'"'."|\)|%0A|%0D|%22|%27|%3C|%3E|%00).*(/\*|union|select|insert|drop|delete|update|cast|create|char|convert|alter|declare|order|script|set|md5|benchmark|encode) [NC,OR]
RewriteCond %{QUERY_STRING} (sp_executesql) [NC]
RewriteRule ^(.*)$ - [F,L]\n";

$bps_secure_content_mid_bottom = "RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . $bps_get_wp_root_secure"."index.php [L]\n\n";

$bps_secure_content_bottom = "# DENY BROWSER ACCESS TO THESE FILES 
# wp-config.php, bb-config.php, php.ini, php5.ini, readme.html
# Replace Allow from 88.77.66.55 with your current IP address and remove the  
# pound sign # from in front of the Allow from line of code below to access these
# files directly from your browser.\n
<FilesMatch ".'"'."^(wp-config\.php|php\.ini|php5\.ini|readme\.html|bb-config\.php)".'"'.">
Order allow,deny
Deny from all
#Allow from 88.77.66.55
</FilesMatch>\n
# IMPORTANT!!! DO NOT DELETE!!! the END WordPress text below
# END WordPress\n\n";

// AutoMagic - CUSTOM CODE BOTTOM
$options = get_option('bulletproof_security_options_customcode');
if ($options['bps_customcode_three'] != '') {
$CustomCodeThree = "# CUSTOM CODE BOTTOM - Your Custom .htaccess code will be created here with AutoMagic\n".htmlspecialchars_decode($options['bps_customcode_three'])."\n\n";
}

$bps_secure_content_bottom2 = "# BLOCK HOTLINKING TO IMAGES
# To Test that your Hotlinking protection is working visit http://altlab.com/htaccess_tutorial.html
#RewriteEngine On
#RewriteCond %{HTTP_REFERER} !^https?://(www\.)?add-your-domain-here\.com [NC]
#RewriteCond %{HTTP_REFERER} !^$
#RewriteRule .*\.(jpeg|jpg|gif|bmp|png)$ - [F]\n
# BLOCK MORE BAD BOTS RIPPERS AND OFFLINE BROWSERS
# If you would like to block more bad bots you can get a blacklist from
# http://perishablepress.com/press/2007/06/28/ultimate-htaccess-blacklist/
# You should monitor your site very closely for at least a week if you add a bad bots list
# to see if any website traffic problems or other problems occur.
# Copy and paste your bad bots user agent code list directly below.";

// Create maintenance htaccess file
if (isset($_POST['bps-auto-write-maint']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_maint' );
	
$bps_string_replace_maint = array(".");
$bps_get_IP_maint = str_replace($bps_string_replace_maint, "\.", $_SERVER['REMOTE_ADDR']) . "$";
$bps_get_wp_root_maint = bps_wp_get_root_folder();
$bps_auto_write_maint_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
$bps_maint_top = "#   BULLETPROOF .46.9 MAINTENANCE  .HTACCESS     \n\n";    
$bps_maint_content = "RewriteEngine On
RewriteBase $bps_get_wp_root_maint\n
RewriteCond %{REQUEST_METHOD} ^(HEAD|TRACE|DELETE|TRACK|DEBUG) [NC]
RewriteRule ^(.*)$ - [F,L]\n
# TIMTHUMB FORBID RFI BY HOST NAME BUT ALLOW INTERNAL REQUESTS
RewriteCond %{QUERY_STRING} ^.*(http|https|ftp)(%3A|:)(%2F|/)(%2F|/)(w){0,3}.?(blogger|picasa|blogspot|tsunami|petapolitik|photobucket|imgur|imageshack|wordpress\.com|img\.youtube|tinypic\.com|upload\.wikimedia|kkc|start-thegame).*$ [NC,OR]
RewriteCond %{THE_REQUEST} ^.*(http|https|ftp)(%3A|:)(%2F|/)(%2F|/)(w){0,3}.?(blogger|picasa|blogspot|tsunami|petapolitik|photobucket|imgur|imageshack|wordpress\.com|img\.youtube|tinypic\.com|upload\.wikimedia|kkc|start-thegame).*$ [NC]
RewriteRule .* index.php [F,L]
RewriteCond %{REQUEST_URI} (timthumb\.php|phpthumb\.php|thumb\.php|thumbs\.php) [NC]
RewriteRule . - [S=1]\n
# BPSQSE BPS QUERY STRING EXPLOITS
RewriteCond %{HTTP_USER_AGENT} (libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]
RewriteCond %{HTTP_USER_AGENT} (%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{HTTP_USER_AGENT} (;|<|>|'|".'"'."|\)|\(|%0A|%0D|%22|%27|%28|%3C|%3E|%00).*(libwww-perl|wget|python|nikto|curl|scan|java|winhttp|HTTrack|clshttp|archiver|loader|email|harvest|extract|grab|miner) [NC,OR]
RewriteCond %{THE_REQUEST} \?\ HTTP/ [NC,OR]
RewriteCond %{THE_REQUEST} \/\*\ HTTP/ [NC,OR]
RewriteCond %{THE_REQUEST} etc/passwd [NC,OR]
RewriteCond %{THE_REQUEST} cgi-bin [NC,OR]
RewriteCond %{REQUEST_URI} owssvr\.dll [NC,OR]
RewriteCond %{HTTP_REFERER} (%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{HTTP_REFERER} \.opendirviewer\. [NC,OR]
RewriteCond %{HTTP_REFERER} users\.skynet\.be.* [NC,OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=http:// [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=(\.\.//?)+ [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=/([a-z0-9_.]//?)+ [NC,OR]
RewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC,OR]
RewriteCond %{QUERY_STRING} (\.\./|\.\.) [OR]
RewriteCond %{QUERY_STRING} ftp\: [NC,OR]
RewriteCond %{QUERY_STRING} http\: [NC,OR] 
RewriteCond %{QUERY_STRING} https\: [NC,OR]
RewriteCond %{QUERY_STRING} \=\|w\| [NC,OR]
RewriteCond %{QUERY_STRING} ^(.*)/self/(.*)$ [NC,OR]
RewriteCond %{QUERY_STRING} ^(.*)cPath=http://(.*)$ [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*iframe.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^i]*i)+frame.*(>|%3E) [NC,OR] 
RewriteCond %{QUERY_STRING} base64_encode.*\(.*\) [NC,OR]
RewriteCond %{QUERY_STRING} base64_(en|de)code[^(]*\([^)]*\) [NC,OR]
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} ^.*(\[|\]|\(|\)|<|>).* [NC,OR]
RewriteCond %{QUERY_STRING} (NULL|OUTFILE|LOAD_FILE) [OR]
RewriteCond %{QUERY_STRING} (\./|\../|\.../)+(motd|etc|bin) [NC,OR]
RewriteCond %{QUERY_STRING} (localhost|loopback|127\.0\.0\.1) [NC,OR]
RewriteCond %{QUERY_STRING} (<|>|'|%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{QUERY_STRING} concat[^\(]*\( [NC,OR]
RewriteCond %{QUERY_STRING} union([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} (;|<|>|'|".'"'."|\)|%0A|%0D|%22|%27|%3C|%3E|%00).*(/\*|union|select|insert|drop|delete|update|cast|create|char|convert|alter|declare|order|script|set|md5|benchmark|encode) [NC,OR]
RewriteCond %{QUERY_STRING} (sp_executesql) [NC]
RewriteRule ^(.*)$ - [F,L]\n
RewriteCond %{REMOTE_ADDR} !^$bps_get_IP_maint
RewriteCond %{REQUEST_URI} !^$bps_get_wp_root_maint"."bp-maintenance\.php$
RewriteCond %{REQUEST_URI} !^$bps_get_wp_root_maint"."wp-content/plugins/bulletproof-security/abstract-blue-bg\.png$
RewriteRule ^(.*)$ $bps_get_wp_root_maint"."bp-maintenance.php [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . $bps_get_wp_root_maint"."index.php [L]";
	if (is_writable($bps_auto_write_maint_file)) {
	if (!$handle = fopen($bps_auto_write_maint_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_maint_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_maint_top.$phpiniHCode.$bps_maint_content) === FALSE) {
		_e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_maint_file" . '</strong></font>');
        exit;
    }
    _e('<font color="green"><strong>Success! Your Maintenance Mode htaccess file was created successfully! Select the Maintenance Mode radio button and click Activate to put your website in Maintenance Mode.</strong></font>');
    fclose($handle);
	} else {
    _e('<font color="red"><strong>The file ' . "$bps_auto_write_maint_file" . ' is not writable or does not exist.</strong></font><br><strong>Check that the file is named maintenance.htaccess and that the file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click <a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here</a> for more help info.</strong><br>');
	}
}

// Create Secure htaccess master Root file - Single Site
if (isset($_POST['bps-auto-write-secure-root']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_secure_root' );

	if (is_writable($bps_auto_write_secure_file)) {
	if (!$handle = fopen($bps_auto_write_secure_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_secure_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_secure_content_top.$phpiniHCode.$bps_secure_content_top_two.$bps_secure_content_wpadmin.$bpsBeginWP.$bps_secure_content_mid_top.$CustomCodeTwo.$bps_secure_content_mid_top2.$bps_secure_content_mid_bottom.$bps_secure_content_bottom.$CustomCodeThree.$bps_secure_content_bottom2) === FALSE) {
        _e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_secure_file" . '</strong></font>');
        exit;
    }
    _e($bpsSuccessMessageSec);
    fclose($handle);
	} else {
    _e($bpsFailMessageSec);
	}
}

// Create Secure htaccess master Root file - MU Subdirectory
if (isset($_POST['bps-auto-write-secure-root-MUSDir']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_secure_root_MUSDir' );

	if (is_writable($bps_auto_write_secure_file)) {
	if (!$handle = fopen($bps_auto_write_secure_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_secure_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_secure_content_top.$phpiniHCode.$bps_secure_content_top_two.$bpsBeginWP.$bpsMUSDirTop.$bps_secure_content_mid_top.$CustomCodeTwo.$bps_secure_content_mid_top2.$bpsMUSDirBottom.$bps_secure_content_bottom.$CustomCodeThree.$bps_secure_content_bottom2) === FALSE) {
        _e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_secure_file" . '</strong></font>');
        exit;
    }
    _e($bpsSuccessMessageSec);
    fclose($handle);
	} else {
    _e($bpsFailMessageSec);
	}
}

// Create Secure htaccess master Root file - MU Subdomain
if (isset($_POST['bps-auto-write-secure-root-MUSDom']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_secure_MUSDom' );

	if (is_writable($bps_auto_write_secure_file)) {
	if (!$handle = fopen($bps_auto_write_secure_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_secure_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_secure_content_top.$phpiniHCode.$bps_secure_content_top_two.$bpsBeginWP.$bpsMUSDomTop.$bps_secure_content_mid_top.$CustomCodeTwo.$bps_secure_content_mid_top2.$bpsMUSDomBottom.$bps_secure_content_bottom.$CustomCodeThree.$bps_secure_content_bottom2) === FALSE) {
        _e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_secure_file" . '</strong></font>');
        exit;
    }
    _e($bpsSuccessMessageSec);
    fclose($handle);
	} else {
    _e($bpsFailMessageSec);
	}
}

// Create the Maintenance Mode Settings Values Form File - values from DB
if (isset($_POST['bps-maintenance-create-values_submit']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_create_values_form' );
	
	$options = get_option('bulletproof_security_options_maint');
	$bps_retry_after_write = $options['bps-retry-after'];
	$bps_site_title_write = $options['bps-site-title'];
	$bps_message1_write = $options['bps-message-1'];
	$bps_message2_write = $options['bps-message-2'];
	$bps_body_background_image_write = $options['bps-background-image'];

	$bps_auto_write_maint_file_form = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
$bps_maint_content_form = "<?php".'
$bps_retry_after'." = '$bps_retry_after_write';\n"
.'$bps_site_title'." = '$bps_site_title_write';\n"
.'$bps_message1'." = '$bps_message1_write';\n"
.'$bps_message2'." = '$bps_message2_write';\n"
.'$bps_body_background_image'." = '$bps_body_background_image_write';
?>";
	if (is_writable($bps_auto_write_maint_file_form)) {
	if (!$handle = fopen($bps_auto_write_maint_file_form, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$bps_auto_write_maint_file_form" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_maint_content_form) === FALSE) {
        _e('<font color="red"><strong>Cannot write to file' . "$bps_auto_write_maint_file_form" . '</strong></font>');
        exit;
    }
    _e('<font color="green"><strong>Success! Your Maintenance Mode Form has been created successfully! Click the Preview button to preview your Website Under Maintenance page.</strong></font>');
    fclose($handle);
	} else {
    _e('<font color="red"><strong>The file ' . "$bps_auto_write_maint_file_form" . ' is not writable or does not exist.</strong></font><br><strong>Check that the bps-maintenance-values.php file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click <a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here</a> for more help info.</strong><br>');
	}
}

// Simple Secure Old School PHP file upload
if (isset($_POST['submit-bps-upload']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_upload' );
	
	$tmp_file = $_FILES['bps_file_upload']['tmp_name'];
	$folder_path = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/';
	$bps_uploaded_file =  str_replace('//','/',$folder_path) . $_FILES['bps_file_upload']['name'];
	if (!empty($_FILES)) {
	move_uploaded_file($tmp_file,$bps_uploaded_file);
		_e('<font color="green"><strong>File Uploaded Successfully To: </strong></font><br>');
		echo "$bps_uploaded_file";
	} else {
		_e('<font color="red"><strong>File upload error. File was not successfully uploaded.</strong></font><br>');
	}
}

// Enable File Downloading for Master Files - writes a new denyall htaccess file with the current IP address
if (isset($_POST['bps-enable-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_enable_download' );
	
	$bps_get_IP = $_SERVER['REMOTE_ADDR'];
	$denyall_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/.htaccess';
	$bps_denyall_content = "order deny,allow\ndeny from all\nallow from $bps_get_IP";
	
	if (is_writable($denyall_htaccess_file)) {
	if (!$handle = fopen($denyall_htaccess_file, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$denyall_htaccess_file" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_denyall_content) === FALSE) {
        _e('<font color="red"><strong>Cannot write to file' . "$denyall_htaccess_file" . '</strong></font>');
        exit;
    }
    _e('<font color="green"><strong>Success! File open, preview and downloading for your BPS Master Files is enabled for your IP address only ===' . "$bps_get_IP." .'</strong></font>');
    fclose($handle);
	} else {
    _e('<font color="red"><strong>The file ' . "$denyall_htaccess_file" . ' is not writable or does not exist yet.</strong></font><br><strong>Check the BPS Status page to see if Deny All protection has been activated. Activate Deny All htaccess Folder Protection For The BPS Master htaccess Folder on the BPS Security Modes page. If this is not the problem click <a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here</a> for more help info.</strong><br>');
	}
}

// Enable File Downloading for BPS Backup Folder - writes a new denyall htaccess file with the current IP address
if (isset($_POST['bps-enable-download-backup']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_enable_download-backup' );
	
	$bps_get_IP2 = $_SERVER['REMOTE_ADDR'];
	$denyall_htaccess_file_backup = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	$bps_denyall_content_backup = "order deny,allow\ndeny from all\nallow from $bps_get_IP2";
	
	if (is_writable($denyall_htaccess_file_backup)) {
	if (!$handle = fopen($denyall_htaccess_file_backup, 'w+b')) {
         _e('<font color="red"><strong>Cannot open file' . "$denyall_htaccess_file_backup" . '</strong></font>');
         exit;
    }
    if (fwrite($handle, $bps_denyall_content_backup) === FALSE) {
        _e('<font color="red"><strong>Cannot write to file' . "$denyall_htaccess_file_backup" . '</strong></font>');
        exit;
    }
    _e('<font color="green"><strong>Success! File open, preview and downloading for your Backed Up htaccess Files is enabled for your IP address only ===' . "$bps_get_IP2." .'</strong></font>');
    fclose($handle);
	} else {
    _e('<font color="red"><strong>The file ' . "$denyall_htaccess_file_backup" . ' is not writable or does not exist yet.</strong></font><br><strong>Check the BPS Status page to see if Deny All protection has been activated. Activate Deny All htaccess Folder Protection For The BPS Backup Folder on the BPS Security Modes page. If this is not the problem click <a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here</a> for more help info.</strong><br>');
	}
}

// Get DNS Name Server from [target] - only using $bpsTargetNS and $bpsTarget variables for BPS Free
// additional BPS Pro variables not used in BPS Free
$bpsHostName = $_SERVER['SERVER_NAME']; 
$bpsGetDNS = @dns_get_record($bpsHostName, DNS_NS);
	$bpsTargetNS = $bpsGetDNS[0]['target'];
	if ($bpsTargetNS != '') {
	preg_match('/[^.]+\.[^.]+$/', $bpsTargetNS, $bpsTmatches);
	$bpsNSHostSubject = $bpsTmatches[0];
	// echo 'DNS_NS -- '.$bpsTargetNS.' -- preg match -- '.$bpsNSHostSubject.'<br>'; // for testing
	}
	
	if ($bpsTargetNS == '') {
	@dns_get_record($bpsHostName, DNS_ALL, $authns, $addtl);
	$bpsTarget = $authns[0]['target'];
	if ($bpsTarget != '') {
	preg_match('/[^.]+\.[^.]+$/', $bpsTarget, $bpsTmatches);
	$bpsNSHostSubject = $bpsTmatches[0];
	// echo 'DNS_ALL -- '.$bpsTarget.' -- preg match -- '.$bpsNSHostSubject.'<br>'; // for testing
	}
	}
	
	if ($bpsTarget && $bpsTargetNS == '') {
	@dns_get_record($bpsHostName, DNS_ANY, $authns, $addtl);
	$bpsTarget = $authns[0]['target'];
	preg_match('/[^.]+\.[^.]+$/', $bpsTarget, $bpsTmatches);
	$bpsNSHostSubject = $bpsTmatches[0];
	// echo 'DNS_ANY -- '.$bpsTarget.' -- preg match -- '.$bpsNSHostSubject.'<br>'; // for testing
	}

// General all purpose "Settings Saved." message for forms
if (current_user_can('manage_options')) {
if (@$_GET['settings-updated'] == true) {
_e('<font color="green"><strong><p>Settings Saved.</p></strong></font>');
}
}
?>
</div>

<!-- jQuery UI Tab Menu -->
<div id="bps-container">
	<div id="bps-tabs" class="bps-menu">
    <div id="bpsHead" style="position:relative; top:0px; left:0px;"><img src="<?php echo get_site_url(); ?>/wp-content/plugins/bulletproof-security/admin/images/bps-security-shield.png" style="float:left; padding:0px 8px 0px 0px; margin:-68px 0px 0px 0px;" /></div>
		<ul>
			<li><a href="#bps-tabs-1">Security Modes</a></li>
            <li><a href="#bps-tabs-2">Security Status</a></li>
			<li><a href="#bps-tabs-3">System Info</a></li>
			<li><a href="#bps-tabs-4">Backup &amp; Restore</a></li>
            <li><a href="#bps-tabs-5">Edit/Upload/Download</a></li>
            <li><a href="#bps-tabs-6">Custom Code</a></li>
			<li><a href="#bps-tabs-7">Maintenance Mode</a></li>
			<li><a href="#bps-tabs-8">Help &amp; FAQ</a></li>
			<li><a href="#bps-tabs-9">Whats New</a></li>
            <li><a href="#bps-tabs-10">My Notes</a></li>
            <li><a href="#bps-tabs-11">BPS Pro Features</a></li>
            <li><a href="#bps-tabs-12">Website Scanner</a></li>
		</ul>
            
<div id="bps-tabs-1" class="bps-tab-page">
<h2><?php _e('BulletProof Security Modes'); ?></h2>

<div id="bpsMonitoringAlerting" style="border-top:1px solid #999999;">

<h3><?php _e('AutoMagic - Create Your htaccess Master Files'); ?>  <button id="bps-open-modal1" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content1" title="<?php _e('AutoMagic'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Backup your existing htaccess files if you have any first by clicking on the Backup & Restore menu tab - click on the Backup htaccess files radio button to select it and click on the Backup Files button to back up your existing htaccess files.</strong><br><br><strong>AutoMagic - BPS Creates Customized .htaccess Master Files For Your Website Automatically</strong><br>BPS detects what type of WordPress installation you have and will tell you which AutoMagic buttons to use for your website.<br><br><strong>BPS Pro AutoMagic: </strong>BPS Pro AutoMagic detects your Web Host and if your Web Host requires custom php.ini handler code, BPS Pro will automatically create this code in your Root .htaccess file - <strong>IF YOUR WEB HOST HAS BEEN ADDED TO THE BPS WEB HOST LIST.</strong> To see the complete BPS Pro Web Host list go to the Help &amp; FAQ page and click on the <strong>AutoMagic php.ini Handler Web Hosts List</strong> link.<br><br> -- Click the <strong>Create default.htaccess File</strong> button.<br> -- Click the <strong>Create secure.htaccess File</strong> button.<br> -- If you would like to view, edit or add any additional .htaccess code to your new secure.htaccess Master file. Click on the Edit/Upload/Download menu tab, click on the secure.htaccess menu tab and make your changes before you Activate BulletProof Mode for your Root folder.<br> -- Activate BulletProof Mode for your Root folder.<br> -- Activate BulletProof Mode for your wp-admin folder.<br> -- Activate BulletProof Mode for the BPS Master htaccess folder.<br> -- Activate BulletProof Mode for the BPS Backup folder.<br><br><strong>WordPress Network (Multisite) Sites Info</strong><br>BPS will automatically detect whether you have a subdomain or subdirectory Network (Multisite) installation and tell you which AutoMagic buttons to use. DO NOT Network Activate BPS. BPS will not work correctly if you choose Network Activate. BPS only needs to be activated and set up on your Primary site to automatically add security protection to all of your sub sites. Network / MU sub sites are virtual and do not really exist in separate website folders. BPS menus will only be displayed to Super Admins.<br><br><strong> Explanation Of The Steps Above and Additional Info:</strong><br>If you see error messages when performing a first time backup do not worry about it. BPS will backup whatever files should be or are available to backup for your website.<br><br>Clicking the <strong>Create default.htaccess File</strong> button and the <strong>Create secure.htaccess File</strong> button will create these two new customized master .htaccess files for your website. The correct RewriteBase and RewriteRule for your website will be automatically added to these files. The default.htaccess file is the master .htaccess file that is copied to your root folder when you Activate Default Mode. Default Mode should only be activated for testing and troubleshooting purposes - it does not provide any website security. The secure.htaccess file is the master .htaccess file that is copied to your Root folder when you Activate BulletProof Mode for your Root folder.<br><br><strong>When you Activate BulletProof Mode for your Root folder it will overwrite the existing Root .htaccess file.</strong> If you have added any custom .htaccess code in your Root .htaccess file you should save that custom code to My Notes. My Notes allows you to permanently save custom .htaccess code or any other notes.<br><br>The plugin conflict fixes in the secure.htaccess master file will also have your correct WordPress installation folder name automatically added to it. The .htaccess file for your wp-admin folder does not require any editing nor do the Deny All htaccess files.<br><br><strong>Editing .htaccess Files - BPS Built-in File Editor</strong><br>BPS has a built-in .htaccess File Editor if you want to edit your .htaccess files manually. Go to the Edit/Upload/Download menu tab.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0">
  <tr>
    <td width="33%"><?php echo bps_multsite_check_smode_single(); ?></td>
    <td width="33%"><?php echo bps_multsite_check_smode_MUSDir(); ?></td>
    <td width="34%"><?php echo bps_multsite_check_smode_MUSDom(); ?></td>
  </tr>
  <tr>
    <td>
    <form name="bps-auto-write-default" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_default'); ?>
<input type="hidden" name="filename" value="bps-auto-write-default_write" />
<p class="submit">
<input type="submit" name="bps-auto-write-default" value="<?php _e('Create default.htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Clicking OK will create a new customized default.htaccess Master file for your website.\n\nThis is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.\n\nNOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.\n\nClick OK to Create your new default.htaccess Master file or click Cancel.'); ?>')" />
</p>
</form>

<form name="bps-auto-write-secure-root" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_root'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write" />
<p class="submit">
<input type="submit" name="bps-auto-write-secure-root" value="<?php _e('Create secure.htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Clicking OK will create a new customized secure.htaccess Master file for your website.\n\nThis is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.\n\nClick OK to Create your new secure.htaccess Master file or click Cancel.'); ?>')" />
</p>
</form>
</td>
    <td>
<form name="bps-auto-write-default-MUSDir" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_default_MUSDir'); ?>
<input type="hidden" name="filename" value="bps-auto-write-default_write-MUSDir" />
<p class="submit">
<input type="submit" name="bps-auto-write-default-MUSDir" value="<?php _e('Create default.htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Clicking OK will create a new customized default.htaccess Master file for your Network / Multisite subdirectory website.\n\nThis is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.\n\nNOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.\n\nClick OK to Create your new default.htaccess Master file or click Cancel.'); ?>')" />
</p>
</form>

<form name="bps-auto-write-secure-root-MUSDir" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_root_MUSDir'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write_MUSDir" />
<p class="submit">
<input type="submit" name="bps-auto-write-secure-root-MUSDir" value="<?php _e('Create secure.htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Clicking OK will create a new customized secure.htaccess Master file for your Network / Multisite subdirectory website.\n\nThis is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.\n\nClick OK to Create your new secure.htaccess Master file or click Cancel.'); ?>')" />
</p>
</form>
</td>
    <td>
<form name="bps-auto-write-default-MUSDom" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_default_MUSDom'); ?>
<input type="hidden" name="filename" value="bps-auto-write-default_write_MUSDom" />
<p class="submit">
<input type="submit" name="bps-auto-write-default-MUSDom" value="<?php _e('Create default.htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Clicking OK will create a new customized default.htaccess Master file for your Network / Multisite subdomain website.\n\nThis is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.\n\nNOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.\n\nClick OK to Create your new default.htaccess Master file or click Cancel.'); ?>')" />
</p>
</form>

<form name="bps-auto-write-secure-root-MUSDom" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_MUSDom'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write_MUSDom" />
<p class="submit">
<input type="submit" name="bps-auto-write-secure-root-MUSDom" value="<?php _e('Create secure.htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Clicking OK will create a new customized secure.htaccess Master file for your Network / Multisite subdomain website.\n\nThis is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.\n\nClick OK to Create your new secure.htaccess Master file or click Cancel.'); ?>')" />
</p>
</form>
</td>
  </tr>
</table>
<?php } ?>
</div>
<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    <h2><?php _e('Activate Security Modes'); ?></h2>
    <h3><?php _e('Activate Website Root Folder .htaccess Security Mode'); ?>  <button id="bps-open-modal2" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
    <div id="bps-modal-content2" title="<?php _e('Activate Root Folder BulletProof Mode'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Installing the BulletProof Security plugin does not activate any security modes.<br>If you activate BulletProof Mode for your Root folder you must also activate BulletProof Mode for your wp-admin folder.</strong><br>Perform a backup first before activating any BulletProof Security modes (backs up both currently active root and wp-admin htaccess files at the same time).<br><br><strong>What Is Going On Here?</strong><br> Clicking the AutoMagic  buttons creates your customized Master .htaccess files for your website. Activating  BulletProof Modes copies and renames  those Master .htaccess files  from /plugins/bulletproof-security/admin/htaccess/ to your website root folder. Default Mode does not have any security protection - it is just a standard generic WordPress .htaccess file that you should only Activate  for testing or troubleshooting purposes.<br><br><strong>Help and FAQ links are available on the BPS Help and FAQ page</strong><br><br><strong>Additional Info</strong><br>Before upgrading or any time you add some additional custom code to your .htaccess files you can save that custom .htaccess code to the My Notes page. This code or notes are saved permanently to your WP database until you edit or delete it.<br><br>When you upgrade BPS your currently active root and wp-admin .htaccess files are not changed or overwritten. BPS master .htaccess files ARE replaced when you upgrade BPS so if you have made changes to your BPS master files that you want to keep make sure they are backed up using Backup and Restore first before upgrading. You can also download copies of the BPS master files to your computer using the BPS File Downloader if you want. When you backup your BPS files this is an online backup so the files will be available to you to restore from if you run into any problems at any point.<br><br>You should always be using the newest BPS master .htaccess files for the latest security protection updates and plugin conflict fixes. Before activating new BPS master files you can use the BPS File Editor to copy and paste any existing custom .htaccess code that you want to keep from your current active .htaccess files to the new BPS master .htaccess files and save your changes before activating your new BPS master .htaccess files. You can copy from one .htaccess file editing window to any other window and then save your changes. Or you can copy any new htaccess code from the new BPS master files to your existing currently active htaccess files. If you do this be sure to edit the BPS version number at the top of your currently active htaccess files or you will see BPS error messages. And the My Notes page allows you to save any code you want to save permanently for later use or reminders.<br><br><strong>Troubleshooting Error Messages</strong><br>Check the Security Status menu tab to see potential problems and explanations of what might be causing the error message. Check the Edit/Upload/Download page to view all of your .htaccess files. Click on Your Current Root htaccess File menu tab to view your actual root .htaccess file. At the top of this .htaccess file you will see the BPS version and which .htaccess file is activated. Check that the BPS QUERY STRING EXPLOITS code does actually exist in your root .htaccess file. When you update your WordPress Permalinks the BPSQSE BPS QUERY STRING EXPLOITS code is overwritten with the WordPress standard default .htaccess code. You will either need to use Backup and Restore to restore you backed up .htaccess files or activate BulletProof Mode again for your Root Folder. To check your wp-admin .htaccess file click on the Your Current wp-admin htaccess File menu tab.<br><br><strong>Testing or Removing / Uninstalling BPS</strong><br>If you are testing BPS to determine if there is a plugin conflict or other conflict then Activate Default Mode and select the Delete wp-admin htaccess File radio button and click the Activate button or you can now just go to the WordPress Permalinks page and update / resave your permalinks. This overwrites all BPS security code with the standard default WP .htaccess code. This puts your site in a standard WordPress state with a default or generic Root .htaccess file and no .htaccess file in your wp-admin folder if you selected Delete wp-admin htaccess file. After testing or troubleshooting is completed reactivate BulletProof Modes for both the Root and wp-admin folders. If you are removing / uninstalling BPS then follow the same steps and then select Deactivate from the Wordpress Plugins page and then click Delete to uninstall the BPS plugin.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="BulletProof-Root" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_root_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection12" type="radio" value="bpsecureroot" class="tog" <?php checked('', $bpsecureroot); ?> /> <?php _e('BulletProof Mode'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/.htaccess<br /><?php _e('<font color="green">Copies the file secure.htaccess to your root folder and renames the file name to just .htaccess</font>'); ?></td>
   </tr>
   <tr>   
   <th><label><input name="selection12" type="radio" value="bpdefaultroot" class="tog" <?php checked('', $bpdefaultroot); ?> /><?php _e('<font color="red">Default Mode<br>WP Default htaccess File</font>'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/.htaccess<br /><?php _e('<font color="red"> CAUTION: </font>Your site will not be protected if you activate Default Mode. ONLY activate Default Mode for Testing and Troubleshooting.'); ?></td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit12" value="<?php esc_attr_e('Activate') ?>" class="button" onClick="return confirm('<?php _e('Did you create your Master .htaccess files using the AutoMagic buttons?\n\nDid you backup your existing .htaccess files?\n\nDo you have any custom .htaccess code in your Root .htaccess file that you want to save before Activating BulletProof Mode?\n\nClicking OK will overwrite your existing Root .htaccess file.\n\nClick OK to Activate BulletProof Mode for your Root folder or click Cancel.'); ?>')" /></p></form>

<h3><?php _e('Activate Website wp-admin Folder .htaccess Security Mode'); ?>  <button id="bps-open-modal3" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content3" title="<?php _e('Activate wp-admin BulletProof Mode'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Installing the BulletProof Security plugin does not activate any security modes.<br>If you activate BulletProof Mode for your wp-admin folder you must also activate BulletProof Mode for your Root folder.</strong><br>Activating BulletProof Mode copies, renames and moves the master .htaccess file wpadmin-secure.htaccess from /plugins/bulletproof-security/admin/htaccess/ to your /wp-admin folder. If you customize or modify the master .htaccess files then be sure to make an online backup and also download backups of these master .htaccess files to your computer using the BPS File Downloader.<br><br>For more information click this Read Me button link to view the <strong>BulletProof Security Guide.</strong><br><br><strong>Testing or Removing / Uninstalling BPS</strong><br>If you are testing BPS to determine if there is a plugin conflict or other conflict then Activate Default Mode and select the Delete wp-admin htaccess File radio button and click the Activate button. This puts your site in a standard WordPress state with a default or generic Root .htaccess file and no .htaccess file in your wp-admin folder. After testing or troubleshooting is completed reactivate BulletProof Modes for both the Root and wp-admin folders. If you are removing / uninstalling BPS then follow the same steps and then select Deactivate from the Wordpress Plugins page and then click Delete to uninstall the BPS plugin.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="BulletProof-WPadmin" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_wpadmin_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection13" type="radio" value="bpsecurewpadmin" class="tog" <?php checked('', $bpsecurewpadmin); ?> /> <?php _e('BulletProof Mode'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-admin/.htaccess<br /><?php _e('<font color="green"> Copies the file wpadmin-secure.htaccess to your /wp-admin folder and renames the file name to just .htaccess</font>'); ?></td>
   </tr>
   <tr>
	<th><label><input name="selection13" type="radio" value="Removebpsecurewpadmin" class="tog" <?php checked('', $Removebpsecurewpadmin); ?> /> <?php _e('<font color="red">Delete wp-admin<br>htaccess File</font>'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-admin/.htaccess<br /><?php _e('<font color="red"> CAUTION: </font>Deletes the .htaccess file in your /wp-admin folder. ONLY delete For testing or BPS removal.'); ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit13" class="button" value="<?php esc_attr_e('Activate') ?>" />
</p></form>

<h3><?php _e('Activate Deny All htaccess Folder Protection For The BPS Master htaccess Folder'); ?>  <button id="bps-open-modal4" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content4" title="<?php _e('BPS Master htaccess Folder'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Your BPS Master htaccess folder should already be automatically protected by BPS Pro, but if it is not then activate BulletProof Mode for your BPS Master htaccess folder</strong><br><br>Activating BulletProof Mode for Deny All htaccess Folder Protection copies and renames the deny-all.htaccess file located in the /plugins/bulletproof-security/admin/htaccess/ folder and renames it to just .htaccess. The Deny All htaccess file blocks everyone, except for you, from accessing and viewing the BPS Master htaccess files.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="BulletProof-deny-all-htaccess" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_master'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection8" type="radio" value="bps_rename_htaccess_files" class="tog" <?php checked('', $bps_rename_htaccess_files); ?> /> <?php _e('BulletProof Mode'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-content/plugins/bulletproof-security/admin/htaccess/<br /><?php _e('<font color="green"> Copies the file deny-all.htaccess to the BPS Master htaccess folder and renames the file name to just .htaccess</font>'); ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit8" class="button" value="<?php esc_attr_e('Activate') ?>" />
</p></form>

<h3><?php _e('Activate Deny All htaccess Folder Protection For The BPS Backup Folder'); ?>  <button id="bps-open-modal5" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content5" title="<?php _e('BPS Backup Folder'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Your BPS Backup Folder is NOT already automatically protected by BPS Pro and requires that you activate BulletProof Mode to htaccess protect it</strong><br><br>Activating BulletProof Mode for Deny All BPS Backup Folder Protection copies and renames the deny-all.htaccess file located in the /bulletproof-security/admin/htaccess/ folder to the BPS Backup folder /wp-content/bps-backup and renames it to just .htaccess. The Deny All htaccess file blocks everyone, except for you, from accessing and viewing your backed up htaccess files.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="BulletProof-deny-all-backup" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_bpsbackup'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection14" type="radio" value="bps_rename_htaccess_files_backup" class="tog" <?php checked('', $bps_rename_htaccess_files_backup); ?> /> <?php _e('BulletProof Mode'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-content/bps-backup/<br /><?php _e('<font color="green"> Copies and the file deny-all.htaccess to the BPS Backup folder and renames the file name to just .htaccess</font>'); ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit14" class="button" value="<?php esc_attr_e('Activate') ?>" />
</p></form>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>

</div>
            
<div id="bps-tabs-2" class="bps-tab-page">
<h2><?php _e('BulletProof Security Status'); ?></h2>


<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-status_table">
  <tr>
    <td width="49%" class="bps-table_title_SS"><?php _e('Activated BulletProof Security .htaccess Files'); ?>  <button id="bps-open-modal6" class="bps-modal-button"><?php _e('Read Me'); ?></button>
    <div id="bps-modal-content6" title="<?php _e('Activated .htaccess Files'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Installing the BulletProof Security plugin does not activate any security modes.<br>If you activate BulletProof Mode for your Root folder you must also activate BulletProof Mode for your wp-admin folder.</strong><br>Perform a backup first before activating any BulletProof Security modes (backs up both currently active root and wp-admin htaccess files at the same time).<br><br><strong>Help and FAQ links are available on the BPS Help and FAQ page</strong><br><br><strong>Additional Info</strong><br>Before upgrading or any time you add some additional custom code to your .htaccess files you can save that custom .htaccess code to the My Notes page. This code or notes are saved permanently to your WP database until you edit or delete it.<br><br>When you upgrade BPS your currently active root and wp-admin .htaccess files are not changed or overwritten. BPS master .htaccess files ARE replaced when you upgrade BPS so if you have made changes to your BPS master files that you want to keep make sure they are backed up using Backup and Restore first before upgrading. You can also download copies of the BPS master files to your computer using the BPS File Downloader if you want. When you backup your BPS files this is an online backup so the files will be available to you to restore from if you run into any problems at any point.<br><br>You should always be using the newest BPS master .htaccess files for the latest security protection updates and plugin conflict fixes. Before activating new BPS master files you can use the BPS File Editor to copy and paste any existing custom .htaccess code that you want to keep from your current active .htaccess files to the new BPS master .htaccess files and save your changes before activating your new BPS master .htaccess files. You can copy from one .htaccess file editing window to any other window and then save your changes. Or you can copy any new htaccess code from the new BPS master files to your existing currently active htaccess files. If you do this be sure to edit the BPS version number at the top of your currently active htaccess files or you will see BPS error messages. And the My Notes page allows you to save any code you want to save permanently for later use or reminders.<br><br>The Text Strings you see listed in the Activated BulletProof Security Status window if you have an active BulletProof .htaccess file (or an existing .htaccess file) is reading and displaying the actual contents of any existing .htaccess files here. <strong>This is not just a displayed message - this is the actual first 46 string characters (text) of the contents of your .htaccess files.</strong>The BPSQSE BPS QUERY STRING EXPLOITS code check is done by searching the root .htaccess file to verify that the string/text/word BPSQSE is in the file.<br><br><strong>Troubleshooting Error Messages</strong><br>Check the Edit/Upload/Download page to view all of your .htaccess files. Click on Your Current Root htaccess File menu tab to view your actual root .htaccess file. At the top of this .htaccess file you will see the BPS version and which .htaccess file is activated. Check that the BPS QUERY STRING EXPLOITS code does actually exist in your root .htaccess file. When you update your WordPress Permalinks the BPSQSE BPS QUERY STRING EXPLOITS code is overwritten with the WordPress standard default .htaccess code. You will either need to use Backup and Restore to restore you backed up .htaccess files or activate BulletProof Mode again for your Root Folder. To check your wp-admin .htaccess file click on the Your Current wp-admin htaccess File menu tab.<br><br><strong>Miscellaneous Info</strong><br>To change or modify the Text String that you see displayed here you would use the BPS built in Text Editor to change the actual text content of the BulletProof Security master .htaccess files. If the change the BULLETPROOF SECURITY title shown here then you must also change the coding contained in the /wp-content/plugins/bulletproof-security/includes/functions.php file to match your changes or you will get some error messages. The rest of the text content in the .htaccess files can be modified just like a normal post. Just this top line ot text in the .htaccess files contains version information that BPS checks to do verifications and other file checking. For detailed instructions on modifying what text is displayed here click this Read Me button link.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>
</td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title"><?php _e('Additional Website Security Measures'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell">
<?php 
	echo root_htaccess_status();
	echo denyall_htaccess_status_master();
	echo denyall_htaccess_status_backup();
	echo wpadmin_htaccess_status();
?>
    <td>&nbsp;</td>
    <td class="bps-table_cell">
<?php 
	echo bps_wpdb_errors_off();
	echo bps_wp_remove_version();
	echo check_admin_username();
	echo bps_filesmatch_check_readmehtml();
	echo bps_filesmatch_check_installphp();
	//echo bpsPro_sysinfo_message();
?>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
</table>
<?php } ?>
<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-perms_table">
  <tr>
    <td class="bps-table_title_SS"><?php _e('File and Folder Permissions - CGI or DSO'); ?>  <button id="bps-open-modal7" class="bps-modal-button"><?php _e('Read Me'); ?></button>
    <div id="bps-modal-content7" title="<?php _e('File and Folder Permissions'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>CGI And DSO File And Folder Permission Recommendations</strong><br>If your Server API (SAPI) is CGI you will see a table displayed with recommendations for file and folder permissions for CGI. If your SAPI is DSO / Apache mod_php you will see a table listing file and folder permission recommendations for DSO. If you Host is using CGI, but they do not allow you to set your folder permissions more restricive to 705 and file permissions more restrictive to 604 then most likely when you change your folder and file permissions they will automatically be changed back to 755 and 644 by your Host. CGI 705 folder permissions have been thoroughly tested with WordPress and no problems have been discovered with WP or with WP Plugins. Changing your folder permissions to 705 helps in protecting against Mass Host Code Injections. CGI 604 file permissions have been thoroughly tested with WordPress and no problems have been discovered with WP or with WP Plugins. Changing your file permissions to 604 helps in protecting your files from Mass Host Code Injections. CGI Mission Critical files should be set to 400 and 404 respectively.<br><br><strong>If you have BPS Pro installed then use F-Lock to Lock or Unlock your Mission Critical files. BPS Pro S-Monitor will automatically display warning messages if your files are unlocked.</strong><br><br><strong>The wp-content/bps-backup/ folder permission recommendation is 755 for CGI or DSO for compatibility reasons. The /bps-backup folder has a deny all .htaccess file in it so that it cannot be accessed by anyone other than you so the folder permissions for this folder are irrelevant.</strong><br><br>Your current file and folder permissions are shown below with suggested / recommended file and folder permissions. <strong>Not all web hosts will allow you to set your folder permissions to these Recommended folder permissions.</strong> If you see 500 errors after changing your folder permissions than change them back to what they were.<br><br>I recommend using FileZilla to change your file and folder permissions. FileZilla is a free FTP software that makes changing your file and folder permissions very simple and easy as well as many other very nice FTP features. With FileZilla you can right mouse click on your files or folders and set the permissions with a Numeric value like 755, 644, etc. Takes the confusion out of which attributes to check or uncheck.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>
</td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title_SS"><?php _e('General BulletProof Security File Checks'); ?>  <button id="bps-open-modal8" class="bps-modal-button"><?php _e('Read Me'); ?></button>
    <div id="bps-modal-content8" title="<?php _e('General File Checks'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br>This is a quick visual check to verify that you have active .htaccess files in your root and /wp-admin folders and that all the required BPS files are in your BulletProof Security plugin folder. The BulletProof Security .htaccess master files (default.htaccess, secure.htaccess, wpadmin-secure.htaccess, maintenance.htaccess and bp-maintenance.php) are located in this folder /wp-content/plugins/bulletproof-security/admin/htaccess/<br><br>For new installations and upgrades of BulletProof Security you will see red warning messages. This is completely normal. These warnings are there to remind you to perform backups if they have not been performed yet. Also you may see warning messages if files do not exist yet.<br><br>You can also download backups of any existing .htaccess files using the BPS File Downloader.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>
</td>
  </tr>
  <tr>
  	<td height="100%" class="bps-table_cell_perms_blank">
	<?php 
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
	_e('<div style=\'padding:5px 0px 5px 5px;\'><strong>CGI File and Folder Permissions / Recommendations</strong></div>');
	echo '<table style="width:100%;background-color:#A9F5A0;border-bottom:1px solid black;border-top:1px solid black;">';
	echo '<tr>';
	_e('<td style="padding:2px;width:35%;font-weight:bold;">File Name<br>Folder Name</td>');
    _e('<td style="padding:2px;width:35%;font-weight:bold;">File Path<br>Folder Path</td>');
    _e('<td style="padding:2px;width:15%;font-weight:bold;">Recommended<br>Permissions</td>');
    _e('<td style="padding:2px;width:15%;font-weight:bold;">Current<br>Permissions</td>');
	echo '</tr>';
    echo '</table>';

	bps_check_perms(".htaccess","../.htaccess","404");
	bps_check_perms("wp-config.php","../wp-config.php","400");
	bps_check_perms("index.php","../index.php","400");
	bps_check_perms("wp-blog-header.php","../wp-blog-header.php","400");
	bps_check_perms("root folder","../","705");
	bps_check_perms("wp-admin/","../wp-admin","705");
	bps_check_perms("wp-includes/","../wp-includes","705");
	bps_check_perms("wp-content/","../wp-content","705");
	bps_check_perms("wp-content/bps-backup/","../wp-content/bps-backup","755");
	_e('<div style=\'padding-bottom:15px;\'></div>');
	
	} else {
	
	_e('<div style=\'padding:5px 0px 5px 5px;\'><strong>DSO File and Folder Permissions / Recommendations</strong></div>');
	echo '<table style="width:100%;background-color:#A9F5A0;border-bottom:1px solid black;border-top:1px solid black;">';
	echo '<tr>';
	_e('<td style="padding:2px;width:35%;font-weight:bold;">File Name<br>Folder Name</td>');
    _e('<td style="padding:2px;width:35%;font-weight:bold;">File Path<br>Folder Path</td>');
    _e('<td style="padding:2px;width:15%;font-weight:bold;">Recommended<br>Permissions</td>');
    _e('<td style="padding:2px;width:15%;font-weight:bold;">Current<br>Permissions</td>');
	echo '</tr>';
    echo '</table>';
	
	bps_check_perms(".htaccess","../.htaccess","644");
	bps_check_perms("wp-config.php","../wp-config.php","644");
	bps_check_perms("index.php","../index.php","644");
	bps_check_perms("wp-blog-header.php","../wp-blog-header.php","644");
	bps_check_perms("root folder","../","755");
	bps_check_perms("wp-admin/","../wp-admin","755");
	bps_check_perms("wp-includes/","../wp-includes","755");
	bps_check_perms("wp-content/","../wp-content","755");
	bps_check_perms("wp-content/bps-backup/","../wp-content/bps-backup","755");
	_e('<div style=\'padding-bottom:15px;\'></div>');
	}
?></td>
    <td>&nbsp;</td>
    <td rowspan="4" class="bps-table_cell_file_checks">
    <?php echo general_bps_file_checks(); ?>
   <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-file_checks_bottom_table" style="margin-top:38px;">
      <tr>
        <td class="bps-file_checks_bottom_bps-table_cell">&nbsp;</td>
      </tr>
    </table>
    </td>
  </tr>
 <tr>
    <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-file_checks_bottom_table_special">
      <tr>
        <td class="bps-file_checks_bottom_bps-table_cell">&nbsp;</td>
      </tr>
    </table>    
    </td>
    <td>&nbsp;</td>
    </tr>
</table>
<br />
<?php } ?>
</div>
            
<div id="bps-tabs-3">
<h2><?php _e('System Information'); ?></h2>

<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-system_info_table">
  <tr>
    <td width="49%" class="bps-table_title"><?php _e('Website / Server / IP Info'); ?></td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title"><?php _e('SQL Database / Permalink Structure / WP Installation Folder'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Website Root Folder'); ?>: <strong><?php echo get_site_url(); ?></strong></td>
    <td>&nbsp;</td>
    <td rowspan="12" class="bps-table_cell">
	<?php _e('MySQL Database Version'); ?>: <?php $sqlversion = $wpdb->get_var("SELECT VERSION() AS version"); ?><strong><?php echo $sqlversion; ?></strong><br />
      <?php _e('MySQL Client Version'); ?>
      : <strong><?php echo mysql_get_client_info(); ?></strong><br />
      <?php _e('Database Host'); ?>
      : <strong><?php echo DB_HOST; ?></strong>&nbsp;</strong><br />
      <?php _e('Database Name'); ?>
      : <strong><?php echo DB_NAME; ?></strong>&nbsp;</strong><br />
      <?php _e('Database User'); ?>
      : <strong><?php echo DB_USER; ?></strong><br />
      <?php _e('SQL Mode'); ?>
      : 
      <?php $mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
	if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
    if (empty($sql_mode)) $sql_mode = __('Not Set');
	else $sql_mode = __('Off'); ?>      <strong><?php echo $sql_mode; ?></strong><br /><br />
      <?php _e('WordPress Installation Folder'); ?>
      : <strong><?php echo bps_wp_get_root_folder(); ?></strong><br />
      <?php _e('WordPress Installation Type'); ?>
      : <strong><?php echo bps_wp_get_root_folder_display_type(); ?></strong><br />
      <?php _e('WP Permalink Structure'); ?>
      : <strong><?php $permalink_structure = get_option('permalink_structure'); echo $permalink_structure; ?></strong><br />
	  <?php echo bps_check_permalinks(); ?><br />
      <?php echo bps_check_php_version (); ?>
      </td>
  </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Document Root Path'); ?>: <strong><?php echo $_SERVER['DOCUMENT_ROOT']; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('WP ABSPATH'); ?>: <strong><?php echo ABSPATH; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Parent Directory'); ?>: <strong><?php echo dirname(ABSPATH); ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Server / Website IP Address'); ?>: <strong><?php echo $_SERVER['SERVER_ADDR']; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Host by Address'); ?>: <strong><?php echo gethostbyaddr($_SERVER['SERVER_ADDR']); ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('DNS Name Server'); ?>: <strong><?php if ($bpsTargetNS != '') { echo $bpsTargetNS; } else { echo $bpsTarget; } ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Public IP / Your Computer IP Address'); ?>: <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Server Type'); ?>: <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Operating System'); ?>: <strong><?php echo PHP_OS; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Server API'); ?>: <strong><?php $sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
    _e($sapi_type.' - Your Host Server is using CGI.');
	} else {
    _e($sapi_type.' - Your Host Server is using DSO or another SAPI type.');
	} ?></strong>
    </td>
    <td>&nbsp;</td>
    </tr>  
  <tr>
    <td class="bps-table_cell"><?php echo bps_multsite_check(); ?></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('Browser Compression Supported'); ?>: <strong><?php echo $_SERVER['HTTP_ACCEPT_ENCODING']; ?></strong></td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_title"><?php _e('PHP Server / PHP.ini Info'); ?></td>
    <td>&nbsp;</td>
    <td class="bps-table_title"><?php _e('BPS Pro Security Modules Info'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Version'); ?>: <strong><?php echo PHP_VERSION; ?></strong></td>
    <td>&nbsp;</td>
    <td rowspan="18" class="bps-table_cell">
	<?php // BPS Pro ONLY ?>
	<?php //echo bpsPro_sysinfo_mod_checks_smon(); ?><br />
	<?php //echo bpsPro_sysinfo_mod_checks_hud(); ?><br />
	<?php //echo bpsPro_sysinfo_mod_checks_phpini(); ?><br />
	<?php //echo bpsPro_sysinfo_mod_checks_elog(); ?><br />
    </td>
  </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Memory Usage'); ?>: <strong><?php echo round(memory_get_usage() / 1024 / 1024, 2) . __(' MB'); ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Memory Limit'); ?>: <?php if(ini_get('memory_limit')) $memory_limit = ini_get('memory_limit');
        else $memory_limit = __('N/A'); ?><strong><?php echo $memory_limit; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Max Upload Size'); ?>: <?php if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');
        else $upload_max = __('N/A'); ?><strong><?php echo $upload_max; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Max Post Size'); ?>: <?php if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
        else $post_max = __('N/A'); ?><strong><?php echo $post_max; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Safe Mode'); ?>: <?php if(ini_get('safe_mode')) $safe_mode = __('On');
        else $safe_mode = __('Off'); ?><strong><?php echo $safe_mode; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Allow URL fopen'); ?>: <?php if(ini_get('allow_url_fopen')) $allow_url_fopen = __('On');
        else $allow_url_fopen = __('Off'); ?><strong><?php echo $allow_url_fopen; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
   <tr>
    <td class="bps-table_cell"><?php _e('PHP Allow URL Include'); ?>: <?php if(ini_get('allow_url_include')) $allow_url_include = __('On');
        else $allow_url_include = __('Off'); ?><strong><?php echo $allow_url_include; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Display Errors'); ?>: <?php if(ini_get('display_errors')) $display_errors = __('On');
        else $display_errors = __('Off'); ?><strong><?php echo $display_errors; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Display Startup Errors'); ?>: <?php if(ini_get('display_startup_errors')) $display_startup_errors = __('On');
        else $display_startup_errors = __('Off'); ?><strong><?php echo $display_startup_errors; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
   <tr>
    <td class="bps-table_cell"><?php _e('PHP Expose PHP'); ?>: <?php if(ini_get('expose_php')) $expose_php = __('On');
        else $expose_php = __('Off'); ?><strong><?php echo $expose_php; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Register Globals'); ?>: <?php if(ini_get('register_globals')) $register_globals = __('On');
        else $register_globals = __('Off'); ?><strong><?php echo $register_globals; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Max Script Execution Time'); ?>: <?php if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
        else $max_execute = __('N/A'); ?><strong><?php echo $max_execute; ?> Seconds</strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Magic Quotes GPC'); ?>: <?php if(ini_get('magic_quotes_gpc')) $magic_quotes_gpc = __('On');
        else $magic_quotes_gpc = __('Off'); ?><strong><?php echo $magic_quotes_gpc; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP open_basedir'); ?>: <?php if(ini_get('open_basedir')) $open_basedir = __('On');
        else $open_basedir = __('Off'); ?><strong><?php echo $open_basedir; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP XML Support'); ?>: <?php if (is_callable('xml_parser_create')) $xml = __('Yes');
        else $xml = __('No'); ?><strong><?php echo $xml; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP IPTC Support'); ?>: <?php if (is_callable('iptcparse')) $iptc = __('Yes');
        else $iptc = __('No'); ?><strong><?php echo $iptc; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell"><?php _e('PHP Exif Support'); ?>: <?php if (is_callable('exif_read_data')) $exif = __('Yes'). " ( V" . substr(phpversion('exif'),0,4) . ")" ;
        else $exif = __('No'); ?><strong><?php echo $exif; ?></strong></td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<br />
<?php } ?>
</div>
            
<div id="bps-tabs-4" class="bps-tab-page">
<h2><?php _e('BulletProof Security Backup &amp; Restore'); ?></h2>

<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
<h3><?php _e('Backup Your Currently Active .htaccess Files'); ?></h3>
<h3><?php _e('<font color="red"><strong>CAUTION: </strong></font>'); ?>  <button id="bps-open-modal9" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content9" title="<?php _e('.htaccess File Backup'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br>Back up your existing .htaccess files first before activating any BulletProof Security Modes in case of a problem when you first install and activate any BulletProof Security Modes. Once you have backed up your original existing .htaccess files you will see the status listed in the <strong>Current Backed Up .htaccess Files Status</strong> window below. <br><br><strong>Backup files are stored in this folder /wp-content/bps-backup.</strong><br><br>In cases where you install a plugin that writes to your htaccess files you will want to perform another backup of your htaccess files. Each time you perform a backup you are overwriting older backed up htaccess files. Backed up files are stored in the /wp-content/bps-folder.<br><br>You could also use the BPS File Downloader to download any existing .htaccess files, customized .htaccess files or other BPS files that you have personally customized or modified just for an additional local backup.<br><br><strong>The BPS Master .htaccess files are stored in your /plugins/bulletproof-security/admin/htaccess folder and can also be backed up to the /wp-content/bps-backup/master-backups folder.</strong><br>Backed up files are stored online so they will be available to you after upgrading to a newer version of BPS if you run into a problem. There is no Restore feature for the BPS Master files because you should be using the latest versions of the BPS master .htaccess files after you upgrade BPS. You can manually download the files from this folder /wp-content/bps-backup/master-backups using FTP or your web host file downloader.<br><br>When you upgrade BPS your current root and wp-admin htaccess files are not affected. BPS master htaccess files are replaced when you upgrade BPS so if you have made changes to your BPS master files that you want to keep make sure they are backed up first before upgrading. You can also download copies of the BPS master files to your computer using the BPS File Downloader if you want. When you backup your BPS files it is an online backup so the files will be available to you to restore from if you run into any problems at any point. You should always be using the newest BPS master htaccess files for the latest security protection updates and plugin conflict fixes. Before activating new BPS master files you can use the BPS File Editor to copy and paste any existing htaccess code that you want to keep from your current active htaccess files to the new BPS master htaccess files and save your changes before activating the new BPS htaccess files. Or you can copy any new htaccess code from the new BPS master files to your existing currently active htaccess files. If you do this be sure to edit the BPS  version number in your currently active htaccess files or you will get error messages.<br><br><strong>If something goes wrong in the .htaccess file editing process or at any point you can restore your good .htaccess files with one click as long as you already backed them up.</strong><br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>


<form name="BulletProof-Backup" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-4" method="post">
<?php wp_nonce_field('bulletproof_security_backup_active_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection9" type="radio" value="backup_htaccess" class="tog" <?php echo checked('', $backup_htaccess); ?> />
<?php _e('Backup .htaccess Files'); ?></label></th>
	<td><?php _e('<font color="green"><strong>Backs up your currently active .htaccess files in your root and /wp-admin folders.</strong></font><br><strong>Backup your htaccess files for first time installations of BPS or whenever new modifications have been made to your htaccess files. Read the <font color="red"><strong>CAUTION: </strong></font>Read Me button.</strong>'); ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit9" class="button" value="<?php esc_attr_e('Backup Files') ?>" />
</p></form>

<h3><?php _e('Restore Your .htaccess Files From Backup'); ?>  <button id="bps-open-modal10" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content10" title="<?php _e('.htaccess File Restore'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br>Restores your backed up .htaccess files that you backed up. Your backed up .htaccess files were renamed to root.htaccess and wpadmin.htaccess and copied to the /wp-content/bps-backup folder. Restoring your backed up .htaccess files will rename them back to .htaccess and copy them back to your root and /wp-admin folders respectively.<br><br><strong>If you did not have any original .htaccess files to begin with and / or you did not back up any files then you will not have any backed up .htaccess files.</strong><br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="BulletProof-Restore" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-4" method="post">
<?php wp_nonce_field('bulletproof_security_restore_active_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection10" type="radio" value="restore_htaccess" class="tog" <?php checked('', $restore_htaccess); ?> />
<?php _e('Restore .htaccess Files'); ?></label></th>
	<td><?php _e('<font color="green"><strong>Restores your backed up .htaccess files to your root and /wp-admin folders.</strong></font><br><strong>Restore your backed up .htaccess files if you have any problems or for use between BPS ugrades.</strong>'); ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit10" class="button" value="<?php esc_attr_e('Restore Files') ?>" />
</p></form>

<h3><?php _e('Backup Your BPS Master .htaccess Files'); ?>  <button id="bps-open-modal11" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content11" title="<?php _e('Master .htaccess File Backup'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br>The BPS Master .htaccess files are stored in your /plugins/bulletproof-security/admin/htaccess folder and can also be backed up using this Master Backup feature. The backed up BPS Master .htaccess files are copied to this folder /wp-content/bps-backup/master-backups folder. This way they will be available to you online after upgrading to a newer version of BPS. There is no Restore feature for the BPS Master files because you should be using the latest versions of the BPS master .htaccess files after you upgrade BPS. You can manually download the files from this folder /wp-content/bps-backup/master-backups using FTP or your web host file downloader.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="BPS-Master-Htaccess-Backup" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-4" method="post">
<?php wp_nonce_field('bulletproof_security_backup_master_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection11" type="radio" value="backup_master_htaccess_files" class="tog" <?php checked('', $backup_master_htaccess_files); ?> />
<?php _e('Backup BPS Master .htaccess Files'); ?></label></th>
	<td><?php _e('<font color="green"><strong>Backs up your BPS Master .htaccess files to the /wp-content/bps-backup/master-backups folder.</strong></font><br><strong>There is no Restore feature for the BPS Master .htaccess files because you should be using the latest most current BPS Master .htaccess security coding and plugin fixes included in the most current version of the BPS master .htacess files.</strong>'); ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit11" class="button" value="<?php esc_attr_e('Backup Master Files') ?>" />
</p></form>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-backup_restore_table">
  <tr>
    <td class="bps-table_title_SS"><?php _e('Current Backed Up .htaccess Files Status'); ?>  <button id="bps-open-modal12" class="bps-modal-button"><?php _e('Read Me'); ?></button>
    <div id="bps-modal-content12" title="<?php _e('Backup Up .htaccesss File'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br>General file checks to check which files have been backed up or not.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell"><strong><?php general_bps_file_checks_backup_restore(); ?></strong></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell"><?php echo backup_restore_checks(); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell"><?php echo bps_master_file_backups(); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<br />
</div>
        
<div id="bps-tabs-5" class="bps-tab-page">
<table width="100%" border="0">
  <tr>
    <td width="33%"><h2><?php _e('BulletProof Security File Editing'); ?></h2></td>
    <td width="21%"><button id="bps-open-modal13" class="bps-modal-button"><?php _e('Read Me'); ?></button>
    <div id="bps-modal-content13" title="<?php _e('File Editing'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Lock / Unlock .htaccess Files</strong><br>If your Server API is using CGI then you will see Lock and Unlock buttons to lock your Root .htaccess file with 404 Permissions and unlock your root .htaccess file with 644 Permissions. If your Server API is using CLI - DSO / Apache / mod_php then you will not see lock and unlock buttons. 644 Permissions are required to write to / edit the root .htaccess file. Once you are done editing your root .htaccess file use the lock button to lock it with 404 Permissions. 644 Permissions for DSO are considered secure for DSO because of the different way that file security is handled with DSO.<br><br>If your Root .htaccess file is locked and you try to save your editing changes you will see a pop message that your Root .htaccess file is locked. You will need to unlock your Root .htaccess file before you can save your changes.<br><br><strong>The File Editor is designed to open all of your .htaccess files simultaneously and allow you to copy and paste from one window (file) to another window (file), BUT you can ONLY save your edits for one file at a time. Whichever file you currently have opened (the tab that you are currently viewing) when you click the Update File button is the file that will be updated / saved. This is done for 2 reasons - reduces the chances of making an editing mistake and for better security.</strong><br><br>A help link is provided in the Help & FAQ page <strong>File Editing Within The Dashboard Help Info.</strong> File Editing is also demonstrated and explained in the B-Core htaccess Video Tutorial.<strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>
</td>
    <td width="19%" align="right">
    <h3 style="margin-right:0px;"><button id="bps-open-modal14" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
    <div id="bps-modal-content14" title="<?php _e('File Uploading / Downloading'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>File Uploading</strong><br>The file upload location is preset to the /wp-content/plugins/bulletproof-security/admin/htaccess folder and the intended use is just for uploading the BPS Master files: secure.htaccess, default.htaccess, wpadmin-secure.htaccess, maintenance.htaccess, bp-maintenance.php, bps-maintenance-values.php, http_error_log.txt (BPS Pro only) or other files from your computer to the BPS Master htaccess folder.<br><br><strong>File Downloading</strong><br><strong>File Downloading is automatically not allowed. Folder permissions must be set to a minimum of 705 for the /htaccess and /bps-backup folders in order to open and download files.</strong><br>Click the Enable Master File Downloading button to enable file downloading. This will write your current IP address to the deny all htaccess file and allow ONLY you access to the /plugins/bulletproof-security/admin/htaccess folder to open and download files. To open and download your Backed up files click the Enable Backed Up File Downloading button. After clicking the Enable File Downloading buttons you can click the download buttons below to open or download files. If your IP address changes which it will do frequently then click the Enable File Downloading buttons again to write a new IP address to the deny all htaccess files.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>
    </td>
    <td width="27%" align="center"><h2><?php _e('Uploads - Downloads'); ?></h2></td>
  </tr>
</table>

<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0">
  <tr>
    <td colspan="2">
    <div id="bps_file_editor" class="bps_file_editor_update">
<?php
echo secure_htaccess_file_check();
echo default_htaccess_file_check();
echo maintenance_htaccess_file_check();
echo wpadmin_htaccess_file_check();

// Perform File Open and Write test first by appending a literal blank space
// or nothing at all to end of the htaccess files.
// If append write test is successful file is writable on submit
if (current_user_can('manage_options')) {
$secure_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
$write_test = "";
	if (is_writable($secure_htaccess_file)) {
    if (!$handle = fopen($secure_htaccess_file, 'a+b')) {
	_e('<font color="red"><strong>Cannot open file' . "$secure_htaccess_file" . '</strong></font><br>');
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	_e('<font color="red"><strong>Cannot write to file' . "$secure_htaccess_file" . '</strong></font><br>');
    exit;
    }
	_e('<strong>File Open and Write test successful! The secure.htaccess file is writable.</strong><br>');
	} else {
	if (file_exists($secure_htaccess_file)) {
	_e('<font color="blue"><strong>Cannot write to file: ' . "$secure_htaccess_file" . '</strong></font><br>');
	}
	}
	}
	
	if (isset($_POST['submit1']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_1' );
	$newcontent1 = stripslashes($_POST['newcontent1']);
	if ( is_writable($secure_htaccess_file) ) {
		$handle = fopen($secure_htaccess_file, 'w+b');
		fwrite($handle, $newcontent1);
	_e('<font color="green"><strong>Success! The secure.htaccess file has been updated.</strong></font><br>');
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$default_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';
$write_test = "";
	if (is_writable($default_htaccess_file)) {
    if (!$handle = fopen($default_htaccess_file, 'a+b')) {
	_e('<font color="red"><strong>Cannot open file' . "$default_htaccess_file" . '</strong></font><br>');
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	_e('<font color="red"><strong>Cannot write to file' . "$default_htaccess_file" . '</strong></font><br>');
    exit;
    }
	_e('<strong>File Open and Write test successful! The default.htaccess file is writable.</strong><br>');
	} else {
	if (file_exists($default_htaccess_file)) {
	_e('<font color="blue"><strong>Cannot write to file: ' . "$default_htaccess_file" . '</strong></font><br>');
	}
	}
	}
	
	if (isset($_POST['submit2']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_2' );
	$newcontent2 = stripslashes($_POST['newcontent2']);
	if ( is_writable($default_htaccess_file) ) {
		$handle = fopen($default_htaccess_file, 'w+b');
		fwrite($handle, $newcontent2);
	_e('<font color="green"><strong>Success! The default.htaccess file has been updated.</strong></font><br>');	
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$maintenance_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
$write_test = "";
	if (is_writable($maintenance_htaccess_file)) {
    if (!$handle = fopen($maintenance_htaccess_file, 'a+b')) {
	_e('<font color="red"><strong>Cannot open file' . "$maintenance_htaccess_file" . '</strong></font><br>');
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	_e('<font color="red"><strong>Cannot write to file' . "$maintenance_htaccess_file" . '</strong></font><br>');
    exit;
    }
	_e('<strong>File Open and Write test successful! The maintenance.htaccess file is writable.</strong><br>');
	} else {
	if (file_exists($maintenance_htaccess_file)) {
	_e('<font color="blue"><strong>Cannot write to file: ' . "$maintenance_htaccess_file" . '</strong></font><br>');
	}
	}
	}
	
	if (isset($_POST['submit3']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_3' );
	$newcontent3 = stripslashes($_POST['newcontent3']);
	if ( is_writable($maintenance_htaccess_file) ) {
		$handle = fopen($maintenance_htaccess_file, 'w+b');
		fwrite($handle, $newcontent3);
	_e('<font color="green"><strong>Success! The maintenance.htaccess file has been updated.</strong></font><br>');	
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$wpadmin_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
$write_test = "";
	if (is_writable($wpadmin_htaccess_file)) {
    if (!$handle = fopen($wpadmin_htaccess_file, 'a+b')) {
	_e('<font color="red"><strong>Cannot open file' . "$wpadmin_htaccess_file" . '</strong></font><br>');
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	_e('<font color="red"><strong>Cannot write to file' . "$wpadmin_htaccess_file" . '</strong></font><br>');
    exit;
    }
	_e('<strong>File Open and Write test successful! The wpadmin-secure.htaccess file is writable.</strong><br>');
	} else {
	if (file_exists($wpadmin_htaccess_file)) {
	_e('<font color="blue"><strong>Cannot write to file: ' . "$wpadmin_htaccess_file" . '</strong></font><br>');
	}
	}
	}
	
	if (isset($_POST['submit4']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_4' );
	$newcontent4 = stripslashes($_POST['newcontent4']);
	if ( is_writable($wpadmin_htaccess_file) ) {
		$handle = fopen($wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent4);
	_e('<font color="green"><strong>Success! The wpadmin-secure.htaccess file has been updated.</strong></font><br>');	
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$root_htaccess_file = ABSPATH . '.htaccess';
$write_test = "";
	if (is_writable($root_htaccess_file)) {
    if (!$handle = fopen($root_htaccess_file, 'a+b')) {
	_e('<font color="black"><strong>Cannot open file' . "$root_htaccess_file" . '</strong></font><br>');
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	_e('<font color="black"><strong>Cannot write to file' . "$root_htaccess_file" . '</strong></font><br>');
    exit;
    }
	_e('<strong>File Open and Write test successful! Your currently active root .htaccess file is writable.</strong><br>');
	} else {
	if (file_exists($root_htaccess_file)) {
	_e('<font color="blue"><strong>Your root .htaccess file is Locked with Read Only Permissions.<br>Use the Lock and Unlock buttons below to Lock or Unlock your root .htaccess file for editing.</strong></font><br>');
	} else {
	_e('<font color="black"><strong>Cannot write to file: ' . "$root_htaccess_file" . '</strong></font><br>');
	}
	}
	}
	
	if (isset($_POST['submit5']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_5' );
	$newcontent5 = stripslashes($_POST['newcontent5']);
	if ( !is_writable($root_htaccess_file) ) {
	_e('<font color="red"><strong>Error: Unable to write to the Root .htaccess file. If your Root .htaccess file is locked you must unlock first.</strong></font><br>');
	}	
	if ( is_writable($root_htaccess_file) ) {
		$handle = fopen($root_htaccess_file, 'w+b');
		fwrite($handle, $newcontent5);
	_e('<font color="green"><strong>Success! Your currently active root .htaccess file has been updated.</strong></font><br>');	
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$current_wpadmin_htaccess_file = ABSPATH . 'wp-admin/.htaccess';
$write_test = "";
	if (is_writable($current_wpadmin_htaccess_file)) {
    if (!$handle = fopen($current_wpadmin_htaccess_file, 'a+b')) {
	_e('<font color="red"><strong>Cannot open file' . "$current_wpadmin_htaccess_file" . '</strong></font><br>');
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	_e('<font color="red"><strong>Cannot write to file' . "$current_wpadmin_htaccess_file" . '</strong></font><br>');
    exit;
    }
	_e('<strong>File Open and Write test successful! Your currently active wp-admin .htaccess file is writable.</strong><br>');
	} else {
	if (file_exists($current_wpadmin_htaccess_file)) {
	_e('<font color="blue"><strong>Cannot write to file: ' . "$current_wpadmin_htaccess_file" . '</strong></font><br>');
	}
	}
	}
	
	if (isset($_POST['submit6']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_6' );
	$newcontent6 = stripslashes($_POST['newcontent6']);
	if ( is_writable($current_wpadmin_htaccess_file) ) {
		$handle = fopen($current_wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent6);
	_e('<font color="green"><strong>Success! Your currently active wp-admin .htaccess file has been updated.</strong></font><br>');	
    fclose($handle);
	}
}

// BPS Pro Only - Lock and Unlock Root .htaccess file 
if (isset($_POST['submit-ProFlockLock']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_flock_lock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
	
	if (file_exists($bpsRootHtaccessOL)) {
	chmod($bpsRootHtaccessOL, 0404);
	_e('<font color="blue"><strong><br>Your Root .htaccess file has been Locked.</strong></font><br>');
	} else {
	_e('<font color="red"><strong><br>Unable to Lock your Root .htaccess file.</strong></font><br>');
	}
}
	
if (isset($_POST['submit-ProFlockUnLock']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_flock_unlock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
		
	if (file_exists($bpsRootHtaccessOL)) {
	chmod($bpsRootHtaccessOL, 0644);
	_e('<font color="blue"><strong><br>Your Root .htaccess file has been Unlocked.</strong></font><br>');
	} else {
	_e('<font color="red"><strong><br>Unable to Unlock your Root .htaccess file.</strong></font><br>');
	}
}
?>

</div>

</td>
    <td width="33%" align="center" valign="top">
	<?php _e("<div class=\"bps-file_upload_title\"><strong>File Uploads<br></strong></div>"); ?>
<form name="BPS-upload" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post" enctype="multipart/form-data"><?php wp_nonce_field('bulletproof_security_upload'); ?>
<p class="submit">
<input id="bps_file_upload" name="bps_file_upload" type="file" />
</p>
<p class="submit" style="margin:-5px 0px 0px -12px;">
<input type="submit" name="submit-bps-upload" class="button" value="<?php esc_attr_e('Upload File') ?>" />
</p>
</form></td>
  </tr>
  <tr>
    <td width="22%">
<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') { ?>    
    
    <form name="bpsFlockLockForm" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_flock_lock'); ?>
<p class="submit">
	<input type="submit" name="submit-ProFlockLock" value="<?php _e('Lock .htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Click OK to Lock your Root .htaccess file or click Cancel.\n\nNote: The File Open and Write Test window will still display the last status of the file as Unlocked until the next time you refresh your browser.'); ?>')" /></p>
</form>
<?php } else { echo ''; } ?>
</td>
    <td width="45%">
<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') { ?>        
    
    <form name="bpsFlockUnLockForm" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_flock_unlock'); ?>
<p class="submit">
	<input type="submit" name="submit-ProFlockUnLock" value="<?php _e('Unlock .htaccess File'); ?>" class="button" onClick="return confirm('<?php _e('Click OK to Unlock your Root .htaccess file or click Cancel.\n\nNote: The File Open and Write Test window will still display the last status of the file as Locked until the next time you refresh your browser.'); ?>')" /></p>
</form>
<?php } else { echo ''; } ?>
</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">
    
    <!-- jQuery UI File Editor Tab Menu -->
<div id="bps-edittabs" class="bps-edittabs-class">
		<ul>
			<li><a href="#bps-edittabs-1">secure.htaccess</a></li>
			<li><a href="#bps-edittabs-2">default.htaccess</a></li>
			<li><a href="#bps-edittabs-3">maintenance.htaccess</a></li>
			<li><a href="#bps-edittabs-4">wpadmin-secure.htaccess</a></li>
            <li><a href="#bps-edittabs-5">Your Current Root htaccess File</a></li>
            <li><a href="#bps-edittabs-6">Your Current wp-admin htaccess File</a></li>
        </ul>
       
<?php 
$scrollto1 = isset($_REQUEST['scrollto1']) ? (int) $_REQUEST['scrollto1'] : 0; 
$scrollto2 = isset($_REQUEST['scrollto2']) ? (int) $_REQUEST['scrollto2'] : 0;
$scrollto3 = isset($_REQUEST['scrollto3']) ? (int) $_REQUEST['scrollto3'] : 0;
$scrollto4 = isset($_REQUEST['scrollto4']) ? (int) $_REQUEST['scrollto4'] : 0;
$scrollto5 = isset($_REQUEST['scrollto5']) ? (int) $_REQUEST['scrollto5'] : 0;
$scrollto6 = isset($_REQUEST['scrollto6']) ? (int) $_REQUEST['scrollto6'] : 0;
?>

<div id="bps-edittabs-1" class="bps-edittabs-page-class">
<form name="template1" id="template1" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_1'); ?>
    <div>
    <textarea cols="115" rows="27" name="newcontent1" id="newcontent1" tabindex="1"><?php echo get_secure_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($secure_htaccess_file) ?>" />
	<input type="hidden" name="scrollto1" id="scrollto1" value="<?php echo $scrollto1; ?>" />
    <p class="submit">
	<input type="submit" name="submit1" class="button" value="<?php esc_attr_e('Update File') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template1').submit(function(){ $('#scrollto1').val( $('#newcontent1').scrollTop() ); });
	$('#newcontent1').scrollTop( $('#scrollto1').val() ); 
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-2" class="bps-edittabs-page-class">
<form name="template2" id="template2" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_2'); ?>
	<div>
    <textarea cols="115" rows="27" name="newcontent2" id="newcontent2" tabindex="2"><?php echo get_default_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($default_htaccess_file) ?>" />
	<input type="hidden" name="scrollto2" id="scrollto2" value="<?php echo $scrollto2; ?>" />
    <p class="submit">
	<input type="submit" name="submit2" class="button" value="<?php esc_attr_e('Update File') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template2').submit(function(){ $('#scrollto2').val( $('#newcontent2').scrollTop() ); });
	$('#newcontent2').scrollTop( $('#scrollto2').val() );
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-3" class="bps-edittabs-page-class">
<form name="template3" id="template3" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_3'); ?>
	<div>
    <textarea cols="115" rows="27" name="newcontent3" id="newcontent3" tabindex="3"><?php echo get_maintenance_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($maintenance_htaccess_file) ?>" />
	<input type="hidden" name="scrollto3" id="scrollto3" value="<?php echo $scrollto3; ?>" />
    <p class="submit">
	<input type="submit" name="submit3" class="button" value="<?php esc_attr_e('Update File') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template3').submit(function(){ $('#scrollto3').val( $('#newcontent3').scrollTop() ); });
	$('#newcontent3').scrollTop( $('#scrollto3').val() );
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-4" class="bps-edittabs-page-class">
<form name="template4" id="template4" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_4'); ?>
	<div>
    <textarea cols="115" rows="27" name="newcontent4" id="newcontent4" tabindex="4"><?php echo get_wpadmin_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto4" id="scrollto4" value="<?php echo $scrollto4; ?>" />
    <p class="submit">
	<input type="submit" name="submit4" class="button" value="<?php esc_attr_e('Update File') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template4').submit(function(){ $('#scrollto4').val( $('#newcontent4').scrollTop() ); });
	$('#newcontent4').scrollTop( $('#scrollto4').val() );
});
/* ]]> */
</script>     
</div>

<?php
// File Editor Root .htaccess file Lock check with pop up Confirm message
function bpsStatusRHE() {
	clearstatcache();
	$file = ABSPATH . '.htaccess';
	$perms = @substr(sprintf(".%o.", fileperms($file)), -4);
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
	if (file_exists($file)) {
	$perms = str_replace('.', '', $perms);
	return $perms;
	}
	}
}
?>

<div id="bps-edittabs-5" class="bps-edittabs-page-class">
<form name="template5" id="template5" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_5'); ?>
	<div>
    <textarea cols="115" rows="27" name="newcontent5" id="newcontent5" tabindex="5"><?php echo get_root_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($root_htaccess_file) ?>" />
	<input type="hidden" name="scrollto5" id="scrollto5" value="<?php echo $scrollto5; ?>" />
    <p class="submit">
    <?php if (bpsStatusRHE($perms) == '404') { ?>
	<input type="submit" name="submit5" value="<?php esc_attr_e('Update File') ?>" class="button" onClick="return confirm('<?php _e('YOUR ROOT HTACCESS FILE IS LOCKED.\n\nYOUR FILE EDITS / CHANGES CANNOT BE SAVED.\n\nClick Cancel, copy the file editing changes you made to save them and then click the Unlock .htaccess File button to unlock your Root .htaccess file. After your Root .htaccess file is unlocked paste your file editing changes back into your Root .htaccess file and click this Update File button again to save your file edits / changes.'); ?>')" />
	<?php } else { ?>
	<input type="submit" name="submit5" class="button" value="<?php esc_attr_e('Update File') ?>" /></p>
<?php } ?>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template5').submit(function(){ $('#scrollto5').val( $('#newcontent5').scrollTop() ); });
	$('#newcontent5').scrollTop( $('#scrollto5').val() );
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-6" class="bps-edittabs-page-class">
<form name="template6" id="template6" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_6'); ?>
	<div>
    <textarea cols="115" rows="27" name="newcontent6" id="newcontent6" tabindex="6"><?php echo get_current_wpadmin_htaccess_file(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($current_wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto6" id="scrollto6" value="<?php echo $scrollto6; ?>" />
    <p class="submit">
	<input type="submit" name="submit6" class="button" value="<?php esc_attr_e('Update File') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template6').submit(function(){ $('#scrollto6').val( $('#newcontent6').scrollTop() ); });
	$('#newcontent6').scrollTop( $('#scrollto6').val() );
});
/* ]]> */
</script>     
</div>
</div></td>
    <td align="center" valign="top">
<?php _e("<div class=\"bps-file_download_title\"><strong>File Downloads</strong></div>"); ?>

<form name="bps-enable-download" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_enable_download'); ?>
<input type="hidden" name="filename" value="bps-enable-download-edit" />
<p class="submit">
<input type="submit" name="bps-enable-download" class="button" value="<?php esc_attr_e('Enable Master File Downloading') ?>" /></p>
</form>

<div id="bps-enable_bu_file_dl_button">
<form name="bps-enable-download-backup" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_enable_download-backup'); ?>
<input type="hidden" name="filename" value="bps-enable-download-edit-backup" />
<p class="submit">
<input type="submit" name="bps-enable-download-backup" class="button" value="<?php esc_attr_e('Enable Backed Up File Downloading') ?>" /></p>
</form>
</div>
<div id="bps-download_buttons_table">
<?php _e("<p class=\"bps-download_titles\">BPS Master Files</p>");
	
if (isset($_POST['bps-master-secure-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_download_secure' );
	header('Content-Description: File Transfer');
	header('Content-type: application/force-download');
	header('Content-Disposition: attachment; filename="secure.htaccess"');
	}
if (isset($_POST['bps-master-default-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_download_default' );	
	header("Content-Description: File Transfer");
	header("Content-type: application/force-download");
	header("Content-Disposition: attachment; filename=default.htaccess");
	}	
if (isset($_POST['bps-master-maintenance-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_download_maintenance' );	
	header("Content-Description: File Transfer");
	header("Content-type: application/force-download");
	header("Content-Disposition: attachment; filename=maintenance.htaccess");
	}
if (isset($_POST['bps-master-wpadmin-secure-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_download_wpadmin-secure' );	
	header("Content-Description: File Transfer");
	header("Content-type: application/force-download");
	header("Content-Disposition: attachment; filename=wpadmin-secure.htaccess");
	}
if (isset($_POST['bps-master-root-backup-htaccess-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_download_root-backup-htaccess' );	
	header("Content-Description: File Transfer");
	header("Content-type: application/force-download");
	header("Content-Disposition: attachment; filename=root.htaccess_backup");
	}	
if (isset($_POST['bps-master-wpadmin-backup-htaccess-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_download_wpadmin-backup-htaccess' );	
	header("Content-Description: File Transfer");
	header("Content-type: application/force-download");
	header("Content-Disposition: attachment; filename=wpadmin.htaccess_backup");
	}	
?> 

<form name="bps-master-secure-download" action="<?php echo get_site_url() .'/wp-content/plugins/bulletproof-security/admin/htaccess/secure.htaccess'; ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_secure'); ?>
<input type="submit" name="bps-master-secure-download" class="button" value="<?php esc_attr_e('secure.htaccess') ?>" onClick="return confirm('<?php _e('Click OK to Download the file now or click Cancel to cancel the download.'); ?>')" /></p>
</form>

<form name="bps-master-default-download" action="<?php echo get_site_url() .'/wp-content/plugins/bulletproof-security/admin/htaccess/default.htaccess'; ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_default'); ?>
<input type="hidden" name="filename" value="bps-default-download" />
<input type="submit" name="bps-master-default-download" class="button" value="<?php esc_attr_e('default.htaccess') ?>" /></p>
</form>

<form name="bps-master-maintenance-download" action="<?php echo get_site_url() .'/wp-content/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess'; ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_maintenance'); ?>
<input type="hidden" name="filename" value="bps-maintenance-download" />
<input type="submit" name="bps-master-maintenance-download" class="button" value="<?php esc_attr_e('maintenance.htaccess') ?>" /></p>
</form>

<form name="bps-master-wpadmin-secure-download" action="<?php echo get_site_url() .'/wp-content/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess'; ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_wpadmin-secure'); ?>
<input type="hidden" name="filename" value="bps-wpadmin-secure-download" />
<input type="submit" name="bps-master-wpadmin-secure-download" class="button" value="<?php esc_attr_e('wpadmin-secure.htaccess') ?>" /></p>
</form>

	<?php _e("<p class=\"bps-download_titles\">Backed Up htaccess Files</p>"); ?>
    
<form name="bps-master-root-backup-htaccess-download" action="<?php echo get_site_url() . '/wp-content/bps-backup/root.htaccess'; ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_root-backup-htaccess'); ?>
<input type="hidden" name="filename" value="bps-root-backup-htaccess-download" />
<input type="submit" name="bps-master-root-backup-htaccess-download" class="button" value="<?php esc_attr_e('root.htaccess Backup File') ?>" /></p>
</form>

<form name="bps-master-wpadmin-backup-htaccess-download" action="<?php echo get_site_url() . '/wp-content/bps-backup/wpadmin.htaccess'; ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_wpadmin-backup-htaccess'); ?>
<input type="hidden" name="filename" value="bps-wpadmin-backup-htaccess-download" />
<input type="submit" name="bps-master-wpadmin-backup-htaccess-download" class="button" value="<?php esc_attr_e('wpadmin.htaccess Backup File') ?>" /></p>
</form>
</div>    </td>
  </tr>
</table>
<?php } ?>
</div>

<div id="bps-tabs-6" class="bps-tab-page">
<h2><?php _e('Custom Code'); ?></h2>
<div id="bpsCustomCode" style="border-top:1px solid #999999;">

<h3><?php _e('Add Custom htaccess Code To Root and wp-admin htaccess Files'); ?>  <button id="bps-open-modal15" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content15" title="<?php _e('Custom Code'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>IMPORTANT!!! Custom Code Info IMPORTANT!!!</strong><br><br><strong>Add ONLY valid .htaccess code into these text boxes. If you want to add regular text then you need to add a pound sign # in front of the text to comment it out. If you do not do this then the next time you use AutoMagic and activate BulletProof Mode for your Root folder your website WILL crash.</strong><br><br>Your Custom Code is saved permanently to your WordPress Database until you delete it and will not be removed or deleted when you upgrade BPS.<br><br><strong>Root .htaccess File Custom Code and AutoMagic</strong><br>Any custom .htaccess code that you save here will be saved to your WP Database permanently and automatically be created in your Root .htaccess file when you click the Create secure.htaccess File AutoMagic button.<br><br><strong>CUSTOM CODE TOP: Add php.ini handler code and / or miscellaneous custom code here</strong><br>The CUSTOM CODE TOP text area should really ONLY be used for php.ini handler code if BPS is unable to detect your Web Host. You can add your php.ini handler code and miscellaneous custom .htaccess code in the CUSTOM CODE TOP text area together, but it is recommended that you ONLY use this text area for your php.ini handler code if your website requires php.ini handler code in your Root .htaccess file. BPS Pro ONLY: If BPS Pro is unable to detect your Web Host when you have a Private Name Server, you are using CloudFlare, you are using Pipe DNS or some other service that is blocking your true Web Host Name Servers and DNS information. The CUSTOM CODE BOTTOM text area should be used for miscellaneous custom .htaccess code.<br><br><strong>CUSTOM CODE PLUGIN FIXES: Add ONLY personal plugin fixes code here</strong><br>This text area is for plugin fixes that are specific to your website. BPS already has some plugin fixes included in the Root .htaccess file. Adding additional plugin fixes for your personal plugins on your website goes in this text area. For each plugin fix that you add above RewriteRule . - [S=12] you will need to increase the S= number by one. For Example: if you added 2 plugin fixes above the Adminer plugin fix they would be .htaccess Skip rules #13 and #14 - RewriteRule . - [S=13] and RewriteRule . - [S=14]. If you added a third Skip rule above #13 and #14 it would be Skip rule #15 - RewriteRule . - [S=15].<br><br><strong>CUSTOM CODE BOTTOM: Add miscellaneous custom .htaccess code here </strong><br>You can save any miscellaneous custom .htaccess code here as long as it is valid .htaccess code or if it is just plain text then you will need to comment it out with a pound sign # in front of the text.<br><br><strong>wp-admin .htaccess File Custom Code</strong><br>The wp-admin .htaccess File Custom Code feature works differently then the Root .htaccess Custom Code feature. The wp-admin .htaccess file does not use AutoMagic and your Custom Code is written directly to your wp-admin .htaccess file when you Activate BulletProof Mode for your wp-admin folder.<br><br><strong>CUSTOM CODE WPADMIN TOP: Add miscellaneous custom code here</strong><br>You can save any miscellaneous custom .htaccess code here as long as it is valid .htaccess code or if it is just plain text then you will need to comment it out with a pound sign # in front of the text.<br><br><strong>CUSTOM CODE WPADMIN PLUGIN FIXES: Add ONLY WPADMIN personal plugin fixes code here</strong><br>There are only a couple of plugins that require a skip rule in the wp-admin .htaccess file. This text area is for plugin fixes that may require a wp-admin .htaccess skip rule. There is currently one skip rule in the wp-admin .htaccess file - the WP Press This skip rule - RewriteRule . - [S=1]. For each plugin fix / skip rule that you add above RewriteRule . - [S=1] you will need to increase the S= number by one. For Example: if you added 2 wp-admin plugin fixes above the - WP Press This skip rule - they would be .htaccess Skip rules #2 and #3 - RewriteRule . - [S=2] and RewriteRule . - [S=3]. If you added a third Skip rule above #2 and #3 it would be Skip rule #4 - RewriteRule . - [S=4].<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>

<?php $scrolltoCCode = isset($_REQUEST['scrolltoCCode']) ? (int) $_REQUEST['scrolltoCCode'] : 0; 
	$scrolltoCCodeWPA = isset($_REQUEST['scrolltoCCodeWPA']) ? (int) $_REQUEST['scrolltoCCodeWPA'] : 0;
?>
        
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" class="bps-table_cell_help">
     <h3 style="margin-top:-10px;"><?php _e('Root .htaccess File Custom Code'); ?></h3>
<form name="bpsCustomCodeForm" action="options.php" method="post">
	<?php settings_fields('bulletproof_security_options_customcode'); ?>
	<?php $options = get_option('bulletproof_security_options_customcode'); ?>
<div><strong><label for="bps-CCode"><?php _e('CUSTOM CODE TOP: Add php.ini handler code and / or miscellaneous custom code here'); ?> </label></strong><br />
<strong><label for="bps-CCode"><?php _e('<font color="blue">ONLY add valid .htaccess code below or text commented out with a pound sign #</font>'); ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode[bps_customcode_one]" tabindex="1"><?php echo $options['bps_customcode_one']; ?></textarea><br /><br />
    <strong><label for="bps-CCode"><?php _e('CUSTOM CODE PLUGIN FIXES: Add ONLY personal plugin fixes code here'); ?> </label></strong><br />
 <strong><label for="bps-CCode"><?php _e('<font color="blue">ONLY add valid .htaccess code below or text commented out with a pound sign #</font>'); ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode[bps_customcode_two]" tabindex="2"><?php echo $options['bps_customcode_two']; ?></textarea><br /><br />
    <strong><label for="bps-CCode"><?php _e('CUSTOM CODE BOTTOM: Add miscellaneous custom .htaccess code here'); ?> </label></strong><br />
 <strong><label for="bps-CCode"><?php _e('<font color="blue">ONLY add valid .htaccess code below or text commented out with a pound sign #</font>'); ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode[bps_customcode_three]" tabindex="3"><?php echo $options['bps_customcode_three']; ?></textarea>
    <input type="hidden" name="scrolltoCCode" value="<?php echo $scrolltoCCode; ?>" />
    <p class="submit">
	<input type="submit" name="bps_customcode_submit" class="button" value="<?php esc_attr_e('Save Custom Code') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#bpsCustomCodeForm').submit(function(){ $('#scrolltoCCode').val( $('#bulletproof_security_options_customcode[bps_customcode_one]').scrollTop() ); });
	$('#bulletproof_security_options_customcode[bps_customcode_one]').scrollTop( $('#scrolltoCCode').val() ); 
});
/* ]]> */
</script>
</td>
    <td width="50%" valign="top" class="bps-table_cell_help" style="padding:70px 0px 0px 10px;"># TURN OFF YOUR SERVER SIGNATURE<br />
      ServerSignature Off<br /><br /># ADD A PHP HANDLER<br /># If you are using a PHP Handler add your web hosts PHP Handler below<br /><br /><span style="background-color:#FFFF00;"># CUSTOM CODE TOP - Your Custom .htaccess code will be created here with AutoMagic</span><br /><br /># DO NOT SHOW DIRECTORY LISTING<br />
# If you are getting 500 Errors when activating BPS then comment out Options -Indexes<br /># by adding a # sign in front of it. If there is a typo anywhere in this file you will also see 500 errors.<br />Options -Indexes<br /><br />
<table width="100%" border="0">
  <tr>
    <td style="padding:100px 0px 0px 0px;"># PLUGINS AND VARIOUS EXPLOIT FILTER SKIP RULES<br /># IMPORTANT!!! If you add or remove a skip rule you must change S= to the new skip number<br /># Example: If RewriteRule S=5 is deleted than change S=6 to S=5, S=7 to S=6, etc.<br /><br /><span style="background-color:#FFFF00;"># CUSTOM CODE PLUGIN FIXES - Your plugin fixes .htaccess code will be created here with AutoMagic</span><br /><br /># Adminer MySQL management tool data populate<br />RewriteCond %{REQUEST_URI} ^/wp-content/plugins/adminer/ [NC]<br />RewriteRule . - [S=12]<br /><br /></td>
  </tr>
  <tr>
    <td style="padding:140px 0px 0px 0px;"># IMPORTANT!!! DO NOT DELETE!!! the END WordPress text below<br />
      # END WordPress<br /><br /><span style="background-color:#FFFF00;"># CUSTOM CODE BOTTOM - Your Custom .htaccess code will be created here with AutoMagic</span><br /><br /># BLOCK HOTLINKING TO IMAGES<br /># To Test that your Hotlinking protection is working visit http://altlab.com/htaccess_tutorial.html<br />#RewriteEngine On</td>
  </tr>
</table>
</td>
  </tr>
   <tr>
    <td class="bps-table_cell_help">
    <h3><?php _e('wp-admin .htaccess File Custom Code'); ?></h3>
    <form name="bpsCustomCodeFormWPA" action="options.php" method="post">
	<?php settings_fields('bulletproof_security_options_customcode_WPA'); ?>
	<?php $options = get_option('bulletproof_security_options_customcode_WPA'); ?>
<div><strong><label for="bps-CCode"><?php _e('CUSTOM CODE WPADMIN TOP: Add miscellaneous custom code here'); ?> </label></strong><br />
<strong><label for="bps-CCode"><?php _e('<font color="blue">ONLY add valid .htaccess code below or text commented out with a pound sign #</font>'); ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode_WPA[bps_customcode_one_wpa]" tabindex="4"><?php echo $options['bps_customcode_one_wpa']; ?></textarea><br /><br />
   <strong><label for="bps-CCode"><?php _e('CUSTOM CODE WPADMIN PLUGIN FIXES: Add ONLY WPADMIN personal plugin fixes code here'); ?> </label></strong><br />
 <strong><label for="bps-CCode"><?php _e('<font color="blue">ONLY add valid .htaccess code below or text commented out with a pound sign #</font>'); ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode_WPA[bps_customcode_two_wpa]" tabindex="5"><?php echo $options['bps_customcode_two_wpa']; ?></textarea>
    <input type="hidden" name="scrolltoCCodeWPA" value="<?php echo $scrolltoCCodeWPA; ?>" />
    <p class="submit">
	<input type="submit" name="bps_customcode_submit_wpa" class="button" value="<?php esc_attr_e('Save wp-admin Custom Code') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#bpsCustomCodeFormWPA').submit(function(){ $('#scrolltoCCodeWPA').val( $('#bulletproof_security_options_customcode_WPA[bps_customcode_one_wpa]').scrollTop() ); });
	$('#bulletproof_security_options_customcode_WPA[bps_customcode_one_wpa]').scrollTop( $('#scrolltoCCodeWPA').val() ); 
});
/* ]]> */
</script>
</td>
    <td valign="top" class="bps-table_cell_help">
    <table width="100%" border="0">
  <tr>
    <td style="padding:70px 0px 0px 10px;"># BEGIN OPTIONAL WP-ADMIN ADDITIONAL SECURITY MEASURES:<br /><br /># BEGIN CUSTOM CODE WPADMIN TOP: Add miscellaneous custom code here<br /><span style="background-color:#FFFF00;"># CCWTOP - Your custom code will be created here when you activate wp-admin BulletProof Mode</span><br /># END CUSTOM CODE WPADMIN TOP<br /><br /># WP-ADMIN DIRECTORY PASSWORD PROTECTION - .htpasswd<br /># The BPS root .htaccess file already has a security rule that blocks access to all </td>
  </tr>
  <tr>
    <td style="padding:175px 0px 0px 10px;"># REQUEST METHODS FILTERED<br />RewriteEngine On<br />RewriteCond %{REQUEST_METHOD} ^(HEAD|TRACE|DELETE|TRACK|DEBUG) [NC]<br />RewriteRule ^(.*)$ - [F,L]<br /><br /># BEGIN CUSTOM CODE WPADMIN PLUGIN FIXES: Add ONLY WPADMIN personal plugin fixes code here<br /><span style="background-color:#FFFF00;"># CCWPF - Your custom code will be created here when you activate wp-admin BulletProof Mode</span><br /># END CUSTOM CODE WPADMIN PLUGIN FIXES<br /><br /># Allow wp-admin files that are called by plugins<br /># Fix for WP Press This</td>
  </tr>
</table>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>
</div>
</div>

<div id="bps-tabs-7" class="bps-tab-page">
<h2><?php _e('BulletProof Security Maintenance Mode'); ?></h2>

<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    <div id="bps-maintenance_form_table">
<h3><?php _e('Website Maintenance Mode Settings'); ?></h3>
<h3><?php _e('<font color="red"><strong>CAUTION: </strong></font>'); ?>  <button id="bps-open-modal16" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content16" title="<?php _e('Website Maintenance Mode'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Your Maintenance Mode Form data is saved to the WordPress Database and will remain permanently until you delete it. When you upgrade BPS your form data will still be saved in your database.</strong><br><br><strong>Maintenance Mode Activation Steps</strong><br><br><strong>Filling In The Maintenance Mode Settings Form</strong><br><strong>1. Fill out the Website Maintenance Mode Form</strong><br> -- You can copy and paste the example Background Image URL into the Background Image text field if you want to use the background image file that comes with BPS. If you have another background image file that you want to use then just name it with the same name as the example image file and copy it to the /bulletproof-security folder. If you do not want a background image then leave this text field blank. The background color will be white. If you want to customize the Website Under Maintenance template then download this file located in this folder /bulletproof-security/admin/htaccess/bp-maintenance.php.<br><strong>2. Click the Save Form Settings button to save your form data to your database.</strong><br><strong>3. Click the Create Form button to create your Website Under Maintenance form.</strong><br><strong>4. Click the Preview Form button to preview your Website Under Maintenance page.</strong><br> -- If you see a 404 or 403 Forbidden message in the popup preview window refresh the popup preview window or just close the popup window and click the Preview button again.<br> -- You can use the Preview button at any time to preview how your site will be displayed to everyone else except you when your website is in Maintenance Mode.<br><br><strong>Create Your Maintenance Mode .htaccess File</strong><br>After you have finished previewing your Website Under Maintenance page, click the Create htaccess File button. This creates your Maintenance Mode .htaccess file for your website. Your current Public IP address and correct RewriteBase and RewriteRule are included when this new Maintenance Mode .htaccess file is created.<br><br><strong>Activate Website Under Maintenance Mode</strong><br>Select the Maintenance Mode radio button and click the Activate Maintenance Mode button. Your website is now in Maintenance Mode. Everyone else will see your Website Under Maintenance page while you can still view and work on your site as you normally would.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="bps-maintenance-values" action="options.php" method="post">
<?php settings_fields('bulletproof_security_options_maint'); ?>
			<?php $options = get_option('bulletproof_security_options_maint'); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="bps-site-title"><?php _e('Site Title:') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-site-title]" type="text" value="<?php echo $options['bps-site-title']; ?>" class="regular-text" /><span class="description"><?php _e('Add Your Page Title') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-message-1"><?php _e('Message 1:') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-message-1]" type="text" value="<?php echo $options['bps-message-1']; ?>" class="regular-text" /><span class="description"><?php _e('Add Your Message') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-message-2"><?php _e('Message 2:') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-message-2]" type="text" value="<?php echo $options['bps-message-2']; ?>" class="regular-text" /><span class="description"><?php _e('Add Another Message or Not') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-retry-after"><?php _e('Retry-After:') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-retry-after]" type="text" value="<?php echo $options['bps-retry-after']; ?>" class="regular-text" /><span class="description"><?php _e('259200') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-background-image"><?php _e('Background Image') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-background-image]" type="text" value="<?php echo $options['bps-background-image']; ?>" class="regular-text" /><span class="description"><?php echo get_site_url(); ?>/wp-content/plugins/bulletproof-security/abstract-blue-bg.png</span></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="bps-maintenance-values_submit" class="button" value="<?php esc_attr_e('Save Form Settings') ?>" />
</p>
</form>


<form name="bps-maintenance-create-values" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-7" method="post">
<?php wp_nonce_field('bulletproof_security_create_values_form'); ?>
<input type="hidden" name="mmfilename" value="bps-maintenance-create-valuesH" />
<p class="submit">
<input type="submit" name="bps-maintenance-create-values_submit" class="button" value="<?php esc_attr_e('Create Form') ?>" /></p>
</form>

<!-- this is the Enable Download form reused for maintenance mode Preview -->
<form name="bps-enable-download" method="POST" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-7" target="" onSubmit="window.open('<?php echo get_site_url(); ?>/wp-content/plugins/bulletproof-security/admin/htaccess/bp-maintenance.php','','scrollbars=yes,menubar=yes,width=800,height=600,resizable=yes,status=yes,toolbar=yes')">
<?php wp_nonce_field('bulletproof_security_enable_download'); ?>
<input type="hidden" name="filename" value="bps-enable-download-edit" />
<p class="submit">
<input type="submit" name="bps-enable-download" class="button" value="<?php esc_attr_e('Preview Form') ?>" /></p>
</form>
</div>

<h3><?php _e('Activate Website Under Maintenance Mode'); ?></h3>
<h3><?php _e('<font color="red"><strong>CAUTION: </strong></font>'); ?>  <button id="bps-open-modal17" class="bps-modal-button"><?php _e('Read Me'); ?></button></h3>
<div id="bps-modal-content17" title="<?php _e('Activate Maintenance Mode'); ?>">
	<p><?php _e('<strong>This Read Me Help window is draggable and resizable</strong><br><br><strong>Activating Maintenance Mode will automatically unlock your Root .htaccess file. Be sure to lock your Root .htaccess file after you have put your site back in BulletProof Mode.</strong><br><br><strong>You must click the Create htaccess File button FIRST to create your Maintenance Mode htaccess file before activating Maintenance Mode if you want to be able to continue working on your website while everyone else sees the Website Under Maintenance page</strong><br>After you have created your Maintenance Mode .htaccess file - Select the Maintenance Mode radio button and click Activate.<br><br><strong>You might see BPS error messages displayed when you put your site in Maintenance Mode. You can disregard these error messages. When you put your site back into BulletProof Mode these error messages will automatically go away.</strong><br><br><strong>To switch out of or exit Maintenance Mode just activate BulletProof Security Mode for your Root folder on the Security Modes page.</strong><br><br>To view the Maintenance Mode page that your website visitors are seeing click the Preview Form button.<br><br>When you activate Maintenance Mode your website will be put in HTTP 503 Service Temporarily Unavailable status and display a Website Under Maintenance page to everyone except you. Your current Public IP address was automatically added to the Maintenance Mode file as well as the correct .htaccess RewriteRule and RewriteBase for your website when you clicked the Create File button.<br><br>To manually add additional IP addresses that are allowed to view your website normally use the BPS File Editor to add them. To view your current Public IP address click on the System Info tab menu.<br><br><strong>Your current Public IP address is also displayed on the Website Under Maintenance page itself.</strong><br><br>Your SERPs (website or web page ranking) will not be affected by putting your website in Maintenance Mode for several days for existing websites. To manually add additional IP addresses that can view your website you would add them using the BPS File Editor.<br><br>If you are unable to log back into your WordPress Dashboard and are also seeing the Website Under Maintenance page then you will need to FTP to your website and either delete the .htaccess file in your website root folder or download the .htaccess file - add your correct current Public IP address and upload it back to your website.<br><br><strong>BPS Pro Video Tutorial links can be found in the Help & FAQ pages.</strong>'); ?></p>
</div>

<form name="bps-auto-write-maint" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-7" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_maint'); ?>
<input type="hidden" name="filename" value="bps-auto-write-maint_write" />
<p class="submit">
<input type="submit" name="bps-auto-write-maint" class="button" value="<?php esc_attr_e('Create htaccess File') ?>" /></p>
</form>

<form name="BulletProof-Maintenance" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-7" method="post">
<?php wp_nonce_field('bulletproof_security_maintenance_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection15" type="radio" value="bpmaintenance" class="tog" <?php checked('', $bpmaintenance); ?> />
	<?php _e('Maintenance Mode'); ?></label></th>
	<td class="url-path"><?php _e('<font color="green">Click the Create htaccess File button first to create your Maintenance Mode .htaccess file. To switch out of or exit Maintenance Mode just activate BulletProof Security Mode for your Root Folder.</font><strong> Read the <font color="red">CAUTION:</font> Read Me button for more detailed information.</strong>'); ?></td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit15" class="button" value="<?php esc_attr_e('Activate Maintenance Mode') ?>" />
</p>
</form>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>
</div>

<div id="bps-tabs-8">
<h2><?php _e('BulletProof Security Help &amp; FAQ'); ?></h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
   <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/category/bulletproof-security-contributors/" target="_blank"><?php _e('Contributors Page'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/#root-or-subfolder-wordpress-installation" target="_blank"><?php _e('Website Domain Root Help Info'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/331/bulletproof-security-plugin-support/bulletproof-security-donations-page/" target="_blank"><?php _e('BPS Donations'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/#bps-backup-restore" target="_blank"><?php _e('Backup & Restore Help Info'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/" target="_blank"><?php _e('BPS .46.9 Guide'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2585/bulletproof-security-plugin-support/wordpress-website-maintenance-wordpress-maintenance-mode" target="_blank"><?php _e('Maintenance Mode Help Info'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/#bps-45-new-features" target="_blank"><?php _e('BPS .46.9 Features'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/#bps-advanced-coding-modfications" target="_blank"><?php _e('BPS .46.9 Coding Modifications Help Info'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/319/bulletproof-security-plugin-support/bulletproof-security-comments-questions-problems-wishlist/" target="_blank"><?php _e('Post Questions and Comments for Assistance'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/#modifying-htaccess-files" target="_blank"><?php _e('Modifying BPS .htaccess Files for WordPress Subfolders'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/1183/bulletproof-security-plugin-support/bulletproof-security-plugin-bps-version-45-screenshots/" target="_blank"><?php _e('BPS .46.9 Screenshots'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2185/bulletproof-security-plugin-support/bulletproof-security-file-editing-editing-files-within-the-wordpress-dashboard/" target="_blank"><?php _e('File Editing Within The Dashboard Help Info'); ?></a></td>
  </tr>
  <tr>
    <td width="50%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2330/bulletproof-security-plugin-support/bps-future-features-that-will-be-included-in-future-releases-of-bps" target="_blank"><?php _e('BPS Future and Whats New From Previous Versions'); ?></a></td>
    <td width="50%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2190/bulletproof-security-plugin-support/bulletproof-security-file-uploading-and-file-downloading-uploading-and-downloading-files-within-the-wordpress-dashboard/" target="_blank"><?php _e('File Uploading &amp; Downloading Within The Dashboard Help Info'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2252/bulletproof-security-plugin-support/checking-plugin-compatibility-with-bps-plugin-testing-to-do-list/" target="_blank"><?php _e('Plugin Compatibility Testing - Recent New Permanent Plugin Fixes'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2304/wordpress-tips-tricks-fixes/permalinks-wordpress-custom-permalinks-wordpress-best-wordpress-permalinks-structure/" target="_blank"><?php _e('WP Permalinks - Custom Permalink Structure Help Info'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank"><?php _e('BulletProof Security Error, Warning and HUD Messages'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2239/bulletproof-security-plugin-support/adding-a-custom-403-forbidden-page-htaccess-403-errordocument-directive-examples/" target="_blank"><?php _e('Adding a Custom 403 Forbidden Page For Your Website'); ?></a></td>
  </tr>
    <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/3898/bulletproof-security-pro/custom-code-help-and-faq-how-to-use-custom-code-adding-custom-code-to-automagic/" target="_blank"><?php _e('Custom Code Feature Help Info'); ?></a></td>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-9">
<h2><?php _e('Whats New in'); ?><?php echo bpsWhatVersion(); ?></h2>
<h3><?php _e('The Whats New page will list new changes that were made in each new version release of BulletProof Security'); ?></h3>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-whats_new_table">
  <tr>
   <td width="1%" class="bps-table_title_no_border">&nbsp;</td>
   <td width="99%" class="bps-table_title_no_border">&nbsp;</td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
 <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('Significant changes to both the Root and wp-admin .htaccess files'); ?></strong><br /><?php _e('Create new Master .htaccess files with AutoMagic and activate all BulletProof Modes.'); ?></td>
  </tr>
    <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('NEW Custom Code feature added'); ?></strong><br /><?php _e('Root .htaccess File Custom Code and AutoMagic:  Any custom .htaccess code that you save here will be saved to your WP Database permanently and automatically be created in your Root .htaccess file when you click the Create secure.htaccess File AutoMagic button. wp-admin .htaccess File Custom Code:  The wp-admin .htaccess File Custom Code feature works differently then the Root .htaccess Custom Code feature. The wp-admin .htaccess file does not use AutoMagic and your Custom Code is written directly to your wp-admin .htaccess file when you Activate BulletProof Mode for your wp-admin folder.'); ?></td>
  </tr>
    <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('Coding improvements / enhancements'); ?></strong><br /><?php _e('Coding enhancements and improvements made throughout BPS Free.'); ?></td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('BPS Pro 5.1.5'); ?></strong><br /><?php _e('The first generation of AutoRestore released in BPS Pro 5.1.5. BPS Pro 5.1.7 will have full site AutoRestore.'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom_no_border">&nbsp;</td>
    <td class="bps-table_cell_bottom_no_border">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-10" class="bps-tab-page">
<h2><?php _e('My Notes'); ?></h2>
<div id="bpsMyNotesborder" style="border-top:1px solid #999999;">
<h3><?php _e('Save any personal notes or htaccess code to your WordPress Database'); ?></h3>
</div>
<?php if (!current_user_can('manage_options')) { echo 'Permission Denied'; } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
	<?php $scrolltoNotes = isset($_REQUEST['scrolltoNotes']) ? (int) $_REQUEST['scrolltoNotes'] : 0; ?>

<form name="myNotes" action="options.php" method="post">
	<?php settings_fields('bulletproof_security_options_mynotes'); ?>
	<?php $options = get_option('bulletproof_security_options_mynotes'); ?>
<div>
    <textarea cols="130" rows="27" name="bulletproof_security_options_mynotes[bps_my_notes]" tabindex="1"><?php echo $options['bps_my_notes']; ?></textarea>
    <input type="hidden" name="scrolltoNotes" value="<?php echo $scrolltoNotes; ?>" />
    <p class="submit">
	<input type="submit" name="myNotes_submit" class="button" value="<?php esc_attr_e('Save My Notes') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#myNotes').submit(function(){ $('#scrolltoNotes').val( $('#bulletproof_security_options_mynotes[bps_my_notes]').scrollTop() ); });
	$('#bulletproof_security_options_mynotes[bps_my_notes]').scrollTop( $('#scrolltoNotes').val() ); 
});
/* ]]> */
</script>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>

</div>

<div id="bps-tabs-11">
<h2><?php _e('BulletProof Security Pro Feature Highlights'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="62%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2835/bulletproof-security-pro/bulletproof-security-pro-features/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro Features'); ?></a></td>
    <td width="38%" rowspan="7" valign="top" class="bps-table_cell_help">
    <a href="http://www.ait-pro.com/aitpro-blog/3395/bulletproof-security-pro/bps-free-vs-bps-pro-feature-comparison/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro Vs BPS Free Feature Comparison'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/2837/bulletproof-security-pro/bulletproof-security-pro-screenshots/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro Screenshots'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/2835/bulletproof-security-pro/bulletproof-security-pro-features/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.0'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.1'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3529/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.2'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3605/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-3" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.3'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3732/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.4'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3845/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.5'); ?></a>
    </td>
  </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('Hacker Logging / HTTP Error Logging / 400, 403 and 404 Error Logging - Log hacking attempts against your website. When a hacker attempts to hack your website the hackers IP address, Host name, Request Method, Referering link, the file name or requested resource, the user agent of the hacker and the query string used in the hack attempt are logged.'); ?></td>
    </tr>
  <tr>
     <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('AutoRestore CM: Automatic file restore countermeasure website security that will automatically restore your WP Core Root Files if a hacker has compromised your Web Host Server and injected code into your WP Core Root files...'); ?></td>
    </tr>  
  <tr>
     <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('F-Lock: Lock ALL WordPress Mission Critical files on the fly, lock index.php, lock wp-config.php, lock wp-blog-header.php, lock .htaccess files...'); ?></td>
    </tr>  
  <tr>
     <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('Custom php.ini File Creator, php.ini File Manager, php.ini Editor, Protected PHP Error Log, PHP Error Alerts, Secure phpinfo Viewer...'); ?></td>
    </tr>
    <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('Monitoring &amp; Alerting Options: WP Dashboard, BPS Pro pages only, Turned off, Email Alerts...'); ?></td>
    </tr>
  <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('Decode Hackers Code: Base64 Decoder / Encoder, Mcrypt Decrypt / Encrypt, Crypt Encryption...'); ?></td>
    </tr>
  <tr>
   <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('File String Finder, Replacer, Remover: Search all files for text, code, etc....'); ?></td>
    <td class="bps-table_cell_help">&nbsp;</td>
   </tr>
  <tr>
     <td class="bps-table_cell_help" style="font-size:14px;"><?php _e('Database String Finder: Search entire Database for text, code, etc....'); ?></td>
    <td class="bps-table_cell_help">&nbsp;</td>
    </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
   <tr>
    <td colspan="2" class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>

<div id="bps-tabs-12">
<h2><?php _e('Sucuri SiteCheck - Free website malware & blacklist scan'); ?></h2>
<h3><?php _e('BPS is designed to protect your website from being hacked. If your website was already hacked prior to installing BPS then BPS will not automatically clean it up. Sucuri offers hacked website cleanup services.'); ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    <div id="SucuriLogo" style="position:relative; top:0px; left:0px;"><img src="<?php echo get_site_url(); ?>/wp-content/plugins/bulletproof-security/admin/images/sucuri-logo.png" style="float:left; padding:0px 10px 0px 0px; margin:0px;" /><h3><?php _e('<em>'.'"'.'...the sheer nature of malware makes it very challenging to give you 100% certainty you will not get infected. The good news though is that we are doing everything in our power to ensure that 1 - you do not get infected, but 2 - if you do, we have the best solution to get you back on your feet.'.'"'.'</em><br> -- Tony Perez, CFO Sucuri, LLC'); ?></h3><a href="http://sitecheck.sucuri.net/" target="_blank" title="Link opens in new browser window">Sucuri SiteCheck Scanner</a></div>    
    </td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>
        
<div id="AITpro-link">BulletProof Security Plugin by <a href="http://www.ait-pro.com/" target="_blank" title="AITpro Website Design">AITpro Website Design</a>
</div>
</div>
</div>