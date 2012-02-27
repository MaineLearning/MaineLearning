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
    <td width="150" nowrap="nowrap"><?php _e("Display Votes From", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $this->get_field_name('show'); ?>" id="<?php echo $this->get_field_id('show'); ?>" style="width: 110px">
            <option value="total"<?php echo $instance['show'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $instance['show'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $instance['show'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Number Of Comments", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('rows'); ?>" id="<?php echo $this->get_field_id('rows'); ?>" value="<?php echo $instance["rows"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Maximal length of comment text", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('text_max'); ?>" id="<?php echo $this->get_field_id('text_max'); ?>" value="<?php echo $instance["text_max"]; ?>" /></td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Minimum Votes", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('min_votes'); ?>" id="<?php echo $this->get_field_id('min_votes'); ?>" value="<?php echo $instance["min_votes"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Sorting Column", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $this->get_field_name('column'); ?>" id="<?php echo $this->get_field_id('column'); ?>" style="width: 110px">
            <option value="rating"<?php echo $instance['column'] == 'rating' ? ' selected="selected"' : ''; ?>><?php _e("Rating", "gd-star-rating"); ?></option>
            <option value="voters"<?php echo $instance['column'] == 'voters' ? ' selected="selected"' : ''; ?>><?php _e("Total Votes", "gd-star-rating"); ?></option>
            <option value="comment_id"<?php echo $instance['column'] == 'comment_id' ? ' selected="selected"' : ''; ?>><?php _e("Comment ID", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Sorting Order", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $this->get_field_name('order'); ?>" id="<?php echo $this->get_field_id('order'); ?>" style="width: 110px">
            <option value="desc"<?php echo $instance['order'] == 'desc' ? ' selected="selected"' : ''; ?>><?php _e("Descending", "gd-star-rating"); ?></option>
            <option value="asc"<?php echo $instance['order'] == 'asc' ? ' selected="selected"' : ''; ?>><?php _e("Ascending", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2" height="25">
        <label for="gdstarr-hidempty" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $instance['hide_empty'] ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" /> <?php _e("Hide articles with no recorded votes.", "gd-star-rating"); ?></label>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2">
        <div class="gdsr-table-split-filter"></div>
        <?php _e("Use only comments voted for in last # days.", "gd-star-rating") ?>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Enter 0 for all", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('last_voted_days'); ?>" id="<?php echo $this->get_field_id('last_voted_days'); ?>" value="<?php echo $instance["last_voted_days"]; ?>" />
    </td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
