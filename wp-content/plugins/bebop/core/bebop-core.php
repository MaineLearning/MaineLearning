<?php
bebop_extensions::load_extensions();



/*
 * Hook into admin functions to provide functionality to update, delete, etc.
 */
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
			if ( ! empty( $_POST['bebop_general_crontime'] ) ) {
				$crontime = bebop_tables::update_option( 'bebop_general_crontime', trim( strip_tags( strtolower( $_POST['bebop_general_crontime'] ) ) ) );
				wp_clear_scheduled_hook( 'bebop_cron' ); //Stops the cron
				if( $crontime > 0 ) {	//if cron time is > 0, reschedule the cron. If zero, do nto reschedule
					wp_schedule_event( time(), 'secs', 'bebop_cron' );//Re-activate with new time.
				}
				$_SESSION['bebop_admin_notice'] = true;
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
		if ( $current_page == 'bebop_oer_providers' ) {
			if ( empty( $_GET['provider'] ) ) {
				if ( isset( $_POST['submit'] ) ){
					//reset the importer queue
					bebop_tables::update_option( 'bebop_importers_queue', '' );
					
					//set the new importer queue
					$importerQueue = array();
					foreach ( bebop_extensions::get_extension_configs() as $extension ) {
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
			if ( $current_page == 'bebop_oer_providers' ) {
				$extension = bebop_extensions::get_extension_config_by_name( strtolower( $_GET['provider'] ) );
				/*
				 * update section - if you add more parameters, don't forget to update them here too.
				 */
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
							$success = 'RSS feeds cannot be modified while the extension is not enabled.';
						}
					}
					else {
						bebop_tables::update_option( 'bebop_' . $extension['name'] . '_rss_feed', '' );
					}
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
			}// End if ( $current_page == 'bebop_oer_providers' ) {
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
			echo '<div class="bebop_success_box">Settings Saved.</div>';
		}
		else {
			echo '<div class="bebop_error_box">' . ucfirst($success) . '</div>';
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
						$_SESSION['bebop_admin_notice'] ='Error clearing table data.';
					}
					wp_safe_redirect( $_SERVER['PHP_SELF'] . '?page=' . $current_page );
					exit();
				}
			}
		}
	}
}

//Adds a hook which detects and updates the oer status.
add_action( 'bp_actions', 'bebop_manage_oers' );
function bebop_manage_oers() {
	if ( bp_is_current_component( 'bebop-oers' ) && bp_is_current_action('manager' ) ) {
		if ( isset( $_POST['action'] ) ) {
			global $bp;
			$oer_count  = 0;
			$success = false;
			//Add OER's to the activity stream.
			if ( $_POST['action'] == 'verify' ) {
				foreach ( array_keys( $_POST ) as $oer ) {
					if ( $oer != 'action' ) {
						$data = bebop_tables::fetch_individual_oer_data( $oer ); //go and fetch data from the activity buffer table.
						if ( ! empty( $data->secondary_item_id ) ) {
							global $wpdb;
							if ( ! bp_has_activities( 'secondary_id=' . $data->secondary_item_id ) ) {
								$new_activity_item = array (
									'user_id'			=> $data->user_id,
									'component'			=> 'bebop_oer_plugin',
									'type'				=> $data->type,
									'action'			=> $data->action,
									'content'			=> $data->content,
									'secondary_item_id'	=> $data->secondary_item_id,
									'date_recorded'		=> $data->date_recorded,
									'hide_sitewide'		=> $data->hide_sitewide,
								);
								if ( bp_activity_add( $new_activity_item ) ) {
									bebop_tables::update_oer_data( $data->secondary_item_id, 'status', 'verified' );
									bebop_tables::update_oer_data( $data->secondary_item_id, 'activity_stream_id', $activity_stream_id = $wpdb->insert_id );
									$oer_count++;
								}
								else {
									bebop_tables::log_error( 'Activity Stream', 'Could not update the oer buffer status.' );
								}
							}
							else {
								bebop_tables::log_error( 'Activity Stream', 'This content already exists in the activity stream.' );
							}
						}
					}
				}//End foreach ( array_keys($_POST) as $oer ) {
				if ( $oer_count > 1 ) {
					$success = true;
					$message = 'Resources verified.';
				}
				else {
					$success = true;
					$message = 'Resource verified.';
				}
			}//End if ( $_POST['action'] == 'verify' ) {
			else if ( $_POST['action'] == 'delete' ) {
				foreach ( array_keys( $_POST ) as $oer ) {
					if ( $oer != 'action' ) {
						$data = bebop_tables::fetch_individual_oer_data( $oer );//go and fetch data from the activity buffer table.
						if ( ! empty( $data->id ) ) {
							//delete the activity, let the filter update the tables.
							if ( ! empty( $data->activity_stream_id ) ) {
								bp_activity_delete(
												array(
													'id' => $data->activity_stream_id,
												)
								);
								$oer_count++;
							}
							else {
								//else just update the status
								bebop_tables::update_oer_data( $data->secondary_item_id, 'status', 'deleted' );
								$oer_count++;
							}
						}
					}
				} //End foreach ( array_keys( $_POST ) as $oer ) {
				if ( $oer_count > 1 ) {
					$success = true;
					$message = 'Resources deleted.';
				}
				else {
					$success = true;
					$message = 'Resource deleted.';
				}
			}
			else if ( $_POST['action'] == 'undelete' ) {
				foreach ( array_keys( $_POST ) as $oer ) {
					$exclude_array = array( 'action', 'submit' );
					if ( ! in_array( $oer, $exclude_array ) ) {
						$data = bebop_tables::fetch_individual_oer_data( $oer );//go and fetch data from the activity buffer table.
						bebop_tables::update_oer_data( $data->secondary_item_id, 'status', 'unverified' );
						$oer_count++;
					}
				}
				if ( $oer_count > 1 ) {
					$success = true;
					$message = 'Resources undeleted.';
				}
				else {
					$success = true;
					$message = 'Resource undeleted.';
				}
			}
			if ( $success ) {
				bp_core_add_message( $message );
			}
			else {
				bp_core_add_message( "We couldnt do that for you. Please try again.", 'error' );
			}
			bp_core_redirect( $bp->loggedin_user->domain  .'/' . bp_current_component() . '/' . bp_current_action() . '/' );
		}
	}
	add_action( 'wp_enqueue_scripts', 'bebop_oer_js' ); //enqueue  selectall/none script
}
/*
 * function to update user settings pages.
 */
