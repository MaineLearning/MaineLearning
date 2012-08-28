<?php
// Direct calls to this file are Forbidden when core files are not present
if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

// Get BPS Version - Just for display purposes
function bpsWhatVersion() {
echo " ~ .47.3";
}

// BPS Master htaccess File Editing - file checks and get contents for editor
function get_secure_htaccess() {
	$secure_htaccess_file = WP_CONTENT_DIR .'/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
	if (file_exists($secure_htaccess_file)) {
	$bpsString = file_get_contents($secure_htaccess_file);
	echo $bpsString;
	} else {
	_e('The secure.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the secure.htaccess file exists and is named secure.htaccess.', 'bulletproof-security');
	}
}

function get_default_htaccess() {
	$default_htaccess_file = WP_CONTENT_DIR .'/plugins/bulletproof-security/admin/htaccess/default.htaccess';
	if (file_exists($default_htaccess_file)) {
	$bpsString = file_get_contents($default_htaccess_file);
	echo $bpsString;
	} else {
	_e('The default.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the default.htaccess file exists and is named default.htaccess.', 'bulletproof-security');
	}
}

function get_maintenance_htaccess() {
	$maintenance_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (file_exists($maintenance_htaccess_file)) {
	$bpsString = file_get_contents($maintenance_htaccess_file);
	echo $bpsString;
	} else {
	_e('The maintenance.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the maintenance.htaccess file exists and is named maintenance.htaccess.', 'bulletproof-security');
	}
}

function get_wpadmin_htaccess() {
	$wpadmin_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	if (file_exists($wpadmin_htaccess_file)) {
	$bpsString = file_get_contents($wpadmin_htaccess_file);
	echo $bpsString;
	} else {
	_e('The wpadmin-secure.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the wpadmin-secure.htaccess file exists and is named wpadmin-secure.htaccess.', 'bulletproof-security');
	}
}

// The current active root htaccess file - file check
function get_root_htaccess() {
	$root_htaccess_file = ABSPATH . '.htaccess';
	if (file_exists($root_htaccess_file)) {
	$bpsString = file_get_contents($root_htaccess_file);
	echo $bpsString;
	} else {
	_e('An .htaccess file was not found in your website root folder.', 'bulletproof-security');
	}
}

// The current active wp-admin htaccess file - file check
function get_current_wpadmin_htaccess_file() {
	$current_wpadmin_htaccess_file = ABSPATH . 'wp-admin/.htaccess';
	if (file_exists($current_wpadmin_htaccess_file)) {
	$bpsString = file_get_contents($current_wpadmin_htaccess_file);
	echo $bpsString;
	} else {
	_e('An .htaccess file was not found in your wp-admin folder.', 'bulletproof-security');
	}
}

