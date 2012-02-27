<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td nowrap="nowrap" width="140"><?php _e("Title", "gd-star-rating"); ?>:</td>
    <td align="right"><input class="widefat" style="width: 260px" type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo $instance["title"]; ?>" /></td>
  </tr>
</table>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Template", "gd-star-rating"); ?>:</td>
    <td align="right"><?php gdTemplateHelper::render_templates_section("WSR", $this->get_field_name('template_id'), $instance["template_id"], 260); ?>
    </td>
  </tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Show Widget To", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $this->get_field_name('dispaly'); ?>" id="<?php echo $this->get_field_id('display'); ?>" style="width: 130px">
            <option value="all"<?php echo $instance['display'] == 'all' ? ' selected="selected"' : ''; ?>><?php _e("Everyone", "gd-star-rating"); ?></option>
            <option value="visitors"<?php echo $instance['display'] == 'visitors' ? ' selected="selected"' : ''; ?>><?php _e("Visitors Only", "gd-star-rating"); ?></option>
            <option value="users"<?php echo $instance['display'] == 'users' ? ' selected="selected"' : ''; ?>><?php _e("Users Only", "gd-star-rating"); ?></option>
            <option value="hide"<?php echo $instance['display'] == 'hide' ? ' selected="selected"' : ''; ?>><?php _e("Hide Widget", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Items Grouping", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $this->get_field_name('grouping'); ?>" id="<?php echo $this->get_field_id('grouping'); ?>" style="width: 130px" onchange="gdsrChangeTaxonomy('<?php echo $this->get_field_id('grouping'); ?>', this.options[this.selectedIndex].value)">
            <option value="post"<?php echo $instance['grouping'] == 'post' ? ' selected="selected"' : ''; ?>><?php _e("No grouping", "gd-star-rating"); ?></option>
            <option value="user"<?php echo $instance['grouping'] == 'user' ? ' selected="selected"' : ''; ?>><?php _e("User based", "gd-star-rating"); ?></option>
            <option value="category"<?php echo $instance['grouping'] == 'category' ? ' selected="selected"' : ''; ?>><?php _e("Category based", "gd-star-rating"); ?></option>
            <option value="taxonomy"<?php echo $instance['grouping'] == 'taxonomy' ? ' selected="selected"' : ''; ?>><?php _e("Taxonomy based", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
<div id="<?php echo $this->get_field_id('grouping'); ?>-tax" style="display: <?php echo $instance['grouping'] == 'taxonomy' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Taxonomy", "gd-star-rating"); ?>:</td>
    <td align="right">
        <select name="<?php echo $this->get_field_name('taxonomy'); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>" style="width: 130px">
            <?php GDSRHelper::render_taxonomy_select($instance['taxonomy']); ?>
        </select>
    </td>
  </tr>
</table>
</div>
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Data Source", "gd-star-rating"); ?>:</td>
    <td align="right">
        <label><select name="<?php echo $this->get_field_name('source'); ?>" id="<?php echo $this->get_field_id('source'); ?>" style="width: 130px" onchange="gdsrChangeSource('<?php echo $this->get_field_id('source'); ?>', this.options[this.selectedIndex].value)">
            <option value="standard"<?php echo $instance['source'] == 'standard' ? ' selected="selected"' : ''; ?>><?php _e("Standard Rating", "gd-star-rating"); ?></option>
            <?php if (count($wpml) > 0) { ?><option value="multis"<?php echo $instance['source'] == 'multis' ? ' selected="selected"' : ''; ?>><?php _e("Multi Rating", "gd-star-rating"); ?></option><?php } ?>
            <option value="thumbs"<?php echo $instance['source'] == 'thumbs' ? ' selected="selected"' : ''; ?>><?php _e("Thumbs Rating", "gd-star-rating"); ?></option>
        </select></label>
    </td>
  </tr>
</table>
<div id="<?php echo $this->get_field_id('source'); ?>-multis" style="display: <?php echo $instance['source'] == 'multis' ? 'block' : 'none' ?>">
<table border="0" cellpadding="2" cellspacing="0" width="100%">
  <tr>
    <td width="140" nowrap="nowrap"><?php _e("Multi Set", "gd-star-rating"); ?>:</td>
    <td align="right"><select name="<?php echo $this->get_field_name('source_set'); ?>" id="<?php echo $this->get_field_id('source_set'); ?>" style="width: 200px"><?php GDSRHelper::render_styles_select($wpml, $instance['source_set']); ?></select></td>
  </tr>
</table>
</div>
<div class="gdsr-table-split"></div>
