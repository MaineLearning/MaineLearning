<?php

global $wpdb, $gdsr;

$options = $gdsr->o;
$posts_per_page = $options["admin_rows"];

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdsr=voters";

$select = "";
$post_id = -1;
$page_id = 1;
$filter_date = "";
$filter_vote = 0;
$vote_type = "article";
$sort_column = '';
$sort_order = '';

if (isset($_GET["pid"])) $post_id = $_GET["pid"];
if (isset($_GET["vg"])) $select = $_GET["vg"];
if (isset($_GET["vt"])) $vote_type = $_GET["vt"];
if (isset($_GET["pg"])) $page_id = $_GET["pg"];
if (isset($_GET["date"])) $filter_date = $_GET["date"];
if (isset($_GET["vote"])) $filter_vote = $_GET["vote"];
if (isset($_GET["sc"])) $sort_column = $_GET["sc"];
if (isset($_GET["so"])) $sort_order = $_GET["so"];

if (isset($_POST["gdsr_filter"]) && $_POST["gdsr_filter"] == __("Filter", "gd-star-rating")) {
    $filter_date = $_POST["gdsr_dates"];
    $filter_vote = $_POST["gdsr_vote"];
    $page_id = 1;
}

$is_thumb = substr($vote_type, 3) == "thumb";

if (isset($_POST["gdsr_update"]) && $_POST["gdsr_update"] == __("Update", "gd-star-rating")) {
    $gdsr_items = $_POST["gdsr_item"];
    if (count($gdsr_items) > 0) {
        $ids = "(".join(", ", $gdsr_items).")";
        $delact = $_POST["gdsr_delete_voters"];
        if ($delact == "L") gdsrAdmDB::delete_voters_log($ids);
        if ($delact == "D") gdsrAdmDB::delete_voters_full($ids, $vote_type, $is_thumb);
    }
}

$url.= "&amp;pid=".$post_id."&amp;vt=".$vote_type;
if ($filter_date != '' || $filter_date != '0') $url.= "&amp;date=".$filter_date;
if ($filter_vote > 0) $url.= "&amp;vote=".$filter_vote;
if ($select != '') $url.= "&amp;vg=".$select;
$b_url = $url;
if ($sort_column != '') $url.= '&amp;sc='.$sort_column.'&amp;so='.$sort_order;

$sql_count = gdsrAdmDB::get_voters_count($post_id, $filter_date, $vote_type, $filter_vote);
$np = $wpdb->get_results($sql_count);
$number_posts_users = 0;
$number_posts_visitors = 0;
if (count($np) > 0) {
    foreach ($np as $n) {
        if ($n->user == "1") $number_posts_visitors = $n->count;
        else $number_posts_users = $n->count;
    }
}
$number_posts_all = $number_posts_users + $number_posts_visitors;
if ($select == "users") $number_posts = $number_posts_users;
else if ($select == "visitors") $number_posts = $number_posts_visitors;
else $number_posts = $number_posts_all;

$max_page = floor($number_posts / $posts_per_page);
if ($max_page * $posts_per_page != $number_posts) $max_page++;

$pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

if ($vote_type == "article")
    $max_stars = $options["stars"];
else
    $max_stars = $options["cmm_stars"];

?>

<div class="wrap">
<form id="gdsr-comments" method="post" action="">
<p><strong><?php _e("Vote log for post", "gd-star-rating"); ?>: 
    <?php echo sprintf('<a href="./post.php?action=edit&amp;post=%s">%s</a> <a href="%s" target="_blank">[view]</a>', $post_id, gdsrAdmDB::get_post_title($post_id), get_permalink($post_id)); ?>
</strong></p>
<ul class="subsubsub">
    <li><a<?php echo $select == "total" ? ' class="current"' : ''; ?> href="<?php echo $url; ?>&amp;vg=total">All Votes (<?php echo $number_posts_all; ?>)</a> |</li>
    <li><a<?php echo $select == "users" ? ' class="current"' : ''; ?> href="<?php echo $url; ?>&amp;vg=users">Users (<?php echo $number_posts_users; ?>)</a> |</li>
    <li><a<?php echo $select == "visitors" ? ' class="current"' : ''; ?> href="<?php echo $url; ?>&amp;vg=visitors">Visitors (<?php echo $number_posts_visitors; ?>)</a></li>
</ul>
<?php
    if ($select != '') $url.= "&amp;vg=".$select;
?>
<div class="tablenav">
    <div class="alignleft">
        <?php gdsrAdmDB::get_combo_months($filter_date); ?>
        <select style="width: 100px;" name="gdsr_vote" id="gdsr_vote">
        <option value="0"<?php if ($filter_vote == 0) echo ' selected="selected"'; ?>><?php _e("All Votes", "gd-star-rating"); ?></option>
        <?php GDSRHelper::render_stars_select_full($filter_vote, $max_stars, 1, __("Vote", "gd-star-rating")); ?>
        </select>
        <input class="button-secondary delete" type="submit" name="gdsr_filter" value="<?php _e("Filter", "gd-star-rating"); ?>" />
    </div>
    <div class="tablenav-pages">
        <?php echo $pager; ?>
    </div>
