<?php
if ( !defined( 'PB_WP_LOADED' ) ) :
	global $pluginbuddy_repairbuddy;
	$pluginbuddy_repairbuddy->output_status( "WordPress is required for this functionality.  Please make sure RepairBuddy is placed at the root of your WordPress install.", true );
else:
?>
<h3>WordPress Options</h3>

<?php
if ( $_POST['action'] == 'update_options' ) {
	$save_options = explode( ',', $_POST['page_options'] );
	
	foreach( $save_options as $save_option ) {
		update_option( $save_option, $_POST[$save_option] );
	}
	global $pluginbuddy_repairbuddy;
	$pluginbuddy_repairbuddy->output_status( 'Options saved' );
}
global $pluginbuddy_repairbuddy;
?>

<form action="<?php echo $pluginbuddy_repairbuddy->page_link( 'options_editor', 'home' ); ?>" method="post">
	<input type="hidden" name="action" value="update_options">
	
<?php
echo '<table>';

global $wpdb;
$options = $wpdb->get_results( "SELECT * FROM $wpdb->options ORDER BY option_name" );

foreach ( (array) $options as $option ) :
	$disabled = false;
	if ( $option->option_name == '' )
		continue;
	if ( is_serialized( $option->option_value ) ) {
		if ( is_serialized_string( $option->option_value ) ) {
			// this is a serialized string, so we should display it
			$value = maybe_unserialize( $option->option_value );
			$options_to_update[] = $option->option_name;
			$class = 'all-options';
		} else {
			/*
			$value = 'SERIALIZED DATA';
			$disabled = true;
			$class = 'all-options disabled';
			*/
			
			$value = maybe_unserialize( $option->option_value );
			$value = print_r( $value, true );
			
			$disabled = true;
			$class = 'all-options disabled';
		}
	} else {
		$value = $option->option_value;
		$options_to_update[] = $option->option_name;
		$class = 'all-options';
	}
	$name = esc_attr( $option->option_name );
	echo "
<tr>
	<td style='font-size: 10px;' valign=\"top\">" . esc_html( $option->option_name ) . "</td>
<td>";
	if ( strpos( $value, "\n" ) !== false ) {
		echo "<textarea class='$class' name='$name' id='$name' cols='30' rows='5' " . disabled( $disabled, true, false );
		if ( $disabled == true ) {
			echo 'wrap="off" ';
		}
		echo ">" . esc_textarea( $value ) . "</textarea>";
	} else {
		echo "<input class='regular-text $class' type='text' name='$name' id='$name' value='" . esc_attr( $value ) . "'" . disabled( $disabled, true, false ) . " />";
	}
	echo "</td>
</tr>";
endforeach;
?>
</table>

	<br>
	<input type="hidden" name="page_options" value="<?php echo esc_attr( implode( ',', $options_to_update ) ); ?>" />
	<p style="text-align: center;"><input type="submit" name="submit" value="Save Changes" class="button" /></p>

</form>
<?php endif; ?>