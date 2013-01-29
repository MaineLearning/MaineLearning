<?php
/**
 * Fetch ratings result
 *
 * @param string $post_id
 * @param string $session_key
 * @param string $is_page
 *
 * @return object
 */
if ( !function_exists( 'aft_plgn_fetch_ratings' ) ) {
	function aft_plgn_fetch_ratings( $post_id = null, $session_key = null, $is_page = null ) {
		global $wpdb;
		
		if ( !empty( $post_id ) && !empty( $session_key ) && !empty( $is_page ) ) {
				// Fetch data by session key and post id.
				// Additional where clause is if the fetched data is from a page.
				$sql = sprintf( "SELECT * FROM %sfeedbacks
								WHERE post_id = %s AND session_key = '%s' AND is_page = '%s'",
								$wpdb->prefix, $post_id, $session_key, $is_page );
				$aft_get_result = $wpdb->get_row( $sql );
		}
		
		return $aft_get_result;
	}
}

function rtp_fetch_ratings_byip( $post_id, $ip_address, $is_page ) {
	global $wpdb;
	
	if ( !empty( $post_id ) && !empty( $ip_address ) && !empty( $is_page ) ) {
			$sql = sprintf( "SELECT * FROM %sfeedbacks
							WHERE post_id = %s AND ip = '%s' AND is_page = '%s'",
							$wpdb->prefix, $post_id, $ip_address, $is_page );
			$aft_get_result = $wpdb->get_row( $sql );
	}
	
	return $wpdb->get_row( $sql );
}

/**
 * Save function for rated posts/pages
 *
 * @param bool $isnew
 * @param array $aft_data
 * @param string $post_id
 * @param string $session_key
 *
 * @return void
 */
if ( !function_exists( 'aft_plgn_save_ratings' ) ) {
	function aft_plgn_save_ratings( $isnew, $aft_data, $post_id = null, $session_key = null ) {
		global $wpdb;
		
		$pid = $aft_data['post_id'];
		$is_page = $aft_data['is_page'];
		
		if ( !empty( $aft_data ) ) {
			if ( !$isnew ) {
				// Apply values for where clause
				if ( !empty( $post_id ) && !empty( $session_key ) ) {
					$where = array(
						'post_id' => $post_id,
						'session_key' => $session_key,
						'is_page' => $is_page, // include is_page to avoid updating conflict.
					);
				}
				
				// Apply date and time for updated rating of posts/pages.
				$aft_data['rate_modified'] = current_time('mysql');
				
				if ( empty($aft_data['ip']) && empty($aft_data['host']) ) {
					$aft_data['ip'] = get_ipaddress();
					$aft_data['host'] = @gethostbyaddr(get_ipaddress());
				}
				
				// Before updating the data in wp_feedbacks store first old data's.
				$aft_old_data = aft_plgn_fetch_ratings( $post_id, $session_key, $is_page );
				
				$sql = sprintf( '%sfeedbacks', $wpdb->prefix );
				$wpdb->update( $sql, $aft_data, $where );
			} else {
				// Apply date and time for rated posts/pages.
				if ( isset( $aft_data['rate_date'] ) ) {
					$aft_data['rate_date'] = current_time('mysql');
				}
				
				$aft_data['ip'] = get_ipaddress();
				$aft_data['host'] = @gethostbyaddr(get_ipaddress());
				
				$sql = sprintf( '%sfeedbacks', $wpdb->prefix );
				$wpdb->insert( $sql, $aft_data );
			}
			
			aft_plgn_calculate_summary( $aft_data,
										aft_plgn_fetch_rate_summary( 
											( !empty( $post_id ) ) ? $post_id : $pid,
											$is_page
										),
										$isnew,
										$aft_old_data );
		}
	}
}

/**
 * Calculate the overall average of the rated posts and pages
 *
 * @param array $aft_data
 * @param array $aft_fetch_data
 * @param bool $isnew
 * @param array $aft_old_data
 *
 * @return void
 */