</div>
<br class="clear"/>
<?php

    $sql = gdsrAdmDB::get_visitors($post_id, $vote_type, $filter_date, $filter_vote, $select, ($page_id - 1) * $posts_per_page, $posts_per_page, $sort_column, $sort_order);
    $rows = $wpdb->get_results($sql, OBJECT);

    $col[0] = gdFunctionsGDSR::column_sort_vars("user_id", $sort_order, $sort_column);
    $col[1] = gdFunctionsGDSR::column_sort_vars("user_nicename", $sort_order, $sort_column);
    $col[2] = gdFunctionsGDSR::column_sort_vars("vote", $sort_order, $sort_column);
    $col[3] = gdFunctionsGDSR::column_sort_vars("voted", $sort_order, $sort_column);
    $col[4] = gdFunctionsGDSR::column_sort_vars("ip", $sort_order, $sort_column);
    $col[5] = gdFunctionsGDSR::column_sort_vars("user_agent", $sort_order, $sort_column);

?>

<table class="widefat">
    <thead>
        <tr>
            <th class="check-column" scope="col"><input type="checkbox" onclick="checkAll(document.getElementById('gdsr-articles'));"/></th>
            <th scope="col" nowrap="nowrap"><a href="<?php echo $b_url.$col[0]["url"]; ?>"<?php echo $col[0]["cls"]; ?>><?php _e("ID", "gd-star-rating"); ?></a></th>
            <th scope="col"><a href="<?php echo $b_url.$col[1]["url"]; ?>"<?php echo $col[1]["cls"]; ?>><?php _e("Name", "gd-star-rating"); ?></a></th>
            <th scope="col"><a href="<?php echo $b_url.$col[2]["url"]; ?>"<?php echo $col[2]["cls"]; ?>><?php _e("Vote", "gd-star-rating"); ?></a></th>
            <th scope="col"><a href="<?php echo $b_url.$col[3]["url"]; ?>"<?php echo $col[3]["cls"]; ?>><?php _e("Vote Date", "gd-star-rating"); ?></a></th>
            <th scope="col"><a href="<?php echo $b_url.$col[4]["url"]; ?>"<?php echo $col[4]["cls"]; ?>><?php _e("IP", "gd-star-rating"); ?></a></th>
            <th scope="col"><a href="<?php echo $b_url.$col[5]["url"]; ?>"<?php echo $col[5]["cls"]; ?>><?php _e("User Agent", "gd-star-rating"); ?></a></th>
        </tr>
    </thead>
    <tbody>
<?php

    $tr_class = "";
    foreach ($rows as $row) {
        if ($row->user_id == 0) $tr_class.= " visitor";
        if ($row->user_id == 1) $tr_class.= " admin";

        echo '<tr id="post-'.$row->record_id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<th scope="row" class="check-column"><input name="gdsr_item[]" value="'.$row->record_id.'" type="checkbox"></th>';
        echo '<td><strong>'.$row->user_id.'</strong></td>';
        echo '<td><strong>';
        echo $row->user_id == 0 ? "Visitor" : $row->user_nicename;
        echo '</strong></td>';
        echo '<td>'.($is_thumb && $row->vote > 0 ? "+" : "").$row->vote.'</td>';
        echo '<td>'.$row->voted.'</td>';
        echo '<td>'.$row->ip.'</td>';
        echo '<td>'.$row->user_agent.'</td>';

        echo '</tr>';
        $tr_class = $tr_class == "" ? "alternate " : "";
    }
    
?>
    </tbody>
</table>
<div class="tablenav" style="height: 3em">
    <div class="alignleft">
        <div class="panel">
            <table width="100%"><tr>
                <td style="width: 120px; height: 29px;">
                    <span class="paneltext"><strong><?php _e("With selected", "gd-star-rating"); ?>:</strong></span>
                </td>
                <td style="height: 29px;">
                    <span class="paneltext"><?php _e("Delete", "gd-star-rating"); ?>:</span>
                    <select id="gdsr_delete_voters" name="gdsr_delete_voters" style="margin-top: -4px; width: 150px;">
                        <option value="">/</option>
                        <option value="D"><?php _e("Full Delete", "gd-star-rating"); ?></option>
                        <option value="L"><?php _e("From Log Only", "gd-star-rating"); ?></option>
                    </select>
                </td>
                <td align="right" style="width: 80px; height: 29px;">
                    <input class="button-secondary delete" type="submit" name="gdsr_update" value="<?php _e("Update", "gd-star-rating"); ?>" style="margin-top: -4px;" />
                </td>
            </tr></table>
        </div>
    </div>
<br class="clear"/>
</div>
</form>
</div>