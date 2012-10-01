<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">

<?php include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
<div id='bebop_admin_container'>
	<div class='postbox center_margin margin-bottom_22px'>
		<h3><?php _e( 'Bebop Log', 'bebop' ); ?></h3>
		<div class="inside">
			<p><?php _e( 'When stuff happens, it is logged here.', 'bebop' ); ?></p>
		</div>
	</div>
	<?php
	$table_row_data = bebop_tables::fetch_table_data( 'bp_bebop_general_log' );
	if ( count( $table_row_data ) > 0 ) {
		?>
		<a class='button-secondary' href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query( $_GET ); ?>&clear_table=true"><?php _e( 'Flush table data', 'bebop' ); ?></a>
		
		<table class="widefat margin-top_22px">
			<thead>
				<tr>
					<th><?php _e( 'Log ID', 'bebop' ); ?></th>
					<th><?php _e( 'Timestamp', 'bebop' ); ?></th>
					<th><?php _e( 'Log Type', 'bebop' ); ?></th>
					<th><?php _e( 'Log Message', 'bebop' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e( '>Log ID', 'bebop' ); ?></th>
					<th><?php _e( 'Timestamp', 'bebop' ); ?></th>
					<th><?php _e( 'Log Type', 'bebop' ); ?></th>
					<th><?php _e( 'Log Message', 'bebop' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				foreach ( $table_row_data as $row_data ) {
				echo '<tr>
					<td>' . bebop_tables::sanitise_element( $row_data->id ) . '</td>' .
					'<td>' . bebop_tables::sanitise_element( $row_data->timestamp ) . '</td>
					<td>' . bebop_tables::sanitise_element( $row_data->type ) . '</td>
					<td>' . bebop_tables::sanitise_element( $row_data->message ) . '</td>
				</tr>';
			}
			?>
			</tbody>
		</table>
		<?php
	}
	else {
		_e( 'No data found in the general log table.', 'bebop' ); 
	}
	?>	
<!-- End bebop_admin_container -->
</div>
