<?php
/*
Plugin Name: WTI Like Post
Plugin URI: http://www.webtechideas.com/wti-like-post-plugin/
Description: WTI Like Post is a plugin for adding like (thumbs up) and unlike (thumbs down) functionality for wordpress posts/pages. On admin end alongwith handful of configuration settings, it will show a list of most liked posts/pages. If you have already liked a post/page and now you dislike it, then the old voting will be cancelled and vice-versa. It also has the option to reset the settings to default if needed. You can reset the like counts for all/selected posts/pages. It comes with two widgets, one to display the most liked posts/pages for a given time range and another to show recently liked posts.
Version: 1.4
Author: webtechideas
Author URI: http://www.webtechideas.com/
License: GPLv2 or later

Copyright 2011  Webtechideas  (email : webtechideas@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

#### INSTALLATION PROCESS ####
/*
1. Download the plugin and extract it
2. Upload the directory '/wti-like-post/' to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Click on 'WTI Like Post' link under Settings menu to access the admin section
*/

$wti_like_post_db_version = "1.0";

add_action( 'init', 'WtiLoadPluginTextdomain' );

function WtiLoadPluginTextdomain() {
     load_plugin_textdomain( 'wti-like-post', false, 'wti-like-post/lang' );
}

add_filter('plugin_action_links', 'wti_like_post_plugin_links', 10, 2);

function wti_like_post_plugin_links($links, $file) {
     static $this_plugin;

     if (!$this_plugin) {
		$this_plugin = plugin_basename(__FILE__);
     }

     if ($file == $this_plugin) {
		// The "page" query string value must be equal to the slug
		// of the Settings admin page we defined earlier, which in
		// this case equals "myplugin-settings".
		$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=WtiLikePostAdminMenu">' . __('Settings', 'wti-like-post') . '</a>';
		array_unshift($links, $settings_link);
     }

     return $links;
}

