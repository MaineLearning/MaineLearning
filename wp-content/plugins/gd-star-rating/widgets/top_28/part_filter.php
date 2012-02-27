<input type="hidden" id="<?php echo $this->get_field_id('div_filter'); ?>" name="<?php echo $this->get_field_name('div_filter'); ?>" value="<?php echo $instance['div_filter'] ?>" />
<div id="<?php echo $this->get_field_id('div_filter'); ?>-off" style="display: <?php echo $instance['div_filter'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_filter'); ?>')"><?php _e("Filter", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_id('div_filter'); ?>-on" style="display: <?php echo $instance['div_filter'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_filter'); ?>')"><?php _e("Filter", "gd-star-rating"); ?></a></strong></td>
    <td width="150" nowrap="nowrap"><?php _e("Include Articles", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select class="widefat" name="<?php echo $this->get_field_name('select'); ?>" id="<?php echo $this->get_field_id('select'); ?>" style="width: 110px">
            <option value="postpage"<?php echo $instance['select'] == 'postpage' ? ' selected="selected"' : ''; ?>><?php _e("Posts & Pages", "gd-star-rating"); ?></option>
            <option value="post"<?php echo $instance['select'] == 'post' ? ' selected="selected"' : ''; ?>><?php _e("Posts Only", "gd-star-rating"); ?></option>
            <option value="page"<?php echo $instance['select'] == 'page' ? ' selected="selected"' : ''; ?>><?php _e("Pages Only", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Display Votes From", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $this->get_field_name('show'); ?>" id="<?php echo $this->get_field_id('show'); ?>" style="width: 110px">
            <option value="total"<?php echo $instance['show'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $instance['show'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $instance['show'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
