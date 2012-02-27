<?php

$review_set = $options["mur_review_set"];

if (isset($_POST["gdsr_operation"]) && $_POST["gdsr_operation"] == __("Delete Selected", "gd-star-rating")) {
    $gdsr_items = $_POST["gdsr_item"];
    if (count($gdsr_items) > 0) {
        $ids = "(".join(", ", $gdsr_items).")";
        GDSRDBMulti::delete_sets($ids);
    }
}

$posts_per_page = $options["admin_rows"];

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdsr=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url_edit = $url."&gdsr=";
$url.= "&amp;gdsr=mulist";

$page_id = 1;

if (isset($_GET["pg"])) $page_id = $_GET["pg"];

$number_posts = GDSRDBMulti::get_multis_count();
$max_page = floor($number_posts / $posts_per_page);
if ($max_page * $posts_per_page != $number_posts) $max_page++;

$pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

?>

<script type="text/javascript">
function gdsrAddNewMulti() {
    window.location = "<?php echo $url_edit."munew"; ?>";
}
</script>

<div class="wrap">
<form method="post" action="admin.php?page=gd-star-rating-multi-sets">
<div class="gdsr">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("Multi Sets", "gd-star-rating"); ?></h2>

<div class="tablenav">
    <div class="alignleft">
        <input onclick="gdsrAddNewMulti()" class="inputbutton" style="width: 200px" type="button" name="gdsr_filter" value="<?php _e("Add New Multi Rating Set", "gd-star-rating"); ?>" />
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
            <th scope="col" width="33"><?php _e("ID", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Name", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Auto Insert", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Defaults", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Stars", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Ratings", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Review", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Statistics", "gd-star-rating"); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

    $rows = GDSRDBMulti::get_multis(($page_id - 1) * $posts_per_page, $posts_per_page);

    $tr_class = "";
    foreach ($rows as $row) {
        echo '<tr id="multi-'.$row->multi_id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<th scope="row" class="check-column"><input name="gdsr_item[]" value="'.$row->multi_id.'" type="checkbox"></th>';
        echo '<td>'.$row->multi_id.'</td>';
        echo '<td><a href="'.$url_edit.'muedit&amp;id='.$row->multi_id.'"><strong>'.$row->name.'</strong></a></td>';
        echo '<td>';
        switch ($row->auto_insert) {
            default:
            case "none":
                _e("No", "gd-star-rating");
                break;
            case "cats":
                _e("Category Based", "gd-star-rating");
                break;
            case "apst":
                _e("All Posts", "gd-star-rating");
                break;
            case "apgs":
                _e("All Pages", "gd-star-rating");
                break;
            case "allp":
                _e("All Posts &amp; Pages", "gd-star-rating");
                break;
        }
        if ($row->auto_insert != "none") {
            echo '<br />[';
            switch ($row->auto_location) {
                case "top":
                    _e("Top", "gd-star-rating");
                    break;
                case "bottom":
                    _e("Bottom", "gd-star-rating");
                    break;
            }
            echo ']';
        }
        echo '</td>';
        echo '<td>'.$row->description.'</td>';
        echo '<td><strong>'.$row->stars.'</strong></td>';
        echo '<td>';
            $elements = unserialize($row->object);
            $weights = unserialize($row->weight);
            $half = floor(count($elements) / 2);
            if ($half * 2 < count($elements)) $half++;
            echo '<table style="width: 100%;"><tr><td style="border: 0; padding: 0; width: 50%;">';
            for ($i = 0; $i < $half; $i++)
                echo sprintf("[%s] %s (%s)<br />", $i+1, $elements[$i], $weights[$i]);
            echo '</td><td style="border: 0; padding: 0; width: 50%;">';
            for ($i = $half; $i < count($elements); $i++)
                echo sprintf("[%s] %s (%s)<br />", $i+1, $elements[$i], $weights[$i]);
            echo '</td></tr></table>';
        echo '</td>';
        echo '<td>';
            $rvw_counter = intval(GDSRDBMulti::get_usage_count_post_reviews($row->multi_id));
            if ($rvw_counter > 0)
                $rvw_counter = sprintf('<a href="./admin.php?page=gd-star-rating-multi-sets&amp;gdsr=murpost&amp;sid=%s"><strong style="color: red;">%s</strong></a>', $row->multi_id, $rvw_counter);
            echo sprintf("[ <strong>%s</strong> ] %s", $rvw_counter, __("Posts", "gd-star-rating"));
        echo '</td>';
        echo '<td>';
            $usg_counter = intval(GDSRDBMulti::get_usage_count_posts($row->multi_id));
            $vtr_counter = intval(GDSRDBMulti::get_usage_count_voters($row->multi_id));
            if ($usg_counter > 0)
                $usg_counter = sprintf('<a href="./admin.php?page=gd-star-rating-multi-sets&amp;gdsr=murpost&amp;sid=%s"><strong style="color: red;">%s</strong></a>', $row->multi_id, $usg_counter);
            if ($vtr_counter > 0)
                $vtr_counter = sprintf('<a href="./admin.php?page=gd-star-rating-multi-sets&amp;gdsr=murset&amp;sid=%s"><strong style="color: red;">%s</strong></a>', $row->multi_id, $vtr_counter);
            echo sprintf("[ <strong>%s</strong> ] %s<br />", $usg_counter, __("Posts", "gd-star-rating"));
            echo sprintf("[ <strong>%s</strong> ] %s", $vtr_counter, __("Voters", "gd-star-rating"));
        echo '</td>';
        echo '</tr>';
        
        if ($tr_class == "")
            $tr_class = "alternate ";
        else
            $tr_class = "";
    }

?>

    </tbody>
</table>
<br class="clear"/>

<div class="tablenav">
    <div class="alignleft">
        <input class="inputbutton" type="submit" name="gdsr_operation" value="<?php _e("Delete Selected", "gd-star-rating"); ?>" />
    </div>
    <div class="tablenav-pages">
        <?php echo $pager; ?>
    </div>
</div>
<br class="clear"/>
</div>
</form>
</div>
