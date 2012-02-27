<input type="hidden" id="gdstarr-divtrend[<?php echo $wpnm; ?>]" name="<?php echo $wpfn; ?>[div_trend]" value="<?php echo $wpno['div_trend'] ?>" />
<div id="gdstarr-divtrend-off[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_trend'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divtrend', '<?php echo $wpnm; ?>')"><?php _e("Trend", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="gdstarr-divtrend-on[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_trend'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divtrend', '<?php echo $wpnm; ?>')"><?php _e("Trend", "gd-star-rating"); ?></a></strong></td>
    <td width="150" nowrap="nowrap"><?php _e("Rating trend display as", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $wpfn; ?>[trends_rating]" style="width: 110px" id="gdstarr-trend-rating" onchange="gdsrChangeTrend('tr', this.options[this.selectedIndex].value, '<?php echo $wpnm; ?>')">
            <option value="txt"<?php echo $wpno['trends_rating'] == 'txt' ? ' selected="selected"' : ''; ?>><?php _e("Text", "gd-star-rating"); ?></option>
            <option value="img"<?php echo $wpno['trends_rating'] == 'img' ? ' selected="selected"' : ''; ?>><?php _e("Image", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="gdsr-tr-txt[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['trends_rating'] == 'txt' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Up", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $wpfn; ?>[trends_rating_rise]" id="gdstarr-trendsratingrise" value="<?php echo $wpno["trends_rating_rise"]; ?>" /></td>
        <td><?php _e("Equal", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $wpfn; ?>[trends_rating_same]" id="gdstarr-trendsratingsame" value="<?php echo $wpno["trends_rating_same"]; ?>" /></td>
        <td><?php _e("Down", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $wpfn; ?>[trends_rating_fall]" id="gdstarr-trendsratingfall" value="<?php echo $wpno["trends_rating_fall"]; ?>" /></td>
      </tr>
    </table>
</div>
<div id="gdsr-tr-img[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['trends_rating'] == 'img' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Image set", "gd-star-rating"); ?>:</td>
        <td align="right">
            <select style="width: 180px;" name="<?php echo $wpfn; ?>[trends_rating_set]" id="gdstarr-trendsratingset">
                <?php GDSRHelper::render_styles_select($wptr, $wpno["trends_rating_set"]); ?>
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
        <select name="<?php echo $wpfn; ?>[trends_voting]" style="width: 110px" id="gdstarr-trend-voting" onchange="gdsrChangeTrend('tv', this.options[this.selectedIndex].value, '<?php echo $wpnm; ?>')">
            <option value="txt"<?php echo $wpno['trends_voting'] == 'txt' ? ' selected="selected"' : ''; ?>><?php _e("Text", "gd-star-rating"); ?></option>
            <option value="img"<?php echo $wpno['trends_voting'] == 'img' ? ' selected="selected"' : ''; ?>><?php _e("Image", "gd-star-rating"); ?></option>
        </select>
    </td>
  </tr>
</table>
<div id="gdsr-tv-txt[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['trends_voting'] == 'txt' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Up", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $wpfn; ?>[trends_voting_rise]" id="gdstarr-trendsvotingrise" value="<?php echo $wpno["trends_voting_rise"]; ?>" /></td>
        <td><?php _e("Equal", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $wpfn; ?>[trends_voting_same]" id="gdstarr-trendsvotingsame" value="<?php echo $wpno["trends_voting_same"]; ?>" /></td>
        <td><?php _e("Down", "gd-star-rating"); ?>:</td>
        <td width="50" align="right"><input class="widefat" style="width: 35px" type="text" name="<?php echo $wpfn; ?>[trends_voting_fall]" id="gdstarr-trendsvotingfall" value="<?php echo $wpno["trends_voting_fall"]; ?>" /></td>
      </tr>
    </table>
</div>
<div id="gdsr-tv-img[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['trends_voting'] == 'img' ? 'block' : 'none' ?>">
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td width="100"></td>
        <td><?php _e("Image set", "gd-star-rating"); ?>:</td>
        <td align="right">
            <select style="width: 180px;" name="<?php echo $wpfn; ?>[trends_voting_set]" id="gdstarr-trendsvotingset">
                <?php GDSRHelper::render_styles_select($wptr, $wpno["trends_voting_set"]); ?>
            </select>
        </td>
      </tr>
    </table>
</div>
</div>
<div class="gdsr-table-split"></div>
