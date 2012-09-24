<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">

<?php include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
<div id='bebop_admin_container'>
	<div class='postbox center_margin margin-bottom_22px'>
		<h3>Bebop Log</h3>
		<div class="inside">
			When stuff happens, it is logged here. 
		</div>
	</div>
	<?php
	$table_row_data = bebop_tables::fetch_table_data( 'bp_bebop_general_log' );
	if ( count( $table_row_data ) > 0 ) {
		?>
		<a class='button-secondary' href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query( $_GET ); ?>&clear_table=true">Flush table data</a>
		
		<table class="widefat margin-top_22px">
			<thead>
				<tr>
					<th>Log ID</th>
					<th>Timestamp</th>
					<th>Log Type</th>
					<th>Log Message</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Log ID</th>
					<th>Timestamp</th>
					<th>Log Type</th>
					<th>Log Message</th>
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
		echo 'No data found in the general log table.';
	}
	?>	
<!-- End bebop_admin_container -->
</div>
