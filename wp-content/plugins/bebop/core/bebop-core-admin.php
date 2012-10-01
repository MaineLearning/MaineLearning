<?php

global $pagenow;
if ( ($pagenow == 'admin.php') && ( is_admin() ) ) {
	add_action( 'admin_init', 'bebop_general_admin_update_settings' );		//general settings.
	add_action( 'admin_init', 'bebop_oer_providers_update_active' );		//active providers.
	add_action( 'admin_init', 'bebop_extension_admin_update_settings' );	//extension settings.
	add_action( 'admin_init', 'bebop_admin_flush_table' );					//tables.
	add_action( 'all_admin_notices', 'bebop_admin_notice' );				//Notices
}
/*
 * Function to update the general admin settings.
 */
function bebop_general_admin_update_settings() {
	if ( ! empty( $_GET['page']) ) {
		
		$current_page = $_GET['page'];
		if ( $current_page == 'bebop_admin_settings' ) {
			$edited = false;
			if ( isset( $_POST['bebop_content_user_verification'] ) ) {
				bebop_tables::update_option( 'bebop_content_user_verification', trim( strip_tags( strtolower( $_POST['bebop_content_user_verification'] ) ) ) );
				$_SESSION['bebop_admin_notice'] = true;
				
				$edited = true;
			}
			
			if ( isset( $_POST['bebop_general_crontime'] ) ) {
				$crontime = trim( strip_tags( strtolower( $_POST['bebop_general_crontime'] ) ) );
				$result = bebop_tables::update_option( 'bebop_general_crontime', $crontime );
				
				wp_clear_scheduled_hook( 'bebop_main_import_cron' ); //Stops the cron
				if ( $crontime > 0 ) {	//if cron time is > 0, reschedule the cron. If zero, do not reschedule
					wp_schedule_event( time(), 'bebop_main_cron_time', 'bebop_main_import_cron' );//Re-activate with new time.
				}
				$_SESSION['bebop_admin_notice'] = true;
				
				$edited = true;
			}
			//var_dump($edited);
			if ( $edited == true ) {
				wp_safe_redirect( wp_get_referer() );
				exit();
			}
		}
	}
}
/*
 * Function to update extension activation
 */
function bebop_oer_providers_update_active() {
	if ( ! empty( $_GET['page']) ) {
		$current_page = $_GET['page'];
		if ( $current_page == 'bebop_providers' ) {
			if ( empty( $_GET['provider'] ) ) {
				if ( isset( $_POST['submit'] ) ){
					//reset the importer queue
					bebop_tables::update_option( 'bebop_importers_queue', '' );
					
					//set the new importer queue
					$importerQueue = array();
					foreach ( bebop_extensions::bebop_get_extension_configs() as $extension ) {
						if ( isset( $_POST['bebop_' . $extension['name'] . '_provider'] ) ) {
							bebop_tables::update_option( 'bebop_' . $extension['name'] . '_provider', trim( $_POST['bebop_' . $extension['name'] . '_provider'] ) );
							if ( ! bebop_tables::check_option_exists( 'bebop_' . $extension['name'] . '_rss_feed' ) ) {
								bebop_tables::update_option( 'bebop_' . $extension['name'] . '_rss_feed', 'on' );
							}
						}
						else {
							bebop_tables::update_option( 'bebop_' . $extension['name'] . '_provider', '' );
						}
						
						if ( is_array( $extension ) && isset( $_POST['bebop_' . $extension['name'] . '_provider'] ) && $_POST['bebop_' . $extension['name'] . '_provider'] == 'on' ) {
							$importerQueue[] = $extension['name'];
						}
					}
					bebop_tables::update_option( 'bebop_importers_queue', implode( ',', $importerQueue ) );
					$_SESSION['bebop_admin_notice'] = true;
					wp_safe_redirect( wp_get_referer() );
					exit();
				}
			}
		}
	}
}
/*
 * Function to sort out admin oer provider settings
 */
