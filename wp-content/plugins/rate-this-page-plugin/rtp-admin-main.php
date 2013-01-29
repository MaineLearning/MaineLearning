<?php
	global $wpdb, $title, $aft_options_array, $aft_options_by_array;
	
	$theme_lists = get_option('aft_themes_array');
	$options = $aft_options_array;
	$options_by = $aft_options_by_array;
	
	$base_name = plugin_basename('rate-this-page-plugin/rtp-admin-main.php');
	$base_page = 'admin.php?page=' . $base_name;
	$message = "";
	
	$tbl_feedbacks = $wpdb->prefix . "feedbacks";
	$tbl_feedbacks_summary = $wpdb->prefix . "feedbacks_summary";
	
	$cat_id = get_all_category_ids();
	$page_id = get_all_page_ids();
	
	$count_c = count( $cat_id );
	$count_p = count( $page_id );
	
	//Accept the changes for the plugin options
	if ( isset( $_REQUEST['aft_plgn_submit_options'] ) ) {	
		$options['aft_is_enable'] 		= $_REQUEST['aft_is_enable'];
		$options['aft_location'] 		= $_REQUEST['aft_location'];
		$options['aft_insertion'] 		= $_REQUEST['rtp-insertion'];
		$options['rtp_theme'] 			= $_REQUEST['rtp-theme-select'];
		$options['rtp_can_rate'] 		= $_REQUEST['rtp-can-rate'];
		$options['rtp_logged_by']		= $_REQUEST['rtp-logged-by'];
		$options['rtp_use_shortcode']	= $_REQUEST['rtp-use-shortcode'];
		
		update_option( 'aft_options_array', $options );
		$message = "Option Saved!";
	}
	
	// Process custom label configuration of the plugin.
	if ( isset( $_REQUEST['rtp_submit_custom'] ) ) {
		$rtp_labels = array( $_REQUEST['trustworthy-custom'],
							 $_REQUEST['objective-custom'],
							 $_REQUEST['complete-custom'],
							 $_REQUEST['well-written-custom']
							);
		
		$options['rtp_is_custom_label'] = $_REQUEST['rtp-is-custom-label'];
		$options['rtp_custom_labels'] 	= $rtp_labels;
		
		if ( $_REQUEST['rtp-is-custom-label'] == 'true' ) {
			$options['rtp_custom_hints'] = ( $_REQUEST['rtp-custom-hint'] == 0 || $_REQUEST['rtp-custom-hint'] == '' ) ? 1 : $_REQUEST['rtp-custom-hint'];
		} else {
			$options['rtp_custom_hints'] = 0; //Custom hints disabled
		}
		
		update_option( 'aft_options_array', $options );
		$message = "Option Saved!";
	}
	
	// Process insertion options of the plugin.
	if ( isset( $_REQUEST['submit-insertion'] ) ) {
		//$cat_id = get_all_category_ids();
		//$page_id = get_all_page_ids();

		$options_category = array();
		$options_page = array();

		// by category
		if ( $cat_id ) {
			$i = 0;
			foreach( $cat_id as $id ) {
				$options_category[get_cat_name($id)] = ( isset( $_REQUEST['aft-category-check-'.$i.''] ) ) ? $_REQUEST['aft-category-check-'.$i.''] : 0;
				$i++;
			}
		}
		
		// by page
		if ( $page_id ) {
			$i = 0;
			foreach( $page_id as $id ) {
				$page_data = get_page( $id );
				$options_page[$page_data->post_title] = ( isset( $_REQUEST['aft-page-check-'.$i.''] ) ) ? $_REQUEST['aft-page-check-'.$i.''] : 0;
				$i++;
			}
		}
		
		$options_by['category'] = $options_category;
		$options_by['page'] = $options_page;
		
		update_option( 'aft_options_by_array', $options_by );
		$message = "Insertion Options Saved!";
	}
	
	// Process table installation of the plugin.
	if ( isset( $_REQUEST['rtp-install-table'] ) ) {
		create_table();
		$message = "Successfully Created Tables!";
	}
	
	// Process table uninstallation of the plugin.
	if ( isset( $_REQUEST['rtp-uninstall-table'] ) ) {
		remove_table();
		$message = "Successfully Drop Tables!";
	}
	
	if ( $options['aft_insertion'] == 'by-page' ) {
		$lbl_insertion = 'By Page Insertion';
	} else if ( $options['aft_insertion'] == 'by-category' ) {
		$lbl_insertion = 'By Category Insertion';
	} else if ( $options['aft_insertion'] == 'page-category' ) {
		$lbl_insertion = 'Insertion For Both';
	}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		/** Initialize jQuery Tabs **/
		jQuery(".tabs").tabs({
			cookie: { expires: 30 },
			fx: { opacity: 'toggle', duration: 'fast' }
		});
		
		/** Pagination by Category **/
		jQuery("#insertion-by-category").tablesorter({
			sortList: [[1,0]],
			headers: {0: { sorter: false } }
		})
		.tablesorterPager({
			container: jQuery("#rpt-pager-bycategory"),
			positionFixed: false,
			removeRows: false,
			output: '{page} / {totalPages}'
		});
		
		/** Pagination by Page **/
		jQuery("#insertion-by-page").tablesorter({
			sortList: [[1,0]],
			headers: {0: { sorter: false } }
		})
		.tablesorterPager({
			container: jQuery("#rpt-pager-bypage"),
			positionFixed: false,
			removeRows: false,
			output: '{page} / {totalPages}',
		});
		
		/** Pagination Page and Category **/
		jQuery("#insertion-category")
		.tablesorter({
			sortList: [[1,0]],
			headers: {0: { sorter: false } }
		})
		.tablesorterPager({
			container: jQuery("#rpt-pager-category"),
			positionFixed: false,
			removeRows: false,
			output: '{page} / {totalPages}'
		});
		
		jQuery("#insertion-page")
		.tablesorter({
			sortList: [[1,0]],
			headers: {0: { sorter: false } }
		})
		.tablesorterPager({
			container: jQuery("#rpt-pager-page"),
			positionFixed: false,
			removeRows: false,
			output: '{page} / {totalPages}'
		});
		
		/** Validation For Custom Label if Enabled **/
		var rtpForm = jQuery('#rtp-custom-form');
		var isCustom = jQuery('#rtp-custom-label');
		var trustworthy = jQuery('#trustworthy-custom');
		var objective = jQuery('#objective-custom');
		var complete = jQuery('#complete-custom');
		var wellwritten = jQuery('#well-written-custom');
		
		var vTrustworthy = jQuery('#rtp-valid-trustworthy');
		var vObjective = jQuery('#rtp-valid-objective');
		var vComplete = jQuery('#rtp-valid-complete');
		var vWellwritten = jQuery('#rtp-valid-wellwritten');
		
		var errMsg = "<?php _e( 'Field must not be empty!', RTP_PLUGIN_SNAME ); ?>";
		var reqMsg = "<?php _e( 'Field requires atleast 6 letters!', RTP_PLUGIN_SNAME ); ?>";
		
		isCustom.change(function() {
			if ( isCustom.val() == 'true' ) {
				trustworthy.blur(validateFieldTrustworthy);
				objective.blur(validateFieldObjective);
				complete.blur(validateFieldComplete);
				wellwritten.blur(validateFieldWellwritten);
				
				trustworthy.keyup(validateFieldTrustworthy);
				objective.keyup(validateFieldObjective);
				complete.keyup(validateFieldComplete);
				wellwritten.keyup(validateFieldWellwritten);
				
				validateFieldTrustworthy();
				validateFieldObjective();
				validateFieldComplete();
				validateFieldWellwritten();
			} else {
				vTrustworthy.text("");
				vObjective.text("");
				vComplete.text("");
				vWellwritten.text("");
				
				vTrustworthy.removeClass("rtp-error");
				vObjective.removeClass("rtp-error");
				vComplete.removeClass("rtp-error");
				vWellwritten.removeClass("rtp-error");
			}
		});
		
		rtpForm.submit(function() {
			if ( isCustom.val() == 'true' ) {
				if( validateFieldTrustworthy() && validateFieldObjective() && validateFieldComplete() && validateFieldWellwritten() ) {
					return true;
				} else {
					return false;
				}
			}
		});
		
		function validateFieldTrustworthy() {
			if ( trustworthy.val() == '' || trustworthy.val().length < 6 ) {
				vTrustworthy.addClass("rtp-error");
				if ( trustworthy.val() == '' ) {
					vTrustworthy.text(errMsg);
				} else {
					vTrustworthy.text(reqMsg);
				}
				return false;
			} else {
				vTrustworthy.text("");
				vTrustworthy.removeClass("rtp-error");
				return true;
			}
		}
		
		function validateFieldObjective() {
			if ( objective.val() == '' || objective.val().length < 6 ) {
				vObjective.addClass("rtp-error");
				if ( objective.val() == '' ) {
					vObjective.text(errMsg);
				} else {
					vObjective.text(reqMsg);
				}
				return false;
			} else {
				vObjective.text("");
				vObjective.removeClass("rtp-error");
				return true;
			}
		}
		
		function validateFieldComplete() {
			if ( complete.val() == '' || complete.val().length < 6 ) {
				vComplete.addClass("rtp-error");
				if ( complete.val() == '' ) {
					vComplete.text(errMsg);
				} else {
					vComplete.text(reqMsg);
				}
				return false;
			} else {
				vComplete.text("");
				vComplete.removeClass("rtp-error");
				return true;
			}
		}
		
		function validateFieldWellwritten() {
			if ( wellwritten.val() == '' || wellwritten.val().length < 6 ) {
				vWellwritten.addClass("rtp-error");
				if ( wellwritten.val() == '' ) {
					vWellwritten.text(errMsg);
				} else {
					vWellwritten.text(reqMsg);
				}
				return false;
			} else {
				vWellwritten.text("");
				vWellwritten.removeClass("rtp-error");
				return true;
			}
		}
	});
