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
    <td width="150" nowrap="nowrap"><?php _e("Include articles", "gd-star-rating"); ?>:</td>
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
    <td width="150" nowrap="nowrap"><?php _e("Display votes from", "gd-star-rating"); ?>:</td>
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
    <td width="150" nowrap="nowrap"><?php _e("Number of posts", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[rows]" id="gdstarr-rows" value="<?php echo $wpno["rows"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Maximal character length for title", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[title_max]" id="gdstarr-titlemax" value="<?php echo $wpno["tpl_title_length"]; ?>" /></td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Number of words for excerpt", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[excerpt_words]" id="gdstarr-excerptwords" value="<?php echo $wpno["excerpt_words"]; ?>" /></td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Minimum votes", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[min_votes]" id="gdstarr-minvotes" value="<?php echo $wpno["min_votes"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2">
        <div class="gdsr-table-split-filter"></div>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Minimum posts count", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[min_count]" id="gdstarr-mincount" value="<?php echo $wpno["min_count"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td colspan="2" align="right"><em><small><?php _e("This filter will be used only with grouping options.", "gd-star-rating"); ?></small></em></td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2">
        <div class="gdsr-table-split-filter"></div>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Sorting column", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $wpfn; ?>[column]" id="gdstarr-column" style="width: 110px">
            <option value="rating"<?php echo $wpno['column'] == 'rating' ? ' selected="selected"' : ''; ?>><?php _e("Rating", "gd-star-rating"); ?></option>
            <option value="voters"<?php echo $wpno['column'] == 'voters' ? ' selected="selected"' : ''; ?>><?php _e("Total Votes", "gd-star-rating"); ?></option>
            <option value="id"<?php echo $wpno['column'] == 'id' ? ' selected="selected"' : ''; ?>><?php _e("ID", "gd-star-rating"); ?></option>
            <option value="title"<?php echo $wpno['column'] == 'title' ? ' selected="selected"' : ''; ?>><?php _e("Title", "gd-star-rating"); ?></option>
            <option value="review"<?php echo $wpno['column'] == 'review' ? ' selected="selected"' : ''; ?>><?php _e("Review", "gd-star-rating"); ?></option>
            <option value="counter"<?php echo $wpno['column'] == 'counter' ? ' selected="selected"' : ''; ?>><?php _e("Count", "gd-star-rating"); ?></option>
            <option value="bayesian"<?php echo $wpno['column'] == 'bayesian' ? ' selected="selected"' : ''; ?>><?php _e("Bayesian Rating", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Sorting order", "gd-star-rating"); ?>:</td>
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
        <label for="gdstarr-bayesiancalculation" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $wpno['bayesian_calculation'] ? 'checked="checked"' : ''; ?> id="gdstarr-bayesiancalculation" name="<?php echo $wpfn; ?>[bayesian_calculation]" /> <?php _e("Bayesian minumum votes required.", "gd-star-rating"); ?></label>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2" height="25">
        <label for="gdstarr-hidempty" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $wpno['hide_empty'] ? 'checked="checked"' : ''; ?> id="gdstarr-hidempty" name="<?php echo $wpfn; ?>[hide_empty]" /> <?php _e("Hide articles with no recorded votes.", "gd-star-rating"); ?></label>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2" height="25">
        <label for="gdstarr-hidenoreview" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $wpno['hide_noreview'] ? 'checked="checked"' : ''; ?> id="gdstarr-hidenoreview" name="<?php echo $wpfn; ?>[hide_noreview]" /> <?php _e("Hide articles with no review values.", "gd-star-rating"); ?></label>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2">
        <div class="gdsr-table-split-filter"></div>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td width="150" nowrap="nowrap"><?php _e("Article Category", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><?php gdsrAdmDB::get_combo_categories($wpno['category'], $wpfn.'[category]'); ?></label>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2" height="25">
        <label for="gdstarr-categorytoponly" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $wpno['category_toponly'] ? 'checked="checked"' : ''; ?> id="gdstarr-categorytoponly" name="<?php echo $wpfn; ?>[category_toponly]" /> <?php _e("Only selected category, no subcategories.", "gd-star-rating"); ?></label>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2">
        <div class="gdsr-table-split-filter"></div>
        <?php _e("Use only articles voted for in last # days.", "gd-star-rating") ?>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Enter 0 for all", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[last_voted_days]" id="gdstarr-lastvoteddays" value="<?php echo $wpno["last_voted_days"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td colspan="2">
        <div class="gdsr-table-split-filter"></div>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td width="150" nowrap="nowrap"><?php _e("Article Publish Date", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $wpfn; ?>[publish_date]" style="width: 110px" id="gdstarr-publishdate" onchange="gdsrChangeDate(this.options[this.selectedIndex].value, '<?php echo $wpnm; ?>')">
            <option value="alldt"<?php echo $wpno['publish_date'] == 'alldt' ? ' selected="selected"' : ''; ?>><?php _e("Any date", "gd-star-rating"); ?></option>
            <option value="lastd"<?php echo $wpno['publish_date'] == 'lastd' ? ' selected="selected"' : ''; ?>><?php _e("Last # days", "gd-star-rating"); ?></option>
            <option value="month"<?php echo $wpno['publish_date'] == 'month' ? ' selected="selected"' : ''; ?>><?php _e("Exact month", "gd-star-rating"); ?></option>
            <option value="range"<?php echo $wpno['publish_date'] == 'range' ? ' selected="selected"' : ''; ?>><?php _e("Date range", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="gdsr-pd-lastd[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['publish_date'] == 'lastd' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100"></td>
    <td width="150" nowrap="nowrap"><?php _e("Number Of Days", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 102px" type="text" name="<?php echo $wpfn; ?>[publish_days]" id="gdstarr-publishdays" value="<?php echo $wpno["publish_days"]; ?>" />
    </td>
  </tr>
</table>
</div>
<div id="gdsr-pd-month[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['publish_date'] == 'month' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100"></td>
    <td width="150" nowrap="nowrap"><?php _e("Month", "gd-star-rating"); ?>:</td>
    <td align="right">
        <?php gdsrAdmDB::get_combo_months($wpno['publish_month'], $wpfn."[publish_month]"); ?>
    </td>
  </tr>
</table>
</div>
<div id="gdsr-pd-range[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['publish_date'] == 'range' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Range", "gd-star-rating"); ?>:</td>
    <td align="right" width="85"><input class="widefat" style="text-align: right; width: 85px" type="text" name="<?php echo $wpfn; ?>[publish_range_from]" id="gdstarr-publishrangefrom" value="<?php echo $wpno["publish_range_from"]; ?>" /></td>
    <td align="center" width="10">-</td>
    <td align="right" width="85"><input class="widefat" style="text-align: right; width: 85px" type="text" name="<?php echo $wpfn; ?>[publish_range_to]" id="gdstarr-publishrangeto" value="<?php echo $wpno["publish_range_to"]; ?>" /></td>
  </tr>
</table>
</div>
</div>
<div class="gdsr-table-split"></div>
