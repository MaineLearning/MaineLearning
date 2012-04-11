<?php
// Direct calls to this file are Forbidden when core files are not present
if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

// Get BPS Version - Just for display purposes
function bpsWhatVersion() {
echo " ~ .46.9";
}

// BPS Master htaccess File Editing - file checks and get contents for editor
function get_secure_htaccess() {
	$secure_htaccess_file = WP_CONTENT_DIR .'/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
	if (file_exists($secure_htaccess_file)) {
	$bpsString = file_get_contents($secure_htaccess_file);
	echo $bpsString;
	} else {
	_e('The secure.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the secure.htaccess file exists and is named secure.htaccess.');
	}
}

function get_default_htaccess() {
	$default_htaccess_file = WP_CONTENT_DIR .'/plugins/bulletproof-security/admin/htaccess/default.htaccess';
	if (file_exists($default_htaccess_file)) {
	$bpsString = file_get_contents($default_htaccess_file);
	echo $bpsString;
	} else {
	_e('The default.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the default.htaccess file exists and is named default.htaccess.');
	}
}

function get_maintenance_htaccess() {
	$maintenance_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (file_exists($maintenance_htaccess_file)) {
	$bpsString = file_get_contents($maintenance_htaccess_file);
	echo $bpsString;
	} else {
	_e('The maintenance.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the maintenance.htaccess file exists and is named maintenance.htaccess.');
	}
}

function get_wpadmin_htaccess() {
	$wpadmin_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	if (file_exists($wpadmin_htaccess_file)) {
	$bpsString = file_get_contents($wpadmin_htaccess_file);
	echo $bpsString;
	} else {
	_e('The wpadmin-secure.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the wpadmin-secure.htaccess file exists and is named wpadmin-secure.htaccess.');
	}
}

// The current active root htaccess file - file check
function get_root_htaccess() {
	$root_htaccess_file = ABSPATH . '.htaccess';
	if (file_exists($root_htaccess_file)) {
	$bpsString = file_get_contents($root_htaccess_file);
	echo $bpsString;
	} else {
	_e('An .htaccess file was not found in your website root folder.');
	}
}

// The current active wp-admin htaccess file - file check
function get_current_wpadmin_htaccess_file() {
	$current_wpadmin_htaccess_file = ABSPATH . 'wp-admin/.htaccess';
	if (file_exists($current_wpadmin_htaccess_file)) {
	$bpsString = file_get_contents($current_wpadmin_htaccess_file);
	echo $bpsString;
	} else {
	_e('An .htaccess file was not found in your wp-admin folder.');
	}
}

// File write checks for editor
function secure_htaccess_file_check() {
	$secure_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
	if (!is_writable($secure_htaccess_file)) {
 		_e('<font color="red"><strong>Cannot write to the secure.htaccess file. Minimum file permission required is 600.</strong></font><br>');
	    } else {
	_e('');
}
}

// File write checks for editor
function default_htaccess_file_check() {
	$default_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';
	if (!is_writable($default_htaccess_file)) {
 		_e('<font color="red"><strong>Cannot write to the default.htaccess file. Minimum file permission required is 600.</strong></font><br>');
	    } else {
	_e('');
}
}
// File write checks for editor
function maintenance_htaccess_file_check() {
	$maintenance_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (!is_writable($maintenance_htaccess_file)) {
 		_e('<font color="red"><strong>Cannot write to the maintenance.htaccess file. Minimum file permission required is 600.</strong></font><br>');
	    } else {
	_e('');
}
}
// File write checks for editor
function wpadmin_htaccess_file_check() {
	$wpadmin_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	if (!is_writable($wpadmin_htaccess_file)) {
 		_e('<font color="red"><strong>Cannot write to the wpadmin-secure.htaccess file. Minimum file permission required is 600.</strong></font><br>');
	    } else {
	_e('');
}
}
// File write checks for editor
function root_htaccess_file_check() {
$root_htaccess_file = ABSPATH . '/.htaccess';
	if (!is_writable($root_htaccess_file)) {
 		_e('<font color="red"><strong>Cannot write to the root .htaccess file. Minimum file permission required is 600.</strong></font><br>');
	    } else {
	_e('');
}
}
// File write checks for editor
function current_wpadmin_htaccess_file_check() {
$current_wpadmin_htaccess_file = ABSPATH . '/wp-admin/.htaccess';
	if (!is_writable($current_wpadmin_htaccess_file)) {
 		_e('<font color="red"><strong>Cannot write to the wp-admin .htaccess file. Minimum file permission required is 600.</strong></font><br>');
	    } else {
	_e('');
}
}

