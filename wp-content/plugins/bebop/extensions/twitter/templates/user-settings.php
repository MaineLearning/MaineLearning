<?php
/*
 * IMPORTANT - PLEASE READ **************************************************************************
 * All the mechanics to control this plugin are automatically generated from the extension name		*
 * You do not need to modify this page, unless you wish to add additional customisable parameters	*
 * for the extension. Removing/changing any of the pre defined functions will cause import errors,	*
 * and possible other unexpected or unwanted behaviour.												*
 * For information on bebop_tables:: functions, please see bebop/core/bebop-tables.php				*
 * **************************************************************************************************
 */
global $bp;
/*
 * '$extension' controls content on this page and is set to whatever admin-settings.php file is being viewed.
 * i.e. if you extension name is 'my_extension', the value of $extension will be 'my_extension'.
 * The extension has to exist if this page is being included.
 */
$extension = bebop_extensions::get_extension_config_by_name( strtolower( $_GET['provider'] ) );

//put some options into variables
$variable_name = 'bebop_' . $extension['name'] . '_active_for_user';																//the name of the variable
$$variable_name = bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_active_for_user' );	//the value of the variable

if ( ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on') && ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_key' ) ) ) {
	if ( ( bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_oauth_token' ) ) && ( bebop_tables::check_user_meta_exists( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_username' ) ) ) {
		echo '<h5>' . $extension['display_name'] . ' Settings</h5>
		<p>Generic settings for ' . $extension['display_name'] . '. Here you can select whether content is actively imported into WordPress.</p>';
	
		echo '<form id="settings_form" action="' . $bp->loggedin_user->domain . 'bebop-oers/accounts/?provider=' . $extension['name'] . '" method="post">
		<label>Enable ' . $extension['display_name'] . ' import:</label>
		<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="1"';  if ( $$variable_name == 1 ) {
			echo 'checked';
		} echo '>
		<label for="yes">Yes</label>
		<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="0"'; if ( $$variable_name == 0 ) {
			echo 'checked';
		} echo '>
		<label for="no">No</label><br>
		<div class="button_container"><input class="auto button" type="submit" id="submit" name="submit" value="Save Changes"></div>';
		echo '<div class="clear_both"></div>';
			
		if ( bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_oauth_token' ) ) {
			echo '<div class="button_container"><a class="auto button" href="?provider=' . $extension['name'] . '&reset=true">Remove Authorisation</a></div>';
			echo '<div class="clear_both"></div>';
		}
		echo '</form>';
	}
	else {
		echo '<h5>' . $extension['display_name'] . ' Setup</h5>
		You may setup ' . $extension['display_name'] . ' intergration over here.
		Before you can begin using ' . $extension['display_name'] . ' with this site you must authorise on ' . $extension['display_name'] . ' by clicking the link below.';
		
		//oauth
		$OAuth = new bebop_oauth();
		$OAuth->set_request_token_url( $extension['request_token_url'] );
		$OAuth->set_access_token_url( $extension['access_token_url'] );
		$OAuth->set_authorize_url( $extension['authorize_url'] );
		$OAuth->set_callback_url( $bp->loggedin_user->domain . 'bebop-oers/accounts/?provider=' . $extension['name'] );
		$OAuth->set_consumer_key( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_key' ) );
		$OAuth->set_consumer_secret( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_secret' ) );
		
		//get the oauth token
		$requestToken = $OAuth->request_token();
		
		$OAuth->set_request_token( $requestToken['oauth_token'] );
		$OAuth->set_request_token_secret( $requestToken['oauth_token_secret'] );
		
		bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_oauth_token_temp','' . $requestToken['oauth_token'].'' );
		bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_oauth_token_secret_temp','' . $requestToken['oauth_token_secret'].'' );
		
		//get the redirect url for the user
		$redirectUrl = $OAuth->get_redirect_url();
		if ( $redirectUrl ) {
			echo '<div class="button_container"><a class="auto button" href="' . $redirectUrl . '">Start Authorisation</a></div>';
			echo '<div class="clear_both"></div>';
		}
		else {
			echo 'authentication is all broken :(';
		}
	}
}// if ( ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on') && ( bebop_tables::check_option_exists( 'bebop_' . $extension['name'] . '_consumer_key' ) ) ) {
else {
	echo $extension['display_name'] . ' has not yet been configured. Please contact the blog admin to make sure ' . $extension['display_name'] . ' is configured properly.';
}