function SetOptionsWtiLikePost() {
     global $wpdb, $wti_like_post_db_version;

     //creating the like post table on activating the plugin
     $wti_like_post_table_name = $wpdb->prefix . "wti_like_post";
	
     if($wpdb->get_var("show tables like '$wti_like_post_table_name'") != $wti_like_post_table_name) {
		$sql = "CREATE TABLE " . $wti_like_post_table_name . " (
			`id` bigint(11) NOT NULL AUTO_INCREMENT,
			`post_id` int(11) NOT NULL,
			`value` int(2) NOT NULL,
			`date_time` datetime NOT NULL,
			`ip` varchar(20) NOT NULL,
			`user_id` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`)
		)";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		add_option("wti_like_post_db_version", $wti_like_post_db_version);
     }
	
	$user_col = $wpdb->get_col("SELECT user_id FROM {$wpdb->prefix}wti_like_post");
	
	if(!$user_col) {
		$wpdb->query("ALTER TABLE `$wti_like_post_table_name` ADD `user_id` INT NOT NULL DEFAULT '0'");
	}
	
     //adding options for the like post plugin
     add_option('wti_like_post_jquery', '1', '', 'yes');
     add_option('wti_like_post_voting_period', '0', '', 'yes');
     add_option('wti_like_post_voting_style', 'style1', '', 'yes');
     add_option('wti_like_post_alignment', 'left', '', 'yes');
     add_option('wti_like_post_position', 'bottom', '', 'yes');
     add_option('wti_like_post_login_required', '0', '', 'yes');
     add_option('wti_like_post_login_message', __('Please login to vote.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_thank_message', __('Thanks for your vote.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_voted_message', __('You have already voted.', 'wti-like-post'), '', 'yes');
     add_option('wti_like_post_allowed_posts', '', '', 'yes');
     add_option('wti_like_post_excluded_posts', '', '', 'yes');
     add_option('wti_like_post_excluded_categories', '', '', 'yes');
     add_option('wti_like_post_excluded_sections', '', '', 'yes');
     add_option('wti_like_post_show_on_pages', '0', '', 'yes');
     add_option('wti_like_post_show_on_widget', '1', '', 'yes');
     add_option('wti_like_post_show_symbols', '1', '', 'yes');
     add_option('wti_like_post_show_dislike', '1', '', 'yes');
     add_option('wti_like_post_title_text', 'Like/Unlike', '', 'yes');
     add_option('wti_like_post_db_version', $wti_like_post_db_version, '', 'yes');	
}

register_activation_hook(__FILE__, 'SetOptionsWtiLikePost');

function UnsetOptionsWtiLikePost() {
     global $wpdb;
     
     //dropping the table on plugin uninstall
     $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wti_like_post");

     //deleting the added options on plugin uninstall
     delete_option('wti_like_post_jquery');
     delete_option('wti_like_post_voting_period');
     delete_option('wti_like_post_voting_style');
     delete_option('wti_like_post_alignment');
     delete_option('wti_like_post_position');
     delete_option('wti_like_post_login_required');
     delete_option('wti_like_post_login_message');
     delete_option('wti_like_post_thank_message');
     delete_option('wti_like_post_voted_message');
     delete_option('wti_like_post_db_version');
     delete_option('wti_like_post_allowed_posts');
     delete_option('wti_like_post_excluded_posts');
     delete_option('wti_like_post_excluded_categories');
     delete_option('wti_like_post_excluded_sections');
     delete_option('wti_like_post_show_on_pages');
     delete_option('wti_like_post_show_on_widget');
     delete_option('wti_like_post_show_symbols');
     delete_option('wti_like_post_show_dislike');
     delete_option('wti_like_post_title_text');
}

register_uninstall_hook(__FILE__, 'UnsetOptionsWtiLikePost');

#### ADMIN OPTIONS ####
function WtiLikePostAdminMenu() {
     add_options_page('WTI Like Post', __('WTI Like Post', 'wti-like-post'), 'activate_plugins', 'WtiLikePostAdminMenu', 'WtiLikePostAdminContent');
}
add_action('admin_menu', 'WtiLikePostAdminMenu');

function WtiLikePostAdminRegisterSettings() {
     //registering the settings
     register_setting( 'wti_like_post_options', 'wti_like_post_jquery' );
     register_setting( 'wti_like_post_options', 'wti_like_post_voting_period' );
     register_setting( 'wti_like_post_options', 'wti_like_post_voting_style' );
     register_setting( 'wti_like_post_options', 'wti_like_post_alignment' );
     register_setting( 'wti_like_post_options', 'wti_like_post_position' );
     register_setting( 'wti_like_post_options', 'wti_like_post_login_required' );
     register_setting( 'wti_like_post_options', 'wti_like_post_login_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_thank_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_voted_message' );
     register_setting( 'wti_like_post_options', 'wti_like_post_allowed_posts' );
     register_setting( 'wti_like_post_options', 'wti_like_post_excluded_posts' );
     register_setting( 'wti_like_post_options', 'wti_like_post_excluded_categories' );
     register_setting( 'wti_like_post_options', 'wti_like_post_excluded_sections' );
     register_setting( 'wti_like_post_options', 'wti_like_post_show_on_pages' );
     register_setting( 'wti_like_post_options', 'wti_like_post_show_on_widget' );
     register_setting( 'wti_like_post_options', 'wti_like_post_db_version' );	
     register_setting( 'wti_like_post_options', 'wti_like_post_show_symbols' );
     register_setting( 'wti_like_post_options', 'wti_like_post_show_dislike' );
     register_setting( 'wti_like_post_options', 'wti_like_post_title_text' );	
}
add_action('admin_init', 'WtiLikePostAdminRegisterSettings');

function WtiLikePostAdminContent() {
     //creating the admin configuration interface
     global $wpdb, $wti_like_post_db_version;
     
	$excluded_sections = get_option('wti_like_post_excluded_sections');
	$excluded_categories = get_option('wti_like_post_excluded_categories');
	
	if(empty($excluded_sections)) {
		$excluded_sections = array();
	}
	
	if(empty($excluded_categories)) {
		$excluded_categories = array();
	}
?>
<div class="wrap">
     <h2><?php _e('WTI Like Post Options', 'wti-like-post');?></h2>
     <br class="clear" />
     
	<div id="poststuff" class="ui-sortable meta-box-sortables">
		<div id="WtiLikePostOptions" class="postbox">
			<h3><?php _e('Donation', 'wti-like-post'); ?></h3>
			<div class="inside">
				<p>
					<?php echo __('There has been a lot of effort put behind the development of this plugin. Please consider donating towards this plugin development.', 'wti-like-post');?>
					<form method="post" action="https://www.paypal.com/cgi-bin/webscr" target="_blank">
						<?php _e('Amount', 'wti-like-post'); ?> $ <input type="text" value="" title="Other donate" size="5" name="amount"><br />
						<input type="hidden" value="_xclick" name="cmd" />
						<input type="hidden" value="webtechideas@gmail.com" name="business" />
						<input type="hidden" value="WTI Like Post" name="item_name" />
						<input type="hidden" value="USD" name="currency_code" />
						<input type="hidden" value="0" name="no_shipping" />
						<input type="hidden" value="1" name="no_note" />
						<input type="hidden" value="3FWGC6LFTMTUG" name="mrb" />
						<input type="hidden" value="IC_Sample" name="bn" />
						<input type="hidden" value="http://www.webtechideas.com/thanks/" name="return" />
						<input type="image" alt="Make payments with payPal - it's fast, free and secure!" name="submit" src="https://www.paypal.com/en_US/i/btn/x-click-but11.gif" />
					</form>
				</p>
			</div>
		</div>
	</div>
	
     <div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="WtiLikePostOptions" class="postbox">
			<h3><?php _e('Configuration', 'wti-like-post'); ?></h3>
			<div class="inside">
				<form method="post" action="options.php">
					<?php settings_fields('wti_like_post_options'); ?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><label for="wti_like_post_jquery"><?php _e('jQuery Framework', 'wti-like-post'); ?></label></th>
							<td>
								<select name="wti_like_post_jquery" id="wti_like_post_jquery">
									<option value="1" <?php if(get_option('wti_like_post_jquery') == '1') { echo 'selected'; }?>><?php _e('Enabled', 'wti-like-post') ?></option>
									<option value="0" <?php if(get_option('wti_like_post_jquery') == '0') { echo 'selected'; }?>><?php _e('Disabled', 'wti-like-post') ?></option>
								</select>
								<span class="description"><?php _e('Disable it if you already have the jQuery framework enabled in your theme.', 'wti-like-post'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Voting Period', 'wti-like-post'); ?></label></th>
							<td>
								<?php
								$voting_period = get_option('wti_like_post_voting_period');
								?>
								<select name="wti_like_post_voting_period" id="wti_like_post_voting_period">
									<option value="0"><?php echo __('Always can vote', 'wti-like-post'); ?></option>
									<option value="once" <?php if("once" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Only once', 'wti-like-post'); ?></option>
									<option value="1" <?php if("1" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One day', 'wti-like-post'); ?></option>
									<option value="2" <?php if("2" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Two days', 'wti-like-post'); ?></option>
									<option value="3" <?php if("3" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Three days', 'wti-like-post'); ?></option>
									<option value="7" <?php if("7" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One week', 'wti-like-post'); ?></option>
									<option value="14" <?php if("14" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Two weeks', 'wti-like-post'); ?></option>
									<option value="21" <?php if("21" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Three weeks', 'wti-like-post'); ?></option>
									<option value="1m" <?php if("1m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One month', 'wti-like-post'); ?></option>
									<option value="2m" <?php if("2m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Two months', 'wti-like-post'); ?></option>
									<option value="3m" <?php if("3m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Three months', 'wti-like-post'); ?></option>
									<option value="6m" <?php if("6m" == $voting_period) echo "selected='selected'"; ?>><?php echo __('Six Months', 'wti-like-post'); ?></option>
									<option value="1y" <?php if("1y" == $voting_period) echo "selected='selected'"; ?>><?php echo __('One Year', 'wti-like-post'); ?></option>
								</select>
								<span class="description"><?php _e('Select the voting period after which user can vote again.', 'wti-like-post');?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Voting Style', 'wti-like-post'); ?></label></th>
							<td>
								<?php
								$voting_style = get_option('wti_like_post_voting_style');
								?>
								<select name="wti_like_post_voting_style" id="wti_like_post_voting_style">
									<option value="style1" <?php if("style1" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style1', 'wti-like-post'); ?></option>
									<option value="style2" <?php if("style2" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style2', 'wti-like-post'); ?></option>
									<option value="style3" <?php if("style3" == $voting_style) echo "selected='selected'"; ?>><?php echo __('Style3', 'wti-like-post'); ?></option>
								</select>
								<span class="description"><?php _e('Select the voting style from 3 available options with 3 different sets of images.', 'wti-like-post'); ?></span>
							</td>
						</tr>			
						<tr valign="top">
							<th scope="row"><label><?php _e('Login required to vote', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="radio" name="wti_like_post_login_required" id="login_yes" value="1" <?php if(1 == get_option('wti_like_post_login_required')) { echo 'checked'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
								<input type="radio" name="wti_like_post_login_required" id="login_no" value="0" <?php if((0 == get_option('wti_like_post_login_required')) || ('' == get_option('wti_like_post_login_required'))) { echo 'checked'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
								<span class="description"><?php _e('Select whether only logged in users can vote or not.', 'wti-like-post');?></span>
							</td>
						</tr>			
						<tr valign="top">
							<th scope="row"><label><?php _e('Login required message', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="text" size="40" name="wti_like_post_login_message" id="wti_like_post_login_message" value="<?php echo get_option('wti_like_post_login_message'); ?>" />
								<span class="description"><?php _e('Message to show in case login required and user is not logged in.', 'wti-like-post');?></span>
							</td>
						</tr>			
						<tr valign="top">
							<th scope="row"><label><?php _e('Thank you message', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="text" size="40" name="wti_like_post_thank_message" id="wti_like_post_thank_message" value="<?php echo get_option('wti_like_post_thank_message'); ?>" />
								<span class="description"><?php _e('Message to show after successful voting.', 'wti-like-post');?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Already voted message', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="text" size="40" name="wti_like_post_voted_message" id="wti_like_post_voted_message" value="<?php echo get_option('wti_like_post_voted_message'); ?>" />
								<span class="description"><?php _e('Message to show if user has already voted.', 'wti-like-post');?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Show on pages', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="radio" name="wti_like_post_show_on_pages" id="show_pages_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_on_pages'))) { echo 'checked'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
								<input type="radio" name="wti_like_post_show_on_pages" id="show_pages_no" value="0" <?php if('0' == get_option('wti_like_post_show_on_pages') || ('' == get_option('wti_like_post_show_on_pages'))) { echo 'checked'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
								<span class="description"><?php _e('Select yes if you want to show the like option on pages as well.', 'wti-like-post')?></span>
							</td>
						</tr>	
						<tr valign="top">
							<th scope="row"><label><?php _e('Exclude on selected sections', 'wti-like-post'); ?></label></th>
							<td>
								<input type="checkbox" name="wti_like_post_excluded_sections[]" id="wti_like_post_excluded_home" value="home" <?php if(in_array('home', $excluded_sections)) { echo 'checked'; } ?> /> <?php echo __('Home', 'wti-like-post'); ?>
								<input type="checkbox" name="wti_like_post_excluded_sections[]" id="wti_like_post_excluded_archive" value="archive" <?php if(in_array('archive', $excluded_sections)) { echo 'checked'; } ?> /> <?php echo __('Archive', 'wti-like-post'); ?>
								<span class="description"><?php _e('Check the sections where you do not want to avail the like/dislike options. This has higher priority than the "Exclude post/page IDs" setting.', 'wti-like-post');?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Exclude selected categories', 'wti-like-post'); ?></label></th>
							<td>	
								<select name='wti_like_post_excluded_categories[]' id='wti_like_post_excluded_categories' multiple="multiple" size="4" style="height:auto !important;">
									<?php 
									$categories=  get_categories();
									
									foreach ($categories as $category) {
										$selected = (in_array($category->cat_ID, $excluded_categories)) ? 'selected="selected"' : '';
										$option  = '<option value="' . $category->cat_ID . '" ' . $selected . '>';
										$option .= $category->cat_name;
										$option .= ' (' . $category->category_count . ')';
										$option .= '</option>';
										echo $option;
									}
									?>
								</select>
								<span class="description"><?php _e('Select categories where you do not want to show the like option. It has higher priority than "Exclude post/page IDs" setting.', 'wti-like-post');?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Allow post IDs', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="text" size="40" name="wti_like_post_allowed_posts" id="wti_like_post_allowed_posts" value="<?php _e(get_option('wti_like_post_allowed_posts')); ?>" />
								<span class="description"><?php _e('Suppose you have a post which belongs to more than one categories and you have excluded one of those categories. So the like/dislike will not be available for that post. Enter comma separated those post ids where you want to show the like/dislike option irrespective of that post category being excluded.', 'wti-like-post');?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Exclude post/page IDs', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="text" size="40" name="wti_like_post_excluded_posts" id="wti_like_post_excluded_posts" value="<?php _e(get_option('wti_like_post_excluded_posts')); ?>" />
								<span class="description"><?php _e('Enter comma separated post/page ids where you do not want to show the like option. If Show on pages setting is set to Yes but you have added the page id here, then like option will not be shown for the same page.', 'wti-like-post');?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Show excluded posts/pages on widget', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="radio" name="wti_like_post_show_on_widget" id="show_widget_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_on_widget')) || ('' == get_option('wti_like_post_show_on_widget'))) { echo 'checked'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
								<input type="radio" name="wti_like_post_show_on_widget" id="show_widget_no" value="0" <?php if('0' == get_option('wti_like_post_show_on_widget')) { echo 'checked'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
								<span class="description"><?php _e('Select yes if you want to show the excluded posts/pages on widget.', 'wti-like-post')?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Position Setting', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="radio" name="wti_like_post_position" id="position_top" value="top" <?php if(('top' == get_option('wti_like_post_position')) || ('' == get_option('wti_like_post_position'))) { echo 'checked'; } ?> /> <?php echo __('Top of Content', 'wti-like-post'); ?>
								<input type="radio" name="wti_like_post_position" id="position_bottom" value="bottom" <?php if('bottom' == get_option('wti_like_post_position')) { echo 'checked'; } ?> /> <?php echo __('Bottom of Content', 'wti-like-post'); ?>
								<span class="description"><?php _e('Select the position where you want to show the like options.', 'wti-like-post')?></span>
							</td>
						</tr>			
						<tr valign="top">
							<th scope="row"><label><?php _e('Alignment Setting', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="radio" name="wti_like_post_alignment" id="alignment_left" value="left" <?php if(('left' == get_option('wti_like_post_alignment')) || ('' == get_option('wti_like_post_alignment'))) { echo 'checked'; } ?> /> <?php echo __('Left', 'wti-like-post'); ?>
								<input type="radio" name="wti_like_post_alignment" id="alignment_right" value="right" <?php if('right' == get_option('wti_like_post_alignment')) { echo 'checked'; } ?> /> <?php echo __('Right', 'wti-like-post'); ?>
								<span class="description"><?php _e('Select the alignment whether to show on left or on right.', 'wti-like-post')?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Title text for like/unlike images', 'wti-like-post'); ?></label></th>
							<td>
								<input type="text" name="wti_like_post_title_text" id="wti_like_post_title_text" value="<?php echo get_option('wti_like_post_title_text')?>" />
								<span class="description"><?php echo __('Enter both texts separated by "/" to show when user puts mouse over like/unlike images.', 'wti-like-post')?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Show dislike option', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="radio" name="wti_like_post_show_dislike" id="show_dislike_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_dislike')) || ('' == get_option('wti_like_post_show_dislike'))) { echo 'checked'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
								<input type="radio" name="wti_like_post_show_dislike" id="show_dislike_no" value="0" <?php if('0' == get_option('wti_like_post_show_dislike')) { echo 'checked'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
								<span class="description"><?php _e('Select the option whether to show or hide the dislike option.', 'wti-like-post')?></span>
							</td>
						</tr>	
						<tr valign="top">
							<th scope="row"><label><?php _e('Show +/- symbols', 'wti-like-post'); ?></label></th>
							<td>	
								<input type="radio" name="wti_like_post_show_symbols" id="show_symbol_yes" value="1" <?php if(('1' == get_option('wti_like_post_show_symbols')) || ('' == get_option('wti_like_post_show_symbols'))) { echo 'checked'; } ?> /> <?php echo __('Yes', 'wti-like-post'); ?>
								<input type="radio" name="wti_like_post_show_symbols" id="show_symbol_no" value="0" <?php if('0' == get_option('wti_like_post_show_symbols')) { echo 'checked'; } ?> /> <?php echo __('No', 'wti-like-post'); ?>
								<span class="description"><?php _e('Select the option whether to show or hide the plus or minus symbols before like/unlike count.', 'wti-like-post')?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"></th>
							<td>
								<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options', 'wti-like-post'); ?>" />
								<input class="button-secondary" type="submit" name="Reset" value="<?php _e('Reset Options', 'wti-like-post'); ?>" onclick="return confirmReset()" />
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
     </div>	
     <script>
     function confirmReset()
     {
		//check whether user agrees to reset the settings to default or not
		var check = confirm("<?php _e('Are you sure to reset the options to default settings?', 'wti-like-post')?>");
		
		if(check)
		{
			//reset the settings
			document.getElementById('wti_like_post_jquery').value = 1;
			document.getElementById('wti_like_post_voting_period').value = 0;
			document.getElementById('wti_like_post_voting_style').value = 'style1';
			document.getElementById('login_yes').checked = false;
			document.getElementById('login_no').checked = true;
			document.getElementById('wti_like_post_login_message').value = 'Please login to vote.';
			document.getElementById('wti_like_post_thank_message').value = 'Thanks for your vote.';
			document.getElementById('wti_like_post_voted_message').value = 'You have already voted.';
			document.getElementById('show_pages_yes').checked = false;
			document.getElementById('show_pages_no').checked = true;
			document.getElementById('wti_like_post_allowed_posts').value = '';
			document.getElementById('wti_like_post_excluded_posts').value = '';
			document.getElementById('wti_like_post_excluded_categories').selectedIndex = -1;
			document.getElementById('wti_like_post_excluded_home').value = '';
			document.getElementById('wti_like_post_excluded_archive').value = '';
			document.getElementById('show_widget_yes').checked = true;
			document.getElementById('show_widget_no').checked = false;
			document.getElementById('position_top').checked = false;
			document.getElementById('position_bottom').checked = true;
			document.getElementById('alignment_left').checked = true;
			document.getElementById('alignment_right').checked = false;
			document.getElementById('show_symbol_yes').checked = true;
			document.getElementById('show_symbol_no').checked = false;
			document.getElementById('show_dislike_yes').checked = true;
			document.getElementById('show_dislike_no').checked = false;
			document.getElementById('wti_like_post_title_text').value = 'Like/Unlike';
			
			return true;
		}
		
		return false;
     }
	
     function processAll()
     {
		var cfm = confirm('<?php echo __('Are you sure to reset all the counts present in the database?', 'wti-like-post')?>');
		
		if(cfm)
		{
			return true;
		}
		else
		{
			return false;
		}
     }
	
     function processSelected()
     {
		var cfm = confirm('<?php echo __('Are you sure to reset selected counts present in the database?', 'wti-like-post')?>');
		
		if(cfm)
		{
			return true;
		}
		else
		{
			return false;
		}
     }
     </script>
	
     <?php
     if(isset($_POST['resetall'])) {
		$status = $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wti_like_post");
		if($status) {
			echo '<div class="updated" id="message"><p>';
			echo __('All counts have been reset successfully.', 'wti-like-post');
			echo '</p></div>';
		} else {
			echo '<div class="error" id="error"><p>';
			echo __('All counts could not be reset.', 'wti-like-post');
			echo '</p></div>';
		}
     }
     if(isset($_POST['resetselected'])) {
		if(count($_POST['post_ids']) > 0) {
			$post_ids = implode(",", $_POST['post_ids']);
			$status = $wpdb->query("DELETE FROM {$wpdb->prefix}wti_like_post WHERE post_id IN ($post_ids)");
			if($status) {
				echo '<div class="updated" id="message"><p>';
				if($status > 1) {
					echo $status . ' ' . __('counts have been reset successfully.', 'wti-like-post');
				} else {
					echo $status . ' ' . __('count has been reset successfully.', 'wti-like-post');
				}
				echo '</p></div>';
			} else {
				echo '<div class="error" id="error"><p>';
				echo __('Selected counts could not be reset.', 'wti-like-post');
				echo '</p></div>';
			}
		} else {
			echo '<div class="error" id="error"><p>';
			echo __('Please select posts to reset count.', 'wti-like-post');
			echo '</p></div>';
		}
     }
     ?>
	
     <div id="poststuff" class="ui-sortable meta-box-sortables">
		<h2><?php _e('Most Liked Posts', 'wti-like-post');?></h2>
		<?php
		//getting the most liked posts
		$query = "SELECT COUNT(post_id) AS total FROM `{$wpdb->prefix}wti_like_post` L JOIN {$wpdb->prefix}posts P ";
		$query .= "ON L.post_id = P.ID WHERE value > 0";
		$post_count = $wpdb->get_var($query);
   
		if($post_count > 0) {

			//pagination script
			$limit = get_option('posts_per_page');
			$current = max( 1, $_GET['paged'] );
			$total_pages = ceil($post_count / $limit);
			$start = $current * $limit - $limit;
			
			$query = "SELECT post_id, SUM(value) AS like_count, post_title FROM `{$wpdb->prefix}wti_like_post` L JOIN {$wpdb->prefix}posts P ";
			$query .= "ON L.post_id = P.ID WHERE value > 0 GROUP BY post_id ORDER BY like_count DESC, post_title LIMIT $start, $limit";
			$result = $wpdb->get_results($query);
			?>
			<form method="post" action="<?php echo get_bloginfo('url')?>/wp-admin/options-general.php?page=WtiLikePostAdminMenu" name="most_liked_posts" id="most_liked_posts">
				<div style="float:left">
					<input class="button-secondary" type="submit" name="resetall" id="resetall" onclick="return processAll()" value="<?php echo __('Reset All Counts', 'wti-like-post')?>" />
					<input class="button-secondary" type="submit" name="resetselected" id="resetselected" onclick="return processSelected()" value="<?php echo __('Reset Selected Counts', 'wti-like-post')?>" />
				</div>
				<div style="float:right">
					<div class="tablenav top">
						<div class="tablenav-pages">
							<span class="displaying-num"><?php echo $post_count?> <?php echo __('items', 'wti-like-post'); ?></span>
							<?php
							echo paginate_links(
										array(
											'current' 	=> $current,
											'prev_text'	=> '&laquo; ' . __('Prev', 'wti-like-post'),
											'next_text'    	=> __('Next', 'wti-like-post') . ' &raquo;',
											'base' 		=> @add_query_arg('paged','%#%'),
											'format'  	=> '?page=WtiLikePostAdminMenu',
											'total'   	=> $total_pages
										)
							);
							?>
						</div>
					</div>
				</div>
				<?php
				echo '<table cellspacing="0" class="wp-list-table widefat fixed likes">';
				echo '<thead><tr><th class="manage-column column-cb check-column" id="cb" scope="col">';
				echo '<input type="checkbox" id="checkall">';
				echo '</th><th>';
				_e('Post Title', 'wti-like-post');
				echo '</th><th>';
				_e('Like Count', 'wti-like-post');
				echo '</th><tr></thead>';
				echo '<tbody class="list:likes" id="the-list">';
				
				foreach ($result as $post) {
					$post_title = stripslashes($post->post_title);
					$permalink = get_permalink($post->post_id);
					$like_count = $post->like_count;
					
					echo '<tr>';
					echo '<th class="check-column" scope="row" align="center"><input type="checkbox" value="' . $post->post_id . '" class="administrator" id="post_id_' . $post->post_id . '" name="post_ids[]"></th>';
					echo '<td><a href="' . $permalink . '" title="' . $post_title.'" rel="nofollow" target="_blank">' . $post_title . '</a></td>';
					echo '<td>'.$like_count.'</td>';
					echo '</tr>';
				}
	 
				echo '</tbody></table>';
			?>
			</form>
			<?php
		} else {
			echo '<p>';
			echo __('No posts liked yet.', 'wti-like-post');
			echo '</p>';
		}
		?>
     </div>
</div>
<?php
}

#### WIDGET ####
require_once ABSPATH . 'wp-content/plugins/wti-like-post/wti_like_class.php';

#### FRONT-END VIEW ####
function GetWtiLikePost($arg = null) {
     global $wpdb;
     $post_id = get_the_ID();
     $wti_like_post = "";
	
     //get the posts ids where we do not need to show like functionality
     $allowed_posts = explode(",", get_option('wti_like_post_allowed_posts'));
     $excluded_posts = explode(",", get_option('wti_like_post_excluded_posts'));
	$excluded_categories = get_option('wti_like_post_excluded_categories');
	$excluded_sections = get_option('wti_like_post_excluded_sections');
	
	if(empty($excluded_categories)) {
		$excluded_categories = array();
	}
	
	if(empty($excluded_sections)) {
		$excluded_sections = array();
	}
     
	$title_text = get_option('wti_like_post_title_text');
	$category = get_the_category();
	$excluded = false;
	
	//checking for excluded section. if yes, then dont show the like/dislike option
	if((in_array('home', $excluded_sections) && is_home()) || (in_array('archive', $excluded_sections) && is_archive())) {
		return;
	}
	
	//checking for excluded categories
	foreach($category as $cat) {
		if(in_array($cat->cat_ID, $excluded_categories) && !in_array($post_id, $allowed_posts)) {
			$excluded = true;
		}
	}
	
	//if excluded category, then dont show the like/dislike option
	if($excluded) {
		return;
	}
	
	//check for title text. if empty then have the default value
     if(empty($title_text)) {
		$title_text_like = __('Like', 'wti-like-post');
		$title_text_unlike = __('Unlike', 'wti-like-post');
     } else {
		$title_text = explode('/', get_option('wti_like_post_title_text'));
		$title_text_like = $title_text[0];
		$title_text_unlike = $title_text[1];
     }
	
	//checking for excluded posts
     if(!in_array($post_id, $excluded_posts)) {		
		$like_count = GetWtiLikeCount($post_id);
		$unlike_count = GetWtiUnlikeCount($post_id);
		$msg = GetWtiVotedMessage($post_id);
		$alignment = ("left" == get_option('wti_like_post_alignment')) ? 'left' : 'right';
		$show_dislike = get_option('wti_like_post_show_dislike');
		$style = (get_option('wti_like_post_voting_style') == "") ? 'style1' : get_option('wti_like_post_voting_style');
		
		$wti_like_post .= "<div id='watch_action'>";
		$wti_like_post .= "<div id='watch_position' style='float:".$alignment."; '>";
		$wti_like_post .= "<div id='action_like' >".
							"<span class='like-".$post_id." like'><img title='".__($title_text_like, 'wti-like-post')."' id='like-".$post_id."' rel='like' class='lbg-$style jlk' src='".WP_PLUGIN_URL."/wti-like-post/images/pixel.gif'></span>".
							/*"<span class='like-".$post_id." like'><img title='".__($title_text_like, 'wti-like-post')."' id='like-".$post_id."' rel='like' class='jlk' src='".WP_PLUGIN_URL."/wti-like-post/images/thumb_up_".$style.".png'></span>".*/
							"<span id='lc-".$post_id."' class='lc'>".$like_count."</span>".
					   "</div>";
		
		if($show_dislike) {
			$wti_like_post .= "<div id='action_unlike' >".
								"<span class='unlike-".$post_id." unlike'><img title='".__($title_text_unlike, 'wti-like-post')."' id='unlike-".$post_id."' rel='unlike' class='unlbg-$style jlk' src='".WP_PLUGIN_URL."/wti-like-post/images/pixel.gif'></span>".
								/*"<span class='unlike-".$post_id." unlike'><img title='".__($title_text_unlike, 'wti-like-post')."' id='unlike-".$post_id."' rel='unlike' class='jlk' src='".WP_PLUGIN_URL."/wti-like-post/images/thumb_down_".$style.".png'></span>".*/
								"<span id='unlc-".$post_id."' class='unlc'>".$unlike_count."</span>".
						   "</div> ";
		}
		
		$wti_like_post .= "</div> ";
          $wti_like_post .= "<div id='status-".$post_id."' class='status' style='float:".$alignment."; '>&nbsp;&nbsp;" . $msg . "</div>";
		$wti_like_post .= "</div><div id='clear'></div>";
     }
     
     if ($arg == 'put') {
		return $wti_like_post;
     } else {
		echo $wti_like_post;
     }
}

function PutWtiLikePost($content) {
     $show_on_pages = false;
	
     if((is_page() && get_option('wti_like_post_show_on_pages')) || (!is_page())) {
		$show_on_pages = true;
     }
  
     if (!is_feed() && $show_on_pages) {     
	  $wti_like_post_content = GetWtiLikePost('put');
		$wti_like_post_position = get_option('wti_like_post_position');
		
		if ($wti_like_post_position == 'top') {
			$content = $wti_like_post_content . $content;
		} elseif ($wti_like_post_position == 'bottom') {
			$content = $content . $wti_like_post_content;
		} else {
			$content = $wti_like_post_content . $content . $wti_like_post_content;
		}
     }
     
     return $content;
}

add_filter('the_content', 'PutWtiLikePost');

function GetWtiLikeCount($post_id) {
     global $wpdb;
     $show_symbols = get_option('wti_like_post_show_symbols');
     $wti_like_count = $wpdb->get_var("SELECT SUM(value) FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND value >= 0");
	
     if(!$wti_like_count) {
		$wti_like_count = 0;
     } else {
		if($show_symbols) {
			$wti_like_count = "+" . $wti_like_count;
		} else {
			$wti_like_count = $wti_like_count;
		}
     }
	
     return $wti_like_count;
}

function GetWtiUnlikeCount($post_id) {
     global $wpdb;
     $show_symbols = get_option('wti_like_post_show_symbols');
     $wti_unlike_count = $wpdb->get_var("SELECT SUM(value) FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND value <= 0");
     
     if(!$wti_unlike_count) {
		$wti_unlike_count = 0;
     } else {
		if($show_symbols) {
		} else {
			$wti_unlike_count = str_replace('-', '', $wti_unlike_count);
		}
     }
     
     return $wti_unlike_count;
}

function GetWtiVotedMessage($post_id, $ip = null) {
     global $wpdb;
	
     if(null == $ip)
     {
		$ip = $_SERVER['REMOTE_ADDR'];
     }
     
     $wti_has_voted = $wpdb->get_var("SELECT COUNT(id) AS has_voted FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND ip = '$ip'");
     
     if($wti_has_voted > 0) {
		$wti_voted_message = get_option('wti_like_post_voted_message');
     }
     
     return $wti_voted_message;
}

function HasWtiAlreadyVoted($post_id, $ip = null) {
     global $wpdb;
     
     if(null == $ip)
     {
		$ip = $_SERVER['REMOTE_ADDR'];
     }
	
     $wti_has_voted = $wpdb->get_var("SELECT COUNT(id) AS has_voted FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND ip = '$ip'");
     
     return $wti_has_voted;
}

function GetWtiLastVotedDate($post_id, $ip = null) {
     global $wpdb;
     
     if(null == $ip)
     {
		$ip = $_SERVER['REMOTE_ADDR'];
     }
     
     $wti_has_voted = $wpdb->get_var("SELECT date_time FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND ip = '$ip'");

     return $wti_has_voted;
}

function GetWtiNextVoteDate($last_voted_date, $voting_period) {
     switch($voting_period) {
		case "1":
			$day = 1;
			break;
		case "2":
			$day = 2;
			break;
		case "3":
			$day = 3;
			break;
		case "7":
			$day = 7;
			break;
		case "14":
			$day = 14;
			break;
		case "21":
			$day = 21;
			break;
		case "1m":
			$month = 1;
			break;
		case "2m":
			$month = 2;
			break;
		case "3m":
			$month = 3;
			break;
		case "6m":
			$month = 6;
			break;
		case "1y":
			$year = 1;
	       break;
     }
	
     $last_strtotime = strtotime($last_voted_date);
     $next_strtotime = mktime(date('H', $last_strtotime), date('i', $last_strtotime), date('s', $last_strtotime),
			     date('m', $last_strtotime) + $month, date('d', $last_strtotime) + $day, date('Y', $last_strtotime) + $year);
     
     $next_voting_date = date('Y-m-d H:i:s', $next_strtotime);
     
     return $next_voting_date;
}

function GetWtiLastDate($voting_period) {
     switch($voting_period) {
		case "1":
			$day = 1;
			break;
		case "2":
			$day = 2;
			break;
		case "3":
			$day = 3;
			break;
		case "7":
			$day = 7;
			break;
		case "14":
			$day = 14;
			break;
		case "21":
			$day = 21;
			break;
		case "1m":
			$month = 1;
			break;
		case "2m":
			$month = 2;
			break;
		case "3m":
			$month = 3;
			break;
		case "6m":
			$month = 6;
			break;
		case "1y":
			$year = 1;
	       break;
     }
	
     $last_strtotime = strtotime(date('Y-m-d H:i:s'));
     $last_strtotime = mktime(date('H', $last_strtotime), date('i', $last_strtotime), date('s', $last_strtotime),
			     date('m', $last_strtotime) - $month, date('d', $last_strtotime) - $day, date('Y', $last_strtotime) - $year);
     
     $last_voting_date = date('Y-m-d H:i:s', $last_strtotime);
     
     return $last_voting_date;
}

add_shortcode('most_liked_posts', 'WtiMostLikedPostsShortcode');

function WtiMostLikedPostsShortcode($args) {
     global $wpdb;
     $most_liked_post = '';
     
     if($args['limit']) {
		$limit = $args['limit'];
     } else {
		$limit = 10;
     }
	
	if($args['time'] != 'all') {
		$last_date = GetWtiLastDate($args['time']);
		$where .= " AND date_time >= '$last_date'";
	}
     
     //getting the most liked posts
     $query = "SELECT post_id, SUM(value) AS like_count, post_title FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
     $query .= "WHERE L.post_id = P.ID AND post_status = 'publish' AND value > 0 $where GROUP BY post_id ORDER BY like_count DESC, post_title ASC LIMIT $limit";

     $posts = $wpdb->get_results($query);
 
     if(count($posts) > 0) {
		$most_liked_post .= '<table>';
		$most_liked_post .= '<tr>';
		$most_liked_post .= '<td>' . __('Title', 'wti-like-post') .'</td>';
		$most_liked_post .= '<td>' . __('Like Count', 'wti-like-post') .'</td>';
		$most_liked_post .= '</tr>';
	  
          foreach ($posts as $post) {
               $post_title = stripslashes($post->post_title);
               $permalink = get_permalink($post->post_id);
               $like_count = $post->like_count;
               
               $most_liked_post .= '<tr>';
			$most_liked_post .= '<td><a href="' . $permalink . '" title="' . $post_title.'" rel="nofollow">' . $post_title . '</a></td>';
               $most_liked_post .= '<td>' . $like_count . '</td>';
               $most_liked_post .= '</tr>';
          }
	  
		$most_liked_post .= '</table>';
     } else {
		$most_liked_post .= '<p>' . __('No posts liked yet.', 'wti-like-post') . '</p>';
     }
     
     return $most_liked_post;
}

add_shortcode('recently_liked_posts', 'WtiRecentlyLikedPostsShortcode');

function WtiRecentlyLikedPostsShortcode($args) {
     global $wpdb;
     $recently_liked_post = '';
     
     if($args['limit']) {
		$limit = $args['limit'];
     } else {
		$limit = 10;
     }
	
	$show_excluded_posts = get_option('wti_like_post_show_on_widget');
	$excluded_post_ids = explode(',', get_option('wti_like_post_excluded_posts'));
	
	if(!$show_excluded_posts && count($excluded_post_ids) > 0) {
		$where = "AND post_id NOT IN (" . get_option('wti_like_post_excluded_posts') . ")";
	}
	
	$recent_ids = $wpdb->get_col("SELECT DISTINCT(post_id) FROM `{$wpdb->prefix}wti_like_post` $where ORDER BY date_time DESC");
		
	if(count($recent_ids) > 0) {
		$where = "AND post_id IN(" . implode(",", $recent_ids) . ")";
	}
	
	//getting the most liked posts
	$query = "SELECT post_id, SUM(value) AS like_count, post_title FROM `{$wpdb->prefix}wti_like_post` L, {$wpdb->prefix}posts P ";
	$query .= "WHERE L.post_id = P.ID AND post_status = 'publish' AND value > 0 $where GROUP BY post_id ORDER BY date_time DESC LIMIT $limit";

	$posts = $wpdb->get_results($query);

     if(count($posts) > 0) {
		$recently_liked_post .= '<table>';
		$recently_liked_post .= '<tr>';
		$recently_liked_post .= '<td>' . __('Title', 'wti-like-post') .'</td>';
		$recently_liked_post .= '</tr>';
	  
          foreach ($posts as $post) {
               $post_title = stripslashes($post->post_title);
               $permalink = get_permalink($post->post_id);
               
               $recently_liked_post .= '<tr>';
			$recently_liked_post .= '<td><a href="' . $permalink . '" title="' . $post_title.'" rel="nofollow">' . $post_title . '</a></td>';
               $recently_liked_post .= '</tr>';
          }
	  
		$recently_liked_post .= '</table>';
     } else {
		$recently_liked_post .= '<p>' . __('No posts liked yet.', 'wti-like-post') . '</p>';
     }
     
     return $recently_liked_post;
}

function WtiLikePostEnqueueScripts() {
     if (get_option('wti_like_post_jquery') == '1') {
		wp_enqueue_script('WtiLikePost', WP_PLUGIN_URL.'/wti-like-post/js/wti_like_post.js', array('jquery'));	
     }
     else {
		wp_enqueue_script('WtiLikePost', WP_PLUGIN_URL.'/wti-like-post/js/wti_like_post.js');	
     }
}

function WtiLikePostAddHeaderLinks() {
     echo '<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL.'/wti-like-post/css/wti_like_post.css" media="screen" />'."\n";
     echo '<script type="text/javascript">';
     echo 'var blog_url = \''.get_bloginfo('wpurl').'\'';
     echo '</script>'."\n";
}

if(!is_admin()) {
     add_action('init', 'WtiLikePostEnqueueScripts');
     add_action('wp_head', 'WtiLikePostAddHeaderLinks');
}

//for adding metabox for posts/pages
add_action('admin_menu', 'wti_like_post_add_meta_box');
 
// Add meta box
function wti_like_post_add_meta_box() {
	//add the meta box for posts/pages
     add_meta_box('wti-like-post-meta-box', __('WTI Like Post Exclude Option', 'wti-like-post'), 'wti_like_post_show_meta_box', 'post', 'side', 'high');
     add_meta_box('wti-like-post-meta-box', __('WTI Like Post Exclude Option', 'wti-like-post'), 'wti_like_post_show_meta_box', 'page', 'side', 'high');
}

// Callback function to show fields in meta box
function wti_like_post_show_meta_box() {
     global $post;
         
     // Use nonce for verification
     echo '<input type="hidden" name="wti_like_post_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

     // get whether current post is excluded or not
	$excluded_posts = explode(',', get_option('wti_like_post_excluded_posts'));
	if(in_array($post->ID, $excluded_posts)) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}

     echo '<p>';    
     echo '<label for="wti_exclude_post"><input type="checkbox" name="wti_exclude_post" id="wti_exclude_post" value="1" ', $checked, ' /> ';
	echo __('Check to disable like/unlike functionality', 'wti-like-post');
     echo '</label>';
     echo '</p>';
}

add_action('save_post', 'wti_like_post_save_data');
    
// Save data from meta box
function wti_like_post_save_data($post_id) {    
     // verify nonce
     if (!wp_verify_nonce($_POST['wti_like_post_meta_box_nonce'], basename(__FILE__))) {
          return $post_id;
     }
    
     // check autosave
     if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
          return $post_id;
     }
    
     // check permissions
     if ('page' == $_POST['post_type']) {
          if (!current_user_can('edit_page', $post_id)) {
               return $post_id;
          }
     } elseif (!current_user_can('edit_post', $post_id)) {
          return $post_id;
     }
	
	//initialise the excluded posts array
	$excluded_posts = array();
	
	//check whether this post/page is to be excluded
	$exclude_post = $_POST['wti_exclude_post'];
	
	//get old excluded posts/pages
	if(strlen(get_option('wti_like_post_excluded_posts')) > 0) {
		$excluded_posts = explode(',', get_option('wti_like_post_excluded_posts'));
	}
	
	if($exclude_post == 1 && !in_array($_POST['ID'], $excluded_posts)) {
		//add this post/page id to the excluded list
		$excluded_posts[] = $_POST['ID'];
		
		if(!empty($excluded_posts)) {
			//since there are already excluded posts/pages, add this as a comma separated value
			update_option('wti_like_post_excluded_posts', implode(',', $excluded_posts));
		} else {
			//since there is no old excluded post/page, add this directly
			update_option('wti_like_post_excluded_posts', $_POST['ID']);
		}
	} else if(!$exclude_post){
		//check whether this id is already in the excluded list or not
		$key = array_search($_POST['ID'], $excluded_posts);
		
		if($key !== false) {
			//since this is already in the list, so exluded this
			unset($excluded_posts[$key]);
			
			//update the excluded posts list
			update_option('wti_like_post_excluded_posts', implode(',', $excluded_posts));
		}
	}
}
?>