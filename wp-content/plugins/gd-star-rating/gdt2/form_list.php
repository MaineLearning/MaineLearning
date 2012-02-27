<?php

$posts_per_page = 50;

$url = $_SERVER['REQUEST_URI'];
$url_pos = strpos($url, "&gdt=");
if (!($url_pos === false))
    $url = substr($url, 0, $url_pos);

$url.= "&amp;gdt=t2";

$filter_section = "";
$page_id = 1;
if (isset($_GET["pg"])) $page_id = $_GET["pg"];

if (isset($_POST["gdsr_filter"]) && $_POST["gdsr_filter"] == __("Filter", "gd-star-rating")) {
    $filter_section = $_POST["filter_section"];
    $page_id = 1;
}

$number_posts = gdTemplateDB::get_templates_count($filter_section);
$max_page = floor($number_posts / $posts_per_page);
if ($max_page * $posts_per_page != $number_posts) $max_page++;
$pager = $max_page > 1 ? gdFunctionsGDSR::draw_pager($max_page, $page_id, $url, "pg") : "";

$all_sections = $tpls->list_sections_assoc();

?>

<div class="tablenav">
    <div class="alignleft">
        <form method="post">
            <?php gdTemplateHelper::render_templates_sections("filter_section", $tpls->list_sections(), true, $filter_section) ?>
            <input class="inputbutton inputfilter" type="submit" name="gdsr_filter" value="<?php _e("Filter", "gd-star-rating"); ?>" />
        </form>
    </div>
    <div class="tablenav-pages"><?php echo $pager; ?></div>
</div>
<br class="clear"/>

<table class="widefat">
    <thead>
        <tr>
            <th scope="col" width="33"><?php _e("ID", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Name", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Section / Type", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Tag", "gd-star-rating"); ?></th>
            <th scope="col" style="text-align: right"><?php _e("Options", "gd-star-rating"); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

    $templates = gdTemplateDB::get_templates_paged($filter_section, ($page_id - 1) * $posts_per_page, $posts_per_page);

    $tr_class = "";
    foreach ($templates as $t) {
        $mode = $t->preinstalled == "0" ? "edit" : "copy";
        $url = "admin.php?page=gd-star-rating-t2&amp;";
        echo '<tr id="post-'.$t->template_id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<td><strong>'.$t->template_id.'</strong></td>';
        echo '<td><strong><a href="'.$url.'mode='.$mode.'&amp;tplid='.$t->template_id.'">'.$t->name.'</a></strong></td>';
        echo '<td>'.$all_sections[$t->section].' ['.$t->section.']'.'</td>';
        echo '<td>'.$tpls->find_template_tag($t->section).'</td>';
        echo '<td style="text-align: right">';
        if ($t->preinstalled == "0") {
            echo '<a href="'.$url.'deltpl='.$t->template_id.'">'.__("delete", "gd-star-rating").'</a> | ';
            echo '<a href="'.$url.'mode=edit&amp;tplid='.$t->template_id.'">'.__("edit", "gd-star-rating").'</a> | ';
        }
        echo '<a href="'.$url.'mode=copy&amp;tplid='.$t->template_id.'">'.__("duplicate", "gd-star-rating").'</a>';
        echo '</td>';
        echo '</tr>';

        if ($tr_class == "") $tr_class = "alternate ";
        else $tr_class = "";
    }

?>

    </tbody>
</table>
<br class="clear"/>

<div class="tablenav">
    <div class="alignleft">
        <?php _e("Map with dependencies for templates", "gd-star-rating"); ?>:<br />
        <a style="font-weight: bold" target="_blank" href="<?php echo STARRATING_URL; ?>info/t2map.html"><?php _e("T2 Templates Map", "gd-star-rating") ?></a>
    </div>
    <div class="tablenav-pages">
        <form method="post">
            <table cellpadding="0" cellspacing="0"><tr><td>
            <?php _e("New template for:", "gd-star-rating"); ?> </td><td>
            <?php gdTemplateHelper::render_templates_sections("tpl_section", $tpls->list_sections(), false) ?>
            <input class="inputbutton inputfilter" type="submit" name="gdsr_create" value="<?php _e("Create", "gd-star-rating"); ?>" />
            </td></tr></table>
        </form>
    </div>
</div>
