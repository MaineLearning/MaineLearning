<?php
/*
 * Hook into admin functions to provide functionality to update, delete, etc.
 */


//Adds a hook which detects and updates the oer status.
add_action( 'bp_actions', 'bebop_manage_oers' );
function bebop_manage_oers() {
	if ( bp_is_current_component( 'bebop' ) && bp_is_current_action('bebop-manager' ) ) {
		if ( isset( $_POST['action'] ) ) {
			global $bp;
			$oer_count = 0;
			$success = false;
			//Add OER's to the activity stream.
			if ( $_POST['action'] == 'verify' ) {
				foreach ( array_keys( $_POST ) as $oer ) {
					if ( $oer != 'action' ) {
						$data = bebop_tables::fetch_individual_oer_data( $oer ); //go and fetch data from the activity buffer table.
						if ( ! empty( $data->secondary_item_id ) ) {
							$should_users_verify_content = bebop_tables::get_option_value( 'bebop_' . $data->type . '_content_user_verification' );
							if ( $should_users_verify_content != 'no' ) { 
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
										bebop_tables::log_error( __( 'Activity Stream', 'bebop' ),  __( 'Could not update the content status.', 'bebop' ) );
									}
								}
								else {
									bebop_tables::log_error(  __( 'Activity Stream', 'bebop'),  __( 'This content already exists in the activity stream.', 'bebop' ) );
								}
							}//End if ( $should_users_verify_content != 'no' ) { 
						}
					}
				}//End foreach ( array_keys($_POST) as $oer ) {
				if ( $oer_count > 1 ) {
					$success = true;
					$message = __( 'Resources verified.', 'bebop' );
				}
				else {
					$success = true;
					$message = __( 'Resource verified.', 'bebop' );
				}
			}//End if ( $_POST['action'] == 'verify' ) {
			else if ( $_POST['action'] == 'delete' ) {
				foreach ( array_keys( $_POST ) as $oer ) {
					if ( $oer != 'action' ) {
						$data = bebop_tables::fetch_individual_oer_data( $oer );//go and fetch data from the activity buffer table.
						if ( ! empty( $data->id ) ) {
							//delete the activity, let the filter update the tables.
							if ( ! empty( $data->activity_stream_id ) ) {
								$should_users_verify_content = bebop_tables::get_option_value( 'bebop_' . $data->type . '_content_user_verification' );
								if ( $should_users_verify_content != 'no' ) { 
									bp_activity_delete(
													array(
														'id' => $data->activity_stream_id,
													)
									);
									$oer_count++;
								}
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
					$message = __( 'Resources deleted.', 'bebop' );
				}
				else {
					$success = true;
					$message = __( 'Resource deleted.', 'bebop' );
				}
			}
			else if ( $_POST['action'] == 'undelete' ) {
				foreach ( array_keys( $_POST ) as $oer ) {
					$exclude_array = array( 'action', 'submit' );
					if ( ! in_array( $oer, $exclude_array ) ) {
						$data = bebop_tables::fetch_individual_oer_data( $oer );//go and fetch data from the activity buffer table.
						$should_users_verify_content = bebop_tables::get_option_value( 'bebop_' . $data->type . '_content_user_verification' );
						if ( $should_users_verify_content != 'no' ) { 
							bebop_tables::update_oer_data( $data->secondary_item_id, 'status', 'unverified' );
							$oer_count++;
						}
					}
				}
				if ( $oer_count > 1 ) {
					$success = true;
					$message =  __( 'Resources undeleted.', 'bebop' );
				}
				else {
					$success = true;
					$message = __( 'Resource undeleted.', 'bebop' );
				}
			}
			if ( $success ) {
				bp_core_add_message( $message );
			}
			else {
				bp_core_add_message( __( 'We couldnt do that for you. Please try again.', 'bebop' ), 'error' );
			}
			bp_core_redirect( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() );
		}//End if ( isset( $_POST['action'] ) ) {
	}//End if ( bp_is_current_component( 'bebop' ) && bp_is_current_action('bebop-manager' ) ) {
	add_action( 'wp_enqueue_scripts', 'bebop_oer_js' ); //enqueue  selectall/none script
}//End function bebop_manage_oers() {
/*
 * function to update user settings pages.
 */
add_action( 'bp_actions', 'bebop_manage_provider' );
function bebop_manage_provider() {
	global $bp;
	if ( bp_is_current_component( 'bebop' ) && bp_is_current_action('bebop-accounts' ) ) {
		$query_string = bp_action_variables();
		if ( ! empty( $query_string ) ) {
			$provider = $query_string[0];
		}
				
		if ( !empty( $provider ) ) {
			global $bp;
			$extension = bebop_extensions::bebop_get_extension_config_by_name( strtolower( $provider ) );
			if ( isset( $_POST['submit'] ) ) {
				
				check_admin_referer( 'bebop_' . $extension['name'] . '_user_settings' );
				
				if ( isset( $_POST['bebop_' . $extension['name'] . '_active_for_user'] ) ) {
					bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_active_for_user', $_POST['bebop_' . $extension['name'] . '_active_for_user'] );
					bp_core_add_message( 'Settings for ' . $extension['display_name'] . ' have been saved.' );
				}
				
				if ( ! empty( $_POST['bebop_' . $extension['name'] . '_username'] ) ) {
					$new_name = stripslashes( $_POST['bebop_' . $extension['name'] . '_username'] );
					if ( bebop_tables::add_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_username', $new_name, $check_meta_value = true ) ) {
						bebop_tables::add_to_first_importers_list( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_' . $new_name . '_do_initial_import', $new_name );
						bp_core_add_message( sprintf( __( '%1$s has been added to the %2$s feed.', 'bebop' ), $new_name, $extension['display_name'] ) );
					}
					else {
						bp_core_add_message( sprintf( __( '%1$s already exists in the %2$s feed; you cannot add it again.', 'bebop' ), $new_name, $extension['display_name'] ), 'error' );
					}
				}
				
				//Try and add a new RSS feed.
				if ( ( ! empty( $_POST['bebop_' . $extension['name'] . '_newfeedname'] ) ) && 
					( ! empty( $_POST['bebop_' . $extension['name'] . '_newfeedurl'] ) ) ) {
					if ( filter_var(  $_POST['bebop_' . $extension['name'] . '_newfeedurl'], FILTER_VALIDATE_URL ) ) {
						$insert_url = $_POST['bebop_' . $extension['name'] . '_newfeedurl'];
						$new_name = str_replace(' ', '_', stripslashes( strip_tags( $_POST['bebop_' . $extension['name'] . '_newfeedname'] ) ) );
						if( bebop_tables::add_user_meta( $bp->loggedin_user->id, $extension['name']. '_' . $new_name, $new_name, strip_tags( $insert_url ) ) ) {
							bebop_tables::add_to_first_importers_list( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_' . $new_name . '_do_initial_import', $new_name );
							bp_core_add_message( __( 'Feed successfully added.', 'bebop' ) );
						}
						else {
							bp_core_add_message( __( 'This feed already exists, you cannot add it again.', 'bebop' ), 'error' );
						}
					}
					else {
						bp_core_add_message( __( 'That feed cannot be added as it is not a valid URL.', 'bebop' ), 'error' );
					}
				}
				
				//
				//Extension authors: use this hook to add your own data saves.
				do_action( 'bebop_user_settings_pre_edit_save', $extension );
				
				bp_core_redirect( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() );
			}//End if ( isset( $_POST['submit'] ) ) {
			
			//Twitter Oauth stuff
			if ( isset( $_GET['oauth_token'] ) ) {
				//Handle the oAuth requests
				$OAuth = new bebop_oauth();
				$OAuth->set_request_token_url( $extension['request_token_url'] );
				$OAuth->set_access_token_url( $extension['access_token_url'] );
				$OAuth->set_authorize_url( $extension['authorize_url'] );
				
				$OAuth->set_parameters( array( 'oauth_verifier' => $_GET['oauth_verifier'] ) );
				$OAuth->set_callback_url( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() . '/' . $extension['name'] );
				$OAuth->set_consumer_key( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_key' ) );
				$OAuth->set_consumer_secret( bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_secret' ) );
				$OAuth->set_request_token( bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_oauth_token_temp' ) );
				$OAuth->set_request_token_secret( bebop_tables::get_user_meta_value( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_oauth_token_secret_temp' ) );
				
				$accessToken = $OAuth->access_token();
				
				bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_oauth_token', $accessToken['oauth_token'] );
				bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_oauth_token_secret', $accessToken['oauth_token_secret'] );
				bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_active_for_user', 1 );
				bebop_tables::add_to_first_importers_list( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_do_initial_import', 1 );
				
				bp_core_add_message( sprintf( __( 'You have successfully authenticated your %1$s account.', 'bebop' ), $extension['display_name'] ) );
				bp_core_redirect( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() );
			}
			
			//Facebook oAuth stuff.
			if ( isset( $_REQUEST['code'] ) ) {
				
				$app_id = bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_key' );
				$app_secret = bebop_tables::get_option_value( 'bebop_' . $extension['name'] . '_consumer_secret' );
				$my_url = urlencode( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() . '/' . $extension['name'] . '?scope=read_stream' );
				
				if ( $_SESSION['facebook_state'] == $_GET['state'] ) {
					
					$code = $_GET['code'];
					
					$accessTokenUrl = str_replace( 'APP_ID', $app_id, $extension['access_token_url'] );
					$accessTokenUrl = str_replace( 'REDIRECT_URI', $my_url, $accessTokenUrl );
					$accessTokenUrl = str_replace( 'APP_SECRET', $app_secret, $accessTokenUrl );
					$accessTokenUrl = str_replace( 'CODE', $code, $accessTokenUrl );
					
					$response = file_get_contents( $accessTokenUrl );
					parse_str( $response, $params );
					
					//extend access token
					$extendedAccessTokenUrl = str_replace( 'APP_ID', $app_id, $extension['extend_access_token_url'] );
					$extendedAccessTokenUrl = str_replace( 'APP_SECRET', $app_secret, $extendedAccessTokenUrl );
					$extendedAccessTokenUrl = str_replace( 'SHORT_TOKEN', $params['access_token'], $extendedAccessTokenUrl );
					
					$response2 = file_get_contents( $extendedAccessTokenUrl );
					parse_str( $response2, $params2 );
					
					//save the extended access token
					if ( isset( $params['access_token'] ) ) {
						
						bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_oauth_token', $params2['access_token'] );
						bebop_tables::update_user_meta( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_active_for_user', 1 );
						bebop_tables::add_to_first_importers_list( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_do_initial_import', 1 );
						
						bp_core_add_message( sprintf( __( 'You have successfully authenticated your %1$s account.', 'bebop' ), $extension['display_name'] ) );
						bp_core_redirect( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() );
						unset( $_SESSION['facebook_state'] );
					}
				}
			}
			
			
			//delete a user's feed
			if ( isset( $_GET['delete_feed'] ) ) {
				$feed_name = str_replace( ' ', '_', stripslashes( urldecode( $_GET['delete_feed'] ) ) );
				$check_feed = bebop_tables::get_user_meta_value( $bp->loggedin_user->id, $feed_name );
				if ( ! empty( $check_feed ) ) {
					if( filter_var( $check_feed, FILTER_VALIDATE_URL ) ) {
						if ( bebop_tables::remove_user_meta( $bp->loggedin_user->id, $feed_name ) ) {
							bebop_tables::delete_from_first_importers( $bp->loggedin_user->id, $extension['name'], 'bebop_' . $extension['name'] . '_' . $feed_name . '_do_initial_import' );
							bp_core_add_message( __('Feed successfully deleted.', 'bebop' ) );
							//bp_core_redirect( $bp->loggedin_user->domain  . bp_current_component() . '/' . bp_current_action() );
						}
						else {
							bp_core_add_message( __('We could not delete that feed.', 'bebop' ), 'error' );
							bp_core_redirect( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() );
						}
					}
				}
			}
			//resets the user's data - twitter
			if ( isset( $_GET['reset'] ) ) {
				bebop_tables::remove_user_meta( $bp->loggedin_user->id, 'bebop_' . $extension['name'] . '_username' );
			}
			
			//resets the user's data - other
			if ( isset( $_GET['remove_username'] ) ) {
				$username = stripslashes( $_GET['remove_username'] );
				if ( bebop_tables::remove_user_meta_value( $bp->loggedin_user->id, $username ) ) {
					bp_core_add_message( sprintf( __( '%1$s has been removed from your %2$s feed.', 'bebop' ),$username, $extension['display_name'] ) );
					bp_core_redirect( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() );
				}
				else {
					bp_core_add_message( __( 'We could not delete that feed.', 'bebop' ), 'error' );
					bp_core_redirect( $bp->loggedin_user->domain . bp_current_component() . '/' . bp_current_action() );
				}
			}
			
			//Extension authors: use this hook to add your own removal functionality.
			do_action( 'bebop_admin_settings_pre_remove', $extension );
		}//End if ( !empty( $provider ) ) {
	}//End if ( bp_is_current_component( 'bebop' ) && bp_is_current_action('bebop-accounts' ) ) {
}//End function bebop_manage_provider() {

/*
 * Returns status from get array
 */
function bebop_get_oer_type() {
	global $bp, $wpdb;
	if ( bp_is_current_component( 'bebop' ) && bp_is_current_action('bebop-manager' ) ) {
		if ( isset( $_GET['type'] ) ) {
			if ( strtolower( strip_tags( $_GET['type'] == 'unverified' ) ) ) {
				return  __( 'unverified', 'bebop' );
			}
			else if ( strtolower( strip_tags( $_GET['type'] == 'verified' ) ) ) {
				return __( 'verified', 'bebop' );
			}
			else if ( strtolower( strip_tags( $_GET['type'] == 'deleted' ) ) ) {
				return __( 'deleted', 'bebop' );
			}
		}
	}
}
function bebop_get_oers( $type, $page_number, $per_page ) {
	global $bp, $wpdb;
	$active_extensions = bebop_extensions::bebop_get_active_extension_names( $addslashes = true );
	$extension_names   = join( ',' ,$wpdb->escape( $active_extensions ) );
	return bebop_tables::fetch_oer_data( $bp->loggedin_user->id, $extension_names, $type, $page_number, $per_page );
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
					$content = $original_text;
				}
				else {
					$content = '<div class="bebop_activity_container ' . $params['extension'] . '">' . $original_text . '</div>';
				}
				$action  = '<a href="' . bp_core_get_user_domain( $params['user_id'] ) .'" title="' . bp_core_get_username( $params['user_id'] ).'">'.bp_core_get_user_displayname( $params['user_id'] ) . '</a>';
				$action .= ' ' . __( 'posted a', 'bebop' );
				$action .= '<a href="' . $params['actionlink'] . '" target="_blank" rel="external"> '.__( $params['type'], 'bebop_' . $params['extension'] );
				$action .= '</a>: ';
				
				$date_imported = gmdate( 'Y-m-d H:i:s', time() );
				
				//extra check to be sure we don't have an empty activity
				$clean_comment = '';
				$clean_comment = trim( strip_tags( $content ) );
				
				//controls how user content is verified.
				$should_users_verify_content = bebop_tables::get_option_value( 'bebop_' . $params['extension'] . '_content_user_verification' );
				
				if ( $should_users_verify_content == 'no' ) {
					$oer_status = 'verified';
				}
				else {
					$oer_status = 'unverified';
				}
				
				$hide_sitewide = bebop_tables::get_option_value( 'bebop_' . $params['extension'] . '_hide_sitewide' );
				if( $hide_sitewide == 'yes' ) {
					$oer_hide_sitewide = 1;
				}
				else {
					$oer_hide_sitewide = 0;
				}
				
				if ( ! empty( $clean_comment ) ) {
					
					if ( bebop_filters::day_increase( $params['extension'], $params['user_id'], $params['username'] ) ) {
						if ( $wpdb->query(
										$wpdb->prepare(
														'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager ( user_id, status, type, action, content, secondary_item_id, date_imported, date_recorded, hide_sitewide ) VALUES ( %s, %s, %s, %s, %s, %s, %s, %s, %s )',
														$wpdb->escape( $params['user_id'] ), $oer_status, $wpdb->escape( $params['extension'] ), $wpdb->escape( $action ), $wpdb->escape( $content ),
														$wpdb->escape( $params['item_id'] ), $wpdb->escape( $date_imported ), $wpdb->escape( $params['raw_date'] ), $wpdb->escape( $oer_hide_sitewide )
										)
						) ) {
							
							//if users shouldn't verify content, add it to the activity stream immediately.
							if ( $should_users_verify_content == 'no' ) {
								
								$new_activity_item = array (
											'user_id'			=> $params['user_id'],
											'component'			=> 'bebop_oer_plugin',
											'type'				=> $params['extension'],
											'action'			=> $action,
											'content'			=> $content,
											'secondary_item_id'	=> $params['item_id'],
											'date_recorded'		=> $date_imported,
											'hide_sitewide'		=>$oer_hide_sitewide,
								);
								if ( bp_activity_add( $new_activity_item ) ) {
									bebop_tables::update_oer_data( $params['item_id'], 'activity_stream_id', $activity_stream_id = $wpdb->insert_id );
								}
							}
						}
						else {
							bebop_tables::log_error( __( 'Importer', 'bebop' ), __( 'Import query error', 'bebop' ) );
						}
					}//End if ( bebop_filters::day_increase( $params['extension'], $params['user_id'], $params['username'] ) ) {
					else {
						bebop_tables::log_error( __( 'Importer', 'bebop' ), __( 'Could not import as a daycounter could not be found.', 'bebop' ) );
						return false;
					}
				}
				else {
					bebop_tables::log_error( __( 'Importer', 'bebop' ), __( 'Could not import, content already exists.', 'bebop' ) );
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			bebop_tables::log_error( sprintf(__( 'Import Error - %1$s', 'bebop' ), $params['extension'] ), sprintf( __( '%1$s already exists', 'bebop' ), $params['item_id'] ) );
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
	$active_extensions = bebop_extensions::bebop_get_active_extension_names();
	foreach ( $active_extensions as $extension ) {
		if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_provider' ) == 'on' ) {
			$this_extension = bebop_extensions::bebop_get_extension_config_by_name( $extension );
			$store[] = '<option value="' . $this_extension['name'] .'">' . $this_extension['display_name'] . '</option>';
		}
	}
	
	//Ensures the All OER only shows if there are two or more OER's to choose from.
	if ( count( $store ) >= 2 ) {
		echo '<option value="all_oer">' . __( 'All OERs', 'bebop' ) . '</option>';
	}
	else if ( count( $store ) === 0 ) {
		echo '<option>' . __( 'No Extensions are active - please enable them in the admin panel', 'bebop' ) . '</option>';
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
	if ( $bp->current_component == 'bebop' ) {
		
		//Passes the query string as an array as its easy to determine the page number then "if any".
		parse_str( $query_string, $str );
		$page_number = '';
		//This checks if there is a certain page and if so ensure it is saved to be put into the query string.
		if ( isset( $str['page'] ) ) {
			$page_number = '&page=' . $str['page'];
		}
		//if type isnt set or it equals 'all_oer'
		if ( ( ! isset( $str['type']) ) || ( $str['type'] == 'all_oer' ) ) {
			//Sets the string_build variable ready.
			$string_build = '';
			
			//Loops through all the different extensions and adds the active extensions to the temp variable.
			$active_extensions = bebop_extensions::bebop_get_active_extension_names();
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
		
		//if the query string is empty there are no extensions active. fill with an obscure query type/action.
		if ( empty( $new_query_string ) ) {
			$new_query_string = 'type=thisisadelightfulplugin&action=soitisitsamazing';
		}
		//Sets the page number for the bebop page.
		$new_query_string .= '&per_page=10&show_hidden=true';
		
		//sets the reset session variable to allow for resetting activty stream if they have come from the oer page.
		if ( isset( $_SESSION['bebop_area'] ) ) {
			if ( $_SESSION['bebop_area'] == 'not_bebop_oer_plugin' ) {
				$_SESSION['bebop_area'] = 'bebop_oer_plugin';
			}
		}
		else {
			$_SESSION['bebop_area'] = 'bebop_oer_plugin';
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
				$active_extensions = bebop_extensions::bebop_get_active_extension_names();
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
		if ( isset( $_SESSION['bebop_area'] ) ) {
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
		else {
			$_SESSION['bebop_area'] = 'not_bebop_oer_plugin';
		}
	}
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
function bebop_rss_buttons() {
	global $bp;
	
	$count = 0;
	$rss_active_extensions = array();
	$extensions = bebop_extensions::bebop_get_active_extension_names();
	
	$user = $bp->displayed_user->userdata;
	
	echo '<div class="rss_feed_container">';
	foreach ( $extensions as $extension ) {
		if ( bebop_tables::get_option_value( 'bebop_' . $extension . '_rss_feed' ) == 'on' ) {
			$extension = bebop_extensions::bebop_get_extension_config_by_name( strtolower( $extension ) );
			if ( bebop_tables::get_user_meta_value( $user->ID, 'bebop_' . $extension['name'] . '_active_for_user' ) == 1 ) {
				echo '<a class="button bp-secondary-action" href="' . get_bloginfo('url') . '/' . $user->user_nicename . '/' . bp_get_activity_slug() . '/' . $extension['name'] . '"><img style="vertical-align: text-top;"' .
				'src="' . plugins_url() . '/bebop/core/resources/images/feed_14px.png"> ' .$extension['display_name'] . '</a>';
				$count++;
			}
		}
	}
	if ( $count >= 2 ) {
		echo ' <a class="button bp-secondary-action" href="' . get_bloginfo('url') . '/' . $user->user_nicename . '/' . bp_get_activity_slug() . '/all_oers"><img style="vertical-align: text-top;"' . 
		'src="' . plugins_url() . '/bebop/core/resources/images/feed_14px.png"> All</a>';
	}

	echo '</div>';
}

//pagination
function bebop_pagination_vars( $custom_per_page = null ) {
	if ( isset( $_GET['page_number'] ) ) {
		$page_number = substr( strip_tags( $_GET['page_number'] ), 0 , 4 );
	}
	if ( empty( $page_number ) || ! is_numeric( $page_number ) ) {
		$page_number = 1;
	}
	
	if ( isset( $_GET['per_page'] ) ) {
		$per_page = substr( strip_tags( $_GET['per_page'] ), 0 , 4 );
	}
	if ( empty( $per_page ) || ! is_numeric( $per_page ) ) {
		if ( ! empty( $custom_per_page ) || ! is_numeric( $custom_per_page ) ) {
			$per_page = $custom_per_page;
		}
		else {
			$per_page = 30;
		}
	}
	
	return array(
		'page_number'	=> $page_number,
		'per_page'		=> $per_page,
	);
}
function bebop_pagination( $number_of_rows, $per_page ) {
	$number_of_pages = (int)ceil( $number_of_rows / $per_page );
	if( isset( $_GET['page_number'] ) ) {
		$this_page_number = $_GET['page_number'];
		unset($_GET['page_number']);
		unset($_GET['per_page']);
	}
		
	$return = '<div class="margin-top_22px clear_above">';
	for ( $i = 1; $i <= $number_of_pages; $i++ ) {
		
		$return .= '<a href="?' . http_build_query( $_GET ) . '&page_number=' . $i . '&per_page=' . $per_page . '">';
		
		if ( ( isset( $this_page_number ) && $this_page_number == $i )  ||
		( ( ! isset( $this_page_number ) ) && $i == 1 ) ) {
			$return .= '<strong>' . $i . '</strong>';
		}
		else {
			$return .= $i;
		}
		$return .= '</a> ';
	}
	$return .= '</div>';
	return $return;
}
	
?>
