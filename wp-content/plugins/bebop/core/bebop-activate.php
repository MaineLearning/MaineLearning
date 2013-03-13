<?php
global $wpdb;

//create tables if necessary.
$bebop_error_log = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_error_log ( 
	id bigint(20) NOT NULL auto_increment PRIMARY KEY, 
	timestamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	error_type varchar(40) NOT NULL,
	error_message text NOT NULL
);';
$bebop_general_log = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_general_log ( 
	id bigint(20) NOT NULL auto_increment PRIMARY KEY,
	timestamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	type varchar(40) NOT NULL,
	message text NOT NULL
);';

$bebop_options = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_options ( 
	timestamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,	
	option_name varchar(100) NOT NULL PRIMARY KEY,
	option_value longtext NOT NULL
);';

$bebop_user_meta = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_user_meta ( 
	id bigint(20) NOT NULL auto_increment PRIMARY KEY,
	user_id bigint(20) NOT NULL,
	meta_type varchar(255) NOT NULL,
	meta_name varchar(255) NOT NULL,
	meta_value longtext NOT NULL
);';

$bebop_oer_manager = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager ( 
	id bigint(20) NOT NULL auto_increment PRIMARY KEY,
	user_id bigint(20) NOT NULL,
	status varchar(75) NOT NULL,
	type varchar(255) NOT NULL,
	action text NOT NULL,
	content longtext NOT NULL,
	activity_stream_id bigint(20),
	secondary_item_id varchar(32),
	date_imported datetime,
	date_recorded datetime,
	hide_sitewide tinyint(1)
);';

$bebop_first_import = 'CREATE TABLE IF NOT EXISTS ' . bp_core_get_table_prefix() . 'bp_bebop_first_imports ( 
	id bigint(20) NOT NULL auto_increment PRIMARY KEY,
	user_id bigint(20) NOT NULL,
	extension varchar(255) NOT NULL,
	name varchar(255) NOT NULL,
	value longtext NOT NULL
);'; 
//run queries
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $bebop_error_log );
dbDelta( $bebop_general_log );
dbDelta( $bebop_options );
dbDelta( $bebop_user_meta );
dbDelta( $bebop_oer_manager );
dbDelta( $bebop_first_import );

//cleanup
unset( $bebop_error_log );
unset( $bebop_general_log );
unset( $bebop_options );
unset( $bebop_user_meta );
unset( $bebop_oer_manager );
unset( $bebop_first_import );


//Scripts to change DB architecture and data from from previous versions of bebop to the latest version.

//1 - update wp_bp_activity to use wp_bp_bebop_oer_manager id as the wp_bp_activity primary_item_id and remove secondary item_id
$update_1 = bebop_tables::get_option_value( 'bebop_db_update_1' );
if ( ! $update_1 ) {
	$secondary_ids				= array();
	$update_item_id				= array();
	$update_secondary_item_id 	= array();
	
	$secondary_id_query = $wpdb->get_results( 'SELECT secondary_item_id FROM ' . bp_core_get_table_prefix() . 'bp_activity WHERE component = "bebop_oer_plugin"' );
	if ( ! empty( $secondary_id_query ) )
	{
		foreach ( $secondary_id_query as $result ) {
			//get it's wp_bp_bebop_oer_manager id
			$secondary_ids[] = $result->secondary_item_id;
			unset($result);
		}
		$ids = implode( ',', $secondary_ids );
		$oer_manager_query = $wpdb->get_results( 'SELECT id, secondary_item_id FROM ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager WHERE secondary_item_id IN (' . $ids . ')' );
		foreach ( $oer_manager_query as $result )
		{
			$update_item_id[] 				= array( 'indices' => array( 'secondary_item_id' => $result->secondary_item_id ), 'data' => $result->id );
			$update_secondary_item_id[] 	= array( 'indices' => array( 'secondary_item_id' => $result->secondary_item_id ), 'data' => '' );
			unset($result);
		}
		$update_array = array(
			'item_id' 			=> $update_item_id,
			'secondary_item_id' => $update_secondary_item_id
		);
		
		$update_string = array();
		foreach ( $update_array as $key => $data )
		{
			$string = $key . ' = CASE ';
			foreach ( $data as $update_data )
			{
				$indices_loop = array();
				foreach (  $update_data['indices'] as $index_name => $index_data )
				{
					$indices_loop[] = $index_name . ' = \'' . $index_data . '\'';
					unset($index_data);
				}
				$string .= 'WHEN ' . implode (' AND ', $indices_loop ) . ' THEN  \'' . $update_data['data'] . '\' ';
				unset($update_data);
			}
			$update_string[] = $string . 'ELSE ' . $key . ' END';
			unset($data);
		}
		$query = implode( ', ', $update_string );
		$update = $wpdb->get_results( 'UPDATE ' . bp_core_get_table_prefix() . 'bp_activity SET ' . $query );
		unset($secondary_ids);
		unset($update_item_id);
		unset($update_secondary_item_id);
	}
	bebop_tables::add_option( 'bebop_db_update_1', true );
}

//2 - update column definitions
$update_2 = bebop_tables::get_option_value( 'bebop_db_update_2' );
if ( ! $update_2 ) {
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager MODIFY secondary_item_id VARCHAR(32)' );
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT' );
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager MODIFY user_id BIGINT(20)' );
	
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_user_meta MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT' );
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_user_meta MODIFY user_id BIGINT(20)' );
	
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_first_imports MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT' );
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_first_imports MODIFY user_id BIGINT(20)' );
	
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_error_log MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT' );
	$wpdb->get_results( 'ALTER TABLE ' . bp_core_get_table_prefix() . 'bp_bebop_general_log MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT' );
	bebop_tables::add_option( 'bebop_db_update_2', true );
}

//3 - hash secondary_item_ids
$update_3 = bebop_tables::get_option_value( 'bebop_db_update_3' );
if ( ! $update_3 ) {
	$update_secondary_item_id = array();
	$results = $wpdb->get_results( 'SELECT id, secondary_item_id FROM ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager' );
	if ( ! empty( $results ) )
	{
		foreach ( $results as $result ) {
			$update_secondary_item_id[] = array( 'indices' => array( 'id' => $result->id ), 'data' => md5( $result->secondary_item_id ) );
		}
		$update_array = array(
			'secondary_item_id' => $update_secondary_item_id
		);
		
		$update_string = array();
		foreach ( $update_array as $key => $data )
		{
			$string = $key . ' = CASE ';
			foreach ( $data as $update_data )
			{
				$indices_loop = array();
				foreach (  $update_data['indices'] as $index_name => $index_data )
				{
					$indices_loop[] = $index_name . ' = \'' . $index_data . '\'';
				}
				$string .= 'WHEN ' . implode (' AND ', $indices_loop ) . ' THEN  \'' . $update_data['data'] . '\' ';
			}
			$update_string[] = $string . 'ELSE ' . $key . ' END';
		}
		$query = implode( ', ', $update_string );
		$update = $wpdb->get_results( 'UPDATE ' . bp_core_get_table_prefix() . 'bp_bebop_oer_manager SET ' . $query );
		unset($update_secondary_item_id);
	}
	bebop_tables::add_option( 'bebop_db_update_3', true );
}

bebop_tables::add_option( 'bebop_db_version', '1.3.1' );
?>