add_action( 'bp_actions', 'bebop_manage_provider' );
function bebop_manage_provider() {
	if ( bp_is_current_component( 'bebop-oers' ) && bp_is_current_action('accounts' ) ) {
		if ( isset( $_GET['provider'] ) ) {
		global $bp;
		$extension = bebop_extensions::get_extension_config_by_name( strtolower( $_GET['provider'] ) );
			if ( isset( $_POST['submit'] ) ) {
				if ( isset( $_POST['bebop_' . $extension['name'] . '_active_for_user'] ) ) {
					bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_active_for_user', $_POST['bebop_' . $extension['name'] . '_active_for_user'] );
					bp_core_add_message( 'Settings for ' . $extension['display_name'] . ' have been saved.' );
				}
				
				if ( ! empty( $_POST['bebop_' . $extension['name'] . '_username'] ) ) {
					$new_name = strip_tags( $_POST['bebop_' . $extension['name'] . '_username'] );
					if ( bebop_tables::add_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_username', $new_name, $check_meta_value = true ) ) {
						bp_core_add_message( $new_name . ' has been added to the ' . $extension['display_name'] . ' feed.' );
					}
					else {
						bp_core_add_message( $new_name . ' already exists in the ' . $extension['display_name'] . ' feed; you cannot add it again.', 'error' );
					}
				}
				
				//RSS stuff
				if ( ( ! empty( $_POST['bebop_' . $extension['name'] . '_newfeedname'] ) ) && 
					( ! empty( $_POST['bebop_' . $extension['name'] . '_newfeedurl'] ) ) ) {
					//Updates the channel name.
					
					$found_http = strpos( $_POST['bebop_' . $extension['name'] . '_newfeedurl'], '://' );
					if ( ! $found_http ) {
						$insert_url = 'http://' . $_POST['bebop_' . $extension['name'] . '_newfeedurl'];
					}
					else {
						$insert_url = $_POST['bebop_' . $extension['name'] . '_newfeedurl'];
					}
					$new_name = strip_tags( $_POST['bebop_' . $extension['name'] . '_newfeedname'] );
					if( bebop_tables::add_user_meta( $bp->loggedin_user->id, $extension['name']. '_' . $_POST['bebop_' . $extension['name'] . '_newfeedname'], $new_name, strip_tags( $insert_url ) ) ) {
						bp_core_add_message( $new_name . ' has been added to the ' . $extension['display_name'] . ' feed.' );
					}
					else {
						bp_core_add_message( $new_name . ' already exists in the ' . $extension['display_name'] . ' feed; you cannot add it again.', 'error' );
					}
				}
				bp_core_redirect( $bp->loggedin_user->domain  .'/' . bp_current_component() . '/' . bp_current_action() . '/' );
			}//End if ( isset( $_POST['submit'] ) ) {
			
			//Oauth stuff
			if ( isset( $_GET['oauth_token'] ) ) {
				//Handle the oAuth requests
				$OAuth = new bebop_oauth();
				$OAuth->set_request_token_url( $extension['request_token_url'] );
				$OAuth->set_access_token_url( $extension['access_token_url'] );
				$OAuth->set_authorize_url( $extension['authorize_url'] );
				
				$OAuth->set_parameters( array( 'oauth_verifier' => $_GET['oauth_verifier'] ) );
				$OAuth->set_callback_url( $bp->loggedin_user->domain . 'bebop-oers/accounts/?provider=' . $extension['name'] );
				$OAuth->set_consumer_key( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_key' ) );
				$OAuth->set_consumer_secret( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_secret' ) );
				$OAuth->set_request_token( bebop_tables::get_user_meta_value( $bp->loggedin_user->id,'bebop_' . $extension['name'] . '_oauth_token_temp' ) );
				$OAuth->set_request_token_secret( bebop_tables::get_user_meta_value( $bp->loggedin_user->id,'bebop_' . $extension['name'] . '_oauth_token_secret_temp' ) );
				
				$accessToken = $OAuth->access_token();
				
				bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_oauth_token', $accessToken['oauth_token'] );
				bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_oauth_token_secret', $accessToken['oauth_token_secret'] );
				bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_active_for_user', 1 );
				
				bp_core_add_message( 'You have successfully authenticated your ' . $extension['display_name'] . ' account.' );
				bp_core_redirect( $bp->loggedin_user->domain  .'/' . bp_current_component() . '/' . bp_current_action() . '/' );
			}
			
			//delete a user's feed
			if ( isset( $_GET['delete_feed'] ) ) {
				$check_feed = bebop_tables::get_user_meta_value( $bp->loggedin_user->id, $_GET['delete_feed'] );
				if ( ! empty( $check_feed ) ) {
					$check_http = strpos( $check_feed, '://' );
					if ( $check_http ) {
						bebop_tables::remove_user_meta( $bp->loggedin_user->id, $_GET['delete_feed'] );
						bebop_tables::remove_username_from_provider( $bp->loggedin_user->id, $extension['name'], $_GET['delete_feed'] );
						bp_core_add_message( $extension['display_name'] . ' feed deleted.' );
						bp_core_redirect( $bp->loggedin_user->domain  .'/' . bp_current_component() . '/' . bp_current_action() . '/' );
					}
				}
			}
			//resets the user's data
				if ( isset( $_GET['reset'] ) ) {
					bebop_tables::remove_user_meta( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_username' );
				}
			
			//resets the user's data
			if ( isset( $_GET['remove_username'] ) ) {
				$username = $_GET['remove_username'];
				bebop_tables::remove_username_from_provider( $bp->loggedin_user->id, $extension['name'], $username );
				bp_core_add_message( $username . ' has been removed from your ' . $extension['display_name'] . ' feed.' );
				bp_core_redirect( $bp->loggedin_user->domain  .'/' . bp_current_component() . '/' . bp_current_action() . '/' );
			}
		}//End if ( isset( $_GET['provider'] ) ) {
	}//End if ( bp_is_current_component( 'bebop-oers' ) && bp_is_current_action('accounts' ) ) {
}

/*
 * Returns status from get array
 */
function bebop_get_oer_type() {
	global $bp, $wpdb;
	if ( bp_is_current_component( 'bebop-oers' ) && bp_is_current_action('manager' ) ) {
		if ( isset( $_GET['type'] ) ) {
			if ( strtolower( strip_tags( $_GET['type'] == 'unverified' ) ) ) {
				return 'unverified';
			}
			else if ( strtolower( strip_tags( $_GET['type'] == 'verified' ) ) ) {
				return 'verified';
			}
			else if ( strtolower( strip_tags( $_GET['type'] == 'deleted' ) ) ) {
				return 'deleted';
			}
		}
	}
}
function bebop_get_oers( $type ) {
	global $bp, $wpdb;
	$active_extensions = bebop_extensions::get_active_extension_names( $addslashes = true );
	$extension_names   = join( ',' ,$wpdb->escape( $active_extensions ) );
	return bebop_tables::fetch_oer_data( $bp->loggedin_user->id, $extension_names, $type );
}


/*
 * Generic function to generate secondary_item_id's
 */
function bebop_generate_secondary_id( $user_id, $id, $timestamp = null ) {
	if ( is_numeric( $id ) ) {
		$item_id = $user_id . $id . strtotime( $timestamp );
	}
	else {
		$item_id = $user_id . strtotime( $timestamp );
	}
	return $item_id;
}
//User styles.
function bebop_user_stylesheets() {
	wp_register_style( 'bebop-user-styles', plugins_url() . '/bebop/core/resources/css/user.css' );
	wp_enqueue_style( 'bebop-user-styles' );
}
//Javascript
function bebop_oer_js() {
	wp_register_script( 'bebop-oer-js', plugins_url() . '/bebop/core/resources/js/bebop-oers.js' );
	wp_enqueue_script( 'bebop-oer-js' );
}
/*function bebop_loop_js() {
	wp_register_script( 'bebop-loop-js', plugins_url() . '/bebop/core/resources/js/bebop-loop.js' );
	wp_enqueue_script( 'bebop-loop-js' );
}*/

/*
 * Gets the url of a page
 */
function page_url( $last_folders = null ) {
	if ( isset( $_SERVER['HTTPS'] ) ) {
		if (  $_SERVER['HTTPS'] == 'on' ) {
			$page_url = 'https://';
		}
	}
	else {
		$page_url = 'http://';
	}
	if ( $_SERVER['SERVER_PORT'] != '80' ) {
		$page_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
	}
	else {
		$page_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	}
	if ( $last_folders != null ) {
		$exp = array_reverse( explode( '/', $page_url ) );
		$arr = array();
		while ( $last_folders > 0 ) {
			$arr[] = $exp[$last_folders];
			$last_folders--;
		}
		$page_url = '/' . implode( '/', $arr ) . '/';
	}
	return $page_url;
}
/*
 * Adds an imported item to the buffer table
 */
function bebop_create_buffer_item( $params ) {
	global $bp, $wpdb;
	if ( is_array( $params ) ) {
		if ( ! bp_has_activities( 'secondary_id=' . $params['item_id'] ) ) {
			$original_text = $params['content'];
			if ( ! bebop_tables::bebop_check_existing_content_buffer( $params['user_id'], $params['extension'], $original_text ) ) {
				$content = '';
				if ( $params['content_oembed'] == true ) {
					$content = $original_ext;
				}
				else {
					$content = '<div class="bebop_activity_container ' . $params['extension'] . '">' . $original_text . '</div>';
				}
				$action  = '<a href="' . bp_core_get_user_domain( $params['user_id'] ) .'" title="' . bp_core_get_username( $params['user_id'] ).'">'.bp_core_get_user_displayname( $params['user_id'] ) . '</a>';
				$action .= ' ' . __( 'posted&nbsp;a', 'bebop' . $extension['name'] ) . ' ';
				$action .= '<a href="' . $params['actionlink'] . '" target="_blank" rel="external"> '.__( $params['type'], 'bebop_'.$extension['name'] );
				$action .= '</a>: ';
				
				$oer_hide_sitewide = 0;
				$date_imported = gmdate( 'Y-m-d H:i:s', time() );
				
				//extra check to be sure we don't have an empty activity
				$clean_comment = '';
				$clean_comment = trim( strip_tags( $content ) );
				
				if ( ! empty( $clean_comment ) ) {
					if ( $wpdb->query(
									$wpdb->prepare(
													'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager ( user_id, status, type, action, content, secondary_item_id, date_imported, date_recorded, hide_sitewide ) VALUES ( %s, %s, %s, %s, %s, %s, %s, %s, %s )',
													$wpdb->escape( $params['user_id'] ), 'unverified', $wpdb->escape( $params['extension'] ), $wpdb->escape( $action ), $wpdb->escape( $content ),
													$wpdb->escape( $params['item_id'] ), $wpdb->escape( $date_imported ), $wpdb->escape( $params['raw_date'] ), $wpdb->escape( $oer_hide_sitewide )
									)
					) ) {
						bebop_filters::day_increase( $params['extension'], $params['user_id'], $params['username'] );
					}
					else {
						bebop_tables::log_error( 'Importer', 'Import query error' );
					}
				}
				else {
					bebop_tables::log_error( 'Importer', 'Could not import, content already exists.' );
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			bebop_tables::log_error( 'Import Error - ' . $params['extension'], $params['item_id'] . ' already exists', serialize( $secondary ) );
			return false;
		}
	}
	return true;
}


//hook and function to update status in the buffer table if the activity belongs to this plugin.
add_action( 'bp_activity_deleted_activities', 'update_bebop_status' );

function update_bebop_status( $deleted_ids ) {
	global $wpdb;
	foreach ( $deleted_ids as $id ) {
		$result = $wpdb->get_row( 'SELECT secondary_item_id FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager WHERE activity_stream_id = '" . $id . "'" );
		if ( ! empty( $result->secondary_item_id ) ) {
			bebop_tables::update_oer_data( $result->secondary_item_id, 'status', 'deleted' );
			bebop_tables::update_oer_data( $result->secondary_item_id, 'activity_stream_id', '' );
		}
	}
}

//This function loads additional filter options for the extensions.
function bebop_load_filter_options() {
	
	$store = array();
	//gets only the active extension list.
	$active_extensions = bebop_extensions::get_active_extension_names();
	foreach ( $active_extensions as $extension ) {
		if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_provider' ) == 'on' ) {
			$this_extension = bebop_extensions::get_extension_config_by_name( $extension );
			$store[] = '<option value="' . $this_extension['name'] .'">' . $this_extension['display_name'] . '</option>';
		}
	}
	
	//Ensures the All OER only shows if there are two or more OER's to choose from.
	if ( count( $store ) >= 2 ) {
		echo '<option value="all_oer">All OERs</option>';
	}
	
	//Outputs the options
	foreach ( $store as $option ) {
		echo $option;
	}
}

/*This function is added to the filter of the current query string and deals with the OER page
 * as well as the all_oer option. This is because the all_oer is not a type thus the query for what
 * to pull from the database needs to be created manually. */
function bebop_dropdown_query_checker( $query_string ) {
	global $bp;
	$new_query_string = '';
	//Checks if this is the oer page
	if ( $bp->current_component == 'bebop-oers' ) {
		
		//Passes the query string as an array as its easy to determine the page number then "if any".
		parse_str( $query_string, $str );
		$page_number = '';
		//This checks if there is a certain page and if so ensure it is saved to be put into the query string.
		if ( isset( $str['page'] ) ) {
			$page_number = '&page=' . $str['page'];
		}
		//if str isnt set or it equals 'all_oer'
		if ( ( ! isset( $str['type']) ) || ( $str['type'] == 'all_oer' ) ) {
			//Sets the string_build variable ready.
			$string_build = '';
			
			//Loops through all the different extensions and adds the active extensions to the temp variable.
			$active_extensions = bebop_extensions::get_active_extension_names();
			$extensions = array();
			foreach ( $active_extensions as $extension ) {
				if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_provider' ) == 'on' ) {
					$extensions[] = $extension;
				}
			}
			
			if ( ! empty( $extensions ) ) {
				$string_build = implode( ',', $extensions );
				
				//Recreates the query string with the new views.
				$new_query_string = 'type=' . $string_build . '&action=' . $string_build;
			}
			/*Puts the current page number onto the query for the all_oer.
			(others are dealt with by the activity stream processes)*/
			$new_query_string .= $page_number;
		}
		else {
			$new_query_string = 'type=' . $str['type'] . '&action=' . $str['type'] . $page_number;
			$_COOKIE['bp-activity-filter'] = $str['type'];
		}
		//Sets the page number for the bebop-oers page.
		$new_query_string .= '&per_page=10';
		
		//sets the reset session variable to allow for resetting activty stream if they have come from the oer page.
		if ( $_SESSION['bebop_area'] == 'not_bebop_oer_plugin' ) {
			
			$_SESSION['bebop_area'] = 'bebop_oer_plugin';
			
			var_dump($_SESSION['bebop_area']);
			/*
			 * This ensures that the default activity stream is reset if they have left the OER page.
			 * "This is done to stop the dropdown list and activity stream being the same as the oer 
			 * page was peviously on.
			 */
			echo  "<script type='text/javascript' src='" . WP_CONTENT_URL . '/plugins/bebop/core/resources/js/bebop-loop.js' . "'></script>";
			echo '<script type="text/javascript">';
			echo 'bebop_activity_cookie_modify("","");';
			echo '</script>';
			$_COOKIE['bp-activity-filter'] = '';
		}
	}
	else {
		
		//This checks if the oer page was visited so it can reset the filters for the activity stream.
		$new_query_string = $query_string;
			
		//Passes the query string as an array as its easy to determine the page number then "if any".
		parse_str( $query_string, $str );
		
		if ( isset( $str['type'] ) ) {
			if ( $str['type'] == 'all_oer' ) {
				//Sets the string_build variable ready.
				$string_build = '';
				
				//Loops through all the different extensions and adds the active extensions to the temp variable.
				$active_extensions = bebop_extensions::get_active_extension_names();
				$extensions = array();
				foreach ( $active_extensions as $extension ) {
					if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_provider' ) == 'on' ) {
						$extensions[] = $extension;
					}
				}
				
				if ( ! empty( $extensions ) ) {
					$string_build = implode( ',', $extensions );
					
					//Recreates the query string with the new views.
					$new_query_string = 'type=' . $string_build . '&action=' . $string_build;
				}
				//This checks if there is a certain page and if so ensure it is saved to be put into the query string.
				if ( isset( $str['page'] ) ) {
					$new_query_string .= '&page=' . $str['page'];
				}
			}
		}
			
		if ( $_SESSION['bebop_area'] == 'bebop_oer_plugin' ) {
			$_SESSION['bebop_area'] = 'not_bebop_oer_plugin';
			/*
			 * This ensures that the default activity stream is reset if they have left the OER page.
			 * "This is done to stop the dropdown list and activity stream being the same as the oer 
			 * page was peviously on.
			 */
			echo  "<script type='text/javascript' src='" . WP_CONTENT_URL . '/plugins/bebop/core/resources/js/bebop-loop.js' . "'></script>";
			echo '<script type="text/javascript">';
			echo 'bebop_activity_cookie_modify("","");';
			echo '</script>';
			$_COOKIE['bp-activity-filter'] = '';
		}
	}
	//Returns the query string.
	return $new_query_string;
}

//This is a hook into the member activity filter options.
add_action( 'bp_member_activity_filter_options', 'bebop_load_filter_options' );

//This is a hook into the activity filter options.
add_action( 'bp_activity_filter_options', 'bebop_load_filter_options' );

//This adds a hook before the loading of the activity data to adjust if all_oer is selected.
add_action( 'bp_before_activity_loop', 'bebop_access_ajax' );
function bebop_access_ajax() {
	//Adds the filter to the function to check for all_oer and rebuild the query if so.
	add_filter( 'bp_ajax_querystring', 'bebop_dropdown_query_checker' );
}


//add extra rss buttons to activity stream content.
function my_bp_activity_entry_meta() {
	global $bp;
	if ( bp_get_activity_object_name() == 'bebop_oer_plugin' ) {
		
		$rss_active_extensions = array();
		$extensions = bebop_extensions::get_active_extension_names();
		foreach ( $extensions as $extension ) {
			if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_rss_feed' ) == 'on' ) {
				$rss_active_extensions[] = $extension;
			}
		}
		
		foreach ( $rss_active_extensions as $feed )
		{
			if( bp_get_activity_type() == $feed ) {
				$user = get_user_by( 'id', bp_get_activity_user_id() );
				$extension = bebop_extensions::get_extension_config_by_name( strtolower( $feed ) );
				echo '<a class="button bp-secondary-action" href="' . get_bloginfo('url') . '/members/' . $user->user_nicename . '/' . bp_get_activity_slug() . '/' . $extension['name'] . '/feed"><img style="vertical-align: text-top;"' .
				'src="' . plugins_url() . '/bebop/core/resources/images/feed_14px.png"> ' .
				$extension['display_name'] . ' resources for ' . $user->user_nicename . '</a>';
			}
		}
		
		if ( count( $rss_active_extensions ) >= 2 ) {
			echo ' <a class="button bp-secondary-action" href="' . get_bloginfo('url') . '/members/' . $user->user_nicename . '/' . bp_get_activity_slug() . '/all_oers/feed"><img style="vertical-align: text-top;"' . 
			'src="' . plugins_url() . '/bebop/core/resources/images/feed_14px.png"> All resources for ' . $user->user_nicename . '</a>';
		}
	}
}
add_action('bp_activity_entry_meta', 'my_bp_activity_entry_meta');
?>
