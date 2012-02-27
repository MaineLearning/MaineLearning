<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td nowrap="nowrap" width="140"><?php _e("Title", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="width: 260px" type="text" name="<?php echo $wpfn; ?>[title]" id="gdstarr-title" value="<?php echo $wpno["title"]; ?>" /></td>
  </tr>
</table>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Template", "gd-star-rating"); ?>:</td>
    <td align="right"><?php gdTemplateHelper::render_templates_section("WSR", $wpfn."[template_id]", $wpno["template_id"], 260); ?></td>
  </tr>
    <td width="140" nowrap="nowrap"><?php _e("Show Widget To", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $wpfn; ?>[display]" id="gdstarr-display" style="width: 130px">
            <option value="all"<?php echo $wpno['display'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $wpno['display'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $wpno['display'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
            <option value="hide"<?php echo $wpno['display'] == 'hide' ? ' selected="selected"' : ''; ?>><?php _e("Hide Widget", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Items Grouping", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $wpfn; ?>[grouping]" id="gdstarr-grouping" style="width: 130px" onchange="gdsrChangeTaxonomy(this.options[this.selectedIndex].value, '<?php echo $wpnm; ?>')">
            <option value="post"<?php echo $wpno['grouping'] == 'post' ? ' selected="selected"' : ''; ?>><?php _e("No grouping", "gd-star-rating"); ?></option>
            <option value="user"<?php echo $wpno['grouping'] == 'user' ? ' selected="selected"' : ''; ?>><?php _e("User based", "gd-star-rating"); ?></option>
            <option value="category"<?php echo $wpno['grouping'] == 'category' ? ' selected="selected"' : ''; ?>><?php _e("Category based", "gd-star-rating"); ?></option>
            <option value="taxonomy"<?php echo $wpno['grouping'] == 'taxonomy' ? ' selected="selected"' : ''; ?>><?php _e("Taxonomy based", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
<div id="gdsr-src-tax[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['grouping'] == 'taxonomy' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Taxonomy", "gd-star-rating"); ?>:</td>
    <td align="right"><select name="<?php echo $wpfn; ?>[taxonomy]" style="width: 130px"><?php GDSRHelper::render_taxonomy_select($wpno['taxonomy']); ?></select></td>
  </tr>
</table>
</div>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Data Source", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $wpfn; ?>[source]" id="gdstarr-source" style="width: 130px" onchange="gdsrChangeSource(this.options[this.selectedIndex].value, '<?php echo $wpnm; ?>')">
            <option value="standard"<?php echo $wpno['source'] == 'standard' ? ' selected="selected"' : ''; ?>><?php _e("Standard Rating", "gd-star-rating"); ?></option>
<?php if (count($wpml) > 0) { ?>
            <option value="multis"<?php echo $wpno['source'] == 'multis' ? ' selected="selected"' : ''; ?>><?php _e("Multi Rating", "gd-star-rating"); ?></option>
<?php } ?>
            <option value="thumbs"<?php echo $wpno['source'] == 'thumbs' ? ' selected="selected"' : ''; ?>><?php _e("Thumbs Rating", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
<div id="gdsr-src-multi[<?php echo $wpnm; ?>]" style="display: <?php echo $wpno['source'] == 'multis' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Multi Set", "gd-star-rating"); ?>:</td>
    <td align="right"><select name="<?php echo $wpfn; ?>[source_set]" style="width: 200px"><?php GDSRHelper::render_styles_select($wpml, $wpno['source_set']); ?></select></td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
