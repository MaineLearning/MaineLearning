<div class="wrap"><h2 class="gdptlogopage">GD Star Rating: <?php _e("Tools", "gd-star-rating"); ?></h2>
<div class="gdsr">

<div id="gdsr_tabs" class="gdsrtabs">
<ul>
    <li><a href="#fragment-1"><span><?php _e("Bulk", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-2"><span><?php _e("Database", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-3"><span><?php _e("Cleanup", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-4"><span><?php _e("Edit CSS", "gd-star-rating"); ?></span></a></li>
</ul>
<div style="clear: both"></div>
<div id="fragment-1">
<?php include STARRATING_PATH."options/tools/tools_bulk.php"; ?>
</div>
<div id="fragment-2">
<?php include STARRATING_PATH."options/tools/tools_db.php"; ?>
</div>
<div id="fragment-3">
<?php include STARRATING_PATH."options/tools/tools_cleanup.php"; ?>
</div>
<div id="fragment-4">
<?php include STARRATING_PATH."options/tools/tools_editcss.php"; ?>
</div>
</div>

</div>
</div>