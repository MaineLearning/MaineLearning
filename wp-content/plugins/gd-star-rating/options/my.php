<?php

$sett = array();
$sett["integrate_dashboard_latest_filter_thumb_std"] = 1;
$sett["integrate_dashboard_latest_filter_thumb_cmm"] = 1;
$sett["integrate_dashboard_latest_filter_stars_std"] = 1;
$sett["integrate_dashboard_latest_filter_stars_cmm"] = 1;
$sett["integrate_dashboard_latest_filter_stars_mur"] = 1;
$sett["integrate_dashboard_latest_count"] = 100;

?>

<div class="wrap"><h2 class="gdptlogopage">GD Star Rating: <?php _e("My Ratings", "gd-star-rating"); ?></h2>
<div class="gdsr">

<table><tr><td valign="top">
<div class="metabox-holder">
<?php include(STARRATING_PATH.'options/my/my_general.php'); ?>
<?php include(STARRATING_PATH.'options/my/my_posts.php'); ?>
</div>
</td><td style="width: 20px"> </td><td valign="top">
<div class="metabox-holder">
<?php include(STARRATING_PATH.'options/my/my_voted.php'); ?>
<?php include(STARRATING_PATH.'options/my/my_comments.php'); ?>
</div>
</td></tr></table>

</div>
</div>