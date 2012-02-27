<input type="hidden" id="gdstarr-divimage[<?php echo $wpnm; ?>]" name="<?php echo $wpfn; ?>[div_image]" value="<?php echo $wpno['div_image'] ?>" />
<div id="gdstarr-divimage-off[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_image'] == '1' ? 'none' : 'block' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divimage', '<?php echo $wpnm; ?>')"><?php _e("Avatar", "gd-star-rating"); ?></a></strong></td>
    <td align="right"><?php _e("Click on the header title to display the options.", "gd-star-rating"); ?></td>
  </tr>
</table>
</div>
<div id="gdstarr-divimage-on[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['div_image'] == '1' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="100" valign="top"><strong><a style="text-decoration: none" href="javascript:gdsrShowHidePreview('gdstarr-divimage', '<?php echo $wpnm; ?>')"><?php _e("Avatar", "gd-star-rating"); ?></a></strong></td>
    <td width="150" nowrap="nowrap"><?php _e("Size", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="text-align: right; width: 40px" type="text" name="<?php echo $wpfn; ?>[avatar]" id="gdstarr-avatar" value="<?php echo $wpno["avatar"]; ?>" /></td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
