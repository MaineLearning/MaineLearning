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

$query_string = bp_action_variables();
if ( ! empty( $query_string ) ) {
	$provider = $query_string[0];
}
$extension = bebop_extensions::bebop_get_extension_config_by_name( strtolower( $provider ) );

//put some options into variables
$variable_name = 'bebop_' . $extension['name'] . '_active_for_user';																//the name of the variable
$$variable_name = bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_active_for_user' );	//the value of the variable

if ( ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on') && ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_key' ) ) ) {
	if ( ( bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_oauth_token' ) ) && ( bebop_tables::check_user_meta_exists( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_username' ) ) ) {
		echo '<h5>' . sprintf( __( '%1$s Settings', 'bebop' ), $extension['display_name'] ) . '</h5>
		<p>' . sprintf( __( 'Generic settings for %1$s. Here you can select whether content is actively imported into WordPress.', 'bebop' ), $extension['display_name'] ) . '</p>';
	
		echo '<form id="settings_form" action="' . $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() . '/' . $extension['name'] . '" method="post">
		<label>' . sprintf( __( 'Enable %1$s import', 'bebop' ), $extension['display_name'] ) . ':</label>
		<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="1"';  if ( $$variable_name == 1 ) {
			echo 'checked';
		} echo '>
		<label for="yes">' . __( 'Yes', 'bebop' ) . '</label>
		<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="0"'; if ( $$variable_name == 0 ) {
			echo 'checked';
		} echo '>
		<label for="no">' . __( 'No', 'bebop' ) . '</label><br><br>
		<div class="button_container"><input class="button" type="submit" id="submit" name="submit" value="Save Changes"></div>';
		
		wp_nonce_field( 'bebop_' . $extension['name'] . '_user_settings' );
		
		echo '<div class="clear_both"></div>';
			
		if ( bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_oauth_token' ) ) {
			echo '<div class="button_container"><a class="button" href="' . $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() . '/' . $extension['name'] . '?reset=true">' . __(' Remove Authorisation', 'bebop') . '</a></div>';
			echo '<div class="clear_both"></div>';
		}
		echo '</form>';
	}
	else {
		echo '<h5>' . sprintf( __( '%1$s Setup', 'bebop' ), $extension['display_name'] ) . '</h5>
		<p>' . sprintf( __( 'You can setup %1$s integration here.', 'bebop' ), $extension['display_name'] ) . '</p>
		</p>' . sprintf( __( 'Before you can begin using %1$s with this site you must authorise on %1$s by clicking the link below.', 'bebop' ), $extension['display_name'] ) . '</p>';
		
		//oauth
		$OAuth = new bebop_oauth();
		$OAuth->set_request_token_url( $extension['request_token_url'] );
		$OAuth->set_access_token_url( $extension['access_token_url'] );
		$OAuth->set_authorize_url( $extension['authorize_url'] );
		$OAuth->set_callback_url( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() . '/' . $extension['name'] );
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
			echo '<div class="button_container"><a class="button" href="' . $redirectUrl . '">' . __(' Start Authorisation', 'bebop') . '</a></div>';
			echo '<div class="clear_both"></div>';
		}
		else {
			_e( 'authentication is all broken :(', 'bebop' );
		}
	}
}// if ( ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on') && ( bebop_tables::check_option_exists( 'bebop_' . $extension['name'] . '_consumer_key' ) ) ) {
else {
	echo sprintf( __( '%1$s has not yet been configured. Please contact the blog admin to make sure %1$s is configured properly.', 'bebop' ), $extension['display_name'] );
}