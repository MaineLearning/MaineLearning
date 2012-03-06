<?php
if ( !isset( $parent_class ) ) {
	$parent_class = $this;
}
if ( defined( 'pluginbuddy_importbuddy' ) ) {
	//$parent_class->admin_scripts();
}
?>

<table class="widefat">
	<thead>
		<tr class="thead">
			<?php
				echo '<th>', __('Database Table', 'it-l10n-backupbuddy'),'</th>',
					 '<th>', __('Status', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Engine', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Last Updated', 'it-l10n-backupbuddy'),'</th>',
					 '<th>', __('Rows', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Size', 'it-l10n-backupbuddy'), '</th>';
			?>
		</tr>
	</thead>
	<tfoot>
		<tr class="thead">
			<?php
				echo '<th>', __('Database Table', 'it-l10n-backupbuddy'),'</th>',
					 '<th>', __('Status', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Engine', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Last Updated', 'it-l10n-backupbuddy'),'</th>',
					 '<th>', __('Rows', 'it-l10n-backupbuddy'), '</th>',
					 '<th>', __('Size', 'it-l10n-backupbuddy'), '</th>';
			?>
		</tr>
	</tfoot>
	<tbody>
		<?php
		$total_size = 0;
		$total_rows = 0;
		$result = mysql_query("SHOW TABLE STATUS");
		while( $rs = mysql_fetch_array( $result ) ) {
			echo '<tr class="entry-row alternate">';
			echo '	<td>' . $rs['Name'] . '</td>';
			
			$resultb = mysql_query("CHECK TABLE `{$rs['Name']}`");
			while( $rsb = mysql_fetch_array( $resultb ) ) {
				if ( $rsb['Msg_type'] == 'status' ) {
					echo '<td>' . $rsb['Msg_text'];
					echo '</td>';
				}
			}
			mysql_free_result( $resultb );
			
			echo '	<td>' . $rs['Engine'] . '</td>';
			echo '	<td>' . $rs['Update_time'] . '</td>';
			echo '	<td>' . $rs['Rows'] . '</td>';
			$size = ( $rs['Data_length'] + $rs['Index_length'] );
			$total_size += $size;
			$total_rows += $rs['Rows'];
			echo '	<td>' . $parent_class->format_size( $size ) . '</td>';
			echo '</tr>';
		}
		echo '<tr class="entry-row alternate">';
		echo '	<td>&nbsp;</td>';
		echo '	<td>&nbsp;</td>';
		echo '	<td>&nbsp;</td>';
		echo '<td><b>',__('TOTALS','it-l10n-backupbuddy'),':</b></td>';
		echo '<td><b>' . $total_rows . '</b></td>';
		echo '<td><b>' . $parent_class->format_size( $total_size ) . '</b></td>';
		echo '</tr>';
		
		unset( $total_size );
		unset( $total_rows );
		mysql_free_result( $result );
		?>
	</tbody>
</table>