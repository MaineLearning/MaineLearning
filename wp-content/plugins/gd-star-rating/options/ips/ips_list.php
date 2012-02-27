<?php

$posts_per_page = $options["admin_rows"];

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$log_url = $url."&amp;gdsr=iplist";
$url.= "&amp;gdsr=iplist";

$page_id = 1;
if (isset($_GET["pg"])) $page_id = $_GET["pg"];

$number_posts = gdsrAdmDB::get_all_banned_ips_count();

$max_page = floor($number_posts / $posts_per_page);
if ($max_page * $posts_per_page != $number_posts) $max_page++;

$pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

?>

<div class="wrap">
<form id="gdsr-articles" method="post" action="">
<input type="hidden" name="action" value="deletebans" />
<div class="tablenav">
    <div class="alignleft">
    </div>
    <div class="tablenav-pages">
        <?php echo $pager; ?>
    </div>
</div>
<br class="clear"/>
<table class="widefat">
    <thead>
        <tr>
            <th class="check-column" scope="col"><input type="checkbox" onclick="checkAll(document.getElementById('gdsr-articles'));"/></th>
            <th scope="col"><?php _e("Record Id", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Type", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("IP", "gd-star-rating"); ?></th>
        </tr>
    </thead>
    <tbody>
<?php

    $rows = gdsrAdmDB::get_all_banned_ips(($page_id - 1) * $posts_per_page, $posts_per_page);
    $tr_class = "";
    foreach ($rows as $row) {
        echo '<tr id="post-'.$row->id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<th scope="row" class="check-column"><input name="gdsr_item[]" value="'.$row->id.'" type="checkbox"></th>';
        echo '<td>'.$row->id.'</td>';
        echo '<td><strong>';
        switch ($row->mode) {
            case "S":
                echo "Single";
                break;
            case "M":
                echo "Masked";
                break;
            case "R":
                echo "Range";
                break;
        }
        echo '</strong></td>';
        echo '<td>'.$row->ip.'</td>';
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
        <input type="submit" class="inputbutton" value="<?php _e("Unban Selected", "gd-star-rating"); ?>" name="gdsr_unban" id="gdsr_unban" />
    </div>
    <div class="tablenav-pages">
    </div>
</div>
<br class="clear"/>
</form>
</div>