function bebop_extension_admin_update_settings() {
	if ( ! empty( $_GET['page']) ) {
		$current_page = $_GET['page'];
		if ( ! empty( $_GET['provider'] ) ) {
			if ( $current_page == 'bebop_providers' ) {
				$extension = bebop_extensions::bebop_get_extension_config_by_name( strtolower( $_GET['provider'] ) );
				
				if ( isset( $_POST['submit'] ) ) {
					$success = true;
					if ( isset( $_POST['bebop_' . $extension['name'] . '_consumer_key'] ) ) {
						bebop_tables::update_option( 'bebop_' . $extension['name'] . '_consumer_key', trim( $_POST['bebop_' . $extension['name'] . '_consumer_key'] ) );
					}
					if ( isset( $_POST['bebop_' . $extension['name'] . '_consumer_secret'] ) ) {
						bebop_tables::update_option( 'bebop_' . $extension['name'] . '_consumer_secret', trim( $_POST['bebop_' . $extension['name'] . '_consumer_secret'] ) );
					}
					if ( isset( $_POST['bebop_' . $extension['name'] . '_maximport'] ) ) {
						if ( empty( $_POST['bebop_' . $extension['name'] . '_maximport'] ) || is_numeric( $_POST['bebop_' . $extension['name'] . '_maximport'] ) ) {
							bebop_tables::update_option( 'bebop_' . $extension['name'] . '_maximport', trim( $_POST['bebop_' . $extension['name'] . '_maximport'] ) );
						}
						else {
							$success = '"Imports per day" must be a number (or blank).';
						}
					}
					/*rss stuff, dont touch */
					if ( isset( $_POST['bebop_' . $extension['name'] . '_rss_feed'] ) ) {
						if ( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_provider' ) == 'on' ) {
							bebop_tables::update_option( 'bebop_' . $extension['name'] . '_rss_feed', trim( $_POST['bebop_' . $extension['name'] . '_rss_feed'] ) );
						}
						else {
							$success = __( 'RSS feeds cannot be modified while the extension is not enabled.', 'bebop');
						}
					}
					else {
						bebop_tables::update_option( 'bebop_' . $extension['name'] . '_rss_feed', '' );
					}
					
					//Extension authors: use this hook to add your own admin data saves.
					do_action( 'bebop_admin_settings_pre_save', $extension, $success );
					
					$_SESSION['bebop_admin_notice'] = $success;
					wp_safe_redirect( wp_get_referer() );
					exit();
				}
				/*
				 * Mechanics to remove a user from your extension is already provided - you do not need to modify this.
				 */
				if ( isset( $_GET['reset_user_id'] ) ) {
					$user_id = trim( $_GET['reset_user_id'] );
					bebop_tables::remove_user_from_provider( $user_id, $extension['name'] );
					$success = 'User has been removed';
					$_SESSION['bebop_admin_notice'] = $success;
					wp_safe_redirect( wp_get_referer() );
					exit();
				}
			}// End if ( $current_page == 'bebop_providers' ) {
		}//End if ( ! empty( $_GET['provider'] ) ) {
	}//End if ( ! empty( $_GET['page']) ) {
}

/*
 * function to show bebop admin notices
 */
function bebop_admin_notice() {
	if ( isset( $_SESSION['bebop_admin_notice'] ) ) {
		$success = $_SESSION['bebop_admin_notice'];
		if ( $success === true ) {
			echo '<div class="bebop_success_box">';
			_e( 'Settings Saved', 'bebop');
			echo '</div>';
		}
		else {
			echo '<div class="bebop_error_box">' . ucfirst( $success ) . '</div>';
		}
		$_SESSION['bebop_admin_notice'] = null;
		unset( $_SESSION['bebop_admin_notice'] );
	}
}
/*
 * function to remove data from a database
 */
function bebop_admin_flush_table() {
	if ( ! empty( $_GET['page']) ) {
		$current_page = $_GET['page'];
		if ( ( $current_page == 'bebop_error_log' ) || ( $current_page == 'bebop_general_log' ) ) {
			if ( isset( $_GET ) ) {
				if ( isset( $_GET['clear_table'] ) ) {
					if ( $table_row_data = bebop_tables::flush_table_data( 'bp_' . $current_page ) ) {
						$_SESSION['bebop_admin_notice'] = true;
						
					}
					else {
						$_SESSION['bebop_admin_notice'] = __( 'Error clearing table data.', 'bebop');
					}
					wp_safe_redirect( $_SERVER['PHP_SELF'] . '?page=' . $current_page );
					exit();
				}
			}
		}
	}
}
?>