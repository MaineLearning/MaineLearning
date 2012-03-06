<?php
if ( substr( $_POST['submit'], 0, 1 ) == 'D' ) { // DECRYPT
	$dat_encrypted = trim( $_POST['dat_encrypted'] );
	
	$backupdata = trim( $_POST['dat_encrypted'] ); // Trim and surrounding whitespace.
	if ( substr( $backupdata, 0, 1 ) == '<' ) {
		$second_line_pos = strpos( $backupdata, "\n" ) + 1; // Skip first line.
	}
	$backupdata = substr( $backupdata, $second_line_pos );
	$dat_decrypted = base64_decode( $backupdata );
	if ( is_serialized( $dat_decrypted ) ) {
		$dat_decrypted = unserialize( $dat_decrypted ); // Decode back into an array.
	} else { // invalid content.
		$dat_decrypted = "Error #2482. Unable to extract BackupBuddy DAT content from provided text.\n\nPlease verify you pasted the entire file and try again.";
	}
}
/*	
} elseif ( substr( $_POST['submit'], 0, 1 ) == 'E' ) { // ENCRYPT
	$dat_encrypted = trim( $_POST['dat_encrypted'] );
	$dat_decrypted = trim( $_POST['dat_decrypted'] );
}
*/
global $pluginbuddy_repairbuddy;
?>
<form action="<?php echo $pluginbuddy_repairbuddy->page_link( 'backupbuddy_dat', 'home' ); ?>" method="post">
	
	&nbsp;<b>Encrypted DAT File:</b>
	<textarea name="dat_encrypted" style="width: 100%; min-height: 200px;"><?php echo $dat_encrypted; ?></textarea><br><br>
	&nbsp;<b>Decrypted DAT File:</b>
	<textarea name="dat_decrypted" style="width: 100%; min-height: 200px;"><?php print_r( $dat_decrypted ); ?></textarea><br>
	
	<p style="text-align: center;">
		<input type="submit" name="submit" value="Decrypt DAT Content &raquo;" class="button" />
		<?php
		/*
		&nbsp;&nbsp;&nbsp;
		<input type="submit" name="submit" value="Encrypt DAT Content &raquo;" class="button-secondary" />
		*/
		?>
	</p>
</form>