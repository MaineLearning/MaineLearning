<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td nowrap="nowrap" width="140"><?php _e("Title", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="width: 260px" type="text" name="<?php echo $wpfn; ?>[title]" id="gdstarr-title" value="<?php echo $wpno["title"]; ?>" /></td>
  </tr>
</table>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Template", "gd-star-rating"); ?>:</td>
    <td align="right"><?php gdTemplateHelper::render_templates_section("WBR", $wpfn."[template_id]", $wpno["template_id"], 260); ?>
    </td>
  </tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Show Widget To", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $wpfn; ?>[display]" id="gdstarr-display" style="width: 110px">
            <option value="all"<?php echo $wpno['display'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $wpno['display'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $wpno['display'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
            <option value="hide"<?php echo $wpno['display'] == 'hide' ? ' selected="selected"' : ''; ?>><?php _e("Hide Widget", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Data Source", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $wpfn; ?>[source]" id="gdstarr-source" style="width: 130px">
            <option value="standard"<?php echo $wpno['source'] == 'standard' ? ' selected="selected"' : ''; ?>><?php _e("Standard Rating", "gd-star-rating"); ?></option>
            <option value="thumbs"<?php echo $wpno['source'] == 'thumbs' ? ' selected="selected"' : ''; ?>><?php _e("Thumbs Rating", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
<div class="gdsr-table-split"></div>
