<?php
echo $this->status_box( 'Removing RepairBuddy files...');
echo '<div id="pb_repairbuddy_working"><img src="repairbuddy/images/working.gif" title="Working. Please wait as this may take a moment."></div>';

flush();
sleep( 5 );

$this->wipe_repairbuddy();

echo '<script type="text/javascript">jQuery("#pb_repairbuddy_working").hide();</script>';
?>