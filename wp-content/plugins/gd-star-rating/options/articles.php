<?php

global $wpdb;

$posts_per_page = $options["admin_rows"];

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdsr=articles";

$search = "";
$page_id = 1;
$filter_select = "";
$filter_date = "";
$filter_cats = "";
if (isset($_GET["select"])) $filter_select = $_GET["select"];
if (isset($_GET["pg"])) $page_id = $_GET["pg"];
if (isset($_GET["date"])) $filter_date = $_GET["date"];
if (isset($_GET["cat"])) $filter_cats = $_GET["cat"];
if (isset($_GET["s"])) $search = $_GET["s"];

if (isset($_POST["gdsr_filter"]) && $_POST["gdsr_filter"] == __("Filter", "gd-star-rating")) {
    $filter_select = $_POST["gdsr_postype"];
    $filter_date = $_POST["gdsr_dates"];
    $filter_cats = $_POST["gdsr_categories"];
    $page_id = 1;
}

if (isset($_POST["gdsr_search"]) && $_POST["gdsr_search"] == __("Search Posts", "gd-star-rating")) {
    $search = apply_filters('get_search_query', stripslashes($_POST["s"]));
    $page_id = 1;
}

if (isset($_POST["gdsr_update"]) && $_POST["gdsr_update"] == __("Update", "gd-star-rating")) {
    $gdsr_items = $_POST["gdsr_item"];
    if (count($gdsr_items) > 0) {
        $ids = "(".join(", ", $gdsr_items).")";
        if ($_POST["gdsr_delete_articles"] != "")
            GDSRDatabase::delete_votes($ids, $_POST["gdsr_delete_articles"], $gdsr_items);
        if ($_POST["gdsr_delete_articles_recc"] != "")
            GDSRDatabase::delete_votes($ids, $_POST["gdsr_delete_articles_recc"], $gdsr_items, true);
        if ($_POST["gdsr_delete_comments"] != "")
            GDSRDatabase::delete_votes($ids, $_POST["gdsr_delete_comments"], $gdsr_items);
        if ($_POST["gdsr_delete_comments_recc"] != "")
            GDSRDatabase::delete_votes($ids, $_POST["gdsr_delete_comments_recc"], $gdsr_items, true);

        if ($_POST["gdsr_review_rating"] != "") {
            $review = $_POST["gdsr_review_rating"];
            if ($_POST["gdsr_review_rating_decimal"] != "" && $_POST["gdsr_review_rating"] < $options["review_stars"])
                $review.= ".".$_POST["gdsr_review_rating_decimal"];
            gdsrAdmDB::update_reviews($ids, $review, $gdsr_items);
        }

        if ($_POST["gdsr_timer_type"] != "") {
            gdsrAdmDB::update_restrictions($ids, $_POST["gdsr_timer_type"], GDSRHelper::timer_value($_POST["gdsr_timer_type"], $_POST["gdsr_timer_date_value"], $_POST["gdsr_timer_countdown_value"], $_POST["gdsr_timer_countdown_type"]));
        }

        if ($_POST["gdsr_timer_type_recc"] != "") {
            gdsrAdmDB::update_restrictions_thumbs($ids, $_POST["gdsr_timer_type_recc"], GDSRHelper::timer_value($_POST["gdsr_timer_type_recc"], $_POST["gdsr_timer_date_value_recc"], $_POST["gdsr_timer_countdown_value_recc"], $_POST["gdsr_timer_countdown_type_recc"]));
        }

        gdsrAdmDB::update_settings($ids,
            $_POST["gdsr_article_moderation"], $_POST["gdsr_article_voterules"],
            $_POST["gdsr_comments_moderation"], $_POST["gdsr_comments_voterules"],
            $_POST["gdsr_article_moderation_recc"], $_POST["gdsr_article_voterules_recc"],
            $_POST["gdsr_comments_moderation_recc"], $_POST["gdsr_comments_voterules_recc"],
            $gdsr_items);

        gdsrAdmDB::upgrade_integration($ids,
            $_POST["gdsr_integration_active_std"], $_POST["gdsr_integration_active_mur"],
            $_POST["gdsr_integration_mur"]);
    }
}

