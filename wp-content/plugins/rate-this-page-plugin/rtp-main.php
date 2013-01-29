<?php
/**
 * Saving process using ajax method
 *
 * @return void
 */
if ( !function_exists( 'rtp_process_save' ) ) {
	function rtp_process_save() {
		global $user_identity;
		
		$rtp_options = get_option( 'aft_options_array' );
		
		$user_type = ( !empty($user_identity) ) ? 1 : 0;
		
		$post_id = $_POST['post_id'];
		$session_key = $_COOKIE[ RTP_COOKIE_NAME ];
		
		if ( $post_id > 0 && !empty( $session_key ) ) {
			if ( !rtp_is_rated( $post_id ) ) {
				$isnew = ( empty( $_POST['rate_date'] ) ) ? true : false;
				
				$trustworthy_rate = intval($_POST['trustworthy_rate']);
				$objective_rate = intval($_POST['objective_rate']);
				$complete_rate = intval($_POST['complete_rate']);
				$wellwritten_rate = intval($_POST['wellwritten_rate']);
				
				// If null supply 0 value.
				if ( $trustworthy_rate == null ) { $trustworthy_rate = 0; }
				if ( $objective_rate == null ) { $objective_rate = 0; }
				if ( $complete_rate == null ) { $complete_rate = 0; }
				if ( $wellwritten_rate == null ) { $wellwritten_rate = 0; }
				
				$rtp_data = array(
					'post_id' 					=> $post_id,
					'user_type'					=> $user_type,
					'session_key'				=> $session_key,
					'trustworthy_rate' 			=> $trustworthy_rate,
					'objective_rate' 			=> $objective_rate,
					'complete_rate' 			=> $complete_rate,
					'wellwritten_rate' 			=> $wellwritten_rate,
					'is_highly_knowledgable' 	=> $_POST['is_highly_knowledgable'],
					'is_relevant' 				=> $_POST['is_relevant'],
					'is_my_profession' 			=> $_POST['is_my_profession'],
					'is_personal_passion' 		=> $_POST['is_personal_passion'],
					'rate_date'					=> $_POST['rate_date'],
					'is_page'					=> $_POST['is_page'],
				);
				
				// Create cookie if logging method set to Cookie Only or IP and Cookie
				if ( $rtp_options[ 'rtp_logged_by' ] == 2 || $rtp_options[ 'rtp_logged_by' ] == 3 ) {
					setcookie( RTP_COOKIE_LOGNAME . $post_id, $_COOKIE[ RTP_COOKIE_NAME ], RTP_COOKIE_EXPIRATION2, SITECOOKIEPATH, COOKIE_DOMAIN );
				}
				
				aft_plgn_save_ratings( $isnew, $rtp_data, $post_id, $session_key );
				die();
			}
		}
	}
}

/**
 * Display the plugin in a content
 *
 * @param content - The content of the site.
 *
 * @return content
 */