</script>
<div class="wrap">
	<div id="rtp-logo-title" class="icon32"></div>
	<h2><?php echo $title; ?></h2>
	<div id="message" class="updated"
	<?php 
		if( ( !isset( $_REQUEST['aft_plgn_submit_options'] ) || $error != "") &&
			( !isset( $_REQUEST['rtp_submit_custom'] ) || $error != "" ) &&
			( !isset( $_REQUEST['submit-insertion'] ) || $error != "") &&
			( !isset( $_REQUEST['rtp-install-table'] ) || $error != "") &&
			( !isset( $_REQUEST['rtp-uninstall-table'] ) || $error != "") )
			echo "style=\"display:none\"";
	?>>
	<p><strong><?php _e( $message, RTP_PLUGIN_SNAME ); ?></strong></p></div>
	<div class="tabs">
		<ul class="rtp-tab-navigation">
			<li><a href="#rtp-configuration"><?php _e( 'Main Configuration', RTP_PLUGIN_SNAME ); ?></a></li>
			<li <?php if ( $options['aft_insertion'] == 'all-article' ) echo "style='display:none;'" ?>><a href='#rtp-insertion'><?php _e( $lbl_insertion, RTP_PLUGIN_SNAME ); ?></a></li>
			<li><a href="#rtp-db-configuration"><?php _e( 'Database Configuration', RTP_PLUGIN_SNAME ); ?></a></li>
		</ul>
		<div id="rtp-configuration">
			<form id="rtp-config-form" name="frmoptions" method="post" action="<?php echo $base_page; ?>" enctype="multipart/form-data" >
				<h4><?php _e( 'Plugin Configurations', RTP_PLUGIN_SNAME ); ?>:</h4>
				<div id="rtp-plugin-config">
					<table class="rtp-form-table">
						<tr>
							<th><?php _e( 'Display Plugin:', RTP_PLUGIN_SNAME ); ?></th>
							<td>
								<select name="aft_is_enable" style="width: 120px;">
									<option <?php if ( $options['aft_is_enable'] == 'false' ) echo 'selected=""' ?> value="false">No</option>
									<option <?php if ( $options['aft_is_enable'] == 'true' ) echo 'selected=""' ?> value="true">Yes</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Plugin Insertion:', RTP_PLUGIN_SNAME ); ?></th>
							<td>
								<ul>
									<li>
										<input type="radio" id="rtp-all-article" name="rtp-insertion" value="all-article" <?php if ( $options['aft_insertion'] == 'all-article' ) echo "checked=''" ?> />
										<label for="rtp-all-article"><?php _e( 'All Article', RTP_PLUGIN_SNAME ); ?></label>
									</li>
									<li>
										<input type="radio" id="rtp-by-page" name="rtp-insertion" value="by-page" <?php if ( $options['aft_insertion'] == 'by-page' ) echo "checked=''" ?> />
										<label for="rtp-by-page"><?php _e( 'By Page', RTP_PLUGIN_SNAME ); ?></label>
									</li>
									<li>
										<input type="radio" id="rtp-by-category" name="rtp-insertion" value="by-category" <?php if ( $options['aft_insertion'] == 'by-category' ) echo "checked=''" ?> />
										<label for="rtp-by-category"><?php _e( 'By Category', RTP_PLUGIN_SNAME ); ?></label>
									</li>
									<li>
										<input type="radio" id="rtp-page-category" name="rtp-insertion" value="page-category" <?php if ( $options['aft_insertion'] == 'page-category' ) echo "checked=''" ?> />
										<label for="rtp-page-category"><?php _e( 'By Page and Category', RTP_PLUGIN_SNAME ); ?></label>
									</li>
								</ul>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Plugin Position:', RTP_PLUGIN_SNAME ); ?></th>
							<td>
								<select name="aft_location" style="width: 120px;">
									<option <?php if ( $options['aft_location'] == 'top' ) echo 'selected="selected"' ?> value="top"><?php _e( 'Top', RTP_PLUGIN_SNAME ); ?></option>
									<option <?php if ( $options['aft_location'] == 'bottom' ) echo 'selected="selected"' ?> value="bottom"><?php _e( 'Bottom', RTP_PLUGIN_SNAME ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Theme Selection:', RTP_PLUGIN_SNAME ); ?></th>
							<td>
								<select name="rtp-theme-select" style="width: 120px;">
									<?php foreach ( $theme_lists as $themes ) : ?>
									<option <?php if ( $options['rtp_theme'] == strtolower(preg_replace('/ /', '-', $themes)) ) echo 'selected=""' ?> value="<?php echo strtolower(preg_replace('/ /', '-', $themes)); ?>"><?php echo $themes; ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Logging Method:', RTP_PLUGIN_NAME ); ?></th>
							<td>
								<select name="rtp-logged-by">
									<option value="0" <?php if ( $options['rtp_logged_by'] == 0 ) echo 'selected=""' ?>><?php _e( 'Do Not Log', RTP_PLUGIN_NAME ); ?></option>
									<option value="1" <?php if ( $options['rtp_logged_by'] == 1 ) echo 'selected=""' ?>><?php _e( 'Logged by IP', RTP_PLUGIN_NAME ); ?></option>
									<option value="2" <?php if ( $options['rtp_logged_by'] == 2 ) echo 'selected=""' ?>><?php _e( 'Logged by Cookie', RTP_PLUGIN_NAME ); ?></option>
									<option value="3" <?php if ( $options['rtp_logged_by'] == 3 ) echo 'selected=""' ?>><?php _e( 'Logged by Cookie and IP', RTP_PLUGIN_NAME ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Allowed to Rate:', RTP_PLUGIN_NAME ); ?></th>
							<td>
								<select name="rtp-can-rate">
									<option value="0" <?php if ( $options['rtp_can_rate'] == 0 ) echo 'selected=""' ?>><?php _e( 'No One Can Rate', RTP_PLUGIN_NAME ); ?></option>
									<option value="1" <?php if ( $options['rtp_can_rate'] == 1 ) echo 'selected=""' ?>><?php _e( 'Registered Users Only', RTP_PLUGIN_NAME ); ?></option>
									<option value="2" <?php if ( $options['rtp_can_rate'] == 2 ) echo 'selected=""' ?>><?php _e( 'Guests Users Only', RTP_PLUGIN_NAME ); ?></option>
									<option value="3" <?php if ( $options['rtp_can_rate'] == 3 ) echo 'selected=""' ?>><?php _e( 'Both Can Rate', RTP_PLUGIN_NAME ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Use Shortcode:', RTP_PLUGIN_NAME ); ?><span>( <?php _e( 'Use [rate_this_page] syntax.', RTP_PLUGIN_SNAME ); ?> )</span></th>
							<td>
								<select name="rtp-use-shortcode" style="width: 60px">
									<option value="0" <?php if ( $options['rtp_use_shortcode'] == 0 ) echo 'selected=""' ?>><?php _e( 'No', RTP_PLUGIN_NAME ); ?></option>
									<option value="1" <?php if ( $options['rtp_use_shortcode'] == 1 ) echo 'selected=""' ?>><?php _e( 'Yes', RTP_PLUGIN_NAME ); ?></option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', RTP_PLUGIN_SNAME ); ?>" />
				<input type="hidden" name="aft_plgn_submit_options" value="submit" />
			</form>
			<form id="rtp-custom-form" name="frmoptions" method="post" action="<?php echo $base_page; ?>" enctype="multipart/form-data" >
				<h4><?php _e( 'Enable Custom Labels', RTP_PLUGIN_SNAME ); ?>:</h4>
				<div id="rtp-questions">
					<table class="rtp-form-table">
						<tr>
							<th><?php _e( 'Enable Custom Rating Label?', RTP_PLUGIN_SNAME ); ?></th>
							<td>
								<select id="rtp-custom-label" name="rtp-is-custom-label" style="width: 120px;">
									<option <?php if ( $options['rtp_is_custom_label'] == 'false' ) echo 'selected=""' ?> value="false">No</option>
									<option <?php if ( $options['rtp_is_custom_label'] == 'true' ) echo 'selected=""' ?> value="true">Yes</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Customize Label:', RTP_PLUGIN_SNAME ); ?><span>( <?php _e( 'will be used if customizing is enabled.', RTP_PLUGIN_SNAME ); ?> )</span></th>
							<td>
								<input id="trustworthy-custom" type="text" name="trustworthy-custom" value="<?php echo $options['rtp_custom_labels'][0]; ?>" maxlength="25" />
								<span id="rtp-valid-trustworthy"></span><br />								
								<input id="objective-custom" type="text" name="objective-custom" value="<?php echo $options['rtp_custom_labels'][1]; ?>" maxlength="25" />
								<span id="rtp-valid-objective"></span><br />								
								<input id="complete-custom" type="text" name="complete-custom" value="<?php echo $options['rtp_custom_labels'][2]; ?>" maxlength="25" />
								<span id="rtp-valid-complete"></span><br />								
								<input id="well-written-custom" type="text" name="well-written-custom" value="<?php echo $options['rtp_custom_labels'][3]; ?>" maxlength="25" />
								<span id="rtp-valid-wellwritten"></span>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Choose Hint:', RTP_PLUGIN_SNAME ); ?><span>( <?php _e( 'if customizing label is enabled you must choose atleast 1 of the custom hints.', RTP_PLUGIN_SNAME ); ?> )<span></th>
							<td>
								<input id="rtp-custom-hint-1" name="rtp-custom-hint" type="radio" value="1" <?php if ( $options['rtp_custom_hints'] == 1 ) echo 'checked=""' ?> /><label for="rtp-custom-hint-1">[<?php _e( 'Poor, Average and Excellent', RTP_PLUGIN_SNAME ); ?>]</label>
								<br />
								<input id="rtp-custom-hint-2" name="rtp-custom-hint" type="radio" value="2" <?php if ( $options['rtp_custom_hints'] == 2 ) echo 'checked=""' ?> /><label for="rtp-custom-hint-2">[<?php _e( 'Low, Medium and High', RTP_PLUGIN_SNAME ); ?>]</label>
								<br />
								<input id="rtp-custom-hint-3" name="rtp-custom-hint" type="radio" value="3" <?php if ( $options['rtp_custom_hints'] == 3 ) echo 'checked=""' ?> /><label for="rtp-custom-hint-3"><?php _e( 'Use Default', RTP_PLUGIN_SNAME ); ?></label>
							</td>
						</tr>
					</table>
				</div>
				<input class="button-primary" type="submit" value="<?php _e( 'Save Changes', RTP_PLUGIN_SNAME ); ?>" />
				<input type="hidden" name="rtp_submit_custom" value="submit" />
			</form>
		</div>
		<div id="rtp-insertion">
			<form method="post" action="<?php echo $base_page; ?>" enctype="multipart/form-data" >
				<input type="hidden" name="submit-insertion" value="submit" />
				<div class="rtp-label-top">
					<input class="button-secondary" type="submit" value="<?php _e( 'Apply Insertion' ); ?>" />
				</div>
				<?php if ( $options['aft_insertion'] == 'by-category' ) : ?>
				<table id="insertion-by-category" class="widefat tablesorter">
					<thead>
						<th id="cb" class="manage-column column-cb check-column" style="" scope="col" width="20" align="center">
							<input type="checkbox" class="aft-all-category" />
						</th>
						<th><?php _e( 'ID', RTP_PLUGIN_SNAME ); ?></th>
						<th><?php _e( 'Title', RTP_PLUGIN_SNAME ); ?></th>
						<th><?php _e( 'Slug', RTP_PLUGIN_SNAME ); ?></th>
					</thead>
					<tbody>
						<?php
							$cat_opt_id = array();
							foreach( $options_by['category'] as $category ) {
								$cat_opt_id[] = $category;
							}
						?>
						<?php $i = 0; ?>
						<?php foreach ( $cat_id as $id ) : ?>
						<?php $cat_arr = get_category($id); ?>
						<tr>
							<th class="check-column" scope="row" align="center">
							<input id="aft-category-check-<?php echo $i; ?>" type="checkbox" name="aft-category-check-<?php echo $i; ?>" value="<?php echo $cat_arr->term_id; ?>" <?php if ( $cat_arr->term_id == $cat_opt_id[$i] ) echo 'checked="checked"'; ?> />
							</th>
							<td class="rtp-ins-id"><?php echo $cat_arr->term_id; ?></td>
							<td class="rtp-ins-name"><?php echo $cat_arr->name; ?></td>
							<td class="rtp-ins-slug"><?php echo $cat_arr->slug; ?></td>
						</tr>
						<?php $i++; ?>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr id="rpt-pager-bycategory" style="text-align: center;">
							<td colspan="4">
								<img class="first" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop-180.png" />
								<img class="prev" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-180.png" />
								<input class="pagedisplay" type="text" readonly="true" />
								<img class="next" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow.png" />
								<img class="last" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop.png" />
								<input type="hidden" class="pagesize" value="10">
							</td>
						</tr>
					</tfoot>
				</table>
				<?php elseif ( $options['aft_insertion'] == 'by-page' ) : ?>
				<table id="insertion-by-page" class="widefat tablesorter">
					<thead>
						<th id="cb" class="manage-column column-cb check-column" style="" scope="col" width="20" align="center">
							<input type="checkbox" id="aft-all-page" />
						</th>
						<th><?php _e( 'ID', RTP_PLUGIN_SNAME ); ?></th>
						<th><?php _e( 'Title', RTP_PLUGIN_SNAME ); ?></th>
						<th><?php _e( 'Slug', RTP_PLUGIN_SNAME ); ?></th>
					</thead>
					<tbody>
						<?php
							$page_opt_id = array();
							foreach ( $options_by['page'] as $page ) {
								$page_opt_id[] = $page;
							}
						?>
						<?php $i = 0; ?>
						<?php foreach ( $page_id as $id ) : ?>
						<?php $page_data = get_page( $id ); ?>
						<tr>
							<th class="check-column" scope="row" align="center">
								<input id="aft-page-check-<?php echo $i; ?>" type="checkbox" name="aft-page-check-<?php echo $i; ?>" value="<?php echo $id; ?>" <?php if ( $id == $page_opt_id[$i] ) echo 'checked="checked"'; ?> />
							</th>
							<td class="rtp-ins-id"><?php echo $page_data->ID; ?></td>
							<td class="rtp-ins-name"><?php echo $page_data->post_title; ?></td>
							<td class="rtp-ins-slug"><?php echo $page_data->post_name; ?></td>
						</tr>
						<?php $i++; ?>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr id="rpt-pager-bypage" style="text-align: center;">
							<td colspan="4">
								<img class="first" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop-180.png" />
								<img class="prev" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-180.png" />
								<input class="pagedisplay" type="text" readonly="true" />
								<img class="next" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow.png" />
								<img class="last" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop.png" />
								<input type="hidden" class="pagesize" value="10">
							</td>
						</tr>
					</tfoot>
				</table>
				<?php else: ?>
				<div style="clear: both;"></div>
				<div>
					<fieldset>
						<legend><b><?php _e( _n( 'Category List', 'Category Lists', $count_c ), RTP_PLUGIN_SNAME ); ?></b></legend>
						<table id="insertion-category" class="widefat tablesorter">
							<thead>
								<th id="cb" class="manage-column column-cb check-column" style="" scope="col" width="20" align="center">
									<input type="checkbox" class="aft-all-category" />
								</th>
								<th><?php _e( 'ID', RTP_PLUGIN_SNAME ); ?></th>
								<th><?php _e( 'Title', RTP_PLUGIN_SNAME ); ?></th>
								<th><?php _e( 'Slug', RTP_PLUGIN_SNAME ); ?></th>
							</thead>
							<tbody>
								<?php
									$cat_opt_id = array();
									foreach( $options_by['category'] as $category ) {
										$cat_opt_id[] = $category;
									}
								?>
								<?php $i = 0; ?>
								<?php foreach ( $cat_id as $id ) : ?>
								<?php $cat_arr = get_category($id); ?>
								<tr>
									<th class="check-column" scope="row" align="center">
									<input id="aft-category-check-<?php echo $i; ?>" type="checkbox" name="aft-category-check-<?php echo $i; ?>" value="<?php echo $cat_arr->term_id; ?>" <?php if ( $cat_arr->term_id == $cat_opt_id[$i] ) echo 'checked="checked"'; ?> />
									</th>
									<td class="rtp-ins-id"><?php echo $cat_arr->term_id; ?></td>
									<td class="rtp-ins-name"><?php echo $cat_arr->name; ?></td>
									<td class="rtp-ins-slug"><?php echo $cat_arr->slug; ?></td>
								</tr>
								<?php $i++; ?>
								<?php endforeach; ?>
							</tbody>
							<tfoot>
								<tr id="rpt-pager-category" style="text-align: center;">
									<td colspan="4">
										<img class="first" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop-180.png" />
										<img class="prev" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-180.png" />
										<input class="pagedisplay" type="text" readonly="true" />
										<img class="next" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow.png" />
										<img class="last" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop.png" />
										<input type="hidden" class="pagesize" value="10">
									</td>
								</tr>
							</tfoot>
						</table>
					</fieldset>
				</div><br />
				<div>
					<fieldset>
						<legend><b><?php _e( _n( 'Page List', 'Page Lists', $count_p ), RTP_PLUGIN_SNAME ); ?></b></legend>
						<table id="insertion-page" class="widefat tablesorter">
							<thead>
								<th id="cb" class="manage-column column-cb check-column" style="" scope="col" width="20" align="center">
									<input type="checkbox" id="aft-all-page" />
								</th>
								<th><?php _e( 'ID', RTP_PLUGIN_SNAME ); ?></th>
								<th><?php _e( 'Title', RTP_PLUGIN_SNAME ); ?></th>
								<th><?php _e( 'Slug', RTP_PLUGIN_SNAME ); ?></th>
							</thead>
							<tbody>
								<?php
									$page_opt_id = array();
									foreach ( $options_by['page'] as $page ) {
										$page_opt_id[] = $page;
									}
								?>
								<?php $i = 0; ?>
								<?php foreach ( $page_id as $id ) : ?>
								<?php $page_data = get_page( $id ); ?>
								<tr>
									<th class="check-column" scope="row" align="center">
										<input id="aft-page-check-<?php echo $i; ?>" type="checkbox" name="aft-page-check-<?php echo $i; ?>" value="<?php echo $page_data->ID; ?>" <?php if ( $page_data->ID == $page_opt_id[$i] ) echo 'checked="checked"'; ?> />
									</th>
									<td class="rtp-ins-id"><?php echo $page_data->ID; ?></td>
									<td class="rtp-ins-name"><?php echo $page_data->post_title; ?></td>
									<td class="rtp-ins-slug"><?php echo $page_data->post_name; ?></td>
								</tr>
								<?php $i++; ?>
								<?php endforeach; ?>
							</tbody>
							<tfoot>
								<tr id="rpt-pager-page" style="text-align: center;">
									<td colspan="4">
										<img class="first" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop-180.png" />
										<img class="prev" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-180.png" />
										<input class="pagedisplay" type="text" readonly="true" />
										<img class="next" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow.png" />
										<img class="last" src="<?php echo RTP_PLUGIN_DIR_IMG; ?>arrow-stop.png" />
										<input type="hidden" class="pagesize" value="10">
									</td>
								</tr>
							</tfoot>
						</table>
					</fieldset>
				</div>
				<?php endif; ?>
				<br /><div class="rtp-label-top">
					<input class="button-secondary" type="submit" value="<?php _e( 'Apply Insertion' ); ?>" />
				</div>
			</form>
		</div>
		
		<div id="rtp-db-configuration">
			<div>
				<form method="post" action="<?php echo $base_page; ?>" enctype="multipart/form-data">
					<table class="rtp-form-table">
						<tr>
							<th class="rtp-p"><?php _e( 'Table Installation:', RTP_PLUGIN_SNAME ); ?></th>
							<td>
							<?php if ( $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks'") != $tbl_feedbacks 
									&& $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks_summary'") != $tbl_feedbacks_summary ) : ?>
							<input class="button-secondary" name="rtp-install-table" type="submit" value="<?php _e( 'Install', RTP_PLUGIN_SNAME ); ?>" />
							<?php else : ?>
							<input class="button-secondary" name="rtp-uninstall-table" type="submit" value="<?php _e( 'Uninstall', RTP_PLUGIN_SNAME ); ?>" />
							<?php endif; ?>
							</td>
						</tr>
						<tr><p class="rtp-p"><?php _e( "All database tables will be deleted. This procedure is not reversible. <br />Backup the database (this plugin use wp-feedbacks and wp-feedbacks-summary in phpMyAdmin) if you are in process of migration and want to restore the report ratings later.", RTP_PLUGIN_SNAME ); ?></p></tr>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="corners">
	<?php global $wpdb; ?>
	<?php $tbl_feedbacks = $wpdb->prefix . "feedbacks"; ?>
	<?php $tbl_feedbacks_summary = $wpdb->prefix . "feedbacks_summary"; ?>
	<?php if ( $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks'") != $tbl_feedbacks 
			&& $wpdb->get_var("SHOW TABLES LIKE '$tbl_feedbacks_summary'") != $tbl_feedbacks_summary ) : ?>
			<p class='rtp-p rtp-bg corners'><strong><?php _e( 'Warning', RTP_PLUGIN_SNAME ); ?>:</strong><br />
			<em><?php _e( "No tables found inside the database.<br />
			Please go to Database Configuration Tab then (Click Install) to install
			the required database tables.", RTP_PLUGIN_SNAME ); ?></em></p>
	<?php endif; ?>
</div>