// Get contents of Root .htaccess file from 3-45 - if "9" found in string position 17 - offset 16 - good - else bad
// Check for string BPSQSE
function root_htaccess_status() {
	$filename = ABSPATH . '.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 45);
	$check_stringBPSQSE = @file_get_contents($filename);
	$check_string = @strpos($section, "9", 16);
	if ( !file_exists($filename)) {
	_e('<font color="red">An .htaccess file was NOT found in your root folder</font><br><br>');
	_e('<font color="red">wp-config.php is NOT .htaccess protected by BPS</font><br><br>');
	} else {
	if (file_exists($filename)) {
	_e('<font color="green"><strong>The .htaccess file that is activated in your root folder is:</strong></font><br>');
		print($section);
		if ($check_string == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
		_e('<font color="green"><strong><br><br>&radic; wp-config.php is .htaccess protected by BPS<br>&radic; php.ini and php5.ini are .htaccess protected by BPS</strong></font><br><br>');
	} else {
	_e('<font color="red"><br><br><strong>Either a BPS .htaccess file was NOT found in your root folder or you have not activated BulletProof Mode for your Root folder yet, Default Mode is activated, Maintenance Mode is activated or the version of the BPS Pro htaccess file that you are using is not .46.9 or the BPS QUERY STRING EXPLOITS code does not exist in your root .htaccess file. Please read the Read Me button above.</strong></font><br><br>');
	_e('<font color="red"><strong>wp-config.php is NOT .htaccess protected by BPS</strong></font><br><br>');
}}}}

// Get contents of wp-admin .htaccess file from 3-45 - if "9" found in string position 17 - offset 16 - good - else bad
function wpadmin_htaccess_status() {
	$filename = ABSPATH . 'wp-admin/.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 45);
	$check_stringBPSQSE = @file_get_contents($filename);
	$check_string = @strpos($section, "9", 16);
	if ( !file_exists($filename)) {
	_e('<font color="red"><strong>An .htaccess file was NOT found in your wp-admin folder.<br>BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder.</strong></font><br>');
	} else {
		if ($check_string == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
	_e('<font color="green"><strong>The .htaccess file that is activated in your wp-admin folder is:</strong></font><br>');
		print($section);
	} else {
	_e('<font color="red"><strong><br><br>A valid BPS .htaccess file was NOT found in your wp-admin folder. Either you have not activated BulletProof Mode for your wp-admin folder yet or the version of the wp-admin htaccess file that you are using is not .46.9. BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder. Please read the Read Me button above.</strong></font><br>');
	}
	}
}
	
// Check if BPS Deny ALL htaccess file is activated for the BPS Master htaccess folder
function denyall_htaccess_status_master() {
	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green"><strong>&radic; Deny All protection activated for BPS Master /htaccess folder</strong></font><br>');
	} else {
    _e('<font color="red"><strong>Deny All protection NOT activated for BPS Master /htaccess folder</strong></font><br>');
	}
}
// Check if BPS Deny ALL htaccess file is activated for the /wp-content/bps-backup folder
function denyall_htaccess_status_backup() {
	$filename = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green"><strong>&radic; Deny All protection activated for /wp-content/bps-backup folder</strong></font><br><br>');
	} else {
    _e('<font color="red"><strong>Deny All protection NOT activated for /wp-content/bps-backup folder</strong></font><br><br>');
	}
}

// File and Folder Permission Checking - substr error is suppressed @ else fileperms error if file does not exist
function bps_check_perms($name,$path,$perm) {
	clearstatcache();
	$current_perms = @substr(sprintf(".%o.", fileperms($path)), -4);
	echo '<table style="width:100%;background-color:#fff;">';
	echo '<tr>';
    echo '<td style="background-color:#fff;padding:2px;width:35%;">' . $name . '</td>';
    echo '<td style="background-color:#fff;padding:2px;width:35%;">' . $path . '</td>';
    echo '<td style="background-color:#fff;padding:2px;width:15%;">' . $perm . '</td>';
    echo '<td style="background-color:#fff;padding:2px;width:15%;">' . $current_perms . '</td>';
    echo '</tr>';
	echo '</table>';
}
	