if ( !function_exists( 'rtp_display_to_content' ) ) {
	function rtp_display_to_content( $content ) {
		$rtp_options = get_option( 'aft_options_array' );
		$rtp_options_by = get_option( 'aft_options_by_array' );
		
		$is_enable = $rtp_options['aft_is_enable'];
		$location = $rtp_options['aft_location'];
		$insertion = $rtp_options['aft_insertion'];
		$use_shortcode = intval( $rtp_options['rtp_use_shortcode'] );
		
		$options_category = $rtp_options_by['category'];
		$options_page = $rtp_options_by['page'];
		
		// This part will display the plugin together with the content.
		if ( $use_shortcode == 0 )
			$display = rtp_display_template();
		else
			add_shortcode( 'rate_this_page', 'rtp_shortcode' ); // Register shortcode.
		
		$display_content = $content;
		
		if ( $is_enable == 'true' && rtp_can_rate() ) {
			$cat_opt_id = array();
			$page_opt_id = array();
			
			if ( $insertion == 'by-category' ) { // Show plugin by category
				foreach( $options_category as $category ) {
					if ( $category > 0 ) {
						$cat_opt_id[] = intval($category);
					}
				}
			
				if ( is_single() && in_category( $cat_opt_id ) ) {
					if ( $location == 'bottom' ) {
						$display_content = $content . $display; // Below the content
					} else {
						$display_content = $display . $content; // Above the content
					}
				}
			} else if ( $insertion == 'by-page' ) { // Show plugin by page
				foreach( $options_page as $page ) {
					if ( $page > 0 ) {
						$page_opt_id[] = $page;
					}
				}
				
				// To avoid displaying the plugin in all page if page_opt_id is null.
				if( count($page_opt_id) == 0 ) $page_opt_id = 'no data';
				
				if ( is_page( $page_opt_id ) ) {
					if ( $location == 'bottom' ) {
						$display_content = $content . $display; // Below the content
					} else {
						$display_content = $display . $content; // Above the content
					}
				}
			} else if ( $insertion == 'page-category' ) { // Show plugin by page and category
				foreach( $options_category as $category ) {
					if ( $category > 0 ) {
						$cat_opt_id[] = intval($category);
					}
				}
				
				foreach( $options_page as $page ) {
					if ( $page > 0 ) {
						$page_opt_id[] = $page;
					}
				}
				
				// To avoid displaying the plugin in all page if page_opt_id is null.
				if( count($page_opt_id) == 0 ) $page_opt_id = 'no data';
				
				if ( (is_single() && in_category( $cat_opt_id )) || is_page( $page_opt_id ) ) {
					if ( $location == 'bottom' ) {
						$display_content = $content . $display; // Below the content
					} else {
						$display_content = $display . $content; // Above the content
					}
				}
			} else { // Show in all article
				if ( is_single() ) {
					if ( $location == 'bottom' ) {
						$display_content = $content . $display; // Below the content
					} else {
						$display_content = $display . $content; // Above the content
					}
				}
			}
		}
		
		return $display_content;
	}
}

