<?php 

    if (isset($_POST['gdsr_action']) && $_POST['gdsr_action'] == 'save') {
        $gdsr_oldstars = $gdsr_options["stars"];
        $gdsr_newstars = $_POST['gdsr_stars'];
        $gdsr_cmm_oldstars = $gdsr_options["cmm_stars"];
        $gdsr_cmm_newstars = $_POST['gdsr_cmm_stars'];
        $gdsr_review_oldstars = $gdsr_options["review_stars"];
        $gdsr_review_newstars = $_POST['gdsr_review_stars'];
        $gdsr_cmm_review_oldstars = $gdsr_options["cmm_review_stars"];
        $gdsr_cmm_review_newstars = isset($_POST['gdsr_cmm_review_stars']) ? $_POST['gdsr_cmm_review_stars'] : $gdsr_cmm_review_oldstars;

        if ($gdsr_options["stars"] != $gdsr_newstars) $recalculate_articles = true;
        else $recalculate_articles = false;
        if ($gdsr_options["cmm_stars"] != $gdsr_cmm_newstars) $recalculate_comment = true;
        else $recalculate_comment = false;
        if ($gdsr_options["review_stars"] != $gdsr_review_newstars) $recalculate_reviews = true;
        else $recalculate_reviews = false;
        if ($gdsr_options["cmm_review_stars"] != $gdsr_cmm_review_newstars) $recalculate_cmm_reviews = true;
        else $recalculate_cmm_reviews = false;

        $gdsr_options["stars"] = $gdsr_newstars;
        $gdsr_options["cmm_stars"] = $gdsr_cmm_newstars;
        $gdsr_options["review_stars"] = $gdsr_review_newstars;
        $gdsr_options["cmm_review_stars"] = $gdsr_cmm_review_newstars;

        update_option("gd-star-rating", $gdsr_options);

?>

<div id="message" class="updated fade" style="background-color: rgb(255, 251, 204);"><p><strong><?php _e("Settings saved.", "gd-star-rating"); ?></strong></p></div>

<?php } ?>

<div class="gdsr">
<form action="" method="post">
<input type="hidden" id="gdsr_action" name="gdsr_action" value="save" />
<div class="wrap"><h2 class="gdptlogopage">GD Star Rating: <?php _e("Settings", "gd-star-rating"); ?></h2>

<div id="gdsr_tabs" class="gdsrtabs">
<ul>
    <li><a href="#fragment-7"><span><?php _e("Features", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-5"><span><?php _e("Security", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-9"><span><?php _e("Advanced", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-6"><span><?php _e("Integration", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-2" class="tbs-star"><span><?php _e("Articles", "gd-star-rating"); ?></span></a></li>
    <?php if ($gdsr_options["comments_active"] == 1) { ?><li><a href="#fragment-3" class="tbs-star"><span><?php _e("Comments", "gd-star-rating"); ?></span></a></li><?php } ?>
    <?php if ($gdsr_options["multis_active"] == 1) { ?><li><a href="#fragment-8" class="tbs-star"><span><?php _e("Multis", "gd-star-rating"); ?></span></a></li><?php } ?>
    <li><a href="#fragment-1" class="tbs-thumb"><span><?php _e("Articles", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-10" class="tbs-thumb"><span><?php _e("Comments", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-4" class="tbs-spec"><span><?php _e("Special", "gd-star-rating"); ?></span></a></li>
</ul>
<div style="clear: both"></div>
<div id="fragment-7">
<?php include STARRATING_PATH."options/settings/settings_features.php"; ?>
</div>
<div id="fragment-5">
<?php include STARRATING_PATH."options/settings/settings_security.php"; ?>
</div>
<div id="fragment-9">
<?php include STARRATING_PATH."options/settings/settings_advanced.php"; ?>
<?php include STARRATING_PATH."options/settings/settings_administration.php"; ?>
</div>
<div id="fragment-6">
<?php include STARRATING_PATH."options/settings/settings_integration.php"; ?>
</div>
<div id="fragment-4">
<?php include STARRATING_PATH."options/settings/settings_special.php"; ?>
</div>
<div id="fragment-2">
<?php include STARRATING_PATH."options/settings/settings_articles.php"; ?>
</div>
<?php if ($gdsr_options["comments_active"] == 1) { ?><div id="fragment-3">
<?php include STARRATING_PATH."options/settings/settings_comments.php"; ?>
</div><?php } ?>
<?php if ($gdsr_options["multis_active"] == 1) { ?><div id="fragment-8">
<?php include STARRATING_PATH."options/settings/settings_multis.php"; ?>
</div><?php } ?>
<div id="fragment-1">
<?php include STARRATING_PATH."options/settings/settings_thumbs_articles.php"; ?>
</div>
<div id="fragment-10">
<?php include STARRATING_PATH."options/settings/settings_thumbs_comments.php"; ?>
</div>
</div>

<div style="margin-top: 10px"><input type="submit" class="inputbutton" value="<?php _e("Save Settings", "gd-star-rating"); ?>" name="gdsr_saving"/></div>
</div>
</form>
</div>