if ( !function_exists( 'aft_plgn_calculate_summary' ) ) {
	function aft_plgn_calculate_summary( $aft_data, $aft_fetch_data, $isnew,  $aft_old_data = null, $is_delete = false ) {
		global $wpdb;
		
		// Initialize value for declared variables.
		$post_id = $aft_data['post_id'];
		$total_trustworthy = $aft_data['trustworthy_rate'];
		$total_objective = $aft_data['objective_rate'];
		$total_complete = $aft_data['complete_rate'];
		$total_wellwritten = $aft_data['wellwritten_rate'];
		$is_page = $aft_data['is_page']; // Identify if the summary stored is a page rated summary.
		
		// Counting the # of user who rated the posts/pages based on each items.
		$count_trustworthy = rtp_count_rated_item( $post_id, 'trustworthy_rate' );
		$count_objective = rtp_count_rated_item( $post_id, 'objective_rate' );
		$count_complete = rtp_count_rated_item( $post_id, 'complete_rate' );
		$count_wellwritten = rtp_count_rated_item( $post_id, 'wellwritten_rate' );
		
		// Check if post_id already exist in wp_feedbacks_summary table.
		if ( $aft_fetch_data->post_id != 0 ) {
			if ( !$isnew ) {
				// Sum of total rate from users for Trustworthy Rating.
				if ( $total_trustworthy <= $aft_old_data->trustworthy_rate ) {
					$temp_total = intval( $aft_old_data->trustworthy_rate - $total_trustworthy );
					
					if ( $temp_total == 0 && $is_delete ) {
						$total_trustworthy = intval( $aft_fetch_data->total_trustworthy - $total_trustworthy );
					} else {
						$total_trustworthy = intval( $aft_fetch_data->total_trustworthy - $temp_total );
					}
				} else {
					$temp_total = intval( $total_trustworthy - $aft_old_data->trustworthy_rate );
					$total_trustworthy = intval( $aft_fetch_data->total_trustworthy + $temp_total );
				}
				
				// Sum of total rate from users for Objective Rating.
				if ( $total_objective <= $aft_old_data->objective_rate ) {
					$temp_total = intval( $aft_old_data->objective_rate - $total_objective );
					
					if ( $temp_total == 0 && $is_delete ) {
						$total_objective = intval( $aft_fetch_data->total_objective - $total_objective );
					} else {
						$total_objective = intval( $aft_fetch_data->total_objective - $temp_total );
					}
				} else {
					$temp_total = intval( $total_objective - $aft_old_data->objective_rate );
					$total_objective = intval( $aft_fetch_data->total_objective + $temp_total );
				}
				
				// Sum of total rate from users for Complete Rating.
				if ( $total_complete <= $aft_old_data->complete_rate ) {
					$temp_total = intval( $aft_old_data->complete_rate - $total_complete );
					
					if ( $temp_total == 0 && $is_delete ) {
						$total_complete = intval( $aft_fetch_data->total_complete - $total_complete );
					} else {
						$total_complete = intval( $aft_fetch_data->total_complete - $temp_total );
					}
				} else {
					$temp_total = intval( $total_complete - $aft_old_data->complete_rate );
					$total_complete = intval( $aft_fetch_data->total_complete + $temp_total );
				}
				
				// Sum of total rate from users for Well-written Rating.
				if ( $total_wellwritten <= $aft_old_data->wellwritten_rate ) {
					$temp_total = intval( $aft_old_data->wellwritten_rate - $total_wellwritten );
					
					if ( $temp_total == 0 && $is_delete ) {
						$total_wellwritten = intval( $aft_fetch_data->total_wellwritten - $total_wellwritten );
					} else {
						$total_wellwritten = intval( $aft_fetch_data->total_wellwritten - $temp_total );
					}
				} else {
					$temp_total = intval( $total_wellwritten - $aft_old_data->wellwritten_rate );
					$total_wellwritten = intval( $aft_fetch_data->total_wellwritten + $temp_total );
				}
			} else {
				$total_trustworthy = intval( $aft_fetch_data->total_trustworthy + $total_trustworthy );
				$total_objective = intval( $aft_fetch_data->total_objective + $total_objective );
				$total_complete = intval( $aft_fetch_data->total_complete + $total_complete );
				$total_wellwritten = intval( $aft_fetch_data->total_wellwritten + $total_wellwritten );
			}
		}
		
		// Place all supplied variable in an array.
		$aft_sdata = array(
			'post_id'			 	=> $post_id,
			'total_trustworthy' 	=> $total_trustworthy,
			'count_trustworthy' 	=> $count_trustworthy,
			'total_objective' 		=> $total_objective,
			'count_objective' 		=> $count_objective,
			'total_complete' 		=> $total_complete,
			'count_complete' 		=> $count_complete,
			'total_wellwritten' 	=> $total_wellwritten,
			'count_wellwritten' 	=> $count_wellwritten,
			'is_page'				=> $is_page,
		);
		
		// Compute overall average rating of the article that was rated.
		$total_votes_score = array( $total_trustworthy, $total_objective, $total_complete, $total_wellwritten );
		
		$total_votes_count = array( $count_trustworthy, $count_objective, $count_complete, $count_wellwritten );
		
		$sum_of_votes = array_sum( $total_votes_score );
		$sum_of_counts = array_sum( $total_votes_count );
		
		if ( $sum_of_votes == 0 && $sum_of_counts == 0 ) {
			$aft_sdata['rate_average'] = 0;
		} else {
			$aft_sdata['rate_average'] = $sum_of_votes / $sum_of_counts;
		}
		
		// Check if post_id not exist in wp_feedbacks_summary table.
		if ( $aft_fetch_data->post_id == 0 ) {
			$sql = sprintf( '%sfeedbacks_summary', $wpdb->prefix );
			$wpdb->insert( $sql, $aft_sdata );
		} else {
			// The summary of the page being rated.
			if ( $aft_fetch_data->is_page == 'true' ) {
				$sql = sprintf( '%sfeedbacks_summary', $wpdb->prefix );
				$wpdb->update( $sql, $aft_sdata, array( 'post_id' => $post_id, 'is_page' => $is_page ) );
			} else {
				$sql = sprintf( '%sfeedbacks_summary', $wpdb->prefix );
				$wpdb->update( $sql, $aft_sdata, array( 'post_id' => $post_id ) );
			}
		}
	}
}

