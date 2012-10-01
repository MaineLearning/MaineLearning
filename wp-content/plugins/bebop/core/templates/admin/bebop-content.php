<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">

<?php include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
<div id='bebop_admin_container'>
	
	<div class='postbox center_margin margin-bottom_22px'>
		<h3><?php _e( 'Content', 'bebop' ); ?></h3>
		<div class="inside">
			<p><?php _e( 'Lists all Bebop content in the database by type.', 'bebop' ); ?></p>
		</div>
	</div>
	<?php 
	if ( isset( $_GET['type'] ) ) {
		if ( strtolower( strip_tags( $_GET['type'] == 'unverified' ) ) ) {
			$type = 'unverified';
			$message = __( 'This content has not bee verified by your users.', 'bebop' );
		}
		else if ( strtolower( strip_tags( $_GET['type'] == 'verified' ) ) ) {
			$type = 'verified';
			$message = __( 'This content has been verified and is actively displayed in your users activity streams.', 'bebop' );
		}
		else if ( strtolower( strip_tags( $_GET['type'] == 'deleted' ) ) ) {
			$type = 'deleted';
			$message = __( 'This content has been deleted by your users.', 'bebop' );
		}
	}
	else {
		$type = 'verified';
		$message = __( 'This content has not bee verified by your users.', 'bebop' );
	}
	echo '<a class="button-secondary" href="' . $_SERVER['PHP_SELF'] . '?page=bebop_content&type=unverified">'; _e( 'Unverified Content', 'bebop' ); echo '</a>';
	echo '<a class="button-secondary" href="' . $_SERVER['PHP_SELF'] . '?page=bebop_content&type=verified">'; _e( 'Verified Content', 'bebop' ); echo '</a>';
	echo '<a class="button-secondary" href="' . $_SERVER['PHP_SELF'] . '?page=bebop_content&type=deleted">'; _e( 'Deleted Content', 'bebop' ); echo '</a>';
	
	$contents = bebop_tables::admin_fetch_content_data( $type );
	
	if ( count( $contents ) > 0 ) {
		echo '<h4>' . ucfirst( $type ) . ' '; _e( 'Content', 'bebop' ); echo '</h4>';
		echo $message;
		
		
		echo '<table class="widefat margin-top_22px">
			<thead>
				<tr>
					<th>'; _e( 'Buffer ID', 'bebop'); echo '</th>
					<th>'; _e( 'Secondary ID', 'bebop'); echo '</th>
					<th>'; _e( 'Activity Stream ID', 'bebop'); echo '</th>
					<th>'; _e( 'Username', 'bebop'); echo '</th>
					<th>'; _e( 'Type', 'bebop'); echo '</th>
					<th>'; _e( 'Imported', 'bebop'); echo '</th>
					<th>'; _e( 'Published', 'bebop'); echo '</th>
					<th>'; _e( 'Content', 'bebop'); echo '</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>'; _e( 'Buffer ID', 'bebop'); echo '</th>
					<th>'; _e( 'Secondary ID', 'bebop'); echo '</th>
					<th>'; _e( 'Activity Stream ID', 'bebop'); echo '</th>
					<th>'; _e( 'Username', 'bebop'); echo '</th>
					<th>'; _e( 'Type', 'bebop'); echo '</th>
					<th>'; _e( 'Imported', 'bebop'); echo '</th>
					<th>'; _e( 'Published', 'bebop'); echo '</th>
					<th>'; _e( 'Content', 'bebop'); echo '</th>
				</tr>
			</tfoot>
			<tbody>';
				
				foreach ( $contents as $content ) {
				$extension = bebop_extensions::bebop_get_extension_config_by_name( $content->type );
				echo '<tr>
					<td>' . $content->id . '</td>' .
					'<td>' . $content->secondary_item_id . '</td>' .
					'<td>' . $content->activity_stream_id . '</td>' .
					'<td>' . bp_core_get_username( $content->user_id ) . '</td>' .
					'<td>' . bebop_tables::sanitise_element( $extension['display_name'] ) . '</td>' .
					'<td>' . bp_core_time_since( $content->date_imported ) . '</td>' .
					'<td>' . bp_core_time_since( $content->date_recorded ) . '</td>' .
					'<td class="content">' . bebop_tables::sanitise_element( $content->content ) . '</td>' .
				'</tr>';
				}
			echo '
			</tbody>
		</table>';
	}
	else {
		echo '<h4>' . ucfirst( $type ) . ' '; _e( 'Content', 'bebop' ); echo '</h4>';
		echo '<p>'; _e( 'No content was found in the content manager.', 'bebop' ); echo '</p>';
	}
		
	?>
	<div class="clear"></div>
</div>
<!-- end bebop_admin_container -->