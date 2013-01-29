<div id='bebop_user_container'>
	
<?php
if ( bp_is_my_profile() ) {
	if ( bp_is_current_component( 'bebop' ) )
	{
		if ( bp_is_current_action('bebop-manager' ) ) {
			$should_users_verify_content = bebop_tables::get_option_value( 'bebop_content_user_verification' );
			if ( $should_users_verify_content != 'no' ) {
				include( WP_PLUGIN_DIR . '/bebop/core/templates/user/oer-manager.php' );
			}
		}
		else if ( bp_is_current_action('bebop-accounts' ) ) {
			$query_string = bp_action_variables();
			if ( ! empty( $query_string ) ) {
				$provider = $query_string[0];
				if ( bebop_extensions::bebop_extension_exists( $provider ) ) {
					include( WP_PLUGIN_DIR . '/bebop/extensions/' . $provider . '/templates/user-settings.php' );
				}
				else {
					 _e( 'Could not locate this extensions user settings template.', 'bebop' );
				}
			}
			else {
				$active_extensions = bebop_extensions::bebop_get_active_extension_names();
				if ( count( $active_extensions ) == 0 ) {
					echo '<p>';
					_e( 'No extensions are currently active. Please activate them in the bebop content providers admin panel.', 'bebop' );
					echo '</p>';
				}
				else {
					echo '<div class="help"><p>';
					_e( 'Bebop allows you to add content from external accounts to your profile. This means you can pull in content from sites such as YouTube, Vimeo, Flickr and others. This content is then added to your activity stream, once you have verified it in the content manager.', 'bebop' );
					echo '</p>
					<p>';
					_e( 'To add an account, click on the relevant button below. You will then be guided through the setup process.', 'bebop' );
					echo '</p><br></div>';
					
					foreach ( $active_extensions as $extension ) {
						$extension = bebop_extensions::bebop_get_extension_config_by_name( strtolower( $extension ) );
						echo '<div class="button_container"><a class="auto button min_width_100" href="' . $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() . '/' . $extension['name'] .'">' . $extension['display_name'] . '</a></div>';
					}
				}
			}
		}
		else {
			add_action( 'wp_enqueue_scripts', 'bebop_loop_js' );
			?>
			<!-- This overrides the current filter in the cookie to nothing "i.e.
				on page refresh it will reset back to default" -->
			<script type='text/javascript'>
				var scope = '';
				var filter = 'all_oer';
				
				<?php /*This function below deals with the first load of the activity stream in the OER page,
				it has been directly taken from the global.js buddypress file in the activity section
				and modified due to lack of pratical hooks. Taken from bp_activity_request(scope, filter).*/ ?>
				bebop_activity_cookie_modify( scope,filter );
			</script>
			
			<!-- This section creates the drop-down menu with its classes hooked into buddypress -->
			<div class='item-list-tabs no-ajax' id='subnav' role='navigation'>
				<?php echo bebop_rss_buttons(); ?>
				<ul class='clearfix'>
					<li id='activity-filter-select' class='last'>
						<label for='activity-filter-by'>Show:</label> 
						<select id='activity-filter-by'>
							<!-- This adds the hook from the main bebop file to add the extension filter -->
							<?php
							bebop_load_filter_options(); //load the options.
							?>
						</select>
					</li>
				</ul>	
			</div>
			<!--This deals with pulling the activity stream -->
			<div class='activity' role='main'>
				<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>
			</div><!-- .activity -->
			<?php
		}
	}
}

?>
</div> <!-- End bebop_user_container -->