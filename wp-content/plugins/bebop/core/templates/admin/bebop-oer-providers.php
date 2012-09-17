<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">

<?php
//load the individual admin page
if ( isset( $_GET['provider'] ) ) {
	bebop_extensions::page_loader( $_GET['provider'] );
}
else {
	include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
	<div id='bebop_admin_container'>
		
		<div class='postbox center_margin margin-bottom_22px'>
			<h3>OER Providers</h3>
			<div class="inside">
				Here you can manage installed OER extensions. To enable an extension, click the "enabled" checkbox and click "Save Changes". The "Admin Settings" link can now be clicked to 
				change any configuration settings the extension might require, such as API keys and import limits.
			</div>
		</div>
		
		<form method='post' class='bebop_admin_form no_border'>
			<table class="widefat margin-top_22px margin-bottom_22px">
				<thead>
					<tr>
						<th>Extension Name</th>
						<th>Active Users</th>
						<th>Inactive Users</th>
						<th colspan=>Unverified OERs</th>
						<th colspan=>Verified OERs</th>
						<th colspan=>Deleted OERs</th>
						<th colspan='2'>Options</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Extension Name</th>
						<th>Active Users</th>
						<th>Inactive Users</th>
						<th colspan=>Unverified OERs</th>
						<th colspan=>Verified OERs</th>
						<th colspan=>Deleted OERs</th>
						<th colspan='2'>Options</th>
					</tr>
				</tfoot>
				<tbody>
				<?php
					//loop throught extensions directory and get all extensions
					foreach ( bebop_extensions::get_extension_configs() as $extension ) {
							echo '<tr>
						<td>' . $extension['display_name'] . '</td>
						<td>' . bebop_tables::count_users_using_extension( $extension['name'], 1 ) . '</td>
						<td>' . bebop_tables::count_users_using_extension( $extension['name'], 0 ) . '</td>
						<td><a href="?page=bebop_oers&type=unverified">' . bebop_tables::count_oers_by_extension( $extension['name'], 'unverified' ) . '</a></td>
						<td><a href="?page=bebop_oers&type=verified">' . bebop_tables::count_oers_by_extension( $extension['name'], 'verified' ) . '</a></td>
						<td><a href="?page=bebop_oers&type=deleted">' . bebop_tables::count_oers_by_extension( $extension['name'], 'deleted' ) . '</a></td>
						<td>';
						echo "<label for='bebop_" . $extension['name'] . "_provider'>Enabled:</label><input id='bebop_" .$extension['name'] . "_provider' name='bebop_".$extension['name'] . "_provider' type='checkbox'";
						if ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on' ) {
							echo 'CHECKED';
						}
						echo '></td>
						<td><a class="button auto" style="display:inline-block;margin:6px 0 6px 0;" href="?page=bebop_oer_providers&provider=' . strtolower( $extension['name'] ) . '">Settings</a></td>
					</tr>';
					}
				?>
				</tbody>
			</table>
			<input class='button-primary' type='submit' id='submit' name='submit' value='Save Changes'>
		</form>
	<!-- End bebop_admin_container -->
	</div>
<?php
}