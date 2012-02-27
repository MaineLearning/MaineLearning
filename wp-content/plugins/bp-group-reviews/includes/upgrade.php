<?php

if ( !class_exists( 'BP_Group_Reviews_Upgrade' ) ) :

class BP_Group_Reviews_Upgrade {
	function bp_group_reviews_upgrade() {
		$this->__construct();
	}
	
	function __construct() {
		$this->current_version = get_option( 'bp_group_reviews_version' );
		$this->new_version = BP_GROUP_REVIEWS_VERSION;
		
		// No version number existed before version 1.02
		if ( empty( $this->current_version ) ) {
			$this->upgrade_1_02();
		}
	
		update_option( 'bp_group_reviews_version', $this->new_version );
	}
	
	function upgrade_1_02() {
		global $bp, $wpdb;
		
		$sql = $wpdb->prepare( "SELECT group_id, meta_value FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'bpgr_rating'" );
		
		$old_ratings = $wpdb->get_results( $sql );
		
		foreach( $old_ratings as $old_rating ) {
			$group_id = $old_rating->group_id;
			$rating = maybe_unserialize( $old_rating->meta_value );
			
			if ( !empty( $rating['avg_score'] ) ) {
				groups_update_groupmeta( $group_id, 'bpgr_rating', $rating['avg_score'] ); 
			}
			
			if ( !empty( $rating['number'] ) ) {
				groups_update_groupmeta( $group_id, 'bpgr_how_many_ratings', $rating['number'] ); 
			}
		}
		
	}
}

endif;

$bpgr_upgrade = new BP_Group_Reviews_Upgrade;

?>