// General BulletProof Security File Status Checking
function general_bps_file_checks() {
	$dir='../';
	$filename = '.htaccess';
	if (file_exists($dir.$filename)) {
    _e('<font color="green">&radic; An .htaccess file was found in your root folder</font><br>');
	} else {
    _e('<font color="red">NO .htaccess file was found in your root folder</font><br>');
	}

	$filename = '.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; An .htaccess file was found in your /wp-admin folder</font><br>');
	} else {
    _e('<font color="red">NO .htaccess file was found in your /wp-admin folder</font><br>');
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; A default.htaccess file was found in the /htaccess folder</font><br>');
	} else {
    _e('<font color="red">NO default.htaccess file found in the /htaccess folder</font><br>');
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; A secure.htaccess file was found in the /htaccess folder</font><br>');
	} else {
    _e('<font color="red">NO secure.htaccess file found in the /htaccess folder</font><br>');
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; A maintenance.htaccess file was found in the /htaccess folder</font><br>');
	} else {
    _e('<font color="red">NO maintenance.htaccess file found in the /htaccess folder</font><br>');
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bp-maintenance.php';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; A bp-maintenance.php file was found in the /htaccess folder</font><br>');
	} else {
    _e('<font color="red">NO bp-maintenance.php file found in the /htaccess folder</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; A bps-maintenance-values.php file was found in the /htaccess folder</font><br>');
	} else {
    _e('<font color="red">NO bps-maintenance-values.php file found in the /htaccess folder</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; A wpadmin-secure.htaccess file was found in the /htaccess folder</font><br>');
	} else {
    _e('<font color="red">NO wpadmin-secure.htaccess file found in the /htaccess folder</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/root.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your Current Root .htaccess File is backed up</font><br>');
	} else {
    _e('<font color="red">Your Current Root .htaccess file is NOT backed up yet</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/wpadmin.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your Current wp-admin .htaccess File is backed up</font><br>');
	} else {
    _e('<font color="red">Your Current wp-admin .htaccess File is NOT backed up yet</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_default.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your BPS Master default.htaccess file is backed up</font><br>');
	} else {
    _e('<font color="red">Your BPS Master default.htaccess file is NOT backed up yet</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_secure.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your BPS Master secure.htaccess file is backed up</font><br>');
	} else {
    _e('<font color="red">Your BPS Master secure.htaccess file is NOT backed up yet</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_wpadmin-secure.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your BPS Master wpadmin-secure.htaccess file is backed up</font><br>');
	} else {
    _e('<font color="red">Your BPS Master wpadmin-secure.htaccess file is NOT backed up yet</font><br>');
	}

	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_maintenance.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your BPS Master maintenance.htaccess file is backed up</font><br>');
	} else {
    _e('<font color="red">Your BPS Master maintenance.htaccess file is NOT backed up yet</font><br>');
	}

	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bp-maintenance.php';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your BPS Master bp-maintenance.php file is backed up</font><br>');
	} else {
    _e('<font color="red">Your BPS Master bp-maintenance.php file is NOT backed up yet</font><br>');
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bps-maintenance-values.php';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; Your BPS Master bps-maintenance-values.php file is backed up</font><br>');
	} else {
    _e('<font color="red">Your BPS Master bps-maintenance-values.php file is NOT backed up yet</font><br>');
	}
}

