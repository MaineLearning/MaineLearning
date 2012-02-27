<div id="gdsr_tabs" class="gdsrtabs">
<ul>
    <li id="general_tab"><a href="#fragment-1"><span><?php _e("General", "gd-star-rating"); ?></span></a></li>
    <li id="filter_tab"><a href="#fragment-2"><span><?php _e("Filter    ", "gd-star-rating"); ?></span></a></li>
    <li id="styles_tab"><a href="#fragment-3"><span><?php _e("Graphics", "gd-star-rating"); ?></span></a></li>
    <li style="display: none" id="multis_tab"><a href="#fragment-4"><span><?php _e("Multi Rating", "gd-star-rating"); ?></span></a></li>
    <li style="display: none" id="multisreview_tab"><a href="#fragment-5"><span><?php _e("Multi Review", "gd-star-rating"); ?></span></a></li>
    <li style="display: none" id="articlesreview_tab"><a href="#fragment-6"><span><?php _e("Articles Review", "gd-star-rating"); ?></span></a></li>
    <li style="display: none" id="articlesrater_tab"><a href="#fragment-7"><span><?php _e("Articles Rating Block", "gd-star-rating"); ?></span></a></li>
    <li style="display: none" id="commentsaggr_tab"><a href="#fragment-8"><span><?php _e("Aggregated Comments", "gd-star-rating"); ?></span></a></li>
    <li style="display: none" id="blograting_tab"><a href="#fragment-9"><span><?php _e("Blog Rating", "gd-star-rating"); ?></span></a></li>
    <li style="display: none" id="thumbsrating_tab"><a href="#fragment-10"><span><?php _e("Thumbs Rating", "gd-star-rating"); ?></span></a></li>
</ul>
<div style="clear: both"></div>
<div id="fragment-1">
<?php include(STARRATING_PATH."tinymce3/panels/general.php"); ?>
</div>
<div id="fragment-2">
<?php include(STARRATING_PATH."tinymce3/panels/filter.php"); ?>
</div>
<div id="fragment-3">
<?php include(STARRATING_PATH."tinymce3/panels/styles.php"); ?>
</div>
<div id="fragment-4">
<?php include(STARRATING_PATH."tinymce3/panels/multis.php"); ?>
</div>
<div id="fragment-5">
<?php include(STARRATING_PATH."tinymce3/panels/multisreview.php"); ?>
</div>
<div id="fragment-6">
<?php include(STARRATING_PATH."tinymce3/panels/articlesreview.php"); ?>
</div>
<div id="fragment-7">
<?php include(STARRATING_PATH."tinymce3/panels/articlesrater.php"); ?>
</div>
<div id="fragment-8">
<?php include(STARRATING_PATH."tinymce3/panels/commentsaggr.php"); ?>
</div>
<div id="fragment-9">
<?php include(STARRATING_PATH."tinymce3/panels/blograting.php"); ?>
</div>
<div id="fragment-10">
<?php include(STARRATING_PATH."tinymce3/panels/thumbsrating.php"); ?>
</div>
</div>
