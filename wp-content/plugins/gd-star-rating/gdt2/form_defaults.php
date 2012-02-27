<form method="post">
<table class="widefat">
    <thead>
        <tr>
            <th scope="col" style="width: 80px"><?php _e("ID", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Section / Type", "gd-star-rating"); ?></th>
            <th scope="col"><?php _e("Template", "gd-star-rating"); ?></th>
            <th scope="col" style="text-align: right"><?php _e("Options", "gd-star-rating"); ?></th>
        </tr>
    </thead>
    <tbody>

<?php

    $tr_class = "";
    foreach ($tpls->tpls as $t) {
        echo '<tr id="post-'.$t->code.'" class="'.$tr_class.' author-self status-publish" valign="top">';
        echo '<td>'.$t->code.'</td>';
        echo '<td><strong>'.$t->section.'</strong></td>';
        echo '<td>';
            gdTemplateHelper::render_templates_section($t->code, "gdsr_section[".$t->code."]", 0, 350);
        echo '</td>';
        echo '<td></td>';
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
        <input class="inputbutton" type="submit" name="gdsr_setdefaults" value="<?php _e("Save Changes", "gd-star-rating"); ?>" />
    </div>
    <div class="tablenav-pages">
    </div>
</div>
</form>
