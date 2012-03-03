<?php
$api->parse_options();

echo $this->status_box( 'Extracting backup ZIP file with ImportBuddy ' . $this->_version . '...' );
echo '<div id="pb_importbuddy_working"><img src="?ezimg=working.gif" title="Working... Please wait as this may take a moment..."></div>';

$results = $api->extract();

echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';

if ( true === $results ) { // Move on to next step.
	echo '<br><br><b>Files successfully extracted.</b>';
	echo '<form action="?step=3" method=post>';
	echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
	echo '<br><br><p style="text-align: center;"><input type="submit" name="submit" value="Next Step &raquo;" class="button" /></p>';
	echo '</form>';
} else {
	$this->alert( 'File extraction process did not complete successfully.', 'Unable to continue to next step. Manually extract the backup ZIP file and choose to "Skip File Extraction" from the advanced options on Step 1.', '9005' );
	echo '<p style="text-align: center;"><a href="http://pluginbuddy.com/tutorials/unzip-backup-zip-file-in-cpanel/">Click here for instructions on manual ZIP extraction as a work-around.</a></p>';
}
?>