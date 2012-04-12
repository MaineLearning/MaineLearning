<?php
// Direct calls to this file are Forbidden when wp core files are not present
if (!function_exists ('add_action')) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

// BPS Class vars 
if ( !class_exists('Bulletproof_Security') ) :
	class Bulletproof_Security {
	var $hook 		= 'bulletproof-security';
	var $filename	= 'bulletproof-security/bulletproof-security.php';
	var $longname	= 'BulletProof Security Settings';
	var $shortname	= 'BulletProof Security';
	var $optionname = 'BulletProof';
	var $options;
	var $errors;

function bulletproof_save_options() {
	return update_option('bulletproof_security', $this->options);
}

function bulletproof_set_error($code = '', $error = '', $data = '') {
	if ( empty($code) )
		$this->errors = new WP_Error();
	elseif ( is_a($code, 'WP_Error') )
		$this->errors = $code;
	elseif ( is_a($this->errors, 'WP_Error') )
		$this->errors->add($code, $error, $data);
	else
		$this->errors = new WP_Error($code, $error, $data);
}

function bulletproof_get_error($code = '') {
	if ( is_a($this->errors, 'WP_Error') )
	return $this->errors->get_error_message($code);
	return false;
	}
}
endif;
?>