if ($filter_cats != '' || $filter_cats != '0') $url.= "&amp;cat=".$filter_cats;
if ($filter_date != '' || $filter_date != '0') $url.= "&amp;date=".$filter_date;
if ($filter_select != '') $url.= "&amp;select=".$filter_select;
if ($search != '') $url.= "&amp;s=".$search;

$sql_count = gdsrAdmDB::get_stats_count($filter_select, $filter_date, $filter_cats, $search);
$number_posts = $wpdb->get_var($sql_count);

$max_page = floor($number_posts / $posts_per_page);
if ($max_page * $posts_per_page != $number_posts) $max_page++;

$pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

?>

<div class="wrap">
<form id="gdsr-articles" method="post" action="">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("Articles", "gd-star-rating"); ?></h2>
<p id="post-search">
    <label class="hidden" for="post-search-input"><?php _e("Search Posts", "gd-star-rating"); ?>:</label>
    <input class="search-input" id="post-search-input" type="text" value="<?php echo $search; ?>" name="s"/>
    <input class="button" type="submit" value="<?php _e("Search Posts", "gd-star-rating"); ?>" name="gdsr_search" />
</p>
<div class="tablenav">
    <div class="alignleft">
<?php gdsrAdmDB::get_combo_post_types($filter_select); ?>
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

    $sql = gdsrAdmDB::get_stats($filter_select, ($page_id - 1) * $posts_per_page, $posts_per_page, $filter_date, $filter_cats, $search);
    $rows = $wpdb->get_results($sql, OBJECT);

