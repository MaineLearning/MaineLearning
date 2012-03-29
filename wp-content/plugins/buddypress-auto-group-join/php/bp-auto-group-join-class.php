<?php
/**********
* Auto Group Join Class File 
* Defines functions and main functionality of plugin. 
* Instantiated in the main plugin file after loading class file.
**********/
class BuddyPressAutoGroupJoinObject {
	var $adminOptionsName = "BPAutoGroupJoinAdminOptions";
	
	/**********
	* get_admin_options function
	* - Loads admin options for plugin.
	* - Defines default options for plugin.
	* - Returns options array.
	**********/
	function get_admin_options() {
		$autoGroupJoinAdminOptions = array('disableAutoNotification'=>'Yes');
		$devOptions = get_option($this->adminOptionsName);
		if(!empty($devOptions)) {
			foreach ($devOptions as $key => $option) $autoGroupJoinAdminOptions[$key] = $option;
		}
		update_option($this->adminOptionsName, $autoGroupJoinAdminOptions);
		return $autoGroupJoinAdminOptions;
	}

	/***********
	* display_admin_page function
	* - Displays the admin page as well as processing changes.
	* - Will update database if needed.
	***********/
	function display_admin_page() {
		global $wpdb, $bp;
		$saved_message = __( 'Options have beed updated.', 'bpAutoGroupJoin' );
		$not_saved_message = __( 'No options have been updated.', 'bpAutoGroupJoin' );
		
		// Only display admin panel if groups are active. The function groups_are_active is defined in the init file.
		if ( groups_are_active() ) {
		
			$ouput_message = "";								// initialize an output message variable
			
			// If our form has been submitted, then handle the input, otherwise just output the form.
			if ( isset($_POST['agj-submit']) ) {
				$autojoin = array();							// initialize an empty array to hold group id's we want to add users to.
				
				// SELECT groups' IDs and if they are currently auto joined from the buddypress groups table.
				$query_results = $wpdb->get_results("SELECT id, auto_join FROM {$bp->groups->table_name}");
				
				foreach ($query_results as $group_item) {		// Loop through each group to see if the check box has been changed.
					$post_option = (isset($_POST['aj_'.$group_item->id])) ? 1 : 0;
					if ($post_option != $group_item->auto_join) {	// If checkbox has changed, update database.
						$mysql = "UPDATE {$bp->groups->table_name} SET auto_join='{$post_option}' WHERE id='{$group_item->id}'";
						$wpdb->query($mysql);
						$output_message = $saved_message;		// Change our output message.
						if ( $post_option == 1 ) {				// Updated the auto_join field to a "1" therefore we have added a group to be "auto-joined"
							$autojoin[] = $group_item->id;		// Add the group id to the list to be auto-joined.
						}
					}
				}
				
				// If the form was submitted with no changes, set the output message to the not_saved message.
				if ( $output_message != $saved_message) $output_message = $not_saved_message;
				
				if ( count($autojoin) > 0 ) {						// If we have groups to join people to then loop the users and add them to the groups
					$utn = $wpdb->users;							// Create a shortcut variable for wordpress users table.
					$gmtn = $bp->groups->table_name . "_members";	// Create a shortcut variable for buddypress groups_members table.
				
					foreach ( $autojoin as $group_id ) {	
						// Create a sql query that selects only users that are not already members of the group we wish to auto join. This greatly 
						// reduces demand on the system instead of looping through each user for each group. 
						$mysql = "SELECT users.ID FROM $utn AS users LEFT JOIN $gmtn AS bp ON users.ID=bp.user_id AND bp.group_id=$group_id WHERE bp.id IS NULL";
						$results = $wpdb->get_results( $mysql);
						foreach ( $results as $user) {
							groups_accept_invite($user->ID, $group_id);
						}
					}
				}
				
				// Wrap our output message with the updated class div.
				$output_message = '<div class="updated"><p><strong>' . $output_message . '</strong></p></div><br />'."\n";
			}
		
			// display the Auto Group Join Admin Menu page.
	 		if ( bp_has_groups('type=alphabetical&max=false&per_page=99999') ) {
				echo "<h2>" . __('BuddyPress Auto Group Join Administration', 'bpAutoGroupJoin') . "</h2>";
				echo $output_message;
  				$action_url = $_SERVER['REQUEST_URI'];
  				$autojoin = array();
  				$query_results = $wpdb->get_results("SELECT id, auto_join FROM {$bp->groups->table_name}");
  				foreach ($query_results as $group_item) {
  					$autojoin[$group_item->id] = $group_item->auto_join;
  				}
  				echo '<form action="'.$action_url.'" method="post">';
  				_e( 'Check the boxes next to the groups you would like to have people "auto-join". Checking the boxes will add all current users to the selected group(s). It will also enable newly registered users to auto join the same group(s).', 'bpAutoGroupJoin' ); 
  				echo "<br /><br /><table class=\"widefat\" style=\"width:95%;\"><thead><tr><th>" . __( 'Group Name', 'bpAutoGroupJoin' ) . "</th><th>" . __( 'Status', 'bpAutoGroupJoin' ) . "</th><th>" . __( 'Auto Join', 'bpAutoGroupJoin' ) . "</th></tr></thead>";
  				while ( bp_groups() ) : bp_the_group(); ?>
  					<?php $is_checked = ($autojoin[bp_get_group_id()]) ? "checked" : ""; ?>
  					<tr>
  						<td><?php bp_group_name() ?></td><td><?php bp_group_status() ?></td><td><input type="checkbox" name="aj_<?php bp_group_id() ?>" value="1" <?php echo $is_checked ?> /></td>
  					</tr>
  					<?php endwhile; ?>
  				</table>
					<p class="submit">
  						<input class="button-primary" type="submit" name="agj-submit" value="<?php _e( 'Save Changes', 'bpAutoGroupJoin' ); ?>" />
					</p>
  				</form>
  				<?php
			} else { 			// If groups are active, but none have been created yet, this message will be presented.
				echo '<div id="message" class="info">';
				echo '<p>' . __('There were no groups found.', 'bpAutoGroupJoin'). '</p></div>'."\n";
			}
	
		} else {				// If groups are not active, then this message will be presented.
			echo "<h2>" . __('BuddyPress Auto Group Join Administration', 'bpAutoGroupJoin') . "</h2>";
			echo '<div id="message" class="updated">';
			echo '<p>' . __('BuddyPress Groups are currently Disabled. Please enable groups in the BuddyPress - Component Setup Menu.', 'bpAutoGroupJoin'). '</p></div>'."\n";
		}
	
	}
	
}

?>
