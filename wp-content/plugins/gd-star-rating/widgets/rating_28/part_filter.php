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
    <td width="150" nowrap="nowrap" valign="top">
        <?php _e("Include post types", "gd-star-rating"); ?>:<br/>
        <em><small><?php _e("Select one or more.", "gd-star-rating"); ?></small></em>
    </td>
    <td align="right">
        <?php if ($wpvr > 29) { ?>
        <select multiple class="widefat" name="<?php echo $this->get_field_name('select'); ?>[]" id="<?php echo $this->get_field_id('select'); ?>" style="height: 66px;">
            <?php foreach ($custom_post_types as $cp_name => $cp_label) {
                $selected = in_array($cp_name, $select_post_types) ? ' selected="selected"' : ' ';
                echo '<option value="'.$cp_name.'"'.$selected.'>'.$cp_label.'</option>';
            } ?>
        </select>
        <?php } else { ?>
        <select class="widefat" name="<?php echo $this->get_field_name('select'); ?>" id="<?php echo $this->get_field_id('select'); ?>" style="width: 110px">
            <option value="postpage"<?php echo $instance['select'] == 'postpage' ? ' selected="selected"' : ''; ?>><?php _e("Posts & Pages", "gd-star-rating"); ?></option>
            <option value="post"<?php echo $instance['select'] == 'post' ? ' selected="selected"' : ''; ?>><?php _e("Posts Only", "gd-star-rating"); ?></option>
            <option value="page"<?php echo $instance['select'] == 'page' ? ' selected="selected"' : ''; ?>><?php _e("Pages Only", "gd-star-rating"); ?></option>
        </select>
        <?php } ?>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Display votes from", "gd-star-rating"); ?>:</td>
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
    <td width="150" nowrap="nowrap"><?php _e("Number of posts", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('rows'); ?>" id="<?php echo $this->get_field_id('rows'); ?>" value="<?php echo $instance["rows"]; ?>" />
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Maximal character length for title", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('tpl_title_length'); ?>" id="<?php echo $this->get_field_id('tpl_title_length'); ?>" value="<?php echo $instance["tpl_title_length"]; ?>" /></td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Number of words for excerpt", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('excerpt_words'); ?>" id="<?php echo $this->get_field_id('excerpt_words'); ?>" value="<?php echo $instance["excerpt_words"]; ?>" /></td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Minimum votes", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('min_votes'); ?>" id="<?php echo $this->get_field_id('min_votes'); ?>" value="<?php echo $instance["min_votes"]; ?>" />
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
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('min_count'); ?>" id="<?php echo $this->get_field_id('min_count'); ?>" value="<?php echo $instance["min_count"]; ?>" />
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
        <select name="<?php echo $this->get_field_name('column'); ?>" id="<?php echo $this->get_field_id('column'); ?>" style="width: 110px">
            <option value="rating"<?php echo $instance['column'] == 'rating' ? ' selected="selected"' : ''; ?>><?php _e("Rating", "gd-star-rating"); ?></option>
            <option value="voters"<?php echo $instance['column'] == 'voters' ? ' selected="selected"' : ''; ?>><?php _e("Total Votes", "gd-star-rating"); ?></option>
            <option value="post_id"<?php echo $instance['column'] == 'post_id' ? ' selected="selected"' : ''; ?>><?php _e("ID", "gd-star-rating"); ?></option>
            <option value="title"<?php echo $instance['column'] == 'title' ? ' selected="selected"' : ''; ?>><?php _e("Title", "gd-star-rating"); ?></option>
            <option value="review"<?php echo $instance['column'] == 'review' ? ' selected="selected"' : ''; ?>><?php _e("Review", "gd-star-rating"); ?></option>
            <option value="counter"<?php echo $instance['column'] == 'counter' ? ' selected="selected"' : ''; ?>><?php _e("Count", "gd-star-rating"); ?></option>
            <option value="bayesian"<?php echo $instance['column'] == 'bayesian' ? ' selected="selected"' : ''; ?>><?php _e("Bayesian Rating", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
  <tr>
    <td width="100" valign="top"></td>
    <td width="150" nowrap="nowrap"><?php _e("Sorting order", "gd-star-rating"); ?>:</td>
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
        <label for="gdstarr-bayesiancalculation" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $instance['bayesian_calculation'] ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('bayesian_calculation'); ?>" name="<?php echo $this->get_field_name('bayesian_calculation'); ?>" /> <?php _e("Bayesian minumum votes required.", "gd-star-rating"); ?></label>
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
    <td nowrap="nowrap" colspan="2" height="25">
        <label for="gdstarr-hidenoreview" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $instance['hide_noreview'] ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('hide_noreview'); ?>" name="<?php echo $this->get_field_name('hide_noreview'); ?>" /> <?php _e("Hide articles with no review values.", "gd-star-rating"); ?></label>
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
        <label><?php gdsrAdmDB::get_combo_categories($instance['category'], $this->get_field_name('category')); ?></label>
    </td>
  </tr>
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap" colspan="2" height="25">
        <label for="gdstarr-categorytoponly" style="text-align:right;"><input class="checkbox" type="checkbox" <?php echo $instance['category_toponly'] ? 'checked="checked"' : ''; ?> id="<?php echo $this->get_field_id('category_toponly'); ?>" name="<?php echo $this->get_field_id('category_toponly'); ?>" /> <?php _e("Only selected category, no subcategories.", "gd-star-rating"); ?></label>
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
        <input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $this->get_field_name('last_voted_days'); ?>" id="<?php echo $this->get_field_id('last_voted_days'); ?>" value="<?php echo $instance["last_voted_days"]; ?>" />
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
        <select name="<?php echo $this->get_field_name('publish_date'); ?>" style="width: 110px" id="<?php echo $this->get_field_id('publish_date'); ?>" onchange="gdsrChangeDate('<?php echo $this->get_field_name('publish_date'); ?>', this.options[this.selectedIndex].value)">
            <option value="alldt"<?php echo $instance['publish_date'] == 'alldt' ? ' selected="selected"' : ''; ?>><?php _e("Any date", "gd-star-rating"); ?></option>
            <option value="lastd"<?php echo $instance['publish_date'] == 'lastd' ? ' selected="selected"' : ''; ?>><?php _e("Last # days", "gd-star-rating"); ?></option>
            <option value="month"<?php echo $instance['publish_date'] == 'month' ? ' selected="selected"' : ''; ?>><?php _e("Exact month", "gd-star-rating"); ?></option>
            <option value="range"<?php echo $instance['publish_date'] == 'range' ? ' selected="selected"' : ''; ?>><?php _e("Date range", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="<?php echo $this->get_field_name('publish_date'); ?>-lastd" style="display: <?php echo $instance['publish_date'] == 'lastd' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100"></td>
    <td width="150" nowrap="nowrap"><?php _e("Number Of Days", "gd-star-rating"); ?>:</td>
    <td align="right">
        <input class="widefat" style="text-align: right; width: 102px" type="text" name="<?php echo $this->get_field_name('publish_days'); ?>" id="<?php echo $this->get_field_id('publish_days'); ?>" value="<?php echo $instance["publish_days"]; ?>" />
    </td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_name('publish_date'); ?>-month" style="display: <?php echo $instance['publish_date'] == 'month' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100"></td>
    <td width="150" nowrap="nowrap"><?php _e("Month", "gd-star-rating"); ?>:</td>
    <td align="right">
        <?php gdsrAdmDB::get_combo_months($instance['publish_month'], $this->get_field_name('publish_month')); ?>
    </td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_name('publish_date'); ?>-range" style="display: <?php echo $instance['publish_date'] == 'range' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100"></td>
    <td nowrap="nowrap"><?php _e("Range", "gd-star-rating"); ?>:</td>
    <td align="right" width="85"><input class="widefat" style="text-align: right; width: 85px" type="text" name="<?php echo $this->get_field_name('publish_range_from'); ?>" id="<?php echo $this->get_field_id('publish_range_from'); ?>" value="<?php echo $instance["publish_range_from"]; ?>" /></td>
    <td align="center" width="10">-</td>
    <td align="right" width="85"><input class="widefat" style="text-align: right; width: 85px" type="text" name="<?php echo $this->get_field_name('publish_range_to'); ?>" id="<?php echo $this->get_field_id('publish_range_to'); ?>" value="<?php echo $instance["publish_range_to"]; ?>" /></td>
  </tr>
</table>
</div>
</div>
<div class="gdsr-table-split"></div>