/**
 * Fetch the article that was rated with its average and total average.
 *
 * @param string $filter
 *
 * @return object
 */
if ( !function_exists( 'aft_plgn_fetch_article_rate' ) ) {
	function aft_plgn_fetch_article_rate( $filter = null, $insertion = 'post' ) {
		global $wpdb;
		
		$_sql = "SELECT post_id, post_title,
				CAST((total_trustworthy / count_trustworthy) AS decimal(3,2)) AS trustworthy_rate,
				CAST((total_objective / count_objective) AS decimal(3,2)) AS objective_rate,
				CAST((total_complete / count_complete) AS decimal(3,2)) AS complete_rate,
				CAST((total_wellwritten / count_wellwritten) AS decimal(3,2)) AS wellwritten_rate, rate_average
				FROM %sfeedbacks_summary ";
		
		switch ( $filter ) {
			case "highest":
				$_sql .= "INNER JOIN $wpdb->posts ON $wpdb->posts.id = %sfeedbacks_summary.post_id
						WHERE rate_average >= 3.5";
				if ( $insertion == 'page' ) {
					$_sql .= " AND is_page = 'true'";
				} else {
					$_sql .= " AND is_page = 'false'";
				}
				$_sql .= " ORDER BY rate_average DESC";
				$sql = sprintf( $_sql, $wpdb->prefix, $wpdb->prefix, $wpdb->prefix );
				break;
			case "lowest":
				$_sql .= "INNER JOIN $wpdb->posts ON $wpdb->posts.id = %sfeedbacks_summary.post_id
						WHERE rate_average <= 3.4";
				if ( $insertion == 'page' ) {
					$_sql .= " AND is_page = 'true'";
				} else {
					$_sql .= " AND is_page = 'false'";
				}
				$_sql .= " ORDER BY rate_average DESC";
				$sql = sprintf( $_sql, $wpdb->prefix, $wpdb->prefix, $wpdb->prefix );
				break;
			case "last-update":
				//$date_begin = date('Y-m-d') . " 00:00:00";
				//$date_end = date('Y-m-d') . " 23:59:59";
				
				$_sql = "SELECT %sfeedbacks.id as fid, post_id, post_title, trustworthy_rate, 
						objective_rate, complete_rate, wellwritten_rate, rate_modified
						FROM %sfeedbacks
						INNER JOIN $wpdb->posts ON $wpdb->posts.id = %sfeedbacks.post_id";
				if ( $insertion == 'page' ) {
					$_sql .= " WHERE is_page = 'true'";
				} else {
					$_sql .= " WHERE is_page = 'false'";
				}
				$_sql .= " ORDER BY rate_modified DESC";
				$sql = sprintf( $_sql, $wpdb->prefix, $wpdb->prefix, $wpdb->prefix, $date_begin, $date_end );
				break;
			case "all":
			default:
				$_sql .= "INNER JOIN $wpdb->posts ON $wpdb->posts.id = %sfeedbacks_summary.post_id";
				if ( $insertion == 'page' ) {
					$_sql .= " WHERE is_page = 'true'";
				} else {
					$_sql .= " WHERE is_page = 'false'";
				}
				$_sql .= " ORDER BY rate_average DESC";
				$sql = sprintf( $_sql, $wpdb->prefix, $wpdb->prefix, $wpdb->prefix );
				break;
		}
		$aft_get_result = $wpdb->get_results( $sql );
		
		return $aft_get_result;
	}
}

