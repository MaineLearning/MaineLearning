<?php
$this->log( 'Beginning final step cleanup!' );

echo 'This step handles cleanup of files. It is common to not be able to delete some files due to permission errors. You may manually delete them or ignore any errors if you wish.<br><br>';

echo $this->status_box( 'Cleaning up after restore with ImportBuddy ' . $this->_version . '...' );
echo '<div id="pb_importbuddy_working"><img src="?ezimg=working.gif" title="Working... Please wait as this may take a moment..."></div>';

flush();
sleep( 5 ); // Pause to allow CSS, etc time to load before importbuddy starts deleting those files.

$api->cleanup();

echo '<script type="text/javascript">jQuery("#pb_importbuddy_working").hide();</script>';

echo '<br ><br>';
echo '<br><br><b>Import complete for the site: </b><a href="' . $this->_options['siteurl'] . '" target="_new">' . $this->_options['siteurl'] . '</a><br><br>';
echo '<br><br><b>Thank you for choosing BackupBuddy!</b>';

$this->log( 'Finished final step cleanup! Kerfuffle!' );
?>