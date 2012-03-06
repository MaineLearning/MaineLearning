<?php
// EXPECTS $dat_file to point to the backupbuddy_dat.php file.

$this->log( 'STARTING Loading backup dat file....' );
if ( file_exists( $dat_file ) ) {
	$backupdata = file_get_contents( $dat_file );
} else { // Missing.
	$this->alert( 'Error #9003: BackupBuddy data file (backupbuddy_dat.php) missing or unreadable.', 'There may be a problem with the backup file, the files could not be extracted (you may manually extract the zip file in this directory to manually do this portion of restore), or the files were deleted before this portion of the restore was reached.  Start the import process over or try manually extracting (unzipping) the files then starting over. Restore will not continue to protect integrity of any existing data.', true, '9003' );
	die( ' Halted.' );
}

// Unserialize data; If it fails it then decodes the obscufated data then unserializes it. (new dat file method starting at 2.0).
if ( !is_serialized( $backupdata ) || ( false === ( $return = unserialize( $backupdata ) ) ) ) {
	// Skip first line.
	$second_line_pos = strpos( $backupdata, "\n" ) + 1;
	$backupdata = substr( $backupdata, $second_line_pos );
	
	// Decode back into an array.
	$return = unserialize( base64_decode( $backupdata ) );
}

$this->log( 'DONE Loading backup dat file.' );

// DAT IN $return
?>