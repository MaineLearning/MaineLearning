<?php

if ($gdsr_page == "munew") {
    $edit_id = 0;
    $set = new GDMultiSingle();
}
else {
    $edit_id = $_GET["id"];
    $set = gd_get_multi_set($edit_id);
}

$review_set = $options["mur_review_set"];

?>
<script type="text/javascript">
    function gdsrMultiCats(el) {
        document.getElementById("gdsr_ms_autocats").style.display = el == "cats" ? "block" : "none";
    }
</script>
<div class="wrap">
<form method="post">
<input type="hidden" id="gdsr_action" name="gdsr_action" value="save" />
<input type="hidden" id="gdsr_ms_id" name="gdsr_ms_id" value="<?php echo $set->multi_id; ?>" />
<div class="gdsr">
<h2 class="gdptlogopage">GD Star Rating: <?php _e("Multi Sets Editor", "gd-star-rating"); ?></h2>

<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Name", "gd-star-rating"); ?></th>
    <td>
        <input maxlength="64" type="text" name="gdsr_ms_name" id="gdsr_ms_name" value="<?php echo html_entity_decode($set->name, ENT_QUOTES, STARRATING_ENCODING); ?>" style="width: 300px" />
    </td>
</tr>
<tr><th scope="row"><?php _e("Description", "gd-star-rating"); ?></th>
    <td>
        <input maxlength="256" type="text" name="gdsr_ms_description" id="gdsr_ms_description" value="<?php echo html_entity_decode($set->description, ENT_QUOTES, STARRATING_ENCODING); ?>" style="width: 700px" />
    </td>
</tr>
<tr><th scope="row"><?php _e("Number Of Stars", "gd-star-rating"); ?></th>
    <td>
        <select<?php if ($gdsr_page == "muedit") echo ' disabled="disabled"'; ?> style="width: 70px;" name="gdsr_ms_stars" id="gdsr_ms_stars">
            <?php GDSRHelper::render_stars_select($set->stars); ?>
        </select>
    </td>
</tr>
<tr><th scope="row"><?php _e("Defaults", "gd-star-rating"); ?></th>
    <td>
    </td>
</tr>
<tr><th scope="row"><?php _e("Auto Insertion", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td style="width: 150px"><?php _e("Insertion", "gd-star-rating"); ?>:</td>
                <td>
                    <select name="gdsr_ms_autoinsert" id="gdsr_ms_autoinsert" style="width: 150px" onchange="gdsrMultiCats(this.options[this.selectedIndex].value)">
                        <option value="none"<?php echo isset($set->auto_insert) && $set->auto_insert == 'none' ? ' selected="selected"' : ''; ?>><?php _e("No", "gd-star-rating"); ?></option>
                        <option value="cats"<?php echo isset($set->auto_insert) && $set->auto_insert == 'cats' ? ' selected="selected"' : ''; ?>><?php _e("Category Based", "gd-star-rating"); ?></option>
                        <option value="apst"<?php echo isset($set->auto_insert) && $set->auto_insert == 'apst' ? ' selected="selected"' : ''; ?>><?php _e("All Posts", "gd-star-rating"); ?></option>
                        <option value="apgs"<?php echo isset($set->auto_insert) && $set->auto_insert == 'apgs' ? ' selected="selected"' : ''; ?>><?php _e("All Pages", "gd-star-rating"); ?></option>
                        <option value="allp"<?php echo isset($set->auto_insert) && $set->auto_insert == 'allp' ? ' selected="selected"' : ''; ?>><?php _e("All Posts &amp; Pages", "gd-star-rating"); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="width: 150px"><?php _e("Location", "gd-star-rating"); ?>:</td>
                <td>
                    <select name="gdsr_ms_autolocation" id="gdsr_ms_autolocation" style="width: 150px">
                        <option value="bottom"<?php echo isset($set->auto_location) && $set->auto_location == 'bottom' ? ' selected="selected"' : ''; ?>><?php _e("Bottom", "gd-star-rating"); ?></option>
                        <option value="top"<?php echo isset($set->auto_location) && $set->auto_location == 'top' ? ' selected="selected"' : ''; ?>><?php _e("Top", "gd-star-rating"); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <div id="gdsr_ms_autocats" style="display: <?php echo $set->auto_insert == 'cats' ? "block" : "none"; ?>">
            <div class="gdsr-table-split"></div>
            <table cellpadding="0" cellspacing="0" class="previewtable">
                <tr>
                    <td style="width: 150px"><?php _e("Categories", "gd-star-rating"); ?>:</td>
                    <td>
                        <input type="text" name="gdsr_ms_autocategories" id="gdsr_ms_autocategories" value="<?php if (isset($set->auto_categories)) echo $set->auto_categories; ?>" style="width: 400px" />
                         [comma separated list of category ID's]
                    </td>
                </tr>
            </table>
        </div>
    </td>
</tr>
<tr><th scope="row"><?php _e("Elements", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <?php for ($i = 0; $i < count($set->object); $i++) { $counter = $i + 1; if ($counter < 10) $counter = "0".$counter;  ?>
            <tr>
                <td width="50">[ <?php echo $counter; ?> ]</td>
                <td width="100"><?php _e("Name", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_ms_element[<?php echo $i; ?>]" id="gdsr_ms_element_<?php echo $i; ?>" value="<?php echo html_entity_decode($set->object[$i], ENT_QUOTES, STARRATING_ENCODING); ?>" style="width: 200px" /></td>
                <td width="20"></td>
                <td width="100"><?php _e("Weight", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_ms_weight[<?php echo $i; ?>]" id="gdsr_ms_weight_<?php echo $i; ?>" value="<?php echo $set->weight[$i]; ?>" style="width: 50px; text-align: right;" /></td>
            </tr>
            <?php } ?>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Important Notice", "gd-star-rating"); ?></th>
    <td>
        <?php _e("Once the set is created, you can't change number of elements in the set or number of stars! You can edit names and weight of the elements, name and description of the set.", "gd-star-rating"); ?>
    </td>
</tr>
</tbody></table>

<p class="submit"><input type="submit" value="<?php _e("Save Multi Set", "gd-star-rating"); ?>" name="gdsr_saving"/></p>
</div>
</form>
</div>
