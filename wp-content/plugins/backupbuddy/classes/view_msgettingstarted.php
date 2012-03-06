<?php
update_option( 'dustins_option_mainsitedash', 'used update_option' );
update_site_option( 'dustins_site_option_mainsitedash', 'used update_site_option' );


if ( isset( $_GET['pb_backupbuddy_alt_cron'] ) ) {
	echo __('Alternate Cron Should Run Now', 'it-l10n-backupbuddy');
} else {

	$this->admin_scripts();
	
	// Needed for fancy boxes...
	wp_enqueue_style('dashboard');
	wp_print_styles('dashboard');
	wp_enqueue_script('dashboard');
	wp_print_scripts('dashboard');

	// If they clicked the button to reset plugin defaults...
	if (!empty($_POST['reset_defaults'])) {
		$this->_options = $this->_parent->_defaults;
		$this->_parent->activate();
		$this->_parent->save();
		$this->_parent->alert( __('Plugin settings have been reset to defaults.', 'it-l10n-backupbuddy') );
	}
	if (!empty($_POST['reset_log'])) {
		if ( file_exists ( ABSPATH . '/wp-content/uploads/pluginbuddy_backupbuddy.txt' ) ) {
			unlink( ABSPATH . '/wp-content/uploads/pluginbuddy_backupbuddy.txt' );
		}
		$this->_parent->alert( __('Plugin log has been cleared.', 'it-l10n-backupbuddy') );
	}
	?>

	<div class="wrap">
		<div class="postbox-container" style="width:70%;">
			<?php
			$this->title( _x('Getting Started with BackupBuddy v', 'v for version', 'it-l10n-backupbuddy') . $this->_parent->_version );
			?>
			<p>For BackupBuddy Multisite documentation, please visit the <a href='http://ithemes.com/codex/page/BackupBuddy_Multisite'>BackupBuddy Multisite Codex</a>.</p>
			<?php
			
			echo '<br />';
			
			echo __("You are viewing BackupBuddy on a WordPress Multisite Installation.  When viewing BackupBuddy on a single site, you can export the site out of a network and use BackupBuddy to bring the site into an existing network or create a stand-alone site.", 'it-l10n-backupbuddy');
			
			echo '<br /><br />';
			
			?>
			<p>
				<h3><?php _e('Export', 'it-l10n-backupbuddy');?></h3>
				<ol>
					<li type="disc">
					<?php
						_e( 'Exporting creates a standalone BackupBuddy file that you can migrate or bring into an existing network using BackupBuddy', 'it-l10n-backupbuddy' );
					?>
					</li>
					<li type="disc">
						<?php
							_e( 'Since a network has network-activated and MU-Plugins, BackupBuddy export will ask you which of these files you will want to bring over', 'it-l10n-backupbuddy' );
						?>	
					</li>
					<li type="disc"><?php _e( 'The Export feature will try to bring over all applicable users, plugins, themes, and files.  When running ImportBuddy, you will be asked to create a new administrator user so you can log in.', 'it-l10n-backupbuddy' ); ?></li>
				</ol>
				
			</p>
			
			<br /><br />
			
			<br />
			
			<center>
				<div style="background: #D4E4EE; -webkit-border-radius: 8px; -moz-border-radius: 8px; border-radius: 8px; padding: 15px; text-align: center; width: 395px; line-height: 1.6em;">
					<a title="<?php _e('Click to visit the PluginBuddy Knowledge Base', 'it-l10n-backupbuddy');?>" href="http://ithemes.com/codex/page/BackupBuddy" style="text-decoration: none; color: #000000;">
						<img src="<?php echo $this->_pluginURL , '/images/kb.png';?>" alt="" width="70" height="70" style="float: right;" /><?php _e('For documentation &#038; help visit our', 'it-l10n-backupbuddy');?><br />
						<span style="font-size: 2.8em;"><?php _e('Knowledge Base', 'it-l10n-backupbuddy');?></span>
						<br /><?php _e('Walkthroughs &middot; Tutorials &middot;  Technical Details', 'it-l10n-backupbuddy');?>
					</a>
				</div>
			</center>
			
			<br style="clear: both;" />
			
			
			
			<h3><?php _e('Version History', 'it-l10n-backupbuddy');?></h3>
			<textarea rows="7" cols="70"><?php readfile( $this->_parent->_pluginPath . '/history.txt' ); ?></textarea>
			<br /><br />
			
				
			<br /><br /><br />
			<a href="http://pluginbuddy.com" style="text-decoration: none;"><img src="<?php echo $this->_pluginURL; ?>/images/pluginbuddy.png" style="vertical-align: -3px;" /> PluginBuddy.com</a><br /><br />
		</div>
		<div class="postbox-container" style="width:20%; margin-top: 35px; margin-left: 15px;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span><?php _e('Things to do...','it-l10n-backupbuddy');?></span></h3>
						<div class="inside">
							<ul class="pluginbuddy-nodecor">
								<li>- <a href="http://twitter.com/home?status=<?php echo urlencode(__('Check out this awesome plugin', 'it-l10n-backupbuddy') . ' ' . $this->_parent->_name . '! ' . $this->_parent->_url . ' @pluginbuddy'); ?>" title="<?php _e('Share on Twitter', 'it-l10n-backupbuddy');?>" onClick="window.open(jQuery(this).attr('href'),'ithemes_popup','toolbar=0,status=0,width=820,height=500,scrollbars=1'); return false;"><?php _e('Tweet about this plugin.', 'it-l10n-backupbuddy');?></a></li>
								<li>- <a href="http://pluginbuddy.com/purchase/"><?php _e('Check out PluginBuddy plugins.', 'it-l10n-backupbuddy');?></a></li>
								<li>- <a href="http://ithemes.com/purchase/"><?php _e('Check out iThemes themes.', 'it-l10n-backupbuddy');?></a></li>
								<li>- <a href="http://secure.hostgator.com/cgi-bin/affiliates/clickthru.cgi?id=ithemes"><?php _e('Get HostGator web hosting.','it-l10n-backupbuddy');?></a></li>
							</ul>
						</div>
					</div>

					<div id="breadcrumsnews" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Latest news from PluginBuddy', 'it-l10n-backupbuddy');?></span></h3>
						<div class="inside">
							<p style="font-weight: bold;">PluginBuddy.com</p>
							<?php $this->get_feed( 'http://pluginbuddy.com/feed/', 5 );  ?>
							<p style="font-weight: bold;">Twitter @pluginbuddy</p>
							<?php
							$twit_append = '<li>&nbsp;</li>';
							$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/twitter.png" style="vertical-align: -3px;" /> <a href="http://twitter.com/pluginbuddy/">';
							$twit_append .= __('Follow @pluginbuddy on Twitter.', 'it-l10n-backupbuddy') . '</a></li>';
							$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/feed.png" style="vertical-align: -3px;" /> <a href="http://pluginbuddy.com/feed/">';
							$twit_append .= __('Subscribe to RSS news feed.', 'it-l10n-backupbuddy') . '</a></li>';
							$twit_append .= '<li><img src="'.$this->_pluginURL.'/images/email.png" style="vertical-align: -3px;" /> <a href="http://pluginbuddy.com/subscribe/">';
							$twit_append .= __('Subscribe to Email Newsletter.', 'it-l10n-backupbuddy') . '</a></li>';
							$this->get_feed( 'http://twitter.com/statuses/user_timeline/108700480.rss', 5, $twit_append, 'pluginbuddy: ' );
							?>
						</div>
					</div>
					
					<div id="breadcrumbssupport" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Need support?', 'it-l10n-backupbuddy');?></span></h3>
						<div class="inside">
							<?php echo '<p>', __('See our <a href="http://pluginbuddy.com/tutorials/">tutorials & videos</a> or visit our <a href="http://pluginbuddy.com/support/">support forum</a> for additional information and help.', 'it-l10n-backupbuddy'), '</p>';?>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
<?php } ?>
