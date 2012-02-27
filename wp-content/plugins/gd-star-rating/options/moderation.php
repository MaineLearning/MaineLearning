<?php

require_once(STARRATING_PATH."/code/blg/db.php");

global $wpdb, $gdsr;

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdsr=moderation";

if (isset($_GET["pid"])) {
    $id = $_GET["pid"];
    $vt = $_GET["vt"];
}

?>

<div class="wrap">
<form id="gdsr-moderation" method="post" action="">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("Moderation", "gd-star-rating"); ?></h2>
<div>
<p><strong>
    <?php
        if ($vt == "post")
            echo "Queue for all comments for a post.";
        if ($vt == "article")
            echo "Queue for article.";
        if ($vt == "comment")
            echo "Queue for comment.";
    ?>
</strong></p>
<?php

if (isset($id)) {
    $url.= "&amp;gdsr=moderate";
    
    $page_id = 1;
    if (isset($_GET["pg"])) $page_id = $_GET["pg"];
    if (isset($_GET["usr"])) $filter_user = $_GET["usr"];

    if (isset($_POST["gdsr_filter"]) && $_POST["gdsr_filter"] == "Filter") {
        $filter_user = $_POST["gdsr_users"];
    }
    else
        $filter_user = 'all';
    
    $url.= "&amp;usr=".$filter_user;
    
    if (isset($_POST["gdsr_update"]) && $_POST["gdsr_update"] == "Update") {
        $gdsr_items = $_POST["gdsr_item"];
        if (count($gdsr_items) > 0) {
            $ids = "(".join(", ", $gdsr_items).")";
            $mod = $_POST["gdsr_moderate"];
            if ($mod == "delete")
                gdsrAdmDB::moderation_delete($ids);
            if ($mod == "approve") 
                gdsrAdmDB::moderation_approve($ids, $gdsr_items);
        }
    }
    
    $options = $gdsr->o;
    $posts_per_page = $options["admin_rows"];

    if ($vt == "post")
        $number_posts = gdsrAdmDB::get_moderation_count_joined($id, $filter_user);
    else
        $number_posts = gdsrAdmDB::get_moderation_count($id, $vt, $filter_user);

    $max_page = floor($number_posts / $posts_per_page);
    if ($max_page * $posts_per_page != $number_posts) $max_page++;

    $pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

?>
    
<div class="tablenav">
    <div class="alignleft">
<?php gdsrAdmDB::get_combo_users($filter_user); ?>
        <input class="button-secondary delete" type="submit" name="gdsr_filter" value="Filter" />
    </div>
    <div class="tablenav-pages">
        <?php echo $pager; ?>
    </div>
</div>
<br class="clear"/>
<?php

    if ($vt == "post")
        $sql = gdsrAdmDB::get_moderation_joined($id, ($page_id - 1) * $posts_per_page, $posts_per_page, $filter_user);
    else
        $sql = gdsrAdmDB::get_moderation($id, $vt, ($page_id - 1) * $posts_per_page, $posts_per_page, $filter_user);
    $rows = $wpdb->get_results($sql, OBJECT);
    
?>
<table class="widefat">
    <thead>
        <tr>
            <th class="check-column" scope="col"><input type="checkbox" onclick="checkAll(document.getElementById('gdsr-moderation'));"/></th>
            <th scope="col"><?php _e("Date And Time", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("User", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Vote", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("IP", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("User Agent", "gd-star-rating"); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
    $tr_class = "";
    
    foreach ($rows as $row) {
        $row = gdsrAdmDB::convert_moderation_row($row);
        echo '<tr id="post-'.$row->record_id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<th scope="row" class="check-column"><input name="gdsr_item[]" value="'.$row->record_id.'" type="checkbox"></th>';
        echo '<td><strong>'.$row->voted.'</strong></td>';
        echo '<td>'.$row->username.'</td>';
        echo '<td><strong>'.$row->vote.'</strong></td>';
        echo '<td>'.$row->ip.'</td>';
        echo '<td>'.$row->user_agent.'</td>';
        echo '</tr>';
        
        if ($tr_class == "")
            $tr_class = "alternate ";
        else
            $tr_class = "";
    }
?>
    </tbody>
</table>
<div class="tablenav">
    <div class="alignleft">
        <div class="panel">
            <span class="paneltext"><strong><?php _e("With Selected", "gd-star-rating"); ?>:</strong></span>
            <select id="gdsr_moderate" name="gdsr_moderate" style="margin-top: -4px; width: 80px;">
                <option value="">/</option>
                <option value="approve"><?php _e("Approve", "gd-star-rating"); ?></option>
                <option value="delete"><?php _e("Delete", "gd-star-rating"); ?></option>
            </select>
            <input class="button-secondary delete" type="submit" name="gdsr_update" value="Update" style="margin-top: -3px" />
        </div>
    </div>
<br class="clear"/>
</div>

    <?php
}
?>

</div>
</form>
</div>