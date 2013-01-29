<?php
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

global $db_version;

$db_version = "1.2";

// Installation process on creating tables for Rate This Page plugin.
if ( !function_exists( 'create_table' ) ) {
	function create_table() {
		global $wpdb, $db_version;
		
		$installed_ver = get_option( "aft_db_version" );
		
		$tbl_feedbacks = $wpdb->prefix . "feedbacks";
		$tbl_feedbacks_summary = $wpdb->prefix . "feedbacks_summary";
		
		if ( $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks'") != $tbl_feedbacks
	        ||  $installed_ver != $db_version ) {
			$sql = "CREATE TABLE ". $tbl_feedbacks ." (
					  id bigint(20) NOT NULL AUTO_INCREMENT,
					  post_id bigint(20) NOT NULL,
					  user_type tinyint(4) NOT NULL,
					  session_key varchar(50) NOT NULL,
					  trustworthy_rate decimal(3,1) NOT NULL,
					  objective_rate decimal(3,1) NOT NULL,
					  complete_rate decimal(3,1) NOT NULL,
					  wellwritten_rate decimal(3,1) NOT NULL,
					  is_highly_knowledgable varchar(10) NOT NULL,
					  is_relevant varchar(10) NOT NULL,
					  is_my_profession varchar(10) NOT NULL,
					  is_personal_passion varchar(10) NOT NULL,
					  rate_date datetime NOT NULL,
					  rate_modified datetime NOT NULL,
					  is_page VARCHAR(10) NOT NULL DEFAULT 'false',
					  ip VARCHAR(40) NOT NULL,
					  host VARCHAR(200) NOT NULL,
					  PRIMARY KEY (id)
					) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
					
			dbDelta($sql);
		}
		
		if ( $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks_summary'") != $tbl_feedbacks_summary
	        ||  $installed_ver != $db_version ) {
			$sql = "CREATE TABLE ". $tbl_feedbacks_summary ." (
					  id bigint(20) NOT NULL AUTO_INCREMENT,
					  post_id bigint(20) NOT NULL,
					  total_trustworthy int(10) NOT NULL DEFAULT '0',
					  count_trustworthy int(10) NOT NULL DEFAULT '0',
					  total_objective int(10) NOT NULL DEFAULT '0',
					  count_objective int(10) NOT NULL DEFAULT '0',
					  total_complete int(10) NOT NULL DEFAULT '0',
					  count_complete int(10) NOT NULL DEFAULT '0',
					  total_wellwritten int(10) NOT NULL DEFAULT '0',
					  count_wellwritten int(10) NOT NULL DEFAULT '0',
					  rate_average decimal(3,2) NOT NULL,
					  is_page VARCHAR(10) NOT NULL DEFAULT 'false',
					  PRIMARY KEY (id)
					) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
					
			dbDelta($sql);
		}
		
		if ( !get_option("aft_db_version") ) {
			add_option("aft_db_version", $db_version);
		} else {
			update_option( "aft_db_version", $db_version );
		}
	}
}

if ( !function_exists( 'remove_table' ) ) {
	function remove_table() {
		global $wpdb, $db_version;
		
		//$installed_version = get_option( 'aft_db_version' );
		
		$tbl_feedbacks = $wpdb->prefix . "feedbacks";
		$tbl_feedbacks_summary = $wpdb->prefix . "feedbacks_summary";
		
		if ( $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks'") == $tbl_feedbacks ) {
			$sql = "DROP TABLE ". $tbl_feedbacks ."";
			$wpdb->query($sql);
		}
		
		if ( $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks_summary'") == $tbl_feedbacks_summary ) {
			$sql = "DROP TABLE ". $tbl_feedbacks_summary ."";
			$wpdb->query($sql);
		}
	}
}

if ( !function_exists( 'remove_table' ) ) {
	function update_table() {
		global $wpdb, $db_version;
		
		$installed_ver = get_option( "aft_db_version" );
		
		$tbl_feedbacks = $wpdb->prefix . "feedbacks";
		$tbl_feedbacks_summary = $wpdb->prefix . "feedbacks_summary";
		
		if ( $db_version == "1.2" ) {
			$sql = "ALTER TABLE $tbl_feedbacks ADD ip VARCHAR(40) NOT NULL AFTER is_page ,
					ADD host VARCHAR(200) NOT NULL AFTER ip";
		}
		
		$wpdb->query($sql);
		
		if ( !get_option("aft_db_version") ) {
			add_option("aft_db_version", $db_version);
		} else {
			update_option( "aft_db_version", $db_version );
		}
	}
}
?>