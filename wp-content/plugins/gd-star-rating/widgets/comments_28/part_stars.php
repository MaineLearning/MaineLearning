<input type="hidden" id="<?php echo $this->get_field_id('div_stars'); ?>" name="<?php echo $this->get_field_name('div_stars'); ?>" value="<?php echo $instance['div_stars'] ?>" />
<div id="<?php echo $this->get_field_id('div_stars'); ?>-off" style="display: <?php echo $instance['div_stars'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_stars'); ?>')"><?php _e("Stars", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_id('div_stars'); ?>-on" style="display: <?php echo $instance['div_stars'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_stars'); ?>')"><?php _e("Stars", "gd-star-rating"); ?></a></strong></td>
    <td width="100" nowrap="nowrap"><?php _e("Set", "gd-star-rating"); ?>:</td>
    <td align="right"><select style="width: 200px" name="<?php echo $this->get_field_name('rating_stars'); ?>"><?php GDSRHelper::render_styles_select($wpst, $instance['rating_stars']); ?></select></td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="100" nowrap="nowrap"><?php _e("Size", "gd-star-rating"); ?>:</td>
    <td align="right"><?php GDSRHelper::render_star_sizes($this->get_field_name('rating_size'), $instance['rating_size'], 200); ?></td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
