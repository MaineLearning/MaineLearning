<?php
// Direct calls to this file are Forbidden when core files are not present
if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

// Create the BPS Master /htaccess Folder Deny All .htaccess file automatically
// Create the BPS Backup /bps-backup Folder Deny All .htaccess file automatically
function bps_Master_htaccess_folder_bpsbackup_denyall() {
$denyAllHtaccess = WP_PLUGIN_DIR .'/bulletproof-security/admin/htaccess/deny-all.htaccess';
$denyAllHtaccessCopy = WP_PLUGIN_DIR .'/bulletproof-security/admin/htaccess/.htaccess';
$bpsBackup = WP_CONTENT_DIR . '/bps-backup';
$bpsBackupHtaccess = WP_CONTENT_DIR . '/bps-backup/.htaccess';

	if ( current_user_can('manage_options') ) { 
	
	if ( !file_exists($denyAllHtaccessCopy) ) {
		@copy($denyAllHtaccess, $denyAllHtaccessCopy);	
	}
	
	if ( is_dir($bpsBackup) && !file_exists($bpsBackupHtaccess) ) {
		@copy($denyAllHtaccess, $bpsBackupHtaccess);	
	}
	}
}
add_action('admin_notices', 'bps_Master_htaccess_folder_bpsbackup_denyall');

// Get File Size of the Security Log File - 500KB = 512000 bytes - Display Dashboard Alert when log file exceeds 500KB
function getSecurityLogSize_wp() {
$filename = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
if (file_exists($filename)) {
	$logSize = filesize($filename);
	if ($logSize >= 512000) {
 		$text = '<div class="update-nag"><strong><font color="red">'. __('Security Log File Size is: ', 'bulletproof-security') . round($logSize / 1024, 2) .' KB</font><br>'.__('Your Security Log file is very large which will cause the BPS Options page to load much slower.', 'bulletproof-security').'<br>'.__('To Fix this issue ', 'bulletproof-security').'<a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-3">'.__('Click Here', 'bulletproof-security').'</a>'.__(' to go to the Security Log page and copy and paste the Security Log file contents into a Notepad text file on your computer and save it.', 'bulletproof-security').'<br>'.__('Then click the Delete Log button to delete the contents of this Log file. If you have BPS Pro your Log files are zipped, emailed and deleted automatically.', 'bulletproof-security').'</strong></div>';		
		echo $text; 
	} else {
 		return;
	}
	}
}
add_action('admin_notices', 'getSecurityLogSize_wp');

// BPS Master htaccess File Editing - file checks and get contents for editor
function get_secure_htaccess() {
	$secure_htaccess_file = WP_PLUGIN_DIR .'/bulletproof-security/admin/htaccess/secure.htaccess';
	if (file_exists($secure_htaccess_file)) {
	$bpsString = file_get_contents($secure_htaccess_file);
	echo $bpsString;
	} else {
	_e('The secure.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the secure.htaccess file exists and is named secure.htaccess.', 'bulletproof-security');
	}
}

function get_default_htaccess() {
	$default_htaccess_file = WP_PLUGIN_DIR .'/bulletproof-security/admin/htaccess/default.htaccess';
	if (file_exists($default_htaccess_file)) {
	$bpsString = file_get_contents($default_htaccess_file);
	echo $bpsString;
	} else {
	_e('The default.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the default.htaccess file exists and is named default.htaccess.', 'bulletproof-security');
	}
}

function get_maintenance_htaccess() {
	$maintenance_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (file_exists($maintenance_htaccess_file)) {
	$bpsString = file_get_contents($maintenance_htaccess_file);
	echo $bpsString;
	} else {
	_e('The maintenance.htaccess file either does not exist or is not named correctly. Check the /wp-content/plugins/bulletproof-security/admin/htaccess/ folder to make sure the maintenance.htaccess file exists and is named maintenance.htaccess.', 'bulletproof-security');
	}
}

