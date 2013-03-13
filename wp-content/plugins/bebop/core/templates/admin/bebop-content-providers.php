<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">

<?php
//load the individual admin page
if ( isset( $_GET['provider'] ) ) {
	bebop_extensions::bebop_page_loader( $_GET['provider'] );
}
else {
	include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
	<div id='bebop_admin_container'>
		
		<div class='postbox center_margin margin-bottom_22px'>
			<h3><?php _e( 'Content Providers', 'bebop' ); ?></h3>
			<div class="inside">
				<?php _e( 'Here you can manage Bebop\'s extensions. To enable an extension, click the "enabled" checkbox and click the "Save Changes" button. The "Admin Settings" link can now be clicked to 
				change any configuration settings the extension might require, such as API keys and import limits.', 'bebop' ); ?>
			</div>
		</div>
		
		<form method='post' class='bebop_admin_form no_border'>
			<table class="widefat margin-top_22px margin-bottom_22px">
				<thead>
					<tr>
						<th><?php _e( 'Extension Name', 'bebop' ); ?></th>
						<th><?php _e( 'Active Users', 'bebop' ); ?></th>
						<th><?php _e( 'Inactive Users', 'bebop' ); ?></th>
						<th colspan=><?php _e( 'Unverified Content', 'bebop' ); ?></th>
						<th colspan=><?php _e( 'Verified Content', 'bebop' ); ?></th>
						<th colspan=><?php _e( 'Deleted Content', 'bebop' ); ?></th>
						<th colspan='2'><?php _e( 'Options', 'bebop' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'Extension Name', 'bebop' ); ?></th>
						<th><?php _e( 'Active Users', 'bebop' ); ?></th>
						<th><?php _e( 'Inactive Users', 'bebop' ); ?></th>
						<th colspan=><?php _e( 'Unverified Content', 'bebop' ); ?></th>
						<th colspan=><?php _e( 'Verified Content', 'bebop' ); ?></th>
						<th colspan=><?php _e( 'Deleted Content', 'bebop' ); ?></th>
						<th colspan='2'><?php _e( 'Options', 'bebop' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
				<?php
					//loop throught extensions directory and get all extensions
					foreach ( bebop_extensions::bebop_get_extension_configs() as $extension ) {
							echo '<tr>
						<td>' . $extension['display_name'] . '</td>
						<td>' . bebop_tables::count_users_using_extension( $extension['name'], 1 ) . '</td>
						<td>' . bebop_tables::count_users_using_extension( $extension['name'], 0 ) . '</td>
						<td><a href="?page=bebop_content&type=unverified">' . bebop_tables::count_content_by_extension( $extension['name'], 'unverified' ) . '</a></td>
						<td><a href="?page=bebop_content&type=verified">' . bebop_tables::count_content_by_extension( $extension['name'], 'verified' ) . '</a></td>
						<td><a href="?page=bebop_content&type=deleted">' . bebop_tables::count_content_by_extension( $extension['name'], 'deleted' ) . '</a></td>
						<td>';
						echo "<label class='alt' for='bebop_" . $extension['name'] . "_provider'>";  _e( 'Enabled:', 'bebop' ); echo "</label><input id='bebop_" .$extension['name'] . "_provider' name='bebop_".$extension['name'] . "_provider' type='checkbox'";
						if ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on' ) {
							echo 'CHECKED';
						}
						echo '></td>
						<td><a class="button-secondary" style="display:inline-block;margin:6px 0 6px 0;" href="?page=bebop_providers&provider=' . strtolower( $extension['name'] ) . '">' . __( 'Settings', 'bebop' ) . '</a></td>
					</tr>';
					}
				?>
				</tbody>
			</table>
			
			<?php wp_nonce_field( 'bebop_content_provider_settings' ); ?>
			
			<input class='button-primary' type='submit' id='submit' name='submit' value='<?php _e( 'Save Changes', 'bebop' ); ?>'>
		</form>
	<!-- End bebop_admin_container -->
	</div>
<?php
}