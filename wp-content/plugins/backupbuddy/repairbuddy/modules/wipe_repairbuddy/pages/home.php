<?php
global $pluginbuddy_repairbuddy;
echo $pluginbuddy_repairbuddy->status_box( 'Removing RepairBuddy files...');
echo '<div id="pb_repairbuddy_working"><img src="repairbuddy/images/working.gif" title="Working. Please wait as this may take a moment."></div>';

flush();
sleep( 10 );

$pluginbuddy_repairbuddy->wipe_repairbuddy();

echo '<script type="text/javascript">jQuery("#pb_repairbuddy_working").hide();</script>';
?>