// Backup and Restore page - Backed up Root and wp-admin .htaccess file checks
function backup_restore_checks() {
	$bp_root_back = WP_CONTENT_DIR . '/bps-backup/root.htaccess'; 
	if (file_exists($bp_root_back)) { 
	_e('<font color="green"><strong>&radic; Your Root .htaccess file is backed up.</strong></font><br>'); 
	} else { 
	_e('<font color="red"><strong>Your Root .htaccess file is NOT backed up either because you have not done a Backup yet, an .htaccess file did NOT already exist in your root folder or because of a file copy error. Read the "Current Backed Up .htaccess Files Status Read Me" button for more specific information.</strong></font><br><br>');
	} 

	$bp_wpadmin_back = WP_CONTENT_DIR . '/bps-backup/wpadmin.htaccess'; 
	if (file_exists($bp_wpadmin_back)) { 
	_e('<font color="green"><strong>&radic; Your wp-admin .htaccess file is backed up.</strong></font><br>'); 
	} else { 
	_e('<font color="red"><strong>Your wp-admin .htaccess file is NOT backed up either because you have not done a Backup yet, an .htaccess file did NOT already exist in your /wp-admin folder or because of a file copy error. Read the "Current Backed Up .htaccess Files Status Read Me" button for more specific information.</strong></font><br>'); 
	} 
}

// Backup and Restore page - General check if existing .htaccess files already exist 
function general_bps_file_checks_backup_restore() {
	$dir='../';
	$filename = '.htaccess';
	if (file_exists($dir.$filename)) {
    _e('<font color="green">&radic; An .htaccess file was found in your root folder</font><br>');
	} else {
    _e('<font color="red">NO .htaccess file was found in your root folder</font><br>');
	}

	$filename = '.htaccess';
	if (file_exists($filename)) {
    _e('<font color="green">&radic; An .htaccess file was found in your /wp-admin folder</font><br>');
	} else {
    _e('<font color="red">NO .htaccess file was found in your /wp-admin folder</font><br>');
	}
}

// Backup and Restore page - BPS Master .htaccess backup file checks
function bps_master_file_backups() {
	$bps_default_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_default.htaccess'; 
	if (file_exists($bps_default_master)) {
    _e('<font color="green"><strong>&radic; The default.htaccess Master file is backed up.</strong></font><br>');
	} else {
    _e('<font color="red"><strong>Your default.htaccess Master file has NOT been backed up yet!</strong></font><br>');
	}

	$bps_secure_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_secure.htaccess'; 
	if (file_exists($bps_secure_master)) {
    _e('<font color="green"><strong>&radic; The secure.htaccess Master file is backed up.</strong></font><br>');
	} else {
    _e('<font color="red"><strong>Your secure.htaccess Master file has NOT been backed up yet!</strong></font><br>');
	}
	
	$bps_wpadmin_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_wpadmin-secure.htaccess'; 
	if (file_exists($bps_wpadmin_master)) {
    _e('<font color="green"><strong>&radic; The wpadmin-secure.htaccess Master file is backed up.</strong></font><br>');
	} else {
    _e('<font color="red"><strong>Your wpadmin-secure.htaccess Master file has NOT been backed up yet!</strong></font><br>');
	}
	
	$bps_maintenance_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_maintenance.htaccess'; 
	if (file_exists($bps_maintenance_master)) {
    _e('<font color="green"><strong>&radic; The maintenance.htaccess Master file is backed up.<strong</font><br>');
	} else {
    _e('<font color="red"><strong>Your maintenance.htaccess Master file has NOT been backed up yet!</strong></font><br>');
	}
	
	$bps_bp_maintenance_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bp-maintenance.php'; 
	if (file_exists($bps_bp_maintenance_master)) {
    _e('<font color="green"><strong>&radic; The bp-maintenance.php Master file is backed up.</strong></font><br>');
	} else {
    _e('<font color="red"><strong>Your bp-maintenance.php Master file has NOT been backed up yet!</strong></font><br>');
	}
	
	$bps_bp_maintenance_values = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bps-maintenance-values.php'; 
	if (file_exists($bps_bp_maintenance_values)) {
    _e('<font color="green"><strong>&radic; The bps-maintenance-values.php Master file is backed up.</strong></font><br>');
	} else {
    _e('<font color="red"><strong>Your bps-maintenance-values.php Master file has NOT been backed up yet!</strong></font><br>');
	}
}

// Check if Permalinks are enabled
$permalink_structure = get_option('permalink_structure');
function bps_check_permalinks() {
	if ( get_option('permalink_structure') != '' ) { 
	_e('Permalinks Enabled: <font color="green"><strong>&radic; Permalinks are Enabled</strong></font>'); 
	} else {
	_e('Permalinks Enabled: <font color="red"><strong>WARNING! Permalinks are NOT Enabled<br>Permalinks MUST be enabled for BPS to function correctly</strong></font>'); 
	}
}

