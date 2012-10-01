<?php
class bebop_filters {
	//Increment the day counter
	public function day_increase( $extension, $user_id, $username ) {
		$maximport_value = bebop_tables::get_option_value( 'bebop_' . $extension . '_maximport' );
		$user_count = bebop_tables::get_user_meta_value( $user_id, 'bebop_' . $extension . '_' . $username . '_daycounter' );
		
		if ( ( ! empty( $user_count ) ) || ( is_numeric( $user_count ) ) ) {
			
			if ( ( empty( $maximport_value ) || $maximport_value === 0 ) || ( $maximport_value > $user_count ) ) {
				$new_count = $user_count + 1;
				if ( bebop_tables::update_user_meta( $user_id, $extension, 'bebop_' . $extension . '_' . $username . '_daycounter', $new_count ) ) {
					return true;
				}
				else {
					return false;
				}
			}
		}
		else {
			return false;
		}
	}
	
	//Check import limits
	public function import_limit_reached( $extension, $user_id, $username = null ) {
		//different day ot no day set, set the day and the counter to 0;
		if ( bebop_tables::get_user_meta_value( $user_id, 'bebop_' . $extension . '_' . $username . '_counterdate' ) != date( 'dmy' ) ) {
			bebop_tables::update_user_meta( $user_id, $extension, 'bebop_' . $extension . '_' . $username . '_daycounter', '0' );
			bebop_tables::update_user_meta( $user_id, $extension, 'bebop_' . $extension . '_' . $username . '_counterdate', date( 'dmy' ) );
		}
		
		//max items per day * < should return false*
		$maximport_value = bebop_tables::get_option_value( 'bebop_' . $extension . '_maximport' );
		
		if ( empty( $maximport_value ) || $maximport_value === 0 ) { //its empty. no value is set (unlimited)
			return false;
		}
		else if ( is_numeric( $maximport_value ) ) { //not empty but value is numeric
			if ( bebop_tables::get_user_meta_value( $user_id, 'bebop_' . $extension . '_' . $username . '_daycounter' ) < $maximport_value ) {
				return false;
			}
		}
		//otherwise limit must be have been met.
		return true;
	}
	
	function search_filter( $content, $filters = null, $returnOnFirst = false, $findAll = false, $returnDefault = false ){
		if ( ! $filters ) {
			return $returnDefault;
		}
		$content = strip_tags( $content );
		
		foreach ( explode( ',', $filters ) as $filterValue ) {
			if ( $filterValue ) {
				$filterValue = trim( $filterValue );
				$filterValue = str_replace( '/', '', $filterValue );
				
				if ( preg_match( '/' . $filterValue . '/', $content ) > 0 ) {
					if ( $returnOnFirst ) {
						return true;
					}
					
					if ( $findAll ) {
						$returnValue = true;
					}
				}
				else {
					if ( $findAll ) {
						$returnValue = false;
					}
				}
			}
		}
		return $returnValue;
	}
}