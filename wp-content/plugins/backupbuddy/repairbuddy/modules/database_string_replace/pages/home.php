<?php
if ( !defined( 'PB_WP_LOADED' ) ) :
	global $pluginbuddy_repairbuddy;
	$pluginbuddy_repairbuddy->output_status( "WordPress is required for this functionality.  Please make sure RepairBuddy is placed at the root of your WordPress install.", true );
else:

global $pluginbuddy_repairbuddy;

if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'replace' ) ) {
	echo $pluginbuddy_repairbuddy->status_box( 'Beginning database replacement.');
	echo '<div id="pb_repairbuddy_working"><img src="repairbuddy/images/working.gif" title="Working. Please wait as this may take a moment."></div>';
	
	$bruteforce_tables = array();
	
	if ( $_POST['table_selection'] == 'all' ) {
		$result = mysql_query( 'SHOW TABLES' );
		while( $rs = mysql_fetch_row( $result ) ) {
			$bruteforce_tables[] = $rs[0];
		}
		mysql_free_result( $result ); // Free memory.
	} elseif ( $_POST['table_selection'] == 'prefix' ) {
		$result = mysql_query( "SHOW TABLES LIKE '{$_POST['table_prefix']}%'" );
		while( $rs = mysql_fetch_row( $result ) ) {
			$bruteforce_tables[] = $rs[0];
		}
		mysql_free_result( $result ); // Free memory.
	} elseif ( $_POST['table_selection'] == 'single_table' ) {
		$bruteforce_tables = array( $_POST['table'] );
	}
	
	if ( $_POST['trim_whitespace'] == 'true' ) {
		$needle = trim( $_POST['needle'] );
		$replacement = trim( $_POST['replacement'] );
	} else {
		$needle = $_POST['needle'];
		$replacement = $_POST['replacement'];
	}
	
	$db_replace_file = $this->get_plugin_dir( 'repairbuddy/lib/dbreplace/dbreplace.php', dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
	require_once( $db_replace_file  );
	$dbreplace = new pluginbuddy_dbreplace( $pluginbuddy_repairbuddy );
	foreach( $bruteforce_tables as $bruteforce_table ) {
		//echo 'force ' . $bruteforce_table . ' replace ' . $needle . ' with ' . $replacement . '<br>';
		$dbreplace->bruteforce_table( $bruteforce_table, $_POST['needle'], $_POST['replacement'] );
	}
	$pluginbuddy_repairbuddy->output_status( 'Database replacement complete.', false );
	
	echo '<script type="text/javascript">jQuery("#pb_repairbuddy_working").hide();</script>';
	echo '<br><div style="text-align: center; margin-top: 30px;"><span class="pb_fancy">Database replacement complete.</span></div>';
} else {
	?>
	
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery( '#pb_repairbuddy_table' ).change( function() {
				alert( jQuery( '#pb_repairbuddy_table' ).val() );
				jQuery.post( '<?php echo $pluginbuddy_repairbuddy->page_link( 'database_string_replace', 'get_table_rows' ); ?>', { table: jQuery( '#pb_repairbuddy_table' ).val() }, 
					function(data) {
						alert( 'yo' + data );
					}
				);
			} );
		} );
	</script>
	
	
	<?php
	
	$tables = array();
	$prefixes = array();
	
	$last_prefix = '';
	$result = mysql_query( 'SHOW TABLES' );
	while( $rs = mysql_fetch_row( $result ) ) {
		$tables[] = $rs[0];
		
		if ( preg_match( '/wp_([0-9]+_)*/i', $rs[0], $matches ) ) {
			$prefixes[] = $matches[0];
		}
	}
	mysql_free_result( $result ); // Free memory.
	$prefixes = array_unique( $prefixes );
	natsort( $prefixes );
	?>
	<form action="<?php echo $pluginbuddy_repairbuddy->page_link( 'database_string_replace', 'home' ); ?>" method="post">
		<input type="hidden" name="action" value="replace">
		
		<h3>Replace</h3>
		<textarea name="needle" style="width: 100%;"></textarea>
		<br>
		
		<h3>With</h3>
		<textarea name="replacement" style="width: 100%;"></textarea>
		<br>
		<span class="light">
			<label for="trim_whitespace"><input id="trim_whitespace" type="checkbox" name="trim_whitespace" value="true">Automatically trim whitespace from front and end of text.</label>
		</span>
		
		<h3>In table(s)</h3>
		<label for="table_selection_all"><input id="table_selection_all" <?php checked( true, true ); ?> type="radio" name="table_selection" value="all"> all tables</label>
		<label for="table_selection_prefix"><input id="table_selection_prefix" type="radio" name="table_selection" value="prefix"> with prefix:</label>
		<select name="table_prefix" id="table_selection_prefix" onclick="jQuery('#table_selection_prefix').click();">
			<?php
			foreach( $prefixes as $prefix ) {
				echo '<option value="' . $prefix . '">' . $prefix . '</option>';
			}
			?>
		</select>
		<label for="table_selection_table"><input id="table_selection_table" type="radio" name="table_selection" value="single_table"> single:</label>
		<select name="table" id="table_selection_table" onclick="jQuery('#table_selection_table').click();">
			<?php
			foreach( $tables as $table ) {
				echo '<option value="' . $table . '">' . $table . '</option>';
			}
			?>
		</select>
		<br><br><br>
		
		<p style="text-align: center;"><input type="submit" name="submit" value="Perform Replacement &raquo;" class="button" /></p>
		
	</form>
<?php } //end if ?>
<?php endif; ?>