if ( !function_exists( 'rtp_display_template' ) ) {
	function rtp_display_template( $post_id = 0 ) {
		global $id;
		
		$rtp_options = get_option( 'aft_options_array' );
		
		$session_key = $_COOKIE[ RTP_COOKIE_NAME ];
		
		if ( intval($post_id) > 0 ) {
			$postid = $post_id;
		} else {
			$postid = $id;
		}
		
		$is_rated = rtp_is_rated( $postid );		
		$is_page = ( 'post' === get_post_type() ) ? 'false' : 'true';
		
		// Show the current rating data if logged by IP or by IP and Cookie
		if ( $rtp_options['rtp_logged_by'] == 1 || $rtp_options['rtp_logged_by'] == 3 )
			$data = rtp_fetch_ratings_byip( $postid, get_ipaddress(), $is_page );
		else
			$data = aft_plgn_fetch_ratings( $postid, $session_key, $is_page );
		
		$ave_data = aft_plgn_fetch_rate_summary( $postid, $is_page );
		
		$display = '<!--Main Content-->
			<div class="rtp-feedback">
				<div class="rtp-feedback-panel">
					<div class="rtp-feedback-buffer">
						<div id="rtp-report" class="rtp-switch rtp-switch-form">View ratings</div>
						<div id="rtp-form" class="rtp-switch rtp-switch-report">Rate this article</div>
						<div class="feedback-title rtp-switch-form">%TITLE_LABEL%</div>
						<div class="feedback-title rtp-switch-report" id="rtp-report-title">%TITLE_LABEL_REPORT%<br /><span>Current average ratings.</span></div>
						<div style="clear:both;"></div>
						<!--Primary Feedback-->
						<div class="feedback-rating">									
							<div class="feedback-ratings">
								<div class="feedback-label">%LABEL_TRUSTWORTHY%</div>
								<span class="rtp-average-report rtp-switch-report">%AVE_TRUSTWORTHY%</span>
								<div id="rtp-trustworthy-bar" class="rtp-switch-report"></div>
								<div class="rtp-switch-form" id="feedback-trustworthy">
									<input type="hidden" id="rtp-trustworthy-val" name="trustworthy-score" value="%VAL_TRUSTWORTHY%" />
								</div>
								<div class="rtp-rating-count rtp-switch-report">%COUNT_TRUSTWORTHY%</div>
								<div class="rtp-trustworthy-hint"></div>
							</div>
							<div class="feedback-ratings">
								<div class="feedback-label">%LABEL_OBJECTIVE%</div>
								<span class="rtp-average-report rtp-switch-report">%AVE_OBJECTIVE%</span>
								<div id="rtp-objective-bar" class="rtp-switch-report"></div>
								<div class="rtp-switch-form" id="feedback-objective">
									<input type="hidden" id="rtp-objective-val" name="objective-score" value="%VAL_OBJECTIVE%" />
								</div>
								<div class="rtp-rating-count rtp-switch-report">%COUNT_OBJECTIVE%</div>
								<div class="rtp-objective-hint"></div>
							</div>
							<div class="feedback-ratings">
								<div class="feedback-label">%LABEL_COMPLETE%</div>
								<span class="rtp-average-report rtp-switch-report">%AVE_COMPLETE%</span>
								<div id="rtp-complete-bar" class="rtp-switch-report"></div>
								<div class="rtp-switch-form" id="feedback-complete">
									<input type="hidden" id="rtp-complete-val" name="complete-score" value="%VAL_COMPLETE%" />
								</div>
								<div class="rtp-rating-count rtp-switch-report">%COUNT_COMPLETE%</div>
								<div class="rtp-complete-hint"></div>
							</div>
							<div class="feedback-ratings">
								<div class="feedback-label">%LABEL_WELLWRITTEN%</div>
								<span class="rtp-average-report rtp-switch-report">%AVE_WELLWRITTEN%</span>
								<div id="rtp-wellwritten-bar" class="rtp-switch-report"></div>
								<div class="rtp-switch-form" id="feedback-wellwritten">
									<input type="hidden" id="rtp-wellwritten-val" name="wellwritten-score" value="%VAL_WELLWRITTEN%" />
								</div>
								<div class="rtp-rating-count rtp-switch-report">%COUNT_WELLWRITTEN%</div>
								<div class="rtp-wellwritten-hint"></div>
							</div>
						</div>
					<div style="clear:both;"></div>
					<!--For Feedback Options-->
					<div class="feedback-options rtp-switch-form">
						<div class="feedback-options-selection">
							<input id="feedback-options-general" type="checkbox" name="feedback-general" value="" />
							<label class="feedback-label" for="feedback-options-general">'. __('I am highly knowledgeable about this topic (optional)').'</label>
						</div>
						<div class="feeback-helpimprove-hidden" style="display:none;">
							<ul id="feedback-helpimprove">
								<li><input id="feedbackStudies" type="checkbox" name="feedback-studies" value="" /><label class="feedback-label" for="feedbackStudies">'.__('I have a relevant college/university degree').'</label></li>
								<li><input id="feedbackProfession" type="checkbox" name="feedback-profession" value="" /><label class="feedback-label" for="feedbackProfession">'.__('It is part of my profession').'</label></li>
								<li><input id="feedbackPassion" type="checkbox" name="feedback-passion" value="" /><label class="feedback-label" for="feedbackPassion">'.__('It is a deep personal passion').'</label></li>
							</ul>
						</div>
					</div>
					<button class="rtp-button rtp-switch-form" id="submit-feedback" type="submit" name="aft-submit-feedback" value="aft-submit-feedback"><span id="rtp-button-label">'. __( 'Submit Ratings' ) . '</span></button>
					<div id="formstatus" class="rtp-switch-form"></div>
					</div>
				</div>
				<input type="hidden" id="feedback-postid" name="feedback-postid" value="%POST_ID%" />
				<input type="hidden" id="feedback-datetime" name="feedback-datetime" value="%RATE_DATE%" />
				<input type="hidden" id="feedback-ispage" name="feedback-ispage" value="%IS_PAGE%" />
			</div>';
		
		return rtp_extend_template( $display, $postid, $data, $ave_data );
	}
}