function get_wpadmin_htaccess() {
	$wpadmin_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
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
	$secure_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
	if (!is_writable($secure_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the secure.htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}

// File write checks for editor
function default_htaccess_file_check() {
	$default_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
	if (!is_writable($default_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the default.htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}
// File write checks for editor
function maintenance_htaccess_file_check() {
	$maintenance_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (!is_writable($maintenance_htaccess_file)) {
 		$text = '<font color="red"><strong>'.__('Cannot write to the maintenance.htaccess file. Minimum file permission required is 600.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	    } else {
	echo '';
}
}
// File write checks for editor
function wpadmin_htaccess_file_check() {
	$wpadmin_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
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

// Get DNS Name Server from [target]
function bps_DNS_NS() {
$bpsHostName = esc_html($_SERVER['SERVER_NAME']);
$bpsTargetNS = '';
$bpsTarget = '';
$bpsNSHostSubject = '';
$bpsGetDNS = @dns_get_record($bpsHostName, DNS_NS);
	
	if (!isset($bpsGetDNS[0]['target'])) {
		echo '';
		} else {
		$bpsTargetNS = $bpsGetDNS[0]['target'];
	if ($bpsTargetNS != '') {
		preg_match('/[^.]+\.[^.]+$/', $bpsTargetNS, $bpsTmatches);
		$bpsNSHostSubject = $bpsTmatches[0];
	return $bpsNSHostSubject;
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
	return $bpsNSHostSubject;
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
	return $bpsNSHostSubject;
	}
	}
}


// Get Domain Root without prefix
function bpsGetDomainRoot() {
	$ServerName = $_SERVER['SERVER_NAME'];
	preg_match('/[^.]+\.[^.]+$/', $ServerName, $matches);
	return $matches[0];
}

// BPS Update/Upgrade Status Alert in WP Dashboard - Root .htaccess file
// Automatic .htaccess file update for BPS versions from .46.9 to current version
// Check for security filters string BPSQSE
// IMPORTANT Note: preg_match must be enclosed otherwise the conditions fail
// also a nice bonus is that this forces the string replace on a new line.
function root_htaccess_status_dashboard() {
global $bps_version;
$options = get_option('bulletproof_security_options_autolock');	
	$filename = ABSPATH . '.htaccess';
	$permsHtaccess = @substr(sprintf(".%o.", fileperms($filename)), -4);
	$sapi_type = php_sapi_name();	
	$check_string = @file_get_contents($filename);
	$section = @file_get_contents($filename, NULL, NULL, 3, 46);	
	$bps_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_denyall_htaccess_renamed = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
	$bps_get_domain_root = bpsGetDomainRoot();
	$bps_get_wp_root_secure = bps_wp_get_root_folder();
	$pattern0 = '/#\sBPS\sPRO\sERROR\sLOGGING(.*)ErrorDocument\s404\s(.*)\/404\.php/s';
	$pattern1 = '/#\sFORBID\sEMPTY\sREFFERER\sSPAMBOTS(.*)RewriteCond\s%{HTTP_USER_AGENT}\s\^\$\sRewriteRule\s\.\*\s\-\s\[F\]/s';	
	$pattern2 = '/TIMTHUMB FORBID RFI and MISC FILE SKIP\/BYPASS RULE/s';
	$pattern3 = '/\[NC\]\s*RewriteCond %{HTTP_REFERER} \^\.\*(.*)\.\*\s*(.*)\s*RewriteRule \. \- \[S\=1\]/s';
	$pattern4 = '/\.\*\(allow_url_include\|allow_url_fopen\|safe_mode\|disable_functions\|auto_prepend_file\) \[NC,OR\]/s';
	$pattern5 = '/FORBID COMMENT SPAMMERS ACCESS TO YOUR wp-comments-post.php FILE/s';
	$pattern6 = '/(\[|\]|\(|\)|<|>|%3c|%3e|%5b|%5d)/s';
	$pattern7 = '/RewriteCond %{QUERY_STRING} \^\.\*(.*)[3](.*)[5](.*)[5](.*)[7](.*)\)/';
	$ExcludedHosts = array('webmasters.com', 'rzone.de', 'softcomca.com');

	if ( !file_exists($filename)) {
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! An htaccess file was NOT found in your root folder. Check the BPS', 'bulletproof-security').' <a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a> '.__('for more specific information.', 'bulletproof-security').'</strong></font></div>';
	echo $text;
	
	} else {
	
	if (file_exists($filename)) {

switch ($bps_version) {
    case ".47.7": // for testing
		if (strpos($check_string, "BULLETPROOF .47.7") && strpos($check_string, "BPSQSE")) {
			print($section.'...Testing...');
		break;
		}
    case ".47.8":
		if (!strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE")) {
			chmod($filename, 0644);
			$stringReplace = @file_get_contents($filename);
			$stringReplace = str_replace("BULLETPROOF .46.9", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.1", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.2", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.3", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.4", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.5", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.6", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.7", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("RewriteCond %{HTTP_USER_AGENT} (libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]", "RewriteCond %{HTTP_USER_AGENT} (havij|libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]", $stringReplace);
			
		if ( preg_match($pattern0, $stringReplace, $matches) ) {
			$stringReplace = preg_replace('/#\sBPS\sPRO\sERROR\sLOGGING(.*)ErrorDocument\s404\s(.*)\/404\.php/s', "# BPS ERROR LOGGING AND TRACKING\n# BPS has premade 403 Forbidden, 400 Bad Request and 404 Not Found files that are used\n# to track and log 403, 400 and 404 errors that occur on your website. When a hacker attempts to\n# hack your website the hackers IP address, Host name, Request Method, Referering link, the file name or\n# requested resource, the user agent of the hacker and the query string used in the hack attempt are logged.\n# All BPS log files are htaccess protected so that only you can view them.\n# The 400.php, 403.php and 404.php files are located in /wp-content/plugins/bulletproof-security/\n# The 400 and 403 Error logging files are already set up and will automatically start logging errors\n# after you install BPS and have activated BulletProof Mode for your Root folder.\n# If you would like to log 404 errors you will need to copy the logging code in the BPS 404.php file\n# to your Theme's 404.php template file. Simple instructions are included in the BPS 404.php file.\n# You can open the BPS 404.php file using the WP Plugins Editor.\n# NOTE: By default WordPress automatically looks in your Theme's folder for a 404.php template file.\n
ErrorDocument 400 $bps_get_wp_root_secure"."wp-content/plugins/bulletproof-security/400.php
ErrorDocument 403 $bps_get_wp_root_secure"."wp-content/plugins/bulletproof-security/403.php
ErrorDocument 404 $bps_get_wp_root_secure"."404.php", $stringReplace);
		}

		if ( preg_match($pattern1, $stringReplace, $matches) ) {
			$stringReplace = preg_replace('/#\sFORBID\sEMPTY\sREFFERER\sSPAMBOTS(.*)RewriteCond\s%{HTTP_USER_AGENT}\s\^\$\sRewriteRule\s\.\*\s\-\s\[F\]/s', '', $stringReplace);
		}			
			
		if (!preg_match($pattern2, $stringReplace, $matches)) {
			$stringReplace = str_replace("# TimThumb Forbid RFI By Host Name But Allow Internal Requests", "# TIMTHUMB FORBID RFI and MISC FILE SKIP/BYPASS RULE\n# Only Allow Internal File Requests From Your Website\n# To Allow Additional Websites Access to a File Use [OR] as shown below.\n# RewriteCond %{HTTP_REFERER} ^.*YourWebsite.com.* [OR]\n# RewriteCond %{HTTP_REFERER} ^.*AnotherWebsite.com.*", $stringReplace);
		}
		
		if (!preg_match($pattern3, $stringReplace, $matches)) {
			$stringReplace = str_replace("RewriteRule . - [S=1]", "RewriteCond %{HTTP_REFERER} ^.*$bps_get_domain_root.*\nRewriteRule . - [S=1]", $stringReplace);
		}
		
		if (preg_match($pattern3, $stringReplace, $matches)) {
			$stringReplace = preg_replace('/\[NC\]\s*RewriteCond %{HTTP_REFERER} \^\.\*(.*)\.\*\s*(.*)\s*RewriteRule \. \- \[S\=1\]/s', "[NC]\nRewriteCond %{HTTP_REFERER} ^.*$bps_get_domain_root.*\nRewriteRule . - [S=1]", $stringReplace);
		}

		if ( preg_match($pattern6, $stringReplace, $matches)) {
			$stringReplace = str_replace("RewriteCond %{QUERY_STRING} ^.*(\[|\]|\(|\)|<|>|%3c|%3e|%5b|%5d).* [NC,OR]", "RewriteCond %{QUERY_STRING} ^.*(\(|\)|<|>|%3c|%3e).* [NC,OR]", $stringReplace);
			$stringReplace = str_replace("RewriteCond %{QUERY_STRING} ^.*(\x00|\x04|\x08|\x0d|\x1b|\x20|\x3c|\x3e|\x5b|\x5d|\x7f).* [NC,OR]", "RewriteCond %{QUERY_STRING} ^.*(\x00|\x04|\x08|\x0d|\x1b|\x20|\x3c|\x3e|\x7f).* [NC,OR]", $stringReplace);		
		}
		
		if ( preg_match($pattern7, $stringReplace, $matches)) {
$stringReplace = preg_replace('/RewriteCond %{QUERY_STRING} \^\.\*(.*)[5](.*)[5](.*)\)/', 'RewriteCond %{QUERY_STRING} ^.*(\x00|\x04|\x08|\x0d|\x1b|\x20|\x3c|\x3e|\x7f)', $stringReplace);
		}

		if (!preg_match($pattern4, $stringReplace, $matches)) {
			$stringReplace = str_replace("RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]", "RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]\nRewriteCond %{QUERY_STRING} \-[sdcr].*(allow_url_include|allow_url_fopen|safe_mode|disable_functions|auto_prepend_file) [NC,OR]", $stringReplace);
		}
		
		if (!preg_match($pattern5, $stringReplace, $matches)) {
			$stringReplace = str_replace("# BLOCK MORE BAD BOTS RIPPERS AND OFFLINE BROWSERS", "# FORBID COMMENT SPAMMERS ACCESS TO YOUR wp-comments-post.php FILE\n# This is a better approach to blocking Comment Spammers so that you do not\n# accidentally block good traffic to your website. You can add additional\n# Comment Spammer IP addresses on a case by case basis below.\n# Searchable Database of known Comment Spammers http://www.stopforumspam.com/\n
<FilesMatch ".'"'."^(wp-comments-post\.php)".'"'.">\nOrder Allow,Deny\nDeny from 46.119.35.\nDeny from 46.119.45.\nDeny from 91.236.74.\nDeny from 93.182.147.\nDeny from 93.182.187.\nDeny from 94.27.72.\nDeny from 94.27.75.\nDeny from 94.27.76.\nDeny from 193.105.210.\nDeny from 195.43.128.\nDeny from 198.144.105.\nDeny from 199.15.234.\nAllow from all\n</FilesMatch>\n\n# BLOCK MORE BAD BOTS RIPPERS AND OFFLINE BROWSERS", $stringReplace);
		}
		
		// Clean up - replace 3 and 4 multiple newlines with 1 newline
		if ( preg_match('/(\n\n\n|\n\n\n\n)/', $stringReplace, $matches) ) {			
			$stringReplace = preg_replace("/(\n\n\n|\n\n\n\n)/", "\n", $stringReplace);
		}
			file_put_contents($filename, $stringReplace);
		
		if (@$permsHtaccess == '644.' && !in_array(bps_DNS_NS(), $ExcludedHosts) && $options['bps_root_htaccess_autolock'] != 'Off') {
		if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 9) == 'litespeed' || substr($sapi_type, 0, 7) == 'caudium' || substr($sapi_type, 0, 8) == 'webjames' || substr($sapi_type, 0, 3) == 'tux' || substr($sapi_type, 0, 5) == 'roxen' || substr($sapi_type, 0, 6) == 'thttpd' || substr($sapi_type, 0, 6) == 'phttpd' || substr($sapi_type, 0, 10) == 'continuity' || substr($sapi_type, 0, 6) == 'pi3web' || substr($sapi_type, 0, 6) == 'milter') {
		chmod($filename, 0404);
		}}
		print("................BPS Automatic htaccess File Update in Progress. Refresh Your Browser To Clear The BPS Alert.");	
			copy($bps_denyall_htaccess, $bps_denyall_htaccess_renamed);		
		}
		if (strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE")) {		
			//print($section);
		break;
		}
	default:
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! Your site does not appear to be protected by BulletProof Security', 'bulletproof-security').'</strong></font><br><strong>'.__('If you are upgrading BPS - BPS will now automatically update your htaccess files and add any new security filters automatically.', 'bulletproof-security').'</strong><br><strong>'.__('Refresh your Browser to clear this Alert', 'bulletproof-security').'</strong><br>'.__('Any custom htaccess code or modifications that you have made will not be altered/changed. Activating BulletProof Modes again after upgrading BPS is no longer necessary.', 'bulletproof-security').'<br>'.__('In order for BPS to automatically update htaccess files you will need to stay current with BPS plugin updates and install the latest BPS plugin updates when they are available.', 'bulletproof-security').'<br>'.__('If refreshing your Browser does not clear this alert then you will need to create new Master htaccess files with the AutoMagic buttons and Activate All BulletProof Modes.', 'bulletproof-security').'<br>'.__('If your site is in Maintenance Mode your site is protected by BPS and this Alert will remain to remind you to put your site back in BulletProof Mode again.', 'bulletproof-security').'<br>'.__('If your site is in Default Mode then it is not protected by BulletProof Security. Check the BPS', 'bulletproof-security').' <strong><a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a></strong> '.__('to view your BPS Security Status information.', 'bulletproof-security').'</div>';
		echo $text;
	}
}}}
add_action('admin_notices', 'root_htaccess_status_dashboard');


// BPS Update/Upgrade Status Alert in WP Dashboard - wp-admin .htaccess file
// Automatic .htaccess file update for BPS versions from .46.9 to current version
// Check for security filters string BPSQSE-check
function wpadmin_htaccess_status_dashboard() {
global $bps_version;
	$filename = ABSPATH . 'wp-admin/.htaccess';
	$check_string = @file_get_contents($filename);
	$pattern1 = '/(\[|\]|\(|\)|<|>)/s';
	
	if ( !file_exists($filename)) {
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! An htaccess file was NOT found in your wp-admin folder. Check the BPS', 'bulletproof-security').' <a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a> '.__('for more specific information.', 'bulletproof-security').'</strong></font></div>';
	echo $text;
	
	} else {
	
	if (file_exists($filename)) {

switch ($bps_version) {
    case ".47.7": // for Testing
		if (strpos($check_string, "BULLETPROOF .47.7") && strpos($check_string, "BPSQSE-check")) {
			echo '';
		break;
		}
    case ".47.8":
		if (!strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE-check")) {
			chmod($filename, 0644);
			$stringReplace = @file_get_contents($filename);
			$stringReplace = str_replace("BULLETPROOF .46.9", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.1", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.2", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.3", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.4", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.5", "BULLETPROOF .47.8", $stringReplace);
			$stringReplace = str_replace("BULLETPROOF .47.6", "BULLETPROOF .47.8", $stringReplace);		
			$stringReplace = str_replace("BULLETPROOF .47.7", "BULLETPROOF .47.8", $stringReplace);				

		if ( preg_match($pattern1, $stringReplace, $matches) ) {
			$stringReplace = str_replace("RewriteCond %{QUERY_STRING} ^.*(\[|\]|\(|\)|<|>).* [NC,OR]", "RewriteCond %{QUERY_STRING} ^.*(\(|\)|<|>).* [NC,OR]", $stringReplace);		
		}

			file_put_contents($filename, $stringReplace);
			echo '';
		}
		if (strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE-check")) {		
			echo '';
		break;
		}
	default:
	$text = '<div class="update-nag"><font color="red"><strong>'.__('BPS Alert! A valid BPS htaccess file was NOT found in your wp-admin folder', 'bulletproof-security').'</strong></font><br>'.__('If you are upgrading BPS this Alert will go away after you Refresh your Browser.', 'bulletproof-security').'<br>'.__('If you still see this Alert after refreshing your Browser then Activate BulletProof Mode for your wp-admin folder.', 'bulletproof-security').'<br>'.__('BulletProof Mode for the wp-admin folder MUST be activated when you have BulletProof Mode activated for the Root folder.', 'bulletproof-security').'<br>'.__('Check the BPS', 'bulletproof-security').' <strong><a href="admin.php?page=bulletproof-security/admin/options.php#bps-tabs-2">'.__('Security Status page', 'bulletproof-security').'</a></strong> '.__('to view your BPS Security Status.', 'bulletproof-security').'</div>';
	echo $text;
	}
}}}
add_action('admin_notices', 'wpadmin_htaccess_status_dashboard');

// B-Core Security Status inpage display - Root .htaccess
function root_htaccess_status() {
global $bps_version;
	$filename = ABSPATH . '.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 46);
	$check_string = @file_get_contents($filename);	
	
	if ( !file_exists($filename)) {
	$text = '<font color="red">'.__('An htaccess file was NOT found in your root folder', 'bulletproof-security').'</font><br><br>'.__('wp-config.php is NOT htaccess protected by BPS', 'bulletproof-security').'</font><br><br>';
	echo $text;
	
	} else {
	
	if (file_exists($filename)) {
	$text = '<font color="green"><strong>'.__('The htaccess file that is activated in your root folder is:', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	print($section);

switch ($bps_version) {
    case ".47.7": // for Testing
		if (!strpos($check_string, "BULLETPROOF .47.7") && strpos($check_string, "BPSQSE")) {
		$text = '<font color="red"><br><br><strong>'.__('BPS may be in the process of updating the version number in your root htaccess file. Refresh your browser to display your current security status and this message should go away. If the BPS QUERY STRING EXPLOITS code does not exist in your root htaccess file then the version number update will fail and this message will still be displayed after you have refreshed your Browser. You will need to click the AutoMagic buttons and activate all BulletProof Modes again.', 'bulletproof-security').'<br><br>'.__('wp-config.php is NOT htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';
		echo $text;
		}
		if (strpos($check_string, "BULLETPROOF .47.7") && strpos($check_string, "BPSQSE")) {
		$text = '<font color="green"><strong><br><br>&radic; '.__('wp-config.php is htaccess protected by BPS', 'bulletproof-security').'<br>&radic; '.__('php.ini and php5.ini are htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';
		echo $text;
		break;
		}
    case ".47.8":
		if (!strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE")) {
		$text = '<font color="red"><br><br><strong>'.__('BPS may be in the process of updating the version number in your root htaccess file. Refresh your browser to display your current security status and this message should go away. If the BPS QUERY STRING EXPLOITS code does not exist in your root htaccess file then the version number update will fail and this message will still be displayed after you have refreshed your Browser. You will need to click the AutoMagic buttons and activate all BulletProof Modes again.', 'bulletproof-security').'<br><br>'.__('wp-config.php is NOT htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';
		echo $text;
		}
		if (strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE")) {		
		$text = '<font color="green"><strong><br><br>&radic; '.__('wp-config.php is htaccess protected by BPS', 'bulletproof-security').'<br>&radic; '.__('php.ini and php5.ini are htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';
		echo $text;
		break;
		}
	default:
	$text = '<font color="red"><br><br><strong>'.__('Either a BPS htaccess file was NOT found in your root folder or you have not activated BulletProof Mode for your Root folder yet, Default Mode is activated, Maintenance Mode is activated or the version of the BPS Pro htaccess file that you are using is not the most current version or the BPS QUERY STRING EXPLOITS code does not exist in your root htaccess file. Please view the Read Me Help button above.', 'bulletproof-security').'<br><br>'.__('wp-config.php is NOT htaccess protected by BPS', 'bulletproof-security').'</strong></font><br><br>';
	echo $text;
}}}}

// B-Core Security Status inpage display - wp-admin .htaccess
function wpadmin_htaccess_status() {
global $bps_version;
	$filename = ABSPATH . 'wp-admin/.htaccess';
	$section = @file_get_contents($filename, NULL, NULL, 3, 50);
	$check_string = @file_get_contents($filename);	
	
	if ( !file_exists($filename)) {
	$text = '<font color="red"><strong>'.__('An htaccess file was NOT found in your wp-admin folder.', 'bulletproof-security').'<br>'.__('BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
	
	} else {
	
	if (file_exists($filename)) {

switch ($bps_version) {
    case ".47.7":
		if (!strpos($check_string, "BULLETPROOF .47.7") && strpos($check_string, "BPSQSE-check")) {
		$text = '<font color="red"><strong><br><br>'.__('BPS may be in the process of updating the version number in your wp-admin htaccess file. Refresh your browser to display your current security status and this message should go away. If the BPS QUERY STRING EXPLOITS code does not exist in your wp-admin htaccess file then the version number update will fail and this message will still be displayed after you have refreshed your Browser. You will need to activate BulletProof Mode for your wp-admin folder again.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		}
		if (strpos($check_string, "BULLETPROOF .47.7") && strpos($check_string, "BPSQSE-check")) {
		$text = '<font color="green"><strong>'.__('The htaccess file that is activated in your wp-admin folder is:', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		print($section);
		break;
		}
    case ".47.8":
		if (!strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE-check")) {
		$text = '<font color="red"><strong><br><br>'.__('BPS may be in the process of updating the version number in your wp-admin htaccess file. Refresh your browser to display your current security status and this message should go away. If the BPS QUERY STRING EXPLOITS code does not exist in your wp-admin htaccess file then the version number update will fail and this message will still be displayed after you have refreshed your Browser. You will need to activate BulletProof Mode for your wp-admin folder again.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		}
		if (strpos($check_string, "BULLETPROOF .47.8") && strpos($check_string, "BPSQSE-check")) {		
		$text = '<font color="green"><strong>'.__('The htaccess file that is activated in your wp-admin folder is:', 'bulletproof-security').'</strong></font><br>';
		echo $text;
		print($section);
		break;
		}
	default:
	$text = '<font color="red"><strong><br><br>'.__('A valid BPS htaccess file was NOT found in your wp-admin folder. Either you have not activated BulletProof Mode for your wp-admin folder yet or the version of the wp-admin htaccess file that you are using is not the most current version. BulletProof Mode for the wp-admin folder MUST also be activated when you have BulletProof Mode activated for the Root folder. Please view the Read Me Help button above.', 'bulletproof-security').'</strong></font><br>';
	echo $text;
}}}}

// Check if BPS Deny ALL htaccess file is activated for the BPS Master htaccess folder
function denyall_htaccess_status_master() {
	$filename = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
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
	$filename = ABSPATH . '.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('An .htaccess file was found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('An .htaccess file was NOT found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = ABSPATH .'wp-admin/.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('An .htaccess file was found in your /wp-admin folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('An .htaccess file was NOT found in your /wp-admin folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('A default.htaccess file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A default.htaccess file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('A secure.htaccess file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A secure.htaccess file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/maintenance.htaccess';
	if (file_exists($filename)) {
    $text = '<font color="green">&radic; '.__('A maintenance.htaccess file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A maintenance.htaccess file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/bp-maintenance.php';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('A bp-maintenance.php file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A bp-maintenance.php file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/bps-maintenance-values.php';
	if (file_exists($filename)) {
   	$text = '<font color="green">&radic; '.__('A bps-maintenance-values.php file was found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('A bps-maintenance-values.php file was NOT found in the /htaccess folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}
	
	$filename = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
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
	$filename = ABSPATH . '.htaccess';
	if (file_exists($filename)) {
  	$text = '<font color="green">&radic; '.__('An .htaccess file was found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	} else {
    $text = '<font color="red">'.__('An .htaccess file was NOT found in your root folder', 'bulletproof-security').'</font><br>';
	echo $text;
	}

	$filename = ABSPATH . 'wp-admin/.htaccess';
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
     $text = '<font color="red"><strong>'.__('WARNING! BPS requires at least PHP5 to function correctly. Your PHP version is: ', 'bulletproof-security').PHP_VERSION.'</font></strong><br><strong><a href="http://www.ait-pro.com/aitpro-blog/1166/bulletproof-security-plugin-support/bulletproof-security-plugin-guide-bps-version-45#bulletproof-security-issues-problems" target="_blank">'.__(' BPS Guide - PHP5 Solution ', 'bulletproof-security').'</a></strong><br><strong>'.__('The BPS Guide will open in a new browser window. You will not be directed away from your WordPress Dashboard.', 'bulletproof-security').'</strong><br>';
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
	$text = '<font color="red"><strong>'.__('W3 Total Cache is activated, but W3TC .htaccess code was NOT found in your root .htaccess file.', 'bulletproof-security').'</strong></font><br><strong>'.__('W3TC needs to be redeployed by clicking either the auto-install or deploy buttons. If your root .htaccess file is locked then you need to unlock it to allow W3TC to write its htaccess code to your root htaccess file. Click to ', 'bulletproof-security').'<a href="admin.php?page=w3tc_general" >'.__('Redeploy W3TC.', 'bulletproof-security').'</a></strong><br><br>';
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
	$text = '<font color="red"><strong>'.__('WP Super Cache is activated, but either you are not using WPSC mod_rewrite to serve cache files or the WPSC .htaccess code was NOT found in your root .htaccess file.', 'bulletproof-security').'</strong></font><br><strong>'.__('If you are not using WPSC mod_rewrite then just add this commented out line of code in anywhere in your root htaccess file - # WPSuperCache. If you are using WPSC mod_rewrite and the WPSC htaccess code is not in your root htaccess file then click this ', 'bulletproof-security').'<a href="options-general.php?page=wpsupercache&tab=settings" >'.__('Update WPSC link', 'bulletproof-security').'</a> '.__('to go to the WPSC Settings page and click the Update Mod_Rewrite Rules button. If your root .htaccess file is locked then you will need to unlock it to allow WPSC to write its htaccess code to your root htaccess file. Refresh your browser to perform a new htaccess file check after updating WPSC mod_rewrite.', 'bulletproof-security').'</strong><br><br>';
	echo $text;
	} 
	}
	}
	elseif ($return_var != 1 || !is_plugin_active_for_network( 'wp-super-cache/wp-cache.php' )) { // checks if WPSC is NOT active for Single or Network
	if ($bpsSiteUrl == $bpsHomeUrl) {
	if (strpos($string, "WPSuperCache") ) {
	$text = '<font color="red"><strong>'.__('WP Super Cache is deactivated and WPSC .htaccess code - # BEGIN WPSuperCache # END WPSuperCache - was found in your root .htaccess file.', 'bulletproof-security').'</strong></font><br><strong>'.__('If this is just temporary then this warning message will go away when you reactivate WPSC. You will need to set up and reconfigure WPSC again when you reactivate WPSC. If you are planning on uninstalling WPSC the WPSC .htaccess code will be automatically removed from your root .htaccess file when you uninstall WPSC. If you added this commented out line of code in anywhere in your root htaccess file - # WPSuperCache - then delete it and refresh your browser.', 'bulletproof-security').'</strong><br><br>';
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
	$text = '<strong>'.__('Multisite is enabled', 'bulletproof-security').'</strong><br>';
	echo $text;
	} else {
	$text = '<strong>'.__('Multisite is Not enabled', 'bulletproof-security').'</strong><br>';
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
// .47 check - will only check the 7 in position 15 - offset 14
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
// .47 check - will only check the 7 in position 15 - offset 14
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
	$filename_pro = WP_PLUGIN_DIR . '/bulletproof-security/admin/options-bps-pro-modules.php';
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