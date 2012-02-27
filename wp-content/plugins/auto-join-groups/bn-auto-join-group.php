<?php
/**
 * Plugin Name: Auto Group Join
 * Plugin Description: Members automatically are joined to groups based on a profile field
 * Author: Brent Layman
 * Author URI: http://buglenotes.com
 * Plugin URI: http://buglenotes.com
 * Version: 1.0
 */

/*
Place this folder in your plugins folder.  In the admin panel, configure under "Site Admin -> Profile->Group Links"

If you know the profile field name and the group name are not exact matches, use the group_pre_regex and group_post_regex fields.
	a. For example, say you have a profile field for "Graduation Year" where an option is "1987".  The corresponding group is "USMA 1987".  So, in the 
		group_pre_regex field put "USMA%".  (yes ... put the % into the db field).
	b. Another example, say you have a profile field for "Type of Fishing" where an option is "Fly".  The corresponding group is "Fly Fishing".  So, in the 
		group_post_regex field put "%Fishing".
	c. If the profile options exactly match the group names, leave group_pre_regex and group_post_regex blank.
*/
    
function addHeaderCode() {
   echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/bn-auto-join-group/css/bn-auto-join-group.css" />' . "\n";
   }

 

 
function auto_join_group_check_installed() {	
	global $wpdb, $bp;
	
	if ( is_site_admin() ) {
		/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
		if ( !$wpdb->get_var("SHOW COLUMNS FROM {$bp->profile->table_name_fields} LIKE 'group_link'")  ) {
			$mysql = "ALTER TABLE {$bp->profile->table_name_fields} ADD `group_link` BINARY NOT NULL DEFAULT '0'";
			$wpdb->query($mysql);
		}
		if ( !$wpdb->get_var("SHOW COLUMNS FROM {$bp->profile->table_name_fields} LIKE 'group_pre_regex'")  ) {
			$mysql = "ALTER TABLE {$bp->profile->table_name_fields} ADD `group_pre_regex` varchar(100) NOT NULL";
			$wpdb->query($mysql);
		}
		if ( !$wpdb->get_var("SHOW COLUMNS FROM {$bp->profile->table_name_fields} LIKE 'group_post_regex'")  ) {
			$mysql = "ALTER TABLE {$bp->profile->table_name_fields} ADD `group_post_regex` varchar(100) NOT NULL";
			$wpdb->query($mysql);
		}
	}
}
add_action('admin_head', 'addHeaderCode');
add_action( 'admin_menu', 'auto_join_group_check_installed' ); 

// Now that we have the fields we need, let's do the admin menu
function auto_join_group_plugin_menu() {
  add_submenu_page( 'wpmu-admin.php', __("Link Profile->Groups", 'buddypress'), __("Profile->Groups Link", 'buddypress'), 1, __FILE__, "auto_join_group_plugin_options" );
}