if ( !function_exists( 'rtp_extend_template' ) ) {
	function rtp_extend_template( $template, $postid, $data, $ave_data ) {
		$display = $template;
		
		$rtp_options = get_option( 'aft_options_array' );
		$rtp_options_by = get_option( 'aft_options_by_array' );
		
		$is_custom_label = $rtp_options['rtp_is_custom_label'];
		
		$is_page = ( 'post' === get_post_type() ) ? 'false' : 'true';
		
		//Default Rating Labels and Questions
		$lbl_trustworthy = 'Trustworthy'; //default label for trustworthy
		$lbl_objective = 'Objective'; //default label for objective
		$lbl_complete = 'Complete'; //default label for complete
		$lbl_wellwritten = 'Well-written'; //default label for well written
		
		//Custom Rating Labels and Questions
		if ( $is_custom_label == 'true' ) {
			$rtp_custom_labels = $rtp_options['rtp_custom_labels'];
			
			$lbl_trustworthy = $rtp_custom_labels[0]; //custom label for trustworthy
			$lbl_objective = $rtp_custom_labels[1]; //custom label for objective
			$lbl_complete = $rtp_custom_labels[2]; //custom label for complete
			$lbl_wellwritten = $rtp_custom_labels[3]; //custom label for well written
		}
		
		try {
			$ave_trustworthy = rtp_compute_average( $ave_data->total_trustworthy, $ave_data->count_trustworthy );
		} catch ( Exception $e ) {
			$ave_trustworthy = $e->getMessage();
		}
		$display = str_replace( "%AVE_TRUSTWORTHY%", number_format( $ave_trustworthy, 1 ), $display );
		
		try {
			$ave_objective = rtp_compute_average( $ave_data->total_objective, $ave_data->count_objective );
		} catch ( Exception $e ) {
			$ave_objective = $e->getMessage();
		}
		$display = str_replace( "%AVE_OBJECTIVE%", number_format( $ave_objective, 1 ), $display );
		
		try {
			$ave_complete = rtp_compute_average( $ave_data->total_complete, $ave_data->count_complete );
		} catch ( Exception $e ) {
			$ave_complete = $e->getMessage();
		}
		$display = str_replace( "%AVE_COMPLETE%", number_format( $ave_complete, 1 ), $display );
		
		try {
			$ave_wellwritten = rtp_compute_average( $ave_data->total_wellwritten, $ave_data->count_wellwritten );
		} catch ( Exception $e ) {
			$ave_wellwritten = $e->getMessage();
		}
		$display = str_replace( "%AVE_WELLWRITTEN%", number_format( $ave_wellwritten, 1 ), $display );
		
		if ( is_page() ) {
			$lbl_title = __('Rate this page');
			$lbl_title_rpt = __('Page ratings');
		} else {
			$lbl_title = __('Rate this article');
			$lbl_title_rpt = __('Article ratings');
		}
		
		$display = str_replace( "%TITLE_LABEL%", $lbl_title , $display );
		$display = str_replace( "%TITLE_LABEL_REPORT%", $lbl_title_rpt, $display );
		
		$display = str_replace( "%LABEL_TRUSTWORTHY%", $lbl_trustworthy, $display );
		$display = str_replace( "%LABEL_OBJECTIVE%", $lbl_objective, $display );
		$display = str_replace( "%LABEL_COMPLETE%", $lbl_complete, $display );
		$display = str_replace( "%LABEL_WELLWRITTEN%", $lbl_wellwritten, $display );
		
		$display = str_replace( "%VAL_TRUSTWORTHY%", $data->trustworthy_rate, $display );
		$display = str_replace( "%VAL_OBJECTIVE%", $data->objective_rate, $display );
		$display = str_replace( "%VAL_COMPLETE%", $data->complete_rate, $display );
		$display = str_replace( "%VAL_WELLWRITTEN%", $data->wellwritten_rate, $display );
		
		$display = str_replace( "%COUNT_TRUSTWORTHY%", $ave_data->count_trustworthy . _n(' rating', ' ratings', $ave_data->count_trustworthy ), $display );
		$display = str_replace( "%COUNT_OBJECTIVE%", $ave_data->count_objective . _n(' rating', ' ratings', $ave_data->count_objective ), $display );
		$display = str_replace( "%COUNT_COMPLETE%", $ave_data->count_complete . _n(' rating', ' ratings', $ave_data->count_complete ), $display );
		$display = str_replace( "%COUNT_WELLWRITTEN%", $ave_data->count_wellwritten . _n(' rating', ' ratings', $ave_data->count_wellwritten ), $display );
		
		$display = str_replace( "%POST_ID%", $postid, $display );
		$display = str_replace( "%RATE_DATE%", $data->rate_date, $display );
		$display = str_replace( "%IS_PAGE%", $is_page, $display );
		
		return apply_filters( 'rtp_extend_template', htmlspecialchars_decode($display) );
	}
}
?>