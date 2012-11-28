<?php
/**
 * Extension Import function. You will need to modify this function slightly ensure all values are added to the database.
 * Please see the section below on how to do this.
 */

//replace 'twitter' with the 'name' of your extension, as defined in your config.php file.
function bebop_twitter_import( $extension, $user_metas = null ) {
	global $wpdb, $bp;
	if ( empty( $extension ) ) {
		bebop_tables::log_general( 'Importer', 'The $extension parameter is empty.' );
		return false;
	}
	else if ( ! bebop_tables::check_option_exists( 'bebop_' . $extension . '_consumer_key' ) ) {
		bebop_tables::log_general( 'Importer', 'No consumer key was found for ' . $extension );
		return false;
	}
	else {
		$this_extension = bebop_extensions::bebop_get_extension_config_by_name( $extension );
	}
	
	//item counter for in the logs
	$itemCounter = 0;
	
	//if no user_metas are supplied, serarch for them.
	if( ! isset( $user_metas ) ) {
		$user_metas = bebop_tables::get_user_ids_from_meta_name( 'bebop_' . $this_extension['name'] . '_oauth_token' );
	}
	
	if ( isset( $user_metas ) ) {
		foreach ( $user_metas as $user_meta ) {
			$errors = null;
			$items 	= null;
			
			//Ensure the user is currently wanting to import items.
			if ( bebop_tables::get_user_meta_value( $user_meta->user_id, 'bebop_' . $this_extension['name'] . '_active_for_user' ) == 1 ) {
				
				//if it is the first import, update the flag.
				if ( bebop_tables::check_for_first_import( $user_meta->user_id, $this_extension['name'], 'bebop_' . $this_extension['name'] . '_do_initial_import' ) ) {
					bebop_tables::delete_from_first_importers( $user_meta->user_id, $this_extension['name'], 'bebop_' . $this_extension['name'] . '_do_initial_import' );
				}
				
				/* 
				 * ******************************************************************************************************************
				 * Depending on the data source, you will need to switch how the data is retrieved. If the feed is RSS, use the 	*
				 * SimplePie method, as shown in the youtube extension. If the feed is oAuth API based, use the oAuth implementation*
				 * as shown in the twitter extension. If the feed is an API without oAuth authentication, use SlideShare			*
				 * ******************************************************************************************************************
				 */
				
				//Twitter uses oAuth - so let the pre-built oAuth class handle the data request.
				
				//Handle the OAuth requests
				$OAuth = new bebop_oauth();
				$OAuth->set_callback_url( $bp->root_domain );
				$OAuth->set_consumer_key( bebop_tables::get_option_value( 'bebop_' . $this_extension['name'] . '_consumer_key' ) );
				$OAuth->set_consumer_secret( bebop_tables::get_option_value( 'bebop_' . $this_extension ['name']. '_consumer_secret' ) );
				$OAuth->set_access_token( bebop_tables::get_user_meta_value( $user_meta->user_id, 'bebop_' . $this_extension['name'] . '_oauth_token' ) );
				$OAuth->set_access_token_secret( bebop_tables::get_user_meta_value( $user_meta->user_id, 'bebop_' . $this_extension['name'] . '_oauth_token_secret' ) );
				
				$items = $OAuth->oauth_request( $this_extension['data_feed'] );
				$items = simplexml_load_string( $items );
				/* 
				 * ******************************************************************************************************************
				 * We can get as far as loading the items, but you will need to adjust the values of the variables below to match 	*
				 * the values from the extension's API.																				*
				 * This is because each API return data under different parameter names, and the simplest way to get around this is *
				 * to quickly match the values. To find out what values you should be using, consult the provider's documentation.	*
				 * You can also contact us if you get stuck - details are in the 'support' section of the admin homepage.			*
				 * ******************************************************************************************************************
				 * 
				 * Values you will need to check and update are:
				 *		$errors 				- Must point to the error boolean value (true/false)
				 *		$username				- Must point to the value holding the username of the person.
				 *		$id						- Must be the ID of the item returned through the data API.
				 *		$item_content			- The actual content of the imported item.
				 *		$item_published			- The time the item was published.
				 *		$action_link			- This is where the link will point to - i.e. where the user can click to get more info.
				 */
				
				//Edit the following two variables to point to where the relevant content is being stored in the API:
				$errors		 = $items->error;
				$username	 = $items->status->user->screen_name[0];
				
				if ( ! $errors ) {
					if ( $items ) {
					
						bebop_tables::update_user_meta( $user_meta->user_id, $this_extension['name'], 'bebop_' . $this_extension['name'] . '_username', $username );
						foreach ( $items as $item ) {
							if ( ! bebop_filters::import_limit_reached( $this_extension['name'], $user_meta->user_id, $username ) ) {
								
								//Edit the following three variables to point to where the relevant content is being stored:
								$id					= $item->id;
								$item_content		= $item->text;
								$item_published		= gmdate( 'Y-m-d H:i:s', strtotime( $item->created_at ) );
								$action_link 		= str_replace( 'bebop_replace_username', $username, $this_extension['action_link'] ) . $id;
								//Stop editing - you should be all done.
								
								
								//generate an $item_id
								$item_id = bebop_generate_secondary_id( $user_meta->user_id, $id, $item_published );
								
								//check if the secondary_id already exists
								$secondary = bebop_tables::fetch_individual_oer_data( $item_id );
								//if the id is not found, import the content.
								if ( empty( $secondary->secondary_item_id ) ) {
									
									if ( bebop_create_buffer_item(
													array(
														'user_id'			=> $user_meta->user_id,
														'extension'			=> $this_extension['name'],
														'type'				=> $this_extension['content_type'],
														'username'			=> $username,							//required for day counter increases.
														'content'			=> $item_content,
														'content_oembed'	=> $this_extension['content_oembed'],
														'item_id'			=> $item_id,
														'raw_date'			=> $item_published,
														'actionlink'		=> $action_link,
													)
									) ) {
										$itemCounter++;
									}
								}
							}
						}
					}
				}
				else {
					bebop_tables::log_error( sprintf( __( 'Importer - %1$s', 'bebop' ), $this_extension['display_name'] ), sprintf( __( 'Feed Error: %1$s', 'bebop' ), $errors ) );
				}
			}
		}
	}
	//return the result
	return $itemCounter . ' ' . $this_extension['content_type'] . 's';
}