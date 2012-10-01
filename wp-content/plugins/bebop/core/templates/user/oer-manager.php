<p><?php _e( 'Here you can manage your OERs. Recently imported OERs are categorised as unverified, items that appear in your activity stream are categorised as verified. The deleted category holds items which you do not want to show in your activity stream.', 'bebop' ); ?>
</p><br>
<div class="button_container"><a class="auto button min_width_100" href="?type=unverified"><?php _e( 'Unverified', 'bebop' ); ?></a></div>
<div class="button_container"><a class="auto button min_width_100" href="?type=verified"><?php _e( 'Verified', 'bebop' ); ?></a></div>
<div class="button_container"><a class="auto button min_width_100" href="?type=deleted"><?php _e( 'Deleted', 'bebop' ); ?></a></div>
<div class="clear_both"></div>
<?php
$type = bebop_get_oer_type();
if ( ! empty( $type ) ) {
	$oers = bebop_get_oers( $type );
	
	if ( count( $oers ) > 0 ) {
		echo '<form id="oer_table" class="bebop_user_form" method="post">';
		echo '<h5>' . ucfirst( $type ) . ' '; _e( 'OERs', 'bebop' ); echo '</h5>';
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_all">'; _e( 'Select All', 'bebop' ); echo '</a></div>';
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_none">'; _e( 'Select None', 'bebop' ); echo '</a></div>';
		echo '<div class="clear_both"></div>';
		
		echo '<table class="bebop_user_table">
			<tr class="nodata">
				<th>'; _e( 'Type', 'bebop' ); echo '</th>
				<th>'; _e( 'Imported', 'bebop' ); echo '</th>
				<th>'; _e( 'Published', 'bebop' ); echo '</th>
				<th>'; _e( 'Content', 'bebop' ); echo '</th>
				<th>'; _e( 'Select', 'bebop' ); echo '</th>
			</tr>';
		
		foreach ( $oers as $oer ) {
			echo '<tr>' .
				'<td><label for="' . $oer->secondary_item_id . '">' . bebop_tables::sanitise_element( $oer->type ) . '</label></td>' .
				'<td><label for="' . $oer->secondary_item_id . '">' . bebop_tables::sanitise_element( bp_core_time_since( $oer->date_imported ) ) . '</label></td>' .
				'<td><label for="' . $oer->secondary_item_id . '">' . bp_core_time_since( $oer->date_recorded ) . '</label></td>' .
				'<td class="content"><label for="' . $oer->secondary_item_id . '">' . bebop_tables::sanitise_element( $oer->content, $allow_tags = true ) . '</label></td>' .
				"<td class='checkbox_container'><label for='" . $oer->secondary_item_id . "'><div class='checkbox'><input type='checkbox' id='" . $oer->secondary_item_id . "' name='" . $oer->secondary_item_id ."'></div></label></td>" .
			'</tr>';
		}
		echo '</table>';
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_all">'; _e( 'Select All', 'bebop' ); echo '</a></div>';
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_none">'; _e( 'Select None', 'bebop' ); echo '</a></div>';
		
		echo '<h5>'; _e( 'Action', 'bebop' ); echo '</h5>';
		$verify_oer_option = '<label class="alt" for="verify">' . __( 'Verify', 'bebop' ) . ':</label><input type="radio" name="action" id="verify" value="verify"><br>';
		$delete_oer_option = '<label class="alt" for="delete">' . __( 'Delete', 'bebop' ) . ':</label><input type="radio" name="action" id="delete" value="delete"><br>';
		$undelete_oer_option  = '<label class="alt" for="undelete">' . __( 'Undelete', 'bebop' ) . ':</label><input type="radio" name="action" id="undelete" value="undelete"><br>';
		
		if ( $type == 'unverified' ) {
			echo $verify_oer_option . $delete_oer_option;
		}
		else if ( $type == 'verified' ) {
			echo $delete_oer_option;
		}
		else if ( $type == 'deleted' ) {
			echo $undelete_oer_option;
		}
			
		echo '<div class="button_container"><input class="auto button" type="submit" id="submit" name="submit" value="'; _e( 'Save Changes', 'bebop' ); echo '"></div>
		</form>';
	}
	else {
		_e( '<p>Unfortunately, we could not find any oers for you to manage.</p>', 'bebop' );
	}
}//End if ( ! empty( $type ) ) {
?>