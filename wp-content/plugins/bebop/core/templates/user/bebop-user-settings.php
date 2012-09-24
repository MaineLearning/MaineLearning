<div id='bebop_user_container'>
	
<?php 
$page = page_url( 2 );
if ( bp_is_my_profile() ) {
	if ( $page == '/bebop-oers/manager/' ) {
		include(WP_PLUGIN_DIR . '/bebop/core/templates/user/oer-manager.php');
	}
	else if ( $page == '/bebop-oers/accounts/' ) {
		if ( isset( $_GET['provider'] ) ) {
			if ( bebop_extensions::extension_exist( $_GET['provider'] ) ) {
				include( WP_PLUGIN_DIR . '/bebop/extensions/' . $_GET['provider'] . '/templates/user-settings.php' );
			}
			else {
				echo 'The extension \'' .  $_GET['provider'] . '\' doesn\'t exist.';
			}
			
		}
		else {
			$active_extensions = bebop_extensions::get_active_extension_names();
			if ( count( $active_extensions ) == 0 ) {
				echo '<p>No extensions are currently active. Please activate them in the bebop OER providers admin panel.</p>';
			}
			else {
				echo '<div class=\'help\'><p>Bebop allows you to add content from external accounts to your profile. This means you can pull in content from sites such as YouTube, Vimeo, Flickr and others. This content is then added you your activity stream, once you have verified it in the OER manager.</p>
				<p>To add an account, click on the relevant button below. You will then be guided through the setup process.</p><br></div>';
				
				foreach ( $active_extensions as $extension ) {
					$extension = bebop_extensions::get_extension_config_by_name( strtolower( $extension ) );
					echo '<div class="button_container"><a class="auto button min_width_100" href="?provider=' . $extension['name'] .'">' . $extension['display_name'] . '</a></div>';
				}
			}
		}
	}
} 

if ( $page == '/bebop-oers/home/' ) {
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
?>
</div> <!-- End bebop_user_container -->