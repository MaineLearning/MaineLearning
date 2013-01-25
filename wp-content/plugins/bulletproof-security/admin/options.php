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

<h2 style="margin-left:70px;"><?php _e('BulletProof Security ~ htaccess Core', 'bulletproof-security'); ?></h2>
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
        echo '<div class="bps-downloaded">'.sprintf(__('Downloaded %s times', 'bulletproof-security'),number_format_i18n($bpsapi->downloaded)).'.</div>';
      }
?>
		<?php if ( !empty($bpsapi->rating) ) : ?>
		<div class="bps-star-container" title="<?php echo esc_attr(sprintf(__('Average Rating (%s ratings)', 'bulletproof-security'),number_format_i18n($bpsapi->num_ratings))); ?>">
			<div class="bps-star bps-star-rating" style="width: <?php echo esc_attr($bpsapi->rating) ?>px"></div>
			<div class="bps-star bps-star5"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/star.png'); ?>" alt="<?php _e('5 stars', 'bulletproof-security') ?>" /></div>
			<div class="bps-star bps-star4"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/star.png'); ?>" alt="<?php _e('4 stars', 'bulletproof-security') ?>" /></div>
			<div class="bps-star bps-star3"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/star.png'); ?>" alt="<?php _e('3 stars', 'bulletproof-security') ?>" /></div>
			<div class="bps-star bps-star2"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/star.png'); ?>" alt="<?php _e('2 stars', 'bulletproof-security') ?>" /></div>
			<div class="bps-star bps-star1"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/star.png'); ?>" alt="<?php _e('1 star', 'bulletproof-security') ?>" /></div>
		</div>
		<div class="bps-star-link"><a target="_blank" title="Link opens in new browser window" href="http://wordpress.org/extend/plugins/<?php echo $bpsapi->slug ?>/"> <?php _e('Please Rate BPS', 'bulletproof-security'); ?></a> <small><?php echo sprintf(__('%s Ratings', 'bulletproof-security'),number_format_i18n($bpsapi->num_ratings)); ?> </small></div>
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
	
	$old = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
	$new = ABSPATH . '/.htaccess';
	$old1 = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
	$new1 = ABSPATH . '/.htaccess';
	
	$selected_radio = $_POST['selection12'];
	if ($selected_radio == 'bpsecureroot') {
	$bpsecureroot = 'checked';
		@copy($old1, $new1);
		chmod($new1, 0644);
		if (!copy($old1, $new1)) {
	$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security Root Folder Protection! Your Website is NOT protected with BulletProof Security!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('BulletProof Security Root Folder Protection Activated. Your website Root folder is now protected with BulletProof Security.', 'bulletproof-security').'</strong></font><br><font color="red"><strong>'.__('IMPORTANT!', 'bulletproof-security').'</strong></font><strong>'.__(' BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder.', 'bulletproof-security').'</strong><br>';
	echo $text;
    }
	}
	elseif ($selected_radio == 'bpdefaultroot') {
	$bpdefaultroot = 'checked';
		@copy($old, $new);
		chmod($new, 0644);
		if (!copy($old, $new)) {
	$text = '<font color="red"><strong>'.__('Failed to Activate Default .htaccess Mode!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="red"><strong>'.__('Warning: Default .htaccess Mode Is Activated In Your Website Root Folder. Your Website Is Not Protected With BulletProof Security.', 'bulletproof-security').'</strong></font>';
	echo $text;
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
	
	$oldadmin1 = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
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
	$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security wp-admin Folder Protection! Your wp-admin folder is NOT protected with BulletProof Security!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	if (file_exists($newadmin1)) {
	$bpsBaseContent = @file_get_contents($newadmin1);
		$bpsBaseContent = str_replace($bpsString1, $bpsReplace1, $bpsBaseContent);
		$bpsBaseContent = str_replace($bpsString2, $bpsReplace2, $bpsBaseContent);
		@file_put_contents($newadmin1, $bpsBaseContent);
	$text = '<font color="green"><strong>'.__('BulletProof Security wp-admin Folder Protection Activated. Your wp-admin folder is now protected with BulletProof Security.', 'bulletproof-security').'</strong></font>';
	echo $text;
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
	$text = '<font color="red"><strong>'.__('Failed to Delete the wp-admin .htaccess file! The file does not exist. It may have been deleted or renamed already.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('The wp-admin .htaccess file has been Deleted.', 'bulletproof-security').'</strong></font><font color="red"><strong>'.__(' Your wp-admin folder is no longer .htaccess protected.', 'bulletproof-security').'</strong></font>'.__(' If you are testing then be sure to reactivate BulletProof Mode for your wp-admin folder when you are done testing. If you are removing BPS from your website then be sure to also Activate Default Mode for your Root folder. The Root and wp-admin BulletProof Modes must be activated together or removed together.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	}
}

// Form rename Deny All htaccess file to .htaccess for the BPS Master htaccess folder
$bps_rename_htaccess_files = 'unchecked';
if (isset($_POST['submit8']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_denyall_master' );
	
	$bps_rename_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_renamed = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
		
	$selected_radio = $_POST['selection8'];
	if ($selected_radio == 'bps_rename_htaccess_files') {
	$bps_rename_htaccess_files = 'checked';
		@copy($bps_rename_htaccess, $bps_rename_htaccess_renamed);
		if (!copy($bps_rename_htaccess, $bps_rename_htaccess_renamed)) {
	$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security Deny All Folder Protection! Your BPS Master htaccess folder is NOT Protected with Deny All htaccess folder protection!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = __('BulletProof Security Deny All Folder Protection', 'bulletproof-security').'<font color="green"><strong>'.__(' Activated. ', 'bulletproof-security').'</strong></font>'.__('Your BPS Master htaccess folder is Now Protected with Deny All htaccess folder protection.', 'bulletproof-security');
	echo $text;
	}
}
}

// Form copy and rename the Deny All htaccess file to the BPS backup folder
$bps_rename_htaccess_files_backup = 'unchecked';
if (isset($_POST['submit14']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_denyall_bpsbackup' );
	
	$bps_rename_htaccess_backup = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_backup_online = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	
	$selected_radio = $_POST['selection14'];
	if ($selected_radio == 'bps_rename_htaccess_files_backup') {
	$bps_rename_htaccess_files_backup = 'checked';
		copy($bps_rename_htaccess_backup, $bps_rename_htaccess_backup_online);
		if (!copy($bps_rename_htaccess_backup, $bps_rename_htaccess_backup_online)) {
	$text = '<font color="red"><strong>'.__('Failed to Activate BulletProof Security Deny All Folder Protection! Your BPS /wp-content/bps-backup folder is NOT Protected with Deny All htaccess folder protection!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = __('BulletProof Security Deny All Folder Protection', 'bulletproof-security').'<font color="green"><strong>'.__(' Activated. ', 'bulletproof-security').'</strong></font>'.__('Your BPS /wp-content/bps-backup folder is Now Protected with Deny All htaccess folder protection.', 'bulletproof-security');
	echo $text;
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
	$text = '<font color="red"><strong>'.__('You do not currently have an .htaccess file in your Root folder to backup.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {	
	if (file_exists($old_backroot)) { 
 		@copy($old_backroot, $new_backroot);
		if (!copy($old_backroot, $new_backroot)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your Root .htaccess File! File copy function failed. Check the folder permissions for the /wp-content/bps-backup folder. Folder permissions should be set to 755.', 'bulletproof-security').'</strong></font><br><br>';
	echo $text;
	} else {
	$text = '<font color="green"><strong>'.__('Your currently active Root .htaccess file has been backed up successfully! ', 'bulletproof-security').'</strong></font><br>'.__('Use the Restore feature to restore your .htaccess files if you run into a problem at any time. If you make additional changes or install a plugin that writes to the htaccess files then back them up again. This will overwrite the currently backed up htaccess files. Please read the ', 'bulletproof-security').'<font color="red"><strong>'.__(' CAUTION: ', 'bulletproof-security').'</strong></font>'.__('Read Me button on the Backup & Restore Page for more detailed information.', 'bulletproof-security').'<br><br>';
	echo $text;
	
	if ( !file_exists($old_backwpadmin)) { 
	$text = '<font color="red"><strong>'.__('You do not currently have an .htaccess file in your wp-admin folder to backup.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
	if (file_exists($old_backwpadmin)) { 	
		@copy($old_backwpadmin, $new_backwpadmin);
		if (!copy($old_backwpadmin, $new_backwpadmin)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your wp-admin .htaccess File! File copy function failed. Check the folder permissions for the /wp-content/bps-backup folder. Folder permissions should be set to 755.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
	$text = '<font color="green"><strong>'.__('Your currently active wp-admin .htaccess file has been backed up successfully!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
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
		$text = '<font color="red"><strong>'.__('Failed to Restore Your Root .htaccess File! This is most likely because you DO NOT currently have a Backed up Root .htaccess file.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
   	} else {
		$text = '<font color="green"><strong>'.__('Your Root .htaccess file has been Restored successfully!', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	if (file_exists($old_restorewpadmin)) { 	
		@copy($old_restorewpadmin, $new_restorewpadmin);
	if (!copy($old_restorewpadmin, $new_restorewpadmin)) {
	$text = '<font color="red"><strong>'.__('Failed to Restore Your wp-admin .htaccess File! This is most likely because you DO NOT currently have a Backed up wp-admin .htaccess file.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
   	} else {
		$text = '<font color="green"><strong>'.__('Your wp-admin .htaccess file has been Restored successfully!', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}
}}}}}

// Form - Backup the BPS Master Files to /wp-content/bps-backup/master-backups
$backup_master_htaccess_files = 'unchecked';
if (isset($_POST['submit11']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_backup_master_htaccess_files' );

$default_master = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
$default_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_default.htaccess';
$secure_master = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
$secure_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_secure.htaccess';
$wpadmin_master = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
$wpadmin_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_wpadmin-secure.htaccess';
$maintenance_master = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/maintenance.htaccess';
$maintenance_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_maintenance.htaccess';
$bp_maintenance_master = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/bp-maintenance.php';
$bp_maintenance_master_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bp-maintenance.php';
$bps_maintenance_values = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
$bps_maintenance_values_backup = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bps-maintenance-values.php';

	$selected_radio = $_POST['selection11'];
	if ($selected_radio == 'backup_master_htaccess_files') {
		$backup_master_htaccess_files = 'checked';
	if (file_exists($default_master)) { 
 		copy($default_master, $default_master_backup);
		if (!copy($default_master, $default_master_backup)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your default.htaccess File!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('The default.htaccess file has been backed up successfully!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	if (file_exists($secure_master)) { 	
		copy($secure_master, $secure_master_backup);
		if (!copy($secure_master, $secure_master_backup)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your secure.htaccess File!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('The secure.htaccess file has been backed up successfully!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	if (file_exists($wpadmin_master)) { 	
		copy($wpadmin_master, $wpadmin_master_backup);
		if (!copy($wpadmin_master, $wpadmin_master_backup)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your wpadmin-secure.htaccess File!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('The wpadmin-secure.htaccess file has been backed up successfully!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	if (file_exists($maintenance_master)) { 	
		copy($maintenance_master, $maintenance_master_backup);
		if (!copy($maintenance_master, $maintenance_master_backup)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your maintenance.htaccess File!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('The maintenance.htaccess file has been backed up successfully!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	if (file_exists($bp_maintenance_master)) { 	
		copy($bp_maintenance_master, $bp_maintenance_master_backup);
		if (!copy($bp_maintenance_master, $bp_maintenance_master_backup)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your bp-maintenance.php File!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('The bp-maintenance.php file has been backed up successfully!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	if (file_exists($bps_maintenance_values)) { 	
		copy($bps_maintenance_values, $bps_maintenance_values_backup);
		if (!copy($bps_maintenance_values, $bps_maintenance_values_backup)) {
	$text = '<font color="red"><strong>'.__('Failed to Backup Your bps-maintenance-values.php File!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
   	} else {
	$text = '<font color="green"><strong>'.__('The bps-maintenance-values.php file has been backed up successfully!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
}}}}}}}}
	
// Form - Activate Maintenance Mode copy and rename maintenance htaccess, bp-maintenance.php and bps-maintenance-values.php to root
$bpmaintenance = 'unchecked';
if (isset($_POST['submit15']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_maintenance_copy' );

$oldmaint = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/maintenance.htaccess';
$newmaint = ABSPATH . '/.htaccess';
$oldmaint1 = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/bp-maintenance.php';
$newmaint1 = ABSPATH . '/bp-maintenance.php';
$oldmaint_values = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
$newmaint_values = ABSPATH . '/bps-maintenance-values.php';

	$selected_radio = $_POST['selection15'];
	if ($selected_radio == 'bpmaintenance') {
	$bpmaintenance = 'checked';
		@copy($oldmaint, $newmaint);
		chmod($newmaint, 0644);
		copy($oldmaint1, $newmaint1);
		copy($oldmaint_values, $newmaint_values);
		if (!copy($oldmaint, $newmaint)) {
	$text = '<p><font color="red"><strong>'.__('Failed to Activate Maintenance Mode! Your Website is NOT in Maintenance Mode!', 'bulletproof-security').'<br>'.__('If your Root .htaccess file is locked you must unlock it first before activating Maintenance Mode.', 'bulletproof-security').'</strong></font></p>';
	echo $text;
   	} else {
	$text = '<font color="red"><strong>'.__('Warning: ', 'bulletproof-security').'</strong></font>'.__('Maintenance Mode Is Activated. Your website is now displaying the Website Under Maintenance page to everyone except you. To switch out of Maintenance mode activate BulletProof Security Mode. You can log in and out of your Dashboard / WordPress website in Maintenance Mode as long as your current IP address does not change. If your current IP address changes you will have to FTP to your website and delete the .htaccess file in your website root folder (or download the .htaccess file and add your new IP address and upload it back to your root website folder) to be able to log back into your WordPress Dashboard. Your ISP provides your current Public IP address. If you reboot your computer or disconnect from the Internet there is a good chance that you will get a new Public IP address from your ISP.', 'bulletproof-security');
	echo $text;
	}
	}
}	

// default.htaccess and secure.htaccess fwrite content for all WP site types
$bps_get_domain_root = bpsGetDomainRoot();
$bps_get_wp_root_default = bps_wp_get_root_folder();
$bps_auto_write_default_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';

$bpsSuccessMessageDef = '<font color="green"><strong>'.__('Success! Your Default Mode Master htaccess file was created successfully!', 'bulletproof-security').'</strong></font><br><font color="red"><strong>'.__('CAUTION: Default Mode should only be activated for testing or troubleshooting purposes. Default Mode does not protect your website with any security protection.', 'bulletproof-security').'</strong></font><br><font color="black"><strong>'.__('To activate Default Mode select the Default Mode radio button and click Activate to put your website in Default Mode.', 'bulletproof-security').'</strong></font>';

$bpsFailMessageDef = '<font color="red"><strong>'.__('The file ', 'bulletproof-security') . "$bps_auto_write_default_file" . __(' is not writable or does not exist. ', 'bulletproof-security').'</strong></font><br><strong>'.__('Check that the file is named default.htaccess and that the file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click', 'bulletproof-security').' <a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here', 'bulletproof-security').'</a>'.__(' for more help info.', 'bulletproof-security').'</strong><br>';

$bpsBeginWP = "\nRewriteEngine On
RewriteBase $bps_get_wp_root_default
RewriteRule ^index\.php$ - [L]\n";

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
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_default_file" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_default_content_top.$bps_default_content_bottom) === FALSE) {
    	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_default_file" . '</strong></font>';
		echo $text;
        exit;
    }
    echo $bpsSuccessMessageDef;
    fclose($handle);
	} else {
    echo $bpsFailMessageDef;
	}
}

// Create Default htaccess file - MU Subdirectory
if (isset($_POST['bps-auto-write-default-MUSDir']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_default_MUSDir' );

	if (is_writable($bps_auto_write_default_file)) {
	if (!$handle = fopen($bps_auto_write_default_file, 'w+b')) {
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_default_file" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_default_content_top.$bpsBeginWP.$bpsMUSDirTop.$bpsMUSDirBottom.$bpsMUEndWP) === FALSE) {
    	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_default_file" . '</strong></font>';
		echo $text;
        exit;
    }
    echo $bpsSuccessMessageDef;
    fclose($handle);
	} else {
    echo $bpsFailMessageDef;
	}
}

// Create Default htaccess file - MU Subdomain
if (isset($_POST['bps-auto-write-default-MUSDom']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_default_MUSDom' );

	if (is_writable($bps_auto_write_default_file)) {
	if (!$handle = fopen($bps_auto_write_default_file, 'w+b')) {
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_default_file" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_default_content_top.$bpsBeginWP.$bpsMUSDomTop.$bpsMUSDomBottom.$bpsMUEndWP) === FALSE) {
    	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_default_file" . '</strong></font>';
		echo $text;
        exit;
    }
    echo $bpsSuccessMessageDef;
    fclose($handle);
	} else {
    echo $bpsFailMessageDef;
	}
}

// secure.htaccess fwrite content for all WP site types
$bps_get_wp_root_secure = bps_wp_get_root_folder();
$bps_auto_write_secure_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';

$bpsSuccessMessageSec = '<font color="green"><strong>'.__('Success! Your BulletProof Security Root Master htaccess file was created successfully!', 'bulletproof-security').'</strong></font><br><font color="black"><strong>'.__('You can now Activate BulletProof Mode for your Root folder. Select the BulletProof Mode radio button and click Activate to put your website in BulletProof Mode.', 'bulletproof-security').'</strong></font>';

$bpsFailMessageSec = '<font color="red"><strong>'.__('The file ', 'bulletproof-security')."$bps_auto_write_secure_file" . __(' is not writable or does not exist.', 'bulletproof-security').'</strong></font><br><strong>'.__('Check that the file is named secure.htaccess and that the file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click ', 'bulletproof-security').'<a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here', 'bulletproof-security').'</a>'.__(' for more help info.', 'bulletproof-security').'</strong><br>';

$bps_secure_content_top = "#   BULLETPROOF .47.8 >>>>>>> SECURE .HTACCESS     \n
# If you edit the  BULLETPROOF .47.8 >>>>>>> SECURE .HTACCESS text above
# you will see error messages on the BPS Security Status page
# BPS is reading the version number in the htaccess file to validate checks
# If you would like to change what is displayed above you
# will need to edit the BPS /includes/functions.php file to match your changes
# If you update your WordPress Permalinks the code between BEGIN WordPress and
# END WordPress is replaced by WP htaccess code.
# This removes all of the BPS security code and replaces it with just the default WP htaccess code
# To restore this file use BPS Restore or activate BulletProof Mode for your Root folder again.\n
# BEGIN WordPress
# IMPORTANT!!! DO NOT DELETE!!! - B E G I N Wordpress above or E N D WordPress - text in this file
# They are reference points for WP, BPS and other plugins to write to this htaccess file.
# IMPORTANT!!! DO NOT DELETE!!! - BPSQSE BPS QUERY STRING EXPLOITS - text
# BPS needs to find the - BPSQSE - text string in this file to validate that your security filters exist\n
# TURN OFF YOUR SERVER SIGNATURE
ServerSignature Off\n
# ADD A PHP HANDLER
# If you are using a PHP Handler add your web hosts PHP Handler below\n\n";

$bpsCCTop = '';
$phpiniHCode = '';
$options = get_option('bulletproof_security_options_customcode');
if ($options['bps_customcode_one'] != '') {
$bpsCCTop = 'CustomCodeOne';

// AutoMagic - CUSTOM CODE TOP
switch ($bpsCCTop) {
	case "CustomCodeOne":
        $phpiniHCode = "# CUSTOM CODE TOP - Your Custom .htaccess code will be created here with AutoMagic\n".htmlspecialchars_decode($options['bps_customcode_one'])."\n\n";
		break;
	default:
		$phpiniHCode = "# CUSTOM CODE TOP - Your Custom .htaccess code will be created here with AutoMagic\n\n";
	}
}

$bps_secure_content_top_two = "# DO NOT SHOW DIRECTORY LISTING
# If you are getting 500 Errors when activating BPS then comment out Options -Indexes 
# by adding a # sign in front of it. If there is a typo anywhere in this file you will also see 500 errors.
Options -Indexes\n
# DIRECTORY INDEX FORCE INDEX.PHP
# Use index.php as default directory index file
# index.html will be ignored will not load.
DirectoryIndex index.php index.html /index.php\n
# BPS ERROR LOGGING AND TRACKING
# BPS has premade 403 Forbidden, 400 Bad Request and 404 Not Found files that are used 
# to track and log 403, 400 and 404 errors that occur on your website. When a hacker attempts to
# hack your website the hackers IP address, Host name, Request Method, Referering link, the file name or
# requested resource, the user agent of the hacker and the query string used in the hack attempt are logged.
# All BPS log files are htaccess protected so that only you can view them. 
# The 400.php, 403.php and 404.php files are located in /wp-content/plugins/bulletproof-security/
# The 400 and 403 Error logging files are already set up and will automatically start logging errors
# after you install BPS and have activated BulletProof Mode for your Root folder.
# If you would like to log 404 errors you will need to copy the logging code in the BPS 404.php file
# to your Theme's 404.php template file. Simple instructions are included in the BPS 404.php file.
# You can open the BPS 404.php file using the WP Plugins Editor.
# NOTE: By default WordPress automatically looks in your Theme's folder for a 404.php template file.\n
ErrorDocument 400 $bps_get_wp_root_secure"."wp-content/plugins/bulletproof-security/400.php
ErrorDocument 403 $bps_get_wp_root_secure"."wp-content/plugins/bulletproof-security/403.php
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

$bps_secure_content_mid_top = "\n# REQUEST METHODS FILTERED
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
$CustomCodeTwo = '';
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
RewriteCond %{HTTP_REFERER} ^.*$bps_get_domain_root.*
RewriteRule . - [S=1]\n
# BPSQSE BPS QUERY STRING EXPLOITS
# The libwww-perl User Agent is forbidden - Many bad bots use libwww-perl modules, but some good bots use it too.
# Good sites such as W3C use it for their W3C-LinkChecker. 
# Add or remove user agents temporarily or permanently from the first User Agent filter below.
# If you want a list of bad bots / User Agents to block then scroll to the end of this file.
RewriteCond %{HTTP_USER_AGENT} (havij|libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]
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
RewriteCond %{QUERY_STRING} ^.*(\(|\)|<|>|%3c|%3e).* [NC,OR]
RewriteCond %{QUERY_STRING} ^.*(\\x00|\\x04|\\x08|\\x0d|\\x1b|\\x20|\\x3c|\\x3e|\\x7f).* [NC,OR]
RewriteCond %{QUERY_STRING} (NULL|OUTFILE|LOAD_FILE) [OR]
RewriteCond %{QUERY_STRING} (\./|\../|\.../)+(motd|etc|bin) [NC,OR]
RewriteCond %{QUERY_STRING} (localhost|loopback|127\.0\.0\.1) [NC,OR]
RewriteCond %{QUERY_STRING} (<|>|'|%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{QUERY_STRING} concat[^\(]*\( [NC,OR]
RewriteCond %{QUERY_STRING} union([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} \-[sdcr].*(allow_url_include|allow_url_fopen|safe_mode|disable_functions|auto_prepend_file) [NC,OR]
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
$CustomCodeThree = '';
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
# FORBID COMMENT SPAMMERS ACCESS TO YOUR wp-comments-post.php FILE
# This is a better approach to blocking Comment Spammers so that you do not 
# accidentally block good traffic to your website. You can add additional
# Comment Spammer IP addresses on a case by case basis below.
# Searchable Database of known Comment Spammers http://www.stopforumspam.com/\n
<FilesMatch ".'"'."^(wp-comments-post\.php)".'"'.">
Order Allow,Deny
Deny from 46.119.35.
Deny from 46.119.45.
Deny from 91.236.74.
Deny from 93.182.147.
Deny from 93.182.187.
Deny from 94.27.72.
Deny from 94.27.75.
Deny from 94.27.76.
Deny from 193.105.210.
Deny from 195.43.128.
Deny from 198.144.105.
Deny from 199.15.234.
Allow from all
</FilesMatch>\n
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
$bps_auto_write_maint_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/maintenance.htaccess';
$bps_maint_top = "#   BULLETPROOF .47.8 MAINTENANCE  .HTACCESS     \n\n";    
$bps_maint_content = "# BEGIN WordPress\n\n
RewriteEngine On
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
RewriteCond %{QUERY_STRING} ^.*(\(|\)|<|>).* [NC,OR]
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
        $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_maint_file" . '</strong></font>';
		echo $text;
         exit;
    }
    if (fwrite($handle, $bps_maint_top.$phpiniHCode.$bps_maint_content) === FALSE) {
		$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_maint_file" . '</strong></font>';
		echo $text;
        exit;
    }
    $text = '<font color="green"><strong>'.__('Success! Your Maintenance Mode htaccess file was created successfully! Select the Maintenance Mode radio button and click Activate to put your website in Maintenance Mode.', 'bulletproof-security').'</strong></font>';
	echo $text;
    fclose($handle);
	} else {
    $text = '<font color="red"><strong>'.__('The file ', 'bulletproof-security')."$bps_auto_write_maint_file".__(' is not writable or does not exist.', 'bulletproof-security').'</strong></font><br><strong>'.__('Check that the file is named maintenance.htaccess and that the file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click', 'bulletproof-security').'<a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here</a>'.__(' for more help info.', 'bulletproof-security').'</strong><br>';
	echo $text;
	}
}

// Create Secure htaccess master Root file - Single Site
if (isset($_POST['bps-auto-write-secure-root']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_secure_root' );

	if (is_writable($bps_auto_write_secure_file)) {
	if (!$handle = fopen($bps_auto_write_secure_file, 'w+b')) {
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_secure_file" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_secure_content_top.$phpiniHCode.$bps_secure_content_top_two.$bps_secure_content_wpadmin.$bpsBeginWP.$bps_secure_content_mid_top.$CustomCodeTwo.$bps_secure_content_mid_top2.$bps_secure_content_mid_bottom.$bps_secure_content_bottom.$CustomCodeThree.$bps_secure_content_bottom2) === FALSE) {
        $text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_secure_file" . '</strong></font>';
		echo $text;
        exit;
    }
    echo $bpsSuccessMessageSec;
    fclose($handle);
	} else {
    echo $bpsFailMessageSec;
	}
}

// Create Secure htaccess master Root file - MU Subdirectory
if (isset($_POST['bps-auto-write-secure-root-MUSDir']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_secure_root_MUSDir' );

	if (is_writable($bps_auto_write_secure_file)) {
	if (!$handle = fopen($bps_auto_write_secure_file, 'w+b')) {
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_secure_file" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_secure_content_top.$phpiniHCode.$bps_secure_content_top_two.$bpsBeginWP.$bpsMUSDirTop.$bps_secure_content_mid_top.$CustomCodeTwo.$bps_secure_content_mid_top2.$bpsMUSDirBottom.$bps_secure_content_bottom.$CustomCodeThree.$bps_secure_content_bottom2) === FALSE) {
        $text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_secure_file" . '</strong></font>';
		echo $text;
        exit;
    }
    echo $bpsSuccessMessageSec;
    fclose($handle);
	} else {
    echo $bpsFailMessageSec;
	}
}

// Create Secure htaccess master Root file - MU Subdomain
if (isset($_POST['bps-auto-write-secure-root-MUSDom']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_auto_write_secure_MUSDom' );

	if (is_writable($bps_auto_write_secure_file)) {
	if (!$handle = fopen($bps_auto_write_secure_file, 'w+b')) {
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_secure_file" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_secure_content_top.$phpiniHCode.$bps_secure_content_top_two.$bpsBeginWP.$bpsMUSDomTop.$bps_secure_content_mid_top.$CustomCodeTwo.$bps_secure_content_mid_top2.$bpsMUSDomBottom.$bps_secure_content_bottom.$CustomCodeThree.$bps_secure_content_bottom2) === FALSE) {
        $text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_secure_file" . '</strong></font>';
		echo $text;
        exit;
    }
    echo $bpsSuccessMessageSec;
    fclose($handle);
	} else {
    echo $bpsFailMessageSec;
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

	$bps_auto_write_maint_file_form = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
$bps_maint_content_form = "<?php".'
$bps_retry_after'." = '$bps_retry_after_write';\n"
.'$bps_site_title'." = '$bps_site_title_write';\n"
.'$bps_message1'." = '$bps_message1_write';\n"
.'$bps_message2'." = '$bps_message2_write';\n"
.'$bps_body_background_image'." = '$bps_body_background_image_write';
?>";
	if (is_writable($bps_auto_write_maint_file_form)) {
	if (!$handle = fopen($bps_auto_write_maint_file_form, 'w+b')) {
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$bps_auto_write_maint_file_form" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_maint_content_form) === FALSE) {
        $text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$bps_auto_write_maint_file_form" . '</strong></font>';
		echo $text;
        exit;
    }
    $text = '<font color="green"><strong>'.__('Success! Your Maintenance Mode Form has been created successfully! Click the Preview button to preview your Website Under Maintenance page.', 'bulletproof-security').'</strong></font>';
	echo $text;
    fclose($handle);
	} else {
    $text = '<font color="red"><strong>'.__('The file ', 'bulletproof-security')."$bps_auto_write_maint_file_form".__(' is not writable or does not exist.', 'bulletproof-security').'</strong></font><br><strong>'.__('Check that the bps-maintenance-values.php file exists in the /bulletproof-security/admin/htaccess master folder. If this is not the problem click ', 'bulletproof-security').'<a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here', 'bulletproof-security').'</a>'.__(' for more help info.', 'bulletproof-security').'</strong><br>';
	echo $text;
	}
}

// Simple Secure Old School PHP file upload
if (isset($_POST['submit-bps-upload']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_upload' );
	
	$tmp_file = $_FILES['bps_file_upload']['tmp_name'];
	$folder_path = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/';
	$bps_uploaded_file =  str_replace('//','/',$folder_path) . $_FILES['bps_file_upload']['name'];
	if (!empty($_FILES)) {
	move_uploaded_file($tmp_file,$bps_uploaded_file);
		$text = '<font color="green"><strong>'.__('File Uploaded Successfully To: ', 'bulletproof-security').'</strong></font><br>'."$bps_uploaded_file";
		echo $text;
	} else {
		$text = '<font color="red"><strong>'.__('File upload error. File was not successfully uploaded.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}
}

// Enable File Downloading for Master Files - writes a new denyall htaccess file with the current IP address
if (isset($_POST['bps-enable-download']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_enable_download' );
	
	$bps_get_IP = $_SERVER['REMOTE_ADDR'];
	$denyall_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
	$bps_denyall_content = "order deny,allow\ndeny from all\nallow from $bps_get_IP";
	
	if (is_writable($denyall_htaccess_file)) {
	if (!$handle = fopen($denyall_htaccess_file, 'w+b')) {
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$denyall_htaccess_file" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_denyall_content) === FALSE) {
       $text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$denyall_htaccess_file" . '</strong></font>';
	   echo $text;
       exit;
    }
    $text = '<font color="green"><strong>'.__('Success! File open, preview and downloading for your BPS Master Files is enabled for your IP address only === ', 'bulletproof-security')."$bps_get_IP." .'</strong></font>';
	echo $text;
    fclose($handle);
	} else {
    $text = '<font color="red"><strong>'.__('The file ', 'bulletproof-security')."$denyall_htaccess_file".__(' is not writable or does not exist yet. ', 'bulletproof-security').'</strong></font><br><strong>'.__('Check the BPS Status page to see if Deny All protection has been activated. Activate Deny All htaccess Folder Protection For The BPS Master htaccess Folder on the BPS Security Modes page. If this is not the problem click ', 'bulletproof-security').'<a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here', 'bulletproof-security').'</a>'.__(' for more help info.', 'bulletproof-security').'</strong><br>';
	echo $text;
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
         $text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$denyall_htaccess_file_backup" . '</strong></font>';
		 echo $text;
         exit;
    }
    if (fwrite($handle, $bps_denyall_content_backup) === FALSE) {
        $text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$denyall_htaccess_file_backup" . '</strong></font>';
		echo $text;
        exit;
    }
    $text = '<font color="green"><strong>'.__('Success! File open, preview and downloading for your Backed Up htaccess Files is enabled for your IP address only ===', 'bulletproof-security')."$bps_get_IP2." .'</strong></font>';
	echo $text;
    fclose($handle);
	} else {
    $text = '<font color="red"><strong>'.__('The file ', 'bulletproof-security')."$denyall_htaccess_file_backup".__(' is not writable or does not exist yet. ', 'bulletproof-security').'</strong></font><br><strong>'.__('Check the BPS Status page to see if Deny All protection has been activated. Activate Deny All htaccess Folder Protection For The BPS Backup Folder on the BPS Security Modes page. If this is not the problem click ', 'bulletproof-security').'<a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here', 'bulletproof-security').'</a>'.__(' for more help info.', 'bulletproof-security').'</strong><br>';
	echo $text;
	}
}

// Get DNS Name Server from [target] - only using $bpsTargetNS and $bpsTarget variables for BPS Free
// additional BPS Pro variables not used in BPS Free
$bpsHostName = esc_html($_SERVER['SERVER_NAME']);
$bpsTargetNS = '';
$bpsTarget = '';
$bpsNSHostSubject = '';
	
	$bpsGetDNS = @dns_get_record($bpsHostName, DNS_NS);
	if (!isset($bpsGetDNS[0]['target'])) {
	echo '';
	} else {
	$bpsTargetNS = @$bpsGetDNS[0]['target'];
	if ($bpsTargetNS != '') {
	preg_match('/[^.]+\.[^.]+$/', $bpsTargetNS, $bpsTmatches);
	$bpsNSHostSubject = $bpsTmatches[0];
	// echo 'DNS_NS -- '.$bpsTargetNS.' -- preg match -- '.$bpsNSHostSubject.'<br>'; // for testing
	} else {
	echo '';
	}
	}
	
	if ($bpsTargetNS == '') {
	@dns_get_record($bpsHostName, DNS_ALL, $authns, $addtl);
	if (!isset($authns[0]['target'])) {
	echo '';
	} else {
	$bpsTarget = $authns[0]['target'];
	if ($bpsTarget != '') {
	preg_match('/[^.]+\.[^.]+$/', $bpsTarget, $bpsTmatches);
	$bpsNSHostSubject = $bpsTmatches[0];
	// echo 'DNS_ALL -- '.$bpsTarget.' -- preg match -- '.$bpsNSHostSubject.'<br>'; // for testing
	}
	}
	}	
	
	if ($bpsTarget && $bpsTargetNS == '') {
	@dns_get_record($bpsHostName, DNS_ANY, $authns, $addtl);
	if (!isset($authns[0]['target'])) {
	echo '';
	} else {
	$bpsTarget = $authns[0]['target'];
	preg_match('/[^.]+\.[^.]+$/', $bpsTarget, $bpsTmatches);
	$bpsNSHostSubject = $bpsTmatches[0];
	// echo 'DNS_ANY -- '.$bpsTarget.' -- preg match -- '.$bpsNSHostSubject.'<br>'; // for testing	
	}
	}

// General all purpose "Settings Saved." message for forms
if (current_user_can('manage_options')) {
if (@$_GET['settings-updated'] == true) {
	$text = '<p><font color="green"><strong>'.__('Settings Saved', 'bulletproof-security').'</strong></font></p>';
	echo $text;
	}
}
$bpsSpacePop = '-------------------------------------------------------------';
?>
</div>

<!-- jQuery UI Tabs Menu -->
<div id="bps-container">
	<div id="bps-tabs" class="bps-menu">
    <div id="bpsHead" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-security-shield.png'); ?>" style="float:left; padding:0px 8px 0px 0px; margin:-72px 0px 0px 0px;" /></div>
		<ul>
			<li><a href="#bps-tabs-1"><?php _e('Security Modes', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-2"><?php _e('Security Status', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-3"><?php _e('Security Log', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-4"><?php _e('System Info', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-5"><?php _e('Backup &amp; Restore', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-6"><?php _e('Edit/Upload/Download', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-7"><?php _e('Custom Code', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-8"><?php _e('Maintenance Mode', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-9"><?php _e('Help &amp; FAQ', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-10"><?php _e('Whats New', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-11"><?php _e('My Notes', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-12"><?php _e('BPS Pro Features', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-13"><?php _e('Website Scanner', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-14"><?php _e('Website SEO', 'bulletproof-security'); ?></a></li>
		</ul>
            
<div id="bps-tabs-1" class="bps-tab-page">
<h2><?php _e('BulletProof Security Modes', 'bulletproof-security'); ?></h2>

<div id="bpsMonitoringAlerting" style="border-top:1px solid #999999;">

<h3><?php _e('AutoMagic - Create Your htaccess Master Files', 'bulletproof-security'); ?>  <button id="bps-open-modal1" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content1" title="<?php _e('AutoMagic', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('Backup your existing htaccess files if you have any first by clicking on the Backup & Restore menu tab - click on the Backup htaccess files radio button to select it and click on the Backup Files button to back up your existing htaccess files.','bulletproof-security').'</strong><br><br><strong>'.__('AutoMagic - BPS Creates Customized .htaccess Master Files For Your Website Automatically','bulletproof-security').'</strong><br>'.__('BPS detects what type of WordPress installation you have and will tell you which AutoMagic buttons to use for your website.','bulletproof-security').'<br><br><strong>'.__('BPS Pro AutoMagic: ','bulletproof-security').'</strong>'.__('BPS Pro AutoMagic detects your Web Host and if your Web Host requires custom php.ini handler code, BPS Pro will automatically create this code in your Root .htaccess file - ','bulletproof-security').'<strong>'.__('IF YOUR WEB HOST HAS BEEN ADDED TO THE BPS WEB HOST LIST.','bulletproof-security').'</strong>'.__(' To see the complete BPS Pro Web Host list go to the Help &amp; FAQ page and click on the ','bulletproof-security').'<strong>'.__('AutoMagic php.ini Handler Web Hosts List ','bulletproof-security').'</strong>'.__('link.','bulletproof-security').'<br><br> -- '.__('Click the ','bulletproof-security').'<strong>'.__('Create default.htaccess File ','bulletproof-security').'</strong>'.__('button.','bulletproof-security').'<br> -- '.__('Click the ','bulletproof-security').'<strong>'.__('Create secure.htaccess File ','bulletproof-security').'</strong>'.__('button.','bulletproof-security').'<br> -- '.__('If you would like to view, edit or add any additional .htaccess code to your new secure.htaccess Master file. Click on the Edit/Upload/Download menu tab, click on the secure.htaccess menu tab and make your changes before you Activate BulletProof Mode for your Root folder.','bulletproof-security').'<br> -- '.__('Activate BulletProof Mode for your Root folder.','bulletproof-security').'<br> -- '.__('Activate BulletProof Mode for your wp-admin folder.','bulletproof-security').'<br> -- '.__('Activate BulletProof Mode for the BPS Master htaccess folder.','bulletproof-security').'<br> -- '.__('Activate BulletProof Mode for the BPS Backup folder.','bulletproof-security').'<br><br><strong>'.__('WordPress Network (Multisite) Sites Info','bulletproof-security').'</strong><br>'.__('BPS will automatically detect whether you have a subdomain or subdirectory Network (Multisite) installation and tell you which AutoMagic buttons to use. DO NOT Network Activate BPS. BPS will not work correctly if you choose Network Activate. BPS only needs to be activated and set up on your Primary site to automatically add security protection to all of your sub sites. Network / MU sub sites are virtual and do not really exist in separate website folders. BPS menus will only be displayed to Super Admins. ','bulletproof-security').'<br><br><strong>'.__('Explanation Of The Steps Above and Additional Info:','bulletproof-security').'</strong><br>'.__('If you see error messages when performing a first time backup do not worry about it. BPS will backup whatever files should be or are available to backup for your website.','bulletproof-security').'<br><br>'.__('Clicking the ','bulletproof-security').'<strong>'.__('Create default.htaccess File ','bulletproof-security').'</strong>'.__('button and the ','bulletproof-security').'<strong>'.__('Create secure.htaccess File ','bulletproof-security').'</strong>'.__('button will create these two new customized master .htaccess files for your website. The correct RewriteBase and RewriteRule for your website will be automatically added to these files. The default.htaccess file is the master .htaccess file that is copied to your root folder when you Activate Default Mode. Default Mode should only be activated for testing and troubleshooting purposes - it does not provide any website security. The secure.htaccess file is the master .htaccess file that is copied to your Root folder when you Activate BulletProof Mode for your Root folder.','bulletproof-security').'<br><br><strong>'.__('When you Activate BulletProof Mode for your Root folder it will overwrite the existing Root .htaccess file.','bulletproof-security').'</strong>'.__(' If you have added any custom .htaccess code in your Root .htaccess file you should save that custom code to My Notes. My Notes allows you to permanently save custom .htaccess code or any other notes.','bulletproof-security').'<br><br>'.__('The plugin conflict fixes in the secure.htaccess master file will also have your correct WordPress installation folder name automatically added to it. The .htaccess file for your wp-admin folder does not require any editing nor do the Deny All htaccess files.','bulletproof-security').'<br><br><strong>'.__('Editing .htaccess Files - BPS Built-in File Editor','bulletproof-security').'</strong><br>'.__('BPS has a built-in .htaccess File Editor if you want to edit your .htaccess files manually. Go to the Edit/Upload/Download menu tab.','bulletproof-security'); echo $text; ?></p>
</div>

<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
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
<input type="submit" name="bps-auto-write-default" value="<?php _e('Create default.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized default.htaccess Master file for your website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('NOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new default.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</p>
</form>

<form name="bps-auto-write-secure-root" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_root'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write" />
<p class="submit">
<input type="submit" name="bps-auto-write-secure-root" value="<?php _e('Create secure.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized secure.htaccess Master file for your website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new secure.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</p>
</form>
</td>
    <td>
<form name="bps-auto-write-default-MUSDir" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_default_MUSDir'); ?>
<input type="hidden" name="filename" value="bps-auto-write-default_write-MUSDir" />
<p class="submit">
<input type="submit" name="bps-auto-write-default-MUSDir" value="<?php _e('Create default.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized default.htaccess Master file for your Network / Multisite subdirectory website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('NOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new default.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</p>
</form>

<form name="bps-auto-write-secure-root-MUSDir" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_root_MUSDir'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write_MUSDir" />
<p class="submit">
<input type="submit" name="bps-auto-write-secure-root-MUSDir" value="<?php _e('Create secure.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized secure.htaccess Master file for your Network / Multisite subdirectory website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new secure.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</p>
</form>
</td>
    <td>
<form name="bps-auto-write-default-MUSDom" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_default_MUSDom'); ?>
<input type="hidden" name="filename" value="bps-auto-write-default_write_MUSDom" />
<p class="submit">
<input type="submit" name="bps-auto-write-default-MUSDom" value="<?php _e('Create default.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized default.htaccess Master file for your Network / Multisite subdomain website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('NOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new default.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</p>
</form>

<form name="bps-auto-write-secure-root-MUSDom" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_secure_MUSDom'); ?>
<input type="hidden" name="filename" value="bps-auto-write-secure_write_MUSDom" />
<p class="submit">
<input type="submit" name="bps-auto-write-secure-root-MUSDom" value="<?php _e('Create secure.htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Clicking OK will create a new customized secure.htaccess Master file for your Network / Multisite subdomain website.', 'bulletproof-security').'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.__('Click OK to Create your new secure.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</p>
</form>
</td>
  </tr>
</table>
<?php } ?>
</div>
<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    <h2><?php _e('Activate Security Modes', 'bulletproof-security'); ?></h2>
    <h3><?php _e('Activate Website Root Folder .htaccess Security Mode', 'bulletproof-security'); ?>  <button id="bps-open-modal2" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
    <div id="bps-modal-content2" title="<?php _e('Activate Root Folder BulletProof Mode', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('If you activate BulletProof Mode for your Root folder you must also activate BulletProof Mode for your wp-admin folder.','bulletproof-security').'</strong><br><br>'.__('Perform a backup first before activating any BulletProof Security modes (backs up both currently active root and wp-admin htaccess files at the same time).','bulletproof-security').'<br><br><strong>'.__('What Is Going On Here?','bulletproof-security').'</strong><br>'.__(' Clicking the AutoMagic buttons creates your customized Master .htaccess files for your website. Activating BulletProof Modes copies and renames those Master .htaccess files from /plugins/bulletproof-security/admin/htaccess/ to your website root folder. Default Mode does not have any security protection - it is just a standard generic WordPress .htaccess file that you should only Activate for testing or troubleshooting purposes.','bulletproof-security').'<br><br><strong>'.__('Help and FAQ links are available on the BPS Help and FAQ page','bulletproof-security').'</strong><br><br><strong>'.__('Additional Info','bulletproof-security').'</strong><br>'.__('Before upgrading or any time you add some additional custom code to your .htaccess files you can save that custom .htaccess code to the My Notes page. This code or notes are saved permanently to your WP database until you edit or delete it.','bulletproof-security').'<br><br>'.__('When you upgrade BPS your currently active root and wp-admin .htaccess files are not changed or overwritten. BPS master .htaccess files ARE replaced when you upgrade BPS so if you have made changes to your BPS master files that you want to keep make sure they are backed up using Backup and Restore first before upgrading. You can also download copies of the BPS master files to your computer using the BPS File Downloader if you want. When you backup your BPS files this is an online backup so the files will be available to you to restore from if you run into any problems at any point.','bulletproof-security').'<br><br>'.__('You should always be using the newest BPS master .htaccess files for the latest security protection updates and plugin conflict fixes. Before activating new BPS master files you can use the BPS File Editor to copy and paste any existing custom .htaccess code that you want to keep from your current active .htaccess files to the new BPS master .htaccess files and save your changes before activating your new BPS master .htaccess files. You can copy from one .htaccess file editing window to any other window and then save your changes. Or you can copy any new htaccess code from the new BPS master files to your existing currently active htaccess files. If you do this be sure to edit the BPS version number at the top of your currently active htaccess files or you will see BPS error messages. And the My Notes page allows you to save any code you want to save permanently for later use or reminders.','bulletproof-security').'<br><br><strong>'.__('Troubleshooting Error Messages','bulletproof-security').'</strong><br>'.__('Check the Security Status menu tab to see potential problems and explanations of what might be causing the error message. Check the Edit/Upload/Download page to view all of your .htaccess files. Click on Your Current Root htaccess File menu tab to view your actual root .htaccess file. At the top of this .htaccess file you will see the BPS version and which .htaccess file is activated. Check that the BPS QUERY STRING EXPLOITS code does actually exist in your root .htaccess file. When you update your WordPress Permalinks the BPSQSE BPS QUERY STRING EXPLOITS code is overwritten with the WordPress standard default .htaccess code. You will either need to use Backup and Restore to restore you backed up .htaccess files or activate BulletProof Mode again for your Root Folder. To check your wp-admin .htaccess file click on the Your Current wp-admin htaccess File menu tab.','bulletproof-security').'<br><br><strong>'.__('Testing or Removing / Uninstalling BPS','bulletproof-security').'</strong><br>'.__('If you are testing BPS to determine if there is a plugin conflict or other conflict then Activate Default Mode and select the Delete wp-admin htaccess File radio button and click the Activate button or you can now just go to the WordPress Permalinks page and update / resave your permalinks. This overwrites all BPS security code with the standard default WP .htaccess code. This puts your site in a standard WordPress state with a default or generic Root .htaccess file and no .htaccess file in your wp-admin folder if you selected Delete wp-admin htaccess file. After testing or troubleshooting is completed reactivate BulletProof Modes for both the Root and wp-admin folders. If you are removing / uninstalling BPS then follow the same steps and then select Deactivate from the Wordpress Plugins page and then click Delete to uninstall the BPS plugin.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="BulletProof-Root" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_root_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection12" type="radio" value="bpsecureroot" class="tog" <?php checked('', $bpsecureroot); ?> /> <?php _e('BulletProof Mode', 'bulletproof-security'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/.htaccess<br /><?php $text = '<font color="green">'.__('Copies the file secure.htaccess to your root folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
   <tr>   
   <th><label><input name="selection12" type="radio" value="bpdefaultroot" class="tog" <?php checked('', $bpdefaultroot); ?> /><?php $text = '<font color="red">'.__('Default Mode', 'bulletproof-security').'<br>'.__('WP Default htaccess File', 'bulletproof-security').'</font>'; echo $text; ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/.htaccess<br /><?php $text = '<font color="red">'.__(' CAUTION: ', 'bulletproof-security').'</font>'.__('Your site will not be protected if you activate Default Mode. ONLY activate Default Mode for Testing and Troubleshooting.', 'bulletproof-security'); echo $text; ?></td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit12" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Did you create your Master .htaccess files using the AutoMagic buttons?', 'bulletproof-security').'\n\n'.__('Did you backup your existing .htaccess files?', 'bulletproof-security').'\n\n'.__('Do you have any custom .htaccess code in your Root .htaccess file that you want to save before Activating BulletProof Mode?', 'bulletproof-security').'\n\n'.__('Clicking OK will overwrite your existing Root .htaccess file.', 'bulletproof-security').'\n\n'.__('Click OK to Activate BulletProof Mode for your Root folder or click Cancel.', 'bulletproof-security'); echo $text; ?>')" /></p></form>

<h3><?php _e('Activate Website wp-admin Folder .htaccess Security Mode', 'bulletproof-security'); ?>  <button id="bps-open-modal3" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content3" title="<?php _e('Activate wp-admin BulletProof Mode', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('If you activate BulletProof Mode for your wp-admin folder you must also activate BulletProof Mode for your Root folder.','bulletproof-security').'</strong><br><br>'.__('Activating BulletProof Mode copies, renames and moves the master .htaccess file wpadmin-secure.htaccess from /plugins/bulletproof-security/admin/htaccess/ to your /wp-admin folder. If you customize or modify the master .htaccess files then be sure to make an online backup and also download backups of these master .htaccess files to your computer using the BPS File Downloader.','bulletproof-security').'<br><br>'.__('For more information click this Read Me button link to view the ','bulletproof-security').'<strong>'.__('BulletProof Security Guide.','bulletproof-security').'</strong><br><br><strong>'.__('Testing or Removing / Uninstalling BPS','bulletproof-security').'</strong><br>'.__('If you are testing BPS to determine if there is a plugin conflict or other conflict then Activate Default Mode and select the Delete wp-admin htaccess File radio button and click the Activate button. This puts your site in a standard WordPress state with a default or generic Root .htaccess file and no .htaccess file in your wp-admin folder. After testing or troubleshooting is completed reactivate BulletProof Modes for both the Root and wp-admin folders. If you are removing / uninstalling BPS then follow the same steps and then select Deactivate from the Wordpress Plugins page and then click Delete to uninstall the BPS plugin.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="BulletProof-WPadmin" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_wpadmin_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection13" type="radio" value="bpsecurewpadmin" class="tog" <?php checked('', $bpsecurewpadmin); ?> /> <?php _e('BulletProof Mode', 'bulletproof-security'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-admin/.htaccess<br /><?php $text = '<font color="green">'.__(' Copies the file wpadmin-secure.htaccess to your /wp-admin folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
   <tr>
	<th><label><input name="selection13" type="radio" value="Removebpsecurewpadmin" class="tog" <?php checked('', $Removebpsecurewpadmin); ?> /> <?php $text = '<font color="red">'.__('Delete wp-admin', 'bulletproof-security').'<br>'.__('htaccess File', 'bulletproof-security').'</font>'; echo $text; ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-admin/.htaccess<br /><?php $text = '<font color="red">'.__(' CAUTION: ', 'bulletproof-security').'</font>'.__('Deletes the .htaccess file in your /wp-admin folder. ONLY delete For testing or BPS removal.', 'bulletproof-security'); echo $text; ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit13" class="bps-blue-button" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
</p></form>

<h3><?php _e('Activate Deny All htaccess Folder Protection For The BPS Master htaccess Folder', 'bulletproof-security'); ?>  <button id="bps-open-modal4" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content4" title="<?php _e('BPS Master htaccess Folder', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('Your BPS Master htaccess folder should already be automatically protected by BPS, but if it is not then activate BulletProof Mode for your BPS Master htaccess folder.','bulletproof-security').'</strong><br><br>'.__('Activating BulletProof Mode for Deny All htaccess Folder Protection copies and renames the deny-all.htaccess file located in the /plugins/bulletproof-security/admin/htaccess/ folder and renames it to just .htaccess. The Deny All htaccess file blocks everyone, except for you, from accessing and viewing the BPS Master htaccess files.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="BulletProof-deny-all-htaccess" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_master'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection8" type="radio" value="bps_rename_htaccess_files" class="tog" <?php checked('', $bps_rename_htaccess_files); ?> /> <?php _e('BulletProof Mode', 'bulletproof-security'); ?></label></th>
	<td class="url-path"><?php echo plugins_url('/bulletproof-security/admin/htaccess/'); ?><br /><?php $text = '<font color="green">'.__(' Copies the file deny-all.htaccess to the BPS Master htaccess folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit8" class="bps-blue-button" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
</p></form>

<h3><?php _e('Activate Deny All htaccess Folder Protection For The BPS Backup Folder', 'bulletproof-security'); ?>  <button id="bps-open-modal5" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content5" title="<?php _e('BPS Backup Folder', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('Your BPS Backup folder should already be automatically protected by BPS, but if it is not then activate BulletProof Mode for your BPS Backup folder.','bulletproof-security').'</strong><br><br>'.__('Activating BulletProof Mode for Deny All BPS Backup Folder Protection copies and renames the deny-all.htaccess file located in the /bulletproof-security/admin/htaccess/ folder to the BPS Backup folder /wp-content/bps-backup and renames it to just .htaccess. The Deny All htaccess file blocks everyone, except for you, from accessing and viewing your backed up htaccess files.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="BulletProof-deny-all-backup" action="admin.php?page=bulletproof-security/admin/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_bpsbackup'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection14" type="radio" value="bps_rename_htaccess_files_backup" class="tog" <?php checked('', $bps_rename_htaccess_files_backup); ?> /> <?php _e('BulletProof Mode', 'bulletproof-security'); ?></label></th>
	<td class="url-path"><?php echo get_site_url(); ?>/wp-content/bps-backup/<br /><?php $text = '<font color="green">'.__(' Copies the file deny-all.htaccess to the BPS Backup folder and renames the file name to just .htaccess', 'bulletproof-security').'</font>'; echo $text; ?></td>
   </tr>
</table>
<p class="submit"><input type="submit" name="submit14" class="bps-blue-button" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
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
<h2><?php _e('BulletProof Security Status', 'bulletproof-security'); ?></h2>


<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-status_table">
  <tr>
    <td width="49%" class="bps-table_title_SS"><?php _e('Activated BulletProof Security .htaccess Files', 'bulletproof-security'); ?>  <button id="bps-open-modal6" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    <div id="bps-modal-content6" title="<?php _e('Activated .htaccess Files', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('If you activate BulletProof Mode for your Root folder you must also activate BulletProof Mode for your wp-admin folder.','bulletproof-security').'</strong><br><br>'.__('Perform a backup first before activating any BulletProof Security modes (backs up both currently active root and wp-admin htaccess files at the same time).','bulletproof-security').'<br><br><strong>'.__('Help and FAQ links are available on the BPS Help and FAQ page','bulletproof-security').'</strong><br><br><strong>'.__('Additional Info','bulletproof-security').'</strong><br>'.__('Before upgrading or any time you add some additional custom code to your .htaccess files you can save that custom .htaccess code to the My Notes page. This code or notes are saved permanently to your WP database until you edit or delete it.','bulletproof-security').'<br><br>'.__('When you upgrade BPS your currently active root and wp-admin .htaccess files are not changed or overwritten. BPS master .htaccess files ARE replaced when you upgrade BPS so if you have made changes to your BPS master files that you want to keep make sure they are backed up using Backup and Restore first before upgrading. You can also download copies of the BPS master files to your computer using the BPS File Downloader if you want. When you backup your BPS files this is an online backup so the files will be available to you to restore from if you run into any problems at any point.','bulletproof-security').'<br><br>'.__('You should always be using the newest BPS master .htaccess files for the latest security protection updates and plugin conflict fixes. Before activating new BPS master files you can use the BPS File Editor to copy and paste any existing custom .htaccess code that you want to keep from your current active .htaccess files to the new BPS master .htaccess files and save your changes before activating your new BPS master .htaccess files. You can copy from one .htaccess file editing window to any other window and then save your changes. Or you can copy any new htaccess code from the new BPS master files to your existing currently active htaccess files. If you do this be sure to edit the BPS version number at the top of your currently active htaccess files or you will see BPS error messages. And the My Notes page allows you to save any code you want to save permanently for later use or reminders.','bulletproof-security').'<br><br>'.__('The Text Strings you see listed in the Activated BulletProof Security Status window if you have an active BulletProof .htaccess file (or an existing .htaccess file) is reading and displaying the actual contents of any existing .htaccess files here. ','bulletproof-security').'<strong>'.__('This is not just a displayed message - this is the actual first 46 string characters (text) of the contents of your .htaccess files.','bulletproof-security').'</strong>'.__('The BPSQSE BPS QUERY STRING EXPLOITS code check is done by searching the root .htaccess file to verify that the string/text/word BPSQSE is in the file.','bulletproof-security').'<br><br><strong>'.__('Troubleshooting Error Messages','bulletproof-security').'</strong><br>'.__('Check the Edit/Upload/Download page to view all of your .htaccess files. Click on Your Current Root htaccess File menu tab to view your actual root .htaccess file. At the top of this .htaccess file you will see the BPS version and which .htaccess file is activated. Check that the BPS QUERY STRING EXPLOITS code does actually exist in your root .htaccess file. When you update your WordPress Permalinks the BPSQSE BPS QUERY STRING EXPLOITS code is overwritten with the WordPress standard default .htaccess code. You will either need to use Backup and Restore to restore you backed up .htaccess files or activate BulletProof Mode again for your Root Folder. To check your wp-admin .htaccess file click on the Your Current wp-admin htaccess File menu tab.','bulletproof-security').'<br><br><strong>'.__('Miscellaneous Info','bulletproof-security').'</strong><br>'.__('To change or modify the Text String that you see displayed here you would use the BPS built in Text Editor to change the actual text content of the BulletProof Security master .htaccess files. If the change the BULLETPROOF SECURITY title shown here then you must also change the coding contained in the /wp-content/plugins/bulletproof-security/includes/functions.php file to match your changes or you will get some error messages. The rest of the text content in the .htaccess files can be modified just like a normal post. Just this top line ot text in the .htaccess files contains version information that BPS checks to do verifications and other file checking. For detailed instructions on modifying what text is displayed here click this Read Me button link.','bulletproof-security');  echo $text; ?></p>
</div>
</td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title"><?php _e('Additional Website Security Measures', 'bulletproof-security'); ?></td>
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
<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-perms_table">
  <tr>
    <td class="bps-table_title_SS"><?php _e('File and Folder Permissions - CGI or DSO', 'bulletproof-security'); ?>  <button id="bps-open-modal7" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    <div id="bps-modal-content7" title="<?php _e('File and Folder Permissions', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('CGI And DSO File And Folder Permission Recommendations','bulletproof-security').'</strong><br>'.__('If your Server API (SAPI) is CGI you will see a table displayed with recommendations for file and folder permissions for CGI. If your SAPI is DSO / Apache mod_php you will see a table listing file and folder permission recommendations for DSO.', 'bulletproof-security').'<br><br>'.__('If your Host is using CGI, but they do not allow you to set your folder permissions more restrictive to 705 and file permissions more restrictive to 604 then most likely when you change your folder and file permissions they will automatically be changed back to 755 and 644 by your Host or you may see a 403 or 500 error and will need to change the folder permissions back to what they were before. CGI 705 folder permissions have been thoroughly tested with WordPress and no problems have been discovered with WP or with WP Plugins on several different Web Hosts, but all web hosts have different things that they specifically allow or do not allow.', 'bulletproof-security').'<br><br>'.__('You CANNOT typically change your Root folder permissions to 705. You may be able to change the Root folder to 750, but typically 755 is fine for your Root folder. Changing your folder permissions to 705 helps in protecting against Mass Host Code Injections. CGI 604 file permissions have been thoroughly tested with WordPress and no problems have been discovered with WP or with WP Plugins. Changing your file permissions to 604 helps in protecting your files from Mass Host Code Injections. CGI Mission Critical files should be set to 400 and 404 respectively.','bulletproof-security').'<br><br><strong>'.__('If you have BPS Pro installed then use F-Lock to Lock or Unlock your Mission Critical files. BPS Pro S-Monitor will automatically display warning messages if your files are unlocked.','bulletproof-security').'</strong><br><br><strong>'.__('The wp-content/bps-backup/ folder permission recommendation is 755 for CGI or DSO for compatibility reasons. The /bps-backup folder has a deny all htaccess file in it so that it cannot be accessed by anyone other than you so the folder permissions for this folder are irrelevant.','bulletproof-security').'</strong><br><br>'.__('Your current file and folder permissions are shown below with suggested / recommended file and folder permissions. ','bulletproof-security').'<strong>'.__('Not all web hosts will allow you to set your folder permissions to these Recommended folder permissions.', 'bulletproof-security').'</strong> '.__('If you see 500 errors after changing your folder permissions than change them back to what they were.','bulletproof-security').'<br><br>'.__('I recommend using FileZilla to change your file and folder permissions. FileZilla is a free FTP software that makes changing your file and folder permissions very simple and easy as well as many other very nice FTP features. With FileZilla you can right mouse click on your files or folders and set the permissions with a Numeric value like 755, 644, etc. Takes the confusion out of which attributes to check or uncheck.','bulletproof-security'); echo $text; ?></p>
</div>
</td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title_SS"><?php _e('General BulletProof Security File Checks', 'bulletproof-security'); ?>  <button id="bps-open-modal8" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    <div id="bps-modal-content8" title="<?php _e('General File Checks', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br>'.__('This is a quick visual check to verify that you have active .htaccess files in your root and /wp-admin folders and that all the required BPS files are in your BulletProof Security plugin folder. The BulletProof Security .htaccess master files (default.htaccess, secure.htaccess, wpadmin-secure.htaccess, maintenance.htaccess and bp-maintenance.php) are located in this folder /wp-content/plugins/bulletproof-security/admin/htaccess/','bulletproof-security').'<br><br>'.__('For new installations and upgrades of BulletProof Security you will see red warning messages. This is completely normal. These warnings are there to remind you to perform backups if they have not been performed yet. Also you may see warning messages if files do not exist yet.','bulletproof-security').'<br><br>'.__('You can also download backups of any existing .htaccess files using the BPS File Downloader.','bulletproof-security'); echo $text; ?></p>
</div>
</td>
  </tr>
  <tr>
  	<td height="100%" class="bps-table_cell_perms_blank">
	<?php 
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
	echo '<div style=\'padding:5px 0px 5px 5px;\'><strong>'; _e('CGI File and Folder Permissions / Recommendations', 'bulletproof-security'); echo '</strong></div>';
	echo '<table style="width:100%;background-color:#A9F5A0;border-bottom:1px solid black;border-top:1px solid black;">';
	echo '<tr>';
	echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Name', 'bulletproof-security'); echo '<br>'; _e('Folder Name', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Path', 'bulletproof-security'); echo '<br>'; _e('Folder Path', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Recommended', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Current', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
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
	echo '<div style=\'padding-bottom:15px;\'></div>';
	
	} else {
	
	echo '<div style=\'padding:5px 0px 5px 5px;\'><strong>'; _e('DSO File and Folder Permissions / Recommendations', 'bulletproof-security'); echo '</strong></div>';
	echo '<table style="width:100%;background-color:#A9F5A0;border-bottom:1px solid black;border-top:1px solid black;">';
	echo '<tr>';
	echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Name', 'bulletproof-security'); echo '<br>'; _e('Folder Name', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:35%;font-weight:bold;">'; _e('File Path', 'bulletproof-security'); echo '<br>'; _e('Folder Path', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Recommended', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
    echo '<td style="padding:2px;width:15%;font-weight:bold;">'; _e('Current', 'bulletproof-security'); echo '<br>'; _e('Permissions', 'bulletproof-security'); echo '</td>';
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
	echo '<div style=\'padding-bottom:15px;\'></div>';
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
            
<div id="bps-tabs-3" class="bps-tab-page">
<h2><?php _e('Security Log', 'bulletproof-security'); ?></h2>
<div id="bpsAutoProtect" style="border-top:1px solid #999999;">

<h3><?php _e('Security Log', 'bulletproof-security'); ?>  <button id="bps-open-modal9" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content9" title="<?php _e('Security Log', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br><strong>'.__('Security Log General Information', 'bulletproof-security').'</strong><br>'.__('Your Security Log file is a plain text static file and not a dynamic file or dynamic display to keep your website resource usage at a bare minimum and keep your website performance at a maximum.', 'bulletproof-security').'<br><br>'.__('Log entries are logged in descending order by Date and Time. You can copy, edit and delete this plain text file.', 'bulletproof-security').'<strong>'.__(' NOTE: ', 'bulletproof-security').'</strong>'.__('500KB is the maximum recommended log file size. If your log file reaches 500KB in size then copy it to your computer and click the Delete button to delete it.', 'bulletproof-security').'<br><br>'.__('The Security Log logs 400 and 403 HTTP Response Status Codes by default. You can also log 404 HTTP Response Status Codes by opening this BPS 404 Template file - /bulletproof-security/404.php and copying the logging code into your Theme\'s 404 Template file. When you open the BPS Pro 404.php file you will see simple instructions on how to add the 404 logging code to your Theme\'s 404 Template file.', 'bulletproof-security').'<br><br><strong>'.__('HTTP Response Status Codes', 'bulletproof-security').'</strong><br>'.__('400 Bad Request - The request could not be understood by the server due to malformed syntax.', 'bulletproof-security').'<br><br>'.__('403 Forbidden - The Server understood the request, but is refusing to fulfill it.', 'bulletproof-security').'<br><br>'.__('404 Not Found - The server has not found anything matching the Request-URI / URL. No indication is given of whether the condition is temporary or permanent.', 'bulletproof-security').'<br><br><strong>'.__('Security Log File Size', 'bulletproof-security').'</strong><br>'.__('Displays the size of your Security Log file. If your log file is larger than 500KB you will see a Red warning message displayed: Your Security Log file is very large which will cause the BPS Options page to load much slower. Copy and paste the Security Log file contents into a Notepad text file on your computer and save it. Then click the Delete Log button to delete the contents of this Log file.', 'bulletproof-security').'<br><br><strong>'.__('Security Log Last Modified Time:', 'bulletproof-security').'</strong><br>'.__('Displays the last time a Security Log entry was logged.', 'bulletproof-security').'<br><br><strong>'.__('Delete Log Button', 'bulletproof-security').'</strong><br>'.__('Clicking the Delete Log button will delete the entire contents of your Security Log File.', 'bulletproof-security'); echo $text; ?></p>
</div>

<?php
// Get File Size of the Security Log File 
// 1MB = 1048576 bytes - 500KB = 512000 bytes
function getSecurityLogSize() {
$filename = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
if (file_exists($filename)) {
	$logSize = filesize($filename);
	if ($logSize <= 512000) {
 		$text = '<strong>'. __('Security Log File Size: ', 'bulletproof-security').'<font color="blue">'. round($logSize / 1024, 2) .' KB</font></strong><br>';
		echo $text;
	} else {
 		$text = '<strong>'. __('Security Log File Size: ', 'bulletproof-security').'<font color="red">'. round($logSize / 1024, 2) .' KB<br>'.__('Your Security Log file is very large which will cause the BPS Options page to load much slower.', 'bulletproof-security').'<br>'.__('Copy and paste the Security Log file contents into a Notepad text file on your computer and save it.', 'bulletproof-security').'<br>'.__('Then click the Delete Log button to delete the contents of this Log file.', 'bulletproof-security').'</font></strong><br>';		
		echo $text;
	}
	}
}
echo getSecurityLogSize().'<br>';

// Get the Current / Last Modifed Date of the Security Log File
function getSecurityLogLastMod() {
$filename = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
if (file_exists($filename)) {
	$last_modified = date ("F d Y H:i:s", filemtime($filename));
	$text = '<strong>'. __('Security Log Last Modified Time: ', 'bulletproof-security').'<font color="blue">'. $last_modified . '</font></strong><br>';
		echo $text;
	}
}
echo getSecurityLogLastMod();

if (isset($_POST['Submit-Delete-Log']) && current_user_can('manage_options')) {
	check_admin_referer( 'bps-delete-security-log' );

	$SecurityLog = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
	$SecurityLogMaster = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/http_error_log.txt'; 
	copy($SecurityLogMaster, $SecurityLog);
}
?>

<form name="DeleteLogForm" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-3" method="post">
<?php wp_nonce_field('bps-delete-security-log'); ?>
<p class="submit">
<input type="submit" name="Submit-Delete-Log" value="<?php esc_attr_e('Delete Log', 'bulletproof-security') ?>" class="bps-blue-button" onclick="return confirm('<?php $text = __('Clicking OK will delete the contents of your Security Log file.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Delete the Log file contents or click Cancel.', 'bulletproof-security'); echo $text; ?>')" /></p>
</form>

<div id="messageinner" class="updatedinner" style="width:665px;">

<?php

// Get BPS Security log file contents
function bps_get_security_log() {
if (current_user_can('manage_options')) {
	$bps_sec_log = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
	if (file_exists($bps_sec_log)) {
	$bps_sec_log = file_get_contents($bps_sec_log);
	return $bps_sec_log;
	} else {
	_e('The Security Log File Was Not Found! Check that the file really exists here - /wp-content/bps-backup/logs/http_error_log.txt and is named correctly.', 'bulletproof-security');
	}
	}
}

// Form - Security Log - Perform File Open and Write test - If append write test is successful write to file
if (current_user_can('manage_options')) {
$bps_sec_log = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
$write_test = "";
	if (is_writable($bps_sec_log)) {
    if (!$handle = fopen($bps_sec_log, 'a+b')) {
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	exit;
    }
	$text = '<font color="green"><strong>'.__('File Open and Write test successful! Your Security Log file is writable.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	}
	
	if (isset($_POST['submit-security-log']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_security_log' );
	$newcontentSecLog = stripslashes($_POST['newcontentSecLog']);
	if ( is_writable($bps_sec_log) ) {
		$handle = fopen($bps_sec_log, 'w+b');
		fwrite($handle, $newcontentSecLog);
	$text = '<font color="green"><strong>'.__('Success! Your Security Log file has been updated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;	
    fclose($handle);
	}
}
$scrolltoSecLog = isset($_REQUEST['scrolltoSecLog']) ? (int) $_REQUEST['scrolltoSecLog'] : 0;
?>
</div>

<div id="SecLogEditor">
<form name="bpsSecLog" id="bpsSecLog" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-3" method="post">
<?php wp_nonce_field('bulletproof_security_save_security_log'); ?>
<div id="bpsSecLog">
    <textarea cols="130" rows="27" name="newcontentSecLog" id="newcontentSecLog" tabindex="1"><?php echo bps_get_security_log(); ?></textarea>
	<input type="hidden" name="scrolltoSecLog" id="scrolltoSecLog" value="<?php echo $scrolltoSecLog; ?>" />
    <p class="submit">
	<input type="submit" name="submit-security-log" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#bpsSecLog').submit(function(){ $('#scrolltoSecLog').val( $('#newcontentSecLog').scrollTop() ); });
	$('#newcontentSecLog').scrollTop( $('#scrolltoSecLog').val() ); 
});
/* ]]> */
</script>
</div>
</div>
</div>

<div id="bps-tabs-4">
<h2><?php _e('System Information', 'bulletproof-security'); ?></h2>

<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-system_info_table">
  <tr>
    <td width="49%" class="bps-table_title"><?php _e('Website / Server / Opcode Cache / Accelerators / IP Info', 'bulletproof-security'); ?></td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title"><?php _e('SQL Database / Permalink Structure / WP Installation Folder', 'bulletproof-security'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell">
    <?php 
	echo __('Website Root Folder', 'bulletproof-security').': <strong>'.get_site_url().'</strong><br>';
	echo __('Document Root Path', 'bulletproof-security').': <strong>'.esc_html($_SERVER['DOCUMENT_ROOT']).'</strong><br>'; 
	echo __('WP ABSPATH', 'bulletproof-security').': <strong>'.ABSPATH.'</strong><br>';
	echo __('Parent Directory', 'bulletproof-security').': <strong>'.dirname(ABSPATH).'</strong><br>';  
	echo __('Server / Website IP Address', 'bulletproof-security').': <strong>'.esc_html($_SERVER['SERVER_ADDR']).'</strong><br>';    
	echo __('Host by Address', 'bulletproof-security').': <strong>'.esc_html(@gethostbyaddr($_SERVER['SERVER_ADDR'])).'</strong><br>';    
	echo __('DNS Name Server', 'bulletproof-security').': <strong>'; if ($bpsTargetNS != '') { echo $bpsTargetNS; } else { echo $bpsTarget; } echo '</strong><br>';
	echo __('Public IP / Your Computer IP Address', 'bulletproof-security').': <strong>'.esc_html($_SERVER['REMOTE_ADDR']).'</strong><br>';
	echo __('Server Type', 'bulletproof-security').': <strong>'.esc_html($_SERVER['SERVER_SOFTWARE']).'</strong><br>';
	echo __('Operating System', 'bulletproof-security').': <strong>'.PHP_OS.'</strong><br>';  
	echo __('Server API', 'bulletproof-security').': <strong>';
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
    echo $sapi_type.' - '.__('Your Host Server is using CGI.', 'bulletproof-security');
	} else {
    echo $sapi_type.' - '.__('Your Host Server is using DSO or another SAPI type.', 'bulletproof-security');
	}
	echo '</strong><br>';
echo __('cURL', 'bulletproof-security').': <strong>';
	if (extension_loaded('curl')) {
		_e('cURL Extension is Loaded', 'bulletproof-security');
	} else {
		_e('cURL Extension is Not Loaded', 'bulletproof-security');
	}
	echo '</strong><br>';	
	echo __('Zend Engine Version', 'bulletproof-security').': <strong>'.zend_version().'</strong><br>'; 
	echo __('Zend Guard/Optimizer', 'bulletproof-security').': <strong>';
	if (extension_loaded('Zend Optimizer+') && ini_get('zend_optimizerplus.enable') == 1 || ini_get('zend_optimizerplus.enable') == 'On' ) {
		_e('Zend Optimizer+ Extension is Loaded and Enabled', 'bulletproof-security');
	}
	if (extension_loaded('Zend Optimizer')) {
		_e('Zend Optimizer Extension is Loaded', 'bulletproof-security');
	}
	if (extension_loaded('Zend Guard Loader')) {
		_e('Zend Guard Loader Extension is Loaded', 'bulletproof-security');
	} else {
	if (!extension_loaded('Zend Optimizer+') && !extension_loaded('Zend Optimizer') && !extension_loaded('Zend Guard Loader')) {
		_e('A Zend Extension is Not Loaded', 'bulletproof-security');		
	}
	}
	echo '</strong><br>';    
	echo __('ionCube Loader', 'bulletproof-security').': <strong>'; 
	if (extension_loaded('IonCube Loader') && function_exists('ioncube_loader_iversion')) {
	echo __('ionCube Loader Extension is Loaded ', 'bulletproof-security').__('Version: ', 'bulletproof-security').ioncube_loader_iversion();
	} else {
	echo __('ionCube Loader Extension is Not Loaded', 'bulletproof-security');
	}
	echo '</strong><br>';
	echo __('Suhosin', 'bulletproof-security').': <strong>';
	$bpsconstants = get_defined_constants();
	if (isset($bpsconstants['SUHOSIN_PATCH']) && $bpsconstants['SUHOSIN_PATCH'] == 1) {
		_e('The Suhosin-Patch is installed', 'bulletproof-security');
	}
	if (extension_loaded('suhosin')) {
		_e('Suhosin-Extension is Loaded', 'bulletproof-security');	
	} else {
	if (!isset($bpsconstants['SUHOSIN_PATCH']) && @$bpsconstants['SUHOSIN_PATCH'] != 1 && !extension_loaded('suhosin')) {
		_e('Suhosin is Not Installed/Loaded', 'bulletproof-security');			
	}
	}
	echo '</strong><br>';
	echo __('APC', 'bulletproof-security').': <strong>';
	if (extension_loaded('apc') && ini_get('apc.enabled') == 1 || ini_get('apc.enabled') == 'On' ) {
		_e('APC Extension is Loaded and Enabled', 'bulletproof-security');
	} 
	elseif (extension_loaded('apc') && ini_get('apc.enabled') == 0 || ini_get('apc.enabled') == 'Off' ) {
		_e('APC Extension is Loaded but Not Enabled', 'bulletproof-security');
	} else {
		_e('APC Extension is Not Loaded', 'bulletproof-security');	
	}
	echo '</strong><br>';  	    
	echo __('eAccelerator', 'bulletproof-security').': <strong>';
	if (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') == 1 || ini_get('eaccelerator.enable') == 'On' ) {
		_e('eAccelerator Extension is Loaded and Enabled', 'bulletproof-security');
	} 
	elseif (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') == 0 || ini_get('eaccelerator.enable') == 'Off' ) {
		_e('eAccelerator Extension is Loaded but Not Enabled', 'bulletproof-security');
	} else {
		_e('eAccelerator Extension is Not Loaded', 'bulletproof-security');	
	}	
	echo '</strong><br>';  	  
	echo __('XCache', 'bulletproof-security').': <strong>';
	if (extension_loaded('xcache') && ini_get('xcache.size') > 0 && ini_get('xcache.cacher') == 'On' || ini_get('xcache.cacher') == '1') {
		_e('XCache Extension is Loaded and Enabled', 'bulletproof-security');
	} 
	elseif (extension_loaded('xcache') && ini_get('xcache.size') <= 0 && ini_get('xcache.cacher') == 'Off' || ini_get('xcache.cacher') == '0') {
		_e('XCache Extension is Loaded but Not Enabled', 'bulletproof-security');
	} else {
		_e('XCache Extension is Not Loaded', 'bulletproof-security');	
	}	
	echo '</strong><br>';
	echo __('Varnish', 'bulletproof-security').': <strong>';
	if (extension_loaded('varnish')) {
		_e('Varnish Extension is Loaded', 'bulletproof-security');
	} else {
		_e('Varnish Extension is Not Loaded', 'bulletproof-security');	
	}	
	echo '</strong><br>';
	echo __('Memcache', 'bulletproof-security').': <strong>';
	if (extension_loaded('memcache')) {
	$memcache = new Memcache;
	@$memcache->connect('localhost', 11211);
	echo __('Memcache Extension is Loaded', 'bulletproof-security').__('Version: ', 'bulletproof-security').@$memcache->getVersion();
	} else {
		_e('Memcache Extension is Not Loaded', 'bulletproof-security');	
	}	
	echo '</strong><br>';
	echo __('Memcached', 'bulletproof-security').': <strong>';
	if (extension_loaded('memcached')) {
	$memcached = new Memcached();
	@$memcached->addServer('localhost', 11211);
	echo __('Memcached Extension is Loaded', 'bulletproof-security').__('Version: ', 'bulletproof-security').@$memcached->getVersion();
	} else {
		_e('Memcached Extension is Not Loaded', 'bulletproof-security');	
	}	
	echo '</strong><br>';
	?>

    </td>
    <td>&nbsp;</td>
    <td rowspan="2" class="bps-table_cell">
	<?php 
	echo __('MySQL Database Version', 'bulletproof-security').': ';
	$sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
	echo '<strong>'.$sqlversion.'</strong><br>';
	echo __('MySQL Client Version', 'bulletproof-security').': <strong>'.mysql_get_client_info().'</strong><br>';
	echo __('Database Host', 'bulletproof-security').': <strong>'.DB_HOST.'</strong><br>';
	echo __('Database Name', 'bulletproof-security').': <strong>'.DB_NAME.'</strong><br>';
	echo __('Database User', 'bulletproof-security').': <strong>'.DB_USER.'</strong><br>';
	echo __('SQL Mode', 'bulletproof-security').': ';
	$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
	if (is_array($mysqlinfo)) { 
	$sql_mode = $mysqlinfo[0]->Value;
    if (empty($sql_mode)) { 
	$sql_mode = '<strong>'.__('Not Set', 'bulletproof-security').'</strong>';
	} else {
	$sql_mode = '<strong>'.__('Off', 'bulletproof-security').'</strong>';
	}}
	echo $sql_mode;
	echo '<br><br>';
	echo __('WordPress Installation Folder', 'bulletproof-security').': <strong>';
	echo bps_wp_get_root_folder().'</strong><br>';
	echo __('WordPress Installation Type', 'bulletproof-security').': ';
	echo bps_wp_get_root_folder_display_type().'<br>';
	echo __('Network/Multisite', 'bulletproof-security').': ';
	echo bps_multsite_check().'<br>';
	echo __('WP Permalink Structure', 'bulletproof-security').': <strong>';
	$permalink_structure = get_option('permalink_structure'); 
	echo $permalink_structure.'</strong><br>';
	echo bps_check_permalinks().'<br>';
	echo bps_check_php_version().'<br>';
	echo __('Browser Compression Supported', 'bulletproof-security').': <strong>'.esc_html($_SERVER['HTTP_ACCEPT_ENCODING']).'</strong>';
	?>
      </td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <!-- <td class="bps-table_cell">&nbsp;</td> -->
    </tr>
  <tr>
    <td class="bps-table_title"><?php _e('PHP Server / PHP.ini Info', 'bulletproof-security'); ?></td>
    <td>&nbsp;</td>
    <td class="bps-table_title"><?php _e('BPS Pro Security Modules Info', 'bulletproof-security'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell">
	<?php 
	echo __('PHP Version', 'bulletproof-security').': <strong>'.PHP_VERSION.'</strong><br>';
	echo __('PHP Memory Usage', 'bulletproof-security').': <strong>'.round(memory_get_usage() / 1024 / 1024, 2) . __(' MB').'</strong><br>';    
	echo __('WordPress Admin Memory Limit', 'bulletproof-security').': '; $memory_limit = ini_get('memory_limit');
	echo '<strong>'.$memory_limit.'</strong><br>';
	echo __('WordPress Base Memory Limit', 'bulletproof-security').': <strong>'.WP_MEMORY_LIMIT.'</strong><br>';
	echo __('PHP Actual Configuration Memory Limit', 'bulletproof-security').': <strong>'.get_cfg_var('memory_limit').'</strong><br>';
	echo __('PHP Max Upload Size', 'bulletproof-security').': '; $upload_max = ini_get('upload_max_filesize');
	echo '<strong>'.$upload_max.'</strong><br>';
	echo __('PHP Max Post Size', 'bulletproof-security').': '; $post_max = ini_get('post_max_size');
	echo '<strong>'.$post_max.'</strong><br>';
	echo __('PHP Safe Mode', 'bulletproof-security').': ';
	if (ini_get('safe_mode') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>'; 
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP Allow URL fopen', 'bulletproof-security').': ';
	if (ini_get('allow_url_fopen') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}	
	echo __('PHP Allow URL Include', 'bulletproof-security').': ';
	if (ini_get('allow_url_include') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>'; 
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} 
	echo __('PHP Display Errors', 'bulletproof-security').': ';
	if (ini_get('display_errors') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>'; 
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP Display Startup Errors', 'bulletproof-security').': ';
	if (ini_get('display_startup_errors') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP Expose PHP', 'bulletproof-security').': ';
	if (ini_get('expose_php') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP Register Globals', 'bulletproof-security').': ';
	if (ini_get('register_globals') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP MySQL Allow Persistent Connections', 'bulletproof-security').': ';
	if (ini_get('mysql.allow_persistent') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>'; 
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP Output Buffering', 'bulletproof-security').': ';
	$output_buffering = ini_get('output_buffering');
	if (ini_get('output_buffering') != 0) { 
	echo '<font color="red"><strong>'.$output_buffering.'</strong></font><br>';
	} else { 
	echo '<font color="green"><strong>'.$output_buffering.'</strong></font><br>'; 
	}
	echo __('PHP Max Script Execution Time', 'bulletproof-security').': '; $max_execute = ini_get('max_execution_time');
	echo '<strong>'.$max_execute.' Seconds</strong><br>';
	echo __('PHP Magic Quotes GPC', 'bulletproof-security').': ';
	if (ini_get('magic_quotes_gpc') == 1) { 
	$text = '<font color="red"><strong>'.__('On', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>'; 
	} else { 
	$text = '<font color="green"><strong>'.__('Off', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>'; 
	}
	echo __('PHP open_basedir', 'bulletproof-security').': ';
	$open_basedir = ini_get('open_basedir');
	if ($open_basedir != '') {
	echo '<strong>'.$open_basedir.'</strong><br>';
	} else {
	echo '<strong>'.__('not in use', 'bulletproof-security').'</strong><br>';	
	}
	echo __('PHP XML Support', 'bulletproof-security').': ';
	if (is_callable('xml_parser_create')) { 
	$text = '<strong>'.__('Yes', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} else { 
	$text = '<strong>'.__('No', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP IPTC Support', 'bulletproof-security').': ';
	if (is_callable('iptcparse')) { 
	$text = '<strong>'.__('Yes', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} else { 
	$text = '<strong>'.__('No', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	echo __('PHP Exif Support', 'bulletproof-security').': ';
	if (is_callable('exif_read_data')) { 
	$text = '<strong>'.__('Yes', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	} else { 
	$text = '<strong>'.__('No', 'bulletproof-security').'</strong></font>';
	echo $text.'<br>';
	}
	?>
	
    </td>      
    <td>&nbsp;</td>
    <td rowspan="2" class="bps-table_cell">
	<?php 
	//echo bpsPro_sysinfo_mod_checks_smon().'<br>';
	//echo bpsPro_sysinfo_mod_checks_hud().'<br>';
	//echo bpsPro_sysinfo_mod_checks_phpini().'<br>';
	//echo bpsPro_sysinfo_mod_checks_elog().'<br>';
	?>
    
    </td>
  </tr>
  <tr>
    <td class="bps-table_cell">&nbsp;</td>
    <td>&nbsp;</td>
    <!-- <td class="bps-table_cell">&nbsp;</td> -->
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
            
<div id="bps-tabs-5" class="bps-tab-page">
<h2><?php _e('BulletProof Security Backup &amp; Restore', 'bulletproof-security'); ?></h2>

<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
<h3><?php _e('Backup Your Currently Active .htaccess Files', 'bulletproof-security'); ?></h3>
<h3><?php echo '<font color="red"><strong>'; _e('CAUTION: ', 'bulletproof-security'); echo '</strong></font>'; ?>  <button id="bps-open-modal10" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content10" title="<?php _e('.htaccess File Backup', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br>'.__('Back up your existing .htaccess files first before activating any BulletProof Security Modes in case of a problem when you first install and activate any BulletProof Security Modes. Once you have backed up your original existing .htaccess files you will see the status listed in the ','bulletproof-security').'<strong>'.__('Current Backed Up .htaccess Files Status','bulletproof-security').'</strong>'.__(' window below. ','bulletproof-security').'<br><br><strong>'.__('Backup files are stored in this folder /wp-content/bps-backup.','bulletproof-security').'</strong><br><br>'.__('In cases where you install a plugin that writes to your htaccess files you will want to perform another backup of your htaccess files. Each time you perform a backup you are overwriting older backed up htaccess files. Backed up files are stored in the /wp-content/bps-folder.','bulletproof-security').'<br><br>'.__('You could also use the BPS File Downloader to download any existing .htaccess files, customized .htaccess files or other BPS files that you have personally customized or modified just for an additional local backup.','bulletproof-security').'<br><br><strong>'.__('The BPS Master .htaccess files are stored in your /plugins/bulletproof-security/admin/htaccess folder and can also be backed up to the /wp-content/bps-backup/master-backups folder.','bulletproof-security').'</strong><br>'.__('Backed up files are stored online so they will be available to you after upgrading to a newer version of BPS if you run into a problem. There is no Restore feature for the BPS Master files because you should be using the latest versions of the BPS master .htaccess files after you upgrade BPS. You can manually download the files from this folder /wp-content/bps-backup/master-backups using FTP or your web host file downloader.','bulletproof-security').'<br><br>'.__('When you upgrade BPS your current root and wp-admin htaccess files are not affected. BPS master htaccess files are replaced when you upgrade BPS so if you have made changes to your BPS master files that you want to keep make sure they are backed up first before upgrading. You can also download copies of the BPS master files to your computer using the BPS File Downloader if you want. When you backup your BPS files it is an online backup so the files will be available to you to restore from if you run into any problems at any point. You should always be using the newest BPS master htaccess files for the latest security protection updates and plugin conflict fixes. Before activating new BPS master files you can use the BPS File Editor to copy and paste any existing htaccess code that you want to keep from your current active htaccess files to the new BPS master htaccess files and save your changes before activating the new BPS htaccess files. Or you can copy any new htaccess code from the new BPS master files to your existing currently active htaccess files. If you do this be sure to edit the BPS  version number in your currently active htaccess files or you will get error messages.','bulletproof-security').'<br><br><strong>'.__('If something goes wrong in the .htaccess file editing process or at any point you can restore your good .htaccess files with one click as long as you already backed them up.','bulletproof-security'); echo $text; ?></p>
</div>


<form name="BulletProof-Backup" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_backup_active_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection9" type="radio" value="backup_htaccess" class="tog" <?php echo checked('', $backup_htaccess); ?> />
<?php _e('Backup .htaccess Files', 'bulletproof-security'); ?></label></th>
	<td><?php $text = '<font color="green"><strong>'.__('Backs up your currently active .htaccess files in your root and /wp-admin folders.', 'bulletproof-security').'</strong></font><br><strong>'.__('Backup your htaccess files for first time installations of BPS or whenever new modifications have been made to your htaccess files. Read the ', 'bulletproof-security').'<font color="red"><strong>'.__('CAUTION: ', 'bulletproof-security').'</strong></font>'.__('Read Me button.', 'bulletproof-security').'</strong>'; echo $text; ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit9" class="bps-blue-button" value="<?php esc_attr_e('Backup Files', 'bulletproof-security') ?>" />
</p></form>

<h3><?php _e('Restore Your .htaccess Files From Backup', 'bulletproof-security'); ?>  <button id="bps-open-modal11" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content11" title="<?php _e('.htaccess File Restore', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br>'.__('Restores your backed up .htaccess files that you backed up. Your backed up .htaccess files were renamed to root.htaccess and wpadmin.htaccess and copied to the /wp-content/bps-backup folder. Restoring your backed up .htaccess files will rename them back to .htaccess and copy them back to your root and /wp-admin folders respectively.','bulletproof-security').'<br><br><strong>'.__('If you did not have any original .htaccess files to begin with and / or you did not back up any files then you will not have any backed up .htaccess files.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="BulletProof-Restore" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_restore_active_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection10" type="radio" value="restore_htaccess" class="tog" <?php checked('', $restore_htaccess); ?> />
<?php _e('Restore .htaccess Files', 'bulletproof-security'); ?></label></th>
	<td><?php $text = '<font color="green"><strong>'.__('Restores your backed up .htaccess files to your root and /wp-admin folders.', 'bulletproof-security').'</strong></font><br><strong>'.__('Restore your backed up .htaccess files if you have any problems or for use between BPS ugrades.', 'bulletproof-security').'</strong>'; echo $text; ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit10" class="bps-blue-button" value="<?php esc_attr_e('Restore Files', 'bulletproof-security') ?>" />
</p></form>

<h3><?php _e('Backup Your BPS Master .htaccess Files', 'bulletproof-security'); ?>  <button id="bps-open-modal12" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content12" title="<?php _e('Master .htaccess File Backup', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br>'.__('The BPS Master .htaccess files are stored in your /plugins/bulletproof-security/admin/htaccess folder and can also be backed up using this Master Backup feature. The backed up BPS Master .htaccess files are copied to this folder /wp-content/bps-backup/master-backups folder. This way they will be available to you online after upgrading to a newer version of BPS. There is no Restore feature for the BPS Master files because you should be using the latest versions of the BPS master .htaccess files after you upgrade BPS. You can manually download the files from this folder /wp-content/bps-backup/master-backups using FTP or your web host file downloader.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="BPS-Master-Htaccess-Backup" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_backup_master_htaccess_files'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection11" type="radio" value="backup_master_htaccess_files" class="tog" <?php checked('', $backup_master_htaccess_files); ?> />
<?php _e('Backup BPS Master .htaccess Files', 'bulletproof-security'); ?></label></th>
	<td><?php $text = '<font color="green"><strong>'.__('Backs up your BPS Master .htaccess files to the /wp-content/bps-backup/master-backups folder.', 'bulletproof-security').'</strong></font><br><strong>'.__('There is no Restore feature for the BPS Master .htaccess files because you should be using the latest most current BPS Master .htaccess security coding and plugin fixes included in the most current version of the BPS master .htacess files.', 'bulletproof-security').'</strong>'; echo $text; ?></td>
	<td>
    </td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit11" class="bps-blue-button" value="<?php esc_attr_e('Backup Master Files', 'bulletproof-security') ?>" />
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
    <td class="bps-table_title_SS"><?php _e('Current Backed Up .htaccess Files Status', 'bulletproof-security'); ?>  <button id="bps-open-modal13" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    <div id="bps-modal-content13" title="<?php _e('Backup .htaccess File', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br>'.__('General file checks to check which files have been backed up or not.','bulletproof-security'); echo $text; ?></p>
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
        
<div id="bps-tabs-6" class="bps-tab-page">
<table width="100%" border="0">
  <tr>
    <td width="33%"><h2><?php _e('BulletProof Security File Editing', 'bulletproof-security'); ?></h2></td>
    <td width="21%"><button id="bps-open-modal14" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button>
    <div id="bps-modal-content14" title="<?php _e('File Editing', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('Lock / Unlock .htaccess Files','bulletproof-security').'</strong><br>'.__('If your Server API is using CGI then you will see Lock and Unlock buttons to lock your Root .htaccess file with 404 Permissions and unlock your root .htaccess file with 644 Permissions. If your Server API is using CLI - DSO / Apache / mod_php then you will not see lock and unlock buttons. 644 Permissions are required to write to / edit the root .htaccess file. Once you are done editing your root .htaccess file use the lock button to lock it with 404 Permissions. 644 Permissions for DSO are considered secure for DSO because of the different way that file security is handled with DSO.','bulletproof-security').'<br><br>'.__('If your Root .htaccess file is locked and you try to save your editing changes you will see a pop message that your Root .htaccess file is locked. You will need to unlock your Root .htaccess file before you can save your changes.','bulletproof-security').'<br><br><strong>'.__('Turn On AutoLock / Turn Off AutoLock','bulletproof-security').'</strong><br>'.__('AutoLock is designed to automatically lock your root .htaccess file to save you an additional step of locking your root .htaccess file when performing certain actions, tasks or functions and AutoLock also automatically locks your root .htaccess during BPS Pro upgrades. This can be a problem for some folks whose Web Hosts do not allow locking the root .htaccess file with 404 file permissions and can cause 403 errors and/or cause a website to crash. For 99.99% of folks leaving AutoLock turned On will work fine. If your Web Host ONLY allows 644 file permissions for your root .htaccess file then click the Turn Off AutoLock button. This turns Off AutoLocking for all BPS actions, tasks, functions and also for BPS upgrades.','bulletproof-security').'<br><br><strong>'.__('The File Editor is designed to open all of your .htaccess files simultaneously and allow you to copy and paste from one window (file) to another window (file), BUT you can ONLY save your edits for one file at a time. Whichever file you currently have opened (the tab that you are currently viewing) when you click the Update File button is the file that will be updated / saved. This is done for 2 reasons - reduces the chances of making an editing mistake and for better security.','bulletproof-security'); echo $text; ?></p>
</div>
</td>
    <td width="19%" align="right">
    <h3 style="margin-right:0px;"><button id="bps-open-modal15" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
    <div id="bps-modal-content15" title="<?php _e('File Uploading / Downloading', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('File Uploading','bulletproof-security').'</strong><br>'.__('The file upload location is preset to the /wp-content/plugins/bulletproof-security/admin/htaccess folder and the intended use is just for uploading the BPS Master files: secure.htaccess, default.htaccess, wpadmin-secure.htaccess, maintenance.htaccess, bp-maintenance.php, bps-maintenance-values.php, http_error_log.txt (BPS Pro only) or other files from your computer to the BPS Master htaccess folder.','bulletproof-security').'<br><br><strong>'.__('File Downloading','bulletproof-security').'</strong><br><strong>'.__('File Downloading is automatically not allowed. Folder permissions must be set to a minimum of 705 for the /htaccess and /bps-backup folders in order to open and download files.','bulletproof-security').'</strong><br>'.__('Click the Enable Master File Downloading button to enable file downloading. This will write your current IP address to the deny all htaccess file and allow ONLY you access to the /plugins/bulletproof-security/admin/htaccess folder to open and download files. To open and download your Backed up files click the Enable Backed Up File Downloading button. After clicking the Enable File Downloading buttons you can click the download buttons below to open or download files. If your IP address changes which it will do frequently then click the Enable File Downloading buttons again to write a new IP address to the deny all htaccess files.','bulletproof-security').'<br><br><strong>'; echo $text; ?></p>
</div>
    </td>
    <td width="27%" align="center"><h2><?php _e('Uploads - Downloads', 'bulletproof-security'); ?></h2></td>
  </tr>
</table>

<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
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
$secure_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
$write_test = "";
	if (is_writable($secure_htaccess_file)) {
    if (!$handle = fopen($secure_htaccess_file, 'a+b')) {
	$text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$secure_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$secure_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
	$text = '<strong>'.__('File Open and Write test successful! The secure.htaccess file is writable.', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	if (file_exists($secure_htaccess_file)) {
	$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security')."$secure_htaccess_file" . '</strong></font><br>';
	echo $text;
	}
	}
	}
	
	if (isset($_POST['submit1']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_1' );
	$newcontent1 = stripslashes($_POST['newcontent1']);
	if ( is_writable($secure_htaccess_file) ) {
		$handle = fopen($secure_htaccess_file, 'w+b');
		fwrite($handle, $newcontent1);
		$text = '<font color="green"><strong>'.__('Success! The secure.htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$default_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
$write_test = "";
	if (is_writable($default_htaccess_file)) {
    if (!$handle = fopen($default_htaccess_file, 'a+b')) {
	$text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$default_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$default_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
	$text = '<strong>'.__('File Open and Write test successful! The default.htaccess file is writable.', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	if (file_exists($default_htaccess_file)) {
	$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security')."$default_htaccess_file" . '</strong></font><br>';
	echo $text;
	}
	}
	}
	
	if (isset($_POST['submit2']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_2' );
	$newcontent2 = stripslashes($_POST['newcontent2']);
	if ( is_writable($default_htaccess_file) ) {
		$handle = fopen($default_htaccess_file, 'w+b');
		fwrite($handle, $newcontent2);
	$text = '<font color="green"><strong>'.__('Success! The default.htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$maintenance_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/maintenance.htaccess';
$write_test = "";
	if (is_writable($maintenance_htaccess_file)) {
    if (!$handle = fopen($maintenance_htaccess_file, 'a+b')) {
	$text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$maintenance_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$maintenance_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
	$text = '<strong>'.__('File Open and Write test successful! The maintenance.htaccess file is writable.', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	if (file_exists($maintenance_htaccess_file)) {
	$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security')."$maintenance_htaccess_file" . '</strong></font><br>';
	echo $text;
	}
	}
	}
	
	if (isset($_POST['submit3']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_3' );
	$newcontent3 = stripslashes($_POST['newcontent3']);
	if ( is_writable($maintenance_htaccess_file) ) {
		$handle = fopen($maintenance_htaccess_file, 'w+b');
		fwrite($handle, $newcontent3);
	$text = '<font color="green"><strong>'.__('Success! The maintenance.htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;	
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$wpadmin_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
$write_test = "";
	if (is_writable($wpadmin_htaccess_file)) {
    if (!$handle = fopen($wpadmin_htaccess_file, 'a+b')) {
	$text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$wpadmin_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$wpadmin_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
	$text = '<strong>'.__('File Open and Write test successful! The wpadmin-secure.htaccess file is writable.', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	if (file_exists($wpadmin_htaccess_file)) {
	$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security')."$wpadmin_htaccess_file" . '</strong></font><br>';
	echo $text;
	}
	}
	}
	
	if (isset($_POST['submit4']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_4' );
	$newcontent4 = stripslashes($_POST['newcontent4']);
	if ( is_writable($wpadmin_htaccess_file) ) {
		$handle = fopen($wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent4);
	$text = '<font color="green"><strong>'.__('Success! The wpadmin-secure.htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$root_htaccess_file = ABSPATH . '.htaccess';
$write_test = "";
	if (is_writable($root_htaccess_file)) {
    if (!$handle = fopen($root_htaccess_file, 'a+b')) {
	$text = '<font color="black"><strong>'.__('Cannot open file ', 'bulletproof-security')."$root_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	$text = '<font color="black"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$root_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
	$text = '<strong>'.__('File Open and Write test successful! Your currently active root .htaccess file is writable.', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	if (file_exists($root_htaccess_file)) {
	$text = '<font color="blue"><strong>'.__('Your root .htaccess file is Locked with Read Only Permissions.', 'bulletproof-security').'<br>'.__('Use the Lock and Unlock buttons below to Lock or Unlock your root .htaccess file for editing.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
	$text = '<font color="black"><strong>'.__('Cannot write to file: ', 'bulletproof-security')."$root_htaccess_file" . '</strong></font><br>';
	echo $text;
	}
	}
	}
	
	if (isset($_POST['submit5']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_5' );
	$newcontent5 = stripslashes($_POST['newcontent5']);
	if ( !is_writable($root_htaccess_file) ) {
	$text = '<font color="red"><strong>'.__('Error: Unable to write to the Root .htaccess file. If your Root .htaccess file is locked you must unlock first.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}	
	if ( is_writable($root_htaccess_file) ) {
		$handle = fopen($root_htaccess_file, 'w+b');
		fwrite($handle, $newcontent5);
	$text = '<font color="green"><strong>'.__('Success! Your currently active root .htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
    fclose($handle);
	}
}

if (current_user_can('manage_options')) {
$current_wpadmin_htaccess_file = ABSPATH . 'wp-admin/.htaccess';
$write_test = "";
	if (is_writable($current_wpadmin_htaccess_file)) {
    if (!$handle = fopen($current_wpadmin_htaccess_file, 'a+b')) {
	$text = '<font color="red"><strong>'.__('Cannot open file ', 'bulletproof-security')."$current_wpadmin_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
    if (fwrite($handle, $write_test) === FALSE) {
	$text = '<font color="red"><strong>'.__('Cannot write to file ', 'bulletproof-security')."$current_wpadmin_htaccess_file" . '</strong></font><br>';
	echo $text;
    exit;
    }
	$text = '<strong>'.__('File Open and Write test successful! Your currently active wp-admin .htaccess file is writable.', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	if (file_exists($current_wpadmin_htaccess_file)) {
	$text = '<font color="blue"><strong>'.__('Cannot write to file: ', 'bulletproof-security')."$current_wpadmin_htaccess_file" . '</strong></font><br>';
	echo $text;
	}
	}
	}
	
	if (isset($_POST['submit6']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_save_settings_6' );
	$newcontent6 = stripslashes($_POST['newcontent6']);
	if ( is_writable($current_wpadmin_htaccess_file) ) {
		$handle = fopen($current_wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent6);
	$text = '<font color="green"><strong>'.__('Success! Your currently active wp-admin .htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
    fclose($handle);
	}
}

// BPS Pro Only - Lock and Unlock Root .htaccess file 
if (isset($_POST['submit-ProFlockLock']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_flock_lock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
	
	if (file_exists($bpsRootHtaccessOL)) {
	chmod($bpsRootHtaccessOL, 0404);
	$text = '<font color="blue"><strong><br>'.__('Your Root .htaccess file has been Locked.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
	$text = '<font color="red"><strong><br>'.__('Unable to Lock your Root .htaccess file.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
}
	
if (isset($_POST['submit-ProFlockUnLock']) && current_user_can('manage_options')) {
	check_admin_referer( 'bulletproof_security_flock_unlock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
		
	if (file_exists($bpsRootHtaccessOL)) {
	chmod($bpsRootHtaccessOL, 0644);
	$text = '<font color="blue"><strong><br>'.__('Your Root .htaccess file has been Unlocked.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
	$text = '<font color="red"><strong><br>'.__('Unable to Unlock your Root .htaccess file.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
}
?>
</div>
</td>
    <td width="33%" align="center" valign="top">
	<?php echo '<div class="bps-file_upload_title"><strong>'; _e('File Uploads', 'bulletproof-security'); echo '<br></strong></div>'; ?>
<form name="BPS-upload" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post" enctype="multipart/form-data"><?php wp_nonce_field('bulletproof_security_upload'); ?>
<p class="submit">
<input id="bps_file_upload" name="bps_file_upload" type="file" />
</p>
<p class="submit" style="margin:-5px 0px 0px -12px;">
<input type="submit" name="submit-bps-upload" class="bps-blue-button" value="<?php esc_attr_e('Upload File', 'bulletproof-security') ?>" />
</p>
</form></td>
  </tr>
  <tr>
    <td width="22%">
<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') { ?>    
 
 	<div style="margin: 5px;">  
    <form name="bpsFlockLockForm" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_flock_lock'); ?>
	<input type="submit" name="submit-ProFlockLock" value="<?php _e('Lock htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Click OK to Lock your Root htaccess file or click Cancel.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Note: The File Open and Write Test window will still display the last status of the file as Unlocked. To see the current status refresh your browser.', 'bulletproof-security'); echo $text; ?>')" />
</form>
<br />
    <form name="bpsRootAutoLock-On" action="options.php" method="post">
    <?php settings_fields('bulletproof_security_options_autolock'); ?>
	<?php $options = get_option('bulletproof_security_options_autolock'); ?>
<input type="hidden" name="bulletproof_security_options_autolock[bps_root_htaccess_autolock]" value="On" />
<input type="submit" name="submit-RootHtaccessAutoLock-On" value="<?php _e('Turn On AutoLock', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Turning AutoLock On will allow BPS Pro to automatically lock your Root .htaccess file. For some folks this causes a problem because their Web Hosts do not allow the Root .htaccess file to be locked. For most folks allowing BPS Pro to AutoLock the Root .htaccess file works fine.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Turn AutoLock On or click Cancel.', 'bulletproof-security'); echo $text; ?>')" /><?php if ($options['bps_root_htaccess_autolock'] == '' || $options['bps_root_htaccess_autolock'] == 'On') { $text = '<font color="blue" style="font-size:14px;border:2px solid gray;padding:2px;margin-left:5px;background-color:#5cf1f9;position:relative;top:1px;"><strong>'.__('On', 'bulletproof-security').'</strong></font>'; echo $text; } ?>
</form>
</div>
<?php } else { echo ''; } ?>
</td>
    <td width="45%">
<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') { ?>        

	<div style="margin: 5px;">    
    <form name="bpsFlockUnLockForm" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_flock_unlock'); ?>

	<input type="submit" name="submit-ProFlockUnLock" value="<?php _e('Unlock htaccess File', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Click OK to Unlock your Root htaccess file or click Cancel.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Note: The File Open and Write Test window will still display the last status of the file as Locked. To see the current status refresh your browser.', 'bulletproof-security'); echo $text; ?>')" />
</form>
<br />
    <form name="bpsRootAutoLock-Off" action="options.php" method="post">
    <?php settings_fields('bulletproof_security_options_autolock'); ?>
	<?php $options = get_option('bulletproof_security_options_autolock'); ?>
<input type="hidden" name="bulletproof_security_options_autolock[bps_root_htaccess_autolock]" value="Off" />
<input type="submit" name="submit-RootHtaccessAutoLock-Off" value="<?php _e('Turn Off AutoLock', 'bulletproof-security'); ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('Turning AutoLock Off will prevent BPS Pro from automatically locking your Root .htaccess file. For some folks this is necessary because their Web Hosts do not allow the Root .htaccess file to be locked. For most folks allowing BPS Pro to AutoLock the Root .htaccess file works fine.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Turn AutoLock Off or click Cancel.', 'bulletproof-security'); echo $text; ?>')" /><?php if ($options['bps_root_htaccess_autolock'] == 'Off') { $text = '<font color="blue" style="font-size:14px;border:2px solid gray;padding:2px;margin-left:5px;background-color:#5cf1f9;position:relative;top:1px;"><strong>'.__('Off', 'bulletproof-security').'</strong></font>'; echo $text; } ?>
</form>
</div>

<?php } else { echo ''; } ?>

</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">
    
    <!-- jQuery UI File Editor Tab Menu -->
<div id="bps-edittabs" class="bps-edittabs-class">
		<ul>
			<li><a href="#bps-edittabs-1"><?php _e('secure.htaccess', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-edittabs-2"><?php _e('default.htaccess', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-edittabs-3"><?php _e('maintenance.htaccess', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-edittabs-4"><?php _e('wpadmin-secure.htaccess', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-edittabs-5"><?php _e('Your Current Root htaccess File', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-edittabs-6"><?php _e('Your Current wp-admin htaccess File', 'bulletproof-security'); ?></a></li>
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
<form name="template1" id="template1" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_1'); ?>
    <div>
    <textarea cols="135" rows="27" name="newcontent1" id="newcontent1" tabindex="1"><?php echo get_secure_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($secure_htaccess_file) ?>" />
	<input type="hidden" name="scrollto1" id="scrollto1" value="<?php echo $scrollto1; ?>" />
    <p class="submit">
	<input type="submit" name="submit1" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
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
<form name="template2" id="template2" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_2'); ?>
	<div>
    <textarea cols="135" rows="27" name="newcontent2" id="newcontent2" tabindex="2"><?php echo get_default_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($default_htaccess_file) ?>" />
	<input type="hidden" name="scrollto2" id="scrollto2" value="<?php echo $scrollto2; ?>" />
    <p class="submit">
	<input type="submit" name="submit2" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
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
<form name="template3" id="template3" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_3'); ?>
	<div>
    <textarea cols="135" rows="27" name="newcontent3" id="newcontent3" tabindex="3"><?php echo get_maintenance_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($maintenance_htaccess_file) ?>" />
	<input type="hidden" name="scrollto3" id="scrollto3" value="<?php echo $scrollto3; ?>" />
    <p class="submit">
	<input type="submit" name="submit3" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
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
<form name="template4" id="template4" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_4'); ?>
	<div>
    <textarea cols="135" rows="27" name="newcontent4" id="newcontent4" tabindex="4"><?php echo get_wpadmin_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto4" id="scrollto4" value="<?php echo $scrollto4; ?>" />
    <p class="submit">
	<input type="submit" name="submit4" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
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
<form name="template5" id="template5" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_5'); ?>
	<div>
    <textarea cols="135" rows="27" name="newcontent5" id="newcontent5" tabindex="5"><?php echo get_root_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($root_htaccess_file) ?>" />
	<input type="hidden" name="scrollto5" id="scrollto5" value="<?php echo $scrollto5; ?>" />
    <p class="submit">
    <?php if (@bpsStatusRHE($perms) == '404') { ?>
	<input type="submit" name="submit5" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" class="bps-blue-button" onClick="return confirm('<?php $text = __('YOUR ROOT HTACCESS FILE IS LOCKED.', 'bulletproof-security').'\n\n'.__('YOUR FILE EDITS / CHANGES CANNOT BE SAVED.', 'bulletproof-security').'\n\n'.__('Click Cancel, copy the file editing changes you made to save them and then click the Unlock .htaccess File button to unlock your Root .htaccess file. After your Root .htaccess file is unlocked paste your file editing changes back into your Root .htaccess file and click this Update File button again to save your file edits / changes.', 'bulletproof-security'); echo $text; ?>')" />
	<?php } else { ?>
	<input type="submit" name="submit5" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
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
<form name="template6" id="template6" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_6'); ?>
	<div>
    <textarea cols="135" rows="27" name="newcontent6" id="newcontent6" tabindex="6"><?php echo get_current_wpadmin_htaccess_file(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($current_wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto6" id="scrollto6" value="<?php echo $scrollto6; ?>" />
    <p class="submit">
	<input type="submit" name="submit6" class="bps-blue-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
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
<?php echo '<div class="bps-file_download_title"><strong>'; _e('File Downloads', 'bulletproof-security'); echo '</strong></div>'; ?>

<form name="bps-enable-download" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_enable_download'); ?>
<input type="hidden" name="filename" value="bps-enable-download-edit" />
<p class="submit">
<input type="submit" name="bps-enable-download" class="bps-blue-button" value="<?php esc_attr_e('Enable Master File Downloading', 'bulletproof-security') ?>" /></p>
</form>

<div id="bps-enable_bu_file_dl_button">
<form name="bps-enable-download-backup" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_enable_download-backup'); ?>
<input type="hidden" name="filename" value="bps-enable-download-edit-backup" />
<p class="submit">
<input type="submit" name="bps-enable-download-backup" class="bps-blue-button" value="<?php esc_attr_e('Enable Backed Up File Downloading', 'bulletproof-security') ?>" /></p>
</form>
</div>
<div id="bps-download_buttons_table">
<?php  echo '<p class="bps-download_titles">'; _e('BPS Master Files', 'bulletproof-security'); echo '</p>';
	
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

<form name="bps-master-secure-download" action="<?php echo plugins_url('/bulletproof-security/admin/htaccess/secure.htaccess'); ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_secure'); ?>
<input type="submit" name="bps-master-secure-download" class="bps-blue-button" value="<?php esc_attr_e('secure.htaccess', 'bulletproof-security') ?>" onClick="return confirm('<?php _e('Click OK to Download the file now or click Cancel to cancel the download.', 'bulletproof-security'); ?>')" /></p>
</form>

<form name="bps-master-default-download" action="<?php echo plugins_url('/bulletproof-security/admin/htaccess/default.htaccess'); ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_default'); ?>
<input type="hidden" name="filename" value="bps-default-download" />
<input type="submit" name="bps-master-default-download" class="bps-blue-button" value="<?php esc_attr_e('default.htaccess', 'bulletproof-security') ?>" /></p>
</form>

<form name="bps-master-maintenance-download" action="<?php echo plugins_url('/bulletproof-security/admin/htaccess/maintenance.htaccess'); ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_maintenance'); ?>
<input type="hidden" name="filename" value="bps-maintenance-download" />
<input type="submit" name="bps-master-maintenance-download" class="bps-blue-button" value="<?php esc_attr_e('maintenance.htaccess', 'bulletproof-security') ?>" /></p>
</form>

<form name="bps-master-wpadmin-secure-download" action="<?php echo plugins_url('/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess'); ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_wpadmin-secure'); ?>
<input type="hidden" name="filename" value="bps-wpadmin-secure-download" />
<input type="submit" name="bps-master-wpadmin-secure-download" class="bps-blue-button" value="<?php esc_attr_e('wpadmin-secure.htaccess', 'bulletproof-security') ?>" /></p>
</form>

	<?php  echo '<p class="bps-download_titles">'; _e('Backed Up htaccess Files', 'bulletproof-security'); echo '</p>'; ?>
    
<form name="bps-master-root-backup-htaccess-download" action="<?php echo content_url('/bps-backup/root.htaccess'); ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_root-backup-htaccess'); ?>
<input type="hidden" name="filename" value="bps-root-backup-htaccess-download" />
<input type="submit" name="bps-master-root-backup-htaccess-download" class="bps-blue-button" value="<?php esc_attr_e('root.htaccess Backup File', 'bulletproof-security') ?>" /></p>
</form>

<form name="bps-master-wpadmin-backup-htaccess-download" action="<?php echo content_url('/bps-backup/wpadmin.htaccess'); ?>" method="post">
<?php wp_nonce_field('bulletproof_security_download_wpadmin-backup-htaccess'); ?>
<input type="hidden" name="filename" value="bps-wpadmin-backup-htaccess-download" />
<input type="submit" name="bps-master-wpadmin-backup-htaccess-download" class="bps-blue-button" value="<?php esc_attr_e('wpadmin.htaccess Backup File', 'bulletproof-security') ?>" /></p>
</form>
</div>    
</td>
  </tr>
</table>
<?php } ?>
</div>

<div id="bps-tabs-7" class="bps-tab-page">
<h2><?php _e('Custom Code', 'bulletproof-security'); ?></h2>
<div id="bpsCustomCode" style="border-top:1px solid #999999;">

<h3><?php _e('Add Custom htaccess Code To Root and wp-admin htaccess Files', 'bulletproof-security'); ?>  <button id="bps-open-modal16" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content16" title="<?php _e('Custom Code', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('IMPORTANT!!! Custom Code Info IMPORTANT!!!','bulletproof-security').'</strong><br><br><strong>'.__('Add ONLY valid htaccess code into these text boxes. If you want to add regular text then you need to add a pound sign # in front of the text to comment it out. If you do not do this then the next time you use AutoMagic and activate BulletProof Mode for your Root folder your website WILL crash.','bulletproof-security').'</strong><br><br>'.__('Your Custom Code is saved permanently to your WordPress Database until you delete it and will not be removed or deleted when you upgrade BPS.','bulletproof-security').'<br><br><strong>'.__('Root htaccess File Custom Code Setup Steps','bulletproof-security').'</strong><br>'.__('1. Enter your custom code in the appropriate Custom Code text box.', 'bulletproof-security').'<br>'.__('2. Click the Save Root Custom Code button to save your custom code.', 'bulletproof-security').'<br>'.__('3. Go to the Security Modes page and click the AutoMagic buttons.', 'bulletproof-security').'<br>'.__('4. Activate BulletProof Mode for your Root folder.','bulletproof-security').'<br><br><strong>'.__('CUSTOM CODE TOP: Add php.ini handler code and / or miscellaneous custom code here','bulletproof-security').'</strong><br>'.__('The CUSTOM CODE TOP text area should really ONLY be used for php.ini handler code if BPS is unable to detect your Web Host. You can add your php.ini handler code and miscellaneous custom htaccess code in the CUSTOM CODE TOP text area together, but it is recommended that you ONLY use this text area for your php.ini handler code if your website requires php.ini handler code in your Root htaccess file. BPS Pro ONLY: If BPS Pro is unable to detect your Web Host when you have a Private Name Server, you are using CloudFlare, you are using Pipe DNS or some other service that is blocking your true Web Host Name Servers and DNS information. The CUSTOM CODE BOTTOM text area should be used for miscellaneous custom htaccess code.','bulletproof-security').'<br><br><strong>'.__('CUSTOM CODE PLUGIN FIXES: Add ONLY personal plugin fixes code here','bulletproof-security').'</strong><br>'.__('This text area is for plugin fixes that are specific to your website. BPS already has some plugin fixes included in the Root htaccess file. Adding additional plugin fixes for your personal plugins on your website goes in this text area. For each plugin fix that you add above RewriteRule . - [S=12] you will need to increase the S= number by one. For Example: if you added 2 plugin fixes above the Adminer plugin fix they would be htaccess Skip rules #13 and #14 - RewriteRule . - [S=13] and RewriteRule . - [S=14]. If you added a third Skip rule above #13 and #14 it would be Skip rule #15 - RewriteRule . - [S=15].','bulletproof-security').'<br><br><strong>'.__('CUSTOM CODE BOTTOM: Add miscellaneous custom htaccess code here ','bulletproof-security').'</strong><br>'.__('You can save any miscellaneous custom htaccess code here as long as it is valid htaccess code or if it is just plain text then you will need to comment it out with a pound sign # in front of the text.','bulletproof-security').'<br><br><strong>'.__('wp-admin htaccess File Custom Code','bulletproof-security').'</strong><br>'.__('The wp-admin htaccess File Custom Code feature works differently then the Root htaccess Custom Code feature. The wp-admin htaccess file does not use AutoMagic and your Custom Code is written directly to your wp-admin htaccess file when you Activate BulletProof Mode for your wp-admin folder.','bulletproof-security').'<br><br><strong>'.__('wp-admin htaccess File Custom Code Steps','bulletproof-security').'</strong><br>'.__('1. Enter your custom code in the appropriate Custom Code text box.', 'bulletproof-security').'<br>'.__('2. Click the Save wp-admin Custom Code button to save your custom code.', 'bulletproof-security').'<br>'.__('3. Go to the Security Modes page and activate BulletProof Mode for your wp-admin folder.', 'bulletproof-security').'<br><br><strong>'.__('CUSTOM CODE WPADMIN TOP: Add miscellaneous custom code here','bulletproof-security').'</strong><br>'.__('You can save any miscellaneous custom htaccess code here as long as it is valid htaccess code or if it is just plain text then you will need to comment it out with a pound sign # in front of the text.','bulletproof-security').'<br><br><strong>'.__('CUSTOM CODE WPADMIN PLUGIN FIXES: Add ONLY WPADMIN personal plugin fixes code here','bulletproof-security').'</strong><br>'.__('There are only a couple of plugins that require a skip rule in the wp-admin htaccess file. This text area is for plugin fixes that may require a wp-admin htaccess skip rule. There is currently one skip rule in the wp-admin htaccess file - the WP Press This skip rule - RewriteRule . - [S=1]. For each plugin fix / skip rule that you add above RewriteRule . - [S=1] you will need to increase the S= number by one. For Example: if you added 2 wp-admin plugin fixes above the - WP Press This skip rule - they would be htaccess Skip rules #2 and #3 - RewriteRule . - [S=2] and RewriteRule . - [S=3]. If you added a third Skip rule above #2 and #3 it would be Skip rule #4 - RewriteRule . - [S=4].','bulletproof-security'); echo $text; ?></p>
</div>

<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<?php $scrolltoCCode = isset($_REQUEST['scrolltoCCode']) ? (int) $_REQUEST['scrolltoCCode'] : 0; 
	$scrolltoCCodeWPA = isset($_REQUEST['scrolltoCCodeWPA']) ? (int) $_REQUEST['scrolltoCCodeWPA'] : 0;
?>
        
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" class="bps-table_cell_help">
     <h3 style="margin-top:-10px;"><?php _e('Root .htaccess File Custom Code', 'bulletproof-security'); ?></h3>
<form name="bpsCustomCodeForm" action="options.php" method="post">
	<?php settings_fields('bulletproof_security_options_customcode'); ?>
	<?php $options = get_option('bulletproof_security_options_customcode'); ?>
<div><strong><label for="bps-CCode"><?php _e('CUSTOM CODE TOP: Add php.ini handler code and / or miscellaneous custom code here', 'bulletproof-security'); ?> </label></strong><br />
<strong><label for="bps-CCode"><?php $text = '<font color="blue">'.__('ONLY add valid .htaccess code below or text commented out with a pound sign #', 'bulletproof-security').'</font>'; echo $text; ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode[bps_customcode_one]" tabindex="1"><?php echo $options['bps_customcode_one']; ?></textarea><br /><br />
    <strong><label for="bps-CCode"><?php _e('CUSTOM CODE PLUGIN FIXES: Add ONLY personal plugin fixes code here', 'bulletproof-security'); ?> </label></strong><br />
 <strong><label for="bps-CCode"><?php $text = '<font color="blue">'.__('ONLY add valid .htaccess code below or text commented out with a pound sign #', 'bulletproof-security').'</font>'; echo $text; ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode[bps_customcode_two]" tabindex="2"><?php echo $options['bps_customcode_two']; ?></textarea><br /><br />
    <strong><label for="bps-CCode"><?php _e('CUSTOM CODE BOTTOM: Add miscellaneous custom .htaccess code here', 'bulletproof-security'); ?> </label></strong><br />
 <strong><label for="bps-CCode"><?php $text = '<font color="blue">'.__('ONLY add valid .htaccess code below or text commented out with a pound sign #', 'bulletproof-security').'</font>'; echo $text; ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode[bps_customcode_three]" tabindex="3"><?php echo $options['bps_customcode_three']; ?></textarea>
    <input type="hidden" name="scrolltoCCode" value="<?php echo $scrolltoCCode; ?>" />
    <p class="submit">
	<input type="submit" name="bps_customcode_submit" class="bps-blue-button" value="<?php esc_attr_e('Save Custom Code', 'bulletproof-security') ?>" /></p>
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
    <h3><?php _e('wp-admin .htaccess File Custom Code', 'bulletproof-security'); ?></h3>
    <form name="bpsCustomCodeFormWPA" action="options.php" method="post">
	<?php settings_fields('bulletproof_security_options_customcode_WPA'); ?>
	<?php $options = get_option('bulletproof_security_options_customcode_WPA'); ?>
<div><strong><label for="bps-CCode"><?php _e('CUSTOM CODE WPADMIN TOP: Add miscellaneous custom code here', 'bulletproof-security'); ?> </label></strong><br />
<strong><label for="bps-CCode"><?php $text = '<font color="blue">'.__('ONLY add valid .htaccess code below or text commented out with a pound sign #', 'bulletproof-security').'</font>'; echo $text; ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode_WPA[bps_customcode_one_wpa]" tabindex="4"><?php echo $options['bps_customcode_one_wpa']; ?></textarea><br /><br />
   <strong><label for="bps-CCode"><?php _e('CUSTOM CODE WPADMIN PLUGIN FIXES: Add ONLY WPADMIN personal plugin fixes code here', 'bulletproof-security'); ?> </label></strong><br />
 <strong><label for="bps-CCode"><?php $text = '<font color="blue">'.__('ONLY add valid .htaccess code below or text commented out with a pound sign #', 'bulletproof-security').'</font>'; echo $text; ?> </label></strong><br />
    <textarea cols="100" rows="15" name="bulletproof_security_options_customcode_WPA[bps_customcode_two_wpa]" tabindex="5"><?php echo $options['bps_customcode_two_wpa']; ?></textarea>
    <input type="hidden" name="scrolltoCCodeWPA" value="<?php echo $scrolltoCCodeWPA; ?>" />
    <p class="submit">
	<input type="submit" name="bps_customcode_submit_wpa" class="bps-blue-button" value="<?php esc_attr_e('Save wp-admin Custom Code', 'bulletproof-security') ?>" /></p>
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

<div id="bps-tabs-8" class="bps-tab-page">
<h2><?php _e('BulletProof Security Maintenance Mode', 'bulletproof-security'); ?></h2>

<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    <div id="bps-maintenance_form_table">
<h3><?php _e('Website Maintenance Mode Settings', 'bulletproof-security'); ?></h3>
<h3><?php echo '<font color="red"><strong>'; _e('CAUTION: ', 'bulletproof-security'); echo '</strong></font>'; ?>  <button id="bps-open-modal17" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content17" title="<?php _e('Website Maintenance Mode', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br><strong>'.__('Your Maintenance Mode Form data is saved to the WordPress Database and will remain permanently until you delete it. When you upgrade BPS your form data will still be saved in your database.','bulletproof-security').'</strong><br><br>'.__('If you are unable to log back into your website because you are also seeing the Maintenance Mode page then you only need to use FTP or use your Web Host Control Panel and delete the .htaccess file that is in your root website folder to be able to log back into your website.', 'bulletproof-security').'<br><br><strong>'.__('Maintenance Mode Activation Steps','bulletproof-security').'</strong><br><br><strong>'.__('Filling In The Maintenance Mode Settings Form','bulletproof-security').'</strong><br><strong>'.__('1. Fill out the Website Maintenance Mode Form','bulletproof-security').'</strong><br> -- '.__('You can copy and paste the example Background Image URL into the Background Image text field if you want to use the background image file that comes with BPS. If you have another background image file that you want to use then just name it with the same name as the example image file and copy it to the /bulletproof-security folder. If you do not want a background image then leave this text field blank. The background color will be white. If you want to customize the Website Under Maintenance template then download this file located in this folder /bulletproof-security/admin/htaccess/bp-maintenance.php.','bulletproof-security').'<br><strong>'.__('2. Click the Save Form Settings button to save your form data to your database.','bulletproof-security').'</strong><br><strong>'.__('3. Click the Create Form button to create your Website Under Maintenance form.','bulletproof-security').'</strong><br><strong>'.__('4. Click the Preview Form button to preview your Website Under Maintenance page.','bulletproof-security').'</strong><br> -- '.__('If you see a 404 or 403 Forbidden message in the popup preview window refresh the popup preview window or just close the popup window and click the Preview button again.','bulletproof-security').'<br> -- '.__('You can use the Preview button at any time to preview how your site will be displayed to everyone else except you when your website is in Maintenance Mode.','bulletproof-security').'<br><br><strong>'.__('Create Your Maintenance Mode .htaccess File','bulletproof-security').'</strong><br>'.__('After you have finished previewing your Website Under Maintenance page, click the Create htaccess File button. This creates your Maintenance Mode .htaccess file for your website. Your current Public IP address and correct RewriteBase and RewriteRule are included when this new Maintenance Mode .htaccess file is created.','bulletproof-security').'<br><br><strong>'.__('Activate Website Under Maintenance Mode','bulletproof-security').'</strong><br>'.__('Select the Maintenance Mode radio button and click the Activate Maintenance Mode button. Your website is now in Maintenance Mode. Everyone else will see your Website Under Maintenance page while you can still view and work on your site as you normally would.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="bps-maintenance-values" action="options.php" method="post">
<?php settings_fields('bulletproof_security_options_maint'); ?>
			<?php $options = get_option('bulletproof_security_options_maint'); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="bps-site-title"><?php _e('Site Title:', 'bulletproof-security') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-site-title]" type="text" value="<?php echo $options['bps-site-title']; ?>" class="regular-text" /><span class="description"><?php _e('Add Your Page Title', 'bulletproof-security') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-message-1"><?php _e('Message 1:', 'bulletproof-security') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-message-1]" type="text" value="<?php echo $options['bps-message-1']; ?>" class="regular-text" /><span class="description"><?php _e('Add Your Message', 'bulletproof-security') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-message-2"><?php _e('Message 2:', 'bulletproof-security') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-message-2]" type="text" value="<?php echo $options['bps-message-2']; ?>" class="regular-text" /><span class="description"><?php _e('Add Another Message or Not', 'bulletproof-security') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-retry-after"><?php _e('Retry-After:', 'bulletproof-security') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-retry-after]" type="text" value="<?php echo $options['bps-retry-after']; ?>" class="regular-text" /><span class="description"><?php _e('259200', 'bulletproof-security') ?></span></td>
</tr>
<tr valign="top">
<th scope="row"><label for="bps-background-image"><?php _e('Background Image', 'bulletproof-security') ?></label></th>
<td><input name="bulletproof_security_options_maint[bps-background-image]" type="text" value="<?php echo $options['bps-background-image']; ?>" class="regular-text" /><span class="description"><?php echo plugins_url('/bulletproof-security/abstract-blue-bg.png'); ?></span></td>
</tr>
</table>
<p class="submit">
<input type="submit" name="bps-maintenance-values_submit" class="bps-blue-button" value="<?php esc_attr_e('Save Form Settings', 'bulletproof-security') ?>" />
</p>
</form>


<form name="bps-maintenance-create-values" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-8" method="post">
<?php wp_nonce_field('bulletproof_security_create_values_form'); ?>
<input type="hidden" name="mmfilename" value="bps-maintenance-create-valuesH" />
<p class="submit">
<input type="submit" name="bps-maintenance-create-values_submit" class="bps-blue-button" value="<?php esc_attr_e('Create Form', 'bulletproof-security') ?>" /></p>
</form>

<!-- this is the Enable Download form reused for maintenance mode Preview -->
<form name="bps-enable-download" method="POST" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-8" target="" onSubmit="window.open('<?php echo plugins_url('/bulletproof-security/admin/htaccess/bp-maintenance.php'); ?>','','scrollbars=yes,menubar=yes,width=800,height=600,resizable=yes,status=yes,toolbar=yes')">
<?php wp_nonce_field('bulletproof_security_enable_download'); ?>
<input type="hidden" name="filename" value="bps-enable-download-edit" />
<p class="submit">
<input type="submit" name="bps-enable-download" class="bps-blue-button" value="<?php esc_attr_e('Preview Form', 'bulletproof-security') ?>" /></p>
</form>
</div>

<h3><?php _e('Activate Website Under Maintenance Mode', 'bulletproof-security'); ?></h3>
<h3><?php echo '<font color="red"><strong>'; _e('CAUTION: ', 'bulletproof-security'); echo '</strong></font>'; ?>  <button id="bps-open-modal18" class="bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
<div id="bps-modal-content18" title="<?php _e('Activate Maintenance Mode', 'bulletproof-security'); ?>">
	<p><?php $text = '<strong>'.__('This Read Me Help window is draggable and resizable','bulletproof-security').'</strong><br><br>'.__('If you are unable to log back into your website because you are also seeing the Maintenance Mode page then you only need to use FTP or use your Web Host Control Panel and delete the .htaccess file that is in your root website folder to be able to log back into your website.', 'bulletproof-security').'<br><br><strong>'.__('Activating Maintenance Mode will automatically unlock your Root .htaccess file. Be sure to lock your Root .htaccess file after you have put your site back in BulletProof Mode.','bulletproof-security').'</strong><br><br><strong>'.__('You must click the Create htaccess File button FIRST to create your Maintenance Mode htaccess file before activating Maintenance Mode if you want to be able to continue working on your website while everyone else sees the Website Under Maintenance page','bulletproof-security').'</strong><br>'.__('After you have created your Maintenance Mode .htaccess file - Select the Maintenance Mode radio button and click Activate.','bulletproof-security').'<br><br><strong>'.__('You might see BPS error messages displayed when you put your site in Maintenance Mode. You can disregard these error messages. When you put your site back into BulletProof Mode these error messages will automatically go away.','bulletproof-security').'</strong><br><br><strong>'.__('To switch out of or exit Maintenance Mode just activate BulletProof Security Mode for your Root folder on the Security Modes page.','bulletproof-security').'</strong><br><br>'.__('To view the Maintenance Mode page that your website visitors are seeing click the Preview Form button.','bulletproof-security').'<br><br>'.__('When you activate Maintenance Mode your website will be put in HTTP 503 Service Temporarily Unavailable status and display a Website Under Maintenance page to everyone except you. Your current Public IP address was automatically added to the Maintenance Mode file as well as the correct .htaccess RewriteRule and RewriteBase for your website when you clicked the Create File button.','bulletproof-security').'<br><br>'.__('To manually add additional IP addresses that are allowed to view your website normally use the BPS File Editor to add them. To view your current Public IP address click on the System Info tab menu.','bulletproof-security').'<br><br><strong>'.__('Your current Public IP address is also displayed on the Website Under Maintenance page itself.','bulletproof-security').'</strong><br><br>'.__('Your SERPs (website or web page ranking) will not be affected by putting your website in Maintenance Mode for several days for existing websites. To manually add additional IP addresses that can view your website you would add them using the BPS File Editor.','bulletproof-security').'<br><br>'.__('If you are unable to log back into your WordPress Dashboard and are also seeing the Website Under Maintenance page then you will need to FTP to your website and either delete the .htaccess file in your website root folder or download the .htaccess file - add your correct current Public IP address and upload it back to your website.','bulletproof-security'); echo $text; ?></p>
</div>

<form name="bps-auto-write-maint" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-8" method="post">
<?php wp_nonce_field('bulletproof_security_auto_write_maint'); ?>
<input type="hidden" name="filename" value="bps-auto-write-maint_write" />
<p class="submit">
<input type="submit" name="bps-auto-write-maint" class="bps-blue-button" value="<?php esc_attr_e('Create htaccess File', 'bulletproof-security') ?>" /></p>
</form>

<form name="BulletProof-Maintenance" action="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-8" method="post">
<?php wp_nonce_field('bulletproof_security_maintenance_copy'); ?>
<table class="form-table">
   <tr>
	<th><label><input name="selection15" type="radio" value="bpmaintenance" class="tog" <?php checked('', $bpmaintenance); ?> />
	<?php _e('Maintenance Mode', 'bulletproof-security'); ?></label></th>
	<td class="url-path"><?php $text = '<font color="green">'.__('Click the Create htaccess File button first to create your Maintenance Mode .htaccess file. To switch out of or exit Maintenance Mode just activate BulletProof Security Mode for your Root Folder.', 'bulletproof-security').'</font><strong>'.__(' Read the ', 'bulletproof-security').'<font color="red">'.__('CAUTION: ', 'bulletproof-security').'</font>'.__('Read Me button for more detailed information.', 'bulletproof-security').'</strong>'; echo $text; ?></td>
   </tr>
</table>
<p class="submit">
<input type="submit" name="submit15" class="bps-blue-button" value="<?php esc_attr_e('Activate Maintenance Mode', 'bulletproof-security') ?>" />
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

<div id="bps-tabs-9">
<h2><?php _e('BulletProof Security Help &amp; FAQ', 'bulletproof-security'); ?></h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
   <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/category/bulletproof-security-contributors/" target="_blank"><?php _e('Contributors Page', 'bulletproof-security'); ?></a></td>
    <td width="50%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2304/wordpress-tips-tricks-fixes/permalinks-wordpress-custom-permalinks-wordpress-best-wordpress-permalinks-structure/" target="_blank"><?php _e('WP Permalinks - Custom Permalink Structure Help Info', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://forum.ait-pro.com/forums/forum/bulletproof-security-free/" target="_blank"><?php _e('BulletProof Security Forum', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2239/bulletproof-security-plugin-support/adding-a-custom-403-forbidden-page-htaccess-403-errordocument-directive-examples/" target="_blank"><?php _e('Adding a Custom 403 Forbidden Page For Your Website', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2252/bulletproof-security-plugin-support/checking-plugin-compatibility-with-bps-plugin-testing-to-do-list/" target="_blank"><?php _e('Plugin Compatibility Testing - Recent New Permanent Plugin Fixes', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/3898/bulletproof-security-pro/custom-code-help-and-faq-how-to-use-custom-code-adding-custom-code-to-automagic/" target="_blank"><?php _e('Custom Code Feature Help Info', 'bulletproof-security'); ?></a></td>
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

<div id="bps-tabs-10">
<h2><?php _e('Whats New in ~ ', 'bulletproof-security'); ?><?php echo $bps_version; ?></h2>
<h3><?php _e('The Whats New page will list new changes that were made in each new version release of BulletProof Security', 'bulletproof-security'); ?></h3>
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
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
 <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('Security Logging / HTTP Error Logging - Log 400, 403 and 404 Errors:', 'bulletproof-security'); ?></strong><br /><?php _e('Your Security Log file is a plain text static file and not a dynamic file or dynamic display to keep your website resource usage at a bare minimum and keep your website performance at a maximum.', 'bulletproof-security'); ?></td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
 <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('Security Logging / HTTP Error Logging Dashboard Alert:', 'bulletproof-security'); ?></strong><br /><?php _e('When your log file gets larger than 500KB you will see a WP Dashboard Alert displayed. Copy and paste the Security Log file contents into a Notepad text file on your computer and save it. Then click the Delete Log button to delete the contents of this Log file.', 'bulletproof-security'); ?></td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
 <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('NEW root .htacess file code automatically created/modified on upgrade:', 'bulletproof-security'); ?></strong><br /><?php _e('The new ErrorDocument Error log htaccess code is automatically added to your Root .htaccess file when you upgrade to .47.8. The FORBID EMPTY REFFERER SPAMBOTS .htaccess code is automatically deleted from your Root .htaccess file. This code has been problematic on a number of websites and this htaccess code is not really effective against blocking empty referrer spambots anymore.', 'bulletproof-security'); ?></td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
 <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('Additional System Info Check Added: cURL Extension:', 'bulletproof-security'); ?></strong><br /><?php _e('You will see an additional System Information check on the System Info tab page that checks whether or not the cURL Extension is loaded on your website.', 'bulletproof-security'); ?></td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
 <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><strong><?php _e('Coding Improvements & Enhancements:', 'bulletproof-security'); ?></strong><br /><?php _e('Of course, but why not mention it anyway.', 'bulletproof-security'); ?></td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
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

<div id="bps-tabs-11" class="bps-tab-page">
<h2><?php _e('My Notes', 'bulletproof-security'); ?></h2>
<div id="bpsMyNotesborder" style="border-top:1px solid #999999;">
<h3><?php _e('Save any personal notes or htaccess code to your WordPress Database', 'bulletproof-security'); ?></h3>
</div>
<?php if (!current_user_can('manage_options')) { _e('Permission Denied', 'bulletproof-security'); } else { ?>
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
	<input type="submit" name="myNotes_submit" class="bps-blue-button" value="<?php esc_attr_e('Save My Notes', 'bulletproof-security') ?>" /></p>
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

<div id="bps-tabs-12">
<h2><?php _e('BulletProof Security Pro Feature Highlights', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="62%" class="bps-table_cell_help"><a href="http://www.ait-pro.com/aitpro-blog/2841/bulletproof-security-pro/bulletproof-security-pro-overview-video-tutorial/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro 10 Minute Installation & Setup Video Tutorial', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://affiliates.ait-pro.com/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro Affiliate Program', 'bulletproof-security'); ?></a>
    </td>
    <!-- if a new link is added you need to increase the rowspan number for each new row / link -->
    <td width="38%" rowspan="12" valign="top" class="bps-table_cell_help">
    <a href="http://www.ait-pro.com/aitpro-blog/3395/bulletproof-security-pro/bps-free-vs-bps-pro-feature-comparison/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro Vs BPS Free Feature Comparison', 'bulletproof-security'); ?></a><br /><br />
	<a href="http://www.ait-pro.com/aitpro-blog/4683/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.5', 'bulletproof-security'); ?></a><br /><br />	
    <a href="http://www.ait-pro.com/aitpro-blog/4653/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.4', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4628/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.3', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4563/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.2', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4442/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.9', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4197/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.8', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4144/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.7', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/4029/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.6', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3845/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.5', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3732/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.4', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3605/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-3" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.3', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3529/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.2', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.1', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1', 'bulletproof-security'); ?></a><br /><br />
    <a href="http://www.ait-pro.com/aitpro-blog/2835/bulletproof-security-pro/bulletproof-security-pro-features/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.0', 'bulletproof-security'); ?></a>
    </td>
  </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('10 Minute Installation and Setup: ', 'bulletproof-security'); echo '</strong>'; _e('BPS Pro first time installations and setup take only 10 minutes. Click the BPS Pro 10 Minute Installation & Setup Video Tutorial link above.', 'bulletproof-security'); ?></td>
    </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('One Click Upgrades: ', 'bulletproof-security'); echo '</strong>'; _e('BPS Pro Plugin upgrade notifications are displayed in your WordPress Dashboard exactly the same way as all other WordPress plugins. All BPS Pro files are automatically updated during the upgrade and no additional setup steps are required when upgrading.', 'bulletproof-security'); ?></td>
    </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('AutoRestore & Quarantine: ', 'bulletproof-security'); echo '</strong>'; _e('ARQ is a real-time file monitor that automatically AutoRestores and/or Quarantines files. ARQ utilizes countermeasure website security that has the capability to protect all of your website files, both WordPress and non-WordPress files, even if your Web Host Server is hacked. Quarantine Options: Restore File, Delete File and View File. Quarantine Logging and Email alerts.', 'bulletproof-security'); ?></td>
    </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('Plugins Folder Firewall: ', 'bulletproof-security'); echo '</strong>'; _e('The Plugins Folder Firewall prevents/blocks/forbids Remote Access to the plugins folder from external sources (remote script execution, hacker recon, remote scanning, remote accessibility, etc.) and only allows internal access to the plugins folder based on this criteria: Domain name, Server IP Address and Public IP / Your Computer IP Address.', 'bulletproof-security'); ?></td>
    </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('Uploads Folder Anti-Exploit Guard: ', 'bulletproof-security'); echo '</strong>'; _e('The Uploads Folder Anti-Exploit Guard allows ONLY safe image files with valid image file extensions such as jpg, gif, png, etc. to be accessed, opened or viewed from the uploads folder. The Uploads Anti-Exploit Guard prevents/blocks/forbids files by file extension names in the uploads folder from being accessed, opened, viewed, processed or executed.', 'bulletproof-security'); ?></td>
    </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php  echo '<strong>'; _e('Security / HTTP Error Logging: ', 'bulletproof-security'); echo '</strong>'; _e('BPS Pro Logs HTTP Errors and hacking attempts against your website. IP address, Host name, Request Method, Referering link, the file name or requested resource, the user agent and the query string are logged.', 'bulletproof-security'); ?></td>
    </tr>
 <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php  echo '<strong>'; _e('S-Monitor Email Alerting & Log File Options: ', 'bulletproof-security'); echo '</strong>'; _e('Choose whether or not to have email alerts sent when Log files log events. Choose to either automatically Zip and Email Log files to you when they reach the maximum size limit option that you choose or just automatically delete log files when they reach the the maximum size limit option that you choose.', 'bulletproof-security'); ?></td>
    </tr>
  <tr>
     <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('F-Lock: ', 'bulletproof-security'); echo '</strong>'; _e('Lock and Unlock WordPress Mission Critical files from within your WordPress Dashboard.', 'bulletproof-security'); ?></td>
    </tr>  
  <tr>
     <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('Custom php.ini / ini_set Options: ', 'bulletproof-security'); echo '</strong>'; _e('Quickly create a custom php.ini file for your website or use ini_set Options to increase security and performance with just a few clicks. Additional P-Security Features: All-purpose File Manager, All-purpose File Editor, Protected PHP Error Log, PHP Error Alerts, Secure phpinfo Viewer...', 'bulletproof-security'); ?></td>
    </tr>
    <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('Advanced Real-Time Alerts: ', 'bulletproof-security'); echo '</strong>';  _e('BPS Pro checks and displays error, warning, notifications and alert messages in real time. You can choose how you want these messages displayed to you with S-Monitor Monitoring &amp; Alerting Options - Display in your WP Dashboard, BPS Pro pages only, Turned off, Email Alerts, Logging...', 'bulletproof-security'); ?></td>
    </tr>
  <tr>
    <td class="bps-table_cell_help" style="font-size:14px;"><?php echo '<strong>'; _e('Pro-Tools: ', 'bulletproof-security'); echo '</strong>'; _e('Pro-Tools is a set of versatile website tools: Online Base64 Decoder, Offline Base64 Decode/Encode, Mcrypt ~ Decrypt / Encrypt, Crypt Encryption, Scheduled Crons, String Finder, String Replacer / Remover, DB String Finder, DNS Finder...', 'bulletproof-security'); ?></td>
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

<div id="bps-tabs-13">
<h2><?php _e('Sucuri SiteCheck - Free website malware & blacklist scan', 'bulletproof-security'); ?></h2>
<h3><?php _e('BPS is designed to protect your website from being hacked. If your website was already hacked prior to installing BPS then BPS will not automatically clean it up. Sucuri offers hacked website cleanup services.', 'bulletproof-security'); ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    <div id="SucuriLogo" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/sucuri-logo.png'); ?>" style="float:left; padding:0px 10px 0px 0px; margin:0px;" /><h3><?php echo '<em>'.'"'.'...'; _e('the sheer nature of malware makes it very challenging to give you 100% certainty you will not get infected. The good news though is that we are doing everything in our power to ensure that 1 - you do not get infected, but 2 - if you do, we have the best solution to get you back on your feet.', 'bulletproof-security'); echo '"'.'</em><br> -- '; _e('Tony Perez, CFO Sucuri, LLC', 'bulletproof-security'); ?></h3><a href="http://sitecheck.sucuri.net/" target="_blank" title="Link opens in new browser window">Sucuri SiteCheck Scanner</a></div>    
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
        
<div id="bps-tabs-14">
<h2><?php _e('Website SEO', 'bulletproof-security'); ?></h2>
<h3><?php $text = __('Free, Premium Plugin and Theme testing, rating and review.', 'bulletproof-security').'<br><br><font color="blue"><strong>'.__('SPECIAL OFFER!!! ', 'bulletproof-security').'</strong></font>'.__('Search Engine Optimization (SEO) eBook for Beginners to Experienced Website Owners.', 'bulletproof-security'); echo $text; ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    <div id="SucuriLogo" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/themes-plugins-logo.png'); ?>" style="float:left; padding:0px 10px 0px 0px; margin:0px;" />
    <h3><?php echo '<em>'.'"'.'...'; _e('We Test, Review & Rate Premium, Free and Paid WordPress Themes, Templates & Plugins Daily. 440 themes and 158 plugins have been tested to date....', 'bulletproof-security'); echo '"'.'</em><br> -- '; _e('Reza Shadpay, founder of themesplugins.com', 'bulletproof-security'); ?></h3>
    <a href="http://www.themesplugins.com/" target="_blank" title="Link opens in new browser window">ThemesPlugins.com</a>
	<div id="ThemesPlugins" style="position:relative; top:0px; left:0px;">
    <h3><?php echo '<em>'.'"'.'...'; _e('SEO explained for Beginners to Experienced website owners. Simple and fully explained WhiteHat SEO techniques and methods that will get your website top Google page ranking positions.', 'bulletproof-security'); echo '"'.'</em><br> -- '; _e('Reza Shadpay, founder of themesplugins.com', 'bulletproof-security'); ?></h3>
    <a href="http://www.themesplugins.com/downloads/seo-ebook-wordpress-book-seo/" target="_blank" title="Link opens in new browser window">SEO eBook</a><br />    
    </div>    
    </div>

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

<div id="AITpro-link">BulletProof Security Plugin by <a href="http://www.ait-pro.com/aitpro-blog/" target="_blank" title="AITpro Website Security">AITpro Website Security</a>
</div>
</div>
</div>