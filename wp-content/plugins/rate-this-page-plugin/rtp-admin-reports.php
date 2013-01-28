<?php
	global $aft_options_array;
	
	$options = $aft_options_array;
	
	$base_name = plugin_basename( 'rate-this-page-plugin/rtp-admin-reports.php' );
	$base_page = 'admin.php?page=' . $base_name;
	
	//Default Rating Labels and Questions
	$lbl_trustworthy = 'Trustworthy'; //default label for trustworthy
	$lbl_objective = 'Objective'; //default label for objective
	$lbl_complete = 'Complete'; //default label for complete
	$lbl_wellwritten = 'Well-written'; //default label for well written
	
	//Custom Rating Labels and Questions
	if ( $options['rtp_is_custom_label'] == 'true' ) {
		$rtp_custom_labels = $options['rtp_custom_labels'];

		$lbl_trustworthy = $rtp_custom_labels[0]; //custom label for trustworthy
		$lbl_objective = $rtp_custom_labels[1]; //custom label for objective
		$lbl_complete = $rtp_custom_labels[2]; //custom label for complete
		$lbl_wellwritten = $rtp_custom_labels[3]; //custom label for well written
	}
	
	if ( isset( $_REQUEST[ 'rtp-delete-log' ] ) ) {
		if ( rtp_delete_logs( $_POST['rtp-delete-by'], $_POST['rtp-log-value'] ) ) {
			$message = "Successfully deleted a record(s)";
			$class = "updated";
		} else {
			$message = "Failed to delete a record(s)";
			$class = "error";
		}
	}
	
	if ( isset( $_REQUEST['submit-filter'] ) ) {
		$rate_data = aft_plgn_fetch_article_rate( $_POST['aft-filter'], $_POST['aft-by-insertion'] );
	} else {
		$rate_data = aft_plgn_fetch_article_rate(); //Show All
	}
	
	if ( isset( $_REQUEST['submit-search'] ) ) {
		if ( empty( $_POST['search-by'] ) && $_POST[ 'rtp-search-by' ] != 0 ) {
			$message = "Cannot search logs if textbox is empty!";
			$class = "error";
			$data = null;
		} else {
			$data = rtp_fetch_as_log( $_POST['rtp-search-by'], $_POST['search-by'] );
		}
	} else {
		$data = rtp_fetch_as_log( $_POST['rtp-search-by'], $_POST['search-by'] );
	}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery(".tabs").tabs({
			cookie: { expires: 30 },
			fx: { opacity: 'toggle', duration: 'fast' }
		});
		
		/** Table Sorter and Pagination for Log Reports **/
		jQuery("#log-results")
		.tablesorter({ 
			sortList: [[0,1]],
			headers: { 1: { sorter: false } }
		})
		.tablesorterPager({
			container: jQuery("#rtp-pager-logs"),
			positionFixed: false,
			removeRows: false,
			output: '{page} / {totalPages}'
		});
		
		/** Table Sorter and Pagination for Average Reports **/
		jQuery("#report-result")
		.tablesorter({ 
			sortList: [[6,1]],
			headers: {
				2: { sorter: false },
				3: { sorter: false },
				4: { sorter: false },
				5: { sorter: false }
			}
		})
		.tablesorterPager({
			container: jQuery("#rpt-pager-report"),
			positionFixed: false,
			removeRows: false,
			output: '{page} / {totalPages}'
		});
		
		var rtpSearchBy = jQuery('#rtp-search-by');
		
		rtpSearchBy.change(function() {
			if ( rtpSearchBy.val() == 0 ) {
				jQuery('#search-by').hide(500);
			} else {
				jQuery('#search-by').show(500);
			}
		});
	});