// File write checks for editor
function secure_htaccess_file_check() {
	$secure_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
	if (!is_writable($secure_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the secure.htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}

// File write checks for editor
function default_htaccess_file_check() {
	$default_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';
	if (!is_writable($default_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the default.htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}
// File write checks for editor
function maintenance_htaccess_file_check() {
	$maintenance_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (!is_writable($maintenance_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the maintenance.htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}
// File write checks for editor
function wpadmin_htaccess_file_check() {
	$wpadmin_htaccess_file = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	if (!is_writable($wpadmin_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the wpadmin-secure.htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}
// File write checks for editor
function root_htaccess_file_check() {
$root_htaccess_file = ABSPATH . '/.htaccess';
	if (!is_writable($root_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the root .htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}
// File write checks for editor
function current_wpadmin_htaccess_file_check() {
$current_wpadmin_htaccess_file = ABSPATH . '/wp-admin/.htaccess';
	if (!is_writable($current_wpadmin_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the wp-admin .htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}

// BPS Update/Upgrade Status Alert in WP Dashboard - Root .htaccess file - Checks last 2 versions
// .47.1 upgrade to .47.2 - Get contents of Root .htaccess file from 3-45 - if "1" (.47.1) found in string position 17 - offset 16
// .47.2 check - update BPS version number from .47.1 to .47.2 and add new htaccess security filter 
// .47.2 upgrade to .47.3 - Get contents of Root .htaccess file from 3-45 - if "2" (.47.2) found in string position 17 - offset 16
// .47.3 check - update BPS version number from .47.2 to .47.3 and add new htaccess security filter 
// Check for security filters string BPSQSE
function root_htaccess_status_dashboard() {
	$filename = ABSPATH . '.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 45);
	$check_stringBPSQSE = @file_get_contents($filename);
	$permsHtaccess = @substr(sprintf(".%o.", fileperms($filename)), -4);
	$sapi_type = php_sapi_name();
	$check_string_last_ver = @strpos($section, "1", 16);
	$check_string_cur_ver = @strpos($section, "2", 16);
	$check_string_last_ver2 = @strpos($section, "2", 16);
	$check_string_cur_ver2 = @strpos($section, "3", 16);
	$bps_denyall_htaccess = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_denyall_htaccess_renamed = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/.htaccess';
	
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! Your site does not appear to be protected by BulletProof Security', 'bulletproof-security').'</strong></font><br><strong>'.__('If you are upgrading BPS - BPS will now automatically update your htaccess files and add any new security filters automatically.', 'bulletproof-security').'</strong><br><strong>'.__('Refresh your Browser to clear this Alert', 'bulletproof-security').'</strong><br>'.__('Any custom htaccess code or modifications that you have made will not be altered/changed. Activating BulletProof Modes again after upgrading BPS is no longer necessary.', 'bulletproof-security').'<br>'.__('In order for BPS to automatically update htaccess files you will need to stay current with BPS plugin updates and install the latest BPS plugin updates when they are available.', 'bulletproof-security').'<br>'.__('If refreshing your Browser does not clear this alert then you will need to create new Master htaccess files with the AutoMagic buttons and Activate All BulletProof Modes.', 'bulletproof-security').'<br>'.__('If your site is in Maintenance Mode your site is protected by BPS and this Alert will remain to remind you to put your site back in BulletProof Mode again.', 'bulletproof-security').'<br>'.__('If your site is in Default Mode then it is not protected by BulletProof Security. Check the BPS', 'bulletproof-security').' <strong><a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a></strong> '.__('to view your BPS Security Status information.', 'bulletproof-security').'</div>';

	if ( !file_exists($filename)) {
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! An htaccess file was NOT found in your root folder. Check the BPS', 'bulletproof-security').' <a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a> '.__('for more specific information.', 'bulletproof-security').'</strong></font></div>';
	echo $text;
	} else {
	if (file_exists($filename)) {
	if ($check_string_last_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.1", ".47.2", $stringReplace);
		$stringReplace = str_replace("RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]", "RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]\nRewriteCond %{QUERY_STRING} \-[sdcr].*(allow_url_include|allow_url_fopen|safe_mode|disable_functions|auto_prepend_file) [NC,OR]", $stringReplace);
		file_put_contents($filename, $stringReplace);
		copy($bps_denyall_htaccess, $bps_denyall_htaccess_renamed);
	if (@$permsHtaccess == '644.') {
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
	chmod($filename, 0404);
	}
	} 	
	print("................BPS Automatic htaccess File Update in Progress. Refresh Your Browser To Clear The BPS Alert.");
	$text = '<div class="update-nag"><strong>'.__('BPS .47.1 Upgrade Notice', 'bulletproof-security').'</strong><br>'.__('Adding new htaccess security filters included in version .47.2. Refresh your Browser to continue adding new security filters for version .47.3.', 'bulletproof-security').'<br></div>';
	echo $text471;
	}
	if ($check_string_last_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.2", ".47.3", $stringReplace);
		$stringReplace = str_replace("RewriteCond %{HTTP_USER_AGENT} (libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]", "RewriteCond %{HTTP_USER_AGENT} (havij|libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]", $stringReplace);
		file_put_contents($filename, $stringReplace);
		copy($bps_denyall_htaccess, $bps_denyall_htaccess_renamed);
	if (@$permsHtaccess == '644.') {
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
	chmod($filename, 0404);
	}
	} 	
	print("................BPS Automatic htaccess File Update in Progress. Refresh Your Browser To Clear The BPS Alert.");	
	echo $text;
	}
	if ($check_string_cur_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE") || $check_string_cur_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
	//print($section);
	} else {
	echo $text;
}}}}

add_action('admin_notices', 'root_htaccess_status_dashboard');

// BPS Update/Upgrade Status Alert in WP Dashboard - wp-admin .htaccess file
// .47.1 upgrade to .47.2 - Get contents of wp-admin .htaccess file from 3-45 - if "1" (.47.1) found in string position 17 - offset 16
// .47.2 check - update BPS version number from .47.1 to .47.2 
// .47.2 upgrade to .47.3 - Get contents of wp-admin .htaccess file from 3-45 - if "2" (.47.2) found in string position 17 - offset 16
// .47.3 check - update BPS version number from .47.2 to .47.3 
// Check for security filters string BPSQSE-check
function wpadmin_htaccess_status_dashboard() {
	$filename = ABSPATH . 'wp-admin/.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 45);
	$check_stringBPSQSE = @file_get_contents($filename);
	$check_string_last_ver = @strpos($section, "1", 16);
	$check_string_cur_ver = @strpos($section, "2", 16);		
	$check_string_last_ver2 = @strpos($section, "2", 16);
	$check_string_cur_ver2 = @strpos($section, "3", 16);			

	if ( !file_exists($filename)) {
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! An htaccess file was NOT found in your wp-admin folder. Check the BPS', 'bulletproof-security').' <a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a> '.__('for more specific information.', 'bulletproof-security').'</strong></font></div>';
	echo $text;
	} else {
	if (file_exists($filename)) {
		if ($check_string_last_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.1", ".47.2", $stringReplace);
		file_put_contents($filename, $stringReplace);
		//print($section);
		echo '';
		}
	if ($check_string_last_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.2", ".47.3", $stringReplace);
		file_put_contents($filename, $stringReplace);
		//print($section);
		echo '';
		}
	if ($check_string_cur_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE-check") || $check_string_cur_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
	//print($section);
	echo '';
	} else {
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! A valid BPS htaccess file was NOT found in your wp-admin folder', 'bulletproof-security').'</strong></font><br>'.__('If you are upgrading BPS this Alert will go away after you Refresh your Browser.', 'bulletproof-security').'<br>'.__('If you still see this Alert after refreshing your Browser then Activate BulletProof Mode for your wp-admin folder.', 'bulletproof-security').'<br>'.__('BulletProof Mode for the wp-admin folder MUST be activated when you have BulletProof Mode activated for the Root folder.', 'bulletproof-security').'<br>'.__('Check the BPS', 'bulletproof-security').' <strong><a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a></strong> '.__('to view your BPS Security Status.', 'bulletproof-security').'</div>';
	echo $text;
}}}}

add_action('admin_notices', 'wpadmin_htaccess_status_dashboard');

// Fallback function and inpage display - root_htaccess_status_dashboard() will update .htaccess file on upgrade installation
// .47.1 upgrade to .47.2 - Get contents of Root .htaccess file from 3-45 - if "1" (.47.1) found in string position 17 - offset 16
// .47.2 check - update BPS version number from .47.1 to .47.2 - inpage display 
// .47.2 upgrade to .47.3 - Get contents of Root .htaccess file from 3-45 - if "2" (.47.2) found in string position 17 - offset 16
// .47.3 check - update BPS version number from .47.2 to .47.3 - inpage display 
// Check for security filters string BPSQSE
function root_htaccess_status() {
	$filename = ABSPATH . '.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 45);
	$check_stringBPSQSE = @file_get_contents($filename);
	$permsHtaccess = @substr(sprintf(".%o.", fileperms($filename)), -4);
	$sapi_type = php_sapi_name();
	$check_string_last_ver = @strpos($section, "1", 16);
	$check_string_cur_ver = @strpos($section, "2", 16);	
	$check_string_last_ver2 = @strpos($section, "2", 16);
	$check_string_cur_ver2 = @strpos($section, "3", 16);
	$bps_denyall_htaccess = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_denyall_htaccess_renamed = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/.htaccess';
	$textUpdating = '<font color="red"><br><br><strong>'.__('BPS is in the process of updating the version number in your root htaccess file. Refresh your browser to display your current security status and this message should go away. If the BPS QUERY STRING EXPLOITS code does not exist in your root htaccess file then the version number update will fail and this message will still be displayed after you have refreshed your Browser. You will need to click the AutoMagic buttons and activate all BulletProof Modes again.', 'bulletproof-security').'<br><br>'.__('wp-config.php is NOT htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';

	if ( !file_exists($filename)) {
	$text = '<font color="red">'.__('An .htaccess file was NOT found in your root folder', 'bulletproof-security').'<br><br>'.__('wp-config.php is NOT .htaccess protected by BPS', 'bulletproof-security').'</font><br><br>';
	echo $text;
	} else {
	if (file_exists($filename)) {
	$text = '<font color="green"><strong>'.__('The .htaccess file that is activated in your root folder is:', 'bulletproof-security').'</strong></font><br>';
	echo $text;
		print($section);
	if ($check_string_last_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.1", ".47.2", $stringReplace);
		file_put_contents($filename, $stringReplace);
		copy($bps_denyall_htaccess, $bps_denyall_htaccess_renamed);
	if (@$permsHtaccess == '644.') {
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
	chmod($filename, 0404);
	}
	} 	
	echo $textUpdating;
	}
	if ($check_string_last_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.2", ".47.3", $stringReplace);
		file_put_contents($filename, $stringReplace);
		copy($bps_denyall_htaccess, $bps_denyall_htaccess_renamed);
	if (@$permsHtaccess == '644.') {
	if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
	chmod($filename, 0404);
	}
	} 	
	echo $textUpdating;	
	}
	if ($check_string_cur_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE") || $check_string_cur_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE")) {
		$text = '<font color="green"><strong><br><br>&radic; '.__('wp-config.php is htaccess protected by BPS', 'bulletproof-security').'<br>&radic; '.__('php.ini and php5.ini are htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';
		echo $text;
	} else {
	$text = '<font color="red"><br><br><strong>'.__('Either a BPS .htaccess file was NOT found in your root folder or you have not activated BulletProof Mode for your Root folder yet, Default Mode is activated, Maintenance Mode is activated or the version of the htaccess file that you are using is not .47.3 or the BPS QUERY STRING EXPLOITS code does not exist in your root .htaccess file. Please read the Read Me button above.', 'bulletproof-security').'</strong><br><br><strong>'.__('wp-config.php is NOT .htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';
	echo $text;
}}}}

// Fallback function and inpage display - wpadmin_htaccess_status_dashboard() will update .htaccess file on upgrade installation
// .47.1 upgrade to .47.2 - Get contents of wp-admin .htaccess file from 3-45 - if "1" (.47.1) found in string position 17 - offset 16
// .47.2 check - update BPS version number from .47.1 to .47.2 
// .47.2 upgrade to .47.3 - Get contents of wp-admin .htaccess file from 3-45 - if "2" (.47.2) found in string position 17 - offset 16
// .47.3 check - update BPS version number from .47.2 to .47.3 
// Check for security filters string BPSQSE-check
function wpadmin_htaccess_status() {
	$filename = ABSPATH . 'wp-admin/.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 45);
	$check_stringBPSQSE = @file_get_contents($filename);
	$check_string_last_ver = @strpos($section, "1", 16);
	$check_string_cur_ver = @strpos($section, "2", 16);		
	$check_string_last_ver2 = @strpos($section, "2", 16);
	$check_string_cur_ver2 = @strpos($section, "3", 16);
	$textUpdating = '<font color="red"><strong><br><br>'.__('BPS is in the process of updating the version number in your wp-admin htaccess file. Refresh your browser to display your current security status and this message should go away. If the BPS QUERY STRING EXPLOITS code does not exist in your wp-admin htaccess file then the version number update will fail and this message will still be displayed after you have refreshed your Browser. You will need to activate BulletProof Mode for your wp-admin folder again.', 'bulletproof-security').'</strong></font><br>';			

	if ( !file_exists($filename)) {
	$text = '<font color="red"><strong>'.__('An .htaccess file was NOT found in your wp-admin folder.', 'bulletproof-security').'<br>'.__('BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
		if ($check_string_last_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.1", ".47.2", $stringReplace);
		file_put_contents($filename, $stringReplace);
		echo $textUpdating;
		}
		if ($check_string_last_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
		chmod($filename, 0644);
		$stringReplace = @file_get_contents($filename);
		$stringReplace = str_replace(".47.2", ".47.3", $stringReplace);
		file_put_contents($filename, $stringReplace);
		echo $textUpdating;
		}
	if ($check_string_cur_ver == "17" && strpos($check_stringBPSQSE, "BPSQSE-check") || $check_string_cur_ver2 == "17" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
	$text = '<font color="green"><strong>'.__('The htaccess file that is activated in your wp-admin folder is:', 'bulletproof-security').'</strong></font><br>';
	echo $text;
		print($section);
	} else {
	$text = '<font color="red"><strong><br><br>'.__('A valid BPS htaccess file was NOT found in your wp-admin folder. Either you have not activated BulletProof Mode for your wp-admin folder yet or the version of the wp-admin htaccess file that you are using is not .47.3. BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder. Please view the Read Me Help button above.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	}
}	

// Check if BPS Deny ALL htaccess file is activated for the BPS Master htaccess folder
function denyall_htaccess_status_master() {
	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green"><strong>&radic; '.__('Deny All protection activated for BPS Master /htaccess folder', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Deny All protection NOT activated for BPS Master /htaccess folder', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
}
// Check if BPS Deny ALL htaccess file is activated for the /wp-content/bps-backup folder
function denyall_htaccess_status_backup() {
	$filename = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green"><strong>&radic; '.__('Deny All protection activated for /wp-content/bps-backup folder', 'bulletproof-security').'</strong></font><br><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Deny All protection NOT activated for /wp-content/bps-backup folder', 'bulletproof-security').'</strong></font><br><br>';
	echo $text;
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
    $text = '<font color="green">&radic; '.__('An .htaccess file was found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('An .htaccess file was NOT found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = '.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('An .htaccess file was found in your /wp-admin folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('An .htaccess file was NOT found in your /wp-admin folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/default.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('A default.htaccess file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A default.htaccess file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/secure.htaccess';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('A secure.htaccess file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A secure.htaccess file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('A maintenance.htaccess file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A maintenance.htaccess file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bp-maintenance.php';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('A bp-maintenance.php file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A bp-maintenance.php file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('A bps-maintenance-values.php file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A bps-maintenance-values.php file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('A wpadmin-secure.htaccess file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A wpadmin-secure.htaccess file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/root.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('Your Current Root .htaccess File is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your Current Root .htaccess file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/wpadmin.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('Your Current wp-admin .htaccess File is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your Current wp-admin .htaccess file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_default.htaccess';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('Your BPS Master default.htaccess file is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your BPS Master default.htaccess file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_secure.htaccess';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('Your BPS Master secure.htaccess file is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your BPS Master secure.htaccess file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_wpadmin-secure.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('Your BPS Master wpadmin-secure.htaccess file is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your BPS Master wpadmin-secure.htaccess file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_maintenance.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('Your BPS Master maintenance.htaccess file is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your BPS Master maintenance.htaccess file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bp-maintenance.php';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('Your BPS Master bp-maintenance.php file is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your BPS Master bp-maintenance.php file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bps-maintenance-values.php';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('Your BPS Master bps-maintenance-values.php file is backed up', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('Your BPS Master bps-maintenance-values.php file is NOT backed up yet', 'bulletproof-security').'</font><br>';
	echo $text;
	}
}

// Backup and Restore page - Backed up Root and wp-admin .htaccess file checks
function backup_restore_checks() {
	$bp_root_back = WP_CONTENT_DIR . '/bps-backup/root.htaccess'; 
	if (file_exists($bp_root_back)) { 
	 $text = '<font color="green"><strong>&radic; '.__('Your Root .htaccess file is backed up.', 'bulletproof-security').'</strong></font><br>'; 
	echo $text;
	} else { 
	$text = '<font color="red"><strong>'.__('Your Root .htaccess file is NOT backed up either because you have not done a Backup yet, an .htaccess file did NOT already exist in your root folder or because of a file copy error. Read the "Current Backed Up .htaccess Files Status Read Me" button for more specific information.', 'bulletproof-security').'</strong></font><br><br>';
	echo $text;
	} 

	$bp_wpadmin_back = WP_CONTENT_DIR . '/bps-backup/wpadmin.htaccess'; 
	if (file_exists($bp_wpadmin_back)) { 
	$text = '<font color="green"><strong>&radic; '.__('Your wp-admin .htaccess file is backed up.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else { 
	$text = '<font color="red"><strong>'.__('Your wp-admin .htaccess file is NOT backed up either because you have not done a Backup yet, an .htaccess file did NOT already exist in your /wp-admin folder or because of a file copy error. Read the "Current Backed Up .htaccess Files Status Read Me" button for more specific information', 'bulletproof-security').'</strong></font><br>'; 
	echo $text;
	} 
}

// Backup and Restore page - General check if existing .htaccess files already exist 
function general_bps_file_checks_backup_restore() {
	$dir='../';
	$filename = '.htaccess';
	if (file_exists($dir.$filename)) {
  	$text = '<font color="green">&radic; '.__('An .htaccess file was found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('An .htaccess file was NOT found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = '.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('An .htaccess file was found in your /wp-admin folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('An .htaccess file was NOT found in your /wp-admin folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}
}

// Backup and Restore page - BPS Master .htaccess backup file checks
function bps_master_file_backups() {
	$bps_default_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_default.htaccess'; 
	if (file_exists($bps_default_master)) {
    $text = '<font color="green"><strong>&radic; '.__('The default.htaccess Master file is backed up.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Your default.htaccess Master file has NOT been backed up yet!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}

	$bps_secure_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_secure.htaccess'; 
	if (file_exists($bps_secure_master)) {
    $text = '<font color="green"><strong>&radic; '.__('The secure.htaccess Master file is backed up.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Your secure.htaccess Master file has NOT been backed up yet!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	
	$bps_wpadmin_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_wpadmin-secure.htaccess'; 
	if (file_exists($bps_wpadmin_master)) {
    $text = '<font color="green"><strong>&radic; '.__('The wpadmin-secure.htaccess Master file is backed up.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Your wpadmin-secure.htaccess Master file has NOT been backed up yet!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	
	$bps_maintenance_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_maintenance.htaccess'; 
	if (file_exists($bps_maintenance_master)) {
    $text = '<font color="green"><strong>&radic; '.__('The maintenance.htaccess Master file is backed up.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Your maintenance.htaccess Master file has NOT been backed up yet!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	
	$bps_bp_maintenance_master = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bp-maintenance.php'; 
	if (file_exists($bps_bp_maintenance_master)) {
    $text = '<font color="green"><strong>&radic; '.__('The bp-maintenance.php Master file is backed up.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Your bp-maintenance.php Master file has NOT been backed up yet!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
	
	$bps_bp_maintenance_values = WP_CONTENT_DIR . '/bps-backup/master-backups/backup_bps-maintenance-values.php'; 
	if (file_exists($bps_bp_maintenance_values)) {
    $text = '<font color="green"><strong>&radic; '.__('The bps-maintenance-values.php Master file is backed up.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
    $text = '<font color="red"><strong>'.__('Your bps-maintenance-values.php Master file has NOT been backed up yet!', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
}

// Check if Permalinks are enabled
$permalink_structure = get_option('permalink_structure');
function bps_check_permalinks() {
	if ( get_option('permalink_structure') != '' ) { 
	$text = __('Permalinks Enabled: ', 'bulletproof-security').'<font color="green"><strong>&radic; '.__('Permalinks are Enabled', 'bulletproof-security').'</strong></font>'; 
	echo $text;
	} else {
	$text = __('Permalinks Enabled: ', 'bulletproof-security').'<font color="red"><strong>'.__('WARNING! Permalinks are NOT Enabled', 'bulletproof-security').'<br>'.__('Permalinks MUST be enabled for BPS to function correctly', 'bulletproof-security').'</strong></font>';
	echo $text;
	}
}

// Check PHP version
function bps_check_php_version() {
	if (version_compare(PHP_VERSION, '5.0.0', '>=')) {
    $text = __('PHP Version Check: ', 'bulletproof-security').'<font color="green"><strong>&radic; '.__('Using PHP5', 'bulletproof-security').'</strong></font><br>';
	echo $text;
}
	if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    $text = '<font color="red"><strong>'.__('WARNING! BPS requires PHP5 to function correctly. Your PHP version is: ', 'bulletproof-security').PHP_VERSION.'</strong></font><br>';
	echo $text;
	}
}

// Heads Up Display - Check PHP version - top error message new activations / installations
function bps_check_php_version_error() {
	if (version_compare(PHP_VERSION, '5.0.0', '>=')) {
    echo '';
	}
	if (version_compare(PHP_VERSION, '5.0.0', '<')) {
     $text = '<font color="red"><strong>'.__('WARNING! BPS requires at least PHP5 to function correctly. Your PHP version is: ', 'bulletproof-security').PHP_VERSION.'</font></strong><br><strong><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45/#bulletproof-security-issues-problems" target="_blank">'.__(' BPS Guide - PHP5 Solution ', 'bulletproof-security').'</a></strong><br><strong>'.__('The BPS Guide will open in a new browser window. You will not be directed away from your WordPress Dashboard.', 'bulletproof-security').'</strong><br>';
	 echo $text;
	}
}

// Heads Up Display - Check if Permalinks are enabled - top error message new activations / installations
$permalink_structure = get_option('permalink_structure');
function bps_check_permalinks_error() {
	if ( get_option('permalink_structure') != '' ) { 
	echo ''; 
	} else {
	$text = '<br><font color="red"><strong>'.__('WARNING! Permalinks are NOT Enabled. Permalinks MUST be enabled for BPS to function correctly', 'bulletproof-security').'</strong></font><br><strong><a href="http://www.ait-pro.com/aitpro-blog/2304/wordpress-tips-tricks-fixes/permalinks-wordpress-custom-permalinks-wordpress-best-wordpress-permalinks-structure/" target="_blank">'.__(' BPS Guide - Enabling Permalinks ', 'bulletproof-security').'</a></strong><br><strong>'.__('The BPS Guide will open in a new browser window. You will not be directed away from your WordPress Dashboard.', 'bulletproof-security').'</strong><br>';
	echo $text;
	}
}

// Heads Up Display - Check if this is a Windows IIS server and if IIS7 supports permalink rewriting
function bps_check_iis_supports_permalinks() {
global $wp_rewrite, $is_IIS, $is_iis7;
	if ( $is_IIS && !iis7_supports_permalinks() ) {
	$text = '<br><font color="red"><strong>'.__('WARNING! BPS has detected that your Server is a Windows IIS Server that does not support .htaccess rewriting. Do NOT activate BulletProof Security Modes unless you are absolutely sure you know what you are doing. Your Server Type is: ', 'bulletproof-security').$_SERVER['SERVER_SOFTWARE'].'</strong></font><br><strong><a href="http://codex.wordpress.org/Using_Permalinks" target="_blank">'.__(' WordPress Codex - Using Permalinks - see IIS section ', 'bulletproof-security').'</a></strong><br><strong>'.__('This link will open in a new browser window. You will not be directed away from your WordPress Dashboard.', 'bulletproof-security').'</strong><br>'.__('To remove this message permanently click ', 'bulletproof-security').'<strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here.', 'bulletproof-security').'</a></strong><br>';
	echo $text;
	} else {
	echo '';
	}
}

// Heads Up Display - mkdir and chmod errors are suppressed on activation - check if /bps-backup folder exists
function bps_hud_check_bpsbackup() {
	if( !is_dir (WP_CONTENT_DIR . '/bps-backup')) {
	$text = '<br><font color="red"><strong>'.__('WARNING! BPS was unable to automatically create the /wp-content/bps-backup folder.', 'bulletproof-security').'</strong></font><br><strong>'.__('You will need to create the /wp-content/bps-backup folder manually via FTP. The folder permissions for the bps-backup folder need to be set to 755 in order to successfully perform permanent online backups.', 'bulletproof-security').'</strong><br>'.__('To remove this message permanently click ', 'bulletproof-security').'<strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here.', 'bulletproof-security').'</a></strong><br>';
	echo $text;
	} else {
	echo '';
	}
	if( !is_dir (WP_CONTENT_DIR . '/bps-backup/master-backups')) {
	$text = '<br><font color="red"><strong>'.__('WARNING! BPS was unable to automatically create the /wp-content/bps-backup/master-backups folder.', 'bulletproof-security').'</strong></font><br><strong>'.__('You will need to create the /wp-content/bps-backup/master-backups folder manually via FTP. The folder permissions for the master-backups folder need to be set to 755 in order to successfully perform permanent online backups.', 'bulletproof-security').'</strong><br>'.__('To remove this message permanently click ', 'bulletproof-security').'<strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here.', 'bulletproof-security').'</a></strong><br>';
	echo $text;
	} else {
	echo '';
	}
}

// Heads Up Display - Check if PHP Safe Mode is On - 1 is On - 0 is Off
function bps_check_safemode() {
	if (ini_get('safe_mode') == '1') {
	$text = '<br><font color="red"><strong>'.__('WARNING! BPS has detected that Safe Mode is set to On in your php.ini file.', 'bulletproof-security').'</strong></font><br><strong>'.__('If you see errors that BPS was unable to automatically create the backup folders this is probably the reason why.', 'bulletproof-security').'</strong><br>'.__('To remove this message permanently click ', 'bulletproof-security').'<strong><a href="http://www.ait-pro.com/aitpro-blog/2566/bulletproof-security-plugin-support/bulletproof-security-error-messages" target="_blank">'.__('here.', 'bulletproof-security').'</a></strong><br>';
	echo $text;
	} else {
	echo '';
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
	$text = '<font color="red"><strong>'.__('W3 Total Cache is activated, but W3TC .htaccess code was NOT found in your root .htaccess file.', 'bulletproof-security').'</strong></font><br><strong>'.__('W3TC needs to be redeployed by clicking either the auto-install or deploy buttons. Click to ', 'bulletproof-security').'<a href="admin.php?page=w3tc_general" >'.__('Redeploy W3TC.', 'bulletproof-security').'</a></strong><br><br>';
	echo $text;
	} 
	}
	}
	elseif ($return_var != 1) {
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (strpos($string, "W3TC")) {
	$text = '<font color="red"><strong>'.__('W3 Total Cache is deactivated and W3TC .htaccess code was found in your root .htaccess file.', 'bulletproof-security').'</strong></font><br><strong>'.__('If this is just temporary then this warning message will go away when you reactivate W3TC. If you are planning on uninstalling W3TC the W3TC .htaccess code will be automatically removed from your root .htaccess file when you uninstall W3TC. If you manually edit your root htaccess file then refresh your browser to perform a new HUD htaccess file check.', 'bulletproof-security').'</strong><br><br>';
	echo $text;
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
	
	if ($return_var == 1 || is_plugin_active_for_network( 'wp-super-cache/wp-cache.php' )) { // checks if WPSC is active for Single site or Network
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (!strpos($string, "WPSuperCache")) { 
	$text = '<font color="red"><strong>'.__('WP Super Cache is activated, but either you are not using WPSC mod_rewrite to serve cache files or the WPSC .htaccess code was NOT found in your root .htaccess file.', 'bulletproof-security').'</strong></font><br><strong>'.__('If you are not using WPSC mod_rewrite then just add this commented out line of code in anywhere in your root htaccess file - # WPSuperCache. If you are using WPSC mod_rewrite and the WPSC htaccess code is not in your root htaccess file then click this ', 'bulletproof-security').'<a href="options-general.php?page=wpsupercache&tab=settings" >'.__('Update WPSC link', 'bulletproof-security').'</a> '.__('to go to the WPSC Settings page and click the Update Mod_Rewrite Rules button. Refresh your browser to perform a new htaccess file check.', 'bulletproof-security').'</strong><br><br>';
	echo $text;
	} 
	}
	}
	elseif ($return_var != 1 || !is_plugin_active_for_network( 'wp-super-cache/wp-cache.php' )) { // checks if WPSC is NOT active for Single or Network
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (strpos($string, "WPSuperCache") ) {
	$text = '<font color="red"><strong>'.__('WP Super Cache is deactivated and WPSC .htaccess code - # BEGIN WPSuperCache # END WPSuperCache - was found in your root .htaccess file.', 'bulletproof-security').'</strong></font><br><strong>'.__('If this is just temporary then this warning message will go away when you reactivate WPSC. You will need to set up and reconfigure WPSC again when you reactivate WPSC. If you are planning on uninstalling WPSC the WPSC .htaccess code will be automatically removed from your root .htaccess file when you uninstall WPSC. If you added commented out line of code in anywhere in your root htaccess file - # WPSuperCache - then delete it and refresh your browser.', 'bulletproof-security').'</strong><br><br>';
	echo $text;
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
	_e('Subfolder Installation', 'bulletproof-security');
	} else {
	_e('Root Folder Installation', 'bulletproof-security');
	}
}

// Check for Multisite
function bps_multsite_check() {  
	if ( is_multisite() ) { 
	$text = __('Multisite: ', 'bulletproof-security').'<strong>'.__('Multisite is enabled', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	$text = __('Multisite: ', 'bulletproof-security').'<strong>'.__('Multisite is Not enabled', 'bulletproof-security').'</strong><br>';
	echo $text;
	}
}

// Security Modes Page - AutoMagic Single site message
function bps_multsite_check_smode_single() {  
global $wpdb;
	if ( !is_multisite() ) { 
	$text = '<font color="green"><strong>'.__('Use These AutoMagic Buttons For Your Website', 'bulletproof-security').'<br>'.__('For Standard WP Installations', 'bulletproof-security').'</strong></font>';
	echo $text;
	} else {
	$text = '<strong>'.__('Do Not Use These AutoMagic Buttons', 'bulletproof-security').'</strong><br>'.__('For Standard WP Single Sites Only', 'bulletproof-security');
	echo $text;
	}
}

// Security Modes Page - AutoMagic Multisite sub-directory message
function bps_multsite_check_smode_MUSDir() {  
global $wpdb;
	if ( is_multisite() && !is_subdomain_install() ) { 
	$text = '<font color="green"><strong>'.__('Use These AutoMagic Buttons For Your Website', 'bulletproof-security').'<br>'.__('For WP Network / MU sub-directory Installations', 'bulletproof-security').'</strong></font>';
	echo $text;
	} else {
	$text = '<strong>'.__('Do Not Use These AutoMagic Buttons', 'bulletproof-security').'</strong><br>'.__('For Network / MU Sub-directory Websites Only', 'bulletproof-security');
	echo $text;
	}
}

// Security Modes Page - AutoMagic Multisite sub-domain message
function bps_multsite_check_smode_MUSDom() {  
global $wpdb;
	if ( is_multisite() && is_subdomain_install() ) { 
	//if ( is_subdomain_install() ) {
	$text = '<font color="green"><strong>'.__('Use These AutoMagic Buttons For Your Website', 'bulletproof-security').'<br>'.__('For WP Network / MU sub-domain Installations', 'bulletproof-security').'</strong></font>';
	echo $text;
	} else {
	$text = '<strong>'.__('Do Not Use These AutoMagic Buttons', 'bulletproof-security').'</strong><br>'.__('For Network / MU Sub-domain Websites Only', 'bulletproof-security');
	echo $text;
	}
}

// Check if username Admin exists
function check_admin_username() {
	global $wpdb;
	$name = $wpdb->get_var("SELECT user_login FROM $wpdb->users WHERE user_login='admin'");
	if ($name == "admin"){
	$text = '<font color="green"><strong>'.__('Recommended Security Changes: Username '.'"'.'admin'.'"'.' is being used. It is recommended that you change the default administrator username "admin" to a new unique username.', 'bulletproof-security').'</strong></font><br><br>';
	echo $text;
	} else {
	$text = '<font color="green"><strong>&radic; '.__('The Default Admin username '.'"'.'admin'.'"'.' is not being used', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}
}

// Check for WP readme.html file and if valid BPS .htaccess file is activated
// Get contents of Root .htaccess file from 3-45 - if "9" (.46.9) found in string position 17 - offset 16 - good - else bad
// .47 check - will only check the 7 in position 15 - offset 14 - several versions of BPS will not have new .htaccess coding added
function bps_filesmatch_check_readmehtml() {
	$htaccess_filename = ABSPATH . '.htaccess';
	$filename = ABSPATH . 'readme.html';
	$section = @file_get_contents($htaccess_filename, NULL, NULL, 3, 45);
	$check_string = @strpos($section, "7", 14);
	$check_stringBPSQSE = @file_get_contents($htaccess_filename);
	if (file_exists($htaccess_filename)) {
	if ($check_string == "15") { 
		echo '';
		}
		if ( !file_exists($filename)) {
		$text = '<font color="black"><strong>&radic; '.__('The WP readme.html file does not exist', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		} else {
		if ($check_string == "15" && strpos($check_stringBPSQSE, "BPSQSE")) {
		$text = '<font color="green"><strong>&radic; '.__('The WP readme.html file is .htaccess protected', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		} else {
		$text = '<font color="red"><strong>'.__('The WP readme.html file is not .htaccess protected', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		}
}}}

// Check for WP /wp-admin/install.php file and if valid BPS .htaccess file is activated
// Get contents of Root .htaccess file from 3-45 - if "9" (.46.9) found in string position 17 - offset 16 - good - else bad
// .47 check - will only check the 7 in position 15 - offset 14 - several versions of BPS will not have new .htaccess coding added
function bps_filesmatch_check_installphp() {
	$htaccess_filename = ABSPATH . 'wp-admin/.htaccess';
	$filename = ABSPATH . 'wp-admin/install.php';
	$check_stringBPSQSE = @file_get_contents($htaccess_filename);
	$section = @file_get_contents($htaccess_filename, NULL, NULL, 3, 45);
	$check_string = @strpos($section, "7", 14);	
	if (file_exists($htaccess_filename)) {
	if ($check_string == "15") { 
		echo '';
		}
		if ( !file_exists($filename)) {
		$text = '<font color="green"><strong>&radic; '.__('The WP /wp-admin/install.php file does not exist', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		} else {
		if ($check_string == "15" && strpos($check_stringBPSQSE, "BPSQSE-check")) {
		$text = '<font color="green"><strong>&radic; '.__('The WP /wp-admin/install.php file is .htaccess protected', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		} else {
		$text = '<font color="red"><strong>'.__('The WP /wp-admin/install.php file is not .htaccess protected', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		}
}}}

// Check BPS Pro Modules Status
function check_bps_pro_mod () {
	global $bulletproof_security;
	$filename_pro = WP_CONTENT_DIR . '/plugins/bulletproof-security/admin/options-bps-pro-modules.php';
	if (file_exists($filename_pro)) {
	$section_pro = file_get_contents(ABSPATH . $filename, NULL, NULL, 5, 10);
	$text = '<font color="green"><strong>&radic; '.__('BulletProof Security Pro Modules are installed and activated.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	var_dump($section_pro);
	} else {
	$text = '<font color="black"><br>*'.__('BPS Pro Modules are not installed', 'bulletproof-security').'</font><br>';
	echo $text;
	}
}

// Get SQL Mode from WPDB
function bps_get_sql_mode() {
	global $wpdb;
	$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
        if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
        if (empty($sql_mode)) $sql_mode = _e('Not Set', 'bulletproof-security');
		else $sql_mode = _e('Off', 'bulletproof-security');
} 

// Show DB errors should already be set to false in /includes/wp-db.php
// Extra function insurance show_errors = false
function bps_wpdb_errors_off() {
	global $wpdb;
	$wpdb->show_errors = false;
	if ($wpdb->show_errors != false) {
	$text = '<font color="red"><strong>'.__('WARNING! WordPress DB Show Errors Is Set To: true! DB errors will be displayed', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	} else {
	$text = '<font color="green"><strong>&radic; '.__('WordPress DB Show Errors Function Is Set To: ', 'bulletproof-security').'</strong></font><font color="black"><strong> '.__('false', 'bulletproof-security').'</strong></font><br><font color="green"><strong>&radic; '.__('WordPress Database Errors Are Turned Off', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	}	
}

// Hide / Remove WordPress Version Meta Generator Tag - echo only for remove_action('wp_head', 'wp_generator');
function bps_wp_remove_version() {
	global $wp_version;
	$text = '<font color="green"><strong>&radic; '.__('WordPress Meta Generator Tag Removed', 'bulletproof-security').'<br>&radic; '.__('WordPress Version Is Not Displayed / Not Shown', 'bulletproof-security').'</strong></font><br>';
	echo $text;
}

// Return Nothing For WP Version Callback
function bps_wp_generator_meta_removed() {
	if ( !is_admin()) {
	global $wp_version;
	$wp_version = '';
	}
}
?>