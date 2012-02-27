<?php

global $wpdb;

$posts_per_page = $options["admin_rows"];

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdsr=murpost";

$set_id = $_GET["sid"];
$set = gd_get_multi_set($set_id);

$select = "";
$page_id = 1;
$filter_date = "";
$filter_cats = "";
if (isset($_GET["select"])) $select = $_GET["select"];
if (isset($_GET["pg"])) $page_id = $_GET["pg"];
if (isset($_GET["date"])) $filter_date = $_GET["date"];
if (isset($_GET["cat"])) $filter_cats = $_GET["cat"];
if (isset($_GET["s"])) $search = $_GET["s"];

if ($_POST["gdsr_filter"] == __("Filter", "gd-star-rating")) {
    $filter_date = $_POST["gdsr_dates"];
    $filter_cats = $_POST["gdsr_categories"];
}

if ($_POST["gdsr_search"] == __("Search Posts", "gd-star-rating")) {
    $search = apply_filters('get_search_query', stripslashes($_POST["s"]));
}

if (isset($_POST["gdsr_update"]) && $_POST["gdsr_update"] == __("Update", "gd-star-rating")) {
    $gdsr_items = $_POST["gdsr_item"];
    if (count($gdsr_items) > 0) {
        $ids = "(".join(", ", $gdsr_items).")";
        if ($_POST["gdsr_delete_articles"] != '')
            GDSRDBMulti::delete_votes($ids, $_POST["gdsr_delete_articles"], $set_id);
    }
}

$url.= "&amp;sid=".$set_id;
if ($filter_cats != '' || $filter_cats != '0') $url.= "&amp;cat=".$filter_cats;
if ($filter_date != '' || $filter_date != '0') $url.= "&amp;date=".$filter_date;
if ($search != '') $url.= "&amp;s=".$search;
if ($select != '') $url.= "&amp;select=".$select;

$sql_count = gdsrAdmDBMulti::get_stats_count($set_id, $filter_date, $filter_cats, $search);
$np = $wpdb->get_results($sql_count);
$number_posts_page = 0;
$number_posts_post = 0;
if (count($np) > 0) {
    foreach ($np as $n) {
        if ($n->post_type == "page") $number_posts_page = $n->count;
        else $number_posts_post = $n->count;
    }
}
$number_posts_all = $number_posts_post + $number_posts_page;
if ($select == "post") $number_posts = $number_posts_post;
else if ($select == "page") $number_posts = $number_posts_page;
else $number_posts = $number_posts_all;

$max_page = floor($number_posts / $posts_per_page);
if ($max_page * $posts_per_page != $number_posts) $max_page++;

if ($max_page > 1)
    $pager = gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg");

$set = gd_get_multi_set($set_id);

?>

<div class="wrap" style="max-width: <?php echo $options["admin_width"]; ?>px">
<form id="gdsr-articles" method="post" action="">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("Multi Set Results", "gd-star-rating"); ?></h2>
<h3 class="gdsetname"><?php _e("Set Name", "gd-star-rating"); ?>: <span style="color: red;"><?php echo $set->name; ?></span></h3>
<ul class="subsubsub">
    <li><a<?php echo $select == "" ? ' class="current"' : ''; ?> href="<?php echo $url; ?>">All Articles (<?php echo $number_posts_all; ?>)</a> |</li>
    <li><a<?php echo $select == "post" ? ' class="current"' : ''; ?> href="<?php echo $url; ?>&amp;select=post">Posts (<?php echo $number_posts_post; ?>)</a> |</li>
    <li><a<?php echo $select == "page" ? ' class="current"' : ''; ?> href="<?php echo $url; ?>&amp;select=page">Pages (<?php echo $number_posts_page; ?>)</a></li>
</ul>
<?php
    if ($select != '') $url.= "&amp;select=".$select;
