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
			$message = __( 'This content has not been verified by your users.', 'bebop' );
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
		$message = __( 'This content has been verified by your users.', 'bebop' );
	}
	echo '<a class="button-secondary" href="' . $_SERVER['PHP_SELF'] . '?page=bebop_content&type=unverified">' . __( 'Unverified Content', 'bebop' ) . '</a>';
	echo '<a class="button-secondary" href="' . $_SERVER['PHP_SELF'] . '?page=bebop_content&type=verified">' . __( 'Verified Content', 'bebop' ) . '</a>';
	echo '<a class="button-secondary" href="' . $_SERVER['PHP_SELF'] . '?page=bebop_content&type=deleted">' . __( 'Deleted Content', 'bebop' ) . '</a>';
	
	$number_of_rows = bebop_tables::admin_count_content_rows( $type );
	$page_vars = bebop_pagination_vars( 30 );
	$bebop_pagination = bebop_pagination( $number_of_rows, $page_vars['per_page'] );
	
	$contents = bebop_tables::admin_fetch_content_data( $type, $page_vars['page_number'], $page_vars['per_page'] );
	if ( count( $contents ) > 0 ) {
		echo '<h4>' . ucfirst( __( $type, 'bebop' ) ) . ' ' . __( 'Content', 'bebop' ) . '</h4>';
		echo $message;
		
		echo $bebop_pagination;
		
		echo '<table class="widefat margin-top_22px">
			<thead>
				<tr>
					<th>' . __( 'Buffer ID', 'bebop') . '</th>
					<th>' . __( 'Secondary ID', 'bebop') . '</th>
					<th>' . __( 'Activity Stream ID', 'bebop'). '</th>
					<th>' . __( 'Username', 'bebop'). '</th>
					<th>' . __( 'Type', 'bebop'). '</th>
					<th>' . __( 'Imported', 'bebop'). '</th>
					<th>' . __( 'Published', 'bebop'). '</th>
					<th>' . __( 'Content', 'bebop'). '</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>' . __( 'Buffer ID', 'bebop'). '</th>
					<th>' . __( 'Secondary ID', 'bebop'). '</th>
					<th>' . __( 'Activity Stream ID', 'bebop'). '</th>
					<th>' . __( 'Username', 'bebop'). '</th>
					<th>' . __( 'Type', 'bebop'). '</th>
					<th>' . __( 'Imported', 'bebop'). '</th>
					<th>' . __( 'Published', 'bebop'). '</th>
					<th>' . __( 'Content', 'bebop'). '</th>
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
		echo $bebop_pagination;
	}
	else {
		echo '<h4>' . ucfirst( $type ) . ' ' . __( 'Content', 'bebop' ) . '</h4>';
		echo '<p>' . __( 'No content was found in the content manager.', 'bebop' ) . '</p>';
	}
		
	?>
	<div class="clear"></div>
</div>
<!-- end bebop_admin_container -->