</script>
<div class="wrap">
	<h2><?php echo $title; ?></h2>
	<br />
	<div id="message" class="<?php echo $class; ?>"
	<?php 
		if( ( !isset( $_REQUEST[ 'rtp-delete-log' ] ) ) &&
			( !isset( $_REQUEST[ 'submit-search' ] ) ) )
			echo "style=\"display:none\"";
	?>>
	<p><strong><?php _e( $message, RTP_PLUGIN_SNAME ); ?></strong></p></div>
	<div class="tabs">
		<ul class="rtp-tab-navigation">
			<li><a href="#rtp-log-report"><?php _e( 'Logs Report', RTP_PLUGIN_SNAME ); ?></a></li>
			<li><a href="#rtp-reporting"><?php _e( 'Average Ratings Report', RTP_PLUGIN_SNAME ); ?></a></li>
		</ul>
		<div id="rtp-log-report">
			<form method="post" action="<?php echo $base_page; ?>">
				<div class="rtp-label-top">
					<?php _e( 'Search By', RTP_PLUGIN_SNAME ); ?>:&nbsp;
					<select id="rtp-search-by" name="rtp-search-by" style="width: 80px;">
						<option value="0" <?php if ( $_POST[ 'rtp-search-by' ] == 0 ) echo 'selected=""' ?>><?php _e( 'All Logs', RTP_PLUGIN_SNAME ); ?></option>
						<option value="1" <?php if ( $_POST[ 'rtp-search-by' ] == 1 ) echo 'selected=""' ?>><?php _e( 'By IP', RTP_PLUGIN_SNAME ); ?></option>
						<option value="2" <?php if ( $_POST[ 'rtp-search-by' ] == 2 ) echo 'selected=""' ?>><?php _e( 'By Host', RTP_PLUGIN_SNAME ); ?></option>
					</select>
					&nbsp;&nbsp;&nbsp;
					<input id="search-by" type="text" name="search-by" value="" size="35" 
					<?php
						if ( $_POST[ 'rtp-search-by' ] == 1 || $_POST[ 'rtp-search-by' ] == 2 )
							echo 'style="display: inline-block;"';
						else
							echo 'style="display: none;"';
					?> />
					<input class="button-secondary" name="submit-search" type="submit" value="<?php _e( 'Search' ); ?>" />
				</div>
				<div style="clear: both;"></div>
				<table id="log-results" class="widefat tablesorter">
					<thead>
						<tr>
							<th width="5%"><?php _e( 'ID', RTP_PLUGIN_SNAME ); ?></th>
							<th width="8%"><?php _e( 'User Type', RTP_PLUGIN_SNAME ); ?></th>
							<th width="8%"><?php _e( 'Post ID', RTP_PLUGIN_SNAME ); ?></th>
							<th width="18%"><?php _e( 'Title', RTP_PLUGIN_SNAME ); ?></th>
							<th width="9%"><?php _e( $lbl_trustworthy, RTP_PLUGIN_SNAME ); ?></th>
							<th width="9%"><?php _e( $lbl_objective, RTP_PLUGIN_SNAME ); ?></th>
							<th width="9%"><?php _e( $lbl_complete, RTP_PLUGIN_SNAME ); ?></th>
							<th width="9%"><?php _e( $lbl_wellwritten, RTP_PLUGIN_SNAME ); ?></th>
							<th width="10%"><?php _e( 'Date Time', RTP_PLUGIN_SNAME ); ?></th>
							<th width="9%"><?php _e( 'IP / Host', RTP_PLUGIN_SNAME ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( $data ) : ?>
						<?php foreach ( $data as $rows ) : ?>
						<tr valign="top">
							<?php
								$log_id = $rows->id;
								$user_type = ( $rows->user_type == 1 ) ? __( 'User', RTP_PLUGIN_SNAME ) : __( 'Guest', RTP_PLUGIN_SNAME );
								$log_post_id = $rows->post_id;
								$log_post_title = $rows->post_title;
								$log_trustworthy = ( $rows->trustworthy_rate > 0 ) ? $rows->trustworthy_rate : '-';
								$log_objective = ( $rows->objective_rate > 0 ) ? $rows->objective_rate : '-';
								$log_complete = ( $rows->complete_rate > 0 ) ? $rows->complete_rate : '-';
								$log_wellwritten = ( $rows->wellwritten_rate > 0 ) ? $rows->wellwritten_rate : '-';
								$log_date = mysql2date(sprintf(__('%s @ %s', RTP_PLUGIN_SNAME), get_option('date_format'), get_option('time_format')), gmdate('Y-m-d H:i:s', strtotime($rows->rate_date)));;
								$log_host_ip = $rows->ip . ' / ' . $rows->host;
							?>
							<td><?php echo $log_id; ?></td>
							<td><?php echo $user_type; ?></td>
							<td><?php echo $log_post_id; ?></td>
							<td><?php echo $log_post_title; ?></td>
							<td><?php echo $log_trustworthy; ?></td>
							<td><?php echo $log_objective; ?></td>
							<td><?php echo $log_complete; ?></td>
							<td><?php echo $log_wellwritten; ?></td>
							<td><?php echo $log_date; ?></td>
							<td><?php echo $log_host_ip; ?></td>
						</tr>
						<?php endforeach; ?>
						<?php else : echo "<td colspan='10' align='center'>No Results Found</td>"; ?>
						<?php endif; ?>
					</tbody>
					<tfoot>
						<tr id="rtp-pager-logs" style="text-align: center;">
							<td colspan="10">
								<img class="first" title="Go To First" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop-180.png" />
								<img class="prev" title="Previous" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-180.png" />
								<input class="pagedisplay" type="text" readonly="true" />
								<img class="next" title="Next" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow.png" />
								<img class="last" title="Go To Last" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop.png" />
								<input type="hidden" class="pagesize" value="10">
							</td>
						</tr>
					</tfoot>
				</table>
			</form>
			<div id="rtp-log-deletion">
				<h3><?php _e( 'Delete Logs Data' ); ?></h3>
				<form method="post" action="<?php echo $base_page; ?>">
					<table class="widefat">
						<tr>
							<td><strong><?php _e( 'Delete By:' ); ?></strong></td>
							<td>
								<select name="rtp-delete-by" style="width: 95px;">
									<option value="0"><?php _e( 'Log ID' ); ?></option>
									<option value="1"><?php _e( 'IP Address' ); ?></option>
									<!-- TODO: NEED MORE DETAILS IF THIS IS NECESSARY -->
									<!-- <option value="2">Cookies</option> -->
									<option value="3"><?php _e( 'Post ID' ); ?></option>
								</select>
								<input type="text" name="rtp-log-value" value="" size="25" />
								<input class="button-secondary" name="rtp-delete-log" type="submit" value="Delete Log" />
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
		
		<div id="rtp-reporting">
			<form method="post" action="<?php echo $base_page; ?>">
				<div class="rtp-label-top">
					<?php _e( 'Filter By', RTP_PLUGIN_SNAME ); ?>:&nbsp;
					<select name="aft-by-insertion" style="width: 80px;">
						<option <?php if ( $_POST['aft-by-insertion'] == 'post' ) echo 'selected="selected"'; ?> value="post"><?php _e( 'All Post', RTP_PLUGIN_SNAME ); ?></option>
						<option <?php if ( $_POST['aft-by-insertion'] == 'page' ) echo 'selected="selected"'; ?> value="page"><?php _e( 'All Page', RTP_PLUGIN_SNAME ); ?></option>
					</select>
					&nbsp;-&nbsp;
					<select name="aft-filter" style="width: 120px;">
						<option <?php if ( $_POST['aft-filter'] == 'all' ) echo 'selected="selected"'; ?> value="all"><?php _e( 'All Rated', RTP_PLUGIN_SNAME ); ?></option>
						<option <?php if ( $_POST['aft-filter'] == 'highest' ) echo 'selected="selected"'; ?> value="highest"><?php _e( 'Highest Rated', RTP_PLUGIN_SNAME ); ?></option>
						<option <?php if ( $_POST['aft-filter'] == 'lowest' ) echo 'selected="selected"'; ?> value="lowest"><?php _e( 'Lowest Rated', RTP_PLUGIN_SNAME ); ?></option>
						<!--<option <?php //if ( $_POST['aft-filter'] == 'last-update' ) echo 'selected="selected"'; ?> value="last-update"><?php //_e( 'Last Updated', RTP_PLUGIN_SNAME ); ?></option>-->						
					</select>
					<input class="button-secondary" name="submit-filter" type="submit" value="<?php _e( 'Filter' ); ?>" />
				</div>
				<div style="clear: both;"></div>
				<table id="report-result" class="widefat tablesorter">
					<thead>
						<tr>
							<th><?php _e( 'ID', RTP_PLUGIN_SNAME ); ?></th>
							<th><?php _e( 'Title', RTP_PLUGIN_SNAME ); ?></th>
							<th><?php _e( $lbl_trustworthy, RTP_PLUGIN_SNAME ); ?></th>
							<th><?php _e( $lbl_objective, RTP_PLUGIN_SNAME ); ?></th>
							<th><?php _e( $lbl_complete, RTP_PLUGIN_SNAME ); ?></th>
							<th><?php _e( $lbl_wellwritten, RTP_PLUGIN_SNAME ); ?></th>
							<?php if ( $_POST['aft-filter'] != 'last-update' ) : ?>
							<th><?php _e( 'Ratings', RTP_PLUGIN_SNAME ); ?></th>
							<?php else : ?>
							<th><?php _e( 'Date Modified', RTP_PLUGIN_SNAME ); ?></th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
						<?php if ( $rate_data ) : ?>
						<?php foreach ( $rate_data as $rows ) : ?>
						<tr valign="top">
							<td><?php echo $rows->post_id; ?></td>
							<td class="rtp-rpt-name">
								<div class="rtp-title">
									<a class="rtp-post-title" href="<?php echo admin_url(); ?>post.php?post=<?php echo $rows->post_id ?>&action=edit"><?php echo $rows->post_title; ?></a>
								</div>
							</td>
							<td class="rtp-rpt-rate">
								[<strong><?php echo max('0.00', $rows->trustworthy_rate); ?></strong>] Rate / 
								<br />[<strong><?php echo rtp_count_rated_item($rows->post_id, 'trustworthy_rate' ); ?></strong>] Count
							</td>
							<td class="rtp-rpt-rate">
								[<strong><?php echo max('0.00', $rows->objective_rate); ?></strong>] Rate / 
								<br />[<strong><?php echo rtp_count_rated_item($rows->post_id, 'objective_rate' ); ?></strong>] Count
							</td>
							<td class="rtp-rpt-rate">
								[<strong><?php echo max('0.00', $rows->complete_rate); ?></strong>] Rate / 
								<br />[<strong><?php echo rtp_count_rated_item($rows->post_id, 'complete_rate' ); ?></strong>] Count
							</td>
							<td class="rtp-rpt-rate">
								[<strong><?php echo max('0.00', $rows->wellwritten_rate); ?></strong>] Rate / 
								<br />[<strong><?php echo rtp_count_rated_item($rows->post_id, 'wellwritten_rate' ); ?></strong>] Count
							</td>
							<?php if ( $_POST['aft-filter'] != 'last-update' ) : ?>
							<td class="rtp-rpt-average">
								[<strong><?php echo $rows->rate_average; ?></strong>] Ave. / 
								<br />[<strong><?php echo rtp_count_rated_item($rows->post_id, array('trustworthy_rate', 'objective_rate', 'complete_rate', 'wellwritten_rate') ); ?></strong>] Count
							</td>
							<?php else : ?>
							<td><?php echo $rows->rate_modified; ?></td>
							<?php endif; ?>
						</tr>
						<?php endforeach; ?>
						<?php else : ?><td colspan="7" align="center"><?php _e( 'No Records Found' ); ?></td>
						<?php endif; ?>
					</tbody>
					<tfoot>
						<tr id="rpt-pager-report" style="text-align: center;">
							<td colspan="7">
								<img class="first" title="Go To First" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop-180.png" />
								<img class="prev" title="Previous" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-180.png" />
								<input class="pagedisplay" type="text" readonly="true" />
								<img class="next" title="Next" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow.png" />
								<img class="last" title="Go To Last" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop.png" />
								<input type="hidden" class="pagesize" value="10">
							</td>
						</tr>
					</tfoot>
				</table>
			</form>
		</div>
	</div>
</div>