?>
<p id="post-search">
    <label class="hidden" for="post-search-input"><?php _e("Search Posts", "gd-star-rating"); ?>:</label>
    <input class="search-input" id="post-search-input" type="text" value="<?php echo $search; ?>" name="s"/>
    <input class="button" type="submit" value="<?php _e("Search Posts", "gd-star-rating"); ?>" name="gdsr_search" />
</p>
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
<?php

    $sql = gdsrAdmDBMulti::get_stats($set_id, $select, ($page_id - 1) * $posts_per_page, $posts_per_page, $filter_date, $filter_cats, $search);
    $rows = $wpdb->get_results($sql, OBJECT);

?>

<table class="widefat">
    <thead>
        <tr>
            <th class="check-column" scope="col"><input type="checkbox" onclick="checkAll(document.getElementById('gdsr-articles'));"/></th>
            <th scope="col"><?php _e("Title", "gd-star-rating"); ?></th>
            <th scope="col" style="width: 36px; text-align: center;"><?php _e("View", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Categories", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Ratings", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Total", "gd-star-rating"); ?></th>
            <?php if ($options["review_active"] == 1) { ?>
                <th scope="col" style="text-align: right"><?php _e("Review", "gd-star-rating"); ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
<?php

    $tr_class = "";
    foreach ($rows as $row) {
        $total_votes = $row->total_votes_users + $row->total_votes_visitors;
        $total_rating = $total_votes == 0 ? 0 : number_format(($row->total_votes_users * $row->average_rating_users + $row->total_votes_visitors * $row->average_rating_visitors) / $total_votes, 1);

        echo '<tr id="post-'.$row->pid.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<th scope="row" class="check-column"><input name="gdsr_item[]" value="'.$row->pid.'" type="checkbox"></th>';
        echo '<td><strong>'.sprintf('<a href="./post.php?action=edit&amp;post=%s">%s</a>', $row->pid, $row->post_title).'</strong></td>';
        echo '<td nowrap="nowrap" style="text-align: center;">';
            echo '<a href="'.get_permalink($row->pid).'" target="_blank"><img src="'.STARRATING_URL.'gfx/view.png" border="0" /></a>&nbsp;';
        echo '</td>';
        echo '<td>'.gdsrAdmDB::get_categories($row->pid).'</td>';
        echo '<td>';
            if ($row->total_votes_visitors == 0) echo sprintf("[ 0 ] %s: /<br />", __("visitors", "gd-star-rating"));
            else echo sprintf('[ <a href="./admin.php?page=gd-star-rating-multi-sets&amp;gdsr=murset&amp;sid=%s&amp;pid=%s&amp;filter=visitor"><strong style="color: red;">%s</strong></a> ] %s: <strong style="color: red;">%s</strong><br />', $set_id, $row->pid, $row->total_votes_visitors, __("visitors", "gd-star-rating"), $row->average_rating_visitors);
            if ($row->total_votes_users == 0) echo sprintf("[ 0 ] %s: /<br />", __("users", "gd-star-rating"));
            else echo sprintf('[ <a href="./admin.php?page=gd-star-rating-multi-sets&amp;gdsr=murset&amp;sid=%s&amp;pid=%s&amp;filter=user"><strong style="color: red;">%s</strong></a> ] %s: <strong style="color: red;">%s</strong>', $set_id, $row->pid, $row->total_votes_users, __("users", "gd-star-rating"), $row->average_rating_users);
        echo '</td>';
        echo '<td>';
            if ($total_votes == 0) echo sprintf("[ 0 ] %s: /", __("rating", "gd-star-rating"));
            else echo sprintf('[ <a href="./admin.php?page=gd-star-rating-multi-sets&amp;gdsr=murset&amp;sid=%s&amp;pid=%s"><strong style="color: red;">%s</strong></a> ] %s: <strong style="color: red;">%s</strong><br />', $set_id, $row->pid, $total_votes, __("rating", "gd-star-rating"), $total_rating);
        echo '</td>';
        if ($options["review_active"] == 1) {
            echo '<td style="text-align: right">';
            echo '<strong><span style="color: blue">'.$row->average_review.'</span></strong>';
            echo '</td>';
        }
        echo '</tr>';

        if ($tr_class == "")
            $tr_class = "alternate ";
        else
            $tr_class = "";
    }

?>

</tbody>
</table>
<div class="tablenav" style="height: 7em">
    <div class="alignleft">
        <div class="panel">
        <table cellpadding="0" cellspacing="0">
        <tr>
        <td>
            <table cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 120px; height: 29px;">
                    <span class="paneltext"><strong><?php _e("Articles", "gd-star-rating"); ?>:</strong></span>
                </td>
                <td style="width: 80px; height: 29px;">
                    <span class="paneltext"><?php _e("Delete", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 29px;" align="right">
                    <select id="gdsr_delete_articles" name="gdsr_delete_articles" style="width: 120px;">
                        <option value="">/</option>
                        <option value="AV"><?php _e("Visitors", "gd-star-rating"); ?></option>
                        <option value="AU"><?php _e("Users", "gd-star-rating"); ?></option>
                        <option value="AA"><?php _e("All", "gd-star-rating"); ?></option>
                    </select>
                </td><td style="width: 10px"></td>
                <?php if ($options["moderation_active"] == 1) { ?>
                <td style="width: 80px; height: 29px;">
                     <!--<span class="paneltext"><?php _e("Moderation", "gd-star-rating"); ?>:</span>-->
                </td>
                <td style="width: 140px; height: 29px;" align="right">
                <?php //GDSRHelper::render_moderation_combo("gdsr_article_moderation", "/", 120, "", true); ?>
                </td><td style="width: 10px"></td>
                <?php } ?>
                <td style="width: 80px; height: 29px;">
                     <!--<span class="paneltext"><?php _e("Vote Rules", "gd-star-rating"); ?>:</span>-->
                </td>
                <td style="width: 140px; height: 29px;" align="right">
                <?php //GDSRHelper::render_rules_combo("gdsr_article_voterules", "/", 120, "", true); ?>
                </td>
            </tr>
            </table>
        </td>
        <td style="width: 10px;"></td>
        <td class="gdsr-vertical-line">
            <input class="inputbutton" type="submit" name="gdsr_update" value="<?php _e("Update", "gd-star-rating"); ?>" />
        </td>
        </tr>
        </table>
             <!--<table cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 120px; height: 29px;">
                </td>
                <td style="width: 80px; height: 29px;">
                    <span class="paneltext"><?php _e("Restriction", "gd-star-rating"); ?>:</span>
                </td>
                <td style="width: 140px; height: 29px;" align="right">
                <?php GDSRHelper::render_timer_combo("gdsr_timer_type", $timer_restrictions, 120, '', true, 'gdsrTimerChange()'); ?>
                </td><td style="width: 10px"></td>
                <td style="width: 80px; height: 29px;">
                    <div id="gdsr_timer_countdown_text" style="display: none"><span class="paneltext"><?php _e("Countdown", "gd-star-rating"); ?>:</span></div>
                    <div id="gdsr_timer_date_text" style="display: none"><span class="paneltext"><?php _e("Date", "gd-star-rating"); ?>:</span></div>
                </td>
                <td style="width: 140px; height: 29px;" align="right">
                    <div id="gdsr_timer_countdown" style="display: none"><input class="regular-text" type="text" value="<?php echo $countdown_value; ?>" id="gdsr_timer_countdown_value" name="gdsr_timer_countdown_value" style="width: 35px; text-align: right; padding: 2px;" />
                    <?php GDSRHelper::render_countdown_combo("gdsr_timer_countdown_type", $countdown_type, 70); ?></div>
                    <div id="gdsr_timer_date" style="display: none"><input class="regular-text" type="text" value="<?php echo $timer_date_value; ?>" id="gdsr_timer_date_value" name="gdsr_timer_date_value" style="width: 110px; padding: 2px;" /></div>
                </td>
            </tr>
            </table>-->
        </div>
    </div>
<br class="clear"/>
</div>
<br class="clear"/>

</form>
</div>