/**
 * Fetch the summary of the rated post/article
 *
 * @param string $post_id
 *
 * @return object
 */
if ( !function_exists( 'aft_plgn_fetch_rate_summary' ) ) {
	function aft_plgn_fetch_rate_summary( $post_id, $is_page ) {
		global $wpdb;
		
		$sql = sprintf( "SELECT * FROM %sfeedbacks_summary WHERE post_id = %s AND is_page = '%s'",
						$wpdb->prefix, $post_id, $is_page );
		$aft_get_result = $wpdb->get_row( $sql );
		
		return $aft_get_result;
	}
}

/**
 * Fetch the if cookie already generated and exists inside the table to avoid duplication.
 *
 * @param string $cookie
 *
 * @return bool
 */
if ( !function_exists( 'rtp_cookie_is_match' ) ) {
	function rtp_cookie_is_match( $cookie ) {
		global $wpdb;
		
		$sql = sprintf( "SELECT session_key FROM %sfeedbacks
						WHERE user_type = 0 AND session_key = '%s'", $wpdb->prefix, $cookie );
		$sql_col = $wpdb->get_col( $sql );
		
		if ( $sql_col ) {
			$ret = true;
		} else {
			$ret = false;
		}
		
		return $ret;
	}
}

if ( !function_exists( 'rtp_count_rated_item' ) ) {
	function rtp_count_rated_item( $post_id, $field_name, $field_value = '' ) {
		global $wpdb;
		
		if ( !is_array( $field_name ) ) {
			$sql = "SELECT COUNT(%s) AS count
					FROM %sfeedbacks
					WHERE post_id = %d";
					
			if ( empty( $field_value ) ) {
				$sql .= " AND %s > 0";
				$data = $wpdb->get_row( sprintf( $sql, $field_name, $wpdb->prefix, $post_id, $field_name ) );
			}
			
			return $data->count;
		} else { // Instead of counting the rated item return the average.
			$sql = "SELECT COUNT(%s) AS count_trustworthy,
					COUNT(%s) AS count_objective,
					COUNT(%s) AS count_complete,
					COUNT(%s) AS count_wellwritten
					FROM %sfeedbacks
					WHERE post_id = %d";
					
			$data = $wpdb->get_row( sprintf(
							$sql,
							$field_name[0],
							$field_name[1],
							$field_name[2],
							$field_name[3],
							$wpdb->prefix,
							$post_id ) );
							
			$data_arr = array(	$data->count_trustworthy,
								$data->count_objective,
								$data->count_complete,
								$data->count_wellwritten );
								
			return array_sum( $data_arr ) / count( $data_arr );
		}
	}
}

if ( !function_exists( 'rtp_compute_average' ) ) {
	function rtp_compute_average( $val1, $val2 ) {
		if ( !$val2 ) {
			$val2 = 0;
			throw new Exception($val2);
		}
		else return $val1 / $val2;
	}
}

if ( !function_exists( 'rtp_can_rate' ) ) {
	function rtp_can_rate() {
		global $user_ID;
		
		$user_ID = intval($user_ID);
		$option = get_option( 'aft_options_array' );
		
		switch( intval( $option['rtp_can_rate'] ) ) {
			case 1: // registered users only can rate
				if ( $user_ID == 0 ) {
					return false;
				}
				return true;
				break;
			case 2: // guest users only can rate
				if ( $user_ID > 0 ) {
					return false;
				}
				return true;
				break;
			case 3: // both can rate
				return true;
				break;
			default:
				return false; // No one can rate.
				break;
		}
	}
}

if ( !function_exists( 'rtp_fetch_as_log' ) ) {
	function rtp_fetch_as_log( $filter, $keyword ) {
		global $wpdb;
		
		$tbl_feedbacks = sprintf( '%sfeedbacks', $wpdb->prefix );
		$tbl_posts = sprintf( '%sposts', $wpdb->prefix );
		
		$sql = "SELECT $tbl_feedbacks.id, post_id, post_title, user_type,
				session_key, trustworthy_rate, objective_rate, complete_rate,
				wellwritten_rate, rate_date, ip, host
				FROM $tbl_feedbacks
				INNER JOIN $tbl_posts ON $tbl_posts.id = $tbl_feedbacks.post_id";
		
		switch ( intval( $filter ) ) {
			case 1: // search by IP
				$sql .= " WHERE ip LIKE '%$keyword%'";
				break;
			case 2: // search by Host
				$sql .= " WHERE host LIKE '%$keyword%'";
				break;
			case 0: // search all
			default:
				break;
		}
			
		return $wpdb->get_results( $sql );
	}
}

if ( !function_exists( 'rtp_delete_logs' ) ) {
	function rtp_delete_logs( $filter, $keyword ) {
		global $wpdb;
		
		$ret = false;
		
		switch( $filter ) {
			case 1: {
				$data = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."feedbacks WHERE ip = '$keyword'" );
				
				foreach ( $data as $newdata ) {
					$old_data = aft_plgn_fetch_ratings( $newdata->post_id, $newdata->session_key, $newdata->is_page );
					$summary_data = aft_plgn_fetch_rate_summary( $newdata->post_id, $newdata->is_page );
					
					$sql = "DELETE FROM %sfeedbacks WHERE id = %d AND ip = '%s'";
					$result = $wpdb->query( sprintf( $sql, $wpdb->prefix, $newdata->id, $newdata->ip ) );
					
					$rtp_data = array(
						'post_id' 					=> $newdata->post_id,
						'user_type'					=> $newdata->user_type,
						'session_key'				=> $newdata->session_key,
						'trustworthy_rate' 			=> $newdata->trustworthy_rate,
						'objective_rate' 			=> $newdata->objective_rate,
						'complete_rate' 			=> $newdata->complete_rate,
						'wellwritten_rate' 			=> $newdata->wellwritten_rate,
						'is_page'					=> $newdata->is_page,
					);
					
					aft_plgn_calculate_summary( $rtp_data, $summary_data, false, $old_data, true );
				}
			}
			break;
			//case 2: $sql .= " WHERE cookie = '%s'"; break;
			case 3: {
				$data = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."feedbacks WHERE post_id = '$keyword'" );
				
				foreach( $data as $newdata ) {
					$old_data = aft_plgn_fetch_ratings( $newdata->post_id, $newdata->session_key, $newdata->is_page );
					$summary_data = aft_plgn_fetch_rate_summary( $newdata->post_id, $newdata->is_page );
					
					$sql = "DELETE FROM %sfeedbacks WHERE id = %d AND post_id = '%s'";
					$result = $wpdb->query( sprintf( $sql, $wpdb->prefix, $newdata->id, $keyword ) );
					
					$rtp_data = array(
						'post_id' 					=> $newdata->post_id,
						'user_type'					=> $newdata->user_type,
						'session_key'				=> $newdata->session_key,
						'trustworthy_rate' 			=> $newdata->trustworthy_rate,
						'objective_rate' 			=> $newdata->objective_rate,
						'complete_rate' 			=> $newdata->complete_rate,
						'wellwritten_rate' 			=> $newdata->wellwritten_rate,
						'is_page'					=> $newdata->is_page,
					);
					
					aft_plgn_calculate_summary( $rtp_data, $summary_data, false, $old_data, true );
				}
			}
			break;
			default: {
				$data = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."feedbacks WHERE id = '$keyword'" );
				
				foreach( $data as $newdata ) {
					$old_data = aft_plgn_fetch_ratings( $newdata->post_id, $newdata->session_key, $newdata->is_page );
					$summary_data = aft_plgn_fetch_rate_summary( $newdata->post_id, $newdata->is_page );
					
					$sql = "DELETE FROM %sfeedbacks WHERE id = %d";
					$result = $wpdb->query( sprintf( $sql, $wpdb->prefix, $keyword ) );
					
					$rtp_data = array(
						'post_id' 					=> $newdata->post_id,
						'user_type'					=> $newdata->user_type,
						'session_key'				=> $newdata->session_key,
						'trustworthy_rate' 			=> $newdata->trustworthy_rate,
						'objective_rate' 			=> $newdata->objective_rate,
						'complete_rate' 			=> $newdata->complete_rate,
						'wellwritten_rate' 			=> $newdata->wellwritten_rate,
						'is_page'					=> $newdata->is_page,
					);
					
					aft_plgn_calculate_summary( $rtp_data, $summary_data, false, $old_data, true );
				}
			}
			break;
		}
		
		if ( $result )
			$ret = true;
		else
			$ret = false;
		
		return $ret;
	}
}