// Here's the actual admin menu 
function auto_join_group_plugin_options() {
	global $wpdb, $bp;
	
	if ( isset($_GET['pre_regex']) && isset($_GET['post_regex']) && isset($_GET['mode']) && isset($_GET['field_id']) && 'save' == $_GET['mode'] ) {
		// save the changes to the database!
		if (isset($_GET['link'])) { $group_link = 1; } else { $group_link = 0; }
		
		$sql = $wpdb->prepare("UPDATE {$bp->profile->table_name_fields} SET group_link = %d, group_pre_regex = %s, group_post_regex = %s WHERE id = %d", $group_link, $_GET['pre_regex'], $_GET['post_regex'], $_GET['field_id']);
		if ( !$wpdb->query($sql) ) {
			echo "<strong>Update Failed (or no fields were changed)</strong><hr />";
		} else {
			echo "<strong>Update Successful!</strong><hr />";
			// Now ... let's link existing users to the group!!!!
				// get list of users
				if (isset($_GET['link'])) {
					$users = $wpdb->get_results("SELECT user_id FROM {$bp->profile->table_name_data} WHERE field_id = " . $_GET['field_id']);
					foreach ($users as $user) {
						bn_auto_group_join($user->user_id, 'myid');
					}
				}
			
		}
	} 
	
	if ( isset($_GET['mode']) && isset($_GET['field_id']) && 'edit' == $_GET['mode'] ) {
		// edit one of the profile linking options
		$profiles = $wpdb->get_results("SELECT name, id, group_link, group_pre_regex, group_post_regex FROM {$bp->profile->table_name_fields} WHERE id = " . $_GET['field_id']);
		?>
		<form method="get" name="auto-join" action="wpmu-admin.php">
		<input type="hidden" name="field_id" value="<?php echo $_GET['field_id']; ?>">
		<input type="hidden" name="mode" value="save">
		<input type="hidden" name="page" value="bn-auto-join-group/bn-auto-join-group.php">
		<table class="form-table"><?php
		foreach ($profiles as $profile)  { ?>
			<tr><td><strong>Profile Field Name:</strong></td><td align="left"><strong><?php echo $profile->name; ?></strong></td></tr>
			<tr><td>Link to Groups:</td><td align="left"><input type="checkbox" name="link" <?php if ($profile->group_link == 0) { echo "No"; } else { echo "checked"; } ?>></td></tr>
			<tr><td>Pre Matching:</td><td align="left"><input type="text" name="pre_regex" maxlength="100" size="15" value="<?php echo $profile->group_pre_regex; ?>"></td></tr>
			<tr><td>Post Matching:</td><td align="left"><input type="text" name="post_regex" maxlength="100" size="15" value="<?php echo $profile->group_post_regex; ?>"></td></tr>
			<tr><td colspan="2" align="left"><input type="submit" value="Save"></td></tr>
			<tr><td colspan="2" align="left"><strong>DIRECTIONS</strong><br />
			<strong>1.</strong> Check the "Link to Groups" box if you want to try to automatically join members to groups based on this profile field.<br /><br />
			<strong>2.</strong> If you know the profile field name and the group name are not exact matches, use the group_pre_regex and group_post_regex fields.<br />
				&nbsp;&nbsp;<strong>a.</strong> For example, say you have a profile field for "Graduation Year" where an option is "1987".  The corresponding group is "USMA 1987".  So, in the 
					"Pre Matching" field put "USMA%".  (yes ... put the % into the db field).<br />
				&nbsp;&nbsp;<strong>b.</strong> Another example, say you have a profile field for "Type of Fishing" where an option is "Fly".  The corresponding group is "Fly Fishing".  So, in the 
					"Post Matching" field put "%Fishing".<br />
				&nbsp;&nbsp;<strong>c.</strong> If the profile options exactly match the group names, leave "Pre Matching" and "Post Matching" blank.<br /><br />
			<strong>3.</strong> Once you save your changes, if the "Link to Groups" box is checked, all existing wpmu members will be added to the matching groups!
			</td></tr>
		 <?php
		}
		?></table><?php
	} else {
		// get profile fields with group linking
		$profiles = $wpdb->get_results("SELECT name, id, group_link, group_pre_regex, group_post_regex FROM {$bp->profile->table_name_fields} WHERE parent_id = 0 AND (type = 'selectbox' OR type = 'textbox')");
		echo "<table class=\"widefat\"><thead><tr><td colspan='4''><strong>Click edit</strong> on the Profile field you'd like to link to a group.<br /><br />
			<strong>NOTE</strong> - currently only 'selectbox' and 'textbox' type profile fields are supported!</td></tr>
			<tr><td colspan='5'>&nbsp;</td></tr>
			<tr><th>Profile Field Name</th><th>Linked?</th><th>Pre Matching</th><th>Post Matching</th><th>&nbsp;</th></tr>";
		foreach ($profiles as $profile)  { ?>
			<tr>
				<td><?php echo $profile->name; ?></td>
				<td style="text-align:center;"><?php echo ($profile->group_link == 1) ? '<strong>YES</strong>' : 'No'; ?></td>
				<td><?php echo $profile->group_pre_regex; ?></td>
				<td><?php echo $profile->group_post_regex; ?></td>
				<td><a href="wpmu-admin.php?page=bn-auto-join-group/bn-auto-join-group.php&amp;field_id=<?php echo $profile->id; ?>&amp;mode=edit"><?php _e( 'Edit', 'buddypress' ); ?></a></td>
			</tr>
			 <?php
		}
		echo "</table>";
	}
}

add_action('admin_menu', 'auto_join_group_plugin_menu');

 
 
// automatically join users to groups based on profile fields when the user modifies his profile or activates his account
function bn_auto_group_join($userid, $x = 0, $y = 0){
	global $wpdb, $bp, $user_id;
	// get the user id
	if ($bp->loggedin_user->id && $x !== 'myid') {
		$userid =  $bp->loggedin_user->id ; // current user if logged in.  On activation, userid sent with do_action
	}
	
	// get profile fields with group linking
	$profiles = $wpdb->get_results("SELECT * FROM {$bp->profile->table_name_fields} WHERE group_link = 1");
	
	foreach ($profiles as $profile)  {
				
		// see what the person has for that field
		$profileinfo = $wpdb->get_results("SELECT value FROM {$bp->profile->table_name_data} WHERE user_id = $userid AND field_id = $profile->id");
		foreach ($profileinfo as $profilevalue ) {
			
			// see if we can match the group
			$groupmatches = $wpdb->get_results("SELECT * FROM {$bp->groups->table_name} WHERE name like '$profile->group_pre_regex$profilevalue->value$profile->group_post_regex'");
			foreach ($groupmatches as $groupmatch) {
				
				// see if the user is already in the group
				if ( !BP_Groups_Member::check_is_member( $userid, $groupmatch->id ) ) {
					// make sure the user isn't banned from the group!
					if ( !groups_is_user_banned( $userid, $group->id ) ) {
						// add the group already!
							$group_id = $groupmatch->id;
							$user_id = $userid;
						      if ( groups_check_user_has_invite( $user_id, $group_id ) ) {
								groups_delete_invite( $user_id, $group_id );
								}
							  
							 $new_member = new BP_Groups_Member;
							 $new_member->group_id = $group_id;
							 $new_member->inviter_id = 0;
							 $new_member->user_id = $user_id;
							 $new_member->is_admin = 0;
							 $new_member->user_title = '';
							 $new_member->date_modified = time();
							 $new_member->is_confirmed = 1;
							 
							 if ( !$new_member->save() ) {
							 	return false;
								}
								
							// Should I add this to the activity stream?  left off for now
							 
							/* Modify group meta */
							groups_update_groupmeta( $group_id, 'total_member_count', (int) groups_get_groupmeta( $group_id, 'total_member_count') + 1 );
							groups_update_groupmeta( $group_id, 'last_activity', time() );
 
					}

				}
			}
		}
		
	}
	

}
// below hook is located in bp-xprofile.php.  Add user to group when profile is edited
add_action( 'xprofile_updated_profile', 'bn_auto_group_join', 1, 1 );
// below hook is located in wpmu-functions.php.  Add user to group when account is activated
add_action( 'wpmu_activate_user', 'bn_auto_group_join', 12, 3 );


?>
