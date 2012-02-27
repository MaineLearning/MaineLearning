<?php

global $wpdb;

$posts_per_page = $options["admin_rows"];

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdsr=murset";

?>

<div class="wrap" style="max-width: <?php echo $options["admin_width"]; ?>px">
<form id="gdsr-articles" method="post" action="">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("Multi Set Results", "gd-star-rating"); ?>: <?php _e("Voters", "gd-star-rating"); ?></h2>
<div class="tablenav">
    <div class="alignleft">
<?php gdsrAdmDB::get_combo_months($filter_date); ?>
<?php gdsrAdmDB::get_combo_categories($filter_cats); ?>
        <input class="button-secondary delete" type="submit" name="gdsr_filter" value="<?php _e("Filter", "gd-star-rating"); ?>" />
    </div>
    <div class="tablenav-pages">
        <?php echo $pager; ?>
    </div>
</div>
<br class="clear"/>

</form>
</div>
