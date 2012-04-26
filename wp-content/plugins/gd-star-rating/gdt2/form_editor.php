<div class="wrap"><h2 class="gdptlogopage">GD Star Rating: T2 <?php _e("Template Editor", "gd-star-rating"); ?></h2>

<?php

$section = '';
$all_sections = array();
$raw_sections = $tpls->list_sections();
foreach ($raw_sections as $value) {
    $all_sections[] = $value['code'];
}

if ($id == 0) {
    $section = $_POST['tpl_section'];
    $section = substr($section, 0, 3);

    $tpl = new stdClass();
    $tpl->section = $section;
    $tpl->preinstalled = 0;
    $tpl->default = 0;
    $tpl->name = 'New Template';
    $tpl->description = '';
} else {
    $tpl = wp_gdtpl_get_template($id);

    if (is_object($tpl)) {
        $section = $tpl->section;
        $elements = unserialize($tpl->elements);
        $dependencies = unserialize($tpl->dependencies);
    }
}

if (!in_array($section, $all_sections)) {
    echo '<h3>'.__("Your request is not valid.").'</h3>';
} else {

if ($mode == 'copy') {
    $id = 0;
    $tpl->name = 'New Template';
    $tpl->description = '';
} else if ($mode == 'edit' && $tpl->preinstalled == "1") {
    $id = 0;
}

$template = $tpls->get_list($section);

if (!isset($tpl->elements)) {
    $elements = array();
    foreach ($template->parts as $part) {
        $elements[$part->code] = '';
    }
}

if (!isset($tpl->dependencies)) {
    $dependencies = array();
    foreach ($template->tpls as $part) {
        $dependencies[$part->code] = 0;
    }
}

?>

<form method="post">
<input type="hidden" name="gdsr_save_tpl" value="" />
<input type="hidden" name="tpl_section" value="<?php echo $section ?>" />
<input type="hidden" name="tpl_id" value="<?php echo $id ?>" />
<div class="gdsr">
<table width="100%" cellpadding="0" cellspacing="7"><tr>
<td class="tpl-editor-title">
    <h3><?php _e("Editor", "gd-star-rating"); ?></h3>
</td><td class="tpl-editor-title">
    <h3><?php _e("Elements", "gd-star-rating"); ?></h3>
</td>
</tr><tr>
<td class="tpl-editor-form-td">
<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Info", "gd-star-rating"); ?></th>
    <td>
        <strong><?php echo $section; ?></strong>: <?php echo $template->section; ?>
        <div class="gdsr-table-split"></div>
        <?php echo $mode == "edit" ? "Editing: ".$tpl->name : __("Creating new template", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Options", "gd-star-rating"); ?></th>
    <td>
        <input type="checkbox" name="tpl_default_rewrite" id="tpl_default_rewrite" /><label style="margin-left: 5px;" for="tpl_default_rewrite"><?php _e("Set this template as default for this type of templates.", "gd-star-rating"); ?></label>
        <?php if ($template->tag != "") { ?>
        <br />
        <input type="checkbox" name="tpl_dep_rewrite" id="tpl_dep_rewrite" /><label style="margin-left: 5px;" for="tpl_dep_rewrite"><?php _e("Set this template as a dependency for all other templates that use it.", "gd-star-rating"); ?></label>
        <?php } ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("General", "gd-star-rating"); ?></th>
    <td>
        <?php _e("Name", "gd-star-rating"); ?>:<br />
        <input type="text" name="tpl_gen_name" id="tpl_gen_name" value="<?php echo $tpl->name; ?>" style="width: 500px" /><br />
        <?php _e("Description", "gd-star-rating"); ?>:<br />
        <textarea rows="3" name="tpl_gen_desc" style="width: 500px"><?php echo $tpl->description; ?></textarea>
    </td>
</tr>
<tr><th scope="row"><?php _e("Template", "gd-star-rating"); ?></th>
    <td>
<?php

$lines = count($template->parts) - 1;
foreach ($template->parts as $p) {
    echo '<p class="tpl-edit-name">'.$p->name.":</p>";
    if ($p->size == "single") echo '<input type="text" name="tpl_element['.$p->code.']" value="'.esc_attr($elements[$p->code]).'" style="width: 500px" /><br />';
    else echo '<textarea rows="6" style="width: 500px" name="tpl_element['.$p->code.']">'.esc_html($elements[$p->code]).'</textarea><br />';
    if ($p->description != "") echo '<strong>'.__("Description", "gd-star-rating").':</strong><br />';
    echo '<strong>'.__("Allowed elements", "gd-star-rating").':</strong> ';
    if (is_array($p->elements)) {
        for ($i = 0; $i < count($p->elements); $i++) {
            echo $p->elements[$i];
            if ($i < count($p->elements) - 1) echo ', ';
        }
    }
    else {
        if ($p->elements == "all") _e("All tag elements", "gd-star-rating");
        else _e("No tag elements allowed", "gd-star-rating");
    }
    if ($lines > 0) {
        echo '<div class="gdsr-table-split"></div>';
        $lines--;
    }
}

?>
    </td>
</tr>
</tbody></table>
<input type="submit" class="inputbutton" value="<?php _e("Save Template", "gd-star-rating"); ?>" name="gdsr_saving"/>
</td><td class="tpl-editor-list-td">
<?php

foreach ($template->elements as $el) {
    echo '<div class="tpl-element-single">';
    echo '<p class="tpl-element-tag">'.$el->tag.'</p>';
    echo '<p class="tpl-element-desc">'.$el->description.'</p>';
    if ($el->tpl > -1) {
        echo '<div class="tpl-element-single-select"><p>';
        _e("Select template to use for this element:", "gd-star-rating");
        $section = $template->tpls[$el->tpl]->code;
        echo '</p>';
        gdTemplateHelper::render_templates_section($section, "tpl_tpl[".$section."]", $dependencies[$section]);
        echo '</div>';
    }
    echo '</div>';
}

?>
</td>
</tr></table>
</div>
</form>

<?php } ?>

</div>
