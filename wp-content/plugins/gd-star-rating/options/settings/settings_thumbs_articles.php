<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Rating", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Thumbs Set", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_thumb_style" id="gdsr_thumb_style">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->thumbs, $gdsr_options["thumb_style"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150" align="left">
                <?php GDSRHelper::render_thumbs_sizes("gdsr_thumb_size", $gdsr_options["thumb_size"]); ?>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150">MSIE 6:</td>
                <td width="200" align="left">
                <select style="width: 180px;" name="gdsr_thumb_style_ie6" id="gdsr_thumb_style_ie6">
                <?php GDSRHelper::render_styles_select($gdsr_gfx->thumbs, $gdsr_options["thumb_style_ie6"]); ?>
                </select>
                </td>
                <td width="10"></td>
                <td width="150"><?php _e("Rating header", "gd-star-rating"); ?>:</td>
                <td><input type="text" name="gdsr_thumb_header_text" id="gdsr_thumb_header_text" value="<?php echo esc_attr($gdsr_options["thumb_header_text"]); ?>" style="width: 170px" /></td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150" valign="top"><?php _e("Auto insert rating code", "gd-star-rating"); ?>:</td>
                <td width="200" valign="top">
                    <input type="checkbox" name="gdsr_thumb_posts" id="gdsr_thumb_posts"<?php if ($gdsr_options["thumb_display_posts"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_posts"><?php _e("For individual posts.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_thumb_pages" id="gdsr_thumb_pages"<?php if ($gdsr_options["thumb_display_pages"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_pages"><?php _e("For individual pages.", "gd-star-rating"); ?></label>
                </td>
                <td width="10"></td>
                <td valign="top">
                    <input type="checkbox" name="gdsr_thumb_archive" id="gdsr_thumb_archive"<?php if ($gdsr_options["thumb_display_archive"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_archive"><?php _e("For posts displayed in Archives.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_thumb_home" id="gdsr_thumb_home"<?php if ($gdsr_options["thumb_display_home"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_home"><?php _e("For posts displayed on Front Page.", "gd-star-rating"); ?></label>
                    <br />
                    <input type="checkbox" name="gdsr_thumb_search" id="gdsr_thumb_search"<?php if ($gdsr_options["thumb_display_search"] == 1) echo " checked"; ?> /><label style="margin-left: 5px;" for="gdsr_search"><?php _e("For posts displayed on Search results.", "gd-star-rating"); ?></label>
                </td>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Auto insert location", "gd-star-rating"); ?>:</td>
                <td width="200" valign="top"><?php GDSRHelper::render_insert_position("gdsr_thumb_auto_display_position", $gdsr_options["thumb_auto_display_position"]); ?></td>
            </tr>
        </table>
     </td>
</tr>
<tr><th scope="row"><?php _e("Defaults", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Vote rule", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_rules_combo("gdsr_recc_default_vote_articles", $gdsr_options["recc_default_voterules_articles"]); ?>
                </td>
                <td width="10"></td>
            <?php if ($gdsr_options["moderation_active"] == 1) { ?>
                <td width="150"><?php _e("Moderation rule", "gd-star-rating"); ?>:</td>
                <td width="200" align="left">
                <?php GDSRHelper::render_moderation_combo("gdsr_recc_default_mod_articles", $gdsr_options["recc_default_moderation_articles"]); ?>
                </td>
            <?php } ?>
            </tr>
        </table>
        <div class="gdsr-table-split"></div>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tr>
                <td width="150"><?php _e("Rating Template", "gd-star-rating"); ?>:</td>
                <td align="left"><?php gdTemplateHelper::render_templates_section("TAB", "gdsr_default_tab_template", $gdsr_options["default_tab_template"], 350); ?></td>
            </tr>
            <tr>
                <td width="150"><?php _e("Animation indicator", "gd-star-rating"); ?>:</td>
                <td align="left"><?php GDSRHelper::render_loaders("gdsr_wait_loader_artthumb", $gdsr_options["wait_loader_artthumb"], 'jqloaderartthumb', 180, '', true); ?></td>
            </tr>
        </table>
    </td>
</tr>
</tbody></table>
