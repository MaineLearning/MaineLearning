<input type="hidden" id="gdstarr-divimage[<?php echo $wpnm; ?>]" name="<?php echo $wpfn; ?>[div_image]" value="<?php echo $wpno['div_image'] ?>" />
<div id="gdstarr-divimage-off[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_image'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divimage', '<?php echo $wpnm; ?>')"><?php _e("Image", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="gdstarr-divimage-on[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_image'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divimage', '<?php echo $wpnm; ?>')"><?php _e("Image", "gd-star-rating"); ?></a></strong></td>
    <td width="150" nowrap="nowrap"><?php _e("Resize Image", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 47px" type="text" name="<?php echo $wpfn; ?>[image_resize_x]" value="<?php echo $wpno["image_resize_x"]; ?>" /> x
        <input class="widefat" style="text-align: right; width: 47px" type="text" name="<?php echo $wpfn; ?>[image_resize_y]" value="<?php echo $wpno["image_resize_y"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td colspan="2"><?php _e("This will work only if image is stored on the server.", "gd-star-rating"); ?></td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Get Image From", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $wpfn; ?>[image_from]" style="width: 110px" id="gdstarr-imagefrom" onchange="gdsrChangeImage(this.options[this.selectedIndex].value, '<?php echo $wpnm; ?>')">
            <option value="none"<?php echo $wpno['image_from'] == 'none' ? ' selected="selected"' : ''; ?>><?php _e("No image", "gd-star-rating"); ?></option>
            <option value="custom"<?php echo $wpno['image_from'] == 'custom' ? ' selected="selected"' : ''; ?>><?php _e("Custom field", "gd-star-rating"); ?></option>
            <option value="content"<?php echo $wpno['image_from'] == 'content' ? ' selected="selected"' : ''; ?>><?php _e("Post content", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="gdsr-img-none[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['image_from'] == 'none' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"></td>
    <td align="left"><?php _e("If you use %IMAGE% tag in template and this option is selected, image will not be rendered.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="gdsr-img-custom[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['image_from'] == 'custom' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"></td>
    <td width="100" nowrap="nowrap"><?php _e("Custom Field", "gd-star-rating"); ?>:</td>
    <td align="right"><?php GDSRHelper::render_custom_fields($wpfn."[image_custom]", $wpno['image_custom'], 200); ?></td>
  </tr>
</table>
</div>
<div id="gdsr-img-content[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['image_from'] == 'content' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"></td>
    <td align="left"><?php _e("First image from post content will be used for %IMAGE% tag.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
</div>
<div class="gdsr-table-split"></div>
