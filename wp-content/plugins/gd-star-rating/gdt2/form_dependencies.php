<?php

$all_sections = $tpls->list_sections_assoc();

?>

<form method="post">
<table class="widefat">
    <thead>
        <tr>
            <th scope="col" width="33"><?php _e("ID", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Type", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Name", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Dependencies", "gd-star-rating"); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

    $templates = gdTemplateDB::get_templates_dep();

    $tr_class = "";
    foreach ($templates as $t) {
        $dependencies = unserialize($t->dependencies);
        if (is_array($dependencies)) {
            echo '<tr id="post-'.$t->template_id.'" class="'.$tr_class.' author-self status-publish" valign="top">';
            echo '<td><strong>'.$t->template_id.'</strong></td>';
            echo '<td>'.$t->section.'</td>';
            echo '<td>'.$t->name.'</td>';
            echo '<td>';
            gdTemplateHelper::prepare_dependencies($all_sections, $templates, $dependencies, $t->template_id);
            echo '</td>';
            echo '</tr>';

            if ($tr_class == "") $tr_class = "alternate ";
            else $tr_class = "";
        }
    }

?>

    </tbody>
</table>
<br class="clear"/>
<div class="tablenav">
    <div class="alignleft">
        <input class="inputbutton" type="submit" name="gdsr_setdepends" value="<?php _e("Save Changes", "gd-star-rating"); ?>" />
    </div>
    <div class="tablenav-pages">
    </div>
</div>
</form>
