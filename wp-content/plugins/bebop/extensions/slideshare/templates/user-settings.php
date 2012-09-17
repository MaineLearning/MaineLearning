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
$extension = bebop_extensions::get_extension_config_by_name( strtolower( $_GET['provider'] ) );

//put some options into variables
$active = 'bebop_' . $extension['name'] . '_active_for_user';																//the active boolean name
$$active = bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_active_for_user' );	//the value of the boolean

if ( ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on') && ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_key' ) ) ) {
	echo '<h5>' . $extension['display_name'] . ' Settings</h5>
	<p>Generic settings for ' . $extension['display_name'] . '. Here you can select whether content is actively imported into WordPress.</p>';
	
	echo '<form id="settings_form" action="' . $bp->loggedin_user->domain . 'bebop-oers/accounts/?provider=' . $extension['name'] . '" method="post">
	
	<h5>Enable ' . $extension['display_name'] . ' import:</h5>
	<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="1"';  if ( $$active == 1 ) {
		echo 'checked';
	} echo '>
	<label for="yes">Yes</label>
	<input type="radio" name="bebop_' . $extension['name'] . '_active_for_user" id="bebop_' . $extension['name'] . '_active_for_user" value="0"'; if ( $$active == 0 ) {
		echo 'checked';
	} echo '>
	<label for="no">No</label><br>';
	
	echo '<label for="bebop_' . $extension['name'] . '_username">New ' . $extension['display_name'] . ' Username:</label>
	<input type="text" name="bebop_' . $extension['name'] . '_username" value="" size="50"><br>
	
	<div class="button_container"><input class="auto button" type="submit" id="submit" name="submit" value="Save Changes"></div>';
	echo '<div class="clear_both"></div>';
	
	echo '</form>';
	//table of user feeds
	$user_feeds = bebop_tables::get_user_feeds( $bp->loggedin_user->id, $extension['name'] );
	if ( count( $user_feeds ) > 0 ) {
		echo '<h5>Your ' . $extension['display_name'] . ' feeds</h5>';
		echo '<table class="bebop_user_table">
				<tr class="nodata">
					<th>Username</th>
					<th>Options</th>
				</tr>';
		foreach ( $user_feeds as $user_feed ) {
			echo '<tr>
				<td>' . bebop_tables::sanitise_element( $user_feed->meta_value ) . '</td>
				<td><a href="?provider=' . $extension['name'] . '&remove_username=' . $user_feed->meta_value . '">Delete Feed</a></td>
			</tr>';
		}
		echo '</table>';
	}
}
else {
	echo $extension['display_name'] . ' has not yet been configured. Please contact the blog admin to make sure ' . $extension['display_name'] . ' is configured properly.';
}
?>
