<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">

<?php include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
<div id='bebop_admin_container'>
	<div class='bebop_admin_box'>
		<img class='bebop_logo' src="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_logo.png';?>">
		<p>Welcome to the OER plugin for BuddyPress. Developed by <a href='http://www.lncd.lincoln.ac.uk'>LNCD @ the University of Lincoln</a>.</p>
		<p>Bebop was designed for academic institutions who want to incorporate Open Educational Resources into BuddyPress Profiles. This plugin aids the discovery of OERs  in the BuddyPress environment.</p>
		<div class="clear"></div>
	</div>
	
	<div class='postbox-container'>
		<div class='postbox'>
			<h3>Latest News</h3>
			<div class='inside'>
				Version 1.0 of Bebop has now been released. This BuddyPress plugin allows users to import Open Educational Resources from around the web, into their BuddyPress activity stream.
			</div>
		</div>

		<div class="postbox">
			<h3>Support</h3>
			<div class="inside">
				While we cannot guarantee official support, we will always do what we can to help people sing this plugin. For support, please see our <a target="_blank" href="https://github.com/lncd/bebop/wiki">Github Wiki</a>.
			</div>
		</div>
	<!-- End postbox-container -->
	</div>
	
	<div class="postbox-container">
		<div class='postbox'>
			<h3><a href="?page=bebop_oers&type=verified">Recent OERs</a></h3>
			<div class='inside'>
				<?php
				$oers = bebop_tables::admin_fetch_oer_data( 'verified', 20 );
				
				if ( count( $oers ) > 0 ) {
					echo '<table class="postbox_table">
						<tr class="nodata">
							<th>Username</th>
							<th>OER Type</th>
							<th>Imported</th>
							<th>Published</th>
							<th>Content</th>
						</tr>';
					
					foreach ( $oers as $oer ) {
						echo '<tr>
							<td>' . bp_core_get_username( $oer->user_id ) . '</td>' .
							'<td>' . bebop_tables::sanitise_element( ucfirst( $oer->type ) ) . '</td>' .
							'<td>' . bp_core_time_since( $oer->date_imported ) . '</td>' .
							'<td>' . bp_core_time_since( $oer->date_recorded ) . '</td>' .
							'<td class="content">' . bebop_tables::sanitise_element( $oer->content, $allow_tags = true ) . '</td>' .
						'</tr>';
					}
					echo '</table>';
				}
				else {
					echo '<p>No verified oers exist in the oer manager.</p>';
				}
				?>
				
			</div>
		</div>
	</div>
	
	<div class="clear"></div>
</div>
<!-- end bebop_admin_container -->