/*-- Functions for Logging Method --*/
if ( !function_exists( 'rtp_is_rated' ) ) {
	function rtp_is_rated( $post_id ) {
		$rtp_options =  get_option( 'aft_options_array' );
		switch( intval( $rtp_options[ 'rtp_logged_by' ] ) ) {
			// Logged by IP
			case 1:
				return rtp_logged_ip( $post_id );
				break;
			// Logged by Cookies
			case 2:
				return rtp_logged_cookies( $post_id );
				break;
			// Logged by IP and Cookies
			case 3:
				$cookie = rtp_logged_cookies( $post_id );
				if ( $cookie > 0 ) {
					return true;
				} else {
					return rtp_logged_ip( $post_id );
				}
				break;
		}
		
		return false;
	}
}

if ( !function_exists( 'rtp_logged_ip' ) ) {
	function rtp_logged_ip( $post_id ) {
		global $wpdb;
		
		$sql = "SELECT ip FROM %sfeedbacks WHERE post_id = %d AND ip = %d";
		$sql = sprintf( $sql, $wpdb->prefix, $post_id, get_ipaddress() );
		
		return intval( $wpdb->get_var( $sql ) );
	}
}

if ( !function_exists( 'rtp_logged_cookies' ) ) {
	function rtp_logged_cookies( $post_id ) {
		if ( isset( $_COOKIE[ RTP_COOKIE_LOGNAME . $post_id ] ) ) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Get IP address
 *
 * Code from: WP-Postratings Plugin
 *
 * @return string
 */
if( !function_exists( 'get_ipaddress' ) ) {
	function get_ipaddress() {
		if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_address = $_SERVER["REMOTE_ADDR"];
		} else {
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		if(strpos($ip_address, ',') !== false) {
			$ip_address = explode(',', $ip_address);
			$ip_address = $ip_address[0];
		}
		return esc_attr($ip_address);
	}
}

if( !function_exists( 'rtp_shortcode' ) ) {
	function rtp_shortcode() {
		if ( is_single() || is_page() ) {
			return rtp_display_template();
		}
	}
}

if ( !function_exists( 'rtp_top_rated' ) ) {
	function rtp_top_rated( $top, $is_page ) {
		global $wpdb;
		
		$is_page = ( !$is_page ) ? 'false' : 'true';
		
		$sql = "SELECT * FROM %sfeedbacks_summary
				WHERE is_page = '%s' AND rate_average <> 0
				ORDER BY rate_average DESC limit 0, %d";
				
		$sql = sprintf( $sql, $wpdb->prefix, $is_page, $top );
		
		return $wpdb->get_results( $sql );
	}
}
?>