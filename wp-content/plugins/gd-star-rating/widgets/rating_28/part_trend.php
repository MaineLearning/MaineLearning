<input type="hidden" id="<?php echo $this->get_field_id('div_trend'); ?>" name="<?php echo $this->get_field_name('div_trend'); ?>" value="<?php echo $instance['div_trend'] ?>" />
<div id="<?php echo $this->get_field_id('div_trend'); ?>-off" style="display: <?php echo $instance['div_trend'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_trend'); ?>')"><?php _e("Trend", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="<?php echo $this->get_field_id('div_trend'); ?>-on" style="display: <?php echo $instance['div_trend'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('<?php echo $this->get_field_id('div_trend'); ?>')"><?php _e("Trend", "gd-star-rating"); ?></a></strong></td>
    <td width="150" nowrap="nowrap"><?php _e("Rating trend display as", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $this->get_field_name('trends_rating'); ?>" style="width: 110px" id="<?php echo $this->get_field_id('trends_rating'); ?>" onchange="gdsrChangeTrend('tr', this.options[this.selectedIndex].value, '<?php echo $this->get_field_id('trends_rating'); ?>')">
            <option value="txt"<?php echo $instance['trends_rating'] == 'txt' ? ' selected="selected"' : ''; ?>><?php _e("Text", "gd-star-rating"); ?></option>
            <option value="img"<?php echo $instance['trends_rating'] == 'img' ? ' selected="selected"' : ''; ?>><?php _e("Image", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="<?php echo $this->get_field_id('trends_rating'); ?>-tr-txt" style="display: <?php echo $instance['trends_rating'] == 'txt' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Up", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $this->get_field_name('trends_rating_rise'); ?>" id="<?php echo $this->get_field_id('trends_rating_rise'); ?>" value="<?php echo $instance["trends_rating_rise"]; ?>" /></td>
        <td><?php _e("Equal", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $this->get_field_name('trends_rating_same'); ?>" id="<?php echo $this->get_field_id('trends_rating_same'); ?>" value="<?php echo $instance["trends_rating_same"]; ?>" /></td>
        <td><?php _e("Down", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $this->get_field_name('trends_rating_fall'); ?>" id="<?php echo $this->get_field_id('trends_rating_fall'); ?>" value="<?php echo $instance["trends_rating_fall"]; ?>" /></td>
      </tr>
    </table>
</div>
<div id="<?php echo $this->get_field_id('trends_rating'); ?>-tr-img" style="display: <?php echo $instance['trends_rating'] == 'img' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Image set", "gd-star-rating"); ?>:</td>
        <td align="right">
            <select style="width: 180px;" name="<?php echo $this->get_field_name('trends_rating_set'); ?>" id="<?php echo $this->get_field_id('trends_rating_set'); ?>">
                <?php GDSRHelper::render_styles_select($wptr, $instance["trends_rating_set"]); ?>
            </select>
        </td>
      </tr>
    </table>
</div>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100"></td>
    <td width="150" nowrap="nowrap"><?php _e("Voting trend display as", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $this->get_field_name('trends_voting'); ?>" style="width: 110px" id="<?php echo $this->get_field_id('trends_voting'); ?>" onchange="gdsrChangeTrend('tv', this.options[this.selectedIndex].value, '<?php echo $this->get_field_id('trends_voting'); ?>')">
            <option value="txt"<?php echo $instance['trends_voting'] == 'txt' ? ' selected="selected"' : ''; ?>><?php _e("Text", "gd-star-rating"); ?></option>
            <option value="img"<?php echo $instance['trends_voting'] == 'img' ? ' selected="selected"' : ''; ?>><?php _e("Image", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="<?php echo $this->get_field_id('trends_voting'); ?>-tv-txt" style="display: <?php echo $instance['trends_voting'] == 'txt' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Up", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $this->get_field_name('trends_voting_rise'); ?>" id="<?php echo $this->get_field_id('trends_voting_rise'); ?>" value="<?php echo $instance["trends_voting_rise"]; ?>" /></td>
        <td><?php _e("Equal", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $this->get_field_name('trends_voting_same'); ?>" id="<?php echo $this->get_field_id('trends_voting_same'); ?>" value="<?php echo $instance["trends_voting_same"]; ?>" /></td>
        <td><?php _e("Down", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $this->get_field_name('trends_voting_fall'); ?>" id="<?php echo $this->get_field_id('trends_voting_fall'); ?>" value="<?php echo $instance["trends_voting_fall"]; ?>" /></td>
      </tr>
    </table>
</div>
<div id="<?php echo $this->get_field_id('trends_voting'); ?>-tv-img" style="display: <?php echo $instance['trends_voting'] == 'img' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Image set", "gd-star-rating"); ?>:</td>
        <td align="right">
            <select style="width: 180px;" name="<?php echo $this->get_field_name('trends_voting_set'); ?>" id="<?php echo $this->get_field_id('trends_voting_set'); ?>">
                <?php GDSRHelper::render_styles_select($wptr, $instance["trends_voting_set"]); ?>
            </select>
        </td>
      </tr>
    </table>
</div>
</div>
<div class="gdsr-table-split"></div>