// Check PHP version
function bps_check_php_version() {
	if (version_compare(PHP_VERSION, '5.0.0', '>=')) {
    _e('PHP Version Check: <font color="green"><strong>&radic; Running PHP5</strong></font><br>');
}
	if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    _e('<font color="red"><strong>WARNING! BPS requires PHP5 to function correctly. Your PHP version is: ' . PHP_VERSION . '</strong></font><br>');
	}
}

// Heads Up Display - Check PHP version - top error message new activations / installations
function bps_check_php_version_error() {
	if (version_compare(PHP_VERSION, '5.0.0', '>=')) {
    _e('');
	}
	if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    _e('<font color="red"><strong>WARNING! BPS requires at least PHP5 to function correctly. Your PHP version is: ' . PHP_VERSION . '</font></strong><br><strong><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/#bulletproof-security-issues-problems" target="_blank">BPS Guide - PHP5 Solution</a></strong><br><strong>The BPS Guide will open in a new browser window. You will not be directed away from your WordPress Dashboard.</strong><br>');
	}
}

// Heads Up Display - Check if Permalinks are enabled - top error message new activations / installations
$permalink_structure = get_option('permalink_structure');
function bps_check_permalinks_error() {
	if ( get_option('permalink_structure') != '' ) { 
	_e(''); 
	} else {
	_e('<br><font color="red"><strong>WARNING! Permalinks are NOT Enabled. Permalinks MUST be enabled for BPS to function correctly</strong></font><br><strong><a href="http://www.ait-pro.com/aitpro-blog/2304/wordpress-tips-tricks-fixes/permalinks-wordpress-custom-permalinks-wordpress-best-wordpress-permalinks-structure/" target="_blank">BPS Guide - Enabling Permalinks</a></strong><br><strong>The BPS Guide will open in a new browser window. You will not be directed away from your WordPress Dashboard.</strong><br>'); 
	}
}

// Heads Up Display - Check if this is a Windows IIS server and if IIS7 supports permalink rewriting
function bps_check_iis_supports_permalinks() {
global $wp_rewrite, $is_IIS, $is_iis7;
	if ( $is_IIS && !iis7_supports_permalinks() ) {
	_e('<br><font color="red"><strong>WARNING! BPS has detected that your Server is a Windows IIS Server that does not support .htaccess rewriting. Do NOT activate BulletProof Security Modes unless you are absolutely sure you know what you are doing. Your Server Type is: ' . $_SERVER['SERVER_SOFTWARE'] . '</strong></font><br><strong><a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">WordPress Codex - Using Permalinks - see IIS section</a></strong><br><strong>This link will open in a new browser window. You will not be directed away from your WordPress Dashboard.</strong><br>To remove this message permanently click <strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here.</a></strong><br>');
	} else {
	_e('');
	}
}

// Heads Up Display - mkdir and chmod errors are suppressed on activation - check if /bps-backup folder exists
function bps_hud_check_bpsbackup() {
	if( !is_dir (WP_CONTENT_DIR . '/bps-backup')) {
	_e('<br><font color="red"><strong>WARNING! BPS was unable to automatically create the /wp-content/bps-backup folder.</strong></font><br><strong>You will need to create the /wp-content/bps-backup folder manually via FTP.  The folder permissions for the bps-backup folder need to be set to 755 in order to successfully perform permanent online backups.</strong><br>To remove this message permanently click <strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here.</a></strong><br>');
	} else {
	_e('');
	}
	if( !is_dir (WP_CONTENT_DIR . '/bps-backup/master-backups')) {
	_e('<br><font color="red"><strong>WARNING! BPS was unable to automatically create the /wp-content/bps-backup/master-backups folder.</strong></font><br><strong>You will need to create the /wp-content/bps-backup/master-backups folder manually via FTP.  The folder permissions for the master-backups folder need to be set to 755 in order to successfully perform permanent online backups.</strong><br>To remove this message permanently click <strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here.</a></strong><br>');
	} else {
	_e('');
	}
}

