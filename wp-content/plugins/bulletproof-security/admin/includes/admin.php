<?php
// Direct calls to this file are Forbidden when core files are not present
if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

function bulletproof_security_admin_init() {
	// whitelist BPS DB options 
	register_setting('bulletproof_security_options', 'bulletproof_security_options', 'bulletproof_security_options_validate');
	register_setting('bulletproof_security_options_autolock', 'bulletproof_security_options_autolock', 'bulletproof_security_options_validate_autolock');
	register_setting('bulletproof_security_options_customcode', 'bulletproof_security_options_customcode', 'bulletproof_security_options_validate_customcode');
	register_setting('bulletproof_security_options_customcode_WPA', 'bulletproof_security_options_customcode_WPA', 'bulletproof_security_options_validate_customcode_WPA');
	register_setting('bulletproof_security_options_mynotes', 'bulletproof_security_options_mynotes', 'bulletproof_security_options_validate_mynotes');
	register_setting('bulletproof_security_options_maint', 'bulletproof_security_options_maint', 'bulletproof_security_options_validate_maint');
			
	// Register BPS js
	wp_register_script( 'bps-js', plugins_url('/bulletproof-security/admin/js/bulletproof-security-admin.js'));
				
	// Register BPS stylesheet
	wp_register_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));

	// Create BPS Backup Folder structure - suppressing errors on activation - errors displayed in HUD
	if( !is_dir (WP_CONTENT_DIR . '/bps-backup')) {
		@mkdir (WP_CONTENT_DIR . '/bps-backup/master-backups', 0755, true);
		@chmod (WP_CONTENT_DIR . '/bps-backup/', 0755);
		@chmod (WP_CONTENT_DIR . '/bps-backup/master-backups/', 0755);
	}
	
	// Create the BPS Backup folder Deny all .htaccess file - recursive will protect all /bps-backup subfolders
	$bps_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_ARHtaccess = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	
	if (!file_exists($bps_ARHtaccess)) {
	@copy($bps_denyall_htaccess, $bps_ARHtaccess);
	}

	// Create logs folder
	if( !is_dir (WP_CONTENT_DIR . '/bps-backup/logs')) {
		@mkdir (WP_CONTENT_DIR . '/bps-backup/logs', 0755, true);
		@chmod (WP_CONTENT_DIR . '/bps-backup/logs/', 0755);
	}

	// Create the Security / HTTP error log in /logs
	$bpsProLog = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/http_error_log.txt';
	$bpsProLogARQ = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
	if (!file_exists($bpsProLogARQ)) {
	@copy($bpsProLog, $bpsProLogARQ);
	}	

	// Load scripts and styles only on BPS specified pages
	add_action('load-bulletproof-security/admin/options.php', 'bulletproof_security_load_settings_page');

}

// BPS Menu
function bulletproof_security_admin_menu() {
	if (is_multisite() && !is_super_admin()) {
		$bpsSuperAdminsError = 'Only Super Admins can access BPS';
  		return $bpsSuperAdminsError;
		} else {
	//if (function_exists('add_menu_page')){
	add_menu_page(__('BulletProof Security ~ htaccess Core', 'bulletproof-security'), __('BPS Security', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/options.php', '', plugins_url('bulletproof-security/admin/images/bps-icon-small.png'));
	add_submenu_page('bulletproof-security/admin/options.php', __('BulletProof Security ~ htaccess Core', 'bulletproof-security'), __('BPS Settings', 'bulletproof-security'), 'manage_options', 'bulletproof-security/admin/options.php' );
}}

// Loads Settings for H-Core and P-Security
// Enqueue BPS scripts and styles
function bulletproof_security_load_settings_page() {
	global $bulletproof_security;
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_script('jquery-form');
	//wp_enqueue_script('swfobject');
	wp_enqueue_script('bps-js');
	  	
	// Engueue BPS stylesheet
	wp_enqueue_style('bps-css', plugins_url('/bulletproof-security/admin/css/bulletproof-security-admin-blue.css'));
}

function bulletproof_security_install() {
	global $bulletproof_security;
	$previous_install = get_option('bulletproof_security_options');
	if ( $previous_install ) {
	if ( version_compare($previous_install['version'], '.47.8', '<') )
	remove_role('denied');
	}
}

// Deactivation - remove/delete nothing at this point
function bulletproof_security_deactivation() {
// nothing needs to removed on deactivation for now
}

// Uninstall - do not unlink .htaccess files on uninstall to prevent catastrophic user errors
function bulletproof_security_uninstall() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php');
	$options = get_option('bulletproof_security_options');
	delete_option('bulletproof_security_options');
	delete_option('bulletproof_security_options_customcode');
	delete_option('bulletproof_security_options_customcode_WPA');
	delete_option('bulletproof_security_options_maint');
	delete_option('bulletproof_security_options_mynotes');
	delete_option('bulletproof_security_options_autolock');
}

// Validate BPS options 
function bulletproof_security_options_validate($input) {  
	$options = get_option('bulletproof_security_options');  
	$options['bps_blank'] = wp_filter_nohtml_kses($input['bps_blank']);
			
	return $options;  
}

// Validate BPS options - Maintenance Mode Form 
function bulletproof_security_options_validate_maint($input) {  
	$options = get_option('bulletproof_security_options_maint');  
	$options['bps-site-title'] = wp_filter_nohtml_kses($input['bps-site-title']);
	$options['bps-message-1'] = wp_filter_nohtml_kses($input['bps-message-1']);
	$options['bps-message-2'] = wp_filter_nohtml_kses($input['bps-message-2']);
	$options['bps-retry-after'] = wp_filter_nohtml_kses($input['bps-retry-after']);
	$options['bps-background-image'] = wp_filter_nohtml_kses($input['bps-background-image']);
		
	return $options;  
}

// Validate BPS options - Options.php - Edit/Uploads/Downloads page - Root .htaccess file AutoLock 
function bulletproof_security_options_validate_autolock($input) {  
	$options = get_option('bulletproof_security_options_autolock');  
	$options['bps_root_htaccess_autolock'] = wp_filter_nohtml_kses($input['bps_root_htaccess_autolock']);
		
	return $options;  
}

// Validate BPS options - BPS Custom Code - Root .htaccess
function bulletproof_security_options_validate_customcode($input) {  
	$options = get_option('bulletproof_security_options_customcode');  
	$options['bps_customcode_one'] = esc_html($input['bps_customcode_one']);
	$options['bps_customcode_two'] = esc_html($input['bps_customcode_two']);
	$options['bps_customcode_three'] = esc_html($input['bps_customcode_three']);
		
	return $options;  
}

// Validate BPS options - BPS Custom Code - WP-admin .htaccess
function bulletproof_security_options_validate_customcode_WPA($input) {  
	$options = get_option('bulletproof_security_options_customcode_WPA');  
	$options['bps_customcode_one_wpa'] = esc_html($input['bps_customcode_one_wpa']);
	$options['bps_customcode_two_wpa'] = esc_html($input['bps_customcode_two_wpa']);
		
	return $options;  
}

// Validate BPS options - BPS "My Notes" settings 
function bulletproof_security_options_validate_mynotes($input) {  
	$options = get_option('bulletproof_security_options_mynotes');  
	$options['bps_my_notes'] = esc_html($input['bps_my_notes']);
		
	return $options;  
}

?>