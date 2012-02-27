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
	<?php $this->title( 'Server Information' ); ?><br />
		
		<div class="postbox-container" style="width: 80%; min-width: 750px;">
			<div class="metabox-holder">
				<div class="meta-box-sortables">
					
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span>Server Configuration  <?php $this->video( 'XfZy-7DdbS0#14', 'Server Configuration' ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-server.php' );
							?>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span>File Permissions  <?php $this->video( 'XfZy-7DdbS0#40', 'File Permissions' ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-permissions.php' );
							?>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span>Site Size Map  <?php $this->video( 'XfZy-7DdbS0#67', 'Site Size Map' ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-sitesize.php' );
							?>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span>Database/table Size  <?php $this->video( 'XfZy-7DdbS0#154', 'Database/table Size' ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-database.php' );
							?>
						</div>
					</div>
					
					
					<div id="breadcrumbslike" class="postbox">
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class="hndle"><span>WordPress Scheduled Actions (CRON) <?php $this->video( 'XfZy-7DdbS0#177', 'Cron Overview' ); ?></span></h3>
						<div class="inside">
							<?php
							require_once( 'view_tools-cron.php' );
							?>
						</div>
					</div>
					
					
				</div>
			</div>
		</div>
</div>