// Heads Up Display - Check if PHP Safe Mode is On - 1 is On - 0 is Off
function bps_check_safemode() {
	if (ini_get('safe_mode') == '1') {
	_e('<br><font color="red"><strong>WARNING! BPS has detected that Safe Mode is set to On in your php.ini file.</strong></font><br><strong>If you see errors that BPS was unable to automatically create the backup folders this is probably the reason why.</strong><br>To remove this message permanently click <strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">here.</a></strong><br>');
	} else {
	_e('');
	}
	}

// Heads Up Display - Check if W3TC is active or not and check root htaccess file for W3TC htaccess code 
function bps_w3tc_htaccess_check($plugin_var) {
	$filename = ABSPATH . '.htaccess';
	$string = file_get_contents($filename);
	$bpsSiteUrl = get_option('siteurl');
	$bpsHomeUrl = get_option('home');
	$plugin_var = 'w3-total-cache';
    $return_var = in_array( $plugin_var. '/' .$plugin_var. '.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    if ($return_var == 1) { // return $return_var; ---- 1 equals active
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (!strpos($string, "W3TC")) {
	_e('<font color="red"><strong>W3 Total Cache is activated, but W3TC .htaccess code was NOT found in your root .htaccess file.</strong></font><br><strong>W3TC needs to be redeployed by clicking either the auto-install or deploy buttons. Click to <a href="admin.php?page=w3tc_general" >Redeploy W3TC.</a></strong><br><br>');
	} 
	}
	}
	elseif ($return_var != 1) {
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (strpos($string, "W3TC")) {
	_e('<font color="red"><strong>W3 Total Cache is deactivated and W3TC .htaccess code was found in your root .htaccess file.</strong></font><br><strong>If this is just temporary then this warning message will go away when you reactivate W3TC. If you are planning on uninstalling W3TC the W3TC .htaccess code will be automatically removed from your root .htaccess file when you uninstall W3TC. If you manually edit your root htaccess file then refresh your browser to perform a new HUD htaccess file check.</strong><br><br>');
	}
	} 
	}
}

// Heads Up Display - Check if WPSC is active or not and check root htaccess file for WPSC htaccess code 
function bps_wpsc_htaccess_check($plugin_var) {
	$filename = ABSPATH . '.htaccess';
	$string = file_get_contents($filename);
	$bpsSiteUrl = get_option('siteurl');
	$bpsHomeUrl = get_option('home');
	$plugin_var = 'wp-super-cache';
    $return_var = in_array( $plugin_var. '/' .'wp-cache.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    if ($return_var == 1) { // return $return_var; ---- 1 equals active
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (!strpos($string, "WPSuperCache")) { 
	_e('<font color="red"><strong>WP Super Cache is activated, but either you are not using WPSC mod_rewrite to serve cache files or the WPSC .htaccess code was NOT found in your root .htaccess file.</strong></font><br><strong>If you are not using WPSC mod_rewrite then just add this commented out line of code in anywhere in your root htaccess file - # WPSuperCache. If you are using WPSC mod_rewrite and the WPSC htaccess code is not in your root htaccess file then click this <a href="options-general.php?page=wpsupercache&tab=settings" >Update WPSC link</a> to go to the WPSC Settings page and click the Update Mod_Rewrite Rules button. It appears that the BPS filters are working correctly with the WPSC htaccess code being written to the bottom of the root htaccess file, but I recommend that you manually cut and paste the WPSC htaccess code and the section of Wordpress htaccess code that starts with # BEGIN WordPress and ends with # END WordPress to the top area of your root htaccess file right after Options -Indexes in your root htaccess file. Refresh your browser to perform a new HUD htaccess file check.</strong><br><br>');
	} 
	}
	}
	elseif ($return_var != 1) {
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (strpos($string, "WPSuperCache") ) {
	_e('<font color="red"><strong>WP Super Cache is deactivated and WPSC .htaccess code - # BEGIN WPSuperCache # END WPSuperCache - was found in your root .htaccess file.</strong></font><br><strong>If this is just temporary then this warning message will go away when you reactivate WPSC. You will need to set up and reconfigure WPSC again when you reactivate WPSC. If you are planning on uninstalling WPSC the WPSC .htaccess code will be automatically removed from your root .htaccess file when you uninstall WPSC. If you added commented out line of code in anywhere in your root htaccess file - # WPSuperCache - then delete it and refresh your browser. It appears that the BPS filters are working correctly with the WPSC htaccess code being written to the bottom of the root htaccess file, but I recommend that you manually cut and paste the WPSC htaccess code and the section of Wordpress htaccess code that starts with # BEGIN WordPress and ends with # END WordPress to the top area of your root htaccess file right after Options -Indexes in your root htaccess file.</strong><br><br>');
	}
	} 
	}
}