?>
<table class="widefat">
    <thead>
        <tr>
            <th class="check-column" scope="col"><input type="checkbox" onclick="checkAll(document.getElementById('gdsr-articles'));"/></th>
            <th scope="col"><?php _e("ID", "gd-star-rating"); ?></th>
            <th scope="col" style="width: 48px; padding: 1px;"> </th>
            <th scope="col"><?php _e("Title &amp; Categories", "gd-star-rating"); ?></th>
            <th scope="col" style="padding-left: 34px;"><?php _e("Vote Rules", "gd-star-rating"); ?></th>
            <?php if ($options["timer_active"] == 1) { ?>
                <th scope="col"><?php _e("Time", "gd-star-rating"); ?></th>
            <?php } ?>
            <?php if ($options["moderation_active"] == 1) { ?>
                <th scope="col"><?php _e("Moderation", "gd-star-rating"); ?></th>
            <?php } ?>
            <th scope="col"><?php _e("Ratings", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Total", "gd-star-rating"); ?></th>
            <?php if ($options["comments_integration_articles_active"] == 1) { ?>
                <th scope="col"><?php _e("Comment Integration", "gd-star-rating"); ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>

<?php

    $tr_class = "";
    $multi_sets = GDSRDBMulti::get_multis_tinymce();
    $post_types = gdsr_get_public_post_types();
    $multis = array();
    foreach ($multi_sets as $ms) $multis[$ms->folder] = $ms->name;

    foreach ($rows as $row) {
        $row = gdsrAdmDB::convert_row($row, $multis);
        $moderate_articles = $moderate_comments = "";
        if ($options["moderation_active"] == 1) {
            $moderate_articles = gdsrAdmDB::get_moderation_count($row->pid);
            $moderate_comments = gdsrAdmDB::get_moderation_count_joined($row->pid);
            $recc_moderate_articles = gdsrAdmDB::get_moderation_count($row->pid, "artthumb");
            $recc_moderate_comments = gdsrAdmDB::get_moderation_count_joined($row->pid, "artthumb");

            if ($moderate_articles == 0) $moderate_articles = "[ 0 ] ";
            else $moderate_articles = sprintf('[<a href="./admin.php?page=gd-star-rating-stats&amp;gdsr=moderation&amp;pid=%s&amp;vt=article"> <strong style="color: red;">%s</strong> </a>] ', $row->pid, $moderate_articles);
            if ($moderate_comments == 0) $moderate_comments = "[ 0 ] ";
            else $moderate_comments = sprintf('[<a href="./admin.php?page=gd-star-rating-stats&amp;gdsr=moderation&amp;pid=%s&amp;vt=post"> <strong style="color: red;">%s</strong> </a>] ', $row->pid, $moderate_comments);
            if ($recc_moderate_articles == 0) $recc_moderate_articles = "[ 0 ] ";
            else $recc_moderate_articles = sprintf('[<a href="./admin.php?page=gd-star-rating-stats&amp;gdsr=moderation&amp;pid=%s&amp;vt=article"> <strong style="color: red;">%s</strong> </a>] ', $row->pid, $recc_moderate_articles);
            if ($recc_moderate_comments == 0) $recc_moderate_comments = "[ 0 ] ";
            else $recc_moderate_comments = sprintf('[<a href="./admin.php?page=gd-star-rating-stats&amp;gdsr=moderation&amp;pid=%s&amp;vt=post"> <strong style="color: red;">%s</strong> </a>] ', $row->pid, $recc_moderate_comments);
        }

        $timer_info = $recc_timer_info = "";
        if ($options["timer_active"] == 1) {
            if ($row->expiry_type == "D") {
                $timer_info = '<strong><span style="color: red">'.__("date limit", "gd-star-rating").'</span></strong><br />';
                $timer_info.= $row->expiry_value;
            } else if ($row->expiry_type == "T") {
                $timer_info = '<strong><span style="color: red">'.__("countdown", "gd-star-rating").'</span></strong><br />';
                $timer_info.= substr($row->expiry_value, 1)." ";
                switch (substr($row->expiry_value, 0, 1)) {
                    case "H":
                        $timer_info.= __("Hours", "gd-star-rating");
                        break;
                    case "D":
                        $timer_info.= __("Days", "gd-star-rating");
                        break;
                    case "M":
                        $timer_info.= __("Months", "gd-star-rating");
                        break;
                }
            } else $timer_info = __("no limit", "gd-star-rating").'<br /><br />';
        }

        if ($options["timer_active"] == 1) {
            if ($row->recc_expiry_type == "D") {
                $recc_timer_info = '<strong><span style="color: red">'.__("date limit", "gd-star-rating").'</span></strong><br />';
                $recc_timer_info.= $row->recc_expiry_value;
            } else if ($row->recc_expiry_type == "T") {
                $recc_timer_info = '<strong><span style="color: red">'.__("countdown", "gd-star-rating").'</span></strong><br />';
                $recc_timer_info.= substr($row->recc_expiry_value, 1)." ";
                switch (substr($row->recc_expiry_value, 0, 1)) {
                    case "H":
                        $recc_timer_info.= __("Hours", "gd-star-rating");
                        break;
                    case "D":
                        $recc_timer_info.= __("Days", "gd-star-rating");
                        break;
                    case "M":
                        $recc_timer_info.= __("Months", "gd-star-rating");
                        break;
                }
            } else $recc_timer_info = __("no limit", "gd-star-rating").'<br /><br />';
        }

        if ($row->rating_total > $options["stars"] ||
            $row->rating_visitors > $options["stars"] ||
            $row->rating_users > $options["stars"]) $tr_class.=" invalidarticle";

        $chart_url = STARRATING_CHART_URL."post_charts.php?_ajax_nonce=".wp_create_nonce('gdsr_chart_l8')."&postid=".$row->pid."&amp;action=article";

        echo '<tr id="post-'.$row->pid.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<th scope="row" class="check-column"><input name="gdsr_item[]" value="'.$row->pid.'" type="checkbox"/></th>';
        echo '<td nowrap="nowrap">'.$row->pid.'</td>';
        echo '<td class="gdrinner">';
            echo '<a title="'.__("Comments", "gd-star-rating").'" href="./admin.php?page=gd-star-rating-stats&amp;gdsr=comments&amp;postid='.$row->pid.'"><img alt="'.__("Comments", "gd-star-rating").'" src="'.STARRATING_URL.'gfx/comments.png" border="0" /></a>';
            echo '<a title="'.__("Chart", "gd-star-rating").'" href="'.$chart_url.'" class="clrboxed"><img alt="'.__("Chart", "gd-star-rating").'" src="'.STARRATING_URL.'gfx/chart.png" border="0" /></a><br />';
            echo '<a title="'.__("Edit", "gd-star-rating").'" href="'.get_edit_post_link($row->pid).'" target="_blank"><img alt="'.__("Edit", "gd-star-rating").'" src="'.STARRATING_URL.'gfx/edit.png" border="0" /></a>';
            echo '<a title="'.__("View", "gd-star-rating").'" href="'.get_permalink($row->pid).'" target="_blank"><img alt="'.__("View", "gd-star-rating").'" src="'.STARRATING_URL.'gfx/view.png" border="0" /></a>';
        echo '</td>';
            echo '<td><div class="gdsr-td-title">'.$row->title.'</div><div class="gdsr-td-condensed">';
            echo '<span style="color: #c00">'.$post_types[$row->post_type].'</span>';
            $catss = gdsrAdmDB::get_categories($row->pid);
            if ($catss != "/") echo ": ".$catss;
            echo ' | <span style="color: #c00">'.__("Views", "gd-star-rating").'</span>: '.$row->views.'</div>';
        echo '</td>';
        echo '<td nowrap="nowrap" class="gdsr-td-condensed">';
            echo '<div class="gdsr-art-stars">';
            echo $row->rules_articles.'<br />'.$row->rules_comments;
            echo '</div>';
            echo '<div class="gdsr-art-split"></div>';
            echo '<div class="gdsr-art-thumbs">';
            echo $row->recc_rules_articles.'<br />'.$row->recc_rules_comments;
            echo '</div>';
        echo '</td>';
        if ($options["timer_active"] == 1) {
            echo '<td nowrap="nowrap" class="gdsr-td-condensed">';
            echo $timer_info;
            echo '<div class="gdsr-art-split"></div>';
            echo $recc_timer_info;
            echo '</td>';
        }
        if ($options["moderation_active"] == 1) {
            echo '<td nowrap="nowrap" class="gdsr-td-condensed">';
            echo $moderate_articles.$row->moderate_articles.'<br />'.$moderate_comments.$row->moderate_comments;
            echo '<div class="gdsr-art-split"></div>';
            echo $recc_moderate_articles.$row->recc_moderate_articles.'<br />'.$recc_moderate_comments.$row->recc_moderate_comments;
            echo '</td>';
        }
        echo '<td nowrap="nowrap" class="gdsr-td-condensed">';
            echo $row->votes;
            echo '<div class="gdsr-art-split"></div>';
            echo $row->thumbs;
        echo '</td>';
        echo '<td nowrap="nowrap" class="gdsr-td-condensed">'.$row->total.'</td>';
        if ($options["comments_integration_articles_active"] == 1) {
            echo '<td nowrap="nowrap" class="gdsr-td-condensed">';
                echo '<div class="gdsr-art-stars">';
                echo $row->cmm_integration_std;
                echo '</div>';
            echo '<div class="gdsr-art-split"></div>';
                echo '<div class="gdsr-art-multis">';
                echo $row->cmm_integration_mur;
                echo '</div>';
            echo '</td>';
        }
        echo '</tr>';

        if ($tr_class == "") $tr_class = "alternate ";
        else $tr_class = "";
    }

?>

    </tbody>
</table>
<div class="tablenav" style="height: 8em">
    <div class="alignleft">
        <?php include(STARRATING_PATH.'options/elements/articles_options.php'); ?>
    </div>
<br class="clear"/>
</div>
<br class="clear"/>
</form>
</div>
