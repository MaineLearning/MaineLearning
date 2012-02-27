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
    <td width="150" nowrap="nowrap"><?php _e("Include Articles", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select class="widefat" name="<?php echo $wpfn; ?>[select]" id="gdstarr-select" style="width: 110px">
            <option value="postpage"<?php echo $wpno['select'] == 'postpage' ? ' selected="selected"' : ''; ?>><?php _e("Posts & Pages", "gd-star-rating"); ?></option>
            <option value="post"<?php echo $wpno['select'] == 'post' ? ' selected="selected"' : ''; ?>><?php _e("Posts Only", "gd-star-rating"); ?></option>
            <option value="page"<?php echo $wpno['select'] == 'page' ? ' selected="selected"' : ''; ?>><?php _e("Pages Only", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Display Votes From", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $wpfn; ?>[show]" id="gdstarr-show" style="width: 110px">
            <option value="total"<?php echo $wpno['show'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $wpno['show'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $wpno['show'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
