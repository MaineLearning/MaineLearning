<?php
/*
 * IMPORTANT - PLEASE READ **************************************************************************
 * All the mechanics to control this plugin are automatically generated from the extension name.	*
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
$extension = bebop_extensions::bebop_get_extension_config_by_name( strtolower( $_GET['provider'] ) );

//put some options into variables
$active = 'bebop_' . $extension['name'] . '_active_for_user';																//the active boolean name
$$active = bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_active_for_user' );	//the value of the boolean

if ( ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on') ) {
	echo '<h5>' . sprintf( __( '%1$s Settings', 'bebop' ), $extension['display_name'] ) . '</h5>
	<p>' . sprintf( __( 'Generic settings for %1$s. Here you can select whether content is actively imported into WordPress.', 'bebop' ), $extension['display_name'] ) . '</p>';
	
	echo '<form id="settings_form" action="' . $bp->loggedin_user->domain . 'bebop/accounts/?provider=' . $extension['name'] . '" method="post">
	
	<label>' . sprintf( __( 'Enable %1$s import', 'bebop' ), $extension['display_name'] ) . ':</label>
	<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="1"';  if ( $$active == 1 ) {
		echo 'checked';
	} echo '>
	<label for="yes">' . __( 'Yes', 'bebop' ) . '</label>
	<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="0"'; if ( $$active == 0 ) {
		echo 'checked';
	} echo '>
	<label for="no">' . __( 'No', 'bebop' ) . '</label><br><br>';
	
	echo '<label for="bebop_' . $extension['name'] . '_username">' . __( 'New Username', 'bebop' ) . ':</label>
	<input type="text" name="bebop_' . $extension['name'] . '_username" value="" size="50"><br><br>
	
	<div class="button_container"><input class="auto button" type="submit" id="submit" name="submit" value="' . __( 'Save Changes', 'bebop' ) . '"></div>';
	
	wp_nonce_field( 'bebop_' . $extension['name'] . '_user_settings' );
	
	echo '<div class="clear_both"></div>';

	echo '</form>';
	//table of user feeds
	$user_feeds = bebop_tables::get_user_feeds( $bp->loggedin_user->id, $extension['name'] );
	if ( count( $user_feeds ) > 0 ) {
		echo '<h5>Your ' . $extension['display_name'] . ' feeds</h5>';
		echo '<table class="bebop_user_table">
				<tr class="nodata">
					<th>' . __( 'Username', 'bebop' ) . '</th>
					<th>' . __( 'Options', 'bebop' ) . '</th>
				</tr>';
		foreach ( $user_feeds as $user_feed ) {
			echo '<tr>
				<td>' . bebop_tables::sanitise_element( $user_feed->meta_value ) . '</td>
				<td><a href="?provider=' . $extension['name'] . '&remove_username=' . $user_feed->meta_value . '">' . __( 'Delete Feed', 'bebop' ) . '</a></td>
			</tr>';
		}
		echo '</table>';
	}
}
else {
	echo sprintf( __( '%1$s has not yet been configured. Please contact the blog admin to make sure %1$s is configured properly.', 'bebop' ), $extension['display_name'] );
}
?>