<?php global $gdsr; $wpv = $gdsr->wp_version; $gdsr_tinymce = false; ?>
<div class="gdsr">
<div class="wrap">
    <h2 class="gdptlogopage">GD Star Rating: <?php _e("Builder", "gd-star-rating"); ?></h2>

<table cellpadding="0" cellspacing="7">
    <tr>
        <td class="tpl-editor-title"><h3><?php _e("Shortcode/Functions", "gd-star-rating"); ?></h3></td>
        <td class="tpl-editor-title"><h3><?php _e("Settings", "gd-star-rating"); ?></h3></td>
    </tr><tr>
        <td class="tpl-editor-select-td">
            <?php include(STARRATING_PATH.'options/builder/shortcode.php'); ?>
        </td>
        <td class="tpl-editor-settin-td">
            <?php include(STARRATING_PATH.'options/builder/settings.php'); ?>
        </td>
    </tr>
</table>
</div>
</div>
