<table class="widefat">
	<thead>
		<tr class="thead">
			<th>Database Table</th>
			<th>Engine</th>
			<th>Last Updated</th>
			<th>Rows</th>
			<th>Size</th>
		</tr>
	</thead>
	<tfoot>
		<tr class="thead">
			<th>Database Table</th>
			<th>Engine</th>
			<th>Last Updated</th>
			<th>Rows</th>
			<th>Size</th>
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
			echo '	<td>' . $rs['Engine'] . '</td>';
			echo '	<td>' . $rs['Update_time'] . '</td>';
			echo '	<td>' . $rs['Rows'] . '</td>';
			$size = ( $rs['Data_length'] + $rs['Index_length'] );
			$total_size += $size;
			$total_rows += $rs['Rows'];
			echo '	<td>' . $this->_parent->format_size( $size ) . '</td>';
			echo '</tr>';
		}
		echo '<tr class="entry-row alternate">';
		echo '	<td>&nbsp;</td>';
		echo '	<td>&nbsp;</td>';
		echo '<td><b>TOTALS:</b></td>';
		echo '<td><b>' . $total_rows . '</b></td>';
		echo '<td><b>' . $this->_parent->format_size( $total_size ) . '</b></td>';
		echo '</tr>';
		
		unset( $total_size );
		unset( $total_rows );
		mysql_free_result( $result );
		?>
	</tbody>
</table>