<?php

if (isset($_POST["gdsr_t2imp_own"])) {
    if (is_uploaded_file($_FILES["gdsr_t2_import_own"]["tmp_name"])) {
        $t2 = file($_FILES["gdsr_t2_import_own"]["tmp_name"]);
        unlink($_FILES["gdsr_t2_import_own"]["tmp_name"]);
        gdTemplateDB::import_templates_own($t2);
    }
}

if (isset($_POST["gdsr_t2imp_all"])) {
    if (is_uploaded_file($_FILES["gdsr_t2_import_all"]["tmp_name"])) {
        $t2 = file_get_contents($_FILES["gdsr_t2_import_all"]["tmp_name"]);
        unlink($_FILES["gdsr_t2_import_all"]["tmp_name"]);
        gdTemplateDB::import_templates_full($t2);
    }
}

?>

<div class="wrap"><h2 class="gdptlogopage">GD Star Rating: <?php _e("T2 Templates", "gd-star-rating"); ?></h2>
<div class="gdsr">

<div id="gdsr_tabs" class="gdsrtabs">
<ul>
    <li><a href="#fragment-1"><span><?php _e("Templates", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-2"><span><?php _e("Dependencies", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-3"><span><?php _e("Defaults", "gd-star-rating"); ?></span></a></li>
    <li><a href="#fragment-4"><span><?php _e("Tools", "gd-star-rating"); ?></span></a></li>
</ul>
<div style="clear: both"></div>
<div id="fragment-1">
<?php include STARRATING_PATH."gdt2/form_list.php"; ?>
</div>
<div id="fragment-2">
<?php include STARRATING_PATH."gdt2/form_dependencies.php"; ?>
</div>
<div id="fragment-3">
<?php include STARRATING_PATH."gdt2/form_defaults.php"; ?>
</div>
<div id="fragment-4">
<?php include STARRATING_PATH."gdt2/form_files.php"; ?>
</div>
</div>

</div>
</div>