// Get WordPress Root Installation Folder - Borrowed from WP Core 
function bps_wp_get_root_folder() {
$site_root = parse_url(get_option('siteurl'));
	if ( isset( $site_root['path'] ) )
	$site_root = trailingslashit($site_root['path']);
	else
	$site_root = '/';
	return $site_root;
}

// Display Root or Subfolder Installation Type
function bps_wp_get_root_folder_display_type() {
$site_root = parse_url(get_option('siteurl'));
	if ( isset( $site_root['path'] ) )
	$site_root = trailingslashit($site_root['path']);
	else
	$site_root = '/';
	if (preg_match('/[a-zA-Z0-9]/', $site_root)) {
	echo "Subfolder Installation";
	} else {
	echo "Root Folder Installation";
	}
}

// Check for Multisite
function bps_multsite_check() {  
	if ( is_multisite() ) { 
	_e('Multisite: <strong>Multisite is enabled</strong><br>');
	} else {
	_e('Multisite: <strong>Multisite is not enabled</strong><br>');
	}
}

// Security Modes Page - AutoMagic Single site message
function bps_multsite_check_smode_single() {  
global $wpdb;
	if ( !is_multisite() ) { 
	_e('<font color="green"><strong>Use These AutoMagic Buttons For Your Website<br>For Standard WP Installations</strong></font>');
	} else {
	_e('<strong>Do Not Use These AutoMagic Buttons</strong><br>For Standard WP Single Sites Only');
	}
}

// Security Modes Page - AutoMagic Multisite sub-directory message
function bps_multsite_check_smode_MUSDir() {  
global $wpdb;
	if ( is_multisite() && !is_subdomain_install() ) { 
	_e('<font color="green"><strong>Use These AutoMagic Buttons For Your Website<br>For WP Network / MU sub-directory Installations</strong></font>');
	} else {
	_e('<strong>Do Not Use These AutoMagic Buttons</strong><br>For Network / MU Sub-directory Webites Only');
	}
}

// Security Modes Page - AutoMagic Multisite sub-domain message
function bps_multsite_check_smode_MUSDom() {  
global $wpdb;
	if ( is_multisite() && is_subdomain_install() ) { 
	//if ( is_subdomain_install() ) {
	_e('<font color="green"><strong>Use These AutoMagic Buttons For Your Website<br>For WP Network / MU sub-domain Installations</strong></font>');
	} else {
	_e('<strong>Do Not Use These AutoMagic Buttons</strong><br>For Network / MU Sub-domain Websites Only');
	}
}

/*
// Security Modes Page - htaccess warning for Multisite
function bps_multsite_check_smode() {  
	if ( is_multisite() ) { 
	_e('<strong>WordPress Network (Multisite) Installation Detected</strong><br>Please read the Read Me help button for an additional step required to set up WordPress Network sites.<br>');
	} else {
	_e('');
	}
}
*/

// Check if username Admin exists
function check_admin_username() {
	global $wpdb;
	$name = $wpdb->get_var("SELECT user_login FROM $wpdb->users WHERE user_login='admin'");
	if ($name=="admin"){
	_e('<font color="red"><strong>Recommended Security Changes: Username "admin" is being used. It is recommended that you change the default administrator username "admin" to a new unique username.</strong></font><br><br>');
	} else {
	_e('<font color="green"><strong>&radic; The Administrator username "admin" is not being used</strong></font><br>');
	}
}

