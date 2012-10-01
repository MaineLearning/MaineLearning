<?php
//table manipulation.
class bebop_tables {
	/*
	* Admin functions
	*/
	function flush_table_data( $table_name ) {
		global $wpdb;
		
		if ( $wpdb->get_results( 'TRUNCATE TABLE ' . bp_core_get_table_prefix() . $table_name ) ) {
			bebop_tables::log_error( __( 'Table Truncate error', 'bebop' ), sprintf( __( 'Could not empty the %1$s table.', 'bebop'), $table_name ) );
			return false;
		}
		else {
			return true;
		}
	}
	
	function count_users_using_extension( $extension, $status ) {
		global $wpdb;
		
		$count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE meta_name = 'bebop_" . $wpdb->escape( $extension ) . "_active_for_user' AND meta_value='" . $wpdb->escape( $status ) . "'" ) );
		return $count;
	}
	
	function count_content_by_extension( $extension, $status ) {
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager WHERE type = '" . $wpdb->escape( $extension ) . "' AND status = '" . $wpdb->escape( $status ) . "'" ) );
		return $count;
	}
	
	//function to remove a table from the database.
	function drop_table( $table_name ) {
		global $wpdb;
		
		if ( $wpdb->query( 'DROP TABLE IF EXISTS ' . bp_core_get_table_prefix() . $table_name ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	//function to remove activity data imported by the plugin. - Part of the uninstall process.
	function remove_activity_stream_data() {
		global $wpdb, $bp;
		
		if ( $wpdb->get_results( 'DELETE FROM ' . $bp->activity->table_name ." WHERE component = 'bebop_oer_plugin'" ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	//function to remove user data by oer provider (admin function)
	function remove_user_from_provider( $user_id, $provider ) {
		global $wpdb, $bp;
		
		if ( ( $wpdb->get_results( 'DELETE FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta  WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND meta_type LIKE '%" . like_escape( $provider ) . "%'" ) ) || 
		( $wpdb->get_results( 'DELETE FROM ' . $bp->activity->table_name . " WHERE component = 'bebop_oer_plugin' AND type LIKE '%" . like_escape( $provider ) . "%'" ) ) ||
		( $wpdb->get_results( 'DELETE FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND type LIKE '%" . like_escape( $provider ) . "%'" ) ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function bebop_check_existing_content_buffer( $user_id, $extension, $content ) {
		global $wpdb;
		$content = strip_tags( $content );
		$content = like_escape( $content );
		
		if ( $wpdb->get_row( 'SELECT content FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager user_id = '" . $wpdb->escape( $user_id ) . "' AND type = '" . $wpdb->escape( $extension ) . "' AND content LIKE '%" . $content . "%'" ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function fetch_oer_data( $user_id, $extensions, $status ) { //function to retrieve oer data from the oer manager table.
		global $wpdb;
		
		$result = $wpdb->get_results( 'SELECT * FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND status = '" . $wpdb->escape( $status ) . "' AND type IN ( ". stripslashes( $extensions ) . ') ORDER BY date_imported DESC' );
		return $result;
	}
	 
	 function admin_fetch_content_data( $status, $limit = null ) { //function to retrieve all oer data by status in the oer manager table.
		global $wpdb;
		
		if ( $limit != null ) {
			$result = $wpdb->get_results( 'SELECT * FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager WHERE status = '" . $wpdb->escape( $status ) . "' ORDER BY date_imported DESC LIMIT $limit");
		}
		else {
			$result = $wpdb->get_results( 'SELECT * FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager WHERE status = '" . $wpdb->escape( $status ) . "' ORDER BY date_imported DESC");
		}
		return $result;
	}

	function fetch_individual_oer_data( $secondary_item_id ) {
		global $wpdb;
		$result = $wpdb->get_results( 'SELECT * FROM ' . bp_core_get_table_prefix() . "bp_bebop_oer_manager WHERE secondary_item_id = '" . $wpdb->escape( $secondary_item_id ) . "'" );
		if ( ! empty( $result[0]->secondary_item_id ) ) {
			return $result[0];
		}
	}
	
	function update_oer_data( $secondary_item_id, $column, $value ) {
		global $wpdb;
		
		$result = $wpdb->query( 'UPDATE ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager SET ' . $column . " = '"  . $wpdb->escape( $value ) . "' WHERE secondary_item_id = '" . $wpdb->escape( $secondary_item_id ) . "' LIMIT 1" );
		if ( ! empty( $result ) ) {
			return $result;
		}
		else {
			return false;
		}
	}
	
	/*
	* Tables
	*/
	
	function log_error( $error_type, $error_message ) { //function to log errors into the error table.
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_error_log( error_type, error_message ) VALUES ( %s, %s )', $wpdb->escape( $error_type ), $wpdb->escape( $error_message ) ) );
	}
	
	function log_general( $type, $message ) { //function to log general events into the log table.
		global $wpdb;

		$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_general_log (type, message) VALUES (%s, %s)', $wpdb->escape( $type ), $wpdb->escape( $message ) ) );
	}
	
	/*
	* Options
	*/
	function fetch_table_data( $table_name ) { //function to retrieve stuff from tables
		global $wpdb;
		
		$result = $wpdb->get_results( 'SELECT * FROM ' . bp_core_get_table_prefix() . $table_name . ' ORDER BY id DESC' );
		return $result;
	}
	
	function add_option( $option_name, $option_value ) { //function to add option to the options table.
		global $wpdb;
		if ( bebop_tables::check_option_exists( $option_name ) == false ) {
			$wpdb->query( $wpdb->prepare( 'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_options (option_name, option_value) VALUES (%s, %s)', $wpdb->escape( $option_name ), $wpdb->escape( $option_value ) ) );
			return true;
		}
		else {
			bebop_tables::log_error( __( 'bebop_option_error', 'bebop' ), sprintf( __( 'option: %1$s already exists.', 'bebop'), $option_name ) );
			return false;
		}
	}
	
	function check_option_exists( $option_name ) { //function to chech whether an option exists in the options table.
		global $wpdb;
		$result = $wpdb->get_row( 'SELECT option_name FROM ' . bp_core_get_table_prefix() . "bp_bebop_options WHERE option_name = '" . $wpdb->escape( $option_name ) . "'" );
		if ( ! empty( $result->option_name ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function get_option_value( $option_name ) { //function to get an option from the options table.
		global $wpdb;
		$result = $wpdb->get_row( 'SELECT option_value FROM ' . bp_core_get_table_prefix() . "bp_bebop_options WHERE option_name = '" . $wpdb->escape( $option_name ) . "'" );
		
		if ( isset( $result->option_value ) && is_numeric( $result->option_value ) ) {
				return $result->option_value;
			}
		else if ( ! empty($result->option_value ) ) {
			return $result->option_value;
		}
		else {
			return false;
		}
	}
	
	function update_option( $option_name, $option_value ) { //function to update an option in the options table.
		global $wpdb;
		if ( bebop_tables::check_option_exists( $option_name ) == true ) {
			$result = $wpdb->query( 'UPDATE ' . bp_core_get_table_prefix() . "bp_bebop_options SET option_value = '"  . $wpdb->escape( $option_value ) . "' WHERE option_name = '" . $wpdb->escape( $option_name ) . "' LIMIT 1" );
			if ( ! empty( $result ) ) {
				return $result;
			}
			else {
				return false;
			}
		}
		else {
			bebop_tables::add_option( $option_name, $option_value );
			bebop_tables::update_option( $option_name, $option_value );
		}
	}
	
	function remove_option( $option_name ) { //function to remove an option from the options table.
		global $wpdb;
		
		if ( bebop_tables::check_option_exists( $option_name ) == true ) {
			$wpdb->get_results( 'DELETE FROM ' . bp_core_get_table_prefix() . "bp_bebop_options WHERE option_name = '" . $wpdb->escape( $option_name ) . "' LIMIT 1" );
			return true;
		}
		else {
			bebop_tables::log_error( __( 'bebop_option_error', 'bebop' ),  sprintf( __( 'option: %1$s does not exist.', 'bebop'), $option_name ) );
			return false;
		}
	}
	
	/*
	* User Meta
	*/
	
	function check_user_meta_exists( $user_id, $meta_name ) { //function to check if user meta name exists in the user_meta table.
		global $wpdb;
		$meta_name = addslashes( $meta_name );
		$result = $wpdb->get_row( 'SELECT meta_name FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND meta_name = '" . $wpdb->escape( $meta_name ) . "'" );
		if ( ! empty( $result->meta_name ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	function check_user_meta_value_exists( $user_id, $meta_name, $meta_value ) { //function to check if user meta value aready exists for a user and an extension. This is used for adding multiple feeds.
		global $wpdb;
		$result = $wpdb->get_row( 'SELECT meta_value FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND meta_name = '" . $wpdb->escape( $meta_name ) . "' AND meta_value = '" . $wpdb->escape( $meta_value ) . "'" );
		
		if ( ! empty( $result->meta_value ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	/*Special function to return list of users with a specific import type */
	function get_user_ids_from_meta_name( $meta_name ) {//function to get user id's from the meta table
		global $wpdb;
		$result = $wpdb->get_results( 'SELECT user_id FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE meta_name = '" . $wpdb->escape( $meta_name )  . "'" );
		return $result;
	}
	
	function get_user_ids_from_meta_type( $meta_type ) {//function to get user id's from the meta table
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT user_id FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE meta_type= '" . $wpdb->escape( $meta_type )  . "'" );
		$return = array();
		foreach ( $results as $result ) {
			if ( ! in_array( $result, $return ) ) {
				$return[] = $result;
			}
		}
		return $return;
	}
	
	function get_user_meta_value( $user_id, $meta_name ) {//function to get user meta from the user_meta table.
		global $wpdb;
		
		$meta_name = str_replace( "'", "\\\\\'", $meta_name );
		$result = $wpdb->get_row( 'SELECT meta_value FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND meta_name = '" . $meta_name . "'" );
		
		if( ! empty( $result ) ) {
			
			if ( is_numeric(  $result->meta_value ) ) {
				return $result->meta_value;
			}
			else if ( ! empty($result->meta_value ) ) {
				return $result->meta_value;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	function add_user_meta( $user_id, $meta_type, $meta_name, $meta_value, $check_meta_value = false ) { //function to add user meta to the user_meta table. - allow_multiple skips existence checks and inserts.
		global $wpdb;
		if ( $check_meta_value == false ) {
			if ( bebop_tables::check_user_meta_exists( $user_id, $meta_name ) == false ) {
				$wpdb->query(
								$wpdb->prepare(
												'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_user_meta (user_id, meta_type, meta_name, meta_value) VALUES (%s, %s, %s, %s)',
												$wpdb->escape( $user_id ), $wpdb->escape( $meta_type ), $wpdb->escape( $meta_name ), $wpdb->escape( $meta_value )
								)
				);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			if ( bebop_tables::check_user_meta_value_exists( $user_id, $meta_name, $meta_value ) == false ) {
				$wpdb->query(
									$wpdb->prepare(
													'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_user_meta (user_id, meta_type, meta_name, meta_value) VALUES (%s, %s, %s, %s)',
													$wpdb->escape( $user_id ), $wpdb->escape( $meta_type ), $wpdb->escape( $meta_name ), $wpdb->escape( $meta_value )
									)
						);
				return true;
			}
			else {
				return false;
			}
		}
	}
	
	function update_user_meta( $user_id, $meta_type, $meta_name, $meta_value ) { //function to update user meta in the user_meta table.
		global $wpdb;
		if ( bebop_tables::check_user_meta_exists( $user_id, $meta_name ) == true ) {
			
			$meta_name = str_replace( "'", "\\\\\'", $meta_name ); //damn quotes.
			$result = $wpdb->query( 'UPDATE ' . bp_core_get_table_prefix() . "bp_bebop_user_meta SET meta_value = '"  . $wpdb->escape( $meta_value ) . "' WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND meta_name = '" . $meta_name . "' LIMIT 1" );
			if ( ! empty( $result ) ) {
				return $result;
			}
			else {
				return false;
			}
		}
		else {
			bebop_tables::add_user_meta( $user_id, $meta_type, $meta_name, $meta_value );
			return false;
		}
	}
	
	function remove_user_meta( $user_id, $meta_name ) { //function to remove user meta from the user_meta table.
		global $wpdb;
		if ( bebop_tables::check_user_meta_exists( $user_id, $meta_name ) == true ) {
			$meta_name = addslashes( $meta_name );
			$results = $wpdb->get_results( 'DELETE FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND meta_name = '" . $wpdb->escape( $meta_name ) . "' LIMIT 1" );
			if ( mysql_affected_rows() > 0 ) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	function remove_user_meta_value( $user_id, $meta_value ) { //function to remove user meta value from the user_meta table.
		global $wpdb;
		
		$meta_value = addslashes( $meta_value );
		$results = $wpdb->get_results( 'DELETE FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND meta_value = '" . $wpdb->escape( $meta_value ) . "' LIMIT 1" );
		if ( mysql_affected_rows() > 0 ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function get_user_feeds( $user_id, $provider ) {
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT meta_name, meta_value FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE meta_type LIKE '%" . like_escape( $provider ) . "%' AND user_id = '" . $wpdb->escape( $user_id ) . "'" );
		//filter out data we do not want.
		$blacklist = array( 'active', 'counter' );
		$return = array();
		
		foreach ( $results as $result ) {
			foreach ( $blacklist as $value ) {
				$found = false;
				if ( ! stristr( $result->meta_name, $value ) === false ) {
					$found = true;
					break;
				}
			}
			if ( $found == false ) {
				$return[] = $result;
			}
		}
		return $return;
	}

	function get_user_feeds_from_array( $user_id, $provider, $feeds ) {
		global $wpdb;
		if ( is_array( $feeds ) ) {
			$return = array();
			foreach ( $feeds as $feed ) {
				$feed = addslashes( $feed ) ;
				$results = $wpdb->get_row( 'SELECT meta_name, meta_value FROM ' . bp_core_get_table_prefix() . "bp_bebop_user_meta WHERE user_id = '" . $wpdb->escape( $user_id ) .
				"' AND meta_type = '" . $wpdb->escape( $provider . '_' . $feed ) .
				"' AND meta_name = '" . $wpdb->escape( $feed ) . "'" );
				$return[] = $results;
			}
			return $return;
		}
		return false;
	}

	
	function add_to_first_importers_list( $user_id, $extension, $name, $value = null ) {
		global $wpdb;
		if ( empty( $value ) ) {
			$wpdb->query(
									$wpdb->prepare(
													'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_first_imports (user_id, extension, name) VALUES (%s, %s, %s)',
													$wpdb->escape( $user_id ), $wpdb->escape( $extension ), $wpdb->escape( $name )
									)
					);
			return true;
		}
		else {
			$wpdb->query(
									$wpdb->prepare(
													'INSERT INTO ' . bp_core_get_table_prefix() . 'bp_bebop_first_imports (user_id, extension, name, value) VALUES (%s, %s, %s, %s)',
													$wpdb->escape( $user_id ), $wpdb->escape( $extension ), $wpdb->escape( $name ), $wpdb->escape( $value )
									)
					);
			return true;
			
		}
	}
	function delete_from_first_importers( $user_id, $extension, $name ) {
		global $wpdb;
		$name = addslashes( $name );
		$wpdb->get_results( 'DELETE FROM ' . bp_core_get_table_prefix() . "bp_bebop_first_imports WHERE user_id = '" . $wpdb->escape( $user_id ) . "' AND extension = '" . $wpdb->escape( $extension ) . "' 
		AND name = '" . $wpdb->escape( $name ) . "' LIMIT 1" );
		return true;
	}
	
	function get_first_importers_by_extension( $extension ) {
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT user_id FROM ' . bp_core_get_table_prefix() . "bp_bebop_first_imports WHERE extension = '" . $wpdb->escape( $extension ) ."'" );
		return $results;
	}
	
	function check_for_first_import( $user_id, $extension, $name ) {
		global $wpdb;
		$name = addslashes( $name );
		$results =  $wpdb->get_results( 'SELECT name FROM ' . bp_core_get_table_prefix() . "bp_bebop_first_imports WHERE user_id = '" . $wpdb->escape( $user_id ) ."' AND extension = '" . $wpdb->escape( $extension ) . 
		"' AND name = '" . $wpdb->escape( $name ) ."'" );
		
		if ( ! empty( $results ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function get_initial_import_feeds( $user_id, $extension ) {
		global $wpdb;
		$return = array();
		$results = $wpdb->get_results( 'SELECT value FROM ' . bp_core_get_table_prefix() . "bp_bebop_first_imports WHERE user_id = '" . $wpdb->escape( $user_id ) ."' AND extension = '" . $wpdb->escape( $extension ) . "'" );
		foreach ( $results as $result ) {
			$return[] = bebop_tables::sanitise_element( $result->value );
		}
		return $return;
	}
	
	function sanitise_element( $data, $allow_tags = null ) {
		if(	$allow_tags == true ) {
			return stripslashes( $data );
		}
		else {
			return stripslashes( strip_tags( $data ) );
		}
	}
} 
