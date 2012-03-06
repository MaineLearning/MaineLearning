<?php
$this->admin_scripts();

// Used for drag & drop / collapsing boxes.
wp_enqueue_style('dashboard');
wp_print_styles('dashboard');
wp_enqueue_script('dashboard');
//wp_print_scripts('dashboard');

wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );
?>

<script type="text/javascript">
	jQuery(document).ready( function() {
		
	});
</script>
<style type="text/css">
</style>

<div class="wrap">
	<?php $this->title( __('Server Information', 'it-l10n-backupbuddy') ); ?><br />
		
		<div class="postbox-container" style="width: 80%; min-width: 750px;">
			<div class="metabox-holder">
				<div class="meta-box-sortables">
					
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Server Configuration', 'it-l10n-backupbuddy');?>  <?php $this->video( 'XfZy-7DdbS0#t=0m14s', __('Server Configuration', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-server.php' );
							?>
						</div>
					</div>
					
					<?php
					// This page can take a bit to run.
					// Runs AFTER server information is displayed so we can view the default limits for the server.
					$this->set_greedy_script_limits();
					?>
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('File Permissions', 'it-l10n-backupbuddy');?>  <?php $this->video( 'XfZy-7DdbS0#t=0m40s', __('File Permissions', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-permissions.php' );
							?>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Site Size Map', 'it-l10n-backupbuddy');?>  <?php $this->video( 'XfZy-7DdbS0#t=1m7s', __('Site Size Map', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-sitesize.php' );
							?>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Database/table Size', 'it-l10n-backupbuddy');?>  <?php $this->video( 'XfZy-7DdbS0#t=2m34s', __('Database/table Size','it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-database.php' );
							?>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('WordPress Scheduled Actions (CRON)', 'it-l10n-backupbuddy');?> <?php $this->video( 'XfZy-7DdbS0#t=2m57s', __('Cron Overview', 'it-l10n-backupbuddy') ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-cron.php' );
							?>
						</div>
					</div>
					
					<?php
					/*
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="<?php _e('Click to toggle', 'it-l10n-backupbuddy');?>"><br /></div>
						<h3 class="hndle"><span><?php _e('Command Line Access', 'it-l10n-backupbuddy');?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-commandline.php' );
							?>
						</div>
					</div>
					*/
					?>
					
					
				</div>
			</div>
		</div>
</div>