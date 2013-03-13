<link rel='shortcut icon' href="<?php echo plugins_url() . '/bebop/core/resources/images/bebop_icon.png';?>">
<?php include_once( WP_PLUGIN_DIR . '/bebop/core/templates/admin/bebop-admin-menu.php' ); ?>
<div id='bebop_admin_container'>
	<div class='postbox center_margin margin-bottom_22px'>
		<h3><?php _e( 'Bebop Settings', 'bebop' ); ?></h3>
		<div class='inside'>
			<p><?php _e( 'General settings can be modified here.', 'bebop' ); ?></p>
		</div>
	</div>
	<form class='bebop_admin_form' method='post'>
		<fieldset>
			<span class='header'><?php _e( 'Bebop Settings', 'bebop' ); ?></span>
			<p><?php _e( 'The WordPress cron runs the import script for the given timeframe. The default is set to 10 minutes (600 seconds). The only issue with the WordPress cron is that it can only be activated when a page is accessed. So, if no-one was to visit the site for a long period of time,
			the importers might miss some content items. You should therefore use the WordPress cron only if you cannot use a traditional cron. Do not use both together.', 'bebop' ); ?></p>
			<p><?php _e( 'To use the traditional cron, add the following cron command to your webhosting cron lists, setting a timeframe of your choice.', 'bebop' ); ?></p>
			<p><?php _e( 'If you use a traditional cron, set the WordPress Cron time to "0".', 'bebop' ); ?></p>
			
			<p><?php _e( 'As of version 1.1, a secondary cron was introduced. This allows the major import scripts to run at less frequency, while still allowing new users and new feeds to import data within 20 seconds.
				Therefore you should not need to run the cron any less than 10 minutes (600 seconds).', 'bebop' ); ?></p>
			<p><?php _e( 'As of version 1.1, a cron can be forced to run at the click of a button. This can be used to test whether content is being imported and does not affect the WordPress cron.', 'bebop' ); ?></p>
			<p><?php _e( 'As of version 1.2, the content verification has been moved from the general settings, and has been added to each extension.', 'bebop' ); ?></p>
			
			<label for='bebop_general_crontime'><?php _e( 'WordPress Cron time (in seconds):', 'bebop' ); ?></label>
			<input type='text' id='bebop_general_crontime' name='bebop_general_crontime' value='<?php echo bebop_tables::get_option_value( 'bebop_general_crontime' ); ?>' size='10'><br><br>
			
			<label for='traditional_cron'><?php _e( 'Traditional Cron:', 'bebop' ); ?></label>
			<input type='text' id='traditional_cron' value="wget <?php echo plugins_url() . '/bebop/import.php -O /dev/null -q'?>" size='75' READONLY><br><br>
			
			<label><?php _e( 'Force Main Cron:', 'bebop' ); ?></label>
			<a class="button-secondary" target="_blank" href="<?php echo plugins_url(); ?>/bebop/import.php"><?php _e( 'Main Import', 'bebop' ); ?></a> <?php _e( '(all users, all feeds)', 'bebop' ); ?><br><br>
			<label><?php _e( 'Force Secondary Cron:', 'bebop' ); ?></label>
			<a class="button-secondary" target="_blank" href="<?php echo plugins_url(); ?>/bebop/secondary_import.php"><?php _e( 'Secondary Import', 'bebop' ); ?></a> <?php _e( '(new users/new feeds)', 'bebop' ); ?><br><br>
			<div class="clear"></div>
		</fieldset>
		<?php wp_nonce_field( 'bebop_admin_settings' ); ?>
		<input class='button-primary' type='submit' id='submit' name='submit' value='<?php _e( 'Save Changes', 'bebop' ); ?>'>
	</form>
	<div class="clear"></div>
</div>
<!-- end bebop_admin_container -->
