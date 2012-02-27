<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td nowrap="nowrap" width="140"><?php _e("Title", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="width: 260px" type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo $instance["title"]; ?>" /></td>
  </tr>
</table>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Template", "gd-star-rating"); ?>:</td>
    <td align="right"><?php gdTemplateHelper::render_templates_section("WBR", $this->get_field_name('template_id'), $instance["template_id"], 260); ?>
    </td>
  </tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Show Widget To", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $this->get_field_name('display'); ?>" id="<?php echo $this->get_field_id('display'); ?>" style="width: 110px">
            <option value="all"<?php echo $instance['display'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $instance['display'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $instance['display'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
            <option value="hide"<?php echo $instance['display'] == 'hide' ? ' selected="selected"' : ''; ?>><?php _e("Hide Widget", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Data Source", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $this->get_field_name('source'); ?>" id="<?php echo $this->get_field_id('source'); ?>" style="width: 130px">
            <option value="standard"<?php echo $instance['source'] == 'standard' ? ' selected="selected"' : ''; ?>><?php _e("Standard Rating", "gd-star-rating"); ?></option>
            <option value="thumbs"<?php echo $instance['source'] == 'thumbs' ? ' selected="selected"' : ''; ?>><?php _e("Thumbs Rating", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
<div class="gdsr-table-split"></div>
