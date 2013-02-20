<?php
if( !defined('ABSPATH') ) die('Security check');

 global $template_mode;
if ($template_mode=='wprc-uninstall-step-1')
{
?>
<!-- Uninstall WPRC -->
<form method="post" action="">
<div class="wrap">
	<div id="icon-uninstall-installer" class="icon32"><br></div>
	<h2><?php _e('Uninstall Installer', 'installer'); ?></h2>
	<p style="color: red">
		<strong><?php _e('WARNING:', 'installer'); ?></strong><br />
		<?php _e('Once uninstalled, this cannot be undone. All stored data related to Installer will be deleted. You should use a Database Backup plugin of WordPress to back up all the data first.', 'installer'); ?>
	</p>
	<p style="color: red">
		<strong><?php _e('The following WordPress Options/Tables will be DELETED:', 'installer'); ?></strong><br />
	</p>
	<table class="widefat">
		<thead>
			<tr>
				<th><strong><?php _e('WordPress Options', 'installer'); ?></strong></th>
				<th><strong><?php _e('WordPress Tables', 'installer'); ?></th>
			</tr>
		</thead>
		<tr>
			<td valign="top">
				<ol>
				<?php
					foreach($wprc_db_options as $option) {
						echo '<li>'.$option->option_name.'</li>'."\n";
					}
				?>
				</ol>
			</td>
			<td valign="top" class="alternate">
				<ol>
				<?php
					foreach($wprc_db_tables as $table) {
						echo '<li>'.$table[0].'</li>'."\n";
					}
				?>
				</ol>
			</td>
		</tr>
	</table>
	<p>&nbsp;</p>
	<p style="text-align: center;">
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('installer-uninstall-form'); ?>" />
		<input type="checkbox" name="wprc_uninstall_yes" value="yes" />&nbsp;<?php _e('Yes', 'installer'); ?><br /><br />
		<input type="submit" name="wprc_uninstall_action" value="<?php _e('UNINSTALL Installer', 'installer'); ?>" class="button" onclick="return confirm('<?php _e('You Are About To Uninstall Installer From WordPress.\nThis Action Is Not Reversible.\n\n Choose [Cancel] To Stop, [OK] To Uninstall.', 'installer'); ?>')" />
	</p>
</div>
</form>
<?php } elseif ($template_mode=='wprc-uninstall-step-2') { ?>

<div class="wrap">
	<h2><?php _e('Uninstall Installer', 'installer'); ?></h2>
	<p style="color: red">
		<strong><?php _e('The following WordPress Options/Tables are DELETED:', 'installer'); ?></strong><br />
	</p>
	<table class="widefat">
		<thead>
			<tr>
				<th><strong><?php _e('WordPress Options', 'installer'); ?></strong></th>
				<th><strong><?php _e('WordPress Tables', 'installer'); ?></th>
			</tr>
		</thead>
		<tr>
			<td valign="top">
				<ol>
				<?php
					foreach($results_db_options as $option=>$result) {
						if ($result==true)
							echo '<li>'.$option.' <strong>'.__('Deleted', 'installer').'</strong></li>'."\n";
						else
							echo '<li>'.$option.' <strong style="color:red">'.__('Not Deleted', 'installer').'</strong></li>'."\n";
					}
				?>
				</ol>
			</td>
			<td valign="top" class="alternate">
				<ol>
				<?php
					foreach($results_db_tables as $table=>$result) {
						if ($result==true)
							echo '<li>'.$table.' <strong>'.__('Deleted', 'installer').'</strong></li>'."\n";
						else
							echo '<li>'.$table.' <strong style="color:red">'.__('Not Deleted', 'installer').'</strong></li>'."\n";
					}
				?>
				</ol>
			</td>
		</tr>
	</table>
	<p style="text-align: center;">
	<strong><?php printf(__('<a href="%s">Click Here</a> To Finish The Uninstallation And Installer Will Be Deactivated Automatically.', 'installer'), admin_url()); ?></strong>
	</p>
</div>
<?php } ?>