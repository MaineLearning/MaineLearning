<input type="hidden" id="gdstarr-divfilter[<?php echo $wpnm; ?>]" name="<?php echo $wpfn; ?>[div_filter]" value="<?php echo $wpno['div_filter'] ?>" />
<div id="gdstarr-divfilter-off[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_filter'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divfilter', '<?php echo $wpnm; ?>')"><?php _e("Filter", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="gdstarr-divfilter-on[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_filter'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divfilter', '<?php echo $wpnm; ?>')"><?php _e("Filter", "gd-star-rating"); ?></a></strong></td>
    <td width="150" nowrap="nowrap"><?php _e("Display Votes From", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $wpfn; ?>[show]" id="gdstarr-show" style="width: 110px">
            <option value="total"<?php echo $wpno['show'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $wpno['show'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $wpno['show'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Number Of Comments", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[rows]" id="gdstarr-rows" value="<?php echo $wpno["rows"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Maximal length of comment text", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[text_max]" id="gdstarr-textmax" value="<?php echo $wpno["text_max"]; ?>" /></td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Minimum Votes", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[min_votes]" id="gdstarr-minvotes" value="<?php echo $wpno["min_votes"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Sorting Column", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $wpfn; ?>[column]" id="gdstarr-column" style="width: 110px">
            <option value="rating"<?php echo $wpno['column'] == 'rating' ? ' selected="selected"' : ''; ?>><?php _e("Rating", "gd-star-rating"); ?></option>
            <option value="voters"<?php echo $wpno['column'] == 'voters' ? ' selected="selected"' : ''; ?>><?php _e("Total Votes", "gd-star-rating"); ?></option>
            <option value="comment_id"<?php echo $wpno['column'] == 'comment_id' ? ' selected="selected"' : ''; ?>><?php _e("Comment ID", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Sorting Order", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $wpfn; ?>[order]" id="gdstarr-order" style="width: 110px">
            <option value="desc"<?php echo $wpno['order'] == 'desc' ? ' selected="selected"' : ''; ?>><?php _e("Descending", "gd-star-rating"); ?></option>
            <option value="asc"<?php echo $wpno['order'] == 'asc' ? ' selected="selected"' : ''; ?>><?php _e("Ascending", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2" height="25">
        <label for="gdstarr-hidempty" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $wpno['hide_empty'] ? 'checked="checked"' : ''; ?> id="gdstarr-hidempty" name="<?php echo $wpfn; ?>[hide_empty]" /> <?php _e("Hide comments with no recorded votes.", "gd-star-rating"); ?></label>
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
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[last_voted_days]" id="gdstarr-lastvoteddays" value="<?php echo $wpno["last_voted_days"]; ?>" />
    </td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
