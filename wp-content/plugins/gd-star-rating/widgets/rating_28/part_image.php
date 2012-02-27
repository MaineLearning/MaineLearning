<input type="hidden" id="<?php echo $this->get_field_id('div_image'); ?>" name="<?php echo $this->get_field_name('div_image'); ?>" value="<?php echo $instance['div_image'] ?>" />
<div id="<?php echo $this->get_field_id('div_image'); ?>-off" style="display: <?php echo $instance['div_image'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_image'); ?>')"><?php _e("Image", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_id('div_image'); ?>-on" style="display: <?php echo $instance['div_image'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_image'); ?>')"><?php _e("Image", "gd-star-rating"); ?></a></strong></td>
    <td width="150" nowrap="nowrap"><?php _e("Resize Image", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 47px" type="text" name="<?php echo $this->get_field_name('image_resize_x'); ?>" id="<?php echo $this->get_field_id('image_resize_x'); ?>" value="<?php echo $instance["image_resize_x"]; ?>" /> x
        <input class="widefat" style="text-align: right; width: 47px" type="text" name="<?php echo $this->get_field_name('image_resize_y'); ?>" id="<?php echo $this->get_field_id('image_resize_y'); ?>" value="<?php echo $instance["image_resize_y"]; ?>" />
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
        <select name="<?php echo $this->get_field_name('image_from'); ?>" style="width: 110px" id="<?php echo $this->get_field_id('image_from'); ?>" onchange="gdsrChangeImage('<?php echo $this->get_field_id('image_from'); ?>', this.options[this.selectedIndex].value)">
            <option value="none"<?php echo $instance['image_from'] == 'none' ? ' selected="selected"' : ''; ?>><?php _e("No image", "gd-star-rating"); ?></option>
            <option value="custom"<?php echo $instance['image_from'] == 'custom' ? ' selected="selected"' : ''; ?>><?php _e("Custom field", "gd-star-rating"); ?></option>
            <option value="content"<?php echo $instance['image_from'] == 'content' ? ' selected="selected"' : ''; ?>><?php _e("Post content", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="<?php echo $this->get_field_id('image_from'); ?>-none" style="display: <?php echo $instance['image_from'] == 'none' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"></td>
    <td align="left"><?php _e("If you use %IMAGE% tag in template and this option is selected, image will not be rendered.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_id('image_from'); ?>-custom" style="display: <?php echo $instance['image_from'] == 'custom' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"></td>
    <td width="100" nowrap="nowrap"><?php _e("Custom Field", "gd-star-rating"); ?>:</td>
    <td align="right"><?php GDSRHelper::render_custom_fields($this->get_field_name('image_custom'), $instance['image_custom'], 200); ?></td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_id('image_from'); ?>-content" style="display: <?php echo $instance['image_from'] == 'content' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"></td>
    <td align="left"><?php _e("First image from post content will be used for %IMAGE% tag.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
</div>
<div class="gdsr-table-split"></div>
