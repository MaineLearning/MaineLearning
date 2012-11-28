<p><?php _e( 'Here you can manage your content. Recently imported content is categorised as unverified, items that appear in your activity stream are categorised as verified. The deleted category holds items which you do not want to show in your activity stream.', 'bebop' ); ?>
</p><br>
<div class="button_container"><a class="auto button min_width_100" href="?type=unverified"><?php _e( 'Unverified', 'bebop' ); ?></a></div>
<div class="button_container"><a class="auto button min_width_100" href="?type=verified"><?php _e( 'Verified', 'bebop' ); ?></a></div>
<div class="button_container"><a class="auto button min_width_100" href="?type=deleted"><?php _e( 'Deleted', 'bebop' ); ?></a></div>
<div class="clear_both"></div>
<?php
$type = bebop_get_oer_type();
if ( ! empty( $type ) ) {
	
	
	$number_of_rows = bebop_tables::count_content_rows( $bp->loggedin_user->id, $type );
	$page_vars = bebop_pagination_vars( 30 );
	$bebop_pagination = bebop_pagination( $number_of_rows, $page_vars['per_page'] );
	
	$oers = bebop_get_oers( $type, $page_vars['page_number'], $page_vars['per_page'] );
	if ( count( $oers ) > 0 ) {
		echo '<form id="oer_table" class="bebop_user_form" method="post">';
		echo '<h5>' . ucfirst( $type ) . ' ' . __( 'Content', 'bebop' ) . '</h5>';
		echo $bebop_pagination;
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_all">' . __( 'Select All', 'bebop' ) . '</a></div>';
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_none">' . __( 'Select None', 'bebop' ) . '</a></div>';
		echo '<div class="clear_both"></div>';
		
		echo '<table class="bebop_user_table">
			<tr class="nodata">
				<th>' . __( 'Type', 'bebop' ) . '</th>
				<th>' . __( 'Imported', 'bebop' ) . '</th>
				<th>' . __( 'Published', 'bebop' ) . '</th>
				<th>' . __( 'Content', 'bebop' ) . '</th>
				<th>' . __( 'Select', 'bebop' ) . '</th>
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
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_all">' . __( 'Select All', 'bebop' ) . '</a></div>';
		echo '<div class="button_container button_right"><a class="auto button" rel="#oer_table" href="#select_none">' . __( 'Select None', 'bebop' ) . '</a></div>';
		
		echo '<h5>' . __( 'Action', 'bebop' ) . '</h5>';
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
			
		echo '<div class="button_container"><input class="auto button" type="submit" id="submit" name="submit" value="' . __( 'Save Changes', 'bebop' ) . '"></div>
		</form>';
		echo $bebop_pagination;
	}
	else {
		echo '<p>' . __( 'Unfortunately, we could not find any content for you to manage.', 'bebop' ) . '</p>';
	}
}//End if ( ! empty( $type ) ) {
?>