<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">

<?php include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
<div id='bebop_admin_container'>
	<div class='bebop_admin_box'>
		<img class='bebop_logo' src="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_logo.png';?>">
		<p><?php _e( 'Welcome to the Bebop plugin for BuddyPress. Developed by <a target="_blank" href="http://www.lncd.lincoln.ac.uk">LNCD @ the University of Lincoln</a>.', 'bebop' ); ?></p>
		<p><?php _e( 'Bebop was designed for academic institutions who want to incorporate Open Educational Resources into BuddyPress Profiles. This plugin aids the discovery of OERs in the BuddyPress environment.', 'bebop' ); ?></p>
		<p><?php _e( 'As requested by several users, we have also made the terminology more suitable to those who are using Bebop as a social media aggregator.', 'bebop' ); ?></p>
		<div class="clear"></div>
	</div>
	
	<div class='postbox-container'>
		<div id="normal-sortables" class="meta-box-sortables ui-sortable"><div class='postbox'>
			<h3><?php _e( 'Latest News' ); ?></h3>
			<div class='inside'>
				<p><?php _e( 'Version 1.3.1 is a minor release which fixes the update error many users were encountering with 1.3.0', 'bebop' ); ?></p>
				<p><?php _e( 'Version 1.3 is a major release which fixes a few compatability issues with WP 3.5.1 and BP 1.6.4. It also adds more information on creating any required apps, API keys, and API secrets.', 'bebop' ); ?></p>
				<p><?php _e( 'Version 1.2.2 is a minor release which fixes a few compatability issues with WP 3.5 and BP 1.6.2. It also makes some URL\'s cleaner.', 'bebop' ); ?></p>
				<p><?php _e( 'Version 1.2.1 is a minor release which fixes a bug which inadvertently made all content visible in the sitewide activity steam.', 'bebop' ); ?></p>
			</div>
		</div></div>

		<div class="postbox">
			<h3><?php _e( 'Support', 'bebop' ); ?></h3>
			<div class="inside">
				<?php _e( 'While we cannot guarantee official support, we will always do what we can to help people sing this plugin. For support, please see our <a target="_blank" href="https://github.com/lncd/bebop/wiki">Github Wiki</a>.', 'bebop' ); ?>
			</div>
		</div>
	<!-- End postbox-container -->
	</div>
	
	<div class="postbox-container">
		<div id="normal-sortables" class="meta-box-sortables ui-sortable"><div class='postbox'>
			<h3><a href="?page=bebop_content&type=verified"><?php _e( 'Recently Verified Content', 'bebop' ); ?></a></h3>
			<div class='inside'>
				<?php
				$contents = bebop_tables::admin_fetch_content_data( 'verified', 0, 20 );
				
				if ( count( $contents ) > 0 ) {
					echo '<table class="postbox_table">
						<tr class="nodata">
							<th>' . __( 'Username', 'bebop' ) . '</th>
							<th>' . __( 'Type', 'bebop' ) . '</th>
							<th>' . __( 'Imported', 'bebop' ) . '</th>
							<th>' . __( 'Published', 'bebop' ) . '</th>
							<th>' . __( 'Content', 'bebop' ) . '</th>
						</tr>';
					
					foreach ( $contents as $content ) {
						echo '<tr>
							<td>' . bp_core_get_username( $content->user_id ) . '</td>' .
							'<td>' . bebop_tables::sanitise_element( ucfirst( $content->type ) ) . '</td>' .
							'<td>' . bp_core_time_since( $content->date_imported ) . '</td>' .
							'<td>' . bp_core_time_since( $content->date_recorded ) . '</td>' .
							'<td class="content">' . bebop_tables::sanitise_element( $content->content, $allow_tags = true ) . '</td>' .
						'</tr>';
					}
					echo '</table>';
				}
				else {
					echo '<p>' . __( 'No verified content exists in the content manager.', 'bebop' ) . '</p>';
				}
				?>
				
			</div>
		</div></div>
	</div>
	
	<div class="clear"></div>
</div>
<!-- end bebop_admin_container -->