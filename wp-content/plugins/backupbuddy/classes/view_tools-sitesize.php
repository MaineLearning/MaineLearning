<?php
wp_enqueue_script( $this->_var . '_icicle', $this->_pluginURL . '/js/icicle.js' );
wp_print_scripts( $this->_var . '_icicle' );
wp_enqueue_script( $this->_var . '_icicle_setup', $this->_pluginURL . '/js/icicle_setup.js' );
wp_print_scripts( $this->_var . '_icicle_setup' );
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#pb_iciclelaunch').click(function(e) {
			jQuery('#pb_infovis_container').slideDown();
			jQuery.post( '<?php echo admin_url('admin-ajax.php').'?action=' . $this->_var . '_icicletree'; ?>', { action: "none" }, 
				function( data ) {
					jQuery('#infovis').html('');
					icicle_init( data );
				}
			);
		});
		
	});
</script>

<h4 style="margin: 0;"><?php _e('Directory Size Map', 'it-l10n-backupbuddy');?></h4>
<?php _e('This option displays an interactive graphical representation of directories and the corresponding size of all contents within, including subdirectories.
This is useful for finding where space is being used. Directory boxes are scaled based on size. Click on a directory box to move around. Note that this
is a CPU intensive process and may take a while to load and even time out on some servers. Slower computers may have trouble navigating the interactive map.', 'it-l10n-backupbuddy');
?>
<p><a id="pb_iciclelaunch" class="button secondary-button" style="margin-top: 3px;"><?php _e('Display Directory Size Map', 'it-l10n-backupbuddy');?></a></p>

<link type="text/css" href="<?php echo $this->_pluginURL; ?>/css/jit_base.css" rel="stylesheet" />
<link type="text/css" href="<?php echo $this->_pluginURL; ?>/css/jit_icicle.css" rel="stylesheet" />


<div style="display: none;" id="pb_infovis_container">
	<div style="background: #1A1A1A;">
		<div id="infovis">
			<br /><br />
			<div style="margin: 30px;">
				<h4 style="color: #FFFFFF;"><img src="<?php echo $this->_pluginURL; ?>/images/loading_large_darkbg.gif" style="vertical-align: -9px;" /> <?php _e('Loading ... Please wait ...', 'it-l10n-backupbuddy');?></h4>
			</div>
		</div>
	</div>
	
	<label for="s-orientation"><?php _e('Orientation', 'it-l10n-backupbuddy');?>: </label>
	<select name="s-orientation" id="s-orientation">
		<option value="h" selected><?php _e('horizontal', 'it-l10n-backupbuddy');?></option>
		<option value="v"><?php _e('vertical', 'it-l10n-backupbuddy');?></option>
	</select>
	
	<label for="i-levels-to-show"><?php _e('Max levels', 'it-l10n-backupbuddy');?>: </label>
	<select  id="i-levels-to-show" name="i-levels-to-show" style="width: 50px">
		<option>all</option>
		<option>1</option>
		<option>2</option>
		<option selected="selected">3</option>
		<option>4</option>
		<option>5</option>
	</select>

	<a id="update" class="theme button white"><?php _e('Go Up', 'it-l10n-backupbuddy');?></a>
</div>



<?php
$dir_array = array();
$icicle_array = array();
$time_start = microtime(true);

//echo '<pre>' . $this->build_icicle( ABSPATH, ABSPATH, '' ) . '</pre>';

echo '<h4>',__('Directory Size Listing', 'it-l10n-backupbuddy'),'</h4>';

if ( empty( $_GET['site_size'] ) ) {
	echo __('This option displays a comprehensive listing of directories and the corresponding size of all contents within, including subdirectories.  This is useful for finding where space is being used. Note that this is a CPU intensive process and may take a while to load and even time out on some servers.', 'it-l10n-backupbuddy');
	echo '<br /><br /><a href="' . $this->_selfLink . '-tools&site_size=true" class="button secondary-button" style="margin-top: 3px;">', __('Display Directory Size Listing', 'it-l10n-backupbuddy'),'</a>';
} else {
	$total_size = $this->_parent->dir_size( ABSPATH, ABSPATH, $dir_array, $icicle_array );
	echo __('Time taken', 'it-l10n-backupbuddy') , ': ' . ( microtime( true ) - $time_start ) . ' ',__('seconds', 'it-l10n-backupbuddy'),'.';
	
	arsort( $dir_array );
	
	echo '<p><b>',__('Total size of site', 'it-l10n-backupbuddy'),':</b> ' . $this->_parent->format_size( $total_size ) . '</p>';
	?>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<?php
					echo '<th>', __('Directory', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Size with Children', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<?php
					echo '<th>', __('Directory', 'it-l10n-backupbuddy'), '</th>',
						 '<th>', __('Size with Children', 'it-l10n-backupbuddy'), '</th>';
				?>
			</tr>
		</tfoot>
		<tbody>

	<?php
	$item_count = 0;
	foreach ( $dir_array as $id => $item ) {
		$item_count++;
		if ( $item_count > 100 ) {
			flush();
			$item_count = 0;
		}
		echo '<tr><td>' . $id . '/</td><td>' . number_format( $item, 3 ) . ' MB</td></tr>';
	}
	echo '</tbody>';
	echo '</table><br />';
	echo '<p><b>', __('Total size of site', 'it-l10n-backupbuddy'), ':</b> ' . $this->_parent->format_size( $total_size ) . '</p>';
	echo '<br />';
	echo '</div>';
}
?>
