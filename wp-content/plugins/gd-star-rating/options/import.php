<?php

require_once(STARRATING_PATH."code/cls/import.php");

if (isset($_POST["gdsr_import_psr"]) && $_POST["gdsr_import_psr"] == __("Import Data", "gd-star-rating")) {
    GDSRImport::import_psr();
    $imports["post_star_rating"] = $imports["post_star_rating"] + 1;
    update_option('gd-star-rating-import', $imports);
    ?> <div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><p><strong><?php _e("Data import completed.", "gd-star-rating"); ?></strong></p></div> <?php
}

if (isset($_POST["gdsr_import_wpr"]) && $_POST["gdsr_import_wpr"] == __("Import Data", "gd-star-rating")) {
    GDSRImport::import_wpr();
    $imports["wp_post_ratings"] = $imports["wp_post_ratings"] + 1;
    update_option('gd-star-rating-import', $imports);
    ?> <div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><p><strong><?php _e("Data import completed.", "gd-star-rating"); ?></strong></p></div> <?php
}

if (isset($_POST["gdsr_import_srfr"]) && $_POST["gdsr_import_srfr"] == __("Import Data", "gd-star-rating")) {
    GDSRImport::import_srfr($options["review_stars"], $_POST["gdsr_srfr_max"], $_POST["gdsr_srfr_meta"], $_POST["gdsr_srfr_try"]);
    $imports["star_rating_for_reviews"] = $imports["star_rating_for_reviews"] + 1;
    update_option('gd-star-rating-import', $imports);
    ?> <div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><p><strong><?php _e("Data import completed.", "gd-star-rating"); ?></strong></p></div> <?php
}

?>

<div class="wrap">
    <h2 class="gdptlogopage">GD Star Rating: <?php _e("Import Data", "gd-star-rating"); ?></h2>
<div class="gdsr">

<div id="gdsr_tabs" class="gdsrtabs">
<ul>
    <li><a href="#fragment-3"><span>Star Rating For Reviews</span></a></li>
    <li><a href="#fragment-1"><span>Post Star Rating</span></a></li>
    <li><a href="#fragment-2"><span>WP Post Ratings</span></a></li>
</ul>
<div style="clear: both"></div>

<div id="fragment-1">
<form method="post">
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Plugin URL", "gd-star-rating"); ?>:</th>
    <td>
        <a href="http://wordpress.org/extend/plugins/post-star-rating/">http://wordpress.org/extend/plugins/post-star-rating/</a>
    </td>
</tr>
<tr><th scope="row"><?php _e("Author URL", "gd-star-rating"); ?>:</th>
    <td>
        O. Doutor
    </td>
</tr>
<tr><th scope="row"><?php _e("Status", "gd-star-rating"); ?>:</th>
    <td>
        <?php $import_available = GDSRImport::import_psr_check($imports["post_star_rating"]); ?>
    </td>
</tr>
</tbody></table>
<?php if ($import_available) { ?>
<input type="submit" value="<?php _e("Import Data", "gd-star-rating"); ?>" name="gdsr_import_psr" class="inputbutton" />
<?php } ?>
</form>
</div>

<div id="fragment-2">
<form method="post">
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Plugin URL", "gd-star-rating"); ?>:</th>
    <td>
        <a target="_blank" href="http://wordpress.org/extend/plugins/wp-postratings/">http://wordpress.org/extend/plugins/wp-postratings/</a>
    </td>
</tr>
<tr><th scope="row"><?php _e("Author URL", "gd-star-rating"); ?>:</th>
    <td>
        <a target="_blank" href="http://lesterchan.net/">Lester Chan</a>
    </td>
</tr>
<tr><th scope="row"><?php _e("Status", "gd-star-rating"); ?>:</th>
    <td>
        <?php $import_available = GDSRImport::import_wpr_check($imports["wp_post_ratings"]); ?>
    </td>
</tr>
</tbody></table>
<?php if ($import_available) { ?>
<input type="submit" value="<?php _e("Import Data", "gd-star-rating"); ?>" name="gdsr_import_wpr" class="inputbutton" />
<?php } ?>
</form>
</div>

<div id="fragment-3">
<form method="post">
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Plugin URL", "gd-star-rating"); ?>:</th>
    <td>
        <a target="_blank" href="http://wordpress.org/extend/plugins/star-rating-for-reviews/">http://wordpress.org/extend/plugins/star-rating-for-reviews/</a>
    </td>
</tr>
<tr><th scope="row"><?php _e("Author URL", "gd-star-rating"); ?>:</th>
    <td>
        <a target="_blank" href="http://www.channel-ai.com/">eyn</a>
    </td>
</tr>
<tr><th scope="row"><?php _e("Instructions", "gd-star-rating"); ?>:</th>
    <td>
        <?php printf(__("You need to enter few settings for this plugin. This can be found in the plugin's main and only PHP file: %s", "gd-star-rating"), "star-rating.php"); ?>
        <?php _e("For each value you have a name of the variable from plugin file in square brackets.", "gd-star-rating"); ?>
        <?php _e("Plugin could save data in post meta table or only in the post using shortcodes. Import can try both, but if you are usre that ratings are stored in post meta table, use that option.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Import Settings", "gd-star-rating"); ?>:</th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Maximum review value", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_srfr_max" id="gdsr_srfr_max" value="5" style="width: 170px" /> [$sr_defaultstar]
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Meta Key", "gd-star-rating"); ?>:</td>
                <td>
                    <input type="text" name="gdsr_srfr_meta" id="gdsr_srfr_meta" value="rating" style="width: 170px" /> [$sr_metakey]
                </td>
            </tr>
            <tr>
                <td width="150"><?php _e("Try Importing from", "gd-star-rating"); ?>:</td>
                <td>
                    <select name="gdsr_srfr_try" id="gdsr_srfr_try" style="width: 180px">
                        <option value="M"><?php _e("Posts meta table", "gd-star-rating"); ?></option>
                        <option value="P"><?php _e("Posts contents only", "gd-star-rating"); ?></option>
                        <option value="B"><?php _e("Both contents and meta", "gd-star-rating"); ?></option>
                    </select>
                </td>
            </tr>
        </table>
    </td>
<tr><th scope="row"><?php _e("Status", "gd-star-rating"); ?>:</th>
    <td>
        <?php $import_available = GDSRImport::import_srfr_check($imports["star_rating_for_reviews"]); ?>
    </td>
</tr>
</tbody></table>
<?php if ($import_available) { ?>
<input type="submit" value="<?php _e("Import Data", "gd-star-rating"); ?>" name="gdsr_import_srfr" class="inputbutton" />
<?php } ?>
</form>
</div>

<br /><div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><p><strong>
<?php _e("To avoid rating data import problems, please don't use GD Star Rating in the same time as the plugins above, to avoid potential import problems and false data. I can't guarantee that data will be transfered without problems.", "gd-star-rating"); ?>
</strong></p></div>

</div>
</div>
</div>