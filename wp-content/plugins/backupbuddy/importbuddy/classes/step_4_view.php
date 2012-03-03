<?php
$failed = false;

echo $this->status_box( 'Importing database content with ImportBuddy ' . $this->_version . '...' );
echo '<div id="pb_importbuddy_working"><img src="?ezimg=working.gif" title="Working... Please wait as this may take a moment..."></div>';

$import_result = $api->import_database();

echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';

if ( $import_result[0] == true ) {
	echo '<br><br>';
	if ( $import_result[1] !== true ) { // if not finished.
		$this->alert( 'Database too large to import in one step.', 'Your database was too large to import in one step and will be imported in chunks. Please continue the process until this step is finished. This may take a few steps depending on the size of your database and server speed.' );
		echo '<br>';
		echo 'Please keep continuing until your database has fully imported. This may take a few steps.';
		echo '<form action="?step=4" method=post>';
		echo '<input type="hidden" name="db_continue" value="' . $import_result[1] . '">';
		echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
		echo '<br><br><p style="text-align: center;"><input type="submit" name="submit" class="button" value="Continue Database Import &raquo" /></p>';
		echo '</form>';
	} else {
		echo '<b>Initial database import complete!</b><br><br>';
		echo 'Next the data in the database will be migrated to account for any file path or URL changes.';
		echo '<form action="?step=5" method=post>';
		echo '<input type="hidden" name="options" value="' . htmlspecialchars( serialize( $this->_options ) ) . '" />';
		echo '<br><br><p style="text-align: center;"><input type="submit" name="submit" class="button" value="Next Step &raquo" /></p>';
		echo '</form>';
	}
} else {
	echo '<br><b>Database import failed. Please use your back button to correct any errors.</b>';
}
?>