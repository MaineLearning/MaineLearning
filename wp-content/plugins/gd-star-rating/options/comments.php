<?php

global $wpdb, $gdsr;

$options = $gdsr->o;

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdsr=comments";

if (isset($_GET["postid"])) {
    $id = $_GET["postid"];
}

if ($_POST["gdsr_update"] == __("Update", "gd-star-rating")) {
    $gdsr_items = $_POST["gdsr_item"];
    if (count($gdsr_items) > 0) {
        $ids = "(".join(", ", $gdsr_items).")";
        if ($_POST["gdsr_delete_comments"] != '') 
            GDSRDatabase::delete_votes($ids, $_POST["gdsr_delete_comments"], $gdsr_items);
    }
}

?>

<div class="wrap">
<form id="gdsr-comments" method="post" action="">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("Comments", "gd-star-rating"); ?></h2>
<div>
<p><strong><?php _e("Comments for post", "gd-star-rating"); ?>:
    <?php echo ''; ?>
    <?php echo sprintf('<a href="./post.php?action=edit&amp;post=%s">%s</a> <a href="%s" target="_blank">[view]</a>', $id, gdsrAdmDB::get_post_title($id), get_permalink($id)); ?>
</strong></p>
<?php

if (isset($id)) {
    $url.= "&amp;gdsr=comments";

    $page_id = 1;
    if (isset($_GET["pg"])) $page_id = $_GET["pg"];

    $options = $gdsr->o;
    $posts_per_page = $options["admin_rows"];

    $number_posts = GDSRDatabase::get_comments_count($id);
    
    $max_page = floor($number_posts / $posts_per_page);
    if ($max_page * $posts_per_page != $number_posts) $max_page++;

    $pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

?>

<div class="tablenav">
    <div class="alignleft">
    </div>
    <div class="tablenav-pages">
        <?php echo $pager; ?>
    </div>
</div>
<br class="clear"/>

<?php

    $sql = GDSRDatabase::get_comments($id, ($page_id - 1) * $posts_per_page, $posts_per_page);
    $rows = $wpdb->get_results($sql, OBJECT);

?>

<table class="widefat">
    <thead>
        <tr>
            <th class="check-column" scope="col"><input type="checkbox" onclick="checkAll(document.getElementById('gdsr-moderation'));"/></th>
            <th scope="col"><?php _e("Comment", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("User", "gd-star-rating"); ?></th>
            <th scope="col" width="120"><?php _e("Date And Time", "gd-star-rating"); ?></th>
            <th scope="col" width="120"><?php _e("Thumbs", "gd-star-rating"); ?></th>
            <th scope="col" width="120"><?php _e("Votes", "gd-star-rating"); ?></th>
            <th scope="col" width="120"><?php _e("Total", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Review", "gd-star-rating"); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
    
    $tr_class = "";
    foreach ($rows as $row) {
        $row = gdsrAdmDB::convert_comment_row($row);

        if ($row->rating_total > $options["cmm_stars"] || $row->rating_visitors > $options["cmm_stars"] || $row->rating_users > $options["cmm_stars"])
            $tr_class.=" invalidarticle";

        echo '<tr id="post-'.$row->comment_id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<th scope="row" class="check-column"><input name="gdsr_item[]" value="'.$row->comment_id.'" type="checkbox"/></th>';
        echo '<td>'.$row->comment_content.'</td>';
        echo '<td><strong>'.$row->comment_author.'</strong></td>';
        echo '<td>'.$row->comment_date.'</td>';
        echo '<td>'.$row->thumbs.'</td>';
        echo '<td>'.$row->votes.'</td>';
        echo '<td>'.$row->total.'</td>';
        echo '<td>'.$row->review.'</td>';
        echo '</tr>';

        if ($tr_class == "")
            $tr_class = "alternate ";
        else
            $tr_class = "";
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
                    <select id="gdsr_delete_comments" name="gdsr_delete_comments" style="margin-top: -4px; width: 80px;">
                        <option value="">/</option>
                        <option value="KV"><?php _e("Visitors", "gd-star-rating"); ?></option>
                        <option value="KU"><?php _e("Users", "gd-star-rating"); ?></option>
                        <option value="KA"><?php _e("All", "gd-star-rating"); ?></option>
                    </select>
                </td>
                <td align="right" style="width: 80px; height: 29px;">
                    <input class="button-secondary delete" type="submit" name="gdsr_update" value="<?php _e("Update", "gd-star-rating"); ?>" style="margin-top: -3px" />
                </td>
            </tr></table>
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