// Check for WP readme.html file and if valid BPS .htaccess file is activated
// Get contents of Root .htaccess file from 3-45 - if "9" found in string position 17 - offset 16 - good - else bad
// Check for WP readme.html file and if valid BPS .htaccess file is activated
function bps_filesmatch_check_readmehtml() {
	$htaccess_filename = ABSPATH . '.htaccess';
	$filename = ABSPATH . 'readme.html';
	$section = @file_get_contents($htaccess_filename, NULL, NULL, 3, 45);
	$check_string = @strpos($section, "9", 16);
	$check_stringBPSQSE = @file_get_contents($htaccess_filename);
	if (file_exists($htaccess_filename)) {
	if ($check_string == "17") { 
		_e('');
		}
		if ( !file_exists($filename)) {
		_e('<font color="green"><strong>&radic; The WP readme.html file does not exist</strong></font><br>');
		} else {
		if ($check_string == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
		_e('<font color="green"><strong>&radic; The WP readme.html file is .htaccess protected</strong></font><br>');
		} else {
		_e('<font color="red"><strong>The WP readme.html file is not .htaccess protected</strong></font><br>');
		}
}}}

// Check for WP /wp-admin/install.php file and if valid BPS .htaccess file is activated
// Get contents of Root .htaccess file from 3-45 - if "9" found in string position 17 - offset 16 - good - else bad
function bps_filesmatch_check_installphp() {
	$htaccess_filename = ABSPATH . 'wp-admin/.htaccess';
	$filename = ABSPATH . 'wp-admin/install.php';
	$check_stringBPSQSE = @file_get_contents($htaccess_filename);
	$section = @file_get_contents($htaccess_filename, NULL, NULL, 3, 45);
	$check_string = @strpos($section, "9", 16);	
	if (file_exists($htaccess_filename)) {
	if ($check_string == "17") { 
		_e('');
		}
		if ( !file_exists($filename)) {
		_e('<font color="green"><strong>&radic; The WP /wp-admin/install.php file does not exist</strong></font><br>');
		} else {
		if ($check_string == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
		_e('<font color="green"><strong>&radic; The WP /wp-admin/install.php file is .htaccess protected</strong></font><br>');
		} else {
		_e('<font color="red"><strong>The WP /wp-admin/install.php file is not .htaccess protected</strong></font><br>');
		}
}}}

// Check BPS Pro Modules Status
function check_bps_pro_mod () {
	global $bulletproof_security;
	$filename_pro = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/options-bps-pro-modules.php';
	if (file_exists($filename_pro)) {
	$section_pro = file_get_contents(ABSPATH . $filename, NULL, NULL, 5, 10);
	_e('<font color="green"><strong>&radic; BulletProof Security Pro Modules are installed and activated.</strong></font><br>');
	var_dump($section_pro);
	} else {
	_e('<font color="black"><br>*BPS Pro Modules are not installed</font><br>');
	}
}

// Get SQL Mode from WPDB
function bps_get_sql_mode() {
	global $wpdb;
	$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
        if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
        if (empty($sql_mode)) $sql_mode = __('Not Set');
		else $sql_mode = __('Off');
} 

// Show DB errors should already be set to false in /includes/wp-db.php
// Extra function insurance show_errors = false
function bps_wpdb_errors_off() {
	global $wpdb;
	$wpdb->show_errors = false;
	if ($wpdb->show_errors != false) {
	_e('<font color="red"><strong>WARNING! WordPress DB Show Errors Is Set To: true! DB errors will be displayed</strong></font><br>');
	} else {
	_e('<font color="green"><strong>&radic; WordPress DB Show Errors Function Is Set To: </strong></font>');
	_e('<font color="black"><strong>false</strong></font><br>');
	_e('<font color="green"><strong>&radic; WordPress Database Errors Are Turned Off</strong></font><br>');
	}	
}

// Hide / Remove WordPress Version Meta Generator Tag - echo only for remove_action('wp_head', 'wp_generator');
function bps_wp_remove_version() {
	global $wp_version;
	_e('<font color="green"><strong>&radic; WordPress Meta Generator Tag Removed<br>&radic; WordPress Version Is Not Displayed / Not Shown</strong></font><br>');
}

// Return Nothing For WP Version Callback
function bps_wp_generator_meta_removed() {
	if ( !is_admin()) {
	global $wp_version;
	$wp_version = '';
	}
}
?>